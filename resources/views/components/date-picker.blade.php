@php
    $parentClasses = '';

    if ($attributes->has('parent-classes'))
        $parentClasses = $attributes->get('parent-classes');
@endphp


<div class="datetimepicker-textbox datepicker-textbox {{ $parentClasses }} {{ $errors->has($as) ? ' has-error' : '' }} {{ $attributes->has('required') ? 'required' : '' }}">

    <div class="input-wrapper">

        <i class="fas fa-calendar-days leading-icon text-sm ms-2 opacity-80"></i>

        <input type="text" {{ $attributes->merge(['class' => "datepicker main-control"]) }} readonly
            id="{{ $as }}" 
            name="{{ $as }}" 
            value="{{ old($as, $default) }}" />

        <i class="fas fa-circle-xmark error-icon"></i>
    </div>

    {{-- ERROR LABEL --}}
    <h6 class="px-2 my-1 text-danger text-sm error-label">{{ $errors->first($as) }}</h6>

</div>