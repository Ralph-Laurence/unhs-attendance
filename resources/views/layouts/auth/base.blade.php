<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UNHS Attendance')</title>

    <!-- LIBRARY STYLES -->
    <link rel="stylesheet" href="{{ asset('css/lib/mdb/mdb.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lib/fontawesome/css/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lib/fontawesome/css/solid.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lib/mdb/mdb.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('css/main/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main/auth/auth.css') }}">

    <link rel="stylesheet" href="{{ asset('css/main/modals/alert-dialog.css') }}" />
    
    <!-- CHILD VIEW STYLES -->
    @stack('styles')

</head>
<body>

    @include('modals.alert')

    <div class="container-fluid h-100 overflow-hidden position-relative">
        @yield('content')
    </div>

    <!-- LIBRARY SCRIPTS -->
    <script src="{{ asset('js/lib/dompurify/purify.min.js') }}"></script>
    <script>
        'use strict';

        function sanitize(dirty) {
            var clean = DOMPurify.sanitize(dirty);
            return clean;
        }
    </script>

    <script src="{{ asset('js/lib/jquery/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('js/lib/simplebar/simplebar6.2.5.min.js') }}"></script>
    <script src="{{ asset('js/lib/mdb/mdb.min.js') }}"></script>
    <script src="{{ asset('js/main/modals/alert.js') }}"></script>

    <!-- CHILD VIEW SCRIPTS -->
    @stack('scripts')

</body>
</html>

{{--https://stackoverflow.com/a/36679903--}}