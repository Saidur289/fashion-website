<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private Cart $cart) {}

    public function show(Request $request): View
    {
        // safebuy.js re-renders only the cart body after each change.
        if ($request->ajax()) {
            return view('cart._body', ['cart' => $this->cart->summary()]);
        }

        return view('cart.index', [
            'cart' => $this->cart->summary(),
            'districts' => config('districts'),
            'suggested' => Product::active()->with(['category', 'brand', 'variants.values.attribute'])
                ->whereNotIn('id', collect($this->cart->lines())->pluck('product_id'))
                ->where('is_best_seller', true)->take(8)->get(),
        ]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->cart->json());
    }

    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'variant_id' => ['nullable', 'integer'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);
        $product = Product::where('is_active', true)->with('variants')->find($data['product_id']);
        if (! $product) {
            return $this->fail('This product is no longer available.');
        }

        $variant = null;
        if ($product->isVariable()) {
            $variant = $product->variants->firstWhere('id', $data['variant_id'] ?? null);
            if (! $variant) {
                return $this->fail('Please choose an option first.');
            }
        }

        $stock = $variant ? $variant->stock : $product->stock;
        $inCart = $this->cart->lines()[$product->id.($variant ? '-'.$variant->id : '')]['qty'] ?? 0;
        $qty = (int) ($data['qty'] ?? 1);
        if ($stock !== null && $inCart + $qty > $stock) {
            return $this->fail($stock - $inCart > 0
                ? 'Only '.($stock - $inCart).' more can be added (stock: '.$stock.').'
                : 'You already have all '.$stock.' in stock in your cart.');
        }

        $this->cart->add($product, $variant, $qty);

        return $this->ok('Added to cart');
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate(['key' => ['required', 'string'], 'qty' => ['required', 'integer', 'min:0', 'max:99']]);
        $this->cart->setQty($data['key'], $data['qty']);

        return $this->ok();
    }

    public function remove(Request $request): JsonResponse
    {
        $this->cart->remove((string) $request->input('key'));

        return $this->ok('Removed from cart');
    }

    public function coupon(Request $request): JsonResponse
    {
        $code = trim((string) $request->input('code'));
        if ($code === '') {
            $this->cart->setCoupon(null);

            return $this->ok('Coupon removed');
        }

        $coupon = Coupon::findByCode($code);
        $problem = $coupon ? $coupon->problem($this->cart->summary()['subtotal']) : 'This coupon code does not exist.';
        if ($problem) {
            return $this->fail($problem);
        }
        $this->cart->setCoupon($coupon->code);

        return $this->ok('Coupon applied: '.$coupon->label());
    }

    public function shipping(Request $request): JsonResponse
    {
        $this->cart->setShipping($request->input('method'), $request->input('district'));

        return $this->ok();
    }

    private function ok(?string $message = null): JsonResponse
    {
        return response()->json(['ok' => true, 'message' => $message, 'cart' => $this->cart->json()]);
    }

    private function fail(string $message): JsonResponse
    {
        return response()->json(['ok' => false, 'message' => $message, 'cart' => $this->cart->json()], 422);
    }
}
