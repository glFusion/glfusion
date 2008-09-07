<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | install.inc                                                              |
// |                                                                          |
// | Install / Uninstall routines                                             |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
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

// this file can't be used on its own
if (stripos ($_SERVER['PHP_SELF'], 'install.inc') !== false)
{
    die ('This file can not be used on its own.');
}

global $pi_name, $pi_version, $gl_version, $pi_url, $base_path, $NEWFEATURE;

$pi_name        = 'sitetailor';
$pi_version     = '2.0.0';
$gl_version     = '1.1.0';
$pi_url         = 'http://www.glfusion.org';
$base_path      = $_CONF['path'] . 'plugins/' . $pi_name . '/';

$NEWFEATURE = array();
$NEWFEATURE['sitetailor.admin'] =   "Site Tailor Plugin Admin Rights";

/*
 * If we are being called glFusion's install routine, there is no
 * COM_errorLog() function available.
 */

if (!function_exists('COM_errorLOG') ) {
    function COM_errorLOG() {
    }
}

function plugin_install_sitetailor($_DB_table_prefix)
{
    global $_TABLES, $_CONF, $NEWFEATURE;
    global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

    COM_errorLog("Installing the $pi_name Plugin",1);

    require_once($_CONF['path'] . 'plugins/sitetailor/config.php');
    require_once($_CONF['path'] . 'plugins/sitetailor/sql/mysql_install.php');

    $uninstall_plugin = 'plugin_remove_' . $pi_name;

    foreach ($_SQL as $table => $sql) {
        COM_errorLog("Creating $table table",1);
        DB_query($sql,1);
        if (DB_error()) {
            COM_errorLog("Error Creating $table table",1);
            $uninstall_plugin();
            return false;
            exit;
        }
        COM_errorLog("Success - Created $table table",1);
    }

    // Insert default configuration
    COM_errorLog("Inserting default data into tables",1);
    for ($i = 1; $i <= count($_SQL_DEF); $i++) {
        DB_query(current($_SQL_DEF),1);
        if (DB_error()) {
            COM_errorLog("Error inserting Menu Editor Defaults",1);
            $uninstall_plugin();
            return false;
        }
        next($_SQL_DEF);
    }
    COM_errorLog("Success - default data added to tables",1);

    // Create the plugin admin security group
    COM_errorLog("Attempting to create $pi_name admin group", 1);
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) "
        . "VALUES ('$pi_name Admin', 'Users in this group can administer the $pi_name plugin',0)",1);
    if (DB_error()) {
        $uninstall_plugin();
        return false;
        exit;
    }
    COM_errorLog('...success',1);

    COM_errorLog("Calling DB_insertID()");
    $group_id = DB_insertId();
    if ( $group_id == 0 ) {
        $lookup = $pi_name . ' Admin';
        $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='" . $lookup . "'",1);
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $group_id = $row['grp_id'];
        } else {
            COM_errorlog("ERROR: " . $pi_name . " Installation - Unable to determine group_id");
            $uninstall_plugin();
            return false;
        }
    }
    COM_errorLog("...success - group_id = " . $group_id,1);

    // Save the grp id for later uninstall
    COM_errorLog('About to save group_id to vars table for use during uninstall',1);
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_gid', $group_id)",1);
    if (DB_error()) {
        COM_errorLog("Failed to save group_id to vars table",1);
        $uninstall_plugin();
        return false;
        exit;
    }
    COM_errorLog('...success',1);

    // Add plugin Features
    foreach ($NEWFEATURE as $feature => $desc) {
        COM_errorLog("Adding $feature feature",1);
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) "
            . "VALUES ('$feature','$desc',0)",1);
        if (DB_error()) {
            COM_errorLog("Failure adding $feature feature",1);
            $uninstall_plugin();
            return false;
            exit;
        }

        $feat_id = DB_insertId();

        if ( $feat_id == 0 ) {
            $result = DB_query("SELECT * FROM {$_TABLES['features']} WHERE ft_name='$feature'");
            $nRows = DB_numRows($result);
            if ( $nRows > 0 ) {
                $row = DB_fetchArray($result);
                $feat_id = $row['ft_id'];
            } else {
                COM_errorlog("ERROR: " . $pi_name . " Installation - Unable to determine feat_id");
                $uninstall_plugin();
                return false;
            }
        }
        COM_errorLog("Success - feat_id = " . $feat_id,1);

        if ( $feature == 'sitetailor.admin' ) {
            COM_errorLog("Adding $feature feature to admin group",1);
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)",1);
            if (DB_error()) {
                COM_errorLog("Failure adding $feature feature to admin group",1);
                $uninstall_plugin();
                return false;
                exit;
            }
            COM_errorLog("Success",1);
        }
    }


    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    COM_errorLog("Attempting to give all users in Root group access to $pi_name admin group",1);
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($group_id, NULL, 1)");
    if (DB_error()) {
        COM_errorLog("Failure giving all users in Root group access");
        $uninstall_plugin();
        return false;
        exit;
    }
    COM_errorLog("Success",1);

    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1

    // Register the plugin with glFusion
    COM_errorLog("Registering $pi_name plugin with glFusion", 1);
    DB_delete($_TABLES['plugins'],'pi_name','sitetailor');
    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
        . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error()) {
        COM_errorLog("Failure registering plugin with glFusion");
        $uninstall_plugin();
        return false;
        exit;
    }

    DB_query("INSERT INTO {$_TABLES['blocks']} (is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, help, group_id, owner_id, perm_owner, perm_group, perm_members,perm_anon) VALUES (1, 'blockmenu', 'phpblock', 'Navigation', 'all', 0, '', '', 0, 1, 'phpblock_getMenu(block)','', 2, 4, 3, 2, 2, 2);",1);

    COM_errorLog("Succesfully installed the $pi_name Plugin!",1);
    return true;
}

