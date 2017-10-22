<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | classFrame.php                                                           |
// |                                                                          |
// | Image frames (borders) routines                                          |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on work from the Gallery2 Project                                  |
// | Copyright (C) 2006 by the following authors:                             |
// | classFrame.php is based on the work of Alan Harder <alan.harder@sun.com> |
// | from the ImageFrame support in Gallery2                                  |
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

class mgFrame {
    var $name;
    var $frame;
    var $_valid;

    function __construct( ) {
        $this->frame = array();
    }

    function constructor ( $name ) {
        global $_MG_CONF;

        $this->frame = array();

        if ( $name == 'mgAlbum' || $name == 'mgShadow' || $name == 'none' ) {
            $this->name = $name;
            $this->_valid = 1;
            $this->frame['wHL'] = 0;
            $this->frame['wHR'] = 0;
            $this->frame['hVT'] = 0;
            $this->frame['hVB'] = 0;
            $this->frame['rowspan'] = 1;
            $this->frame['colspan'] = 1;
        } elseif (file_exists( $_MG_CONF['path_html'] . '/frames/' . $name . '/frame.inc' ) ) {
            $this->name = $name;
            $this->_valid = 1;
            include( $_MG_CONF['path_html'] . 'frames/' . $name . '/frame.inc');
            $this->frame['wHL'] = max($frameData['widthTTL'], $frameData['widthBBL']);
            $this->frame['wHR'] = max($frameData['widthTTR'], $frameData['widthBBR']);
            $this->frame['hVT'] = max($frameData['heightLLT'], $frameData['heightRRT']);
            $this->frame['hVB'] = max($frameData['heightLLB'], $frameData['heightRRB']);
            $this->frame['rowspan'] = 1 + ($this->frame['hVT']>0?1:0) + ($this->frame['hVB']>0?1:0);
            $this->frame['colspan'] = 1 + ($this->frame['wHL']>0?1:0) + ($this->frame['wHR']>0?1:0);

            foreach ($frameData as $key => $value) {
                $this->frame[$key] = $value;
            }
        } else {
            $this->_valid = 0;
            $this->name = $name;
            $this->frame['wHL'] = 0;
            $this->frame['wHR'] = 0;
            $this->frame['hVT'] = 0;
            $this->frame['hVB'] = 0;
            $this->frame['rowspan'] = 1;
            $this->frame['colspan'] = 1;
            // fall back to our default...
        }
    }

