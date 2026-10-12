<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Grade extends Model
{
    protected $fillable = [
        'name',
    ];
    public function supervisor(): HasOne
    {
        return $this->hasOne(Supervisor::class);
    }
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }


    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(
            Announcement::class,
            'announcement_grades'
        );
    }
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }
}
