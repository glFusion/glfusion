<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
* Language file Include
*/
$langfile = $_CONF['path'] . 'plugins/mediagallery/language/' . $_CONF['language'] . '.php';
$custfile = $_CONF['path'] . 'plugins/mediagallery/language/custom/' . $_CONF['language'] . '.php';
if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/mediagallery/language/english.php';
    $custfile = $_CONF['path'].'plugins/mediagallery/language/custom/english.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}

define("MG_JPG",1);
define("MG_PNG",2);
define("MG_TIF",4);
define("MG_GIF",8);
define("MG_BMP",16);
define("MG_TGA",32);
define("MG_PSD",64);
define("MG_MP3",128);
define("MG_OGG",256);
define("MG_ASF",512);
define("MG_SWF",1024);
define("MG_MOV",2048);
define("MG_MP4",4096);
define("MG_MPG",8192);
define("MG_ZIP",16384);
define("MG_OTHER",32768);
define("MG_PDF",65536);
define("MG_FLV",131072);
define("MG_RFLV",262144);
define("MG_EMB",524288);

USES_lib_image();
/*
* Include MediaGallery config file
*/
require_once $_CONF['path'].'plugins/mediagallery/mediagallery.php';
require_once $_CONF['path'].'plugins/mediagallery/config.php';
require_once $_CONF['path'].'plugins/mediagallery/include/classAlbum.php';

$mg_installed_version = $_PLUGIN_INFO['mediagallery'];

// This is a list of valid extensions for image media thumbnails / display images
$_MG_CONF['validExtensions'] = array('.jpg','.jpeg','.png','.gif','.bmp');

$swfjsinclude = 0;
$themeStyle = '';

$_MG_CONF['random_skin'] = 'default';
$_MG_CONF['zip_enabled'] = 0;
$_MG_CONF['popup_from_album'] = 0;
$_MG_CONF['autotag_caption'] = 0;
$_MG_CONF['jhead_enabled'] = 0;
$_MG_CONF['jpegtran_enabled'] = 0;
$_MG_CONF['ffmpeg_enabled'] = 0;
$_MG_CONF['zip_path'] = '';
$_MG_CONF['ffmpeg_path'] = '';
$_MG_CONF['indextheme'] = '';
$_MG_CONF['jhead_path'] = '';
$_MG_CONF['jpegtran_path'] = '';

// Read config data
$result = DB_query("SELECT * FROM " . $_TABLES['mg_config'],1);
while ($row = DB_fetchArray($result)) {
    $_MG_CONF[$row['config_name']] = $row['config_value'];
}
$_MG_CONF['tmp_path'] = $_CONF['path'] . 'plugins/mediagallery/tmp/';

if ( $_CONF['loginrequired'] == 1 ) {
    $_MG_CONF['loginrequired'] = 1;
}

$_MG_CONF['up_mp3_player_enabled'] = 0;

if ( $_MG_CONF['up_display_rows_enabled'] == 1 ||
     $_MG_CONF['up_display_columns_enabled'] == 1 ||
     $_MG_CONF['up_mp3_player_enabled'] == 1 ||
     $_MG_CONF['up_av_playback_enabled'] == 1 ||
     $_MG_CONF['up_thumbnail_size_enabled'] == 1) {
    $LANG_MYACCOUNT['pe_mgprefs'] = "MG Prefs";
}

function plugin_chkVersion_mediagallery() {
    global $_MG_CONF;

    return $_MG_CONF['pi_version'];
}

function plugin_autotags_mediagallery ( $op, $content = '', $autotag = '') {
    global $_CONF, $_TABLES, $_MG_CONF, $LANG_MG_AUTOTAG;

    if ($op == 'tagname' ) {
        return array('album','media','img','slideshow','fslideshow','video','audio','download',
                      'image','oimage','mlink','alink','playall');
    } else if ( $op == 'tagusage' ) {
        $tagUsage = array(
            array('namespace' => 'mediagallery','usage' => 'album_title'),
            array('namespace' => 'mediagallery','usage' => 'album_description'),
            array('namespace' => 'mediagallery','usage' => 'media_title'),
            array('namespace' => 'mediagallery','usage' => 'media_description'),
            array('namespace' => 'mediagallery','usage' => 'category_title'),
            array('namespace' => 'mediagallery','usage' => 'category_description')
        );
        return $tagUsage;
    } else if ($op == 'desc' ) {
        switch ($content) {
            case 'album' :
                return $LANG_MG_AUTOTAG['desc_album'];
                beak;
            case 'media' :
                return $LANG_MG_AUTOTAG['desc_media'];
                break;
            case 'img' :
                return $LANG_MG_AUTOTAG['desc_img'];
                break;
            case 'slideshow' :
                return $LANG_MG_AUTOTAG['desc_slideshow'];
                break;
            case 'fslideshow' :
                return $LANG_MG_AUTOTAG['desc_fslideshow'];
                break;
            case 'video' :
                return $LANG_MG_AUTOTAG['desc_video'];
                break;
            case 'audio' :
                return $LANG_MG_AUTOTAG['desc_audio'];
                break;
            case 'download' :
                return $LANG_MG_AUTOTAG['desc_download'];
                break;
            case 'image' :
                return $LANG_MG_AUTOTAG['desc_image'];
                break;
            case 'oimage' :
                return $LANG_MG_AUTOTAG['desc_oimage'];
                break;
            case 'mlink' :
                return $LANG_MG_AUTOTAG['desc_mlink'];
                break;
            case 'alink' :
                return $LANG_MG_AUTOTAG['desc_alink'];
                break;
            case 'playall' :
                return $LANG_MG_AUTOTAG['desc_playall'];
                break;
            default :
                return '';
        }
    } else if ($op == 'parse') {
        require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
        require_once $_CONF['path'].'plugins/mediagallery/include/autotag.inc.php';
        return _mg_autotags( $op, $content, $autotag );
    }
}


/**
* This will put an option for the plugin in the command and control block on moderation.php
*
* Add the plugin name, icon and link to the command and control block in moderation.php
*
* @return   array   Array containing (plugin name, admin url, url of plugin icon)
*
*/

function plugin_cclabel_mediagallery()
{
    global $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00;

    if (SEC_hasRights('mediagallery.config')) {
        return array($LANG_MG00['plugin'],
            $_MG_CONF['admin_url'] . 'index.php',
            $_MG_CONF['site_url'].'/images/mediagallery.png');
    } else {
        return '';
    }
}

/**
* Returns the user option for this plugin
*
* Adds the plugin to the user menu
*
* @return   array   Array containing (plugin name, plugin user url, 0)
*
*/
function plugin_getuseroption_mediagallery()
{
    global $_CONF, $_MG_CONF, $_USER, $LANG_MG01, $LANG_MG03, $_TABLES;

    if ( COM_isAnonUser() )  {
        return '';
    }

    if ( $_MG_CONF['up_display_rows_enabled'] == 0 &&
         $_MG_CONF['up_display_columns_enabled'] == 0 &&
         $_MG_CONF['up_mp3_player_enabled'] == 0 &&
         $_MG_CONF['up_av_playback_enabled'] == 0 &&
         $_MG_CONF['up_thumbnail_size_enabled'] == 0) {
             return '';
    }

    $title = $LANG_MG01['user_prefs'];

    if ( $_MG_CONF['member_albums'] == 1 && $_MG_CONF['link_to_member_album'] == 1 && !COM_isAnonUser() ) {
        $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . $_USER['uid'] . " AND album_parent='" . $_MG_CONF['member_album_root'] . "' LIMIT 1";
        $result = DB_query($sql);
        $numRows = DB_numRows($result);
        if ( $numRows > 0 ) {
            $A = DB_fetchArray($result);
            $album_id = $A['album_id'];
            return array($LANG_MG03['your_member_album'],$_MG_CONF['site_url'] . '/album.php?aid=' . $album_id,0);
        }
    }
    return '';
}

function plugin_centerblock_mediagallery( $where=1, $page=1, $topic ='' )
{
    global $_CONF;

    require_once $_CONF['path'].'plugins/mediagallery/include/centerblock.inc.php';

    return _mg_centerblock( $where, $page, $topic );
}


/**
* Check to see if this Plugin uses comments
*
* @return   boolean true or false
*
*/
function plugin_commentsupport_mediagallery()
{
    return true;
}

/**
 * Plugin function to delete a comment
 * $cid    Comment to be deleted
 * $id     Item id to which $cid belongs
 *
 */
function plugin_deletecomment_mediagallery($cid,$id)
{
    global $_CONF, $_MG_CONF, $_TABLES, $MG_albums;

    require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
    require_once $_CONF['path'].'plugins/mediagallery/include/comment.inc.php';

    return _mg_deletecomment($cid,$id);
}

/**
 * Plugin function to display a specific comment thread
 * $id      Unique idenifier for item comment belongs to
 * $cid     Comment id to display (possibly including sub-comments)
 * $title   Page/comment title
 * $order   'ASC' or 'DSC' or blank
 * $format  'threaded', 'nested', or 'flat'
 * $page    Page number of comments to display
 * $view    True to view comment (by cid), false to display (by $pid)
 */
