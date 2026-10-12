<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionTeacherAvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $teacherUser = $this->teacher?->user;

        return [
            'teacher_id' => $this->teacher_id,
            'teacher_name' => $teacherUser
                ? trim($teacherUser->first_name.' '.$teacherUser->last_name)
                : 'معلم غير معروف',

            'subject_id' => $this->subject_id,
            'subject_name' => $this->subject?->name ?? '',

            'availabilities' => TeacherAvailabilityResource::collection(
                $this->teacher?->availabilities ?? collect()
            ),
        ];
    }
}
