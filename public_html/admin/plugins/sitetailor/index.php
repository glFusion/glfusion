<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin                                                       |
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

function ST_logoEdit() {
    global $_CONF, $_TABLES, $_ST_CONF, $LANG_ST01;

    $retval = '';

    if ( file_exists($_CONF['path_html'] . '/images/' . $_ST_CONF['logo_name'] ) ) {
        $current_logo = '<img src="' . $_CONF['site_url'] . '/images/' . $_ST_CONF['logo_name'] . '" alt="" border="0"' . XHTML . '>';
    } else {
        $current_logo = $LANG_ST01['no_logo_graphic'];
    }

    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file( array( 'admin' => 'logo.thtml'));

    $T->set_var(array(
        'site_admin_url'        => $_CONF['site_admin_url'],
        'site_url'              => $_CONF['site_url'],
        's_form_action'         => $_CONF['site_admin_url'] . '/plugins/sitetailor/index.php',
        'xhtml'                 => XHTML,
        'graphic_logo_selected' => $_ST_CONF['use_graphic_logo'] == 1 ? ' checked="checked"' : '',
        'text_logo_selected'    => $_ST_CONF['use_graphic_logo'] == 0 ? ' checked="checked"' : '',
        'slogan_selected'       => $_ST_CONF['display_site_slogan'] == 1 ? ' checked="checked"' : '',
        'current_logo_graphic'  => $current_logo,
    ));
    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function ST_saveLogo() {
    global $_CONF, $_TABLES, $_ST_CONF, $LANG_ST01;

    $retval = 1;

    $logo   = isset($_POST['usegraphic']) ? COM_applyFilter($_POST['usegraphic'],true) : 0;
    $slogan = isset($_POST['siteslogan']) ? COM_applyFilter($_POST['siteslogan'],true) : 0;
    $logo_name = $_ST_CONF['logo_name'];

    $file = array();
    $file = $_FILES['newlogo'];
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
            if ( $imgInfo[0] > $_ST_CONF['max_logo_width'] || $imgInfo[1] > $_ST_CONF['max_logo_height'] ) {
                $retval = 4;
            } else {
                $newlogoname = 'logo' . substr(md5(uniqid(rand())),0,8) . $ext;
                $rc = move_uploaded_file($file['tmp_name'], $_CONF['path_html'] . 'images/' . $newlogoname);
                if ( $rc ) {
                    @unlink($_CONF['path_html'] . '/images/' . $_ST_CONF['logo_name']);
                    $logo_name = $newlogoname;
                }
            }
        }
    }
    DB_save($_TABLES['st_config'],"config_name,config_value","'use_graphic_logo','$logo'");
    DB_save($_TABLES['st_config'],"config_name,config_value","'display_site_slogan','$slogan'");
    DB_save($_TABLES['st_config'],"config_name,config_value","'logo_name','$logo_name'");

    $_ST_CONF['use_graphic_logo'] = $logo;
    $_ST_CONF['display_site_slogan'] = $slogan;
    $_ST_CONF['logo_name'] = $logo_name;

    return $retval;
}

