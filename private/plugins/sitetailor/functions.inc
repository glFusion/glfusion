<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// |                                                                          |
// | This file does two things: 1) it implements the necessary glFusion Plugin|
// | API method and 2) implements all the common code needed by the Site      |
// | Tailor plugin' PHP files.                                                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C)  2008 by the following authors:                            |
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
    die ('This file can not be used on its own.');
}

/**
* Language file Include
*/
$langfile = $_CONF['path'] . 'plugins/sitetailor/language/' . $_CONF['language'] . '.php';

if (file_exists ($langfile)) {
    include_once $langfile;
} else {
    include_once $_CONF['path'] . 'plugins/sitetailor/language/english.php';
}

require_once $_CONF['path'].'plugins/sitetailor/sitetailor.php';
require_once $_CONF['path'].'plugins/sitetailor/config.php';
require_once $_CONF['path'].'plugins/sitetailor/classes/classMenuElement.php';

global $stMenu;

function plugin_enablestatechange_sitetailor() {
    global $_TABLES;
    CACHE_remove_instance('stmenu');
    CACHE_remove_instance('css');
    $randID = rand();
    DB_save($_TABLES['vars'],'name,value',"'cacheid',$randID");
}

function plugin_getheadercss_sitetailor() {
    global $_CONF, $_ST_CONF, $stMenu, $themeAPI, $themeStyle;

    $st_header = array();
    $st_header[] = $_CONF['path_html'] . 'admin/plugins/sitetailor/css/mooRainbow.css';

    if ( is_array($stMenu) ) {
        foreach ($stMenu AS $menu) {
            if ( $menu['active'] == 1 ) {
                $cacheInstance = 'stmenu_css_' . $menu['menu_id'] . '__' . $_CONF['theme'];
                $retval = CACHE_check_instance($cacheInstance, 0);
                if ( $retval ) {
                    $st_header[] = CACHE_instance_filename($cacheInstance,0);
                } else {
                    $ms = new Template( $_CONF['path'].'plugins/sitetailor/templates/' );
                    switch ($menu['menu_type']) {
                        case 1 :    // horizontal with multi-level
                            $stylefile = 'gl_horizontal-cascading.thtml';
                            break;
                        case 2 :    // horizontal with no cascading menus
                            $stylefile = 'gl_horizontal-simple.thtml';
                            break;
                        case 3 :
                            $stylefile = 'gl_vertical-cascading.thtml';
                            break;
                        case 4 :
                            $stylefile = 'gl_vertical-simple.thtml';
                            break;
                        default :
                            $stylefile = 'gl_horizontal-cascading.thtml';
                            break;
                    }
                    $ms->set_file( array(
                        'style'        => $stylefile,
                    ));
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
                    $ms->parse ('output', 'style');
                    $fCSS = $ms->finish ($ms->get_var('output'));
                    CACHE_create_instance($cacheInstance, $fCSS, 0);
                    $st_header[] = CACHE_instance_filename($cacheInstance,0);
                }
            }
        }
    }
    return $st_header;
}

function plugin_getheaderjs_sitetailor() {
    global $_CONF, $_ST_CONF, $stMenu;

    $st_js = array();

    if ( is_array($stMenu) ) {
        foreach ($stMenu AS $menu) {
            if ( $menu['active'] == 1 && $menu['menu_type'] == 1) {
                $cacheInstance = 'stmenu_js_' . $menu['menu_id'] . '__' . $_CONF['theme'];
                $retval = CACHE_check_instance($cacheInstance, 0);
                if ( $retval ) {
                    $st_js[] = CACHE_instance_filename($cacheInstance,0);
                } else {
                    $ms = new Template( $_CONF['path'] .'plugins/sitetailor/templates/');
                    $ms->set_file('js','animate.thtml');
                    $ms->set_var('menu_id',$menu['menu_id']);
                    $ms->parse ('output', 'js');
                    $js = $ms->finish ($ms->get_var('output'));
                    CACHE_create_instance($cacheInstance, $js, 0);
                    $st_js[] = CACHE_instance_filename($cacheInstance,0);
                }
            }
        }
    }
    return $st_js;
}

