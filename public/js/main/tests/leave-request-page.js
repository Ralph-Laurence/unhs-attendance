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
    let dataTable_isFirstDraw = true;
    let iconStyles;
    
    let leaveRequestForm;
    let requestFormInputs;

    let inputEmployeeName;
    let inputEmpIdSelector = '#input-id-no';

    let global_monthFilter;
    let global_roleFilter;
    let global_typeFilter;
    let global_statusFilter;

    let monthFilter;
    let roleFilter;
    let leaveFilter;
    let statusFilter;

    let lblAttendanceRange;

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
            'input-leave-start' : { label: 'Start Date', input : $("#input-leave-start") },
            'input-leave-end'   : { label: 'End Date'  , input : $("#input-leave-end")   },
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

        monthFilter  = to_droplist('#input-month-filter');
        roleFilter   = to_droplist('#input-role-filter');
        leaveFilter  = to_droplist('#input-leave-filter');
        statusFilter = to_droplist('#input-status-filter');

        bindTableDataSource(monthFilter.getValue(), roleFilter.getValue());
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

        $('.filter-options-dialog .btn-cancel').on('click', function() 
        {    
            monthFilter.reset();
            roleFilter.reset();
            statusFilter.reset();
            leaveFilter.reset();

            applyFilters();

            $('.filter-indicators').hide();
        });

        $('.filter-options-dialog .btn-apply').on('click', function () 
        {
            applyFilters();

            $('.filter-indicators').show();
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
                        let message = nl2br(response.message);
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

    function bindTableDataSource(ref_monthIndex, ref_roleFilter, ref_typeFilter, ref_statusFilter)
    {
        disableControlButtons();

        let currentDate = getCurrentDateParts();

        global_roleFilter   = ref_roleFilter;
        global_monthFilter  = ref_monthIndex;
        global_typeFilter   = ref_typeFilter;
        global_statusFilter = ref_statusFilter;
        
        // dataTable.column('empname:name').search('Kim').draw()

        let options = {
            "deferRender": true,
            'searching': false,
            'ordering': false,
            'autoWidth': true,
            'scrollX': true,
            'sScrollXInner': "80%",
            'drawCallback': function () 
            {
                // dataTable_isFirstDraw is when the "Loading..." was first shown.
                // We need to show the alert message only when it is not on first draw
                // and when rows are empty
                if (dataTable_isFirstDraw)
                {
                    dataTable_isFirstDraw = false;
                    return;
                }

                var isEmpty = this.api().rows().count() === 0;

                if (isEmpty)
                {
                    snackbar.showInfo('No records to show');
                    return;
                }
            },

            ajax: {

                url: $('.dataset-table').data('src-default'),
                type: 'POST',
                dataType: 'JSON',
                dataSrc: function (json) 
                {
                    if (!json)
                        return;
                
                    if (iconStyles == undefined)
                        iconStyles = json.icon;

                    // Display Messages By Error Codes
                    if ('code' in json) 
                    {
                        // if (json.code == -1) 
                        // {
                        //     lblAttendanceRange.text('No data');
                        //     alertModal.showDanger(json.message);

                        //     return [];
                        // }
                    }

                    // After AJAX response, reenable the control buttons
                    enableControlButtons();

                    return json.data;
                },
                data: function () 
                {
                    return {
                        '_token':       csrfToken,
                        'monthIndex':   global_monthFilter,
                        'role':         global_roleFilter,
                        'type':         global_typeFilter,
                        'status':       global_statusFilter
                    }
                }
            },
            columns: [

                // First Column -> Record Counter
                {
                    width: '50px',
                    className: 'record-counter text-truncate position-sticky start-0 sticky-cell',
                    data: null,
                    render: function (data, type, row, meta)
                    {
                        return meta.row + 1;
                    }
                },
                // Second Column -> Employee Name
                {
                    className: 'td-employee-name text-truncate',
                    width: '280px',
                    data: 'empname',
                    name: 'empname',
                    defaultContent: ''
                },
                // Third Column -> Leave Type
                {
                    className: 'text-truncate',
                    width: '180px',
                    data: 'type',
                    defaultContent: ''
                },
                // Fourth Column -> Date From
                {
                    className: 'text-truncate',
                    width: '120px',
                    data: 'start',
                    render: function (data, type, row) 
                    {
                        let date = extractDate(data); 
                        return `${date.month} ${date.day}, ${date.year}`;
                    },
                    defaultContent: ''
                },
                // Fifth Column -> Date End
                {
                    className: 'text-truncate',
                    width: '120px',
                    data: 'end',
                    render: function (data, type, row) 
                    {
                        let date = extractDate(data); 
                        return `${date.month} ${date.day}, ${date.year}`;
                    },
                    defaultContent: ''
                },
                // SIXTH Column -> Duration
                {
                    width: '120px',
                    data: 'duration',
                    defaultContent: '',
                },
                // SIXTH Column -> Status
                {
                    width: '120px',
                    data: 'status',
                    defaultContent: '',
                },
                // Seventh Column -> Actions
                {
                    data: null,
                    className: 'text-center position-sticky end-0 z-100 sticky-cell',
                    width: '100px',
                    render: function (data, type, row)
                    {

                        //return data
                        return createRowDeleteAction(data.id);
                    }
                }
            ]
        };

        // If an instance of datatable has already been created,
        // reload its data source with given url instead
        if (dataTable != null)
        {
            dataTable.ajax.reload();
            return;
        }

        // Initialize datatable if not yet created
        dataTable = $('.dataset-table').DataTable(options);
    }

    function applyFilters()
    {
        bindTableDataSource(
            monthFilter.getValue(),
            roleFilter.getValue(),
            leaveFilter.getValue(),
            statusFilter.getValue()
        );

        $('.lbl-month-filter').text( monthFilter.getText() );
        $('.lbl-role-filter').text( roleFilter.getText() );
        $('.lbl-leave-filter').text( leaveFilter.getText() );
        $('.lbl-status-filter').text( statusFilter.getText() );

        $().text( statusFilter.getText() )
    }

    function enableControlButtons()
    {
        monthFilter.enable();
        roleFilter.enable();
    }

    function disableControlButtons()
    {
        monthFilter.disable();
        roleFilter.disable();
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
