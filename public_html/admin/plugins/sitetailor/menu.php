<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | menu.php                                                                 |
// |                                                                          |
// | Site Tailor menu editor                                                  |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
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

require_once '../../../lib-common.php';
require_once $_CONF['path'] . 'system/lib-admin.php';

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('sitetailor.admin')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Site Tailor Administration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_ST00['access_denied']);
    $display .= $LANG_ST00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

/*
 * Displays a list of all menus
 */

function ST_displayMenuList( ) {
    global $_CONF, $LANG_ST00, $LANG_ST01, $LANG_ST_ADMIN, $LANG_ADMIN,$LANG_ST_MENU_TYPES, $_ST_CONF, $stMenu;

    $retval = '';

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/plugins/sitetailor/menu.php?mode=newmenu',
                  'text' => $LANG_ST01['add_newmenu']),
            array('url'  => $_CONF['site_admin_url'],
                  'text' => $LANG_ADMIN['admin_home']),
    );
    $retval  .= COM_startBlock($LANG_ST01['menu_builder'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_ST_ADMIN[1],
                                $_CONF['site_admin_url'] . '/plugins/sitetailor/images/sitetailor-menubuilder.png');

    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file (array ('admin' => 'menulist.thtml'));
    $T->set_block('admin', 'menurow', 'mrow');
    $rowCounter = 0;
    if ( is_array($stMenu) ) {
        foreach ($stMenu AS $menu) {
            $id = $menu['menu_id'];
            $T->set_var('menu_id',$menu['menu_id']);
            $T->set_var('menu_name',$menu['menu_name']);
            $T->set_var('menuactive','<input type="checkbox" name="enabledmenu[' . $menu['menu_id'] . ']" onclick="submit()" value="1"' . ($menu['active'] == 1 ? ' checked="checked"' : '') . XHTML . '>');
            if ( $menu['menu_id'] != 1 ) {
                $T->set_var('delete_menu','<a href="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php?mode=deletemenu&amp;id=' . $menu['menu_id'] . '" onclick="return confirm(\'' . $LANG_ST01['confirm_delete'] . '\');"><img src="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/images/delete.png" alt="' . $LANG_ST01['delete'] . '"' . XHTML . '></a>');
            }
            $T->set_var('menu_tree',isset($stMenu[$id]['elements']) ? $stMenu[$id]['elements'][0]->editTree(0,2) : '');
            $elementDetails = $menu['menu_name'] . '::';
            $elementDetails .= '<b>' . $LANG_ST01['type'] . ':</b><br />' . $LANG_ST_MENU_TYPES[$menu['menu_type']] . '<br' . XHTML . '>';
            $info       = '<a class="gl_mootip" title="' . $elementDetails . '" href="#">'.$menu['menu_name'].'</a>';
            $T->set_var('info',$info);
            $T->set_var('rowclass',($rowCounter % 2)+1);
            $T->parse('mrow','menurow',true);
            $rowCounter++;
        }
    }
    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'status_msg'        => $statusMsg,
        'lang_admin'        => $LANG_ST00['admin'],
        'version'           => $_ST_CONF['version'],
        'menu_list'         => $menulist,
        'menuid'            => $menu_id,
        'menuactive'        => $stMenu[$menu_id]['active'] == 1 ? ' checked="checked"' : ' ',
        'xhtml'             => XHTML,
    ));
    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

/*
 * Create a new menu
 */

function ST_createMenu( ) {
    global $_CONF, $_TABLES, $LANG_ST00, $LANG_ST01, $LANG_ST_ADMIN, $_ST_CONF,
           $LANG_ST_MENU_TYPES, $LANG_ADMIN, $stMenu;

    $retval = '';

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/plugins/sitetailor/menu.php',
                  'text' => $LANG_ST01['menu_list']),
            array('url'  => $_CONF['site_admin_url'],
                  'text' => $LANG_ADMIN['admin_home']),
    );
    $retval  .= COM_startBlock($LANG_ST01['menu_builder'].' :: '.$LANG_ST01['add_newmenu'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_ST_ADMIN[2],
                                $_CONF['site_admin_url'] . '/plugins/sitetailor/images/sitetailor-menubuilder.png');


    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file (array ('admin' => 'createmenu.thtml'));

    // build menu type select

    $menuTypeSelect = '<select id="menutype" name="menutype">' . LB;
    while ( $types = current($LANG_ST_MENU_TYPES) ) {
        $menuTypeSelect .= '<option value="' . key($LANG_ST_MENU_TYPES) . '"';
        $menuTypeSelect .= '>' . $types . '</option>' . LB;
        next($LANG_ST_MENU_TYPES);
    }
    $menuTypeSelect .= '</select>' . LB;

    // build group select

    $rootUser = DB_getItem($_TABLES['group_assignments'],'ug_uid','ug_main_grp_id=1');
    $usergroups = SEC_getUserGroups($rootUser);
    $usergroups[$LANG_ST01['non-logged-in']] = 998;
    ksort($usergroups);
    $group_select .= '<select id="group" name="group">' . LB;
    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        $group_select .= '>' . key($usergroups) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'form_action'       => $_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php',
        'birdseed'          => '<a href="'.$_CONF['site_admin_url'].'/plugins/sitetailor/menu.php">'.$LANG_ST01['menu_list'].'</a> :: '.$LANG_ST01['add_newmenu'],
        'status_msg'        => $statusMsg,
        'lang_admin'        => $LANG_ST00['admin'],
        'version'           => $_ST_CONF['version'],
        'menu_list'         => $menulist,
        'menuid'            => $menu_id,
        'menuactive'        => $stMenu[$menu_id]['active'] == 1 ? ' checked="checked"' : ' ',
        'menutype_select'   => $menuTypeSelect,
        'group_select'      => $group_select,
        'xhtml'             => XHTML,
    ));
    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

/*
 * Saves a new menu element
 */

function ST_saveNewMenu( ) {
    global $_CONF, $_TABLES, $LANG_ST00, $_ST_CONF, $stMenu, $_GROUPS;

    $menuname   = addslashes(COM_applyFilter($_POST['menuname']));
    $menutype   = COM_applyFilter($_POST['menutype'],true);
    $menuactive = COM_applyFilter($_POST['menuactive'],true);
    $menugroup  = COM_applyFilter($_POST['group'],true);

    $sqlFieldList  = 'menu_name,menu_type,menu_active,group_id';
    $sqlDataValues = "'$menuname',$menutype,$menuactive,$menugroup";
    DB_save($_TABLES['st_menus'], $sqlFieldList, $sqlDataValues);

    $menu_id = DB_insertId();

    switch ( $menutype ) {
        case 1:
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_bg_color','#151515'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_bg_color','#3667c0'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_text_color','#CCCCCC'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_text_color','#FFFFFF'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_text_color','#FFFFFF'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_text_color','#679EF1'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_background_color','#151515'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_bg_color','#333333'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_highlight_color','#151515'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_shadow_color','#151515'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'use_images','1'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_bg_filename','menu_bg.gif'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_hover_filename','menu_hover_bg.gif'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_parent_filename','menu_parent.png'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_alignment','1'");
            break;
        case 2:
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_bg_color','#000000'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_bg_color','#000000'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_text_color','#3677C0'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_text_color','#679EF1'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_text_color','#FFFFFF'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_text_color','#679EF1'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_background_color','#151515'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_bg_color','#333333'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_highlight_color','#151515'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_shadow_color','#151515'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'use_images','1'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_bg_filename','menu_bg.gif'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_hover_filename','menu_hover_bg.gif'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_parent_filename','menu_parent.png'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_alignment','1'");
            break;
        case 3:
        case 4:
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_bg_color','#DDDDDD'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_bg_color','#BBBBBB'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_text_color','#0000FF'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_text_color','#FFFFFF'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_text_color','#0000FF'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_text_color','#F7FF00'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_background_color','#DDDDDD'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_bg_color','#BBBBBB'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_highlight_color','#999999'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_shadow_color','#999999'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'use_images','1'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_bg_filename','menu_bg.gif'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_hover_filename','menu_hover_bg.gif'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_parent_filename','vmenu_parent.gif'");
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_alignment','1'");
            break;
    }


    CACHE_remove_instance('stmenu');
    CACHE_remove_instance('css');
    $randID = rand();
    DB_save($_TABLES['vars'],'name,value',"'cacheid',$randID");
    st_initMenu(true);
}

