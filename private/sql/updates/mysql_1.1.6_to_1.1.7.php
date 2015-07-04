<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | mysql_1.1.6_to_1.1.7.php                                                 |
// |                                                                          |
// | glFusion Upgrade SQL                                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

$_SQL[] = "CREATE TABLE IF NOT EXISTS {$_TABLES['rating']} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(254) NOT NULL DEFAULT '',
  `item_id` varchar(40) NOT NULL,
  `votes` int(11) NOT NULL,
  `rating` decimal(4,2) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM;";

$_SQL[] = "CREATE TABLE IF NOT EXISTS {$_TABLES['rating_votes']} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(254) NOT NULL DEFAULT '',
  `item_id` varchar(40) NOT NULL,
  `rating` int(11) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) NOT NULL,
  `ip_address` varchar(14) NOT NULL,
  `ratingdate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `ip_address` (`ip_address`),
  KEY `type` (`type`)
) ENGINE=MyISAM;";

$_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `rating` float NOT NULL DEFAULT '0' AFTER hits";
$_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `votes` int(11) NOT NULL DEFAULT '0' AFTER rating";
?>