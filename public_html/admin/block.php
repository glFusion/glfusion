<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | block.php                                                                |
// |                                                                          |
// | glFusion block administration.                                           |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
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
require_once $_CONF['path_system'] . 'lib-security.php';

if (!SEC_hasRights ('block.edit')) {
    $pageHandle->displayAccessError($MESSAGE[30],$MESSAGE[33],'block administration');
}

$pageHandle->setShowExtraBlocks(false);

/**
* Check for block topic access (need to handle 'all' and 'homeonly' as
* special cases)
*
* @param    string  $tid    ID for topic to check on
* @return   int             returns 3 for read/edit 2 for read only 0 for no access
*
*/
function hasBlockTopicAccess ($tid)
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
function editdefaultblock ($A, $access)
{
    global $_CONF, $_TABLES, $_USER, $LANG21, $LANG_ACCESS, $LANG_ADMIN;

    $retval = '';

    $retval .= COM_startBlock ($LANG21[3], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $block_templates = new Template($_CONF['path_layout'] . 'admin/block');
    $block_templates->set_file('editor','defaultblockeditor.thtml');
    $block_templates->set_var('block_id', $A['bid']);
    $block_templates->set_var('block_title', $A['title']);
    if ($A['is_enabled'] == 1) {
        $block_templates->set_var('is_enabled', 'checked="checked"');
    } else {
        $block_templates->set_var('is_enabled', '');
    }
    $block_templates->set_var('block_help', $A['help']);
    $block_templates->set_var('block_name',$A['name']);
    if ($A['tid'] == 'all') {
        $block_templates->set_var('all_selected', 'selected="selected"');
    } else if ($A['tid'] == 'homeonly') {
        $block_templates->set_var('homeonly_selected', 'selected="selected"');
    }
    $block_templates->set_var('topic_options',
                              COM_topicList ('tid,topic', $A['tid'], 1, true));

    if ($A['onleft'] == 1) {
        $block_templates->set_var('left_selected', 'selected="selected"');
    } else if ($A['onleft'] == 0) {
        $block_templates->set_var('right_selected', 'selected="selected"');
    }
    $block_templates->set_var('block_order', $A['blockorder']);
    $ownername = COM_getDisplayName ($A['owner_id']);
    $block_templates->set_var('owner_username', DB_getItem($_TABLES['users'],
                                    'username', "uid = '{$A['owner_id']}'"));
    $block_templates->set_var('owner_name', $ownername);
    $block_templates->set_var('owner', $ownername);
    $block_templates->set_var('owner_id', $A['owner_id']);

    $block_templates->set_var('group_dropdown',
                              SEC_getGroupDropdown ($A['group_id'], $access));
    $block_templates->set_var('group_name', DB_getItem ($_TABLES['groups'],
                                    'grp_name', "grp_id = '{$A['group_id']}'"));
    $block_templates->set_var('group_id', $A['group_id']);
    $block_templates->set_var('permissions_editor', SEC_getPermissionsHTML($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']));
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
* send it off to editdefaultblock.
*
* @param    string  $bid    ID of block to edit
* @return   string          HTML for block editor
*
*/
function editblock ($bid = '')
{
    global $_CONF, $_GROUPS, $_TABLES, $_USER, $LANG01, $LANG21, $LANG24,$LANG_ACCESS,
           $LANG_ADMIN, $LANG_postmodes,$MESSAGE,$pageHandle;

    $retval = '';

    if (!empty($bid)) {
        $sql = "SELECT * FROM {$_TABLES['blocks']} WHERE bid ='$bid'";

        $result = DB_query($sql);
        $A = DB_fetchArray($result);
        $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);
        if ($access == 2 || $access == 0 || hasBlockTopicAccess ($A['tid']) < 3) {
            $pageHandle->displayAccessError($LANG_ACCESS['accessdenied'],$LANG21[45],'create or edit block $bid.');
        }
        if ($A['type'] == 'gldefault') {
            $retval .= editdefaultblock($A,$access);
            return $retval;
        }
    } else {
        $A['bid'] = 0;
        $A['is_enabled'] = 1;
        $A['name'] = '';
        $A['type'] = 'normal';
        $A['title'] = '';
        $A['tid'] = 'All';
        $A['blockorder'] = 0;
        $A['content'] = '';
        $A['allow_autotags'] = 0;
        $A['rdfurl'] = '';
        $A['rdfupdated'] = '';
        $A['rdflimit'] = 0;
        $A['onleft'] = 0;
        $A['phpblockfn'] = '';
        $A['help'] = '';
        $A['postmode'] = 'html';
        $A['owner_id'] = $_USER['uid'];
        if (isset ($_GROUPS['Block Admin'])) {
            $A['group_id'] = $_GROUPS['Block Admin'];
        } else {
            $A['group_id'] = SEC_getFeatureGroup ('block.edit');
        }
        SEC_setDefaultPermissions ($A, $_CONF['default_permissions_block']);
        $access = 3;
    }
    $A['postmode'] = 'html';
    $retval .= editnormalblock($A,$access);
    return $retval;
}

function editnormalblock($A,$access) {
    global $_CONF, $_GROUPS, $_TABLES, $_USER, $LANG01, $LANG21, $LANG24,$LANG_ACCESS,
           $LANG_ADMIN, $LANG_postmodes,$MESSAGE,$pageHandle;

    $retval = '';

    $A['postmode'] = 'html';

    $block_templates = new Template($_CONF['path_layout'] . 'admin/block');

   if (isset ($_CONF['advanced_editor']) && ($_CONF['advanced_editor'] == 1) ) {
        $block_templates->set_file ('editor', 'blockeditor_advanced.thtml');
        if ( file_exists($_CONF['path_layout'] . '/fckstyles.xml') ) {
            $block_templates->set_var('glfusionStyleBasePath',$_CONF['layout_url']);
        } else {
            $block_templates->set_var('glfusionStyleBasePath',$_CONF['site_url'] . '/fckeditor');
        }
        $block_templates->set_var('show_htmleditor','none');
        $block_templates->set_var('show_texteditor','');

        $post_options = '<option value="html" selected="selected">'.$LANG_postmodes['html'].'</option>';
        if ($A['postmode'] == 'adveditor') {
            $post_options .= '<option value="adveditor" selected="selected">'.$LANG24[86].'</option>';
        } else {
            $post_options .= '<option value="adveditor">'.$LANG24[86].'</option>';
        }

        $block_templates->set_var('post_options',$post_options );
        $block_templates->set_var ('change_editormode', 'onchange="change_editmode(this);"');
    } else {
        $block_templates->set_file('editor','blockeditor.thtml');
    }
    $block_templates->set_var('start_block_editor', COM_startBlock ($LANG21[3],
            '', COM_getBlockTemplate ('_admin_block', 'header')));

    if (!empty($A['bid']) && SEC_hasrights('block.delete')) {
        $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                   . '" name="mode"%s' . XHTML . '>';
        $jsconfirm = ' onclick="return confirm(\'' . $MESSAGE[76] . '\');"';
        $block_templates->set_var ('delete_option',
                                   sprintf ($delbutton, $jsconfirm));
        $block_templates->set_var ('delete_option_no_confirmation',
                                   sprintf ($delbutton, ''));
    }

    $block_templates->set_var('block_bid', $A['bid']);
    // standard Admin strings

    $block_templates->set_var('block_title', $A['title']);
    if ($A['is_enabled'] == 1) {
        $block_templates->set_var('is_enabled', 'checked="checked"');
    } else {
        $block_templates->set_var('is_enabled', '');
    }
    $block_templates->set_var('block_help', $A['help']);
    $block_templates->set_var('block_name', $A['name']);
    if ($A['tid'] == 'all') {
        $block_templates->set_var('all_selected', 'selected="selected"');
    } else if ($A['tid'] == 'homeonly') {
        $block_templates->set_var('homeonly_selected', 'selected="selected"');
    }
    $block_templates->set_var('topic_options',
                              COM_topicList('tid,topic', $A['tid'], 1, true));
    if ($A['onleft'] == 1) {
        $block_templates->set_var('left_selected', 'selected="selected"');
    } else if ($A['onleft'] == 0) {
        $block_templates->set_var('right_selected', 'selected="selected"');
    }
    $block_templates->set_var('block_order', $A['blockorder']);
    if ($A['type'] == 'normal') {
        $block_templates->set_var('normal_selected', 'selected="selected"');
    } else if ($A['type'] == 'phpblock') {
        $block_templates->set_var('php_selected', 'selected="selected"');
    } else if ($A['type'] == 'portal') {
        $block_templates->set_var('portal_selected', 'selected="selected"');
    }
    $ownername = COM_getDisplayName ($A['owner_id']);
    $block_templates->set_var('owner_username', DB_getItem($_TABLES['users'],
                                    'username', "uid = '{$A['owner_id']}'"));
    $block_templates->set_var('owner_name', $ownername);
    $block_templates->set_var('owner', $ownername);
    $block_templates->set_var('owner_id', $A['owner_id']);

    $block_templates->set_var('group_dropdown',
                              SEC_getGroupDropdown ($A['group_id'], $access));
    $block_templates->set_var('permissions_editor', SEC_getPermissionsHTML($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']));
    $block_templates->set_var('block_phpblockfn', $A['phpblockfn']);
    $block_templates->set_var('max_url_length', 255);
    $block_templates->set_var('block_rdfurl', $A['rdfurl']);
    $block_templates->set_var('block_rdflimit', $A['rdflimit']);
    if ($A['rdfupdated'] == '0000-00-00 00:00:00') {
        $block_templates->set_var ('block_rdfupdated', '');
    } else {
        $block_templates->set_var ('block_rdfupdated', $A['rdfupdated']);
    }
    $block_templates->set_var ('block_content', $A['content']);
    $block_templates->set_var ('block_text', $A['content']);
    $block_templates->set_var ('block_html', $A['content']);
    if ($A['allow_autotags'] == 1) {
        $block_templates->set_var ('allow_autotags', 'checked="checked"');
    } else {
        $block_templates->set_var ('allow_autotags', '');
    }
    $block_templates->set_var('gltoken_name', CSRF_TOKEN);
    $block_templates->set_var('gltoken', SEC_createToken());
    $block_templates->set_var ('end_block',
            COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer')));
    $block_templates->parse('output', 'editor');
    $retval .= $block_templates->finish($block_templates->get_var('output'));

    return $retval;
}

function listblocks()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG21, $pageHandle;

    require_once $_CONF['path_system'] . 'lib-admin.php';

    $retval = '';
    $token = SEC_createToken();

    // writing the menu on top
    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/block.php?mode=edit',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG21[19], '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG21[25],
        $pageHandle->getImage('icons/block.png')
    );

    reorderblocks();

    // writing the list
    $header_arr = array(      # display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false),
        array('text' => $LANG21[65], 'field' => 'blockorder', 'sort' => true),
        array('text' => $LANG21[46], 'field' => 'move', 'sort' => false),
        array('text' => $LANG_ADMIN['title'], 'field' => 'title', 'sort' => true),
        array('text' => $LANG_ADMIN['type'], 'field' => 'type', 'sort' => true),
        array('text' => $LANG_ADMIN['topic'], 'field' => 'tid', 'sort' => true),
        array('text' => $LANG_ADMIN['enabled'], 'field' => 'is_enabled', 'sort' => true)
    );

    $defsort_arr = array('field' => 'blockorder', 'direction' => 'asc');

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . '/block.php'
    );

    $query_arr = array(
        'table' => 'blocks',
        'sql' => "SELECT * FROM {$_TABLES['blocks']} WHERE onleft = 1",
        'query_fields' => array('title', 'content'),
        'default_filter' => COM_getPermSql ('AND')
    );

    // this is a dummy variable so we know the form has been used if all blocks
    // should be disabled on one side in order to disable the last one.
    // The value is the onleft var
    $form_arr = array('bottom' => '<input type="hidden" name="blockenabler" value="1"' . XHTML . '>');

    $retval .= ADMIN_list(
        'blocks', 'ADMIN_getListField_blocks', $header_arr, $text_arr,
        $query_arr, $defsort_arr, '', $token, '', $form_arr
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    $query_arr = array(
        'table' => 'blocks',
        'sql' => "SELECT * FROM {$_TABLES['blocks']} WHERE onleft = 0",
        'query_fields' => array('title', 'content'),
        'default_filter' => COM_getPermSql ('AND')
    );

    $text_arr = array(
        'has_extras' => true,
        'title'      => "$LANG21[19] ($LANG21[41])",
        'form_url'   => $_CONF['site_admin_url'] . '/block.php'
    );

    // this is a dummy-variable so we know the form has been used if all blocks should be disabled
    // on one side in order to disable the last one. The value is the onleft var
    $form_arr = array('bottom' => '<input type="hidden" name="blockenabler" value="0"' . XHTML . '>');

    $retval .= ADMIN_list (
        'blocks', 'ADMIN_getListField_blocks', $header_arr, $text_arr,
        $query_arr, $defsort_arr, '', $token, '', $form_arr
    );

    return $retval;
}

/**
* Saves a block
*
* @param    string  $bid            Block ID
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
* @return   string                  HTML redirect or error message
*
*/
function saveblock ($bid, $name, $title, $help, $type, $blockorder, $content, $tid, $rdfurl, $rdfupdated, $rdflimit, $phpblockfn, $onleft, $owner_id, $group_id, $perm_owner, $perm_group, $perm_members, $perm_anon, $is_enabled, $allow_autotags)
{
    global $_CONF, $_TABLES, $LANG01, $LANG21, $MESSAGE, $_USER, $_GROUPS,
           $inputHandler, $pageHandle;

    $retval = '';

    $A['bid'] = $bid;
    $A['is_enabled'] = ($is_enabled == 'on' ? 1 : 0);
    $A['name'] = $name;
    $A['type'] = $type;
    $A['title'] = $title;
    $A['tid'] = $tid;
    $A['blockorder'] = $blockorder;
    $A['content'] = $content;
    $A['allow_autotags'] = ($allow_autotags == 'on' ? 1 : 0);
    $A['rdfurl'] = $rdfurl;
    $A['rdfupdated'] = $rdfupdated;
    $A['rdflimit'] = $rdflimit;
    $A['onleft'] = $onleft;
    $A['phpblockfn'] = $phpblockfn;
    $A['help'] = $help;
    $A['owner_id'] = $owner_id;
    $A['group_id'] = $group_id;
    $A['perm_owner'] =  $perm_owner;
    $A['perm_group'] = $perm_group;
    $A['perm_members'] =  $perm_members;
    $A['perm_anon'] = $perm_anon;


    // Convert array values to numeric permission values
    list($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);

    $access = 0;
    if (($A['bid'] > 0) && DB_count ($_TABLES['blocks'], 'bid', $A['bid']) > 0) {
        $result = DB_query ("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['blocks']} WHERE bid = '{$A['bid']}'");
        $B = DB_fetchArray ($result);
        $access = SEC_hasAccess ($B['owner_id'], $B['group_id'],
                $B['perm_owner'], $B['perm_group'], $B['perm_members'],
                $B['perm_anon']);
    } else {
        $access = SEC_hasAccess ($owner_id, $group_id, $perm_owner, $perm_group,
                $perm_members, $perm_anon);
    }

    if ( !SEC_checkToken() ) {
        $pageHandle->setPageTitle($LANG21[3]);
        $pageHandle->addMessageText($MESSAGE[501]);
        $pageHandle->addContent(editnormalblock($A,$access));
        $pageHandle->displayPage();
        exit;
    }

    if (empty($A['title'])) {
        $pageHandle->setPageTitle($LANG21[63]);
        $pageHandle->addMessageText($LANG21[64]);
        $pageHandle->addContent(editnormalblock($A,$access));
        $pageHandle->displayPage();
        exit;
    }


    if (($access < 3) || !hasBlockTopicAccess ($A['tid']) || !SEC_inGroup ($A['group_id'])) {
        $pageHandle->displayAccessError($MESSAGE[30],$MESSAGE[33],'create or edit block ' . $A['bid']);
    } elseif (($A['type'] == 'normal' && !empty($A['content'])) OR ($A['type'] == 'portal' && !empty($A['rdfurl'])) OR ($A['type'] == 'gldefault' && (strlen($A['blockorder'])>0)) OR ($A['type'] == 'phpblock' && !empty($A['phpblockfn']))) {

        if ($A['type'] == 'portal') {
            $A['content'] = '';
            $A['rdfupdated'] = '';
            $A['phpblockfn'] = '';

            // get rid of possible extra prefixes (e.g. "feed://http://...")
            if (substr ($A['rdfurl'], 0, 4) == 'rss:') {
                $A['rdfurl'] = substr ($A['rdfurl'], 4);
            } else if (substr ($A['rdfurl'], 0, 5) == 'feed:') {
                $A['rdfurl'] = substr ($A['rdfurl'], 5);
            }
            if (substr ($A['rdfurl'], 0, 2) == '//') {
                $A['rdfurl'] = substr ($A['rdfurl'], 2);
            }
            $A['rdfurl'] = $inputHandler->filterVar('url',$A['rdfurl'],'',array('http','https'));
        }
        if ($A['type'] == 'gldefault') {
            if ($A['name'] != 'older_stories') {
                $A['content'] = '';
            }
            $A['rdfurl'] = '';
            $A['rdfupdated'] = '';
            $A['rdflimit'] = 0;
            $A['phpblockfn'] = '';
        }
        if ($A['type'] == 'phpblock') {
            // NOTE: PHP Blocks must be within a function and the function
            // must start with phpblock_ as the prefix.  This will prevent
            // the arbitrary execution of code
            if (!(stristr($A['phpblockfn'],'phpblock_'))) {
                $pageHandle->setPageTitle($LANG21[37]);
                $pageHandle->addMessageText($LANG21[38]);
                $pageHandle->addContent(editnormalblock($A,$access));
                $pageHandle->displayPage();
                exit;
            }
            $A['content'] = '';
            $A['rdfurl'] = '';
            $A['rdfupdated'] = '';
            $A['rdflimit'] = 0;
        }
        if ($A['type'] == 'normal') {
            $A['rdfurl'] = '';
            $A['rdfupdated'] = '';
            $A['rdflimit'] = 0;
            $A['phpblockfn'] = '';
            $A['content'] = $inputHandler->filterVar('sql',$A['content'],'');
        }
        if ($A['rdflimit'] < 0) {
            $A['rdflimit'] = 0;
        }
        $A['rdfurl'] = $inputHandler->filterVar('sql',$A['rdfurl'],'');

        if (empty ($A['rdfupdated'])) {
            $A['rdfupdated'] = '0000-00-00 00:00:00';
        }

        /*
         * apply any special filtering...
         */

        $A['title']      = $inputHandler->filterVar('sql', $title, '','');
        $A['phpblockfn'] = $inputHandler->filterVar('sql',trim ($phpblockfn),'');

        if ($A['bid'] > 0) {
            DB_save($_TABLES['blocks'],'bid,name,title,help,type,blockorder,content,tid,rdfurl,rdfupdated,rdflimit,phpblockfn,onleft,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon,is_enabled,allow_autotags,rdf_last_modified,rdf_etag',
                    "{$A['bid']},'{$A['name']}','{$A['title']}','{$A['help']}','{$A['type']}',
                    '{$A['blockorder']}','{$A['content']}','{$A['tid']}','{$A['rdfurl']}',
                    '{$A['rdfupdated']}','{$A['rdflimit']}','{$A['phpblockfn']}',{$A['onleft']},
                    {$A['owner_id']},{$A['group_id']},{$A['perm_owner']},{$A['perm_group']},{$A['perm_members']},{$A['perm_anon']},{$A['is_enabled']},{$A['allow_autotags']},NULL,NULL");
        } else {
            $sql = "INSERT INTO {$_TABLES['blocks']} "
             .'(name,title,help,type,blockorder,content,tid,rdfurl,rdfupdated,rdflimit,phpblockfn,onleft,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon,is_enabled,allow_autotags) '
             ."VALUES ('{$A['name']}','{$A['title']}','{$A['help']}','{$A['type']}',
                    '{$A['blockorder']}','{$A['content']}','{$A['tid']}','{$A['rdfurl']}',
                    '{$A['rdfupdated']}','{$A['rdflimit']}','{$A['phpblockfn']}',{$A['onleft']},
                    {$A['owner_id']},{$A['group_id']},{$A['perm_owner']},{$A['perm_group']},{$A['perm_members']},{$A['perm_anon']},{$A['is_enabled']},{$A['allow_autotags']})";
             DB_query($sql);
             $A['bid'] = DB_insertId();
        }

        if (($A['type'] == 'gldefault') && ($A['name'] == 'older_stories')) {
            COM_olderStuff ();
        }
        $pageHandle->redirect($_CONF['site_admin_url'] . '/block.php?msg=11');
    } else {
        $pageHandle->setPageTitle($LANG21[32]);
        $pageHandle->addContent(
                COM_startBlock ($LANG21[32], '',
                          COM_getBlockTemplate ('_msg_block', 'header')));
        if ($type == 'portal') {
            // Portal block is missing fields
            $pageHandle->addContent($LANG21[33]);
        } else if ($type == 'phpblock') {
            // PHP Block is missing field
            $pageHandle->addContent($LANG21[34]);
        } else if ($type == 'normal') {
            // Normal block is missing field
            $pageHandle->addContent($LANG21[35]);
        } else if ($type == 'gldefault') {
            // Default glFusion field missing
            $pageHandle->addContent($LANG21[42]);
        } else {
            // Layout block missing content
            $pageHandle->addContent($LANG21[36]);
        }
        @setcookie ($_CONF['cookie_name'].'fckeditor', SEC_createTokenGeneral('advancededitor'),
                    time() + 1200, $_CONF['cookie_path'],
                   $_CONF['cookiedomain'], $_CONF['cookiesecure']);
        $pageHandle->addContent(COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'))
                . editnormalblock ($A,$access));
        $pageHandle->displayPage();
    }

    return $retval;
}
/**
*
* Re-orders all blocks in steps of 10
*
*/
function reorderblocks()
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
}


/**
* Move blocks UP, Down and Switch Sides - Left and Right
*
*/
function moveBlock()
{
    global $_CONF, $_TABLES, $LANG21, $pageHandle, $inputHandler;

    $retval = '';

    $bid    = $inputHandler->getVar('strict','bid','get','');
    $where  = $inputHandler->getVar('strict','where','get','');

    // prepare for use in an sql call
    $bid    = $inputHandler->filterVar('sql',$bid,'');

    // if the block id exists
    if (DB_count($_TABLES['blocks'], "bid", $bid) == 1) {

        switch ($where) {

            case ("up"): $q = "UPDATE {$_TABLES['blocks']} SET blockorder = blockorder-11 WHERE bid = '" . $bid . "'";
                         DB_query($q);
                         break;

            case ("dn"): $q = "UPDATE {$_TABLES['blocks']} SET blockorder = blockorder+11 WHERE bid = '" . $bid . "'";
                         DB_query($q);
                         break;

            case ("0"):  $q = "UPDATE {$_TABLES['blocks']} SET onleft = '1', blockorder = blockorder-1 WHERE bid = '" . $bid ."'";
                         DB_query($q);
                         break;

            case ("1"):  $q = "UPDATE {$_TABLES['blocks']} SET onleft = '0',blockorder = blockorder-1 WHERE bid = '" . $bid ."'";
                         DB_query($q);
                         break;
        }

    } else {
        COM_errorLOG("block admin error: Attempt to move an non existing block id: $bid");
    }
    $pageHandle->redirect($_CONF['site_admin_url'] . "/block.php");
    exit;
    return $retval;
}


/**
* Enable and Disable block
*/
function changeBlockStatus($side, $bid_arr)
{
    global $_CONF, $_TABLES, $inputHandler;

    // first, disable all on the requested side
    $side = $inputHandler->filterVar('int',$side,'');
    $sql = "UPDATE {$_TABLES['blocks']} SET is_enabled = '0' WHERE onleft='$side';";
    DB_query($sql);
    if (isset($bid_arr)) {
        foreach ($bid_arr as $bid => $side) {
            $bid = $inputHandler->filterVar('int',$bid,'');
            // the enable those in the array
            $sql = "UPDATE {$_TABLES['blocks']} SET is_enabled = '1' WHERE bid='$bid' AND onleft='$side'";
            DB_query($sql);
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
function deleteBlock ($bid)
{
    global $_CONF, $_TABLES, $_USER, $pageHandle;

    $result = DB_query ("SELECT tid,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['blocks']} WHERE bid ='$bid'");
    $A = DB_fetchArray($result);
    $access = SEC_hasAccess ($A['owner_id'], $A['group_id'], $A['perm_owner'],
            $A['perm_group'], $A['perm_members'], $A['perm_anon']);
    if (($access < 3) || (hasBlockTopicAccess ($A['tid']) < 3)) {
        COM_accessLog ("User {$_USER['username']} tried to illegally delete block $bid.");
        $pageHandle->redirect($_CONF['site_admin_url'] . '/block.php');
    }

    DB_delete ($_TABLES['blocks'], 'bid', $bid);

    $pageHandle->redirect($_CONF['site_admin_url'] . '/block.php?msg=12');
}

// MAIN
$mode = $inputHandler->getVar('strict','mode','request','');
$bid  = $inputHandler->getVar('strict','bid','request','');

if (isset($_POST['blockenabler']) && SEC_checkToken()) {
    $enabledblocks = array();
    if (isset($_POST['enabledblocks'])) {
        $enabledblocks = $_POST['enabledblocks'];
    }
    changeBlockStatus($_POST['blockenabler'], $enabledblocks);
}

if (($mode == $LANG_ADMIN['delete']) && !empty ($LANG_ADMIN['delete'])) {
    if (!isset ($bid) || empty ($bid) || ($bid == 0)) {
        COM_errorLog ('Attempted to delete block, bid empty or null, value =' . $bid);
        $pageHandle->redirect($_CONF['site_admin_url'] . '/block.php');
    } elseif (SEC_checkToken()) {
        $display .= deleteBlock ($bid);
    } else {
        COM_accessLog("User {$_USER['username']} tried to illegally delete block $bid and failed CSRF checks.");
        $pageHandle->redirect($_CONF['site_admin_url'] . '/index.php');
    }
} elseif (($mode == $LANG_ADMIN['save']) && !empty($LANG_ADMIN['save']) ) {
    $help = $inputHandler->getVar('strict','help','post','');
    if (!empty($help)) {
        $help = $inputHandler->filterVar('url',$help,'',array('http','https'));
    }

    $content = '';
    $postmode = $inputHandler->getVar('strict','postmode','post','');

    if (($_CONF['advanced_editor'] == 1)) {
        if ( $postmode == 'adveditor' ) {
            $content = $inputHandler->getVar('raw','block_html','post','');
            $html = true;
        } else if ( $postmode == 'html' ) {
            $content = $inputHandler->getVar('raw','block_text','post');
            $html = false;
        }
    } else {
        $content = $inputHandler->getVar('raw','content','post');
    }
    $rdfurl         = $inputHandler->getVar('url','rdfurl','post','');
    $rdfupdated     = $inputHandler->getVar('raw','rdfupdated','post','0000-00-00 00:00:00');
    $rdflimit       = $inputHandler->getVar('int','rdflimit','post',0);
    $phpblockfn     = $inputHandler->getVar('text','phpblockfn','post','');
    $is_enabled     = $inputHandler->getVar('int','is_enabled','post',0);
    $allow_autotags = $inputHandler->getVar('int','allow_autotags','post',0);
    $name           = $inputHandler->getVar('strict','name','post','');
    $title          = $inputHandler->getVar('text','title','post','');
    $type           = $inputHandler->getVar('strict','type','post','');
    $blockorder     = $inputHandler->getVar('int','blockorder','post',0);
    $tid            = $inputHandler->getVar('strict','tid','post','');
    $onleft         = $inputHandler->getVar('int','onleft','post',0);
    $owner_id       = $inputHandler->getVar('int','owner_id','post',1);
    $group_id       = $inputHandler->getVar('int','group_id','post',1);
    $perm_owner     = $inputHandler->getVar('int','perm_owner','post',1);
    $perm_group     = $inputHandler->getVar('int','perm_group','post',1);
    $perm_mem       = $inputHandler->getVar('int','perm_members','post',1);
    $perm_anon      = $inputHandler->getVar('int','perm_anon','post',1);

    $display .= saveblock ($bid, $name, $title,
                    $help, $type, $blockorder, $content,
                    $tid, $rdfurl, $rdfupdated,
                    $rdflimit, $phpblockfn, $onleft,
                    $owner_id, $group_id,
                    $perm_owner,$perm_group,
                    $perm_mem, $perm_anon,
                    $is_enabled, $allow_autotags);
} else if ($mode == 'edit') {
    @setcookie ($_CONF['cookie_name'].'fckeditor', SEC_createTokenGeneral('advancededitor'),
                time() + 1200, $_CONF['cookie_path'],
               $_CONF['cookiedomain'], $_CONF['cookiesecure']);
    $pageHandle->setPageTitle($LANG21[3]);
    $pageHandle->addContent(editblock ($bid) );
    $pageHandle->displayPage();

} else if ($mode == 'move') {
    $pageHandle->setPageTitle($LANG21[19]);
    if(SEC_checkToken()) {
        $pageHandle->addContent(moveBlock());
    }
    $pageHandle->addContent(listblocks());
    $pageHandle->displayPage();
} else {  // 'cancel' or no mode at all
    $pageHandle->setPageTitle($LANG21[19]);
    $msg = $inputHandler->getVar('int','msg',array('post','get'),0);
    if ($msg > 0) {
        $pageHandle->addMessage($msg);
    }
    $pageHandle->addContent(listblocks());
    $pageHandle->displayPage();
}
?>