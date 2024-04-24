let employeeLeave = (function () 
{
    const selectors = {
        'datatable'  : '#dataset-table',
        'leaveModal' : '#leaveRequestModal'
    };

    const routes = {
        'datasource'    : $(selectors.datatable).data('src'),
        'requestLeave'  : $(selectors.leaveModal).find('form').data('post-target'),
        'cancelLeave'   : $(selectors.datatable).data('on-cancel')
    };

    let dataTable;
    let tablePageLen;
    let leaveReqModal;
    let leaveReqInputs;

    let columnDefs = [
        // First Column -> Record Counter
        {
            className: 'record-counter text-truncate all',
            data: null,
            render: function(data, type, row, meta) {
                return meta.row + 1;
            }
        },
        { data: 'date_from',      className: 'td-date-from  text-truncate all',               },
        { data: 'date_to',        className: 'td-date-to    text-truncate desktop tablet',    },
        { data: 'duration',       className: 'td-duration   text-truncate desktop tablet',    },
        { data: 'type',           className: 'td-type       text-truncate desktop',           },
        { data: 'status',         className: 'td-status     text-truncate desktop tablet-l',  },
        { data: 'request_date',   className: 'td-reqdate    text-truncate desktop',           },
        { 
            data: null,
            className: 'all td-action text-center', 
            defaultContent: '',
            name: 'action',
            render: function (data, type, row) {  
                if (
                    ('isPending' in data && data.isPending == 1) &&
                    ('id' in data)
                )
                {
                    let content = _makeActionButtonCancel(1, data.id);
                   return content;
                }

                return null;
            }
        }
    ];

    function __bindTableDataSource()
    {
        let options = {
            'deferRender'   : true,
            'searching'     : false,
            'ordering'      : false,
            'autoWidth'     : false,
            'responsive'    : true,
            'drawCallback'  : function (settings) 
            {
                var api = this.api();

                if (api.rows().count() === 0)
                    return;

                updateRowEntryNumbers(api);

                // Highlight the newly added / updated row

                if ('newRowInstance' in dataTable && dataTable.newRowInstance !== null)
                {
                    console.warn('row instance persists');
                    if (!dataTable.newRowInstance.node())
                        return;

                    let rowNode = dataTable.newRowInstance.node();

                    scrollRowToView(rowNode, {
                        'afterScroll': function () 
                        {
                            setTimeout(function () 
                            {
                                flashRow(rowNode, () => dataTable.newRowInstance = null);
                            }, 800);
                        }
                    });
                }
            },
            ajax: {
                url      : routes.datasource,
                type     : 'POST',
                dataType : 'JSON',
                dataSrc  : function (json) 
                {
                    if (!json)
                    {
                        snackbar.showInfo('No records to show');
                        return;
                    }

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
                    //enableControlButtons();

                    return json.data;
                },
                data : function () 
                {
                    return {
                        '_token': getCsrfToken(),
                    }
                }
            },
            columns: columnDefs
        };
        
        // If an instance of datatable has already been created,
        // reload its data source with given url instead
        if (dataTable != null)
        {
            dataTable.ajax.reload();
            redrawPageLenControls(dataTable);
            return;
        }

        // Initialize datatable if not yet created
        dataTable = $(selectors.datatable).DataTable(options);
        redrawPageLenControls(dataTable);
    }

    function redrawPageLenControls(datatable)
    {
        if (tablePageLen)
            tablePageLen = null;

        tablePageLen = to_lengthpager('#table-page-len', datatable);
    }

    function __buildLeaveReqModal()
    {
        leaveReqModal = {
            __instance      : null,
            __dirty         : false,
            getSelector     : function() { return selectors.leaveModal },
            isDirty         : function() { return this.__dirty  },
            setClean        : function() { this.__dirty = false },
            setDirty        : function() { this.__dirty = true  },
            getCancelButton : function() { return $(this.getSelector()).find('.btn-cancel') },
            getSaveButton   : function() { return $(this.getSelector()).find('.btn-submit') },
            initialize      : function() 
            {
                if (this.__instance === null)
                    this.__instance = new mdb.Modal( $(this.getSelector()) );
            },
            getInstance     : function() { return this.__instance },
            show   : function() { this.getInstance().show(); },
            hide   : function() { this.getInstance().hide(); },
            finish : function() 
            {
                Object.keys(leaveReqInputs).forEach(field => leaveReqInputs[field].input.reset());
            
                this.setClean();
                this.hide();
            }
        };
        
        leaveReqModal.initialize();
    }

    let initialize = function() {
        __bindTableDataSource();
        __buildLeaveReqModal();

        leaveReqInputs = {
            'input-leave-start' : { label: 'Start Date', input: to_datepicker('#input-leave-start', false, true) },
            'input-leave-end'   : { label: 'End Date',   input: to_datepicker('#input-leave-end',   false, true) },
            'input-leave-type'  : { label: 'Leave Type', input: to_droplist('#input-leave-type') }
        };
    };

    let bindEvents = function() {
        
        // Handle form submission when save button was clicked
        leaveReqModal.getSaveButton().on('click', () => 
        {
            let errors = 0;

            Object.keys(leaveReqInputs).forEach(k =>
            {
                let field = leaveReqInputs[k];

                if (field.input.getValue())
                    return true;

                let msg = `${field.label} must be filled out`;
                field.input.showError(msg);

                errors++;
            });

            if (errors > 0)
                return;

            __submitForm(leaveReqInputs);
        });

        leaveReqModal.getCancelButton().on('click', () => 
        {
            leaveReqModal.hide();

            if (leaveReqModal.isDirty())
            {
                let message = "Do you wish to cancel the request?";

                alertModal.showWarn(message, 'Warning',

                    () => leaveReqModal.finish(),   // OK was clicked; clean-up the form inputs...   
                    () => leaveReqModal.show()      // CANCEL was clicked; bring back the modal...
                );

                return;
            }
        });
        
        Object.keys(leaveReqInputs).forEach(k => {

            leaveReqInputs[k].input.getInput().on('input', () => leaveReqModal.setDirty());
        });

        $(document).on('click', '.row-action-button.cancel-leave', function() 
        {
            let rowKey = $(this).data('action-key');
            let selectedRow = $(this).closest('tr');
            
            let dateFrom  = selectedRow.find('.td-date-from').text(); 
            let dateTo    = selectedRow.find('.td-date-to').text();
            let duration  = selectedRow.find('.td-duration').text();
            let leaveType = selectedRow.find('.td-type').text();
            let leaveStat = selectedRow.find('.td-status').text();
            let requestOn = selectedRow.find('.td-reqdate').text();

            let message = 
            `<div class="mb-2">Are you sure you want to cancel this leave request, with the following details?</div>
            <div class="note note-primary mb-3">
                <table class="w-100 table-sm">
                    <tbody>
                        <tr>
                            <td><i class="fa-solid fa-caret-right text-primary-dark me-2"></i>Date from:</td>
                            <td class="text-primary-dark">${dateFrom}</td>
                        </tr>
                        <tr>
                            <td><i class="fa-solid fa-caret-right text-primary-dark me-2"></i>Date to:</td>
                            <td class="text-primary-dark">${dateTo}</td>
                        </tr>
                        <tr>
                            <td><i class="fa-solid fa-caret-right text-primary-dark me-2"></i>Duration:</td>
                            <td class="text-primary-dark">${duration}</td>
                        </tr>
                        <tr>
                            <td><i class="fa-solid fa-caret-right text-primary-dark me-2"></i>Leave type:</td>
                            <td class="text-primary-dark text-truncate">${leaveType}</td>
                        </tr>
                        <tr>
                            <td><i class="fa-solid fa-caret-right text-primary-dark me-2"></i>Approval status:</td>
                            <td class="text-primary-dark fw-bold">${leaveStat}</td>
                        </tr>
                        <tr>
                            <td><i class="fa-solid fa-caret-right text-primary-dark me-2"></i>Requested on:</td>
                            <td class="text-primary-dark">${requestOn}</td>
                        </tr>
                    </tbody>
                </table>
            </div>`;
            alertModal.showWarn(message, 'Cancel Leave Request', 
            () => {
                _cancelLeaveRequest(rowKey, selectedRow);
            });
        });
    };

    function __submitForm(formData)
    {
        let postData = { '_token': getCsrfToken() };

        Object.keys(formData).forEach(key => postData[key] = formData[key].input.getValue());

        $.ajax({
            url     : routes.requestLeave,
            type    : 'POST',
            data    : postData,
            success : function (response)
            {
                if (!response)
                {
                    leaveReqModal.finish();
                    _onServerNoResponse();
                    return;
                }

                response = JSON.parse(response);
                
                if (response.code != 0) 
                {
                    leaveReqModal.finish();
                    alertModal.showDanger(response.message);
                    return;
                }

                let rowData = {
                    'date_from'     : response.rowData['date_from'],
                    'date_to'       : response.rowData['date_to'],
                    'duration'      : response.rowData['duration'],
                    'type'          : response.rowData['type'],
                    'status'        : response.rowData['status'],
                    'request_date'  : response.rowData['request_date'],
                };

                if ('isPending' in response.rowData)
                    rowData['isPending'] = response.rowData['isPending'];
                
                if ('id' in response.rowData)
                    rowData['id'] = response.rowData['id'];

                let newRow = dataTable.row.add(rowData);

                // store the new row instance
                dataTable.newRowInstance = newRow;  

                leaveReqModal.finish();
                
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
                        let target  = leaveReqInputs[field].input;

                        target.showError(message);
                    }
                    return;
                }

                // General Error
                leaveReqModal.finish();
                alertModal.showDanger('A problem is preventing your request from being processed. Please try again later.');
            }
        });
    }

    function _onServerNoResponse()
    {
        alertModal.showWarn(GenericMessages.XHR_SERVER_NO_REPLY);
        leaveReqModal.finish();
    }

    function _makeActionButtonCancel(isPendingStat = 0, rowHash = '')
    {
        if (isPendingStat == 0)
            return;

        return `<button type="button" data-action-key="${rowHash}" 
                class="btn flat-button btn-danger row-action-button cancel-leave">
                    <i class="fas fa-times"></i>
                </button>`;
    }

    function _cancelLeaveRequest(key, rowReference)
    {
        $.ajax({
            'url' : routes.cancelLeave,
            'type': 'POST',
            'data': {
                '_token' : getCsrfToken(),
                'rowKey' : key
            },
            success: function(response) 
            {
                if (!response)
                {
                    _onServerNoResponse();
                    return;
                }

                response = JSON.parse(response);
                
                if (response.code != 0) 
                {
                    alertModal.showDanger(response.message);
                    return;
                }

                if (rowReference)
                {
                    dataTable.row(rowReference).remove().draw();
                }

                snackbar.showSuccess('Leave request successfully cancelled.');
            },
            error: (xhr, error, status) => {
                alertModal.showDanger(GenericMessages.XHR_FAIL_ERROR);
            }
        })
    }

    return {
        'init' : initialize,
        'bind' : bindEvents
    };

})();

$(document).ready(function () 
{
    employeeLeave.init();
    employeeLeave.bind();    
});