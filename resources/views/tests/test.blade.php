@extends('layouts.base')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/components/loader.css') }}">
@endpush
@section('content')
    <div class="loader hidden"></div>
@endsection