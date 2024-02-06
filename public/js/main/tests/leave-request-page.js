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

    let recordFilters = {};
    let filterOptions;

    let btnSave;
    
    //============================
    // Initialization
    //============================

    var initialize = function ()
    {
        csrfToken         = $('meta[name="csrf-token"]').attr('content');
        inputEmployeeName = $('#input-employee-name');
        leaveRequestForm  = $('#leaveRequestForm');

        filterOptions     = new mdb.Dropdown('.filter-options-dialog');

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

        recordFilters = 
        {
            'month'  : to_droplist('#input-month-filter'),
            'role'   : to_droplist('#input-role-filter'),
            'leave'  : to_droplist('#input-leave-filter'),
            'status' : to_droplist('#input-status-filter')
        };

        bindTableDataSource();
    };
    
    //============================
    // Event Handling
    //============================

    var handleEvents = function ()
    {
        // An option was selected in the auto-suggest input
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

        $('.filter-options-dialog .btn-clear').on('click', function() 
        {
            Object.values(recordFilters).forEach( f => f.reset() );

            applyFilters();
            filterOptions.hide();

            $('.filter-indicators').addClass('d-hidden'); //.hide();
        });

        $('.filter-options-dialog .btn-apply').on('click', function () 
        {
            applyFilters();
            filterOptions.hide();
            
            $('.filter-indicators').removeClass('d-hidden'); //.show();
        });

        $('.filter-options-dialog .dropdown-menu .btn-close').on('click', () => filterOptions.hide());

        // $(document).on('keypress', (e) => {
            
        //     if (e.which === 121)    // Y
        //         alert( monthFilter.getLastValue() );

        //     if (e.which == 117) // U
        //         monthFilter.setLastValue('hello');
        // });
    };

    //============================
    // Business Logic
    //============================

    var columnDefinitions = [

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
    ];

    function bindTableDataSource()
    {
        disableControlButtons();

        let options = {
            "deferRender"   : true,
            'searching'     : false,
            'ordering'      : false,
            'autoWidth'     : true,
            'scrollX'       : true,
            'sScrollXInner' : "80%",
            'columns'       : columnDefinitions,
            'drawCallback'  : function () 
            {
                // dataTable_isFirstDraw is when the "Loading..." was first shown.
                // We need to show the alert message only when it is not on first draw
                // and when rows are empty
                if (dataTable_isFirstDraw)
                {
                    dataTable_isFirstDraw = false;
                    return;
                }

                if (this.api().rows().count() === 0)
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
                        if (json.code == -1) 
                        {
                            alertModal.showDanger(json.message);
                            return [];
                        }
                    }

                    // After AJAX response, reenable the control buttons
                    enableControlButtons();

                    return json.data;
                },
                data: function () 
                {
                    return {
                        '_token':       csrfToken,
                        'monthIndex':   recordFilters.month.getValue(),
                        'role':         recordFilters.role.getValue(),
                        'type':         recordFilters.leave.getValue(),
                        'status':       recordFilters.status.getValue(),
                    }
                }
            }
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
        // Execute the record filters
        bindTableDataSource();

        // Push the last filter applied into its history
        Object.values(recordFilters).forEach( f => {
            f.setLastValue( f.getValue() );
        });

        $('.lbl-month-filter').text( recordFilters.month.getText() );
        $('.lbl-role-filter').text( recordFilters.role.getText() );
        $('.lbl-leave-filter').text( recordFilters.leave.getText() );
        $('.lbl-status-filter').text( recordFilters.status.getText() );
    }

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
                        let message = nl2br(response.message);
                        
                        alert(message)
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

    function enableControlButtons()
    {
        // monthFilter.enable();
        // roleFilter.enable();
    }

    function disableControlButtons()
    {
        // monthFilter.disable();
        // roleFilter.disable();
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
