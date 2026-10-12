<?php

namespace App\Services\Profiles;

use App\Enums\EnrollmentStatus;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function getSupervisorProfile(User $user): array
    {
        $supervisor = $user->supervisor;

        if (!$supervisor) {
            throw ValidationException::withMessages([
                'user' => 'بيانات المشرف غير موجودة.',
            ]);
        }

        $supervisor->load([
            'grade.sections',
        ]);

        $sections = $supervisor->grade?->sections ?? collect();

        $studentsCount = \App\Models\StudentEnrollment::query()
            ->whereIn('section_id', $sections->pluck('id')->all(), 'and', false)
            ->count('id');

        return [
            'full_name' => $this->getFullName($user),
            'code' => $user->code,
            'job' => 'مشرف',
            'phone' => $user->phone,

            'supervised_grade' => $this->getGradeNumber(
                $supervisor->grade?->name
            ),

            'sections_count' => $sections->count(),

            'students_count' => $studentsCount,

            'sections' => $sections
                ->map(fn($section) => [
                    'id' => $section->id,
                    'name' => $section->name,
                ])
                ->values()
                ->all(),
        ];
    }
    public function getTeacherProfile(User $user): array
    {
        $teacher = $user->teacher;

        if (!$teacher) {
            throw ValidationException::withMessages([
                'user' => 'بيانات المعلم غير موجودة.',
            ]);
        }

        $teacher->load([
            'assignments.subject',
            'assignments.section.grade',
        ]);

        $teachingInformation = $teacher->assignments
            ->groupBy(function ($assignment) {
                return $assignment->subject_id
                    . '-' .
                    $assignment->section->grade_id;
            })
            ->map(function ($assignments) {
                $firstAssignment = $assignments->first();

                return [
                    'subject' => $firstAssignment->subject->name,

                    'grade' => $firstAssignment->section->grade->name,

                    'sections' => $assignments
                        ->map(fn($assignment) => [
                            'id' => $assignment->section->id,
                            'name' => $assignment->section->name,
                        ])
                        ->unique('id')
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        return [
            'full_name' => $this->getFullName($user),
            'code' => $user->code,
            'job' => 'معلم',
            'phone' => $user->phone,

            'teaching_information' => $teachingInformation,
        ];
    }

    public function getStudentProfile(User $user): array
    {
        $student = $user->student;

        if (!$student) {
            throw ValidationException::withMessages([
                'user' => 'بيانات الطالب غير موجودة.',
            ]);
        }

        $student->load([
            'parent.user',
        ]);

        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        $enrollment = null;

        if ($academicYear) {
            $enrollment = $student->enrollments()
                ->where('academic_year_id', $academicYear->id)
                ->where('status', EnrollmentStatus::ACTIVE)
                ->with([
                    'grade',
                    'section',
                ])
                ->first();
        }

        return [
            'full_name' => $this->getFullName($user),
            'code' => $user->code,
            'job' => 'طالب',

            'personal_information' => [
                'phone' => $user->phone,
                'birth_date' => $student->birth_date?->format('Y-m-d'),
                'mother_name' => $student->mother_name,
                'address' => $student->address,
                'grade' => $enrollment?->grade?->name,
                'section' => $enrollment?->section?->name,
            ],

            'parent' => $student->parent ? [
                'full_name' => $this->getFullName(
                    $student->parent->user
                ),
                'phone' => $student->parent->user->phone,
            ] : null,
        ];
    }

    public function getParentProfile(
        User $user,
        int $studentId
    ): array {
        $parent = $user->parentProfile;

        if (!$parent) {
            throw ValidationException::withMessages([
                'user' => 'بيانات ولي الأمر غير موجودة.',
            ]);
        }

        $student = $parent->students()
            ->where('id', $studentId)
            ->with('user')
            ->first();

        if (!$student) {
            throw ValidationException::withMessages([
                'student_id' => 'الطالب غير موجود أو غير تابع لولي الأمر.',
            ]);
        }

        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        $enrollment = null;

        if ($academicYear) {
            $enrollment = $student->enrollments()
                ->where('academic_year_id', $academicYear->id)
                ->where('status', EnrollmentStatus::ACTIVE)
                ->with([
                    'grade',
                    'section',
                ])
                ->first();
        }

        return [
            'full_name' => $this->getFullName($user),
            'code' => $user->code,
            'job' => 'ولي أمر',
            'phone' => $user->phone,
            'address' => $parent->address,

            'student' => [
                'full_name' => $this->getFullName($student->user),
                'code' => $student->user->code,
                'grade' => $enrollment?->grade?->name,
                'section' => $enrollment?->section?->name,
            ],
        ];
    }
    public function getLibrarianProfile(User $user): array
    {
        $librarian = $user->librarian;

        if (!$librarian) {
            throw ValidationException::withMessages([
                'user' => 'بيانات أمين المكتبة غير موجودة.',
            ]);
        }

        return [
            'full_name' => $this->getFullName($user),
            'code' => $user->code,
            'job' => 'أمين مكتبة',
            'phone' => $user->phone,
        ];
    }

    private function getFullName(User $user): string
    {
        return trim(
            $user->first_name . ' ' . $user->last_name
        );
    }
    private function getGradeNumber(?string $gradeName): ?int
    {
        if (!$gradeName) {
            return null;
        }

        $gradeName = trim($gradeName);

        return match (true) {

            str_contains($gradeName, 'سابع') => 7,
            str_contains($gradeName, 'ثامن') => 8,
            str_contains($gradeName, 'تاسع') => 9,
            default => null,
        };
    }
}
