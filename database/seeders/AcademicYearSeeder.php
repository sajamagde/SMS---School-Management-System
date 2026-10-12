<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::updateOrCreate(
            [
                'name' => '2025-2026',
            ],
            [
                'start_date' => '2025-09-01',
                'end_date' => '2026-08-31',
                'is_active' => true,
            ]
        );

        $academicYear->terms()->updateOrCreate(
            [
                'term_number' => 1,
            ],
            [
                'name' => 'الفصل الأول',
                'start_date' => '2025-09-01',
                'end_date' => '2026-01-15',
            ]
        );

        $academicYear->terms()->updateOrCreate(
            [
                'term_number' => 2,
            ],
            [
                'name' => 'الفصل الثاني',
                'start_date' => '2026-01-16',
                'end_date' => '2026-08-31',
            ]
        );
    }
}