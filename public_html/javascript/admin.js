$(document).ready(function(){
    $(".blk-clicker").removeAttr('onclick');
    $(".blk-clicker").change(toggle);
});
var toggle = function() {
    var dataS = {
        "action" :  "blocktoggle",
    };
    data = $("form").serialize() + "&" + $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: site_admin_url + "/ajax_controller.php",
        data: data,
        success: function(data) {
            var result = $.parseJSON(data["json"]);
            $.UIkit.notify("<i class='uk-icon-check'></i>" + result.statusMessage, {timeout: 1000,pos:'bottom-center'});
        }
    });
    return false;
};
