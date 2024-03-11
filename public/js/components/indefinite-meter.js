function to_indefmeter(selector, initialPercent)
{
    let root        = $(selector);
    let $span       = root.find('span');
    let $caption    = root.find('.indef-caption');

    initialPercent  = initialPercent || '';

    if (!initialPercent)
        $span.width(0);
    else
        $span.css('width', `${initialPercent}%`);

    let __setProgress = function (percentInt, caption) 
    {
        if (percentInt > 100)
            percentInt = 100;

        else if (percentInt < 0)
            percentInt = 0;

        $span
            .data("origWidth", `${percentInt}%`)
            .width(0)
            .animate({
                width: $span.data("origWidth") // or + "%" if fluid
            }, 2000);

        if (caption)
            __setCaption(caption);
    }

    let __setCaption = (caption) => $caption.text(caption);
    
    let __reset = function() 
    {
        $span.stop().width(0);
        $caption.text('');
    };

    return {
        'setProgress'   : __setProgress,
        'setCaption'    : __setCaption,
        'reset'         : __reset,
    };
}

