<?php

namespace App\Policies;

use App\Models\User;

/** Shared policy base for Manager + Owner catalog/marketing resources. */
class CatalogItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageCatalog();
    }

    public function view(User $user): bool
    {
        return $user->canManageCatalog();
    }

    public function create(User $user): bool
    {
        return $user->canManageCatalog();
    }

    public function update(User $user): bool
    {
        return $user->canManageCatalog();
    }

    public function delete(User $user): bool
    {
        return $user->canManageCatalog();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageCatalog();
    }
}
