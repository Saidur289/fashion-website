<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Order placed',
        'confirmed' => 'Confirmed',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'Unpaid',
        'pending_check' => 'Pending bKash check',
        'paid' => 'Paid',
        'refunded' => 'Refunded',
    ];

    // The normal path an order moves along, shown as a timeline on the tracking page.
    public const FLOW = ['pending', 'confirmed', 'shipped', 'delivered'];

    public const SHIPPING = [
        'inside_dhaka' => 'Inside Dhaka',
        'outside_dhaka' => 'Outside Dhaka',
        'pickup' => 'Pick up from shop',
    ];

    public const PAYMENTS = [
        'cod' => 'Cash on delivery',
        'bkash' => 'bKash',
    ];

    protected $guarded = ['id'];

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function paymentLabel(): string
    {
        return self::PAYMENTS[$this->payment_method] ?? $this->payment_method;
    }

    public function shippingLabel(): string
    {
        return self::SHIPPING[$this->shipping_method] ?? $this->shipping_method;
    }

    /** "01712345678" -> "017•••••678" for pages anyone with the order number can open. */
    public function maskedPhone(): string
    {
        return substr($this->phone, 0, 3).str_repeat('•', max(0, strlen($this->phone) - 6)).substr($this->phone, -3);
    }
}
