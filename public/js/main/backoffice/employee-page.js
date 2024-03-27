var globalEventBus;

const GEV_LOAD_EDIT = 'onLoadEmployeeDetails';

var employeeDatatables = (function() 
{
    const DATA_TABLE_SELECTOR   = '#records-table';

    // Broadcast Recievers
    const BC_ROW_ACTION_STARTED = 'rowActionStarted';
    const BC_ROW_ACTION_FAILED  = 'rowActionFailed';
    const BC_ROW_ACTION_ENDED   = 'rowActionEnded';

    let dataTable;
    let datasource;

    let empDetailsDialog;
    let eventBus;
    let tablePageLen;

    let emphasizeCounts =  function(data, type, row) 
    {
        data = data || 0;
        
        let style = `opacity-40`;

        if (data && data > 0)
            style = '';

        return `<span class="${style}">${data}</span>`;
    };

    let columnDefs = [
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
        // Second Column -> Employee Number
        {
            width: '120px',
            className: 'text-truncate',
            data: 'emp_num',
            name: 'emp_num',
            defaultContent: ''
        },
        // Third Column -> Name
        {
            className: 'td-employee-name text-truncate',
            width: '250px',
            data: 'empname',
            name: 'empname',
            defaultContent: ''
        },
        // Fourth Column  -> Status
        {
            className: 'text-center',
            width: '120px',
            data: 'emp_status', 
            defaultContent: '',
            render: function(data, type, row) 
            {
                let style = row.status_style || '';
                return `<span class="status-badge text-sm ${style}">${data}</span>`;
            }
        },
        // Fifth Column -> Late
        { 
            className: 'v-stripe-accent-yellow border-start border-end', 
            width: '100px', data: 'total_lates', defaultContent: '0', render: emphasizeCounts
        },

        // Sixth Column -> Leave
        { width: '100px', data: 'total_leave', defaultContent: '0', render: emphasizeCounts},

        // Seventh Column -> Absents
        { 
            className: 'v-stripe-accent-red border-start border-end', 
            width: '100px', data: 'total_absents', defaultContent: '0', render: emphasizeCounts
        },

        // Eighth Column -> Actions
        {
            data: null,
            className: 'td-actions text-center position-sticky end-0 z-100 sticky-cell',
            width: '100px',
            render: function(data, type, row) 
            {    
                return createRowActions(data.id);
            }
        }
    ];

    // The Main entry point of the datatable in this context
    function __initialize() 
    {
        eventBus   = addModuleEventBus();
        datasource = $(DATA_TABLE_SELECTOR).data('src-default');

        empDetailsDialog = to_employeeDetailsDialog('#employeeDetailsModal');

        __bindEvents();
        __bindTableDataSource();
    }

    function __bindEvents() 
    {
        // When a row action button was clicked... 
        // Map button classes to their actions. 
        // We then find the class that the clicked button contains and 
        // use it to get the corresponding action from the actions object
        $(document).on('click', `${DATA_TABLE_SELECTOR} .btn`, function () 
        {
            var actions = {
                'btn-edit'    : 'edit',
                'btn-delete'  : 'delete',
                'btn-details' : 'info'
            };

            var action = Object.keys(actions).find( k => this.classList.contains(k));

            if (action)
            {
                let row = $(this).closest('tr');
                
                if (!row)
                {
                    alertModal.showWarn(GenericMessages.ROW_ACTION_FAIL);
                    return;
                }

                executeRowActions( row, actions[action] );
            }
        });

        // Listen to events
        eventBus.subscribe(BC_ROW_ACTION_STARTED , (row) => showRowActionLoader(row, true));
        eventBus.subscribe(BC_ROW_ACTION_ENDED   , (row) => showRowActionLoader(row, false));
        eventBus.subscribe(BC_ROW_ACTION_FAILED  , (row) => showRowActionLoader(row, false));

        empDetailsDialog.loadEnd = (response) => {

            if (response.row)
                eventBus.publish(BC_ROW_ACTION_ENDED, response.row);
        };

        empDetailsDialog.loadFailed = (response) => {
            alertModal.showDanger(response.message);

            if (response.row)
                eventBus.publish(BC_ROW_ACTION_FAILED, response.row);
        };

        empDetailsDialog.emailSendFail  = (response) => alertModal.showDanger(response);
        empDetailsDialog.emailSendOK    = (response) => snackbar.showSuccess(response);
    }

    function showRowActionLoader(row, show)
    {
        if (typeof show !== 'boolean')
            return;

        let parent  = row.find('.td-actions .row-actions');
            
        if (show === true)
        {
            parent.find('.btn').addClass('d-none');
            parent.find('.loader').removeClass('d-none');

            return;
        }

        parent.find('.btn').removeClass('d-none');
        parent.find('.loader').addClass('d-none');
    }

    function __bindTableDataSource()
    {
        let options = {
            'deferRender'   : true,
            'searching'     : false,
            'ordering'      : false,
            'autoWidth'     : true,
            'scrollX'       : true,
            'sScrollXInner' : "80%",
            'drawCallback'  : function (settings) 
            {
                var api = this.api();

                if (api.rows().count() === 0)
                    return;

                updateRowEntryNumbers(api);

                // Highlight the newly added / updated row

                if ('newRowInstance' in dataTable && dataTable.newRowInstance !== null)
                {
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
                url      : datasource,
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
        dataTable = $(DATA_TABLE_SELECTOR).DataTable(options);
        redrawPageLenControls(dataTable);
    }

    function redrawPageLenControls(datatable)
    {
        if (tablePageLen)
            tablePageLen = null;

        tablePageLen = to_lengthpager('#table-page-len', datatable);
    }
    
    //     // If an instance of datatable has already been created,
    //     // reload its data source with given url instead
    //     if (dataTable != null)
    //     {
    //         dataTable.ajax.url(url).load();
    //         return;
    //     }
        
    //     // Initialize datatable if not yet created
    //     dataTable = $(DATA_TABLE_SELECTOR).DataTable(options);
    // }

    function __insertRow(data)
    {
        let newRow = dataTable.row.add({
            emp_num       : data.emp_num,
            empname       : data.empname,
            emp_status    : data.emp_status,
            total_lates   : data.total_lates,
            total_leave   : data.total_leave,
            total_absents : data.total_absents,
            id            : data.id
        });
        
        // store the new row instance
        dataTable.newRowInstance = newRow;

        goToNewRowPage(newRow);
    }

    function __updateRow(response)
    {
        if (
            response && 
            (response.data && Object.keys(response.data).length > 0) && 
            (response.row  && Object.keys(response.row).length > 0)
        )
        {
            let temp_row = dataTable.row(response.data.row);
            let temp_rowData = temp_row.data();

            temp_rowData.empname = response.data['empname'];
            temp_rowData.emp_num = response.data['emp_num'];

            let newRow = dataTable.row(temp_row).data(temp_rowData).invalidate().draw(false);
            
            // store the new row instance
            dataTable.newRowInstance = newRow;

            goToNewRowPage(newRow);
        }
        else
        {
            alertModal.showWarn(GenericMessages.ROW_REDRAW_FAIL);
        }
    }

    function goToNewRowPage(newRow)
    {
        var rowIndex   = newRow.index();
        var pageNumber = Math.ceil((rowIndex + 1) / dataTable.page.len());

        // Go to the page
        dataTable.page(pageNumber - 1).draw(false);
    }

    function executeRowActions(row, action)
    {
        eventBus.publish(BC_ROW_ACTION_STARTED, row);

        const actions = {
            'info'    : getRecordInfo,
            'edit'    : editRecord,
            'delete'  : deleteRecord
        };

        if (action in actions)
            actions[action](row);
    }

    let deleteRecord = function(row) 
    {
        let onEnded = () => eventBus.publish(BC_ROW_ACTION_ENDED, row);
        let onFail  = () => eventBus.publish(BC_ROW_ACTION_FAILED, row);

        let key     = row.find('.row-actions').attr('data-record-key');
        let empName = row.find('.td-employee-name').text();
        let message = sanitize(`You are about to remove <i><b>"${empName}"</b></i> from the employee records. This action will also erase all data associated with this employee such as attendances.<br><br>Are you sure you want to proceed?`);

        let _delete = function() 
        {
            $.ajax({
                url  : $(DATA_TABLE_SELECTOR).data('action-delete'),
                type : 'POST',
                data : {
                    '_token': getCsrfToken(),
                    'rowKey': key
                },
                success: function (response) 
                {
                    if (!response)
                    {
                        alertModal.showDanger(GenericMessages.XHR_SERVER_NO_REPLY);
                        onFail();
                        return;
                    }

                    response = JSON.parse(response);

                    if (response.code != 0)
                    {
                        alertModal.showDanger(response.message);
                        onFail();
                        return;
                    }

                    snackbar.showSuccess(response.message);
                    dataTable.row(row).remove().draw();
                },
                error: function (xhr, status, error) 
                {
                    alertModal.showDanger(GenericMessages.XHR_FAIL_ON_DELETE);
                    onFail();
                },
                complete: () => onEnded()
            });
        };

        alertModal.showWarn(
            message, 
            'Delete', 
            () => _delete(), 
            () => onEnded()
        );
    };

    let getRecordInfo = function(row)
    {
        let key = $(row).find('.row-actions').data('record-key');

        empDetailsDialog.show({
            employeeKey: key,
            row: row
        });
    };

    let editRecord = function(row)
    {
        $.ajax({
            url     : $(DATA_TABLE_SELECTOR).data('action-edit'),
            type    : 'POST',
            data    : {
                '_token': getCsrfToken(),
                'key'   : $(row).find('.row-actions').data('record-key')
            },
            success: function(response) {

                if (!response)
                {
                    alertModal.showDanger(GenericMessages.XHR_SERVER_NO_REPLY);
                    return;
                }

                response = JSON.parse(response);

                if (response.code == -1)
                {
                    alertModal.showDanger(response.message);
                    return;
                }

                globalEventBus.publish(GEV_LOAD_EDIT, {
                    'data' : response,
                    'row'  : row
                });
            },
            error: function(xhr, status, error) 
            {
                alertModal.showError(GenericMessages.XHR_FAIL_ERROR);
                eventBus.publish(BC_ROW_ACTION_FAILED, row);
            },
            complete: () => eventBus.publish(BC_ROW_ACTION_ENDED, row)
        });
    };

    return {
        'bindDataSource' : __bindTableDataSource,
        'beginDataTable' : __initialize,
        'insertRow'      : __insertRow,
        'updateRow'      : __updateRow
    };

})();

var employeePage = (function()
{
    let crudModal;
    let eventBus;

    const EV_CREATED_EMP = 'onCreateEmployee';
    const EV_UPDATED_EMP = 'onUpdateEmployee';

    var initialize = function()
    {
        eventBus = addModuleEventBus();
        employeeDatatables.beginDataTable();
        __initModal();
    }

    var handleEvents = function()
    {
        $('.btn-add-employee').on('click', () => crudModal.open( crudModal.MODE_CREATE ));

        eventBus.subscribe(EV_CREATED_EMP, (response) => {

            if ('download' in response)
            {
                // This allows the browser to start the download by navigating to the url
                // var base64String = response.download.split('base64,')[1];
                // var byteCharacters = atob(base64String);
                // var byteNumbers = new Array(byteCharacters.length);
                // for (var i = 0; i < byteCharacters.length; i++)
                // {
                //     byteNumbers[i] = byteCharacters.charCodeAt(i);
                // }
                // var byteArray = new Uint8Array(byteNumbers);
                // var blob = new Blob([byteArray], { type: 'application/octet-stream' });
                // var blobUrl = URL.createObjectURL(blob);
                // window.location = blobUrl;

                // This version of download allows filenames
                // Parse blob from JSON response
                var base64String = response.download.split('base64,')[1];
                var byteCharacters = atob(base64String);
                var byteNumbers = new Array(byteCharacters.length);
                for (var i = 0; i < byteCharacters.length; i++)
                {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                var byteArray = new Uint8Array(byteNumbers);
                var blob = new Blob([byteArray], { type: 'application/octet-stream' });

                // Create a hidden <a> element
                var a = document.createElement('a');
                a.style.display = 'none';
                document.body.appendChild(a);

                // Set its href attribute to the blob's URL
                a.href = window.URL.createObjectURL(blob);

                // Set its download attribute to the filename from the response
                a.download = response.filename;

                // Programmatically click the <a> tag to start the download
                a.click();

                // Cleanup
                window.URL.revokeObjectURL(a.href);
                document.body.removeChild(a);
                // downloadQRCode(response);
            }

            employeeDatatables.insertRow(response);
            snackbar.showSuccess(response.message);
        });

        eventBus.subscribe(EV_UPDATED_EMP, (response) => {

            employeeDatatables.updateRow(response);
            snackbar.showSuccess(response.data.message);
        });

        globalEventBus.subscribe(GEV_LOAD_EDIT, (response) => {
            crudModal.fill(
                response.data.dataset,
                response.row
            );
        });
    }

    function __initModal() 
    {
        crudModal = (function() {

            const selector = '#createEmployeeModal';
            const instance = new mdb.Modal(selector);
            
            let actionMode      = null;
            let empUpdateKey    = $(selector).find('#record-key');
            let rowRef_forEdit  = null;

            let modal = {
                'MODE_CREATE' : 1,
                'MODE_UPDATE' : 2,
                'MODE_NONE'   : 0,
                'isDirty'     : false,
                'cancelled'   : null,

                'close' : () => instance.hide(),

                'open'  : (mode) => {
                    actionMode = mode;
                    instance.show();
                },
                'fill'  : function(data, rowReference)
                {
                    if (rowReference)
                        rowRef_forEdit = rowReference;

                    // Fill the modal with the loaded details
                    inputs['input-fname'].setValue(data.fname);
                    inputs['input-mname'].setValue(data.mname);
                    inputs['input-lname'].setValue(data.lname);
                    inputs['input-phone'].setValue(data.phone);
                    inputs['input-id-no'].setValue(data.idNo);
                    inputs['input-email'].setValue(data.email);
                    inputs['input-position'].setValue(data.rank);

                    empUpdateKey.val(data.id);

                    this.open(this.MODE_UPDATE);
                },
                'resetRowRef' : () => {
                    rowRef_forEdit = null;
                    console.warn(rowRef_forEdit)
                }
                    
            };

            let modalTitle  = $(selector).find('.modal-title');
            let modalTitles = {
                [modal.MODE_CREATE]  : modalTitle.data('title-create'),
                [modal.MODE_UPDATE]  : modalTitle.data('title-update'),
                [modal.MODE_NONE]    : 'Employee Form'
            };

            let upsertForm   = $(selector).find('#upsert-form');
            let upsertRoutes = {
                [modal.MODE_CREATE]  : upsertForm.data('action-create'),
                [modal.MODE_UPDATE]  : upsertForm.data('action-update')
            };

            let crudProgress     = to_indefmeter('#emp-crud-progress', 0);
            let progressCaptions = {
                [modal.MODE_CREATE] : "Setting up employee profile... Please stand by.",
                [modal.MODE_UPDATE] : "Applying changes... Please wait."
            };

            let inputs = {
                'input-fname'      : to_textfield( '#input-fname' ),
                'input-mname'      : to_textfield( '#input-mname' ),
                'input-lname'      : to_textfield( '#input-lname' ),
                'input-phone'      : to_textfield( '#input-phone' , TextFieldTypes.TYPE_NUMERIC),
                'input-id-no'      : to_textfield( '#input-id-no' , TextFieldTypes.TYPE_NUMDASH),
                'input-email'      : to_textfield( '#input-email' , TextFieldTypes.TYPE_EMAIL),
                'input-position'   : to_droplist( '#input-position' ),
                'option-save-qr'   : to_checkbox( '#option-save-qr' )
            };

            const optionalInputs = [ 'input-phone', 'option-save-qr' ];
            
            let validate = function() 
            {
                let errors = 0;

                Object.keys(inputs).forEach( i => {

                    if (optionalInputs.includes(i))
                        return true;

                    if ( !(inputs[i].getValue()) )
                    {
                        modal.isDirty = true;
                        errors++;

                        if (inputs[i].getType() == 'droplist')
                            inputs[i].showError('Please select an option.');
                    
                        else if (inputs[i].getType() == 'textfield')
                            inputs[i].showError('This fill out this field.');
                    }
                });

                return (errors == 0);
            }

            let enableControlButtons = (enable) => {
                $(selector).find('.modal-control-button').prop('disabled', !enable);
            };

            function submitForm()
            {
                enableControlButtons(false);
                crudProgress.setProgress(100, progressCaptions[actionMode]);
                
                const data = {
                    '_token' : getCsrfToken(),
                    'action' : actionMode,
                    'role'   : upsertForm.find('#input-role').val()
                };

                for (let input in inputs) {
                    data[input] = inputs[input].getValue()
                }

                if (actionMode == modal.MODE_UPDATE)
                    data['update-key'] = empUpdateKey.val();
                
                $.ajax({
                    url     : upsertRoutes[actionMode],
                    type    : 'POST',
                    data    : data,
                    success : function(response) {

                        let updatedRow = (actionMode == modal.MODE_UPDATE)
                                       ? rowRef_forEdit
                                       : null;

                        terminateModal();

                        if (!response)
                        {
                            alertModal.showDanger(GenericMessages.XHR_SERVER_NO_REPLY);
                            return;
                        }

                        response = JSON.parse(response);

                        if (response.code == -1 || response.code != 0)
                        {
                            alertModal.showDanger(response.message);
                            return;
                        }

                        switch (actionMode)
                        {
                            case modal.MODE_CREATE:
                                eventBus.publish(EV_CREATED_EMP, response);
                                break;

                            case modal.MODE_UPDATE:
                                eventBus.publish(EV_UPDATED_EMP, {
                                    'data' : response,
                                    'row'  : updatedRow
                                });
                                break;
                        }
                    },
                    error   : function(xhr, status, error) 
                    {
                        //
                        // Validation Error
                        //
                        if (xhr.status === 422)
                        {
                            let errorFields = xhr.responseJSON.errors;

                            Object.keys(errorFields).forEach( e => {
                                let message = errorFields[e];
                                inputs[e].showError(message);
                            });

                            return;
                        }
                        //
                        // Handle General Error
                        //
                        terminateModal();
                        alertModal.showDanger(GenericMessages.XHR_FAIL_ERROR);
                    },
                    complete: function() 
                    {
                        crudProgress.reset();
                        enableControlButtons(true);
                    }
                })
            }

            function __clearInputs() 
            {
                upsertForm.trigger('reset');
                Object.values(inputs).forEach( i => i.reset());
                
                modal.isDirty = false;
                modal.resetRowRef();
            }

            // Force closes the modal without warning on Dirty state
            function terminateModal()
            {
                __clearInputs();
                modal.close();
            }

            // When atleast one of them inputs are interacted, they are flagged DIRTY
            Object.values(inputs).forEach( i => {
                
                i.changed = () => modal.isDirty = true;
            });

            $(selector)
            .on('shown.mdb.modal',  () => {

                modalTitle.text( modalTitles[actionMode] );

                if (actionMode == modal.MODE_UPDATE)
                    inputs['option-save-qr'].disable();
                else
                    inputs['option-save-qr'].enable();
            })
            .on('hidden.mdb.modal', () => {

                if (modal.isDirty)
                {
                    alertModal.showWarn
                    (
                        GenericMessages.ALERT_ABORT_CHANGES,    // Message
                        'Unsaved Changes',                      // Title
                        () => __clearInputs(),                  // OK CLICKED
                        () => instance.show()                   // CANCELLED, show the form again
                    );
                }
            });

            $(selector).find('.btn-cancel').on('click', () => instance.hide());
            $(selector).find('.btn-submit').on('click', () => {

                if (!validate())
                    return;

                submitForm();
            });

            return modal;
        })();
    }

    function downloadQRCode(response)
    {
        var dl = response.download;

        if (dl.url == '404')
        {
            alertModal.showDanger('Could not download the generated QR Code.');
            return;
        }
        
        let link        = document.createElement('a');
        link.href       = dl.url;
        link.download   = dl.fileName;

        document.body.appendChild(link);

        // Programmatically click the 'a' element to start the download
        link.click();

        document.body.removeChild(link);
    }

    return {
        init   : initialize,
        handle : handleEvents
    }
})();

$(document).ready(function ()
{
    globalEventBus = addModuleEventBus();

    employeePage.init();
    employeePage.handle();
});
