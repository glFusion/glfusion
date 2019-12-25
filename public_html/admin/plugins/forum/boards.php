<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | boards.php                                                               |
// |                                                                          |
// | Forum Plugin admin - Main program to setup Forums                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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

if (!SEC_hasRights('forum.edit')) {
  $display = COM_siteHeader();
  $display .= COM_startBlock($LANG_GF00['access_denied']);
  $display .= $LANG_GF00['admin_only'];
  $display .= COM_endBlock();
  $display .= COM_siteFooter(true);
  echo $display;
  exit();
}

USES_forum_functions();
USES_forum_format();
USES_forum_admin();
USES_lib_admin();

/*
 * Display full category / forum list
 */
function board_admin_list($statusText='')
{
    global $_CONF, $_TABLES, $_FF_CONF, $LANG_ADMIN,$LANG_GF93, $LANG_GF01,
           $LANG_GF00,$LANG_GF91, $LANG_GF06;

    $retval = '';
    $selected = '';

    $menu_arr = array();

    $boards = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $boards->set_file ('boards','boards.thtml');

    $boards->set_var('block_start',COM_startBlock($LANG_GF06[10],'',COM_getBlockTemplate('_admin_block', 'header')));

    $menu_arr = FF_adminNav($LANG_GF06['3']);

    $boards->set_var('admin_menu',ADMIN_createMenu($menu_arr,$LANG_GF00['instructions'],
          $_CONF['site_url'] . '/forum/images/forum.png')
    );

    $boards->set_var (array(
            's_form_action' => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
            'lang_addcat'   => $LANG_GF93['addcat'],
            'lang_cat'      => $LANG_GF01['category'],
            'lang_edit'     => $LANG_GF01['EDIT'],
            'lang_delete'   => $LANG_GF01['DELETE'],
            'lang_topic'    => $LANG_GF01['TOPIC'],
            'LANG_posts'    => $LANG_GF93['posts'],
            'LANG_order'    => $LANG_GF93['ordertitle'],
            'lang_catorder' => $LANG_GF93['catorder'],
            'LANG_action'   => $LANG_GF93['action'],
            'LANG_forumdesc'=> $LANG_GF93['forumdescription'],
            'lang_addforum' => $LANG_GF93['addforum'],
            'lang_addcat'   => $LANG_GF93['addcat'],
            'lang_description' => $LANG_GF01['DESCRIPTION'],
            'lang_resync'   => $LANG_GF01['RESYNC'],
            'lang_edit'     => $LANG_GF01['EDIT'],
            'lang_resync_cat' => $LANG_GF01['RESYNCCAT'],
            'lang_delete'   => $LANG_GF01['DELETE'],
            'lang_submit'   => $LANG_GF01['SUBMIT'],

            'phpself'       => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
            'addcat'        => $LANG_GF93['addcat'],
            'cat'           => $LANG_GF01['category'],
            'edit'          => $LANG_GF01['EDIT'],
            'delete'        => $LANG_GF01['DELETE'],
            'topic'         => $LANG_GF01['TOPIC'],
            'catorder'      => $LANG_GF93['catorder'],
            'addforum'      => $LANG_GF93['addforum'],
            'addcat'        => $LANG_GF93['addcat'],
            'description'   => $LANG_GF01['DESCRIPTION'],
            'resync'        => $LANG_GF01['RESYNC'],
            'edit'          => $LANG_GF01['EDIT'],
            'resync_cat'    => $LANG_GF01['RESYNCCAT'],
            'delete'        => $LANG_GF01['DELETE'],
            'submit'        => $LANG_GF01['SUBMIT'],
    ));

    if ( !empty($statusText) ) {
        $boards->set_var('status_text',$statusText);
    }

    // Display each Forum Category
    $cat_sql = DB_query("SELECT * FROM {$_TABLES['ff_categories']} ORDER BY cat_order");
    while ($C = DB_fetchArray($cat_sql)) {
        $boards->set_var ('catid', $C['id']);
        $boards->set_var ('catname', $C['cat_name']);
        $boards->set_var ('order', $C['cat_order']);

        // Display each forum within this category
        $forum_sql_result = DB_query("SELECT * FROM {$_TABLES['ff_forums']} WHERE forum_cat=".(int) $C['id']." ORDER BY forum_order ASC");
        $forum_number_of_rows = DB_numRows($forum_sql_result);

        $boards->set_block('boards', 'catrows', 'crow');
        $boards->clear_var('frow');
        $boards->set_block('boards', 'forumrows', 'frow');

        for ($j = 1; $j <= $forum_number_of_rows; $j++) {
            $F = DB_fetchArray($forum_sql_result);
            $boards->set_var (array(
                    'forumname'     => $F['forum_name'],
                    'forumid'       => $F['forum_id'],
                    'messagecount'  => COM_numberFormat($F['post_count']),
                    'forumorder'    => $F['forum_order'],
            ));
            // Check if this is a private forum
            if ($F['grp_id'] != '2') {
                $grp_name = DB_getItem($_TABLES['groups'],'grp_name', "grp_id=".(int) $F['grp_id']);
                $boards->set_var ('forumdscp', "[{$LANG_GF93['private']}&nbsp;-&nbsp;{$grp_name}]<br/>{$F['forum_dscp']}");
            } else {
                $boards->set_var ('forumdscp', $F['forum_dscp']);
            }
            $boards->parse('frow', 'forumrows',true);
        }
        $boards->parse('crow', 'catrows',true);
    }

    $boards->set_var('block_end',COM_endBlock(COM_getBlockTemplate ('_admin_block', 'footer')));

    $boards->parse ('output', 'boards');
    $retval .= $boards->finish ($boards->get_var('output'));

    return $retval;
}

