<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Batch Purge Member Albums
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

function MG_selectAlbums() {
    global $MG_albums, $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $LANG_MG07;

    // start by building an array of all site users (active)

    $result = DB_query("SELECT * FROM {$_TABLES['users']} AS users LEFT JOIN {$_TABLES['userinfo']} AS userinfo ON users.uid=userinfo.uid WHERE users.status=3");

    while ( $U = DB_fetchArray($result)) {
        $siteUsers[$U['uid']]['lastlogin'] = $U['lastlogin'];
        $siteUsers[$U['uid']]['username']  = $U['username'];
        $siteUsers[$U['uid']]['fullname']  = $U['fullname'];
    }

    $retval = '';
    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file ('admin','purgealbums.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $T->set_block('admin', 'UserRow', 'uRow');
    $rowcounter = 0;
    $rowclass = 0;

    // now process all the albums directly off the album_root
    //  we will not call this recursively, we only care about those off the root

    $children = $MG_albums[$_MG_CONF['member_album_root']]->getChildren();
    $numItems = count($children);
    for ($x=0; $x < $numItems; $x++) {
        if ( $MG_albums[$children[$x]]->getMediaCount() == 0 ) {
            if ($siteUsers[ $MG_albums[$children[$x]]->owner_id ]['lastlogin'] == 0 ) {
                $lastlogin[0] = $LANG_MG07['never'];
            } else {
                $lastlogin = MG_getUserDateTimeFormat($siteUsers[ $MG_albums[$children[$x]]->owner_id ]['lastlogin']);
            }
            $T->set_var(array(
                'select'        => '<input type="checkbox" name="album[]" value="' . $MG_albums[$children[$x]]->id . '">',
                'aid'       =>  $MG_albums[$children[$x]]->id,
                'title'     =>  $MG_albums[$children[$x]]->title,
                'owner'     =>  $siteUsers[ $MG_albums[$children[$x]]->owner_id ]['username'] . '/' . $siteUsers[ $MG_albums[$children[$x]]->owner_id ]['fullname'],
                'lastlogin' =>  $lastlogin[0],
                'rowclass'  =>  ($rowclass % 2 ? '1' : '2'),
            ));
            $T->parse('uRow','UserRow',true);
            $rowcounter++;
        }
    }

    $T->set_var(array(
        'lang_last_login'   => $LANG_MG01['last_login'],
        'lang_album_title'  => $LANG_MG01['album_title'],
        'lang_userid'       => $LANG_MG01['userid'],
        'lang_username'     => $LANG_MG01['username'],
        'lang_select'       => $LANG_MG01['select'],
        'lang_checkall'     => $LANG_MG01['check_all'],
        'lang_uncheckall'   => $LANG_MG01['uncheck_all'],
        'lang_delete'       => $LANG_MG01['delete'],
        'lang_cancel'       => $LANG_MG01['cancel'],
        'lang_reset'        => $LANG_MG01['reset'],
        's_form_action'     => $_MG_CONF['admin_url'] . 'purgealbums.php',
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_purgeMemberAlbums() {
    global $MG_albums, $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $_POST;

    $numItems = count($_POST['album']);
    for ($i=0; $i < $numItems; $i++) {
        // grab owner ID
        $result = DB_query("SELECT owner_id FROM {$_TABLES['mg_albums']} WHERE album_id=" . (int) COM_applyFilter($_POST['album'][$i],true));
        $numRows = DB_numRows($result);
        if ( $numRows > 0 ) {
            list($owner_id) = DB_fetchArray($result);
            DB_query("UPDATE {$_TABLES['mg_userprefs']} SET member_gallery=0 WHERE uid=" . $owner_id,1);
        }
        MG_deleteChildAlbums( (int) COM_applyFilter($_POST['album'][$i],true) );
    }
    MG_initAlbums();
    require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
    MG_buildFullRSS( );

    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=8');
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

if ($mode == $LANG_MG01['delete'] && !empty ($LANG_MG01['delete'])) {
    require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
    MG_purgeMemberAlbums();
    exit;
} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_selectAlbums(),
        'title'         => $LANG_MG01['purge_mem_albums_help'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Purge_Member_Albums',
    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>