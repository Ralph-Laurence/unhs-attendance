@php
// Get the current route and split them (called as url segments).
// We will use those for creating breadcrumbs
$routeSegments = \Request::segments();

@endphp

<nav class="navbar navbar-main navbar-expand-lg px-2 py-3 shadow-0">
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
        <div class="collapse navbar-collapse d-flex align-items-center flex-row justify-content-end"> {{-- mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar"> --}}
        <div class="dropdown profile-dropdown p-1">
        
            <div class="dropdown-toggle profile-option-dropdown d-flex align-items-center" data-mdb-toggle="dropdown"
                data-mdb-ripple-init aria-expanded="false">
        
                <div class="profile-pic rounded-circle overflow-hidden d-flex align-items-center justify-content-center"
                    id="dropdownMenuButton">
                    <img loading="lazy" src="{{ asset('images/internal/placeholders/profile.png') }}" alt="profile" width="32" height="32">
                </div>
                <h6 class="my-0 mx-2 text-14 opacity-75">
                    <span class="me-1">{{ auth()->user()->username }}</span>
                    <i class="fas fa-caret-down"></i>
                </h6>
        
            </div>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li>
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button class="dropdown-item w-100">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
        </div>
    </div>
</nav>