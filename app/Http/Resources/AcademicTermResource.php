<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicTermResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'term_number' => $this->term_number,
            'name' => $this->name,

            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),

            'is_current' =>
                $this->start_date &&
                $this->end_date &&
                today()->between(
                    $this->start_date,
                    $this->end_date
                ),
        ];
    }
}