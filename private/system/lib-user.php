<?php
/**
* glFusion CMS
*
* User-related functions needed in more than one place
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*   Tony Bibbs        tony@tonybibbs.com
*   Mark Limburg      mlimburg@users.sourceforge.net
*   Jason Whittenburg jwhitten@securitygeeks.com
*   Dirk Haun         dirk@haun-online.de
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;

/**
* Delete a user account
*
* @param    int       $uid   id of the user to delete
* @return   boolean   true = user deleted, false = an error occured
*
*/
function USER_deleteAccount($uid)
{
    global $_CONF, $_TABLES, $_USER;

    $uid = (int) $uid;

    $db = Database::getInstance();

    // first some checks ...
    if ((($uid == $_USER['uid']) && ($_CONF['allow_account_delete'] == 1)) ||
            SEC_hasRights ('user.delete')) {
        if (SEC_inGroup ('Root', $uid)) {
            if (!SEC_inGroup ('Root')) {
                // can't delete a Root user without being in the Root group
                Log::write('system',Log::NOTICE,"User {$_USER['uid']} just tried to delete Root user $uid with insufficient privileges.");
                return false;
            } else {

                $rootgrp = $db->getItem($_TABLES['groups'],'grp_id',array('grp_name'=>'Root'));

                $userCount = $db->conn->fetchColumn(
                                "SELECT COUNT(DISTINCT {$_TABLES['users']}.uid) AS count
                                    FROM `{$_TABLES['users']}`,`{$_TABLES['group_assignments']}`
                                    WHERE {$_TABLES['users']}.uid > 1 AND {$_TABLES['users']}.uid = {$_TABLES['group_assignments']}.ug_uid
                                        AND ({$_TABLES['group_assignments']}.ug_main_grp_id = ?",
                                array($rootgrp),
                                0,
                                array(Database::STRING)
                             );
                if ($userCount <= 1) {
                    // make sure there's at least 1 Root user left
                    Log::write('system',Log::ERROR,"You cannot delete the last user from the Root group.");
                    return false;
                }
            }
        }
    } else {
        // you can only delete your own account (if enabled) or you need
        // proper permissions to do so (user.delete)
        Log::write('system',Log::NOTICE,"User {$_USER['uid']} just tried to delete user $uid with insufficient privileges.");
        return false;
    }

    // log the user out
    SESS_endUserSession ($uid);

    // Ok, now delete everything related to this user

    // let plugins update their data for this user
    PLG_deleteUser ($uid);

    if ( function_exists('CUSTOM_userDeleteHook')) {
        CUSTOM_userDeleteHook($uid);
    }

    // Call custom account profile delete function if enabled and exists
    if ($_CONF['custom_registration'] && function_exists ('CUSTOM_userDelete')) {
        CUSTOM_userDelete ($uid);
    }

    // remove from all security groups
    $db->conn->delete($_TABLES['group_assignments'],array('ug_uid'=>$uid),array(Database::INTEGER));

    // remove user information and preferences
    $db->conn->delete($_TABLES['userprefs'],
                        array('uid'=> $uid),
                        array(Database::INTEGER)
                     );
    $db->conn->delete($_TABLES['userindex'],
                        array('uid' => $uid),
                        array(Database::INTEGER)
                     );
    $db->conn->delete($_TABLES['usercomment'],
                        array('uid' => $uid),
                        array(Database::INTEGER)
                      );
    $db->conn->delete($_TABLES['userinfo'],
                        array('uid' => $uid),
                        array(Database::INTEGER)
                     );
    $db->conn->delete($_TABLES['social_follow_user'],
                        array('uid' => $uid),
                        array(Database::INTEGER)
                     );
    $db->conn->delete($_TABLES['tfa_backup_codes'],
                        array('uid' => $uid),
                        array(Database::INTEGER)
                     );

    // anonymize comments
    $db->conn->update($_TABLES['comments'],
                      array('name' => 'Anonymous'),
                      array('uid' => $uid),
                      array(Database::STRING, Database::INTEGER)
    );
    $db->conn->update($_TABLES['comments'],
                      array('ipaddress' => ''),
                      array('uid' => $uid),
                      array(Database::STRING, Database::INTEGER)
    );
    $db->conn->update($_TABLES['comments'],
                      array('uid' => 1),
                      array('uid' => $uid),
                      array(Database::INTEGER, Database::INTEGER)
    );
    $db->conn->update($_TABLES['commentedits'],
                      array('uid' => 1),
                      array('uid' => $uid),
                      array(Database::INTEGER, Database::INTEGER)
    );

    // anonymize stories
    $db->conn->update($_TABLES['stories'],
                      array('uid' => 1),
                      array('uid' => $uid),
                      array(Database::INTEGER, Database::INTEGER)
    );
    $db->conn->update($_TABLES['stories'],
                      array('owner_id' => 1),
                      array('uid' => $uid),
                      array(Database::INTEGER, Database::INTEGER)
    );

    // ratings
    $db->conn->update($_TABLES['rating_votes'],
                      array('ip_address' => ''),
                      array('uid' => $uid),
                      array(Database::STRING, Database::INTEGER)
    );
    $db->conn->update($_TABLES['rating_votes'],
                      array('uid' => 1),
                      array('uid' => $uid),
                      array(Database::INTEGER, Database::INTEGER)
    );

    // delete story submissions
    $db->conn->delete($_TABLES['storysubmission'],
                      array('uid' => $uid),
                      array(Database::INTEGER)
    );

    // delete user photo, if enabled & exists
    if ($_CONF['allow_user_photo'] == 1) {
        $photo = $db->getItem($_TABLES['users'],'photo',array('uid' => $uid));
        USER_deletePhoto ($photo, false);
    }

    // delete subscriptions
    $db->conn->delete($_TABLES['subscriptions'],
                      array('uid' => $uid),
                      array(Database::INTEGER)
    );

    // in case the user owned any objects that require Admin access, assign
    // them to the Root user with the lowest uid
    $rootgroup = $db->getItem($_TABLES['groups'], 'grp_id', array("grp_name" => 'Root'));
    $rootuser  = $db->conn->fetchColumn(
                    "SELECT DISTINCT ug_uid FROM `{$_TABLES['group_assignments']}`
                        WHERE ug_main_grp_id = ? ORDER BY ug_uid LIMIT 1",
                    array($rootgroup),
                    0,
                    array(Database::STRING)
                 );

    if ( $rootuser === false || $rootuser == '' || $rootuser < 2 ) {
        $rootuser = 2;
    }
    $db->conn->update($_TABLES['blocks'],
                      array('owner_id' => $rootuser),
                      array('owner_id' => $uid),
                      array(Database::INTEGER, Database::INTEGER)
    );
    $db->conn->update($_TABLES['topics'],
                      array('owner_id' => $rootuser),
                      array('owner_id' => $uid),
                      array(Database::INTEGER, Database::INTEGER)
    );

    // now delete the user
    $db->conn->delete($_TABLES['users'],
                      array('uid' => $uid),
                      array(Database::INTEGER)
    );

    return true;
}

/**
* Create a new password and send it to the user
*
* @param    string  $username   user's login name
* @param    string  $useremail  user's email address
* @param    int     $uid        user id of user
* @param    string  $passwd     user's password (optional)
* @return   bool                true = success, false = an error occured
*
*/
function USER_createAndSendPassword ($username, $useremail, $uid, $passwd = '')
{
    global $_CONF, $_SYSTEM, $_TABLES, $LANG04;

    if ( !isset($_SYSTEM['verification_token_ttl']) ) {
        $_SYSTEM['verification_token_ttl'] = 86400;
    }

    $activation_link = '';
    $remoteuser = 0;

    $uid = (int) $uid;

    $db = Database::getInstance();

    $storedPassword = $db->getItem($_TABLES['users'],'passwd',array('uid' => $uid),array(Database::INTEGER));
    $userStatus     = $db->getItem($_TABLES['users'],'status',array('uid' => $uid),array(Database::INTEGER));
    if ( $passwd == '' && substr($storedPassword,0,4) == '$H$9' ) {
        // no need to update password
    } else {
        if ( $passwd == '' ) {
            $passwd = SEC_generateStrongPassword(12,'lud');
        }
        $passwd2 = SEC_encryptPassword($passwd);
        $db->conn->update($_TABLES['users'],array('passwd' => $passwd2),array('uid' => $uid));
    }

    $remoteservice = $db->getItem($_TABLES['users'],'remoteservice',array('uid' => $uid));
    if ( $remoteservice != '' && $remoteservice != null ) {
        $remoteuser = 1;
    }

    if (file_exists ($_CONF['path_data'] . 'welcome_email.txt')) {
        $template = new Template ($_CONF['path_data']);
        $template->set_file (array ('mail' => 'welcome_email.txt'));
        $template->set_var ('auth_info',
                            "$LANG04[2]: $username\n$LANG04[4]: $passwd");
        $template->set_var ('site_url', $_CONF['site_url']);
        $template->set_var ('site_name', $_CONF['site_name']);
        $template->set_var ('site_slogan', $_CONF['site_slogan']);
        $template->set_var ('lang_text1', sprintf($LANG04[15],$_CONF['site_name']));
        $template->set_var ('lang_text2', $LANG04[14]);
        $template->set_var ('lang_username', $LANG04[2]);
        $template->set_var ('lang_password', $LANG04[4]);
        $template->set_var ('username', $username);
        $template->set_var ('password', $passwd);
        $template->set_var ('name', COM_getDisplayName ($uid));
        $template->parse ('output', 'mail');
        $mailtext = $template->get_var ('output');
    } else {
        $T = new Template($_CONF['path_layout'].'email/');
        $T->set_file(array(
            'html_msg'   => 'newuser_template_html.thtml',
            'text_msg'   => 'newuser_template_text.thtml'
        ));
        if ($remoteuser == 1) {
            $T->set_var(array(
                'url'                   => $_CONF['site_url'].'/usersettings.php',
                'lang_site_or_password' => $LANG04[171],
                'site_link_url'         => $_CONF['site_url'],
                'lang_activation'       => sprintf($LANG04[206],$_CONF['site_name']),
                'lang_button_text'      => '',
                'passwd'                => '',
            ));
        } else if ( $userStatus == USER_ACCOUNT_AWAITING_VERIFICATION ) {
            $verification_id = USER_createActivationToken($uid,$username);

            $T->set_var(array(
                'url'                   => $_CONF['site_url'].'/users.php?mode=verify&vid='.$verification_id.'&u='.$uid,
                'lang_site_or_password' => $LANG04[171],
                'site_link_url'         => $_CONF['site_url'],
                'lang_activation'       => sprintf($LANG04[172],($_SYSTEM['verification_token_ttl']/3600)),
                'lang_button_text'      => $LANG04[203],
                'localuser'             => true,
            ));
        } else {
            $T->set_var(array(
                'url'                   => $_CONF['site_url'].'/usersettings.php',
                'lang_site_or_password' => $LANG04[4],
                'site_link_url'         => '',
                'lang_activation'       => $LANG04[14],
                'lang_button_text'      => 'Change Password',
                'passwd'                => $passwd,
                'localuser'             => true,
            ));
        }
        $T->set_var(array(
            'title'         =>  $_CONF['site_name'] . ': ' . $LANG04[16],
            'site_name'     =>  $_CONF['site_name'],
            'username'      =>  $username,
        ));
        $T->parse ('output', 'html_msg');
        $mailhtml = $T->finish($T->get_var('output'));

        $T->parse ('output', 'text_msg');
        $mailtext = $T->finish($T->get_var('output'));
    }
    $msgData['htmlmessage'] = $mailhtml;
    $msgData['textmessage'] = $mailtext;
    $msgData['subject'] = $_CONF['site_name'] . ': ' . $LANG04[16];

    $to = array();
    $from = array();
    $from = COM_formatEmailAddress( $_CONF['site_name'], $_CONF['noreply_mail'] );
    $to = COM_formatEmailAddress('',$useremail);

    return COM_mail( $to, $msgData['subject'], $msgData['htmlmessage'], $from, true, 0,'', $msgData['textmessage'] );

}

function USER_createActivationToken($uid,$username)
{
    global $_CONF, $_TABLES;

    $db = Database::getInstance();

    $token = md5($uid.$username.uniqid (mt_rand (), 1));

    $db->conn->update($_TABLES['users'],array('act_token'=>$token, 'act_time'=>$_CONF['_now']->toMySQL(true)),array('uid'=>$uid));

    return $token;
}


/**
* Inform a user their account has been activated.
*
* @param    string  $username   user's login name
* @param    string  $useremail  user's email address
* @return   boolean             true = success, false = an error occured
*
*/
//@DEPRECIATED - NO LONGER USER
function USER_sendActivationEmail ($username, $useremail)
{
    global $_CONF, $_TABLES, $LANG04;

    if (file_exists ($_CONF['path_data'] . 'activation_email.txt')) {
        $template = new Template ($_CONF['path_data']);
        $template->set_file (array ('mail' => 'activation_email.txt'));
        $template->set_var ('site_name', $_CONF['site_name']);
        $template->set_var ('site_slogan', $_CONF['site_slogan']);
        $template->set_var ('lang_text1', sprintf($LANG04[15],$_CONF['site_name']));
        $template->set_var ('lang_text2', $LANG04[14]);
        $template->parse ('output', 'mail');
        $mailtext = $template->get_var ('output');
    } else {
//        $mailtext = str_replace("<username>", $username, sprintf($LANG04[118],$_CONF['site_name'])) . "\n\n";
        $mailtext = sprintf($LANG04[118],$username, $_CONF['site_name']) . "\n\n";
        $mailtext .= $_CONF['site_url'] ."\n\n";
        $mailtext .= $LANG04[119] . "\n\n";
        $mailtext .= $_CONF['site_url'] ."/users.php?mode=getpassword\n\n";
        $mailtext .= $_CONF['site_name'] . "\n";
        $mailtext .= $_CONF['site_url'] . "\n";
    }
    $subject = $_CONF['site_name'] . ': ' . $LANG04[120];
    if ($_CONF['site_mail'] !== $_CONF['noreply_mail']) {
        $mailfrom = $_CONF['noreply_mail'];
        global $LANG_LOGIN;
        $mailtext .= LB . LB . $LANG04[159];
    } else {
        $mailfrom = $_CONF['site_mail'];
    }

    $to = array();
    $from = array();
    $from = COM_formatEmailAddress('',$mailfrom);
    $to   = COM_formatEmailAddress( '',$useremail );
    return COM_mail ($to, $subject, $mailtext, $from);
}

/**
* Create a new user
*
* Also calls the custom user registration (if enabled) and plugin functions.
*
* NOTE: Does NOT send out password emails.
*
* @param    string  $username   user name (mandatory)
* @param    string  $email      user's email address (mandatory)
* @param    string  $passwd     password (optional, see above)
* @param    string  $fullname   user's full name (optional)
* @param    string  $homepage   user's home page (optional)
* @param    boolean $batchimport set to true when called from importuser() in admin/users.php (optional)
* @return   int                 new user's ID
*
*/
function USER_createAccount ($username, $email, $passwd = '', $fullname = '', $homepage = '', $remoteusername = '', $service = '', $ignore = 0)
{
    global $_CONF, $_USER, $_TABLES;

    $dt = new Date('now',$_USER['tzid']);

    $db = Database::getInstance();

    $fields = array();
    $values = array();
    $types  = array();

    $queueUser = false;

    $regdate = $dt->toMySQL(true);

    $fields = array('username'  => $username,
                    'email'     => $email,
                    'regdate'   => $regdate,
                    'cookietimeout' => $_CONF['default_perm_cookie_timeout']
              );
    $types  = array(Database::STRING,Database::STRING,Database::STRING,Database::STRING);

    if (!empty ($passwd)) {
        $fields['passwd'] = $passwd;
        $types[] = Database::STRING;
    }
    if (!empty ($fullname)) {
        $fields['fullname'] = $fullname;
        $types[] = Database::STRING;
    }
    if (!empty ($homepage)) {

        $fields['homepage'] = $homepage;
        $types[] = Database::STRING;
    }
    $account_type = LOCAL_USER;

    if (($_CONF['usersubmission'] == 1) && !SEC_hasRights ('user.edit')) {
        $queueUser = true;
        if (!empty ($_CONF['allow_domains'])) {
            if (USER_emailMatches ($email, $_CONF['allow_domains'])) {
                $queueUser = false;
            }
        }

        if ($queueUser) {
            $fields['status'] = USER_ACCOUNT_AWAITING_APPROVAL;
            $types[] = Database::INTEGER;
        }
    } else {
        if (($_CONF['registration_type'] == 1 ) && (empty($remoteusername) || empty($service))) {
            $fields['status'] = USER_ACCOUNT_AWAITING_VERIFICATION;
            $types[] = Database::INTEGER;
        }
    }

    if (!empty($remoteusername)) {

        $fields['remoteusername'] = $remoteusername;
        $types[] = Database::STRING;
        $account_type = REMOTE_USER;
    }
    if (!empty($service)) {
        $fields['remoteservice'] = $service;
        $types[] = Database::STRING;
    }

    $fields['account_type'] = $account_type;
    $types[] = Database::INTEGER;

// our first attempt...
    $db->conn->beginTransaction();
    try {

        $db->conn->beginTransaction();
        try {
            $db->conn->insert($_TABLES['users'],$fields,$types);
            $db->conn->commit();
        } catch(\Doctrine\DBAL\DBALException $e) {
            Log::write('system',Log::ERROR,"Error inserting user into USERS table :: " . $e->getMessage());
            $db->conn->rollBack();
            return null;
        }

        // Get the uid of the user, possibly given a service:
        if ($remoteusername != '') {
            $uid = $db->getItem($_TABLES['users'],'uid',array('remoteusername' => $remoteusername,'remoteservice' => $service));
        } else {
            $uid = $db->conn->fetchColumn(
                "SELECT uid FROM `{$_TABLES['users']}` WHERE username=? AND remoteservice IS NULL",
                array($username),
                0,
                array(Database::STRING)
            );
        }
        if ( $uid === false  ) {
            Log::write('system',Log::ERROR,"Error: Unable to retrieve uid after creating user");
            $db->conn->rollBack();
            return NULL;
        }

        // Add user to Logged-in group (i.e. members) and the All Users group
        $loggedInUsersGrp = $db->getItem ($_TABLES['groups'], 'grp_id',
                                        array('grp_name' => 'Logged-in Users'));

        $all_grp = $db->getItem ($_TABLES['groups'], 'grp_id',
                                        array('grp_name'=>'All Users'));

        $db->conn->beginTransaction();
        try {
            $db->conn->insert($_TABLES['group_assignments'],
                              array(
                                'ug_main_grp_id' => $loggedInUsersGrp,
                                'ug_uid' => $uid
                               ),
                               array(
                                Database::INTEGER,
                                Database::INTEGER
                               )
            );

            $db->conn->insert($_TABLES['group_assignments'],
                              array(
                                'ug_main_grp_id' => $all_grp,
                                'ug_uid' => $uid
                              ),
                              array(
                                Database::INTEGER,
                                Database::INTEGER
                              )
            );
            $db->conn->commit();
        } catch(\Doctrine\DBAL\DBALException $e) {
            $db->conn->rollBack();
            return null;
        }

        // any default groups?
        $stmt = $db->conn->query("SELECT grp_id FROM `{$_TABLES['groups']}` WHERE grp_default=1");
        $grpDefaults = $stmt->fetchAll(Database::ASSOCIATIVE);
        foreach ($grpDefaults AS $row) {
            $db->conn->insert($_TABLES['group_assignments'],
                                array(
                                    'ug_main_grp_id' => $row['grp_id'],
                                    'ug_uid'=> $uid
                                ),
                                array(
                                    Database::INTEGER,
                                    Database::INTEGER
                                )
            );
        }
        $db->conn->insert($_TABLES['userprefs'],
                            array(
                                'uid' => $uid,
                                'tzid' => $_CONF['timezone']
                            ),
                            array(
                                Database::INTEGER,
                                Database::STRING
                            )
        );
        $etids = '';
        if ($_CONF['emailstoriesperdefault'] == 1) {
            $etids = '-';
        }

        $db->conn->insert($_TABLES['userindex'],
                            array('uid' => $uid,'etids' => $etids),
                            array(Database::INTEGER, Database::STRING)
        );

        $db->conn->insert($_TABLES['usercomment'],
                            array('uid' => $uid,'commentmode' => $_CONF['comment_mode'], 'commentlimit' => $_CONF['comment_limit']),
                            array(Database::INTEGER, Database::STRING, Database::INTEGER)
        );

        $db->conn->insert($_TABLES['userinfo'],
            array('uid' => $uid),
            array(Database::INTEGER)
        );

        $db->conn->commit();

    } catch (\Exception $e) {
        $db->conn->rollBack();
        Log::write('system',Log::ERROR,'There was an error in creating the user - Database transaction rolledback');
        return NULL;
    }

    // call custom registration function and plugins
    if ($_CONF['custom_registration'] && (function_exists ('CUSTOM_userCreate'))) {
        CUSTOM_userCreate ($uid,$batchimport);
    }
    if ( function_exists('CUSTOM_userCreateHook') ) {
        CUSTOM_userCreateHook($uid);
    }

    if ( $ignore == 0 ) PLG_createUser($uid);

    // Notify the admin?
    if (($ignore == 0) && (isset ($_CONF['notification']) && in_array ('user', $_CONF['notification']))) {
        if ($queueUser) {
            $mode = 'inactive';
        } else {
            $mode = 'active';
        }
        USER_sendNotification ($username, $email, $uid, $mode);
    }

    return $uid;
}

/**
* Send an email notification when a new user registers with the site.
*
* @param username string      User name of the new user
* @param email    string      Email address of the new user
* @param uid      int         User id of the new user
* @param mode     string      Mode user was added at.
*
*/
function USER_sendNotification ($username, $email, $uid, $mode='inactive')
{
    global $_CONF, $_USER, $_TABLES, $LANG01, $LANG04, $LANG08, $LANG28, $LANG29;

    $dt = new Date('now',$_USER['tzid']);

    $mailbody = "$LANG04[2]: $username\n"
              . "$LANG04[5]: $email\n"
              . "$LANG28[14]: " . $dt->format($_CONF['date'],true) . "\n\n";

    if ($mode == 'inactive') {
        // user needs admin approval
        $mailbody .= "$LANG01[10] {$_CONF['site_admin_url']}/moderation.php\n\n";
    } else {
        // user has been created, or has activated themselves:
        $mailbody .= "$LANG29[4] {$_CONF['site_url']}/users.php?mode=profile&uid={$uid}\n\n";
    }
    $mailbody .= "\n------------------------------\n";
    $mailbody .= "$LANG08[34]";
    $mailbody .= "\n------------------------------\n";

    $mailsubject = $_CONF['site_name'] . ' ' . $LANG29[40];

    $to = array();
    $to   = COM_formatEmailAddress( '',$_CONF['noreply_mail'] );
    COM_mail ($to, $mailsubject, $mailbody);
}

/**
* Get a user's photo, either uploaded or from an external service
*
* @param    int     $uid    User ID
* @param    string  $photo  name of the user's uploaded image
* @param    string  $email  user's email address (for gravatar.com)
* @param    int     $width  preferred image width
* @param    int     $fullURL if true, send full <img> tag, otherwise just the path to image
* @return   string          <img> tag or empty string if no image available
*
* @note     All parameters are optional and can be passed as 0 / empty string.
*
*/
function USER_getPhoto ($uid = 0, $photo = '', $email = '', $width = 0, $fullURL = 1)
{
    global $_CONF, $_TABLES, $_USER;

    $userphoto = '';
    $uid = (int) $uid;

    if ($_CONF['allow_user_photo'] == 1) {

        $db = Database::getInstance();

        if (($width == 0) && !empty ($_CONF['force_photo_width'])) {
            $width = $_CONF['force_photo_width'];
        }

        // collect user's information with as few SQL requests as possible
        if ($uid == 0 && isset($_USER['uid']) && $_USER['uid'] > 1) {
            $uid = $_USER['uid'];
            if (empty ($email)) {
                $email = $_USER['email'];
            }
            if (!empty ($_USER['photo']) &&
                    (empty ($photo) || ($photo == '(none)'))) {
                $photo = $_USER['photo'];
            }
        }
        if ((empty ($photo) || ($photo == '(none)')) ||
                (empty ($email) && $_CONF['use_gravatar'])) {

            $photoRec = $db->conn->fetchAssoc("SELECT email,photo FROM
                        `{$_TABLES['users']}`
                        WHERE uid = ?",
                        array($uid),
                        array(Database::INTEGER)
            );
            $newemail = $photoRec['email'];
            $newphoto = $photoRec['photo'];

            if (empty ($photo) || ($photo == '(none)')) {
                $photo = $newphoto;
            }
            if (empty ($email)) {
                $email = $newemail;
            }
        }

        $img = '';
        if (empty ($photo) || ($photo == '(none)') || $photo == '' ) {
           // no photo - try gravatar.com, if allowed
            if ($_CONF['use_gravatar']) {
                $img = '//www.gravatar.com/avatar/'.md5( $email );
                $url_parms = array();
                if ($width > 0) {
                    $url_parms[] = 's=' . $width;
                }
                if ( ! empty($_CONF['gravatar_rating']) ) {
                    $url_parms[] = 'r=' . $_CONF['gravatar_rating'];
                }
                if ( ! empty($_CONF['default_photo']) ) {
                    $url_parms[] = 'd=' . urlencode($_CONF['default_photo']);
                }
                if (count($url_parms) > 0) {
                    $img .= '?' . implode('&amp;', $url_parms);
                }
            }
        } else {
            // check if images are inside or outside the document root
            if (strstr ($_CONF['path_images'], $_CONF['path_html'])) {
                $imgpath = substr ($_CONF['path_images'],
                                   strlen ($_CONF['path_html']));
                $img = $_CONF['site_url'] . '/' . $imgpath . 'userphotos/'
                     . $photo;
                if ( !@file_exists( $_CONF['path_html'] . $imgpath . 'userphotos/'.$photo ) ) {
                    $img = '';
                }
            } else {
                $img = $_CONF['site_url']
                     . '/getimage.php?mode=userphotos&amp;image=' . $photo;
            }
        }

        if (empty($img) || $img == '' ) {
            if ( !isset($_CONF['default_photo']) || $_CONF['default_photo'] == '' ) {
                $img = $_CONF['site_url'] . '/images/userphotos/default.jpg';
            } else {
                $img = $_CONF['default_photo'];
            }
        }

        if ( $fullURL != 1 ) {
            return $img;
        }

        if (!empty ($img)) {
            $userphoto = '<img src="' . $img . '"';
            if ($width > 0) {
                $userphoto .= ' width="' . $width . '"';
            }
            $userphoto .= ' alt="" class="userphoto" />';
        }
    }

    return $userphoto;
}

/**
* Delete a user's photo (i.e. the actual file)
*
* NOTE:     Will silently ignore non-existing files.
*
* @param    string  $photo          name of the photo (without the path)
* @param    boolean $abortonerror   true: abort script on error, false: don't
* @return   void
*
*/
function USER_deletePhoto ($photo, $abortonerror = true)
{
    global $_CONF, $LANG04;

    if (!empty ($photo)) {
        $filetodelete = $_CONF['path_images'] . 'userphotos/' . $photo;
        if (file_exists ($filetodelete)) {
            if (!@unlink ($filetodelete)) {
                if ($abortonerror) {
                    Log::write('system',Log::ERROR,'Unable to remove the user\'s photo file: '.$photo);

                    $display = COM_siteHeader ('menu', $LANG04[21])
                             . "Unable to remove file $photo"
                             . COM_siteFooter ();
                    echo $display;
                    exit;
                } else {
                    // just log the problem, but don't abort
                    Log::write('system',Log::ERROR,'Unable to remove the user\'s photo file: '.$photo);
                }
            }
        }
    }
}

/**
* Add user to group if user does not belong to specified group
*
* This is part of the glFusion user implementation. This function
* looks up whether a user belongs to a specified group and if not
* adds them to the group
*
* @param        int      $groupid     Group we want to see if user belongs to and if not add to group
* @param        int         $uid        ID for user to check if in group and if not add user. If empty current user.
* @return       boolean     true if user is added to group, otherwise false
*
*/
function USER_addGroup ($groupid, $uid = '')
{
    global $_CONF, $_TABLES, $_USER;

    $db = Database::getInstance();

     // set $uid if $uid is empty
    if (empty ($uid)) {
        // bail for anonymous users
        if (COM_isAnonUser()) {
            return false;
        } else {
            // If logged in set to current uid
            $uid = $_USER['uid'];
        }
    } else {
        $uid = (int) $uid;
    }

    $groupid = (int) $groupid;

    if (($groupid < 1) || SEC_inGroup ($groupid, $uid)) {
        return false;
    } else {
        $db->conn->insert($_TABLES['group_assignments'],
                        array(
                            'ug_main_grp_id' => $groupid,
                            'ug_uid' => $uid
                        ),
                        array(
                            Database::INTEGER,
                            Database::INTEGER
                        )
        );

        return true;
    }
}

/**
* Delete from group if user belongs to specified group
*
* This is part of the glFusion user implementation. This function
* looks up whether a user belongs to a specified group and if so
* removes them from the group
*
* @param        int      $groupid      Group we want to see if user belongs to and if so delete user from group
* @param        int         $uid          ID for user to delete. If empty current user.
* @return       boolean     true if user is removed from group, otherwise false
*
*/
function  USER_delGroup ($groupid, $uid = '')
{
    global $_CONF, $_TABLES, $_USER;

    $db = Database::getInstance();

    // set $uid if $uid is empty
    if (empty ($uid)) {
        // bail for anonymous users
        if (COM_isAnonUser()) {
            return false;
        } else {
            // If logged in set to current uid
            $uid = $_USER['uid'];
        }
    } else {
        $uid = (int) $uid;
    }

    $groupid = (int) $groupid;

    if (($groupid > 0) && SEC_inGroup ($groupid, $uid)) {
        $db->conn->delete($_TABLES['group_assignments'],array('ug_main_grp_id'=>$groupid,'ug_uid'=>$uid),array(Database::INTEGER,Database::INTEGER));
        return true;
    } else {
        return false;
    }
}

/**
* Check email address against a list of domains
*
* Checks if the given email's domain part matches one of the entries in a
* comma-separated list of domain names (regular expressions are allowed).
*
* @param    string  $email          email address to check
* @param    string  $domain_list    list of domain names
* @return   boolean                 true if match found, otherwise false
*
*/
function USER_emailMatches ($email, $domain_list)
{
    $match_found = false;

    if (!empty ($domain_list)) {
        $domains = explode (',', $domain_list);

        // Note: We should already have made sure that $email is a valid address
        $email_domain = substr ($email, strpos ($email, '@') + 1);
        $email_domain = trim($email_domain);

        foreach ($domains as $domain) {
            $domain = trim($domain);
            if (preg_match ("#$domain#i", $email_domain)) {
                $match_found = true;
                break;
            }
        }
    }

    return $match_found;
}

/**
* Ensure unique username
*
* Checks that $username does not exist yet and creates a new unique username
* (based off of $username) if necessary.
* Mostly useful for creating accounts for remote users.
*
* @param    string  $username   initial username
* @return   string              unique username
* @todo     Bugs: Race conditions apply ...
*
*/
function USER_uniqueUsername($username)
{
    global $_TABLES;

    if (function_exists('CUSTOM_uniqueUsername')) {
        return CUSTOM_uniqueUsername($username);
    }

    $try = $username;
    do {
        $uid = $db->getItem($_TABLES['users'],array('username'=> $try));
        if (!empty($uid) && $uid !== false) {
            $r = rand(2, 9999);
            if (strlen($username) > 12) {
                $try = sprintf('%s%d', substr($username, 0, 12), $r);
            } else {
                $try = sprintf('%s%d', $username, $r);
            }
        }
    } while (!empty($uid));

    return $try;
}


/**
* Check to see if the username contains invalid characters.
* Also checks if it includes the " character, which we don't allow in usernames.
* Used for registering, changing names, and posting anonymously with a username
*
* @param string $username The username to check
*
* @return	boolean     true if OK, false if not
*/
function USER_validateUsername($username, $existing_user = 0)
{
	global $_CONF, $_TABLES, $_USER;

    if ( $existing_user == 0 ) {
    	if ( strlen($username) < $_CONF['min_username_length'] ) {
    	    return false;
    	}
    }
    $regex = '[\x00-\x1F\x7F<>"%&*\/\\\\]';
	// ... fast checks first.
	if (strpos($username, '&quot;') !== false || strpos($username, '"') !== false ) {
		return false;
	}
	if (preg_match('/' . $regex . '/u', $username)) {
    	return false;
	}
	$retgex = "[\x{10000}-\x{10FFFF}]";
	if ( preg_match('/' . $regex . '/u', $username)) {
	    return false;
	}
    $regex = "([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{200D}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F9FF}][\x{FE00}-\x{FEFF}]?";
	if ( preg_match('/' . $regex . '/u', $username)) {
	    return false;
	}
	return true;
}


/**
* Sanitize a name by removing the disallowed characters.
* Also checks if it includes the " character, which we don't allow in usernames.
* Used for registering, changing names, and posting anonymously with a username
*
* @param string $text The text to sanitize
*
* @return	string the santized name (username / fullname )
*/
function USER_sanitizeName($text)
{
    $filter = \sanitizer::getInstance();
    $result = $filter->sanitizeUsername($text);
    return $result;
}


/**
* Used to return an array of groups that a base group contains
* GL supports hierarchical groups and this will return all the child groups
*
* @param    int     $groupid        Group id to get list of groups for
* @return   array                   Array of child groups
*
*/
function USER_getChildGroups($groupid)
{
    global $_TABLES;

    $db = Database::getInstance();

Log::debug("in USER_getChildGroups");

    $to_check = array();
    array_push($to_check, $groupid);
    $groups = array();
    while (sizeof($to_check) > 0) {
        $thisgroup = array_pop($to_check);
        if ($thisgroup > 0) {
            $stmt = $db->conn->executeQuery(
                "SELECT ug_grp_id FROM `{$_TABLES['group_assignments']}` WHERE ug_main_grp_id = ?",
                array($thisgroup),
                array(Database::INTEGER)
            );

            while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
                if (!in_array($A['ug_grp_id'], $groups)) {
                    if (!in_array($A['ug_grp_id'], $to_check)) {
                        array_push($to_check, $A['ug_grp_id']);
                    }
                }
            }
            $groups[] = $thisgroup;
        }
    }

    return $groups;
}

