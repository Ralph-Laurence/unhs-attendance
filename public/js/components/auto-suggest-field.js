function to_auto_suggest_ajax(selectorId, options, loadComplete)
{
    var sourceMapping = {};

    $.ajax({
        url: options.action,
        type: options.method || 'POST',
        data: {
            '_token': options.csrfToken,
        },
        success: function(response) 
        {
            buildSuggestions(response, function() 
            {
                loadComplete(sourceMapping);
            });
        }
    });

    var buildSuggestions = function (response, callback)
    {
        let suggestions = [];

        if (response)
        {
            response = JSON.parse(response);

            sourceMapping = response.reduce(function (map, data)
            {
                map[data.value] = data.label;
                suggestions.push(data.value);

                return map;
            }, {});
        }
        
        var input = set_autocomplete(selectorId, suggestions, start_at_letters = 1);
        input.readOnly = false;
        
        callback();
    };
}