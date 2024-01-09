let trails_datasetTable = '.attendance-trail-table';
let dataTable;
let csrfToken;

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
    csrfToken = $('meta[name="csrf-token"]').attr('content');
    bindTableDataSource();
}
//
// Handle events here
//
function handleEvents() 
{
}

function bindTableDataSource(url)
{
    //let currentDate = getCurrentDateParts();

    url = url || $(trails_datasetTable).data('src-default');

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
            // dataSrc : function(json) {

            //     if (iconStyles == undefined)
            //         iconStyles = json.icon;

            //     return json.data;
            // },
            data: {
                '_token' : csrfToken,
                'employee-key': $(trails_datasetTable).data('employee-key')
            }
        },
        columns: [

            // First Column -> Day number
            {
                className: 'daynumber td-50 opacity-45',
                data: 'day_number',
                // render: function(data, type, row, meta) {
                //     return meta.row + 1;
                // }
            },
            // Second Column -> Date
            {
                className: 'dayname td-60',
                data: 'day_name',
                // render: function (data, type, row) 
                // {
                //     if (data == '' || data == undefined)
                //         return '';

                //     var date = extractDate(data);
                //     var dateAttr = `${date.month} ${date.day}`;

                //     let dayMarkerColor = '';

                //     if (date.month == currentDate.month && date.day == currentDate.day && date.year == currentDate.year)
                //         dayMarkerColor = 'day-today';

                //     var dateTile = 
                //     `<div class="date-tile" data-date-attr="${dateAttr}">
                //         <div class="month">${date.month}</div>
                //         <div class="day ${dayMarkerColor} mb-0">${date.day}</div>
                //         <div class="dayname">${date.dayName}</div>
                //     </div>`;

                //     return dateTile;
                // },
                defaultContent: ''
            },
            // Third Column -> Status
            {
                className: 'am_in text-center td-80',
                data: 'am_in', 
                defaultContent: '',
                // render: function(data, type, row) {
                //     return `<div class="attendance-status ${iconStyles[data]}">${data}</div>`;
                // }
            },
            {
                className: 'am_out text-center td-80',
                data: 'am_out', 
                defaultContent: '',
            },
            {
                className: 'pm_in text-center td-80',
                data: 'pm_in', 
                defaultContent: '',
            },
            {
                className: 'pm_out text-center td-80',
                data: 'pm_out', 
                defaultContent: '',
            },
            {
                className: 'duration td-120',
                data: 'duration', 
                defaultContent: '',
            },
            {
                className: 'late td-120',
                data: 'late', 
                defaultContent: '',
            },
            {
                className: 'undertime td-120',
                data: 'undertime', 
                defaultContent: '',
            },
            {
                className: 'overtime td-120',
                data: 'overtime', 
                defaultContent: '',
            },
            {
                className: 'status td-100',
                data: 'status', 
                defaultContent: '',
            },
            // Fourth Column -> Employee Name
            // {
            //     className: 'td-employee-name text-truncate',
            //     width: '280px',
            //     data: null,
            //     render: function (data, type, row) {  
            //         return `<span class="text-darker">${[data.fname, data.mname, data.lname].join(' ')}</span>`;
            //     },
            //     defaultContent: ''
            // },
            // Fifth Column -> Clockin Time
            // {
            //     width: '100px',
            //     data: 'timein',
            //     render: function(data, type, row) {
            //         return data ? format12Hour(data) : ''
            //     },
            //     defaultContent: ''
            // },
            // Sixth Column -> Clockout Time
            // {
            //     width: '100px',
            //     data: 'timeout', 
            //     defaultContent: '',
            //     render: function(data, type, row) {
            //         return data ? `<span class="text-darker">${format12Hour(data)}</span>` : ''
            //     }
            // },
        ]
    };

    // If an instance of datatable has already been created,
    // reload its data source with given url instead
    if (dataTable != null)
    {
        dataTable.ajax.url(url).load();
        return;
    }
    
    // Initialize datatable if not yet created
    dataTable = $(trails_datasetTable).DataTable(options);
}