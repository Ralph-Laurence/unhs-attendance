@php
    $buttonClasses = $attributes->has('button-classes') ? $attributes->get('button-classes') : '';
    $disabled = $attributes->has('input-off') ? 'disabled' : '';
@endphp

@once
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/main/components/droplist.css') }}">
    @endpush
@endonce

<div class="dropdown droplist">
    <button {{ $disabled }} class="btn btn-secondary flat-button dropdown-toggle shadow-0 d-block text-truncate {{ $buttonClasses }}" 
        id="{{ $as }}-drop-btn" data-mdb-toggle="dropdown" aria-expanded="false">
        <div class="w-100 d-flex align-items-center gap-2">
            <span class="button-text d-inline text-truncate me-auto">{{ $text }}</span>
            <i class="fas fa-chevron-down opacity-65"></i>
        </div>
    </button>

    <ul class="dropdown-menu overflow-hidden" aria-labelledby="{{ $as }}-drop-btn">
       <div data-simple-bar class="dropdown-menu-scrollview overflow-y-auto" style="height: 200px;">
        @if (!is_null($items))
            @foreach ($items as $k => $v)
            <li>
                @php
                    $isSelected = $v == $initial ? 'selected' : '';
                @endphp
                <a class="dropdown-item {{ $isSelected }}" role="button" data-value="{{ $v }}">
                    {{ $k }}
                </a>
            </li>
            @endforeach
        @endif
       </div>
    </ul>
    
    <input type="text" name="{{ $as }}" id="{{ $as }}" {{ $attributes->merge(['class' => 'main-control d-none' ]) }} value="{{ old($as, $initial) }}"/>

    {{-- ERROR LABEL --}}
    <h6 class="px-2 my-1 text-danger text-xs error-label">{{ $errors->first($as) }}</h6>
</div>

@once
    @push('scripts')
    <script src="{{ asset('js/components/droplist.js') }}"></script>
    @endpush
@endonce