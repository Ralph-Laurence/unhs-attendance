function to_droplist(selector) 
{  
    let $input = $(selector);

    let root    = $input.closest('.dropdown');
    let btnText = root.find('.dropdown-toggle .button-text');
    let items   = root.find('.dropdown-menu .dropdown-item');
    
    let lastValue = '';

    items.on('click', function()
    {
        let value = $(this).data('value');
        $input.val(value).trigger('input');
        
        __setSelected( $(this) );

        btnText.text( $(this).text() );
    });

    //
    // Hide error labels when interacted
    //
    $input.on('input', function()
    {
        hideDroplistError($(this));

        $(this).trigger('valueChanged', $(this).val());
        
        if (typeof instance.changed === 'function')
            instance.changed();
    });

    var __reset = function() 
    {
        var defaultValue = $input.data('default-value');

        $input.val( defaultValue ).trigger('input');

        __setSelected( root.find(`.dropdown-item[data-value="${defaultValue}"]`) );

        btnText.text( $input.data('default-text') );
    };

    var __setValue = function(value, triggerEvent) 
    {
        $input.val(value);

        triggerEvent = triggerEvent || true;

        if (triggerEvent !== false)
            $input.trigger('input');

        let target = root.find(`.dropdown-item[data-value="${value}"]`);

        __setSelected(target);

        btnText.text( target.text() );
    };

    // Set a value without triggerring the input event.
    // This also sets the value from the default
    var __setValueSilent = function(value)
    {
        if (!value)
            __setValue( $input.data('default-value'), false );
        else
            __setValue(value, false);

        hideDroplistError( $input );
    };

    //
    // Reset all selected elements (remove their class)
    // Then re-assign it to a new target class
    //
    function __setSelected(target) 
    {
        items.removeClass('selected');
        target.addClass('selected');
    }

    function __hideError()
    {
        root.find('.error-label').text('');
    }

    function __showError(message)
    {
        // if (typeof message === 'object' && message.length > 1)
        if ( Array.isArray(message) && message.length > 1 )
            root.find('.error-label').html(sanitize(message.join('<br><br>')));
        else
            root.find('.error-label').text(message);
    }

    let instance = {
        getInput:  () => $input,
        getType :  () => 'droplist',
        reset   :   __reset,
        setValue:   __setValue,
        onValueChanged: function(callback) 
        {
            $input.on('valueChanged', function(event, value) {
                // output the current value
                callback(value);
            });
        },
        changed :     null,
        pushHistory:  (value) => lastValue = value,
        pullHistory:  () => __setValueSilent( lastValue ),
        getText:      () => btnText.text(),
        getValue:     () => $input.val(),
        enable:       () => root.find('.dropdown-toggle').prop('disabled' , false),
        disable:      () => root.find('.dropdown-toggle').prop('disabled' , true),

        hideError :  __hideError,
        showError :  __showError
    };

    return instance;
};

function showDroplistError(target, message)
{
    var root = $(target).closest('.dropdown');

    // if (typeof message === 'object' && message.length > 1)
    if ( Array.isArray(message) && message.length > 1 )
        root.find('.error-label').html( sanitize(message.join('<br><br>')) );
    else
        root.find('.error-label').text(message);
}

function hideDroplistError(target)
{
    var root = $(target).closest('.dropdown');

    //root.removeClass('has-error');
    root.find('.error-label').text('');
}