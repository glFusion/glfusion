<?php
/**
* glFusion CMS
*
* glFusion comment library.
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*   Authors: Tony Bibbs       - tony AT tonybibbs DOT com
*            Mark Limburg       - mlimburg AT users DOT sourceforge DOT net
*            Jason Whittenburg  - jwhitten AT securitygeeks DOT com
*            Dirk Haun          - dirk AT haun-online DOT de
*            Vincent Furia      - vinny01 AT users DOT sourceforge DOT net
*            Jared Wenerd       - wenerd87 AT gmail DOT com
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Formatter;
use \glFusion\Log\Log;
use \glFusion\FieldList;

USES_lib_user();

/*
 * category = comment type i.e.; filemgmt, article
 * track_id = item being tracked
 * post_id  = comment it
 * uid      = userid who posted the comment
 */

function plugin_subscription_email_format_comment($category,$track_id,$post_id,$uid)
{
    global $_CONF, $_USER, $_TABLES, $LANG01, $LANG03, $LANG04;

    $db = Database::getInstance();

    $dt = new Date('now',$_USER['tzid']);
    $permalink = 'Not defined';

    $filter = sanitizer::getInstance();
    $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
    $filter->setAllowedelements($AllowedElements);
    $filter->setNamespace('glfusion','comment');

    $post_id = filter_var($post_id, FILTER_VALIDATE_INT);
    if ($post_id === false) {
        return false;
    }
    $sql = "SELECT * FROM `{$_TABLES['comments']}` WHERE queued = 0 AND cid=?";

    try {
        $commentRecord = $db->conn->fetchAssoc($sql, array($post_id),array(Database::INTEGER));
    } catch(Throwable $e) {
        if (defined('DVLP_DEBUG')) {
            throw($e);
        }
        return false;
    }

    if ($commentRecord !== false) {
        $itemInfo = PLG_getItemInfo($commentRecord['type'],$track_id,'url,title');
        $permalink = $itemInfo['url'];
        if ( empty($permalink) ) {
            $permalink = $_CONF['site_url'];
        }
        if ( $commentRecord['uid'] > 1 ) {
            $name = COM_getDisplayName($commentRecord['uid']);
        } else {
            $name = $filter->sanitizeUsername($commentRecord['name']);
            $name = $filter->censor($name);
        }

        $name = @htmlspecialchars($name,ENT_QUOTES, COM_getEncodingt(),true);

        $A['title']   = COM_checkWords($commentRecord['title']);
        $html2txt = new Html2Text\Html2Text(strip_tags($commentRecord['title']),false);
        $A['title'] = $html2txt->get_text();

        $format = new Formatter();
        $format->setNamespace('glfusion');
        $format->setAction('comment');
        $format->setAllowedHTML($_CONF['htmlfilter_comment']);
        $format->setParseAutoTags(true);
        $format->setProcessBBCode(false);
        $format->setCensor(true);
        $format->setProcessSmilies(true);
        $format->setType($commentRecord['postmode']);
        $commentRecord['comment'] = $format->parse($commentRecord['comment']);

        $notifymsg = sprintf($LANG03[46],'<a href="'.$_CONF['site_url'].'/comment.php?mode=unsubscribe&sid='.htmlentities($track_id).'&type='.$commentRecord['type'].'" rel="nofollow">'.$LANG01['unsubscribe'].'</a>');

        $dt->setTimestamp(strtotime($commentRecord['date']));
        $date = $dt->format('F d Y @ h:i a');
        $T = new Template( $_CONF['path_layout'] . 'comment' );
        $T->set_file (array(
            'htmlemail'     => 'notifymessage_html.thtml',
            'textemail'     => 'notifymessage_text.thtml',
        ));

        $T->set_var(array(
            'post_subject'  => $commentRecord['title'],
            'post_date'     => $date,
            'iso8601_date'  => $dt->toISO8601(),
            'post_name'     => $name,
            'post_comment'  => $commentRecord['comment'],
            'notify_msg'    => $notifymsg,
            'site_name'     => $_CONF['site_name'],
            'online_version' => sprintf($LANG01['view_online'],$permalink),
            'permalink'     => $permalink,
        ));
        $T->parse('htmloutput','htmlemail');
        $message = $T->finish($T->get_var('htmloutput'));
        $T->parse('textoutput','textemail');
        $msgText = $T->finish($T->get_var('textoutput'));

        $html2txt = new Html2Text\Html2Text($msgText,false);

        $messageText = $html2txt->get_text();

        $msgData = array();
        $msgData['msgtext'] = $messageText;
        $msgData['msghtml'] = $message;
        $msgData['imagedata'] = array();

        return $msgData;
    }
    return false;
}

/**
* This function displays the comment control bar
*
* Prints the control that allows the user to interact with glFusion Comments
*
* @param    string  $sid    ID of item in question
* @param    string  $title  Title of item
* @param    string  $type   Type of item (i.e. article, photo, etc)
* @param    string  $order  Order that comments are displayed in
* @param    string  $mode   Mode (nested, flat, etc.)
* @param    int     $ccode  Comment code: -1=no comments, 0=allowed, 1=closed
* @return   string          HTML Formated comment bar
* @see CMT_userComments
* @see CMT_commentChildren
*
*/
function CMT_commentBar( $sid, $title, $type, $order, $mode, $ccode = 0 )
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG03;

    echo __FUNCTION__ . ' deprecated';die;

    $db = Database::getInstance();

    $parts = explode( '/', $_SERVER['PHP_SELF'] );
    $page = array_pop( $parts );

    $sql = "SELECT COUNT(*) FROM `{$_TABLES['comments']}`
            WHERE sid = ?
                AND type = ?
                AND queued = 0";

    $nrows = $db->conn->fetchColumn($sql,array($sid,$type),0,array(Database::STRING,Database::STRING));

    $commentbar = new Template( $_CONF['path_layout'] . 'comment' );
    $commentbar->set_file( array( 'commentbar' => 'commentbar.thtml' ));

    if ( SESS_isSet('glfusion.commentpostsave') ) {
        $msg = COM_showMessageText(SESS_getVar('glfusion.commentpostsave'),'',1,'warning');
        SESS_unSet('glfusion.commentpostsave');
        $commentbar->set_var('info_message',$msg);
    }

    $commentbar->set_var( 'lang_comments', $LANG01[3] );
    $commentbar->set_var( 'lang_refresh', $LANG01[39] );
    $commentbar->set_var( 'lang_reply', $LANG01[60] );
    if ( $nrows > 0 ) {
        $commentbar->set_var( 'lang_disclaimer', $LANG01[26] );
    } else {
        $commentbar->set_var( 'lang_disclaimer', '' );
    }

    if ( !COM_isAnonUser() ) {
        if ( PLG_isSubscribed('comment',$type,$sid) ) {
            $commentbar->set_var( 'subscribe','[<a href="'.$_CONF['site_url'].'/comment.php?mode=unsubscribe&amp;type='.htmlentities($type).'&amp;sid='.htmlentities($sid).'" rel="nofollow">'.$LANG01['unsubscribe'].'</a>]&nbsp;');
            $commentbar->set_var( 'subscribe_url' , $_CONF['site_url'].'/comment.php?mode=unsubscribe&amp;type='.htmlentities($type).'&amp;sid='.htmlentities($sid));
            $commentbar->set_var( 'subscribe_text', $LANG01['unsubscribe']);
        } else {
            $commentbar->set_var( 'subscribe','[<a href="'.$_CONF['site_url'].'/comment.php?mode=subscribe&amp;type='.htmlentities($type).'&amp;sid='.htmlentities($sid).'" rel="nofollow">'.$LANG01['subscribe'].'</a>]&nbsp;');
            $commentbar->set_var( 'subscribe_url', $_CONF['site_url'].'/comment.php?mode=subscribe&amp;type='.htmlentities($type).'&amp;sid='.htmlentities($sid));
            $commentbar->set_var( 'subscribe_text', $LANG01['subscribe']);
        }
    } else {
        $commentbar->unset_var('subscribe');
    }

    if ( $ccode == 1 ) {
        $commentbar->set_var( 'reply_hidden_or_submit', 'hidden');
        $commentbar->set_var( 'comment_option_text', $LANG03[49]);
    } elseif ( $ccode == -1 ) {
        $commentbar->set_var( 'reply_hidden_or_submit', 'hidden');
        $commentbar->set_var( 'comment_option_text', $LANG03[49]);
    } elseif ( $ccode == 0 && ($_CONF['commentsloginrequired'] == 0 || !COM_isAnonUser())) {
        $commentbar->set_var( 'reply_hidden_or_submit', 'submit' );
        $commentbar->unset_var( 'comment_option_text');
    } else {
        $commentbar->set_var( 'reply_hidden_or_submit', 'hidden' );
        $commentbar->set_var( 'comment_option_text', sprintf($LANG03[50],$_CONF['site_url']) );
    }
    $commentbar->set_var( 'num_comments', COM_numberFormat( $nrows ));
    $commentbar->set_var( 'comment_type', $type );
    $commentbar->set_var( 'sid', $sid );

    $cmt_title = $title;
    $commentbar->set_var('story_title', $cmt_title);
    // Article's are pre-escaped.
    if ($type != 'article') {
        $cmt_title = @htmlspecialchars($cmt_title,ENT_COMPAT,COM_getEncodingt());
    }
    $commentbar->set_var('comment_title', $cmt_title);

    if( $type == 'article' ) {
        $articleUrl = COM_buildUrl( $_CONF['site_url']."/article.php?story=$sid" );
        $commentbar->set_var( 'story_link', $articleUrl );
        $commentbar->set_var( 'article_url', $articleUrl );

        if( $page == 'comment.php' ) {
            $commentbar->set_var('story_link',
                COM_createLink(
                    $title,
                    $articleUrl,
                    array('class'=>'non-ul b')
                )
            );
            $commentbar->set_var( 'start_storylink_anchortag', '<a href="'
                . $articleUrl . '" class="non-ul">' );
            $commentbar->set_var( 'end_storylink_anchortag', '</a>' );
        }
    } else { // for a plugin
        // Link to plugin defined link or lacking that a generic link that the plugin should support (hopefully)
        list($plgurl, $plgid) = PLG_getCommentUrlId($type);
        $commentbar->set_var( 'story_link', "$plgurl?$plgid=$sid" );
        $cmt_title = @htmlspecialchars($cmt_title,ENT_COMPAT,COM_getEncodingt());
    }
    $commentbar->set_var('comment_title', $cmt_title);
    if (! COM_isAnonUser()) {
        $username = $_USER['username'];
        $fullname = $_USER['fullname'];
    } else {
        $N = $db->conn->fetchAssoc("SELECT username,fullname FROM `{$_TABLES['users']}` WHERE uid=1");
        $username = $N['username'];
        $fullname = $N['fullname'];
    }
    if( empty( $fullname )) {
        $fullname = $username;
    }
    $commentbar->set_var( 'user_name', $username );
    $commentbar->set_var( 'user_fullname', $fullname );

    if (! COM_isAnonUser()) {
        $author = COM_getDisplayName( $_USER['uid'], $username, $fullname );
        $commentbar->set_var( 'user_nullname', $author );
        $commentbar->set_var( 'author', $author );
        $commentbar->set_var( 'login_logout_url',
                              $_CONF['site_url'] . '/users.php?mode=logout' );
        $commentbar->set_var( 'lang_login_logout', $LANG01[35] );
    } else {
        $commentbar->set_var( 'user_nullname', '' );
        $commentbar->set_var( 'login_logout_url',
                              $_CONF['site_url'] . '/users.php?mode=new' );
        $commentbar->set_var( 'lang_login_logout', $LANG01[61] );
    }

    if( $page == 'comment.php' ) {
        $commentbar->set_var( 'parent_url',
                              $_CONF['site_url'] . '/comment.php' );
        $hidden = '';
        $hmode = isset($_REQUEST['mode']) ? COM_applyFilter($_REQUEST['mode']) : 'entry';
        if( $hmode == 'view' ) {
            $hidden .= '<input type="hidden" name="cid" value="' . @htmlspecialchars(COM_applyFilter($_REQUEST['cid']),ENT_COMPAT,COM_getEncodingt()) . '"/>';
            $hidden .= '<input type="hidden" name="pid" value="' . @htmlspecialchars(COM_applyFilter($_REQUEST['cid']),ENT_COMPAT,COM_getEncodingt()) . '"/>';
        }
        else if( $hmode == 'display' ) {
            $hidden .= '<input type="hidden" name="pid" value="' . @htmlspecialchars(COM_applyFilter($_REQUEST['pid']),ENT_COMPAT,COM_getEncodingt()) . '"/>';
        }
        $commentbar->set_var( 'hidden_field', $hidden .
                '<input type="hidden" name="mode" value="' . @htmlspecialchars($hmode,ENT_COMPAT,COM_getEncodingt()) . '"/>' );
    } else if( $type == 'article' ) {
        $commentbar->set_var( 'parent_url',
                              $_CONF['site_url'] . '/article.php#comments' );
        $commentbar->set_var( 'hidden_field',
                '<input type="hidden" name="story" value="' . $sid . '"/>' );
    } else { // plugin
        // Link to plugin defined link or lacking that a generic link that the plugin should support (hopefully)
        list($plgurl, $plgid) = PLG_getCommentUrlId($type);
        $commentbar->set_var( 'parent_url', $plgurl );
        $commentbar->set_var( 'hidden_field',
                '<input type="hidden" name="' . $plgid . '" value="' . $sid . '"/>' );
    }

    // Order
    $selector_data = COM_optionList( $_TABLES['sortcodes'], 'code,name', $order );
    $commentbar->set_var( 'order_selector', $selector_data);

    // Mode
    $selector_data = COM_optionList( $_TABLES['commentmodes'], 'mode,name', $mode );
    $commentbar->set_var( 'mode_selector', $selector_data);
    $commentbar->set_var( 'mode_select_field_name', ($page == 'comment.php' ? 'format' : 'mode' ) ) ;

    return $commentbar->finish( $commentbar->parse( 'output', 'commentbar' ));
}


