<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Section;
use App\Models\User;

class LibraryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleName::LIBRARIAN);
    }

    public function view(User $user, Section $section): bool
    {
        return $user->hasRole(RoleName::LIBRARIAN);
    }

    public function update(User $user, Section $section): bool
    {
        return $user->hasRole(RoleName::LIBRARIAN);
    }
}