<?php

namespace App\Models;

use App\Enums\JustificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAbsenceJustification extends Model
{
    protected $fillable = [
        'student_id',
        'absence_date',
        'parent_id',
        'reason',
        'attachment',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'absence_date' => 'date',
            'status' => JustificationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            ParentModel::class,
            'parent_id'
        );
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }
}