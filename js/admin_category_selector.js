jQuery(document).ready(function() {

    jQuery(".category-selector").change(function()
    {
        updateCategorySelectors();
    });

    function updateCategorySelectors() {
        var selectedValues = new Array();
        var actions = new Array();

        // get the selected values and names of each category selector
        jQuery(".category-selector").each(function () {
            actions[jQuery(this).prop("name")] = {};
            selectedValues.push(
                {
                    name: jQuery(this).prop("name"),
                    value: jQuery(this).find(":selected").val(),
                }
            );
        });

        // reformat into a setof actions to perform
        for (var selectorName in actions) {
            actions[selectorName] = selectedValues.filter(function (s) {
                return s.name != this;
            }, selectorName);
        }

        // iterate over each selector and disable options
        // that have been selected in the other category selectors
        for (var current in actions) {
            var sel = "[name='" + current + "']";
            jQuery(sel).find("option").prop('disabled', false);

            for (var n = 0; n < actions[current].length; n++) {
                if(actions[current][n].value == "-2") {
                    continue;
                }
                var optSel = "option[value='" + actions[current][n].value + "']";
                jQuery(sel).find(optSel).prop('disabled', true);
            }
        }
    }

    // on load, update the category selectors to disable
    // the options selected in the other category selectors
    updateCategorySelectors();
});