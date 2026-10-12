<?php

namespace App\Policies;

use App\Models\User;

class ClassSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewTeacherSchedule(User $user): bool
    {
        return $user->isTeacher();
    }

    public function viewStudentSchedule(User $user): bool
    {
        return $user->isStudent();
    }

    public function viewParentSchedule(User $user): bool
    {
        return $user->isParent();
    }
}