function plugin_getheadercode_sitetailor() {
    global $_CONF, $_ST_CONF, $stMenu, $themeAPI;

    if ( $themeAPI > 1 ) {
        return '';
    }

    $st_header = '';

    $st_header = "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $_CONF['site_url'] . '/admin/plugins/sitetailor/css/mooRainbow.css"' . XHTML . ">" . LB;

    if ( is_array($stMenu) ) {
        foreach ($stMenu AS $menu) {
            if ( $menu['active'] == 1 ) {
                $cacheInstance = 'stmenu_css_' . $menu['menu_id'] . '__' . $_CONF['theme'];
                $retval = CACHE_check_instance($cacheInstance, 0);
                if ( $retval ) {
                    $st_header .= '<style>'.$retval.'</style>';
                } else {
                    $ms = new Template( $_CONF['path'].'plugins/sitetailor/templates/' );
                    switch ($menu['menu_type']) {
                        case 1 :    // horizontal with multi-level
                            $stylefile = 'gl_horizontal-cascading.thtml';
                            break;
                        case 2 :    // horizontal with no cascading menus
                            $stylefile = 'gl_horizontal-simple.thtml';
                            break;
                        case 3 :
                            $stylefile = 'gl_vertical-cascading.thtml';
                            break;
                        case 4 :
                            $stylefile = 'gl_vertical-simple.thtml';
                            break;
                        default :
                            $stylefile = 'gl_horizontal-cascading.thtml';
                            break;
                    }
                    $ms->set_file( array(
                        'style'        => $stylefile,
                    ));
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
                    $ms->parse ('output', 'style');
                    $fCSS = $ms->finish ($ms->get_var('output'));
                    CACHE_create_instance($cacheInstance, $fCSS, 0);
                    $st_header .= '<style>'.$fCSS.'</style>';
                }
            }
        }
    }
    $st_header .= "<script defer type=\"text/javascript\" src=\"" . $_CONF['site_url'] . '/javascript/sitetailor_ie6vertmenu.js"' . XHTML . '>' . LB;
    return $st_header;
}

/**
* This will put an option for the plugin in the command and control block on moderation.php
*
* Add the plugin name, icon and link to the command and control block in moderation.php
*
* @return   array   Array containing (plugin name, admin url, url of plugin icon)
*
*/

function plugin_cclabel_sitetailor() {
    global $_CONF, $_ST_CONF, $LANG_ST00, $LANG_ST01;

    if (SEC_hasRights('sitetailor.admin') ) {
        return array(
            array($LANG_ST01['menu_builder'],$_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php',
                  st_getImageFile('sitetailor-menubuilder.png')),
            array($LANG_ST01['logo'],$_CONF['site_admin_url'] . '/plugins/sitetailor/logo.php',
                  st_getImageFile('sitetailor.png'))
        );
    } else {
        return '';
    }
}

/**
* Returns the administrative option for this plugin
*
* Adds the plugin to the Admin menu
*
*
* @return   array   Array containing (plugin name, plugin admin url, # of items in plugin or '')
*
*/

function plugin_getadminoption_sitetailor() {
    global $_CONF, $_TABLES, $LANG_ST00, $LANG_ST01,$_ST_CONF;

    if (SEC_hasRights('sitetailor.admin') ) {
        return array(
            array($LANG_ST01['menu_builder'],$_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php',
                  DB_count($_TABLES['st_menus'])
                  ),
            array($LANG_ST01['logo'],$_CONF['site_admin_url'] . '/plugins/sitetailor/logo.php',0)
        );
    } else {
        return false;
    }
}

function plugin_chkVersion_sitetailor() {
    global $_ST_CONF;

    return $_ST_CONF['pi_version'];
}

function plugin_upgrade_sitetailor() {
    global $_CONF;

    require_once $_CONF['path'].'/plugins/sitetailor/upgrade.inc';

    return sitetailor_upgrade();
}

