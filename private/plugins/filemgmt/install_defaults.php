<?php
/**
 * FileMgmt Plugin Configuration Installer for glFusion.
 *
 * @license GNU General Public License version 2 or later
 *     http://www.opensource.org/licenses/gpl-license.php
 *
 *  Copyright (C) 2008-2022 by the following authors:
 *   Mark R. Evans   mark AT glfusion DOT org
 */

if (!defined ('GVERSION')) {
    die('This file can not be used on its own!');
}

/** Utility plugin configuration data
*   @global array */
global $_FM_CONF;
if (!isset($_FM_CONF) || empty($_FM_CONF)) {
    $_FM_CONF = array();
    require_once dirname(__FILE__) . '/filemgmt.php';
}

/**
*   Initialize FileMgmt plugin configuration
*
*   @return boolean             true: success; false: an error occurred
*/
function plugin_initconfig_filemgmt()
{
    global $_CONF;

    $c = config::get_instance();

    if (!$c->group_exists('filemgmt')) {
        require_once $_CONF['path'].'plugins/filemgmt/sql/filemgmt_config_data.php';

        foreach ( $filemgmtConfigData AS $cfgItem ) {
            $c->add(
                $cfgItem['name'],
                $cfgItem['default_value'],
                $cfgItem['type'],
                $cfgItem['subgroup'],
                $cfgItem['fieldset'],
                $cfgItem['selection_array'],
                $cfgItem['sort'],
                $cfgItem['set'],
                $cfgItem['group']
            );
        }
     }
     return true;
}


