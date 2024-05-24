function to_tablesearcher(dropstart, dt_field)
{  
    let root        = $(dropstart);
    let $dtInput    = $(dt_field);
    let $btnFind    = root.find("#btn-finder-search");
    let $btnClear   = root.find("#btn-finder-clear");
    let $btnClose   = root.find("#btn-close-search");
    let $searchbar  = to_textbox(root.find("#search-bar"));

    let instance    = new mdb.Dropdown(root.find('.btn-finder-search'));

    let _clear = function() {
        $dtInput.val('').trigger('keyup');
        $searchbar.reset();
    };

    $btnFind.on('click', function() 
    {
        let find = $searchbar.getValue();
        
        if (find)
            $dtInput.val(find).trigger('keyup');
    });

    $btnClear.on('click', () => _clear());
    
    $btnClose.on('click', function() 
    {
        _clear();
        instance.hide();
    });
}