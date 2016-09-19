<?php
// +--------------------------------------------------------------------------+
// | Menu Editor - glFusion CMS                                               |
// +--------------------------------------------------------------------------+
// | menu.php                                                                 |
// |                                                                          |
// | glFusion CMS Menu Administration                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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
require_once $_CONF['path_system'] . 'classes/menu.class.php';
require_once $_CONF['path_system'] . 'lib-menu.php';

USES_lib_admin();
$display = '';
$content = '';

$MenuElementAllowedHTML = "i[class|style],div[class|style],span[class|style],img[src|class|style],em,strong,del,ins,q,abbr,dfn,small";

// Only let admin users access this page
if (!SEC_hasRights('menu.admin')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[37],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} unauthorized user tried to access the menu editor screen.");
    echo $display;
    exit;
}

function MB_displayMenuList( ) {
    global $_CONF, $_USER, $_TABLES, $LANG_MB01, $LANG_MB_ADMIN, $LANG_ADMIN,$LANG_MB_MENU_TYPES;

    $retval = '';
    $menuArray = array();

    $mbadmin = SEC_hasRights('menu.admin');
    $root    = SEC_inGroup('Root');

    if (COM_isAnonUser()) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $result = DB_query("SELECT * FROM {$_TABLES['menu']}",1);
    while ( $menu = DB_fetchArray($result) ) {
        $menuID = $menu['id'];
        $menuArray[$menu['id']]['menu_name']   = $menu['menu_name'];
        $menuArray[$menu['id']]['menu_id']     = $menu['id'];
        $menuArray[$menu['id']]['active']      = $menu['menu_active'];
        $menuArray[$menu['id']]['menu_type']   = $menu['menu_type'];
        $menuArray[$menu['id']]['group_id']    = $menu['group_id'];

        $menuArray[$menu['id']]['menu_perm'] =  0;

        if ($mbadmin || $root) {
            $menuArray[$menu['id']]['menu_perm'] = 3;
        } else {
            if ( in_array( $menu['group_id'], $_GROUPS ) ) {
                $menuArray[$menu['id']]['menu_perm'] =  3;
            }
        }
    }

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/menu.php?mode=newmenu',
                  'text' => $LANG_MB01['add_newmenu']),
            array('url'  => $_CONF['site_admin_url'],
                  'text' => $LANG_ADMIN['admin_home']),
    );
    $retval  .= COM_startBlock($LANG_MB01['menu_builder'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_MB_ADMIN[1],
                                $_CONF['layout_url'] . '/images/icons/menubuilder.png');
    $data_arr = array();
    $text_arr = array();
    $options  = array();

    $header_arr = array(
        array('text' => $LANG_MB01['label'], 'field' => 'menu_name'),
        array('text' => $LANG_MB01['clone'], 'field' => 'copy','align' => 'center' ),
        array('text' => $LANG_MB01['active'], 'field' => 'active','align' => 'center'),
        array('text' => $LANG_MB01['elements'], 'field' => 'elements', 'align' => 'center' ),
        array('text' => $LANG_MB01['options'], 'field'=> 'options', 'align' => 'center'),
        array('text' => $LANG_MB01['delete'], 'field' => 'delete', 'align' => 'center')
    );

    $text_arr = array('has_menu'    => false,
                      'title'       => '',
                      'help_url'    => '',
                      'no_data'     => $LANG_MB01['no_elements'],
                      'form_url'    => "{$_CONF['site_admin_url']}/menu.php"
    );

    $form_arr['bottom'] = '
    <input type="hidden" name="mode" value="menuactivate" />
    <script type="text/javascript">
        document.getElementById(\'menubuilder\').style.display=\'\'
    </script>
    ';

    if ( is_array($menuArray) ) {
        foreach ($menuArray AS $menu) {
            $menu_entry['menu_id'] = $menu['menu_id'];
            $menu_entry['menu_name'] = $menu['menu_name'];
            $menu_entry['copy'] = $menu['menu_id'];
            $menu_entry['active'] = $menu['active'];
            $menu_entry['elements'] = $menu['menu_id'];
            $menu_entry['options'] = $menu['menu_id'];
            $menu_entry['delete'] = $menu['menu_id'];
            $menu_entry['menu_type'] = $menu['menu_type'];
            $menu_entry['info']   = $menu['menu_name'] . '::'
                                  . '<b>' . $LANG_MB01['type'] . ':</b><br />' . $LANG_MB_MENU_TYPES[$menu['menu_type']] . '<br/>';
            $data_arr[] = $menu_entry;
        }
    }
    $retval .= ADMIN_simpleList("_mb_getListField_menulist", $header_arr, $text_arr, $data_arr,$options,$form_arr);
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    $outputHandle = outputHandler::getInstance();
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/admin.js',HEADER_PRIO_NORMAL,'text/javascript');

    return $retval;
}

/*
 * Create a new menu
 */

function MB_cloneMenu( $menu_id ) {
    global $_CONF, $_TABLES, $LANG_MB01, $LANG_MB_ADMIN,
           $LANG_MB_MENU_TYPES, $LANG_ADMIN;

    $retval = '';

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/menu.php',
                  'text' => $LANG_MB01['menu_list']),
            array('url'  => $_CONF['site_admin_url'],
                  'text' => $LANG_ADMIN['admin_home']),
    );
    $retval  .= COM_startBlock($LANG_MB01['menu_builder'].' :: '.$LANG_MB01['add_newmenu'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_MB_ADMIN[2],
                                $_CONF['layout_url'] . '/images/icons/menubuilder.png');


    $T = new Template($_CONF['path_layout'] . 'admin/menu');
    $T->set_file ('admin','clonemenu.thtml');

    $T->set_var(array(
        'form_action'       => $_CONF['site_admin_url'] . '/menu.php',
        'birdseed'          => '<a href="'.$_CONF['site_admin_url'].'/menu.php">'.$LANG_MB01['menu_list'].'</a> :: '.$LANG_MB01['clone'],
        'lang_admin'        => $LANG_MB01['menu_builder'],
        'menu_id'           => $menu_id,
    ));
    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

/*
 * Saves a clone menu element
 */

function MB_saveCloneMenu( ) {
    global $_CONF, $_TABLES, $_GROUPS;

    $menu_name  = DB_escapeString(COM_applyFilter($_POST['menuname']));
    $menu       = COM_applyFilter($_POST['menu'],true);

    $pidmap = array();

    $sql = "SELECT * FROM {$_TABLES['menu']} WHERE id=".(int) $menu;
    $result = DB_query($sql);
    if ( DB_numRows($result) > 0 ) {
        $M = DB_fetchArray($result);
        $menu_type   = $M['menu_type'];
        $menu_active = $M['menu_active'];
        $group_id    = $M['group_id'];

        $sqlFieldList  = 'menu_name,menu_type,menu_active,group_id';
        $sqlDataValues = "'$menu_name',$menu_type,$menu_active,$group_id";
        DB_save($_TABLES['menu'], $sqlFieldList, $sqlDataValues);
        $menu_id = DB_insertId();
        $meadmin    = SEC_hasRights('menu.admin');
        $root       = SEC_inGroup('Root');
        $groups     = $_GROUPS;

        $sql = "SELECT * FROM {$_TABLES['menu_elements']} WHERE menu_id=".(int)$menu;
        $result = DB_query($sql);
        while ($M = DB_fetchArray($result)) {
            $M['menu_id']       = $menu_id;
            $element            = new menuElement();
            $element->constructor( $M, $meadmin, $root, $groups );
            $element->id        = $element->createElementID($M['menu_id']);
            $pidmap[$M['id']] = $element->id;
            $element->saveElement();
        }
        if ( is_array($pidmap) ) {
            foreach ( $pidmap AS $oldid => $newid ) {
                DB_query("UPDATE {$_TABLES['menu_elements']} SET pid=".(int) $newid." WHERE menu_id=".(int) $menu_id." AND pid=".(int) $oldid);
            }
        }
    }
    CACHE_remove_instance('menu');
    CACHE_remove_instance('css');
    $randID = rand();
    DB_save($_TABLES['vars'],'name,value',"'cacheid',$randID");
}


/*
 * Create a new menu
 */

function MB_createMenu( ) {
    global $_CONF, $_TABLES, $LANG_MB01, $LANG_MB_ADMIN,
           $LANG_MB_MENU_TYPES, $LANG_ADMIN;

    $retval = '';

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/menu.php',
                  'text' => $LANG_MB01['menu_list']),
            array('url'  => $_CONF['site_admin_url'],
                  'text' => $LANG_ADMIN['admin_home']),
    );
    $retval  .= COM_startBlock($LANG_MB01['menu_builder'].' :: '.$LANG_MB01['add_newmenu'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_MB_ADMIN[2],
                                $_CONF['layout_url'] . '/images/icons/menubuilder.png');


    $T = new Template($_CONF['path_layout'] . 'admin/menu');
    $T->set_file ('admin','createmenu.thtml');

    // build menu type select

    $menuTypeSelect = '<select id="menutype" name="menutype">' . LB;
    while ( $types = current($LANG_MB_MENU_TYPES) ) {
        $menuTypeSelect .= '<option value="' . key($LANG_MB_MENU_TYPES) . '"';
        $menuTypeSelect .= '>' . $types . '</option>' . LB;
        next($LANG_MB_MENU_TYPES);
    }
    $menuTypeSelect .= '</select>' . LB;

    // build group select

    $rootUser = DB_getItem($_TABLES['group_assignments'],'ug_uid','ug_main_grp_id=1');
    $usergroups = SEC_getUserGroups($rootUser);
    uksort($usergroups, "strnatcasecmp");
    $group_select = '<select id="group" name="group">' . LB;
    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        $group_select .= '>' . ucfirst(key($usergroups)) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    $T->set_var(array(
        'form_action'       => $_CONF['site_admin_url'] . '/menu.php',
        'birdseed'          => '<a href="'.$_CONF['site_admin_url'].'/menu.php">'.$LANG_MB01['menu_list'].'</a> :: '.$LANG_MB01['add_newmenu'],
        'lang_admin'        => $LANG_MB01['menu_builder'],
        'menutype_select'   => $menuTypeSelect,
        'group_select'      => $group_select,
    ));
    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

/*
 * Saves a new menu element
 */

function MB_saveNewMenu( ) {
    global $_CONF, $_TABLES, $_GROUPS, $LANG_MB;

    $errors = 0;
    $errMsg = '';

    // sanity check

    if ( !isset($_POST['menuname']) || $_POST['menuname'] == '' ) {
        $errors++;
        $errMsg .= $LANG_MB['menu_name_error'];
    } else {
        $menuname = COM_applyFilter($_POST['menuname']);
        if ( strstr($menuname,' ' ) !== FALSE ) {
            $errors++;
            $errMsg .= $LANG_MB['menu_name_space'].'<br/>';
        }
        $existing_id = DB_getItem($_TABLES['menu'],'id','menu_name="'.DB_escapeString($menuname).'"');
        if ( $existing_id > 0 ) {
            $errors++;
            $errMsg .= $LANG_MB['menu_name_exits'];
        }
    }

    if ( $errors > 0 ) {
        return $errMsg;
    }

    $menuname   = DB_escapeString(COM_applyFilter($_POST['menuname']));
    $menutype   = COM_applyFilter($_POST['menutype'],true);
    $menuactive = isset($_POST['menuactive']) ? COM_applyFilter($_POST['menuactive'],true) : 0;
    $menugroup  = COM_applyFilter($_POST['group'],true);

    $sqlFieldList  = 'menu_name,menu_type,menu_active,group_id';
    $sqlDataValues = "'$menuname',$menutype,$menuactive,$menugroup";
    DB_save($_TABLES['menu'], $sqlFieldList, $sqlDataValues);

    $menu_id = DB_insertId();

    CACHE_remove_instance('menu');
    CACHE_remove_instance('css');
    $randID = rand();
    DB_save($_TABLES['vars'],'name,value',"'cacheid',$randID");
    return '';
}

function MB_saveEditMenu( ) {
    global $_CONF, $_TABLES, $_GROUPS, $LANG_MB;

    $errors = 0;
    $errMsg = '';

    // sanity check

    if ( !isset($_POST['menuname']) || $_POST['menuname'] == '' ) {
        $errors++;
        $errMsg .= $LANG_MB['menu_name_error'];
    } else {
        $menuname = COM_applyFilter($_POST['menuname']);
        if ( strstr($menuname,' ' ) !== FALSE ) {
            $errors++;
            $errMsg .= $LANG_MB['menu_name_space'];;
        }
    }

    if ( $errors > 0 ) {
        return $errMsg;
    }

    $menu_id    = COM_applyFilter($_POST['menu_id'],true);
    $menuname   = DB_escapeString(COM_applyFilter($_POST['menuname']));
    $menutype   = COM_applyFilter($_POST['menutype'],true);
    $menuactive = isset($_POST['menuactive']) ? COM_applyFilter($_POST['menuactive'],true) : 0;
    $menugroup  = COM_applyFilter($_POST['group'],true);

    $sqlFieldList  = 'id,menu_name,menu_type,menu_active,group_id';
    $sqlDataValues = "'$menu_id','$menuname',$menutype,$menuactive,$menugroup";
    DB_save($_TABLES['menu'], $sqlFieldList, $sqlDataValues);

    CACHE_remove_instance('menu');
    CACHE_remove_instance('css');
    $randID = rand();
    DB_save($_TABLES['vars'],'name,value',"'cacheid',$randID");
    return '';
}

/*
 * Displays a list of all menu elements for the given menu
 */

function MB_displayTree( $menu_id ) {
    global $_CONF, $LANG_MB01, $LANG_MB_ADMIN, $LANG_ADMIN;

    $retval = '';

    $menu = menu::getInstance( $menu_id );

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/menu.php?mode=new&amp;menuid='.(int) $menu_id,
                  'text' => $LANG_MB01['create_element']),
            array('url'  => $_CONF['site_admin_url'] .'/menu.php',
                  'text' => $LANG_MB01['menu_list']),
    );
    $retval  .= COM_startBlock($LANG_MB01['menu_builder'].' :: '.$menu->name,'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_MB_ADMIN[3],
                                $_CONF['layout_url'] . '/images/icons/menubuilder.png');

    $data_arr = array();
    $text_arr = array();
    $options  = array();

    $header_arr = array(
        array('text' => $LANG_MB01['menu_element'], 'field' => 'label'),
        array('text' => $LANG_MB01['info'], 'field' => 'info', 'align' => 'center' ),
        array('text' => $LANG_MB01['enabled'], 'field' => 'enabled', 'align' => 'center'),
        array('text' => $LANG_MB01['edit'], 'field' => 'edit', 'align' => 'center'),
        array('text' => $LANG_MB01['delete'], 'field' => 'delete', 'align' => 'center'),
        array('text' => $LANG_MB01['order'], 'field' => 'order', 'align' => 'center')
    );

    $text_arr = array('has_menu'    => false,
                      'title'       => '',
                      'help_url'    => '',
                      'no_data'     => $LANG_MB01['no_elements'],
                      'form_url'    => "{$_CONF['site_admin_url']}/menu.php"
    );

    $form_arr['bottom'] = '<input type="hidden" id="menu" name="menu" value="'.$menu_id.'"/>' . LB
                        . '<input type="hidden" name="mode" value="activate"/>'. LB
                        . '<script type="text/javascript">' . LB
                        . '    document.getElementById(\'menubuilder\').style.display=\'\''.LB
                        . '</script>';

    $data_arr = $menu->editTree();

    $retval .= ADMIN_simpleList("_mb_getListField_menu", $header_arr, $text_arr, $data_arr,$options,$form_arr);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    $outputHandle = outputHandler::getInstance();
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/admin.js',HEADER_PRIO_NORMAL,'text/javascript');

    return $retval;
}


