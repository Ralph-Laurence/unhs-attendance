@extends('layouts.base')
@section('title')
{{'Attendance'}}
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/components/loader.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/main/shared/attendance-common-styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/main/backoffice/attendance-page.css') }}" />
    <style>
        

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

                    <div class="d-flex align-items-center">
                        <h6 class="card-title me-auto">Daily Time Records</h6>
                        <div class="dropdown">
                            <button class="btn btn-primary flat-button dropdown-toggle shadow-0" id="add-record-dropdown-button" data-mdb-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-plus"></i>
                                <span class="ms-1">Add</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="options-dropdown-button">
                                <li><a class="dropdown-item" href="{{ $scannerRoute }}">Scan QR Code</a></li>
                                <li>
                                    <a class="dropdown-item" data-mdb-toggle="modal" data-mdb-target="#attendanceFormModal" role="button">Create Manually</a>
                                </li>
                                <li><a class="dropdown-item" href="#">Import Sheet</a></li>
                            </ul>
                        </div>
                    </div>

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

@include('modals.attendance-form')

@endsection

@push('scripts')
    <script>
        const route_deleteRecord = "{{ $deleteRoute }}";
    </script>
    <script src="{{ asset('js/main/utils.js') }}"></script>
    <script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
    <script src="{{ asset('js/main/backoffice/attendance-page.js') }}"></script>
@endpush