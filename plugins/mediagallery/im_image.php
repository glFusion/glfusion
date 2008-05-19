<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id:: im_image.php 1326 2007-10-21 06:06:33Z mevans0263                  $|
// |                                                                           |
// | ImageMagick Graphic Library interface for Media Gallery                   |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2004-2007 by the following authors:                         |
// |                                                                           |
// | Author:                                                                   |
// | Mark R. Evans               -    mark@gllabs.org                          |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

if (strpos ($_SERVER['PHP_SELF'], 'im_image.php') !== false)
{
    die ('This file can not be used on its own.');
}

function _mg_getIMversion() {
    global $_MG_CONF;

    // get im version
    list($results, $status) = MG_exec($_MG_CONF['graphicspackage_path'] . '/identify');

    foreach ($results as $resultLine) {
        if (preg_match('/(ImageMagick|GraphicsMagick)\s+([\d\.r-]+)/', $resultLine, $matches)) {
	        $version = array($matches[1], $matches[2]);
        }
    }
    return $version;
}

/*
 * ImageMagick specific rotate function
 */
function _mg_RotateImage($srcImage, $direction,$mimeType) {
    global $_MG_CONF;

    switch( $direction ) {
        case 'right' :
            $IM_rotate = "90";
            break;
        case 'left' :
            $IM_rotate = "-90";
            break;
        default :
            COM_errorLog("MG_rotateImage: Invalid direction passed to rotate, must be left or right");
            return array(false,'Invalid direction passed to rotate, must be left or right');
    }

    $tmpImage = $srcImage . '.rt';

    MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/convert" . '"' . " -quality 100 -rotate " . $IM_rotate . " $srcImage $tmpImage");
    if ( $_MG_CONF['jhead_enabled'] == 1 && ($mimeType == 'image/jpeg' || $mimeType == 'image/jpg') ) {
        $rc = MG_execWrapper('"' . $_MG_CONF['jhead_path'] . "/jhead" . '"' . " -te " . $srcImage . " " . $tmpImage);
    }
    $rc = copy($tmpImage, $srcImage);
    @unlink($tmpImage);
    return array(true,'');
}

function _mg_resizeImage($srcImage, $destImage, $sImageHeight, $sImageWidth, $dImageHeight, $dImageWidth, $mimeType) {
    global $_MG_CONF;

    $version = _mg_getIMversion();

    $rc = version_compare($version[1],"6.3.4");
    if ( $rc == -1 ) {
        $noLayers = 1;
    } else {
        $noLayers = 0;
    }

    $JpegQuality = 85;

    if ( $_MG_CONF['verbose'] ) {
        COM_errorLog("MG_resizeImage: Resizing using ImageMagick src = " . $srcImage . " mimetype = " . $mimeType);
    }
    if ( ( $dImageHeight > $sImageHeight) && ($dImageWidth > $sImageWidth )) {
        $dImageWidth = $sImageWidth;
        $dImageHeight = $sImageHeight;
    }
    $newdim = $dImageWidth . "x" . $dImageHeight;

    if ( $mimeType == 'image/gif' ) {
        if ( $_MG_CONF['verbose'] ) {
            if ( $noLayers == 0 ) {
                $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/convert" . '"' . " $srcImage -verbose -coalesce -quality $JpegQuality -resize $newdim -layers Optimize $destImage");
            } else {
                $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/convert" . '"' . " $srcImage -verbose -coalesce -quality $JpegQuality -resize $newdim $destImage");
            }
        } else {
            if ( $noLayers == 0 ) {
                $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/convert" . '"' . " $srcImage -coalesce -quality $JpegQuality -resize $newdim -layers Optimize $destImage");
            } else {
                $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/convert" . '"' . " $srcImage -coalesce -quality $JpegQuality -resize $newdim $destImage");
            }
        }
        if ( $rc != true ) {
            COM_errorLog("MG_resizeImage: Error - Unable to resize image - ImageMagick convert failed.");
            return array(false,'Error - Unable to resize image - ImageMagick convert failed.');
        }
        clearstatcache();
        if ( !file_exists($destImage) || !filesize($destImage) ) {
            COM_errorLog("MG_resizeImage: Error - Unable to resize image - ImageMagick convert failed.");
            return array(false,'Error - Unable to resize image - ImageMagick convert failed.');
        }
    } else {
        if ( $_MG_CONF['verbose'] ) {
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/convert" . '"' . " -verbose -flatten -quality $JpegQuality -size $newdim $srcImage -geometry $newdim $destImage");
        } else {
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/convert" . '"' . " -flatten -quality $JpegQuality -size $newdim $srcImage -geometry $newdim $destImage");
        }
        if ( $rc != true ) {
            COM_errorLog("MG_resizeImage: Error - Unable to resize image - ImageMagick convert failed.");
            return array(false,'Error - Unable to resize image - ImageMagick convert failed.');
        }
        clearstatcache();
        if ( !file_exists($destImage) || !filesize($destImage) ) {
            COM_errorLog("MG_resizeImage: Error - Unable to resize image - ImageMagick convert failed.");
            return array(false,'Error - Unable to resize image - ImageMagick convert failed.');
        }
        if ( $_MG_CONF['jhead_enabled'] == 1 ) {
            MG_execWrapper('"' . $_MG_CONF['jhead_path'] . "/jhead" . '"' . " -v -te " . $srcImage . " " . $destImage);
        }
    }
    return array(true,'');
}

/*
 * ImageMagick Specific method to convert image
 */


function _mg_convertImageFormat($srcImage,$destImage,$destFormat, $mimeType) {
    global $_MG_CONF;

    COM_errorLog("MG_convertImageFormat: Converting image to " . $destFormat);
    $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/convert" . '"' . " -flatten -quality " . $_MG_CONF['jpg_orig_quality'] . " $srcImage -geometry +0+0 $destImage");
    if ( $rc != true ) {
        COM_errorLog("MG_convertImageFormat: Error converting " . $srcImage . " to " . $destImage);
        return array(false,'ImageMagick convert failed to convert image.');
    }
    clearstatcache();
    if ( !file_exists($destImage) || !filesize($destImage) ) {
        COM_errorLog("MG_resizeImage: Error - Unable to resize image - ImageMagick convert failed.");
        return array(false,'ImageMagick convert failed to convert image.');
    }

    if ( $srcImage != $destImage) {
        @unlink($srcImage);
    }
    return array(true,'');
}

function _mg_watermarkImage($origImage, $watermarkImage, $opacity, $location, $mimeType ) {
    global $_MG_CONF;

    if ( $_MG_CONF['verbose'] ) {
        COM_errorLog("MG_watermarkImage: Using ImageMagick to watermark image.");
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
            COM_errorLog("MG_watermarkImage: Unknown watermark location: " . $location);
            return array(false,'Unknown watermark location');
            break;
    }
    $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/convert" . '" ' . " $watermarkImage -fill grey50 -colorize 40  miff:- | " . '"' . $_MG_CONF['graphicspackage_path'] . "/composite" . '"' . " -dissolve " . $opacity . " -gravity " . $location . " - $origImage $origImage");
    COM_errorLog("MG_watermarkImage: Watermark successfully applied (ImageMagick)");
    return array($rc,'');
}

?>