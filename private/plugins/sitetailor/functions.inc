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

if (strpos ($_SERVER['PHP_SELF'], 'functions.inc') !== false)
{
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

require_once $_CONF['path'] . 'plugins/sitetailor/config.php';
require_once($_CONF['path'] . 'plugins/sitetailor/classes/classMenuElement.php');



function plugin_getheadercode_sitetailor() {
    global $_CONF, $_ST_CONF, $gllabsMooToolsLoaded;

    $st_header = '';

    if ( $_ST_CONF['load_mootools'] != false && $gllabsMooToolsLoaded != 1) {
        $st_header .= '<script type="text/javascript" src="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/js/mootools-release-1.11.packed.js?ver=0"></script>' . LB;
        $gllabsMooToolsLoaded = 1;
    }
//    $st_header .= '<script type="text/javascript" src="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/js/mooRainbow.js?ver=1"></script>' . LB;
    $st_header .= LB . "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $_CONF['site_admin_url'] . '/plugins/sitetailor/css/mooRainbow.css?ver=20080110"' . XHTML . ">" . LB;
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
    global $_CONF, $_ST_CONF, $LANG_ST00;

    if (SEC_hasRights('sitetailor.admin') ) {
        return array($LANG_ST00['menulabel'],
            $_CONF['site_admin_url'] . '/plugins/sitetailor/index.php',
            st_getImageFile('sitetailor.png'));
    } else {
        return '';
    }
}

/**
* Returns the administrative option for this plugin
*
* Adds the plugin to the Admin menu
*
* Universal Plugin:  Modify/Uncomment use it
*
* @return   array   Array containing (plugin name, plugin admin url, # of items in plugin or '')
*
*/
function plugin_getadminoption_sitetailor() {
    global $_CONF, $_TABLES, $LANG_ST00, $_ST_CONF;
    if (SEC_hasRights('sitetailor.admin')) {
        return array($LANG_ST00['menulabel'], $_CONF['site_admin_url'] . '/plugins/sitetailor/index.php', 'n/a');
    }
}

function plugin_chkVersion_sitetailor() {
    global $_ST_CONF;

    return $_ST_CONF['version'];
}

function plugin_upgrade_sitetailor() {
    global $_CONF;

    include ($_CONF['path'] . '/plugins/sitetailor/glupgrade.inc');

    $rc = glfusion_upgrade_sitetailor();
    if ( $rc == true ) {
        echo COM_refresh($_CONF['site_admin_url'] . 'success.php');
        exit;
    } else {
        return 3;
    }
}

/**
* Automatic uninstall function for plugins
*
* @return   array
*
* This code is automatically uninstalling the plugin.
* It passes an array to the core code function that removes
* tables, groups, features and php blocks from the tables.
* Additionally, this code can perform special actions that cannot be
* foreseen by the core code (interactions with other plugins for example)
*
*/
function plugin_autouninstall_sitetailor() {
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('st_config','st_menu_config','st_menu_elements'),
        /* give the full name of the group, as in the db */
        'groups' => array('sitetailor Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('sitetailor.admin'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars'=> array('sitetailor_gid','sitetailor_cid')
    );
    return $out;
}

/**
* Removes the data structures for this plugin from the Geeklog database.
* This routine will get called from the Plugin install program if user select De-Install or if Delete is used in the Plugin Editor.
* The Plugin Installer will also call this routine upon and install error to remove anything it has created.
* The Plugin installer will pass the optional parameter which will then double check that plugin has first been disabled.
*
* For this plugin, this routine will also remove the Block definition.
*
* Returns True if all Plugin related data is removed without error
*
* @param    string   $installCheck     Defult is blank but if set, check if plugin is disabled first
*
* @return   boolean True if successful false otherwise
*
*/
function plugin_uninstall_sitetailor($installCheck='') {
    global $_CONF;

    require_once $_CONF['path'] . '/plugins/sitetailor/install.inc';

    $rc = glfusion_uninstall_sitetailor($installCheck);
    return ( $rc );
}

function st_initMenu() {
    global $mbMenuConfig,$_GROUPS, $_ST_CONF, $ST_menuElements, $_TABLES, $_USER;

    $mbadmin = SEC_hasRights('sitetailor.admin');
    $root    = SEC_inGroup('Root');

    if (empty($_USER['uid']) ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $groups = $_GROUPS;

    // Red configuration
    $result = DB_query("SELECT * FROM {$_TABLES['st_menu_config']}",1);
    while ( $C = DB_fetchArray($result) ) {
        $mbMenuConfig[] = $C;
    }

    $sql = "SELECT * FROM {$_TABLES['st_menu_elements']} ORDER BY element_order ASC";

    $result         = DB_query( $sql, 1);
    $ST_menuElements    = array();

    $element            = new mbElement();
    $element->id        = 0;
    $element->menu_id   = 0; // 0 is header menu - more to come later
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
    $ST_menuElements[$element->menu_id][$element->id] = $element;

    while ($A = DB_fetchArray($result) ) {
        $element  = new mbElement();
        $element->constructor($A,$mbadmin,$root,$groups);

        if ( $element->access > 0 ) {
            if ( !isset($ST_menuElements[$element->menu_id][0]) ) {
                $toplevel = new mbElement();
                $toplevel->id = 0;
                $toplevel->menu_name = $element->menu_id;
                $toplevel->title = 'Top Level Menu';
                $toplevel->owner_id = $mbadmin;
                $toplevel->group_id = $root;
                if ( $mbadmin ) {
                    $element->access = 3;
                }
                $ST_menuElements[$toplevel->menu_id][0] = $toplevel;
            }
            $ST_menuElements[$element->menu_id][$element->id] = $element;
        }
    }

    foreach( $ST_menuElements as $name => $menu ) {
        foreach( $ST_menuElements[$name] as $id => $element) {
            if ($id != 0 && isset($ST_menuElements[$name][$element->pid]->id) ) {
                $ST_menuElements[$name][$element->pid]->setChild($id);
            }
        }
    }
}

/*
 * This function will return the HTML (using <ul><li></ul>) structure
 */

function st_getMenu($name=0,$ulclass='',$liclass='',$parentclass='', $selected='') {
    global $ST_menuElements, $_GROUPS, $_CONF;

    $groups = implode($_GROUPS,'');

    $hash = md5($groups);
    $cacheInstance = 'stmenu' . $name . '_' . $hash . '__' . $_CONF['theme'];

    $retval = CACHE_check_instance($cacheInstance, 0);
    if ( $retval ) {
        return $retval;
    }
    $retval = '';
    $retval = $ST_menuElements[$name][0]->showTree($name,$ulclass,$liclass,$parentclass,$selected);
    CACHE_create_instance($cacheInstance, $retval, 0);
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

/*
 * Load configuration options (if available)
 */

$result = DB_query("SELECT * FROM {$_TABLES['st_config']}",1);
if ( $result ) {
    while ($row = DB_fetchArray($result)) {
        $_ST_CONF[$row['config_name']] = $row['config_value'];
    }
}
st_initMenu();
?>