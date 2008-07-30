<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | install.inc                                                              |
// |                                                                          |
// | Installation routines                                                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the FileMgmt Plugin for Geeklog                                 |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
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
if (strpos ($_SERVER['PHP_SELF'], 'install.inc') !== false)
{
    die ('This file can not be used on its own.');
}

global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

$pi_name        = 'filemgmt';
$pi_version     = '1.7.0.fusion';  // $_FM_CONF['version'];
$gl_version     = '1.1.0';
$pi_url         = 'http://www.glfusion.org';
$base_path      = $_CONF['path'] . 'plugins/' . $pi_name . '/';

/*
 * If we are being called glFusion's install routine, there is no
 * COM_errorLog() function available.
 */

if (!function_exists('COM_errorLOG') ) {
    function COM_errorLOG() {
    }
}

/**
* Checks the requirements for this plugin and if it is compatible with this
* version of glFusion.
*
* @return   boolean     true = proceed with install, false = not compatible
*
*/
function plugin_compatible_with_this_glfusion_version ()
{
    // requires 1.1.0 or newer
    $glversion = explode(".", glFusion_VERSION);
    if ( $glversion[0] < 1 ) {
        return false;
    }
    if ( $glversion[1] < 1 ) {
        return false;
    }
    return true;
}

/**
* Loads the configuration records for the GL Online Config Manager
*
* @return   boolean     true = proceed with install, false = an error occured
*
*/
function filemgmt_load_configuration()
{
    global $_CONF, $base_path;

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $base_path . 'install_defaults.php';

    return plugin_initconfig_filemgmt();
}

function plugin_install_filemgmt($_DB_table_prefix)
{
    global $_TABLES, $_CONF, $_FM_TABLES;
    global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

    require_once($_CONF['path'] . '/plugins/filemgmt/config.php');

    $DEFVALUES = array();
    $NEWFEATURE = array();
    $NEWFEATURE['filemgmt.user']   = "filemgmt Access";
    $NEWFEATURE['filemgmt.edit']   = "filemgmt Admin Rights";
    $NEWFEATURE['filemgmt.upload'] = "filemgmt File Upload Rights";

    $now = time();
    $DEFVALUES['d1'] = "INSERT INTO {$_FM_TABLES['filemgmt_cat']} (`cid`, `pid`, `title`, `imgurl`, `grp_access`, `grp_writeaccess`) VALUES (1,0,'General','',2,2);";
    $DEFVALUES['d2'] = "INSERT INTO {$_FM_TABLES['filemgmt_filedesc']} (`lid`, `description`) VALUES (1,'glFusion Overview Documentation in PDF format');";
    $DEFVALUES['d3'] = "INSERT INTO {$_FM_TABLES['filemgmt_filedetail']} (`lid`, `cid`, `title`, `url`, `homepage`, `version`, `size`, `platform`, `logourl`, `submitter`, `status`, `date`, `hits`, `rating`, `votes`, `comments`) VALUES (1,1,'glFusion Overview Documentation','glfusion.pdf','http://www.gllabs.org','v1.0.0',208896 ,'','',2,1,$now,0,0.0000,0,1);";

    $uninstall_plugin = 'plugin_remove_' . $pi_name;

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

    // Load the online configuration records
    if (function_exists('filemgmt_load_configuration')) {
        if (!filemgmt_load_configuration()) {
            $uninstall_plugin();
            return false;
        }
    }

    // Register the plugin with glFusion
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
* Removes the datastructures for this plugin from the glFusion database
* This may get called by the install routine to undue anything created during the install.
* Added check to see that plugin is first disabled.
*/
function plugin_remove_filemgmt()
{
    global $LANG_FM00, $LANG_FILEMGMT, $_TABLES,$_FM_TABLES;

    $pi_name    ='filemgmt';
    $FEATURES   = array ('filemgmt.edit', 'filemgmt.user','filemgmt.upload');
    $TABLES     = array ('filemgmt_cat','filemgmt_filedetail','filemgmt_filedesc','filemgmt_brokenlinks','filemgmt_votedata','filemgmt_history');

    // Ok to proceed and delete plugin - Unregister the plugin with glFusion
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

    DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE group_name='filemgmt'");
    COM_errorLog("Removing configuration option for plugin $pi_name");

    return true;
}
?>