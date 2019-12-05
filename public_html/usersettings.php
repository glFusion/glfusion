<?php
/**
* glFusion CMS
*
* glFusion user settings page
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2019 by the following authors:
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
use \glFusion\Cache\Cache;
use \glFusion\Social\Social;

USES_lib_user();

// Set this to true to have this script generate various debug messages in
// error.log
$_US_VERBOSE = false;

/**
* Shows the user's current settings
*
*/
function edituser()
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG04, $LANG_ADMIN, $LANG_TFA;

    $db = Database::getInstance();

    $A = $db->conn->fetchAssoc(
            "SELECT *
             FROM `{$_TABLES['users']}` AS u,`{$_TABLES['userprefs']}` AS up,`{$_TABLES['userinfo']}` AS ui
             WHERE u.uid = ? AND up.uid = ? AND ui.uid=?",
            array($_USER['uid'],$_USER['uid'],$_USER['uid']),
            array(Database::INTEGER,Database::INTEGER,Database::INTEGER)
    );

//    $result = DB_query("SELECT fullname,cookietimeout,email,homepage,sig,emailstories,about,location,pgpkey,photo,remoteservice,account_type FROM {$_TABLES['users']},{$_TABLES['userprefs']},{$_TABLES['userinfo']} WHERE {$_TABLES['users']}.uid = {$_USER['uid']} AND {$_TABLES['userprefs']}.uid = {$_USER['uid']} AND {$_TABLES['userinfo']}.uid=".(int)$_USER['uid']);
//    $A = DB_fetchArray ($result);

    $preferences = new Template ($_CONF['path_layout'] . 'preferences');
    $preferences->set_file (array ('profile'            => 'profile.thtml',
                                   'photo'              => 'userphoto.thtml',
                                   'username'           => 'username.thtml',
                                   'current_password'   => 'current_password.thtml',
                                   'password'           => 'password.thtml',
                                   'resynch'            => 'resynch.thtml',
                                   'deleteaccount'      => 'deleteaccount.thtml'));


    $navbar = new navbar;
    $cnt = 0;
    if ( is_array($LANG_MYACCOUNT) ) {
        foreach ($LANG_MYACCOUNT as $id => $label) {
            if ( $id == 'pe_content' && $_CONF['hide_exclude_content'] == 1 && $_CONF['emailstories'] == 0 ) {
                continue;
            } elseif ( $id == 'pe_twofactor' && (!isset($_CONF['enable_twofactor']) || $_CONF['enable_twofactor'] == 0 || !function_exists('hash_hmac')) ) {
                continue;
            } else {
                $navbar->add_menuitem($label,'showhideProfileEditorDiv("'.$id.'",'.$cnt.');return false;',true);
                $cnt++;
            }
        }
        if ($_CONF['allow_account_delete'] == 1) {
            $navbar->add_menuitem($LANG04[156],'showhideProfileEditorDiv("pe_delete",'.$cnt.');return false;',true);
            $cnt++;
        }
        if ( isset($_GET['mode']) && $_GET['mode'] == 'delete' && $_CONF['allow_account_delete'] == 1 ) {
            $navbar->set_selected($LANG04[156]);
            $preferences->set_var('delete_active',true);
            $preferences->set_var('delete_idx',$cnt-1);
        } else {
            $navbar->set_selected($LANG_MYACCOUNT['pe_namepass']);
            $preferences->unset_var('delete_active');
        }
    }
    $preferences->set_var ('navbar', $navbar->generate());

    $preferences->set_var ('no_javascript_warning',$LANG04[150]);

    $preferences->set_var ('cssid1', 1);
    $preferences->set_var ('cssid2', 2);

    $preferences->set_var ('preview', userprofile($_USER['uid']));
    $preferences->set_var ('prefs', editpreferences());

    // some trickery to ensure alternating colors with the available options ...
    if ($_CONF['allow_username_change'] == 1) {
        $first  = 1;
        $second = 2;
    } else {
        $first  = 2;
        $second = 1;
    }
    $preferences->set_var ('cssid1u', $first);
    $preferences->set_var ('cssid2u', $second);

    if ($_CONF['allow_user_photo'] == 1) {
        $tmp = $first;
        $first = $second;
        $second = $tmp;
    }
    $preferences->set_var ('cssid1p', $first);
    $preferences->set_var ('cssid2p', $second);

    $preferences->set_var ('lang_fullname', $LANG04[3]);
    $preferences->set_var ('lang_fullname_text', $LANG04[34]);
    $preferences->set_var ('lang_username', $LANG04[2]);
    $preferences->set_var ('lang_username_text', $LANG04[87]);
    $preferences->set_var ('lang_password_help_title', $LANG04[146]);
    $preferences->set_var ('lang_password_help', $LANG04[147]);
    $preferences->set_var ('lang_password', $LANG04[4]);
    $preferences->set_var ('lang_password_text', $LANG04[35]);
    $preferences->set_var ('lang_password_conf', $LANG04[108]);
    $preferences->set_var ('lang_password_text_conf', $LANG04[109]);
    $preferences->set_var ('lang_old_password', $LANG04[110]);
    $preferences->set_var ('lang_old_password_text', $LANG04[111]);
    $preferences->set_var ('lang_cooktime', $LANG04[68]);
    $preferences->set_var ('lang_cooktime_text', $LANG04[69]);
    $preferences->set_var ('lang_email', $LANG04[5]);
    $preferences->set_var ('lang_email_text', $LANG04[33]);
    $preferences->set_var ('lang_email_conf', $LANG04[124]);
    $preferences->set_var ('lang_email_conf_text', $LANG04[126]);
    $preferences->set_var ('lang_userinfo_help_title', $LANG04[148]);
    $preferences->set_var ('lang_userinfo_help', $LANG04[149]);
    $preferences->set_var ('lang_homepage', $LANG04[6]);
    $preferences->set_var ('lang_homepage_text', $LANG04[36]);
    $preferences->set_var ('lang_location', $LANG04[106]);
    $preferences->set_var ('lang_location_text', $LANG04[107]);
    $preferences->set_var ('lang_signature', $LANG04[32]);
    $preferences->set_var ('lang_signature_text', $LANG04[37]);
    $preferences->set_var ('lang_userphoto', $LANG04[77]);
    $preferences->set_var ('lang_userphoto_text', $LANG04[78]);
    $preferences->set_var ('lang_about', $LANG04[7]);
    $preferences->set_var ('lang_about_text', $LANG04[38]);
    $preferences->set_var ('lang_pgpkey', $LANG04[8]);
    $preferences->set_var ('lang_pgpkey_text', $LANG04[39]);
    $preferences->set_var ('lang_submit', $LANG04[9]);
    $preferences->set_var ('lang_cancel',$LANG_ADMIN['cancel']);
    $preferences->set_var ('lang_preview_title', $LANG04[145]);
    $preferences->set_var ('lang_enter_current_password', $LANG04[127]);
    $preferences->set_var ('lang_name_legend', $LANG04[128]);
    $preferences->set_var ('lang_password_email_legend', $LANG04[129]);
    $preferences->set_var ('lang_personal_info_legend', $LANG04[130]);
    $preferences->set_var ('lang_resynch', $LANG04[178]);

    $display_name = COM_getDisplayName ($_USER['uid']);

    $preferences->set_var ('profile_headline',
                           $LANG04[1] . ' ' . $display_name);

    if ($_CONF['allow_user_photo'] == 1) {
        $preferences->set_var ('enctype', 'enctype="multipart/form-data"');
    } else {
        $preferences->set_var ('enctype', '');
    }
    $preferences->set_var ('fullname_value', htmlspecialchars ($A['fullname']));
    $preferences->set_var ('new_username_value',
                           htmlspecialchars ($_USER['username']));

    if ( $A['account_type'] & LOCAL_USER ) {
        $preferences->set_var ('password_value', '');
        $preferences->set_var ('password_help',SEC_showPasswordHelp());
        $preferences->parse ('current_password_option', 'current_password', true);
        $preferences->parse ('password_option', 'password', true);
        $preferences->set_var ('resynch_option', '');
    } else {
        $preferences->set_var ('current_password_option', '');
        $preferences->set_var ('password_option', '');
     }

     if ( $A['account_type'] & REMOTE_USER ) {
        if ($_CONF['user_login_method']['oauth'] && (strpos($_USER['remoteservice'], 'oauth.') === 0)) { // OAuth only supports re-synch at the moment

            $pos = strpos($_USER['remoteservice'],'.');
            $remoteService = ucfirst(substr($_USER['remoteservice'],$pos+1));

            $preferences->set_var('remote_account_type',$remoteService);

            if ( $A['account_type'] & LOCAL_USER ) {
                $preferences->set_var ('resynch_checked', '');
                $preferences->set_var('localuid',$_USER['uid']);
                $preferences->set_var('unlink_accounts',true);
            } else {
                $preferences->unset_var('unlink_accounts');
                $preferences->set_var ('resynch_checked', '');

                $localAccountData = $db->conn->fetchAssoc(
                    "SELECT * FROM `{$_TABLES['users']}`
                     WHERE email=? AND account_type = ?",
                    array($A['email'],LOCAL_USER),
                    array(Database::STRING,Database::INTEGER)
                );
                if ($localAccountData !== false && $localAccountData !== null) {
                    $preferences->set_var('merge_account',true);
                    $preferences->set_var('localuid',$localAccountData['uid']);
                    $preferences->set_var('local_username',$localAccountData['username']);
                    $preferences->set_var('remoteuid',$_USER['uid']);
                }
            }
            $preferences->set_var('lang_remote_service',$LANG04[165]);
            $preferences->parse ('resynch_option', 'resynch', true);
        } else {
            $preferences->set_var ('resynch_option', '');
        }
    }

    $preferences->set_var('plugin_namepass_name',PLG_profileEdit($_USER['uid'],'namepass','name'));

    if ($_CONF['allow_username_change'] == 1) {
        $preferences->parse ('username_option', 'username', true);
    } else {
        $preferences->set_var ('username_option', '');
    }

    $selection = '<select id="cooktime" name="cooktime">' . LB;
    $selection .= COM_optionList ($_TABLES['cookiecodes'], 'cc_value,cc_descr',
                                  $A['cookietimeout'], 0);
    $selection .= '</select>';
    $preferences->set_var ('cooktime_selector', $selection);

    $preferences->set_var ('email_value', htmlspecialchars ($A['email']));
    $preferences->set_var ('homepage_value',
                           htmlspecialchars (COM_killJS ($A['homepage'])));
    $preferences->set_var ('location_value',
                           htmlspecialchars (strip_tags ($A['location'])));
    $preferences->set_var ('signature_value', htmlspecialchars ($A['sig']));

    if ($_CONF['allow_user_photo'] == 1) {
        $photo = USER_getPhoto ($_USER['uid'], $A['photo'], $A['email'], -1);
        if (empty ($photo)) {
            $preferences->set_var ('display_photo', '');
        } else {
            if (empty ($A['photo'])) { // external avatar
                $photo = '<br />' . $photo;
            } else { // uploaded photo - add delete option
                $photo = '<br />' . $photo . '<br />' . $LANG04[79]
                       . '&nbsp;<input type="checkbox" name="delete_photo" />'
                       . LB;
            }
            $preferences->set_var ('display_photo', $photo);
        }
        $preferences->parse ('userphoto_option', 'photo', true);
    } else {
        $preferences->set_var ('userphoto_option', '');
    }

    $preferences->set_var('plugin_namepass_pwdemail',PLG_profileEdit($_USER['uid'],'namepass','pwdemail'));
    $preferences->set_var('plugin_namepass',PLG_profileEdit($_USER['uid'],'namepass'));

    if ( isset($_CONF['enable_twofactor']) && $_CONF['enable_twofactor'] == 1 && function_exists('hash_hmac')) {
        $outputHandler = outputHandler::getInstance();
        $outputHandler->addLinkScript($_CONF['site_url'].'/javascript/twofactor.js');
        $tfaMaster = new \Template ($_CONF['path_layout'] . 'preferences');
        $tfaMaster->set_file('tfa_panel','twofactor.thtml');
        if ( isset($_USER['tfa_enabled']) && $_USER['tfa_enabled'] == 1 ) {
            $tfaTemplate = new \Template ($_CONF['path_layout'] . 'preferences');
            $tfaTemplate->set_file('tfa_panel','tfa-enrolled.thtml');
            $tfaTemplate->set_var(array(
                'lang_two_factor'   => $LANG_TFA['two_factor'],
                'lang_enrolled'     => $LANG_TFA['enrolled'],
                'lang_regenerate_backup' => $LANG_TFA['regenerate_backup'],
                'lang_regenerate_button' => $LANG_TFA['regenerate_button'],
                'lang_download_backup'  => $LANG_TFA['download_backup'],
                'sectoken_name'    => CSRF_TOKEN,
                'sectoken_value'   => SEC_createToken(),
                'lang_disable_tfa_help' => $LANG_TFA['disable_tfa_help'],
                'lang_disable_tfa_button' => $LANG_TFA['disable_tfa_button'],
            ));
            $tfaTemplate->parse('tfapanel','tfa_panel');
            $tfaPanelContent = $tfaTemplate->finish($tfaTemplate->get_var('tfapanel'));
        } else {
            $tfaTemplate = new \Template ($_CONF['path_layout'] . 'preferences');
            $tfaTemplate->set_file('tfa_panel','tfa-notenrolled.thtml');
            $tfaTemplate->set_var(array(
                'lang_two_factor'   => $LANG_TFA['two_factor'],
                'lang_not_enrolled' => $LANG_TFA['not_enrolled'],
                'lang_enroll_button'=> $LANG_TFA['enroll_button'],
            ));
            $tfaTemplate->parse('tfapanel','tfa_panel');
            $tfaPanelContent = $tfaTemplate->finish($tfaTemplate->get_var('tfapanel'));
        }
        $tfaMaster->set_var(array(
            'lang_disable_warning'  => $LANG_TFA['disable_warning'],
            'lang_ajax_error'       => $LANG_TFA['ajax_error'],
        ));
        $tfaMaster->set_var('content',$tfaPanelContent);
        $tfaPanel = $tfaMaster->finish($tfaMaster->parse('output','tfa_panel'));

        $preferences->set_var('twofactor', $tfaPanel);
    }

    $preferences->parse ('twofactor', 'twofactor', false);

    $reqid = substr (md5 (uniqid (rand (), 1)), 1, 16);

    $db->conn->update(
            $_TABLES['users'],
            array('pwrequestid' => $reqid),
            array('uid' => $_USER['uid']),
            array(Database::STRING,Database::INTEGER)
    );

    $preferences->set_var ('about_value', htmlspecialchars ($A['about']));
    $preferences->set_var ('pgpkey_value', htmlspecialchars ($A['pgpkey']));
    $preferences->set_var ('uid_value', $reqid);
    $preferences->set_var ('username_value',
                           htmlspecialchars ($_USER['username']));

    if ($_CONF['allow_account_delete'] == 1) {
        $preferences->set_var ('lang_deleteaccount', $LANG04[156]);
        $preferences->set_var ('delete_text', $LANG04[95]);
        $preferences->set_var ('lang_button_delete', $LANG04[96]);
        $preferences->set_var ('delete_mode', 'confirmdelete');
        $preferences->set_var ('account_id', $reqid);
        if (isset ($LANG04[157])) {
            $preferences->set_var ('lang_deleteoption', $LANG04[157]);
        } else {
            $preferences->set_var ('lang_deleteoption', $LANG04[156]);
        }
        if (SEC_isLocalUser($_USER['uid'])) {
            $preferences->set_var('local-acct',true);
        }

        $preferences->parse ('delete_account_option', 'deleteaccount', false);
    } else {
        $preferences->set_var ('delete_account_option', '');
    }

    // Call custom account form and edit function if enabled and exists
    if ($_CONF['custom_registration'] AND (function_exists('CUSTOM_userEdit'))) {
        $preferences->set_var ('customfields', CUSTOM_userEdit($_USER['uid']) );
    }

    $preferences->set_var('plugin_userinfo_personalinfo',PLG_profileEdit($_USER['uid'],'userinfo','personalinfo'));
    $preferences->set_var('plugin_userinfo',PLG_profileEdit($_USER['uid'],'userinfo'));

    $preferences->set_var('plugin_panel',PLG_profileEdit($_USER['uid']));

    PLG_profileVariablesEdit ($_USER['uid'], $preferences);

    $follow_me = Social::followMeProfile( $_USER['uid'] );
    if ( is_array($follow_me) && count($follow_me) > 0 ) {
        $preferences->set_block('profile','social_links','sl');
        $preferences->set_var('social_followme_enabled',true);
        foreach ( $follow_me AS $service ) {
            $preferences->set_var('service_display_name', $service['service_display_name']);
            $preferences->set_var('service',$service['service']);
            $preferences->set_var('service-postfix',$service['service_postfix']);
            $preferences->set_var('service_username',$service['service_username']);
            $preferences->set_var('service_url',$service['service_url']);
            $preferences->parse('sl','social_links',true);
        }
    } else {
        $preferences->unset_var('social_followme_enabled');
    }

    $retval = $preferences->finish ($preferences->parse ('output', 'profile'));
    $retval .= PLG_profileBlocksEdit ($_USER['uid']);

    return $retval;
}

