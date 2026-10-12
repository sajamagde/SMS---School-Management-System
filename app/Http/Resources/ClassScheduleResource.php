<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $teacherUser = $this->teacher?->user;

        return [
            'id' => $this->id,
            'term' => $this->term,
            'day_of_week' => $this->day_of_week,
            'day_name' => $this->getDayName($this->day_of_week),
            'period_number' => $this->period_number,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'subject_name' => $this->subject?->name ?? '',
            'teacher_name' => $teacherUser ? trim($teacherUser->first_name . ' ' . $teacherUser->last_name) : '',
            'section_name' => $this->section?->name ?? '',
            'grade_name' => $this->section?->grade?->name ?? '',
            'students_count' => $this->section?->enrollments?->count() ?? 0,
        ];
    }
    private function getDayName(int $day): string
    {
        return match ($day) {
            0 => 'الأحد',
            1 => 'الاثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            default => '',
        };
    }
}
