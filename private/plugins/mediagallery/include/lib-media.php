<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* General purpose media display / manipulation interface
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

define('GETID3_HELPERAPPSDIR', 'C:/helperapps/');

/*
 * Generate the prev and next links for media browsing.
 */

function MG_getNextandPrev($base_url, $num_items, $per_page, $start_item, $media,$add_prevnext_text = TRUE)
{
    global $LANG_MG03;

    $nextAndprev = array();
    $prev_string = '';
    $next_string = '';

    $hasargs = strstr( $base_url, '?' );

    $total_pages = ceil($num_items/$per_page);

    if ( $total_pages == 1 ) {
        return array('','');
    }

    $on_page = floor($start_item / $per_page) + 1;

    if ( $add_prevnext_text ) {
        if ( $on_page > 1 ) {
            $offset = (( $on_page - 2) * $per_page);
            if ($hasargs ) {
                $prev_string = $base_url . "&amp;s=" . $media[$offset]['media_id'];
            } else {
                $prev_string = $base_url . "?s=" . $media[$offset]['media_id'];
            }
        }

        if ( $on_page < $total_pages ) {
            $offset = ( $on_page * $per_page);
            if ($hasargs ) {
                $next_string = $base_url . "&amp;s=" . $media[$offset]['media_id'];
            } else {
                $next_string = $base_url . "?s=" . $media[$offset]['media_id'];
            }
        }

    }
    return array($prev_string,$next_string);
}


function MG_displayASF( $aid, $I, $full ) {
    global $_TABLES, $_CONF, $_MG_CONF, $_MG_USERPREFS, $MG_albums;

    $retval = '';

    // set the default playback options...

    $playback_options['autostart']          = $_MG_CONF['asf_autostart'];
    $playback_options['enablecontextmenu']  = $_MG_CONF['asf_enablecontextmenu'];
    $playback_options['stretchtofit']       = $_MG_CONF['asf_stretchtofit'];
    $playback_options['showstatusbar']      = $_MG_CONF['asf_showstatusbar'];
    $playback_options['uimode']             = $_MG_CONF['asf_uimode'];
    $playback_options['height']             = $_MG_CONF['asf_height'];
    $playback_options['width']              = $_MG_CONF['asf_width'];
    $playback_options['bgcolor']            = $_MG_CONF['asf_bgcolor'];
    $playback_options['playcount']          = $_MG_CONF['asf_playcount'];

    $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($I['media_id']) . "'");
    while ($poRow = DB_fetchArray($poResult)) {
        $playback_options[$poRow['option_name']] = $poRow['option_value'];
    }

    if (isset($_MG_USERPREFS['playback_mode']) && $_MG_USERPREFS['playback_mode'] != -1 ) {
        $playback_type = $_MG_USERPREFS['playback_mode'];
    } else {
        $playback_type = $MG_albums[$aid]->playback_type;
    }
    if ( isset($I['media_resolution_x']) && $I['media_resolution_x'] > 0 ) {
        $resolution_x = $I['media_resolution_x'];
        $resolution_y = $I['media_resolution_y'];
    } else {
        if ( $I['media_resolution_x'] == 0 ) {
            $getID3 = new getID3;
            // Analyze file and store returned data in $ThisFileInfo
            $ThisFileInfo = $getID3->analyze($_MG_CONF['path_mediaobjects'] . 'orig/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext']);
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
                $sql = "UPDATE " . $_TABLES['mg_media'] . " SET media_resolution_x=" . intval($resolution_x) . ",media_resolution_y=" . intval($resolution_y) . " WHERE media_id='" . DB_escapeString($I['media_id']) . "'";
                DB_query( $sql );
            }
        } else {
            $resolution_x = $I['media_resolution_x'];
            $resolution_y = $I['media_resolution_y'];
        }
    }
    $raw_link_url = '';
    switch ($playback_type) {
        case 0 :                    // Popup Window
            $win_width = $playback_options['width'] + 40;
            $win_height = $playback_options['height'] + 40;
            $u_pic = "javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $I['media_id'] . "'," . $win_height . "," . $win_width . ")";
            $raw_link_url = "javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $I['media_id'] . "'," . $win_height . "," . $win_width . ")";
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                if ( $MG_albums[$aid]->tnWidth > $MG_albums[$aid]->tnHeight ) {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video_w.svg';
                } else {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video.svg';
                }
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight);
            }
            break;
        case 1: // download
            $u_pic = $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'];
            $raw_link_url = $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'];
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                if ( $MG_albums[$aid]->tnWidth > $MG_albums[$aid]->tnHeight ) {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video_w.svg';
                } else {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video.svg';
                }
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight);
            }
            break;
        case 2 :    // inline
            $V = new Template( MG_getTemplatePath($aid) );
            $V->set_file (array ('video' => 'view_asf.thtml'));
            $V->set_var(array(
                'autostart'         => ($playback_options['autostart'] ? 'true' : 'false'),
                'enablecontextmenu' => ($playback_options['enablecontextmenu'] ? 'true' : 'false'),
                'stretchtofit'      => ($playback_options['stretchtofit'] ? 'true' : 'false'),
                'showstatusbar'     => ($playback_options['showstatusbar'] ? 'true' : 'false'),
                'uimode'            => $playback_options['uimode'],
                'playcount'         => $playback_options['playcount'],
                'height'            => $playback_options['height'] + 45,
                'width'             => $playback_options['width'],
                'bgcolor'           => $playback_options['bgcolor'],
                'movie'             => $_MG_CONF['mediaobjects_url'] . '/orig/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext'],
                'autostart0'         => ($playback_options['autostart'] ? '1' : '0'),
                'enablecontextmenu0' => ($playback_options['enablecontextmenu'] ? '1' : '0'),
                'stretchtofit0'      => ($playback_options['stretchtofit'] ? '1' : '0'),
                'showstatusbar0'     => ($playback_options['showstatusbar'] ? '1' : '0'),
            ));
            switch ($playback_options['uimode'] ) {
                case 'mini' :
                case 'full' :
                    $V->set_var(array(
                        'showcontrols'         => 'true',
                        'showcontrols0'         => '1',
                    ));
                    break;
                case 'none' :
                    $V->set_var(array(
                        'showcontrols'         => 'false',
                        'showcontrols0'        => '0',
                    ));
                    break;
            }
            $V->parse('output','video');
            $u_image = $V->finish($V->get_var('output'));
            return array($u_image,'',$resolution_x,$resolution_y,'');
            break;
        case 3: // use mms links
            $mms_path = preg_replace("/http/i",'mms',$_MG_CONF['mediaobjects_url']);
            $u_pic = $mms_path . '/orig/'.  $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext'];
            $raw_link_url = $mms_path . '/orig/'.  $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext'];
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                if ( $MG_albums[$aid]->tnWidth > $MG_albums[$aid]->tnHeight ) {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video_w.svg';
                } else {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video.svg';
                }
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight);
            }
            break;
    }

    $imageWidth  = $media_size_disp[0];
    $imageHeight = $media_size_disp[1];

    //frame
    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$MG_albums[$aid]->displayFrameTemplate);
    $F->set_var(array(
        'media_link_start'  => '<a href="' . $u_pic . '">',
        'media_link_end'    => '</a>',
        'url_media_item'    =>  $u_pic,
        'media_thumbnail'   =>  $u_image,
        'media_size'        =>  'width="' . $imageWidth . '" height="' . $imageHeight . '"',
        'media_height'      =>  $imageHeight,
        'media_width'       =>  $imageWidth,
        'border_width'      =>  $imageWidth + 15,
        'border_height'     =>  $imageHeight + 15,
        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
        'frWidth'           =>  $imageWidth  - $MG_albums[$aid]->dfrWidth,
        'frHeight'          =>  $imageHeight - $MG_albums[$aid]->dfrHeight,
    ));
    $F->parse('media','media_frame');
    $retval .= $F->finish($F->get_var('media'));
    return array($retval,$u_image,$imageWidth,$imageHeight,$raw_link_url);
}

