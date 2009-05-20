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
require_once $_CONF['path_system'] . 'lib-comment.php';

// Uncomment the line below if you need to debug the HTTP variables being passed
// to the script.  This will sometimes cause errors but it will allow you to see
// the data being passed in a POST operation
// echo COM_debug($_POST);

/**
 * Handles a comment submission
 *
 * @copyright Vincent Furia 2005
 * @author Vincent Furia <vinny01 AT users DOT sourceforge DOT net>
 * @return string HTML (possibly a refresh)
 */
function handleSubmit()
{
    global $_CONF, $_TABLES, $LANG03, $inputHandler, $pageHandle;

    $display = '';

    $type           = $inputHandler->getVar('strict','type','post','');
    $sid            = $inputHandler->getVar('strict','sid','post','');
    $postmode       = $inputHandler->getVar('strict','postmode','post','');
    $comment_html   = $inputHandler->getVar('html','comment_html','post','');
    $comment_text   = $inputHandler->getVar('text','comment_text','post','');
    $comment        = $inputHandler->getVar('text','comment','post','');
    $title          = $inputHandler->getVar('text','title','post','');
    $pid            = $inputHandler->getVar('strict','pid','post','');

    switch ( $type ) {
        case 'article':
            $commentcode = DB_getItem ($_TABLES['stories'], 'commentcode',
                                       "sid = '".addslashes($sid)."'" . COM_getPermSQL('AND')
                                       . " AND (draft_flag = 0) AND (date <= NOW()) "
                                       . COM_getTopicSQL('AND'));
            if (!isset($commentcode) || ($commentcode != 0)) {
                $pageHandle->redirect($_CONF['site_url'] . '/index.php');
            }

    if (($_CONF['advanced_editor'] == 1)) {
        if ( $postmode == 'html' ) {
            $comment = $inputHandler->getVar('text','comment_html','post','');
        } else if ( $postmode == 'text' || $postmode == 'plaintext') {
            $comment = $inputHandler->getVar('text','comment_text','post','');
        }
        if ( $comment_html == '' && $comment_text == '' ) {
            $comment = $inputHandler->getVar($postmode,'comment','post','');
        }
    } else {
        if ( $postmode == 'html' ) {
            $comment = $inputHandler->getVar('html','comment','post','');
        } else if ( $postmode == 'text' ) {
            $comment = $inputHandler->getVar('text','comment','post','');
        }
    }

/*---
            if (($_CONF['advanced_editor'] == 1)) {
                if ( $postmode == 'html' ) {
                    $comment = $comment_html;
                } else if ( $postmode == 'text' ) {
                    $comment = $comment_text;
                }
            }
--- */
            $ret = CMT_saveComment ( $title,
                $comment, $sid, $pid,
                'article', $postmode);

            if ( $ret > 0 ) { // failure //FIXME: some failures should not return to comment form
                $pageHandle->setPageTitle($LANG03[1]);
                $pageHandle->addContent(CMT_commentForm ($title, $comment,
                           $sid, $pid, $type,
                           $LANG03[14],$postmode));
                $pageHandle->displayPage();

            } else { // success
                $comments = DB_count ($_TABLES['comments'], 'sid', addslashes($sid));
                DB_change ($_TABLES['stories'], 'comments', $comments, 'sid', addslashes($sid));
                COM_olderStuff (); // update comment count in Older Stories block
                $pageHandle->redirect($pageHandle->buildUrl ($_CONF['site_url']
                    . "/article.php?story=$sid"));
            }
            break;
        default: // assume plugin
            $comment = '';
            if (($_CONF['advanced_editor'] == 1)) {
                if ( $postmode == 'html' ) {
                    $comment = $comment_html;
                } else if ( $postmode == 'text' ) {
                    $comment = $comment_text;
                }
            }
            if ( !($display = PLG_commentSave($type, $title,
                                $comment, $sid, $pid,
                                $postmode)) ) {
                $pageHandle->redirect($_CONF['site_url'] . '/index.php');
            }
            break;
    }

    return $display;
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
    global $_CONF, $_TABLES, $_USER, $pageHandle, $inputHandler;

    $display = '';

    $type = $inputHandler->getVar('strict','type','request','');
    $sid  = $inputHandler->getVar('strict','sid','request','');
    $cid  = $inputHandler->getVar('strict','cid','request',0);

    switch ($type) {
    case 'article':
        $has_editPermissions = SEC_hasRights('story.edit');
        $result = DB_query("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['stories']} WHERE sid = '".addslashes($sid)."'");
        $A = DB_fetchArray($result);

        if ($has_editPermissions && SEC_hasAccess($A['owner_id'],
                $A['group_id'], $A['perm_owner'], $A['perm_group'],
                $A['perm_members'], $A['perm_anon']) == 3) {
            CMT_deleteComment($cid, $sid,
                              'article');
            $comments = DB_count($_TABLES['comments'], 'sid', addslashes($sid));
            DB_change($_TABLES['stories'], 'comments', $comments,
                      'sid', addslashes($sid));
            CACHE_remove_instance('whatsnew');
            $pageHandle->redirect($pageHandle->buildUrl ($_CONF['site_url']
                                    . "/article.php?story=$sid") . '#comments');
        } else {
            COM_errorLog("User {$_USER['username']} (IP: {$_SERVER['REMOTE_ADDR']}) tried to illegally delete comment $cid from $type $sid");
            $pageHandle->redirect($_CONF['site_url'] . '/index.php');
        }
        break;

    default: // assume plugin
        if (!($display = PLG_commentDelete($type,
                            $cid, $sid))) {
            CACHE_remove_instance('whatsnew');
            $pageHandle->redirect($_CONF['site_url'] . '/index.php');
        }
        break;
    }

    return $display;
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
    global $_CONF, $_TABLES, $_USER, $LANG_ACCESS, $inputHandler,$pageHandle;

    $display = '';

    if ($view) {
        $cid = $inputHandler->getVar('strict','cid','request',0);
    } else {
        $cid = $inputHandler->getVar('strict','pid','request',0);
    }

    if ($cid <= 0) {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }

    $sql = "SELECT sid, title, type FROM {$_TABLES['comments']} WHERE cid = $cid";
    $A = DB_fetchArray( DB_query($sql) );
    $sid   = $A['sid'];
    $title = $A['title'];
    $type  = $A['type'];

    $format = $inputHandler->getVar('strict','format','request',$_CONF['comment_mode']);
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
                 . "perm_members, perm_anon FROM {$_TABLES['stories']} WHERE (sid = '".addslashes($sid)."') "
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

                $order = $inputHandler->getVar('strict','order','request','');
                $page  = $inputHandler->getVar('strict','page','request',0);

                $display .= CMT_userComments ($sid, $title, $type, $order,
                                $format, $cid, $page, $view, $delete_option,
                                $B['commentcode']);
            } else {
                $display .= COM_startBlock ($LANG_ACCESS['accessdenied'], '',
                                    COM_getBlockTemplate ('_msg_block', 'header'))
                         . $LANG_ACCESS['storydenialmsg']
                         . COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
            }
            break;

        default: // assume plugin
            $order = $inputHandler->getVar('strict','order','request','');
            $page  = $inputHandler->getVar('strict','page','request',0);

            if ( !($display = PLG_displayComment($type, $sid, $cid, $title,
                                  $order, $format,
                                  $page, $view)) ) {
                $pageHandle->redirect($_CONF['site_url'] . '/index.php');
            }
            break;
    }
    $pageHandle->addContent($display);
    $pageHandle->displayPage();
}

