let attendanceModule = (function ()
{

    const selectors = {
        'datatable' : '#dataset-table'
    };

    const routes = {
        'datasource'    : $(selectors.datatable).data('src')
    };

    let dataTable;
    let tablePageLen;
    let monthFilter;
    let selectedMonth;
    let remarkStyles;

    let columnDefs = [
        // First Column -> Record Counter
        {
            className: 'record-counter text-truncate all',
            data: null,
            render: function (data, type, row, meta)
            {
                return meta.row + 1;
            }
        },
        { data: 'date',      className: 'td-date      text-center text-truncate', },
        { data: 'am_in',     className: 'td-am-in     text-center text-truncate', },
        { data: 'am_out',    className: 'td-am-out    text-center text-truncate', },
        { data: 'pm_in',     className: 'td-pm-in     text-center text-truncate', },
        { data: 'pm_out',    className: 'td-pm-out    text-center text-truncate', },
        { 
            data: 'remarks',   className: 'td-remarks text-center text-truncate', 
            render: function(data, type, row) 
            {
                if (data && remarkStyles)
                {
                    let styles = remarkStyles[data];

                    let html = `<div class="remarks-container">
                        <div class="remark-styles ${styles.style}">
                            <i class="fas icon"></i>
                            <span class="label">${styles.label}</span>
                        </div>
                    </div>`;

                    return html;
                }

                return data;
            }
        },
        { data: 'duration',  className: 'td-duration  text-center text-truncate', },
        { data: 'late',      className: 'td-late      text-center text-truncate', },
        { data: 'overtime',  className: 'td-overtime  text-center text-truncate', },
        { data: 'undertime', className: 'td-undertime text-center text-truncate', },
    ];

    function __bindTableDataSource(month)
    {
        selectedMonth = month;

        let options = {
            'deferRender'   : true,
            'searching'     : false,
            'ordering'      : false,
            'autoWidth'     : false,
            'responsive'    : true,
            'columnDefs'    : [
                { className: 'none',            targets: [3, 4, 7, 8, 9, 10] },
                { className: 'desktop',         targets: [7, 8]},
                { className: 'mobile',          targets: [1, 6] },
                { className: 'desktop tablet',  targets: [0, 1, 2, 5, 6]}
            ],
            'drawCallback': function (settings) 
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
                url: routes.datasource,
                type: 'POST',
                dataType: 'JSON',
                dataSrc: function (json) 
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

                    if ('month' in json)
                        $('.lbl-attendance-range').text(json.month);

                    if ('remarkStyles' in json)
                        remarkStyles = json.remarkStyles;

                    return json.data;
                },
                data: function () 
                {
                    let data = {
                        '_token': getCsrfToken(),
                    };

                    if (selectedMonth)
                    {
                        data['month'] = selectedMonth;
                        selectedMonth = undefined;
                    }

                    return data;
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


    let initialize = function ()
    {
        __bindTableDataSource();

        monthFilter = to_droplist('#input-month-filter');
    };

    let handleEvents = function ()
    {
        monthFilter.changed = () => {
            selectedMonth = monthFilter.getValue();
            __bindTableDataSource(selectedMonth);
        } 
    };

    return {
        'initialize'    : initialize,
        'handleEvents'  : handleEvents
    }

})();

$(document).ready(function () 
{
    attendanceModule.initialize();
    attendanceModule.handleEvents();
});