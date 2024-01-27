let teachers_datasetTable = '.dataset-table';
let dataSrcTarget;
let dataTable;
let dataTable_isFirstDraw = true;
let iconStyles;
let csrfToken;

let global_rangeFilter;
let global_monthFilter;
let global_roleFilter;

let last_selected_range;

let roleFilterEl;
let roleFilterDropdown;
let lblAttendanceRange;

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
    roleFilterEl  = $("#role-filters-dropdown-button");
    roleFilterDropdown = new mdb.Dropdown(roleFilterEl);

    lblAttendanceRange = $('.card-title .lbl-attendance-range');

    bindTableDataSource(RANGE_TODAY);
}
//
// Handle events here
//
function handleEvents() 
{
    dataTable.on('length.dt', function(e, settings, len)
    {
        alert('changed');
    })
    .on('dataSrc.dt', function(e, settings, len)
    {
        alert('data src changed');
    });

    $(document).on('click', '.row-actions .btn-delete', function() 
    {
        let row = $(this).closest('tr');
        
        let employeeName = row.find('.td-employee-name').text();
        let recordDate   = row.find('.absent-date').attr('data-date-attr');

        recordDate = convertToFullMonth(recordDate);

        if (employeeName)
            employeeName = employeeName.trim();

        let message = sanitize(`You are about to delete the record of absence for the employee <span class="me-1"><i><b>"${employeeName}"</b></i></span> which was created on <i>${recordDate}</i>. Once deleted, it cannot be recovered.<br><br>Do you wish to proceed?`);

        alertModal.showWarn(message, 'Warning', () => deleteRecord(row));
    });

    $('.record-range-filter .daily').on('click', function() {
        bindTableDataSource(RANGE_TODAY, undefined, global_roleFilter);
    });

    $('.record-range-filter .weekly').on('click', function() {
        bindTableDataSource(RANGE_WEEK, undefined, global_roleFilter);
    });

    $('.month-select-dropmenu #selected-month-index').on('change', function()
    {
        global_monthFilter = $(this).val();
        bindTableDataSource(RANGE_MONTH, global_monthFilter, global_roleFilter);
    });

    $('.role-filters .dropdown-item').on('click', function() 
    {        
        let option = $(this);

        global_roleFilter = option.data('role');

        $('.role-filters .dropdown-item').removeClass('selected-option');
        option.addClass('selected-option');

        $(this).closest('.dropdown').find('.dropdown-toggle .button-text').text(global_roleFilter);
        bindTableDataSource(last_selected_range, global_monthFilter, global_roleFilter);
    });
}

function bindTableDataSource(ref_range, ref_monthIndex, ref_roleFilter)
{
    disableControlButtons();

    let currentDate = getCurrentDateParts();

    global_rangeFilter  = ref_range;
    global_roleFilter   = ref_roleFilter;
    global_monthFilter  = ref_monthIndex;

    // dataTable.column('empname:name').search('Kim').draw()

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
            {
                snackbar.showInfo('No records to show');
                return;
            }

            // updateRowEntryNumbers(dataTable);

            // var api = dataTable.api();
            // var startIndex = api.context[0]._iDisplayStart; // get the start index

            // api.column(0, { page: 'current' }).nodes().each(function (cell, i)
            // {
            //     cell.innerHTML = startIndex + i + 1; // update cell content
            // });
        },

        ajax: {

            url      : dataSrcTarget,
            type     : 'POST',
            dataType : 'JSON',
            dataSrc  : function(json) 
            {
                if (iconStyles == undefined)
                    iconStyles = json.icon;

                if (!json)
                    return;

                // Display Messages By Error Codes
                if ('code' in json) 
                {
                    if (json.code == -1) 
                    {
                        lblAttendanceRange.text('No data');
                        alertModal.showDanger(json.message);

                        return [];
                    }
                }

                // Descriptive Range
                if ('range' in json)
                {
                    if (json.range)
                        lblAttendanceRange.text(json.range);
                }

                // Last Selected Range Filters
                if ('filters' in json)
                {
                    last_selected_range = json.filters.select_range;
                    
                    if (last_selected_range == RANGE_MONTH)
                        global_monthFilter = json.filters['month_index'];

                    if (json.filters.select_role)
                        $('.lbl-employee-filter').text(json.filters.select_role);
                }

                // After AJAX response, reenable the control buttons
                enableControlButtons();

                return json.data;
            },
            data: function () 
            {
                return {
                    '_token'    : csrfToken,
                    'monthIndex': global_monthFilter,
                    'range'     : global_rangeFilter,
                    'role'      : global_roleFilter
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
            
            // Third Column -> Status
            // Fourth Column -> Employee ID
            {
                width: '120px',
                data: 'idNo',
                defaultContent: ''
            },
            // Fifth Column -> Employee Name
            {
                className: 'td-employee-name text-truncate',
                width: '280px',
                data: null,
                name: 'empname',
                render: function (data, type, row) 
                {  
                    return `<span class="text-darker">${data.emp_fullname}</span>`;
                },
                defaultContent: ''
            },
            // Sixth Column -> Employee Role
            {
                width: '100px',
                data: 'role', 
                defaultContent: ''
            },
            {
                className: 'text-truncate text-center',
                width: '180px',
                data: 'created_at',
                render: function (data, type, row) 
                {
                    if (data == '' || data == undefined)
                        return '';

                    let date = extractDate(data);
                    let dateAttr = `${date.month} ${date.day}`;

                    let dateLabel = '';

                    if (date.month == currentDate.month && date.day == currentDate.day && date.year == currentDate.year)
                        dateLabel = `<div class="absent-date text-primary-dark opacity-90" data-date-attr="${dateAttr}">Today, ${dateAttr}</div>`;
                    else
                        dateLabel = `<div class="absent-date" data-date-attr="${dateAttr}">${date.month} ${date.day}, ${date.year}</div>`;

                    return dateLabel;
                },
                defaultContent: ''
            },
            {
                width: '120px',
                data: 'status', 
                defaultContent: '',
                render: function(data, type, row) {
                    return `<div class="attendance-status ${iconStyles[data]}">${data}</div>`;
                }
            },
            // Seventh Column -> Actions
            {
                data: null,
                className: 'text-center',
                width: '80px',
                render: function(data, type, row) {
                    
                    return createRowDeleteAction(data.id);
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

function enableControlButtons()
{
    enableRangeFilter();
    roleFilterEl.prop('disabled', false);
}

function disableControlButtons()
{
    finishRangeFilter();

    roleFilterDropdown.hide();
    roleFilterEl.prop('disabled', true);
}
