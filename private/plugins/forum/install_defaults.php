<?php
/**
* glFusion CMS
*
* Forum Plugin Configuration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
*   Initialize agenda plugin configuration
*
*   @return boolean             true: success; false: an error occurred
*/
function plugin_initconfig_forum()
{
    global $_CONF;

    $c = config::get_instance();

    if (!$c->group_exists('forum')) {
        require_once $_CONF['path'].'plugins/forum/sql/forum_config_data.php';

        foreach ( $forumConfigData AS $cfgItem ) {
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
