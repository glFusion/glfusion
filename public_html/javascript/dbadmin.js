/**
 * glFusion DB Admin - Conversion / Optimization Ajax Driver
 *
 * Iterates over tables to convert / optimized based on mode
 *
 * LICENSE: This program is free software; you can redistribute it
 *  and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @category   glFusion CMS
 * @package    dbAdmin
 * @author     Mark R. Evans  mark AT glFusion DOT org
 * @copyright  2015-2016 - Mark R. Evans
 * @license    http://opensource.org/licenses/gpl-2.0.php - GNU Public License v2 or later
 * @since      File available since Release 1.6.3
 *
 */

var dbadminint = (function() {

    // public methods/properties
    var pub = {};

    // private vars
    var tables  = null,
        table   = null,
        url     = null,
        done    = 0,
        count   = 0,
        $msg    = null;

    // update process
    pub.update = function() {
        done = 0;
        url = $( '#dbcvtform' ).attr( 'action' );

        $("#dbadmin_batchprocesor").show();
        $('#dbconvertbutton').prop("disabled",true);
        $("#dbconvertbutton").html(lang_converting);

        throbber_on();

        $.ajax({
            type: "POST",
            dataType: "json",
            url: url,
            data: {"mode" : "dblist", "engine" : engine },
            timeout: 30000,
        }).done(function(data) {
            var result = $.parseJSON(data["js"]);
            tables = result.tablelist;
            count = tables.length;
            table = tables.shift();
            message(lang_converting);
            window.setTimeout(processTable,1000);
        });
        return false; // prevent from firing
    };

    /**
    * initialize everything
    */
    pub.init = function() {
        // $msg is the status message area
        $msg = $('#batchinterface_msg');
        $t = $('#t');

        // if $msg does not exist, return.
        if ( ! $msg) {
            return;
        }

        // init interface events
        $('#dbconvertbutton').click(pub.update);
    };

    var processTable = function() {
        if (table) {
            // ajax call to process table
            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: {"mode" : mode, "table" : table, "engine" : engine },
                timeout:60000
            }).done(function(data) {
                var result = $.parseJSON(data["js"]);
                if ( result.errorCode != 0 ) {
                    console.log("DBadmin: The table conversion did not complete");
                }
                message(lang_converting + ' ' + done + '/' + count + ' - '+ table);
                var percent = Math.round(( done / count ) * 100);
                $('#progress-bar').css('width', percent + "%");
                $('#progress-bar').html(percent + "%");
                table = tables.shift();
                done++;
            }).fail(function(jqXHR, textStatus ) {
/*
                if (textStatus === 'timeout') {
                     console.log("DBadmin: Timeout converting table " + table);
                } else {
                     console.log("DBadmin: Error converting table " + table);
                }
                alert("dbAdmin: Error converting table " + table);
                window.location.href = "database.php";
*/

            }).always(function( xhr, status ) {
                var wait = 250;
                window.setTimeout(processTable, wait);
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
            // ajax call to process table
            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: {"mode" : mode + "complete", "engine" : engine},
            }).done(function(data) {
                $('#dbconvertbutton').prop("disabled",false);
                $("#dbconvertbutton").html(lang_convert);
               UIkit.modal.confirm(lang_success, function(){
                    $(location).attr('href', 'database.php');
                }, function(){}, {labels:{'Ok': lang_ok,'Cancel': lang_cancel } });
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
    dbadminint.init();
});