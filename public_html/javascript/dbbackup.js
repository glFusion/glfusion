/**
 * glFusion DB Admin - Backup Ajax Driver
 *
 * Back up all tables in database
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
    var tables = null,
        table = null,
        url = null,
        done = 0,
        count = 0,
        startrecord = 0,
        totalrows = 0,
        totalrowsprocessed = 0,
        dbFileName = null,
        periods = '&nbsp;',
        periodCounter = 0,
        $msg = null;

    // update process
    pub.update = function() {
        done = 0;
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
        }).done(function(data) {
            var result = $.parseJSON(data["js"]);
            tables = result.tablelist;
            totalrows = result.totalrows;
            dbFileName = result.backup_filename;
            count = tables.length;
            if ( result.errorCode != 0 ) {
                throbber_off();
                message('Error');
                $('#dbbackupbutton').prop("disabled",false);
                $("#dbbackupbutton").html(lang_backup);
                return alert(result.statusMessage);
            }
            table = tables.shift();
            message(lang_backingup);
            window.setTimeout(processTable,1000);
        }).fail(function(jqXHR, textStatus ) {
            alert("Error initializing the database backup");
            window.location.href = "database.php";
        });
        return false;
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
        $('#dbbackupbutton').click(pub.update);
    };

    var processTable = function() {
        if (table) {

            if ( startrecord == 0 ) {
                periods = '&nbsp;';
                periodCounter = 0;
            } else {
                periods = periods + '&nbsp;&bull;';
                periodCounter++;
                if ( periodCounter > 20 ) {
                    periodCounter = 0;
                    periods = '&bull;';
                }
            }

            var dataS = {
                "mode" : 'dbbackup_table',
                "table" : table,
                "backup_filename" : dbFileName,
                "start" : startrecord,
            };

            data = $.param(dataS);

            message(lang_backingup + ' ' + done + '/' + count + ' - '+ table + periods);

            // ajax call to process table
            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: data,
                timeout: 60000,
            }).done(function(data) {
                var result = $.parseJSON(data["js"]);
                var rowsthissession = result.processed;

                if ( result.errorCode == 3 ) {
                    alert("Database Backup Failed - unable to open backup file");
                    window.location.href = "database.php";
                }
                if ( result.errorCode == 2 ) {
                    console.log("DBadmin: Table backup incomplete - making another pass");
                    startrecord = result.startrecord;
                } else {
                    table = tables.shift();
                    done++;
                    startrecord = 0;
                    periods = '&nbsp;';
                    periodCounter = 0;
                }
                totalrowsprocessed = totalrowsprocessed + rowsthissession;
                var percent = Math.round(( done / count ) * 100);
                $('#progress-bar').css('width', percent + "%");
                $('#progress-bar').html(percent + "%");

                var wait = 250;
                window.setTimeout(processTable, wait);

            }).fail(function(jqXHR, textStatus ) {
                if (textStatus === 'timeout') {
                    console.log("DBadmin: Timeout - error backing up table " + table);
                }
                alert("Error performing backup - timed out on table " + table);
                window.location.href = "database.php";

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
        startrecord = 0;
        totalrows = 0;
        totalrowsprocessed = 0;
        window.setTimeout(function() {
            // ajax call to process table
            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: {"mode" : 'dbbackup_complete', "backup_filename" : dbFileName},
            }).done(function(data) {
                $("#dbbackupbutton").html(lang_backup);
                UIkit.modal.confirm(lang_success, function(){
                    $(location).attr('href', 'database.php');
                }, function(){}, {labels:{'Ok': lang_ok,'Cancel': lang_cancel } });
            });
        }, 2000);
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