/**
* This function prints &$comments (db results set of comments) in comment format
* -For previews, &$comments is assumed to be an associative array containing
*  data for a single comment.
*
* @param    array    &$comments Database result set of comments to be printed
* @param    string   $mode      'flat', 'threaded', etc
* @param    string   $type      Type of item (article, polls, etc.)
* @param    string   $order     How to order the comments 'ASC' or 'DESC'
* @param    boolean  $delete_option   if current user can delete comments
* @param    boolean  $preview   Preview display (for edit) or not
* @param    int      $ccode     Comment code: -1=no comments, 0=allowed, 1=closed
* @return   string   HTML       Formated Comment
*
*/
function CMT_getComment( &$comments, $mode, $type, $order, $delete_option = false, $preview = false, $ccode = 0, $sid_author_id = '' )
{
    echo __FUNCTION__ . ' deprecated';

    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG03, $MESSAGE, $_IMAGE_TYPE;

    $indent = 0;  // begin with 0 indent
    $retval = ''; // initialize return value

    static $userInfo = array();

    $db = Database::getInstance();

    if ( $mode == 'threaded' ) $mode = 'nested';

    $template = new Template( $_CONF['path_layout'] . 'comment' );
    $template->set_file( array( 'comment' => 'comment.thtml',
                                'thread'  => 'thread.thtml'  ));

    // generic template variables
    $template->set_var( 'lang_authoredby', $LANG01[42] );
    $template->set_var( 'lang_on', $LANG01[36] );
    $template->set_var( 'lang_permlink', $LANG01[120] );
    $template->set_var( 'order', $order );

    if( $ccode == 0 && ($_CONF['commentsloginrequired'] == 0 || !COM_isAnonUser())) {
        $template->set_var( 'lang_replytothis', $LANG01[43] );
        $template->set_var( 'lang_reply', $LANG01[25] );
    } else {
        $template->set_var( 'lang_replytothis', '' );
        $template->set_var( 'lang_reply', '' );
    }

    // Make sure we have a default value for comment indentation
    if( !isset( $_CONF['comment_indent'] )) {
        $_CONF['comment_indent'] = 25;
    }

    // Build the comment data record

    $resultSet = array();

//@TODO - appears if $preview is true- we pass the results instead of the result set
    if ($preview) {
        if ( isset($comments['comment_text'])) {
            $comments['comment'] = $comments['comment_text'];
        }

        $A = $comments;
        if( empty( $A['nice_date'] )) {
            $A['nice_date'] = time();
        }
        if( !isset( $A['cid'] )) {
            $A['cid'] = 0;
        }
        if( !isset( $A['photo'] )) {
            if( isset( $_USER['photo'] )) {
                $A['photo'] = $_USER['photo'];
            } else {
                $A['photo'] = '';
            }
        }
        if (! isset($A['email'])) {
            if (isset($_USER['email'])) {
                $A['email'] = $_USER['email'];
            } else {
                $A['email'] = '';
            }
        }
        $A['name'] = $A['username'];
        $mode = 'flat';
        $template->set_var('preview_mode',true);
        $resultSet[] = $A;
    } else {
//@TODO - $comments is passed from another function -
//          really isn't a good practice -
        $resultSet = $comments->fetchAll(Database::ASSOCIATIVE);
        $template->unset_var('preview_mode');
    }

    if (count($resultSet) == 0 ) {
        return '';
    }

    // initialize our format class

    $filter = sanitizer::getInstance();
    $filter->setNamespace('glfusion','comment');
    $filter->setPostmode('text');

    $format = new Formatter();
    $format->setNamespace('glfusion');
    $format->setAction('comment');
    $format->setAllowedHTML($_CONF['htmlfilter_comment']);
    $format->setParseAutoTags(true);
    $format->setProcessBBCode(false);
    $format->setCensor(true);
    $format->setProcessSmilies(true);

    $token = '';
    if ($delete_option && !$preview) {
        $token = SEC_createToken();
    }

    $row = 1;

    foreach($resultSet AS $A) {
        if ( !isset($A['postmode']) || $A['postmode'] == NULL || $A['postmode'] == '') {
            //get format mode
            if ( preg_match( '/<.*>/', $A['comment'] ) != 0 ) {
                $postmode = 'html';
            } else {
                $postmode = 'plaintext';
            }
        } else {
            $postmode = $A['postmode'];
        }

        //remove any old signatures signature
        $pos = strpos( $A['comment'],'<!-- COMMENTSIG --><div class="comment-sig">');
        if ( $pos > 0) {
            $A['comment'] = substr($A['comment'], 0, $pos);
        } else {
            $pos = strpos( $A['comment'],'<p>---<br />');
            if ( $pos > 0 ) {
                $A['comment'] = substr($A['comment'], 0, $pos);
            }
        }

        // get user information for this comment author

        if ( !isset($A['uid']) || $A['uid'] == '' ) {
            $A['uid'] = 1;
        }

//@TODO - Move this to function
        if ( $A['uid'] > 1 ) {
            if (!isset($userInfo[$A['uid']])) {
                $sql = "SELECT username,remoteusername,remoteservice,fullname,sig,photo
                         FROM `{$_TABLES['users']}` WHERE uid=?";
                $userData = $db->conn->fetchAssoc($sql,array($A['uid']),array(Database::INTEGER));
                if ($userData) {
                    $userInfo[$A['uid']] = $userData;
                } else {
                    $userInfo[$A['uid']] = array('username' => '','remoteusername' => '','remoteservice'=>'','fullname'=>'','sig'=>'','photo'=>'');
                }
            }
            $A['username'] = $userInfo[$A['uid']]['username'];
            $A['remoteusername'] = $userInfo[$A['uid']]['remoteusername'];
            $A['remoteservice'] = $userInfo[$A['uid']]['remoteservice'];
            $A['fullname'] = $userInfo[$A['uid']]['fullname'];
            $A['sig'] = $userInfo[$A['uid']]['sig'];
            $A['photo'] = $userInfo[$A['uid']]['photo'];
        }

        $format->setType($postmode);

        $commentFooter = '';

        // fixes previous encodings...
        if ( $postmode == 'plaintext') {
            $A['comment'] = htmlspecialchars_decode($A['comment']);
            $postmode = 'text';
        }

        $template->unset_var('delete_link');
        $template->unset_var('ipaddress');
        $template->unset_var('reply_link');
        $template->unset_var('edit_link');

        //check for comment edit
        $sql = "SELECT cid,uid,UNIX_TIMESTAMP(time) as time
                    FROM `{$_TABLES['commentedits']}` WHERE cid = ?";
        $B = $db->conn->fetchAssoc($sql,array($A['cid']),array(Database::INTEGER));
        if ($B) { //comment edit present
            //get correct editor name
            if ($A['uid'] == $B['uid']) {
                $editname = $A['username'];
            } else {
                $editname = $db->conn->fetchColumn("SELECT username FROM `{$_TABLES['users']}`
                        WHERE uid=?",array($B['uid']),0,array(Database::INTEGER));
            }
            //add edit info to text
            $dtObject = new Date($B['time'],$_USER['tzid']);

            $commentFooter .= LB . '<div class="comment-edit">' . $LANG03[30] . ' '
                                          . $dtObject->format($_CONF['date'],true) . ' ' . $LANG03[31] . ' '
                                          . $editname . '</div><!-- /COMMENTEDIT -->';
        }


        // determines indentation for current comment
        if( $mode == 'threaded' || $mode == 'nested' ) {
            $indent = ($A['indent'] - $A['pindent']) * $_CONF['comment_indent'];
        }

        // comment variables

        $template->set_var(array(
            'indent'        => $indent,
            'author_name'   => $filter->sanitizeUsername($A['username']),
            'author_id'     => $A['uid'],
            'cid'           => $A['cid'],
            'pid'           => $A['pid'],
            'cssid'         => $row % 2,
        ));

        // set author match flag for styling
        if ( $sid_author_id != '' && $sid_author_id != 1 && ($sid_author_id == $A['uid'] ) ) {
            $template->set_var('author_match','1');
        } else {
            $template->set_var('author_match','');
        }

        if( $A['uid'] > 1 ) {
            $fullname = COM_getDisplayName( $A['uid'], $A['username'],
                                            isset($A['fullname']) ? $A['fullname'] : '' );
            $template->set_var( 'author_fullname', $fullname );
            $template->set_var( 'author', $fullname );
            $alttext = $fullname;

            $photo = '';
            if( $_CONF['allow_user_photo'] ) {
                if (isset ($A['photo']) && empty ($A['photo'])) {
                    $A['photo'] = '';
                }

                $photo = USER_getPhoto( $A['uid'], $A['photo'], $A['email'] );
                $photo_raw = USER_getPhoto( $A['uid'], $A['photo'],$A['email'],64,0);
                if( !empty( $photo ) ) {
                    $template->set_var( 'author_photo', $photo );
                    $template->set_var( 'author_photo_raw',$photo_raw);
                    $camera_icon = '<img src="' . $_CONF['layout_url']
                        . '/images/smallcamera.' . $_IMAGE_TYPE . '" alt=""/>';
                    $template->set_var( 'camera_icon',
                        COM_createLink(
                            $camera_icon,
                            $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $A['uid']
                        )
                    );
                } else {
                    $template->set_var( 'author_photo', '<img src="'.$_CONF['default_photo'].'" alt="" class="userphoto"/>' );
                    $template->set_var( 'author_photo_raw', $_CONF['default_photo']);
                    $template->set_var( 'camera_icon', '' );
                }
            } else {
                $template->set_var( 'author_photo_raw', '' );
                $template->set_var( 'author_photo', '' );
                $template->set_var( 'camera_icon', '' );
            }

            $template->set_var( 'start_author_anchortag', '<a href="'
                    . $_CONF['site_url'] . '/users.php?mode=profile&amp;uid='
                    . $A['uid'] . '">' );
            $template->set_var( 'end_author_anchortag', '</a>' );
            $template->set_var( 'author_link',
                COM_createLink(
                    $fullname,
                    $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $A['uid']
                )
            );
            $template->set_var( 'author_url', $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $A['uid']);
        } else {
// anonymous user
            $username = $filter->sanitizeUsername($A['name']);
            if ( $username == '' ) {
                $username = $LANG01[24];
            }

            $template->set_var( 'author', $username);
            $template->set_var( 'author_fullname', $username);
            $template->set_var( 'author_link', @htmlspecialchars($username,ENT_COMPAT,COM_getEncodingt() ));
            $template->unset_var( 'author_url');

            if( $_CONF['allow_user_photo'] ) {
                $template->set_var( 'author_photo_raw', $_CONF['default_photo'] );
                $template->set_var( 'author_photo', '<img src="'.$_CONF['default_photo'].'" alt="" class="userphoto"/>' );
                $template->set_var( 'camera_icon', '' );
            } else {
                $template->set_var( 'author_photo_raw', '' );
                $template->set_var( 'author_photo', '' );
                $template->set_var( 'camera_icon', '' );
            }
            $template->set_var( 'start_author_anchortag', '' );
            $template->set_var( 'end_author_anchortag', '' );
        }

        // hide reply link from anonymous users if they can't post replies
        $hidefromanon = false;
        if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) || ($_CONF['commentsloginrequired'] == 1))) {
            $hidefromanon = true;
        }

        // this will hide HTML that should not be viewed in preview mode
        if( $preview || $hidefromanon ) {
            $template->set_var( 'hide_if_preview', 'style="display:none"' );
        } else {
            $template->set_var( 'hide_if_preview', '' );
        }

        $dtObject = new Date($A['nice_date'],$_USER['tzid']);

        $template->set_var( 'date', $dtObject->format($_CONF['date'],true));
        $template->set_var( 'iso8601_date', $dtObject->toISO8601() );
        $template->set_var( 'sid', $A['sid'] );
        $template->set_var( 'type', $A['type'] );

        //COMMENT edit rights
        $edit_type = 'edit'; // normal user edit
        if ( !COM_isAnonUser() ) {
            if ( $_USER['uid'] == $A['uid'] && $_CONF['comment_edit'] == 1
                    && ($_CONF['comment_edittime'] == 0 || ((time() - $A['nice_date']) < $_CONF['comment_edittime'] )) &&
                    $ccode == 0 &&
                    ($db->conn->fetchColumn("SELECT COUNT(*) FROM `{$_TABLES['comments']}`
                        WHERE queued=0 AND pid=?",array($A['cid']),0,array(Database::INTEGER)) == 0)) {
                $edit_option = true;
            } else if (SEC_hasRights('comment.moderate') ) {
                $edit_option = true;
                $edit_type   = 'adminedit';
            } else {
                $edit_option = false;
            }
        } else {
            $edit_option = false;
        }

        //edit link
        if ($edit_option && !$preview) {
            $editlink = $_CONF['site_url'] . '/comment.php?mode='.$edit_type.'&amp;cid='
                . $A['cid'] . '&amp;sid=' . $A['sid'] . '&amp;type=' . $type
                . '#comment_entry';

            $template->set_var('edit_link',$editlink);
            $template->set_var('lang_edit',$LANG01[4]);
            $edit = COM_createLink( $LANG01[4], $editlink) . ' | ';
        } else {
            $editlink = '';
            $edit = '';
        }

        // If deletion is allowed, displays delete link
        if( $delete_option && !$preview) {
            $deloption = '';
            if ( SEC_hasRights('comment.moderate') ) {
                if( !empty( $A['ipaddress'] ) ) {
                    if( empty( $_CONF['ip_lookup'] )) {
                        $deloption = $A['ipaddress'] . '  | ';
                        $template->set_var('ipaddress', $A['ipaddress']);
                    } else {
                        $iplookup = str_replace( '*', $A['ipaddress'], $_CONF['ip_lookup'] );
                        $template->set_var('iplookup_link', $iplookup);
                        $template->set_var('ipaddress', $A['ipaddress']);
                        $deloption = COM_createLink($A['ipaddress'], $iplookup) . ' | ';
                    }
                    //insert re-que link here
                }
            }
            $dellink = $_CONF['site_url'] . '/comment.php?mode=delete&amp;cid='
                . $A['cid'] . '&amp;sid=' . $A['sid'] . '&amp;type=' . $type
                . '&amp;' . CSRF_TOKEN . '=' . $token;
            $delattr = array('onclick' => "return confirm('{$MESSAGE[76]}');");

            $delete_link = $dellink;
            $template->set_var('delete_link',$delete_link);
            $template->set_var('lang_delete_link_confirm',$MESSAGE[76]);
            $template->set_var('lang_delete',$LANG01[28]);
            $deloption .= COM_createLink( $LANG01[28], $dellink, $delattr) . ' | ';
            $template->set_var( 'delete_option', $deloption . $edit);
        } else if ( $edit_option) {
            $template->set_var( 'delete_option', $edit );
        } elseif (! COM_isAnonUser()) {
            $reportthis = '';
            if ($A['uid'] != $_USER['uid']) {
                $reportthis_link = $_CONF['site_url']
                    . '/comment.php?mode=report&amp;cid=' . $A['cid']
                    . '&amp;type=' . $type;
                $report_attr = array('title' => $LANG01[110]);
                $template->set_var('report_link',$reportthis_link);
                $template->set_var('lang_report',$LANG01[109]);
                $reportthis = COM_createLink($LANG01[109], $reportthis_link,
                                             $report_attr) . ' | ';
            }
            $template->set_var( 'delete_option', $reportthis );
        } else {
            $template->set_var( 'delete_option', '' );
        }

        //and finally: format the actual text of the comment, but check only the text, not sig or edit

        $text = str_replace('<!-- COMMENTSIG --><div class="comment-sig">', '', $A['comment']);
        $text = str_replace('</div><!-- /COMMENTSIG -->', '', $text);
        $text = str_replace('<div class="comment-edit">', '', $text);
        $A['comment'] = str_replace('</div><!-- /COMMENTEDIT -->', '', $text);

        // create a reply to link
        $reply_link = '';
        if( $ccode == 0 &&
         ($_CONF['commentsloginrequired'] == 0 || !COM_isAnonUser())) {
            $reply_link = $_CONF['site_url'] . '/comment.php?sid=' . $A['sid']
                        . '&amp;pid=' . $A['cid'] . '&amp;title='
                        . urlencode($A['title']) . '&amp;type=' . $A['type']
                        . '#comment_entry';
            $template->set_var('reply_link',$reply_link);
            $template->set_var('lang_reply',$LANG01[43]);
            $reply_option = COM_createLink($LANG01[43], $reply_link,
                                           array('rel' => 'nofollow')) . ' | ';
            $template->set_var('reply_option', $reply_option);
        } else {
            $template->set_var( 'reply_option', '' );
        }
        $template->set_var( 'reply_link', $reply_link );

        // format title for display, must happen after reply_link is created
        $A['title'] = @htmlspecialchars( $A['title'],ENT_COMPAT,COM_getEncodingt() );

        $template->set_var( 'title', $A['title'] );

        // add signature if available
        $sig = isset($A['sig']) ? $filter->censor($A['sig']) : '';
        $finalsig = '';
        if ($A['uid'] > 1 && !empty($sig)) {
            $finalsig .= '<div class="comment-sig">';
            $finalsig .= nl2br('---' . LB . $sig);
            $finalsig .= '</div>';
        }

        // sanitize and format comment
        $A['comment'] = $format->parse($A['comment']);

        // highlight search terms if specified. After formatting to avoid introducing
        // strange comment fields
        if( !empty( $_REQUEST['query'] )) {
            $A['comment'] = COM_highlightQuery( $A['comment'], strip_tags($_REQUEST['query']) );
        }

        $template->set_var( 'comments',  $A['comment'].$finalsig.$commentFooter);

        // parse the templates
        if( ($mode == 'threaded') && $indent > 0 ) {
            $template->set_var( 'pid', $A['pid'] );
            $retval .= $template->parse( 'output', 'thread' );
        } else {
            $template->set_var( 'pid', $A['cid'] );
            $retval .= $template->parse( 'output', 'comment' );
        }
        if ( $preview ) {
            return $retval;
        }
        $row++;
    }
    unset($format);
    return $retval;
}

