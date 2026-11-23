<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'min_order', 'max_discount', 'usage_limit', 'used', 'starts_at', 'expires_at', 'is_active'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'expires_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public static function findByCode(?string $code): ?self
    {
        return $code ? self::where('code', strtoupper(trim($code)))->first() : null;
    }

    /** Why this coupon cannot be used on a cart of $subtotal Taka, or null when it can. */
    public function problem(int $subtotal): ?string
    {
        return match (true) {
            ! $this->is_active => 'This coupon is not active.',
            $this->starts_at && $this->starts_at->isFuture() => 'This coupon is not active yet.',
            $this->expires_at && $this->expires_at->isPast() => 'This coupon has expired.',
            $this->usage_limit !== null && $this->used >= $this->usage_limit => 'This coupon has been fully used.',
            $subtotal < $this->min_order => 'This coupon needs an order of at least '.Product::taka($this->min_order).'.',
            default => null,
        };
    }

    public function discountFor(int $subtotal): int
    {
        $amount = $this->type === 'percent' ? (int) floor($subtotal * $this->value / 100) : $this->value;
        if ($this->max_discount !== null) {
            $amount = min($amount, $this->max_discount);
        }

        return min($amount, $subtotal);
    }

    public function label(): string
    {
        return $this->type === 'percent' ? $this->value.'% off' : Product::taka($this->value).' off';
    }
}
