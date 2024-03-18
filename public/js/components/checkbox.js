function to_checkbox(selector)
{
    let $input = $(selector);

    let _check = function() {
        $input.prop('checked', true).trigger('change');
    }

    let _uncheck = function() {
        $input.prop('checked', false).trigger('change');
    };

    let _getValue = function() {
        return $input.prop('checked') ? 'on' : 'off';
    };

    let _setValue = function(value)
    {
        if (value == 'on')
            _check();
        else if (value == 'off')
            _uncheck();
    };
    
    let instance = {
        getInput : () => $input,
        getValue : _getValue,
        setValue : _setValue,
        getType  : () => 'checkbox',
        reset    : () => $input.prop('checked', false),
        check    : _check,
        uncheck  : _uncheck,
        enable   : () => $input.prop('disabled', false),
        disable  : () => $input.prop('disabled', true),
        changed  : null,
    };

    $input.on('change', function() {
        if (typeof instance.changed === 'function')
            instance.changed();
    });

    return instance;
}