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
// | Copyright (C) 2008-2011 by the following authors:                        |
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

    $result = DB_query("SELECT * FROM {$_TABLES['menu']}",1);
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

        $sql = "SELECT * FROM {$_TABLES['menu_elements']} WHERE menu_id=".(int) $menuID." ORDER BY element_order ASC";
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


function mb_getMenu($name='navigation',$wrapper='',$ulclass='',$liclass='',$parentclass='',$lastclass='',$selected='',$noid=0) {
    global $mbMenu, $menuStyles, $_CONF, $_USER;

    $retval = '';
    $menuID = '';

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
    if ( $menuID != '' ) {

        if ( isset($menuStyles) && is_array($menuStyles) ) {
            if ( $mbMenu[$menuID]['menu_type']  == 1 ) {
                $ulclass     = $menuStyles['horizontal_cascading']['ulclass'];
                $liclass     = $menuStyles['horizontal_cascading']['liclass'];
                $parentclass = $menuStyles['horizontal_cascading']['parentclass'];
                $lastclass   = $menuStyles['horizontal_cascading']['lastclass'];
                $selected    = $menuStyles['horizontal_cascading']['selected'];
                $noid        = $menuStyles['horizontal_cascading']['noid'];
                if ( $mbMenu[$menuID]['config']['menu_alignment'] == 0 ) {
                    $ulclass = $ulclass . ' ' . $ulclass.'-right';
                }
            } else if ($mbMenu[$menuID]['menu_type'] == 2 ) {
                $ulclass    = $menuStyles['horizontal_simple']['ulclass'];
                $liclass    = $menuStyles['horizontal_simple']['liclass'];
                $parentclass = $menuStyles['horizontal_simple']['parentclass'];
                $lastclass  = $menuStyles['horizontal_simple']['lastclass'];
                $selected   = $menuStyles['horizontal_simple']['selected'];
            } else if ($mbMenu[$menuID]['menu_type'] == 3 ) {
                $ulclass    = $menuStyles['vertical_cascading']['ulclass'];
                $liclass    = $menuStyles['vertical_cascading']['liclass'];
                $parentclass = $menuStyles['vertical_cascading']['parentclass'];
                $lastclass  = $menuStyles['vertical_cascading']['lastclass'];
                $selected   = $menuStyles['vertical_cascading']['selected'];
                if ( $mbMenu[$menuID]['config']['menu_alignment'] == 0 ) {
                    $ulclass = $ulclass . ' ' . $ulclass.'-right';
                }
            } else if ($mbMenu[$menuID]['menu_type'] == 4 ) {
                $ulclass    = $menuStyles['vertical_simple']['ulclass'];
                $liclass    = $menuStyles['vertical_simple']['liclass'];
                $parentclass = $menuStyles['vertical_simple']['parentclass'];
                $lastclass  = $menuStyles['vertical_simple']['lastclass'];
                $selected   = $menuStyles['vertical_simple']['selected'];
            }
            $api = 2;
        } else {
            // put default old styles here;
            switch ( $mbMenu[$menuID]['menu_type'] ) {
                case 1 :
                    $wrapper = "gl_moomenu";
                    $ulclass = "gl_moomenu";
                    $liclass = '';
                    $parentclass = 'parent';
                    $lastclass = '';
                    $selected = '';
                    $noid = 0;
                    break;
                case 2 :
                    $wrapper = 'st-fmenu';
                    $ulclass = '';
                    $liclass = '';
                    $parentclass = 'parent';
                    $lastclass = 'st-f-last';
                    $selected = '';
                    $noid = 0;
                    break;
                case 3:
                    $wrapper = 'gl_moomenu-vert';
                    $ulclass = 'gl_moomenu-vert';
                    $liclass = '';
                    $parentclass = 'parent-l';
                    $lastclass = '';
                    $selected = '';
                    $noid = 0;
                    break;
                case 4 :
                    $wrapper = 'st-vmenu';
                    $ulclass = '';
                    $liclass = '';
                    $parentclass = '';
                    $lastclass = '';
                    $selected = '';
                    $noid = 0;
                    break;
            }
            $api = 1;
        }
    } else {
        return ''; // menu not found.
    }

    $optionHash = md5($menuName.$wrapper);

    $cacheInstance = 'mbmenu_' . $_USER['uid'].'_'.$menuName . '_' . CACHE_security_hash() . '_' . $optionHash . '__' . $_USER['theme'];
    $retval = CACHE_check_instance($cacheInstance, 0);
    if ( $retval != '' ) {
        return $retval;
    }

    if ( $menuID != '' && $mbMenu[$menuID]['active'] == 1 && $mbMenu[$menuID]['menu_perm'] == 3) {

        if ( $mbMenu[$menuID]['menu_type'] == 1 && $api == 1) {
            $retval .= '<div id="gl_moomenu'.($noid == 0 ? $menuID : '').'">'. LB;
        }

        $retval .= '<div class="menu_'.$menuID.'">';

        if ( $wrapper != '' ) {
            $retval .= '<div class="'.$wrapper.($noid == 0 ? $menuID : '').'">' . LB;
        }

        $retval .= $mbMenu[$menuID]['elements'][0]->showTree(0,$ulclass,$liclass,$parentclass,$lastclass,$selected,$noid);

        if ( $wrapper != '' ) {
            $retval .= '</div>' . LB;
        }

        if ( $mbMenu[$menuID]['menu_type'] == 1 && $api == 1) {
            $retval .= '</div>'. LB;
        }

        $retval .= '</div><div style="clear:both;"></div>';

    } else {
        return '';
    }
    if ( $noid == 0 )
        CACHE_create_instance($cacheInstance, $retval, 0);

    return $retval;
}

