let dtr_datasetTable = '.dataset-table';
let dataTable;

let range;
let monthNumber;

let exportPdfTarget = undefined;
let employeeKey     = undefined;
let monthPicker;

let periodDropdowns;
let tablePageLen;

const pageDefaultTitle = document.title;

$(document).ready(function() 
{
    initialize();
    handleEvents();
});

//
// Use this for initialization
//
function initialize()
{
    employeeKey     = $(dtr_datasetTable).data('employee-key');
    exportPdfTarget = $(dtr_datasetTable).data('export-target');

    periodDropdowns = new mdb.Dropdown("#period-dropdown-button");

    monthPicker     = to_monthpicker('#dtr-months');

    bindTableDataSource();
}
//
// Handle events here
//
function handleEvents() 
{
    $('.dropdown-item.period-filter').on('click', function()
    {
        range = $(this).data('dtr-period');

        if (range == 'o')
        {
            $('#other-months-filter').show();
            return;
        }
        else
        {
            periodDropdowns.hide();
            monthPicker.reset();
            
            $('#other-months-filter').hide();
        }
        
        bindTableDataSource(range);

        $('.dropdown-item.period-filter').removeClass('active');
        $(this).addClass('active');
    });

    $('#export-button').on('click', () => exportPdf(monthNumber));

    monthPicker.changed = (info) => {

        periodDropdowns.hide();

        if (range && info.monthNumber)
        {
            bindTableDataSource(range, info.monthNumber);

            $('.dropdown-item.period-filter').removeClass('active');
            $('.dropdown-item.period-filter[data-dtr-period="o"]').addClass('active');
        }

        monthNumber = info.monthNumber;
    };
}

