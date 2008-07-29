<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | install.php                                                              |
// |                                                                          |
// | Adds / removes data structures                                           |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Forum Plugin for Geeklog CMS                                |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
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

require_once('../../../lib-common.php');
require_once($_CONF['path'] . 'plugins/forum/config.php');
require_once($_CONF['path'] . 'plugins/forum/functions.inc');

//
// Universal plugin install variables
// Change these to match your plugin
//

$pi_name = 'forum';                          // Plugin name
$pi_version = $CONF_FORUM['version'];        // Plugin Version
$gl_version = '1.5';                         // GL Version plugin for
$pi_url = 'http://www.portalparts.com/';     // Plugin Homepage


// Default data
// Insert table name and sql to insert default data for your plugin.

$DEFVALUES = array();

// Example default data

$DEFVALUES['gf_settings'] = "INSERT INTO {$_TABLES['gf_settings']} VALUES (
'', 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 5, 0, 40, 10, 10, 20, 20, 20, 60, 60, 1, 0, 0, '', 1, 0, 1, 40, 5, 20, 5, 0, 5, 2, 2, 0, 1, 15, 35, 70, 120, 'Newbie', 'Junior', 'Chatty', 'Regular Member', 'Active Member');";

$DEFVALUES['block1'] = "INSERT INTO {$_TABLES['blocks']} (is_enabled,name,type,title,tid,blockorder,onleft,phpblockfn,group_id,owner_id,perm_owner,perm_group,perm_members,perm_anon) "
     . " VALUES ('1','Forum News','phpblock','Forumposts','all',0,0,'phpblock_forum_newposts',2,2,3,3,2,2)";

$DEFVALUES['block2'] = "INSERT INTO {$_TABLES['blocks']} (is_enabled,name,type,title,tid,blockorder,onleft,phpblockfn,group_id,owner_id,perm_owner,perm_group,perm_members,perm_anon) "
     . " VALUES (0, 'forum_menu', 'phpblock', 'Forum Menu', 'all', 0, 1, 'phpblock_forum_menu', 2,2,3,2,2,2)";


/**
* Checks the requirements for this plugin and if it is compatible with this
* version of glFusion.
*
* @return   boolean     true = proceed with install, false = not compatible
*
*/
function plugin_compatible_with_this_glfusion_version ()
{
    return true;
}


//
// Security Feature to add
// Fill in your security features here
// Note you must add these features to the uninstall routine in function.inc so that they will
// be removed when the uninstall routine runs.
// You do not have to use these particular features.  You can edit/add/delete them
// to fit your plugins security model
//

$NEWFEATURE = array();
$NEWFEATURE['forum.edit'] = 'Forum Admin';
$NEWFEATURE['forum.user'] = 'Forum Viewer';


// Only let Root users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the forum install/uninstall page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    $display = COM_siteHeader();
    $display .= COM_startBlock($LANG_GF00['access_denied']);
    $display .= $LANG_GF00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

