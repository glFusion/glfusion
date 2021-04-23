<?php
/**
* glFusion CMS - Links Plugin
*
* Installation
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2012-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs         tony AT tonybibbs DOT com
*  Tom Willett        tom AT pigstye DOT net
*  Blaine Lang        blaine AT portalparts DOT com
*  Dirk Haun          dirk AT haun-online DOT de
*  Vincent Furia      vinny01 AT users DOT sourceforge DOT net
*  Oliver Spiesshofer oliver AT spiesshofer DOT com
*  Euan McKay         info AT heatherengineering DOT com
*
*/

require_once '../../../lib-common.php';
require_once $_CONF['path'].'/plugins/links/autoinstall.php';

use \glFusion\Log\Log;

USES_lib_install();

if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    log::logAccessViolation('Links Plugin Installer');
    $display = COM_siteHeader ('menu', $LANG_ACCESS['accessdenied'])
             . COM_startBlock ($LANG_ACCESS['accessdenied'])
             . $LANG_ACCESS['plugin_access_denied_msg']
             . COM_endBlock ()
             . COM_siteFooter ();
    echo $display;
    exit;
}

/**
* Main Function
*/

if (SEC_checkToken()) {
    $action = COM_applyFilter($_GET['action']);
    if ($action == 'install') {
        if (plugin_install_links()) {
    		// Redirects to the plugin editor
    		echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=44');
    		exit;
        } else {
    		echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=72');
    		exit;
        }
    } else if ($action == 'uninstall') {
    	if (plugin_uninstall_links('installed')) {
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