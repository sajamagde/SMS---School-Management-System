<?php

namespace App\Services\Alerts;

use App\Enums\StudentAlertType;
use App\Models\Student;
use App\Models\StudentAlert;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAlertService
{

    public function getTeacherClasses(
        Teacher $teacher,
        ?int $gradeId = null
    ): Collection {
        return $teacher->assignments()
            ->when(
                $gradeId,
                fn($query) => $query->whereHas(
                    'section',
                    fn($sectionQuery) => $sectionQuery->where(
                        'grade_id',
                        $gradeId
                    )
                )
            )
            ->with([
                'section' => function ($query) {
                    $query->with('grade')
                        ->withCount([
                            'enrollments as students_count' => function ($query) {
                                $query->where('status', 'active');
                            },
                        ]);
                },
                'subject',
            ])
            ->get()
            ->groupBy('section.grade_id')
            ->map(function (Collection $assignments) {
                $firstAssignment = $assignments->first();

                return [
                    'grade_id' => $firstAssignment->section->grade_id,
                    'grade_name' => $firstAssignment->section->grade->name,

                    'sections' => $assignments
                        ->groupBy('section_id')
                        ->map(function (Collection $sectionAssignments) {
                            $assignment = $sectionAssignments->first();
                            $section = $assignment->section;

                            return [
                                'section_id' => $section->id,
                                'section_name' => $section->name,
                                'students_count' => $section->students_count,
                                'subject' => [
                                    'name' => $assignment->subject->name,
                                ],
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();
    }
   
    public function getSectionStudents(
        Teacher $teacher,
        int $sectionId
    ): Collection {
        $this->getTeacherSectionAssignment(
            $teacher,
            $sectionId
        );

        return Student::query()
            ->whereHas('enrollments', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId)
                    ->where('status', 'active');
            })
            ->with('user')
            ->withCount('studentAlerts')
            ->orderBy('id')
            ->get();
    }

   
    public function getStudentDetails(
        Teacher $teacher,
        int $studentId
    ): Student {
        $student = Student::query()
            ->with([
                'user',
                'parent.user',
                'enrollments' => function ($query) {
                    $query->where('status', 'active')
                        ->with('section.grade');
                },
            ])
            ->findOrFail($studentId);

        $enrollment = $student->enrollments->first();

        if (! $enrollment) {
            throw ValidationException::withMessages([
                'student' => 'الطالب ليس لديه شعبة حالية.',
            ]);
        }

        $this->getTeacherSectionAssignment(
            $teacher,
            $enrollment->section_id
        );

        return $student;
    }

   
    public function createAlert(
        Teacher $teacher,
        int $studentId,
        array $data
    ): StudentAlert {
        return DB::transaction(function () use (
            $teacher,
            $studentId,
            $data
        ) {
            $student = Student::query()
                ->with([
                    'enrollments' => function ($query) {
                        $query->where('status', 'active')
                            ->with('section');
                    },
                ])
                ->findOrFail($studentId);

            $enrollment = $student->enrollments->first();

            if (! $enrollment) {
                throw ValidationException::withMessages([
                    'student' => 'الطالب ليس لديه شعبة حالية.',
                ]);
            }

            $assignment = $this->getTeacherSectionAssignment(
                $teacher,
                $enrollment->section_id
            );

            return StudentAlert::create([
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'subject_id' => $assignment->subject_id,
                'section_id' => $enrollment->section_id,
                'type' => $data['type'],
                'title' => $data['title'],
                'content' => $data['content'],
            ]);
        });
    }

    public function getParentAlerts(
        Student $student,
        ?StudentAlertType $type = null
    ): array {
        $baseQuery = StudentAlert::query()
            ->where('student_id', $student->id);

        $counts = [];

        foreach (StudentAlertType::cases() as $alertType) {
            $counts[$alertType->value] = (clone $baseQuery)
                ->where('type', $alertType->value)
                ->count();
        }

        $alerts = (clone $baseQuery)
            ->with([
                'teacher.user',
                'subject',
            ])
            ->when(
                $type,
                fn($query) => $query->where(
                    'type',
                    $type->value
                )
            )
            ->latest()
            ->get();

        return [
            'counts' => $counts,
            'alerts' => $alerts,
        ];
    }

    public function getParentAlertDetails(
        Student $student,
        int $alertId
    ): StudentAlert {
        return StudentAlert::query()
            ->where('student_id', $student->id)
            ->whereKey($alertId)
            ->with([
                'teacher.user',
                'subject',
                'section.grade',
                'student.user',
            ])
            ->firstOrFail();
    }

    private function getTeacherSectionAssignment(
        Teacher $teacher,
        int $sectionId
    ): TeacherAssignment {
        $assignments = $teacher->assignments()
            ->where('section_id', $sectionId)
            ->with('subject')
            ->get();

        if ($assignments->isEmpty()) {
            throw ValidationException::withMessages([
                'section' => 'المعلم غير مكلف بهذه الشعبة.',
            ]);
        }

        if ($assignments->count() > 1) {
            throw ValidationException::withMessages([
                'section' => 'يوجد أكثر من اختصاص مسجل لهذا المعلم في هذه الشعبة.',
            ]);
        }

        return $assignments->first();
    }
}
