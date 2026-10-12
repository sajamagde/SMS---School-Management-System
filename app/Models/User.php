<?php

namespace App\Models;

use App\Enums\RoleName;
use App\Enums\UserStatus;
use Dom\ParentNode;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'username',
        'code',

        'first_name',
        'last_name',

        'phone',

        'password',

        'role_id',

        'status',
    ];

    protected $hidden = [

        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [

            'password' => 'hashed',

            'status' => UserStatus::class,
        ];
    }

    public function hasRole(RoleName $role): bool
    {
        return $this->role?->name === $role->value;
    }
    public function canManageSchedules(): bool
    {
        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleName::SUPER_ADMIN);
    }

    public function isTeacher(): bool
    {
        return $this->hasRole(RoleName::TEACHER);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(RoleName::STUDENT);
    }

    public function isParent(): bool
    {
        return $this->hasRole(RoleName::PARENT);
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole(RoleName::SUPERVISOR);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }
    public function supervisor(): HasOne
    {
        return $this->hasOne(Supervisor::class);
    }
    public function parentProfile(): HasOne
    {
        return $this->hasOne(ParentModel::class);
    }
    public function librarian()
    {
        return $this->hasOne(Librarian::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }
}
