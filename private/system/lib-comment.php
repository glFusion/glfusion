<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-comment.php                                                          |
// |                                                                          |
// | glFusion comment library.                                                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2016 by the following authors:                        |
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
    global $_CONF, $_USER, $_TABLES, $LANG01, $LANG03;

    $dt = new Date('now',$_USER['tzid']);
    $permalink = 'Not defined';

    $filter = sanitizer::getInstance();
    $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
    $filter->setAllowedelements($AllowedElements);
    $filter->setNamespace('glfusion','comment');

    $post_id = COM_applyFilter($post_id,true);
    $result = DB_query("SELECT * FROM {$_TABLES['comments']} WHERE cid={$post_id}");
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
        $A['title']   = @htmlspecialchars($A['title'],ENT_QUOTES, COM_getEncodingt());

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

        $html2txt = new html2text($msgText,false);

        $messageText = $html2txt->get_text();
        return array($message,$messageText,array());
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

    $nrows = DB_count( $_TABLES['comments'], array( 'sid', 'type' ),
                       array( DB_escapeString($sid), DB_escapeString($type) ));

    $commentbar = new Template( $_CONF['path_layout'] . 'comment' );
    $commentbar->set_file( array( 'commentbar' => 'commentbar.thtml' ));
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
        $commentbar->set_var( 'comment_option_text', $LANG03[50] );
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
        $articleUrl = COM_buildUrl( $_CONF['site_url']
                                    . "/article.php?story=$sid" );
        $commentbar->set_var( 'story_link', $articleUrl );
        $commentbar->set_var( 'article_url', $articleUrl );

        if( $page == 'comment.php' ) {
            $commentbar->set_var('story_link',
                COM_createLink(
                    @htmlspecialchars($title,ENT_COMPAT,COM_getEncodingt()),
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
            $A['comment'] .= LB . '<div class="comment-edit">' . $LANG03[30] . ' '
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

        $template->set_var( 'indent', $indent );
        $template->set_var( 'author_name', $filter->sanitizeUsername($A['username'] ));
        $template->set_var( 'author_id', $A['uid'] );
        $template->set_var( 'cid', $A['cid'] );
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

        } else {
            $username = $filter->sanitizeUsername($A['name']);
            if ( $username == '' ) {
                $username = $LANG01[24];
            }
            $template->set_var( 'author', $username);
            $template->set_var( 'author_fullname', $username);
            $template->set_var( 'author_link', @htmlspecialchars($username,ENT_COMPAT,COM_getEncodingt() ));

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
        $template->set_var( 'sid', $A['sid'] );
        $template->set_var( 'type', $A['type'] );

        //COMMENT edit rights
        if ( !COM_isAnonUser() ) {
            if ( $_USER['uid'] == $A['uid'] && $_CONF['comment_edit'] == 1
                    && ($_CONF['comment_edittime'] == 0 || ((time() - $A['nice_date']) < $_CONF['comment_edittime'] )) &&
                    $ccode == 0 &&
                    DB_getItem($_TABLES['comments'], 'COUNT(*)', "pid = ".(int) $A['cid']) == 0) {
                $edit_option = true;
            } else if (SEC_inGroup('Root') ) {
                $edit_option = true;
            } else {
                $edit_option = false;
            }
        } else {
            $edit_option = false;
        }

        //edit link
        if ($edit_option) {
            if ( empty($token)) {
                $token = SEC_createToken();
            }
            $editlink = $_CONF['site_url'] . '/comment.php?mode=edit&amp;cid='
                . $A['cid'] . '&amp;sid=' . $A['sid'] . '&amp;type=' . $type
                . '&amp;' . CSRF_TOKEN . '=' . $token . '#comment_entry';
            $template->set_var('edit_link',$editlink);
            $template->set_var('lang_edit',$LANG01[4]);
            $edit = COM_createLink( $LANG01[4], $editlink) . ' | ';
        } else {
            $editlink = '';
            $edit = '';
        }

        // If deletion is allowed, displays delete link
        if( $delete_option ) {
            $deloption = '';
            if ( SEC_inGroup('Root') ) {
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
        $text = str_replace('</div><!-- /COMMENTEDIT -->', '', $text);

        $filter->setReplaceTags(true);
        $filter->setCensorData(true);

        if( preg_match( '/<.*>/', $text ) == 0 ) {
            $A['comment'] = nl2br( $A['comment'] );
        }
        $filter->setPostmode('html');
        $A['comment'] = $filter->displayText($A['comment']);

        // highlight search terms if specified
        if( !empty( $_REQUEST['query'] )) {
            $A['comment'] = COM_highlightQuery( $A['comment'],
                                                strip_tags($_REQUEST['query']) );
        }

        if (function_exists('msg_replaceEmoticons'))  {
            $A['comment'] = msg_replaceEmoticons($A['comment']);
        }

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
        $template->set_var( 'comments', $A['comment'] );

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
    global $_CONF, $_TABLES, $_USER, $LANG01;

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
            <div id="disqus_thread"></div>
            <script>
                var disqus_config = function () {
                    this.page.url = \''.$pageURL.'\';
                    this.page.identifier = \''.$pageIdentifier.'\';
                    this.page.title = \''.$pageTitle.'\';
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

            $retval = '<div class="fb-comments" data-href="'.$pageURL.'" data-numposts="20"></div>';
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
                               . "WHERE c.uid = u.uid AND c.cid = ".(int) $pid." AND type='".DB_escapeString($type)."'";
                        } else {
                            $count = DB_count( $_TABLES['comments'],
                                        array( 'sid', 'type' ), array( DB_escapeString($sid), DB_escapeString($type) ));

                            $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, "
                               . "UNIX_TIMESTAMP(c.date) AS nice_date "
                               . "FROM {$_TABLES['comments']} AS c, {$_TABLES['users']} AS u "
                               . "WHERE c.uid = u.uid AND c.sid = '".DB_escapeString($sid)."' AND type='".DB_escapeString($type)."' "
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
                                . "WHERE c.sid = '".DB_escapeString($sid)."' AND (c.lft >= c2.lft AND c.lft <= c2.rht) "
                                . "AND c2.cid = ".(int) $pid." AND c.type='".DB_escapeString($type)."'";
                            $result = DB_query( $q2 );
                            list( $count ) = DB_fetchArray( $result );

                            $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, c2.indent AS pindent, "
                               . "UNIX_TIMESTAMP(c.date) AS nice_date "
                               . "FROM {$_TABLES['comments']} AS c, {$_TABLES['comments']} AS c2, "
                               . "{$_TABLES['users']} AS u "
                               . "WHERE c.sid = '".DB_escapeString($sid)."' AND (c.lft >= c2.lft AND c.lft <= c2.rht) "
                               . "AND c2.cid = ".(int) $pid." AND c.uid = u.uid AND c.type='".DB_escapeString($type)."' "
                               . "ORDER BY $cOrder LIMIT $start, $limit";
                        } else {    // pid refers to parentid rather than commentid
                            if( $pid == 0 ) {  // the simple, fast case
                                // count the total number of applicable comments
                                $count = DB_count( $_TABLES['comments'],
                                        array( 'sid', 'type' ), array( DB_escapeString($sid), DB_escapeString($type) ));

                                $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, 0 AS pindent, "
                                   . "UNIX_TIMESTAMP(c.date) AS nice_date "
                                   . "FROM {$_TABLES['comments']} AS c, {$_TABLES['users']} AS u "
                                   . "WHERE c.sid = '".DB_escapeString($sid)."' AND c.uid = u.uid  AND type='".DB_escapeString($type)."' "
                                   . "ORDER BY $cOrder LIMIT $start, $limit";
                            } else {
                                // count the total number of applicable comments
                                $q2 = "SELECT COUNT(*) "
                                    . "FROM {$_TABLES['comments']} AS c, {$_TABLES['comments']} AS c2 "
                                    . "WHERE c.sid = '".DB_escapeString($sid)."' AND (c.lft > c2.lft AND c.lft < c2.rht) "
                                    . "AND c2.cid = ".(int) $pid." AND c.type='".DB_escapeString($type)."'";
                                $result = DB_query($q2);
                                list($count) = DB_fetchArray($result);

                                $q = "SELECT c.*, u.username, u.fullname, u.photo, u.email, c2.indent + 1 AS pindent, "
                                   . "UNIX_TIMESTAMP(c.date) AS nice_date "
                                   . "FROM {$_TABLES['comments']} AS c, {$_TABLES['comments']} AS c2, "
                                   . "{$_TABLES['users']} AS u "
                                   . "WHERE c.sid = '".DB_escapeString($sid)."' AND (c.lft > c2.lft AND c.lft < c2.rht) "
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
function CMT_commentForm($title,$comment,$sid,$pid='0',$type,$mode,$postmode)
{
    global $_CONF, $_TABLES, $_USER, $LANG03, $LANG12, $LANG_LOGIN, $LANG_ACCESS;

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
        $commentuid = DB_getItem ($_TABLES['comments'], 'uid', "cid = ".(int) $cid);
    }

    if (COM_isAnonUser() &&
        (($_CONF['loginrequired'] == 1) || ($_CONF['commentsloginrequired'] == 1))) {
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
            $retval .= COM_showMessageText($LANG03[7].$last.$LANG03[8],$LANG12[26],false);
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
            $title = $filter->editableText($title);
            $filter->setPostmode($postmode);

            $_POST['title']     = $title;
            $_POST['comment']   = $display_comment;

            // Preview mode:
            if (($mode == $LANG03[14] || $mode == 'preview' || $mode == 'preview_new' || $mode == 'preview_edit') && !empty($title) && !empty($comment) ) {
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
                    } else {
                        $A[$key] = COM_applyFilter($_POST[$key]);
                    }
                }

                //correct time and username for edit preview
                if ($mode == 'preview' || $mode == 'preview_new' || $mode == 'preview_edit') {
                    $A['nice_date'] = DB_getItem ($_TABLES['comments'],
                                        'UNIX_TIMESTAMP(date)', "cid = ".(int) $cid);
                    if ($_USER['uid'] != $commentuid) {
                        $A['username'] = DB_getItem ($_TABLES['users'],
                                              'username', "uid = ".(int) $commentuid);
                    }
                }
                if (empty ($A['username'])) {
                    $A['username'] = DB_getItem ($_TABLES['users'], 'username',
                                                 "uid = ".(int) $uid);
                }

                $author_id = PLG_getItemInfo($type, $sid, 'author');

                $thecomments = CMT_getComment ($A, 'flat', $type, 'ASC', false, true,0,$author_id);

                $start->set_var( 'comments', $thecomments );
                $retval .= '<a name="comment_entry"></a>';
                $retval .= COM_startBlock ($LANG03[14])
                        . $start->finish( $start->parse( 'output', 'comment' ))
                        . COM_endBlock ();
            } else if ($mode == 'preview_new' || $mode == 'preview_edit') {
                $retval .= COM_showMessageText($LANG03[12],$LANG03[17],true);
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
                $comment_template->set_var('uid', $_USER['uid']);
                $name = COM_getDisplayName($_USER['uid'], $_USER['username'],$_USER['fullname']);
                $comment_template->set_var('username', $name);
                $comment_template->set_var('action_url',$_CONF['site_url'] . '/users.php?mode=logout');
                $comment_template->set_var('lang_logoutorcreateaccount',$LANG03[03]);
                $comment_template->set_var('username_disabled','disabled="disabled"');

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
            } else {
                //Anonymous user
                $comment_template->set_var('uid', 1);
                if ( isset($_POST['username']) ) {
                    $name = $filter->sanitizeUsername(COM_applyFilter($_POST['username'])); //for preview
                } else {
                    $name = $LANG03[24]; //anonymous user
                }
                $usernameblock = '<input type="text" name="username" size="16" value="' .
                                 $name . '" maxlength="32"/>';
                $comment_template->set_var('username', $name); // $usernameblock);

                $comment_template->set_var('action_url', $_CONF['site_url'] . '/users.php?mode=new');
                $comment_template->set_var('lang_logoutorcreateaccount',$LANG03[04]);
                $comment_template->set_var('username_disabled','');
            }

            if ( $postmode == 'html' ) {
                $comment_template->set_var('htmlmode',true);
            }
            $comment_template->set_var('lang_title', $LANG03[16]);
            $comment_template->set_var('title', @htmlspecialchars($title,ENT_COMPAT,COM_getEncodingt()));
            $comment_template->set_var('lang_comment', $LANG03[9]);
            $comment_template->set_var('comment', $edit_comment);
            $comment_template->set_var('lang_postmode', $LANG03[2]);
            $comment_template->set_var('postmode',$postmode);
            $comment_template->set_var('postmode_options', COM_optionList($_TABLES['postmodes'],'code,name',$postmode));
            $comment_template->set_var('allowed_html', $filter->getAllowedHTML() . '<br/>'. COM_AllowedAutotags('', false, 'glfusion','comment'));
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
    if (empty ($sid) || empty ($title) || empty ($comment) || empty ($type) ) {
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
    if (($uid == 1) && (($_CONF['loginrequired'] == 1)
            || ($_CONF['commentsloginrequired'] == 1))) {
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
    $result = PLG_checkforSpam ($spamcheck, $_CONF['spamx']);
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
    $title = COM_checkWords (strip_tags ($title));
    $comment = CMT_prepareText($comment,$postmode);

    // check for non-int pid's
    // this should just create a top level comment that is a reply to the original item
    if (!is_numeric($pid) || ($pid < 0)) {
        $pid = 0;
    }

    if (!empty ($title) && !empty ($comment)) {
        COM_updateSpeedlimit ('comment');
        $title = DB_escapeString ($title);
        $comment = DB_escapeString ($comment);
        $type = DB_escapeString($type);

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
                DB_save ($_TABLES['comments'], 'sid,uid,comment,date,title,pid,lft,rht,indent,type,ipaddress',
                        "'".DB_escapeString($sid)."',$uid,'$comment',now(),'$title',".(int) $pid.",$rht,$rht+1,$indent+1,'$type','".DB_escapeString($_SERVER['REMOTE_ADDR'])."'");
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
            DB_save ($_TABLES['comments'], 'sid,uid,comment,date,title,pid,lft,rht,indent,type,ipaddress',
                    "'".DB_escapeString($sid)."',".(int) $uid.",'$comment',now(),'$title',".(int) $pid.",$rht+1,$rht+2,0,'$type','".DB_escapeString($_SERVER['REMOTE_ADDR'])."'");
        }
        $cid = DB_insertId();
        //set Anonymous user name if present
        if (isset($_POST['username']) ) {
            $name = strip_tags(USER_sanitizeName ($_POST['username']));
            DB_change($_TABLES['comments'],'name',DB_escapeString($name),'cid',(int) $cid);
        }
        DB_unlockTable ($_TABLES['comments']);

        CACHE_remove_instance('whatsnew');
        if ($type == 'article') {
            CACHE_remove_instance('story_'.$sid);
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
        if (($ret == 0) && isset ($_CONF['notification']) &&
                in_array ('comment', $_CONF['notification'])) {
            CMT_sendNotification ($title, $comment, $uid, $_SERVER['REMOTE_ADDR'],
                              $type, $cid);
        }
        if ( $ret == 0 ) {
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
function CMT_sendNotification ($title, $comment, $uid, $ipaddress, $type, $cid)
{
    global $_CONF, $_TABLES, $LANG03, $LANG08, $LANG09;

    // we have to undo the DB_escapeString() call from savecomment()
    $title = stripslashes ($title);
    $comment = stripslashes ($comment);

    // strip HTML if posted in HTML mode
    if (preg_match ('/<.*>/', $comment) != 0) {
        $comment = strip_tags ($comment);
    }
    $comment = str_replace('&nbsp;',' ', $comment);

    $author = COM_getDisplayName ($uid);
    if (($uid <= 1) && !empty ($ipaddress)) {
        // add IP address for anonymous posters
        $author .= ' (' . $ipaddress . ')';
    }

    $mailbody = "$LANG03[16]: $title\n"
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

    $mailbody .= $LANG08[33] . ' ' . $_CONF['site_url']
              . '/comment.php?mode=view&cid=' . $cid . "\n\n";

    $mailbody .= "\n------------------------------\n";
    $mailbody .= "\n$LANG08[34]\n";
    $mailbody .= "\n------------------------------\n";

    $mailsubject = $_CONF['site_name'] . ' ' . $LANG03[9];

    $to = array();
    $to = COM_formatEmailAddress( '',$_CONF['site_mail'] );
    COM_mail ($to, $mailsubject, $mailbody);
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
        $retval .= COM_showMessageText($LANG12[30].$last.$LANG12[31], $LANG12[26],false);
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

    $sig = '';
    if ($uid > 1) {
        $sig = DB_getItem ($_TABLES['users'], 'sig', "uid = ".(int) $uid);
        if (!empty ($sig)) {
            $comment .= '<!-- COMMENTSIG --><div class="comment-sig">';
            if ( $postmode == 'html') {
                $comment .= nl2br(LB . '---' . LB . $sig);
            } else {
                $comment .=  nl2br(LB . '---' . LB . $sig);
            }
        $comment .= '</div><!-- /COMMENTSIG -->';
        }
    }

    return $comment;
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
                "(sid = '".DB_escapeString($id)."') AND (draft_flag = 0) AND (date <= NOW())"
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
            if ( empty($title) || empty($comment) ) {
                $msg = COM_showMessageText($LANG03[12],'',1,'error');
            }
        }
        $retval .= $msg . CMT_commentForm ($title,$comment,$id,$pid,'article',$LANG03[14],$postmode);

    } else { // success
        $comments = DB_count($_TABLES['comments'], array('type', 'sid'), array('article', $id));
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
        $comments = DB_count ($_TABLES['comments'], 'sid', DB_escapeString($id));
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
    USES_class_story();

    $retval = '';
    // display story
    $sql   = "SELECT s.*, UNIX_TIMESTAMP(s.date) AS unixdate, "
             . 'UNIX_TIMESTAMP(s.expire) as expireunix, '
             . "u.uid, u.username, u.fullname, t.topic, t.imageurl "
             . "FROM {$_TABLES['stories']} AS s LEFT JOIN {$_TABLES['users']} AS u ON s.uid=u.uid "
             . "LEFT JOIN {$_TABLES['topics']} AS t on s.tid=t.tid "
             . "WHERE (sid = '".DB_escapeString($id)."') "
             . 'AND (draft_flag = 0) AND (commentcode >= 0) AND (date <= NOW())' . COM_getPermSQL('AND',0,2, 's')
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
        $retval .= CMT_userComments ($id, $title, 'article', $order,
                        $format, $cid, $page, $view, $delete_option,
                        $B['commentcode'],$B['uid']);

    } else {
        $retval .= COM_showMessageText($LANG_ACCESS['storydenialmsg'], $LANG_ACCESS['accessdenied'], true);
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
?>