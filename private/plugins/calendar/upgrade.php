<?php
// +--------------------------------------------------------------------------+
// | Calendar Plugin - glFusion CMS                                           |
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

function calendar_upgrade()
{
    global $_TABLES, $_CONF, $_CA_CONF;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='calendar'");

    switch( $currentVersion ) {
        case '1.0.2' :
            // add new configuration option
            $c = config::get_instance();
            $c->add('only_admin_submit', 0,'select', 0, 0, 0, 15, true, 'calendar');
        case '1.0.3':
        case '1.0.4':
        case '1.0.5':
            DB_query("ALTER TABLE {$_TABLES['eventsubmission']} ADD  owner_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1' AFTER url");
        case '1.0.6' :
            $c = config::get_instance();
            $c->add('displayblocks', 0,'select', 0, 0, 13, 115, true, 'calendar');
            // allow events to be enabled/disabled
            DB_query("ALTER TABLE {$_TABLES['events']} ADD status tinyint(3) NOT NULL DEFAULT '1' AFTER eid");
            DB_query("ALTER TABLE {$_TABLES['eventsubmission']} ADD status tinyint(3) NOT NULL DEFAULT '1' AFTER eid");
            DB_query("ALTER TABLE {$_TABLES['personal_events']} ADD status tinyint(3) NOT NULL DEFAULT '1' AFTER eid");
        case '1.0.7' :
        case '1.0.8' :
        case '1.0.9' :
        case '1.1.0' :
            // no changes in db / configuration
        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='".$_CA_CONF['pi_version']."',pi_gl_version='".$_CA_CONF['gl_version']."' WHERE pi_name='calendar' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='calendar'") == $_CA_CONF['pi_version']) {
        return true;
    } else {
        return false;
    }
}
?>
