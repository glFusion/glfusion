<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | comment.php                                                              |
// |                                                                          |
// | Let user comment on a story or plugin.                                   |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Vincent Furia     - vinny01 AT users DOT sourceforge DOT net    |
// |          Jared Wenerd      - wenerd87 AT gmail DOT com                   |
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

/**
* This file is responsible for letting user enter a comment and saving the
* comments to the DB.  All comment display stuff is in lib-common.php
*
* @author   Jason Whittenburg
* @author   Tony Bibbs    <tonyAT tonybibbs DOT com>
* @author   Vincent Furia <vinny01 AT users DOT sourceforge DOT net>
*
*/

/**
* glFusion common function library
*/
require_once 'lib-common.php';

/**
 * glFusion comment function library
 */

USES_lib_comments();

/**
 * Handles a comment submission
 *
 * @copyright Vincent Furia 2005
 * @author Vincent Furia <vinny01 AT users DOT sourceforge DOT net>
 * @return string HTML (possibly a refresh)
 */
function handleSubmit()
{
    global $_CONF, $_TABLES, $LANG03;

    $display = '';

    $type           = IO_getVar('strict','type','post','');
    $sid            = IO_getVar('strict','sid','post','');
    $postmode       = IO_getVar('strict','postmode','post','');
    if ( $postmode == 'html' ) {
        $comment    = IO_getVar('html','comment','post','');
    } else {
        $comment    = IO_getVar('text','comment','post','');
    }
    $title          = IO_getVar('text','title','post','');
    $pid            = IO_getVar('strict','pid','post','');

    switch ( $type ) {
        case 'article':
            $commentcode = DB_getItem ($_TABLES['stories'], 'commentcode',
                                       "sid = '".IO_prepareForDB($sid)."'" . COM_getPermSQL('AND')
                                       . " AND (draft_flag = 0) AND (date <= NOW()) "
                                       . COM_getTopicSQL('AND'));
            if (!isset($commentcode) || ($commentcode != 0)) {
                IO_redirect($_CONF['site_url'] . '/index.php');
            }
            $ret = CMT_saveComment ( $title,$comment, $sid, $pid,'article', $postmode);

            if ( $ret > 0 ) { // failure //FIXME: some failures should not return to comment form
                IO_setPageTitle($LANG03[1]);
                IO_addContent(CMT_commentForm ($title, $comment,
                                               $sid, $pid, $type,
                                               'preview', $postmode));

            } else { // success
                $comments = DB_count ($_TABLES['comments'], 'sid', IO_prepareForDB($sid));
                DB_change ($_TABLES['stories'], 'comments', $comments, 'sid', IO_prepareForDB($sid));
                COM_olderStuff (); // update comment count in Older Stories block
                IO_redirect(IO_buildUrl ($_CONF['site_url'] . "/article.php?story=$sid"));
            }
            break;
        default: // assume plugin
            if ( !($display = PLG_commentSave($type, $title,
                                $comment, $sid, $pid,
                                $postmode)) ) {
                IO_redirect ($_CONF['site_url'] . '/index.php');
            } else {
                IO_addContent($display);
            }
            break;
    }
}

/**
 * Handles a comment delete
 *
 * @copyright Vincent Furia 2005
 * @author Vincent Furia <vinny01 AT users DOT sourceforge DOT net>
 * @return string HTML (possibly a refresh)
 */
function handleDelete()
{
    global $_CONF, $_TABLES, $_USER;

    $display = '';

    $type = $inputHandler->getVar('strict','type',array('post','get'),'');
    $sid  = $inputHandler->getVar('strict','sid',array('post','get'),'');
    $cid  = $inputHandler->getVar('strict','cid',array('post','get'),0);

    switch ($type) {
        case 'article':
            $has_editPermissions = SEC_hasRights('story.edit');
            $result = DB_query("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['stories']} WHERE sid = '".IO_prepareForDB($sid)."'");
            $A = DB_fetchArray($result);

            if ($has_editPermissions && SEC_hasAccess($A['owner_id'],
                    $A['group_id'], $A['perm_owner'], $A['perm_group'],
                    $A['perm_members'], $A['perm_anon']) == 3) {
                CMT_deleteComment($cid, $sid,'article');
                $comments = DB_count($_TABLES['comments'], 'sid', IO_prepareForDB($sid));
                DB_change($_TABLES['stories'], 'comments', $comments,
                          'sid', IO_prepareForDB($sid));
                CACHE_remove_instance('whatsnew');
                IO_redirect(COM_buildUrl ($_CONF['site_url']
                                        . "/article.php?story=$sid") . '#comments');
            } else {
                COM_errorLog("User {$_USER['username']} (IP: {$_SERVER['REMOTE_ADDR']}) tried to illegally delete comment $cid from $type $sid");
                IO_redirect($_CONF['site_url'] . '/index.php');
            }
            break;

        default: // assume plugin
            if (!($display = PLG_commentDelete($type,$cid, $sid))) {
                CACHE_remove_instance('whatsnew');
                IO_redirect($_CONF['site_url'] . '/index.php');
            } else {
                IO_addContent($display);
            }
            break;
    }
}

