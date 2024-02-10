//
// Uses revealing module pattern:
//
var leaveRequestPage = (function ()
{
    // JQuery selectors prefixed with 'jq'
    const jq_RECORDS_TABLE   = "#records-table";
    const jq_INPUT_EMP_NO    = '#input-id-no';
    const jq_LEAVE_REQ_MODAL = '#leaveRequestModal';

    const STATUS_PENDING     = 'pending';

    let leaveReqModal;
    let employeeMapping;
    let dataTable;
    let iconStyles;
    
    let formElements;

    let recordFilters = {};
    let filtersContainer;
    
    // State Flags
    let dataTable_isFirstDraw = true;

    //============================
    // Initialization
    //============================

    var initialize = function ()
    {
        filtersContainer = new mdb.Dropdown('.filter-options-dialog');
        leaveReqModal    = new mdb.Modal( $(jq_LEAVE_REQ_MODAL) );

        // $(document).on('keypress', function(e) {
        //     if (e.which == 117)
        //         leaveReqModal.show();
        // });

        formElements = {
            mainForm      : $('#frm-leave-request'),
            inputEmpName  : $('#input-employee-name'),
            btnSave       : $(jq_LEAVE_REQ_MODAL).find('.btn-save'),
            isDirty       : false,
            fields        : {
                'idNo'        : { label: 'ID Number'    , input : $(jq_INPUT_EMP_NO)      },
                'startDate'   : { label: 'Start Date'   , input : $("#input-leave-start") },
                'endDate'     : { label: 'End Date'     , input : $("#input-leave-end")   },
                'leaveType'   : { label: 'Leave Type'   , input : $("#input-leave-type")   ,type: 'droplist' },
                'leaveStatus' : { label: 'Leave Status' , input : $("#input-leave-status") ,type: 'droplist' },
            }
        };

        recordFilters = {
            'month'  : to_droplist('#input-month-filter'),
            'role'   : to_droplist('#input-role-filter'),
            'leave'  : to_droplist('#input-leave-filter'),
            'status' : to_droplist('#input-status-filter')
        };

        $(jq_LEAVE_REQ_MODAL).on('hidden.mdb.modal', function() {
            // alert('closed')
        });

        // Load employee id numbers into autocomplete textbox
        to_auto_suggest_ajax(jq_INPUT_EMP_NO, 
            {
                'action'    : $(jq_RECORDS_TABLE).data('src-emp-ids'),
                'csrfToken' : getCsrfToken(),
            },
            (dataSource) => employeeMapping = dataSource
        );

        to_date_picker("#input-leave-start");
        to_date_picker("#input-leave-end");

        to_droplist('#input-leave-type');
        to_droplist('#input-leave-status');

        bindTableDataSource();
    };
    
    //============================
    // Event Handling
    //============================

    var handleEvents = function ()
    {
        // An option was selected in the auto-suggest input
        $(jq_INPUT_EMP_NO).on('valueSelected', function ()
        {
            let needle = $(this).val();

            if (!(needle in employeeMapping)) 
            {
                formElements.inputEmpName.val('');
                return;
            }

            formElements.inputEmpName.val(employeeMapping[needle]);
        })
        .on('valueCleared', () => formElements.inputEmpName.val(''));

        // Handle form submission when save button was clicked
        formElements.btnSave.on('click', () => 
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

        $(document).on('click', '.row-actions .btn-delete',  function () 
        {
            let row = $(this).closest('tr');
            let employeeName = row.find('.td-employee-name').text();
            let date = row.find('.td-date-from').text();

            let message = sanitize(`Are you sure you want to delete the leave request of "<b><i>${employeeName}</i></b>" which was made on <b><i>${date}</i></b> ?`);

            alertModal.showWarn(message, 'Warning', () => deleteRecord(row));
        });

        $('.filter-options-dialog .btn-clear').on('click',   () => applyFilters(false));
        $('.filter-options-dialog .btn-apply').on('click',   () => applyFilters(true));
        $('.filter-options-dialog .btn-close').on('click',   () => cancelFilter());
        $("#filters-dropdown-button").on('hide.bs.dropdown', () => cancelFilter());
    };

    //============================
    // Business Logic
    //============================

    var columnDefinitions = [

        // First Column -> Record Counter
        {
            width: '50px',
            className: 'record-counter text-truncate position-sticky start-0 sticky-cell',
            name: 'record-number',
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
            className: 'td-date-from text-truncate',
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

                var html = `<div class="row-actions" data-record-key="${data.id}">
                                <div class="loader d-none"></div>
                                {-actions-}
                            </div>`;

                if (data.status.toLowerCase() == STATUS_PENDING)
                {
                    var actionButtons =
                        `<button class="btn btn-sm btn-approve"> 
                            <i class="fa-solid fa-thumbs-up"></i> 
                        </button>
                        <button class="btn btn-sm btn-disapprove"> 
                            <i class="fa-solid fa-thumbs-down"></i> 
                        </button>`;

                    html = html.replace(/{-actions-}/g, actionButtons);
                }
                else
                {
                    var actionButtons =
                        `<button class="btn btn-sm btn-edit"> 
                            <i class="fa-solid fa-pen"></i> 
                        </button>
                        <button class="btn btn-sm btn-delete"> 
                            <i class="fa-solid fa-trash"></i> 
                        </button>`;

                    html = html.replace(/{-actions-}/g, actionButtons);
                }
                
                return sanitize(html);
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
            'drawCallback'  : function (settings) 
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

                // Re assign the row numbers
                var api = this.api();
                var startIndex = api.context[0]._iDisplayStart;

                api.column(0, {page: 'current'}).nodes().each( 
                    (cell, i) => cell.innerHTML = startIndex + i + 1 
                );
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
                        '_token':       getCsrfToken(),
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

    function applyFilters(applyFilter)
    {
        // Do not apply filter if param is invalid
        if (applyFilter && (typeof applyFilter !== 'boolean') )
            return;

        // Clear the filters ...
        if (applyFilter === false)
        {
            // Reset the filters to default value, then hide the indicators
            Object.values(recordFilters).forEach( f => f.reset() );
            $('.filter-indicators').addClass('d-hidden');
        }
        else
        {
            // Show filter indicators
            $('.filter-indicators').removeClass('d-hidden');
        }

        // Execute the record filters
        bindTableDataSource();

        // Push the last filter applied into its history
        Object.values(recordFilters).forEach( f => {
            f.pushHistory( f.getValue() );
        });

        // Update the filter indicator texts
        $('.lbl-month-filter').text( recordFilters.month.getText() );
        $('.lbl-role-filter').text( recordFilters.role.getText() );
        $('.lbl-leave-filter').text( recordFilters.leave.getText() );
        $('.lbl-status-filter').text( recordFilters.status.getText() );

        filtersContainer.hide();
    }

    function cancelFilter() 
    {
        // Read the last filter values from their history
        Object.values(recordFilters).forEach( f => f.pullHistory());

        filtersContainer.hide();
    }

    var validateEntries = function() 
    {
        for (const key in formElements.fields)
        {
            var field = formElements.fields[key];

            if (field.input.val().trim() === '')
            {
                field['status'] = -1;
                return field;
            }
        }

        return {
            status: 0,
            validated: formElements.fields
        };
    };

    function submitForm(formData)
    {
        formElements.btnSave.prop('disabled', true);

        let postData = {
            '_token': getCsrfToken()
        };

        Object.keys(formData).forEach(key => postData[key] = formData[key].input.val() );

        $.ajax({
            url: formElements.mainForm.data('post-create-target'),
            type: 'POST',
            data: postData,
            success: function(response)
            {
                if (!response)
                {
                    onServerNoResponse();
                    return;
                }

                response = JSON.parse(response);

                var statusActions =
                {
                    // Success
                    '0': function () 
                    {
                        closeLeaveRequestModal();
                        snackbar.showSuccess(response.message);
                    },

                    // Validation Error
                    '400': function () 
                    {
                        for (var field in response.errors)
                        {
                            var element = formElements.fields[field];

                            if (element.type == 'droplist')
                                showDroplistError(element.input, response.errors[field]);
                            else
                                showTextboxError(element.input, response.errors[field]);
                        }
                    }
                };

                statusActions[response.code]();
            },
            error: function(xhr, status, error) 
            {
                alertModal.showDanger( 'An unexpected has occurred. Please try again later.' );
                console.warn(xhr.responseText);
            },
            complete: function() 
            {
                formElements.btnSave.prop('disabled', false);
            }
        });
    }

    function deleteRecord(row) 
    {
        let rowActionsDiv = row.find('.row-actions');
        let rowKey  = rowActionsDiv.attr('data-record-key');
        let spinner = rowActionsDiv.find('.loader');
    
        showRowActionSpinner(true, spinner);
        showRowActionButtons(false, rowActionsDiv);
    
        $.ajax({
            url: route_deleteRecord,
            type: 'POST',
            data: {
                '_token' : getCsrfToken(),
                'rowKey' : rowKey
            },
            success: function(response) 
            {
                closeLeaveRequestModal();

                if (response)
                {
                    response = JSON.parse(response);
                    
                    // Success
                    if (response.code === 0)
                    {
                        dataTable.row(row).remove().draw();
                        snackbar.showSuccess(response.message);
                    }

                    // Other Failure response
                    else
                        alertModal.showDanger(response.message);
                }
                else
                    onServerNoResponse();
            },
            error: function(xhr, status, error) 
            {
                alertModal.showDanger('An unexpected error occurred while trying to delete the record.');
            },
            complete: function()
            {
                showRowActionSpinner(false, spinner);
                showRowActionButtons(true, rowActionsDiv);
            }
        })
    }

    function onServerNoResponse()
    {
        alertModal.showDanger('The server did not respond. Please try again later.');
    }

    function closeLeaveRequestModal(clear) 
    {
        if (clear && typeof clear === 'boolean' && clear === true)
        {

        }

        leaveReqModal.hide();
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