/* ------------- Category related administration ----------------- */

/*
 * Edit a forum category
 */
function board_edit_category($id, $statusText='')
{
    global $_CONF, $_FF_CONF, $_TABLES, $LANG_GF00, $LANG_GF01, $LANG_GF06,
           $LANG_GF93, $LANG_ADMIN;

    $retval = '';

    $menu_arr = array();

    $filter = sanitizer::getInstance();

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $T->set_file (array ('boards_edtcategory'=>'boards_edtcategory.thtml'));

    $T->set_var('block_start',COM_startBlock($LANG_GF93['gfboard'],'',COM_getBlockTemplate('_admin_block', 'header')));

    $menu_arr = FF_adminNav();

    $T->set_var('admin_menu',ADMIN_createMenu($menu_arr,$LANG_GF00['instructions'],
          $_CONF['site_url'] . '/forum/images/forum.png')
    );

    if ( empty($statusText)) {
        $esql = DB_query("SELECT * FROM {$_TABLES['ff_categories']} WHERE id=".(int) $id);
        $E = DB_fetchArray($esql);
        $catname = $E['cat_name'];
    } else {
        $catname = DB_getItem($_TABLES['ff_categories'],'cat_name','id=' . (int) $id);
        $E['cat_name']  = isset($_POST['name']) ? $_POST['name'] : '';
        $E['cat_dscp']  = isset($_POST['dscp']) ? $_POST['dscp'] : '';
        $E['cat_order'] = isset($_POST['catorder']) ? COM_applyFilter($_POST['catorder'],true) : 0;
    }
    $E['cat_name'] = $filter->editableText($E['cat_name']);
    $E['cat_dscp'] = $filter->editableText($E['cat_dscp']);

    $title = sprintf($LANG_GF93['editcatnote'].'<br>',$catname);

    $T->set_var(array(
        's_form_action' => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        'title'         => sprintf($LANG_GF93['editcatnote'], $E['cat_name']),
        'catname'       => $E['cat_name'],
        'catorder'      => $E['cat_order'],
        'catdscp'       => $E['cat_dscp'],
        'id'            => $id,
        'mode'          => 'savecat',
        'LANG_NAME'     => $LANG_GF01['NAME'],
        'LANG_DESCRIPTION' => $LANG_GF01['DESCRIPTION'],
        'LANG_ORDER'    => $LANG_GF01['ORDER'],
        'LANG_SAVE'     => $LANG_GF01['SAVE'],
        'LANG_CANCEL'   => $LANG_GF01['CANCEL'],
    ));

    if ( !empty($statusText) ) {
        $T->set_var('status_text',$statusText);
    }

    $T->set_var('block_end',COM_endBlock(COM_getBlockTemplate ('_admin_block', 'footer')));

    $T->parse('output','boards_edtcategory');
    $retval = $T->finish($T->get_var('output'));
    return $retval;
}

function board_add_category($statusText='')
{
    global $_CONF, $_FF_CONF, $_TABLES, $LANG_GF00, $LANG_GF01, $LANG_GF06,
           $LANG_GF93, $LANG_ADMIN;

    $retval = '';

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $T->set_file ('boards_addcategory','boards_edtcategory.thtml');

    $T->set_var('block_start',COM_startBlock($LANG_GF06[10],'',COM_getBlockTemplate('_admin_block', 'header')));

    $menu_arr = FF_adminNav();

    $T->set_var('admin_menu',ADMIN_createMenu($menu_arr,$LANG_GF00['instructions'],
          $_CONF['site_url'] . '/forum/images/forum.png')
    );

    if (!empty($statusText) ) {
        $filter = sanitizer::getInstance();

        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $dscp = isset($_POST['dscp']) ? $_POST['dscp'] : '';
        $catorder = isset($_POST['catorder']) ? COM_applyFilter($_POST['catorder'],true) : 0;

        $name = $filter->editableText($name);
        $dscp = $filter->editableText($dscp);
    } else {
        $name = '';
        $dscp = '';
        $catorder = 0;
    }

    $T->set_var (array(
        'phpself'           => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        's_form_action'     => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        'title'             => $LANG_GF93['addcat'],
        'mode'              => 'saveaddcat',
        'LANG_ADDNOTE'      => $LANG_GF93['addnote'],
        'LANG_NAME'         => $LANG_GF01['NAME'],
        'LANG_DESCRIPTION'  => $LANG_GF01['DESCRIPTION'],
        'LANG_ORDER'        => $LANG_GF01['ORDER'],
        'LANG_CANCEL'       => $LANG_GF01['CANCEL'],
        'LANG_SAVE'         => $LANG_GF01['SUBMIT'],
        'catname'      => $name,
        'catdscp'      => $dscp,
        'catorder'     => $catorder,
        'status_text'  => $statusText,
    ));

    $T->set_var('block_end',COM_endBlock(COM_getBlockTemplate ('_admin_block', 'footer')));

    $T->parse ('output', 'boards_addcategory');
    $retval .= $T->finish ($T->get_var('output'));

    return $retval;
}

/*
 * Savea new category
 */
