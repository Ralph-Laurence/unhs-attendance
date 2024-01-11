let trails_datasetTable = '.attendance-trail-table';
let dataTable;
let csrfToken;

let frmExportPdf = undefined;
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
    csrfToken = $('meta[name="csrf-token"]').attr('content');

    frmExportPdf    = $("#frm-export-trail-pdf");
    exportPdfTarget = frmExportPdf.attr('action');
    employeeKey     = frmExportPdf.find('#employee-key').val();

    bindTableDataSource();
}
//
// Handle events here
//
function handleEvents() 
{
    $('.dropdown-item.trail-range-filter').on('click', function()
    {
        let range    = $(this).data('trail-range');
        let filename = $('#input-export-filename').val();

        exportPdf(filename, range);
    });
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
                'employee-key': employeeKey //$(trails_datasetTable).data('employee-key')
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
        dataTable.ajax.url(url).load();
        return;
    }
    
    // Initialize datatable if not yet created
    dataTable = $(trails_datasetTable).DataTable(options);
}

function exportPdf(filename, range)
{
    $.ajax({
        type: 'POST',
        url: exportPdfTarget,
        data: {
            'employee-key': employeeKey,
            'trail-range':  range,
            'filename':     filename,
            '_token':       csrfToken
        },
        xhrFields: {
            responseType: 'blob'
        },
        success: function(response) 
        {
            if (response)
            {
                var blob = new Blob([response]);
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "MyPDF.pdf";
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