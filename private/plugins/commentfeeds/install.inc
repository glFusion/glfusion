<?php
// +--------------------------------------------------------------------------+
// | CommentFeeds Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | install.inc                                                              |
// |                                                                          |
// | Installation functions to glFusion                                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Comment Feed Plugin for Geeklog                             |
// | Author:                                                                  |
// | Michael Jervis <mike AT fuckingbrit DOT com>                             |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

// this file can't be used on its own
if (strpos ($_SERVER['PHP_SELF'], 'install.inc') !== false)
{
    die ('This file can not be used on its own.');
}

global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

$pi_name        = 'commentfeeds';
$pi_version     = '1.1.0.fusion';
$gl_version     = '1.1.0';
$pi_url         = 'http://www.glfusion.org';
$base_path      = $_CONF['path'] . 'plugins/' . $pi_name . '/';

/*
 * If we are being called glFusion's install routine, there is no
 * COM_errorLog() function available.
 */

if (!function_exists('COM_errorLOG') ) {
    function COM_errorLOG() {
    }
}

/**
* Checks the requirements for this plugin and if it is compatible with this
* version of glFusion.
*
* @return   boolean     true = proceed with install, false = not compatible
*
*/
function commentfeeds_compatible_with_this_glfusion_version ()
{
    // requires 1.1.0 or newer
    $glversion = explode(".", GVERSION);
    if ( $glversion[0] < 1 ) {
        return false;
    }
    if ( $glversion[1] < 1 ) {
        return false;
    }
    return true;
}

function plugin_install_commentfeeds($_DB_table_prefix)
{
    global $_TABLES, $_CONF;
    global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

    // Register the plugin with glFusion
    DB_delete($_TABLES['plugins'],'pi_name','commentfeeds');
    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
        . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error()) {
        plugin_uninstall_commentfeeds();
        return false;
        exit;
    }

    return true;
}


function plugin_remove_commentfeeds() {
    global $_DB_table_prefix, $_TABLES;

    $pi_name    = 'commentfeeds';

    // Remove feed files
    COM_errorLog ('removing comment feed feed files', 1);
    $sql = "SELECT filename FROM {$_TABLES['syndication']} WHERE type = 'commentfeeds';";
    $result = DB_query( $sql );
    $nrows = DB_numRows( $result );
    COM_errorLog ($nrows. ' files stored in table.', 1);
    if ( $nrows > 0 ) {
        for ( $i = 0; $i < $nrows; $i++ ) {
            $fcount = $i + 1;
            $A = DB_fetchArray( $result );
            $fullpath = SYND_getFeedPath( $A[0] );
            if ( file_exists( $fullpath ) ) {
                unlink ($fullpath);
                COM_errorLog ("removed file $fcount of $nrows: $fullpath", 1);
            } else {
                COM_errorLog ("cannot remove file $fcount of $nrows, it does not exist! ($fullpath)", 1);
            }
        }
    }
    COM_errorLog ('...success', 1);

    // Remove comment Feeds from syndiaction table
    COM_errorLog ('removing comment feeds from table', 1);
    DB_query ("DELETE FROM {$_TABLES['syndication']} WHERE type = 'commentfeeds'");
    COM_errorLog ('...success', 1);

    // Ok to proceed and delete plugin

    // Unregister the plugin with glFusion
    COM_errorLog('Attempting to unregister the CommentFeeds Plugin from glFusion',1);
    DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE group_name='commentfeeds'");
    DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = 'commentfeeds'",1);

    COM_errorLog('...success',1);
    return true;
}
?>