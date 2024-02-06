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
    });

    var __reset = function() 
    {
        var defaultValue = $input.data('default-value');

        $input.val( defaultValue ).trigger('input');

        __setSelected( root.find(`.dropdown-item[data-value="${defaultValue}"]`) );

        btnText.text( $input.data('default-text') );
    };

    var __setValue = function(value) 
    {
        $input.val(value).trigger('input');

        let target = root.find(`.dropdown-item[data-value="${value}"]`);

        __setSelected(target);

        btnText.text( target.text() );
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

    return {
        inputElem:  $input,
        reset:      __reset,
        setValue:   __setValue,
        setLastValue: (value) => lastValue = value,
        getLastValue: () => lastValue,
        onValueChanged: function(callback) 
        {
            $input.on('valueChanged', function(event, value) {
                // output the current value
                callback(value);
            });
        },
        getText:    () => btnText.text(),
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