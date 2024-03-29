<?php
/**
* glFusion CMS - Links Plugin
*
* Category Administration Page
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2012-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs         tony AT tonybibbs DOT com
*  Mark Limburg       mlimburg AT users.sourceforge DOT net
*  Jason Whittenburg  jwhitten AT securitygeeks DOT com
*  Dirk Haun          dirk AT haun-online DOT de
*  Euan McKay         info AT heatherengineering DOT com
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

use \glFusion\Log\Log;

// Uncomment the line below if you need to debug the HTTP variables being passed
// to the script.  This will sometimes cause errors but it will allow you to see
// the data being passed in a POST operation
// echo COM_debug($_POST);

$display = '';

if (!SEC_hasRights('links.edit')) {
    $display .= COM_siteHeader ('menu');
    $display .= COM_startBlock ($MESSAGE[30], '',
                                COM_getBlockTemplate ('_msg_block', 'header'));
    $display .= $MESSAGE[34];
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter ();
    COM_accessLog("User {$_USER['username']} tried to illegally access the link administration screen.");
    echo $display;
    exit;
}


// +--------------------------------------------------------------------------+
// | Category administration functions                                        |
// | Located here so that in the future, users can also have their own link   |
// | collections with categories over which they have edit access.            |
// +--------------------------------------------------------------------------+


// Returns a category tree of categories in the database to which
// the user has edit access

function LINK_CAT_list($root)
{
    global $_CONF, $_TABLES, $_USER, $_IMAGE_TYPE, $LANG_ADMIN, $LANG_ACCESS,
           $LANG_LINKS_ADMIN, $LANG_LINKS, $_LI_CONF;

    require_once $_CONF['path_system'] . 'lib-admin.php';

    $retval = '';

    $header_arr = array(      # display 'text' and use table field 'field'
                    array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center', 'width' => '25px'),
                    array('text' => $LANG_LINKS_ADMIN[41], 'field' => 'addchild', 'sort' => false, 'align' => 'center', 'width' => '25px'),
                    array('text' => $LANG_LINKS_ADMIN[30], 'field' => 'category', 'sort' => true),
                    array('text' => $LANG_LINKS_ADMIN[33], 'field' => 'tid', 'sort' => true, 'align' => 'center'),
                    array('text' => $LANG_LINKS_ADMIN[61], 'field' => 'owner', 'sort' => true, 'align' => 'center'),
                    array('text' => $LANG_ACCESS['access'], 'field' => 'access', 'sort' => false, 'align' => 'center'),
                    array('text' => $LANG_LINKS_ADMIN[62], 'field' => 'unixdate', 'sort' => true, 'align' => 'center'),
                    array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center', 'width' => '25px')
                );

    $defsort_arr = array('field' => 'category', 'direction' => 'asc');

    $menu_arr = array(
        array('url' => $_CONF['site_admin_url'] . '/plugins/links/index.php',
                'text' => $LANG_LINKS_ADMIN[53]),
        array('url' => $_CONF['site_admin_url'] . '/plugins/links/index.php?edit=x',
                'text' => $LANG_LINKS_ADMIN[51]),
        array('url' => $_CONF['site_admin_url'] . '/plugins/links/category.php',
                'text' => $LANG_LINKS_ADMIN[50],'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/plugins/links/category.php?edit=x',
              'text' => $LANG_LINKS_ADMIN[52]),
        array('url' => $_CONF['site_admin_url'] . '/plugins/links/index.php?validate=enabled',
            'text' => $LANG_LINKS_ADMIN[26]),
        array('url' => $_CONF['site_admin_url'],
                'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG_LINKS_ADMIN[54], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_LINKS_ADMIN[12], plugin_geticon_links());

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . '/plugins/links/category.php'
    );

    $dummy = array();
    $data_arr = LINK_CAT_list_recursive ($dummy, $_LI_CONF['root'], 0);

    $retval .= ADMIN_simpleList('plugin_getListField_categories', $header_arr,
                                $text_arr, $data_arr);
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

function LINK_CAT_list_recursive($data_arr, $cid, $indent)
{
    global $_CONF, $_TABLES, $_LI_CONF, $LANG_LINKS_ADMIN;

    $indent = $indent + 1;
    $cid = DB_escapeString($cid);

    // get all children of present category
    $sql = "SELECT cid,category,tid,UNIX_TIMESTAMP(modified) AS unixdate,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon "
        . "FROM {$_TABLES['linkcategories']} "
        . "WHERE (pid='{$cid}')" . COM_getPermSQL('AND', 0, 3)
        . "ORDER BY pid,category";
    $result = DB_query($sql);
    $nrows = DB_numRows($result);

    if ($nrows > 0) {
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray($result);
            $topic = DB_getItem($_TABLES['topics'], 'topic', "tid='{$A['tid']}'");
            $A['topic_text'] = $topic;
            $A['indent'] = $indent;
            $data_arr[] = $A;
            if (DB_count($_TABLES['linkcategories'], 'pid', DB_escapeString($A['cid'])) > 0) {
                $data_arr = LINK_CAT_list_recursive($data_arr, $A['cid'], $indent);
            }
        }
    }

    return $data_arr;
}


// Returns form to create a new category or edit an existing one

function LINK_CAT_edit($cid, $pid)
{
    global $_CONF, $_TABLES, $_USER, $MESSAGE,
           $LANG_LINKS_ADMIN, $LANG_ADMIN, $LANG_ACCESS, $_LI_CONF;

    USES_lib_admin();

    $retval = '';
    $editFlag = false;

    $cid = DB_escapeString($cid);

    if (!empty($pid)) {
        // have parent id, so making a new subcategory
        // get parent access rights
        $result = DB_query("SELECT group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['linkcategories']} WHERE cid='" . DB_escapeString($pid) . "'");
        $A = DB_fetchArray($result);
        $A['owner_id'] = $_USER['uid'];
        $A['pid'] = $pid;
    } elseif (!empty($cid)) {
        // have category id, so editing a category
        $sql = "SELECT * FROM {$_TABLES['linkcategories']} WHERE cid='{$cid}'"
             . COM_getPermSQL('AND');
        $result = DB_query($sql);
        $A = DB_fetchArray($result);
        $editFlag = true;
    } else {
        // nothing, so making a new top-level category
        // get default access rights
        $A['group_id'] = DB_getItem($_TABLES['groups'], 'grp_id', "grp_name='Links Admin'");
        SEC_setDefaultPermissions($A, $_LI_CONF['default_permissions']);
        $A['owner_id'] = $_USER['uid'];
        $A['pid']      = $_LI_CONF['root'];
    }

    $access = SEC_hasAccess($A['owner_id'], $A['group_id'], $A['perm_owner'],
                            $A['perm_group'], $A['perm_members'], $A['perm_anon']);

    if ($access < 3) {
        return COM_showMessage(6, 'links');
    }

    if ( $editFlag ) {
        $lang_edit_or_create = $LANG_ADMIN['edit'];
    } else {
        $lang_edit_or_create = $LANG_LINKS_ADMIN[52];
    }

    $menu_arr = array(
        array('url' => $_CONF['site_admin_url'] . '/plugins/links/index.php',
                'text' => $LANG_LINKS_ADMIN[53]),
        array('url' => $_CONF['site_admin_url'] . '/plugins/links/index.php?edit=x',
                'text' => $LANG_LINKS_ADMIN[51]),
        array('url' => $_CONF['site_admin_url'] . '/plugins/links/category.php',
                'text' => $LANG_LINKS_ADMIN[50]),
        array('url' => $_CONF['site_admin_url'] . '/plugins/links/category.php?edit=x',
              'text' => $lang_edit_or_create,'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/plugins/links/index.php?validate=enabled',
            'text' => $LANG_LINKS_ADMIN[26]),
        array('url' => $_CONF['site_admin_url'],
                'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG_LINKS_ADMIN[56], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_LINKS_ADMIN[67], plugin_geticon_links());

    $T = new Template($_CONF['path'] . 'plugins/links/templates/admin');
    $T->set_file(array('page' => 'categoryeditor.thtml'));

    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);
    $T->set_var('layout_url', $_CONF['layout_url']);
    $T->set_var('lang_pagetitle', $LANG_LINKS_ADMIN[28]);
    $T->set_var('lang_link_list', $LANG_LINKS_ADMIN[53]);
    $T->set_var('lang_new_link', $LANG_LINKS_ADMIN[51]);
    $T->set_var('lang_validate_links', $LANG_LINKS_ADMIN[26]);
    $T->set_var('lang_list_categories', $LANG_LINKS_ADMIN[50]);
    $T->set_var('lang_new_category', $LANG_LINKS_ADMIN[52]);
    $T->set_var('lang_admin_home', $LANG_ADMIN['admin_home']);
    $T->set_var('instructions', $LANG_LINKS_ADMIN[29]);
    $T->set_var('lang_category', $LANG_LINKS_ADMIN[30]);
    $T->set_var('lang_cid', $LANG_LINKS_ADMIN[32]);
    $T->set_var('lang_description', $LANG_LINKS_ADMIN[31]);
    $T->set_var('lang_topic', $LANG_LINKS_ADMIN[33]);
    $T->set_var('lang_parent', $LANG_LINKS_ADMIN[34]);
    $T->set_var('lang_save', $LANG_ADMIN['save']);

    if (!empty($cid)) {
        $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                   . '" name="delete"%s>';
        $jsconfirm = ' onclick="return confirm(\'' . $MESSAGE[76] . '\');"';
        $T->set_var('delete_option', sprintf($delbutton, $jsconfirm));
        $T->set_var('delete_option_no_confirmation', sprintf($delbutton, ''));
        $T->set_var('delete_confirm_msg',$MESSAGE[76]);
    } else {
        $T->set_var('delete_option', '');
    }
    $T->set_var('lang_cancel', $LANG_ADMIN['cancel']);

    if (!empty($cid)) {
        $T->set_var('cid_value', $A['cid']);
        $T->set_var('old_cid_value', $A['cid']);
        $T->set_var('category_options', links_select_box(3, $A['pid']));
        $T->set_var('category_value', $A['category']);
        $T->set_var('description_value', $A['description']);
    } else {
        $A['cid'] = COM_makeSid();
        $T->set_var('cid_value', $A['cid']);
        $T->set_var('old_cid_value', '');
        $T->set_var('category_options', links_select_box(3, $A['pid']));
        $T->set_var('category_value', '');
        $T->set_var('description_value', '');
    }

    if (!isset($A['tid'])) {
        $A['tid'] = 'all';
    }
    $topics = COM_topicList('tid,topic,sortnum', $A['tid'], 2, true);
    $T->set_var('topic_list', $topics);
    $alltopics = '<option value="all"';
    if ($A['tid'] == 'all') {
        $alltopics .= ' selected="selected"';
    }
    $alltopics .= '>' . $LANG_LINKS_ADMIN[35] . '</option>' . LB;
    $T->set_var('topic_selection', '<select name="tid">' . $alltopics
                                   . $topics . '</select>');

    // user access info
    $T->set_var('lang_accessrights', $LANG_ACCESS['accessrights']);
    $T->set_var('lang_owner', $LANG_ACCESS['owner']);
    $T->set_var('owner_name', COM_getDisplayName($A['owner_id']));
    $T->set_var('cat_ownerid', $A['owner_id']);
    $T->set_var('lang_group', $LANG_ACCESS['group']);
    $T->set_var('group_dropdown', SEC_getGroupDropdown($A['group_id'], $access));
    $T->set_var('lang_permissions', $LANG_ACCESS['permissions']);
    $T->set_var('lang_permissionskey', $LANG_ACCESS['permissionskey']);
    $T->set_var('permissions_editor', SEC_getPermissionsHTML($A['perm_owner'],
            $A['perm_group'], $A['perm_members'], $A['perm_anon']));
    $T->set_var('lang_lockmsg', $LANG_ACCESS['permmsg']);
    $T->set_var('gltoken_name', CSRF_TOKEN);
    $T->set_var('gltoken', SEC_createToken());

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}


/*
* Save changes to category information
* input     array       values from form (unvalidated, unsafe)
* output    string      message giving outcome status of requested operation
*/

