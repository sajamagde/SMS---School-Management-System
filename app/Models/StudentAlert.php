<?php

namespace App\Models;

use App\Enums\StudentAlertType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAlert extends Model
{
    protected $fillable = [
        'student_id',
        'teacher_id',
        'subject_id',
        'section_id',
        'type',
        'title',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'type' => StudentAlertType::class,
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}