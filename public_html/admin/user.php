<?php
/**
* glFusion CMS
*
* glFusion user administration page.
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*   Mark A. Howard  mark AT usable-web DOT com
*
*  Based on prior work Copyright (C) 2000-2009 by the following authors:
*  Authors: Tony Bibbs        - tony AT tonybibbs DOT com
*           Mark Limburg      - mlimburg AT users DOT sourceforge DOT net
*           Jason Whittenburg - jwhitten AT securitygeeks DOT com
*           Dirk Haun         - dirk AT haun-online DOT de
*
*/

// Set this to true to get various debug messages from this script
$_USER_VERBOSE = false;

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Social\Social;
use \glFusion\Admin\AdminAction;
use \glFusion\Log\Log;
use \glFusion\FieldList;

USES_lib_user();
USES_lib_admin();

$display = '';

// Make sure user has access to this page
if (!SEC_hasRights('user.edit')) {
    Log::logAccessViolation('User Administration');
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[37], $MESSAGE[30], true,'error');
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

/**
* Shows the user edit form
*
* @param    int     $uid    User to edit
* @param    int     $msg    Error message to display
* @return   string          HTML for user edit form
*
*/
function USER_edit($uid = '', $msg = '')
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG01, $LANG04, $LANG28, $LANG_ADMIN,
           $LANG_configselects, $LANG_configSelect,$LANG_confignames,$LANG_ACCESS,$MESSAGE,$_IMAGE_TYPE;

    $retval = '';
    $newuser = 0;

    // override $LANG_MYACCOUNT so we remove any plugins if creating an account....
    if ( $uid == '' || $uid < 2 ) {
        $LANG_MYACCOUNT = array(
            'pe_namepass' => $LANG_ACCESS['pe_namepass'],
            'pe_userinfo' => $LANG_ACCESS['pe_userinfo'],
            'pe_layout'   => $LANG_ACCESS['pe_layout'],
            'pe_content'  => $LANG_ACCESS['pe_content'],
            'pe_privacy'  => $LANG_ACCESS['pe_privacy'],
        );
    }

    // language overrides
    $LANG_MYACCOUNT['pe_namepass'] = $LANG_ACCESS['pe_namepass'];
    $LANG_MYACCOUNT['pe_userinfo'] = $LANG_ACCESS['pe_userinfo'];

    $userform = new Template ($_CONF['path_layout'] . 'admin/user/');
    $userform->set_file('user','adminuseredit.thtml');

    $userform->set_var('enctype',' enctype="multipart/form-data"');
    $userform->set_var('lang_save', $LANG_ADMIN['save']);
    $userform->set_var('lang_cancel',$LANG_ADMIN['cancel']);

    // build navigation bar
    $navbar = new navbar;
    $cnt = 0;

    if ( is_array($LANG_MYACCOUNT) ) {
        foreach ($LANG_MYACCOUNT as $id => $label) {
            if ( $id == 'pe_preview' ) {
                continue;
            }
            if ( $id == 'pe_content' && $_CONF['hide_exclude_content'] == 1 && $_CONF['emailstories'] == 0 ) {
                continue;
            } elseif ($id == 'pe_twofactor' ) {
                continue;
            } else {
                $navbar->add_menuitem($label,'showhideProfileEditorDiv("'.$id.'",'.$cnt.');return false;',true);
                $cnt++;
                if ( $id == 'pe_namepass' ) {
                    $navbar->add_menuitem($LANG01[96],'showhideProfileEditorDiv("'.'pe_usergroup'.'",'.$cnt.');return false;',true);
                    $cnt++;
                }
            }
        }
        $navbar->set_selected($LANG_MYACCOUNT['pe_namepass']);
    }
    $userform->set_var('navbar', $navbar->generate());
    $userform->set_var('no_javascript_warning',$LANG04[150]);


    if (!empty ($msg) && !empty ($uid) && ($uid > 1)) {
        // an error occured while editing a user - if it was a new account,
        // don't bother trying to read the user's data from the database ...
        $cnt = DB_count ($_TABLES['users'], 'uid', $uid);
        if ($cnt == 0) {
            $uid = '';
        }
    }

    if (!empty ($uid) && ($uid > 1)) {
        $result = DB_query("SELECT * FROM {$_TABLES['users']},{$_TABLES['userprefs']},{$_TABLES['userinfo']},{$_TABLES['usercomment']},{$_TABLES['userindex']} WHERE {$_TABLES['users']}.uid = $uid AND {$_TABLES['userprefs']}.uid = $uid AND {$_TABLES['userinfo']}.uid = $uid AND {$_TABLES['usercomment']}.uid = $uid AND {$_TABLES['userindex']}.uid = $uid");
        $U = DB_fetchArray ($result);
        if (empty ($U['uid'])) {
            echo COM_refresh ($_CONF['site_admin_url'] . '/user.php');
            exit;
        }
        if (SEC_inGroup('Root',$uid) AND !SEC_inGroup('Root')) {
            // the current admin user isn't Root but is trying to change
            // a root account.  Deny them and log it.
            $retval .= COM_showMessageText(sprintf($LANG_ACCESS['editrootmsg'],$_CONF['site_admin_url']), $LANG28[1], true,'error');
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to edit a Root account with insufficient privileges.");
            return $retval;
        }
        $curtime = COM_getUserDateTimeFormat($U['regdate']);
        $lastlogin = DB_getItem ($_TABLES['userinfo'], 'lastlogin', "uid = $uid");
        $lasttime = COM_getUserDateTimeFormat ($lastlogin);
        $display_name = COM_getDisplayName ($uid);
        $menuText = $LANG_ACCESS['editinguser'] . $U['username'];
        if ($U['fullname'] != '' ) {
            $menuText .= ' - ' . $U['fullname'];
        }
    } else {
        $U['uid'] = '';
        $U['username'] = '';
        $U['fullname'] = '';
        $U['email'] = '';
        $U['remoteuser'] = 0;
        $U['remoteusername'] = '';
        $U['remoteservice'] = '';
        $U['homepage'] = '';
        $U['location'] = '';
        $U['sig'] = '';
        $U['about'] = '';
        $U['pgpkey'] = '';
        $U['noicons'] = 0;
        $U['etids'] = '-';
        $uid = '';
        $U['cookietimeout'] = $_CONF['session_cookie_timeout'];// 2678400;
        $U['status'] = USER_ACCOUNT_AWAITING_ACTIVATION;
        $U['account_type'] = LOCAL_USER;
        $U['emailfromadmin'] = 1;
        $U['emailfromuser'] = 1;
        $U['showonline'] = 1;
        $U['maxstories'] = 0;
        $U['dfid'] = 0;
        $U['commentmode'] = $_CONF['comment_mode'];
        $U['commentorder'] = 'ASC';
        $U['commentlimit'] = 100;
        $curtime =  COM_getUserDateTimeFormat();
        $lastlogin = '';
        $lasttime = '';
        $U['status'] = USER_ACCOUNT_ACTIVE;
        $newuser = 1;
        $userform->set_var('newuser',1);
        $menuText = $LANG_ACCESS['createnewuser'];
    }

    // now let's check to see if any post vars are set in the event we are returning from an error...

    if ( isset($_POST['new_username']) )
        $U['username']       = trim($_POST['new_username']);
    if ( isset($_POST['fullname']) )
        $U['fullname']       = COM_truncate(trim(USER_sanitizeName($_POST['fullname'])),80);
    if ( isset($_POST['remoteuser']) )
        $U['remoteuser'] = ($_POST['remoteuser'] == 'on' ? 1 : 0);
    if ( isset($_POST['remoteusername']) )
        $U['remoteusername'] = COM_truncate(trim($_POST['remoteusername']),60);
    if ( isset($_POST['remoteservice']) )
        $U['remoteservice'] = COM_applyFilter($_POST['remoteservice']);
    if ( isset($_POST['userstatus'] ) )
        $U['status']     = COM_applyFilter($_POST['userstatus'],true);
    if ( isset($_POST['cooktime'] ) )
        $U['cookietimeout'] = COM_applyFilter($_POST['cooktime'],true);
    if ( isset($_POST['email'] ) )
        $U['email']          = trim($_POST['email']);
    if ( isset($_POST['homepage']) )
        $U['homepage']       = trim($_POST['homepage']);
    if ( isset($_POST['location']) )
        $U['location']       = trim($_POST['location']);
    if ( isset($_POST['sig']) )
        $U['sig']            = trim($_POST['sig']);
    if ( isset($_POST['about'] ) )
        $U['about'] = trim($_POST['about']);
    if ( isset($_POST['pgpkey']) )
        $U['pgpkey']         = trim($_POST['pgpkey']);
    if ( isset($_POST['language'] ) )
        $U['language']       = trim(COM_applyFilter($_POST['language']));
    if ( isset($_POST['theme'] ) )
        $U['theme']          = trim(COM_applyFilter($_POST['theme']));
    if ( isset($_POST['maxstories'] ) )
        $U['maxstories']     = COM_applyFilter($_POST['maxstories'],true);
    if ( isset($_POST['tzid'] ) )
        $U['tzid']           = COM_applyFilter($_POST['tzid']);
    if ( isset($_POST['dfid'] ) )
        $U['dfid']           = COM_applyFilter($_POST['dfid'],true);
    if ( isset($_POST['commentmode'] ) )
        $U['commentmode']    =  COM_applyFilter($_POST['commentmode']);
    if ( isset($_POST['commentorder'] ) )
        $U['commentorder']   = $_POST['commentorder'] == 'DESC' ? 'DESC' : 'ASC';
    if ( isset($_POST['commentlimit'] ) )
        $U['commentlimit']   = COM_applyFilter($_POST['commentlimit'],true);
    if ( isset($_POST['emailfromuser'] ) )
        $U['emailfromuser']  = $_POST['emailfromuser'] == 'on' ? 1 : 0;
    if ( isset($_POST['emailfromadmin']) )
        $U['emailfromadmin'] = $_POST['emailfromadmin'] == 'on' ? 1 : 0;
    if ( isset($_POST['noicons'] ) )
        $U['noicons']        = $_POST['noicons'] == 'on' ? 1 : 0;
    if ( isset($_POST['showonline'] ) )
        $U['showonline']     = $_POST['showonline'] == 'on' ? 1 : 0;
    if ( isset($_POST['topic_order']) )
        $U['topic_order']    = $_POST['topic_order'] == 'ASC' ? 'ASC' : 'DESC';


    $retval .= COM_startBlock($LANG28[1] . ' :: ' . $menuText, '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    if (!empty ($msg)) {
        $retval .= COM_showMessageText($MESSAGE[$msg],$LANG28[22],false);
    }

    if ( $newuser == 1 ) {
        $lang_create_or_edit = $LANG_ADMIN['create_new'];
    } else {
        $lang_create_or_edit = $LANG_ADMIN['edit'];
    }

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/user.php',
              'text' => $LANG_ADMIN['admin_users']),
        array('url' => $_CONF['site_admin_url'] . '/user.php?edit=x',
              'text' => $lang_create_or_edit,'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/user.php?import=x',
              'text' => $LANG28[23]),
        array('url' => $_CONF['site_admin_url'] . '/user.php?batchadmin=x',
              'text' => $LANG28[54]),
        array('url' => $_CONF['site_admin_url'] . '/prefeditor.php',
              'text' => $LANG28[95]),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= ADMIN_createMenu(
        $menu_arr,
        '',
        $_CONF['layout_url'] . '/images/icons/user.' . $_IMAGE_TYPE
    );

    $userform->set_var('account_panel',USER_accountPanel($U));
    $userform->set_var('group_panel',USER_groupPanel($U));
    $userform->set_var('userinfo_panel',USER_userinfoPanel($U));
    $userform->set_var('layout_panel',USER_layoutPanel($U));
    if ( $_CONF['hide_exclude_content'] == 0 || $_CONF['emailstories'] == 1 ) {
        $userform->set_var('content_panel',USER_contentPanel($U));
    }
    $userform->set_var('privacy_panel',USER_privacyPanel($U));
    if (!empty($uid) && $uid > 1 ) {
        $userform->set_var('plugin_panel',PLG_profileEdit($uid));
    }

    if ( isset($LANG_MYACCOUNT['pe_subscriptions']) ) {
        $userform->set_var('subscription_panel',USER_subscriptionPanel($U));
    }

    if (!empty($uid) && ($uid != $_USER['uid']) && SEC_hasRights('user.delete')) {
        $userform->set_var('delete_option', true);
    }

    $userform->set_var('gltoken_name', CSRF_TOKEN);
    $userform->set_var('gltoken', SEC_createToken());

    $retval .= $userform->finish ($userform->parse ('output', 'user'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

function USER_accountPanel($U,$newuser = 0)
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG_TFA, $LANG04,$LANG28;

    $uid = $U['uid'];

    // set template
    $userform = new Template ($_CONF['path_layout'] . 'admin/user/');
    $userform->set_file('user','accountpanel.thtml');

    // get users display name
    $display_name = COM_getDisplayName ($uid);

    // define all the language constants...
    $userform->set_var(array(
        'lang_name_legend'              => $LANG04[128],
        'lang_userid'                   => $LANG28[2],
        'lang_regdate'                  => $LANG28[14],
        'lang_lastlogin'                => $LANG28[35],
        'lang_username'                 => $LANG04[2],
        'lang_fullname'                 => $LANG04[3],
        'lang_user_status'              => $LANG28[46],
        'lang_password_email_legend'    => $LANG04[129],
        'lang_password_help_title'      => $LANG04[146],
        'lang_enter_current_password'   => $LANG04[127],
        'lang_password_help'            => $LANG04[147],
        'lang_old_password'             => $LANG04[110],
        'lang_password'                 => $LANG04[4],
        'lang_password_conf'            => $LANG04[108],
        'lang_cooktime'                 => $LANG04[68],
        'lang_email'                    => $LANG04[5],
        'lang_email_conf'               => $LANG04[124],
        'lang_deleteaccount'            => $LANG04[156],
        'lang_deleteoption'             => $LANG04[156],
        'lang_button_delete'            => $LANG04[96],
    ));
    if (empty($uid) || $uid < 2 ) {
        $userform->set_var('lang_email_password',$LANG04[28]);
    }

    if (!empty ($uid) && ($uid > 1)) {
        $curtime = COM_getUserDateTimeFormat($U['regdate']);
        $lastlogin = DB_getItem ($_TABLES['userinfo'], 'lastlogin', "uid = '$uid'");
        $lasttime = COM_getUserDateTimeFormat ($lastlogin);
    } else {
        $U['uid'] = '';
        $uid = '';
        $curtime =  COM_getUserDateTimeFormat();
        $lastlogin = '';
        $lasttime = '';
        $A['status'] = USER_ACCOUNT_ACTIVE;
        $newuser = 1;
    }

    if ( $U['uid'] == '' ) {
        $userform->set_var('user_id',$LANG28[15]);
    } else {
        $userform->set_var('user_id',$U['uid']);
    }

    $userform->set_var('regdate_timestamp', $curtime[1]);
    $userform->set_var('user_regdate', $curtime[0]);
    if (empty ($lastlogin)) {
        $userform->set_var('user_lastlogin', $LANG28[36]);
    } else {
        $userform->set_var('user_lastlogin', $lasttime[0]);
    }

    $userform->set_var('user_name',$U['username']);
    $userform->set_var('fullname_value', @htmlspecialchars($U['fullname'],ENT_NOQUOTES,COM_getEncodingt()));

    $remote_user_display = 'none';
    $remote_user_checked = '';
    $pwd_disabled = '';
    $remote_user_edit = 0;

    if (($_CONF['user_login_method']['3rdparty'] ||
      $_CONF['user_login_method']['oauth'] ) ) { // && $U['account_type'] & REMOTE_USER /*$allow_remote_user */) {
        $modules = array();

        if ($U['account_type'] & REMOTE_USER) {
            $remote_user_checked = ' checked="checked"';
            $pwd_disabled = ' disabled="disabled"';
            $remote_user_display = '';
            if ( isset($U['uid']) && $U['uid'] > 2 ) {
                $remote_user_edit = 1;
            }
        }
        if ( $_CONF['user_login_method']['3rdparty'] ) {
            $modules = SEC_collectRemoteAuthenticationModules();
        }
        $service_select = '<select name="remoteservice" id="remoteservice"';
        if ( $remote_user_edit == 1 ) {
            $service_select .= ' disabled="disabled"';
        }
        $service_select .= '>' .LB;
        if ( count($modules) > 0 ) {
            foreach( $modules AS $service ) {
                $service_select .= '<option value="' . $service . '"'.($U['remoteservice'] == $service ? ' selected="selected"' : '') . '>' . $service . '</option>' . LB;
            }
        }
        if ( $_CONF['user_login_method']['oauth'] ) {
            $modules = SEC_collectRemoteOAuthModules();
            if ( count($modules) > 0 ) {
                foreach( $modules AS $service ) {
                    $service_select .= '<option value="' . 'oauth.'.$service . '"'.($U['remoteservice'] == 'oauth.'.$service ? ' selected="selected"' : '') . '>' . $service . '</option>' . LB;
                }
            }
        }
        $service_select .= '</select>'.LB;
        $userform->set_var('remoteusername',@htmlspecialchars($U['remoteusername'],ENT_NOQUOTES,COM_getEncodingt()));
        $userform->set_var('remoteservice_select',$service_select);
        $userform->set_var('remote_user_checked',$remote_user_checked);
        $userform->set_var('remote_user_display',$remote_user_display);
        $userform->set_var('remoteuserenable','1');
        $userform->set_var('lang_remoteuser',$LANG04[163]);
        $userform->set_var('lang_remoteusername',$LANG04[164]);
        $userform->set_var('lang_remoteservice',$LANG04[165]);
        $userform->set_var('lang_remoteuserdata',$LANG04[166]);
        $userform->set_var('remote_user_disabled',' disabled="disabled"');
        if ( !($U['account_type'] & LOCAL_USER) ) {
            $userform->set_var('pwd_disabled',$pwd_disabled);
        }
        if (!($U['account_type'] & REMOTE_USER)) {
            $userform->set_var('remoteuserenable','');
        }
    } else {
        $userform->set_var('remoteuserenable','');
        $userform->set_var('remoteusername','');
        $userform->set_var('remoteservice_select','');
        $userform->set_var('remote_user_checked',$remote_user_checked);
        $userform->set_var('remote_user_display',$remote_user_display);
        $userform->set_var('remote_user_disabled',' disabled="disabled"');
    }

    $userform->set_var(
        'cooktime_options',
        COM_optionList($_TABLES['cookiecodes'],'cc_value,cc_descr',$U['cookietimeout'], 0)
    );
    $userform->set_var('email_value', @htmlspecialchars ($U['email'],ENT_NOQUOTES,COM_getEncodingt()));

    $statusarray = array(USER_ACCOUNT_AWAITING_ACTIVATION   => $LANG28[43],
                         USER_ACCOUNT_AWAITING_VERIFICATION => $LANG28[16],
                         USER_ACCOUNT_ACTIVE                => $LANG28[45]
                   );

    $allow_ban = true;

    if (!empty($uid)) {
        if ($U['uid'] == $_USER['uid']) {
            $allow_ban = false; // do not allow to ban yourself
        } else if (SEC_inGroup('Root', $U['uid'])) { // editing a Root user?
            $count_root_sql = "SELECT COUNT(ug_uid) AS root_count FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = 1 GROUP BY ug_uid;";
            $count_root_result = DB_query($count_root_sql);
            $C = DB_fetchArray($count_root_result); // how many are left?
            if ($C['root_count'] < 2) {
                $allow_ban = false; // prevent banning the last root user
            }
        }
    }

    if ($allow_ban) {
        $statusarray[USER_ACCOUNT_DISABLED] = $LANG28[42];
    }

    if (($_CONF['usersubmission'] == 1) && !empty($uid)) {
        $statusarray[USER_ACCOUNT_AWAITING_APPROVAL] = $LANG28[44];
    }
    asort($statusarray);
    $options = '';
    foreach ($statusarray as $key => $value) {
        $options .= '<option value="' . $key . '"';
        if ($key == $U['status']) {
            $options .= ' selected="selected"';
        }
        $options .= '>' . $value . '</option>' . LB;
    }
    $userform->set_var('user_status_options', $options);

    if (
        isset($_CONF['enable_twofactor']) && $_CONF['enable_twofactor'] &&
        isset($U['tfa_enabled']) && $U['tfa_enabled']
    ) {
        $userform->set_var('twofactor',true);
        $userform->set_var(array(
            'lang_two_factor' => $LANG_TFA['two_factor'],
            'lang_disable_tfa' => $LANG_TFA['disable_tfa'],
        ));
    } else {
        $userform->unset_var('twofactor');
    }

    if (!empty($uid) && $uid > 1 ) {
        $userform->set_var('plugin_namepass_name',PLG_profileEdit($uid,'namepass','name'));
        $userform->set_var('plugin_namepass_pwdemail',PLG_profileEdit($uid,'namepass','pwdemail'));
    }

    $retval = $userform->finish ($userform->parse ('output', 'user'));
    return $retval;
}

function USER_groupPanel($U, $newuser = 0)
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_ACCESS, $LANG04, $LANG28;

    $form_url = '';

    $uid = $U['uid'];

    // set template
    $userform = new Template ($_CONF['path_layout'] . 'admin/user/');
    $userform->set_file('user','grouppanel.thtml');

    if (SEC_hasRights('group.edit')) {
        $userform->set_var('lang_securitygroups', $LANG_ACCESS['securitygroups']);
        $userform->set_var('lang_groupinstructions', $LANG_ACCESS['securitygroupsmsg']);
        if (!empty($uid)) {
            $usergroups = SEC_getUserGroups($uid);
            if (is_array($usergroups) && !empty($uid)) {
                $selected = implode(' ',$usergroups);
            } else {
                $selected = '';
            }
        } else {
            if ( isset($_POST['groups']) && is_array($_POST['groups']) ) {
                $selected = implode(' ',$_POST['groups']);
            } else {
                $selected  = DB_getItem($_TABLES['groups'],'grp_id',"grp_name='All Users'") . ' ';
                $selected .= DB_getItem($_TABLES['groups'],'grp_id',"grp_name='Logged-in Users'");
            }
        }
        if (SEC_inGroup('Root')) {
            $where = '1=1';
        } else {
            $thisUsersGroups = SEC_getUserGroups ();
            $remoteGroup = DB_getItem ($_TABLES['groups'], 'grp_id',"grp_name='Remote Users'");
            if (!empty ($remoteGroup)) {
                $thisUsersGroups[] = $remoteGroup;
            }
            $where = 'grp_id IN (' . implode (',', $thisUsersGroups) . ')';
        }

        $header_arr = array(
                        array('text' => $LANG28[86], 'field' => 'checkbox', 'sort' => false, 'align' => 'center'),
                        array('text' => $LANG_ACCESS['groupname'], 'field' => 'grp_name', 'sort' => true),
                        array('text' => $LANG_ACCESS['description'], 'field' => 'grp_descr', 'sort' => true)
        );
        $defsort_arr = array('field' => 'grp_name', 'direction' => 'asc');

        $text_arr = array('has_menu' => false,
                          'title' => '', 'instructions' => '',
                          'icon' => '', 'form_url' => $form_url,
                          'inline' => true
        );

        $sql = "SELECT grp_id, grp_name, grp_descr, grp_gl_core, " . $U['account_type'] . " AS account_type FROM {$_TABLES['groups']} WHERE " . $where;
        $query_arr = array('table' => 'groups',
                           'sql' => $sql,
                           'query_fields' => array('grp_name'),
                           'default_filter' => '',
                           'query' => '',
                           'query_limit' => 0
        );

        $selArray = explode(' ',$selected);
        $al_selected[0] = $uid;
        $al_selected[1] = $selArray;

        $groupoptions = ADMIN_list('usergroups',
                                   'USER_getGroupListField',
                                   $header_arr, $text_arr, $query_arr,
                                   $defsort_arr, '', $al_selected);

        $userform->set_var('group_options', $groupoptions);

        $userform->parse('group_edit', 'groupedit', true);
    }
    $retval = $userform->finish ($userform->parse ('output', 'user'));
    return $retval;
}


