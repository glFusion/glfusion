<?php
// +--------------------------------------------------------------------------+
// | gl Labs Menu Editor Plugin 1.0                                           |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans              - mark at gllabs.org                          |
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
//

$_SQL['mb_elements'] = "CREATE TABLE {$_TABLES['mb_elements']} (
    `id` int(11) NOT NULL auto_increment,
    `pid` int(11) NOT NULL,
    `menu_id` int(11) NOT NULL default '0',
    `element_label` varchar(255) NOT NULL,
    `element_type` int(11) NOT NULL,
    `element_subtype` varchar(255) NOT NULL,
    `element_order` int(11) NOT NULL,
    `element_active` tinyint(4) NOT NULL,
    `element_url` varchar(255) NOT NULL,
    `group_id` mediumint(9) NOT NULL,
    PRIMARY KEY( `id` ),
    INDEX ( `pid` )
) AUTO_INCREMENT=1 ;";

$_SQL['mb_config'] = "CREATE TABLE {$_TABLES['mb_config']} (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `menu_id` INT NOT NULL ,
    `hcolor` CHAR( 7 ) NOT NULL ,
    `hhcolor` CHAR( 7 ) NOT NULL ,
    `htext` CHAR( 7 ) NOT NULL ,
    `hhtext` CHAR( 7 ) NOT NULL ,
    `enabled` TINYINT NOT NULL
) AUTO_INCREMENT=1 ;";

$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_config']} (`id` ,`menu_id` ,`hcolor` ,`hhcolor` ,`htext` ,`hhtext` ,`enabled` )VALUES (NULL , '0', '#000000', '#001eff', '#ffffff', '#ffffff', '1');";

$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(1, 0, 0, 'Home', 2, '0', 10, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(2, 0, 0, 'Contribute', 2, '1', 20, 1, '', 13);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(3, 0, 0, 'Search', 2, '4', 30, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(4, 0, 0, 'Plugins', 1, '', 50, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(5, 4, 0, 'Links', 4, 'links', 10, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(6, 4, 0, 'Polls', 4, 'polls', 20, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(7, 4, 0, 'Calendar', 4, 'calendar', 30, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(8, 4, 0, 'Downloads', 4, 'filemgmt', 40, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(9, 4, 0, 'Forum', 4, 'forum', 50, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(10, 4, 0, 'Media Gallery', 4, 'mediagallery', 60, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(11, 0, 0, 'Directory', 2, '2', 40, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(12, 0, 0, 'Preferences', 2, '3', 60, 1, '', 13);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(13, 0, 0, 'Site Stats', 2, '5', 70, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(14, 0, 0, 'Topics', 3, '3', 80, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mb_elements']} VALUES(15, 0, 0, 'Admin Options', 3, '2', 90, 1, '', 1);";
?>