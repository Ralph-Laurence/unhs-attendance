@php
    $parentClasses = '';

    if ($attributes->has('parent-classes'))
        $parentClasses = $attributes->get('parent-classes');

    $inputType = $attributes->has('of') ? $attributes->get('of') : 'text';
@endphp

@once
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/components/textbox.css') }}">
    @endpush
@endonce

<div class="textbox {{ $parentClasses }} {{ $errors->has($as) ? ' has-error' : '' }} {{ $attributes->has('required') ? 'required' : '' }}">

    <div class="input-wrapper">
        @if ($attributes->has('leading-icon'))
        <i class="fas leading-icon {{ $attributes->get('leading-icon') }}"></i>
        @endif

        @if ($attributes->has('leading-icon-s'))
        <i class="fas leading-icon text-sm ms-2 opacity-80 {{ $attributes->get('leading-icon-s') }}"></i>
        @endif

        <input type="{{ $inputType }}" name="{{ $as }}" id="{{ $as }}" {{ $attributes->merge(['class' => "main-control"]) }} 
        value="{{ old($as) }}"/>

        @if ($attributes->has('trailing-icon'))
        <i class="fas trailing-icon {{ $attributes->get('trailing-icon') }}"></i>
        @endif

        <i class="fas fa-circle-xmark error-icon"></i>
    </div>

    {{-- ERROR LABEL --}}
    <h6 class="px-2 my-1 text-danger text-sm error-label">{{ $errors->first($as) }}</h6>

    @if ($attributes->has('suggest'))
        <div class="auto-suggest-combobox position-absolute invisible" id="{{ $as .'-intellisense'}}"></div>
        @once 
            @push('scripts')
                <script src="{{ asset('js/lib/bs5-autocomplete/autocomplete.js') }}"></script>
            @endpush 
        @endonce
    
    @elseif ($attributes->has('datepicker'))
        <div class="x-datepicker position-absolute invisiblex border" id="{{ $as .'-x-datepicker'}}">
            
            <div class="date-picker-header bg-primary">
                test
            </div>
        </div>
    @endif
</div>

@once
    @push('scripts')
    <script src="{{ asset('js/components/textbox.js') }}"></script>
    @endpush
@endonce