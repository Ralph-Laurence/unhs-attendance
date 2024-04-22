//
// When autoShowOnFocus is set to FALSE, this will require the
// user to click on the datepicker toggle. Otherwise, when the
// input field was focused, it automatically opens the picker.
//
function to_datepicker(selector, autoShowOnFocus = true, editable = false)
{
    let $input          = $(selector);
    let $instance       = null;
    let _defaultFormat  = getCustomFormats('gijgo').ISO_8601;
    let _format         = _defaultFormat;
    let root            =  $input.closest('.datepicker-textbox');
    let isEditable      = editable;
    let toggleIcon      = root.find('.leading-icon');

    let _begin = function() 
    {
        if ($instance)
            $instance.destroy();

        $instance = $input.datepicker({
            format: _format,
            showOnFocus : autoShowOnFocus,
            change: () => $input.trigger('input'),
        });

        $input.attr('placeholder', _format.toUpperCase());

        if (!autoShowOnFocus)
            toggleIcon.addClass('hoverable-toggler');
        else
            toggleIcon.removeClass('hoverable-toggler');

        if (isEditable)
            _setEditable(true);
        else
            _setEditable(false);
    };

    // Setting the format after the instance was initialized,
    // needs to call _begin() to reinitialize
    let _setFormat = function(fmt)
    {
        _format = fmt;
        _begin();
    };

    let _showError = function(message) 
    {
        root.addClass('has-error');
    
        //if (typeof message === 'object' && message.length > 1)
        if ( Array.isArray(message) && message.length > 1 )
            root.find('.error-label').html( sanitize(message.join('<br><br>')) );
        else
            root.find('.error-label').text(message);
    };

    let _hideError = function() 
    {
        root.removeClass('has-error');
        root.find('.error-label').text('');
    };

    // Value must be a valid date string i.e. ``` "08/01/2022" ```
    let _setValue = function(value)
    {
        if (!_checkValidity(value))
            return;

        $instance.value(value);
        $input.trigger('input');
    };

    let _checkValidity = function(value)
    {
        // we need moment.js to parse the date value.
        // We also need to use the gijgo equivalent format supported by moment.
        // Both the object keys of gijgo and moment are similar, and so we can
        // safely use a gijgo key as moment key. These mapping of formats are
        // defined in utils.js
        var mnt = moment(value, _format.toUpperCase(), true);

        if (!mnt.isValid())
        {
            _showError('Date is invalid.');
            return false;
        }
        else
        {
            _hideError();
            return true;
        }
    }

    let _setEditable = function(canEdit)
    {
        if (canEdit)
        {
            isEditable = true;
            $input.attr('readonly', false);
            return;
        }

        isEditable = false;
        $input.attr('readonly', true);
    };

    let _open = function()
    {
        if (!$instance)
            return;

        $instance.open();
    };

    //$input.on('click', () => _open());

    toggleIcon.on('click', () => {

        if (isEditable)
            _open();
    });

    $input.on('change', function() 
    {
        // Do not allow non-numeric values except the dashes.
        var regexp = /[^0-9-]/g;
        $(this).val($(this).val().replace(regexp, ''));
        
        let value = $(this).val();
        _checkValidity(value);
        // setTimeout(() => {
            
        // }, 1200);
    });

    var _reset = function() 
    {
        let defaultValue = moment().format(_format.toUpperCase());

        // Trigger the input event to hide the textbox error
        $input.val( defaultValue ).trigger('input');
        _hideError();
    };

    _begin();

    return {
        begin           : _begin,
        setFormat       : _setFormat,
        getFormat       : () => _format,
        setValue        : (v) => _setValue(v),
        setEditable     : (e) => _setEditable(e),
        reset           : _reset,
        getInstance     : () => $instance,
        getInput        : () => $input,
        getType         : () => 'datepicker',
        getValue        : () => $instance.value(),
        show            : _open,
        showError       : (x) => _showError(x),
        hideError       : _hideError,
    }
}