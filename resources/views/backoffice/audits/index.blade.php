@extends('layouts.backoffice')

@section('title')
{{'Audit Trails'}}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/main/backoffice/audits-page.css') }}" />
@endpush

@section('content')

<div class="card content-card">
    <div class="card-body">
        <div class="row">

            {{-- TITLE TEXT --}}
            <div class="col flex-start">
                <h6 class="card-title m-0">Activity Logs</h6>
            </div>
            <div class="col flex-row flex-end gap-2">

                {{-- <div class="search-wrapper" style="max-width: 240px">
                    <x-text-field as="search-filter" placeholder="Enter search term" />
                </div> --}}

                {{-- FILTERS BUTTON --}}
                <div class="dropdown filter-options-dialog">
                    <button class="btn btn-primary flat-button shadow-0" id="filters-dropdown-button"
                        data-mdb-auto-close="false" data-mdb-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-filter opacity-75"></i>
                        <span class="ms-1">Filter</span>
                    </button>
                    <div class="dropdown-menu p-2 shadow shadow-4-strong user-select-none"
                        aria-labelledby="filters-dropdown-button" style="width: 320px;">

                        <div class="container">
                            <div class="d-flex align-items-center mb-3">
                                <h6 class="text-14 fw-bold my-0 me-auto">
                                    <i class="fas fa-gear me-1"></i>
                                    Create Activity Filters
                                </h6>
                                <button class="btn btn-close shadow-0"></button>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    {{-- RECORD MONTH RANGE FILTERS --}}
                                    <small>
                                        <i class="fas fa-bolt me-1"></i> Action
                                    </small>
                                    <x-drop-list as="input-action-filter" :items="$filters['actions']"
                                        button-classes="w-100" />
                                </div>
                                <div class="col-6">
                                    {{-- EMPLOYEE ROLE FILTERS --}}
                                    <small>
                                        <i class="fas fa-arrows-spin me-1"></i> Affected
                                    </small>

                                    <x-drop-list as="input-affected-filter" :items="$filters['affected']"
                                        button-classes="w-100" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <small>
                                        <i class="fas fa-bullseye me-1"></i> Time (From)
                                    </small>
                                    <x-time-picker as="input-time-from" />
                                </div>
                                <div class="col-6">
                                    <small>
                                        <i class="fas fa-flag-checkered me-1"></i> Time (To)
                                    </small>
                                    <x-time-picker as="input-time-to" />
                                </div>
                            </div>
                            <div class="time-options-wrapper d-flex py-2">
                                <x-check-box parent-classes="me-auto" as="input-time-inclusive" label="Full day, time inclusive" />
                                
                                <div class="time-filter-help ms-2 flex-center" data-mdb-toggle="tooltip" 
                                     data-mdb-title="When selected, all records from the entire day will be included, such as from the first hour of the day (12:00 AM) to the last hour of the day (11:59 PM).">
                                    <i class="fas fa-info-circle text-primary-dark opacity-85"></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <small>
                                        <i class="fas fa-user me-1"></i> User
                                    </small>
                                    <x-drop-list as="input-user-filter" :items="$filters['users']"
                                        button-classes="w-100" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <small>
                                        <i class="fas fa-calendar me-1"></i> Date
                                    </small>
                                    <x-date-picker as="input-date-filter" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <small>
                                        <i class="fas fa-search me-1"></i> Search
                                    </small>
                                    <x-text-field as="search-filter" placeholder="Enter search term" />
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
            </div>
        </div>

        {{-- DATASET TABLE --}}
        <div class="w-100 position-relative overflow-hidden">
            <table class="table table-striped table-fixed w-100 table-sm table-hover dataset-table" id="records-table"
                data-src-datasource="{{ $routes['getAll'] }}" data-src-view-audit="{{ $routes['viewAudit'] }}">
                <thead class="user-select-none">
                    <tr>
                        <th style="width: 80px;" class="record-counter sticky-header">#</th>
                        <th style="width: 120px;">Date</th>
                        <th style="width: 120px;">Time</th>
                        <th class="th-180">User</th>
                        <th class="th-120 text-center">Action</th>
                        <th style="width: 150px;">Affected</th>
                        <th class="th-250">Description</th>
                        <th class="th-80"></th>
                    </tr>
                </thead>
                <tbody>{{-- CONTENT WILL COME FROM AJAX SOURCE --}}</tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('dialogs')
<x-audit-trail-detail-create as="audit-details-create" />
<x-audit-trail-detail-delete as="audit-details-delete" />
<x-audit-trail-detail-update as="audit-details-update" />
@endpush

<x-gijgo-driver type="all" />

@push('scripts')
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
<script src="{{ asset('js/main/tests/audits-page.js') }}"></script>
</script>
@endpush