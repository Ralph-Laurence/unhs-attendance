@php
use \App\Http\Utils\RouteNames; 

$routeItems = 
[
    ['wildcard' => 'backoffice.dashboard',              'icon' => 'fa-chart-pie',           'text' => 'Dashboard',      'target' => route(RouteNames::Dashboard['index'])],
    ['wildcard' => 'backoffice.attendance',             'icon' => 'fa-calendar-check',      'text' => 'Attendance',     'target' => route(RouteNames::Attendance['index'])],
    ['wildcard' => 'backoffice.absence',                'icon' => 'fa-calendar-xmark',      'text' => 'Absence',        'target' => route(RouteNames::Absence['index']),     'menu_type' => 'sub'],
    ['wildcard' => 'backoffice.late',                   'icon' => 'fa-calendar-minus',      'text' => 'Late',           'target' => route(RouteNames::Late['index']),        'menu_type' => 'sub'],
    ['wildcard' => 'backoffice.leave',                  'icon' => 'fa-calendar-week',       'text' => 'Leave',          'target' => route(RouteNames::Leave['index']),       'menu_type' => 'sub'],
    ['wildcard' => '',                                  'icon' => 'fa-qrcode',              'text' => 'Scanner',        'target' => route(RouteNames::Scanner['index']),     'menu_type' => 'sub'],
    ['wildcard' => 'backoffice.faculty',                'icon' => 'fa-person-chalkboard',   'text' => 'Faculty',        'target' => route(RouteNames::Faculty['index'])],
    ['wildcard' => 'backoffice.staff',                  'icon' => 'fa-people-carry-box',    'text' => 'Staff',          'target' => route(RouteNames::Staff['index'])],
    ['separator' => true],
    ['wildcard' => '', /*backoffice.admins*/            'icon' => 'fa-shield',              'text' => 'Administrator',  'target' => ''], // route(RouteNames::Staffs['index'])
    ['wildcard' => 'backoffice.audits',                 'icon' => 'fa-file-signature',      'text' => 'Audit Trails',   'target' => route(RouteNames::AuditTrails['index']), 'menu_type' => 'sub'],
    //['wildcard' => '', /*backoffice.admins*/            'icon' => 'fa-bolt-lightning',      'text' => 'System Logs',    'target' => '', 'menu_type' => 'sub'],
    ['separator' => true],
    //['wildcard' => '', /*backoffice.admins*/            'icon' => 'fa-gear',      'text' => 'Settings',       'target' => '',],
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

                @if (in_array('separator', $obj))
                    <hr class="my-1 border-light opacity-15">
                    @php
                        continue;
                    @endphp
                @endif

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
            {{-- <hr>
            <button class="nav-items p-2 rounded-2 w-100">
                <div class="nav-item-icon">
                    <i class="fa-solid fa-gear"></i>
                </div>
                <div class="ms-2">Settings</div>
            </button> --}}
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