function LINK_CAT_save($cid, $old_cid, $pid, $category, $description, $tid, $owner_id, $group_id, $perm_owner, $perm_group, $perm_members, $perm_anon)
{
    global $_CONF, $_TABLES, $_USER, $LANG_LINKS, $LANG_LINKS_ADMIN, $_LI_CONF,
           $PLG_links_MESSAGE17;

    // Convert array values to numeric permission values
    if (is_array($perm_owner) OR is_array($perm_group) OR is_array($perm_members) OR is_array($perm_anon)) {
        list($perm_owner,$perm_group,$perm_members,$perm_anon) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);
    }

    // clean 'em up
    $description = DB_escapeString (COM_checkHTML(COM_checkWords($description)));
    $category    = DB_escapeString (COM_checkHTML(COM_checkWords($category)));
    $pid     = DB_escapeString(strip_tags($pid));
    $cid     = DB_escapeString(strip_tags($cid));
    $old_cid = DB_escapeString(strip_tags($old_cid));

    if (empty($category) || empty($description)) {
        return 7;
    }

    // Check cid to make sure not illegal
    if (($cid == DB_escapeString($_LI_CONF['root'])) || ($cid == 'user')) {
        return 11;
    }

    if (!empty($cid) && ($cid != $old_cid)) {
        // this is either a new category or an attempt to change the cid
        // - check that cid doesn't exist yet
        $ctrl = DB_getItem($_TABLES['linkcategories'], 'cid', "cid = '$cid'");
        if (!empty($ctrl)) {
            if (isset($PLG_links_MESSAGE17)) {
                return 17;
            } else {
                return 11;
            }
        }
    }

    // Check that they didn't delete the cid. If so, get the hidden one
    if (empty($cid) && !empty($old_cid)) {
        $cid = $old_cid;
    }

    // Make sure they aren't making a parent category child of one of it's own
    // children. This would create orphans
    if ($cid == DB_getItem($_TABLES['linkcategories'], 'pid',"cid='{$pid}'")) {
        return 12;
    }

    $access = 0;
    if (DB_count ($_TABLES['linkcategories'], 'cid', $old_cid) > 0) {
        // update existing item, but new cid so get access from database with old cid
        $result = DB_query("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['linkcategories']} WHERE cid='{$old_cid}'");
        $A = DB_fetchArray ($result);
        $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],
            $A['perm_group'],$A['perm_members'],$A['perm_anon']);
        // set flag
        $update = "existing";
    } else if (DB_count ($_TABLES['linkcategories'], 'cid', $cid) > 0) {
        // update existing item, same cid, so get access from database with existing cid
        $result = DB_query("SELECT owner_id,group_id,perm_owner,perm_group, perm_members,perm_anon FROM {$_TABLES['linkcategories']} WHERE cid='{$cid}'");
        $A = DB_fetchArray ($result);
        $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],
            $A['perm_group'],$A['perm_members'],$A['perm_anon']);
        // set flag
        $update = "same";
    } else {
        // new item, so use passed values
        $access = SEC_hasAccess($owner_id, $group_id, $perm_owner, $perm_group,
                                $perm_members, $perm_anon);
        // set flag
        $update = 'new';
    }

    if ($access < 3) {
        // no access rights: user should not be here
        COM_accessLog(sprintf($LANG_LINKS_ADMIN[60], $_USER['username'], $cid));
        return 6;
    } else {
        // save item
        if ($update == 'existing') {
            // update an existing item but new cid
            $sql = "UPDATE {$_TABLES['linkcategories']}
                    SET cid='{$cid}',
                        pid='{$pid}',
                        tid='{$tid}',category='{$category}',
                        description='{$description}',
                        modified='".$_CONF['_now']->toMySQL(true)."',
                        owner_id='{$owner_id}',group_id='{$group_id}',
                        perm_owner='{$perm_owner}',perm_group='{$perm_group}',
                        perm_members='{$perm_members}',perm_anon='{$perm_anon}'
                    WHERE cid = '{$old_cid}'";
            $result = DB_query($sql);
            $sql = "UPDATE {$_TABLES['linkcategories']}
                    SET pid='{$cid}'
                    WHERE pid='{$old_cid}'";
            $result = DB_query($sql);

            // Also need to update links for this category
            $sql = "UPDATE {$_TABLES['links']} SET cid='{$cid}' WHERE cid='{$old_cid}'";
            $result = DB_query($sql);
        } else if ($update == 'same') {
            // update an existing item
            $sql = "UPDATE {$_TABLES['linkcategories']}
                    SET pid='{$pid}',
                        tid='{$tid}',category='{$category}',
                        description='{$description}',
                        modified='".$_CONF['_now']->toMySQL(true)."',
                        owner_id='{$owner_id}',group_id='{$group_id}',
                        perm_owner='{$perm_owner}',perm_group='{$perm_group}',
                        perm_members='{$perm_members}',perm_anon='{$perm_anon}'
                    WHERE cid = '{$cid}'";
            $result = DB_query($sql);
        } else {
            // insert a new item
            if (empty($cid)) {
                $cid = COM_makeSid();
            }
            $sql = "INSERT INTO {$_TABLES['linkcategories']}
                    (cid, pid, category, description, tid,
                    created,modified,
                    owner_id, group_id, perm_owner, perm_group,
                    perm_members, perm_anon)
                    VALUES
                    ('{$cid}','{$pid}','{$category}',
                    '{$description}','{$tid}',
                    '".$_CONF['_now']->toMySQL(true)."','".$_CONF['_now']->toMySQL(true)."',
                    '{$owner_id}','{$group_id}','{$perm_owner}',
                    '{$perm_group}','{$perm_members}','{$perm_anon}')";
            $result = DB_query($sql);
        }
    }
    if (($update == 'existing') && ($cid != $old_cid)) {
        PLG_itemSaved($cid, 'links.category', $old_cid);
    } else {
        PLG_itemSaved($cid, 'links.category');
    }
    return 10; // success message
}


