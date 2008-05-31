<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin                                                       |
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

$_SQL['st_config'] = "CREATE TABLE {$_TABLES['st_config']} (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `config_name` VARCHAR( 255 ) ,
    `config_value` VARCHAR( 255 ) NOT NULL
) AUTO_INCREMENT=1 ;";

$_SQL['st_menu_elements'] = "CREATE TABLE {$_TABLES['st_menu_elements']} (
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

$_SQL['st_menu_config'] = "CREATE TABLE {$_TABLES['st_menu_config']} (
  `id` int(11) NOT NULL auto_increment,
  `menu_id` int(11) NOT NULL,
  `tmbg` char(7) NOT NULL,
  `tmh` char(7) NOT NULL,
  `tmt` char(7) NOT NULL,
  `tmth` char(7) NOT NULL,
  `smth` char(7) NOT NULL,
  `smbg` char(7) NOT NULL,
  `smh` char(7) NOT NULL,
  `sms` char(7) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  PRIMARY KEY( `id` )
) AUTO_INCREMENT=1 ;";

$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_config']} VALUES (1,0,'#151515','#679ef1','#e8e8e8','#ffffff','#ffe600','#151515','#000000','#333333',1);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(1, 0, 0, 'Home', 2, '0', 10, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(2, 0, 0, 'Contribute', 2, '1', 20, 1, '', 13);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(3, 0, 0, 'Search', 2, '4', 30, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(4, 0, 0, 'Plugins', 1, '', 50, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(5, 4, 0, 'Links', 4, 'links', 10, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(6, 4, 0, 'Polls', 4, 'polls', 20, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(7, 4, 0, 'Calendar', 4, 'calendar', 30, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(8, 4, 0, 'Downloads', 4, 'filemgmt', 40, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(9, 4, 0, 'Forum', 4, 'forum', 50, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(10, 4, 0, 'Media Gallery', 4, 'mediagallery', 60, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(11, 0, 0, 'Directory', 2, '2', 40, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(12, 0, 0, 'Preferences', 2, '3', 60, 1, '', 13);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(13, 0, 0, 'Site Stats', 2, '5', 70, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(14, 0, 0, 'Topics', 3, '3', 80, 1, '', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(15, 0, 0, 'Admin Options', 3, '2', 90, 1, '', 1);";
?>