<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | index-all.php                                                            |
// |                                                                          |
// | Main interface to Media Gallery                                          |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2017 by the following authors:                        |
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

/* ---------------------------------------------------
 * Configuration Options:
 * --------------------------------------------------- */

if (!isset($_MG_CONF['album_display_columns']) || $_MG_CONF['album_display_columns'] < 1 ) {
    $_MG_CONF['album_display_columns'] = 1;
}
if ( $_MG_CONF['album_display_rows'] < 1 ) {
    $_MG_CONF['album_display_rows'] = 9;
}

$ImageSkin      = $_MG_CONF['indexskin'];
$sortOrder      = 'DESC';
$displayColumns = $_MG_CONF['album_display_columns'];
$displayRows    = $_MG_CONF['album_display_rows'];
$tnSize         = $_MG_CONF['gallery_tn_size'] ;

/* --- end of configuration options --- */

class mediaItem extends Media {

    function displayThumb( $s, $sortOrder, $force = 0, $imageFrame = '' ) {
        global $_USER, $_CONF, $_MG_CONF, $MG_albums, $_TABLES, $_MG_USERPREFS, $LANG_MG03, $LANG_MG01, $glversion,$ratedIds;
        global $tnSize;

        $retval = '';
        $T = new Template( MG_getTemplatePath(0) );

        $T->set_file (array(
            'media_cell_image'      => 'media-fi.thtml',
            'media_comments'        => 'album_page_body_media_cell_comment.thtml',
            'media_views'           => 'album_page_body_media_cell_view.thtml',
            'mp3_podcast'			=> 'mp3_podcast.thtml',
        ));
        $F = new Template($_MG_CONF['template_path']);

        $F->set_var('media_frame',$imageFrame);

        // --- set the default thumbnail

        switch( $this->type ) {
            case 0 :    // standard image
                $default_thumbnail = 'tn/' . $this->filename[0] . '/' . $this->filename . '.' . $this->mime_ext;
                if ( !file_exists($_MG_CONF['path_mediaobjects'] . $default_thumbnail) ) {
                    $default_thumbnail = 'tn/' . $this->filename[0] . '/' . $this->filename . '.jpg';
                }
                break;
            case 1 :    // video file
                switch ( $this->mime_type ) {
                    case 'video/x-flv' :
                        $default_thumbnail = 'placeholder_flv.svg';
                        break;
                    case 'application/x-shockwave-flash' :
                        $default_thumbnail = 'placeholder_flash.svg';
                        break;
                    case 'video/mpeg' :
                    case 'video/x-mpeg' :
                    case 'video/x-mpeq2a' :
        				if ( $_MG_CONF['use_wmp_mpeg'] == 1 ) {
            				$default_thumbnail = 'placeholder_video.svg';
            				break;
            			}
                    case 'video/x-motion-jpeg' :
                    case 'video/quicktime' :
                    case 'video/x-qtc' :
                    case 'audio/mpeg' :
                    case 'video/x-m4v' :
                        $default_thumbnail = 'placeholder_quicktime.svg';
                        break;
                    case 'asf' :
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
                        $default_thumbnail = 'placeholder_video.svg';
                        break;
                    default :
                        $default_thumbnail = 'placeholder_video.svg';
                        break;
                }
                break;
            case 2 :    // music file
                $default_thumbnail = 'placeholder_audio.svg';
                break;
            case 4 :    // other files
                switch ($this->mime_type) {
                    case 'application/zip' :
                    case 'zip' :
                    case 'arj' :
                    case 'rar' :
                    case 'gz'  :
                        $default_thumbnail = 'zip.png';
                        break;
                    case 'pdf' :
                    case 'application/pdf' :
                        $default_thumbnail = 'placeholder_pdf.svg';
                        break;
                    default :
                        if ( isset($_MG_CONF['dt'][$this->mime_ext]) ) {
                            $default_thumbnail = $_MG_CONF['dt'][$this->mime_ext];
                        } else {
                            switch ( $this->mime_ext ) {
                                case 'pdf' :
                                    $default_thumbnail = 'placeholder_pdf.svg';
                                    break;
                                case 'arj' :
                                    $default_thumbnail = 'zip.png';
                                    break;
                                case 'gz' :
                                    $default_thumbnail = 'zip.png';
                                    break;
                                default :
                                    $default_thumbnail = 'generic.png';
                                    break;
                            }
                        }
                        break;
                }
                break;
            case 5 :
                case 'embed' :
					if (preg_match("/youtube/i", $this->remote_url)) {
						$default_thumbnail = 'youtube.png'; // 'placeholder_youtube.svg';
					} else if (preg_match("/google/i", $this->remote_url)) {
						$default_thumbnail = 'googlevideo.png';
					} else if (preg_match("/vimeo/i", $this->remote_url)) {
					    $default_thumbnail = 'placeholder_vimeo.svg';
					} else {
						$default_thumbnail = 'remote.png';
					}
					break;
        }

        if ( $this->tn_attached == 1 ) {
            $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $this->filename[0] .'/tn_' . $this->filename . $ext) ) {
                    $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/tn/'.  $this->filename[0] . '/tn_' . $this->filename . $ext;
                    $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . 'tn/'.  $this->filename[0] . '/tn_' . $this->filename . $ext;
                    break;
                }
            }
        } else {
            $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
        }

        // type == 1 video
        // type == 2 audio
        if ( ($this->type == 1 || $this->type == 2 || $this->type == 5 ) && ($MG_albums[$this->album_id]->playback_type == 0 || $MG_albums[$this->album_id]->playback_type == 1) && $_MG_CONF['popup_from_album'] == 1) {
	        if ( $MG_albums[$this->album_id]->playback_type == 0 ) {
	        	if ( $this->type == 2 ) {
			        // determine what type of player we will use (WMP, QT or Flash)
			        $player = $_MG_CONF['mp3_player'];
			        if ( isset($_MG_USERPREFS['mp3_player']) && $_MG_USERPREFS['mp3_player'] != -1 ) {
				        $player = $_MG_USERPREFS['mp3_player'];
			        }
			        switch ( $player ) {
				        case 0 :	// WMP
				        	$new_y = 260;
				        	$new_x = 340;
				        	break;
				        case 1 :	// QT
				        	$new_y = 25;
				        	$new_x = 350;
				        	break;
				        case 2 :
				        	$new_y = 360;
				        	$new_x = 580;
				        	break;
			        }
			        if ( $this->mime_type == 'audio/mpeg' ) {
			            $new_y = 360;
			            $new_x = 580;
			        }
			        if ( $this->tn_attached == 1 && $player != 2) {
				        $thumbsize = @getimagesize($media_thumbnail_file);
				        $new_y += $thumbsize[0];
				        if ( $tnsize[1] > $new_x ) {
					        $new_x = $thumbsize[1];
				        }
			        }
		            if ( $MG_albums[$this->album_id]->playback_type == 0 ) {
		                $url_display_item = "javascript:showVideo('" . $_MG_CONF['site_url'] . '/video.php?n=' . $this->id . "'," . $new_y . ',' . $new_x . ')';
		            } else {
		                $url_display_item = $_MG_CONF['site_url'] . '/download.php?mid=' . $this->id;
		            }
		            $resolution_x = $new_x;
		            $resolution_y = $new_y;
	            } else { // must be a video...
		            // set the default playback options...
		            $playback_options['play']    = $_MG_CONF['swf_play'];
		            $playback_options['menu']    = $_MG_CONF['swf_menu'];
		            $playback_options['quality'] = $_MG_CONF['swf_quality'];
		            $playback_options['height']  = $_MG_CONF['swf_height'];
		            $playback_options['width']   = $_MG_CONF['swf_width'];
		            $playback_options['loop']    = $_MG_CONF['swf_loop'];
		            $playback_options['scale']   = $_MG_CONF['swf_scale'];
		            $playback_options['wmode']   = $_MG_CONF['swf_wmode'];
		            $playback_options['allowscriptaccess'] = $_MG_CONF['swf_allowscriptaccess'];
		            $playback_options['bgcolor']    = $_MG_CONF['swf_bgcolor'];
		            $playback_options['swf_version'] = $_MG_CONF['swf_version'];
		            $playback_options['flashvars']   = $_MG_CONF['swf_flashvars'];

		            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($this->id) . "'");
		            while ( $poRow = DB_fetchArray($poResult) ) {
		                $playback_options[$poRow['option_name']] = $poRow['option_value'];
		            }

		            if ( $this->resolution_x > 0 ) {
		                $resolution_x = $this->resolution_x;
		                $resolution_y = $this->resolution_y;
		            } else {
		                if ( $this->media_resolution_x == 0 && $this->remote_media != 1) {
                            $size = @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $this->filename[0] . '/' . $this->filename . '.' . $this->mime_ext);
                            // skip files over 8M in size..
                            if ( $size < 8388608 ) {
    		                    $ThisFileInfo = MG_getMediaMetaData($_MG_CONF['path_mediaobjects'] . 'orig/' . $this->filename[0] . '/' . $this->filename . '.' . $this->mime_ext);
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
    		                        $sql = "UPDATE " . $_TABLES['mg_media'] . " SET media_resolution_x=" . intval($resolution_x) . ",media_resolution_y=" . intval($resolution_y) . " WHERE media_id='" . DB_escapeString($this->id) . "'";
    		                        DB_query( $sql,1 );
    		                    }
                            }
		                } else {
		                    $resolution_x = $this->resolution_x;
		                    $resolution_y = $this->resolution_y;
		                }
		            }
		            $resolution_x = $playback_options['width'];
		            $resolution_y = $playback_options['height'];
		            if ( $resolution_x < 1 || $resolution_y < 1 ) {
		                $resolution_x = 480;
		                $resolution_y = 320;
		            } else {
		                $resolution_x = $resolution_x + 40;
		                $resolution_y = $resolution_y + 40;
		            }
	            	if ( $this->mime_type == 'video/x-flv' && $_MG_CONF['use_flowplayer'] != 1) {
	            	    $resolution_x = $resolution_x + 60;
		            	if ( $resolution_x < 590 ) {
			            	$resolution_x = 590;
		            	}
		            	$resolution_y = $resolution_y + 80;
		            	if ( $resolution_y < 500 ) {
		            	    $resolution_y = 500;
		                }
	            	}
	            	if ( $this->type == 5 ) {
		            	$resolution_x = 460;
		            	$resolution_y = 380;
	            	}
	                $url_display_item = "javascript:showVideo('" . $_MG_CONF['site_url'] . '/video.php?n=' . $this->id . "'," . $resolution_y . ',' . $resolution_x . ')';
                }
            } else {
                $url_display_item = $_MG_CONF['site_url'] . '/download.php?mid=' . $this->id;
            }
            // check to see if comments and rating are enabled, if not, put a link to edit...

	        if ( $MG_albums[$this->album_id]->access == 3 ) {
	    		$T->set_var(array(
	    			'edit_link'		=> '<br/><a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=mediaedit&amp;s=1&amp;album_id=' . $this->album_id . '&amp;mid=' . $this->id . '">' . $LANG_MG01['edit'] . '</a>',
	    		));
    		} else {
	    		$T->set_var(array(
	    			'edit_link'	=> '',
	    		));
    		}
        } else {
            if ( $MG_albums[$this->album_id]->useAlternate == 1 && $this->type != 5 && !empty($this->remote_url) ) {
                $url_display_item = $this->remote_url;
            } else {
                $url_display_item  = $_MG_CONF['site_url'] . '/media.php?f=0' . '&amp;sort=' . $sortOrder . '&amp;s=' . $this->id ;
            }
        }
        if ( $this->type == 4 ) { // other
            $url_display_item  = $_MG_CONF['site_url'] . '/download.php?mid=' . $this->id ;
        }

        if ( strstr($media_thumbnail_file,'.svg') ) {
            $media_size = array($MG_albums[$this->album_id]->tnWidth,$MG_albums[$this->album_id]->tnHeight);
        } else {
            $media_size        = @getimagesize($media_thumbnail_file);
        }

        if ( $media_size == false ) {
            $default_thumbnail    = 'placeholder.svg';
            $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
            $media_size           = array(200,200); //@getimagesize($media_thumbnail_file);
        }

	if ( $_MG_CONF['use_upload_time'] == 1 ) {
        	$media_time        = MG_getUserDateTimeFormat($this->upload_time);
        } else {
        	$media_time        = MG_getUserDateTimeFormat($this->time);
        }
	    // for index-all sorted by upload time, always display upload time
        $media_time  = MG_getUserDateTimeFormat($this->time);
        $upload_time = MG_getUserDateTimeFormat($this->upload_time);

        $url_media_item = $url_display_item;

        // -- decide what thumbnail size to use, small, medium, large...

        $tn_size = $tnSize;

        switch ($tn_size ) {
            case '0' :      //small
                $tn_height = 100;
                $tn_width  = 100;
                break;
            case '1' :      //medium
                $tn_height = 150;
                $tn_width  = 150;
                break;
            case '2' :
                $tn_height = 200;
                $tn_width  = 200;
                break;
            case '3' :
            	$tn_height = $MG_albums[$this->album_id]->tnHeight;
            	$tn_width  = $MG_albums[$this->album_id]->tnWidth;
            	if ( $tn_height == 0 ) {
            	    $tn_height = 200;
            	}
            	if ( $tn_width == 0 ) {
            	    $tn_width = 200;
            	}
            	break;
            default :
                $tn_height = 150;
                $tn_width  = 150;
                break;
        }
        if ( $media_size[0] > $media_size[1] ) { // landscape
            $ratio = $media_size[0] / $tn_width;
            $newwidth = $tn_width;
            $newheight = round($media_size[1] / $ratio);
        } else {
            $ratio = $media_size[1] / $tn_height;
            $newheight = $tn_height;
            $newwidth = round($media_size[0] / $ratio);
        }

        if ( $media_size[0] > $media_size[1] ) {
            $ratio = $media_size[0] / 50;
            $smallwidth = 50;
            $smallheight = round($media_size[1] / $ratio);
        } else {
            $ratio = $media_size[1] / 50;
            $smallheight = 50;
            $smallwidth = round($media_size[0] / $ratio);
        }

        if ( $this->owner_id != "" && $this->owner_id > 1 ) {
	        $username = DB_getItem($_TABLES['users'],'username',"uid=".(int) $this->owner_id);
	        $fullname = DB_getItem($_TABLES['users'],'fullname',"uid=".(int) $this->owner_id);
        } else {
	        $username = 'anonymous';
	        $fullname = 'anonymous';
        }

        if ( !isset($resolution_x) ) {
            $resolution_x = $newwidth;
        }
        if ( !isset($resolution_y) ) {
            $resolution_y = $newheight;
        }

        if ( $this->mime_type == 'audio/mpeg' && $MG_albums[$this->album_id]->mp3ribbon ) {
	    	$T->set_var(array(
	    		'mp3_file'          => $_MG_CONF['mediaobjects_url'] . '/orig/' . $this->filename[0] . '/' . $this->filename . '.' . $this->mime_ext,
	            'site_url'			=> $_MG_CONF['site_url'],
	            'id'				=> $this->mime_ext . rand(),
	    	));

            $T->parse('mp3_podcast','mp3_podcast');
    	} else {
	    	$T->set_var(array(
	    		'mp3_podcast' => '',
	    	));
    	}

        $fs_bytes = @filesize( $_MG_CONF['path_mediaobjects'] . 'orig/' . $this->filename[0] . '/' . $this->filename . '.' . $this->mime_ext);
        $fileSize = MG_get_size($fs_bytes);

        $direct_url = 'disp/' . $this->filename[0] . '/' . $this->filename . '.' . $this->mime_ext;
        if ( !file_exists($_MG_CONF['path_mediaobjects'] . $direct_url) ) {
            $direct_url = 'disp/' . $this->filename[0] . '/' . $this->filename . '.jpg';
        }

        if ($MG_albums[$this->album_id]->access == 3 ) {
            $edit_item = '<a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=mediaedit&amp;s=1&amp;album_id=' . $this->album_id . '&amp;mid=' . $this->id . '">' . $LANG_MG01['edit'] . '</a>';
        } else {
            $edit_item = '';
        }

    	$L = new Template( $_MG_CONF['template_path'] );
    	$L->set_file('media_link','medialink.thtml');
    	$L->set_var('href',$url_media_item);
    	if ( $this->type == 0 ) {
    	    if ( $this->remote == 1 ) {
        	    $L->set_var('hrefdirect',$this->remote_url);
        	} else {
        	    $L->set_var('hrefdirect',$_MG_CONF['mediaobjects_url'] . '/' . $direct_url);
        	}
    	}

        $caption = PLG_replaceTags(str_replace('$','&#36;',$this->title));
        if ($this->owner_id == $_USER['uid'] ||
                SEC_hasRights('mediagallery.admin')) {
            $caption .= '<br />('.$this->id.')';
        }
        $L->set_var('caption', $caption);
    	$L->set_var('id','id' . rand());

    	$L->parse('media_link_start','media_link');
    	$media_start_link = $L->finish($L->get_var('media_link_start'));

        $T->set_var(array(
    		'play_now'		    => '',
    		'download_now'	    => $_MG_CONF['site_url'] . '/download.php?mid=' . $this->id,
    		'play_in_popup'     => "javascript:showVideo('" . $_MG_CONF['site_url'] . '/video.php?n=' . $this->id . "'," . $resolution_y . ',' . $resolution_x . ')',
            'row_height'        => $tn_height + 40,
            'media_title'       => PLG_replaceTags($this->title),
            'media_description' => PLG_replaceTags(nl2br($this->description)),
            'media_tag'         => strip_tags($this->title),
            'media_time'        => $media_time[0],
            'upload_time'       => $upload_time[0],
            'media_owner'	    => $username,
            'owner_fullname'	=> $fullname,
            'site_url'		    => $_MG_CONF['site_url'],
            'lang_published'	=> $LANG_MG03['published'],
            'lang_album'        => $LANG_MG01['album'],
            'lang_on'		    => $LANG_MG03['on'],
            'lang_posted'       => $LANG_MG01['posted'],
            'lang_hyphen'	    => $this->album == '' ? '' : '-',
            'lang_artist'       => $LANG_MG01['artist'],
            'media_link_start'  => $media_start_link,
            'media_link_end'    => '</a>',
            'artist'		    => $this->artist,
//            'copyright_name'    => ($this->artist != "" ? $this->artist : $fullname),
            'copyright_name'    => ($this->artist != "" ? $this->artist : ''),
            'musicalbum'	    => $this->album != '' ? $this->album : '',
            'genre'		        => $this->genre != '' ? $this->genre : '',
            'alt_edit_link'     => $edit_item,
            'filesize'          => $fileSize,
            'media_id'          => $this->id,
            'album_name_link'   => '<a href="'.$_MG_CONF['site_url'].'/album.php?aid='.$this->album_id.'">'.$MG_albums[$this->album_id]->title.'</a>',
            'media_thumbnail'   => $media_thumbnail,
            'url_display_item'  => $url_display_item,
        ));

        // frame template variables
        $F->set_var(array(
            'media_id'          => $this->id,
            'media_link_start'  => '<a href="'.$url_media_item.'">',
            'media_link_end'    => '</a>',
            'url_media_item'    =>  $url_media_item,
            'url_display_item'  =>  $url_display_item,
            'media_thumbnail'   =>  $media_thumbnail,
            'media_size'        =>  'width="' . $newwidth . '" height="' . $newheight . '"',
            'media_height'      =>  $newheight,
            'media_width'       =>  $newwidth,
            'border_width'      =>  $newwidth + 15,
            'border_height'     =>  $newheight + 15,
            'row_height'        =>  $tn_height + 40,
            'frWidth'           =>  $newwidth  - $MG_albums[$this->album_id]->frWidth,
            'frHeight'          =>  $newheight - $MG_albums[$this->album_id]->frHeight,
            'media_tag'         =>  strip_tags($this->description),
            'filesize'          =>  $fileSize,
        ));

        $F->parse('media','media_frame');
        $media_item_thumbnail = $F->finish($F->get_var('media'));

        $T->set_var(array(
            'media_item_thumbnail' => $media_item_thumbnail,
        ));

        if ( $MG_albums[$this->album_id]->enable_comments) {
            if ( $this->type == 4 || ($this->type == 1 && $MG_albums[$this->album_id]->playback_type != 2) || ($this->type == 2 && $MG_albums[$this->album_id]->playback_type != 2) || ($this->type == 5 && $MG_albums[$this->album_id]->playback_type != 2)) {
                $cmtLink  = '<a href="' . $_MG_CONF['site_url'] . '/media.php?f=0' . '&amp;sort=' . $sortOrder . '&amp;s=' . $this->id . '">' . $LANG_MG03['comments'] . '</a>';
                $cmtLink_alt = $cmtLink;
            }  else {
                $cmtLink = $LANG_MG03['comments'];
                $cmtLink_alt  = '<a href="' . $_MG_CONF['site_url'] . '/media.php?f=0' . '&amp;sort=' . $sortOrder . '&amp;s=' . $this->id . '">' . $LANG_MG03['comments'] . '</a>';
            }
            $T->set_var(array(
                'media_comments_count'  =>  $this->comments,
                'lang_comments'         =>  $cmtLink_alt, // use $cmtLink for just text
                'lang_comments_hot'     =>  $cmtLink_alt,
            ));
            $T->parse('media_comments','media_comments');
        }

        if ( $MG_albums[$this->album_id]->enable_views ) {
            $T->set_var(array(
                'media_views_count'     =>  $this->views,
                'lang_views'            =>  $LANG_MG03['views']
            ));
            $T->parse('media_views','media_views');
        }

		PLG_templateSetVars( 'mediagallery', $T);
        $T->parse('media_cell','media_cell_image');
        $retval = $T->finish($T->get_var('media_cell'));
        return $retval;
    }
}

