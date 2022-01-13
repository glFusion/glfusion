<?php
/**
* glFusion CMS
*
* glFusion User authentication module
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*   Mark A. Howard  mark AT usable-web DOT com
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs        tony@tonybibbs.com
*  Mark Limburg      mlimburg@users.sourceforge.net
*  Jason Whittenburg jwhitten@securitygeeks.com
*  Dirk Haun         dirk@haun-online.de
*
*/

require_once 'lib-common.php';

use \glFusion\Database\Database;
use \glFusion\Log\Log;

USES_lib_user();

if ( !isset($_SYSTEM['verification_token_ttl']) ) {
    $_SYSTEM['verification_token_ttl'] = 86400;
}

/**
* Shows a profile for a user
*
* This grabs the user profile for a given user and displays it
*
* @return   string          HTML for user profile page
*
*/
function userprofile()
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG04, $LANG09, $LANG28, $LANG_LOGIN;
    global $LANG_ADMIN;

    $db = Database::getInstance();

    $retval = '';

    if (COM_isAnonUser() &&
        (($_CONF['loginrequired'] == 1) || ($_CONF['profileloginrequired'] == 1))) {
        $retval .= SEC_loginRequiredForm();
        return $retval;
    }

    if ( isset($_GET['uid']) ) {
        $user = COM_applyFilter ($_GET['uid'], true);
        if (!is_numeric ($user) || ($user < 2)) {
            COM_404();
        }
    } else if ( isset($_GET['username']) ) {
        $username = $_GET['username'];
        if ( !USER_validateUsername($username,true) ) {
            COM_404();
        }
        if ( empty($username) || $username == '' ) {
            COM_404();
        }

        $user = (int) $db->getItem(
                        $_TABLES['users'],
                        'uid',
                        array('username'=>$username),
                        array(Database::STRING)
                      );

        if ($user < 2) {
            COM_404();
        }
    } else {
        COM_404();
    }
    $msg = 0;
    if (isset ($_GET['msg'])) {
        $msg = COM_applyFilter ($_GET['msg'], true);
    }
    $plugin = '';

    if (($msg > 0) && isset($_GET['plugin'])) {
        $plugin = COM_applyFilter($_GET['plugin']);
    }

    $A = $db->conn->fetchAssoc(
                "SELECT u.uid,username,fullname,regdate,lastlogin,homepage,about,location,pgpkey,photo,email,status,emailfromadmin,emailfromuser,showonline
                 FROM `{$_TABLES['userinfo']}` AS ui,`{$_TABLES['userprefs']}` AS up,`{$_TABLES['users']}` AS u
                 WHERE ui.uid = u.uid AND ui.uid = up.uid AND u.uid = ?",
                array($user),
                array(Database::INTEGER)
    );
    if ($A === false || $A === null) {
        COM_404();
    }

    if ($A['status'] == USER_ACCOUNT_DISABLED && !SEC_hasRights ('user.edit')) {
        COM_displayMessageAndAbort (30, '', 403, 'Forbidden');
    }

    $display_name = @htmlspecialchars(COM_getDisplayName($user, $A['username'],$A['fullname']),ENT_COMPAT,COM_getEncodingt());

    if ($msg > 0) {
        $retval .= COM_showMessage($msg, $plugin,'',0,'info');
    }

    // format date/time to user preference
    $curtime = COM_getUserDateTimeFormat ($A['regdate']);
    $A['regdate'] = $curtime[0];

    $user_templates = new Template ($_CONF['path_layout'] . 'users');
    $user_templates->set_file (array ('profile' => 'profile.thtml',
                                      'email'   => 'email.thtml',
                                      'row'     => 'commentrow.thtml',
                                      'strow'   => 'storyrow.thtml'));
    $user_templates->set_var ('layout_url', $_CONF['layout_url']);
    $user_templates->set_var ('start_block_userprofile',
            COM_startBlock ($LANG04[1] . ' ' . $display_name));
    $user_templates->set_var ('end_block', COM_endBlock ());
    $user_templates->set_var ('lang_username', $LANG04[2]);
    $user_templates->set_var ('tooltip', COM_getTooltipStyle());

    if ($_CONF['show_fullname'] == 1) {
        if (empty ($A['fullname'])) {
            $username = $A['username'];
            $fullname = '';
        } else {
            $username = $A['fullname'];
            $fullname = $A['username'];
        }
    } else {
        $username = $A['username'];
        $fullname = '';
    }
    $username = @htmlspecialchars($username,ENT_COMPAT,COM_getEncodingt());
    $fullname = @htmlspecialchars($fullname,ENT_COMPAT,COM_getEncodingt());

    if ($A['status'] == USER_ACCOUNT_DISABLED) {
        $username = sprintf ('%s - %s', $username, $LANG28[42]);
        if (!empty ($fullname)) {
            $fullname = sprintf ('% - %s', $fullname, $LANG28[42]);
        }
    }

    $user_templates->set_var ('username', $username);
    $user_templates->set_var ('user_fullname', $fullname);

    if (SEC_hasRights('user.edit') || (isset($_USER['uid']) && $_USER['uid'] == $A['uid'])) {
        $edit_icon = '<img src="' . $_CONF['layout_url'] . '/images/edit.png'
                   . '" alt="' . $LANG_ADMIN['edit']
                   . '" title="' . $LANG_ADMIN['edit'] . '" />';
        if ($_USER['uid'] == $A['uid']) {
            $edit_url = "{$_CONF['site_url']}/usersettings.php";
        } else {
            $edit_url = "{$_CONF['site_admin_url']}/user.php?edit=x&amp;uid={$A['uid']}";
        }

        $edit_link_url = COM_createLink($edit_icon, $edit_url);
        $user_templates->set_var('edit_icon', $edit_icon);
        $user_templates->set_var('edit_link', $edit_link_url);
        $user_templates->set_var('user_edit', $edit_url);
    } else {
        $user_templates->set_var('user_edit', '');
    }

    if (isset ($A['photo']) && empty ($A['photo'])) {
        $A['photo'] = '(none)'; // user does not have a photo
    }

    $lastlogin = $A['lastlogin'];
    $lasttime = COM_getUserDateTimeFormat ($lastlogin);

    $photo = USER_getPhoto ($user, $A['photo'], $A['email'], -1,0);
    $user_templates->set_var ('user_photo', $photo);

    $user_templates->set_var ('lang_membersince', $LANG04[67]);
    $user_templates->set_var ('user_regdate', $A['regdate']);

    if ($_CONF['lastlogin'] && $A['showonline']) {
        $user_templates->set_var('lang_lastlogin', $LANG28[35]);
        if ( !empty($lastlogin) ) {
            $user_templates->set_var('user_lastlogin', $lasttime[0]);
        } else {
            $user_templates->set_var('user_lastlogin', $LANG28[36]);
        }
    }

    if ($A['showonline']) {
        if ( $db->getCount($_TABLES['sessions'],'uid',$user,Database::INTEGER)) {
            $user_templates->set_var ('online', 'online');
        }
    }

    $user_templates->set_var ('lang_email', $LANG04[5]);
    $user_templates->set_var ('user_id', $user);

    if ( $A['email'] == '' || $A['emailfromuser'] == 0 ) {
        $user_templates->set_var ('email_option', '');
    } else {
        $user_templates->set_var ('lang_sendemail', $LANG04[81]);
        $user_templates->parse ('email_option', 'email', true);
    }

    $user_templates->set_var ('lang_homepage', $LANG04[6]);
    $user_templates->set_var ('user_homepage', COM_killJS ($A['homepage']));
    $user_templates->set_var ('lang_location', $LANG04[106]);
    $user_templates->set_var ('user_location', strip_tags ($A['location']));
    $user_templates->set_var ('lang_online', $LANG04[160]);
    $user_templates->set_var ('lang_bio', $LANG04[7]);
    $user_templates->set_var ('user_bio', PLG_replaceTags(nl2br ($A['about']),'glfusion','about_user'));

    $user_templates->set_var('follow_me',\glFusion\Social\Social::getFollowMeIcons( $user, 'follow_user_profile.thtml' ));

    $user_templates->set_var ('lang_pgpkey', $LANG04[8]);
    $user_templates->set_var ('user_pgp', nl2br ($A['pgpkey']));
    $user_templates->set_var ('start_block_last10stories',
            COM_startBlock ($LANG04[82] . ' ' . $display_name));

    if (!isset($_CONF['comment_engine']) || $_CONF['comment_engine'] == 'internal') {
        $user_templates->set_var ('start_block_last10comments',
                COM_startBlock($LANG04[10] . ' ' . $display_name));
    }
    $user_templates->set_var ('start_block_postingstats',
            COM_startBlock ($LANG04[83] . ' ' . $display_name));
    $user_templates->set_var ('lang_title', $LANG09[16]);
    $user_templates->set_var ('lang_date', $LANG09[17]);

    // for alternative layouts: use these as headlines instead of block titles
    $user_templates->set_var ('headline_last10stories', $LANG04[82] . ' ' . $display_name);
    if (!isset($_CONF['comment_engine']) || $_CONF['comment_engine'] == 'internal') {
        $user_templates->set_var ('headline_last10comments', $LANG04[10] . ' ' . $display_name);
    }
    $user_templates->set_var ('headline_postingstats', $LANG04[83] . ' ' . $display_name);

    $tids = array ();
    $stmt = $db->conn->query(
                "SELECT tid FROM `{$_TABLES['topics']}`" . $db->getPermSQL()
    );
    while ($T = $stmt->fetch(Database::ASSOCIATIVE)) {
        $tids[] = $T['tid'];
    }
    $topics = "'" . implode ("','", $tids) . "'";

    // list of last 10 stories by this user
    if (count($tids) > 0) {
        $sql = "SELECT sid,title,UNIX_TIMESTAMP(date) AS unixdate
                 FROM `{$_TABLES['stories']}` WHERE (uid = ?)
                       AND (draft_flag = 0)
                       AND (date <= ?)
                       AND (tid IN (?))" . $db->getPermSQL('AND') .
                " ORDER BY unixdate DESC LIMIT 10";

        $stmt = $db->conn->executeQuery(
                    $sql,
                    array(
                        $user,
                        $_CONF['_now']->toMySQL(true),
                        $tids
                    ),
                    array(
                        Database::INTEGER,
                        Database::STRING,
                        Database::PARAM_STR_ARRAY
                    )
        );
        $last10Stories = $stmt->fetchAll(Database::ASSOCIATIVE);
    } else {
        $last10Stories = array();
    }
    $i = 0;
    if (count($last10Stories) > 0) {
        foreach($last10Stories AS $C) {
            $user_templates->set_var ('cssid', ($i % 2) + 1);
            $user_templates->set_var ('row_number', ($i + 1) . '.');
            $articleUrl = COM_buildUrl ($_CONF['site_url'].'/article.php?story=' . $C['sid']);
            $user_templates->set_var ('article_url', $articleUrl);
            $C['title'] = str_replace ('$', '&#36;', $C['title']);
            $user_templates->set_var ('story_title',
                COM_createLink(
                    $C['title'],
                    $articleUrl,
                    array ('class'=>''))
            );
            $storytime = COM_getUserDateTimeFormat ($C['unixdate']);
            $user_templates->set_var ('story_date', $storytime[0]);
            $user_templates->parse ('story_row', 'strow', true);
            $i++;
        }
    } else {
        $user_templates->set_var ('story_row','<tr><td>' . $LANG01[37] . '</td></tr>');
    }
    if (!isset($_CONF['comment_engine']) || $_CONF['comment_engine'] == 'internal') {
        $commentCounter = 0;

        $stmt = $db->conn->executeQuery(
                    "SELECT * FROM `{$_TABLES['comments']}`
                     WHERE uid = ? AND queued=0 ORDER BY date DESC",
                    array($user),
                    array(Database::INTEGER)
        );

        while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
            if ( $commentCounter >= 10 ) {
                break;
            }
            $itemInfo = PLG_getItemInfo($row['type'], $row['sid'],'id');
            if ( is_array($itemInfo) || $itemInfo == '' ) {
                continue;
            }
            $user_templates->set_var ('cssid', ($commentCounter % 2) + 1);
            $user_templates->set_var ('row_number', ($commentCounter + 1) . '.');
            $row['title'] = html_entity_decode(str_replace ('$', '&#36;', $row['title']));
            $comment_url = $_CONF['site_url'] .
                    '/comment.php?mode=view&amp;cid=' . $row['cid'] . '#comments';
            $user_templates->set_var ('comment_title',
                COM_createLink(
                    $row['title'],
                    $comment_url,
                    array ('class'=>''))
            );
            $commenttime = COM_getUserDateTimeFormat ($row['date']);
            $user_templates->set_var ('comment_date', $commenttime[0]);
            $user_templates->parse ('comment_row', 'row', true);
            $commentCounter++;
        }
        if ( $commentCounter == 0 ) {
            $user_templates->set_var('comment_row','<tr><td>' . $LANG01[29] . '</td></tr>');
        }
    }

    // posting stats for this user
    $user_templates->set_var ('lang_number_stories', $LANG04[84]);

    $storyCount = (int) $db->conn->fetchColumn(
                    "SELECT COUNT(*) AS count
                     FROM `{$_TABLES['stories']}`
                     WHERE (uid = ?) AND (draft_flag = 0) AND (date <= ?)" . $db->getPermSQL('AND'),
                    array($user,$_CONF['_now']->toMySQL(true)),
                    0,
                    array(Database::INTEGER,Database::STRING)
    );
    $user_templates->set_var ('number_stories', COM_numberFormat($storyCount));
    if (!isset($_CONF['comment_engine']) || $_CONF['comment_engine'] == 'internal') {
        $user_templates->set_var ('lang_number_comments', $LANG04[85]);

        $commentCount = (int) $db->conn->fetchColumn(
                    "SELECT COUNT(*) AS count FROM `{$_TABLES['comments']}`
                     WHERE (queued = 0 AND uid = ?)",
                    array($user),
                    0,
                    array(Database::INTEGER)
        );
        $user_templates->set_var ('number_comments', COM_numberFormat($commentCount));
    }
    $user_templates->set_var ('lang_all_postings_by',
                              $LANG04[86] . ' ' . $display_name);
    // hook to the profile icon display

    $profileIcons = PLG_profileIconDisplay($user);
    if ( is_array($profileIcons) && count($profileIcons) > 0 ) {
	    $user_templates->set_block('profile', 'profileicon', 'pi');
        for ($x=0;$x<count($profileIcons);$x++) {
            if ( isset($profileIcons[$x]['url']) && $profileIcons[$x]['url'] != '' && isset($profileIcons[$x]['icon']) && $profileIcons[$x]['icon'] != '' ) {
                $user_templates->set_var('profile_icon_url',$profileIcons[$x]['url']);
                $user_templates->set_var('profile_icon_icon',$profileIcons[$x]['icon']);
                $user_templates->set_var('profile_icon_text',$profileIcons[$x]['text']);
                $user_templates->parse('pi', 'profileicon',true);
            }
        }
    }

    // Call custom registration function if enabled and exists
    if ($_CONF['custom_registration'] && function_exists ('CUSTOM_userDisplay') ) {
        $user_templates->set_var ('customfields', CUSTOM_userDisplay ($user));
    }
    PLG_profileVariablesDisplay ($user, $user_templates);

    $user_templates->parse ('output', 'profile');
    $retval .= $user_templates->finish ($user_templates->get_var ('output'));

    $retval .= PLG_profileBlocksDisplay ($user);

    return $retval;
}

