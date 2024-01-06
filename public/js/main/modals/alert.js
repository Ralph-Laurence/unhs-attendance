var alertModal = {
    
    iconDataset: document.querySelector('#alertModal .modal-icon').dataset,
    //
    // Modal Appearance by Type
    //
    getTypeConfig: function() {
        return {
            'info':     { btnClass: 'btn-primary',  defaultTitle: 'Information', icon: this.iconDataset.iconDefault },
            'danger':   { btnClass: 'btn-danger',   defaultTitle: 'Failure'    , icon: this.iconDataset.iconFailure },
            'warn':     { btnClass: 'btn-warning',  defaultTitle: 'Warning'    , icon: this.iconDataset.iconWarning },
            'default':  { btnClass: 'btn-primary',  defaultTitle: 'Notice'     , icon: this.iconDataset.iconDefault }
        };
    },
    //
    // Powered by Bootstrap's Modal class; We will apply its custom appearance and behaviours
    //
    show: function (message, title, okClicked = function () { }, cancelClicked = function () { }, type) 
    {
        var config = this.getTypeConfig()[type] || this.getTypeConfig().default;

        // Use the provided title if it exists, otherwise use the default title
        $('#alertModalLabel').text(title || config.defaultTitle);
        $('#alertModal .modal-body').html(message);

        // Remove all btn-* classes except 'btn-ok' and add the specific class to the 'btn-ok' button
        $('.btn.btn-ok').removeClass(function (index, className)
        {
            return (className.match(/(^|\s)btn-(?!ok)\S+/g) || []).join(' ');
        }).addClass(config.btnClass);

        // Set button click handlers
        $('#alertModal .btn.btn-ok').off('click').on('click', okClicked);
        $('#alertModal .btn.btn-cancel, #alertModal .btn-close').off('click').on('click', cancelClicked);

        // Set custom icon
        $('#alertModal .modal-icon').attr('src', config.icon);

        // Show the modal
        var myModal = new mdb.Modal(document.getElementById('alertModal'));
        myModal.show();
    },
    showInfo: function (message, title, okClicked, cancelClicked)
    {
        this.show(message, title, okClicked, cancelClicked, 'info');
    },
    showDanger: function (message, title, okClicked, cancelClicked)
    {
        this.show(message, title, okClicked, cancelClicked, 'danger');
    },
    showWarn: function (message, title, okClicked, cancelClicked)
    {
        this.show(message, title, okClicked, cancelClicked, 'warn');
    },
    // set onClosed(fn) {
    //     $('#alertModal').on('hidden.bs.modal', fn);
    // }
};


/*
Sample Usage:

modal.showDanger('some message', 'alert title', 
    function() { console.log('OK clicked'); }, 
    function() { console.log('Cancel clicked'); }
);
*/