function plugin_displaycomment_mediagallery($id,$commentid,$title,$order,$format,$page,$view)
{
    global $_CONF, $_USER, $_MG_CONF, $LANG_LOGIN;

    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] ) {
        echo SEC_loginRequiredForm();
        exit;
    }
    require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
    require_once $_CONF['path'].'plugins/mediagallery/include/comment.inc.php';

    return _mg_displaycomment($id,$commentid,$title,$order,$format,$page,$view);
}

/**
 * Plugin function that is called after comment form is submitted.
 * Needs to at least save the comment and check return value.
 * Add any additional logic your plugin may need to perform on comments.
 *
 * $title       comment title
 * $comment     comment text
 * $id          Item id to which $cid belongs
 * $pid         comment parent
 * $postmode    'html' or 'text'
 *
 */
function plugin_savecomment_mediagallery($title,$comment,$id,$pid,$postmode)
{
    global $_CONF, $_MG_CONF, $_TABLES, $LANG03;

    require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
    require_once $_CONF['path'].'plugins/mediagallery/include/comment.inc.php';
    return _mg_savecomment($title,$comment,$id,$pid,$postmode);
}


function plugin_editcomment_mediagallery($cid,$id)
{
    global $_CONF, $_MG_CONF, $_TABLES;

    echo COM_refresh ($_MG_CONF['site_url'] . "/media.php?s=$id");
    exit;
}

function plugin_getcommenturlid_mediagallery( )
{
    global $_CONF, $_MG_CONF;
    $retval = array();
    $retval[] = $_MG_CONF['site_url'] . '/media.php';
    $retval[] = 's';
    $retval[] = 'page=';
    return $retval;
}

// Search related functions

function plugin_searchtypes_mediagallery()
{
    global $_MG_CONF,$LANG_MG00;

    if ( $_MG_CONF['disable_search_integration'] == 1) {
        return '';
    }
    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
        return '';
    }

    $tmp['mediagallery'] = $LANG_MG00['plugin'];
    return $tmp;
}

/**
* this searches the plugins comments matching the user query and returns an array of
* for the header and table rows back to search.php where it will be formated and
* printed
*
* @query            string          Keywords user is looking for
* @datestart        date/time       Start date to get results for
* @dateend          date/time       End date to get results for
* @topic            string          The topic they were searching in
* @type             string          Type of items they are searching
* @author           string          Get all results by this author
*
*/
function plugin_dopluginsearch_comment_mediagallery($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $MG_albums, $_TABLES, $_CONF, $_MG_CONF, $LANG_MG00, $LANG_MG01,$LANG09;

    if ( $_MG_CONF['disable_search_integration'] == 1) {
        return '';
    }
    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
        return '';
    }

    $query = trim(DB_escapeString(htmlspecialchars($query)));

    $sql =  "SELECT ma.media_id AS id, c.title AS title, c.comment AS description, UNIX_TIMESTAMP(c.date) AS date, c.uid AS uid, '0' AS hits, ";
    $sql .= "CONCAT('/mediagallery/media.php?s=',ma.media_id,'#comments') AS url ";
    $sql .= "FROM {$_TABLES['users']} AS u, {$_TABLES['comments']} AS c ";
    $sql .= "LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON c.sid=ma.media_id ";
    $sql .= "LEFT JOIN {$_TABLES['mg_albums']} AS a ON ma.album_id=a.album_id ";
    $sql .= "WHERE (c.type='mediagallery') AND (u.uid = c.uid) ";
    $sql .= COM_getPermSQL('AND') . ' ';

    if (!empty($author)) {
        $sql .= "AND (c.uid = '$author') ";
    }

    $search_c = new SearchCriteria('comments', 'Media Gallery > '.$LANG09[66]);
    $columns = array('comment','c.title');
    $sql .= $search_c->getDateRangeSQL('AND', 'UNIX_TIMESTAMP(c.date)', $datestart, $dateend);
    list($sql,$ftsql) = $search_c->buildSearchSQL($keyType, $query, $columns, $sql);
    $search_c->setSQL($sql);
    $search_c->setFTSQL($ftsql);
    $search_c->setRank(2);

    return $search_c;
}

/**
* this searches for pages matching the user query and returns an array of
* for the header and table rows back to search.php where it will be formated and
* printed
*
* @query            string          Keywords user is looking for
* @datestart        date/time       Start date to get results for
* @dateend          date/time       End date to get results for
* @topic            string          The topic they were searching in
* @type             string          Type of items they are searching
* @author           string          Get all results by this author
*
*/
function plugin_dopluginsearch_mediagallery($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage) {
    global $MG_albums, $_TABLES, $_CONF, $_MG_CONF, $LANG_MG00, $LANG_MG01,$LANG09;

    if ( $_MG_CONF['disable_search_integration'] == 1) {
        return '';
    }
    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
        return '';
    }

    $query = trim(DB_escapeString(htmlspecialchars($query)));

    $sql = "SELECT DISTINCT m.media_id AS id,m.media_title AS title,m.media_desc AS description,m.media_time AS date,m.media_user_id AS uid,m.media_views AS hits, CONCAT('".$_MG_CONF['site_url']."/media.php?s=',m.media_id) AS url  FROM " .
            $_TABLES['mg_media'] . " as m " .
            " INNER JOIN " . $_TABLES['mg_media_albums'] . " as ma " .
            " ON m.media_id=ma.media_id " .
            " INNER JOIN {$_TABLES['mg_albums']} AS a ON ma.album_id=a.album_id " .
            " WHERE 1=1 ";
    $sql .= COM_getPermSQL('AND') . ' ';

    if( !SEC_inGroup( 'Root')) {
        $sql .= ' AND hidden=0 ';
    }

    if (!empty ($author))
        $sql .= "AND (m.media_user_id = '$author') ";

    $search = new SearchCriteria('mediagallery', 'Media Gallery');
    $columns = array('m.media_desc','m.media_title', 'm.media_keywords');
    $sql .= $search->getDateRangeSQL('AND', 'm.media_time', $datestart, $dateend);
    list($sql,$ftsql) = $search->buildSearchSQL($keyType, $query, $columns, $sql);
    $search->setSQL($sql);
    $search->setFTSQL($ftsql);
    $search->setRank(3);

    return $search;
}


/**
* Returns the items for this plugin that should appear on the main menu
*
* NOTE: this MUST return the url/value pairs in the following format
* $<arrayname>[<label>] = <url>
*
* @return    array   Associative array of menu items to be added to menu
*
*/

function plugin_getmenuitems_mediagallery()
{
    global $_CONF, $_USER, $_MG_CONF;

    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
        return '';
    }

    $menulabel = $_MG_CONF['menulabel'];
    $menurul   = $_MG_CONF['site_url'] . "/index.php";     // Menu url here

    $menuitems = array();
    $menuitems["$menulabel"] = $menurul;

    return $menuitems;
}

/**
* Returns the administrative option for this plugin
*
* Adds the plugin to the Admin menu
*
* @return   array   Array containing (plugin name, plugin admin url, # of items in plugin or '')
*
*/
function plugin_getadminoption_mediagallery()
{
    global $_CONF, $_TABLES, $LANG_MG00, $_MG_CONF;

    if (SEC_hasRights(array('mediagallery.config','mediagallery.admin'))) {
        return array($LANG_MG00['plugin'], $_MG_CONF['admin_url'] . 'index.php', DB_count($_TABLES['mg_albums']));
    }
}

function plugin_getheadercss_mediagallery()
{
    global $_CONF, $_MG_CONF, $_USER, $_SYSTEM, $swfjsinclude,$themeStyle;

    $styles = array();

    if ( @file_exists($_CONF['path_layout'].'plugins/mediagallery/style.css') ) {
        $styleFile = $_CONF['path_layout'].'plugins/mediagallery/style.css';
        if ( !isset($_SYSTEM['disable_mootools']) || $_SYSTEM['disable_mootools'] == false ) {
            $lightboxFile = $_CONF['path_layout'] . 'mediagallery/lightbox.css';
        	$styles[] = $lightboxFile;
        }
    } else if ( $_MG_CONF['template_path'] == $_CONF['path'] . 'plugins/mediagallery/templates' ) {
	    $styleFile = $_MG_CONF['path_html'] . 'style.css';
        if ( !isset($_SYSTEM['disable_mootools']) || $_SYSTEM['disable_mootools'] == false ) {
            $lightboxFile = $_MG_CONF['path_html'] . 'lightbox.css';
        	$styles[] = $lightboxFile;
        }

	} else {
		$styleFile = $_CONF['path_layout'] . 'mediagallery/style.css';
        if ( !isset($_SYSTEM['disable_mootools']) || $_SYSTEM['disable_mootools'] == false ) {
            $lightboxFile = $_CONF['path_layout'] . 'mediagallery/lightbox.css';
        	$styles[] = $lightboxFile;
        }
	}
	$styles[] = $styleFile;


    // check for random skin css
    $cacheInstance = 'mgrs' . $_MG_CONF['random_skin'] . '__' . $_USER['theme'];
    $retval = CACHE_check_instance($cacheInstance, 0);
    if ( $retval !== false) {
        $styles[] = CACHE_instance_filename($cacheInstance,0);
    } else {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/classFrame.php';
        $nFrame = new mgFrame();
        $nFrame->constructor( $_MG_CONF['random_skin'] );
        $fCSS= $nFrame->getCSS();
        CACHE_create_instance($cacheInstance, $fCSS, 0);
        $styles[] = CACHE_instance_filename($cacheInstance,0);
    }

	return ($styles);
}

