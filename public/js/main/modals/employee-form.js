const employeeFormModal     = '#employeeFormModal';
const employeeDetailsModal  = '#employeeDetailsModal';

let dirty = false;
let formModal = null;
let formMode = undefined;
let formCancelled = false;
let FormMode_Create = 1;
let FormMode_Edit = 2;
let selectedRowForEdit = null;

let detailsModal = undefined;
let employeeForm = undefined;
let formSubmitButton = undefined;
let formCancelButton = undefined;

let metaCSRF = undefined;

$(document).ready(function ()
{
    metaCSRF = $('meta[name="csrf-token"]').attr('content');

    formModal = new mdb.Modal($(employeeFormModal));
    employeeForm = $(`${employeeFormModal} form`);

    detailsModal = new mdb.Modal($(employeeDetailsModal));

    formSubmitButton = $(`${employeeFormModal} .btn-save`);
    formCancelButton = $(`${employeeFormModal} .btn-cancel, ${employeeFormModal} .btn-close`);

    bindEvents();
});

function bindEvents()
{
    // Watch changes for every input fields
    var inputs = employeeForm.find('input[type="text"]');
    inputs.on('input', function()
    {
        dirty = true;

        let inputField = $(this);

        if (inputField.val())
            hideTextboxError(`#${inputField.attr('id')}`);
    });

    formSubmitButton.on('click', () => handleFormSubmit()); 
    
    $(employeeFormModal).on('hidden.bs.modal', () => handleFormClosed());

    formCancelButton.on('click', () => {
        formModal.hide();
        formCancelled = true;
    });
}

function openEditForm(row)
{
    selectedRowForEdit = row;

    formCancelled = false;

    let formTitle = $('#employeeFormModalLabel').data('form-title-update');

    $('#employeeFormModalLabel').text(formTitle);
    $('#optionSaveQRLocalCopy').prop('disabled', true);

    formMode = FormMode_Edit;

    loadEmployeeDetails(row, (data) => 
    {
        if (data.code == 0)
        {
            $('#input-fname').val(data.fname);
            $('#input-mname').val(data.mname);
            $('#input-lname').val(data.lname);
            $('#input-email').val(data.email);
            $('#input-contact-no').val(data.phone);
            $('#input-id-no').val(data.idNo);
            $('#record-key').val(data.rowKey);

            formModal.show();
        }
        else
        {
            alertModal.showDanger(data.message);
        }
    });
}

function openCreateForm()
{
    formCancelled = false;

    let formTitle = $('#employeeFormModalLabel').data('form-title-create')
    
    $('#employeeFormModalLabel').text(formTitle);
    $('#optionSaveQRLocalCopy').prop('disabled', false);

    //clearForm();
    formMode = FormMode_Create;
    formModal.show();
}

function showEmployeeDetails(row)
{
    loadEmployeeDetails(row, (data) => 
    {
        if (data.code == 0)
        {
            bindTrailsData(data);
            detailsModal.show();
        }
        else
        {
            alertModal.showDanger(data.message);
        }
    });
}

function clearForm()
{
    employeeForm.trigger('reset');

    // Hide all textbox errors
    employeeForm.find('input[type="text"]:required').each(function(){
        
        let id = $(this).attr('id');

        hideTextboxError(`#${id}`);
    });

    dirty = false;
    formMode = undefined;
    selectedRowForEdit = null;
}

function loadEmployeeDetails(row, onSuccess)
{
    let rowActionsDiv = row.find('.row-actions');
    let rowKey  = rowActionsDiv.attr('data-record-key');
    let spinner = rowActionsDiv.find('.loader');

    showRowActionSpinner(true, spinner);
    showRowActionButtons(false, rowActionsDiv);

    $.ajax({
        type: 'POST',
        url: route_detailsRecord,
        data: {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            'key': rowKey
        },
        success: function(response) {
            // console.warn(response);
            if (response && (onSuccess && (typeof onSuccess === 'function')))
            {
                var data = JSON.parse(response);
                data['rowKey'] = rowKey;

                onSuccess(data);
            }
            else
                alertModal.showDanger('The employee data could not be loaded or has been corrupt.');
        },
        error: function(xhr, status, error) {
            // console.warn(xhr.responseText);
            showGenericActionError();
        },
        complete: function() {
            showRowActionSpinner(false, spinner);
            showRowActionButtons(true, rowActionsDiv);
        }
    });
}