function USER_userinfoPanel($U, $newuser = 0)
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG04;

    $uid = $U['uid'];

    // set template
    $userform = new Template ($_CONF['path_layout'] . 'admin/user/');
    $userform->set_file('user','userinfopanel.thtml');

    $userform->set_var(array(
        'lang_personal_info_legend' => $LANG04[130],
        'lang_userinfo_help_title'  => $LANG04[148],
        'lang_userinfo_help'        => $LANG04[149],
        'lang_homepage'             => $LANG04[6],
        'lang_location'             => $LANG04[106],
        'lang_signature'            => $LANG04[32],
        'lang_about'                => $LANG04[7],
        'lang_pgpkey'               => $LANG04[8],
        'lang_social_follow'        => $LANG04[198],
        'lang_social_info'          => $LANG04[199],
        'lang_social_service'       => $LANG04[200],
        'lang_social_username'      => $LANG04[201],
    ));

    $follow_me = Social::followMeProfile( $uid );
    if ( is_array($follow_me) && count($follow_me) > 0 ) {
        $userform->set_block('user','social_links','sl');
        $userform->set_var('social_followme_enabled',true);
        foreach ( $follow_me AS $service ) {
            $userform->set_var('service_display_name', $service['service_display_name']);
            $userform->set_var('service',$service['service']);
            $userform->set_var('service_username',$service['service_username']);
            $userform->parse('sl','social_links',true);
        }
    } else {
        $userform->unset_var('social_followme_enabled');
    }

    if ( $_CONF['allow_user_photo'] == 1 ) {
        $userform->set_var('lang_userphoto',$LANG04[77]);
    }

    $userform->set_var('homepage_value',@htmlspecialchars (COM_killJS ($U['homepage']),ENT_NOQUOTES,COM_getEncodingt()));
    $userform->set_var('location_value',@htmlspecialchars (strip_tags ($U['location']),ENT_NOQUOTES,COM_getEncodingt()));
    $userform->set_var('signature_value',@htmlspecialchars ($U['sig'],ENT_NOQUOTES,COM_getEncodingt()));
    $userform->set_var('about_value', @htmlspecialchars ($U['about'],ENT_NOQUOTES,COM_getEncodingt()));
    $userform->set_var('pgpkey_value', @htmlspecialchars ($U['pgpkey'],ENT_NOQUOTES,COM_getEncodingt()));

    if ($_CONF['allow_user_photo'] == 1) {
        if ( !empty($uid) && $uid > 1 ) {
            $photo = USER_getPhoto ($uid, $U['photo'], $U['email'], -1);
            if (!empty($photo)) {
                $userform->set_var('display_photo', $photo);
                if (!empty($U['photo'])) {
                    $userform->set_var('lang_delete', $LANG04[79]);
                }
                $userform->set_var('display_photo', $photo);
            }
        } else {
            $userform->set_var('display_photo', '' );
        }
    }
    if (!empty($uid) && $uid > 1 ) {
        $userform->set_var('plugin_userinfo_personalinfo',PLG_profileEdit($uid,'userinfo','personalinfo'));
        $userform->set_var('plugin_userinfo',PLG_profileEdit($uid,'userinfo'));
        if ($_CONF['custom_registration'] && function_exists('CUSTOM_userEdit')) {
            $userform->set_var('customfields', CUSTOM_userEdit($uid));
        }
    }
    $retval = $userform->finish ($userform->parse ('output', 'user'));
    return $retval;
}

