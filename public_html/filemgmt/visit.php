<?php
/**
* glFusion CMS - FileMgmt Plugin
*
* User file download
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by Consult4Hire Inc.
*  Author:
*  Blaine Lang          blaine AT portalparts DOT com
*
*  Based on:
*   myPHPNUKE Web Portal System - http://myphpnuke.com/
*   PHP-NUKE Web Portal System - http://phpnuke.org/
*   hatware - http://thatware.org/
*
*/

require_once '../lib-common.php';


use \glFusion\Log\Log;

USES_lib_image();

if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $_FM_CONF['selectpriv'] == 1 )  {
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
    COM_setArgNames( array('lid') );
    $lid = COM_applyFilter(COM_getArgument( 'lid' ),true);

    $File = Filemgmt\Download::getInstance($lid);
    if (!$File->canRead()) {
        Log::write('system',Log::ERROR,'FileMgmt: Unauthorized download attempt for file ' . $lid);
        COM_404();
    }

    $File->addHit();
    $url = $File->getUrl();

    $found_it = false;
    Log::write('system',Log::INFO, "Download File:{$url}, User ID is: {$uid}");
 
    $pos = utf8_strpos( $url, ':' );
    if( $pos === false ) {
        $DL = new Filemgmt\UploadDownload();
        $DL->setAllowAnyMimeType(true);
        if ($_FM_CONF['outside_webroot']) {
            $DL->setPath($_CONF['path'].'data/filemgmt_data/files/');
        } else {
            $DL->setPath($_FM_CONF['FileStore']);
        }
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