function MB_getMenuList( $selected = '' )
{
    global $_TABLES;

    $menu_select = '';

    $result = DB_query("SELECT id, menu_name FROM {$_TABLES['menu']} ORDER BY menu_name ASC",1);
    while ( ( $M = DB_fetchArray($result) ) ) {
        $menu_select .= '<option value="' . $M['id'].'"' . ($M['id'] == $selected ? ' selected="selected"' : '') . '>' . $M['menu_name'] .'</option>' . LB;
    }
    return $menu_select;
}


/*
 * Moves a menu element up or down
 */
function MB_moveElement( $menu_id, $mid, $direction ) {
    global $_CONF, $_TABLES;

    $menu = menu::getInstance($menu_id);

    switch ( $direction ) {
        case 'up' :
            $neworder = $menu->menu_elements[$mid]->order - 11;
            DB_query("UPDATE {$_TABLES['menu_elements']} SET element_order=" . $neworder . " WHERE menu_id=".$menu_id." AND id=" . $mid);
            break;
        case 'down' :
            $neworder = $menu->menu_elements[$mid]->order + 11;
            DB_query("UPDATE {$_TABLES['menu_elements']} SET element_order=" . $neworder . " WHERE menu_id=".$menu_id." AND id=" . $mid);
            break;
    }
    $pid = $menu->menu_elements[$mid]->pid;

    $menu->reorderMenu($pid);
    CACHE_remove_instance('menu');

    return;
}

