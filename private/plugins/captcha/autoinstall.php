<?php
/**
* glFusion CMS
*
* glFusion Auto installer
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

global $_DB_dbms;

require_once $_CONF['path'].'plugins/captcha/functions.inc';
require_once $_CONF['path'].'plugins/captcha/captcha.php';
require_once $_CONF['path'].'plugins/captcha/sql/mysql_install.php';

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

global $_CP_CONF;

$INSTALL_plugin['captcha'] = array(
  'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

  'plugin' => array('type' => 'plugin', 'name' => $_CP_CONF['pi_name'],
        'ver' => $_CP_CONF['pi_version'], 'gl_ver' => $_CP_CONF['gl_version'],
        'url' => $_CP_CONF['pi_url'], 'display' => $_CP_CONF['pi_display_name']),

  array('type' => 'table', 'table' => $_TABLES['cp_sessions'], 'sql' => $_SQL['cp_sessions']),
);


/**
* Puts the datastructures for this plugin into the glFusion database
*
* Note: Corresponding uninstall routine is in functions.inc
*
* @return   boolean True if successful False otherwise
*
*/
function plugin_install_captcha()
{
    global $INSTALL_plugin, $_CP_CONF;

    $pi_name            = $_CP_CONF['pi_name'];
    $pi_display_name    = $_CP_CONF['pi_display_name'];
    $pi_version         = $_CP_CONF['pi_version'];

    Log::write('system',Log::INFO,"Attempting to install the $pi_display_name plugin");

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
function plugin_load_configuration_captcha()
{
    global $_CONF;

    require_once $_CONF['path'].'plugins/captcha/install_defaults.php';

    return plugin_initconfig_captcha();
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
function plugin_autouninstall_captcha ()
{
    if ( defined('DEMO_MODE') ) {
        return '';
    }

    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('cp_sessions'),
        /* give the full name of the group, as in the db */
        'groups' => array(),
        /* give the full name of the feature, as in the db */
        'features' => array(),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars'=> array()
    );
    return $out;
}
?>