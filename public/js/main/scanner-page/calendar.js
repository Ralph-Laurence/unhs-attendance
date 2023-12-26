var x_calendar = {

    updateClock()
    {
        var date    = new Date();
        var hours   = date.getHours();
        var minutes = date.getMinutes();
        var seconds = date.getSeconds();

        // The Hour:Minutes time in 12-hour format
        var hoursMins = (hours % 12 || 12) + ':' + (minutes < 10 ? '0' : '') + minutes;

        // The Seconds
        var millisec  = ':' + (seconds < 10 ? '0' : '') + seconds;
    
        document.querySelector('.date-time-label .time-label .hour-minutes-label').textContent = hoursMins;
        document.querySelector('.date-time-label .time-label .millisec-label').textContent = millisec;
    },
    //
    // Scroll the calendar's texts to show between
    // current date in Three-Letter month format and
    // the name of the current day
    //
    animateCalendar()
    {
        let date     = moment();
        let dayToday = date.format('MMM. D, YYYY');    // The current date with 3-letter month
        let dayName  = date.format('dddd');
    
        let animation = new SlideText('.date-label');
    
        animation.items = [dayToday, dayName];
        animation.slideDelay = 4000;                  // Every 4 seconds
        animation.start();
    }
};