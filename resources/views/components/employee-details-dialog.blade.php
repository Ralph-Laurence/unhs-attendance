<div class="modal fade employee-details-modal" id="{{ $as }}" tabindex="-1" aria-labelledby="{{ $modalLabel }}"
    aria-hidden="true" data-mdb-backdrop="static" data-src="{{ $datasource }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ $modalIcon }}" width="24" height="24" alt="icon" class="modal-icon" />
                    <h6 class="modal-title mb-0" id="{{ $modalLabel }}">{{ $modalTitle }}</h6>
                </div>
                <button type="button" class="btn-close close-button" data-mdb-ripple-init data-mdb-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">

                <div id="employee-details-carousel" 
                class="carousel slide" {{-- work around, disabled mdb-ride from ride="carousel" to ride="false" --}} 
                data-mdb-ride="false" {{-- data-mdb-interval="false" // does not work right now, bugged!--}} 
                data-mdb-touch="false">

                    <div class="carousel-inner">
                        <div class="carousel-item profile-slide active">
                            <div class="d-flex">
                                <div class="d-flex flex-column me-4">
                                    <div class="photo-frame flex-center mb-2">
                                        <img id="employee-details-photo"
                                            src="{{ asset('images/internal/placeholders/profile.png') }}">
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
                                    <div class="d-flex text-14 gap-2 mb-1">
                                        <div class="opacity-65 detail-tag">Role:</div>
                                        <div id="emp-details-rank"></div>
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
                                        <div class="opacity-65 detail-tag">Phone:</div>
                                        <div id="emp-details-contact"></div>
                                    </div>
                                    <div class="d-flex text-14 gap-2">
                                        <div class="opacity-65 detail-tag">Email:</div>
                                        <div id="emp-details-email"></div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col">
                                            <div class="text-sm text-primary-dark mb-2">
                                                <i class="fas fa-calendar-days me-1"></i>Daily Time Record
                                            </div>
                                            <div class="flex-column d-flex gap-2">
                                                <button
                                                    class="btn btn-sm btn-primary flat-button shadow-0 btn-view-dtr w-fit-content">
                                                    View all
                                                </button>
                                                <div class="view-dtr-loader flex-start gap-2 d-hidden">
                                                    <div class="loader"></div>
                                                    <small class="fst-italic text-primary-dark opacity-70">Loading,
                                                        please wait...</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="text-sm text-primary-dark mb-2">
                                                <i class="fas fa-qrcode me-1"></i>QR Code
                                            </div>
                                            <button type="button" class="btn btn-sm btn-warning flat-button shadow-0"
                                                data-mdb-target="#employee-details-carousel" data-mdb-slide="next">
                                                <span class="me-1">Preview</span>
                                                <i class="fa-solid fa-chevron-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="carousel-item qrcode-slide">

                            <div class="row">
                                <div class="col">
                                    <button class="btn btn-link btn-sm my-0"
                                            type="button" data-mdb-slide="prev"
                                            data-mdb-target="#employee-details-carousel">
                                        <i class="fa-solid fa-chevron-left"></i>
                                        <span class="ms-1">Back to profile</span>
                                    </button>
                                </div>
                                <div class="col flex-center">
                                    <h6 class="text-sm my-0 text-uppercase text-primary">Preview</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <p class="alert alert-primary text-sm opacity-75 fst-italic text-justify">
                                        Please note that the QR code displayed here is for illustrative purposes and has been scaled down for preview. 
                                        <br><br/>The original size of the QR code is <strong>5.4cm x 8.5cm</strong>. You may download the QR code to access it 
                                        in its original size.<br><br/>Additionally, you have the option to have it resent via email.
                                    </p>
                                </div>
                                <div class="col text-center">
                                    <div class="img-wrapper mb-2">
                                        <img src="" class="rounded-3" id="emp-details-qrcode" alt="QR Code" width="184"
                                        height="290">
                                    </div>
                                    <div class="qr-options flex-center gap-3">
                                        <button type="button" class="btn btn-sm btn-warning flat-button shadow-0" id="btn-save-qr">
                                            <span class="me-1">Save</span>
                                            <i class="fa-solid fa-download"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning flat-button shadow-0" id="btn-send-qr">
                                            <span class="me-1">Send</span>
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <x-indefinite-meter as="send-qr-progress"/>
                                </div>
                            </div>
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