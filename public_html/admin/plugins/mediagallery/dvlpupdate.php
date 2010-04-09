<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// | Development Code Upgrade routine - valid for MG v1.6.0svn                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2010 by the following authors:                        |
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
//
require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

// Only let admin users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Development Code Upgrade Routine.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

COM_errorLog("MediaGallery: Running code update for Media Gallery v1.6.1svn");

$retval .= 'Performing database upgrades if necessary...<br>';

$_SQL = array();
/* ---------------------------------------------------------------------------------------------------------------------
$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `tnheight` INT NOT NULL DEFAULT '0' AFTER `tn_attached`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `tnwidth` INT NOT NULL DEFAULT '0' AFTER `tnheight`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `mp3ribbon` TINYINT NOT NULL DEFAULT '0' AFTER `podcast`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `artist` VARCHAR(255) NULL AFTER `media_watermarked`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `album` VARCHAR(255) NULL AFTER `artist`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `genre` VARCHAR(255) NULL AFTER `album`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `artist` VARCHAR(255) NULL AFTER `media_watermarked`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `album` VARCHAR(255) NULL AFTER `artist`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `genre` VARCHAR(255) NULL AFTER `album`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `usealternate` TINYINT NOT NULL DEFAULT '0' AFTER `playback_type`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `skin` VARCHAR( 255 ) NOT NULL DEFAULT 'default' AFTER `album_order`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `rsschildren` TINYINT NOT NULL DEFAULT '0' AFTER `skin`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_sort']} ADD `referer` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sort_datetime`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_sort']} ADD `keywords` VARCHAR(255) NOT NULL DEFAULT '' AFTER `referer`";
$_SQL[] = "CREATE TABLE {$_TABLES['mg_rating']} ( " .
          "`id` int(11) unsigned NOT NULL auto_increment, " .
          "`ip_address` varchar(14) NOT NULL, " .
          "`uid` mediumint(8) NOT NULL, " .
          "`media_id` varchar(40) NOT NULL, " .
          "`ratingdate` int(11) NOT NULL, " .
          "`owner_id` mediumint(8) NOT NULL default '2', " .
          "PRIMARY KEY  (`id`), " .
          "KEY `owner_id` (`owner_id`) " .
");";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_rating']} ADD `owner_id` MEDIUMINT (8) NOT NULL DEFAULT '2' AFTER `ratingdate`";
$_SQL[] = "ALTER TABLE {$_TABLES['mg_rating']} ADD INDEX ( `owner_id` )";

$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('random_skin','mgShadow')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_border','1')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_align','auto')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_width','0')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_height','0')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_src','tn')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_autoplay','0')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_enable_link','1')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_delay','5')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_showtitle','0')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('use_flowplayer','0')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('search_columns','3')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('search_rows','4')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('search_playback_type','0')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('search_enable_views','1')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('search_enable_rating','1')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('gallery_only','0')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_tn_height','200')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_tn_width','200')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('gallery_tn_height','200')";
$_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('gallery_tn_width','200')";
------------------------------------------------------------------------------------------------ */
/* Execute SQL now to perform the upgrade */
for ($i = 1; $i <= count($_SQL); $i++) {
    COM_errorLOG("Media Gallery plugin 1.6.1svn Development update: Executing SQL => " . current($_SQL));
    DB_query(current($_SQL),1);
    next($_SQL);
}
DB_query("UPDATE {$_TABLES['plugins']} SET pi_homepage='http://www.gllabs.org' WHERE pi_name='mediagallery'",1);
DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='" . $_MG_CONF['version'] . "' WHERE pi_name='mediagallery' LIMIT 1");

$retval .= 'Development Code upgrades complete - see error.log for details<br>';

$display = COM_siteHeader();
$display .= $retval;
$display .= COM_siteFooter();
echo $display;
exit;
?>