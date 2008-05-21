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

if (strpos ($_SERVER['PHP_SELF'], 'functions.inc') !== false)
{
    die ('This file can not be used on its own.');
}

// Mootools global
if (!isset($gllabsMooToolsLoaded) ) {
    $gllabsMooToolsLoaded = 0;
}

/**
* Language file Include
*/
$langfile = $_CONF['path'] . 'plugins/menubuilder/language/' . $_CONF['language'] . '.php';

if (file_exists ($langfile)) {
    include_once ($langfile);
} else {
    include_once ($_CONF['path'] . 'plugins/menubuilder/language/english.php');
}

require_once($_CONF['path'] . 'plugins/menubuilder/config.php');
require_once($_CONF['path'] . 'plugins/menubuilder/includes/classMenuElement.php');

/**
* Check to see if this Plugin uses comments
*
* @return   boolean true or false
*
*/
function plugin_commentsupport_menubuilder()
{
    return false;
}


function plugin_getheadercode_menubuilder() {
    global $_CONF, $_MB_CONF, $gllabsMooToolsLoaded;

    $mb_header = '';

    $mb_header .= LB . "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $_CONF['site_admin_url'] . '/plugins/menubuilder/css/gllabsform.css?ver=20080110"' . XHTML . ">" . LB;
    if ( $_MB_CONF['load_mootools'] != false && $gllabsMooToolsLoaded != 1) {
        $mb_header .= '<script type="text/javascript" src="' . $_CONF['site_url'] . '/mediagallery/js/mootools-release-1.11.packed.js?ver=0"></script>' . LB;
        $gllabsMooToolsLoaded = 1;
    }
    $mb_header .= '<script type="text/javascript" src="' . $_CONF['site_admin_url'] . '/plugins/menubuilder/js/fValidator.js?ver=1"></script>' . LB;
    $mb_header .= '<script type="text/javascript" src="' . $_CONF['site_admin_url'] . '/plugins/menubuilder/js/mooRainbow.js?ver=1"></script>' . LB;
    $mb_header .= LB . "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $_CONF['site_admin_url'] . '/plugins/menubuilder/css/mooRainbow.css?ver=20080110"' . XHTML . ">" . LB;
    return $mb_header;
}


/**
* This will put an option for the plugin in the command and control block on moderation.php
*
* Add the plugin name, icon and link to the command and control block in moderation.php
*
* @return   array   Array containing (plugin name, admin url, url of plugin icon)
*
*/

