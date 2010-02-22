<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | user.php                                                                 |
// |                                                                          |
// | glFusion user administration page.                                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2009 by the following authors:                        |
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

// Set this to true to get various debug messages from this script
$_USER_VERBOSE = false;

require_once '../lib-common.php';
require_once 'auth.inc.php';
USES_lib_user();

$display = '';

// Make sure user has access to this page
if (!SEC_hasRights('user.edit')) {
    $retval .= COM_siteHeader ('menu', $MESSAGE[30]);
    $retval .= COM_startBlock ($MESSAGE[30], '',
               COM_getBlockTemplate ('_msg_block', 'header'));
    $retval .= $MESSAGE[37];
    $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $retval .= COM_siteFooter ();
    COM_accessLog("User {$_USER['username']} tried to illegally access the user administration screen.");
    echo $retval;
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
function edituser($uid = '', $msg = '')
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG04, $LANG28, $LANG_ADMIN,
           $LANG_configselects, $LANG_confignames,$LANG_ACCESS,$MESSAGE,$_IMAGE_TYPE;

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

    USES_class_navbar();
    USES_lib_admin();

    $retval .= COM_startBlock($LANG28[1], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/user.php',
              'text' => $LANG28[11]),
        array('url' => $_CONF['site_admin_url'] . '/user.php?mode=importform',
              'text' => $LANG28[23]),
        array('url' => $_CONF['site_admin_url'] . '/user.php?mode=batchdelete',
              'text' => $LANG28[54]),
        array('url' => $_CONF['site_admin_url'] . '/prefeditor.php',
              'text' => $LANG28[95]),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $userform = new Template ($_CONF['path_layout'] . 'admin/user/');
    $userform->set_file('user','adminuseredit.thtml');

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
            } else {
                $navbar->add_menuitem($label,'showhideProfileEditorDiv("'.$id.'",'.$cnt.');return false;',true);
                $cnt++;
                if ( $id == 'pe_namepass' ) {
                    $navbar->add_menuitem('Groups','showhideProfileEditorDiv("'.'pe_usergroup'.'",'.$cnt.');return false;',true);
                    $cnt++;
                }
            }
        }
        $navbar->set_selected($LANG_MYACCOUNT['pe_namepass']);
    }
    $userform->set_var('navbar', $navbar->generate());
    $userform->set_var('no_javascript_warning',$LANG04[150]);

    if (!empty ($msg)) {
        $retval .= COM_startBlock ($LANG28[22], '',
                           COM_getBlockTemplate ('_msg_block', 'header'))
                . $MESSAGE[$msg]
                . COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    }

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
            $retval .= COM_startBlock ($LANG28[1], '',
                               COM_getBlockTemplate ('_msg_block', 'header'));
            $retval .= $LANG_ACCESS['editrootmsg'];
            COM_accessLog("User {$_USER['username']} tried to edit a Root account with insufficient privileges.");
            $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
            return $retval;
        }
        $curtime = COM_getUserDateTimeFormat($U['regdate']);
        $lastlogin = DB_getItem ($_TABLES['userinfo'], 'lastlogin', "uid = $uid");
        $lasttime = COM_getUserDateTimeFormat ($lastlogin);
        $display_name = COM_getDisplayName ($uid);
        $menuText = 'Editing User: ' . $U['username'];
        if ($U['fullname'] != '' ) {
            $menuText .= ' - ' . $U['fullname'];
        }
    } else {
        $U['uid'] = '';
        $uid = '';
        $U['cookietimeout'] = 2678400;
        $U['etids'] = '-';
        $U['status'] = USER_ACCOUNT_AWAITING_ACTIVATION;
        $U['emailfromadmin'] = 1;
        $U['emailfromuser'] = 1;
        $U['showonline'] = 1;
        $U['maxstories'] = 0;
        $U['dfid'] = 0;
        $U['search_result_format'] = $_CONF['search_style'];
        $U['commentmode'] = $_CONF['comment_mode'];
        $U['commentorder'] = 'ASC';
        $U['commentlimit'] = 100;
        $curtime =  COM_getUserDateTimeFormat();
        $lastlogin = '';
        $lasttime = '';
        $U['status'] = USER_ACCOUNT_ACTIVE;
        $newuser = 1;
        $userform->set_var('newuser',1);
        $menuText = 'Creating New Account';
    }

    // now let's check to see if any post vars are set in the event we are returning from an error...

    if ( isset($_POST['new_username']) )
        $U['username']       = trim(COM_stripslashes($_POST['new_username']));
    if ( isset($_POST['fullname']) )
        $U['fullname']       = trim(COM_stripslashes($_POST['fullname']));
    if ( isset($_POST['userstatus'] ) )
        $U['status']     = COM_applyFilter($_POST['userstatus'],true);
    if ( isset($_POST['cooktime'] ) )
        $U['cookietimeout'] = COM_applyFilter($_POST['cooktime'],true);
    if ( isset($_POST['email'] ) )
        $U['email']          = trim(COM_stripslashes($_POST['email']));
    if ( isset($_POST['homepage']) )
        $U['homepage']       = trim(COM_stripslashes($_POST['homepage']));
    if ( isset($_POST['location']) )
        $U['location']       = trim(COM_stripslashes($_POST['location']));
    if ( isset($_POST['sig']) )
        $U['sig']            = trim(COM_stripslashes($_POST['sig']));
    if ( isset($_POST['about'] ) )
        $U['about'] = trim(COM_stripslashes($_POST['about']));
    if ( isset($_POST['pgpkey']) )
        $U['pgpkey']         = trim(COM_stripslashes($_POST['pgpkey']));
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
    if ( isset($_POST['search_result_format'] ) )
        $U['search_result_format']     = COM_applyFilter($_POST['search_result_format']);
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
    if ( isset($_POST['noboxes'] ) )
        $U['noboxes']        = $_POST['noboxes'] == 'on' ? 1 : 0;
    if ( isset($_POST['showonline'] ) )
        $U['showonline']     = $_POST['showonline'] == 'on' ? 1 : 0;
    if ( isset($_POST['topic_order']) )
        $U['topic_order']    = $_POST['topic_order'] == 'ASC' ? 'ASC' : 'DESC';

    $retval .= ADMIN_createMenu(
        $menu_arr,
        '&nbsp;<br/><strong>'.$menuText.'</strong>',
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

    if (!empty($uid) && ($uid != $_USER['uid']) && SEC_hasRights('user.delete')) {
        $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                   . '" name="mode"%s' . XHTML . '>';
        $jsconfirm = ' onclick="return confirm(\'' . $MESSAGE[76] . '\');"';
        $userform->set_var('delete_option',sprintf ($delbutton, $jsconfirm));
        $userform->set_var('delete_option_no_confirmation',sprintf ($delbutton, ''));
    }

    $userform->set_var('gltoken_name', CSRF_TOKEN);
    $userform->set_var('gltoken', SEC_createToken());

    $retval .= $userform->finish ($userform->parse ('output', 'user'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

function USER_accountPanel($U,$newuser = 0)
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG04,$LANG28;

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
    $userform->set_var('fullname_value', htmlspecialchars($U['fullname']));

    $selection  = '<select id="cooktime" name="cooktime">' . LB;
    $selection .= COM_optionList($_TABLES['cookiecodes'],'cc_value,cc_descr',$U['cookietimeout'], 0);
    $selection .= '</select>';

    $userform->set_var('cooktime_selector', $selection);
    $userform->set_var('email_value', htmlspecialchars ($U['email']));

    $statusarray = array(USER_ACCOUNT_AWAITING_ACTIVATION => $LANG28[43],
                         USER_ACCOUNT_ACTIVE              => $LANG28[45]
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
    $statusselect = '<select name="userstatus" id="userstatus">';
    foreach ($statusarray as $key => $value) {
        $statusselect .= '<option value="' . $key . '"';
        if ($key == $U['status']) {
            $statusselect .= ' selected="selected"';
        }
        $statusselect .= '>' . $value . '</option>' . LB;
    }
    $statusselect .= '</select><input type="hidden" name="oldstatus" value="'.$U['status'] . '"/>';
    $userform->set_var('user_status', $statusselect);

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

    $uid = $U['uid'];

    USES_lib_admin();

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
        $thisUsersGroups = SEC_getUserGroups ();
        $remoteGroup = DB_getItem ($_TABLES['groups'], 'grp_id',"grp_name='Remote Users'");
        if (!empty ($remoteGroup)) {
            $thisUsersGroups[] = $remoteGroup;
        }
        $where = 'grp_id IN (' . implode (',', $thisUsersGroups) . ')';

        $header_arr = array(
                        array('text' => $LANG28[86], 'field' => 'checkbox', 'sort' => false),
                        array('text' => $LANG_ACCESS['groupname'], 'field' => 'grp_name', 'sort' => true),
                        array('text' => $LANG_ACCESS['description'], 'field' => 'grp_descr', 'sort' => true)
        );
        $defsort_arr = array('field' => 'grp_name', 'direction' => 'asc');

        $text_arr = array('has_menu' => false,
                          'title' => '', 'instructions' => '',
                          'icon' => '', 'form_url' => $form_url,
                          'inline' => true
        );

        $sql = "SELECT grp_id, grp_name, grp_descr FROM {$_TABLES['groups']} WHERE " . $where;
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
                                   'ADMIN_getListField_usergroups',
                                   $header_arr, $text_arr, $query_arr,
                                   $defsort_arr, '', $al_selected);

        $userform->set_var('group_options', $groupoptions);

        $userform->parse('group_edit', 'groupedit', true);
    } else {
        // user doesn't have the rights to edit a user's groups so set to -1
        // so we know not to handle the groups array when we save
        $userform->set_var('group_edit',
                '<input type="hidden" name="groups" value="-1"' . XHTML . '>');
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
        'lang_pgpkey'               => $LANG04[8]
    ));

    if ( $_CONF['allow_user_photo'] == 1 ) {
        $userform->set_var('lang_userphoto',$LANG04[77]);
    }

    $userform->set_var('homepage_value',htmlspecialchars (COM_killJS ($U['homepage'])));
    $userform->set_var('location_value',htmlspecialchars (strip_tags ($U['location'])));
    $userform->set_var('signature_value',htmlspecialchars ($U['sig']));
    $userform->set_var('about_value', htmlspecialchars ($U['about']));
    $userform->set_var('pgpkey_value', htmlspecialchars ($U['pgpkey']));

    if ($_CONF['allow_user_photo'] == 1) {
        if ( !empty($uid) && $uid > 1 ) {
            $photo = USER_getPhoto ($uid, $U['photo'], $U['email'], -1);
            if (empty ($photo)) {
                $userform->set_var('display_photo', '');
            } else {
                if (empty ($U['photo'])) { // external avatar
                    $photo = '<br/>' . $photo;
                } else { // uploaded photo - add delete option
                    $photo = '<br/>' . $photo . '<br/>' . $LANG04[79]
                           . '&nbsp;<input type="checkbox" name="delete_photo"/>'.LB;
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
    }
    $retval = $userform->finish ($userform->parse ('output', 'user'));
    return $retval;
}

function USER_layoutPanel($U, $newuser = 0)
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG_MYACCOUNT, $LANG04,
           $LANG_confignames,  $LANG_configselects;

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
    $userform->set_var('lang_noboxes', $LANG04[44]);
    $userform->set_var('lang_maxstories', $LANG04[43]);
    $userform->set_var('lang_timezone', $LANG04[158]);
    $userform->set_var('lang_dateformat', $LANG04[42]);
    $userform->set_var('lang_search_format',$LANG_confignames['Core']['search_show_type']);

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

        $userform->set_var('language_selector', $selection);
    } else {
        $userform->set_var('language_selector', $_CONF['language']);
    }
    if ($_CONF['allow_user_themes'] == 1) {
        $selection = '<select id="theme" name="theme">' . LB;
        if (empty ($U['theme'])) {
            $usertheme = $_CONF['theme'];
        } else {
            $usertheme = $U['theme'];
        }
        $themeFiles = COM_getThemes ();
        usort ($themeFiles,create_function ('$a,$b', 'return strcasecmp($a,$b);'));

        foreach ($themeFiles as $theme) {
            $selection .= '<option value="' . $theme . '"';
            if ($usertheme == $theme) {
                $selection .= ' selected="selected"';
            }
            $words = explode ('_', $theme);
            $bwords = array ();
            foreach ($words as $th) {
                if ((strtolower ($th{0}) == $th{0}) &&
                    (strtolower ($th{1}) == $th{1})) {
                    $bwords[] = strtoupper ($th{0}) . substr ($th, 1);
                } else {
                    $bwords[] = $th;
                }
            }
            $selection .= '>' . implode (' ', $bwords) . '</option>' . LB;
        }
        $selection .= '</select>';
        $userform->set_var('theme_selector', $selection);
    } else {
        $userform->set_var('theme_selector',$_CONF['theme']);
    }
    if ($U['noicons'] == '1') {
        $userform->set_var('noicons_checked', 'checked="checked"');
    } else {
        $userform->set_var('noicons_checked', '');
    }

    if ($U['noboxes'] == 1) {
        $userform->set_var('noboxes_checked', 'checked="checked"');
    } else {
        $userform->set_var('noboxes_checked', '');
    }

    $userform->set_var('maxstories_value', $U['maxstories']);

    // Timezone
    require_once $_CONF['path_system'] . 'classes/timezoneconfig.class.php';

    if ( isset($U['tzid']) ) {
        $timezone = $U['tzid'];
    } else {
        $timezone = TimeZoneConfig::getUserTimeZone();
    }
    $selection = TimeZoneConfig::getTimeZoneDropDown($timezone,
            array('id' => 'tzid', 'name' => 'tzid'));

    $userform->set_var('timezone_selector', $selection);

    $selection = '<select id="dfid" name="dfid">' . LB
               . COM_optionList ($_TABLES['dateformats'], 'dfid,description',
                                 $U['dfid']) . '</select>';
    $userform->set_var('dateformat_selector', $selection);
    $search_result_select  = '<select name="search_result_format" id="search_result_format">'.LB;
    foreach ($LANG_configselects['Core'][18] AS $name => $type ) {
        $search_result_select .= '<option value="'. $type . '"' . ($U['search_result_format'] == $type ? 'selected="selected"' : '') . '>'.$name.'</option>'.LB;
    }
    $search_result_select .= '</select>';
    $userform->set_var('search_result_select',$search_result_select);

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

    $selection = '<select id="commentmode" name="commentmode">';
    $selection .= COM_optionList ($_TABLES['commentmodes'], 'mode,name',
                                  $C['commentmode']);
    $selection .= '</select>';
    $userform->set_var('displaymode_selector', $selection);

    $selection = '<select id="commentorder" name="commentorder">';
    $selection .= COM_optionList ($_TABLES['sortcodes'], 'code,name',
                                  $C['commentorder']);
    $selection .= '</select>';
    $userform->set_var('sortorder_selector', $selection);
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

    $userform->set_var('lang_exclude_title', $LANG04[136]);
    $userform->set_var('lang_excluded_items_title', $LANG04[137]);
    $userform->set_var('lang_excluded_items', $LANG04[54]);
    $userform->set_var('lang_topics', $LANG04[48]);
    $userform->set_var('lang_authors', $LANG04[56]);
    $userform->set_var('lang_digest_top_header', $LANG04[131]);
    $userform->set_var('lang_digest_help_header', $LANG04[132]);
    $userform->set_var('lang_emailedtopics', $LANG04[76]);
    $userform->set_var('lang_boxes_title', $LANG04[144]);
    $userform->set_var('lang_boxes_help_title', $LANG04[143]);
    $userform->set_var('lang_boxes', $LANG04[55]);

    if ( $_CONF['hide_exclude_content'] != 1 ) {
        $permissions = COM_getPermSQL ('',$uid);
        $userform->set_var('exclude_topic_checklist',
             COM_checkList($_TABLES['topics'], 'tid,topic', $permissions, $U['tids'], 'topics'));

        if (($_CONF['contributedbyline'] == 1) && ($_CONF['hide_author_exclusion'] == 0)) {
            $userform->set_var('lang_authors', $LANG04[56]);
            $sql = "SELECT DISTINCT story.uid, users.username,users.fullname FROM {$_TABLES['stories']} story, {$_TABLES['users']} users WHERE story.uid = users.uid";
            if ($_CONF['show_fullname'] == 1) {
                $sql .= ' ORDER BY users.fullname';
            } else {
                $sql .= ' ORDER BY users.username';
            }
            $query = DB_query ($sql);
            $nrows = DB_numRows ($query );
            $authors = explode (' ', $U['aids']);

            $selauthors = '';
            for( $i = 0; $i < $nrows; $i++ ) {
                $B = DB_fetchArray ($query);
                $selauthors .= '<option value="' . $B['uid'] . '"';
                if (in_array (sprintf ('%d', $B['uid']), $authors)) {
                   $selauthors .= ' selected';
                }
                $selauthors .= '>' . COM_getDisplayName ($B['uid'], $B['username'],$B['fullname']).'</option>' . LB;
            }

            if (DB_count($_TABLES['topics']) > 10) {
                $Selboxsize = intval (DB_count ($_TABLES['topics']) * 1.5);
            } else {
                $Selboxsize = 15;
            }
            $userform->set_var('exclude_author_checklist', '<select name="selauthors[]" multiple="multiple" size="'. $Selboxsize. '">' . $selauthors . '</select>');
        } else {
            $userform->set_var('lang_authors', '');
            $userform->set_var('exclude_author_checklist', '');
        }
        if (!empty($uid) && $uid > 1 ) {
            $userform->set_var('plugin_content_exclude',PLG_profileEdit($uid,'content','exclude'));
        }
    } else {
        $userform->set_var('exclude_topic_checklist','');
        $userform->set_var('exclude_author_checklist','');
        $userform->set_var('plugin_content_exclude','');
    }

    // daily digest block
    if ($_CONF['emailstories'] == 1) {
        if ( !empty($uid) && $uid > 1 ) {
            $user_etids = DB_getItem ($_TABLES['userindex'], 'etids',"uid = $uid");
        } else {
            $user_etids = '-';
        }
        if (empty ($user_etids)) { // an empty string now means "all topics"
            $user_etids = buildTopicList ();
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

    if ( $_CONF['hide_exclude_content'] != 1 ) {
        // boxes block
        $selectedblocks = '';
        if (strlen($U['boxes']) > 0) {
            $blockresult = DB_query("SELECT bid FROM {$_TABLES['blocks']} WHERE bid NOT IN (" . str_replace(' ',',',trim($U['boxes'])) . ")");
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
        $userform->set_var('boxes_checklist', COM_checkList ($_TABLES['blocks'],
                'bid,title,type', $whereblock, $selectedblocks,'blocks'));
        if (!empty($uid) && $uid > 1 ) {
            $userform->set_var('plugin_content_boxes',PLG_profileEdit($uid,'content','boxes'));
        }
    } else {
        $userform->set_var('boxes_block', '');
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
* Build a list of all topics the current user has access to
*
* @return   string   List of topic IDs, separated by spaces
*
*/
function buildTopicList ()
{
    global $_TABLES;

    $topics = '';

    $result = DB_query ("SELECT tid FROM {$_TABLES['topics']}");
    $numrows = DB_numRows ($result);
    for ($i = 1; $i <= $numrows; $i++) {
        $A = DB_fetchArray ($result);
        if (SEC_hasTopicAccess ($A['tid'])) {
            if ($i > 1) {
                $topics .= ' ';
            }
            $topics .= $A['tid'];
        }
    }

    return $topics;
}



function listusers()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG04, $LANG28, $_IMAGE_TYPE;

    require_once $_CONF['path_system'] . 'lib-admin.php';

    $retval = '';

    if ($_CONF['lastlogin']) {
        $login_text = $LANG28[41];
        $login_field = 'lastlogin';
    } else {
        $login_text = $LANG28[40];
        $login_field = 'regdate';
    }

    $header_arr = array(      # display 'text' and use table field 'field'
                    array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false),
                    array('text' => $LANG28[37], 'field' => $_TABLES['users'] . '.uid', 'sort' => true),
                    array('text' => $LANG28[3], 'field' => 'username', 'sort' => true),
                    array('text' => $LANG28[4], 'field' => 'fullname', 'sort' => true),
                    array('text' => $login_text, 'field' => $login_field, 'sort' => true),
                    array('text' => $LANG28[7], 'field' => 'email', 'sort' => true)
    );

    if ($_CONF['user_login_method']['openid'] ||
        $_CONF['user_login_method']['3rdparty']) {
        $header_arr[] = array('text' => $LANG04[121], 'field' => 'remoteservice', 'sort' => true);
    }

    $defsort_arr = array('field'     => $_TABLES['users'] . '.uid',
                         'direction' => 'ASC');

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/user.php?mode=edit',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'] . '/user.php?mode=importform',
              'text' => $LANG28[23]),
        array('url' => $_CONF['site_admin_url'] . '/user.php?mode=batchdelete',
              'text' => $LANG28[54]),
        array('url' => $_CONF['site_admin_url'] . '/prefeditor.php',
              'text' => $LANG28[95]),
        array('url' => $_CONF['site_admin_url'],
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
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . '/user.php',
        'help_url'   => ''
    );

    $join_userinfo   = '';
    $select_userinfo = '';
    if ($_CONF['lastlogin']) {
        $join_userinfo .= "LEFT JOIN {$_TABLES['userinfo']} ON {$_TABLES['users']}.uid={$_TABLES['userinfo']}.uid ";
        $select_userinfo .= ",lastlogin";
    }
    if ($_CONF['user_login_method']['openid'] ||
        $_CONF['user_login_method']['3rdparty']) {
        $select_userinfo .= ',remoteservice';
    }
    $sql = "SELECT {$_TABLES['users']}.uid,username,fullname,email,photo,status,regdate$select_userinfo "
         . "FROM {$_TABLES['users']} $join_userinfo WHERE 1=1";

    $query_arr = array('table' => 'users',
                       'sql' => $sql,
                       'query_fields' => array('username', 'email', 'fullname'),
                       'default_filter' => "AND {$_TABLES['users']}.uid > 1");

    $retval .= ADMIN_list('user', 'ADMIN_getListField_users', $header_arr,
                          $text_arr, $query_arr, $defsort_arr);
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
function saveusers ($uid)
{
    global $_CONF, $_TABLES, $_USER, $LANG28, $_USER_VERBOSE;

    $retval = '';
    $userChanged = false;

    if ($_USER_VERBOSE) COM_errorLog("**** entering saveusers****",1);
    if ($_USER_VERBOSE) COM_errorLog("group size at beginning = " . sizeof($groups),1);

    $uid            = COM_applyFilter($_POST['uid'],true);
    if ( $uid == 0 ) {
        $uid = '';
    }
    $regdate        = COM_applyFilter($_POST['regdate'],true);
    $username       = trim(COM_stripslashes($_POST['new_username']));
    $fullname       = trim(COM_stripslashes($_POST['fullname']));
    $userstatus     = COM_applyFilter($_POST['userstatus'],true);
    $oldstatus      = COM_applyFilter($_POST['oldstatus'],true);
    $passwd         = trim(COM_stripslashes($_POST['passwd']));
    $passwd_conf    = trim(COM_stripslashes($_POST['passwd_conf']));
    $cooktime       = COM_applyFilter($_POST['cooktime'],true);
    $email          = trim(COM_stripslashes($_POST['email']));
    $email_conf     = trim(COM_stripslashes($_POST['email_conf']));
    $groups         = $_POST['groups'];
    $homepage       = trim(COM_stripslashes($_POST['homepage']));
    $location       = trim(COM_stripslashes($_POST['location']));
    $photo          = $_POST['photo'];
    $delete_photo   = $_POST['delete_photo'] == 'on' ? 1 : 0;
    $sig            = trim(COM_stripslashes($_POST['sig']));
    $about          = trim(COM_stripslashes($_POST['about']));
    $pgpkey         = trim(COM_stripslashes($_POST['pgpkey']));
    $language       = trim(COM_applyFilter($_POST['language']));
    $theme          = trim(COM_applyFilter($_POST['theme']));
    $maxstories     = COM_applyFilter($_POST['maxstories'],true);
    $tzid           = COM_applyFilter($_POST['tzid']);
    $dfid           = COM_applyFilter($_POST['dfid'],true);
    $search_fmt     = COM_applyFilter($_POST['search_result_format']);
    $commentmode    =  COM_applyFilter($_POST['commentmode']);
    $commentorder   = $_POST['commentorder'] == 'DESC' ? 'DESC' : 'ASC';
    $commentlimit   = COM_applyFilter($_POST['commentlimit'],true);
    $emailfromuser  = $_POST['emailfromuser'] == 'on' ? 1 : 0;
    $emailfromadmin = $_POST['emailfromadmin'] == 'on' ? 1 : 0;
    $noicons        = $_POST['noicons'] == 'on' ? 1 : 0;
    $noboxes        = $_POST['noboxes'] == 'on' ? 1 : 0;
    $showonline     = $_POST['showonline'] == 'on' ? 1 : 0;
    $topic_order    = $_POST['topic_order'] == 'ASC' ? 'ASC' : 'DESC';
    $maxstories     = COM_applyFilter($_POST['maxstories'],true);
    $mode           = $_POST['mode'];
    $newuser        = COM_applyFilter($_POST['newuser'],true);

    if ( $uid == 1 ) {
        return listusers();
    }

    if ( $uid == '' || $uid < 2 || $newuser == 1 ) {
        if (empty($passwd) ) {
            return edituser($uid,504);
        }
        if (empty($email) ) {
            return edituser($uid,505);
        }
    }
    if ( $username == '') {
        return edituser($uid,506);
    }
    if ( $email == '' ) {
        return edituser($uid,507);
    }
    if ($passwd != $passwd_conf) { // passwords don't match
        return edituser ($uid, 67);
    }
    if ($email != $email_conf) {
        return edituser($uid,508);
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
           return edituser ($uid, 52);
        }
        $uname = addslashes ($username);
        if (empty ($uid)) {
            $ucount = DB_getItem ($_TABLES['users'], 'COUNT(*)',
                                  "username = '$uname'");
        } else {
            $uservice = DB_getItem ($_TABLES['users'], 'remoteservice', "uid = $uid");
            if ($uservice != '') {
                $uservice = addslashes($uservice);
                $ucount = DB_getItem ($_TABLES['users'], 'COUNT(*)',
                            "username = '$uname' AND uid <> $uid AND remoteservice = '$uservice'");
            } else {
                $ucount = DB_getItem ($_TABLES['users'], 'COUNT(*)',
                                  "username = '$uname' AND uid <> $uid AND (remoteservice = '' OR remoteservice IS NULL)");
            }
        }
        if ($ucount > 0) {
            // Admin just changed a user's username to one that already exists
            return edituser ($uid, 51);
        }

        $emailaddr = addslashes($email);
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
            return edituser($uid, 56);
        }

        if ($_CONF['custom_registration'] && function_exists('CUSTOM_userCheck')) {
            $ret = CUSTOM_userCheck($username, $email);
            if (! empty($ret)) {
                // need a numeric return value - otherwise use default message
                if (! is_numeric($ret['number'])) {
                    $ret['number'] = 97;
                }
                return edituser($uid, $ret['number']);
            }
        }

        // Let plugins have a chance to decide what to do before saving the user, return errors.
        $msg = PLG_itemPreSave ('useredit', $username);
        if (!empty ($msg)) {
            // need a numeric return value - otherwise use default message
            if (! is_numeric($msg)) {
                $msg = 97;
            }
            return edituser($uid, $msg);
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

            $uid = USER_createAccount ($username, $email, $passwd2, $fullname,
                                       $homepage);
            if ($uid > 1) {
                DB_query("UPDATE {$_TABLES['users']} SET status = $userstatus WHERE uid = $uid");
            }
            if ( isset($_POST['emailuser']) ) {
                USER_createAndSendPassword ($username, $email, $uid, $passwd);
            }
            if ( $uid < 2 ) {
                return edituser('',509);
            }
            $newuser = 1;
        }
        // at this point, we have a valid user...

        // Filter some of the text entry fields to ensure they don't cause problems...

        $fullname = strip_tags($fullname);
        $about    = strip_tags($about);
        $pgpkey   = strip_tags($pgpkey);

        $curphoto = DB_getItem($_TABLES['users'],'photo',"uid = $uid");
        if (!empty ($curphoto) && ($delete_photo)) {
            USER_deletePhoto ($curphoto);
            $curphoto = '';
        }

        if (($_CONF['allow_user_photo'] == 1) && !empty ($curphoto)) {
            $curusername = DB_getItem ($_TABLES['users'], 'username',"uid = $uid");
            if ($curusername != $username) {
                // user has been renamed - rename the photo, too
                $newphoto = preg_replace ('/' . $curusername . '/', $username, $curphoto, 1);
                $imgpath = $_CONF['path_images'] . 'userphotos/';
                if (rename ($imgpath . $curphoto,
                            $imgpath . $newphoto) === false) {
                    $display = COM_siteHeader ('menu', $LANG28[22]);
                    $display .= COM_errorLog ('Could not rename userphoto "'
                                    . $curphoto . '" to "' . $newphoto . '".');
                    $display .= COM_siteFooter ();
                    return $display;
                }
                $curphoto = $newphoto;
            }
        }

        // update users table

        $sql = "UPDATE {$_TABLES['users']} SET ".
            "username = '".addslashes($username)."',".
            "fullname = '".addslashes($fullname)."',".
            "passwd   = '".addslashes($passwd2)."',".
            "email    = '".addslashes($email)."',".
            "homepage = '".addslashes($homepage)."',".
            "sig      = '".addslashes($sig)."',".
            "photo    = '".addslashes($curphoto)."',".
            "cookietimeout = $cooktime,".
            "theme    = '".addslashes($theme)."',".
            "language = '".addslashes($language)."',".
            "status   = $userstatus WHERE uid = $uid;";

        DB_query($sql);

        // update userprefs

        $sql = "UPDATE {$_TABLES['userprefs']} SET ".
            "noicons = $noicons,".
            "dfid    = $dfid,".
            "tzid    = '".addslashes($tzid)."',".
            "emailstories = 0,".
            "emailfromadmin = $emailfromadmin,".
            "emailfromuser  = $emailfromuser,".
            "showonline = $showonline,".
            "search_result_format = '".addslashes($search_fmt)."' WHERE uid=$uid;";

        DB_query($sql);

        // userinfo table

        $sql = "UPDATE {$_TABLES['userinfo']} SET ".
            "about      = '".addslashes($about)."',".
            "location   = '".addslashes($location)."',".
            "pgpkey     = '".addslashes($pgpkey)."' WHERE uid=$uid;";

        DB_query($sql);

        // userindex table

        $AIDS  = @array_values($_POST['selauthors']);
        $BOXES = @array_values($_POST['blocks']);
        $ETIDS = @array_values($_POST['dgtopics']);
        $allowed_etids = buildTopicList ();
        $AETIDS = explode (' ', $allowed_etids);

        $tids = '';
        if (sizeof ($TIDS) > 0) {
            $tids = addslashes (implode (' ', array_intersect ($AETIDS, $TIDS)));
        }
        $aids = '';
        if (sizeof ($AIDS) > 0) {
            foreach ($AIDS as $key => $val) {
                $AIDS[$key] = intval($val);
            }
            $aids = addslashes (implode (' ', $AIDS));
        }
        $selectedblocks = '';
        $selectedBoxes = array();
        if (count ($BOXES) > 0) {
            foreach ($BOXES AS $key => $val) {
                $BOXES[$key] = intval($val);
            }
            $boxes = addslashes(implode(',', $BOXES));

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

        $etids = '-';
        if (sizeof ($ETIDS) > 0) {
            $etids = addslashes (implode (' ', array_intersect ($AETIDS, $ETIDS)));
        } else {
            $etids = '-';
        }
        DB_save($_TABLES['userindex'],"uid,tids,aids,boxes,noboxes,maxstories,etids","$uid,'$tids','$aids','$selectedblocks',$noboxes,$maxstories,'$etids'");

        // usercomment

        DB_save($_TABLES['usercomment'],'uid,commentmode,commentorder,commentlimit',"$uid,'$commentmode','$commentorder',".intval($commentlimit));

        if ($_CONF['custom_registration'] AND (function_exists('CUSTOM_userSave'))) {
            CUSTOM_userSave($uid);
        }
        if( ($_CONF['usersubmission'] == 1) && ($oldstatus == USER_ACCOUNT_AWAITING_APPROVAL)
               && ($userstatus == USER_ACCOUNT_ACTIVE) ) {
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
                    COM_accessLog ("User {$_USER['username']} ({$_USER['uid']}) just tried to give Root permissions to user $username.");
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
                COM_errorLog("deleting all group_assignments for user $uid/$username",1);
            }

            // remove user from all groups that the User Admin is a member of
            $UserAdminGroups = SEC_getUserGroups ();
            $whereGroup = 'ug_main_grp_id IN ('
                        . implode (',', $UserAdminGroups) . ')';
            DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE (ug_uid = $uid) AND " . $whereGroup);

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

            foreach ($groups as $userGroup) {
                if (in_array ($userGroup, $UserAdminGroups)) {
                    if ($_USER_VERBOSE) {
                        COM_errorLog ("adding group_assignment " . $userGroup
                                      . " for $username", 1);
                    }
                    $sql = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid) VALUES ($userGroup, $uid)";
                    DB_query ($sql);
                }
            }
        }

        if ( $newuser == 0 ) {
            PLG_profileSave($uid);
        }

        if ($userChanged) {
            PLG_userInfoChanged ($uid);
        }
        CACHE_remove_instance('stmenu');
        $errors = DB_error();
        if (empty($errors)) {
            echo PLG_afterSaveSwitch (
                $_CONF['aftersave_user'],
                "{$_CONF['site_url']}/users.php?mode=profile&uid=$uid",
                'user',
                21
            );
        } else {
            $retval .= COM_siteHeader ('menu', $LANG28[22]);
            $retval .= COM_errorLog ('Error in saveusers in '.$_CONF['site_admin_url'] . '/user.php');
            $retval .= COM_siteFooter ();
            echo $retval;
            exit;
        }
    } else {
        $retval = COM_siteHeader('menu', $LANG28[1]);
        $retval .= COM_errorLog($LANG28[10]);
        if (DB_count($_TABLES['users'],'uid',$uid) > 0) {
            $retval .= edituser($uid);
        } else {
            $retval .= edituser();
        }
        $retval .= COM_siteFooter();
        echo $retval;
        exit;
    }

    if ($_USER_VERBOSE) COM_errorLog("***************leaving saveusers*****************",1);

    return $retval;
}