    function getTemplate( ) {
        global $_MG_CONF;

        if ( $this->_valid ) {
            if ( $this->name == 'mgShadow' || $this->name == '' ) {
		        $retval = '<div class="out" style="width:{border_width}px;">' . LB;
		        $retval .= '<div class="in ltin tpin">' . LB;
		        $retval .= '{media_link_start}';
                $retval .= '<img src="{media_thumbnail}" alt="{media_tag}" title="{media_tag}" style="width: {media_width}px; height: {media_height}px; border: none;"/>' . LB;
                $retval .= '{media_link_end}' . LB;
		        $retval .= '</div>' . LB;
		        $retval .= '</div>' . LB;
		        return $retval;
	        }

	        if ( $this->name == 'mgAlbum' ) {
    	        $retval = '<div class="out" style="width:{border_width}px">' . LB;
		        $retval .= '<div class="in2">' . LB;
		        $retval .= '<div class="in">' . LB;
		        $retval .= '{media_link_start}' . LB;
                $retval .= '<img src="{media_thumbnail}" alt="" style="width: {media_width}px; height: {media_height}px; border: none;"/>' . LB;
                $retval .= '{media_link_end}' . LB;
        		$retval .= '</div>' . LB;
		        $retval .= '</div>' . LB;
		        $retval .= '</div>' . LB;
		        return $retval;
	        }

	        if ( $this->name == 'none' ) {
		        $retval  = '{media_link_start}' . LB;
                $retval .= '<img src="{media_thumbnail}" alt="" style="width: {media_width}px; height: {media_height}px; border: none;"/>' . LB;
                $retval .= '{media_link_end}' . LB;
                return $retval;
			}

            $retval = '<div>' . LB;
            $retval .= '<table class="mgFrame_' . $this->name . '" border="0" cellspacing="0" cellpadding="0" style="margin:0 auto;">' . LB;

            if ( !empty($this->frame['imageTT']) || !empty($this->frame['imageTL'])  || !empty($this->frame['imageTR']) ||
                 !empty($this->frame['imageTTL']) || !empty($this->frame['imageTr']) ) {
                $retval .= '<tr><td class="TL"></td>' . LB;
                if ( $this->frame['wHL'] ) {
                    $retval .= '<td class="TTL"></td>' . LB;
                }
                $retval .= '<td class="TT"';
                if ( $this->frame['wHL'] || $this->frame['wHR']) {
                    $retval .= ' style="width:{frWidth}px"';
                }
                $retval .= '>';
                $retval .= '<div class="H"></div></td>' . LB;
                if ( $this->frame['wHR'] ) {
                    $retval .= '<td class="TTR"></td>' . LB;
                }
                $retval .= '<td class="TR"></td>' . LB;
                $retval .= '</tr>';
            }
            $retval .= '<tr>' . LB;

            if ( $this->frame['hVT'] ) {
                $retval .= '<td class="LLT"></td>' . LB;
            } else {
                $retval .= '<td class="LL"';
                if ( $this->frame['hVT'] || $this->frame['hVB'] ) {
                    $retval .= ' style="height:{frHeight}px"';
                }
                $retval .= '>';
                $retval .= '<div class="V">&nbsp;</div></td>' . LB;
            }
            $retval .= '<td rowspan="' . $this->frame['rowspan'] . '" colspan="' . $this->frame['colspan'] . '" class="IMG">' . LB;

            $retval .= '{media_link_start}' . LB;
            $retval .= '<img src="{media_thumbnail}" alt="{media_tag}" title="{media_tag}" class="ImageFrame_image" style="width: {media_width}px; height: {media_height}px; border: none;"/>' . LB;
			$retval .= '{media_link_end}' . LB;
            $retval .= '</td>' . LB;

            if ( $this->frame['hVT'] ) {
                $retval .= '<td class="RRT"></td>' . LB;
            } else {
                $retval .= '<td class="RR"';
                if ( $this->frame['hVT'] || $this->frame['hVB'] ) {
                    $retval .= ' style="height:{frHeight}px"';
                }
                $retval .= '>';
                $retval .= '<div class="V">&nbsp;</div></td>' . LB;
            }
            $retval .= '</tr>' . LB;

            if ( $this->frame['hVT'] ) {
                $retval .= '<tr>';
                $retval .= '<td class="LL"';
                if ( $this->frame['hVT'] || $this->frame['hVB'] ) {
                    $retval .= ' style="height:{frHeight}px"';
                }
                $retval .= '>';
                $retval .= '<div class="V">&nbsp;</div></td>' . LB;

                $retval .= '<td class="RR"';
                if ( $this->frame['hVT'] || $this->frame['hVB'] ) {
                    $retval .= ' style="height:{frHeight}px"';
                }
                $retval .= '>';
                $retval .= '<div class="V">&nbsp;</div></td>' . LB;
                $retval .= '</tr>' . LB;
            }

            if ( $this->frame['hVB'] ) {
                $retval .= '<tr>' . LB;
                $retval .= '<td class="LLB"></td>' . LB;
                $retval .= '<td class="RRB"></td>' . LB;
                $retval .= '</tr>' . LB;
            }

            if (!empty($this->frame['imageBB']) || !empty($this->frame['imageBL']) || !empty($this->frame['imageBR']) ||
                !empty($this->frame['imageBBL']) || !empty($this->frame['imageBBR']) ) {

                $retval .= '<tr>' . LB;
                $retval .= '<td class="BL"></td>' . LB;
                if ( $this->frame['wHL'] ) {
                    $retval .= '<td class="BBL"></td>' . LB;
                }
                $retval .= '<td class="BB"';
                if ( $this->frame['wHL'] || $this->frame['wHR'] ) {
                    $retval .= ' style="width:{frWidth}px"';
                }
                $retval .= '>';
                $retval .= '<div class="H"></div></td>' . LB;

                if ( $this->frame['wHR']) {
                    $retval .= '<td class="BBR"></td>' . LB;
                }
                $retval .= '<td class="BR"></td>' . LB;
                $retval .= '</tr>' . LB;
            }
            $retval .= '</table>' . LB;
            $retval .= '</div>' . LB;
        } else {
            $retval = '
              <div class=out style="width:{border_width}px;">
              <div class="in ltin tpin">
              {media_link_start}
              <img src="{media_thumbnail}" alt="{media_tag}" title="{media_tag}" style="width:{media_width}px; height:{media_height}px; border: none;"/>
              {media_link_end}
              </div>
              </div>';
        }
        return $retval;
    }

