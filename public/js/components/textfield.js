const TextFieldTypes = {
    TYPE_NUMERIC : 'numeric',
    TYPE_NUMDASH : 'numeric-dash',
    TYPE_BASIC   : 'basic',
    TYPE_EMAIL   : 'email',
};

function to_textfield(selector, type)
{
    type = type || 'text';

    let $input = $(selector);
    let root   =  $input.closest('.textbox');

    let __showError = function(message) 
    {
        root.addClass('has-error');
    
        //if (typeof message === 'object' && message.length > 1)
        if ( Array.isArray(message) && message.length > 1 )
            root.find('.error-label').html( sanitize(message.join('<br><br>')) );
        else
            root.find('.error-label').text(message);
    };

    let __hideError = function() 
    {
        root.removeClass('has-error');
        root.find('.error-label').text('');
    };

    var __reset = function() 
    {
        __hideError();

        let newValue = $input.data('default-value') || '';

        $input.val( newValue );
    };

    let instance = {
    
        getInput     : ()  => $input,
        getType      : ()  => 'textfield',
        getValue     : ()  => $input.val(),
        setValue     : (v) => $input.val(v).trigger('input'),
        setText      : (t) => $input.val(t),
        showError    : (x) => __showError(x),
        hideError    : __hideError,
        reset        : __reset,
        changed      : null,
    };

    let typeHandlers = {
        
        // Accept only numbers 0-9
        [TextFieldTypes.TYPE_NUMERIC]  : (input) => input.val( input.val().replace(/[^0-9]/g, '') ),

        // Accept only numbers 0-9 and dashes
        [TextFieldTypes.TYPE_NUMDASH]  : (input) => input.val( input.val().replace(/[^0-9-]/g, '') ),

        // Accept only letters A-Z, spaces, dashes and dots
        [TextFieldTypes.TYPE_BASIC]    : (input) => input.val( input.val().replace(/[^a-zA-Z0-9.-\s]/g, '') ),

        // Accept only alphanumeric, @ and dot
        [TextFieldTypes.TYPE_EMAIL]    : (input) => input.val( input.val().replace(/[^a-zA-Z0-9.@]/g, '') )
    };

    $input.on('input', function() 
    {
        __hideError();  // Always hide the error box when interacted

        // Apply input filter
        if (type in typeHandlers)
            typeHandlers[type]( $input );

        if (typeof instance.changed === 'function')
            instance.changed();
    });
   
    return instance;
}