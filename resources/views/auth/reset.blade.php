@extends('layouts.app')

@section('title', 'Choose a new password - SafeBuy')

@section('content')
@include('partials.page-head', ['title' => 'New Password', 'crumbs' => [['My account', route('login')], ['New password', null]]])

<div class="wrap sec-in">
    <section class="auth-box narrow">
        <form method="post" action="{{ route('password.update') }}" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label class="fld">
                <span>Email <i>*</i></span>
                <input name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" @class(['bad' => $errors->has('email')])>
                @error('email')<em>{{ $message }}</em>@enderror
            </label>
            <label class="fld">
                <span>New password <i>*</i></span>
                <span class="pwd"><input name="password" type="password" required autocomplete="new-password" @class(['bad' => $errors->has('password')])><button type="button" class="eye" aria-label="Show password" data-eye><x-icon name="eye" size="18" /></button></span>
                @error('password')<em>{{ $message }}</em>@enderror
            </label>
            <label class="fld">
                <span>Confirm new password <i>*</i></span>
                <input name="password_confirmation" type="password" required autocomplete="new-password">
            </label>
            <button class="btn blue wide" type="submit">Save new password</button>
        </form>
    </section>
</div>
@endsection
