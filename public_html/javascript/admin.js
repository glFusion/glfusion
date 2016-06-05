// block toggle
$(document).ready(function(){
    $(".blk-clicker").removeAttr('onclick');
    $(".blk-clicker").change(blocktoggle);
});
var blocktoggle = function() {
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

            try {
                $.UIkit.notify("<i class='uk-icon-check'></i>&nbsp;" + result.statusMessage, {timeout: 1000,pos:'top-center'});
            }
            catch(err) {
                alert(result.statusMessage);
            }
        }
    });
    return false;
};
// menu element toggle
$(document).ready(function(){
    $(".menu-element-enabler").removeAttr('onclick');
    $(".menu-element-enabler").change(toggleMenuElement);
});
var toggleMenuElement = function() {
    var dataS = {
        "action" : "menu-element-toggle",
    };
    data = $("form").serialize() + "&" + $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: site_admin_url + "/ajax_controller.php",
        data: data,
        success: function(data) {
            var result = $.parseJSON(data["json"]);

            try {
                $.UIkit.notify("<i class='uk-icon-check'></i>&nbsp;" + result.statusMessage, {timeout: 1000,pos:'top-center'});
            }
            catch(err) {
                alert(result.statusMessage);
            }
        }
    });
    return false;
};
// menu element toggle
$(document).ready(function(){
    $(".menu-enabler").removeAttr('onclick');
    $(".menu-enabler").change(toggleMenu);
});
var toggleMenu = function() {
    var dataS = {
        "action" : "menu-toggle",
    };
    data = $("form").serialize() + "&" + $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: site_admin_url + "/ajax_controller.php",
        data: data,
        success: function(data) {
            var result = $.parseJSON(data["json"]);

            try {
                $.UIkit.notify("<i class='uk-icon-check'></i>&nbsp;" + result.statusMessage, {timeout: 1000,pos:'top-center'});
            }
            catch(err) {
                alert(result.statusMessage);
            }
        }
    });
    return false;
};
// menu element toggle
$(document).ready(function(){
    $(".sp-enabler").removeAttr('onclick');
    $(".sp-enabler").change(toggleSP);
});
var toggleSP = function() {
    var dataS = {
        "action" : "sp-toggle",
    };
    data = $("form").serialize() + "&" + $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: site_admin_url + "/ajax_controller.php",
        data: data,
        success: function(data) {
            var result = $.parseJSON(data["json"]);

            try {
                $.UIkit.notify("<i class='uk-icon-check'></i>&nbsp;" + result.statusMessage, {timeout: 1000,pos:'top-center'});
            }
            catch(err) {
                alert(result.statusMessage);
            }
        }
    });
    return false;
};

// si toggle
$(document).ready(function(){
    $(".sis-clicker").removeAttr('onclick');
    $(".sis-clicker").change(sistoggle);
});
var sistoggle = function() {
    var dataS = {
        "action" :  "sistoggle",
    };
    data = $("form").serialize() + "&" + $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: site_admin_url + "/ajax_controller.php",
        data: data,
        success: function(data) {
            var result = $.parseJSON(data["json"]);

            try {
                $.UIkit.notify("<i class='uk-icon-check'></i>&nbsp;" + result.statusMessage, {timeout: 1000,pos:'top-center'});
            }
            catch(err) {
                alert(result.statusMessage);
            }
        }
    });
    return false;
};

// sfm toggle
$(document).ready(function(){
    $(".sfm-clicker").removeAttr('onclick');
    $(".sfm-clicker").change(sfmtoggle);
});
var sfmtoggle = function() {
    var dataS = {
        "action" :  "sfmtoggle",
    };
    data = $("form").serialize() + "&" + $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: site_admin_url + "/ajax_controller.php",
        data: data,
        success: function(data) {
            var result = $.parseJSON(data["json"]);

            try {
                $.UIkit.notify("<i class='uk-icon-check'></i>&nbsp;" + result.statusMessage, {timeout: 1000,pos:'top-center'});
            }
            catch(err) {
                alert(result.statusMessage);
            }
        }
    });
    return false;
};