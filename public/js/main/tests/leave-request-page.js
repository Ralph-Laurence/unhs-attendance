//
// Uses revealing module pattern:
//
var leaveRequestPage = (function ()
{
    // JQuery selectors prefixed with 'jq'
    const jq_RECORDS_TABLE = "#records-table";

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

    // This will be used to track last actions (i.e. last clicked row)    
    let stateStackFrame = {};

    //============================
    // Initialization
    //============================

    var initialize = function ()
    {
        filtersContainer = new mdb.Dropdown('.filter-options-dialog');

        leaveReqModal = {
            getSelector     : () => '#leaveRequestModal',
            __instance      : null,
            __mode          : 0,
            MODE_DEFAULT    : 0,
            MODE_CREATE     : 1,
            MODE_UPDATE     : 2,
            getMode         : function()     { return this.__mode },
            setMode         : function(mode) { this.__mode = mode },
            clearMode       : function()     { this.__mode = this.MODE_DEFAULT },
            getModalTitle   : function(mode)
            {
                let titles = {
                    [this.MODE_DEFAULT] : 'Employee Leave',
                    [this.MODE_CREATE]  : 'Create Employee Leave',
                    [this.MODE_UPDATE]  : 'Update Employee Leave'
                };

                return titles[mode];
            },
            getCancelButton : function() { return $(this.getSelector).find('.btn-cancel') },
            getSaveButton   : function() { return $(this.getSelector).find('.btn-save') },
            getInstance     : function() 
            {
                if (this.__instance === null)
                    this.__instance = new mdb.Modal( $(this.getSelector()) );

                return this.__instance;
            },
            show : function() 
            {
                let title = this.getModalTitle( this.getMode() );
                $(this.getSelector).find("#leaveRequestModalLabel").text(title)

                this.getInstance().show();
            },
            hide   : function() { this.getInstance().hide() },
            finish : function() 
            {
                Object.keys(formElements.fields).forEach(field =>
                {
                    formElements.fields[field].input.reset()
                });
            
                formElements.isDirty = false;
                this.clearMode();
                this.hide();
            }
        };

        // Load employee id numbers into autocomplete textbox
        var inputIdNo = to_auto_suggest_ajax('#input-id-no',
            {
                'action'   : $(jq_RECORDS_TABLE).data('src-emp-ids'),
                'csrfToken': getCsrfToken(),
            },
            (dataSource) => employeeMapping = dataSource
        );

        formElements = {
            mainForm : $('#frm-leave-request'),
            isDirty  : false,
            fields   : {
                'idNo'          : { label: 'ID Number'      , input: inputIdNo },
                'empName'       : { label: null             , input: to_textbox('#input-employee-name'), nullable: true },
                'updateKey'     : { label: null             , input: to_textbox('#input-update-key')   , nullable: true },
                'startDate'     : { label: 'Start Date'     , input: to_date_picker("#input-leave-start")   },
                'endDate'       : { label: 'End Date'       , input: to_date_picker("#input-leave-end")     },
                'leaveType'     : { label: 'Leave Type'     , input: to_droplist('#input-leave-type')       },
                'leaveStatus'   : { label: 'Leave Status'   , input: to_droplist('#input-leave-status')     },
            }
        };

        recordFilters = {
            'month' : to_droplist('#input-month-filter'),
            'role'  : to_droplist('#input-role-filter'),
            'leave' : to_droplist('#input-leave-filter'),
            'status': to_droplist('#input-status-filter')
        };

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
        formElements.fields.idNo.input.getInput().on('valueSelected', function ()
        {
            let needle = $(this).val();

            if (!(needle in employeeMapping)) 
            {
                formElements.fields.empName.input.reset();
                return;
            }
            
            formElements.fields.empName.input.setValue(employeeMapping[needle]);
        })
        .on('valueCleared', () => formElements.fields.empName.input.reset());

        // Handle form submission when save button was clicked
        leaveReqModal.getSaveButton().on('click', () => 
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

        leaveReqModal.getCancelButton().on('click', () => 
        {
            leaveReqModal.hide();

            if (formElements.isDirty)
            {
                let message = "Your changes will not be saved unless you choose to save them. Do you wish to abort the operation?";

                alertModal.showWarn(message, 'Warning',
                     
                    () => finishLeaveReqModal(),    // OK was clicked; clean-up the form inputs...   
                    () => leaveReqModal.show()      // CANCEL was clicked; bring back the modal...
                );

                return;
            }
        });

        // Dropdown option "Create Leave Request"
        $('#opt-add-leave-request').on('click', () => {

            leaveReqModal.setMode( leaveReqModal.MODE_CREATE );
            leaveReqModal.show();
            formElements.fields.idNo.input.enable();
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
                'btn-edit'    : 'edit',
                'btn-delete'  : 'delete',
                'btn-approve' : 'approve',
                'btn-reject'  : 'reject'
            };

            var action = Object.keys(actions).find( k => this.classList.contains(k));

            if (action)
            {
                let row = $(this).closest('tr');
                
                if (!row)
                {
                    alertModal.showWarn("Unable to process the requested action because the current row can't be read. Please try again later.");
                    return;
                }

                stateStackFrame['lastClickedRow'] = row;
                executeRowActions( row, actions[action] );
            }
        });
    };

    //============================
    // Business Logic
    //============================

    /* #region  DATATABLES */

    var columnDefinitions = [

        // First Column -> Record Counter
        {
            width: '80px',
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

                updateRowEntryNumbers(api);

                // Highlight the newly added / updated row

                // $('.simplebar-content-wrapper').scrollTop($('body').height());
                if ('newRowInstance' in dataTable && dataTable.newRowInstance !== null)
                {
                    if (!dataTable.newRowInstance.node())
                        return;

                    let rowNode = dataTable.newRowInstance.node();
                    
                    scrollRowToView(rowNode, {
                        'afterScroll': function () 
                        {
                            setTimeout(function() 
                            {
                                flashRow(rowNode, () => dataTable.newRowInstance = null);
                            }, 800);
                        }
                    });
                }
            },
            ajax: {

                url         : route_getDataSource, //$('.dataset-table').data('src-default'),
                type        : 'POST',
                dataType    : 'JSON',
                dataSrc     : function (json) 
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
            case 'edit':
                editRecord(row);
                break;

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

    function editRecord(row) 
    {
        leaveReqModal.setMode( leaveReqModal.MODE_UPDATE );
        formElements.fields.idNo.input.disable();

        let process = processRowActions(row);
        process.begin();

        $.ajax({
            url: route_editRecord,
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
                    let startDate = extractDateInt(response.data.start);
                    let endDate   = extractDateInt(response.data.end);

                    formElements.fields['idNo'       ].input.setText( response.data.idNo );
                    formElements.fields['empName'    ].input.setValue( response.data.empname );
                    formElements.fields['startDate'  ].input.setValue( startDate.year, (startDate.month - 1), startDate.day );
                    formElements.fields['endDate'    ].input.setValue( endDate.year, (endDate.month - 1), endDate.day );
                    formElements.fields['leaveType'  ].input.setValue( response.data.type   );
                    formElements.fields['leaveStatus'].input.setValue( response.data.status );
                    formElements.fields['updateKey'  ].input.setValue( process.getRowKey() );

                    leaveReqModal.show();
                }
                // Other Failure response
                else
                    alertModal.showDanger(response.message);
            },
            error: function (xhr, status, error) 
            {
                alertModal.showDanger('An unexpected error occurred while trying to modify the record.');
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
                    let dtRow   = dataTable.row(row);
                    let rowData = dtRow.data();

                    dataTable.newRowInstance = dtRow;

                    rowData.status = response.newStatus;
                    dtRow.data(rowData).invalidate().draw(false);

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

            if ('nullable' in field && field.nullable === true)
                continue;

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
            url     : formElements.mainForm.data('post-target'),
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
                
                let lastSelectedRow;

                if (response.rowData.transaction == 1)
                    lastSelectedRow = stateStackFrame.lastClickedRow;
                
                finishLeaveReqModal();

                if (response.code != 0) 
                {
                    alertModal.showDanger(response.message);
                    return;
                }

                let newRow = {};

                switch (response.rowData.transaction) 
                {
                    // Insert
                    case 0:
                        newRow = dataTable.row.add({
                            'empname'   : response.rowData['empname'],
                            'type'      : response.rowData['type'],
                            'start'     : response.rowData['start'],
                            'end'       : response.rowData['end'],
                            'duration'  : response.rowData['duration'],
                            'status'    : response.rowData['status'],
                            'id'        : response.rowData['id']
                        });
                        // store the new row instance
                        dataTable.newRowInstance = newRow;  
                        break;

                    // Update
                    case 1:

                        if (lastSelectedRow)
                        {
                            console.warn(lastSelectedRow);
                            let temp_row = dataTable.row(lastSelectedRow);
                            let temp_rowData = temp_row.data();

                            temp_rowData.empname  = response.rowData['empname'];
                            temp_rowData.type     = response.rowData['type'];
                            temp_rowData.start    = response.rowData['start'];
                            temp_rowData.end      = response.rowData['end'];
                            temp_rowData.duration = response.rowData['duration'];
                            temp_rowData.status   = response.rowData['status'];

                            newRow = dataTable.row(temp_row).data(temp_rowData).invalidate().draw(false);
                            // store the new row instance
                            dataTable.newRowInstance = newRow;  
                        }
                        
                        break;
                }

                var rowIndex   = newRow.index();
                var pageNumber = Math.ceil((rowIndex + 1) / dataTable.page.len());

                // Go to the page
                dataTable.page(pageNumber - 1).draw(false);
                

                snackbar.showSuccess(response.message);
            },
            error: function (xhr, status, error) 
            {
                // Validation Error
                if (xhr.status === 422)
                {
                    let errorFields = xhr.responseJSON.errors;
                    
                    for (var field in errorFields)
                    {
                        let message = errorFields[field];
                        let target  = formElements.fields[field];
                        let element = target.input.getInput();

                        if (target.input.getType() == 'droplist')
                            showDroplistError(element, message);
                        else
                            showTextboxError(element, message);
                    }

                    return;
                }

                // General Error
                finishLeaveReqModal();
                alertModal.showDanger('An unexpected has occurred. Please try again later.');
            },
            complete: () => enableFormModalButtons(true)
        });
    }

    function onServerNoResponse()
    {
        alertModal.showDanger('The server did not respond. Please try again later.');
    }

    function finishLeaveReqModal()
    {
        leaveReqModal.finish();

        var inputIdNo = formElements.fields.idNo.input;
        
        if (!inputIdNo.isEnabled())
            inputIdNo.enable();

        // Clear the reference to the last clicked table row
        stateStackFrame.lastClickedRow = null;
    }

    function enableFormModalButtons(enable) 
    {
        var buttons = [leaveReqModal.getSaveButton(), leaveReqModal.getCancelButton()];
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
        handle: handleEvents,
        stackFrame: stateStackFrame
    };

})();

$(document).ready(function ()
{
    leaveRequestPage.init();
    leaveRequestPage.handle();
});
