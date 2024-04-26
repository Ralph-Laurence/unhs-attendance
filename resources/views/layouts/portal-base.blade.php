@php
use App\Http\Utils\PortalRouteNames;
use App\Http\Utils\Constants;
$organizationName = Constants::OrganizationName;

// Get the current route and split them (called as url segments).
// We will use those for creating breadcrumbs
$routeSegments = \Request::segments();

// MDB5 layout -> https://mdbootstrap.com/snippets/standard/ascensus/4698476
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UNHS Portal')</title>

    <!-- LIBRARY STYLES -->
    <link rel="stylesheet" href="{{ asset('css/lib/mdb/mdb.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lib/fontawesome/css/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lib/fontawesome/css/solid.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lib/mdb/mdb.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lib/simplebar/simplebar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main/components/snackbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/main/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main/modals/alert-dialog.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/overrides/simplebar-overrides.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/overrides/datatable-overrides.css') }}" />

    <!-- CHILD VIEW STYLES -->
    @stack('styles')

    <style>
        #main-navbar {
            background-color: #411AD8;
        }

        #main-navbar #navbar-logo-wrapper {
            background-color: white;
            width: 36px;
            height: 36px;
            padding: 2px;
        }

        #main-navbar #navbar-logo-wrapper img {
            width: 100%;
            height: 100%;
        }

        .btn.row-action-button {
            width: 30px;
            height: 30px;
            max-width: 30px;
            max-height: 30px;
            min-width: 30px;
            min-height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 50%;
            font-size: 14px;
        }
    </style>
</head>

