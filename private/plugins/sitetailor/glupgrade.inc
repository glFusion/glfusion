<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | glupgrade.inc                                                            |
// |                                                                          |
// | Upgrade routines                                                         |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

if (strpos ($_SERVER['PHP_SELF'], 'glupgrade.inc') !== false)
{
    die ('This file can not be used on its own.');
}

require_once $_CONF['path'] . '/plugins/sitetailor/config.php';

/*
 * If we are being called glFusion's upgrade routine, there is no
 * COM_errorLog() function available.
 */

if (!function_exists('COM_errorLog') ) {
    function COM_errorLog() {
    }
}

function glfusion_upgrade_sitetailor()
{
    global $_TABLES, $_CONF, $_ST_CONF, $_DB_dbms, $TEMPLATE_OPTIONS;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='sitetailor'");

    switch( $currentVersion ) {
        case "0.00" :
            if ( SITETAILOR_upgrade_100() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.00' WHERE pi_name='sitetailor' LIMIT 1");
            }
        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_homepage='http://www.gllabs.org' WHERE pi_name='sitetailor'",1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='" . $_GLFUSION_CONF['version'] . "' WHERE pi_name='sitetailor' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='sitetailor'") == $_ST_CONF['version']) {
        return true;
        exit;
    } else {
        return false;
    }
}

function SITETAILOR_upgrade_100() {
    global $_TABLES, $_CONF, $_ST_CONF;

    $_SQL = array();

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("SiteTailor plugin 1.0.0 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        if (DB_error()) {
            COM_errorLog("SQL Error during Site Tailor plugin update",1);
            return 1;
        }
        next($_SQL);
    }
    return 0;
}
?>