/**
* Emails password to a user
*
* This will email the given user their password.
*
* @param    string      $username       Username for which to get and email password
* @param    string      $passwd         Unencrypted password (optional)
* @param    int         $msg            Message number of message to show when done
* @return   string      Optionally returns the HTML for the default form if the user info can't be found
*
*/
function emailpassword ($username, $passwd = '', $msg = 0)
{
    global $_CONF, $_TABLES, $LANG04;

    $retval = '';

    $db = Database::getInstance();

    // don't retrieve any remote users!
    $A = $db->conn->fetchAssoc(
            "SELECT uid,email,status FROM `{$_TABLES['users']}`
             WHERE username = ? AND (account_type & ".LOCAL_USER.")",
            array($username),
            array(Database::STRING)
    );

    if ($A !== false && $A !== null) {
        if (($_CONF['usersubmission'] == 1) && ($A['status'] == USER_ACCOUNT_AWAITING_APPROVAL)) {
            echo COM_refresh ($_CONF['site_url'] . '/index.php?msg=48');
        }

        $mailresult = USER_createAndSendPassword ($username, $A['email'], $A['uid'],$passwd);

        if ($mailresult == false) {
            echo COM_refresh ("{$_CONF['site_url']}/index.php?msg=85");
        } else if ($msg) {
            echo COM_refresh ("{$_CONF['site_url']}/index.php?msg=$msg");
        } else {
            if ($_CONF['registration_type'] == 1 ) {
                echo COM_refresh ("{$_CONF['site_url']}/index.php?msg=3");
            } else {
                echo COM_refresh ("{$_CONF['site_url']}/index.php?msg=1");
            }
        }
    } else {
        $retval = defaultform ('');
    }

    return $retval;
}