/**
 * Handles a comment edit submission
 *
 * @copyright Jared Wenerd 2008
 * @author Jared Wenerd <wenerd87 AT gmail DOT com>
 * @return string HTML (possibly a refresh)
 */
function handleEdit() {
    global $_TABLES, $LANG03,$_USER,$_CONF, $inputHandler, $pageHandle;

    $cid = $inputHandler->getVar('strict','cid','request','');
    $sid = $inputHandler->getVar('strict','sid','request','');
    $type = $inputHandler->getVar('strict','type','request','');
    $pid  = $inputHandler->getVar('strict','pid','request',0);

    if (!is_numeric ($cid) || ($cid < 0) || empty ($sid) || empty ($type)) {
        COM_errorLog("handleEdit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
               . 'to edit a comment with one or more missing/bad values.');
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }

    $result = DB_query ("SELECT title,comment FROM {$_TABLES['comments']} "
        . "WHERE cid = $cid AND sid = '".addslashes($sid)."' AND type = '".addslashes($type)."'");
    if ( DB_numRows($result) == 1 ) {
        $A = DB_fetchArray ($result);
        $title = $A['title'];
        $commenttext = COM_undoSpecialChars ($A['comment']);

        //remove signature
        $pos = strpos( $commenttext,'<!-- COMMENTSIG --><span class="comment-sig">');
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
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }

    $pageHandle->setPageTitle($LANG03[1]);
    $pageHandle->addContent(CMT_commentForm ($title, $commenttext, $sid,
                  $pid, $type, 'edit', $postmode));
    $pageHandle->displayPage();
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
    global $_CONF, $_TABLES, $_USER, $LANG03, $inputHandler,$pageHandle;

    $type = $inputHandler->getVar('strict','type','post');
    $sid  = $inputHandler->getVar('strict','sid','post');
    $cid  = $inputHandler->getVar('strict','cid','post');
    $postmode = $inputHandler->getVar('strict','postmode','post','plaintext');
    $title = $inputHandler->getVar('plain','title','post','');


//    $comment = $inputHandler->getVar('html','comment','post','');

    if (($_CONF['advanced_editor'] == 1)) {
        if ( $postmode == 'html' ) {
            $comment = $inputHandler->getVar('text','comment_html','post','');
        } else if ( $postmode == 'text' || $postmode == 'plaintext') {
            $comment = $inputHandler->getVar('text','comment_text','post','');
        }
        if ( $comment_html == '' && $comment_text == '' ) {
            $comment = $inputHandler->getVar($postmode,'comment','post','');
        }
    } else {
        if ( $postmode == 'html' ) {
            $comment = $inputHandler->getVar('html','comment','post','');
        } else if ( $postmode == 'text' ) {
            $comment = $inputHandler->getVar('text','comment','post','');
        }
    }

    $commentuid = DB_getItem ($_TABLES['comments'], 'uid', "cid = '$cid'");
    if ( empty($_USER['uid'])) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    //check for bad input
    if (empty ($sid) || empty ($title) || empty ($comment) || !is_numeric ($cid)
            || $cid < 1 ) {
        COM_errorLog("handleEditSubmit(): {{$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to edit a comment with one or more missing values.');
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    } elseif ( $uid != $commentuid && !SEC_hasRights( 'comment.moderate' ) ) {
        //check permissions
        COM_errorLog("handleEditSubmit(): {{$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to edit a comment without proper permission.');
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }

    $comment = CMT_prepareText($comment, $postmode);
    $title = COM_checkWords (strip_tags ($title));

    if (!empty ($title) && !empty ($comment)) {
        COM_updateSpeedlimit ('comment');
        $title   = $inputHandler->prepareForDB($title);
        $comment = $inputHandler->prepareForDB($comment);

        // save the comment into the comment table
        DB_query("UPDATE {$_TABLES['comments']} SET comment = '$comment', title = '$title'"
                . " WHERE cid=$cid AND sid='".addslashes($sid)."'");

        if (DB_error() ) { //saving to non-existent comment or comment in wrong article
            COM_errorLog("handleEditSubmit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
            . 'to edit to a non-existent comment or the cid/sid did not match');
            $pageHandle->redirect($_CONF['site_url'] . '/index.php');
        }
        $safecid = addslashes($cid);
        $safeuid = addslashes($uid);
        DB_save($_TABLES['commentedits'],'cid,uid,time',"$safecid,$safeuid,NOW()");
    } else {
        COM_errorLog("handleEditSubmit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to submit a comment with invalid $title and/or $comment.');
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }
    PLG_commentEditSave($type,$cid,$sid);
    $pageHandle->redirect($pageHandle->buildUrl ($_CONF['site_url'] . "/article.php?story=$sid"));
}


// MAIN
CMT_updateCommentcodes();
$display = '';

// If reply specified, force comment submission form
$reply = $inputHandler->getVar('strict','reply','request','');
if ( !empty($reply) ) {
    $mode = '';
} else {
    $mode = $inputHandler->getVar('strict','mode','request','');
}
$postmode     = $inputHandler->getVar('strict','postmode','post','');
$comment_html = $inputHandler->getVar('html','comment_html','post','');
$comment_text = $inputHandler->getVar('text','comment_text','post','');
//$comment      = $inputHandler->getVar('text','comment','post','');

switch ($mode) {
case $LANG03[28]: //Preview Changes (for edit)

case $LANG03[14]: // Preview
    $comment = '';
    $title          = $inputHandler->getVar('text','title','post','');
    $sid            = $inputHandler->getVar('strict','sid','post','');
    $pid            = $inputHandler->getVar('strict','pid','post',0);
    $type           = $inputHandler->getVar('strict','type','post','');

    if (($_CONF['advanced_editor'] == 1)) {
        if ( $postmode == 'html' ) {
            $comment = $comment_html;
        } else if ( $postmode == 'text' ) {
            $comment = $comment_text;
        }
        if ( $comment_html == '' && $comment_text == '' ) {
            $comment = $inputHandler->getVar($postmode,'comment','post','');
        }
    } else {
        if ( $postmode == 'html' ) {
            $comment = $inputHandler->getVar('html','comment','post','');
        } else if ( $postmode == 'text' ) {
            $comment = $inputHandler->getVar('text','comment','post','');
        }
    }

    $pageHandle->setPageTitle($LANG03[14]);
    $pageHandle->addContent(CMT_commentForm ($title, $comment,
                    $sid,
                    $pid,
                    $type, $mode,
                    $postmode));
    $pageHandle->displayPage();
    break;

case $LANG03[29]: //Submit Changes
    if (SEC_checkToken()) {
        $display .= handleEditSubmit();
    } else {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }
    break;

case $LANG03[11]: // Submit Comment
    $display .= handleSubmit();  // moved to function for readibility
    break;

case 'delete':
    if (SEC_checkToken()) {
        $display .= handleDelete();  // moved to function for readibility
    } else {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }
    break;

case 'view':
    $display .= handleView(true);  // moved to function for readibility
    break;

case 'display':
    $display .= handleView(false);  // moved to function for readibility
    break;

case 'report':
    $cid = $inputHandler->getVar('int','cid','get',0);
    $type = $inputHandler->getVar('strict','type','get','');
    $pageHandle->setPageTitle($LANG03[27]);
    $pageHandle->addContent(CMT_reportAbusiveComment ($cid,$type));
    $pageHandle->displayPage();
    break;

case 'sendreport':
    if (SEC_checkToken()) {
        $display .= CMT_sendReport(COM_applyFilter($_POST['cid'], true),
                                   COM_applyFilter($_POST['type']));
    } else {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }
    break;

case 'edit':
    if (SEC_checkToken()) {
        $display .= handleEdit();
    } else {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }
    break;

case 'que':
    if ( SEC_checkToken() && SEC_hasRights('comment.moderate') ) {
        //get comment
        $cid = $inputHandler->getVar('int','cid','get',0);
        $sid = $inputHandler->getVar('strict','sid','get','');
        resubmitToModeration($cid);

        $pageHandle->redirect($pageHandle->buildUrl ($_CONF['site_url']
                                    . "/article.php?story=$sid"));

    } else {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }
    break;


default:  // New Comment
    $sid = $inputHandler->getVar('strict','sid','request','');
    $type = $inputHandler->getVar('strict','type','request','');
    $title = $inputHandler->getVar('text','title','request','');
    $postmode = $inputHandler->getVar('strict','postmode','request',$_CONF['postmode']);
    $pid = $inputHandler->getVar('int','pid','request',0);

    if (!empty ($sid) && !empty ($type)) {
        if (empty ($title)) {
            if ($type == 'article') {
                $title = DB_getItem($_TABLES['stories'], 'title',
                                    "sid = '".addslashes($sid)."'" . COM_getPermSQL('AND')
                                    . COM_getTopicSQL('AND'));
            }
            $title = str_replace ('$', '&#36;', $title);
            // CMT_commentForm expects non-htmlspecial chars for title...
            $title = str_replace ( '&amp;', '&', $title );
            $title = str_replace ( '&quot;', '"', $title );
            $title = str_replace ( '&lt;', '<', $title );
            $title = str_replace ( '&gt;', '>', $title );
        }
        $pageHandle->setPageTitle($LANG03[1]);
        $pageHandle->addContent(CMT_commentForm ($title, '', $sid,
                        $pid, $type, $mode,
                        $postmode));
        $pageHandle->displayPage();
    } else {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }
    break;
}

$pageHandle->addContent($display);
$pageHandle->displayPage();
?>