<div class="modal fade" data-mdb-backdrop="static" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="alertModalLabel">Modal title</h6>
                <button type="button" class="btn-close" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body opacity-75">...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel btn-secondary flat-button" data-mdb-ripple-init
                    data-mdb-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-ok btn-primary flat-button" data-mdb-dismiss="modal"
                    data-mdb-ripple-init>OK</button>
            </div>
        </div>
    </div>
</div>

@once 
    @push('scripts')
        <script src="{{ asset('js/main/modals/alert.js') }}"></script>
    @endpush 
@endonce