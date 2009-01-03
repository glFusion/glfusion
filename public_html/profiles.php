<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | profiles.php                                                             |
// |                                                                          |
// | This pages lets glFusion users communicate with each other without risk  |
// | of their email address being intercepted by spammers.                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
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
    global $_CONF, $_TABLES, $_USER, $pageHandle, $LANG04, $LANG08;

    $retval = '';

    // check for correct $_CONF permission
    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
                             ($_CONF['emailuserloginrequired'] == 1))
                         && ($uid != 2)) {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php?msg=85');
    }

    // check for correct 'to' user preferences
    $result = DB_query ("SELECT emailfromadmin,emailfromuser FROM {$_TABLES['userprefs']} WHERE uid = '$uid'");
    $P = DB_fetchArray ($result);
    if (SEC_inGroup ('Root') || SEC_hasRights ('user.mail')) {
        $isAdmin = true;
    } else {
        $isAdmin = false;
    }
    if ((($P['emailfromadmin'] != 1) && $isAdmin) ||
        (($P['emailfromuser'] != 1) && !$isAdmin)) {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php?msg=85');
    }

    // check mail speedlimit
    COM_clearSpeedlimit ($_CONF['speedlimit'], 'mail');
    if (COM_checkSpeedlimit ('mail') > 0) {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }

    if (!empty($author) && !empty($subject) && !empty($message)) {
        if (COM_isemail($authoremail)) {
            $result = DB_query("SELECT username,fullname,email FROM {$_TABLES['users']} WHERE uid = $uid");
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

            $subject = $subject;
            $message = $message;

            // do a spam check with the unfiltered message text and subject
            $mailtext = $subject . "\n" . $message . $sig;
            $result = PLG_checkforSpam ($mailtext, $_CONF['spamx']);
            if ($result > 0) {
                COM_updateSpeedlimit ('mail');
                COM_displayMessageAndAbort ($result, 'spamx', 403, 'Forbidden');
            }

            $msg = PLG_itemPreSave ('contact', $message);
            if (!empty ($msg)) {
                $pageHandle->setPageTitle('');
                $pageHandle->addContent(COM_errorLog ($msg, 2)
                        . contactform ($uid, $subject, $message));
                $pageHandle->displayPage();
                exit;
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

            if ( $rc === false ) {
                $pageHandle->redirect($_CONF['site_url']
                                       . '/users.php?mode=profile&amp;uid=' . $uid
                                       . '&amp;msg=26');
            } else {
                $pageHandle->redirect($_CONF['site_url']
                                       . '/users.php?mode=profile&amp;uid=' . $uid
                                       . '&amp;msg=27');
            }
        } else {
            $subject = strip_tags ($subject);
            $subject = substr ($subject, 0, strcspn ($subject, "\r\n"));
            $subject = htmlspecialchars (trim ($subject), ENT_QUOTES);
            $pageHandle->setPageTitle($LANG04[81]);
            $pageHandle->addContent(COM_errorLog ($LANG08[3], 2)
                    . contactform ($uid, $subject, $message));
            $pageHandle->displayPage();
        }
    } else {
        $subject = strip_tags ($subject);
        $subject = substr ($subject, 0, strcspn ($subject, "\r\n"));
        $subject = htmlspecialchars (trim ($subject), ENT_QUOTES);
        $pageHandle->setPageTitle($LANG04[81]);
        $pageHandle->addContent(COM_errorLog ($LANG08[4], 2)
                . contactform ($uid, $subject, $message));
        $pageHandle->displayPage();
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

    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
                             ($_CONF['emailuserloginrequired'] == 1))) {
        $retval = COM_startBlock ($LANG_LOGIN[1], '',
                          COM_getBlockTemplate ('_msg_block', 'header'));
        $login = new Template($_CONF['path_layout'] . 'submit');
        $login->set_file (array ('login'=>'submitloginrequired.thtml'));
        $login->set_var ( 'xhtml', XHTML );
        $login->set_var ('site_url', $_CONF['site_url']);
        $login->set_var ('site_admin_url', $_CONF['site_admin_url']);
        $login->set_var ('layout_url', $_CONF['layout_url']);
        $login->set_var ('login_message', $LANG_LOGIN[2]);
        $login->set_var ('lang_login', $LANG_LOGIN[3]);
        $login->set_var ('lang_newuser', $LANG_LOGIN[4]);
        $login->parse ('output', 'login');
        $retval .= $login->finish ($login->get_var('output'));
        $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    } else {
        $result = DB_query ("SELECT emailfromadmin,emailfromuser FROM {$_TABLES['userprefs']} WHERE uid = '$uid'");
        $P = DB_fetchArray ($result);
        if (SEC_inGroup ('Root') || SEC_hasRights ('user.mail')) {
            $isAdmin = true;
        } else {
            $isAdmin = false;
        }

        if ($_CONF['advanced_editor'] == 1) {
            $postmode = 'html';
        } elseif (empty ($postmode)) {
            $postmode = $_CONF['postmode'];
        }

        $displayname = COM_getDisplayName ($uid);
        if ((($P['emailfromadmin'] == 1) && $isAdmin) ||
            (($P['emailfromuser'] == 1) && !$isAdmin)) {

            $retval = COM_startBlock ($LANG08[10] . ' ' . $displayname);
            $mail_template = new Template ($_CONF['path_layout'] . 'profiles');

            if (($_CONF['advanced_editor'] == 1)) {
                $mail_template->set_file('form','contactuserform_advanced.thtml');
            } else {
                $mail_template->set_file('form','contactuserform.thtml');
            }
            if ( file_exists($_CONF['path_layout'] . '/fckstyles.xml') ) {
                $mail_template->set_var('glfusionStyleBasePath',$_CONF['layout_url']);
            } else {
                $mail_template->set_var('glfusionStyleBasePath',$_CONF['site_url'] . '/fckeditor');
            }
            if ($postmode == 'html') {
                $mail_template->set_var ('show_texteditor', 'none');
                $mail_template->set_var ('show_htmleditor', '');
            } else {
                $mail_template->set_var ('show_texteditor', '');
                $mail_template->set_var ('show_htmleditor', 'none');
            }
            $mail_template->set_var('lang_postmode', $LANG03[2]);
            $mail_template->set_var('postmode_options', COM_optionList($_TABLES['postmodes'],'code,name',$postmode));

            $mail_template->set_var ( 'xhtml', XHTML );
            $mail_template->set_var ('site_url', $_CONF['site_url']);
            $mail_template->set_var ('lang_description', $LANG08[26]);
            $mail_template->set_var ('lang_username', $LANG08[11]);
            if (COM_isAnonUser()) {
                $sender = '';
                $sender = $inputHandler->getVar('text','author','post','');
                $mail_template->set_var ('username', $sender);
            } else {
                $mail_template->set_var ('username',
                        COM_getDisplayName ($_USER['uid'], $_USER['username'],
                                            $_USER['fullname']));
            }
            $mail_template->set_var ('lang_useremail', $LANG08[12]);
            if (empty ($_USER['email'])) {
                $email = '';
                $email = $inputHandler->getVar('text','authoremail','post','');
                $mail_template->set_var ('useremail', $email);
            } else {
                $mail_template->set_var ('useremail', $_USER['email']);
            }
            $mail_template->set_var ('lang_subject', $LANG08[13]);
            $mail_template->set_var ('subject', $subject);
            $mail_template->set_var ('lang_message', $LANG08[14]);
            $mail_template->set_var ('message', $message);
            $mail_template->set_var ('message_text',$message);
            $mail_template->set_var ('message_html',$message);
            $mail_template->set_var ('lang_nohtml', $LANG08[15]);
            $mail_template->set_var ('lang_submit', $LANG08[16]);
            $mail_template->set_var ('uid', $uid);
            PLG_templateSetVars ('contact', $mail_template);
            $mail_template->parse ('output', 'form');
            $retval .= $mail_template->finish ($mail_template->get_var ('output'));
            $retval .= COM_endBlock ();
        } else {
            $retval = COM_startBlock ($LANG08[10] . ' ' . $displayname, '',
                              COM_getBlockTemplate ('_msg_block', 'header'));
            $retval .= $LANG08[35];
            $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block',
                                                           'footer'));
        }
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
function mailstory ($sid, $to, $toemail, $from, $fromemail, $shortmsg)
{
    global $_CONF, $_TABLES, $_USER, $pageHandle,$LANG01, $LANG08;

    $storyurl = $pageHandle->buildUrl($_CONF['site_url'] . '/article.php?story=' . $sid);
    if ($_CONF['url_rewrite']) {
        $redirectURL = $storyurl . '?msg=85';
    } else {
        $redirectURL = $storyurl . '&amp;msg=85';
    }
    // check for correct $_CONF permission
    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
                             ($_CONF['emailstoryloginrequired'] == 1))) {
        $pageHandle->redirect($redirectURL);
    }

    // check if emailing of stories is disabled
    if ($_CONF['hideemailicon'] == 1) {
        $pageHandle->redirect($redirectURL);
    }

    // check mail speedlimit
    COM_clearSpeedlimit ($_CONF['speedlimit'], 'mail');
    if (COM_checkSpeedlimit ('mail') > 0) {
        $pageHandle->redirect($redirectURL);
    }

    $sql = "SELECT uid,title,introtext,bodytext,commentcode,UNIX_TIMESTAMP(date) AS day FROM {$_TABLES['stories']} WHERE sid = '$sid'";
    $result = DB_query ($sql);
    $A = DB_fetchArray ($result);

    $mailtext = sprintf ($LANG08[23], $from, $fromemail) . LB;
    if (strlen ($shortmsg) > 0) {
        $mailtext .= LB . sprintf ($LANG08[28], $from) . $shortmsg . LB;
    }

    // just to make sure this isn't an attempt at spamming users ...
    $result = PLG_checkforSpam ($mailtext, $_CONF['spamx']);
    if ($result > 0) {
        COM_updateSpeedlimit ('mail');
        COM_displayMessageAndAbort ($result, 'spamx', 403, 'Forbidden');
    }

    $mailtext .= '------------------------------------------------------------'
              . LB . LB
              . COM_undoSpecialChars ($A['title']) . LB
              . strftime ($_CONF['date'], $A['day']) . LB;

    if ($_CONF['contributedbyline'] == 1) {
        $author = COM_getDisplayName ($A['uid']);
        $mailtext .= $LANG01[1] . ' ' . $author . LB;
    }
    $mailtext .= LB
        . COM_undoSpecialChars(strip_tags($A['introtext'])).LB.LB
        . COM_undoSpecialChars(strip_tags($A['bodytext'])).LB.LB
        . '------------------------------------------------------------'.LB;
    if ($A['commentcode'] == 0) { // comments allowed
        $mailtext .= $LANG08[24] . LB
                  . $pageHandle->buildUrl ($_CONF['site_url'] . '/article.php?story='
                                  . $sid . '#comments');
    } else { // comments not allowed - just add the story's URL
        $mailtext .= $LANG08[33] . LB
                  . $pageHandle->buildUrl ($_CONF['site_url'] . '/article.php?story='
                                  . $sid);
    }

    $mailto = array();
    $mailfrom = array();

    $mailto     = COM_formatEmailAddress ($to, $toemail);
    $mailfrom   = COM_formatEmailAddress ($from, $fromemail);
    $subject    = COM_undoSpecialChars(strip_tags('Re: '.$A['title']));

    COM_mail ($mailto, $subject, $mailtext, $mailfrom);
    COM_updateSpeedlimit ('mail');

    // Increment numemails counter for story
    DB_query ("UPDATE {$_TABLES['stories']} SET numemails = numemails + 1 WHERE sid = '$sid'");

    if ($_CONF['url_rewrite']) {
        $redirectURL = $storyurl . '?msg=27';
    } else {
        $redirectURL = $storyurl . '&amp;msg=27';
    }
    $pageHandle->redirect($redirectURL);
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
    global $_CONF, $_TABLES, $_USER, $pageHandle, $LANG03,$LANG08, $LANG_LOGIN;

    $retval = '';

    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
                             ($_CONF['emailstoryloginrequired'] == 1))) {
        $retval = COM_startBlock ($LANG_LOGIN[1], '',
                          COM_getBlockTemplate ('_msg_block', 'header'));
        $login = new Template($_CONF['path_layout'] . 'submit');
        $login->set_file (array ('login'=>'submitloginrequired.thtml'));
        $login->set_var ( 'xhtml', XHTML );
        $login->set_var ('site_url', $_CONF['site_url']);
        $login->set_var ('site_admin_url', $_CONF['site_admin_url']);
        $login->set_var ('layout_url', $_CONF['layout_url']);
        $login->set_var ('login_message', $LANG_LOGIN[2]);
        $login->set_var ('lang_login', $LANG_LOGIN[3]);
        $login->set_var ('lang_newuser', $LANG_LOGIN[4]);
        $login->parse ('output', 'login');
        $retval .= $login->finish ($login->get_var('output'));
        $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));

        return $retval;
    }

    if ($msg > 0) {
        $pageHandle->addMessage($msg);
    }

    if (empty ($from) && empty ($fromemail)) {
        if (!COM_isAnonUser()) {
            $from = COM_getDisplayName ($_USER['uid'], $_USER['username'],
                                        $_USER['fullname']);
            $fromemail = DB_getItem ($_TABLES['users'], 'email',
                                     "uid = {$_USER['uid']}");
        }
    }

    if ($_CONF['advanced_editor'] == 1) {
        $postmode = 'html';
    } elseif (empty ($postmode)) {
        $postmode = $_CONF['postmode'];
    }

    $mail_template = new Template($_CONF['path_layout'] . 'profiles');

    if (($_CONF['advanced_editor'] == 1)) {
        $mail_template->set_file('form','contactauthorform_advanced.thtml');
    } else {
        $mail_template->set_file('form','contactauthorform.thtml');
    }
    if ( file_exists($_CONF['path_layout'] . '/fckstyles.xml') ) {
        $mail_template->set_var('glfusionStyleBasePath',$_CONF['layout_url']);
    } else {
        $mail_template->set_var('glfusionStyleBasePath',$_CONF['site_url'] . '/fckeditor');
    }
    if ($postmode == 'html') {
        $mail_template->set_var ('show_texteditor', 'none');
        $mail_template->set_var ('show_htmleditor', '');
    } else {
        $mail_template->set_var ('show_texteditor', '');
        $mail_template->set_var ('show_htmleditor', 'none');
    }
    $mail_template->set_var('lang_postmode', $LANG03[2]);
    $mail_template->set_var('postmode_options', COM_optionList($_TABLES['postmodes'],'code,name',$postmode));

    $mail_template->set_var( 'xhtml', XHTML );
    $mail_template->set_var('site_url', $_CONF['site_url']);
    $mail_template->set_var('site_admin_url', $_CONF['site_admin_url']);
    $mail_template->set_var('layout_url', $_CONF['layout_url']);
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
    $mail_template->set_var('shortmsg', $shortmsg);
    $mail_template->set_var('message_text', $shortmsg);
    $mail_template->set_var('message_html', $shortmsg);
    $mail_template->set_var('lang_warning', $LANG08[22]);
    $mail_template->set_var('lang_sendmessage', $LANG08[16]);
    $mail_template->set_var('story_id',$sid);
    PLG_templateSetVars ('emailstory', $mail_template);
    $mail_template->set_var('end_block', COM_endBlock());
    $mail_template->parse('output', 'form');
    $retval .= $mail_template->finish($mail_template->get_var('output'));

    return $retval;
}


