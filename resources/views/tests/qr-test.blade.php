@extends('layouts.base')

@section('content')

{{--
@foreach ($codes as $id => $qrcode)
<div class="row">
    <small>{{ $id }}</small>
    <img src="{{ $qrcode }}" width="215" height="340" />
</div>
@endforeach --}}


@php
$counter = 0;
@endphp

<div class="overflow-y-scroll w-100 h-100" data-simplebar>
@foreach ($codes as $emp_no => $obj)
            @if ($counter % 4 == 0)
                <div class="row mb-5">
            @endif
            <div class="col">
                <div class="d-flex flex-column">
                    <small>Emp# {{ $emp_no }}</small>
                    <small>{{ $obj['name'] }}</small>
                    <img src="{{ $obj['qrcode'] }}" width="215" height="340" />
                </div>
            </div>
            @if ($counter % 4 == 3)
            </div>
            @endif
    @php
        $counter++;
    @endphp
@endforeach

@if ($counter % 4 != 0)
    </div>
@endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/lib/simplebar/simplebar.min.js') }}"></script>
@endpush