<?php

namespace App\Http\Resources;

use App\Enums\TeacherAvailabilityStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherAvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->availability_status instanceof TeacherAvailabilityStatus
            ? $this->availability_status->value
            : $this->availability_status;

        return [
            'day_of_week' => $this->day_of_week,
            'period_number' => $this->period_number,
            'availability_status' => $status,
        ];
    }
}