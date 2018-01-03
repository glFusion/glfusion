var forum_ajaxthank = function(v_uid, t_uid, t_id, vote) {
    var dataS = {
        "action": "thank",
        "v_uid": v_uid,
        "t_uid": t_uid,
        "t_id": t_id,
        "vote": vote,
    };
    data = $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: site_url + "/forum/ajax_controller.php",
        data: data,
        success: function(result) {
            try {
                var span_id = result.t_id + "_" + result.t_uid;
                var div_cls = "thank_lang"+result.t_uid;
                if (result.vote == 1) {     // user was thanked
                    document.getElementById("thank_" + span_id).style.display = "none";
                    document.getElementById("unthank_" + span_id).style.display = "";
                } else {
                    document.getElementById("thank_" + span_id).style.display = "";
                    document.getElementById("unthank_" + span_id).style.display = "none";
                }
                var thank_lang = document.getElementsByClassName("thank_lang_"+result.t_uid);
                var thank_cnt = document.getElementsByClassName("thank_cnt_"+result.t_uid);
                var thank_lang_vis = result.vote_count > 0 ? "" : "none";
                for (var i = 0; i < thank_lang.length; i++) {
                    thank_lang[i].style.display= thank_lang_vis;
                }
                for (var i = 0; i < thank_cnt.length; i++) {
                    thank_cnt[i].innerHTML = result.vote_count;
                }

            }
            catch(err) {
                console.log(result.statusMessage);
            }
        }
    });
    return false;
};

