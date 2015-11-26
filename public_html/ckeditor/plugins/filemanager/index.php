<?php
/**
* glFusion CMS
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2015 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once dirname(__FILE__) . '/../../../lib-common.php';

$urlfor = 'advancededitor';
if (COM_isAnonUser() ) {
    $urlfor = 'advancededitor'.md5($REMOTE_ADDR);
}
$cookiename = $_CONF['cookie_name'].'adveditor';
if ( isset($_COOKIE[$cookiename]) ) {
    $token = $_COOKIE[$cookiename];
} else {
    $token = '';
}
if (!SEC_checkTokenGeneral($token,$urlfor)  ) {
    die('Not authorized');
}

$urlparts = parse_url($_CONF['site_url']);
if ( isset($urlparts['path']) ) {
    $relRoot = $urlparts['path'];
    $relRoot = trim($relRoot);
    if ( $relRoot[strlen($relRoot)-1] != '/' ) {
        $relRoot = $relRoot.'/';
    }
} else {
    $relRoot = '';
}

$inRoot = SEC_inGroup('Root');

$T = new Template($_CONF['path'] . 'plugins/ckeditor/templates');
$T->set_file('filemanager', 'filemanager.thtml');

$relPaths = array(
    'Image'     => 'images/library/Image/',
    'Flash'     => 'images/library/Flash/',
    'Media'     => 'images/library/Media/',
    'File'      => 'images/library/File/',
    'Root'      => 'images/',
    'Userfiles' => 'images/library/userfiles/',
);

$type = isset($_GET['Type']) ? COM_applyFilter($_GET['Type']) : '';

if (!array_key_exists($type, $relPaths)) {
    $type = 'Image';
}

if ( COM_isAnonUser() ) {
    $uid = 1;
} else {
    $uid = $_USER['uid'];
}
if ( $inRoot ) {
    $type = 'Root';
} else {
    if ( $_CK_CONF['filemanager_per_user_dir'] ) {
        $type = 'Userfiles';
        $filePath = $relPaths[$type] . $uid . '/';
        $relPaths['Userfiles'] = $filePath;

        if ( !is_dir($_CONF['path_html'].$filePath) ) {
            $rc = @mkdir($_CONF['path_html'].$filePath, 0755, true);
            if ( $rc === false ) {
                $type = 'Image';
                $_CK_CONF['filemanager_per_user_dir'] = false;
            }
        }
    }
}

$fileRoot = $relRoot . $relPaths[$type];
$fileRoot = str_replace('\\', '/', $fileRoot);

if ( $inRoot ) {
    $capabilities = array("select", "download", "rename", "move", "delete", "replace");
} else if ($_CK_CONF['filemanager_per_user_dir'] ) {
    $capabilities = array("select", "rename", "move", "delete", "replace");
} else {
    $capabilities = array("select");
}

$fmconfiguration = array(
	"_comment" =>  "IMPORTANT  =>  go to the wiki page to know about options configuration https => //github.com/simogeo/Filemanager/wiki/Filemanager-configuration-file",
    "options" =>  array(
        "culture" =>  $_CONF['iso_lang'],
        "lang" =>  "php",
        "theme" => "flat-dark",
        "defaultViewMode" =>  $_CK_CONF['filemanager_default_view_mode'],
        "autoload" =>  true,
        "showFullPath" =>  false,
        "showTitleAttr" =>  false,
        "browseOnly" =>  (COM_isAnonUser() ? true : $_CK_CONF['filemanager_browse_only']),
        "showConfirmation" =>  $_CK_CONF['filemanager_show_confirmation'],
        "showThumbs" =>  $_CK_CONF['filemanager_show_thumbs'],
        "generateThumbnails" =>  $_CK_CONF['filemanager_generate_thumbnails'],
        "searchBox" =>  $_CK_CONF['filemanager_search_box'],
        "listFiles" =>  true,
        "fileSorting" =>  $_CK_CONF['filemanager_file_sorting'],
        "chars_only_latin" =>  $_CK_CONF['filemanager_chars_only_latin'],
        "splitterWidth" => 200,
        "splitterMinWidth" => 200,
        "dateFormat" =>  $_CK_CONF['filemanager_date_format'],
        "serverRoot" =>  true,
        "fileRoot" =>  $fileRoot,
        "baseUrl" =>  false,
        "logger" =>  false,
        "capabilities" =>  $capabilities,
        "plugins" =>  array()
    ),
    "security" =>  array(
        "allowFolderDownload" => false,
        "allowChangeExtensions" => false,
        "allowNoExtension" => false,
        "uploadPolicy" =>  "DISALLOW_ALL",
        "uploadRestrictions" =>  explode(',',$_CK_CONF['filemanager_upload_restrictions'])
    ),
    "upload" =>  array(
        "overwrite" =>  $_CK_CONF['filemanager_upload_overwrite'],
        "imagesOnly" =>  $_CK_CONF['filemanager_upload_images_only'],
        "fileSizeLimit" =>  $_CK_CONF['filemanager_upload_file_size_limit'],
        "multiple" => true,
        "number" => 5,
    ),
    "exclude" =>  array(
        "unallowed_files" =>  explode(',',$_CK_CONF['filemanager_unallowed_files']),
        "unallowed_dirs" =>  explode(',',$_CK_CONF['filemanager_unallowed_dirs']),
        "unallowed_files_REGEXP" =>  $_CK_CONF['filemanager_unallowed_files_regexp'],
        "unallowed_dirs_REGEXP" =>  $_CK_CONF['filemanager_unallowed_dirs_regexp']
    ),
    "images" =>  array(
        "imagesExt" =>  explode(',',$_CK_CONF['filemanager_images_ext']),
        "resize" =>  array(
        	"enabled" => true,
        	"maxWidth" =>  1280,
            "maxHeight" =>  1024
        )
    ),
    "videos" =>  array(
        "showVideoPlayer" =>  $_CK_CONF['filemanager_show_video_player'],
        "videosExt" =>  explode(',',$_CK_CONF['filemanager_videos_ext']),
        "videosPlayerWidth" =>  $_CK_CONF['filemanager_videos_player_width'],
        "videosPlayerHeight" =>  $_CK_CONF['filemanager_videos_player_height']
    ),
    "audios" =>  array (
        "showAudioPlayer" =>  $_CK_CONF['filemanager_show_audio_player'],
        "audiosExt" =>  explode(',',$_CK_CONF['filemanager_audios_ext'])
    ),
    "pdfs" => array (
        "showPdfReader" => true,
        "pdfsExt" => array (
            "pdf",
            "odp"
        ),
	    "pdfsReaderWidth" => "640",
        "pdfsReaderHeight" => "480"
    ),

    "edit" =>  array(
        "enabled" =>  $_CK_CONF['filemanager_edit_enabled'],
        "lineNumbers" =>  $_CK_CONF['filemanager_edit_linenumbers'],
        "lineWrapping" =>  $_CK_CONF['filemanager_edit_linewrapping'],
        "codeHighlight" =>  $_CK_CONF['filemanager_edit_codehighlight'],
        "theme" =>  "elegant",
        "editExt" =>  explode(',',$_CK_CONF['filemanager_edit_editext'])
    ),
    "customScrollbar" => array(
        "enabled" => true,
        "theme" => "insert-2-dark",
        "button" => true
    ),
    "extras" =>  array(
        "extra_js" =>  array(),
        "extra_js_async" =>  true
    ),
    "icons" =>  array(
        "path" =>  "images/fileicons/",
        "directory" =>  "_Open.png",
        "default" =>  "default.png"
    ),
    "url" => "https://github.com/simogeo/Filemanager",
    "version" => "2.3.0"
);

$fm_config = json_encode($fmconfiguration);

$iid = 'fm_config_'.CACHE_security_hash();
$fm_user_cfg_file = CACHE_instance_filename($iid);

if ($fm_config !== false) {
    if (@file_put_contents($fm_user_cfg_file, $fm_config) === false) {
        COM_errorLog('Filemanager: configuration file "' . $fm_user_cfg_file . '" is not writable');
    } else {
        SESS_setVar('fm',CACHE_security_hash());
    }
}

$T->parse('output','filemanager');
header('Expires: on, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: text/html; charset=utf-8');
echo $T->finish($T->get_var('output'));
?>