function plugin_cclabel_menubuilder() {
    global $_CONF, $_MB_CONF, $LANG_MB00;

    if (SEC_hasRights('menubuilder.admin') ) {
        return array($LANG_MB00['plugin'],
            $_CONF['site_admin_url'] . '/plugins/menubuilder/index.php',
            mb_getImageFile('menubuilder.png'));
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
function plugin_getadminoption_menubuilder() {
    global $_CONF, $_TABLES, $LANG_MB00, $_MB_CONF;
    if (SEC_hasRights('menubuilder.admin')) {
        return array($LANG_MB00['plugin'], $_CONF['site_admin_url'] . '/plugins/menubuilder/index.php', DB_count($_TABLES['mb_elements']));
    }
}

function plugin_chkVersion_menubuilder() {
    global $_MB_CONF;

    return $_MB_CONF['version'];
}


function plugin_upgrade_menubuilder() {
    global $_TABLES, $_CONF, $_MB_CONF, $_DB_dbms, $TEMPLATE_OPTIONS;

    include ($_CONF['path'] . '/plugins/menubuilder/upgrade.inc');

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='menubuilder'");

    switch( $currentVersion ) {
        case "0.00" :
            if ( mb_upgrade_100() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.00' WHERE pi_name='menubuilder' LIMIT 1");
            }
        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_homepage='http://www.gllabs.org' WHERE pi_name='menubuilder'",1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='" . $_MB_CONF['version'] . "' WHERE pi_name='menubuilder' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='menubuilder'") == $_MB_CONF['version']) {
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
function plugin_autouninstall_menubuilder() {
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('mb_menus,mb_elements'),
        /* give the full name of the group, as in the db */
        'groups' => array('menubuilder Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('menubuilder.admin'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars'=> array('menubuilder_gid','menubuilder_cid')
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
function plugin_uninstall_menubuilder($installCheck='') {
    global $_DB_table_prefix, $_TABLES, $LANG_MB00;

    $pi_name    = 'menubuilder';

    $FEATURES   = array ( 'menubuilder.admin');

    $TABLES     = array ( 'mb_menus','mb_elements');

    // Check and see if plugin is still enabled - if so display warning and exit
    if ($installCheck != '' && DB_getItem($_TABLES['plugins'],'pi_enabled', 'pi_name = "' .$pi_name. '"')) {
        COM_errorLog("Plugin is installed and enabled. Disable first if you want to de-install it",1);
        $display .= COM_startBlock($LANG_MB00['warning']);
        $display .= $LANG_MB00['enabled'];
        $display .= COM_endBlock();
        echo $display;
        return false;
    }

    // Ok to proceed and delete plugin

    // Unregister the plugin with Geeklog
    COM_errorLog('Attempting to unregister the Menu Editor Plugin from Geeklog',1);
    DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = 'menubuilder'",1);

    // Drop Menu Editor tables
    foreach($TABLES as $table) {
        COM_errorLog("Removing Table $table",1);
        DB_query("DROP TABLE " . $_DB_table_prefix . $table,1);
    }

    // Remove Security for this plugin
    $grp_id = DB_getItem($_TABLES['vars'], 'value', "name = '{$pi_name}_gid'");
    $cgrp_id = DB_getItem($_TABLES['vars'],'value', "name = '{$pi_name}_cid'");

    COM_errorLog("Removing $pi_name Admin Group", 1);
    DB_query("DELETE FROM {$_TABLES['groups']} WHERE grp_id = $grp_id",1);
    DB_query("DELETE FROM {$_TABLES['vars']} WHERE name = '{$pi_name}_gid'");
    DB_query("DELETE FROM {$_TABLES['groups']} WHERE grp_id = $cgrp_id",1);
    DB_query("DELETE FROM {$_TABLES['vars']} WHERE name = '{$pi_name}_cid'");

    COM_errorLog("Removing root users from admin of $pi_name");
    DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $grp_id",1);
    DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $cgrp_id",1);

    COM_errorLog("Removing comments for " . $pi_name);
    DB_query("DELETE FROM {$_TABLES['comments']} WHERE type='menubuilder'",1);

    // Remove all the associated features -- access rights
    foreach ($FEATURES as $feature) {
        COM_errorLog("Removing $feature feature and rights to it",1);
        $feat_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$feature'");
        DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $feat_id",1);
        DB_query("DELETE FROM {$_TABLES['features']} WHERE ft_id = $feat_id",1);
    }

    COM_errorLog('...success',1);
    return true;
}

function mb_initMenu() {
    global $mbMenuConfig,$_GROUPS, $_MB_CONF, $MB_elements, $_TABLES, $_USER, $_DB_dbms;

    $mbadmin = SEC_hasRights('menubuilder.admin');
    $root    = SEC_inGroup('Root');

    if (empty($_USER['uid']) ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $groups = $_GROUPS;

    // Red configuration
    $result = DB_query("SELECT * FROM {$_TABLES['mb_config']}",1);
    while ( $C = DB_fetchArray($result) ) {
        $mbMenuConfig[] = $C;
    }

    $sql = "SELECT * FROM {$_TABLES['mb_elements']} ORDER BY element_order ASC";

    $result         = DB_query( $sql, 1);
    $MB_elements    = array();

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
    $MB_elements[$element->menu_id][$element->id] = $element;

    while ($A = DB_fetchArray($result) ) {
        $element  = new mbElement();
        $element->constructor($A,$mbadmin,$root,$groups);

        if ( $element->access > 0 ) {
            if ( !isset($MB_elements[$element->menu_id][0]) ) {
                $toplevel = new mbElement();
                $toplevel->id = 0;
                $toplevel->menu_name = $element->menu_id;
                $toplevel->title = 'Top Level Menu';
                $toplevel->owner_id = $mbadmin;
                $toplevel->group_id = $root;
                if ( $mbadmin ) {
                    $element->access = 3;
                }
                $MB_elements[$toplevel->menu_id][0] = $toplevel;
            }
            $MB_elements[$element->menu_id][$element->id] = $element;
        }
    }

    foreach( $MB_elements as $name => $menu ) {
        foreach( $MB_elements[$name] as $id => $element) {
            if ($id != 0 && isset($MB_elements[$name][$element->pid]->id) ) {
                $MB_elements[$name][$element->pid]->setChild($id);
            }
        }
    }
}


/*
 * This function will return the HTML (using <ul><li></ul>) structure
 */

function mb_getMenu($name=0,$ulclass='',$liclass='',$parentclass='', $selected='') {
    global $MB_elements;

    $retval = '';

    $retval = $MB_elements[$name][0]->showTree($name,$ulclass,$liclass,$parentclass,$selected);

    return $retval;

}

function mb_buildEditTree() {
    global $MG_elements;



}


function mb_getImageFile($image) {
	global $_CONF;

    return $_CONF['site_admin_url'] . '/plugins/menubuilder/images/' . $image;
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

/*
 * Initialize the menus
 */

mb_initMenu();


//echo mb_getMenu($name='header');

// print_r($MB_elements);

// echo $MB_elements['header'][0]->showEditTree(0);
// exit;


//print_r($MB_elements);
//exit;
?>