<?php
/**
* glFusion CMS
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2016 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';

if ( SEC_inGroup('Root') ) {
    $createDir = "php/createdir.php";
    $deleteDir = "php/deletedir.php";
    $moveDir   = "php/movedir.php";
    $copyDir   = "php/copydir.php";
    $renameDir = "php/renamedir.php";

    $deleteFile = "php/deletefile.php";
    $moveFile   = "php/movefile.php";
    $copyFile   = "php/copyfile.php";
    $renameFile = "php/renamefile.php";

    $uploadFile = "php/upload.php";
    $downloadFile = "php/download.php";
    $downloadDir  = "php/downloaddir.php";
} else {
    $createDir = "";
    $deleteDir = "";
    $moveDir   = "";
    $copyDir   = "";
    $renameDir = "";
    $deleteFile = "";
    $moveFile   = "";
    $copyFile   = "";
    $renameFile = "";
    if ($_CK_CONF['filemanager_browse_only'] ) {
        $uploadFile     = "";
        $downloadFile   = "";
        $downloadDir    = "";
    } else {
        $uploadFile     = "php/upload.php";
        $downloadFile   = "php/download.php";
        $downloadDir    = "php/downloaddir.php";
    }
}

if ( $_CK_CONF['filemanager_default_view_mode'] == 'grid' ) {
    $defaultView = "thumb";
} else {
    $defaultView = "list";
}

$defaultView = $_CK_CONF['filemanager_default_view_mode'];

$cfgarray = array(
    "FILES_ROOT" =>          "",
    "RETURN_URL_PREFIX" =>   "",
    "SESSION_PATH_KEY" =>    "fileman_files_root",
    "THUMBS_VIEW_WIDTH" =>   "140",
    "THUMBS_VIEW_HEIGHT" =>  "120",
    "PREVIEW_THUMB_WIDTH" => "100",
    "PREVIEW_THUMB_HEIGHT" =>"100",
    "MAX_IMAGE_WIDTH" =>     "1000",
    "MAX_IMAGE_HEIGHT" =>    "1000",
    "INTEGRATION" =>         "ckeditor",
    "DIRLIST" =>             "php/dirtree.php",
    "CREATEDIR" =>           $createDir,
    "DELETEDIR" =>           $deleteDir,
    "MOVEDIR" =>             $moveDir,
    "COPYDIR" =>             $copyDir,
    "RENAMEDIR" =>           $renameDir,
    "FILESLIST" =>           "php/fileslist.php",
    "UPLOAD" =>              $uploadFile,
    "DOWNLOAD" =>            $downloadFile,
    "DOWNLOADDIR" =>         $downloadDir,
    "DELETEFILE" =>          $deleteFile,
    "MOVEFILE" =>            $moveFile,
    "COPYFILE" =>            $copyFile,
    "RENAMEFILE" =>          $renameFile,
    "GENERATETHUMB" =>       "php/thumb.php",
    "DEFAULTVIEW" =>         $defaultView,
    "FORBIDDEN_UPLOADS" =>   "tar gz arj bz bz2 bzip 7z zip js jsp jsb html mhtml mht xhtml xht php phtml php3 php4 php5 phps shtml jhtml pl sh py cgi exe application gadget hta cpl msc jar vb jse ws wsf wsc wsh ps1 ps2 psc1 psc2 msh msh1 msh2 inf reg scf msp scr dll msi vbs bat com pif cmd vxd cpl htpasswd htaccess config",
    "ALLOWED_UPLOADS" =>     "",
    "FILEPERMISSIONS" =>     "0664",
    "DIRPERMISSIONS" =>      "0775",
    "LANG" =>                "auto",
    "DATEFORMAT" =>          "dd/MM/yyyy HH =>mm",
    "OPEN_LAST_DIR" =>       "yes"
);
header('Content-Type: application/json');
echo json_encode($cfgarray);
?>