/**
* Ask user for confirmation to delete his/her account.
*
* @param    string   form_reqid   request id
* @return   string   confirmation form
*
*/
function confirmAccountDelete ($form_reqid)
{
    global $_CONF, $_TABLES, $_USER, $LANG04, $MESSAGE;

    $db = Database::getInstance();

    $ct = $db->getCount(
                $_TABLES['users'],
                array ('pwrequestid', 'uid'),
                array ($form_reqid, $_USER['uid']),
                array(Database::STRING,Database::INTEGER)
          );

    if ($ct != 1) {
        // not found - abort
        return COM_refresh ($_CONF['site_url'] . '/index.php');
    }

    if ( SEC_isLocalUser($_USER['uid']) ) {
        // to change the password, email address, or cookie timeout,
        // we need the user's current password
        $current_password = DB_getItem($_TABLES['users'],'passwd',"uid=".(int)$_USER['uid']);
        if (empty($_POST['current_password']) || !SEC_check_hash(trim($_POST['current_password']),$current_password)) {
             COM_setMsg( $MESSAGE[84], 'error',true );
             return COM_refresh($_CONF['site_url'].'/usersettings.php?mode=delete');
        }
    }
    $token = SEC_createToken();

    $reqid = substr (md5 (uniqid (rand (), 1)), 1, 16);

    $db->conn->update(
            $_TABLES['users'],
            array('pwrequestid' => $reqid),
            array('uid' => $_USER['uid']),
            array(Database::STRING,Database::INTEGER)
    );

//    DB_change ($_TABLES['users'], 'pwrequestid', "$reqid",'uid', (int)$_USER['uid']);

    $retval = '';

    $retval .= COM_siteHeader ('menu', $LANG04[97]);
    $retval .= COM_startBlock ($LANG04[97], '',
                               COM_getBlockTemplate ('_msg_block', 'header'));
    $retval .= '<p>' . $LANG04[98] . '</p>' . LB;
    $retval .= '<form class="uk-form" action="' . $_CONF['site_url']
            . '/usersettings.php" method="post"><div>' . LB;
    $retval .= '<p align="center"><button type="submit" class="uk-button uk-button-danger" name="btnsubmit" value="'.$LANG04[96].'">'.$LANG04[96].'</button></p>'.LB;
    $retval .= '<input type="hidden" name="mode" value="deleteconfirmed" />' . LB;
    $retval .= '<input type="hidden" name="account_id" value="' . $reqid
            . '" />' . LB;
    $retval .= '</div></form>' . LB;
    $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $retval .= COM_siteFooter ();

    return $retval;
}

