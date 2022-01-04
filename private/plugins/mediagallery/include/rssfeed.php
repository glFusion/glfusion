<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* RSS Feed Maintenance
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

MG_initAlbums();

function MG_buildAlbumRSS( $aid ) {
    global $MG_albums, $_MG_CONF, $_CONF, $_TABLES;

    $feedpath = MG_getFeedPath();

    $fname = sprintf($_MG_CONF['rss_feed_name'] . "%06d.rss", $aid);
    $feedname = $feedpath . '/' . $fname;

    if ( $MG_albums[$aid]->enable_rss != 1 ) {
        @unlink($feedname);
        return;
    }

    $rss = new UniversalFeedCreator();
    $rss->title = $_CONF['site_name'] . '::' . $MG_albums[$aid]->title;
    $rss->description = $MG_albums[$aid]->description;
    $rss->descriptionTruncSize = 500;
    $rss->descriptionHtmlSyndicated = true;

//    $rss->encoding = strtoupper ($_CONF['default_charset']);

    $imgurl = '';

	$image = new FeedImage();
	$image->title = $_CONF['site_name'] . '::' . $MG_albums[$aid]->title;
    $filename = $MG_albums[$aid]->findCover();
    if ( substr($filename,0,3) == 'tn_') {
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[3] . '/' . 'tn_' . $filename . $ext) ) {
                $imgurl = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[3] . '/' . 'tn_' . $filename . $ext;
                break;
            }
        }
    } elseif ($filename != '') {
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] . '/' . $filename . $ext) ) {
                $imgurl = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] . '/' . $filename . $ext;
                break;
            }
        }
    } else {
        $imgurl = '';
    }
    if ( $MG_albums[$aid]->tn_attached == 1 ) {
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $aid . $ext) ) {
                $imgurl = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $aid . $ext;
                break;
            }
        }
    }

	$image->url = $imgurl;
	$image->link = $_MG_CONF['site_url'];
	$image->description = $MG_albums[$aid]->title;
	$image->descriptionTruncSize = 500;
	$image->descriptionHtmlSyndicated = true;
	$rss->image = $image;

    if ( $MG_albums[$aid]->podcast ) {
		//optional -- applies only if this is a podcast
		$rss->podcast = new Podcast();
		$rss->podcast->subtitle = $MG_albums[$aid]->description;
        if ( $MG_albums[$aid]->owner_id != '' ) {
	        $res = DB_query("SELECT * FROM {$_TABLES['users']} WHERE uid='" . $MG_albums[$aid]->owner_id . "'");
	        $uRow = DB_fetchArray($res);
            $rss->podcast->author = $uRow['username'];
			$rss->podcast->owner_name = $uRow['fullname'];
			$rss->podcast->owner_email = $_MG_CONF['hide_author_email'] == 0 ? $uRow['email'] : '';
        } else {
	        $rss->podcast->author = 'anonymous';
	        $rss->podcast->owner_name = 'anonymous';
        }
		$rss->podcast->summary = $MG_albums[$aid]->description;
 	}

    $rss->link = $_MG_CONF['site_url'];
    $feedurl = SYND_getFeedUrl ();
    $rss->syndicationURL = $feedurl.$fname;

    MG_processAlbumFeedItems( $rss, $aid );
    if ( !empty($MG_albums[$aid]->children) && $MG_albums[$aid]->rssChildren ) {
        $children = $MG_albums[$aid]->getChildren();
        foreach($children as $child) {
            if ( $MG_albums[$child]->hidden != 1 ) {
                if ( $_MG_CONF['rss_ignore_empty'] == 1 && $MG_albums[$child]->last_update != 0 && $MG_albums[$child]->last_update != '' && $MG_albums[$child]->media_count > 0 ) {
                    if ( $_MG_CONF['rss_anonymous_only'] == 1 && $MG_albums[$child]->perm_anon > 0 ) {
                        MG_processAlbumFeedItems($rss, $MG_albums[$child]->id);
                    }
                }
            }
        }
    }
    if ( $MG_albums[$aid]->podcast ) {
    	$rss->saveFeed("PODCAST",$feedname,0);
	} else {
	    $rss->saveFeed($_MG_CONF['rss_feed_type'], $feedname ,0);
    }
    @chmod($feedname, 0664);

}

/*
 * pulls the individual items from an album
 */

