<?php

namespace App\Models;

use App\Services\StorefrontLayout;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = ['key', 'name', 'eyebrow', 'title', 'subtitle', 'settings', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => StorefrontLayout::clearCache());
        static::deleted(fn () => StorefrontLayout::clearCache());
    }
}