function plugin_autotags_sitetailor($op,$content='',$autotag='') {
    global $_CONF;

    if ($op == 'tagname' ) {
        return array('hmenu','vmenu');
    } else if ($op == 'parse') {
        /*
         * Check to see if the menu name has spaces, if so, concatenate into a single parm
         */
        if ( isset($autotag['parm2']) ) {
            $autotag['parm1'] .= ' ' . $autotag['parm2'];
        }
        switch( $autotag['tag'] ) {
            case 'hmenu' :
                $menu = st_getMenu($autotag['parm1'],"gl_moomenu","gl_moomenu",'',"parent");
                $content = str_replace($autotag['tagstr'],$menu,$content);
                break;
            case 'vmenu' :
                $menu = phpblock_getMenu('',$autotag['parm1']);
                $content = str_replace($autotag['tagstr'],$menu,$content);
                break;
        }
        return $content;  // not doing anything right now....
    }
}


function st_initMenu($skipCache=false) {
    global $stMenu,$_GROUPS, $_ST_CONF, $_TABLES, $_USER;

    $cacheInstance = 'stmenu_' . CACHE_security_hash() . '__data';
    $usedCache = 0;
    if ( $skipCache == false ) {
        $retval = CACHE_check_instance($cacheInstance, 0);
        if ( $retval ) {
            $stMenu = unserialize($retval);
            return;
        }
    }

    $mbadmin = SEC_hasRights('sitetailor.admin');
    $root    = SEC_inGroup('Root');

    if (empty($_USER['uid']) ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $result = DB_query("SELECT * FROM {$_TABLES['st_menus']}",1);
    while ( $menu = DB_fetchArray($result) ) {
        $menuID = $menu['id'];
        $stMenu[$menu['id']]['menu_name']   = $menu['menu_name'];
        $stMenu[$menu['id']]['menu_id']     = $menu['id'];
        $stMenu[$menu['id']]['active']      = $menu['menu_active'];
        $stMenu[$menu['id']]['menu_type']   = $menu['menu_type'];
        $stMenu[$menu['id']]['group_id']    = $menu['group_id'];

        if ($mbadmin || $root) {
            $stMenu[$menu['id']]['menu_perm'] = 3;
        } else {
            if ( $menu['group_id'] == 998 ) {
                if( COM_isAnonUser() ) {
                    $stMenu[$menu['id']]['menu_perm'] = 3;
                } else {
                    $stMenu[$menu['id']]['menu_perm'] =  0;
                }
            } else {
                if ( in_array( $menu['group_id'], $_GROUPS ) ) {
                    $stMenu[$menu['id']]['menu_perm'] =  3;
                }
            }
        }

        // retrieve configuration options for this menu...
        $cfgResult = DB_query("SELECT * FROM {$_TABLES['st_menus_config']} WHERE menu_id=".$menu['id']);
        while ($cfgRow = DB_fetchArray($cfgResult)) {
            $stMenu[$menu['id']]['config'][$cfgRow['conf_name']] = $cfgRow['conf_value'];
        }

        $sql = "SELECT * FROM {$_TABLES['st_menu_elements']} WHERE menu_id=".$menuID." ORDER BY element_order ASC";
        $elementResult      = DB_query( $sql, 1);
        $element            = new mbElement();
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
        $stMenu[$menuID]['elements'][0] = $element;

        while ($A = DB_fetchArray($elementResult) ) {
            $element  = new mbElement();
            $element->constructor($A,$mbadmin,$root,$_GROUPS);
            if ( $element->access > 0 ) {
                $stMenu[$menuID]['elements'][$element->id] = $element;
            }
        }
    }

    if ( is_array($stMenu) ) {
        foreach( $stMenu as $name => $menu ) {
            foreach( $stMenu[$name]['elements'] as $id => $element) {
                if ($id != 0 && isset($stMenu[$name]['elements'][$element->pid]->id) ) {
                    $stMenu[$name]['elements'][$element->pid]->setChild($id);
                }
            }
        }
    }
    CACHE_remove_instance('stmenu');
    $cacheMenu = serialize($stMenu);
    CACHE_create_instance($cacheInstance, $cacheMenu, 0);
}

/*
 * This function will return the HTML (using <ul><li></ul>) structure
 */


function st_getMenu($name='navigation',$wrapper='',$ulclass='',$liclass='',$parentclass='',$lastclass='',$selected='',$noid=0) {
    global $stMenu, $_CONF;

    $optionHash = md5($wrapper.$ulclass.$liclass.$parentclass.$lastclass.$selected);

    $cacheInstance = 'stmenu_' . $name . '_' . CACHE_security_hash() . '_' . $optionHash . '__' . $_CONF['theme'];

    $retval = CACHE_check_instance($cacheInstance, 0);
    if ( $retval && $noid == 0) {
        return $retval;
    }
    $retval = '';
    $menuID = '';
    if ( is_array($stMenu) ) {
        foreach($stMenu AS $id) {
            if ( $id['menu_name'] == $name ) {
                $menuID = $id['menu_id'];
                break;
            }
        }
    }

    if ( $menuID != '' && $stMenu[$menuID]['active'] == 1 && $stMenu[$menuID]['menu_perm'] == 3) {
        if ( $stMenu[$menuID]['menu_type'] == 1 ) {
            $retval .= '<div id="gl_moomenu'.($noid == 0 ? $menuID : '').'">'. LB;
        }
        if ( $wrapper != '' ) {
            $retval .= '<div class="'.$wrapper.($noid == 0 ? $menuID : '').'">' . LB;
        }
        $retval .= $stMenu[$menuID]['elements'][0]->showTree(0,$ulclass,$liclass,$parentclass,$lastclass,$selected);
        if ( $wrapper != '' ) {
            $retval .= '</div>' . LB;
        }
        if ( $stMenu[$menuID]['menu_type'] == 1 ) {
            $retval .= '</div>'. LB;
        }
    } else {
        return '';
    }
    if ( $noid == 0 )
        CACHE_create_instance($cacheInstance, $retval, 0);
    return $retval;
}

function phpblock_getMenu($arg1,$arg2) {
    global $stMenu, $_GROUPS, $_CONF;

    if ( is_array($stMenu) ) {
        foreach($stMenu AS $id) {
            if ( $id['menu_name'] == $arg2 ) {
                $menuID = $id['menu_id'];
                break;
            }
        }
    }
    if ( $stMenu[$menuID]['active'] != 1 || $stMenu[$menuID]['menu_perm'] == 0) {
        return;
    }

    if ( $stMenu[$menuID]['menu_type'] == 4 ) {
        $menu = st_getMenu($arg2,'st-vmenu','','','');
        if ( $menu != '' ) {
            $retval = $menu;
        } else {
            $retval = '';
        }
        return $retval;
    }
    if ( $stMenu[$menuID]['config']['menu_alignment'] == 1 ) {
        $parent = 'parent-l';
        $class  = 'gl_moomenu-vert-l';
    } else {
        $parent = 'parent-r';
        $class  = 'gl_moomenu-vert-r';
    }
    $menu = st_getMenu($arg2,$class,'','',$parent);
    if ( $menu != '' ) {
        $retval = '<div id="'.$class.$menuID.'">';
        $retval .= $menu;
        $retval .= '</div>';
    } else {
        $retval = '';
    }

    return $retval;
}

function st_getImageFile($image) {
	global $_CONF;

    return $_CONF['site_admin_url'] . '/plugins/sitetailor/images/' . $image;
}

function _stPLG_getMenuItems()
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
$result = DB_query("SELECT * FROM {$_TABLES['st_config']}",1);
if ( $result ) {
    while ($row = DB_fetchArray($result)) {
        $_ST_CONF[$row['config_name']] = $row['config_value'];
    }
}
st_initMenu();

?>