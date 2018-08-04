<?php
// +--------------------------------------------------------------------------+
// | Static Pages Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | Administration page.                                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark A. Howard         mark AT usable-web DOT com                        |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony AT tonybibbs DOT com                    |
// |          Phill Gillespie  - phill AT mediaaustralia DOT com DOT au       |
// |          Tom Willett      - twillett AT users DOT sourceforge DOT net    |
// |          Dirk Haun        - dirk AT haun-online DOT de                   |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

$display = '';

if (!SEC_hasRights ('staticpages.edit')) {
    $display = COM_siteHeader ('menu', $LANG_STATIC['access_denied']);
    $display .= COM_startBlock ($LANG_STATIC['access_denied'], '',
                        COM_getBlockTemplate ('_msg_block', 'header'));
    $display .= $LANG_STATIC['access_denied_msg'];
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to access the static pages administration screen.");
    echo $display;
    exit;
}

/**
* Displays the static page form
*
* @param    array   $A      Data to display
* @param    string  $error  Error message to display
*
*/
function PAGE_form($A, $error = false, $editFlag = 0)
{
    global $_CONF, $_TABLES, $_USER, $_GROUPS, $_SP_CONF, $action, $sp_id,
           $LANG21, $LANG_STATIC, $LANG_ACCESS, $LANG_ADMIN, $LANG24,
           $LANG_postmodes, $MESSAGE;

    USES_lib_admin();

    $filter = new \sanitizer();

    if ( $editFlag ) {
        $lang_create_or_edit = $LANG_ADMIN['edit'];
    } else {
        $lang_create_or_edit = $LANG_ADMIN['create_new'];
    }

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/staticpages/index.php',
              'text' => $LANG_STATIC['page_list']),
        array('url' => $_CONF['site_admin_url'] . '/plugins/staticpages/index.php?edit=x',
              'text' => $lang_create_or_edit,'active'=>true),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $template_path = staticpages_templatePath ('admin');
    if (!empty($sp_id) && ($action=='edit' || $action =='clone' || $action == 'preview' )) {
        $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);
    } else {
        $A['owner_id'] = $_USER['uid'];
        if (isset ($_GROUPS['staticpages Admin'])) {
            $A['group_id'] = $_GROUPS['staticpages Admin'];
        } else {
            $A['group_id'] = SEC_getFeatureGroup ('staticpages.edit');
        }
        SEC_setDefaultPermissions ($A, $_SP_CONF['default_permissions']);
        $access = 3;
    }
    $retval = '';

    if (empty ($A['owner_id'])) {
        $error = COM_startBlock ($LANG_ACCESS['accessdenied'], '',
                        COM_getBlockTemplate ('_msg_block', 'header'));
        $error .= $LANG_STATIC['deny_msg'];
        $error .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    }

    if ($error) {
        $retval .= $error . '<br><br>';
    } else {
        $sp_template = new Template ($template_path);
        $sp_template->set_file ('form', 'editor.thtml');

        $sp_template->set_var('lang_mode', $LANG24[3]);
        $sp_template->set_var(
            'comment_options',
            COM_optionList($_TABLES['commentcodes'],
            'code,name',
            $A['commentcode'])
        );
        $ownername = COM_getDisplayName ($A['owner_id']);

        if ( !isset($A['sp_search']) ) $A['sp_search'] = 0;
        if ( !isset($A['sp_onmenu']) ) $A['sp_onmenu'] = 0;
        if ( !isset($A['sp_status']) ) $A['sp_status'] = 0;

        $sp_template->set_var(array(
            'sp_search_checked' => $A['sp_search'] == 1 ? ' checked="checked"' : '',
            'sp_status_checked' => $A['sp_status'] == 1 ? ' checked="checked"' : '',
            'sp_onmenu_checked' => $A['sp_onmenu'] == 1 ? ' checked="checked"' : '',
            'lang_accessrights' => $LANG_ACCESS['accessrights'],
            'lang_owner'        => $LANG_ACCESS['owner'],
            'owner_username'    => DB_getItem($_TABLES['users'],'username',"uid = {$A['owner_id']}"),
            'owner_name'        => $ownername,
            'owner'             => $ownername,
            'owner_id'          => $A['owner_id'],
            'lang_group'        => $LANG_ACCESS['group'],
            'group_dropdown'    => SEC_getGroupDropdown ($A['group_id'], $access),
            'permissions_editor'=> SEC_getPermissionsHTML($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']),
            'lang_permissions'  => $LANG_ACCESS['permissions'],
            'lang_perm_key'     => $LANG_ACCESS['permissionskey'],
            'permissions_msg'   => $LANG_ACCESS['permmsg'],
            'start_block_editor' => COM_startBlock($LANG_STATIC['staticpages'].' :: '.$LANG_STATIC['staticpageeditor'], '',
                        COM_getBlockTemplate ('_admin_block', 'header')),
            'lang_save'         => $LANG_ADMIN['save'],
            'lang_cancel'       => $LANG_ADMIN['cancel'],
            'lang_preview'      => $LANG_ADMIN['preview'],
            'lang_editor'       => $LANG_STATIC['staticpageeditor'],
            'lang_attributes'   => $LANG_STATIC['attributes'],
            'lang_timeout'      => $LANG_ADMIN['timeout_msg'],
        ));

        if (SEC_hasRights ('staticpages.delete') && ($action != 'clone') &&
                !empty ($A['sp_old_id'])) {
            $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                       . '" name="delete"%s/>';
            $jsconfirm = ' onclick="return confirm(\'' . $MESSAGE[76] . '\');"';
            $sp_template->set_var ('delete_option',
                                   sprintf ($delbutton, $jsconfirm));

            $sp_template->set_var('delete_button',true);
            $sp_template->set_var('lang_delete_confirm',$MESSAGE[76]);
            $sp_template->set_var('lang_delete',$LANG_ADMIN['delete']);

            $sp_template->set_var ('delete_option_no_confirmation',
                                   sprintf ($delbutton, ''));
        } else {
            $sp_template->set_var('delete_option','');
        }

        $sp_template->set_var('lang_writtenby', $LANG_STATIC['writtenby']);
        $sp_template->set_var('username', DB_getItem($_TABLES['users'],
                              'username', "uid = {$A['sp_uid']}"));
        $authorname = COM_getDisplayName ($A['sp_uid']);
        $sp_template->set_var ('name', $authorname);
        $sp_template->set_var ('author', $authorname);
        $sp_template->set_var ('lang_url', $LANG_STATIC['url']);
        $sp_template->set_var ('lang_id', $LANG_STATIC['id']);
        $sp_template->set_var('sp_uid', $A['sp_uid']);
        $sp_template->set_var('sp_id', $A['sp_id']);
        $sp_template->set_var('sp_old_id', $A['sp_old_id']);
        $sp_template->set_var ('example_url', COM_buildURL ($_CONF['site_url']
                               . '/page.php?page=' . $A['sp_id']));

        $sp_template->set_var ('lang_centerblock', $LANG_STATIC['centerblock']);
        $sp_template->set_var ('lang_centerblock_help', $LANG_ADMIN['help_url']);
        $sp_template->set_var ('lang_centerblock_include', $LANG21[51]);
        $sp_template->set_var ('lang_centerblock_desc', $LANG21[52]);
        $sp_template->set_var ('centerblock_help', $A['sp_help']);
        $sp_template->set_var ('lang_centerblock_msg', $LANG_STATIC['centerblock_msg']);
        if (isset ($A['sp_centerblock']) && (($A['sp_centerblock'] == 1) || $A['sp_centerblock'] == 'on')) {
            $sp_template->set_var('centerblock_checked', 'checked="checked"');
        } else {
            $sp_template->set_var('centerblock_checked', '');
        }
        $sp_template->set_var ('lang_topic', $LANG_STATIC['topic']);
        $sp_template->set_var ('lang_position', $LANG_STATIC['position']);
        $current_topic = '';
        if (isset ($A['sp_tid'])) {
            $current_topic = $A['sp_tid'];
        }
        if (empty ($current_topic)) {
            $current_topic = 'none';
        }
        $topics = COM_topicList ('tid,topic,sortnum', $current_topic, 2, true);
        $alltopics = '<option value="all"';
        if ($current_topic == 'all') {
            $alltopics .= ' selected="selected"';
        }
        $alltopics .= '>' . $LANG_STATIC['all_topics'] . '</option>' . LB;

        $allnhp = '<option value="allnhp"';
        if ($current_topic == 'allnhp') {
            $allnhp .= ' selected="selected"';
        }
        $allnhp .= '>' . $LANG_STATIC['allnhp_topics']. '</option>' . LB;

        $notopic = '<option value="none"';
        if ($current_topic == 'none') {
            $notopic .= ' selected="selected"';
        }
        $notopic .= '>' . $LANG_STATIC['no_topic'] . '</option>' . LB;
        $sp_template->set_var ('topic_selection', '<select name="sp_tid">'
                               . $alltopics . $allnhp . $notopic . $topics . '</select>');
        $position = '<select name="sp_where">';
        $position .= '<option value="1"';
        if ($A['sp_where'] == 1) {
            $position .= ' selected="selected"';
        }
        $position .= '>' . $LANG_STATIC['position_top'] . '</option>';
        $position .= '<option value="2"';
        if ($A['sp_where'] == 2) {
            $position .= ' selected="selected"';
        }
        $position .= '>' . $LANG_STATIC['position_feat'] . '</option>';
        $position .= '<option value="3"';
        if ($A['sp_where'] == 3) {
            $position .= ' selected="selected"';
        }
        $position .= '>' . $LANG_STATIC['position_bottom'] . '</option>';
        $position .= '<option value="0"';
        if ($A['sp_where'] == 0) {
            $position .= ' selected="selected"';
        }
        $position .= '>' . $LANG_STATIC['position_entire'] . '</option>';
        $position .= '<option value="4"';
        if ($A['sp_where'] == 4) {
            $position .= ' selected="selected"';
        }
        $position .= '>' . $LANG_STATIC['position_nonews'] . '</option>';
        $position .= '</select>';
        $sp_template->set_var ('pos_selection', $position);

        if (($_SP_CONF['allow_php'] == 1) && SEC_hasRights ('staticpages.PHP')) {
            if (!isset ($A['sp_php'])) {
                $A['sp_php'] = 0;
            }
            $selection = '<select name="sp_php">' . LB;
            $selection .= '<option value="0"';
            if (($A['sp_php'] <= 0) || ($A['sp_php'] > 2)) {
                $selection .= ' selected="selected"';
            }
            $selection .= '>' . $LANG_STATIC['select_php_none'] . '</option>' . LB;
            $selection .= '<option value="1"';
            if ($A['sp_php'] == 1) {
                $selection .= ' selected="selected"';
            }
            $selection .= '>' . $LANG_STATIC['select_php_return'] . '</option>' . LB;
            $selection .= '<option value="2"';
            if ($A['sp_php'] == 2) {
                $selection .= ' selected="selected"';
            }
            $selection .= '>' . $LANG_STATIC['select_php_free'] . '</option>' . LB;
            $selection .= '</select>';
            $sp_template->set_var ('php_selector', $selection);
            $sp_template->set_var ('php_warn', $LANG_STATIC['php_warn']);
        } else {
            $sp_template->set_var ('php_selector', '');
            $sp_template->set_var ('php_warn', $LANG_STATIC['php_not_activated']);
        }
        $sp_template->set_var ('php_msg', $LANG_STATIC['php_msg']);

        // old variables (for the 1.3-type checkbox)
        $sp_template->set_var ('php_checked', '');
        $sp_template->set_var ('php_type', 'hidden');

        if (isset ($A['sp_nf']) && (($A['sp_nf'] == 1) || $A['sp_nf'] == 'on')) {
            $sp_template->set_var('exit_checked','checked="checked"');
        } else {
            $sp_template->set_var('exit_checked','');
        }
        $sp_template->set_var('exit_msg',$LANG_STATIC['exit_msg']);
        $sp_template->set_var('exit_info',$LANG_STATIC['exit_info']);

        if (isset($A['sp_inblock']) && ( $A['sp_inblock'] == 1 || $A['sp_inblock'] == 'on' )) {
            $sp_template->set_var ('inblock_checked', 'checked="checked"');
        } else {
            $sp_template->set_var ('inblock_checked', '');
        }
        $sp_template->set_var ('inblock_msg', $LANG_STATIC['inblock_msg']);
        $sp_template->set_var ('inblock_info', $LANG_STATIC['inblock_info']);

        $curtime = COM_getUserDateTimeFormat ($A['unixdate']);
        $sp_template->set_var ('lang_lastupdated', $LANG_STATIC['date']);
        $sp_template->set_var ('sp_formateddate', $curtime[0]);
        $sp_template->set_var ('sp_date', $curtime[1]);

        $sp_template->set_var('lang_title', $LANG_STATIC['title']);
        $title = '';
        if (isset ($A['sp_title'])) {
            $title = $filter->editableText($A['sp_title']);
        }
        $sp_template->set_var('sp_title', $title);
        $sp_template->set_var('lang_addtomenu', $LANG_STATIC['addtomenu']);
        if (isset ($A['sp_onmenu']) && ($A['sp_onmenu'] == 1)) {
            $sp_template->set_var('onmenu_checked', 'checked="checked"');
        } else {
            $sp_template->set_var('onmenu_checked', '');
        }
        $sp_template->set_var('lang_label', $LANG_STATIC['label']);
        if (isset ($A['sp_label'])) {
            $sp_template->set_var('sp_label', $filter->editableText($A['sp_label']));
        } else {
            $sp_template->set_var('sp_label', '');
        }
        $sp_template->set_var('lang_pageformat', $LANG_STATIC['pageformat']);
        $sp_template->set_var('lang_blankpage', $LANG_STATIC['blankpage']);
        $sp_template->set_var('lang_noblocks', $LANG_STATIC['noblocks']);
        $sp_template->set_var('lang_leftblocks', $LANG_STATIC['leftblocks']);
        $sp_template->set_var('lang_rightblocks', $LANG_STATIC['rightblocks']);
        $sp_template->set_var('lang_leftrightblocks', $LANG_STATIC['leftrightblocks']);
        if (!isset ($A['sp_format'])) {
            $A['sp_format'] = '';
        }
        if ($A['sp_format'] == 'noblocks') {
            $sp_template->set_var('noblock_selected', 'selected="selected"');
        } else {
            $sp_template->set_var('noblock_selected', '');
        }
        if ($A['sp_format'] == 'leftblocks') {
            $sp_template->set_var('leftblocks_selected', 'selected="selected"');
        } else {
            $sp_template->set_var('leftblocks_selected', '');
        }
        if ($A['sp_format'] == 'rightblocks') {
            $sp_template->set_var('rightblocks_selected', 'selected="selected"');
        } else {
            $sp_template->set_var('rightblocks_selected', '');
        }
        if ($A['sp_format'] == 'blankpage') {
            $sp_template->set_var('blankpage_selected', 'selected="selected"');
        } else {
            $sp_template->set_var('blankpage_selected', '');
        }
        if (($A['sp_format'] == 'allblocks') OR empty ($A['sp_format'])) {
            $sp_template->set_var('allblocks_selected', 'selected="selected"');
        } else {
            $sp_template->set_var('allblocks_selected', '');
        }

        $sp_template->set_var('lang_content', $LANG_STATIC['content']);
        $content = '';
        if (isset ($A['sp_content'])) {
            $content = $filter->editableText($A['sp_content']);
        }
        $sp_template->set_var('sp_content', $content);
        if ($_SP_CONF['filter_html'] == 1) {
            $sp_template->set_var('lang_allowedhtml', COM_allowedHTML(SEC_getUserPermissions(),false,'staticpages','page'));
        } else {
            $sp_template->set_var('lang_allowedhtml', $LANG_STATIC['all_html_allowed']);
        }
        $sp_template->set_var ('lang_hits', $LANG_STATIC['hits']);
        if (empty ($A['sp_hits'])) {
            $sp_template->set_var ('sp_hits', '0');
            $sp_template->set_var ('sp_hits_formatted', '0');
        } else {
            $sp_template->set_var ('sp_hits', $A['sp_hits']);
            $sp_template->set_var ('sp_hits_formatted',
                                   COM_numberFormat ($A['sp_hits']));
        }

        $sp_template->set_var('sp_preview_content',$A['preview_content']);
        $sp_template->set_var('sp_preview_title',isset($A['preview_title']) ? $A['preview_title'] : '');

        if (isset($A['preview'])) {
            $sp_template->set_var('show_preview',true);
        }

        $sp_template->set_var('end_block',
                COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer')));

        $sp_template->set_var('owner_dropdown',COM_buildOwnerList('owner_id',$A['owner_id']));
        $sp_template->set_var('writtenby_dropdown',COM_buildOwnerList('sp_uid',$A['sp_uid']));

        $sp_template->set_var( 'gltoken_name', CSRF_TOKEN );
        $sp_template->set_var( 'gltoken', SEC_createToken() );
        $sp_template->set_var( 'admin_menu',ADMIN_createMenu($menu_arr, $LANG_STATIC['instructions_edit'], plugin_geticon_staticpages()));
        PLG_templateSetVars('sp_editor',$sp_template);
        $retval .= $sp_template->parse('output','form');
    }

    return $retval;
}

/**
* Displays the Static Page Editor
*
* @sp_id        string      ID of static page to edit
* @action       string      action (edit, clone or null)
* @editor       string      editor to use
*
*/
function PAGE_edit($sp_id, $action = '', $editor = '',$preview_content = '')
{
    global $_CONF, $_SP_CONF, $_TABLES, $_USER, $LANG_STATIC;

    $editFlag = false;

    if (!empty ($sp_id) && $action == 'edit') {
        $result = DB_query ("SELECT *,UNIX_TIMESTAMP(sp_date) AS unixdate FROM {$_TABLES['staticpage']} WHERE sp_id = '$sp_id'" . COM_getPermSQL ('AND', 0, 3));
        $A = DB_fetchArray ($result);
        $A['sp_old_id'] = $A['sp_id'];  // // sp_old_id is not null, this is an existing page


        if (($_SP_CONF['allow_php'] == 1) && SEC_hasRights ('staticpages.PHP')) {
            if (!isset ($A['sp_php'])) {
                $A['sp_php'] = 0;
            }
        }
        // do not set preview until user has requested - this will allow
        // editing pages with PHP errors
//        $preview_content = SP_render_content ($A['sp_content'], $A['sp_php']);
//        $preview_title = isset($A['sp_title']) ? $A['sp_title'] : '';
        $editFlag = true;

    } elseif ($action == 'edit') {
        // we're creating a new staticpage, set default values
        $A['sp_id'] = COM_makesid ();   // make a default new/unique staticpage ID based upon the datetime
        $A['sp_status'] = $_SP_CONF['status_flag'];
        $A['sp_uid'] = $_USER['uid'];   // created by current user
        $A['unixdate'] = time ();       // date/time created
        $A['sp_help'] = '';             // no help URL
        $A['sp_old_id'] = '';           // sp_old_id is null, this is a new page
        $A['commentcode'] = $_SP_CONF['comment_code'];
        $A['sp_where'] = 1;             // top of page
        $A['sp_search'] = $_SP_CONF['include_search'];
        $A['sp_onmenu'] = 0;
    } elseif (!empty ($sp_id) && $action == 'clone') {
        // we're creating a new staticpage based upon an old one.  get the page to be cloned
        $result = DB_query ("SELECT *,UNIX_TIMESTAMP(sp_date) AS unixdate FROM {$_TABLES['staticpage']} WHERE sp_id = '$sp_id'" . COM_getPermSQL ('AND', 0, 2));
        $A = DB_fetchArray ($result);
        // override old page values with values unique to this page
        $A['sp_id'] = COM_makesid ();   // make a default new/unique staticpage ID based upon the datetime
        $sp_id = $A['sp_id'];           // to ensure value displayed in field reflects updated value
        $sp_title = $A['sp_title'] . ' (' . $LANG_STATIC['copy'] . ')';
        $A['sp_title'] = $sp_title;     // indicate in title that this is a cloned page
        $A['sp_uid'] = $_USER['uid'];   // created by current user
        $A['unixdate'] = time ();       // date/time created
        $A['sp_hits'] = 0;              // reset page hits
        $A['sp_old_id'] = '';           // sp_old_id is null, this is a new page
    } else {
        $A = $_POST;
        if (empty ($A['unixdate'])) {
            $A['unixdate'] = time ();   // update date and time
        }
        if ( isset($_POST['sp_status_yes'] ) || isset($_POST['sp_status_no']) ) {
            if ( isset($_POST['sp_status_yes'])) $A['sp_status'] = 1;
            if ( isset($_POST['sp_status_no']))  $A['sp_status'] = 0;
        } else {
            $A['sp_status'] = isset($_POST['sp_status']) ? 1 : 0;
        }
//        $A['sp_content'] = COM_checkHTML (COM_checkWords ($A['sp_content']));
    }
    if (isset ($A['sp_title'])) {
        $A['sp_title'] = strip_tags ($A['sp_title']);
        $A['preview_title'] = $A['sp_title'];
    }
    $A['editor'] = $editor;
    $A['preview_content'] = $preview_content;

    if ( $action == 'preview') { $A['show_preview'] = 1; $A['preview'] = 1; }

    return PAGE_form($A,'',$editFlag);
}

/**
* Saves a Static Page to the database
*
* @param sp_id           string  ID of static page
* @param sp_uid          string  ID of user that created page
* @param sp_title        string  title of page
* @param sp_content      string  page content
* @param sp_hits         int     Number of page views
* @param sp_format       string  HTML or plain text
* @param sp_onmenu       string  Flag to place entry on menu
* @param sp_label        string  Menu Entry
* @param commentcode     int     Comment Code
* @param owner_id        int     Permission bits
* @param group_id        int
* @param perm_owner      int
* @param perm_members    int
* @param perm_anon       int
* @param sp_php          int     Flag to indicate PHP usage
* @param sp_nf           string  Flag to indicate type of not found message
* @param sp_old_id       string  original ID of this static page
* @param sp_centerblock  string  Flag to indicate display as a center block
* @param sp_help         string  Help URL that displays in the block
* @param sp_tid          string  topid id (for center block)
* @param sp_where        int     position of center block
* @param sp_inblock      string  Flag: wrap page in a block (or not)
*
*/
function PAGE_submit($sp_id, $sp_status, $sp_uid, $sp_title, $sp_content, $sp_hits,
                           $sp_format, $sp_onmenu, $sp_label, $commentcode,
                           $owner_id, $group_id, $perm_owner, $perm_group,
                           $perm_members, $perm_anon, $sp_php, $sp_nf,
                           $sp_old_id, $sp_centerblock, $sp_help, $sp_tid,
                           $sp_where, $sp_inblock, $postmode,$sp_search)
{
    global $_CONF, $_TABLES, $LANG12, $LANG_STATIC, $_SP_CONF;

    $retval = '';

    $args = array(
                'sp_id' => $sp_id,
                'sp_status' => $sp_status,
                'sp_uid' => $sp_uid,
                'sp_title' => $sp_title,
                'sp_content' => $sp_content,
                'sp_hits' => $sp_hits,
                'sp_format' => $sp_format,
                'sp_onmenu' => $sp_onmenu,
                'sp_label' => $sp_label,
                'commentcode' => $commentcode,
                'owner_id' => $owner_id,
                'group_id' => $group_id,
                'perm_owner' => $perm_owner,
                'perm_group' => $perm_group,
                'perm_members' => $perm_members,
                'perm_anon' => $perm_anon,
                'sp_php' => $sp_php,
                'sp_nf' => $sp_nf,
                'sp_old_id' => $sp_old_id,
                'sp_centerblock' => $sp_centerblock,
                'sp_help' => $sp_help,
                'sp_tid' => $sp_tid,
                'sp_where' => $sp_where,
                'sp_inblock' => $sp_inblock,
                'postmode' => $postmode,
                'sp_search' => $sp_search
                 );

    PLG_invokeService('staticpages', 'submit', $args, $retval, $svc_msg);
    return $retval;
}

function PAGE_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $LANG_ADMIN, $LANG_STATIC, $LANG_ACCESS, $_TABLES;

    $retval = '';
    $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],
                            $A['perm_group'],$A['perm_members'],$A['perm_anon']);
    $enabled = ($A['sp_status'] == 1) ? true : false;

    $dt = new Date('now',$_USER['tzid']);

    switch($fieldname) {

        case 'edit':
            if ($access == 3) {
                $attr['title'] = $LANG_ADMIN['edit'];
                $retval = COM_createLink(
                    $icon_arr['edit'],
                    $_CONF['site_admin_url'] . '/plugins/staticpages/index.php'
                    . '?edit=x&amp;sp_id=' . $A['sp_id'], $attr );
            } else {
                $retval = $icon_arr['blank'];
            }
            break;

        case 'copy':
            if ($access >= 2) {
                $attr['title'] = $LANG_ADMIN['copy'];
                $retval = COM_createLink(
                    $icon_arr['copy'],
                    $_CONF['site_admin_url'] . '/plugins/staticpages/index.php'
                    . '?clone=x&amp;sp_id=' . $A['sp_id'], $attr);
            } else {
                $retval = $icon_arr['blank'];
            }
            break;

        case "sp_title":
            $sp_title = $A['sp_title'];
            if ($enabled) {
                $url = COM_buildUrl(
                    $_CONF['site_url'] .
                    '/page.php?page=' . $A['sp_id']);
                $retval = COM_createLink(
                    $sp_title, $url,
                    array('title'=>$LANG_STATIC['title_display']));
            } else {
                $retval = '<span class="disabledfield">' . $sp_title . '</span>';
            }
            break;

        case 'sp_search' :
            if ($fieldvalue == 0) {
                $retval = '<i class="uk-icon uk-icon-minus uk-text-danger"></i>';
            } else {
                $retval = '<i class="uk-icon uk-icon-check uk-text-success"></i>';
            }
            break;
        case 'access':
            if ($access == 3) {
                $privs = $LANG_ACCESS['edit'];
            } else {
                $privs = $LANG_ACCESS['readonly'];
            }
            $retval = ($enabled) ? $privs : '<span class="disabledfield">' . $privs . '</span>';
            break;

        case "sp_uid":
            $owner = COM_getDisplayName ($A['sp_uid']);
            $retval = ($enabled) ? $owner : '<span class="disabledfield">' . $owner . '</span>';
            break;

        case "sp_centerblock":
            if ($A['sp_centerblock']) {
                switch ($A['sp_where']) {
                    case '1': $where = $LANG_STATIC['centerblock_top']; break;
                    case '2': $where = $LANG_STATIC['centerblock_feat']; break;
                    case '3': $where = $LANG_STATIC['centerblock_bottom']; break;
                    default:  $where = $LANG_STATIC['centerblock_entire']; break;
                }
            } else {
                $where = $LANG_STATIC['centerblock_no'];
            }
            $retval = ($enabled) ? $where : '<span class="disabledfield">' . $where . '</span>';
            break;

        case "unixdate":
            $dt->setTimestamp($A['unixdate']);
            $datetime = $dt->format($_CONF['daytime'],true);
            $retval = ($enabled) ? $datetime : '<span class="disabledfield">' . $datetime . '</span>';
            break;

        case 'delete':
            if ($access == 3) {
                $attr['title'] = $LANG_ADMIN['delete'];
                $attr['onclick'] = "return confirm('" . $LANG_STATIC['delete_confirm'] . "');";
                $retval = COM_createLink(
                    $icon_arr['delete'],
                    $_CONF['site_admin_url'] . '/plugins/staticpages/index.php'
                    . '?delete=x&amp;sp_id=' . $A['sp_id'] . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);

            } else {
                $retval = $icon_arr['blank'];
            }
            break;

        case 'sp_status':
            if ($access == 3) {
                if ($enabled) {
                    $switch = ' checked="checked"';
                    $title = 'title="' . $LANG_ADMIN['disable'] . '" ';
                } else {
                    $title = 'title="' . $LANG_ADMIN['enable'] . '" ';
                    $switch = '';
                }
                $retval = '<input class="sp-enabler" type="checkbox" name="enabledstaticpages[' . $A['sp_id'] . ']" ' . $title
                    . 'onclick="submit()" value="1"' . $switch . '/>';
                $retval .= '<input type="hidden" name="sp_idarray['.$A['sp_id'].']" value="1" />';
            } else {
                $retval = ($enabled) ? $LANG_ACCESS['yes'] : $LANG_ACCESS['No'];
            }
            break;

        default:
            $retval = ($enabled) ? $fieldvalue : '<span class="disabledfield">' . $fieldvalue . '</span>';
            break;
    }
    return $retval;
}

