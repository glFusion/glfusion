var forum_ajaxlike = function(v_uid, t_uid, t_id, vote) {
    var dataS = {
        "action": "like",
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
console.log(result);
                var span_id = result.t_id + "_" + result.t_uid;
                var div_cls = "like_lang"+result.t_uid;
                if (result.vote == 1) {     // user was likeed
                    document.getElementById("like_" + span_id).style.display = "none";
                    document.getElementById("unlike_" + span_id).style.display = "";
                } else {
                    document.getElementById("like_" + span_id).style.display = "";
                    document.getElementById("unlike_" + span_id).style.display = "none";
                }
                var like_lang = document.getElementsByClassName("like_lang_"+result.t_uid);
                var like_cnt = document.getElementsByClassName("like_cnt_"+result.t_uid);
                var like_lang_vis = result.vote_count > 0 ? "" : "none";
                for (var i = 0; i < like_lang.length; i++) {
                    like_lang[i].style.display= like_lang_vis;
                }
                for (var i = 0; i < like_cnt.length; i++) {
                    like_cnt[i].innerHTML = result.vote_count;
                }

            }
            catch(err) {
                console.log(result.statusMessage);
            }
        }
    });
    return false;
};

