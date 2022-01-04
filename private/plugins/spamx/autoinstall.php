<?php
/**
* glFusion CMS - SpamX Plugin
*
* glFusion Interface functions.inc
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the prior work
*  Copyright (C) 2004-2010 by the following authors:
*   Authors: Tom Willett     tomw AT pigstye DOT net
*            Dirk Haun       dirk AT haun-online DOT de
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_dbms;

require_once $_CONF['path'].'plugins/spamx/functions.inc';
require_once $_CONF['path'].'plugins/spamx/spamx.php';
require_once $_CONF['path'].'plugins/spamx/sql/mysql_install.php';

use \glFusion\Log\Log;

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

$INSTALL_plugin['spamx'] = array(
  'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

  'plugin' => array('type' => 'plugin', 'name' => $_SPX_CONF['pi_name'],
        'ver' => $_SPX_CONF['pi_version'], 'gl_ver' => $_SPX_CONF['gl_version'],
        'url' => $_SPX_CONF['pi_url'], 'display' => $_SPX_CONF['pi_display_name']),

  array('type' => 'table', 'table' => $_TABLES['spamx'], 'sql' => $_SQL['spamx']),
  array('type' => 'table', 'table' => $_TABLES['spamx_stats'], 'sql' => $_SQL['spamx_stats']),

  array('type' => 'group', 'group' => 'spamx Admin', 'desc' => 'Users in this group can administer the Spamx plugin',
        'variable' => 'admin_group_id', 'addroot' => true, 'admin' => true),

  array('type' => 'feature', 'feature' => 'spamx.admin', 'desc' => 'Full access to Spam-X plugin',
        'variable' => 'admin_feature_id'),

  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'admin_feature_id',
        'log' => 'Adding feature to the admin group'),
);


/**
* Puts the datastructures for this plugin into the glFusion database
*
* Note: Corresponding uninstall routine is in functions.inc
*
* @return   boolean True if successful False otherwise
*
*/
function plugin_install_spamx()
{
    global $INSTALL_plugin, $_SPX_CONF;

    $pi_name            = $_SPX_CONF['pi_name'];
    $pi_display_name    = $_SPX_CONF['pi_display_name'];
    $pi_version         = $_SPX_CONF['pi_version'];

    Log::write('system',Log::INFO,'Attempting to install the '.$pi_display_name.' plugin');

    $ret = INSTALLER_install($INSTALL_plugin[$pi_name]);
    if ($ret > 0) {
        return false;
    }

    return true;
}


/**
* Loads the configuration records for the Online Config Manager
*
* @return   boolean     true = proceed with install, false = an error occured
*
*/
function plugin_load_configuration_spamx()
{
    global $_CONF;

    require_once $_CONF['path'].'plugins/spamx/install_defaults.php';

    return plugin_initconfig_spamx();
}

function plugin_postinstall_spamx()
{
    global $_CONF, $_TABLES;

//    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('spamx.counter', '0')",1);
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
function plugin_autouninstall_spamx ()
{
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('spamx','spamx_stats'),
        /* give the full name of the group, as in the db */
        'groups' => array('spamx Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('spamx.admin', 'spamx.view'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars' => array('spamx_gid', 'spamx.counter')
    );
    return $out;
}
?>