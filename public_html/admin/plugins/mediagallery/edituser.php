<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// | edit user album info.                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2010 by the following authors:                        |
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

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
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

    $T = new Template($_MG_CONF['template_path']);
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

$mode = COM_applyFilter ($_REQUEST['mode']);
$display = '';
$mode = '';

if (isset ($_POST['mode'])) {
    $mode = $_POST['mode'];
} else if (isset ($_GET['mode'])) {
    $mode = $_GET['mode'];
}

$display = COM_siteHeader();
$T = new Template($_MG_CONF['template_path']);
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['version'],

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
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>