function MG_processAlbumFeedItems( &$rss, $aid ) {
    global $MG_albums, $_MG_CONF, $_CONF, $_TABLES, $LANG_MG00;

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
            " ON ma.media_id=m.media_id WHERE ma.album_id=" . (int) $aid . ' ORDER BY m.media_upload_time DESC';

    $result = DB_query( $sql );
    $nRows  = DB_numRows( $result );

    if ( $nRows > 0 ) {
        while ( $row = DB_fetchArray($result)) {
            $item = new FeedItem();
            if ( $row['media_title'] != '' ) {
                $item->title = $row['media_title'];
            } else {
                $item->title = $LANG_MG00['no_title'];
            }
            $item->link = $_MG_CONF['site_url'] . '/media.php?s=' . $row['media_id'];
            $item->guid = $_MG_CONF['site_url'] . '/media.php?s=' . $row['media_id'];

		    if ( $MG_albums[$aid]->podcast ) {
				// optional -- applies only if this is a podcast
			    $item->podcast = new PodcastItem();
			    $item->podcast->enclosure_url = $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
			    $item->podcast->enclosure_length = @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
			    $item->podcast->enclosure_type = $row['mime_type'];
			}
            $dt = new Date($row['media_upload_time'],$_CONF['timezone']);
            $item->date = $dt->toRFC822(true);
            $item->source = $_CONF['site_url'];
            if ( $MG_albums[$aid]->podcast && $row['artist'] != '' ) {
                $item->author = $row['artist'];
                $item->podcast->author = $row['artist'];
            }
            if ( $MG_albums[$aid]->podcast && $row['media_keywords'] != '' ) {
                $item->podcast->keywords = $row['media_keywords'];
            }
            switch( $row['media_type'] ) {
                case 0 :    // standard image
                    $default_thumbnail = 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.jpg';
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                            $default_thumbnail      = 'tn/'.  $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                            break;
                        }
                    }
                    break;
                case 1 :    // video file
                    switch ( $row['mime_type'] ) {
                        case 'video/x-flv' :
                            $default_thumbnail = 'flv.png';
                            break;
                        case 'application/x-shockwave-flash' :
                            $default_thumbnail = 'flash.png';
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
                            $default_thumbnail = 'quicktime.png';
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
                            $default_thumbnail = 'wmp.png';
                            break;
                        default :
                            $default_thumbnail = 'video.png';
                            break;
                    }
                    break;
                case 2 :    // music file
                    $default_thumbnail = 'audio.png';
                    break;
                case 4 :    // other files
                    switch ( $row['mime_type'] ) {
                        case 'application/zip' :
                        case 'application/x-gzip' :
                        case 'application/x-tar' :
                        case 'arj' :
                        case 'rar' :
                        case 'gz'  :
                            $default_thumbnail = 'zip.png';
                            break;
                        case 'application/pdf' :
                        case 'pdf' :
                            $default_thumbnail = 'pdf.png';
                            break;
                        case 'application/octet-stream' :
                            if ( $row['mime_ext'] == 'pdf' ) {
                                $default_thumbnail = 'pdf.png';
                            } else if ( $row['mime_ext'] == 'arj' ) {
                                $default_thumbnail = 'zip.png';
                            } else if ( $row['mime_ext'] == 'rar' ) {
                                $default_thumbnail = 'zip.png';
                            } else {
                                $default_thumbnail = 'generic.png';
                            }
                            break;
                        default :
                            $default_thumbnail = 'generic.png';
                            break;
                    }
                    break;
        		case 5 :
                    $default_thumbnail = 'remote.png';
                    break;
            }

            if ( $row['media_tn_attached'] == 1 ) {
                $media_thumbnail = '';
                $media_thumbnail_file = '';
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                        $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                        $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . 'tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                        break;
                    }
                }
            } else {
                $media_thumbnail      = $_MG_CONF['assets_url'] . '/' . $default_thumbnail;
                $media_thumbnail_file = $_MG_CONF['path_assets'] . $default_thumbnail;
            }

            $media_size        = @getimagesize($media_thumbnail_file);

            if ( $media_thumbnail == '' || $media_size == false ) {
                $default_thumbnail    = 'generic.png';
                $media_thumbnail      = $_MG_CONF['assets_url'] . '/' . $default_thumbnail;
                $media_thumbnail_file = $_MG_CONF['path_assets'] . $default_thumbnail;
                $media_size           = @getimagesize($media_thumbnail_file);
            }

          	$imgurl = $media_thumbnail;
            $description = "<img width=\"".$media_size[0]."\" vspace=\"5\" hspace=\"5\" height=\"".$media_size[1]."\" border=\"1\" align=\"left\" src=\"".$imgurl."\" alt=\"\" />\n";
            $item->description = $description . $row['media_desc'];
            $item->descriptionTruncSize = 500;
            $item->descriptionHtmlSyndicated = true;
            $rss->addItem($item);
        }
    }
    /*
     * Process the children albums
     */

    if ( !empty($MG_albums[$aid]->children) && $MG_albums[$aid]->rssChildren ) {
        $children = $MG_albums[$aid]->getChildren();
        foreach($children as $child) {
            if ( $MG_albums[$child]->hidden != 1 ) {
                if ( $_MG_CONF['rss_ignore_empty'] == 1 && $MG_albums[$child]->last_update != 0 && $MG_albums[$child]->last_update != '' && $MG_albums[$aid]->media_count > 0 ) {
                    if ( $_MG_CONF['rss_anonymous_only'] == 1 && $MG_albums[$child]->perm_anon > 0 ) {
                        MG_processAlbumFeedItems($rss, $MG_albums[$child]->id);
                    }
                }
            }
        }
    }
}

