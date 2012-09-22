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
// | Copyright (C) 2008-2012 by the following authors:                        |
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

        // retrieve configuration options for this menu...
        $cfgResult = DB_query("SELECT * FROM {$_TABLES['menu_config']} WHERE menu_id=". (int) $menu['id']);
        while ($cfgRow = DB_fetchArray($cfgResult)) {
            $mbMenu[$menu['id']]['config'][$cfgRow['conf_name']] = $cfgRow['conf_value'];
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
        if ( $mbMenu[$menuID]['config']['menu_alignment'] == 0 ) {
            $ulclass = $ulclass . ' ' . $ulclass.'-right';
        }
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
        if ( $mbMenu[$menuID]['config']['menu_alignment'] == 0 ) {
            $ulclass = $ulclass . ' ' . $ulclass.'-right';
        }
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
    return( mb_getMenu($arg2));
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


function mb_getheadercss() {
    global $_CONF, $_USER, $_MB_CONF, $mbMenu, $themeAPI, $themeStyle;

    $mb_header = array();
    $fCSS = '';
    $mb_header[] = $_CONF['path_layout'] . 'admin/menu/mooRainbow.css';

    if ( is_array($mbMenu) ) {
        $cacheInstance = 'mbmenu_css' . '__' . $_USER['theme'];
        $retval = CACHE_check_instance($cacheInstance, 0);
        if ( $retval ) {
            $mb_header[] = CACHE_instance_filename($cacheInstance,0);
        } else {
            foreach ($mbMenu AS $menu) {
                if ( $menu['active'] == 1 ) {
                    $ms = new Template( $_CONF['path_layout'] . 'menu' );
                    $under = 0;
                    $over = 0;
                    switch ($menu['menu_type']) {
                        case 1 :
                            $stylefile = 'horizontal-cascading.css';
                            $under = 50;
                            $over = 51;
                            break;
                        case 2 :
                            $stylefile = 'horizontal-simple.css';
                            break;
                        case 3 :
                            $stylefile = 'vertical-cascading.css';
                            $under = 40;
                            break;
                        case 4 :
                            $stylefile = 'vertical-simple.css';
                            break;
                        default :
                            $stylefile = 'horizontal-cascading.css';
                            break;
                    }
                    $ms->set_file('style',$stylefile);
                    $ms->set_var('menu_id',$menu['menu_id']);
                    $ms->set_var('menuname','.menu_'.$menu['menu_name']);
                    if ( isset($menu['config']) && is_array($menu['config']) ) {
                        foreach ($menu['config'] AS $name => $value ) {
                            if ( $value != '' ) {
                                $ms->set_var($name,$value);
                            }
                        }
                    }
                    if ( isset($menu['config']['menu_alignment']) && $menu['config']['menu_alignment'] == 1 ) {
                        $ms->set_var('alignment','left');
                        $ms->set_var('right_align','');
                    } else {
                        $ms->set_var('alignment','right');
                        $ms->set_var('right_align',true);
                    }
                    if ( $menu['menu_id'] < 4 ) {
                        $under *= 10;
                        $over *= 10;
                    }
                    if ( $under > 0 ) {
                        $ms->set_var('under', $under);
                    } else {
                        $ms->set_var('under',50);
                    }
                    if ( $over > 0 ) {
                        $ms->set_var('over', $over);
                    } else {
                        $ms->set_var('over',50);
                    }
                    if ( isset($menu['config']['ch_menu_element_width']) ) {
                        if ( strstr($menu['config']['ch_menu_element_width'],'em') !== FALSE ) {
                            $emwidth = intval($menu['config']['ch_menu_element_width']);
                            $pxwidth = (int) ($emwidth * 12.16);
                            $ms->set_var('iefixwidth',$pxwidth);
                        }
                    }
                    $ms->parse ('output', 'style');
                    $fCSS .= $ms->finish ($ms->get_var('output')) . LB;
                }
            }
            CACHE_create_instance($cacheInstance, $fCSS, 0);
            $mb_header[] = CACHE_instance_filename($cacheInstance,0);
        }
    }
    return $mb_header;
}

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