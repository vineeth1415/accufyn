<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function view(?User $user, Property $property): bool
    {
        if ($property->status === 'PUBLISHED') {
            return true;
        }
        if (!$user) {
            return false;
        }
        return $user->isStaff() || $user->id === $property->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isSeller() || $user->isStaff();
    }

    public function update(User $user, Property $property): bool
    {
        return $user->isStaff() || $user->id === $property->user_id;
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->isStaff() || $user->id === $property->user_id;
    }

    public function verify(User $user, Property $property): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }
}
