<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public const GROUP_BEAUTY = 'beauty';
    public const GROUP_GADGET = 'gadget';

    protected $fillable = ['name', 'slug', 'group', 'image', 'sort_order'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
