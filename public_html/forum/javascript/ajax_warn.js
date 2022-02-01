var modal;
var forum_ajaxwarnform = function(t_uid, t_id) {
    var dataS = {
        "action": "warn_getform",
        "uid": t_uid,
        "t_id": t_id,
    };
    data = $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "text",
        url: site_url + "/forum/ajax_controller.php",
        data: data,
        success: function(result) {
            try {
				if (typeof(UIkit.modal.blockUI) == 'function') {
					// UIkit v2
					modal = UIkit.modal.blockUI(result);
				} else if (typeof(UIkit.modal.dialog) == 'function') {
					// UIkit v3
					modal = UIkit.modal.dialog(
						result,
						{'bgClose':true}
					);
			    }
            }
            catch(err) {
                console.log(err);
            }
        }
    });
    return false;
};

var forum_ajaxwarn = function() {
	modal.hide();
}