/*
 * new api to get comment links...
 *
 * $type = plugin, article, etc.
 * $sid  = unique identifier for the item
 * $cmtCount - if local, the comment count for the item
 * $url - unique url (canonical) for the item
 * $urlRewrite - if the item supports URL rewrite
 */

function CMT_getCommentLinkWithCount( $type, $sid, $url, $cmtCount = 0, $urlRewrite = 0 ) {
    global $_CONF, $LANG01;

    return glFusion\Comments\CommentEngine::getEngine()->getLinkWithCount($type, $sid, $url, $cmtCount);

    $retval = '';

    if (!isset($_CONF['comment_engine'])) {
        $_CONF['comment_engine'] = 'internal';
    }

    switch ( $_CONF['comment_engine'] ) {
        case 'disqus' :
            if ( $urlRewrite ) {
                $url = COM_buildURL($url.'#disqus_thread');
            } else {
                $url = $url.'#disqus_thread';
            }
            if( $type == 'filemgmt' ) $type = 'filemgmt_fileid';
            $link = '<a href="'.$url.'" data-disqus-identifier='.$type.'_'.$sid.'>';
            $retval = array(
                        'url'   => $url,
                        'url_extra'=> ' data-disqus-identifier="'.$type.'_'.$sid.'"',
                        'link'  => $link,
                        'nonlink'   => '<span class="disqus-comment-count" data-disqus-identifier="'.$type.'_'.$sid.'"></span>',
                        'comment_count'=> '<span class="disqus-comment-count" data-disqus-identifier="'.$type.'_'.$sid.'">0 '.$LANG01[83].'</span>',
                        'comment_text'=> $LANG01[83],
                        'link_with_count' => $link.'<span class="disqus-comment-count" data-disqus-identifier="'.$type.'_'.$sid.'">'.$cmtCount.' '.$LANG01[83].'</span></a>',
                    );
            break;
        case 'facebook' :
            if ( $urlRewrite ) {
                $url = COM_buildURL($url);
            } else {
                $url = $url;
            }
            $link = '<a href="'.$url.'">';

            $retval = array(
                        'url'           => $url,
                        'url_extra'     => '',
                        'link'          => $link,
                        'nonlink'       => '<span class="fb-comments-count" data-href="'.$url.'"></span>',
                        'comment_count' => '<span class="fb-comments-count" data-href="'.$url.'"></span> ' . $LANG01[83],
                        'comment_text'  => $LANG01[83],
                        'link_with_count' => $link.'<span class="fb-comments-count" data-href="'.$url.'"></span>'.' '.$LANG01[83].'</a>',
                    );
            break;
        case 'internal' :
        default :
            $link = '<a href="'.$url.'#comments">';

            $retval = array(
                        'url'           => $url,
                        'url_extra'     => '',
                        'link'          => $link,
                        'nonlink'       => '',
                        'comment_count' => $cmtCount . ' '. $LANG01[83],
                        'comment_text'  => $LANG01[83],
                        'link_with_count' => $link . ' ' . $cmtCount . ' ' . $LANG01[83].'</a>',
                    );
            break;
    }
    return $retval;
}

