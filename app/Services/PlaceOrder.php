<?php

namespace App\Services;

use App\Mail\OrderPlaced;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Turns the session cart into an order inside one database transaction:
 * stock is re-checked with row locks and lowered, the coupon use is counted, and the order number is set.
 */
class PlaceOrder
{
    public function __construct(private Cart $cart, private Sms $sms) {}

    /** @param array $data validated checkout fields */
    public function handle(array $data, ?string $ip = null): Order
    {
        $summary = $this->cart->summary();
        if (! $summary['items']) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }
        // Stock or availability changed since the customer last looked: show what changed, don't place silently.
        if ($summary['notes']) {
            throw ValidationException::withMessages(['cart' => array_merge($summary['notes'], ['Please check your order and place it again.'])]);
        }

        $order = DB::transaction(function () use ($data, $summary, $ip) {
            foreach ($summary['items'] as $item) {
                $row = $item['vid']
                    ? ProductVariant::whereKey($item['vid'])->lockForUpdate()->first()
                    : Product::whereKey($item['id'])->lockForUpdate()->first();
                if (! $row) {
                    throw ValidationException::withMessages(['cart' => "“{$item['name']}” is no longer available."]);
                }
                if ($row->stock !== null) {
                    if ($row->stock < $item['qty']) {
                        throw ValidationException::withMessages(['cart' => "Sorry, only {$row->stock} of “{$item['name']}” left. Please update your cart."]);
                    }
                    $row->decrement('stock', $item['qty']);
                }
                Product::whereKey($item['id'])->increment('sold_count', $item['qty']);
            }

            if ($summary['coupon']) {
                $coupon = Coupon::where('code', $summary['coupon']['code'])->lockForUpdate()->first();
                if (! $coupon || $coupon->problem($summary['subtotal'])) {
                    throw ValidationException::withMessages(['coupon' => 'Your coupon can no longer be used. Please remove it and try again.']);
                }
                $coupon->increment('used');
            }

            $isBkash = $data['payment_method'] === 'bkash';
            $order = Order::create([
                'number' => 'TMP-'.uniqid(),
                'user_id' => auth()->id(),
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'district' => $data['district'],
                'area' => $data['area'] ?? null,
                'address' => $data['address'],
                'ship_name' => $data['ship_name'] ?? null,
                'ship_phone' => $data['ship_phone'] ?? null,
                'ship_district' => $data['ship_district'] ?? null,
                'ship_area' => $data['ship_area'] ?? null,
                'ship_address' => $data['ship_address'] ?? null,
                'note' => $data['note'] ?? null,
                'subtotal' => $summary['subtotal'],
                'coupon_code' => $summary['coupon']['code'] ?? null,
                'discount' => $summary['discount'],
                'shipping_method' => $summary['shipping_method'],
                'shipping' => $summary['shipping'],
                'total' => $summary['total'],
                'payment_method' => $data['payment_method'],
                'payment_status' => $isBkash ? 'pending_check' : 'unpaid',
                'bkash_sender' => $isBkash ? $data['bkash_sender'] : null,
                'bkash_trx_id' => $isBkash ? strtoupper($data['bkash_trx_id']) : null,
                'status' => 'pending',
                'ip' => $ip,
            ]);
            // SB-261123-0001 style: date + running number, easy to read out on the phone.
            $order->update(['number' => 'SB-'.now()->format('ymd').'-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)]);

            foreach ($summary['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['id'],
                    'product_variant_id' => $item['vid'],
                    'name' => $item['name'],
                    'variant' => $item['variant'],
                    'sku' => $item['sku'],
                    'image' => $item['image'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'total' => $item['line'],
                ]);
            }
            $order->histories()->create(['status' => 'pending', 'note' => 'Order placed on the website.']);

            return $order;
        });

        $this->cart->clear();
        $this->notify($order);

        return $order;
    }

    private function notify(Order $order): void
    {
        $track = route('order.track', ['number' => $order->number]);
        $total = Product::taka($order->total);

        $this->sms->send($order->phone, "SafeBuy: Order {$order->number} received. Total {$total}. We will call you to confirm. Track: {$track}");
        $this->sms->send(config('safebuy.notify_phone'), "New SafeBuy order {$order->number}: {$total}, {$order->paymentLabel()}, {$order->name} {$order->phone}");

        try {
            $order->load('items');
            if ($order->email) {
                Mail::to($order->email)->send(new OrderPlaced($order, false));
            }
            if ($to = config('safebuy.notify_email')) {
                Mail::to($to)->send(new OrderPlaced($order, true));
            }
        } catch (\Throwable $e) {
            Log::warning("Order {$order->number} email failed: {$e->getMessage()}");
        }
    }
}