/*
 * Displays a list of all menu elements for the given menu
 */

function ST_displayTree( $menu_id ) {
    global $_CONF, $LANG_ST00, $LANG_ST01, $LANG_ST_ADMIN, $LANG_ADMIN, $_ST_CONF, $stMenu;

    $retval = '';

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/plugins/sitetailor/menu.php?mode=new&amp;menuid='.$menu_id,
                  'text' => $LANG_ST01['create_element']),
            array('url'  => $_CONF['site_admin_url'] .'/plugins/sitetailor/menu.php',
                  'text' => $LANG_ST01['menu_list']),
    );
    $retval  .= COM_startBlock($LANG_ST01['menu_builder'].' :: '.$stMenu[$menu_id]['menu_name'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_ST_ADMIN[3],
                                $_CONF['site_admin_url'] . '/plugins/sitetailor/images/sitetailor-menubuilder.png');



    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file (array ('admin' => 'menutree.thtml'));

    $menu_select = '<form name="jumpbox" id="jumpbox" action="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php" method="get" style="margin:0;padding:0"><div>';
    $menu_select .= '<input type="hidden" name="mode" id="mode" value="menu"'.XHTML.'>' . LB;
    $menu_select .= '<strong>Menu</strong>' . ':&nbsp;<select name="menu" onchange="submit()">';
    foreach ($stMenu AS $menu) {
        $menu_select .= '<option value="' . $menu['menu_id'].'"' . ($menu['menu_id'] == $menu_id ? ' selected="selected"' : '') . '>' . $menu['menu_name'] .'</option>' . LB;
    }
    $menu_select .= '</select>';
    $menu_select .= '&nbsp;<input type="submit" value="' . 'go' . '"' . XHTML . '>';
    $menu_select .= '</div></form>';

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'birdseed'          => '<a href="'.$_CONF['site_admin_url'].'/plugins/sitetailor/menu.php">'.$LANG_ST01['menu_list'].'</a> :: '.$stMenu[$menu_id]['menu_name'].' :: '.$LANG_ST01['elements'],
        'status_msg'        => $statusMsg,
        'lang_admin'        => $LANG_ST00['admin'],
        'version'           => $_ST_CONF['version'],
        'menu_tree'         => $stMenu[$menu_id]['elements'][0]->editTree(0,2),
        'menuid'            => $menu_id,
        'menuname'          => $stMenu[$menu_id]['menu_name'],
        'menu_select'       => $menu_select,
        'menuactive'        => $stMenu[$menu_id]['active'] == 1 ? ' checked="checked"' : ' ',
        'xhtml'             => XHTML,
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

/*
 * Moves a menu element up or down
 */
function ST_moveElement( $menu_id, $mid, $direction ) {
    global $_CONF, $_TABLES, $_ST_CONF, $stMenu;

    switch ( $direction ) {
        case 'up' :
            $neworder = $stMenu[$menu_id]['elements'][$mid]->order - 11;
            DB_query("UPDATE {$_TABLES['st_menu_elements']} SET element_order=" . $neworder . " WHERE menu_id=".$menu_id." AND id=" . $mid);
            break;
        case 'down' :
            $neworder = $stMenu[$menu_id]['elements'][$mid]->order + 11;
            DB_query("UPDATE {$_TABLES['st_menu_elements']} SET element_order=" . $neworder . " WHERE menu_id=".$menu_id." AND id=" . $mid);
            break;
    }
    $pid = $stMenu[$menu_id]['elements'][$mid]->pid;

    $stMenu[$menu_id]['elements'][$pid]->reorderMenu();
    CACHE_remove_instance('stmenu');

    return;
}

/*
 * Creates a new menu element
 */

function ST_createElement ( $menu_id ) {
    global $_CONF, $_TABLES, $_ST_CONF, $stMenu, $LANG_ST00, $LANG_ST01, $LANG_ST_ADMIN, $LANG_ST_TYPES,
           $LANG_ST_GLTYPES, $LANG_ST_GLFUNCTION;

    $retval = '';

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/plugins/sitetailor/menu.php?mode=menu&amp;id='.$menu_id,
                  'text' => 'Back to ' . $stMenu[$menu_id]['menu_name']),
            array('url'  => $_CONF['site_admin_url'] .'/plugins/sitetailor/menu.php',
                  'text' => $LANG_ST01['menu_list']),
    );
    $retval  .= COM_startBlock($LANG_ST01['menu_builder'].' :: '.$LANG_ST01['create_element'] .' for ' . $stMenu[$menu_id]['menu_name'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_ST_ADMIN[4],
                                $_CONF['site_admin_url'] . '/plugins/sitetailor/images/sitetailor-menubuilder.png');

    // build types select

    $spCount = 0;
    $sp_select = '<select id="spname" name="spname">' . LB;
    $sql = "SELECT sp_id,sp_title,sp_label FROM {$_TABLES['staticpage']} ORDER BY sp_title";
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
    $sp_select .= '</select>' . LB;

    if ( $spCount == 0 ) {
        $sp_select = '';
    }

    $type_select = '<select id="menutype" name="menutype">' . LB;
    while ( $types = current($LANG_ST_TYPES) ) {
        if ( $spCount == 0 && key($LANG_ST_TYPES) == 5 ) {
            // skip it
        } else {
            if ( ($stMenu[$menu_id]['menu_type'] == 2 || $stMenu[$menu_id]['menu_type'] == 4 ) && (key($LANG_ST_TYPES) == 1 || key($LANG_ST_TYPES) == 3)){
                // skip it
            } else {
                $type_select .= '<option value="' . key($LANG_ST_TYPES) . '"';
                $type_select .= '>' . $types . '</option>' . LB;
            }
        }
        next($LANG_ST_TYPES);
    }
    $type_select .= '</select>' . LB;

    $gl_select = '<select id="gltype" name="gltype">' . LB;
    while ( $gltype = current($LANG_ST_GLTYPES) ) {
        $gl_select .= '<option value="' . key($LANG_ST_GLTYPES) . '"';
        $gl_select .= '>' . $gltype . '</option>' . LB;
        next($LANG_ST_GLTYPES);
    }
    $gl_select .= '</select>' . LB;

    $plugin_select = '<select id="pluginname" name="pluginname">' . LB;
    $plugin_menus = _stPLG_getMenuItems(); // PLG_getMenuItems();

    $num_plugins = count($plugin_menus);
    for( $i = 1; $i <= $num_plugins; $i++ ) {
        $plugin_select .= '<option value="' . key($plugin_menus) . '">' . key($plugin_menus) . '</option>' . LB;
        next( $plugin_menus );
    }
    $plugin_select .= '</select>' . LB;

    $glfunction_select = '<select id="glfunction" name="glfunction">' . LB;
    while ( $glfunction = current($LANG_ST_GLFUNCTION) ) {
        $glfunction_select .= '<option value="' . key($LANG_ST_GLFUNCTION) . '"';
        $glfunction_select .= '>' . $glfunction . '</option>' . LB;
        next($LANG_ST_GLFUNCTION);
    }
    $glfunction_select .= '</select>' . LB;

    if ( $stMenu[$menu_id]['menu_type'] == 2 || $stMenu[$menu_id]['menu_type'] == 4 ) {
        $parent_select = '<input type="hidden" name="pid" id="pid" value="0"'.XHTML.'>'.$LANG_ST01['top_level'];
    } else {
        $parent_select = '<select name="pid" id="pid">' . LB;
        $parent_select .= '<option value="0">' . $LANG_ST01['top_level'] . '</option>' . LB;
        $result = DB_query("SELECT id,element_label FROM {$_TABLES['st_menu_elements']} WHERE menu_id='" . $menu_id . "' AND element_type=1");
        while ($row = DB_fetchArray($result)) {
            $parent_select .= '<option value="' . $row['id'] . '">' . $row['element_label'] . '</option>' . LB;
        }
        $parent_select .= '</select>' . LB;
    }

    $order_select = '<select id="menuorder" name="menuorder">' . LB;
    $order_select .= '<option value="0">' . $LANG_ST01['first_position'] . '</option>' . LB;

    $result = DB_query("SELECT id,element_label,element_order FROM {$_TABLES['st_menu_elements']} WHERE menu_id='" . $menu_id . "' AND pid=0 ORDER BY element_order ASC");
    while ($row = DB_fetchArray($result)) {
        $order_select .= '<option value="' . $row['id'] . '">' . $row['element_label'] . '</option>' . LB;
    }
    $order_select .= '</select>' . LB;

    // build group select

    $rootUser = DB_getItem($_TABLES['group_assignments'],'ug_uid','ug_main_grp_id=1');

    $usergroups = SEC_getUserGroups($rootUser);
    $usergroups[$LANG_ST01['non-logged-in']] = 998;
    ksort($usergroups);
    $group_select .= '<select id="group" name="group">' . LB;

    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        $group_select .= '>' . key($usergroups) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file( array( 'admin' => 'createelement.thtml'));

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'form_action'       => $_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php',
        'birdseed'          => '<a href="'.$_CONF['site_admin_url'].'/plugins/sitetailor/menu.php">'.$LANG_ST01['menu_list'].'</a> :: <a href="'.$_CONF['site_admin_url'].'/plugins/sitetailor/menu.php?mode=menu&amp;id='.$menu_id.'">'.$stMenu[$menu_id]['menu_name'].'</a> :: '.$LANG_ST01['create_elements'],
        'menuname'          => $menu_name,
        'menuid'            => $menu_id,
        'type_select'       => $type_select,
        'gl_select'         => $gl_select,
        'parent_select'     => $parent_select,
        'order_select'      => $order_select,
        'plugin_select'     => $plugin_select,
        'sp_select'         => $sp_select,
        'glfunction_select' => $glfunction_select,
        'group_select'      => $group_select,
        'xhtml'             => XHTML,
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

/*
 * Saves a new menu element
 */

function ST_saveNewMenuElement ( ) {
    global $_CONF, $_TABLES, $LANG_ST00, $_ST_CONF, $stMenu,$_GROUPS;

    // build post vars
    $E['menu_id']           = COM_applyFilter($_POST['menuid'],true);
    $E['pid']               = COM_applyFilter($_POST['pid'],true);
    $E['element_label']     = htmlspecialchars(strip_tags(COM_checkWords($_POST['menulabel'])));
    $E['element_type']      = COM_applyFilter($_POST['menutype'],true);
    $E['element_target']    = COM_applyFilter($_POST['urltarget']);
    $afterElementID         = COM_applyFilter($_POST['menuorder'],true);
    $E['element_active']    = COM_applyFilter($_POST['menuactive'],true);
    $E['element_url']       = trim(COM_applyFilter($_POST['menuurl']));
    $E['group_id']          = COM_applyFilter($_POST['group'],true);

    switch($E['element_type']) {
        case 2 :
            $E['element_subtype'] = COM_applyFilter($_POST['glfunction']);
            break;
        case 3 :
            $E['element_subtype'] = COM_applyFilter($_POST['gltype'],true);
            break;
        case 4 :
            $E['element_subtype'] = COM_applyFilter($_POST['pluginname']);
            break;
        case 5 :
            $E['element_subtype'] = COM_applyFilter($_POST['spname']);
            break;
        case 6 :
            $E['element_subtype'] = COM_applyFilter($_POST['menuurl']);
            /*
             * check URL if it needs http:// appended...
             */
            if ( trim($E['element_subtype']) != '' ) {
                if(strpos($E['element_subtype'], "http") !== 0 && strpos($E['element_subtype'],"%site") === false) {
                    $E['element_subtype'] = 'http://' . $E['element_subtype'];
                }
            }
            break;
        case 7 :
            $E['element_subtype'] = COM_applyFilter($_POST['phpfunction']);
            break;
        default :
            $E['element_subtype'] = '';
            break;
    }

    // check if URL needs the http:// added

    if ( trim($E['element_url']) != '' ) {
        if ( strpos($E['element_url'],"http") !== 0 && strpos($E['element_url'],"%site") === false && $E['element_url'][0] != '#') {
            $E['element_url'] = 'http://' . $E['element_url'];
        }
    }

    /*
     * Pull some constants..
     */

    $meadmin    = SEC_hasRights('sitetailor.admin');
    $root       = SEC_inGroup('Root');
    $groups     = $_GROUPS;

    /* set element order */
    if ( $afterElementID == 0 ) {
        $aorder = 0;
    } else {
        $aorder             = DB_getItem($_TABLES['st_menu_elements'],'element_order','id=' . $afterElementID);
    }
    $E['element_order'] = $aorder + 1;

    /*
     * build our class
     */

    $element            = new mbElement();
    $element->constructor( $E, $meadmin, $root, $groups );
    $element->id        = $element->createElementID($E['menu_id']);
    $element->saveElement();
    $pid                = $E['pid'];
    $menu_id            = $E['menu_id'];
    $stMenu[$menu_id]['elements'][$pid]->reorderMenu();
    CACHE_remove_instance('stmenu');
}

/*
 * Edit an existing menu element
 */

function ST_editElement( $menu_id, $mid ) {
    global $_CONF, $_TABLES, $_ST_CONF, $stMenu,$stMenu,$LANG_ST00, $LANG_ST01, $LANG_ST_ADMIN,
           $LANG_ST_TYPES, $LANG_ST_GLTYPES,$LANG_ST_GLFUNCTION;

    $retval = '';

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/plugins/sitetailor/menu.php?mode=menu&amp;id='.$menu_id,
                  'text' => 'Back to ' . $stMenu[$menu_id]['menu_name']),
            array('url'  => $_CONF['site_admin_url'] .'/plugins/sitetailor/menu.php',
                  'text' => $LANG_ST01['menu_list']),
    );
    $retval  .= COM_startBlock($LANG_ST01['menu_builder'].' :: '.$LANG_ST01['edit_element'] .' for ' . $stMenu[$menu_id]['menu_name'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_ST_ADMIN[5],
                                $_CONF['site_admin_url'] . '/plugins/sitetailor/images/sitetailor-menubuilder.png');


    // build types select

    if ( $stMenu[$menu_id]['elements'][$mid]->type == 1 ) {
        $type_select = '<select id="menutype" name="menutype" disabled="disabled">' . LB;
    } else {
        $type_select = '<select id="menutype" name="menutype">' . LB;
    }
    while ( $types = current($LANG_ST_TYPES) ) {
        if ( ($stMenu[$menu_id]['menu_type'] == 2 || $stMenu[$menu_id]['menu_type'] == 4 ) && (key($LANG_ST_TYPES) == 1 || key($LANG_ST_TYPES) == 3)){
            // skip it
        } else {
            $type_select .= '<option value="' . key($LANG_ST_TYPES) . '"';
            $type_select .= ($stMenu[$menu_id]['elements'][$mid]->type==key($LANG_ST_TYPES) ? ' selected="selected"' : '') . '>' . $types . '</option>' . LB;
        }
        next($LANG_ST_TYPES);
    }
    $type_select .= '</select>' . LB;


    $glfunction_select = '<select id="glfunction" name="glfunction">' . LB;
    while ( $glfunction = current($LANG_ST_GLFUNCTION) ) {
        $glfunction_select .= '<option value="' . key($LANG_ST_GLFUNCTION) . '"';
        $glfunction_select .= ($stMenu[$menu_id]['elements'][$mid]->subtype==key($LANG_ST_GLFUNCTION) ? ' selected="selected"' : '') . '>' . $glfunction . '</option>' . LB;
        next($LANG_ST_GLFUNCTION);
    }
    $glfunction_select .= '</select>' . LB;

    $gl_select = '<select id="gltype" name="gltype">' . LB;
    while ( $gltype = current($LANG_ST_GLTYPES) ) {
        $gl_select .= '<option value="' . key($LANG_ST_GLTYPES) . '"';
        $gl_select .= ($stMenu[$menu_id]['elements'][$mid]->subtype==key($LANG_ST_GLTYPES) ? ' selected="selected"' : '') . '>' . $gltype . '</option>' . LB;
        next($LANG_ST_GLTYPES);
    }
    $gl_select .= '</select>' . LB;

    $plugin_select = '<select id="pluginname" name="pluginname">' . LB;
    $plugin_menus = _stPLG_getMenuItems(); // PLG_getMenuItems();

    $num_plugins = count($plugin_menus);
    for( $i = 1; $i <= $num_plugins; $i++ )
    {
        $plugin_select .= '<option value="' . key($plugin_menus) . '"' . ($stMenu[$menu_id]['elements'][$mid]->subtype==key($plugin_menus) ? ' selected="selected"' : '') . '>' . key($plugin_menus) . '</option>' . LB;
        next( $plugin_menus );
    }
    $plugin_select .= '</select>' . LB;

    $sp_select = '<select id="spname" name="spname">' . LB;
    $sql = "SELECT sp_id,sp_title,sp_label FROM {$_TABLES['staticpage']} ORDER BY sp_title";
    $result = DB_query($sql);
    while (list ($sp_id, $sp_title,$sp_label) = DB_fetchArray($result)) {
        if (trim($sp_label) == '') {
            $label = $sp_title;
        } else {
            $label = $sp_label;
        }
        $sp_select .= '<option value="' . $sp_id . '"' . ($stMenu[$menu_id]['elements'][$mid]->subtype == $sp_id ? ' selected="selected"' : '') . '>' . $label . '</option>' . LB;
    }
    $sp_select .= '</select>' . LB;

    if ( $stMenu[$menu_id]['menu_type'] == 2 || $stMenu[$menu_id]['menu_type'] == 4 ) {
        $parent_select = '<input type="hidden" name="pid" id="pid" value="0"'.XHTML.'>'.$LANG_ST01['top_level'];
    } else {
        $parent_select = '<select id="pid" name="pid">' . LB;
        $parent_select .= '<option value="0">' . $LANG_ST01['top_level'] . '</option>' . LB;
        $result = DB_query("SELECT id,element_label FROM {$_TABLES['st_menu_elements']} WHERE menu_id='" . $menu_id . "' AND element_type=1");
        while ($row = DB_fetchArray($result)) {
            $parent_select .= '<option value="' . $row['id'] . '" ' . ($stMenu[$menu_id]['elements'][$mid]->pid==$row['id'] ? 'selected="selected"' : '') . '>' . $row['element_label'] . '</option>' . LB;
        }
        $parent_select .= '</select>' . LB;
    }

    // build group select

    $rootUser = DB_getItem($_TABLES['group_assignments'],'ug_uid','ug_main_grp_id=1');

    $usergroups = SEC_getUserGroups($rootUser);
    $usergroups[$LANG_ST01['non-logged-in']] = 998;
    ksort($usergroups);
    $group_select .= '<select id="group" name="group">' . LB;

    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        if ($stMenu[$menu_id]['elements'][$mid]->group_id==$usergroups[key($usergroups)] ) {
            $group_select .= ' selected="selected"';
        }
        $group_select .= '>' . key($usergroups) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    $target_select = '<select id="urltarget" name="urltarget">' . LB;
    $target_select .= '<option value=""' . ($stMenu[$menu_id]['elements'][$mid]->target == "" ? ' selected="selected"' : '') . '>' . $LANG_ST01['same_window'] . '</option>' . LB;
    $target_select .= '<option value="_blank"' . ($stMenu[$menu_id]['elements'][$mid]->target == "_blank" ? ' selected="selected"' : '') . '>' . $LANG_ST01['new_window'] . '</option>' . LB;
    $target_select .= '</select>' . LB;

    if ( $stMenu[$menu_id]['elements'][$mid]->active ) {
        $active_selected = ' checked="checked"';
    } else {
        $active_selected = '';
    }

    $order_select = '<select id="menuorder" name="menuorder">' . LB;
    $order_select .= '<option value="0">' . $LANG_ST01['first_position'] . '</option>' . LB;
    $result = DB_query("SELECT id,element_label,element_order FROM {$_TABLES['st_menu_elements']} WHERE menu_id='" . $menu_id . "' AND pid=0 ORDER BY element_order ASC");
    $order = 10;

    while ($row = DB_fetchArray($result)) {
        if ( $stMenu[$menu_id]['elements'][$mid]->order != $order ) {
            $test_order = $order + 10;
            $order_select .= '<option value="' . $row['id'] . '"' . ($stMenu[$menu_id]['elements'][$mid]->order == $test_order ? ' selected="selected"' : '') . '>' . $row['element_label'] . '</option>' . LB;
        }
        $order += 10;
    }
    $order_select .= '</select>' . LB;

    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file( array( 'admin' => 'editelement.thtml'));

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'form_action'       => $_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php',
        'birdseed'          => '<a href="'.$_CONF['site_admin_url'].'/plugins/sitetailor/menu.php">Menu List</a> :: <a href="'.$_CONF['site_admin_url'].'/plugins/sitetailor/menu.php?mode=menu&amp;id='.$menu_id.'">'.$stMenu[$menu_id]['menu_name'].'</a> :: Edit Element',
        'menulabel'         => $stMenu[$menu_id]['elements'][$mid]->label,
        'menuorder'         => $stMenu[$menu_id]['elements'][$mid]->order,
        'order_select'      => $order_select,
        'menuurl'           => $stMenu[$menu_id]['elements'][$mid]->url,
        'phpfunction'       => $stMenu[$menu_id]['elements'][$mid]->subtype,
        'type_select'       => $type_select,
        'gl_select'         => $gl_select,
        'plugin_select'     => $plugin_select,
        'sp_select'         => $sp_select,
        'glfunction_select' => $glfunction_select,
        'parent_select'     => $parent_select,
        'group_select'      => $group_select,
        'target_select'     => $target_select,
        'active_selected'   => $active_selected,
        'menu'              => $menu_id,
        'mid'               => $mid,
        'xhtml'             => XHTML,
    ));
    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

