<?php

namespace Database\Seeders;

use App\Models\SectionSubject;
use Illuminate\Database\Seeder;

class SectionSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            1  => 4, // Physics
            2  => 2, // Chemistry
            3  => 9, // Arabic
            4  => 1, // History
            5  => 1, // Geography
            6  => 5, // Mathematics
            7  => 3, // English
            8  => 4, // French
            9  => 3, // Religion
            10 => 1, // Biology
            11 => 1, // Physical Education
            12 => 1, // Art
        ];

        $sectionIds = range(1, 9);

        foreach ($sectionIds as $sectionId) {
            foreach ($subjects as $subjectId => $weeklyPeriods) {
                SectionSubject::create([
                    'section_id' => $sectionId,
                    'subject_id' => $subjectId,
                    'weekly_periods' => $weeklyPeriods,
                ]);
            }
        }
    }
}