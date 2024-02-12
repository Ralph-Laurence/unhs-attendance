let beep_timeIn;
let beep_timeOut;
let beep_blip;

let scanner;
let lastScanned = {};

let dataTable;
let iconStyles;

let inputId;
let inputPin;

let metaCSRF;
let pinAuthTarget;
let pinAuthModal;

const attendanceTable  = '.attendance-table';
const refractoryPeriod = 10000; //ms

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
    metaCSRF      = $('meta[name="csrf-token"]').attr('content');
    pinAuthTarget = $('.frm-pin-auth').data('action-target');
    pinAuthModal  = new mdb.Modal($('#pinAuthModal'));

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

    inputId = $('#input-id-no');
    inputPin = $('#input-pin-no');
}
//
// Handle events here   
//
function handleEvents()
{
    scanner.render(onScanSuccess);

    // If PIN Auth modal was shown, stop the camera
    $('#pinAuthModal').on('shown.mdb.modal', function()
    {
        if (scanner.getState() === Html5QrcodeScannerState.SCANNING)
            scanner.pause();
    })
    .on('hidden.mdb.modal', function()
    {
        if (scanner.getState() === Html5QrcodeScannerState.PAUSED)
            scanner.resume();

        resetPinAuthForm();
    });

    $('#pinAuthModal .btn-ok').on('click', function()
    {
        // validate the input fields
        if (!inputId.val())
        {
            showTextboxError(inputId, 'Please enter your ID number.');
            return;
        }

        if (!inputPin.val())
        {
            showTextboxError(inputPin, 'Please enter your PIN code.');
            return;
        }

        authenticatePin(inputId.val(), inputPin.val());
    });

    inputPin.on('input', function() 
    {
        if ($(this).val())
            hideTextboxError($(this));
    });

    inputId.on('input', function() 
    {
        if ($(this).val())
            hideTextboxError($(this));
    });
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

            url     : $(attendanceTable).data('default-src'),
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
                render: function (data, type, row) 
                {  
                    // remove all empty strings from the array and then concatenate the remaining elements with a single space.
                    let str = [data.fname, data.mname, data.lname].filter(e => e.length).join(' ');
                    
                    return `<span class="text-darker">${str}</span>`;
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

//==================================================//
//:::::::::::::::    PIN CODE FORM   ::::::::::::::://
//==================================================//

function authenticatePin(idno, pin)
{
    $.ajax({
        url: pinAuthTarget,
        type: 'POST',
        data: {
            '_token' : metaCSRF, 
            'input-id-no': idno,
            'input-pin-no': pin
        },
        success: function(response) 
        {
            if (response)
            {
                var data = JSON.parse(response);

                // Validation Failed
                if (data.validation_stat == 422)
                {
                    for (var field in data.errors)
                    {
                        showTextboxError(`#${field}`, data.errors[field]);
                    }

                    showPinAuthError(data.err_msg);
                    return;
                }

                if ('code' in data)
                {
                    let message = data.message.replace(/\r?\n/g, '<br>');

                    if (data.code == 0)
                    {
                        closePinAuthModal();
                        dataTable.ajax.reload();
                        snackbar.showSuccess(data.message);
                    }
                    else
                    {
                        showPinAuthError(message);
                        showTextboxError('#input-id-no, #input-pin-no');
                    }   
                }
            }
            console.log(response);
        },
        error: function(xhr, error, status) {
            console.log(xhr.responseText);
        }
    });
}

function showPinAuthError(error)
{
    $('.pin-auth-alert-error').text(error).fadeIn();
}

function hidePinAuthError()
{
    $('.pin-auth-alert-error').text('').hide();
}

function closePinAuthModal()
{
    resetPinAuthForm();
    pinAuthModal.hide();
}

function resetPinAuthForm()
{
    hideTextboxError(inputId);
    hideTextboxError(inputPin);
    hidePinAuthError();
    $(".frm-pin-auth").trigger('reset');
}