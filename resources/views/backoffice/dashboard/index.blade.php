@extends('layouts.backoffice')

@section('title')
{{'Dashboard'}}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/main/backoffice/dashboard-page.css') }}">
@endpush

@section('content')

<div class="row mb-4">
    <div class="col-3">
        <div class="card" style="min-width: 240px;" >
            <div class="card-body p-2">
                <div class="d-flex align-items-center mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate">
                        <span class="me-2">Total Employees</span>
                        <i class="fas fa-chart-simple text-primary-dark"></i>
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
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate">
                        <span class="me-2">Attendance Statistics</span>
                        <i class="fas fa-chart-simple text-primary-dark"></i>
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
                <div class="flex-start mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate">
                        <span class="me-2">Leave Requests</span>
                        <i class="fas fa-chart-simple text-primary-dark"></i>
                    </h6>
                    <h6 class="text-14 fw-bold total-leave-reqs my-1"></h6>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <div class="leave-count-wrapper leave-approved rounded-5 p-1" 
                            data-action="{{ $routes['leaveReqStats'] }}"
                            data-segment="{{ $leaveReqFilters['a'] }}">
                            <small class="ms-1 leave-count-label me-auto">Approved</small>
                            <div class="rounded-5 bg-white p-2 leave-count leave-count-approved">0</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="leave-count-wrapper leave-rejected rounded-5 p-1" 
                            data-action="{{ $routes['leaveReqStats'] }}"
                            data-segment="{{ $leaveReqFilters['r'] }}">
                            <small class="ms-1 leave-count-label me-auto">Rejected</small>
                            <div class="rounded-5 bg-white p-2 leave-count leave-count-rejected">0</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="leave-count-wrapper leave-pending rounded-5 p-1" 
                            data-action="{{ $routes['leaveReqStats'] }}"
                            data-segment="{{ $leaveReqFilters['p'] }}">
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
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate">
                        Total Monthly Attendances
                    </h6>
                    <h6 class="my-1 text-14 opacity-45">{{ $allMonths }}</h6>
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
    @include('modals.statistics-modal')
    @include('modals.statistics-leave-modal')
@endpush

@push('scripts')
<script src="{{ asset('js/lib/chartjs/chart.umd.js') }}"></script>
<script src="{{ asset('js/main/backoffice/dashboard-page.js') }}"></script>
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
@endpush