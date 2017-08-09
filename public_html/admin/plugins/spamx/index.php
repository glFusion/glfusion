<?php
// +--------------------------------------------------------------------------+
// | Spam-X Plugin - glFusion CMS                                             |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | Administration Page.                                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Tom Willett       - twillett@users.sourceforge.net              |
// |          Blaine Lang       - langmail@sympatico.ca                       |
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
require_once '../../auth.inc.php';

// Only let admin users access this page
if (!SEC_hasRights ('spamx.admin')) {
    COM_accessLog ("Someone has tried to access the Spam-X Admin page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: {$_SERVER['REMOTE_ADDR']}", 1);
    $display = COM_siteHeader ('menu', $LANG_SX00['access_denied']);
    $display .= COM_startBlock ($LANG_SX00['access_denied']);
    $display .= $LANG_SX00['access_denied_msg'];
    $display .= COM_endBlock ();
    $display .= COM_siteFooter (true);
    echo $display;
    exit;
}

/**
* Main
*/

USES_lib_admin();

$retval = '';

$menu_arr = array (
    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['plugin_name']),
    array('url' => $_CONF['site_admin_url'] . '/index.php','text' => $LANG_ADMIN['admin_home']),
);

$retval .= ADMIN_createMenu($menu_arr, $LANG_SX00['instructions'],$_CONF['site_admin_url'] . '/plugins/spamx/images/spamx.png');

$files = array ();
if ($dir = @opendir ($_CONF['path'] . 'plugins/spamx/modules/')) {
    while (($file = readdir ($dir)) !== false) {
        if (is_file ($_CONF['path'] . 'plugins/spamx/modules/' . $file)) {
            if (substr ($file, -16) == '.Admin.class.php') {
                $tmp = str_replace ('.Admin.class.php', '', $file);
                array_push ($files, $tmp);
            }
        }
    }
    closedir ($dir);
}

$header_arr = array(
    array(
        'text'  => $LANG_confignames['spamx']['action'],
        'field' => 'title',
    ),
);

$data_arr = array();

foreach ($files as $file) {
    require_once ($_CONF['path'] . 'plugins/spamx/modules/' . $file . '.Admin.class.php');
    $CM = new $file;
    $data_arr[] = array(
        'title'    => COM_createLink(
            $CM->link(),
            $_CONF['site_admin_url'] . '/plugins/spamx/index.php?command=' . $file
        ),
    );
}
$data_arr[] = array(
    'title'    => COM_createLink(
                    $LANG_SX00['documentation'],
                    $_CONF['site_url'] . '/docs/english/spamx.html',
                    array('target'=>'_blank')
                  ),
);
$retval .= ADMIN_simpleList(null, $header_arr, null, $data_arr);

$display = COM_siteHeader ('menu', $LANG_SX00['plugin_name']);

$display .= $retval;

if (isset ($_REQUEST['command'])) {
    $CM = new $_REQUEST['command'];
    $display .= $CM->display();
}

$display .= COM_siteFooter();
echo $display;
?>