function USER_subscriptionPanel($U)
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG04,
           $LANG_confignames,  $LANG_configselects, $LANG_configSelect;

    $uid = $U['uid'];

    // set template
    $preferences = new Template ($_CONF['path_layout'] . 'admin/user/');
    $preferences->set_file ('subscriptions','subscriptionpanel.thtml');

    // subscription block
    $csscounter = 1;
    $res = DB_query("SELECT * FROM {$_TABLES['subscriptions']} WHERE uid=".(int) $uid . " ORDER BY type,category ASC");
    $preferences->set_block('subscriptions', 'subrows', 'srow');
    while ( ($S = DB_fetchArray($res) ) != NULL ) {
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
    return $preferences->finish ($preferences->parse ('output', 'subscriptions_block'));
}


function USER_layoutPanel($U, $newuser = 0)
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG04,
           $LANG_confignames,  $LANG_configselects,$LANG_configSelect;

    $uid = $U['uid'];

    // set template
    $userform = new Template ($_CONF['path_layout'] . 'admin/user/');
    $userform->set_file('user','layoutpanel.thtml');

    $userform->set_var('lang_misc_title', $LANG04[138]);
    $userform->set_var('lang_misc_help_title', $LANG04[139]);
    $userform->set_var('lang_misc_help', $LANG04[140]);
    $userform->set_var('lang_language', $LANG04[73]);
    $userform->set_var('lang_theme', $LANG04[72]);
    $userform->set_var('lang_noicons', $LANG04[40]);
    $userform->set_var('lang_maxstories', $LANG04[43]);
    $userform->set_var('lang_timezone', $LANG04[158]);
    $userform->set_var('lang_dateformat', $LANG04[42]);

    $userform->set_var('lang_comment_title', $LANG04[133]);
    $userform->set_var('lang_comment_help_title', $LANG04[134]);
    $userform->set_var('lang_comment_help', $LANG04[135]);
    $userform->set_var('lang_displaymode', $LANG04[57]);
    $userform->set_var('lang_sortorder', $LANG04[58]);
    $userform->set_var('lang_commentlimit', $LANG04[59]);

    if ($_CONF['allow_user_language'] == 1) {
        if (empty ($U['language'])) {
            $userlang = $_CONF['language'];
        } else {
            $userlang = $U['language'];
        }

        // Get available languages
        $language = MBYTE_languageList ($_CONF['default_charset']);

        $has_valid_language = count (array_keys ($language, $userlang));
        if ($has_valid_language == 0) {
            // The user's preferred language is no longer available.
            // We have a problem now, since we've overwritten $_CONF['language']
            // with the user's preferred language ($U['language']) and
            // therefore don't know what the system's default language is.
            // So we'll try to find a similar language. If that doesn't help,
            // the dropdown will default to the first language in the list ...
            $tmp = explode ('_', $userlang);
            $similarLang = $tmp[0];
        }

        // build language select
        $options = '';
        foreach ($language as $langFile => $langName) {
            $options .= '<option value="' . $langFile . '"';
            if (
                ($langFile == $userlang) ||
                (
                    ($has_valid_language == 0) && (strpos ($langFile, $similarLang) === 0)
                )
            ) {
                $options .= ' selected="selected"';
                $has_valid_language = 1;
            } else if ($userlang == $langFile) {
                $options .= ' selected="selected"';
            }

            $options .= '>' . $langName . '</option>' . LB;
        }
        $userform->set_var('language_options', $options);
    } else {
        $userform->set_var('language_name', $_CONF['language']);
    }

    if ($_CONF['allow_user_themes'] == 1) {
        if (empty ($U['theme'])) {
            $usertheme = $_CONF['theme'];
        } else {
            $usertheme = $U['theme'];
        }
        $themeFiles = COM_getThemes ();
        usort ($themeFiles,function ($a,$b) { return strcasecmp($a,$b); } );

        $options = '';
        foreach ($themeFiles as $theme) {
            $options .= '<option value="' . $theme . '"';
            if ($usertheme == $theme) {
                $options .= ' selected="selected"';
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
            $options .= '>' . implode (' ', $bwords) . '</option>' . LB;
        }
        $userform->set_var('theme_options', $options);
    } else {
        $userform->set_var('theme_name', $_CONF['theme']);
    }

    if ($U['noicons'] == '1') {
        $userform->set_var('noicons_checked', 'checked="checked"');
    } else {
        $userform->set_var('noicons_checked', '');
    }

    $userform->set_var('maxstories_value', $U['maxstories']);

    // Timezone

    if ( isset($U['tzid']) ) {
        $timezone = $U['tzid'];
    } else {
        $timezone = $_CONF['timezone'];
    }
    $userform->set_var('timezone_options', Date::getTimeZoneOptions($timezone));

    /*$selection = '<select id="dfid" name="dfid">' . LB
               . COM_optionList ($_TABLES['dateformats'], 'dfid,description',
               $U['dfid']) . '</select>';*/
    $userform->set_var(
        'dateformat_options',
        COM_optionList ($_TABLES['dateformats'], 'dfid,description', $U['dfid'])
    );

    if (!empty($uid) && $uid > 1 ) {
        $userform->set_var('plugin_layout_display',PLG_profileEdit($uid,'layout','display'));
    }

    // comment preferences block
    if ( !empty($uid) && $uid > 1 ) {
        $result = DB_query("SELECT commentmode,commentorder,commentlimit FROM {$_TABLES['usercomment']} WHERE uid = $uid");
        $C = DB_fetchArray ($result);

        if (empty ($C['commentmode'])) {
            $C['commentmode'] = $_CONF['comment_mode'];
        }
        if (empty ($C['commentorder'])) $C['commentorder'] = 0;
        if (empty ($C['commentlimit'])) $C['commentlimit'] = 100;
    } else {
        $C['commentmode'] = $_CONF['comment_mode'];
        $C['commentorder'] = 0;
        $C['commentlimit'] = 100;
    }
    $userform->set_var(
        'displaymode_options',
        COM_optionList ($_TABLES['commentmodes'], 'mode,name', $C['commentmode'])
    );


    $userform->set_var(
        'sortorder_options',
        COM_optionList ($_TABLES['sortcodes'], 'code,name', $C['commentorder'])
    );

    $userform->set_var('commentlimit_value', $U['commentlimit']);
    if (!empty($uid) && $uid > 1 ) {
        $userform->set_var('plugin_layout_comment',PLG_profileEdit($uid,'layout','comment'));
        $userform->set_var('plugin_layout',PLG_profileEdit($uid,'layout'));
    }

    $retval = $userform->finish ($userform->parse ('output', 'user'));
    return $retval;
}

function USER_contentPanel($U, $newuser = 0)
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG04,
           $LANG_confignames;

    $uid = $U['uid'];

    // set template
    $userform = new Template ($_CONF['path_layout'] . 'admin/user/');
    $userform->set_file('user','contentpanel.thtml');

    $userform->set_var('lang_topics', $LANG04[48]);
    $userform->set_var('lang_authors', $LANG04[56]);

    $userform->set_var('lang_digest_top_header', $LANG04[131]);
    $userform->set_var('lang_digest_help_header', $LANG04[132]);
    $userform->set_var('lang_emailedtopics', $LANG04[76]);

    $permissions = '';

    // daily digest block
    if ($_CONF['emailstories'] == 1) {
        if ( !empty($uid) && $uid > 1 ) {
            $user_etids = DB_getItem ($_TABLES['userindex'], 'etids',"uid = $uid");
        } else {
            $user_etids = '-';
        }
        if (empty ($user_etids)) { // an empty string now means "all topics"
            $user_etids = USER_buildTopicList();
        } elseif ($user_etids == '-') { // this means "no topics"
            $user_etids = '';
        }
        $tmp = COM_checkList($_TABLES['topics'], 'tid,topic', $permissions, $user_etids, 'dgtopics');
        $userform->set_var('email_topic_checklist',str_replace($_TABLES['topics'], 'etids', $tmp));
        if (!empty($uid) && $uid > 1 ) {
            $userform->set_var('plugin_content_digest',PLG_profileEdit($uid,'content','digest'));
        }
    } else {
        $userform->set_var('email_topic_checklist', '');
    }

    if (!empty($uid) && $uid > 1 ) {
        $userform->set_var('plugin_content',PLG_profileEdit($uid,'content'));
    }

    $retval = $userform->finish ($userform->parse ('output', 'user'));
    return $retval;
}

function USER_privacyPanel($U, $newuser = 0)
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG04,
           $LANG_confignames;

    $uid = $U['uid'];

    // set template
    $userform = new Template ($_CONF['path_layout'] . 'admin/user/');
    $userform->set_file('user','privacypanel.thtml');

    $userform->set_var('lang_privacy_title', $LANG04[141]);
    $userform->set_var('lang_privacy_help_title', $LANG04[141]);
    $userform->set_var('lang_privacy_help', $LANG04[142]);
    $userform->set_var('lang_emailfromadmin', $LANG04[100]);
    $userform->set_var('lang_emailfromadmin_text', $LANG04[101]);
    $userform->set_var('lang_emailfromuser', $LANG04[102]);
    $userform->set_var('lang_emailfromuser_text', $LANG04[103]);
    $userform->set_var('lang_showonline', $LANG04[104]);
    $userform->set_var('lang_showonline_text', $LANG04[105]);

    if ($U['emailfromadmin'] == 1) {
        $userform->set_var('emailfromadmin_checked', 'checked="checked"');
    } else {
        $userform->set_var('emailfromadmin_checked', '');
    }
    if ($U['emailfromuser'] == 1) {
        $userform->set_var('emailfromuser_checked', 'checked="checked"');
    } else {
        $userform->set_var('emailfromuser_checked', '');
    }
    if ($U['showonline'] == 1) {
        $userform->set_var('showonline_checked', 'checked="checked"');
    } else {
        $userform->set_var('showonline_checked', '');
    }
    if (!empty($uid) && $uid > 1 ) {
        $userform->set_var('plugin_privacy_privacy',PLG_profileEdit($uid,'privacy','privacy'));
        $userform->set_var('plugin_privacy',PLG_profileEdit($uid,'privacy'));
    }

    $retval = $userform->finish ($userform->parse ('output', 'user'));
    return $retval;
}


