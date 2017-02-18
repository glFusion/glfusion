<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | profiles.php                                                             |
// |                                                                          |
// | This pages lets glFusion users communicate with each other without       |
// | exposing email addresses.                                                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2011-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans      - mark AT glfusion DOT org                            |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
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

require_once 'lib-common.php';

/**
* Mails the contents of the contact form to that user
*
* @param    int     $uid            User ID of person to send email to
* @param    string  $author         The name of the person sending the email
* @param    string  $authoremail    Email address of person sending the email
* @param    string  $subject        Subject of email
* @param    string  $message        Text of message to send
* @return   string                  Meta redirect or HTML for the contact form
*/
function contactemail($uid,$author,$authoremail,$subject,$message,$html=0)
{
    global $_CONF, $_TABLES, $_USER, $LANG04, $LANG08, $LANG_LOGIN, $MESSAGE;

    $retval = '';

    // check for correct $_CONF permission

    if ( COM_isAnonUser() ) {
        if ( !SEC_inGroup('Contact',(int)$uid) ) {
            if ( ( ( $_CONF['loginrequired'] == 1 ) || ($_CONF['emailuserloginrequired'] == 1 ) ) && $uid != 2 ) {
                $display  = COM_siteHeader('menu', $LANG_LOGIN[1]);
                $display .= SEC_loginRequiredForm();
                $display .= COM_siteFooter();
                echo $display;
                exit;
            }
        }
    }

    // check for correct 'to' user preferences
    $result = DB_query ("SELECT emailfromadmin,emailfromuser FROM {$_TABLES['userprefs']} WHERE uid = ".(int) $uid);
    $P = DB_fetchArray ($result);
    if (SEC_inGroup ('Root') || SEC_hasRights ('user.mail')) {
        $isAdmin = true;
    } else {
        $isAdmin = false;
    }
    if ((($P['emailfromadmin'] != 1) && $isAdmin) ||
        (($P['emailfromuser'] != 1) && !$isAdmin)) {
        return COM_refresh ($_CONF['site_url'] . '/index.php?msg=85');
    }

    // check mail speedlimit
    COM_clearSpeedlimit ($_CONF['speedlimit'], 'mail');
    if (COM_checkSpeedlimit ('mail') > 0) {
        return COM_refresh ($_CONF['site_url'] . '/index.php?msg=85');
    }

    if (!empty($author) && !empty($subject) && !empty($message)) {
        if (COM_isemail($authoremail)) {
            $result = DB_query("SELECT username,fullname,email FROM {$_TABLES['users']} WHERE uid = ".(int) $uid);
            $A = DB_fetchArray($result);

            // Append the user's signature to the message
            $sig = '';
            if (!COM_isAnonUser()) {
                $sig = DB_getItem($_TABLES['users'], 'sig',
                                  "uid={$_USER['uid']}");
                if (!empty ($sig)) {
                    $sig = strip_tags ($sig);
                    $sig = "\n\n-- \n" . $sig;
                }
            }

            $subject = COM_filterHTML($subject);
            $message = COM_filterHTML($message);

            // do a spam check with the unfiltered message text and subject
            $mailtext = $subject . "\n" . $message . $sig;
            $result = PLG_checkforSpam ($mailtext, $_CONF['spamx']);
            if ($result > 0) {
                COM_updateSpeedlimit ('mail');
                COM_displayMessageAndAbort ($result, 'spamx', 403, 'Forbidden');
            }

            $msg = PLG_itemPreSave ('contact', $message);
            if (!empty ($msg)) {
                $subject = @htmlspecialchars($subject,ENT_QUOTES,COM_getEncodingt());
                $retval .= COM_siteHeader ('menu', '')
                        . COM_errorLog ($msg, 2)
                        . contactform ($uid, $subject, $message)
                        . COM_siteFooter ();

                return $retval;
            }

            $subject = strip_tags ($subject);
            $subject = substr ($subject, 0, strcspn ($subject, "\r\n"));
            if ( $html ) {
                $message = $message . $sig;
            } else {
                $message = strip_tags ($message) . $sig;
            }
            $to = array();
            $from = array();

            if (!empty ($A['fullname'])) {
                $to = COM_formatEmailAddress ($A['fullname'], $A['email']);
            } else {
                $to = COM_formatEmailAddress ($A['username'], $A['email']);
            }
            $from = COM_formatEmailAddress ($author, $authoremail);

            $rc = COM_mail ($to, $subject, $message, $from,$html);
            COM_updateSpeedlimit ('mail');

            if ( COM_isAnonUser() && $_CONF['profileloginrequired'] == true) {
                $redirectURL = $_CONF['site_url'] . '/index.php';
            } else {
                $redirectURL = $_CONF['site_url']
                                . '/users.php?mode=profile&amp;uid=' . $uid;
            }

            if ( $rc === false ) {
                COM_setMsg( $MESSAGE[26], 'error' );
                $retval .= COM_refresh($redirectURL);
            } else {
                COM_setMsg( $MESSAGE[27], 'info' );
                $retval .= COM_refresh($redirectURL);
            }
        } else {
            $subject = strip_tags ($subject);
            $subject = substr ($subject, 0, strcspn ($subject, "\r\n"));
            $subject = @htmlspecialchars (trim ($subject), ENT_QUOTES,COM_getEncodingt());
            $retval .= COM_siteHeader ('menu', $LANG04[81])
                    . COM_errorLog ($LANG08[3], 2)
                    . contactform ($uid, $subject, $message)
                    . COM_siteFooter ();
        }
    } else {
        $subject = strip_tags ($subject);
        $subject = substr ($subject, 0, strcspn ($subject, "\r\n"));
        $subject = @htmlspecialchars (trim ($subject), ENT_QUOTES,COM_getEncodingt());
        $retval .= COM_siteHeader ('menu', $LANG04[81])
                . COM_errorLog ($LANG08[4], 2)
                . contactform ($uid, $subject, $message)
                . COM_siteFooter ();
    }

    return $retval;
}

