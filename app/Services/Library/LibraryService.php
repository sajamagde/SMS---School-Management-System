<?php

namespace App\Services\Library;

use App\Enums\BookDistributionCondition;
use App\Enums\BookReturnCondition;
use App\Enums\EnrollmentStatus;
use App\Models\BookDistribution;
use App\Models\BookReturn;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LibraryService
{
    public function getClasses(int $academicYearId)
    {
        return Section::query()
            ->with('grade')
            ->whereHas('enrollments', function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId)
                    ->where('status', EnrollmentStatus::ACTIVE->value);
            })
            ->withCount([
                'enrollments as students_count' => function ($query) use ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId)
                        ->where('status', EnrollmentStatus::ACTIVE->value);
                },
            ])
            ->orderBy('grade_id')
            ->orderBy('id')
            ->get();
    }

    public function getSectionStudents(
        Section $section,
        int $academicYearId
    ) {
        return $section->enrollments()
            ->where('academic_year_id', $academicYearId)
            ->where('status', EnrollmentStatus::ACTIVE->value)
            ->with('student.user.role')
            ->get()
            ->pluck('student');
    }

    public function getDistribution(
        Section $section,
        int $academicYearId
    ) {
        $students = $section->enrollments()
            ->where('academic_year_id', $academicYearId)
            ->where('status', EnrollmentStatus::ACTIVE->value)
            ->with([
                'student.user.role',
                'student.bookDistributions' => function ($query) use ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId);
                },
            ])
            ->get()
            ->pluck('student');

        $newBooks = $students->filter(function ($student) {
            return $student->bookDistributions->first()?->condition
                === BookDistributionCondition::NEW;
        })->count();

        $usedBooks = $students->filter(function ($student) {
            return $student->bookDistributions->first()?->condition
                === BookDistributionCondition::USED;
        })->count();

        return [
            'students' => $students,
            'new_books' => $newBooks,
            'used_books' => $usedBooks,
        ];
    }
    public function saveDistribution(
        Section $section,
        int $academicYearId,
        array $students
    ): void {
        DB::transaction(function () use (
            $section,
            $academicYearId,
            $students
        ) {
            $studentIds = $this->getSectionStudentIds(
                $section,
                $academicYearId
            );

            foreach ($students as $student) {
                if (!in_array($student['student_id'], $studentIds)) {
                    throw ValidationException::withMessages([
                        'students' => [
                            'أحد الطلاب لا ينتمي إلى الشعبة المحددة في العام الدراسي المحدد.'
                        ],
                    ]);
                }

                BookDistribution::updateOrCreate(
                    [
                        'student_id' => $student['student_id'],
                        'academic_year_id' => $academicYearId,
                    ],
                    [
                        'condition' => $student['condition'],
                    ]
                );
            }
        });
    }

    public function getReturns(
        Section $section,
        int $academicYearId
    ) {
        return $section->enrollments()
            ->where('academic_year_id', $academicYearId)
            ->where('status', EnrollmentStatus::ACTIVE->value)
            ->with([
                'student.user.role',
                'student.bookReturns' => function ($query) use ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId);
                },
            ])
            ->get()
            ->pluck('student');
    }

    public function saveReturns(
        Section $section,
        int $academicYearId,
        array $students
    ): void {
        DB::transaction(function () use (
            $section,
            $academicYearId,
            $students
        ) {
            $studentIds = $this->getSectionStudentIds(
                $section,
                $academicYearId
            );

            foreach ($students as $student) {
                if (!in_array($student['student_id'], $studentIds)) {
                    throw ValidationException::withMessages([
                        'students' => [
                            'أحد الطلاب لا ينتمي إلى الشعبة المحددة في العام الدراسي المحدد.'
                        ],
                    ]);
                }

                BookReturn::updateOrCreate(
                    [
                        'student_id' => $student['student_id'],
                        'academic_year_id' => $academicYearId,
                    ],
                    [
                        'condition' => $student['condition'],
                    ]
                );
            }
        });
    }

    private function getSectionStudentIds(
        Section $section,
        int $academicYearId
    ): array {
        return $section->enrollments()
            ->where('academic_year_id', $academicYearId)
            ->where('status', EnrollmentStatus::ACTIVE->value)
            ->pluck('student_id')
            ->toArray();
    }
}
