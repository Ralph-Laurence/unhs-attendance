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
