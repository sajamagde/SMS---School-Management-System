<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            //grade 7
            [
                'id' => 1,
                'grade_id' => 1,
                'name' => 'أ'
            ],

            [
                'id' => 2,
                'grade_id' => 1,
                'name' => 'ب'
            ],

            [
                'id' => 3,
                'grade_id' => 1,
                'name' => 'ت'
            ],

            //grade 8
            [
                'id' => 4,
                'grade_id' => 2,
                'name' => 'أ'
            ],

            [
                'id' => 5,
                'grade_id' => 2,
                'name' => 'ب'
            ],

            [
                'id' => 6,
                'grade_id' => 2,
                'name' => 'ت'
            ],

            //grade 9
            [
                'id' => 7,
                'grade_id' => 3,
                'name' => 'أ'
            ],

            [
                'id' => 8,
                'grade_id' => 3,
                'name' => 'ب'
            ],

            [
                'id' => 9,
                'grade_id' => 3,
                'name' => 'ت'
            ],

        ];
        foreach ($sections as $section) {
            Section::create($section);
        }
    }
}