/**
* Generate a password
*
* @param    int     $length         Length of the password
* @return   string                  Generated password
*
*/

function USER_createPassword ($length = 7)
{
    // Enforce reasonable limits
    if (($length < 5) || ($length > 10)) {
        $length = 7;
    }

    // Exclude 0,O,o,1,i,I,L,l because they're frequently mistyped
    // -----------------------------------------------------------
    $legal_characters = "-23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ";

    $password = "";
    $num_legal_chars = strlen($legal_characters);
    while (strlen($password) < $length) {
        $password .= $legal_characters[mt_rand(0,$num_legal_chars-1)];
    }

    return($password);
}

/**
* Build a list of all topics the current user has access to
*
* @return   string   List of topic IDs, separated by spaces
*
*/
function USER_buildTopicList ()
{
    global $_TABLES;

    $topics = '';

    $db = Database::getInstance();

    $stmt = $db->conn->query("SELECT tid FROM `{$_TABLES['topics']}`");
    while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
        if (SEC_hasTopicAccess ($A['tid'])) {
            $topics .= $A['tid'] . ' ';
        }
    }
    return (rtrim($topics));
}

function USER_mergeAccountScreen( $remoteUID, $localUID, $msg='' )
{
    global $_USER, $_CONF, $_TABLES;

    $retval = '';

    $db = Database::getInstance();

    $row = $db->conn->fetchAssoc(
        "SELECT * FROM `{$_TABLES['users']}` WHERE uid = ?",
        array($localUID),
        array(Database::INTEGER)
    );
    // if no user is found
    if ($row === null || $row === false) {
        echo COM_refresh($_CONF['site_url'].'/index.php');
    }

    $T = new Template($_CONF['path_layout'].'/users/');
    $T->set_file('merge','mergeacct.thtml');
    $T->set_var(array(
        'localuid'       => $row['uid'],
        'local_username' => $row['username'],
        'remoteuid'      => $remoteUID,
        'sec_token'      => CSRF_TOKEN,
        'token'          => SEC_createToken(),
    ));
    $T->parse( 'page', 'merge' );
    if ( $msg != '' ) {
        $retval .= COM_showMessageText($msg,'',false,'info');
    }
    $retval .= $T->finish( $T->get_var( 'page' ));

    echo COM_siteHeader();
    echo $retval;
    echo COM_siteFooter();
    exit;
}