function MG_displayMOV( $aid, $I, $full ) {
    global $_TABLES, $_CONF, $_MG_CONF, $_MG_USERPREFS, $MG_albums, $LANG_MG03;

    $retval = '';

    // set the default playback options...
    $playback_options['autoref']        = $_MG_CONF['mov_autoref'];
    $playback_options['autoplay']       = $_MG_CONF['mov_autoplay'];
    $playback_options['controller']     = $_MG_CONF['mov_controller'];
    $playback_options['kioskmode']      = $_MG_CONF['mov_kioskmode'];
    $playback_options['scale']          = $_MG_CONF['mov_scale'];
    $playback_options['loop']           = $_MG_CONF['mov_loop'];
    $playback_options['height']         = $_MG_CONF['mov_height'];
    $playback_options['width']          = $_MG_CONF['mov_width'];
    $playback_options['bgcolor']        = $_MG_CONF['mov_bgcolor'];

    $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($I['media_id']) . "'");
    while ( $poRow = DB_fetchArray($poResult) ) {
        $playback_options[$poRow['option_name']] = $poRow['option_value'];
    }
    if (isset($_MG_USERPREFS['playback_mode']) && $_MG_USERPREFS['playback_mode'] != -1 ) {
        $playback_type = $_MG_USERPREFS['playback_mode'];
    } else {
        $playback_type = $MG_albums[$aid]->playback_type;
    }

    if ( isset($I['resolution_x']) && $I['resolution_x'] > 0 ) {
        $resolution_x = $I['resolution_x'];
        $resolution_y = $I['resolution_y'];
    } else {
        if ( $I['media_resolution_x'] == 0 ) {
            $getID3 = new getID3;
            // Analyze file and store returned data in $ThisFileInfo
            $ThisFileInfo = $getID3->analyze($_MG_CONF['path_mediaobjects'] . 'orig/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext']);
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
                $sql = "UPDATE " . $_TABLES['mg_media'] . " SET media_resolution_x=" . intval($resolution_x) . ",media_resolution_y=" . intval($resolution_y) . " WHERE media_id='" . DB_escapeString($I['media_id']) . "'";
                DB_query( $sql );
            }
        } else {
            $resolution_x = $I['media_resolution_x'];
            $resolution_y = $I['media_resolution_y'];
        }
    }

    switch ($playback_type) {
        case 0 :                    // Popup Window
            $win_width = $playback_options['width'] + 40;
            $win_height = $playback_options['height'] + 40;
            $u_pic = "javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $I['media_id'] . "'," . $win_height . "," . $win_width . ")";
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                if ( $MG_albums[$aid]->tnWidth > $MG_albums[$aid]->tnHeight ) {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video_w.svg';
                } else {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video.svg';
                }
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight);
            }
            break;
        case 1: // download
        case 3: // use mms links
            $u_pic = $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'];
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                if ( $MG_albums[$aid]->tnWidth > $MG_albums[$aid]->tnHeight ) {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video_w.svg';
                } else {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video.svg';
                }
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight);
            }
            break;
        case 2 :    // inline
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                if ( $MG_albums[$aid]->tnWidth > $MG_albums[$aid]->tnHeight ) {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video_w.svg';
                } else {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video.svg';
                }
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight);
            }

            $V = new Template( MG_getTemplatePath($aid) );
            $V->set_file (array ('video' => 'view_quicktime.thtml'));
            $V->set_var(array(
                'site_url'      => $_MG_CONF['site_url'],
                'autoref'       => ($playback_options['autoref'] ? 'true' : 'false'),
                'autoplay'      => ($playback_options['autoplay'] ? 'true' : 'false'),
                'controller'    => ($playback_options['controller'] ? 'true' : 'false'),
                'kioskmode'     => ($playback_options['kioskmode'] ? 'true' : 'false'),
                'loop'          => ($playback_options['loop'] ? 'true' : 'false'),
                'scale'         => $playback_options['scale'],
                'height'        => $playback_options['height'] + ($playback_options['controller'] ? 20 : 0),
                'width'         => $playback_options['width'],
                'bgcolor'       => $playback_options['bgcolor'],
                'movie'         => $_MG_CONF['mediaobjects_url'] . '/orig/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext'],
                'filename'      => $I['media_original_filename'],
                'lang_noquicktime' => $LANG_MG03['no_quicktime'],
                'thumbnail'     => $u_image,
                'mime_type'     => $I['mime_type'],
            ));
            $V->parse('output','video');
            $u_image = $V->finish($V->get_var('output'));
            return array($u_image,'',$resolution_x,$resolution_y,'');
            break;
    }

    $imageWidth  = $media_size_disp[0];
    $imageHeight = $media_size_disp[1];

    //frame
    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$MG_albums[$aid]->displayFrameTemplate);
    $F->set_var(array(
        'media_link_start'  =>  '<a href="' . $u_pic . '">',
        'media_link_end'    =>  '</a>',
        'url_media_item'    =>  $u_pic,
        'media_thumbnail'   =>  $u_image,
        'media_size'        =>  'width="' . $imageWidth . '" height="' . $imageHeight . '"',
        'media_height'      =>  $imageHeight,
        'media_width'       =>  $imageWidth,
        'border_width'      =>  $imageWidth + 15,
        'border_height'     =>  $imageHeight + 15,
        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
        'frWidth'           =>  $imageWidth  - $MG_albums[$aid]->dfrWidth,
        'frHeight'          =>  $imageHeight - $MG_albums[$aid]->dfrHeight,
    ));
    $F->parse('media','media_frame');
    $retval .= $F->finish($F->get_var('media'));
    return array($retval,$u_image,$imageWidth,$imageHeight,$u_pic);
}

function MG_displayMP4( $aid, $I, $full ) {
    global $_TABLES, $_CONF, $_MG_CONF, $_MG_USERPREFS, $MG_albums, $LANG_MG03;

    $retval = '';

    // set the default playback options...
    $playback_options['autoref']        = $_MG_CONF['mov_autoref'];
    $playback_options['autoplay']       = $_MG_CONF['mov_autoplay'];
    $playback_options['controller']     = $_MG_CONF['mov_controller'];
    $playback_options['kioskmode']      = $_MG_CONF['mov_kioskmode'];
    $playback_options['scale']          = $_MG_CONF['mov_scale'];
    $playback_options['loop']           = $_MG_CONF['mov_loop'];
    $playback_options['height']         = $_MG_CONF['mov_height'];
    $playback_options['width']          = $_MG_CONF['mov_width'];
    $playback_options['bgcolor']        = $_MG_CONF['mov_bgcolor'];

    $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($I['media_id']) . "'");
    while ( $poRow = DB_fetchArray($poResult) ) {
        $playback_options[$poRow['option_name']] = $poRow['option_value'];
    }
    if (isset($_MG_USERPREFS['playback_mode']) && $_MG_USERPREFS['playback_mode'] != -1 ) {
        $playback_type = $_MG_USERPREFS['playback_mode'];
    } else {
        $playback_type = $MG_albums[$aid]->playback_type;
    }

    if ( isset($I['resolution_x']) && $I['resolution_x'] > 0 ) {
        $resolution_x = $I['resolution_x'];
        $resolution_y = $I['resolution_y'];
    } else {
        if ( $I['media_resolution_x'] == 0 ) {
            $getID3 = new getID3;
            // Analyze file and store returned data in $ThisFileInfo
            $ThisFileInfo = $getID3->analyze($_MG_CONF['path_mediaobjects'] . 'orig/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext']);
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
                $sql = "UPDATE " . $_TABLES['mg_media'] . " SET media_resolution_x=" . intval($resolution_x) . ",media_resolution_y=" . intval($resolution_y) . " WHERE media_id='" . DB_escapeString($I['media_id']) . "'";
                DB_query( $sql );
            }
        } else {
            $resolution_x = $I['media_resolution_x'];
            $resolution_y = $I['media_resolution_y'];
        }
    }

    switch ($playback_type) {
        case 0 :                    // Popup Window
            $win_width = $playback_options['width'] + 40;
            $win_height = $playback_options['height'] + 40;
            $u_pic = "javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $I['media_id'] . "'," . $win_height . "," . $win_width . ")";
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                if ( $MG_albums[$aid]->tnWidth > $MG_albums[$aid]->tnHeight ) {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video_w.svg';
                } else {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video.svg';
                }
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight);
            }
            break;
        case 1: // download
        case 3: // use mms links
            $u_pic = $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'];
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                if ( $MG_albums[$aid]->tnWidth > $MG_albums[$aid]->tnHeight ) {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video_w.svg';
                } else {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video.svg';
                }
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight); //@getimagesize($_MG_CONF['path_mediaobjects'] . 'placeholder_audio.svg');
            }
            break;
        case 2 :    // inline
            if ( $I['media_tn_attached'] == 1 ) {
                $foundTN = 0;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'orig/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/orig/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        $foundTN = 1;
                        break;
                    }
                }
                if ( $foundTN == 0 ) {
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                            $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                            $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                            $foundTN = 1;
                            break;
                        }
                    }
                }
            } else {
/* ---
                if ( $MG_albums[$aid]->tnWidth > $MG_albums[$aid]->tnHeight ) {
                    $u_image     = $_MG_CONF['mediaobjects_url'] . '/placeholder_video_w.svg';
                } else {
                    $u_image     = $_MG_CONF['mediaobjects_url'] . '/placeholder_video.svg';
                }
 --- */
                $u_image = '';
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight); //@getimagesize($_MG_CONF['path_mediaobjects'] . 'placeholder_audio.svg');
            }

            $V = new Template( MG_getTemplatePath($aid) );
            $V->set_file (array ('video' => 'view_mp4.thtml'));
            $V->set_var(array(
                'site_url'      => $_MG_CONF['site_url'],
                'autoref'       => ($playback_options['autoref'] ? 'true' : 'false'),
                'autoplay'      => ($playback_options['autoplay'] ? 'true' : 'false'),
                'autoplay_text' => ($playback_options['autoplay'] ? ' autoplay ' : ''),
                'controller'    => ($playback_options['controller'] ? 'true' : 'false'),
                'kioskmode'     => ($playback_options['kioskmode'] ? 'true' : 'false'),
                'loop'          => ($playback_options['loop'] ? 'true' : 'false'),
                'scale'         => $playback_options['scale'],
                'height'        => $playback_options['height'] + ($playback_options['controller'] ? 20 : 0),
                'width'         => $playback_options['width'],
                'bgcolor'       => $playback_options['bgcolor'],
                'movie'         => $_MG_CONF['mediaobjects_url'] . '/orig/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext'],
                'filename'      => $I['media_original_filename'],
                'lang_noquicktime' => $LANG_MG03['no_quicktime'],
                'thumbnail'     => $u_image,
                'mime_type'     => $I['mime_type'],
//                'player_url'    => $_CONF['site_url'].'/javascript/addons/mediaplayer/',
            ));
            $V->parse('output','video');
            $u_image = $V->finish($V->get_var('output'));
            return array($u_image,'',$resolution_x,$resolution_y,'');
            break;
    }

    $imageWidth  = $media_size_disp[0];
    $imageHeight = $media_size_disp[1];

    //frame
    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$MG_albums[$aid]->displayFrameTemplate);
    $F->set_var(array(
        'media_link_start'  =>  '<a href="' . $u_pic . '">',
        'media_link_end'    =>  '</a>',
        'url_media_item'    =>  $u_pic,
        'media_thumbnail'   =>  $u_image,
        'media_size'        =>  'width="' . $imageWidth . '" height="' . $imageHeight . '"',
        'media_height'      =>  $imageHeight,
        'media_width'       =>  $imageWidth,
        'border_width'      =>  $imageWidth + 15,
        'border_height'     =>  $imageHeight + 15,
        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
        'frWidth'           =>  $imageWidth  - $MG_albums[$aid]->dfrWidth,
        'frHeight'          =>  $imageHeight - $MG_albums[$aid]->dfrHeight,
    ));
    $F->parse('media','media_frame');
    $retval .= $F->finish($F->get_var('media'));
    return array($retval,$u_image,$imageWidth,$imageHeight,$u_pic);
}

