let MIN_YEAR_OFFSET = 2;
let MAX_YEARS_ALLOWED = 8;

function to_date_picker(selector) 
{
    // Select the input element as JQUERY object using the provided selector
    var $input          = $(selector);
    var $rootDiv        = $input.closest('.moment-picker-textbox');
    var $mainPopover    = $rootDiv.find('.moment-picker-popover');
    var bsDropdown      = new mdb.Dropdown($input.closest('.dropdown-toggle'));

    // Create Month and Year select options
    var $monthSelect    = $mainPopover.find('.month-select');
    var $yearSelect     = $mainPopover.find('.year-select');

    // Create the calendar table
    var $table          = $mainPopover.find('.day-table');
    var $tbody          = $table.find('tbody');

    var monthPickerBtn  = $mainPopover.find('.control-ribbon .btn-month-picker');
    var monthPicker     = $mainPopover.find('.month-picker');

    var yearPickerBtn   = $mainPopover.find('.control-ribbon .btn-year-picker');
    var yearPicker      = $mainPopover.find('.year-picker');

    var dayPicker       = $mainPopover.find('.day-picker');

    var className_currentMonth = 'current-month';
    var className_currentYear  = 'current-year';
    var className_currentDay   = 'current-day';

    // When either the month select or year select changes,
    // update the calendar table
    $monthSelect.add($yearSelect).on('change', updateDaysTable);
    updateDaysTable();

    function updateDaysTable() 
    {
        // Clear the table's body
        $tbody.empty();

        var month = parseInt($monthSelect.val());
        var year  = parseInt($yearSelect.val(), 10);

        console.warn(`Month = ${month}; Year = ${year}`);

        var firstDay = moment([year, month]);
        var lastDay  = moment(firstDay).endOf('month');
        var day      = moment(firstDay).startOf('week');

        while (day <= lastDay || day.day() !== 0) 
        {
            var $tr = $('<tr>');

            for (var i = 0; i < 7; i++) 
            {
                var $td   = $('<td>');
                var $span = $('<span>').addClass('day-item').text(day.date()).attr('data-day', day.date());

                // Disable those days that don't belong to current month
                // such as the days of next and previous months but still
                // visible on the picker calendar
                if (day.month() !== month)
                {
                    $span.addClass('disabled')
                }
                else
                {   
                    $span.on('click', function () 
                    {
                        applyValue(year, month, $(this).text());
                        bsDropdown.hide();
                    });
                }
                $tr.append($td.append($span));
                day.add(1, 'day');
            }

            $tbody.append($tr);

            resetPickerDisplay();
        }
    }

    function resetPickerDisplay()
    {
        monthPicker.hide();
        yearPicker.hide();
        dayPicker.show();

        monthPickerBtn.show();
        yearPickerBtn.show();
    }
    //
    // Remove the marker color of selected month
    //
    function unmarkSelectedMonth()
    {
        var elements = $mainPopover.find('.month-picker .months-table .month-item');
        elements.removeClass(className_currentMonth);

        return elements;
    }
    //
    // Remove the marker color of selected year
    //
    function unmarkSelectedYear()
    {
        var elements = $mainPopover.find('.year-picker .years-table .year-item');
        elements.removeClass(className_currentYear);

        return elements;
    }
    //
    // Remove the marker color of selected day
    //
    function unmarkSelectedDay()
    {
        $mainPopover.find('.day-picker .day-table .day-item')
                    .removeClass(className_currentDay);
    }

    function applyValue(year, month, day)
    {
        // Update the input field with the selected date in the format 'Y-m-d'
        $input.val(year + '-' + (month + 1)
            .toString().padStart(2, '0') + '-' + day.padStart(2, '0'));
    }

    $rootDiv.on('show.bs.dropdown', function()
    {
        resetPickerDisplay();

        // Get the existing input date value.
        // Extract the date parts then apply it onto picker
        // If no existing date or is invalid, use current date.
        var date = moment($input.val(), "YYYY-MM-DD");

        if (!date.isValid())
        {
            var currentDate = moment();

            $yearSelect.val(currentDate.year()).trigger('change');
            $monthSelect.val(currentDate.month()).trigger('change');

            // Change the marked selected month
            var resetSelectedMonth = unmarkSelectedMonth();
            var targetMonthItem = resetSelectedMonth.filter(`[data-month="${currentDate.month()}"]`);

            targetMonthItem.addClass(className_currentMonth);
            monthPickerBtn.find('.btn-text').text(currentDate.format('MMM'));

            // Change the marked selected year
            var resetSelectedYear = unmarkSelectedYear();
            var targetYearItem = resetSelectedYear.filter(`[data-year="${currentDate.year()}"]`);

            targetYearItem.addClass(className_currentYear);
            yearPickerBtn.find('.btn-text').text(currentDate.year());

            dayPicker.find(`.day-item[data-day="${currentDate.date()}"]`).addClass('current-day');

            return;
        }

        $monthSelect.val(date.month()).trigger('change');
        $yearSelect.val(date.year()).trigger('change');

        // Change the marked selected month
        var resetSelectedMonth = unmarkSelectedMonth();
        var targetMonthItem = resetSelectedMonth.filter(`[data-month="${date.month()}"]`);
        
        targetMonthItem.addClass(className_currentMonth);
        monthPickerBtn.find('.btn-text').text(targetMonthItem.data('month-name'));

        // Change the marked selected year
        var resetSelectedYear = unmarkSelectedYear();
        var targetYearItem = resetSelectedYear.filter(`[data-year="${date.year()}"]`);

        targetYearItem.addClass(className_currentYear);
        yearPickerBtn.find('.btn-text').text(targetYearItem.data('year'));

        // Change the marked selected day
        unmarkSelectedDay();
        var _dayPicker = dayPicker.find(`.day-item[data-day="${date.date()}"]:not(.disabled)`);
        _dayPicker.addClass(className_currentDay);
    });

    monthPickerBtn.on('click', function()
    {
        dayPicker.hide();
        yearPicker.hide();
        monthPicker.show();
    });

    yearPickerBtn.on('click', function()
    {
        dayPicker.hide();
        monthPicker.hide();
        yearPicker.show();
    });
    //
    // Month Items on clicked
    // 
    $mainPopover.find('.month-picker .months-table .month-item').on('click', function() 
    {
        $input.val('');

        var dataMonth = $(this).data('month');
        $monthSelect.val(dataMonth).trigger('change');
        monthPickerBtn.find('.btn-text').text($(this).data('month-name'));

        unmarkSelectedMonth();
        $(this).addClass(className_currentMonth);
    });
    //
    // Year Items on clicked
    // 
    $mainPopover.find('.year-picker .years-table .year-item').on('click', function() 
    {
        $input.val('');

        var dataYear = $(this).data('year');
        $yearSelect.val(dataYear).trigger('change');
        yearPickerBtn.find('.btn-text').text(dataYear);

        unmarkSelectedYear();
        $(this).addClass(className_currentYear);
    });
}
