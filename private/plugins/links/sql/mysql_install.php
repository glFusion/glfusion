<?php
// +--------------------------------------------------------------------------+
// | Links Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | mysql_install.php                                                        |
// |                                                                          |
// | Installation SQL                                                         |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Trinity Bays      - trinity93 AT gmail DOT com                  |
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
/**
 * Links plugin Installation SQL
 *
 * @package Links
 * @filesource
 * @version 2.0
 * @since GL 1.4.0
 * @copyright Copyright &copy; 2005-2008
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Trinity Bays <trinity93@gmail.com>
 * @author Tony Bibbs <tony@tonybibbs.com>
 * @author Tom Willett <twillett@users.sourceforge.net>
 * @author Blaine Lang <langmail@sympatico.ca>
 * @author Dirk Haun <dirk@haun-online.de>
 *
 */

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$_SQL['linkcategories'] = "
CREATE TABLE {$_TABLES['linkcategories']} (
  cid varchar(32) NOT NULL,
  pid varchar(32) NOT NULL,
  category varchar(32) NOT NULL,
  description text DEFAULT NULL,
  tid varchar(20) DEFAULT NULL,
  created datetime DEFAULT NULL,
  modified datetime DEFAULT NULL,
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '2',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  PRIMARY KEY (cid),
  KEY links_pid (pid)
) ENGINE=MyISAM
";

$_SQL['links'] = "
CREATE TABLE {$_TABLES['links']} (
  lid varchar(128) NOT NULL default '',
  cid varchar(32) default NULL,
  url varchar(255) default NULL,
  description text,
  title varchar(96) default NULL,
  hits int(11) NOT NULL default '0',
  date datetime default NULL,
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '2',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  INDEX links_category(cid),
  INDEX links_date(date),
  PRIMARY KEY (lid)
) ENGINE=MyISAM
";

$_SQL['linksubmission'] = "
CREATE TABLE {$_TABLES['linksubmission']} (
  lid varchar(128) NOT NULL default '',
  cid varchar(32) default NULL,
  url varchar(255) default NULL,
  description text,
  title varchar(96) default NULL,
  hits int(11) default NULL,
  date datetime default NULL,
  owner_id mediumint(8) unsigned NOT NULL default '1',
  PRIMARY KEY (lid)
) ENGINE=MyISAM
";

// Links Default Data

//$_SQL['links_data'][] = "INSERT INTO {$_TABLES['linkcategories']} (cid, pid, category, description, tid, created, modified, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('site', 'root', 'Root', 'Website root', NULL, NOW(), NOW(), 2, %%admin_group_id%%, 3, 3, 2, 2);";
//$_SQL['links_data'][] = "INSERT INTO {$_TABLES['linkcategories']} (cid, pid, category, description, tid, created, modified, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('blog-roll', 'site', 'Blog Roll', 'glFusion Related Sites', NULL, NOW(), NOW(), 2, %%admin_group_id%%, 3, 3, 2, 2);";
//$_SQL['links_data'][] = "INSERT INTO {$_TABLES['links']} (lid, cid, url, description, title, hits, date, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('glfusion.org', 'blog-roll', 'http://www.glfusion.org/', 'Visit glFusion - A site dedicated to enhancing glFusion.', 'glFusion - Enhancing glFusion', 1, NOW(), 2, %%admin_group_id%%, 3, 3, 2, 2);";
//$_SQL['links_data'][] = "INSERT INTO {$_TABLES['links']} (lid, cid, url, description, title, hits, date, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('glfusion_wiki', 'blog-roll', 'http://www.glfusion.org/wiki/doku.php?id=glfusion:start', 'The glFusion documentation wiki.', 'glFusion Wiki', 1, NOW(), 2, %%admin_group_id%%, 3, 3, 2, 2);";

?>