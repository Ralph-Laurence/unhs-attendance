<div class="modal fade dashboard-stat-modal statistics-emp-status-modal" id="statistics-emp-status-modal" tabindex="-1" aria-hidden="true"
    data-mdb-backdrop="static">
    <div class="modal-dialog modal-dialog-wide modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/modal_icon_leave.png') }}" width="28" height="28"
                        alt="icon" class="modal-icon" />
                    <h6 class="modal-title mb-0" id="statistics-emp-status-modal-title">Leave Requests</h6>
                </div>
                <button type="button" class="btn-close close-button" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="container px-0">
                    <div class="row mb-2">
                        <div class="col flex-start">
                            <h6 class="text-14 mb-0"><i class="fas fa-caret-right"></i> Segment :
                                <span class="statistic-context rounded-6 text-white px-2">Statistic</span>
                            </h6>
                        </div>
                        <div class="col flex-end">
                            <x-table-length-pager as="stats-leave-table-page-len" />
                            <div id="stats-table-page-container"></div>
                        </div>
                    </div>
                </div>
                {{-- DATASET TABLE --}}
                <div class="w-100 position-relative overflow-y-auto rounded-3" data-simplebar
                    style="max-height: 400px;">
                    <table class="table table-striped w-100 table-sm modal-table" id="leave-stats-table">
                        <thead class="position-sticky top-0 shadow-3-soft">
                            <tr>
                                <th style="width: 20%;">ID No</th>
                                <th style="width: 30%;">Name</th>
                                <th style="width: 25%;">Leave Type</th>
                                <th style="width: 20%">Duration</th>
                                <th style="width: 20%">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary flat-button close-button" data-mdb-ripple-init
                    data-mdb-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>