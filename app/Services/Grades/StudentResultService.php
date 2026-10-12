<?php

namespace App\Services\Grades;

use App\Enums\EnrollmentStatus;
use App\Models\AcademicTerm;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentGrade;
use Illuminate\Validation\ValidationException;

class StudentResultService
{
    public function getResults(
        Student $student,
        int $academicYearId,
        int $academicTermId
    ): array {
        $term = AcademicTerm::query()
            ->where('id', $academicTermId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        if (!$term) {
            throw ValidationException::withMessages([
                'academic_term_id' => 'الفصل الدراسي لا ينتمي إلى العام الدراسي المحدد.',
            ]);
        }

        $enrollment = $student->enrollments()
            ->where('academic_year_id', $academicYearId)
            ->where('status', EnrollmentStatus::ACTIVE)
            ->with([
                'academicYear',
                'grade',
                'section.subjects.subject',
            ])
            ->first();

        if (!$enrollment) {
            throw ValidationException::withMessages([
                'academic_year_id' => 'الطالب غير مسجل في هذا العام الدراسي.',
            ]);
        }

        $grades = StudentGrade::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->where('academic_term_id', $academicTermId)
            ->get()
            ->keyBy('subject_id');

        $subjects = $enrollment->section
            ->subjects
            ->map(function ($sectionSubject) use ($grades) {
                $grade = $grades->get($sectionSubject->subject_id);

                return [
                    'subject_name' => $sectionSubject->subject->name,
                    'score' => $grade?->score,

                    'rating' => $grade?->score !== null
                        ? $this->getRating((float) $grade->score)
                        : null,
                ];
            })
            ->values();

        $scores = $subjects
            ->pluck('score')
            ->filter(fn($score) => is_numeric($score))
            ->map(fn($score) => (float) $score);
        $average = $scores->isNotEmpty()
            ? round($scores->average(), 2)
            : null;

        return [
            'academic_year' => $enrollment->academicYear->name,
            'term' => $term->name,

            'grade' => $enrollment->grade->name,

            'section' => $enrollment->section->name,

            'average' => $average,

            'rating' => $average !== null
                ? $this->getRating((float) $average)
                : null,
            'rank' => $this->calculateRank(
                student: $student,
                academicYearId: $academicYearId,
                academicTermId: $academicTermId,
                gradeId: $enrollment->grade_id,
            ),

            'subjects' => $subjects->all(),
        ];
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
    private function calculateRank(
        Student $student,
        int $academicYearId,
        int $academicTermId,
        int $gradeId
    ): ?int {
        $enrollments = StudentEnrollment::query()
            ->where('academic_year_id', $academicYearId)
            ->where('grade_id', $gradeId)
            ->where('status', EnrollmentStatus::ACTIVE)
            ->with('section.subjects')
            ->get();

        $studentIds = $enrollments->pluck('student_id');

        $grades = StudentGrade::query()
            ->whereIn('student_id', $studentIds)
            ->where('academic_year_id', $academicYearId)
            ->where('academic_term_id', $academicTermId)
            ->whereNotNull('score')
            ->get()
            ->groupBy('student_id');

        $totals = [];

        foreach ($enrollments as $enrollment) {
            $subjectIds = $enrollment->section
                ->subjects
                ->pluck('subject_id');

            $scores = $grades
                ->get($enrollment->student_id, collect())
                ->whereIn('subject_id', $subjectIds)
                ->pluck('score');

            if ($scores->isNotEmpty()) {
                $totals[$enrollment->student_id] = $scores->sum(
                    fn($score) => (float) $score
                );
            }
        }

        if (!array_key_exists($student->id, $totals)) {
            return null;
        }

        $studentTotal = $totals[$student->id];

        return collect($totals)
            ->filter(fn($total) => $total > $studentTotal)
            ->unique()
            ->count() + 1;
    }
}
