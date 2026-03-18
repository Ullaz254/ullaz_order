{{-- cabservice-live: uses cabbooking-single-module with store layout (header, footer, theme). Do not remove. --}}
<!-- view: frontend.booking.cabservice-live -->
@extends('layouts.store', ['title' => __('Cab Service')])
@section('css')
    <link href="{{ asset('assets/css/azul.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    @include('frontend.booking.cabbooking-single-module')
@endsection
