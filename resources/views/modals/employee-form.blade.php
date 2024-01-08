@php
$type = 'Employee';

if (!empty($role))
    $type = $role;

$icons = [
    'Employee'  => 'modal_icon_employee.png',
    'Staff'     => 'modal_icon_staff.png',
    'Teacher'   => 'modal_icon_teacher.png',
];
@endphp

<div class="modal fade" data-mdb-backdrop="static" id="employeeFormModal" tabindex="-1"
    aria-labelledby="employeeFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/internal/icons/' . $icons[$type]) }}" width="24" height="24" alt="icon"
                        class="modal-icon" />
                    <h6 class="modal-title mb-0" id="employeeFormModalLabel">Add New {{ $type }}</h6>
                </div>
                <button type="button" class="btn-close" data-mdb-ripple-init data-mdb-dismissx="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body opacity-75 py-2">
                <small class="text-sm d-block mb-2">Please fill out all fields with an asterisk(*) as they are
                    required.</small>
                <form data-post-create-target="{{ $postCreate }}" data-post-update-target="" method="post">
                    @csrf
                    <input type="hidden" name="roleKey" value="{{ $empType }}" />
                    <div class="row mb-2">
                        <div class="col">
                            <x-text-box as="input-id-no" placeholder="ID Number" maxlength="32" class="numeric-dash"
                                required />
                        </div>
                        <div class="col"></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <x-text-box as="input-fname" placeholder="Firstname" maxlength="32" class="alpha-dash-dot"
                                required />
                        </div>
                        <div class="col">
                            <x-text-box as="input-contact-no" placeholder="Contact #" maxlength="13" class="numeric" />
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <x-text-box as="input-mname" placeholder="Middlename" maxlength="32" class="alpha-dash-dot"
                                required />
                        </div>
                        <div class="col">
                            @if (!empty($requireEmail) && $requireEmail === true)
                            <x-text-box as="input-email" placeholder="Email" maxlength="32" required />
                            @else
                            <x-text-box as="input-email" placeholder="Email" maxlength="32" />
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <x-text-box as="input-lname" placeholder="Lastname" maxlength="32" class="alpha-dash-dot"
                                required />
                        </div>
                        <div class="col">
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input" type="checkbox" name="optionSaveQRLocalCopy"
                                    id="optionSaveQRLocalCopy" />
                                <label class="form-check-label text-14" for="optionSaveQRLocalCopy">Save QR code local
                                    copy</label>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="py-2 progress-loader-wrapper d-hidden">
                    <div class="d-flex align-items-center gap-2">
                        <div class="loader"></div>
                        <h6 class="mb-0 text-sm fst-italic">Processing request, please wait...</h6>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel btn-secondary flat-button" data-mdb-ripple-init
                    data-mdb-dismissx="modal">Cancel</button>

                <button type="button" class="btn btn-save btn-primary flat-button shadow-0" data-mdb-dismissx="modal"
                    data-mdb-ripple-init>Save</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/main/modals/employee-form.js') }}"></script>
@endpush