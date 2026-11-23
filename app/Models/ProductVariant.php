<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'price', 'old_price', 'stock', 'image', 'sort_order'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'old_price' => 'integer', 'stock' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class)->with('attribute');
    }

    /** e.g. "150ml" or "Pink / 50ml" */
    public function label(): string
    {
        return $this->values->pluck('value')->implode(' / ');
    }

    public function inStock(): bool
    {
        return $this->stock === null || $this->stock > 0;
    }
}