function MG_displaySWF( $aid, $I, $full ) {
    global $_TABLES, $_CONF, $_MG_CONF, $_MG_USERPREFS, $MG_albums, $LANG_MG03;

    $retval = '';

    $u_image = '';
    $V = new Template( MG_getTemplatePath($aid) );
    $V->set_file (array ('video' => 'view_swf.thtml'));
    $V->set_var(array(
        'site_url'  => $_MG_CONF['site_url'],
        'lang_noflash' => $LANG_MG03['no_flash'],
    ));

    $V->parse('output','video');

    $u_image .= $V->finish($V->get_var('output'));
    return array($u_image,'',250,250,'');
}


function MG_displayFLV ( $aid, $I, $full ) {
    global $_TABLES, $_CONF, $_MG_CONF, $_MG_USERPREFS, $MG_albums, $LANG_MG03;

    $retval = '';

    $u_image = '';
    // Initialize the flvpopup.thtml template

    $V = new Template( MG_getTemplatePath($aid) );
    $V->set_file('video','view_flv.thtml');
    $V->set_var(array(
        'site_url'  	=> $_MG_CONF['site_url'],
        'lang_noflash'  => $LANG_MG03['no_flash'],
    ));
    $V->parse('output','video');
    $u_image .= $V->finish($V->get_var('output'));
    return array($u_image,'',250,250,'');
}

function MG_displayMP3( $aid, $I, $full ) {
    global $_TABLES, $_CONF, $_MG_CONF, $_MG_USERPREFS, $MG_albums, $LANG_MG03;

    $retval = '';

    // set the default playback options...

    $playback_options['autostart']          = $_MG_CONF['mp3_autostart'];
    $playback_options['autostart_tf']       = ($_MG_CONF['mp3_autostart'] ? 'true' : 'false');
    $playback_options['enablecontextmenu']  = $_MG_CONF['mp3_enablecontextmenu'];
    $playback_options['enablecontextmenu_tf'] = ($_MG_CONF['mp3_enablecontextmenu'] ? 'true' : 'false');
    $playback_options['showstatusbar']      = $_MG_CONF['mp3_showstatusbar'];
    $playback_options['uimode']             = $_MG_CONF['mp3_uimode'];
    $playback_options['loop']               = $_MG_CONF['mp3_loop'];

    $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($I['media_id']) . "'");
    while ( $poRow = DB_fetchArray($poResult) ) {
        $playback_options[$poRow['option_name']] = $poRow['option_value'];
        $playback_options[$poRow['option_name']. '_tf'] = ( $poRow['option_value'] ? 'true' : 'false');
    }

    if (isset($_MG_USERPREFS['playback_mode']) && $_MG_USERPREFS['playback_mode'] != -1 ) {
        $playback_type = $_MG_USERPREFS['playback_mode'];
    } else {
        $playback_type = $MG_albums[$aid]->playback_type;
    }
    $u_tn = '';

    $_MG_USERPREFS['mp3_player'] = 2;
    $_MG_CONF['mp3_player'] = 2;

    switch ($playback_type) {
        case 0 :                    // Popup Window
            $win_height = 450;
            $win_width = 600;
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                $win_height = 320;
                $u_image     = $_MG_CONF['assets_url'] . '/placeholder_audio.svg';
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight); //@getimagesize($_MG_CONF['path_mediaobjects'] . 'placeholder_audio.svg');
            }
            $u_pic = "javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $I['media_id'] . "'," . $win_height . "," . $win_width . ")";
            break;
        case 1: // download
            $u_pic = $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'];
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                $u_image     = $_MG_CONF['assets_url'] . '/placeholder_audio.svg';
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight); //@getimagesize($_MG_CONF['path_mediaobjects'] . 'placeholder_audio.svg');
            }
            break;
        case 2 :    // inline
            if ( $I['media_tn_attached'] == 1 ) {

                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_tn = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
                $border_width = $media_size_disp[0] + 15;
                $u_pic = '<div class=out style="width:' . $border_width . 'px"><div class="in ltin tpin"><img src="' . $u_tn . '"/></div></div>';
                $playback_options['height'] = $media_size_disp[1]; // 50;
                $playback_options['width']  = 300;
            } else {
                $u_pic='';
                $u_tn     = $_MG_CONF['assets_url'] . '/placeholder_audio.svg';
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight); //@getimagesize($_MG_CONF['path_mediaobjects'] . 'placeholder_audio.svg');
                $playback_options['height'] = 200;
                $playback_options['width']  = 300;
            }
            $win_width = $playback_options['width'];
            $win_height = $playback_options['height'];

            $V = new Template( MG_getTemplatePath($aid) );

            $tfile = 'view_mp3.thtml';
            if ( $I['mime_type'] == 'audio/x-ms-wma' ) {
                $tfile = 'view_mp3_wmp.thtml';
            }

            $getID3 = new getID3;
            // Analyze file and store returned data in $ThisFileInfo
            $ThisFileInfo = $getID3->analyze($_MG_CONF['path_mediaobjects'] . 'orig/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext']);
            getid3_lib::CopyTagsToComments($ThisFileInfo);

            if ( isset($ThisFileInfo['tags']['id3v1']['title'][0]) ) {
                $mp3_title = str_replace(' ','+',$ThisFileInfo['tags']['id3v1']['title'][0]);
            } else {
                if ( isset($ThisFileInfo['tags']['id3v2']['title'][0] ) ) {
                    $mp3_title = str_replace(' ','+',$ThisFileInfo['tags']['id3v2']['title'][0]);
                } else {
                    $mp3_title = str_replace(' ','+',$I['media_original_filename']);
                }
            }
            if ( isset($ThisFileInfo['tags']['id3v1']['artist']) ) {
                $mp3_artist = $ThisFileInfo['tags']['id3v1']['artist'];
            } else {
                $mp3_artist = '';
            }

            $u_image = '';

            $V->set_file (array ('video' => $tfile));
            $V->set_var(array(
                'u_pic'             =>  $u_pic,
                'u_tn'              =>  $u_tn,
                'thumbnail'         => $u_tn,
                'autostart'         => ($playback_options['autostart'] ? 'true' : 'false'),
                'enablecontextmenu' => ($playback_options['enablecontextmenu'] ? 'true' : 'false'),
                'stretchtofit'      => isset($playback_options['stretchtofit']) ? ($playback_options['stretchtofit'] ? 'true' : 'false') : 'false',
                'showstatusbar'     => ($playback_options['showstatusbar'] ? 'true' : 'false'),
                'loop'              => ($playback_options['loop'] ? 'true' : 'false'),
                'playcount'         => ($playback_options['loop'] ? '9999' : '1'),
                'uimode'            => $playback_options['uimode'],
                'height'            => $playback_options['height'],
                'width'             => $playback_options['width'],
                'movie'             => $_MG_CONF['mediaobjects_url'] . '/orig/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext'],
                'site_url'      	=> $_MG_CONF['site_url'],
                'mp3_title'     	=> $mp3_title,
                'mp3_artist'    	=> $mp3_artist,
                'allow_download'    => ($MG_albums[$aid]->allow_download ? 'true' : 'false'),
                'lang_artist'       => $LANG_MG03['artist'],
                'lang_album'        => $LANG_MG03['album'],
                'lang_song'         => $LANG_MG03['song'],
                'lang_track'        => $LANG_MG03['track'],
                'lang_genre'        => $LANG_MG03['genre'],
                'lang_year'         => $LANG_MG03['year'],
                'lang_download'     => $LANG_MG03['download'],
                'lang_info'         => $LANG_MG03['info'],
                'lang_noflash' 		=> $LANG_MG03['no_flash'],
//                'player_url'        => $_CONF['site_url'].'/javascript/addons/mediaplayer/',
                'swf_version'   	=> '9',
            ));
            $V->parse('output','video');
            $u_image .= $V->finish($V->get_var('output'));
            return array($u_image,'',$win_width,$win_height,'');
            break;
        case 3: // use mms links
            $mms_path = preg_replace("/http/i",'mms',$_MG_CONF['mediaobjects_url']);
            $u_pic = $mms_path . '/orig/'.  $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext'];

            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                $u_image     = $_MG_CONF['assets_url'] . '/placeholder_audio.svg';
                $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight); //@getimagesize($_MG_CONF['path_mediaobjects'] . 'placeholder_audio.svg');
            }
            break;
    }

    $imageWidth  = $media_size_disp[0];
    $imageHeight = $media_size_disp[1];

    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$MG_albums[$aid]->displayFrameTemplate);
    $F->set_var(array(
        'media_link_start'  =>  '<a href="' . $u_pic . '">',
        'media_link_end'    =>  '</a>',
        'url_media_item'    =>  $u_pic,
        'media_thumbnail'   =>  $u_image,
        'media_size'        =>  'width="' . $imageWidth . '" height="' . $imageHeight . '"',
        'media_height'      =>  $imageHeight,
        'media_width'       =>  $imageWidth,
        'border_width'      =>  $imageWidth + 15,
        'border_height'     =>  $imageHeight + 15,
        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
        'frWidth'           =>  $imageWidth  - $MG_albums[$aid]->dfrWidth,
        'frHeight'          =>  $imageHeight - $MG_albums[$aid]->dfrHeight,
    ));
    $F->parse('media','media_frame');
    $retval = $F->finish($F->get_var('media'));
    return array($retval,$u_image,$imageWidth,$imageHeight,$u_pic);
}

function MG_displayOGG( $aid, $I, $full ) {
    global $_TABLES, $_CONF, $_MG_CONF, $MG_albums;

    $retval = '';

    $u_pic = $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'];

    if ( $I['media_tn_attached'] == 1 ) {
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                break;
            }
        }
    } else {
        $u_image     = $_MG_CONF['assets_url'] . '/placeholder_audio.svg';
        $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight); //@getimagesize($_MG_CONF['path_mediaobjects'] . 'placeholder_audio.svg');
    }

    $imageWidth  = $media_size_disp[0];
    $imageHeight = $media_size_disp[1];

    //frame
    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$MG_albums[$aid]->displayFrameTemplate);
    $F->set_var(array(
        'media_link_start'  =>  '<a href="' . $u_pic . '">',
        'media_link_end'    =>  '</a>',
        'url_media_item'    =>  $u_pic,
        'media_thumbnail'   =>  $u_image,
        'media_size'        =>  'width="' . $imageWidth . '" height="' . $imageHeight . '"',
        'media_height'      =>  $imageHeight,
        'media_width'       =>  $imageWidth,
        'border_width'      =>  $imageWidth + 15,
        'border_height'     =>  $imageHeight + 15,
        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
        'frWidth'           =>  $imageWidth  - $MG_albums[$aid]->dfrWidth,
        'frHeight'          =>  $imageHeight - $MG_albums[$aid]->dfrHeight,
    ));
    $F->parse('media','media_frame');
    $retval = $F->finish($F->get_var('media'));
    return $retval;
}

