@extends('layouts.backoffice')

@section('title')
{{"Daily Time Records"}}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/main/backoffice/dtr-page.css') }}" />
<link rel="stylesheet" href="{{ asset('css/main/shared/table-common-sizes.css') }}" />
@endpush

@section('content')

<div class="card content-card">
    <div class="card-body">

        {{-- TABLE TITLE HEADER --}}
        <div class="d-flex align-items-center gap-1">
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-warning flat-button px-2 py-1 me-auto shadow-0">
                <i class="fas fa-arrow-left me-1"></i>
                Back
            </a>
            <div class="dropdown">
                <button class="btn btn-secondary flat-button dropdown-toggle shadow-0" id="period-dropdown-button"
                    data-mdb-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-calendar-days"></i>
                    <span class="ms-1">Period</span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="period-dropdown-button">
                    @foreach ($dtrPeriods as $label => $value)
                    <li>
                        <a class="dropdown-item period-filter" data-dtr-period="{{ $value }}" role="button">
                            {{ $label}}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            {{-- <button class="btn btn-primary flat-button shadow-0" id="export-button">
                <i class="fas fa-download"></i>
                <span class="ms-1">Export</span>
            </button> --}}
        </div>

        <div class="employee-trail-identity py-2">
            <h6 class="card-title">{{ $empName }}</h6>
            <small class="d-block opacity-65">{{ 'ID #' . $empIdNo }}</small>
        </div>

        {{-- DATASET TABLE --}}
        <table class="table table-striped table-sm w-100 dataset-table" data-employee-key="{{ $empKey }}"
            data-src-default="{{ $routes['ajax_dtr_get_all'] }}" data-export-target="{{ $routes['ajax_export_pdf'] }}">
            <thead class="user-select-none">
                <tr class="borderx border-top">
                    <th class="align-middle text-center" rowspan="2" colspan="2">Day</th>
                    <th class="py-0 th-h30 text-center border-start" colspan="2">AM</th>
                    <th class="py-0 th-h30 text-center border-start border-end" colspan="2">PM</th>
                    <th class="align-middle text-center" rowspan="2">Duration</th>
                    <th class="align-middle text-center v-stripe-accent border-start border-end" rowspan="2">Late</th>
                    <th class="align-middle text-center" rowspan="2">Undertime</th>
                    <th class="align-middle text-center v-stripe-accent border-start border-end" rowspan="2">Overtime
                    </th>
                    <th class="align-middle text-center" rowspan="2">Status</th>
                </tr>
                <tr>
                    <th class="py-0 th-h30 text-center border-start v-stripe-accent-green">In</th>
                    <th class="py-0 th-h30 text-center">Out</th>
                    <th class="py-0 th-h30 text-center border-start">In</th>
                    <th class="py-0 th-h30 text-center border-end v-stripe-accent-yellow">Out</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th class="text-center">
                        <span class="bg-success text-sm text-white rounded-2 px-2 py-1">Stats:</span>
                    </th>
                    <th class="text-sm text-center fw-bold" colspan="2">Days Present</th>
                    <th class="text-sm text-center fw-bold" colspan="2">Days Absent</th>
                    <th class="text-sm fw-bold">Hours Worked</th>
                    <th class="text-sm fw-bold">Total Hours</th>
                    <th class="text-sm fw-bold">Total Hours</th>
                    <th class="text-sm fw-bold">Total Hours</th>
                    <th class="text-sm fw-bold">Leave Count</th>
                </tr>
                <tr class="tr-statistics">
                    <th></th>
                    <th class="text-center">
                        <span class="bg-color-primary text-sm text-white rounded-2 px-2 py-1">Total:</span>
                    </th>
                    <th class="text-sm th-total-present text-center fw-bold" colspan="2">0</th>
                    <th class="text-sm th-total-absent text-center fw-bold" colspan="2">0</th>
                    <th class="text-sm th-work-hrs text-center bg-color-primary text-white"></th>
                    <th class="text-sm th-late-hrs text-center fw-bold">0</th>
                    <th class="text-sm th-undertime-hrs text-center fw-bold">0</th>
                    <th class="text-sm th-overtime-hrs text-center fw-bold">0</th>
                    <th class="text-sm th-leave-count text-center fw-bold">0</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection

@push('dialogs')
@endpush

@push('scripts')
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
<script src="{{ asset('js/main/backoffice/dtr-page.js') }}"></script>
@endpush