let datasetTable = '.dataset-table';
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
    $(document).on('employeeFormInsertSuccess', function (event, data) 
    { 
        console.warn(data)

        if (data && data.code == 0)
        {
            // {code: 0, message: 'Success!', idNo: '11', name: 'Apollo Buzz Aldrin', rowKey: 'e5oLyp3k19'}
            var rowNode = dataTable.row.add({
                emp_num: data.emp_num,
                fname: data.fname,
                mname: data.mname,
                lname: data.lname,
                emp_status: data.emp_status,
                total_lates: 0,
                total_leave: 0,
                total_absents: 0,
                id: data.id
            }).draw().node();

             // Add classes to the new row
            $(rowNode).find('td').eq(0).addClass('record-counter text-truncate opacity-45');
            $(rowNode).find('td').eq(1).addClass('text-truncate');
            $(rowNode).find('td').eq(2).addClass('td-employee-name text-truncate');
            $(rowNode).find('td').eq(7).addClass('text-center');
        }
    });
    // $(document).on('click', '.row-actions .btn-delete', function() 
    // {
    //     let row = $(this).closest('tr');
        
    //     let employeeName = row.find('.td-employee-name').text();
    //     let recordDate   = row.find('.date-tile').attr('data-date-attr');

    //     recordDate = convertToFullMonth(recordDate);

    //     if (employeeName)
    //         employeeName = employeeName.trim();

    //     let message = sanitize(`You are about to delete the attendance record of <i><b>"${employeeName}"</b></i> which was created on ${recordDate}. Once deleted, it cannot be recovered.<br><br>Do you wish to proceed?`);

    //     alertModal.showWarn(message, 'Warning', () => deleteRecord(row));
    // });
    // $(document).on('click', '.row-actions')
    // $(document).on('click', '.row-actions')
}

function bindTableDataSource(url)
{
    url = url || $(datasetTable).data('src-default');

    let options = {
        "deferRender"  : true,
        'searching'    : false,
        'ordering'     : false,
        'bAutoWidth'   : false,
        ajax: {

            url     : url,
            type    : 'POST',
            dataType: 'JSON',
            data: {
                '_token' : csrfToken
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
            {
                width: '120px',
                className: 'text-truncate',
                data: 'emp_num',
                defaultContent: ''
            },
            // Second Column -> Names
            {
                className: 'td-employee-name text-truncate',
                width: '300px',
                data: null,
                render: function (data, type, row) {  
                    return `<span class="text-darker">${[data.fname, data.mname, data.lname].join(' ')}</span>`;
                },
                defaultContent: ''
            },
            // Third Column -> Status
            {
                width: '120px',
                data: 'emp_status', 
                defaultContent: '',
                render: function(data, type, row) {
                    return data; //`<div class="attendance-status ${iconStyles[data]}">${data}</div>`;
                }
            },
            // Seventh Column -> Work Hours (Duration)
            { width: '80px', data: 'total_lates', defaultContent: ''},
            { width: '80px', data: 'total_leave', defaultContent: ''},
            { width: '80px', data: 'total_absents', defaultContent: ''},

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
        dataTable.ajax.url(url).load();
        return;
    }
    
    // Initialize datatable if not yet created
    dataTable = $(datasetTable).DataTable(options);
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