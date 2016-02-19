<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | install.php                                                              |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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
require_once $_CONF['path'].'/plugins/captcha/autoinstall.php';

USES_lib_install();

if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to access the CAPTCHA install/uninstall page without the proper permissions.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    $display = COM_siteHeader ('menu', $LANG_ACCESS['accessdenied'])
             . COM_startBlock ($LANG_ACCESS['accessdenied'])
             . $LANG_ACCESS['plugin_access_denied_msg']
             . COM_endBlock ()
             . COM_siteFooter ();
    echo $display;
    exit;
}

/*
* Main Function
*/

if (SEC_checkToken()) {
    $action = COM_applyFilter($_GET['action']);
    if ($action == 'install') {
        if (plugin_install_captcha()) {
    		// Redirects to the plugin editor
    		echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=44');
    		exit;
        } else {
    		echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=72');
    		exit;
        }
    } else if ($action == 'uninstall') {
    	if (plugin_uninstall_captcha('installed')) {
    		/**
    		* Redirects to the plugin editor
    		*/
    		echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=45');
    		exit;
    	} else {
    		echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=73');
    		exit;
    	}
    }
}

echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php');
?>