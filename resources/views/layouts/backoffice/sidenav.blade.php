@php
use \App\Http\Utils\RouteNames; 

$routeItems = 
[
    ['wildcard' => '',      'icon' => 'fa-chart-pie',    'text' => 'Dashboard',      'target' => ''], //route(RouteNames::DASHBOARD['index'])
    ['wildcard' => 'backoffice.attendance',             'icon' => 'fa-calendar-check',      'text' => 'Attendance',     'target' => route(RouteNames::Attendance['index'])],
    ['wildcard' => 'backoffice.attendance.absence',     'icon' => 'fa-calendar-xmark',      'text' => 'Absence',        'target' => '', 'menu_type' => 'sub'],
    ['wildcard' => 'backoffice.attendance.tardiness',   'icon' => 'fa-calendar-minus',      'text' => 'Late',           'target' => '', 'menu_type' => 'sub' ],
    ['wildcard' => 'backoffice.attendance.tardiness',   'icon' => 'fa-calendar-week',       'text' => 'Leave',          'target' => '', 'menu_type' => 'sub' ],
    ['wildcard' => 'backoffice.attendance.tardiness',   'icon' => 'fa-qrcode',              'text' => 'Scanner',        'target' => route(RouteNames::Scanner['index']), 'menu_type' => 'sub' ],
    ['wildcard' => 'backoffice.teachers',               'icon' => 'fa-person-chalkboard',   'text' => 'Teachers',       'target' => route(RouteNames::Teachers['index'])],
    ['wildcard' => 'backoffice.staff',                  'icon' => 'fa-people-carry-box',    'text' => 'Staff',          'target' => route(RouteNames::Staff['index'])],
    ['wildcard' => '', /*backoffice.teachers*/          'icon' => 'fa-shield',              'text' => 'Administrator',  'target' => ''], // route(RouteNames::Staffs['index'])
];
@endphp
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

            @foreach ($routeItems as $obj)
                @php
                    $wildcard    = $obj['wildcard'];
                    $isCurrent   = ( Request::routeIs($wildcard) || Request::routeIs("$wildcard.*") );
                    $marginStart = (array_key_exists('menu_type', $obj) && $obj['menu_type'] == 'sub') 
                                 ? 'ms-4'
                                 : '';
                @endphp
                @if ($isCurrent)
                    <a class="nav-items p-2 rounded-2 {{ $marginStart }} current">
                @else
                    <a class="nav-items p-2 rounded-2 {{ $marginStart }}" href="{{ $obj['target'] }}">
                @endif
                
                    <div class="nav-item-icon">
                        <i class="fa-solid {{ $obj['icon'] }}"></i>
                    </div>
                    <div class="ms-2">{{ $obj['text'] }}</div>
                </a>
                
            @endforeach
            <hr>
            <button class="nav-items p-2 rounded-2 w-100">
                <div class="nav-item-icon">
                    <i class="fa-solid fa-gear"></i>
                </div>
                <div class="ms-2">Settings</div>
            </button>
            <form action="{{ route('logout') }}" method="post">
                @csrf
                {{-- <button class="btn btn-primary flat-btn">Logout</button> --}}
                <button class="nav-items p-2 rounded-2 w-100">
                    <div class="nav-item-icon">
                        <i class="fa-solid fa-times"></i>
                    </div>
                    <div class="ms-2">Logout</div>
                </button>
            </form>
       </div>
    </div>
</div>