function plugin_getheaderjs_mediagallery()
{
    global $_CONF, $_MG_CONF, $_SYSTEM, $swfjsinclude,$themeStyle;

    $js = array();

    $js[] = $_MG_CONF['path_html'] . 'js/swfobject.js';
    $js[] = $_MG_CONF['path_html'] . 'js/mediagallery.js';
    if ( !isset($_SYSTEM['disable_mootools']) || $_SYSTEM['disable_mootools'] == false ) {
        $js[] = $_MG_CONF['path_html'] . 'js/slideshow.js';
        $js[] = $_MG_CONF['path_html'] . 'js/slimbox.js';
    }

//    if ( isset($_SYSTEM['disable_jquery_slimbox']) && $_SYSTEM['disable_jquery_slimbox'] == true ) {
//        $js[] = $_CONF['path_html'].'javascript/addons/slimbox/slimbox2.min.js';
//    }


	return ($js);
}

function plugin_getglobaljs_mediagallery()
{
    global $_MG_CONF, $_CONF;

    $globalJS = array();
    $globalJS['mgBaseURL'] = $_MG_CONF['site_url'];

    return($globalJS);
}

function plugin_getheadercode_mediagallery()
{
    global $_CONF, $_MG_CONF, $swfjsinclude,$themeStyle;

    $mg_header = '';

    if ( $themeStyle != '' ) {
        $mg_header .= $themeStyle;
    }
    return $mg_header;
}

/**
  * API function provides the content of our "What's New" feed
*/
function plugin_getwhatsnew_mediagallery()
{
    global $MG_albums, $_TABLES, $_CONF, $_MG_CONF, $_USER, $LANG_MG00,$LANG_MG03, $mg_installed_version;

    $retval = '';

    if ($_MG_CONF['whatsnew'] == 0 ) {
        return;
    }

    if ( $mg_installed_version != $_MG_CONF['pi_version'] ) {
        return;
    }

    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
        return;
    }
    MG_initAlbums();
    $newMediaCount    = 0;
    $newCommentCount = 0;

    $items = array();

    $newAge = time() - ($_MG_CONF['whatsnew_time'] * 86400);

    $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE last_update >= '" . $newAge . "' ORDER BY last_update DESC";
    $result = DB_query($sql,1);
    while ($row = DB_fetchArray($result)) {
        $aid = $row['album_id'];

        if ( isset($MG_albums[$aid]) && $MG_albums[$aid]->access > 0 && $MG_albums[$aid]->hidden != 1 && $MG_albums[$aid]->getOffset() != -1) {
            $title = $MG_albums[$aid]->title;
            if ( strlen($title) > $_MG_CONF['title_length'] ) {
                $dTitle = substr($title,0,$_MG_CONF['title_length'] - 3) . '...';
            } else {
                $dTitle = $title;
            }
            $items[] = '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $aid . '" title="'.htmlspecialchars($title,ENT_COMPAT,COM_getEncodingt()).'">' . $dTitle . '</a>';
            $newMediaCount++;
        }

    }
    if ( $newMediaCount == 0 ) {
        $retval .= $LANG_MG03['no_new_items'] . '<br>' . LB;
    }

    if ( $_MG_CONF['disable_whatsnew_comments'] == 0 ) {
        $sql = "SELECT * FROM {$_TABLES['comments']} AS c LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON c.sid=ma.media_id LEFT JOIN {$_TABLES['mg_albums']} AS a ON ma.album_id=a.album_id WHERE c.date >=  DATE_SUB(NOW(), INTERVAL " . $_MG_CONF['whatsnew_time'] . " DAY ) AND c.type='mediagallery' GROUP BY c.sid ORDER BY c.date DESC LIMIT 15";

        $result = DB_query( $sql,1);
        $nrows2 = DB_numRows( $result );
        if( $nrows2 == 0 ) {
            $retval .= '' . '<br />' . LB;
        } else {
            while ($A = DB_fetchArray($result)) {
                $access = SEC_hasAccess ($A['owner_id'],
                                         $A['group_id'],
                                         $A['perm_owner'],
                                         $A['perm_group'],
                                         $A['perm_members'],
                                         $A['perm_anon']);
                if ( $access > 0 ) {
                    $titleLength = $_MG_CONF['title_length'];        // Compensate for the added HTML
                    $title = "<b>C:</b>&nbsp;".$A['title'];
                    $str = "<a href=\"{$_CONF['site_url']}/comment.php?mode=view&amp;cid={$A['cid']}&amp;sid={$A['sid']}&amp;type=mediagallery\">";
                    $str .= substr($title,0,$titleLength);
                    $str .= '</a>';
                    $items[] = $str;
                    $newCommentCount++;
                }
            }
        }
    }
    $label = sprintf($LANG_MG00['whatsnew_prompt'],$_MG_CONF['whatsnew_time']);
    if ( $newMediaCount == 0 && $newCommentCount == 0 ) {
        return $retval;
    } else {
        return array($LANG_MG00['whatsnew_title'],$label,$items);
    }
}

/**
* Shows the statistics for the plugin on stats.php.  If
* $showsitestats is 1 then we are to only print the overall stats in the 'site
* statistics' box otherwise we show the detailed stats for the plugin
*
* @param    int showsitestate   Flag to let us know which stats to get
* @return   string  returns formatted HTML to insert in stats page
*
*/
function plugin_showstats_mediagallery($showsitestats)
{
    global $_CONF, $_MG_CONF, $_TABLES, $LANG_MG00, $MG_albums, $_USER;

    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
        return;
    }
    require_once $_CONF['path'].'plugins/mediagallery/include/stats.inc.php';
    return _mg_showstats($showsitestats);
}

function plugin_statssummary_mediagallery()
{
    global $_CONF, $_MG_CONF, $_TABLES, $LANG_MG00, $_USER;

    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
        return;
    }
    require_once $_CONF['path'].'plugins/mediagallery/include/stats.inc.php';
    return _mg_statssummary();
}


/**
* returns list of moderation values
*
* The array returned contains (in order): the row 'id' label, main plugin
* table, moderation fields (comma seperated), and plugin submission table
*
* @return       array        Returns array of useful moderation values
*
*/
function plugin_moderationvalues_mediagallery()
{
    global $_TABLES;

    return array (
        'media_id',
        $_TABLES['mg_media'],
        "media_id,media_filename,media_original_filename,media_mime_ext,media_exif,mime_type,media_title,media_desc,media_keywords,media_time,media_views,media_comments,media_votes,media_rating,media_resolution_x,media_resolution_y,remote_media,remote_url,media_tn_attached,media_tn_image,include_ss,media_user_id,media_user_ip,media_approval,media_type,media_upload_time,media_category,media_watermarked,artist,album,genre,v100,maint",
        $_TABLES['mg_mediaqueue']
    );
}

