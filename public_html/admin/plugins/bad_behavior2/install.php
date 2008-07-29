<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | install.php                                                              |
// |                                                                          |
// | Plugin Installation.                                                     |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2008 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Dirk Haun         - dirk AT haun-online DOT de                  |
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

require_once('../../../lib-common.php');
require_once($_CONF['path'] . '/plugins/bad_behavior2/functions.inc');
require_once($_CONF['path'] . '/plugins/bad_behavior2/install.inc');

// Only let Root users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Bad Behavior2 install/uninstall page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    $display = COM_siteHeader ('menu', $LANG_ACCESS['accessdenied'])
             . COM_startBlock ($LANG_ACCESS['accessdenied'])
             . $LANG_ACCESS['plugin_access_denied_msg']
             . COM_endBlock ()
             . COM_siteFooter ();
    echo $display;
    exit;
}

/**
* Checks the requirements for this plugin and if it is compatible with this
* version of glFusion.
*
* @return   boolean     true = proceed with install, false = not compatible
*
*/
function plugin_compatible_with_this_glfusion_version ()
{
    if (!function_exists ('COM_numberFormat')) {
        return false;
    }

    return true;
}

$action = isset($_GET['action']) ? COM_applyFilter($_GET['action']) : '';

$display = '';

if ($action == 'install') {
    if (DB_count ($_TABLES['plugins'], 'pi_name', 'bad_behavior2') == 0) {
        if (plugin_compatible_with_this_glfusion_version ()) {
            if (plugin_install_bad_behavior2 ($_DB_table_prefix)) {
                $display = COM_refresh ($_CONF['site_admin_url']
                                        . '/plugins.php?msg=44');
            } else {
                $display = COM_refresh ($_CONF['site_admin_url']
                                        . '/plugins.php?msg=72');
            }
        } else {
            // plugin needs a newer version of glFusion
            $display .= COM_siteHeader ('menu', $LANG32[8])
                     . COM_startBlock ($LANG32[8])
                     . '<p>' . $LANG32[9] . '</p>'
                     . COM_endBlock ()
                     . COM_siteFooter ();
        }
    }
} else if ($action == "uninstall") {
    if (plugin_uninstall_bad_behavior2 ()) {
        $display = COM_refresh ($_CONF['site_admin_url']
                                . '/plugins.php?msg=45');
    } else {
        $display = COM_refresh ($_CONF['site_admin_url']
                                . '/plugins.php?msg=73');
    }
} else {
    $display = COM_refresh($_CONF['site_admin_url']);
}

echo $display;

?>