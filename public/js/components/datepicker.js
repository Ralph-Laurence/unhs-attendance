function to_datepicker(selector)
{
    let $input          = $(selector);
    let $instance       = null;
    let _defaultFormat  = 'mmmm dd, yyyy';
    let _format         = _defaultFormat;

    let _begin = function() 
    {
        if ($instance)
            $instance.destroy();

        $instance = $input.datepicker({
            format: _format
        });
    };

    _begin();

    // Setting the format after the instance was initialized,
    // needs to call _begin() to reinitialize
    let _setFormat = function(fmt)
    {
        _format = fmt;
        _begin();
    };

    // Value must be a valid date string i.e. ``` "08/01/2022" ```
    let _setValue = function(value)
    {
        $instance.value(value);
        $input.trigger('input');
    };

    let _open = function()
    {
        if (!$instance)
            return;

        $instance.open();
    };

    $input.on('click', () => _open());

    var _reset = function() 
    {
        let defaultValue = moment().format('MMMM DD, YYYY');

        // Trigger the input event to hide the textbox error
        $input.val( defaultValue ).trigger('input');
    };

    return {
        begin           : _begin,
        setFormat       : _setFormat,
        setValue        : _setValue,
        reset           : _reset,
        getInstance     : () => $instance,
        getInput        : () => $input,
        getType         : () => 'datepicker',
        getValue        : () => $instance.value(),
        show            : _open
    }
}