/**
* show files for moderation on submissions page
*
* Uses the Plugin class to return data required by moderation.php to list
* plugin objects that need to be moderated.
*
* @param        string token The
* @return       Plugin       return HTML
*
*/
function plugin_itemlist_mediagallery($token)
{
    global $_CONF, $_TABLES, $_USER, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG02,$LANG_MG10;
    global $LANG01, $LANG24, $LANG29, $LANG_ADMIN, $_IMAGE_TYPE;

    $retval = '';
    $key='media_id';

    if ( COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $_GROUPS = SEC_getUserGroups($uid);
    $groupsql = '';
    if (count($_GROUPS) == 1) {
        $groupsql .= " mod_group_id = '" . current($_GROUPS) ."'";
    } else {
        $groupsql .= " mod_group_id IN (" . implode(',',array_values($_GROUPS)) .")";
    }

    $sql = "SELECT * FROM {$_TABLES['mg_albums']} WHERE " . $groupsql;
    $result = DB_query($sql);
    $albumCount = DB_numRows($result);
    if ( $albumCount < 1 ) {
        return;
    }
    $modAlbums = array();
    for ( $i=0; $i < $albumCount; $i++ ) {
        $A = DB_fetchArray($result);
        $modAlbums[] = $A['album_id'];
    }
    $sql = "SELECT m.media_id AS id,m.media_title AS title,m.media_upload_time AS day,ma.album_id, m.media_user_id AS uid, m.media_type,m.media_filename,m.media_mime_ext "
         . "FROM {$_TABLES['mg_media_album_queue']} as ma INNER JOIN {$_TABLES['mg_mediaqueue']} as m "
         . "ON ma.media_id=m.media_id WHERE ma.album_id IN (" . implode(',',array_values($modAlbums)) . ") ORDER BY ma.media_order DESC";

    $result = DB_query($sql);
    $nrows = DB_numRows($result);

    if ( $nrows == 0 ) return;

    $data_arr = array();
    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray($result);
        $A['edit'] = $_MG_CONF['site_url'] . '/admin.php?mode=mediaeditq&amp;mid=' . $A['id'] . '&amp;album_id=' . $A['album_id'] .  '&amp;t=' . time();
        $A['_type_']  = 'mediagallery';
        $A['_key_']   = $key;      // name of key/id field
        $data_arr[$i] = $A;     // push row data into array
    }

    $header_arr = array(      // display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 0, 'align' => 'center', 'width' => '25px'),
        array('text' => $LANG_MG01['mod_mediatitle'], 'field' => 'title'),
        array('text' => $LANG_MG10['upload_date'], 'field' => 'day', 'align' => 'center', 'width' => '15%'),
        array('text' => $LANG_MG01['album'], 'field' => 'album_id', 'width' => '20%'),
        array('text' => $LANG_MG01['uploaded_by'], 'field' => 'uid', 'width' => '15%', 'nowrap' => true),
        array('text' => $LANG29[1], 'field' => 'approve', 'align' => 'center', 'width' => '35px'),
        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'align' => 'center', 'width' => '35px')
    );

    $text_arr = array('has_menu'    => false,
                      'title'       => $LANG_MG01['submissions'],
                      'help_url'    => '',
                      'no_data'     => $LANG29[39],
                      'form_url'    => "{$_CONF['site_admin_url']}/moderation.php"
    );

    $actions = '<input name="approve" type="image" src="'
        . $_CONF['layout_url'] . '/images/admin/accept.' . $_IMAGE_TYPE
        . '" style="vertical-align:bottom;" title="' . $LANG29[44]
        . '" onclick="return confirm(\'' . $LANG29[45] . '\');"'
        . '/>&nbsp;' . $LANG29[1];
    $actions .= '&nbsp;&nbsp;&nbsp;&nbsp;';
    $actions .= '<input name="delbutton" type="image" src="'
        . $_CONF['layout_url'] . '/images/admin/delete.' . $_IMAGE_TYPE
        . '" style="vertical-align:text-bottom;" title="' . $LANG01[124]
        . '" onclick="return confirm(\'' . $LANG01[125] . '\');"'
        . '/>&nbsp;' . $LANG_ADMIN['delete'];

    $options = array('chkselect' => true,
                     'chkfield' => 'id',
                     'chkname' => 'selitem',
                     'chkminimum' => 0,
                     'chkall' => false,
                     'chkactions' => $actions,
                     );

    $form_arr['bottom'] = '<input type="hidden" name="type" value="mediagallery"/>' . LB
            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"/>' . LB
            . '<input type="hidden" name="moderation" value="x"/>' . LB
            . '<input type="hidden" name="count" value="' . $nrows . '"/>';

    $retval .= ADMIN_simpleList('_mg_getListField', $header_arr,
                              $text_arr, $data_arr, $options, $form_arr, $token);
    return $retval;
}

/**
 * Returns formatted field values for the moderation lists
 *
 */
function _mg_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $_TABLES, $_MG_CONF, $MG_albums, $LANG_ADMIN, $LANG28, $LANG29, $LANG_MG01, $_IMAGE_TYPE;

    $retval = '';

    $type = '';
    if (isset($A['_type_']) && !empty($A['_type_'])) {
        $type = $A['_type_'];
    } else {
        return $retval; // we can't work without an item type
    }

    $dt = new Date('now',$_USER['tzid']);

    $field = $fieldname;

    switch ($field) {

        case 'edit':
            $retval = COM_createLink($icon_arr['edit'], $A['edit']);
            break;

        case 'user':
            $retval =  '<img src="' . $_CONF['layout_url']
            . '/images/admin/user.' . $_IMAGE_TYPE
            . '" style="vertical-align:bottom;"/>&nbsp;' . $fieldvalue;
            break;

        case 'day':
            $dt->setTimeStamp($A['day']);
            $retval = $dt->format($_CONF['daytime'],true);
            break;

        case 'album_id' :
            $retval = DB_getItem($_TABLES['mg_albums'],'album_title','album_id='.(int) $A['album_id']);
            break;

        case 'title' :
            $dImage = '';
            $retval = '';
            switch ( $A['media_type']) {
                case 0 :
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $A['media_filename'][0] .'/' . $A['media_filename'] . $ext) ) {
                            $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/tn/' . $A['media_filename'][0] .'/' . $A['media_filename'] . $ext;
                            $dImage     = $_MG_CONF['mediaobjects_url'] . '/disp/' . $A['media_filename'][0] .'/' . $A['media_filename'] . $ext;
                            $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'tn/' . $A['media_filename'][0] .'/' . $A['media_filename'] . $ext;
                            break;
                        }
                    }
                    break;
                case 1 :
                    switch ( $A['media_mime_ext'] ) {
                        case 'swf' :
                            $thumbnail = $_MG_CONF['mediaobjects_url'] . '/flash.png';
                            $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'flash.png';
                            break;
                        case 'mov' :
                        case 'mp4' :
                            $thumbnail = $_MG_CONF['mediaobjects_url'] . '/quicktime.png';
                            $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'quicktime.png';
                            break;
                        case 'asf' :
                            $thumbnail = $_MG_CONF['mediaobjects_url'] . '/wmp.png';
                            $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'wmp.png';
                            break;
                        default :
                            $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/video.png';
                            $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'video.png';
                            break;
                    }
                    break;
                case 2 :
                    $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/audio.png';
                    $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'audio.png';
                    break;
                case 4 :
                    switch ($A['media_mime_ext']) {
                        case 'zip' :
                        case 'arj' :
                        case 'rar' :
                        case 'gz'  :
                            $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/zip.png';
                            $pThumbnail  = $_MG_CONF['path_mediaobjects'] . 'zip.png';
                            break;
                        case 'pdf' :
                            $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/pdf.png';
                            $pThumbnail  = $_MG_CONF['path_mediaobjects'] . 'pdf.png';
                            break;
                        default :
                            $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/generic.png';
                            $pThumbnail  = $_MG_CONF['path_mediaobjects'] . 'generic.png';
                            break;
                    }
                    break;
            }

            $img_size = @getimagesize($pThumbnail);
            if ( $img_size != false ) {
                $imgwidth  = $img_size[0];
                $imgheight = $img_size[1];
                if ( $imgwidth > $imgheight ) {
                    $ratio = $imgwidth / 90;
                    $width = 90;
                    $height = round($imgheight / $ratio);
                } else {
                    $ratio = $imgheight / 90;
                    $height = 90;
                    $width = round($imgwidth / $ratio);
                }
            }
            if ( $dImage != '' ) {
                $retval .= '<a href="'.$dImage.'" rel="lightbox" data-uk-lightbox target="_new">';
            }
            $retval .= '<img title="'.$LANG_MG01['lightbox_image'].'" style="float:left;padding:5px;" src="'.$thumbnail.'" height="'.$height.'" width="'.$width.'" alt="" />';
            if ( $dImage != '' ) {
                $retval .= '</a>';
            }
            $retval .= '<p>'.$A['title'].'</p>';

            break;

        case 'uid':
            if ( !isset($A['uid']) ) {
                $A['uid'] = 1;
            }

            // lookup the username from the uid
            $username = DB_getItem($_TABLES['users'], 'username',
                                   "uid = ". (int) $A['uid']);

            if ($A['uid'] == 1) { // anonymous user
                $retval = $icon_arr['greyuser']
                            . '&nbsp;&nbsp;'
                            . '<span style="vertical-align:top">' . $username . '</span>';
            } else {
                $attr['title'] = $LANG28[108];
                $url = $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' .  $A['uid'];
                $retval = COM_createLink($icon_arr['user'], $url, $attr);
                $retval .= '&nbsp;&nbsp;';
                $attr['style'] = 'vertical-align:top;';
                $retval .= COM_createLink($username, $url, $attr);
            }
            break;

        case 'email':
            $url = 'mailto:' . $fieldvalue;
            $attr['title'] = $LANG28[111];
            $retval = COM_createLink($icon_arr['mail'], $url, $attr);
            $retval .= '&nbsp;&nbsp;';
            $attr['title'] = $LANG28[99];
            $url = $_CONF['site_admin_url'] . '/mail.php?uid=' . $A['uid'];
            $attr['style'] = 'vertical-align:top;';
            $retval .= COM_createLink($fieldvalue, $url, $attr);
            break;

        case 'approve':
            $retval = '';
            $attr['title'] = $LANG29[1];
            $attr['onclick'] = 'return confirm(\'' . $LANG29[48] . '\');';
            $retval .= COM_createLink($icon_arr['accept'],
                $_CONF['site_admin_url'] . '/moderation.php'
                . '?approve=x'
                . '&amp;type=mediagallery'
                . '&amp;id=' . $A[0]
                . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            break;

        case 'delete':
            $retval = '';
            $attr['title'] = $LANG_ADMIN['delete'];
            $attr['onclick'] = 'return confirm(\'' . $LANG29[49] . '\');';
            $retval .= COM_createLink($icon_arr['delete'],
                $_CONF['site_admin_url'] . '/moderation.php'
                . '?delete=x'
                . '&amp;type=mediagallery'
                . '&amp;id=' . $A[0]
                . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            break;

        default:
            $retval = COM_makeClickableLinks($fieldvalue);
            break;
    }

    return $retval;
}

