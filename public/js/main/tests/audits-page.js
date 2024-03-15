var auditsPage = (function ()
{
    let datasetTable;
    
    //============================
    // Initialization
    //============================

    var initialize = function ()
    {
        datasetTable = {
            // Adapter is an instance of JqueryDatatables                
            __adapter     : null,
            getSelector   : () => "#records-table",
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
                        render: function() 
                        {
                            let classList = `btn btn-sm btn-view px-2 btn-link text-primary-dark text-capitalize rounded-3`;

                            return `<button type="button" class="${classList}">View</button>`;
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
                console.warn(dt)
            },
        };

        datasetTable.bindAdapter();
    };

    //============================
    // Event Handling
    //============================

    var handleEvents = function ()
    {

    };

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
