<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'name',
        'grade_id',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
        ];
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'section_id');
    }
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'section_id');
    }
    public function subjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class);
    }

    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(
            Announcement::class,
            'announcement_sections'
        );
    }
}
