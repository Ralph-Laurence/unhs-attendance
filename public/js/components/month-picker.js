// https://bootstrap-datepicker.readthedocs.io/en/latest/methods.html
function to_monthpicker(selector, autoclose) 
{
    autoclose = autoclose || true;

    let value;
    let $parent   = $(selector);
    let $input    = $parent.find('.main-control');

    let container = `${$parent.prop('nodeName').toLowerCase()}#${$parent.attr('id')}`;
     
    // Checks a date string if it meets the format 'mm/yyyy'.
    // This also checks for the validity of the year and months.
    function isValidFormat(dateString)
    {
        // Case 1 : Format checking
        var regex = /^(0[1-9]|1[0-2])\/\d{4}$/;

        if (!regex.test(dateString))
            return false;

        // Case 2 : Validity Checking
        var parts = dateString.split('/');
        var monthNumber = parseInt(parts[0], 10);
        var year = parseInt(parts[1], 10);

        if (monthNumber < 1 || monthNumber > 12)
            return false;

        if (year < 1)
            return false;

        return true;
    }

    function _getMonthName(monthNumber, year, format)
    {
        var date = new Date(year, monthNumber - 1);
        return date.toLocaleString('default', { month: format });
    }

    function _getMonthNameFromDate(dateString, format)
    {
        var parts = dateString.split('/');
        var monthNumber = parseInt(parts[0], 10);
        var year = parseInt(parts[1], 10);
        return _getMonthName(monthNumber, year, format);
    }

    // Translate month index to string name.
    // Default translation is Three-letter month
    let __translate = function(translation) 
    {
        translation = translation || 'M';

        if (!isValidFormat(value)) 
            return '';

        if (translation == 'F')
            return _getMonthNameFromDate(value, 'long');

        return _getMonthNameFromDate(value, 'short')
    }

    let triggerBtn = $parent.find(`${selector}-trigger`);
    let picker     = $input.datepicker({
        startView   : 'month',
        minViewMode : 'months',
        container   : container
    }); 

    let bsdp = {
        'getInstance' : () => picker,
        'getValue'    : () => value,
        'setValue'    : (v) => value = v,
        'translate'   : (t) => __translate(t),

        'reset' : () => {
            //picker.datepicker('setDate', 'now');
            picker.datepicker('setDate', 'null');
            value = '';
            triggerBtn.text('Select Month');
        },

        'show'    : () => picker.datepicker('show'),
        'close'   : () => picker.datepicker('hide'),
        'changed' : null
    };

    triggerBtn.on('click', () => picker.datepicker('show'));

    picker.on('changeMonth', function(e) 
    {
        if (autoclose === true)
            bsdp.close();
        
        let date  = e.date;

        if (!e.date)    
            return;

        let month = date.getMonth() + 1; // getMonth() returns month index starting from 0
        let year  = date.getFullYear();
        let formattedDate = (month < 10 ? '0' : '') + month + '/' + year; // add leading zero to month if necessary

        let info = {
            'monthIndex'  : date.getMonth(),
            'monthNumber' : month,
            'year'        : year,
            'formatted'   : formattedDate
        };

        if (typeof bsdp.changed === 'function')
            bsdp.changed(info);

        bsdp.setValue(formattedDate);
        bsdp.close();
        
        triggerBtn.text(__translate('F'));
    });

    return bsdp;
}