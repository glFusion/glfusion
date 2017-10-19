<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | bad_behavior2.php                                                        |
// |                                                                          |
// | Plugin system integration options                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2016 by the following authors:                        |
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

$_SQL['bad_behavior2'] = "CREATE TABLE {$_TABLES['bad_behavior2']} (
    `id` INT(10) unsigned NOT NULL auto_increment,
    `ip` TEXT NOT NULL,
    `date` DATETIME NOT NULL default '1000-01-01 00:00:00.000000',
    `request_method` TEXT NOT NULL,
    `request_uri` TEXT NOT NULL,
    `server_protocol` TEXT NOT NULL,
    `http_headers` TEXT NOT NULL,
    `user_agent` TEXT NOT NULL,
    `request_entity` TEXT NOT NULL,
    `key` TEXT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX (`ip`(15)),
    INDEX (`user_agent`(10))
) ENGINE=MyISAM;";

$_SQL['bad_behavior2_ban'] = "CREATE TABLE {$_TABLES['bad_behavior2_ban']} (
    `id` smallint(5) unsigned NOT NULL auto_increment,
    `ip` varbinary(16) NOT NULL,
    `type` tinyint(3) unsigned NOT NULL,
    `timestamp` int(8) NOT NULL DEFAULT '0',
    `reason` VARCHAR(255) NULL DEFAULT NULL,
    PRIMARY KEY  (id),
    UNIQUE ip (ip),
    INDEX type (type),
    INDEX timestamp (timestamp)
) ENGINE=MyISAM;";

$_SQL['bad_behavior2_whitelist'] = "CREATE TABLE {$_TABLES['bad_behavior2_whitelist']} (
    `id` smallint(5) unsigned NOT NULL auto_increment,
    `item` varchar(254) NOT NULL DEFAULT '',
    `type` varchar(128) NOT NULL DEFAULT 'IP',
    `reason` VARCHAR(255) NULL DEFAULT NULL,
    `timestamp` int(8) NOT NULL DEFAULT '0',
    PRIMARY KEY  (id),
    INDEX type (type),
    INDEX timestamp (timestamp)
) ENGINE=MyISAM;";

$_SQL['bad_behavior2_blacklist'] = "CREATE TABLE {$_TABLES['bad_behavior2_blacklist']} (
    `id` smallint(5) unsigned NOT NULL auto_increment,
    `item` varchar(254) NOT NULL DEFAULT '',
    `type` varchar(128) NOT NULL DEFAULT 'IP',
    `autoban` tinyint(3) unsigned NOT NULL DEFAULT 0,
    `reason` VARCHAR(255) NULL DEFAULT NULL,
    `timestamp` int(8) NOT NULL DEFAULT '0',
    PRIMARY KEY  (id),
    INDEX type (type),
    INDEX timestamp (timestamp)
) ENGINE=MyISAM;";

?>