/**
* Delete an account
*
* @param    string   form_reqid   request id
* @return   string   redirection to main page (+ success msg)
*
*/
function deleteUserAccount ($form_reqid)
{
    global $_CONF, $_TABLES, $_USER, $MESSAGE;

    $db = Database::getInstance();

    $ct = $db->getCount(
                $_TABLES['users'],
                array ('pwrequestid', 'uid'),
                array ($form_reqid, $_USER['uid']),
                array(Database::STRING,Database::INTEGER)
          );

    if ($ct != 1) {
        // not found - abort
        return COM_refresh ($_CONF['site_url'] . '/index.php');
    }

    if (!USER_deleteAccount ($_USER['uid'])) {
        return COM_refresh ($_CONF['site_url'] . '/index.php');
    }
    COM_setMsg( $MESSAGE[57], 'error',true );
    return COM_refresh ($_CONF['site_url'] . '/index.php');
}

/**
* Displays user preferences
*
*/
function editpreferences()
{
    global $_TABLES, $_CONF, $_SYSTEM, $LANG04, $_USER, $_GROUPS,
           $LANG_confignames, $LANG_configselects, $LANG_configSelect;

    $db = Database::getInstance();

    $sql = "SELECT *
            FROM `{$_TABLES['userprefs']}` AS up,`{$_TABLES['userindex']}` AS ui
            WHERE ui.uid = ? AND up.uid = ?";

    $A = $db->conn->fetchAssoc(
            $sql,
            array($_USER['uid'],$_USER['uid']),
            array(Database::INTEGER,Database::INTEGER)
    );
//    $result = DB_query("SELECT noicons,willing,dfid,tzid,noboxes,maxstories,tids,aids,boxes,emailfromadmin,emailfromuser,showonline,search_result_format FROM {$_TABLES['userprefs']},{$_TABLES['userindex']} WHERE {$_TABLES['userindex']}.uid = {$_USER['uid']} AND {$_TABLES['userprefs']}.uid = {$_USER['uid']}");

//    $A = DB_fetchArray($result);

    if ( $A['tzid'] == '' ) {
        $A['tzid'] = $_CONF['timezone'];
    }

    // 'maxstories' may be 0, in which case it will pick up the default
    // setting for the current topic or $_CONF['limitnews'] (see index.php)
    if (empty ($A['maxstories'])) {
        $A['maxstories'] = 0;
    } else if ($A['maxstories'] > 0) {
        if ($A['maxstories'] < $_CONF['minnews']) {
            $A['maxstories'] = $_CONF['minnews'];
        }
    }

    $preferences = new Template ($_CONF['path_layout'] . 'preferences');
    $preferences->set_file (array ('prefs' => 'displayprefs.thtml',
                                   'display' => 'displayblock.thtml',
                                   'exclude' => 'excludeblock.thtml',
                                   'digest' => 'digestblock.thtml',
                                   'boxes' => 'boxesblock.thtml',
                                   'comment' => 'commentblock.thtml',
                                   'language' => 'language.thtml',
                                   'theme' => 'theme.thtml',
                                   'privacy' => 'privacyblock.thtml',
                                   'subscriptions' => 'subscriptionblock.thtml'
                                  ));

    $preferences->set_var ('user_name', $_USER['username']);

    $preferences->set_var ('lang_language', $LANG04[73]);
    $preferences->set_var ('lang_theme', $LANG04[72]);
    $preferences->set_var ('lang_theme_text', $LANG04[74]);
    $preferences->set_var ('lang_misc_title', $LANG04[138]);
    $preferences->set_var ('lang_misc_help_title', $LANG04[139]);
    $preferences->set_var ('lang_misc_help', $LANG04[140]);
    $preferences->set_var ('lang_noicons', $LANG04[40]);
    $preferences->set_var ('lang_noicons_text', $LANG04[49]);
    $preferences->set_var ('lang_noboxes', $LANG04[44]);
    $preferences->set_var ('lang_noboxes_text', $LANG04[51]);
    $preferences->set_var ('lang_maxstories', $LANG04[43]);
    if (strpos ($LANG04[52], '%d') === false) {
        $maxtext = $LANG04[52] . ' ' . $_CONF['limitnews'];
    } else {
        $maxtext = sprintf ($LANG04[52], $_CONF['limitnews']);
    }
    $preferences->set_var ('lang_maxstories_text', $maxtext);
    $preferences->set_var ('lang_dateformat', $LANG04[42]);
    $preferences->set_var ('lang_excluded_items_title', $LANG04[137]);
    $preferences->set_var ('lang_excluded_items', $LANG04[54]);
    $preferences->set_var ('lang_exclude_title', $LANG04[136]);
    $preferences->set_var ('lang_topics', $LANG04[48]);
    $preferences->set_var ('lang_emailedtopics', $LANG04[76]);
    $preferences->set_var ('lang_digest_top_header', $LANG04[131]);
    $preferences->set_var ('lang_digest_help_header', $LANG04[132]);
    $preferences->set_var ('lang_boxes_title', $LANG04[144]);
    $preferences->set_var ('lang_boxes_help_title', $LANG04[143]);
    $preferences->set_var ('lang_boxes', $LANG04[55]);
    $preferences->set_var ('lang_displaymode', $LANG04[57]);
    $preferences->set_var ('lang_displaymode_text', $LANG04[60]);
    $preferences->set_var ('lang_sortorder', $LANG04[58]);
    $preferences->set_var ('lang_sortorder_text', $LANG04[61]);
    $preferences->set_var ('lang_comment_title', $LANG04[133]);
    $preferences->set_var ('lang_comment_help_title', $LANG04[134]);
    $preferences->set_var ('lang_comment_help', $LANG04[135]);
    $preferences->set_var ('lang_commentlimit', $LANG04[59]);
    $preferences->set_var ('lang_commentlimit_text', $LANG04[62]);
    $preferences->set_var ('lang_privacy_title', $LANG04[141]);
    $preferences->set_var ('lang_privacy_help_title', $LANG04[141]);
    $preferences->set_var ('lang_privacy_help', $LANG04[142]);
    $preferences->set_var ('lang_emailfromadmin', $LANG04[100]);
    $preferences->set_var ('lang_emailfromadmin_text', $LANG04[101]);
    $preferences->set_var ('lang_emailfromuser', $LANG04[102]);
    $preferences->set_var ('lang_emailfromuser_text', $LANG04[103]);
    $preferences->set_var ('lang_showonline', $LANG04[104]);
    $preferences->set_var ('lang_showonline_text', $LANG04[105]);
    $preferences->set_var ('lang_submit', $LANG04[9]);

    $display_name = COM_getDisplayName ($_USER['uid']);

    $preferences->set_var ('lang_authors_exclude', $LANG04[46]);
    $preferences->set_var ('lang_boxes_exclude', $LANG04[47]);

    $preferences->set_var ('start_block_display',
            COM_startBlock ($LANG04[45] . ' ' . $display_name));
    $preferences->set_var ('start_block_digest',
            COM_startBlock ($LANG04[75] . ' ' . $display_name));
    $preferences->set_var ('start_block_comment',
            COM_startBlock ($LANG04[64] . ' ' . $display_name));
    $preferences->set_var ('start_block_privacy',
            COM_startBlock ($LANG04[99] . ' ' . $display_name));
    $preferences->set_var ('end_block', COM_endBlock ());

    $preferences->set_var ('display_headline',
                           $LANG04[45] . ' ' . $display_name);
    $preferences->set_var ('exclude_headline',
                           $LANG04[46] . ' ' . $display_name);
    $preferences->set_var ('digest_headline',
                           $LANG04[75] . ' ' . $display_name);
    $preferences->set_var ('boxes_headline',
                           $LANG04[47] . ' ' . $display_name);
    $preferences->set_var ('comment_headline',
                           $LANG04[64] . ' ' . $display_name);
    $preferences->set_var ('privacy_headline',
                           $LANG04[99] . ' ' . $display_name);

    // display preferences block
    if ($_CONF['allow_user_language'] == 1) {

        if (empty ($_USER['language'])) {
            $userlang = $_CONF['language'];
        } else {
            $userlang = $_USER['language'];
        }

        // Get available languages
        $language = MBYTE_languageList ($_CONF['default_charset']);

        $has_valid_language = count (array_keys ($language, $userlang));
        if ($has_valid_language == 0) {
            // The user's preferred language is no longer available.
            // We have a problem now, since we've overwritten $_CONF['language']
            // with the user's preferred language ($_USER['language']) and
            // therefore don't know what the system's default language is.
            // So we'll try to find a similar language. If that doesn't help,
            // the dropdown will default to the first language in the list ...
            $tmp = explode ('_', $userlang);
            $similarLang = $tmp[0];
        }

        $selection = '<select id="language" name="language">' . LB;

        foreach ($language as $langFile => $langName) {
            $selection .= '<option value="' . $langFile . '"';
            if (($langFile == $userlang) || (($has_valid_language == 0) &&
                    (strpos ($langFile, $similarLang) === 0))) {
                $selection .= ' selected="selected"';
                $has_valid_language = 1;
            } else if ($userlang == $langFile) {
                $selection .= ' selected="selected"';
            }

            $selection .= '>' . $langName . '</option>' . LB;
        }
        $selection .= '</select>';
        $preferences->set_var ('language_selector', $selection);
        $preferences->parse ('language_selection', 'language', true);
    } else {
        $preferences->set_var ('language_selection', '');
    }

    if ($_CONF['allow_user_themes'] == 1) {
        $selection = '<select id="theme" name="theme">' . LB;

        if (empty ($_USER['theme'])) {
            $usertheme = $_CONF['theme'];
        } else {
            $usertheme = $_USER['theme'];
        }

        $themeFiles = COM_getThemes ();
        usort ($themeFiles,
               function ($a,$b) { return strcasecmp($a,$b); } );

        foreach ($themeFiles as $theme) {
            $selection .= '<option value="' . $theme . '"';
            if ($usertheme == $theme) {
                $selection .= ' selected="selected"';
            }
            $words = explode ('_', $theme);
            $bwords = array ();
            foreach ($words as $th) {
                if ((strtolower ($th[0]) == $th[0]) &&
                    (strtolower ($th[1]) == $th[1])) {
                    $bwords[] = strtoupper ($th[0]) . substr ($th, 1);
                } else {
                    $bwords[] = $th;
                }
            }
            $selection .= '>' . implode (' ', $bwords) . '</option>' . LB;
        }
        $selection .= '</select>';
        $preferences->set_var ('theme_selector', $selection);
        $preferences->parse ('theme_selection', 'theme', true);
    } else {
        $preferences->set_var ('theme_selection', '');
    }

    // Timezone
    $selection = Date::getTimeZoneDropDown($A['tzid'],array('id' => 'tzid', 'name' => 'tzid'));

    $preferences->set_var('timezone_selector', $selection);
    $preferences->set_var('lang_timezone', $LANG04[158]);

    if ($A['noicons'] == '1') {
        $preferences->set_var ('noicons_checked', 'checked="checked"');
    } else {
        $preferences->set_var ('noicons_checked', '');
    }

    if ($A['noboxes'] == 1) {
        $preferences->set_var ('noboxes_checked', 'checked="checked"');
    } else {
        $preferences->set_var ('noboxes_checked', '');
    }

    $preferences->set_var ('maxstories_value', $A['maxstories']);
    $selection = '<select id="dfid" name="dfid">' . LB
               . COM_optionList ($_TABLES['dateformats'], 'dfid,description',
                                 $A['dfid']) . '</select>';
    $preferences->set_var ('dateformat_selector', $selection);
    $preferences->set_var('plugin_layout_display',PLG_profileEdit($_USER['uid'],'layout','display'));

    if (isset($LANG_configSelect['Core'])) {
        $cfgSelect = $LANG_configSelect['Core'][18];

    } else {
        $cfgSelect = array_flip($LANG_configselects['Core'][18]);
    }

    $search_result_select  = '<select name="search_result_format" id="search_result_format">'.LB;
    foreach ($cfgSelect AS $type => $name ) {
        $search_result_select .= '<option value="'. $type . '"' . ($A['search_result_format'] == $type ? 'selected="selected"' : '') . '>'.$name.'</option>'.LB;
    }
    $search_result_select .= '</select>';

    $preferences->set_var('search_result_select',$search_result_select);
    $preferences->set_var('lang_search_format',$LANG_confignames['Core']['search_show_type']);

    $preferences->parse ('display_block', 'display', true);

    // privacy options block
    if ($A['emailfromadmin'] == 1) {
        $preferences->set_var ('emailfromadmin_checked', 'checked="checked"');
    } else {
        $preferences->set_var ('emailfromadmin_checked', '');
    }
    if ($A['emailfromuser'] == 1) {
        $preferences->set_var ('emailfromuser_checked', 'checked="checked"');
    } else {
        $preferences->set_var ('emailfromuser_checked', '');
    }
    if ($A['showonline'] == 1) {
        $preferences->set_var ('showonline_checked', 'checked="checked"');
    } else {
        $preferences->set_var ('showonline_checked', '');
    }
    PLG_profileVariablesEdit ($_USER['uid'], $preferences);

    $preferences->set_var('plugin_layout',PLG_profileEdit($_USER['uid'],'layout'));


    $preferences->set_var('plugin_privacy_privacy',PLG_profileEdit($_USER['uid'],'privacy','privacy'));
    $preferences->set_var('plugin_privacy',PLG_profileEdit($_USER['uid'],'privacy'));

    $preferences->parse ('privacy_block', 'privacy', true);

    // subscription block
    $csscounter = 1;
    $preferences->set_block('subscriptions', 'subrows', 'srow');

    $stmt = $db->conn->executeQuery(
                "SELECT * FROM `{$_TABLES['subscriptions']}`
                WHERE uid=? ORDER BY type,category ASC",
                array($_USER['uid']),
                array(Database::INTEGER)
    );

    while ($S = $stmt->fetch(Database::ASSOCIATIVE)) {
        $cssstyle = ($csscounter % 2) + 1;
        $preferences->set_var('subid',$S['sub_id']);
        $preferences->set_var('sub_type',$S['type']);
        $preferences->set_var('sub_category',$S['category_desc']);
        $preferences->set_var('sub_description',$S['id_desc']);
        $preferences->set_var('csscounter',$cssstyle);
        if ( $S['id'] < 0 ) {
            $preferences->set_var('excludeclass','subexclude');
        } else {
            $preferences->set_var('excludeclass','');
        }
        $preferences->parse('srow', 'subrows',true);
        $csscounter++;
    }
    $preferences->parse ('subscriptions_block','subscriptions',true);

    // excluded items block
    $permissions = '';
    if ( $_CONF['hide_exclude_content'] != 1 ) {
        $permissions = $db->getPermSQL ('');
        $preferences->set_var ('exclude_topic_checklist',
                COM_checkList($_TABLES['topics'], 'tid,topic', $permissions, $A['tids'], 'topics'));

        if (($_CONF['contributedbyline'] == 1) && ($_CONF['hide_author_exclusion'] == 0)) {
            $preferences->set_var ('lang_authors', $LANG04[56]);

$sql = "SELECT DISTINCT story.uid, users.username,users.fullname
        FROM `{$_TABLES['stories']}` story, `{$_TABLES['users']}` users
        WHERE story.uid = users.uid";

            if ($_CONF['show_fullname'] == 1) {
                $sql .= ' ORDER BY users.fullname';
            } else {
                $sql .= ' ORDER BY users.username';
            }

$stmt = $db->conn->query($sql);

//            $query = DB_query ($sql);
//            $nrows = DB_numRows ($query );
            $authors = explode (' ', $A['aids']);

            $selauthors = '';
            while ($B = $stmt->fetch(Database::ASSOCIATIVE)) {
//            for( $i = 0; $i < $nrows; $i++ ) {
//                $B = DB_fetchArray ($query);
                $selauthors .= '<option value="' . $B['uid'] . '"';
                if (in_array (sprintf ('%d', $B['uid']), $authors)) {
                   $selauthors .= ' selected';
                }
                $selauthors .= '>' . COM_getDisplayName ($B['uid'], $B['username'],$B['fullname'])
                            . '</option>' . LB;
            }

            if ($db->getCount($_TABLES['topics']) > 10) {
                $Selboxsize = intval (DB_count ($_TABLES['topics']) * 1.5);
            } else {
                $Selboxsize = 15;
            }
            $preferences->set_var ('exclude_author_checklist', '<select name="selauthors[]" multiple="multiple" size="'. $Selboxsize. '">' . $selauthors . '</select>');
        } else {
            $preferences->set_var ('lang_authors', '');
            $preferences->set_var ('exclude_author_checklist', '');
        }
        $preferences->set_var('plugin_content_exclude',PLG_profileEdit($_USER['uid'],'content','exclude'));
        $preferences->parse ('exclude_block', 'exclude', true);
    } else {
        $preferences->set_var ('exclude_block', '');
    }

    // daily digest block
    if ($_CONF['emailstories'] == 1) {
        $user_etids = $db->getItem ($_TABLES['userindex'], 'etids',array('uid' => $_USER['uid']),array(Database::INTEGER));
        if (empty ($user_etids)) { // an empty string now means "all topics"
            $user_etids = USER_buildTopicList ();
        } elseif ($user_etids == '-') { // this means "no topics"
            $user_etids = '';
        }
        $tmp = COM_checkList($_TABLES['topics'], 'tid,topic', $permissions,
                             $user_etids, 'dgtopics');
        $preferences->set_var('email_topic_checklist',
                str_replace($_TABLES['topics'], 'etids', $tmp));
        $preferences->set_var('plugin_content_digest',PLG_profileEdit($_USER['uid'],'content','digest'));
        $preferences->parse('digest_block', 'digest', true);
    } else {
        $preferences->set_var('digest_block', '');
    }


    if ( $_CONF['hide_exclude_content'] != 1 ) {
        // boxes block
        $selectedblocks = '';
        if (strlen($A['boxes']) > 0) {
            $blockresult = DB_query("SELECT bid FROM {$_TABLES['blocks']} WHERE bid NOT IN (" . str_replace(' ',',',trim($A['boxes'])) . ")");
            for ($x = 1; $x <= DB_numRows($blockresult); $x++) {
                $row = DB_fetchArray($blockresult);
                $selectedblocks .= $row['bid'];
                if ($x <> DB_numRows($blockresult)) {
                    $selectedblocks .= ' ';
                }
            }
        }
        $whereblock = '';
        if (!empty ($permissions)) {
            $whereblock .= $permissions . ' AND ';
        }
        $whereblock .= "((type != 'layout' AND type != 'gldefault' AND is_enabled = 1) OR "
                     . "(type = 'gldefault' AND is_enabled = 1 AND name IN ('whats_new_block','older_stories'))) "
                     . "ORDER BY onleft desc,blockorder,title";
        $preferences->set_var ('boxes_checklist', COM_checkList ($_TABLES['blocks'],
                'bid,title,type', $whereblock, $selectedblocks,'blocks'));
        $preferences->set_var('plugin_content_boxes',PLG_profileEdit($_USER['uid'],'content','boxes'));
        $preferences->parse ('boxes_block', 'boxes', true);
    } else {
        $preferences->set_var ('boxes_block', '');
    }

    $preferences->set_var('plugin_content',PLG_profileEdit($_USER['uid'],'content'));

    // comment preferences block
    $result = DB_query("SELECT commentmode,commentorder,commentlimit FROM {$_TABLES['usercomment']} WHERE uid = {$_USER['uid']}");
    $A = DB_fetchArray ($result);

    if (empty ($A['commentmode'])) {
        $A['commentmode'] = $_CONF['comment_mode'];
    }
    if (empty ($A['commentorder'])) $A['commentorder'] = 0;
    if (empty ($A['commentlimit'])) $A['commentlimit'] = 100;

    $selection = '<select id="commentmode" name="commentmode">';
    $selection .= COM_optionList ($_TABLES['commentmodes'], 'mode,name',
                                  $A['commentmode']);
    $selection .= '</select>';
    $preferences->set_var ('displaymode_selector', $selection);

    $selection = '<select id="commentorder" name="commentorder">';
    $selection .= COM_optionList ($_TABLES['sortcodes'], 'code,name',
                                  $A['commentorder']);
    $selection .= '</select>';
    $preferences->set_var ('sortorder_selector', $selection);
    $preferences->set_var ('commentlimit_value', $A['commentlimit']);
    $preferences->set_var('plugin_layout_comment',PLG_profileEdit($_USER['uid'],'layout','comment'));
    $preferences->parse ('comment_block', 'comment', true);

    return $preferences->finish ($preferences->parse ('output', 'prefs'));
}

