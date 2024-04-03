<div class="modal fade dashboard-stat-modal statistics-monthly-atx-modal" id="statistics-monthly-atx-modal" tabindex="-1" aria-hidden="true"
    data-mdb-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/modal_icon_create_attendance.png') }}" width="28" height="28"
                        alt="icon" class="modal-icon" />
                    <h6 class="modal-title mb-0" id="statistics-monthly-atx-modal-title">Monthly Attendances</h6>
                </div>
                <button type="button" class="btn-close close-button" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="container px-0">
                    <div class="row mb-2">
                        <div class="col flex-start">
                            <h6 class="text-14 mb-0"><i class="fas fa-caret-right"></i> Month of :
                                <span class="statistic-context rounded-6 bg-color-primary text-white px-2">Month</span>
                            </h6>
                        </div>
                        <div class="col flex-end">
                            <x-table-length-pager as="stats-monthly-table-page-len" />
                        </div>
                    </div>
                </div>
                {{-- DATASET TABLE --}}
                <div class="w-100 position-relative overflow-y-auto rounded-3" data-simplebar
                    style="max-height: 400px;">
                    <table class="table table-striped w-100 table-sm modal-table" id="monthly-atx-table">
                        <thead class="position-sticky top-0 shadow-3-soft">
                            <tr>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 30%;">Name</th>
                                <th style="width: 20%;">Time In</th>
                                <th style="width: 20%;">Time Out</th>
                                <th style="width: 20%">Duration</th>
                                {{-- <th style="width: 20%">Status</th> --}}
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