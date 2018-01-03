var forum_ajaxvote = function(v_uid, t_uid, t_id, vote) {
    var dataS = {
        "action": "vote",
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
                var upvote = document.getElementsByClassName("upvote_"+result.t_uid);
                var dnvote = document.getElementsByClassName("downvote_"+result.t_uid);
                var user_rep = document.getElementsByClassName("user_rep_"+result.t_uid);
                var upvote_vis = result.plus_vote ? "" : "hidden";
                var dnvote_vis = result.minus_vote ? "" : "hidden";
                if (result.rating > 0) result.rating = "+" + result.rating;

                for (var i = 0; i < upvote.length; i++) {
                    upvote[i].style.visibility = upvote_vis;
                    upvote[i].title = result.vote_language;
                }
                for (var i = 0; i < dnvote.length; i++) {
                    dnvote[i].style.visibility = dnvote_vis;
                    dnvote[i].title = result.vote_language;
                }
                for (var i = 0; i < user_rep.length; i++) {
                    user_rep[i].innerHTML = result.rating;
                }
            }
            catch(err) {
                console.log("Error");
            }
        }
    });
    return false;
};