/**
* User request for a new password - send email with a link and request id
*
* @param username string   name of user who requested the new password
* @param msg      int      index of message to display (if any)
* @return         string   form or meta redirect
*
*/
function requestpassword ($username, $msg = 0)
{
    global $_CONF, $_TABLES, $LANG04;

    $db = Database::getInstance();

    $retval = '';

    // no remote users!
    $A = $db->conn->fetchAssoc(
            "SELECT uid,email,passwd,status FROM `{$_TABLES['users']}`
             WHERE username = ? AND (account_type & ".LOCAL_USER.")",
            array($username),
            array(Database::STRING)
    );

    if ($A !== false && $A !== null) {
        if (($_CONF['usersubmission'] == 1) && ($A['status'] == USER_ACCOUNT_AWAITING_APPROVAL)) {
            echo COM_refresh ($_CONF['site_url'] . '/index.php?msg=48');
        }
        $reqid = substr (md5 (uniqid (rand (), 1)), 1, 16);

        $db->conn->update(
                $_TABLES['users'],
                array('pwrequestid' => $reqid),
                array('uid' => $A['uid']),
                array(Database::STRING, Database::INTEGER)
        );

        $T = new Template($_CONF['path_layout'].'email/');
        $T->set_file(array(
            'html_msg'   => 'mailtemplate_html.thtml',
            'text_msg'   => 'mailtemplate_text.thtml'
        ));

        $T->set_block('html_msg', 'content', 'contentblock');
        $T->set_block('text_msg', 'contenttext', 'contenttextblock');

        $T->set_var('content_text',sprintf ($LANG04[88], $username,$_CONF['site_name'],$_CONF['site_url']) );

        $T->parse('contentblock', 'content',true);
        $T->parse('contenttextblock', 'contenttext',true);

        $T->set_var('url',$_CONF['site_url'] . '/users.php?mode=newpwd&uid=' . $A['uid'] . '&rid=' . $reqid);
        $T->set_var('button_text',$LANG04[91]);
        $T->parse('contentblock', 'content',true);
        $T->parse('contenttextblock', 'contenttext',true);

        $T->unset_var('button_text');
        $T->set_var('content_text',$LANG04[89]);
        $T->parse('contentblock', 'content',true);
        $T->parse('contenttextblock', 'contenttext',true);

        $T->set_var('site_url',$_CONF['site_url']);
        $T->set_var('site_name',$_CONF['site_name']);
        $T->set_var('title',$_CONF['site_name'] . ': ' . $LANG04[16]);
        $T->parse ('output', 'html_msg');
        $mailhtml = $T->finish($T->get_var('output'));

        $T->parse ('textoutput', 'text_msg');
        $mailtext = $T->finish($T->get_var('textoutput'));

        $msgData['htmlmessage'] = $mailhtml;
        $msgData['textmessage'] = $mailtext;
        $msgData['subject'] = $_CONF['site_name'] . ': ' . $LANG04[16];

        $msgData['from']['name'] = $_CONF['site_name'];
        $msgData['from']['email'] = $_CONF['noreply_mail'];
        $msgData['to']['email'] = $A['email'];
        $msgData['to']['name'] = $username;

        COM_emailNotification($msgData);

        COM_updateSpeedlimit ('password');

        if ($msg) {
            echo COM_refresh ($_CONF['site_url'] . "/index.php?msg=$msg");
        } else {
            echo COM_refresh ($_CONF['site_url'] . '/index.php');
        }
    } else {
        COM_updateSpeedlimit ('password');
        echo COM_refresh ($_CONF['site_url'].'/users.php?mode=getpassword');
        exit;
    }

    return $retval;
}

/**
* Display a form where the user can enter a new password.
*
* @param uid       int      user id
* @param requestid string   request id for password change
* @return          string   new password form
*
*/
function newpasswordform ($uid, $requestid)
{
    global $_CONF, $_TABLES, $LANG04;

    $db = Database::getInstance();

    $pwform = new Template ($_CONF['path_layout'] . 'users');
    $pwform->set_file ('newpw','newpassword.thtml');
    $pwform->set_var (array(
            'user_id'       => $uid,
            'user_name'     => $db->getItem ($_TABLES['users'], 'username',array('uid' => $uid),array(Database::INTEGER)),
            'request_id'    => $requestid,
            'password_help' => SEC_showPasswordHelp(),
            'lang_explain'  => $LANG04[90],
            'lang_username' => $LANG04[2],
            'lang_newpassword'  => $LANG04[4],
            'lang_newpassword_conf' => $LANG04[108],
            'lang_setnewpwd'    => $LANG04[91]));

    $retval = COM_startBlock ($LANG04[92]);
    $retval .= $pwform->finish ($pwform->parse ('output', 'newpw'));
    $retval .= COM_endBlock ();

    return $retval;
}

/**
* User request for a verification token - send email with a link and request id
*
* @param uid      int      userid of user who requested the new token
* @param msg      int      index of message to display (if any)
* @return         string   form or meta redirect
*
*/
function requesttoken ($uid, $msg = 0)
{
    global $_CONF, $_SYSTEM, $_TABLES, $LANG04;

    $db = Database::getInstance();

    if ( !isset($_SYSTEM['verification_token_ttl']) ) {
        $_SYSTEM['verification_token_ttl'] = 86400;
    }

    $retval = '';
    $uid = (int) $uid;

    $A = $db->conn->fetchAssoc(
                "SELECT uid,username,email,passwd,status
                 FROM `{$_TABLES['users']}`
                 WHERE uid = ? AND (account_type & ".LOCAL_USER.")",
                array($uid),
                array(Database::INTEGER)
    );
    if ($A !== false && $A !== null) {
        if (($_CONF['usersubmission'] == 1) && ($A['status'] == USER_ACCOUNT_AWAITING_APPROVAL)) {
            echo COM_refresh ($_CONF['site_url'] . '/index.php?msg=48');
        }
        $verification_id = USER_createActivationToken($uid,$A['username']);
        $activation_link = $_CONF['site_url'].'/users.php?mode=verify&vid='.$verification_id.'&u='.$uid;

        $T = new Template($_CONF['path_layout'].'email/');
        $T->set_file(array(
            'html_msg'   => 'newuser_template_html.thtml',
            'text_msg'   => 'newuser_template_text.thtml'
        ));

        $T->set_var(array(
            'url'                   => $_CONF['site_url'].'/users.php?mode=verify&vid='.$verification_id.'&u='.$uid,
            'lang_site_or_password' => $LANG04[171],
            'site_link_url'         => $_CONF['site_url'],
            'lang_activation'       => sprintf($LANG04[172],($_SYSTEM['verification_token_ttl']/3600)),
            'lang_button_text'      => $LANG04[203],
            'title'                 =>  $_CONF['site_name'] . ': ' . $LANG04[16],
            'site_name'             =>  $_CONF['site_name'],
            'username'              =>  $A['username'],
        ));
        $T->parse ('output', 'html_msg');
        $mailhtml = $T->finish($T->get_var('output'));

        $T->parse ('output', 'text_msg');
        $mailtext = $T->finish($T->get_var('output'));

        $msgData['htmlmessage'] = $mailhtml;
        $msgData['textmessage'] = $mailtext;
        $msgData['subject'] = $_CONF['site_name'] . ': ' . $LANG04[16];

        $to = array();
        $from = array();
        $from = COM_formatEmailAddress( $_CONF['site_name'], $_CONF['noreply_mail'] );
        $to = COM_formatEmailAddress('',$A['email']);

        COM_mail( $to, $msgData['subject'], $msgData['htmlmessage'], $from, true, 0,'', $msgData['textmessage'] );

        COM_updateSpeedlimit ('verifytoken');
        if ($msg) {
            echo COM_refresh ($_CONF['site_url'] . "/index.php?msg=$msg");
        } else {
            echo COM_refresh ($_CONF['site_url'] . '/index.php');
        }
    } else {
        COM_updateSpeedlimit ('verifytoken');
        echo COM_refresh ($_CONF['site_url'] .'/users.php?mode=getnewtoken' );
    }
    return $retval;
}

/**
* Display a form where the user can request a new token.
*
* @param uid       int      user id
* @return          string   new token form
*
*/
function newtokenform ($uid)
{
    global $_CONF, $_TABLES, $LANG01, $LANG04;

    $tokenform = new Template ($_CONF['path_layout'] . 'users');
    $tokenform->set_file ('newtoken', 'newtoken.thtml');
    $tokenform->set_var (array(
            'user_id'       => $uid,
            'lang_explain'  => $LANG04[175],
            'lang_username' => $LANG04[2],
            'lang_password' => $LANG01[57],
            'lang_submit'   => $LANG04[169]));

    $retval = COM_startBlock ($LANG04[169]);
    $retval .= $tokenform->finish ($tokenform->parse ('output', 'newtoken'));
    $retval .= COM_endBlock ();

    return $retval;
}

