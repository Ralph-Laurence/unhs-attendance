function to_droplist(selector) 
{  
    let $input = $(selector);

    let root    = $input.closest('.dropdown');
    let btnText = root.find('.dropdown-toggle .button-text');

    root.find('.dropdown-menu .dropdown-item').on('click', function()
    {
        let value = $(this).data('value');
        $input.val(value).trigger('input');

        btnText.text( $(this).text() );
    });

    //
    // Hide error labels when interacted
    //
    $input.on('input', function()
    {
        hideDroplistError($(this));
    });
}

function showDroplistError(target, message)
{
    var root = $(target).closest('.dropdown');

    //root.addClass('has-error');
    root.find('.error-label').text(message);
}

function hideDroplistError(target)
{
    var root = $(target).closest('.dropdown');

    //root.removeClass('has-error');
    root.find('.error-label').text('');
}