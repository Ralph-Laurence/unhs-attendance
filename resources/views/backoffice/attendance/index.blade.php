@extends('layouts.backoffice')

@section('title')
{{'Attendance'}}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/main/shared/attendance-common-styles.css') }}" />
<link rel="stylesheet" href="{{ asset('css/main/backoffice/attendance-page.css') }}" />
@endpush

@section('content')

<div class="card content-card">
    <div class="card-body">

        {{-- TABLE TITLE HEADER --}}
        <div class="d-flex align-items-center gap-1">
            <h6 class="card-title me-auto">
                <span>Daily Time Records</span>
                <i class="fas fa-caret-right mx-2 opacity-60"></i>
                <span class="opacity-90 lbl-attendance-range text-14 text-primary-dark"></span>
                <i class="fas fa-caret-right mx-2 opacity-60"></i>
                <span class="opacity-90 lbl-employee-filter text-14 text-primary-dark"></span>
            </h6>

            {{-- STATUS FILTERS --}}
            {{-- <div class="dropdown">
                <button class="btn btn-secondary flat-button dropdown-toggle shadow-0"
                    id="status-filters-dropdown-button" data-mdb-toggle="dropdown" aria-expanded="false" disabled>
                    <i class="fas fa-stopwatch me-1 opacity-65"></i>
                    <span class="me-1 button-text">All</span>
                    <i class="fas fa-chevron-down opacity-65"></i>
                </button>
                <ul class="dropdown-menu status-filters" aria-labelledby="status-filters-dropdown-button">
                    <li><a class="dropdown-item selected-option" role="button" data-role="All">All</a></li>
                </ul>
            </div> --}}

            {{-- RECORD DATE RANGE FILTERS --}}
            @include('components.record-range-filters')

            {{-- EMPLOYEE ROLE FILTERS --}}
            <div class="dropdown">
                <button class="btn btn-secondary flat-button dropdown-toggle shadow-0" id="role-filters-dropdown-button"
                    data-mdb-toggle="dropdown" aria-expanded="false" disabled>
                    <i class="fas fa-users me-1 opacity-65"></i>
                    <span class="me-1 button-text">All</span>
                    <i class="fas fa-chevron-down opacity-65"></i>
                </button>
                <ul class="dropdown-menu role-filters" aria-labelledby="role-filters-dropdown-button">
                    <li><a class="dropdown-item selected-option" role="button" data-role="All">All</a></li>
                    @foreach ($roleFilters as $role)
                    <li><a class="dropdown-item" role="button" data-role="{{ $role }}">{{ $role }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- ADD BUTTON --}}
            {{-- <div class="dropdown">
                <button class="btn btn-primary flat-button dropdown-toggle shadow-0" id="add-record-dropdown-button"
                    data-mdb-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Add</span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="options-dropdown-button">
                    <li><a class="dropdown-item" href="{{ $routes['scannerRoute'] }}">Scan QR Code</a></li>
                    <li>
                        <a class="dropdown-item" data-mdb-toggle="modal" data-mdb-target="#attendanceFormModal"
                            role="button">Create Manually</a>
                    </li>
                    <li><a class="dropdown-item" href="#">Import Sheet</a></li>
                </ul>
            </div> --}}
        </div>

        {{-- DATASET TABLE --}}
        <table class="table table-striped table-sm table-hover table-fixed dataset-table"
            data-src-default="{{ $routes['ajax_get_all'] }}">
            <thead class="user-select-none">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th class="ps-2">Status</th>
                    <th>Name</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Duration</th>
                    {{-- <th>Actions</th> --}}
                </tr>
            </thead>
            <tbody>{{-- CONTENT WILL COME FROM AJAX SOURCE --}}</tbody>
        </table>
    </div>
</div>

@endsection

@push('dialogs')
@include('modals.attendance-form')
@endpush

@push('scripts')
<script>
    const route_deleteRecord = "{{ $routes['deleteRoute'] }}";
</script>
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
<script src="{{ asset('js/main/backoffice/attendance-page.js') }}"></script>
@endpush