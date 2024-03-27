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

        <div class="card-ribbon">
            <div class="row">
                <div class="col-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-warning flat-button px-2 py-1 me-auto shadow-0">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back
                    </a>
                </div>
                <div class="col">
                    <small class="text-sm d-inline">
                        <i class="fas fa-info-circle text-primary me-1"></i>
                        <span class="opacity-55 fst-italic">The data presented below are READ-ONLY and are automatically calculated.</span>
                    </small>
                </div>
                <div class="col-4">
                    <div class="d-flex align-items-center gap-1 flex-row flex-end">
                        <div class="dropdown">
                            <button class="btn btn-secondary flat-button dropdown-toggle shadow-0 control-button" 
                                id="period-dropdown-button" disabled
                                data-mdb-toggle="dropdown" aria-expanded="false" data-mdb-auto-close="outside">
                                <i class="fas fa-calendar-days"></i>
                                <span class="mx-1">Period</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <ul class="dtr-periods dropdown-menu" aria-labelledby="period-dropdown-button">
                                <li><h6 class="dropdown-header mx-0 mt-2 text-primary-dark text-sm py-0">Current Month</h6></li>
                                <li><hr class="dropdown-divider" /></li>
                                @foreach ($dtrPeriods as $value => $label)
                                <li>
                                    @php 
                                        $active = ($value == $defaultRange_Value) ? 'active' : '';
                                    @endphp
            
                                    @if ($value == $rangeOther)
                                        <li><hr class="dropdown-divider" /></li>
                                        <li><h6 class="dropdown-header mx-0 my-2 text-primary-dark text-sm py-0">By Month</h6></li>
                                    @endif
                                    <a class="dropdown-item period-filter {{ $active }}" data-dtr-period="{{ $value }}" role="button">
                                        {{ $label}}
                                    </a>
                                </li>
                                @endforeach
            
                                <li class="px-2 py-2 d-hidden" id="other-months-filter">
                                    <x-month-picker as="dtr-months" class="w-100"/>
                                </li>
                            </ul>
                        </div>
                        <button class="btn btn-primary flat-button shadow-0 control-button" 
                        id="export-button" disabled
                        data-mdb-toggle="tooltip" data-mdb-html="true" 
                        data-mdb-title="<i class='fas fa-info-circle me-2 text-warning-light'></i>Exports <span class='fw-bold text-decoration-underline'>always include</span> all records from the 1<sup>st</sup> to the last day of the selected period's month.">
                            <i class="fas fa-download"></i>
                            <span class="ms-1">Export</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="employee-trail-identity py-2">
            <div class="row mb-1">
                <div class="col-2 flex-start">
                    <h6 class="my-0 me-auto text-14 opacity-65">
                        <div class="min-hw-20 d-inline-block">
                            <i class="fas fa-calendar-days"></i>
                        </div>
                        DTR Period
                    </h6>
                </div>
                <div class="col flex-start">
                    <i class="fas fa-caret-right text-sm me-1 opacity-20"></i>
                    <small class="text-14 m-0 lbl-dtr-period text-primary-dark">{{ $defaultRange_Label }}</small>
                </div>
                <div class="col-3">
                    <div class="export-status d-none flex-start">
                        <div class="loader"></div>
                        <small class="text-sm status-text ms-2">Fetching data, please wait...</small>
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-2">
                    <h6 class="text-14 m-0 opacity-65">
                        <div class="min-hw-20 d-inline-block">
                            <i class="fas fa-user"></i>
                        </div>
                        Employee
                    </h6>
                </div>
                <div class="col d-flex align-items-center">
                    <i class="fas fa-caret-right text-sm me-1 opacity-20"></i>
                    <h6 class="m-0">
                        {{ $empName }} <small class="opacity-65 fst-italic">{{ "($empRole)" }}</small>
                    </h6>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-2">
                    <h6 class="text-14 m-0 opacity-65">
                        <div class="min-hw-20 d-inline-block">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                        ID Number
                    </h6>
                </div>
                <div class="col d-flex align-items-center">
                    <i class="fas fa-caret-right text-sm me-1 opacity-20"></i>
                    <h6 class="d-block text-14 m-0">{{ "#$empIdNo" }}</h6>
                </div>
                <div class="col-3 flex-end">
                    <div class="page-length-controls">
                        <x-table-length-pager as="table-page-len"/>
                    </div>
                </div>
            </div>
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
<div class="print-dtr w-100 flex-center position-fixed top-0 start-0 d-hidden">

    <div class="a4-page container-fluid">
        
        <div class="printable-content row">
            <div class="col ps-3 pe-2">
               @include('reports.dtr-template')
            </div>
            <div class="col ps-2 pe-3">
                @include('reports.dtr-template')
            </div>
        </div>

    </div>

</div>
@endpush

@push('scripts')
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/printthis/printThis.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
<script src="{{ asset('js/main/backoffice/dtr-page.js') }}"></script>
@endpush