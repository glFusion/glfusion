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

/*
 * CAPTCHA default settings
 *
 * Initial Installation Defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 *
 */

global $_CP_DEFAULT;
$_CP_DEFAULT = array();

$_CP_DEFAULT['expire'] = 900;
$_CP_DEFAULT['anonymous_only'] = '1';
$_CP_DEFAULT['remoteusers'] = 0;
$_CP_DEFAULT['debug'] = 0;
$_CP_DEFAULT['enable_comment'] = 1;
$_CP_DEFAULT['enable_contact'] = 1;
$_CP_DEFAULT['enable_emailstory'] = 1;
$_CP_DEFAULT['enable_forum'] = 1;
$_CP_DEFAULT['enable_registration'] = 1;
$_CP_DEFAULT['enable_mediagallery'] = 1;
$_CP_DEFAULT['enable_rating'] = 1;
$_CP_DEFAULT['enable_story'] = 1;
$_CP_DEFAULT['enable_calendar'] = 1;
$_CP_DEFAULT['enable_links'] = 1;
$_CP_DEFAULT['gfxDriver'] = 6;
$_CP_DEFAULT['gfxFormat'] = 'jpg';
$_CP_DEFAULT['imageset'] = 'default';
$_CP_DEFAULT['logging'] = 0;
$_CP_DEFAULT['gfxPath'] = '';
$_CP_DEFAULT['publickey']  = '';
$_CP_DEFAULT['privatekey'] = '';
$_CP_DEFAULT['recaptcha_theme'] = 'light';


/**
* the captcha plugin's config array
*/
global $_CP_CONF;
$_CP_CONF = array();

/**
* Initialize CAPTCHA plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist. Initial values will be taken from $_FM_CONF if available (e.g. from
* an old config.php), uses $_FM_DEFAULT otherwise.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_captcha()
{
    global $_CP_CONF, $_CP_DEFAULT;

    if (is_array($_CP_CONF) && (count($_CP_CONF) > 1)) {
        $_CP_DEFAULT = array_merge($_CP_DEFAULT, $_CP_CONF);
    }
    $c = config::get_instance();
    if (!$c->group_exists('captcha')) {

        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, 'captcha');
        $c->add('cp_public', NULL, 'fieldset', 0, 0, NULL, 0, true, 'captcha');

        $c->add('gfxDriver', $_CP_DEFAULT['gfxDriver'], 'select',
                0, 0, 2, 10, true, 'captcha');
        $c->add('imageset', $_CP_DEFAULT['imageset'], 'select',
                0, 0, 4, 20, true, 'captcha');
        $c->add('gfxFormat', $_CP_DEFAULT['gfxFormat'], 'select',
                0, 0, 5, 30, true, 'captcha');
        $c->add('gfxPath', $_CP_DEFAULT['gfxPath'],'text',
                0, 0, 0, 40, true, 'captcha');

        $c->add('publickey', $_CP_DEFAULT['publickey'],'text',
                0, 0, 0, 42, true, 'captcha');
        $c->add('privatekey', $_CP_DEFAULT['privatekey'],'text',
                0, 0, 0, 44, true, 'captcha');
        $c->add('recaptcha_theme', $_CP_DEFAULT['recaptcha_theme'],'select',
                0, 0, 6, 46, true, 'captcha');
        $c->add('debug', $_CP_DEFAULT['debug'], 'select',
                0, 0, 0, 50, true, 'captcha');
        $c->add('logging', $_CP_DEFAULT['logging'],'select',
                0, 0, 0, 60, true, 'captcha');
        $c->add('expire', $_CP_DEFAULT['expire'],'text',
                0, 0, 0, 70, true, 'captcha');

        $c->add('cp_integration', NULL, 'fieldset', 0, 1, NULL, 0, true,
                'captcha');

        $c->add('anonymous_only', $_CP_DEFAULT['anonymous_only'],'select',
                0, 1, 0, 10, true, 'captcha');
        $c->add('remoteusers', $_CP_DEFAULT['remoteusers'],'select',
                0, 1, 0, 20, true, 'captcha');
        $c->add('enable_comment', $_CP_DEFAULT['enable_comment'],'select',
                0, 1, 0, 30, true, 'captcha');
        $c->add('enable_story', $_CP_DEFAULT['enable_story'],'select',
                0, 1, 0, 40, true, 'captcha');
        $c->add('enable_registration', $_CP_DEFAULT['enable_registration'],'select',
                0, 1, 0, 50, true, 'captcha');
        $c->add('enable_contact', $_CP_DEFAULT['enable_contact'],'select',
                0, 1, 0, 60, true, 'captcha');
        $c->add('enable_emailstory', $_CP_DEFAULT['enable_emailstory'],'select',
                0, 1, 0, 70, true, 'captcha');
        $c->add('enable_forum', $_CP_DEFAULT['enable_forum'],'select',
                0, 1, 0, 80, true, 'captcha');
        $c->add('enable_mediagallery', $_CP_DEFAULT['enable_mediagallery'],'select',
                0, 1, 0, 90, true, 'captcha');
        $c->add('enable_links', $_CP_DEFAULT['enable_links'],'select',
                0, 1, 0, 100, true, 'captcha');
        $c->add('enable_calendar', $_CP_DEFAULT['enable_calendar'],'select',
                0, 1, 0, 110, true, 'captcha');
    }

    return true;
}
?>