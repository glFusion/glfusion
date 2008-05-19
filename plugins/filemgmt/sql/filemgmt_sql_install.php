<?php

/**
* SQL Commands for the Filemgmt Plugin
* Last updated March 28/2004
* blaine@portalparts.com
**/

#
# Table structure for filemgmt categories - Top Level and subcategories
#

$_SQL[] = "CREATE TABLE {$_FM_TABLES['filemgmt_cat']} (
  cid int(5) unsigned NOT NULL auto_increment,
  pid int(5) unsigned NOT NULL default '0',
  title varchar(50) NOT NULL default '',
  imgurl varchar(150) NOT NULL default '',
 `grp_access` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (cid),
  KEY pid (pid)
) TYPE=MyISAM";

#
# Table structure for filemgmt file details - main table
#

$_SQL[] = "CREATE TABLE {$_FM_TABLES['filemgmt_filedetail']} (
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
) TYPE=MyISAM";

#
# Table structure for filemgmt description details
#

$_SQL[] = "CREATE TABLE {$_FM_TABLES['filemgmt_filedesc']} (
  lid int(11) unsigned NOT NULL default '0',
  description text NOT NULL,
  KEY lid (lid)
) TYPE=MyISAM";

#
# Table structure for table to hold reported broken links
#
$_SQL[] = "CREATE TABLE {$_FM_TABLES['filemgmt_brokenlinks']} (
  reportid int(5) NOT NULL auto_increment,
  lid int(11) NOT NULL default '0',
  sender int(11) NOT NULL default '0',
  ip varchar(20) NOT NULL default '',
  PRIMARY KEY  (reportid),
  KEY lid (lid),
  KEY sender (sender),
  KEY ip (ip)
) TYPE=MyISAM";


#
# Table structure for filemgmt voting detail records
#

$_SQL[] = "CREATE TABLE {$_FM_TABLES['filemgmt_votedata']} (
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
) TYPE=MyISAM";


#
# Table structure for filemgmt download history
#

$_SQL[] = "CREATE TABLE {$_FM_TABLES['filemgmt_history']} (
  uid mediumint(8) NOT NULL default '0',
  lid int(11) NOT NULL default '0',
  remote_ip varchar(15) NOT NULL default '',
  date datetime NOT NULL default '0000-00-00 00:00:00',
  KEY lid (lid),
  KEY uid (uid)
) TYPE=MyISAM";


?>