/**
* This function displays the comments in a high level format.
*
* Begins displaying user comments for an item
*
* @param    string      $sid       ID for item to show comments for
* @param    string      $title     Title of item
* @param    string      $type      Type of item (article, polls, etc.)
* @param    string      $order     How to order the comments 'ASC' or 'DESC'
* @param    string      $mode      comment mode (nested, flat, etc.)
* @param    int         $pid       id of parent comment
* @param    int         $page      page number of comments to display
* @param    boolean     $cid       true if $pid should be interpreted as a cid instead
* @param    boolean     $delete_option   if current user can delete comments
* @param    int         $ccode     Comment code: -1=no comments, 0=allowed, 1=closed
* @return   string  HTML Formated Comments
* @see CMT_commentBar
*
*/
function CMT_userComments( $sid, $title, $type='article', $order='', $mode='', $pid = 0, $page = 1, $cid = false, $delete_option = false, $ccode = 0, $sid_author_id = '' )
{
    $UC = glFusion\Comments\CommentEngine::getEngine();
    if (!empty($mode)) {
        $UC->withMode($mode);
    }
    return $UC->withSid($sid)
              ->withTitle($title)
              ->withType($type)
              ->withOrder($order)
              ->withPid((int)$pid)
              ->withPage((int)$page)
              ->withCid((int)$cid)
              ->withDeleteOption((int)$delete_option)
              ->withCommentCode((int)$ccode)
              ->withSidAuthorId((int)$sid_author_id)
              ->displayComments();

    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG03;

    $retval = '';

    if ( !isset($_CONF['comment_engine'])) $_CONF['comment_engine'] = 'internal';

    $db = Database::getInstance();

    $order = strtoupper($order);

    switch ( $_CONF['comment_engine'] ) {
        case 'disqus' :
            if( $type == 'article' ) {
                $pageURL = COM_buildUrl( $_CONF['site_url']."/article.php?story=$sid" );
                $pageIdentifier = 'article_'.$sid;
            } else { // for a plugin
                // Link to plugin defined link or lacking that a generic link that the plugin should support (hopefully)
                list($pageURL, $plgid) = PLG_getCommentUrlId($type);
                $pageIdentifier = $type.'_'.$sid;
                $pageURL = PLG_getItemInfo($type, $sid, 'url');
            }
            $pageTitle = $title;
            $pageURL = str_replace ( '&amp;', '&', $pageURL );

            $retval = '
            <a name="comment_entry"></a>
            <div id="disqus_thread"></div>
            <script>
                var disqus_config = function () {
                    this.page.url = \''.$pageURL.'\';
                    this.page.identifier = \''.$pageIdentifier.'\';
                    this.page.title = \''.addslashes($pageTitle).'\';
                };
                (function() {
                    var d = document, s = d.createElement(\'script\');
                    s.src = \'//'.$_CONF['comment_disqus_shortname'].'.disqus.com/embed.js\';
                    s.setAttribute(\'data-timestamp\', +new Date());
                    (d.head || d.body).appendChild(s);
                })();
            </script>
            <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript>
            ';
            break;

        case 'facebook' :
            if( $type == 'article' ) {
                $pageURL = COM_buildUrl( $_CONF['site_url']."/article.php?story=$sid" );
                $pageIdentifier = 'article_'.$sid;
            } else { // for a plugin
                // Link to plugin defined link or lacking that a generic link that the plugin should support (hopefully)
                list($pageURL, $plgid) = PLG_getCommentUrlId($type);
                $pageIdentifier = $type.'_'.$sid;
                $pageURL = PLG_getItemInfo($type, $sid, 'url');
            }
            $pageTitle = urlencode($title);
            $pageURL = str_replace ( '&amp;', '&', $pageURL );

            $retval = '<a name="comment_entry"></a><div class="fb-comments" data-href="'.$pageURL.'" data-numposts="20"></div>';
            break;

        case 'internal' :
        default :
            $valid_modes = array('threaded','nested','flat','nocomment');
            if ( in_array($mode,$valid_modes) === false ) {
                $mode = 'nested';
            }

            if ($mode == 'threaded') $mode = 'nested';

            if (! COM_isAnonUser()) {
                $sql = "SELECT commentorder,commentmode,commentlimit
                    FROM `{$_TABLES['usercomment']}` WHERE uid = ?";
                $U = $db->conn->fetchAssoc($sql,array($_USER['uid']),array(Database::INTEGER));
                if( empty( $order ) ) {
                    $order = $U['commentorder'];
                }
                if( empty( $mode ) ) {
                    $mode = $U['commentmode'];
                }
                $limit = $U['commentlimit'];
            }

            if( $order != 'ASC' && $order != 'DESC' ) {
                $order = 'ASC';
            }

            $validmodes = array('flat','nested','nocomment','nobar');
            if ( !in_array($mode,$validmodes) ) {
                $mode = $_CONF['comment_mode'];
            }

            if( empty( $mode )) {
                $mode = $_CONF['comment_mode'];
            }

            if( empty( $limit )) {
                $limit = (int) $_CONF['comment_limit'];
            } else {
                $limit = (int) $limit;
            }

            if( !is_numeric($page) || $page < 1 ) {
                $page = 1;
            } else {
                $page = (int) $page;
            }

            $start = (int) $limit * ( $page - 1 );

            $template = new Template( $_CONF['path_layout'] . 'comment' );
            $template->set_file( array( 'commentarea' => 'startcomment.thtml' ));
            if ( $mode != 'nobar' ) {
                $template->set_var( 'commentbar',
                        CMT_commentBar( $sid, $title, $type, $order, $mode, $ccode ));
            }
            $template->set_var( 'sid', $sid );
            $template->set_var( 'comment_type', $type );

            if( $mode == 'nested' || $mode == 'threaded' || $mode == 'flat' ) {
                // build query
                switch( $mode ) {
                    case 'flat':
                        if( $cid ) {
                            $count = 1;

                            $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, "
                                . "UNIX_TIMESTAMP(c.date) AS nice_date "
                                . "FROM `{$_TABLES['comments']}` AS c, `{$_TABLES['users']}` AS u "
                                . "WHERE c.queued = 0 AND c.uid = u.uid AND c.cid = ? AND type=?";
                            // (int) $pid and (string) $type
                            $stmt = $db->conn->prepare($q);
                            $stmt->bindValue(1, $pid, Database::INTEGER);
                            $stmt->bindValue(2, $type, Database::STRING);

                        } else {
                            $q2 = "SELECT COUNT(*) FROM `{$_TABLES['comments']}` WHERE sid=? AND type=? AND queued=0";
                            $count = $db->conn->fetchColumn($q2,array($sid,$type),
                                        0,
                                        array(Database::STRING,Database::STRING));

                            $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, "
                               . "UNIX_TIMESTAMP(c.date) AS nice_date "
                               . "FROM `{$_TABLES['comments']}` AS c, `{$_TABLES['users']}` AS u "
                               . "WHERE c.queued = 0 AND c.uid = u.uid AND c.sid = ? AND type=? "
                               . "ORDER BY date ". $order ." LIMIT ".(int) $start.", ". (int) $limit;
                            // $sid , $type, $order, $start, $limit
                            $stmt = $db->conn->prepare($q);
                            $stmt->bindValue(1, $sid, Database::STRING);
                            $stmt->bindValue(2, $type, Database::STRING);
                        }
                        break;

                    case 'nested':
                    case 'threaded':
                    default:
                        if( $order == 'DESC' ) {
                            $cOrder = 'c.rht DESC';
                        } else {
                            $cOrder = 'c.lft ASC';
                        }

                        // We can simplify the query, and hence increase performance
                        // when pid = 0 (when fetching all the comments for a given sid)
                        if( $cid ) {  // pid refers to commentid rather than parentid
                            // count the total number of applicable comments
                            $q2 = "SELECT COUNT(*) "
                                . "FROM `{$_TABLES['comments']}` AS c, `{$_TABLES['comments']}` AS c2 "
                                . "WHERE c.queued = 0 AND c.sid = ? AND (c.lft >= c2.lft AND c.lft <= c2.rht) "
                                . "AND c2.cid = ? AND c.type=?";

                            $count = $db->conn->fetchColumn($q2,array($sid,$pid,$type),0,array(Database::STRING,Database::INTEGER,Database::STRING));

                            $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, c2.indent AS pindent, "
                               . "UNIX_TIMESTAMP(c.date) AS nice_date "
                               . "FROM `{$_TABLES['comments']}` AS c, `{$_TABLES['comments']}` AS c2, "
                               . "{$_TABLES['users']} AS u "
                               . "WHERE c.queued = 0 AND c.sid = ? AND (c.lft >= c2.lft AND c.lft <= c2.rht) "
                               . "AND c2.cid = ? AND c.uid = u.uid AND c.type=? "
                               . "ORDER BY ".$cOrder." LIMIT ".(int)$start.", ".(int)$limit;

                            $stmt = $db->conn->prepare($q);
                            $stmt->bindValue(1, $sid, Database::STRING);
                            $stmt->bindValue(2, $pid, Database::INTEGER);
                            $stmt->bindValue(3, $type, Database::STRING);

                        } else {    // pid refers to parentid rather than commentid
                            if( $pid == 0 ) {  // the simple, fast case
                                // count the total number of applicable comments

                                $q2 = "SELECT COUNT(*) FROM `{$_TABLES['comments']}` WHERE sid=? AND type=? AND queued=0";
                                $count = $db->conn->fetchColumn($q2,array($sid,$type),0,array(Database::STRING,Database::STRING));

                                $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, 0 AS pindent, "
                                   . "UNIX_TIMESTAMP(c.date) AS nice_date "
                                   . "FROM `{$_TABLES['comments']}` AS c, `{$_TABLES['users']}` AS u "
                                   . "WHERE queued = 0 AND c.sid = ? AND c.uid = u.uid  AND type=? "
                                   . "ORDER BY ".$cOrder." LIMIT ".(int)$start.", ".(int)$limit;
                                $stmt = $db->conn->prepare($q);
                                $stmt->bindValue(1, $sid, Database::STRING);
                                $stmt->bindValue(2, $type, Database::STRING);
                            } else {
                                // count the total number of applicable comments
                                $q2 = "SELECT COUNT(*) "
                                    . "FROM `{$_TABLES['comments']}` AS c, `{$_TABLES['comments']}` AS c2 "
                                    . "WHERE c.queued = 0 AND c.sid = ? AND (c.lft > c2.lft AND c.lft < c2.rht) "
                                    . "AND c2.cid = ? AND c.type=?";
                                $count = $db->conn->fetchColumn($q2,array($sid,$pid,$type),0,array(Database::STRING,Database::INTEGER,Database::STRING));

                                $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, c2.indent + 1 AS pindent, "
                                   . "UNIX_TIMESTAMP(c.date) AS nice_date "
                                   . "FROM `{$_TABLES['comments']}` AS c, `{$_TABLES['comments']}` AS c2, "
                                   . "{$_TABLES['users']} AS u "
                                   . "WHERE c.queued = 0 AND c.sid = ? AND (c.lft > c2.lft AND c.lft < c2.rht) "
                                   . "AND c2.cid = ? AND c.uid = u.uid AND c.type=? "
                                   . "ORDER BY ".$cOrder." LIMIT ".(int)$start.", ".(int)$limit;
                                $stmt = $db->conn->prepare($q);
                                $stmt->bindValue(1, $sid, Database::STRING);
                                $stmt->bindValue(2, $pid, Database::INTEGER);
                                $stmt->bindValue(3, $type, Database::STRING);
                            }
                        }
                        break;
                }

                $thecomments = '';
                $stmt->execute();

                $thecomments .= CMT_getComment( $stmt, $mode, $type, $order,
                                                $delete_option, false, $ccode, $sid_author_id );

                if ( $thecomments == '' ) {
                    if ( $ccode == 0 ) {
                        $template->set_var( 'lang_be_the_first',$LANG03[51]);
                    }
                }

                // Pagination
                $tot_pages =  ceil( $count / $limit );

                if( $type == 'article' ) {
                    $pLink = $_CONF['site_url'] . "/article.php?story=$sid&amp;type=$type&amp;order=$order&amp;mode=$mode";
                    $pageStr = 'page=';
                } else { // plugin
                    // Link to plugin defined link or lacking that a generic link that the plugin should support (hopefully)
                    list($plgurl, $plgid,$plg_page_str) = PLG_getCommentUrlId($type);
                    $pLink = $plgurl.'?'.$plgid.'='.$sid."&amp;type=$type&amp;order=$order&amp;mode=$mode";
                    if ( $plg_page_str != '' ) {
                        $pageStr = $plg_page_str;
                    } else {
                        $pageStr = 'page=';
                    }
                }
                $template->set_var( 'pagenav',
                                 COM_printPageNavigation($pLink, $page, $tot_pages,$pageStr,false,'','','#comments'));

                $template->set_var( 'comments', $thecomments );

            }
            $retval = $template->finish($template->parse('output', 'commentarea'));
            break;
    }
    return $retval;
}

/**
* Returns number of comments for a specific item
*
* @param    string  $type       Type of object comment is posted to
* @param    string  $sid        ID of object comment belongs to
* @param    int     $queued     Queued or published
* @return   int     Number of comments
*
*/
function CMT_getCount($type, $sid, $queued = 0)
{
    return glFusion\Comments\CommentEngine::getEngine()->getCount($type, $sid, $queued);
    global $_TABLES;

    $db = Database::getInstance();

    if ( $type == '' || $sid == '' ) return 0;

    return $db->conn->fetchColumn(
            "SELECT COUNT(*) FROM `{$_TABLES['comments']}` WHERE sid=? AND type=? AND queued=?",
            array($sid, $type, $queued),
            0,
            array(Database::STRING, Database::STRING, Database::INTEGER)
    );
}

/**
* Displays the comment form
*
* @param    string  $title      Title of comment
* @param    string  $comment    Text of comment
* @param    string  $sid        ID of object comment belongs to
* @param    int     $pid        ID of parent comment
* @param    string  $type       Type of object comment is posted to
* @param    string  $mode       Mode, e.g. 'preview'
* @param    string  $postmode   Indicates if comment is plain text or HTML
* @return   string  HTML for comment form
*
*/
function CMT_commentForm($title,$comment,$sid,$pid='0',$type = '',$mode = '',$postmode = '')
{
    global $_CONF, $_TABLES, $_USER, $LANG03, $LANG12, $LANG_LOGIN, $LANG_ACCESS, $LANG_ADMIN;

    $vals = array(
        'title' => $title,
        'comment' => $comment,
        'sid' => $sid,
        'pid' => $pid,
        'type' => $type,
        'mode' => $mode,
        'postmode' => $postmode,
    );
    $Comment = glFusion\Comments\Internal\Comment::fromArray($vals);
    $CF = new glFusion\Comments\Internal\CommentForm;
    return $CF->withComment($Comment)->render();

    $retval         = '';
    $cid            = 0;
    $edit_comment   = '';
    $moderatorEdit  = false;
    $adminEdit      = false;

    // bail if anonymous user and we require login
    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) || ($_CONF['commentsloginrequired'] == 1))) {
        $retval .= SEC_loginRequiredForm();
        return $retval;
    }

    $db = Database::getInstance();

    switch ( $mode ) {
        case 'modedit' :
            if ( SEC_hasRights('comment.moderate')) {
                $moderatorEdit = true;
                $_CONF['skip_preview'] = 1;
            }
            $mode = 'edit';
            break;
        case 'preview_edit_mod' :
            if ( SEC_hasRights('comment.moderate')) {
                $moderatorEdit = true;
                $_CONF['skip_preview'] = 1;
            }
            $mode = 'preview_edit';
            break;
        case 'adminedit' :
            if ( SEC_hasRights('comment.moderate')) {
                $adminEdit = true;
                $_CONF['skip_preview'] = 1;
            }
            $mode = 'edit';
            break;
        case 'preview_edit_admin' :
            if ( SEC_hasRights('comment.moderate')) {
                $adminEdit = true;
                $_CONF['skip_preview'] = 1;
            }
            $mode = 'preview_edit';
            break;
        default :
            break;
    }

    if ( empty($postmode) ) {
        $postmode = $_CONF['comment_postmode'];
    }

    $filter = sanitizer::getInstance();
    $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
    $filter->setAllowedelements($AllowedElements);
    $filter->setNamespace('glfusion','comment');
    $filter->setCensorData(true);
    $filter->setPostmode($postmode);

    if (COM_isAnonUser()) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $commentuid = $uid;
    if ( ($mode == 'edit' || $mode == 'preview_edit') && isset($_REQUEST['cid']) ) {
        $cid = (int) COM_applyFilter($_REQUEST['cid'],true);

        $commentuid = $db->conn->fetchColumn(
                        "SELECT uid FROM `{$_TABLES['comments']}` WHERE queued=0 AND cid=?",
                        array($cid),
                        0,
                        array(Database::INTEGER)
                      );
    }

    COM_clearSpeedlimit ($_CONF['commentspeedlimit'], 'comment');

    $last = 0;
    if ($mode != 'edit' && $mode != 'preview' && $mode != 'preview_new' && $mode != 'preview_edit') {
        //not edit mode or preview changes
        $last = COM_checkSpeedlimit ('comment');
    }
    if ($last > 0) {
        $retval .= COM_showMessageText($LANG03[7].$last.sprintf($LANG03[8],$_CONF['commentspeedlimit']),$LANG12[26],false,'error');
        return $retval;
    }

    // Preview mode:
    if (($mode == $LANG03[14] || $mode == 'preview' || $mode == 'preview_new' || $mode == 'preview_edit') && !empty($comment) ) {
        $start = new Template( $_CONF['path_layout'] . 'comment' );
        $start->set_file( array( 'comment' => 'startcomment.thtml' ));
        $start->set_var( 'hide_if_preview', 'style="display:none"' );

        // Clean up all the vars
        $A = array();
        foreach ($_POST as $key => $value) {
            if (($key == 'pid') || ($key == 'cid')) {
                $A[$key] = (int) COM_applyFilter ($_POST[$key], true);
            } else if (($key == 'title') || ($key == 'comment') || ($key == 'comment_text')) {
                $A[$key] = $_POST[$key];
            } else if ($key == 'username') {
//@@ we probably don't need to do this here
//   since we really want the raw data
//   we can do the filtering on display or edit below
                $A[$key] = @htmlspecialchars(COM_checkWords(strip_tags($_POST[$key])),ENT_QUOTES,COM_getEncodingt());
                $A[$key] = USER_uniqueUsername($A[$key]);
            } else {
                $A[$key] = COM_applyFilter($_POST[$key]);
            }
        }

        //correct time and username for edit preview
        if ($mode == 'preview' || $mode == 'preview_new' || $mode == 'preview_edit') {

            $A['nice_date'] = $db->conn->fetchColumn(
                                "SELECT UNIX_TIMESTAMP(date) FROM `{$_TABLES['comments']}` WHERE cid=?",
                                array($cid),
                                0,
                                array(Database::INTEGER)
                              );

            // not an anonymous user
            if ( $commentuid > 1 ) {
                // get username from DB - we don't allow
                // logged-in-users to set or change their name
                $A['username'] = $db->conn->fetchColumn(
                                        "SELECT username FROM `{$_TABLES['users']}` WHERE uid=?",
                                        array($commentuid),
                                        0,
                                        array(Database::INTEGER)
                                  );
            } else {
                // we have an anonymous user - so $_POST['username'] should be set
                // already from above
            }

        }
        if (empty ($A['username'])) {
            $A['username'] = $db->conn->fetchColumn(
                                    "SELECT username FROM `{$_TABLES['users']}` WHERE uid=?",
                                    array($commentuid),
                                    0,
                                    array(Database::INTEGER)
                              );
        }

        $author_id = PLG_getItemInfo($type, $sid, 'author');

        $A['comment'] = $A['comment_text'];

// In preview mode - the first parameter is an associative array
        $thecomments = CMT_getComment ($A, 'flat', $type, 'ASC', false, true,0,$author_id);

        $start->set_var( 'comments', $thecomments );
        $retval .= '<a name="comment_entry"></a>';
        $retval .= COM_startBlock ($LANG03[14])
                . $start->finish( $start->parse( 'output', 'comment' ))
                . COM_endBlock ();
    } else if ($mode == 'preview_new' || $mode == 'preview_edit') {
        $retval .= COM_showMessageText($LANG03[12],$LANG03[17],true,'error');
        $mode = 'error';
    }

    $comment_template = new Template($_CONF['path_layout'] . 'comment');
    $comment_template->set_file('form','commentform.thtml');

    if ($mode == 'preview_new' ) {
        $comment_template->set_var('mode','new');
        $comment_template->set_var('show_anchor','');
    } else if ($mode == 'preview_edit' ) {
        $comment_template->set_var('mode','edit');
        $comment_template->set_var('show_anchor','');
    } else {
        $comment_template->set_var('mode',$mode);
        $comment_template->set_var('show_anchor',1);
    }
    $comment_template->set_var('start_block_postacomment', COM_startBlock($LANG03[1]));
    if ($_CONF['show_fullname'] == 1) {
        $comment_template->set_var('lang_username', $LANG_ACCESS['name']);
    } else {
        $comment_template->set_var('lang_username', $LANG03[5]);
    }
    $comment_template->set_var('sid', $sid);
    $comment_template->set_var('pid', $pid);
    $comment_template->set_var('type', $type);

    if ($mode == 'edit' || $mode == 'preview_edit') { //edit modes
    	$comment_template->set_var('start_block_postacomment', COM_startBlock($LANG03[41]));
    	$comment_template->set_var('cid', '<input type="hidden" name="cid" value="' . @htmlspecialchars(COM_applyFilter($_REQUEST['cid']),ENT_COMPAT,COM_getEncodingt()) . '"/>');
    } else {
        $comment_template->set_var('start_block_postacomment', COM_startBlock($LANG03[1]));
    	$comment_template->set_var('cid', '');
    }
  	$comment_template->set_var('CSRF_TOKEN', SEC_createToken());
  	$comment_template->set_var('token_name', CSRF_TOKEN);

    if (! COM_isAnonUser()) {
        if ( $moderatorEdit == true || $adminEdit == true ) {
            // we know we are editing
            $comment_template->set_var('uid',$commentuid);

            if  ( isset($A['username'])) {
                $username = $A['username'];
            } else {
                $username = $db->conn->fetchColumn(
                                        "SELECT name FROM `{$_TABLES['comments']}` WHERE cid=?",
                                        array($cid),
                                        0,
                                        array(Database::INTEGER)
                                  );
            }
            if ( empty($username)) {
                $username = $LANG03[24]; //anonymous user
            }
            $comment_template->set_var('username',$filter->editableText($username));
            if ( $commentuid > 1 ) {
                $comment_template->set_var('username_disabled','disabled="disabled"');
            } else {
                $comment_template->unset_var('username_disabled');
            }
        } else {
            $comment_template->set_var('uid', $_USER['uid']);
            $name = COM_getDisplayName($_USER['uid'], $_USER['username'],$_USER['fullname']);
            $comment_template->set_var('username', $filter->editableText($name));
            $comment_template->set_var('action_url',$_CONF['site_url'] . '/users.php?mode=logout');
            $comment_template->set_var('lang_logoutorcreateaccount',$LANG03[03]);
            $comment_template->set_var('username_disabled','disabled="disabled"');
        }

        if ( !$moderatorEdit && !$adminEdit) {
            $comment_template->set_var('suballowed',true);
            $isSub = 0;
            if ( $mode == 'preview_edit' || $mode == 'preview_new' ) {
                $isSub = isset($_POST['subscribe']) ? 1 : 0;
            } else if ( PLG_isSubscribed('comment',$type,$sid) ) {
                $isSub = 1;
            }
            if ( $isSub == 0 ) {
                $subchecked = '';
            } else {
                $subchecked = 'checked="checked"';
            }
            $comment_template->set_var('subchecked',$subchecked);
        }
    } else {
        //Anonymous user
        $comment_template->set_var('uid', 1);

        if ( isset($A['username'])) {
            $name = USER_uniqueUsername($A['username']);
        } else {
            $name = $LANG03[24]; //anonymous user
        }
        $comment_template->set_var('username', $filter->editableText($name));

        $comment_template->set_var('action_url', $_CONF['site_url'] . '/users.php?mode=new');
        $comment_template->set_var('lang_logoutorcreateaccount',$LANG03[04]);
        $comment_template->unset_var('username_disabled');
    }

    if ( $postmode == 'html' ) {
        $comment_template->set_var('htmlmode',true);
    }
    $comment_template->set_var('lang_title', $LANG03[16]);
    $comment_template->set_var('title', $filter->editableText($title));
    $comment_template->set_var('lang_timeout',$LANG_ADMIN['timeout_msg']);
    $comment_template->set_var('lang_comment', $LANG03[9]);

    $comment_template->set_var('comment', $filter->editableText($comment));
    $comment_template->set_var('lang_postmode', $LANG03[2]);
    $comment_template->set_var('postmode',$postmode);
    $comment_template->set_var('postmode_options', COM_optionList($_TABLES['postmodes'],'code,name',$postmode));
    if ( $postmode == 'html' ) {
        $comment_template->set_var('allowed_html', $filter->getAllowedHTML() . '<br/>'. COM_AllowedAutotags('', false, 'glfusion','comment'));
    } else {
        $comment_template->set_var('allowed_html', COM_AllowedAutotags('', false, 'glfusion','comment'));
    }
    $comment_template->set_var('lang_importantstuff', $LANG03[18]);
    $comment_template->set_var('lang_instr_line1', $LANG03[19]);
    $comment_template->set_var('lang_instr_line2', $LANG03[20]);
    $comment_template->set_var('lang_instr_line3', $LANG03[21]);
    $comment_template->set_var('lang_instr_line4', $LANG03[22]);
    $comment_template->set_var('lang_instr_line5', $LANG03[23]);

    if ($mode == 'edit' || $mode == 'preview_edit') {
        //editing comment or preview changes
        $comment_template->set_var('lang_preview', $LANG03[28]);
    } else {
    	//new comment
        $comment_template->set_var('lang_preview', $LANG03[14]);
    }

    if (function_exists('msg_replaceEmoticons'))  {
        $comment_template->set_var('smilies',msg_showsmilies());
    }

    $comment_template->unset_var('save_type');
