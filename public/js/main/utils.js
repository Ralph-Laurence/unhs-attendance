const monthNames  = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
const dayNames    = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

/**
 * Format a timestamp into 12-Hour time string
 * @param {*} timestamp 
 * @returns 
 */
function format12Hour(timestamp) 
{
    var date    = new Date(timestamp);
    var hours   = date.getHours();
    var minutes = date.getMinutes();

    var ampm    = hours >= 12 ? 'pm' : 'am';
    hours       = hours % 12;
    hours       = hours ? hours : 12; // the hour '0' should be '12'
    minutes     = minutes < 10 ? '0' + minutes : minutes;

    return hours + ':' + minutes + ' ' + ampm;
}

function extractDate(timestamp)
{
    // Create a new Date object with the UTC time
    let date    = new Date(timestamp + 'Z');

    let year    = date.getUTCFullYear();
    let month   = date.getUTCMonth() + 1; // JavaScript counts months from 0 to 11, so we add 1
    let day     = date.getUTCDate();
    let dayName = dayNames[date.getUTCDay()]; // getUTCDay() returns the day of the week (from 0 to 6) for the specified date according to universal time

    // Add leading '0' to day
    if (day < 10)
        day = '0' + day;

    month = monthNames[month - 1];

    let parts = {
        'year': year,
        'month': month,
        'day': day,
        'dayName': dayName
    };

    return parts;
}

function getCurrentDateParts()
{
    let dateToday = new Date();
    let currentYear = dateToday.getFullYear();
    let currentMonth = monthNames[dateToday.getMonth()];
    let currentDay = (dateToday.getDate() < 10 ? '0' + dateToday.getDate() : dateToday.getDate());

    let parts = {
        'year': currentYear,
        'month': currentMonth,
        'day': currentDay
    };

    return parts;
}

// remove all empty strings from the array 
// and then concatenate the remaining elements with a separator.
function concat_ws(array, separator = ' ')
{
    let concat = array.filter(e => e.length).join(separator);
    return concat;
}

function nl2br(str) {
    return str.replace(/\r?\n/g, '<br>');
}

function isObjectEmpty(obj) {
    return Object.keys(obj).length === 0;
}