<?php
// +---------------------------------------------------------------------------+
// | MediaGallery Plugin                                                       |
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

function plugin_install_mediagallery($_DB_table_prefix)
{
    global $_TABLES, $_CONF, $LANG_MG00, $_DB_dbms;

    require_once($_CONF['path'] . '/plugins/mediagallery/config.php');

    $pi_name = 'mediagallery';                  // Plugin name  Must be 15 chars or less
    $pi_version = $_MG_CONF['version'];         // Plugin Version
    $gl_version = '1.4.0';                      // GL Version plugin for
    $pi_url = 'http://www.gllabs.org';          // Plugin Homepage

    //
    // Default data
    // Insert table name and sql to insert default data for your plugin.
    //
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
            plugin_uninstall_mediagallery();
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
            plugin_uninstall_mediagallery();
            return false;
        }
        next($_SQL_DEF);
    }

    // create random image block

    DB_query("INSERT INTO {$_TABLES['blocks']} (is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, help, group_id, owner_id, perm_owner, perm_group, perm_members,perm_anon) VALUES (0, 'mgrandom', 'phpblock', '" . $LANG_MG00['mg_block_header'] . "', 'all', 0, '', '', 0, 1, 'phpblock_mg_randommedia','', 4, 2, 3, 3, 2, 2);",1);
    DB_query("INSERT INTO {$_TABLES['blocks']} (is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, help, group_id, owner_id, perm_owner, perm_group, perm_members,perm_anon) VALUES (0, 'mgenroll', 'phpblock', '" . $LANG_MG00['mg_enroll_header'] . "', 'all', 0, '', '', 0, 1, 'phpblock_mg_maenroll','', 4, 2, 3, 3, 2, 0);",1);


    // Create the plugin admin security group
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) "
        . "VALUES ('$pi_name Admin', 'Users in this group can administer the $pi_name plugin',0)",1);
    if (DB_error()) {
        plugin_uninstall_mediagallery();
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
        plugin_uninstall_mediagallery();
        return false;
        exit;
    }

    /* --- create mediagallery config group --- */

    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) "
        . "VALUES ('$pi_name Config', 'Users in this group can configure the $pi_name plugin',0)",1);
    if (DB_error()) {
        plugin_uninstall_mediagallery();
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
        plugin_uninstall_mediagallery();
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
            plugin_uninstall_mediagallery();
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
                plugin_uninstall_mediagallery();
                return false;
                exit;
            }
        }
        if ( $feature == 'mediagallery.config' ) {
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $cgroup_id)",1);
            if (DB_error()) {
                plugin_uninstall_mediagallery();
                return false;
                exit;
            }
        }
    }


    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($group_id, NULL, 1)");
    if (DB_error()) {
        plugin_uninstall_mediagallery();
        return false;
        exit;
    }

    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($cgroup_id, NULL, 1)");
    if (DB_error()) {
        plugin_uninstall_mediagallery();
        return false;
        exit;
    }

    // Register the plugin with Geeklog
    DB_delete($_TABLES['plugins'],'pi_name','mediagallery');
    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
        . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error()) {
        plugin_uninstall_mediagallery();
        return false;
        exit;
    }

    return true;
}