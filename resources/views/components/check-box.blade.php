<div class="form-check flex-center">
    <input class="form-check-input" type="checkbox" name="{{ $as }}"
        id="{{ $as }}" />
    <label class="form-check-label pt-1 text-14 user-select-none" 
        for="{{ $as }}">{{ $label }}</label>
</div>

@push('scripts')
    <script src="{{ asset('js/components/checkbox.js') }}"></script>
@endpush