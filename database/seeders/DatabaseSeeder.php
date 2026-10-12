<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\TeacherSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([

            RoleSeeder::class,
            AcademicYearSeeder::class,
            GradeSeeder::class,
            SectionSeeder::class,
            SubjectSeeder::class,
            AdminSeeder::class,
            TeacherSeeder::class,
            ParentSeeder::class,
            StudentSeeder::class,
            SupervisorSeeder::class,
            LibrarianSeeder::class,
            AnnouncementSeeder::class,
            SectionSubjectSeeder::class,
            ClassScheduleSeeder::class,
            ExamScheduleSeeder::class,
            StudentAttendanceSeeder::class,
            StudentAbsenceJustificationSeeder::class,
            StudentGradeSeeder::class,
            TeacherAttendanceSeeder::class,
        ]);
    }
}
