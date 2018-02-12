<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | block.php                                                                |
// |                                                                          |
// | glFusion block administration.                                           |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2010-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark Howard            mark AT usable-web DOT com                        |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Michael Jervis    - mike AT fuckingbrit DOT com                 |
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


require_once '../lib-common.php';
require_once 'auth.inc.php';

$display = '';

// Make sure user has rights to access this page
if (!SEC_hasRights ('block.edit')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30])
        . COM_showMessageText($MESSAGE[33],$MESSAGE[30],true,'error')
        . COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to access the block administration screen");
    echo $display;
    exit;
}


/**
* Check for block topic access (need to handle 'all' and 'homeonly' as
* special cases)
*
* @param    string  $tid    ID for topic to check on
* @return   int             returns 3 for read/edit 2 for read only 0 for no access
*
*/
function BLOCK_hasTopicAccess ($tid)
{
    $access = 0;

    if (($tid == 'all') || ($tid == 'homeonly')) {
        $access = 3;
    } else {
        $access = SEC_hasTopicAccess ($tid);
    }

    return $access;
}

/**
* Shows default block editor
*
* Default blocks are those blocks that glFusion requires to function
* properly.  Because of their special role, they have restricted
* edit properties so this form shows that.
*
* @param    array   $A      Array of data to show on form
* @param    int     $access Permissions this user has
* @return   string          HTML for default block editor
*
*/
function BLOCK_editDefault($A, $access)
{
    global $_CONF, $_TABLES, $_USER, $LANG21, $LANG_ACCESS, $LANG_ADMIN,$_IMAGE_TYPE;

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/block.php',
              'text' => $LANG_ADMIN['block_list']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG21[3], '',COM_getBlockTemplate ('_admin_block', 'header'));
    $retval .= ADMIN_createMenu($menu_arr,$LANG21[72],$_CONF['layout_url'] . '/images/icons/block.'. $_IMAGE_TYPE);

    $block_templates = new Template($_CONF['path_layout'] . 'admin/block');
    $block_templates->set_file('editor','defaultblockeditor.thtml');

    $block_templates->set_var('block_id', $A['bid']);
    // standard Admin strings
    $block_templates->set_var('lang_blocktitle', $LANG_ADMIN['title']);
    $block_templates->set_var('lang_enabled', $LANG_ADMIN['enabled']);
    $block_templates->set_var('lang_blockhelpurl', $LANG_ADMIN['help_url']);
    $block_templates->set_var('lang_topic', $LANG_ADMIN['topic']);
    $block_templates->set_var('lang_save', $LANG_ADMIN['save']);
    $block_templates->set_var('lang_cancel', $LANG_ADMIN['cancel']);
    $block_templates->set_var('lang_blocktype', $LANG_ADMIN['type']);

    $block_templates->set_var('block_title', htmlspecialchars($A['title'],ENT_QUOTES,COM_getEncodingt()));
    if ($A['is_enabled'] == 1) {
        $block_templates->set_var('is_enabled', 'checked="checked"');
    } else {
        $block_templates->set_var('is_enabled', '');
    }
    $block_templates->set_var('block_help', $A['help']);
    $block_templates->set_var('lang_includehttp', $LANG21[51]);
    $block_templates->set_var('lang_explanation', $LANG21[52]);
    $block_templates->set_var('block_name',$A['name']);
    $block_templates->set_var('lang_blockname', $LANG21[48]);
    $block_templates->set_var('lang_homeonly', $LANG21[43]);
    $block_templates->set_var('lang_nohomepage', $LANG21[44]);
    if ($A['tid'] == 'all') {
        $block_templates->set_var('all_selected', 'selected="selected"');
    } else if ($A['tid'] == 'homeonly') {
        $block_templates->set_var('homeonly_selected', 'selected="selected"');
    } else if ( $A['tid'] == 'allnhp' ) {
        $block_templates->set_var('nohomepage_selected','selected="selected"');
    }
    $block_templates->set_var('topic_options',
                              COM_topicList ('tid,topic,sortnum', $A['tid'], 2, true));
    $block_templates->set_var('lang_all', $LANG21[7]);
    $block_templates->set_var('lang_side', $LANG21[39]);
    $block_templates->set_var('lang_left', $LANG21[40]);
    $block_templates->set_var('lang_right', $LANG21[41]);

    if ($A['onleft'] == 1) {
        $block_templates->set_var('left_selected', 'selected="selected"');
    } else if ($A['onleft'] == 0) {
        $block_templates->set_var('right_selected', 'selected="selected"');
    }
    $block_templates->set_var('lang_blockorder', $LANG21[9]);
    $block_templates->set_var('block_order', $A['blockorder']);
    $block_templates->set_var('lang_accessrights', $LANG_ACCESS['accessrights']);
    $block_templates->set_var('lang_owner', $LANG_ACCESS['owner']);
    $ownername = COM_getDisplayName ($A['owner_id']);
    $block_templates->set_var('owner_username', DB_getItem($_TABLES['users'],
                                    'username', "uid = '{$A['owner_id']}'"));
    $block_templates->set_var('owner_name', $ownername);
    $block_templates->set_var('owner', $ownername);
    $block_templates->set_var('owner_id', $A['owner_id']);

    $block_templates->set_var('lang_group', $LANG_ACCESS['group']);
    $block_templates->set_var('group_dropdown',
                              SEC_getGroupDropdown ($A['group_id'], $access));
    $block_templates->set_var('group_name', DB_getItem ($_TABLES['groups'],
                                    'grp_name', "grp_id = '{$A['group_id']}'"));
    $block_templates->set_var('group_id', $A['group_id']);
    $block_templates->set_var('lang_permissions', $LANG_ACCESS['permissions']);
    $block_templates->set_var('lang_perm_key', $LANG_ACCESS['permissionskey']);
    $block_templates->set_var('permissions_editor', SEC_getPermissionsHTML($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']));
    $block_templates->set_var('permissions_msg', $LANG_ACCESS['permmsg']);
    $block_templates->set_var('max_url_length', 255);
    $block_templates->set_var('gltoken_name', CSRF_TOKEN);
    $block_templates->set_var('gltoken', SEC_createToken());
    $block_templates->parse('output','editor');
    $retval .= $block_templates->finish($block_templates->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}

/**
* Shows the block editor
*
* This will show a block edit form.  If this is a glFusion default block it will
* send it off to BLOCK_editDefault().
*
* @param    string  $bid    ID of block to edit
* @param    array   $B      An array of block fields (optional)
* @return   string          HTML for block editor
*
*/
function BLOCK_edit($bid = '', $B = array())
{
    global $_CONF, $_GROUPS, $_TABLES, $_USER, $LANG01, $LANG21, $LANG24,$LANG_ACCESS,
           $LANG_ADMIN, $LANG_postmodes,$MESSAGE,$_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';
    $A = array();
    $editMode = false;

    if (!empty($bid)) {
        $editMode = true;
        $result = DB_query("SELECT * FROM {$_TABLES['blocks']} WHERE bid ='".DB_escapeString($bid)."'");
        $A = DB_fetchArray($result);
        $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);
        if ($access == 2 || $access == 0 || BLOCK_hasTopicAccess($A['tid']) < 3) {
            $retval .= COM_showMessageText($LANG21[45],$LANG_ACCESS['accessdenied'],true,'error');
            COM_accessLog("User {$_USER['username']} tried to illegally create or edit block ".$bid);
            return $retval;
        }
        if ($A['type'] == 'gldefault') {
            $retval .= BLOCK_editDefault($A,$access);
            return $retval;
        }
    } else {
        $A['bid'] = isset($B['bid']) ? $B['bid'] : 0;
        $A['is_enabled'] = isset($B['is_enabled']) ? $B['is_enabled'] : 1;
        $A['name'] = isset($B['name']) ? $B['name'] : '';
        $A['type'] = isset($B['type']) ? $B['type'] : 'normal';
        $A['title'] = isset($B['title']) ? $B['title'] : '';
        $A['tid'] = isset($B['tid']) ? $B['tid'] : 'All';
        $A['blockorder'] = isset($B['blockorder']) ? $B['blockorder'] : 0;
        $A['content'] = isset($B['content']) ? $B['content'] : '';
        $A['allow_autotags'] = isset($B['allow_autotags']) && $B['allow_autotags'] == 1 ? 1 : 0;
        $A['rdfurl'] = isset($B['rdfurl']) ? $B['rdfurl'] : '';
        $A['rdfupdated'] = isset($B['rdfupdated']) ? $B['rdfupdated'] : '';
        $A['rdflimit'] = isset($B['rdflimit']) ? $B['rdflimit'] : 0;
        $A['onleft'] = isset($B['onleft']) ? $B['onleft'] : 0;
        $A['phpblockfn'] = isset($B['phpblockfn']) ? $B['phpblockfn'] : '';
        $A['help'] = isset($B['help']) ? $B['help'] : '';
        $A['owner_id'] = isset($B['owner_id']) ? $B['owner_id'] : $_USER['uid'];
        if ( isset($B['group_id']) ) {
            $A['group_id'] = $B['group_id'];
        } else {
            if (isset ($_GROUPS['Block Admin'])) {
                $A['group_id'] = $_GROUPS['Block Admin'];
            } else {
                $A['group_id'] = SEC_getFeatureGroup ('block.edit');
            }
        }
        if ( isset($B['perm_owner']) ) {
            $A['perm_owner'] = SEC_getPermissionValue($B['perm_owner']);
            $A['perm_group'] = SEC_getPermissionValue($B['perm_group']);
            $A['perm_members'] = SEC_getPermissionValue($B['perm_members']);
            $A['perm_anon'] = SEC_getPermissionValue($B['perm_anon']);
        } else {
            SEC_setDefaultPermissions ($A, $_CONF['default_permissions_block']);
        }
        $access = 3;
    }
    if ( $editMode ) {
        $lang_menu_edit = $LANG01[4];
    } else {
        $lang_menu_edit = $LANG_ADMIN['create_new'];
    }

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/block.php',
              'text' => $LANG_ADMIN['block_list']),
        array('url' => $_CONF['site_admin_url'] . '/block.php?edit=x',
              'text' => $lang_menu_edit,'active'=>true),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $block_templates = new Template($_CONF['path_layout'] . 'admin/block');

    $block_templates->set_file('editor','blockeditor.thtml');

    $block_templates->set_var('start_block_editor', COM_startBlock ($LANG21[3],
            '', COM_getBlockTemplate ('_admin_block', 'header')));

    if (!empty($bid) && SEC_hasrights('block.delete')) {
        $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                   . '" name="delete"%s >';
        $jsconfirm = ' onclick="return confirm(\'' . $MESSAGE[76] . '\');"';
        $block_templates->set_var ('delete_option',
                                   sprintf ($delbutton, $jsconfirm));
        $block_templates->set_var ('delete_option_no_confirmation',
                                   sprintf ($delbutton, ''));
        $block_templates->set_var('lang_delete',$LANG_ADMIN['delete']);
        $block_templates->set_var('lang_delete_confirm',$MESSAGE[76]);
    }

    $block_templates->set_var('block_bid', $A['bid']);
    // standard Admin strings
    $block_templates->set_var('lang_blocktitle', $LANG_ADMIN['title']);
    $block_templates->set_var('lang_enabled', $LANG_ADMIN['enabled']);
    $block_templates->set_var('lang_blockhelpurl', $LANG_ADMIN['help_url']);
    $block_templates->set_var('lang_topic', $LANG_ADMIN['topic']);
    $block_templates->set_var('lang_save', $LANG_ADMIN['save']);

    $block_templates->set_var('lang_cancel', $LANG_ADMIN['cancel']);
    $block_templates->set_var('lang_blocktype', $LANG_ADMIN['type']);
    $block_templates->set_var('lang_allowed_html', $LANG01[123]);

    $block_templates->set_var('block_title', htmlspecialchars($A['title'],ENT_QUOTES,COM_getEncodingt()));
    $block_templates->set_var('lang_enabled', $LANG21[53]);
    if ($A['is_enabled'] == 1) {
        $block_templates->set_var('is_enabled', 'checked="checked"');
    } else {
        $block_templates->set_var('is_enabled', '');
    }
    $block_templates->set_var('block_help', $A['help']);
    $block_templates->set_var('lang_includehttp', $LANG21[51]);
    $block_templates->set_var('lang_explanation', $LANG21[52]);
    $block_templates->set_var('block_name', $A['name']);
    $block_templates->set_var('lang_blockname', $LANG21[48]);
    $block_templates->set_var('lang_nospaces', $LANG21[49]);
    $block_templates->set_var('lang_all', $LANG21[7]);
    $block_templates->set_var('lang_homeonly', $LANG21[43]);
    $block_templates->set_var('lang_nohomepage', $LANG21[44]);
    if ($A['tid'] == 'all') {
        $block_templates->set_var('all_selected', 'selected="selected"');
    } else if ($A['tid'] == 'homeonly') {
        $block_templates->set_var('homeonly_selected', 'selected="selected"');
    } else if ( $A['tid'] == 'allnhp' ) {
        $block_templates->set_var('nohomepage_selected','selected="selected"');
    }
    $block_templates->set_var('topic_options',
                              COM_topicList('tid,topic,sortnum', $A['tid'], 2, true));
    $block_templates->set_var('lang_side', $LANG21[39]);
    $block_templates->set_var('lang_left', $LANG21[40]);
    $block_templates->set_var('lang_right', $LANG21[41]);
    if ($A['onleft'] == 1) {
        $block_templates->set_var('left_selected', 'selected="selected"');
    } else if ($A['onleft'] == 0) {
        $block_templates->set_var('right_selected', 'selected="selected"');
    }
    $block_templates->set_var('lang_blockorder', $LANG21[9]);
    $block_templates->set_var('block_order', $A['blockorder']);
    $block_templates->set_var('lang_normalblock', $LANG21[12]);
    $block_templates->set_var('lang_phpblock', $LANG21[27]);
    $block_templates->set_var('lang_portalblock', $LANG21[11]);
    if ($A['type'] == 'normal') {
        $block_templates->set_var('normal_selected', 'selected="selected"');
    } else if ($A['type'] == 'phpblock') {
        $block_templates->set_var('php_selected', 'selected="selected"');
    } else if ($A['type'] == 'portal') {
        $block_templates->set_var('portal_selected', 'selected="selected"');
    }
    $block_templates->set_var('lang_accessrights', $LANG_ACCESS['accessrights']);
    $block_templates->set_var('lang_owner', $LANG_ACCESS['owner']);
    $ownername = COM_getDisplayName ($A['owner_id']);
    $block_templates->set_var('owner_username', DB_getItem($_TABLES['users'],
                                    'username', "uid = '{$A['owner_id']}'"));
    $block_templates->set_var('owner_name', $ownername);
    $block_templates->set_var('owner', $ownername);
    $block_templates->set_var('owner_id', $A['owner_id']);

    $block_templates->set_var('lang_group', $LANG_ACCESS['group']);
    $block_templates->set_var('group_dropdown',
                              SEC_getGroupDropdown ($A['group_id'], $access));
    $block_templates->set_var('lang_permissions', $LANG_ACCESS['permissions']);
    $block_templates->set_var('lang_perm_key', $LANG_ACCESS['permissionskey']);
    $block_templates->set_var('permissions_editor', SEC_getPermissionsHTML($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']));
    $block_templates->set_var('lang_permissions_msg', $LANG_ACCESS['permmsg']);
    $block_templates->set_var('lang_phpblockoptions', $LANG21[28]);
    $block_templates->set_var('lang_blockfunction', $LANG21[29]);
    $block_templates->set_var('block_phpblockfn', $A['phpblockfn']);
    $block_templates->set_var('lang_phpblockwarning', $LANG21[30]);
    $block_templates->set_var('lang_portalblockoptions', $LANG21[13]);
    $block_templates->set_var('lang_rdfurl', $LANG21[14]);
    $block_templates->set_var('max_url_length', 255);
    $block_templates->set_var('block_rdfurl', $A['rdfurl']);
    $block_templates->set_var('lang_rdflimit', $LANG21[62]);
    $block_templates->set_var('block_rdflimit', $A['rdflimit']);
    $block_templates->set_var('lang_lastrdfupdate', $LANG21[15]);
    if ($A['rdfupdated'] == '1000-01-01 00:00:00') {
        $block_templates->set_var ('block_rdfupdated', '');
    } else {
        $block_templates->set_var ('block_rdfupdated', $A['rdfupdated']);
    }
    $block_templates->set_var ('lang_normalblockoptions', $LANG21[16]);
    $block_templates->set_var ('lang_blockcontent', $LANG21[17]);
    $block_templates->set_var ('lang_autotags', $LANG21[66]);
    $block_templates->set_var ('lang_use_autotags', $LANG21[67]);
    $block_templates->set_var ('block_content',
                               htmlspecialchars ($A['content'],ENT_QUOTES,COM_getEncodingt()));
    $block_templates->set_var ('block_text',
                               htmlspecialchars ($A['content'],ENT_QUOTES,COM_getEncodingt()));
    $block_templates->set_var ('block_html',
                               htmlspecialchars ($A['content'],ENT_QUOTES,COM_getEncodingt()));
    if ($A['allow_autotags'] == 1) {
        $block_templates->set_var ('allow_autotags', 'checked="checked"');
    } else {
        $block_templates->set_var ('allow_autotags', '');
    }
    $block_templates->set_var('gltoken_name', CSRF_TOKEN);
    $block_templates->set_var('gltoken', SEC_createToken());
    $block_templates->set_var('admin_menu', ADMIN_createMenu($menu_arr,$LANG21[71],$_CONF['layout_url'] . '/images/icons/block.'. $_IMAGE_TYPE));
    $block_templates->set_var ('end_block',
            COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer')));
    PLG_templateSetVars('blockeditor',$block_templates);
    $block_templates->parse('output', 'editor');
    $retval .= $block_templates->finish($block_templates->get_var('output'));
    return $retval;
}

/**
 * return a field value for the block administration list
 *
 */
function BLOCK_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $LANG_ADMIN, $LANG21, $_IMAGE_TYPE;

    $retval = false;

    $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);
    $enabled = ($A['is_enabled'] == 1) ? true : false;

    if (($access > 0) && (BLOCK_hasTopicAccess($A['tid']) > 0)) {
        switch($fieldname) {

            case 'edit':
                $retval = '';
                if ($access == 3) {
                    $attr['title'] = $LANG_ADMIN['edit'];
                    $retval .= COM_createLink($icon_arr['edit'],
                        $_CONF['site_admin_url'] . '/block.php?edit=x&amp;bid=' . $A['bid'], $attr);
                }
                break;

            case 'blockorder':
                $order = $A['blockorder'];
                $retval = ($enabled) ? $order : '<span class="disabledfield">' . $order . '</span>';
                break;

            case 'move':
                if ($access == 3) {
                    if ($A['onleft'] == 1) {
                        $side = $LANG21[40];
                        $blockcontrol_image = 'block-right.' . $_IMAGE_TYPE;
                        $moveTitleMsg = $LANG21[59];
                        $switchside = '1';
                    } else {
                        $blockcontrol_image = 'block-left.' . $_IMAGE_TYPE;
                        $moveTitleMsg = $LANG21[60];
                        $switchside = '0';
                    }
                    $retval.="<img src=\"{$_CONF['layout_url']}/images/admin/$blockcontrol_image\" width=\"45\" height=\"20\" usemap=\"#arrow{$A['bid']}\" alt=\"\">"
                            ."<map id=\"arrow{$A['bid']}\" name=\"arrow{$A['bid']}\">"
                            ."<area coords=\"0,0,12,20\"  title=\"{$LANG21[58]}\" href=\"{$_CONF['site_admin_url']}/block.php?move=1&amp;bid={$A['bid']}&amp;where=up&amp;".CSRF_TOKEN."={$token}\" alt=\"{$LANG21[58]}\">"
                            ."<area coords=\"13,0,29,20\" title=\"$moveTitleMsg\" href=\"{$_CONF['site_admin_url']}/block.php?move=1&amp;bid={$A['bid']}&amp;where=$switchside&amp;".CSRF_TOKEN."={$token}\" alt=\"$moveTitleMsg\">"
                            ."<area coords=\"30,0,43,20\" title=\"{$LANG21[57]}\" href=\"{$_CONF['site_admin_url']}/block.php?move=1&amp;bid={$A['bid']}&amp;where=dn&amp;".CSRF_TOKEN."={$token}\" alt=\"{$LANG21[57]}\">"
                            ."</map>";
                }
                break;

            case 'name':
                $name =  COM_truncate($A['name'], 20, ' ...', true);
                $retval = ($enabled) ? $name : '<span class="disabledfield">' . $name . '</span>';
                break;

            case 'title':
                $title =  COM_truncate(strip_tags($A['title']), 20, ' ...', true);
                $retval = ($enabled) ? $title : '<span class="disabledfield">' . $title . '</span>';
                break;

            case 'tid':
                $topic =  COM_truncate($A['tid'], 20, ' ...', true);
                $retval = ($enabled) ? $topic : '<span class="disabledfield">' . $topic . '</span>';
                break;

            case 'delete':
                $retval = '';
                if ($access == 3 && $A['type'] != 'gldefault' ) {
                    $attr['title'] = $LANG_ADMIN['delete'];
                    $attr['onclick'] = "return confirm('" . $LANG21[69] . "');";
                    $retval .= COM_createLink($icon_arr['delete'],
                        $_CONF['site_admin_url'] . '/block.php'
                        . '?delete=x&amp;bid=' . $A['bid'] . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
                }
                break;

            case 'is_enabled':
                if ($access == 3) {
                    if ($enabled) {
                        $switch = ' checked="checked"';
                        $title = 'title="' . $LANG_ADMIN['disable'] . '" ';
                    } else {
                        $title = 'title="' . $LANG_ADMIN['enable'] . '" ';
                        $switch = '';
                    }
                    $retval = '<input class="blk-clicker" type="checkbox" id="enabledblocks['.$A['bid'].']" name="enabledblocks[' . $A['bid'] . ']" ' . $title
//                        . 'onclick="submit()" value="' . $A['onleft'] . '"' . $switch .'>';
                        . 'onclick="submit()" value="' . $A['bid'] . '"' . $switch .'>';

                    $retval .= '<input type="hidden" name="bidarray[' . $A['bid'] . ']" value="' . $A['onleft'] . '" >';
                }
                break;

            default:
                $retval = ($enabled) ? $fieldvalue : '<span class="disabledfield">' . $fieldvalue . '</span>';
                break;
        }
    }
    return $retval;
}


/**
 * display the block administration list
 *
 */
function BLOCK_list()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG21, $_IMAGE_TYPE, $blockInterface;

    USES_lib_admin();

    $retval = '';

    // writing the menu on top
    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/block.php',
              'text' => $LANG_ADMIN['block_list'],'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/block.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG21[19], '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG21[25],
        $_CONF['layout_url'] . '/images/icons/block.'. $_IMAGE_TYPE
    );

    BLOCK_reorder();

    // writing the list
    $header_arr = array(      # display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center', 'width' => '5%'),
        array('text' => $LANG21[65], 'field' => 'blockorder', 'sort' => true, 'align' => 'center', 'width' => '8%'),
        array('text' => $LANG21[46], 'field' => 'move', 'sort' => false, 'align' => 'center', 'width' => '8%'),
        array('text' => $LANG_ADMIN['name'], 'field' => 'name', 'sort' => true, 'width' => '18%', 'align' => 'center'),
        array('text' => $LANG_ADMIN['title'], 'field' => 'title', 'sort' => true, 'width' => '18%', 'align' => 'center'),
        array('text' => $LANG_ADMIN['topic'], 'field' => 'tid', 'sort' => true, 'align' => 'center', 'width' => '18%'),
        array('text' => $LANG_ADMIN['type'], 'field' => 'type', 'sort' => true, 'align' => 'center', 'width' => '9%'),
        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center', 'width' => '7%'),
        array('text' => $LANG_ADMIN['enabled'], 'field' => 'is_enabled', 'sort' => true, 'align' => 'center', 'width' => '9%')
    );

    $defsort_arr = array('field' => 'blockorder', 'direction' => 'asc');

    if ( isset($blockInterface['left']['title']) ) {
        $label = $blockInterface['left']['title'];
    } else {
        $label = $LANG21[40];
    }

    $text_arr = array(
        'title'      => $label,
        'form_url'   => $_CONF['site_admin_url'] . '/block.php'
    );

    $query_arr = array(
        'table' => 'blocks',
        'sql' => "SELECT * FROM {$_TABLES['blocks']} WHERE onleft = 1",
        'query_fields' => array('title', 'content'),
        'default_filter' => COM_getPermSql ('AND')
    );

    // embed a CSRF token as a hidden var at the top of each of the lists
    // this is used to validate block enable/disable

    $token = SEC_createToken();

    // blockenabler is a hidden field which if set, indicates that one of the
    // blocks has been enabled or disabled - the value is the onleft var

    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
        'bottom' => '<input type="hidden" name="blockenabler" value="1">'
    );

    $retval .= ADMIN_list(
        'blocks', 'BLOCK_getListField', $header_arr, $text_arr,
        $query_arr, $defsort_arr, '', $token, '', $form_arr
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    $query_arr = array(
        'table' => 'blocks',
        'sql' => "SELECT * FROM {$_TABLES['blocks']} WHERE onleft = 0",
        'query_fields' => array('title', 'content'),
        'default_filter' => COM_getPermSql ('AND')
    );

    if ( isset($blockInterface['right']['title']) ) {
        $label = $blockInterface['right']['title'];
    } else {
        $label = $LANG21[41];
    }

    $text_arr = array(
        'title'      => $label,
        'form_url'   => $_CONF['site_admin_url'] . '/block.php'
    );

    // blockenabler is a hidden field which if set, indicates that one of the
    // blocks has been enabled or disabled - the value is the onleft var

    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
        'bottom' => '<input type="hidden" name="blockenabler" value="0"/>'
    );

    $retval .= ADMIN_list (
        'blocks', 'BLOCK_getListField', $header_arr, $text_arr,
        $query_arr, $defsort_arr, '', $token, '', $form_arr
    );

    $outputHandle = outputHandler::getInstance();
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/admin.js',HEADER_PRIO_NORMAL,'text/javascript');

    return $retval;
}

