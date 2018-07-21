<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | ajaxbookmark.php                                                         |
// |                                                                          |
// | AJAX Server functions for file attachments                               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
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

$id = COM_applyFilter($_GET['id'], true);
$sql = "SELECT filename,repository_id FROM {$_TABLES['ff_attachments']} WHERE id=".(int) $id.";";
$res = DB_query($sql);
$A = DB_fetchArray($res);

if ($A === FALSE) {
    echo "Error: Cannot Display Selected File";
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
        header("Content-type: application/octet-stream");
        header("Content-Disposition: inline; filename=\"{$realname}\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        fpassthru($fd);
        fclose ($fd);
    } else {
        echo "Error: Cannot Display Selected File, $realname";
        COM_errorLog("Error: Cannot Display Selected File, $realname");
    }
} else {
    echo "Error: Cannot Display Selected File, $realname";
    COM_errorLog("Error: Cannot Display Selected File, $realname");
}
?>