function bindTableDataSource(newRange, newMonthNumber)
{
    disableControlButtons();

    range       = newRange;
    monthNumber = newMonthNumber;

    let url = $(dtr_datasetTable).data('src-default');

    let weekendDays = [];
    let statusMap   = {};

    let today = getCurrentDateParts().day;

    var onRenderDefaultContent = function(data, type, row) 
    {
        if (row)
        {
            if (row.day_number && weekendDays.includes(row.day_number))
                return `<span class="text-sm opacity-65">Rest Day</span>`;
            
            // Dont put 'x' mark on future days but only on past days with no 'am_in'
            if (!row.am_in && row.day_number < today)
                return `<span class="text-danger">${'\u00d7'}</span>`;
        }

        return data;
    };

    let options = {
        "processing"   : true,
        "deferRender"  : true,
        'searching'    : false,
        'ordering'     : false,
        'bAutoWidth'   : false,
        'drawCallback' : function(settings) 
        {
            enableControlButtons();
        },
        ajax: {

            url        : url,
            type       : 'POST',
            dataType   : 'JSON',
            data : function() {
                return {
                    '_token'        : getCsrfToken(),
                    'employee-key'  : employeeKey,
                    'range'         : range,
                    'month'         : monthNumber
                };
            },
            dataSrc : function (json) 
            {
                if (!json)
                    return;

                if (json.code == -1)
                {
                    alertModal.showDanger(json.message);
                    return
                }

                if (json.weekendDays && weekendDays.length < 1)
                    weekendDays = json.weekendDays;

                if (json.statistics)
                {
                    let stats = json.statistics;
                    
                    $('.th-work-hrs').text(stats.totalWorkHrs);
                    $('.th-undertime-hrs').text(stats.totalUndertime);
                    $('.th-overtime-hrs').text(stats.totalOvertime);
                    $('.th-late-hrs').text(stats.totalLateHrs);
                    $('.th-total-present').text(stats.totalPresent);
                    $('.th-total-absent').text(stats.totalAbsent);
                    $('.th-leave-count').text(stats.leaveCount);

                    statusMap = stats.statusMap;
                }

                $('.lbl-dtr-period').text(json.dtrRange || 'Unknown (Reload Required)');

                return json.data;
            },
        },
        columns: [

            // First Column -> Day number
            {
                className: 'daynumber td-50',
                data: 'day_number',
                defaultContent: '',
                render: function (data, type, row) 
                {
                    let color = 'text-record-counter ';

                    if(data && weekendDays.includes(data))
                        color = 'text-danger';
                    
                    return `<span class="${color}">${data}</span>`;
                }
            },
            // Second Column -> Date
            {
                className: 'dayname td-60',
                data: 'day_name',
                name: 'day_name',
                defaultContent: '',
                render: function(data, type, row) 
                {
                    let color = 'text-primary-dark';
                     
                    let dayName = data.toString().toLowerCase();

                    if (dayName == 'sat' || dayName == 'sun')
                        color = 'text-danger';

                    return `<span class="${color}">${data}</span>`;
                }
            },
            {
                className: 'am_in text-center td-80 v-stripe-accent-green border-start border-end',
                data: 'am_in', 
                render: function(data, type, row) 
                {
                    data = data || '\u00d7';
                    
                    if(row.day_number && weekendDays.includes(row.day_number))
                        return '<span class="text-special-dark-green text-sm">Rest Day</span>';

                    return `<span class="text-special-dark-green">${data}</span>`;
                },
            },
            {
                className: 'am_out text-center td-80',
                data: 'am_out', 
                defaultContent: '',
                render: onRenderDefaultContent,
            },
            {
                className: 'pm_in text-center td-80',
                data: 'pm_in', 
                //defaultContent: '',
                render: onRenderDefaultContent,
            },
            {
                className: 'pm_out text-center td-80 v-stripe-accent-yellow border-start border-end',
                data: 'pm_out', 
                defaultContent: '',
                render: function(data, type, row) 
                {
                    data = data || '\u00d7';
                    
                    if(row.day_number && weekendDays.includes(row.day_number))
                        return '<span class="text-special-dark-warning text-sm">Rest Day</span>';

                    return `<span class="text-special-dark-warning">${data}</span>`;
                },
            },
            {
                className: 'duration td-120 text-center',
                data: 'duration', 
                defaultContent: '',
                render: onRenderDefaultContent,
            },
            {
                className: 'late td-120 v-stripe-accent border-start border-end text-center',
                data: 'late', 
                defaultContent: '',
                render: onRenderDefaultContent,
            },
            {
                className: 'undertime td-120 text-center',
                data: 'undertime', 
                defaultContent: '',
                render: onRenderDefaultContent,
            },
            {
                className: 'overtime td-120 v-stripe-accent border-start border-end text-center',
                data: 'overtime', 
                defaultContent: '',
                render: onRenderDefaultContent,
            },
            {
                className: 'status td-100 text-center',
                data: 'status', 
                defaultContent: '',
                render: function(data, type, row)
                {
                    if (!statusMap || (statusMap && !statusMap[data]))
                        return data;

                    let status = statusMap[data];

                    return `<span class="dtr-status ${status.style} px-2 py-1 rounded-2">${status.label}</span>`;
                }
            },
        ]
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
    dataTable = $(dtr_datasetTable).DataTable(options);
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
//         dataTable.ajax.reload();
//         return;
//     }
    
//     // Initialize datatable if not yet created
//     dataTable = $(dtr_datasetTable).DataTable(options);
// }

function exportPdf(monthNumber)
{
    disableControlButtons();
    updateExportStatus('Fetching data, please wait...');

    $.ajax({
        type : 'POST',
        url  : exportPdfTarget,
        data : {
            '_token'        : getCsrfToken(),
            'employee-key'  : employeeKey,
            'month'         : monthNumber
        },
        success: function (response) 
        {
            if (!response)
            {
                alertModal.showDanger('The server was unable to generate the PDF report');
                return;
            }

            response = JSON.parse(response);

            if (response)
                onPrintResponse(response);
        },
        error: function (xhr, error, status)
        {
            enableControlButtons();
            clearExportStatus();
            console.warn(xhr.responseText);
        },
    });
}

function onPrintResponse(response)
{
    updateExportStatus('Preparing printable copy...');
    let tbody = $('.print-dtr .printable-content table.dtr-summary tbody');

    try
    {
        if (response.code == -1)
        {
            let error = new Error(response.message);
            error.code = -1;

            throw error;
        }

        if (response.code == 0)
        {
            tbody.empty();

            for (let row of response.dataset)
            {
                let weekends = '';

                if (row.am_in &&
                    (typeof row.am_in === 'string' && row.am_in.toString().toLowerCase() == 'sat') ||
                    (typeof row.am_in === 'string' && row.am_in.toString().toLowerCase() == 'sun'))
                {
                    weekends = 'text-danger';
                }

                let tr =
                `<tr>
                    <td class="${weekends}">${row.day_number || ''}</td>
                    <td class="${weekends}">${row.am_in || ''}</td>
                    <td>${row.am_out || ''}</td>
                    <td>${row.pm_in || ''}</td>
                    <td>${row.pm_out || ''}</td>
                    <td>${row.undertime_hours || ''}</td>
                    <td>${row.undertime_minutes || ''}</td>
                </tr>`;

                tbody.append(tr);
            }
            $('.dtr-empname').text(response.empDetails.empname || '');
            $('.dtr-month-range .month-of').text(response.monthOf || '');
            $('.print-dtr .printable-content table.dtr-summary .th-undertime').text(response.undertime || '');

            $('.printable-content').show().printThis({

                beforePrint: () => {
                    document.title = $('.printable-content').data('export-filename');
                },
                afterPrint : () =>
                {
                    document.title = pageDefaultTitle;
                    clearExportStatus();
                    $('.printable-content').hide();
                    enableControlButtons();
                }
            });
        }
    }
    catch (error) 
    {
        enableControlButtons();
        clearExportStatus();
 
        if (error.code)
        {
            alertModal.showDanger(error.message);
            return;
        }

        alertModal.showDanger("The requested action can't be completed because an error has occurred. Please try again later.");
    }
}

function enableControlButtons() 
{
    $('.control-button').prop('disabled', false);
}

function disableControlButtons()
{
    $('.control-button').prop('disabled', true);
}

function updateExportStatus(status) 
{
    $('.export-status').removeClass('d-none');
    $('.export-status .status-text').text(status);
}

function clearExportStatus(status) 
{
    $('.export-status .status-text').text('');
    $('.export-status').addClass('d-none');
}