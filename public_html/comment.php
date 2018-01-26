<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | comment.php                                                              |
// |                                                                          |
// | Let user comment on a story or plugin.                                   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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
USES_lib_comment();

function _getReferer()
{
    global $_CONF;

    if ( isset($_POST['referer']) ) {
        $referer = COM_sanitizeUrl($_POST['referer']);
    } else {
        if ( isset($_SERVER['HTTP_REFERER'] ) ) {
            $referer = COM_sanitizeUrl($_SERVER['HTTP_REFERER']);
        } else {
            $referer = '';
        }
    }

    $sLength = strlen($_CONF['site_url']);
    if ( substr($referer,0,$sLength) != $_CONF['site_url'] ) {
        $referer = $_CONF['site_url'].'/forum/index.php';
    }
    $referer = @htmlspecialchars($referer,ENT_COMPAT, COM_getEncodingt());
    if ( strstr($referer,'comment.php') !== false ) {
        if ( isset($_REQUEST['sid']) && isset($_REQUEST['type']) ) {
            $referer = PLG_getCommentUrlId($type);
        }
    }
    return $referer;
}

/**
 * Handles a comment submission
 *
 * @copyright Vincent Furia 2005
 * @author Vincent Furia <vinny01 AT users DOT sourceforge DOT net>
 * @return string HTML (possibly a refresh)
 */
