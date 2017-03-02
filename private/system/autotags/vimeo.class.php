<?php
/**
* glFusion CMS
*
* Vimeo PHP auto tag
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2017 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*/

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
        return $retval;
    }
}
?>