/**
* Creates a user
*
* Creates a user with the give username and email address
*
* @param    string      $username       username to create user for
* @param    string      $email          email address to assign to user
* @param    string      $email_conf     confirmation email address check
* @param    string      $passwd         password
* @param    string      $passwd_conf    confirmation password check
* @return   string      HTML for the form again if error occurs, otherwise nothing.
*
*/
function USER_createuser($info = array())
{
    global $_CONF, $_TABLES, $LANG01, $LANG04, $MESSAGE;

    $retval = '';
    $validationErrors = 0;
    $errorMessages = array();

    $db = Database::getInstance();

    if (!isset ($_CONF['disallow_domains'])) {
        $_CONF['disallow_domains'] = '';
    }

    // if new user registration is disabled - abort
    if ($_CONF['disable_new_user_registration']) {
        COM_setMsg($LANG04[122],'error');
        COM_404();
    }
    // do not want re-auth option

    if ( !_sec_checkToken()) {
        echo COM_refresh($_CONF['site_url'].'/users.php?msg=523');
    }

    // defaults

    $data['regtype']        = 'local';  // registration type - local or oauth
    $data['username']       = '';       // contains the username for the glFusion site
    $data['email']          = '';       // user's email address
    $data['email_conf']     = '';       // always defaults to blank
    $data['passwd']         = '';       // user's password
    $data['passwd_conf']    = '';       // always defaults to blank
    $data['fullname']       = '';       // user's fullname
    $data['oauth_provider'] = '';       // oauth provider (i.e.; oauth.twitter, oauth.facebook, etc.)
    $data['oauth_username'] = '';       // oauth username
    $data['oauth_email']    = '';
    $data['oauth_service']  = '';

    // submitted data

    $data['regtype']    = isset($info['regtype']) && $info['regtype'] != '' ? $info['regtype'] : 'local';

    $data['username']   = isset($info['username']) ? $info['username'] : '';

    $data['email']      = isset($info['email']) ? filter_var($info['email'],FILTER_SANITIZE_EMAIL) : '';
    $data['email_conf'] = isset($info['email_conf']) ? filter_var($info['email_conf'],FILTER_SANITIZE_EMAIL) : '';

    $data['passwd']      = isset($info['passwd']) ? COM_applyFilter ($info['passwd']) : '';
    $data['passwd_conf'] = isset($info['passwd_conf']) ? COM_applyFilter ($info['passwd_conf']) : '';

    $data['fullname']    = isset($info['fullname']) ? $info['fullname'] : '';

    $data['oauth_provider'] = isset($info['oauth_provider']) ? $info['oauth_provider'] : '';
    $data['oauth_service']  = isset($info['oauth_service']) ? $info['oauth_service'] : '';
    $data['oauth_username'] = isset($info['oauth_username']) ? $info['oauth_username'] : '';
    $data['oauth_email']    = isset($info['oauth_email']) ? filter_var($info['oauth_email'],FILTER_SANITIZE_EMAIL) : '';

    // data cleanup and validations

    // username
    $data['username'] = COM_truncate(trim($data['username']),48);

    if (empty($data['username'])) {
        $validationErrors++;
        $errorMessages[] = $LANG01[32];
    } else if ( !USER_validateUsername($data['username'])) {
        $validationErrors++;
        $errorMessages[] = sprintf($LANG04[162],$_CONF['min_username_length']);
    }

    // email
    $data['email'] = COM_truncate(trim($data['email']),96);
    $data['email_conf'] = COM_truncate(trim($data['email_conf']),96);

    if ( empty($data['oauth_email']) ) {
        if ($data['email'] !== $data['email_conf']) {
            $validationErrors++;
            $errorMessages[] = $LANG04[125];
        } else if (!COM_isEmail($data['email'])) {
            $validationErrors++;
            $errorMessages[] = $LANG04[18];
        } else if ( USER_emailMatches($data['email'],$_CONF['disallow_domains'])) {
            $validationErrors++;
            $errorMessages[] = $LANG04[18];
        }
    } else {
        $data['email'] = $data['oauth_email'];
    }

    $ucount = $ecount = 0;

    $ucount = $db->getCount($_TABLES['users'], 'username',$data['username'],Database::STRING);
    if ( $ucount != 0 ) {
        $validationErrors++;
        $errorMessages[] = $LANG04[19];
    }

    if ( $data['regtype'] == 'local' || $data['regtype'] == '' ) {
        $ecount = $db->getCount($_TABLES['users'], 'email', $data['email'], Database::STRING);
        if ( $ecount != 0 ) {
            $validationErrors++;
            $errorMessages[] = $LANG04[19];
        }
    }

    // passwd
    $data['passwd'] = trim($data['passwd']);
    $data['passwd_conf'] = trim($data['passwd_conf']);

    if ($data['regtype'] == 'local' && $_CONF['registration_type'] == 1 ) {
        if ( empty($data['passwd']) || $data['passwd'] != $data['passwd_conf'] ) {
            $validationErrors++;
            $errorMessages[] = $MESSAGE[67];
        } else {
            $err = SEC_checkPwdComplexity($data['passwd']);
            if (count($err) > 0 ) {
                $validationErrors++;
                $errorMessages[] = implode('<br>',$err);
            }
        }
    }

    $data['fullname'] = COM_truncate(trim(USER_sanitizeName($data['fullname'])),80);

    if ( $_CONF['user_reg_fullname'] == 2) {
        if (empty($data['fullname'])) {
            $validationErrors++;
            $errorMessages[] = 'Please enter full name';
        }
    }

    $spamCheckData = array(
        'username'  => $data['username'],
        'email'     => $data['email'],
        'ip'        => $_SERVER['REAL_ADDR'],
        'type'      => 'registration');

    $msg = PLG_itemPreSave ('registration', $spamCheckData);
    if (!empty ($msg)) {
        $validationErrors++;
        $errorMessages[] = $msg;
    }

    // do our spam check
    $result = PLG_checkforSpam($data['username'], $_CONF['spamx'],$spamCheckData);
    if ($result > 0) {
        COM_displayMessageAndAbort($result, 'spamx', 403, 'Forbidden');
    }

    // is there a custom user check
    if ($_CONF['custom_registration'] && function_exists ('CUSTOM_userCheck')) {
        $msg = CUSTOM_userCheck ($username, $email);
        if (is_array($msg)) {
            $validationErrors += count($msg);
            array_merge($errorMessages, $msg);
        } else {
            $validationErrors++;
            array_push($errorMessages, $msg);
        }
        if (!empty ($msg)) {
            if (function_exists('CUSTOM_userForm')) {
                return CUSTOM_userForm ($msg);
            }
            // else, fall through to display the stock registration form
        }
    }

    if ( $validationErrors > 0 ) {
        // we cannot proceed - we have issues to resolve
        return USER_registrationForm($data,$errorMessages);
    }

    //
    // All validations have passed - we need to now create the user
    // based on if it is a local user or a oauth user
    //

    $mergeAccount = 0;
    $remoteUID = '';
    $localUID = '';

    if ( $data['regtype'] == 'local' || $data['regtype'] == '' ) {
        if ( $_CONF['registration_type'] == 1 && !empty($data['passwd']) ) {
            $encryptedPasswd = SEC_encryptPassword($data['passwd']);
        } else {
            $encryptedPasswd = '';
        }
        $uid = USER_createAccount ($data['username'], $data['email'], $encryptedPasswd, $data['fullname']);

        if ($_CONF['usersubmission'] == 1) {
            if ((int) $db->getItem ($_TABLES['users'],'status',array('uid' => $uid),array(Database::INTEGER)) == USER_ACCOUNT_AWAITING_APPROVAL) {
               echo COM_refresh ($_CONF['site_url'].'/index.php?msg=48');
            } else {
                $retval = emailpassword ($data['username'], $data['passwd'], 1);
            }
        } else {
            $retval = emailpassword ($data['username'],$data['passwd']);
        }
    } else {
        // oauth user
        $users = SESS_getVar('users');
        $userinfo = SESS_getVar('userinfo');

        if ( !isset($users['homepage']) ) $users['homepage'] = '';
        $users['homepage'] = COM_truncate($users['homepage'],255);

//@TODO - fix the var names
        $uid = USER_createAccount($data['username'], $data['email'], '', $data['fullname'], $users['homepage'], $users['remoteusername'], $users['remoteservice']);
//@TODO should probably display an error
        if ( $uid == NULL ) {
            Log::write('system',Log::ERROR,"USER_createAccount() failed to return valid UID");
            echo COM_refresh($_CONF['site_url']);
        }

        $oauth = new OAuthConsumer($info['oauth_service']);

        if (is_array($users)) {
            $oauth->_DBupdate_users($uid, $users);
        }
        if (is_array($userinfo)) {
            $oauth->_DBupdate_userinfo($uid, $userinfo);
        }

        $status = $db->getItem($_TABLES['users'],'status',array('uid' => $uid),array(Database::INTEGER));
        $remote_grp = $db->getItem($_TABLES['groups'], 'grp_id', array('grp_name' => 'Remote Users'), array(Database::STRING));

        $db->conn->insert(
                $_TABLES['group_assignments'],
                array(
                    'ug_main_grp_id' => $remote_grp,
                    'ug_uid' => $uid
                ),
                array(
                    Database::INTEGER,
                    Database::INTEGER
                )
        );

        if ( isset($users['socialuser']) ) {
            $social_row = $db->conn->fetchAssoc(
                            "SELECT * FROM `{$_TABLES['social_follow_services']}`
                             WHERE service_name=? AND enabled=1",
                            array($users['socialservice']),
                            array(Database::STRING)
            );
            if ($social_row !== false && $social_row !== null) {
                $sql  = "REPLACE INTO `{$_TABLES['social_follow_user']}` (ssid,uid,ss_username)
                         VALUES (?, ?, ?)";
                try {
                    $db->conn->executeUpdate(
                            $sql,
                            array(
                                $social_row['ssid'],
                                $uid,
                                $users['socialuser']
                            ),
                            array(
                                Database::STRING,
                                Database::INTEGER,
                                Database::STRING
                            )
                    );
                } catch(Throwable $e) {
                    // ignore error
                }
            }
        }

        // check and see if we need to merge the account
        if (isset($data['email']) && $data['email'] != '') {
            $row = $db->conn->fetchAssoc(
                        "SELECT * FROM `{$_TABLES['users']}`
                         WHERE account_type = ".LOCAL_USER." AND email=? AND uid > 1",
                        array($data['email']),
                        array(Database::STRING)
            );
            if ($row !== false && $row !== null) {
                $remoteUID = $uid;
                $localUID  = $row['uid'];
                $mergeAccount = 1;
            }
        }
    }

    if ($_CONF['usersubmission'] == 1) {
        if ((int) ($db->getItem ($_TABLES['users'], 'status', array('uid' =>  $uid),Database::INTEGER)) == USER_ACCOUNT_AWAITING_APPROVAL) {
            echo COM_refresh ($_CONF['site_url'] . '/index.php?msg=48');
        }
    }

    if ( $uid != null ) {
        SESS_completeLogin($uid,0);
    }

    if ( $mergeAccount ) {
        USER_mergeAccountScreen($remoteUID, $localUID);
    }

    echo COM_refresh($_CONF['site_url']);
}

