@extends('layouts.backoffice')

@section('title')
{{ 'Faculty' }}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/main/backoffice/employees-page.css') }}">
<link rel="stylesheet" href="{{ asset('css/main/shared/table-common-sizes.css') }}">
@endpush

@section('content')
<div class="card content-card">
    <div class="card-body">

        <div class="row">
            <div class="col-2 flex-start">
                <h6 class="card-title m-0">Faculty Directory</h6>
            </div>
            <div class="col flex-start gx-0 flex-row">
                {{-- <div class="filter-indicators me-auto flex-start gap-2 d-hidden">
                    <div class="filter-arrow filter-arrow-head-cap px-2">
                        <i class="fas fa-filter me-2"></i>
                        <span class="text-uppercase">Filters</span>
                    </div>
                    {{ -- MONTH FILTER -- }}
                    <div class="filter-arrow filter-arrow-item">
                        <i class="fas fa-calendar-days me-2"></i>
                        <span class="text-uppercase text-truncate lbl-month-filter">Filters</span>
                    </div>
                    {{ -- EMPLOYEE FILTER -- }}
                    <div class="filter-arrow filter-arrow-item">
                        <i class="fas fa-user-tie me-2"></i>
                        <span class="text-uppercase text-truncate lbl-role-filter">Filters</span>
                    </div>
                    {{ -- LEAVE TYPE FILTER -- }}
                    <div class="filter-arrow filter-arrow-item">
                        <i class="fas fa-chart-area me-2"></i>
                        <span class="text-uppercase text-truncate lbl-leave-filter">Filters</span>
                    </div>
                    {{ -- LEAVE STATUS FILTER -- }}
                    <div class="filter-arrow filter-arrow-item">
                        <i class="fas fa-chart-line me-2"></i>
                        <span class="text-uppercase text-truncate lbl-status-filter">Filters</span>
                    </div>
                </div> --}}
            </div>
            <div class="col-4 flex-end gap-2">
                {{-- ADD BUTTON --}}
                <div class="dropdown">
                    <button class="btn btn-primary flat-button dropdown-toggle shadow-0" id="drop-btn-add"
                        data-mdb-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-plus"></i>
                        <span class="ms-1">Add</span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="drop-btn-add">
                        <li>
                            <a class="dropdown-item btn-add-employee" role="button">Create Manually</a>
                        </li>
                        {{-- <li><a class="dropdown-item" href="#">Import Sheet</a></li> --}}
                    </ul>
                </div>
            </div>
        </div>

        {{-- DATASET TABLE --}}
        <div class="w-100 position-relative overflow-hidden">
            <table class="table table-striped w-100 table-sm table-hover dataset-table" id="records-table"
                data-src-default="{{ $routes['defaultDataSource'] }}"
                data-action-delete="{{ $routes['DELETE_Employee'] }}"
                data-action-edit="{{ $routes['actionEdit'] }}">
                <thead class="user-select-none">
                    <tr>
                        <th class="record-counter sticky-header">#</th>
                        <th class="ps-2">ID no.</th>
                        <th>Employee Name</th>
                        <th class="th-120 text-center">Status</th>
                        <th class="text-center th-100">
                            <span class="text-xs bg-warning px-2 py-1 rounded-4 text-white text-capitalize">No. LATE</span>
                        </th>
                        <th class="text-center th-100">
                            <span class="text-xs bg-color-primary px-2 py-1 rounded-4 text-white text-capitalize">No. LEAVE</span>
                        </th>
                        <th class="text-center th-100">
                            <span class="text-xs bg-danger px-2 py-1 rounded-4 text-white text-capitalize">No. ABSENT</span>
                        </th>
                        <th class="sticky-header">Actions</th>
                    </tr>
                </thead>
                <tbody>{{-- CONTENT WILL COME FROM AJAX SOURCE --}}</tbody>
            </table>
        </div>
    </div>
</div>

@push('dialogs')
{{-- @include('modals.employee-form', [
'employeeType' => $empType,
'requireEmail' => true,
'postCreate' => $routes['POST_Create_Employee'], // Route for create
'postUpdate' => $routes['POST_Update_Employee'] // Route for update
]) --}}

@include('modals.create-employee', ['positions' => $positions, 'modalSetup' => $modalSetup])

<x-employee-details-dialog as="employeeDetailsModal" :modalFor="$role"
    datasource="{{ $routes['DETAILS_Employee'] }}" />
@endpush

@endsection

@push('scripts')
<script>
    //const route_deleteRecord  = "{{ $routes['DELETE_Employee'] }}";
</script>
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
<script src="{{ asset('js/main/backoffice/employee-page.js') }}"></script>
@endpush