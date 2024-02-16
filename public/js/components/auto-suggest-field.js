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
            _buildSuggestions(response, function() 
            {
                loadComplete(sourceMapping);
            });
        }
    });

    var _buildSuggestions = function (response, callback)
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

    let $input = $(selectorId);

    var _setVal = function(value) {
        $input.val(value).trigger('keyup');
    };

    return {
        getInput : ()  => $(selectorId),
        getType  : ()  => 'autosuggest',
        getValue : ()  => $input.val(),
        setValue : (v) => _setVal(v),       // Set value then trigger keyup
        setText  : (t) => $input.val(t),    // Set value silently
        reset    : ()  => {
            _setVal('');
            hideTextboxError($input)
        },
    }
}