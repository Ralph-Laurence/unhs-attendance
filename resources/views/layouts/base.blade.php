<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'UNHS Attendance')</title>

    <!-- LIBRARY STYLES -->
    <link rel="stylesheet" href="{{ asset('css/lib/mdb/mdb.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lib/fontawesome/css/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lib/fontawesome/css/solid.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lib/mdb/mdb.min.css') }}" />
    
    <link rel="stylesheet" href="{{ asset('css/main/main.css') }}">

    <!-- CHILD VIEW STYLES -->
    @stack('styles')

</head>
<body>
    
    <div class="container-fluid">
        @yield('content')
    </div>

    <!-- LIBRARY SCRIPTS -->
    <script src="{{ asset('js/lib/jquery/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('js/lib/mdb/mdb.min.js') }}"></script>

    <!-- CHILD VIEW SCRIPTS -->
    @stack('scripts')

</body>
</html>