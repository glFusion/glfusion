<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Edit user album info
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

use \glFusion\Log\Log;

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    Log::write('system',Log::WARNING,"Someone has tried to access the Media Gallery Configuration page.  User id: ".$_USER['uid']);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_editUser( $uid ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01;

    $retval = '';
    $active = 0;
    $quota  = 0;

    $username = DB_getItem ($_TABLES['users'],'username', "uid=" . $uid);
    $result = DB_query("SELECT active,quota FROM {$_TABLES['mg_userprefs']} WHERE uid=" . $uid);
    $nRows  = DB_numRows($result);
    if ( $nRows > 0 ) {
        $row = DB_fetchArray($result);
        $active = $row['active'];
        $quota  = $row['quota'] / 1048576;
    } else {
        $active = 1;
        $quota  = $_MG_CONF['member_quota'] / 1048576;
    }

    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file ('admin','useredit.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $active_select          = '<input type="checkbox" name="active" value="1" ' . ($active ? ' CHECKED' : '') . '/>';

    $T->set_var(array(
        's_form_action'     => $_MG_CONF['admin_url'] . 'edituser.php',
        'lang_user_edit'    => $LANG_MG01['edit_user'],
        'lang_username'     => $LANG_MG01['username'],
        'lang_active'       => $LANG_MG01['active'],
        'lang_quota'        => $LANG_MG01['quota'],
        'lang_save'         => $LANG_MG01['save'],
        'lang_cancel'       => $LANG_MG01['cancel'],
        'lang_reset'        => $LANG_MG01['reset'],
        'lang_unlimited'    => $LANG_MG01['zero_unlimited'],
        'uid'               => $uid,
        'active'            => $active_select,
        'quota'             => $quota,
        'username'          => $username,
    ));
    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_saveUser() {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $_POST;

    $uid    = COM_applyFilter($_POST['uid'],true);
    $quota  = COM_applyFilter($_POST['quota'],true) * 1048576;
    $active = COM_applyFilter($_POST['active'],true);

    $result = DB_query("SELECT uid FROM {$_TABLES['mg_userprefs']} WHERE uid=" . $uid);
    $nRows  = DB_numRows($result);
    if ( $nRows > 0 ) {
        DB_query("UPDATE {$_TABLES['mg_userprefs']} SET quota=" . $quota . ",active=" . $active . " WHERE uid=" . $uid,1);
    } else {
        DB_query("INSERT INTO {$_TABLES['mg_userprefs']} SET uid=" . $uid . ", quota=" . $quota . ",active=" . $active,1);
    }
    echo COM_refresh($_MG_CONF['admin_url'] . 'quotareport.php');
    exit;
}

/**
* Main
*/

$mode = '';
$display = '';

if (isset ($_POST['mode'])) {
    $mode = $_POST['mode'];
} else if (isset ($_GET['mode'])) {
    $mode = $_GET['mode'];
}

$T = new Template($_MG_CONF['template_path'].'/admin');
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],

));

if ($mode == $LANG_MG01['save'] && !empty ($LANG_MG01['save'])) {   // save the config
    MG_saveUser();
    exit;
} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $uid = COM_applyFilter($_GET['uid'],true);
    $T->set_var(array(
        'admin_body'    => MG_editUser($uid),
        'title'         => $LANG_MG01['edit_user'],
    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>