/**
* Check if an email address already exists in the database
*
* @param   email   string   email address to check
* @param   uid     int      user id of current user
* @return          bool     true = exists, false = does not exist
* @note    Allows remote accounts to have duplicate email addresses
*/
function emailAddressExists ($email, $uid)
{
    global $_TABLES;

    $old_email = DB_getItem($_TABLES['users'], 'email', "uid = ".(int) $uid);
    if ($email == $old_email) {
        // email address didn't change so don't care
        return false;
    }

    $email = DB_escapeString($email);
    $result = DB_query("SELECT uid FROM {$_TABLES['users']} WHERE email = '$email' AND uid <> ".(int) $uid." AND (remoteservice IS NULL OR remoteservice = '')");
    if (DB_numRows($result) > 0) {
        // email address is already in use for another non-remote account
        return true;
    }

    return false;
}

/**
* Upload new photo, delete old photo
*
* @param    string  $delete_photo   'on': delete old photo
* @return   string                  filename of new photo (empty = no new photo)
*
*/
function handlePhotoUpload ($delete_photo = '')
{
    global $_CONF, $_TABLES, $_USER, $LANG24;

    $upload = new upload();
    if (!empty ($_CONF['image_lib'])) {

        $upload->setAutomaticResize (true);
        if (isset ($_CONF['debug_image_upload']) &&
                $_CONF['debug_image_upload']) {
            $upload->setLogFile ($_CONF['path'] . 'logs/error.log');
            $upload->setDebug (true);
        }
    }
    $upload->setAllowedMimeTypes (array ('image/gif'   => '.gif',
                                         'image/jpeg'  => '.jpg,.jpeg',
                                         'image/pjpeg' => '.jpg,.jpeg',
                                         'image/x-png' => '.png',
                                         'image/png'   => '.png'
                                 )      );
    if (!$upload->setPath ($_CONF['path_images'] . 'userphotos')) {
        $display = COM_siteHeader ('menu', $LANG24[30]);
        $display .= COM_showMessageText($upload->printErrors (false),$LANG24[30],true,'error');
        $display .= COM_siteFooter ();
        echo $display;
        exit; // don't return
    }

    $filename = '';
    if (!empty ($delete_photo) && ($delete_photo == 'on')) {
        $delete_photo = true;
    } else {
        $delete_photo = false;
    }

    $curphoto = DB_getItem ($_TABLES['users'], 'photo',
                            "uid = {$_USER['uid']}");
    if (empty ($curphoto)) {
        $delete_photo = false;
    }

    // see if user wants to upload a (new) photo
    $newphoto = $_FILES['photo'];
    if (!empty ($newphoto['name'])) {
        $pos = strrpos ($newphoto['name'], '.') + 1;
        $fextension = substr ($newphoto['name'], $pos);
        $filename = $_USER['uid'] . '_'.mt_rand(1,1000000).'.'.$fextension;
        if (!empty ($curphoto) && ($filename != $curphoto)) {
            $delete_photo = true;
        } else {
            $delete_photo = false;
        }
    }

    // delete old photo first
    if ($delete_photo) {
        USER_deletePhoto ($curphoto);
    }

    // now do the upload
    if (!empty ($filename)) {
        $upload->setFileNames ($filename);
        $upload->setFieldName('photo');
        $upload->setPerms ('0644');
        if (($_CONF['max_photo_width'] > 0) &&
            ($_CONF['max_photo_height'] > 0)) {
            $upload->setMaxDimensions ($_CONF['max_photo_width'],
                                       $_CONF['max_photo_height']);
        } else {
            $upload->setMaxDimensions ($_CONF['max_image_width'],
                                       $_CONF['max_image_height']);
        }
        if ($_CONF['max_photo_size'] > 0) {
            $upload->setMaxFileSize($_CONF['max_photo_size']);
        } else {
            $upload->setMaxFileSize($_CONF['max_image_size']);
        }
        $upload->uploadFiles ();

        if ($upload->areErrors ()) {
            $display = COM_siteHeader ('menu', $LANG24[30]);
            $display .= COM_showMessageText($upload->printErrors (false),$LANG24[30],true,'error');
            $display .= COM_siteFooter ();
            echo $display;
            exit; // don't return
        }
    } else if (!$delete_photo && !empty ($curphoto)) {
        $filename = $curphoto;
    }

    return $filename;
}

