@extends('layouts.backoffice')

@section('title')
{{'Leave'}}
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
                <span>Leave Requests</span>
                <i class="fas fa-caret-right mx-2 opacity-60"></i>
                <span class="opacity-90 lbl-attendance-range text-14 text-primary-dark"></span>
                <i class="fas fa-caret-right mx-2 opacity-60"></i>
                <span class="opacity-90 lbl-employee-filter text-14 text-primary-dark"></span>
            </h6>

            {{-- RECORD MONTH RANGE FILTERS --}}
            <div class="dropdown">
                <button class="btn btn-secondary flat-button dropdown-toggle shadow-0" 
                    id="role-filters-dropdown-button" data-mdb-toggle="dropdown" aria-expanded="false" disabled>
                    <span class="me-1 button-text">{{ date('F') }}</span>
                    <i class="fas fa-chevron-down opacity-65"></i>
                </button>
                <ul class="dropdown-menu role-filters" aria-labelledby="role-filters-dropdown-button">
                    @for ($i = 1; $i <= 12; $i++)
                    <li><a class="dropdown-item" role="button" data-month="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</a></li>
                    @endfor
                </ul>
            </div>

            {{-- EMPLOYEE ROLE FILTERS --}}
            <div class="dropdown">
                <button class="btn btn-secondary flat-button dropdown-toggle shadow-0" 
                    id="role-filters-dropdown-button" data-mdb-toggle="dropdown" aria-expanded="false" disabled>
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
            <div class="dropdown">
                <button class="btn btn-primary flat-button dropdown-toggle shadow-0" id="add-record-dropdown-button"
                    data-mdb-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Add</span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="options-dropdown-button">
                    <li>
                        <a class="dropdown-item" data-mdb-toggle="modal" data-mdb-target="#leaveRequestForm"
                            role="button">Create Manually</a>
                    </li>
                    <li><a class="dropdown-item" href="#">Import Sheet</a></li>
                </ul>
            </div>
        </div>

        {{-- DATASET TABLE --}}
        <table class="table table-striped table-fixedx table-sm table-hover dataset-table"
            id="records-table"
            data-src-default="{{ $routes['ajax_get_all'] }}"
            data-src-emp-ids="{{ $routes['ajax_load_empids'] }}">
            <thead class="user-select-none">
                <tr>
                    <th>#</th>
                    <th class="ps-2">Employee Name</th>
                    <th>Leave Type</th>
                    <th>Date From</th>
                    <th>Date End</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>{{-- CONTENT WILL COME FROM AJAX SOURCE --}}</tbody>
        </table>
    </div>
</div>

@endsection

@push('dialogs')
<div class="modal fade" data-mdb-backdrop="static" id="leaveRequestForm" tabindex="-1" aria-labelledby="leaveRequestFormLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/modal_icon_leave.png') }}" width="28" height="28" alt="icon" class="modal-icon" />
                    <h6 class="modal-title mb-0" id="leaveRequestFormLabel">Employee Leave</h6>
                </div>
                <button type="button" class="btn-close" data-mdb-ripple-init data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger text-center py-2 text-14 error-box d-hidden"></div>
                <form action="" method="POST" class="frm-leave-request">
                    <div class="container">
                        <div class="row mb-3">
                            <div class="col">
                                <x-text-box as="input-id-no" placeholder="Employee ID" maxlength="32" aria-autocomplete="none" 
                                leading-icon-s="fa-user" suggest readonly/>
                            </div>
                            <div class="col">
                                <x-text-box as="input-employee-name" placeholder="Name" maxlength="64" aria-autocomplete="none" 
                                readonly parent-classes="opacity-75"/>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <h6 class="text-14">Start Date</h6>
                                <x-moment-picker as="input-leave-start" />
                            </div>
                            <div class="col">
                                <h6 class="text-14">End Date</h6>
                                <x-moment-picker as="input-leave-end" />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <h6 class="text-14">Leave Type</h6>
                                <x-drop-list :items="$leaveTypes" button-classes="w-100"/>
                            </div>
                            <div class="col"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel btn-secondary flat-button" data-mdb-ripple-init
                    data-mdb-dismissx="modal">Cancel</button>
                <button type="button" class="btn btn-save btn-primary flat-button shadow-0" data-mdb-dismissx="modal"
                    data-mdb-ripple-init>Save</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const route_deleteRecord = "{{ $routes['deleteRoute'] }}";
</script>
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
{{-- <script src="{{ asset('js/main/backoffice/leave-request-page.js') }}"></script> --}}
<script src="{{ asset('js/components/auto-suggest-field.js') }}"></script>
<script src="{{ asset('js/main/tests/leave-request-page.js') }}"></script>
@endpush