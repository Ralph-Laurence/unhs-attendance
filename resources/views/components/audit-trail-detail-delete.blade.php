<div class="modal fade audit-details-modal" id="{{ $as }}" tabindex="-1" aria-labelledby="{{ $modalLabel }}"
    aria-hidden="true" data-mdb-backdrop="static" {{--data-src="{{ $datasource }}--}}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/modal_icon_audits.png') }}" width="26" height="26"
                        alt="icon" class="modal-icon" />
                    <h6 class="modal-title mb-0" id="{{ $modalLabel }}">Audit Trail Details</h6>
                </div>
                <button type="button" class="btn-close close-button" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body px-4">

                <div class="description-wrapper mb-2">
                    <small class="description">
                        User "<span class="user">{#user}</span>" removed a record on <span class="date">{#date}</span>,
                        at <span class="time">{#time}</span>, from the <span class="affected">{#table}</span> records.
                        The last known values were:
                    </small>
                </div>
                <div data-simplebar class="table-container overflow-y-auto mb-2" style="max-height: 200px;">
                    <table class="table table-sm table-striped table-fixed audit-details-table audit-deleted-table">
                        <thead>
                            <tr>
                                <th>Affected Field</th>
                                <th class="text-center">Values</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <small class="text-sm text-primary-dark">Tracing Details</small>
                <hr class="my-1 opacity-10">
                <div class="tracing-details container-fluid text-14">
                    <div class="row mb-2">
                        <div class="col-3 px-0">
                            <div class="flex-start gap-2 me-auto">
                                <img src="{{ asset('images/internal/icons/icn_user_agent.png') }}" alt="user-agent"
                                    width="16" height="16">
                                <small>User Agent:</small>
                            </div>
                        </div>
                        <div class="col">
                            <small class="user-agent text-break">User Agent</small>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-3 px-0">
                            <div class="flex-start gap-2 me-auto">
                                <img src="{{ asset('images/internal/icons/icn_url.png') }}" alt="user-agent" width="16"
                                    height="16">
                                <small>URL:</small>
                            </div>
                        </div>
                        <div class="col">
                            <small class="url text-break">URL</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-3 px-0">
                            <div class="flex-start gap-2 me-auto">
                                <img src="{{ asset('images/internal/icons/icn_ip.png') }}" alt="user-agent" width="16"
                                    height="16">
                                <small>IP Address:</small>
                            </div>
                        </div>
                        <div class="col">
                            <small class="ip text-break">IP</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary flat-button close-button" data-mdb-ripple-init
                    data-mdb-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/components/audit-trail-detail-delete.js') }}"></script>
@endpush