/**
* Saves the user's information back to the database
*
* @A        array       User's data
*
*/
function saveuser($A)
{
    global $_CONF, $_TABLES, $_USER, $LANG04, $LANG24, $MESSAGE, $_US_VERBOSE;

    if ($_US_VERBOSE) {
        COM_errorLog('**** Inside saveuser in usersettings.php ****', 1);
    }

    $db = Database::getInstance();

    $reqid = DB_getItem ($_TABLES['users'], 'pwrequestid',"uid = " . (int) $_USER['uid']);
    if ($reqid != $A['uid']) {
        DB_change ($_TABLES['users'], 'pwrequestid', "NULL", 'uid', (int) $_USER['uid']);
        COM_accessLog ("An attempt was made to illegally change the account information of user {$_USER['uid']}.");

        return COM_refresh ($_CONF['site_url'] . '/index.php');
    }

    if ( isset($_POST['merge']) ) {
        if ( COM_applyFilter($_POST['remoteuid'],true) != $_USER['uid'] ) {
            echo COM_refresh($_CONF['site_url'].'/usersettings.php?mode=edit');
        }
        USER_mergeAccounts();
    }

    // If not set or possibly removed from template - initialize variable
    if (!isset($A['cooktime'])) {
        $A['cooktime'] = 0;
    } else {
        $A['cooktime'] = COM_applyFilter ($A['cooktime'], true);
    }
    // If empty or invalid - set to user default
    // So code after this does not fail the user password required test
    if ($A['cooktime'] < 0) { // note that == 0 is allowed!
        $A['cooktime'] = $_USER['cookietimeout'];
    }

    // to change the password, email address, or cookie timeout,
    // we need the user's current password
    $account_type = DB_getItem ($_TABLES['users'], 'account_type', "uid = {$_USER['uid']}");
    $service = DB_getItem ($_TABLES['users'], 'remoteservice', "uid = {$_USER['uid']}");
    $current_password = DB_getItem($_TABLES['users'], 'passwd',"uid = {$_USER['uid']}");
    if ( $current_password != '' && $current_password != NULL ) {
        if (!empty ($A['newp']) || ($A['email'] != $_USER['email']) || ($A['cooktime'] != $_USER['cookietimeout'])) {
            if (empty($A['passwd']) || !SEC_check_hash($A['passwd'],$current_password)) {
                COM_setMsg($MESSAGE[83],'error');
                return COM_refresh ($_CONF['site_url'].'/usersettings.php');
            } elseif ($_CONF['custom_registration'] && function_exists ('CUSTOM_userCheck')) {
                $ret = CUSTOM_userCheck ($A['username'], $A['email']);
                if (!empty($ret)) {
                    // Need a numeric return for the default message handler
                    // - if not numeric use default message
                    if (!is_numeric($ret)) {
                        $ret = 97;
                    }
                    COM_setMsg( $MESSAGE[$ret], 'error',true );
                    return COM_refresh("{$_CONF['site_url']}/usersettings.php");
                }
            } elseif ($A['newp'] != '') {
                $err = SEC_checkPwdComplexity($A['newp']);
                if ( count($err) > 0 ) {
                    $msg = implode('<br>',$err);
                    COM_setMsg($msg,'error');
                    return COM_refresh ($_CONF['site_url'].'/usersettings.php');
                }
            }
        } elseif ($_CONF['custom_registration'] && function_exists ('CUSTOM_userCheck')) {
            $ret = CUSTOM_userCheck ($A['username'], $A['email']);
            if (!empty($ret)) {
                // Need a numeric return for the default message hander - if not numeric use default message
                // - if not numeric use default message
                if (!is_numeric($ret)) {
                    $ret = 97;
                }
                COM_setMsg( $MESSAGE[$ret], 'error',true );
                return COM_refresh("{$_CONF['site_url']}/usersettings.php");
            }
        }
    }

    // Let plugins have a chance to decide what to do before saving the user, return errors.
    $msg = PLG_itemPreSave ('useredit', $A['username']);
    if (!empty ($msg)) {
        // need a numeric return value - otherwise use default message
        if (! is_numeric($msg)) {
            $msg = 97;
        }
        COM_setMsg( $MESSAGE[$msg], 'error',true );
        return COM_refresh("{$_CONF['site_url']}/usersettings.php");
    }

    // no need to filter the password as it's encoded anyway
    if ($_CONF['allow_username_change'] == 1) {
        $A['new_username'] = $A['new_username'];
        if (!empty ($A['new_username']) && USER_validateUsername($A['new_username']) && ($A['new_username'] != $_USER['username'])) {
            $A['new_username'] = DB_escapeString ($A['new_username']);
            if (DB_count ($_TABLES['users'], 'username', $A['new_username']) == 0) {
                if ($_CONF['allow_user_photo'] == 1) {
                    $photo = DB_getItem ($_TABLES['users'], 'photo',"uid = ".(int)$_USER['uid']);
                    if (!empty ($photo) && strstr($photo,$_USER['username']) !== false ) {
                        $newphoto = preg_replace ('/' . $_USER['username'] . '/',$_USER['uid'], $photo, 1);
                        $imgpath = $_CONF['path_images'] . 'userphotos/';

                        @rename ($imgpath . $photo, $imgpath . $newphoto);
                        DB_change ($_TABLES['users'], 'photo',DB_escapeString ($newphoto), "uid", (int) $_USER['uid']);
                    }
                }
                DB_change ($_TABLES['users'], 'username', $A['new_username'],"uid", (int)$_USER['uid']);
            } else {
                COM_setMsg( $MESSAGE[51], 'error',true );
                return COM_refresh ($_CONF['site_url'].'/usersettings.php');
            }
        }
    }
/* ----
    // a quick spam check with the unfiltered field contents
    $profile = '<h1>' . $LANG04[1] . ' ' . $_USER['username'] . '</h1><p>';
    if (empty($service)) {
        $profile .= COM_createLink($A['homepage'], $A['homepage']) . '<br />';
    }
    $profile .= $A['location'] . '<br />' . $A['sig'] . '<br />'
                . PLG_replaceTags($A['about']) . '<br />' . $A['pgpkey'] . '</p>';

    $spamData = array(
        'username'  => $A['username'],
        'email'     => $A['email'],
        'ip'        => $_SERVER['REAL_ADDR'],
        'type'      => 'profile'
    );

    $result = PLG_checkforSpam ($profile, $_CONF['spamx'],$spamData);
    if ($result > 0) {
        COM_displayMessageAndAbort ($result, 'spamx', 403, 'Forbidden');
    }
---- */
    $A['email'] = COM_applyFilter ($A['email']);
    $A['email_conf'] = COM_applyFilter ($A['email_conf']);
    $A['homepage'] = COM_applyFilter ($A['homepage']);

    // basic filtering only
    $A['fullname'] = COM_truncate(trim(USER_sanitizeName($A['fullname'])),80);
    $A['location'] = strip_tags ($A['location']);
    $A['sig'] = strip_tags ($A['sig']);
    $A['about'] = strip_tags ($A['about']);
    $A['pgpkey'] = strip_tags ($A['pgpkey']);

    // filter / check social integrations here
    $social_services = Social::followMeProfile( $_USER['uid'] );
    foreach ( $social_services AS $service ) {
        $service_input = $service['service'].'_username';
        if ( isset( $A['$service_input'])) {
            $A[$service_input] = strip_tags($A[$service_input]);
        }
    }

    if (!COM_isEmail ($A['email'])) {
        COM_setMsg($MESSAGE[52],'error');
        return COM_refresh ($_CONF['site_url'].'/usersettings.php');
    } else if ($A['email'] !== $A['email_conf']) {
        COM_setMsg($MESSAGE[78],'error');
        return COM_refresh ($_CONF['site_url'].'/usersettings.php');
    } else if (emailAddressExists ($A['email'], $_USER['uid'])) {
        COM_setMsg($MESSAGE[56],'error');
        return COM_refresh ($_CONF['site_url'].'/usersettings.php');
    } else {
        if ( $current_password != '' ) {
            if (!empty($A['newp'])) {
                $A['newp'] = trim($A['newp']);
                $A['newp_conf'] = trim($A['newp_conf']);
                if (($A['newp'] == $A['newp_conf']) && SEC_check_hash($A['passwd'],$current_password) ){
                    $passwd = SEC_encryptPassword($A['newp']);
                    DB_change($_TABLES['users'], 'passwd', DB_escapeString($passwd),"uid", (int)$_USER['uid']);
                    if ($A['cooktime'] > 0) {
                        $cooktime = $A['cooktime'];
                        $token_ttl = $A['cooktime'];
                    } else {
                        $cooktime = 0;
                        $token_ttl = 14400;
                    }
                    $ltToken = SEC_createTokenGeneral('ltc',$token_ttl);
                    SEC_setCookie($_CONF['cookie_password'], $ltToken,time() + $cooktime);
                } elseif (!SEC_check_hash($A['passwd'],$current_password) ) {
                    COM_setMsg($MESSAGE[68],'error');
                    return COM_refresh ($_CONF['site_url'].'/usersettings.php');
                } elseif ($A['newp'] != $A['newp_conf']) {
                    COM_setMsg($MESSAGE[67],'error');
                    return COM_refresh ($_CONF['site_url'].'/usersettings.php');
                }
            }
        } else {
            // Cookie
            if ($A['cooktime'] > 0) {
                $cooktime = $A['cooktime'];
            } else {
                $cooktime = 0;
            }
            $ltToken = SEC_createTokenGeneral('ltc',$cooktime);
            SEC_setCookie($_CONF['cookie_password'], $ltToken, time() + $cooktime);
        }

        if ($_US_VERBOSE) {
            COM_errorLog('cooktime = ' . $A['cooktime'],1);
        }

        if ($A['cooktime'] <= 0) {
            $cookie_timeout = 0;
            $token_ttl = 14400;
        } else {
            $cookie_timeout = time() + $A['cooktime'];
            $token_ttl = $A['cooktime'];
        }
        SEC_setCookie ($_CONF['cookie_name'], $_USER['uid'], $cookie_timeout,
                       $_CONF['cookie_path'], $_CONF['cookiedomain'],
                       $_CONF['cookiesecure'],true);
        DB_query("DELETE FROM {$_TABLES['tokens']} WHERE owner_id=".(int)$_USER['uid']." AND urlfor='ltc'");
        if ( $cookie_timeout > 0 ) {
            $ltToken = SEC_createTokenGeneral('ltc',$token_ttl);
            SEC_setCookie ($_CONF['cookie_password'], $ltToken, $cookie_timeout,
                           $_CONF['cookie_path'], $_CONF['cookiedomain'],
                           $_CONF['cookiesecure'],true);
        } else {
            SEC_setCookie ($_CONF['cookie_password'], '', -10000,
                           $_CONF['cookie_path'], $_CONF['cookiedomain'],
                           $_CONF['cookiesecure'],true);
        }
        if ($_CONF['allow_user_photo'] == 1) {
            $delete_photo = '';
            if (isset ($A['delete_photo'])) {
                $delete_photo = $A['delete_photo'];
            }
            $filename = handlePhotoUpload ($delete_photo);
        }

        if (!empty ($A['homepage'])) {
            $pos = MBYTE_strpos ($A['homepage'], ':');
            if ($pos === false) {
                $A['homepage'] = 'http://' . $A['homepage'];
            }
            else {
                $prot = substr ($A['homepage'], 0, $pos + 1);
                if (($prot != 'http:') && ($prot != 'https:')) {
                    $A['homepage'] = 'http:' . substr ($A['homepage'], $pos + 1);
                }
            }
            $A['homepage'] = DB_escapeString ($A['homepage']);
        }

        $A['fullname']  = DB_escapeString ($A['fullname']);
        $A['email']     = DB_escapeString ($A['email']);
        $A['location']  = DB_escapeString ($A['location']);
        $A['sig']       = DB_escapeString ($A['sig']);
        $A['about']     = DB_escapeString ($A['about']);
        $A['pgpkey']    = DB_escapeString ($A['pgpkey']);

        if (!empty ($filename)) {
            if (!file_exists ($_CONF['path_images'] . 'userphotos/' . $filename)) {
                $filename = '';
            }
        }

        DB_query("UPDATE {$_TABLES['users']} SET fullname='{$A['fullname']}',email='{$A['email']}',homepage='{$A['homepage']}',sig='{$A['sig']}',cookietimeout=".(int) $A['cooktime'].",photo='".DB_escapeString($filename)."' WHERE uid=".(int)$_USER['uid']);
        DB_query("UPDATE {$_TABLES['userinfo']} SET pgpkey='{$A['pgpkey']}',about='{$A['about']}',location='{$A['location']}' WHERE uid=".(int)$_USER['uid']);

        foreach ( $social_services AS $service ) {
            $service_input = $service['service'].'_username';
            if ( isset($A[$service_input])) {
                $A[$service_input] = DB_escapeString($A[$service_input]);
            } else {
                $A[$service_input] = '';
            }
            if ( $A[$service_input] != '' ) {
                $sql  = "REPLACE INTO {$_TABLES['social_follow_user']} (ssid,uid,ss_username) ";
                $sql .= " VALUES (" . (int) $service['service_id'] . ",".$_USER['uid'].",'".$A[$service_input]."');";
                DB_query($sql,1);
            } else {
                $sql = "DELETE FROM {$_TABLES['social_follow_user']} WHERE ssid = ".(int) $service['service_id']." AND uid=".(int) $_USER['uid'];
                DB_query($sql,1);
            }
        }

        // Call custom registration save function if enabled and exists
        if ($_CONF['custom_registration'] AND (function_exists('CUSTOM_userSave'))) {
            CUSTOM_userSave($_USER['uid']);
        }

        PLG_userInfoChanged ((int)$_USER['uid']);

        // at this point, the user information has been saved, but now we're going to check to see if
        // the user has requested resynchronization with their remoteservice account or to unlink their remote account
        $msg = 5; // default msg = Your account information has been successfully saved

        if ( isset($A['unmerge']) ) {
            if ( USER_unmergeAccounts() ) {
                $msg = 117;
            } else {
                $msg = 118;
            }
        } else {
            if (isset($A['resynch']) ) {
                if ($_CONF['user_login_method']['oauth'] && (strpos($_USER['remoteservice'], 'oauth.') === 0)) {
                    $modules = SEC_collectRemoteOAuthModules();
                    $active_service = (count($modules) == 0) ? false : in_array(substr($_USER['remoteservice'], 6), $modules);
                    if (!$active_service) {
                        $status = -1;
                        $msg = 115; // Remote service has been disabled.
                    } else {
                        $service = substr($_USER['remoteservice'], 6);
                        $consumer = new OAuthConsumer($service);
                        $callback_url = $_CONF['site_url'];
                        $consumer->setRedirectURL($callback_url);
                        $user = $consumer->authenticateUser();
                        $consumer->resyncUserData($user);
                    }
                }

                if ($msg != 5) {
                    $msg = 114; // Account saved but re-synch failed.
                    COM_errorLog($MESSAGE[$msg]);
                }
            }
        }
        PLG_profileExtrasSave ();
        PLG_profileSave();

        $c = Cache::getInstance()->deleteItemsByTags(array('menu','userdata'));

        if ($_US_VERBOSE) {
            COM_errorLog('**** Leaving saveuser in usersettings.php ****', 1);
        }
        if ( $msg == 5 ) {
            COM_setMsg($MESSAGE[$msg],'info');
        } else {
            COM_setMsg($MESSAGE[$msg],'error');
        }

        return COM_refresh ($_CONF['site_url'] . '/users.php?mode=profile&amp;uid='.$_USER['uid']);
    }
}

