<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Mass Delete
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

function MG_massDelete() {
    global $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01;

    $retval = '';
    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file ('admin','massdelete.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $T->set_var(array(
        'album_list'    => $MG_albums[0]->showSelectTree(0),
        's_form_action' => $_MG_CONF['admin_url'] . 'massdelete.php',
        'lang_save'     => $LANG_MG01['save'],
        'lang_cancel'   => $LANG_MG01['cancel'],
        'lang_reset'    => $LANG_MG01['reset'],
        'lang_delete_confirm' => $LANG_MG01['delete_item_confirm'],
        'lang_delete'   => $LANG_MG01['delete'],
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}


function MG_MassdeleteAlbum( $album_id ) {
    global $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01;

    // need to check perms here...

    if ( $MG_albums[$album_id]->access != 3) {
        Log::write('system',Log::WARNING,"Someone has tried to delete an album without proper permissions.  User id: ".$_USER['uid']);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }
    MG_MassdeleteChildAlbums( $album_id );

    if ( $_MG_CONF['member_albums'] == 1 && $MG_albums[$album_id]->parent == $_MG_CONF['member_album_root'] ) {
        $result = DB_query("SELECT * FROM {$_TABLES['mg_albums']} WHERE owner_id=" . $MG_albums[$album_id]->owner_id . " AND album_parent=" . $MG_albums[$album_id]->parent);
        $numRows = DB_numRows($result);
        if ( $numRows == 0 ) {
            DB_query("UPDATE {$_TABLES['mg_userprefs']} SET member_gallery=0 WHERE uid=" . $MG_albums[$album_id]->owner_id,1);
        }
    }
    MG_initAlbums();
    require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
    MG_buildFullRSS( );

}



/**
* Recursivly deletes all albums and child albums
*
* @param    int     album_id    album id to delete
* @return   int     true for success or false for failure
*
*/
function MG_MassdeleteChildAlbums( $album_id ){
    global $_CONF, $_MG_CONF, $_TABLES, $_USER;

    $sql = "SELECT * FROM {$_TABLES['mg_albums']} WHERE album_parent=" . $album_id;
    $aResult = DB_query( $sql );
    $rowCount = DB_numRows($aResult);
    for ( $z=0; $z < $rowCount; $z++ ) {
        $row = DB_fetchArray( $aResult );
        MG_MassdeleteChildAlbums( $row['album_id'] );
    }

    $sql = "SELECT ma.media_id, m.media_filename, m.media_mime_ext
            FROM " . $_TABLES['mg_media_albums'] .
            " as ma LEFT JOIN " . $_TABLES['mg_media'] .
            " as m ON ma.media_id=m.media_id
            WHERE ma.album_id = " . $album_id;

    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );
    $mediarow = array();
    for ( $i=0; $i < $nRows; $i++) {
      $row = DB_fetchArray( $result );
      $mediarow[] = $row;
    }
    if ( count( $mediarow ) != 0 ) {
        for ( $i = 0; $i < count( $mediarow ); $i++ ) {
            $sql = "SELECT COUNT(media_id) AS count FROM " . $_TABLES['mg_media_albums'] . "  WHERE media_id = '" . $mediarow[$i]['media_id'] . "'";
            $result = DB_query( $sql );
            $row = DB_fetchArray( $result );
            if ( $row['count'] <= 1 ) {
                @unlink($_MG_CONF['path_mediaobjects'] . 'tn/'   . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . '.jpg');
                @unlink($_MG_CONF['path_mediaobjects'] . 'disp/' . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . '.jpg');
                @unlink($_MG_CONF['path_mediaobjects'] . 'orig/' . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . '.' . $mediarow[$i]['media_mime_ext']);
                $sql = "DELETE FROM " . $_TABLES['mg_media'] . "  WHERE media_id = '" . $mediarow[$i]['media_id'] . "'";
                DB_query( $sql );
                DB_delete($_TABLES['comments'], 'sid', $mediarow[$i]['media_id']);
                DB_delete($_TABLES['mg_playback_options'],'media_id', $mediarow[$i]['media_id']);
            }
        }
    }
    $sql = "DELETE FROM " . $_TABLES['mg_media_albums'] . " WHERE album_id = " . $album_id;
    DB_query( $sql );
    $sql = "DELETE FROM " . $_TABLES['mg_albums'] . " WHERE album_id = " . $album_id;
    DB_query( $sql );
    $feedname = sprintf($_MG_CONF['rss_feed_name'] . "%06d", $album_id);
    @unlink($_MG_CONF['path_html'] . 'rss/' . $feedname . '.rdf');
}


function MG_massDeleteAlbums( $aid ) {
    global $_TABLES, $_MG_CONF, $_CONF, $MG_albums, $_POST;

    $children = $MG_albums[$aid]->getChildren();
    $numItems = count($children);
    for ($x=0; $x < $numItems; $x++) {
        $i = $MG_albums[$children[$x]]->id;
        if ( isset($_POST['album'][$i]) && $_POST['album'][$i] == 1 ) {
            MG_MassdeleteAlbum($MG_albums[$children[$x]]->id);
        } else {
            MG_massDeleteAlbums($MG_albums[$children[$x]]->id);
        }
    }
}

/**
* Main
*/

$mode = isset($_REQUEST['mode']) ? COM_applyFilter ($_REQUEST['mode']) : '';
$display = '';
$mode = '';

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

if ($mode == $LANG_MG01['delete'] && !empty ($LANG_MG01['delete'])) {
    $T->set_var(array(
        'admin_body'    => MG_massDeleteAlbums(0),
    ));
    echo COM_refresh($_MG_CONF['admin_url'] . '/index.php?msg=15');
} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_massDelete(),
        'title'         => $LANG_MG01['mass_delete_help'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" border="0" alt="?">',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Batch_Delete_Albums',

    ));
}
$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>