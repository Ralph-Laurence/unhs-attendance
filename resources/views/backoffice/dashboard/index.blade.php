@extends('layouts.backoffice')

@section('title')
{{'Dashboard'}}
@endsection

@push('styles')
<style>
    .min-chart-height {
        min-height: 235px;
    }
    .min-chart-height-sm {
        min-height: 180px;
    }
    .chart-title {
        min-height: 32px;
    }
    .leave-count-wrapper .leave-count-label {
        font-size: 14px;
    }

    .leave-count-wrapper .leave-count,
    .emp-status-count {
        min-width: 38px;
        min-height: 38px;
        max-height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #021e3d;
    }

    .leave-count-wrapper,
    .employee-status-wrapper {
        font-size: 14px;
        display: flex;
        align-items: center;
    }

    .leave-approved {
        background-color: #69dbc2;
    }
    .leave-rejected {
        background-color: #FF6E80;
    }
    .leave-pending {
        background-color: #F9D385;
    }
</style>
@endpush

@section('content')

<div class="row mb-4">
    <div class="col-3">
        <div class="card" style="min-width: 240px;" >
            <div class="card-body p-2">
                <div class="d-flex align-items-center mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate">Total Employees
                    </h6>
                    <h5 class="my-1" id="employee-count">0</h5>
                </div>
                <canvas id="employees-diff" class="min-chart-height" data-src="{{ $routes['employeeCompare'] }}"></canvas>
            </div>
        </div>
    </div>
    <div class="col-5">
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex align-items-center mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate">Attendance Statistics
                    </h6>
                    <h6 class="my-1 opacity-45">Today</h6>
                </div>
                <canvas id="attendance-statistics" class="min-chart-height" data-src="{{ $routes['attendanceStats'] }}"></canvas>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card mb-3">
            <div class="card-body p-2">
                <div class="d-flex align-items-center mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 text-truncate">Employee Status
                    </h6>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="employee-status-wrapper rounded-5 p-1">
                            <h6 class="my-0 ms-1 me-auto text-14">On Duty</h6>
                            <div class="rounded-5 bg-color-primary text-white p-2 emp-status-count" id="count-on-duty">0</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="employee-status-wrapper rounded-5 p-1">
                            <h6 class="my-0 ms-1 me-auto text-14">On Leave</h6>
                            <div class="rounded-5 bg-color-warning p-2 emp-status-count" id="count-on-leave">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex align-items-center mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 text-truncate">Leave Requests
                    </h6>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <div class="leave-count-wrapper leave-approved rounded-5 p-1">
                            <small class="ms-1 leave-count-label me-auto">Approved</small>
                            <div class="rounded-5 bg-white p-2 leave-count leave-count-approved">0</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="leave-count-wrapper leave-rejected rounded-5 p-1">
                            <small class="ms-1 leave-count-label me-auto">Rejected</small>
                            <div class="rounded-5 bg-white p-2 leave-count leave-count-rejected">0</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="leave-count-wrapper leave-pending rounded-5 p-1">
                            <small class="ms-1 leave-count-label me-auto">Pending</small>
                            <div class="rounded-5 bg-white p-2 leave-count leave-count-pending">0</div>
                        </div>
                    </div>
                    <div class="col"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex align-items-center mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate">Monthly Attendances
                    </h6>
                    <h6 class="my-1 opacity-45">Average</h6>
                </div>
                <canvas id="monthly-totals" class="min-chart-height-sm xw-100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-4">
        
    </div>
</div>
@endsection

@push('dialogs')
<div class="modal fade statistics-modal" id="statistics-modal" tabindex="-1" aria-hidden="true" data-mdb-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/modal_icon_stats.png') }}" width="28" height="28" alt="icon" class="modal-icon" />
                    <h6 class="modal-title mb-0" id="statistics-modal-title">Statistics</h6>
                </div>
                <button type="button" class="btn-close close-button" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                {{-- DATASET TABLE --}}
                <div class="w-100 position-relative overflow-y-auto" data-simplebar style="max-height: 400px;">
                    <table class="table table-striped w-100 table-sm table-fixedx dataset-table" id="stats-table">
                        <thead class="position-sticky top-0 bg-color-primary-200 shadow-3-soft">
                            <tr>
                                <th style="width: 20%;">ID No</th>
                                <th style="width: 35%;">Name</th>
                                <th style="width: 20%;">Position</th>
                                <th style="width: 20%"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary flat-button close-button" data-mdb-ripple-init
                    data-mdb-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/lib/chartjs/chart.umd.js') }}"></script>
<script src="{{ asset('js/main/backoffice/dashboard-page.js') }}"></script>
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
@endpush