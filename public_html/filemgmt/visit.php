<?php
// +-------------------------------------------------------------------------+
// | File Management Plugin for Geeklog - by portalparts www.portalparts.com | 
// +-------------------------------------------------------------------------+
// | Filemgmt plugin - version 1.5                                           |
// | Date: Mar 18, 2006                                                      |    
// +-------------------------------------------------------------------------+
// | Copyright (C) 2004 by Consult4Hire Inc.                                 |
// | Author:                                                                 |
// | Blaine Lang                 -    blaine@portalparts.com                 |
// |                                                                         |
// | Based on:                                                               |
// | myPHPNUKE Web Portal System - http://myphpnuke.com/                     |
// | PHP-NUKE Web Portal System - http://phpnuke.org/                        |
// | Thatware - http://thatware.org/                                         |
// +-------------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or           |
// | modify it under the terms of the GNU General Public License             |
// | as published by the Free Software Foundation; either version 2          |
// | of the License, or (at your option) any later version.                  |
// |                                                                         |
// | This program is distributed in the hope that it will be useful,         |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                    |
// | See the GNU General Public License for more details.                    |
// |                                                                         |
// | You should have received a copy of the GNU General Public License       |
// | along with this program; if not, write to the Free Software Foundation, |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.         |
// |                                                                         |
// +-------------------------------------------------------------------------+
//

require_once('../lib-common.php');
include_once($_CONF[path_html]."filemgmt/include/header.php");
include($_CONF[path_html] ."filemgmt/include/functions.php"); 

if (SEC_hasRights('filemgmt.user') OR $mydownloads_publicpriv == 1) {

    if (isset($_USER['uid'])) {
        $uid = $_USER['uid'];
    } else {
        $uid = 1;    // Set to annonymous GL User ID
    }

    $lid = COM_applyFilter($_GET['lid'],true);
    $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
    $groupsql = filemgmt_buildAccessSql();

    $sql = "SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_filedetail']} a ";
    $sql .= "LEFT JOIN {$_FM_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
    $sql .= "WHERE a.lid='$lid' $groupsql";
    list($testaccess_cnt) = DB_fetchArray( DB_query($sql));

    if ($testaccess_cnt == 0 OR DB_count($_FM_TABLES['filemgmt_filedetail'],"lid",$lid ) == 0) {
        COM_errorLOG("filemgmt visit.php ERROR: Invalid attempt to download a file. User:{$_USER['username']}, IP:{$_SERVER['REMOTE_ADDR']}, File ID:{$lid}");
        echo COM_refresh($_CONF['site_url'] . '/filemgmt/index.php');
        exit;
    } else {
        DB_query("INSERT INTO {$_FM_TABLES['filemgmt_history']} (uid, lid, remote_ip, date) VALUES ($uid, $lid, '{$_SERVER['REMOTE_ADDR']}', NOW())") or $eh->show("0013");
        DB_query("UPDATE {$_FM_TABLES['filemgmt_filedetail']} SET hits=hits+1 WHERE lid=$lid AND status>0");
        $result = DB_query("SELECT url FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE lid=$lid AND status>0");
        list($url) = DB_fetchArray($result);
        $fullurl = $filemgmt_FileStoreURL .$url;
        $fullurl = stripslashes($fullurl);
        COM_accessLOG("Visit.php => Download File:{$url}, User ID is:{$uid}, Remote address is: {$_SERVER['REMOTE_ADDR']}");
        Header("Location: $fullurl");
        echo "<html><head><meta http-equiv=\"Refresh\" content=\"0; URL=".$fullurl."\"></meta></head><body></body></html>";
        exit();
    }

} else {
    COM_errorLOG("Visit.php => FileMgmt Plugin Access denied. Attempted download of file ID:{$lid}, Remote address is: {$_SERVER['REMOTE_ADDR']}");
    redirect_header($_CONF['site_url']."/index.php",1,_GL_ERRORNOACCESS);
    exit();
}

?>