<?php

namespace App\Services\Schedules;

use App\Enums\TeacherAvailabilityStatus;
use App\Models\Section;
use App\Models\TeacherAssignment;
use App\Models\TeacherAvailability;
use Illuminate\Support\Facades\DB;

class TeacherAvailabilityService extends BaseScheduleService
{
   public function setAvailability(int $teacherId, array $availabilities): void
{
    DB::transaction(function () use ($teacherId, $availabilities) {
        foreach ($availabilities as $slot) {
            $availability = TeacherAvailability::firstOrNew([
                'teacher_id' => $teacherId,
                'day_of_week' => $slot['day_of_week'],
                'period_number' => $slot['period_number'],
            ]);

            if (array_key_exists('availability_status', $slot)) {
                $availability->availability_status = $slot['availability_status'];
            } elseif (! $availability->exists) {
                $availability->availability_status =
                    TeacherAvailabilityStatus::AVAILABLE->value;
            }

            $availability->save();
        }
    });
}
   public function getSectionTeacherAvailabilities(int $sectionId): array
{
    Section::findOrFail($sectionId);

    $assignments = TeacherAssignment::query()
        ->where('section_id', $sectionId)
        ->with([
            'teacher.user',
            'subject',
            'teacher.availabilities',
        ])
        ->get();

    return $assignments
        ->groupBy('teacher_id')
        ->map(function ($assignments) {

            $teacher = $assignments->first()->teacher;

            return [
                'teacher_id' => $teacher->id,

                'teacher_name' => trim(
                    $teacher->user->first_name . ' ' .
                    $teacher->user->last_name
                ),

                'subjects' => $assignments
                    ->map(function ($assignment) {
                        return [
                            'subject_id' => $assignment->subject_id,
                            'subject_name' => $assignment->subject?->name ?? '',
                        ];
                    })
                    ->values(),

                'availability' => $teacher->availabilities
                    ->map(function ($availability) {
                        return [
                            'day_of_week' => $availability->day_of_week,
                            'period_number' => $availability->period_number,
                            'status' => $availability->availability_status,
                        ];
                    })
                    ->values(),
            ];
        })
        ->values()
        ->all();
}
}
