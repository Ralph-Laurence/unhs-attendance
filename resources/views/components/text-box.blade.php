@php
    $parentClasses = '';

    if ($attributes->has('parent-classes'))
        $parentClasses = $attributes->get('parent-classes')

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

        <input type="text" name="{{ $as }}" id="{{ $as }}" {{ $attributes->merge(['class' => "main-control"]) }}>

        @if ($attributes->has('trailing-icon'))
        <i class="fas trailing-icon {{ $attributes->get('trailing-icon') }}"></i>
        @endif

        <i class="fas fa-circle-xmark error-icon"></i>
    </div>

    {{-- ERROR LABEL --}}
    <h6 class="px-2 my-1 text-danger text-xs error-label">{{ $errors->first($as) }}</h6>
</div>

@once
    @push('scripts')
    <script>
        $(document).ready(function() 
        {
            // Force numeric input texts to accept only numbers 0-9
            $(".numeric").on("input", function() 
            {
                var regexp = /[^0-9]/g;
                $(this).val($(this).val().replace(regexp, ''));
            });

            // Force numeric-dash input texts to accept only numbers 0-9 and dashes
            $(".numeric-dash").on("input", function() 
            {
                var regexp = /[^0-9-]/g;
                $(this).val($(this).val().replace(regexp, ''));
            });

            // Force numeric input texts to accept only letters A-Z, spaces, dashes and dots
            $(".alpha-dash-dot").on("input", function() 
            {
                var regexp = /[^a-zA-Z0-9.-\s]/g;
                $(this).val($(this).val().replace(regexp, ''));
            });

            // Force email fields to accept only alphanumeric, @ and dot
            $(".email").on("input", function() 
            {
                var regexp = /[^a-zA-Z0-9.@]/g;
                $(this).val($(this).val().replace(regexp, ''));
            });
        });

        function showTextboxError(target, message)
        {
            var root = $(target).closest('.textbox');

            root.addClass('has-error');
            root.find('.error-label').text(message);
        }

        function hideTextboxError(target)
        {
            var root = $(target).closest('.textbox');

            root.removeClass('has-error');
            root.find('.error-label').text('');
        }
    </script>
    @endpush
@endonce