/**
* Get a list (actually an array) of all groups this group belongs to.
*
* @param    int     $basegroup  id of group
* @return   array               array of all groups $basegroup belongs to
*
*/
function USER_getGroupList($basegroup)
{
    global $_TABLES;

    $to_check = array ();
    array_push ($to_check, $basegroup);

    $checked = array ();

    while (count($to_check) > 0) {
        $thisgroup = array_pop ($to_check);
        if ($thisgroup > 0) {
            $result = DB_query ("SELECT ug_grp_id FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $thisgroup");
            $numGroups = DB_numRows ($result);
            for ($i = 0; $i < $numGroups; $i++) {
                $A = DB_fetchArray ($result);
                if (!in_array ($A['ug_grp_id'], $checked)) {
                    if (!in_array ($A['ug_grp_id'], $to_check)) {
                        array_push ($to_check, $A['ug_grp_id']);
                    }
                }
            }
            $checked[] = $thisgroup;
        }
    }

    return $checked;
}

/**
* Get a list of all groups for select/option
*
* @param    int     $grp_id     currently selected group
* @return   html                html for <option></option> list
*
*/
function USER_groupSelectList( $sel_grp_id = 0 )
{
    global $_TABLES;

    $retval = '';
    $sql = 'SELECT grp_id,grp_name FROM ' . $_TABLES['groups'] . ' WHERE 1=1';
    $result= DB_query( $sql );
    $rows = DB_numRows( $result );
    for( $i = 1; $i <= $rows; $i++ )  {
        $A = DB_fetchArray ($result);
        if ($A['grp_name'] <> 'Logged-in Users' && $A['grp_name'] <> 'Non-Logged-in Users') { // don't offer Logged-In users as an option
            $retval .= '<option value="' . $A['grp_id'] . '"';
            if ($sel_grp_id == $A['grp_id']) {
                $retval .= ' selected="selected"';
            }
            $retval .= '>' . ucwords($A['grp_name']) . '</option>' . LB;
        }
    }
    return $retval;
}


/**
 * returns field data for list of groups in the admin user editor group panel
 *
 */
function USER_getGroupListField($fieldname, $fieldvalue, $A, $icon_arr, $al_selected = '')
{
    global $_TABLES, $thisUsersGroups;

    $retval = false;

    if(! is_array($thisUsersGroups)) {
        $thisUsersGroups = SEC_getUserGroups();
    }
    if ( is_array($al_selected) ) {
        $selected = $al_selected[1];
        $uid      = (int) $al_selected[0];
    }

    if (SEC_inGroup('Root') || in_array($A['grp_id'], $thisUsersGroups ) ||
          SEC_groupIsRemoteUserAndHaveAccess($A['grp_id'], $thisUsersGroups)) {
        switch($fieldname) {
        case 'checkbox':
            $checked = '';
            if (is_array($selected) && in_array($A['grp_id'], $selected)) {
                $checked = ' checked="checked"';
                if ( $uid != '' && $uid > 0 ) {
                    $tresult = DB_query("SELECT COUNT(*) AS count FROM {$_TABLES['group_assignments']} WHERE ug_uid=".$uid." AND ug_main_grp_id=".$A['grp_id']);
                    list($gcount) = DB_fetchArray($tresult);
                    if ( $gcount < 1 ) {
                        $checked = ' checked="checked" disabled="disabled"';
                    }
                }
            }
            if (($A['grp_name'] == 'All Users') ||
                ($A['grp_name'] == 'Logged-in Users') ||
                ($A['grp_name'] == 'Non-Logged-in Users') ||
                ($A['grp_name'] == 'Remote Users')
            ) {
                $retval = '<input type="checkbox" disabled="disabled"'
                        . $checked . '/>'
                        . '<input type="hidden" name="groups[]" value="'
                        . $A['grp_id'] . '"' . $checked . '/>';
            } else {
                if ( $A['grp_gl_core'] > 0 && !($A['account_type'] & LOCAL_USER) /*!SEC_isLocalUser($uid) */ ) {
                    $checked = ' disabled="disabled"';
                }
                $retval = '<input type="checkbox" name="groups[]" value="'
                        . $A['grp_id'] . '"' . $checked . '/>';
            }
            break;

        case 'grp_name':
            $retval = ucwords($fieldvalue);
            break;

        default:
            $retval = $fieldvalue;
            break;
        }
    }

    return $retval;
}

/**
 * returns field data for the user administration panel list
 *
 */
function USER_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG04, $LANG28, $_IMAGE_TYPE;

    $retval = '';

    $dt = new Date('now',$_USER['tzid']);

    switch ($fieldname) {
        case 'edit':
            $args['attr'] = array('title' => $LANG_ADMIN['edit']);
            $args['url'] = $_CONF['site_admin_url'].'/user.php?edit=x&amp;uid='.$A['uid'];
            $retval = FieldList::edit($args);
            break;

        case 'username':
            $attr['title'] = $LANG28[108];
            $url = $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' .  $A['uid'];
            $retval = FieldList::editusers(
                array(
                    'url' => $url,
                    'attr' => $attr,
                )
            );
            $retval .= '&nbsp;&nbsp;';
            $retval .= COM_createLink($fieldvalue, $url, $attr);
            if (SEC_inGroup('Root',$A['uid'])) {
                $retval .= '&nbsp;' . FieldList::rootUser(array(
                    'attr' => array(
                        'title' => 'Root User',
                    )
                ));
            }

            $photoico = '';
            if (!empty ($A['photo'])) {
                $retval .= '&nbsp;&nbsp;' . FieldList::userPhoto();

            }
            break;

        case 'fullname':
            $retval = COM_truncate($fieldvalue, 24, ' ...', true);
            break;

        case 'status':
            $status = $A['status'];
            switch ($status) {
                case 0:
                    $retval = $LANG28[42];
                    break;
                case 1:
                    $retval = $LANG28[14];
                    break;
                case 2:
                    $retval = $LANG28[106];
                    break;
                case 3:
                    $retval = $LANG28[45];
                    break;
                case 4:
                    $retval = $LANG28[107];
                    break;
            }
            break;

        case 'lastlogin':
            if ($fieldvalue < 1) {
                // if the user never logged in, show the registration date
                $dt->setTimestamp(strtotime($A['regdate']));
                $regdate = $dt->format($_CONF['shortdate'],true);
                $retval = "{$LANG28[36]} ({$LANG28[53]}: $regdate)";
            } else {
                $dt->setTimestamp($fieldvalue);
                $retval = $dt->format($_CONF['shortdate'],true);
            }
            break;

        case 'lastlogin_short':
            if ($fieldvalue < 1) {
                // if the user never logged in, show the registration date
                $dt->setTimestamp(strtotime($A['regdate']));
                $regdate = $dt->format($_CONF['shortdate'],true);
                $retval = "({$LANG28[36]})";
            } else {
                $dt->setTimestamp($fieldvalue);
                $retval = $dt->format($_CONF['shortdate'],true);
            }
            break;

        case 'online_days':
            if ($fieldvalue < 0){
                // users that never logged in, would have a negative online days
                $retval = "N/A";
            } else {
                $retval = $fieldvalue;
            }
            break;

        case 'phantom_date':

        case 'offline_months':
            $fieldvalue = intval($fieldvalue);
            $retval = COM_numberFormat(round($fieldvalue / 2592000));
            break;

        case 'online_hours':
            $fieldvalue = intval($fieldvalue);
            $retval = COM_numberFormat(round($fieldvalue / 3600, 3));
            break;

        case 'regdate':
            $dt->setTimestamp(strtotime($fieldvalue));
            $retval = $dt->format($_CONF['shortdate'],true);
            break;

        case $_TABLES['users'] . '.uid':
            $retval = $A['uid'];
            break;

        case 'email':
            if (COM_isEmail($fieldvalue)) {
                $url = 'mailto:'.$fieldvalue;
                $retval = FieldList::email(array(
                    'url' => 'mailto: ' . $fieldvalue . '&nbsp;&nbsp;',
                ));
                $attr['title'] = $LANG28[99];
                $url = $_CONF['site_admin_url'] . '/mail.php?uid=' . $A['uid'];
                $retval .= COM_createLink($fieldvalue, $url, $attr);
            } else {
                $retval .= $fieldvalue; //COM_createLink($fieldvalue, $url, $attr);
            }
            break;

        case 'delete':
            $retval = '';
            $args = array();
            $args['attr'] = array(
                'title' => $LANG_ADMIN['delete'],
                'onclick' => 'return doubleconfirm(\'' . $LANG28[104] . '\',\'' . $LANG28[109] . '\');',
            );
            $args['url'] = $_CONF['site_admin_url'] . '/user.php'
                    . '?delete=x&amp;uid=' . $A['uid'] . '&amp;' . CSRF_TOKEN . '=' . $token;

            $retval = FieldList::delete($args);
            break;

        default:
            $retval = $fieldvalue;
            break;
    }

    if (isset($A['status']) && ($A['status'] == USER_ACCOUNT_DISABLED)) {
        if (($fieldname != 'edit') && ($fieldname != 'username')) {
            $retval = sprintf ('<span class="strike" title="%s">%s</span>',
                               $LANG28[42], $retval);
        }
    }

    return $retval;
}

/**
 *  generates a list of users for the user administration panel
 *
 */
function USER_list($grp_id)
{
    global $_CONF, $_TABLES, $LANG_ACCESS, $LANG_ADMIN, $LANG04, $LANG28, $_IMAGE_TYPE;

    $retval = '';
    $group_all = '';

    // no grp_id defaults to all users
    if( $grp_id == 0) {
        $grp_id = 2;
    } else {
        $result = DB_query("SELECT grp_id,grp_name FROM {$_TABLES['groups']} WHERE grp_id = '" . $grp_id . "'");
        if (DB_numRows($result) == 0) {
            $grp_id = 2;
        }
    }

    $filter = $LANG28[101]
        . ': <select name="grp_id" onchange="this.form.submit()">'
        . $group_all . USER_groupSelectList($grp_id) . '</select>';

    if ($_CONF['lastlogin']) {
        $login_text = $LANG28[41];
        $login_field = 'lastlogin';
    } else {
        $login_text = $LANG28[40];
        $login_field = 'regdate';
    }

    $header_arr = array(      # display 'text' and use table field 'field'
                    array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center', 'width' => '35px'),
                    array('text' => $LANG28[37], 'field' => $_TABLES['users'] . '.uid', 'sort' => true, 'align' => 'center'),
                    array('text' => $LANG28[3], 'field' => 'username', 'sort' => true),
                    array('text' => $LANG28[4], 'field' => 'fullname', 'sort' => true),
                    array('text' => $LANG28[105], 'field' => 'status', 'sort' => true, 'align' => 'center'),
                    array('text' => $login_text, 'field' => $login_field, 'sort' => true, 'align' => 'center'),
                    array('text' => $LANG28[7], 'field' => 'email', 'sort' => true),
                    array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center', 'width' => '35px')
    );

    if ($_CONF['user_login_method']['oauth'] ||
        $_CONF['user_login_method']['3rdparty']) {
        $header_arr[] = array('text' => $LANG04[121], 'field' => 'remoteservice', 'sort' => true);
    }

    $defsort_arr = array('field'     => $_TABLES['users'] . '.uid',
                         'direction' => 'ASC');

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/user.php',
              'text' => $LANG_ADMIN['admin_users'],'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/user.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'] . '/user.php?import=x',
              'text' => $LANG28[23]),
        array('url' => $_CONF['site_admin_url'] . '/user.php?batchadmin=x',
              'text' => $LANG28[54]),
        array('url' => $_CONF['site_admin_url'] . '/prefeditor.php',
              'text' => $LANG28[95]),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG28[11], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG28[12],
        $_CONF['layout_url'] . '/images/icons/user.' . $_IMAGE_TYPE
    );

    $text_arr = array(
        'form_url'      => $_CONF['site_admin_url'] . '/user.php',
        'help_url'      => '',
        'has_search'    => true,
        'has_limit'     => true,
        'has_paging'    => true,
    );

    $join_userinfo   = '';
    $select_userinfo = '';
    if ($_CONF['lastlogin']) {
        $join_userinfo .= "LEFT JOIN {$_TABLES['userinfo']} ON {$_TABLES['users']}.uid={$_TABLES['userinfo']}.uid ";
        $select_userinfo .= ",lastlogin";
    }
    if ($_CONF['user_login_method']['oauth'] ||
        $_CONF['user_login_method']['3rdparty']) {
        $select_userinfo .= ',remoteservice';
    }
    if ($grp_id > 0 && ($grp_id != 2 && $grp_id != 13)) {
        $groups = USER_getGroupList ($grp_id);
        $text_arr['form_url'] .= '?grp_id=' . $grp_id;
        $groupList = implode (',', $groups);
        $sql = "SELECT DISTINCT {$_TABLES['users']}.uid,username,fullname,email,photo,regdate,status$select_userinfo "
              ."FROM {$_TABLES['group_assignments']},{$_TABLES['users']} $join_userinfo "
              ."WHERE {$_TABLES['users']}.uid > 1 "
              ."AND {$_TABLES['users']}.uid = {$_TABLES['group_assignments']}.ug_uid "
              ."AND ({$_TABLES['group_assignments']}.ug_main_grp_id IN ({$groupList}))";
    } else {
        $sql = "SELECT {$_TABLES['users']}.uid,username,fullname,email,photo,regdate,status$select_userinfo "
             . "FROM {$_TABLES['users']} $join_userinfo WHERE 1=1";
    }

    $query_arr = array('table' => 'users',
                       'sql' => $sql,
                       'query_fields' => array('username', 'email', 'fullname'),
                       'default_filter' => "AND {$_TABLES['users']}.uid > 1");

    $token = SEC_createToken();

    $retval .= ADMIN_list('user', 'USER_getListField', $header_arr,
                          $text_arr, $query_arr, $defsort_arr, $filter, $token);
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}