function MG_displayGeneric( $aid, $I, $full ) {
    global $_TABLES, $_CONF, $_MG_CONF, $MG_albums;

    $retval = '';

    $u_pic = $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'];
    if ( $I['media_tn_attached'] == 1 ) {
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                break;
            }
        }
    } else {
        switch ( $I['mime_type'] ) {
            case 'application/pdf' :
                $u_image     = $_MG_CONF['assets_url'] . '/placeholder_pdf.svg';
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight); //@getimagesize($_MG_CONF['path_mediaobjects'] . 'placeholder_audio.svg');
                break;
            case 'application/zip' :
            case 'application/x-compressed' :
            case 'application/x-gzip' :
            case 'application/x-gzip' :
            case 'multipart/x-gzip' :
            case 'application/arj' :
                $u_image     = $_MG_CONF['assets_url'] . '/placeholder_zip.svg';
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight);
                break;
            default :
                $u_image     = $_MG_CONF['assets_url'] . '/placeholder.svg';
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight);
                break;
        }
    }

    $imageWidth  = $media_size_disp[0];
    $imageHeight = $media_size_disp[1];

    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$MG_albums[$aid]->displayFrameTemplate);
    $F->set_var(array(
        'media_link_start'  =>  '<a href="' . $u_pic . '">',
        'media_link_end'    =>  '</a>',
        'url_media_item'    =>  $u_pic,
        'media_thumbnail'   =>  $u_image,
        'media_size'        =>  'width="' . $imageWidth . '" height="' . $imageHeight . '"',
        'media_height'      =>  $imageHeight,
        'media_width'       =>  $imageWidth,
        'border_width'      =>  $imageWidth + 15,
        'border_height'     =>  $imageHeight + 15,
        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
        'frWidth'           =>  $imageWidth  - $MG_albums[$aid]->dfrWidth,
        'frHeight'          =>  $imageHeight - $MG_albums[$aid]->dfrHeight,
    ));
    $F->parse('media','media_frame');
    $retval = $F->finish($F->get_var('media'));
    return array($retval,$u_image,$imageWidth,$imageHeight,$u_pic);
}


function MG_displayTGA($aid,$I,$full,$mediaObject) {
    global $_CONF, $_MG_CONF, $MG_albums, $_USER;

    $retval = '';
    $media_link_start = '';
    $media_link_end   = '';

    $media_size_disp = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.jpg');

    if ( $media_size_disp == false ) {
        $u_image = $_MG_CONF['assets_url'] . '/placeholder_missing.svg';
        $media_size_disp[0] = 200;
        $media_size_disp[1] = 200;
        $media_link_start = '';
        $u_pic = '#';
    } else {
        if ( $MG_albums[$aid]->full == 2 || $_MG_CONF['discard_original'] == 1 || ( $MG_albums[$aid]->full == 1 && !COM_isAnonUser() )) {
            $u_pic = '#';
            $media_link_start = '';
        } else {
            $media_link_start = '<a href="' . $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'] . '">';
            $media_link_end = '</a>';
            $u_pic      = $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'] . '"';
        }
        $u_image    = $_MG_CONF['mediaobjects_url'] . '/disp/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.jpg';
    }
    $imageWidth  = $media_size_disp[0];
    $imageHeight = $media_size_disp[1];

    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$MG_albums[$aid]->displayFrameTemplate);
    $F->set_var(array(
        'media_link_start'  => $media_link_start,
        'media_link_end'    => $media_link_end,
        'url_media_item'    =>  $u_pic,
        'media_thumbnail'   =>  $u_image,
        'media_size'        =>  'width="' . $imageWidth . '" height="' . $imageHeight . '"',
        'media_height'      =>  $imageHeight,
        'media_width'       =>  $imageWidth,
        'border_width'      =>  $imageWidth + 15,
        'border_height'     =>  $imageHeight + 15,
        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
        'frWidth'           =>  $imageWidth  - $MG_albums[$aid]->dfrWidth,
        'frHeight'          =>  $imageHeight - $MG_albums[$aid]->dfrHeight,
    ));
    $F->parse('media','media_frame');
    $retval = $F->finish($F->get_var('media'));
    return array($retval,$u_image,$imageWidth,$imageHeight,$u_pic);
}

function MG_displayPSD($aid,$I,$full,$mediaObject) {
    global $_CONF, $_MG_CONF, $MG_albums, $_USER;

    $retval = '';
    $media_link_start = '';
    $media_link_end   = '';

    $media_size_disp = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.jpg');

    if ( $MG_albums[$aid]->full == 2 || $_MG_CONF['discard_original'] == 1 || ( $MG_albums[$aid]->full == 1 && !COM_isAnonUser() )) {
        $u_pic = '';
        $media_link_start = '';
    } else {
        $u_pic      = $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'] . '"';
        $media_link_start = '<a href="' . $_MG_CONF['site_url'] . '/download.php?mid=' . $I['media_id'] . '">';
        $media_link_end   = '</a>';
    }
    $u_image    = $_MG_CONF['mediaobjects_url'] . '/disp/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.jpg';

    $imageWidth  = $media_size_disp[0];
    $imageHeight = $media_size_disp[1];
    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$MG_albums[$aid]->displayFrameTemplate);
    $F->set_var(array(
        'media_link_start'  =>  $media_link_start,
        'media_link_end'    =>  $media_link_end,
        'url_media_item'    =>  $u_pic,
        'media_thumbnail'   =>  $u_image,
        'media_size'        =>  'width="' . $imageWidth . '" height="' . $imageHeight . '"',
        'media_height'      =>  $imageHeight,
        'media_width'       =>  $imageWidth,
        'border_width'      =>  $imageWidth + 15,
        'border_height'     =>  $imageHeight + 15,
        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
        'frWidth'           =>  $imageWidth  - $MG_albums[$aid]->dfrWidth,
        'frHeight'          =>  $imageHeight - $MG_albums[$aid]->dfrHeight,
    ));
    $F->parse('media','media_frame');
    $retval = $F->finish($F->get_var('media'));
    return array($retval,$u_image,$imageWidth,$imageHeight,$u_pic);
}

function MG_displayEmbed($aid,$I,$full,$mediaObject) {
    global $_CONF, $_MG_CONF, $MG_albums, $_USER;

    $retval = '';
    $resolution_x = 0;
    $resolution_y = 0;

	$playback_type = $MG_albums[$aid]->playback_type;

    switch ($playback_type) {
        case 0 :                    // Popup Window
        	if ( $I['media_type'] == 5 ) {
            	$resolution_x = 460;
            	$resolution_y = 380;
        	}
			if (preg_match("/youtube/i", $I['remote_url'])) {
				$default_thumbnail = 'youtube.png';
			} else if (preg_match("/google/i", $I['remote_url'])) {
				$default_thumbnail = 'googlevideo.png';
			} else {
				$default_thumbnail = 'remote.png';
			}
            $u_pic = "javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $I['media_id'] . "'," . $resolution_y . "," . $resolution_x . ")";
            if ( $I['media_tn_attached'] == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext) ) {
                        $u_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext;
                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $I['media_filename'][0] . '/tn_' . $I['media_filename'] . $ext);
                        break;
                    }
                }
            } else {
                if ( $MG_albums[$aid]->tnWidth > $MG_albums[$aid]->tnHeight ) {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video_w.svg';
                } else {
                    $u_image     = $_MG_CONF['assets_url'] . '/placeholder_video.svg';
                }
                $media_size_orig = $media_size_disp  = array($MG_albums[$aid]->tnWidth,$MG_albums[$aid]->tnHeight);
            }
            break;
        case 1: 	// download - not supported for embedded video
        case 3: 	// mms - not supported for embedded video
        case 2 :    // inline
		    $F = new Template( MG_getTemplatePath($aid) );
		    $F->set_file('media_frame','embed.thtml');
		    $F->set_var(array(
		    	'embed_string'		=>  $I['remote_url'],
		        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
		        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
		    ));
		    $F->parse('media','media_frame');
		    $retval = $F->finish($F->get_var('media'));
            return array($retval,'',$resolution_x,$resolution_y,'');
	}
    $imageWidth  = $media_size_orig[0];
    $imageHeight = $media_size_orig[1];
    //frame
    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$MG_albums[$aid]->displayFrameTemplate);
    $F->set_var(array(
        'media_link_start'  =>  '<a href="' . $u_pic . '">',
        'media_link_end'    =>  '</a>',
        'url_media_item'    =>  $u_pic,
        'media_thumbnail'   =>  $u_image,
        'media_size'        =>  'width="' . $imageWidth . '" height="' . $imageHeight . '"',
        'media_height'      =>  $imageHeight,
        'media_width'       =>  $imageWidth,
        'border_width'      =>  $imageWidth + 15,
        'border_height'     =>  $imageHeight + 15,
        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
        'frWidth'           =>  $imageWidth  - $MG_albums[$aid]->dfrWidth,
        'frHeight'          =>  $imageHeight - $MG_albums[$aid]->dfrHeight,
    ));
    $F->parse('media','media_frame');
    $retval .= $F->finish($F->get_var('media'));
    return array($retval,$u_image,$imageWidth,$imageHeight,$u_pic);
}