function phpblock_getMenu( $arg1, $arg2 ) {
    global $mbMenu, $menuStyles, $_GROUPS, $_CONF;

    $retval = '';
    $menuID = '';

    $lang = COM_getLanguageId();
    if (!empty($lang)) {
        $menuName = $arg2 . '_'.$lang;
    } else {
        $menuName = $arg2;
    }
    if ( is_array($mbMenu) ) {
        foreach($mbMenu AS $id) {
           if ( strcasecmp(trim($id['menu_name']), trim($menuName)) == 0 ) {
                $menuID = $id['menu_id'];
                break;
            }
        }
    }

    if ( $menuID != '' ) {

        if ( isset($menuStyles) && is_array($menuStyles) ) {
            if ( $mbMenu[$menuID]['menu_type']  == 1 ) {
                $ulclass    = $menuStyles['horizontal_cascading']['ulclass'];
                $liclass    = $menuStyles['horizontal_cascading']['liclass'];
                $parentclass = $menuStyles['horizontal_cascading']['parentclass'];
                $lastclass  = $menuStyles['horizontal_cascading']['lastclass'];
                $selected   = $menuStyles['horizontal_cascading']['selected'];
            } else if ($mbMenu[$menuID]['menu_type'] == 2 ) {
                $ulclass    = $menuStyles['horizontal_simple']['ulclass'];
                $liclass    = $menuStyles['horizontal_simple']['liclass'];
                $parentclass = $menuStyles['horizontal_simple']['parentclass'];
                $lastclass  = $menuStyles['horizontal_simple']['lastclass'];
                $selected   = $menuStyles['horizontal_simple']['selected'];
            } else if ($mbMenu[$menuID]['menu_type'] == 3 ) {
                $ulclass    = $menuStyles['vertical_cascading']['ulclass'];
                $liclass    = $menuStyles['vertical_cascading']['liclass'];
                $parentclass = $menuStyles['vertical_cascading']['parentclass'];
                $lastclass  = $menuStyles['vertical_cascading']['lastclass'];
                $selected   = $menuStyles['vertical_cascading']['selected'];
            } else if ($mbMenu[$menuID]['menu_type'] == 4 ) {
                $ulclass    = $menuStyles['vertical_simple']['ulclass'];
                $liclass    = $menuStyles['vertical_simple']['liclass'];
                $parentclass = $menuStyles['vertical_simple']['parentclass'];
                $lastclass  = $menuStyles['vertical_simple']['lastclass'];
                $selected   = $menuStyles['vertical_simple']['selected'];
            }
            $api = 2;
        } else {
            // put default old styles here;
            switch ( $mbMenu[$menuID]['menu_type'] ) {
                case 1 :
                    $wrapper = "gl_moomenu";
                    $ulclass = "gl_moomenu";
                    $liclass = '';
                    $parentclass = 'parent';
                    $lastclass = '';
                    $selected = '';
                    break;
                case 2 :
                    $wrapper = 'footer';
                    $ulclass = 'st-fmenu';
                    $liclass = '';
                    $parentclass = '';
                    $lastclass = 'st-f-last';
                    $selected = '';
                    break;
                case 3:
                    $wrapper = 'gl_moomenu-vert';
                    $ulclass = 'gl_moomenu-vert';
                    $liclass = '';
                    $parentclass = 'parent';
                    $lastclass = '';
                    $selected = '';
                    break;
                case 4 :
                    $wrapper = 'st-vmenu';
                    $ulclass = '';
                    $liclass = '';
                    $parentclass = '';
                    $lastclass = '';
                    $selected = '';
                    break;
            }
            $api = 1;
        }
    } else {
        return ''; // menu not found.
    }

    if ( $mbMenu[$menuID]['active'] != 1 || $mbMenu[$menuID]['menu_perm'] == 0) {
        return;
    }

    if ( $mbMenu[$menuID]['menu_type'] == 4 ) {
        $menu = mb_getMenu($arg2,'st-vmenu','','','');
        if ( $menu != '' ) {
            $retval = $menu;
        } else {
            $retval = '';
        }
        return $retval;
    }
    if ( $api == 1 ) {
        if ( $mbMenu[$menuID]['config']['menu_alignment'] == 1 ) {
            $parent = $parentclass . '-l'; //'parent-l';
            $class  = $ulclass . '-l'; //'gl_moomenu-vert-l';
        } else {
            $parent = $parentclass . '-r'; //'parent-r';
            $class  = $ulclass . '-r'; //'gl_moomenu-vert-r';
        }
    } else {
        $parent = $parentclass;
        $class = $ulclass;
        if ( $mbMenu[$menuID]['config']['menu_alignment'] == 0 ) {
            $class = $class.' '.$class.'-right';
        }
    }

    $menu = mb_getMenu($arg2,$class,'','',$parent,'','',1);

    if ( $menu != '' ) {
        $retval = '<div id="'.$class.$menuID.'">';
        $retval .= $menu;
        $retval .= '</div>';
    } else {
        $retval = '';
    }

    return $retval;
}
/* -------------
function mb_getImageFile($image) {
	global $_CONF;

    return $_CONF['site_admin_url'] . '/plugins/sitetailor/images/' . $image;
}
-------------- */
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

    $st_header = array();
    $fCSS = '';
    $st_header[] = $_CONF['path_layout'] . 'admin/menu/mooRainbow.css';

    if ( is_array($mbMenu) ) {
        $cacheInstance = 'mbmenu_css' . '__' . $_USER['theme'];
        $retval = CACHE_check_instance($cacheInstance, 0);
        if ( $retval ) {
            $st_header[] = CACHE_instance_filename($cacheInstance,0);
        } else {
            foreach ($mbMenu AS $menu) {
                if ( $menu['active'] == 1 ) {
                    $ms = new Template( $_CONF['path_layout'] . 'menu' );
                    $under = 0;
                    $over = 0;
                    switch ($menu['menu_type']) {
                        case 1 :    // horizontal with multi-level
                            $stylefile = 'horizontal-cascading.thtml';
                            $under = 50;
                            $over = 51;
                            break;
                        case 2 :    // horizontal with no cascading menus
                            $stylefile = 'horizontal-simple.thtml';
                            break;
                        case 3 :
                            $stylefile = 'vertical-cascading.thtml';
                            $under = 40;
                            break;
                        case 4 :
                            $stylefile = 'vertical-simple.thtml';
                            break;
                        default :
                            $stylefile = 'horizontal-cascading.thtml';
                            break;
                    }
                    $ms->set_file('style',$stylefile);
                    $ms->set_var('menu_id',$menu['menu_id']);
                    if ( is_array($menu['config']) ) {
                        foreach ($menu['config'] AS $name => $value ) {
                            if ( $name == 'use_images' && $value == 0 ) {
                                continue;
                            }
                            $ms->set_var($name,$value);
                        }
                    }
                    if ( $menu['config']['menu_alignment'] == 1 ) {
                        $ms->set_var('alignment','left');
                    } else {
                        $ms->set_var('alignment','right');
                    }
                    if ( $themeAPI > 1 ) {
                        $ms->set_var('themeapi','');
                    } else {
                        $ms->set_var('themeapi','1');
                    }
                    if ( $themeStyle == 'table' ) {
                        $ms->set_var('themeapi','1');
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

                    $ms->parse ('output', 'style');
                    $fCSS .= $ms->finish ($ms->get_var('output')) . LB;
                }
            }
            CACHE_create_instance($cacheInstance, $fCSS, 0);
            $st_header[] = CACHE_instance_filename($cacheInstance,0);
        }
    }
    return $st_header;
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

//mb_initMenu();

?>