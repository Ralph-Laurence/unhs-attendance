<div class="modal fade employee-details-modal" id="{{ $as }}" tabindex="-1" aria-labelledby="{{ $modalLabel }}"
    aria-hidden="true" data-mdb-backdrop="static" data-src="{{ $datasource }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ $modalIcon }}" width="24" height="24" alt="icon"
                        class="modal-icon" />
                    <h6 class="modal-title mb-0" id="{{ $modalLabel }}">{{ $modalTitle }}</h6>
                </div>
                <button type="button" class="btn-close" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex px-4">
                <div class="d-flex flex-column me-4">
                    <div class="photo-frame flex-center mb-2">
                        <img id="employee-details-photo" src="{{ asset('images/internal/placeholders/profile.png') }}">
                    </div>
                    <div class="rounded-8 text-center emp-details-position">
                        {{ $employeeRole }}
                    </div>
                </div>
                <div class="right-contents flex-fill">
                    <div class="text-sm text-primary-dark mb-1">
                        <i class="fas fa-user me-1"></i> Basic Details
                    </div>
                    <div class="d-flex text-14 gap-2 mb-1 text-break">
                        <div class="opacity-65 detail-tag">Name:</div>
                        <div id="emp-details-name"></div>
                    </div>
                    <div class="d-flex text-14 gap-2 mb-1">
                        <div class="opacity-65 detail-tag">ID #:</div>
                        <div id="emp-details-idno"></div>
                    </div>
                    <div class="d-flex text-14 gap-2">
                        <div class="opacity-65 detail-tag">Status:</div>
                        <div id="emp-details-status"></div>
                    </div>
                    <hr>
                    <div class="text-sm text-primary-dark mb-1">
                        <i class="fas fa-phone me-1"></i>Contact Details
                    </div>
                    <div class="d-flex text-14 gap-2 mb-1">
                        <div class="opacity-65 detail-tag">Mobile:</div>
                        <div id="emp-details-contact"></div>
                    </div>
                    <div class="d-flex text-14 gap-2">
                        <div class="opacity-65 detail-tag">Email:</div>
                        <div id="emp-details-email"></div>
                    </div>
                    <hr>
                    <div class="text-sm text-primary-dark mb-2">
                        <i class="fas fa-calendar-days me-1"></i>Daily Time Record
                    </div>
                    <div class="flex-column d-flex gap-2">
                        <button class="btn btn-sm btn-primary flat-button shadow-0 btn-view-dtr w-fit-content">
                            View all
                        </button>
                        <div class="view-dtr-loader flex-start gap-2 d-hidden">
                            <div class="loader"></div>
                            <small class="fst-italic text-primary-dark opacity-70">Loading, please wait...</small>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary flat-button" data-mdb-ripple-init
                    data-mdb-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
    <form action="{{ $viewDtrRoute }}" method="post" class="frm-view-dtr d-none">
        <input type="hidden" name="_token" class="csrf" value="{{ csrf_token() }}">
        <input type="hidden" name="employee-key" id="employee-key">
    </form>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/main/components/employee-details-dialog.css') }}">
    @endpush    
    @push('scripts')
        <script src="{{ asset('js/components/employee-details-dialog.js') }}"></script>
    @endpush    
@endonce