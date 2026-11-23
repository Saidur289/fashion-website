@extends('layouts.app')

@section('title', 'My addresses - SafeBuy')

@section('content')
@include('partials.page-head', ['title' => 'Addresses', 'crumbs' => [['My account', route('account')], ['Addresses', null]]])
<div class="wrap sec-in acc">
    @include('account._shell', ['active' => 'addresses'])
    <div class="acc-main">
        @if (session('status'))<p class="alert ok">{{ session('status') }}</p>@endif
        <p class="lead2">Saved addresses fill in the checkout form for you. The default one is used first.</p>

        <ul class="addr-list">
            @foreach ($addresses as $a)
                <li @class(['addr', 'def' => $a->is_default])>
                    <div class="addr-h">
                        <b>{{ $a->label ?: 'Address' }}</b>
                        @if ($a->is_default)<span class="stag">Default</span>@endif
                    </div>
                    <p>{{ $a->name }}<br>{{ $a->phone }}<br>{{ $a->oneLine() }}</p>
                    <div class="addr-act">
                        <details>
                            <summary class="tlink">Edit</summary>
                            @include('account._address-form', ['a' => $a, 'action' => route('account.addresses.update', $a), 'method' => 'PUT'])
                        </details>
                        <form method="post" action="{{ route('account.addresses.delete', $a) }}" data-confirm="Remove this address?">
                            @csrf @method('DELETE')
                            <button type="submit" class="linkbtn">Remove</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>

        <details class="addr-new" @if ($addresses->isEmpty() || $errors->any()) open @endif>
            <summary class="btn blue sm"><x-icon name="plus" size="16" /> Add a new address</summary>
            @include('account._address-form', ['a' => null, 'action' => route('account.addresses.store'), 'method' => 'POST'])
        </details>
    </div>
</div>
@endsection