// allow plugins the option to set some template vars
    PLG_templateSetVars ('comment', $comment_template);

// set up the save / preview buttons
    if ($mode == 'preview_edit' || ($mode == 'edit' && $_CONF['skip_preview'] == 1) ) {
        //for editing
        $comment_template->set_var('save_type','saveedit');
        $comment_template->set_var('lang_save',$LANG03[29]);
    } elseif (($_CONF['skip_preview'] == 1) || ($mode == 'preview_new')) {
        //new comment
        $comment_template->set_var('save_type','savecomment');
        $comment_template->set_var('lang_save',$LANG03[11]);
    }

// set some fields if mod or admin edit
    if ( $moderatorEdit == true ) {
        $comment_template->set_var('modedit_mode','modedit');
        $comment_template->set_var('modedit','x');
    }
    if ( $adminEdit == true ) {
        $comment_template->set_var('modedit_mode','adminedit');
        $comment_template->set_var('modedit','x');
        $comment_template->set_var('silent_edit',true);
        $comment_template->set_var('lang_silent_edit', $LANG03[57]);
    }

    $comment_template->set_var('end_block', COM_endBlock());
    $comment_template->parse('output', 'form');
    $retval .= $comment_template->finish($comment_template->get_var('output'));

    return $retval;
}

/**
 * Save a comment
 *
 * @author   Vincent Furia, vinny01 AT users DOT sourceforge DOT net
 * @param    string      $title      Title of comment
 * @param    string      $comment    Text of comment
 * @param    string      $sid        ID of object receiving comment
 * @param    int         $pid        ID of parent comment
 * @param    string      $type       Type of comment this is (article, polls, etc)
 * @param    string      $postmode   Indicates if text is HTML or plain text
 * @return   int         0 for success, > 0 indicates error
 *
 */
function CMT_saveComment ($title, $comment, $sid, $pid, $type, $postmode)
{
    global $_CONF, $_TABLES, $_USER, $LANG03;
    if ( COM_isAnonUser() ) {
        if (isset($_POST['username']) ) {
            $uname = @htmlspecialchars(strip_tags(trim(COM_checkWords(USER_sanitizeName($_POST['username'])))),ENT_QUOTES,COM_getEncodingt(),true);
            $uname = USER_uniqueUsername($uname);
        } else {
            $uname = '';
        }
        $email = '';
    } else {
        $uname = $_USER['username'];
        $email = $_USER['email'];
    }

    if (empty($title)) {
        $info = PLG_getItemInfo($type, $sid, 'id,title');
        $title = (isset($info['title']) ? $info['title'] : 'Comment');
    }

    $vars = array(
        'title' => $title,
        'comment' => $comment,
        'sid' => $sid,
        'pid' => $pid,
        'type' => $type,
        'postmode' => $postmode,
        'uid' => $_USER['uid'],
        'name' => $uname,
    );
    $Comment = glFusion\Comments\Internal\Comment::fromArray($vars);
    if (!$Comment->save()) {
        COM_setMsg($Comment->printErrors(), 'error');
        return 1;
    }
    return 0;


    $ret = 0;

    $db = Database::getInstance();

    // Get a valid uid
    if (empty ($_USER['uid'])) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    // Check that anonymous comments are allowed
    if (($uid == 1) && (($_CONF['loginrequired'] == 1) || ($_CONF['commentsloginrequired'] == 1))) {
        Log::write('system',Log::WARNING,'CMT_saveComment: IP address '.$_SERVER['REAL_ADDR'].' '
                   . 'attempted to save a comment with anonymous comments disabled for site.');
        return $ret = 2;
    }

    // Check for people breaking the speed limit
    COM_clearSpeedlimit ($_CONF['commentspeedlimit'], 'comment');
    $last = COM_checkSpeedlimit ('comment');
    if ($last > 0) {
        Log::write('system',Log::WARNING,'CMT_saveComment: '.$uid.' from IP address '.$_SERVER['REAL_ADDR'].' '
        . 'attempted to submit a comment before the speed limit expired.');
        return $ret = 3;
    }

    $uname = '';
    $email = '';

    if ( COM_isAnonUser() ) {
        if (isset($_POST['username']) ) {
            $uname = @htmlspecialchars(strip_tags(trim(COM_checkWords(USER_sanitizeName($_POST['username'])))),ENT_QUOTES,COM_getEncodingt(),true);
            $uname = USER_uniqueUsername($uname);
        } else {
            $uname = '';
        }
        $email = '';
    } else {
        $uname = $_USER['username'];
        $email = $_USER['email'];
    }

    $info = PLG_getItemInfo($type, $sid, 'id,title');
    $title = (isset($info['title']) ? $info['title'] : 'Comment');

    // Error Checking
    if (empty ($sid) || empty ($comment) || empty ($type) ) {
        Log::write('system',Log::WARNING,'CMT_saveComment: '.$uid.' from '.$_SERVER['REAL_ADDR'].' tried to submit a comment with one or more missing values.');
        if ( SESS_isSet('glfusion.commentpresave.error') ) {
            $msg = SESS_getVar('glfusion.commentpresave.error') . '<br/>' . $LANG03[12];
        } else {
            $msg = $LANG03[12];
        }
        SESS_setVar('glfusion.commentpresave.error',$msg);

        return $ret = 1;
    }

    if (!is_numeric($pid) || ($pid < 0)) {
        $pid = 0;
    }

    // Call Spam based plugins
    $spamcheck = '<h1>' . $title . '</h1><p>' . $comment . '</p>';
    $spamData = array(
        'username' => $uname,
        'email'    => $email,
        'ip'       => $_SERVER['REAL_ADDR'],
        'type'     => 'comment'
    );
    $result = PLG_checkforSpam ($spamcheck, $_CONF['spamx'],$spamData);
    // Now check the result and display message if spam action was taken
    if ($result > 0) {
        // update speed limit nonetheless
        COM_updateSpeedlimit ('comment');
        // then tell them to get lost ...
        COM_displayMessageAndAbort ($result, 'spamx', 403, 'Forbidden');
    }
    COM_updateSpeedlimit ('comment');

    // Let plugins have a chance to decide what to do before saving the comment, return errors.
    if ($someError = PLG_commentPreSave($uid, $title, $comment, $sid, $pid, $type, $postmode)) {
        return $someError;
    }

    // determine if we are queuing
    $queued = 0;
    if ( isset($_CONF['commentssubmission'] ) && $_CONF['commentssubmission'] > 0 ) {
        switch ( $_CONF['commentssubmission'] ) {
            case 1 : // anonymous only
                if ( COM_isAnonUser() ) {
                    $queued = 1;
                }
                break;
            case 2 : // all users
            default :
                $queued = 1;
                break;
        }
        if ( SEC_hasRights('comment.submit') ) {
            $queued = 0;
        }
    }

    $IP = $_SERVER['REMOTE_ADDR'];

    $cid = 0;

    if ($pid > 0) { // reply to an existing comment
        $row = $db->conn->fetchAssoc(
            "SELECT rht, indent FROM `{$_TABLES['comments']}` WHERE cid = ? AND sid = ?",
            array($pid, $sid),
            array(Database::INTEGER, Database::STRING)
        );
        if ($row === false) {
            Log::write('system',Log::WARNING,'CMT_saveComment: '.$uid.' from '.$_SERVER['REAL_ADDR'].' tried '
                       . 'to reply to a non-existent comment or the pid/sid did not match');
            return 4;
        }

        $rht    = $row['rht'];
        $indent = $row['indent'];

        $db->conn->query("LOCK TABLES `{$_TABLES['comments']}` WRITE");
        $db->conn->beginTransaction();
        try {
            $db->conn->executeUpdate(
                    "UPDATE `{$_TABLES['comments']}` SET lft = lft + 2 "
                  . "WHERE sid = ? AND type = ? AND lft >= ?",
                  array($sid,$type,$rht),
                  array(Database::STRING, Database::STRING, Database::INTEGER)
            );
            $db->conn->executeUpdate(
                    "UPDATE `{$_TABLES['comments']}` SET rht = rht + 2 "
                  . "WHERE sid = ? AND type = ? AND rht >= ?",
                  array($sid,$type,$rht),
                  array(Database::STRING, Database::STRING, Database::INTEGER)
            );
            $db->conn->executeUpdate(
                "INSERT INTO `{$_TABLES['comments']}` (sid,uid,name,comment,date,title,pid,queued,postmode,lft,rht,indent,type,ipaddress) "
              . " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                array($sid,$uid,$uname,$comment,$_CONF['_now']->toMySQL(true),$title,$pid,$queued,$postmode,$rht,$rht+1,$indent+1,$type,$IP),
                array(
                    Database::STRING,
                    Database::INTEGER,
                    Database::STRING,
                    Database::STRING,
                    Database::STRING, // date
                    Database::STRING, // title
                    Database::INTEGER, // pid
                    Database::INTEGER, // queued
                    Database::STRING, // postmode
                    Database::INTEGER, //rht
                    Database::INTEGER, // rht+1
                    Database::INTEGER, // indent
                    Database::STRING // IP
                )
            );
            $cid = $db->conn->lastInsertId();
            $db->conn->commit();
            $db->conn->query("UNLOCK TABLES");
        } catch (\Doctrine\DBAL\Exception\RetryableException $e) {
            $db->conn->commit();
            $db->conn->query("UNLOCK TABLES `{$_TABLES['comments']}` WRITE");
            usleep(250000);
        } catch (\Exception $e) {
            $db->conn->rollBack();
            $db->conn->query("UNLOCK TABLES");
            throw($e);
        }
    } else {  // first - parent level comment

        $db->conn->query("LOCK TABLES `{$_TABLES['comments']}` WRITE");
        $db->conn->beginTransaction();
        try {
            $rht = $db->conn->fetchColumn(
                "SELECT MAX(rht) FROM `{$_TABLES['comments']}` WHERE sid=?",
                array($sid),
                0
            );
            if ($rht === null) {
                $rht = 0;
            }
        } catch(Throwable $e) {
            $rht = 0;
        }

        $indent = 0;

        try {
            $db->conn->executeQuery(
                "INSERT INTO `{$_TABLES['comments']}` (sid,uid,name,comment,date,title,pid,queued,postmode,lft,rht,indent,type,ipaddress) "
              . " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                array($sid,$uid,$uname,$comment,$_CONF['_now']->toMySQL(true),$title,$pid,$queued,$postmode,$rht+1,$rht+2,0,$type,$IP),
                array(
                    Database::STRING,
                    Database::INTEGER,
                    Database::STRING,
                    Database::STRING,
                    Database::STRING, // date
                    Database::STRING, // title
                    Database::INTEGER, // pid
                    Database::INTEGER, // queued
                    Database::STRING, // postmode
                    Database::INTEGER, //rht
                    Database::INTEGER, // rht+1
                    Database::INTEGER, // indent
                    Database::STRING // IP
                )
            );

            $cid = $db->conn->lastInsertId();
            $db->conn->commit();
            $db->conn->query("UNLOCK TABLES");
        } catch (\Doctrine\DBAL\Exception\RetryableException $e) {
            $db->conn->commit();
            $db->conn->query("UNLOCK TABLES");
            usleep(250000);
        } catch (\Exception $e) {
            $db->conn->rollBack();
            $db->conn->query("UNLOCK TABLES");
            throw($e);
        }
    }
    if ( $queued == 0 ) {
        $c = Cache::getInstance();
        $c->deleteItemsByTag('whatsnew');
        if ($type == 'article') {
            $c->deleteItemsByTag('story_'.$sid);
        }
        PLG_itemSaved($cid, 'comment');
    } else {
        $c = Cache::getInstance()->deleteItemsByTag('menu');
        SESS_setVar('glfusion.commentpostsave',$LANG03[52]);
    }

    // check to see if user has subscribed....

    if ( !COM_isAnonUser() ) {
        if ( isset($_POST['subscribe']) && $_POST['subscribe'] == 1 ) {
            $itemInfo = PLG_getItemInfo($type,$sid,('url,title'));
            if ( isset($itemInfo['title']) ) {
                $id_desc = $itemInfo['title'];
            } else {
                $id_desc = 'not defined';
            }
            $rc = PLG_subscribe('comment',$type,$sid,$uid,$type,$id_desc);
        } else {
            PLG_unsubscribe('comment',$type,$sid);
        }
    }
    if (($ret == 0) && isset ($_CONF['notification']) && in_array ('comment', $_CONF['notification'])) {
        $commentData = array(
            'cid'      => $cid,
            'uid'      => $uid,
            'username' => $uname,
            'ip'       => $IP,
            'title'    => $title,
            'comment'  => $comment,
            'type'     => $type,
            'postmode' => $postmode,
            'queued'   => $queued
        );

        CMT_sendNotification($commentData);
    }
    if ( $ret == 0 && $queued == 0) {
        // handles sending out subscription emails
        PLG_sendSubscriptionNotification('comment',$type,$sid,$cid,$uid);
    }

    return $ret;
}