/*
 * Saves an edited menu element
 */

function ST_saveEditMenuElement ( ) {
    global $_TABLES, $stMenu;

    $id            = COM_applyFilter($_POST['id'],true);
    $menu_id       = COM_applyFilter($_POST['menu']);
    $pid           = COM_applyFilter($_POST['pid'],true);
    $label         = addslashes(htmlspecialchars(strip_tags(COM_checkWords($_POST['menulabel']))));
    $type          = COM_applyFilter($_POST['menutype'],true);
    $target        = COM_applyFilter($_POST['urltarget']);

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
            if ( strpos($subtype,"http") !== 0 && strpos($subtype,"%site") === false && $subtype[0] != '#') {
                $subtype = 'http://' . $subtype;
            }
            break;
        case 7 :
            $subtype = COM_applyFilter($_POST['phpfunction']);
            break;
        default :
            $subtype = '';
            break;
    }
    $active     = COM_applyFilter($_POST['menuactive'],true);
    $url        = trim(addslashes(COM_applyFilter($_POST['menuurl'])));
    if ( strpos($url,"http") !== 0 && strpos($url,"%site") === false && $url[0] != '#') {
        $url = 'http://' . $url;
    }
    $group_id   = COM_applyFilter($_POST['group'],true);

    $aid                = COM_applyFilter($_POST['menuorder'],true);
    $aorder             = DB_getItem($_TABLES['st_menu_elements'],'element_order','id=' . $aid);
    $neworder = $aorder + 1;

    $sql        = "UPDATE {$_TABLES['st_menu_elements']} SET pid=$pid, element_order=$neworder, element_label='$label', element_type='$type', element_subtype='$subtype', element_active=$active, element_url='$url', element_target='$target', group_id=$group_id WHERE id=$id";

    DB_query($sql);

    CACHE_remove_instance('stmenu');

    st_initMenu();

    $stMenu[$menu_id]['elements'][$pid]->reorderMenu();

    st_initMenu();
}