/**
* This function allows the batch deletion of users that are inactive
* It shows the form that will filter user that will be deleted
*
* @return   string          HTML Form
*/
function batchdelete()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG01, $LANG28, $_IMAGE_TYPE;

    $display = '';
    if (!$_CONF['lastlogin']) {
        $retval = '<br' . XHTML . '>'. $_LANG28[55];
        return $retval;
    }

    require_once $_CONF['path_system'] . 'lib-admin.php';

    $usr_type = '';
    if (isset($_REQUEST['usr_type'])) {
        $usr_type = COM_applyFilter($_REQUEST['usr_type']);
    } else {
        $usr_type = 'phantom';
    }
    $usr_time_arr = array();
    $usr_time = '';
    if (isset($_REQUEST['usr_time'])) {
        $usr_time_arr = $_REQUEST['usr_time'];
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
    $user_templates->set_file (array ('form' => 'batchdelete.thtml',
                                      'options' => 'batchdelete_options.thtml',
                                      'reminder' => 'reminder.thtml'));
    $user_templates->set_var( 'xhtml', XHTML );
    $user_templates->set_var('site_url', $_CONF['site_url']);
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
                    array('text' => $LANG28[37], 'field' => $_TABLES['users'] . '.uid', 'sort' => true),
                    array('text' => $LANG28[3], 'field' => 'username', 'sort' => true),
                    array('text' => $LANG28[4], 'field' => 'fullname', 'sort' => true)
    );

    switch ($usr_type) {
        case 'phantom':
            $header_arr[] = array('text' => $LANG28[14], 'field' => 'regdate', 'sort' => true);
            $header_arr[] = array('text' => $LANG28[41], 'field' => 'lastlogin_short', 'sort' => true);
            $header_arr[] = array('text' => $LANG28[67], 'field' => 'phantom_date', 'sort' => true);
            $list_sql = ", UNIX_TIMESTAMP()- UNIX_TIMESTAMP(regdate) as phantom_date";
            $filter_sql = "lastlogin = 0 AND UNIX_TIMESTAMP()- UNIX_TIMESTAMP(regdate) > " . ($usr_time * 2592000) . " AND";
            $sort = 'regdate';
            break;
        case 'short':
            $header_arr[] = array('text' => $LANG28[14], 'field' => 'regdate', 'sort' => true);
            $header_arr[] = array('text' => $LANG28[41], 'field' => 'lastlogin_short', 'sort' => true);
            $header_arr[] = array('text' => $LANG28[68], 'field' => 'online_hours', 'sort' => true);
            $header_arr[] = array('text' => $LANG28[69], 'field' => 'offline_months', 'sort' => true);
            $list_sql = ", (lastlogin - UNIX_TIMESTAMP(regdate)) AS online_hours, (UNIX_TIMESTAMP() - lastlogin) AS offline_months";
            $filter_sql = "lastlogin > 0 AND lastlogin - UNIX_TIMESTAMP(regdate) < 86400 "
                         . "AND UNIX_TIMESTAMP() - lastlogin > " . ($usr_time * 2592000) . " AND";
            $sort = 'lastlogin';
            break;
        case 'old':
            $header_arr[] = array('text' => $LANG28[41], 'field' => 'lastlogin_short', 'sort' => true);
            $header_arr[] = array('text' => $LANG28[69], 'field' => 'offline_months', 'sort' => true);
            $list_sql = ", (UNIX_TIMESTAMP() - lastlogin) AS offline_months";
            $filter_sql = "lastlogin > 0 AND (UNIX_TIMESTAMP() - lastlogin) > " . ($usr_time * 2592000) . " AND";
            $sort = 'lastlogin';
            break;
        case 'recent':
            $header_arr[] = array('text' => $LANG28[14], 'field' => 'regdate', 'sort' => true);
            $header_arr[] = array('text' => $LANG28[41], 'field' => 'lastlogin_short', 'sort' => true);
            $list_sql = "";
            $filter_sql = "(UNIX_TIMESTAMP() - UNIX_TIMESTAMP(regdate)) < " . ($usr_time * 2592000) . " AND";
            $sort = 'regdate';
            break;
    }

    $header_arr[] = array('text' => $LANG28[7], 'field' => 'email', 'sort' => true);
    $header_arr[] = array('text' => 'Reminders', 'field' => 'num_reminders', 'sort' => true);
