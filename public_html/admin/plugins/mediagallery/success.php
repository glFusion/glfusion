<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | Upgrade Successful Page                                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2015 by the following authors:                        |
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
//

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

function MG_return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

$display = '';

// Only let admin users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Upgrade page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: {$_SERVER['REMOTE_ADDR']}",1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

// Sucess!

$display = '';

if (is_array($TEMPLATE_OPTIONS)) {
    $tc_installed = 1;
} else {
    $tc_installed = 0;
}
$memory_limit = MG_return_bytes(ini_get('memory_limit'));

if ( $tc_installed == 0 ) {
    $noCache=1;;
    $cacheCheck = '<div style="background-color:#ffff00;color:#000000;vertical-align:middle;padding:5px;"><img src="redX.png" alt="error" style="padding:5px;vertical-align:middle;">&nbsp;' . $LANG_MG00['tc_error'] . '</div>';
} else {
    $noCache=0;
    $cacheCheck = '<div style="vertical-align:middle;padding:5px;"><img src="check.png" alt="OK" style="padding:5px;vertical-align:middle;">' . $LANG_MG00['tc_ok'] . '</div>';
}
if ( $memory_limit < 50331648 ) {
    $noMemory = 1;
    $memoryCheck = '<div style="background-color:#ffff00;color:#000000;vertical-align:middle;padding:5px;"><img src="redX.png" alt="error" style="padding:5px;vertical-align:middle;">&nbsp;' . $LANG_MG00['ml_error'] . '</div>';
} else {
    $noMemory = 0;
    $memoryCheck = '<div style="vertical-align:middle;padding:5px;"><img src="check.png" alt="OK" style="padding:5px;vertical-align:middle;">' . $LANG_MG00['ml_ok'] . '</div>';
}

$T = new Template($_MG_CONF['template_path'].'/admin');
$T->set_file (array ('admin' => 'success.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'cache_check'       => $cacheCheck,
    'memory_check'      => $memoryCheck,
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],
    'thank_you'         => $LANG_MG00['thank_you'],
    'support'           => $LANG_MG00['support'],
    'success_upgrade'   => $LANG_MG00['success_upgrade'],
));

if ( $noCache ) {
    $T->set_var('need_cache',$LANG_MG00['need_cache']);
}
if ( $noMemory ) {
    $T->set_var('need_memory',$LANG_MG00['need_memory']);
}

$T->parse('output', 'admin');
$display  = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;

?>