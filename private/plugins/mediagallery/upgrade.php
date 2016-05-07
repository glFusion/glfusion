<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | upgrade.php                                                              |
// |                                                                          |
// | Plugin upgrade routines                                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';

function mediagallery_upgrade()
{
    global $_TABLES, $_CONF, $_MG_CONF, $_DB_dbms, $TEMPLATE_OPTIONS;

    $result = DB_query("SELECT * FROM " . $_TABLES['mg_config'],1);
    while ($row = DB_fetchArray($result)) {
        $_MG_CONF[$row['config_name']] = $row['config_value'];
    }
    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='mediagallery'");

    switch( $currentVersion ) {
        case "0.80" :
            if ( MG_upgrade_090() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='0.90' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "0.90" :
        case "0.91" :
        case "0.92" :
        case "0.92a" :
        case "0.92b" :
            if ( MG_upgrade_095() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='0.95' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "0.95" :
        case "0.95b1" :
        case "0.95b2" :
        case "0.95beta1" :
        case "0.95beta2" :
        case "0.95rc1" :
        case "0.95rc2" :
        case "0.95rc3" :
        case "0.95rc4" :
            if ( MG_upgrade_096() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='0.97' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "0.96b1" :
        case "0.96b2" :
            if ( MG_upgrade_096b() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='0.97' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "0.96b3" :
        case "0.97" :
            if ( MG_upgrade_098() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='0.98' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "0.98"   :
        case "0.98a"  :
        case "0.98b"  :
        case "0.98c"  :
            if ( MG_upgrade_120() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.2.1' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.1.0" :
        case "1.02cvs" :
            DB_query("ALTER TABLE {$_TABLES['mg_albums']} ADD `full_display` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `albums_first`",1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.2.1' WHERE pi_name='mediagallery' LIMIT 1");
        case "1.2.0rc1" :
            DB_query("ALTER TABLE {$_TABLES['mg_playback_options']} ADD UNIQUE (`media_id` ,`option_name`);",1);
            DB_query("ALTER TABLE {$_TABLES['mg_albums']} ADD INDEX ( `last_update` )",1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.2.1' WHERE pi_name='mediagallery' LIMIT 1",1);
        case "1.2.0rc2" :
        case "1.2.0rc3" :
        case "1.2.1" :
        case "1.2.2" :
        case "1.2.3" :
        case "1.2.4" :
        case "1.2.5" :
        case "1.2.6" :
            if ( MG_upgrade_131() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.3.1' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.3.1" :
        case "1.3.2" :
            if ( MG_upgrade_133() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.3.3' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.3.3" :
            if ( MG_upgrade_134() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.3.4' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.3.4" :
            if ( MG_upgrade_135() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.3.5' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.3.5" :
            if ( MG_upgrade_136() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.3.6' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.3.6" :
            if ( MG_upgrade_137() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.3.7' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.3.7" :
            if ( MG_upgrade_138() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.3.8' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.3.8" :
            if ( MG_upgrade_139() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.3.9' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.3.9" :
            if ( MG_upgrade_1310() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.3.10' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.3.10" :
        case "1.3.11" :
            if ( MG_upgrade_1312() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.3.12' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.3.12" :
        case "1.3.C" :
        case "1.4.0RC1" :
        case "1.4.0RC2" :
        case "1.4.0RC3" :
        case "1.4.1"  :
        case "1.4.2"  :
        case "1.4.3"  :
        case "1.4.3a" :
            if ( MG_upgrade_144() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.4.4' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.4.4" :
            if ( MG_upgrade_145() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.4.5' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.4.5" :
            if ( MG_upgrade_146() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.4.6' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.4.6" :
        case "1.4.7" :
            if ( MG_upgrade_148() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.4.8' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.4.8" :
        case "1.4.8a" :
        case "1.4.8b" :
            if ( MG_upgrade_150() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.5.0' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.5.0" :
        case "1.5.1" :
            if ( MG_upgrade_160() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.6.0' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.6.0" :
        case "1.6.1" :
        case "1.6.2" :
        case "1.6.3" :
        case "1.6.4" :
            if ( MG_upgrade_165() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.6.5' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.6.5" :
        case "1.6.6" :
        case "1.6.7" :
            if ( MG_upgrade_168() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.6.8' WHERE pi_name='mediagallery' LIMIT 1");
            }
        case "1.6.8" :
        case "1.6.9" :
        case '1.6.10':
        case '2.0.0' :
            // no db / config changes.
        default :
            if ( $_DB_dbms != 'mssql' ) {
                // we missed media_keywords field somewhere along the way...
                $result = DB_query("SHOW COLUMNS FROM {$_TABLES['mg_mediaqueue']}");
                $numColumns = DB_numRows($result);
                $x=0;
                while ( $x < $numColumns ) {
                    $colname = DB_fetchArray($result);
                    $col[$colname[0]] = $colname[0];
                    $x++;
                }
                if ( $col['media_category'] != 'media_category' ) {
                    DB_query("ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_category` int(11) NOT NULL default '0' AFTER `media_upload_time`");
                }
                if ( $col['media_keywords'] != 'media_keywords' ) {
                    DB_query("ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_keywords` varchar(255) NOT NULL default '' AFTER `media_desc`");
                }
            }

            // now we do a little housekeeping...

            $album = array();
            $sql = "SELECT album_id FROM {$_TABLES['mg_albums']}";
            $result = DB_query($sql);
            $albumCount  = DB_numRows($result);
            for ($i=0; $i < $albumCount; $i++ ) {
                $album[] = DB_fetchArray($result);
            }
            for ($i=0; $i<$albumCount; $i++) {
                // set media count...
                $mediaCount  = DB_count($_TABLES['mg_media_albums'],'album_id',$album[$i]['album_id']);
                $sql = "UPDATE {$_TABLES['mg_albums']} set media_count=" . $mediaCount  . " WHERE album_id=" . $album[$i]['album_id'];
                DB_query($sql);
            }

            // rebuild the disk usage statistics

            $res1 = DB_query("SELECT album_id FROM {$_TABLES['mg_albums']}");
            while ( $row = DB_fetchArray($res1)) {
                $quota = 0;
                $sql = "SELECT m.media_filename, m.media_mime_ext FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                        " ON ma.media_id=m.media_id WHERE ma.album_id=" . $row['album_id'];

                $result = DB_query( $sql );
                while (list($filename, $mimeExt) = DB_fetchArray($result)) {
                    if ( $_MG_CONF['discard_original'] == 1 ) {
                        $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $filename[0] . '/' . $filename . '.' . $mimeExt);
                        $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $filename[0] . '/' . $filename . '.jpg');
                    } else {
                        $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $filename[0] . '/' . $filename . '.' . $mimeExt);
                    }
                }
                DB_query("UPDATE {$_TABLES['mg_albums']} SET album_disk_usage=" . $quota . " WHERE album_id=" . $row['album_id']);
            }

            if ( isset($TEMPLATE_OPTIONS['path_cache']) ) {
                MG_cleanup_plugin('mediagallery');
            }
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_homepage='http://www.glfusion.org' WHERE pi_name='mediagallery'",1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='".$_MG_CONF['pi_version']."' WHERE pi_name='mediagallery' LIMIT 1");
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_gl_version='".$_MG_CONF['gl_version']."' WHERE pi_name='mediagallery' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='mediagallery'") == $_MG_CONF['pi_version']) {
        return true;
        exit;
    } else {
        return false;
    }
}

function MG_upgrade_090( )
{
    global $_TABLES;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `member_uploads` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `album_cover`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `moderate` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `member_uploads`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `mod_group_id` MEDIUMINT( 9 ) DEFAULT '0' NOT NULL AFTER `group_id`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `mime_type` VARCHAR(255) DEFAULT '' NOT NULL AFTER `media_mime_ext`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `mime_type` VARCHAR(255) DEFAULT '' NOT NULL AFTER `media_mime_ext`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_comments` INT( 11 ) DEFAULT '0' NOT NULL AFTER `media_views`";
    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('dateformat',0)";
    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('usage_tracking',0)";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 0.90 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }
    COM_errorLog("Success - Completed Media Gallery plugin version 0.90 update",1);
    return 0;
}

function MG_upgrade_095() {
    global $_TABLES, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_comments` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `album_cover`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `exif_display` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `enable_comments`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_rating` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `exif_display`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `va_playback` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `enable_rating`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `playback_type` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `va_playback`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `tn_attached` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `playback_type`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `email_mod` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `moderate`";

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `media_votes` INT( 11 ) DEFAULT '0' NOT NULL AFTER `media_comments`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `media_rating` DECIMAL( 4,2 ) DEFAULT '0' NOT NULL AFTER `media_votes`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `media_tn_attached` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `media_rating`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `media_tn_image` VARCHAR( 255 ) DEFAULT '0' NOT NULL AFTER `media_tn_attached`";


    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_votes` INT( 11 ) DEFAULT '0' NOT NULL AFTER `media_comments`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_rating` DECIMAL( 4,2 ) DEFAULT '0' NOT NULL AFTER `media_votes`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_tn_attached` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `media_rating`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_tn_image` VARCHAR( 255 ) DEFAULT '0' NOT NULL AFTER `media_tn_attached`";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 0.95 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    // -- now set the comment flag for each album based on global config...

    if ( $_MG_CONF['comments'] == 1 ) {
        $sql = "UPDATE {$_TABLES['mg_albums']} SET enable_comments=1";
        DB_query($sql);
    }

    COM_errorLog("Success - Completed Media Gallery plugin version 0.95 update",1);
    return 0;
}

function MG_upgrade_096() {
    global $_TABLES, $_MG_CONF;

    @ini_set('max_execution_time',300);

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `album_cover_filename` VARCHAR( 255 ) DEFAULT '' NULL AFTER `album_cover`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `last_update` INT( 11 ) DEFAULT '0' NOT NULL AFTER `album_cover_filename`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `media_count` INT( 11 ) DEFAULT '0' NOT NULL AFTER `last_update`";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 0.96 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    //
    // Perform data conversion

    $album = array();

    $sql = "SELECT * FROM {$_TABLES['mg_albums']}";
    $result = DB_query($sql);
    $albumCount  = DB_numRows($result);
    for ($i=0; $i < $albumCount; $i++ ) {
        $album[] = DB_fetchArray($result);
    }

    for ($i=0; $i<$albumCount; $i++) {
        // set media count...
        $mediaCount  = DB_count($_TABLES['mg_media_albums'],'album_id',$album[$i]['album_id']);
        $sql = "SELECT *FROM " . $_TABLES['mg_media_albums'] . " as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE ma.album_id=" . $album[$i]['album_id'] . " AND m.media_type=0 ORDER BY m.media_upload_time DESC LIMIT 1";

        $result = DB_query($sql);

        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $last_update = $row['media_upload_time'];
            $media_filename = $row['media_filename'];
        } else {
            $last_update = 0;
            $media_filename = '';
        }

        $sql = "SELECT *FROM " . $_TABLES['mg_media_albums'] . " as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE ma.album_id=" . $album[$i]['album_id'] . " ORDER BY m.media_upload_time DESC LIMIT 1";

        $result = DB_query($sql);
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $last_update = $row['media_upload_time'];
            COM_errorLog("Set last_update to " . $row['media_upload_time']);
        } else {
            $last_update = 0;
            $media_filename = '';
        }

        if ( $album[$i]['album_cover'] != -1 ) {
            $media_filename = DB_getItem($_TABLES['mg_media'],'media_filename',"media_id='" . $album[$i]['album_cover'] . "'");
        }
        $sql = "UPDATE {$_TABLES['mg_albums']} set media_count=" . $mediaCount . ",last_update=" . $last_update . ",album_cover_filename='" . $media_filename . "' WHERE album_id=" . $album[$i]['album_id'];
        DB_query($sql);
    }

    COM_errorLog("Success - Completed Media Gallery plugin version 0.96 update",1);
    return 0;
}

function MG_upgrade_096b() {
    global $_TABLES, $_MG_CONF;

    @ini_set('max_execution_time',300);

    $album = array();

    $sql = "SELECT * FROM {$_TABLES['mg_albums']}";
    $result = DB_query($sql);
    $albumCount  = DB_numRows($result);
    for ($i=0; $i < $albumCount; $i++ ) {
        $album[] = DB_fetchArray($result);
    }

    for ($i=0; $i<$albumCount; $i++) {
        // set media count...
        $mediaCount  = DB_count($_TABLES['mg_media_albums'],'album_id',$album[$i]['album_id']);
        $sql = "SELECT *FROM " . $_TABLES['mg_media_albums'] . " as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE ma.album_id=" . $album[$i]['album_id'] . " AND m.media_type=0 ORDER BY m.media_upload_time DESC LIMIT 1";

        $result = DB_query($sql);

        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $last_update = $row['media_upload_time'];
            $media_filename = $row['media_filename'];
        } else {
            $last_update = 0;
            $media_filename = '';
        }

        $sql = "SELECT *FROM " . $_TABLES['mg_media_albums'] . " as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE ma.album_id=" . $album[$i]['album_id'] . " ORDER BY m.media_upload_time DESC LIMIT 1";

        $result = DB_query($sql);
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $last_update = $row['media_upload_time'];
            COM_errorLog("Set last_update to " . $row['media_upload_time']);
        } else {
            $last_update = 0;
            $media_filename = '';
        }

        if ( $album[$i]['album_cover'] != -1 ) {
            $media_filename = DB_getItem($_TABLES['mg_media'],'media_filename',"media_id='" . $album[$i]['album_cover'] . "'");
        }
        $sql = "UPDATE {$_TABLES['mg_albums']} set media_count=" . $mediaCount . ",last_update=" . $last_update . ",album_cover_filename='" . $media_filename . "' WHERE album_id=" . $album[$i]['album_id'];
        DB_query($sql);
    }

    COM_errorLog("Success - Completed Media Gallery plugin version 0.96b update",1);
    return 0;
}

function MG_upgrade_098() {
    global $_TABLES, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_random` TINYINT( 4 ) DEFAULT '1' NOT NULL AFTER `album_cover`";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 0.98 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    // -- now set the comment flag for each album based on global config...

    $sql = "UPDATE {$_TABLES['mg_albums']} SET enable_random=1";
    DB_query($sql);

    COM_errorLog("Success - Completed Media Gallery plugin version 0.98 update",1);
    return 0;
}

function MG_upgrade_120() {
    global $_TABLES, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_slideshow` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `tn_attached`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_shutterfly` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `enable_random`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_views` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `enable_shutterfly`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_sort` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `enable_views`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `albums_first` TINYINT( 4 ) DEFAULT '1' NOT NULL AFTER `enable_sort`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `full_display` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `albums_first`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `tn_size` TINYINT( 4 ) DEFAULT '1' NOT NULL AFTER `albums_first`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `display_rows` TINYINT( 4 ) DEFAULT '3' NOT NULL AFTER `tn_size`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `display_columns` TINYINT( 4 ) DEFAULT '3' NOT NULL AFTER `display_rows`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} DROP `va_playback`";

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD INDEX ( `last_update` )";

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `v100` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `media_upload_time`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `v100` TINYINT( 4 ) DEFAULT '0' NOT NULL AFTER `media_upload_time`";

    $_SQL[]="CREATE TABLE {$_TABLES['mg_userprefs']} (
      `uid` mediumint(8) NOT NULL default '0',
      `display_rows` tinyint(4) NOT NULL default '0',
      `display_columns` tinyint(4) NOT NULL default '0',
      `mp3_player` tinyint(4) NOT NULL default '-1',
      `playback_mode` tinyint(4) NOT NULL default '-1',
      `tn_size` tinyint(4) NOT NULL default '-1',
      `quota` bigint(20) unsigned NOT NULL default '0',
      `member_gallery` mediumint(8) NOT NULL default '0',
      PRIMARY KEY  (`uid`)
    );";

    $_SQL[]="CREATE TABLE {$_TABLES['mg_playback_options']} (
      `media_id` varchar(40) NOT NULL default '',
      `option_name` varchar(255) NOT NULL default '',
      `option_value` varchar(255) NOT NULL default '',
      UNIQUE KEY `media_id_2` (`media_id`,`option_name`),
      KEY `media_id` (`media_id`)
    );";

    $_SQL[]="CREATE TABLE {$_TABLES['mg_exif_tags']} (
      `name` varchar(255) NOT NULL default '',
      `selected` tinyint(1) NOT NULL default '0',
      PRIMARY KEY  (`name`)
    );";

    $_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ApertureValue', 1),
            ('ShutterSpeedValue', 1),
            ('ISO', 1),
            ('FocalLength', 0),
            ('Flash', 1),
            ('ACDComment', 0),
            ('AEWarning', 0),
            ('AFFocusPosition', 0),
            ('AFPointSelected', 0),
            ('AFPointUsed', 0),
            ('Adapter', 0),
            ('Artist', 0),
            ('BatteryLevel', 0),
            ('BitsPerSample', 0),
            ('BlurWarning', 0),
            ('BrightnessValue', 0),
            ('CCDSensitivity', 0),
            ('CameraID', 0),
            ('CameraSerialNumber', 0),
            ('Color', 0),
            ('ColorMode', 0),
            ('ColorSpace', 0),
            ('ComponentsConfiguration', 0),
            ('CompressedBitsPerPixel', 0),
            ('Compression', 0),
            ('ContinuousTakingBracket', 0),
            ('Contrast', 0),
            ('Converter', 0),
            ('Copyright', 0),
            ('CustomFunctions', 0),
            ('CustomerRender', 0),
            ('DateTime', 1),
            ('DigitalZoom', 0),
            ('DigitalZoomRatio', 0),
            ('DriveMode', 0),
            ('EasyShooting', 0),
            ('ExposureBiasValue', 0),
            ('ExposureIndex', 0),
            ('ExposureMode', 0),
            ('ExposureProgram', 0),
            ('FileSource', 0),
            ('FirmwareVersion', 0),
            ('FlashBias', 0),
            ('FlashDetails', 0),
            ('FlashEnergy', 0),
            ('FlashMode', 0),
            ('FlashPixVersion', 0),
            ('FlashSetting', 0),
            ('FlashStrength', 0),
            ('FocalPlaneResolutionUnit', 0),
            ('FocalPlaneXResolution', 0),
            ('FocalPlaneYResolution', 0),
            ('FocalUnits', 0),
            ('Focus', 0),
            ('FocusMode', 0),
            ('FocusWarning', 0),
            ('GainControl', 0),
            ('ImageAdjustment', 0),
            ('ImageDescription', 0),
            ('ImageHistory', 0),
            ('ImageLength', 0),
            ('ImageNumber', 0),
            ('ImageSharpening', 0),
            ('ImageSize', 0),
            ('ImageType', 0),
            ('ImageWidth', 0),
            ('InterColorProfile', 0),
            ('Interlace', 0),
            ('InteroperabilityIFD.InteroperabilityIndex', 0),
            ('InteroperabilityIFD.InteroperabilityVersion', 0),
            ('InteroperabilityIFD.RelatedImageFileFormat', 0),
            ('InteroperabilityIFD.RelatedImageLength', 0),
            ('InteroperabilityIFD.RelatedImageWidth', 0),
            ('JPEGTables', 0),
            ('JpegIFByteCount', 0),
            ('JpegIFOffset', 0),
            ('JpegQual', 0),
            ('LightSource', 0),
            ('LongFocalLength', 0),
            ('Macro', 0),
            ('Make', 1),
            ('ManualFocusDistance', 0),
            ('MaxApertureValue', 0),
            ('MeteringMode', 0),
            ('Model', 1),
            ('Noise', 0),
            ('NoiseReduction', 0),
            ('Orientation', 0),
            ('OwnerName', 0),
            ('PhotometricInterpret', 0),
            ('PhotoshopSettings', 0),
            ('PictInfo', 0),
            ('PictureMode', 0),
            ('PlanarConfiguration', 0),
            ('Predictor', 0),
            ('PrimaryChromaticities', 0),
            ('Quality', 0),
            ('ReferenceBlackWhite', 0),
            ('RelatedSoundFile', 0),
            ('ResolutionUnit', 0),
            ('RowsPerStrip', 0),
            ('SamplesPerPixel', 0),
            ('Saturation', 0),
            ('SceneCaptureMode', 0),
            ('SceneType', 0),
            ('SecurityClassification', 0),
            ('SelfTimer', 0),
            ('SelfTimerMode', 0),
            ('SensingMethod', 0),
            ('SequenceNumber', 0),
            ('Sharpness', 0),
            ('ShortFocalLength', 0),
            ('SlowSync', 0),
            ('Software', 0),
            ('SoftwareRelease', 0),
            ('SpatialFrequencyResponse', 0),
            ('SpecialMode', 0),
            ('SpectralSensitivity', 0),
            ('StripByteCounts', 0),
            ('StripOffsets', 0),
            ('SubIFDs', 0),
            ('SubfileType', 0),
            ('SubjectDistance', 0),
            ('SubjectLocation', 0),
            ('SubsecTime', 0),
            ('SubsecTimeDigitized', 0),
            ('SubsecTimeOriginal', 0),
            ('TIFF/EPStandardID', 0),
            ('TileByteCounts', 0),
            ('TileLength', 0),
            ('TileOffsets', 0),
            ('TileWidth', 0),
            ('TimeZoneOffset', 0),
            ('Tone', 0),
            ('TransferFunction', 0),
            ('UserComment', 0),
            ('Version', 0),
            ('WhiteBalance', 0),
            ('WhitePoint', 0),
            ('YCbCrCoefficients', 0),
            ('YCbCrPositioning', 0),
            ('YCbCrSubSampling', 0),
            ('xResolution', 0),
            ('yResolution', 0),
            ('ExifImageHeight', 0),
            ('ExifImageWidth', 0),
            ('IPTC/SupplementalCategories', 0),
            ('IPTC/Keywords', 0),
            ('IPTC/Caption', 0),
            ('IPTC/CaptionWriter', 0),
            ('IPTC/Headline', 0),
            ('IPTC/SpecialInstructions', 0),
            ('IPTC/Category', 0),
            ('IPTC/Byline', 0),
            ('IPTC/BylineTitle', 0),
            ('IPTC/Credit', 0),
            ('IPTC/Source', 0),
            ('IPTC/CopyrightNotice', 0),
            ('IPTC/ObjectName', 0),
            ('IPTC/City', 0),
            ('IPTC/ProvinceState', 0),
            ('IPTC/CountryName', 0),
            ('IPTC/OriginalTransmissionReference', 0),
            ('IPTC/DateCreated', 0),
            ('IPTC/CopyrightFlag', 0),
            ('IPTC/TimeCreated', 0);";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.2.0 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    // put new config items into database

    DB_query("REPLACE INTO {$_TABLES['mg_config']} VALUES ('jpg_quality', '75');");
    DB_query("REPLACE INTO {$_TABLES['mg_config']} VALUES ('whatsnew_time', '7');");
    DB_query("REPLACE INTO {$_TABLES['mg_config']} VALUES ('gallery_tn_size', '1');");
    DB_query("REPLACE INTO {$_TABLES['mg_config']} VALUES ('mp3_player', '0');");
    DB_query("REPLACE INTO {$_TABLES['mg_config']} VALUES ('seperator', '|');");

    // set defaults for the albums...

    DB_query("UPDATE {$_TABLES['mg_albums']} SET display_rows=" . $_MG_CONF['display_rows'] . ", display_columns=" . $_MG_CONF['display_columns'] . ", tn_size=1" );

    // set defaults for user pref overrides

    DB_query("REPLACE INTO {$_TABLES['mg_config']} (config_name, config_value) VALUES ('up_display_rows_enabled',   '1')");
    DB_query("REPLACE INTO {$_TABLES['mg_config']} (config_name, config_value) VALUES ('up_display_columns_enabled','1')");
    DB_query("REPLACE INTO {$_TABLES['mg_config']} (config_name, config_value) VALUES ('up_mp3_player_enabled',     '1')");
    DB_query("REPLACE INTO {$_TABLES['mg_config']} (config_name, config_value) VALUES ('up_av_playback_enabled',    '1')");
    DB_query("REPLACE INTO {$_TABLES['mg_config']} (config_name, config_value) VALUES ('up_thumbnail_size_enabled', '0')");

    DB_query("UPDATE {$_TABLES['plugins']} SET pi_homepage='http://www.gllabs.org' WHERE pi_name='mediagallery'",1);

    //
    // Perform data conversion

    $album = array();

    $sql = "SELECT * FROM {$_TABLES['mg_albums']}";
    $result = DB_query($sql);
    $albumCount  = DB_numRows($result);
    for ($i=0; $i < $albumCount; $i++ ) {
        $album[] = DB_fetchArray($result);
    }

    for ($i=0; $i<$albumCount; $i++) {
        // set media count...
        $mediaCount  = DB_count($_TABLES['mg_media_albums'],'album_id',$album[$i]['album_id']);
        $sql = "UPDATE {$_TABLES['mg_albums']} set media_count=" . $mediaCount . " WHERE album_id=" . $album[$i]['album_id'];
        DB_query($sql);
    }

    COM_errorLog("Success - Completed Media Gallery plugin version 1.2.1 update",1);
    return 0;
}

function MG_upgrade_131() {
    global $_TABLES, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `max_image_height` INT( 11 ) DEFAULT '0' NOT NULL AFTER `tn_size`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `max_image_width`  INT( 11 ) DEFAULT '0' NOT NULL AFTER `max_image_height`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `max_filesize` BIGINT( 20 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `max_image_width`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `display_image_size` TINYINT( 4 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `max_filesize`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `album_views` INT( 11 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `last_update`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_album_views` TINYINT( 4 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `album_views`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `maint` TINYINT( 4 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `v100`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `maint` TINYINT( 4 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `v100`";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.3.1 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    // -- set some defaults

    $sql = "UPDATE {$_TABLES['mg_albums']} SET display_image_size=2";
    DB_query($sql);

    COM_errorLog("Success - Completed Media Gallery plugin version 1.3.1 update",1);
    return 0;
}

function MG_upgrade_133() {
    global $_TABLES, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `include_ss` TINYINT( 4 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `media_tn_image`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `media_original_filename` VARCHAR( 255 ) DEFAULT '' AFTER `media_filename`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `include_ss` TINYINT( 4 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `media_tn_image`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} CHANGE `media_mime_ext` `media_mime_ext` VARCHAR( 255 ) NOT NULL";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `hidden` TINYINT( 4 ) DEFAULT '0' AFTER `album_order`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `allow_download` TINYINT( 4 ) DEFAULT '0' AFTER `albums_first`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_original_filename` VARCHAR( 255 ) DEFAULT '' AFTER `media_filename`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} CHANGE `media_mime_ext` `media_mime_ext` VARCHAR( 255 ) NOT NULL";
    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} (config_name, config_value) VALUES ('tn_jpg_quality','100')";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.3.3 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    // -- set some defaults

    $sql = "UPDATE {$_TABLES['mg_media']} SET include_ss=1";
    DB_query($sql);

    COM_errorLog("Success - Completed Media Gallery plugin version 1.3.3 update",1);
    return 0;
}

function MG_upgrade_134() {
    global $_TABLES, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `include_ss` TINYINT( 4 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `media_tn_image`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `include_ss` TINYINT( 4 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `media_tn_image`";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.3.4 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        next($_SQL);
    }
    COM_errorLog("Success - Completed Media Gallery plugin version 1.3.4 update",1);
    return 0;
}

function MG_upgrade_135() {
    global $_TABLES, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "CREATE TABLE {$_TABLES['mg_watermarks']} (
            `wm_id` int(11) NOT NULL default 0,
            `owner_id` int(11) NOT NULL default '0',
            `filename` varchar(255) NOT NULL default '',
            `description` varchar(255) NOT NULL default '',
            PRIMARY KEY  (`wm_id`)
            );";
    $_SQL[] = "INSERT INTO {$_TABLES['mg_watermarks']} VALUES ( 0, 0, 'blank.png', '---');";

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `media_watermarked` tinyint(4) NOT NULL default '0' AFTER `media_upload_time`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_watermarked` tinyint(4) NOT NULL default '0' AFTER `media_upload_time`";

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `wm_auto` tinyint(4) NOT NULL default '0' AFTER `display_columns`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `wm_id` int(11) NOT NULL default '0' AFTER `wm_auto`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `opacity` int(11) NOT NULL default '0' AFTER `wm_id`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `wm_location` tinyint(4) NOT NULL default '0' AFTER `opacity`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `album_sort_order` tinyint(4) NOT NULL default '0' AFTER `wm_location`";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.3.5 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    // -- set some defaults

    COM_errorLog("Success - Completed Media Gallery plugin version 1.3.5 update",1);
    return 0;
}

function MG_upgrade_136() {
    global $_TABLES, $_MG_CONF;

    $_SQL = array();

    // scan for images that need a new orientation...

    $_SQL[] = "CREATE TABLE {$_TABLES['mg_session_items']} (
      `id` bigint(20) unsigned NOT NULL auto_increment,
      `session_id` varchar(40) NOT NULL default '',
      `mid` varchar(40) NOT NULL default '',
      `aid` int(11) NOT NULL default '0',
      `data` varchar(255) NOT NULL default '',
      `data2` varchar(255) NOT NULL default '',
      `data3` varchar(255) NOT NULL default '',
      `status` tinyint(4) NOT NULL default '0',
      PRIMARY KEY  (`id`),
      KEY `session_id` (`session_id`)
    );";

    $_SQL[] = "CREATE TABLE {$_TABLES['mg_session_log']} (
      `session_id` varchar(40) NOT NULL default '',
      `session_log` varchar(255) NOT NULL default '',
      KEY `session_id` (`session_id`)
    );";

    $_SQL[] = "CREATE TABLE {$_TABLES['mg_sessions']} (
      `session_id` varchar(40) NOT NULL default '',
      `session_uid` mediumint(8) NOT NULL default '0',
      `session_description` varchar(255) NOT NULL default '',
      `session_status` tinyint(4) NOT NULL default '0',
      `session_cycles` tinyint(4) NOT NULL default '0',
      `session_action` varchar(255) NOT NULL default '',
      `session_origin` varchar(255) NOT NULL default '',
      `session_start_time` mediumint(11) NOT NULL default '0',
      `session_end_time` mediumint(11) NOT NULL default '0',
      PRIMARY KEY  (`session_id`)
    );";

    $_SQL[] = "CREATE TABLE {$_TABLES['mg_category']} (
      `cat_id` mediumint(9) NOT NULL default '0',
      `cat_name` varchar(255) NOT NULL default '',
      `cat_description` varchar(255) NOT NULL default '',
      `cat_order` mediumint(11) NOT NULL default '0',
      PRIMARY KEY  (`cat_id`)
    );";

    $_SQL[] = "CREATE TABLE {$_TABLES['mg_sort']} (
      `sort_id` varchar(40) NOT NULL default '',
      `sort_user` varchar(40) NOT NULL default '',
      `sort_query` text NOT NULL,
      `sort_results` int(11) NOT NULL default '0',
      `sort_datetime` int(11) NOT NULL default '0'
    );";

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `media_category` int(11) NOT NULL default '0' AFTER `media_upload_time`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `media_keywords` varchar(255) NOT NULL default '' AFTER `media_desc`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `media_exif` TINYINT(4) NOT NULL DEFAULT '1' AFTER `media_mime_ext`";

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_category` int(11) NOT NULL default '0' AFTER `media_upload_time`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_keywords` varchar(255) NOT NULL default '' AFTER `media_desc`";

    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('preserve_filename', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('discard_original', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('verbose', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('disable_whatsnew_comments', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('enable_media_id', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('full_in_popup', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('commentbar', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('title_length', '28');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_enable_comments', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_exif_display', '2');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_enable_rating', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_playback_type', '2');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_enable_slideshow', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_enable_random', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_enable_shutterfly', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_enable_views', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_enable_album_views', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_enable_sort', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_albums_first', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_full_display', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_tn_size', '2');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_display_rows', '3');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_display_columns', '3');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_member_uploads', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_moderate', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_email_mod', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_wm_auto', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_wm_id', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_wm_opacity', '10');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_wm_location', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_album_sort_order', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_max_filesize', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_max_image_height', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_max_image_width', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_display_image_size', '2');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_perm_owner', '3');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_perm_group', '3');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_perm_members', '2');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_perm_anon', '2');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_mod_id', '17');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_mod_group_id', '17');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('custom_image_height', '412');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('custom_image_width', '550');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('def_refresh_rate', '30');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('def_time_limit', '90');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('def_item_limit', '10');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('asf_autostart', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('asf_enablecontextmenu', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('asf_stretchtofit', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('asf_showstatusbar', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('asf_uimode', 'full');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('asf_playcount', '9999');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('asf_height', '480');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('asf_width', '640');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('asf_bgcolor', '#FFFFFF');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mov_autoref', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('move_autoplay', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mov_controller', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mov_kioskmode', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mov_scale', 'tofit');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mov_loop', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mov_height', '480');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mov_width', '640');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mov_bgcolor', '#FFFFFF');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mp3_autostart', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mp3_enablecontextmenu', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mp3_showstatusbar', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mp3_loop', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('mp3_uimode', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_play', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_menu', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_loop', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_quality', 'high');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_scale', 'showall');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_wmode', '');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_asa', '');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_flashvars', '');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_clsid', 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_height', '480');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_width', '640');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_bgcolor', '#FFFFFF');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('swf_allowscriptaccess', '');";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.3.6 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    // -- set some defaults

    DB_query("UPDATE {$_TABLES['mg_media']} SET media_exif=1",1);

    COM_errorLog("Success - Completed Media Gallery plugin version 1.3.5 update",1);
    return 0;
}

function MG_upgrade_137() {
    global $_TABLES, $_CONF, $_MG_CONF;


    $ftp_path = $_CONF['path'] . 'plugins/mediagallery/uploads/';

    $_SQL = array();

    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ftp_path', '$ftp_path');";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.3.7 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }
}

function MG_upgrade_138() {
    global $_TABLES, $_CONF, $_MG_CONF;

    $grp_id = DB_getItem($_TABLES['vars'], 'value', "name = '{$pi_name}_gid'");

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `album_disk_usage` bigint(20) NOT NULL default '0' AFTER `media_count`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_userprefs']} ADD `active` TINYINT NOT NULL DEFAULT '1' AFTER `uid`";

    // Member Album Defaults
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_albums', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_quota', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_auto_create', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_create_new', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_album_root', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_album_archive', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_enable_random', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_max_width', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_max_height', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_max_filesize', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_uploads', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_moderate', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_mod_group_id', '" . $grp_id . "');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_email_mod', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_perm_owner', '3');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_perm_group', '3');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_perm_members', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_perm_anon', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('last_usage_purge', '0');";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.3.8 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    // setup the initial album disk quota

    $res1 = DB_query("SELECT album_id FROM {$_TABLES['mg_albums']}");
    while ( $row = DB_fetchArray($res1)) {
        $quota = 0;
        $sql = "SELECT m.media_filename, m.media_mime_ext FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE ma.album_id=" . $row['album_id'];

        $result = DB_query( $sql );
        while (list($filename, $mimeExt) = DB_fetchArray($result)) {
            if ( $_MG_CONF['discard_original'] == 1 ) {
                $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $filename[0] . '/' . $filename . '.' . $mimeExt);
                $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $filename[0] . '/' . $filename . '.jpg');
            } else {
                $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $filename[0] . '/' . $filename . '.' . $mimeExt);
            }
        }
        DB_query("UPDATE {$_TABLES['mg_albums']} SET album_disk_usage=" . $quota . " WHERE album_id=" . $row['album_id']);
    }
    DB_query("UPDATE {$_TABLES['mg_userprefs']} SET active=1");
}

function MG_upgrade_139() {
    global $_TABLES, $_CONF, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "CREATE TABLE {$_TABLES['mg_postcard']} (
        `pc_id` varchar(40) NOT NULL default '',
        `mid` varchar(40) NOT NULL default '',
        `to_name` varchar(255) NOT NULL default '',
        `to_email` varchar(255) NOT NULL default '',
        `from_name` varchar(255) NOT NULL default '',
        `from_email` varchar(255) NOT NULL default '',
        `subject` varchar(255) NOT NULL default '',
        `message` text NOT NULL,
        `pc_time` int(11) NOT NULL default '0',
        `uid` mediumint(9) NOT NULL default '0',
         PRIMARY KEY  (`pc_id`)
    );";


    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_rss` tinyint(4) NOT NULL default '0' AFTER `enable_sort`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_postcard` tinyint(4) NOT NULL default '0' AFTER `enable_rss`";

    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_enable_rss', '0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_enable_postcard', '0');";

    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('rss_full_enabled', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('rss_feed_type', 'RSS2.0');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('rss_ignore_empty', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('rss_anonymous_only', '1');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('rss_feed_name', 'mgmedia');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('postcard_retention', '7');";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.3.9 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }
}

function MG_upgrade_1310() {
    global $_TABLES, $_CONF, $_MG_CONF, $LANG_MG00;

    $_SQL = array();


    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.3.10 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }
    // add the new block for self enrollment

    DB_query("INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, help, group_id, owner_id, perm_owner, perm_group, perm_members,perm_anon) VALUES ('', 0, 'mgenroll', 'phpblock', '" . $LANG_MG00['mg_enroll_header'] . "', 'all', 0, '', '', '', 1, 'phpblock_mg_maenroll','', 4, 2, 3, 3, 2, 0);",1);
}

function MG_upgrade_1312() {
    global $_TABLES, $_CONF, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_userprefs']} CHANGE `display_rows` `display_rows` TINYINT( 4 ) NOT NULL DEFAULT '0'";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_userprefs']} CHANGE `display_columns` `display_columns` TINYINT( 4 ) NOT NULL DEFAULT '0'";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_userprefs']} CHANGE `mp3_player` `mp3_player` TINYINT( 4 ) NOT NULL DEFAULT '-1'";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_userprefs']} CHANGE `playback_mode` `playback_mode` TINYINT( 4 ) NOT NULL DEFAULT '-1'";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_userprefs']} CHANGE `tn_size` `tn_size` TINYINT( 4 ) NOT NULL DEFAULT '-1'";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.3.12 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }
}

function MG_upgrade_144() {
    global $_TABLES, $_CONF, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_sessions']} CHANGE `session_start_time` `session_start_time` BIGINT( 11 ) NOT NULL DEFAULT '0'";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_sessions']} CHANGE `session_end_time` `session_end_time` BIGINT( 11 ) NOT NULL DEFAULT '0'";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_sessions']} ADD `session_var0` VARCHAR( 255 ) NULL";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_sessions']} ADD `session_var1` VARCHAR( 255 ) NULL";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_sessions']} ADD `session_var2` VARCHAR( 255 ) NULL";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_sessions']} ADD `session_var3` VARCHAR( 255 ) NULL";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_sessions']} ADD `session_var4` VARCHAR( 255 ) NULL";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `display_album_desc` TINYINT( 4 ) NOT NULL DEFAULT '0' AFTER `album_views`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `valid_formats` INT( 11 ) UNSIGNED NOT NULL DEFAULT '65535' AFTER `display_columns`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `filename_title` TINYINT( 4 ) NOT NULL DEFAULT '0' AFTER `valid_formats`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `shopping_cart` TINYINT( 4 )  NOT NULL DEFAULT '0' AFTER `filename_title`";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_display_album_desc', '0')";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_allow_download', '0')";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_valid_formats', '65535')";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('ad_filename_title', '0')";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.4.4 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL));
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }
    $sql = "ALTER TABLE {$_TABLES['mg_albums']} CHANGE `allow_downlaod` `allow_download` TINYINT( 4 ) NOT NULL DEFAULT '0'";
    DB_query($sql,1);
    $sql = "UPDATE {$_TABLES['mg_albums']} SET valid_formats=65535";
    DB_query($sql);
    DB_query("REPLACE INTO {$_TABLES['mg_config']} (config_name, config_value) VALUES ('profile_hook','1')");
}

function MG_upgrade_145() {
    global $_TABLES, $_CONF, $_MG_CONF;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_exif` TINYINT(4) NOT NULL DEFAULT '1' AFTER `media_mime_ext`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `album_view_type` TINYINT(4) NOT NULL DEFAULT '0' AFTER `enable_album_views`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `image_skin` VARCHAR(255) NOT NULL DEFAULT 'mgShadow' AFTER `album_view_type`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `album_skin` VARCHAR(255) NOT NULL DEFAULT 'mgAlbum' AFTER `image_skin`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `display_skin` VARCHAR(255) NOT NULL DEFAULT 'mgShadow' AFTER `album_skin`";
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_postcard']} CHANGE `pc_time` `pc_time` INT(11) NOT NULL DEFAULT '0'";

    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('album_display_rows', '9');";
    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('indexskin', 'mgAlbum');";
    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_image_skin', 'mgShadow');";
    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_album_skin', 'mgAlbum');";
    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_display_skin', 'mgShadow');";
    $_SQL[] = "REPLACE INTO {$_TABLES['mg_config']} VALUES ('member_valid_formats', '65535');";


    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.4.5 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }
}

function MG_upgrade_146() {
    global $_TABLES, $_CONF, $_MG_CONF;

    $_SQL = array();

    // somewhere along the way I neglected to put the media_keywords column in the queue table, this will take care of it...
    $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `media_keywords` varchar(255) NOT NULL default '' AFTER `media_desc`";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.4.6 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }
}

function MG_upgrade_148() {
    global $_TABLES, $_CONF, $_MG_CONF, $_DB_dbms;

    $_SQL = array();

    if ( $_DB_dbms == 'mssql' ) {
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_keywords` SMALLINT NOT NULL DEFAULT '0'";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `podcast` SMALLINT NOT NULL DEFAULT '0'";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `media_resolution_x` INT NOT NULL default '0'";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `media_resolution_y` INT NOT NULL default '0'";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `media_resolution_x` INT NOT NULL default '0'";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `media_resolution_y` INT NOT NULL default '0'";
        $_SQL['mg_session_items2'] = "CREATE TABLE [dbo].[{$_TABLES['mg_session_items2']}](
            [id] [int] IDENTITY(1,1) NOT NULL,
            [data1] [nvarchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
            [data2] [nvarchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
            [data3] [nvarchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
            [data4] [nvarchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
            [data5] [nvarchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
            [data6] [nvarchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
            [data7] [nvarchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
            [data8] [nvarchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
            [data9] [nvarchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
         CONSTRAINT [PK_mg_batch_session_items2] PRIMARY KEY CLUSTERED
        (
            [id] ASC
        )WITH (PAD_INDEX  = OFF, IGNORE_DUP_KEY = OFF) ON [PRIMARY]
        ) ON [PRIMARY]
        ";
    } else {
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `enable_keywords` TINYINT(4) NOT NULL DEFAULT '0' AFTER `enable_views`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `podcast` TINYINT(4) NOT NULL DEFAULT '0' AFTER `hidden`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `media_resolution_x` INT(11) NOT NULL default '0' AFTER `media_rating`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `media_resolution_y` int(11) NOT NULL default '0' AFTER `media_resolution_x`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `media_resolution_x` INT(11) NOT NULL default '0' AFTER `media_rating`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `media_resolution_y` int(11) NOT NULL default '0' AFTER `media_resolution_x`";
        $_SQL[] = "CREATE TABLE {$_TABLES['mg_session_items2']} (
          `id` bigint(20) NOT NULL,
          `data1` varchar(255) NOT NULL,
          `data2` varchar(255) NOT NULL,
          `data3` varchar(255) NOT NULL,
          `data4` varchar(255) NOT NULL,
          `data5` varchar(255) NOT NULL,
          `data6` varchar(255) NOT NULL,
          `data7` varchar(255) NOT NULL,
          `data8` varchar(255) NOT NULL,
          `data9` varchar(255) NOT NULL,
          KEY `id` (`id`)
        );";
    }

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.4.8 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }
    // need to make this a db save instead...
    DB_save($_TABLES['mg_config'], 'config_name,config_value',"'ad_enable_keywords','0'");

    // Add new group MediaGallery.config

    COM_errorLog("Attempting to create mediagallery config group", 1);
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) "
        . "VALUES ('mediagallery Config', 'Users in this group can configure the mediagallery plugin',0)",1);
    if (DB_error()) {
        return 1;
    }

    $group_id = DB_insertId();
    if ( $group_id == 0 ) {
        $lookup = 'mediagallery Config';
        $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='" . $lookup . "'");
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $group_id = $row['grp_id'];
        } else {
            COM_errorlog("ERROR: Media Gallery Installation - Unable to determine group_id");
            return 1;
        }
    }

    // Save the cgrp id for later uninstall
    COM_errorLog('About to save cgroup_id to vars table for use during uninstall',1);
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('mediagallery_cid', $group_id)",1);
    if (DB_error()) {
        COM_errorLog("Failed to save group_id to vars table",1);
        return 1;
    }

    // Added new feature mediagallery.config

    COM_errorLog("Adding mediagallery.config feature",1);
    DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) "
        . "VALUES ('mediagallery.config','Media Gallery Config Rights',0)",1);
    if (DB_error()) {
        COM_errorLog("Failure adding mediagallery.config feature",1);
        return 1;
    }

    $feat_id = DB_insertId();

    if ( $feat_id == 0 ) {
        $result = DB_query("SELECT * FROM {$_TABLES['features']} WHERE ft_name='mediagallery.config'");
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $feat_id = $row['ft_id'];
        } else {
            COM_errorlog("ERROR: Media Gallery Upgrade - Unable to determine feat_id for mediagallery.config");
            return 1;
        }
    }
    COM_errorLog("Success - feat_id = " . $feat_id,1);

    COM_errorLog("Adding mediagallery.config feature to config group",1);
    DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)",1);
    if (DB_error()) {
        COM_errorLog("Failure adding $feature feature to config group",1);
        return 1;
    }

    COM_errorLog("Attempting to give all users in Root group access to mediagallery config group",1);
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($group_id, NULL, 1)");
    if (DB_error()) {
        COM_errorLog("Failure giving all users in Root group access");
        return 1;
    }
    /* --- end of new feature / group --- */

    // fix missing mime types for older installations
    $sql = "SELECT * FROM {$_TABLES['mg_media']} WHERE mime_type = '' OR mime_type = 'application/octet-stream'";
    $result = DB_query($sql);
    while ( $M = DB_fetchArray($result) ) {
        switch ( $M['media_mime_ext'] ) {
            case 'jpg' :
                $mimeType = 'image/jpeg';
                break;
            case 'png' :
                $mimeType = 'image/png';
                break;
            case 'tif' :
                $mimeType = 'image/tiff';
                break;
            case 'gif' :
                $mimeType = 'image/gif';
                break;
            case 'bmp' :
                $mimeType = 'image/bmp';
                break;
            case 'tga' :
                $mimeType = 'image/tga';
                break;
            case 'psd' :
                $mimeType = 'image/psd';
                break;
            case 'mp3' :
                $mimeType = 'audio/mpeg';
                break;
            case 'ogg' :
                $mimeType = 'application/ogg';
                break;
            case 'asf' :
                $mimeType = 'video/x-ms-asf';
                break;
            case 'wma' :
                $mimeType = 'audio/x-ms-wma';
                break;
            case 'swf' :
                $mimeType = 'application/x-shockwave-flash';
                break;
            case 'mov' :
                $mimeType = 'video/quicktime';
                break;
            case 'mp4' :
            case 'mpg' :
            case 'mpeg' :
                $mimeType = 'video/mpeg';
                break;
            case 'zip' :
                $mimeType = 'application/zip';
                break;
            case 'pdf' :
                $mimeType = 'application/pdf';
                break;
            case 'flv' :
                $mimeType = 'video/x-flv';
                break;
            default :
                $mimeType = 'application/octet-stream';
                break;
        }
        DB_query("UPDATE {$_TABLES['mg_media']} set mime_type='" . $mimeType . "' WHERE media_id='" . $M['media_id'] . "'");
    }
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_homepage='http://www.gllabs.org' WHERE pi_name='mediagallery'",1);
}

function MG_upgrade_150() {
    global $_TABLES, $_CONF, $_MG_CONF;

    $_SQL = array();

    if ( $_DB_dbms == 'mssql' ) {
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `remote_media` SMALLINT NOT NULL DEFAULT '0'";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']} ADD `remote_url` varchar(2000) NULL";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `remote_media` SMALLINT NOT NULL DEFAULT '0'";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']} ADD `remote_url` varchar(2000) NULL";
    } else {
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `remote_media` TINYINT(4) NOT NULL default '0' AFTER `media_resolution_y`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `remote_url` TEXT NOT NULL default '' AFTER `remote_media`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `remote_media` TINYINT(4) NOT NULL default '0' AFTER `media_resolution_y`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `remote_url` TEXT NOT NULL default '' AFTER `remote_media`";
    }
    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('random_width', '120')";
    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('jpg_orig_quality','85')";
    $_SQL[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('truncate_breadcrumb','0')";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.5.0 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
        }
        next($_SQL);
    }
    return 0;
}

function MG_upgrade_160() {
    global $_TABLES, $_CONF, $_MG_CONF;

    $_SQL = array();

    if ( $_DB_dbms == 'mssql' ) {
		$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `tnheight` SMALLINT NOT NULL DEFAULT '0'";
		$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `tnwidth` SMALLINT NOT NULL DEFAULT '0'";
		$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `usealternate` SMALLINT NOT NULL DEFAULT '0'";
		$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `skin` VARCHAR( 255 ) NOT NULL DEFAULT 'default'";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `rsschildren` SMALLINT NOT NULL DEFAULT '0'";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `mp3ribbon` SMALLINT NOT NULL DEFAULT '0'";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `artist` VARCHAR(255) NULL";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `album` VARCHAR(255) NULL";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `genre` VARCHAR(255) NULL";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `artist` VARCHAR(255) NULL";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `album` VARCHAR(255) NULL";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `genre` VARCHAR(255) NULL";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_sort']} ADD `referer` VARCHAR(255) NOT NULL DEFAULT ''";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_sort']} ADD `keywords` VARCHAR(255) NOT NULL DEFAULT ''";
        $_SQL[] = "CREATE TABLE [dbo].[{$_TABLES['mg_rating']}] (
          [id] [int] PRIMARY KEY CLUSTERED,
          [ip_address] [varchar] (14) NOT NULL DEFAULT ('0'),
          [uid] [int] NOT NULL DEFAULT ('0'),
          [media_id] [varchar] (40) NOT NULL DEFAULT ('0'),
          [ratingdate] [int] NOT NULL DEFAULT ('0'),
          [owner_id] [int] NOT NULL default ('2')
        ) ON [PRIMARY]
        ";
        $_SQL[] = "CREATE NONCLUSTERED INDEX [IX_mg_rating_owner_id] ON [dbo].[{$_TABLES['mg_rating']}]
        (
        	[owner_id] ASC
        ) ON [PRIMARY]
        ";
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
    } else {
		$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `tnheight` INT NOT NULL DEFAULT '0' AFTER `tn_attached`";
		$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `tnwidth` INT NOT NULL DEFAULT '0' AFTER `tnheight`";
		$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `usealternate` TINYINT NOT NULL DEFAULT '0' AFTER `playback_type`";
		$_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `skin` VARCHAR( 255 ) NOT NULL DEFAULT 'default' AFTER `album_order`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `rsschildren` TINYINT NOT NULL DEFAULT '0' AFTER `shopping_cart`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_albums']} ADD `mp3ribbon` TINYINT NOT NULL DEFAULT '0' AFTER `podcast`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `artist` VARCHAR(255) NULL AFTER `media_watermarked`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `album` VARCHAR(255) NULL AFTER `artist`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_media']}  ADD `genre` VARCHAR(255) NULL AFTER `album`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `artist` VARCHAR(255) NULL AFTER `media_watermarked`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `album` VARCHAR(255) NULL AFTER `artist`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_mediaqueue']}  ADD `genre` VARCHAR(255) NULL AFTER `album`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_sort']} ADD `referer` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sort_datetime`";
        $_SQL[] = "ALTER TABLE {$_TABLES['mg_sort']} ADD `keywords` VARCHAR(255) NOT NULL DEFAULT '' AFTER `referer`";
        $_SQL[] = "CREATE TABLE {$_TABLES['mg_rating']} ( " .
                  "`id` int(11) unsigned NOT NULL default '0', " .
                  "`ip_address` varchar(14) NOT NULL, " .
                  "`uid` mediumint(8) NOT NULL, " .
                  "`media_id` varchar(40) NOT NULL, " .
                  "`ratingdate` int(11) NOT NULL, " .
                  "`owner_id` mediumint(8) NOT NULL default '2', " .
                  "PRIMARY KEY  (`id`), " .
                  "KEY `owner_id` (`owner_id`));";
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
    }

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("Media Gallery plugin 1.6.0 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        if (DB_error()) {
            COM_errorLog("SQL Error during Media Gallery plugin update",1);
            return 1;
        }
        next($_SQL);
    }
    return 0;
}

function MG_upgrade_165() {
    global $_TABLES, $_CONF, $_MG_CONF;

    MG_buildFullRSS();
    MGUPG_rebuildAllAlbumsRSS(0);

    return 0;
}

function MG_upgrade_168() {
    global $_TABLES, $_CONF, $_MG_CONF;

    // convert the existing Media Gallery ratings to new rating system...

    DB_query("UPDATE {$_TABLES['mg_media']} set media_rating = media_rating / 2",1);
    $result = DB_query("SELECT * FROM {$_TABLES['mg_media']} WHERE media_votes > 0");
    while ( $F = DB_fetchArray($result) ) {
        $item_id = $F['media_id'];
        $votes   = $F['media_votes'];
        $rating  = $F['media_rating'];
        DB_query("INSERT INTO {$_TABLES['rating']} (type,item_id,votes,rating) VALUES ('mediagallery','".$item_id."',$votes,$rating);",1);
    }

    $result = DB_query("SELECT * FROM {$_TABLES['mg_rating']}");
    while ( $H = DB_fetchArray($result) ) {
        $item_id = $H['media_id'];
        $user_id = $H['uid'];
        $ip      = $H['ip_address'];
        $time    = $H['ratingdate'];
        DB_query("INSERT INTO {$_TABLES['rating_votes']} (type,item_id,uid,ip_address,ratingdate) VALUES ('mediagallery','".$item_id."',$user_id,'".$ip."',$time);");
    }

    return 0;
}

function MGUPG_rebuildAllAlbumsRSS( $aid ){
    global $MG_albums;

    MG_buildAlbumRSS($aid);

    if ( !empty($MG_albums[$aid]->children)) {
        $children = $MG_albums[$aid]->getChildren();
        foreach($children as $child) {
            MGUPG_rebuildAllAlbumsRSS($MG_albums[$child]->id);
        }
    }
}
?>