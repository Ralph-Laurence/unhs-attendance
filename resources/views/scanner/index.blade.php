@extends('layouts.base')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/overrides/scanner-overrides.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/main/scanner-page.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/effects/slide-text.css') }}"/>
@endpush

@section('content')

    <div class="row banner-wrapper px-5 mx-3 py-4">
        <div class="col px-3">
            <div class="logo-wrapper">
                <div class="logo-background me-2">
                    <img src="{{ asset('images/logo.svg') }}" alt="logo" width="48" height="48" />
                </div>
                <div class="log-text-wrapper">
                    <h5 class="logo-text mb-0">{{ $layoutTitles['header'] }}</h5>
                    <h6 class="logo-sub-text mb-0">{{ $layoutTitles['system'] }}</h6>
                </div>
            </div>
        </div>
        <div class="col px-3 d-flex flex-row align-items-center justify-content-end gap-2 pe-0">
            <small class="mb-0 mx-2 opacity-45">
                <i class="fa-solid fa-circle-info"></i>
                <span class="ms-1">About</span>
            </small>
            <small class="mb-0 mx-2 opacity-45">
                <i class="fa-solid fa-circle-question"></i>
                <span class="ms-1">Help</span>
            </small>
            {{-- CALENDAR --}}
            <div class="calendar-clock flex-center py-2 px-4">
                <h6 class="date-time-label mb-0 d-flex">
                    <div class="date-label">{{ date('M. d, Y') }}</div>
                    <div class="separator"></div>
                    <div class="time-label">
                        <span class="hour-minutes-label">{{ date('g:i') }}</span>
                        <small class="millisec-label opacity-65">{{ date(':s') }}</small>
                        <span class="meridiem-label">{{ date('a') }}</span>
                    </div>
                </h6>
            </div>
            {{-- MENU BUTTON --}}
            <div class="options-wrapper flex-center">
                <i class="fas fa-gear"></i>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center flex-column flex-lg-row flex-md-column gap-4 px-5 mx-3">
        {{-- SCANNER --}}
        <div id="reader" class="shadow shadow-3-strong"></div>
        {{-- TABLE --}}
        <div class="attendance-table-wrapper shadow shadow-3-strong flex-fill position-relative">
            <div class="overflow-hidden w-100 h-100">
                <div data-simplebar class="overflow-y-auto h-100">
                    <table class="table table-hover table-sm table-striped table-fixed attendance-table position-relative">
                        <thead class="position-sticky top-0">
                            <tr>
                                <th scope="col" style="min-width: 250px; width: 250px;" >Name</th>
                                <th scope="col">Time In</th>
                                <th scope="col">Time Out</th>
                                <th scope="col">Duration</th>
                                <th scope="col" style="max-width: 80px; width: 100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 1; $i <= 30; $i++)
                            <tr>
                                <td class="text-truncate">
                                    @if ($i % 2 == 0)
                                        {{'Mark Cortes'}}
                                    @else
                                        {{'Jann Maglente'}}
                                    @endif
                                </td>
                                <td>{{ date('g:i a') }}</td>
                                <td></td>
                                <td></td>
                                <td scope="row">{{ 'Present' }}</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="row px-5 mx-3 py-3">
        <div class="col">
            <span class="opacity-75 small">&copy; {{ $layoutTitles['footer'] }}</span>
        </div>
        <div class="col">
            <div class="text-end">
                <span class="opacity-75 small">{{ $layoutTitles['version'] }}</span>
            </div>
        </div>
    </div> --}}

    <div class="d-none beep-sounds">
        <audio src="{{ asset('audio/beep-time-in.mp3') }}" id="beep-time-in"></audio>
        <audio src="{{ asset('audio/beep-time-out.mp3') }}" id="beep-time-out"></audio>
        <audio src="{{ asset('audio/blip.mp3') }}" id="blip"></audio>
    </div>

    @include('modals.alert')
@endsection

@push('scripts')
    <script src="{{ asset('js/lib/html5-qrcode/html5-qrcode.min.v2.3.0.js') }}"></script>
    <script src="{{ asset('js/lib/momentjs/moment-with-locales.js') }}"></script>
    <script src="{{ asset('js/effects/slide-text.js') }}"></script>
    <script>
        const scannerSubmitUrl = "{{ $scannerPostURL }}";
    </script>
    <script src="{{ asset('js/main/scanner-page/scanner.js') }}"></script>
    <script src="{{ asset('js/main/scanner-page/calendar.js') }}"></script>
@endpush