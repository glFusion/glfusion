<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id:: pbm_image.php 1312 2007-10-20 23:22:42Z mevans0263                 $|
// |                                                                           |
// | NetPBM Graphic Library interface for Media Gallery                        |
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

if (strpos ($_SERVER['PHP_SELF'], 'pbm_image.php') !== false)
{
    die ('This file can not be used on its own.');
}

function _mg_resizeImage($srcImage, $destImage, $sImageHeight, $sImageWidth, $dImageHeight, $dImageWidth, $mimeType) {
    global $_MG_CONF;

    $JpegQuality = 85;

    if ( $_MG_CONF['verbose'] ) {
        COM_errorLog("MG_resizeImage: Resizing using NetPBM src = " . $srcImage . " mimetype = " . $mimeType);
    }
    // determine which program to use, pamscale or pnmscale...
    if ( file_exists( $_MG_CONF['graphicspackage_path'] . "pamscale" ) ) {
        $pamscale = 'pamscale';
    } else {
        $pamscale = 'pnmscale';
    }
    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/jpegtopnm" . '" ' . $srcImage . " | " . '"' . $_MG_CONF['graphicspackage_path'] . "/" . $pamscale .'"' . " -xsize " . $dImageWidth . " -ysize " . $dImageHeight . " | " . '"' . $_MG_CONF['graphicspackage_path'] . "/pnmtojpeg" . '" > ' . $destImage);

            if ( $_MG_CONF['jhead_enabled'] == 1 ) {
                MG_execWrapper('"' . $_MG_CONF['jhead_path'] . "/jhead" . '"' . " -v -te " . $srcImage . " " . $destImage);
            }
            if ( $rc != true ) {
                COM_errorLog("MG_resizeImage: Unable to resize image - NetPBM failed.");
                return array(false,'Unable to resize image - NetPBM failed.');
            }
            break;
        case 'image/bmp' :
            if ( ( $dImageHeight > $sImageHeight) && ($dImageWidth > $sImageWidth )) {
                $dImageWidth = $sImageWidth;
                $dImageHeight = $sImageHeight;
            }
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/bmptopnm" . '" ' . $srcImage . " | " . '"' . $_MG_CONF['graphicspackage_path'] . "/" . $pamscale . '"' . " -xsize " . $dImageWidth . " -ysize " . $dImageHeight . " | " . '"' . $_MG_CONF['graphicspackage_path'] . "/ppmtobmp" .'" > ' . $destImage);
            if ( $rc != true ) {
                COM_errorLog("MG_resizeImage: Unable to resize image - NetPBM failed.");
                return array(false,'Unable to resize image - NetPBM failed.');
            }
            break;
        case 'image/gif' :
            if ( !file_exists($_MG_CONF['graphicspackage_path'] . "/pamtogif") ) {
                COM_errorLog("MG_resizeImage: NetPBM installation does have have pamtogif binary.");
                return array(false,'NetPBM installation does have have pamtogif binary.');
            }
            if ( ( $dImageHeight > $sImageHeight) && ($dImageWidth > $sImageWidth )) {
                $dImageWidth = $sImageWidth;
                $dImageHeight = $sImageHeight;
            }
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/giftopnm" . '" ' . $srcImage . " | " . '"' . $_MG_CONF['graphicspackage_path'] . "/" . $pamscale . '"' . " -xsize " . $dImageWidth . " -ysize " . $dImageHeight . " | " . '"' . $_MG_CONF['graphicspackage_path'] . "/pamtogif" . '" > ' . $destImage);
            if ( $rc != true ) {
                COM_errorLog("MG_resizeImage: Unable to resize image - NetPBM failed.");
                @unlink($destImage);
                return array(false,'Unable to resize image - NetPBM failed.');
            }
            break;
        case 'image/png' :
            if ( ( $dImageHeight > $sImageHeight) && ($dImageWidth > $sImageWidth )) {
                $dImageWidth = $sImageWidth;
                $dImageHeight = $sImageHeight;
            }
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/pngtopnm" . '" ' . $srcImage . " | " . '"' . $_MG_CONF['graphicspackage_path'] . "/" . $pamscale . '"' . " -xsize " . $dImageWidth . " -ysize " . $dImageHeight . " | " . '"' . $_MG_CONF['graphicspackage_path'] . "/pnmtopng" . '" > ' . $destImage);
            if ( $rc != true ) {
                COM_errorLog("MG_resizeImage: Unable to resize image - NetPBM failed.");
                return array(false,'Unable to resize image - NetPBM failed.');
            }
            break;
        case 'image/tiff' :
            if ( ( $dImageHeight > $sImageHeight) && ($dImageWidth > $sImageWidth )) {
                $dImageWidth = $sImageWidth;
                $dImageHeight = $sImageHeight;
            }
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/tifftopnm" . '" ' . $srcImage . " | " . '"' . $_MG_CONF['graphicspackage_path'] . "/" . $pamscale . '"' . " -xsize " . $dImageWidth . " -ysize " . $dImageHeight . " | " . '"' . $_MG_CONF['graphicspackage_path'] . "/pnmtotiff" . '" > ' . $destImage);
            if ( $rc != true ) {
                COM_errorLog("MG_resizeImage: Unable to resize image - NetPBM failed.");
                return array(false,'Unable to resize image - NetPBM failed.');
            }
            break;
        case 'image/x-targa' :
        case 'image/tga' :
                COM_errorLog("MG_resizeImage: TGA files not supported by NetPBM");
                return array(false,'TGA format not supported by NetPBM');
                break;
    }
    return array(true,'');
}

