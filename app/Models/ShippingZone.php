<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'districts', 'charge', 'estimated_days', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'districts' => 'array',
            'charge' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /** Find the first active zone matching a district (or fallback zone with empty districts). */
    public static function forDistrict(?string $district): ?self
    {
        $zones = self::active()->get();
        if ($zones->isEmpty()) {
            return null;
        }

        if ($district) {
            $matched = $zones->first(fn (self $z) => is_array($z->districts) && in_array($district, $z->districts, true));
            if ($matched) {
                return $matched;
            }
        }

        return $zones->first(fn (self $z) => empty($z->districts));
    }
}