/**
* Shows the user login form after failed attempts to either login or access a page
* requiring login.
*
* @return   string      HTML for login form
*
*/
function loginform ($hide_forgotpw_link = false, $statusmode = -1)
{
    global $_CONF, $LANG04;

    $options = array(
        'hide_forgotpw_link' => $hide_forgotpw_link,
        'form_action'        => $_CONF['site_url'].'/users.php',
    );
    if (isset($_POST['login_landing'])) {
        $options['login_landing'] = $_POST['login_landing'];
    } elseif (isset($_GET['landing'])) {
        $options['login_landing'] = $_GET['landing'];
    }

    if ($statusmode == USER_ACCOUNT_DISABLED) {
        $options['title']   = $LANG04[114]; // account disabled
        $options['message'] = $LANG04[115]; // your account has been disabled, you may not login
        $options['forgotpw_link']      = false;
        $options['newreg_link']        = false;
        $options['verification_link']  = false;
    } elseif ($statusmode == USER_ACCOUNT_AWAITING_APPROVAL) {
        $options['title']   = $LANG04[116]; // account awaiting activation
        $options['message'] = $LANG04[117]; // your account is currently awaiting activation by an admin
        $options['forgotpw_link']      = false;
        $options['newreg_link']        = false;
        $options['verification_link']  = false;
    } elseif ($statusmode == USER_ACCOUNT_AWAITING_VERIFICATION ) {
        $options['title']   = $LANG04[116]; // account awaiting activation
        $options['message'] = $LANG04[177]; // your account is currently awaiting verification
        $options['forgotpw_link']      = false;
        $options['newreg_link']        = false;
        $options['verification_link']  = true;
    } elseif ($statusmode == -1) { // invalid credentials
        $options['title']   = sprintf($LANG04[65],$_CONF['site_name']); // log in to {site_name}
        $options['message'] = $LANG04[113]; // login attempt failed
    } else {
        $options['title']   = sprintf($LANG04[65],$_CONF['site_name']); // log in to {site_name}
        $options['message'] = ''; // $LANG04[66]; // please enter your user name and password below
    }

    return SEC_loginForm($options);
}

/**
* Shows the user registration form
*
* @param    int     $msg        message number to show
* @param    string  $referrer   page to send user to after registration
* @return   string  HTML for user registration page
*/
function USER_registrationForm($info = array(), $messages = array())
{
    global $_CONF, $_USER, $LANG01, $LANG04;

    $retval = '';

    // if user is already logged in - take them to their profile page
    if ( !COM_isAnonUser() ) {
        echo COM_refresh($_CONF['site_url'].'/users.php?mode=profile&uid='.$_USER['uid']);
    }

    // if new user registration is disabled - abort
    if ($_CONF['disable_new_user_registration']) {
        COM_setMsg($LANG04[122],'error');
        echo COM_refresh($_CONF['site_url']);
        exit;
    }

    // Hook into Custom Registration
    if ($_CONF['custom_registration'] AND (function_exists('CUSTOM_userForm'))) {
        return CUSTOM_userForm();
    }

    // defaults
    $options = array(
        'oauth_login'       => false,
        'show_password_entry' => true,    // dependent on registration type
        'show_email_confirmation' => true,
        'show_fullname'     => false,
        'plugin_vars'       => true,
        'registration_type' => 1,
        'form_action'       => $_CONF['site_url'].'/users.php',
    );

    // registration type can be
    //  0 - email user their password
    //  1 - user enters password and receives verification link
    if ( (isset($info['oauth_provider']) && !empty($info['oauth_provider']) ) || $_CONF['registration_type'] == 0 ) {
        $options['show_password_entry'] = false;
    }

    if ( (isset($info['oauth_provider']) && !empty($info['oauth_provider'])) && (isset($info['oauth_email']) && !empty($info['oauth_email'])) ) {
        $options['show_email_confirmation'] = false;
    }

    if ( !isset($info['oauth_service'])) $info['oauth_service'] = '';

    // full name
    // 0 = no
    // 1 = optional
    // 2 = required
    if ( $_CONF['user_reg_fullname'] == 1 || $_CONF['user_reg_fullname'] == 2 ) {
        $options['show_fullname'] = true;
    }
    if ($_CONF['user_reg_fullname'] == 2) {
        $options['require_fullname'] = true;
    }

    $T = new Template($_CONF['path_layout'].'users');
    $T->set_file('regform', 'registrationform.thtml');

    $T->set_var('form_action',$options['form_action']);

    $T->set_var(array(
        'lang_instructions' => sprintf($LANG04[23],$_CONF['site_name']),
        'lang_username'     => $LANG04[2],
        'lang_fullname'     => $LANG04[3],
        'lang_email'        => $LANG04[5],
        'lang_email_conf'   => $LANG04[124],
        'lang_register'     => $LANG04[27],
        'lang_passwd'       => $LANG01[57],
        'lang_passwd_conf'  => $LANG04[176],
        'lang_oauth_heading' => $LANG04[208],
        'lang_local_heading' => $LANG04[211],
        'lang_info_oauth'   => $LANG04[209],
        'lang_password_help'=> SEC_showPasswordHelp(),  // dynamic based on password rules
        'site_name'         => $_CONF['site_name'],
        'sec_token'         => SEC_createToken(),
        'sec_token_name'    => CSRF_TOKEN,
    ));

    if ( isset($info['oauth_provider']) && !empty($info['oauth_provider']) ) {
        $T->set_var('lang_action',sprintf($LANG04[210],$_CONF['site_name'],$LANG04[$info['oauth_service']]));
        $T->set_var('oauth_login',true);
    }

    if ( $_CONF['registration_type'] == 1 ) { // verification link
        $T->set_var('lang_warning', $LANG04[167]);
    } else {
        $T->set_var('lang_warning', $LANG04[24]);
    }

    if ($options['show_fullname']) {
        $T->set_var('show_fullname',true);
    }
    if ($_CONF['user_reg_fullname'] == 2) {
        $T->set_var('require_fullname',true);
    }
    if ($options['show_email_confirmation']) {
        $T->set_var('show_email_confirmation',true);
    }

    // registration type can be
    //  0 - email user their password
    //  1 - user enters password and receives verification link

    if ($options['show_password_entry']) {
        $T->set_var('show_password_entry',true);
    }

    foreach($info AS $item => $value) {
        $T->set_var($item,$value);
    }

    // Plugin Hook
    if ( $options['plugin_vars'] ) {
        PLG_templateSetVars('registration', $T);
    }

    // display any error messages
    $T->set_var('feedback',implode('<br>',$messages));

    $T->parse('output', 'regform');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

/**
* Shows the password retrieval form
*
* @return   string  HTML for form used to retrieve user's password
*
*/
function getpasswordform()
{
    global $_CONF, $LANG04;

    $retval = '';

    $user_templates = new Template($_CONF['path_layout'] . 'users');
    $user_templates->set_file('form', 'getpasswordform.thtml');
    $user_templates->set_var(array(
        'start_block_forgetpassword'    => COM_startBlock($LANG04[25]),
        'lang_instructions'             => $LANG04[26],
        'lang_username'                 => $LANG04[2],
        'lang_email'                    => $LANG04[5],
        'lang_emailpassword'            => $LANG04[28],
        'end_block'                     => COM_endBlock()
    ));

    PLG_templateSetVars('forgotpassword',$user_templates);

    $user_templates->parse('output', 'form');

    $retval .= $user_templates->finish($user_templates->get_var('output'));

    return $retval;
}

/**
* Account does not exist - show both the login and register forms
*
* @param    string  $msg        message to display if one is needed
* @return   string  HTML for form
*
*/
function defaultform ($msg)
{
    global $LANG04, $_CONF;

    $retval = '';

    if (!empty ($msg)) {
        $retval .= COM_showMessageText($msg,$LANG04[21],false,'info');
    }

    $retval .= loginform (true);

    if ( $_CONF['disable_new_user_registration'] == FALSE ) {
        $retval .= USER_registrationForm ();
    }

    $retval .= getpasswordform ();

    return $retval;
}

/**
* Display message after a login error
*
* @param    int     $msg            message number for custom handler
* @param    string  $message_title  title for the message box
* @param    string  $message_text   text of the message box
* @return   void                    function does not return!
*
*/
function displayLoginErrorAndAbort($msg, $message_title, $message_text)
{
    global $_CONF;

    if ($_CONF['custom_registration'] &&
            function_exists('CUSTOM_loginErrorHandler')) {
        // Typically this will be used if you have a custom main site page
        // and need to control the login process
        CUSTOM_loginErrorHandler($msg);
    } else {
        @header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
        @header('Status: 403 Forbidden');
        $retval = COM_siteHeader('menu', $message_title)
                . COM_showMessageText($message_text,$message_title,false,'error')
                . COM_siteFooter();
        echo $retval;
    }
    // don't return
    exit();
}


/**
* Log user out
*
* This logs the user out of the system and clears all session vars
*
* @return   none    Redirects user to index page
*
*/
function userLogout()
{
    global $_CONF, $_TABLES, $_USER, $_COOKIE;

    $db = Database::getInstance();

    if (!empty ($_USER['uid']) AND $_USER['uid'] > 1) {
        try {
            $db->conn->update(
                        $_TABLES['users'],
                        array('remote_ip' => ''),
                        array('uid' => $_USER['uid'])
            );
        } catch(Throwable $e) {
            // ignore any errors
        }
        SESS_endUserSession ($_USER['uid']);
        PLG_logoutUser ($_USER['uid']);
    }
    SEC_setCookie ($_CONF['cookie_session'], '', time() - 10000,
                   $_CONF['cookie_path'], $_CONF['cookiedomain'],
                   $_CONF['cookiesecure'],true);
    SEC_setCookie ($_CONF['cookie_password'], '', time() - 10000,
                   $_CONF['cookie_path'], $_CONF['cookiedomain'],
                   $_CONF['cookiesecure'],true);
    SEC_setCookie ($_CONF['cookie_name'], '', time() - 10000,
                   $_CONF['cookie_path'], $_CONF['cookiedomain'],
                   $_CONF['cookiesecure'],true);
    if ( isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];
        $db->conn->delete(
                    $_TABLES['tokens'],
                    array('token' => $token),
                    array(Database::STRING)
        );
        SEC_setCookie ('token', '', time() - 10000,
                       $_CONF['cookie_path'], $_CONF['cookiedomain'],
                       $_CONF['cookiesecure'],true);
    }
    $db->conn->delete($_TABLES['tokens'],array('owner_id' => $_USER['uid']),array(Database::INTEGER));
    echo COM_refresh($_CONF['site_url'] . '/index.php?msg=8');
}


