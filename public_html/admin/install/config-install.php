<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | config-install.php                                                       |
// |                                                                          |
// | Initial configuration setup.                                             |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2007-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Aaron Blankstein  - kantai AT gmail DOT com                     |
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
    die ('This file can not be used on its own!');
}

function install_config($site_url, $coreConfigData)
{
    global $_CONF, $_TABLES;

    if (preg_match("@^https://@",$site_url)) {
        $cookiesecure = 1;
    } else {
        $cookiesecure = 0;
    }

    $c = config::get_instance();

    foreach ( $coreConfigData AS $cfgItem ) {
        // set default values...
        if ( $cfgItem['name'] == 'default_photo' )
            $cfgItem['default_value'] = $def_photo;
        if ( $cfgItem['name'] == 'site_disabled_msg')
            $cfgItem['default_value'] = urldecode($site_url) . '/sitedown.html';
        if ( $cfgItem['name'] == 'cookiesecure')
            $cfgItem['default_value'] = $cookiesecure;

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
    // hidden system configuration options
    $c->add('social_site_extra','', 'text',0,0,NULL,1,TRUE,'social_internal');
}
?>