/**
* Saves user to the database
*
* @param    int     $uid            user id
* @return   string                  HTML redirect or error message
*
*/
function USER_save($uid)
{
    global $_CONF, $_TABLES, $_USER, $LANG28, $_USER_VERBOSE;

    $retval = '';
    $sql = '';
    $userChanged = false;

    $db = Database::getInstance();

    if ($_USER_VERBOSE) Log::write('system',Log::DEBUG,"**** entering USER_save()****");
    if ($_USER_VERBOSE) Log::write('system',Log::DEBUG,"group size at beginning = " . sizeof($groups));

    $uid            = COM_applyFilter($_POST['uid'],true);
    if ( $uid == 0 ) {
        $uid = '';
    }
    $regdate        = COM_applyFilter($_POST['regdate'],true);
    $username       = trim($_POST['new_username']);
    $fullname       = COM_truncate(trim(USER_sanitizeName($_POST['fullname'])),80);
    $userstatus     = COM_applyFilter($_POST['userstatus'],true);
    $oldstatus      = COM_applyFilter($_POST['oldstatus'],true);
    $passwd         = (isset($_POST['newp'])) ? trim($_POST['newp']) : '';
    $passwd_conf    = (isset($_POST['newp_conf']) ) ? trim($_POST['newp_conf']) : '';
    $cooktime       = COM_applyFilter($_POST['cooktime'],true);
    $email          = trim($_POST['email']);
    $email_conf     = trim($_POST['email_conf']);
    $groups         = $_POST['groups'];
    $homepage       = trim($_POST['homepage']);
    $location       = strip_tags(trim($_POST['location']));
    $photo          = isset($_POST['photo']) ? $_POST['photo'] : '';
    $delete_photo   = (isset($_POST['delete_photo']) && $_POST['delete_photo'] == 'on') ? 1 : 0;
    $sig            = trim($_POST['sig']);
    $about          = trim($_POST['about']);
    $pgpkey         = trim($_POST['pgpkey']);
    $language       = isset($_POST['language']) ? trim(COM_applyFilter($_POST['language'])) : '';
    $theme          = isset($_POST['theme']) ? trim(COM_applyFilter($_POST['theme'])) : '';
    $maxstories     = COM_applyFilter($_POST['maxstories'],true);
    $tzid           = COM_applyFilter($_POST['tzid']);
    $dfid           = COM_applyFilter($_POST['dfid'],true);
    $commentmode    = COM_applyFilter($_POST['commentmode']);
    $commentorder   = (isset($_POST['commentorder']) && $_POST['commentorder'] == 'DESC') ? 'DESC' : 'ASC';
    $commentlimit   = COM_applyFilter($_POST['commentlimit'],true);
    $emailfromuser  = (isset($_POST['emailfromuser']) && $_POST['emailfromuser'] == 'on') ? 1 : 0;
    $emailfromadmin = (isset($_POST['emailfromadmin']) && $_POST['emailfromadmin'] == 'on') ? 1 : 0;
    $noicons        = (isset($_POST['noicons']) && $_POST['noicons'] == 'on') ? 1 : 0;
    $showonline     = (isset($_POST['showonline']) && $_POST['showonline'] == 'on') ? 1 : 0;
    $topic_order    = (isset($_POST['topic_order']) && $_POST['topic_order'] == 'ASC') ? 'ASC' : 'DESC';
    $maxstories     = COM_applyFilter($_POST['maxstories'],true);
    $newuser        = COM_applyFilter($_POST['newuser'],true);
    $remoteuser     = (isset($_POST['remoteuser']) && $_POST['remoteuser'] == 'on') ? 1 : 0;
    $remoteusername = (isset($_POST['remoteusername'])) ? strip_tags(trim($_POST['remoteusername'] ) ): '';
    $remoteservice  = (isset($_POST['remoteservice'])) ? COM_applyFilter($_POST['remoteservice']) : '';

    $social_services = Social::followMeProfile( $uid );
    foreach ( $social_services AS $service ) {
        $service_input = $service['service'].'_username';
        $_POST[$service_input] = strip_tags($_POST[$service_input]);
    }

    if ( $uid == 1 ) {
        return USER_list();
    }

    if ( $uid == '' || $uid < 2 || $newuser == 1 ) {
        if (empty($passwd) && $remoteuser == 0) {
            return USER_edit($uid,504);
        }
        if (empty($email) ) {
            return USER_edit($uid,505);
        }
    }
    if ( $username == '') {
        return USER_edit($uid,506);
    }
    if ( !USER_validateUsername($username,1)) {
        return USER_edit($uid,512);
    }
    if ( $email == '' ) {
        return USER_edit($uid,507);
    }
    if ($passwd != $passwd_conf && $remoteuser == 0) { // passwords don't match
        return USER_edit($uid, 67);
    }
    if ($email != $email_conf) {
        return USER_edit($uid,508);
    }
    // remote user checks
    if ( $remoteuser == 1 ) {
        if ( $remoteusername == '' ) {
            return USER_edit($uid,513);
        }
        if ( $remoteservice == '' ) {
            return USER_edit($uid,514);
        }
    }

    $validEmail = true;
    if (empty($username)) {
        $validEmail = false;
    } elseif (empty($email)) {
        if (empty($uid)) {
            $validEmail = false;
        } else {
            $ws_user = DB_getItem($_TABLES['users'], 'remoteservice',"uid = ".intval($uid));
            if (empty($ws_user)) {
                $validEmail = false;
            }
        }
    }
    if ( $validEmail ) {
        if (!empty($email) && !COM_isEmail($email)) {
           return USER_edit($uid, 52);
        }
        $uname = DB_escapeString ($username);
        if (empty ($uid)) {
            $ucount = DB_getItem ($_TABLES['users'], 'COUNT(*)',
                                  "username = '$uname'");
        } else {
            $uservice = DB_getItem ($_TABLES['users'], 'remoteservice', "uid = $uid");
            if ($uservice != '') {
                $uservice = DB_escapeString($uservice);
                $ucount = DB_getItem ($_TABLES['users'], 'COUNT(*)',
                            "username = '$uname' AND uid <> $uid AND remoteservice = '$uservice'");
            } else {
                $ucount = DB_getItem ($_TABLES['users'], 'COUNT(*)',
                                  "username = '$uname' AND uid <> $uid AND (remoteservice = '' OR remoteservice IS NULL)");
            }
        }
        if ($ucount > 0) {
            // Admin just changed a user's username to one that already exists
            return USER_edit($uid, 51);
        }

        $emailaddr = DB_escapeString($email);
        $exclude_remote = " AND (remoteservice IS NULL OR remoteservice = '')";
        if (empty($uid)) {
            $ucount = DB_getItem($_TABLES['users'], 'COUNT(*)',
                                 "email = '$emailaddr'" . $exclude_remote);
        } else {
            $old_email = DB_getItem($_TABLES['users'], 'email', "uid = $uid");
            if ($old_email == $email) {
                // email address didn't change so don't care
                $ucount = 0;
            } else {
                $ucount = DB_getItem($_TABLES['users'], 'COUNT(*)',
                                     "email = '$emailaddr' AND uid <> $uid"
                                     . $exclude_remote);
            }
        }
        if ($ucount > 0) {
            // Admin just changed a user's email to one that already exists
            return USER_edit($uid, 56);
        }

        if ($_CONF['custom_registration'] && function_exists('CUSTOM_userCheck')) {
            $ret = CUSTOM_userCheck($username, $email);
            if (! empty($ret)) {
                // need a numeric return value - otherwise use default message
                if (! is_numeric($ret['number'])) {
                    $ret['number'] = 97;
                }
                return USER_edit($uid, $ret['number']);
            }
        }

        // Let plugins have a chance to decide what to do before saving the user, return errors.
        $msg = PLG_itemPreSave ('useredit', $username);
        if (!empty ($msg)) {
            // need a numeric return value - otherwise use default message
            if (! is_numeric($msg)) {
                $msg = 97;
            }
            return USER_edit($uid, $msg);
        }

        if (empty ($uid) || !empty ($passwd)) {
            $passwd2 = SEC_encryptPassword($passwd);
        } else {
            $passwd2 = DB_getItem ($_TABLES['users'], 'passwd', "uid = $uid");
        }
// do we need to create the user?
        if (empty ($uid)) {
            if (empty ($passwd)) {
                // no password? create one ...
                $passwd = USER_createPassword (8);
                $passwd2 = SEC_encryptPassword($passwd);
            }
            if ( $remoteuser == 1 ) {
                $uid = USER_createAccount($username, $email, '',
                           $fullname, '', $remoteusername,
                           $remoteservice,1);
            } else {
                $uid = USER_createAccount ($username, $email, $passwd2, $fullname,
                                           $homepage,'','',1);
            }
            if ($uid > 1) {
                DB_query("UPDATE {$_TABLES['users']} SET status = $userstatus WHERE uid = $uid");
            }
            if ( isset($_POST['emailuser']) ) {
                USER_createAndSendPassword ($username, $email, $uid, $passwd);
            }
            if ( $uid < 2 ) {
                return USER_edit('',509);
            }
            $newuser = 1;
        }
        // at this point, we have a valid user...

        // Filter some of the text entry fields to ensure they don't cause problems...

        $fullname = strip_tags($fullname);
        $about    = strip_tags($about);
        $pgpkey   = strip_tags($pgpkey);

        $curphoto = USER_handlePhotoUpload ($uid, $delete_photo);
        if (($_CONF['allow_user_photo'] == 1) && !empty ($curphoto)) {
            $curusername = DB_getItem ($_TABLES['users'], 'username',"uid = $uid");
            if ($curusername != $username) {
                // user has been renamed - rename the photo, too
                $newphoto = preg_replace ('/' . $curusername . '/', $username, $curphoto, 1);
                $imgpath = $_CONF['path_images'] . 'userphotos/';
                if (@rename ($imgpath . $curphoto,$imgpath . $newphoto) === false) {
                    Log::write('system',Log::ERROR,'Could not rename userphoto "'.$curphoto . '" to "' . $newphoto . '".');
                } else {
                    $curphoto = $newphoto;
                }
            }
        }

        $disableTFA = '';
        if ( isset($_POST['disable_tfa'])) {
            $disableTFA = "tfa_enabled = 0,tfa_secret=NULL,";
            DB_delete ($_TABLES['tfa_backup_codes'], 'uid', $uid);
        }

        // update users table

        $sql = "UPDATE {$_TABLES['users']} SET ".
            "username = '".DB_escapeString($username)."',".
            "fullname = '".DB_escapeString($fullname)."',".
            "passwd   = '".DB_escapeString($passwd2)."',".
            "email    = '".DB_escapeString($email)."',".
            "homepage = '".DB_escapeString($homepage)."',".
            "sig      = '".DB_escapeString($sig)."',".
            "photo    = '".DB_escapeString($curphoto)."',".
            "cookietimeout = $cooktime,".
            "theme    = '".DB_escapeString($theme)."',".
            "language = '".DB_escapeString($language)."',".
            $disableTFA .
            "status   = $userstatus WHERE uid = $uid;";

        DB_query($sql);

        // update userprefs

        $sql = "UPDATE {$_TABLES['userprefs']} SET ".
            "noicons = $noicons,".
            "dfid    = $dfid,".
            "tzid    = '".DB_escapeString($tzid)."',".
            "emailstories = 0,".
            "emailfromadmin = $emailfromadmin,".
            "emailfromuser  = $emailfromuser,".
            "showonline = $showonline".
            " WHERE uid=$uid;";

        DB_query($sql);

        // userinfo table

        $sql = "UPDATE {$_TABLES['userinfo']} SET ".
            "about      = '".DB_escapeString($about)."',".
            "location   = '".DB_escapeString($location)."',".
            "pgpkey     = '".DB_escapeString($pgpkey)."' WHERE uid=$uid;";

        DB_query($sql);

        // userindex table
        $ETIDS = array();
        $allowed_etids = array();

        if (isset($_POST['dgtopics']) && is_array($_POST['dgtopics'])) {
            $ETIDS = @array_values($_POST['dgtopics']);
        }

        $etids = '-';
        if (is_array($ETIDS) && sizeof ($ETIDS) > 0) {
            $etids = DB_escapeString (implode (' ', $ETIDS));
        } else {
            $etids = '-';
        }
        DB_save($_TABLES['userindex'],"uid,maxstories,etids","$uid,$maxstories,'$etids'");

        // usercomment

        DB_save($_TABLES['usercomment'],'uid,commentmode,commentorder,commentlimit',"$uid,'$commentmode','$commentorder',".intval($commentlimit));

        if ($_CONF['custom_registration'] AND (function_exists('CUSTOM_userSave'))) {
            CUSTOM_userSave($uid);
        }
        if( ($_CONF['usersubmission'] == 1) && ($oldstatus == USER_ACCOUNT_AWAITING_APPROVAL)
               && ($userstatus == USER_ACCOUNT_ACTIVE || $userstatus == USER_ACCOUNT_AWAITING_ACTIVATION || $userstatus == USER_ACCOUNT_AWAITING_VERIFICATION) ) {
            USER_createAndSendPassword ($username, $email, $uid);
        }
        if ($userstatus == USER_ACCOUNT_DISABLED) {
            SESS_endUserSession($uid);
        }
        $userChanged = true;

        // if groups is -1 then this user isn't allowed to change any groups so ignore
        if (is_array ($groups) && SEC_hasRights ('group.edit')) {
            if (!SEC_inGroup ('Root')) {
                $rootgrp = DB_getItem ($_TABLES['groups'], 'grp_id',"grp_name = 'Root'");
                if (in_array ($rootgrp, $groups)) {
                    Log::write('system',Log::ERROR,"User {$_USER['username']} ({$_USER['uid']}) just tried to give Root permissions to user $username.");
                    echo COM_refresh ($_CONF['site_admin_url'] . '/index.php');
                    exit;
                }
            }

            // make sure the Remote Users group is in $groups
            if (SEC_inGroup ('Remote Users', $uid)) {
                $remUsers = DB_getItem ($_TABLES['groups'], 'grp_id',
                                        "grp_name = 'Remote Users'");
                if (!in_array ($remUsers, $groups)) {
                    $groups[] = $remUsers;
                }
            }

            if ($_USER_VERBOSE) {
                Log::write('system',Log::DEBUG,"deleting all group_assignments for user $uid/$username");
            }

            // remove user from all groups that the User Admin is a member of
            $UserAdminGroups = SEC_getUserGroups ();
            $whereGroup = 'ug_main_grp_id IN ('
                        . implode (',', $UserAdminGroups) . ')';
            DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE (ug_uid = $uid) AND " . $whereGroup);
/* --- May 2021 - no longer add users to All Users or Logged-In User groups
            // make sure to add user to All Users and Logged-in Users groups
            $allUsers = DB_getItem ($_TABLES['groups'], 'grp_id',
                                    "grp_name = 'All Users'");
            if (!in_array ($allUsers, $groups)) {
                $groups[] = $allUsers;
            }
            $logUsers = DB_getItem ($_TABLES['groups'], 'grp_id',
                                    "grp_name = 'Logged-in Users'");
            if (!in_array ($logUsers, $groups)) {
                $groups[] = $logUsers;
            }
*/
            foreach ($groups as $userGroup) {
                if (in_array ($userGroup, $UserAdminGroups) || SEC_inGroup(1)) {
                    if ($_USER_VERBOSE) {
                        Log::write('system',Log::DEBUG,"adding group_assignment ".$userGroup." for $username");
                    }
                    $sql = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid) VALUES ($userGroup, $uid)";
                    DB_query ($sql);
                }
            }
        }

        // subscriptions
        $subscription_deletes = array();
        if (isset($_POST['subdelete']) && is_array($_POST['subdelete'])) {
            $subscription_deletes  = @array_values($_POST['subdelete']);
        }
        if ( is_array($subscription_deletes) ) {
            foreach ( $subscription_deletes AS $subid ) {
                DB_delete($_TABLES['subscriptions'],'sub_id',(int) $subid);
            }
        }

        foreach ( $social_services AS $service ) {
            $service_input = $service['service'].'_username';
            $_POST[$service_input] = DB_escapeString($_POST[$service_input]);

            if ( $_POST[$service_input] != '' ) {
                $sql  = "REPLACE INTO {$_TABLES['social_follow_user']} (ssid,uid,ss_username) ";
                $sql .= " VALUES (" . (int) $service['service_id'] . ",".$uid.",'".$_POST[$service_input]."');";
                DB_query($sql,1);
            } else {
                $sql = "DELETE FROM {$_TABLES['social_follow_user']} WHERE ssid = ".(int) $service['service_id']." AND uid=".(int) $uid;
                DB_query($sql,1);
            }
        }

        if ( $newuser == 0 ) {
            PLG_profileSave('',$uid);
        } else {
            PLG_createUser( $uid );
        }

        if ($userChanged) {
            CACHE_clear();
            PLG_userInfoChanged ($uid);
        }

//@TODO - this error check does not seem correct
//      - this only checks the last SQL
        $errors = DB_error($sql);
        if (empty($errors)) {
            echo PLG_afterSaveSwitch (
                $_CONF['aftersave_user'],
                "{$_CONF['site_url']}/users.php?mode=profile&uid=$uid",
                'user',
                21
            );
        } else {
            Log::write('system',Log::ERROR,'Error in USER_save() in '.$_CONF['site_admin_url'] . '/user.php');
            $retval .= COM_siteHeader ('menu', $LANG28[22]);
            $retval .= 'Error in USER_save() in '.$_CONF['site_admin_url'] . '/user.php';
            $retval .= COM_siteFooter ();
            echo $retval;
            exit;
        }
    } else {
        $retval = COM_siteHeader('menu', $LANG28[1]);
        $retval .= $LANG28[10];
        if (DB_count($_TABLES['users'],'uid',$uid) > 0) {
            $retval .= USER_edit($uid);
        } else {
            $retval .= USER_edit();
        }
        $retval .= COM_siteFooter();
        echo $retval;
        exit;
    }
    Cache::getInstance()->deleteItemsByTags(array('menu', 'users', 'userdata','user_' . $uid));

    if ($_USER_VERBOSE) Log::write('system',Log::DEBUG,"***************leaving USER_save()*****************",1);

    return $retval;
}



