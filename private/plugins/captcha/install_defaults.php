<?php
/**
* glFusion CMS
*
* CAPTCHA Plugin Defaults Installation
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
* the captcha plugin's config array
*/
global $_CP_CONF;
if (!isset($_CP_CONF) || empty($_CP_CONF)) {
    $_CP_CONF = array();
    require_once dirname(__FILE__) . '/captcha.php';
}

/**
* Initialize CAPTCHA plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_captcha()
{
    global $_CONF, $_CP_CONF;

    $c = config::get_instance();

    if (!$c->group_exists('captcha')) {
        require_once $_CONF['path'].'plugins/captcha/sql/captcha_config_data.php';

        foreach ( $captchaConfigData AS $cfgItem ) {
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