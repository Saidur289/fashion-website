@extends('layouts.app')

@section('title', 'Lost password - SafeBuy')

@section('content')
@include('partials.page-head', ['title' => 'Lost Password', 'crumbs' => [['My account', route('login')], ['Lost password', null]]])

<div class="wrap sec-in">
    <section class="auth-box narrow">
        @if (session('status'))<p class="alert ok">{{ session('status') }}</p>@endif
        <p>Enter the email on your account and we will send you a link to choose a new password.</p>
        <form method="post" action="{{ route('password.email') }}" novalidate>
            @csrf
            <label class="fld">
                <span>Email <i>*</i></span>
                <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" @class(['bad' => $errors->has('email')])>
                @error('email')<em>{{ $message }}</em>@enderror
            </label>
            <button class="btn blue wide" type="submit">Send reset link</button>
        </form>
        <p class="small">Signed up with a mobile number only? Contact us at <a href="mailto:{{ config('safebuy.email') }}">{{ config('safebuy.email') }}</a>@if (config('safebuy.phone')) or call {{ config('safebuy.phone') }}@endif and we will help you get back in.</p>
        <a class="tlink" href="{{ route('login') }}"><x-icon name="arrow-left" size="14" /> Back to log in</a>
    </section>
</div>
@endsection
