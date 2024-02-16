$(document).ready(function () 
{
    // Force numeric input texts to accept only numbers 0-9
    $(".numeric").on("input", function () 
    {
        var regexp = /[^0-9]/g;
        $(this).val($(this).val().replace(regexp, ''));
    });

    // Force numeric-dash input texts to accept only numbers 0-9 and dashes
    $(".numeric-dash").on("input", function () 
    {
        var regexp = /[^0-9-]/g;
        $(this).val($(this).val().replace(regexp, ''));
    });

    // Force numeric input texts to accept only letters A-Z, spaces, dashes and dots
    $(".alpha-dash-dot").on("input", function () 
    {
        var regexp = /[^a-zA-Z0-9.-\s]/g;
        $(this).val($(this).val().replace(regexp, ''));
    });

    // Force email fields to accept only alphanumeric, @ and dot
    $(".email").on("input", function () 
    {
        var regexp = /[^a-zA-Z0-9.@]/g;
        $(this).val($(this).val().replace(regexp, ''));
    });

    $('.textbox').on('input', function() 
    {
        hideTextboxError($(this));
    });
});

function to_textbox(selector)
{
    let $input = $(selector);
    
    var __reset = function() 
    {
        let defaultValue = $input.data('default-value');
        // Trigger the input event to hide the textbox error
        $input.val( defaultValue ).trigger('input');
    };

    return {
        getInput:  ()  => $input,
        getType :  ()  => 'textbox',
        getValue:  ()  => $input.val(),
        setValue:  (v) => $input.val(v).trigger('input'),
        setText :  (t) => $input.val(t),
        reset   :  __reset
    }
}

function showTextboxError(target, message)
{
    var root = $(target).closest('.textbox');

    root.addClass('has-error');

    if (typeof message === 'object' && message.length > 1)
        root.find('.error-label').html( sanitize(message.join('<br><br>')) );
    else
        root.find('.error-label').text(message);

    // root.find('.error-label').text(message);
}

function hideTextboxError(target)
{
    var root = $(target).closest('.textbox');

    root.removeClass('has-error');
    root.find('.error-label').text('');
}