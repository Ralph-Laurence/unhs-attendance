<div class="dropstart table-searcher" id="{{ $as }}">
    <button type="button" class="btn btn-primary flat-button btn-md btn-floating btn-finder-search" type="button"
        data-mdb-ripple-init data-mdb-toggle="dropdown" aria-expanded="false" data-mdb-auto-close="false">
        <i class="fas fa-search"></i>
    </button>
    <div class="dropdown-menu p-3" style="width: 360px;" aria-labelledby="dropdownMenuButton">
        <div class="d-flex flex-row justify-content-center align-items-center mb-2">
            <h6 class="text-13 opacity-80 fst-italic flex-fill">*Search applies to all columns</h6>
            <button type="button" class="btn flat-close-button-sm" id="btn-close-search" type="button">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <x-text-box as="search-bar" parent-classes="mb-3" aria-autocomplete="none" placeholder="Enter search term" />
        <div class="flex-center flex-end gap-1">
            <button class="btn btn-secondary flat-button shadow-0" id="btn-finder-clear">
                <i class="fas fa-undo"></i>
                <span class="ms-1">Clear</span>
            </button>
            <button class="btn btn-primary flat-button shadow-0" id="btn-finder-search">
                <i class="fas fa-search"></i>
                <span class="ms-1">Find</span>
            </button>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/components/table-searcher.js') }}"></script>
    @endpush
@endonce