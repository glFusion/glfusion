<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | init.php                                                                 |
// |                                                                          |
// | Startup and general purpose routines                                     |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

require_once $_CONF['path'].'plugins/mediagallery/include/classMedia.php';
require_once $_CONF['path'].'plugins/mediagallery/include/classFrame.php';


function MG_quotaUsage( $uid ) {
    global $_MG_CONF, $_TABLES;

    $quota = 0;
    $result = DB_query("SELECT album_disk_usage FROM {$_TABLES['mg_albums']} WHERE owner_id=" . (int) $uid);
    while ($A=DB_fetchArray($result)) {
        $quota += $A['album_disk_usage'];
    }
    return $quota;
}

function MG_getUserQuota( $uid ) {
    global $_TABLES;

    $result = DB_query("SELECT quota FROM {$_TABLES['mg_userprefs']} WHERE uid=" . (int) $uid);
    $nRows  = DB_numRows($result);
    if ( $nRows > 0 ) {
        $row = DB_fetchArray($result);
        return ($row['quota']);
    }
    return 0;
}

function MG_getUserActive( $uid ) {
    global $_TABLES;

    $result = DB_query("SELECT active FROM {$_TABLES['mg_userprefs']} WHERE uid=" . (int) $uid);
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
    $user_id  = (int) $_USER['uid'];
    $user_ip  = DB_escapeString($REMOTE_ADDR);
    $user_name = DB_escapeString($_USER['username']);

    $title  = DB_escapeString($album_title);
    $ititle = DB_escapeString($media_title);

    $sql = "INSERT INTO " . $_TABLES['mg_usage_tracking']
         . " (time,user_id,user_ip, user_name,application, album_title, media_title,media_id)"
         . " VALUES ($log_time, $user_id, '$user_ip', '$user_name', '$application', '$title', '$ititle', '$media_id')";

    DB_query( $sql );
}

function MG_errorHandler( $message ) {
    global $_CONF, $LANG_MG02;

    $retval =  '<div style="width:90%;border:1px solid;padding:8px;margin-top:8px;text-align:center;">' . LB;
    $retval .= '  <span style="text-align:center;font-weight:bold;">' . $LANG_MG02['error_header'] . '</span>' . LB;
    $retval .= '  <br />' . LB;
    $retval .= '  <br />' . LB;
    $retval .= '  <span style="text-align:center;font-weight:bold;">' . $LANG_MG02['error'] . '</span>' . LB;
    $retval .= $message . LB;
    $retval .= '  <br />' . LB;
    $retval .= '  <br />' . LB;
    $retval .= '  [ <a href=\'javascript:history.go(-1)\'>' . $LANG_MG02['go_back'] . '</a> ]' . LB;
    $retval .= '  <br />' . LB;
    $retval .= '  <br />' . LB;
    $retval .= '</div>' . LB;

    return $retval;
}

function MG_getUserDateTimeFormat($date = 'now'){
    global $_TABLES, $_CONF, $_USER, $_MG_CONF, $_SYSTEM;

    if ( $date == '99')
        return '';

    // Get display format for time
    $dfid = $_MG_CONF['dfid'];
    if ($dfid == '0'){
        $dateformat = $_CONF['date'];
    } else {
        $dateformat = $_MG_CONF['dateformat'][$dfid];
    }

    $dtObject = new Date($date,$_USER['tzid']);

    if ( empty( $date ) || $date == 'now') {
        // Date is empty, get current date/time
        $stamp = time();
    } else if ( is_numeric( $date )) {
        // This is a timestamp
        $stamp = $date;
    } else {
        // This is a string representation of a date/time
        $stamp = $dtObject->toUnix();
    }
    $date = $dtObject->format($dateformat,true);

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

    if ( $total_pages == 1 ) {
        return '';
    }

    $on_page = floor($start_item / $per_page) + 1;

    $page_string = '';

    if ( $add_prevnext_text ) {
        if ( $on_page > 1 ) {
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
//    $str = htmlentities($str, ENT_QUOTES, COM_getEncodingt());
    $str = preg_replace("/[^a-z0-9-.]/", "-", strtolower($str));
    return $str;
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

    $skin = '';
    if ( isset($MG_albums[$aid]) ) {
        $skin = $MG_albums[$aid]->skin;
    }

    if ( $aid < 0 || $skin == 'default' || $skin == '' ) {
        return array($_MG_CONF['template_path'],$_MG_CONF['template_path'].'/admin/');
    }
    return (array($path != '' ? $path : '',$_MG_CONF['template_path'] . '/themes/' . $MG_albums[$aid]->skin,$_MG_CONF['template_path'],$_MG_CONF['template_path'].'/admin/'));
}

function MG_getThemeJS( $aid ) {
    global $MG_albums, $_MG_CONF;

    $js = '';

    if ( !isset($MG_albums[$aid]) || $MG_albums[$aid]->skin == 'default' || $MG_albums[$aid]->skin == '' ) {
        return '';
    }
    if ( file_exists ($_MG_CONF['template_path'] . '/themes/' . $MG_albums[$aid]->skin . '/javascript.js') ) {
        $js = '<script src="' . $_MG_CONF['site_url'] . '/mgjs.php?theme=' . $MG_albums[$aid]->skin . '"></script>' . LB;
    }
    return ($js);
}

function MG_getThemeCSS( $aid ) {
    global $MG_albums, $_MG_CONF;

    $css = '';
    $css = MG_getThemeJS($aid);

    if ( !isset($MG_albums[$aid]) || $MG_albums[$aid]->skin == 'default' || $MG_albums[$aid]->skin == '' ) {
        return '';
    }
    if ( file_exists ($_MG_CONF['template_path'] . '/themes/' . $MG_albums[$aid]->skin . '/style.css') ) {
        $css .= '<link rel="stylesheet" type="text/css" href="' . $_MG_CONF['site_url'] . '/mgcss.php?theme=' . $MG_albums[$aid]->skin . '" />' . LB;
    }
    return ($css);
}

function MG_getThemes() {
	global $_MG_CONF, $_CONF;

    $index = 1;

    $skins = array();

    $skins[0] = 'default';

    $fd = opendir( $_MG_CONF['template_path'] . '/themes/' );

    while(( $dir = @readdir( $fd )) == TRUE ) {
        if( is_dir( $_MG_CONF['template_path'] . '/themes/' . $dir) && $dir <> '.' && $dir <> '..' && $dir <> 'CVS' && substr( $dir, 0 , 1 ) <> '.' ) {
            clearstatcache();
            $skins[$index] = $dir;
            $index++;
        }
    }

    return $skins;
}

function MG_get_size( $size )
{
    $bytes = array('B','KB','MB','GB','TB');
    foreach ( $bytes as $val ) {
        if ( $size > 1024 ) {
            $size = $size / 1024;
        } else {
            break;
        }
    }
    return round($size, 2)." ".$val;
}



/**
* Convert k/m/g size string to number of bytes
*
* @param    string      val    a string expressing size in K, M or G
* @return   int                 the resultant value in bytes
*
*/
function MG_return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        case 'g':
            $val = (int) $val * pow(1024,2);
        case 'm':
            $val = (int) $val * pow(1024,1);
        case 'k':
            $val = (int) $val * 1024;
    }
    return $val;
}