function PAGE_list()
{
    global $_CONF, $_TABLES, $_IMAGE_TYPE, $LANG_ADMIN, $LANG_ACCESS, $LANG_STATIC;

    USES_lib_admin();

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/staticpages/index.php',
              'text' => $LANG_STATIC['page_list'],'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/plugins/staticpages/index.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG_STATIC['staticpagelist'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_STATIC['instructions'], plugin_geticon_staticpages());

    $header_arr = array(      # display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_ADMIN['copy'], 'field' => 'copy', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_STATIC['id'], 'field' => 'sp_id', 'sort' => true),
        array('text' => $LANG_ADMIN['title'], 'field' => 'sp_title', 'sort' => true),
        array('text' => $LANG_STATIC['head_centerblock'], 'field' => 'sp_centerblock', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG_STATIC['writtenby'], 'field' => 'sp_uid', 'sort' => true),
        array('text' => $LANG_STATIC['searchable'], 'field' => 'sp_search', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_STATIC['date'], 'field' => 'unixdate', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_ADMIN['enabled'], 'field' => 'sp_status', 'sort' => true, 'align' => 'center'),
    );

    $defsort_arr = array('field' => 'sp_title', 'direction' => 'asc');

    $text_arr = array(
        'has_extras' => true,
        'form_url' => $_CONF['site_admin_url'] . '/plugins/staticpages/index.php'
    );

    // sql query which drives the list
    $sql = "SELECT *,UNIX_TIMESTAMP(sp_date) AS unixdate "
                ."FROM {$_TABLES['staticpage']} WHERE 1=1 ";

    $query_arr = array(
        'table' => 'staticpage',
        'sql' => $sql,
        'query_fields' => array('sp_title', 'sp_id'),
        'default_filter' => COM_getPermSQL ('AND')
    );

    // create the security token, and embed it in the list form
    // also set the hidden var which signifies that this list allows for pages
    // to be enabled/disabled via checkbox
    $token = SEC_createToken();
    $form_arr = array(
        'top'    => '<input type="hidden" name="'.CSRF_TOKEN.'" value="'.$token.'"/>',
        'bottom' => '<input type="hidden" name="staticpageenabler" value="true"/>'
    );

    $retval .= ADMIN_list('static_pages', 'PAGE_getListField',
                          $header_arr, $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr);
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    $outputHandle = outputHandler::getInstance();
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/admin.js',HEADER_PRIO_NORMAL,'text/javascript');

    return $retval;

}

