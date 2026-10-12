<?php

namespace App\Http\Resources\Alerts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherStudentDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $enrollment = $this->enrollments->first();

        return [
            'student' => [
                'id' => $this->id,
                'name' => $this->user->name,
                'code' => $this->user->code,
                'birth_date' => $this->birth_date?->format('Y-m-d'),
                'status' => $this->user->status,
                'mother_name' => $this->mother_name,
                'address' => $this->address,

                'grade' => [
                    'id' => $enrollment?->section?->grade?->id,
                    'name' => $enrollment?->section?->grade?->name,
                ],

                'section' => [
                    'id' => $enrollment?->section?->id,
                    'name' => $enrollment?->section?->name,
                ],
            ],

            'parent' => [
                'name' => trim(
                    ($this->parent?->user?->first_name ?? '') . ' ' .
                        ($this->parent?->user?->last_name ?? '')
                ),
                'phone' => $this->parent?->user?->phone,
            ],
        ];
    }
}
