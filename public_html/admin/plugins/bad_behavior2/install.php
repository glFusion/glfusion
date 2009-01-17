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
// | Copyright (C) 2008 by the following authors:                             |
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
    $pageHandle->displayAccessError($LANG_ACCESS['accessdenied'],$LANG_ACCESS['plugin_access_denied_msg'],'bad behavior install screen');
    exit;
}

$action = $inputHandler->getVar('strict','action','get','');


$display = '';

if ($action == 'install') {
    if (DB_count ($_TABLES['plugins'], 'pi_name', 'bad_behavior2') == 0) {
        if (bad_behavior2_compatible_with_this_glfusion_version ()) {
            if ( plugin_install_bad_behavior2($_DB_table_prefix) ) {
                $pageHandle->redirect ($_CONF['site_admin_url']
                                        . '/plugins.php?msg=44');
            } else {
                $pageHandle->redirect ($_CONF['site_admin_url']
                                        . '/plugins.php?msg=72');
            }
        } else {
            // plugin needs a newer version of glFusion
            $pageHandle->addContent(COM_startBlock ($LANG32[8])
                     . '<p>' . $LANG32[9] . '</p>'
                     . COM_endBlock ());
        }
    }
} else if ($action == "uninstall") {
    if (plugin_uninstall_bad_behavior2 ()) {
        $pageHandle->redirect ($_CONF['site_admin_url']
                                . '/plugins.php?msg=45');
    } else {
        $pageHandle->redirect ($_CONF['site_admin_url']
                                . '/plugins.php?msg=73');
    }
} else {
    $pageHandle->redirect($_CONF['site_admin_url']);
}

$pageHandle->displayPage();

?>