/**
* Return the max upload file size for the specified album
*
* @param    intval      album_id        the album_id to return the max upload file size for
* @return   intval      upload_limit    the upload size imit, in bytes
*
* if the type cannot be determined from the extension because the extension is
* not known, then the default value is returned (even if null)
*
* NOTE: the album array must be pre-initialized via MG_AlbumsInit()
*
*/
function MG_getUploadLimit( $album_id ) {

    global $MG_albums;

    $post_max = MG_return_bytes( ini_get( 'post_max_size' ) );
    $album_max = $MG_albums[$album_id]->max_filesize;
    if( $album_max > 0 && $album_max < $post_max ) {
        return $album_max;
    } else {
        return $post_max;
    }
}

/**
* Return a string of valid upload filetypes for the specified album
*
* @param    intval      album_id        the album_id to return the max upload file size for
* @return   string      valid_types     string of filetypes allowed, delimited by semicolons
*
* if the type cannot be determined from the extension because the extension is
* not known, then the default value is returned (even if null)
*
* NOTE: the album array must be pre-initialized via MG_AlbumsInit()
*
*/
function MG_getValidFileTypes( $album_id ) {

    global $MG_albums;

    $valid_formats = $MG_albums[$album_id]->valid_formats;
    if( $valid_formats & MG_OTHER ) {
        $valid_types = '*.*';
    } else {
        $valid_types = '';
        $valid_types .= ( $valid_formats & MG_JPG ) ? '*.jpg; ' : '';
        $valid_types .= ( $valid_formats & MG_PNG ) ? '*.png; ' : '';
        $valid_types .= ( $valid_formats & MG_TIF ) ? '*.tif; ' : '';
        $valid_types .= ( $valid_formats & MG_GIF ) ? '*.gif; ' : '';
        $valid_types .= ( $valid_formats & MG_BMP ) ? '*.bmp; ' : '';
        $valid_types .= ( $valid_formats & MG_TGA ) ? '*.tga; ' : '';
        $valid_types .= ( $valid_formats & MG_PSD ) ? '*.psd; ' : '';
        $valid_types .= ( $valid_formats & MG_MP3 ) ? '*.mp3; ' : '';
        $valid_types .= ( $valid_formats & MG_OGG ) ? '*.ogg; ' : '';
        $valid_types .= ( $valid_formats & MG_ASF ) ? '*.asf; *.wma; *.wmv; ' : '';
        $valid_types .= ( $valid_formats & MG_SWF ) ? '*.swf; ' : '';
        $valid_types .= ( $valid_formats & MG_MOV ) ? '*.mov; *.qt; ' : '';
        $valid_types .= ( $valid_formats & MG_MP4 ) ? '*.mp4; ' : '';
        $valid_types .= ( $valid_formats & MG_MPG ) ? '*.mpg; ' : '';
        $valid_types .= ( $valid_formats & MG_FLV ) ? '*.flv; ' : '';
        $valid_types .= ( $valid_formats & MG_ZIP ) ? '*.zip; ' : '';
        $valid_types .= ( $valid_formats & MG_PDF ) ? '*.pdf; ' : '';
        $valid_types = substr( $valid_types, 0, strlen($valid_types)-1 );
    }
    return $valid_types;
}

?>
