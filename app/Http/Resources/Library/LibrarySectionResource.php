<?php

namespace App\Http\Resources\Library;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LibrarySectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'grade' => [
                'id' => $this->grade->id,
                'name' => $this->grade->name,
            ],
            'students_count' => $this->students_count,
        ];
    }
}