<?php

namespace App\Http\Controllers;

use App\Services\Cart;
use App\Services\PlaceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /** Bangladeshi mobile number: 01 + operator digit 3-9 + 8 digits. */
    public const PHONE = '/^01[3-9]\d{8}$/';

    public function __construct(private Cart $cart) {}

    public function show(Request $request): View|RedirectResponse
    {
        if ($request->ajax()) {
            return view('checkout._summary', ['cart' => $this->cart->summary()]);
        }
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart')->with('note', 'Your cart is empty. Add something first.');
        }

        // Logged-in customers: fill the form from the default saved address (or the account).
        $user = $request->user();
        $addresses = $user ? $user->addresses : collect();
        $def = $addresses->first();
        $prefill = [
            'name' => $def->name ?? $user?->name,
            'phone' => $def->phone ?? $user?->phone,
            'email' => $user?->email,
            'district' => $def->district ?? null,
            'area' => $def->area ?? null,
            'address' => $def->address ?? null,
        ];
        if ($def && ! $this->cart->summary()['district']) {
            $this->cart->setShipping(null, $def->district);
        }

        return view('checkout.index', [
            'prefill' => $prefill,
            'addresses' => $addresses,
            'cart' => $this->cart->summary(),
            'districts' => config('districts'),
            'payments' => $this->payments(),
        ]);
    }

    public function store(Request $request, PlaceOrder $placeOrder): RedirectResponse
    {
        // Normalise phone numbers typed with spaces, dashes or +88.
        foreach (['phone', 'ship_phone', 'bkash_sender'] as $f) {
            if ($request->filled($f)) {
                $digits = preg_replace('/\D/', '', $request->input($f));
                $request->merge([$f => preg_replace('/^880?(?=1)/', '0', $digits)]);
            }
        }

        $districts = config('districts');
        $payments = array_keys($this->payments());
        $other = $request->boolean('ship_different');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:'.self::PHONE],
            'email' => ['nullable', 'email', 'max:150'],
            'district' => ['required', Rule::in($districts)],
            'area' => ['nullable', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:500'],
            'ship_name' => [Rule::requiredIf($other), 'nullable', 'string', 'max:120'],
            'ship_phone' => [Rule::requiredIf($other), 'nullable', 'regex:'.self::PHONE],
            'ship_district' => [Rule::requiredIf($other), 'nullable', Rule::in($districts)],
            'ship_area' => ['nullable', 'string', 'max:120'],
            'ship_address' => [Rule::requiredIf($other), 'nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:1000'],
            'pickup' => ['nullable', 'boolean'],
            'payment_method' => ['required', Rule::in($payments)],
            'bkash_sender' => ['required_if:payment_method,bkash', 'nullable', 'regex:'.self::PHONE],
            'bkash_trx_id' => ['required_if:payment_method,bkash', 'nullable', 'regex:/^[A-Za-z0-9]{8,12}$/'],
            'terms' => ['accepted'],
        ], [
            'phone.regex' => 'Enter an 11-digit Bangladeshi mobile number, like 01712345678.',
            'ship_phone.regex' => 'Enter an 11-digit Bangladeshi mobile number, like 01712345678.',
            'bkash_sender.regex' => 'Enter the 11-digit bKash number you paid from.',
            'bkash_sender.required_if' => 'Enter the bKash number you paid from.',
            'bkash_trx_id.required_if' => 'Enter the Transaction ID from your bKash message.',
            'bkash_trx_id.regex' => 'The Transaction ID is 8–12 letters and numbers, like 9KD4AB12XY.',
            'terms.accepted' => 'Please accept the terms to place your order.',
        ]);

        if (! $other) {
            foreach (['ship_name', 'ship_phone', 'ship_district', 'ship_area', 'ship_address'] as $f) {
                $data[$f] = null;
            }
        }

        // The delivery district decides the charge (unless the customer collects from the shop).
        $deliverTo = $data['ship_district'] ?? $data['district'];
        $method = ($request->boolean('pickup') && config('safebuy.shipping.pickup_enabled')) ? 'pickup' : Cart::methodForDistrict($deliverTo);
        $this->cart->setShipping($method, $deliverTo);

        $order = $placeOrder->handle($data, $request->ip());

        // Lets this browser open the thank-you page for its own orders.
        session()->push('placed_orders', $order->number);

        return redirect()->route('order.thanks', $order);
    }

    /** Payment methods switched on in .env. bKash only shows once its number is set. */
    private function payments(): array
    {
        $list = [];
        if (config('safebuy.cod_enabled')) {
            $list['cod'] = 'Cash on delivery';
        }
        if (config('safebuy.bkash.enabled') && config('safebuy.bkash.number')) {
            $list['bkash'] = 'bKash';
        }

        return $list;
    }
}
