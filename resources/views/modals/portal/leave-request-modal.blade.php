<div class="modal fade" data-mdb-backdrop="static" data-mdb-keyboard="false"
    id="leaveRequestModal" tabindex="-1" aria-labelledby="leaveRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/modal_icon_leave.png') }}" width="28" height="28" alt="icon" class="modal-icon" />
                    <h6 class="modal-title mb-0" id="leaveRequestModalLabel">Request new leave</h6>
                </div>
                <button type="button" class="btn-close btn-cancel" data-mdb-ripple-init data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger text-center py-2 text-14 error-box d-hidden"></div>
                <form data-post-target="{{ $post }}" method="post"
                      id="frm-leave-request">
                      <x-text-field as="input-update-key" readonly parent-classes="d-none"/>
                    <div class="container">
                        <div class="row mb-3">
                            <div class="col-md-6 col-12">
                                <h6 class="text-14">Start Date</h6>
                                <x-date-picker as="input-leave-start" required/>
                            </div>
                            <div class="col-md-6 col-12">
                                <h6 class="text-14">End Date</h6>
                                <x-date-picker as="input-leave-end" required/>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 col-12">
                                <h6 class="text-14">Leave Type</h6>
                                <x-drop-list as="input-leave-type" :items="$leaveTypes" button-classes="w-100"/>
                            </div>
                            <div class="col-md-6 col-12">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel btn-secondary flat-button">Cancel</button>
                <button type="button" class="btn btn-submit btn-primary flat-button shadow-0">Submit</button>
            </div>
        </div>
    </div>
</div>