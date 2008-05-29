<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin                                                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans              - mark at gllabs.org                          |
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
require_once $_CONF['path'] . '/plugins/sitetailor/config.php';
require_once $_CONF['path'] . '/plugins/sitetailor/functions.inc';

function SITETAILOR_return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
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

// Only let Root users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Site Tailor install/uninstall page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display = COM_siteHeader();
    $display .= COM_startBlock($LANG_ST00['access_denied']);
    $display .= $LANG_ST00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

/**
* Puts the datastructures for this plugin into the Geeklog database
*
* Note: Corresponding uninstall routine is in functions.inc
*
* @return   boolean True if successful False otherwise
*
*/
function plugin_install_sitetailor()
{
    global $_CONF, $_DB_table_prefix;
    global $pi_name, $pi_version, $gl_version, $pi_url;
    global $DEFVALUES, $NEWFEATURE;
    global $_ST_CONF;

    require_once $_CONF['path'] .'/plugins/sitetailor/install.inc';

    $rc = glfusion_install_sitetailor($_DB_table_prefix);

    return ($rc);
}

/*
* Main Function
*/

$action = COM_applyFilter($_POST['action']);

$display = COM_siteHeader();
$T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
$T->set_file('install', 'install.thtml');
$T->set_var('install_header', $LANG_ST00['install_header']);
$T->set_var('img',ST_getImageFile('sitetailor.png'));
$T->set_var('cgiurl', $_CONF['site_admin_url'] . '/plugins/sitetailor/install.php');
$T->set_var('admin_url', $_CONF['site_admin_url'] . '/plugins/sitetailor/index.php');

if ($action == 'install') {
    if (plugin_install_sitetailor()) {
        $installMsg = sprintf($LANG_ST00['install_success'],$_CONF['site_admin_url'] . '/plugins/sitetailor/index.php');
        $T->set_var('installmsg1',$installMsg);
    } else {
       	echo COM_refresh ($_CONF['site_admin_url'] . '/plugins.php?msg=72');
    }
} else if ($action == "uninstall") {
   plugin_uninstall_sitetailor('installed');
   $T->set_var('installmsg1',$LANG_ST00['uninstall_msg']);
}

if (DB_count($_TABLES['plugins'], 'pi_name', 'sitetailor') == 0) {

    $errCheck = 0;

    $T->set_var('installmsg2', $LANG_ST00['uninstalled']);
    $T->set_var('readme', $LANG_ST00['readme']);
    $T->set_var('btnmsg', $LANG_ST00['install']);
    $T->set_var('action','install');

    $gl_version     = glFusion_VERSION;
    $php_version    = phpversion();
    if (is_array($TEMPLATE_OPTIONS)) {
        $tc_installed = 1;
    } else {
        $tc_installed = 0;
    }
    $memory_limit = SITETAILOR_return_bytes(ini_get('memory_limit'));

    $glversion = explode(".", glFusion_VERSION);
    if ( $glversion[1] < 4 ) {
        $versionCheck = '<div style="background-color:#ffff00;color:#000000;vertical-align:middle;padding:5px;"><img src="images/redX.png" alt="error" style="padding:5px;vertical-align:middle;">&nbsp;' . $LANG_ST00['gl_version_error'] . '</div>';
        $errCheck++;
    } else {
        $versionCheck = '<div style="vertical-align:middle;padding:5px;"><img src="images/check.png" alt="OK" style="padding:5px;vertical-align:middle;">' . $LANG_ST00['gl_version_ok'] . '</div>';
    }
    if ( $tc_installed == 0 ) {
        $errCheck++;
        $cacheCheck = '<div style="background-color:#ffff00;color:#000000;vertical-align:middle;padding:5px;"><img src="images/redX.png" alt="error" style="padding:5px;vertical-align:middle;">&nbsp;' . $LANG_ST00['tc_error'] . '</div>';
    } else {
        $cacheCheck = '<div style="vertical-align:middle;padding:5px;"><img src="images/check.png" alt="OK" style="padding:5px;vertical-align:middle;">' . $LANG_ST00['tc_ok'] . '</div>';
    }
    if ( $memory_limit < 50331648 ) {
        $memoryCheck = '<div style="background-color:#ffff00;color:#000000;vertical-align:middle;padding:5px;"><img src="images/redX.png" alt="error" style="padding:5px;vertical-align:middle;">&nbsp;' . $LANG_ST00['ml_error'] . '</div>';
    } else {
        $memoryCheck = '<div style="vertical-align:middle;padding:5px;"><img src="images/check.png" alt="OK" style="padding:5px;vertical-align:middle;">' . $LANG_ST00['ml_ok'] . '</div>';
    }

    $glver  = sprintf($LANG_ST00['geeklog_check'],$gl_version);
    $phpver = sprintf($LANG_ST00['php_check'],$php_version);

    $T->set_var(array(
        'lang_overview'     => $LANG_ST00['overview'],
        'mg_requirements'   => $LANG_ST00['preinstall_check'],
        'gl_version'        => $glver,
        'php_version'       => $phpver,
        'tc_installed'      => $tc_installed == 0 ? $LANG_ST01['no'] : $LANG_ST01['yes'],
        'install_doc'       => $LANG_ST00['preinstall_confirm'],
        'lang_template_cache'    => $LANG_ST00['template_cache'],
        'version_check'     => $versionCheck,
        'cache_check'       => $cacheCheck,
        'memory_check'      => $memoryCheck,
        'lang_env_check'    => $LANG_ST00['env_check'],
    ));
    if ( $errCheck == 0 ) {
        $T->set_var('btnmsg', $LANG_ST00['install']);
        $T->set_var('action','install');
        $T->set_var('errormessage','');
    } else {
        $T->set_var('btnmsg', $LANG_ST00['recheck_env']);
        $T->set_var('action','recheck');
        $T->set_var('errormessage',$LANG_ST00['fix_install']);
    }
} else {
   echo COM_refresh($_CONF['site_admin_url'] . '/plugins/sitetailor/index.php?mode=install');
   exit;
}
$T->parse('output','install');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter(true);

echo $display;
?>