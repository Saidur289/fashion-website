<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

/**
 * Handles admin order lifecycle transitions:
 * status updates, automatic stock restore/deduct on cancel/return, SMS alerts,
 * bKash verification, tracking updates, and manual phone orders.
 */
class OrderManager
{
    public const STOCK_RELEASED_STATUSES = ['cancelled', 'returned'];

    public function __construct(private Sms $sms) {}

    public function transitionStatus(Order $order, string $newStatus, ?string $note = null, bool $sendSms = true): void
    {
        $oldStatus = $order->status;
        if ($oldStatus === $newStatus && ! $note) {
            return;
        }

        DB::transaction(function () use ($order, $oldStatus, $newStatus, $note) {
            $wasReleased = in_array($oldStatus, self::STOCK_RELEASED_STATUSES, true);
            $willRelease = in_array($newStatus, self::STOCK_RELEASED_STATUSES, true);

            if (! $wasReleased && $willRelease) {
                $this->adjustOrderStock($order, +1);
            } elseif ($wasReleased && ! $willRelease) {
                $this->adjustOrderStock($order, -1);
            }

            $updates = ['status' => $newStatus];
            if ($newStatus === 'delivered' && $order->payment_method === 'cod' && $order->payment_status === 'unpaid') {
                $updates['payment_status'] = 'paid';
            }

            $order->update($updates);

            $order->histories()->create([
                'status' => $newStatus,
                'note' => $note ?: "Status changed from {$oldStatus} to {$newStatus}.",
                'changed_by' => auth()->id(),
            ]);

            ActivityLog::record(
                'status_changed',
                "Order {$order->number}: {$oldStatus} → {$newStatus}".($note ? " ({$note})" : ''),
                $order,
                ['from' => $oldStatus, 'to' => $newStatus, 'note' => $note],
                $order->number,
            );
        });

        if ($sendSms && $oldStatus !== $newStatus) {
            $this->sendStatusSms($order->fresh(), $newStatus);
        }
    }

    public function updatePaymentStatus(Order $order, string $paymentStatus, ?string $note = null): void
    {
        $old = $order->payment_status;
        if ($old === $paymentStatus) {
            return;
        }

        $order->update(['payment_status' => $paymentStatus]);

        $order->histories()->create([
            'status' => $order->status,
            'note' => $note ?: "Payment status changed from {$old} to {$paymentStatus}.",
            'changed_by' => auth()->id(),
        ]);

        ActivityLog::record(
            'payment_verified',
            "Order {$order->number} payment marked as {$paymentStatus}".($order->bkash_trx_id ? " (TrxID: {$order->bkash_trx_id})" : ''),
            $order,
            ['from' => $old, 'to' => $paymentStatus, 'bkash_trx_id' => $order->bkash_trx_id],
            $order->number,
        );
    }

    public function updateTracking(Order $order, ?string $trackingCode): void
    {
        $old = $order->tracking_code;
        $order->update(['tracking_code' => $trackingCode ?: null]);

        if ($old !== $order->tracking_code) {
            $order->histories()->create([
                'status' => $order->status,
                'note' => $trackingCode ? "Courier tracking code set: {$trackingCode}" : 'Courier tracking code cleared.',
                'changed_by' => auth()->id(),
            ]);

            ActivityLog::record(
                'updated',
                "Order {$order->number} tracking code updated to ".($trackingCode ?: 'none'),
                $order,
                ['from' => $old, 'to' => $trackingCode],
                $order->number,
            );
        }
    }

    /** Create an order placed over the phone by an admin/staff member. */
    public function createManualOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $itemsData = [];
            $subtotal = 0;