/**
* Displays the contact form
*
* @param    int     $uid        User ID of article author
* @param    string  $subject    Subject of email
* @param    string  $message    Text of message to send
* @return   string              HTML for the contact form
*
*/
function contactform ($uid, $subject = '', $message = '')
{
    global $_CONF, $_TABLES, $_USER, $LANG03, $LANG08, $LANG_LOGIN;

    $retval = '';

    if ( COM_isAnonUser() ) {
        if ( !SEC_inGroup('Contact',(int)$uid) ) {
            if ( ( (  $_CONF['loginrequired'] == 1 ) || ($_CONF['emailuserloginrequired'] == 1 ) ) && $uid != 2 ) {
                $display  = COM_siteHeader('menu', $LANG_LOGIN[1]);
                $display .= SEC_loginRequiredForm();
                $display .= COM_siteFooter();
                echo $display;
                exit;
            }
        }
    }

    $result = DB_query ("SELECT emailfromadmin,emailfromuser FROM {$_TABLES['userprefs']} WHERE uid = ".(int) $uid);
    $P = DB_fetchArray ($result);
    if (SEC_inGroup ('Root') || SEC_hasRights ('user.mail')) {
        $isAdmin = true;
    } else {
        $isAdmin = false;
    }
    $postmode = $_CONF['mailuser_postmode'];

    $displayname = COM_getDisplayName ($uid);
    if ((($P['emailfromadmin'] == 1) && $isAdmin) || ($P['emailfromuser'] == 1) && !$isAdmin) {

        $token = SEC_createToken();

        $retval = COM_startBlock ($LANG08[10] . ' ' . $displayname);
        $mail_template = new Template ($_CONF['path_layout'] . 'profiles');

        $mail_template->set_file('form','contactuserform.thtml');

        if ($postmode == 'html') {
            $mail_template->set_var ('show_htmleditor', true);
        } else {
            $mail_template->unset_var ('show_htmleditor');
        }

        $mail_template->set_var('lang_postmode', $LANG03[2]);
        $mail_template->set_var('postmode_options', COM_optionList($_TABLES['postmodes'],'code,name',$postmode));

        $mail_template->set_var ('lang_description', $LANG08[26]);
        $mail_template->set_var ('lang_username', $LANG08[11]);
        if (COM_isAnonUser()) {
            $sender = '';
            if (isset ($_POST['author'])) {
                $sender = strip_tags ($_POST['author']);
                $sender = substr ($sender, 0, strcspn ($sender, "\r\n"));
                $sender = @htmlspecialchars (trim ($sender), ENT_QUOTES,COM_getEncodingt());
            }
            $mail_template->set_var ('username', $sender);
        } else {
            $mail_template->set_var ('username',
                    COM_getDisplayName ($_USER['uid'], $_USER['username'],
                                        $_USER['fullname']));
        }
        $mail_template->set_var ('lang_useremail', $LANG08[12]);
        if (empty ($_USER['email'])) {
            $email = '';
            if (isset ($_POST['authoremail'])) {
                $email = strip_tags ($_POST['authoremail']);
                $email = substr ($email, 0, strcspn ($email, "\r\n"));
                $email = @htmlspecialchars (trim ($email), ENT_QUOTES,COM_getEncodingt());
            }
            $mail_template->set_var ('useremail', $email);
        } else {
            $mail_template->set_var ('useremail', $_USER['email']);
        }
        $mail_template->set_var ('lang_subject', $LANG08[13]);
        $mail_template->set_var ('subject', $subject);
        $mail_template->set_var ('lang_message', $LANG08[14]);
        $mail_template->set_var ('message', @htmlspecialchars($message),ENT_QUOTES,COM_getEncodingt());
        $mail_template->set_var ('lang_nohtml', $LANG08[15]);
        $mail_template->set_var ('lang_submit', $LANG08[16]);
        $mail_template->set_var ('uid', $uid);
        $mail_template->set_var ('sec_token_name', CSRF_TOKEN);
        $mail_template->set_var ('sec_token', $token);
        PLG_templateSetVars ('contact', $mail_template);
        $mail_template->parse ('output', 'form');
        $retval .= $mail_template->finish ($mail_template->get_var ('output'));
        $retval .= COM_endBlock ();
    } else {
        $retval = COM_showMessageText($LANG08[35],$LANG08[10],false,'error');
    }

    return $retval;
}

