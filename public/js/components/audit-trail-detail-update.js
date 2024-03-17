function to_auditTrailsDetailsUpdate(selector)
{
    let el = $(selector);
    let modal = new mdb.Modal(document.querySelector(selector));
    let tbody = el.find('.audit-details-table tbody');

    let sbar = $(`${selector} .simplebar-content-wrapper`);

    let descriptionText = el.find('.description');
    let descUser  = descriptionText.find('.user');
    let descDate  = descriptionText.find('.date');
    let descTime  = descriptionText.find('.time');
    let descModel = descriptionText.find('.affected');

    let tracingText = el.find('.tracing-details');
    let userAgent   = tracingText.find('.user-agent');
    let url         = tracingText.find('.url');
    let ip          = tracingText.find('.ip');

    el.on('hide.bs.modal', () => sbar.scrollTop(0));

    let __presentData = function(dataset)
    {
        tbody.empty();

        descUser.text(dataset.user);
        descDate.text(dataset.date);
        descTime.text(dataset.time);
        descModel.text(dataset.affected);

        userAgent.text(dataset.ua);
        url.text(dataset.url);
        ip.text(dataset.ip);

        // Parse the old and new values
        var oldValues = JSON.parse(dataset.old_values);
        var newValues = JSON.parse(dataset.new_values);

        // For each field in oldValues
        $.each(oldValues, function (field, oldValue)
        {
            // Get the corresponding new value
            let newValue = newValues[field];

            // Create a new row
            let row = $('<tr>').append(
                $('<td>').text(field),
                $('<td class="text-center">').text(oldValue),
                $('<td class="text-center">').text(newValue)
            );

            // Append the new row to the table body
            tbody.append(row);
        });
    };

    return {
        'presentData' : __presentData,
        'show'        : () => modal.show(),
        'close'       : () => modal.hide()
    }
}