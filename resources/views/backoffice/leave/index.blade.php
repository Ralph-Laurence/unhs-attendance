@extends('layouts.backoffice')

@section('title')
{{'Leave'}}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/main/shared/attendance-common-styles.css') }}" />
<link rel="stylesheet" href="{{ asset('css/main/backoffice/leave-request-page.css') }}" />
@endpush

@section('content')

<div class="card content-card">
    <div class="card-body">

        <div class="row">
            <div class="col-2 flex-start">
                <h6 class="card-title m-0">Leave Requests</h6>
            </div>
            <div class="col flex-start gx-0 flex-row">
                <div class="filter-indicators me-auto flex-start gap-2 d-hidden">
                    <div class="filter-arrow filter-arrow-head-cap px-2">
                        <i class="fas fa-filter me-2"></i>
                        <span class="text-uppercase">Filters</span>
                    </div>
                    {{-- MONTH FILTER --}}
                    <div class="filter-arrow filter-arrow-item">
                        <i class="fas fa-calendar-days me-2"></i>
                        <span class="text-uppercase text-truncate lbl-month-filter">Filters</span>
                    </div>
                    {{-- EMPLOYEE FILTER --}}
                    <div class="filter-arrow filter-arrow-item">
                        <i class="fas fa-user-tie me-2"></i>
                        <span class="text-uppercase text-truncate lbl-role-filter">Filters</span>
                    </div>
                    {{-- LEAVE TYPE FILTER --}}
                    <div class="filter-arrow filter-arrow-item">
                        <i class="fas fa-chart-area me-2"></i>
                        <span class="text-uppercase text-truncate lbl-leave-filter">Filters</span>
                    </div>
                    {{-- LEAVE STATUS FILTER --}}
                    <div class="filter-arrow filter-arrow-item">
                        <i class="fas fa-chart-line me-2"></i>
                        <span class="text-uppercase text-truncate lbl-status-filter">Filters</span>
                    </div>
                </div>
            </div>
            <div class="col-4 flex-end gap-2">

                {{-- FILTERS BUTTON --}}
                <div class="dropdown filter-options-dialog">
                    <button class="btn btn-secondary flat-button shadow-0" 
                        id="filters-dropdown-button" data-mdb-auto-close="outside" 
                        data-mdb-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-filter opacity-75"></i>
                        <span class="ms-1">Filter</span>
                    </button>
                    <div class="dropdown-menu p-2 shadow shadow-4-strong user-select-none" 
                         aria-labelledby="filters-dropdown-button" style="width: 320px;">
                        <div class="container">
                            <div class="d-flex align-items-center mb-3">
                                <h6 class="text-14 fw-bold my-0 me-auto">
                                    <i class="fas fa-gear me-1"></i>
                                    Select Record Filters
                                </h6>
                                <button class="btn btn-close shadow-0"></button>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    {{-- RECORD MONTH RANGE FILTERS --}}
                                    <small> 
                                        <i class="fas fa-calendar-days me-1"></i> Requested On
                                    </small>
                                    <x-drop-list as="input-month-filter" :items="$monthOptions" text="{{ date('F') }}" 
                                    default="{{ date('n') }}" button-classes="w-100"/>
                                </div>
                                <div class="col-6">
                                    {{-- EMPLOYEE ROLE FILTERS --}}
                                    <small> 
                                        <i class="fas fa-user-tie me-1"></i> Employee
                                    </small>
                                    <x-drop-list as="input-role-filter" :items="$datasetFilters['role']" 
                                    text="{{ $datasetFilters['defaultText'] }}" button-classes="w-100"
                                    default="{{ $datasetFilters['defaultValue'] }}"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    {{-- LEAVE TYPE FILTERS --}}
                                    <small> 
                                        <i class="fas fa-chart-area me-1"></i> Leave Type
                                    </small>
                                    <x-drop-list as="input-leave-filter" :items="$datasetFilters['leaveType']" 
                                    text="{{ $datasetFilters['defaultText'] }}"  button-classes="w-100"
                                    default="{{ $datasetFilters['defaultValue']  }}"/>
                                </div>
                                <div class="col-6">
                                    {{-- LEAVE STATUS FILTERS --}}
                                    <small> 
                                        <i class="fas fa-chart-line me-1"></i> Leave Status
                                    </small>
                                    <x-drop-list as="input-status-filter" :items="$datasetFilters['leaveStatus']" 
                                    text="{{ $datasetFilters['defaultText'] }}"  button-classes="w-100"
                                    default="{{ $datasetFilters['defaultValue']  }}"/>
                                </div>
                            </div>
                            <hr class="my-3 opacity-10">
                            <div class="row">
                                <div class="col"></div>
                                <div class="col">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <button class="btn shadow-0 flat-button btn-clear btn-secondary">Clear</button>
                                        <button class="btn shadow-0 flat-button btn-apply btn-primary">Apply</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
                {{-- ADD BUTTON --}}
                <button class="btn btn-primary flat-button shadow-0" id="btn-add-leave-record">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Add</span>
                </button>
            </div>
        </div>

        <div class="page-length-controls">
            <x-table-length-pager as="table-page-len"/>
        </div>
        
        {{-- DATASET TABLE --}}
        <div class="w-100 position-relative overflow-hidden">
            <table class="table table-striped table-fixed w-100 table-sm table-hover dataset-table" id="records-table"
                data-src-emp-ids="{{ $routes['ajax_load_empids'] }}"
                data-src-emp-nos="{{ $routes['getEmpNos'] }}">
                <thead class="user-select-none">
                    <tr>
                        <th class="record-counter sticky-header">#</th>
                        <th class="ps-2">Employee Name</th>
                        <th>Leave Type</th>
                        <th>Date From</th>
                        <th>Date End</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th class="sticky-header">Action</th>
                    </tr>
                </thead>
                <tbody>{{-- CONTENT WILL COME FROM AJAX SOURCE --}}</tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('dialogs')
