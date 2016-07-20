<?php
// +--------------------------------------------------------------------------+
// | Static Pages Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | autoinstall.php                                                          |
// |                                                                          |
// | glFusion Auto Installer module                                           |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony AT tonybibbs DOT com                    |
// |          Tom Willett      - twillett AT users DOT sourceforge DOT net    |
// |          Blaine Lang      - blaine AT portalparts DOT com                |
// |          Dirk Haun        - dirk AT haun-online DOT de                   |
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

require_once $_CONF['path'].'plugins/staticpages/functions.inc';
require_once $_CONF['path'].'plugins/staticpages/staticpages.php';
require_once $_CONF['path'].'plugins/staticpages/sql/mysql_install.php';

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

$INSTALL_plugin['staticpages'] = array(
  'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

  'plugin' => array('type' => 'plugin', 'name' => $_SP_CONF['pi_name'],
        'ver' => $_SP_CONF['pi_version'], 'gl_ver' => $_SP_CONF['gl_version'],
        'url' => $_SP_CONF['pi_url'], 'display' => $_SP_CONF['pi_display_name']),

  array('type' => 'table', 'table' => $_TABLES['staticpage'], 'sql' => $_SQL['staticpage']),

  array('type' => 'group', 'group' => 'staticpages Admin', 'desc' => 'Users in this group can administer the StaticPages plugin',
        'variable' => 'admin_group_id', 'addroot' => true, 'admin' => true),

  array('type' => 'feature', 'feature' => 'staticpages.edit', 'desc' => 'Ability to edit static pages',
        'variable' => 'edit_feature_id'),
  array('type' => 'feature', 'feature' => 'staticpages.delete', 'desc' => 'Ability to delete static pages',
        'variable' => 'delete_feature_id'),
  array('type' => 'feature', 'feature' => 'staticpages.PHP', 'desc' => 'Ability use PHP in static pages',
        'variable' => 'php_feature_id'),

  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'edit_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'delete_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'php_feature_id',
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
function plugin_install_staticpages()
{
    global $INSTALL_plugin, $_SP_CONF;

    $pi_name            = $_SP_CONF['pi_name'];
    $pi_display_name    = $_SP_CONF['pi_display_name'];
    $pi_version         = $_SP_CONF['pi_version'];

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
function plugin_load_configuration_staticpages()
{
    global $_CONF;

    require_once $_CONF['path'].'plugins/staticpages/install_defaults.php';

    return plugin_initconfig_staticpages();
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
function plugin_autouninstall_staticpages ()
{
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('staticpage'),
        /* give the full name of the group, as in the db */
        'groups' => array('staticpages Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('staticpages.edit', 'staticpages.delete', 'staticpages.PHP'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars'=> array()
    );
    return $out;
}
?>