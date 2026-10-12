<?php

namespace App\Services\Grades;

use App\Enums\EnrollmentStatus;
use App\Models\AcademicTerm;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\StudentGrade;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupervisorGradeService
{
    public function getGradeBook(
        int $academicYearId,
        int $academicTermId,
        int $gradeId,
        int $sectionId,
        int $subjectId
    ): array {
        $this->validateGradeContext(
            academicYearId: $academicYearId,
            academicTermId: $academicTermId,
            gradeId: $gradeId,
            sectionId: $sectionId,
            subjectId: $subjectId,
        );

        $section = Section::query()
            ->where('id', $sectionId)
            ->where('grade_id', $gradeId)
            ->firstOrFail();

        $enrollments = $section->enrollments()
            ->where('academic_year_id', $academicYearId)
            ->where('grade_id', $gradeId)
            ->where('status', EnrollmentStatus::ACTIVE)
            ->with('student.user')
            ->get();

        $studentIds = $enrollments->pluck('student_id');

        $grades = StudentGrade::query()
            ->whereIn('student_id', $studentIds)
            ->where('academic_year_id', $academicYearId)
            ->where('academic_term_id', $academicTermId)
            ->where('subject_id', $subjectId)
            ->get()
            ->keyBy('student_id');

        $students = $enrollments->map(function ($enrollment) use ($grades) {
            $grade = $grades->get($enrollment->student_id);

            return [
                'student_id' => $enrollment->student_id,
                'student_name' => $enrollment->student->user->name,
                'score' => $grade?->score,

                'rating' => $grade?->score !== null
                    ? $this->getRating((float) $grade->score)
                    : null,
            ];
        });

        $scores = $students
            ->pluck('score')
            ->filter(fn($score) => $score !== null)
            ->map(fn($score) => (float) $score);

        return [
            'statistics' => [
                'average' => $scores->isNotEmpty()
                    ? round($scores->average(), 2)
                    : null,

                'highest' => $scores->isNotEmpty()
                    ? $scores->max()
                    : null,

                'lowest' => $scores->isNotEmpty()
                    ? $scores->min()
                    : null,
            ],
            'students' => $students->values()->all(),
        ];
    }

    public function saveGrades(
        int $academicYearId,
        int $academicTermId,
        int $gradeId,
        int $sectionId,
        int $subjectId,
        array $grades
    ): void {
        $this->validateGradeContext(
            academicYearId: $academicYearId,
            academicTermId: $academicTermId,
            gradeId: $gradeId,
            sectionId: $sectionId,
            subjectId: $subjectId,
        );

        $studentIds = collect($grades)
            ->pluck('student_id')
            ->unique()
            ->values();

        $validStudentIds = Section::query()
            ->where('id', $sectionId)
            ->where('grade_id', $gradeId)
            ->firstOrFail()
            ->enrollments()
            ->where('academic_year_id', $academicYearId)
            ->where('grade_id', $gradeId)
            ->where('status', EnrollmentStatus::ACTIVE)
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id');

        if ($validStudentIds->count() !== $studentIds->count()) {
            throw ValidationException::withMessages([
                'grades' => 'يوجد طالب واحد أو أكثر لا ينتمي إلى الشعبة المحددة.',
            ]);
        }

        DB::transaction(function () use (
            $grades,
            $academicYearId,
            $academicTermId,
            $subjectId
        ) {
            foreach ($grades as $grade) {
                StudentGrade::updateOrCreate(
                    [
                        'student_id' => $grade['student_id'],
                        'academic_year_id' => $academicYearId,
                        'academic_term_id' => $academicTermId,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'score' => $grade['score'],
                    ]
                );
            }
        });
    }

    private function getRating(float $score): string
    {
        return match (true) {
            $score >= 90 => 'ممتاز',
            $score >= 80 => 'جيد جدًا',
            $score >= 50 => 'جيد',
            default => 'راسب',
        };
    }

    private function validateGradeContext(
        int $academicYearId,
        int $academicTermId,
        int $gradeId,
        int $sectionId,
        int $subjectId
    ): void {
        $term = AcademicTerm::query()
            ->where('id', $academicTermId)
            ->where('academic_year_id', $academicYearId)
            ->exists();

        if (!$term) {
            throw ValidationException::withMessages([
                'academic_term_id' => 'الفصل الدراسي لا ينتمي إلى العام الدراسي المحدد.',
            ]);
        }

        $section = Section::query()
            ->where('id', $sectionId)
            ->where('grade_id', $gradeId)
            ->exists();

        if (!$section) {
            throw ValidationException::withMessages([
                'section_id' => 'الشعبة لا تنتمي إلى الصف المحدد.',
            ]);
        }

        $sectionSubjectExists = SectionSubject::query()
            ->where('section_id', $sectionId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$sectionSubjectExists) {
            throw ValidationException::withMessages([
                'subject_id' => 'المادة غير مرتبطة بهذه الشعبة.',
            ]);
        }
    }
}
