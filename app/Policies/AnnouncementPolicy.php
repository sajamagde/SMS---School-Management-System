<?php
namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Announcement$announcement): bool { return true; }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    public function update(User $user, Announcement$announcement): bool
    {
        return $this->canManage($user,$announcement);
    }

    public function delete(User $user, Announcement$announcement): bool
    {
        return $this->canManage($user,$announcement);
    }

    public function togglePin(User $user, Announcement$announcement): bool
    {
        return $this->canManage($user,$announcement);
    }

    private function canManage(User $user, Announcement$announcement): bool
    {
        return $user->isAdmin() || $announcement->user_id ===$user->id;
    }
}