function handleSubmit()
{
    global $_PLUGINS;

    $display = '';

    $type     = COM_applyFilter ($_POST['type']);
    $sid      = COM_sanitizeID(COM_applyFilter ($_POST['sid']));
    $title    = '';

    $pid      = COM_applyFilter($_POST['pid'],true);
    $postmode = COM_applyFilter($_POST['postmode']);
    $comment = '';

    if ( $type != 'article' ) {
        if (!in_array($type,$_PLUGINS) ) {
            $type = '';
        }
    }

    $comment = $_POST['comment_text'];

    if ( !($display = PLG_commentSave($type, $title,$comment, $sid, $pid,$postmode)) ) {
        $display = COM_refresh ($_CONF['site_url'] . '/index.php');
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
    global $_CONF, $_TABLES, $_USER, $_PLUGINS;

    $retval = '';
    $cid     = 0;

    $type = COM_applyFilter($_REQUEST['type']);
    $sid = COM_sanitizeID(COM_applyFilter($_REQUEST['sid']));
    if (isset($_REQUEST['cid'])) {
    	$cid = COM_applyFilter($_REQUEST['cid'],true);
    }
    if ( $type != 'article' ) {
        if (!in_array($type,$_PLUGINS) ) {
            $type = '';
        }
    }

    if (!($retval = PLG_commentDelete($type,$cid,$sid))) {
        $c = glFusion\Cache::getInstance()->deleteItemsByTag('whatsnew');
        echo COM_refresh($_CONF['site_url'] . '/index.php');
        exit;
    }

    return $retval;
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

    if ( !isset($_REQUEST['cid'])) $_REQUEST['cid'] = 0;

    if ($view) {
        $cid = COM_applyFilter ($_REQUEST['cid'], true);
    } else {
        $cid = COM_applyFilter ($_REQUEST['pid']);
    }

    if ($cid == 0 || $cid == '') {
        echo COM_refresh($_CONF['site_url'] . '/index.php');
        exit;
    }

    $sql = "SELECT sid, title, type FROM {$_TABLES['comments']} WHERE cid = " . (int) $cid;
    $A = DB_fetchArray( DB_query($sql) );
    $sid   = $A['sid'];
    $title = $A['title'];
    $type  = $A['type'];

    $format = $_CONF['comment_mode'];
    if( isset( $_REQUEST['format'] )) {
        $format = COM_applyFilter( $_REQUEST['format'] );
    }
    if ( $format != 'threaded' && $format != 'nested' && $format != 'flat' ) {
        if ( $_USER['uid'] > 1 ) {
            $format = DB_getItem( $_TABLES['usercomment'], 'commentmode',
                                  "uid = {$_USER['uid']}" );
        } else {
            $format = $_CONF['comment_mode'];
        }
    }

    $order = '';
    if (isset($_REQUEST['order'])) {
        $order = COM_applyFilter($_REQUEST['order']);
    }
    $page = 0;
    if (isset($_REQUEST['page'])) {
        $page = COM_applyFilter($_REQUEST['page'], true);
    }
    if ( !($display = PLG_displayComment($type, $sid, $cid, $title,
                          $order, $format, $page, $view)) ) {
        return COM_refresh($_CONF['site_url'] . '/index.php');
    }
    return COM_showMessageFromParameter() . $display;
}

/**
 * Handles a comment edit submission
 *
 * @copyright Jared Wenerd 2008
 * @author Jared Wenerd <wenerd87 AT gmail DOT com>
 * @return string HTML (possibly a refresh)
 */
function handleEdit($mod = false, $admin = false) {
    global $_TABLES, $LANG03,$_USER,$_CONF, $_PLUGINS;

    if ( isset($_POST['cid']) ) {
        $cid = COM_applyFilter ($_POST['cid'],true);
    } else if (isset($_GET['cid']) ) {
        $cid = COM_applyFilter ($_GET['cid'],true);
    } else {
        $cid = -1;
    }

    if ( $mod == false && $admin == false) {
// user edit
        if ( isset($_POST['sid']) ) {
            $sid = COM_sanitizeID(COM_applyFilter ($_POST['sid']));
        } else if (isset($_GET['sid']) ) {
            $sid = COM_sanitizeID(COM_applyFilter ($_GET['sid']));
        } else {
            $sid = '';
        }
        if ( isset($_POST['type']) ) {
            $type = COM_applyFilter ($_POST['type']);
        } else if (isset($_GET['type']) ) {
            $type = COM_applyFilter ($_GET['type']);
        } else {
            $type = '';
        }
        if ( $type != 'article' ) {
            if (!in_array($type,$_PLUGINS) ) {
                $type = '';
            }
        }
        if (!is_numeric ($cid) || ($cid < 0) || empty ($sid) || empty ($type)) {
            COM_errorLog("handleEdit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to edit a comment with one or more missing/bad values.');
            echo COM_refresh($_CONF['site_url'] . '/index.php');
            exit;
        }
        $pid = isset($_REQUEST['pid']) ? COM_applyFilter($_REQUEST['pid'],true) : 0;
        $result = DB_query ("SELECT * FROM {$_TABLES['comments']} "
            . "WHERE queued=0 AND cid = ".(int) $cid." AND sid = '".DB_escapeString($sid)."' AND type = '".DB_escapeString($type)."'");
        if ( DB_numRows($result) == 1 ) {
            $A = DB_fetchArray ($result);
        } else {
            COM_errorLog("handleEdit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to edit a comment that doesn\'t exist as described.');
            return COM_refresh($_CONF['site_url'] . '/index.php');
        }
    } else {
// moderator or admin edit
        $result = DB_query("SELECT * FROM {$_TABLES['comments']} WHERE cid=".(int) $cid);
        if ( DB_numRows($result) == 1 ) {
            $A = DB_fetchArray ($result);
            $sid = $A['sid'];
            $type = $A['type'];
            $pid = $A['pid'];
        } else {
            COM_errorLog("handleEdit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to edit a comment that doesn\'t exist.');
            return COM_refresh($_CONF['site_admin_url'] . '/moderation.php');
        }

    }
    $title = $A['title'];
    $commenttext = COM_undoSpecialChars ($A['comment']);

    //remove signature
    $pos = strpos( $commenttext,'<!-- COMMENTSIG --><div class="comment-sig">');
    if ( $pos > 0) {
        $commenttext = substr($commenttext, 0, $pos);
    }
    if ( !isset($A['postmode']) || $A['postmode'] == NULL || $A['postmode'] == '') {
        //get format mode
        if ( preg_match( '/<.*>/', $commenttext ) != 0 ){
            $postmode = 'html';
        } else {
            $postmode = 'plaintext';
        }
    } else {
        $postmode = $A['postmode'];
    }
    if ( $mod ) {
        $retval = CMT_commentForm ($title, $commenttext, $sid,$pid, $type, 'modedit', $postmode);
    } else {
        $edit_type = 'edit';
        if ( $admin == true ) $edit_type = 'adminedit';
//        $retval =  PLG_displayComment($type, $sid, 0, $title, '', 'nobar', 0, 0)
        $retval =  CMT_commentForm ($title, $commenttext, $sid,$pid, $type, $edit_type, $postmode);
    }
    return $retval;
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
    global $_CONF, $_TABLES, $_USER, $LANG03, $_PLUGINS;

    $modedit = false;
    $adminedit = false;

    $type       = COM_applyFilter ($_POST['type']);
    $sid        = COM_sanitizeID(COM_applyFilter ($_POST['sid']));
    $cid        = COM_applyFilter ($_POST['cid'],true);
    $postmode   = COM_applyFilter ($_POST['postmode']);
    if ( isset($_POST['modedit'])) $modedit    = COM_applyFilter ($_POST['modedit']);
    if ( isset($_POST['adminedit'])) $adminedit  = COM_applyFilter ($_POST['adminedit']);

    $moderatorEdit = false;
    if ( $modedit == 'x' && SEC_hasRights('comment.moderate') ) $moderatorEdit = true;

    if ( $type != 'article' ) {
        if (!in_array($type,$_PLUGINS) ) {
            $type = '';
        }
    }

    $commentuid = DB_getItem ($_TABLES['comments'], 'uid', "cid = ".(int) $cid);
    if ( COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $comment = $_POST['comment_text'];

    //check for bad input
    if (empty ($sid) || empty ($comment) || !is_numeric ($cid) || $cid < 1 ) {
        COM_errorLog("handleEditSubmit(): {{$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to edit a comment with one or more missing values.');
        return COM_refresh($_CONF['site_url'] . '/index.php');
    } elseif ( $uid != $commentuid && !SEC_hasRights( 'comment.moderate' ) ) {
        //check permissions
        COM_errorLog("handleEditSubmit(): {{$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to edit a comment without proper permission.');
        return COM_refresh($_CONF['site_url'] . '/index.php');
    }

    $comment    = CMT_prepareText($comment, $postmode,true,$cid);
    $title      = COM_checkWords (strip_tags ($_POST['title']));

    if (!empty ($title) && !empty ($comment)) {
        COM_updateSpeedlimit ('comment');
        $title   = DB_escapeString ($title);
        $comment = DB_escapeString ($comment);

        // check if commentuid > 1
        if ( $commentuid == 1 ) {
            $filter = sanitizer::getInstance();
            // anonymous user - so the name could have been edited.
            $name = @htmlspecialchars(strip_tags(trim(COM_checkWords(USER_sanitizeName($_POST['username'])))),ENT_QUOTES,COM_getEncodingt());

            $sql = "UPDATE {$_TABLES['comments']} SET comment = '$comment', title = '$title', name='".DB_escapeString($name)."',postmode='".DB_escapeString($postmode)."'"
                    . " WHERE cid=".(int)$cid." AND sid='".DB_escapeString($sid)."'";
        } else {
        // save the comment into the comment table
            $sql = "UPDATE {$_TABLES['comments']} SET comment = '$comment', title = '$title', postmode='".DB_escapeString($postmode)."'"
                    . " WHERE cid=".(int)$cid." AND sid='".DB_escapeString($sid)."'";
        }
        DB_query($sql);

        if (DB_error($sql) ) { //saving to non-existent comment or comment in wrong article
            COM_errorLog("handleEditSubmit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
            . 'to edit to a non-existent comment or the cid/sid did not match');
            return COM_refresh($_CONF['site_url'] . '/index.php');
        }

        $silentEdit = false;
        if ( isset($_POST['silent_edit']) && SEC_hasRights('comment.moderate') ) {
            $silentEdit = true;
        }

        if ( !$moderatorEdit && $silentEdit == false ) {
            PLG_itemSaved((int) $cid,'comment');
            $safecid = (int) $cid;
            $safeuid = (int) $uid;
            DB_save($_TABLES['commentedits'],'cid,uid,time',"$safecid,$safeuid,NOW()");
        }
    } else {
        COM_errorLog("handleEditSubmit(): {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to submit a comment with invalid $title and/or $comment.');
        return COM_refresh($_CONF['site_url'] . '/index.php');
    }

    if ( !$moderatorEdit) PLG_commentEditSave($type,$cid,$sid);

    if ( $moderatorEdit ) {
        echo COM_refresh($_CONF['site_admin_url'].'/moderation.php');
    }

    $urlArray = PLG_getCommentUrlId($type);
    if ( is_array($urlArray) ) {
        $url = $urlArray[0] . '?' . $urlArray[1].'='.$sid;
        echo COM_refresh($url);
        exit;
    }
    return COM_refresh($_CONF['site_url'] . '/index.php');
}

function handleSubscribe($sid,$type)
{
    global $_CONF, $_TABLES, $_USER;

    $dirty_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_CONF['site_url'];
    if ( $dirty_referer == '' ) {
        $dirty_referer = $_CONF['site_url'];
    }

    $referer = COM_sanitizeUrl($dirty_referer);

    $sLength = strlen($_CONF['site_url']);
    if ( substr($referer,0,$sLength) != $_CONF['site_url'] ) {
        $referer = $_CONF['site_url'];
    }
    $hasargs = strstr( $referer, '?' );
    if ( $hasargs ) {
        $sep = '&amp;';
    } else {
        $sep = '?';
    }

    if ( COM_isAnonUser() ) {
        echo COM_refresh($referer.$sep.'msg=518');
        exit;
    }
    $uid = $_USER['uid'];

    $itemInfo = PLG_getItemInfo($type,$sid,('url,title'));
    if ( isset($itemInfo['title']) ) {
        $id_desc = $itemInfo['title'];
    } else {
        $id_desc = 'not defined';
    }

    $rc = PLG_subscribe('comment',$type,$sid,$uid,$type,$id_desc);
    if ( $rc === false ) {
        echo COM_refresh($referer.$sep.'msg=519'.'#comments');
        exit;
    }
    echo COM_refresh($referer.$sep.'msg=520'.'#comments');
    exit;
}

function handleunSubscribe($sid,$type)
{
    global $_CONF, $_TABLES, $_USER;

    $dirty_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_CONF['site_url'];
    if ( $dirty_referer == '' ) {
        $dirty_referer = $_CONF['site_url'];
    }
    $referer = COM_sanitizeUrl($dirty_referer);

    $sLength = strlen($_CONF['site_url']);
    if ( substr($referer,0,$sLength) != $_CONF['site_url'] ) {
        $referer = $_CONF['site_url'];
    }
    if ( strcasecmp($referer,$_CONF['site_url'].'/users.php')  == 0 ) {
        $referer = $_CONF['site_url'];
    }
    $hasargs = strstr( $referer, '?' );
    if ( $hasargs ) {
        $sep = '&amp;';
    } else {
        $sep = '?';
    }
    if ( COM_isAnonUser() ) {
        $display = COM_siteHeader();
        $display .= SEC_loginRequiredForm();
        $display .= COM_siteFooter();
        echo $display;
        exit;
    }

    $rc = PLG_unsubscribe('comment',$type,$sid);
    echo COM_refresh($referer.$sep.'msg=521'.'#comments');
    exit;
}

// MAIN
CMT_updateCommentcodes();

$display = '';
$pageBody = '';
$pageTitle = '';

// If reply specified, force comment submission form
if (isset ($_REQUEST['reply'])) {
    $_REQUEST['mode'] = '';
}

$mode = '';
if (!empty ($_REQUEST['mode'])) {
    $mode = COM_applyFilter ($_REQUEST['mode']);
}

if ( isset($_POST['cancel'] ) ) {
    if ( isset($_POST['modedit']) && $_POST['modedit'] == 'x' ) echo COM_refresh($_CONF['site_admin_url'].'/moderation.php');

    $type = COM_applyFilter($_POST['type']);
    $sid  = COM_sanitizeID(COM_applyFilter($_POST['sid']));

    if ( $type != 'article' ) {
        if (!in_array($type,$_PLUGINS) ) {
            $type = '';
        }
    }

    $urlArray = PLG_getCommentUrlId($type);
    if ( is_array($urlArray) ) {
        $url = $urlArray[0] . '?' . $urlArray[1].'='.$sid;
        echo COM_refresh($url);
        exit;
    }
    $pageBody = PLG_displayComment($type, $sid, 0, '', '', '', 0, 0);
} elseif ( isset($_POST['preview']) ) {
    $pageTitle = $LANG03[14];
    $comment = '';

    $comment = isset($_POST['comment_text']) ? $_POST['comment_text'] : '';

    $type    = isset($_POST['type']) ? COM_applyFilter ($_POST['type']) : '';
    $sid     = isset($_POST['sid']) ? COM_sanitizeID(COM_applyFilter ($_POST['sid'])) : '';
    $pid     = isset($_POST['pid']) ? COM_applyFilter ($_POST['pid'],true) : 0;
    $postmode = isset($_POST['postmode']) ? COM_applyFilter($_POST['postmode']) : 'text';
    $title   = isset($_POST['title']) ? strip_tags ($_POST['title']) : '';
    $mode    = isset($_POST['mode']) ? COM_applyFilter($_POST['mode']) : '';

    $modedit = isset($_POST['modedit']) ? COM_applyFilter($_POST['modedit']) : '';
    $adminedit = isset($_POST['adminedit']) ? COM_applyFilter($_POST['adminedit']) : '';

    $moderatorEdit = false;
    if ( $modedit == 'x' ) {
        $moderatorEdit = true;
    }
    $administratorEdit = false;
    if ( $adminedit  == 'x' && SEC_hasRights('comment.moderate')) {
        $administratorEdit = true;
    }

    if ( $type != 'article' ) {
        if (!in_array($type,$_PLUGINS) ) {
            $type = '';
        }
    }

    if ( $moderatorEdit ) {
        $previewType = 'preview_edit_mod';
    } elseif ( $administratorEdit ) {
        $previewType = 'preview_edit_admin';
    } elseif ( $mode == 'edit' ) {
        $previewType = 'preview_edit';
    } elseif ($mode == 'new' ) {
        $previewType = 'preview_new';
    } else {
        $previewType = 'preview_new';
    }
    if ( $moderatorEdit || $administratorEdit || $previewType == 'preview_edit') {
        $pageBody .= CMT_commentForm ($title, $comment,$sid,$pid,$type, $previewType,$postmode);
    } else {
        $pageBody .=  PLG_displayComment($type, $sid, 0, $title, '', 'nobar', 0, 0)
              . CMT_commentForm ($title, $comment,$sid,$pid,$type, $previewType,$postmode);
    }

} elseif (isset($_POST['saveedit']) ) {
    if (SEC_checkToken()) {
        $pageBody .= handleEditSubmit();
    } else {
        echo COM_refresh($_CONF['site_url'] . '/index.php');
        exit;
    }

} elseif ( isset($_POST['savecomment'] ) ) {
    if ( SEC_checkToken() ) {
        $subReturn = handleSubmit();
        if ( $subReturn != '' ) {
            $type    = COM_applyFilter ($_POST['type']);
            $sid     = COM_sanitizeID(COM_applyFilter ($_POST['sid']));
            $pid     = COM_applyFilter ($_POST['pid'],true);
            $postmode = COM_applyFilter($_POST['postmode']);
            $title   = strip_tags ($_POST['title']);
            $mode    = COM_applyFilter($_POST['mode']);

            if ( $type != 'article' ) {
                if (!in_array($type,$_PLUGINS) ) {
                    $type = 'article';
                }
            }
            $pageBody .= PLG_displayComment($type, $sid, 0, $title, '', 'nobar', 0, 0) . $subReturn;
        }
    } else {
        echo COM_refresh($_CONF['site_url'] . '/index.php');
        exit;
    }

} elseif ( isset($_POST['delete'] ) || $mode == 'delete' ) {
    if (SEC_checkToken()) {
        $pageBody .= handleDelete();
    } else {
        echo COM_refresh($_CONF['site_url'] . '/index.php');
        exit;
    }

} elseif ( isset($_POST['sendreport'] ) ) {
    if (SEC_checkToken()) {
        if (isset($_POST['type']) ) {
            $type = $_POST['type'];
            if ( $type != 'article' ) {
                if (!in_array($type,$_PLUGINS) ) {
                    $type = 'article';
                }
            }
        } else {
            $type = '';
        }
        $pageBody .= CMT_sendReport(COM_sanitizeID(COM_applyFilter($_POST['cid'], true)),
                                   $type);
    } else {
        echo COM_refresh($_CONF['site_url'] . '/index.php');
        exit;
    }
} else {
    // finished with button checks, now look at $_GET items...
    switch ( $mode ) {
        case 'view':
            $pageBody .= handleView(true);
            break;

        case 'display':
            $pageBody .= handleView(false);
            break;

        case 'report':
            if (isset($_POST['type']) ) {
                $type = $_POST['type'];
                if ( $type != 'article' ) {
                    if (!in_array($type,$_PLUGINS) ) {
                        $type = 'article';
                    }
                }
            } else {
                $type = '';
            }
            $pageTitle = $LANG03[27];
            $pageBody .= CMT_reportAbusiveComment (COM_applyFilter ($_GET['cid'], true),
                                 $type);
            break;

        case 'edit':
            $pageBody .= handleEdit();
            break;

        case 'modedit' :
            $pageBody .= handleEdit(true);
            break;

        case 'adminedit' :
            $pageBody .= handleEdit(false,true);
            break;

        case 'subscribe' :
            if ( isset($_GET['sid']) ) {
                $sid = COM_sanitizeID(COM_applyFilter($_GET['sid']));
                $type = COM_applyFilter($_GET['type']);
                if ( $type != 'article' ) {
                    if (!in_array($type,$_PLUGINS) ) {
                        $type = 'article';
                    }
                }
                $pageBody .= handleSubscribe($sid,$type);
            } else {
                echo COM_refresh($_CONF['site_url'] . '/index.php');
                exit;
            }
            break;

        case 'unsubscribe' :
            if ( isset($_GET['sid']) ) {
                $sid = COM_sanitizeID(COM_applyFilter($_GET['sid']));
                $type = COM_applyFilter($_GET['type']);
                if ( $type != 'article' ) {
                    if (!in_array($type,$_PLUGINS) ) {
                        $type = 'article';
                    }
                }
                $pageBody .= handleunSubscribe($sid,$type);
            } else {
                echo COM_refresh($_CONF['site_url'] . '/index.php');
                exit;
            }
            break;

        default:  // New Comment
            // do our speed limit check here
            COM_clearSpeedlimit ($_CONF['commentspeedlimit'], 'comment');
            $last = 0;
            $last = COM_checkSpeedlimit ('comment');

            if ($last > 0) {
                $goBack = '<br/><br/>'.$LANG03[48];
                $pageBody .= COM_showMessageText($LANG03[7].$last.$LANG03[8].$goBack,$LANG12[26],true,'error');
            } else {
                $sid   = isset($_REQUEST['sid']) ? COM_sanitizeID(COM_applyFilter ($_REQUEST['sid'])) : '';
                $type  = isset($_REQUEST['type']) ? COM_applyFilter ($_REQUEST['type']) : '';
                $title = isset($_REQUEST['title']) ? strip_tags($_REQUEST['title']) : '';
                $postmode = $_CONF['comment_postmode'];
                $pid = isset($_REQUEST['pid']) ? COM_applyFilter($_REQUEST['pid'],true) : 0;

                if ( $type != 'article' ) {
                    if (!in_array($type,$_PLUGINS) ) {
                        $type = '';
                    }
                }

                if (!empty ($sid) && !empty ($type)) {
                    if (empty ($title)) {
                        if ($type == 'article') {
                            $title = DB_getItem($_TABLES['stories'], 'title',
                                                "sid = '".DB_escapeString($sid)."'"
                                                . COM_getPermSQL('AND')
                                                . COM_getTopicSQL('AND'));
                        }
                        // CMT_commentForm expects non-htmlspecial chars for title...
                        $title = str_replace ( '&amp;', '&', $title );
                        $title = str_replace ( '&quot;', '"', $title );
                        $title = str_replace ( '&lt;', '<', $title );
                        $title = str_replace ( '&gt;', '>', $title );
                    }
                    if ( isset($_CONF['comment_engine']) && $_CONF['comment_engine'] != 'internal') {
                        $pageBody = PLG_displayComment($type, $sid, 0, $title, '', 'nobar', 0, 0);
                    } else {
                        $currentComments = PLG_displayComment($type, $sid, 0, $title, '', 'nobar', 0, 0);
                        if ( $currentComments != false ) {
                            $outputHandle = outputHandler::getInstance();
                            $outputHandle->addMeta('name','robots','noindex');
                            $pageBody .= $currentComments
                                      .  CMT_commentForm ($title, '', $sid,$pid, $type, $mode,$postmode);
                        } else {
                            COM_404();
                        }
                    }
                } else {
                    echo COM_refresh($_CONF['site_url'].'/index.php');
                    exit;
                }
            }
            break;
    }
}

echo COM_siteHeader('menu',$pageTitle);
echo $pageBody;
echo COM_siteFooter();
?>