/**
 * Handles a comment view request
 *
 * @copyright Vincent Furia 2005
 * @author Vincent Furia <vinny01 AT users DOT sourceforge DOT net>
 * @param boolean $view View or display (true for view)
 * @return string HTML (possibly a refresh)
 */
function handleView($view = true)
{
    global $_CONF, $_TABLES, $_USER, $LANG_ACCESS;

    $display = '';

    if ($view) {
        $cid = IO_getVar('integer','cid',array('post','get'),0);
    } else {
        $cid = IO_getVar('integer','pid',array('post','get'),0);
    }

    if ($cid <= 0) {
        IO_redirect($_CONF['site_url'] . '/index.php');
    }

    $order = IO_getVar('strict','order',array('post','get'),'');
    $page  = IO_getVar('integer','page',array('post','get'),0);

    $sql = "SELECT sid, title, type FROM {$_TABLES['comments']} WHERE cid = $cid";
    $A = DB_fetchArray( DB_query($sql) );
    $sid   = $A['sid'];
    $title = $A['title'];
    $type  = $A['type'];

    $format = IO_getVar('strict','format',array('post','get'),$_CONF['comment_mode']);

    if ( $format != 'threaded' && $format != 'nested' && $format != 'flat' ) {
        if ( $_USER['uid'] > 1 ) {
            $format = DB_getItem( $_TABLES['usercomment'], 'commentmode',
                                  "uid = {$_USER['uid']}" );
        } else {
            $format = $_CONF['comment_mode'];
        }
    }

    switch ( $type ) {
        case 'article':
            $sql = 'SELECT COUNT(*) AS count, commentcode, owner_id, group_id, perm_owner, perm_group, '
                 . "perm_members, perm_anon FROM {$_TABLES['stories']} WHERE (sid = '".IO_prepareForDB($sid)."') "
                 . 'AND (draft_flag = 0) AND (commentcode >= 0) AND (date <= NOW())' . COM_getPermSQL('AND')
                 . COM_getTopicSQL('AND') . ' GROUP BY sid,owner_id, group_id, perm_owner, perm_group,perm_members, perm_anon ';
            $result = DB_query ($sql);
            $B = DB_fetchArray ($result);
            $allowed = $B['count'];

            if ( $allowed == 1 ) {
                $delete_option = ( SEC_hasRights( 'story.edit' ) &&
                    ( SEC_hasAccess( $B['owner_id'], $B['group_id'],
                        $B['perm_owner'], $B['perm_group'], $B['perm_members'],
                        $B['perm_anon'] ) == 3 ) );

                IO_addContent(CMT_userComments ($sid, $title, $type, $order,
                                $format, $cid, $page, $view, $delete_option,
                                $B['commentcode']));
            } else {
                $pageHandle->displayAccessError($LANG_ACCESS['accessdenied'],$LANG_ACCESS['storydenialmsg']);
            }
            break;

        default: // assume plugin
            if ( !($display = PLG_displayComment($type, $sid, $cid, $title,
                                  $order, $format, $page, $view)) ) {
                IO_redirect($_CONF['site_url'] . '/index.php');
            } else {
                IO_addContent($display);
            }
            break;
    }

    IO_addContent(COM_showMessageFromParameter());
}

/**
 * Handles a comment edit submission
 *
 * @copyright Jared Wenerd 2008
 * @author Jared Wenerd <wenerd87 AT gmail DOT com>
 * @return string HTML (possibly a refresh)
 */
