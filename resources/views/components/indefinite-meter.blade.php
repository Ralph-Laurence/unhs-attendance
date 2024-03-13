<div id="{{ $as }}" {{ $attributes->merge(['class' => "indef-meter "]) }}>
    <span class="d-block"></span>
    <small class="text-sm fst-italic indef-caption">
        @if ($caption)
            {{ $caption }}
        @endif
    </small>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/main/components/indefinite-meter.css') }}">
    @endpush
    @push('scripts')
        <script src="{{ asset('js/components/indefinite-meter.js') }}"></script>
    @endpush
@endonce