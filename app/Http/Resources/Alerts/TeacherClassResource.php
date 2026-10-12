<?php

namespace App\Http\Resources\Alerts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'grade_id' => $this['grade_id'],
            'grade_name' => $this['grade_name'],

            'sections' => TeacherSectionResource::collection(
                collect($this['sections'])
            ),
        ];
    }
}