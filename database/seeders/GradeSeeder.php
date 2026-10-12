<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gardes = [
            [
                'id' => 1,
                'name' => 'صف سابع'
            ],

            [
                'id' => 2,
                'name' => 'صف ثامن'
            ],

            [
                'id' => 3,
                'name' => 'صف تاسع'
            ],

        ];
        foreach ($gardes as $grade) {
            Grade::create($grade);
        }
    }
}
