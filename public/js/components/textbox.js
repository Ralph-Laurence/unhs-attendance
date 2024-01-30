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
});

function showTextboxError(target, message)
{
    var root = $(target).closest('.textbox');

    root.addClass('has-error');
    root.find('.error-label').text(message);
}

function hideTextboxError(target)
{
    var root = $(target).closest('.textbox');

    root.removeClass('has-error');
    root.find('.error-label').text('');
}