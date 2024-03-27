@php
$buttonClasses = $attributes->has('button-classes') ? $attributes->get('button-classes') : '';
$disabled = $attributes->has('input-off') ? 'disabled' : '';

@endphp

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/main/components/droplist.css') }}">
@endpush
@endonce

<div class="dropdown droplist table-length-pager d-flex align-items-center gap-1">
    <small class="text-13">Show</small>
    <button {{ $disabled }}
        class="btn btn-secondary btn-sm flat-button dropdown-toggle shadow-0 d-block text-truncate px-3 {{ $buttonClasses }}"
        id="{{ $as }}" data-mdb-toggle="dropdown" aria-expanded="false">
        <div class="w-100 d-flex align-items-center gap-2">
            <span class="button-text d-inline text-start text-truncate flex-fill me-1">{{ $default }}</span>
            <i class="fas fa-chevron-down droplist-arrow ms-auto"></i>
        </div>
    </button>

    <ul class="dropdown-menu overflow-hidden shadow shadow-4-strong user-select-none"
        aria-labelledby="{{ $as }}-drop-btn">
        <div data-simple-bar class="dropdown-menu-scrollview overflow-y-auto">

            @foreach ($items as $k => $v)
            <li>
                @php
                $isSelected = '';

                if ($k == $default)
                    $isSelected = 'selected';

                @endphp
                <a class="dropdown-item {{ $isSelected }}" role="button" data-value="{{ $v }}">
                    {{ $k }}
                </a>
            </li>
            @endforeach
        </div>
    </ul>
    <small class="text-13">Entries</small>
</div>

@once
@push('scripts')
<script src="{{ asset('js/components/table-length-pager.js') }}"></script>
@endpush
@endonce