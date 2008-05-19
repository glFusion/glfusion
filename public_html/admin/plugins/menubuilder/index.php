<?php
// +--------------------------------------------------------------------------+
// | gl Labs Menu Builder Plugin 1.0                                          |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans              - mark at gllabs.org                          |
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
//

require_once('../../../lib-common.php');

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('menubuilder.admin')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Menu Builder Administration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MB00['access_denied']);
    $display .= $LANG_MB00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function mbDisplayTree( $menu_id ) {
    global $_CONF, $LANG_MB00, $_MB_CONF, $MB_elements;

    $retval = '';

    $T = new Template($_CONF['path'] . 'plugins/menubuilder/templates/');
    $T->set_file (array ('admin' => 'menutree.thtml'));

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'status_msg'        => $statusMsg,
        'lang_admin'        => $LANG_MB00['admin'],
        'version'           => $_MB_CONF['version'],
        'menu_tree'         => $MB_elements[$menu_id][0]->editTree(0,2),
        'menuid'            => $menu_id,
        'xhtml'             => XHTML,
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function mbMoveElement( $menu, $mid, $direction ) {
    global $_CONF, $_TABLES, $_MB_CONF, $MB_elements;

    switch ( $direction ) {
        case 'up' :
            $neworder = $MB_elements[$menu][$mid]->order - 11;

            DB_query("UPDATE {$_TABLES['mb_elements']} SET element_order=" . $neworder . " WHERE id=" . $mid);
            break;
        case 'down' :
            $neworder = $MB_elements[$menu][$mid]->order + 11;
            DB_query("UPDATE {$_TABLES['mb_elements']} SET element_order=" . $neworder . " WHERE id=" . $mid);
            break;
    }
    $pid = $MB_elements[$menu][$mid]->pid;

    MB_initMenu();
    $MB_elements[$menu][$pid]->reorderMenu();
    MB_initMenu();
    return;
}

function mbCreateElement ( $menu ) {
    global $_CONF, $_TABLES, $_MB_CONF, $MB_elements;
    global $LANG_MB00, $LANG_MB01, $LANG_MB_TYPES, $LANG_MB_GLTYPES,$LANG_MB_GLFUNCTION;

    $retval = '';

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
    while ( $types = current($LANG_MB_TYPES) ) {
        if ( $spCount == 0 && key($LANG_MB_TYPES) == 5 ) {
            // skip it
        } else {
            $type_select .= '<option value="' . key($LANG_MB_TYPES) . '"';
            $type_select .= '>' . $types . '</option>' . LB;
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
    $plugin_menus = _mbPLG_getMenuItems(); // PLG_getMenuItems();

    $num_plugins = count($plugin_menus);
    for( $i = 1; $i <= $num_plugins; $i++ )
    {
        $plugin_select .= '<option value="' . key($plugin_menus) . '">' . key($plugin_menus) . '</option>' . LB;
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

    $parent_select = '<select name="pid" id="pid">' . LB;
    $parent_select .= '<option value="0">' . $LANG_MB01['top_level'] . '</option>' . LB;
    $result = DB_query("SELECT id,element_label FROM {$_TABLES['mb_elements']} WHERE menu_id='" . $menu . "' AND element_type=1");
    while ($row = DB_fetchArray($result)) {
        $parent_select .= '<option value="' . $row['id'] . '">' . $row['element_label'] . '</option>' . LB;
    }
    $parent_select .= '</select>' . LB;

    $order_select = '<select id="menuorder" name="menuorder">' . LB;
    $order_select .= '<option value="0">' . $LANG_MB01['first_position'] . '</option>' . LB;
    $result = DB_query("SELECT id,element_label,element_order FROM {$_TABLES['mb_elements']} WHERE menu_id='" . $menu . "' AND pid=0 ORDER BY element_order ASC");
    while ($row = DB_fetchArray($result)) {
        $order_select .= '<option value="' . $row['id'] . '">' . $row['element_label'] . '</option>' . LB;
    }
    $order_select .= '</select>' . LB;

    // build group select

    $usergroups = SEC_getUserGroups();
    $group_select .= '<select id="group" name="group">' . LB;
    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        $group_select .= '>' . key($usergroups) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    $T = new Template($_CONF['path'] . 'plugins/menubuilder/templates/');
    $T->set_file( array( 'admin' => 'createelement.thtml'));

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'form_action'       => $_CONF['site_admin_url'] . '/plugins/menubuilder/index.php',
        'menuname'          => $menu_name,
        'menuid'            => $menu,
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
    $retval = $T->finish($T->get_var('output'));
    return $retval;
}

function mbSaveElement ( ) {
    global $_CONF, $_TABLES, $LANG_MB00, $_MB_CONF, $MB_elements;

    $meadmin = SEC_hasRights('menubuilder.admin');
    $root    = SEC_inGroup('Root');
    $groups = $_GROUPS;

    $element            = new mbElement();
    $E['menu_id']       = COM_applyFilter($_POST['menuid']);
    $menu_id            = $E['menu_id'];
    $E['pid']           = COM_applyFilter($_POST['pid'],true);
    $E['element_label'] = COM_applyFilter($_POST['menulabel']);
    $E['element_type']  = COM_applyFilter($_POST['menutype'],true);
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
            break;
        case 7 :
            $E['element_subtype'] = COM_applyFilter($_POST['phpfunction']);
            break;
        default :
            $E['element_subtype'] = '';
            break;
    }
    $aid                = COM_applyFilter($_POST['menuorder'],true);
    $aorder             = DB_getItem($_TABLES['mb_elements'],'element_order','id=' . $aid);
    $E['element_order'] = $aorder + 1;

    $E['element_active']    = COM_applyFilter($_POST['menuactive'],true);
    $E['element_url']       = COM_applyFilter($_POST['menuurl']);
    $E['group_id']          = COM_applyFilter($_POST['group'],true);

    $element->constructor( $E, $meadmin, $root, $groups );
    $element->id        = $element->createElementID($E['menu_name']);

    $element->saveElement();
    $pid = $E['pid'];
    $menuname = $E['menu_name'];

    MB_initMenu();

    $MB_elements[$menu_id][$pid]->reorderMenu();

    MB_initMenu();
}

function mbEditElement( $menu, $mid ) {
    global $_CONF, $_TABLES, $_MB_CONF, $MB_elements;
    global $LANG_MB00, $LANG_MB01, $LANG_MB_TYPES, $LANG_MB_GLTYPES,$LANG_MB_GLFUNCTION;

    $retval = '';

    // build types select

    if ( $MB_elements[$menu][$mid]->type == 1 ) {
        $type_select = '<select id="menutype" name="menutype" disabled="disabled">' . LB;
    } else {
        $type_select = '<select id="menutype" name="menutype">' . LB;
    }
    while ( $types = current($LANG_MB_TYPES) ) {
        $type_select .= '<option value="' . key($LANG_MB_TYPES) . '"';
        $type_select .= ($MB_elements[$menu][$mid]->type==key($LANG_MB_TYPES) ? ' selected="selected"' : '') . '>' . $types . '</option>' . LB;
        next($LANG_MB_TYPES);
    }
    $type_select .= '</select>' . LB;


    $glfunction_select = '<select id="glfunction" name="glfunction">' . LB;
    while ( $glfunction = current($LANG_MB_GLFUNCTION) ) {
        $glfunction_select .= '<option value="' . key($LANG_MB_GLFUNCTION) . '"';
        $glfunction_select .= ($MB_elements[$menu][$mid]->subtype==key($LANG_MB_GLFUNCTION) ? ' selected="selected"' : '') . '>' . $glfunction . '</option>' . LB;
        next($LANG_MB_GLFUNCTION);
    }
    $glfunction_select .= '</select>' . LB;

    $gl_select = '<select id="gltype" name="gltype">' . LB;
    while ( $gltype = current($LANG_MB_GLTYPES) ) {
        $gl_select .= '<option value="' . key($LANG_MB_GLTYPES) . '"';
        $gl_select .= ($MB_elements[$menu][$mid]->subtype==key($LANG_MB_GLTYPES) ? ' selected="selected"' : '') . '>' . $gltype . '</option>' . LB;
        next($LANG_MB_GLTYPES);
    }
    $gl_select .= '</select>' . LB;

    $plugin_select = '<select id="pluginname" name="pluginname">' . LB;
    $plugin_menus = _mbPLG_getMenuItems(); // PLG_getMenuItems();

    $num_plugins = count($plugin_menus);
    for( $i = 1; $i <= $num_plugins; $i++ )
    {
        $plugin_select .= '<option value="' . key($plugin_menus) . '"' . ($MB_elements[$menu][$mid]->subtype==key($plugin_menus) ? ' selected="selected"' : '') . '>' . key($plugin_menus) . '</option>' . LB;
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
        $sp_select .= '<option value="' . $sp_id . '">' . $label . '</option>' . LB;
    }
    $sp_select .= '</select>' . LB;

    $parent_select = '<select id="pid" name="pid">' . LB;
    $parent_select .= '<option value="0">' . $LANG_MB01['top_level'] . '</option>' . LB;
    $result = DB_query("SELECT id,element_label FROM {$_TABLES['mb_elements']} WHERE menu_id='" . $menu . "' AND element_type=1");
    while ($row = DB_fetchArray($result)) {
        $parent_select .= '<option value="' . $row['id'] . '" ' . ($MB_elements[$menu][$mid]->pid==$row['id'] ? 'selected="selected"' : '') . '>' . $row['element_label'] . '</option>' . LB;
    }
    $parent_select .= '</select>' . LB;

    // build group select

    $usergroups = SEC_getUserGroups();
    $group_select .= '<select id="group" name="group">' . LB;
    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        if ($MB_elements[$menu][$mid]->group_id==$usergroups[key($usergroups)] ) {
            $group_select .= ' selected="selected"';
        }
        $group_select .= '>' . key($usergroups) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    if ( $MB_elements[$menu][$mid]->active ) {
        $active_selected = ' checked="checked"';
    } else {
        $active_selected = '';
    }

    $T = new Template($_CONF['path'] . 'plugins/menubuilder/templates/');
    $T->set_file( array( 'admin' => 'editelement.thtml'));

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'form_action'       => $_CONF['site_admin_url'] . '/plugins/menubuilder/index.php',
        'menulabel'         => $MB_elements[$menu][$mid]->label,
        'menuorder'         => $MB_elements[$menu][$mid]->order,
        'menuurl'           => $MB_elements[$menu][$mid]->url,
        'phpfunction'       => $MB_elements[$menu][$mid]->subtype,
        'type_select'       => $type_select,
        'gl_select'         => $gl_select,
        'plugin_select'     => $plugin_select,
        'order_select'      => $order_select,
        'sp_select'         => $sp_select,
        'glfunction_select' => $glfunction_select,
        'parent_select'     => $parent_select,
        'group_select'      => $group_select,
        'active_selected'   => $active_selected,
        'menu'              => $menu,
        'mid'               => $mid,
        'xhtml'             => XHTML,
    ));
    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function mbSaveEdit ( ) {
    global $_TABLES;

    $id            = COM_applyFilter($_POST['id'],true);
    $menu_id       = COM_applyFilter($_POST['menu']);
    $pid           = COM_applyFilter($_POST['pid'],true);
    $label         = addslashes(COM_applyFilter($_POST['menulabel']));
    $type          = COM_applyFilter($_POST['menutype'],true);

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
            break;
        case 7 :
            $subtype = COM_applyFilter($_POST['phpfunction']);
            break;
        default :
            $subtype = '';
            break;
    }
    $active     = COM_applyFilter($_POST['menuactive'],true);
    $url        = addslashes(COM_applyFilter($_POST['menuurl']));
    $group_id   = COM_applyFilter($_POST['group'],true);
    $sql        = "UPDATE {$_TABLES['mb_elements']} SET pid=$pid, element_label='$label', element_type='$type', element_subtype='$subtype', element_active=$active, element_url='$url',group_id=$group_id WHERE id=$id";

    DB_query($sql);
    MB_initMenu();
}


/**
* Enable and Disable block
*/
function changeActiveStatus ($bid_arr)
{
    global $_CONF, $_TABLES;
    // first, disable all on the requested side
    $sql = "UPDATE {$_TABLES['mb_elements']} SET element_active = '0' WHERE menu_id=0;";
    DB_query($sql);
    if (isset($bid_arr)) {
        foreach ($bid_arr as $bid => $side) {
            $bid = COM_applyFilter($bid, true);
            // the enable those in the array
            $sql = "UPDATE {$_TABLES['mb_elements']} SET element_active = '1' WHERE id='$bid'";
            DB_query($sql);
        }
    }
    return;
}



/**
* Recursivly deletes all elements and child elements
*
*/
function MB_deleteChildElements( $id, $menu_id ){
    global $MB_elements, $_CONF, $_TABLES, $_USER;

    $sql = "SELECT * FROM {$_TABLES['mb_elements']} WHERE pid=" . $id . " AND menu_id='" . $menu_id . "'";
    $aResult = DB_query( $sql );
    $rowCount = DB_numRows($aResult);
    for ( $z=0; $z < $rowCount; $z++ ) {
        $row = DB_fetchArray( $aResult );
        MB_deleteChildElements( $row['id'],$menu_id );
    }
    $sql = "DELETE FROM " . $_TABLES['mb_elements'] . " WHERE id=" . $id;
    DB_query( $sql );
}

function mbConfig( ) {
    global $_CONF, $_TABLES, $_MB_CONF, $MB_elements;
    global $LANG_MB00, $LANG_MB01, $LANG_MB_TYPES, $LANG_MB_GLTYPES,$LANG_MB_GLFUNCTION;

    $retval = '';

    $sql = "SELECT * FROM {$_TABLES['mb_config']} WHERE menu_id=0";
    $result = DB_query($sql);
    list($cid,$menu_id,$hcolor,$hhcolor,$htext,$hhtext,$enabled) = DB_fetchArray($result);

    $hcolorRGB  = '[' . hexrgb($hcolor,'r') . ',' . hexrgb($hcolor,'g') . ',' . hexrgb($hcolor,'b') . ']';
    $hhcolorRGB = '[' . hexrgb($hhcolor,'r') . ',' . hexrgb($hhcolor,'g') . ',' . hexrgb($hhcolor,'b') . ']';
    $htextRGB   = '[' . hexrgb($htext,'r') . ',' . hexrgb($htext,'g') . ',' . hexrgb($htext,'b') . ']';
    $hhtextRGB  = '[' . hexrgb($hhtext,'r') . ',' . hexrgb($hhtext,'g') . ',' . hexrgb($hhtext,'b') . ']';

    $menuenabled_check = ($enabled == 1 ? ' checked="checked"' : '');

    $T = new Template($_CONF['path'] . 'plugins/menubuilder/templates/');
    $T->set_file( array( 'admin' => 'config.thtml'));

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'form_action'       => $_CONF['site_admin_url'] . '/plugins/menubuilder/index.php',
        'hcolor'            => $hcolor,
        'hcolorrgb'         => $hcolorRGB,
        'hhcolorrgb'        => $hhcolorRGB,
        'htextrgb'          => $htextRGB,
        'hhtextrgb'         => $hhtextRGB,
        'hhcolor'           => $hhcolor,
        'htext'             => $htext,
        'hhtext'            => $hhtext,
        'enabled'           => ($enabled == 1 ? ' checked="checked"' : ''),
        'xhtml'             => XHTML,
    ));
    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function mbSaveConfig( ) {
    global $_CONF, $_TABLES, $_MB_CONF, $MB_elements;
    global $LANG_MB00, $LANG_MB01, $LANG_MB_TYPES, $LANG_MB_GLTYPES,$LANG_MB_GLFUNCTION;

    $menu_id = 0;
    $hcolor  = COM_applyFilter($_POST['hcolor']);
    $hhcolor = COM_applyFilter($_POST['hhcolor']);
    $htext   = COM_applyFilter($_POST['htext']);
    $hhtext  = COM_applyFilter($_POST['hhtext']);
    $enabled = COM_applyFilter($_POST['menuenabled'],true);

    DB_query("UPDATE {$_TABLES['mb_config']} SET hcolor='$hcolor',hhcolor='$hhcolor',htext='$htext',hhtext='$hhtext',enabled=$enabled WHERE menu_id=0");
    return;
}