/**
* This function allows the batch deletion of users that are inactive
* It shows the form that will filter user that will be deleted
*
* @return   string          HTML Form
*/
function USER_batchAdmin()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG01, $LANG28, $_IMAGE_TYPE;

    $display = '';

    $usr_type = '';
    if (isset($_REQUEST['usr_type'])) {
        $usr_type = COM_applyFilter($_REQUEST['usr_type']);
    } else {
        $usr_type = 'phantom';
    }

    if (!in_array($usr_type,array('phantom','old','recent','short')) ) {
        $usr_type = 'phantom';
    }

    $usr_time_arr = array();
    $usr_time = '';
    $usr_time_arr['phantom'] = 2;
    $usr_time_arr['short'] = 6;
    $usr_time_arr['old'] = 24;
    $usr_time_arr['recent'] = 1;

    if (isset($_POST['usr_time'])) {
        $usr_time_arr = $_POST['usr_time'];
    } elseif (isset($_GET['usr_time']) ) {
        $usr_time_arr[$usr_type] = $_GET['usr_time'];
    } else {
        $usr_time_arr['phantom'] = 2;
        $usr_time_arr['short'] = 6;
        $usr_time_arr['old'] = 24;
        $usr_time_arr['recent'] = 1;
    }
    $usr_time = $usr_time_arr[$usr_type];

    // list of options for user display
    // sel => form-id
    // desc => title
    // txt1 => text before input-field
    // txt2 => text after input field
    $opt_arr = array(
        array('sel' => 'phantom', 'desc' => $LANG28[57], 'txt1' => $LANG28[60], 'txt2' => $LANG28[61]),
        array('sel' => 'short', 'desc' => $LANG28[58], 'txt1' => $LANG28[62], 'txt2' => $LANG28[63]),
        array('sel' => 'old', 'desc' => $LANG28[59], 'txt1' => $LANG28[64], 'txt2' => $LANG28[65]),
        array('sel' => 'recent', 'desc' => $LANG28[74], 'txt1' => $LANG28[75], 'txt2' => $LANG28[76])
    );

    $user_templates = new Template($_CONF['path_layout'] . 'admin/user');
    $user_templates->set_file (array ('form' => 'batchadmin.thtml',
                                      'options' => 'batchadmin_options.thtml'
                                      ));
    $user_templates->set_var('site_admin_url', $_CONF['site_admin_url']);
    $user_templates->set_var('layout_url', $_CONF['layout_url']);
    $user_templates->set_var('usr_type', $usr_type);
    $user_templates->set_var('usr_time', $usr_time);
    $user_templates->set_var('lang_instruction', $LANG28[56]);
    $user_templates->set_var('lang_updatelist', $LANG28[66]);

    $num_opts = count($opt_arr);
    for ($i = 0; $i < $num_opts; $i++) {
        $selector = '';
        if ($usr_type == $opt_arr[$i]['sel']) {
            $selector = ' checked="checked"';
        }
        $user_templates->set_var('sel_id', $opt_arr[$i]['sel']);
        $user_templates->set_var('selector', $selector);
        $user_templates->set_var('lang_description', $opt_arr[$i]['desc']);
        $user_templates->set_var('lang_text_start', $opt_arr[$i]['txt1']);
        $user_templates->set_var('lang_text_end', $opt_arr[$i]['txt2']);
        $user_templates->set_var('id_value', $usr_time_arr[$opt_arr[$i]['sel']]);
        $user_templates->parse('options_list', 'options', true);
    }
    $user_templates->parse('form', 'form');
    $desc = $user_templates->finish($user_templates->get_var('form'));

    $header_arr = array(      # display 'text' and use table field 'field'
//                  array('text' => $LANG28[37], 'field' => $_TABLES['users'] . '.uid', 'sort' => true, 'align' => 'center'),
                    array('text' => $LANG28[3], 'field' => 'username', 'sort' => true),
                    array('text' => $LANG28[4], 'field' => 'fullname', 'sort' => true)
    );

    switch ($usr_type) {
        case 'phantom':
            $header_arr[] = array('text' => $LANG28[14], 'field' => 'regdate', 'sort' => true, 'align' => 'center');
            $header_arr[] = array('text' => $LANG28[41], 'field' => 'lastlogin_short', 'sort' => true, 'align' => 'center');
            $header_arr[] = array('text' => $LANG28[67], 'field' => 'phantom_date', 'sort' => true, 'align' => 'center');
            $list_sql = ", UNIX_TIMESTAMP()- UNIX_TIMESTAMP(regdate) as phantom_date";
            $filter_sql = "lastlogin = 0 AND UNIX_TIMESTAMP()- UNIX_TIMESTAMP(regdate) > " . ($usr_time * 2592000) . " AND";
            $sort = 'regdate';
            break;
        case 'short':
            $header_arr[] = array('text' => $LANG28[14], 'field' => 'regdate', 'sort' => true, 'align' => 'center');
            $header_arr[] = array('text' => $LANG28[41], 'field' => 'lastlogin_short', 'sort' => true, 'align' => 'center');
            $header_arr[] = array('text' => $LANG28[68], 'field' => 'online_hours', 'sort' => true, 'align' => 'center');
            $header_arr[] = array('text' => $LANG28[69], 'field' => 'offline_months', 'sort' => true, 'align' => 'center');
            $list_sql = ", (lastlogin - UNIX_TIMESTAMP(regdate)) AS online_hours, (UNIX_TIMESTAMP() - lastlogin) AS offline_months";
            $filter_sql = "lastlogin > 0 AND lastlogin - UNIX_TIMESTAMP(regdate) < 86400 "
                         . "AND UNIX_TIMESTAMP() - lastlogin > " . ($usr_time * 2592000) . " AND";
            $sort = 'lastlogin';
            break;
        case 'old':
            $header_arr[] = array('text' => $LANG28[41], 'field' => 'lastlogin_short', 'sort' => true, 'align' => 'center');
            $header_arr[] = array('text' => $LANG28[69], 'field' => 'offline_months', 'sort' => true, 'align' => 'center');
            $list_sql = ", (UNIX_TIMESTAMP() - lastlogin) AS offline_months";
            $filter_sql = "lastlogin > 0 AND (UNIX_TIMESTAMP() - lastlogin) > " . ($usr_time * 2592000) . " AND";
            $sort = 'lastlogin';
            break;
        case 'recent':
            $header_arr[] = array('text' => $LANG28[14], 'field' => 'regdate', 'sort' => true, 'align' => 'center');
            $header_arr[] = array('text' => $LANG28[41], 'field' => 'lastlogin_short', 'sort' => true, 'align' => 'center');
            $list_sql = "";
            $filter_sql = "(UNIX_TIMESTAMP() - UNIX_TIMESTAMP(regdate)) < " . ($usr_time * 2592000) . " AND";
            $sort = 'regdate';
            break;
    }

    $header_arr[] = array('text' => $LANG28[7], 'field' => 'email', 'sort' => true);
    $header_arr[] = array('text' => $LANG28[87], 'field' => 'num_reminders', 'sort' => true, 'align' => 'center', 'width' => '40px');

    $text_arr = array('has_menu'     => true,
                      'title'        => '',//$LANG28[54],
                      'instructions' => "$desc",
                      'icon'         => $_CONF['layout_url'] . '/images/icons/user.' . $_IMAGE_TYPE,
                      'form_url'     => $_CONF['site_admin_url'] . "/user.php?batchadmin=x&amp;usr_type=$usr_type&amp;usr_time=$usr_time",
                      'help_url'     => '',
                      'has_search'    => true,
                      'has_limit'     => true,
                      'has_paging'    => true,
    );

    $defsort_arr = array('field'     => $sort,
                         'direction' => 'ASC');

    $join_userinfo = "LEFT JOIN {$_TABLES['userinfo']} ON {$_TABLES['users']}.uid={$_TABLES['userinfo']}.uid ";
    $select_userinfo = ", lastlogin as lastlogin_short $list_sql ";

    $sql = "SELECT {$_TABLES['users']}.uid,username,fullname,email,photo,status,regdate,num_reminders$select_userinfo "
         . "FROM {$_TABLES['users']} $join_userinfo WHERE 1=1";

    $query_arr = array (
        'table' => 'users',
        'sql' => $sql,
        'query_fields' => array('username', 'email', 'fullname'),
        'default_filter' => "AND $filter_sql {$_TABLES['users']}.uid > 1"
    );

    $actions = FieldList::deleteButton(array(
        'name' => 'delbutton',
        'text' => $LANG01[124],
        'attr' => array(
            'title' => $LANG01[124],
            'onclick' => 'return doubleconfirm(\'' . $LANG28[73] . '\',\'' . $LANG28[110] . '\');'
        )
    ));
    $actions .= '&nbsp;&nbsp;&nbsp;&nbsp;';
    $actions .= FieldList::emailButton(array(
        'name' => 'reminder',
        'text' => $LANG28[78],
        'attr' => array(
            'title' => $LANG28[78],
            'onclick' => 'return confirm(\'' . $LANG28[100] . '\');'
        )
    ));

    $options = array('chkselect' => true, 'chkfield' => 'uid', 'chkactions' => $actions);

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/user.php',
              'text' => $LANG_ADMIN['admin_users']),
        array('url' => $_CONF['site_admin_url'] . '/user.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'] . '/user.php?import=x',
              'text' => $LANG28[23]),
        array('url' => $_CONF['site_admin_url'] . '/user.php?batchadmin=x',
              'text' => $LANG28[54],'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/prefeditor.php',
              'text' => $LANG28[95]),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home'])
    );

    $display .= COM_startBlock($LANG28[103], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $display .= ADMIN_createMenu(
        $menu_arr,
        '', //$desc,
        $_CONF['layout_url'] . '/images/icons/user.' . $_IMAGE_TYPE
    );

    $display .= '<div class="batch-admin_filter">'.$desc.'</div>';

    $token = SEC_createToken();
    $form_arr['bottom'] = "<input type=\"hidden\" name=\"" . CSRF_TOKEN
                        . "\" value=\"{$token}\"" . "/>";

    $display .= ADMIN_list('user', 'USER_getListField', $header_arr,
                           $text_arr, $query_arr, $defsort_arr, '', $token,
                           $options, $form_arr);

    $display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $display;
}

