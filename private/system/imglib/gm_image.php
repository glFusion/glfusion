<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | gm_image.php                                                             |
// |                                                                          |
// | GraphicsMagick Graphic Library interface                                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2018 by the following authors:                        |
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
    die ('This file can not be used on its own.');
}

function _img_getIMversion()
{
    global $_CONF;

    $version = '';

    // get im version
    list($results, $status) = UTL_exec($_CONF['path_to_mogrify'] . '/gm identify');

    foreach ($results as $resultLine) {
        if (preg_match('/(ImageMagick|GraphicsMagick)\s+([\d\.r-]+)/', $resultLine, $matches)) {
	        $version = array($matches[1], $matches[2]);
        }
    }
    return $version;
}

/*
 * GraphicsMagick specific rotate function
 */
function _img_RotateImage($srcImage, $direction,$mimeType)
{
    global $_CONF;

    switch( $direction ) {
        case 'right' :
            $IM_rotate = "90";
            break;
        case 'left' :
            $IM_rotate = "-90";
            break;
        default :
            COM_errorLog("_img_rotateImage: Invalid direction passed to rotate, must be left or right");
            return array(false,'Invalid direction passed to rotate, must be left or right');
    }

    $tmp = pathinfo($srcImage);
    $tmpImage = $tmp['dirname'] .'/' . $tmp['filename'] . '_RT.' . $tmp['extension'];

    UTL_execWrapper('"' . $_CONF['path_to_mogrify'] . '/gm" convert -quality 100 -rotate ' . $IM_rotate . " $srcImage $tmpImage");
    if ( $_CONF['jhead_enabled'] == 1 && ($mimeType == 'image/jpeg' || $mimeType == 'image/jpg') ) {
        $rc = UTL_execWrapper('"' . $_CONF['path_to_jhead'] . "/jhead" . '"' . " -te " . $srcImage . " " . $tmpImage);
    }
    $rc = copy($tmpImage, $srcImage);
    @unlink($tmpImage);
    return array(true,'');
}

function _img_resizeImage($srcImage, $destImage, $sImageHeight, $sImageWidth, $dImageHeight, $dImageWidth, $mimeType)
{
    global $_CONF;

    $JpegQuality = $_CONF['jpg_orig_quality'];

    if ( $_CONF['debug_image_upload'] ) {
        COM_errorLog("_img_resizeImage: Resizing using GraphicsMagick src = " . $srcImage . " mimetype = " . $mimeType);
    }
    if ( ( $dImageHeight > $sImageHeight) && ($dImageWidth > $sImageWidth )) {
        $dImageWidth = $sImageWidth;
        $dImageHeight = $sImageHeight;
    }
    $newdim = $dImageWidth . "x" . $dImageHeight;

    if ( $mimeType == 'image/gif' ) {
        if ( $_CONF['debug_image_upload'] ) {
            $rc = UTL_execWrapper('"' . $_CONF['path_to_mogrify'] . "/gm\" convert $srcImage -verbose -coalesce -quality $JpegQuality -resize $newdim $destImage");
        } else {
            $rc = UTL_execWrapper('"' . $_CONF['path_to_mogrify'] . "/gm\" convert $srcImage -coalesce -quality $JpegQuality -resize $newdim $destImage");
        }
        if ( $rc != true ) {
            COM_errorLog("_img_resizeImage: Error - Unable to resize image - GraphicsMagick convert failed.");
            return array(false,'Error - Unable to resize image - GraphicsMagick convert failed.');
        }
        clearstatcache();
        if ( !file_exists($destImage) || !filesize($destImage) ) {
            COM_errorLog("_img_resizeImage: Error - Unable to resize image - GraphicsMagick convert failed.");
            return array(false,'Error - Unable to resize image - GraphicsMagick convert failed.');
        }
    } else {
        if ( $_CONF['debug_image_upload'] ) {
            $rc = UTL_execWrapper('"' . $_CONF['path_to_mogrify'] . "/gm\" convert -verbose -flatten -quality $JpegQuality -size $newdim $srcImage -geometry $newdim $destImage");
        } else {
            $rc = UTL_execWrapper('"' . $_CONF['path_to_mogrify'] . "/gm\" convert -flatten -quality $JpegQuality -thumbnail $newdim $srcImage -geometry $newdim $destImage");
        }
        if ( $rc != true ) {
            COM_errorLog("_img_resizeImage: Error - Unable to resize image - GraphicsMagick convert failed.");
            return array(false,'Error - Unable to resize image - GraphicsMagick convert failed.');
        }
        clearstatcache();
        if ( !file_exists($destImage) || !filesize($destImage) ) {
            COM_errorLog("_img_resizeImage: Error - Unable to resize image - GraphicsMagick convert failed.");
            return array(false,'Error - Unable to resize image - GraphicsMagick convert failed.');
        }
        if ( $_CONF['jhead_enabled'] == 1 ) {
            UTL_execWrapper('"' . $_CONF['path_to_jhead'] . "/jhead" . '"' . " -v -te " . $srcImage . " " . $destImage);
        }
    }
    return array(true,'');
}

