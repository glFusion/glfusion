/**
* glFusion CMS
*
* glFusion DB Admin - Ajax UTF8 Conversion
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2015-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

/* ***************************************************************************
 * NOTE: Must update dbadmin_utf8.min.js for distribution
 * ***************************************************************************
 */

var dbadminint = (function() {

    // public methods/properties
    var pub = {};

    // private vars
    var tables  = null,
    table       = null,
    columns     = null,
    column      = null,
    columnCount = 0,
    columnDone  = 1,
    url         = null,
    done        = 0,
    count       = 0,
    conversionMessage = '',
    $m          = null;
    $t          = null;

    /**
    * sets HTML
    * retrieves table list for database
    *
    * calls convertDB
    *
    */
    pub.update = function() {
        done = 0;

        // retrieve the URL from the form
        url = $( '#dbcvtform' ).attr( 'action' );

        $("#dbadmin_batchprocesor").show();
        $('#cvtb').prop("disabled",true);
        $("#cvtb").html(lang_converting);
        t_on();

        message(lang_retrieve_tables);

        $.ajax({
            type: "POST",
            dataType: "json",
            url: url,
            data: {"mode" : "gettables" },
            timeout: 30000,
        }).done(function(data) {
            var result = $.parseJSON(data["js"]);
            if ( result.errorCode != 0 ) {
                console.log('DBadmin: Table List returned no results');
                alert(lang_error_gettable);
                window.location.href = destination;
            }
            tables = result.tablelist;
            count = tables.length;
            table = tables.shift();
            window.setTimeout(convertDB,250);
        }).fail(function(jqXHR, textStatus ) {
            console.log("DBadmin: Error retrieving table list from glFusion");
            alert(lang_error_gettable);
            window.location.href = destination;
        });
        return false; // prevent from firing
    };

    /**
    * initialize everything
    */
    pub.init = function() {
        // $m is the status message area
        $m = $('#batchinterface_msg');
        $t = $('#t');
        // if $m does not exist, return.
        if ( ! $m ) {
            return;
        }
        // init interface events
        $('#cvtb').click(pub.update);
    };

    /**
    * Convert the database to utf8mb4
    */
    var convertDB = function() {

        message(lang_converting + ' ' + 'database');

        var dataS = {
            "mode" : 'utfdb',
        };

        data = $.param(dataS);

        $.ajax({
            type: "POST",
            dataType: "json",
            url: url,
            data: data,
            timeout:60000
        }).done(function(data) {
            var result = $.parseJSON(data["js"]);
            if ( result.errorCode != 0 ) {
                console.log("DBadmin: The database conversion failed - " + result.message );
                alert(lang_error_db + ' :: ' + result.message);
                window.location.href = destination;
            }
            window.setTimeout(processTables,250);
        }).fail(function(jqXHR, textStatus ) {
            if (textStatus === 'timeout') {
                console.log("DBadmin: database conversion failed");
                alert(lang_error_db);
                window.location.href = destination;
            } else {
                console.log("DBadmin: Failed converting database - aborting.... " + textStatus );
                alert(lang_error_db);
                window.location.href = destination;
            }
        });
    };


    /**
    * Convert the table to utf8
    *
    * controls UI updates
    *
    * calls init colums
    */

    var processTables = function() {
        if (table) {
            var dataS = {
                "mode" : 'utftb',
                "table" : table,
            };

            data = $.param(dataS);

            message(lang_converting + ' ' + done + '/' + count + ' - '+ table);

            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: data,
                timeout:60000
            }).done(function(data) {
                var result = $.parseJSON(data["js"]);
                if ( result.errorCode != 0 ) {
                    console.log("DBadmin: The table conversion failed - " + result.message );
                    conversionMessage = conversionMessage + 'Table: ' + table + ' :: ' + result.message + '<br>';
                }
                var percent = Math.round(( done / count ) * 100);
                $('#pb').css('width', percent + "%");
                $('#pb').html(percent + "%");
                done++;
            }).fail(function(jqXHR, textStatus ) {
                if (textStatus === 'timeout') {
                    console.log("DBadmin: Error converting table " + table);
                    alert(lang_error_table + ' :: ' +table);
                    window.location.href = destination;
                }
            }).always(function( xhr, status ) {
                var wait = 250;
                window.setTimeout(initColumns, wait);
            });

        } else {
            finished();
        }
    };


    /**
    * retrieve list of columns for table
    */
    var initColumns = function() {
        if (table) {

            columns = null;

            var dataS = {
                "mode" : 'getcolumns',
                "table" : table,
            };

            data = $.param(dataS);

            columnDone = 1;
            var percent = 0;
            $('#pb-current').css('width', percent + "%");
            $('#pb-current').html(percent + "%");

            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: data,
                timeout:60000
            }).done(function(data) {
                var result = $.parseJSON(data["js"]);
                if ( result.errorCode != 0 ) {
                    console.log("DBadmin: " + result.message );
                    conversionMessage = conversionMessage + 'Table: ' + table + ' :: ' + result.message + '<br>';
                }
                columns = result.columnlist;
                columnCount = columns.length;
                column = columns.shift();
            }).fail(function(jqXHR, textStatus ) {
                conversionMessage = conversionMessage + 'Table: ' + table + ' :: ' + 'Failed to retrieve column list<br>';
                if (textStatus === 'timeout') {
                    console.log("DBadmin: Unable to retrieve column list from " + table);
                    alert(lang_error_getcolumn + " :: " + table);
                    window.location.href = destination;
                }
            }).always(function( xhr, status ) {
                var wait = 250;
                window.setTimeout(processColumn, wait);
            });
        }
    };

    /**
    * process each column in the table
    * converting to utf8mb4
    *
    * if a column cannot convert - display in the error section
    */

    var processColumn = function() {
        if (column) {

            var dataS = {
                "mode" : 'utfcm',  // need to change.
                "table" : table,
                "column" : column,
            };

            data = $.param(dataS);

            message(lang_converting + ' ' + done + '/' + count + ' - '+ table + ' - ' + column);

            var percent = Math.round(( columnDone / columnCount ) * 100);
            if ( percent == 0 ) percent = 1;
            $('#pb-current').css('width', percent + "%");
            $('#pb-current').html(percent + "%");

            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: data,
                timeout:60000
            }).done(function(data) {
                var result = $.parseJSON(data["js"]);
                if ( result.errorCode != 0 ) {
                    console.log("DBadmin: The column conversion did not complete");
                    conversionMessage = conversionMessage + 'Table: ' + table + ' Column: ' + column + ' :: ' + result.message + '<br>';
                }
                columnDone++;

            }).fail(function(jqXHR, textStatus ) {
                if (textStatus === 'timeout') {
                    console.log("DBadmin: Error converting column " + column);
                    conversionMessage = conversionMessage + 'Table: ' + table + ' Column: ' + column + ' :: ' + result.message + '<br>';
                }
            }).always(function( xhr, status ) {
                column = columns.shift();
                var wait = 250;
                window.setTimeout(processColumn, wait);
            });
        } else {
            var percent = 100;
            $('#pb-current').css('width', percent + "%");
            $('#pb-current').html(percent + "%");

            table = tables.shift();
            window.setTimeout(processTables, 250);
        }
    };

    /**
    * called when done - we make an ajax call
    * that currently fails as we are not setup for this one
    * need to update.
    */

    var finished = function() {

        $('#pb').css('width', "100%");
        $('#pb').html("100%");
        t_off();
        message(lang_success);

        window.setTimeout(function() {
            // ajax call to process table
            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: {"mode" : 'utfcomplete'},
            }).done(function(data) {
                var result = $.parseJSON(data["js"]);
                if ( result.errorCode != 0 ) {
                    conversionMessage = conversionMessage + '<br>' + lang_sc_error;
                }
                UIkit.modal.alert(lang_success);
                var $msgWindow = $('#dbadmin_messages');
                if ( conversionMessage == '' ) {
                    conversionMessage = lang_no_errors;
                }
                $msgWindow.html(conversionMessage);
                $("#dbadmin_message_window").show();
                $("#cvtb").html(lang_convert);

            });
        }, 3000);
    };

    /**
    * Gives textual feedback
    * updates the ID defined in the $msg variable
    */
    var message = function(text) {
        $m.html(text);
    };

    /**
    * add a throbber image
    */
    var t_on = function() {
        $t.show();
    };

    /**
    * Stop the throbber
    */
    var t_off = function() {
        $t.hide();
    };

    // return only public methods/properties
    return pub;
})();

$(function() {
    dbadminint.init();
});