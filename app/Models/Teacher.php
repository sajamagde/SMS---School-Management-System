<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_assignments')
            ->withPivot('section_id')
            ->withTimestamps();
    }


    public function availabilities(): HasMany
    {
        return $this->hasMany(TeacherAvailability::class);
    }
    public function studentAlerts(): HasMany
    {
        return $this->hasMany(StudentAlert::class);
    }
    public function attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }
}