function ST_displayTree( $menu_id ) {
    global $_CONF, $LANG_ST00, $_ST_CONF, $ST_menuElements, $mbMenuConfig;

    $retval = '';

    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file (array ('admin' => 'menutree.thtml'));

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'status_msg'        => $statusMsg,
        'lang_admin'        => $LANG_ST00['admin'],
        'version'           => $_ST_CONF['version'],
        'menu_tree'         => $ST_menuElements[$menu_id][0]->editTree(0,2),
        'menuid'            => $menu_id,
        'menuactive'        => $mbMenuConfig[$menu_id]['enabled'] == 1 ? ' checked="checked"' : ' ',
        'xhtml'             => XHTML,
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function ST_moveElement( $menu, $mid, $direction ) {
    global $_CONF, $_TABLES, $_ST_CONF, $ST_menuElements,$TEMPLATE_OPTIONS;

    switch ( $direction ) {
        case 'up' :
            $neworder = $ST_menuElements[$menu][$mid]->order - 11;

            DB_query("UPDATE {$_TABLES['st_menu_elements']} SET element_order=" . $neworder . " WHERE id=" . $mid);
            break;
        case 'down' :
            $neworder = $ST_menuElements[$menu][$mid]->order + 11;
            DB_query("UPDATE {$_TABLES['st_menu_elements']} SET element_order=" . $neworder . " WHERE id=" . $mid);
            break;
    }

    $pid = $ST_menuElements[$menu][$mid]->pid;
    CACHE_remove_instance('stmenu');

    st_initMenu();
    $ST_menuElements[$menu][$pid]->reorderMenu();
    st_initMenu();
    return;
}

function ST_createElement ( $menu ) {
    global $_CONF, $_TABLES, $_ST_CONF, $ST_menuElements;
    global $LANG_ST00, $LANG_ST01, $LANG_ST_TYPES, $LANG_ST_GLTYPES,$LANG_ST_GLFUNCTION;

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
    while ( $types = current($LANG_ST_TYPES) ) {
        if ( $spCount == 0 && key($LANG_ST_TYPES) == 5 ) {
            // skip it
        } else {
            $type_select .= '<option value="' . key($LANG_ST_TYPES) . '"';
            $type_select .= '>' . $types . '</option>' . LB;
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
    for( $i = 1; $i <= $num_plugins; $i++ )
    {
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

    $parent_select = '<select name="pid" id="pid">' . LB;
    $parent_select .= '<option value="0">' . $LANG_ST01['top_level'] . '</option>' . LB;
    $result = DB_query("SELECT id,element_label FROM {$_TABLES['st_menu_elements']} WHERE menu_id='" . $menu . "' AND element_type=1");
    while ($row = DB_fetchArray($result)) {
        $parent_select .= '<option value="' . $row['id'] . '">' . $row['element_label'] . '</option>' . LB;
    }
    $parent_select .= '</select>' . LB;

    $order_select = '<select id="menuorder" name="menuorder">' . LB;
    $order_select .= '<option value="0">' . $LANG_ST01['first_position'] . '</option>' . LB;
    $result = DB_query("SELECT id,element_label,element_order FROM {$_TABLES['st_menu_elements']} WHERE menu_id='" . $menu . "' AND pid=0 ORDER BY element_order ASC");
    while ($row = DB_fetchArray($result)) {
        $order_select .= '<option value="' . $row['id'] . '">' . $row['element_label'] . '</option>' . LB;
    }
    $order_select .= '</select>' . LB;

    // build group select

    $usergroups = SEC_getUserGroups(2);
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
        'form_action'       => $_CONF['site_admin_url'] . '/plugins/sitetailor/index.php',
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

function ST_saveNewMenuElement ( ) {
    global $_CONF, $_TABLES, $LANG_ST00, $_ST_CONF, $ST_menuElements,$TEMPLATE_OPTIONS, $_GROUPS;

    $meadmin = SEC_hasRights('sitetailor.admin');
    $root    = SEC_inGroup('Root');
    $groups = $_GROUPS;

    $element            = new mbElement();
    $E['menu_id']       = COM_applyFilter($_POST['menuid']);
    $menu_id            = $E['menu_id'];
    $E['pid']           = COM_applyFilter($_POST['pid'],true);
    $E['element_label'] = htmlspecialchars(strip_tags(COM_checkWords($_POST['menulabel'])));
    $E['element_type']  = COM_applyFilter($_POST['menutype'],true);

    $E['element_target']  = COM_applyFilter($_POST['urltarget']);
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
    $aorder             = DB_getItem($_TABLES['st_menu_elements'],'element_order','id=' . $aid);
    $E['element_order'] = $aorder + 1;

    $E['element_active']    = COM_applyFilter($_POST['menuactive'],true);
    $E['element_url']       = trim(COM_applyFilter($_POST['menuurl']));
    $E['group_id']          = COM_applyFilter($_POST['group'],true);

    $element->constructor( $E, $meadmin, $root, $groups );
    $element->id        = $element->createElementID($E['menu_name']);

    $element->saveElement();
    $pid = $E['pid'];
    $menuname = $E['menu_name'];

    CACHE_remove_instance('stmenu');

    st_initMenu();

    $ST_menuElements[$menu_id][$pid]->reorderMenu();

    st_initMenu();
}

function ST_editElement( $menu, $mid ) {
    global $_CONF, $_TABLES, $_ST_CONF, $ST_menuElements;
    global $LANG_ST00, $LANG_ST01, $LANG_ST_TYPES, $LANG_ST_GLTYPES,$LANG_ST_GLFUNCTION;

    $retval = '';

    // build types select

    if ( $ST_menuElements[$menu][$mid]->type == 1 ) {
        $type_select = '<select id="menutype" name="menutype" disabled="disabled">' . LB;
    } else {
        $type_select = '<select id="menutype" name="menutype">' . LB;
    }
    while ( $types = current($LANG_ST_TYPES) ) {
        $type_select .= '<option value="' . key($LANG_ST_TYPES) . '"';
        $type_select .= ($ST_menuElements[$menu][$mid]->type==key($LANG_ST_TYPES) ? ' selected="selected"' : '') . '>' . $types . '</option>' . LB;
        next($LANG_ST_TYPES);
    }
    $type_select .= '</select>' . LB;


    $glfunction_select = '<select id="glfunction" name="glfunction">' . LB;
    while ( $glfunction = current($LANG_ST_GLFUNCTION) ) {
        $glfunction_select .= '<option value="' . key($LANG_ST_GLFUNCTION) . '"';
        $glfunction_select .= ($ST_menuElements[$menu][$mid]->subtype==key($LANG_ST_GLFUNCTION) ? ' selected="selected"' : '') . '>' . $glfunction . '</option>' . LB;
        next($LANG_ST_GLFUNCTION);
    }
    $glfunction_select .= '</select>' . LB;

    $gl_select = '<select id="gltype" name="gltype">' . LB;
    while ( $gltype = current($LANG_ST_GLTYPES) ) {
        $gl_select .= '<option value="' . key($LANG_ST_GLTYPES) . '"';
        $gl_select .= ($ST_menuElements[$menu][$mid]->subtype==key($LANG_ST_GLTYPES) ? ' selected="selected"' : '') . '>' . $gltype . '</option>' . LB;
        next($LANG_ST_GLTYPES);
    }
    $gl_select .= '</select>' . LB;

    $plugin_select = '<select id="pluginname" name="pluginname">' . LB;
    $plugin_menus = _stPLG_getMenuItems(); // PLG_getMenuItems();

    $num_plugins = count($plugin_menus);
    for( $i = 1; $i <= $num_plugins; $i++ )
    {
        $plugin_select .= '<option value="' . key($plugin_menus) . '"' . ($ST_menuElements[$menu][$mid]->subtype==key($plugin_menus) ? ' selected="selected"' : '') . '>' . key($plugin_menus) . '</option>' . LB;
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
    $parent_select .= '<option value="0">' . $LANG_ST01['top_level'] . '</option>' . LB;
    $result = DB_query("SELECT id,element_label FROM {$_TABLES['st_menu_elements']} WHERE menu_id='" . $menu . "' AND element_type=1");
    while ($row = DB_fetchArray($result)) {
        $parent_select .= '<option value="' . $row['id'] . '" ' . ($ST_menuElements[$menu][$mid]->pid==$row['id'] ? 'selected="selected"' : '') . '>' . $row['element_label'] . '</option>' . LB;
    }
    $parent_select .= '</select>' . LB;

    // build group select

    $usergroups = SEC_getUserGroups(2);
    $usergroups[$LANG_ST01['non-logged-in']] = 998;
    ksort($usergroups);
    $group_select .= '<select id="group" name="group">' . LB;

    for ($i = 0; $i < count($usergroups); $i++) {
        $group_select .= '<option value="' . $usergroups[key($usergroups)] . '"';
        if ($ST_menuElements[$menu][$mid]->group_id==$usergroups[key($usergroups)] ) {
            $group_select .= ' selected="selected"';
        }
        $group_select .= '>' . key($usergroups) . '</option>' . LB;
        next($usergroups);
    }
    $group_select .= '</select>' . LB;

    $target_select = '<select id="urltarget" name="urltarget">' . LB;
    $target_select .= '<option value=""' . ($ST_menuElements[$menu][$mid]->target == "" ? ' selected="selected"' : '') . '>' . $LANG_ST01['same_window'] . '</option>' . LB;
    $target_select .= '<option value="_blank"' . ($ST_menuElements[$menu][$mid]->target == "_blank" ? ' selected="selected"' : '') . '>' . $LANG_ST01['new_window'] . '</option>' . LB;
    $target_select .= '</select>' . LB;

    if ( $ST_menuElements[$menu][$mid]->active ) {
        $active_selected = ' checked="checked"';
    } else {
        $active_selected = '';
    }

    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file( array( 'admin' => 'editelement.thtml'));

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'form_action'       => $_CONF['site_admin_url'] . '/plugins/sitetailor/index.php',
        'menulabel'         => $ST_menuElements[$menu][$mid]->label,
        'menuorder'         => $ST_menuElements[$menu][$mid]->order,
        'menuurl'           => $ST_menuElements[$menu][$mid]->url,
        'phpfunction'       => $ST_menuElements[$menu][$mid]->subtype,
        'type_select'       => $type_select,
        'gl_select'         => $gl_select,
        'plugin_select'     => $plugin_select,
        'sp_select'         => $sp_select,
        'glfunction_select' => $glfunction_select,
        'parent_select'     => $parent_select,
        'group_select'      => $group_select,
        'target_select'     => $target_select,
        'active_selected'   => $active_selected,
        'menu'              => $menu,
        'mid'               => $mid,
        'xhtml'             => XHTML,
    ));
    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function ST_saveEditMenuElement ( ) {
    global $_TABLES,$TEMPLATE_OPTIONS;

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
    $group_id   = COM_applyFilter($_POST['group'],true);
    $sql        = "UPDATE {$_TABLES['st_menu_elements']} SET pid=$pid, element_label='$label', element_type='$type', element_subtype='$subtype', element_active=$active, element_url='$url', element_target='$target', group_id=$group_id WHERE id=$id";

    DB_query($sql);

    CACHE_remove_instance('stmenu');

    st_initMenu();
}


/**
* Enable and Disable block
*/
function ST_changeActiveStatusElement ($bid_arr)
{
    global $_CONF, $_TABLES,$TEMPLATE_OPTIONS;
    // first, disable all on the requested side
    $sql = "UPDATE {$_TABLES['st_menu_elements']} SET element_active = '0' WHERE menu_id=0;";
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

    return;
}


/**
* Recursivly deletes all elements and child elements
*
*/
function ST_deleteChildElements( $id, $menu_id ){
    global $ST_menuElements, $_CONF, $_TABLES, $_USER,$TEMPLATE_OPTIONS;

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

function ST_menuConfig( $mid ) {
    global $_CONF, $_TABLES, $_ST_CONF, $ST_menuElements;
    global $LANG_ST00, $LANG_ST01, $LANG_ST_TYPES, $LANG_ST_GLTYPES,$LANG_ST_GLFUNCTION;

    $retval = '';

    $sql = "SELECT * FROM {$_TABLES['st_menu_config']} WHERE menu_id=" . $mid;
    $result = DB_query($sql);
    list($cid,$menu_id,$tmbg,$tmh,$tmt,$tmth,$smth,$smbg,$smh,$sms,$gorc,$bgimage,$hoverimage,$parentimage,$alignment,$enabled) = DB_fetchArray($result);

    $tmbgRGB    = '[' . ST_hexrgb($tmbg,'r') . ',' . ST_hexrgb($tmbg,'g') . ',' . ST_hexrgb($tmbg,'b') . ']';
    $tmhRGB     = '[' . ST_hexrgb($tmh,'r')  . ',' . ST_hexrgb($tmh,'g')  . ',' . ST_hexrgb($tmh,'b')  . ']';
    $tmtRGB     = '[' . ST_hexrgb($tmt,'r')  . ',' . ST_hexrgb($tmt,'g')  . ',' . ST_hexrgb($tmt,'b')  . ']';
    $tmthRGB    = '[' . ST_hexrgb($tmth,'r') . ',' . ST_hexrgb($tmth,'g') . ',' . ST_hexrgb($tmth,'b') . ']';
    $smthRGB    = '[' . ST_hexrgb($smth,'r') . ',' . ST_hexrgb($smth,'g') . ',' . ST_hexrgb($smth,'b') . ']';
    $smbgRGB    = '[' . ST_hexrgb($smbg,'r') . ',' . ST_hexrgb($smbg,'g') . ',' . ST_hexrgb($smbg,'b') . ']';
    $smhRGB     = '[' . ST_hexrgb($smh,'r')  . ',' . ST_hexrgb($smh,'g')  . ',' . ST_hexrgb($smh,'b')  . ']';
    $smsRGB     = '[' . ST_hexrgb($sms,'r')  . ',' . ST_hexrgb($sms,'g')  . ',' . ST_hexrgb($sms,'b')  . ']';

    $menuenabled_check = ($enabled == 1 ? ' checked="checked"' : '');
    $alignment_left_checked  = ($alignment == 1 ? ' checked="checked"' : '');
    $alignment_right_checked = ($alignment == 0 ? ' checked="checked"' : '');

    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file( array( 'admin' => 'config.thtml'));

    $T->set_var(array(
        'site_admin_url'    => $_CONF['site_admin_url'],
        'site_url'          => $_CONF['site_url'],
        'form_action'       => $_CONF['site_admin_url'] . '/plugins/sitetailor/index.php',
        'menu_id'           => $mid,
        'tmbgcolor'         => $tmbg,
        'tmbgcolorrgb'      => $tmbgRGB,
        'tmhcolor'          => $tmh,
        'tmhcolorrgb'       => $tmhRGB,
        'tmtcolor'          => $tmt,
        'tmtcolorrgb'       => $tmtRGB,
        'tmthcolor'         => $tmth,
        'tmthcolorrgb'      => $tmthRGB,
        'smthcolor'         => $smth,
        'smthcolorrgb'      => $smthRGB,
        'smbgcolor'         => $smbg,
        'smbgcolorrgb'      => $smbgRGB,
        'smhcolor'          => $smh,
        'smhcolorrgb'       => $smhRGB,
        'smscolor'          => $sms,
        'smscolorrgb'       => $smsRGB,
        'enabled'           => ($enabled == 1 ? ' checked="checked"' : ''),
        'graphics_selected' => ($gorc == 1 ? ' checked="checked"' : ''),
        'colors_selected'   => ($gorc == 0 ? ' checked="checked"' : ''),
        'menu_bg_filename'  => $bgimage,
        'menu_hover_filename' => $hoverimage,
        'menu_parent_filename' => $parentimage,
        'alignment_left_checked'    => $alignment_left_checked,
        'alignment_right_checked'   => $alignment_right_checked,
        'xhtml'             => XHTML,
    ));
    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function ST_saveMenuConfig($menu_id=0) {
    global $_CONF, $_TABLES, $_ST_CONF, $ST_menuElements, $mbMenuConfig,$TEMPLATE_OPTIONS;

    $tmbg   = COM_applyFilter($_POST['tmbg_sample']);
    $tmh    = COM_applyFilter($_POST['tmh_sample']);
    $tmt    = COM_applyFilter($_POST['tmt_sample']);
    $tmth   = COM_applyFilter($_POST['tmth_sample']);
    $smth   = COM_applyFilter($_POST['smth_sample']);
    $smbg   = COM_applyFilter($_POST['smbg_sample']);
    $smh    = COM_applyFilter($_POST['smh_sample']);
    $sms    = COM_applyFilter($_POST['sms_sample']);
    $gorc   = isset($_POST['gorc']) ? COM_applyFilter($_POST['gorc'],true) : 0;
    $malign = isset($_POST['malign']) ? COM_applyFilter($_POST['malign'],true) : 0;

    DB_query("UPDATE {$_TABLES['st_menu_config']} SET tmbg='$tmbg',tmh='$tmh',tmt='$tmt',tmth='$tmth',smth='$smth',smbg='$smbg',smh='$smh',sms='$sms',gorc='$gorc',alignment='$malign' WHERE menu_id=" . $menu_id);

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
//                $newFilename = 'menu_bg' . $ext;
                $rc = move_uploaded_file($file['tmp_name'],$_CONF['path_html'] . 'images/menu/' . $newFilename);
                if ( $rc ) {
                    @unlink($_CONF['path_html'] . '/menu/images/' . $mbMenuConfig[$menu_id]['bgimage']);
                    DB_query("UPDATE {$_TABLES['st_menu_config']} SET bgimage='$newFilename' WHERE menu_id=" . $menu_id);
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
                    @unlink($_CONF['path_html'] . '/menu/images/' . $mbMenuConfig[$menu_id]['hoverimage']);
                    DB_query("UPDATE {$_TABLES['st_menu_config']} SET hoverimage='$newFilename' WHERE menu_id=" . $menu_id);
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
                    @unlink($_CONF['path_html'] . '/menu/images/' . $mbMenuConfig[$menu_id]['parentimage']);
                    DB_query("UPDATE {$_TABLES['st_menu_config']} SET parentimage='$newFilename' WHERE menu_id=" . $menu_id);
                }
            }
        }
    }
    CACHE_remove_instance('stmenu');

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

if ( isset($_REQUEST['mid']) ) {
    $menu_id = COM_applyFilter($_REQUEST['mid'],true);
} else {
    $menu_id = 0;
}

if ( (isset($_POST['execute']) || $mode != '') && !isset($_POST['cancel']) ) {

    switch ( $mode ) {
        case 'menu' :
            // display the tree
            $content = ST_displayTree( $menu_id );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'logo' :
            $content    = ST_logoEdit ( );
            $currentSelect = $LANG_ST01['logo'];
            break;
        case 'savelogo' :
            $rc = ST_saveLogo();
            $content = COM_showMessage( $rc, 'sitetailor' );
            $content .= ST_logoEdit( );
            $currentSelect = $LANG_ST01['logo'];
            break;
        case 'new' :
            $menu = COM_applyFilter($_GET['menu'],true);
            $content = ST_createElement ( $menu );
            $currentSelect = $LANG_ST01['menu_builder']; // $LANG_ST01['add_new'];
            break;
        case 'move' :
            // do something with the direction
            $direction = COM_applyFilter($_GET['where']);
            $mid       = COM_applyFilter($_GET['mid'],true);
            $menu_id   = COM_applyFilter($_GET['menu'],true);
            ST_moveElement( $menu_id, $mid, $direction );
            $content = ST_displayTree( $menu_id );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'edit' :
            // call the editor
            $mid       = COM_applyFilter($_GET['mid'],true);
            $menu_id   = COM_applyFilter($_GET['menu'],true);
            $content = ST_editElement( $menu_id, $mid );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'saveedit' :
            ST_saveEditMenuElement();
            $content = ST_displayTree( $menu_id );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'save' :
            // save the new or edited element
            ST_saveNewMenuElement();
            $content = ST_displayTree( $menu_id );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'activate' :
            ST_changeActiveStatusElement ($_POST['enableditem']);
            st_initMenu();
            $content = ST_displayTree( $menu_id );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'delete' :
            // delete the element
            $id        = COM_applyFilter($_GET['mid'],true);
            $menu_id   = 0;
            ST_deleteChildElements( $id, $menu );
            st_initMenu();
            $content = ST_displayTree( $menu_id );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'config' :
            $content = ST_menuConfig($menu_id);
            $currentSelect = $LANG_ST01['configuration'];
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
        case 'savecfg' :
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
            echo COM_refresh($_CONF['site_admin_url'] . '/plugins/sitetailor/index.php?mode=menu&amp;mid=' . $mid);
            exit;
            break;
        case 'newmenu' :
            $content = 'We will create a new menu here';
            break;
        case 'menucolor' :
            $content = ST_menuConfig($menu_id);
            $currentSelect = $LANG_ST01['menu_colors'];
            break;
        default :
            // display the tree
            $content = ST_displayTree( $menu_id );
            $currentSelect = $LANG_ST01['menu_builder'];
            break;
    }
} else {
    // display the tree
    $content = ST_displayTree( $menu_id );
    $currentSelect = $LANG_ST01['menu_builder'];
}

/*
 * Generate navigation bar
 */


include_once $_CONF['path_system']."classes/navbar.class.php";

$navbar = new navbar;
$navbar->add_menuitem($LANG_ST01['menu_builder'],$_CONF['site_admin_url'] . '/plugins/sitetailor/index.php?mode=menu');
$navbar->add_menuitem($LANG_ST01['menu_colors'],$_CONF['site_admin_url'] . '/plugins/sitetailor/index.php?mode=menucolor');
$navbar->add_menuitem($LANG_ST01['logo'],$_CONF['site_admin_url'] . '/plugins/sitetailor/index.php?mode=logo');
$navbar->set_selected($currentSelect);

$display = COM_siteHeader();
$display .= '<noscript>' . LB;
$display .= '    <div class="pluginAlert aligncenter" style="border:1px dashed #ccc;margin-top:10px;padding:15px;">' . LB;
$display .= '    <p>' . $LANG_ST01['javascript_required'] . '</p>' . LB;
$display .= '    </div>' . LB;
$display .= '</noscript>' . LB;
$display .= '<div id="sitetailor" style="display:none;">' . LB;
$display .= '<span><img style="vertical-align:middle;padding-right:10px;float:left;" src="images/sitetailor.png" alt=""' . XHTML . '></span><h1 style="float:left">' . $LANG_ST00['menulabel'] . '</h1>';
$display .= $navbar->generate();
$display .= $content;
$display .= '</div>';
$display .= COM_siteFooter();
echo $display;
exit;
?>