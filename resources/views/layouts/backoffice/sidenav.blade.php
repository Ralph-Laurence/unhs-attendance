@push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/backoffice/sidenav.css') }}" />
@endpush
<div class="col sidenav shadow-4-strong user-select-none d-flex flex-column vh-100 px-0">
    <div class="logo-wrapper p-3 shadow-3-strong">
        <div class="logo-background logo-background-sm me-2">
            <img src="http://localhost:8000/images/logo.svg" alt="logo" width="32" height="32">
        </div>
        <div class="log-text-wrapper text-white">
            <h6 class="logo-text mb-0">Uddiawan NHS</h6>
            <small class="logo-sub-text mb-0 d-block opacity-75" style="font-size: 10px;">Attendance Monitoring</small>
        </div>
    </div>
    <div class="flex-grow-1 overflow-hidden">
        <div data-simplebar class="overflow-y-auto nav-items-container p-2 h-100">
            <a class="nav-items p-2 rounded-2">
                <div class="nav-item-icon">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div class="ms-2">Dashboard</div>
            </a>
            <a class="nav-items p-2 rounded-2">
                <div class="nav-item-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="ms-2">Attendance</div>
            </a>
            <a class="nav-items p-2 rounded-2">
                <div class="nav-item-icon">
                    <i class="fa-solid fa-person-chalkboard"></i>
                </div>
                <div class="ms-2">Teachers</div>
            </a>
            <a class="nav-items p-2 rounded-2">
                <div class="nav-item-icon">
                    <i class="fa-solid fa-people-carry-box"></i>
                </div>
                <div class="ms-2">Staffs</div>
            </a>
       </div>
    </div>
</div>