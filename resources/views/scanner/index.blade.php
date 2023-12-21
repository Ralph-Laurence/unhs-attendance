@extends('layouts.base')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/scanner.css') }}">
@endpush

@section('content')

    <div class="row banner-wrapper px-5 mx-3 py-4">
        <div class="col px-3">
            <div class="logo-wrapper">
                <div class="logo-background me-2">
                    <img src="{{ asset('images/logo.svg') }}" alt="logo" width="48" height="48" />
                </div>
                <div class="log-text-wrapper">
                    <h5 class="logo-text mb-0">Uddiawan National High School</h5>
                    <h6 class="logo-sub-text mb-0">Attendance Monitoring System</h6>
                </div>
            </div>
        </div>
        <div class="col px-3 d-flex flex-row align-items-center justify-content-end gap-2">
            {{-- CALENDAR --}}
            <div class="calendar-clock flex-center py-2 px-4">
                <h6 class="date-time-label mb-0">
                    <span class="date-label">{{ date('M. d, Y') }}</span>
                    <span class="mx-1 opacity-25">|</span>
                    <span class="time-label">{{ date('g:i a') }}</span>
                </h6>
            </div>
            {{-- MENU BUTTON --}}
            <div class="options-wrapper flex-center">
                <i class="fas fa-gear"></i>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            {{-- SCANNER --}}
            <div style="width: 500px; height: 500px;" id="reader"></div>
        </div>
        <div class="col">
            {{-- TABLE --}}
            <textarea id="output" cols="50" rows="10" readonly></textarea>
            <h5 class="sec-ctr"></h5>
        </div>
    </div>

    <div class="d-none beep-sounds">
        <audio src="{{ asset('audio/beep-time-in.mp3') }}" id="beep-time-in"></audio>
        <audio src="{{ asset('audio/beep-time-out.mp3') }}" id="beep-time-out"></audio>
        <audio src="{{ asset('audio/blip.mp3') }}" id="blip"></audio>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/lib/html5-qrcode/html5-qrcode.min.v2.3.0.js') }}"></script>
    <script src="{{ asset('js/lib/momentjs/moment-with-locales.js') }}"></script>
    <script src="{{ asset('js/main/queue.js') }}"></script>
    <script src="{{ asset('js/main/scanner.js') }}"></script>
@endpush