/**
* Toggle status of a staticpage from enabled to disabled and back
*
* @param    array   $enabledstaticpages    array of sp_id's available
* @param    array   $spidarray             array of status (1/0)
* @return   void
*
*/
function PAGE_toggleStatus($enabledstaticpages, $sp_idarray)
{
    global $_TABLES, $_DB_table_prefix;
    if (isset($sp_idarray) && is_array($sp_idarray) ) {
        foreach ($sp_idarray AS $sp_id => $junk ) {
            $sp_id = COM_applyFilter($sp_id);
            if (isset($enabledstaticpages[$sp_id])) {
                DB_query ("UPDATE {$_TABLES['staticpage']} SET sp_status = '1' WHERE sp_id = '".DB_escapeString($sp_id)."'");
            } else {
                DB_query ("UPDATE {$_TABLES['staticpage']} SET sp_status = '0' WHERE sp_id = '".DB_escapeString($sp_id)."'");
            }
        }
    }
    PLG_itemSaved($sp_id,'staticpages');
    glFusion\Cache::getInstance()->deleteItemsByTag('staticpage');
}

// MAIN ========================================================================

$action = '';
$expected = array('edit','clone','save','delete','cancel','preview');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	    $action = $provided;
    }
}

$sp_id = '';
if (isset($_POST['sp_id'])) {
    $sp_id = COM_applyFilter($_POST['sp_id']);
} elseif (isset($_GET['sp_id'])) {
    $sp_id = COM_applyFilter($_GET['sp_id']);
}