/*
 * Creates a new menu element
 */

function MB_createElement ( $menu_id ) {
    global $_CONF, $_TABLES, $_PLUGINS, $LANG_MB01, $LANG_MB_ADMIN, $LANG_MB_TYPES,
           $LANG_MB_GLTYPES, $LANG_MB_GLFUNCTION;

    $menu = menu::getInstance($menu_id);

    $retval = '';
    $group_select = '';

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/menu.php?mode=menu&amp;menu='.$menu_id,
                  'text' => $LANG_MB01['return_to'] . $menu->name),
            array('url'  => $_CONF['site_admin_url'] .'/menu.php',
                  'text' => $LANG_MB01['menu_list']),
    );
    $retval  .= COM_startBlock($LANG_MB01['menu_builder'].' :: '.$LANG_MB01['create_element'] .' for ' . $menu->name,'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_MB_ADMIN[4],
                                $_CONF['layout_url'] . '/images/icons/menubuilder.png');

    // build types select

    $spCount = 0;

    $sp_select = '<select id="spname" name="spname">' . LB;
    if (in_array('staticpages', $_PLUGINS)) {
        $sql = "SELECT sp_id,sp_title,sp_label FROM {$_TABLES['staticpage']} WHERE sp_status = 1 ORDER BY sp_title ";
        $result = DB_query($sql);
        while (list ($sp_id, $sp_title,$sp_label) = DB_fetchArray($result)) {
            if ( $sp_title == '' ) {
                $label = $sp_label;
            } else {
                $label = $sp_title;
            }
            $sp_select .= '<option value="' . $sp_id . '">' . $label . '</option>' . LB;
            $spCount++;
        }
    }
    $sp_select .= '</select>' . LB;

    $topicCount = 0;
    $topic_select = '<select id="topicname" name="topicname">' . LB;
    $sql = "SELECT tid,topic FROM {$_TABLES['topics']} ORDER BY topic";
    $result = DB_query($sql);
    while (list ($tid, $topic) = DB_fetchArray($result)) {
        $topic_select .= '<option value="' . $tid . '">' . $topic . '</option>' . LB;
        $topicCount++;
    }
    $topic_select .= '</select>' . LB;

    if ( $topicCount == 0 ) {
        $topic_select = '';
    }

    $type_select = '<select id="menutype" name="menutype">' . LB;
    while ( $types = current($LANG_MB_TYPES) ) {
        if ( $spCount == 0 && key($LANG_MB_TYPES) == 5 ) {
            // skip it
        } else {
            if ( ($menu->type == 2 || $menu->type == 4 ) && (key($LANG_MB_TYPES) == 1 || key($LANG_MB_TYPES) == 3)){
                // skip it
            } else {
                $type_select .= '<option value="' . key($LANG_MB_TYPES) . '"';
                $type_select .= '>' . $types . '</option>' . LB;
            }
        }
        next($LANG_MB_TYPES);
    }
    $type_select .= '</select>' . LB;

    $gl_select = '<select id="gltype" name="gltype">' . LB;
    while ( $gltype = current($LANG_MB_GLTYPES) ) {
        $gl_select .= '<option value="' . key($LANG_MB_GLTYPES) . '"';
        $gl_select .= '>' . $gltype . '</option>' . LB;
        next($LANG_MB_GLTYPES);
    }
    $gl_select .= '</select>' . LB;

    $plugin_select = '<select id="pluginname" name="pluginname">' . LB;
    $plugin_menus = _mbPLG_getMenuItems();
    ksort($plugin_menus);
    $num_plugins = count($plugin_menus);
    for( $i = 1; $i <= $num_plugins; $i++ ) {
        $plugin_select .= '<option value="' . key($plugin_menus) . '">' . ucfirst(key($plugin_menus)) . '</option>' . LB;
        next( $plugin_menus );
    }
    $plugin_select .= '</select>' . LB;

    $glfunction_select = '<select id="glfunction" name="glfunction">' . LB;
    while ( $glfunction = current($LANG_MB_GLFUNCTION) ) {
        $glfunction_select .= '<option value="' . key($LANG_MB_GLFUNCTION) . '"';
        $glfunction_select .= '>' . $glfunction . '</option>' . LB;
        next($LANG_MB_GLFUNCTION);
    }
    $glfunction_select .= '</select>' . LB;

    if ( $menu->type == 2 || $menu->type == 4 ) {
        $parent_select = '<input type="hidden" name="pid" id="pid" value="0"/>'.$LANG_MB01['top_level'];
    } else {
        $parent_select = '<select name="pid" id="pid">' . LB;
        $parent_select .= '<option value="0">' . $LANG_MB01['top_level'] . '</option>' . LB;
        $result = DB_query("SELECT id,element_label FROM {$_TABLES['menu_elements']} WHERE menu_id='" . (int) $menu_id . "' AND element_type=1");
        while ($row = DB_fetchArray($result)) {
            $parent_select .= '<option value="' . $row['id'] . '">' . $row['element_label'] . '</option>' . LB;
        }
        $parent_select .= '</select>' . LB;
    }

    $order_select = '<select id="menuorder" name="menuorder">' . LB;
    $order_select .= '<option value="0">' . $LANG_MB01['first_position'] . '</option>' . LB;

    $result = DB_query("SELECT id,element_label,element_order FROM {$_TABLES['menu_elements']} WHERE menu_id='" . $menu_id . "' AND pid=0 ORDER BY element_order ASC");
    while ($row = DB_fetchArray($result)) {
        $label = strip_tags($row['element_label']);
        if ( trim($label) == "") {
            $label = htmlspecialchars($row['element_label']);
        }
        $order_select .= '<option value="' . $row['id'] . '">' . $label . '</option>' . LB;
    }
    $order_select .= '</select>' . LB;

    $target_select = '<select id="urltarget" name="urltarget">' . LB;
    $target_select .= '<option value="">' . $LANG_MB01['same_window'] . '</option>' . LB;
    $target_select .= '<option value="_blank">' . $LANG_MB01['new_window'] . '</option>' . LB;
    $target_select .= '</select>' . LB;

    // build group select

    $rootUser = DB_getItem($_TABLES['group_assignments'],'ug_uid','ug_main_grp_id=1');

    $usergroups = SEC_getUserGroups($rootUser);
    uksort($usergroups, "strnatcasecmp");
    $group_select .= '<select id="group" name="group">' . LB;

    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        if ( $usergroups[key($usergroups)] == 2 ) $group_select .= ' selected="selected"';
        $group_select .= '>' . ucfirst(key($usergroups)) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    $T = new Template($_CONF['path_layout'] . 'admin/menu');
    $T->set_file( 'admin','editelement.thtml');

    $T->set_var(array(
        'form_action'       => $_CONF['site_admin_url'] . '/menu.php',
        'birdseed'          => '<a href="'.$_CONF['site_admin_url'].'/menu.php">'.$LANG_MB01['menu_list'].'</a> :: <a href="'.$_CONF['site_admin_url'].'/menu.php?mode=menu&amp;menu='.$menu_id.'">'.$menu->name.'</a> :: '.$LANG_MB01['create_element'],
        'menuname'          => $menu->name,
//depreciated
        'menuid'            => $menu_id,
//
        'menu'              => $menu_id,
        'type_select'       => $type_select,
        'gl_select'         => $gl_select,
        'parent_select'     => $parent_select,
        'order_select'      => $order_select,
        'plugin_select'     => $plugin_select,
        'sp_select'         => $sp_select,
        'topic_select'      => $topic_select,
        'glfunction_select' => $glfunction_select,
        'group_select'      => $group_select,
        'target_select'     => $target_select,
// new
        'mode'              => 'save',
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

/*
 * Saves a new menu element
 */

function MB_saveNewMenuElement ( ) {
    global $_CONF, $_TABLES, $_GROUPS, $MenuElementAllowedHTML;

    $filter = sanitizer::getInstance();
    $allowedElements = $filter->makeAllowedElements($MenuElementAllowedHTML);
    $filter->setAllowedElements($allowedElements);
    $filter->setPostmode('html');

    // build post vars
    $E['menu_id']           = COM_applyFilter($_POST['menu'],true);
    $E['pid']               = COM_applyFilter($_POST['pid'],true);
    $E['element_label']     = $filter->filterHTML($_POST['menulabel']);
    $E['element_type']      = COM_applyFilter($_POST['menutype'],true);
    $E['element_target']    = isset($_POST['urltarget']) ? COM_applyFilter($_POST['urltarget']) : '';
    $afterElementID         = COM_applyFilter($_POST['menuorder'],true);
    $E['element_active']    = COM_applyFilter($_POST['menuactive'],true);
    $E['element_url']       = isset($_POST['menuurl']) ? trim(COM_applyFilter($_POST['menuurl'])) : '';
    $E['group_id']          = COM_applyFilter($_POST['group'],true);

    $menu = menu::getInstance($E['menu_id']);

    switch($E['element_type']) {
        case 2 :
            $E['element_subtype'] = DB_escapeString(COM_applyFilter($_POST['glfunction']));
            break;
        case 3 :
            $E['element_subtype'] = COM_applyFilter($_POST['gltype'],true);
            break;
        case 4 :
            $E['element_subtype'] = DB_escapeString(COM_applyFilter($_POST['pluginname']));
            break;
        case 5 :
            $E['element_subtype'] = DB_escapeString(COM_applyFilter($_POST['spname']));
            break;
        case 6 :
            $E['element_subtype'] = DB_escapeString(COM_applyFilter($_POST['menuurl']));
            /*
             * check URL if it needs http:// appended...
             */
            if ( trim($E['element_subtype']) != '' ) {
                if(strpos($E['element_subtype'], "http") !== 0 && strpos($E['element_subtype'],"%site") === false && rtrim($E['element_subtype']) != '') {
                    $E['element_subtype'] = 'http://' . $E['element_subtype'];
                }
            }
            break;
        case 7 :
            $E['element_subtype'] = DB_escapeString(COM_applyFilter($_POST['phpfunction']));
            break;
        case 9 :
            $E['element_subtype'] = DB_escapeString(COM_applyFilter($_POST['topicname']));
            break;
        default :
            $E['element_subtype'] = '';
            break;
    }

    // check if URL needs the http:// added

    if ( trim($E['element_url']) != '' ) {
        if ( strpos($E['element_url'],"http") !== 0 && strpos($E['element_url'],"%site") === false && $E['element_url'][0] != '#' && rtrim($E['element_url']) != '' ) {
            $E['element_url'] = 'http://' . $E['element_url'];
        }
    }

    /*
     * Pull some constants..
     */

    $meadmin    = SEC_hasRights('menu.admin');
    $root       = SEC_inGroup('Root');
    $groups     = $_GROUPS;

    /* set element order */
    if ( $afterElementID == 0 ) {
        $aorder = 0;
    } else {
        $aorder = DB_getItem($_TABLES['menu_elements'],'element_order','id=' . $afterElementID);
    }
    $E['element_order'] = $aorder + 1;

    /*
     * build our class
     */

    $element            = new menuElement();
    $element->constructor( $E, $meadmin, $root, $groups,1 );
    $element->id        = $element->createElementID($E['menu_id']);
    $element->saveElement();
    $pid                = $E['pid'];
    $menu_id            = $E['menu_id'];
    $menu->reorderMenu($pid);
    CACHE_remove_instance('menu');
}

/*
 * Edit an existing menu element
 */

function MB_editElement( $menu_id, $mid ) {
    global $_CONF, $_TABLES, $_PLUGINS, $LANG_MB01, $LANG_MB_ADMIN,
           $LANG_MB_TYPES, $LANG_MB_GLTYPES,$LANG_MB_GLFUNCTION;

    $retval = '';

    $menu = menu::getInstance($menu_id);

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/menu.php?mode=menu&amp;menu='.$menu_id,
                  'text' => $LANG_MB01['return_to'] . $menu->name),
            array('url'  => $_CONF['site_admin_url'] .'/menu.php',
                  'text' => $LANG_MB01['menu_list']),
    );
    $retval  .= COM_startBlock($LANG_MB01['menu_builder'].' :: '.$LANG_MB01['edit_element'] .' for ' . $menu->name,'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_MB_ADMIN[5],
                                $_CONF['layout_url'] . '/images/icons/menubuilder.png');

    // build types select

    if ( $menu->menu_elements[$mid]->type == 1 && count($menu->menu_elements[$mid]->children) > 0) {
        $type_select = '<input type="hidden" name="menutype" id="menutype" value="1">';
        $type_select .= '<select id="menutyped" name="menutyped" disabled="disabled">' . LB;
    } else {
        $type_select = '<select id="menutype" name="menutype">' . LB;
    }
    while ( $types = current($LANG_MB_TYPES) ) {
        if ( ($menu->type == 2 || $menu->type == 4 ) && (key($LANG_MB_TYPES) == 1 || key($LANG_MB_TYPES) == 3)){
            // skip it
        } else {
            $type_select .= '<option value="' . key($LANG_MB_TYPES) . '"';
            $type_select .= ($menu->menu_elements[$mid]->type==key($LANG_MB_TYPES) ? ' selected="selected"' : '') . '>' . $types . '</option>' . LB;
        }
        next($LANG_MB_TYPES);
    }
    $type_select .= '</select>' . LB;

    $glfunction_select = '<select id="glfunction" name="glfunction">' . LB;
    while ( $glfunction = current($LANG_MB_GLFUNCTION) ) {
        $glfunction_select .= '<option value="' . key($LANG_MB_GLFUNCTION) . '"';
        $glfunction_select .= ($menu->menu_elements[$mid]->subtype==key($LANG_MB_GLFUNCTION) ? ' selected="selected"' : '') . '>' . $glfunction . '</option>' . LB;
        next($LANG_MB_GLFUNCTION);
    }
    $glfunction_select .= '</select>' . LB;

    $gl_select = '<select id="gltype" name="gltype">' . LB;
    while ( $gltype = current($LANG_MB_GLTYPES) ) {
        $gl_select .= '<option value="' . key($LANG_MB_GLTYPES) . '"';
        $gl_select .= ($menu->menu_elements[$mid]->subtype==key($LANG_MB_GLTYPES) ? ' selected="selected"' : '') . '>' . $gltype . '</option>' . LB;
        next($LANG_MB_GLTYPES);
    }
    $gl_select .= '</select>' . LB;

    $plugin_select = '<select id="pluginname" name="pluginname">' . LB;
    $plugin_menus = _mbPLG_getMenuItems();
    ksort($plugin_menus);
    $found = 0;
    $num_plugins = count($plugin_menus);
    for( $i = 1; $i <= $num_plugins; $i++ ) {
        $plugin_select .= '<option value="' . key($plugin_menus) . '"';

        if ( $menu->menu_elements[$mid]->subtype==key($plugin_menus) ) {
            $plugin_select .= ' selected="selected"';
            $found++;
        }
        $plugin_select .= '>' . ucfirst(key($plugin_menus)) . '</option>' . LB;

        next( $plugin_menus );
    }
    if ( $found == 0 ) {
        $plugin_select .= '<option value="'.$menu->menu_elements[$mid]->subtype.'" selected="selected">'.$LANG_MB01['disabled_plugin'].'</option>'.LB;
    }
    $plugin_select .= '</select>' . LB;

    $sp_select = '<select id="spname" name="spname">' . LB;
    if (in_array('staticpages', $_PLUGINS)) {
        $sql = "SELECT sp_id,sp_title,sp_label FROM {$_TABLES['staticpage']} WHERE sp_status = 1 ORDER BY sp_title";
        $result = DB_query($sql);
        while (list ($sp_id, $sp_title,$sp_label) = DB_fetchArray($result)) {
            if (trim($sp_label) == '') {
                $label = $sp_title;
            } else {
                $label = $sp_label;
            }
            $sp_select .= '<option value="' . $sp_id . '"' . ($menu->menu_elements[$mid]->subtype == $sp_id ? ' selected="selected"' : '') . '>' . $label . '</option>' . LB;
        }
    }
    $sp_select .= '</select>' . LB;

    $topic_select = '<select id="topicname" name="topicname">' . LB;
    $sql = "SELECT tid,topic FROM {$_TABLES['topics']} ORDER BY topic";
    $result = DB_query($sql);
    while (list ($tid, $topic) = DB_fetchArray($result)) {
        $topic_select .= '<option value="' . $tid . '"' . ($menu->menu_elements[$mid]->subtype == $tid ? ' selected="selected"' : '') . '>' . $topic . '</option>' . LB;
    }
    $topic_select .= '</select>' . LB;

    if ( $menu->type == 2 || $menu->type == 4 ) {
        $parent_select = '<input type="hidden" name="pid" id="pid" value="0"/>'.$LANG_MB01['top_level'];
    } else {
        $parent_select = '<select id="pid" name="pid">' . LB;
        $parent_select .= '<option value="0">' . $LANG_MB01['top_level'] . '</option>' . LB;
        $result = DB_query("SELECT id,element_label FROM {$_TABLES['menu_elements']} WHERE menu_id='" . $menu_id . "' AND element_type=1");
        while ($row = DB_fetchArray($result)) {
            if ($row['id'] != $mid ) {
                $parent_select .= '<option value="' . $row['id'] . '" ' . ($menu->menu_elements[$mid]->pid==$row['id'] ? 'selected="selected"' : '') . '>' . $row['element_label'] . '</option>' . LB;
            }
        }
        $parent_select .= '</select>' . LB;
    }

    // build group select

    $rootUser = DB_getItem($_TABLES['group_assignments'],'ug_uid','ug_main_grp_id=1');

    $usergroups = SEC_getUserGroups($rootUser);

    uksort($usergroups, "strnatcasecmp");
    $group_select = '<select id="group" name="group">' . LB;

    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        if ($menu->menu_elements[$mid]->group_id == $usergroups[key($usergroups)] ) {
            $group_select .= ' selected="selected"';
        }
        $group_select .= '>' . ucfirst(key($usergroups)) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    $target_select = '<select id="urltarget" name="urltarget">' . LB;
    $target_select .= '<option value=""' . ($menu->menu_elements[$mid]->target == "" ? ' selected="selected"' : '') . '>' . $LANG_MB01['same_window'] . '</option>' . LB;
    $target_select .= '<option value="_blank"' . ($menu->menu_elements[$mid]->target == "_blank" ? ' selected="selected"' : '') . '>' . $LANG_MB01['new_window'] . '</option>' . LB;
    $target_select .= '</select>' . LB;

    if ( $menu->menu_elements[$mid]->active ) {
        $active_selected = ' checked="checked"';
    } else {
        $active_selected = '';
    }

    $order_select = '<select id="menuorder" name="menuorder">' . LB;
    $order_select .= '<option value="0">' . $LANG_MB01['first_position'] . '</option>' . LB;
    $result = DB_query("SELECT id,element_label,element_order FROM {$_TABLES['menu_elements']} WHERE menu_id='" . $menu_id . "' AND pid=".(int) $menu->menu_elements[$mid]->pid." ORDER BY element_order ASC");
    $order = 10;

    while ($row = DB_fetchArray($result)) {
        if ( $menu->menu_elements[$mid]->order != $order ) {
            $label = strip_tags($row['element_label']);
            if ( trim($label) == "") {
                $label = htmlspecialchars($row['element_label']);
            }
            $test_order = $order + 10;
            $order_select .= '<option value="' . $row['id'] . '"' . ($menu->menu_elements[$mid]->order == $test_order ? ' selected="selected"' : '') . '>' . $label . '</option>' . LB;
        }
        $order += 10;
    }
    $order_select .= '</select>' . LB;

    $T = new Template($_CONF['path_layout'] . 'admin/menu');
    $T->set_file('admin','editelement.thtml');

    $T->set_var(array(
        'form_action'       => $_CONF['site_admin_url'] . '/menu.php',
        'birdseed'          => '<a href="'.$_CONF['site_admin_url'].'/menu.php">'.$LANG_MB01['menu_list'].'</a> :: <a href="'.$_CONF['site_admin_url'].'/menu.php?mode=menu&amp;menu='.$menu_id.'">'.$menu->name.'</a> :: '.$LANG_MB01['edit_element'],
        'menulabel'         => htmlspecialchars($menu->menu_elements[$mid]->label),
        'menuorder'         => $menu->menu_elements[$mid]->order,
        'order_select'      => $order_select,
        'menuurl'           => $menu->menu_elements[$mid]->url,
        'phpfunction'       => $menu->menu_elements[$mid]->subtype,
        'type_select'       => $type_select,
        'gl_select'         => $gl_select,
        'plugin_select'     => $plugin_select,
        'sp_select'         => $sp_select,
        'topic_select'      => $topic_select,
        'glfunction_select' => $glfunction_select,
        'parent_select'     => $parent_select,
        'group_select'      => $group_select,
        'target_select'     => $target_select,
        'active_selected'   => $active_selected,
        'menu'              => $menu_id,
        'mid'               => $mid,
        'mode'              => 'saveedit',
    ));
    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

/*
 * Saves an edited menu element
 */

function MB_saveEditMenuElement ( ) {
    global $_CONF, $_TABLES, $MenuElementAllowedHTML;

    $filter = sanitizer::getInstance();
    $allowedElements = $filter->makeAllowedElements($MenuElementAllowedHTML);
    $filter->setAllowedElements($allowedElements);
    $filter->setPostmode('html');

    $id            = COM_applyFilter($_POST['id'],true);
    $menu_id       = COM_applyFilter($_POST['menu']);
    $pid           = COM_applyFilter($_POST['pid'],true);
    $label         = DB_escapeString($filter->filterHTML($_POST['menulabel']));
    $type          = COM_applyFilter($_POST['menutype'],true);
    $target        = COM_applyFilter($_POST['urltarget']);

    $menu = menu::getInstance($menu_id);

    if ( $type == 0 )
        $type = 1;

    switch($type) {
        case 2 :
            $subtype = COM_applyFilter($_POST['glfunction']);
            break;
        case 3 :
            $subtype = COM_applyFilter($_POST['gltype'],true);
            break;
        case 4 :
            $subtype = COM_applyFilter($_POST['pluginname']);
            break;
        case 5 :
            $subtype = COM_applyFilter($_POST['spname']);
            break;
        case 6 :
            $subtype = COM_applyFilter($_POST['menuurl']);
            if ( strpos($subtype,"http") !== 0 && strpos($subtype,"%site") === false && $subtype[0] != '#' && rtrim($subtype) != '' ) {
                $subtype = 'http://' . $subtype;
            }
            break;
        case 7 :
            $subtype = COM_applyFilter($_POST['phpfunction']);
            break;
        case 9 :
            $subtype = COM_applyFIlter($_POST['topicname']);
            break;
        default :
            $subtype = '';
            break;
    }
    $active     = COM_applyFilter($_POST['menuactive'],true);
    $url = '';
    if ( isset($_POST['menuurl']) && $_POST['menuurl'] != '' ) {
        $url        = trim(DB_escapeString(COM_applyFilter($_POST['menuurl'])));
        if ( strpos($url,"http") !== 0 && strpos($url,"%site") === false && $url[0] != '#' && rtrim($url) != '') {
            $url = 'http://' . $url;
        }
    }
    $group_id   = COM_applyFilter($_POST['group'],true);

    $aid                = COM_applyFilter($_POST['menuorder'],true);
    $aorder             = DB_getItem($_TABLES['menu_elements'],'element_order','id=' . $aid);
    $neworder = $aorder + 1;

    $sql        = "UPDATE {$_TABLES['menu_elements']} SET pid=".(int) $pid.", element_order=".(int) $neworder.", element_label='$label', element_type='$type', element_subtype='$subtype', element_active=$active, element_url='$url', element_target='".DB_escapeString($target)."', group_id=".(int) $group_id." WHERE id=".(int) $id;

    DB_query($sql);
    $menu->reorderMenu($pid);
}

/**
* Enable and Disable Menu
*/
function MB_changeActiveStatusMenuXX ($menu_arr)
{
    global $_CONF, $_TABLES;
    // disable all menus
    $sql = "UPDATE {$_TABLES['menu']} SET menu_active = '0'";
    DB_query($sql);
    if (isset($menu_arr)) {
        foreach ($menu_arr AS $menu => $side) {
            $menu = COM_applyFilter($menu, true);
            // the enable those in the array
            $sql = "UPDATE {$_TABLES['menu']} SET menu_active = '1' WHERE id=".(int) $menu;
            DB_query($sql);
        }
    }
    CACHE_remove_instance('menu');

    return;
}

function MB_deleteMenu($menu_id) {
    global $_CONF, $_TABLES, $_USER;

    MB_deleteChildElements(0,$menu_id);

    DB_query("DELETE FROM {$_TABLES['menu']} WHERE id=".(int) $menu_id);
    DB_query("DELETE FROM {$_TABLES['menu_elements']} WHERE menu_id=".(int) $menu_id);

    CACHE_remove_instance('menu');
    CACHE_remove_instance('css');
}


/**
* Recursivly deletes all elements and child elements
*
*/
function MB_deleteChildElements( $id, $menu_id ){
    global $_CONF, $_TABLES, $_USER;

    $sql = "SELECT * FROM {$_TABLES['menu_elements']} WHERE pid=" . (int) $id . " AND menu_id=" . (int) $menu_id;
    $aResult = DB_query( $sql );
    $rowCount = DB_numRows($aResult);
    for ( $z=0; $z < $rowCount; $z++ ) {
        $row = DB_fetchArray( $aResult );
        MB_deleteChildElements( $row['id'],$menu_id );
    }
    $sql = "DELETE FROM " . $_TABLES['menu_elements'] . " WHERE id=" . (int) $id;
    DB_query( $sql );

    CACHE_remove_instance('menu');
}


function MB_editMenu( $mid ) {
    global $_CONF, $_TABLES, $_ST_CONF, $stMenu, $LANG_MB00, $LANG_MB01, $LANG_MB_ADMIN,
           $LANG_MB_TYPES, $LANG_MB_GLTYPES,$LANG_MB_GLFUNCTION,
           $LANG_MB_MENU_TYPES;

    $retval = '';
    $menu_id = $mid;

    $menu = menu::getInstance($menu_id);

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/menu.php?mode=menu&amp;menu='.$menu_id,
                  'text' => $LANG_MB01['return_to'] . $menu->name),
            array('url'  => $_CONF['site_admin_url'] .'/menu.php',
                  'text' => $LANG_MB01['menu_list']),
    );
    $retval  .= COM_startBlock($LANG_MB01['menu_builder'].' :: '.$LANG_MB01['edit_element'] .' for ' . $menu->name,'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_MB_ADMIN[5],
                                $_CONF['layout_url'] . '/images/icons/menubuilder.png');

    // build menu type select

    $menuTypeSelect = '<select id="menutype" name="menutype">' . LB;
    while ( $types = current($LANG_MB_MENU_TYPES) ) {
        $menuTypeSelect .= '<option value="' . key($LANG_MB_MENU_TYPES) . '"';
        if (key($LANG_MB_MENU_TYPES) == $menu->type) {
            $menuTypeSelect .= ' selected="selected"';
        }
        $menuTypeSelect .= '>' . $types . '</option>' . LB;
        next($LANG_MB_MENU_TYPES);
    }
    $menuTypeSelect .= '</select>' . LB;

    // build group select

    $rootUser = DB_getItem($_TABLES['group_assignments'],'ug_uid','ug_main_grp_id=1');
    $usergroups = SEC_getUserGroups($rootUser);
    uksort($usergroups, "strnatcasecmp");

    $group_select = '<select id="group" name="group">' . LB;
    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        if ( $usergroups[key($usergroups)] == $menu->group_id) {
            $group_select .= ' selected="selected"';
        }
        $group_select .= '>' . ucfirst(key($usergroups)) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    $T = new Template($_CONF['path_layout'] . 'admin/menu');
    $T->set_file( array( 'admin' => 'editmenu.thtml'));

    if ( $mid == 1 || $mid == 2 || $mid == 3 ) {
        $disabled = ' readonly ';
    } else {
        $disabled = '';
    }

    $menu_active_check = ($menu->active == 1  ? ' checked="checked"' : '');

    $T->set_var(array(
        'group_select'      => $group_select,
        'menutype'          => $menu->type,
        'menutype_select'   => $menuTypeSelect,
        'menuactive'        => $menu->active == 1 ? ' checked="checked"' : ' ',
        'form_action'       => $_CONF['site_admin_url'] . '/menu.php',
        'menu_id'           => $mid,
        'menuname'          => $menu->name,
        'enabled'           => $menu_active_check,
        'disabled'          => $disabled,
    ));

    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