<div class="modal fade" data-mdb-backdrop="static" data-mdb-keyboard="false"
    id="leaveRequestModal" tabindex="-1" aria-labelledby="leaveRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/modal_icon_leave.png') }}" width="28" height="28" alt="icon" class="modal-icon" />
                    <h6 class="modal-title mb-0" id="leaveRequestModalLabel">Employee Leave</h6>
                </div>
                <button type="button" class="btn-close btn-cancel" data-mdb-ripple-init data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger text-center py-2 text-14 error-box d-hidden"></div>
                <form data-post-target="{{ $routes['insertPostRoute'] }}" method="post"
                      id="frm-leave-request">
                      <x-text-field as="input-update-key" readonly parent-classes="d-none"/>
                    <div class="container">
                        <div class="row mb-3">
                            <div class="col">
                                <x-type-ahead as="input-id-no" leading-icon="fa-fingerprint" placeholder="Employee ID" maxlength="32" required/>
                            </div>
                            <div class="col">
                                <x-text-field as="input-employee-name" placeholder="Name" maxlength="64" aria-autocomplete="none" 
                                readonly parent-classes="opacity-75" data-mdb-toggle="tooltip" data-mdb-placement="bottom"
                                data-mdb-title="This field is read-only. Please enter an ID number from the &quot;Employee ID&quot; field to load the matching employee name."/>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <h6 class="text-14">Start Date</h6>
                                <x-date-picker as="input-leave-start" required/>
                            </div>
                            <div class="col">
                                <h6 class="text-14">End Date</h6>
                                <x-date-picker as="input-leave-end" required/>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <h6 class="text-14">Leave Type</h6>
                                <x-drop-list as="input-leave-type" :items="$leaveTypes" button-classes="w-100"/>
                            </div>
                            <div class="col-6">
                                {{-- <h6 class="text-14">Leave Status</h6>
                                <x-drop-list as="input-leave-status" :items="$leaveStatuses" button-classes="w-100"
                                text="{{ $defaultLeaveStatus['label'] }}"
                                default="{{ $defaultLeaveStatus['value'] }}" /> --}}
                            </div>
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

<x-gijgo-driver type="date" />

@push('scripts')
<script>
    const route_deleteRecord    = "{{ $routes['deleteRoute'] }}";
    const route_editRecord      = "{{ $routes['editRoute'] }}";
    const route_approveRequest  = "{{ $routes['approveRoute'] }}";
    const route_rejectRequest   = "{{ $routes['rejectRoute'] }}";
    const route_getDataSource   = "{{ $routes['ajax_get_all'] }}";
    // security concerns : https://stackoverflow.com/questions/32584700/how-to-prevent-laravel-routes-from-being-accessed-directly-i-e-non-ajax-reques
</script>
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
<script src="{{ asset('js/components/auto-suggest-field.js') }}"></script>
<script src="{{ asset('js/main/backoffice/leave-request-page.js') }}"></script>
@endpush