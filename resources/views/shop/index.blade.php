@extends('layouts.app')

@section('title', $title.' - SafeBuy')

@section('content')
    @include('partials.page-head', ['title' => $title, 'crumbs' => $crumbs])

    <div class="wrap shop" data-shop>
        @include('shop._main')
    </div>
@endsection