/**
* Saves a block
*
* @param    string  $bid            Block ID
* @param    string  $name           Block name
* @param    string  $title          Block title
* @param    string  $type           Type of block
* @param    int     $blockorder     Order block appears relative to the others
* @param    string  $content        Content of block
* @param    string  $tid            Topic block should appear in
* @param    string  $rdfurl         URL to headline feed for portal blocks
* @param    string  $rdfupdated     Date RSS/RDF feed was last updated
* @param    string  $rdflimit       max. number of entries to import from feed
* @param    string  $phpblockfn     Name of php function to call to get content
* @param    int     $onleft         Flag indicates if block shows up on left or right
* @param    int     $owner_id       ID of owner
* @param    int     $group_id       ID of group block belongs to
* @param    array   $perm_owner     Permissions the owner has on the object
* @param    array   $perm_group     Permissions the group has on the object
* @param    array   $perm_members   Permissions the logged in members have
* @param    array   $perm_anon      Permissinos anonymous users have
* @param    int     $is_enabled     Flag, indicates if block is enabled or not
* @param    int     $allow_autotags Flag, indicates if autotags are enabed or not
* @return   string                  HTML redirect or error message
*
*/
function BLOCK_save($bid, $name, $title, $help, $type, $blockorder, $content, $tid, $rdfurl, $rdfupdated, $rdflimit, $phpblockfn, $onleft, $owner_id, $group_id, $perm_owner, $perm_group, $perm_members, $perm_anon, $is_enabled, $allow_autotags)
{
    global $_CONF, $_TABLES, $LANG01, $LANG21, $MESSAGE;

    $retval = '';

    $B['bid']           = (int) $bid;
    $B['name']          = $name;
    $B['title']         = $title;
    $B['type']          = $type;
    $B['blockorder']    = $blockorder;
    $B['content']       = $content;
    $B['tid']           = $tid;
    $B['rdfurl']        = $rdfurl;
    $B['rdfupdated']    = $rdfupdated;
    $B['rdflimit']      = $rdflimit;
    $B['phpblockfn']    = $phpblockfn;
    $B['onleft']        = $onleft;
    $B['owner_id']      = $owner_id;
    $B['group_id']      = $group_id;
    $B['perm_owner']    = $perm_owner;
    $B['perm_group']    = $perm_group;
    $B['perm_members']  = $perm_members;
    $B['perm_anon']     = $perm_anon;
    $B['is_enabled']    = $is_enabled;
    $B['allow_autotags'] = $allow_autotags;

    $bid   = (int) $bid;

    $MenuElementAllowedHTML = "i[class|style],div[class|style],span[class|style],img[src|class|style],em,strong,del,ins,q,abbr,dfn,small";
    $filter = sanitizer::getInstance();
    $allowedElements = $filter->makeAllowedElements($MenuElementAllowedHTML);
    $filter->setAllowedElements($allowedElements);
    $filter->setPostmode('html');
    $title = $filter->filterHTML($title);

    $title = DB_escapeString ($title);
    $phpblockfn = DB_escapeString (trim ($phpblockfn));

    if ( !BLOCK_validateName($name) ) {
        $msg = $LANG21[70];

        SEC_setCookie ($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral('advancededitor'),
                        time() + 1200, $_CONF['cookie_path'],
                        $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);
        $retval .= COM_siteHeader ('menu', $LANG21[63])
                . COM_showMessageText($msg,$LANG21[63],true,'error')
                . BLOCK_edit($bid,$B)
                . COM_siteFooter ();
        return $retval;
    }

    // Convert array values to numeric permission values
    list($perm_owner,$perm_group,$perm_members,$perm_anon) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);

    $access = 0;
    if (($bid > 0) && DB_count ($_TABLES['blocks'], 'bid', $bid) > 0) {
        $result = DB_query ("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['blocks']} WHERE bid = '{$bid}'");
        $A = DB_fetchArray ($result);
        $access = SEC_hasAccess ($A['owner_id'], $A['group_id'],
                $A['perm_owner'], $A['perm_group'], $A['perm_members'],
                $A['perm_anon']);
    } else {
        $access = SEC_hasAccess ($owner_id, $group_id, $perm_owner, $perm_group,
                $perm_members, $perm_anon);
    }
    if (($access < 3) || !BLOCK_hasTopicAccess($tid) || !SEC_inGroup ($group_id)) {
        $retval .= COM_siteHeader('menu', $MESSAGE[30]);
        $retval .= COM_showMessageText($MESSAGE[33],$MESSAGE[30],true,'error');
        $retval .= COM_siteFooter();
        COM_accessLog("User {$_USER['username']} tried to illegally create or edit block $bid.");

        return $retval;
    } elseif (($type == 'normal' && !empty($content)) OR ($type == 'portal' && !empty($rdfurl)) OR ($type == 'gldefault' && (strlen($blockorder)>0)) OR ($type == 'phpblock' && !empty($phpblockfn))) {
        if ($is_enabled == 'on') {
            $is_enabled = 1;
        } else {
            $is_enabled = 0;
        }
        if ($allow_autotags == 1) {
            $allow_autotags = 1;
        } else {
            $allow_autotags = 0;
        }

        if ($type == 'portal') {
            $content = '';
            $rdfupdated = '';
            $phpblockfn = '';

            // get rid of possible extra prefixes (e.g. "feed://http://...")
            if (substr ($rdfurl, 0, 4) == 'rss:') {
                $rdfurl = substr ($rdfurl, 4);
            } else if (substr ($rdfurl, 0, 5) == 'feed:') {
                $rdfurl = substr ($rdfurl, 5);
            }
            if (substr ($rdfurl, 0, 2) == '//') {
                $rdfurl = substr ($rdfurl, 2);
            }
            $rdfurl = COM_sanitizeUrl ($rdfurl, array ('http', 'https'));
        }
        if ($type == 'gldefault') {
            if ($name != 'older_stories') {
                $content = '';
            }
            $rdfurl = '';
            $rdfupdated = '';
            $rdflimit = 0;
            $phpblockfn = '';
        }
        if ($type == 'phpblock') {

            // NOTE: PHP Blocks must be within a function and the function
            // must start with phpblock_ as the prefix.  This will prevent
            // the arbitrary execution of code
            if (!(stristr($phpblockfn,'phpblock_'))) {
                $retval .= COM_siteHeader ('menu', $LANG21[37])
                        . COM_showMessageText($LANG21[38],$LANG21[37],true,'error')
                        . BLOCK_edit($bid,$B)
                        . COM_siteFooter ();
                return $retval;
            }
            $content = '';
            $rdfurl = '';
            $rdfupdated = '';
            $rdflimit = 0;
        }
        if ($type == 'normal') {
            $rdfurl = '';
            $rdfupdated = '';
            $rdflimit = 0;
            $phpblockfn = '';
            $content = DB_escapeString ($content);
        }
        if ($rdflimit < 0) {
            $rdflimit = 0;
        }
        if (!empty ($rdfurl)) {
            $rdfurl = DB_escapeString ($rdfurl);
        }
        if (empty ($rdfupdated)) {
            $rdfupdated = '1000-01-01 00:00:00';
        }

        $name = DB_escapeString($name);

        if ($bid > 0) {
            DB_save($_TABLES['blocks'],'bid,name,title,help,type,blockorder,content,tid,rdfurl,rdfupdated,rdflimit,phpblockfn,onleft,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon,is_enabled,allow_autotags,rdf_last_modified,rdf_etag',"$bid,'$name','$title','$help','$type','$blockorder','$content','$tid','$rdfurl','$rdfupdated','$rdflimit','$phpblockfn',$onleft,$owner_id,$group_id,$perm_owner,$perm_group,$perm_members,$perm_anon,$is_enabled,$allow_autotags,NULL,NULL");
        } else {
            $sql = "INSERT INTO {$_TABLES['blocks']} "
             .'(name,title,help,type,blockorder,content,tid,rdfurl,rdfupdated,rdflimit,phpblockfn,onleft,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon,is_enabled,allow_autotags) '
             ."VALUES ('$name','$title','$help','$type','$blockorder','$content','$tid','$rdfurl','$rdfupdated','$rdflimit','$phpblockfn',$onleft,$owner_id,$group_id,$perm_owner,$perm_group,$perm_members,$perm_anon,$is_enabled,$allow_autotags)";
             DB_query($sql);
             $bid = DB_insertId();
        }

        if (($type == 'gldefault') && ($name == 'older_stories')) {
            COM_olderStuff ();
        }
        CACHE_clear();
        COM_setMessage(11);
        return COM_refresh ($_CONF['site_admin_url'] . '/block.php');
    } else {
        SEC_setCookie ($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral('advancededitor'),
                        time() + 1200, $_CONF['cookie_path'],
                        $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);

        $retval .= COM_siteHeader ('menu', $LANG21[32]);
        if ($type == 'portal') {
            // Portal block is missing fields
            $msg = $LANG21[33];
        } else if ($type == 'phpblock') {
            // PHP Block is missing field
            $msg = $LANG21[34];
        } else if ($type == 'normal') {
            // Normal block is missing field
            $msg = $LANG21[35];
        } else if ($type == 'gldefault') {
            // Default glFusion field missing
            $msg = $LANG21[42];
        } else {
            // Layout block missing content
            $msg = $LANG21[36];
        }
        $retval .= COM_showMessageText($msg,$LANG21[32],true,'error');
        $retval .= BLOCK_edit($bid,$B);
        $retval .= COM_siteFooter ();
    }

    return $retval;
}
/**
*
* Re-orders all blocks in steps of 10
*
*/
function BLOCK_reorder()
{
    global $_TABLES;
    $sql = "SELECT * FROM {$_TABLES['blocks']} ORDER BY onleft asc, blockorder asc;";
    $result = DB_query($sql);
    $nrows = DB_numRows($result);

    $lastside = 0;
    $blockOrd = 10;
    $stepNumber = 10;

    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray($result);

        if ($lastside != $A['onleft']) { // we are switching left/right blocks
            $blockOrd = 10;              // so start with 10 again
        }
        if ($A['blockorder'] != $blockOrd) {  // only update incorrect ones
            $q = "UPDATE " . $_TABLES['blocks'] . " SET blockorder = '" .
                  $blockOrd . "' WHERE bid = '" . $A['bid'] ."'";
            DB_query($q);
        }
        $blockOrd += $stepNumber;
        $lastside = $A['onleft'];       // save variable for next round
    }
    return true;
}