function handleFormSubmit()
{
    if (!validateForm())
        return;

    showProgressLoader(true);
    disableControls();

    let postData = {
        '_token'       : metaCSRF,
        'input-role'   : $("#input-role").val(),
        'input-id-no'  : $("#input-id-no").val(),
        'input-fname'  : $("#input-fname").val(),
        'input-mname'  : $("#input-mname").val(),
        'input-lname'  : $("#input-lname").val(),
        'input-email'  : $("#input-email").val(),
        'input-contact': $("#input-contact-no").val()
    };

    let submitTarget = '';
    
    switch (formMode) 
    {
        case FormMode_Edit:
            submitTarget = employeeForm.attr('data-post-update-target');
            postData['row-key'] = $('#record-key').val();
            break;

        default:
        case FormMode_Create:
            submitTarget = employeeForm.attr('data-post-create-target');
            postData['save_qr_copy'] = $('#optionSaveQRLocalCopy').is(':checked');
            break;
    }

    $.ajax({
        url: submitTarget,
        type: 'POST',
        data: postData,
        success: function(response)
        {
            if (response)
            {
                var data = JSON.parse(response);

                // Validation Failed
                if (data.validation_stat == 400)
                {
                    for (var field in data.errors)
                    {
                        showTextboxError(`#${field}`, data.errors[field]);
                    }

                    return;
                }

                if ('code' in data && data.code != 0)
                {
                    closeForm();
                    let message = data.message.replace(/\r?\n/g, '<br>');
                    alertModal.showDanger(message);

                    return;
                }

                // Successful inserts; display newly added record
                if (formMode == FormMode_Edit)
                    $(document).trigger('employeeFormUpdateSuccess', [data, selectedRowForEdit]);
                else
                    $(document).trigger('employeeFormInsertSuccess', [data]);

                closeForm();    
            }
        },
        error: function(xhr, status, error) 
        {
            showGenericActionError();
        },
        complete: function() {
            // Enable the control buttons when the operation
            // has completed either successfully or not
            enableControls();
            showProgressLoader(false);
        }
    });
}

function closeForm()
{
    clearForm();
    formModal.hide();
}

function handleFormClosed()
{
    if (dirty)
    {
        alertModal.showWarn('You have unsaved changes. Do you wish to cancel the operation?', 'Warning', 
            // OK CLICKED
            () => clearForm(),

            // CANCELLED, show the form again
            () => formModal.show());

        return;
    }

    // Do not clear the form when it was cancelled during edit mode
    // as cancelling it was temporary closing the form to show the
    // warning dialog. We need to persist the field values
    if (!dirty && formMode == FormMode_Edit && formCancelled)
        clearForm();
}

function validateForm()
{
    let valid = true;

    employeeForm.find('input[type="text"]:required').each(function()
    {
        if (!$(this).val() || $(this).val().length == 0)
        {
            var placeholder = $(this).attr('placeholder');
            var id = $(this).attr('id');

            showTextboxError(`#${id}`, `${placeholder} must be filled out`);

            $(this).focus();

            dirty = true;
            valid = false;
            return false;
        }
    });

    return valid;
}

function enableControls()
{
    formSubmitButton.prop('disabled', false);
    formCancelButton.prop('disabled', false);
}

function disableControls()
{
    formSubmitButton.prop('disabled', true);
    formCancelButton.prop('disabled', true);
}

function showProgressLoader(show) 
{
    if (show) {
        $('.progress-loader-wrapper').fadeIn('fast');
        return;
    }

    $('.progress-loader-wrapper').hide();
}

function showGenericActionError() {
    alertModal.showDanger("The requested action cannot be processed because of an error. Please try again later.", "Failure");
}