<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Models\Supervisor;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TeacherAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = Teacher::query()
            ->with([
                'assignments.section',
            ])
            ->get();

        $supervisors = Supervisor::query()->get();

        if ($supervisors->isEmpty()) {
            return;
        }

        foreach ($teachers as $teacher) {

            /*
            |--------------------------------------------------------------------------
            | المشرفون المسؤولون عن الصفوف التي يدرّس فيها المعلم
            |--------------------------------------------------------------------------
            */

            $teacherGradeIds = $teacher->assignments
                ->pluck('section.grade_id')
                ->unique();

            $eligibleSupervisors = $supervisors
                ->whereIn('grade_id', $teacherGradeIds);

            /*
            |--------------------------------------------------------------------------
            | إذا لم يوجد مشرف مناسب لهذا المعلم نتجاوزه
            |--------------------------------------------------------------------------
            */

            if ($eligibleSupervisors->isEmpty()) {
                continue;
            }

            for ($i = 1; $i <= 20; $i++) {

                $date = Carbon::today()
                    ->subDays($i);

                /*
                |--------------------------------------------------------------------------
                | اختيار مشرف عشوائي من المشرفين المسؤولين عن صفوف المعلم
                |--------------------------------------------------------------------------
                */

                $recordedBy = $eligibleSupervisors->random();

                /*
                |--------------------------------------------------------------------------
                | توزيع عشوائي للحالة
                |--------------------------------------------------------------------------
                */

                $status = fake()->randomElement([
                    AttendanceStatus::PRESENT,
                    AttendanceStatus::PRESENT,
                    AttendanceStatus::PRESENT,
                    AttendanceStatus::ABSENT,
                    AttendanceStatus::EXCUSED_ABSENT,
                ]);

                TeacherAttendance::updateOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'date' => $date,
                    ],
                    [
                        'status' => $status,
                        'recorded_by' => $recordedBy->user_id,
                    ]
                );
            }
        }
    }
}