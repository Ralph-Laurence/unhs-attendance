function to_employeeDetailsDialog(selector)
{
    let el              = $(selector);
    let modal           = new mdb.Modal(el);
    
    let empKey          = el.find('.frm-view-dtr #employee-key');
    let btnSaveQR       = el.find('#btn-save-qr');
    let btnSendQR       = el.find('#btn-send-qr');
    let sendQrProgress  = to_indefmeter('#send-qr-progress', 0);
    let closeButtons    = el.find('.close-button');

    let carousel = new mdb.Carousel(document.querySelector(`${selector} #employee-details-carousel`), {
        'interval' : false,
        'keyboard' : false,
        'touch'    : false
    });

    if (empKey.length < 1)
        return;

    el.on('hidden.mdb.modal', () => {
        carousel.to(0);
        toggleCloseButtons(true);
        toggleDownloadButton(true);
    });

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
                __presentData(response.dataset, data.employeeKey);
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

    function __emailSendFail(message) 
    {
        close();

        if (typeof dialog.emailSendFail === 'function')
        {
            dialog.emailSendFail(message);
        }
    }

    function __presentData(jsonObj, empKey)
    {
        $(`${selector} #emp-details-name`).text(jsonObj.empname);
        $(`${selector} #emp-details-idno`).text(jsonObj.idNo);
        $(`${selector} #emp-details-email`).text(jsonObj.email);

        $(`${selector} #emp-details-rank`).text(jsonObj.rank);
        $(`${selector} #emp-details-status`).text(jsonObj.status);
        $(`${selector} #emp-details-contact`).text(jsonObj.phone);

        if (jsonObj.qrCode)
        {
            if (jsonObj.qrCode == '404')
            {
                toggleDownloadButton(false);
                return;
            }

            toggleDownloadButton(true);

            $(`${selector} #emp-details-qrcode`).attr('src', jsonObj.qrCode);

            btnSaveQR.off('click').on('click', () => 
            {
                toggleDownloadButton(false);
                beginDownload(jsonObj);

                setTimeout(() => {
                    toggleDownloadButton(true); 
                }, 2000);
            });

            btnSendQR.off('click')
                     .on('click', () => sendQrCode(empKey, jsonObj.qrCodeResend) );
        }

        sendQrProgress.reset();
    }

    function toggleDownloadButton(toggle) 
    {
        if (!toggle)
            btnSaveQR.prop('disabled', true);
        else
            btnSaveQR.removeAttr('disabled');
    }

    function beginDownload(data)
    {
        var a = document.createElement('a');

        a.href = data.qrBlob;
        a.download = data.qrFile;

        document.body.appendChild(a);

        a.click();  // Calling click() initiates the download
        a.remove(); // Remove the element from the body
    }

    function sendQrCode(employeeKey, url)
    {
        toggleCloseButtons(false);
        btnSendQR.prop('disabled', true);
        sendQrProgress.setProgress(100, 'Sending QR Code... Please wait.');

        $.ajax({
            url     : url,
            type    : 'POST',
            data    : {
                '_token': el.find('.frm-view-dtr .csrf').val(),
                'key'   : employeeKey
            },
            success: function(response) 
            {
                if (!response)
                {
                    __emailSendFail('TThe mail server did not respond. Please reload the page.');
                    return;
                }

                response = JSON.parse(response);
                
                if (response.code == -1)
                {
                    __emailSendFail(response.message);
                    return;
                }

                if (typeof dialog.emailSendOK === 'function')
                    dialog.emailSendOK('QR Code successfully sent thru email.');
            },
            error: function(xhr, status, error) 
            {
                close();
                __triggerError('The requested action could not be performed because of an error. Please try again later.');
            },
            complete: function(response) 
            {
                sendQrProgress.reset();

                toggleCloseButtons(true);
                btnSendQR.prop('disabled', false);
            }
        });
    }

    function toggleCloseButtons(toggle)
    {
        closeButtons.prop('disabled', !toggle);
    }

    let dialog = {
        'getInstance'   : () => modal,
        'show'          : show,
        'close'         : close,

        // Events
        'loadFailed'    : null, // When loading failed
        'loadEnd'       : null, // Loading has finished
        'emailSendFail' : null,
        'emailSendOK'   : null,
    };

    return dialog;
}