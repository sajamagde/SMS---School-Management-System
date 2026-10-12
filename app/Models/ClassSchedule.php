<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    protected $fillable = [
        'academic_year_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'term',
        'day_of_week',
        'period_number',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'term' => 'integer',
            'day_of_week' => 'integer',
            'period_number' => 'integer',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