/**
* Un-Merge User Accounts
*
* This validates the entered password and then unlinks a remote account
* from the local account
*
* @return   bool          true on success, false on failure
*
*/
function USER_unmergeAccounts()
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER;

    $db = Database::getInstance();

    $retval = false;

    $localUID = (int) filter_input(INPUT_POST, 'localuid', FILTER_SANITIZE_NUMBER_INT);
    $localpwd  = $_POST['passwd'];

    $localUserInfo = $db->conn->fetchAssoc(
            "SELECT * FROM `{$_TABLES['users']} WHERE uid=?",
            array($localUID),
            array(Database::INTEGER)
    );

    if ($localUserInfo !== false && $localUserInfo !== null) {
        if ( SEC_check_hash($localpwd, $localUserInfo['passwd']) ) {

            $db->conn->update(
                    $_TABLES['users'],
                    array(
                        'account_type' => LOCAL_USER,
                        'remoteusername' => '',
                        'remoteservice' => null
                    ),
                    array(
                        'uid' => $localUID
                    ),
                    array(
                        Database::INTEGER,
                        Database::STRING,
                        Database::STRING,
                        Database::INTEGER
                    )
            );
            $retval = true;
        }
    }
    return $retval;
}

/**
* Merge User Accounts
*
* This validates the entered password and then merges a remote
* account with a local account.
*
* @return   string          HTML merge form if error, redirect on success
*
*/
function USER_mergeAccounts()
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG04, $LANG12, $LANG20;

    $retval = '';

    $db = Database::getInstance();

    $remoteUID = (int) filter_input(INPUT_POST, 'remoteuid', FILTER_SANITIZE_NUMBER_INT);
    $localUID  = (int) filter_input(INPUT_POST, 'localuid', FILTER_SANITIZE_NUMBER_INT);
    $localpwd  = $_POST['localp'];

    $userData = $db->conn->fetchAssoc(
            "SELECT * FROM `{$_TABLES['users']}` WHERE uid=?",
            array($localUID),
            array(Database::INTEGER)
    );
    if ($userData === false || $userData === null) {
        Log::write('system',Log::WARNING,"ERROR: Attempting to merge local UID: " . $localUID. " with remote UID: " . $remoteUID . " failed due to uid mismatch");
        echo COM_refresh($_CONF['site_url'].'/index.php');
    }
    if (SEC_check_hash($localpwd, $userData['passwd'])) {
        // password is valid
        $remoteUserData = $db->conn->fetchAssoc(
            "SELECT * FROM `{$_TABLES['users']}` WHERE account_type = ? AND email=? AND uid = ?",
            array(
                REMOTE_USER,
                $userData['email'],
                $remoteUID
            ),
            array(Database::INTEGER,Database::STRING,Database::INTEGER)
        );
        if ($remoteUserData === false || $remoteUserData === null) {
            Log::write('system',Log::ERROR,"Attempting to merge local UID: " . $localUID. " with remote UID: " . $remoteUID . " failed due to uid mismatch");
            echo COM_refresh($_CONF['site_url'].'/index.php');
        }
        if ($remoteUID != $remoteUserData['uid']) {
            Log::write('system',Log::ERROR,"Attempting to merge local UID: " . $localUID. " with remote UID: " . $remoteUID . " failed due to uid mismatch");
            echo COM_refresh($_CONF['site_url'].'/index.php');
        }
        $remoteUID = (int) $remoteUserData['uid'];
        $remoteService = substr($remoteUserData['remoteservice'],6);

        $db->conn->update($_TABLES['users'],
                array(
                    'remoteusername' => $remoteUserData['remoteusername'],
                    'remoteservice' => $remoteUserData['remoteservice'],
                    'account_type'  => 3
                ),
                array(
                    'uid' => $localUID
                ),
                array(
                    Database::STRING,
                    Database::STRING,
                    Database::INTEGER
                )
        );

        $_USER['uid'] = $localRow['uid'];
        $local_login = true;

        SESS_completeLogin($localUID);
        $_GROUPS = SEC_getUserGroups( $_USER['uid'] );
        $_RIGHTS = explode( ',', SEC_getUserPermissions() );
        if ($_SYSTEM['admin_session'] > 0 && $local_login ) {
            if (SEC_isModerator() || SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit','OR')
                     || (count(PLG_getAdminOptions()) > 0)) {
                $admin_token = SEC_createTokenGeneral('administration',$_SYSTEM['admin_session']);
                SEC_setCookie('token',$admin_token,0,$_CONF['cookie_path'],$_CONF['cookiedomain'],$_CONF['cookiesecure'],true);
            }
        }
        COM_resetSpeedlimit('login');

        // log the user out
        SESS_endUserSession ($remoteUID);

        // Let plugins know a user is being merged
        PLG_moveUser($remoteUID, $_USER['uid']);

        // Ok, now delete everything related to this user

        // let plugins update their data for this user
        PLG_deleteUser ($remoteUID);

        if ( function_exists('CUSTOM_userDeleteHook')) {
            CUSTOM_userDeleteHook($remoteUID);
        }

        // Call custom account profile delete function if enabled and exists
        if ($_CONF['custom_registration'] && function_exists ('CUSTOM_userDelete')) {
            CUSTOM_userDelete ($remoteUID);
        }

        // remove from all security groups
        $db->conn->delete($_TABLES['group_assignments'],array('ug_uid'=>$remoteUID),array(Database::INTEGER));

        // remove user information and preferences
        $db->conn->delete($_TABLES['userprefs'], array('uid' => $remoteUID),array(Database::INTEGER));
        $db->conn->delete($_TABLES['userindex'], array('uid' => $remoteUID),array(Database::INTEGER));
        $db->conn->delete($_TABLES['usercomment'], array('uid' => $remoteUID),array(Database::INTEGER));
        $db->conn->delete($_TABLES['userinfo'], array('uid' => $remoteUID),array(Database::INTEGER));

        // delete user photo, if enabled & exists
        if ($_CONF['allow_user_photo'] == 1) {
            $photo = $db->getItem ($_TABLES['users'], 'photo', array('uid' => $remoteUID),array(Database::INTEGER));
            USER_deletePhoto ($photo, false);
        }

        // delete subscriptions
        $db->conn->delete($_TABLES['subscriptions'],array('uid' => $remoteUID),array(Database::INTEGER));

        // in case the user owned any objects that require Admin access, assign
        // them to the Root user with the lowest uid
        $rootgroup = $db->getItem ($_TABLES['groups'], 'grp_id', array('grp_name' => 'Root'));

        $row = $db->conn->fetchAssoc(
            "SELECT DISTINCT ug_uid FROM `{$_TABLES['group_assignments']}` WHERE ug_main_grp_id = ? ORDER BY ug_uid LIMIT 1",
            array($rootgroup),
            array(Database::INTEGER)
        );
        $rootuser = $row['ug_uid'];

        if ( $rootuser == '' || $rootuser < 2 ) {
            $rootuser = 2;
        }
        $db->conn->update($_TABLES['blocks'],
                    array('owner_id' => $rootuser),
                    array('owner_id' => $remoteUID),
                    array(Database::INTEGER,Database::INTEGER)
        );
        $db->conn->update($_TABLES['topics'],
                    array('owner_id' => $rootuser),
                    array('owner_id' => $remoteUID),
                    array(Database::INTEGER,Database::INTEGER)
        );
        // now delete the user itself
        $db->conn->delete($_TABLES['users'], array('uid' => $remoteUID),array(Database::INTEGER));
    } else {
        // invalid password - let's try one more time
        // need to set speed limit and give them 3 tries
        COM_clearSpeedlimit($_CONF['login_speedlimit'], 'merge');
        $last = COM_checkSpeedlimit ('merge',4);
        if ($last > 0) {
            COM_setMsg($LANG04[190],'error');
            echo COM_refresh($_CONF['site_url'].'/users.php');
        } else {
            COM_updateSpeedlimit ('merge');
            USER_mergeAccountScreen($remoteUID,$localUID,$LANG20[3]);
        }
        return $retval;
    }
    // can't use COM_setMsg here since the session is being destroyed.
    echo COM_refresh($_CONF['site_url'].'/index.php?msg=522');
}
?>