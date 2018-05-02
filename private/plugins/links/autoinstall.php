<?php
// +--------------------------------------------------------------------------+
// | Links Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | autoinstall.php                                                          |
// |                                                                          |
// | glFusion Auto Installer module                                           |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs         - tony AT tonybibbs DOT com                  |
// |          Mark Limburg       - mlimburg AT users.sourceforge DOT net      |
// |          Jason Whittenburg  - jwhitten AT securitygeeks DOT com          |
// |          Dirk Haun          - dirk AT haun-online DOT de                 |
// |          Trinity Bays       - trinity93 AT gmail DOT com                 |
// |          Oliver Spiesshofer - oliver AT spiesshofer DOT com              |
// |          Euan McKay         - info AT heatherengineering DOT com         |
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

require_once $_CONF['path'].'plugins/links/functions.inc';
require_once $_CONF['path'].'plugins/links/links.php';
require_once $_CONF['path'].'plugins/links/sql/mysql_install.php';

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

$INSTALL_plugin['links'] = array(
  'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

  'plugin' => array('type' => 'plugin', 'name' => $_LI_CONF['pi_name'],
        'ver' => $_LI_CONF['pi_version'], 'gl_ver' => $_LI_CONF['gl_version'],
        'url' => $_LI_CONF['pi_url'], 'display' => $_LI_CONF['pi_display_name']),

  array('type' => 'table', 'table' => $_TABLES['linkcategories'], 'sql' => $_SQL['linkcategories']),

  array('type' => 'table', 'table' => $_TABLES['links'], 'sql' => $_SQL['links']),

  array('type' => 'table', 'table' => $_TABLES['linksubmission'], 'sql' => $_SQL['linksubmission']),

  array('type' => 'group', 'group' => 'links Admin', 'desc' => 'Users in this group can administer the Links plugin',
        'variable' => 'admin_group_id', 'addroot' => true, 'admin' => true),

  array('type' => 'feature', 'feature' => 'links.edit', 'desc' => 'Ability to edit links',
        'variable' => 'edit_feature_id'),
  array('type' => 'feature', 'feature' => 'links.moderate', 'desc' => 'Ability to moderate the Links Plugin',
        'variable' => 'moderate_feature_id'),
  array('type' => 'feature', 'feature' => 'links.submit', 'desc' => 'Ability to submit Links',
        'variable' => 'submit_feature_id'),

  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'edit_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'moderate_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'submit_feature_id',
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
function plugin_install_links()
{
    global $INSTALL_plugin, $_LI_CONF;

    $pi_name            = $_LI_CONF['pi_name'];
    $pi_display_name    = $_LI_CONF['pi_display_name'];
    $pi_version         = $_LI_CONF['pi_version'];

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
function plugin_load_configuration_links()
{
    global $_CONF;

    require_once $_CONF['path'].'plugins/links/install_defaults.php';

    return plugin_initconfig_links();
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
function plugin_autouninstall_links ()
{
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('links','linksubmission','linkcategories'),
        /* give the full name of the group, as in the db */
        'groups' => array('Links Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('links.edit', 'links.moderate', 'links.submit'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array('phpblock_blogroll'),
        /* give all vars with their name */
        'vars'=> array()
    );
    return $out;
}
?>