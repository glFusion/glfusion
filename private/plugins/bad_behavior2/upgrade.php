<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | upgrade.php                                                              |
// |                                                                          |
// | This file has the functions necessary to upgrade Bad Behavior2           |
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2014 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2016 by the following authors:                        |
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
    die ('This file can not be used on its own!');
}

function bad_behavior2_upgrade ()
{
    global $_TABLES, $_CONF, $_BB2_CONF;

    $sql = '';

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='bad_behavior2'");

    switch( $currentVersion ) {
        case '2.0.13' :
        case '2.0.13a' :
        case '2.0.24' :
        case '2.0.26' :
        case '2.0.27' :
        case '2.0.28' :
        case '2.0.29' :
        case '2.0.35' :
        case '2.0.36' :
        case '2.0.37' :
        case '2.0.38' :
        case '2.0.39' :
        case '2.0.40' :
        case '2.0.41' :
        case '2.0.42' :
        case '2.0.43' :
        case '2.0.44' :
        case '2.0.45' :
        case '2.0.46' :
        case '2.0.47' :
        case '2.0.48' :
        case '2.0.49' :
            $sql .= "CREATE TABLE IF NOT EXISTS {$_TABLES['bad_behavior2_ban']} (
                `id` smallint(5) unsigned NOT NULL auto_increment,
                `ip` varbinary(16) NOT NULL,
                `type` tinyint(3) unsigned NOT NULL,
                `timestamp` int(8) NOT NULL DEFAULT '0',
                `reason` VARCHAR(255) NULL DEFAULT NULL,
                PRIMARY KEY  (id),
                UNIQUE ip (ip),
                INDEX type (type),
                INDEX timestamp (timestamp) ) ENGINE=MyISAM;";
            DB_query($sql,1);

            require_once $_CONF['path_system'] . 'classes/config.class.php';
            $c = config::get_instance();

            // Subgroup: Spam / Bot Protection
            $c->add('sg_spam', NULL, 'subgroup', 8, 0, NULL, 0, TRUE);
            $c->add('fs_spam_config', NULL, 'fieldset', 8, 1, NULL, 0, TRUE);
            $c->add('bb2_enabled',1,'select',8,1,0,10,TRUE);
            $c->add('bb2_ban_enabled',0,'select',8,1,0,20,TRUE);
            $c->add('bb2_ban_timeout',24,'text',8,1,0,30,TRUE);
            $c->add('bb2_display_stats',0,'select',8,1,0,40,TRUE);
            $c->add('bb2_strict',0,'select',8,1,0,50,TRUE);
            $c->add('bb2_verbose',0,'select',8,1,0,60,TRUE);
            $c->add('bb2_logging',1,'select',8,1,0,70,TRUE);
            $c->add('bb2_httpbl_key','','text',8,1,NULL,80,TRUE);
            $c->add('bb2_httpbl_threat',25,'text',8,1,NULL,90,TRUE);
            $c->add('bb2_httpbl_maxage',30,'text',8,1,NULL,100,TRUE);
            $c->add('bb2_offsite_forms',0,'select',8,1,0,110,TRUE);
            $c->add('bb2_eu_cookie',0,'select',8,1,0,120,TRUE);

        case '2.0.50' :
            DB_query("ALTER TABLE {$_TABLES['bad_behavior2']} DROP `id`;",1);
            DB_query("ALTER TABLE {$_TABLES['bad_behavior2']} ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);",1);

        default:
            break;
    }

    DB_query ("UPDATE {$_TABLES['plugins']} SET pi_version = '".$_BB2_CONF['pi_version']."', pi_gl_version = '".$_BB2_CONF['gl_version']."', pi_homepage = 'https://www.glfusion.org' WHERE pi_name = 'bad_behavior2'");

    return true;
}
?>
