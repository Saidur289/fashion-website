@extends('layouts.app')

@section('title', 'Account details - SafeBuy')

@section('content')
@include('partials.page-head', ['title' => 'Account Details', 'crumbs' => [['My account', route('account')], ['Account details', null]]])
<div class="wrap sec-in acc">
    @include('account._shell', ['active' => 'details'])
    <div class="acc-main">
        @if (session('status'))<p class="alert ok">{{ session('status') }}</p>@endif
        <form class="auth-box flat" method="post" action="{{ route('account.details.update') }}" novalidate>
            @csrf @method('PUT')
            <label class="fld"><span>Full name <i>*</i></span><input name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" @class(['bad' => $errors->has('name')])>@error('name')<em>{{ $message }}</em>@enderror</label>
            <div class="frow two">
                <label class="fld"><span>Mobile number <i>*</i></span><input name="phone" value="{{ old('phone', $user->phone) }}" inputmode="tel" required @class(['bad' => $errors->has('phone')])>@error('phone')<em>{{ $message }}</em>@enderror</label>
                <label class="fld"><span>Email</span><input name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" @class(['bad' => $errors->has('email')])>@error('email')<em>{{ $message }}</em>@enderror</label>
            </div>

            <fieldset class="pwbox">
                <legend>Change password <small>(leave empty to keep the current one)</small></legend>
                <label class="fld"><span>Current password</span><span class="pwd"><input name="current_password" type="password" autocomplete="current-password" @class(['bad' => $errors->has('current_password')])><button type="button" class="eye" aria-label="Show password" data-eye><x-icon name="eye" size="18" /></button></span>@error('current_password')<em>{{ $message }}</em>@enderror</label>
                <div class="frow two">
                    <label class="fld"><span>New password</span><span class="pwd"><input name="password" type="password" autocomplete="new-password" @class(['bad' => $errors->has('password')])><button type="button" class="eye" aria-label="Show password" data-eye><x-icon name="eye" size="18" /></button></span>@error('password')<em>{{ $message }}</em>@enderror</label>
                    <label class="fld"><span>Confirm new password</span><input name="password_confirmation" type="password" autocomplete="new-password"></label>
                </div>
            </fieldset>
            <button class="btn orange" type="submit">Save changes</button>
        </form>
    </div>
</div>
@endsection
