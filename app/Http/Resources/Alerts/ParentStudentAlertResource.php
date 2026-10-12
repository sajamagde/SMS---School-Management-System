<?php

namespace App\Http\Resources\Alerts;

use App\Enums\StudentAlertType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentStudentAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => trim(
                ($this->teacher?->user?->first_name ?? '') . ' ' .
                    ($this->teacher?->user?->last_name ?? '')
            ),
            'subject_name' => $this->subject->name,
            'type' => $this->type->value,
            'type_name' => match ($this->type) {
                StudentAlertType::GENERAL => 'إشعار عام',
                StudentAlertType::BEHAVIORAL_COMPLAINT => 'شكوى سلوكية',
                StudentAlertType::ABSENCE_WARNING => 'تحذير غياب'
            },
            'title' => $this->title,
            'content' => $this->content,
            'sent_at' => $this->created_at->diffForHumans(),
        ];
    }
}
