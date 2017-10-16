<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | comment.inc.php                                                          |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2017 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

MG_initAlbums();

/**
 * Plugin function to delete a comment
 * $cid    Comment to be deleted
 * $id     Item id to which $cid belongs
 *
 */
function _mg_deletecomment($cid,$id)
{
    global $_CONF, $_MG_CONF, $_TABLES, $MG_albums;

    // find the album that holds this peice of media

    $sql = "SELECT album_id FROM {$_TABLES['mg_media_albums']} WHERE media_id='" . DB_escapeString($id) . "'";
    $result = DB_query($sql);
    $nRows  = DB_numRows($result);
    if ( $nRows > 0 ) {
        $row = DB_fetchArray($result);
        $aid = $row['album_id'];
        if ( $MG_albums[0]->owner_id ) {
            $access = 3;
        } else {
            $access = $MG_albums[$aid]->access;
        }
    } else {
        $access = 0;
    }

    if ($access == 3 || SEC_hasRights('mediagallery.admin')){
        if (CMT_deleteComment($cid, $id, 'mediagallery') == 0) {
            //reduce count in media table
            $comments = CMT_getCount('mediagallery', $id);
//            $comments = DB_count ($_TABLES['comments'], array('sid','type','queued'), array(DB_escapeString($id), 'mediagallery',0));
            DB_change($_TABLES['mg_media'],'media_comments', $comments, 'media_id', DB_escapeString($id));
            // Now redirect the program flow to the view of the file and its comments
            return (COM_refresh($_MG_CONF['site_url'] . "/media.php?s=$id"));

        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Plugin function to display a specific comment thread
 * $id      Unique idenifier for item comment belongs to
 * $cid     Comment id to display (possibly including sub-comments)
 * $title   Page/comment title
 * $order   'ASC' or 'DSC' or blank
 * $format  'threaded', 'nested', or 'flat'
 * $page    Page number of comments to display
 * $view    True to view comment (by cid), false to display (by $pid)
 */
function _mg_displaycomment($id,$commentid,$title,$order,$format,$page,$view)
{
    global $_CONF, $_USER, $_MG_CONF, $LANG_LOGIN;

    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] ) {
        echo SEC_loginRequiredForm();
        exit;
    }

    $retval = '';
    require_once $_CONF['path'].'plugins/mediagallery/include/classAlbum.php';
    require_once $_CONF['path'].'plugins/mediagallery/include/lib-media.php';

    $rc = PLG_getItemInfo('mediagallery', $id, 'id');
    if ( is_array($rc) && count($rc) === 0 ) {
        return false;
    }

    list($ptitle,$retval,$themeCSS,$album_id) =  MG_displayMediaImage( $id, 0, 0, 0 );

    $retval = $themeCSS . $retval;

    if (SEC_hasRights('mediagallery.admin')) {
        $delete_option = true;
    } else {
        $delete_option = false;
    }

    $view = $view == 1 ? true : false;
    $retval .= CMT_userComments ($id, $title, 'mediagallery',$order,$format,$commentid,$page,$view,$delete_option);

    return $retval;
}

/**
 * Plugin function that is called after comment form is submitted.
 * Needs to at least save the comment and check return value.
 * Add any additional logic your plugin may need to perform on comments.
 *
 * $title       comment title
 * $comment     comment text
 * $id          Item id to which $cid belongs
 * $pid         comment parent
 * $postmode    'html' or 'text'
 *
 */
function _mg_savecomment($title,$comment,$id,$pid,$postmode)
{
    global $_CONF, $_MG_CONF, $_TABLES, $LANG03;

    $retval = '';

    $title = strip_tags ($title);
    $pid = COM_applyFilter ($pid, true);
    $postmode = COM_applyFilter ($postmode);

    $ret = CMT_saveComment ( $title, $comment, $id, $pid, 'mediagallery',$postmode);

    if ( $ret > 0 ) {
        $retval = '';
        if ( SESS_isSet('glfusion.commentpresave.error') ) {
            $retval = COM_showMessageText(SESS_getVar('glfusion.commentpresave.error'), '', true,'error');
            SESS_unSet('glfusion.commentpresave.error');
        }
        $retval .= CMT_commentform ($title, $comment, $id, $pid, 'mediagallery', $LANG03[14], $postmode);
        return $retval;
    } else {
        $comments = CMT_getCount('mediagallery', $id);
//        $comments = DB_count ($_TABLES['comments'], array('sid','type','queued'), array(DB_escapeString($id), 'mediagallery',0));
        DB_change($_TABLES['mg_media'],'media_comments', $comments, 'media_id',DB_escapeString($id));
        return (COM_refresh ($_MG_CONF['site_url'] . "/media.php?s=$id#comments") );
    }
}
?>