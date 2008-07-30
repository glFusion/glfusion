<?php
// +---------------------------------------------------------------------------+
// | MediaGallery Plugin                                                       |
// | glFusion auto install / uninstall routines                                |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                              |
// |                                                                           |
// | Author: Mark R. Evans - mark AT glfusion DOT org                          |
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

global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

$pi_name        = 'mediagallery';
$pi_version     = '1.6.2';
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
function mediagallery_compatible_with_this_glfusion_version ()
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

function plugin_install_mediagallery($_DB_table_prefix)
{
    global $_TABLES, $_CONF, $LANG_MG00, $_DB_dbms;
    global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

    require_once($_CONF['path'] . '/plugins/mediagallery/config.php');

    $DEFVALUES = array();

    $NEWFEATURE = array();
    $NEWFEATURE['mediagallery.admin']    ="MediaGallery Admin Rights";
    $NEWFEATURE['mediagallery.config']   ="MediaGallery Configuration Rights";

    // Create the Plugins Tables

    if ( $_DB_dbms == 'mysql' ) {
        require_once($_CONF['path'] . 'plugins/mediagallery/sql/sql_install.php');
    } else {
        require_once($_CONF['path'] . 'plugins/mediagallery/sql/mssql_install.php');
    }
    foreach ($_SQL as $table => $sql) {
        DB_query($sql,1);
        if (DB_error()) {
            plugin_remove_mediagallery();
            return false;
            exit;
        }
    }

    $ftp_path = $_CONF['path'] . 'plugins/mediagallery/uploads/';
    $tmp_path = $_CONF['path'] . 'plugins/mediagallery/tmp/';

    require_once($_CONF['path'] . 'plugins/mediagallery/sql/sql_defaults.php');
    // Insert default configuration
    for ($i = 1; $i <= count($_SQL_DEF); $i++) {
        DB_query(current($_SQL_DEF));
        if (DB_error()) {
            plugin_remove_mediagallery();
            return false;
        }
        next($_SQL_DEF);
    }

    // create random image block

    DB_query("INSERT INTO {$_TABLES['blocks']} (is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, help, group_id, owner_id, perm_owner, perm_group, perm_members,perm_anon) VALUES (0, 'mgrandom', 'phpblock', 'Random Image', 'all', 0, '', '', 0, 1, 'phpblock_mg_randommedia','', 4, 2, 3, 3, 2, 2);",1);
    DB_query("INSERT INTO {$_TABLES['blocks']} (is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, help, group_id, owner_id, perm_owner, perm_group, perm_members,perm_anon) VALUES (0, 'mgenroll', 'phpblock', 'Member Album Enroll', 'all', 0, '', '', 0, 1, 'phpblock_mg_maenroll','', 4, 2, 3, 3, 2, 0);",1);

    // Create the plugin admin security group
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) "
        . "VALUES ('$pi_name Admin', 'Users in this group can administer the $pi_name plugin',0)",1);
    if (DB_error()) {
        plugin_remove_mediagallery();
        return false;
        exit;
    }

    $group_id = DB_insertId();
    if ( $group_id == 0 ) {
        $lookup = $pi_name . ' Admin';
        $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='" . $lookup . "'");
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $group_id = $row['grp_id'];
        } else {
            plugin_uninstal_mediagallery();
            return false;
        }
    }

    // Save the grp id for later uninstall
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_gid', $group_id)",1);
    if (DB_error()) {
        plugin_remove_mediagallery();
        return false;
        exit;
    }

    /* --- create mediagallery config group --- */

    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) "
        . "VALUES ('$pi_name Config', 'Users in this group can configure the $pi_name plugin',0)",1);
    if (DB_error()) {
        plugin_remove_mediagallery();
        return false;
        exit;
    }
    $cgroup_id = DB_insertId();
    if ( $group_id == 0 ) {
        $lookup = $pi_name . ' Config';
        $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='" . $lookup . "'");
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $cgroup_id = $row['grp_id'];
        } else {
            plugin_uninstal_mediagallery();
            return false;
        }
    }

    // Save the grp id for later uninstall
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_cid', $cgroup_id)",1);
    if (DB_error()) {
        plugin_remove_mediagallery();
        return false;
        exit;
    }
    /* --- end of mediagallery config group --- */

    // insert some defaults now that we have our group id
    DB_query("INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_mod_id', '" . $group_id . "')");
    DB_query("INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_mod_group_id', '" . $group_id . "')");
    DB_query("INSERT INTO {$_TABLES['mg_config']} VALUES ('member_mod_group_id', '" . $group_id . "');");

    // Add plugin Features
    foreach ($NEWFEATURE as $feature => $desc) {
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) "
            . "VALUES ('$feature','$desc',0)",1);
        if (DB_error()) {
            plugin_remove_mediagallery();
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
                plugin_uninstal_mediagallery();
                return false;
            }
        }

        if ( $feature == 'mediagallery.admin' ) {
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)",1);
            if (DB_error()) {
                plugin_remove_mediagallery();
                return false;
                exit;
            }
        }
        if ( $feature == 'mediagallery.config' ) {
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $cgroup_id)",1);
            if (DB_error()) {
                plugin_remove_mediagallery();
                return false;
                exit;
            }
        }
    }


    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($group_id, NULL, 1)");
    if (DB_error()) {
        plugin_remove_mediagallery();
        return false;
        exit;
    }

    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($cgroup_id, NULL, 1)");
    if (DB_error()) {
        plugin_remove_mediagallery();
        return false;
        exit;
    }

    // Register the plugin with glFusion
    DB_delete($_TABLES['plugins'],'pi_name','mediagallery');
    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
        . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error()) {
        plugin_remove_mediagallery();
        return false;
        exit;
    }

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
* @param    string   $installCheck     Defult is blank but if set, check if plugin is disabled first
*
* @return   boolean True if successful false otherwise
*
*/
function plugin_remove_mediagallery() {
    global $_DB_table_prefix, $_TABLES, $LANG_MG00;

    $pi_name    = 'mediagallery';

    $FEATURES   = array ( 'mediagallery.admin','mediagallery.config');
    $TABLES     = array ( 'mg_albums','mg_media','mg_media_albums','mg_usage_tracking','mg_config', 'mg_media_queue', 'mg_media_album_queue','mg_playback_options','mg_userprefs','mg_exif_tags', 'mg_watermarks', 'mg_category', 'mg_batch_sessions', 'mg_batch_session_items', 'mg_batch_session_items2','mg_batch_session_log', 'mg_sort', 'mg_postcard','mg_rating');

    // Check and see if plugin is still enabled - if so display warning and exit
    if ($installCheck != '' && DB_getItem($_TABLES['plugins'],'pi_enabled', 'pi_name = "' .$pi_name. '"')) {
        COM_errorLog("Plugin is installed and enabled. Disable first if you want to de-install it",1);
        $display .= COM_startBlock($LANG_MG00['warning']);
        $display .= $LANG_MG00['enabled'];
        $display .= COM_endBlock();
        echo $display;
        return false;
    }

    // Ok to proceed and delete plugin

    // Unregister the plugin with glFusion
    COM_errorLog('Attempting to unregister the Media Gallery Plugin from glFusion',1);
    DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = 'mediagallery'",1);

    // Drop mediagallery tables
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
    DB_query("DELETE FROM {$_TABLES['comments']} WHERE type='mediagallery'",1);

    // Remove all the associated features -- access rights
    foreach ($FEATURES as $feature) {
        COM_errorLog("Removing $feature feature and rights to it",1);
        $feat_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$feature'");
        DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $feat_id",1);
        DB_query("DELETE FROM {$_TABLES['features']} WHERE ft_id = $feat_id",1);
    }

    DB_query("DELETE FROM {$_TABLES['blocks']} WHERE name='mgrandom'",1);
    DB_query("DELETE FROM {$_TABLES['blocks']} WHERE name='mgenroll'",1);

    if ( function_exists('CACHE_cleanup_plugin') ) {
        CACHE_cleanup_plugin('mediagallery');
    }
    COM_errorLog('...success',1);
    return true;
}
?>