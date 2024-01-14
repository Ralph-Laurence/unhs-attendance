{{-- 
    Laravel Fortify Adopted From:    
    https://gitlab.com/penguindigital/laravel-admin-dashboard-starter/-/snippets/2016100 
--}}
@extends('layouts.auth.base')

@php
use App\Http\Utils\Constants;
use Illuminate\Support\Facades\Lang;

$orgName    = Constants::OrganizationName;
$system     = Constants::SystemName;
$version    = Constants::BuildVersion;

$non_existent_email = Lang::get('passwords.user');
$email_sent     = Lang::get('passwords.sent');
$show_success   = false;

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
 
        @php
            // Check for error messages returned by the Message Bag. 
            // Then find the 'email' message key
            foreach ($errors->getMessages() as $key => $message)
            {
                // If the message returned is a "non existent email" message,
                // we should not show it to prevent giving the hackers
                // a hint for enumerating the email or users. We must mask or
                // disguise the error message as a success message instead.
                if ($key == 'email' && in_array($non_existent_email, $message)) // $message[0]
                {
                    $show_success = true;
                    break;
                }
            }
        @endphp
        {{-- session('status') indeed means successful.
            this time, we force the success message to show up 
            wether the request was successful or not
        --}}
        @if (session('status') || $show_success !== false)
        
            <div class="alert alert-success p-2 my-4" role="alert">
                <small>{{ $email_sent }}</small>
            </div> 
            <a href="{{ route('login') }}" class="mx-2 link-primary text-center">
                <small>Continue to Login</small>
            </a>
        @else
            <h6 class="text-center my-4 text-primary-dark">Forgot Password</h6>
            <form method="POST" action="{{ route('password.request') }}" autocomplete="new-password">
                @csrf
                <label class="text-14 opacity-75 mb-1" for="email">Please enter your email address so that we can send you a password reset link.</label>
                <x-text-box as="email" placeholder="Email" maxlength="32" parent-classes="mb-3" aria-autocomplete="none" 
                leading-icon-s="fa-envelope"/>

                <div class="d-flex flex-row align-items-center">
                    <a href="{{ route('login') }}" class="mx-2 link-primary">
                        <small>Login instead</small>
                    </a>
                    <button name="login" class="btn btn-primary flat-button login-btn shadow-0 ms-auto">Reset</button>
                </div>
            </form>
        @endif
    </div>
</div>

<div class="sticky-bottom text-end">
    <small class="version text-white text-sm opacity-80">{{ $version }}</small>
</div>
@endsection