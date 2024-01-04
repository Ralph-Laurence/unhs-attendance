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
            <h6 class="card-title me-auto">Daily Time Records</h6>

            {{-- RECORD DATE RANGE FILTERS --}}
            <div class="dropdown">
                <button class="btn btn-secondary flat-button dropdown-toggle shadow-0" id="record-date-dropdown-button"
                    data-mdb-toggle="dropdown" aria-expanded="false" data-mdb-auto-close="outside">
                    <span class="me-1">Today</span>
                    <i class="fas fa-chevron-down opacity-65"></i>
                </button>
                <ul class="dropdown-menu record-range-filter" aria-labelledby="options-dropdown-button">
                    <li><a class="dropdown-item daily" role="button">Today</a></li>
                    <li><a class="dropdown-item weekly" role="button">This Week</a></li>
                    <li class="dropstart">
                        <a class="dropdown-item dropdown-toggle" id="date-range-dropdown-button"
                            data-mdb-toggle="dropdown" role="button">By Month</a>
                        <ul class="dropdown-menu" aria-labelledby="date-range-dropdown-button">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <li><a class="dropdown-item" href="#">Separated link</a></li>
                        </ul>
                    </li>
                </ul>
            </div>

            {{-- EMPLOYEE ROLE FILTERS --}}
            <div class="dropdown">
                <button class="btn btn-secondary flat-button dropdown-toggle shadow-0" id="role-filters-dropdown-button"
                    data-mdb-toggle="dropdown" aria-expanded="false">
                    <span class="me-1">All</span>
                    <i class="fas fa-chevron-down opacity-65"></i>
                </button>
                <ul class="dropdown-menu role-filters" aria-labelledby="role-filters-dropdown-button">
                    <li><a class="dropdown-item" role="button" data-role="all">All</a></li>
                    @foreach ($roleFilters as $roleKey => $roleValue)
                    {{-- The role key comes from the backend, which are hashed.
                    We will use those hashes for identifying the roles --}}
                    <li><a class="dropdown-item" role="button" data-role="{{ $roleKey }}">{{ $roleValue }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- ADD BUTTON --}}
            <div class="dropdown">
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
            </div>
        </div>

        {{-- DATASET TABLE --}}
        <table class="table table-striped table-sm table-hover table-fixed dataset-table"
            data-src-default="{{ $routes['filter_thisDay'] }}" data-src-weekly="{{ $routes['filter_thisWeek'] }}">
            <thead class="user-select-none">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th class="ps-2">Status</th>
                    <th>Name</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Duration</th>
                    <th>Actions</th>
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