/**
* Send an email notification to mod / admin for a new comment submission.
*
* Note - this function receives raw / unfiltered / unsanitized data
*
* @param    $commentData array      All comment information posted
*
*/
function CMT_sendNotification( $commentData = array())
{
    echo __FUNCTION__ . ' deprecated';die;

    global $_CONF, $_TABLES, $LANG03, $LANG08, $LANG09;

    $filter = sanitizer::getInstance();

    $db = Database::getInstance();

    $author = $filter->sanitizeUsername($commentData['username']) . ' ('.$commentData['ip'].')';
    $type   = $commentData['type'];
    $html2txt  = new Html2Text\Html2Text(strip_tags($commentData['title']),false);
    $title = trim($html2txt->get_text());

    // build out the view

    $format = new Formatter();
    $format->setNamespace('glfusion');
    $format->setAction('comment');
    $format->setAllowedHTML($_CONF['htmlfilter_comment']);
    $format->setParseAutoTags(true);
    $format->setProcessBBCode(false);
    $format->setCensor(true);
    $format->setProcessSmilies(false);
    $format->setType($commentData['postmode']);

    $comment = $format->parse($commentData['comment']);

    if ($_CONF['emailstorieslength'] > 1) {
        $comment = COM_truncateHTML ( $comment, $_CONF['emailstorieslength']);
    }
    // we have 2 different types of email - one for queued and one for alerting
    if ( $commentData['queued'] == 0 ) {
        $mailbody = "$LANG03[16]: " . $title ."<br>"
                  . "$LANG03[5]: "  . $author."<br>";
        if (($type != 'article') && ($type != 'poll')) {
            $mailbody .= "$LANG09[5]: $type<br>";
        }
        $mailbody .= '<br>'. $comment . '<br>';
        $mailbody .= $LANG08[33] . ' ' . $_CONF['site_url']
                  . '/comment.php?mode=view&cid=' . $commentData['cid'] . "<br><br>";
        $mailbody .= "------------------------------<br>";
        $mailbody .= "$LANG08[34]";
        $mailbody .= "<br>------------------------------<br>";

        $mailsubject = $_CONF['site_name'] . ' ' . $LANG03[9];

    } else {
        $mailbody  = $LANG03[53].'<br><br>';
        $mailbody .= $LANG03[16].': '. $title .'<br>';
        $mailbody .= $LANG03[5].': ' . $author.'<br><br>';
        $mailbody .= $comment . '<br><br>';
        $mailbody .= sprintf($LANG03[54].'<br>',$_CONF['site_admin_url'].'/moderation.php');

        $mailsubject = $LANG03[55];
    }

    // now we have the HTML mail message built
    // build text version
    $html2txt  = new Html2Text\Html2Text($mailbody,false);
    $mailbody_text = trim($html2txt->get_text());

    $to = array();
    $msgData = array();
    $toCount = 0;

    if ( $commentData['queued'] == 0 ) {
        $to[] = array('email' => $_CONF['site_mail'], 'name' => '');
        $toCount++;
    } else {

        $commentadmin_grp_id = $db->conn->fetchColumn("SELECT grp_id FROM `{$_TABLES['groups']}` WHERE grp_name='Comment Admin'",array(),0);
        if ( $commentadmin_grp_id === null ) {
            return;
        }
        $groups = SEC_getGroupList($commentadmin_grp_id);

	    $sql = "SELECT DISTINCT {$_TABLES['users']}.uid,username,fullname,email "
	          ."FROM `{$_TABLES['group_assignments']}`,`{$_TABLES['users']}` "
	          ."WHERE {$_TABLES['users']}.uid > 1 "
	          ."AND {$_TABLES['users']}.uid = {$_TABLES['group_assignments']}.ug_uid "
	          ."AND ({$_TABLES['group_assignments']}.ug_main_grp_id IN (?))";

        $stmt = $db->conn->executeQuery($sql,array($groups),array(Database::PARAM_INT_ARRAY));
        $resultSet = $stmt->fetchAll(Database::ASSOCIATIVE);

        foreach($resultSet AS $row) {
            if ( $row['email'] != '' ) {
                $toCount++;
                $to[] = array('email' => $row['email'], 'name' => $row['username']);
            }
        }
    }
    if ( $toCount > 0 ) {
        $msgData['htmlmessage'] = $mailbody;
        $msgData['textmessage'] = $mailbody_text;
        $msgData['subject']     = $mailsubject;
        $msgData['from']['email'] = $_CONF['noreply_mail'];
        $msgData['from']['name'] = $_CONF['site_name'];
        $msgData['to'] = $to;
        COM_emailNotification( $msgData );
    }
}

/**
 * Deletes a given comment
 *
 * The function expects the calling function to check to make sure the
 * requesting user has the correct permissions and that the comment exits
 * for the specified $type and $sid.
 *
 * @author  Vincent Furia, vinny01 AT users DOT sourceforge DOT net
 * @param   string      $type   article, poll, or plugin identifier
 * @param   string      $sid    id of object comment belongs to
 * @param   int         $cid    Comment ID
 * @return  string      0 indicates success, >0 identifies problem
 */
function CMT_deleteComment ($cid, $sid, $type)
{
    $Cmt = glFusion\Comments\Internal\Comment::getByCid($cid);
    if ($Cmt->getSid() != $sid || $Cmt->getType() != $type) {
        return 1;
    }
    $Cmt->delete();
    return 0;

    global $_CONF, $_TABLES, $_USER;

    $ret = 0;  // Assume good status unless reported otherwise

    // Sanity check, note we return immediately here and no DB operations
    // are performed
    if (!is_numeric ($cid) || ($cid < 0) || empty ($sid) || empty ($type)) {
        Log::write('system',Log::WARNING,'CMT_deleteComment: '.$_USER['uid'].' from '.$_SERVER['REAL_ADDR'].' tried '
                   . 'to delete a comment with one or more missing/bad values.');
        return 1;
    }

    $db = Database::getInstance();

    // Delete the comment from the DB and update the other comments to
    // maintain the tree structure
    // A lock is needed here to prevent other additions and/or deletions
    // from happening at the same time. A transaction would work better,
    // but aren't supported with MyISAM tables.

    $db->conn->query("LOCK TABLES `{$_TABLES['comments']}` WRITE");
    $db->conn->beginTransaction();
    try {
        $cmtData = $db->conn->fetchAssoc(
            "SELECT pid, lft, rht FROM `{$_TABLES['comments']}` "
          . "WHERE cid = ? AND sid = ? AND type = ?",
          array($cid,$sid,$type),
          array(Database::INTEGER, Database::STRING, Database::STRING)
        );
        if ($cmtData === false) {
            Log::write('system',Log::WARNING,'CMT_deleteComment: '.$_USER['uid'].' from '.$_SERVER['REAL_ADDR'].' tried '
                       . 'to delete a comment that doesn\'t exist as described.');
            return $ret = 2;
        }
        $pid = $cmtData['pid'];
        $rht = $cmtData['rht'];
        $lft = $cmtData['lft'];

        $db->conn->executeUpdate(
                    "UPDATE `{$_TABLES['comments']}` SET pid=? WHERE pid=?",
                    array($pid,$cid),
                    array(Database::INTEGER, Database::INTEGER)
        );
        $db->conn->executeUpdate(
                    "DELETE FROM `{$_TABLES['comments']}` WHERE cid=?",
                    array($cid),
                    array(Database::INTEGER)
        );
        $db->conn->executeUpdate(
                    "UPDATE {$_TABLES['comments']} SET indent = indent - 1 WHERE sid = ? AND type = ? AND lft BETWEEN ? AND ?",
                    array($sid,$type,$lft,$rht),
                    array(Database::STRING, Database::STRING, Database::INTEGER, Database::INTEGER)
        );
        $db->conn->executeUpdate(
                    "UPDATE `{$_TABLES['comments']}` SET lft = lft - 2 WHERE sid = ? AND type = ?  AND lft >= ?",
                    array($sid,$type,$rht),
                    array(Database::STRING, Database::STRING, Database::INTEGER)
        );
        $db->conn->executeUpdate(
                    "UPDATE `{$_TABLES['comments']}` SET rht = rht - 2 WHERE sid = ? AND type = ?  AND rht >= ?",
                    array($sid,$type,$rht),
                    array(Database::STRING,Database::STRING,Database::INTEGER)
        );
        $db->conn->commit();
        $db->conn->query("UNLOCK TABLES");
    } catch (\Doctrine\DBAL\Exception\RetryableException $e) {
        $db->conn->commit();
        $db->conn->query("UNLOCK TABLES");
        usleep(250000);
    } catch(Throwable $e) {
        $db->conn->rollBack();
        $db->conn->query("UNLOCK TABLES");
        throw($e);
    }

    PLG_itemDeleted((int) $cid, 'comment');

    Cache::getInstance()->deleteItemsByTags(array('whatsnew','story_'.$sid));

    return $ret;
}

/**
* Display form to report abusive comment.
*
* @param    string  $cid    comment id
* @param    string  $type   type of comment ('article', 'poll', ...)
* @return   string          HTML for the form (or error message)
*
*/
function CMT_reportAbusiveComment ($cid, $type)
{
    global $_CONF, $_TABLES, $_USER, $LANG03, $LANG12, $LANG_LOGIN;

    $retval = '';

    if ( COM_isAnonUser() ) {
        $retval .= SEC_loginRequiredForm();

        return $retval;
    }

    COM_clearSpeedlimit ($_CONF['speedlimit'], 'mail');
    $last = COM_checkSpeedlimit ('mail');
    if ($last > 0) {
        $retval .= COM_showMessageText($LANG12[30].$last.sprintf($LANG12[31],$_CONF['speedlimit']), $LANG12[26],false,'error');
        return $retval;
    }

    $db = Database::getInstance();

    $start = new Template($_CONF['path_layout'] . 'comment');
    $start->set_file(array('report' => 'reportcomment.thtml'));
    $start->set_var('lang_report_this', $LANG03[25]);
    $start->set_var('lang_send_report', $LANG03[10]);
    $start->set_var('cid', $cid);
    $start->set_var('type', $type);
    $start->set_var('gltoken_name', CSRF_TOKEN);
    $start->set_var('gltoken', SEC_createToken());

    $A = $db->conn->fetchAssoc("SELECT uid,sid,pid,title,comment,UNIX_TIMESTAMP(date) AS nice_date FROM `{$_TABLES['comments']}` WHERE cid = ? AND type = ?",
            array($cid,$type),
            array(Database::INTEGER,Database::STRING)
         );

    if ($A === false) {
        return $retval;
    }

    $B = $db->conn->fetchAssoc("SELECT username,fullname,photo,email FROM `{$_TABLES['users']}` WHERE uid = ?",
            array($A['uid']),
            array(Database::INTEGER)

         );

    // prepare data for comment preview
    $A['cid'] = $cid;
    $A['type'] = $type;
    $A['username'] = $B['username'];
    $A['fullname'] = $B['fullname'];
    $A['photo'] = $B['photo'];
    $A['email'] = $B['email'];
    $A['indent'] = 0;
    $A['pindent'] = 0;

//@TODO - we are calling this in preview mode so the first parameter is an associative array
    $thecomment = CMT_getComment ($A, 'flat', $type, 'ASC', false, true);
    $start->set_var ('comment', $thecomment);
    $retval .= COM_startBlock ($LANG03[15])
            . $start->finish ($start->parse ('output', 'report'))
            . COM_endBlock ();

    return $retval;
}

/**
* Send report about abusive comment
*
* @param    string  $cid    comment id
* @param    string  $type   type of comment ('article', 'poll', ...)
* @return   string          Meta refresh or HTML for error message
*
*/
function CMT_sendReport ($cid, $type)
{
    global $_CONF, $_TABLES, $_USER, $LANG03, $LANG08, $LANG09, $LANG_LOGIN;

    if ( COM_isAnonUser() ) {
        $retval = COM_siteHeader ('menu', $LANG_LOGIN[1]);
        $retval .= SEC_loginRequiredForm();
        $retval .= COM_siteFooter ();

        return $retval;
    }

    COM_clearSpeedlimit ($_CONF['speedlimit'], 'mail');
    if (COM_checkSpeedlimit ('mail') > 0) {
        return COM_refresh ($_CONF['site_url'] . '/index.php');
    }

    $db = Database::getInstance();

    $username = $db->conn->fetchColumn("SELECT username FROM `{$_TABLES['users']}` WHERE uid=?",
                    array($_USER['uid'],0,array(Database::INTEGER)));


    $A = $db->conn->fetchAssoc("SELECT uid,title,comment,sid,ipaddress FROM `{$_TABLES['comments']}`
                              WHERE cid = ? AND type = ?",
                              array($cid,$type),
                              array(Database::INTEGER,Database::STRING)
                     );

    if ($A == false) {
        return;
    }

    $title = $A['title'];
    $comment = $A['comment'];

    // strip HTML if posted in HTML mode
    if (preg_match ('/<.*>/', $comment) != 0) {
        $comment = strip_tags ($comment);
    }

    $author = COM_getDisplayName ($A['uid']);
    if (($A['uid'] <= 1) && !empty ($A['ipaddress'])) {
        // add IP address for anonymous posters
        $author .= ' (' . $A['ipaddress'] . ')';
    }

    $mailbody = sprintf ($LANG03[26], $username);
    $mailbody .= "\n\n"
              . "$LANG03[16]: $title\n"
              . "$LANG03[5]: $author\n";

    if (($type != 'article') && ($type != 'poll')) {
        $mailbody .= "$LANG09[5]: $type\n";
    }

    if ($_CONF['emailstorieslength'] > 0) {
        if ($_CONF['emailstorieslength'] > 1) {
            $comment = MBYTE_substr ($comment, 0, $_CONF['emailstorieslength'])
                     . '...';
        }
        $mailbody .= $comment . "\n\n";
    }

    $mailbody .= $LANG08[33] . ' <' . $_CONF['site_url']
              . '/comment.php?mode=view&cid=' . $cid . ">\n\n";

    $mailbody .= "\n------------------------------\n";
    $mailbody .= "\n$LANG08[34]\n";
    $mailbody .= "\n------------------------------\n";

    $mailsubject = $_CONF['site_name'] . ' ' . $LANG03[27];

    $to = array();
    $to = COM_formatEmailAddress( '',$_CONF['site_mail'] );
    COM_mail ($to, $mailsubject, $mailbody);

    COM_updateSpeedlimit ('mail');

    return COM_refresh ($_CONF['site_url'] . '/index.php?msg=27');
}


