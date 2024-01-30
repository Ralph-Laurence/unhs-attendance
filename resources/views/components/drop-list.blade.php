@php
    $buttonClasses = $attributes->has('button-classes') ? $attributes->get('button-classes') : '';
@endphp
<div class="dropdown">
    <button class="btn btn-secondary flat-button dropdown-toggle shadow-0 {{ $buttonClasses }}" 
        id="{{ $as }}-drop-btn" data-mdb-toggle="dropdown" aria-expanded="false">
        <span class="me-1 button-text text-truncate">{{ $text }}</span>
        <i class="fas fa-chevron-down opacity-65"></i>
    </button>
    <ul class="dropdown-menu role-filters" aria-labelledby="{{ $as }}-drop-btn">
        @if (!is_null($items))
            @foreach ($items as $k => $v)
            <li>
                <a class="dropdown-item" role="button" data-value="{{ $v }}">
                    {{ $k }}
                </a>
            </li>
            @endforeach
        @endif
    </ul>
    <input type="text" name="{{ $as }}" id="{{ $as }}" {{ $attributes->merge(['class' => 'd-none' ]) }} />
</div>