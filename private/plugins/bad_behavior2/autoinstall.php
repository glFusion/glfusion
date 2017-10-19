<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | autoinstall.php                                                          |
// |                                                                          |
// | glFusion Auto Installer module                                           |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2017 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_dbms;

require_once $_CONF['path'].'plugins/bad_behavior2/functions.inc';
require_once $_CONF['path'].'plugins/bad_behavior2/bad_behavior2.php';
require_once $_CONF['path'].'plugins/bad_behavior2/sql/mysql_install.php';

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

$INSTALL_plugin['bad_behavior2'] = array(
  'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

  'plugin' => array('type' => 'plugin', 'name' => $_BB2_CONF['pi_name'],
        'ver' => $_BB2_CONF['pi_version'], 'gl_ver' => $_BB2_CONF['gl_version'],
        'url' => $_BB2_CONF['pi_url'], 'display' => $_BB2_CONF['pi_display_name']),

    array('type' => 'group', 'group' => 'Bad Behavior2 Admin', 'desc' => 'Users in this group can administer the Bad Behavior2 plugin',
        'variable' => 'admin_group_id', 'addroot' => true, 'admin' => true),

    array('type' => 'table', 'table' => $_TABLES['bad_behavior2'], 'sql' => $_SQL['bad_behavior2']),
    array('type' => 'table', 'table' => $_TABLES['bad_behavior2_whitelist'], 'sql' => $_SQL['bad_behavior2_whitelist']),
    array('type' => 'table', 'table' => $_TABLES['bad_behavior2_blacklist'], 'sql' => $_SQL['bad_behavior2_blacklist']),
);


/**
* Puts the datastructures for this plugin into the glFusion database
*
* Note: Corresponding uninstall routine is in functions.inc
*
* @return   boolean True if successful False otherwise
*
*/
function plugin_install_bad_behavior2()
{
    global $INSTALL_plugin, $_BB2_CONF;

    $pi_name            = $_BB2_CONF['pi_name'];
    $pi_display_name    = $_BB2_CONF['pi_display_name'];
    $pi_version         = $_BB2_CONF['pi_version'];

    COM_errorLog("Attempting to install the $pi_display_name plugin", 1);

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
function plugin_load_configuration_bad_behavior2()
{
    global $_CONF;

    require_once $_CONF['path'].'plugins/bad_behavior2/install_defaults.php';

    return plugin_initconfig_bad_behavior2();
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
function plugin_autouninstall_bad_behavior2 ()
{
    if ( defined('DEMO_MODE') ) {
        return '';
    }

    $c = config::get_instance();
    $c->del('sg_spam', 'Core');
    $c->del('fs_spam_config', 'Core');
    $c->del('bb2_enabled','Core');
    $c->del('bb2_ban_enabled','Core');
    $c->del('bb2_display_stats','Core');
    $c->del('bb2_strict','Core');
    $c->del('bb2_verbose','Core');
    $c->del('bb2_logging','Core');
    $c->del('bb2_httpbl_key','Core');
    $c->del('bb2_httpbl_threat','Core');
    $c->del('bb2_httpbl_maxage','Core');
    $c->del('bb2_offsite_forms','Core');
    $c->del('bb2_eu_cookie','Core');

    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('bad_behavior2','bad_behavior2_whitelist','bad_behavior2_blacklist'),
        /* give the full name of the group, as in the db */
        'groups' => array('Bad Behavior2 Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array(),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars'=> array('bb2_installed')
    );
    return $out;
}
?>