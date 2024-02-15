const ROW_ACTION_INFO    = 'info';
const ROW_ACTION_EDIT    = 'edit';
const ROW_ACTION_DELETE  = 'delete';
const ROW_ACTION_APPROVE = 'approve';
const ROW_ACTION_REJECT  = 'reject';

const ROW_ACTION_BUTTONS = 
{
    [ROW_ACTION_INFO]    : `<button class="btn btn-sm btn-details"><i class="fa-solid fa-circle-info"></i></button>`,
    [ROW_ACTION_EDIT]    : `<button class="btn btn-sm btn-edit"><i class="fa-solid fa-pen"></i></button>`,
    [ROW_ACTION_DELETE]  : `<button class="btn btn-sm btn-delete"><i class="fa-solid fa-trash"></i></button>`,
    [ROW_ACTION_APPROVE] : `<button class="btn btn-sm btn-approve"><i class="fa-solid fa-thumbs-up"></i></button>`,
    [ROW_ACTION_REJECT]  : `<button class="btn btn-sm btn-reject"><i class="fa-solid fa-thumbs-down"></i></button>`
};

function makeRowActionButtons(recordKey, actions)
{
    var html = 
    `<div class="row-actions" data-record-key="${recordKey}">
        <div class="loader d-none"></div>
        {-actions-}
    </div>`;

    // Fallback Action Buttons
    actions = actions || [ROW_ACTION_INFO, ROW_ACTION_EDIT, ROW_ACTION_DELETE];
    
    let actionButtons = [];

    actions.forEach(a => {

        if (a in ROW_ACTION_BUTTONS)
        {
            let button = ROW_ACTION_BUTTONS[a];
            actionButtons.push(button);
        }
    });

    html = html.replace(/{-actions-}/g, actionButtons.join(''));

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

function processRowActions(row)
{
    let rowActionsDiv = row.find('.row-actions');
    let rowKey        = rowActionsDiv.data('record-key');
    let spinner       = rowActionsDiv.find('.loader');

    return {
        getRowKey   : () => rowKey,
        begin       : () => {
            showRowActionSpinner(true, spinner);
            showRowActionButtons(false, rowActionsDiv);
        },
        end         : () => {
            showRowActionSpinner(false, spinner);
            showRowActionButtons(true, rowActionsDiv);
        }
    };
}

// Getting Obsolete
function createRowActions(recordKey)
{
    var html = 
    `<div class="row-actions" data-record-key="${recordKey}">
        <div class="loader d-none"></div>
        <button class="btn btn-sm btn-details"> 
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

function createRowDeleteAction(recordKey)
{
    var html = 
    `<div class="row-actions" data-record-key="${recordKey}">
        <div class="loader d-none"></div>
        <button class="btn btn-sm btn-delete"> 
            <i class="fa-solid fa-trash"></i> 
        </button>
    </div>`;

    return html;
}

// Re assign the row numbers. Default column is 0
function updateRowEntryNumbers(apiInstance, columnIndex)
{
    columnIndex = columnIndex || 0;
    
    var startIndex = apiInstance.context[0]._iDisplayStart;

    apiInstance.column(columnIndex, {page: 'current'}).nodes().each( 
        (cell, i) => cell.innerHTML = startIndex + i + 1 
    );
}

// Re draw the data table after modifications. By default,
// it returns to the first page when redrawing.
function redrawTable(dataTable, retainPage)
{
    retainPage = retainPage || false;

    if (retainPage === false)
    {
        dataTable.draw();
        return;
    }

    var info = dataTable.page.info();

    var recordsInCurrentPage = info.recordsTotal;
    var currentPageNumber    = info.page;
    var currentPageLength    = info.length;
 
    // if ( currentPageNumber > 0 && (recordsInCurrentPage - 1) > (currentPageNumber * currentPageLength) )
    if ( currentPageNumber > 0 && (recordsInCurrentPage > (currentPageNumber * currentPageLength) ))
        // Redraw current page
        dataTable.draw(false);
    else
        // There are no more records in current page,
        // so we go back up one page
        dataTable.page('previous').draw('page');
}

function flashRow(rowNode, onAnimationEnd)
{
    let node = $(rowNode);

    node.addClass('row-flash');
    node.on('animationend', function ()
    {
        // Remove the class after the animation ends
        node.removeClass('row-flash');

        if (onAnimationEnd && typeof onAnimationEnd === 'function')
            onAnimationEnd();
    });
}