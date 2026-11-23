<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConciergeRequest extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'searching' => 'Searching',
        'quoted' => 'Price Quoted',
        'closed' => 'Closed',
    ];

    protected $fillable = [
        'name', 'phone', 'email', 'product_name', 'brand', 'reference_url',
        'budget', 'notes', 'image', 'status', 'quoted_price', 'admin_note',
    ];

    protected function casts(): array
    {
        return ['quoted_price' => 'integer'];
    }
}