/**
*
* Checks that the current user has the rights to moderate the
* plugin, returns true if this is the case, false otherwise
*
* @return        boolean       Returns true if moderator
*
*/
function plugin_ismoderator_mediagallery() {

    global $_TABLES, $_USER;

    if ( COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $_GROUPS = SEC_getUserGroups($uid);
    $groupsql = '';
    if (count($_GROUPS) == 1) {
        $groupsql .= " mod_group_id = '" . current($_GROUPS) ."'";
    } else {
        $groupsql .= " mod_group_id IN (" . implode(',',array_values($_GROUPS)) .")";
    }

    $sql = "SELECT * FROM {$_TABLES['mg_albums']} WHERE " . $groupsql;
    $result = DB_query($sql);
    $nrows  = DB_numRows($result);

    if ( SEC_hasRights('mediagallery.admin') || $nrows > 0 )
        return true;
    return false;
}

/**
* Returns the number of submissions waiting moderation
*
* Calculates the number of submissions that are currently waiting
* to be moderated and returns that number.
*
* @return      int       number of submissions awaiting moderation
*/
function plugin_submissioncount_mediagallery() {
    global $_TABLES;

    $result = DB_query("SELECT count(*) FROM {$_TABLES['mg_mediaqueue']}");
    list($count) = DB_fetchArray($result);

    return $count;
}

/**
* Performs plugin exclusive work for items approved by moderation
*
* While moderation.php handles the actual move from mediagallery submission
* to mediagallery tables, within the function we handle all other approval
* relate tasks
*
* @param      string       $id      Identifying string
* @return     string       Any wanted HTML output
*
*/
function plugin_moderationapprove_mediagallery($id)
{
    global $_CONF, $_TABLES, $_MG_CONF, $MG_albums, $LANG_MG01;

    $statusReport = '';

    require_once $_CONF['path'] . 'plugins/mediagallery/include/init.php';
    require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
    require_once $_CONF['path'] . 'lib/html2text/html2text.php';

    MG_initAlbums();

    $result = DB_query("SELECT * FROM {$_TABLES['mg_media']} WHERE media_id='".DB_escapeString($id)."'");
    if ( DB_numRows($result) > 0 ) {
        $media = DB_fetchArray($result);
        $owner_uid = $media['media_user_id'];
        $media_upload_time = $media['media_upload_time'];
        $media_filename = $media['media_filename'];
        $media_type = $media['media_type'];
        $sql = "SELECT * FROM " . $_TABLES['mg_media_album_queue'] . " WHERE media_id='" . DB_escapeString($id) . "'";
        $res2 = DB_query($sql);
        $nRows = DB_numRows($res2);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($res2);
            $sql = "INSERT INTO {$_TABLES['mg_media_albums']} (album_id,media_id,media_order) "
                   . "VALUES ('" .
                   (int) $row['album_id'] . "','" .
                   DB_escapeString($row['media_id']) . "',32000)";

            DB_query($sql);
            if ( DB_error() ) {
                COM_errorLog("Media Gallery: Error inserting queued item into album.");
            }
            $sql = "DELETE FROM " . $_TABLES['mg_media_album_queue'] . " WHERE media_id='" . DB_escapeString($id) . "'";
            DB_query($sql);
            if ( DB_error() ) {
                COM_errorLog("Media Gallery: Error deleting queued item from album queue.");
            }
            $statusReport .= "Media ID " . $id . $LANG_MG01['queue_processed'];
            MG_SortMedia( $row['album_id'] );

            $mediaCount  = DB_getItem($_TABLES['mg_albums'],'media_count','album_id=' . $row['album_id']);
            $album_cover = DB_getItem($_TABLES['mg_albums'],'album_cover','album_id=' . $row['album_id']);

            $media_count = $mediaCount + 1;
            DB_query("UPDATE {$_TABLES['mg_albums']} SET media_count=" . $media_count .
                     ",last_update='" . $media_upload_time . "'" .
                     " WHERE album_id='" . $row['album_id'] . "'");

            if ( $album_cover == -1 && $media_type == 0 ) {
                DB_query("UPDATE {$_TABLES['mg_albums']} SET album_cover_filename='" . DB_escapeString($media_filename) . "'" .
                         " WHERE album_id=" . (int) $row['album_id'] );
            }
            CACHE_remove_instance('whatsnew');
            // email the owner / uploader that the item has been approved.

            COM_clearSpeedlimit(600,'mgapprove');
            $last = COM_checkSpeedlimit ('mgapprove');
            if ( $last == 0 ) {
                $result2 = DB_query("SELECT username, fullname, email FROM {$_TABLES['users']} WHERE uid='" . (int) $owner_uid . "'");
                list($username,$fullname,$email) = DB_fetchArray($result2);
                if ( $email != '' ) {
                    $subject = $LANG_MG01['upload_approved'];
                    $body  = $LANG_MG01['upload_approved'];
                    $body .= '<br/><br/>';
                    $body .= $LANG_MG01['thanks_submit'];
                    $body .= '<br/><br/>';
                    $body .= $_CONF['site_name'] . '<br/>';
                    $body .= $_CONF['site_url'] . '<br/>';

                    $msgData = array();
                    $msgData['subject'] = $LANG_MG01['upload_approved'];
                    $msgData['htmlmessage'] = $body;
                    $html2txt = new html2text($body,false);
                    $msgData['textmessage'] = $html2txt->get_text();
                    $msgData['from'] = $_CONF['noreply_mail'];
                    $msgData['to']   = array('email' => $email, 'name' => $username);
                    COM_emailNotification($msgData);
                    COM_updateSpeedlimit ('mgapprove');
                }
            }
        }
    }

    return '';
}

/**
* Performs plugin exclusive work for items deleted by moderation
*
* While moderation.php handles the actual removal from mediagallery submission
* table, within this function we handle all other deletion
* relate tasks
*
* @param      string       $id      Identifying string
* @return     string       Any wanted HTML output
*
*/
function plugin_moderationdelete_mediagallery($id)
{
    global $_CONF, $_TABLES, $_MG_CONF;

    $result = DB_query("SELECT * FROM {$_TABLES['mg_mediaqueue']} WHERE media_id='".DB_escapeString($id)."'");
    if ( DB_numRows($result) > 0 ) {
        $row = DB_fetchArray($result);
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'   . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext) ) {
                @unlink($_MG_CONF['path_mediaobjects'] . 'tn/'   . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
                @unlink($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
                break;
            }
        }
        @unlink($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
        DB_delete($_TABLES['mg_media_album_queue'],"media_id",$id);
    }
    return;
}


function plugin_upgrade_mediagallery()
{
    global $_CONF, $_TABLES,$_MG_CONF;

    require_once $_CONF['path'].'plugins/mediagallery/upgrade.php';

    return mediagallery_upgrade();
}

function plugin_user_create_mediagallery( $uid, $force = 0 )
{
    global $_CONF, $_TABLES, $_MG_CONF, $LANG_MG01;

    require_once $_CONF['path'].'plugins/mediagallery/include/profile.inc.php';
    return _mg_user_create( $uid, $force );
}

function plugin_user_delete_mediagallery( $uid )
{
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01;

    require_once $_CONF['path'].'plugins/mediagallery/include/profile.inc.php';
    return _mg_user_delete( $uid );
}

function plugin_user_move_mediagallery($origUID, $destUID)
{
    global $_TABLES;

    $sql = "UPDATE {$_TABLES['mg_albums']} SET owner_id=".(int) $destUID . " WHERE owner_id=".(int) $origUID;
    DB_query($sql);
    $sql = "UPDATE {$_TABLES['mg_media']} SET media_user_id=".(int)$destUID. " WHERE media_user_id=".(int)$origUID;
    DB_query($sql);
}

function plugin_profileicondisplay_mediagallery( $uid )
{
    global $MG_albums,$_TABLES, $_MG_CONF, $_CONF, $_USER,$LANG_MG01;

    require_once $_CONF['path'].'plugins/mediagallery/include/profile.inc.php';
    return _mg_profileicondisplay( $uid );
}

function plugin_profileblocksdisplay_mediagallery( $uid )
{
    global $MG_albums,$_TABLES, $_MG_CONF, $_CONF, $LANG_MG10, $_USER;

    if ( $_MG_CONF['profile_hook'] != 1 ) {
        return '';
    }
    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1) {
        return '';
    }
    if ( $uid == '' ) {
        return '';
    }
    require_once $_CONF['path'].'plugins/mediagallery/include/profile.inc.php';
    return _mg_profileblocksdisplay( $uid );
}

