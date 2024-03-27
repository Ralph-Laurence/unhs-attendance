function to_lengthpager(selector, datatable) 
{  
    let $input = $(selector);

    let root    = $input.closest('.dropdown');
    let btnText = root.find('.dropdown-toggle .button-text');
    let items   = root.find('.dropdown-menu .dropdown-item');
    let lastValue = '';
    let instance = {
        changed    :  null,
        getType    :  () => 'pagelength',
        getValue   :  () => lastValue,
        setValue   :  setValue,
        enable     :  enable,
        disable    :  disable,
    };
    
    let dtable;

    if (datatable instanceof $.fn.dataTable.Api)
    {
        dtable = datatable;
        instance.setValue(dtable.page.len(), false);
    }
    else
        dtable = null;

    items.on('click', function()
    {
        lastValue = $(this).data('value');
        
        setSelected( $(this) );

        btnText.text( $(this).text() );

        setPageLength(lastValue);

        if (typeof instance.changed === 'function')
            instance.changed(lastValue);
    });

    function setPageLength(length)
    {
        if (!dtable) return;

        // Change the page length
        dtable.page.len(length).draw();
    }
    //
    // Reset all selected elements (remove their class)
    // Then re-assign it to a new target class
    //
    function setSelected(target) 
    {
        items.removeClass('selected');
        target.addClass('selected');
    }

    function setValue(value, forceRedrawTable)
    {
        forceRedrawTable = forceRedrawTable || false;

        lastValue = value;

        let target = root.find(`.dropdown-item[data-value="${value}"]`);

        setSelected(target);

        btnText.text( target.text() );

        if (forceRedrawTable)
            setPageLength(lastValue);
        
        if (typeof instance.changed === 'function')
            instance.changed(value);
    }

    function enable() {
        root.find('.dropdown-toggle').prop('disabled' , false);
    }

    function disable() {
        root.find('.dropdown-toggle').prop('disabled' , true);
    }

    return instance;
};