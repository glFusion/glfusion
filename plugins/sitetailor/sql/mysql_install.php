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
    `element_target` varchar(255) NOT NULL,
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
  `gorc` tinyint(4) NOT NULL,
  `bgimage` varchar(255) NOT NULL,
  `hoverimage` varchar(255) NOT NULL,
  `parentimage` varchar(255) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  PRIMARY KEY( `id` )
) AUTO_INCREMENT=1 ;";

$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_config']} (`id`, `config_name`, `config_value`) VALUES (1,'use_graphic_logo','1'),(2,'display_site_slogan','1'),(3,'logo_name','logo1234.png');";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_config']} VALUES (1,0,'#151515','#3667c0','#CCCCCC','#ffffff','#679EF1','#151515','#333333','#000000',1,'menu_bg.gif','menu_hover_bg.gif','menu_parent.png',1);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(1, 0, 0, 'Home', 2, '0', 10, 1, '','', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(2, 0, 0, 'Contribute', 2, '1', 20, 1, '','', 13);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(3, 0, 0, 'Search', 2, '4', 30, 1, '','', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(4, 0, 0, 'Plugins', 3, '5', 50, 1, '','', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(5, 0, 0, 'Directory', 2, '2', 40, 1, '','', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(6, 0, 0, 'Preferences', 2, '3', 60, 1, '','', 13);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(7, 0, 0, 'Site Stats', 2, '5', 70, 1, '','', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(8, 0, 0, 'Topics', 3, '3', 80, 1, '','', 2);";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} VALUES(9, 0, 0, 'Admin Options', 3, '2', 90, 1, '','', 1);";
?>