var snackbar = {

    getTypeConfig: function () {  

        return {
            'default'   : { defaultTitle: 'Notice'      , icon: 'fa-circle-info'},
            'info'      : { defaultTitle: 'Information' , icon: 'fa-circle-info'},
            'success'   : { defaultTitle: 'Success'     , icon: 'fa-circle-check'},
            'danger'    : { defaultTitle: 'Failure'     , icon: 'fa-circle-exclamation'},
            'warning'   : { defaultTitle: 'Warning'     , icon: 'fa-triangle-exclamation'},
        }
    },

    show: function(message, title, type) 
    {
        var config = this.getTypeConfig()[type] || this.getTypeConfig().default;

        // Create main snackbar layout
        let root = document.createElement('div');
        root.classList.add('snackbar');
        root.classList.add(type);

        // Build the icons
        let iconWrapper = document.createElement('div');
        iconWrapper.classList.add('snackbar-icon')

        let fasIcon = document.createElement('i');
        fasIcon.classList.add('fas');
        fasIcon.classList.add(config.icon)

        iconWrapper.appendChild(fasIcon);
        root.appendChild(iconWrapper);

        // Main content wrapper
        var body = document.createElement('div');
        body.classList.add('snackbar-body');

        // Build the title layout
        var titleWrapper = document.createElement('div');
        titleWrapper.classList.add('snackbar-title-wrapper');

        var titleText = document.createElement('h6');
        titleText.classList.add('snackbar-title');
        titleText.classList.add('me-auto');
        titleText.innerText = title || config.defaultTitle;

        var timeLabel = document.createElement('small');
        timeLabel.classList.add('snackbar-time');

        var currentTime = moment().format('hh:mm A');
        timeLabel.innerText = currentTime;

        // The message
        var textContent = document.createElement('div');
        textContent.classList.add('snackbar-content');
        textContent.innerText = message;

        titleWrapper.appendChild(titleText);
        titleWrapper.appendChild(timeLabel);
        body.appendChild(titleWrapper);
        body.appendChild(textContent);

        root.appendChild(body);
         
        let frame = document.querySelector('.snackbar-frame');
        frame.appendChild(root);

        // The snackbar must slide from right to left. We must
        // initially set its left position to be equal to its width
        $(root).css('left', $(root).width()).animate({
            'left': 0
        }, 600);

        // Wait 4 seconds before removing the snackbar
        setTimeout(() => {

            $(root).animate({
                left: '+=500'
            }, 
            600, 
            function() {
                $(this).remove();
            });

        }, 4000);
    },
    showSuccess(message, title) {
        this.show(message, title, 'success');
    },
    showInfo(message, title) {
        this.show(message, title, 'info');
    },
    showDanger(message, title) {
        this.show(message, title, 'danger');
    },
    showWarn(message, title) {
        this.show(message, title, 'warning');
    }
};

/*
EXPECTED STRUCTURE:
<div class="snackbar">
    <div class="snackbar-icon">
        <i class="fas"></i>
    </div>
    <div class="snackbar-body">
        <div class="snackbar-title-wrapper">
            <h6 class="snackbar-title me-auto"></h6>
            <small class="snackbar-time"></small>
        </div>
        <p class="snackbar-content"></p>
    </div>
</div>

*/