function MG_displayJPG($aid,$I,$full,$mid,$sortOrder,$sortID=0,$spage=0) {
    global $_CONF, $_MG_CONF, $MG_albums, $_USER;

    $retval = '';
    $media_size_disp = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext']);

    if ($I['remote_media'] == 1 ) {
        if ( $I['media_resolution_x'] != 0 && $I['media_resolution_y'] != 0 ) {
    	    $media_size_disp[0] = $I['media_resolution_x'];
            $media_size_disp[1] = $I['media_resolution_y'];
        } else {
            $media_size_disp = @getimagesize($I['remote_url']);
            if ( $media_size_disp = false ) {
                $media_size_disp[0] = 0;
                $media_size_disp[1] = 0;
            }
        }
    }

    if ( $media_size_disp == false && $I['remote_media'] == 0) {
        $media_size_disp = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.jpg');
    }
    $media_size_orig = @getimagesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext']);

    $media_link_start = '';
    $media_link_end   = '';

    if ( $media_size_orig == FALSE || $MG_albums[$aid]->full == 2 || $_MG_CONF['discard_original'] == 1 || ( $MG_albums[$aid]->full == 1 && COM_isAnonUser() )) {
        $u_pic = '#';
        $media_link_start = '';
        $raw_link_url = '';
    } else {
        if ( $full == 0 && $_MG_CONF['full_in_popup'] ) {
            $popup_x = $media_size_orig[0] + 75;
            $popup_y = $media_size_orig[1] + 100;
            $u_pic  = 'javascript:showVideo(\'' . $_MG_CONF['site_url'] . '/popup.php?s=' . $mid .'&amp;sort=' . $sortOrder . "'," . $popup_y . "," . $popup_x . ")";
            $media_link_start = '<a href="' . 'javascript:showVideo(\'' . $_MG_CONF['site_url'] . '/popup.php?s=' . $mid .'&amp;sort=' . $sortOrder . "'," . $popup_y . "," . $popup_x . ")\">";
            $media_link_end = '</a>';
            $raw_link_url = 'javascript:showVideo(\'' . $_MG_CONF['site_url'] . '/popup.php?s=' . $mid .'&amp;sort=' . $sortOrder . "'," . $popup_y . "," . $popup_x . ")\"";
        } else {
            $u_pic      = $_MG_CONF['site_url'] . '/media.php?f=' . ($full ? '0' : '1') . '&amp;s=' . $mid . '&amp;i=' . $sortID . '&amp;p=' . $spage;
            $media_link_start = '<a href="' . $_MG_CONF['site_url'] . '/media.php?f=' . ($full ? '0' : '1') . '&amp;s=' . $mid . '&amp;i=' . $sortID . '&amp;p=' . $spage . '">';
            $media_link_end = '</a>';
            $raw_link_url = $_MG_CONF['site_url'] . '/media.php?f=' . ($full ? '0' : '1') . '&amp;s=' . $mid . '&amp;i=' . $sortID . '&amp;p=' . $spage;
        }
    }

    if ( $full == 1 ) {
        $u_image    = $_MG_CONF['mediaobjects_url'] . '/orig/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext'];
    } else {
        if ( $media_size_disp == false && !$I['remote_media']) {
            $u_image = $_MG_CONF['assets_url'] . '/placeholder_missing.svg';
            $media_size_disp[0] = 200;
            $media_size_disp[1] = 200;
        } else {
            if ($I['remote_media'] == 1 ) {
		        $u_image = $I['remote_url'];
            } else {
                $u_image    = $_MG_CONF['mediaobjects_url'] . '/disp/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.jpg';
                if ( !file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext'])) {
                    $u_image    = $_MG_CONF['mediaobjects_url'] . '/disp/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.jpg';
                } else {
                    $u_image    = $_MG_CONF['mediaobjects_url'] . '/disp/' . $I['media_filename'][0] . '/' . $I['media_filename'] . '.' . $I['media_mime_ext'];
                }
            }
        }
    }
    if ( $media_size_orig == false ) {
        $media_size_orig[0] = 200;
        $media_size_orig[1] = 150;
    }

    $imageWidth  = $full ? $media_size_orig[0] : $media_size_disp[0];
    $imageHeight = $full ? $media_size_orig[1] : $media_size_disp[1];

    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$MG_albums[$aid]->displayFrameTemplate);
    $F->set_var(array(
        'url_media_item'    =>  $u_pic,
        'media_link_start'  =>  $media_link_start,
        'media_link_end'    =>  $media_link_end,
        'media_thumbnail'   =>  $u_image,
        'media_size'        =>  ($imageWidth != 0 && $imageHeight != 0 ) ? 'width="' . $imageWidth . '" height="' . $imageHeight . '"' : '',
        'media_height'      =>  $imageHeight,
        'media_width'       =>  $imageWidth,

        'media_title'       =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? PLG_replaceTags($I['media_title'],'mediagallery','media_title') : '',
        'media_tag'         =>  (isset($I['media_title']) && $I['media_title'] != ' ') ? strip_tags($I['media_title']) : '',
        'frWidth'           =>  $imageWidth  - $MG_albums[$aid]->dfrWidth,
        'frHeight'          =>  $imageHeight - $MG_albums[$aid]->dfrHeight,
    ));
    if ( $imageWidth > 0 && $imageHeight > 0 ) {
        $F->set_var(array(
            'border_width'      =>  $imageWidth + 15,
            'border_height'     =>  $imageHeight + 15,
        ));
    }
    $F->parse('media','media_frame');
    $retval = $F->finish($F->get_var('media'));
    return array($retval,$u_image,$imageWidth,$imageHeight,$raw_link_url,$media_link_start,$media_link_end);
}


function MG_displayMediaImage( $mediaObject, $full, $sortOrder, $comments, $sortID=0,$spage=0) {
    global $MG_albums, $_TABLES, $_CONF, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $LANG_MG04, $LANG_ACCESS, $LANG01, $album_jumpbox, $glversion, $_USER, $_MG_USERPREFS;
    global $_DB_dbms, $LANG04,$ratedIds, $LANG_LOCALE;

    USES_lib_social();

    $retval = '';
    $media_link_start = '';
    $media_link_end = '';
    $srcID  = $mediaObject;

    $outputHandle = outputHandler::getInstance();

    $aid  = DB_getItem($_TABLES['mg_media_albums'], 'album_id','media_id="' . DB_escapeString($mediaObject) . '"');

    if ( isset($MG_albums[$aid]->pid) ) {
        $pid = $MG_albums[$aid]->pid;
    } else {
        $pid = 0;
    }
    if ( @method_exists($MG_albums[$aid],'getOffset') ) {
        $aOffset = $MG_albums[$aid]->getOffset();
    } else {
        $aOffset = -1;
    }
    if ( $aOffset == -1 || $MG_albums[$aid]->access == 0 || ($MG_albums[$aid]->hidden == 1 && $MG_albums[$aid]->access != 3 )) {
        $retval .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
        return array($LANG_MG00['access_denied_msg'],$retval,'','');
    }

    $mid = $mediaObject;

    $orderBy = MG_getSortOrder($aid,$sortOrder);

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma LEFT JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE ma.album_id=" . $aid . $orderBy;

    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );

    $total_media = $nRows;
    $media = array();
    while ( $row = DB_fetchArray($result) ) {
        $media[] = $row;
        $ids[] = $row['media_id'];
    }

    $key = array_search($mid,$ids);
    if ( $key === false ) {
        $retval .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
        return array($LANG_MG00['access_denited_msg'], $retval,'','');
    }

    $mediaObject = $key;

    if ( $MG_albums[$aid]->full == 2 || $_MG_CONF['discard_original'] == 1 || ( $MG_albums[$aid]->full == 1 && COM_isAnonUser())) {
        $full = 0;
    }
    if ( $full )
        $disp = 'orig';
    else
        $disp = 'disp';

    if ( $MG_albums[$aid]->enable_comments == 0 ) {
        $comments = 0;
    }

    if ( $sortID > 0 ) {
        $MG_albums[$aid]->enable_slideshow = 0;
    }

    $themeCSS = '';
    $nFrame = new mgFrame();
    $nFrame->constructor( $MG_albums[$aid]->display_skin );
    $MG_albums[$aid]->displayFrameTemplate = $nFrame->getTemplate();
    $MG_albums[$aid]->dfrWidth = $nFrame->frame['wHL'] + $nFrame->frame['wHR'];
    $MG_albums[$aid]->dfrHeight = $nFrame->frame['hVT'] + $nFrame->frame['hVB'];
    $themeCSS = $nFrame->getCSS();

    if ($themeCSS != '') {
        $outputHandle->addStyle($themeCSS);
        $themeCSS = '';
    }

	$T = new Template( MG_getTemplatePath($aid) );
    switch ( $media[$mediaObject]['media_type'] ) {
    	case '0':		// image
    		$T->set_file('page','view_image.thtml');
    		$ogType = 'article';
    		break;
    	case '1' :		// video
    	case '5' : 		// embedded video
            $meta_file_name = 	$_MG_CONF['path_mediaobjects'] . 'orig/' . $media[$mediaObject]['media_filename'][0] . '/' . $media[$mediaObject]['media_filename'] . '.' . $media[$mediaObject]['media_mime_ext'];
            $meta = IMG_getMediaMetaData($_MG_CONF['path_mediaobjects'] . 'orig/' . $media[$mediaObject]['media_filename'][0] . '/' . $media[$mediaObject]['media_filename'] . '.' . $media[$mediaObject]['media_mime_ext']);

            if ( $meta['mime_type'] == 'video/quicktime' || $meta['mime_type'] == 'video/mp4') {
                if ( $meta['fileformat'] == 'mp4' ) {
                    $media[$mediaObject]['mime_type'] = 'video/mp4';
                }
            }
    		$T->set_file('page','view_video.thtml');
    		$ogType = 'video.movie';
    		break;
 		case '2' :		// audio
 			$T->set_file('page','view_audio.thtml');
 			$ogType = 'music.song';
 			break;
 		default:
 			$T->set_file('page','view_image.thtml');
 			$ogType = 'article';
 			break;
 	}

    $filter = new \sanitizer();
    $filter->setNamespace('mediagallery','media_title');
    $filter->setReplaceTags(false);
    $filter->setCensorData(true);
    if ($MG_albums[$aid]->enable_html == 1) {
        $filter->setPostmode('html');
    } else {
        $filter->setPostmode('text');
    }
    if (isset($media[$mediaObject]['media_title'])) {
        $media[$mediaObject]['media_title'] = $filter->filterData($media[$mediaObject]['media_title']);
    }
    if (isset($media[$mediaObject]['media_desc'])) {
        $media[$mediaObject]['media_desc'] = $filter->filterData($media[$mediaObject]['media_desc']);
    }
    $ptitle = (isset($media[$mediaObject]['media_title']) && $media[$mediaObject]['media_title'] != ' ' ) ? PLG_replaceTags($media[$mediaObject]['media_title'],'mediagallery','media_title') : '';

    $permalink = $_MG_CONF['site_url'] . '/media.php?s='.$srcID;
    $outputHandle->addLink("canonical",$permalink);

    $outputHandle->addMeta('property','og:site_name',$_CONF['site_name']);
    $outputHandle->addMeta('property','og:locale',isset($LANG_LOCALE) ? $LANG_LOCALE : 'en_US');

    $outputHandle->addMeta('property','og:title',$ptitle);
    $outputHandle->addMeta('property','og:type',$ogType);
    $outputHandle->addMeta('property','og:url',$permalink);

    $T->set_var('permalink',$permalink);
    $T->set_file (array(
        'shutterfly'    => 'digibug.thtml',
    ));
    $T->set_var('header', $LANG_MG00['plugin']);
    $T->set_var('site_url',$_MG_CONF['site_url']);
    $T->set_var('plugin','mediagallery');

    // construct the album jumpbox...
    $level = 0;
    $album_jumpbox = '<form class="uk-form" name="jumpbox" action="' . $_MG_CONF['site_url'] . '/album.php' . '" method="get" style="margin:0;padding:0"><div>';
    $album_jumpbox .= $LANG_MG03['jump_to'] . ':&nbsp;<select name="aid" onchange="forms[\'jumpbox\'].submit()">';
    $MG_albums[0]->buildJumpBox($aid);
    $album_jumpbox .= '</select>';
