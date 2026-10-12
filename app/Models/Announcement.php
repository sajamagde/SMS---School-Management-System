<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\AnnouncementTarget;
use Illuminate\Database\Eloquent\Builder;

class Announcement extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'target_audience',
        'is_pinned',
        'expires_at',
    ];
    protected $casts = [
        'is_pinned' => 'boolean',
        'expires_at' => 'datetime',
        'target_audience' => 'array',
    ];


    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
    public function scopeForAudience(Builder $query, array $audiences): Builder
    {
        return $query->where(function ($q) use ($audiences) {
            foreach ($audiences as $audience) {
                $q->orWhereJsonContains('target_audience', $audience);
            }
        });
    }
    public function scopeForStudent(Builder $query, int $gradeId, int $sectionId): Builder
    {
        return $query
            ->where(function ($q) {
                $q->whereJsonContains('target_audience', AnnouncementTarget::ALL->value)
                    ->orWhereJsonContains('target_audience', AnnouncementTarget::STUDENTS->value);
            })
            ->where(function ($q) use ($gradeId) {
                $q->whereDoesntHave('grades')
                    ->orWhereHas('grades', fn($x) => $x->where('grades.id', $gradeId));
            })
            ->where(function ($q) use ($sectionId) {
                $q->whereDoesntHave('sections')
                    ->orWhereHas('sections', fn($x) => $x->where('sections.id', $sectionId));
            });
    }
    public function scopeForSupervisor(Builder $query, int $gradeId): Builder
    {
        return $query->where(function ($q) use ($gradeId) {
            $q->whereDoesntHave('grades')
                ->orWhereHas('grades', fn($x) => $x->where('grades.id', $gradeId));
        });
    }
    public function scopeForParent(Builder $query, array $gradeIds, array $sectionIds): Builder
    {
        return $query->where(function ($q) use ($gradeIds, $sectionIds) {

            $q->whereHas('grades', function ($x) use ($gradeIds) {
                $x->whereIn('grades.id', $gradeIds);
            })
                ->orWhereHas('sections', function ($x) use ($sectionIds) {
                    $x->whereIn('sections.id', $sectionIds);
                });
        });
    }

    public function getIsEditedAttribute()
    {
        return $this->updated_at->gt($this->created_at);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function grades()
    {
        return $this->belongsToMany(
            Grade::class,
            'announcement_grades'
        );
    }

    public function sections()
    {
        return $this->belongsToMany(
            Section::class,
            'announcement_sections'
        );
    }
}
