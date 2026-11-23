<?php

namespace App\Models;

use App\Services\StorefrontLayout;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    public const LOCATIONS = [
        'header' => 'Header & Mobile Main Menu',
        'footer_quick' => 'Footer — Quick Links',
        'footer_bottom' => 'Footer — Bottom Row',
    ];

    protected $fillable = ['location', 'label', 'url', 'open_in_new_tab', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
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