function plugin_profileedit_mediagallery($uid,$panel,$fieldset)
{
    global $_CONF, $_MG_USERPREFS, $_MG_CONF, $_TABLES, $_USER, $LANG_MG01;

    if ( COM_isAnonUser() ) {
        return;
    }
    if ($panel != '' || $fieldset != '' ) {
        return;
    }
    if ( $_MG_CONF['up_display_rows_enabled'] == 0 &&
         $_MG_CONF['up_display_columns_enabled'] == 0 &&
         $_MG_CONF['up_mp3_player_enabled'] == 0 &&
         $_MG_CONF['up_av_playback_enabled'] == 0 &&
         $_MG_CONF['up_thumbnail_size_enabled'] == 0) {
        return;
    }
    require_once $_CONF['path'].'plugins/mediagallery/include/profile.inc.php';
    return _mg_profileedit($uid,$panel,$fieldset);
}

function plugin_profilesave_mediagallery($uid = 0)
{
    global $_CONF, $_MG_CONF, $_TABLES, $_USER;

    if ( COM_isAnonUser() ) {
        return;
    }
    if ( $_MG_CONF['up_display_rows_enabled'] == 0 &&
         $_MG_CONF['up_display_columns_enabled'] == 0 &&
         $_MG_CONF['up_mp3_player_enabled'] == 0 &&
         $_MG_CONF['up_av_playback_enabled'] == 0 &&
         $_MG_CONF['up_thumbnail_size_enabled'] == 0) {
        return;
    }
    require_once $_CONF['path'].'plugins/mediagallery/include/profile.inc.php';
    return _mg_profilesave($uid);
}


function plugin_canuserrate_mediagallery($item_id, $uid)
{
    global $MG_albums, $_TABLES, $_CONF, $_MG_CONF;

    $retval = false;

    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
        return $retval;
    }
    $result = DB_query("SELECT m.media_user_id,ma.album_id,a.enable_rating FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id LEFT JOIN {$_TABLES['mg_albums']} AS a ON ma.album_id=a.album_id WHERE m.media_id='".DB_escapeString($item_id)."'");
    if ( DB_numRows($result) > 0 ) {
        $row = DB_fetchArray($result);
        if ($row['enable_rating'] > 0 ) {
            if ($uid == $row['media_user_id'] ) {
                return false;
            }
            if ( $row['enable_rating'] == 1 && COM_isAnonUser() ) {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
    return true;
}

function plugin_itemrated_mediagallery($media_id, $rating, $votes)
{
    global $_TABLES;

    $sql = "UPDATE {$_TABLES['mg_media']} SET media_votes = $votes, media_rating = '".DB_escapeString($rating)."'
                    WHERE media_id='" . DB_escapeString($media_id) . "'";
    DB_query($sql);
    return true;
}

function plugin_subscription_email_format_mediagallery($category,$track_id,$post_id,$uid)
{
    global $_CONF, $_USER, $_MG_CONF, $_TABLES, $MG_albums, $LANG_MG01,$LANG01, $LANG03;

    require_once $_CONF['path'] . 'lib/html2text/html2text.php';
    MG_initAlbums();
    $dt = new Date('now',$_USER['tzid']);
    $permalink = 'Not defined';

    $aid = 0;

    $post_id = COM_applyFilter($post_id,true);
    $sql = "SELECT * FROM {$_TABLES['mg_media']} LEFT JOIN {$_TABLES['mg_media_albums']} ON {$_TABLES['mg_media']}.media_id={$_TABLES['mg_media_albums']}.media_id WHERE {$_TABLES['mg_media']}.media_id='".DB_escapeString($post_id)."'";

    $result = DB_query("SELECT * FROM {$_TABLES['mg_media']} LEFT JOIN {$_TABLES['mg_media_albums']} ON {$_TABLES['mg_media']}.media_id={$_TABLES['mg_media_albums']}.media_id WHERE {$_TABLES['mg_media']}.media_id='".DB_escapeString($post_id)."'");
    if ( DB_numRows($result) > 0 ) {
        $A = DB_fetchArray($result);
        $permalink = $_MG_CONF['site_url'].'/album.php?aid='.$track_id;

        if ( $A['media_user_id'] > 1 ) {
            $name = COM_getDisplayName($A['media_user_id']);
        } else {
            $name = 'Anonymous User';
        }

        $name = @htmlspecialchars($name,ENT_QUOTES, COM_getEncodingt());

        $aid = $A['album_id'];

        $A['media_title']   = COM_checkWords($A['media_title']);
        $A['media_title']   = @htmlspecialchars($A['media_title'],ENT_QUOTES, COM_getEncodingt());

        $notifymsg = sprintf($LANG03[46],'<a href="'.$_MG_CONF['site_url'].'/subscription.php?op=unsubscribe&sid='.htmlentities($track_id).'&type=mediagallery">'.$LANG01['unsubscribe'].'</a>');

        $dt->setTimestamp($A['media_time']);
        $date = $dt->format('F d Y @ h:i a');
        $T = new Template($_MG_CONF['template_path']);
        $T->set_file (array(
            'htmlemail'     => 'notifymessage_html.thtml',
            'textemail'     => 'notifymessage_text.thtml',
        ));

        if ( empty($A['media_title']) ) {
            $A['media_title'] = $LANG_MG01['no_title'];
        }
        if ( empty($A['media_desc']) ) {
            $A['media_desc'] = '';
        }

        $imageData = array();

        foreach ($_MG_CONF['validExtensions'] as $tnext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $A['media_filename'][0] . '/' . $A['media_filename'] . $tnext) ) {
                $imageData[0]['file'] = $_MG_CONF['path_mediaobjects'] . 'tn/' . $A['media_filename'][0] . '/' . $A['media_filename'] . $tnext;
                $imageData[0]['name'] = "pc-image";
                $imageData[0]['filename'] = $A['media_original_filename'];
                $imageData[0]['encoding'] = 'base64';
                $imageData[0]['mime'] = $A['mime_type'];
            }
        }

        $T->set_var(array(
            'post_subject'  => $A['media_title'],
            'post_text'     => $LANG_MG01['new_upload_body'].'<strong>'.$MG_albums[$aid]->title.'</strong>',
            'post_date'     => $date,
            'post_name'     => $name,
            'album_title'   => $MG_albums[$aid]->title,
            'media_title'   => $A['media_title'],
            'media_description' => $A['media_desc'],
            'notify_msg'    => $notifymsg,
            'site_name'     => $_CONF['site_name'],
            'online_version' => sprintf($LANG01['view_online'],$permalink),
            'permalink'     => $permalink,
        ));
        $T->parse('htmloutput','htmlemail');
        $message = $T->finish($T->get_var('htmloutput'));

        $T->parse('textoutput','textemail');
        $msgText = $T->finish($T->get_var('textoutput'));

        $html2txt = new html2text($msgText,false);

        $messageText = $html2txt->get_text();
        return array($message,$messageText,$imageData);
    }
    return false;
}

/**
* Return information for a media item
*
* @param    string  $id         media id or *
* @param    string  $what       comma-separated list of properties
* @param    int     $uid        user ID or 0 = current user
* @param    array   $options    (reserved for future extensions)
* @return   mixed               string or array of strings with the information
*
*/
function plugin_getiteminfo_mediagallery($id, $what, $uid = 0, $options = array())
{
    global $_CONF, $_TABLES, $MG_albums, $_MG_CONF, $LANG_MG00;
    MG_initAlbums();
    $properties = explode(',', $what);
    $fields = array();
    foreach ($properties as $p) {
        switch ($p) {
            case 'date-modified':
                $fields[] = 'UNIX_TIMESTAMP(media_upload_time) AS unixdate';
                break;
            case 'description':
            case 'excerpt':
            case 'raw-description' :
                $fields[] = 'media_desc';
                break;
            case 'id':
                $fields[] = 'a.media_id';
                break;
            case 'title':
                $fields[] = 'media_title';
                break;
            case 'label':
            case 'url':
            case 'status' :
                $fields[] = 'a.media_id';
                break;
            case 'author' :
                $fields[] = 'a.media_user_id';
                break;
            default:
                break;
        }
    }

    $fields = array_unique($fields);

    if (count($fields) == 0) {
        $retval = array();

        return $retval;
    }

    if ($id == '*') {
        $where = '';
        $permOp = '';
    } else {
        $where = " WHERE a.media_id = '" . DB_escapeString($id) . "'";
        $permOp = '';
    }
    $sql = "SELECT " . implode(',', $fields)
            . ",album_id FROM {$_TABLES['mg_media']} a LEFT JOIN {$_TABLES['mg_media_albums']} b ON a.media_id=b.media_id " . $where;
    if ($id != '*') {
        $sql .= ' LIMIT 1';
    }
    $result = DB_query($sql);
    $numRows = DB_numRows($result);

    $retval = array();
    for ($i = 0; $i < $numRows; $i++) {
        $A = DB_fetchArray($result);

        $aid = $A['album_id'];
        if ( isset($MG_albums[$aid]) && $MG_albums[$aid]->access > 0 ) {
            $props = array();
            foreach ($properties as $p) {
                switch ($p) {
                    case 'date-modified':
                        $props['date-modified'] = $A['unixdate'];
                        break;
                    case 'description':
                    case 'excerpt':
                        $prop[$p] = PLG_replaceTags($A['media_desc'],'mediagallery','media_description');
                        break;
                    case 'raw-description' :
                        $props[$p] = $A['media_desc'];
                        break;
                    case 'id':
                        $props['id'] = $A['media_id'];
                        break;
                    case 'title':
                        $props['title'] = $A['media_title'];
                        break;
                    case 'url':
                        if (empty($A['id'])) {
                            $props['url'] = $_MG_CONF['site_url'].'/media.php?f=0&amp;sort=0&amp;s='.$id;
                        } else {
                            $props['url'] = $_MG_CONF['site_url'].'/media.php?f=0&amp;sort=0&amp;s='.$A['media_id'];
                        }
                        break;
                    case 'label':
                        $props['label'] = $LANG_MG00['menulabel'];
                        break;
                    case 'status':
                        $props['status'] = 1; // stub - default
                        break;
                    case 'author' :
                        $props['author'] = $A['media_user_id'];
                        break;
                    default:
                        $props[$p] = '';
                        break;
                }
            }
        }

        $mapped = array();
        if ( isset($props) && is_array($props) ) {
            foreach ($props as $key => $value) {
                if ($id == '*') {
                    if ($value != '') {
                        $mapped[$key] = $value;
                    }
                } else {
                    $mapped[$key] = $value;
                }
            }
        }

        if ($id == '*') {
            $retval[] = $mapped;
        } else {
            $retval = $mapped;
            break;
        }
    }

    if (($id != '*') && (count($retval) == 1)) {
        $tRet = array_values($retval);
        $retval = $tRet[0];
    }

    return $retval;
}