/*
 * ImageMagick specific rotate function
 */
function _mg_RotateImage($srcImage, $direction,$mimeType) {
    global $_MG_CONF;

    switch( $direction ) {
        case 'right' :
            $NP_rotate = "270";
            break;
        case 'left' :
            $NP_rotate = "90";
            break;
        default :
            COM_errorLog("MG_rotateImage: Invalid direction passed to rotate, must be left or right");
            return array(false,'Invalid direction passed to rotate, must be left or right');
    }

    $pamflip = '/pamflip';

    $tmpImage = $srcImage . '.rt';

    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/jpegtopnm" . '" ' . $srcImage . " | " . '"' . $_MG_CONF['graphicspackage_path'] . $pamflip . '" -r' . $NP_rotate . " > " . $srcImage . ".PNM");
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/pnmtojpeg" . '"' . " -quality=100 " . $srcImage . ".PNM > " . $tmpImage);
            @unlink($srcImage . ".PNM");
            if ( $_MG_CONF['jhead_enabled'] == 1 ) {
                $rc = MG_execWrapper('"' . $_MG_CONF['jhead_path'] . "/jhead" . '"' . " -te " . $srcImage . " " . $tmpImage);
            }
            $rc = copy($tmpImage, $srcImage);
            @unlink($tmpImage);
            break;
        case 'image/bmp' :
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/bmptopnm" . '" ' . $srcImage . " | " . '"' . $_MG_CONF['graphicspackage_path'] . $pamflip . '" -r' . $NP_rotate . " > " . $srcImage . ".PNM");
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/pnmtobmp" . '"' . " " . $srcImage . ".PNM > " . $tmpImage);
            @unlink($srcImage . ".PNM");
            $rc = copy($tmpImage, $srcImage);
            @unlink($tmpImage);
            break;
        case 'image/gif' :
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/giftopnm" . '" ' . $srcImage . " | " . '"' . $_MG_CONF['graphicspackage_path'] . $pamflip . '" -r' . $NP_rotate . " > " . $srcImage . ".PNM");
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/pnmtogif" . '"' . " " . $srcImage . ".PNM > " . $tmpImage);
            @unlink($srcImage . ".PNM");
            $rc = copy($tmpImage, $srcImage);
            @unlink($tmpImage);
            break;
        case 'image/png' :
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/pngtopnm" . '" ' . $srcImage . " | " . '"' . $_MG_CONF['graphicspackage_path'] . $pamflip . '" -r' . $NP_rotate . " > " . $srcImage . ".PNM");
            if ( $rc != true ) {
                COM_errorLog("MG_rotateImage: Error executing pngtopnm (NetPBM)");
                return array(false,'Error executing pngtopnm (NetPBM)');
            }
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/pnmtopng" . '"' . " " . $srcImage . ".PNM > " . $tmpImage);
            @unlink($srcImage . ".PNM");
            $rc = copy($tmpImage, $srcImage);
            @unlink($tmpImage);
            break;
        default:
            COM_errorLog("MG_roateImage: NetPBM only support JPG, BMP, GIF or PNG");
            return array(false,'NetPBM only supports JPG, BMP, GIF or PNG');
    }

    return array(true,'');
}

