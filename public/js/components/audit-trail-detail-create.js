let sbar_cre;

function to_auditTrailsDetailsCreate(selector)
{
    let el = $(selector);
    let modal = new mdb.Modal(document.querySelector(selector));
    let tbody = el.find('.audit-details-table tbody');

    sbar = $(`${selector} .simplebar-content-wrapper`);

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

        // Parse the values
        var lastValues = JSON.parse(dataset.new_values);

        $.each(lastValues, function (field, value)
        {
            tbody.append(`
                <tr>
                    <td>${field}</td>
                    <td class="text-center">${value}</td>
                </tr>
            `);
        });
    };

    return {
        'presentData' : __presentData,
        'show'        : () => modal.show(),
        'close'       : () => modal.hide()
    }
}