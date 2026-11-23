<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Session cart. Lines are stored as [key => [product_id, variant_id, qty]]; prices and stock are always
 * read fresh from the database, so a changed price or sold-out item is caught before checkout.
 * (Step 3 links the cart to customer accounts.)
 */
class Cart
{
    private const KEY = 'cart';

    public function lines(): array
    {
        return session(self::KEY.'.lines', []);
    }

    public function add(Product $product, ?ProductVariant $variant, int $qty): void
    {
        $key = $product->id.($variant ? '-'.$variant->id : '');
        $lines = $this->lines();
        $lines[$key] = [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'qty' => ($lines[$key]['qty'] ?? 0) + max(1, $qty),
        ];
        $this->store($lines);
    }

    public function setQty(string $key, int $qty): void
    {
        $lines = $this->lines();
        if (! isset($lines[$key])) {
            return;
        }
        if ($qty < 1) {
            unset($lines[$key]);
        } else {
            $lines[$key]['qty'] = $qty;
        }
        $this->store($lines);
    }

    public function remove(string $key): void
    {
        $this->setQty($key, 0);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
        auth()->user()?->forceFill(['saved_cart' => null])->save();
    }

    /** Keep lines in the session and, for a logged-in customer, on the account (so the cart follows them). */
    private function store(array $lines): void
    {
        session([self::KEY.'.lines' => $lines]);
        if ($user = auth()->user()) {
            $user->forceFill(['saved_cart' => $lines ?: null])->save();
        }
    }

    /** After login: combine the guest cart with the cart saved on the account (higher quantity wins). */
    public function mergeSaved(\App\Models\User $user): void
    {
        $lines = $this->lines();
        foreach ((array) $user->saved_cart as $key => $line) {
            if (! isset($line['product_id'], $line['qty'])) {
                continue;
            }
            $lines[$key] = isset($lines[$key]) ? array_merge($line, ['qty' => max($line['qty'], $lines[$key]['qty'])]) : $line;
        }
        $this->store($lines);
    }

    public function setCoupon(?string $code): void
    {
        session([self::KEY.'.coupon' => $code ? strtoupper(trim($code)) : null]);
    }

    /** A district without a method picks inside/outside Dhaka from the district. */
    public function setShipping(?string $method, ?string $district = null): void
    {
        if ($method === null && $district && in_array($district, config('districts'), true)) {
            $method = self::methodForDistrict($district);
        }
        if ($method !== null && array_key_exists($method, $this->shippingOptions())) {
            session([self::KEY.'.shipping' => $method]);
        }
        if ($district !== null) {
            session([self::KEY.'.district' => in_array($district, config('districts'), true) ? $district : null]);
        }
    }

    /** Delivery choices with their price before the free-delivery rule. */
    public function shippingOptions(): array
    {
        $c = config('safebuy.shipping');
        $options = [
            'inside_dhaka' => ['label' => Order::SHIPPING['inside_dhaka'], 'price' => $c['inside_dhaka']],
            'outside_dhaka' => ['label' => Order::SHIPPING['outside_dhaka'], 'price' => $c['outside_dhaka']],
        ];
        if ($c['pickup_enabled']) {
            $options['pickup'] = ['label' => Order::SHIPPING['pickup'], 'price' => 0, 'note' => $c['pickup_address']];
        }

        return $options;
    }

    public static function methodForDistrict(string $district): string
    {
        return in_array($district, config('safebuy.shipping.inside_districts'), true) ? 'inside_dhaka' : 'outside_dhaka';
    }

