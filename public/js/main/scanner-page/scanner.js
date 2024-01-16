let beep_timeIn;
let beep_timeOut;
let beep_blip;

let scanner;
let lastScanned = {};

let dataTable;
let iconStyles;

const attendanceTable  = '.attendance-table';
const refractoryPeriod = 5000;

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
    // Initially load datatable data
    bindDatatableData();

    // Prepare the scanner beeps
    prepareSounds();

    // Prepare the scanner, with 250px scan area, and 10 frames per secs. scan rate
    scanner = new Html5QrcodeScanner('reader', { 
        fps: 10,
        qrbox: {width: 250, height: 250}
    });

    // update the calendar display's date and time every second
    setInterval(x_calendar.updateClock, 1000);

    // animate the calendar to change from Date today and Day name
    x_calendar.animateCalendar();
}
//
// Handle events here   
//
function handleEvents()
{
    scanner.render(onScanSuccess);
}

//==================================================//
//:::::::::::::::   QR CODE SCANNER  ::::::::::::::://
//==================================================//

/**
 * Task:
 * Process each QR Code only once within a specified refractory period. Which
 * means, we must wait for the refractory period (stored in the 'lastScanned')
 * to elapse (lumipas) before processing the same QR Code again. If no time is 
 * found in the 'lastScanned', it is assumed to be its first scan.
 * 
 * @param {*} decodedText 
 * @param {*} decodedResult 
 */
function onScanSuccess(decodedText, decodedResult)
{
    let currentTime     = new Date().getTime();         // Get the current time
    let lastScannedTime = lastScanned[decodedText];     // Get the time stored in the 'lastScanned' qr code data

    if (lastScannedTime === undefined || currentTime - lastScannedTime >= refractoryPeriod)
    {
        lastScanned[decodedText] = currentTime;         // Remember the last scanned data

        playSound(beep_timeIn);                         // Play a beep sound after a successful scan
        submitScanResult(decodedText);                  // Process the scanned result
    } 
    // Otherwise, ignore the scanned qr code
}
//
// Cache (store a reference) to the sound files stored in <audio> elements
//
function prepareSounds()
{
    beep_timeIn  = $('#beep-time-in')[0].src;
    beep_timeOut = $('#beep-time-out')[0].src;
    beep_blip    = $('#blip')[0].src;
}
//
//
function playSound(soundSource)
{
    var audio = new Audio(soundSource);
    audio.play();
}
//
//
function submitScanResult(data) 
{
    console.log(data);

    var metaCSRF = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        type: 'POST',
        url: scannerSubmitUrl,
        //url: "https://cors-anywhere.herokuapp.com/" + scannerSubmitUrl,
        data: {
            '_token' : metaCSRF, 
            hash: data 
        },
        //dataType: "json",
        success: function(response) 
        {
            if (response)
            {
                var data = JSON.parse(response);

                console.log(data);

                if (parseInt(data.code) == -1)
                {
                    showScanFailure(data.message);
                    return;
                }

                dataTable.ajax.reload();
            }
        },
        error: function(xhr, status, error)
        {
            console.warn('ops!\n\n' + xhr.responseText);
            showScanFailure('QR Code failed to authenticate. Please try again.');
        }
    });
}

function showScanFailure(message) 
{
    scanner.pause();
    alertModal.showDanger(message, 'Failure', () => scanner.resume());
}

function bindDatatableData()
{
    let options = {

        'searching'    : false,
        'lengthChange' : false,
        'ordering'     : false,
        'paging'       : false,
        'info'         : false,
        'bAutoWidth'   : false,
        ajax: {

            url     : $(attendanceTable).data('default-src'),//'http://localhost:8000/dtr-scanner/history',
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
                className: 'text-truncate',
                width: '250px',
                data: null,
                render: function (data, type, row) {  
                    return [data.fname, data.mname, data.lname].join(' ')
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
                width: '120px',
                data: 'status', 
                defaultContent: '',
                render: function(data, type, row) 
                {
                    return `<div class="attendance-status ${iconStyles[data]}">${data}</div>`;
                }
            },
        ]
    };

    dataTable = $(attendanceTable).DataTable(options);
}