function board_add_category_save()
{
    global $_CONF, $_FF_CONF, $_TABLES, $LANG_GF93;

    $retval     = false;
    $statusText = array();
    $numErrors  = 0;

    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $dscp = isset($_POST['dscp']) ? $_POST['dscp'] : '';
    $catorder = isset($_POST['catorder']) ? COM_applyFilter($_POST['catorder'],true) : 0;

    // data validation
    if ( empty($name) ) {
        $statusText[] = $LANG_GF93['name_blank'];
        $numErrors++;
    }
    if ( MBYTE_strlen($name) > 70 ) {
        $name = MBYTE_substr($name,0,70);
    }
    if ( empty($dscp) ) {
        $statusText[] = $LANG_GF93['desc_blank'];
        $numErrors++;
    }
    if ( $catorder < 0 ) {
        $catorder = 0;
    }
    if ( $numErrors == 0 ) {
        $name = _ff_preparefordb($name,'text');
        $dscp = _ff_preparefordb($dscp,'text');
        DB_query("INSERT INTO {$_TABLES['ff_categories']} (cat_order,cat_name,cat_dscp) VALUES (".(int) $catorder.",'$name','$dscp')");
        $retval = true;
        $statusText[] = $LANG_GF93['catadded'];
        \glFusion\Admin\AdminAction::write('forum','create_category','Created Category '.$name);
    }
    return array($retval, $statusText);
}

/*
 * Edit a category form
 */
function board_edit_category_save($id)
{
    global $_CONF, $_FF_CONF, $_TABLES, $LANG_GF93;

    $retval     = false;
    $statusText = array();
    $numErrors  = 0;

    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $dscp = isset($_POST['dscp']) ? $_POST['dscp'] : '';
    $catorder = isset($_POST['catorder']) ? COM_applyFilter($_POST['catorder'],true) : 0;

    // data validation
    if ( empty($name) ) {
        $statusText[] = $LANG_GF93['name_blank'];
        $numErrors++;
    }
    if ( MBYTE_strlen($name) > 70 ) {
        $name = MBYTE_substr($name,0,70);
    }
    if ( empty($dscp) ) {
        $statusText[] = $LANG_GF93['desc_blank'];
        $numErrors++;
    }
    if ( $catorder < 0 ) {
        $catorder = 0;
    }
    if ( $numErrors == 0 ) {
        $name = _ff_preparefordb($_POST['name'],'text');
        $dscp = _ff_preparefordb($_POST['dscp'],'text');

        DB_query("UPDATE {$_TABLES['ff_categories']} SET cat_order=".(int) $catorder.",cat_name='$name',cat_dscp='$dscp' WHERE id=".(int) $id);
        $retval = true;
        $statusText[] = $LANG_GF93['catedited'];

        \glFusion\Admin\AdminAction::write('forum','edit_category','Edited Category '.$name);

    }
    return array($retval, $statusText);
}

// get confirmation to delete forum category
function board_delete_category($id)
{
    global $_CONF, $_FF_CONF, $_TABLES, $LANG_GF01, $LANG_GF06, $LANG_GF93;

    $retval = '';

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $T->set_file ('boards_delcategory','boards_delete.thtml');

    $T->set_var('block_start',COM_startBlock($LANG_GF06[10],'',COM_getBlockTemplate('_admin_block', 'header')));

    $catname = DB_getItem($_TABLES['ff_categories'], "cat_name","id=".(int) $id);

    $T->set_var (array(
        'phpself'       => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        's_form_action' => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',

        'deletenote1'   => sprintf($LANG_GF93['deletecatnote1'], $catname),
        'id'            => $id,
        'type'          => 'category',
        'lang_title'    => $LANG_GF93['gfboard'],
        'deletenote2'   => $LANG_GF93['deletecatnote2'],
        'LANG_DELETE'   => $LANG_GF01['DELETE'],
        'LANG_CANCEL'   => $LANG_GF01['CANCEL'],
        'mode'          => 'delcat',
        'submit_button' => 'delcatconfirm',
    ));

    $T->set_var('block_end',COM_endBlock(COM_getBlockTemplate ('_admin_block', 'footer')));

    $T->parse ('output', 'boards_delcategory');

    $retval .= $T->finish ($T->get_var('output'));

    return $retval;
}

function board_delete_category_confirmed($id)
{
    global $_CONF, $_FF_CONF, $_TABLES, $LANG_GF93;

    $retval = false;
    $statusMessage = array();

    DB_query("DELETE FROM {$_TABLES['ff_categories']} WHERE id=".(int) $id);
    $result = DB_query("SELECT * FROM {$_TABLES['ff_forums']} WHERE forum_cat=".(int) $id);
    while ($A = DB_fetchArray($result) ) {
        $fid = $A['forum_id'];
        ff_deleteForum($fid);
    }
    DB_query("DELETE FROM {$_TABLES['ff_forums']} WHERE forum_cat=".(int) $id);
    $retval = true;
    $statusMessage[] = $LANG_GF93['catdeleted'];

    \glFusion\Admin\AdminAction::write('forum','delete_category','Deleted Category '.$id);

    return array($retval, $statusMessage);
}

/*
 * Resync all forums in a category
 */
function board_resync_category($id)
{
    global $_CONF, $_FF_CONF, $_TABLES, $LANG_GF93;

    $retval = false;
    $statusMessage = array();

    $query = DB_query("SELECT forum_id FROM {$_TABLES['ff_forums']} WHERE forum_cat=".(int) $id);
    while (list($forum_id) = DB_fetchArray($query)) {
        gf_resyncforum($forum_id);
    }
    $retval = true;
    $statusMessage[] = $LANG_GF93['category_resynced'];
    return array($retval, $statusMessage);
}