/**
* Enable and Disable block
*/
function ST_changeActiveStatusElement ($bid_arr)
{
    global $_CONF, $_TABLES;

    $menu_id = COM_applyFilter($_POST['menu'],true);

    // first, disable all on the requested side
    $sql = "UPDATE {$_TABLES['st_menu_elements']} SET element_active = '0' WHERE menu_id=".$menu_id;
    DB_query($sql);
    if (isset($bid_arr)) {
        foreach ($bid_arr as $bid => $side) {
            $bid = COM_applyFilter($bid, true);
            // the enable those in the array
            $sql = "UPDATE {$_TABLES['st_menu_elements']} SET element_active = '1' WHERE id='$bid'";
            DB_query($sql);
        }
    }
    CACHE_remove_instance('stmenu');
    CACHE_remove_instance('css');
    CACHE_remove_instance('js');

    return;
}

/**
* Enable and Disable block
*/
function ST_changeActiveStatusMenu ($bid_arr)
{
    global $_CONF, $_TABLES;
    // first, disable all on the requested side
    $sql = "UPDATE {$_TABLES['st_menus']} SET menu_active = '0'";
    DB_query($sql);
    if (isset($bid_arr)) {
        foreach ($bid_arr as $bid => $side) {
            $bid = COM_applyFilter($bid, true);
            // the enable those in the array
            $sql = "UPDATE {$_TABLES['st_menus']} SET menu_active = '1' WHERE id='$bid'";
            DB_query($sql);
        }
    }
    CACHE_remove_instance('stmenu');

    return;
}

