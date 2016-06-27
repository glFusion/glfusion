<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | visit.php                                                                |
// |                                                                          |
// | downloads a file directly                                                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
// |                                                                          |
// | Based on:                                                                |
// | myPHPNUKE Web Portal System - http://myphpnuke.com/                      |
// | PHP-NUKE Web Portal System - http://phpnuke.org/                         |
// | Thatware - http://thatware.org/                                          |
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

require_once '../lib-common.php';
include_once $_CONF['path'].'plugins/filemgmt/include/header.php';
include_once $_CONF['path'].'plugins/filemgmt/include/functions.php';

USES_lib_image();

if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $mydownloads_publicpriv != 1 )  {
    COM_errorLog("Visit.php => FileMgmt Plugin Access denied. Attempted download of file ID:{$lid}");
    redirect_header($_CONF['site_url']."/index.php",1,_GL_ERRORNOACCESS);
    exit();
} else {
    if (!COM_isAnonUser()) {
        $uid = $_USER['uid'];
    } else {
        $uid = 1;
    }
    $tempFile = 0;
    if ( isset($_GET['lid']) ) {
        $lid = COM_applyFilter($_GET['lid'],true);
        $status = 'status>0';
    }
    if ( isset($_GET['tid']) ) {
        $lid = COM_applyFilter($_GET['tid'],true);
        $tempFile = 1;
        $status = ' status = 0';
    }
    if ($tempFile == 1 && !SEC_hasRights('filemgmt.edit')) {
        exit;
    }
    $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
    $groupsql = filemgmt_buildAccessSql();

    $sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_filedetail']} a ";
    $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
    $sql .= "WHERE a.lid='".DB_escapeString($lid)."' $groupsql";
    list($testaccess_cnt) = DB_fetchArray( DB_query($sql));

    if ($testaccess_cnt == 0 OR DB_count($_TABLES['filemgmt_filedetail'],"lid",DB_escapeString($lid) ) == 0) {
        COM_errorLOG("filemgmt visit.php ERROR: Invalid attempt to download a file. User:{$_USER['username']}, File ID:{$lid}");
        echo COM_refresh($_CONF['site_url'] . '/filemgmt/index.php');
        exit;
    } else {
        $result = DB_query("SELECT url,platform FROM {$_TABLES['filemgmt_filedetail']} WHERE lid='".DB_escapeString($lid)."' AND ".$status);
        list($url,$tmpnames) = DB_fetchArray($result);
        if ( $tempFile == 1 ) {
            $tmpfilenames = explode(";",$tmpnames);
            $tempfilepath = $filemgmt_FileStore . 'tmp/' .$tmpfilenames[0];
        } else {
            DB_query("INSERT INTO {$_TABLES['filemgmt_history']} (uid, lid, remote_ip, date) VALUES ($uid, '".DB_escapeString($lid)."', '".DB_escapeString($_SERVER['REMOTE_ADDR'])."', NOW())") or $eh->show("0013");
            DB_query("UPDATE {$_TABLES['filemgmt_filedetail']} SET hits=hits+1 WHERE lid='".DB_escapeString($lid)."' AND status>0");
        }
        $allowed_protocols = array('http','https','ftp');
        $found_it = false;
        COM_accessLog("Visit.php => Download File:{$url}, User ID is:{$uid}");
        $pos = utf8_strpos( $url, ':' );
        if( $pos === false ) {
            if ( $_FM_CONF['outside_webroot'] == 1 ) {
                if ( $tempFile == 1 ) {
                    $fullurl = $tempfilepath;
                } else {
                    $fullurl = $filemgmt_FileStore . urldecode($url);
                }
                if ( file_exists($fullurl) ) {
                    if ($fd = fopen ($fullurl, "rb")) {
                        ob_end_flush();
                        header('Content-Type: application/octet-stream; name="'.urldecode($url).'"');
                        header('Content-Disposition: attachment; filename="'.urldecode($url).'"');
                        header('Accept-Ranges: bytes');
                        if (!$_CONF['cookiesecure']) {
                            header('Pragma: no-cache');
                        }
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Content-transfer-encoding: binary');
                        session_write_close();
                        ob_end_flush();
                        fpassthru($fd);
                        flush();
                    } else {
                        COM_errorLog("FileMgmt: Error - Unable to download selected file: ". urldecode($url));
                    }
                }
            } else {
                $fullurl = $filemgmt_FileStore .$url;
                $fullurl = $fullurl;
                ob_end_flush();
                header('Content-Disposition: attachment; filename="' . $url . '"');
                header('Content-Type: application/octet-stream');
                header('Content-Description: File Transfer');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                if (!$_CONF['cookiesecure']) {
                    header('Pragma: no-cache');
                }
                header('Content-Length: ' . filesize($fullurl));
                ob_clean();
                ob_end_flush();
                flush();
                @readfile($fullurl);
                flush();
            }
        } else {
            $protocol = utf8_substr( $url, 0, $pos + 1 );
            $found_it = false;
            foreach( $allowed_protocols as $allowed ) {
                if( substr( $allowed, -1 ) != ':' ) {
                    $allowed .= ':';
                }
                if( $protocol == $allowed ) {
                    $found_it = true;
                    break;
                }
            }
            if( !$found_it ) {
                exit;
            } else {
                $fullurl = $url;
            }
            $fullurl = $fullurl;
            Header("Location: $fullurl");
            echo "<html><head><meta http-equiv=\"Refresh\" content=\"0; URL=".$fullurl."\"></meta></head><body></body></html>";
            exit();
        }
    }
}
?>