/**
* This function deletes the users selected in the USER_batchAdmin function
*
* @return   string          HTML with success or error message
*
*/
function USER_batchDeleteExec()
{
    global $_CONF, $LANG28;

    $msg = '';
    $user_list = array();
    if (isset($_POST['delitem'])) {
        $user_list = $_POST['delitem'];
    }

    $nusers = count($user_list);
    if ($nusers == 0) {
        $msg = $LANG28[72] . '<br/>';
    } else {
        $c = 0;

        if (isset($user_list) AND is_array($user_list)) {
            foreach($user_list as $delitem) {
                $delitem = COM_applyFilter($delitem);
                if (!USER_deleteAccount ($delitem)) {
                    $msg .= "<strong>{$LANG28[2]} $delitem {$LANG28[70]}</strong><br/>\n";
                } else {
                    $c++; // count the deleted users
                }
            }
        }

        $c = Cache::getInstance()->deleteItemsByTag('menu');

        COM_numberFormat($c); // just in case we have more than 999 ...
        $msg .= "{$LANG28[71]}: $c {$LANG28[102]}.<br/>\n";
    }

    return $msg;
}


/**
* This function used to send out reminders to users to access the site or account may be deleted
*
* @return   string          HTML with success or error message
*
*/
function USER_sendReminders()
{
    global $_CONF, $_TABLES, $LANG04, $LANG28;

    $msg = '';
    $user_list = array();
    if (isset($_POST['delitem'])) {
        $user_list = $_POST['delitem'];
    }

    $nusers = count($user_list);

    if (count($user_list) == 0) {
        $msg = $LANG28[79] . '<br/>';
    } else {
        $c = 0;

        if (isset($_POST['delitem']) AND is_array($_POST['delitem'])) {
            foreach($_POST['delitem'] as $delitem) {
                $uid = COM_applyFilter($delitem);
                $useremail = DB_getItem ($_TABLES['users'], 'email', "uid = '$uid'");
                $username = DB_getItem ($_TABLES['users'], 'username', "uid = '$uid'");
                $lastlogin = DB_getItem ($_TABLES['userinfo'], 'lastlogin', "uid = '$uid'");
                $lasttime = COM_getUserDateTimeFormat ($lastlogin);
                if (file_exists ($_CONF['path_data'] . 'reminder_email.txt')) {
                    $template = new Template ($_CONF['path_data']);
                    $template->set_file (array ('mail' => 'reminder_email.txt'));
                    $template->set_var('site_url', $_CONF['site_url']);
                    $template->set_var('site_name', $_CONF['site_name']);
                    $template->set_var('site_slogan', $_CONF['site_slogan']);
                    $template->set_var('lang_username', $LANG04[2]);
                    $template->set_var('username', $username);
                    $template->set_var('name', COM_getDisplayName ($uid));
                    $template->set_var('lastlogin', $lasttime[0]);

                    $template->parse ('output', 'mail');
                    $mailtext = $template->get_var ('output');
                } else {
                    if ($lastlogin == 0) {
                        $mailtext = sprintf($LANG28[83],$_CONF['site_name']) . "\n\n";
                    } else {
                        $mailtext = sprintf($LANG28[82], $_CONF['site_name'], $lasttime[0]) . "\n\n";
                    }
                    $mailtext .= sprintf($LANG28[84], $username,$_CONF['site_url']) . "\n";
                    $mailtext .= sprintf($LANG28[85], $_CONF['site_url']
                                         . '/users.php?mode=getpassword') . "\n\n";

                }
                $subject = sprintf($LANG28[81], $_CONF['site_name']);
                if ($_CONF['site_mail'] !== $_CONF['noreply_mail']) {
                    $mailfrom = $_CONF['noreply_mail'];
                    global $LANG_LOGIN;
                    $mailtext .= LB . LB . $LANG04[159];
                } else {
                    $mailfrom = $_CONF['site_mail'];
                }

                $to = array();
                $to = COM_formatEmailAddress($username,$useremail);
                $from = array();
                $from = COM_formatEmailAddress('',$mailfrom);

                if (COM_mail ($to, $subject, $mailtext, $from)) {
                    DB_query("UPDATE {$_TABLES['users']} SET num_reminders=num_reminders+1 WHERE uid=$uid");
                    $c++;
                } else {
                    Log::write('system',Log::ERROR,"Error attempting to send account reminder to user: $username ($uid)");
                }
            }
        }

        COM_numberFormat($c); // just in case we have more than 999)..
        $msg .= "{$LANG28[80]}: $c<br/>\n";
    }

    return $msg;
}