/**
* Display get password page
*
* This function validates speed limits
*
* @return   string          HTML
*
*/
function _userGetpassword()
{
    global $_CONF, $_TABLES, $_USER, $LANG04;

    $retval = '';

    if ($_CONF['passwordspeedlimit'] == 0) {
        $_CONF['passwordspeedlimit'] = 300; // 5 minutes
    }
    COM_clearSpeedlimit ($_CONF['passwordspeedlimit'], 'password');
    $last = COM_checkSpeedlimit ('password',4);
    if ($last > 0) {
        $retval .= COM_showMessageText(sprintf ($LANG04[93], $last, $_CONF['passwordspeedlimit']),$LANG12[26],false,'error');
    }
    $retval .= getpasswordform ();

    return $retval;
}

function _userNewpwd()
{
    global $_CONF, $_TABLES, $_USER, $LANG04;

    $retval = '';

    $db = Database::getInstance();

    $uid    = (int) filter_input(INPUT_GET,'uid',FILTER_SANITIZE_NUMBER_INT);
    $reqid  = COM_sanitizeID(filter_input(INPUT_GET,'rid',FILTER_SANITIZE_STRING));

    if (!empty ($uid) && is_numeric ($uid) && ($uid > 1) && !empty ($reqid) && (strlen ($reqid) == 16)) {
        $valid = $db->getCount ($_TABLES['users'], array ('uid', 'pwrequestid'),
                           array ($uid, $reqid),array(Database::INTEGER,Database::STRING));
        if ($valid == 1) {
            $retval .= newpasswordform ($uid, $reqid);
        } else { // request invalid or expired
            $retval .= COM_showMessage (54,'','',1,'error');
            $retval .= getpasswordform ();
        }
    } else {
        // this request doesn't make sense - ignore it
        COM_404();
    }
    return $retval;
}

function _userSetnewpwd()
{
    global $_CONF, $_TABLES, $_USER, $LANG04;

    $db = Database::getInstance();

    $retval = '';
    if ((empty($_POST['passwd'])) || ($_POST['passwd'] != $_POST['passwd_conf'])) {
        $uid = (int) filter_input(INPUT_POST,'uid',FILTER_SANITIZE_NUMBER_INT);
        $reqid = COM_sanitizeID(filter_input(INPUT_POST,'rid',FILTER_SANITIZE_STRING));
        echo COM_refresh ($_CONF['site_url'].'/users.php?mode=newpwd&amp;uid='.$uid.'&amp;rid='.$reqid);
    } else {
        $uid    = (int) filter_input(INPUT_POST,'uid',FILTER_SANITIZE_NUMBER_INT);
        $reqid  = COM_sanitizeID(filter_input(INPUT_POST,'rid',FILTER_SANITIZE_STRING));

        if (!empty ($uid) && is_numeric ($uid) && ($uid > 1) && !empty ($reqid) && (strlen($reqid) == 16)) {

            $valid = $db->getCount(
                            $_TABLES['users'],
                            array('uid','pwrequestid'),
                            array($uid,$reqid),
                            array(Database::INTEGER,Database::STRING)
                     );
            if ($valid == 1) {
                $err = SEC_checkPwdComplexity($_POST['passwd']);
                if (count($err) > 0) {
                    $msg = implode('<br>',$err);
                    $retval .= COM_showMessageText($msg,'',true,'error');
                    $retval .= newpasswordform($uid,$reqid);
                } else {
                    $passwd = SEC_encryptPassword($_POST['passwd']);

                    $db->conn->update(
                            $_TABLES['users'],
                            array('passwd' => $passwd),
                            array('uid',$uid),
                            array(Database::STRING, Database::INTEGER)
                    );
                    $db->conn->delete(
                            $_TABLES['sessions'],
                            array('uid' => $uid),
                            array(Database::INTEGER)
                    );
                    $db->conn->update(
                            $_TABLES['users'],
                            array('pwrequestid' => NULL),
                            array('uid' => $uid),
                            array(Database::STRING)
                    );
                    echo COM_refresh ($_CONF['site_url'] . '/users.php?msg=53');
                }
            } else { // request invalid or expired
                $retval .= COM_showMessage (54,'','',1,'error');
                $retval .= getpasswordform ();
            }
        } else {
            // this request doesn't make sense - ignore it
            COM_404();
        }
    }
    return $retval;
}

function _userEmailpassword()
{
    global $_CONF, $_TABLES, $_USER, $LANG04, $LANG12;

    $retval = '';

    $db = Database::getInstance();

    if ($_CONF['passwordspeedlimit'] == 0) {
        $_CONF['passwordspeedlimit'] = 300; // 5 minutes
    }
    COM_clearSpeedlimit ($_CONF['passwordspeedlimit'], 'password');
    $last = COM_checkSpeedlimit ('password');
    if ($last > 0) {
        $retval .= COM_showMessageText(sprintf ($LANG04[93], $last, $_CONF['passwordspeedlimit']),$LANG12[26],true,'error');
        $retval .= getpasswordform();
    } else {
        // Validate captcha
        $msg = PLG_itemPreSave ('forgotpassword');
        if (!empty ($msg)) {
            COM_setMsg($msg,'error');
            echo COM_refresh ($_CONF['site_url'].'/users.php?mode=getpassword');
        }
        $username = $_POST['username'];
        $email = COM_applyFilter ($_POST['email']);
        if (empty ($username) && !empty ($email)) {
            $username = $db->conn->fetchColumn(
                            "SELECT username FROM `{$_TABLES['users']}`
                             WHERE email = ? AND ((remoteservice IS NULL) OR (remoteservice = ''))",
                            array($email),
                            0,
                            array(Database::STRING)
            );
            if ($username === false || $username === null) {
                $username = '';
            }
        }
        if (!empty ($username)) {
            $retval .= requestpassword ($username, 55);
        } else {
            echo COM_refresh ($_CONF['site_url'].'/users.php?mode=getpassword');
        }
    }
    return $retval;
}

