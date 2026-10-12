<?php

namespace App\Models;

use App\Enums\TeacherAvailabilityStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAvailability extends Model
{
    protected $fillable = [
        'teacher_id',
        'day_of_week',
        'period_number',
        'availability_status',
    ];

    protected function casts(): array
    {
        return [
            'availability_status' => TeacherAvailabilityStatus::class,
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