function ST_deleteMenu($menu_id) {
    global $stMenu, $_CONF, $_TABLES, $_USER;

    ST_deleteChildElements(0,$menu_id);

    DB_query("DELETE FROM {$_TABLES['st_menus']} WHERE id=".$menu_id);

    CACHE_remove_instance('stmenu');
    CACHE_remove_instance('css');
}


/**
* Recursivly deletes all elements and child elements
*
*/
function ST_deleteChildElements( $id, $menu_id ){
    global $stMenu, $_CONF, $_TABLES, $_USER;

    $sql = "SELECT * FROM {$_TABLES['st_menu_elements']} WHERE pid=" . $id . " AND menu_id='" . $menu_id . "'";
    $aResult = DB_query( $sql );
    $rowCount = DB_numRows($aResult);
    for ( $z=0; $z < $rowCount; $z++ ) {
        $row = DB_fetchArray( $aResult );
        ST_deleteChildElements( $row['id'],$menu_id );
    }
    $sql = "DELETE FROM " . $_TABLES['st_menu_elements'] . " WHERE id=" . $id;
    DB_query( $sql );

    CACHE_remove_instance('stmenu');
}

/*
 * Sets colors, etc. for the menu
 */

function ST_menuConfig( $mid ) {
    global $_CONF, $_TABLES, $_ST_CONF, $stMenu, $LANG_ST00, $LANG_ST01, $LANG_ST_ADMIN,
           $LANG_ST_TYPES, $LANG_ST_GLTYPES,$LANG_ST_GLFUNCTION,
           $LANG_ST_MENU_TYPES;

    /* define the active attributes for each menu type */

    $menuAttributes = array( 'main_menu_bg_color'       => 'none',
                             'main_menu_hover_bg_color' => 'none',
                             'main_menu_text_color'     => 'none',
                             'main_menu_hover_text_color' => 'none',
                             'submenu_text_color'       => 'none',
                             'submenu_hover_text_color' => 'none',
                             'submenu_background_color' => 'none',
                             'submenu_hover_bg_color'   => 'none',
                             'submenu_highlight_color'  => 'none',
                             'submenu_shadow_color'     => 'none',
                             'menu_bg_filename'         => 'none',
                             'menu_hover_filename'      => 'none',
                             'menu_parent_filename'     => 'none',
                             'menu_alignment'           => 'none',
                             'use_images'               => 'none',
                        );

    $HCattributes = array(   'main_menu_bg_color',
                             'main_menu_hover_bg_color',
                             'main_menu_text_color',
                             'main_menu_hover_text_color',
                             'submenu_hover_text_color',
                             'submenu_background_color',
                             'submenu_highlight_color',
                             'submenu_shadow_color',
                             'menu_bg_filename',
                             'menu_hover_filename',
                             'menu_parent_filename',
                             'menu_alignment',
                             'use_images',
                        );
    $HSattributes = array(   'main_menu_text_color',
                             'main_menu_hover_text_color',
                             'submenu_highlight_color',
                        );

    $VCattributes = array(   'main_menu_bg_color',
                             'main_menu_hover_bg_color',
                             'main_menu_text_color',
                             'main_menu_hover_text_color',
                             'submenu_text_color',
                             'submenu_hover_text_color',
                             'submenu_background_color',
                             'submenu_hover_bg_color',
                             'submenu_highlight_color',
                             'submenu_shadow_color',
                             'menu_parent_filename',
                             'menu_alignment',
                        );

    $VSattributes = array(   'main_menu_text_color',
                             'main_menu_hover_text_color',
                             'menu_alignment',
                        );

    $retval = '';
    $menu_id = $mid;
    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'] .'/plugins/sitetailor/menu.php',
                  'text' => $LANG_ST01['menu_list']),
    );
    $retval  .= COM_startBlock($LANG_ST01['menu_builder'].' :: '.$LANG_ST01['menu_colors'] .' for ' . $stMenu[$menu_id]['menu_name'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_ST_ADMIN[6],
                                $_CONF['site_admin_url'] . '/plugins/sitetailor/images/sitetailor-menubuilder.png');



    foreach ($menuAttributes AS $name => $display ) {
        $menuConfig[$name] = '#000000';
    }

    if ( is_array($stMenu[$mid]['config']) ) {
        foreach ($stMenu[$mid]['config'] AS $name => $value ) {
            $menuConfig[$name] = $value;
        }
    } else {
        foreach ($menuAttributes AS $name => $display ) {
            $menuConfig[$name] = '#000000';
        }
    }


    $main_menu_bg_colorRGB         = '[' . ST_hexrgb($menuConfig['main_menu_bg_color'],'r') .
                                      ',' . ST_hexrgb($menuConfig['main_menu_bg_color'],'g') .
                                      ',' . ST_hexrgb($menuConfig['main_menu_bg_color'],'b') . ']';

    $main_menu_hover_bg_colorRGB   = '[' . ST_hexrgb($menuConfig['main_menu_hover_bg_color'],'r')  .
                                      ',' . ST_hexrgb($menuConfig['main_menu_hover_bg_color'],'g')  .
                                      ',' . ST_hexrgb($menuConfig['main_menu_hover_bg_color'],'b')  . ']';

    $main_menu_text_colorRGB       = '[' . ST_hexrgb($menuConfig['main_menu_text_color'],'r')  .
                                      ',' . ST_hexrgb($menuConfig['main_menu_text_color'],'g')  .
                                      ',' . ST_hexrgb($menuConfig['main_menu_text_color'],'b')  . ']';

    $main_menu_hover_text_colorRGB = '[' . ST_hexrgb($menuConfig['main_menu_hover_text_color'],'r') .
                                      ',' . ST_hexrgb($menuConfig['main_menu_hover_text_color'],'g') .
                                      ',' . ST_hexrgb($menuConfig['main_menu_hover_text_color'],'b') . ']';

    $submenu_text_colorRGB         = '[' .  ST_hexrgb($menuConfig['submenu_text_color'],'r')  .
                                      ',' . ST_hexrgb($menuConfig['submenu_text_color'],'g')  .
                                      ',' . ST_hexrgb($menuConfig['submenu_text_color'],'b')  . ']';

    $submenu_hover_text_colorRGB   = '[' . ST_hexrgb($menuConfig['submenu_hover_text_color'],'r') .
                                      ',' . ST_hexrgb($menuConfig['submenu_hover_text_color'],'g') .
                                      ',' . ST_hexrgb($menuConfig['submenu_hover_text_color'],'b') . ']';

    $submenu_hover_bg_colorRGB     = '[' . ST_hexrgb($menuConfig['submenu_hover_bg_color'],'r') .
                                      ',' . ST_hexrgb($menuConfig['submenu_hover_bg_color'],'g') .
                                      ',' . ST_hexrgb($menuConfig['submenu_hover_bg_color'],'b') . ']';

    $submenu_background_colorRGB   = '[' . ST_hexrgb($menuConfig['submenu_background_color'],'r') .
                                      ',' . ST_hexrgb($menuConfig['submenu_background_color'],'g') .
                                      ',' . ST_hexrgb($menuConfig['submenu_background_color'],'b') . ']';

    $submenu_highlight_colorRGB    = '[' . ST_hexrgb($menuConfig['submenu_highlight_color'],'r')  .
                                      ',' . ST_hexrgb($menuConfig['submenu_highlight_color'],'g')  .
                                      ',' . ST_hexrgb($menuConfig['submenu_highlight_color'],'b')  . ']';

    $submenu_shadow_colorRGB       = '[' . ST_hexrgb($menuConfig['submenu_shadow_color'],'r')  .
                                      ',' . ST_hexrgb($menuConfig['submenu_shadow_color'],'g')  .
                                      ',' . ST_hexrgb($menuConfig['submenu_shadow_color'],'b')  . ']';

    $menu_active_check = ($stMenu[$mid]['active'] == 1  ? ' checked="checked"' : '');

    $menu_align_left_checked  = ($menuConfig['menu_alignment'] == 1 ? 'checked="checked"' : '');
    $menu_align_right_checked = ($menuConfig['menu_alignment'] == 0 ? 'checked="checked"' : '');

    $use_images_checked = ($menuConfig['use_images'] == 1 ? ' checked="checked"' : '');
    $use_colors_checked = ($menuConfig['use_images'] == 0 ? ' checked="checked"' : '');

    // build menu type select

    $menuTypeSelect = '<select id="menutype" name="menutype">' . LB;
    while ( $types = current($LANG_ST_MENU_TYPES) ) {
        $menuTypeSelect .= '<option value="' . key($LANG_ST_MENU_TYPES) . '"';
        if (key($LANG_ST_MENU_TYPES) == $stMenu[$menu_id]['menu_type']) {
            $menuTypeSelect .= ' selected="selected"';
        }
        $menuTypeSelect .= '>' . $types . '</option>' . LB;
        next($LANG_ST_MENU_TYPES);
    }
    $menuTypeSelect .= '</select>' . LB;

    // build group select

    $rootUser = DB_getItem($_TABLES['group_assignments'],'ug_uid','ug_main_grp_id=1');
    $usergroups = SEC_getUserGroups($rootUser);
    $usergroups[$LANG_ST01['non-logged-in']] = 998;
    ksort($usergroups);
    $group_select .= '<select id="group" name="group">' . LB;
    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        if ( $usergroups[key($usergroups)] == $stMenu[$menu_id]['group_id']) {
            $group_select .= ' selected="selected"';
        }
        $group_select .= '>' . key($usergroups) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file( array( 'admin' => 'menuconfig.thtml'));

    $T->set_var(array(
        'group_select'      => $group_select,
        'menutype'          => $stMenu[$menu_id]['menu_type'],
        'menutype_select'   => $menuTypeSelect,
        'menuactive'        => $stMenu[$menu_id]['active'] == 1 ? ' checked="checked"' : ' ',
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'form_action'       => $_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php',
        'birdseed'          => '<a href="'.$_CONF['site_admin_url'].'/plugins/sitetailor/menu.php">Menu List</a> :: '.$stMenu[$mid]['menu_name'].' :: Configuration',
        'menu_id'           => $mid,
        'menu_name'         => $stMenu[$mid]['menu_name'],
        'tmbgcolor'         => $menuConfig['main_menu_bg_color'],
        'tmbgcolorrgb'      => $main_menu_bg_colorRGB,
        'tmhcolor'          => $menuConfig['main_menu_hover_bg_color'],
        'tmhcolorrgb'       => $main_menu_hover_bg_colorRGB,
        'tmtcolor'          => $menuConfig['main_menu_text_color'],
        'tmtcolorrgb'       => $main_menu_text_colorRGB,
        'tmthcolor'         => $menuConfig['main_menu_hover_text_color'],
        'tmthcolorrgb'      => $main_menu_hover_text_colorRGB,
        'smtcolor'          => $menuConfig['submenu_text_color'],
        'smtcolorrgb'       => $submenu_text_colorRGB,
        'smthcolor'         => $menuConfig['submenu_hover_text_color'],
        'smthcolorrgb'      => $submenu_hover_text_colorRGB,
        'smbgcolor'         => $menuConfig['submenu_background_color'],
        'smbgcolorrgb'      => $submenu_background_colorRGB,
        'smhbgcolor'         => $menuConfig['submenu_hover_bg_color'],
        'smhbgcolorrgb'      => $submenu_hover_bg_colorRGB,
        'smhcolor'          => $menuConfig['submenu_highlight_color'],
        'smhcolorrgb'       => $submenu_highlight_colorRGB,
        'smscolor'          => $menuConfig['submenu_shadow_color'],
        'smscolorrgb'       => $submenu_shadow_colorRGB,
        'enabled'           => $menu_active_check,
        'graphics_selected' => $use_images_checked,
        'colors_selected'   => $use_colors_checked,
        'menu_bg_filename'          => $menuConfig['menu_bg_filename'],
        'menu_hover_filename'       => $menuConfig['menu_hover_filename'],
        'menu_parent_filename'      => $menuConfig['menu_parent_filename'],
        'alignment_left_checked'    => $menu_align_left_checked,
        'alignment_right_checked'   => $menu_align_right_checked,
        'xhtml'                     => XHTML,
    ));

    if ( $stMenu[$menu_id]['menu_type'] == 1 ) {
        $T->set_var('show_warning','1');
    }

    /* check menu type and call the proper foreach call to
       set the display for the items.
    */

    switch ($stMenu[$mid]['menu_type']) {
        case 1: // horizontal cascading...
            foreach ($HCattributes AS $name) {
                $menuAttributes[$name] = 'show';
            }
            break;
        case 2: // horizontal simple
            foreach ($HSattributes AS $name) {
                $menuAttributes[$name] = 'show';
            }
            break;
        case 3: // vertical cascading
            foreach ($VCattributes AS $name) {
                $menuAttributes[$name] = 'show';
            }
            break;
        case 4: // vertical simple
            foreach ($VSattributes AS $name) {
                $menuAttributes[$name] = 'show';
            }
            break;
    }

    foreach ($menuAttributes AS $name => $display ) {
        $T->set_var($name.'_show', $display);
    }

    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

/*
 * Saves the menu configuration
 */

function ST_saveMenuConfig($menu_id=0) {
    global $_CONF, $_TABLES, $_ST_CONF, $stMenu;

    $menu_id                          = COM_applyFilter($_POST['menu_id'],true);
    $mc['main_menu_bg_color']         = COM_applyFilter($_POST['tmbg_sample']);
    $mc['main_menu_hover_bg_color']   = COM_applyFilter($_POST['tmh_sample']);
    $mc['main_menu_text_color']       = COM_applyFilter($_POST['tmt_sample']);
    $mc['main_menu_hover_text_color'] = COM_applyFilter($_POST['tmth_sample']);
    $mc['submenu_text_color']         = COM_applyFilter($_POST['smt_sample']);
    $mc['submenu_hover_text_color']   = COM_applyFilter($_POST['smth_sample']);
    $mc['submenu_background_color']   = COM_applyFilter($_POST['smbg_sample']);
    $mc['submenu_hover_bg_color']     = COM_applyFilter($_POST['smhbg_sample']);
    $mc['submenu_highlight_color']    = COM_applyFilter($_POST['smh_sample']);
    $mc['submenu_shadow_color']       = COM_applyFilter($_POST['sms_sample']);
    $mc['menu_alignment']             = isset($_POST['malign']) ? COM_applyFilter($_POST['malign'],true) : 0;
    $mc['use_images']                 = isset($_POST['gorc']) ? COM_applyFilter($_POST['gorc'],true) : 0;

    $menutype   = COM_applyFilter($_POST['menutype'],true);
    $menuactive = COM_applyFilter($_POST['menuactive'],true);
    $menugroup  = COM_applyFilter($_POST['group'],true);

    $menuname   = addslashes($stMenu[$menu_id]['menu_name']);

    $sqlFieldList  = 'id,menu_name,menu_type,menu_active,group_id';
    $sqlDataValues = "$menu_id,'$menuname',$menutype,$menuactive,$menugroup";
    DB_save($_TABLES['st_menus'], $sqlFieldList, $sqlDataValues);

    foreach ($mc AS $name => $value) {
        DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'$name','$value'");
    }

    $file = array();
    $file = $_FILES['bgimg'];
    if ( isset($file['tmp_name']) && $file['tmp_name'] != '' ) {

        switch ( $file['type'] ) {
            case 'image/png' :
            case 'image/x-png' :
                $ext = '.png';
                break;
            case 'image/gif' :
                $ext = '.gif';
                break;
            case 'image/jpg' :
            case 'image/jpeg' :
            case 'image/pjpeg' :
                $ext = '.jpg';
                break;
            default :
                $ext = 'unknown';
                $retval = 2;
                break;
        }
        if ( $ext != 'unknown' ) {
            $imgInfo = @getimagesize($file['tmp_name']);
            if ( $imgInfo != false ) {
                $newFilename = 'menu_bg' . substr(md5(uniqid(rand())),0,8) . $ext;
                $rc = move_uploaded_file($file['tmp_name'],$_CONF['path_html'] . 'images/menu/' . $newFilename);
                if ( $rc ) {
                    @unlink($_CONF['path_html'] . '/menu/images/' . $stMenu[$menu_id]['config']['bgimage']);
                    DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_bg_filename','$newFilename'");
                }
            }
        }
    }
    $file = array();
    $file = $_FILES['hvimg'];
    if ( isset($file['tmp_name']) && $file['tmp_name'] != '' ) {
        switch ( $file['type'] ) {
            case 'image/png' :
            case 'image/x-png' :
                $ext = '.png';
                break;
            case 'image/gif' :
                $ext = '.gif';
                break;
            case 'image/jpg' :
            case 'image/jpeg' :
            case 'image/pjpeg' :
                $ext = '.jpg';
                break;
            default :
                $ext = 'unknown';
                $retval = 2;
                break;
        }
        if ( $ext != 'unknown' ) {
            $imgInfo = @getimagesize($file['tmp_name']);
            if ( $imgInfo != false ) {
                $newFilename = 'menu_hover_bg' . substr(md5(uniqid(rand())),0,8) . $ext;
                $rc = move_uploaded_file($file['tmp_name'],$_CONF['path_html'] . 'images/menu/' . $newFilename);
                if ( $rc ) {
                    @unlink($_CONF['path_html'] . '/menu/images/' . $stMenu[$menu_id]['config']['hoverimage']);
                    DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_hover_filename','$newFilename'");
                }
            }
        }
    }
    $file = array();
    $file = $_FILES['piimg'];
    if ( isset($file['tmp_name']) && $file['tmp_name'] != '' ) {
        switch ( $file['type'] ) {
            case 'image/png' :
            case 'image/x-png' :
                $ext = '.png';
                break;
            case 'image/gif' :
                $ext = '.gif';
                break;
            case 'image/jpg' :
            case 'image/jpeg' :
            case 'image/pjpeg' :
                $ext = '.jpg';
                break;
            default :
                $ext = 'unknown';
                $retval = 2;
                break;
        }
        if ( $ext != 'unknown' ) {
            $imgInfo = @getimagesize($file['tmp_name']);
            if ( $imgInfo != false ) {
                $newFilename = 'menu_parent' . substr(md5(uniqid(rand())),0,8) . $ext;
                $rc = move_uploaded_file($file['tmp_name'],$_CONF['path_html'] . 'images/menu/' . $newFilename);
                if ( $rc ) {
                    @unlink($_CONF['path_html'] . '/menu/images/' . $stMenu[$menu_id]['config']['parentimage']);
                    DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_parent_filename','$newFilename'");
                }
            }
        }
    }
    CACHE_remove_instance('stmenu');
    CACHE_remove_instance('css');
    $randID = rand();
    DB_save($_TABLES['vars'],'name,value',"'cacheid',$randID");
    return;
}

function ST_hexrgb($hexstr, $rgb) {
    $int = hexdec($hexstr);
    switch($rgb) {
        case "r":
            return 0xFF & $int >> 0x10;
            break;
        case "g":
            return 0xFF & ($int >> 0x8);
            break;
        case "b":
            return 0xFF & $int;
            break;
        default:
            return array(
                "r" => 0xFF & $int >> 0x10,
                "g" => 0xFF & ($int >> 0x8),
                "b" => 0xFF & $int
            );
            break;
    }
}

/*
 * Main processing loop
 */

if (isset($_GET['msg']) ) {
    $msg = COM_applyFilter($_GET['msg'],true);
} else {
    $msg = 0;
}

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
if ( isset($_REQUEST['id']) ) {
    $menu_id = COM_applyFilter($_REQUEST['id'],true);
}
if ( isset($_REQUEST['menu'] ) ) {
    $menu_id = COM_applyFilter($_REQUEST['menu'],true);
}
if ( isset($_REQUEST['mid']) ) {
    $mid = COM_applyFilter($_REQUEST['mid'],true);
}
if ( (isset($_POST['execute']) || $mode != '') && !isset($_POST['cancel']) && !isset($_POST['defaults'])) {
    switch ( $mode ) {
        case 'menu' :
            // display the tree
            $content = ST_displayTree( $menu_id );
            break;
        case 'new' :
            $menu = COM_applyFilter($_GET['menuid'],true);
            $content = ST_createElement ( $menu );
            break;
        case 'move' :
            // do something with the direction
            $direction = COM_applyFilter($_GET['where']);
            $mid       = COM_applyFilter($_GET['mid'],true);
            $menu_id   = COM_applyFilter($_GET['menu'],true);
            ST_moveElement( $menu_id, $mid, $direction );
            echo COM_refresh($_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php?mode=menu&amp;id=' . $menu_id);
            exit;
            break;
        case 'edit' :
            // call the editor
            $mid       = COM_applyFilter($_GET['mid'],true);
            $menu_id   = COM_applyFilter($_GET['menu'],true);
            $content   = ST_editElement( $menu_id, $mid );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'saveedit' :
            ST_saveEditMenuElement();
            $content = ST_displayTree( $menu_id );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'save' :
            // save the new or edited element
            $menu_id = COM_applyFilter($_POST['menuid'],true);
            ST_saveNewMenuElement();
            echo COM_refresh($_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php?mode=menu&amp;id=' . $menu_id);
            exit;
            break;
        case 'savenewmenu' :
            ST_saveNewMenu();
            $content = ST_displayMenuList( );
            break;
        case 'saveeditmenu' :
            ST_saveEditMenu();
            $content = ST_displayMenuList( );
            break;
        case 'editmenu' :
            $menu_id = COM_applyFilter($_GET['menu_id'],true);
            $content = ST_editMenu( $menu_id );
            break;
        case 'activate' :
            ST_changeActiveStatusElement ($_POST['enableditem']);
            st_initMenu();
            $content = ST_displayTree( $menu_id );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'menuactivate' :
            ST_changeActiveStatusMenu ($_POST['enabledmenu']);
            st_initMenu();
            $content = ST_displayMenuList( );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'delete' :
            // delete the element
            $id        = COM_applyFilter($_GET['mid'],true);
            $menu_id   = COM_applyFilter($_GET['menuid'],true);
            ST_deleteChildElements( $id, $menu_id );
            $stMenu[$menu_id]['elements'][0]->reorderMenu();
            echo COM_refresh($_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php?mode=menu&amp;id=' . $menu_id);
            exit;
            break;
        case 'deletemenu' :
            // delete the element
            $menu_id   = COM_applyFilter($_GET['id'],true);
            ST_deleteMenu($menu_id);
            echo COM_refresh($_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php');
            exit;
            break;
        case 'config' :
            $content = ST_menuConfig($menu_id);
            $currentSelect = $LANG_ST01['configuration'];
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'savecfg' :
            $menu_id = COM_applyFilter($_POST['menu_id'],true);
            ST_saveMenuConfig($menu_id);
            st_initMenu();
            $content = ST_menuConfig( $menu_id );
            $currentSelect = $LANG_ST01['menu_colors'];
            break;
        case 'disablemenu' :
            $action = COM_applyFilter($_POST['menuactive'],true);
            $mid    = COM_applyFilter($_POST['menutodisable'],true);
            $sql = "UPDATE {$_TABLES['st_menu_config']} SET enabled = " . $action . " WHERE menu_id=" . $mid . ";";
            DB_query($sql);
            echo COM_refresh($_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php?mode=menu&amp;mid=' . $mid);
            exit;
            break;
        case 'menucolor' :
            $content = ST_menuConfig($menu_id);
            $currentSelect = $LANG_ST01['menu_colors'];
            break;
        case 'menuconfig' :
            $menu_id = COM_applyFilter($_REQUEST['menuid'],true);
            $content = ST_menuConfig($menu_id);
            $currentSelect = $LANG_ST01['menu_colors'];
            break;
        case 'newmenu' :
            $content = ST_createMenu( );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        default :
            $content = ST_displayMenuList( );
            break;
    }
} else {
    if ( isset($_POST['defaults']) ) {
        $menu_id = COM_applyFilter($_POST['menu_id'],true);
        switch ( $stMenu[$menu_id]['menu_type']) {
            case 1:
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_bg_color','#151515'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_bg_color','#3667c0'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_text_color','#CCCCCC'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_text_color','#FFFFFF'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_text_color','#FFFFFF'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_text_color','#679EF1'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_background_color','#151515'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_bg_color','#333333'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_highlight_color','#333333'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_shadow_color','#000000'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'use_images','1'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_bg_filename','menu_bg.gif'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_hover_filename','menu_hover_bg.gif'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_parent_filename','menu_parent.png'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_alignment','1'");
                break;
            case 2:
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_bg_color','#000000'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_bg_color','#000000'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_text_color','#3677C0'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_text_color','#679EF1'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_text_color','#FFFFFF'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_text_color','#679EF1'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_background_color','#151515'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_bg_color','#333333'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_highlight_color','#151515'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_shadow_color','#151515'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'use_images','1'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_bg_filename','menu_bg.gif'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_hover_filename','menu_hover_bg.gif'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_parent_filename','menu_parent.png'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_alignment','1'");
                break;
            case 3:
            case 4:
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_bg_color','#DDDDDD'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_bg_color','#BBBBBB'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_text_color','#0000FF'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'main_menu_hover_text_color','#FFFFFF'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_text_color','#0000FF'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_text_color','#F7FF00'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_background_color','#DDDDDD'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_hover_bg_color','#BBBBBB'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_highlight_color','#999999'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'submenu_shadow_color','#999999'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'use_images','1'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_bg_filename','menu_bg.gif'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_hover_filename','menu_hover_bg.gif'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_parent_filename','vmenu_parent.gif'");
                DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'menu_alignment','1'");
                break;
        }

    }
    // display the tree
    $content = ST_displayMenuList( );
}

$display = COM_siteHeader();
$display .= '<noscript>' . LB;
$display .= '    <div class="pluginAlert aligncenter" style="border:1px dashed #ccc;margin-top:10px;padding:15px;">' . LB;
$display .= '    <p>' . $LANG_ST01['javascript_required'] . '</p>' . LB;
$display .= '    </div>' . LB;
$display .= '</noscript>' . LB;
$display .= '<div id="sitetailor" style="display:none;">' . LB;
$display .= $content;
$display .= '</div>';
$display .= COM_siteFooter();
echo $display;
exit;
?>