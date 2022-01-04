<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Media objects class and hanlding
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

class Media {
    var $id;
    var $title;
    var $description;
    var $filename;
    var $keywords;
    var $mime_ext;
    var $mime_type;
    var $time;
    var $views;
    var $comments;
    var $votes;
    var $rating;
    var $tn_attached;
    var $tn_image;
    var $owner_id;
    var $type;
    var $upload_time;
    var $album_id;
    var $cat_id;
    var $watermarked;
    var $artist;
    var $album;
    var $genre;
    var $resolution_x;
    var $resolution_y;
    var $remote;
    var $remote_url;
    var $access;

    function __construct () {
    }

    function constructor( $M, $aid ) {
        global $MG_albums;
        $this->id               = $M['media_id'];
        $this->type             = $M['media_type'];
        if ( $this->type != -1 ) {
            $filter = new \sanitizer();
            $filter->setNamespace('mediagallery','');
            $filter->setReplaceTags(false);
            $filter->setCensorData(true);
            $filter->setPostmode('text');

            $this->title = '';
            $this->description = '';
            if ($MG_albums[$aid]->enable_html ) {
                $filter->setPostmode('html');
            } else {
                $filter->setPostmode('text');
            }
            if (isset($M['media_title'])) {
                $this->title = $filter->filterData($M['media_title']);
            }
            if (isset($M['media_desc'])) {
                $this->description = $filter->filterData($M['media_desc']);
            }
            $this->filename         = $M['media_filename'];
            $this->keywords         = (isset($M['media_keywords']) && $M['media_keywords'] != ' ') ? $M['media_keywords'] : '';
            $this->mime_ext         = $M['media_mime_ext'];
            $this->mime_type        = $M['mime_type'];
            $this->time             = $M['media_time'];
            $this->views            = $M['media_views'];
            $this->comments         = $M['media_comments'];
            $this->votes            = $M['media_votes'];
            $this->rating           = $M['media_rating'];
            $this->tn_attached      = $M['media_tn_attached'];
            $this->tn_image         = $M['media_tn_image'];
            $this->owner_id         = $M['media_user_id'];
            $this->upload_time      = $M['media_upload_time'];
            $this->cat_id           = $M['media_category'];
            $this->watermarked      = $M['media_watermarked'];
            $this->artist			= (isset($M['artist']) && $M['artist'] != ' ') ? $M['artist'] : '';
            $this->album			= (isset($M['album']) && $M['album'] != ' ') ? $M['album'] : '';
            $this->genre			= (isset($M['genre']) && $M['genre'] != ' ') ? $M['genre'] : '';
            $this->resolution_x     = $M['media_resolution_x'];
            $this->resolution_y     = $M['media_resolution_y'];
            $this->remote			= $M['remote_media'];
            $this->remote_url		= (isset($M['remote_url']) && $M['remote_url'] != ' ') ? $M['remote_url'] : '';
            $this->album_id         = $aid;
            $this->setAccessRights();
        }

    }

    function setAccessRights() {
        global $MG_albums;

        $this->access = $MG_albums[$this->album_id]->access;
    }

