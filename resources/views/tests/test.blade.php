@extends('layouts.base')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/components/loader.css') }}">
@endpush
@section('content')
    {{-- <div class="loader hidden"></div> --}}
    <x-text-box as="test" placeholder="ID Number" maxlength="32" trailing-icon="fa-calendar-days" class="alpha-dash-dot"/>
@endsection