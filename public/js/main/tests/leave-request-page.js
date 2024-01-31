//
// Uses revealing module pattern:
//
var leaveRequestPage = (function ()
{
    const tableSelector = "#records-table";
    const formSelector  = "#frm-leave-request";

    let csrfToken;
    let employeeMapping;
    let dataTable;
    
    let leaveRequestForm;
    let requestFormInputs;

    let inputEmployeeName;
    let inputEmpIdSelector = '#input-id-no';

    let btnSave;
    
    //============================
    // Initialization
    //============================

    var initialize = function ()
    {
        csrfToken         = $('meta[name="csrf-token"]').attr('content');
        inputEmployeeName = $('#input-employee-name');
        leaveRequestForm  = $('#leaveRequestForm');

        requestFormInputs = 
        {
            'input-id-no'       : { label: 'ID Number' , input : $(inputEmpIdSelector)    },
            'input-leave-start'  : { label: 'Start Date', input : $("#input-leave-start") },
            'input-leave-end'    : { label: 'End Date'  , input : $("#input-leave-end")   },
            'input-leave-type'  : { label: 'Leave Type', input : $("#input-leave-type")  , type: 'droplist' },
        };

        btnSave = leaveRequestForm.find('.btn-save');

        // Load employee id numbers into autocomplete textbox
        to_auto_suggest_ajax(
            inputEmpIdSelector, 
            {
                action: $(tableSelector).data('src-emp-ids'),
                csrfToken: csrfToken,
            },
            (dataSource) => employeeMapping = dataSource
        );

        to_date_picker("#input-leave-start");
        to_date_picker("#input-leave-end");

        to_droplist('#input-leave-type');
    };
    
    //============================
    // Event Handling
    //============================

    var handleEvents = function ()
    {
        // When an option was selected in the auto-suggest input
        $(inputEmpIdSelector).on('valueSelected', function ()
        {
            let needle = $(this).val();

            if (!(needle in employeeMapping)) {
                inputEmployeeName.val('');
                return;
            }

            inputEmployeeName.val(employeeMapping[needle]);
        })
        .on('valueCleared', () => inputEmployeeName.val(''));

        // Handle form submission when save button was clicked
        btnSave.on('click', () => 
        {
            let validation = validateEntries();
            console.warn(validation);

            //if (validation != null && 'status' in validation && validation.status === -1)
            if (validation?.status ?? null === -1)
            {
                let msg = `${validation.label} must be filled out`;

                //if ('type' in validation && validation.type === 'droplist')
                (validation.type === 'droplist') ?
                    showDroplistError(validation.input, msg) :
                    showTextboxError(validation.input, msg);

                validation.input.focus();
                return;
            }

            submitForm(validation.validated);
        });
    };

    //============================
    // Business Logic
    //============================

    var validateEntries = function() 
    {
        for (const key in requestFormInputs)
        {
            var field = requestFormInputs[key];

            if (field.input.val().trim() === '')
            {
                field['status'] = -1;
                return field;
            }
        }

        return {
            status: 0,
            validated: requestFormInputs
        };
    };

    function submitForm(formData)
    {
        btnSave.prop('disabled', true);

        let postData = {
            '_token': csrfToken
        };

        Object.keys(formData).forEach(key => postData[key] = formData[key].input.val() );

        $.ajax({
            url: $(formSelector).data('post-create-target'),
            type: 'POST',
            data: postData,
            success: function(response)
            {
                if (response)
                {
                    console.warn(response)
                    response = JSON.parse(response);

                    // Validation Failed
                    if (response.validation_stat == 400)
                    {
                        for (var field in response.errors)
                        {
                            var fieldType = requestFormInputs[field].type;
                            
                            if (fieldType == 'droplist')
                                showDroplistError(`#${field}`, response.errors[field]);
                            else
                                showTextboxError(`#${field}`, response.errors[field]);
                        }

                        return;
                    }

                    if ('code' in response && response.code != 0)
                    {
                        //closeForm();
                        let message = nl2br(data.message);
                        //alertModal.showDanger(message);
                        alert(message)
                        return;
                    }
                }
            },
            error: function(xhr, status, error) 
            {
                console.warn(xhr.responseText);
            },
            complete: function() 
            {
                btnSave.prop('disabled', false);
            }
        });
    }

    // End of Revealing Module Pattern
    return {
        init: initialize,
        handle: handleEvents
    };

})();

$(document).ready(function()
{
    leaveRequestPage.init();
    leaveRequestPage.handle();
});
