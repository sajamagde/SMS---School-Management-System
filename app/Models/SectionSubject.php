<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionSubject extends Model
{
    protected $fillable = [
        'section_id',
        'subject_id',
        'weekly_periods',
    ];

    protected function casts(): array
    {
        return [
            'weekly_periods' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
