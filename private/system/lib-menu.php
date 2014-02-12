<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-menu.php                                                             |
// |                                                                          |
// | glFusion menu library.                                                   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2014 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

define('MENU_TOP_LEVEL',-1);
define('MENU_HORIZONTAL_CASCADING',1);
define('MENU_HORIZONTAL_SIMPLE',2);
define('MENU_VERTICAL_CASCADING',3);
define('MENU_VERTICAL_SIMPLE',4);

define('ET_SUB_MENU',1);
define('ET_FUSION_ACTION',2);
define('ET_FUSION_MENU',3);
define('ET_PLUGIN',4);
define('ET_STATICPAGE',5);
define('ET_URL',6);
define('ET_PHP',7);
define('ET_LABEL',8);
define('ET_TOPIC',9);

define('USER_MENU',1);
define('ADMIN_MENU',2);
define('TOPIC_MENU',3);
define('STATICPAGE_MENU',4);
define('PLUGIN_MENU',5);
define('HEADER_MENU',6);

require_once $_CONF['path_system'] . 'classes/menu.class.php';

// new code here

/* reconsider the heirarchy on caching and calling each function in the tree
 * as we want to leverage building admin menus, users menus, topic menus, etc
 * throughout the system.
 */

/*
 * pull the data for a specific menu from the DB
 *
 * returns a menu object

 */
function initMenu($menuname, $skipCache=false) {
    global $_GROUPS, $_TABLES, $_USER;

    $menu = NULL;

    $cacheInstance = 'menuobject_' .$menuname . '_' . CACHE_security_hash() . '__data';
    if ( $skipCache == false ) {
        $retval = CACHE_check_instance($cacheInstance, 0);
        if ( $retval ) {
            $menu = unserialize($retval);
            return $menu ;
        }
    }
    $mbadmin = SEC_hasRights('menu.admin');
    $root    = SEC_inGroup('Root');

    if (COM_isAnonUser()) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }
    $result = DB_query("SELECT * FROM {$_TABLES['menu']} WHERE menu_active=1 AND menu_name='".DB_escapeString($menuname)."'",1);
    $menuRow = DB_fetchArray($result);
    if ( $menuRow ) {
        $menu = new menu();

        $menu->id = $menuRow['id'];
        $menu->name = $menuRow['menu_name'];
        $menu->type = $menuRow['menu_type'];
        $menu->active = $menuRow['menu_active'];
        $menu->group_id = $menuRow['group_id'];

        if ($mbadmin || $root) {
            $menu->permission = 3;
        } else {
            if ( $menuRow['group_id'] == 998 ) {
                if( COM_isAnonUser() ) {
                    $menu->permission = 3;
                } else {
                    $menu->permission =  0;
                    return NULL;
                }
            } else {
                if ( in_array( $menuRow['group_id'], $_GROUPS ) ) {
                    $menu->permission =  3;
                } else {
                    return NULL;
                }
            }
        }
        $menu->getElements();

        $cacheMenu = serialize($menu);
        CACHE_create_instance($cacheInstance, $cacheMenu, 0);
    }

    return $menu;
}

/*
 * New assembleMenu function - this one only builds the menu structe
 * into an array - it does not do any styling.
 *
 * takes a menu object and turns it into a full data structure
 *
 * first check to see if the data structure is on disk (cache)
 *
 *
 */
function assembleMenu($name, $skipCache=false) {

    $menuData = NULL;

    $lang = COM_getLanguageId();
    if (!empty($lang)) {
        $menuName = $name . '_'.$lang;
    } else {
        $menuName = $name;
    }

    $cacheInstance = 'menudata_' .$menuName . '_' . CACHE_security_hash() . '__data';

    if ( $skipCache == false ) {
        $cacheCheck = CACHE_check_instance($cacheInstance, 0);
        if ( $cacheCheck ) {
            $menuData = unserialize($cacheCheck);
            return $menuData;
        }
    }

    $menuObject = initMenu($menuName, $skipCache);
    if ( $menuObject != NULL ) {
        $menuData = $menuObject->_parseMenu();
        $menuData['type'] = $menuObject->type;
        $cacheMenu = serialize($menuData);
        CACHE_create_instance($cacheInstance, $cacheMenu, 0);
    }

    return $menuData;
}

/*
 * Render the menu
 */

