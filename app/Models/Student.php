<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [

        'user_id',

        'mother_name',

        'birth_date',

        'address',

        'parent_id',
    ];
    protected function casts(): array
    {
        return [

            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class);
    }
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'student_id');
    }
    public function studentAlerts(): HasMany
    {
        return $this->hasMany(StudentAlert::class);
    }
    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }
    public function absenceJustifications(): HasMany
    {
        return $this->hasMany(
            StudentAbsenceJustification::class
        );
    }
    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class);
    }

    public function bookDistributions(): HasMany
    {
        return $this->hasMany(BookDistribution::class);
    }

    public function bookReturns(): HasMany
    {
        return $this->hasMany(BookReturn::class);
    }
}
