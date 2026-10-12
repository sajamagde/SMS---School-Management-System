<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function examSchedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class);
    }
    public function terms(): HasMany
    {
        return $this->hasMany(AcademicTerm::class);
    }
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }
    public function studentGrades(): HasMany
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
