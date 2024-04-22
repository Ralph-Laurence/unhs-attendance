@extends('layouts.auth.base')

@php
    use App\Http\Utils\Constants;

    $orgName    = Constants::OrganizationName;
    $system     = Constants::SystemName;
    $version    = Constants::BuildVersion;
@endphp

@section('content')
<div class="bg-image"></div>
<div class="position-relative d-flex align-items-center justify-content-center auth-container flex-column w-100 h-100">

    
    <div class="card login-card shadow-4-strong p-4">
        <div class="flex-center mb-2 gap-1">
            <img src="{{ asset('images/logo.svg') }}" alt="logo" width="45" height="45"/>
            <div class="d-flex flex-column">
                <h6 class="mb-1 text-sm text-uppercase fw-bold letter-sp-1 opacity-85">{{ $orgName }}</h6>
                <h6 class="text-sm ms-3 opacity-50 mb-1">{{ $system }}</h6>
            </div>
        </div>
        <h6 class="text-center my-4 text-primary-dark">Employee Login</h6>
        <form method="POST" action="{{ $postAction }}" autocomplete="new-password">
            @csrf
            {{-- @if ($errors->any())
                @dd($errors)
            @endif --}}
            <input type="hidden" name="type" value="1">
            <x-text-box as="idno" placeholder="ID Number" maxlength="32" parent-classes="mb-3"
                leading-icon-s="fa-fingerprint" aria-autocomplete="none" />

            <x-text-box as="pin" of="password" placeholder="Pin Code" maxlength="32" parent-classes="mb-3"
                leading-icon-s="fa-key" aria-autocomplete="none" />

            <div class="d-flex flex-row align-items-center">
                {{-- <p class="opacity-60 m-0">
                    <small>Forgot Pin?</small>
                </p>
                <a href="{{ route('password.request') }}" class="mx-2 link-primary">
                    <small>Reset</small>
                </a> --}}
                <button name="login" class="btn btn-primary flat-button login-btn shadow-0 ms-auto">Login</button>
            </div>
        </form>
    </div>
</div>

<div class="sticky-bottom text-end">
    <small class="version text-white text-sm opacity-80">{{ $version }}</small>
</div>
@endsection