/*
    $menu_arr = array (
                    array('url' => $_CONF['site_admin_url'] . '/user.php',
                          'text' => $LANG28[11]),
                    array('url' => $_CONF['site_admin_url'] . '/user.php?mode=importform',
                          'text' => $LANG28[23]),
                    array('url' => $_CONF['site_admin_url'] . '/user.php?mode=edit',
                          'text' => $LANG_ADMIN['create_new']),
                    array('url' => $_CONF['site_admin_url'] . '/prefeditor.php',
                          'text' => $LANG28[95]),

                    array('url' => $_CONF['site_admin_url'],
                          'text' => $LANG_ADMIN['admin_home'])
    );
*/
    $text_arr = array('has_menu'     => true,
                      'has_extras'   => true,
                      'title'        => $LANG28[54],
                      'instructions' => "$desc",
                      'icon'         => $_CONF['layout_url'] . '/images/icons/user.' . $_IMAGE_TYPE,
                      'form_url'     => $_CONF['site_admin_url'] . "/user.php?mode=batchdelete&amp;usr_type=$usr_type&amp;usr_time=$usr_time",
                      'help_url'     => ''
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
    $listoptions = array('chkdelete' => true, 'chkfield' => 'uid');

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/user.php',
              'text' => $LANG28[11]),
        array('url' => $_CONF['site_admin_url'] . '/user.php?mode=edit',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'] . '/user.php?mode=importform',
              'text' => $LANG28[23]),
        array('url' => $_CONF['site_admin_url'] . '/prefeditor.php',
                          'text' => $LANG28[95]),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $display .= ADMIN_createMenu(
        $menu_arr,
        $desc,
        $_CONF['layout_url'] . '/images/icons/user.' . $_IMAGE_TYPE
    );

    $user_templates->set_var('lang_reminder', $LANG28[77]);
    $user_templates->set_var('action_reminder', $LANG28[78]);
    $user_templates->parse('test', 'reminder');

    $form_arr['top'] = $user_templates->get_var('test');
    $token = SEC_createToken();
    $form_arr['bottom'] = "<input type=\"hidden\" name=\"" . CSRF_TOKEN
                        . "\" value=\"{$token}\"" . XHTML . ">";
    $display .= ADMIN_list('user', 'ADMIN_getListField_users', $header_arr,
                           $text_arr, $query_arr, $defsort_arr, '', '',
                           $listoptions, $form_arr);

    // $display .= "<input type=\"hidden\" name=\"mode\" value=\"batchdeleteexec\"" . XHTML . "></form>" . LB;

    return $display;
}

