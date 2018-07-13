<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | install_defaults.php                                                     |
// |                                                                          |
// | Configuration Defaults                                                   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2018 by the following authors:                        |
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
* Initialize Bad Behavior2 plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_bad_behavior2()
{
    $c = config::get_instance();

    // Subgroup: Spam / Bot Protection
    $c->add('sg_spam', NULL, 'subgroup', 8, 0, NULL, 0, TRUE);
    $c->add('fs_spam_config', NULL, 'fieldset', 8, 1, NULL, 0, TRUE);
    $c->add('bb2_enabled',1,'select',8,1,0,10,TRUE);
    $c->add('bb2_ban_enabled',0,'select',8,1,0,20,TRUE);
    $c->add('bb2_ban_log',1,'select',8,1,0,30,TRUE);
    $c->add('bb2_ban_timeout',24,'text',8,1,0,40,TRUE);
    $c->add('bb2_strict',0,'select',8,1,0,50,TRUE);
    $c->add('bb2_verbose',0,'select',8,1,0,60,TRUE);
    $c->add('bb2_logging',1,'select',8,1,0,70,TRUE);
    $c->add('bb2_httpbl_key','','text',8,1,NULL,80,TRUE);
    $c->add('bb2_httpbl_threat',25,'text',8,1,NULL,90,TRUE);
    $c->add('bb2_httpbl_maxage',30,'text',8,1,NULL,100,TRUE);
    $c->add('bb2_offsite_forms',0,'select',8,1,0,110,TRUE);
    $c->add('bb2_reverse_proxy',0,'select',8,1,0,120,TRUE);
    $c->add('bb2_reverse_proxy_header','X-Forwarded-For','text',8,1,0,130,TRUE);
    $c->add('bb2_reverse_proxy_addresses',array(),'*text',8,1,0,140,TRUE);

    return true;
}
?>