//    $album_jumpbox .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '"/>';
    $album_jumpbox .= '<button class="uk-button" type="submit" value="'.$LANG_MG03['go'].'">'.$LANG_MG03['go'].'</button>';
    $album_jumpbox .= '<input type="hidden" name="page" value="1"/>';
    $album_jumpbox .= '</div></form>';

    // Update the views count... But only for non-admins

    if (!$MG_albums[0]->owner_id) {
        $media_views = $media[$mediaObject]['media_views'] + 1;
        DB_query("UPDATE " . $_TABLES['mg_media'] . " SET media_views=" . $media_views . " WHERE media_id='" . DB_escapeString($media[$mediaObject]['media_id']) . "'");
    }

    $columns_per_page   = ($MG_albums[$aid]->display_columns == 0 ? $_MG_CONF['ad_display_columns'] : $MG_albums[$aid]->display_columns);
    $rows_per_page      = ($MG_albums[$aid]->display_rows == 0 ? $_MG_CONF['ad_display_rows'] : $MG_albums[$aid]->display_rows);

    if (isset($_MG_USERPREFS['display_rows']) && $_MG_USERPREFS['display_rows'] > 0 ) {
        $rows_per_page = $_MG_USERPREFS['display_rows'];
    }
    if (isset($_MG_USERPREFS['display_columns'] ) && $_MG_USERPREFS['display_columns'] > 0 ) {
        $columns_per_page = $_MG_USERPREFS['display_columns'];
    }
    $media_per_page     = $columns_per_page * $rows_per_page;

    if ( $MG_albums[$aid]->albums_first ) {
        $childCount = $MG_albums[$aid]->getChildCount();
        $page = intval(($mediaObject + $childCount) / $media_per_page) + 1;
    } else {
        $page = intval(($mediaObject)  / $media_per_page) + 1;
    }

    /*
     * check to see if the original image exists, if not fall back to full image
     */

    $media_size_orig = @getimagesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $media[$mediaObject]['media_filename'][0] . '/' . $media[$mediaObject]['media_filename'] . '.' . $media[$mediaObject]['media_mime_ext']);

    if ($media_size_orig == false ) {
        $full = 0;
        $disp = 'disp';
    }

    $aPage = intval(($aOffset)  / ($_MG_CONF['album_display_columns'] * $_MG_CONF['album_display_rows'])) + 1;
    if ( $sortID > 0 ) {
        $birdseed = '<a href="' . $_CONF['site_url'] . '/index.php">' . $LANG_MG03['home'] . '</a> ' .
                	($_MG_CONF['gallery_only'] == 1 ? '' : $_MG_CONF['seperator'] . ' <a href="' . $_MG_CONF['site_url'] . '/index.php?page=' . $aPage . '">' . $_MG_CONF['menulabel'] . '</a> ') .
    			    $_MG_CONF['seperator'] . '<a href="' . $_MG_CONF['site_url'] . '/search.php?id=' . $sortID . '&amp;page=' . $spage . '">' . $LANG_MG03['search_results'] . '</a>';  $MG_albums[$aid]->getPath(1,$sortOrder,$page) . '</a>';

        $birdseed_ul = '<li><a href="' . $_CONF['site_url'] . '/index.php">' . $LANG_MG03['home'] . '</a></li>' .
        	'<li><a href="' . $_MG_CONF['site_url'] . '/index.php?page=' . $aPage . '">' . $_MG_CONF['menulabel'] . '</a></li>' .
		    '<li><a href="' . $_MG_CONF['site_url'] . '/search.php?id=' . $sortID . '&amp;page=' . $spage . '">' . $LANG_MG03['search_results'] . '</a></li>' .
		    $MG_albums[$aid]->getPath_ul(1,$sortOrder,$page) . '</a>';


        $album_link = '<a href="' . $_MG_CONF['site_url'] . '/search.php?id=' . $sortID . '&amp;page=' . $spage . '">';
    } else {
        $birdseed = '<a href="' . $_CONF['site_url'] . '/index.php">' . $LANG_MG03['home'] . '</a> ' .
        			($_MG_CONF['gallery_only'] == 1 ? '' : $_MG_CONF['seperator'] . ' <a href="' . $_MG_CONF['site_url'] . '/index.php?page=' . $aPage . '">' . $_MG_CONF['menulabel'] . '</a> ') .
    	    		$MG_albums[$aid]->getPath(1,$sortOrder,$page) . '</a>';

        $birdseed_ul = '<li><a href="' . $_CONF['site_url'] . '/index.php">' . $LANG_MG03['home'] . '</a></li>' .
			'<li><a href="' . $_MG_CONF['site_url'] . '/index.php?page=' . $aPage . '">' . $_MG_CONF['menulabel'] . '</a></li>'.
    		$MG_albums[$aid]->getPath_ul(1,$sortOrder,$page) . '</a>';

    	$album_link = '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $aid . '&amp;page=' . $page . '&amp;sort=' . $sortOrder . '">';
    }

    mg_usage('media_view',$MG_albums[$aid]->title, $media[$mediaObject]['media_title'],$media[$mediaObject]['media_id']);

    // hack for tga files...
    if ( $media[$mediaObject]['mime_type'] == 'image/x-targa' || $media[$mediaObject]['mime_type'] == 'image/tga' ) {
        $full = 0;
        $disp = 'disp';
    }

    switch( $media[$mediaObject]['mime_type'] ) {
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
            list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayASF($aid,$media[$mediaObject],$full);
            break;
        case 'audio/x-ms-wma' :
            list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayMP3($aid,$media[$mediaObject],$full);
            break;
        case 'video/mp4' :
            list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayMP4($aid,$media[$mediaObject],$full);
            break;
        case 'video/mpeg' :
        case 'video/x-mpeg' :
        case 'video/x-mpeq2a' :
        	if ( $_MG_CONF['use_wmp_mpeg'] == 1 ) {
            	list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayASF($aid,$media[$mediaObject],$full);
            	break;
            }
        case 'video/x-motion-jpeg' :
        case 'video/quicktime' :
        case 'video/x-qtc' :
        case 'video/x-m4v' :
            if ($media[$mediaObject]['media_mime_ext'] == 'mp4' && isset($_MG_CONF['play_mp4_flv']) && $_MG_CONF['play_mp4_flv'] == true) {
                list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayFLV($aid,$media[$mediaObject],$full);
            } else {
                list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayMOV($aid,$media[$mediaObject],$full);
            }

            break;
        case 'embed' :
	        list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayEmbed($aid,$media[$mediaObject],$full,$mediaObject);
			break;
        case 'application/x-shockwave-flash' :
           	list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displaySWF($aid,$media[$mediaObject],$full);
            break;
        case 'video/x-flv' :
            list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayFLV($aid,$media[$mediaObject],$full);
            break;
        case 'audio/mpeg' :
        case 'audio/x-mpeg' :
        case 'audio/mpeg3' :
        case 'audio/x-mpeg-3' :
            list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayMP3($aid,$media[$mediaObject],$full);
            break;
        case 'application/ogg' :
        case 'application/x-ogg' :
            list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayOGG($aid,$media[$mediaObject],$full);
            break;
        case 'image/x-targa' :
        case 'image/tga' :
        case 'image/tiff' :
            list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayTGA($aid,$media[$mediaObject],$full,$mediaObject);
            break;
        case 'image/photoshop' :
        case 'image/x-photoshop' :
        case 'image/psd' :
        case 'application/photoshop' :
        case 'application/psd' :
            list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayPSD($aid,$media[$mediaObject],$full,$mediaObject);
            break;
        case 'image/gif' :
        case 'image/jpeg' :
        case 'image/jpg' :
        case 'image/png' :
        case 'image/bmp' :
            list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url,$media_link_start,$media_link_end) = MG_displayJPG($aid,$media[$mediaObject],$full, $media[$mediaObject]['media_id'], $sortOrder,$sortID,$spage);
            break;
        default :
            switch( $media[$mediaObject]['media_mime_ext']) {
                case 'jpg' :
                case 'gif' :
                case 'png' :
                case 'bmp' :
                    list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url,$media_link_start,$media_link_end) = MG_displayJPG($aid,$media[$mediaObject],$full, $media[$mediaObject]['media_id'], $sortOrder,$sortID,$spage);
                    break;
                case 'asf' :
                    list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayASF($aid,$media[$mediaObject],$full);
                    break;
                default :
                    list($u_image,$raw_image,$raw_image_width,$raw_image_height,$raw_link_url) = MG_displayGeneric($aid,$media[$mediaObject],$full, $media[$mediaObject]['media_id'], $sortOrder);
                    break;
            }
    }

    $mid = $media[$mediaObject]['media_id'];

  	$media_date = MG_getUserDateTimeFormat( $media[$mediaObject]['media_time'] );
    $upload_date = MG_getUserDateTimeFormat( $media[$mediaObject]['media_upload_time'] );

    // build the rating bar if rating is enabled.
    if ( $MG_albums[$aid]->enable_rating > 0 ) {
        $uid    = COM_isAnonUser() ? 1 : $_USER['uid'];
        $static = false;
        $voted  = 0;
        // check to see if we are the owner, if so, no rating for us...
        if (isset($_USER['uid']) && $_USER['uid'] == $media[$mediaObject]['media_user_id'] ) {
            $static = true;
            $voted = 0;
        } else {
            if ( in_array($media[$mediaObject]['media_id'],$ratedIds)) {
                $static = true;
                $voted = 1;
            } else {
                $static = 0;
                $voted = 0;
            }
        }

        if ( $MG_albums[$aid]->enable_rating == 1 && (COM_isAnonUser() ) ) {
            $static = true;
            $voted = 0;
        }
        $rating_box = RATING_ratingBar('mediagallery',$media[$mediaObject]['media_id'], $media[$mediaObject]['media_votes'], $media[$mediaObject]['media_rating'], $voted, 5, $static, '');
    } else {
        $rating_box = '';
    }
    $T->set_var('rating_box',$rating_box);

    if ( $MG_albums[$aid]->allow_download ) {
        $T->set_var(array(
            'download'  => '<a href="' . $_MG_CONF['site_url'] . '/download.php?mid=' . $media[$mediaObject]['media_id'] . '">' . $LANG_MG01['download'] . '</a>',
        ));
    }
    if ( $media[$mediaObject]['media_type'] == 0 && $MG_albums[$aid]->enable_shutterfly ) {
        $media_size_orig = false;
        $media_size_tn   = false;
        if ( $_MG_CONF['discard_original'] == 1 ) {
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $media[$mediaObject]['media_filename'][0] .'/' . $media[$mediaObject]['media_filename'] . $ext) ) {
                    $sf_picture = $_MG_CONF['mediaobjects_url'] . '/disp/' . $media[$mediaObject]['media_filename'][0] .'/' . $media[$mediaObject]['media_filename'] . $ext;
                    $media_size_orig = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $media[$mediaObject]['media_filename'][0] . '/' . $media[$mediaObject]['media_filename'] . $ext);
                    break;
                }
            }
        } else {
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'orig/' . $media[$mediaObject]['media_filename'][0] .'/' . $media[$mediaObject]['media_filename'] . $ext) ) {
                    $sf_picture = $_MG_CONF['mediaobjects_url'] . '/orig/' . $media[$mediaObject]['media_filename'][0] .'/' . $media[$mediaObject]['media_filename'] . $ext;
                    $media_size_orig = @getimagesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $media[$mediaObject]['media_filename'][0] . '/' . $media[$mediaObject]['media_filename'] . $ext);
                    break;
                }
            }
        }

        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $media[$mediaObject]['media_filename'][0] .'/' . $media[$mediaObject]['media_filename'] . $ext) ) {
                $tnImage = $_MG_CONF['mediaobjects_url'] . '/tn/' . $media[$mediaObject]['media_filename'][0] .'/' . $media[$mediaObject]['media_filename'] . $ext;
                $media_size_tn = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $media[$mediaObject]['media_filename'][0] . '/' . $media[$mediaObject]['media_filename'] . $ext);
                break;
            }
        }
        $outputHandle->addMeta('property','og:image',$tnImage);
        if ( $media_size_orig != false && $media_size_tn != false ) {
            $T->set_var(array(
                'sf_height'             =>  $media_size_orig[1],
                'sf_width'              =>  $media_size_orig[0],
                'sf_tn_height'          =>  $media_size_tn[1],
                'sf_tn_width'           =>  $media_size_tn[0],
                'sf_thumbnail'          =>  $tnImage,
                'sf_picture'            =>  $sf_picture,
                'sf_title'              =>  $media[$mediaObject]['media_title'],
                'lang_print_digibug'    =>  $LANG_MG03['print_digibug'],
                'lang_print_shutterfly' =>  $LANG_MG03['print_shutterfly'],
            ));
            $T->parse('shutterfly_submit','shutterfly');
        }
    }

    if ($MG_albums[$aid]->access == 3 || ($_MG_CONF['allow_user_edit'] == true && isset($_USER['uid']) && $media[$mediaObject]['media_user_id'] == $_USER['uid'])) {
        $edit_item = '<a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=mediaedit&amp;s=1&amp;album_id=' . $aid . '&amp;mid=' . $mid . '">' . $LANG_MG01['edit'] . '</a>';
    } else {
        $edit_item = '';
    }

    $media_desc = PLG_replaceTags(nl2br($media[$mediaObject]['media_desc']),'mediagallery','media_description');

    if ( strlen($media_desc) > 0 ) {
        $metaDesc = $media_desc;
        $metaDesc = strip_tags($metaDesc);
        $html2txt = new Html2Text\Html2Text($metaDesc,false);
        $metaDesc = trim($html2txt->get_text());
        $shortComment = '';
        $metaArray = explode(' ',$metaDesc);
        $wordCount = count($metaArray);
        $lengthCount = 0;
        $tailString = '';
        foreach ($metaArray AS $word) {
            $lengthCount = $lengthCount + strlen($word);
            $shortComment .= $word.' ';
            if ( $lengthCount >= 100 ) {
                $tailString = '...';
                break;
            }
        }
        $metaDesc = trim($shortComment).$tailString;
        $outputHandle->addMeta('name','description',$metaDesc);
        $media_desc .= '<br/><br/>';
    }

    // start of the lightbox slideshow code

    if ( $MG_albums[$aid]->enable_slideshow == 2 ) {
        $lbSlideShow  = '<noscript><div class="pluginAlert">' . $LANG04[150] . '</div></noscript>' . LB;
        $lbSlideShow .= '<script>' . LB;
        $lbSlideShow .= 'function openGallery1() {' . LB;
        $lbSlideShow .= '    return loadXMLDoc("' . $_MG_CONF['site_url'] . '/lightbox.php?aid=' . $aid . '");';
        $lbSlideShow .= '}' . LB;
        $lbSlideShow .= '</script>' . LB;
        $T->set_var('lbslideshow',$lbSlideShow);
    } else {
        $T->set_var('lbslideshow','');
    }

    // end of the lightbox slideshow code

    switch ( $MG_albums[$aid]->enable_slideshow ) {
        case 0 :
            $url_slideshow = '';
            break;
        case 1 :
            $url_slideshow = '<a href="' . $_MG_CONF['site_url'] . '/slideshow.php?aid=' . $aid . '&amp;sort=' . $sortOrder . '"><b>' . $LANG_MG03['slide_show'] . '</b></a>';
            break;
        case 2:
        case 3:
        case 4:
            $lbss_count = DB_count($_TABLES['mg_media'],'media_type',0);
            $sql = "SELECT COUNT(m.media_id) as lbss_count FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                                " ON ma.media_id=m.media_id WHERE m.media_type = 0 AND ma.album_id=" . $aid;
            $res = DB_query($sql);
            list($lbss_count) = DB_fetchArray($res);

        	if ( $lbss_count != 0 ) {
            	$url_slideshow = '<span id="mgslideshow" class="jsenabled_show" style="display:none"><a href="#" onclick="return openGallery1()"><b>' . $LANG_MG03['slide_show'] . '</b></a></span>';
            } else {
            	$MG_albums[$aid]->enable_slideshow = 0;
            }
            break;
    }

    $prevLink = '';
    $nextLink = '';
    list($prevLink,$nextLink) = ($sortID > 0 ? array('','') : MG_getNextandPrev($_MG_CONF['site_url'] . "/media.php?f=" . ($full ? '1' : '0') . "&amp;sort=" . $sortOrder, $nRows, 1, $mediaObject, $media, TRUE));

    $T->set_var(array(
        'birdseed'      =>  $birdseed,
        'birdseed_ul'   =>  $birdseed_ul,
        'slide_show'    =>  isset($url_slideshow) ? $url_slideshow : '',
        'image_detail'  =>  $u_image,
        'border_height' =>  $raw_image_height + 30,
        'border_width'  =>  $raw_image_width + 30,
        'media_title'   =>  (isset($media[$mediaObject]['media_title']) && $media[$mediaObject]['media_title'] != ' ' ) ? PLG_replaceTags($media[$mediaObject]['media_title'],'mediagallery','media_title') : '',
        'album_title'   =>  ($sortID > 0 ? $LANG_MG03['search_results'] : $MG_albums[$aid]->title),
        'media_desc'    =>  (isset($media[$mediaObject]['media_desc']) && $media[$mediaObject]['media_desc'] != ' ' ) ? $media_desc : '',
        'artist'        =>  (isset($media[$mediaObject]['artist'])) ? $media[$mediaObject]['artist'] : '',
        'media_time'    =>  $media_date[0],
        'upload_time'   =>  $upload_date[0],
        'media_views'   =>  ($MG_albums[$aid]->enable_views ? $media[$mediaObject]['media_views'] : ''),
        'media_comments' => ($MG_albums[$aid]->enable_comments ? $media[$mediaObject]['media_comments'] . '<br />' : ''),
        'pagination'    =>  ($sortID > 0 ? '' : generate_pic_pagination($_MG_CONF['site_url'] . "/media.php?f=" . ($full ? '1' : '0') . "&amp;sort=" . $sortOrder, $nRows, 1, $mediaObject, $media, TRUE)),
        'media_number'  =>  sprintf("%s %d %s %d", $LANG_MG03['image'], $mediaObject + 1 , $LANG_MG03['of'], $total_media ),
        'jumpbox'       =>  $album_jumpbox,
        'edit_item'     =>  $edit_item,
        'site_url'      =>  $_MG_CONF['site_url'],
        'lang_prev'     =>  $LANG_MG03['previous'],
        'lang_next'     =>  $LANG_MG03['next'],
        'next_link'     =>  $nextLink,
        'prev_link'     =>  $prevLink,
        'image_height'  =>  $raw_image_height,
        'image_width'   =>  $raw_image_width,
        'left_side'     =>  intval( $raw_image_width / 2 ) - 1,
        'right_side'    =>  intval( $raw_image_width / 2 ),
        'raw_image'     =>  $raw_image,
        'media_link_start' => $media_link_start,
        'media_link_end'  => $media_link_end,

        'raw_link_url'  =>  $raw_link_url,
        'album_link'    =>  $MG_albums[$aid]->getPath(1,$sortOrder,$page),
        'item_number'   =>  $mediaObject + 1,
        'total_items'   =>  $total_media,
        'lang_of'       =>  $LANG_MG03['of'],
        'album_link'    =>  $album_link,
    ));
    $outputHandle->addMeta('property','og:image',$raw_image);
    $outputHandle->addMeta('property','og:image:width',$raw_image_width);
    $outputHandle->addMeta('property','og:image:height',$raw_image_height);
    $outputHandle->addMeta('property','og:image:type',$media[$mediaObject]['mime_type']);

    $shareImage = '';

    // look for twitter social site config
    if ( $media[$mediaObject]['media_type'] == 0 ) { // only for images
        $twitterSiteUser = '';
        $sql = "SELECT * FROM {$_TABLES['social_follow_services']} as ss LEFT JOIN
                {$_TABLES['social_follow_user']} AS su ON ss.ssid = su.ssid
                WHERE su.uid = -1 AND ss.enabled = 1 AND ss.service_name='twitter'";
        $result = DB_query($sql);
        $numRows = DB_numRows($result);
        if ( $numRows > 0 ) {
            $row = DB_fetchArray($result);
            $twitterSiteUser = $row['ss_username'];
            $outputHandle->addMeta('property','twitter:card','summary_large_image');
            $outputHandle->addMeta('property','twitter:site','@'.$twitterSiteUser);
            $outputHandle->addMeta('property','twitter:title',$ptitle);
            $imageDesc = (isset($media[$mediaObject]['media_desc']) && $media[$mediaObject]['media_desc'] != ' ' ) ? $media_desc : '';
            $outputHandle->addMeta('property','twitter:description',$imageDesc);
            $outputHandle->addMeta('property','twitter:image',$raw_image);
            $shareImage = $raw_image;
        }
    }

    $social_icons = \glFusion\Social\Social::getShareIcons($ptitle,'',$permalink,$shareImage,'mediagallery');
    $T->set_var('social_share',$social_icons);

    $getid3link = '';
    $getid3linkend = '';

    $T->set_var(array(
        'getid3'    => $getid3link,
        'getid3end' => $getid3linkend,
    ));
    if ( $getid3link != '' ) {
        $T->set_var('media_properties',$LANG_MG03['media_properties']);
    } else {
        $T->set_var('media_properties','');
    }

    if ( $MG_albums[$aid]->enable_keywords == 1 && !empty($media[$mediaObject]['media_keywords'])) {
        $kwText = '';
        $keyWords = array();
        $keyWords = explode(' ',$media[$mediaObject]['media_keywords']);
        $numKeyWords = count($keyWords);
        for ( $i=0;$i<$numKeyWords;$i++ ) {
            $keyWords[$i] = str_replace('"',' ',$keyWords[$i]);
            $searchKeyword = $keyWords[$i];
            $keyWords[$i] = str_replace('_',' ',$keyWords[$i]);
            $kwText .= '<a href="' . $_MG_CONF['site_url'] . '/search.php?mode=search&amp;swhere=1&amp;keywords=' . $searchKeyword . '&amp;keyType=any">' . $keyWords[$i] . '</a> ';
        }
        $T->set_var(array(
            'media_keywords'    => $kwText,
            'lang_keywords'     => $LANG_MG01['keywords']
        ));
    } else {
        $T->set_var(array(
            'media_keywords'    => '',
            'lang_keywords'     => '',
         ));
    }

    if ( $media[$mediaObject]['media_user_id'] == '' || !isset($media[$mediaObject]['media_user_id'] ) ) {
        $media[$mediaObject]['media_user_id'] = 0;
    }
	if ($_CONF['show_fullname']) {
		$displayname = 'fullname';
	} else {
		$displayname = 'username';
	}
    $owner_name = DB_getItem ($_TABLES['users'],$displayname, "uid = {$media[$mediaObject]['media_user_id']}");
    if ( empty($owner_name) || $owner_name == '') {
         $owner_name = DB_getItem ($_TABLES['users'],'username', "uid = {$media[$mediaObject]['media_user_id']}");
         if (empty($owner_name) || $owner_name == '' ) {
            $owner_name = 'unknown';
        }
    }
    if ( $owner_name != 'unknown' && $media[$mediaObject]['media_user_id'] != 1) {
        $owner_link = '<a href="' . $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $media[$mediaObject]['media_user_id'] . '">' . $owner_name . '</a>';
    } else {
        $owner_link = $owner_name;
    }
    $T->set_var('owner_username',$owner_link);

    if ( ($MG_albums[$aid]->exif_display==2 || $MG_albums[$aid]->exif_display==3) && $media[$mediaObject]['media_type']==0 ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-exif.php';

        $haveEXIF = MG_haveEXIF($media[$mediaObject]['media_id']);
        if ( $haveEXIF ) {
            $T->set_var(array(
                'property'      =>  $_MG_CONF['site_url'] . '/property.php?mid=' . $media[$mediaObject]['media_id'],
                'lang_property' =>  $LANG_MG04['exif_header'],
            ));
        }
    }

    if ($MG_albums[0]->owner_id || $_MG_CONF['enable_media_id'] == 1) {
        $T->set_var(array(
            'media_id'  => $media[$mediaObject]['media_id']
        ));
    }

    // Language specific vars

    $T->set_var(array(
        'lang_comments'     => ($MG_albums[$aid]->enable_comments ? $LANG_MG03['comments'] : ''),
        'lang_views'        => ($MG_albums[$aid]->enable_views ? $LANG_MG03['views'] : ''),
        'lang_title'        => $LANG_MG01['title'],
        'print_shutterfly'  => $LANG_MG03['print_shutterfly'],
        'lang_uploaded_by'  => $LANG_MG01['uploaded_by'],
        'album_id'          => $aid,
        'lang_search'       => $LANG_MG01['search'],
    ));

    if ( ($MG_albums[$aid]->exif_display == 1 || $MG_albums[$aid]->exif_display == 3) && ($media[$mediaObject]['media_type'] == 0) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-exif.php';
        $haveEXIF = MG_haveEXIF($media[$mediaObject]['media_id']);

        if ( $haveEXIF ) {
            $exifData = MG_readEXIF($media[$mediaObject]['media_id'],2);

            $T->set_var(array(
                'exif_info'         => $exifData
            ));
        }
    }

    if ( $sortID == 0 ) {

        if ( ($MG_albums[$aid]->enable_postcard == 1 && !COM_isAnonUser() ) || ($MG_albums[$aid]->enable_postcard == 2)  ) {
            if ( $media[$mediaObject]['media_type'] == 0 ) {
                $postcard_link = '<a href="' . $_MG_CONF['site_url'] . '/postcard.php?mode=edit&amp;mid=' . $media[$mediaObject]['media_id'] . '"><img src="' . MG_getImageFile('icon_envelopeSmall.gif') . '" alt="' . $LANG_MG03['send_postcard'] . '" style="border:none;"/></a>';
                $postcard_url = $_MG_CONF['site_url'] . '/postcard.php?mode=edit&amp;mid=' . $media[$mediaObject]['media_id'];
                $T->set_var('postcard_url', $postcard_url);
                $T->set_var('postcard_link', $postcard_link);
            }
        }
    }
	PLG_templateSetVars( 'mediagallery', $T);

    $T->parse('output','page');

    $retval .= $T->finish($T->get_var('output'));

    if ($comments) {
        // glFusion Comment support
        $mid = $media[$mediaObject]['media_id'];
        if ($MG_albums[$aid]->enable_comments == 1) {
            USES_lib_comment();
            if ($MG_albums[$aid]->access == 3 || $MG_albums[0]->owner_id) {
                $delete_option = true;
            } else {
                $delete_option = false;
            }
            if ( CMT_getCount('mediagallery', $mid) > 0  || $_MG_CONF['commentbar'] ) {
                $cid        = $mid;
                $page       = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 0;
                if ( isset($_POST['order']) ) {
                    $comorder  =  $_POST['order'] == 'ASC' ? 'ASC' : 'DESC';
                } elseif (isset($_GET['order']) ) {
                    $comorder =  $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
                } else {
                    $comorder = '';
                }
                if ( isset($_POST['mode']) ) {
                    $commode = COM_applyFilter($_POST['mode']);
                } elseif ( isset($_GET['mode']) ) {
                    $commode = COM_applyFilter($_GET['mode']);
                } else {
                    $commode = '';
                }
                $valid_cmt_modes = array('flat','nested','nocomment','nobar');
                if ( !in_array($commode,$valid_cmt_modes) ) {
                    $commode = 'nested';
                }
                $commentbar = CMT_userComments ($cid,$media[$mediaObject]['media_title'],
                              'mediagallery',$comorder,$commode,0,$page,false,$delete_option, 0, $media[$mediaObject]['media_user_id']);
                $retval    .= $commentbar;
            } else {
                $retval .= ' <center><a href="' . $_CONF['site_url'] . '/comment.php?sid=' . $mid . '&amp;title=' . $title . '&amp;pid=0&amp;type=mediagallery' . '">' . $LANG01[60] . '</a></center>';
            }
        }
    }

    return array(strip_tags($media[$mediaObject]['media_title']),$retval,'',$aid);
}
?>