function _mg_convertImageFormat($srcImage,$destImage,$destFormat,$mimeType) {
    global $_MG_CONF;

    if ( $_MG_CONF['verbose'] ) {
        COM_errorLog("MG_convertImageFormat: Converting using NetPBM src = " . $srcImage . " mimetype = " . $mimeType);
    }
    // determine which program to use, pamscale or pnmscale...
    if ( file_exists( $_MG_CONF['graphicspackage_path'] . "pamscale" ) ) {
        $pamscale = 'pamscale';
    } else {
        $pamscale = 'pnmscale';
    }
    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            $cvtFrom = '/jpegtopnm';
            break;
        case 'image/bmp' :
            $cvtFrom = '/bmptopnm';
            break;
        case 'image/gif' :
            $cvtFrom = '/giftopnm';
            break;
        case 'image/png' :
            $cvtFrom = '/pngtopnm';
            break;
        case 'image/tiff' :
            $cvtFrom = '/tifftopnm';
            break;
        case 'image/tga' :
        case 'image/targa-x' :
            $cvtFrom = '/tgatoppm';
            break;
        default :
            COM_errorLog("MG_convertImageFormat: NetPBM only supports JPG, BMP, GIF, PNG, TIFF, and TGA source images.");
            return array(false,'NetPBM only supports JPG, BMP, GIF, PNG, TIFF and TGA formats.');
    }
    switch ( $destFormat ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            $cvtTo = '/pnmtojpeg';
            break;
        case 'image/bmp' :
            $cvtTo = '/pnmtobmp';
            break;
        case 'image/gif' :
            $cvtTo = '/pnmtogif';
            break;
        case 'image/png' :
            $cvtTo = '/pnmtopng';
            break;
        case 'image/tiff' :
            $cvtTo = '/pnmtotiff';
            break;
        default :
            COM_errorLog("MG_convertImageFormat: NetPBM only supports JPG, BMP, GIF, PNG, and TIFF destination formats.");
            return array(false,'NetPBM only supports JPG, BMP, GIF, PNG, TIFF and TGA formats.');
    }
    $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . $cvtFrom . '" ' . $srcImage . " | " . '"' . $_MG_CONF['graphicspackage_path'] . $cvtTo . '" > ' . $destImage);
    if ( $rc == true ) {
        if ( $srcImage != $destImage) {
            @unlink($srcImage);
        }
    } else {
        COM_errorLog("MG_convertImageFormat: NetPBM returned an error converting image.");
        return array(false,'NetPBM returned an error when converting image.');
    }
    return array(true,'');
}

