@extends('layouts.base')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/shared/attendance-common-styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/main/backoffice/attendance-page.css') }}" />
    <style>
        .sidenav {
            width: 250px;
            max-width: 250px;
            background-color: var(--primary-color);
        }

        .sidenav .menu-items {
            overflow-y: auto;
        }

        .sidenav .logo-wrapper {
            flex: 0 0 auto; 
            background-color: var(--primary-dark);
            height: 68px;
        }

        .sidenav .nav-items-container .nav-items {
            color: white;
            opacity: 0.65;
            width: 100%;
            display: block;
            font-weight: normal;
            font-family: var(--accent-font);
            transition: 0.18s background-color ease-in-out;
        }

        .sidenav .nav-items-container .nav-items:hover {
            background-color: var(--primary-600);
            opacity: 1;
        }

        .sidenav .nav-items-container .nav-items:active {
            background-color: var(--primary-400);
            opacity: 1;
        }

        .sidenav .nav-items-container .nav-items.active {
            background-color: #FFFFFF;
        }

        .content-wrapper .title-banner {
            height: 68px;
        }

        .content-wrapper .title-banner .title-text {
            font-family: var(--accent-font);
        }
    </style>
@endpush

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/main/components/row-action-buttons.css') }}">
    @endpush
@endonce

@section('content')

<div class="row">
    
    @include('layouts.backoffice.sidenav')

    <div class="col content-wrapper overflow-hidden d-flex flex-column px-0 vh-100">
        <div data-simplebar class="overflow-y-auto nav-items-container px-3 h-100">
            
            @include('layouts.backoffice.header-bannder')

            <div class="card content-card">
                <div class="card-body">
                    <h6 class="card-title">Daily Time Records</h6>

                    <table class="table table-striped table-sm table-hover table-fixed dtr-table" data-ajax-src="{{ $ajaxDataSource }}">
                        <thead class="user-select-none">
                            <th>#</th>
                            <th>Date</th>
                            <th class="ps-4">Status</th>
                            <th>Name</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="footer text-center opacity-65 p-2">
                <small>&copy; {{ date('Y') .' '. $organizationName }}</small>
            </div>
        </div>
        {{-- <div class="sticky-bottom bg-white">
            
        </div> --}}
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/main/utils.js') }}"></script>
    <script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('js/main/backoffice/attendance-page.js') }}"></script>
@endpush