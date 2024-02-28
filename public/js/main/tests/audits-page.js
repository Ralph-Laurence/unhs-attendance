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
                        width: '240px',
                        data: 'adminname',
                        name: 'adminname',
                        defaultContent: ''
                    },
                    {
                        className: 'td-action text-truncate',
                        width: '120px',
                        data: 'action',
                    },
                    {
                        className: 'td-target text-truncate',
                        width: '150px',
                        data: 'target',
                    },
                    {
                        className: 'td-desc text-truncate',
                        width: '200px',
                        data: null //'description',
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
        
                            // if (iconStyles == undefined)
                            //     iconStyles = json.icon;
        
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
