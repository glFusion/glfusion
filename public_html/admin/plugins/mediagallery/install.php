<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | installed media gallery plugin for geeklog                                |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2005-2008 by the following authors:                         |
// |                                                                           |
// | Mark R. Evans              - mark@gllabs.org                              |
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

require_once('../../../lib-common.php');
require_once($_CONF['path'] . '/plugins/mediagallery/config.php');
require_once($_CONF['path'] . '/plugins/mediagallery/functions.inc');

function MG_return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

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

// Only let Root users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the MediaGallery install/uninstall page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

/**
* Puts the datastructures for this plugin into the Geeklog database
*
* Note: Corresponding uninstall routine is in functions.inc
*
* @return   boolean True if successful False otherwise
*
*/
function plugin_install_mediagallery()
{
    global $pi_name, $pi_version, $gl_version, $pi_url, $NEWTABLE, $DEFVALUES, $NEWFEATURE;
    global $_TABLES, $_CONF, $LANG_MG00, $_DB_dbms;

    COM_errorLog("Attempting to install the $pi_name Plugin",1);

    // Create the Plugins Tables

    if ( $_DB_dbms == 'mysql' ) {
        require_once($_CONF['path'] . 'plugins/mediagallery/sql/sql_install.php');
    } else {
        require_once($_CONF['path'] . 'plugins/mediagallery/sql/mssql_install.php');
    }
    foreach ($_SQL as $table => $sql) {
        COM_errorLog("Creating $table table",1);
        DB_query($sql,1);
        if (DB_error()) {
            COM_errorLog("Error Creating $table table",1);
            plugin_uninstall_mediagallery();
            return false;
            exit;
        }
        COM_errorLog("Success - Created $table table",1);
    }

    $ftp_path = $_CONF['path'] . 'plugins/mediagallery/uploads/';
    $tmp_path = $_CONF['path'] . 'plugins/mediagallery/tmp/';

    require_once($_CONF['path'] . 'plugins/mediagallery/sql/sql_defaults.php');
    // Insert default configuration
    COM_errorLog("Inserting default data into tables",1);
    for ($i = 1; $i <= count($_SQL_DEF); $i++) {
        DB_query(current($_SQL_DEF));
        if (DB_error()) {
            COM_errorLog("Error inserting Media Gallery Defaults",1);
            plugin_uninstall_mediagallery();
            return false;
        }
        next($_SQL_DEF);
    }
    COM_errorLog("Success - default data added to Media Gallery tables",1);

    // create random image block

    DB_query("INSERT INTO {$_TABLES['blocks']} (is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, help, group_id, owner_id, perm_owner, perm_group, perm_members,perm_anon) VALUES (0, 'mgrandom', 'phpblock', '" . $LANG_MG00['mg_block_header'] . "', 'all', 0, '', '', 0, 1, 'phpblock_mg_randommedia','', 4, 2, 3, 3, 2, 2);",1);
    DB_query("INSERT INTO {$_TABLES['blocks']} (is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, help, group_id, owner_id, perm_owner, perm_group, perm_members,perm_anon) VALUES (0, 'mgenroll', 'phpblock', '" . $LANG_MG00['mg_enroll_header'] . "', 'all', 0, '', '', 0, 1, 'phpblock_mg_maenroll','', 4, 2, 3, 3, 2, 0);",1);

    COM_errorLog("Success - inserting data into $table table",1);

    // Create the plugin admin security group
    COM_errorLog("Attempting to create $pi_name admin group", 1);
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) "
        . "VALUES ('$pi_name Admin', 'Users in this group can administer the $pi_name plugin',0)",1);
    if (DB_error()) {
        plugin_uninstall_mediagallery();
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
            COM_errorlog("ERROR: Media Gallery Installation - Unable to determine group_id");
            plugin_uninstal_mediagallery();
            return false;
        }
    }
    COM_errorLog("...success - group_id = " . $group_id,1);

    // Save the grp id for later uninstall
    COM_errorLog('About to save group_id to vars table for use during uninstall',1);
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_gid', $group_id)",1);
    if (DB_error()) {
        COM_errorLog("Failed to save group_id to vars table",1);
        plugin_uninstall_mediagallery();
        return false;
        exit;
    }
    COM_errorLog('...success',1);

    /* --- create mediagallery config group --- */

    COM_errorLog("Attempting to create $pi_name config group", 1);
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) "
        . "VALUES ('$pi_name Config', 'Users in this group can configure the $pi_name plugin',0)",1);
    if (DB_error()) {
        plugin_uninstall_mediagallery();
        return false;
        exit;
    }
    COM_errorLog('...success',1);

    COM_errorLog("Calling DB_insertID()");
    $cgroup_id = DB_insertId();
    if ( $group_id == 0 ) {
        $lookup = $pi_name . ' Config';
        $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='" . $lookup . "'");
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $cgroup_id = $row['grp_id'];
        } else {
            COM_errorlog("ERROR: Media Gallery Installation - Unable to determine cgroup_id");
            plugin_uninstal_mediagallery();
            return false;
        }
    }
    COM_errorLog("...success - cgroup_id = " . $group_id,1);

    // Save the grp id for later uninstall
    COM_errorLog('About to save cgroup_id to vars table for use during uninstall',1);
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_cid', $cgroup_id)",1);
    if (DB_error()) {
        COM_errorLog("Failed to save cgroup_id to vars table",1);
        plugin_uninstall_mediagallery();
        return false;
        exit;
    }
    COM_errorLog('...success',1);

    /* --- end of mediagallery config group --- */

    // insert some defaults now that we have our group id
    DB_query("INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_mod_id', '" . $group_id . "')");
    DB_query("INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_mod_group_id', '" . $group_id . "')");
    DB_query("INSERT INTO {$_TABLES['mg_config']} VALUES ('member_mod_group_id', '" . $group_id . "');");

    // Add plugin Features
    foreach ($NEWFEATURE as $feature => $desc) {
        COM_errorLog("Adding $feature feature",1);
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) "
            . "VALUES ('$feature','$desc',0)",1);
        if (DB_error()) {
            COM_errorLog("Failure adding $feature feature",1);
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
                COM_errorlog("ERROR: Media Gallery Installation - Unable to determine feat_id");
                plugin_uninstal_mediagallery();
                return false;
            }
        }
        COM_errorLog("Success - feat_id = " . $feat_id,1);

        if ( $feature == 'mediagallery.admin' ) {
            COM_errorLog("Adding $feature feature to admin group",1);
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)",1);
            if (DB_error()) {
                COM_errorLog("Failure adding $feature feature to admin group",1);
                plugin_uninstall_mediagallery();
                return false;
                exit;
            }
            COM_errorLog("Success",1);
        }
        if ( $feature == 'mediagallery.config' ) {
            COM_errorLog("Adding $feature feature to config group",1);
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $cgroup_id)",1);
            if (DB_error()) {
                COM_errorLog("Failure adding $feature feature to config group",1);
                plugin_uninstall_mediagallery();
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
        plugin_uninstall_mediagallery();
        return false;
        exit;
    }
    COM_errorLog("Success",1);

    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    COM_errorLog("Attempting to give all users in Root group access to $pi_name config group",1);
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($cgroup_id, NULL, 1)");
    if (DB_error()) {
        COM_errorLog("Failure giving all users in Root group access to config");
        plugin_uninstall_mediagallery();
        return false;
        exit;
    }
    COM_errorLog("Success",1);

    // Register the plugin with Geeklog
    COM_errorLog("Registering $pi_name plugin with Geeklog", 1);
    DB_delete($_TABLES['plugins'],'pi_name','mediagallery');
    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
        . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error()) {
        COM_errorLog("Failure registering plugin with Geeklog");
        plugin_uninstall_mediagallery();
        return false;
        exit;
    }

    COM_errorLog("Succesfully installed the $pi_name Plugin!",1);
    return true;
}