function _mb_getListField_menu($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN,$LANG_MB01;

    switch ($fieldname) {
        case 'label':
            $retval = "<span style=\"padding:0 5px;margin-left:" .$A['indent'] . "px;\">" . ($A['type'] == 1 ? '<b>' : '') . $fieldvalue . ($A['type'] == 1 ? '</b>' : '').'</span>';
            break;
        case 'enabled' :
            $retval =  '<input class="menu-element-enabler" type="checkbox" name="enableditem[' . $A['id'] . ']" onclick="submit()" value="1"' . ($fieldvalue == 1 ? ' checked="checked"' : '') . '/>';
            break;
        case 'edit' :
            $retval = '<a href="' . $_CONF['site_admin_url'] . '/menu.php?mode=edit&amp;mid=' . $A['id'] . '&amp;menu=' . $A['menu_id']. '">';
            $retval .= '<img src="' . $_CONF['layout_url'] . '/images/edit.png" alt="' . $LANG_MB01['edit'] . '"/></a>';
            break;
        case 'delete' :
            $retval = '<a href="' . $_CONF['site_admin_url'] . '/menu.php?mode=delete&amp;mid=' . $fieldvalue . '&amp;menuid='.$A['menu_id'].'" onclick="return confirm(\'' . $LANG_MB01['confirm_delete'] . '\');">';
            $retval .= '<img src="' . $_CONF['layout_url'] . '/images/delete.png" alt="' . $LANG_MB01['delete'] . '"/></a>';
            break;
        case 'order' :
            $moveup = '<a href="' . $_CONF['site_admin_url'] . '/menu.php?mode=move&amp;where=up&amp;mid=' . $A['id'] . '&amp;menu=' . $A['menu_id'] . '">';
            $movedown = '<a href="' . $_CONF['site_admin_url'] . '/menu.php?mode=move&amp;where=down&amp;mid=' . $A['id'] . '&amp;menu=' . $A['menu_id'] . '">';
            $retval = $moveup . '<img src="' . $_CONF['layout_url'] . '/images/up.png" alt="' . $LANG_MB01['move_up'] . '"/></a>&nbsp;' . $movedown . '<img src="' . $_CONF['layout_url'] . '/images/down.png" alt="' . $LANG_MB01['move_down'] . '"/></a>';
            break;
        case 'info' :
            $retval = '<a class="'.COM_getToolTipStyle().'" title="' . htmlspecialchars($fieldvalue) . '" href="#"><img src="' . $_CONF['layout_url'] . '/images/info.png" alt=""/></a>';
            break;
        default :
            $retval = $fieldvalue;
            break;
    }
    return $retval;
}