if (isset ($_POST['staticpageenabler']) && SEC_checkToken()) {
    $enabledstaticpages = array();
    if (isset($_POST['enabledstaticpages'])) {
        $enabledstaticpages = $_POST['enabledstaticpages'];
    }
    $sp_idarray = array();
    if ( isset($_POST['sp_idarray']) ) {
        $sp_idarray = $_POST['sp_idarray'];
    }
    PAGE_toggleStatus($enabledstaticpages,$sp_idarray);
    // force a refresh to redisplay staticpage status
    header ('Location: ' . $_CONF['site_admin_url'] . '/plugins/staticpages/index.php');
    exit;
}

$preview_content = '';
$sp_php = '';
switch ($action) {

    case 'preview':
        $sp_php = isset($_POST['sp_php']) ? $_POST['sp_php'] : '';
        $editor_content = isset($_POST['sp_content']) ? $_POST['sp_content'] : '';

        if ( isset($_POST['sp_php']) && (int) $_POST['sp_php'] > 0 && defined('DEMO_MODE') ) {
            $preview_content = 'StaticPage Preview is disabled in Demo Mode';
        } else {
            $preview_content = SP_render_content ($editor_content, $sp_php);
        }

        $preview_title = isset($_POST['sp_title']) ? $_POST['sp_title'] : '';

        $owner_id = $_POST['owner_id'];
        $group_id = $_POST['group_id'];
        $perm_owner = $_POST['perm_owner'];
        $perm_group = $_POST['perm_group'];
        $perm_members = $_POST['perm_members'];
        $perm_anon = $_POST['perm_anon'];
        list($perm_owner,$perm_group,$perm_members,$perm_anon) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);
        $_POST['perm_owner'] = $perm_owner;
        $_POST['perm_group'] = $perm_group;
        $_POST['perm_members'] = $perm_members;
        $_POST['perm_anon'] = $perm_anon;

    case 'edit':
        SEC_setCookie ($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral('advancededitor'),
                        time() + 1200, $_CONF['cookie_path'],
                        $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);
        $display .= COM_siteHeader ('menu', $LANG_STATIC['staticpageeditor']);
        $editor = '';
        if (isset ($_GET['editor'])) {
            $editor = COM_applyFilter ($_GET['editor']);
        }
        $display .= PAGE_edit($sp_id, $action, $editor, $preview_content);
        $display .= COM_siteFooter ();
        break;

    case 'clone':
        if (!empty($sp_id)) {
            SEC_setCookie ($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral('advancededitor'),
                            time() + 1200, $_CONF['cookie_path'],
                            $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);
            $display .= COM_siteHeader('menu', $LANG_STATIC['staticpageeditor']);
            $display .= PAGE_edit($sp_id,$action);
            $display .= COM_siteFooter();
        } else {
            $display = COM_refresh ($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'save':
        if ( SEC_checkToken() ) {
            // purge any tokens we created for the advanced editor
            $sql = "DELETE FROM {$_TABLES['tokens']} WHERE owner_id={$_USER['uid']} AND urlfor='advancededitor'";
            DB_query($sql,1);

            if (!empty ($sp_id)) {
/*
                if (!isset ($_POST['sp_onmenu'])) {
                    $_POST['sp_onmenu'] = '';
                }
*/
                if (!isset ($_POST['sp_php'])) {
                    $_POST['sp_php'] = '';
                }
                if (!isset ($_POST['sp_nf'])) {
                    $_POST['sp_nf'] = '';
                }
                if (!isset ($_POST['sp_centerblock'])) {
                    $_POST['sp_centerblock'] = '';
                }
                $help = '';
                if (isset ($_POST['sp_help'])) {
                    $sp_help = COM_sanitizeUrl ($_POST['sp_help'], array ('http', 'https'));
                }
                if (!isset ($_POST['sp_inblock'])) {
                    $_POST['sp_inblock'] = '';
                }
                $sp_uid = COM_applyFilter ($_POST['sp_uid'], true);
                if ($sp_uid == 0) {
                    $sp_uid = $_USER['uid'];
                }
                if (!isset ($_POST['postmode'])) {
                    $_POST['postmode'] = '';
                }
                $sp_status = 0;
                if ( isset($_POST['sp_status_yes'] ) || isset($_POST['sp_status_no']) ) {
                    if ( isset($_POST['sp_status_yes'])) $sp_status = 1;
                    if ( isset($_POST['sp_status_no']))  $sp_status = 0;
                } else {
                    $sp_status = isset($_POST['sp_status']) ? 1 : 0;
                }
                $display .= PAGE_submit(
                    $sp_id,
                    $sp_status,
                    $sp_uid,
                    isset($_POST['sp_title']) ? $_POST['sp_title'] : '',
                    isset($_POST['sp_content']) ? $_POST['sp_content'] : '',
                    isset($_POST['sp_hits']) ? COM_applyFilter ($_POST['sp_hits'], true) : 0,
                    isset($_POST['sp_format']) ? COM_applyFilter ($_POST['sp_format']) : '',
                    isset($_POST['sp_onmenu']) ? 1 : 0,
                    isset($_POST['sp_label']) ? $_POST['sp_label'] : '',
                    isset($_POST['commentcode']) ? COM_applyFilter ($_POST['commentcode'], true) : 0,
                    isset($_POST['owner_id']) ? COM_applyFilter ($_POST['owner_id'], true) : 2,
                    isset($_POST['group_id']) ? COM_applyFilter ($_POST['group_id'], true) : 0,
                    isset($_POST['perm_owner']) ? $_POST['perm_owner'] : '',
                    isset($_POST['perm_group']) ? $_POST['perm_group'] : '',
                    isset($_POST['perm_members']) ? $_POST['perm_members'] : '',
                    isset($_POST['perm_anon']) ? $_POST['perm_anon'] : '',
                    isset($_POST['sp_php']) ? $_POST['sp_php'] : '',
                    isset($_POST['sp_nf']) ? $_POST['sp_nf'] : '',
                    isset($_POST['sp_old_id']) ? COM_applyFilter ($_POST['sp_old_id']) : '',
                    isset($_POST['sp_nf']) ? $_POST['sp_centerblock'] : '',
                    $sp_help,
                    isset($_POST['sp_tid']) ? COM_applyFilter ($_POST['sp_tid']) : '',
                    isset($_POST['sp_where']) ? COM_applyFilter ($_POST['sp_where'], true) : 0,
                    isset($_POST['sp_inblock']) ? $_POST['sp_inblock'] : '',
                    isset($_POST['postmode']) ? COM_applyFilter ($_POST['postmode']) : '',
                    isset($_POST['sp_search']) ? 1 : 0
                );
            } else {
                $display = COM_refresh ($_CONF['site_admin_url'] . '/index.php');
            }
        } else {
            //token expired?
            SEC_setCookie ($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral('advancededitor'),
                        time() + 1200, $_CONF['cookie_path'],
                        $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);
            $display .= COM_siteHeader ('menu', $LANG_STATIC['staticpageeditor']);
            $display .= COM_showMessage(501);
            $editor = '';
            if (isset ($_GET['editor'])) {
                $editor = COM_applyFilter ($_GET['editor']);
            }
            // $mode = 'edit';
            $owner_id = $_POST['owner_id'];
            $group_id = $_POST['group_id'];
            $perm_owner = $_POST['perm_owner'];
            $perm_group = $_POST['perm_group'];
            $perm_members = $_POST['perm_members'];
            $perm_anon = $_POST['perm_anon'];
            list($perm_owner,$perm_group,$perm_members,$perm_anon) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);
            $_POST['perm_owner'] = $perm_owner;
            $_POST['perm_group'] = $perm_group;
            $_POST['perm_members'] = $perm_members;
            $_POST['perm_anon'] = $perm_anon;
            $sp_centerblock = isset($_POST['sp_centerblock']) ? $_POST['sp_centerblock'] : '';
            $sp_help = '';
            if (!empty($_POST['sp_help'])) {
                $sp_help = $_POST['sp_help'];
            }
            $sp_inblock = (isset($_POST['sp_inblock']) ? $_POST['sp_inblock'] : '');
            $sp_search = (isset($_POST['sp_search']) ? $_POST['sp_search'] : '');
            $sp_onmenu = (isset($_POST['sp_onmenu']) ? $_POST['sp_onmenu'] : '');
            $sp_nf     = (isset($_POST['sp_nf']) ? $_POST['sp_nf'] : '');
/*
            if ($sp_onmenu == 'on') {
                $_POST['sp_onmenu'] = 1;
            } else {
                $_POST['sp_onmenu'] = 0;
            }
*/
            if ($sp_nf == 'on') {
                $_POST['sp_nf'] = 1;
            } else {
                $_POST['sp_nf'] = 0;
            }
            if ($sp_centerblock == 'on') {
                $_POST['sp_centerblock'] = 1;
            } else {
                $_POST['sp_centerblock'] = 0;
            }
            if ($sp_inblock == 'on') {
                $_POST['sp_inblock'] = 1;
            } else {
                $_POST['sp_inblock'] = 0;
            }
            $display .= PAGE_edit($sp_id, '', $editor);
            $display .= COM_siteFooter ();
        }
        break;

    case 'delete':
        if (empty($sp_id) || (is_numeric ($sp_id) && ($sp_id == 0))) {
            COM_errorLog('Attempted to delete staticpage, sp_id empty or null, value =' . $sp_id);
            $display .= COM_refresh($_CONF['site_admin_url'] . '/plugins/staticpages/index.php');
        } elseif (SEC_checkToken()) {
            $args = array(
                        'sp_id' => $sp_id
                         );
            PLG_invokeService('staticpages', 'delete', $args, $display, $svc_msg);
        } else {
            COM_accessLog("User {$_USER['username']} tried to delete staticpage $sp_id and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        $display .= COM_siteHeader ('menu', $LANG_STATIC['staticpagelist']);
        $display .= PAGE_list();
        $display .= COM_siteFooter ();
        break;
}

echo $display;

?>