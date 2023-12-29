let dtrTable = '.dtr-table';
let dataTable;
let iconStyles;

$(document).ready(function(){

    bindDatatableData();
});

function bindDatatableData()
{
    let options = {

        'searching'    : false,
        // 'lengthChange' : false,
        'ordering'     : false,
        // 'paging'       : false,
        // 'info'         : false,
        'bAutoWidth'   : false,
        ajax: {

            url     : $(dtrTable).data('ajax-src'),
            type    : 'GET',
            dataType: 'JSON',
            dataSrc : function(json) {

                if (iconStyles == undefined)
                    iconStyles = json.icon;

                return json.data;
            },
            data: function (d) {
                d.csrf_token = $('meta[name="csrf_token"]').attr('content');
            }
        },
        columns: [
            {
                width: '50px',
                className: 'record-counter text-truncate opacity-45',
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                className: 'text-truncate text-center',
                width: '80px',
                data: 'created_at',
                render: function (data, type, row) 
                {
                    if (data == '' || data == undefined)
                        return '';

                    var date = extractDate(data);

                    var dateTile = 
                    `<div class="date-tile">
                        <div class="month">${date.month}</div>
                        <div class="day mb-0">${date.day}</div>
                        <div class="dayname">${date.dayName}</div>
                    </div>`;

                    return dateTile;
                },
                defaultContent: ''
            },
            {
                width: '120px',
                data: 'status', 
                defaultContent: '',
                render: function(data, type, row) 
                {
                    console.log(data);

                    return `<div class="attendance-status ${iconStyles[data]}">${data}</div>`;
                }
            },
            {
                className: 'text-truncate',
                width: '280px',
                data: null,
                render: function (data, type, row) {  
                    return `<span class="text-darker">${[data.fname, data.mname, data.lname].join(' ')}</span>`;
                },
                defaultContent: ''
            },
            {
                width: '100px',
                data: 'timein',
                render: function(data, type, row) {
                    return data ? format12Hour(data) : ''
                },
                defaultContent: ''
            },
            {
                width: '100px',
                data: 'timeout', 
                defaultContent: '',
                render: function(data, type, row) {
                    return data ? `<span class="text-darker">${format12Hour(data)}</span>` : ''
                }
            },
            {data: 'duration', defaultContent: ''},
            {
                data: null,
                className: 'text-center',
                width: '120px',
                render: function(data, type, row) {
                    var td = 
                    `<div class="row-actions">
                        <button class="btn btn-sm btn-about"> 
                            <i class="fa-solid fa-circle-info"></i> 
                        </button>
                        <button class="btn btn-sm btn-edit"> 
                            <i class="fa-solid fa-pen"></i> 
                        </button>
                        <button class="btn btn-sm btn-delete"> 
                            <i class="fa-solid fa-trash"></i> 
                        </button>
                    </div>`;

                    return td;
                }
            }
        ]
    };

    dataTable = $(dtrTable).DataTable(options);
}