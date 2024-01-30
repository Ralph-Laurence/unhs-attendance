//
// Uses revealing module pattern:
//
var leaveRequestPage = (function ()
{
    const tableSelector = "#records-table";

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

        requestFormInputs = {
            'ID Number' : $("#input-id-no"),
            'Start Date': $("#input-leave-start"),
            'End Date'  : $("#input-leave-start")
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

        btnSave.on('click', () => {
            
            var validation = validateEntries();

            if (validation != null && 'fieldName' in validation)
            {
                showTextboxError(validation.target, `${validation.fieldName} must be filled out`);
                validation.target.focus()
            }
        });

        // Hide the textbox error messages when they are interacted
        Object.values(requestFormInputs).forEach(input => {

            input.on('input', () => hideTextboxError(input));
        });
    };

    //============================
    // Business Logic
    //============================

    var validateEntries = function() 
    {
        for (const key in requestFormInputs)
        {
            var input = requestFormInputs[key];

            if (input.val().trim() === '')
            {
                return {
                    fieldName: key,
                    target: input
                };
            }
        }

        return null;
    };

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