function _mb_getListField_menulist($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG_MB01, $LANG_MB_ADMIN, $LANG_ADMIN,$LANG_MB_MENU_TYPES;

    $retval = '';

    switch ($fieldname) {
        case 'menu_name':
            $elementDetails = $A['menu_name'] . '::';
            $elementDetails .= '<b>' . $LANG_MB01['type'] . ':</b><br/>' . $LANG_MB_MENU_TYPES[$A['menu_type']] . '<br/>';
            $retval = '<span style="cursor:pointer;" class="'.COM_getToolTipStyle().'" title="' . $elementDetails . '">'.$A['menu_name'].'</span>';
            break;
        case 'copy' :
            $retval = '<a href="'.$_CONF['site_admin_url'].'/menu.php?mode=clone&amp;id='.$fieldvalue.'">'
                    . '<img src="'.$_CONF['layout_url'].'/images/copy.png" alt="'.$LANG_MB01['clone'].'" />';
            break;
        case 'active' :
            $retval = '<input class="menu-enabler" type="checkbox" name="enabledmenu[' . $A['menu_id'] . ']" onclick="submit()" value="1"' . ($A['active'] == 1 ? ' checked="checked"' : '') . '/>';
            break;
        case 'elements' :
            $retval = '<a href="'.$_CONF['site_admin_url'].'/menu.php?mode=menu&amp;menu='.$A['menu_id'].'">'
            . '<img src="'.$_CONF['layout_url'].'/images/edit.png" alt="'.$LANG_MB01['edit'].'">';
            break;
        case 'delete' :
            if ( $A['menu_id'] != 1 && $A['menu_id'] != 2 && $A['menu_id'] != 3 ) {
                $retval = '<a href="' . $_CONF['site_admin_url'] . '/menu.php?mode=deletemenu&amp;id=' . $A['menu_id'] . '" onclick="return confirm(\'' . $LANG_MB01['confirm_delete'] . '\');"><img src="' . $_CONF['layout_url'] . '/images/delete.png" alt="' . $LANG_MB01['delete'] . '"' . '/></a>';
            }
            break;
        case 'options' :
            $retval = '<a href="'.$_CONF['site_admin_url'].'/menu.php?mode=editmenu&amp;menuid='.$A['menu_id'].'">'
            . '<img src="'.$_CONF['layout_url'].'/images/gear.png" height="16" width="16" alt="'.$LANG_MB01['options'].'"/></a>';
            break;
        default :
            $retval = $fieldvalue;
            break;
    }
    return $retval;
}