function _userVerify()
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG04;

    $retval = '';

    $db = Database::getInstance();

    $uid = (int) filter_input(INPUT_GET,'u',FILTER_SANITIZE_NUMBER_INT);
    $vid = filter_input(INPUT_GET,'vid',FILTER_SANITIZE_STRING);

    if (!empty ($uid) && is_numeric ($uid) && ($uid > 1) && !empty ($vid) && (strlen ($vid) == 32)) {
        $U = $db->conn->fetchAssoc(
                "SELECT UNIX_TIMESTAMP(act_time) AS act_time FROM `{$_TABLES['users']}`
                 WHERE uid=? AND act_token=? AND status=?",
                array(
                    $uid,
                    $vid,
                    USER_ACCOUNT_AWAITING_VERIFICATION
                ),
                array(
                    Database::INTEGER,
                    Database::STRING,
                    Database::INTEGER
                )
        );
        if ($U === false || $U === null) {
            $valid = 0;
        } else {
            if ( $U['act_time'] != '' && $U['act_time'] > (time() - $_SYSTEM['verification_token_ttl']) ) {
                $valid = 1;
            } else {
                $valid = 0;
            }
        }
        if ($valid == 1) {
            $db->conn->update(
                    $_TABLES['users'],
                    array(
                        'status' => USER_ACCOUNT_AWAITING_ACTIVATION,
                        'act_time' => null
                    ),
                    array(
                        'uid' => $uid
                    ),
                    array(
                        Database::INTEGER,
                        Database::STRING,
                        Database::INTEGER
                    )
            );
            $retval .= COM_showMessage (515,'','',0,'success');
            $retval .= SEC_loginForm();
        } else { // request invalid or expired
            $U = $db->conn->fetchAssoc(
                    "SELECT * FROM `{$_TABLES['users']}` WHERE uid=?",
                    array($uid),
                    array(Database::INTEGER)
            );
            if ($U !== false && $U !== null) {
                switch ($U['status']) {
                    case USER_ACCOUNT_AWAITING_ACTIVATION :
                    case USER_ACCOUNT_ACTIVE :
                        $retval .= COM_showMessage(517,'','',0,'info');
                        $retval .= SEC_loginForm();
                        break;
                    case USER_ACCOUNT_AWAITING_VERIFICATION :
                        $retval .= COM_showMessage(516,'','',1,'error');
                        $retval .= newtokenform($uid);
                        break;
                    default :
                        echo COM_refresh($_CONF['site_url']);
                }
            } else {
                COM_404();
            }
        }
    } else {
        // this request doesn't make sense - ignore it
        COM_404();
    }
    return $retval;
}

function _userGetnewtoken()
{
    global $_CONF, $_TABLES, $_USER, $LANG04;

    $retval = '';

    $db = Database::getInstance();

    $uid = 0;
    if ($_CONF['passwordspeedlimit'] == 0) {
        $_CONF['passwordspeedlimit'] = 300; // 5 minutes
    }
    COM_clearSpeedlimit ($_CONF['passwordspeedlimit'], 'verifytoken');
    $last = COM_checkSpeedlimit ('verifytoken');
    if ($last > 0) {
        $retval .= COM_showMessageText(sprintf ($LANG04[93], $last, $_CONF['passwordspeedlimit']),$LANG12[26],true,'error');
    } else {
        $username = (isset($_POST['username']) ? $_POST['username'] : '');
        $passwd   = (isset($_POST['passwd']) ? $_POST['passwd'] : '');
        if (!empty ($username) && !empty ($passwd) && USER_validateUsername($username,1)) {
            $encryptedPassword = '';
            $uid = 0;

            $row = $db->conn->fetchAssoc(
                        "SELECT uid,passwd FROM `{$_TABLES['users']}`
                         WHERE username=?",
                        array($username),
                        array(Database::STRING)
            );
            if ($row !== false && $row !== null) {
                $encryptedPassword = $row['passwd'];
                $uid = $row['uid'];
            }
            if ( $encryptedPassword != '' && SEC_check_hash($passwd, $encryptedPassword) ) {
                $retval .= requesttoken ($uid, 3);
            } else {
                $retval .= newtokenform($uid);
            }
        } else {
            $retval .= newtokenform($uid);
        }
    }
    return $retval;
}

function validateTFA()
{
    global $_CONF, $LANG12, $LANG04;

    if (!isset($_CONF['enable_twofactor']) || !$_CONF['enable_twofactor']) {
        return true;
    }
    $_USER['uid'] = (int) COM_applyFilter($_POST['uid'],true);
    if ( _sec_checkToken() ) {
        // Check login speed limit
        COM_clearSpeedlimit($_CONF['login_speedlimit'], 'login');
        if (COM_checkSpeedlimit('login', $_CONF['login_attempts']) > 0) {
            displayLoginErrorAndAbort(82, $LANG12[26], $LANG04[112]);
        } else {
            COM_updateSpeedlimit('login');
            $tfaCode = COM_applyFilter($_POST['tfacode']);
            $tfa = \TwoFactor::getInstance($_USER['uid']);
            if ($tfa->validateCode($tfaCode)) {
                return true;
            } else {
                return false;
            }
        }
    }
    return false;
}


// MAIN
if ( isset($_POST['mode']) ) {
    $mode = $_POST['mode'];
} elseif (isset($_GET['mode']) ) {
    $mode = $_GET['mode'];
} else {
    $mode = '';
}

$display = '';
$pageBody = '';

if ( isset($_POST['cancel']) ) {
    echo COM_refresh($_CONF['site_url'].'/index.php');
}

$db = Database::getInstance();