/*
 * GraphicsMagick Specific method to convert image
 */
function _img_convertImageFormat($srcImage,$destImage,$destFormat, $mimeType)
{
    global $_CONF;

    COM_errorLog("_img_convertImageFormat: Converting image to " . $destFormat);
    $rc = UTL_execWrapper('"' . $_CONF['path_to_mogrify'] . "/gm\" convert -flatten -quality " . $_CONF['jpg_orig_quality'] . " $srcImage -geometry +0+0 $destImage");
    if ( $rc != true ) {
        COM_errorLog("_img_convertImageFormat: Error converting " . $srcImage . " to " . $destImage);
        return array(false,'GraphicsMagick convert failed to convert image.');
    }
    clearstatcache();
    if ( !file_exists($destImage) || !filesize($destImage) ) {
        COM_errorLog("_img_resizeImage: Error - Unable to resize image - GraphicsMagick convert failed.");
        return array(false,'GraphicsMagick convert failed to convert image.');
    }

    if ( $srcImage != $destImage) {
        @unlink($srcImage);
    }
    return array(true,'');
}

function _img_watermarkImage($origImage, $watermarkImage, $opacity, $location, $mimeType )
{
    global $_CONF;

    if ( $_CONF['debug_image_upload'] ) {
        COM_errorLog("_img_watermarkImage: Using GraphicsMagick to watermark image.");
    }
    switch( $location ) {
        case 'topleft' : // 1 :
            $location = "NorthWest";
            break;
        case 'topcenter' : // 2:
            $location = "North";
            break;
        case 'topright': // 3:
            $location = "NorthEast";
            break;
        case 'leftmiddle' : // 4 :
            $location = "West";
            break;
        case 'center' : // 5 :
            $location = "Center";
            break;
        case 'rightmiddle' : // 6 :
            $location = "East";
            break;
        case 'bottomleft' : //7 :
            $location = "SouthWest";
            break;
        case 'bottomcenter' : // 8 :
            $location = "South";
            break;
        case 'bottomright' : // 9 :
            $location = "SouthEast";
            break;
        default:
            COM_errorLog("_img_watermarkImage: Unknown watermark location: " . $location);
            return array(false,'Unknown watermark location');
            break;
    }
    $rc = UTL_execWrapper('"' . $_CONF['path_to_mogrify'] . "/gm\" convert $watermarkImage -fill grey50 -colorize 40  miff:- | " . '"' . $_CONF['path_to_mogrify'] . "/composite" . '"' . " -dissolve " . $opacity . " -gravity " . $location . " - $origImage $origImage");
    COM_errorLog("_img_watermarkImage: Watermark successfully applied (GraphicsMagick)");
    return array($rc,'');
}

function _img_squareThumbnail($srcImage, $destImage, $sImageHeight, $sImageWidth, $dSize, $mimeType)
{
    global $_CONF;

    $opt = '-quality ' . 91;

    if ($_CONF['debug_image_upload']) {
        $opt .= ' -verbose';
        COM_errorLog("_img_squareThumbnail: Resizing using GraphicsMagick src = " . $srcImage . " mimetype = " . $mimeType);
    }

    if ($mimeType == 'image/gif') {
        $opt .= ' -coalesce';
    } else {
        $opt .= ' -flatten';
    }

    $dImageWidth = $dSize;
    $dImageHeight = $dSize;
    $dSizeX2 = (int) $dSize * 2;

    $binary = 'gm' . ((PHP_OS == 'WINNT') ? '.exe' : '');

    $opt .= " -thumbnail x".$dSizeX2;
    $opt .= " -resize " . ((PHP_OS == 'WINNT') ? $dSizeX2."x^<" : "'".$dSizeX2."x<'");

    $rc = UTL_execWrapper('"' . $_CONF['path_to_mogrify'] . $binary . '" convert'
                          . " $opt -resize 50% -gravity center -crop ".$dSize."x".$dSize."+0+0 +repage -quality 91 $srcImage $destImage");

    if ($rc != true) {
        COM_errorLog("_img_resizeImage_crop: Error - Unable to resize image - GraphicsMagick convert failed.");
        return array(false, 'Error - Unable to resize image (square thumbnail) - GraphicsMagick convert failed.');
    }

    clearstatcache();

    if (!file_exists($destImage) || !filesize($destImage)) {
        COM_errorLog("_img_resizeImage_crop: Error - Unable to resize image - GraphicsMagick convert failed.");
        return array(false, 'Error - Unable to resize image (square thumbnail) - GraphicsMagick convert failed.');
    }
    if (($mimeType != 'image/gif') && ($_CONF['jhead_enabled'] == 1)) {
        UTL_execWrapper('"' . $_CONF['path_to_jhead'] . "/jhead" . '"' . " -v -te " . $srcImage . " " . $destImage);
    }
    return array(true, '');
}
?>