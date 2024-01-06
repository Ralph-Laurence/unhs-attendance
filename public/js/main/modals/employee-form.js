let dirty = false;
let formModal = null;
let employeeFormModal = '#employeeFormModal';
let employeeForm = undefined;
let formSubmitButton = undefined;
let metaCSRF = undefined;

const errorBox = '.error-box';

$(document).ready(function ()
{
    metaCSRF = $('meta[name="csrf-token"]').attr('content');

    formModal = new mdb.Modal($(employeeFormModal));
    employeeForm = $(`${employeeFormModal} form`);

    formSubmitButton = $(`${employeeFormModal} .btn-save`);

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

    $(`${employeeFormModal} .btn-cancel, #employeeFormModal .btn-close`).on('click', () => formModal.hide());
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

    formSubmitButton.prop('disabled', true);

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
        },
        success: function(response)
        {
            console.warn(response)

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
        error: function(xhr, status, error) {
            console.warn(xhr.responseText)
        },
        complete: function() {
            // Enable the Submit (save) button when the operation
            // has completed either successfully or not
            formSubmitButton.prop('disabled', false);
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

            // CANCELLED
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