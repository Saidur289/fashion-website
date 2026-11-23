<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Cart;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Customer accounts. Customers log in with their mobile number (or email) and a password.
 * Guest checkout still works; an account adds order history, saved addresses, wishlist and reviews.
 */
class AuthController extends Controller
{
    /** /account when logged out: login and register side by side (Hara "My account" layout). */
    public function show(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account');
        }
        if ($request->filled('redirect') && $this->safeRedirect($request->query('redirect'))) {
            session(['url.intended' => $request->query('redirect')]);
        }

        return view('auth.login', ['tab' => $request->routeIs('register') ? 'register' : 'login']);
    }

    public function login(Request $request, Cart $cart): RedirectResponse
    {
        $request->validate(['login' => ['required', 'string'], 'password' => ['required', 'string']]);
        $this->setIntended($request);

        $key = 'login:'.Str::lower($request->input('login')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['login' => 'Too many tries. Please wait '.RateLimiter::availableIn($key).' seconds.'])->redirectTo(route('login'));
        }

        $field = str_contains($request->input('login'), '@') ? 'email' : 'phone';
        $value = $field === 'phone' ? self::phone($request->input('login')) : Str::lower(trim($request->input('login')));

        if (! Auth::attempt([$field => $value, 'password' => $request->input('password')], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['login' => 'The mobile number / email or password is not correct.'])->redirectTo(route('login'));
        }
        if (Auth::user()->is_blocked) {
            Auth::logout();
            throw ValidationException::withMessages(['login' => 'This account is blocked. Please contact us.'])->redirectTo(route('login'));
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $cart->mergeSaved(Auth::user());

        return redirect()->intended(route('account'))->with('status', 'Welcome back, '.Auth::user()->name.'!');
    }

    public function register(Request $request, Cart $cart): RedirectResponse
    {
        $request->merge(['phone' => self::phone((string) $request->input('phone')), 'email' => $request->filled('email') ? Str::lower(trim($request->input('email'))) : null]);
        $this->setIntended($request);

        $data = $request->validateWithBag('register', [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:'.CheckoutController::PHONE, Rule::unique('users', 'phone')],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'phone.regex' => 'Enter an 11-digit Bangladeshi mobile number, like 01712345678.',
            'phone.unique' => 'An account with this mobile number already exists. Please log in.',
            'email.unique' => 'An account with this email already exists. Please log in.',
        ]);

        $user = User::create($data);
        Auth::login($user, true);
        $request->session()->regenerate();
        $cart->mergeSaved($user);

        return redirect()->intended(route('account'))->with('status', 'Your account is ready. Welcome, '.$user->name.'!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'You are logged out.');
    }

    /* ---------- password reset (by email) ---------- */

    public function forgot(): View
    {
        return view('auth.forgot');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        // Same answer whether or not the email exists, so the form can't be used to find accounts.
        Password::sendResetLink(['email' => Str::lower(trim($request->input('email')))]);

        return back()->with('status', 'If an account uses this email, a reset link is on its way. Check your inbox (and spam).');
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('status', 'Your password is changed. You can log in now.')
            : back()->withErrors(['email' => __($status)]);
    }

    /* ---------- helpers ---------- */

    /** 01712-345678 / +8801712345678 -> 01712345678 */
    public static function phone(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value);

        return preg_replace('/^880?(?=1)/', '0', $digits);
    }

    private function setIntended(Request $request): void
    {
        if ($request->filled('redirect') && $this->safeRedirect($request->input('redirect'))) {
            session(['url.intended' => $request->input('redirect')]);
        }
    }

    /** Only redirect back to pages on this site. */
    private function safeRedirect(string $url): bool
    {
        return str_starts_with($url, url('/'));
    }
}
