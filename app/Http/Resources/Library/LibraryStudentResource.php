<?php

namespace App\Http\Resources\Library;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LibraryStudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => trim(
                $this->user->first_name . ' ' . $this->user->last_name
            ),
            'code' => $this->user->code,
            'role' => match ($this->user->role?->name) {
                'student' => 'طالب',
                default => $this->user->role?->name,
            },
        ];
    }
}
