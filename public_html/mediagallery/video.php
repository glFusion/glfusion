<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Displays video in pop-up window
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';

use \glFusion\Log\Log;

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
    $display = MG_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

define('GETID3_HELPERAPPSDIR', 'C:/helperapps/');

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
MG_initAlbums();

if( empty( $LANG_CHARSET )) {
    $charset = $_CONF['default_charset'];
    if( empty( $charset )) {
        $charset = 'iso-8859-1';
    }
} else {
    $charset = $LANG_CHARSET;
}

$video_id = COM_applyFilter($_GET['n']);
$source   = isset($_GET['s']) ? COM_applyFilter($_GET['s']) : '';
if ( $source == 'q' ) {
    $mediaQueue = 1;
} else {
    $mediaQueue = 0;
}
if ($video_id == '') {
    Log::write('system',Log::ERROR,"MediaGallery: No video id passed to video.php");
    die("Invalid ID");
}

$playButtonMG = '';
$u_tn = '';

// -- get the movie info...

if ( $mediaQueue == 1 ) {
    $sql = "SELECT * FROM {$_TABLES['mg_mediaqueue']} AS m LEFT JOIN {$_TABLES['mg_media_album_queue']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . DB_escapeString($video_id) . "'";
} else {
    $sql = "SELECT * FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . DB_escapeString($video_id) . "'";
}
$result = DB_query($sql);
$nRows = DB_numRows($result);
if ( $nRows > 0 ) {
    $row = DB_fetchArray($result);

    $aid = $row['album_id'];
    if ( $MG_albums[$aid]->access == 0 ) {
        $display  = MG_siteHeader();
        $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
        $display .= MG_siteFooter();
        echo $display;
        exit;
    }

	$T = new Template( MG_getTemplatePath($aid) );
	$P = new Template( MG_getTemplatePath($aid) );

    $P->set_file('page','video_popup.thtml');

    $P->set_var('site_url',$_CONF['site_url']);
    $P->set_var('themeCSS',MG_getThemeCSS($aid));
    $P->set_var('charset',$charset);

    $meta_file_name = 	$_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
    $meta = IMG_getMediaMetaData($meta_file_name);
    if ( $meta['mime_type'] == 'video/quicktime' || $meta['mime_type'] == 'video/mp4') {
        if ( $meta['fileformat'] == 'mp4' ) {
            $row['mime_type'] = 'video/mp4';
        }
    }

    switch ( $row['mime_type'] ) {
	    case 'embed' :
            $T->set_file ('video','embed.thtml');
		    $T->set_var(array(
		        'video'             =>  'embed.thtml',
		    	'embed_string'		=>  $row['remote_url'],
		        'media_title'       =>  PLG_replaceTags($row['media_title'],'mediagallery','media_title'),
		        'media_tag'         =>  strip_tags($row['media_title']),
		    ));
		    break;

        case 'application/x-shockwave-flash' :
            $T->set_file ('video','swf.thtml');
            $T->set_var('title',$row['media_title']);
            $T->set_var(array(
                'site_url'  => $_MG_CONF['site_url'],
                'lang_noflash' => $LANG_MG03['no_flash'],
                'charset'	   => $LANG_CHARSET,
            ));
            break;
        case 'video/x-flv' :
            // Initialize the flvpopup.thtml template
    		$T->set_file('video','flvpopup.thtml');

  			if ( $row['media_title'] != '' ) {
  				$title = urlencode($row['media_title']);
  			} else {
  				$title = urlencode($row['media_original_filename']);
  			}
       		$T->set_var(array(
                'site_url'  	=> $_MG_CONF['site_url'],
                'lang_noflash'  => $LANG_MG03['no_flash'],
			));
        	break;
        case 'video/mp4' :
            $T->set_file (array ('video'=>'mp4.thtml'));
            $T->set_var('movie',$_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
            $T->set_var('title',$row['media_title']);

            $playback_options['autoref']        = $_MG_CONF['mov_autoref'];
            $playback_options['autoplay']       = $_MG_CONF['mov_autoplay'];
            $playback_options['controller']     = $_MG_CONF['mov_controller'];
            $playback_options['kioskmode']      = $_MG_CONF['mov_kioskmode'];
            $playback_options['scale']          = $_MG_CONF['mov_scale'];
            $playback_options['height']         = $_MG_CONF['mov_height'];
            $playback_options['width']          = $_MG_CONF['mov_width'];
            $playback_options['bgcolor']        = $_MG_CONF['mov_bgcolor'];

            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($row['media_id']) . "'");
            $poNumRows = DB_numRows($poResult);
            for ($i=0; $i < $poNumRows; $i++ ) {
                $poRow = DB_fetchArray($poResult);
                $playback_options[$poRow['option_name']] = $poRow['option_value'];
            }

            if ( isset($row['resolution_x']) && $row['resolution_x'] > 0 ) {
                $resolution_x = $row['resolution_x'];
                $resolution_y = $row['resolution_y'];
            } else {
                if ( $row['media_resolution_x'] == 0 ) {
                    $getID3 = new getID3;
                    // Analyze file and store returned data in $ThisFileInfo
                    $ThisFileInfo = $getID3->analyze($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
                    getid3_lib::CopyTagsToComments($ThisFileInfo);
                    if ( $ThisFileInfo['video']['resolution_x'] < 1 || $ThisFileInfo['video']['resolution_y'] < 1 ) {
                        if (isset($ThisFileInfo['meta']['onMetaData']['width']) && isset($ThisFileInfo['meta']['onMetaData']['height']) ) {
                            $resolution_x = $ThisFileInfo['meta']['onMetaData']['width'];
                            $resolution_y = $ThisFileInfo['meta']['onMetaData']['height'];
                        } else {
                            $resolution_x = -1;
                            $resolution_y = -1;
                        }
                    } else {
                        $resolution_x = $ThisFileInfo['video']['resolution_x'];
                        $resolution_y = $ThisFileInfo['video']['resolution_y'];
                    }
                    if ( $resolution_x != 0 ) {
                        $sql = "UPDATE " . $_TABLES['mg_media'] . " SET media_resolution_x=" . $resolution_x . ",media_resolution_y=" . $resolution_y . " WHERE media_id='" . DB_escapeString($row['media_id']) . "'";
                        DB_query( $sql );
                    }
                } else {
                    $resolution_x = $row['media_resolution_x'];
                    $resolution_y = $row['media_resolution_y'];
                }
            }

            $T->set_var(array(
                'autoref'       => ($playback_options['autoref'] ? 'true' : 'false'),
                'autoplay'      => ($playback_options['autoplay'] ? 'true' : 'false'),
                'controller'    => ($playback_options['controller'] ? 'true' : 'false'),
                'kioskmode'     => ($playback_options['kioskmode'] ? 'true' : 'false'),
                'bgcolor'       => $playback_options['bgcolor'],
                'scale'         => $playback_options['scale'],
                'height'        => $playback_options['height'],
                'width'         => $playback_options['width'],
                'resolution_x'  => $resolution_x,
                'resolution_y'  => $resolution_y,
                'charset'       => $LANG_CHARSET,
                'player_url'    => $_CONF['site_url'].'/javascript/addons/mediaplayer/',
                'mime_type'     => 'video/mp4',
                'artist'        => $row['artist'],
                'album'         => $row['album'],
                'title'         => $row['media_title'],
            ));
            break;

        case 'video/mpeg' :
        case 'video/x-mpeg' :
        case 'video/x-mpeq2a' :
        	if ( $_MG_CONF['use_wmp_mpeg'] == 1 ) {
	            $T->set_file (array ('video' => 'asf.thtml'));
	            $T->set_var('movie',$_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
	            $T->set_var('title',$row['media_title']);

	            $playback_options['autostart'] = $_MG_CONF['asf_autostart'];
	            $playback_options['enablecontextmenu'] = $_MG_CONF['asf_enablecontextmenu'];
	            $playback_options['stretchtofit'] = $_MG_CONF['asf_stretchtofit'];
	            $playback_options['showstatusbar'] = $_MG_CONF['asf_showstatusbar'];
	            $playback_options['uimode'] = $_MG_CONF['asf_uimode'];
	            $playback_options['height'] = $_MG_CONF['asf_height'];
	            $playback_options['width'] = $_MG_CONF['asf_width'];
	            $playback_options['bgcolor'] = $_MG_CONF['asf_bgcolor'];
	            $playback_options['playcount'] = $_MG_CONF['asf_playcount'];

	            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($row['media_id']) . "'");
	            $poNumRows = DB_numRows($poResult);
	            for ($i=0; $i < $poNumRows; $i++ ) {
	                $poRow = DB_fetchArray($poResult);
	                $playback_options[$poRow['option_name']] = $poRow['option_value'];
	            }
	            $T->set_var(array(
	                'autostart'         => $playback_options['autostart'],
	                'enablecontextmenu' => $playback_options['enablecontextmenu'],
	                'stretchtofit'      => $playback_options['stretchtofit'],
	                'showstatusbar'     => $playback_options['showstatusbar'],
	                'uimode'            => $playback_options['uimode'],
	                'height'            => $playback_options['height'],
	                'width'             => $playback_options['width'],
	                'bgcolor'           => $playback_options['bgcolor'],
	                'playcount'         => $playback_options['playcount'],
	            ));
	            break;
            }
        case 'video/x-motion-jpeg' :
        case 'video/quicktime' :
        case 'video/x-qtc' :
        case 'video/x-m4v' :
            $T->set_file (array ('video'=>'quicktime.thtml'));
            $T->set_var('movie',$_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
            $T->set_var('title',$row['media_title']);

            $playback_options['autoref']        = $_MG_CONF['mov_autoref'];
            $playback_options['autoplay']       = $_MG_CONF['mov_autoplay'];
            $playback_options['controller']     = $_MG_CONF['mov_controller'];
            $playback_options['kioskmode']      = $_MG_CONF['mov_kioskmode'];
            $playback_options['scale']          = $_MG_CONF['mov_scale'];
            $playback_options['height']         = $_MG_CONF['mov_height'];
            $playback_options['width']          = $_MG_CONF['mov_width'];
            $playback_options['bgcolor']        = $_MG_CONF['mov_bgcolor'];
            $playback_options['loop']           = $_MG_CONF['mov_loop'];

            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($row['media_id']) . "'");
            $poNumRows = DB_numRows($poResult);
            for ($i=0; $i < $poNumRows; $i++ ) {
                $poRow = DB_fetchArray($poResult);
                $playback_options[$poRow['option_name']] = $poRow['option_value'];
            }

            if ( isset($row['resolution_x']) && $row['resolution_x'] > 0 ) {
                $resolution_x = $row['resolution_x'];
                $resolution_y = $row['resolution_y'];
            } else {
                if ( $row['media_resolution_x'] == 0 ) {
                    $getID3 = new getID3;
                    // Analyze file and store returned data in $ThisFileInfo
                    $ThisFileInfo = $getID3->analyze($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
                    getid3_lib::CopyTagsToComments($ThisFileInfo);
                    if ( $ThisFileInfo['video']['resolution_x'] < 1 || $ThisFileInfo['video']['resolution_y'] < 1 ) {
                        if (isset($ThisFileInfo['meta']['onMetaData']['width']) && isset($ThisFileInfo['meta']['onMetaData']['height']) ) {
                            $resolution_x = $ThisFileInfo['meta']['onMetaData']['width'];
                            $resolution_y = $ThisFileInfo['meta']['onMetaData']['height'];
                        } else {
                            $resolution_x = -1;
                            $resolution_y = -1;
                        }
                    } else {
                        $resolution_x = $ThisFileInfo['video']['resolution_x'];
                        $resolution_y = $ThisFileInfo['video']['resolution_y'];
                    }
                    if ( $resolution_x != 0 ) {
                        $sql = "UPDATE " . $_TABLES['mg_media'] . " SET media_resolution_x=" . $resolution_x . ",media_resolution_y=" . $resolution_y . " WHERE media_id='" . DB_escapeString($row['media_id']) . "'";
                        DB_query( $sql );
                    }
                } else {
                    $resolution_x = $row['media_resolution_x'];
                    $resolution_y = $row['media_resolution_y'];
                }
            }

            $T->set_var(array(
                'autoref'       => ($playback_options['autoref'] ? 'true' : 'false'),
                'autoplay'      => ($playback_options['autoplay'] ? 'true' : 'false'),
                'controller'    => ($playback_options['controller'] ? 'true' : 'false'),
                'kioskmode'     => ($playback_options['kioskmode'] ? 'true' : 'false'),
                'bgcolor'       => $playback_options['bgcolor'],
                'scale'         => $playback_options['scale'],
                'height'        => $playback_options['height'],
                'width'         => $playback_options['width'],
                'resolution_x'  => $resolution_x,
                'resolution_y'  => $resolution_y,
                'charset'       => $LANG_CHARSET,
                'filename'      => $row['media_original_filename'],
                'loop'          => ($playback_options['loop'] ? 'true' : 'false'),
            ));

            break;
        case 'audio/x-ms-wma' :
        case 'audio/x-ms-wax' :
        case 'audio/x-ms-wmv' :
        case 'video/x-ms-asf' :
        case 'video/x-ms-asf-plugin' :
        case 'video/avi' :
        case 'video/msvideo' :
        case 'video/x-msvideo' :
        case 'video/avs-video' :
        case 'video/x-ms-wmv' :
        case 'video/x-ms-wvx' :
        case 'video/x-ms-wm' :
        case 'application/x-troff-msvideo' :
        case 'application/x-ms-wmz' :
        case 'application/x-ms-wmd' :
            $T->set_file (array ('video' => 'asf.thtml'));
            $T->set_var('movie',$_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
            $T->set_var('title',$row['media_title']);

			switch ( $row['mime_type'] ) {
		        case 'audio/x-ms-wma' :
		        case 'audio/x-ms-wax' :
		        case 'audio/x-ms-wmv' :
		            $playback_options['autostart']          = ($_MG_CONF['mp3_autostart'] ? 'true' : 'false');
		            $playback_options['enablecontextmenu']  = ($_MG_CONF['mp3_enablecontextmenu'] ? 'true' : 'false');
		            $playback_options['stretchtofit']       = $_MG_CONF['mp3_stretchtofit'];
		            $playback_options['showstatusbar']      = $_MG_CONF['mp3_showstatusbar'];
		            $playback_options['uimode']             = $_MG_CONF['mp3_uimode'];
		            $playback_options['height']             = $_MG_CONF['mp3_height'];
		            $playback_options['width']              = $_MG_CONF['mp3_width'];
		            $playback_options['loop']               = $_MG_CONF['mp3_loop'];
		            break;
		        default:
		            $playback_options['autostart'] = $_MG_CONF['asf_autostart'];
		            $playback_options['enablecontextmenu'] = $_MG_CONF['asf_enablecontextmenu'];
		            $playback_options['stretchtofit'] = $_MG_CONF['asf_stretchtofit'];
		            $playback_options['showstatusbar'] = $_MG_CONF['asf_showstatusbar'];
		            $playback_options['uimode'] = $_MG_CONF['asf_uimode'];
		            $playback_options['height'] = $_MG_CONF['asf_height'];
		            $playback_options['width'] = $_MG_CONF['asf_width'];
		            $playback_options['bgcolor'] = $_MG_CONF['asf_bgcolor'];
		            $playback_options['playcount'] = $_MG_CONF['asf_playcount'];
	        }

            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($row['media_id']) . "'");
            $poNumRows = DB_numRows($poResult);
            for ($i=0; $i < $poNumRows; $i++ ) {
                $poRow = DB_fetchArray($poResult);
                $playback_options[$poRow['option_name']] = $poRow['option_value'];
            }
            if ( !isset($playback_options['width']) || $playback_options['width'] == '' ) {
                $playback_options['width'] = 320;
            }
            if ( !isset($playback_options['height']) || $playback_options['height'] == '' ) {
                $playback_options['height'] = 240;
            }
            if ( !isset($playback_options['playcount']) || $playback_options['playcount'] == '' ) {
                $playback_options['playcount'] = 1;
            }
            $T->set_var(array(
                'autostart'         => $playback_options['autostart'],
                'enablecontextmenu' => $playback_options['enablecontextmenu'],
                'stretchtofit'      => $playback_options['stretchtofit'],
                'showstatusbar'     => $playback_options['showstatusbar'],
                'uimode'            => $playback_options['uimode'],
                'height'            => $playback_options['height'],
                'width'             => $playback_options['width'],
                'bgcolor'           => $playback_options['bgcolor'],
                'playcount'         => $playback_options['playcount'],
            ));
            break;
        case 'mp3' :
        case 'audio/mpeg' :
            $tfile = 'mp4.thtml';

            $T->set_file (array ('video' => $tfile));
            $T->set_var('movie',$_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
            $T->set_var('title',$row['media_title']);

            $playback_options['autostart']          = ($_MG_CONF['mp3_autostart'] ? 'true' : 'false');
            $playback_options['enablecontextmenu']  = ($_MG_CONF['mp3_enablecontextmenu'] ? 'true' : 'false');
            $playback_options['stretchtofit']       = isset($_MG_CONF['mp3_stretchtofit']) ? $_MG_CONF['mp3_stretchtofit'] : 'false';
            $playback_options['showstatusbar']      = $_MG_CONF['mp3_showstatusbar'];
            $playback_options['uimode']             = $_MG_CONF['mp3_uimode'];
            $playback_options['height']             = isset($_MG_CONF['mp3_height']) ? $_MG_CONF['mp3_height'] : 0;
            $playback_options['width']              = isset($_MG_CONF['mp3_width']) ? $_MG_CONF['mp3_width'] : 0;
            $playback_options['loop']               = $_MG_CONF['mp3_loop'];

            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($row['media_id']) . "'");
            $poNumRows = DB_numRows($poResult);
            for ($i=0; $i < $poNumRows; $i++ ) {
                $poRow = DB_fetchArray($poResult);
                $playback_options[$poRow['option_name']] = $poRow['option_value'];
            }
            if ( $row['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                        $u_tn = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext);
                        break;
                    }
                }
                if ( !isset($media_size_orig) || $media_size_orig === false ) {
                    $u_pic = $u_tn = $_MG_CONF['assets_url'] . '/placeholder_audio.svg';
                    $playback_options['height'] = 200;
                    $playback_options['width'] = 300;
                } else {
                    $border_width = $media_size_disp[0] + 15;
                    $u_pic = '<div class=out style="width:' . $border_width . 'px"><div class="in ltin tpin"><img src="' . $u_tn . '"></div></div>';
                    $playback_options['height'] = $media_size_disp[1] + 40;
                    $playback_options['width']  = 300;
                }
            } else {
                $u_pic = $u_tn = $_MG_CONF['assets_url'] . '/placeholder_audio.svg';
                $playback_options['height'] = 200;
                $playback_options['width'] = 300;
            }
            $T->set_var(array(
                'u_tn'              => $u_tn,
                'thumbnail'         => $u_tn,
                'u_pic'             => $u_pic,
                'autostart'         => $playback_options['autostart'],
                'enablecontextmenu' => $playback_options['enablecontextmenu'],
                'stretchtofit'      => $playback_options['stretchtofit'],
                'showstatusbar'     => $playback_options['showstatusbar'],
                'uimode'            => $playback_options['uimode'],
                'height'            => $playback_options['height'], // 365, //
                'width'             => $playback_options['width'], // 600, //
                'loop'              => ($playback_options['loop'] ? 'true' : 'false'),
                'playcount'         => ($playback_options['loop'] ? '9999' : '1'),
                'site_url'          => $_MG_CONF['site_url'],
                'id'                => $row['media_mime_ext'] . rand(),
                'allow_download'    => ($MG_albums[$aid]->allow_download ? 'true' : 'false'),
                'lang_artist'       => $LANG_MG03['artist'],
                'lang_album'        => $LANG_MG03['album'],
                'lang_song'         => $LANG_MG03['song'],
                'lang_track'        => $LANG_MG03['track'],
                'lang_genre'        => $LANG_MG03['genre'],
                'lang_year'         => $LANG_MG03['year'],
                'lang_download'     => $LANG_MG03['download'],
                'lang_info'         => $LANG_MG03['info'],
                'lang_noflash'      => $LANG_MG03['no_flash'],
                'charset'           => $LANG_CHARSET,
                'title'             => $row['media_title'],
                'artist'            => $row['artist'],
                'album'             => $row['album'],
            ));
            break;
        default :
            Log::write('system',Log::ERROR,"Media Gallery - Unknown video filetype found");
            die($row['mime_type'] . "Invalid Media Format");
            break;
    }

    if (!SEC_hasRights('mediagallery.admin')) {
        $media_views = $row['media_views'] + 1;
        DB_query("UPDATE " . $_TABLES['mg_media'] . " SET media_views=" . $media_views . " WHERE media_id='" . DB_escapeString($row['media_id']) . "'");
    }

    $T->parse('output','video');
    $video_player = $T->finish($T->get_var('output'));

    list ( $jsfile, $jsurl) = COM_getJSCacheLocation();
    list ( $cssfile, $cssurl) = COM_getStyleCacheLocation();
    $P->set_var(array(
        'css_url'       => $cssurl,
        'js_url'        => $jsurl,
    ));

    $P->set_var('video_player',$video_player);

    $P->parse('output','page');
    $display = $P->finish($P->get_var('output'));
    echo $display;
}
?>