function getAlbumList( $aid, $first ) {
    global $MG_albums;

    $albumList = '';

    $children = $MG_albums[$aid]->getChildren();
    $nrows = count($children);

    if( $nrows == 0 ) {
        return $albumList;
    }

    $checkCounter = 0;

    $aCount = 0;
    $achild = array();
    $albumList = '';
    for ($i=0; $i<$nrows;$i++) {
        $access = $MG_albums[$children[$i]]->access;
        if ( $access == 0 || ( $MG_albums[$children[$i]]->hidden == 1 && $access != 3)) {
            // no op
        } else {
            if ( $first ) {
                $albumList .= ',';
            } else {
                $first = 1;
            }
            $albumList .= "'".$MG_albums[$children[$i]]->id."'";
            $aCount++;
        }
        $albumList .= getAlbumList($MG_albums[$children[$i]]->id,$first);
    }
    return $albumList;
}

function MG_indexAll()
{
    global $_USER, $_MG_CONF, $_CONF, $_TABLES, $MG_albums,
           $LANG_MG00, $LANG_MG01, $LANG_MG02, $LANG_MG03,
           $themeStyle, $ImageSkin, $sortOrder, $displayColumns,
           $displayRows, $tnSize, $level, $album_jumpbox;

    $album_id = 0;
    if (isset($_GET['aid']) ) {
        $album_id  = (int) COM_applyFilter($_GET['aid'],true);
    }
    $page = 0;

    if (isset($_GET['page']) ) {
        $page      = (int) COM_applyFilter($_GET['page'],true);
    }

    if ( $page != 0 ) {
        $page = $page - 1;
    }

    $lbSlideShow = '';
    $errorMessage = '';

    $columns_per_page   = $displayColumns;
    $rows_per_page      = $displayRows;

    $media_per_page     = $columns_per_page * $rows_per_page;

    // image frame setup

    $nFrame = new mgFrame();
    $nFrame->constructor( $ImageSkin );
    $imageFrameTemplate = $nFrame->getTemplate();
    $frWidth = $nFrame->frame['wHL'] + $nFrame->frame['wHR'];
    $frHeight = $nFrame->frame['hVT'] + $nFrame->frame['hVB'];
    $fCSS= $nFrame->getCSS();

    // Let's build our admin menu options

    $showAdminBox = 0;

    $admin_box  = '<form name="adminbox" id="adminbox" action="' . $_MG_CONF['site_url'] . '/admin.php" method="get" style="margin:0;padding:0;">' . LB;
    $admin_box .= '<div>';
    $admin_box .= '<select onchange="javascript:forms[\'adminbox\'].submit();" name="mode">' . LB;
    $admin_box .= '<option label="' . $LANG_MG01['options'] . '" value="">' . $LANG_MG01['options'] . '</option>' . LB;
    $disabled = '';
    if ( ($MG_albums[0]->member_uploads || $MG_albums[0]->access == 3) && (!COM_isAnonUser() ) )  {
        if ( count($MG_albums) == 1 ) {
            $disabled = ' disabled="disabled" ';
        }
        $admin_box_item .= '<option value="upload"'.$disabled.'>' . $LANG_MG01['add_media'] . '</option>' . LB;
        $showAdminBox = 1;
    }
    if ( $MG_albums[0]->owner_id ) {
        $admin_box .= '<option value="albumsort">'  . $LANG_MG01['sort_albums'] . '</option>' . LB;
        $admin_box .= '<option value="globalattr">' . $LANG_MG01['globalattr'] . '</option>' . LB;
        $admin_box .= '<option value="globalperm">' . $LANG_MG01['globalperm'] . '</option>' . LB;
        $queue_count = DB_count($_TABLES['mg_media_album_queue']);
        $admin_box .= '<option value="moderate">' . $LANG_MG01['media_queue'] . ' (' . $queue_count . ')</option>' . LB;
        $admin_box .= '<option value="wmmanage">' . $LANG_MG01['wm_management'] . '</option>' . LB;
        $admin_box .= '<option value="create">' . $LANG_MG01['create_album'] . '</option>' . LB;
        $showAdminBox = 1;
    } elseif ( $MG_albums[0]->access == 3 ) {
        $admin_box .= '<option value="create">' . $LANG_MG01['create_album'] . '</option>' . LB;
        $showAdminBox = 1;
    } elseif ( $_MG_CONF['member_albums'] == 1 && ( isset($_USER['uid']) && $_USER['uid'] > 1 ) && $_MG_CONF['member_album_root'] == 0 && $_MG_CONF['member_create_new']) {
        $admin_box .= '<option value="create">' . $LANG_MG01['create_album'] . '</option>' . LB;
        $showAdminBox = 1;
    }

    $admin_box .= '</select>' . LB;
    $admin_box .= '<input type="hidden" name="album_id" value="0"/>' . LB;
    $admin_box .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '"/>' . LB;
    $admin_box .= '</div>';
    $admin_box .= '</form>';

    if ( $showAdminBox == 0 ) {
        $admin_box = '';
    }

    // construct the album jumpbox...

    $level = 0;
    $album_jumpbox = '<form name="jumpbox" id="jumpbox" action="' . $_MG_CONF['site_url'] . '/album.php' . '" method="get" style="margin:0;padding:0"><div>';
    $album_jumpbox .= $LANG_MG03['jump_to'] . ':&nbsp;<select name="aid" onchange="forms[\'jumpbox\'].submit()">';
    $MG_albums[0]->buildJumpBox(0);
    $album_jumpbox .= '</select>';
    $album_jumpbox .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '"/>';
    $album_jumpbox .= '<input type="hidden" name="page" value="1"/>';
    $album_jumpbox .= '</div></form>';

    // initialize our variables

    $total_media = 0;
    $arrayCounter = 0;
    $total_object_count = 0;
    $mediaObject = array();

    $begin = $media_per_page * $page;
    $end   = $media_per_page;
    $MG_media = array();

    // loop thru all the albums and build a list of valid albums that the user can see

    $first = 0;
    $albumList = getAlbumList($album_id,$first);


    $orderBy = ' ORDER BY m.media_upload_time '.$sortOrder;

    if ( $albumList != '' ) {
        $sql = "SELECT COUNT(*) AS total FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE ma.album_id IN (".$albumList.") " . $orderBy;
        $result = DB_query($sql);
        $row    = DB_fetchArray($result);
        $cCount = $row['total'];
    } else {
        $cCount = 0;
    }

    if ( $albumList != '' ) {

        $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE ma.album_id IN (".$albumList.") " . $orderBy;
        $sql .= ' LIMIT ' . $begin . ',' . $end;

        $result = DB_query( $sql );
        $nRows  = DB_numRows( $result );
    } else {
        $nRows = 0;
    }
    $mediaRows = 0;
    $lbss_count = 0;
    $posCount = 0;
    if ( $nRows > 0 ) {
        while ( $row = DB_fetchArray($result)) {
            $media = new MediaItem();
            $media->constructor($row,$row['album_id']);
            $MG_media[$arrayCounter] = $media;
            $MG_albums[$row['album_id']]->imageFrameTemplate = $imageFrameTemplate;
            $arrayCounter++;
            $mediaRows++;
        }
    }

    $total_media = $total_media + $mediaRows;
    $total_items_in_album = $cCount;
    $total_pages = ceil($total_items_in_album/($media_per_page));

    if ( $page >= $total_pages ) {
        $page = $total_pages - 1;
    }

    $start = $page * $media_per_page;

    $current_print_page = ( floor( $start / $media_per_page ) + 1 );
    $total_print_pages  = ceil($total_items_in_album/($media_per_page));


    if ( $current_print_page == 0 ) {
        $current_print_page = 1;
    }
    if ( $total_print_pages == 0 ) {
        $total_print_pages = 1;
    }

    // now build the admin select...

    $admin_box = '';
    $admin_box = '<form name="adminbox" id="adminbox" action="' . $_MG_CONF['site_url'] . '/admin.php" method="get" style="margin:0;padding:0">';
    $admin_box .= '<div><input type="hidden" name="album_id" value="' . $album_id . '"/>';
    $admin_box .= '<select name="mode" onchange="forms[\'adminbox\'].submit()">';
    $admin_box .= '<option label="' . $LANG_MG01['options'] .'" value="">' . $LANG_MG01['options'] .'</option>';
    $admin_box .= '<option value="search">' . $LANG_MG01['search'] . '</option>';

    $uploadMenu = 0;
    $adminMenu  = 0;
    if ( $MG_albums[0]->owner_id ) {
        $uploadMenu = 1;
        $adminMenu  = 1;
    } else if ( $MG_albums[$album_id]->access == 3 ) {
        $uploadMenu = 1;
        $adminMenu  = 1;
        if ( $_MG_CONF['member_albums'] ) {
            if ( $_MG_USERPREFS['active'] != 1 ) {
                $uploadMenu = 0;
                $adminMenu  = 0;
            } else {
                $uploadMenu = 1;
                $adminMenu  = 1;
            }
        }
    } else if ( $MG_albums[$album_id]->member_uploads == 1 && isset($_USER['uid']) && $_USER['uid'] >= 2 ) {
        $uploadMenu = 1;
        $adminMenu  = 0;
    }
    if ( $uploadMenu == 1 ) {
        $admin_box .= '<option value="upload">' . $LANG_MG01['add_media'] . '</option>';
    }
    if ( $adminMenu == 1 ) {
        $admin_box .= '<option value="create">' . $LANG_MG01['create_album'] . '</option>';
    } elseif ($_MG_CONF['member_albums'] == 1 && !empty ($_USER['username']) && $_MG_CONF['member_create_new'] == 1 && $_MG_USERPREFS['active'] == 1 && ($album_id == $_MG_CONF['member_album_root'])) {
        $admin_box .= '<option value="create">' . $LANG_MG01['create_album'] . '</option>';
        $adminMenu = 1;
    }
    // now check for moderation capabilities....
    if ( $MG_albums[$album_id]->member_uploads == 1 && $MG_albums[$album_id]->moderate == 1 ){
        // check to see if we are in the album_mod_group
        if ( SEC_inGroup($MG_albums[$album_id]->mod_group_id) || $MG_albums[0]->owner_id /*SEC_hasRights('mediagallery.admin')*/ ) {
            $queue_count = DB_count($_TABLES['mg_media_album_queue'],'album_id',$album_id);
            $admin_box .= '<option value="moderate">' . $LANG_MG01['media_queue'] . ' (' . $queue_count . ')</option>';
            $adminMenu = 1;
        }
    }

    $admin_box .= '</select>';
    $admin_box .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '" style="padding:0px;margin:0px;"/>';

    $admin_box .= '</div></form>';

    if ( $uploadMenu == 0 && $adminMenu == 0 ) {
        $admin_box = '';
    }

    if ( $MG_albums[$album_id]->enable_sort == 1 ) {
        $sort_box = '<form name="sortbox" id="sortbox" action="' . $_MG_CONF['site_url'] . '/album.php" method="get" style="margin:0;padding:0"><div>';
        $sort_box .= '<input type="hidden" name="aid" value="' . $album_id . '"/>';
        $sort_box .= '<input type="hidden" name="page" value="' . $page . '"/>';
        $sort_box .= $LANG_MG03['sort_by'] . '&nbsp;<select name="sort" onchange="forms[\'sortbox\'].submit()">';
        $sort_box .= '<option value="0" ' . ($sortOrder==0 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_default'] . '</option>';
        $sort_box .= '<option value="1" ' . ($sortOrder==1 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_default_asc'] . '</option>';
        $sort_box .= '<option value="2" ' . ($sortOrder==2 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_upload'] . '</option>';
        $sort_box .= '<option value="3" ' . ($sortOrder==3 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_upload_asc'] . '</option>';
        $sort_box .= '<option value="4" ' . ($sortOrder==4 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_capture'] . '</option>';
        $sort_box .= '<option value="5" ' . ($sortOrder==5 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_capture_asc'] . '</option>';
        $sort_box .= '<option value="6" ' . ($sortOrder==6 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_rating'] . '</option>';
        $sort_box .= '<option value="7" ' . ($sortOrder==7 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_rating_asc'] . '</option>';
        $sort_box .= '<option value="8" ' . ($sortOrder==8 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_views'] . '</option>';
        $sort_box .= '<option value="9" ' . ($sortOrder==9 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_views_asc'] . '</option>';
        $sort_box .= '<option value="10" ' . ($sortOrder==10 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_alpha'] . '</option>';
        $sort_box .= '<option value="11" ' . ($sortOrder==11 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_alpha_asc'] . '</option>';

        $sort_box .= '</select>';
        $sort_box .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '"/>';
        $sort_box .= '</div></form>';
    } else {
        $sort_box = '';
    }

    $owner_id = $MG_albums[$album_id]->owner_id;

    if ( $owner_id == '' || !isset($MG_albums[$album_id]->owner_id) ) {
        $owner_id = 0;
    }
    $ownername = DB_getItem ($_TABLES['users'],'username', "uid=".(int) $owner_id);
    $album_last_update = MG_getUserDateTimeFormat($MG_albums[$album_id]->last_update);

    $T = new Template( MG_getTemplatePath(0) );

    $T->set_file (array(
        'page'      => 'index-all.thtml',
    ));
    $T->set_var(array(
        'site_url'              => $_MG_CONF['site_url'],
//        'album_title'           => "All Photos - Sorted by Post Date", // PLG_replaceTags($MG_albums[$album_id]->title),
        'album_title'           => $LANG_MG03['all_media'],
        'table_columns'         => $columns_per_page,
        'table_column_width'    => intval(100 / $columns_per_page) . '%',
        'top_pagination'        => COM_printPageNavigation($_MG_CONF['site_url'] . '/index.php?aid='.$album_id,$page+1,ceil($total_items_in_album  / $media_per_page)),
        'bottom_pagination'     => COM_printPageNavigation($_MG_CONF['site_url'] . '/index.php?aid='.$album_id, $page+1,ceil($total_items_in_album  / $media_per_page)),
        'page_number'           => sprintf("%s %d %s %d",$LANG_MG03['page'], $current_print_page, $LANG_MG03['of'], $total_print_pages),
        'jumpbox'               => $album_jumpbox,
        'album_id'              => $album_id,
    	'lbslideshow'           => $lbSlideShow,
    	'album_description' 	=> ($MG_albums[$album_id]->display_album_desc ? PLG_replaceTags($MG_albums[$album_id]->description) : ''),
    	'album_id_display'  	=> ($MG_albums[0]->owner_id || $_MG_CONF['enable_media_id'] == 1 ? $LANG_MG03['album_id_display'] . $album_id : ''),
    	'select_adminbox'		=> $admin_box,
    	'select_sortbox'		=> $sort_box,
    	'album_last_update'		=> $album_last_update[0],
    	'album_owner'			=> $ownername,
    	'media_count'			=> $MG_albums[$album_id]->getMediaCount(),
    	'lang_search'           => $LANG_MG01['search'],
    	'table_columns'         => $displayColumns,
    ));

    $T->set_var('select_adminbox',$admin_box);

    if ( $_MG_CONF['rss_full_enabled'] ) {
        $feedUrl = MG_getFeedUrl($_MG_CONF['rss_feed_name'].'.rss');
        $rsslink = '<a href="' . $feedUrl . '"' . ' type="application/rss+xml">';
        $rsslink .= '<img src="' . MG_getImageFile('feed.png') . '" alt="" style="border:none;"/></a>';
        $T->set_var('rsslink', $rsslink);
    } else {
        $T->set_var('rsslink','');
    }

    // completed setting header / footer vars, parse them

    PLG_templateSetVars('mediagallery',$T);

    if ( $total_media == 0 ) {
        $T->set_var(array(
            'lang_no_image'       =>  $LANG_MG03['no_media_objects']
        ));
    }

    //
    // main processing of the album contents.
    //

    $noParse = 0;
    $needFinalParse = 0;

    if ( $total_media > 0 ) {
        $k = 0;

        $T->set_block('page', 'ImageColumn', 'IColumn');
        $T->set_block('page', 'ImageRow','IRow');

        for ( $i = 0; $i < ($media_per_page ); $i += $columns_per_page ) {

            for ($j = $i; $j < ($i + $columns_per_page); $j++) {
                if ($j >= $total_media) {
                    $k = ($i+$columns_per_page) - $j;
                    $m = $k % $columns_per_page;
                    for ( $z = $m; $z > 0; $z--) {
                        $T->set_var(array(
                            'CELL_DISPLAY_IMAGE'  =>  '',
                        ));
                        $T->parse('IColumn', 'ImageColumn',true);
                            $needFinalParse = 1;
                    }
                    if ( $needFinalParse == 1 ) {
                        $T->parse('IRow','ImageRow',true);
                        $T->set_var('IColumn','');
                    }
                    $noParse = 1;
    	            break;
                }
                $previous_image = $i - 1;
                if ( $previous_image < 0 ) {
                    $previous_image = -1;
                }
                $next_image = $i + 1;
                if ( $next_image >= $total_media - 1 ) {
                    $next_image = -1;
                }
                $z = $j+$start;

                $celldisplay = $MG_media[$j]->displayThumb($z,0,0,$imageFrameTemplate);

                if ( $MG_media[$j]->type == 1 ) {
                    $PhotoURL = $_MG_CONF['mediaobjects_url'] . '/disp/' . $MG_media[$j]->filename[0] .'/' . $MG_media[$j]->filename . '.jpg';
                    $T->set_var(array(
                        'URL' => $PhotoURL,
                    ));
                }

                $T->set_var(array(
                    'CELL_DISPLAY_IMAGE'  =>  $celldisplay,
                ));
                $T->parse('IColumn', 'ImageColumn',true);
            }
            if ( $noParse == 1 ) {
                break;
            }
            $T->parse('IRow','ImageRow',true);
            $T->set_var('IColumn','');

        }
    }

    $T->parse('output','page');

    $fCSS= $nFrame->getCSS();
    if ( $fCSS != '' ) {
        $outputHandle = outputHandler::getInstance();
        $outputHandle->addStyle($fCSS);
    }

    $display = MG_siteHeader($LANG_MG00['plugin']);
    $display .= $T->finish($T->get_var('output'));
    $display .= MG_siteFooter();
    echo $display;
}
?>