function _mg_watermarkImage($origImage, $watermarkImage, $opacity, $location, $mimeType ) {
    global $_MG_CONF;

    if ( $_MG_CONF['verbose'] ) {
        COM_errorLog("MG_watermarkImage: Using NetPBM to watermark image.");
    }
    $errors = 0;
    $newSrc  = $origImage . '.tmp';
    $IntFile = $origImage . '.int';
    $origImagePNM = $origImage . '.pnm';
    $overlay = $watermarkImage . '.oly';
    $alpha   = $watermarkImage . '.alpa';
    $srcSize = @getimagesize($origImage);
    $overlaySize = @getimagesize($watermarkImage);

    $file_extension = strtolower(substr(strrchr($watermarkImage,"."),1));

    switch( $file_extension ) {
        case "png":
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/pngtopnm" . '" ' . $watermarkImage . ' > ' . $overlay);
            if ( $rc != true ) {
                COM_errorLog("MG_watermarkImage: Unable to apply watermark, error executing pngtopnm (NetPBM) rc = " . $rc);
                return array(false,'Error executing pngtopnm (NetPBM)');
            }
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/pngtopnm" . '" -alpha ' . $watermarkImage . ' > ' . $alpha);
            if ( $rc != 1 ) {
                COM_errorLog("MG_watermarkImage: Unable to apply watermark, error executing pngtopnm (alpha mask) (NetPBM)");
                return array(false,'Error executing pngtopnm (alpha mask) (NetPBM)');
            }
            break;
        case "jpg":
            $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/jpegtopnm" . '" ' . $watermarkImage . '" > ' . $overlay);
            if ( $rc != 1 ) {
                COM_errorLog("MG_watermarkImage: Unable to apply watermark, error executing jpegtopnm (NetPBM)");
                return array(false,'Error executing jpegtopnm');
            }
            break;
        default :
            COM_errorLog("MG_watermarkImage: Unable to apply watermark, unrecognized filetype for watermark image (NetPBM)");
            return array(false,'Unrecognized file type for watermark image');
    }
    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            $toPNM = '/jpegtopnm';
            $fromPNM = '/pnmtojpeg';
            break;
        case 'image/png' :
            $toPNM = '/pngtopnm';
            $fromPNM = '/pnmtopng';
            break;
        case 'image/gif' :
            $toPNM = '/giftopnm';
            $fromPNM = '/ppmtogif';
            break;
        case 'image/bmp' :
            $toPNM = '/bmptopnm';
            $fromPNM = '/ppmtobmp';
            break;
        case 'image/x-targa' :
        case 'image/tga' :
            COM_errorLog("MG_watermark: TGA files not supported by NetPBM");
            return array(false,'TGA files not supported by NetPBM');
        default :
            COM_errorLog("MG_watermark: NetPBM only support JPG, PNG,GIF, and BMP image types.");
            return array(false,'NetPBM only supports JPG, PNG, GIF, and BMP image formats');
    }
    $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . $toPNM . '" ' . $origImage . ' > ' . $origImagePNM);
    if ( $rc != 1 ) {
        COM_errorLog("MG_watermarkImage: Unable to apply watermark, error creating pnm image (NetPBM)");
        return array(false,'Error creating pnm image (NetPBM)');
    }

    switch ($location) {
        case 'topleft': // Top - Left
            $align = 'left';
            $valign = 'top';
            $wmAlignX = 0;
            $wmAlignY = 0;
            break;
        case 'topcenter' : // Top - centered...
            $align = 'center';
            $valign = 'top';
            $wmAlignX = ($srcSize[0] - $overlaySize[0]) / 2;
            $wmAlignY = 0;
            break;
        case 'topright': // Top - Right
            $align = 'right';
            $valign = 'center';
            $wmAlignX = ($srcSize[0] - $overlaySize[0]);
            $wmAlignY = 0;
            break;
        case 'leftmiddle': // Left
            $align = 'left';
            $valign = 'middle';
            $wmAlignX = 0;
            $wmAlignY = ($srcSize[1] - $overlaySize[1]) / 2;
            break;
        case 'center': // Center
            $align = 'center';
            $valign='middle';
            $wmAlignX = ($srcSize[0] - $overlaySize[0]) / 2;
            $wmAlignY = ($srcSize[1] - $overlaySize[1]) / 2;
            break;
        case 'rightmiddle': // Right
            $align = 'right';
            $valign = 'middle';
            $wmAlignX = ($srcSize[0] - $overlaySize[0]);
            $wmAlignY = ($srcSize[1] - $overlaySize[1]) / 2;
            break;
        case 'bottomleft': // Bottom - Left
            $align='left';
            $valign='bottom';
            $wmAlignX = 0;
            $wmAlignY = ($srcSize[1] - $overlaySize[1]);
            break;
        case 'bottomcenter': // Bottom
            $align='center';
            $valign='bottom';
            $wmAlignX = ($srcSize[0] - $overlaySize[0]) / 2;
            $wmAlignY = 0; // ($srcSize[1] - $overlaySize[1]);
            break;
        case 'bottomright': // Bottom Right
            $align='right';
            $valign='bottom';
            $wmAlignX = ($srcSize[0] - $overlaySize[0]);
            $wmAlignY = ($srcSize[1] - $overlaySize[1]);
            break;
        default:
            COM_errorLog("MG_watermarkImage: Unknown watermark location: " . $location);
            return array(false,'Invalid watermark position');
            break;
    }
    $args = "-align=" . $align . " -valign=" . $valign . " ";
    $args .= "-opacity=" . $opacity . ' ';
    if ($alpha) {
        $args .= "-alpha=$alpha ";
    }
    $args .= $overlay;
    $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . "/pnmcomp" . '" '  . $args . ' ' . $origImagePNM . " > " . $IntFile);
    if ( $rc != 1 ) {
        COM_errorLog("MG_watermarkImage: Unable to apply watermark, error executing pamcomp (NetPBM)");
        return array(false,'Error executing pamcomp (NetPBM)');
    }
    $rc = MG_execWrapper('"' . $_MG_CONF['graphicspackage_path'] . $fromPNM . '"  ' . $IntFile . ' > ' . $newSrc);
    if ( $rc != 1 ) {
        COM_errorLog("MG_watermarkImage: Unable to apply watermark, error executing " . $fromPNM . " (NetPBM)");
        return array(false,'Error executing ' . $fromPNM . ' (NetPBM)');
    }

    if ( $_MG_CONF['jhead_enabled'] == 1 && ($mimeType == 'image/jpeg' || $mimeType == 'image/jpg') ) {
        $rc = MG_execWrapper('"' . $_MG_CONF['jhead_path'] . "/jhead" . '"' . " -te " . $origImage . " " . $newSrc);
    }
    @unlink($origImage);
    $rc = copy($newSrc, $origImage);
    @unlink($newSrc);
    @unlink($alpha);
    @unlink($overlay);
    @unlink($origImagePNM);
    @unlink($IntFile);
    COM_errorLog("MG_watermarkImage: Watermark successfully applied (NetPBM)");
    return array(true,'');
}
?>