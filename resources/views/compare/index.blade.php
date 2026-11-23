@extends('layouts.app')

@section('title', 'Compare products - SafeBuy')

@section('content')
    @include('partials.page-head', ['title' => 'Compare products', 'crumbs' => [['Compare', null]]])
    <div class="wrap sec-in" data-compare-page>
        @include('compare._table')
    </div>
@endsection
