@extends('layouts.backoffice')

@section('title')
{{'Audit Trails'}}
@endsection


@section('content')

<div class="card content-card">
    <div class="card-body">
        <div class="row">

            {{-- TITLE TEXT --}}
            <div class="col-2 flex-start">
                <h6 class="card-title m-0">Activity Logs</h6>
            </div>

            {{-- FILTER INDICATORS --}}
            <div class="col flex-start gx-0 flex-row">
                <div class="filter-indicators me-auto flex-start gap-2 d-hidden">
                    <div class="filter-arrow filter-arrow-head-cap px-2">
                        <i class="fas fa-filter me-2"></i>
                        <span class="text-uppercase">Filters</span>
                    </div>
                    {{-- AUDIT TYPE FILTER --}}
                    <div class="filter-arrow filter-arrow-item">
                        <i class="fas fa-calendar-days me-2"></i>
                        <span class="text-uppercase text-truncate lbl-audit-type-filter">Filters</span>
                    </div>
                    {{-- EMPLOYEE FILTER -- }}
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
                    </div> --}}
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
                                    {{-- DATE FILTERS --}}
                                    <small> 
                                        <i class="fas fa-calendar-days me-1"></i> Date
                                    </small>
                                    {{-- <x-drop-list as="input-month-filter" :items="$monthOptions" text="{{ date('F') }}" 
                                    default="{{ date('n') }}" button-classes="w-100"/> --}}
                                </div>
                                <div class="col-6">
                                    {{-- TIME FILTERS --}}
                                    <small> 
                                        <i class="fas fa-clock me-1"></i> Time
                                    </small>
                                    {{-- <x-drop-list as="input-role-filter" :items="$datasetFilters['role']" 
                                    text="{{ $datasetFilters['defaultText'] }}" button-classes="w-100"
                                    default="{{ $datasetFilters['defaultValue'] }}"/> --}}
                                </div>
                            </div>
                            {{-- <div class="row">
                                <div class="col-6">
                                    {{ -- LEAVE TYPE FILTERS -- }}
                                    <small> 
                                        <i class="fas fa-chart-area me-1"></i> Leave Type
                                    </small>
                                    <x-drop-list as="input-leave-filter" :items="$datasetFilters['leaveType']" 
                                    text="{{ $datasetFilters['defaultText'] }}"  button-classes="w-100"
                                    default="{{ $datasetFilters['defaultValue']  }}"/>
                                </div>
                                <div class="col-6">
                                    {{ -- LEAVE STATUS FILTERS -- }}
                                    <small> 
                                        <i class="fas fa-chart-line me-1"></i> Leave Status
                                    </small>
                                    <x-drop-list as="input-status-filter" :items="$datasetFilters['leaveStatus']" 
                                    text="{{ $datasetFilters['defaultText'] }}"  button-classes="w-100"
                                    default="{{ $datasetFilters['defaultValue']  }}"/>
                                </div>
                            </div> --}}

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
    
            </div>
        </div>

        {{-- DATASET TABLE --}}
        <div class="w-100 position-relative overflow-hidden">
            <table class="table table-striped table-fixed w-100 table-sm table-hover dataset-table" id="records-table"
                data-src-datasource="{{ $routes['getAll'] }}">
                <thead class="user-select-none">
                    <tr>
                        <th class="record-counter sticky-header">#</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>{{-- CONTENT WILL COME FROM AJAX SOURCE --}}</tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('js/main/tests/audits-page.js') }}"></script>
@endpush