/* ------------- Forum related administration ----------------- */

function board_add_forum( $statusText = '' )
{
    global $_CONF, $_FF_CONF, $_TABLES, $LANG_GF00, $LANG_GF01, $LANG_GF06, $LANG_GF93;

    $retval = '';
    $rc = false;
    $grouplist = '';
    $ugrouplist = '';
    $id = 0;

    if (!empty($statusText) ) {
        $filter = sanitizer::getInstance();

        $catid      = isset($_POST['category']) ? COM_applyFilter($_POST['category'],true) : 0;
        $order      = isset($_POST['order']) ? COM_applyFilter($_POST['order'],true) : 0;
        $name       = isset($_POST['name']) ? $_POST['name'] : '';
        $dscp       = isset($_POST['dscp']) ? $_POST['dscp'] : '';
        $is_readonly = isset($_POST['is_readonly']) ? COM_applyFilter($_POST['is_readonly'],true) : 0;
        $is_hidden = isset($_POST['is_hidden']) ? COM_applyFilter($_POST['is_hidden'],true) : 0;
        $no_newposts = isset($_POST['no_newposts']) ? COM_applyFilter($_POST['no_newposts'],true) : 0;
        $privgroup = isset($_POST['privgroup']) ? COM_applyFilter($_POST['privgroup'],true) : 0;
        if ($privgroup == 0) {
            $privgroup = 2;
        }
        $attachmentgroup = COM_applyFilter($_POST['attachmentgroup'],true);
        if ( $attachmentgroup == 0) $privgroup = 1;

        $name = $filter->editableText($name);
        $dscp = $filter->editableText($dscp);
    } else {
        $catid = isset($_GET['category']) ? COM_applyFilter($_GET['category']) : 0;
        $name = '';
        $dscp = '';
        $is_readonly = 0;
        $is_hidden = 0;
        $no_newposts = 0;
        $privgroup = 2;
        $attachmentgroup = 1;
    }


    $T = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $T->set_file ('boards_addforum','boards_edtforum.thtml');

    $T->set_var('block_start',COM_startBlock($LANG_GF06[10],'',COM_getBlockTemplate('_admin_block', 'header')));

    $menu_arr = FF_adminNav();

    $T->set_var('admin_menu',ADMIN_createMenu($menu_arr,$LANG_GF00['instructions'],
          $_CONF['site_url'] . '/forum/images/forum.png')
    );

    $result = DB_query("SELECT DISTINCT grp_id, grp_name FROM {$_TABLES['groups']} ORDER BY grp_name");
    $nrows = DB_numRows($result);
    if ( $nrows > 0 ) {
        for ( $i = 1; $i <= $nrows; $i++ ) {
            $G = DB_fetchArray($result);
            if ( $G['grp_id'] == $privgroup ) {
                $grouplist .= '<option value="' . $G['grp_id'] . '" selected="selected">' . ucfirst($G['grp_name']) . '</option>';
            } else {
                $grouplist .= '<option value="' . $G['grp_id'] . '">' . ucfirst($G['grp_name']) . '</option>';
            }
            if ( $G['grp_id'] == $attachmentgroup ) {
                $ugrouplist .= '<option value="' . $G['grp_id'] . '" selected="selected">' . ucfirst($G['grp_name']) . '</option>';
            } else {
                $ugrouplist .= '<option value="' . $G['grp_id'] . '">' . ucfirst($G['grp_name']) . '</option>';
            }
        }
    }
    $catname = DB_getItem($_TABLES['ff_categories'], "cat_name","id=".(int) $catid);

    // build order select
    $order_select = '<option value="0">First Position</option>';

    $sql = "SELECT * FROM {$_TABLES['ff_forums']} WHERE forum_cat = " . (int) $catid . " ORDER BY forum_order ASC";
    $result = DB_query($sql);
    while (($row = DB_fetchArray($result) ) != NULL ) {
        $order_num = $row['forum_order'];
        $order_num++;
        $order_select .= '<option value="'.$row['forum_id'].'">'.$row['forum_name'].'</option>';
    }
    $T->set_var(array(
        'order_list'        => $order_select,
        'phpself'           => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        's_form_action'     => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        'title'             => "{$LANG_GF93['addforum']}&nbsp;{$LANG_GF93['undercat']}&nbsp;" .$catname,
        'mode'              => 'saveaddforum',
        'category_id'       => $catid,
        'cat_select'        => '<input type="hidden" name="category" value="'.$catid.'">',
        'lang_category'     => '',
        'id'                => $id,
        'LANG_DESCRIPTION'  => $LANG_GF01['DESCRIPTION'],
        'LANG_NAME'         => $LANG_GF01['NAME'],
        'LANG_GROUPACCESS'  => $LANG_GF93['groupaccess'],
        'LANG_ATTACHACCESS' => $LANG_GF93['attachaccess'],
        'LANG_readonly'     => $LANG_GF93['readonly'],
        'LANG_readonlydscp' => $LANG_GF93['readonlydscp'],
        'LANG_hidden'       => $LANG_GF93['hidden'],
        'LANG_hiddendscp'   => $LANG_GF93['hiddendscp'],
        'LANG_hideposts'    => $LANG_GF93['hideposts'],
        'LANG_hidepostsdscp'=> $LANG_GF93['hidepostsdscp'],
        'grouplist'         => $grouplist,
        'attachmentgrouplist' => $ugrouplist,
        'LANG_CANCEL'       => $LANG_GF01['CANCEL'],
        'LANG_SAVE'         => $LANG_GF01['SAVE'],
        'block_end'         => COM_endBlock(COM_getBlockTemplate ('_admin_block', 'footer')),
        'status_text'       => $statusText,
        'forum_name'        => $name,
        'forum_dscp'        => $dscp,
        'chk_readonly'      => $is_readonly == 0 ? '' : ' checked="checked"',
        'chk_hidden'        => $is_hidden == 0 ? '' : ' checked="checked"',
        'chk_newposts'      => $no_newposts == 0 ? '' : ' checked="checked"',
    ));

    $T->parse ('output', 'boards_addforum');

    $retval .= $T->finish ($T->get_var('output'));
    return $retval;
}