/**
* Email story to a friend
*
* @param    string  $sid        id of story to email
* @param    string  $to         name of person / friend to email
* @param    string  $toemail    friend's email address
* @param    string  $from       name of person sending the email
* @param    string  $fromemail  sender's email address
* @param    string  $shortmsg   short intro text to send with the story
* @return   string              Meta refresh
*
* Modification History
*
* Date        Author        Description
* ----        ------        -----------
* 4/17/01    Tony Bibbs    Code now allows anonymous users to send email
*                and it allows user to input a message as well
*                Thanks to Yngve Wassvik Bergheim for some of
*                this code
*
*/
function mailstory ($sid, $to, $toemail, $from, $fromemail, $shortmsg,$html=0)
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG08, $MESSAGE;

    $dt = new Date('now',$_USER['tzid']);

    $storyurl = COM_buildUrl($_CONF['site_url'] . '/article.php?story=' . $sid);
    if ($_CONF['url_rewrite']) {
        $retURL = $storyurl . '?msg=85';
    } else {
        $retURL = $storyurl . '&amp;msg=85';
    }
    // check for correct $_CONF permission
    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
                             ($_CONF['emailstoryloginrequired'] == 1))) {
        echo COM_refresh($retURL);
        exit;
    }

    // check if emailing of stories is disabled
    if ($_CONF['hideemailicon'] == 1) {
        echo COM_refresh($retURL);
        exit;
    }

    // check mail speedlimit
    COM_clearSpeedlimit ($_CONF['speedlimit'], 'mail');
    if (COM_checkSpeedlimit ('mail') > 0) {
        echo COM_refresh($retURL);
        exit;
    }

    $filter = sanitizer::getInstance();
    if ( $html ) {
        $filter->setPostmode('html');
    } else {
        $filter->setPostmode('text');
    }
    $allowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_default']);
    $filter->setAllowedElements($allowedElements);
    $filter->setCensorData(true);
    $filter->setReplaceTags(true);
    $filter->setNamespace('glfusion','mail_story');

    $story = new Story();
    $args = array ( 'sid' => $sid, 'mode' => 'view' );
    $output = STORY_LOADED_OK;
    $result = PLG_invokeService('story', 'get', $args, $output, $svc_msg);
    if ( $result == PLG_RET_OK ) {
        reset($story->_dbFields);
        while (list($fieldname,$save) = each($story->_dbFields)) {
            $varname = '_' . $fieldname;
            if (array_key_exists($fieldname, $output)) {
                $story->{$varname} = $output[$fieldname];
            }
        }
        $story->_username = $output['username'];
        $story->_fullname = $output['fullname'];
    } else {
        return COM_refresh($_CONF['site_url'] . '/index.php');
    }
    $A['title'] = $story->DisplayElements('title');
    $A['introtext'] = $story->DisplayElements('introtext');
    $A['uid'] = $story->displayElements('uid');
    $A['story_image'] = $story->DisplayElements('story_image');
    $A['day'] = $story->DisplayElements('date');

    $result = PLG_checkforSpam ($shortmsg, $_CONF['spamx']);
    if ($result > 0) {
        COM_updateSpeedlimit ('mail');
        COM_displayMessageAndAbort ($result, 'spamx', 403, 'Forbidden');
    }

    USES_lib_html2text();

    $T = new Template($_CONF['path_layout'].'email/');
    $T->set_file(array('html_msg'   => 'mailstory_html.thtml',
                       'text_msg'   => 'mailstory_text.thtml'
    ));

    // filter any HTML from the short message
    $shortmsg = $filter->filterHTML($shortmsg);

    $html2txt = new html2text($shortmsg,false);
    $shortmsg_text = $html2txt->get_text();

    $emailStory = preg_replace_callback('/<a\s+.*?href="(.*?)".*?>/i',
        function ($matches) {
            global $_CONF;
            $tag = $matches[0];
            $url = $matches[1];
            if (!preg_match('/\A(http|https|ftp|ftps|javascript):/i', $url)) {
                $absUrl = rtrim($_CONF['site_url'], '/') . '/' . ltrim($url, '/');
                $tag = str_replace($url, $absUrl, $tag);
            }
            return $tag;
        },$A['introtext']);

    $emailStory = preg_replace_callback('/<img\s+.*?src="(.*?)".*?>/i',
        function ($matches) {
            global $_CONF;
            $tag = $matches[0];
            $url = $matches[1];
            if (!preg_match('/\A(http|https|ftp|ftps|javascript):/i', $url)) {
                $absUrl = rtrim($_CONF['site_url'], '/') . '/' . ltrim($url, '/');
                $tag = str_replace($url, $absUrl, $tag);
            }
            return $tag;
        },$emailStory);

    $story_body = COM_truncateHTML($emailStory,512);

    $html2txt = new html2text($story_body,false);
    $story_body_text = $html2txt->get_text();

    $dt->setTimestamp($A['day']);
    $story_date = $dt->format($_CONF['date'], true);

    $story_title = COM_undoSpecialChars ($A['title']);

    $story_url = COM_buildUrl ($_CONF['site_url'] . '/article.php?story='.$sid);

    if ($_CONF['contributedbyline'] == 1) {
        $author = COM_getDisplayName ($A['uid']);
    } else {
        $author = '';
    }

    if ( $A['story_image'] != '' ) {
        $story_image = $_CONF['site_url'].$A['story_image'];
    } else {
        $story_image = '';
    }

    $T->set_var(array(
        'shortmsg_html'     => $shortmsg,
        'shortmsg_text'     => $shortmsg_text,
        'story_title'       => $story_title,
        'story_date'        => $story_date,
        'story_url'         => $story_url,
        'author'            => $author,
        'story_image'       => $story_image,
        'story_body_html'   => $story_body,
        'story_body_text'   => $story_body_text,
        'lang_by'           => $LANG01[1],
        'site_name'         => $_CONF['site_name'],
        'from_name'         => $from,
        'disclaimer'        => sprintf ($LANG08[23], $from, $fromemail),
    ));

    $T->parse( 'message_body_html', 'html_msg' );
    $message_body_html = $T->finish( $T->get_var( 'message_body_html' ));

    $T->parse( 'message_body_text', 'text_msg' );
    $message_body_text = $T->finish( $T->get_var( 'message_body_text' ));

    $msgData = array(
        'htmlmessage' => $message_body_html,
        'textmessage' => $message_body_text,
        'subject'     => $story_title,
        'from'        => array('email' => $_CONF['site_mail'], 'name' => $from),
        'to'          => array('email' => $toemail, 'name' => $to),
    );

    $mailto = array();
    $mailfrom = array();

    $mailto = COM_formatEmailAddress ($to, $toemail);
    $mailfrom = COM_formatEmailAddress ($from, $fromemail);
    $subject = COM_undoSpecialChars(strip_tags('Re: '.$A['title']));

    $rc = COM_mail ($mailto, $msgData['subject'], $msgData['htmlmessage'], $mailfrom,true,0,'',$msgData['textmessage']);
    COM_updateSpeedlimit ('mail');

    if ( $rc ) {
        if ($_CONF['url_rewrite']) {
            COM_setMsg( $MESSAGE[27], 'info' );
            $retval = COM_refresh($storyurl);
        } else {
            COM_setMsg($MESSAGE[27],'info');
            $retval = COM_refresh($storyurl);
        }
    } else {
        // Increment numemails counter for story
        DB_query ("UPDATE {$_TABLES['stories']} SET numemails = numemails + 1 WHERE sid = '".DB_escapeString($sid)."'");

        if ($_CONF['url_rewrite']) {
            COM_setMsg($MESSAGE[26],'error');
            $retval = COM_refresh($storyurl);
        } else {
            COM_setMsg($MESSAGE[26],'error');
            $retval = COM_refresh($storyurl);
        }
    }
    echo COM_refresh($retval);
    exit;
}


