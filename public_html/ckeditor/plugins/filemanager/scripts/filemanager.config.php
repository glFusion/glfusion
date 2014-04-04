<?php
require_once '../../../../lib-common.php';
if ( SEC_inGroup('Root') ) {
    $capabilities = array("select", "download", "rename", "move", "delete", "replace");
} else {
    $capabilities = array("select");
}
$fmconfiguration = array(
    "_comment" => "IMPORTANT : go to the wiki page to know about options configuration https://github.com/simogeo/Filemanager/wiki/Filemanager-configuration-file",
        "options" => array(
            "culture" =>  "en",
            "lang" => "php",
            "defaultViewMode" => "grid",
            "autoload" => true,
            "showFullPath" => false,
            "showTitleAttr" => false,
            "browseOnly" => false,
            "showConfirmation" => true,
            "showThumbs" => true,
            "generateThumbnails" => true,
            "searchBox" => true,
            "listFiles" => true,
            "fileSorting" => "default",
            "chars_only_latin" => true,
            "dateFormat" => "d M Y H:i",
            "serverRoot" => true,
            "fileRoot" => "images/library/",
            "relPath" => false,
            "logger" => false,
            "capabilities" => $capabilities,
            "plugins" => array()
        ),
        "security" => array(
            "uploadPolicy" => "DISALLOW_ALL",
            "uploadRestrictions" =>  array(
                "jpg",
                "jpeg",
                "gif",
                "png",
                "svg",
                "txt",
                "pdf",
                "odp",
                "ods",
                "odt",
                "rtf",
                "doc",
                "docx",
                "xls",
                "xlsx",
                "ppt",
                "pptx",
                "ogv",
                "mp4",
                "webm",
                "m4v",
                "ogg",
                "mp3",
                "wav"
            )
    ),
    "upload" => array(
        "overwrite" => false,
        "imagesOnly" => false,
        "fileSizeLimit" => 16
    ),
    "exclude" => array(
        "unallowed_files" => array(".htaccess"),
        "unallowed_dirs" => array(
            "_thumbs",
            ".CDN_ACCESS_LOGS",
            "cloudservers"
        ),
        "unallowed_files_REGEXP" => "/^\\./uis",
        "unallowed_dirs_REGEXP" => "/^\\./uis"
    ),
    "images" => array(
        "imagesExt" => array(
            "jpg",
            "jpeg",
            "gif",
            "png",
            "svg"
        ),
        "resize"=> array(
        	"enabled" => true,
        	"maxWidth" =>  1280,
            "maxHeight" => 1024
        )
    ),
    "videos" => array(
        "showVideoPlayer" => true,
        "videosExt" => array(
            "ogv",
            "mp4",
            "webm",
            "m4v"
        ),
        "videosPlayerWidth" => 400,
        "videosPlayerHeight" => 222
    ),
    "audios" => array(
        "showAudioPlayer" => true,
        "audiosExt" => array(
            "ogg",
            "mp3",
            "wav"
        )
    ),
    "extras" => array(
        "extra_js" => array(),
        "extra_js_async" => false
    ),
    "icons" => array(
        "path" => "images/fileicons/",
        "directory" => "_Open.png",
        "default" => "default.png"
    )
);

echo json_encode($fmconfiguration);
?>