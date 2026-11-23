<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = [
        'customer' => 'Customer',
        'staff' => 'Staff (Orders only)',
        'manager' => 'Manager (Orders, Products & Content)',
        'owner' => 'Owner (Full Access)',
    ];

    protected $fillable = ['name', 'email', 'phone', 'password'];

    protected $hidden = ['password', 'remember_token', 'saved_cart'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'saved_cart' => 'array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() && ! $this->is_blocked;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest('id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class)->orderByDesc('is_default')->latest('id');
    }

    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class)->latest('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['staff', 'manager', 'owner'], true);
    }

    public function canManageCatalog(): bool
    {
        return in_array($this->role, ['manager', 'owner'], true);
    }

    public function canManageOrders(): bool
    {
        return $this->isAdmin();
    }

    public function canManageSettings(): bool
    {
        return $this->isOwner();
    }

    /** Has this customer received this product in a delivered order? (Only buyers can review.) */
    public function deliveredOrderWith(int $productId): ?Order
    {
        return $this->orders()->where('status', 'delivered')
            ->whereHas('items', fn ($q) => $q->where('product_id', $productId))
            ->first();
    }
}