function _createMailStory( $sid )
{
    global $_CONF, $_TABLES, $LANG_DIRECTION, $LANG01, $LANG08;

    USES_lib_story();

    $story = new Story();

    $args = array (
                    'sid' => $sid,
                    'mode' => 'view'
                  );

    $output = STORY_LOADED_OK;
    $result = PLG_invokeService('story', 'get', $args, $output, $svc_msg);

    if($result == PLG_RET_OK) {
        /* loadFromArray cannot be used, since it overwrites the timestamp */
        reset($story->_dbFields);

        while (list($fieldname,$save) = each($story->_dbFields)) {
            $varname = '_' . $fieldname;

            if (array_key_exists($fieldname, $output)) {
                $story->{$varname} = $output[$fieldname];
            }
        }
       $story->_username = $output['username'];
       $story->_fullname = $output['fullname'];
    }
    if ($output == STORY_PERMISSION_DENIED) {
        $display = COM_siteHeader ('menu', $LANG_ACCESS['accessdenied'])
                 . COM_showMessageText($LANG_ACCESS['storydenialmsg'], $LANG_ACCESS['accessdenied'], true,'error')
                 . COM_siteFooter ();
        echo $display;
        exit;
    } elseif ( $output == STORY_INVALID_SID ) {
        COM_404();
    } else {
        $T = new Template($_CONF['path_layout'] . 'article');
        $T->set_file('article', 'mailable.thtml');
        list($cacheFile,$style_cache_url) = COM_getStyleCacheLocation();
        $T->set_var('direction', $LANG_DIRECTION);
        $T->set_var('css_url',$style_cache_url);
        $T->set_var('page_title',
                $_CONF['site_name'] . ': ' . $story->displayElements('title'));
        $T->set_var ( 'story_title', $story->DisplayElements( 'title' ) );
        $T->set_var ( 'story_subtitle',$story->DisplayElements('subtitle'));
        $story_image = $story->DisplayElements('story_image');
        if ( $story_image != '' ) {
            $T->set_var('story_image',$story_image);
        } else {
            $T->unset_var('story_image');
        }

         if ( $_CONF['hidestorydate'] != 1 ) {
            $T->set_var ('story_date', $story->displayElements('date'));
        }

        if ($_CONF['contributedbyline'] == 1) {
            $T->set_var ('lang_contributedby', $LANG01[1]);
            $authorname = COM_getDisplayName ($story->displayElements('uid'));
            $T->set_var ('author', $authorname);
            $T->set_var ('story_author', $authorname);
            $T->set_var ('story_author_username', $story->DisplayElements('username'));
        }

        $T->set_var ('story_introtext',
                                    $story->DisplayElements('introtext'));
        $T->set_var ('story_bodytext',
                                    $story->DisplayElements('bodytext'));

        $T->set_var ('site_name', $_CONF['site_name']);
        $T->set_var ('site_slogan', $_CONF['site_slogan']);
        $T->set_var ('story_id', $story->getSid());
        $articleUrl = COM_buildUrl ($_CONF['site_url']
                                    . '/article.php?story=' . $story->getSid());
        if ($story->DisplayElements('commentcode') >= 0) {
            $commentsUrl = $articleUrl . '#comments';
            $comments = $story->DisplayElements('comments');
            $numComments = COM_numberFormat ($comments);
            $T->set_var ('story_comments', $numComments);
            $T->set_var ('comments_url', $commentsUrl);
            $T->set_var ('comments_text',
                    $numComments . ' ' . $LANG01[3]);
            $T->set_var ('comments_count', $numComments);
            $T->set_var ('lang_comments', $LANG01[3]);
            $comments_with_count = sprintf ($LANG01[121], $numComments);

            if ($comments > 0) {
                $comments_with_count = COM_createLink($comments_with_count, $commentsUrl);
            }
            $T->set_var ('comments_with_count', $comments_with_count);
        }
        $T->set_var ('lang_full_article', $LANG08[33]);
        $T->set_var ('article_url', $articleUrl);

        COM_setLangIdAndAttribute($T);

        $T->parse('output', 'article');
        $htmlMsg =  $T->finish($T->get_var('output'));

        return $htmlMsg;
    }
}

