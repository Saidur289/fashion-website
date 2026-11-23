@extends('layouts.app')

@section('title', 'Our Team — '.config('safebuy.shop_name', 'SafeBuy'))
@section('description', 'Meet the people behind SafeBuy who verify authenticity, curate products, and support every order.')

@section('content')
@include('partials.page-head', [
    'title' => 'Our Team',
    'crumbs' => [['About Us', route('about')], ['Our Team', null]],
])

<div class="wrap cp-wrap">
    <p class="cp-lead">Every SafeBuy order is checked, packed, and supported by people who genuinely care about authenticity and great service.</p>

    @if ($team->isEmpty())
        <div class="empty">
            <h2>Team profiles coming shortly</h2>
            <a class="btn" href="{{ route('about') }}">Back to About Us</a>
        </div>
    @else
        <div class="team-grid" data-stagger="0.07">
            @foreach ($team as $member)
                <div class="team-card">
                    <div class="team-avatar">
                        @if ($member->photo)
                            <img src="{{ asset($member->photo) }}" alt="{{ $member->name }}" loading="lazy" decoding="async">
                        @else
                            <span>{{ strtoupper(mb_substr($member->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <h3>{{ $member->name }}</h3>
                    <small>{{ $member->role }}</small>
                    @if ($member->bio)<p>{{ $member->bio }}</p>@endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
