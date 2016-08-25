<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | users.php                                                                |
// |                                                                          |
// | User authentication module.                                              |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark A. Howard         mark AT usable-web DOT com                        |
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

USES_lib_user();
USES_lib_social();

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

// @param    int     $user   User ID of profile to get
// @param    int     $msg    Message to display (if != 0)
// @param    string  $plugin optional plugin name for message

    $retval = '';
    if (COM_isAnonUser() &&
        (($_CONF['loginrequired'] == 1) || ($_CONF['profileloginrequired'] == 1))) {
        $retval .= SEC_loginRequiredForm();
        return $retval;
    }

    if ( isset($_GET['uid']) ) {
        $user = COM_applyFilter ($_GET['uid'], true);
        if (!is_numeric ($user) || ($user < 2)) {
            echo COM_refresh ($_CONF['site_url'] . '/index.php');
        }
    } else if ( isset($_GET['username']) ) {
        $username = $_GET['username'];
        if ( !USER_validateUsername($username,1) ) {
            echo COM_refresh ($_CONF['site_url'] . '/index.php');
        }
        if ( empty($username) || $username == '' ) {
            echo COM_refresh ($_CONF['site_url'] . '/index.php');
        }
        $username = DB_escapeString ($username);
        $user = DB_getItem ($_TABLES['users'], 'uid', "username = '$username'");
        if ($user < 2) {
            echo COM_refresh ($_CONF['site_url'] . '/index.php');
        }
    } else {
        echo COM_refresh ($_CONF['site_url'] . '/index.php');
    }

    $result = DB_query ("SELECT {$_TABLES['users']}.uid,username,fullname,regdate,lastlogin,homepage,about,location,pgpkey,photo,email,status,emailfromadmin,emailfromuser,showonline FROM {$_TABLES['userinfo']},{$_TABLES['userprefs']},{$_TABLES['users']} WHERE {$_TABLES['userinfo']}.uid = {$_TABLES['users']}.uid AND {$_TABLES['userinfo']}.uid = {$_TABLES['userprefs']}.uid AND {$_TABLES['users']}.uid = ".(int) $user);
    $nrows = DB_numRows ($result);
    if ($nrows == 0) { // no such user
        echo COM_refresh ($_CONF['site_url'] . '/index.php');
    }
    $A = DB_fetchArray ($result);

    if ($A['status'] == USER_ACCOUNT_DISABLED && !SEC_hasRights ('user.edit')) {
        COM_displayMessageAndAbort (30, '', 403, 'Forbidden');
    }

    $display_name = @htmlspecialchars(COM_getDisplayName($user, $A['username'],$A['fullname']),ENT_COMPAT,COM_getEncodingt());

    $msg = COM_getMessage();
    if ($msg > 0) {
        $plugin = '';
        if (isset ($_GET['plugin'])) {
            $plugin = COM_applyFilter ($_GET['plugin']);
        }
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
        global $_IMAGE_TYPE, $LANG_ADMIN;

        $edit_icon = '<img src="' . $_CONF['layout_url'] . '/images/edit.'
                   . $_IMAGE_TYPE . '" alt="' . $LANG_ADMIN['edit']
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
        if ( DB_count($_TABLES['sessions'],'uid',(int) $user)) {
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
    $user_templates->set_var ('user_bio', nl2br ($A['about']));

    $user_templates->set_var('follow_me',SOC_getFollowMeIcons( $user, 'follow_user_profile.thtml' ));

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

    $result = DB_query ("SELECT tid FROM {$_TABLES['topics']}" . COM_getPermSQL ());
    $nrows = DB_numRows ($result);
    $tids = array ();
    for ($i = 0; $i < $nrows; $i++) {
        $T = DB_fetchArray ($result);
        $tids[] = $T['tid'];
    }
    $topics = "'" . implode ("','", $tids) . "'";

    // list of last 10 stories by this user
    if (sizeof ($tids) > 0) {
        $sql = "SELECT sid,title,UNIX_TIMESTAMP(date) AS unixdate FROM {$_TABLES['stories']} WHERE (uid = '".(int) $user."') AND (draft_flag = 0) AND (date <= NOW()) AND (tid IN ($topics))" . COM_getPermSQL ('AND');
        $sql .= " ORDER BY unixdate DESC LIMIT 10";
        $result = DB_query ($sql);
        $nrows = DB_numRows ($result);
    } else {
        $nrows = 0;
    }
    if ($nrows > 0) {
        for ($i = 0; $i < $nrows; $i++) {
            $C = DB_fetchArray ($result);
            $user_templates->set_var ('cssid', ($i % 2) + 1);
            $user_templates->set_var ('row_number', ($i + 1) . '.');
            $articleUrl = COM_buildUrl ($_CONF['site_url']
                                        . '/article.php?story=' . $C['sid']);
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
        }
    } else {
        $user_templates->set_var ('story_row',
                                  '<tr><td>' . $LANG01[37] . '</td></tr>');
    }
    if (!isset($_CONF['comment_engine']) || $_CONF['comment_engine'] == 'internal') {
        // list of last 10 comments by this user
        $sidArray = array();
        if (sizeof ($tids) > 0) {
            // first, get a list of all stories the current visitor has access to
            $sql = "SELECT sid FROM {$_TABLES['stories']} WHERE (draft_flag = 0) AND (date <= NOW()) AND (tid IN ($topics))" . COM_getPermSQL ('AND');
            $result = DB_query($sql);
            $numsids = DB_numRows($result);
            for ($i = 1; $i <= $numsids; $i++) {
                $S = DB_fetchArray ($result);
                $sidArray[] = $S['sid'];
            }
        }

        $sidList = implode("', '",$sidArray);
        $sidList = "'$sidList'";

        // then, find all comments by the user in those stories
        $sql = "SELECT sid,title,cid,UNIX_TIMESTAMP(date) AS unixdate FROM {$_TABLES['comments']} WHERE (uid = '".(int) $user."') GROUP BY sid,title,cid,UNIX_TIMESTAMP(date)";

        // SQL NOTE:  Using a HAVING clause is usually faster than a where if the
        // field is part of the select
        // if (!empty ($sidList)) {
        //     $sql .= " AND (sid in ($sidList))";
        // }
        if (!empty ($sidList)) {
            $sql .= " HAVING sid in ($sidList)";
        }
        $sql .= " ORDER BY unixdate DESC LIMIT 10";

        $result = DB_query($sql);
        $nrows = DB_numRows($result);
        if ($nrows > 0) {
            for ($i = 0; $i < $nrows; $i++) {
                $C = DB_fetchArray ($result);
                $user_templates->set_var ('cssid', ($i % 2) + 1);
                $user_templates->set_var ('row_number', ($i + 1) . '.');
                $C['title'] = str_replace ('$', '&#36;', $C['title']);
                $comment_url = $_CONF['site_url'] .
                        '/comment.php?mode=view&amp;cid=' . $C['cid'];
                $user_templates->set_var ('comment_title',
                    COM_createLink(
                        $C['title'],
                        $comment_url,
                        array ('class'=>''))
                );
                $commenttime = COM_getUserDateTimeFormat ($C['unixdate']);
                $user_templates->set_var ('comment_date', $commenttime[0]);
                $user_templates->parse ('comment_row', 'row', true);
            }
        } else {
            $user_templates->set_var('comment_row','<tr><td>' . $LANG01[29] . '</td></tr>');
        }
    }
    // posting stats for this user
    $user_templates->set_var ('lang_number_stories', $LANG04[84]);
    $sql = "SELECT COUNT(*) AS count FROM {$_TABLES['stories']} WHERE (uid = ".(int) $user.") AND (draft_flag = 0) AND (date <= NOW())" . COM_getPermSQL ('AND');
    $result = DB_query($sql);
    $N = DB_fetchArray ($result);
    $user_templates->set_var ('number_stories', COM_numberFormat ($N['count']));
    if (!isset($_CONF['comment_engine']) || $_CONF['comment_engine'] == 'internal') {
        $user_templates->set_var ('lang_number_comments', $LANG04[85]);

        $sql = "SELECT COUNT(*) AS count FROM {$_TABLES['comments']} WHERE (uid = ".(int) $user.")";
        if (!empty ($sidList)) {
            $sql .= " AND (sid in ($sidList))";
        }
        $result = DB_query ($sql);
        $N = DB_fetchArray ($result);
        $user_templates->set_var ('number_comments', COM_numberFormat($N['count']));
        $user_templates->set_var ('lang_all_postings_by',
                                  $LANG04[86] . ' ' . $display_name);
    }
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

    $username = DB_escapeString ($username);
    // don't retrieve any remote users!
    $result = DB_query ("SELECT uid,email,status FROM {$_TABLES['users']} WHERE username = '".$username."' AND (account_type & ".LOCAL_USER.")");
    $nrows = DB_numRows ($result);
    if ($nrows == 1) {
        $A = DB_fetchArray ($result);
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

    $retval = '';

    // no remote users!
    $username = DB_escapeString($username);
    $result = DB_query ("SELECT uid,email,passwd,status FROM {$_TABLES['users']} WHERE username = '".$username."' AND (account_type & ".LOCAL_USER.")");
    $nrows = DB_numRows ($result);
    if ($nrows == 1) {
        $A = DB_fetchArray ($result);
        if (($_CONF['usersubmission'] == 1) && ($A['status'] == USER_ACCOUNT_AWAITING_APPROVAL)) {
            echo COM_refresh ($_CONF['site_url'] . '/index.php?msg=48');
        }
        $reqid = substr (md5 (uniqid (rand (), 1)), 1, 16);
        DB_change ($_TABLES['users'], 'pwrequestid', "$reqid",'uid', (int) $A['uid']);

        $T = new Template($_CONF['path_layout'].'email/');
        $T->set_file(array(
            'html_msg'   => 'mailtemplate_html.thtml',
            'text_msg'   => 'mailtemplate_text.thtml'
        ));

        $T->set_block('html_msg', 'content', 'contentblock');
        $T->set_block('text_msg', 'contenttext', 'contenttextblock');

        $T->set_var('content_text',sprintf ($LANG04[88], $username) );

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

    $pwform = new Template ($_CONF['path_layout'] . 'users');
    $pwform->set_file ('newpw','newpassword.thtml');
    $pwform->set_var (array(
            'user_id'       => $uid,
            'user_name'     => DB_getItem ($_TABLES['users'], 'username',"uid = ".(int)$uid),
            'request_id'    => $requestid,
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

    if ( !isset($_SYSTEM['verification_token_ttl']) ) {
        $_SYSTEM['verification_token_ttl'] = 86400;
    }

    $retval = '';
    $uid = (int) $uid;
    $result = DB_query ("SELECT uid,username,email,passwd,status FROM {$_TABLES['users']} WHERE uid = ".(int)$uid." AND (account_type & ".LOCAL_USER.")");
    $nrows = DB_numRows ($result);
    if ($nrows == 1) {
        $A = DB_fetchArray ($result);
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
// function createuser ($username, $email, $email_conf, $passwd='', $passwd_conf='')
function createuser ( )
{
    global $_CONF, $_TABLES, $LANG01, $LANG04, $MESSAGE, $REMOTE_ADDR;

    $retval = '';

    $retval = '';
    $passwd = '';
    $passwd_conf = '';

    if ($_CONF['disable_new_user_registration']) {
        COM_setMsg($LANG04[122],'error');
        echo COM_refresh($_CONF['site_url']);
    }

    $email       = isset($_POST['email']) ? COM_applyFilter ($_POST['email']) : '';
    $email_conf  = isset($_POST['email_conf']) ? COM_applyFilter ($_POST['email_conf']) : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';

    if ( isset($_POST['passwd']) ) {
        $passwd = trim($_POST['passwd']);
    }
    if ( isset($_POST['passwd_conf']) ) {
        $passwd_conf = trim($_POST['passwd_conf']);
    }

    $username   = COM_truncate(trim ($username),48);

    if ( !USER_validateUsername($username)) {
        $retval .= newuserform ($LANG04[162]);
        return $retval;
    }

    $email      = COM_truncate(trim ($email),96);
    $email_conf = trim ($email_conf);

    if ( $_CONF['registration_type'] == 1 ) {
        if ( empty($passwd) || ($passwd != $passwd_conf) ) {
            $retval .= newuserform($MESSAGE[67]);
            return $retval;
        }
    }

    $fullname = '';
    if (!empty ($_POST['fullname'])) {
        $fullname   = COM_truncate(trim(USER_sanitizeName($_POST['fullname'])),80);
    }

    if (!isset ($_CONF['disallow_domains'])) {
        $_CONF['disallow_domains'] = '';
    }

    if (COM_isEmail ($email) && !empty ($username) && ($email === $email_conf)
            && !USER_emailMatches ($email, $_CONF['disallow_domains'])
            && (strlen ($username) <= 48)) {

        $ucount = DB_count ($_TABLES['users'], 'username',
                            DB_escapeString ($username));
        $ecount = DB_count ($_TABLES['users'], 'email', DB_escapeString ($email));

        if ($ucount == 0 AND $ecount == 0) {

            // For glFusion, it would be okay to create this user now. But check
            // with a custom userform first, if one exists.
            if ($_CONF['custom_registration'] && function_exists ('CUSTOM_userCheck')) {
                $msg = CUSTOM_userCheck ($username, $email);
                if (!empty ($msg)) {
                    // no, it's not okay with the custom userform
                    $retval = CUSTOM_userForm ($msg);

                    return $retval;
                }
            }

            // Let plugins have a chance to decide what to do before creating the user, return errors.

            $spamCheckData = array(
                'username'  => $username,
                'email'     => $email,
                'ip'        => $REMOTE_ADDR);

            $msg = PLG_itemPreSave ('registration', $spamCheckData);
            if (!empty ($msg)) {
                $retval .= newuserform ($msg);
                return $retval;
            }
            if ( $_CONF['registration_type'] == 1 && !empty($passwd) ) {
                $encryptedPasswd = SEC_encryptPassword($passwd);
            } else {
                $encryptedPasswd = '';
            }

            $uid = USER_createAccount ($username, $email, $encryptedPasswd, $fullname);

            if ($_CONF['usersubmission'] == 1) {
                if (DB_getItem ($_TABLES['users'], 'status', "uid = ".(int) $uid)
                        == USER_ACCOUNT_AWAITING_APPROVAL) {
                   echo COM_refresh ($_CONF['site_url']
                                           . '/index.php?msg=48');
                } else {
                    $retval = emailpassword ($username, $passwd, 1);
                }
            } else {
                $retval = emailpassword ($username,$passwd);
            }

            return $retval;
        } else {
            $retval .= newuserform ($LANG04[19]);
        }
    } else if ($email !== $email_conf) {
        $msg = $LANG04[125];
        $retval .= newuserform ($msg);
    } else { // invalid username or email address

        if ((empty ($username)) || (strlen($username) > 48)) {
            $msg = $LANG01[32]; // invalid username
        } else {
            $msg = $LANG04[18]; // invalid email address
        }
        $retval .= newuserform ($msg);
    }

    return $retval;
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
        $options['title']   = $LANG04[65]; // log in to {site_name}
        $options['message'] = $LANG04[113]; // login attempt failed
    } else {
        $options['title']   = $LANG04[65]; // log in to {site_name}
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
function newuserform ($msg = '')
{
    global $_CONF, $LANG01, $LANG04;

    $retval = '';

    if ($_CONF['disable_new_user_registration']) {
        COM_setMsg($LANG04[122],'error');
        echo COM_refresh($_CONF['site_url']);
    }

    if ($_CONF['custom_registration'] AND (function_exists('CUSTOM_userForm'))) {
        return CUSTOM_userForm($msg);
    }

    if (!empty ($msg)) {
        $retval .= COM_showMessageText($msg,$LANG04[21],false,'info');
    }
    $user_templates = new Template($_CONF['path_layout'] . 'users');
    $user_templates->set_file('regform', 'registrationform.thtml');
    $user_templates->set_var('start_block', COM_startBlock($LANG04[22]));
    $user_templates->set_var('lang_instructions', $LANG04[23]);
    $user_templates->set_var('lang_username', $LANG04[2]);
    $user_templates->set_var('lang_fullname', $LANG04[3]);
    $user_templates->set_var('lang_email', $LANG04[5]);
    $user_templates->set_var('lang_email_conf', $LANG04[124]);
    if ( $_CONF['registration_type'] == 1 ) { // verification link
        $user_templates->set_var('lang_passwd',$LANG01[57]);
        $user_templates->set_var('lang_passwd_conf',$LANG04[176]);
        $user_templates->set_var('lang_warning',$LANG04[167]);
    } else {
        $user_templates->set_var('lang_warning', $LANG04[24]);
    }

    $user_templates->set_var('lang_register', $LANG04[27]);
    PLG_templateSetVars ('registration', $user_templates);
    $user_templates->set_var('end_block', COM_endBlock());

    $username = '';
    if (!empty ($_POST['username'])) {
        $username = trim ( $_POST['username'] );
    }
    $user_templates->set_var ('username', @htmlentities($username,ENT_COMPAT,COM_getEncodingt()));

    $fullname = '';
    if (!empty ($_POST['fullname'])) {
        $fullname = $_POST['fullname'];
    }
    $fullname = USER_sanitizeName($fullname);

    $user_templates->set_var ('fullname', @htmlentities($fullname,ENT_COMPAT,COM_getEncodingt()));
    switch ($_CONF['user_reg_fullname']) {
    case 2:
        $user_templates->set_var('require_fullname', 'true');
    case 1:
        $user_templates->set_var('show_fullname', 'true');
    }

    $email = '';
    if (!empty ($_POST['email'])) {
        $email = COM_applyFilter ($_POST['email']);
    }
    $user_templates->set_var ('email', $email);

    $email_conf = '';
    if (!empty ($_POST['email_conf'])) {
        $email_conf = COM_applyFilter ($_POST['email_conf']);
    }
    $user_templates->set_var ('email_conf', $email_conf);


    $user_templates->parse('output', 'regform');
    $retval .= $user_templates->finish($user_templates->get_var('output'));

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
        $retval .= newuserform ();
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

   if (!empty ($_USER['uid']) AND $_USER['uid'] > 1) {
        DB_query("UPDATE {$_TABLES['users']} set remote_ip='' WHERE uid=".$_USER['uid'],1);
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
        DB_delete($_TABLES['tokens'],'token',DB_escapeString($token));
        SEC_setCookie ('token', '', time() - 10000,
                       $_CONF['cookie_path'], $_CONF['cookiedomain'],
                       $_CONF['cookiesecure'],true);
    }
    DB_delete($_TABLES['tokens'],'owner_id',(int) $_USER['uid']);
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

    $uid    = COM_applyFilter ($_GET['uid'], true);
    $reqid  = COM_sanitizeID(COM_applyFilter ($_GET['rid']));

    if (!empty ($uid) && is_numeric ($uid) && ($uid > 1) && !empty ($reqid) && (strlen ($reqid) == 16)) {
        $uid = (int) $uid;
        $safereqid = DB_escapeString($reqid);
        $valid = DB_count ($_TABLES['users'], array ('uid', 'pwrequestid'),
                           array ($uid, $safereqid));
        if ($valid == 1) {
            $retval .= newpasswordform ($uid, $reqid);
        } else { // request invalid or expired
            $retval .= COM_showMessage (54,'','',1,'error');
            $retval .= getpasswordform ();
        }
    } else {
        // this request doesn't make sense - ignore it
        echo COM_refresh ($_CONF['site_url']);
    }
    return $retval;
}

function _userSetnewpwd()
{
    global $_CONF, $_TABLES, $_USER, $LANG04;

    $retval = '';
    if ( (empty ($_POST['passwd']))
            || ($_POST['passwd'] != $_POST['passwd_conf']) ) {
        echo COM_refresh ($_CONF['site_url']
                 . '/users.php?mode=newpwd&amp;uid=' . COM_applyFilter($_POST['uid'],true)
                 . '&amp;rid=' . COM_applyFilter($_POST['rid']));
    } else {
        $uid = COM_applyFilter ($_POST['uid'], true);
        $reqid = COM_sanitizeID(COM_applyFilter ($_POST['rid']));
        if (!empty ($uid) && is_numeric ($uid) && ($uid > 1) &&
                !empty ($reqid) && (strlen ($reqid) == 16)) {
            $uid = (int) $uid;
            $safereqid = DB_escapeString($reqid);
            $valid = DB_count ($_TABLES['users'], array ('uid', 'pwrequestid'),
                               array ($uid, $safereqid));
            if ($valid == 1) {
                $passwd = SEC_encryptPassword($_POST['passwd']);
                DB_change ($_TABLES['users'], 'passwd', DB_escapeString($passwd),"uid", $uid);
                DB_delete ($_TABLES['sessions'], 'uid', $uid);
                DB_change ($_TABLES['users'], 'pwrequestid', "NULL",'uid', $uid);
                echo COM_refresh ($_CONF['site_url'] . '/users.php?msg=53');
            } else { // request invalid or expired
                $retval .= COM_showMessage (54,'','',1,'error');
                $retval .= getpasswordform ();
            }
        } else {
            // this request doesn't make sense - ignore it
            echo COM_refresh ($_CONF['site_url']);
        }
    }
}

function _userEmailpassword()
{
    global $_CONF, $_TABLES, $_USER, $LANG04, $LANG12;

    $retval = '';

    if ($_CONF['passwordspeedlimit'] == 0) {
        $_CONF['passwordspeedlimit'] = 300; // 5 minutes
    }
    COM_clearSpeedlimit ($_CONF['passwordspeedlimit'], 'password');
    $last = COM_checkSpeedlimit ('password');
    if ($last > 0) {
        $retval .= COM_showMessageText(sprintf ($LANG04[93], $last, $_CONF['passwordspeedlimit']),$LANG12[26],true,'error');
$retval .= getpasswordform();
    } else {
        $username = $_POST['username'];
        $email = COM_applyFilter ($_POST['email']);
        if (empty ($username) && !empty ($email)) {
            $username = DB_getItem ($_TABLES['users'], 'username',
                                    "email = '".DB_escapeString($email)."' AND ((remoteservice IS NULL) OR (remoteservice = ''))");
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

    $uid    = (int) COM_applyFilter ($_GET['u'], true);
    $vid    = COM_applyFilter ($_GET['vid']);

    if (!empty ($uid) && is_numeric ($uid) && ($uid > 1) &&
            !empty ($vid) && (strlen ($vid) == 32)) {
        $uid = (int) $uid;
        $safevid = DB_escapeString($vid);
        $result = DB_query("SELECT UNIX_TIMESTAMP(act_time) AS act_time FROM {$_TABLES['users']} WHERE uid=".$uid." AND act_token='".$safevid."' AND status=".USER_ACCOUNT_AWAITING_VERIFICATION);
        if ( DB_numRows($result) != 1 ) {
            $valid = 0;
        } else {
            $U = DB_fetchArray($result);
            if ( $U['act_time'] != '' && $U['act_time'] > (time() - $_SYSTEM['verification_token_ttl']) ) {
                $valid = 1;
            } else {
                $valid = 0;
            }
        }
        if ($valid == 1) {
            DB_query("UPDATE {$_TABLES['users']} SET status=".USER_ACCOUNT_AWAITING_ACTIVATION.",act_time='1000-01-01 00:00:00' WHERE uid=".$uid);
            $retval .= COM_showMessage (515,'','',0,'success');
            $retval .= SEC_loginForm();
        } else { // request invalid or expired
            $result = DB_query("SELECT * FROM {$_TABLES['users']} WHERE uid=".$uid);
            if ( DB_numRows($result) == 1 ) {
                $U = DB_fetchArray($result);
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
                echo COM_refresh ($_CONF['site_url']);
            }
        }
    } else {
        // this request doesn't make sense - ignore it
        echo COM_refresh ($_CONF['site_url']);
    }
    return $retval;
}

function _userGetnewtoken()
{
    global $_CONF, $_TABLES, $_USER, $LANG04;

    $retval = '';

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
            $result = DB_query("SELECT uid,passwd FROM {$_TABLES['users']} WHERE username='".DB_escapeString($username)."'");
            if ( DB_numRows($result)  > 0 ) {
                $row = DB_fetchArray($result);
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

switch ($mode) {
    case 'logout':
        $pageBody = userLogout();
        break;
    case 'profile':
    case 'user' :
        $pageBody .= userprofile();
        break;
    case 'create':
        $pageBody .= createuser();
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
        $pageBody .= newuserform();
        break;
    case 'verify':
        $pageBody .= _userVerify();
        break;
    case 'getnewtoken':
        $pageBody .= _userGetnewtoken();
        break;
    case 'mergeacct' :
        $pageBody .= USER_mergeAccounts();
        break;
    default:
        $status = -2;
        $local_login = false;
        $newTwitter  = false;

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
                COM_updateSpeedlimit('login');
                $status = SEC_authenticate($loginname, $passwd, $uid);
                if ($status == USER_ACCOUNT_ACTIVE) {
                    $local_login = true;
                }
            } else {
                $status = -2;
            }

        // begin distributed (3rd party) remote authentication method

        } elseif (!empty($loginname) && $_CONF['user_login_method']['3rdparty'] &&
            ($_CONF['usersubmission'] == 0) &&
            ($service != '')) {

            COM_updateSpeedlimit('login');
            //pass $loginname by ref so we can change it ;-)
            $status = SEC_remoteAuthentication($loginname, $passwd, $service, $uid);

        // end distributed (3rd party) remote authentication method

        // begin OAuth authentication method(s)

        } elseif ($_CONF['user_login_method']['oauth'] && isset($_GET['oauth_login'])) {
            $modules = SEC_collectRemoteOAuthModules();
            $active_service = (count($modules) == 0) ? false : in_array($_GET['oauth_login'], $modules);
            if (!$active_service) {
                $status = -1;
                COM_errorLog("OAuth login failed - there was no consumer available for the service:" . $_GET['oauth_login']);
            } else {
                $query = array_merge($_GET, $_POST);
                $service = $query['oauth_login'];

                COM_clearSpeedlimit($_CONF['login_speedlimit'], $service);
                if (COM_checkSpeedlimit($service, $_CONF['login_attempts']) > 0) {
                    displayLoginErrorAndAbort(82, $LANG12[26], $LANG04[112]);
                }

                require_once $_CONF['path_system'] . 'classes/oauthhelper.class.php';

                $consumer = new OAuthConsumer($service);

                $callback_url = $_CONF['site_url'] . '/users.php?oauth_login=' . $service;

                $consumer->setRedirectURL($callback_url);
                $oauth_userinfo = $consumer->authenticate_user();
                if ( $oauth_userinfo === false ) {
                    COM_updateSpeedlimit('login');
                    COM_errorLog("OAuth Error: " . $consumer->error);
                    echo COM_refresh($_CONF['site_url'] . '/users.php?msg=111'); // OAuth authentication error
                }
                $consumer->doAction($oauth_userinfo);
            }

        //  end OAuth authentication method(s)

        } else {
            $status = -2;
        }

        if ($status == USER_ACCOUNT_ACTIVE || $status == USER_ACCOUNT_AWAITING_ACTIVATION ) { // logged in AOK.
            SESS_completeLogin($uid);
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
            COM_resetSpeedlimit('login');

            // we are now fully logged in, let's see if there is someplace we need to go....
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
                    $pageBody .= newuserform ();
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
                            if ($_CONF['custom_registration'] AND function_exists('CUSTOM_loginErrorHandler') && $msg != 0) {
                                // Typically this will be used if you have a custom main site page and need to control the login process
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
