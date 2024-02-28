let dtr_datasetTable = '.dataset-table';
let dataTable;
let csrfToken;
let range;

let exportPdfTarget = undefined;
let employeeKey = undefined;

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
    csrfToken   = $('meta[name="csrf-token"]').attr('content');
    employeeKey = $(dtr_datasetTable).data('employee-key');

    exportPdfTarget = $(dtr_datasetTable).data('export-target');

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

        bindTableDataSource(range);
    });

    $('#export-button').on('click', function()
    {
        exportPdf(range);
    });
}

function bindTableDataSource(newRange)
{
    range = newRange;

    let url = $(dtr_datasetTable).data('src-default');

    let weekendDays = [];
    let statusMap = {};

    let today = getCurrentDateParts().day;

    var onRenderDefaultContent = function(data, type, row) 
    {
        if (row)
        {
            if (row.day_number && weekendDays.includes(row.day_number))
                return `<span class="text-sm opacity-65">Rest Day</span>`;
            
            // Dont put 'x' mark on future days but only on past days wiht no 'am_in'
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
        ajax: {

            url     : url,
            type    : 'POST',
            dataType: 'JSON',
            data: function() {
                return {
                    '_token': csrfToken,
                    'employee-key': employeeKey,
                    'range': range
                };
            },
            dataSrc : function (json) 
            {
                if (!json)
                    return;

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
        return;
    }
    
    // Initialize datatable if not yet created
    dataTable = $(dtr_datasetTable).DataTable(options);
}

function exportPdf(range)
{
    $.ajax({
        type: 'POST',
        url: exportPdfTarget,
        data: {
            'employee-key': employeeKey,
            'range':        range,
            '_token':       csrfToken
        },
        success: function(response) 
        {
            if (!response)
            {
                alertModal.showDanger('The server was unable to generate the PDF report');
                return;
            }

            response = JSON.parse(response);

            if (response && response.code == -1)
            {
                alertModal.showDanger(response.message);
                return;
            }

            if (response && response.code == 0)
            {
                var byteCharacters = atob(response.fileData);
                var byteNumbers = new Array(byteCharacters.length);
                for (var i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                var byteArray = new Uint8Array(byteNumbers);
            
                var blob = new Blob([byteArray], {type: "application/pdf"});
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = response.filename;
                link.click();
            }
        },
        error: function(xhr, error, status) {
            console.warn(xhr.responseText);
        }
    });
}