/**
* This function deletes the users selected in the batchdeletelist function
*
* @return   string          HTML with success or error message
*
*/
function batchdeleteexec()
{
    global $_CONF, $LANG28;

    $msg = '';
    $user_list = array();
    if (isset($_POST['delitem'])) {
        $user_list = $_POST['delitem'];
    }

    if (count($user_list) == 0) {
        $msg = $LANG28[72] . '<br' . XHTML . '>';
    }
    $c = 0;

    if (isset($user_list) AND is_array($user_list)) {
        foreach($user_list as $delitem) {
            $delitem = COM_applyFilter($delitem);
            if (!USER_deleteAccount ($delitem)) {
                $msg .= "<strong>{$LANG28[2]} $delitem {$LANG28[70]}</strong><br" . XHTML . ">\n";
            } else {
                $c++; // count the deleted users
            }
        }
    }
    CACHE_remove_instance('stmenu');
    // Since this function is used for deletion only, it's necessary to say that
    // zero were deleted instead of just leaving this message away.
    COM_numberFormat($c); // just in case we have more than 999 ...
    $msg .= "{$LANG28[71]}: $c<br" . XHTML . ">\n";

    return $msg;
}


/**
* This function used to send out reminders to users to access the site or account may be deleted
*
* @return   string          HTML with success or error message
*
*/
function batchreminders()
{
    global $_CONF, $_TABLES, $LANG04, $LANG28;

    $msg = '';
    $user_list = array();
    if (isset($_POST['delitem'])) {
        $user_list = $_POST['delitem'];
    }

    if (count($user_list) == 0) {
        $msg = $LANG28[79] . '<br' . XHTML . '>';
    }
    $c = 0;

    if (isset($_POST['delitem']) AND is_array($_POST['delitem'])) {
        foreach($_POST['delitem'] as $delitem) {
            $userid = COM_applyFilter($delitem);
            $useremail = DB_getItem ($_TABLES['users'], 'email', "uid = '$userid'");
            $username = DB_getItem ($_TABLES['users'], 'username', "uid = '$userid'");
            $lastlogin = DB_getItem ($_TABLES['userinfo'], 'lastlogin', "uid = '$userid'");
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
                    $mailtext = $LANG28[83] . "\n\n";
                } else {
                    $mailtext = sprintf($LANG28[82], $lasttime[0]) . "\n\n";
                }
                $mailtext .= sprintf($LANG28[84], $username) . "\n";
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
                DB_query("UPDATE {$_TABLES['users']} SET num_reminders=num_reminders+1 WHERE uid=$userid");
                $c++;
            } else {
                COM_errorLog("Error attempting to send account reminder to use:$username ($userid)");
            }
        }
    }

    // Since this function is used for deletion only, its necessary to say that
    // zero where deleted instead of just leaving this message away.
    COM_numberFormat($c); // just in case we have more than 999)..
    $msg .= "{$LANG28[80]}: $c<br" . XHTML . ">\n";

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
function importusers()
{
    global $_CONF, $_TABLES, $LANG04, $LANG28;

    // Setting this to true will cause import to print processing status to
    // webpage and to the error.log file
    $verbose_import = true;

    $retval = '';

    // Bulk import implies admin authorisation:
    $_CONF['usersubmission'] = 0;

    // First, upload the file
    USES_class_upload();

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
                                  . '/user.php?mode=importform');
            return $retval;
        }
    } else {
        // A problem occurred, print debug information
        $retval = COM_siteHeader ('menu', $LANG28[22]);
        $retval .= COM_startBlock ($LANG28[24], '',
                COM_getBlockTemplate ('_msg_block', 'header'));
        $retval .= $upload->printErrors(false);
        $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
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
            $retval .="<br" . XHTML . "><b>Working on username=$u_name, fullname=$full_name, and email=$email</b><br" . XHTML . ">\n";
            COM_errorLog ("Working on username=$u_name, fullname=$full_name, and email=$email",1);
        }

        // prepare for database
        $userName  = trim ($u_name);
        $fullName  = trim ($full_name);
        $emailAddr = trim ($email);

        if (COM_isEmail ($email)) {
            // email is valid form
            $ucount = DB_count ($_TABLES['users'], 'username',
                                addslashes ($userName));
            $ecount = DB_count ($_TABLES['users'], 'email',
                                addslashes ($emailAddr));

            if (($ucount == 0) && ($ecount == 0)) {
                // user doesn't already exist - pass in optional true for $batchimport parm
                $uid = USER_createAccount ($userName, $emailAddr, '',
                                           $fullName,'','','',true);

                $result = USER_createAndSendPassword ($userName, $emailAddr, $uid);

                if ($result && $verbose_import) {
                    $retval .= "<br" . XHTML . "> Account for <b>$u_name</b> created successfully.<br" . XHTML . ">\n";
                    COM_errorLog("Account for $u_name created successfully",1);
                } else if ($result) {
                    $successes++;
                } else {
                    // user creation failed
                    $retval .= "<br" . XHTML . ">ERROR: There was a problem creating the account for <b>$u_name</b>.<br" . XHTML . ">\n";
                    COM_errorLog("ERROR: here was a problem creating the account for $u_name.",1);
                }
            } else {
                if ($verbose_import) {
                    $retval .= "<br" . XHTML . "><b>$u_name</b> or <b>$email</b> already exists, account not created.<br" . XHTML . ">\n"; // user already exists
                    COM_errorLog("$u_name,$email: username or email already exists, account not created",1);
                }
                $failures++;
            } // end if $ucount == 0 && ecount == 0
        } else {
            if ($verbose_import) {
                $retval .= "<br" . XHTML . "><b>$email</b> is not a valid email address, account not created<br" . XHTML . ">\n"; // malformed email
                COM_errorLog("$email is not a valid email address, account not created",1);
            }
            $failures++;
        } // end if COM_isEmail($email)
    } // end foreach

    unlink ($filename);
    CACHE_remove_instance('stmenu');
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
function display_batchAddform()
{
    global $_CONF, $LANG28;

    $token = SEC_createToken();
    $retval = '<form action="' . $_CONF['site_admin_url']
            . '/user.php" method="post" enctype="multipart/form-data"><div>'
            . $LANG28[29]
            . ': <input type="file" dir="ltr" name="importfile" size="40"'
            . XHTML . '>'
            . '<input type="hidden" name="mode" value="import"' . XHTML . '>'
            . '<input type="submit" name="submit" value="' . $LANG28[30]
            . '"' . XHTML . '><input type="hidden" name="' . CSRF_TOKEN
            . "\" value=\"{$token}\"" . XHTML . '></div></form>';

    return $retval;
}

