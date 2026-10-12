<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StudentAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();

        $recordedBy = User::query()->first();

        if (! $recordedBy) {
            return;
        }

        foreach ($students as $student) {

            for ($i = 1; $i <= 10; $i++) {

                $date = Carbon::today()->subDays($i);

                $status = fake()->randomElement([
                    AttendanceStatus::PRESENT,
                    AttendanceStatus::PRESENT,
                    AttendanceStatus::PRESENT,
                    AttendanceStatus::PRESENT,
                    AttendanceStatus::PRESENT,

                    AttendanceStatus::ABSENT,
                    AttendanceStatus::ABSENT,

                    AttendanceStatus::EXCUSED_ABSENT,
                ]);

                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'date' => $date,
                    ],
                    [
                        'status' => $status,
                        'recorded_by' => $recordedBy->id,
                    ]
                );
            }
        }
    }
}