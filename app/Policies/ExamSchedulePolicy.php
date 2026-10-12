<?php

namespace App\Policies;

use App\Models\User;

class ExamSchedulePolicy
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

    public function viewStudentExams(User $user): bool
    {
        return $user->isStudent();
    }

    public function viewParentExams(User $user): bool
    {
        return $user->isParent();
    }
}
