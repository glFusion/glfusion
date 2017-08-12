<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | upload.inc.php                                                           |
// |                                                                          |
// | functions for uploading attachments                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

function _ff_check4files($id,$tempfile=false) {
    global $_FILES,$_CONF,$_TABLES,$_USER,$_FF_CONF,$LANG_GF00,
           $_FF_CONF,$filemgmt_FileStore;

    $retval = '';

    for ( $z = 1; $z <= $_FF_CONF['maxattachments']; $z++ ) {
        $filelinks = '';
        $varName = 'file_forum'.$z;
        $chk_usefilemgmt = 'chk_usefilemgmt'.$z;
        $filemgmtcat  = 'filemgmtcat' . $z;
        $filemgmt_desc = 'filemgmt_desc' . $z;
        if ( isset($_FILES[$varName]) && is_array($_FILES[$varName]) ) {
            $uploadfile = $_FILES[$varName];
        } else {
            $uploadfile['name'] = '';
        }
        if ($uploadfile['name'] != '' ) {
            if (isset($_POST[$chk_usefilemgmt]) && $_POST[$chk_usefilemgmt] == 1) {
                $filename = $uploadfile['name'];
                $pos = strrpos($uploadfile['name'],'.') + 1;
                $ext = strtolower(substr($uploadfile['name'], $pos));
            } else {
                $uploadfilename =  glfRandomFilename();
                $pos = strrpos($uploadfile['name'],'.') + 1;
                $ext = strtolower(substr($uploadfile['name'], $pos));
                $filename = "{$uploadfilename}.{$ext}";
            }
            $set_chk_usefilemgmt = (isset($_POST[$chk_usefilemgmt]) ? (int) $_POST[$chk_usefilemgmt] : 0);
            if ( _ff_uploadfile($filename,$uploadfile,$_FF_CONF['allowablefiletypes'],$set_chk_usefilemgmt) ) {
                if (array_key_exists($uploadfile['type'],$_FF_CONF['inlineimageypes'])) {
                    if (isset($_POST[$chk_usefilemgmt]) && $_POST[$chk_usefilemgmt] == 1) {
                        $srcImage = "{$filemgmt_FileStore}{$filename}";
                        $destImage = "{$_FF_CONF['uploadpath']}/tn/{$filename}";
                    } else {
                        $srcImage = "{$_FF_CONF['uploadpath']}/{$filename}";
                        $destImage = "{$_FF_CONF['uploadpath']}/tn/{$uploadfilename}.{$ext}";
                    }
                    $ret = IMG_resizeImage($srcImage,$destImage,$_FF_CONF['inlineimage_height'],$_FF_CONF['inlineimage_width']);
                }
                // Store both the created filename and the real file source filename
                $realfilename = $filename;
                $filename = "$filename:{$uploadfile['name']}";
                if ($tempfile) {
                    $temp = 1;
                } else {
                    $temp = 0;
                }
                if (isset($_POST[$chk_usefilemgmt]) && $_POST[$chk_usefilemgmt] == 1) {
                    $cid = COM_applyFilter($_POST[$filemgmtcat],true);
                    $sql = "INSERT INTO {$_TABLES['filemgmt_filedetail']} (cid, title, url, size, submitter, status,date ) ";
                    $sql .= "VALUES ('".DB_escapeString($cid)."', '".DB_escapeString($realfilename)."', '".DB_escapeString($realfilename)."', '".DB_escapeString($uploadfile['size'])."', '{$_USER['uid']}', 1, UNIX_TIMESTAMP())";
                    DB_query($sql);
                    $newid = DB_insertID();
                    DB_query("INSERT INTO {$_TABLES['ff_attachments']} (topic_id,repository_id,filename,tempfile)
                        VALUES ('".DB_escapeString($id)."',$newid,'".DB_escapeString($filename)."',$temp)");
                    $description = glfPrepareForDB($_POST[$filemgmt_desc]);
                    DB_query("INSERT INTO {$_TABLES['filemgmt_filedesc']} (lid, description) VALUES ($newid, '$description')");
                } else {
                    DB_query("INSERT INTO {$_TABLES['ff_attachments']} (topic_id,filename,tempfile)
                        VALUES ('".DB_escapeString($id)."','".DB_escapeString($filename)."',$temp)");
                }

            } else {
                COM_errorlog("upload error:" . $GLOBALS['ff_errmsg']);
                $retval .= $GLOBALS['ff_errmsg'];
                $filelinks = -1;
            }
        }
    }

    if (!$tempfile AND isset($_POST['uniqueid']) AND COM_applyFilter($_POST['uniqueid'],true) > 0 AND DB_COUNT($_TABLES['ff_topic'],'id',(int) $id)) {
        $tid = COM_applyFilter($_POST['uniqueid']);
        DB_query("UPDATE {$_TABLES['ff_attachments']} SET topic_id=".(int)$id.", tempfile=0 WHERE topic_id=".(int) $tid);
    }

    return $retval;
}


function _ff_uploadfile($filename,&$upload_file,$allowablefiletypes,$use_filemgmt=0) {
    global $_FILES,$_CONF,$_TABLES,$_FF_CONF,$LANG_GF00,$filemgmt_FileStore;

    $upload = new upload();
    if ($use_filemgmt == 1) {
        $upload->setPath($filemgmt_FileStore);
    } else {
        $upload->setPath($_FF_CONF['uploadpath']);
    }
    $upload->setLogging(true);
    $upload->setAllowedMimeTypes($allowablefiletypes);
    // Set max dimensions as well in case user is uploading a full size image
    $upload->setMaxDimensions ($_FF_CONF['max_uploadimage_width'], $_FF_CONF['max_uploadimage_height']);
    if ( !isset($_FF_CONF['max_uploadimage_size']) || $_FF_CONF['max_uploadimage_size'] == 0 ) {
        $upload->setMaxFileSize(100000000);
    } else {
        $upload->setMaxFileSize($_FF_CONF['max_uploadimage_size']);
    }
    $upload->setAutomaticResize(true);

    if (strlen($upload_file['name']) > 0) {
        $upload->setFileNames($filename);
        $upload->setPerms( $_FF_CONF['fileperms'] );
        $upload->_currentFile = $upload_file;

        // Verify file meets size limitations
        if (!$upload->_fileSizeOk()) {
            $upload->_addError('File, ' . $upload->_currentFile['name'] . ', is bigger than the ' . $upload->_maxFileSize . ' byte limit');
        }
        // If all systems check, do the upload
        if ($upload->checkMimeType() AND $upload->_imageSizeOK() AND !$upload->areErrors()) {
            if ($upload->_copyFile()) {
                $upload->_uploadedFiles[] = $upload->_fileUploadDirectory . '/' . $upload->_getDestinationName();
            }
        }

        $upload->_currentFile = array();

        if ($upload->areErrors() AND !$upload->_continueOnError) {
            $errmsg = "Forum Upload Attachment Error:" . $upload->printErrors(false);
            COM_errorlog($errmsg);
            $GLOBALS['ff_errmsg'] = $LANG_GF00['uploaderr'] .':<br/>' . $upload->printErrors(false);
            return false;
        }
        return true;

    } else {
        return false;
    }
    return false;
}

function _ff_FileCleanup($uniqueid)
{
    global $_TABLES,$_FF_CONF,$filemgmt_FileStore;

    $sql = "SELECT * FROM {$_TABLES['ff_attachments']} WHERE tempfile=1 AND topic_id=".(int)$uniqueid;
    $result = DB_query($sql);
    while ($F = DB_fetchArray($result) ) {
        $filedata = explode(':', $F['filename']);
        $filename = $filedata[0];
        $realname = $filedata[1];
        $filepath = "{$_FF_CONF['uploadpath']}/$filename";
        $tnpath   = $_FF_CONF['uploadpath'].'/tn/'.$filename;
        @unlink($filepath);
        @unlink($tnpath);
        DB_delete($_TABLES['ff_attachments'],'id',$F['id']);
    }
}

function glfRandomFilename() {
    $length=10;
    srand((double)microtime()*1000000);
    $possible_charactors = "abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $string = "";
    while(strlen($string)<$length) {
        $string .= substr($possible_charactors, rand()%(strlen($possible_charactors)),1);
    }
    $string .= date('mdHms');   // Now add the numerical MonthDayHourSecond just to ensure no possible duplicate
    return($string);

}
function glfPrepareForDB($var) {
    // Need to call DB_escapeString again as COM_checkHTML stips it out
    $var = COM_checkHTML($var);
    $var = DB_escapeString($var);
    return $var;
}
?>