/**
* Move blocks UP, Down and Switch Sides - Left and Right
*
*/
function BLOCK_move($bid, $where)
{
    global $_CONF, $_TABLES, $LANG21;

    $retval = '';

    // if the block id exists
    if (DB_count($_TABLES['blocks'], "bid", $bid) == 1) {

        switch ($where) {

            case ("up"): $q = "UPDATE " . $_TABLES['blocks'] . " SET blockorder = blockorder-11 WHERE bid = '" . $bid . "'";
                         DB_query($q);
                         break;

            case ("dn"): $q = "UPDATE " . $_TABLES['blocks'] . " SET blockorder = blockorder+11 WHERE bid = '" . $bid . "'";
                         DB_query($q);
                         break;

            case ("0"):  $q = "UPDATE " . $_TABLES['blocks'] . " SET onleft = '1', blockorder = blockorder-1 WHERE bid = '" . $bid ."'";
                         DB_query($q);
                         break;

            case ("1"):  $q = "UPDATE " . $_TABLES['blocks'] . " SET onleft = '0',blockorder = blockorder-1 WHERE bid = '" . $bid ."'";
                         DB_query($q);
                         break;
        }

    } else {
        COM_errorLOG("block admin error: Attempt to move an non existing block id: $bid");
    }
    echo COM_refresh($_CONF['site_admin_url'] . "/block.php");
    return $retval;
}


