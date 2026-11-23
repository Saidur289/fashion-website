<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/** Logged-in customer area: dashboard, orders, addresses, account details. */
class AccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        return view('account.dashboard', [
            'user' => $user,
            'recent' => $user->orders()->with('items')->take(3)->get(),
            'counts' => [
                'orders' => $user->orders()->count(),
                'wishlist' => $user->wishlist()->count(),
                'addresses' => $user->addresses()->count(),
            ],
        ]);
    }

    public function orders(Request $request): View
    {
        return view('account.orders', ['orders' => $request->user()->orders()->with('items')->paginate(10)]);
    }

    public function order(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return view('account.order', ['order' => $order->load(['items', 'histories'])]);
    }

    /* ---------- addresses ---------- */

    public function addresses(Request $request): View
    {
        return view('account.addresses', ['addresses' => $request->user()->addresses, 'districts' => config('districts')]);
    }

    public function saveAddress(Request $request, ?Address $address = null): RedirectResponse
    {
        if ($address) {
            abort_unless($address->user_id === $request->user()->id, 404);
        }
        $request->merge(['phone' => AuthController::phone((string) $request->input('phone'))]);
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:'.CheckoutController::PHONE],
            'district' => ['required', Rule::in(config('districts'))],
            'area' => ['nullable', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
        ], ['phone.regex' => 'Enter an 11-digit Bangladeshi mobile number, like 01712345678.']);

        $user = $request->user();
        $data['is_default'] = $request->boolean('is_default') || ! $user->addresses()->exists();
        if ($data['is_default']) {
            $user->addresses()->update(['is_default' => false]);
        }
        $address ? $address->update($data) : $user->addresses()->create($data);

        return redirect()->route('account.addresses')->with('status', 'Address saved.');
    }

    public function deleteAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $wasDefault = $address->is_default;
        $address->delete();
        if ($wasDefault && ($first = $request->user()->addresses()->first())) {
            $first->update(['is_default' => true]);
        }

        return redirect()->route('account.addresses')->with('status', 'Address removed.');
    }

    /* ---------- account details ---------- */

    public function details(Request $request): View
    {
        return view('account.details', ['user' => $request->user()]);
    }

    public function saveDetails(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->merge([
            'phone' => AuthController::phone((string) $request->input('phone')),
            'email' => $request->filled('email') ? Str::lower(trim($request->input('email'))) : null,
        ]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:'.CheckoutController::PHONE, Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', PasswordRule::min(8)],
        ], [
            'phone.regex' => 'Enter an 11-digit Bangladeshi mobile number, like 01712345678.',
            'current_password.current_password' => 'Your current password is not correct.',
        ]);

        $user->fill(['name' => $data['name'], 'phone' => $data['phone'], 'email' => $data['email']]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return redirect()->route('account.details')->with('status', 'Your details are saved.');
    }
}
