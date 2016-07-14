<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | mysql_install.php                                                        |
// |                                                                          |
// | SQL commands                                                             |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
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

#
# Table structure for filemgmt categories - Top Level and subcategories
#

$_SQL['filemgmt_cat'] = "CREATE TABLE {$_TABLES['filemgmt_cat']} (
  cid int(5) unsigned NOT NULL auto_increment,
  pid int(5) unsigned NOT NULL default '0',
  title varchar(50) NOT NULL default '',
  imgurl varchar(150) NOT NULL default '',
  grp_access mediumint(8) NOT NULL default '0',
  grp_writeaccess mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (cid),
  KEY pid (pid)
) ENGINE=MyISAM";

#
# Table structure for filemgmt file details - main table
#

$_SQL['filemgmt_filedetail'] = "CREATE TABLE {$_TABLES['filemgmt_filedetail']} (
  lid int(11) unsigned NOT NULL auto_increment,
  cid int(5) unsigned NOT NULL default '0',
  title varchar(100) NOT NULL default '',
  url varchar(250) NOT NULL default '',
  homepage varchar(100) NOT NULL default '',
  version varchar(10) NOT NULL default '',
  size int(8) NOT NULL default '0',
  platform varchar(50) NOT NULL default '',
  logourl varchar(250) NOT NULL default '',
  submitter int(11) NOT NULL default '0',
  status tinyint(2) NOT NULL default '0',
  date int(10) NOT NULL default '0',
  hits int(11) unsigned NOT NULL default '0',
  rating double(6,4) NOT NULL default '0.0000',
  votes int(11) unsigned NOT NULL default '0',
  comments tinyint(2) NOT NULL default '1',
  PRIMARY KEY  (lid),
  KEY cid (cid),
  KEY status (status),
  KEY title (title(40))
) ENGINE=MyISAM";

#
# Table structure for filemgmt description details
#

$_SQL['filemgmt_filedesc'] = "CREATE TABLE {$_TABLES['filemgmt_filedesc']} (
  lid int(11) unsigned NOT NULL default '0',
  description text NOT NULL,
  KEY lid (lid)
) ENGINE=MyISAM";

#
# Table structure for table to hold reported broken links
#
$_SQL['filemgmt_brokenlinks'] = "CREATE TABLE {$_TABLES['filemgmt_brokenlinks']} (
  reportid int(5) NOT NULL auto_increment,
  lid int(11) NOT NULL default '0',
  sender int(11) NOT NULL default '0',
  ip varchar(20) NOT NULL default '',
  PRIMARY KEY  (reportid),
  KEY lid (lid),
  KEY sender (sender),
  KEY ip (ip)
) ENGINE=MyISAM";


#
# Table structure for filemgmt voting detail records
#

$_SQL['filemgmt_votedata'] = "CREATE TABLE {$_TABLES['filemgmt_votedata']} (
  ratingid int(11) unsigned NOT NULL auto_increment,
  lid int(11) unsigned NOT NULL default '0',
  ratinguser int(11) NOT NULL default '0',
  rating tinyint(3) unsigned NOT NULL default '0',
  ratinghostname varchar(60) NOT NULL default '',
  ratingtimestamp int(10) NOT NULL default '0',
  PRIMARY KEY  (ratingid),
  KEY ratinguser (ratinguser),
  KEY ratinghostname (ratinghostname),
  KEY lid (lid)
) ENGINE=MyISAM";

#
# Table structure for filemgmt download history
#

$_SQL['filemgmt_history'] = "CREATE TABLE {$_TABLES['filemgmt_history']} (
  uid mediumint(8) NOT NULL default '0',
  lid int(11) NOT NULL default '0',
  remote_ip varchar(15) NOT NULL default '',
  date datetime NOT NULL default '1000-01-01 00:00:00.000000',
  KEY lid (lid),
  KEY uid (uid)
) ENGINE=MyISAM";


$_SQL['d1'] = "INSERT INTO {$_TABLES['filemgmt_cat']} ('cid', 'pid', 'title', 'imgurl', 'grp_access', 'grp_writeaccess') VALUES (1,0,'General','',2,2);";
$_SQL['d2'] = "INSERT INTO {$_TABLES['filemgmt_filedesc']} ('lid', 'description') VALUES (1,'Yahoo User Interface Grids CSS framework cheat sheet in .pdf format.');";
$_SQL['d3'] = "INSERT INTO {$_TABLES['filemgmt_filedetail']} ('lid', 'cid', 'title', 'url', 'homepage', 'version', 'size', 'platform', 'logourl', 'submitter', 'status', 'date', 'hits', 'rating', 'votes', 'comments') VALUES (1,1,'YUI Grids CSS Cheat Sheet','css.pdf','http://developer.yahoo.com/yui/grids/','v2.6',131072 ,'','',2,1,NOW(),0,0.0000,0,1);";
?>