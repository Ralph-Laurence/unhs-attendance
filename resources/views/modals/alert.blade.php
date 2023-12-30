<div class="modal fade" data-mdb-backdrop="static" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 user-select-none">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/modal_icon_default.png') }}" alt="icon" class="modal-icon"
                    data-icon-default="{{ asset('images/internal/icons/modal_icon_default.png') }}"
                    data-icon-warning="{{ asset('images/internal/icons/modal_icon_warning.png') }}"
                    data-icon-failure="{{ asset('images/internal/icons/modal_icon_failure.png') }}">
                    
                    <h6 class="modal-title mb-0" id="alertModalLabel">Title</h6>
                </div>
                <button type="button" class="btn-close" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body opacity-75 user-select-none">...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel btn-secondary flat-button" data-mdb-ripple-init
                    data-mdb-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-ok btn-primary flat-button shadow-0" data-mdb-dismiss="modal"
                    data-mdb-ripple-init>OK</button>
            </div>
        </div>
    </div>
</div>