@extends('layouts.backoffice')

@section('title')
{{"Teacher's Attendances"}}
@endsection

@push('styles')
{{--
<link rel="stylesheet" href="{{ asset('css/main/shared/attendance-common-styles.css') }}" /> --}}
<link rel="stylesheet" href="{{ asset('css/main/backoffice/attendance-page.css') }}" />
<link rel="stylesheet" href="{{ asset('css/main/shared/table-common-sizes.css') }}" />
@endpush

@section('content')

<div class="card content-card">
    <div class="card-body">

        {{-- TABLE TITLE HEADER --}}
        <div class="d-flex align-items-center gap-1">
            <a href="{{ url()->previous() }}" class="text-primary-dark text-sm border rounded-6 px-2 py-1 me-auto">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
            {{-- <button class="btn btn-sm btn-outline-light flat-button text-primary-dark">
                Back
            </button> --}}

            {{-- RECORD DATE RANGE FILTERS --}}
            {{-- <div class="dropdown">
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
            </div> --}}

            {{-- ADD BUTTON --}}
            <div class="dropdown">
                <button class="btn btn-primary flat-button dropdown-toggle shadow-0" id="add-record-dropdown-button"
                    data-mdb-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download"></i>
                    <span class="ms-1">Export</span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="options-dropdown-button">
                    <li>
                        <a class="dropdown-item" role="button" data-mdb-toggle="modal" 
                        data-mdb-target="#exportPrintablesModal">Printable Report (pdf)</a>
                    </li>
                    <li>
                        <a class="dropdown-item" role="button">Raw Dataset (csv)</a>
                    </li>
                    {{-- <li><a class="dropdown-item" href="#">Import Sheet</a></li> --}}
                </ul>
            </div>
        </div>

        <div class="employee-trail-identity py-2">
            <h6 class="card-title">{{ $empName }}</h6>
            <small class="d-block opacity-65">{{ 'ID #' . $empIdNo }}</small>
        </div>

        {{-- DATASET TABLE --}}
        <table class="table table-striped table-sm w-100 attendance-trail-table" data-employee-key="{{ $empKey }}"
            data-src-default="{{ $routes['trails_all'] }}">
            <thead class="user-select-none">
                <tr class="borderx border-top">
                    <th class="align-middle" rowspan="2" colspan="2">Day</th>
                    <th class="py-0 th-h30 text-center border-start" colspan="2">AM</th>
                    <th class="py-0 th-h30 text-center border-start border-end" colspan="2">PM</th>
                    <th class="align-middle" rowspan="2">Duration</th>
                    <th class="align-middle" rowspan="2">Late</th>
                    <th class="align-middle" rowspan="2">Undertime</th>
                    <th class="align-middle" rowspan="2">Overtime</th>
                    <th class="align-middle" rowspan="2">Status</th>
                </tr>
                <tr>
                    <th class="py-0 th-h30 text-center border-start">In</th>
                    <th class="py-0 th-h30 text-center">Out</th>
                    <th class="py-0 th-h30 text-center border-start">In</th>
                    <th class="py-0 th-h30 text-center border-end">Out</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@endsection

@push('dialogs')
<div class="modal fade" data-mdb-backdrop="static" id="exportPrintablesModal" tabindex="-1" aria-labelledby="exportPrintablesModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 user-select-none">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/modal_icon_export_pdf.png') }}" 
                    alt="icon" class="modal-icon" width="24" height="24"/>
                    <h6 class="modal-title mb-0" id="exportPrintablesModalLabel">Export Printable Report</h6>
                </div>
                <button type="button" class="btn-close" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body user-select-none">

                <div class="pdf-parameter" style="height: 150px;">
                    <div class="d-flex align-items-center gap-2 w-100">
                        
                        <x-text-box as="input-export-filename" parent-classes="me-auto w-100" placeholder="Enter Output Filename" class="alpha-dash-dot"/>
                        
                        <div class="dropdown" style="margin-bottom: 4px;">
                            <button class="btn btn-secondary flat-button dropdown-toggle shadow-0" id="pdf-scopes-dropdown-button"
                                data-mdb-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-clock-rotate-left"></i>
                                <span class="ms-1">Set Range</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="pdf-scopes-dropdown-button">
                                <li>
                                    <a class="dropdown-item trail-range-filter" data-trail-range="{{ $trailRange['today'] }}" role="button">This Day</a>
                                </li>
                                <li>
                                    <a class="dropdown-item trail-range-filter" data-trail-range="{{ $trailRange['week'] }}"  role="button">This Week</a>
                                </li>
                                <li>
                                    <a class="dropdown-item trail-range-filter" data-trail-range="{{ $trailRange['month'] }}" role="button">This Month</a>
                                </li>
                                <li>
                                    <a class="dropdown-item trail-range-filter" data-trail-range="{{ $trailRange['all'] }}"   role="button">All Time</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <hr class="opacity-10 my-2">
                    {{-- <div class="text-primary-dark text-sm">Export Options:</div>
                    <div class="export-options">
                        <!-- Default radio -->
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="input-export-option" id="input-export-option-basic" />
                            <label class="form-check-label" for="input-export-option-basic">Basic</label>
                        </div>
                        
                        <!-- Default checked radio -->
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="input-export-option" id="input-export-option-detailed" checked />
                            <label class="form-check-label" for="input-export-option-detailed"> Detailed </label>
                        </div>
                    </div> --}}
                    <div class="d-none">
                        <form action="{{ $routes['export_trail_pdf'] }}" method="post" id="frm-export-trail-pdf">
                            <input type="hidden" id="employee-key" value="{{ $empKey }}" />
                        </form>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel btn-secondary flat-button" data-mdb-ripple-init
                    data-mdb-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-ok btn-primary flat-button shadow-0" data-mdb-dismiss="modal"
                    data-mdb-ripple-init>OK</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    //const route_deleteRecord = "{{-- $routes['deleteRoute'] --}}";
</script>
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
{{-- <script src="{{ asset('js/main/backoffice/attendance-page.js') }}"></script> --}}
<script src="{{ asset('js/main/backoffice/attendance-trail-page.js') }}"></script>
@endpush