/**
 * Filters comment text and appends necessary tags (sig and/or edit)
 *
 * @copyright Jared Wenerd 2008
 * @author Jared Wenerd <wenerd87 AT gmail DOT com>
 * @param string  $comment   comment text
 * @param string  $postmode ('html', 'plaintext',..)
 * @param bool    $edit     if true append edit tag
 * @param int     $cid      commentid if editing comment (for proper sig)
 * @return string of comment text
*/
function CMT_prepareText($comment, $postmode, $edit = false, $cid = null) {
echo __FUNCTION__ . ' deprecated';
//@@ WE are retiring this function

    global $_USER, $_TABLES, $LANG03, $_CONF;

    $filter = sanitizer::getInstance();
    $filter->setPostmode($postmode);
    $filter->setCensorData(true);
    $filter->setNamespace('glfusion','comment');
    $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
    $filter->setAllowedElements($AllowedElements);

    if ( $postmode != 'text' ) {
        $comment = $filter->filterHTML($comment);
    }

    return $comment;
}

function CMT_preview( $data )
{
echo __FUNCTION__ . ' deprecated';
    global $_CONF, $_TABLES,$_USER,$LANG03;

    $retval = '';
    $mode = 'preview_edit';
    $start = new Template( $_CONF['path_layout'] . 'comment' );
    $start->set_file( array( 'comment' => 'startcomment.thtml' ));
    $start->set_var( 'hide_if_preview', 'style="display:none"' );

    $db = Database::getInstance();

    // Clean up all the vars
    $A = array();
    foreach ($data as $key => $value) {
        if (($key == 'pid') || ($key == 'cid')) {
            $A[$key] = (int) COM_applyFilter ($data[$key], true);
        } else if (($key == 'title') || ($key == 'comment')) {
            // these have already been filtered above

// now we want the raw data

            $A[$key] = $data[$key];
        } else if ($key == 'username') {
            $A[$key] = @htmlspecialchars(strip_tags(trim(COM_checkWords(USER_sanitizeName($data[$key])))),ENT_QUOTES,COM_getEncodingt());
        } else {
            $A[$key] = COM_applyFilter($data[$key]);
        }
    }

    //correct time and username for edit preview
    if ($mode == 'preview' || $mode == 'preview_new' || $mode == 'preview_edit') {
        $A['nice_date'] = $db->conn->fetchColumn("SELECT UNIX_TIMESTAMP(date) FROM `{$_TABLES['comments']}` WHERE cid = ?",
                          array($data['cid']),
                          0,
                          array(Database::INTEGER)
        );
    }
    if (empty ($A['username'])) {
        $A['username'] = $db->conn->fetchColumn("SELECT username FROM `{$_TABLES['users']}` WHERE uid=?",
                          array($data['uid']),
                          0,
                          array(Database::INTEGER)
        );
    }

    $author_id = PLG_getItemInfo($data['type'], $data['sid'], 'author');

//@TODO - we are calling this in preview mode so the first parameter is an associative array
    $thecomments = CMT_getComment ($A, 'flat', $data['type'], 'ASC', false, true,0,$author_id);

    $start->set_var( 'comments', $thecomments );
    $retval .= '<a name="comment_entry"></a>';
    $retval .= COM_startBlock ($LANG03[14])
            . $start->finish( $start->parse( 'output', 'comment' ))
            . COM_endBlock ();

    return $retval;
}


/**
* Return information for a comment
*
* @param    string  $id         topic id or *
* @param    string  $what       comma-separated list of properties
* @param    int     $uid        user ID or 0 = current user
* @param    array   $options    (reserved for future extensions)
* @return   mixed               string or array of strings with the information
*
*/
function plugin_getiteminfo_comment($id, $what, $uid = 0, $options = array())
{
    global $_CONF, $_TABLES, $LANG03;

    $db = Database::getInstance();
    $sqlParams = array();
    $sqlTypes  = array();

    $buildingSearchIndex = false;

    $properties = explode(',', $what);
    $fields = array();
    $fields[] = 'type';
    $fields[] = 'sid';
    foreach ($properties as $p) {
        switch ($p) {
            case 'search_index' :
                $buildingSearchIndex = true;
                break;
            case 'date' :
            case 'date-modified':
            case 'date-created' :
                $fields[] = 'UNIX_TIMESTAMP(date) AS unixdate';
                break;
            case 'description':
            case 'excerpt':
            case 'raw-description' :
            case 'searchidx' :
                $fields[] = 'comment';
                break;
            case 'id':
                $fields[] = 'cid';
                break;
            case 'title':
                $fields[] = 'title';
                break;
            case 'label':
            case 'url':
                $fields[] = 'cid';
                break;
            case 'author' :
                $fields[] = 'uid';
                break;
            case 'author_name' :
                $fields[] = 'uid';
                $fields[] = 'name';
                break;
            default:
                break;
        }
    }

    $fields = array_unique($fields);

    if (count($fields) == 0) {
        $retval = array();
        return $retval;
    }

    if ($id == '*') {
        if ( $buildingSearchIndex ) {
            $where = " WHERE queued = 0 AND pid=0 ";
            $permOp = " AND ";
        } else {
            $where = '';
            $permOp = ' WHERE ';
        }
    } else {
        $where = " WHERE cid = ? ";
        $sqlParams[] = $id;
        $sqlTypes[] = Database::INTEGER;
        $permOp = ' AND ';
    }

    $sql  = "SELECT " . implode(',', $fields) . " ";
    $sql .= "FROM `{$_TABLES['comments']}` ";
    $sql .= $where;

    if ($id != '*') {
        $sql .= ' LIMIT 1';
    }
    try {
        $stmt = $db->conn->executeQuery($sql,$sqlParams,$sqlTypes);
    } catch(Throwable $e) {
        throw($e);
    }

    $retval = array();

    while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
        $props = array();
        foreach ($properties as $p) {
            switch ($p) {
                case 'date' :
                case 'date-created' :
                case 'date-modified':
                    $props[$p] = $A['unixdate'];
                    break;
                case 'description':
                case 'excerpt':
                    $props[$p] = $A['comment'];
                    break;
                case 'searchidx' :
                case 'raw-description' :
                    if ( $buildingSearchIndex ) {
                        $sql = "SELECT GROUP_CONCAT(comment SEPARATOR ' ') as comment
                                FROM `{$_TABLES['comments']}`
                                WHERE pid=? GROUP BY pid";
                        $cmtStmt = $db->conn->executeQuery($sql,array($id),array(Database::INTEGER));
                        while ($B = $cmtStmt->fetch(Database::ASSOCIATIVE)) {
                            if ( isset($B['comment'])) {
                                $A['comment'] = $A['comment'] . $B['comment'];
                            }
                        }
                    }
                    $props[$p] = $A['comment'];
                    break;
                case 'id':
                    $props['id'] = $A['cid'];
                    break;
                case 'title':
                    $props['title'] = strip_tags($A['title']);
                    break;
                case 'url':
                    $url = PLG_getCommentUrlId($A['type']);
                    $sep = strpos($url[0], '?') ? '&' : '?';
                    $finalurl = $url[0] . $sep . $url[1].'='.$A['sid'];
                    $props['url'] = $finalurl.'#cid_'.$A['cid'];
                    break;
                case 'label':
                    $props['label'] = 'Comments';
                    break;
                case 'status':
                    $props['status'] = 1; // stub - default
                    break;
                case 'author' :
                    $props['author'] = $A['uid'];
                    break;
                case 'author_name' :
                    if ( $A['uid'] == 1 ) {
                        // anonymous user
                        if ( $A['name'] != NULL && $A['name'] != '' ) {
                            $props['author_name'] = $A['name'];
                        } else {
                            $props['author_name'] = $LANG03[24];
                        }
                    } else {
                        $props['author_name'] = COM_getDisplayName($A['uid']);
                    }
                    break;
                case 'hits' :
                    $props['hits'] = 0;
                    break;
                case 'perms' :
                    $props['perms'] = array(
                        'owner_id' => 2,
                        'group_id' => 2,
                        'perm_owner' => 3,
                        'perm_group' => 3,
                        'perm_members' => 1,    // fix
                        'perm_anon' => 1,       // fix
                    );
                    break;
                case 'parent_id':
                    $props[$p] = $A['sid'];
                    break;
                case 'parent_type':
                    $props[$p] = $A['type'];
                    break;
                default:
                    $props[$p] = '';
                    break;
            }
        }

        $mapped = array();
        if ( is_array($props) ) {
            foreach ($props as $key => $value) {
                if ($id == '*') {
                    if ($value != '') {
                        $mapped[$key] = $value;
                    }
                } else {
                    $mapped[$key] = $value;
                }
            }
        }

        if ($id == '*') {
            $retval[] = $mapped;
        } else {
            $retval = $mapped;
            break;
        }
    }

    if (($id != '*') && (count($retval) == 1)) {
        $tRet = array_values($retval);
        $retval = $tRet[0];
    }
    if ( $retval === '' || count($retval) == 0 ) return NULL;

    return $retval;
}

/**
* returns list of moderation values
*
* The array returned contains (in order): the row 'id' label, main plugin
* table, moderation fields (comma seperated), and plugin submission table
*
* @return       array        Returns array of useful moderation values
*
*/
function plugin_moderationvalues_comment()
{
    global $_TABLES;

    return array (
        'cid',
        $_TABLES['comments'],
        "cid,queued",
        ''
    );
}

/**
* show files for moderation on submissions page
*
* Uses the Plugin class to return data required by moderation.php to list
* plugin objects that need to be moderated.
*
* @param        string token The
* @return       Plugin       return HTML
*
*/
function plugin_itemlist_comment($token)
{
    global $_CONF, $_TABLES, $_USER;
    global $LANG01, $LANG24, $LANG29, $LANG_ADMIN, $_IMAGE_TYPE;

    $retval = '';
    $key='cid';

    if ( COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $db = Database::getInstance();

    $Coll = new glFusion\Comments\Internal\CommentCollection;
    $Coll->withQueued(true)->execute();
    $commentData = $Coll->getObjects();
    /*$sql = "SELECT *,UNIX_TIMESTAMP(date) AS day, name AS username FROM `{$_TABLES['comments']}` WHERE queued = 1 ORDER BY date DESC";
    $stmt = $db->conn->query($sql);

    $commentData = $stmt->fetchAll(Database::ASSOCIATIVE);*/

    if (count($commentData) == 0) {
        return;
    }

    $data_arr = array();

    foreach($commentData AS $A) {
        $A['edit']      = $_CONF['site_url'].'/comment.php?mode=modedit&amp;cid='.$A['cid'].'&amp;'.CSRF_TOKEN.'='.$token;
        $A['_type_']    = 'comment';
        $A['_key_']     = 'cid';        // name of key/id field
        //$A['preview']   = CMT_preview($A); // format a comment for preview.
        $A['preview'] = $A->preview();
        $A['username']  = $A['name'];
        $A['day'] = $A['nice_date'];
        $data_arr[]   = $A;           // push row data into array
    }

    $header_arr = array(      // display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 0, 'align' => 'center', 'width' => '25px'),
        array('text' => $LANG29[10], 'field' => 'title'),
        array('text' => $LANG29[14], 'field' => 'day', 'align' => 'center', 'width' => '15%'),
        array('text' => $LANG_ADMIN['type'], 'field' => 'type', 'align' => 'center', 'width' => '15%'),
        array('text' => $LANG29[46], 'field' => 'uid', 'width' => '15%', 'nowrap' => true),

        array('text' => $LANG_ADMIN['preview'],'field' => 'preview'),

        array('text' => $LANG29[1], 'field' => 'approve', 'align' => 'center', 'width' => '35px'),
        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'align' => 'center', 'width' => '35px')
    );

    $text_arr = array('has_menu'    => false,
                      'title'       => $LANG01[83],
                      'help_url'    => '',
                      'no_data'     => $LANG01[29],
                      'form_url'    => "{$_CONF['site_admin_url']}/moderation.php"
    );
    $actions = FieldList::approveButton(array(
        'name' => 'approve_x',
        'text' => $LANG29[1],
        'attr' => array(
            'title' => $LANG29[44],
            'onclick' => 'return confirm(\'' . $LANG29[45] . '\');'
        )
    ));
    $actions .= '&nbsp;&nbsp;&nbsp;&nbsp;';

    $actions .= FieldList::deleteButton(array(
        'name' => 'delbutton_x',
        'text' => $LANG_ADMIN['delete'],
        'attr' => array(
            'title' => $LANG01[124],
            'onclick' => 'return confirm(\'' . $LANG29[45] . '\');'
        )
    ));

    $options = array('chkselect' => true,
                     'chkfield' => 'cid',
                     'chkname' => 'selitem',
                     'chkminimum' => 0,
                     'chkall' => true,
                     'chkactions' => $actions,
                     );

    $form_arr['bottom'] = '<input type="hidden" name="type" value="comment"/>' . LB
            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"/>' . LB
            . '<input type="hidden" name="moderation" value="x"/>' . LB
            . '<input type="hidden" name="count" value="' . count($commentData) . '"/>';

    $retval .= ADMIN_simpleList('CMT_getListField', $header_arr,
                              $text_arr, $data_arr, $options, $form_arr, $token);
    return $retval;
}

