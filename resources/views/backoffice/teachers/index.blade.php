@extends('layouts.backoffice')

@section('title')
{{'Teachers'}}
@endsection

@section('content')
<div class="card content-card">
    <div class="card-body">
        {{-- TABLE TITLE HEADER --}}
        <div class="d-flex align-items-center gap-1 mb-3">
            <h6 class="card-title me-auto">Faculty</h6>

            {{-- ADD BUTTON --}}
            <div class="dropdown">
                <button class="btn btn-primary flat-button dropdown-toggle shadow-0" id="add-record-dropdown-button"
                    data-mdb-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Add</span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="options-dropdown-button">
                    <li>
                        <a class="dropdown-item" data-mdb-togglex="modal" data-mdb-targetx="#attendanceFormModal"
                            role="button">Create Manually</a>
                    </li>
                    <li><a class="dropdown-item" href="#">Import Sheet</a></li>
                </ul>
            </div>
        </div>

        {{-- DATASET TABLE --}}
        <table class="table table-striped table-sm table-hover dataset-table"
            data-src-default="{{ $routes['defaultDataSource'] }}">
            <thead class="user-select-none">
                <tr class="border-top">
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th class="text-center" style="background-color: #F1F2F3;" colspan="3">Total No. Of</th>
                    <th></th>
                </tr>
                <tr>
                    <th>#</th>
                    <th>ID no.</th>
                    <th>Faculty Name</th>
                    <th>Status</th>
                    <th style="background-color: #FADCB7;">Late</th>
                    <th>Leave</th>
                    <th style="background-color: #FFC9CF;">Absent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>{{-- CONTENT WILL COME FROM AJAX SOURCE --}}</tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
{{-- <script>
    const route_deleteRecord = "{{ $routes['deleteRoute'] }}";
</script> --}}
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
<script src="{{ asset('js/main/backoffice/teachers-page.js') }}"></script>
@endpush