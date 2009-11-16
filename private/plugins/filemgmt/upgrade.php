<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | upgrade.php                                                              |
// |                                                                          |
// | Plugin upgrade routines                                                  |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the FileMgmt Plugin for Geeklog                                 |
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

/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function filemgmt_upgrade()
{
    global $_TABLES,$_CONF,$_FM_TABLES,$CONF_FM, $_DB_table_prefix;;

    include $_CONF['path'].'/plugins/filemgmt/config.php';
    include $_CONF['path'].'/plugins/filemgmt/filemgmt.php';

    require_once $_CONF['path_system'] . 'classes/config.class.php';

    $cur_version = DB_getItem($_TABLES['plugins'],'pi_version', "pi_name='filemgmt'");

    switch ( $cur_version ) {
        case '1.3' :
            DB_query("ALTER TABLE {$_FM_TABLES['filemgmt_cat']} ADD `grp_access` mediumint(8) DEFAULT '2' NOT NULL AFTER imgurl");
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '1.5' WHERE pi_name = 'filemgmt'");
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_gl_version = '1.0.0' WHERE pi_name = 'filemgmt'");

            // Update all the comment records
            $result = DB_query("SELECT cid,sid FROM {$_TABLES['comments']} WHERE type='filemgmt'");
            while (list($cid,$sid) = DB_fetchArray($result)) {
                if (strpos($sid,'fileid_') === FALSE) {
                    $sid = "fileid_{$sid}";
                    DB_query("UPDATE {$_TABLES['comments']} SET sid='$sid' WHERE cid='$cid'");
                }
            }
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '1.5' WHERE pi_name = 'filemgmt'");
        case '1.5' :
        case '1.5.1' :
        case '1.5.2' :
        case '1.5.3' :
            DB_query("ALTER TABLE {$_FM_TABLES['filemgmt_cat']} ADD `grp_writeaccess` MEDIUMINT( 8 ) NOT NULL DEFAULT '1'");
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '1.6' WHERE pi_name = 'filemgmt'");
        case '1.6' :
        case '1.6.0' :
            // need to migrate the configuration to our new online configuration.
            require_once $_CONF['path_system'] . 'classes/config.class.php';
            require_once $_CONF['path'] . 'plugins/filemgmt/install_defaults.php';
            plugin_initconfig_filemgmt();
        case '1.7.0.fusion' :
            $c = config::get_instance();
            $c->add('outside_webroot', 0, 'select', 0, 2, 0, 100, true, 'filemgmt');
        case '1.7.0' :
        case '1.7.1' :
        case '1.7.2' :
        case '1.7.3' :
        case '1.7.4' :
            DB_query("UPDATE {$_FM_TABLES['filemgmt_filedetail']} set rating = rating / 2",1);
            $result = DB_query("SELECT * FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE votes > 0");
            while ( $F = DB_fetchArray($result) ) {
                $item_id = $F['lid'];
                $votes   = $F['votes'];
                $rating  = $F['rating'];
                DB_query("INSERT INTO {$_TABLES['rating']} (type,item_id,votes,rating) VALUES ('filemgmt','".$item_id."',$votes,$rating);",1);
            }

            $result = DB_query("SELECT * FROM {$_FM_TABLES['filemgmt_votedata']}");
            while ( $H = DB_fetchArray($result) ) {
                $item_id = $H['lid'];
                $user_id = $H['ratinguser'];
                $ip      = $H['ratinghostname'];
                $time    = $H['ratingtimestamp'];
                DB_query("INSERT INTO {$_TABLES['rating_votes']} (type,item_id,uid,ip_address,ratingdate) VALUES ('filemgmt','".$item_id."',$user_id,'".$ip."',$time);",1);
            }
        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '".$CONF_FM['pi_version']."',pi_gl_version = '".$CONF_FM['gl_version']."' WHERE pi_name = 'filemgmt'");
            return true;
    }
}
?>