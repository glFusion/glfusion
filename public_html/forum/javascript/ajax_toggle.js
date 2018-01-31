/*  Updates database values as checkboxes are checked.
 */
var forum_ajaxtoggle = function(cbox, id, field, component) {
    oldval = cbox.checked ? 0 : 1;
    var dataS = {
        "action" : "admin_toggle",
        "id": id,
        "field": field,
        "oldval": oldval,
        "component": component,
    };
    data = $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: glfusionSiteUrl + "/forum/ajax_controller.php",
        data: data,
        success: function(result) {
            cbox.checked = result.newval == 1 ? true : false;
            try {
                $.UIkit.notify("<i class='uk-icon-check'></i>&nbsp;" + result.statusMessage, {timeout: 1000,pos:'top-center'});
            }
            catch(err) {
                alert(result.statusMessage);
            }
        }
    });
    return false;
}
