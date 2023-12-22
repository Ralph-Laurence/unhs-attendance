let beep_timeIn;
let beep_timeOut;
let beep_blip;

let scanner;
let lastScanned = {};
const refractoryPeriod = 5000;
//
//
// (Entry Point or...) Starting Point;
// Runs only when the page is fully loaded
//
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
    setInterval(updateClock, 1000);

    // animate the calendar to change from Date today and Day name
    animateCalendar();

    // Prepare the scanner, with 250px scan area, and 10 frames per secs. scan rate
    scanner = new Html5QrcodeScanner('reader', { 
        fps: 10,
        qrbox: {width: 250, height: 250}
    });
}
//
// Handle events here
//
function handleEvents()
{
    // Called when the scanner is done scanning and decoded a result
    scanner.render(onScanSuccess);
}
//
//
//
function onScanSuccess(decodedText, decodedResult)
{
    // Get the current time
    let currentTime = new Date().getTime();

    // Get the time stored in the 'lastScanned' qr code data
    let lastScannedTime = lastScanned[decodedText];

    // Process each QR Code only once within a specified refractory period. Which
    // means, we must wait for the refractory period (stored in the 'lastScanned')
    // to elapse (lumipas) before processing the same QR Code again. If no time is 
    // found in the 'lastScanned', it is assumed to be its first scan.
    if (lastScannedTime === undefined || currentTime - lastScannedTime >= refractoryPeriod)
    {
        lastScanned[decodedText] = currentTime;

        // Play a beep sound after a successful scan
        playSound(beep_timeIn);
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
//
function playSound(soundSource)
{
    var audio = new Audio(soundSource);
    audio.play();
}
//
//
//
function updateClock()
{
    /*
    var time = moment();                            // Create a MomentJS date-time object

    var hoursMins = time.format('h:mm');            // The Hour:Minutes time in 12-hour format
    var millisec  = time.format(':ss');             // The Milliseconds time
    */

    // $('.date-time-label .time-label .hour-minutes-label').text(hoursMins);
    // $('.date-time-label .time-label .millisec-label').text(millisec);

    var date    = new Date();           // Create a JavaScript Date object
    var hours   = date.getHours();
    var minutes = date.getMinutes();
    var seconds = date.getSeconds();

    // Format the hours, minutes, and seconds
    var hoursMins = (hours % 12 || 12) + ':' + (minutes < 10 ? '0' : '') + minutes; // The Hour:Minutes time in 12-hour format
    var millisec  = ':' + (seconds < 10 ? '0' : '') + seconds; // The Seconds

    document.querySelector('.date-time-label .time-label .hour-minutes-label').textContent = hoursMins;
    document.querySelector('.date-time-label .time-label .millisec-label').textContent = millisec;
}
//
//
//
function animateCalendar()
{
    let date     = moment();
    let dayToday = date.format('MMM. D, YYYY');    // The current date with 3-letter month
    let dayName  = date.format('dddd');

    let animation = new SlideText('.date-label');

    animation.items = [dayToday, dayName];
    animation.slideDelay = 4000;                  // Every 3 seconds
    animation.start();
}