/**
* Display form to email a story to someone.
*
* @param    string  $sid    ID of article to email
* @return   string          HTML for email story form
*
*/
function mailstoryform ($sid, $to = '', $toemail = '', $from = '',
                        $fromemail = '', $shortmsg = '', $msg = 0)
{
    global $_CONF, $_TABLES, $_USER, $LANG03,$LANG08, $LANG_LOGIN;

    $retval = '';

    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
                             ($_CONF['emailstoryloginrequired'] == 1))) {
        $display  = COM_siteHeader('menu', $LANG_LOGIN[1]);
        $display .= SEC_loginRequiredForm();
        $display .= COM_siteFooter();
        echo $display;
        exit;
    }

    $token = SEC_createToken();

    $result = DB_query("SELECT COUNT(*) AS count FROM {$_TABLES['stories']} WHERE sid = '".DB_escapeString($sid)."'" . COM_getTopicSql('AND') . COM_getPermSql('AND'));
    $A = DB_fetchArray($result);
    if ($A['count'] == 0) {
        return COM_refresh($_CONF['site_url'] . '/index.php');
    }

    if ($msg > 0) {
        $retval .= COM_showMessage ($msg,'','',0,'error');
    }

    if (empty ($from) && empty ($fromemail)) {
        if (!COM_isAnonUser()) {
            $from = COM_getDisplayName ($_USER['uid'], $_USER['username'],
                                        $_USER['fullname']);
            $fromemail = DB_getItem ($_TABLES['users'], 'email',
                                     "uid = {$_USER['uid']}");
        }
    }

    $postmode = $_CONF['mailuser_postmode'];

    $mail_template = new Template($_CONF['path_layout'] . 'profiles');

    $mail_template->set_file('form','contactauthorform.thtml');

    if ($postmode == 'html') {
        $mail_template->set_var ('show_htmleditor', true);
    } else {
        $mail_template->unset_var ('show_htmleditor');
    }
    $mail_template->set_var('lang_postmode', $LANG03[2]);
    $mail_template->set_var('postmode', $postmode);
    $mail_template->set_var('start_block_mailstory2friend', COM_startBlock($LANG08[17]));
    $mail_template->set_var('lang_fromname', $LANG08[20]);
    $mail_template->set_var('name', $from);
    $mail_template->set_var('lang_fromemailaddress', $LANG08[21]);
    $mail_template->set_var('email', $fromemail);
    $mail_template->set_var('lang_toname', $LANG08[18]);
    $mail_template->set_var('toname', $to);
    $mail_template->set_var('lang_toemailaddress', $LANG08[19]);
    $mail_template->set_var('toemail', $toemail);
    $mail_template->set_var('lang_shortmessage', $LANG08[27]);
    $mail_template->set_var('shortmsg', @htmlspecialchars($shortmsg,ENT_COMPAT,COM_getEncodingt()));
    $mail_template->set_var('lang_warning', $LANG08[22]);
    $mail_template->set_var('lang_sendmessage', $LANG08[16]);
    $mail_template->set_var('story_id',$sid);
    $mail_template->set_var ('sec_token_name', CSRF_TOKEN);
    $mail_template->set_var ('sec_token', $token);
    PLG_templateSetVars ('emailstory', $mail_template);
    $mail_template->set_var('end_block', COM_endBlock());
    $mail_template->parse('output', 'form');
    $retval .= $mail_template->finish($mail_template->get_var('output'));

    return $retval;
}