/*
* Main Function
*/

$action = COM_applyFilter($_POST['action']);

$display = COM_siteHeader();
$T = new Template($_MG_CONF['template_path']);
$T->set_file('install', 'install.thtml');
$T->set_var('install_header', $LANG_MG00['install_header']);
$T->set_var('img',MG_getImageFile('mediagallery.png'));
$T->set_var('cgiurl', $_CONF['site_admin_url'] . '/plugins/mediagallery/install.php');
$T->set_var('admin_url', $_CONF['site_admin_url'] . '/plugins/mediagallery/index.php');

if ($action == 'install') {
    if (plugin_install_mediagallery()) {
        $installMsg = sprintf($LANG_MG00['install_success'],$_CONF['site_admin_url'] . '/plugins/mediagallery/index.php');
        $T->set_var('installmsg1',$installMsg);
    } else {
       	echo COM_refresh ($_CONF['site_admin_url'] . '/plugins.php?msg=72');
    }
} else if ($action == "uninstall") {
   plugin_uninstall_mediagallery('installed');
   $T->set_var('installmsg1',$LANG_MG00['uninstall_msg']);
}

if (DB_count($_TABLES['plugins'], 'pi_name', 'mediagallery') == 0) {

    $errCheck = 0;

    $T->set_var('installmsg2', $LANG_MG00['uninstalled']);
    $T->set_var('readme', $LANG_MG00['readme']);
    $T->set_var('btnmsg', $LANG_MG00['install']);
    $T->set_var('action','install');

    $gl_version     = VERSION;
    $php_version    = phpversion();
    if (is_array($TEMPLATE_OPTIONS)) {
        $tc_installed = 1;
    } else {
        $tc_installed = 0;
    }
    $memory_limit = MG_return_bytes(ini_get('memory_limit'));

    $glversion = explode(".", VERSION);
    if ( $glversion[1] < 4 ) {
        $versionCheck = '<div style="background-color:#ffff00;color:#000000;vertical-align:middle;padding:5px;"><img src="redX.png" alt="error" style="padding:5px;vertical-align:middle;">&nbsp;' . $LANG_MG00['gl_version_error'] . '</div>';
        $errCheck++;
    } else {
        $versionCheck = '<div style="vertical-align:middle;padding:5px;"><img src="check.png" alt="OK" style="padding:5px;vertical-align:middle;">' . $LANG_MG00['gl_version_ok'] . '</div>';
    }
    if ( $tc_installed == 0 ) {
        $errCheck++;
        $cacheCheck = '<div style="background-color:#ffff00;color:#000000;vertical-align:middle;padding:5px;"><img src="redX.png" alt="error" style="padding:5px;vertical-align:middle;">&nbsp;' . $LANG_MG00['tc_error'] . '</div>';
    } else {
        $cacheCheck = '<div style="vertical-align:middle;padding:5px;"><img src="check.png" alt="OK" style="padding:5px;vertical-align:middle;">' . $LANG_MG00['tc_ok'] . '</div>';
    }
    if ( $memory_limit < 50331648 ) {
        $memoryCheck = '<div style="background-color:#ffff00;color:#000000;vertical-align:middle;padding:5px;"><img src="redX.png" alt="error" style="padding:5px;vertical-align:middle;">&nbsp;' . $LANG_MG00['ml_error'] . '</div>';
    } else {
        $memoryCheck = '<div style="vertical-align:middle;padding:5px;"><img src="check.png" alt="OK" style="padding:5px;vertical-align:middle;">' . $LANG_MG00['ml_ok'] . '</div>';
    }

    $glver  = sprintf($LANG_MG00['geeklog_check'],$gl_version);
    $phpver = sprintf($LANG_MG00['php_check'],$php_version);

    $T->set_var(array(
        'lang_overview'     => $LANG_MG00['overview'],
        'mg_requirements'   => $LANG_MG00['preinstall_check'],
        'gl_version'        => $glver,
        'php_version'       => $phpver,
        'tc_installed'      => $tc_installed == 0 ? $LANG_MG01['no'] : $LANG_MG01['yes'],
        'install_doc'       => $LANG_MG00['preinstall_confirm'],
        'lang_template_cache'    => $LANG_MG00['template_cache'],
        'version_check'     => $versionCheck,
        'cache_check'       => $cacheCheck,
        'memory_check'      => $memoryCheck,
        'lang_env_check'    => $LANG_MG00['env_check'],
    ));
    if ( $errCheck == 0 ) {
        $T->set_var('btnmsg', $LANG_MG00['install']);
        $T->set_var('action','install');
        $T->set_var('errormessage','');
    } else {
        $T->set_var('btnmsg', $LANG_MG00['recheck_env']);
        $T->set_var('action','recheck');
        $T->set_var('errormessage',$LANG_MG00['fix_install']);
    }
} else {
   echo COM_refresh($_CONF['site_admin_url'] . '/plugins/mediagallery/index.php?mode=install');
   exit;
}
$T->parse('output','install');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter(true);

echo $display;
?>