/**
* Performs plugin exclusive work for items approved by moderation
*
* While moderation.php handles the actual move from mediagallery submission
* to mediagallery tables, within the function we handle all other approval
* relate tasks
*
* @param      string       $id      Identifying string
* @return     string       Any wanted HTML output
*
*/
function plugin_moderationapprove_comment($id)
{
    global $_CONF, $_TABLES, $LANG03;

    if ( (int) $id <= 0 ) {
        return '';
    }

    $Comment = \glFusion\Comments\Internal\Comment::getByCid($id);
    if ($Comment->getCid() < 1) {
        return false;
    }
    $status = $Comment->approve();
    /*$db = Database::getInstance();

    try {
        $stmt = $db->conn->update(
            $_TABLES['comments'],
            array(0),
            array($id),
            array(Database::INTEGER, Database::INTEGER)
        );
    } catch (Throwable $e) {
        if ($db->getIgnore()) {
            $db->_errorlog("SQL Error: " . $e->getMessage());
        } else {
            $db->dbError($e->getMessage(),$sql);
        }
    }

    $sql = "SELECT * FROM `{$_TABLES['comments']}` WHERE cid=?";
    try {
        $row = $db->conn->fetchAssoc($sql,array($id),array(Database::INTEGER));
    } catch(Throwable $e) {
        if ($db->getIgnore()) {
            $db->_errorlog("SQL Error: " . $e->getMessage());
            return false;
        } else {
            $db->dbError($e->getMessage(),$sql);
        }
    }
    if ($row === false) {
        return false;
    }*/

/*    $cid = $id;
    $type = $row['type'];
    $sid  = $row['sid'];
    // now we need to alert everyone a comment has been saved.
    PLG_commentApproved($cid,$type,$sid);   // let plugins know they should update their counts if necessary
    $c = Cache::getInstance();
    $c->deleteItemsByTags(array('whatsnew','menu'));
    if ( $type == 'article' ) {
        $c->deleteItemsByTag('story_'.$sid);
    }
    PLG_itemSaved($cid, 'comment');     // let others know we saved a comment to the prod table
 */

    if ($status) {
        COM_setMsg($LANG03[56],'warning');
    }
    // should handle notification here if we want to.
    return '';
}

/**
* Performs plugin exclusive work for items deleted by moderation
*
*
* @param      string       $id      Identifying string
* @return     string       Any wanted HTML output
*
*/
function plugin_moderationdelete_comment($id)
{
    global $_CONF, $_TABLES;

    if ( (int) $id <= 0 ) {
        return '';
    }
    $db = Database::getInstance();
    $sql = "DELETE FROM `{$_TABLES['comments']}` WHERE cid=? AND queued=1";
    try {
        $stmt = $db->conn->executeUpdate($sql,array($id),array(Database::INTEGER));
    } catch(Throwable $e) {
        if ($db->getIgnore()) {
            $db->_errorlog("SQL Error: " . $e->getMessage());
        } else {
            $db->dbError($e->getMessage(),$sql);
        }
    }
    $c = Cache::getInstance()->deleteItemsByTag('menu');
    return;
}

/**
* Counts the items that are submitted
*
* @return   int     number of items in submission queue
*
*/
function plugin_submissioncount_comment()
{
    global $_TABLES;

    $db = Database::getInstance();

    $retval = $db->conn->fetchColumn("SELECT COUNT(queued) FROM `{$_TABLES['comments']}` WHERE queued = 1",array(),0);
    if ($retval === false) {
        $retval = 0;
    }
    return $retval;
}

function CMT_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN,$LANG03, $LANG28, $LANG29, $_IMAGE_TYPE;

    $retval = '';

    $type = '';
    if (isset($A['_type_']) && !empty($A['_type_'])) {
        $type = $A['_type_'];
    } else {
        return $retval; // we can't work without an item type
    }

    $dt = new Date('now',$_USER['tzid']);
    $field = $fieldname;
    $field = ($fieldname == "0" ) ? 'edit' : $field;
    $field = ($type == 'user' && $fieldname == 1) ? 'user' : $field;
    $field = ($type == 'story' && $fieldname == 2) ? 'day' : $field;
    $field = ($type == 'story' && $fieldname == 3) ? 'tid' : $field;
    $field = ($type == 'user' && $fieldname == 3) ? 'email' : $field;
    $field = ($type <> 'user' && $fieldname == 4) ? 'uid' : $field;
    $field = ($type == 'user' && $fieldname == 4) ? 'day' : $field;
    switch ($field) {
        case 'edit':
            $retval = FieldList::edit(array(
                'url' => $A['edit'],
                'attr' => array(
                    'title' => $LANG_ADMIN['edit'],
                )
            ));
            break;

        case 'title' :
            $retval =  html_entity_decode(htmlspecialchars_decode($fieldvalue));
            break;

        case 'user':
            $retval = FieldList::user(array(

            ));
            $retval .= '&nbsp;' . $fieldvalue;
/*
            $retval =  '<img src="' . $_CONF['layout_url']
            . '/images/admin/user.' . $_IMAGE_TYPE
            . '" style="vertical-align:bottom;"/>&nbsp;' . $fieldvalue;
*/
            break;

        case 'day':
            $dt->setTimeStamp($A['day']);
            $retval = $dt->format($_CONF['daytime'],true);
            break;

        case 'tid':
            $db = Database::getInstance();
            $retval = $db->conn->fetchColumn(
                        "SELECT topic FROM `{$_TABLES['topics']}` WHERE tid=?",
                        array($A['tid']),
                        0,
                        array(Database::STRING)
                      );
            if ($retval === false) {
                $retval = '';
            }
            break;

        case 'uid':
            if ( !isset($A['uid']) ) {
                $A['uid'] = 1;
            }
            if ( $A['uid'] == 1 ) {

                if ( empty($A['name']) ) $A['name'] = $LANG03[24];
                $retval = FieldList::editusers();
                $retval .= $A['name'];
            } else {
                $db = Database::getInstance();
                $username = $db->conn->fetchColumn(
                                "SELECT username FROM `{$_TABLES['users']}` WHERE uid=?",
                                array($A['uid']),
                                0,
                                array(Database::INTEGER)
                            );

                $retval = FieldList::editusers(
                    array(
                        'url' => $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' .  $A['uid'],
                        'attr' => array(
                            'title' => $LANG28[108],
                        )
                    )
                );
                $attr['title'] = $LANG28[108];
                $url = $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' .  $A['uid'];
                $retval .= COM_createLink($username, $url, $attr);
            }
            break;

        case 'email':
            $retval = FieldList::email(array(
                'url' => 'mailto:' . $fieldvalue,
                'attr' => array(
                    'title' => $LANG28[111],
                )
            ));
            $retval .= '&nbsp;&nbsp;';
            $attr['title'] = $LANG28[99];
            $url = $_CONF['site_admin_url'] . '/mail.php?uid=' . $A['uid'];
            $retval .= COM_createLink($fieldvalue, $url, $attr);
            break;

        case 'approve':
            $retval = FieldList::approve(array(
                'url' => $_CONF['site_admin_url'] . '/moderation.php'.'?approve=x'.'&amp;type=' . $A['_type_'].'&amp;id=' . $A['cid']. '&amp;' . CSRF_TOKEN . '=' . $token,
                'attr' => array(
                    'title' => $LANG29[1],
                    'onclick' => 'return confirm(\'' . $LANG29[48] . '\');',
                )
            ));
            break;

        case 'delete':
            $retval = FieldList::delete(array(
                'delete_url' => $_CONF['site_admin_url'] . '/moderation.php'.'?delete=x'.'&amp;type=' . $A['_type_'].'&amp;id=' . $A['cid'].'&amp;' . CSRF_TOKEN . '=' . $token,
                'attr' => array(
                    'title' => $LANG_ADMIN['delete'],
                    'onclick' => 'return confirm(\'' . $LANG29[49] . '\');',
                )
            ));
            break;

        case 'preview' :
            $retval = '
                <a href="#cmtpreview'.$A['cid'].'" rel="modal:open">'.$LANG_ADMIN['preview'].'</a>
                <div id="cmtpreview'.$A['cid'].'" style="display:none;">
                '.$fieldvalue.'
                </div>';
            break;

        default:
            $retval = COM_makeClickableLinks($fieldvalue);
            break;
    }

    return $retval;
}

/**
* Do we support feeds?
*
* @return   array   id/name pairs of all supported feeds
*
*/
function plugin_getfeednames_comment()
{
    global $_TABLES, $_PLUGIN_INFO;

    $feeds = array ();

    $feeds[] = array ('id' => 'all', 'name' => 'All Comments');
    $feeds[] = array ('id' => 'article', 'name' => 'Stories');
    foreach ( $_PLUGIN_INFO as $plugin ) {
        if ( $plugin['pi_enabled']  == 1 ) {
            if ( function_exists('plugin_commentsupport_'.$plugin['pi_name']) ) {
                $function = 'plugin_commentsupport_'.$plugin['pi_name'];
                if ( $function() == true ) {
                    $feeds[] = array('id' => $plugin['pi_name'], 'name' => ucfirst($plugin['pi_name']));
                }
            }
        }
    }
    return $feeds;
}

/**
* Provide feed data
*
* @param    int     $feed       feed ID
* @param    ref     $link
* @param    ref     $update
* @return   array               feed entries
*
*/
function plugin_getfeedcontent_comment ($feed, &$link, &$update)
{
    global $_CONF, $_TABLES;

    $db = Database::getInstance();

    $sql = "SELECT topic,limits,content_length
            FROM `{$_TABLES['syndication']}`
            WHERE fid = ?";

    $S = $db->conn->fetchAssoc($sql,array($feed),array(Database::STRING));
    $sql = "SELECT * FROM `{$_TABLES['comments']}` where queued = 0 ";

    if( $S['topic'] != 'all' ) {
        $sql .= " AND type = " . $db->conn->quote($S['topic']) . " ";
    }
    $sql .= " ORDER BY date DESC ";

    $stmt = $db->conn->query($sql);
    $counter = 0;

    while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
        $itemInfo = PLG_getItemInfo($row['type'],$row['sid'],'url,perms',1);
        if ( isset($itemInfo['perms'] ) ) {
            if ( $itemInfo['perms']['perm_anon'] > 0 && isset($itemInfo['url']) && $itemInfo['url'] != '' ) {
                $cids[] = $row['cid'];
                $title = $row['title'];
                $body = str_replace('<!-- COMMENTSIG --><div class="comment-sig">', '', $row['comment']);
                $body = str_replace('</div><!-- /COMMENTSIG -->', '', $body);
                $body = str_replace('<div class="comment-edit">', '', $body);
                $body = str_replace('</div><!-- /COMMENTEDIT -->', '', $body);
                if( preg_match( '/<.*>/', $body ) == 0 ) {
                    $body = nl2br( $body );
                }
                $body = str_replace('<!-- comment -->', '', $body);
                $author = ($row['uid'] == 1 ? $row['name'] : COM_getDisplayName( $row['uid'] ));

                switch ( (int) $S['content_length'] ) {
                    case 0 :
                        $body = '';
                        break;
                    case 1 :
                        $body = trim($body).'<br><br>'.$author;
                        break;
                    default :
                        $body = trim(SYND_truncateSummary( $body, $S['content_length'] ));
                        break;
                }

                $url   = $itemInfo['url'];
                $content[] = array( 'title'   => $title,
                                    'summary' => $body,
                                    'link'    => $url . '#cid_'.$row['cid'],
                                    'uid'     => $row['uid'],
                                    'author'  => $author,
                                    'date'    => $row['date'],
                                    'format'  => 'html'
                                  );

            }
            $counter++;
            if ( $counter > $S['limits'] )
                break;
        }
    }
    $link = $_CONF['site_url'];
    $update = time();

    return $content;
}

/**
* Checking if comment feeds are up to date
*
* @param    int     $feed           id of feed to be checked
* @param    string  $topic          topic
* @param    string  $update_data    data describing current feed contents
* @param    string  $limit          number of entries or number of hours
* @param    string  $updated_type   (optional) type of feed to be updated
* @param    string  $updated_topic  (optional) feed's "topic" to be updated
* @param    string  $updated_id     (optional) id of entry that has changed
* @return   boolean                 true: feed data is up to date; false: isn't
*
*/
function plugin_feedupdatecheck_comment ($feed, $topic, $update_data, $limit, $updated_type = '', $updated_topic = '', $updated_id = '')
{
    global $_TABLES, $_VARS;

    $is_current = true;

    if ($updated_type != 'comment') {
        $updated_type = '';
        $updated_topic = '';
        $updated_id = '';
    }
    if ( (int) $_VARS['cmt_update'] > (int) $update_data ) {
        return false;
    }
    return true;
}


function plugin_itemsaved_comment( $id, $type )
{
    global $_TABLES, $_VARS;

    if ($type != 'comment') {
        return;
    }
    $db = Database::getInstance();
    $sql = "SELECT * FROM `{$_TABLES['comments']}` WHERE queued = 0 AND cid=?";

    $stmt = $db->conn->executeQuery($sql,array($id),array(Database::INTEGER));
    while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
        $itemInfo = PLG_getItemInfo($row['type'],$row['sid'],'id,perms',1);
        if ( isset($itemInfo['perms'] ) ) {
            if ( isset($itemInfo['perms']['perm_anon']) && $itemInfo['perms']['perm_anon'] > 0 ) {
                $now = time();
                $db->conn->executeUpdate(
                    "REPLACE INTO `{$_TABLES['vars']}` (name,value) VALUES('cmt_update',?)",
                    array($now),
                    array(Database::STRING)
                );
                $_VARS['cmt_update'] = $now;
            }
        }
    }
    return;
}

function plugin_privacy_export_comment($uid,$email='',$username='',$ip='')
{
    global $_CONF, $_TABLES;

    $db = Database::getInstance();
    $sqlParams = array();
    $sqlTypes  = array();

    $retval = '';

    $exportFields = array('type','sid','date','title','name','uid','ipaddress');

    $sql = "SELECT * `FROM {$_TABLES['comments']}` WHERE uid = ? ";
    $sqlParams[] = $uid;
    $sqlTypes[]  = Database::INTEGER;

    if ( $ip != '' ) {
        $sql .= " OR ipaddress = ?";
        $sqlParams[] = $ip;
        $sqlTypes[]  = Database::STRING;

    }
    $sql .= " ORDER BY date ASC";

    $stmt = $db->conn->executeQuery($sql,$sqlParams,$sqlTypes);

    $rows = $stmt->fetchAll(Database::ASSOCIATIVE);

    $retval .= "<comments>\n";

    foreach($rows AS $row) {
        $retval .= "<comment>\n";
        foreach($row AS $item => $value) {
            if ( in_array($item,$exportFields) && $item != '0') {
                $retval .= '<'.$item.'>'.addSlashes(htmlentities($value)).'</'.$item.">\n";
            }
        }
        $retval .= "</comment>\n";
    }
    $retval .= "</comments>\n";

    if ( function_exists('tidy_repair_string')) {
        $retval = tidy_repair_string($retval, array('input-xml' => 1));
    }

    return $retval;

}
?>
