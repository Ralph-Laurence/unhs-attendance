@php
// Get the current route and split them (called as url segments)
// We will use this for creating breadcrumbs
$routeSegments = \Request::segments();

@endphp
{{-- <div class="title-banner px-2 py-3">
    <h6 class="mb-0 title-text">Title</h6>
    <small>Url/Bread/Crumbs</small>
</div> --}}

<nav class="navbar navbar-main navbar-expand-lg px-2 py-3 shadow-0 -border-radius-xl">
    <div class="container-fluid pb-1 px-3">
        <nav aria-label="breadcrumb">
            {{-- THE PAGE TITLE WILL COME FROM THE URL's END SEGMENT --}}
            <h6 class="mb-0 page-title-segment text-capitalize">@yield('title', 'Back Office')</h6>
            
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
        {{-- <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            right content
        </div> --}}
    </div>
</nav>