function handleEdit() {
    global $_TABLES, $LANG03,$_USER,$_CONF;

    $cid  = IO_getVar('integer','cid',array('post','get'),-1);
    $sid  = IO_getVar('strict','sid',array('post','get'),'');
    $type = IO_getVar('strict','type',array('post','get'),'');
    $pid  = IO_getVar('integer','pid',array('post','get'),0);

    if (!is_numeric ($cid) || ($cid < 0) || empty ($sid) || empty ($type)) {
        COM_errorLog("handleEdit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
               . 'to edit a comment with one or more missing/bad values.');
        IO_redirect($_CONF['site_url'] . '/index.php');
    }

    $result = DB_query ("SELECT title,comment FROM {$_TABLES['comments']} "
        . "WHERE cid = $cid AND sid = '".IO_prepareForDB($sid)."' AND type = '".IO_prepareForDB($type)."'");
    if ( DB_numRows($result) == 1 ) {
        $A = DB_fetchArray ($result);
        $title = $A['title'];
        $commenttext = COM_undoSpecialChars ($A['comment']);

        //remove signature
        $pos = strpos( $commenttext,'<!-- COMMENTSIG --><div class="comment-sig">');
        if ( $pos > 0) {
            $commenttext = substr($commenttext, 0, $pos);
        }

        //get format mode
        if ( preg_match( '/<.*>/', $commenttext ) != 0 ){
            $postmode = 'html';
        } else {
            $postmode = 'plaintext';
        }
    } else {
        COM_errorLog("handleEdit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
               . 'to edit a comment that doesn\'t exist as described.');
        IO_redirect($_CONF['site_url'] . '/index.php');
    }
    IO_setPageTitle($LANG03[1]);
    IO_addContent(CMT_commentForm ($title, $commenttext, $sid, $pid, $type, 'edit', $postmode));
}

/**
 * Handles a comment edit submission
 *
 * @copyright Jared Wenerd 2008
 * @author Jared Wenerd <wenerd87 AT gmail DOT com>
 * @return string HTML (possibly a refresh)
 */
