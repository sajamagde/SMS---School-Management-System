<?php

namespace App\Models;

use App\Enums\BookDistributionCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookDistribution extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'condition',
    ];

    protected function casts(): array
    {
        return [
            'condition' => BookDistributionCondition::class,
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}