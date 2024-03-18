@extends('layouts.base')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/shared/attendance-common-styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/overrides/scanner-overrides.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/main/scanner-page.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/effects/slide-text.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/main/components/loader.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/main/components/fab.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/main/components/snackbar.css') }}" />
    <style>
        #input-pin-no::placeholder,
        #input-id-no::placeholder {
            font-size: 18px;
            transform: translate3d(0, -3px, 0);
        }
    </style>
@endpush

@section('content')

    <div class="row banner-wrapper px-5 mx-3 py-4">
        <div class="col px-3">
            <div class="logo-wrapper">
                <div class="logo-background logo-background-lg me-2">
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
            <div class="dropdown">
                <div class="options-wrapper flex-center dropdown-toggle" 
                id="options-dropdown-button"
                data-mdb-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-gear"></i>
                </div>
                <ul class="dropdown-menu" aria-labelledby="options-dropdown-button">
                    <li><a class="dropdown-item" href="{{ $routes['recordsManagement'] }}">Manage Records</a></li>
                    {{-- <li><a class="dropdown-item" href="#">Another action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li> --}}
                </ul>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center flex-column flex-lg-row flex-md-column gap-4 px-5 mx-3">
        {{-- SCANNER --}}
        <div id="reader" class="shadow shadow-3-strong"></div>
        {{-- TABLE --}}
        <div class="attendance-table-wrapper shadow shadow-3-strong flex-fill position-relative">
            <div class="overflow-hidden w-100 h-100">
                <div data-simplebar class="overflow-y-auto h-100 w-100 scrollbar-parent">
                    <table class="table table-hover table-sm table-striped table-fixed w-100 attendance-table position-relative"
                        data-default-src="{{ $routes['scannerHistory'] }}">
                        <thead class="position-sticky top-0">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Time In</th>
                                <th scope="col">Time Out</th>
                                <th scope="col">Duration</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- TO BE FILLED WITH AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row px-5 mx-3 py-3">
        <div class="col">
            <span class="opacity-75 small">&copy; {{ $layoutTitles['footer'] }}</span>
        </div>
        <div class="col">
            <div class="text-end">
                <span class="opacity-75 small">{{ $layoutTitles['version'] }}</span>
            </div>
        </div>
    </div>

    <button type="button" data-mdb-toggle="modal" data-mdb-target="#pinAuthModal" data-mdb-ripple-init
        class="btn shadow shadow-4-strong btn-floating fab fab-danger position-fixed mx-4 my-5 bottom-0 end-0" 
        id="fab-pin-auth">
        <i class="fa-solid fa-key"></i>
      </button>

    <div class="d-none beep-sounds">
        <audio src="{{ asset('audio/beep-time-in.mp3') }}" id="beep-time-in"></audio>
        <audio src="{{ asset('audio/beep-time-out.mp3') }}" id="beep-time-out"></audio>
        <audio src="{{ asset('audio/blip.mp3') }}" id="blip"></audio>
    </div>

@endsection

@push('dialogs')
<div class="modal fade" data-mdb-keyboard="false" data-mdb-backdrop="static" id="pinAuthModal" tabindex="-1" aria-labelledby="pinAuthModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 user-select-none">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/modal_icon_pin_auth.png') }}" alt="icon" class="modal-icon">
                    
                    <h6 class="modal-title mb-0" id="pinAuthModalLabel">Authenticate with PIN</h6>
                </div>
                <button type="button" class="btn-close" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body px-5 py-3 opacity-75 user-select-none">
                <h6 class="text-14 text-center mb-4">
                    Having problems with the scanner?<br><br>
                    Did you misplace your QR Code?<br><br>
                    <b>Use your PIN Code instead.</b>
                </h6>
                <div class="alert alert-danger p-2 text-center pin-auth-alert-error d-hidden"></div>
                <div class="w-100 flex-center">
                    <form class="frm-pin-auth w-75" data-action-target="{{ $routes['pincodeForm'] }}" aria-autocomplete="none">
                        @csrf
                        <x-text-box as="input-id-no" placeholder="ID Number" maxlength="32" class="numeric-dash fs-3" parent-classes="mb-3" required autocomplete="new-password" aria-autocomplete="none"/>
                        <x-text-box of="password" as="input-pin-no" placeholder="PIN Code" maxlength="8" class="numeric fs-3" required autocomplete="new-password" aria-autocomplete="new-password"/>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel btn-secondary flat-button" data-mdb-ripple-init
                    data-mdb-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-ok btn-primary flat-button shadow-0" data-mdb-dismissx="modal"
                    data-mdb-ripple-init>OK</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
    <script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('js/lib/html5-qrcode/html5-qrcode.min.v2.3.0.js') }}"></script>
    <script src="{{ asset('js/lib/momentjs/moment-with-locales.js') }}"></script>
    <script src="{{ asset('js/effects/slide-text.js') }}"></script>
    <script>
        const scannerSubmitUrl = "{{ $routes['scannerPostURL'] }}";
    </script>
    <script src="{{ asset('js/main/utils.js') }}"></script>
    <script src="{{ asset('js/main/scanner-page/scanner.js') }}"></script>
    <script src="{{ asset('js/main/scanner-page/calendar.js') }}"></script>
    <script src="{{ asset('js/components/snackbar.js') }}"></script>
@endpush