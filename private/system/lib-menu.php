<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-menu.php                                                             |
// |                                                                          |
// | glFusion menu library.                                                   |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
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
define('MENU_GENERIC',5);

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
    $sql = "SELECT * FROM {$_TABLES['menu']} WHERE menu_active=1 AND menu_name='".DB_escapeString($menuname)."'";
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
        $sql = "SELECT * FROM {$_TABLES['menu_elements']} WHERE menu_id=".(int) $menu->id." AND element_active = 1 ORDER BY element_order ASC";
        $elementResult      = DB_query( $sql, 1);
        $element            = new menuElement();
        $element->id        = 0;
        $element->menu_id   = $menu->id;
        $element->label     = 'Top Level Menu';
        $element->type      = -1;
        $element->pid       = 0;
        $element->order     = 0;
        $element->url       = '';
        $element->owner_id  = $mbadmin;
        $element->group_id  = $root;
        if ( $mbadmin ) {
            $element->access = 3;
        }
        $menu->menu_elements[0] = $element;

        while ($A = DB_fetchArray($elementResult) ) {
            $element  = new menuElement();
            $element->constructor($A,$mbadmin,$root,$_GROUPS);
            if ( $element->access > 0 ) {
                $menu->menu_elements[$A['id']] = $element;
            }
        }
        if ( isset($menu->menu_elements) && is_array( $menu->menu_elements ) ) {
            foreach($menu->menu_elements as $element) {
                if ( $element->id != 0 && is_object($menu->menu_elements[$element->pid]) ) {
                    $menu->menu_elements[$element->pid]->setChild($element);
                }
            }
        }
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
        $menuData = $menuObject->_parseMenu($menuObject->menu_elements[0]);
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

    $retval = '';

    $structure = assembleMenu($menuName, $skipCache);
    if ( $structure == NULL ) {
        return $retval;
    }

    $T = new Template( $_CONF['path_layout'].'/menu/');

    switch ( $structure['type'] ) {
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

    // should probably get the name of the menu so we can pass it too...

    $T->set_block('page', 'Elements', 'element');

    foreach($structure['children'] AS $item ) {
        $T->set_var(array(
                        'label' => $item['label'],
                        'url'   => $item['url']
                    ));
        if ( $item['children'] != NULL && is_array($item['children']) ) {
            $childrenHTML = displayMenuChildren($structure['type'],$item['children']);
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
        case MENU_GENERIC :
            $template_file = 'menu_generic.thtml';
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

// OLD CODE



function mb_initMenu($skipCache=false) {
    global $mbMenu,$_GROUPS, $_TABLES, $_USER;

    $cacheInstance = 'mbmenu_menu_' . CACHE_security_hash() . '__data';
    $usedCache = 0;
    if ( $skipCache == false ) {
        $retval = CACHE_check_instance($cacheInstance, 0);
        if ( $retval ) {
            $mbMenu = unserialize($retval);
            return;
        }
    }

    $mbadmin = SEC_hasRights('menu.admin');
    $root    = SEC_inGroup('Root');

    if (COM_isAnonUser()) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $result = DB_query("SELECT * FROM {$_TABLES['menu']} WHERE menu_active=1",1);
    while ( $menu = DB_fetchArray($result) ) {
        $menuID = $menu['id'];
        $mbMenu[$menu['id']]['menu_name']   = $menu['menu_name'];
        $mbMenu[$menu['id']]['menu_id']     = $menu['id'];
        $mbMenu[$menu['id']]['active']      = $menu['menu_active'];
        $mbMenu[$menu['id']]['menu_type']   = $menu['menu_type'];
        $mbMenu[$menu['id']]['group_id']    = $menu['group_id'];

        if ($mbadmin || $root) {
            $mbMenu[$menu['id']]['menu_perm'] = 3;
        } else {
            if ( $menu['group_id'] == 998 ) {
                if( COM_isAnonUser() ) {
                    $mbMenu[$menu['id']]['menu_perm'] = 3;
                } else {
                    $mbMenu[$menu['id']]['menu_perm'] =  0;
                }
            } else {
                if ( in_array( $menu['group_id'], $_GROUPS ) ) {
                    $mbMenu[$menu['id']]['menu_perm'] =  3;
                }
            }
        }

        $sql = "SELECT * FROM {$_TABLES['menu_elements']} WHERE menu_id=".(int) $menuID." AND element_active = 1 ORDER BY element_order ASC";
        $elementResult      = DB_query( $sql, 1);
        $element            = new menuElement();
        $element->id        = 0;
        $element->menu_id   = $menuID;
        $element->label     = 'Top Level Menu';
        $element->type      = -1;
        $element->pid       = 0;
        $element->order     = 0;
        $element->url       = '';
        $element->owner_id  = $mbadmin;
        $element->group_id  = $root;
        if ( $mbadmin ) {
            $element->access = 3;
        }
        $mbMenu[$menuID]['elements'][0] = $element;

        while ($A = DB_fetchArray($elementResult) ) {
            $element  = new menuElement();
            $element->constructor($A,$mbadmin,$root,$_GROUPS);
            if ( $element->access > 0 ) {
                $mbMenu[$menuID]['elements'][$element->id] = $element;
            }
        }
    }

    if ( is_array($mbMenu) ) {
        foreach( $mbMenu as $name => $menu ) {
            foreach( $mbMenu[$name]['elements'] as $id => $element) {
                if ($id != 0 && isset($mbMenu[$name]['elements'][$element->pid]->id) ) {
                    $mbMenu[$name]['elements'][$element->pid]->setChild($id);
                }
            }
        }
    }
    $cacheMenu = serialize($mbMenu);
    CACHE_create_instance($cacheInstance, $cacheMenu, 0);
}

/*
 * This function will return the HTML (using <ul><li></ul>) structure
 */

function mb_getMenu($name, $selected='') {
    global $mbMenu, $menuStyles, $_CONF, $_USER;

    $retval  = '';
    $menuID  = '';
    $id      = '';
    $wrapper = '';

    $lang = COM_getLanguageId();
    if (!empty($lang)) {
        $menuName = $name . '_'.$lang;
    } else {
        $menuName = $name;
    }
    if ( is_array($mbMenu) ) {
        foreach($mbMenu AS $id) {
           if ( strcasecmp(trim($id['menu_name']), trim($menuName)) == 0 ) {
                $menuID = $id['menu_id'];
                break;
            }
        }
    }

    if ( $menuID == '' ) {
        return;
    }

    $defaultStyles = array(
        'horizontal_cascading'  => array('ulclass' => 'menu-horizontal-cascading',
                                         'liclass' => '',
                                         'parentclass' => 'parent',
                                         'lastclass' => '',
                                         'selclass' => ''
                                         ),
        'horizontal_simple'     => array('ulclass' => 'menu-horizontal-simple',
                                         'liclass' => '',
                                         'parentclass' => 'parent',
                                         'lastclass' => 'last',
                                         'selclass' => ''
                                         ),
        'vertical_cascading'    => array('ulclass' => 'menu-vertical-cascading',
                                         'liclass' => '',
                                         'parentclass' => 'parent',
                                         'lastclass' => '',
                                         'selclass' => ''
                                        ),
        'vertical_simple'       => array('ulclass' => 'menu-vertical-simple',
                                         'liclass' => '',
                                         'parentclass' => '',
                                         'lastclass' => '',
                                         'selclass' => ''
                                         )
    );
    if (!isset($menuStyles) || !is_array($menuStyles)) {
        $menuStyles = $defaultStyles;
    }

    if ( $mbMenu[$menuID]['menu_type']  == MENU_HORIZONTAL_CASCADING ) {
        $ulclass     = $menuStyles['horizontal_cascading']['ulclass'];
        $liclass     = $menuStyles['horizontal_cascading']['liclass'];
        $parentclass = $menuStyles['horizontal_cascading']['parentclass'];
        $lastclass   = $menuStyles['horizontal_cascading']['lastclass'];
        $selclass    = $menuStyles['horizontal_cascading']['selclass'];
//        if ( $mbMenu[$menuID]['config']['menu_alignment'] == 0 ) {
//            $ulclass = $ulclass . ' ' . $ulclass.'-right';
//        }
    } else if ($mbMenu[$menuID]['menu_type'] == MENU_HORIZONTAL_SIMPLE ) {
        $ulclass    = $menuStyles['horizontal_simple']['ulclass'];
        $liclass    = $menuStyles['horizontal_simple']['liclass'];
        $parentclass = $menuStyles['horizontal_simple']['parentclass'];
        $lastclass  = $menuStyles['horizontal_simple']['lastclass'];
        $selclass   = $menuStyles['horizontal_simple']['selclass'];
    } else if ($mbMenu[$menuID]['menu_type'] == MENU_VERTICAL_CASCADING ) {
        $wrapper    = $menuStyles['vertical_cascading']['ulclass'];
        $ulclass    = $menuStyles['vertical_cascading']['ulclass'];
        $liclass    = $menuStyles['vertical_cascading']['liclass'];
        $parentclass = $menuStyles['vertical_cascading']['parentclass'];
        $lastclass  = $menuStyles['vertical_cascading']['lastclass'];
        $selclass   = $menuStyles['vertical_cascading']['selclass'];
//        if ( $mbMenu[$menuID]['config']['menu_alignment'] == 0 ) {
//            $ulclass = $ulclass . ' ' . $ulclass.'-right';
//        }
    } else if ($mbMenu[$menuID]['menu_type'] == MENU_VERTICAL_SIMPLE ) {
        $ulclass    = $menuStyles['vertical_simple']['ulclass'];
        $liclass    = $menuStyles['vertical_simple']['liclass'];
        $parentclass = $menuStyles['vertical_simple']['parentclass'];
        $lastclass  = $menuStyles['vertical_simple']['lastclass'];
        $selclass   = $menuStyles['vertical_simple']['selclass'];
    }

    $noSpaceName = strtolower(str_replace(" ","_",$menuName));

    // check the cache
    $cacheInstance = 'mbmenu_' . $_USER['uid'].'_'.$noSpaceName . '_' . CACHE_security_hash() . '__' . $_USER['theme'];
    $retval = CACHE_check_instance($cacheInstance, 0);
    if ( $retval != '' ) {
        return $retval;
    }
    if ( $menuID != '' && $mbMenu[$menuID]['active'] == 1 && $mbMenu[$menuID]['menu_perm'] == 3) {
        $retval .= '<div id="menu_' .$noSpaceName.'" class="menu_'.$noSpaceName.'">';

        if ( $wrapper != '' ) {
            $retval .= '<div class="'.$wrapper.'">' . LB;
        }

        $retval .= $mbMenu[$menuID]['elements'][0]->showTree(0,$ulclass,$liclass,$parentclass,$lastclass,$selected);

        if ( $wrapper != '' ) {
            $retval .= '</div>' . LB;
        }
        $retval .= '</div><div style="clear:both;"></div>';

    } else {
        return '';
    }
    CACHE_create_instance($cacheInstance, $retval, 0);

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