const SCANNER_STATE_NOT_STARTED = 1;
const SCANNER_STATE_SCANNING    = 2;

let scanner;

let beep_timeIn;
let beep_timeOut;
let blip;

let ctr = 0;

let lastScanned = {};
let refractoryPeriod = 5000;

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
    prepareSounds();

    // update the calendar display's date and time every second
    setInterval(updateCalendar, 1000);

    // Prepare the scanner, with 250px scan area, and 10 frames per secs. scan rate
    scanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: 250});
}

//
// Handle events here
//
function handleEvents()
{
    scanner.render(onScanSuccess);
}

function onScanSuccess(decodedText, decodedResult)
{
    let currentTime = new Date().getTime();
    let lastScannedTime = lastScanned[decodedText];

    if (lastScannedTime === undefined || currentTime - lastScannedTime >= refractoryPeriod)
    {
        console.log("Decoded: " + decodedText);
        lastScanned[decodedText] = currentTime;

        // test output
        $('#output').val($('#output').val() + '\n' + decodedText);

        // Play a beep sound after a successful scan
        playSound(beep_timeIn);

        ctr = 0;
    } 
    else
    {
        console.log("Ignored: " + decodedText);
    }
}
//
// Cache (store a reference) to the sound files stored in <audio> elements
//
function prepareSounds()
{
    beep_timeIn  = $('#beep-time-in')[0].src;
    beep_timeOut = $('#beep-time-out')[0].src;
    blip         = $('#blip')[0].src;
}

function playSound(soundSource)
{
    var audio = new Audio(soundSource);
    audio.play();
}

function updateCalendar()
{
    ctr++;
    $('.sec-ctr').text(ctr);

    var date = moment().format('MMM. D, YYYY');         // The current date with 3-letter month
    var time = moment().format('h:mm a');               // The time in 12-hour format

    $('.date-time-label .date-label').text(date);
    $('.date-time-label .time-label').text(time);
}