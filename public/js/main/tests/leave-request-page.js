//
// Uses revealing module pattern:
//
var leaveRequestPage = (function ()
{
    // JQuery selectors prefixed with 'jq'
    const jq_RECORDS_TABLE = "#records-table";
    const jq_INPUT_EMP_NO = '#input-id-no';
    const jq_LEAVE_REQ_MODAL = '#leaveRequestModal';

    const STATUS_PENDING = 'pending';

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
        leaveReqModal    = new mdb.Modal($(jq_LEAVE_REQ_MODAL));

        // Load employee id numbers into autocomplete textbox
        var inputIdNo = to_auto_suggest_ajax(jq_INPUT_EMP_NO,
            {
                'action': $(jq_RECORDS_TABLE).data('src-emp-ids'),
                'csrfToken': getCsrfToken(),
            },
            (dataSource) => employeeMapping = dataSource
        );

        formElements = {
            mainForm     : $('#frm-leave-request'),
            inputEmpName : $('#input-employee-name'),
            btnSave      : $(jq_LEAVE_REQ_MODAL).find('.btn-save'),
            btnCancel    : $(jq_LEAVE_REQ_MODAL).find('.btn-cancel'),
            isDirty      : false,
            fields: {
                'idNo'          : { label: 'ID Number', input: inputIdNo },
                'startDate'     : { label: 'Start Date', input: to_date_picker("#input-leave-start") },
                'endDate'       : { label: 'End Date', input: to_date_picker("#input-leave-end") },
                'leaveType'     : { label: 'Leave Type', input: to_droplist('#input-leave-type') },
                'leaveStatus'   : { label: 'Leave Status', input: to_droplist('#input-leave-status') },
            }
        };

        recordFilters = {
            'month' : to_droplist('#input-month-filter'),
            'role'  : to_droplist('#input-role-filter'),
            'leave' : to_droplist('#input-leave-filter'),
            'status': to_droplist('#input-status-filter')
        };

        // $(jq_LEAVE_REQ_MODAL).on('hidden.mdb.modal', function ()
        // {
        //     alert('closed')
        // });

        bindTableDataSource();
    };

    //============================
    // Event Handling
    //============================

    var handleEvents = function ()
    {
        Object.keys(formElements.fields).forEach(k =>
        {
            // When all inputs inside the <form> are interacted,
            // flag the form as 'dirty'
            formElements.fields[k].input.getInput().on('input', function ()
            {
                if ($(this).val())
                    formElements.isDirty = true;
            });
        });

        // Reflect the employee's name when an employee id was selected
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

            if (!isObjectEmpty(validation.errorFields))
            {
                Object.keys(validation.errorFields).forEach(k =>
                {
                    let field = validation.errorFields[k];
                    let msg   = `${field.label} must be filled out`;
                    let elem  = field.input.getInput();

                    (field.input.getType() === 'droplist') ?
                        showDroplistError(elem, msg) :
                        showTextboxError(elem, msg);
                });
                return;
            }

            submitForm(validation.passedFields);
        });

        formElements.btnCancel.on('click', () => 
        {
            leaveReqModal.hide();

            if (formElements.isDirty)
            {
                let message = 'You have unsaved changes. Do you wish to cancel the operation?';

                alertModal.showWarn(message, 'Warning',
                    // OK was clicked; clean-up the form inputs...    
                    () => closeLeaveRequestModal(true),

                    // CANCEL was clicked; bring back the modal...
                    () => leaveReqModal.show()
                );

                return;
            }
        });

        $('.filter-options-dialog .btn-clear').on('click',   () => applyFilters(false));
        $('.filter-options-dialog .btn-apply').on('click',   () => applyFilters(true));
        $('.filter-options-dialog .btn-close').on('click',   () => cancelFilter());
        $("#filters-dropdown-button").on('hide.bs.dropdown', () => cancelFilter());

        // When a row action button was clicked... 
        // Map button classes to their actions. 
        // We then find the class that the clicked button contains and 
        // use it to get the corresponding action from the actions object
        $(document).on('click', '.row-actions .btn', function () 
        {
            var actions = {
                'btn-delete'  : 'delete',
                'btn-approve' : 'approve',
                'btn-reject'  : 'reject'
            };

            var action = Object.keys(actions).find( k => this.classList.contains(k));

            if (action)
                executeRowActions( $(this).closest('tr'), actions[action] );
        });
    };

    //============================
    // Business Logic
    //============================

    /* #region  DATATABLES */

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
            className: 'td-date-to text-truncate',
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
            className: 'td-duration',
            width: '120px',
            data: 'duration',
            defaultContent: '',
        },
        // SIXTH Column -> Status
        {
            width: '120px',
            data: 'status',
            name: 'request-status',
            defaultContent: '',
        },
        // Seventh Column -> Actions
        {
            data: null,
            className: 'td-actions text-center position-sticky end-0 z-100 sticky-cell',
            name: 'row-actions',
            width: '100px',
            render: function (data, type, row)
            {
                var actionButtons = makeRowActionButtons(data.id, [ROW_ACTION_EDIT, ROW_ACTION_DELETE]);

                if (data.status.toLowerCase() == STATUS_PENDING)
                    actionButtons = makeRowActionButtons(data.id, [ROW_ACTION_APPROVE, ROW_ACTION_REJECT]);

                return sanitize(actionButtons);
            }
        }
    ];

    function bindTableDataSource()
    {
        disableControlButtons();

        let options = {
            "deferRender": true,
            'searching': false,
            'ordering': false,
            'autoWidth': true,
            'scrollX': true,
            'sScrollXInner': "80%",
            'columns': columnDefinitions,
            'drawCallback': function (settings) 
            {
                // dataTable_isFirstDraw is when the "Loading..." was first shown.
                // We need to show the alert message only when it is not on first draw
                // and when rows are empty
                if (dataTable_isFirstDraw)
                {
                    dataTable_isFirstDraw = false;
                    return;
                }

                var api = this.api();

                if (api.rows().count() === 0)
                {
                    snackbar.showInfo('No records to show');
                    return;
                }

                updateRowEntryNumbers(api)
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
                        '_token': getCsrfToken(),
                        'monthIndex': recordFilters.month.getValue(),
                        'role': recordFilters.role.getValue(),
                        'type': recordFilters.leave.getValue(),
                        'status': recordFilters.status.getValue(),
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
        if (applyFilter && (typeof applyFilter !== 'boolean'))
            return;

        // Clear the filters ...
        if (applyFilter === false)
        {
            // Reset the filters to default value, then hide the indicators
            Object.values(recordFilters).forEach(f => f.reset());
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
        Object.values(recordFilters).forEach(f =>
        {
            f.pushHistory(f.getValue());
        });

        // Update the filter indicator texts
        $('.lbl-month-filter').text(recordFilters.month.getText());
        $('.lbl-role-filter').text(recordFilters.role.getText());
        $('.lbl-leave-filter').text(recordFilters.leave.getText());
        $('.lbl-status-filter').text(recordFilters.status.getText());

        filtersContainer.hide();
    }

    function cancelFilter() 
    {
        // Read the last filter values from their history
        Object.values(recordFilters).forEach(f => f.pullHistory());

        filtersContainer.hide();
    }

    function executeRowActions(row, action)
    {
        let employeeName = row.find('.td-employee-name').text();
        let startDate    = row.find('.td-date-from').text();
        let endDate      = row.find('.td-date-to').text();
        let duration     = row.find('.td-duration').text();

        let message;

        switch (action) 
        {
            case 'delete':
                message = sanitize(`Are you sure you want to delete the leave request of "<b><i>${employeeName}</i></b>" which was made on <b><i>${startDate}</i></b> ?`);
                alertModal.showWarn(message, 'Delete', () => deleteRecord(row));
                break;

            case 'approve':
                message = `Are you sure you want to approve the leave request of "<i>${employeeName}</i>" from <b><i>${startDate}</i></b>, to <b><i>${endDate}</i></b>, for a duration of <b><i>${duration}</i></b> ?`;
                alertModal.showWarn(message, 'Approve', () => completeLeaveRequest(row, action));
                break;

            case 'reject':
                message = `Are you sure you want to reject the leave request of "<i>${employeeName}</i>" from <b><i>${startDate}</i></b>, to <b><i>${endDate}</i></b>, for a duration of <b><i>${duration}</i></b> ?`;
                alertModal.showWarn(message, 'Reject', () => completeLeaveRequest(row, action));
                break;
        }
    }

    function deleteRecord(row) 
    {
        let process = processRowActions(row);

        process.begin();

        $.ajax({
            url: route_deleteRecord,
            type: 'POST',
            data: {
                '_token': getCsrfToken(),
                'rowKey': process.getRowKey()
            },
            success: function (response) 
            {
                if (!response)
                {
                    onServerNoResponse();
                    return;
                }

                response = JSON.parse(response);

                // Success
                if (response.code === 0)
                {
                    dataTable.row(row).remove();
                    redrawTable(dataTable, true);

                    snackbar.showSuccess(response.message);
                }
                // Other Failure response
                else
                    alertModal.showDanger(response.message);
            },
            error: function (xhr, status, error) 
            {
                alertModal.showDanger('An unexpected error occurred while trying to delete the record.');
            },
            complete: () => process.end()
        });
    }

    function completeLeaveRequest(row, mode)
    {
        let process = processRowActions(row);

        let action = {
            'approve': route_approveRequest,
            'reject' : route_rejectRequest
        };

        process.begin();

        $.ajax({
            url: action[mode],
            type: 'POST',
            data: {
                '_token' : getCsrfToken(),
                'rowKey' : process.getRowKey()
            },
            success: function (response) 
            {
                if (!response)
                {
                    onServerNoResponse();
                    return;
                }

                response = JSON.parse(response);

                // Success
                if (response.code === 0)
                {
                    // Update the 'status' cell
                    dataTable.cell(row.index(), 'request-status:name').data(response.newStatus).draw();

                    // Update the 'row actions' cell with new action buttons
                    let actionButtons = makeRowActionButtons(response.rowKey, [ROW_ACTION_EDIT, ROW_ACTION_DELETE]);

                    dataTable.cell(row.index(), 'row-actions:name').data( sanitize(actionButtons) ).draw();

                    snackbar.showSuccess(response.message);
                }
                // Other Failure response
                else
                    alertModal.showDanger(response.message);
            },
            error: function (xhr, status, error) 
            {
                alertModal.showDanger('An unexpected error occurred while trying to modify the leave request.');
            },
            complete: () => process.end()
        });
    }

    /* #endregion */

    var validateEntries = function () 
    {
        let errorFields  = {};
        let passedFields = {};

        for (const key in formElements.fields)
        {
            let field = formElements.fields[key];

            if (field.input.getValue().trim() === '')
            {
                errorFields[key] = field;
                formElements.isDirty = true;
            }
        }

        if (isObjectEmpty(errorFields))
            passedFields = formElements.fields;

        return {
            errorFields: errorFields,
            passedFields: passedFields
        };
    };

    function submitForm(formData)
    {
        enableFormModalButtons(false);

        let postData = { '_token': getCsrfToken() };

        Object.keys(formData).forEach(key => postData[key] = formData[key].input.getValue());

        $.ajax({
            url     : formElements.mainForm.data('post-create-target'),
            type    : 'POST',
            data    : postData,
            success : function (response)
            {
                if (!response)
                {
                    onServerNoResponse();
                    return;
                }

                response = JSON.parse(response);
                console.warn(response);
                let statusActions =
                {
                    // Success
                    '0': function ()
                    {
                        let rowNode = dataTable.row.add({
                            'empname'   : response.rowData['empname'],
                            'type'      : response.rowData['type'],
                            'start'     : response.rowData['start'],
                            'end'       : response.rowData['end'],
                            'duration'  : response.rowData['duration'],
                            'status'    : response.rowData['status'],
                            'id'        : response.rowData['id']
                            //'row-actions'     : response.rowData['']
                        }).draw().node();

                        console.warn( dataTable.row(rowNode).index());
                        snackbar.showSuccess(response.message);
                    },

                    // Exceptions
                    '-1': () => alertModal.showDanger(response.message),

                    // Validation Error
                    '422': function () 
                    {
                        for (var field in response.errors)
                        {
                            var target = formElements.fields[field];
                            var element = target.input.getInput();

                            if (target.input.getType() == 'droplist')
                                showDroplistError(element, response.errors[field]);
                            else
                                showTextboxError(element, response.errors[field]);
                        }
                    }
                };

                if (response.code in statusActions)
                {
                    // We should only close the modal when it does not
                    // require the user to retry their entries.
                    if (response.code != '422')
                        closeLeaveRequestModal(true);

                    statusActions[response.code]();
                }
            },
            error: function (xhr, status, error) 
            {
                closeLeaveRequestModal(true);
                alertModal.showDanger('An unexpected has occurred. Please try again later.');
                //console.warn(xhr.responseText);
            },
            complete: function () 
            {
                enableFormModalButtons(true);
            }
        });
    }

    function onServerNoResponse()
    {
        alertModal.showDanger('The server did not respond. Please try again later.');
    }

    function closeLeaveRequestModal(clear) 
    {
        if (typeof clear === 'boolean' && clear === true)
        {
            Object.keys(formElements.fields).forEach(field =>
            {
                formElements.fields[field].input.reset()
            });

            formElements.inputEmpName.val('');
            formElements.isDirty = false;
        }

        leaveReqModal.hide();
    }

    function enableFormModalButtons(enable) 
    {
        var buttons = [formElements.btnSave, formElements.btnCancel];

        buttons.forEach(b => b.prop('disabled', !enable));
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

$(document).ready(function ()
{
    leaveRequestPage.init();
    leaveRequestPage.handle();
});
