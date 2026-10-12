<?php

namespace App\Http\Resources\Alerts;

use App\Enums\StudentAlertType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentStudentAlertDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type->value,
            'type_name' => match ($this->type) {
                StudentAlertType::GENERAL => 'إشعار عام',
                StudentAlertType::BEHAVIORAL_COMPLAINT => 'شكوى سلوكية',
                StudentAlertType::ABSENCE_WARNING => 'تحذير غياب'
            },
            'sender' => [
                'name' => trim(
                    ($this->teacher?->user?->first_name ?? '') . ' ' .
                        ($this->teacher?->user?->last_name ?? '')
                ),
                'subject' => $this->subject->name,

                'grade' => $this->section->grade->name,
                'section' => $this->section->name,

                'sent_date' => $this->created_at->format('Y-m-d'),
                'sent_time' => $this->created_at->format('h:i A'),
            ],

            'student' => [
                'name' => trim(
                    ($this->student?->user?->first_name ?? '') . ' ' .
                        ($this->student?->user?->last_name ?? '')
                ),
                'code' => $this->student->user->code,
            ],

            'content' => $this->content,
        ];
    }
}
