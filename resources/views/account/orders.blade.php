@extends('layouts.app')

@section('title', 'My orders - SafeBuy')

@section('content')
@include('partials.page-head', ['title' => 'My Orders', 'crumbs' => [['My account', route('account')], ['Orders', null]]])
<div class="wrap sec-in acc">
    @include('account._shell', ['active' => 'orders'])
    <div class="acc-main">
        @include('account._orders-table', ['orders' => $orders])
        @if ($orders->hasPages())
            <nav class="pager" aria-label="Pages">
                @foreach ($orders->getUrlRange(1, $orders->lastPage()) as $n => $u)
                    @if ($n === $orders->currentPage())<span class="pg on" aria-current="page">{{ $n }}</span>@else<a class="pg" href="{{ $u }}">{{ $n }}</a>@endif
                @endforeach
            </nav>
        @endif
    </div>
</div>
@endsection
