<?php
// +--------------------------------------------------------------------------+
// | Links Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | upgrade.php                                                              |
// |                                                                          |
// | Upgrade routines                                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs         - tony AT tonybibbs DOT com                  |
// |          Mark Limburg       - mlimburg AT users.sourceforge DOT net      |
// |          Jason Whittenburg  - jwhitten AT securitygeeks DOT com          |
// |          Dirk Haun          - dirk AT haun-online DOT de                 |
// |          Trinity Bays       - trinity93 AT gmail DOT com                 |
// |          Oliver Spiesshofer - oliver AT spiesshofer DOT com              |
// |          Euan McKay         - info AT heatherengineering DOT com         |
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

function links_upgrade()
{
    global $_TABLES, $_CONF, $_LI_CONF;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='links'");

    switch( $currentVersion ) {
        case '2.0.0' :
        case '2.0.1' :
            $c = config::get_instance();
            $c->add('target_blank',FALSE,'select',0, 1, 0, 55, true, 'links');
        case '2.0.2' :
        case '2.0.3' :
        case '2.0.4' :
        case '2.0.5' :
        case '2.0.6' :
        case '2.0.7' :
        case '2.0.8' :
        case '2.0.9' :
        case '2.1.0' :
        case '2.1.1' :
        case '2.1.2' :
            $c = config::get_instance();
            $c->add('displayblocks',0,'select',0, 0, 13, 60, true, 'links');
        case '2.1.3' :
        case '2.1.4' :
            DB_query("ALTER TABLE {$_TABLES['links']} CHANGE `lid` `lid` VARCHAR(128) NOT NULL DEFAULT '';",1);
            DB_query("ALTER TABLE {$_TABLES['linksubmission']} CHANGE `lid` `lid` VARCHAR(128) NOT NULL DEFAULT '';",1);

        case '2.1.5' :

        case '2.1.6' :
            DB_query("UPDATE {$_TABLES['linkcategories']} SET `created` = '1970-01-01 00:00:00' WHERE CAST(`created` AS CHAR(20)) = '0000-00-00 00:00:00';");
            DB_query("UPDATE {$_TABLES['linkcategories']} SET `modified` = '1970-01-01 00:00:00' WHERE CAST(`modified` AS CHAR(20)) = '0000-00-00 00:00:00';");
            DB_query("UPDATE {$_TABLES['links']} SET `date` = '1970-01-01 00:00:00' WHERE CAST(`date` AS CHAR(20)) = '0000-00-00 00:00:00';");
            DB_query("UPDATE {$_TABLES['linksubmission']} SET `date` = '1970-01-01 00:00:00' WHERE CAST(`date` AS CHAR(20)) = '0000-00-00 00:00:00';");
            DB_query("ALTER TABLE `{$_TABLES['linkcategories']}` CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL,
                        CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL;",1);
            DB_query("ALTER TABLE `{$_TABLES['links']}` CHANGE COLUMN `date` `date` DATETIME NULL DEFAULT NULL;",1);
            DB_query("ALTER TABLE `{$_TABLES['linksubmission']}` CHANGE COLUMN `date` `date` DATETIME NULL DEFAULT NULL;",1);

        default :
            links_update_config();
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='".$_LI_CONF['pi_version']."',pi_gl_version='".$_LI_CONF['gl_version']."' WHERE pi_name='links' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='links'") == $_LI_CONF['pi_version']) {
        return true;
    } else {
        return false;
    }
}

function links_update_config()
{
    global $_CONF, $_LI_CONF, $_TABLES;

    USES_lib_install();

    require_once $_CONF['path'].'plugins/links/sql/links_config_data.php';
    _update_config('links', $linksConfigData);

}
?>