<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageOrders();
    }

    public function view(User $user, Order $order): bool
    {
        return $user->canManageOrders();
    }

    public function create(User $user): bool
    {
        return $user->canManageOrders();
    }

    public function update(User $user, Order $order): bool
    {
        return $user->canManageOrders();
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->isOwner();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isOwner();
    }
}