function MG_parseAlbumsRSS( &$rss, $aid ) {
    global $MG_albums, $_MG_CONF, $_CONF, $_TABLES;

    if ( $MG_albums[$aid]->hidden != 1 ) {
        if ( $_MG_CONF['rss_ignore_empty'] == 1 && $MG_albums[$aid]->last_update != 0 && $MG_albums[$aid]->last_update != '' && $MG_albums[$aid]->media_count > 0 ) {
            if ( $_MG_CONF['rss_anonymous_only'] == 1 && $MG_albums[$aid]->perm_anon > 0 ) {
                $item = new FeedItem();
                $item->title = $MG_albums[$aid]->title;
                $item->link = $_MG_CONF['site_url'] . '/album.php?aid=' . $aid;
                $item->guid = $_MG_CONF['site_url'] . '/album.php?aid=' . $aid;
                $description = '';
                $childCount = $MG_albums[$aid]->getChildcount();
                $description = 'Album contains ' . $MG_albums[$aid]->media_count . ' item and ' . $childCount . ' sub-albums.<br /><br />';
                $filename = $MG_albums[$aid]->findCover();
                if ( substr($filename,0,3) == 'tn_') {
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[3] . '/' . $filename . $ext) ) {
                            $description .= '<img src="' . $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[3] . '/' . $filename . $ext . '" align="left">';
                            break;
                        }
                    }
                } elseif ($filename != '') {
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] . '/' . $filename . $ext) ) {
                            $description .= '<img src="' . $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] . '/' . $filename . $ext . '" align="left">';
                            break;
                        }
                    }
                }
                $description .= $MG_albums[$aid]->description;
                $item->description = $description;
                //optional
                $item->descriptionTruncSize = 500;
                $item->descriptionHtmlSyndicated = true;

                $item->date = strftime("%a, %d %b %Y %H:%M:%S %z",$MG_albums[$aid]->last_update);
                $item->source = $_CONF['site_url'];
                if ($MG_albums[$aid]->owner_id != '' ) {
                    $username = DB_getItem($_TABLES['users'],'username',"uid={$MG_albums[$aid]->owner_id}");
                    $item->author = $username;
                }
                $rss->addItem($item);
            }
        }
    }
    if ( !empty($MG_albums[$aid]->children)) {
        $children = $MG_albums[$aid]->getChildren();
        foreach($children as $child) {
            MG_parseAlbumsRSS($rss, $MG_albums[$child]->id);
        }
    }
}


function MG_buildFullRSS( ) {
    global $LANG_CHARSET, $MG_albums, $_MG_CONF, $_CONF, $_TABLES;

    $feedpath = MG_getFeedPath();

    if ( $_MG_CONF['rss_full_enabled'] != 1 ) {
        @unlink($feedpath . '/' . $_MG_CONF['rss_feed_name'] . '.rss');
        return;
    }
    $rss = new UniversalFeedCreator();
    $rss->title = $_CONF['site_name'] . ' Media Gallery RSS Feed';
    $rss->description = $_CONF['site_slogan'];
    $rss->descriptionTruncSize = 500;
    $rss->descriptionHtmlSyndicated = true;
//    $rss->encoding = strtoupper ($_CONF['default_charset']);
    $rss->link = $_CONF['site_url'];
    $feedurl = SYND_getFeedUrl ();
    $rss->syndicationURL = $feedurl. $_MG_CONF['rss_feed_name'] . '.rss';
    MG_parseAlbumsRSS($rss, 0);
    $rss->saveFeed($_MG_CONF['rss_feed_type'], $feedpath . "/" . $_MG_CONF['rss_feed_name'] . '.rss',0);
    @chmod($feedpath . '/' . $_MG_CONF['rss_feed_name'] . '.rss', 0664);

    return;
}

function MG_buildNewRSS() {

}
?>