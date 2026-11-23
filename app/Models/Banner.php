<?php

namespace App\Models;

use App\Services\StorefrontLayout;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    public const SLOTS = [
        'promo' => 'Homepage Offer / Promo Banner',
        'twin_left' => 'Homepage Twin Banner — Left (Beauty)',
        'twin_right' => 'Homepage Twin Banner — Right (Gadgets)',
        'concierge' => 'Homepage Concierge Banner',
        'shop_promo' => 'Shop Page Inline Promo Banner',
    ];

    protected $fillable = [
        'slot', 'eyebrow', 'title', 'subtitle', 'cta_label', 'cta_url',
        'image', 'theme', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => StorefrontLayout::clearCache());
        static::deleted(fn () => StorefrontLayout::clearCache());
    }
}
