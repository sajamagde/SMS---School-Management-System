<?php

namespace Database\Seeders;

use App\Enums\JustificationStatus;
use App\Models\StudentAbsenceJustification;
use App\Models\StudentAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StudentAbsenceJustificationSeeder extends Seeder
{
    public function run(): void
    {
        $reviewer = User::query()->first();

        if (! $reviewer) {
            return;
        }

        $absences = StudentAttendance::query()
            ->where('status', 'absent')
            ->with('student')
            ->get();

        foreach ($absences as $absence) {

            $student = $absence->student;

            if (! $student || ! $student->parent_id) {
                continue;
            }

            /*
             * توزيع عشوائي لحالات التبرير:
             *
             * PENDING  → قيد المراجعة
             * APPROVED → مقبول
             * REJECTED → مرفوض
             */

            $status = fake()->randomElement([
                JustificationStatus::PENDING,
                JustificationStatus::PENDING,

                JustificationStatus::APPROVED,
                JustificationStatus::APPROVED,

                JustificationStatus::REJECTED,
            ]);

            /*
             * التبرير المعلق لم تتم مراجعته.
             */

            if ($status === JustificationStatus::PENDING) {

                $reviewedBy = null;
                $reviewedAt = null;
            } else {

                $reviewedBy = $reviewer->id;

                $reviewedAt = $status === JustificationStatus::APPROVED
                    ? Carbon::now()->subDay()
                    : Carbon::now()->subDays(2);
            }

            StudentAbsenceJustification::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'absence_date' => $absence->date,
                ],
                [
                    'parent_id' => $student->parent_id,

                    'reason' => match ($status) {
                        JustificationStatus::PENDING =>
                        'طلب تبرير غياب قيد المراجعة',

                        JustificationStatus::APPROVED =>
                        'غياب بسبب حالة صحية',

                        JustificationStatus::REJECTED =>
                        'السبب غير كافٍ',
                    },

                    'attachment' => null,

                    'status' => $status,

                    'reviewed_by' => $reviewedBy,

                    'reviewed_at' => $reviewedAt,
                ]
            );
        }
    }
}