            foreach ($data['items'] ?? [] as $row) {
                $product = Product::find($row['product_id']);
                if (! $product) {
                    continue;
                }
                $variant = ! empty($row['product_variant_id'])
                    ? ProductVariant::where('product_id', $product->id)->find($row['product_variant_id'])
                    : null;

                $qty = max(1, (int) ($row['qty'] ?? 1));
                $price = isset($row['price']) && $row['price'] !== ''
                    ? (int) $row['price']
                    : (int) ($variant ? $variant->price : $product->price);
                $line = $price * $qty;
                $subtotal += $line;

                // Deduct stock if tracked
                $stockTarget = $variant ?: $product;
                if ($stockTarget->stock !== null) {
                    $stockTarget->update(['stock' => max(0, $stockTarget->stock - $qty)]);
                }
                $product->increment('sold_count', $qty);

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'name' => $product->name,
                    'variant' => $variant?->label(),
                    'sku' => $variant ? $variant->sku : $product->sku,
                    'image' => asset($variant?->image ?: $product->image),
                    'price' => $price,
                    'qty' => $qty,
                    'total' => $line,
                ];
            }

            $discount = max(0, (int) ($data['discount'] ?? 0));
            $couponCode = ! empty($data['coupon_code']) ? strtoupper(trim($data['coupon_code'])) : null;
            if ($couponCode && $discount === 0) {
                if ($coupon = Coupon::findByCode($couponCode)) {
                    $discount = $coupon->discountFor($subtotal);
                    $coupon->increment('used');
                }
            }

            $shipping = max(0, (int) ($data['shipping'] ?? 0));
            $total = max(0, $subtotal - $discount) + $shipping;
            $status = $data['status'] ?? 'confirmed';

            $order = Order::create([
                'number' => 'TMP-'.uniqid(),
                'user_id' => $data['user_id'] ?? null,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'district' => $data['district'],
                'area' => $data['area'] ?? null,
                'address' => $data['address'],
                'note' => $data['note'] ?? null,
                'subtotal' => $subtotal,
                'coupon_code' => $couponCode,
                'discount' => $discount,
                'shipping_method' => $data['shipping_method'] ?? Cart::methodForDistrict($data['district']),
                'shipping' => $shipping,
                'total' => $total,
                'payment_method' => $data['payment_method'] ?? 'cod',
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'bkash_sender' => $data['bkash_sender'] ?? null,
                'bkash_trx_id' => ! empty($data['bkash_trx_id']) ? strtoupper($data['bkash_trx_id']) : null,
                'status' => $status,
                'tracking_code' => $data['tracking_code'] ?? null,
                'ip' => request()?->ip(),
            ]);

            $order->update(['number' => 'SB-'.now()->format('ymd').'-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            $order->histories()->create([
                'status' => $status,
                'note' => 'Order created manually from admin panel (phone order).',
                'changed_by' => auth()->id(),
            ]);

            ActivityLog::record(
                'created',
                "Manual phone order {$order->number} created for {$order->name} ({$order->phone}) — ".Product::taka($order->total),
                $order,
                ['total' => $order->total, 'items_count' => count($itemsData)],
                $order->number,
            );

            return $order;
        });
    }

    /** $direction: +1 to restore stock on cancel/return, -1 to deduct again if re-opened. */
    private function adjustOrderStock(Order $order, int $direction): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                $variant = ProductVariant::whereKey($item->product_variant_id)->lockForUpdate()->first();
                if ($variant && $variant->stock !== null) {
                    $variant->update(['stock' => max(0, $variant->stock + ($direction * $item->qty))]);
                }
            } elseif ($item->product_id) {
                $product = Product::withTrashed()->whereKey($item->product_id)->lockForUpdate()->first();
                if ($product && $product->stock !== null) {
                    $product->update(['stock' => max(0, $product->stock + ($direction * $item->qty))]);
                }
            }

            if ($item->product_id) {
                $product = Product::withTrashed()->find($item->product_id);
                if ($product) {
                    $newSold = max(0, (int) $product->sold_count - ($direction * $item->qty));
                    $product->update(['sold_count' => $newSold]);
                }
            }
        }
    }

    private function sendStatusSms(Order $order, string $status): void
    {
        $tpl = Settings::smsTemplate($status);
        $tracking = $order->tracking_code ? " Tracking: {$order->tracking_code}." : '';
        $message = strtr($tpl, [
            '{number}' => $order->number,
            '{name}' => $order->name,
            '{total}' => Product::taka($order->total),
            '{status}' => $order->statusLabel(),
            '{tracking}' => $tracking,
            '{track_url}' => route('order.track', ['number' => $order->number]),
        ]);

        $this->sms->send($order->phone, $message);
    }
}