function handleEditSubmit()
{
    global $_CONF, $_TABLES, $_USER, $LANG03;

    $type     = IO_getVar('strict','type','post');
    $sid      = IO_getVar('strict','sid','post');
    $cid      = IO_getVar('integer','cid','post',0);
    $postmode = IO_getVar('strict','postmode','post','plaintext');
    $title    = IO_getVar('plain','title','post','');

    $comment  = '';


    $commentuid = DB_getItem ($_TABLES['comments'], 'uid', "cid = $cid");
    if ( empty($_USER['uid'])) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $comment = $_POST['comment'];

    //check for bad input
    if (empty ($sid) || empty ($title) || empty ($comment) || !is_numeric ($cid)
            || $cid < 1 ) {
        COM_errorLog("handleEditSubmit(): {{$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to edit a comment with one or more missing values.');
        IO_redirect($_CONF['site_url'] . '/index.php');
    } elseif ( $uid != $commentuid && !SEC_inGroup( 'Root' ) ) {
        //check permissions
        COM_errorLog("handleEditSubmit(): {{$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to edit a comment without proper permission.');
        IO_redirect($_CONF['site_url'] . '/index.php');
    }

    $comment = CMT_prepareText($comment, $postmode,true,$cid);
    $title = COM_checkWords ($title);

    if (!empty ($title) && !empty ($comment)) {
        COM_updateSpeedlimit ('comment');
        $title = IO_prepareForDB ($title);
        $comment = IO_prepareForDB ($comment);

        // save the comment into the comment table
        DB_query("UPDATE {$_TABLES['comments']} SET comment = '$comment', title = '$title'"
                . " WHERE cid=$cid AND sid='".IO_prepareForDB($sid)."'");

        if (DB_error() ) { //saving to non-existent comment or comment in wrong article
            COM_errorLog("handleEditSubmit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
            . 'to edit to a non-existent comment or the cid/sid did not match');
            IO_redirect($_CONF['site_url'] . '/index.php');
        }
        $safecid = IO_prepareForDB($cid);
        $safeuid = IO_prepareForDB($uid);
        DB_save($_TABLES['commentedits'],'cid,uid,time',"$safecid,$safeuid,NOW()");
    } else {
        COM_errorLog("handleEditSubmit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to submit a comment with invalid $title and/or $comment.');
        IO_redirect($_CONF['site_url'] . '/index.php');
    }
    PLG_commentEditSave($type,$cid,$sid);
    IO_redirect(IO_buildUrl ($_CONF['site_url'] . "/article.php?story=$sid"));
}


// MAIN
CMT_updateCommentcodes();
$display = '';

$mode = IO_buttonCheck(array('save','saveedit','preview','previewchanges'), $_POST, '');

// If reply specified, force comment submission form
$reply = IO_getVar('strict','reply',array('post','get'),'');
if ( !empty($reply) ) {
    $mode = '';
} else {
    if ($mode == '' ) {
        $mode = IO_getVar('strict','mode',array('get','post'),'');
    }
}

$postmode      = IO_getVar('strict','postmode','post','');
if ( $postmode == 'html' ) {
    $comment = IO_getVar('html','comment','post','');
} else {
    $comment = IO_getVar('text','comment','post','');
}

switch ($mode) {
    case 'preview': //Preview Changes (for edit)
    case 'previewchanges' : // Preview

        $title          = $inputHandler->getVar('text','title','post','');
        $sid            = $inputHandler->getVar('strict','sid','post','');
        $pid            = $inputHandler->getVar('integer','pid','post',0);
        $type           = $inputHandler->getVar('strict','type','post','');

        IO_setPageTitle($LANG03[14]);
        IO_addContent(CMT_commentForm ($title, $comment,
                        $sid,
                        $pid,
                        $type, $mode,
                        $postmode));
        break;

    case 'saveedit': //Submit Changes
        if (SEC_checkToken()) {
            handleEditSubmit();
        } else {
            IO_redirect($_CONF['site_url'] . '/index.php');
        }
        break;

    case 'save': // Submit Comment
        handleSubmit();  // moved to function for readibility
        break;

    case 'delete':
        if (SEC_checkToken()) {
            handleDelete();  // moved to function for readibility
        } else {
            IO_redirect($_CONF['site_url'] . '/index.php');
        }
        break;

    case 'view':
        handleView(true);  // moved to function for readibility
        break;

    case 'display':
        handleView(false);  // moved to function for readibility
        break;

    case 'report':
        $cid  = IO_getVar('integer','cid','get',0);
        $type = IO_getVar('strict','type','get','');
        IO_addContent(CMT_reportAbusiveComment($cid,$type));
        break;

    case 'sendreport':
        $cid  = IO_getVar('integer','cid','post',0);
        $type = IO_getVar('strict','type','post','');

        if (SEC_checkToken()) {
            IO_addContent(CMT_sendReport($cid,$type));
        } else {
            IO_redirect($_CONF['site_url'] . '/index.php');
        }
        break;

    case 'edit':
        if (SEC_checkToken()) {
            handleEdit();
        } else {
            IO_redirect($_CONF['site_url'] . '/index.php');
        }
        break;


    default:  // New Comment
        $sid      = IO_getVar('strict','sid',  array('post','get'),'');
        $type     = IO_getVar('strict','type', array('post','get'),'');
        $title    = IO_getVar('plain' ,'title',array('post','get'),'');
        $postmode = IO_getVar('strict','postmode',array('post','get'),$_CONF['postmode']);

        if (!empty ($sid) && !empty ($type)) {
            if (empty ($title)) {
                if ($type == 'article') {
                    $title = DB_getItem($_TABLES['stories'], 'title',
                                        "sid = '".IO_prepareForDB($sid)."'" . COM_getPermSQL('AND')
                                        . COM_getTopicSQL('AND'));
                }
                $title = str_replace ('$', '&#36;', $title);
                // CMT_commentForm expects non-htmlspecial chars for title...
                $title = str_replace ( '&amp;', '&', $title );
                $title = str_replace ( '&quot;', '"', $title );
                $title = str_replace ( '&lt;', '<', $title );
                $title = str_replace ( '&gt;', '>', $title );
            }
            $pid = IO_getVar('integer','pid',array('post','get'),0);
            IO_addMetaName('robots', 'noindex');
            IO_addContent(CMT_commentForm ($title, '', $sid,$pid, $type, $mode,$postmode));
        } else {
            // we did not get the needed parameters....
            IO_redirect($_CONF['site_url'].'/index.php');
        }
        break;
}

IO_displayPage();

?>