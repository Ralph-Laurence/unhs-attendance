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
                    <h6 class="me-auto">On Duty: 16</h6>
                    <h6>On Leave: 4</h6>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex align-items-center mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 text-truncate">Leave Requests
                    </h6>
                </div>
                <div class="d-flex align-items-center">
                    <h6 class="me-auto">Approved: 4</h6>
                    <h6>Unapproved: 8</h6>
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