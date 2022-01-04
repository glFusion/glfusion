<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Session Management
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

$display = '';

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


function MG_batchDeleteSession() {
    global $_MG_CONF, $_CONF, $_TABLES, $_POST;

    $numItems = count($_POST['sel']);
    for ($i=0; $i < $numItems; $i++) {
        $sql = "DELETE FROM {$_TABLES['mg_session_items']} WHERE session_id='" . $_POST['sel'][$i] . "'";
        $result = DB_query($sql);
        if ( DB_error() ) {
            Log::write('system',Log::ERROR,"Media Gallery: Error removing session items");
        }
        $sql = "DELETE FROM {$_TABLES['mg_sessions']} WHERE session_id='" . $_POST['sel'][$i] . "'";
        $result = DB_query($sql);
        if ( DB_error() ) {
            Log::write('system',Log::ERRROR,"Media Gallery: Error removing session");
        }
    }

    echo COM_refresh($_MG_CONF['admin_url'] . 'sessions.php');
    exit;
}


function MG_displaySessions() {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01;

    $retval = '';
    $T = new Template($_MG_CONF['template_path'].'/admin');

    $T->set_file (array(
        'sessions'      =>  'sessions.thtml',
        'empty'         =>  'sess_noitems.thtml',
        'sessitems'     =>  'sessitems.thtml'
    ));
    $T->set_var(array(
        'site_url'          => $_CONF['site_url'],
        'lang_select'       => $LANG_MG01['select'],
        'lang_checkall'     => $LANG_MG01['check_all'],
        'lang_uncheckall'   => $LANG_MG01['uncheck_all'],
    ));

    $sql      = "SELECT * FROM {$_TABLES['mg_sessions']} WHERE session_status=1";
    $result   = DB_query($sql);
    $numRows  = DB_numRows($result);
    $rowclass = 0;

    if ( $numRows == 0 ) {
        // we have no active sessions
        $T->set_var(array(
            'lang_no_sessions'  =>  $LANG_MG01['no_sessions']
        ));
        $T->parse('noitems','empty');
    } else {
        $totalSess = $numRows;

        $T->set_block('sessitems', 'sessRow','sRow');

        for ($x = 0; $x < $numRows; $x++ ) {
            $row = DB_fetchArray( $result );

            $res2 = DB_query("SELECT COUNT(id) FROM {$_TABLES['mg_session_items']} WHERE session_id='" . $row['session_id'] . "' AND status=0");
            list($count) = DB_fetchArray($res2);

            $T->set_var(array(
                'row_class'             => ($rowclass % 2) ? '1' : '2',
                'session_id'            => $row['session_id'],
                'session_owner'         => DB_getItem($_TABLES['users'],'username',"uid={$row['session_uid']}"),
                'session_description'   => $row['session_description'],
                'session_continue'      => $_MG_CONF['site_url'] . '/batch.php?mode=continue&amp;sid=' . $row['session_id'],
                'count'                 => $count,
            ));
            $T->parse('sRow','sessRow',true);
            $rowclass++;
        }
        $T->parse('sessitems','sessitems');
    }
    $T->set_var(array(
        's_form_action'     => $_MG_CONF['admin_url'] . 'sessions.php',
        'mode'              => 'sessions',
        'lang_category_manage_help' => $LANG_MG01['category_manage_help'],
        'lang_catid'        => $LANG_MG01['cat_id'],
        'lang_cat_name'     => $LANG_MG01['cat_name'],
        'lang_cat_description' => $LANG_MG01['cat_description'],
        'lang_save'         => $LANG_MG01['save'],
        'lang_cancel'       => $LANG_MG01['cancel'],
        'lang_delete'       => $LANG_MG01['delete'],
        'lang_select'       => $LANG_MG01['select'],
        'lang_checkall'     => $LANG_MG01['check_all'],
        'lang_uncheckall'   => $LANG_MG01['uncheck_all'],
        'lang_session_id'   => $LANG_MG01['cat_id'],
        'lang_session_description' => $LANG_MG01['description'],
        'lang_session_owner'    => $LANG_MG01['owner'],
        'lang_session_count'    => $LANG_MG01['count'],
        'lang_action'           => $LANG_MG01['action'],
    ));

    $T->parse('output','sessions');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
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

if ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} elseif ($mode == $LANG_MG01['delete'] && !empty ($LANG_MG01['delete'])) {
    MG_batchDeleteSession();
} else {
    $T->set_var(array(
        'admin_body'    => MG_displaySessions(),
        'title'         => $LANG_MG01['batch_sessions'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Paused_Sessions',
    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>