<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | install_defaults.php                                                     |
// |                                                                          |
// | Initial Installation Defaults used when loading the online configuration |
// | records. These settings are only used during the initial installation    |
// | and not referenced any more once the plugin is installed.                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
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