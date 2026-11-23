@extends('layouts.app')

@section('title', 'My account - SafeBuy')

@section('content')
@include('partials.page-head', ['title' => 'My Account', 'crumbs' => [['My account', null]]])

<div class="wrap sec-in">
    @if (session('status'))<p class="alert ok">{{ session('status') }}</p>@endif
    <div class="auth-grid">
        <section class="auth-box" aria-labelledby="t-login">
            <h2 id="t-login">Log in</h2>
            @include('auth._login-form', ['idp' => 'p'])
        </section>

        <section class="auth-box" aria-labelledby="t-reg" id="register">
            <h2 id="t-reg">Create an account</h2>
            <p class="small">Save your addresses, see your orders and keep a wishlist. You can also check out as a guest.</p>
            <form method="post" action="{{ route('register.store') }}" novalidate>
                @csrf
                <label class="fld">
                    <span>Full name <i>*</i></span>
                    <input name="name" value="{{ old('name') }}" required autocomplete="name" @class(['bad' => $errors->register->has('name')])>
                    @if ($errors->register->has('name'))<em>{{ $errors->register->first('name') }}</em>@endif
                </label>
                <label class="fld">
                    <span>Mobile number <i>*</i></span>
                    <input name="phone" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" @class(['bad' => $errors->register->has('phone')])>
                    @if ($errors->register->has('phone'))<em>{{ $errors->register->first('phone') }}</em>@endif
                </label>
                <label class="fld">
                    <span>Email <small>(optional, lets you reset your password by email)</small></span>
                    <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" @class(['bad' => $errors->register->has('email')])>
                    @if ($errors->register->has('email'))<em>{{ $errors->register->first('email') }}</em>@endif
                </label>
                <div class="frow two">
                    <label class="fld">
                        <span>Password <i>*</i></span>
                        <span class="pwd"><input name="password" type="password" required autocomplete="new-password" minlength="8" @class(['bad' => $errors->register->has('password')])><button type="button" class="eye" aria-label="Show password" data-eye><x-icon name="eye" size="18" /></button></span>
                        @if ($errors->register->has('password'))<em>{{ $errors->register->first('password') }}</em>@else<small class="hint2">At least 8 characters</small>@endif
                    </label>
                    <label class="fld">
                        <span>Confirm password <i>*</i></span>
                        <span class="pwd"><input name="password_confirmation" type="password" required autocomplete="new-password"><button type="button" class="eye" aria-label="Show password" data-eye><x-icon name="eye" size="18" /></button></span>
                    </label>
                </div>
                <button class="btn orange wide" type="submit">Create account</button>
            </form>
        </section>
    </div>
</div>
@endsection