// MAIN
$display = '';

$pageHandle->setShowExtraBlocks(false);

$what = $inputHandler->getVar('strict','what',array('post','get'),'');

switch ($what) {
    case 'contact':
        $uid = $inputHandler->getVar('integer','uid','post',0);
        if ($uid > 1) {
            $html = 0;
            if (($_CONF['advanced_editor'] == 1)) {
                if ( $_POST['postmode'] == 'html' ) {
                    $message = $inputHandler->getVar('html','message_html','post','');
                    $html = 1;
                } else if ( $_POST['postmode'] == 'text' ) {
                    $message = $inputHandler->getVar('text','message_text','post','');
                    $html = 0;
                }
            } else {
                $message = $inputHandler->getVar('text','message','post','');
            }
            $author = $inputHandler->getVar('text','author','post','');
            $authoremail = $inputHandler->getVar('text','authoremail','post','');
            $subject = $inputHandler->getVar('text','subject','post','');

            $pageHandle->addContent(contactemail ($uid, $author,
                    $authoremail, $subject,
                    $message,$html));
            $pageHandle->displayPage();
        } else {
            $pageHandle->redirect($_CONF['site_url'] . '/index.php');
        }
        break;

    case 'emailstory':
        $sid = $inputHandler->getVar('strict','sid','get','');
        if (empty ($sid)) {
            $pageHandle->redirect($_CONF['site_url'] . '/index.php');
        } else if ($_CONF['hideemailicon'] == 1) {
            $pageHandle->redirect($pageHandle->buildUrl ($_CONF['site_url']
                                    . '/article.php?story=' . $sid));
        } else {
            $pageHandle->setPageTitle($LANG08[17]);
            $pageHandle->addContent(mailstoryform ($sid));
            $pageHandle->displayPage();
        }
        break;

    case 'sendstory':
        $sid = $inputHandler->getVar('strict','sid','post','');
        if (empty ($sid)) {
            $pageHandle->redirect($_CONF['site_url'] . '/index.php');
        } else {
            $html = 0;
            if (($_CONF['advanced_editor'] == 1)) {
                if ( $_POST['postmode'] == 'html' ) {
                    $shortmessage = $inputHandler->getVar('html','message_html','post','');
                    $html = 1;
                } else if ( $_POST['postmode'] == 'text' ) {
                    $shortmessage = $inputHandler->getVar('text','message_text','post','');
                    $html = 0;
                }
            } else {
                $shortmessage = $inputHandler->getVar('text','shortmsg','post','');
            }
            $toemail   = $inputHandler->getVar('text','toemail','post','');
            $fromemail = $inputHandler->getVar('text','fromemail','post','');
            $to        = $inputHandler->getVar('text','to','post','');
            $from      = $inputHandler->getVar('text','from','post','');


            if (empty ($toemail) || empty ($fromemail)
                    || !COM_isEmail ($toemail)
                    || !COM_isEmail ($fromemail)) {
                $pageHandle->setPageTitle($LANG08[17]);
                $pageHandle->addContent(mailstoryform ($sid, $to, $toemail,
                                          $from, $fromemail,
                                          $shortmessage, 52));
                $pageHandle->displayPage();
            } else if (empty ($to) || empty ($from) ||
                    empty ($shortmessage)) {
                $pagehandle->setPageTitle($LANG08[17]);
                $pageHandle->addContent(mailstoryform ($sid, $to, $toemail,
                                          $from, $fromemail,
                                          $shortmessage, 52));
                $pageHandle->displayPage();
            } else {
                $msg = PLG_itemPreSave ('emailstory', $shortmessage);
                if (!empty ($msg)) {
                    $pageHandle->setPageTitle('');
                    $pageHandle->addContent(COM_errorLog ($msg, 2)
                             . mailstoryform ($sid, $to, $toemail,
                                              $from, $fromemail,
                                              $shortmessage));
                    $pageHandle->displayPage();
                } else {
                    $pageHandle->addContent( mailstory ($sid, $to, $toemail,
                        $from, $fromemail, $shortmessage,$html));
                }
            }
        }
        break;

    default:
        $uid = $inputHandler->getVar('integer','uid','get',0);

        if ($uid > 1) {
            $subject = '';
            $subject = $inputHandler->getVar('text','subject','get','');
            $subject = substr ($subject, 0, strcspn ($subject, "\r\n"));

            $pageHandle->setPageTitle($LANG04[81]);
            $pageHandle->addContent(contactform ($uid, $subject));
            $pageHandle->displayPage();
        } else {
            $pageHandle->redirect($_CONF['site_url'] . '/index.php');
        }
        break;
}
?>