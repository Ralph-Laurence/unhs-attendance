<div class="modal fade" data-mdb-backdrop="static" id="createEmployeeModal" tabindex="-1"
    aria-labelledby="createEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ $modalSetup['icon'] }}" width="24" height="24" alt="icon"
                        class="modal-icon" />
                    <h6 class="modal-title mb-0" id="createEmployeeModalLabel"
                        data-title-create="{{ $modalSetup['titleAdd'] }}"
                        data-title-update="{{ $modalSetup['titleEdit'] }}"></h6>
                </div>
                <button type="button" class="btn-close btn-cancel modal-control-button"
                        data-mdb-ripple-init aria-label="Close">
                </button>
            </div>
            <div class="modal-body py-2">
                <small class="text-sm d-block mb-2">Please fill out all fields with an asterisk(*) as they are
                    required.</small>
                <form id="upsert-form" method="post"
                    data-action-create="{{ $routes['actionCreate'] }}" 
                    data-action-update="{{ $routes['actionUpdate'] }}">
                    @csrf

                    <div class="d-none" >
                        <input type="text" name="input-role" id="input-role" value="{{ $role }}" />
                        <input type="text" name="record-key" id="record-key" value=""/>
                    </div>

                    <div class="row mb-2">
                        <div class="col">
                            <small class="text-primary-dark text-sm"> 
                                <i class="fas fa-user me-1"></i>
                                Basic Details
                            </small>
                        </div>
                        <div class="col">
                            <small class="text-primary-dark text-sm"> 
                                <i class="fas fa-fingerprint me-1"></i>
                                Identification
                            </small>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col">
                            <x-text-field as="input-fname" placeholder="Firstname" maxlength="32" class="alpha-dash-dot"
                                required />
                        </div>
                        <div class="col">
                            <x-text-field as="input-id-no" placeholder="ID Number" maxlength="32" class="numeric-dash" required />
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <x-text-field as="input-mname" placeholder="Middlename" maxlength="32" class="alpha-dash-dot"
                                required />
                        </div>
                        <div class="col-6 pt-1">
                            <x-drop-list as="input-position" button-classes="w-100" text="Position" data-default-text="Position" :items="$positions" required />
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <x-text-field as="input-lname" placeholder="Lastname" maxlength="32" class="alpha-dash-dot"
                                required />
                        </div>
                        <div class="col flex-start">
                            <small class="text-primary-dark text-sm"> 
                                <i class="fas fa-key me-1"></i>
                                Authentication
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <x-text-field as="input-phone" placeholder="Contact #" maxlength="11" class="numeric" />
                            <p class="ms-1 py-1 flex-start gap-1">
                                <img src="{{ asset('images/internal/icons/icn_mobile_ph.png') }}" alt="PH" width="18" height="18"/>
                                <small class="text-sm">Must begin with "09"</small>
                            </p>
                        </div>
                        <div class="col">
                            <x-text-field as="input-email" placeholder="Email" maxlength="32" required />
                            <x-check-box as="option-save-qr" label="Save QR code local copy" />
                        </div>
                    </div>
                </form>

                <x-indefinite-meter as="emp-crud-progress"/>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel btn-secondary flat-button modal-control-button" data-mdb-ripple-init
                    data-mdb-dismissx="modal">Cancel</button>

                <button type="button" class="btn btn-submit btn-primary flat-button shadow-0 modal-control-button" data-mdb-dismissx="modal"
                    data-mdb-ripple-init>Save</button>
            </div>
        </div>
    </div>
</div>