/**
* Puts the datastructures for this plugin into the glFusion database
*
* Note: Corresponding uninstall routine is in functions.inc
*
* @return    boolean    True if successful False otherwise
*
*/
function plugin_install_now()
{
    global $pi_name, $pi_version, $gl_version, $pi_url, $NEWTABLE, $DEFVALUES, $NEWFEATURE;
    global $_TABLES, $_CONF;

    COM_errorLog("Attempting to install the $pi_name Plugin",1);
    $uninstall_plugin = 'plugin_uninstall_' . $pi_name;

    // Create the Plugins Tables
    require_once($_CONF['path'] . 'plugins/forum/sql/mysql_install_3.0.php');

    for ($i = 1; $i <= count($_SQL); $i++) {
        $progress .= "executing " . current($_SQL) . "<br>\n";
        COM_errorLOG("executing " . current($_SQL));
        DB_query(current($_SQL),'1');
        if (DB_error()) {
            COM_errorLog("Error Creating $table table",1);
            $uninstall_plugin ('DeletePlugin');
            return false;
            exit;
        }
        next($_SQL);
    }
    COM_errorLog("Success - Created $table table",1);

    // Insert Default Data

    foreach ($DEFVALUES as $table => $sql) {
        COM_errorLog("Inserting default data into $table table",1);
        DB_query($sql,1);
        if (DB_error()) {
            COM_errorLog("Error inserting default data into $table table",1);
            $uninstall_plugin ();
            return false;
            exit;
        }
        COM_errorLog("Success - inserting data into $table table",1);
    }

    // Create the plugin admin security group
    COM_errorLog("Attempting to create $pi_name admin group", 1);
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr) "
        . "VALUES ('$pi_name Admin', 'Users in this group can administer the $pi_name plugin')",1);
    if (DB_error()) {
        $uninstall_plugin();
        return false;
        exit;
    }
    COM_errorLog('...success',1);
    $query = DB_query("SELECT max(grp_id) FROM {$_TABLES['groups']} ");
    list ($group_id) = DB_fetchArray($query);

    // Save the grp id for later uninstall
    COM_errorLog('About to save group_id to vars table for use during uninstall',1);
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_admin', $group_id)",1);
    if (DB_error()) {
        $uninstall_plugin ();
        return false;
        exit;
    }
    COM_errorLog('...success',1);

    // Add plugin Features
    foreach ($NEWFEATURE as $feature => $desc) {
        COM_errorLog("Adding $feature feature",1);
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr) "
            . "VALUES ('$feature','$desc')",1);
        if (DB_error()) {
            COM_errorLog("Failure adding $feature feature",1);
            $uninstall_plugin ();
            return false;
            exit;
        }
        $query = DB_query("SELECT max(ft_id) FROM {$_TABLES['features']} ");
        list ($feat_id) = DB_fetchArray($query);

        COM_errorLog("Success",1);
        COM_errorLog("Adding $feature feature to admin group",1);
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)");
        if (DB_error()) {
            COM_errorLog("Failure adding $feature feature to admin group",1);
            $uninstall_plugin ();
            return false;
            exit;
        }
        COM_errorLog("Success",1);
    }

    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    COM_errorLog("Attempting to give all users in Root group access to $pi_name admin group",1);
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($group_id, NULL, 1)");
    if (DB_error()) {
        $uninstall_plugin ();
        return false;
        exit;
    }

    // Register the plugin with glFusion
    COM_errorLog("Registering $pi_name plugin with glFusion", 1);
    DB_delete($_TABLES['plugins'],'pi_name',$pi_name);
    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
        . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error()) {
        $uninstall_plugin ();
        return false;
        exit;
    }

    COM_errorLog("Succesfully installed the $pi_name Plugin!",1);
    return true;
}

// MAIN
$display = '';

if ($_REQUEST['action'] == 'uninstall') {
    $uninstall_plugin = 'plugin_uninstall_' . $pi_name;
    if ($uninstall_plugin ()) {
        $display = COM_refresh ($_CONF['site_admin_url']
                                . '/plugins.php?msg=45');
    } else {
        $display = COM_refresh ($_CONF['site_admin_url']
                                . '/plugins.php?msg=73');
    }

} else if (DB_count ($_TABLES['plugins'], 'pi_name', $pi_name) == 0) {
    // plugin not installed
    if (plugin_compatible_with_this_glfusion_version ()) {
        if (plugin_install_now ()) {
            $display = COM_refresh ($_CONF['site_admin_url']
                                    . '/plugins.php?msg=44');
        } else {
            $display = COM_refresh ($_CONF['site_admin_url']
                                    . '/plugins.php?msg=72');
        }
    } else {
        // plugin needs a newer version of glFusion
        $display .= COM_siteHeader ('menu', $LANG32[8])
                 . COM_startBlock ($LANG32[8])
                 . '<p>' . $LANG32[9] . '</p>'
                 . COM_endBlock ()
                 . COM_siteFooter ();
    }
} else {
    // plugin already installed
    $display .= COM_siteHeader ('menu', $LANG01[77])
             . COM_startBlock ($LANG32[6])
             . '<p>' . $LANG32[7] . '</p>'
             . COM_endBlock ()
             . COM_siteFooter();
}

echo $display;

?>