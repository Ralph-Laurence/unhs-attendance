function to_droplist(selector) 
{  
    let $input = $(selector);

    let root    = $input.closest('.dropdown');
    let btnText = root.find('.dropdown-toggle .button-text');
    let items   = root.find('.dropdown-menu .dropdown-item');

    items.on('click', function()
    {
        items.removeClass('selected');

        let value = $(this).data('value');
        $input.val(value).trigger('input');
        
        $(this).addClass('selected');

        btnText.text( $(this).text() );
    });

    //
    // Hide error labels when interacted
    //
    $input.on('input', function()
    {
        hideDroplistError($(this));

        $(this).trigger('valueChanged', $(this).val());
    });

    return {
        inputElem: $input,
        onValueChanged: function(callback) 
        {
            $input.on('valueChanged', function(event, value) {
                // output the current value
                callback(value);
            });
        },
        getValue:   () => $input.val(),
        enable:     () => root.find('.dropdown-toggle').prop('disabled' , false),
        disable:    () => root.find('.dropdown-toggle').prop('disabled' , true),
    };
};

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