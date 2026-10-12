<?php

namespace App\Http\Resources\Grades;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'academic_year' => $this['academic_year'],
            'term' => $this['term'],

            'grade' => $this['grade'],
            'section' => $this['section'],

            'average' => $this['average'] ?? '-',
            'rating' => $this['rating'] ?? '-',
            'rank' => $this['rank'] ?? '-',

            'subjects' => collect($this['subjects'])
                ->map(function ($subject) {
                    return [
                        'subject_name' => $subject['subject_name'],
                        'score' => $subject['score'] ?? '-',
                        'rating' => $subject['rating'] ?? '-',
                    ];
                }),
        ];
    }
}
