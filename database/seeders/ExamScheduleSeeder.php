<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ExamSchedule;
use Illuminate\Database\Seeder;
use RuntimeException;

class ExamScheduleSeeder extends Seeder
{
    private const TERM = 1;

    public function run(): void
    {
        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            throw new RuntimeException(
                'لا توجد سنة دراسية نشطة.'
            );
        }

        ExamSchedule::query()
            ->where('academic_year_id', $academicYear->id)
            ->where('term', self::TERM)
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Grade 1
        |--------------------------------------------------------------------------
        */

        $this->createExam(
            $academicYear->id,
            1,
            3, // Arabic
            '2026-06-01',
            '09:00',
            '11:00'
        );

        $this->createExam(
            $academicYear->id,
            1,
            6, // Mathematics
            '2026-06-03',
            '09:00',
            '11:00'
        );

        $this->createExam(
            $academicYear->id,
            1,
            7, // English
            '2026-06-05',
            '09:00',
            '11:00'
        );

        $this->createExam(
            $academicYear->id,
            1,
            1, // Physics
            '2026-06-07',
            '09:00',
            '11:00'
        );

        /*
        |--------------------------------------------------------------------------
        | Grade 2
        |--------------------------------------------------------------------------
        */

        $this->createExam(
            $academicYear->id,
            2,
            3,
            '2026-06-01',
            '09:00',
            '11:00'
        );

        $this->createExam(
            $academicYear->id,
            2,
            6,
            '2026-06-03',
            '09:00',
            '11:00'
        );

        $this->createExam(
            $academicYear->id,
            2,
            7,
            '2026-06-05',
            '09:00',
            '11:00'
        );

        $this->createExam(
            $academicYear->id,
            2,
            2, // Chemistry
            '2026-06-07',
            '09:00',
            '11:00'
        );

        /*
        |--------------------------------------------------------------------------
        | Grade 3
        |--------------------------------------------------------------------------
        */

        $this->createExam(
            $academicYear->id,
            3,
            3,
            '2026-06-01',
            '09:00',
            '11:00'
        );

        $this->createExam(
            $academicYear->id,
            3,
            6,
            '2026-06-03',
            '09:00',
            '11:00'
        );

        $this->createExam(
            $academicYear->id,
            3,
            7,
            '2026-06-05',
            '09:00',
            '11:00'
        );

        $this->createExam(
            $academicYear->id,
            3,
            10, // Biology
            '2026-06-07',
            '09:00',
            '11:00'
        );
    }

    private function createExam(
        int $academicYearId,
        int $gradeId,
        int $subjectId,
        string $date,
        string $startTime,
        string $endTime
    ): void {
        ExamSchedule::create([
            'academic_year_id' => $academicYearId,
            'grade_id' => $gradeId,
            'subject_id' => $subjectId,
            'term' => self::TERM,
            'exam_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }
}