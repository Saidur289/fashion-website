<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageCatalog();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->canManageCatalog();
    }

    public function create(User $user): bool
    {
        return $user->canManageCatalog();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->canManageCatalog();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->canManageCatalog();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageCatalog();
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->canManageCatalog();
    }

    public function restoreAny(User $user): bool
    {
        return $user->canManageCatalog();
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->isOwner();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->isOwner();
    }
}
