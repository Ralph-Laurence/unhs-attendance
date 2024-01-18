const selected_month_class = 'selected-month';
const selected_range_class = 'selected-option';

let rangeFilterEl;
let rangeFilterDropdown;

$(document).ready(function ()
{
    $('.month-select-dropmenu .month-select .month-item').on('click', function ()
    {
        let month = $(this).data('month');

        $('#selected-month-index').val(month).trigger('change');
        
        // Remove 'check' icon on previous checked items
        // then set the icon to the currently selected
        $('.month-select-dropmenu .month-select .month-item').removeClass(selected_month_class);
        $(this).addClass(selected_month_class);

        redrawSelectedRangeIcon($(this).closest('.month-range-dropstart').find('.dropdown-item'));
        setDropbuttonText($(this), 'By Month');
    });

    $('.record-range-filter .dropdown-item').on('click', function ()
    {
        // If the dropdown item is a submenu, do not add  a
        // check indicator to it unless its item was selected
        if ($(this).is('.with-submenu'))
            return;

        redrawSelectedRangeIcon($(this));
        setDropbuttonText($(this), $(this).data('button-text'));
    });

    rangeFilterEl = $('#record-date-dropdown-button');
    rangeFilterDropdown = new mdb.Dropdown(rangeFilterEl);
});

function redrawSelectedRangeIcon(sender)
{
    $('.record-range-filter .dropdown-item').removeClass(selected_range_class);
    sender.addClass(selected_range_class);
}

function setDropbuttonText(sender, text) 
{
    sender.closest('.record-range-dropdown')
        .find('#record-date-dropdown-button .button-text')
        .text(text);
}

function finishRangeFilter()
{
    rangeFilterDropdown.hide();
    rangeFilterEl.prop('disabled', true);

}

function enableRangeFilter() {  
    rangeFilterEl.prop('disabled', false);
}