function displayMenu( $menuName, $skipCache=false ) {
    global $_CONF;
$skipCache = true;
    $retval = '';

    $structure = assembleMenu($menuName, $skipCache);
    if ( $structure == NULL ) {
        return $retval;
    }

    $menuType = $structure['type'];
    unset($structure['type']);

    $T = new Template( $_CONF['path_layout'].'/menu/');

    switch ( $menuType ) {
        case MENU_HORIZONTAL_CASCADING :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        case MENU_HORIZONTAL_SIMPLE :
            $template_file = 'menu_horizontal_simple.thtml';
            break;
        case MENU_VERTICAL_CASCADING :
            $template_file = 'menu_vertical_cascading.thtml';
            break;
        case MENU_VERTICAL_SIMPLE :
            $template_file = 'menu_horizontal_simple.thtml';
            break;
        case MENU_GENERIC :
            $template_file = 'menu_generic.thtml';
            break;
        default:
            return $retval;
            break;
    }

    $T->set_file (array(
        'page'      => $template_file,
    ));

    $T->set_var('menuname',$menuName);

    $T->set_block('page', 'Elements', 'element');

    foreach($structure as $item) {

        $T->set_var(array(
                        'label' => $item['label'],
                        'url'   => $item['url']
                    ));
        if ( isset($item['children']) && $item['children'] != NULL && is_array($item['children']) ) {
            $childrenHTML = displayMenuChildren($menuType,$item['children']);
            $T->set_var('haschildren',true);
            $T->set_var('children',$childrenHTML);
        }
        $T->parse('element', 'Elements',true);
        $T->unset_var('haschildren');
        $T->unset_var('children');
    }
    $T->set_var('wrapper',true);

    $T->parse('output','page');
    $retval = $T->finish($T->get_var('output'));
    return $retval;
}

/*
 * handle the children elements when building the menu HTML
 */
function displayMenuChildren( $type, $elements ) {
    global $_CONF;

    $retval = '';

    $C = new Template( $_CONF['path_layout'].'/menu/');

    switch ( $type ) {
        case MENU_HORIZONTAL_CASCADING :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        case MENU_HORIZONTAL_SIMPLE :
            $template_file = 'menu_horizontal_simple.thtml';
            break;
        case MENU_VERTICAL_CASCADING :
            $template_file = 'menu_vertical_cascading.thtml';
            break;
        case MENU_VERTICAL_SIMPLE :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        default:
            return $retval;
            break;
    }
    $C->set_file (array(
        'page'      => $template_file,
    ));

    $C->set_block('page', 'Elements', 'element');

    foreach ($elements AS $child) {
        $C->unset_var('haschildren');

        $C->set_var(array(
                        'label' => $child['label'],
                        'url'   => $child['url']
                    ));
        if ( isset($child['children']) && $child['children'] != NULL && is_array($child['children']) ) {
            $childHTML = displayMenuChildren($type, $child['children']);
            $C->set_var('haschildren',true);
            $C->set_var('children',$childHTML);
        }
        $C->parse('element', 'Elements',true);
        $C->unset_var('haschildren');
        $C->unset_var('children');
    }
    $C->parse('output','page');
    $retval = $C->finish($C->get_var('output'));

    return $retval;
}

function phpblock_getMenu( $arg1, $arg2 )
{
    return( displayMenu($arg2) );
}

function _mbPLG_getMenuItems()
{
    global $_PLUGINS;

    $menu = array();
    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_getmenuitems_' . $pi_name;
        if (function_exists($function)) {
            $menuitems = $function();
            if (is_array ($menuitems)) {
                $url = current($menuitems);
                $label = key($menuitems);
                $mbmenu[$pi_name] = $url;
                $menu = array_merge ($menu, $mbmenu );
            }
        }
    }
    return $menu;
}

// broken now - since we don't load the array - need a better
// method to get the JS loaded per menu.

function mb_getheaderjs() {
    global $_CONF, $_USER, $mbMenu;

    $mb_js = array();
    $js = '';

    if ( is_array($mbMenu) ) {
        $cacheInstance = 'mbmenu_js' .'__' . $_USER['theme'];
        $retval = CACHE_check_instance($cacheInstance, 0);
        if ( $retval ) {
            $mb_js[] = CACHE_instance_filename($cacheInstance,0);
        } else {
            foreach ($mbMenu AS $menu) {
                if ($menu['menu_type'] == 1 /* || $menu['menu_type'] == 3 */ ) {
                    $ms = new Template( $_CONF['path_layout'] . 'menu' );
                    $ms->set_file('js','animate.thtml');
                    $ms->set_var('menu_id',$menu['menu_id']);
                    $ms->set_var('menu_name',strtolower(str_replace(" ","_",$menu['menu_name'])));
                    $ms->parse ('output', 'js');
                    $js .= $ms->finish ($ms->get_var('output')) . LB;
                }
            }
            CACHE_create_instance($cacheInstance, $js, 0);
            $mb_js[] = CACHE_instance_filename($cacheInstance,0);
        }
    }
    return $mb_js;
}

function _mb_cmp($a,$b)
{
    return strcasecmp($a['label'],$b['label']);
}

?>