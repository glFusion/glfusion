<?php

// +---------------------------------------------------------------------------+
// | Universal Plugin Install Script 1.4 for Geeklog - The Ultimate Weblog     |
// +---------------------------------------------------------------------------+
// | install.php                                                               |
// |                                                                           |
// | This file installs the data structures for the FileManager plugin         |
// |                                                                           |
// | Install Script performs the following:                                    |
// | 1) It creates the tables                                                  |
// | 2) It creates an admin security group for you plugin                      |
// | 3) It adds the security features and adds them to the admin group         |
// | 4) It adds the plugin to the gl_plugins table                             |
// | 5) It adds any default data you have provided                             |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2002 by the following authors:                              |
// |                                                                           |
// | Author:                                                                   |
// | Blaine Lang (C) 2003        -    blaine@portalparts.com                   |
// | Constructed with the Universal Plugin                                     |
// | Copyright (C) 2002 by the following authors:                              |
// | Tom Willett                 -    tomw@pigstye.net                         |
// | Blaine Lang                 -    geeklog@langfamily.ca                    |
// | The Universal Plugin is based on prior work by:                           |
// | Tony Bibbs                  -    tony@tonybibbs.com                       |
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
require_once($_CONF['path'] . 'plugins/filemgmt/config.php');
require_once($_CONF['path'] . 'plugins/filemgmt/functions.inc');


//
// Universal plugin install variables
// Change these to match your plugin
//

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


/**
* Checks the requirements for this plugin and if it is compatible with this
* version of Geeklog.
*
* @return   boolean     true = proceed with install, false = not compatible
*
*/
function plugin_compatible_with_this_geeklog_version ()
{
    // Check for version 1.4+
    $gl_version = floatval (VERSION);
    if ($gl_version >= 1.3) {
        return true;
    } else {
        return false;
    }
}

// Only let Root users access this script
if (!SEC_inGroup('Root')) {
    COM_errorLog("Someone has tried to illegally access the FileMgmt Pro install/uninstall page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    $display = COM_siteHeader();
    $display .= COM_startBlock($LANG_FM00['access_denied']);
    $display .= $LANG_FM00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

/**
* Creates the datastructures for this plugin into the Geeklog database
* Note: Corresponding uninstall routine is in functions.inc
* @return    boolean    True if successful False otherwise
*/


function plugin_install_now()
{
    global $pi_name, $pi_version, $gl_version, $pi_url, $NEWTABLE, $DEFVALUES, $NEWFEATURE;
    global $_TABLES, $_CONF, $_FM_TABLES;

    COM_errorLog("Attempting to install the $pi_name Plugin",1);
    $uninstall_plugin = 'plugin_uninstall_' . $pi_name;

    // Create the Plugins Tables
    require_once($_CONF['path'] . 'plugins/filemgmt/sql/filemgmt_sql_install.php');
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
        $uninstall_plugin ();
        return false;
        exit;
    }
    COM_errorLog('...success',1);
    $group_id = DB_insertId();
    
    // Save the grp id for later uninstall
    COM_errorLog('About to save group_id to vars table for use during uninstall',1);
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_admingrp_id', $group_id)",1);
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
        $feat_id = DB_insertId();
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

    // Register the plugin with Geeklog
    COM_errorLog("Registering $pi_name plugin with Geeklog", 1);
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



/* 
* Main Function
*/

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
    if (plugin_compatible_with_this_geeklog_version ()) {
        if (plugin_install_now ()) {
            $display = COM_refresh ($_CONF['site_admin_url']
                                    . '/plugins.php?msg=44');
        } else {
            $display = COM_refresh ($_CONF['site_admin_url']
                                    . '/plugins.php?msg=72');
        }
    } else {
        // plugin needs a newer version of Geeklog
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