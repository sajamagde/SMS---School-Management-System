<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentGrade;
use Illuminate\Database\Seeder;

class StudentGradeSeeder extends Seeder
{
    public function run(): void
    {
        $academicYearId = 1;

        $terms = AcademicTerm::query()
            ->where('academic_year_id', $academicYearId)
            ->get();

        if ($terms->isEmpty()) {
            return;
        }

        $students = Student::query()
            ->with('enrollments')
            ->get();

        foreach ($students as $student) {

            $enrollment = $student->enrollments()
                ->where('academic_year_id', $academicYearId)
                ->first();

            if (! $enrollment) {
                continue;
            }

            $subjects = SectionSubject::query()
                ->where('section_id', $enrollment->section_id)
                ->get();

            foreach ($terms as $term) {

                foreach ($subjects as $sectionSubject) {

                    StudentGrade::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_year_id' => $academicYearId,
                            'academic_term_id' => $term->id,
                            'subject_id' => $sectionSubject->subject_id,
                        ],
                        [
                            'score' => rand(50, 100),
                        ]
                    );
                }
            }
        }
    }
}