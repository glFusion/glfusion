<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Album Sort
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

/**
* sorts the albums into the proper order
*
* @param    int     parent  parent album id
* @return   int     true for success or false for failure
*
*/
function MG_reorderAlbum( $parent = 0)
{
    global $_TABLES, $_POST, $_CONF;

    $sql = "SELECT album_id, album_order
            FROM " . $_TABLES['mg_albums'] .
            " WHERE album_parent=" . intval($parent) . "
            ORDER BY album_order ASC";

    $result = DB_query( $sql );
    if ( DB_error() ) {
        return false;
    }
    $nRows = DB_numRows($result);
    $row = array();
    for ($x=0; $x < $nRows; $x++)
    {
        $row[] = DB_fetchArray($result);
    }
    $i = 10;
    for ($x = 0; $x < $nRows; $x++ )
    {
        $sql = "UPDATE " . $_TABLES['mg_albums'] .
                " SET album_order = $i
                WHERE album_id = " . $row[$x]['album_id'];
        DB_query( $sql );
        $i += 10;
    }
    return true;
}



/**
* sorts all albums starting at the $parent level
*
* @param    int     parent  parent album id
* @param    int     page number to start
* @return   string  HTML for list of albums
*
*/
function MG_sortAlbums( $parent=0, $actionURL = '' ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG01;

    $retval = '';

    $retval .= COM_startBlock ($LANG_MG01['sort_albums'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $T = new Template( MG_getTemplatePath($parent) );
    $T->set_file (array ('admin' => 'sortalbum.thtml'));

    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);
    $T->set_var(array(
        'lang_new_album'        => $LANG_MG01['new_album'],
        'lang_upload_media'     => $LANG_MG01['upload_media'],
        'lang_ftp_media'        => $LANG_MG01['ftp_media'],
        'lang_usage_reports'    => $LANG_MG01['usage_reports'],
        'lang_configuration'    => $LANG_MG01['configuration'],
        'lang_media_queue'      => $LANG_MG01['media_queue'],
        'lang_admin_home'       => $LANG_MG01['admin_home'],
        'lang_album_name_desc'  => $LANG_MG01['album_name_desc'],
        'lang_count'            => $LANG_MG01['count'],
        'lang_order'            => $LANG_MG01['order'],
        'lang_action'           => $LANG_MG01['action'],
        'lang_move_up'          => $LANG_MG01['move_up'],
        'lang_move_down'        => $LANG_MG01['move_down'],
        'lang_edit'             => $LANG_MG01['edit'],
        'lang_delete'           => $LANG_MG01['delete'],
        'lang_caption'          => $LANG_MG01['caption'],
        'lang_images'           => $LANG_MG01['images'],
        'lang_admin_main_help'  => $LANG_MG01['admin_main_help'],
        'lang_parent_album'     => $LANG_MG01['parent_album'],
        'lang_save'             => $LANG_MG01['save'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        'lang_reset'            => $LANG_MG01['reset'],
        's_form_action'         => $_MG_CONF['site_url'] . '/admin.php',
    ));

    $rowcounter = 1;

    $sql = "SELECT a.album_id, a.album_title as album_title, a.album_desc, a.album_order, a.owner_id, a.group_id, a.perm_owner,a.perm_group,a.perm_members,a.perm_anon,
            COUNT(ma.media_id) AS media_count, album_cover
            FROM " . $_TABLES['mg_albums'] . " as a LEFT JOIN " . $_TABLES['mg_media_albums'] .
            " as ma ON a.album_id=ma.album_id WHERE album_parent=".intval($parent)."
            GROUP BY a.album_id
            ORDER BY a.album_order DESC";

    $result = DB_query( $sql, 1 );
    if ( DB_error() ) {
        Log::write('system',Log::ERROR,"Media Gallery Error - Unable to build album select list");
        $T->parse('output', 'admin');
        $retval .= $T->finish($T->get_var('output'));
        $retval .= 'There was an error in the SQL statement - Check the error.log';
        $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
        return $retval;
    }
    $nRows  = DB_numRows( $result );

    $T->set_block('admin', 'AlbumRow', 'ARow');

    for ($i = 0; $i < $nRows; $i++ ) {
        $row = DB_fetchArray( $result );

        $access = SEC_hasAccess (   $row['owner_id'],
                                    $row['group_id'],
                                    $row['perm_owner'],
                                    $row['perm_group'],
                                    $row['perm_members'],
                                    $row['perm_anon']
                                );

        if ( $access != 3  && !SEC_hasRights('mediagallery.admin')) {    // only allow access to items that we can edit
            continue;
        }

        $subalbums = DB_count($_TABLES['mg_albums'], 'album_parent', $row['album_id']);

        if ( $subalbums ) {
            $albumTitle = strip_tags($row['album_title']) . ' - ' . '<a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=albumsort&amp;album_id=' . $row['album_id'] . '">' . $LANG_MG01['subalbums'] . ' (' . $subalbums . ')</a>';
        } else {
            $albumTitle = strip_tags($row['album_title']);
        }

        $T->set_var(array(
            'row_class'     => ($rowcounter % 2) ? '2' : '1',
            'album_id'      => $row['album_id'],
            'album_title'   => $albumTitle,
            'album_desc'    => $row['album_desc'],
            'media_count'   => $row['media_count'],
            'album_order'   => $row['album_order'],
        ));
        $T->parse('ARow','AlbumRow',true);
        $rowcounter++;
    }

    if ( $parent != 0 ) {
        $parent_album_title = DB_getItem($_TABLES['mg_albums'],'album_title','album_id=' . intval($parent));
        $parent_parent = DB_getItem($_TABLES['mg_albums'], 'album_parent', 'album_id=' . intval($parent));
        $parent_album = '<a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=albumsort&amp;album_id=' . $parent_parent . '">' . strip_tags($parent_album_title) . '</a>';
        $T->set_var('parent_album', '<h1>' . $LANG_MG01['parent_album'] . ' : ' . $parent_album . '</h1>');
    }

    $T->set_var('parent_id',$parent);

    $mqueue_count = DB_count($_TABLES['mg_mediaqueue']);
    $T->set_var(array(
        'mqueue_count'  =>  $mqueue_count
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}


/**
* saves user album list in specified order
*
* @param    int     album_id    parent album id to begin sort
* @return   redirects to index page
*
*/
function MG_saveAlbumSort( $album_id ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $_POST, $REMOTE_ADDR;

    // check permissions...

    if ( !SEC_hasRights('mediagallery.admin')) {
        Log::write('system',Log::WARNING,"MediaGallery: Someone has tried to sort albums in Media Gallery.  User id: ".$_USER['uid']." IP: ".$_SERVER['REAL_ADDR']);
        return(MG_genericError( $LANG_MG00['access_denied_msg'] ));
    }

    $parent = COM_applyFilter($_POST['parent_id'],true);

    $numItems = count($_POST['aid']);

    for ($i=0; $i < $numItems; $i++) {
        $album[$i]['aid'] = $_POST['aid'][$i];
        $album[$i]['seq'] = $_POST['seq'][$i];
    }

    for ( $i=0; $i < $numItems; $i++ ) {
        $sql = "UPDATE {$_TABLES['mg_albums']} SET album_order=" . intval($album[$i]['seq']) . " WHERE album_id=" . intval($album[$i]['aid']);
        DB_query($sql);
        if ( DB_error() ) {
            Log::write('system',Log::ERROR,"MediaGallery: Error updating album sort order MG_saveAlbumSort()");
        }
    }

    MG_reorderAlbum( $parent );

    echo COM_refresh($_MG_CONF['site_url'] . '/admin.php?album_id=0&mode=albumsort');
    exit;
}

function MG_staticSortMedia( $album_id, $actionURL='' ) {
    global $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    $album_title = DB_getItem($_TABLES['mg_albums'],'album_title','album_id=' . intval($album_id));

    $retval = '';
    $retval .= COM_startBlock ($LANG_MG01['static_media_sort'] . ' - ' . strip_tags($album_title), '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $T = new Template( MG_getTemplatePath($album_id) );
    $T->set_file ('admin','staticsort.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('album_id',$album_id);

    // check permissions...

    if ( $MG_albums[$album_id]->access != 3 ) {
        Log::write('system',Log::WARNING,"Media Gallery: Someone has tried to sort albums in Media Gallery.  User id: ".$_USER['uid'].", IP: ".$_SERVER['REAL_ADDR']);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    // make sure there is something here to sort...

    $sql = "SELECT * FROM " .
            $_TABLES['mg_media_albums'] .
            " as ma INNER JOIN " .
            $_TABLES['mg_media'] .
            " as m ON ma.media_id=m.media_id" .
            " WHERE ma.album_id=" . intval($album_id) .
            " ORDER BY ma.media_order DESC LIMIT 1";

    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );

    if ( $nRows == 0 ) {
        return(MG_genericError($LANG_MG00['no_media_objects']));
    }

    $T->set_var(array(
        'album_id'                  => $album_id,
        's_form_action'             => $actionURL,
        'lang_save'                 => $LANG_MG01['save'],
        'lang_cancel'               => $LANG_MG01['cancel'],
        'lang_static_sort_help'     => $LANG_MG01['static_sort_help'],
        'lang_media_capture_time'   => $LANG_MG01['media_capture_time'],
        'lang_media_upload_time'    => $LANG_MG01['media_upload_time'],
        'lang_media_title'          => $LANG_MG01['mod_mediatitle'],
        'lang_media_filename'       => $LANG_MG01['media_original_filename'],
        'lang_rating'               => $LANG_MG03['rating'],
        'lang_ascending'            => $LANG_MG01['ascending'],
        'lang_descending'           => $LANG_MG01['descending'],
        'lang_sort_options'         => $LANG_MG01['sort_options'],
        'lang_order_options'        => $LANG_MG01['order_options'],
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}



function MG_saveStaticSortMedia( $album_id, $actionURL='' ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    // check permissions...

    if ( $album_id == 0 ) {
        Log::write('system',Log::ERROR,"Media Gallery: Invalid album_id passed to sort");
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    //
    // -- get the sort options
    //

    $sortby     = COM_applyFilter($_POST['sortyby'],true);
    $sortorder  = COM_applyFilter($_POST['sortorder'],true);

    switch ($sortby)
    {
        case '0' :  // media_time
            $sql_sort_by = " ORDER BY m.media_time ";
            break;
        case '1' :  // media_upload_time
            $sql_sort_by = " ORDER BY m.media_upload_time ";
            break;
        case '2' : // media title
            $sql_sort_by = " ORDER BY m.media_title ";
            break;
        case '3' : // media original filename
            $sql_sort_by = " ORDER BY m.media_original_filename ";
            break;
        case '4' : // rating
            $sql_sort_by = " ORDER BY m.media_rating ";
            break;
        default :
            $sql_sort_by = " ORDER BY m.media_time ";
            break;
    }

    switch( $sortorder ) {
        case '0' :  // ascending
            $sql_order = " DESC";
            break;
        case '1' :  // descending
            $sql_order = " ASC";
            break;
        default :
            $sql_order = " ASC";
            break;
    }

    $sql = "SELECT  *
            FROM " . $_TABLES['mg_media_albums'] . " as ma LEFT  JOIN " . $_TABLES['mg_media'] . " as m ON m.media_id = ma.media_id
            WHERE ma.album_id=" . intval($album_id) .
            $sql_sort_by . $sql_order;

    $order = 10;
    $result = DB_query($sql);
    $numRows = DB_numRows($result);
    for ($x = 0; $x < $numRows; $x++ )
    {
        $row = DB_fetchArray($result);
        $media_id[$x] = $row['media_id'];
        $media_order[$x] = $order;
        $order += 10;
    }

    $media_count = $numRows;

    for ($x = 0; $x < $media_count; $x++ ) {
        $sql = "UPDATE " . $_TABLES['mg_media_albums'] . " SET media_order=" . $media_order[$x] .
                " WHERE media_id='" . $media_id[$x] . "' AND album_id=" . $album_id;
        $res = DB_query($sql);
    }

    echo COM_refresh( $actionURL );
    exit;
}


function MG_reorderMedia($album_id) {
    global $_TABLES, $_CONF;

    $sql = "SELECT media_id, media_order
            FROM " . $_TABLES['mg_media_albums'] .
            " WHERE album_id = " . intval($album_id) .
            " ORDER BY media_order ASC";

    $result = DB_query($sql);
    $nrows = DB_numRows($result);
    $row = array();
    for ($x = 0; $x < $nrows; $x++ ) {
        $row[] = DB_fetchArray($result);
    }
    $i = 10;
    for ($x=0; $x < $nrows; $x++) {
        $sql = "UPDATE " . $_TABLES['mg_media_albums'] .
            " SET media_order = " . $i .
            " WHERE media_id='" . $row[$x]['media_id'] . "' AND album_id = " . intval($album_id);
        DB_query($sql);
        $i += 10;
    }
}

function MG_SortMedia( $album_id ) {
    global $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03;

    if ($MG_albums[$album_id]->album_sort_order == 0 ) {
        return;
    }

    switch ($MG_albums[$album_id]->album_sort_order) {
        case '1' :  // media_time
            $sql_sort_by = " ORDER BY m.media_time DESC";
            break;
        case '2' :  // media_time
            $sql_sort_by = " ORDER BY m.media_time ASC";
            break;
        case '3' :  // media_upload_time
            $sql_sort_by = " ORDER BY m.media_upload_time DESC";
            break;
        case '4' :  // media_upload_time
            $sql_sort_by = " ORDER BY m.media_upload_time ASC";
            break;
        case '5' :  // title
            $sql_sort_by = " ORDER BY m.media_title ASC";
            break;
        case '6' :  // title
            $sql_sort_by = " ORDER BY m.media_title DESC";
            break;
    }

    $sql = "SELECT  *
            FROM " . $_TABLES['mg_media_albums'] . " as ma LEFT  JOIN " . $_TABLES['mg_media'] . " as m ON m.media_id = ma.media_id
            WHERE ma.album_id=" . intval($album_id) .
            $sql_sort_by;

    $order = 10;
    $result = DB_query($sql);
    $numRows = DB_numRows($result);
    for ($x = 0; $x < $numRows; $x++ ) {
        $row = DB_fetchArray($result);
        $media_id[$x] = $row['media_id'];
        $media_order[$x] = $order;
        $order += 10;
    }

    $media_count = $numRows;

    $i = 0;
    for ($x = 0; $x < $media_count; $x++ ) {
        $sql = "UPDATE " . $_TABLES['mg_media_albums'] . " SET media_order=" . $media_order[$x] .
                " WHERE media_id='" . $media_id[$x] . "' AND album_id=" . intval($album_id);
        $res = DB_query($sql);
    }
    return;
}
?>