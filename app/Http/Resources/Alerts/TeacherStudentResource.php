<?php

namespace App\Http\Resources\Alerts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherStudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_id' => $this->id,
             'name' => trim(
                    ($this->student?->user?->first_name ?? '') . ' ' .
                        ($this->student?->user?->last_name ?? '')
                ),
            'code' => $this->user->code,
            'alerts_count' => $this->student_alerts_count,
        ];
    }
}