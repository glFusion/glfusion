/**
 * glFusion Batch interface - rebuild stuff
 *
 * @author Mark R. Evans <mark AT glfusion DOT org>
 *
 * get a list of items to process - getList()
 * go through the list, processing one by one
 * done.
 *
 */

var glfusion_batchinterface = (function() {

    // public methods/properties
    var pub = {};

    // private vars
    var items = null,
        item =  null,
        url =  null,
        aid = 0,
        done =  1,
        count = 0,
        $msg = null,
        $buttons = null,
        lang = null;
        force = '';

    /**
     * initialize everything
     */
    pub.init = function() {
        // $msg is the status message area
        $msg = $('#glfusion_batchinterface_msg');

        // if $msg does not exist, return.
        if ( ! $msg) {
            console.log("msg was not set");
            return;
        }

        $buttons = jQuery('#plugin__searchindex_buttons');

        // init interface events
        $('#'+button_id).click(pub.update);
    };

    var index = function() {
        if (item) {

            var dataS = {
                "mode"      : "ajaxrebuild",
                "action"    : ajaxmode,
                "id"        : item,
                "aid"       : aid,
            };

            data = $.param(dataS);

            // ajax call to process item
            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: data,
                success: function(data) {
                    var wait = 250;
                    var result = $.parseJSON(data["json"]);
                    try {
                        message('<p style="padding-left:20px;">' + lang_processing + ' ' + done + '/' + count + '</p>');

                        var percent = Math.round(( done / count ) * 100);
                        $('#progress-bar').css('width', percent + "%");
                        $('#progress-bar').html(percent + "%");

                        item = items.shift();
                        done++;
                        window.setTimeout(index, wait);
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
        throbber_off();
        message(lang_success);
        window.setTimeout(function() {
            $('#'+button_id).prop("disabled",false);
            $('#'+button_id).html(lang_process);
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


    pub.rebuild = function() {
        pub.update(true);
    };

    // update process
    pub.update = function(rebuild) {
        done = 1;

        url = $( '#bpform' ).attr( 'action' );

        rebuild = rebuild || false;

        aid = $( "#aid" ).val();

        $("#bpstatus").show();

        $('#'+button_id).prop("disabled",true);
        $('#'+button_id).html(lang_processing + '...');

        throbber_on();

        $.ajax({
            type: "POST",
            dataType: "json",
            url: url,
            data: {"mode" : "ajaxrebuild", "action" : "itemlist", "aid" : aid },
            success: function(data) {
                console.log(data);
                var result = $.parseJSON(data["json"]);
                items = result.itemlist;
                count = items.length;
                try {
                    item = items.shift();
                    message(lang_processing + '...');
                    window.setTimeout(index,1000);
                }
                catch(err) {
                    alert(result.statusMessage);
                }
            }
        });
        return false;
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
    glfusion_batchinterface.init();
});