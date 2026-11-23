<?php

namespace App\Models;

use App\Services\StorefrontLayout;
use Illuminate\Database\Eloquent\Model;

class TrustBadge extends Model
{
    public const LOCATIONS = [
        'product' => 'Product Page Trust Strip',
        'home_why' => 'Homepage — Why Shop with SafeBuy',
    ];

    public const ICONS = [
        'shield' => 'Shield (Authentic)',
        'truck' => 'Truck (Delivery)',
        'card' => 'Card (Payment / COD)',
        'globe' => 'Globe (Global Sourcing)',
        'barcode' => 'Barcode (Verified)',
        'headset' => 'Headset (Support)',
        'box' => 'Box (Safe Packaging)',
        'check' => 'Checkmark',
    ];

    protected $fillable = ['location', 'icon', 'title', 'subtitle', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => StorefrontLayout::clearCache());
        static::deleted(fn () => StorefrontLayout::clearCache());
    }
}