function board_add_forum_save()
{
    global $_CONF, $_TABLES, $_USER, $_FF_CONF, $LANG_GF93;

    $retval     = false;
    $statusText = array();
    $numErrors  = 0;

    $category    = isset($_POST['category']) ? COM_applyFilter($_POST['category'],true) : 0;
    $name        = isset($_POST['name']) ? $_POST['name'] : '';
    $dscp        = isset($_POST['dscp']) ? $_POST['dscp'] : '';
    $is_readonly = isset($_POST['is_readonly']) ? COM_applyFilter($_POST['is_readonly'],true) : 0;
    $is_hidden   = isset($_POST['is_hidden']) ? COM_applyFilter($_POST['is_hidden'],true) : 0;
    $no_newposts = isset($_POST['no_newposts']) ? COM_applyFilter($_POST['no_newposts'],true) : 0;
    $privgroup   = isset($_POST['privgroup']) ? COM_applyFilter($_POST['privgroup'],true) : 0;
    $forum_order_id = isset($_POST['order']) ? COM_applyFilter($_POST['order'],true) : 0;
    if ($privgroup == 0) {
        $privgroup = 2;
    }
    $attachmentgroup = COM_applyFilter($_POST['attachmentgroup'],true);
    if ( $attachmentgroup == 0) $privgroup = 1;

    if ( $forum_order_id == 0 ) {
        $forum_order = 0;
        $order = 0;
    } else {
        $forum_order = DB_getItem($_TABLES['ff_forums'],'forum_order','forum_id=' . (int) $forum_order_id);
        $order = $forum_order++;
    }

    // data validation
    if ( empty($name) ) {
        $statusText[] = $LANG_GF93['name_blank'];
        $numErrors++;
    }
    if ( MBYTE_strlen($name) > 70 ) {
        $name = MBYTE_substr($name,0,70);
    }
    if ( empty($dscp) ) {
        $statusText[] = $LANG_GF93['desc_blank'];
        $numErrors++;
    }

    if ( $numErrors == 0 ) {
        $name = _ff_preparefordb($name,'text');
        $dscp = _ff_preparefordb($dscp,'text');

        $fields = 'forum_order,forum_name,forum_dscp,forum_cat,grp_id,is_readonly,is_hidden,no_newposts,use_attachment_grpid,rating_view,rating_post';

        DB_query("INSERT INTO {$_TABLES['ff_forums']} ($fields)
            VALUES ('$order','$name','$dscp','$category','$privgroup','$is_readonly','$is_hidden','$no_newposts',$attachmentgroup,0,0)");

        $query = DB_query("SELECT max(forum_id) FROM {$_TABLES['ff_forums']} ");
        list ($forumid) = DB_fetchArray($query);
        $modquery = DB_query("SELECT * FROM {$_TABLES['ff_moderators']} WHERE mod_uid='{$_USER['uid']}' AND mod_forum='$forumid'");
        if (DB_numrows($modquery) < 1) {
            $fields = 'mod_uid,mod_username,mod_forum,mod_delete,mod_ban,mod_edit,mod_move,mod_stick';
            $username = DB_escapeString($_USER['username']);
            DB_query("INSERT INTO {$_TABLES['ff_moderators']} ($fields) VALUES ('{$_USER['uid']}','{$username}', '$forumid','1','1','1','1','1')");
        }
        reorderForums($category);
        $retval = true;
        $statusText[] = $LANG_GF93['forumadded'];

        \glFusion\Admin\AdminAction::write('forum','add_forum','Created Forum '.$name);

    }
    return array($retval, $statusText);
}

/* get confirmation */
function board_delete_forum($id)
{
    global $_CONF, $_TABLES, $_FF_CONF, $LANG_GF01, $LANG_GF06, $LANG_GF93;

    $retval = '';

    $forum_name = DB_getItem($_TABLES['ff_forums'], "forum_name","forum_id=".(int) $id);

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $T->set_file ('boards_delforum','boards_delete.thtml');

    $T->set_var (array(
        'block_start'   => COM_startBlock($LANG_GF06['10'],'',COM_getBlockTemplate('_admin_block', 'header')),
        'phpself'       => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        's_form_action' => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        'deletenote1'   => sprintf($LANG_GF93['deleteforumnote1'], $forum_name),
        'deletenote2'   => $LANG_GF93['deleteforumnote2'],
        'id'            => $id,
        'type'          => 'forum',
        'LANG_DELETE'   => $LANG_GF01['DELETE'],
        'LANG_CANCEL'   => $LANG_GF01['CANCEL'],
        'block_end'     => COM_endBlock(COM_getBlockTemplate ('_admin_block', 'footer')),
        'mode'          => 'delforum',
        'lang_title'    => $LANG_GF06['10'],
         'submit_button' => 'delforumconfirm',
    ));
    $T->parse ('output', 'boards_delforum');
    $retval .= $T->finish ($T->get_var('output'));

    return $retval;
}

function board_delete_forum_confirmed($id)
{
    global $_CONF, $_FF_CONF, $_TABLES, $LANG_GF93;

    $cat_id = DB_getItem($_TABLES['ff_forums'],'forum_cat','forum_id='.(int)$id);

    $retval = false;
    $statusMessage = array();

    ff_deleteForum($id);

    reorderForums($cat_id);

    $retval = true;
    $statusMessage[] = $LANG_GF93['forumdeleted'];

    \glFusion\Admin\AdminAction::write('forum','delete_forum','Deleted Forum '.$id);

    return array($retval, $statusMessage);
}

function board_edit_forum($id, $statusText='')
{
    global $_CONF, $_FF_CONF, $_TABLES, $LANG_GF00, $LANG_GF01, $LANG_GF06,
           $LANG_GF93, $LANG_ADMIN;

    $retval = '';

    $menu_arr = array();

    $filter = sanitizer::getInstance();

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $T->set_file ('boards_edtforum','boards_edtforum.thtml');

    $T->set_var('block_start',COM_startBlock($LANG_GF93['gfboard'],'',COM_getBlockTemplate('_admin_block', 'header')));

    $menu_arr = FF_adminNav();

    $T->set_var('admin_menu',ADMIN_createMenu($menu_arr,$LANG_GF00['instructions'],
          $_CONF['site_url'] . '/forum/images/forum.png')
    );

    if ( empty($statusText)) {
        // first time in - pull data from database...
        $sql  = "SELECT forum_name,forum_cat,forum_dscp,grp_id,use_attachment_grpid,forum_order,is_hidden,is_readonly,no_newposts ";
        $sql .= "FROM {$_TABLES['ff_forums']} WHERE forum_id=".(int) $id;

        $resForum  = DB_query($sql);
        list ($forum_name, $forum_category,$forum_dscp,$privgroup,$attachgroup,$forum_order,$is_hidden,$is_readonly,$no_newposts) = DB_fetchArray($resForum);
    } else {
        // grab the posted data
        $forum_name     = $_POST['name'];
        $forum_category = COM_applyFilter($_POST['category'],true);
        $forum_dscp     = $_POST['dscp'];
        $privgroup      = COM_applyFilter($_POST['privgroup'],true);
        $attachgroup    = COM_applyFilter($_POST['attachmentgroup'],true);
        $forum_order    = 0;

        $is_hidden      = isset($_POST['is_hidden']) ? 1 : 0;
        $is_readonly    = isset($_POST['is_readonly']) ? 1 : 0;
        $no_newposts    = isset($_POST['no_newposts']) ? 1 : 0;

        $forum_name = $filter->editableText($forum_name);
        $forum_dscp = $filter->editableText($forum_dscp);
    }

    $title = sprintf($LANG_GF93['editforumnote'], $forum_name);

    // build select lists

    $resGroups = DB_query("SELECT DISTINCT grp_id,grp_name FROM {$_TABLES['groups']} ORDER BY grp_name ASC ");
    $nrows     = DB_numRows($resGroups);
    $grouplist = '';
    $attachgrouplist = '';
    while ( list($grp, $name) = DB_fetchArray($resGroups)) {
        if ($grp == $privgroup) {
            $grouplist .= '<option value="' .$grp. '" selected="selected">' . $name. '</option>';
        } else {
            $grouplist .= '<option value="' .$grp. '">' . ucfirst($name). '</option>';
        }
        if ($grp == $attachgroup) {
            $attachgrouplist .= '<option value="' .$grp. '" selected="selected">' . ucfirst($name). '</option>';
        } else {
            $attachgrouplist .= '<option value="' .$grp. '">' . ucfirst($name). '</option>';
        }
    }
    $catSelect = '<select name="category">';
    $catResult = DB_query("SELECT * FROM {$_TABLES['ff_categories']} ORDER BY cat_order ASC");
    while ( ($C = DB_fetchArray($catResult)) != FALSE ) {
        $catSelect .= '<option value="'.$C['id'].'" '.($C['id'] == $forum_category ? ' selected="selected"' : '').'>'.$C['cat_name'].'</option>';
    }
    $catSelect .= '</select>';

    $order_select = '<option value="0">' . 'First Position' . '</option>' . LB;
    $result = DB_query("SELECT forum_id,forum_name,forum_order FROM {$_TABLES['ff_forums']} WHERE forum_cat=" . (int) $forum_category." ORDER BY forum_order ASC");
    $order = 10;
    while ($row = DB_fetchArray($result)) {
        if ( $forum_order != $order ) {
            $test_order = $order + 10;
            $order_select .= '<option value="' . $row['forum_id'] . '"' . ($forum_order == $test_order ? ' selected="selected"' : '') . '>' . $row['forum_name'] . '</option>' . LB;
        }
        $order += 10;
    }

    $T->set_var ( array (
        'order_list'  => $order_select,
        'phpself'       => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        's_form_action' => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        'title'         => sprintf($LANG_GF93['editforumnote'], $forum_name),
        'cat_select'    => $catSelect,
        'lang_category' => $LANG_GF01['category'],
        'id'            => $id,
        'mode'          => 'saveeditforum',
        'category_id'   => $forum_category,
        'forum_name'    => $forum_name,
        'forum_dscp'    => $forum_dscp,
        'forum_order'   => $forum_order,
        'chk_hidden'    => ($is_hidden) ? 'checked="checked"' : '',
        'chk_readonly'  => ($is_readonly) ? 'checked="checked"' : '',
        'chk_newposts'  => ($no_newposts) ? 'checked="checked"' : '',
        'LANG_DESCRIPTION'  => $LANG_GF01['DESCRIPTION'],
        'LANG_NAME'     => $LANG_GF01['NAME'],
        'LANG_GROUPACCESS'  => $LANG_GF93['groupaccess'],
        'LANG_ATTACHACCESS' => $LANG_GF93['attachaccess'],
        'LANG_readonly' => $LANG_GF93['readonly'],
        'LANG_readonlydscp' => $LANG_GF93['readonlydscp'],
        'LANG_hidden'       => $LANG_GF93['hidden'],
        'LANG_hiddendscp'   => $LANG_GF93['hiddendscp'],
        'LANG_hideposts'    => $LANG_GF93['hideposts'],
        'LANG_hidepostsdscp'    => $LANG_GF93['hidepostsdscp'],
        'grouplist'         => $grouplist,
        'attachmentgrouplist'   => $attachgrouplist,
        'LANG_SAVE'         => $LANG_GF01['SAVE'],
        'LANG_CANCEL'       => $LANG_GF01['CANCEL'],
        'block_end'         => COM_endBlock(COM_getBlockTemplate ('_admin_block', 'footer')),
        'status_text'       => $statusText,
        'lang_display_after'=> $LANG_GF01['display_after'],
    ));

    $T->parse ('output', 'boards_edtforum');
    $retval .= $T->finish ($T->get_var('output'));

    return $retval;
}

function board_edit_forum_save($id)
{
   global $_CONF, $_TABLES, $_USER, $_FF_CONF, $LANG_GF93;

    $retval     = false;
    $statusText = array();
    $numErrors  = 0;

    $category    = isset($_POST['category']) ? COM_applyFilter($_POST['category'],true) : 0;
    $forum_order_id = isset($_POST['order']) ? COM_applyFilter($_POST['order'],true) : 0;
    $name        = isset($_POST['name']) ? $_POST['name'] : '';
    $dscp        = isset($_POST['dscp']) ? $_POST['dscp'] : '';
    $is_readonly = isset($_POST['is_readonly']) ? COM_applyFilter($_POST['is_readonly'],true) : 0;
    $is_hidden   = isset($_POST['is_hidden']) ? COM_applyFilter($_POST['is_hidden'],true) : 0;
    $no_newposts = isset($_POST['no_newposts']) ? COM_applyFilter($_POST['no_newposts'],true) : 0;
    $privgroup   = isset($_POST['privgroup']) ? COM_applyFilter($_POST['privgroup'],true) : 0;
    if ($privgroup == 0) {
        $privgroup = 2;
    }
    $attachmentgroup = COM_applyFilter($_POST['attachmentgroup'],true);
    if ( $attachmentgroup == 0) $privgroup = 1;

    // data validation
    if ( empty($name) ) {
        $statusText[] = $LANG_GF93['name_blank'];
        $numErrors++;
    }
    if ( MBYTE_strlen($name) > 70 ) {
        $name = MBYTE_substr($name,0,70);
    }
    if ( empty($dscp) ) {
        $statusText[] = $LANG_GF93['desc_blank'];
        $numErrors++;
    }

    if ( $numErrors == 0 ) {
        if ( $forum_order_id == 0 ) {
            $forum_order = 0;
        } else {
            $forum_order = DB_getItem($_TABLES['ff_forums'],'forum_order','forum_id=' . (int) $forum_order_id);
        }
        $order = $forum_order + 1;

        $name = _ff_preparefordb($name,'text');
        $dscp = _ff_preparefordb($dscp,'text');

        $sql = "UPDATE {$_TABLES['ff_forums']} SET forum_name='".$name."',forum_order=".(int) $order.",forum_dscp='".$dscp."', grp_id=".(int) $privgroup.", ";
        $sql .= "is_hidden='".DB_escapeString($is_hidden)."', is_readonly='".DB_escapeString($is_readonly)."', no_newposts='".DB_escapeString($no_newposts)."',use_attachment_grpid=".(int) $attachmentgroup.",forum_cat=".(int) $category." ";
        $sql .= "WHERE forum_id=".(int) $id;

        DB_query($sql);
        reorderForums($category);
        $retval = true;
        $statusText[] = $LANG_GF93['forumedited'];

        \glFusion\Admin\AdminAction::write('forum','edit_forum','Edited Forum '.$name);

    }
    return array($retval, $statusText);
}

function reorderForums( $catid ) {
    global $_TABLES;

    $orderCount = 10;

    $sql = "SELECT forum_id,`forum_order` FROM {$_TABLES['ff_forums']} WHERE forum_cat=".$catid." ORDER BY `forum_order` ASC";
    $result = DB_query($sql);
    while ($M = DB_fetchArray($result)) {
        $M['forum_order'] = $orderCount;
        $orderCount += 10;
        DB_query("UPDATE {$_TABLES['ff_forums']} SET `forum_order`=" . $M['forum_order'] . " WHERE forum_id=".$M['forum_id'] );
    }
}

/* ------------- Main Code Block ----------------- */

$mode       = isset($_REQUEST['mode']) ? COM_applyFilter($_REQUEST['mode']) : '';
$id         = isset($_POST['id']) ? COM_applyFilter($_POST['id'],true) : 0;

$display    = '';
$grouplist  = '';
$ugrouplist = '';
$pageBody   = '';
$msgText    = '';
$statusText = '';

$valid_modes = array( 'savecat','addcat','saveaddcat','resynccat','delcat',
                      'editcat','addforum','saveaddforum','delforum','editforum',
                      'saveeditforum','resyncforum'
               );

$mode = isset($_REQUEST['mode']) ? COM_applyFilter($_REQUEST['mode']) : 'index';

if (!in_array($mode,$valid_modes) ) {
    $mode = 'index';
}

if ( isset($_POST['cancel']) ) {
    $pageBody .= board_admin_list();
} else {
    switch ( $mode ) {
        case 'addforum' :
            $pageBody .= board_add_forum();
            break;
        case 'saveaddforum' :
          list($rc,$message) = board_add_forum_save();
            if ( is_array($message) ) {
                foreach ($message as $msg ) {
                    $msgText .= $msg . '<br>';
                }
            }
            if ( $rc === true ) {
                $statusText = COM_showMessageText($msgText, $LANG_GF93['catadded'], false, 'success');
                $pageBody .= board_admin_list($statusText);
            } else {
                $statusText = COM_showMessageText($msgText, $LANG_GF01['ERROR'],true,'error');
                $pageBody .= board_add_forum($statusText);
            }
            break;
        case 'editforum' :
            $pageBody .= board_edit_forum($id);
            break;
        case 'saveeditforum' :
            list($rc,$message) = board_edit_forum_save($id);
            if ( is_array($message) ) {
                foreach ($message as $msg ) {
                    $msgText .= $msg . '<br>';
                }
            }
            if ( $rc === true ) {
                $statusText = COM_showMessageText($msgText, $LANG_GF93['forumedited'], false, 'success');
                $pageBody .= board_admin_list($statusText);
            } else {
                $statusText = COM_showMessageText($msgText, $LANG_GF01['ERROR'],true,'error');
                $pageBody .= board_edit_forum($id,$statusText);
            }
            break;
        case 'delforum' :
            if ( isset($_POST['delforumconfirm']) ) {
                list($rc, $message) = board_delete_forum_confirmed($id);
                if ( is_array($message) ) {
                    foreach ($message as $msg ) {
                        $msgText .= $msg . '<br>';
                    }
                }
                if ( $rc === true ) {
                    $statusText = COM_showMessageText($msgText, $LANG_GF93['forumdeleted'], false, 'success');
                }
                $pageBody .= board_admin_list($statusText);
            } else {
                $pageBody .= board_delete_forum($id);
            }
            break;
        case 'resyncforum' :
            gf_resyncforum($id);
            $statusText = COM_showMessageText($LANG_GF93['forum_resynced'],false,'success');
            $pageBody .= board_admin_list($statusText);
            break;
        case 'editcat' :
            $pageBody .= board_edit_category($id);
            break;
        case 'delcat' :
            if ( isset($_POST['delcatconfirm']) ) {
                list($rc, $message) = board_delete_category_confirmed($id);
                if ( is_array($message) ) {
                    foreach ($message as $msg ) {
                        $msgText .= $msg . '<br>';
                    }
                }
                if ( $rc === true ) {
                    $statusText = COM_showMessageText($msgText, $LANG_GF93['catdeleted'], false, 'success');
                }
                $pageBody .= board_admin_list($statusText);
            } else {
                $pageBody .= board_delete_category($id);
            }
            break;
        case 'resynccat' :
            $msgCount = 0;
            list($rc,$message) = board_resync_category($id);
            if ( is_array($message) ) {
                foreach ($message as $msg ) {
                    $msgText .= $msg . '<br>';
                    $msgCount++;
                }
            }
            if ( $msgCount > 0 ) {
                $statusText = COM_showMessageText($msgText, $LANG_GF01['RESYNCCAT'],false,'success');
            }
            $pageBody .= board_admin_list($statusText);
            break;

        case 'savecat' :
            list($rc,$message) = board_edit_category_save($id);
            if ( is_array($message) ) {
                foreach ($message as $msg ) {
                    $msgText .= $msg . '<br>';
                }
            }
            if ( $rc === true ) {
                $statusText = COM_showMessageText($msgText, $LANG_GF93['catedited'], false, 'success');
                $pageBody .= board_admin_list($statusText);
            } else {
                $statusText = COM_showMessageText($msgText, $LANG_GF01['ERROR'],true,'error');
                $pageBody .= board_edit_category($id,$statusText);
            }
            break;
        case 'saveaddcat' :
          list($rc,$message) = board_add_category_save();
            if ( is_array($message) ) {
                foreach ($message as $msg ) {
                    $msgText .= $msg . '<br>';
                }
            }
            if ( $rc === true ) {
                $statusText = COM_showMessageText($msgText, $LANG_GF93['catadded'], false, 'success');
                $pageBody .= board_admin_list($statusText);
            } else {
                $statusText = COM_showMessageText($msgText, $LANG_GF01['ERROR'],true,'error');
                $pageBody .= board_add_category($statusText);
            }
            break;
        case 'addcat':
             $pageBody = board_add_category();
             break;
        default :
            $pageBody .= board_admin_list();
            break;
    }
}

$display  = COM_siteHeader();
$display .= $pageBody;
$display .= COM_siteFooter();
echo $display;
?>