switch ($mode) {
    case 'logout':
        $pageBody = userLogout();
        break;
    case 'profile':
    case 'user' :
        $pageBody .= userprofile();
        break;
    case 'create':
        $pageBody .= USER_createuser($_POST);
        break;
    case 'getpassword':
        $pageBody .= _userGetpassword();
        break;
    case 'newpwd':
        $pageBody .= _userNewpwd();
        break;
    case 'setnewpwd':
        $pageBody .= _userSetnewpwd();
        break;
    case 'emailpasswd':
        $pageBody .= _userEmailpassword();
        break;
    case 'new':
        $pageBody .= USER_registrationForm();
        break;
    case 'verify':
        $pageBody .= _userVerify();
        break;
    case 'getnewtoken':
        $pageBody .= _userGetnewtoken();
        break;
    case 'mergeacct' :
        if ( SEC_checkToken() ) {
            $pageBody .= USER_mergeAccounts();
        } else {
            echo COM_refresh($_CONF['site_url']);
        }
        break;

    default:
        $status = -2;
        $local_login = false;
        $newTwitter  = false;
        $authenticated = 0;
        // prevent dictionary attacks on passwords
        COM_clearSpeedlimit($_CONF['login_speedlimit'], 'login');
        if (COM_checkSpeedlimit('login', $_CONF['login_attempts']) > 0) {
            displayLoginErrorAndAbort(82, $LANG12[26], $LANG04[112]);
        }

        $loginname = '';
        if (isset ($_POST['loginname'])) {
            $loginname = $_POST['loginname'];
            if ( !USER_validateUsername($loginname,1) ) {
                $loginname = '';
            }
        }
        $passwd = '';
        if (isset ($_POST['passwd'])) {
            $passwd = $_POST['passwd'];
        }

        $service = '';
        if (isset ($_POST['service'])) {
            $service = COM_applyFilter($_POST['service']);
        }

        $uid = '';
        if (!empty($loginname) && !empty($passwd) && empty($service)) {
            if (empty($service) && $_CONF['user_login_method']['standard']) {

                // check captcha here
                $msg = PLG_itemPreSave ('loginform', $loginname);
                if (!empty ($msg)) {
                    COM_setMsg($msg,'error');
                    $status = -2;
                } else {
                    COM_updateSpeedlimit('login');
                    $status = SEC_authenticate($loginname, $passwd, $uid);
                    if ($status == USER_ACCOUNT_ACTIVE) {
                        $local_login = true;
                    }
                }
            } else {
                Log::write('system',Log::ERROR,"ERROR: Username and Password were posted, but local authentication is disabled - check configuration settings");
                $status = -2;
            }

        // begin distributed (3rd party) remote authentication method

        } elseif (!empty($loginname) && $_CONF['user_login_method']['3rdparty'] &&
            ($_CONF['usersubmission'] == 0) && ($service != '')) {
            COM_updateSpeedlimit('login');
            //pass $loginname by ref so we can change it ;-)
            $status = SEC_remoteAuthentication($loginname, $passwd, $service, $uid);

        // end distributed (3rd party) remote authentication method

        // begin OAuth authentication method(s)

        } elseif ($_CONF['user_login_method']['oauth'] && isset($_GET['oauth_login'])) {
            if ( !SESS_isSet('oauth_redirect') && isset($_SERVER['HTTP_REFERER']) ) {
                if ( substr($_SERVER['HTTP_REFERER'], 0,strlen($_CONF['site_url'])) == $_CONF['site_url']) {
                    SESS_setVar('oauth_redirect',$_SERVER['HTTP_REFERER']);
                }
            }
            $modules = SEC_collectRemoteOAuthModules();
            $active_service = (count($modules) == 0) ? false : in_array(COM_applyFilter($_GET['oauth_login']), $modules);
            if (!$active_service) {
                $status = -1;
                Log::write('system',Log::ERROR,"OAuth login failed - there was no consumer available for the service:" . COM_applyFilter($_GET['oauth_login']));
            } else {
                $query = array_merge($_GET, $_POST);
                $service = COM_applyFilter($query['oauth_login']);

                COM_clearSpeedlimit($_CONF['login_speedlimit'], $service);
                if ( COM_checkSpeedlimit($service, $_CONF['login_attempts']) > 0 ) {
                    displayLoginErrorAndAbort(82, $LANG12[26], $LANG04[112]);
                }

                $consumer = new OAuthConsumer($service);

                $callback_url = $_CONF['site_url'] . '/users.php?oauth_login=' . $service;

                $consumer->setRedirectURL($callback_url);
                $oauth_userinfo = $consumer->authenticateUser();
                if ( $oauth_userinfo === false ) {
                    COM_updateSpeedlimit('login');
                    Log::write('system',Log::ERROR,"OAuth Error: " . $consumer->error);
                    COM_setMsg($MESSAGE[111],'error');
                } else {
                    if ($consumer->doFinalLogin($oauth_userinfo,$status,$uid) === null) {
                        Log::write('system',Log::ERROR,"Oauth: Error creating new user in OAuth authentication");
                        COM_setMsg($MESSAGE[111],'error');
                    }
                    $_SERVER['HTTP_REFERER'] = SESS_getVar('oauth_redirect');
                    SESS_unSet('oauth_redirect');
                }
            }

        //  end OAuth authentication method(s)

        } elseif ($mode == 'tfa' ) {
            if ( !validateTFA() ) {
                $authenticated = 0;
                COM_setMsg($LANG_TFA['error_invalid_code'],'error',true);
            } else {
                $authenticated = 1;
            }
            $uid = (int) filter_input(INPUT_POST,'uid',FILTER_SANITIZE_NUMBER_INT);

            $row = $db->conn->fetchAssoc(
                    "SELECT status,account_type FROM `{$_TABLES['users']}` WHERE uid=?",
                    array($uid),
                    array(Database::INTEGER)
            );
            if ($row !== false && $row !== null) {
                $status = $row['status'];
                $local_login = $row['account_type'] & LOCAL_USER;
            } else {
                $status = -2;
            }
        } else {
            $status = -2;
        }

        if ($status == USER_ACCOUNT_ACTIVE || $status == USER_ACCOUNT_AWAITING_ACTIVATION ) { // logged in AOK.
            SESS_completeLogin($uid,$authenticated);
            $_GROUPS = SEC_getUserGroups( $_USER['uid'] );
            $_RIGHTS = explode( ',', SEC_getUserPermissions() );
            if ($_SYSTEM['admin_session'] > 0 && $local_login ) {
                if (SEC_isModerator() || SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit','OR')
                         || (count(PLG_getAdminOptions()) > 0)) {
                    $admin_token = SEC_createTokenGeneral('administration',$_SYSTEM['admin_session']);
                    SEC_setCookie('token',$admin_token,0,$_CONF['cookie_path'],$_CONF['cookiedomain'],$_CONF['cookiesecure'],true);
                }
            }
            if ( !isset($_USER['theme']) ) {
                $_USER['theme'] = $_CONF['theme'];
                $_CONF['path_layout'] = $_CONF['path_themes'] . $_USER['theme'] . '/';
                $_CONF['layout_url'] = $_CONF['site_url'] . '/layout/' . $_USER['theme'];
                if ( $_CONF['allow_user_themes'] == 1 ) {
                    if ( isset( $_COOKIE[$_CONF['cookie_theme']] ) ) {
                        $theme = COM_sanitizeFilename($_COOKIE[$_CONF['cookie_theme']], true);
                        if ( is_dir( $_CONF['path_themes'] . $theme )) {
                            $_USER['theme'] = $theme;
                            $_CONF['path_layout'] = $_CONF['path_themes'] . $theme . '/';
                            $_CONF['layout_url'] = $_CONF['site_url'] . '/layout/' . $theme;
                        }
                    }
                }
            }
            SEC_setCookie ($_CONF['cookie_language'], $_USER['language'], time() + 31536000,
                           $_CONF['cookie_path'], $_CONF['cookiedomain'],
                           $_CONF['cookiesecure'],false);
            COM_resetSpeedlimit('login');

            // we are now fully logged in, let's see if there is someplace we need to go....
            // First, check for a landing page supplied by the form or global config
            foreach (array($_POST, $_CONF) as $A) {
                if (isset($A['login_landing']) && !empty($A['login_landing'])) {
                    if ($A['login_landing'][0] != '/') {
                        $A['login_landing'] = '/' . $A['login_landing'];
                    }
                    COM_refresh($_CONF['site_url'] . $A['login_landing']);
                }
            }

            if ( SESS_isSet('login_referer') ) {
                $_SERVER['HTTP_REFERER'] = SESS_getVar('login_referer');
                SESS_unSet('login_referer');
            }
            if (!empty($_SERVER['HTTP_REFERER'])
                    && (strstr($_SERVER['HTTP_REFERER'], '/users.php') === false)
                    && (substr($_SERVER['HTTP_REFERER'], 0,
                            strlen($_CONF['site_url'])) == $_CONF['site_url'])) {
                $indexMsg = $_CONF['site_url'] . '/index.php?msg=';
                if (substr ($_SERVER['HTTP_REFERER'], 0, strlen ($indexMsg)) == $indexMsg) {
                    echo COM_refresh ($_CONF['site_url'] . '/index.php');
                } else {
                    // If user is trying to login - force redirect to index.php
                    if (strstr ($_SERVER['HTTP_REFERER'], 'mode=login') === false) {
                    // if article - we need to ensure we have the story
                        if ( substr($_SERVER['HTTP_REFERER'], 0,strlen($_CONF['site_url'])) == $_CONF['site_url']) {
                            echo COM_refresh (COM_sanitizeUrl($_SERVER['HTTP_REFERER']));
                        } else {
                            echo COM_refresh($_CONF['site_url'].'/index.php');
                        }
                    } else {
                        echo COM_refresh ($_CONF['site_url'] . '/index.php');
                    }
                }
            } else {
                echo COM_refresh ($_CONF['site_url'] . '/index.php');
            }
        } else {
            $msg = COM_getMessage();
            if ($msg > 0) {
                $pageBody .= COM_showMessage($msg,'','',0,'info');
            }

            switch ($mode) {
                case 'create':
                    // Got bad account info from registration process, show error
                    // message and display form again
                    $pageBody .= USER_registrationForm ();
                    break;
                default:
                    if (!empty($_SERVER['HTTP_REFERER'])
                            && (strstr($_SERVER['HTTP_REFERER'], '/users.php') === false)
                            && (substr($_SERVER['HTTP_REFERER'], 0,
                                    strlen($_CONF['site_url'])) == $_CONF['site_url'])) {
                            SESS_setVar('login_referer',$_SERVER['HTTP_REFERER']);
                    }

                    // check to see if this was the last allowed attempt
                    if (COM_checkSpeedlimit('login', $_CONF['login_attempts']) > 0) {
                        displayLoginErrorAndAbort(82, $LANG04[113], $LANG04[112]);
                    } else { // Show login form
                        if(($msg != 69) && ($msg != 70)) {
                            if ( !COM_isAnonUser() ) {
                                echo COM_refresh($_CONF['site_url'].'/usersettings.php');
                                exit;
                            }
                            if ($_CONF['custom_registration'] AND function_exists('CUSTOM_loginErrorHandler') && $msg != 0) {
                                $pageBody .= CUSTOM_loginErrorHandler($msg);
                            } else {
                                $pageBody .= loginform(false, $status);
                            }
                        }
                    }
                    break;
            }
        }
        break;
}

$display = COM_siteHeader('menu');
$display .= $pageBody;
$display .= COM_siteFooter();
echo $display;
?>