/**
* Delete a user
*
* @param    int     $uid    id of user to delete
* @return   string          HTML redirect
*
*/
function deleteUser ($uid)
{
    global $_CONF;

    if (!USER_deleteAccount ($uid)) {
        return COM_refresh ($_CONF['site_admin_url'] . '/user.php');
    }
    CACHE_remove_instance('stmenu');
    return COM_refresh ($_CONF['site_admin_url'] . '/user.php?msg=22');
}

// MAIN
$mode = '';
if (isset($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
}

if (isset($_POST['delbutton_x'])) {
    $mode = 'batchdeleteexec';
}

if (isset ($_REQUEST['order'])) {
    $order =  COM_applyFilter ($_REQUEST['order'],true);
}

if (isset ($_GET['direction'])) {
    $direction =  COM_applyFilter ($_GET['direction']);
}

/* ---if (isset ($_POST['passwd']) && isset ($_POST['passwd_conf']) &&
        ($_POST['passwd'] != $_POST['passwd_conf'])) {
    // entered passwords were different
    $uid = COM_applyFilter ($_POST['uid'], true);
    if ($uid > 1) {
        $display .= COM_refresh ($_CONF['site_admin_url']
                                 . '/user.php?mode=edit&amp;msg=67&amp;uid=' . $uid);
    } else {
        $display .= COM_refresh ($_CONF['site_admin_url'] . '/user.php?msg=67');
    }
} else*/  if (($mode == $LANG_ADMIN['delete']) && !empty ($LANG_ADMIN['delete'])) { // delete
    $uid = COM_applyFilter($_POST['uid'], true);
    if ($uid <= 1) {
        COM_errorLog('Attempted to delete user uid=' . $uid);
        $display = COM_refresh($_CONF['site_admin_url'] . '/user.php');
    } elseif (SEC_checkToken()) {
        $display .= deleteUser($uid);
    } else {
        COM_accessLog("User {$_USER['username']} tried to illegally delete user $uid and failed CSRF checks.");
        echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
    }
} elseif (($mode == $LANG_ADMIN['save']) && !empty($LANG_ADMIN['save']) && SEC_checkToken()) {
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
    $display = saveusers (COM_applyFilter ($_POST['uid'], true),
            trim(COM_stripslashes($_POST['username'])), COM_stripslashes($_POST['fullname']),
            trim(COM_stripslashes($_POST['passwd'])), trim(COM_stripslashes($_POST['passwd_conf'])),
            trim(COM_stripslashes($_POST['email'])),
            $_POST['regdate'], COM_stripSlashes($_POST['homepage']),
            $_POST['groups'],
            $delphoto, $_POST['userstatus'], $_POST['oldstatus']);
    if (!empty($display)) {
        $tmp = COM_siteHeader('menu', $LANG28[22]);
        $tmp .= $display;
        $tmp .= COM_siteFooter();
        $display = $tmp;
    }
} elseif ($mode == 'edit') {
    $display .= COM_siteHeader('menu', $LANG28[1]);
    $msg = '';
    if (isset ($_GET['msg'])) {
        $msg = COM_applyFilter ($_GET['msg'], true);
    }
    $uid = '';
    if (isset ($_GET['uid'])) {
        $uid = COM_applyFilter ($_GET['uid'], true);
    }
    if ( $uid == 1 ) {
        $display .= COM_siteHeader('menu', $LANG28[11]);
        $display .= COM_showMessageFromParameter();
        $display .= listusers();
        $display .= COM_siteFooter();
    } else {
        $display .= edituser ($uid, $msg);
        $display .= COM_siteFooter();
    }
} elseif (($mode == 'import') && SEC_checkToken()) {
    $display .= importusers();
} elseif ($mode == 'importform') {
    $display .= COM_siteHeader('menu', $LANG28[24]);
    $display .= COM_startBlock ($LANG28[24], '',
                        COM_getBlockTemplate ('_admin_block', 'header'));
    $display .= $LANG28[25] . '<br' . XHTML . '><br' . XHTML . '>';
    $display .= display_batchAddform();
    $display .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    $display .= COM_siteFooter();
} elseif ($mode == 'batchdelete') {
    $display .= COM_siteHeader ('menu', $LANG28[54]);
    $display .= batchdelete();
    $display .= COM_siteFooter();
} elseif (($mode == $LANG28[78]) && !empty($LANG28[78]) && SEC_checkToken()) {
    $msg = batchreminders();
    $display .= COM_siteHeader ('menu', $LANG28[11])
        . COM_showMessage($msg)
        . batchdelete()
        . COM_siteFooter();
} elseif (($mode == 'batchdeleteexec') && SEC_checkToken()) {
    $msg = batchdeleteexec();
    $display .= COM_siteHeader ('menu', $LANG28[11])
        . COM_showMessage($msg)
        . batchdelete()
        . COM_siteFooter();
} else { // 'cancel' or no mode at all
    $display .= COM_siteHeader('menu', $LANG28[11]);
    $display .= COM_showMessageFromParameter();
    $display .= listusers();
    $display .= COM_siteFooter();
}

echo $display;

?>