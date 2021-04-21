<?php
/**
* glFusion CMS - Polls Plugin
*
* Auto Installer
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs         tony AT tonybibbs DOT com
*  Tom Willett        twillett AT users DOT sourceforge DOT ne
*  Blaine Lang        langmail AT sympatico DOT ca
*  Dirk Haun          dirk AT haun-online DOT de
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_dbms;

require_once $_CONF['path'].'plugins/polls/functions.inc';
require_once $_CONF['path'].'plugins/polls/polls.php';
require_once $_CONF['path'].'plugins/polls/sql/mysql_install.php';

use \glFusion\Log\Log;

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

$INSTALL_plugin['polls'] = array(
  'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

  'plugin' => array('type' => 'plugin', 'name' => $_PO_CONF['pi_name'],
        'ver' => $_PO_CONF['pi_version'], 'gl_ver' => $_PO_CONF['gl_version'],
        'url' => $_PO_CONF['pi_url'], 'display' => $_PO_CONF['pi_display_name']),

  array('type' => 'table', 'table' => $_TABLES['pollanswers'], 'sql' => $_SQL['pollanswers']),

  array('type' => 'table', 'table' => $_TABLES['pollquestions'], 'sql' => $_SQL['pollquestions']),

  array('type' => 'table', 'table' => $_TABLES['polltopics'], 'sql' => $_SQL['polltopics']),
  array('type' => 'table', 'table' => $_TABLES['pollvoters'], 'sql' => $_SQL['pollvoters']),

  array('type' => 'group', 'group' => 'polls Admin', 'desc' => 'Users in this group can administer the Polls plugin',
        'variable' => 'admin_group_id', 'addroot' => true, 'admin' => true),

  array('type' => 'feature', 'feature' => 'polls.edit', 'desc' => 'Ability to edit Polls',
        'variable' => 'edit_feature_id'),

  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'edit_feature_id',
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
function plugin_install_polls()
{
    global $INSTALL_plugin, $_PO_CONF;

    $pi_name            = $_PO_CONF['pi_name'];
    $pi_display_name    = $_PO_CONF['pi_display_name'];
    $pi_version         = $_PO_CONF['pi_version'];

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
function plugin_load_configuration_polls()
{
    global $_CONF;

    require_once $_CONF['path'].'plugins/polls/install_defaults.php';

    return plugin_initconfig_polls();
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
function plugin_autouninstall_polls ()
{
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('pollanswers','polltopics','pollvoters','pollquestions'),
        /* give the full name of the group, as in the db */
        'groups' => array('Polls Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('polls.edit'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array('phpblock_polls'),
        /* give all vars with their name */
        'vars'=> array()
    );
    return $out;
}
?>