/**
* Removes the data structures for this plugin from the glFusion database.
* This routine will get called from the Plugin install program if user select De-Install or if Delete is used in the Plugin Editor.
* The Plugin Installer will also call this routine upon and install error to remove anything it has created.
* The Plugin installer will pass the optional parameter which will then double check that plugin has first been disabled.
*
* For this plugin, this routine will also remove the Block definition.
*
* Returns True if all Plugin related data is removed without error
*
* @param    string   $installCheck     Default is blank but if set, check if plugin is disabled first
*
* @return   boolean True if successful false otherwise
*
*/
function plugin_remove_sitetailor() {
    global $_CONF, $_DB_table_prefix, $_TABLES, $LANG_ST00, $NEWFEATURE;
    global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

    $TABLES     = array ( 'st_config','st_menus','st_menu_config','st_menus_config','st_menu_elements');

    // Unregister the plugin with glFusion
    COM_errorLog('Attempting to unregister the ' . $pi_name . ' Plugin from glFusion',1);
    DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = '" . $pi_name . "'",1);

    // Drop Menu Editor tables
    foreach($TABLES as $table) {
        COM_errorLog("Removing Table $table",1);
        DB_query("DROP TABLE " . $_DB_table_prefix . $table,1);
    }

    // Remove Security for this plugin
    $grp_id  = DB_getItem($_TABLES['vars'], 'value', "name = '{$pi_name}_gid'");
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
    DB_query("DELETE FROM {$_TABLES['comments']} WHERE type='" . $pi_name . "'",1);

    // Remove all the associated features -- access rights
    foreach ($NEWFEATURE as $feature) {
        COM_errorLog("Removing $feature feature and rights to it",1);
        $feat_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$feature'");
        DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $feat_id",1);
        DB_query("DELETE FROM {$_TABLES['features']} WHERE ft_id = $feat_id",1);
    }
    COM_errorLog('...success',1);
    return true;
}
?>