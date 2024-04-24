@once
@push('styles')
    <style>
        .fab {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1020;
        }

        .fab .fab-icon {
            font-size: 18px;
        }

        .fab.fab-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .fab.fab-primary {
            background-color: var(--primary-dark);
            color: white;
        }

        .fab.fab-accent {
            background-color: var(--accent-color);
            color: white;
        }

        .fab.fab-warning {
            background-color: var(--warning-color);
            color: white;
        }

        .fab.fab-danger:hover {
            background-color: #a91726;
            color: white;
        }

        .fab.fab-primary:hover {
            background-color: #411AD8;
            color: white;
        }

        .fab.fab-accent:hover {
            background-color: #00B799;
            color: white;
        }

        .fab.fab-warning:hover {
            background-color: #FF8620;
            color: white;
        }
    </style>
@endpush    
@endonce
@php
    $classes = "btn ripple-surface-light shadow shadow-4-strong btn-floating fab position-fixed me-4 mb-5 bottom-0 end-0 $tint";
@endphp
<button type="button" {{ $attributes->merge(['class' => $classes]) }} id="{{ $as }}"
    @if ($attributes->has('toggle-modal'))
        @php
            $toggleModal = $attributes->get('toggle-modal');
        @endphp
        data-mdb-toggle="modal" data-mdb-target="{{ $toggleModal }}"
    @endif
    >
    <i class="fab-icon fa-solid {{ $icon }}"></i>
</button>