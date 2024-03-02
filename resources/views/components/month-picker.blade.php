<div {{ $attributes->merge(['class' => "month-picker"]) }} id="{{ $as }}">
    <input class="main-control d-none" data-provide="month-picker" data-date-format="mm/yyyy"/>
    <button class="btn btn-secondary btn-trigger flat-button text-truncate" id="{{ $as }}-trigger">
        Select Month
    </button>
</div>
@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/lib/bsdp/bootstrap-datepicker3.standalone.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/main/components/month-picker.css') }}">

    @endpush
    @push('scripts')
        <script src="{{ asset('js/lib/bsdp/bootstrap-datepicker.min.js') }}"></script>
        <script src="{{ asset('js/components/month-picker.js') }}"></script>
    @endpush
@endonce