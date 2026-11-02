<?php

namespace App\Policies;

use App\Models\Requirement;
use App\Models\User;

class RequirementPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function view(User $user, Requirement $requirement): bool
    {
        return $user->isStaff() || $user->id === $requirement->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isBuyer() || $user->isStaff();
    }

    public function update(User $user, Requirement $requirement): bool
    {
        return $user->isStaff() || $user->id === $requirement->user_id;
    }

    public function delete(User $user, Requirement $requirement): bool
    {
        return $user->isStaff() || $user->id === $requirement->user_id;
    }
}
