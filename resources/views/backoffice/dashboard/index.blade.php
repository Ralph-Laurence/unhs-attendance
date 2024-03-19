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

    .leave-count-wrapper .leave-count {
        min-width: 38px;
        min-height: 38px;
        max-height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #021e3d;
    }

    .leave-count-wrapper {
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
        <div class="card">
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
                <canvas id="employee-status" class="min-chart-height"></canvas>
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
                <div class="d-flex align-items-center">
                    <h6 class="me-auto">On Duty: <span id="count-on-duty">0</span></h6>
                    <h6>On Leave: <span id="count-on-leave">0</span></h6>
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
                {{-- <div class="d-flex align-items-center">
                    <h6 class="me-auto">Approved: 4</h6>
                    <h6>Unapproved: 8</h6>
                </div> --}}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex align-items-center mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate">Daily Worked Hours
                    </h6>
                    <h6 class="my-1 opacity-45">Average</h6>
                </div>
                <canvas id="daily-work-hrs" class="min-chart-height-sm"></canvas>
            </div>
        </div>
    </div>
    <div class="col-4">
        
    </div>
</div>
{{-- <div class="card content-card">
    <div class="card-body">

    </div>
</div> --}}

@endsection

@push('scripts')
<script src="{{ asset('js/lib/chartjs/chart.umd.js') }}"></script>
<script src="{{ asset('js/main/backoffice/dashboard-page.js') }}"></script>
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
@endpush