    function getCSS ( ) {
        global $_MG_CONF;

        $retval = '';

        if ( $this->_valid ) {
            if ( $this->name == 'mgShadow' || $this->name == 'mgAlbum' || $this->name == 'none') {
                return '';
            }

            $retval .= 'img.ImageFrame_image { vertical-align:bottom; border:none; }' . LB;
            $retval .= 'img.ImageFrame_solid { border: 1px solid black !important }' . LB;

            $retval .= 'table.mgFrame_' . $this->name . ' { direction: ltr; }' . LB;

            if (!empty( $this->frame['imageTL']) ) {
                $retval .= 'table.mgFrame_' . $this->name . ' .TL { width:' . $this->frame['widthTL'] . 'px; height:' . $this->frame['heightTL'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageTL'] . ') no-repeat; }' . LB;
            }

            if ( !empty( $this->frame['imageTTL'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .TTL { width:' . $this->frame['widthTTL'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageTTL'] . ') no-repeat; }' . LB;
            }
            if (!empty( $this->frame['imageTT'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .TT { height:' . $this->frame['heightTT'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageTT'] . ') repeat-x; }' . LB;
            }

            if (!empty( $this->frame['imageTTR'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .TTR { width:' . $this->frame['widthTTR'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageTTR'] . ') no-repeat; }' . LB;
            }

            if (!empty( $this->frame['imageTR'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .TR { width:' . $this->frame['widthTR'] . 'px; height:' . $this->frame['heightTR'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageTR'] . ') no-repeat; }' . LB;
            }

            if (!empty( $this->frame['imageLLT'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .LLT { height:' . $this->frame['heightLLT'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageLLT'] . ') no-repeat; }' . LB;
            }

            if (!empty( $this->frame['imageLL'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .LL { width:' . $this->frame['widthLL'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageLL'] . ') repeat-y; }' . LB;
                $retval .= 'table.mgFrame_' . $this->name . ' .LL div.V { width:' . $this->frame['widthLL'] . 'px; }' . LB;
            }

            if (!empty( $this->frame['imageLLB'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .LLB { height:' . $this->frame['heightLLB'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageLLB'] . ') no-repeat; }' . LB;
            }

            if (!empty( $this->frame['imageRRT'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .RRT { height:' . $this->frame['heightRRT'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageRRT'] . ') no-repeat; }' . LB;
            }

            if (!empty( $this->frame['imageRR'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .RR { width:' . $this->frame['widthRR'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageRR'] . ') repeat-y; }' . LB;
                $retval .= 'table.mgFrame_' . $this->name . ' .RR div.V { width:' . $this->frame['widthRR'] . 'px; }' . LB;
            }

            if (!empty( $this->frame['imageRRB'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .RRB { height:' . $this->frame['heightRRB'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageRRB'] . ') no-repeat; }'  . LB;
            }

            if (!empty( $this->frame['imageBL'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .BL { width:' . $this->frame['widthBL'] . 'px; height:' . $this->frame['heightBL'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageBL'] . ') no-repeat; }' . LB;
            }

            if (!empty( $this->frame['imageBBL'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .BBL { width:' . $this->frame['widthBBL'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageBBL'] . ') no-repeat; }' . LB;
            }

            if (!empty( $this->frame['imageBB'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .BB { height:' . $this->frame['heightBB'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageBB'] . ') repeat-x; }' . LB;
            }

            if (!empty( $this->frame['imageBBR'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .BBR { width:' . $this->frame['widthBBR'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageBBR'] . ') no-repeat; }' . LB;
            }

            if (!empty( $this->frame['imageBR'])) {
                $retval .= 'table.mgFrame_' . $this->name . ' .BR { width:' . $this->frame['widthBR'] . 'px; height:' . $this->frame['heightBR'] . 'px; background:url(' . $_MG_CONF['site_url'] . '/frames/' . $this->name . '/' . $this->frame['imageBR'] . ') no-repeat; }' . LB;
            }
            $retval .= 'table.mgFrame_' . $this->name . ' td { font-size:1px } /* For IE */' . LB;
            $retval .= 'td div.H { width:1px; height:0; }' . LB;
            $retval .= 'td div.V { width:0; height:1px; }'. LB;
        }
        return $retval;
    }

    function getFrames() {
        global $_MG_CONF, $LANG_MG01;

        $skins = array();

        // hard code the MG defaults

        $skins[1]['dir']='mgShadow';
        $skins[1]['name']='MG Shadow';
        $skins[2]['dir'] = 'mgAlbum';
        $skins[2]['name']= 'MG Album';
        $skins[0]['dir'] = 'none';
        $skins[0]['name']= $LANG_MG01['none'];

        $i = 3;

        $directory = $_MG_CONF['path_html'] . 'frames/';
        $dh = @opendir($directory);
        if ( $dh != false ) {
            while ( ( $file = @readdir($dh) ) != false ) {
                if ( $file == '..' || $file == '.' ) {
                    continue;
                }
                $skindir = $directory . $file;
                if (@is_dir($skindir)) {
                    if ( file_exists($skindir . '/' . 'frame.inc') ) {
                        include ( $skindir . '/' . 'frame.inc');
                        $skins[$i]['dir'] = $file;
                        $skins[$i]['name'] = $frameData['name'];
                        $i++;
                    }
                }
            }
            closedir($dh);
        }
        $sSkins = MG_array_sort($skins,'name');
        return $sSkins;
    }
}

function MG_array_sort($array, $key) {
    for ($i=0;$i<sizeof($array);$i++) {
        $sort_values[$i] = $array[$i][$key];
    }
    asort($sort_values);
    foreach ( $sort_values AS $arr_key => $arr_val ) {
        $sorted_arr[] = $array[$arr_key];
    }
    return $sorted_arr;
}
?>