/**
* Enable and Disable block
*/
function BLOCK_toggleStatus($side, $bid_arr, $bidarray)
{
    global $_CONF, $_TABLES;

    if (isset($bidarray) ) {
        foreach ($bidarray AS $bid => $side ) {
            $bid = (int) $bid;
            $side = (int) $side;
            if ( isset($bid_arr[$bid]) ) {
                $sql = "UPDATE {$_TABLES['blocks']} SET is_enabled = '1' WHERE bid=$bid AND onleft=$side";
                DB_query($sql);
            } else {
                $sql = "UPDATE {$_TABLES['blocks']} SET is_enabled = '0' WHERE bid=$bid AND onleft=$side";
                DB_query($sql);
            }
        }
    }

    return;
}

/**
* Delete a block
*
* @param    string  $bid    id of block to delete
* @return   string          HTML redirect or error message
*
*/
function BLOCK_delete($bid)
{
    global $_CONF, $_TABLES, $_USER;

    $result = DB_query ("SELECT tid,owner_id,type,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['blocks']} WHERE bid ='$bid'");
    $A = DB_fetchArray($result);

    if ( $A['type'] == 'gldefault' ) {
        return COM_refresh ($_CONF['site_admin_url'] . '/block.php');
    }

    $access = SEC_hasAccess ($A['owner_id'], $A['group_id'], $A['perm_owner'],
            $A['perm_group'], $A['perm_members'], $A['perm_anon']);
    if (($access < 3) || (BLOCK_hasTopicAccess($A['tid']) < 3)) {
        COM_accessLog ("User {$_USER['username']} tried to illegally delete block $bid.");
        return COM_refresh ($_CONF['site_admin_url'] . '/block.php');
    }

    DB_delete ($_TABLES['blocks'], 'bid', $bid);
    CACHE_clear();
    return COM_refresh ($_CONF['site_admin_url'] . '/block.php?msg=12');
}

