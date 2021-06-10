<?php
/**
* glFusion CMS - Forum Plugin
*
* AJAX Server functions for file attachments
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*   Blaine Lang          blaine AT portalparts DOT com
*                        www.portalparts.com
*
*/

require_once '../lib-common.php';

use \glFusion\Log\Log;

$id = COM_applyFilter($_GET['id'], true);
$sql = "SELECT filename,repository_id FROM {$_TABLES['ff_attachments']} WHERE id=".(int) $id.";";
$res = DB_query($sql);
$A = DB_fetchArray($res);

if ($A === FALSE) {
    die();
    exit;
}

if ($A['repository_id'] > 0) {
    echo COM_refresh("{$_CONF['site_url']}/filemgmt/visit.php?lid={$A['repository_id']}");
    exit;
}

$filedata = explode(':', $A['filename']);
$filename = $filedata[0];
$realname = $filedata[1];
$filepath = $_FF_CONF['uploadpath'].'/'.$filename;

if ( file_exists($filepath) ) {
    if ($fd = fopen ($filepath, "rb")) {

        if(ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }
        ob_end_flush();
        header('Pragma: public'); 	// required
        header('Expires: 0');		// no cache
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Last-Modified: '.gmdate ('D, d M Y H:i:s', @filemtime ($filepath)).' GMT');
        header('Cache-Control: private',false);
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename="'.basename($realname).'"');
        header('Content-Transfer-Encoding: binary');
        if (!$_CONF['cookiesecure']) {
            header('Pragma: no-cache');
        }
        header('Content-Length: '. @filesize($filepath) );
        header('Connection: close');
        ob_clean();
        ob_end_flush();
        flush();
        fpassthru($fd);
        flush();
        die();
/* --- old method
        header("Content-type: application/octet-stream");
        header("Content-Disposition: inline; filename=\"{$realname}\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        fpassthru($fd);
        fclose ($fd);
*/
    } else {
        echo "Error: Cannot Display Selected File, $realname";
        Log::write('system',Log::ERROR, 'Forum Error: Cannot Display Selected File, '.$realname);
    }
} else {
    echo "Error: Cannot Display Selected File, $realname";
    Log::write('system',Log::ERROR,'Forum Error: Cannot Display Selected File, '.$realname);
}
?>