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

class autotag_mgslider extends BaseAutotag {

    function __construct()
    {
        global $_AUTOTAGS;

        $this->description = $_AUTOTAGS['mgslider']['description'];
    }

    function parse($p1, $p2='', $fulltag='')
    {
        global $_CONF, $_TABLES, $_MG_CONF, $MG_albums;

        $retval     = '';
        $skip       = 0;
        $template        = 'mgslider.thtml';
        $overlayPosition = 'top'; // top, bottom, left, right, center
        $kenBurns        = false;
        $autoPlay        = false;
        $height          = 'auto';

        $px = explode (' ', trim ($p2));
        if (is_array ($px)) {
            foreach ($px as $part) {
                if (substr ($part, 0, 9) == 'autoplay:') {
                    $a = explode (':', $part);
                    $autoPlay = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 9) == 'kenburns:') {
                    $a = explode (':', $part);
                    $kenBurns =  $a[1];
                    $skip++;
                } elseif (substr ($part,0, 8) == 'overlay:') {
                    $a = explode(':', $part);
                    $overlayPosition = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 9) == 'template:') {
                    $a = explode(':', $part);
                    $template = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 7) == 'height:') {
                    $a = explode(':', $part);
                    $height = $a[1];
                    $skip++;
                } else {
                    break;
                }
            }
        }

        MG_initAlbums();

        if ( $p1 == '' || $p1 == 0 ) {
            return $content;
        }
        if ( !isset($MG_albums[$p1]->id) || $MG_albums[$p1]->access == 0 ) {
            $link = '';
            $content = $fulltag;
            return $content;
        }

        $T = new Template($_CONF['path'].'system/autotags/');
        $T->set_file('page',$template);

        switch ($overlayPosition) {
            case 'center':
                $overlay_position = 'uk-flex uk-flex-center uk-flex-middle uk-text-center';
                break;
            case 'top' :
                $overlay_position = 'uk-overlay-top';
                break;
            case 'bottom' :
                $overlay_position = 'uk-overlay-bottom';
                break;
            default :
                $overlay_position = 'uk-overlay-top';
                break;
        }

        $options = '';
        $T->set_var('overlay_position',$overlay_position);
        if ( $kenBurns == true ) {
            $options .= 'kenburns:true,';
        } else {
            $options .= 'kenburns:false,';
        }
        if ( $autoPlay == 1 ) {
            $options .= 'autoplay:true';
        } else {
            $options .= 'autoplay:false';
        }
        $T->set_var('options',$options);

        $T->set_block('page','slides','sl');
        $T->set_block('page','dotnav','dn');

        $aid = (int) $p1;

        $counter = 0;

        $sql = "SELECT m.* FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
        " ON ma.media_id=m.media_id WHERE ma.album_id='" . DB_escapeString($aid) . "' AND m.media_type=0 AND m.include_ss=1 ORDER BY ma.media_order DESC";
        $result = DB_query($sql);
        while ($row = DB_fetchArray($result)) {
            $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
            $ext = $row['media_mime_ext'];
            if ( $media_size == false ) {
                continue;
            }
            $T->set_var('image_url',$_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $ext);
            $T->set_var('image_title',$row['media_title']);
            $T->set_var('image_desc',$row['media_desc']);
            $T->parse('sl','slides',true);
            $T->set_var('image_counter',$counter);
            $T->parse('dn','dotnav',true);
            $counter++;
        }

        $retval = $T->finish($T->parse('output','page'));

        return $retval;
    }
}
?>