/*
 * Main processing loop
 */
$msg = COM_getMessage();

if ( isset($_GET['mode']) ) {
    $mode = COM_applyFilter($_GET['mode']);
} else if ( isset($_POST['mode']) ) {
    $mode = COM_applyFilter($_POST['mode']);
} else {
    $mode = '';
}
if ( isset($_REQUEST['menumid']) ) {
    $menu_id = COM_applyFilter($_REQUEST['menumid'],true);
} else {
    $menu_id = 0;
}
if ( isset($_REQUEST['menu'] ) ) {
    $menu_id = COM_applyFilter($_REQUEST['menu'],true);
}
if ( isset($_REQUEST['mid']) ) {
    $mid = COM_applyFilter($_REQUEST['mid'],true);
}

if ( (isset($_POST['execute']) || $mode != '') && !isset($_POST['cancel']) && !isset($_POST['defaults'])) {
    switch ( $mode ) {
        case 'clone' :
            $menu = COM_applyFilter($_GET['id'],true);
            $content = MB_cloneMenu($menu);
            break;
        case 'menu' :
            // display the tree
            $content = MB_displayTree( $menu_id );
            break;
        case 'new' :
            $menu = COM_applyFilter($_GET['menuid'],true);
            $content = MB_createElement ( $menu );
            break;
        case 'move' :
            // do something with the direction
            $direction = COM_applyFilter($_GET['where']);
            $mid       = COM_applyFilter($_GET['mid'],true);
            $menu_id   = COM_applyFilter($_GET['menu'],true);
            MB_moveElement( $menu_id, $mid, $direction );
            echo COM_refresh($_CONF['site_admin_url'] . '/menu.php?mode=menu&amp;menu=' . $menu_id);
            exit;
            break;
        case 'edit' :
            // call the editor
            $mid       = COM_applyFilter($_GET['mid'],true);
            $menu_id   = COM_applyFilter($_GET['menu'],true);
            $content   = MB_editElement( $menu_id, $mid );
            $currentSelect = $LANG_MB01['menu_builder'];
            break;
        case 'saveedit' :
            MB_saveEditMenuElement();
            CACHE_remove_instance('menu');
            echo COM_refresh($_CONF['site_admin_url'] . '/menu.php?mode=menu&amp;menu=' . $menu_id);
            exit;
            break;
        case 'save' :
            // save the new or edited element
            $menu_id = COM_applyFilter($_POST['menu'],true);
            MB_saveNewMenuElement();
            CACHE_remove_instance('menu');
            echo COM_refresh($_CONF['site_admin_url'] . '/menu.php?mode=menu&amp;menu=' . $menu_id);
            exit;
            break;
        case 'savenewmenu' :
            $rc = MB_saveNewMenu();
            if ( $rc != '' ) {
                $content = COM_showMessageText($rc, '', true,'error');
                $content .= MB_createMenu();
            } else {
                $content = MB_displayMenuList( );
            }
            break;
        case 'saveclonemenu' :
            MB_saveCloneMenu();
            $content = MB_displayMenuList( );
            break;
        case 'saveeditmenu' :
            $rc = MB_saveEditMenu();
            if ( $rc != '' ) {
                $content = COM_showMessageText($rc, '', true,'error');
                $menu_id = COM_applyFilter($_POST['menu_id'],true);
                $content .= MB_editMenu( $menu_id );
            } else {
                $content = MB_displayMenuList( );
            }
            break;
        case 'editmenu' :
            $menu_id = COM_applyFilter($_GET['menuid'],true);
            $content = MB_editMenu( $menu_id );
            break;
        case 'activate' :
            MB_changeActiveStatusElement ($_POST['enableditem']);
            $content = MB_displayTree( $menu_id );
            $currentSelect = $LANG_MB01['menu_builder'];
            break;
        case 'menuactivate' :
            MB_changeActiveStatusMenu ($_POST['enabledmenu']);
            $content = MB_displayMenuList( );
            $currentSelect = $LANG_MB01['menu_builder'];
            break;
        case 'delete' :
            // delete the element
            $id        = COM_applyFilter($_GET['mid'],true);
            $menu_id   = COM_applyFilter($_GET['menuid'],true);

            $menu = menu::getInstance( $menu_id );

            MB_deleteChildElements( $id, $menu_id );

            $menu->reorderMenu(0);
            echo COM_refresh($_CONF['site_admin_url'] . '/menu.php?mode=menu&amp;menu=' . $menu_id);
            exit;
            break;
        case 'deletemenu' :
            // delete the element
            $menu_id   = COM_applyFilter($_GET['id'],true);
            MB_deleteMenu($menu_id);
            echo COM_refresh($_CONF['site_admin_url'] . '/menu.php');
            exit;
            break;
        case 'newmenu' :
            $content = MB_createMenu( );
            $currentSelect = $LANG_MB01['menu_builder'];
            break;
        default :
            $content = MB_displayMenuList( );
            break;
    }
} else if ( isset($_POST['cancel']) && isset($_POST['menu']) ) {
    $menu_id = COM_applyFilter($_POST['menu'],true);
    $content = MB_displayTree( $menu_id );
} else {
    // display the tree
    $content = MB_displayMenuList( );
}

$display = COM_siteHeader();
$display .= $content;
$display .= COM_siteFooter();
echo $display;
exit;
?>