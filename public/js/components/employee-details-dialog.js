function to_employeeDetailsDialog(selector)
{
    let el      = $(selector);
    let modal   = new mdb.Modal(el);
    
    let empKey  = el.find('.frm-view-dtr #employee-key');

    if (empKey.length < 1)
        return;//alert('cant find emp key');

    el.find('.btn-view-dtr').on('click', function() 
    {
        $(this).prop('disabled', true);

        let loader = $('.view-dtr-loader');

        loader.removeClass('d-hidden');

        if (!empKey.val())
        {
            close();
            $(this).prop('disabled', false);
            loader.addClass('d-hidden');

            __triggerError("[Security Error] Unable to view the record because some required information is missing. Please reload the page and try again later.");
            return;
        }

        el.find('.frm-view-dtr').trigger('submit');
    });

    let close = function () 
    {  
        modal.hide();
        empKey.val('');
    };

    let show = function(data) 
    {
        $.ajax({
            url     : el.data('src'),
            type    : 'POST',
            data    : {
                '_token': el.find('.frm-view-dtr .csrf').val(),
                'key'   : data.employeeKey
            },
            success: function(response) {

                if (!response)
                {
                    __triggerError('The server did not returned any data. Please try again later.', data.row);
                    return;
                }

                response = JSON.parse(response);

                if (response.code != 0)
                {
                    __triggerError(response.message, data.row);
                    return;
                }

                // Bind the response data into the frontend
                __presentData(response.dataset);
                empKey.val(data.employeeKey);

                modal.show();
            },
            error: function(xhr, status, error) {
                __triggerError('The requested action could not be performed because of an error. Please try again later.', data.row);
            },
            complete: function(response) 
            {
                var content = null;

                if (response.responseText)
                    content = response.responseText;

                if (typeof dialog.loadEnd === 'function')
                {
                    dialog.loadEnd({
                        content: content,
                        row    : data.row || null 
                    });
                }
            }
        });
    };

    function __triggerError(message, senderRow) 
    {
        if (typeof dialog.loadFailed === 'function')
        {
            dialog.loadFailed({
                message : message,
                row     : senderRow || null
            });
        }
    }

    function __presentData(jsonObj)
    {
        $(`${selector} #emp-details-name`).text(jsonObj.empname);
        $(`${selector} #emp-details-idno`).text(jsonObj.idNo);
        $(`${selector} #emp-details-email`).text(jsonObj.email);

        $(`${selector} #emp-details-status`).text(jsonObj.status);
        $(`${selector} #emp-details-contact`).text(jsonObj.phone);
    }

    let dialog = {
        'getInstance'   : () => modal,
        'show'          : show,
        'close'         : close,

        // Events
        'loadFailed'    : null, // When loading failed
        'loadEnd'       : null, // Loading has finished
    };

    return dialog;
}