<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'content'       => $this->content,
           'target_audience' => $this->target_audience,
            'isPinned'      => (bool) $this->is_pinned,

            'publishedAt'   => $this->created_at->diffForHumans(),
            'expiresAt'     => $this->expires_at ? $this->expires_at->toDateTimeString() : null,
            'is_edited'        => $this->updated_at->gt($this->created_at),
            'publisher'     => [
                'id'    => $this->user->id,
                'name'  => $this->user->first_name . ' ' . $this->user->last_name,
                'role'  => $this->user->role->name,
            ],

            'grades'        => $this->whenLoaded('grades', function () {
                return $this->grades->map(fn($grade) => [
                    'id'   => $grade->id,
                    'name' => $grade->name
                ]);
            }),
            'sections'      => $this->whenLoaded('sections', function () {
                return $this->sections->map(fn($section) => [
                    'id'   => $section->id,
                    'name' => $section->name
                ]);
            }),
        ];
    }
}