function hexrgb($hexstr, $rgb) {
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

/*
 * We are hard coding the menu id to 0 (header menu) for now
 */
$menu_id = 0;

$currentSelect = $LANG_MB01['menu_list'];

if ( (isset($_POST['execute']) || $mode != '') && !isset($_POST['cancel']) ) {
    switch ( $mode ) {
        case 'new' :
            $menu = COM_applyFilter($_GET['menu'],true);
            $content = mbCreateElement ( $menu );
            $currentSelect = $LANG_MB01['add_new'];
            break;
        case 'move' :
            // do something with the direction
            $direction = COM_applyFilter($_GET['where']);
            $mid       = COM_applyFilter($_GET['mid'],true);
            $menu_id   = COM_applyFilter($_GET['menu'],true);
            mbMoveElement( $menu_id, $mid, $direction );
            $content = mbDisplayTree( $menu_id );
            break;
        case 'edit' :
            // call the editor
            $mid       = COM_applyFilter($_GET['mid'],true);
            $menu_id   = COM_applyFilter($_GET['menu'],true);
            $content = mbEditElement( $menu_id, $mid );
            $currentSelect = '';
            break;
        case 'saveedit' :
            mbSaveEdit();
            $content = mbDisplayTree( $menu_id );
            break;
        case 'save' :
            // save the new or edited element
            mbSaveElement();
            $content = mbDisplayTree( $menu_id );
            break;
        case 'activate' :
            changeActiveStatus ($_POST['enableditem']);
            MB_initMenu();
            $content = mbDisplayTree( $menu_id );
            break;
        case 'delete' :
            // delete the element
            $id        = COM_applyFilter($_GET['mid'],true);
            $menu      = 0;
            MB_deleteChildElements( $id, $menu );
            MB_initMenu();
            $content = mbDisplayTree( $menu_id );
            break;
        case 'config' :
            $content = mbConfig();
            $currentSelect = $LANG_MB01['configuration'];
            break;
        case 'savecfg' :
            mbSaveConfig();
            MB_initMenu();
            $content = mbDisplayTree( $menu_id );
            break;
        default :
            // display the tree
            $content = mbDisplayTree( $menu_id );
            break;
    }
} else {
    $content = mbDisplayTree( $menu_id );
}

include_once($_CONF['path_system']."classes/navbar.class.php");

$navbar = new navbar;
$navbar->add_menuitem($LANG_MB01['menu_list'],$_CONF['site_admin_url'] . '/plugins/menubuilder/index.php');
$navbar->add_menuitem($LANG_MB01['add_new'],$_CONF['site_admin_url'] . '/plugins/menubuilder/index.php?mode=new&amp;menu=0');
$navbar->add_menuitem($LANG_MB01['configuration'],$_CONF['site_admin_url'] . '/plugins/menubuilder/index.php?mode=config');
$navbar->set_selected($currentSelect);

$display = COM_siteHeader();
$display .= '<h1>' . $LANG_MB00['menulabel'] . '</h1>';
$display .= $navbar->generate();
$display .= $content;
$display .= COM_siteFooter();
echo $display;
exit;
?>