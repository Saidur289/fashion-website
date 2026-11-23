<?php

namespace App\Models;

use App\Services\StorefrontLayout;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['name', 'location', 'quote', 'photo', 'rating', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
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
