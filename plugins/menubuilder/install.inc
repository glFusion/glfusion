<?php
// +---------------------------------------------------------------------------+
// | Menu Builder v1 Plugin                                                    |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | install.php                                                               |
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

/*
 * If we are being called Geeklog's upgrade routine, there is no
 * COM_errorLog() function available.
 */

if (!function_exists('COM_errorLOG') ) {
    function COM_errorLOG() {
    }
}

function plugin_install_menubuilder($_DB_table_prefix)
{
    global $pi_name, $pi_version, $gl_version, $pi_url, $NEWTABLE, $DEFVALUES, $NEWFEATURE;
    global $_TABLES, $_CONF, $LANG_MB00, $_DB_dbms;

    require_once($_CONF['path'] . '/plugins/menubuilder/config.php');

    $pi_name = 'menubuilder';            // Plugin name  Must be 15 chars or less
    $pi_version = $_MB_CONF['version']; // Plugin Version
    $gl_version = '1.5.0';              // GL Version plugin for
    $pi_url = 'http://www.gllabs.org';  // Plugin Homepage

    //
    // Default data
    // Insert table name and sql to insert default data for your plugin.
    //
    $DEFVALUES = array();

    $NEWFEATURE = array();
    $NEWFEATURE['menubuilder.admin'] =   "Menu Editor Admin Rights";

    COM_errorLog("Attempting to install the $pi_name Plugin",1);

    // Create the Plugins Tables

    if ( $_DB_dbms == 'mysql' ) {
        require_once($_CONF['path'] . 'plugins/menubuilder/sql/mysql_install.php');
    } else {
        require_once($_CONF['path'] . 'plugins/menubuilder/sql/mssql_install.php');
    }
    foreach ($_SQL as $table => $sql) {
        COM_errorLog("Creating $table table",1);
        DB_query($sql,1);
        if (DB_error()) {
            COM_errorLog("Error Creating $table table",1);
            plugin_uninstall_menubuilder();
            return false;
            exit;
        }
        COM_errorLog("Success - Created $table table",1);
    }

    $ftp_path = $_CONF['path'] . 'plugins/menubuilder/uploads/';
    $tmp_path = $_CONF['path'] . 'plugins/menubuilder/tmp/';

    // Insert default configuration
    COM_errorLog("Inserting default data into tables",1);
    for ($i = 1; $i <= count($_SQL_DEF); $i++) {
        DB_query(current($_SQL_DEF));
        if (DB_error()) {
            COM_errorLog("Error inserting Menu Editor Defaults",1);
            plugin_uninstall_menubuilder();
            return false;
        }
        next($_SQL_DEF);
    }
    COM_errorLog("Success - default data added to Menu Editor tables",1);

    // Create the plugin admin security group
    COM_errorLog("Attempting to create $pi_name admin group", 1);
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) "
        . "VALUES ('$pi_name Admin', 'Users in this group can administer the $pi_name plugin',0)",1);
    if (DB_error()) {
        plugin_uninstall_menubuilder();
        return false;
        exit;
    }
    COM_errorLog('...success',1);

    COM_errorLog("Calling DB_insertID()");
    $group_id = DB_insertId();
    if ( $group_id == 0 ) {
        $lookup = $pi_name . ' Admin';
        $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='" . $lookup . "'");
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $group_id = $row['grp_id'];
        } else {
            COM_errorlog("ERROR: Menu Editor Installation - Unable to determine group_id");
            plugin_uninstal_menubuilder();
            return false;
        }
    }
    COM_errorLog("...success - group_id = " . $group_id,1);

    // Save the grp id for later uninstall
    COM_errorLog('About to save group_id to vars table for use during uninstall',1);
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_gid', $group_id)",1);
    if (DB_error()) {
        COM_errorLog("Failed to save group_id to vars table",1);
        plugin_uninstall_menubuilder();
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
            plugin_uninstall_menubuilder();
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
                COM_errorlog("ERROR: Menu Editor Installation - Unable to determine feat_id");
                plugin_uninstal_menubuilder();
                return false;
            }
        }
        COM_errorLog("Success - feat_id = " . $feat_id,1);

        if ( $feature == 'menubuilder.admin' ) {
            COM_errorLog("Adding $feature feature to admin group",1);
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)",1);
            if (DB_error()) {
                COM_errorLog("Failure adding $feature feature to admin group",1);
                plugin_uninstall_menubuilder();
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
        plugin_uninstall_menubuilder();
        return false;
        exit;
    }
    COM_errorLog("Success",1);

    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1

    // Register the plugin with Geeklog
    COM_errorLog("Registering $pi_name plugin with Geeklog", 1);
    DB_delete($_TABLES['plugins'],'pi_name','menubuilder');
    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
        . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error()) {
        COM_errorLog("Failure registering plugin with Geeklog");
        plugin_uninstall_menubuilder();
        return false;
        exit;
    }

    COM_errorLog("Succesfully installed the $pi_name Plugin!",1);
    return true;
}