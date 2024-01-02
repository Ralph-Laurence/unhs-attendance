function createRowActions(recordKey)
{
    var html = 
    `<div class="row-actions" data-record-key="${recordKey}">
        <div class="loader d-none"></div>
        <button class="btn btn-sm btn-about"> 
            <i class="fa-solid fa-circle-info"></i> 
        </button>
        <button class="btn btn-sm btn-edit"> 
            <i class="fa-solid fa-pen"></i> 
        </button>
        <button class="btn btn-sm btn-delete"> 
            <i class="fa-solid fa-trash"></i> 
        </button>
    </div>`;

    return html;
}

function showRowActionButtons(showButtons, container)
{
    if (!container)
        throw new Error('Unable to find parent container');

    if (showButtons === true)
        $(container).find('button').show();
    else
        $(container).find('button').hide();
}

function showRowActionSpinner(show, spinner)
{
    if (!spinner)
        return;

    $(spinner).toggleClass('d-none', !show);
}