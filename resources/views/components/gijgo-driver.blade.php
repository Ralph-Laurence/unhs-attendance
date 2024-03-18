@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/lib/gijgo/gijgo.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/main/components/datetimepicker.css') }}">
    @endpush
@endonce    

@once    
@push('scripts')
    <script src="{{ asset('js/lib/momentjs/moment-with-locales.js') }}"></script>
    <script src="{{ asset('js/lib/gijgo/gijgo.min.js') }}"></script>
@switch($type)
    @case('all')
    @default
        <script src="{{ asset('js/components/timepicker.js') }}"></script>
        <script src="{{ asset('js/components/datepicker.js') }}"></script>
    @break
    
    @case('time')
        <script src="{{ asset('js/components/timepicker.js') }}"></script>
    @break

    @case('date')
        <script src="{{ asset('js/components/datepicker.js') }}"></script>
    @break
@endswitch
@endpush
@endonce