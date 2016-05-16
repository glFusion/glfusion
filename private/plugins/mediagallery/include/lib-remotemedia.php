<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | Retrieve video id from dailymotion, vimeo, youtube                       |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2016 by the following authors:                        |
// |                                                                          |
// | https://github.com/lingtalfi                                             |
// |                                                                          |
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

/**
* Extracts the daily motion id from a daily motion url.
* Returns false if the url is not recognized as a daily motion url.
*/
function getDailyMotionId($url)
{

    if (preg_match('!^.+dailymotion\.com/(video|hub)/([^_]+)[^#]*(#video=([^_&]+))?|(dai\.ly/([^_]+))!', $url, $m)) {
        if (isset($m[6])) {
            return $m[6];
        }
        if (isset($m[4])) {
            return $m[4];
        }
        return $m[2];
    }
    return false;
}


/**
* Extracts the vimeo id from a vimeo url.
* Returns false if the url is not recognized as a vimeo url.
*/
function getVimeoId($url)
{
    if (preg_match('#(?:https?://)?(?:www.)?(?:player.)?vimeo.com/(?:[a-z]*/)*([0-9]{6,11})[?]?.*#', $url, $m)) {
        return $m[1];
    }
    return false;
}

/**
* Extracts the youtube id from a youtube url.
* Returns false if the url is not recognized as a youtube url.
*/
function getYoutubeId($url)
{
    if ( strpos($url,'iframe')) {
        if ( preg_match('#<iframe[^>]*src=\"[^\"]+\/([^\"]+)\"[^>]*>#', $url, $m) ) {
            return $m[1];
        }
        return false;
    }

    $parts = parse_url($url);
    if (isset($parts['host'])) {
        $host = $parts['host'];
        if (
            false === strpos($host, 'youtube') &&
            false === strpos($host, 'youtu.be')
        ) {
            return false;
        }
    }
    if (isset($parts['query'])) {
        parse_str($parts['query'], $qs);
        if (isset($qs['v'])) {
            return $qs['v'];
        }
        else if (isset($qs['vi'])) {
            return $qs['vi'];
        }
    }
    if (isset($parts['path'])) {
        $path = explode('/', trim($parts['path'], '/'));
        return $path[count($path) - 1];
    }
    return false;
}


/**
* Gets the thumbnail url associated with an url from either:
*
*      - youtube
*      - daily motion
*      - vimeo
*
* Returns false if the url couldn't be identified.
*
* In the case of you tube, we can use the second parameter (format), which
* takes one of the following values:
*      - small         (returns the url for a small thumbnail)
*      - medium        (returns the url for a medium thumbnail)
*
*
*
*/
function getVideoThumbnailByUrl($url, $format = 'small')
{
    if (false !== ($id = getVimeoId($url))) {
        $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$id.php"));
        /**
        * thumbnail_small
        * thumbnail_medium
        * thumbnail_large
        */
        return $hash[0]['thumbnail_large'];
    }
    elseif (false !== ($id = getDailyMotionId($url))) {
        return 'http://www.dailymotion.com/thumbnail/video/' . $id;
    }
    elseif (false !== ($id = getYoutubeId($url))) {
        /**
        * http://img.youtube.com/vi/<insert-youtube-video-id-here>/0.jpg
        * http://img.youtube.com/vi/<insert-youtube-video-id-here>/1.jpg
        * http://img.youtube.com/vi/<insert-youtube-video-id-here>/2.jpg
        * http://img.youtube.com/vi/<insert-youtube-video-id-here>/3.jpg
        *
        * http://img.youtube.com/vi/<insert-youtube-video-id-here>/default.jpg
        * http://img.youtube.com/vi/<insert-youtube-video-id-here>/hqdefault.jpg
        * http://img.youtube.com/vi/<insert-youtube-video-id-here>/mqdefault.jpg
        * http://img.youtube.com/vi/<insert-youtube-video-id-here>/sddefault.jpg
        * http://img.youtube.com/vi/<insert-youtube-video-id-here>/maxresdefault.jpg
        */
        if ('medium' === $format) {
            return 'http://img.youtube.com/vi/' . $id . '/hqdefault.jpg';
        }
        return 'http://img.youtube.com/vi/' . $id . '/default.jpg';
    }
    return false;
}

/**
* Returns the location of the actual video for a given url which belongs to either:
*
*      - youtube
*      - daily motion
*      - vimeo
*
* Or returns false in case of failure.
* This function can be used for creating video sitemaps.
*/
function getVideoLocation($url)
{
    if (false !== ($id = getDailyMotionId($url))) {
        return 'http://www.dailymotion.com/embed/video/' . $id;
    }
    elseif (false !== ($id = getVimeoId($url))) {
        return 'http://player.vimeo.com/video/' . $id;
    }
    elseif (false !== ($id = getYoutubeId($url))) {
        return 'http://www.youtube.com/embed/' . $id;
    }
    return false;
}

/**
* Returns the html code for an embed responsive video, for a given url.
* The url has to be either from:
* - youtube
* - daily motion
* - vimeo
*
* Returns false in case of failure
*/

?>