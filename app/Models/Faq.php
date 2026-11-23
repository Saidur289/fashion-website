<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    public const CATEGORIES = [
        'orders' => 'Orders & Ordering',
        'delivery' => 'Delivery & Shipping',
        'payment' => 'Payment (COD & bKash)',
        'returns' => 'Returns & Refunds',
        'authenticity' => 'Product Authenticity',
    ];

    protected $fillable = ['category', 'question', 'answer', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
