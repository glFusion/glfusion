<?php
/**
* glFusion CMS - SpamX Plugin
*
* Config Defaults
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
    die('This file can not be used on its own!');
}

/**
* Initialize Spam-X plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist. Initial values will be taken from $_SPX_CONF if available (e.g. from
* an old config.php), uses $_SPX_DEFAULT otherwise.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_spamx()
{
    global $_CONF;

    $c = config::get_instance();

    if (!$c->group_exists('spamx')) {
        require_once $_CONF['path'].'plugins/spamx/sql/spamx_config_data.php';

        foreach ( $spamxConfigData AS $cfgItem ) {
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
?>