function MG_clean_directories($path, $needle = '')
{
    if ( $path[strlen($path)-1] != '/' ) {
        $path .= '/';
    }
    if ($dir = @opendir($path)) {
        while ($entry = readdir($dir)) {
            if ($entry == '.' || $entry == '..' || is_link($entry)) {
                continue;
            } elseif (is_dir($path . $entry)) {
                MG_clean_directories($path . $entry, $needle);
                @rmdir($entry);
            } elseif (empty($needle) || strpos($entry, $needle) !== false) {
                @unlink($path . $entry);
            }
        }
        @closedir($dir);
    }
}

function MG_cleanup_plugin($plugin)
{
    global $TEMPLATE_OPTIONS;

    if (!empty($plugin)) {
        $plugin = '__' . $plugin . '__';
    }
    MG_clean_directories($TEMPLATE_OPTIONS['path_cache'], $plugin);
}


function MG_deleteMemberAlbums( $album_id ){
    global $_CONF, $_MG_CONF, $_TABLES, $_USER;

    $sql = "SELECT ma.media_id, m.media_filename, m.media_mime_ext
            FROM " . $_TABLES['mg_media_albums'] .
            " as ma LEFT JOIN " . $_TABLES['mg_media'] .
            " as m ON ma.media_id=m.media_id
            WHERE ma.album_id = '" . DB_escapeString($album_id) ."'";

    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );
    $mediarow = array();
    for ( $i=0; $i < $nRows; $i++) {
      $row = DB_fetchArray( $result );
      $mediarow[] = $row;
    }
    if ( count( $mediarow ) != 0 ) {
        for ( $i = 0; $i < count( $mediarow ); $i++ ) {
            $sql = "SELECT COUNT(media_id) AS count FROM " . $_TABLES['mg_media_albums'] . "  WHERE media_id = '" . DB_escapeString($mediarow[$i]['media_id']) . "'";
            $result = DB_query( $sql );
            $row = DB_fetchArray( $result );
            if ( $row['count'] <= 1 ) {
                @unlink($_MG_CONF['path_mediaobjects'] . 'tn/'   . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . '.*');
                @unlink($_MG_CONF['path_mediaobjects'] . 'disp/' . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . '.*');
                @unlink($_MG_CONF['path_mediaobjects'] . 'orig/' . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . '.' . $mediarow[$i]['media_mime_ext']);
                $sql = "DELETE FROM " . $_TABLES['mg_media'] . "  WHERE media_id = '" . DB_escapeString($mediarow[$i]['media_id']) . "'";
                DB_query( $sql );
                DB_delete($_TABLES['comments'], 'sid', DB_escapeString($mediarow[$i]['media_id']));
                DB_delete($_TABLES['mg_playback_options'],'media_id', DB_escapeString($mediarow[$i]['media_id']));
            }
        }
    }
    $sql = "DELETE FROM " . $_TABLES['mg_media_albums'] . " WHERE album_id = '" . DB_escapeString($album_id) ."'";
    DB_query( $sql );
    $sql = "DELETE FROM " . $_TABLES['mg_albums'] . " WHERE album_id = '" . DB_escapeString($album_id)."'";
    DB_query( $sql );
}



function MG_galleryOnly() {
	global $_MG_CONF, $_CONF, $_USER, $LANG_LOGIN, $_PLUGINS;

    require_once $_CONF['path'] . 'plugins/mediagallery/include/init.php';
	require_once $_CONF['path'] . 'plugins/mediagallery/include/mgindex.php';

    if (!in_array('mediagallery', $_PLUGINS)) {
	    $display = COM_siteHeader();
	    $display .= COM_startBlock('Plugin disabled');
	    $display .= '<br /><br />The Media Gallery plugin is currently disabled.';
	    $display .= COM_endBlock();
	    $display .= COM_siteFooter(true);
	    echo $display;
	    exit;
	}

    if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
        $display = MG_siteHeader();
        $display .= SEC_loginRequiredForm();
        $display .= COM_siteFooter();
        echo $display;
        exit;
    }

	MG_index();
}


function phpblock_mg_randommedia($parm1=array(),$parm2=''){
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG03, $MG_albums, $_DB_dbms, $mg_installed_version;

    $retval = '';
    $gotit = 0;

    if ( $mg_installed_version != $_MG_CONF['pi_version'] ) {
        return $retval;
    }
    require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
    MG_initAlbums();
    $randomSelect = "RAND(NOW()) ";

    $sql = "SELECT ma.album_id,m.media_user_id,m.media_id,m.media_filename,m.media_title,
            u.fullname FROM {$_TABLES['mg_albums']} as a
            LEFT JOIN {$_TABLES['mg_media_albums']} as ma
            on a.album_id=ma.album_id LEFT JOIN {$_TABLES['mg_media']} as m
            on ma.media_id=m.media_id
            LEFT JOIN {$_TABLES['users']} as u ON m.media_user_id=u.uid
            WHERE
            m.media_type=0 AND a.enable_random=1 AND a.hidden=0 "
            . COM_getPermSQL('and') . " ORDER BY " . $randomSelect . " LIMIT 1";

    $result = DB_query( $sql,1 );
    $nRows  = DB_numRows( $result );
    if ( $nRows > 0 ) {
        $row    = DB_fetchArray( $result );
        $gotit = true;
    } else {
        $gotit = false;
    }

    if ( $gotit ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/classFrame.php';
        $nFrame = new mgFrame();
        $nFrame->constructor( $_MG_CONF['random_skin'] );
        $imageFrameTemplate = $nFrame->getTemplate();
        $frWidth = $nFrame->frame['wHL'] + $nFrame->frame['wHR'];
        $frHeight = $nFrame->frame['hVT'] + $nFrame->frame['hVB'];
        $fCSS= $nFrame->getCSS();

        $F = new Template($_MG_CONF['template_path']);
        $F->set_var('media_frame',$imageFrameTemplate);


        $T = new Template( MG_getTemplatePath(0) );
        $T->set_file (array ('rblock' => 'random_block.thtml'));
        $T->set_var('site_url', $_CONF['site_url']);
        $T->set_var('site_admin_url', $_CONF['site_admin_url']);

        $url_media      = $_MG_CONF['site_url'] . '/media.php?s=' . $row['media_id'];
        $url_album      = $_MG_CONF['site_url'] . '/album.php?aid=' . $row['album_id'] .'&amp;s='.$row['media_id'].'#'.$row['media_id'];

        $msize = false;
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext) ) {
                $media_thumbnail= $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
                break;
            }
        }

        if ($msize == false ) {
            return '';
        }
        $imgwidth = $msize[0];
        $imgheight = $msize[1];
        if ( !isset($_MG_CONF['random_width']) || $_MG_CONF['random_width'] == 0 ) {
            $_MG_CONF['random_width'] = 120;
        }
        if ( $MG_albums[$row['album_id']]->tn_size == 4 ) {
            $width = $_MG_CONF['random_width'];
            $height = $width;
        } else {

            if ( $imgwidth > $imgheight ) {
                $ratio = $imgwidth / $_MG_CONF['random_width'];
                $width = $_MG_CONF['random_width'];
                $height = round($imgheight / $ratio);
            } else {
                $ratio = $imgheight / $_MG_CONF['random_width'];
                $height = $_MG_CONF['random_width'];
                $width = round($imgwidth / $ratio);
            }
        }

        if ( $parm2 == 'album' ) {
            $url_media = $url_album;
        }

        // frame template variables
        $F->set_var(array(
            'media_link_start'  => '<a href="' . $url_media . '">',
            'media_link_end'    => '</a>',
            'url_display_item'  =>  $url_media,
            'media_thumbnail'   =>  $media_thumbnail,
            'media_size'        =>  'width="' . $width . '" height="' . $height . '"',
            'media_height'      =>  $height,
            'media_width'       =>  $width,
            'border_width'      =>  $width + 15,
            'border_height'     =>  $height + 15,
            'row_height'        =>  $height + 40,
            'frWidth'           =>  $width  - $frWidth,
            'frHeight'          =>  $height - $frHeight,
            'media_tag'         =>  '',
        ));
        $F->parse('media','media_frame');
        $media_item_thumbnail = $F->finish($F->get_var('media'));

        $T->set_var(array(
            'url_media'         =>  $url_media,
            'url_album'         =>  $url_album,
            'media_thumbnail'   =>  $media_item_thumbnail,
            'media_thumbnail_img' => $media_thumbnail,
            'media_height'      =>  $height,
            'media_width'       =>  $width,
            'border_width'      =>  $width + 14,
            'media_title'       =>  PLG_replaceTags(strip_tags($row['media_title']),'mediagallery','media_title'),
            'lang_browse_album' =>  $LANG_MG03['browse_album'],
            'framecss'          =>  '',
            'owner_fullname'    =>  $row['fullname'],
        ));

        $T->parse('output', 'rblock');
        $retval .= $T->finish($T->get_var('output'));
    }

    return $retval;
}

