const selected_month_class = 'selected-month';
const selected_range_class = 'selected-option';

$(document).ready(function ()
{
    $('.month-select-dropmenu .month-select .month-item').on('click', function ()
    {
        let month = $(this).data('month');

        $('#selected-month-index').val(month).trigger('change');
        
        $('.month-select-dropmenu .month-select .month-item').removeClass(selected_month_class);
        $(this).addClass(selected_month_class);

        redrawSelectedRangeIcon($(this).closest('.month-range-dropstart').find('.dropdown-item'));

        $(this).closest('.record-range-dropdown')
               .find('#record-date-dropdown-button .button-text')
               .text('By Month');
    });

    $('.record-range-filter .dropdown-item').on('click', function ()
    {
        if ($(this).is('.with-submenu'))
            return;

        redrawSelectedRangeIcon($(this));

        $(this).closest('.record-range-dropdown')
               .find('#record-date-dropdown-button .button-text')
               .text($(this).data('button-text'));
    });
});

function redrawSelectedRangeIcon(sender)
{
    $('.record-range-filter .dropdown-item').removeClass(selected_range_class);
    sender.addClass(selected_range_class);
}