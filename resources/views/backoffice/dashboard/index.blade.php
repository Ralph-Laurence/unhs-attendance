@extends('layouts.backoffice')

@section('title')
{{'Dashboard'}}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/main/backoffice/dashboard-page.css') }}" />
<link rel="stylesheet" href="{{ asset('css/main/shared/audits-action-icons.css') }}" />
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
                        <i class="fas fa-info-circle text-primary-dark ms-2 daily-attx-stat-info"
                        data-mdb-toggle="tooltip" data-mdb-html="true"
                        data-mdb-title='<span class="fst-italic text-13">
                            <i class="fas fa-triangle-exclamation text-warning-light"></i>
                            Charts or graphs may appear empty on weekends
                        </span>'></i>
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
                <div class="flex-start mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate">
                        <span class="me-2">Employee Status</span>
                        <i class="fas fa-chart-simple text-primary-dark"></i>
                    </h6>
                </div>
                <div class="row mb-2">
                    <div class="col text-center">
                        <div class="emp-stat-count-indicator pt-2 pb-1 d-flex flex-column align-items-center emp-stat-active">
                            <div class="emp-stat-count stat-active" id="count-active-stat"
                            data-action="{{ $routes['empStats'] }}"
                            data-segment="{{ $empStatusFilters['active'] }}"
                            data-segment-color="#3c2ee3">0</div>
                            <small class="d-block">Active</small>
                        </div>
                    </div>
                    <div class="col text-center">
                        <div class="emp-stat-count-indicator pt-2 pb-1 d-flex flex-column align-items-center emp-stat-leave">
                            <div class="emp-stat-count stat-leave" id="count-on-leave"
                            data-action="{{ $routes['empStats'] }}"
                            data-segment="{{ $empStatusFilters['leave'] }}"
                            data-segment-color="#ffac34">0</div>
                            <small>Leave</small>
                        </div>
                    </div>
                    <div class="col text-center">
                        <div class="emp-stat-count-indicator pt-2 pb-1 d-flex flex-column align-items-center emp-stat-onduty">
                            <div class="emp-stat-count stat-on-duty" id="count-on-duty"
                            data-action="{{ $routes['empStats'] }}"
                            data-segment="{{ $empStatusFilters['onduty'] }}"
                            data-segment-color="#1fc9a4">0</div>
                            <small>On Duty</small>
                        </div>
                    </div>
                    <div class="col text-center">
                        <div class="emp-stat-count-indicator pt-2 pb-1 d-flex flex-column align-items-center emp-stat-out">
                            <div class="emp-stat-count stat-out" id="count-out"
                            data-action="{{ $routes['empStats'] }}"
                            data-segment="{{ $empStatusFilters['out'] }}"
                            data-segment-color="#ff2641">0</div>
                            <small>Out</small>
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
                    <a class="my-1 text-13 text-primary-dark" href="{{ $routes['leaveRequests']}}">
                        <span class="me-1">See All</span>
                        <i class="fas fa-up-right-from-square"></i>
                    </a>
                </div>
                <div class="row mb-2">
                    <div class="col-3 text-center">
                        <div class="leave-count-indicator d-flex flex-column align-items-center"
                            data-action="{{ $routes['leaveReqStats'] }}"
                            data-segment="{{ $leaveReqFilters['p'] }}">
                            <div class="leave-count leave-count-pending">0</div>
                            <small class="text-sm">Pending</small>
                        </div>
                    </div>
                    <div class="col-3 text-center">
                        <div class="leave-count-indicator d-flex flex-column align-items-center"
                            data-action="{{ $routes['leaveReqStats'] }}"
                            data-segment="{{ $leaveReqFilters['a'] }}">
                            <div class="leave-count leave-count-approved">0</div>
                            <small class="text-sm">Approved</small>
                        </div>
                    </div>
                    <div class="col-3 text-center">
                        <div class="leave-count-indicator d-flex flex-column align-items-center"
                            data-action="{{ $routes['leaveReqStats'] }}"
                            data-segment="{{ $leaveReqFilters['r'] }}">
                            <div class="leave-count leave-count-rejected">0</div>
                            <small class="text-sm">Rejected</small>
                        </div>
                    </div>
                    <div class="col-3 text-center">
                        <div class="leave-count-indicator d-flex flex-column align-items-center"
                            data-action="{{ $routes['leaveReqStats'] }}"
                            data-segment="{{ $leaveReqFilters['u'] }}">
                            <div class="leave-count leave-count-unnoticed">0</div>
                            <small class="text-sm">Expired</small>
                        </div>
                    </div>
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
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate"
                    data-mdb-toggle="tooltip" data-mdb-html="true"
                    data-mdb-title='<ul class="list-unstyled text-start my-2">
                                        <li class="mb-1">
                                            <i class="fas fa-circle dp-icn me-1"></i>
                                            <span class="dp-label">Datapoint Total</span>
                                        </li>
                                        <li class="mb-1">
                                            <i class="fas fa-caret-up dp-icn me-1"></i>
                                            <span class="dp-label">Highest Datapoint</span>
                                        </li>
                                        <li>
                                            <i class="fas fa-xmark dp-icn me-1"></i>
                                            <span class="dp-label">Lowest Datapoint</span>
                                        </li>
                                    </ul>
                                    <span class="fst-italic text-13">
                                        <i class="fas fa-triangle-exclamation text-warning-light"></i>
                                        Charts or graphs may appear empty on weekends
                                    </span>'>
                        <span class="me-1">Total Monthly Attendances</span>
                        <i class="fas fa-info-circle monthly-attx-stat-info"></i>
                    </h6>
                    <h6 class="my-1 text-14 opacity-45">{{ $allMonths }}</h6>
                </div>
                <canvas id="monthly-totals" class="min-chart-height-sm xw-100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex align-items-center mb-2 px-1 chart-title">
                    <h6 class="card-title fw-bold text-14 text-uppercase my-1 me-auto text-truncate">
                        Recent Activity
                    </h6>
                    <a class="my-1 text-13 text-primary-dark" href="{{ $routes['allAudits']}}">
                        <span class="me-1">See All</span>
                        <i class="fas fa-up-right-from-square"></i>
                    </a>
                </div>
                    @if ($recentActivity)
                        <div class="overflow-y-auto" data-simplebar style="max-height: 350px;">
                            <table class="table table-sm table-striped table-fixed w-100">
                                <thead class="position-sticky top-0 bg-light z-100">
                                    <tr>
                                        <th class="text-primary-dark text-start">User</th>
                                        <th class="text-primary-dark text-center">Action</th>
                                        <th class="text-primary-dark text-center">Affected</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentActivity as $row)
                                        <tr class="text-13">
                                            <td class="text-truncate">{{ $row->user }}</td>
                                            <td class="text-center">
                                            
                                                <div class="audit-action action-badge {{ $row->actionIcon }} px-2 py-1 w-100">
                                                    <i class="fas me-1 fasicon"></i>
                                                    <span class="label text-capitalize text-sm">{{ $row->action }}</span>
                                                </div>
                                            
                                            </td>
                                            <td class="text-center text-truncate">{{ $row->affected }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <h6 class="text-14">No activity to display.</h6>
                    @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('dialogs')
    @include('modals.statistics-modal')
    @include('modals.statistics-leave-modal')
    @include('modals.statistics-empstat-modal')
    @include('modals.statistics-monthly-attendances')
@endpush

@push('scripts')
<script src="{{ asset('js/lib/chartjs/chart.umd.js') }}"></script>
<script src="{{ asset('js/main/backoffice/dashboard-page.js') }}"></script>
<script src="{{ asset('js/main/utils.js') }}"></script>
<script src="{{ asset('js/lib/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/main/shared/record-utils.js') }}"></script>
@endpush