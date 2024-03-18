@extends('layouts.base')

@section('content')
<x-time-picker as="tp" />
<x-date-picker as="dp" />
@endsection

@push('scripts')
    <script>
        let timepicker;
        let datepicker;

        $(() => {
            timepicker = to_timepicker('#tp');
            datepicker = to_datepicker('#dp');
        });
    </script>
@endpush
