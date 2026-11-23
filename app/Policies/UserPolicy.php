<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageCatalog();
    }

    public function view(User $user, User $target): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        return $user->isManager() && $target->role === 'customer';
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, User $target): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Managers may view/block customers, never edit admin accounts.
        return $user->isManager() && $target->role === 'customer';
    }

    public function delete(User $user, User $target): bool
    {
        if (! $user->isOwner() || $user->id === $target->id) {
            return false;
        }

        if ($target->isOwner() && User::where('role', 'owner')->count() <= 1) {
            return false;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
