<?php
// +---------------------------------------------------------------------------+
// | FileMgmt Plugin                                                           |
// | glFusion auto install / uninstall routines                                |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                              |
// |                                                                           |
// | Author: Mark R. Evans - mark@gllabs.org                                   |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

// this file can't be used on its own
if (strpos ($_SERVER['PHP_SELF'], 'install.inc') !== false)
{
    die ('This file can not be used on its own.');
}

function plugin_install_filemgmt($_DB_table_prefix)
{
    global $_TABLES, $_CONF, $_FM_TABLES;

    require_once($_CONF['path'] . '/plugins/filemgmt/config.php');

    $pi_name = 'filemgmt';                    // Plugin name
    $pi_version = $CONF_FM['version'];        // Plugin Version
    $gl_version = '1.4';                      // GL Version plugin for
    $pi_url = 'http://www.portalparts.com';   // Plugin Homepage

    // Default data
    // Insert table name and sql to insert default data for your plugin.

    $DEFVALUES = array();
    $NEWFEATURE = array();
    $NEWFEATURE['filemgmt.user']   = "filemgmt Access";
    $NEWFEATURE['filemgmt.edit']   = "filemgmt Admin Rights";
    $NEWFEATURE['filemgmt.upload'] = "filemgmt File Upload Rights";

    $uninstall_plugin = 'plugin_uninstall_' . $pi_name;

    // Create the Plugins Tables
    require_once($_CONF['path'] . 'plugins/filemgmt/sql/filemgmt_sql_install.php');
    for ($i = 1; $i <= count($_SQL); $i++) {
        $progress .= "executing " . current($_SQL) . "<br>\n";
        DB_query(current($_SQL),'1');
        if (DB_error()) {
            $uninstall_plugin ('DeletePlugin');
            return false;
            exit;
        }
        next($_SQL);
    }

    // Insert Default Data

    foreach ($DEFVALUES as $table => $sql) {
        DB_query($sql,1);
        if (DB_error()) {
            $uninstall_plugin ();
            return false;
            exit;
        }
    }

    // Create the plugin admin security group
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr) "
        . "VALUES ('$pi_name Admin', 'Users in this group can administer the $pi_name plugin')",1);
    if (DB_error()) {
        $uninstall_plugin ();
        return false;
        exit;
    }
    $group_id = DB_insertId();

    // Save the grp id for later uninstall
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_admingrp_id', $group_id)",1);
    if (DB_error()) {
        $uninstall_plugin ();
        return false;
        exit;
    }

    // Add plugin Features
    foreach ($NEWFEATURE as $feature => $desc) {
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr) "
            . "VALUES ('$feature','$desc')",1);
        if (DB_error()) {
            $uninstall_plugin ();
            return false;
            exit;
        }
        $feat_id = DB_insertId();
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)");
        if (DB_error()) {
            $uninstall_plugin ();
            return false;
            exit;
        }
    }

    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($group_id, NULL, 1)");
    if (DB_error()) {
        $uninstall_plugin ();
        return false;
        exit;
    }

    $fmconfigdist_path = $_CONF['path'] . '/plugins/filemgmt/filemgmt.php.dist';
    $fmconfig_path = $_CONF['path'] . '/plugins/filemgmt/filemgmt.php';

    // Read the distribution file
    $fmconfig_file = fopen($fmconfigdist_path, 'r');
    $fmconfig_data = fread($fmconfig_file, filesize($fmconfigdist_path));
    fclose($fmconfig_file);

    // Replace the values with the new ones
    $fmconfig_data = str_replace("##FILESTORE##", $_CONF['path_html'] . "/filemgmt_data/files/", $fmconfig_data); // Filestore
    $fmconfig_data = str_replace("##SNAPSTORE##", $_CONF['path_html'] . "/filemgmt_data/snaps/", $fmconfig_data); // Filestore
    $fmconfig_data = str_replace("##SNAPCAP##", $_CONF['path_html'] . "/filemgmt_data/category_snaps/", $fmconfig_data); // Filestore
    $fmconfig_data = str_replace("##STOREURL##", $_CONF['site_url'] . "/filemgmt_data/files/", $fmconfig_data); // Filestore
    $fmconfig_data = str_replace("##SNAPURL##", $_CONF['site_url'] . "/filemgmt_data/snaps/", $fmconfig_data); // Filestore
    $fmconfig_data = str_replace("##CATURL##", $_CONF['site_url'] . "/filemgmt_data/category_snaps/", $fmconfig_data); // Filestore

    // Write our changes to db-config.php
    $fmconfig_file = fopen($fmconfig_path, 'w');
    if (!fwrite($fmconfig_file, $fmconfig_data)) {
        $uninstall_plugin ();
        return false;
    }
    fclose($fmconfig_file);

    // Register the plugin with Geeklog
    DB_delete($_TABLES['plugins'],'pi_name',$pi_name);
    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
        . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error()) {
        $uninstall_plugin ();
        return false;
        exit;
    }
    return true;
}

/**
* Removes the datastructures for this plugin from the Geeklog database
* This may get called by the install routine to undue anything created during the install.
* Added check to see that plugin is first disabled.
*/
function plugin_uninstall_filemgmt()
{
    global $LANG_FM00, $LANG_FILEMGMT, $_TABLES,$_FM_TABLES;

    $pi_name='filemgmt';
    $FEATURES = array ('filemgmt.edit', 'filemgmt.user','filemgmt.upload');
    $TABLES = array ('filemgmt_cat','filemgmt_filedetail','filemgmt_filedesc','filemgmt_brokenlinks','filemgmt_votedata','filemgmt_history');

    // Ok to proceed and delete plugin - Unregister the plugin with Geeklog
    DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = 'filemgmt'",1);
    // Drop tables
    foreach($TABLES as $table) {
        $t = $_FM_TABLES["$table"];
        DB_query("DROP TABLE $t",1);
    }

    // Remove the Admin group definition for this plugin
    $grp_id = DB_getItem($_TABLES['vars'], 'value', "name = '{$pi_name}_admingrp_id'");
    DB_query("DELETE FROM {$_TABLES['groups']} WHERE grp_id = $grp_id",1);
    DB_query("DELETE FROM {$_TABLES['vars']} WHERE name = '{$pi_name}_admingrp_id'");
    DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $grp_id",1);

    // Remove User group definition for this plugin
    $grp_id = DB_getItem($_TABLES['vars'], 'value', "name = '{$pi_name}_usersgrp_id'");
    DB_query("DELETE FROM {$_TABLES['groups']} WHERE grp_id = $grp_id",1);
    DB_query("DELETE FROM {$_TABLES['vars']} WHERE name = '{$pi_name}_usersgrp_id'");
    DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $grp_id",1);

    // Remove all the associated features - access rights. The feature ID's were stored in the vars table during install.
    foreach ($FEATURES as $feature) {
        $feat_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$feature'");
        DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $feat_id",1);
        DB_query("DELETE FROM {$_TABLES['features']} WHERE ft_id = $feat_id",1);
    }

    DB_query("DELETE FROM {$_TABLES['comments']} WHERE type='filemgmt'");
    DB_query("DELETE FROM {$_TABLES['blocks']} WHERE phpblockfn='phpblock_NewDownloads'");
    return true;
}
?>