/**
* Check to see if the block name contains invalid characters.
*
* @param string $name   block name
*
* @return	boolean     true if OK, false if not
*/
function BLOCK_validateName($name)
{
    $regex = '[\x00-\x1F\x7F<> \'"%&*\/\\\\]';
	// ... fast checks first.
	if ( strlen($name) < 1 ) {
	    return false;
	}
	if (strpos($name, '&quot;') !== false || strpos($name, '"') !== false ) {
		return false;
	}
	if (preg_match('/' . $regex . '/u', $name)) {
    	return false;
	}
	return true;
}

// MAIN ========================================================================

$action = '';
$expected = array('edit','save','move','delete','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	    $action = $provided;
    }
}

$bid = 0;
if (isset($_POST['bid'])) {
    $bid = COM_applyFilter($_POST['bid'], true);
} elseif (isset($_GET['bid'])) {
    $bid = COM_applyFilter($_GET['bid'], true);
}

$where = '';
if (isset($_POST['where'])) {
    $where = COM_applyFilter($_POST['where']);
} elseif (isset($_GET['where'])) {
    $where = COM_applyFilter($_GET['where']);
}

if (isset($_POST['blockenabler']) && SEC_checkToken()) {
    $side = COM_applyFilter($_POST['blockenabler'], true);
    $enabledblocks = array();
    if (isset($_POST['enabledblocks'])) {
        $enabledblocks = $_POST['enabledblocks'];
    }
    $bidarray = array();
    if ( isset($_POST['bidarray']) ) {
        $bidarray = $_POST['bidarray'];
    }
    BLOCK_toggleStatus($side, $enabledblocks, $bidarray);
}

