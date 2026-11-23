<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $fillable = ['product_id', 'user_id', 'order_id', 'rating', 'body', 'is_approved', 'reply'];

    protected function casts(): array
    {
        return ['is_approved' => 'boolean', 'rating' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class);
    }

    /** "Rahim U." — reviews never show a full name or phone. */
    public function authorName(): string
    {
        $parts = preg_split('/\s+/', trim($this->user->name ?? 'Customer'));

        return $parts[0].(isset($parts[1]) ? ' '.mb_substr($parts[1], 0, 1).'.' : '');
    }
}
