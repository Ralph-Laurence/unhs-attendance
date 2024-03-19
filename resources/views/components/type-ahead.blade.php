@php
    $parentClasses = '';

    if ($attributes->has('parent-classes'))
        $parentClasses = $attributes->get('parent-classes');
@endphp

@once
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/components/textbox.css') }}">
    @endpush
@endonce

<div class="textbox typeahead {{ $parentClasses }} {{ $errors->has($as) ? ' has-error' : '' }} {{ $attributes->has('required') ? 'required' : '' }}">

    <div class="input-wrapper overflow-visible">

        @if ($attributes->has('leading-icon'))
            <i class="fas leading-icon {{ $attributes->get('leading-icon') }}"></i>
        @endif

        <div id="parent" class="form-group">
            <input type="text" name="{{ $as }}" id="{{ $as }}" {{ $attributes->merge(['class' => "main-control"]) }} 
            value="{{ old($as) }}" autocomplete="off"/>
        </div>

        <i class="fas fa-circle-xmark error-icon"></i>
    </div>

    {{-- ERROR LABEL --}}
    <h6 class="px-2 my-1 text-danger text-sm error-label">{{ $errors->first($as) }}</h6>
</div>

@once
    @push('scripts')
    <script src="{{ asset('js/lib/bs5-autocomplete/gch1p-autocomplete.js') }}"></script>
    <script src="{{ asset('js/components/typeahead.js') }}"></script>
    @endpush
@endonce