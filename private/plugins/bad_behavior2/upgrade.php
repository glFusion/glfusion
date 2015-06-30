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
// | Copyright (C) 2008-2015 by the following authors:                        |
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
    global $_TABLES, $_BB2_CONF;

    // Bad Behavior handles its database changes automatically,
    // so only update the version number

    $sql .= "CREATE TABLE IF NOT EXISTS `gl_bad_behavior2_ban` (
        `id` smallint(5) unsigned NOT NULL auto_increment,
        `ip` varbinary(16) NOT NULL,
        `type` tinyint(3) unsigned NOT NULL,
        `timestamp` int(8) NOT NULL DEFAULT '0',
        PRIMARY KEY  (id),
        UNIQUE ip (ip),
        INDEX type (type),
        INDEX timestamp (timestamp) ) ENGINE=MyISAM;";
    DB_query($sql);

    DB_query ("UPDATE {$_TABLES['plugins']} SET pi_version = '".$_BB2_CONF['pi_version']."', pi_gl_version = '".$_BB2_CONF['gl_version']."', pi_homepage = 'https://www.glfusion.org' WHERE pi_name = 'bad_behavior2'");

    return true;
}
?>
