<?php
// +--------------------------------------------------------------------------+
// | Spam-X Plugin - glFusion CMS                                             |
// +--------------------------------------------------------------------------+
// | mysql_install.php                                                        |
// |                                                                          |
// | Installation SQL.                                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2016-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tom Willett       - tomw AT pigstye DOT net                     |
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

$_SQL['spamx'] = "
CREATE TABLE {$_TABLES['spamx']} (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(20) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  PRIMARY KEY (id),
  INDEX spamx_name(name)
) ENGINE=MyISAM
";

$_SQL['spamx_stats'] = "
CREATE TABLE {$_TABLES['spamx_stats']} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(128) NOT NULL DEFAULT '',
  `type` VARCHAR(50) NOT NULL DEFAULT '',
  `blockdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` VARCHAR(50) NOT NULL DEFAULT '',
  `email` VARCHAR(50) NOT NULL DEFAULT '',
  `username` VARCHAR(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX `type` (`type`),
  INDEX `blockdate` (`blockdate`)
) ENGINE=MyISAM
";
?>