/**
* This function allows the administrator to import batches of users
*
* TODO: This function should first display the users that are to be imported,
* together with the invalid users and the reason of invalidity. Each valid line
* should have a checkbox that allows selection of final to be imported users.
* After clicking an extra button, the actual import should take place. This will
* prevent problems in case the list formatting is incorrect.
*
* @return   string          HTML with success or error message
*
*/
function USER_importExec()
{
    global $_CONF, $_TABLES, $LANG04, $LANG28;

    // Setting this to true will cause import to print processing status to
    // webpage and to the error.log file
    $verbose_import = true;

    $retval = '';

    // Bulk import implies admin authorisation:
    $_CONF['usersubmission'] = 0;

    // First, upload the file

    $upload = new upload ();
    $upload->setPath ($_CONF['path_data']);
    $upload->setAllowedMimeTypes (array ('text/plain' => '.txt','application/octet-stream' => '.txt'));
    $upload->setFileNames ('user_import_file.txt');
    $upload->setFieldName('importfile');
    if ($upload->uploadFiles()) {
        // Good, file got uploaded, now install everything
        $filename = $_CONF['path_data'] . 'user_import_file.txt';
        if (!file_exists($filename)) { // empty upload form
            $retval = COM_refresh($_CONF['site_admin_url']
                                  . '/user.php?import=x');
            return $retval;
        }
    } else {
        // A problem occurred, print debug information
        $retval  = COM_siteHeader ('menu', $LANG28[22]);
        $retval .= COM_showMessageText($upload->printErrors(false),$LANG27[24],true,'error');
        $retval .= COM_siteFooter();
        return $retval;
    }

    $users = file ($filename);

    $retval .= COM_siteHeader ('menu', $LANG28[24]);
    $retval .= COM_startBlock ($LANG28[31], '',
            COM_getBlockTemplate ('_admin_block', 'header'));

    // Following variables track import processing statistics
    $successes = 0;
    $failures = 0;
    foreach ($users as $line) {
        $line = rtrim ($line);
        if (empty ($line)) {
            continue;
        }

        list ($full_name, $u_name, $email) = explode ("\t", $line);

        $full_name = strip_tags ($full_name);
        $u_name = COM_applyFilter ($u_name);
        $email = COM_applyFilter ($email);

        if ($verbose_import) {
            $retval .="<br/><b>Working on username=$u_name, fullname=$full_name, and email=$email</b><br/>\n";
            Log::write('system',Log::INFO,"Working on username=$u_name, fullname=$full_name, and email=$email");
        }

        // prepare for database
        $userName  = trim ($u_name);
        $fullName  = trim ($full_name);
        $emailAddr = trim ($email);

        if (COM_isEmail ($email)) {
            // email is valid form
            $ucount = DB_count ($_TABLES['users'], 'username',
                                DB_escapeString ($userName));
            $ecount = DB_count ($_TABLES['users'], 'email',
                                DB_escapeString ($emailAddr));

            if (($ucount == 0) && ($ecount == 0)) {
                // user doesn't already exist - pass in optional true for $batchimport parm
                $uid = USER_createAccount ($userName, $emailAddr, '',
                                           $fullName,'','','',true);

                $result = USER_createAndSendPassword ($userName, $emailAddr, $uid);

                if ($result && $verbose_import) {
                    $retval .= "<br/> Account for <b>$u_name</b> created successfully.<br/>\n";
                    Log::write('system',Log::INFO,"Account for $u_name created successfully");
                } else if ($result) {
                    $successes++;
                } else {
                    // user creation failed
                    $retval .= "<br/>ERROR: There was a problem creating the account for <b>$u_name</b>.<br/>\n";
                    Log::write('system',Log::ERROR,"ERROR: here was a problem creating the account for $u_name.");
                }
            } else {
                if ($verbose_import) {
                    $retval .= "<br/><b>$u_name</b> or <b>$email</b> already exists, account not created.<br/>\n"; // user already exists
                    Log::write('system',Log::INFO,"$u_name,$email: username or email already exists, account not created");
                }
                $failures++;
            } // end if $ucount == 0 && ecount == 0
        } else {
            if ($verbose_import) {
                $retval .= "<br/><b>$email</b> is not a valid email address, account not created<br/>\n"; // malformed email
                Log::write('system',Log::ERROR,"$email is not a valid email address, account not created");
            }
            $failures++;
        } // end if COM_isEmail($email)
    } // end foreach

    unlink ($filename);
    $c = Cache::getInstance()->deleteItemsByTag('menu');
    $retval .= '<p>' . sprintf ($LANG28[32], $successes, $failures);

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    $retval .= COM_siteFooter ();

    return $retval;
}

/**
* Display "batch add" (import) form
*
* @return   string      HTML for import form
*
*/
function USER_import()
{
    global $_CONF, $LANG28;

    $T = new Template($_CONF['path_layout'] . '/admin/user');
    $T->set_file('import', 'batchimport.thtml');
    $T->set_var(array(
        'token' => SEC_createToken(),
        'lang_submit' => $LANG28[30],
        'lang_path' => $LANG28[29],
        'csrf_token' => CSRF_TOKEN,
    ) );
    return $T->finish($T->parse('output', 'import'));
}

/**
* Delete a user
*
* @param    int     $uid    id of user to delete
* @return   string          HTML redirect
*
*/
function USER_delete($uid)
{
    global $_CONF, $_TABLES, $LANG_ADM_ACTIONS;

    // pull in additional user information to add to the log.

    $db = Database::getInstance();

    $row = $db->conn->fetchAssoc(
        "SELECT * FROM `{$_TABLES['users']}` WHERE uid = ?",
        array($uid),
        array(Database::INTEGER)
    );
    // if no user is found
    if ($row === null || $row === false) {
        echo COM_refresh ($_CONF['site_admin_url'] . '/user.php');
    }

    if (!USER_deleteAccount ($uid)) {
        return COM_refresh ($_CONF['site_admin_url'] . '/user.php');
    }
    Cache::getInstance()->deleteItemsByTags(array('menu', 'users', 'user_' . $uid));
    COM_setMessage(22);

    AdminAction::write('system','delete_user',sprintf($LANG_ADM_ACTIONS['delete_user'],$row['username'],$uid));

    return COM_refresh ($_CONF['site_admin_url'] . '/user.php');
}

/**
* Upload new photo, delete old photo
*
* @param    string  $delete_photo   'on': delete old photo
* @return   string                  filename of new photo (empty = no new photo)
*
*/
function USER_handlePhotoUpload ($uid, $delete_photo = '')
{
    global $_CONF, $_TABLES, $LANG24;

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
        return '';
    }

    $filename = '';
    if (!empty ($delete_photo) && ($delete_photo == 1)) {
        $delete_photo = true;
    } else {
        $delete_photo = false;
    }

    $curphoto = DB_getItem ($_TABLES['users'], 'photo',"uid = ".(int) $uid);
    if (empty ($curphoto)) {
        $delete_photo = false;
    }
    // see if user wants to upload a (new) photo
    $newphoto = $_FILES['photo'];
    if (!empty ($newphoto['name'])) {
        $pos = strrpos ($newphoto['name'], '.') + 1;
        $fextension = substr ($newphoto['name'], $pos);
        $filename = $uid . '.' . $fextension;

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
        $upload->setMaxDimensions (1024000,1024000);
        $upload->uploadFiles ();

        if ($upload->areErrors ()) {
            return '';
        }
        IMG_resizeImage($_CONF['path_images'] . 'userphotos/'.$filename,$_CONF['path_images'] . 'userphotos/'.$filename,$_CONF['max_photo_height'],$_CONF['max_photo_width']);
    } else if (!$delete_photo && !empty ($curphoto)) {
        $filename = $curphoto;
    }
    return $filename;
}

// MAIN ========================================================================

$display = '';

$action = '';
$expected = array('edit','save','delete','import','importexec','batchadmin','delbutton_x','reminder_x' );
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

$uid = 0;
if (isset($_POST['uid'])) {
    $uid = COM_applyFilter($_POST['uid'], true);
} elseif (isset($_GET['uid'])) {
    $uid = COM_applyFilter($_GET['uid'], true);
}

$grp_id = 0;
if (isset($_POST['grp_id'])) {
    $grp_id = COM_applyFilter($_POST['grp_id'], true);
} elseif (isset($_GET['grp_id'])) {
    $grp_id = COM_applyFilter($_GET['grp_id'], true);
} elseif (SESS_isSet('grp_id') ) {
    $grp_id = SESS_getVar('grp_id');
}
SESS_setVar('grp_id',$grp_id);

$msg = COM_getMessage();

switch($action) {

    case 'edit':
        $display .= COM_siteHeader('menu', $LANG28[1]);
        if ( $uid == 1 ) {
            $display .= COM_siteHeader('menu', $LANG28[11]);
            $display .= COM_showMessageFromParameter();
            $display .= USER_list();
            $display .= COM_siteFooter();
        } else {
            $display .= USER_edit($uid, $msg);
            $display .= COM_siteFooter();
        }
        break;

    case 'save':
        if (SEC_checkToken()) {
            $delphoto = '';
            if (isset ($_POST['delete_photo'])) {
                $delphoto = $_POST['delete_photo'];
            }
            if (!isset ($_POST['oldstatus'])) {
                $_POST['oldstatus'] = USER_ACCOUNT_ACTIVE;
            }
            if (!isset ($_POST['userstatus'])) {
                $_POST['userstatus'] = USER_ACCOUNT_ACTIVE;
            }

            $username = (isset($_POST['username']) ? trim($_POST['username']) : '');
            $fullname = (isset($_POST['fullname']) ? $_POST['fullname'] : '');
            $passwd   = (isset($_POST['newp'])   ? trim($_POST['newp']) : '');
            $passwd_conf = (isset($_POST['newp_conf']) ? trim($_POST['newp_conf']) : '');
            $email = (isset($_POST['email']) ? trim($_POST['email']) : '');
            $regdate = (isset($_POST['regdate']) ? $_POST['regdate'] : '');
            $homepage = (isset($_POST['homepage']) ? $_POST['homepage'] : '');
            $groups  = (isset($_POST['groups']) ? $_POST['groups'] : array());
            $userstatus = (isset($_POST['userstatus']) ? $_POST['userstatus'] : 0);
            $oldstatus = (isset($_POST['oldstatus']) ? $_POST['oldstatus'] : 0 );

            $display = USER_save($uid,
                    $username,$fullname,$passwd,$passwd_conf,$email,$regdate,$homepage,$groups,$delphoto,$userstatus,$oldstatus);
            if (!empty($display)) {
                $tmp = COM_siteHeader('menu', $LANG28[22]);
                $tmp .= $display;
                $tmp .= COM_siteFooter();
                $display = $tmp;
            }

        } else {
            Log::write('system',Log::ERROR,'User ' . $_USER['username'] . ' tried to edit user ' . $uid . ' and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'delete':
         if (!isset ($uid) || empty ($uid) || ($uid <= 1)) {
            Log::write('system',Log::ERROR,'User ' . $_USER['username'] . ' attempted to delete user, uid empty, null, or is <= 1');
            $display .= COM_refresh($_CONF['site_admin_url'] . '/user.php');
        } elseif (SEC_checkToken()) {
            $display .= USER_delete($uid);
        } else {
            Log::write('system',Log::ERROR,'User ' . $_USER['username'] . ' tried to delete user ' . $uid . ' and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'import':
        $display .= COM_siteHeader('menu', $LANG28[24]);
        $menu_arr = array (
            array('url' => $_CONF['site_admin_url'] . '/user.php',
                  'text' => $LANG_ADMIN['admin_users']),
            array('url' => $_CONF['site_admin_url'] . '/user.php?edit=x',
                  'text' => $LANG_ADMIN['create_new']),
            array('url' => $_CONF['site_admin_url'] . '/user.php?import=x',
                  'text' => $LANG28[23],'active'=>true),
            array('url' => $_CONF['site_admin_url'] . '/user.php?batchadmin=x',
                  'text' => $LANG28[54]),
            array('url' => $_CONF['site_admin_url'] . '/prefeditor.php',
                  'text' => $LANG28[95]),
            array('url' => $_CONF['site_admin_url'].'/index.php',
                  'text' => $LANG_ADMIN['admin_home'])
        );
        $display .= COM_startBlock ($LANG28[24], '',
                            COM_getBlockTemplate ('_admin_block', 'header'));
        $display .= ADMIN_createMenu(
            $menu_arr,
            '',
            $_CONF['layout_url'] . '/images/icons/user.' . $_IMAGE_TYPE
        );
        $display .= $LANG28[25] . '<br/><br/>';
        $display .= USER_import();
        $display .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
        $display .= COM_siteFooter();
        break;

    case 'importexec':
        if (SEC_checkToken()) {
            $display .= USER_importExec();
        } else {
            Log::write('system',Log::ERROR,'User ' . $_USER['username'] . ' tried to import users and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'batchadmin':
        if (!$_CONF['lastlogin']) {
            $msg = '<br/>'. $LANG28[55];
            $display .= COM_siteHeader('menu', $LANG28[11])
            . COM_showMessageText($msg)
            . USER_list($grp_id)
            . COM_siteFooter();
        } else {
            $display .= COM_siteHeader ('menu', $LANG28[54]);
            $display .= USER_batchAdmin();
            $display .= COM_siteFooter();
        }
        break;

    case 'delbutton_x':
        if (SEC_checkToken()) {
            $msg = USER_batchDeleteExec();
            $display .= COM_siteHeader ('menu', $LANG28[11])
                . COM_showMessageText($msg)
                . USER_batchAdmin()
                . COM_siteFooter();
        } else {
            Log::write('system',Log::ERROR,'User ' . $_USER['username'] . ' tried to batch delete users and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'reminder_x':
        if (SEC_checkToken()) {
            $msg = USER_sendReminders();
            $display .= COM_siteHeader ('menu', $LANG28[11])
                . COM_showMessageText($msg)
                . USER_batchAdmin()
                . COM_siteFooter();
        } else {
            Log::write('system',Log::ERROR,'User ' . $_USER['username'] . ' tried to send Site Login Reminders and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        $display .= COM_siteHeader('menu', $LANG28[11]);
        $display .= COM_showMessageFromParameter();
        $display .= USER_list($grp_id);
        $display .= COM_siteFooter();
        break;

}

echo $display;

?>
