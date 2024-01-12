let dtr_datasetTable = '.dtr-dataset-table';
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

var test = 'x';

function bindTableDataSource(newRange)
{
    range = newRange;

    let url = $(dtr_datasetTable).data('src-default');

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
            data: function() {
                return {
                    '_token': csrfToken,
                    'employee-key': employeeKey,
                    'range': range
                };
            }
        },
        columns: [

            // First Column -> Day number
            {
                className: 'daynumber td-50 opacity-45',
                data: 'day_number',
            },
            // Second Column -> Date
            {
                className: 'dayname td-60',
                data: 'day_name',
                defaultContent: ''
            },
            // Third Column -> Status
            {
                className: 'am_in text-center td-80',
                data: 'am_in', 
                defaultContent: '',
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
        ]
    };

    // If an instance of datatable has already been created,
    // reload its data source with given url instead
    if (dataTable != null)
    {
        // dataTable.ajax.url(url);
        // dataTable.ajax.data(postData);
        // dataTable.ajax.load();
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
        // xhrFields: {
        //     responseType: 'blob'
        // },
        // success: function(response) 
        // {
        //     if (response)
        //     {
        //         response = JSON.parse(response);

        //         var blob = new Blob([response.blob]);
        //         var link = document.createElement('a');
        //         link.href = window.URL.createObjectURL(blob);
        //         link.download = "MyPDF.pdf";
        //         link.click();
        //     }
        //     else
        //     {
        //         alertModal.showDanger('The server was unable to generate the PDF report');
        //     }
        // },
        success: function(response) 
        {
            if (response)
            {
                response = JSON.parse(response);
            
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
            else
            {
                alertModal.showDanger('The server was unable to generate the PDF report');
            }
        },
        error: function(xhr, error, status) {
            console.warn(xhr.responseText);
        }
    });
}