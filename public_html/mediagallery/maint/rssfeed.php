<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | rssfeed.php                                                              |
// |                                                                          |
// | RSS Feed maintenance                                                     |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
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
if (strpos ($_SERVER['PHP_SELF'], 'rssfeed.php') !== false)
{
    die ('This file can not be used on its own.');
}

require_once($_MG_CONF['path_html'] . 'classes/feedcreator.class.php');

function MG_buildAlbumRSS( $aid ) {
    global $MG_albums, $_MG_CONF, $_CONF, $_TABLES;

    $fname = sprintf($_MG_CONF['rss_feed_name'] . "%06d.rss", $aid);
    $feedname = $_MG_CONF['path_html'] . "rss/" . $fname;

    if ( $MG_albums[$aid]->enable_rss != 1 ) {
        @unlink($feedname);
        return;
    }

    $rss = new UniversalFeedCreator();
    $rss->title = $_CONF['site_name'] . '::' . $MG_albums[$aid]->title;
    $rss->description = $MG_albums[$aid]->description;
    $rss->descriptionTruncSize = 500;
    $rss->descriptionHtmlSyndicated = true;

    $rss->encoding = strtoupper ($_CONF['default_charset']);

	$image = new FeedImage();
	$image->title = $MG_albums[$aid]->title;
    $filename = $MG_albums[$aid]->findCover();
    if ( substr($filename,0,3) == 'tn_') {
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[3] . '/' . 'tn_' . $filename . $ext) ) {
                $imgurl = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[3] . '/' . 'tn_' . $filename . $ext;
                break;
            }
        }
//        $imgurl = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[3] . '/' . 'tn_' . $filename . '.jpg';
    } elseif ($filename != '') {
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] . '/' . $filename . $ext) ) {
                $imgurl = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] . '/' . $filename . $ext;
                break;
            }
        }
//        $imgurl = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] . '/' . $filename . '.jpg';
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
//	    $imgurl = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $aid . '.jpg';
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
//		$rss->podcast->keywords = "php podcast rss itunes";
//		$rss->podcast->owner_email = "owner@example.com";

		// file this podcast under Technology->Computers
//		$podcast_tech_category = new PodcastCategory('Technology');
//		$podcast_comp_category = new PodcastCategory('Computers');
//		$podcast_tech_category->addCategory($podcast_comp_category);
//		$podcast_comp_category->addCategory($podcast_tech_category);
 	}

    $rss->link = $_MG_CONF['site_url'];
    $rss->syndicationURL = $_MG_CONF['site_url'] . '/rss/' . $fname;

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
    global $MG_albums, $_MG_CONF, $_CONF, $_TABLES;

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
            " ON ma.media_id=m.media_id WHERE ma.album_id=" . $aid . ' ORDER BY m.media_time DESC';

    $result = DB_query( $sql );
    $nRows  = DB_numRows( $result );

    if ( $nRows > 0 ) {
        while ( $row = DB_fetchArray($result)) {
            $item = new FeedItem();
            $item->title = $row['media_title'];
            $item->link =  $_MG_CONF['site_url'] . '/media.php?s=' . $row['media_id'];
	        $description = '';
            $item->description = $description . $row['media_desc'];
            $item->descriptionTruncSize = 500;
            $item->descriptionHtmlSyndicated = true;

		    if ( $MG_albums[$aid]->podcast ) {
				// optional -- applies only if this is a podcast
			    $item->podcast = new PodcastItem();
			    $item->podcast->enclosure_url = $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
			    $item->podcast->enclosure_length = filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
			    $item->podcast->enclosure_type = $row['mime_type'];
			}

            $item->date = strftime("%a, %d %b %Y %H:%M:%S %z",$row['media_time']);
            $item->source = $_CONF['site_url'];
            if ( $row['artist'] != '' ) {
                $item->author = $row['artist'];
                $item->podcast->author = $row['artist'];
            }
            if ( $row['media_keywords'] != '' ) {
                $item->podcast->keywords = $row['media_keywords'];
            }
/* ---
            if ( $row['media_user_id'] != '' && $row['media_user_id'] > 1 ) {
	        	$res = DB_query("SELECT * FROM {$_TABLES['users']} WHERE uid='" . $row['media_user_id'] . "'");
	        	$uRow = DB_fetchArray($res);
                $item->author = $_MG_CONF['hide_author_email'] == 0 ? $uRow['email'] : '' . ' (' . $uRow['fullname'] . ')';
            }
--- */
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
                $item->link =  $_MG_CONF['site_url'] . '/album.php?aid=' . $aid;
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
//                    $description .= '<img src="' . $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[3] . '/' . $filename . '.jpg" align="left">';
                } elseif ($filename != '') {
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] . '/' . $filename . $ext) ) {
                            $description .= '<img src="' . $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] . '/' . $filename . $ext . '" align="left">';
                            break;
                        }
                    }
//                    $description .= '<img src="' . $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] . '/' . $filename . '.jpg" align="left">';
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

    if ( $_MG_CONF['rss_full_enabled'] != 1 ) {
        @unlink($_MG_CONF['path_html'] . "rss/" . $_MG_CONF['rss_feed_name'] . '.rss');
        return;
    }
    $rss = new UniversalFeedCreator();
    $rss->title = $_CONF['site_name'] . ' Media Gallery RSS Feed';
    $rss->description = $_CONF['site_slogan'];
    $rss->descriptionTruncSize = 500;
    $rss->descriptionHtmlSyndicated = true;
    $rss->encoding = strtoupper ($_CONF['default_charset']);
    $rss->link = $_CONF['site_url'];
    $rss->syndicationURL = $_CONF['site_url'] . $_SERVER["PHP_SELF"];
    MG_parseAlbumsRSS($rss, 0);
    // valid format strings are: RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
    // MBOX, OPML, ATOM, ATOM0.3, HTML, JS
    $rss->saveFeed($_MG_CONF['rss_feed_type'], $_MG_CONF['path_html'] . "rss/" . $_MG_CONF['rss_feed_name'] . '.rss',0);
    @chmod($_MG_CONF['path_html'] . 'rss/' . $_MG_CONF['rss_feed_name'] . '.rss', 0664);

    return;
}

function MG_buildNewRSS() {

}
?>