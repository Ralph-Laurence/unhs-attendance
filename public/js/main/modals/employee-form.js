let dirty = false;
let formModal = null;
let employeeFormModal = '#employeeFormModal';
let employeeForm = undefined;
let formSubmitButton = undefined;
let formCancelButton = undefined;

let metaCSRF = undefined;

$(document).ready(function ()
{
    metaCSRF = $('meta[name="csrf-token"]').attr('content');

    formModal = new mdb.Modal($(employeeFormModal));
    employeeForm = $(`${employeeFormModal} form`);

    formSubmitButton = $(`${employeeFormModal} .btn-save`);
    formCancelButton = $(`${employeeFormModal} .btn-cancel, #employeeFormModal .btn-close`);

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

    formCancelButton.on('click', () => formModal.hide());
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
}

function handleFormSubmit()
{
    if (!validateForm())
        return;

    showProgressLoader(true);
    disableControls();

    let submitTarget = employeeForm.attr('data-post-create-target');

    $.ajax({
        url: submitTarget,
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': metaCSRF },
        data: {
            'input-id-no'  : $("#input-id-no").val(),
            'input-fname'  : $("#input-fname").val(),
            'input-mname'  : $("#input-mname").val(),
            'input-lname'  : $("#input-lname").val(),
            'input-email'  : $("#input-email").val(),
            'input-contact': $("#input-contact-no").val(),
            'save_qr_copy' : $('#optionSaveQRLocalCopy').is(':checked')
        },
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

                clearForm();
                formModal.hide();

                // Successful inserts; display newly added record
                $(document).trigger('employeeFormInsertSuccess', [data]);
            }
        },
        error: function(xhr, status, error) 
        {
            alertModal.showDanger("The requested action cannot be processed because of an error. Please try again later.", "Failure");
        },
        complete: function() {
            // Enable the control buttons when the operation
            // has completed either successfully or not
            enableControls();
            showProgressLoader(false);
        }
    });
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
    }
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