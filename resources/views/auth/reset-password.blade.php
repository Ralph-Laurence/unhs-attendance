@extends('layouts.auth.base')

@php
use App\Http\Utils\Constants;

$orgName = Constants::OrganizationName;
$system = Constants::SystemName;
$version = Constants::BuildVersion;

@endphp

@section('content')
<div class="bg-image"></div>
<div class="position-relative d-flex align-items-center justify-content-centerx auth-container flex-column w-100 h-100">

    <div class="card login-card shadow-4-strong p-4">
        <div class="flex-center mb-2 gap-1">
            <img src="{{ asset('images/logo.svg') }}" alt="logo" width="45" height="45" />
            <div class="d-flex flex-column">
                <h6 class="mb-1 text-sm text-uppercase fw-bold letter-sp-1 opacity-85">{{ $orgName }}</h6>
                <h6 class="text-sm ms-3 opacity-50 mb-1">{{ $system }}</h6>
            </div>
        </div>

        @if (session('status'))

        <div class="alert alert-success p-2 my-4" role="alert">
            <small>{{ session('status') }}</small>
        </div>
        @else

        <h6 class="text-center my-4 text-primary-dark">Update Password</h6>
        <form method="POST" action="{{ route('password.update') }}" autocomplete="new-password">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}" />

            <div class="alert alert-info">
                <small class="text-sm d-block opacity-75 mb-1" for="email">
                    <i class="fas fa-caret-right me-1 text-primary-dark"></i>
                    We will use your email address so that we can verify that it is really you.
                </small>
                <small class="text-sm d-block opacity-75 mb-0" for="email">
                    <i class="fas fa-caret-right me-1 text-primary-dark"></i>
                    Please choose a password that is easy to remember and at least 8 characters long.
                </small>
            </div>

            <x-text-box as="email" readonly placeholder="Email" maxlength="32" parent-classes="mb-3"
                aria-autocomplete="none" leading-icon-s="fa-envelope" value="{{ $request->email }}" />

            <x-text-box of="password" as="password" placeholder="New Password" maxlength="32" parent-classes="mb-3"
                aria-autocomplete="none" leading-icon-s="fa-key" />

            <x-text-box of="password" as="password_confirmation" placeholder="Retype Password" maxlength="32"
                parent-classes="mb-3" aria-autocomplete="none" leading-icon-s="fa-key" />

            <div class="d-flex flex-row align-items-center">
                <a href="{{ route('login') }}" class="mx-2 link-primary">
                    <small>Login instead</small>
                </a>
                <button name="reset" class="btn btn-primary flat-button reset-btn shadow-0 ms-auto">Update</button>
            </div>
        </form>
        @endif
    </div>
</div>

<div class="sticky-bottom text-end">
    <small class="version text-white text-sm opacity-80">{{ $version }}</small>
</div>
@endsection