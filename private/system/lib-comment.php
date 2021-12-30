<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-comment.php                                                          |
// |                                                                          |
// | glFusion comment library.                                                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2018 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

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

    $dt = new Date('now',$_USER['tzid']);
    $permalink = 'Not defined';

    $filter = sanitizer::getInstance();
    $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
    $filter->setAllowedelements($AllowedElements);
    $filter->setNamespace('glfusion','comment');

    $post_id = COM_applyFilter($post_id,true);
    $result = DB_query("SELECT * FROM {$_TABLES['comments']} WHERE queued = 0 AND cid={$post_id}");
    if ( DB_numRows($result) > 0 ) {
        $A = DB_fetchArray($result);
        $itemInfo = PLG_getItemInfo($A['type'],$track_id,'url,title');
        $permalink = $itemInfo['url'];
        if ( empty($permalink) ) {
            $permalink = $_CONF['site_url'];
        }
        if ( $A['uid'] > 1 ) {
            $name = COM_getDisplayName($A['uid']);
        } else {
            $name = $filter->sanitizeUsername($A['name']);
            $name = $filter->censor($name);
        }

        $name = @htmlspecialchars($name,ENT_QUOTES, COM_getEncodingt());

        $A['title']   = COM_checkWords($A['title']);
        $html2txt = new Html2Text\Html2Text(strip_tags($A['title']),false);
        $A['title'] = $html2txt->get_text();

        //and finally: format the actual text of the comment, but check only the text, not sig or edit
        $text = str_replace('<!-- COMMENTSIG --><div class="comment-sig">', '', $A['comment']);
        $text = str_replace('</div><!-- /COMMENTSIG -->', '', $text);
        $text = str_replace('<div class="comment-edit">', '', $text);
        $text = str_replace('</div><!-- /COMMENTEDIT -->', '', $text);

        $A['comment'] = $text;
        if( preg_match( '/<.*>/', $text ) == 0 ) {
            $A['comment'] = nl2br( $A['comment'] );
        }
        $A['comment'] = $filter->_replaceTags($A['comment']);

        $notifymsg = sprintf($LANG03[46],'<a href="'.$_CONF['site_url'].'/comment.php?mode=unsubscribe&sid='.htmlentities($track_id).'&type='.$A['type'].'" rel="nofollow">'.$LANG01['unsubscribe'].'</a>');

        $dt->setTimestamp(strtotime($A['date']));
        $date = $dt->format('F d Y @ h:i a');
        $T = new Template( $_CONF['path_layout'] . 'comment' );
        $T->set_file (array(
            'htmlemail'     => 'notifymessage_html.thtml',
            'textemail'     => 'notifymessage_text.thtml',
        ));

        $T->set_var(array(
            'post_subject'  => $A['title'],
            'post_date'     => $date,
            'iso8601_date'  => $dt->toISO8601(),
            'post_name'     => $name,
            'post_comment'  => $A['comment'],
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

//        return array($message,$messageText,array());
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

    $parts = explode( '/', $_SERVER['PHP_SELF'] );
    $page = array_pop( $parts );

    $nrows = DB_count( $_TABLES['comments'], array( 'sid', 'type','queued' ),
                       array( DB_escapeString($sid), DB_escapeString($type),0 ));

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
        $result = DB_query( "SELECT username,fullname FROM {$_TABLES['users']} WHERE uid = 1" );
        $N = DB_fetchArray( $result );
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
    $selector = '<select name="order">' . LB
              . $selector_data
              . LB . '</select>';
    $commentbar->set_var( 'order_selector', $selector);
    $commentbar->set_var( 'order_select_data', $selector_data);

    // Mode
    if( $page == 'comment.php' ) {
        $selector = '<select name="format">';
    } else {
        $selector = '<select name="mode">';
    }
    $selector_data = COM_optionList( $_TABLES['commentmodes'], 'mode,name', $mode );
    $selector .= LB
               . $selector_data
               . LB . '</select>';
    $commentbar->set_var( 'mode_selector', $selector);
    $commentbar->set_var( 'mode_select_data',$selector_data);
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
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG03, $MESSAGE, $_IMAGE_TYPE;

    $indent = 0;  // begin with 0 indent
    $retval = ''; // initialize return value

    static $userInfo = array();

    $filter = sanitizer::getInstance();
    $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
    $filter->setAllowedelements($AllowedElements);
    $filter->setNamespace('glfusion','comment');

    if ( $mode == 'threaded' ) $mode = 'nested';

    $template = new Template( $_CONF['path_layout'] . 'comment' );
    $template->set_file( array( 'comment' => 'comment.thtml',
                                'thread'  => 'thread.thtml'  ));

    // generic template variables
    $template->set_var( 'lang_authoredby', $LANG01[42] );
    $template->set_var( 'lang_on', $LANG01[36] );
    $template->set_var( 'lang_permlink', $LANG01[120] );
    $template->set_var( 'order', $order );

    if( $ccode == 0 &&
     ($_CONF['commentsloginrequired'] == 0 || !COM_isAnonUser())) {
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

    if( $preview ) {
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
    } else {
        $A = DB_fetchArray( $comments );
        $template->unset_var('preview_mode');
    }

    if( empty( $A ) ) {
        return '';
    }
    $token = '';
    if ($delete_option && !$preview) {
        $token = SEC_createToken();
    }

    $row = 1;

    do {
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
        if ( $A['postmode'] == 'plaintext' ) $A['comment'] = nl2br($A['comment']);

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
        $commentFooter = '';

        $template->unset_var('delete_link');
        $template->unset_var('ipaddress');
        $template->unset_var('reply_link');
        $template->unset_var('edit_link');
        //check for comment edit
        $commentedit = DB_query("SELECT cid,uid,UNIX_TIMESTAMP(time) as time FROM {$_TABLES['commentedits']} WHERE cid = ".(int) $A['cid']);
        $B = DB_fetchArray($commentedit);
        if ($B) { //comment edit present
            //get correct editor name
            if ($A['uid'] == $B['uid']) {
                $editname = $A['username'];
            } else {
                $editname = DB_getItem($_TABLES['users'], 'username', "uid=".(int) $B['uid']);
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

        if ( !isset($A['uid']) || $A['uid'] == '' ) {
            $A['uid'] = 1;
        }

        //@TODO - Move this to function
        if ( $A['uid'] > 1 ) {
            if (!isset($userInfo[$A['uid']])) {
                $result = DB_query("SELECT username,remoteusername,remoteservice,fullname,sig,photo FROM {$_TABLES['users']} WHERE uid=".(int) $A['uid']);
                $resultSet = DB_fetchAll($result);
                if (count($resultSet) > 0 ) {
                    $userInfo[$A['uid']] = $resultSet[0];
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

        $filter->setReplaceTags(true);
        $filter->setCensorData(true);

        $filter->setPostmode('html');

        $template->set_var( 'indent', $indent );
        $template->set_var( 'author_name', $filter->sanitizeUsername($A['username'] ));
        $template->set_var( 'author_id', $A['uid'] );
        $template->set_var( 'cid', $A['cid'] );
        $template->set_var( 'pid', $A['pid'] );
        $template->set_var( 'cssid', $row % 2 );
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
        if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
                                 ($_CONF['commentsloginrequired'] == 1))) {
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
                    DB_getItem($_TABLES['comments'], 'COUNT(*)', "queued = 0 AND pid = ".(int) $A['cid']) == 0) {
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

        //and finally: format the actual text of the comment
        $text = str_replace('<!-- COMMENTSIG --><div class="comment-sig">', '', $A['comment']);
        $text = str_replace('</div><!-- /COMMENTSIG -->', '', $text);
        $text = str_replace('<div class="comment-edit">', '', $text);
        $text = str_replace('</div><!-- /COMMENTEDIT -->', '', $text);

        // highlight search terms if specified
        if( !empty( $_REQUEST['query'] )) {
            $A['comment'] = COM_highlightQuery( $A['comment'], strip_tags($_REQUEST['query']) );
        }

        if (function_exists('msg_replaceEmoticons'))  {
            $A['comment'] = msg_replaceEmoticons($A['comment']);
        }
        $A['comment'] = $filter->displayText($A['comment']);

        // create a reply to link
        $reply_link = '';
        if( $ccode == 0 &&
         ($_CONF['commentsloginrequired'] == 0 || !COM_isAnonUser())) {
            $reply_link = $_CONF['site_url'] . '/comment.php?sid=' . $A['sid']
                        . '&amp;pid=' . $A['cid'] . '&amp;type=' . $A['type']
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

        // add signature if available
        $sig = isset($A['sig']) ? $filter->censor($A['sig']) : '';
        $finalsig = '';
        if ($A['uid'] > 1 && !empty($sig)) {
            $finalsig .= '<div class="comment-sig tm-comment-sig">';
            $finalsig .= nl2br('---' . LB . $sig);
            $finalsig .= '</div>';
        }

        $template->set_var( 'title', $A['title'] );
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
    } while( $A = DB_fetchArray( $comments ));

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
 *
 *
 *
 */

function CMT_getCommentLinkWithCount( $type, $sid, $url, $cmtCount = 0, $urlRewrite = 0 ) {
    global $_CONF, $LANG01;

    $retval = '';

    if ( !isset($_CONF['comment_engine']) ) $_CONF['comment_engine'] = 'internal';
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
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG03;

    $retval = '';

    if ( !isset($_CONF['comment_engine'])) $_CONF['comment_engine'] = 'internal';

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
                $result = DB_query( "SELECT commentorder,commentmode,commentlimit FROM {$_TABLES['usercomment']} WHERE uid = {$_USER['uid']}" );
                $U = DB_fetchArray( $result );
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
                $limit = $_CONF['comment_limit'];
            } else {
                $limit = (int) $limit;
            }

            if( !is_numeric($page) || $page < 1 ) {
                $page = 1;
            } else {
                $page = (int) $page;
            }

            $start = $limit * ( $page - 1 );

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
                               . "FROM {$_TABLES['comments']} AS c, {$_TABLES['users']} AS u "
                               . "WHERE c.queued = 0 AND c.uid = u.uid AND c.cid = ".(int) $pid." AND type='".DB_escapeString($type)."'";
                        } else {
                            $count = DB_count( $_TABLES['comments'],
                                        array( 'sid', 'type','queued' ), array( DB_escapeString($sid), DB_escapeString($type),0 ));

                            $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, "
                               . "UNIX_TIMESTAMP(c.date) AS nice_date "
                               . "FROM {$_TABLES['comments']} AS c, {$_TABLES['users']} AS u "
                               . "WHERE c.queued = 0 AND c.uid = u.uid AND c.sid = '".DB_escapeString($sid)."' AND type='".DB_escapeString($type)."' "
                               . "ORDER BY date $order LIMIT $start, $limit";
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
                                . "FROM {$_TABLES['comments']} AS c, {$_TABLES['comments']} AS c2 "
                                . "WHERE c.queued = 0 AND c.sid = '".DB_escapeString($sid)."' AND (c.lft >= c2.lft AND c.lft <= c2.rht) "
                                . "AND c2.cid = ".(int) $pid." AND c.type='".DB_escapeString($type)."'";
                            $result = DB_query( $q2 );
                            list( $count ) = DB_fetchArray( $result );

                            $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, c2.indent AS pindent, "
                               . "UNIX_TIMESTAMP(c.date) AS nice_date "
                               . "FROM {$_TABLES['comments']} AS c, {$_TABLES['comments']} AS c2, "
                               . "{$_TABLES['users']} AS u "
                               . "WHERE c.queued = 0 AND c.sid = '".DB_escapeString($sid)."' AND (c.lft >= c2.lft AND c.lft <= c2.rht) "
                               . "AND c2.cid = ".(int) $pid." AND c.uid = u.uid AND c.type='".DB_escapeString($type)."' "
                               . "ORDER BY $cOrder LIMIT $start, $limit";
                        } else {    // pid refers to parentid rather than commentid
                            if( $pid == 0 ) {  // the simple, fast case
                                // count the total number of applicable comments
                                $count = DB_count( $_TABLES['comments'],
                                        array( 'sid', 'type','queued' ), array( DB_escapeString($sid), DB_escapeString($type),0 ));

                                $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, 0 AS pindent, "
                                   . "UNIX_TIMESTAMP(c.date) AS nice_date "
                                   . "FROM {$_TABLES['comments']} AS c, {$_TABLES['users']} AS u "
                                   . "WHERE queued = 0 AND c.sid = '".DB_escapeString($sid)."' AND c.uid = u.uid  AND type='".DB_escapeString($type)."' "
                                   . "ORDER BY $cOrder LIMIT $start, $limit";
                            } else {
                                // count the total number of applicable comments
                                $q2 = "SELECT COUNT(*) "
                                    . "FROM {$_TABLES['comments']} AS c, {$_TABLES['comments']} AS c2 "
                                    . "WHERE c.queued = 0 AND c.sid = '".DB_escapeString($sid)."' AND (c.lft > c2.lft AND c.lft < c2.rht) "
                                    . "AND c2.cid = ".(int) $pid." AND c.type='".DB_escapeString($type)."'";
                                $result = DB_query($q2);
                                list($count) = DB_fetchArray($result);

                                $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, c2.indent + 1 AS pindent, "
                                   . "UNIX_TIMESTAMP(c.date) AS nice_date "
                                   . "FROM {$_TABLES['comments']} AS c, {$_TABLES['comments']} AS c2, "
                                   . "{$_TABLES['users']} AS u "
                                   . "WHERE c.queued = 0 AND c.sid = '".DB_escapeString($sid)."' AND (c.lft > c2.lft AND c.lft < c2.rht) "
                                   . "AND c2.cid = ".(int) $pid." AND c.uid = u.uid AND c.type='".DB_escapeString($type)."' "
                                   . "ORDER BY $cOrder LIMIT $start, $limit";
                            }
                        }
                        break;
                }

                $thecomments = '';
                $result = DB_query( $q );

                $thecomments .= CMT_getComment( $result, $mode, $type, $order,
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
    global $_TABLES;
    if ( $type == '' || $sid == '' ) return 0;
    return DB_count ($_TABLES['comments'], array('sid','type','queued'), array(DB_escapeString($sid), DB_escapeString($type),(int) $queued ) );
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
function CMT_commentForm($title,$comment,$sid,$pid='0',$type='',$mode='',$postmode='')
{
    global $_CONF, $_TABLES, $_USER, $LANG03, $LANG12, $LANG_LOGIN, $LANG_ACCESS, $LANG_ADMIN;

    $moderatorEdit = false;
    $adminEdit = false;
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

    $retval = '';
    $cid = 0;
    $edit_comment = '';

    $filter = sanitizer::getInstance();
    $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
    $filter->setAllowedelements($AllowedElements);
    $filter->setNamespace('glfusion','comment');

    // never trust $uid ...
    if (COM_isAnonUser()) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $commentuid = $uid;
    if ( ($mode == 'edit' || $mode == 'preview_edit') && isset($_REQUEST['cid']) ) {
        $cid = COM_applyFilter ($_REQUEST['cid']);
        $commentuid = DB_getItem ($_TABLES['comments'], 'uid', "queued=0 AND cid = ".(int) $cid);
    }

    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) || ($_CONF['commentsloginrequired'] == 1))) {
        $retval .= SEC_loginRequiredForm();
        return $retval;
    } else {
        COM_clearSpeedlimit ($_CONF['commentspeedlimit'], 'comment');

        $last = 0;
        if ($mode != 'edit' && $mode != 'preview' && $mode != 'preview_new' && $mode != 'preview_edit') {
            //not edit mode or preview changes
            $last = COM_checkSpeedlimit ('comment');
        }

        if ($last > 0) {
            $retval .= COM_showMessageText($LANG03[7].$last.sprintf($LANG03[8],$_CONF['commentspeedlimit']),$LANG12[26],false,'error');
        } else {
            if ( empty($postmode) ) {
                $postmode = $_CONF['comment_postmode'];
            }

            $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
            $filter->setPostmode($postmode);
            $filter->setCensorData(true);
            $filter->setAllowedElements($AllowedElements);
            $comment         = $filter->filterHTML($comment);
            $display_comment = $filter->displayText($comment);
            $edit_comment    = $filter->editableText($comment);

            $filter->setPostmode('text');
            $title = $filter->displayText($title);
//            $title = $filter->editableText($title);
            $filter->setPostmode($postmode);

            $_POST['title']     = $title;
            $_POST['comment']   = $display_comment;
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
                    } else if (($key == 'title') || ($key == 'comment')) {
                        // these have already been filtered above
                        $A[$key] = $_POST[$key];
                    } else if ($key == 'username') {
                        $A[$key] = @htmlspecialchars(COM_checkWords(strip_tags($_POST[$key])),ENT_QUOTES,COM_getEncodingt());
                        $A[$key] = USER_uniqueUsername($A[$key]);
                    } else {
                        $A[$key] = COM_applyFilter($_POST[$key]);
                    }
                }

                //correct time and username for edit preview
                if ($mode == 'preview' || $mode == 'preview_new' || $mode == 'preview_edit') {
                    $A['nice_date'] = DB_getItem ($_TABLES['comments'],'UNIX_TIMESTAMP(date)', "cid = ".(int) $cid);

                    // not an anonymous user
                    if ( $commentuid > 1 ) {
                        // get username from DB - we don't allow
                        // logged-in-users to set or change their name
                        $A['username'] = DB_getItem ($_TABLES['users'],'username', "uid = ".(int) $commentuid);
                    } else {
                        // we have an anonymous user - so $_POST['username'] should be set
                        // already from above
                    }

                }
                if (empty ($A['username'])) {
                    $A['username'] = DB_getItem ($_TABLES['users'], 'username',"uid = ".(int) $commentuid);
                }

                $author_id = PLG_getItemInfo($type, $sid, 'author');

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
                        $username = DB_getItem($_TABLES['comments'],'name','cid='.(int) $cid);
                    }
                    if ( empty($username)) {
                        $username = $LANG03[24]; //anonymous user
                    }

                    $comment_template->set_var('username',$username);
                    if ( $commentuid > 1 ) {
                        $comment_template->set_var('username_disabled','disabled="disabled"');
                    } else {
                        $comment_template->unset_var('username_disabled');
                    }
                } else {
                    $comment_template->set_var('uid', $_USER['uid']);
                    $name = COM_getDisplayName($_USER['uid'], $_USER['username'],$_USER['fullname']);
                    $comment_template->set_var('username', $name);
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
                    $name = $A['username'];
                } else {
                    $name = $LANG03[24]; //anonymous user
                }
                $usernameblock = '<input type="text" name="username" size="16" value="' .
                                 $name . '" maxlength="32"/>';
                $comment_template->set_var('username', $name); // $usernameblock);

                $comment_template->set_var('action_url', $_CONF['site_url'] . '/users.php?mode=new');
                $comment_template->set_var('lang_logoutorcreateaccount',$LANG03[04]);
                $comment_template->unset_var('username_disabled');
            }

            if ( $postmode == 'html' ) {
                $comment_template->set_var('htmlmode',true);
            }
            $comment_template->set_var('lang_title', $LANG03[16]);
//            $comment_template->set_var('title', @htmlspecialchars($title,ENT_COMPAT,COM_getEncodingt()));

            $comment_template->set_var('title', $title);

            $comment_template->set_var('lang_timeout',$LANG_ADMIN['timeout_msg']);
            $comment_template->set_var('lang_comment', $LANG03[9]);
            $comment_template->set_var('comment', $edit_comment);
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
            PLG_templateSetVars ('comment', $comment_template);
            if ($mode == 'preview_edit' || ($mode == 'edit' && $_CONF['skip_preview'] == 1) ) {
                //for editing
                $comment_template->set_var('save_type','saveedit');
                $comment_template->set_var('lang_save',$LANG03[29]);
                $comment_template->set_var('save_option', '<input type="submit" name="saveedit" value="'
                    . $LANG03[29] . '"/>');
            } elseif (($_CONF['skip_preview'] == 1) || ($mode == 'preview_new')) {
                //new comment
                $comment_template->set_var('save_type','savecomment');
                $comment_template->set_var('lang_save',$LANG03[11]);
                $comment_template->set_var('save_option', '<input type="submit" name="savecomment" value="'
                    . $LANG03[11] . '"/>');
            }

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
        }
    }

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

    $ret = 0;

    // Get a valid uid
    if (empty ($_USER['uid'])) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    // Sanity check
    if (empty ($sid) || empty ($comment) || empty ($type) ) {
        COM_errorLog("CMT_saveComment: $uid from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to submit a comment with one or more missing values.');
       if ( SESS_isSet('glfusion.commentpresave.error') ) {
            $msg = SESS_getVar('glfusion.commentpresave.error') . '<br/>' . $LANG03[12];
        } else {
            $msg = $LANG03[12];
        }
        SESS_setVar('glfusion.commentpresave.error',$msg);

        return $ret = 1;
    }

    // Check that anonymous comments are allowed
    if (($uid == 1) && (($_CONF['loginrequired'] == 1) || ($_CONF['commentsloginrequired'] == 1))) {
        COM_errorLog("CMT_saveComment: IP address {$_SERVER['REMOTE_ADDR']} "
                   . 'attempted to save a comment with anonymous comments disabled for site.');
        return $ret = 2;
    }

    // Check for people breaking the speed limit
    COM_clearSpeedlimit ($_CONF['commentspeedlimit'], 'comment');
    $last = COM_checkSpeedlimit ('comment');
    if ($last > 0) {
        COM_errorLog("CMT_saveComment: $uid from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to submit a comment before the speed limit expired');
        return $ret = 3;
    }
    // Let plugins have a chance to check for spam
    $spamcheck = '<h1>' . $title . '</h1><p>' . $comment . '</p>';
    if ( COM_isAnonUser() ) {
        if (isset($_POST['username']) ) {
            $uname = $_POST['username'];
        } else {
            $uname = '';
        }
        $email = '';
    } else {
        $uname = $_USER['username'];
        $email = $_USER['email'];
    }
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

    // Let plugins have a chance to decide what to do before saving the comment, return errors.
    if ($someError = PLG_commentPreSave($uid, $title, $comment, $sid, $pid, $type, $postmode)) {
        return $someError;
    }

    $info = PLG_getItemInfo($type, $sid, 'id,title');
    $title = (isset($info['title']) ? $info['title'] : 'Comment');
    $comment = CMT_prepareText($comment,$postmode);

    // check for non-int pid's
    // this should just create a top level comment that is a reply to the original item
    if (!is_numeric($pid) || ($pid < 0)) {
        $pid = 0;
    }

    if ( !empty ($comment) ) {
        $filter = sanitizer::getInstance();
        COM_updateSpeedlimit ('comment');

        $queued = 0;
        if ( isset($_CONF['commentssubmission'] ) && $_CONF['commentssubmission'] > 0 ) {
            switch ( $_CONF['commentssubmission'] ) {
                case 1 : // anonymous only
                    if ( COM_isAnonUser() ) $queued = 1;
                    break;
                case 2 : // all users
                default :
                    $queued = 1;
                    break;
            }
            if ( SEC_hasRights('comment.submit') ) $queued = 0;
        }

        // Insert the comment into the comment table
        DB_lockTable ($_TABLES['comments']);
        if ($pid > 0) {
            $result = DB_query("SELECT rht, indent FROM {$_TABLES['comments']} WHERE cid = ".(int) $pid
                             . " AND sid = '".DB_escapeString($sid)."'");
            list($rht, $indent) = DB_fetchArray($result);
            if ( !DB_error() ) {
                DB_query("UPDATE {$_TABLES['comments']} SET lft = lft + 2 "
                       . "WHERE sid = '".DB_escapeString($sid)."' AND type = '$type' AND lft >= $rht");
                DB_query("UPDATE {$_TABLES['comments']} SET rht = rht + 2 "
                       . "WHERE sid = '".DB_escapeString($sid)."' AND type = '$type' AND rht >= $rht");
                DB_save ($_TABLES['comments'], 'sid,uid,comment,date,title,pid,queued,postmode,lft,rht,indent,type,ipaddress',
                        "'".DB_escapeString($sid)."',$uid,'".DB_escapeString($comment)."','".$_CONF['_now']->toMySQL(true)."','".DB_escapeString($title)."',".(int) $pid.",$queued,'".DB_escapeString($postmode)."',$rht,$rht+1,$indent+1,'".DB_escapeString($type)."','".DB_escapeString($_SERVER['REMOTE_ADDR'])."'");
            } else { //replying to non-existent comment or comment in wrong article
                COM_errorLog("CMT_saveComment: $uid from {$_SERVER['REMOTE_ADDR']} tried "
                           . 'to reply to a non-existent comment or the pid/sid did not match');
                $ret = 4; // Cannot return here, tables locked!
            }
        } else {
            $rht = DB_getItem($_TABLES['comments'], 'MAX(rht)', "sid = '".DB_escapeString($sid)."'");
            if ( DB_error() ) {
                $rht = 0;
            }
            DB_save ($_TABLES['comments'], 'sid,uid,comment,date,title,pid,queued,postmode,lft,rht,indent,type,ipaddress',
                    "'".DB_escapeString($sid)."',".(int) $uid.",'".DB_escapeString($comment)."','".$_CONF['_now']->toMySQL(true)."','".DB_escapeString($title)."',".(int) $pid.",$queued,'".DB_escapeString($postmode)."',$rht+1,$rht+2,0,'".DB_escapeString($type)."','".DB_escapeString($_SERVER['REMOTE_ADDR'])."'");
        }
        $cid = DB_insertId();
        //set Anonymous user name if present
        if (isset($_POST['username']) ) {
            $name = @htmlspecialchars(strip_tags(trim(COM_checkWords(USER_sanitizeName($_POST['username'])))),ENT_QUOTES,COM_getEncodingt());
            DB_change($_TABLES['comments'],'name',DB_escapeString($name),'cid',(int) $cid);
        }
        DB_unlockTable ($_TABLES['comments']);

        if ( $queued == 0 ) {
            CACHE_remove_instance('whatsnew');
            if ($type == 'article') {
                CACHE_remove_instance('story_'.$sid);
            }
            PLG_itemSaved($cid, 'comment');
        } else {
            CACHE_remove_instance('menu');
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
        // Send notification of comment if no errors and notications enabled for comments
        if (($ret == 0) && isset ($_CONF['notification']) && in_array ('comment', $_CONF['notification'])) {
            CMT_sendNotification ($title, $comment, $uid, $_SERVER['REMOTE_ADDR'],$type, $cid,$queued);
        }
        if ( $ret == 0 && $queued == 0) {
            PLG_sendSubscriptionNotification('comment',$type,$sid,$cid,$uid);
        }
    } else {
        COM_errorLog("CMT_saveComment: $uid from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to submit a comment with invalid $title and/or $comment.');
        return $ret = 5;
    }

    return $ret;
}

/**
* Send an email notification for a new comment submission.
*
* @param    $title      string      comment title
* @param    $comment    string      text of the comment
* @param    $uid        int         user id
* @param    $ipaddress  string      poster's IP address
* @param    $type       string      type of comment ('article', 'poll', ...)
* @param    $cid        int         comment id
*
*/
function CMT_sendNotification ($title, $comment, $uid, $ipaddress, $type, $cid, $queued = 0)
{
    global $_CONF, $_TABLES, $LANG03, $LANG08, $LANG09;

    // strip HTML if posted in HTML mode
    if (preg_match ('/<.*>/', $comment) != 0) {
        $comment = strip_tags ($comment);
    }
    $comment = str_replace('&nbsp;',' ', $comment);

// need to handle anonymous names here
    if ( $uid > 1 ) {
        $author = COM_getDisplayName ($uid);
    } else {
        $author = DB_getItem($_TABLES['comments'],'name','cid='.(int) $cid);
        if ( empty($author) ) $author = $LANG03[24];
    }
    if (($uid <= 1) && !empty ($ipaddress)) {
        // add IP address for anonymous posters
        $author .= ' (' . $ipaddress . ')';
    }

    $html2txt  = new Html2Text\Html2Text($title,false);

    $mailbody = "$LANG03[16]: " . $html2txt->get_text() /* html_entity_decode($title)*/ ."\n"
              . "$LANG03[5]: $author\n";

    if (($type != 'article') && ($type != 'poll')) {
        $mailbody .= "$LANG09[5]: $type\n";
    }

    if ($_CONF['emailstorieslength'] > 0) {
        if ($_CONF['emailstorieslength'] > 1) {
            $comment = MBYTE_substr ($comment, 0, $_CONF['emailstorieslength'])
                     . '...';
        }
        $mailbody .= html_entity_decode($comment) . "\n\n";
    }

    if ( $queued == 0 ) {
        $mailbody .= $LANG08[33] . ' ' . $_CONF['site_url']
                  . '/comment.php?mode=view&cid=' . $cid . "\n\n";
        $mailbody .= "\n------------------------------\n";
        $mailbody .= "$LANG08[34]";
        $mailbody .= "\n------------------------------\n";

        $mailsubject = $_CONF['site_name'] . ' ' . $LANG03[9];

        $to = array();
        $to = COM_formatEmailAddress( '',$_CONF['site_mail'] );
        COM_mail ($to, $mailsubject, $mailbody);
    } else {
        $html2txt  = new Html2Text\Html2Text($title,false);
        $mailbody = $LANG03[53].'<br><br>';
        $mailbody .= $LANG03[16].': '. $html2txt->get_text().'<br>';
        $mailbody .= $LANG03[5].': '.$author.'<br><br>';
        $mailbody .= nl2br($comment) . '<br><br>';
        $mailbody .= sprintf($LANG03[54].'<br>',$_CONF['site_admin_url'].'/moderation.php');

        $html2txt  = new Html2Text\Html2Text($mailbody,false);
        $mailbody_text = trim($html2txt->get_text());

        $commentadmin_grp_id = DB_getItem($_TABLES['groups'],'grp_id','grp_name="Comment Admin"');
        if ( $commentadmin_grp_id === NULL ) return;
        $groups = SEC_getGroupList($commentadmin_grp_id);
        $groupList = implode(',',$groups);
	    $sql = "SELECT DISTINCT {$_TABLES['users']}.uid,username,fullname,email "
	          ."FROM {$_TABLES['group_assignments']},{$_TABLES['users']} "
	          ."WHERE {$_TABLES['users']}.uid > 1 "
	          ."AND {$_TABLES['users']}.uid = {$_TABLES['group_assignments']}.ug_uid "
	          ."AND ({$_TABLES['group_assignments']}.ug_main_grp_id IN (".$groupList."))";
        $result = DB_query($sql);
        $nRows = DB_numRows($result);
        $toCount = 0;
        $to = array();
        $msgData = array();
        for ($i=0;$i < $nRows; $i++ ) {
            $row = DB_fetchArray($result);
            if ( $row['email'] != '' ) {
                $toCount++;
                $to[] = array('email' => $row['email'], 'name' => $row['username']);
            }
        }
        if ( $toCount > 0 ) {
            $msgData['htmlmessage'] = $mailbody;
            $msgData['textmessage'] = $mailbody_text;
            $msgData['subject'] = $LANG03[55];
            $msgData['from']['email'] = $_CONF['noreply_mail'];
            $msgData['from']['name'] = $_CONF['site_name'];
            $msgData['to'] = $to;
            COM_emailNotification( $msgData );
            return;
    	} else {
        	COM_errorLog("CMT Notification: Error - Did not find any moderators to email");
    	}
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
    global $_CONF, $_TABLES, $_USER;

    $ret = 0;  // Assume good status unless reported otherwise

    // Sanity check, note we return immediately here and no DB operations
    // are performed
    if (!is_numeric ($cid) || ($cid < 0) || empty ($sid) || empty ($type)) {
        COM_errorLog("CMT_deleteComment: {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to delete a comment with one or more missing/bad values.');
        return $ret = 1;
    }

    // Delete the comment from the DB and update the other comments to
    // maintain the tree structure
    // A lock is needed here to prevent other additions and/or deletions
    // from happening at the same time. A transaction would work better,
    // but aren't supported with MyISAM tables.
    DB_lockTable ($_TABLES['comments']);
    $result = DB_query("SELECT pid, lft, rht FROM {$_TABLES['comments']} "
                     . "WHERE cid = ".(int) $cid." AND sid = '".DB_escapeString($sid)."' AND type = '".DB_escapeString($type)."'");
    if ( DB_numRows($result) == 1 ) {
        list($pid,$lft,$rht) = DB_fetchArray($result);
        DB_change ($_TABLES['comments'], 'pid', (int) $pid, 'pid', (int) $cid);
        DB_delete ($_TABLES['comments'], 'cid', (int) $cid);
        DB_query("UPDATE {$_TABLES['comments']} SET indent = indent - 1 "
           . "WHERE sid = '".DB_escapeString($sid)."' AND type = '".DB_escapeString($type)."' AND lft BETWEEN $lft AND $rht");
        DB_query("UPDATE {$_TABLES['comments']} SET lft = lft - 2 "
           . "WHERE sid = '".DB_escapeString($sid)."' AND type = '".DB_escapeString($type)."'  AND lft >= $rht");
        DB_query("UPDATE {$_TABLES['comments']} SET rht = rht - 2 "
           . "WHERE sid = '".DB_escapeString($sid)."' AND type = '".DB_escapeString($type)."'  AND rht >= $rht");
    } else {
        COM_errorLog("CMT_deleteComment: {$_USER['uid']} from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to delete a comment that doesn\'t exist as described.');
        return $ret = 2;
    }

    DB_unlockTable ($_TABLES['comments']);
    PLG_itemDeleted((int) $cid, 'comment');

    CACHE_remove_instance('whatsnew');
    CACHE_remove_instance('story_');

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
        $retval .= COM_showMessageText($LANG12[30].$last.$LANG12[31], $LANG12[26],false,'error');
        return $retval;
    }

    $start = new Template($_CONF['path_layout'] . 'comment');
    $start->set_file(array('report' => 'reportcomment.thtml'));
    $start->set_var('lang_report_this', $LANG03[25]);
    $start->set_var('lang_send_report', $LANG03[10]);
    $start->set_var('cid', $cid);
    $start->set_var('type', $type);
    $start->set_var('gltoken_name', CSRF_TOKEN);
    $start->set_var('gltoken', SEC_createToken());

    $result = DB_query ("SELECT uid,sid,pid,title,comment,UNIX_TIMESTAMP(date) AS nice_date FROM {$_TABLES['comments']} WHERE cid = ".(int) $cid." AND type = '".DB_escapeString($type)."'");
    $A = DB_fetchArray ($result);

    $result = DB_query ("SELECT username,fullname,photo,email FROM {$_TABLES['users']} WHERE uid = ".(int) $A['uid']);
    $B = DB_fetchArray ($result);

    // prepare data for comment preview
    $A['cid'] = $cid;
    $A['type'] = $type;
    $A['username'] = $B['username'];
    $A['fullname'] = $B['fullname'];
    $A['photo'] = $B['photo'];
    $A['email'] = $B['email'];
    $A['indent'] = 0;
    $A['pindent'] = 0;

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

    $username = DB_getItem ($_TABLES['users'], 'username',
                            "uid = {$_USER['uid']}");
    $result = DB_query ("SELECT uid,title,comment,sid,ipaddress FROM {$_TABLES['comments']} WHERE cid = ".(int) $cid." AND type = '".DB_escapeString($type)."'");
    $A = DB_fetchArray ($result);

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

    global $_USER, $_TABLES, $LANG03, $_CONF;

    $filter = sanitizer::getInstance();
    $filter->setPostmode($postmode);
    $filter->setCensorData(true);
    $filter->setNamespace('glfusion','comment');
    $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
    $filter->setAllowedElements($AllowedElements);
    $comment = $filter->filterData($comment);  // does not censor...
    $comment = $filter->censor($comment);

    if (COM_isAnonUser()) {
        $uid = 1;
    } elseif ($edit && is_numeric($cid) ){
        //if comment moderator
        $uid = DB_getItem ($_TABLES['comments'], 'uid', "cid = ".(int) $cid);
    } else {
        $uid = $_USER['uid'];
    }

    return $comment;
}

function CMT_preview( $data )
{
    global $_CONF, $_TABLES,$_USER,$LANG03;

    $retval = '';
    $mode = 'preview_edit';
    $start = new Template( $_CONF['path_layout'] . 'comment' );
    $start->set_file( array( 'comment' => 'startcomment.thtml' ));
    $start->set_var( 'hide_if_preview', 'style="display:none"' );

    // Clean up all the vars
    $A = array();
    foreach ($data as $key => $value) {
        if (($key == 'pid') || ($key == 'cid')) {
            $A[$key] = (int) COM_applyFilter ($data[$key], true);
        } else if (($key == 'title') || ($key == 'comment')) {
            // these have already been filtered above
            $A[$key] = $data[$key];
        } else if ($key == 'username') {
            $A[$key] = @htmlspecialchars(strip_tags(trim(COM_checkWords(USER_sanitizeName($data[$key])))),ENT_QUOTES,COM_getEncodingt());
//            $A[$key] = @htmlspecialchars(COM_checkWords(strip_tags($data[$key])),ENT_QUOTES,COM_getEncodingt());
        } else {
            $A[$key] = COM_applyFilter($data[$key]);
        }
    }

    //correct time and username for edit preview
    if ($mode == 'preview' || $mode == 'preview_new' || $mode == 'preview_edit') {
        $A['nice_date'] = DB_getItem ($_TABLES['comments'],'UNIX_TIMESTAMP(date)', "cid = ".(int) $data['cid']);
    }
    if (empty ($A['username'])) {
        $A['username'] = DB_getItem ($_TABLES['users'], 'username',"uid = ".(int) $data['uid']);
    }

    $author_id = PLG_getItemInfo($data['type'], $data['sid'], 'author');

    $thecomments = CMT_getComment ($A, 'flat', $data['type'], 'ASC', false, true,0,$author_id);

    $start->set_var( 'comments', $thecomments );
    $retval .= '<a name="comment_entry"></a>';
    $retval .= COM_startBlock ($LANG03[14])
            . $start->finish( $start->parse( 'output', 'comment' ))
            . COM_endBlock ();

    return $retval;
}


/**
 * article: saves a comment
 *
 * @param   string  $title  comment title
 * @param   string  $comment comment text
 * @param   string  $id     Item id to which $cid belongs
 * @param   int     $pid    comment parent
 * @param   string  $postmode 'html' or 'text'
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function plugin_savecomment_article($title, $comment, $id, $pid, $postmode)
{
    global $_CONF, $_TABLES, $LANG03, $_USER;

    $retval = '';

    $commentcode = DB_getItem($_TABLES['stories'], 'commentcode',
                "(sid = '".DB_escapeString($id)."') AND (draft_flag = 0) AND (date <= '".$_CONF['_now']->toMySQL(true)."')"
                . COM_getPermSQL('AND'));
    if (!isset($commentcode) || ($commentcode != 0)) {
        return COM_refresh($_CONF['site_url'] . '/index.php');
    }

    $ret = CMT_saveComment($title, $comment, $id, $pid, 'article', $postmode);
    if ($ret > 0) { // failure
        $msg = '';
        if ( SESS_isSet('glfusion.commentpresave.error') ) {
            $msg = COM_showMessageText(SESS_getVar('glfusion.commentpresave.error'),'',1,'error');
            SESS_unSet('glfusion.commentpresave.error');
        } else {
            if ( empty($comment) ) {
                $msg = COM_showMessageText($LANG03[12],'',1,'error');
            }
        }
        $retval .= $msg . CMT_commentForm ($title,$comment,$id,$pid,'article',$LANG03[14],$postmode);

    } else { // success
        $comments = CMT_getCount('article',$id); // DB_count($_TABLES['comments'], array('type', 'sid','queued'), array('article', $id,0));
        DB_change($_TABLES['stories'], 'comments', $comments, 'sid', $id);
        COM_olderStuff(); // update comment count in Older Stories block
        $retval = COM_refresh(COM_buildUrl($_CONF['site_url']
                              . "/article.php?story=$id#comments"));
    }

    return $retval;
}


/**
 * article: delete a comment
 *
 * @param   int     $cid    Comment to be deleted
 * @param   string  $id     Item id to which $cid belongs
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function plugin_deletecomment_article($cid, $id)
{
    global $_CONF, $_TABLES, $_USER;

    $retval = '';

    $has_editPermissions = SEC_hasRights ('story.edit');
    $result = DB_query ("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon "
                      . "FROM {$_TABLES['stories']} WHERE sid = '".DB_escapeString($id)."'");
    $A = DB_fetchArray ($result);

    if ($has_editPermissions && SEC_hasAccess ($A['owner_id'],
            $A['group_id'], $A['perm_owner'], $A['perm_group'],
            $A['perm_members'], $A['perm_anon']) == 3) {
        CMT_deleteComment($cid, $id, 'article');
        $comments = DB_count ($_TABLES['comments'], array('sid','queued'), array(DB_escapeString($id),0));
        DB_change ($_TABLES['stories'], 'comments', $comments, 'sid', DB_escapeString($id));
        CACHE_remove_instance('whatsnew');
        $retval .= COM_refresh(COM_buildUrl($_CONF['site_url']
                 . "/article.php?story=$id") . '#comments');
    } else {
        COM_errorLog ("User {$_USER['username']} "
                    . "did not have permissions to delete comment $cid from $id");
        $retval .= COM_refresh ($_CONF['site_url'] . '/index.php');
    }

    return $retval;
}


/**
 * article: display comment(s)
 *
 * @param   string  $id     Unique idenifier for item comment belongs to
 * @param   int     $cid    Comment id to display (possibly including sub-comments)
 * @param   string  $title  Page/comment title
 * @param   string  $order  'ASC' or 'DESC' or blank
 * @param   string  $format 'threaded', 'nested', or 'flat'
 * @param   int     $page   Page number of comments to display
 * @param   boolean $view   True to view comment (by cid), false to display (by $pid)
 * @return  mixed   results of calling the plugin_displaycomment_ function
*/
function plugin_displaycomment_article($id, $cid, $title, $order, $format, $page, $view)
{
    global $_CONF, $_TABLES, $LANG_ACCESS;

    USES_lib_story();

    $retval = '';
    // display story
    $sql   = "SELECT s.*, UNIX_TIMESTAMP(s.date) AS unixdate, "
             . 'UNIX_TIMESTAMP(s.expire) as expireunix, '
             . "u.uid, u.username, u.fullname, t.topic, t.imageurl "
             . "FROM {$_TABLES['stories']} AS s LEFT JOIN {$_TABLES['users']} AS u ON s.uid=u.uid "
             . "LEFT JOIN {$_TABLES['topics']} AS t on s.tid=t.tid "
             . "WHERE (sid = '".DB_escapeString($id)."') "
             . 'AND (draft_flag = 0) AND (date <= "'.$_CONF['_now']->toMySQL(true).'")' . COM_getPermSQL('AND',0,2, 's')
             . COM_getTopicSQL('AND',0,'t') . ' GROUP BY sid,owner_id, group_id, perm_owner, s.perm_group,s.perm_members, s.perm_anon ';


    $result = DB_query ($sql);

    $nrows = DB_numRows ($result);
    if ( $A = DB_fetchArray( $result ) ) {

        $story = new Story();
        $story->loadFromArray($A);
        $retval .= STORY_renderArticle ($story, 'n');
    }
    // end
    $sql = 'SELECT COUNT(*) AS count, commentcode, uid, owner_id, group_id, perm_owner, perm_group, '
         . "perm_members, perm_anon FROM {$_TABLES['stories']} "
         . "WHERE (sid = '".DB_escapeString($id)."') "
         . 'AND (draft_flag = 0) AND (date <= "'.$_CONF['_now']->toMySQL(true).'")' . COM_getPermSQL('AND')
         . COM_getTopicSQL('AND') . ' GROUP BY sid,owner_id, group_id, perm_owner, perm_group,perm_members, perm_anon ';

    $result = DB_query ($sql);
    $B = DB_fetchArray ($result);
    $allowed = $B['count'];

    if ( $allowed == 1 ) {
        $delete_option = ( SEC_hasRights( 'story.edit' ) &&
            ( SEC_hasAccess( $B['owner_id'], $B['group_id'],
                $B['perm_owner'], $B['perm_group'], $B['perm_members'],
                $B['perm_anon'] ) == 3 ) );
        $retval .= CMT_userComments ($id, $title, 'article', $order,
                        $format, $cid, $page, $view, $delete_option,
                        $B['commentcode'],$B['uid']);

    } else {
        return false;
    }
    return $retval;
}
function plugin_getcommenturlid_article( )
{
    global $_CONF;
    $retval = array();
    $retval[] = $_CONF['site_url'] . '/article.php';
    $retval[] = 'story';
    $retval[] = 'page=';
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
        $where = " WHERE cid = '" . DB_escapeString($id) . "'";
        $permOp = ' AND ';
    }

    $groups = array ();
    $usergroups = SEC_getUserGroups();
    foreach ($usergroups as $group) {
        $groups[] = $group;
    }
    $grouplist = implode(',',$groups);

    $sql  = "SELECT " . implode(',', $fields) . " ";
    $sql .= "FROM {$_TABLES['comments']} ";
    $sql .= $where;

    if ($id != '*') {
        $sql .= ' LIMIT 1';
    }

    $result = DB_query($sql);
    $numRows = DB_numRows($result);

    $retval = array();
    for ($i = 0; $i < $numRows; $i++) {
        $A = DB_fetchArray($result);

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
                        $sql = "SELECT GROUP_CONCAT(comment SEPARATOR ' ') as comment FROM {$_TABLES['comments']} WHERE pid=".$id." GROUP BY pid";
                        $childres = DB_query($sql);
                        if ( DB_numRows($childres) > 0 ) {
                            $B = DB_fetchArray($childres);
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
    global $_CONF, $_TABLES, $_USER, $LANG_TSTM01;
    global $LANG01, $LANG24, $LANG29, $LANG_ADMIN, $_IMAGE_TYPE;

    $retval = '';
    $key='cid';

    if ( COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $sql = "SELECT *,UNIX_TIMESTAMP(date) AS day, name AS username FROM {$_TABLES['comments']} WHERE queued = 1 ORDER BY date DESC";

    $result = DB_query($sql);
    $nrows = DB_numRows($result);

    if ( $nrows == 0 ) return;

    $data_arr = array();
    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray($result);
        $A['edit'] = $_CONF['site_url'].'/comment.php?mode=modedit&amp;cid='.$A['cid'].'&amp;'.CSRF_TOKEN.'='.$token;
        $A['_type_']    = 'comment';
        $A['_key_']     = 'cid';        // name of key/id field
        $A['preview']   = CMT_preview($A); // format a comment for preview.
        $A['username']  = $A['name'];
        $data_arr[$i]   = $A;           // push row data into array
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

    $actions = '<input name="approve" type="image" src="'
        . $_CONF['layout_url'] . '/images/admin/accept.' . $_IMAGE_TYPE
        . '" style="vertical-align:bottom;" title="' . $LANG29[44]
        . '" onclick="return confirm(\'' . $LANG29[45] . '\');"'
        . '/>&nbsp;' . $LANG29[1];
    $actions .= '&nbsp;&nbsp;&nbsp;&nbsp;';
    $actions .= '<input name="delbutton" type="image" src="'
        . $_CONF['layout_url'] . '/images/admin/delete.' . $_IMAGE_TYPE
        . '" style="vertical-align:text-bottom;" title="' . $LANG01[124]
        . '" onclick="return confirm(\'' . $LANG01[125] . '\');"'
        . '/>&nbsp;' . $LANG_ADMIN['delete'];

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
            . '<input type="hidden" name="count" value="' . $nrows . '"/>';

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

    if ( (int) $id <= 0 ) return '';

    $sql = "UPDATE {$_TABLES['comments']} SET queued=0 WHERE cid=".(int) $id;
    DB_query($sql);

    // now we need to determine the type of comment and call all the stuff we need
    $result = DB_query("SELECT * FROM {$_TABLES['comments']} WHERE cid=".(int) $id);
    if ( ( DB_numRows( $result) ) != 1 ) {
        COM_errorLog("Unable to retrieve approved comment");
        return false;
    }
    $row = DB_fetchArray($result);
    $cid = $id;
    $type = $row['type'];
    $sid  = $row['sid'];
    // now we need to alert everyone a comment has been saved.
    PLG_commentApproved($cid,$type,$sid);   // let plugins know they should update their counts if necessary
    CACHE_remove_instance('menu');
    CACHE_remove_instance('whatsnew');
    if ( $type == 'article' ) {
        CACHE_remove_instance('story_'.$sid);
    }
    PLG_itemSaved($cid, 'comment');     // let others know we saved a comment to the prod table

    COM_setMsg($LANG03[56],'warning');

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

    if ( (int) $id <= 0 ) return '';

    $sql = "DELETE FROM {$_TABLES['comments']} WHERE cid=".(int) $id . " AND queued=1";
    DB_query($sql);
    CACHE_remove_instance('menu'); // update menus to reflect new queued counts
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

    $retval = 0;

    $retval = DB_count ($_TABLES['comments'],'queued',1);

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
    $field = ($type == 'user' && $fieldname == 1) ? 'user' : $field;
    $field = ($type == 'story' && $fieldname == 2) ? 'day' : $field;
    $field = ($type == 'story' && $fieldname == 3) ? 'tid' : $field;
    $field = ($type == 'user' && $fieldname == 3) ? 'email' : $field;
    $field = ($type <> 'user' && $fieldname == 4) ? 'uid' : $field;
    $field = ($type == 'user' && $fieldname == 4) ? 'day' : $field;

    switch ($field) {
        case 'edit':
            $retval = COM_createLink($icon_arr['edit'], $A['edit']);
            break;

        case 'title' :
            $retval =  html_entity_decode(htmlspecialchars_decode($fieldvalue));
            break;

        case 'user':
            $retval =  '<img src="' . $_CONF['layout_url']
            . '/images/admin/user.' . $_IMAGE_TYPE
            . '" style="vertical-align:bottom;"/>&nbsp;' . $fieldvalue;
            break;

        case 'day':
            $dt->setTimeStamp($A['day']);
            $retval = $dt->format($_CONF['daytime'],true);
            break;

        case 'tid':
            $retval = DB_getItem($_TABLES['topics'], 'topic',
                                 "tid = '".DB_escapeString($A['tid'])."'");
            break;

        case 'uid':
            if ( !isset($A['uid']) ) {
                $A['uid'] = 1;
            }
            if ( $A['uid'] == 1 ) {

                if ( empty($A['name']) ) $A['name'] = $LANG03[24];

                $retval = $icon_arr['greyuser']
                            . '&nbsp;&nbsp;'
                            . '<span style="vertical-align:top">' . $A['name'] . '</span>';
            } else {
                // lookup the username from the uid
                $username = DB_getItem($_TABLES['users'], 'username',"uid = ". (int) $A['uid']);

                $attr['title'] = $LANG28[108];
                $url = $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' .  $A['uid'];
                $retval = COM_createLink($icon_arr['user'], $url, $attr);
                $retval .= '&nbsp;&nbsp;';
                $attr['style'] = 'vertical-align:top;';
                $retval .= COM_createLink($username, $url, $attr);
            }
            break;

        case 'email':
            $url = 'mailto:' . $fieldvalue;
            $attr['title'] = $LANG28[111];
            $retval = COM_createLink($icon_arr['mail'], $url, $attr);
            $retval .= '&nbsp;&nbsp;';
            $attr['title'] = $LANG28[99];
            $url = $_CONF['site_admin_url'] . '/mail.php?uid=' . $A['uid'];
            $attr['style'] = 'vertical-align:top;';
            $retval .= COM_createLink($fieldvalue, $url, $attr);
            break;

        case 'approve':
            $retval = '';
            $attr['title'] = $LANG29[1];
            $attr['onclick'] = 'return confirm(\'' . $LANG29[48] . '\');';
            $retval .= COM_createLink($icon_arr['accept'],
                $_CONF['site_admin_url'] . '/moderation.php'
                . '?approve=x'
                . '&amp;type=' . $A['_type_']
                . '&amp;id=' . $A[0]
                . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            break;

        case 'delete':
            $retval = '';
            $attr['title'] = $LANG_ADMIN['delete'];
            $attr['onclick'] = 'return confirm(\'' . $LANG29[49] . '\');';
            $retval .= COM_createLink($icon_arr['delete'],
                $_CONF['site_admin_url'] . '/moderation.php'
                . '?delete=x'
                . '&amp;type=' . $A['_type_']
                . '&amp;id=' . $A[0]
                . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
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


function plugin_privacy_export_comment($uid,$email='',$username='',$ip='')
{
    global $_CONF, $_TABLES;

    $retval = '';

    $exportFields = array('type','sid','date','title','name','uid','ipaddress');

    $sql = "SELECT * FROM {$_TABLES['comments']} WHERE uid = ". (int) $uid;
    if ( $ip != '' ) {
        $sql .= " OR ipaddress = '" . DB_escapeString($ip)."'";
    }
    $sql .= " ORDER BY date ASC";

    $result = DB_query($sql);
    $rows = DB_fetchAll($result);

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
