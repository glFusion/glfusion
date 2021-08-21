<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | visit.php                                                                |
// |                                                                          |
// | downloads a file directly                                                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
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
//include_once $_CONF['path'].'plugins/filemgmt/include/header.php';
//include_once $_CONF['path'].'plugins/filemgmt/include/functions.php';

use \glFusion\Log\Log;

USES_lib_image();

if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $mydownloads_publicpriv != 1 )  {
    Log::write('system',Log::ERROR,"Visit.php => FileMgmt Plugin Access denied. Attempted download of file ID:{$lid}");
    COM_setMsg(_GL_ERRORNOACCESS, 'error');
    COM_refresh($_CONF['site_url']."/index.php");
    exit();
} else {
    if (!COM_isAnonUser()) {
        $uid = $_USER['uid'];
    } else {
        $uid = 1;
        $_USER['username'] = 'anon';
    }
    $tempFile = 0;
    $lid = 0;
    $status = '';
    if ( isset($_GET['lid']) ) {
        $lid = COM_applyFilter($_GET['lid'],true);
        //$status = 'status>0';
    }

    $File = Filemgmt\Download::getInstance($lid);
    if (!$File->canRead()) {
        COM_errorLog("Unauthorized download attempt for file " . $File->getLid());
        COM_404();
    }

    $File->addHit();
    $url = $File->getUrl();

    $found_it = false;
    Log::write('system',Log::INFO, "Download File:{$url}, User ID is:{$uid}");
 
    $pos = utf8_strpos( $url, ':' );
    if( $pos === false ) {
        $DL = new Filemgmt\UploadDownload();
        $DL->setAllowAnyMimeType(true);
        $DL->setPath($_FM_CONF['FileStore']);
        $DL->downloadFile($url);
    } else {
        $protocol = utf8_substr( $url, 0, $pos + 1 );
        $found_it = false;
        $allowed_protocols = array('http','https','ftp');
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