<body>

    @include('modals.alert')
    @stack('dialogs')

    {{-- SNACKBAR HOLDER --}}
    <div class="snackbar-frame position-fixed bottom-0 end-0 flex-column-reverse d-flex gap-2"></div>

    <div class="container-fluid h-100 overflow-hidden">

        <div class="row">

            {{-- @include('layouts.backoffice.sidenav') --}}

            <div class="col content-wrapper overflow-hidden d-flex flex-column px-0 vh-100">
                <div data-simplebar class="overflow-y-auto nav-items-containerx work-area px-3 h-100">

                    {{--NAVBAR--}}
                    <header class="mb-8">
                        <!-- Navbar -->
                        <nav class="navbar px-md-4 navbar-expand-lg navbar-dark fixed-top" id="main-navbar">
                            <!-- Container wrapper -->
                            <div class="container-fluid">
                                <!-- Toggle button -->
                                <button class="navbar-toggler" type="button" data-mdb-toggle="collapse"
                                    data-mdb-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                    aria-expanded="false" aria-label="Toggle navigation">
                                    <i class="fas fa-bars text-white"></i>
                                </button>

                                <!-- Collapsible wrapper -->
                                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                    <!-- Navbar brand -->
                                    {{-- <a class="navbar-brand mt-2 mt-lg-0" href="#">
                                        <img src="https://mdbcdn.b-cdn.net/img/logo/mdb-transaprent-noshadows.webp"
                                            height="15" alt="MDB Logo" loading="lazy" />
                                    </a> --}}
                                    <div class="navbar-brand py-0 mt-2 mt-lg-0">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle" id="navbar-logo-wrapper">
                                                <img src="{{ asset('images/internal/icons/logo-sm.png') }}">
                                            </div>
                                            <h6 class="text-14 m-0 text-white">UNHS Portal</h6>
                                        </div>
                                    </div>
                                    <!-- Left links -->
                                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 text-14">
                                        {{-- <li class="nav-item">
                                            <a class="nav-link text-white"
                                                href="{{ route(PortalRouteNames::Employee_Home) }}">Home</a>
                                        </li> --}}
                                        <li class="nav-item">
                                            <a class="nav-link text-white"
                                                href="{{ route(PortalRouteNames::Employee_Attendance) }}">Attendances</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link text-white"
                                                href="{{ route(PortalRouteNames::Employee_Leave) }}">Leave</a>
                                        </li>
                                    </ul>
                                    <!-- Left links -->
                                </div>
                                <!-- Collapsible wrapper -->

                                <!-- Right elements -->
                                <div class="d-flex align-items-center">
                                    <!-- Icon -->
                                    {{-- <a class="text-reset me-3" href="#">
                                        <i class="fas fa-shopping-cart"></i>
                                    </a> --}}
                                    <div class="h-100 flex-start">
                                        <h6 class="my-0 mx-2 text-14 text-white text-capitalize">
                                            {{ auth()->user()->firstname.' '.auth()->user()->lastname }}
                                        </h6>
                                    </div>
                                    <!-- Notifications -->
                                    {{-- <div class="dropdown">
                                        <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#"
                                            id="navbarDropdownMenuLink" role="button" data-mdb-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fas fa-bell"></i>
                                            <span class="badge rounded-pill badge-notification bg-danger">1</span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end"
                                            aria-labelledby="navbarDropdownMenuLink">
                                            <li>
                                                <a class="dropdown-item" href="#">Some news</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#">Another news</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#">Something else here</a>
                                            </li>
                                        </ul>
                                    </div> --}}
                                    <!-- Avatar -->
                                    <div class="dropdown">
                                        <a class="dropdown-toggle d-flex align-items-center hidden-arrow" href="#"
                                            id="navbarDropdownMenuAvatar" role="button" data-mdb-toggle="dropdown"
                                            aria-expanded="false">
                                            <img src="{{ asset('images/internal/placeholders/profile.png') }}"
                                                class="rounded-circle" height="25"
                                                alt="Black and White Portrait of a Man" loading="lazy" />
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end"
                                            aria-labelledby="navbarDropdownMenuAvatar">
                                            <li>
                                                <a class="dropdown-item" href="#">My profile</a>
                                            </li>
                                            {{-- <li>
                                                <a class="dropdown-item" href="#">Settings</a>
                                            </li> --}}
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route(PortalRouteNames::Employee_Logout) }}">Logout</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- Right elements -->
                            </div>
                            <!-- Container wrapper -->
                        </nav>
                        <!-- Navbar -->
                    </header>

                    {{--BREADCRUMB--}}
                    <nav aria-label="breadcrumb" class="px-md-4 px-2 mx-md-2 mx-1 mb-3">
                        {{-- THE PAGE TITLE WILL COME FROM THE URL's END SEGMENT --}}
                        <h6 class="mb-0 page-title-segment text-capitalize">@yield('title', 'Employee Portal')</h6>
        
                        <ol class="breadcrumb bg-transparent mb-0 pb-0 px-0 me-sm-6 me-5">
        
                            @foreach ($routeSegments as $segment)
        
                            @php
                            $clean_segment = str_replace('-', ' ', $segment);
                            @endphp
        
                            @if ($segment === end($routeSegments))
                            <li class="breadcrumb-item text-sm active">{{ ucfirst($clean_segment) }}</li>
                            @else
                            <li class="breadcrumb-item text-sm opacity-50">{{ ucfirst($clean_segment) }}</li>
                            @endif
        
                            @endforeach
                        </ol>
                    </nav>

                    {{-- CONTENT --}}
                    @yield('content')

                    <div class="footer text-center opacity-65 p-2">
                        <small>&copy; {{ date('Y') .' '. $organizationName }}</small>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- LIBRARY SCRIPTS -->
    <script src="{{ asset('js/lib/dompurify/purify.min.js') }}"></script>
    <script>
        'use strict';
    
        function sanitize(dirty) {
            var clean = DOMPurify.sanitize(dirty);
            return clean;
        }
        
        function getCsrfToken() {
            return $('meta[name="csrf-token"]').attr('content');
        }
    </script>

    <script src="{{ asset('js/lib/jquery/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('js/lib/momentjs/moment-with-locales.js') }}"></script>
    <script src="{{ asset('js/lib/simplebar/simplebar6.2.5.min.js') }}"></script>
    <script src="{{ asset('js/lib/mdb/mdb.min.js') }}"></script>
    <script src="{{ asset('js/main/modals/alert.js') }}"></script>
    <script src="{{ asset('js/components/snackbar.js') }}"></script>

    <!-- CHILD VIEW SCRIPTS -->
    @stack('scripts')

</body>

</html>