// MAIN
$display = '';

if ( isset($_POST['cancel'])) {
    echo COM_refresh ($_CONF['site_url'] . '/index.php');
    exit;
}

if (isset ($_POST['what'])) {
    $what = COM_applyFilter ($_POST['what']);
} else if (isset ($_GET['what'])) {
    $what = COM_applyFilter ($_GET['what']);
} else {
    $what = '';
}

$postmode = $_CONF['mailuser_postmode'];

switch ($what) {
    case 'contact':
        if ( SEC_checkToken() ) {
            $uid = COM_applyFilter ($_POST['uid'], true);
            if ($uid > 1) {
                $html = 0;
                if ( $postmode == 'html' ) {
                    $html = 1;
                }
                $message = $_POST['message'];
                $display .= contactemail ($uid, $_POST['author'], $_POST['authoremail'], $_POST['subject'], $message,$html);
                echo $display;
                exit;
            } else {
                $display .= COM_refresh ($_CONF['site_url'] . '/index.php');
                echo $display;
                exit;
            }
        } else {
            COM_setMsg( $MESSAGE[26], 'error' );
            $display .= COM_refresh ($_CONF['site_url'] . '/index.php');
            echo $display;
            exit;
        }
        break;

    case 'emailstory':
        $sid = COM_sanitizeID(COM_applyFilter ($_GET['sid']));
        if (empty ($sid)) {
            $display = COM_refresh ($_CONF['site_url'] . '/index.php');
        } else if ($_CONF['hideemailicon'] == 1) {
            $display = COM_refresh (COM_buildUrl ($_CONF['site_url']
                                    . '/article.php?story=' . $sid));
        } else {
            $display .= COM_siteHeader ('menu', $LANG08[17])
                     . mailstoryform ($sid)
                     . COM_siteFooter ();
        }
        break;

    case 'sendstory':
        if ( SEC_checkToken() ) {
            $sid = COM_sanitizeID(COM_applyFilter ($_POST['sid']));
            if (empty ($sid)) {
                $display = COM_refresh ($_CONF['site_url'] . '/index.php');
            } else {
                $html = 0;

                if ( $postmode == 'html' ) {
                    $html = 1;
                }
                $shortmessage = $_POST['shortmsg'];

                if (empty ($_POST['toemail']) || empty ($_POST['fromemail'])
                        || !COM_isEmail ($_POST['toemail'])
                        || !COM_isEmail ($_POST['fromemail'])) {
                    $display .= COM_siteHeader ('menu', $LANG08[17])
                             . mailstoryform ($sid, COM_applyFilter($_POST['to']), COM_applyFilter($_POST['toemail']),
                                              COM_applyFilter($_POST['from']), COM_applyFilter($_POST['fromemail']),
                                              $shortmessage, 52)
                             . COM_siteFooter ();
                } else if (empty ($_POST['to']) || empty ($_POST['from']) ||
                        empty ($shortmessage)) {
                    $display .= COM_siteHeader ('menu', $LANG08[17])
                             . mailstoryform ($sid, COM_applyFilter($_POST['to']), COM_applyFilter($_POST['toemail']),
                                              COM_applyFilter($_POST['from']), COM_applyFilter($_POST['fromemail']),
                                              $shortmessage)
                             . COM_siteFooter ();
                } else {
                    $msg = PLG_itemPreSave ('emailstory', $shortmessage);
                    if (!empty ($msg)) {
                        $display .= COM_siteHeader ('menu', '')
                                 . COM_errorLog ($msg, 2)
                                 . mailstoryform ($sid, COM_applyFilter($_POST['to']), COM_applyFilter($_POST['toemail']),
                                                  COM_applyFilter($_POST['from']), COM_applyFilter($_POST['fromemail']),
                                                  $shortmessage)
                                 . COM_siteFooter ();
                    } else {

                        $display .= mailstory ($sid, $_POST['to'], $_POST['toemail'],
                            $_POST['from'], $_POST['fromemail'], $shortmessage,$html);
                    }
                }
            }
        } else {
            COM_setMsg( $MESSAGE[26], 'error' );
            $display .= COM_refresh ($_CONF['site_url'] . '/index.php');
            echo $display;
            exit;
        }
        break;

    default:
        if (isset ($_GET['uid'])) {
            $uid = (int) COM_applyFilter ($_GET['uid'], true);
        } else {
            $uid = 0;
        }
        if ($uid > 1) {
            $subject = '';
            if (isset ($_GET['subject'])) {
                $subject = strip_tags ($_GET['subject']);
                $subject = substr ($subject, 0, strcspn ($subject, "\r\n"));
                $subject = htmlspecialchars (trim ($subject), ENT_QUOTES,COM_getEncodingt());
            }
            $display .= COM_siteHeader ('menu', $LANG04[81])
                     . contactform ($uid, $subject)
                     . COM_siteFooter ();
        } else {
            $display .= COM_refresh ($_CONF['site_url'] . '/index.php');
        }
        break;
}

echo $display;

?>