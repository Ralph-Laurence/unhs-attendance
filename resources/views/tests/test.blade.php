@extends('layouts.base')

@section('content')

{{-- @php
    use \Carbon\Carbon;
    $employees_toUpdate_onLeave = [];
    $employees_toUpdate_onDuty = [];
    $today = now()->startOfDay();
@endphp
<h6>today is: {{ $today }}</h6>
@foreach ($employees as $emp)
    @foreach ($emp->leave_requests as $req)
    @php
        if ($today >= Carbon::parse($req['start_date']) && $today <= Carbon::parse($req['end_date']) )
            $employees_toUpdate_onLeave[] = $req['id'];
    @endphp
        <p>
            {{  }}
        </p>
    @endforeach
@endforeach --}}

@dd($employees)

@endsection