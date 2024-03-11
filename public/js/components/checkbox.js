function to_checkbox(selector)
{
    let $input = $(selector);
    
    let instance = {
        getInput : () => $input,
        getType  : () => 'checkbox',
        getValue : () => $input.prop('checked') ? 'on' : 'off',
        reset    : () => $input.prop('checked', false),
        check    : () => $input.prop('checked', true).trigger('change'),
        uncheck  : () => $input.prop('checked', false).trigger('change'),
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