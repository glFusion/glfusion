<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Batch Create Member Albums
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

function MG_selectUsers($page) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01;

    $retval = '';
    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file ('admin','createmembers.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $T->set_block('admin', 'UserRow', 'uRow');
    $rowcounter = 0;

    $start = $page * 50;
    $end   = 50;

    $tres = DB_query("SELECT COUNT(gl.uid) AS count FROM {$_TABLES['users']} AS gl LEFT JOIN {$_TABLES['mg_userprefs']} AS mg ON gl.uid=mg.uid WHERE gl.status = 3 AND gl.uid > 2 AND (mg.member_gallery IS NULL OR mg.member_gallery < 1)");
    $trow = DB_fetchArray($tres);
    $total_records = $trow['count'];

    $sql = "SELECT gl.uid,  gl.status, gl.username, gl.fullname, mg.member_gallery FROM {$_TABLES['users']} AS gl LEFT JOIN {$_TABLES['mg_userprefs']} AS mg ON gl.uid=mg.uid WHERE gl.status = 3 AND gl.uid > 2 AND (mg.member_gallery IS NULL OR mg.member_gallery < 1) ORDER BY gl.username ASC LIMIT $start,$end";

    $result = DB_query($sql);
    $nRows = DB_numRows($result);
    for ($x=0; $x< $nRows; $x++) {
        $row = DB_fetchArray($result);
        $uid = $row['uid'];
        $remote = (SEC_inGroup("Remote Users",$uid) ? '(r)' : '');
        $username = $row['username'];
        $member_gallery = $row['member_gallery'];
        $T->set_var(array(
            'uid'           => $uid,
            'username'      => $username . ' ' . $remote . ' - ' . $row['fullname'],
            'select'        => '<input type="checkbox" name="user[]" value="' . $uid . '"/>',
        ));
        $T->parse('uRow','UserRow',true);
        $rowcounter++;
    }

    $T->set_var(array(
        'lang_userid'   => $LANG_MG01['userid'],
        'lang_username' => $LANG_MG01['username'],
        'lang_select'   => $LANG_MG01['select'],
        'lang_checkall' => $LANG_MG01['check_all'],
        'lang_uncheckall'   => $LANG_MG01['uncheck_all'],
        'lang_save'     => $LANG_MG01['save'],
        'lang_cancel'   => $LANG_MG01['cancel'],
        'lang_reset'    => $LANG_MG01['reset'],
        's_form_action' => $_MG_CONF['admin_url'] . '/createmembers.php',
        'pagenav'        => COM_printPageNavigation($_MG_CONF['admin_url'] . '/createmembers.php', $page+1,ceil($total_records  / 50)),
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_createUsers() {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $_POST;

    if ( !isset($_POST['user']) ) {
        echo COM_refresh($_MG_CONF['admin_url'] . 'createmembers.php');
        exit;
    }

    $numItems = count($_POST['user']);
    for ($i=0; $i < $numItems; $i++) {
        plugin_user_create_mediagallery( $_POST['user'][$i],1);
    }
    echo COM_refresh($_MG_CONF['admin_url'] . 'createmembers.php');
    exit;
}

/**
* Main
*/

$display = '';
$mode = '';

if (isset ($_POST['mode'])) {
    $mode = COM_applyFilter($_POST['mode']);
} else if (isset ($_GET['mode'])) {
    $mode = COM_applyFilter($_GET['mode']);
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
    MG_createUsers();
    exit;
} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    if ( isset($_REQUEST['page']) ) {
        $page = COM_applyFilter($_REQUEST['page'],true) - 1;
        if ( $page < 0 ) {
            $page = 0;
        }
    } else {
        $page = 0;
    }
    $T->set_var(array(
        'admin_body'    => MG_selectUsers($page),
        'title'         => $LANG_MG01['batch_create_members'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Batch_Create_Member_Albums',
    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>