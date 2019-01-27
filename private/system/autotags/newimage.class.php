<?php
/**
 * @package    glFusion CMS
 *
 * @copyright   Copyright (C) 2014-2018 by the following authors
 *              Mark R. Evans          mark AT glfusion DOT org
 *
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

class autotag_newimage extends BaseAutotag {

    function __construct()
    {
        global $_AUTOTAGS;

        $this->description = $_AUTOTAGS['newimage']['description'];
    }

    function parse($p1, $p2='', $fulltag)
    {
        global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG03, $_PLUGINS, $MG_albums, $_DB_dbms, $mg_installed_version;

        $retval = '';
        $skip = 0;
        $itemsToDisplay = 15;

        if (!in_array('mediagallery', $_PLUGINS)) {
            return $retval;
        }

        // defaults:

        $itemsToDisplay = $p1;
        $uniqueID = md5($p1);

        if ( $mg_installed_version != $_MG_CONF['pi_version'] ) {
            return $retval;
        }

        $truncate = 1;
        $caption  = 0;

        $px = explode (' ', trim ($p2));
        if (is_array ($px)) {
            foreach ($px as $part) {
                if (substr ($part, 0, 9) == 'truncate:') {
                    $a = explode (':', $part);
                    $truncate = (int) $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 8) == 'caption:') {
                    $a = explode (':', $part);
                    $caption = (int) $a[1];
                    $skip++;
                } else {
                    break;
                }
            }
        }

        if ( $truncate == 1 ) {
            $truncate_word = 'true';
        } else {
            $truncate_word = 'false';
        }

        $outputHandle = outputHandler::getInstance();
        $outputHandle->addLinkScript($_CONF['site_url'].'/mediagallery/js/jquery.flex-images.js');
        $outputHandle->addLinkStyle($_CONF['site_url'].'/mediagallery/js/jquery.flex-images.css');

        $c = glFusion\Cache::getInstance();
        $key = 'newimages__'.$uniqueID.'_'.$c->securityHash(true,true);
        if ( $c->has($key)) {
            return $c->get($key);
        }
        $imageArray = array();

        require_once $_CONF['path'].'plugins/mediagallery/include/init.php';

        MG_initAlbums();

        $sql = "SELECT ma.album_id,m.media_user_id,m.media_id,m.media_filename,m.media_title,
                u.fullname FROM {$_TABLES['mg_albums']} as a
                LEFT JOIN {$_TABLES['mg_media_albums']} as ma
                on a.album_id=ma.album_id LEFT JOIN {$_TABLES['mg_media']} as m
                on ma.media_id=m.media_id
                LEFT JOIN {$_TABLES['users']} as u ON m.media_user_id=u.uid
                WHERE
                m.media_type=0 AND a.hidden=0 "
                . COM_getPermSQL('and') . " ORDER BY m.media_upload_time DESC LIMIT ". (int) $itemsToDisplay;

        $result = DB_query( $sql,1 );
        $nRows  = DB_numRows( $result );

        for ( $x=0; $x < $nRows; $x++ ) {
            $row = DB_fetchArray($result);

            $url_media      = $_MG_CONF['site_url'] . '/media.php?s=' . $row['media_id'];
            $url_album      = $_MG_CONF['site_url'] . '/album.php?aid=' . $row['album_id'] .'&amp;s='.$row['media_id'].'#'.$row['media_id'];

            $msize = false;
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext) ) {
                    $media_thumbnail= $_MG_CONF['mediaobjects_url'] . '/disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                    $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
                    $media_url = $_MG_CONF['site_url'].'/mediaobjects/'.'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                    break;
                }
            }

            if ($msize == false ) {
                continue;
            }

            $imageArray[] = array(  'url' => $media_url,
                                    'height' => $msize[1],
                                    'width' => $msize[0],
                                    'link'  => $url_media,
                                    'caption' => $row['media_title'],
                                  );
        }

        $retval .= '<div class="flex-images uk-panel uk-panel-box">';
        foreach ($imageArray AS $image ) {
            $retval .= '<div class="item" data-w="'.$image['width'].'" data-h="'.$image['height'].'">';
            if ( $caption ) {
                $retval .= '<div class="over uk-hidden-small">'.$image['caption'].'</div>';
            }
            $retval .= '<a href="'.$image['url'].'"  data-uk-lightbox="{group:\'newimages\'}">';
            $retval .= '<img class="uk-thumbnail" src="'.$image['url'].'">';
            $retval .= '</a>';
            $retval .= '</div>';
        }
        $retval .= '</div>';
        $retval .= '<script>$(\'.flex-images\').flexImages({rowHeight: 200, truncate:'.$truncate_word.'});</script>';
        $c->set($key,$retval,'whatsnew');
        return $retval;
    }
}