/*
* Delete a category
* input     $cid    string      category id number
* output            string      message about success of requested operation
*/

function LINK_CAT_delete($cid)
{
    global $_TABLES, $LANG_LINKS_ADMIN;

    $cid = DB_escapeString($cid);
    if (DB_count ($_TABLES['linkcategories'], 'cid', $cid) > 0) {
        // item exists so check access rights
        $result = DB_query("SELECT owner_id,group_id,perm_owner,perm_group,
            perm_members,perm_anon FROM {$_TABLES['linkcategories']}
            WHERE cid='{$cid}'");
        $A = DB_fetchArray ($result);
        $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],
            $A['perm_group'],$A['perm_members'],$A['perm_anon']);
        if ($access > 2) {
            // has edit rights
            // Check for subfolders and sublinks
            $sf = DB_count($_TABLES['linkcategories'], 'pid', $cid);
            $sl = DB_count($_TABLES['links'], 'cid', $cid);
            if (($sf == 0) && ($sl == 0)) {
                // No subfolder/links so OK to delete
                DB_delete($_TABLES['linkcategories'], 'cid', $cid);
                PLG_itemDeleted($cid, 'links.category');
                return 13;
            } else {
                // Subfolders and/or sublinks exist so return a message
                return 14;
            }
        } else {
            // no access
            return 15;
            COM_accessLog(sprintf($LANG_LINKS_ADMIN[46], $_USER['username']));
        }
    } else {
        // no such category
        return 16;
    }
}