switch ($action) {

    case 'edit':
        SEC_setCookie($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral('advancededitor'),
                       time() + 1200, $_CONF['cookie_path'],
                       $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);
        $display .= COM_siteHeader('menu', $LANG21[3])
                 . BLOCK_edit($bid)
                 . COM_siteFooter();
        break;

    case 'save':
        if (SEC_checkToken()) {
            $help = '';
            if (isset ($_POST['help'])) {
                $help = COM_sanitizeUrl ($_POST['help'], array ('http', 'https'));
            }

            $content = '';
            $content = isset($_POST['content']) ? $_POST['content'] : '';
//@TODO look to see if $html is needed elsewhere...

            $rdfurl         = isset ($_POST['rdfurl'])         ? COM_applyFilter($_POST['rdfurl']) : '';
            $rdfupdated     = isset ($_POST['rdfupdated'])     ? $_POST['rdfupdated'] : '';
            $rdflimit       = isset ($_POST['rdflimit'])       ? COM_applyFilter ($_POST['rdflimit'], true) : 0;
            $phpblockfn     = isset ($_POST['phpblockfn'])     ? COM_applyFilter($_POST['phpblockfn']) : '';
            $is_enabled     = isset ($_POST['is_enabled'])     ? 'on' : '';
            $allow_autotags = isset ($_POST['allow_autotags']) ? 1 : 0;
            $name           = isset ($_POST['name'])        ? $_POST['name'] : '';
            $title          = isset ($_POST['title'])       ? $_POST['title'] : '';
            $type           = isset ($_POST['type'])        ? COM_applyFilter($_POST['type']) : '';
            $blockorder     = isset ($_POST['blockorder'])  ? COM_applyFilter($_POST['blockorder'],true) : 0;
            $tid            = isset ($_POST['tid'])         ? COM_applyFilter($_POST['tid']) : '';
            $onleft         = isset ($_POST['onleft'])      ? COM_applyFilter($_POST['onleft']) : '';
            $owner_id       = isset ($_POST['owner_id'])    ? COM_applyFilter($_POST['owner_id'],true) : 2;
            $group_id       = isset ($_POST['group_id'])    ? COM_applyFilter($_POST['group_id'],true) : 0;
            $perm_owner     = isset ($_POST['perm_owner'])  ? $_POST['perm_owner'] : array();
            $perm_group     = isset ($_POST['perm_group'])  ? $_POST['perm_group'] : array();
            $perm_members   = isset ($_POST['perm_members']) ? $_POST['perm_members'] : array();
            $perm_anon      = isset ($_POST['perm_anon'])   ? $_POST['perm_anon'] : array();

            $display .= BLOCK_save( $bid,
                                    $name,
                                    $title,
                                    $help,
                                    $type,
                                    $blockorder,
                                    $content,
                                    $tid,
                                    $rdfurl,
                                    $rdfupdated,
                                    $rdflimit,
                                    $phpblockfn,
                                    $onleft,
                                    $owner_id,
                                    $group_id,
                                    $perm_owner,
                                    $perm_group,
                                    $perm_members,
                                    $perm_anon,
                                    $is_enabled,
                                    $allow_autotags
                                  );
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally edit block $bid and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'move':
        $display .= COM_siteHeader('menu', $LANG21[19]);
        if(SEC_checkToken()) {
            $display .= BLOCK_move($bid, $where);
        }
        $display .= BLOCK_list();
        $display .= COM_siteFooter();
        break;

    case 'delete':
        if (!isset ($bid) || empty ($bid) || ($bid == 0)) {
            COM_errorLog('Attempted to delete block, bid empty or null, value =' . $bid);
            $display .= COM_refresh($_CONF['site_admin_url'] . '/block.php');
        } elseif (SEC_checkToken()) {
            $display .= BLOCK_delete($bid);
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally delete block $bid and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        $display .= COM_siteHeader ('menu', $LANG21[19]);
        $msg = COM_getMessage();
        $display .= ($msg > 0) ? COM_showMessage($msg) : '';
        $display .= BLOCK_list();
        $display .= COM_siteFooter();
        break;

}

echo $display;

?>