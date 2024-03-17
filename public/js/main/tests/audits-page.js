let auditUpdateDetailsModal;
let auditDeleteDetailsModal;
let auditCreateDetailsModal;

var auditsPage = (function ()
{
    let eventBus;

    const EV_VIEW_DETAIL_AUDIT_CREATED = 'onViewAuditCreatedEvent';
    const EV_VIEW_DETAIL_AUDIT_UPDATED = 'onViewAuditUpdatedEvent';
    const EV_VIEW_DETAIL_AUDIT_DELETED = 'onViewAuditDeletedEvent';

    let datasetTable;
    const DATA_TABLE_SELECTOR = '#records-table';
    
    //============================
    // Initialization
    //============================

    var initialize = function ()
    {
        eventBus = addModuleEventBus();
        auditUpdateDetailsModal = to_auditTrailsDetailsUpdate('#audit-details-update');
        auditDeleteDetailsModal = to_auditTrailsDetailsDelete('#audit-details-delete');
        auditCreateDetailsModal = to_auditTrailsDetailsCreate('#audit-details-create');

        datasetTable = {
            // Adapter is an instance of JqueryDatatables                
            __adapter     : null,
            getSelector   : () => DATA_TABLE_SELECTOR,
            getDataSource : function(source) 
            {
                let el = $(this.getSelector());

                var sources = {
                    'main' : el.data('src-datasource')
                };

                if (source in sources)
                    return sources[source];

                return '';
            },
            defineColumns : function() 
            {
                return [
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
                    {
                        className: 'td-date text-truncate',
                        width: '120px',
                        data: 'date',
                    },
                    {
                        className: 'td-time text-truncate',
                        width: '120px',
                        data: 'time',
                    },
                    {
                        className: 'td-employee-name text-truncate',
                        width: '180px',
                        data: 'adminname',
                        name: 'adminname',
                        defaultContent: ''
                    },
                    {
                        className: 'td-action text-truncate',
                        width: '120px',
                        data: 'action',
                        render: function(data, type, row) {
                            
                            let html =
                            `<div class="action-badge ${row.action_icon} px-2 py-1 w-100">
                                <i class="fas me-1 fasicon"></i>
                                <span class="label text-capitalize text-sm">${data}</span>
                            </div>`;

                            return html;
                        }
                    },
                    {
                        className: 'td-affected text-truncate td-120',
                        width: '150px',
                        data: 'affected',
                    },
                    {
                        className: 'td-desc text-truncate text-14',
                        width: '250px',
                        data: 'description',
                    },
                    {
                        className: 'td-view-detail text-center px-1 position-sticky end-0 z-100 sticky-cell',
                        width: '80px',
                        data: null,
                        render: function(data, type, row) 
                        {
                            let html = 
                            `<div class="row-actions unstyled-buttons" data-record-key="${data.id}">
                                <div class="loader d-none"></div>
                                <button class="btn btn-sm btn-view px-2 btn-link text-primary-dark text-capitalize rounded-3"
                                    type="button">View
                                </button>
                            </div>`;

                            return html;
                        }
                    }
                ];
            },
            getAdapter  : ()  => this.__adapter,
            setAdapter  : (a) => this.__adapter = a,
            bindAdapter : function()
            {
                let el = $(this.getSelector());

                let options = {
                    'deferRender'   : true,
                    'searching'     : false,
                    'ordering'      : false,
                    'autoWidth'     : true,
                    'scrollX'       : true,
                    'sScrollXInner' : "80%",
                    'columns'       : this.defineColumns(),
                    ajax: {
                        url         : this.getDataSource('main'),
                        type        : 'POST',
                        dataType    : 'JSON',
                        dataSrc     : function (json) 
                        {
                            if (!json)
                                return null;

                            if ('data' in json && json.data.length == 0)
                            {
                                snackbar.showInfo('No records to show');
                            }

                            // Display Messages By Error Codes
                            if ('code' in json) 
                            {
                                if (json.code == -1) 
                                {
                                    //alertModal.showDanger(json.message);
                                    return [];
                                }
                            }
                            // After AJAX response, reenable the control buttons
                            //enableControlButtons();
        
                            return json.data;
                        },
                        data: function () 
                        {
                            return {
                                '_token' : getCsrfToken(),
                            }
                        }
                    }
                };

                if (this.getAdapter() != null)
                {
                    this.getAdapter().ajax.reload();
                    return;
                }

                var dt = el.DataTable(options);
                //console.warn(dt)
            },
        };

        datasetTable.bindAdapter();
    };

    //============================
    // Event Handling
    //============================

    var handleEvents = function ()
    {
        $(document).on('click', `${DATA_TABLE_SELECTOR} .btn-view`, function () 
        {
            let row = $(this).closest('tr');

            if (!row)
            {
                alertModal.showWarn(GenericMessages.ROW_ACTION_FAIL);
                return;
            }

            viewAuditDetails(row);
        });

        eventBus.subscribe(EV_VIEW_DETAIL_AUDIT_CREATED, (dataset) => {

            auditCreateDetailsModal.presentData(dataset);
            auditCreateDetailsModal.show();
        });

        eventBus.subscribe(EV_VIEW_DETAIL_AUDIT_UPDATED, (dataset) => {
            
            auditUpdateDetailsModal.presentData(dataset);
            auditUpdateDetailsModal.show();
        });

        eventBus.subscribe(EV_VIEW_DETAIL_AUDIT_DELETED, (dataset) => {

            auditDeleteDetailsModal.presentData(dataset);
            auditDeleteDetailsModal.show();
        });

    };

    let viewAuditDetails = function(row)
    {
        let key = $(row).find('.row-actions').data('record-key');

        $.ajax({
            url     : $(DATA_TABLE_SELECTOR).data('src-view-audit'),
            type    : 'POST',
            data    : {
                '_token': getCsrfToken(),
                'rowKey': key,
            },
            success: function(response) 
            {
                if (!response)
                {
                    alertModal.showDanger(GenericMessages.XHR_SERVER_NO_REPLY);
                    return;
                }

                response = JSON.parse(response);

                if (response.code != 0)
                {
                    alertModal.showDanger(response.message);
                    return;
                }

                switch(response.dataset.action)
                {
                    case 'created':
                        eventBus.publish(EV_VIEW_DETAIL_AUDIT_CREATED, response.dataset);
                        break;

                    case 'updated':
                        eventBus.publish(EV_VIEW_DETAIL_AUDIT_UPDATED, response.dataset);
                        break;

                    case 'deleted':
                        eventBus.publish(EV_VIEW_DETAIL_AUDIT_DELETED, response.dataset);
                        break;
                }
            },
            error: function (xhr, status, error) {
                console.warn(xhr.responseText);
            }
        });

    };
    // function executeRowActions(row, action)
    // {
    //     eventBus.publish(BC_ROW_ACTION_STARTED, row);

    //     const actions = {
    //         'info'    : getRecordInfo,
    //         'edit'    : editRecord,
    //         'delete'  : deleteRecord
    //     };

    //     if (action in actions)
    //         actions[action](row);
    // }

    //============================
    // Business Logic
    //============================

    return {
        init    : initialize,
        handle  : handleEvents,
    };

})();

$(document).ready(function ()
{
    auditsPage.init();
    auditsPage.handle();
});
