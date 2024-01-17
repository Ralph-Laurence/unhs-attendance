let datasetTable = '.dataset-table';
let dataTable;
let csrfToken;

let desc_emp_role = "Employee";

$(document).ready(function() 
{
    initialize();
    handleEvents();
});
//
// Use this block for initialization
//
function initialize()
{
    // Descriptive employee role
    desc_emp_role = $(datasetTable).data('employee-role');

    csrfToken = $('meta[name="csrf-token"]').attr('content');
    bindTableDataSource();
}
//
// Use this block to Handle events
//
function handleEvents() 
{
    $(document).on('employeeFormUpdateSuccess', function (event, data, selectedRow) {
        
        let row     = dataTable.row(selectedRow);
        let rowData = row.data();

        rowData.fname = data.fname;
        rowData.mname = data.mname;
        rowData.lname = data.lname;

        // Update the row data
        dataTable.row(row).data(rowData).invalidate().draw();

        let rowNode = $(row.node());
        rowNode.addClass('row-flash');

        // Remove the class after the animation ends
        rowNode.on('animationend', function ()
        {
            rowNode.removeClass('row-flash');
        });
    });

    $(document).on('employeeFormInsertSuccess', function (event, data) 
    { 
        if (!data)
            return;

        if (data.code == 0)
        {
            var rowNode = dataTable.row.add({

                emp_num       : data.emp_num,
                fname         : data.fname,
                mname         : data.mname,
                lname         : data.lname,
                emp_status    : data.emp_status,
                total_lates   : data.total_lates,
                total_leave   : data.total_leave,
                total_absents : data.total_absents,
                id            : data.id

            }).draw().node();

             // Add classes to the new row
            $(rowNode).find('td').eq(0).addClass('record-counter text-truncate opacity-45');
            $(rowNode).find('td').eq(1).addClass('text-truncate');
            $(rowNode).find('td').eq(2).addClass('td-employee-name text-truncate');
            $(rowNode).find('td').eq(7).addClass('text-center');

            snackbar.showSuccess('Employee successfully added.');

            // Check for download link
            if ('qrcode_download' in data) 
            {
                var dl = data.qrcode_download;

                if (dl.url == '404')
                {
                    alertModal.showDanger('Could not download the generated QR Code.');
                    return;
                }
                
                let link        = document.createElement('a');
                link.href       = dl.url;
                link.download   = dl.fileName;

                document.body.appendChild(link);

                // Programmatically click the 'a' element to start the download
                link.click();

                document.body.removeChild(link);
            }
        }
        else
        {
            snackbar.showWarn('Action completed with problems');
        }
    });

    $(document).on('click', '.row-actions .btn-delete', function() 
    {
        let row  = $(this).closest('tr');
        let name = row.find('.td-employee-name').text();

        if (name)
            name = name.trim();

        let message = sanitize(`You are about to remove the ${desc_emp_role} <i><b>"${name}"</b></i> from the employee records. This action will also erase all data associated with this employee such as attendances.<br><br>Are you sure you want to proceed?`);

        alertModal.showWarn(message, 'Warning', () => deleteRecord(row));
    });
    
    $(document).on('click', '.row-actions .btn-edit', function()
    {
        let row = $(this).closest('tr');

        openEditForm(row);
    });

    $(document).on('click', '.row-actions .btn-details', function () 
    {  
        let row = $(this).closest('tr');

        showEmployeeDetails(row);
    });

    $('.btn-add-record').on('click', () => openCreateForm());
}

function bindTableDataSource(url)
{
    url = url || $(datasetTable).data('src-default');

    let emphasizeCounts =  function(data, type, row) {
        let style = `opacity-40`;

        if (data && data > 0)
            style = '';

        return `<span class="${style}">${data}</span>`;
    };

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
            // Second Column -> Employee Number
            {
                width: '120px',
                className: 'text-truncate',
                data: 'emp_num',
                defaultContent: ''
            },
            // Third Column -> Name
            {
                className: 'td-employee-name text-truncate',
                width: '300px',
                data: null,
                render: function (data, type, row) {  
                    return `<span class="text-darker">${[data.fname, data.mname, data.lname].join(' ')}</span>`;
                },
                defaultContent: ''
            },
            // Fourth Column  -> Status
            {
                width: '120px',
                data: 'emp_status', 
                defaultContent: '',
                render: function(data, type, row) {
                    return data; //`<div class="attendance-status ${iconStyles[data]}">${data}</div>`;
                }
            },
            // Fifth Column -> Late
            { width: '80px', data: 'total_lates', defaultContent: '', render: emphasizeCounts},
            { width: '80px', data: 'total_leave', defaultContent: '', render: emphasizeCounts},
            { width: '80px', data: 'total_absents', defaultContent: '', render: emphasizeCounts},

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