    function displayThumb( $s, $sortOrder, $force=0, $imageFrame = '' ) {
        global $_USER, $_CONF, $_MG_CONF, $MG_albums, $_TABLES, $_MG_USERPREFS, $LANG_MG03, $LANG_MG01, $glversion,$ratedIds;

        $retval = '';

        $T = new Template( MG_getTemplatePath($this->album_id) );

        if ( $MG_albums[$this->album_id]->display_columns == 1 ) {
        	$media_cell_image_template = 'album_page_body_media_cell_1.thtml';
        } else {
        	$media_cell_image_template = 'album_page_body_media_cell.thtml';
        }
        if ( $force ) {
 			$media_cell_image_template = 'album_page_body_media_cell.thtml';
 		}

        $T->set_file (array(
            'media_cell_image'      => $media_cell_image_template,
            'media_rate_results'    => 'album_page_body_media_cell_rating.thtml',
            'media_comments'        => 'album_page_body_media_cell_comment.thtml',
            'media_views'           => 'album_page_body_media_cell_view.thtml',
            'media_cell_keywords'   => 'album_page_body_media_cell_keywords.thtml',
            'mp3_podcast'			=> 'mp3_podcast.thtml',
        ));

        $F = new Template($_MG_CONF['template_path']);
        $F->set_var('media_frame',$MG_albums[$this->album_id]->imageFrameTemplate);

        // --- set the default thumbnail

        $data_type = '';
        $videoid   = '';
        $url_orig  = '';

        $foundImageDefaultThumbnail = false;

        switch( $this->type ) {
            case 0 :    // standard image
                $data_type = 'image';
                $default_thumbnail = 'tn/' . $this->filename[0] . '/' . $this->filename . '.' . $this->mime_ext;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $this->filename[0] . '/' . $this->filename . $ext) ) {
                        $default_thumbnail      = 'tn/'.  $this->filename[0] . '/' . $this->filename . $ext;
                        $foundImageDefaultThumbnail = true;
                        break;
                    }
                }
                if ($foundImageDefaultThumbnail === false) {
                    $default_thumbnail = 'placeholder.svg';
                }
                if ( $_MG_CONF['discard_original'] == 1 ) {
                    $orig = 'disp';
                } else {
                    $orig = 'orig';
                }
                $default_orig_file = $orig.'/'.$this->filename[0].'/'.$this->filename.'.'.$this->mime_ext;
                if ( file_exists($_MG_CONF['path_mediaobjects'] . $default_orig_file) ) {
                    $url_orig = $_MG_CONF['mediaobjects_url'].'/'.$default_orig_file;
                } else {
                    $url_orig = '';
                }
                break;
            case 1 :    // video file
                switch ( $this->mime_type ) {

                    case 'video/x-flv' :
                        $default_thumbnail = 'placeholder_flv.svg';
                        if ( $MG_albums[$this->album_id]->tnWidth > $MG_albums[$this->album_id]->tnHeight ) {
                            $default_thumbnail = 'placeholder_flv_w.svg';
                        } else {
                            $default_thumbnail = 'placeholder_flv.svg';
                        }

                        break;
                    case 'application/x-shockwave-flash' :
                        if ( $MG_albums[$this->album_id]->tnWidth > $MG_albums[$this->album_id]->tnHeight ) {
                            $default_thumbnail = 'placeholder_flash_w.svg';
                        } else {
                            $default_thumbnail = 'placeholder_flash.svg';
                        }
                        break;
                    case 'video/mpeg' :
                    case 'video/x-mpeg' :
                    case 'video/x-mpeq2a' :
                    case 'video/webm' :
                        if ( $MG_albums[$this->album_id]->tnWidth > $MG_albums[$this->album_id]->tnHeight ) {
                            $default_thumbnail = 'placeholder_video_w.svg';
                        } else {
                            $default_thumbnail = 'placeholder_video.svg';
                        }
                        $orig = 'orig';
                        $default_orig_file = $orig.'/'.$this->filename[0].'/'.$this->filename.'.'.$this->mime_ext;
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . $default_orig_file) ) {
                            $url_orig = $_MG_CONF['site_url'].'/mediaobjects/'.$default_orig_file;
                        } else {
                            $url_orig = '';
                        }
                        $data_type='html5video';

                        break;


                    case 'video/x-motion-jpeg' :
                    case 'video/quicktime' :
                    case 'video/x-qtc' :
                    case 'audio/mpeg' :
                    case 'video/x-m4v' :
                        if ( $MG_albums[$this->album_id]->tnWidth > $MG_albums[$this->album_id]->tnHeight ) {
                            $default_thumbnail = 'placeholder_video_w.svg';
                        } else {
                            $default_thumbnail = 'placeholder_video.svg';
                        }
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
                        if ( $MG_albums[$this->album_id]->tnWidth > $MG_albums[$this->album_id]->tnHeight ) {
                            $default_thumbnail = 'placeholder_video_w.svg';
                        } else {
                            $default_thumbnail = 'placeholder_video.svg';
                        }
                        break;
                    default :
                        if ( $MG_albums[$this->album_id]->tnWidth > $MG_albums[$this->album_id]->tnHeight ) {
                            $default_thumbnail = 'placeholder_video_w.svg';
                        } else {
                            $default_thumbnail = 'placeholder_video.svg';
                        }
                        $orig = 'orig';
                        $default_orig_file = $orig.'/'.$this->filename[0].'/'.$this->filename.'.'.$this->mime_ext;
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . $default_orig_file) ) {
                            $url_orig = $_MG_CONF['site_url'].'/mediaobjects/'.$default_orig_file;
                        } else {
                            $url_orig = '';
                        }
                        $data_type='html5video';
                        break;
                }
                break;
            case 2 :    // music file
                $default_thumbnail = 'placeholder_audio.svg';
                if ( $MG_albums[$this->album_id]->tnWidth > $MG_albums[$this->album_id]->tnHeight ) {
                    $default_thumbnail = 'placeholder_audio_w.svg';
                } else {
                    $default_thumbnail = 'placeholder_audio.svg';
                }
                break;
            case 4 :    // other files
                switch ($this->mime_type) {
                    case 'application/zip' :
                    case 'zip' :
                    case 'arj' :
                    case 'rar' :
                    case 'gz'  :
                        $default_thumbnail = 'placeholder_zip.svg';
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
                                    $default_thumbnail = 'placeholder_zip.svg';
                                    break;
                                case 'gz' :
                                    $default_thumbnail = 'zip.png';
                                    $default_thumbnail = 'placeholder_zip.svg';
                                    break;
                                default :
                                    $default_thumbnail = 'generic.png';
                                    $default_thumbnail = 'placeholder.svg';
                                    break;
                            }
                        }
                        break;
                }
                break;
            case 5 :
                case 'embed' :
                require_once $_CONF['path'].'plugins/mediagallery/include/lib-remotemedia.php';
					if (preg_match("/youtube/i", $this->remote_url)) {
						$default_thumbnail = 'youtube.png';
						$data_type = 'youtube';
						$videoid = getYoutubeId($this->remote_url);
					} else if (preg_match("/google/i", $this->remote_url)) {
						$default_thumbnail = 'googlevideo.png';
					} else if (preg_match("/vimeo/i", $this->remote_url)) {
					    $default_thumbnail = 'placeholder_vimeo.svg';
					    $data_type = 'vimeo';
					    $videoid = getVimeoId($this->remote_url);
					} else {
						$default_thumbnail = 'remote.png';
					}
                    if ( $MG_albums[$this->album_id]->tnWidth > $MG_albums[$this->album_id]->tnHeight ) {
                        $default_thumbnail = 'placeholder_video_w.svg';
                    } else {
                        $default_thumbnail = 'placeholder_video.svg';
                    }

                    $url_orig = $this->remote_url;
					break;

        }

        if ( $this->tn_attached == 1 ) {
            $media_thumbnail      = $_MG_CONF['assetss_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_assets'] . $default_thumbnail;
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $this->filename[0] .'/tn_' . $this->filename . $ext) ) {
                    $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/tn/'.  $this->filename[0] . '/tn_' . $this->filename . $ext;
                    $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . 'tn/'.  $this->filename[0] . '/tn_' . $this->filename . $ext;
                    break;
                }
            }
        } else if ($foundImageDefaultThumbnail === true) {
            $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
        } else {
            $media_thumbnail      = $_MG_CONF['assets_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_assets'] . $default_thumbnail;
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
				        $tnsize = @getimagesize($media_thumbnail_file);
				        $new_y += $tnsize[0];
				        if ( $tnsize[1] > $new_x ) {
					        $new_x = $tnsize[1];
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


		            if ( $this->resolution_x > 0 ) {
		                $resolution_x = $this->resolution_x;
		                $resolution_y = $this->resolution_y;
		            } else {
		                if ( $this->resolution_x == 0 && $this->remote != 1) {
                            $size = @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $this->filename[0] . '/' . $this->filename . '.' . $this->mime_ext);
                            // skip files over 8M in size..
                            if ( $size < 8388608 ) {
    		                    $ThisFileInfo = IMG_getMediaMetaData($_MG_CONF['path_mediaobjects'] . 'orig/' . $this->filename[0] . '/' . $this->filename . '.' . $this->mime_ext);
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
		            $resolution_x = 480;
		            $resolution_y = 320;
		            if ( $resolution_x < 1 || $resolution_y < 1 ) {
		                $resolution_x = 480;
		                $resolution_y = 320;
		            } else {
		                $resolution_x = $resolution_x + 40;
		                $resolution_y = $resolution_y + 40;
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
            if ($this->type == 2 ) {
                $default_thumbnail = 'placeholder_audio.svg';
            } else {
                $default_thumbnail    = 'placeholder_missing.svg';
            }
            $media_thumbnail      = $_MG_CONF['assets_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_assets'] . $default_thumbnail;
        	$tn_height = $MG_albums[$this->album_id]->tnHeight;
        	$tn_width  = $MG_albums[$this->album_id]->tnWidth;

            $media_size           = array($tn_width,$tn_height); //@getimagesize($media_thumbnail_file);
        }

       	$media_time        = MG_getUserDateTimeFormat($this->time);
      	$upload_time = MG_getUserDateTimeFormat($this->upload_time);

        $url_media_item = $url_display_item;

        // -- decide what thumbnail size to use, small, medium, large...

        if (isset($_MG_USERPREFS['tn_size']) && $_MG_USERPREFS['tn_size'] != -1 ) {
            $tn_size = $_MG_USERPREFS['tn_size'];
        } else {
            $tn_size = $MG_albums[$this->album_id]->tn_size;
        }

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
            case '4' :
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
            if ( $ratio == 0 ) {
                $newheight = $tn_height;
                $newwidth  = $tn_width;
            } else {
                $newheight = $tn_height;
                $newwidth = round($media_size[0] / $ratio);
            }
        }

        if ( $media_size[0] > $media_size[1] ) {
            $ratio = $media_size[0] / 50;
            $smallwidth = 50;
            $smallheight = round($media_size[1] / $ratio);
        } else {
            $ratio = $media_size[1] / 50;
            if ( $ratio == 0 ) {
                $smallheight = 50;
                $smallwidth = 50;
            } else {
                $smallheight = 50;
                $smallwidth = round($media_size[0] / $ratio);
            }
        }

        if ( $this->owner_id != "" && $this->owner_id > 1 ) {
	        $username = DB_getItem($_TABLES['users'],'username',"uid=".intval($this->owner_id));
        } else {
	        $username = 'anonymous';
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

    	/*
    	 * build the small rating bar
    	 *
    	 */
        if ( $MG_albums[$this->album_id]->enable_rating > 0 ) {
            $uid    = COM_isAnonUser() ? 1 : $_USER['uid'];
            $static = false;

            // check to see if we are the owner, if so, no rating for us...
            if (isset($_USER['uid']) && $_USER['uid'] == $this->owner_id ) {
                $static = true;
                $voted = 0;
            } else {
                if ( in_array($this->id,$ratedIds)) {
                    $static = true;
                    $voted = 1;
                } else {
                    $static = 0;
                    $voted = 0;
                }
            }

            if ( $MG_albums[$this->album_id]->enable_rating == 1 && COM_isAnonUser() ) {
                $static = true;
            }
            if ( $_MG_CONF['use_large_stars'] == 1 ) {
                $starSize = '';
            } else {
                $starSize = 'sm';
            }
            $rating_box = RATING_ratingBar('mediagallery',$this->id, $this->votes,$this->rating, $voted,
                                            5,$static,$starSize);
        } else {
            $rating_box = '';
        }

        $T->set_var('rating_box','<center>'.$rating_box.'</center>');

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

    	$L = new Template( MG_getTemplatePath($this->album_id) );
    	$L->set_file('media_link','medialink.thtml');
    	$L->set_var('href',$url_media_item);

        if ( $this->type == 1) { // local media
            switch ($this->mime_type ) {
                case 'audio/mpeg' :
                case 'audio/x-mpeg' :
                case 'audio/mpeg3' :
                case 'audio/x-mpeg-3' :
                case 'video/mp4' :
                case 'video/mpeg' :
                case 'video/x-mpeg' :
                case 'video/x-mpeq2a' :
                case 'video/x-motion-jpeg' :
                case 'video/quicktime' :
                case 'video/x-qtc' :
                case 'video/x-m4v' :
                    $hrefdirect = $_MG_CONF['site_url'] . '/mediaobjects/orig/'.$this->filename[0] . '/' . $this->filename . '.' . $this->mime_ext;
                    $L->set_var('hrefdirect',$hrefdirect);
                    break;
                default :
                    $hrefdirect = "javascript:showVideo('" . $_MG_CONF['site_url'] . '/video.php?n=' . $this->id . "'," . $resolution_y . ',' . $resolution_x . ')';
                    $L->set_var('hrefdirect_popup',$hrefdirect);
                    $L->unset_var('iframe');
                    break;
            }
        } elseif ( $this->type == 2 ) {
            $hrefdirect = "javascript:showVideo('" . $_MG_CONF['site_url'] . '/video.php?n=' . $this->id . "'," . $resolution_y . ',' . $resolution_x . ')';
            $L->set_var('hrefdirect_popup',$hrefdirect);
            $L->set_var('iframe',true);
        } elseif ( $this->type == 5 ) {
            if ( stristr($this->remote_url,'iframe') !== 0 ) {
                preg_match('/src="([^"]+)"/', $this->remote_url, $match);
                if ( isset($match[1]) ) {
                    $url = $match[1];
                } else {
                    $url = $this->remote_url;
                }
                $L->set_var('hrefdirect',$url);
                $L->set_var('iframe',true);
            } else {
                $L->set_var('hrefdirect',$this->remote_url);
                $L->set_var('iframe',true);
            }
        } elseif ( $this->type == 0 ) {
    	    if ( $this->remote == 1 ) {
        	    $L->set_var('hrefdirect',$this->remote_url);
        	} else {
        	    $L->set_var('hrefdirect',$_MG_CONF['mediaobjects_url'] . '/' . $direct_url);
        	}
    	}

        $caption = PLG_replaceTags(str_replace('$','&#36;',$this->title),'mediagallery','media_description');
        if ($this->owner_id == $_USER['uid'] ||
                SEC_hasRights('mediagallery.admin')) {
            $caption .= '<br />('.$this->id.')';
        }
        $L->set_var('caption', $caption);
        $L->set_var('caption_text', COM_getTextContent($caption));
    	$L->set_var('id','id' . rand());

    	$L->parse('media_link_start','media_link');
    	$media_start_link = $L->finish($L->get_var('media_link_start'));

        $T->set_var(array(
    		'play_now'		    => '',
    		'download_now'	    => $_MG_CONF['site_url'] . '/download.php?mid=' . $this->id,
    		'play_in_popup'     => "javascript:showVideo('" . $_MG_CONF['site_url'] . '/video.php?n=' . $this->id . "'," . $resolution_y . ',' . $resolution_x . ')',
            'row_height'        => $tn_height + 40,
            'media_title'       => PLG_replaceTags($this->title,'mediagallery','media_title'),
            'media_description' => PLG_replaceTags(nl2br($this->description),'mediagallery','media_description'),
            'media_tag'         => strip_tags($this->title),
            'media_time'        => $media_time[0],
            'upload_time'       => $upload_time[0],
            'media_owner'		=> $username,
            'site_url'			=> $_MG_CONF['site_url'],
            'lang_published'	=> $LANG_MG03['published'],
            'lang_on'			=> $LANG_MG03['on'],
            'lang_hyphen'		=> $this->album == '' ? '' : '-',
            'media_link_start'  => $media_start_link,
            'media_link_end'    => '</a>',
            'artist'			=> $this->artist,
            'musicalbum'		=> $this->album != '' ? $this->album : '',
            'genre'				=> $this->genre != '' ? $this->genre : '',
            'alt_edit_link'     => $edit_item,
            'filesize'          => $fileSize,
            'media_id'          => $this->id,
            'raw_media_thumbnail'   =>  $media_thumbnail,
            'display_url'       => $url_media_item,
            'orig_url'          => $url_orig,
            'url_display_item'  =>  $url_display_item,
        ));

        if ( $data_type != '' ) {
            $T->set_var('data_type',$data_type);
        } else {
            $T->unset_var('data_type');
        }

        if ( $videoid != '' ) {
            $T->set_var('videoid',$videoid);
        } else {
            $T->unset_var('videoid');
        }

        // frame template variables
        $F->set_var(array(
            'media_id'          => $this->id,
            'media_link_start'  => $media_start_link,
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
            'url_media_item'    =>  $url_media_item,
            'url_display_item'  =>  $url_display_item,
            'media_thumbnail'   =>  $media_thumbnail,
            'media_size'        =>  'width="' . $newwidth . '" height="' . $newheight . '"',
        ));

        if ( $MG_albums[$this->album_id]->enable_keywords) {
            if ( !empty($this->keywords ) ) {
                $kwText = '';
                $keyWords = array();
                $keyWords = explode(' ',$this->keywords);
                $numKeyWords = count($keyWords);
                for ( $i=0;$i<$numKeyWords;$i++ ) {
                    $keyWords[$i] = str_replace('"',' ',$keyWords[$i]);
                    $searchKeyword = $keyWords[$i];
                    $keyWords[$i] = str_replace('_',' ',$keyWords[$i]);
                    $kwText .= '<a href="' . $_MG_CONF['site_url'] . '/search.php?mode=search&amp;swhere=1&amp;keywords=' . $searchKeyword . '&amp;keyType=any">' . $keyWords[$i] . '</a> ';
                }
                $T->set_var(array(
                    'media_keywords'  =>  $kwText,
                    'lang_keywords'   =>  $LANG_MG01['keywords'],
                ));
                $T->parse('media_cell_keywords','media_cell_keywords');
            } else {
                $T->set_var('lang_keywords','');
            }
        } else {
            $T->set_var(array(
                'media_cell_keywords'   => '',
                'lang_keywords'         => '',
            ));
        }

        if ( $MG_albums[$this->album_id]->enable_rating) {
            $rating = $LANG_MG03['rating'] . ': <strong> ' . $this->rating / 2 .'</strong>/5 ('.$this->votes.' ' . $LANG_MG03['votes'] . ')';
            $T->set_var('media_rating',$rating);
            $T->parse('media_rate_results','media_rate_results');
        }

        if ( $MG_albums[$this->album_id]->enable_comments) {
            USES_lib_comment();
            $cmtLinkArray = CMT_getCommentLinkWithCount( 'mediagallery', $this->id, $_MG_CONF['site_url'] . '/media.php?f=0' . '&amp;sort=' . $sortOrder . '&amp;s=' . $this->id,$this->comments,0);

            if ( $this->type == 4 || ($this->type == 1 && $MG_albums[$this->album_id]->playback_type != 2) || ($this->type == 2 && $MG_albums[$this->album_id]->playback_type != 2) || ($this->type == 5 && $MG_albums[$this->album_id]->playback_type != 2)) {
                $cmtLink_alt  = $cmtLinkArray['link_with_count']; // '<a href="' . $_MG_CONF['site_url'] . '/media.php?f=0' . '&amp;sort=' . $sortOrder . '&amp;s=' . $this->id . '">' . $LANG_MG03['comments'] . '</a>';
                $cmtLink = '';
            }  else {
                $cmtLink = ''; //$LANG_MG03['comments'];
                $cmtLink_alt  = $cmtLinkArray['link_with_count']; //'<a href="' . $_MG_CONF['site_url'] . '/media.php?f=0' . '&amp;sort=' . $sortOrder . '&amp;s=' . $this->id . '">' . $LANG_MG03['comments'] . '</a>';
            }

            $T->set_var(array(
                'comments_with_count'   =>  $cmtLinkArray['link_with_count'],
                'media_comments_count'  =>  $cmtLinkArray['comment_count'],
                'lang_comments'         =>  $cmtLink,
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
        $T->set_var(array('max-width' => $tn_width));

		PLG_templateSetVars( 'mediagallery', $T);
        $T->parse('media_cell','media_cell_image');
        $retval = $T->finish($T->get_var('media_cell'));
        return $retval;
    }

    function displayRawThumb( $namesOnly=0 ) {
        global $_CONF, $_MG_CONF, $MG_albums, $_MG_USERPREFS, $LANG_MG03;

    	$tn_height = $MG_albums[$this->album_id]->tnHeight;
    	$tn_width  = $MG_albums[$this->album_id]->tnWidth;
        $foundImageDefaultThumbnail = false;
        switch( $this->type ) {
            case 0 :    // standard image
                $default_thumbnail = 'tn/' . $this->filename[0] . '/' . $this->filename . '.jpg';
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $this->filename[0] . '/' . $this->filename . $ext) ) {
                        $default_thumbnail      = 'tn/'.  $this->filename[0] . '/' . $this->filename . $ext;
                        $foundImageDefaultThumbnail = true;
                        break;
                    }
                }
                if ($foundImageDefaultThumbnail === false) {
                    $default_thumbnail = 'placeholder.svg';
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
                        break;
                    case 'video/mpeg' :
                    case 'video/x-motion-jpeg' :
                    case 'video/quicktime' :
                    case 'video/mpeg' :
                    case 'video/x-mpeg' :
                    case 'video/x-mpeq2a' :
                    case 'video/x-qtc' :
                    case 'video/x-m4v' :
                        $default_thumbnail = 'placeholder_quicktime.svg';
                        break;
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
                switch ($this->mime_type ) {
                    case 'application/zip' :
                    case 'application/x-gzip' :
                    case 'application/x-tar' :
                    case 'arj' :
                    case 'rar' :
                    case 'gz'  :
                        $default_thumbnail = 'placeholder_zip.svg';
                        break;
                    case 'application/pdf' :
                    case 'pdf' :
                        $default_thumbnail = 'placeholder_pdf.svg';
                        break;
                    case 'application/octet-stream' :
                        if ( $this->mime_ext == 'pdf' ) {
                            $default_thumbnail = 'placeholder_pdf.svg';
                        } else if ( $this->mime_ext == 'arj' ) {
                            $default_thumbnail = 'placeholder_zip.svg';
                        } else if ( $this->mime_ext == 'rar' ) {
                            $default_thumbnail = 'placeholder_zip.svg';
                        } else {
                            $default_thumbnail = 'placeholder.svg';
                        }
                        break;
                    default :
                        $default_thumbnail = 'placeholder.svg';
                        break;
                }
                break;
			case 5 :
                if ( $MG_albums[$this->album_id]->tnWidth > $MG_albums[$this->album_id]->tnHeight ) {
                    $default_thumbnail = 'placeholder_video_w.svg';
                } else {
                    $default_thumbnail = 'placeholder_video.svg';
                }
	            break;

        }

        if ( $this->tn_attached == 1 ) {
            $media_thumbnail = '';
            $media_thumbnail_file = '';
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $this->filename[0] . '/tn_' . $this->filename . $ext) ) {
                    $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/tn/'.  $this->filename[0] . '/tn_' . $this->filename . $ext;
                    $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . 'tn/'.  $this->filename[0] . '/tn_' . $this->filename . $ext;
                    break;
                }
            }
        } else if ($foundImageDefaultThumbnail === true) {
            $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
        } else {
            $media_thumbnail      = $_MG_CONF['assets_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_assets'] . $default_thumbnail;
        }

        if ( strstr($media_thumbnail_file,'.svg') ) {
            $media_size = array($tn_width,$tn_height);
        } else {
            $media_size        = @getimagesize($media_thumbnail_file);
        }

        if ( $media_thumbnail == '' || $media_size == false ) {
            if ($this->type == 2 ) {
                $default_thumbnail = 'placeholder_audio.svg';
            } else {
                $default_thumbnail    = 'placeholder_missing.svg';
            }
            $media_thumbnail      = $_MG_CONF['assets_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_assets'] . $default_thumbnail;
            $media_size           = array($tn_width,$tn_height);
        }

        if ( $namesOnly == 1 ) {
            return (array($media_thumbnail,$media_thumbnail_file));
        }

        $tn_height = 100;
        if ( $media_size[0] > $media_size[1] ) {
            $ratio = $media_size[0] / $tn_height;
            $newwidth = $tn_height;
            $newheight = round($media_size[1] / $ratio);
        } else {
            $ratio = $media_size[1] / $tn_height;
            if ( $ratio == 0 ) {
                $newheight = $tn_height;
                $newwidth  = $tn_width;
            } else {
                $newheight = $tn_height;
                $newwidth = round($media_size[0] / $ratio);
            }
        }
        $media_dim = 'width="' . $newwidth . '" height="' . $newheight . '"';
        return '<img src="' .$media_thumbnail . '" ' . $media_dim . ' style="border:none;" alt="' . strip_tags($this->title) . '" title="' . strip_tags($this->title) . '"/>';
    }

    function displayRaw( $namesOnly=0 ) {
        global $_CONF, $_MG_CONF, $MG_albums, $_MG_USERPREFS, $LANG_MG03;

        $foundImageDefaultThumbnail = false;

        switch( $this->type ) {
            case 0 :    // standard image
                $default_thumbnail = 'tn/' . $this->filename[0] . '/' . $this->filename . '.jpg';
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $this->filename[0] . '/' . $this->filename . $ext) ) {
                        $default_thumbnail      = 'tn/'.  $this->filename[0] . '/' . $this->filename . $ext;
                        $foundImageDefaultThumbnail = true;
                        break;
                    }
                }
                if ($foundImageDefaultThumbnail === false) {
                    $default_thumbnail = 'placeholder.svg';
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
                        break;
                    case 'video/mpeg' :
                    case 'video/x-motion-jpeg' :
                    case 'video/quicktime' :
                    case 'video/mpeg' :
                    case 'video/x-mpeg' :
                    case 'video/x-mpeq2a' :
                    case 'video/x-qtc' :
                    case 'video/x-m4v' :
                        $default_thumbnail = 'placeholder_quicktime.svg';
                        break;
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
                switch ($this->mime_type ) {
                    case 'application/zip' :
                    case 'application/x-gzip' :
                    case 'application/x-tar' :
                    case 'arj' :
                    case 'rar' :
                    case 'gz'  :
                        $default_thumbnail = 'placeholder_zip.svg';
                        break;
                    case 'application/pdf' :
                    case 'pdf' :
                        $default_thumbnail = 'placeholder_pdf.svg';
                        break;
                    case 'application/octet-stream' :
                        if ( $this->mime_ext == 'pdf' ) {
                            $default_thumbnail = 'placeholder_pdf.svg';
                        } else if ( $this->mime_ext == 'arj' ) {
                            $default_thumbnail = 'placeholder_zip.svg';
                        } else if ( $this->mime_ext == 'rar' ) {
                            $default_thumbnail = 'placeholder_zip.svg';
                        } else {
                            $default_thumbnail = 'placeholder.svg';
                        }
                        break;
                    default :
                        $default_thumbnail = 'placeholder.svg';
                        break;
                }
                break;
			case 5 :
                if ( $MG_albums[$this->album_id]->tnWidth > $MG_albums[$this->album_id]->tnHeight ) {
                    $default_thumbnail = 'placeholder_video_w.svg';
                } else {
                    $default_thumbnail = 'placeholder_video.svg';
                }
//	            $default_thumbnail = 'remote.png';
	            break;

        }

        if ( $this->tn_attached == 1 ) {
            $media_thumbnail = '';
            $media_thumbnail_file = '';
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $this->filename[0] . '/tn_' . $this->filename . $ext) ) {
                    $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/tn/'.  $this->filename[0] . '/tn_' . $this->filename . $ext;
                    $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . 'tn/'.  $this->filename[0] . '/tn_' . $this->filename . $ext;
                    break;
                }
            }
        } else if ($foundImageDefaultThumbnail === true) {
            $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
        } else {
            $media_thumbnail      = $_MG_CONF['assets_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_assets'] . $default_thumbnail;
        }

        if ( strstr($media_thumbnail_file,'.svg') ) {
            $media_size = array($tn_width,$tn_height);
        } else {
            $media_size        = @getimagesize($media_thumbnail_file);
        }

        if ( $media_thumbnail == '' || $media_size == false ) {
            if ($this->type == 2 ) {
                $default_thumbnail = 'placeholder_audio.svg';
            } else {
                $default_thumbnail    = 'placeholder_missing.svg';
            }
            $media_thumbnail      = $_MG_CONF['assets_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_assets'] . $default_thumbnail;
            $media_size           = array($tn_width,$tn_height);
        }

        if ( $namesOnly == 1 ) {
            return (array($media_thumbnail,$media_thumbnail_file));
        }

        $tn_height = 100;
        if ( $media_size[0] > $media_size[1] ) {
            $ratio = $media_size[0] / $tn_height;
            $newwidth = $tn_height;
            $newheight = round($media_size[1] / $ratio);
        } else {
            $ratio = $media_size[1] / $tn_height;
            $newheight = $tn_height;
            $newwidth = round($media_size[0] / $ratio);
        }
        return $media_thumbnail;
        $media_dim = 'width="' . $newwidth . '" height="' . $newheight . '"';
        return '<img src="' .$media_thumbnail . '" ' . $media_dim . ' style="border:none;" alt="' . strip_tags($this->title) . '" title="' . strip_tags($this->title) . '"/>';
    }
}
?>