    /**
     * Everything the cart page, mini-cart and checkout show, computed from current prices and stock.
     * Lines that no longer exist are dropped; quantities above stock are lowered (with a note).
     */
    public function summary(): array
    {
        $lines = $this->lines();
        $notes = [];
        $products = Product::whereIn('id', collect($lines)->pluck('product_id'))->where('is_active', true)
            ->with('variants.values')->get()->keyBy('id');

        $items = collect();
        foreach ($lines as $key => $line) {
            $product = $products->get($line['product_id']);
            $variant = $product && $line['variant_id'] ? $product->variants->firstWhere('id', $line['variant_id']) : null;
            if (! $product || ($line['variant_id'] && ! $variant) || (! $line['variant_id'] && $product->isVariable())) {
                unset($lines[$key]);
                $notes[] = 'An item in your cart is no longer available and was removed.';
                continue;
            }
            $stock = $variant ? $variant->stock : $product->stock;
            $qty = $line['qty'];
            if ($stock !== null && $qty > $stock) {
                $qty = $stock;
                $notes[] = $stock > 0
                    ? "Only {$stock} of “{$product->name}” left, so the quantity was lowered."
                    : "“{$product->name}” just sold out and was removed.";
                if ($qty < 1) {
                    unset($lines[$key]);
                    continue;
                }
                $lines[$key]['qty'] = $qty;
            }
            $price = $variant ? $variant->price : $product->price;
            $items->push([
                'key' => (string) $key,
                'id' => $product->id,
                'vid' => $variant?->id,
                'name' => $product->name,
                'variant' => $variant?->label(),
                'sku' => $variant ? $variant->sku : $product->sku,
                'image' => asset($variant?->image ?: $product->image),
                'url' => route('product.show', $product),
                'price' => $price,
                'old_price' => $variant ? $variant->old_price : $product->old_price,
                'qty' => $qty,
                'max' => $stock,
                'line' => $price * $qty,
            ]);
        }
        if ($lines !== $this->lines()) {
            $this->store($lines);
        }

        $subtotal = (int) $items->sum('line');

        // Coupon
        $coupon = null;
        $discount = 0;
        $couponError = null;
        if ($code = session(self::KEY.'.coupon')) {
            $coupon = Coupon::findByCode($code);
            $couponError = $coupon ? $coupon->problem($subtotal) : 'This coupon code does not exist.';
            if ($couponError || ! $items->count()) {
                $coupon = null;
            } else {
                $discount = $coupon->discountFor($subtotal);
            }
        }

        // Delivery
        $options = $this->shippingOptions();
        $district = session(self::KEY.'.district');
        $method = session(self::KEY.'.shipping');
        if (! isset($options[$method])) {
            $method = $district ? self::methodForDistrict($district) : 'inside_dhaka';
        }
        $freeOver = (int) config('safebuy.shipping.free_over');
        $afterDiscount = $subtotal - $discount;
        $isFree = $freeOver > 0 && $afterDiscount >= $freeOver;
        $zoneCharge = null;
        if ($method !== 'pickup' && $district && Schema::hasTable('shipping_zones')) {
            if ($zone = ShippingZone::forDistrict($district)) {
                $zoneCharge = $zone->charge;
            }
        }
        $shipping = ($items->isEmpty() || $isFree) ? 0 : ($zoneCharge ?? $options[$method]['price']);

        return [
            'items' => $items->values()->all(),
            'count' => (int) $items->sum('qty'),
            'subtotal' => $subtotal,
            'coupon' => $coupon ? ['code' => $coupon->code, 'label' => $coupon->label()] : null,
            'coupon_error' => $couponError,
            'discount' => $discount,
            'shipping_method' => $method,
            'shipping_options' => $options,
            'district' => $district,
            'shipping' => $shipping,
            'free_over' => $freeOver,
            'free_left' => $freeOver > 0 ? max(0, $freeOver - $afterDiscount) : null,
            'total' => max(0, $afterDiscount) + $shipping,
            'notes' => array_values(array_unique($notes)),
        ];
    }

    /** Summary with Taka strings added, for the browser. */
    public function json(): array
    {
        $s = $this->summary();
        $s['items'] = collect($s['items'])->map(fn ($i) => $i + ['price_text' => Product::taka($i['price']), 'line_text' => Product::taka($i['line'])])->all();
        foreach (['subtotal', 'discount', 'shipping', 'total'] as $k) {
            $s[$k.'_text'] = Product::taka($s[$k]);
        }
        $s['free_left_text'] = $s['free_left'] !== null ? Product::taka($s['free_left']) : null;

        return $s;
    }

    public function isEmpty(): bool
    {
        return empty($this->lines());
    }
}
