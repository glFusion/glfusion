<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | mysql_1.2.2_to_1.3.0.php                                                 |
// |                                                                          |
// | glFusion Upgrade SQL                                                     |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2012 by the following authors:                             |
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

$_SQL = array();

$_SQL[] = "CREATE TABLE {$_TABLES['autotags']} (
  tag varchar( 24 ) NOT NULL DEFAULT '',
  description varchar( 128 ) DEFAULT '',
  is_enabled tinyint( 1 ) NOT NULL DEFAULT '0',
  is_function tinyint( 1 ) NOT NULL DEFAULT '0',
  replacement text,
  PRIMARY KEY ( tag )
) ENGINE=MYISAM;
";

$_SQL[] = "CREATE TABLE {$_TABLES['autotag_perm']} (
  autotag_id varchar(128) NOT NULL,
  autotag_namespace varchar(128) NOT NULL,
  autotag_name varchar(128) NOT NULL,
  PRIMARY KEY (autotag_id)
) ENGINE=MyISAM
";

$_SQL[] = "CREATE TABLE {$_TABLES['autotag_usage']} (
  autotag_id varchar(128) NOT NULL,
  autotag_allowed tinyint(1) NOT NULL DEFAULT '1',
  usage_namespace varchar(128) NOT NULL,
  usage_operation varchar(128) NOT NULL,
  KEY autotag_id (autotag_id)
) ENGINE=MyISAM
";

$_SQL[] = "CREATE TABLE {$_TABLES['subscriptions']} (
  sub_id int(11) NOT NULL AUTO_INCREMENT,
  type varchar(50) NOT NULL,
  category varchar(128) NOT NULL DEFAULT '',
  category_desc varchar(255) NOT NULL DEFAULT '',
  id varchar(40) NOT NULL,
  id_desc varchar(255) NOT NULL DEFAULT '',
  uid int(11) NOT NULL,
  date_added datetime NOT NULL,
  PRIMARY KEY (sub_id),
  UNIQUE KEY descriptor (type,category,id,uid),
  KEY uid (uid)
) ENGINE=MyISAM
";

$_SQL[] = "CREATE TABLE {$_TABLES['logo']} (
  id int(11) NOT NULL auto_increment,
  config_name varchar(255) default NULL,
  config_value varchar(255) NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY config_name (config_name)
) ENGINE=MyISAM;
";

$_SQL[] = "CREATE TABLE {$_TABLES['menu']} (
  id int(11) NOT NULL auto_increment,
  menu_name varchar(64) NOT NULL,
  menu_type tinyint(4) NOT NULL,
  menu_active tinyint(3) NOT NULL,
  group_id mediumint(9) NOT NULL,
  PRIMARY KEY  (id),
  KEY menu_name (menu_name)
) ENGINE=MyISAM;
";

$_SQL[] = "CREATE TABLE {$_TABLES['menu_elements']} (
  id int(11) NOT NULL auto_increment,
  pid int(11) NOT NULL,
  menu_id int(11) NOT NULL default '0',
  element_label varchar(255) NOT NULL,
  element_type int(11) NOT NULL,
  element_subtype varchar(255) NOT NULL,
  element_order int(11) NOT NULL,
  element_active tinyint(4) NOT NULL,
  element_url varchar(255) NOT NULL,
  element_target varchar(255) NOT NULL,
  group_id mediumint(9) NOT NULL,
  PRIMARY KEY( id ),
  INDEX ( pid )
) ENGINE=MyISAM;
";

$_SQL[] = "ALTER TABLE {$_TABLES['sessions']} ADD browser varchar(255) default '' AFTER sess_id";

$_SQL[] = "ALTER TABLE {$_TABLES['users']} ADD account_type smallint(5) NOT NULL default '1' AFTER status";

$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='l F d, Y @h:iA' WHERE dfid=1";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='l F d, Y @H:i' WHERE dfid=2";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='l F d @H:i' WHERE dfid=4";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='H:i d F Y' WHERE dfid=5";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='H:i l d F Y' WHERE dfid=6";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='h:iA -- l F d Y' WHERE dfid=7";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='D F d, h:iA' WHERE dfid=8";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='D F d, H:i' WHERE dfid=9";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='m-d-y H:i' WHERE dfid=10";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='d-m-y H:i' WHERE dfid=11";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='m-d-y h:iA' WHERE dfid=12";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='h:iA  F d, Y' WHERE dfid=13";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='D M d, \'y h:iA' WHERE dfid=14";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='Day z, h ish' WHERE dfid=15";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='y-m-d h:i' WHERE dfid=16";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='d/m/y H:i' WHERE dfid=17";
$_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='D d M h:iA' WHERE dfid=18";
?>