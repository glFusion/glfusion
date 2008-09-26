<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | install.php                                                              |
// |                                                                          |
// | Installs / uninstalls plugin                                             |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006-2008 by the following authors:                        |
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
require_once $_CONF['path'] . '/plugins/captcha/config.php';
require_once $_CONF['path'] . '/plugins/captcha/functions.inc';
require_once $_CONF['path'] . '/plugins/captcha/install.inc';

// Only let Root users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the CAPTCHA install/uninstall page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    $display = COM_siteHeader();
    $display .= COM_startBlock($LANG_CP00['access_denied']);
    $display .= $LANG_CP00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

/*
* Main Function
*/

$action = isset($_POST['action']) ? COM_applyFilter($_POST['action']) : '';

$display = COM_siteHeader();
$T = new Template($_CONF['path'] . 'plugins/captcha/templates');
$T->set_file('install', 'install.thtml');
$T->set_var('install_header', $LANG_CP00['install_header']);
$T->set_var('img',$_CONF['site_url'] . '/captcha/captcha.png');
$T->set_var('cgiurl', $_CONF['site_admin_url'] . '/plugins/captcha/install.php');
$T->set_var('admin_url', $_CONF['site_admin_url'] . '/plugins/captcha/index.php');

if ($action == 'install') {
    if (plugin_install_captcha( $_DB_table_prefix )) {
        $installMsg = sprintf($LANG_CP00['install_success'],$_CONF['site_admin_url'] . '/plugins/captcha/index.php');
        $T->set_var('installmsg1',$installMsg);
    } else {
       	echo COM_refresh ($_CONF['site_admin_url'] . '/plugins.php?msg=72');
    }
} else if ($action == "uninstall") {
   plugin_uninstall_captcha('installed');
   $T->set_var('installmsg1',$LANG_CP00['uninstall_msg']);
}

if (DB_count($_TABLES['plugins'], 'pi_name', 'captcha') == 0) {
    $T->set_var('installmsg2', $LANG_CP00['uninstalled']);
    $T->set_var('readme', $LANG_CP00['readme']);
    $T->set_var('btnmsg', $LANG_CP00['install']);
    $T->set_var('action','install');

    $gl_version = VERSION;
    $php_version = phpversion();

    $glver = sprintf($LANG_CP00['glfusion_check'],$gl_version);
    $phpver = sprintf($LANG_CP00['php_check'],$php_version);
    $T->set_var(array(
        'lang_overview'     => $LANG_CP00['overview'],
        'lang_details'      => $LANG_CP00['details'],
        'cp_requirements'   => $LANG_CP00['preinstall_check'],
        'gl_version'        => $glver,
        'php_version'       => $phpver,
        'install_doc'       => $LANG_CP00['preinstall_confirm'],
    ));
} else {
    echo COM_refresh($_CONF['site_url'] . '/index.php?msg=3&amp;plugin=captcha');
    exit;
}
$T->parse('output','install');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter(true);

echo $display;

?>