function phpblock_mg_maenroll(){
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG03;

    if ( COM_isAnonUser() )  {
        return;
    }
    if ( $_MG_CONF['member_albums'] != 1 || $_USER['uid'] < 3 ) {
        return;
    }
    if ( !($_USER['account_type'] & LOCAL_USER) && $_MG_CONF['allow_remote'] != 1 ) {
        return;
    }
    $mg = (int) DB_getItem($_TABLES['mg_userprefs'],'member_gallery','uid='.(int) $_USER['uid']);
    if ( $mg == 1 ) {
        return;
    }
    $url = sprintf($LANG_MG03['member_album_signup'], $_MG_CONF['site_url'] . '/enroll.php');
    return $url;
}


function MG_initAlbums($force=0) {
    global $_CONF, $_TABLES, $_USER, $_GROUPS, $_MG_CONF, $MG_albums, $_MG_USERPREFS, $mg_installed_version, $ratedIds;

    if ( isset($MG_albums) && $force == 0) {
        return;
    }

    $_MG_CONF['dateformat'] = array();
    // let's load all the dataformats in memory so we do not have to keep processing it over and over...
    $result = DB_query("SELECT * FROM {$_TABLES['dateformats']}",1);
    while ($row = DB_fetchArray($result)) {
        $_MG_CONF['dateformat'][$row['dfid']] = $row['format'];
    }

    // read user prefs, if any...
    if ( !COM_isAnonUser() ) {
        $result = DB_query("SELECT * FROM " . $_TABLES['mg_userprefs'] . " WHERE uid='" . (int) $_USER['uid']."'", 1);
        $nRows  = DB_numRows($result);
        if ( $nRows > 0 ) {
            $_MG_USERPREFS = DB_fetchArray($result);
        } else {
            $_MG_USERPREFS = array();
        }
    }

    if ( $_MG_CONF['album_display_columns'] < 1 ) {
        $_MG_CONF['album_display_columns'] = 1;
    }
    if ( $_MG_CONF['album_display_rows'] < 1 ) {
        $_MG_CONF['album_display_rows'] = 9;
    }
    $ratedIds = array();
    $ratedIds = RATING_getRatedIds('mediagallery');

    $mgadmin = SEC_hasRights('mediagallery.admin');
    $root    = SEC_inGroup('Root');

    if (empty($_USER['uid']) ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $groups    = $_GROUPS;
    $sql       = "SELECT * FROM " . $_TABLES['mg_albums'] . " ORDER BY album_order DESC";
    $result    = DB_query( $sql, 1);
    $MG_albums = array();
    $album = new mgAlbum();
    $album->id = 0;
    $album->title = 'root album';
    $album->owner_id = $mgadmin;
    $album->group_id = $root;
    $album->skin     = isset($_MG_CONF['indextheme']) ? $_MG_CONF['indextheme'] : 'default';
    if ( $mgadmin ) {
        $album->access = 3;
    }
    $MG_albums[$album->id] = $album;

    while ($A = DB_fetchArray($result) ) {
        $album  = new mgAlbum();
        $album->constructor($A,$mgadmin,$root,$groups);

        /*
         * We include hidden albums in the array since they
         * can be used in auto tags which a user will have
         * access to.
         */

        if ( $album->access > 0 ) {
                $MG_albums[$album->id] = $album;
        }
    }

    foreach( $MG_albums as $id => $album) {
        if ($id != 0 && isset($MG_albums[$album->parent]->id) ) {
            $MG_albums[$album->parent]->setChild($id);
        }
    }
}

/**
* Get the path of the feed directory or a specific feed file
*
* @param    string  $feedfile   (option) feed file name
* @return   string              path of feed directory or file
*
*/
function MG_getFeedPath( $feedfile = '' )
{
    global $_CONF;

    $feedpath = $_CONF['rdf_file'];
    $pos = strrpos( $feedpath, '/' );
    $feed = substr( $feedpath, 0, $pos + 1 );
    $feed .= $feedfile;

    return $feed;
}

/**
* Get the URL of the feed directory or a specific feed file
*
* @param    string  $feedfile   (option) feed file name
* @return   string              URL of feed directory or file
*
*/
function MG_getFeedUrl( $feedfile = '' )
{
    global $_CONF;

    $feedpath = SYND_getFeedPath();
    $url = substr_replace ($feedpath, $_CONF['site_url'], 0,
                           strlen ($_CONF['path_html']) - 1);
    $url .= $feedfile;

    return $url;
}

function MG_getSortOrder( $aid, $sortOrder ) {
    global $MG_albums;

    // translate global album option to client option
    switch ( $MG_albums[$aid]->album_sort_order ) {
        case 0 :
            $album_sort_order = 1;
            break;
        case 1 :
            $album_sort_order = 5;
            break;
        case 2 :
            $album_sort_order = 4;
            break;
        case 3 :
            $album_sort_order = 3;
            break;
        case 4 :
            $album_sort_order = 2;
            break;
        case 5 :
            $album_sort_order = 10;
            break;
        case 6 :
            $album_sort_order = 11;
            break;
        default :
            $album_sort_order = 0;
            break;
    }

    if ($MG_albums[$aid]->enable_sort == 0 ) {
        $sortOrder = 0;
    }

    if ( $sortOrder == 0 ) $sortOrder = $album_sort_order;

    switch ( $sortOrder ) {
        case 0 :    // default
            $orderBy = ' ORDER BY ma.media_order DESC';
            break;
        case 1 :    // default, reverse order
            $orderBy = ' ORDER BY ma.media_order ASC';
            break;
        case 2 :    //  upload time, DESC
            $orderBy = ' ORDER BY m.media_upload_time DESC';
            break;
        case 3 :
            $orderBy = ' ORDER BY m.media_upload_time ASC';
            break;
        case 4 :    // capture time, DESC
            $orderBy = ' ORDER BY m.media_time DESC';
            break;
        case 5 :
            $orderBy = ' ORDER BY m.media_time ASC';
            break;
        case 6 :
            $orderBy = ' ORDER BY m.media_rating DESC';
            break;
        case 7 :
            $orderBy = ' ORDER BY m.media_rating ASC';
            break;
        case 8 :
            $orderBy = ' ORDER BY m.media_views DESC';
            break;
        case 9 :
            $orderBy = ' ORDER BY m.media_views ASC';
            break;
        case 10 :
            $orderBy = ' ORDER BY m.media_title DESC';
            break;
        case 11 :
            $orderBy = ' ORDER BY m.media_title ASC';
            break;
        default :
            $orderBy = ' ORDER BY ma.media_order DESC';
            break;
    }
    return $orderBy;
}


function MG_siteHeader($title='', $meta='') {
    global $_MG_CONF;

    switch( $_MG_CONF['displayblocks'] ) {
        case 0 : // left only
        case 2 :
            return( COM_siteHeader('menu',$title,$meta) );
            break;
        case 1 : // right only
        case 3 :
            return ( COM_siteHeader('none',$title,$meta) );
            break;
        default :
            return ( COM_siteHeader('menu',$title,$meta) );
            break;
    }
}

function MG_siteFooter() {
    global $_CONF, $_MG_CONF;

    $retval = '<br /><div style="text-align:center;"><a href="http://www.glfusion.org"><img src="' . MG_getImageFile('powerby_mg.png') . '" alt="" style="border:none;" /></a></div><br />';

    switch( $_MG_CONF['displayblocks'] ) {
        case 0 : // left only
        case 3 : // none
            $retval .= COM_siteFooter();
            break;
        case 1 : // right only
        case 2 : // left and right
            $retval .= COM_siteFooter( true );
            break;
        default :
            $retval .= COM_siteFooter();
            break;
    }

    // DEBUG
//    if ( function_exists('xdebug_peak_memory_usage') ) {
//        $retval .= '<br>Peak Memory: ' . (xdebug_peak_memory_usage() / 1024) / 1024 . ' mb';
//    }

    return $retval;
}
?>