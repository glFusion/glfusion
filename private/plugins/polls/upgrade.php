<?php
// +--------------------------------------------------------------------------+
// | Polls Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | upgrade.php                                                              |
// |                                                                          |
// | Upgrade routines                                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony AT tonybibbs DOT com                    |
// |          Tom Willett      - twillett AT users DOT sourceforge DOT net    |
// |          Blaine Lang      - langmail AT sympatico DOT ca                 |
// |          Dirk Haun        - dirk AT haun-online DOT de                   |
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

function polls_upgrade()
{
    global $_TABLES, $_CONF, $_PO_CONF;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='polls'");

    switch( $currentVersion ) {
        case '2.0.0' :
        case '2.0.1' :
        case '2.0.2' :
        case '2.0.3' :
        case '2.0.4' :
        case '2.0.5' :
        case '2.0.6' :
        case '2.0.7' :
        case '2.0.8' :
        case '2.0.9' :
        case '2.1.0' :
            $c = config::get_instance();
            $c->add('displayblocks',0, 'select', 0, 0, 13, 85, true, 'polls');

        case '2.1.1' :
            DB_query("ALTER TABLE {$_TABLES['pollanswers']} CHANGE `pid` `pid` VARCHAR(128) NOT NULL DEFAULT '';",1);
            DB_query("ALTER TABLE {$_TABLES['pollquestions']} CHANGE `pid` `pid` VARCHAR(128) NOT NULL;",1);
            DB_query("ALTER TABLE {$_TABLES['polltopics']} CHANGE `pid` `pid` VARCHAR(128) NOT NULL;",1);
            DB_query("ALTER TABLE {$_TABLES['pollvoters']} CHANGE `pid` `pid` VARCHAR(128) NOT NULL DEFAULT '';",1);

        case '2.1.2' :
            DB_query("ALTER TABLE {$_TABLES['pollvoters']} ADD `uid` MEDIUMINT NOT NULL DEFAULT '1' AFTER `ipaddress`;",1);
            DB_query("ALTER TABLE {$_TABLES['polltopics']} ADD `login_required` TINYINT NOT NULL DEFAULT '0' AFTER `is_open`;",1);

        case '2.2.0' :
            DB_query("ALTER TABLE {$_TABLES['pollvoters']} CHANGE `pid` `pid` VARCHAR(128) NOT NULL DEFAULT '';",1);

        case '2.2.1' :
            DB_query("ALTER TABLE {$_TABLES['pollvoters']} ADD INDEX(`pid`);",1);
            DB_query("ALTER TABLE {$_TABLES['polltopics']} ADD `description` TEXT NULL DEFAULT NULL AFTER `topic`;",1);

        case '2.2.2' :
            // no changes

        case '2.2.3' :
            DB_query("UPDATE `{$_TABLES['polltopics']}` SET `date` = '1970-01-01 00:00:00' WHERE CAST(`date` AS CHAR(20)) = '0000-00-00 00:00:00';");
            DB_query("UPDATE `{$_TABLES['polltopics']}` SET `date` = '1970-01-01 00:00:00' WHERE CAST(`date` AS CHAR(20)) = '1000-01-01 00:00:00';");
            DB_query("ALTER TABLE `{$_TABLES['polltopics']}` CHANGE COLUMN `date` `date` DATETIME NULL DEFAULT NULL;",1);

        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='".$_PO_CONF['pi_version']."',pi_gl_version='".$_PO_CONF['gl_version']."' WHERE pi_name='polls' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='polls'") == $_PO_CONF['pi_version']) {
        return true;
    } else {
        return false;
    }
}
?>