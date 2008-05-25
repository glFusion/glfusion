<?php
// +---------------------------------------------------------------------------+
// | FileMgmt Plugin glFusion Auto Upgrade                                     |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | This file has the functions necessary to upgrade FileMgmt to the latest   |
// | release.                                                                  |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2005-2008 by the following authors:                         |
// |                                                                           |
// | Mark R. Evans              - mark@gllabs.org                              |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

if (strpos ($_SERVER['PHP_SELF'], 'glupgrade.inc') !== false)
{
    die ('This file can not be used on its own.');
}

/*
 * If we are being called Geeklog's upgrade routine, there is no
 * COM_errorLog() function available.
 */

if (!function_exists('COM_errorLOG') ) {
    function COM_errorLOG() {
    }
}

/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_filemgmt() {
    global $_TABLES,$_CONF, $_FM_TABLES,$CONF_FM;

    include($_CONF['path'] . '/plugins/filemgmt/config.php');

    $cur_version = DB_getItem($_TABLES['plugins'],'pi_version', "pi_name='filemgmt'");

    switch ( $cur_version ) {
        case '1.3' :
            DB_query("ALTER TABLE {$_FM_TABLES['filemgmt_cat']} ADD `grp_access` mediumint(8) DEFAULT '2' NOT NULL AFTER imgurl");
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '{$CONF_FM['version']}' WHERE pi_name = 'filemgmt'");
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_gl_version = '1.4' WHERE pi_name = 'filemgmt'");

            // Update all the comment records
            $result = DB_query("SELECT cid,sid FROM {$_TABLES['comments']} WHERE type='filemgmt'");
            while (list($cid,$sid) = DB_fetchArray($result)) {
                if (strpos($sid,'fileid_') === FALSE) {
                    $sid = "fileid_{$sid}";
                    DB_query("UPDATE {$_TABLES['comments']} SET sid='$sid' WHERE cid='$cid'");
                }
            }
        case '1.5' :
        case '1.5.1' :
        case '1.5.2' :
        case '1.5.3' :
            DB_query("ALTER TABLE {$_FM_TABLES['filemgmt_cat']} ADD `grp_writeaccess` MEDIUMINT( 8 ) NOT NULL DEFAULT '1'");
        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '1.6' WHERE pi_name = 'filemgmt'");
            return true;
    }
}
?>