<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | common.php                                                               |
// |                                                                          |
// | Startup and general purpose routines                                     |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2009 by the following authors:                        |
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

// this file can't be used on its own
if (!defined ('GVERSION'))
{
    die ('This file can not be used on its own.');
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
define("MG_PDF"  ,65536);
define("MG_FLV"  ,131072);
define("MG_RFLV" ,262144);
define("MG_EMB"  ,524288);

// Mootools global
if (!isset($gllabsMooToolsLoaded) ) {
    $gllabsMooToolsLoaded = 0;
}

// This is a list of valid extensions for image media thumbnails / display images
$_MG_CONF['validExtensions'] = array('.jpg','.png','.gif','.bmp');

// placeholder until implemented
$_MG_CONF['ad_shopping_cart'] = 0;

$swfjsinclude = 0;
$mgLightBox = 0;
$themeStyle = '';

$_MG_CONF['random_skin'] = 'default';

// Read config data

$_MG_CONF['tmp_path'] = $_CONF['path'] . 'plugins/mediagallery/tmp/';

$result = DB_query("SELECT * FROM " . $_TABLES['mg_config'],1);
while ($row = DB_fetchArray($result)) {
    $_MG_CONF[$row['config_name']] = $row['config_value'];
}
$_MG_CONF['up_mp3_player_enabled'] = 0;

$_MG_CONF['dateformat'] = array();
// let's load all the dataformats in memory so we do not have to keep processing it over and over...
$result = DB_query("SELECT * FROM {$_TABLES['dateformats']}",1);
while ($row = DB_fetchArray($result)) {
    $_MG_CONF['dateformat'][$row['dfid']] = $row['format'];
}

// read user prefs, if any...
if ( isset($_USER['uid']) && $_USER['uid'] > 1 ) {
    $result = DB_query("SELECT * FROM " . $_TABLES['mg_userprefs'] . " WHERE uid='" . $_USER['uid']."'", 1);
    $nRows  = DB_numRows($result);
    if ( $nRows > 0 ) {
        $_MG_USERPREFS = DB_fetchArray($result);
    }
}

// safety checks....
if ( $_MG_CONF['album_display_columns'] < 1 ) {
    $_MG_CONF['album_display_columns'] = 1;
}
if ( $_MG_CONF['album_display_rows'] < 1 ) {
    $_MG_CONF['album_display_rows'] = 9;
}

$result = DB_query("SELECT pi_version FROM {$_TABLES['plugins']} WHERE pi_name='mediagallery'",1);
$row = DB_fetchArray($result,true);
$mg_installed_version = $row[0];

/*
 * Pull all rated media
 */

$ratedIds = array();
$ratedIds = RATING_getRatedIds('mediagallery');

function MG_initAlbums() {
    global $_GROUPS, $_MG_CONF, $MG_albums, $_TABLES, $_USER, $_DB_dbms;

    $mgadmin = SEC_hasRights('mediagallery.admin');
    $root    = SEC_inGroup('Root');

    if (empty($_USER['uid']) ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $groups = $_GROUPS;

    if ( $_DB_dbms == "mssql" ) {
        $sql        = "SELECT *, CAST(album_desc AS TEXT) as album_desc FROM " . $_TABLES['mg_albums'] . " ORDER BY album_order DESC";
    } else {
        $sql        = "SELECT * FROM " . $_TABLES['mg_albums'] . " ORDER BY album_order DESC";
    }
    $result     = DB_query( $sql, 1);
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

function MG_getSortOrder( $aid, $sortOrder ) {
    global $MG_albums;

    if ($MG_albums[$aid]->enable_sort == 0 ) {
        return " ORDER BY ma.media_order DESC";
    }

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

    $retval = '<br' . XHTML . '><div style="text-align:center;"><a href="http://www.glfusion.org"><img src="' . MG_getImageFile('powerby_mg.png') . '" alt="" style="border:none;"' . XHTML . '></a></div><br' . XHTML . '>';

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

function MG_quotaUsage( $uid ) {
    global $_MG_CONF, $_TABLES;

    $quota = 0;
    $result = DB_query("SELECT album_disk_usage FROM {$_TABLES['mg_albums']} WHERE owner_id=" . intval($uid));
    while ($A=DB_fetchArray($result)) {
        $quota += $A['album_disk_usage'];
    }
    return $quota;
}

function MG_getUserQuota( $uid ) {
    global $_TABLES;

    $result = DB_query("SELECT quota FROM {$_TABLES['mg_userprefs']} WHERE uid=" . intval($uid));
    $nRows  = DB_numRows($result);
    if ( $nRows > 0 ) {
        $row = DB_fetchArray($result);
        return ($row['quota']);
    }
    return 0;
}

function MG_getUserActive( $uid ) {
    global $_TABLES;

    $result = DB_query("SELECT active FROM {$_TABLES['mg_userprefs']} WHERE uid=" . intval($uid));
    $nRows  = DB_numRows($result);
    if ( $nRows > 0 ) {
        $row = DB_fetchArray($result);
        return ($row['active']);
    }
    return 0;
}


function MG_usage( $application, $album_title, $media_title, $media_id ) {
    global $_MG_CONF, $_USER, $_TABLES, $REMOTE_ADDR;

    if ( !$_MG_CONF['usage_tracking'] ) {
        return;
    }

    $now = time();
    if ( $now - $_MG_CONF['last_usage_purge'] > 5184000) {
        $purgetime = $now - 5184000; // 60 days
        DB_query("DELETE FROM {$_TABLES['mg_usage_tracking']} WHERE time < " . $purgetime);
        DB_save($_TABLES['mg_config'],'config_name,config_value',"'last_usage_purge','$now'");
        COM_errorLog("Media Gallery: Purged old data from Usage Tracking Tables");
    }

    $log_time = $now;
    $user_id  = intval($_USER['uid']);
    $user_ip  = addslashes($REMOTE_ADDR);
    $user_name = addslashes($_USER['username']);

    $title  = addslashes($album_title);
    $ititle = addslashes($media_title);

    $sql = "INSERT INTO " . $_TABLES['mg_usage_tracking'] .
            " (time,user_id,user_ip, user_name,application, album_title, media_title,media_id)
              VALUES ($log_time, $user_id, '$user_ip', '$user_name', '$application', '$title', '$ititle', '$media_id')";

    DB_query( $sql );
}

function MG_errorHandler( $message ) {
    global $_CONF, $LANG_MG02;

    $retval =  '<div style="width:90%;border:1px solid;padding:8px;margin-top:8px;text-align:center;">' . LB;
    $retval .= '  <span style="text-align:center;font-weight:bold;">' . $LANG_MG02['error_header'] . '</span>' . LB;
    $retval .= '  <br' . XHTML . '>' . LB;
    $retval .= '  <br' . XHTML . '>' . LB;
    $retval .= '  <span style="text-align:center;font-weight:bold;">' . $LANG_MG02['error'] . '</span>' . LB;
    $retval .= $message . LB;
    $retval .= '  <br' . XHTML . '>' . LB;
    $retval .= '  <br' . XHTML . '>' . LB;
    $retval .= '  [ <a href=\'javascript:history.go(-1)\'>' . $LANG_MG02['go_back'] . '</a> ]' . LB;
    $retval .= '  <br' . XHTML . '>' . LB;
    $retval .= '  <br' . XHTML . '>' . LB;
    $retval .= '</div>' . LB;

    return $retval;
}


//hacked COM_getUserDateTimeFormat to allow different format for Media Gallery

function MG_getUserDateTimeFormat($date = ''){
    global $_TABLES, $_CONF, $_MG_CONF, $_SYSTEM;

    if ( $date == '99')
        return '';

    // Get display format for time
    $dfid = $_MG_CONF['dfid'];
    if ($dfid == '0'){
        $dateformat = $_CONF['date'];
    } else {
        $dateformat = $_MG_CONF['dateformat'][$dfid];
    }
    if(empty($date)){
        // Date is empty, get current date/time
        $stamp = time();
    } elseif (is_numeric($date)){
        // This is a timestamp
        $stamp = $date;
    } else {
        // This is a string representation of a date/time
        $stamp = strtotime($date);
    }

    // Format the date
    $date = strftime($dateformat, $stamp);
    if ( $_SYSTEM['swedish_date_hack'] == true && function_exists('iconv') ) {
        $date = iconv('ISO-8859-1','UTF-8',$date);
    }

    return array( $date, $stamp );
}


// Pagination routine, generates
// page number sequence
//
function generate_pic_pagination($base_url, $num_items, $per_page, $start_item, $media,$add_prevnext_text = TRUE)
{
    global $LANG_MG03;

    $hasargs = strstr( $base_url, '?' );

    $total_pages = ceil($num_items/$per_page);

    if ( $total_pages == 1 )
    {
        return '';
    }

    $on_page = floor($start_item / $per_page) + 1;

    $page_string = '';

    if ( $add_prevnext_text )
    {
        if ( $on_page > 1 )
        {
            $offset = (( $on_page - 2) * $per_page);
            if ($hasargs ) {
                $page_string = ' <a href="' . $base_url . "&amp;s=" . ( ( $on_page - 2 ) * $per_page )  . '">' . $LANG_MG03['previous'] . '</a>&nbsp;&nbsp;';
                $page_string = ' <a href="' . $base_url . "&amp;s=" . $media[$offset]['media_id']  . '">' . $LANG_MG03['previous'] . '</a>&nbsp;&nbsp;';
            } else {
                $page_string = ' <a href="' . $base_url . "?s=" . $media[$offset]['media_id']  . '">' . $LANG_MG03['previous'] . '</a>&nbsp;&nbsp;';
            }
        }

        if ( $on_page < $total_pages )
        {
            $offset = ( $on_page * $per_page);
            if ($hasargs ) {
                $page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "&amp;s=" . $media[$offset]['media_id']  . '">' . $LANG_MG03['next'] . '</a>';
            } else {
                $page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "?s=" . $media[$offset]['media_id']  . '">' . $LANG_MG03['next'] . '</a>';
            }
        }

    }
    return $page_string;
}

function MG_genericError( $errorMessage ) {
    global $_MG_CONF, $_CONF, $LANG_MG02;

    $display .= COM_startBlock ($LANG_MG02['error_header'], '',COM_getBlockTemplate ('_admin_block', 'header'));
    $T = new Template($_MG_CONF['template_path']);
    $T->set_file('admin','error.thtml');
    $T->set_var('errormessage',$errorMessage);
    $T->parse('output', 'admin');
    $display .= $T->finish($T->get_var('output'));
    $display .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $display;
}

function MG_replace_accents($str) {
    $str = htmlentities($str, ENT_QUOTES, COM_getEncodingt());
    $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde);/','$1',$str);
    return html_entity_decode($str);
}

function MG_getImageFile($image) {
	global $_MG_CONF, $_CONF;

	if ( $_MG_CONF['template_path'] == $_CONF['path'] . 'plugins/mediagallery/templates' ) {
		return $_MG_CONF['site_url'] . '/images/' . $image;
	} else {
		return $_CONF['layout_url'] . '/mediagallery/images/' . $image;
	}
}

function MG_getImageFilePath($image) {
	global $_MG_CONF, $_CONF;

	if ( $_MG_CONF['template_path'] == $_CONF['path'] . 'plugins/mediagallery/templates' ) {
		return $_MG_CONF['path_html'] . '/images/' . $image;
	} else {
		return $styleFile = $_CONF['layout_path'] . '/mediagallery/images/' . $image;
	}
}

function MG_getTemplatePath( $aid, $path = '')
{
    global $MG_albums, $_MG_CONF, $_CONF;

    if ( $MG_albums[$aid]->skin == 'default' || $MG_albums[$aid]->skin == '' ) {
        return $_MG_CONF['template_path'];
    }
    return (array($path != '' ? $path : '',$_MG_CONF['template_path'] . '/themes/' . $MG_albums[$aid]->skin,$_MG_CONF['template_path']));
}

function MG_getThemeJS( $aid ) {
    global $MG_albums, $_MG_CONF, $glversion;

    $js = '';

    if ( $MG_albums[$aid]->skin == 'default' || $MG_albums[$aid]->skin == '' ) {
        return '';
    }
    if ( file_exists ($_MG_CONF['template_path'] . '/themes/' . $MG_albums[$aid]->skin . '/javascript.js') ) {
        $js = '<script type="text/javascript" src="' . $_MG_CONF['site_url'] . '/mgjs.php?theme=' . $MG_albums[$aid]->skin . '"></script>' . LB;
    }
    return ($js);
}

function MG_getThemeCSS( $aid ) {
    global $MG_albums, $_MG_CONF, $glversion;

    $css = '';
    $css = MG_getThemeJS($aid);

    if ( $MG_albums[$aid]->skin == 'default' || $MG_albums[$aid]->skin == '' ) {
        return '';
    }
    if ( file_exists ($_MG_CONF['template_path'] . '/themes/' . $MG_albums[$aid]->skin . '/style.css') ) {
        $css .= '<link rel="stylesheet" type="text/css" href="' . $_MG_CONF['site_url'] . '/mgcss.php?theme=' . $MG_albums[$aid]->skin . '"'.XHTML.'>' . LB;
    }
    return ($css);
}

function MG_getThemes() {
	global $_MG_CONF, $_CONF;

    $index = 1;

    $skins = array();

    $skins[0] = 'default';

    $fd = opendir( $_MG_CONF['template_path'] . '/themes/' );

    while(( $dir = @readdir( $fd )) == TRUE )
    {
        if( is_dir( $_MG_CONF['template_path'] . '/themes/' . $dir) && $dir <> '.' && $dir <> '..' && $dir <> 'CVS' && substr( $dir, 0 , 1 ) <> '.' )
        {
            clearstatcache();
            $skins[$index] = $dir;
            $index++;
        }
    }

    return $skins;
}

function MG_get_size($size) {
$bytes = array('B','KB','MB','GB','TB');
  foreach($bytes as $val) {
   if($size > 1024){
    $size = $size / 1024;
   }else{
    break;
   }
  }
  return round($size, 2)." ".$val;
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

?>