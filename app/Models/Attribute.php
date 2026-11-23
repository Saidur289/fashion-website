<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A product attribute such as Color or Size. */
class Attribute extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'sort_order'];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }
}
