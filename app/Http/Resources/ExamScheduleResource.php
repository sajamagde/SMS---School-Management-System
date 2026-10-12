<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'term' => $this->term,
            'exam_date' => $this->exam_date?->format('Y-m-d') ?? $this->exam_date,
            'day_name' => $this->exam_date?->locale('ar')->translatedFormat('l') ?? '',
            'start_time' => $this->start_time ? substr($this->start_time, 0, 5) : '',
            'end_time' => $this->end_time ? substr($this->end_time, 0, 5) : '',
            'subject' => $this->subject?->name ?? '',
            'grade' => $this->grade?->name ?? '',
        ];
    }

}
