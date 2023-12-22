@extends('layouts.base')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/scanner-overrides.css') }}"/>
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
                    <h5 class="logo-text mb-0">Uddiawan National High School</h5>
                    <h6 class="logo-sub-text mb-0">Attendance Monitoring System</h6>
                </div>
            </div>
        </div>
        <div class="col px-3 d-flex flex-row align-items-center justify-content-end gap-2 pe-0">
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

    <div class="row px-5 mx-3">
        <div class="col-5">
            {{-- SCANNER --}}
            <div id="reader" class="shadow shadow-3-strong"></div>
        </div>
        {{-- TABLE --}}
        <div class="col-7 position-relative">

            <div class="position-absolute w-100">
                <div class="bottom-scroll-fade-blur w-100 h-100 bg-danger start-0 top-0 end-0 bottom-0" style="z-index: 1200;">

                </div>
                <div class="attendance-table-wrapper shadow shadow-3-strong w-100 position-relative">
                    <div data-simplebar class="overflow-y-auto h-100">
                        <table class="table table-hover table-sm table-striped attendance-table position-relative">
                            <thead class="position-sticky top-0">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Time In</th>
                                    <th scope="col">Time Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 1; $i <= 30; $i++)
                                <tr>
                                    <th scope="row">{{ $i }}</th>
                                    @if ($i % 2 == 0)
                                        <td>Mark Cortes</td>
                                    @else
                                        <td>Jann Maglente</td>
                                    @endif
                                    <td>{{ date('g:i a') }}</td>
                                    <td></td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="row px-5 mx-3 py-3">
        <div class="col">
            <span class="opacity-75 small">&copy; {{ $footerText }}</span>
        </div>
        <div class="col"></div>
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
    <script src="{{ asset('js/effects/slide-text.js') }}"></script>
@endpush