// MAIN ========================================================================

$action = '';
$expected = array('edit','save','delete','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

$cid = '';
if (isset($_POST['cid'])) {
    $cid = COM_applyFilter($_POST['cid']);
} elseif (isset($_GET['cid'])) {
    $cid = COM_applyFilter($_GET['cid']);
}

$pid = '';
if (isset($_POST['pid'])) {
    $pid = COM_applyFilter($_POST['pid']);
} elseif (isset($_GET['pid'])) {
    $pid = COM_applyFilter($_GET['pid']);
}

$msg = (isset($_GET['msg'])) ? COM_applyFilter($_GET['msg']) : '';

$root = $_LI_CONF['root'];

switch ($action) {

    case 'edit':
        $display .= COM_siteHeader('menu', $LANG_LINKS_ADMIN[56]);
        $display .= LINK_CAT_edit($cid, $pid);
        $display .= COM_siteFooter();
        break;

    case 'save':
        $msg = LINK_CAT_save($cid, $_POST['old_cid'],
                    $_POST['pid'], $_POST['category'],
                    $_POST['description'], COM_applyFilter($_POST['tid']),
                    COM_applyFilter($_POST['owner_id'], true),
                    COM_applyFilter($_POST['group_id'], true),
                    $_POST['perm_owner'], $_POST['perm_group'],
                    $_POST['perm_members'], $_POST['perm_anon']);
        $display .= COM_siteHeader ('menu', $LANG_LINKS_ADMIN[11]);
        $display .= COM_showMessage ($msg, 'links');
        $display .= LINK_CAT_list($root);
        $display .= COM_siteFooter();
        break;

    case 'delete':
        if (!isset ($cid) || empty ($cid)) {
            Log::write('system',Log::ERROR,'User ' . $_USER['username'] . ' attempted to delete link category, cid is null');
            $display .= COM_refresh ($_CONF['site_admin_url'] . '/plugins/links/category.php');
        } elseif (SEC_checkToken()) {
            $msg = LINK_CAT_delete($cid);
            $display .= COM_siteHeader('menu', $LANG_LINKS_ADMIN[11]);
            $display .= COM_showMessage($msg, 'links');
            $display .= LINK_CAT_list($root);
            $display .= COM_siteFooter();
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally delete link category $cid and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        $display .= COM_siteHeader ('menu', $LANG_LINKS_ADMIN[11]);
        if(isset($msg)) {
            $display .= (is_numeric($msg)) ? COM_showMessage($msg, 'links') : COM_showMessageText( $msg );
        }
        $display .= LINK_CAT_list($root);
        $display .= COM_siteFooter();
        break;
}

echo $display;

?>