/**
* Shows a profile for a user
*
* This grabs the user profile for a given user and displays it
*
* @param    int     $user   User ID of profile to get
* @param    int     $msg    Message to display (if != 0)
* @return   string          HTML for user profile page
*
*/
function userprofile ($user, $msg = 0)
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG04, $LANG09, $LANG28, $LANG_LOGIN;

    $retval = '';

    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) || ($_CONF['profileloginrequired'] == 1))) {
        $retval .= COM_siteHeader ('menu');
        $retval .= SEC_loginRequiredForm();
        $retval .= COM_siteFooter ();
        return $retval;
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
    $user_templates->set_var ('user_bio', PLG_replaceTags(nl2br ($A['about']),'glfusion','about_user'));

    $user_templates->set_var('follow_me',Social::getFollowMeIcons( $user, 'follow_user_profile.thtml' ));

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
        $sql = "SELECT sid,title,UNIX_TIMESTAMP(date) AS unixdate FROM {$_TABLES['stories']} WHERE (uid = '".(int) $user."') AND (draft_flag = 0) AND (date <= '".$_CONF['_now']->toMySQL(true)."') AND (tid IN ($topics))" . COM_getPermSQL ('AND');
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
        $commentCounter = 0;
        $sql = "SELECT * FROM {$_TABLES['comments']} WHERE uid = " . (int) $user . " ORDER BY date DESC";
        $result = DB_query($sql);

        while ( ( $row = DB_fetchArray($result) ) ) {
            if ( $commentCounter >= 10 ) break;
                $itemInfo = PLG_getItemInfo($row['type'], $row['sid'],'id');
                if ( is_array($itemInfo) || $itemInfo == '' ) continue;
                $user_templates->set_var ('cssid', ($commentCounter % 2) + 1);
                $user_templates->set_var ('row_number', ($commentCounter + 1) . '.');
                $row['title'] = str_replace ('$', '&#36;', $row['title']);
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
    $sql = "SELECT COUNT(*) AS count FROM {$_TABLES['stories']} WHERE (uid = ".(int) $user.") AND (draft_flag = 0) AND (date <= '".$_CONF['_now']->toMySQL(true)."')" . COM_getPermSQL ('AND');
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
* Saves user's preferences back to the database
*
* @A        array       User's data to save
*
*/
function savepreferences($A)
{
    global $_CONF, $_TABLES, $_USER;

    if (isset ($A['noicons']) && ($A['noicons'] == 'on')) {
        $A['noicons'] = 1;
    } else {
        $A['noicons'] = 0;
    }
    if (isset ($A['willing']) && ($A['willing'] == 'on')) {
        $A['willing'] = 1;
    } else {
        $A['willing'] = 0;
    }
    if (isset ($A['noboxes']) && ($A['noboxes'] == 'on')) {
        $A['noboxes'] = 1;
    } else {
        $A['noboxes'] = 0;
    }
    if (isset ($A['emailfromadmin']) && ($A['emailfromadmin'] == 'on')) {
        $A['emailfromadmin'] = 1;
    } else {
        $A['emailfromadmin'] = 0;
    }
    if (isset ($A['emailfromuser']) && ($A['emailfromuser'] == 'on')) {
        $A['emailfromuser'] = 1;
    } else {
        $A['emailfromuser'] = 0;
    }
    if (isset ($A['showonline']) && ($A['showonline'] == 'on')) {
        $A['showonline'] = 1;
    } else {
        $A['showonline'] = 0;
    }

    $A['maxstories'] = COM_applyFilter ($A['maxstories'], true);
    if (empty ($A['maxstories'])) {
        $A['maxstories'] = 0;
    } else if ($A['maxstories'] > 0) {
        if ($A['maxstories'] < $_CONF['minnews']) {
            $A['maxstories'] = $_CONF['minnews'];
        }
    }

    $TIDS  = @array_values($A['topics']);
    $AIDS  = @array_values($A['selauthors']);
    $BOXES = @array_values($A['blocks']);
    $ETIDS = @array_values($A['dgtopics']);
    $allowed_etids = USER_buildTopicList ();
    $AETIDS = explode (' ', $allowed_etids);

    $tids = '';
    if (is_array($TIDS) && sizeof ($TIDS) > 0) {
        $tids = DB_escapeString (implode (' ', array_intersect ($AETIDS, $TIDS)));
    }
    $aids = '';
    if (is_array($AIDS) && sizeof ($AIDS) > 0) {
        foreach ($AIDS as $key => $val) {
            $AIDS[$key] = intval($val);
        }
        $aids = DB_escapeString (implode (' ', $AIDS));
    }
    $selectedblocks = '';
    $selectedBoxes = array();
    if (is_array($BOXES) && count ($BOXES) > 0) {
        foreach ($BOXES AS $key => $val) {
            $BOXES[$key] = intval($val);
        }
        $boxes = DB_escapeString(implode(',', $BOXES));

        $blockresult = DB_query("SELECT bid,name FROM {$_TABLES['blocks']} WHERE bid NOT IN ($boxes)");

        $numRows = DB_numRows($blockresult);
        for ($x = 1; $x <= $numRows; $x++) {
            $row = DB_fetchArray ($blockresult);
            if ($row['name'] <> 'user_block' AND $row['name'] <> 'admin_block' AND $row['name'] <> 'section_block') {
                $selectedblocks .= $row['bid'];
                if ($x <> $numRows) {
                    $selectedblocks .= ' ';
                }
            }
        }
    }

    $etids = '';
    if (is_array($ETIDS) && sizeof ($ETIDS) > 0) {
        $etids = DB_escapeString (implode (' ', array_intersect ($AETIDS, $ETIDS)));
    }

    if (isset ($A['tzid'])) {
        $A['tzid'] = COM_applyFilter ($A['tzid']);
    } else {
        $A['tzid'] = '';
    }

    if (isset($A['theme'])) {
        $A['theme'] = COM_applyFilter($A['theme']);
    }
    if (empty($A['theme'])) {
        $A['theme'] = $_CONF['theme'];
    }

    if (isset($A['language'])) {
        $A['language'] = COM_applyFilter($A['language']);
    }
    if (empty($A['language'])) {
        $A['language'] = $_CONF['language'];
    }

    if ( isset($A['search_result_format']) ) {
        $A['search_result_format'] = COM_applyFilter($A['search_result_format']);
    } else {
        $A['search_result_format'] = 'google';
    }

    // Save theme, when doing so, put in cookie so we can set the user's theme
    // even when they aren't logged in
    $theme    = DB_escapeString ($A['theme']);
    $language = DB_escapeString ($A['language']);
    DB_query("UPDATE {$_TABLES['users']} SET theme='$theme',language='$language' WHERE uid = {$_USER['uid']}");
    SEC_setCookie ($_CONF['cookie_theme'], $A['theme'], time() + 31536000,
                   $_CONF['cookie_path'], $_CONF['cookiedomain'],
                   $_CONF['cookiesecure'],false);
    SEC_setCookie ($_CONF['cookie_language'], $A['language'], time() + 31536000,
                   $_CONF['cookie_path'], $_CONF['cookiedomain'],
                   $_CONF['cookiesecure'],false);
    SEC_setCookie ($_CONF['cookie_tzid'], $A['tzid'], time() + 31536000,
                   $_CONF['cookie_path'], $_CONF['cookiedomain'],
                   $_CONF['cookiesecure'],false);

    $A['dfid'] = (int)COM_applyFilter ($A['dfid'], true);

    DB_query("UPDATE {$_TABLES['userprefs']} SET search_result_format='".DB_escapeString($A['search_result_format'])."',noicons=".(int) $A['noicons'].", willing=".(int) $A['willing'].", dfid=".(int) $A['dfid'].", tzid='".DB_escapeString($A['tzid'])."', emailfromadmin='".DB_escapeString($A['emailfromadmin'])."', emailfromuser=".(int) $A['emailfromuser'].", showonline=".(int)$A['showonline']." WHERE uid=".(int)$_USER['uid']);

    if (empty ($etids)) {
        $etids = '-';
    }
    DB_save($_TABLES['userindex'],"uid,tids,aids,boxes,noboxes,maxstories,etids","{$_USER['uid']},'$tids','$aids','$selectedblocks',".(int)$A['noboxes'].",".(int)$A['maxstories'].",'$etids'");

    $A['commentmode'] = COM_applyFilter ($A['commentmode']);
    if (empty ($A['commentmode'])) {
        $A['commentmode'] = $_CONF['comment_mode'];
    }
    $A['commentmode'] = DB_escapeString ($A['commentmode']);

    $A['commentorder'] = COM_applyFilter ($A['commentorder']);
    $A['commentorder'] = strtoupper($A['commentorder']) == 'DESC' ? 'DESC' : 'ASC';

    $A['commentorder'] = DB_escapeString ($A['commentorder']);

    $A['commentlimit'] = COM_applyFilter ($A['commentlimit'], true);
    if ($A['commentlimit'] <= 0) {
        $A['commentlimit'] = $_CONF['comment_limit'];
    }

    DB_save($_TABLES['usercomment'],'uid,commentmode,commentorder,commentlimit',"{$_USER['uid']},'{$A['commentmode']}','{$A['commentorder']}',".(int) $A['commentlimit']);

    $subscription_deletes  = @array_values($A['subdelete']);
    if ( is_array($subscription_deletes) ) {
        foreach ( $subscription_deletes AS $subid ) {
            DB_delete($_TABLES['subscriptions'],'sub_id',(int) $subid);
        }
    }
    $c = Cache::getInstance()->deleteItemsByTags(array('story','menu','userdata'));

    PLG_userInfoChanged ($_USER['uid']);
}

// MAIN
$mode = '';
if (isset($_POST['btncancel']) AND $_POST['btncancel'] == $LANG_ADMIN['cancel']) {
    echo COM_refresh($_CONF['site_url']);
    exit;
} else if (isset($_POST['btnsubmit']) AND ($_POST['btnsubmit'] == $LANG04[96]) && ($_POST['mode'] != 'deleteconfirmed')) {
    $mode = 'confirmdelete';
} else if (isset ($_POST['mode'])) {
    $mode = COM_applyFilter ($_POST['mode']);
} else if (isset ($_GET['mode'])) {
    $mode = COM_applyFilter ($_GET['mode']);
}

$display = '';

if (isset ($_USER['uid']) && ($_USER['uid'] > 1)) {
    switch ($mode) {
    case 'saveuser':
        savepreferences ($_POST);
        $display .= saveuser($_POST);
        break;

    case 'savepreferences':
        savepreferences ($_POST);
        COM_setMsg( $MESSAGE[6], 'info',false );
        $display .= COM_refresh ($_CONF['site_url'].'/usersettings.php?mode=preferences');
        break;

    case 'confirmdelete':
        if (($_CONF['allow_account_delete'] == 1) && ($_USER['uid'] > 1)) {
            $accountId = COM_applyFilter ($_POST['account_id']);

            $current_password = DB_getItem($_TABLES['users'],'passwd',"uid=".(int)$_USER['uid']);
//            if (!empty ($accountId) && !empty($_POST['current_password']) && SEC_check_hash(trim($_POST['current_password']),$current_password)) {
            if (!empty ($accountId)) {
                $display .= confirmAccountDelete ($accountId);
            } else {
                $display = COM_refresh ($_CONF['site_url'] . '/index.php');
            }
        } else {
            $display = COM_refresh ($_CONF['site_url'] . '/index.php');
        }
        break;

    case 'deleteconfirmed':
        if (($_CONF['allow_account_delete'] == 1) && ($_USER['uid'] > 1)) {
            $accountId = COM_applyFilter ($_POST['account_id']);
            if (!empty ($accountId)) {
                $display .= deleteUserAccount ($accountId);
            } else {
                $display = COM_refresh ($_CONF['site_url'] . '/index.php');
            }
        } else {
            $display = COM_refresh ($_CONF['site_url'] . '/index.php');
        }
        break;

    case 'plugin':
        PLG_profileExtrasSave ($_POST['plugin']);
        COM_setMsg( $MESSAGE[5], 'info',false );
        $display = COM_refresh ($_CONF['site_url'].'/usersettings.php');
        break;

    case 'synch':
        // This case is the result of a callback from an OAuth service.
        // The user has made a request to resynch their glFusion user account with the remote OAuth service
        if ($_CONF['user_login_method']['oauth'] && (strpos($_USER['remoteservice'], 'oauth.') === 0) && isset($_GET['oauth_login'])) {
            $msg = 5;

            $modules = SEC_collectRemoteOAuthModules();
            $active_service = (count($modules) == 0) ? false : in_array(substr($_GET['oauth_login'], 6), $modules);
            if (!$active_service) {
                $status = -1;
                $msg = 114; // resynch with remote account has failed but your other account information has been successfully saved.
            } else {
                $query = array_merge($_GET, $_POST);
                $service = $query['oauth_login'];
                // COM_errorLog("-------------------------------------------------------------------------");
                // COM_errorLog("usersettings.php?mode=resynch&oauth_login={$service}");
                // COM_errorLog("-------------------------------------------------------------------------");

                $consumer = new OAuthConsumer($service);

                if($service == 'oauth.facebook') {
                    // facebook resynchronizations are simple to perform
                    $oauth_userinfo = $consumer->refresh_userinfo();
                    if (empty($oauth_userinfo)) {
                        $msg = 114; // Account saved but re-synch failed.
                        COM_errorLog($MESSAGE[$msg]);
                    } else {
                        $consumer->resyncUserData($oauth_userinfo);
                    }
                } else {
                    // other OAuth services are more complex
                    // setup what we need to callback and authenticate
                    $callback_query_string = $consumer->getCallback_query_string();
                    // COM_errorLog("callback_query_string={$callback_query_string}");
                    $cancel_query_string = $consumer->getCancel_query_string();
                    // COM_errorLog("cancel_query_string={$cancel_query_string}");
                    $callback_url = $_CONF['site_url'] . '/usersettings.php?mode=synch&oauth_login=' . $service;
                    // COM_errorLog("callback_url={$callback_url}");

                    // authenticate with the remote service
                    if (!isset($query[$callback_query_string]) && (empty($cancel_query_string) || !isset($query[$cancel_query_string]))) {
                        $msg = 114; // Resynch with remote account has failed but other account information has been successfully saved
                    // elseif the callback query string is set, then we have successfully authenticated
                    } elseif (isset($query[$callback_query_string])) {
                        $oauth_userinfo = $consumer->sreq_userinfo_response($query);
                        if (empty($oauth_userinfo)) {
                            $msg = 111; // Authentication error.
                        } else {
                            $consumer->resyncUserData($oauth_userinfo);
                        }
                    } elseif (!empty($cancel_query_string) && isset($query[$cancel_query_string])) {
                        $msg = 112; // Certification has been cancelled.
                    } else {
                        $msg = 91; // You specified an invalid identity URL.
                    }
                }
            }

            if ($msg == 5) {
                COM_setMsg( $MESSAGE[5], 'info',false );
                $display = COM_refresh ($_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $_USER['uid']);
            } else {
                COM_setMsg( $MESSAGE[$msg], 'error',true );
                COM_errorLog($MESSAGE[$msg]);
                $display = COM_refresh ($_CONF['site_url'] . '/usersettings.php');
            }
            break;
        }

        // If OAuth is disabled, drop into default case

    default: // also if $mode == 'edit', 'preferences', or 'comments'
        $display .= COM_siteHeader('menu', $LANG04[16]);
        $display .= COM_showMessageFromParameter();
        $display .= edituser();
        $display .= COM_siteFooter();
        break;
    }
} else {
    $display .= COM_siteHeader ('menu');
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter ();
}

echo $display;

?>
