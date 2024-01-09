@extends('layouts.backoffice')

@section('title')
{{"Teacher's Attendances"}}
@endsection

@push('styles')
{{-- <link rel="stylesheet" href="{{ asset('css/main/shared/attendance-common-styles.css') }}" /> --}}
<link rel="stylesheet" href="{{ asset('css/main/backoffice/attendance-page.css') }}" />
<link rel="stylesheet" href="{{ asset('css/main/shared/table-common-sizes.css') }}" />
@endpush

@section('content')

<div class="card content-card">
    <div class="card-body">

        {{-- TABLE TITLE HEADER --}}
        <div class="d-flex align-items-center gap-1">
            <h6 class="card-title me-auto">Teacher's Name</h6>

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
            {{-- <div class="dropdown">
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
            </div> --}}
        </div>

        {{-- DATASET TABLE --}}
        <table class="table table-striped table-sm w-100 attendance-trail-table" data-employee-key="{{ $empKey }}" data-src-default="{{ $routes['trails_all'] }}">
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
            <tbody>
        
                {{-- <tr>
                    <td class="daynumber td-50">30</td>
                    <td class="dayname td-60">Mon</td>
                    <td class="am_in text-center td-80">7:32a</td>
                    <td class="am_out text-center td-80">12:00p</td>
                    <td class="pm_in text-center td-80">1:02p</td>
                    <td class="pm_out text-center td-80">4:55p</td>
                    <td class="duration td-120">8hr 5min</td>
                    <td class="late td-120">2min</td>
                    <td class="undertime td-120">5min</td>
                    <td class="overtime td-120"></td>
                    <td class="status td-100">Present</td>
                </tr> --}}
            </tbody>
        </table>
    </div>
</div>

@endsection

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