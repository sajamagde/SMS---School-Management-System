<?php

namespace App\Http\Resources\Alerts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'section_id' => $this['section_id'],
            'section_name' => $this['section_name'],
            'students_count' => $this['students_count'],
            'subject_name' => $this['subject']['name'],
        ];
    }
}