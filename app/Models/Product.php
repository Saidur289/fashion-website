<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'sku', 'summary', 'description', 'how_to_use', 'ingredients', 'specs',
        'origin', 'skin_type', 'volume', 'price', 'old_price', 'stock', 'sale_ends_at', 'image', 'image_hover',
        'is_new_arrival', 'is_trending', 'is_featured', 'is_best_seller', 'is_active',
        'reviews_count', 'sold_count', 'rating', 'sort_order', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'old_price' => 'integer',
            'stock' => 'integer',
            'description' => 'array',
            'how_to_use' => 'array',
            'specs' => 'array',
            'sale_ends_at' => 'datetime',
            'is_new_arrival' => 'boolean',
            'is_trending' => 'boolean',
            'is_featured' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_active' => 'boolean',
            'rating' => 'float',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ---------- relations ---------- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class)->with('attribute');
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_id')
            ->wherePivot('type', 'related')->orderByPivot('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->latest('id');
    }

    /** Recalculate the star rating and review count from approved reviews. */
    public function refreshRating(): void
    {
        $q = Review::where('product_id', $this->id)->where('is_approved', true);
        $this->forceFill([
            'reviews_count' => (clone $q)->count(),
            'rating' => (clone $q)->count() ? round((clone $q)->avg('rating'), 1) : null,
        ])->save();
    }

    public function boughtTogether(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_id')
            ->wherePivot('type', 'bought_together')->orderByPivot('sort_order');
    }

    /* ---------- scopes ---------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeInGroup(Builder $query, string $group): Builder
    {
        return $query->whereHas('category', fn (Builder $q) => $q->where('group', $group));
    }

    /* ---------- helpers ---------- */

    public function isVariable(): bool
    {
        return $this->variants->isNotEmpty();
    }

    public function minPrice(): int
    {
        return $this->isVariable() ? $this->variants->min('price') : $this->price;
    }

    public function maxPrice(): int
    {
        return $this->isVariable() ? $this->variants->max('price') : $this->price;
    }

    /** "Tk 735 – Tk 999" for variable products, else the single price. */
    public function priceLabel(): string
    {
        $min = $this->minPrice();
        $max = $this->maxPrice();

        return $min === $max ? self::taka($min) : self::taka($min).' – '.self::taka($max);
    }

    public function totalStock(): ?int
    {
        if ($this->isVariable()) {
            return $this->variants->contains(fn ($v) => $v->stock === null) ? null : (int) $this->variants->sum('stock');
        }

        return $this->stock;
    }

    public function inStock(): bool
    {
        $stock = $this->totalStock();

        return $stock === null || $stock > 0;
    }

    /** All gallery image paths: uploaded gallery first, else the card images. */
    public function gallery(): array
    {
        $paths = $this->images->pluck('path')->all();

        if (! $paths) {
            $paths = array_values(array_filter([$this->image, $this->image_hover]));
        }

        return $paths;
    }

    /**
     * Up to 5 short feature points for the buy box (Hara shows a bullet list under the rating).
     * Taken from the first bullet list in the description: "High UV Defense: Provides..." -> "High UV Defense".
     */
    public function shortFeatures(int $max = 5): array
    {
        foreach ((array) $this->description as $block) {
            if (($block[0] ?? null) !== 'ul' || empty($block[1])) {
                continue;
            }

            return collect($block[1])->map(function ($item) {
                $item = trim((string) $item);
                $colon = strpos($item, ':');
                if ($colon !== false && $colon <= 45) {
                    $label = trim(substr($item, 0, $colon));
                    // "Size: 70ml" keeps its value; a descriptive label like "High UV Defense" stands alone.
                    if (str_word_count(preg_replace('/\(.*?\)/', '', $label)) <= 1) {
                        return \Illuminate\Support\Str::limit(rtrim($item, '.'), 55);
                    }

                    return $label;
                }

                return \Illuminate\Support\Str::limit(rtrim($item, '.'), 60);
            })->filter()->take($max)->values()->all();
        }

        return [];
    }

    /** Values grouped by attribute, e.g. ['Size' => [AttributeValue, ...]] */
    public function variantOptions(): array
    {
        $groups = [];
        foreach ($this->variants as $variant) {
            foreach ($variant->values as $value) {
                $groups[$value->attribute->slug]['attribute'] = $value->attribute;
                $groups[$value->attribute->slug]['values'][$value->id] = $value;
            }
        }

        return $groups;
    }

    /** Variant data for the browser: id, price, stock, image and the value ids it needs. */
    public function variantData(): array
    {
        return $this->variants->map(fn (ProductVariant $v) => [
            'id' => $v->id,
            'label' => $v->label(),
            'sku' => $v->sku,
            'price' => $v->price,
            'old_price' => $v->old_price,
            'stock' => $v->stock,
            'image' => $v->image ? asset($v->image) : null,
            'values' => $v->values->pluck('id')->all(),
        ])->values()->all();
    }

    /** The small data set the header mini-cart, compare and "recently viewed" need in the browser. */
    public function cardData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->minPrice(),
            'image' => asset($this->image),
            'url' => route('product.show', $this),
            'variable' => $this->isVariable(),
            // Cheapest option, used when a variable product is added from "Frequently bought together".
            'default_variant' => $this->isVariable() ? (function () {
                $v = $this->variants->sortBy('price')->first();

                return ['id' => $v->id, 'label' => $v->label(), 'price' => $v->price, 'image' => $v->image ? asset($v->image) : null];
            })() : null,
        ];
    }

    /** Rounded discount percentage, or null when there is no old price. */
    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->old_price || $this->old_price <= $this->price) {
            return null;
        }

        return (int) round((1 - $this->price / $this->old_price) * 100);
    }

    public static function taka(int $amount): string
    {
        return 'Tk '.number_format($amount);
    }
}
