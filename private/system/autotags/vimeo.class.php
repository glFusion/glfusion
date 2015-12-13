<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | vimeo.class.php                                                          |
// |                                                                          |
// | Vimeo PHP autotag functions                                              |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2014-2015 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

class autotag_vimeo extends BaseAutotag {

    public function __construct()
    {
        global $_AUTOTAGS;

        $this->description = $_AUTOTAGS['vimeo']['description'];
    }

    public function parse($p1, $p2='', $fulltag)
    {
        global $_CONF;

        $retval = '';

        $width = 560;
        $height = 315;
        $align = '';
        $pad = 0;
        $responsive = 0;
        $skip = 0;

        $px = explode (' ', $p2);
        if (is_array ($px)) {
            foreach ($px as $part) {
                if (substr ($part, 0, 6) == 'width:') {
                    $a = explode (':', $part);
                    $width = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 7) == 'height:') {
                    $a = explode (':', $part);
                    $height = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 6) == 'align:') {
                    $a = explode(':', $part);
                    $align = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 4) == 'pad:') {
                    $a = explode(':', $part);
                    $pad = $a[1];
                    $skip++;
                } elseif (substr ($part,0,11) == 'responsive:') {
                    $a = explode(':', $part);
                    $responsive = $a[1];
                    $skip++;
                } else {
                    break;
                }
            }

            if ($skip != 0) {
                if (count ($px) > $skip) {
                    for ($i = 0; $i < $skip; $i++) {
                        array_shift ($px);
                    }
                }
            }
        }
/*
        $extra = '';
        if ( $align != '') {
            if ( $align == 'center' ) {
                $extra .= ' text-align:center;';
            } else {
                $extra .= ' float:'.$align.';';
            }
        }
        if ( $pad != 0 ) {
            $extra .= ' padding:' . $pad . 'px;';
        }

        $retval .= '<div class="uk-responsive-width uk-responsive-height" style="width:'.$width.'px; height:'.$height.'px;'.$extra.'">';
*/

        $extra = '';
        if ( $align != '' && $responsive == 0) {
            if ( $align == 'center' ) {
                $extra .= ' text-align:center;';
            } else {
                $extra .= ' float:'.$align.';';
            }
        }
        if ( $pad != 0 ) {
            $extra .= ' padding:' . $pad . 'px;';
        }

        if ( $responsive == 1 ) {
            $retval .= '<div class="video [vimeo, widescreen]">';
        }

        if ( $extra != '' ) {
            $retval .= '<div style="'.$extra.'">';
        }

        $retval .= '<iframe src="https://player.vimeo.com/video/'.$p1.'" width="'.$width.'" height="'.$height.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        if ( $extra != '' ) {
            $retval .= '</div>';
        }

        if ( $responsive == 1 ) {
            $retval .= '</div>';
        }
/*
        $retval .= '</div>';
*/
        return $retval;
    }
}
?>