function to_timepicker(selector)
{
    let $input          = $(selector);
    let _defaultFormat  = 'hh:MM tt';
    let _format         = _defaultFormat;
    let $instance       = null;

    let _begin = function() 
    {
        if ($instance)
            $instance.destroy();

        $instance = $input.timepicker({
            format: _format,
        });
    };

    // Setting the format after the instance was initialized,
    // needs to call _begin() to reinitialize
    let _setFormat = function(fmt)
    {
        _format = fmt;
        _begin();
    };

    // Value must be a valid time string i.e. ``` "11:00" ```
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
        let defaultValue = moment().format('hh:mm a');

        // Trigger the input event to hide the textbox error
        $input.val( defaultValue ).trigger('input');
    };

    let _toggle = function(enable)
    {
        $input.prop('disabled', !enable);
    };

    _begin();

    return {
        begin           : _begin,
        setFormat       : _setFormat,
        setValue        : _setValue,
        reset           : _reset,
        getInstance     : () => $instance,
        getInput        : () => $input,
        getType         : () => 'timepicker',
        getValue        : () => $instance.value(),
        disable         : () => _toggle(false),
        enable          : () => _toggle(true),
        show            : _open
    }
}