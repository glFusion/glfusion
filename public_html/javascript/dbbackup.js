/**
* glFusion Database Administration Ajax Driver
*
* @author Mark R. Evans <mark AT glfusion DOT org>
*
*/

var glfusion_dbadminInterface = (function() {

    // public methods/properties
    var pub = {};

    // private vars
    var items = null,
    item =  null,
    url =  null,
    done =  1,
    count = 0,
    dbFileName = null,
    $msg = null;

    /**
    * initialize everything
    */
    pub.init = function() {
        // $msg is the status message area
        $msg = $('#batchinterface_msg');

        // if $msg does not exist, return.
        if ( ! $msg) {
            return;
        }

        // init interface events
        $('#dbbackupbutton').click(pub.update);
    };

    var process = function() {
        if (item) {

            var dataS = {
                "mode" : 'dbbackup_table',
                "table" : item,
                "backup_filename" : dbFileName,
            };

            data = $.param(dataS);

            // ajax call to process item
            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: data,
                timeout: 60000, // sets timeout to 1 minute
                success: function(data) {
                    var wait = 250;
                    var result = $.parseJSON(data["json"]);
                    try {
                        message('<p style="padding-left:20px;">' + lang_backingup + ' ' + done + '/' + count + ' - '+ item + '</p>');
                        var percent = Math.round(( done / count ) * 100);
                        $('#progress-bar').css('width', percent + "%");
                        $('#progress-bar').html(percent + "%");
                        item = items.shift();
                        done++;
                        window.setTimeout(process, wait);
                    }
                    catch(err) {
                        alert(result.statusMessage);
                    }
                }
            });

        } else {
            finished();
        }
    };

    var finished = function() {
        // we're done
        $('#progress-bar').css('width', "100%");
        $('#progress-bar').html("100%");
        throbber_off();
        message(lang_success);
        window.setTimeout(function() {
            // ajax call to process item
            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: {"mode" : 'dbbackup_complete', "backup_filename" : dbFileName},
                success: function(data) {
                    var wait = 250;
                    var result = $.parseJSON(data["json"]);
                    try {
                        $('#dbbackupbutton').prop("disabled",false);
                        $("#dbbackupbutton").html(lang_backup);
                    }
                    catch(err) {
                        alert(result.statusMessage);
                    }
                }
            });

        }, 3000);
    };


    /**
    * Gives textual feedback
    * updates the ID defined in the $msg variable
    */
    var message = function(text) {
        if (text.charAt(0) !== '<') {
            text = '<p style="padding-left:20px;">' + text + '</p>'
        }
        $msg.html(text);
    };

    // update process
    pub.update = function() {
        done = 1;
        url = $( '#dbbackupform' ).attr( 'action' );

        $("#dbadmin_batchprocesor").show();
        $('#dbbackupbutton').prop("disabled",true);
        $("#dbbackupbutton").html(lang_backingup);

        throbber_on();

        $.ajax({
            type: "POST",
            dataType: "json",
            url: url,
            data: { "mode" : "dbbackup_init" },
            success: function(data) {
                var result = $.parseJSON(data["json"]);

                items = result.tablelist;
                dbFileName = result.backup_filename;
                count = items.length;
                if ( result.errorCode != 0 ) {
                    throbber_off();
                    message('Error');
                    $('#dbbackupbutton').prop("disabled",false);
                    $("#dbbackupbutton").html(lang_backup);
                    return alert(result.statusMessage);
                }
                try {
                    item = items.shift();
                    message(lang_backingup);
                    window.setTimeout(process,1000);
                }
                catch(err) {
                    alert('Error: See error.log');
                }
            }
        });
        return false; // prevent from firing
    };

    /**
    * add a throbber image
    */
    var throbber_on = function() {
        $msg.addClass('tm-updating');
    };

    /**
    * Stop the throbber
    */
    var throbber_off = function() {
        $msg.removeClass('tm-updating');
    };

    // return only public methods/properties
    return pub;
})();

$(function() {
    glfusion_dbadminInterface.init();
});