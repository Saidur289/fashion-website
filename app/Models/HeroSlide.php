<?php

namespace App\Models;

use App\Services\StorefrontLayout;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    public const STYLES = [
        'split' => 'Hara 3-Zone Centered (Left Model + Right Products)',
        'arch' => 'Hara 2-Zone Arch + Blurred Leaves',
        'rings' => 'Hara 2-Zone Pedestal + Floating Leaves',
    ];

    protected $fillable = [
        'eyebrow', 'title', 'description', 'cta_label', 'cta_url',
        'bg_image', 'image_primary', 'image_secondary', 'layout_style',
        'accent_color', 'sort_order', 'is_active',
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
