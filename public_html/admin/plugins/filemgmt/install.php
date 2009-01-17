<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | install.php                                                              |
// |                                                                          |
// | Install / Uninstall routines                                             |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the FileMgmt Plugin for Geeklog                                 |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
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

require_once '../../../lib-common.php';
require_once $_CONF['path'] . 'plugins/filemgmt/config.php';
require_once $_CONF['path'] . 'plugins/filemgmt/functions.inc';
require_once $_CONF['path'] . 'plugins/filemgmt/install.inc';

// Only let Root users access this script
if (!SEC_inGroup('Root')) {
    $pageHandle->displayAccessError('',$LANG_FM00['access_denied_msg'],'FileMgmt install/uninstall page');
    exit;
}

/*
* Main Function
*/

$display = '';

if ($_REQUEST['action'] == 'uninstall') {
    $uninstall_plugin = 'plugin_uninstall_' . $pi_name;
    if ($uninstall_plugin ()) {
        $pageHandle->redirect ($_CONF['site_admin_url']
                                . '/plugins.php?msg=45');
    } else {
        $pageHandle->redirect($_CONF['site_admin_url']
                                . '/plugins.php?msg=73');
    }

} else if (DB_count ($_TABLES['plugins'], 'pi_name', $pi_name) == 0) {
    // plugin not installed
    if (plugin_compatible_with_this_glfusion_version ()) {
        if ( plugin_install_filemgmt($_DB_table_prefix) ) {
            $pageHandle->redirect ($_CONF['site_admin_url']
                                    . '/plugins.php?msg=44');
        } else {
            $pageHandle->redirect ($_CONF['site_admin_url']
                                    . '/plugins.php?msg=72');
        }
    } else {
        // plugin needs a newer version of glFusion
        $pageHandle->setPageTitle($LANG32[8]);
        $display = COM_startBlock ($LANG32[8])
                 . '<p>' . $LANG32[9] . '</p>'
                 . COM_endBlock ();
        $pageHandle->addContent($display);
    }
} else {
    // plugin already installed
    $pageHandle->setPageTitle($LANG01[77]);
    $pageHandle->addContent(COM_startBlock ($LANG32[6])
             . '<p>' . $LANG32[7] . '</p>'
             . COM_endBlock ());
}

$pageHandle->displayPage();
?>