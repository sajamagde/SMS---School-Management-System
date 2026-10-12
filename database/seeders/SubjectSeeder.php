<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'id' => 1,
                'name' => 'فيزيا',
                'has_exam' => true,
            ],
            [
                'id' => 2,
                'name' => 'كيميا',
                'has_exam' => true,
            ],
            [
                'id' => 3,
                'name' => 'لغة عربية',
                'has_exam' => true,
            ],
            [
                'id' => 4,
                'name' => 'تاريخ',
                'has_exam' => true,
            ],
            [
                'id' => 5,
                'name' => 'جغرافيا',
                'has_exam' => true,
            ],
            [
                'id' => 6,
                'name' => 'رياضيات',
                'has_exam' => true,
            ],
            [
                'id' => 7,
                'name' => 'انجليزي',
                'has_exam' => true,
            ],
            [
                'id' => 8,
                'name' => 'فرنسي',
                'has_exam' => true,
            ],
            [
                'id' => 9,
                'name' => 'ديانة',
                'has_exam' => true,
            ],
            [
                'id' => 10,
                'name' => 'احياء',
                'has_exam' => true,
            ],
            [
                'id' => 11,
                'name' => 'رياضة',
                'has_exam' => false,
            ],
            [
                'id' => 12,
                'name' => 'فنية',
                'has_exam' => false,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['id' => $subject['id']],
                [
                    'name' => $subject['name'],
                    'has_exam' => $subject['has_exam'],
                ]
            );
        }
    }
}