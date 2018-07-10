$(document).ready(function() {
    var lastUpdate = 0;

    var myVar = setInterval(function(){ myTimer() }, 240000); // 4 minutes

    refreshTokens();

    function myTimer() {
        if (new Date().getTime() - lastActivity > 900000) { // 15 minutes
            clearInterval(myVar);
            alert( lang_timer );
            console.log("Activity Timer: Reset lastActivity Date");
            refreshTokens();
            lastActivity = new Date().getTime();
            myVar = setInterval(function(){ myTimer() }, 240000); // 4 minutes
        }
        lastUpdate = new Date().getTime();
        refreshTokens();
    };

    function refreshTokens() {
        var sec_token = $('#sectoken').val();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: site_url + '/refresh.php',
            data: {"keepalive" : editor_type, "token" : sec_token },
            success: function(data) {
            }
        });
        console.log("Activity Timer: Refreshed session tokens and cookies");
    }
});