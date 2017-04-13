/**
* glFusion Media Gallery
*
* Performs batch operations on albums
*
* LICENSE: This program is free software; you can redistribute it
*  and/or modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* @category   glFusion CMS
* @author     Mark R. Evans  mark AT glFusion DOT org
* @copyright  2015-2017 - Mark R. Evans
* @license    http://opensource.org/licenses/gpl-2.0.php - GNU Public License v2 or later
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
    $errorMessages = lang_error,
    errorCount = 0,
    $msg = null,
    $buttons = null,
    lang = null;
    force = '';

    /**
    * initialize everything
    */
    pub.init = function() {
        $msg = $('#batchinterface_msg');
        $t = $('#t');
        if ( ! $msg) {
            console.log("msg was not set");
            return;
        }
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

            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: data,
                timeout: 60000
            }).done(function(data) {
                var result = $.parseJSON(data["json"]);
                if ( result.errorCode != 0 ) {
                    $errorMessages = $errorMessages + "ERROR: " + result.statusMessage + "<br>";
                    errorCount++;
                }
            }).fail(function(jqXHR, textStatus ) {
                $errorMessages = $errorMessages + "ERROR: Timeout processing image " + item + "<br>";
                errorCount++;
            }).always(function( xhr, status ) {
                item = items.shift();
                done++;
                message(lang_processing + ' ' + done + '/' + count);
                var percent = Math.round(( done / count ) * 100);
                $('#progress-bar').css('width', percent + "%");
                $('#progress-bar').html(percent + "%");
                var wait = 250;
                window.setTimeout(index, wait);
            });
        } else {
            finished();
        }
    };

    var finished = function() {
        $('#progress-bar').css('width', "100%");
        $('#progress-bar').html("100%");
        throbber_off();
        message(lang_success);
        window.setTimeout(function() {
            $('#'+button_id).prop("disabled",false);
            $('#'+button_id).html(lang_process);
            if ( errorCount != 0 ) lang_success = $errorMessages;
            var modal = UIkit.modal.alert(lang_success);
            modal.on ({
                'hide.uk.modal': function(){
                    $(location).attr('href', 'album.php?aid='+aid);
                }
            });
        }, 3000);
    };

    /**
    * Gives textual feedback
    * updates the ID defined in the $msg variable
    */
    var message = function(text) {
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
        }).done(function(data) {
            var result = $.parseJSON(data["json"]);
            items = result.itemlist;
            count = items.length;
            count++;
            message(lang_processing + '...');
            item = items.shift();
            window.setTimeout(index,1000);
        });
        return false; // prevent from firing
    };

    /**
    * add a throbber image
    */
    var throbber_on = function() {
        $t.show();
    };

    /**
    * Stop the throbber
    */
    var throbber_off = function() {
        $t.hide();
    };

    // return only public methods/properties
    return pub;
})();

$(function() {
    glfusion_batchinterface.init();
});