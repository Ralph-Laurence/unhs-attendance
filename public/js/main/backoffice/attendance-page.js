let teachers_datasetTable = '.dataset-table';
let dataSrcTarget;
let dataTable;
let dataTable_isFirstDraw = true;
let iconStyles;
let csrfToken;

let range;
let monthIndex;

const RANGE_TODAY = 'this_day';
const RANGE_WEEK  = 'this_week';
const RANGE_MONTH = 'by_month';

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

    dataSrcTarget = $(teachers_datasetTable).data('src-default');

    bindTableDataSource(RANGE_TODAY);
}
//
// Handle events here
//
function handleEvents() 
{
    $(document).on('click', '.row-actions .btn-delete', function() 
    {
        let row = $(this).closest('tr');
        
        let employeeName = row.find('.td-employee-name').text();
        let recordDate   = row.find('.date-tile').attr('data-date-attr');

        recordDate = convertToFullMonth(recordDate);

        if (employeeName)
            employeeName = employeeName.trim();

        let message = sanitize(`You are about to delete the attendance record of <i><b>"${employeeName}"</b></i> which was created on ${recordDate}. Once deleted, it cannot be recovered.<br><br>Do you wish to proceed?`);

        alertModal.showWarn(message, 'Warning', () => deleteRecord(row));
    });

    $('.record-range-filter .daily').on('click', function() {
        bindTableDataSource(RANGE_TODAY);
    });

    $('.record-range-filter .weekly').on('click', function() {
        bindTableDataSource(RANGE_WEEK);
    });

    $('.role-filters .dropdown-item').on('click', function() {
        var role = $(this).data('role');
        alert(role);
    });

    $('.month-select-dropmenu #selected-month-index').on('change', function()
    {
        monthIndex = $(this).val();
        bindTableDataSource(RANGE_MONTH, monthIndex);
    });
}

function bindTableDataSource(new_range, new_monthIndex)
{
    let currentDate = getCurrentDateParts();

    range = new_range;
    monthIndex = new_monthIndex;

    let options = {
        "deferRender"  : true,
        'searching'    : false,
        'ordering'     : false,
        'bAutoWidth'   : false,

        'drawCallback' : function() 
        {   
            // dataTable_isFirstDraw is when the "Loading..." was first shown.
            // We need to show the alert message only when it is not on first draw
            // and when rows are empty
            if (dataTable_isFirstDraw)
            {
                dataTable_isFirstDraw = false;
                return;
            }

            var isEmpty = this.api().rows().count() === 0;

            if (isEmpty)
                snackbar.showInfo('No data to show');
        },

        ajax: {

            url     : dataSrcTarget,
            type    : 'POST',
            dataType: 'JSON',
            dataSrc : function(json) 
            {
                if (iconStyles == undefined)
                    iconStyles = json.icon;

                if (json && 'code' in json)
                {
                    if (json.code == -1) 
                    {
                        $('.card-title .attendance-range').text('No data');
                        alertModal.showDanger(json.message);

                        return [];
                    }
                }

                if ('range' in json)
                {
                    if (json.range)
                        $('.card-title .attendance-range').text(json.range);
                }

                monthIndex = undefined;

                return json.data;
            },
            data: function () 
            {
                return {
                    '_token' : csrfToken,
                    'monthIndex': monthIndex,
                    'range': range
                }
            }
        },
        columns: [

            // First Column -> Record Counter
            {
                width: '50px',
                className: 'record-counter text-truncate opacity-45',
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            // Second Column -> Date
            {
                className: 'text-truncate text-center',
                width: '80px',
                data: 'created_at',
                render: function (data, type, row) 
                {
                    if (data == '' || data == undefined)
                        return '';

                    var date = extractDate(data);
                    var dateAttr = `${date.month} ${date.day}`;

                    let dayMarkerColor = '';

                    if (date.month == currentDate.month && date.day == currentDate.day && date.year == currentDate.year)
                        dayMarkerColor = 'day-today';

                    var dateTile = 
                    `<div class="date-tile" data-date-attr="${dateAttr}">
                        <div class="month">${date.month}</div>
                        <div class="day ${dayMarkerColor} mb-0">${date.day}</div>
                        <div class="dayname">${date.dayName}</div>
                    </div>`;

                    return dateTile;
                },
                defaultContent: ''
            },
            // Third Column -> Status
            {
                width: '120px',
                data: 'status', 
                defaultContent: '',
                render: function(data, type, row) {
                    return `<div class="attendance-status ${iconStyles[data]}">${data}</div>`;
                }
            },
            // Fourth Column -> Employee Name
            {
                className: 'td-employee-name text-truncate',
                width: '280px',
                data: null,
                render: function (data, type, row) {  
                    return `<span class="text-darker">${[data.fname, data.mname, data.lname].join(' ')}</span>`;
                },
                defaultContent: ''
            },
            // Fifth Column -> Clockin Time
            {
                width: '100px',
                data: 'timein',
                render: function(data, type, row) {
                    return data ? format12Hour(data) : ''
                },
                defaultContent: ''
            },
            // Sixth Column -> Clockout Time
            {
                width: '100px',
                data: 'timeout', 
                defaultContent: '',
                render: function(data, type, row) {
                    return data ? `<span class="text-darker">${format12Hour(data)}</span>` : ''
                }
            },
            // Seventh Column -> Work Hours (Duration)
            {data: 'duration', defaultContent: ''},

            // Eighth Column -> Actions
            {
                data: null,
                className: 'text-center',
                width: '120px',
                render: function(data, type, row) {
                    
                    return createRowActions(data.id);
                }
            }
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
    dataTable = $(teachers_datasetTable).DataTable(options);
}

function deleteRecord(row) 
{
    let rowActionsDiv = row.find('.row-actions');
    let rowKey  = rowActionsDiv.attr('data-record-key');
    let spinner = rowActionsDiv.find('.loader');

    showRowActionSpinner(true, spinner);
    showRowActionButtons(false, rowActionsDiv);

    $.ajax({
        url: route_deleteRecord,
        type: 'POST',
        data: {
            '_token' : csrfToken,
            'rowKey' : rowKey
        },
        success: function(response) 
        {
            if (response)
            {
                response = JSON.parse(response);
                
                console.log(response);
                console.log(response.code == 0);
                console.log(typeof (response.code));

                if (response.code == 0)
                {
                    snackbar.showSuccess(response.message);
                    dataTable.row(row).remove().draw();
                }
                else
                    snackbar.showDanger(response.message);
            }
            else
                alertModal.showDanger('Something went wrong while trying to delete the record');
        },
        error: function(xhr, status, error) 
        {
            alertModal.showDanger(xhr.reponseText);
            showRowActionSpinner(false, spinner);
            showRowActionButtons(true, rowActionsDiv);
        }
    })
}

// We assume that the input has a format like "Jan 02".
// Then we will convert the Three-letter month into
// its full month equivalent
function convertToFullMonth(dateString)
{
    // Parse the date string
    var date = new Date(dateString);

    // Check if the date is valid
    if (isNaN(date.getTime()))
    {
        // Not a valid date
        return '(Unknown date)';
    }

    // Get the full month name
    var fullMonth = date.toLocaleString('default', { month: 'long' });

    // Get the day
    var day = date.getDate();

    // Return the full month name and day
    return fullMonth + ' ' + day;
}
