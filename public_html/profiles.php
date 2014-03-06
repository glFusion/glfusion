<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | profiles.php                                                             |
// |                                                                          |
// | This pages lets glFusion users communicate with each other without       |
// | exposing email addresses.                                                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2011-2014 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans      - mark AT glfusion DOT org                            |
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
    global $_CONF, $_TABLES, $_USER, $LANG04, $LANG08, $LANG_LOGIN;

    $retval = '';

    // check for correct $_CONF permission
    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
                             ($_CONF['emailuserloginrequired'] == 1))
                         && ($uid != 2)) {
        $display  = COM_siteHeader('menu', $LANG_LOGIN[1]);
        $display .= SEC_loginRequiredForm();
        $display .= COM_siteFooter();
        echo $display;
        exit;
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
                $redirectURL = $_CONF['site_url'] . '/index.php?msg=';
            } else {
                $redirectURL = $_CONF['site_url']
                                . '/users.php?mode=profile&amp;uid=' . $uid
                                . '&amp;msg=';
            }

            if ( $rc === false ) {
                $retval .= COM_refresh($redirectURL . '26');
            } else {
                $retval .= COM_refresh($redirectURL . '27');
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

    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
                             ($_CONF['emailuserloginrequired'] == 1))
                         && ($uid != 2)) {
        $display  = COM_siteHeader('menu', $LANG_LOGIN[1]);
        $display .= SEC_loginRequiredForm();
        $display .= COM_siteFooter();
        echo $display;
        exit;
    } else {
        $result = DB_query ("SELECT emailfromadmin,emailfromuser FROM {$_TABLES['userprefs']} WHERE uid = ".(int) $uid);
        $P = DB_fetchArray ($result);
        if (SEC_inGroup ('Root') || SEC_hasRights ('user.mail')) {
            $isAdmin = true;
        } else {
            $isAdmin = false;
        }
//@TODO validate postmode
        if (empty ($postmode)) {
            $postmode = $_CONF['postmode'];
        }

        $displayname = COM_getDisplayName ($uid);
        if ((($P['emailfromadmin'] == 1) && $isAdmin) ||
            (($P['emailfromuser'] == 1) && !$isAdmin)) {

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

            $mail_template->set_var ('site_url', $_CONF['site_url']);
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
            PLG_templateSetVars ('contact', $mail_template);
            $mail_template->parse ('output', 'form');
            $retval .= $mail_template->finish ($mail_template->get_var ('output'));
            $retval .= COM_endBlock ();
        } else {
            $retval = COM_showMessageText($LANG08[35],$LANG08[10],false,'error');
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
function mailstory ($sid, $to, $toemail, $from, $fromemail, $shortmsg,$html=0)
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG08;

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

    $sql = "SELECT uid,title,introtext,bodytext,commentcode,UNIX_TIMESTAMP(date) AS day,postmode FROM {$_TABLES['stories']} WHERE sid = '".DB_escapeString($sid)."'" . COM_getTopicSql('AND') . COM_getPermSql('AND');
    $result = DB_query($sql);
    if (DB_numRows($result) == 0) {
        return COM_refresh($_CONF['site_url'] . '/index.php');
    }
    $A = DB_fetchArray($result);

    $mailtext = sprintf ($LANG08[23], $from, $fromemail) . LB;
    if (strlen ($shortmsg) > 0) {
        if ( $html ) {
            $shortmsg = COM_filterHTML($shortmsg);
        }
        $mailtext .= LB . sprintf ($LANG08[28], $from) . $shortmsg . LB;
    }

    // just to make sure this isn't an attempt at spamming users ...
    $result = PLG_checkforSpam ($mailtext, $_CONF['spamx']);
    if ($result > 0) {
        COM_updateSpeedlimit ('mail');
        COM_displayMessageAndAbort ($result, 'spamx', 403, 'Forbidden');
    }
    $dt->setTimestamp($A['day']);
    if ( $html ) {
        $mailtext .= '------------------------------------------------------------<br /><br />'
                  . COM_undoSpecialChars ($A['title']) . '<br />'
                  . $dt->format($_CONF['date'], true) . '<br />';
    } else {
        $mailtext .= '------------------------------------------------------------'
                  . LB . LB
                  . COM_undoSpecialChars ($A['title']) . LB
                  . $dt->format($_CONF['date'], true) . LB;
    }

    if ($_CONF['contributedbyline'] == 1) {
        $author = COM_getDisplayName ($A['uid']);
        $mailtext .= $LANG01[1] . ' ' . $author . LB;
    }
    if ( $html ) {
        $mailtext .= PLG_replaceTags($A['introtext'],'glfusion','mail_story') . '<br />'.PLG_replaceTags($A['bodytext'],'glfusion','mail_story').'<br /><br />'
        . '------------------------------------------------------------<br />';
    } else {
        $mailtext .= LB
            . COM_undoSpecialChars(strip_tags(PLG_replaceTags($A['introtext'],'glfusion','mail_story'))).LB.LB
            . COM_undoSpecialChars(strip_tags(PLG_replaceTags($A['bodytext'],'glfusion','mail_story'))).LB.LB
            . '------------------------------------------------------------'.LB;
    }
    if ($A['commentcode'] == 0) { // comments allowed
        $mailtext .= $LANG08[24] . LB
                  . COM_buildUrl ($_CONF['site_url'] . '/article.php?story='
                                  . $sid . '#comments');
    } else { // comments not allowed - just add the story's URL
        $mailtext .= $LANG08[33] . LB
                  . COM_buildUrl ($_CONF['site_url'] . '/article.php?story='
                                  . $sid);
    }

    $mailto = array();
    $mailfrom = array();

    $mailto = COM_formatEmailAddress ($to, $toemail);
    $mailfrom = COM_formatEmailAddress ($from, $fromemail);
    $subject = COM_undoSpecialChars(strip_tags('Re: '.$A['title']));

    $rc = COM_mail ($mailto, $subject, $mailtext, $mailfrom,$html);
    COM_updateSpeedlimit ('mail');

    if ( $rc ) {
        if ($_CONF['url_rewrite']) {
            $retval = COM_refresh($storyurl . '?msg=27');
        } else {
            $retval = COM_refresh($storyurl . '&amp;msg=27');
        }
    } else {
        // Increment numemails counter for story
        DB_query ("UPDATE {$_TABLES['stories']} SET numemails = numemails + 1 WHERE sid = '".DB_escapeString($sid)."'");

        if ($_CONF['url_rewrite']) {
            $retval = COM_refresh($storyurl . '?msg=26');
        } else {
            $retval = COM_refresh($storyurl . '&amp;msg=26');
        }
    }
    echo COM_refresh($retval);
    exit;
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

    $result = DB_query("SELECT COUNT(*) AS count FROM {$_TABLES['stories']} WHERE sid = '".DB_escapeString($sid)."'" . COM_getTopicSql('AND') . COM_getPermSql('AND'));
    $A = DB_fetchArray($result);
    if ($A['count'] == 0) {
        return COM_refresh($_CONF['site_url'] . '/index.php');
    }

    if ($msg > 0) {
        $retval .= COM_showMessage ($msg,'','',0,'info');
    }

    if (empty ($from) && empty ($fromemail)) {
        if (!COM_isAnonUser()) {
            $from = COM_getDisplayName ($_USER['uid'], $_USER['username'],
                                        $_USER['fullname']);
            $fromemail = DB_getItem ($_TABLES['users'], 'email',
                                     "uid = {$_USER['uid']}");
        }
    }

    if (empty ($postmode) || (!in_array($postmode,array('html','plaintext')))) {
        $postmode = $_CONF['postmode'];
    }

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
    PLG_templateSetVars ('emailstory', $mail_template);
    $mail_template->set_var('end_block', COM_endBlock());
    $mail_template->parse('output', 'form');
    $retval .= $mail_template->finish($mail_template->get_var('output'));

    return $retval;
}


// MAIN
$display = '';

if (isset ($_POST['what'])) {
    $what = COM_applyFilter ($_POST['what']);
} else if (isset ($_GET['what'])) {
    $what = COM_applyFilter ($_GET['what']);
} else {
    $what = '';
}

$postmode = $postmode = $_CONF['postmode'];
if ( isset($_POST['postmode'] ) )  {
    if ( !in_array($_POST['postmode'],array('html','plaintext') ) ) {
        $postmode = 'plaintext';
    } else {
        $postmode = COM_applyFilter($_POST['postmode']);
    }
}

switch ($what) {
    case 'contact':
        $uid = COM_applyFilter ($_POST['uid'], true);
        if ($uid > 1) {
            $html = 0;
            if ( $postmode == 'html' ) {
                $html = 1;
            }
            $message = $_POST['message'];

            $display .= contactemail ($uid, $_POST['author'],
                    $_POST['authoremail'], $_POST['subject'],
                    $message,$html);
            echo $display;
            exit;
        } else {
            $display .= COM_refresh ($_CONF['site_url'] . '/index.php');
            echo $display;
            exit;
        }
        break;

    case 'emailstory':
        $sid = COM_applyFilter ($_GET['sid']);
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
        $sid = COM_applyFilter ($_POST['sid']);
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