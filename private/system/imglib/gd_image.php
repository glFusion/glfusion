<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | gd-image.php                                                             |
// |                                                                          |
// | GD Lib v2 Graphic Library interface                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
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

function _img_resizeImage($srcImage, $destImage, $sImageHeight, $sImageWidth, $dImageHeight, $dImageWidth, $mimeType)
{
    global $_CONF;

    $JpegQuality = 85;

    if ( $_CONF['debug_image_upload'] ) {
        COM_errorLog("IMG_resizeImage: Resizing using GD2: Src = " . $srcImage . " mimetype = " . $mimeType);
    }
    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            $image = @imagecreatefromjpeg($srcImage);
            break;
        case 'image/png' :
            $image = @imagecreatefrompng($srcImage);
            break;
        case 'image/bmp' :
            $image = @imagecreatefromwbmp($srcImage);
            break;
        case 'image/gif' :
            $image = @imagecreatefromgif($srcImage);
            break;
        case 'image/x-targa' :
        case 'image/tga' :
            COM_errorLog("IMG_resizeImage: TGA files not supported by GD2 Libs");
            return array(false,'TGA format not supported by GD2 Libs');;
        default :
            COM_errorLog("IMG_resizeImage: GD2 only supports JPG, PNG, and GIF image types.");
            return array(false,'GD2 only supports JPG, PNG and GIF image types');
    }
    if ( !$image ) {
        COM_errorLog("IMG_resizeImage: GD Libs failed to create working image.");
        return array(false,'GD Libs failed to create working image.');
    }
    if ( ( $dImageHeight > $sImageHeight) && ($dImageWidth > $sImageWidth )) {
        $dImageWidth = $sImageWidth;
        $dImageHeight = $sImageHeight;
    }

    $newimage = imagecreatetruecolor( $dImageWidth, $dImageHeight);
    imagealphablending( $newimage, false );
    imagesavealpha( $newimage, true );

    imagecopyresampled( $newimage, $image,
                        0, 0,
                        0, 0,
                        $dImageWidth, $dImageHeight,
                        $sImageWidth, $sImageHeight );

    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            imagejpeg($newimage,$destImage,$JpegQuality);
            break;
        case 'image/png' :
            $pngQuality = ceil(intval(($JpegQuality / 100) + 8));
            imagepng($newimage,$destImage,$pngQuality);
            break;
        case 'image/bmp' :
            imagewbmp($newimage,$destImage);
            break;
        case 'image/gif' :
            imagegif($newimage,$destImage);
            break;
    }
    imagedestroy($newimage);
    return array(true,'');
}

function _img_squareThumbnail($srcImage, $destImage, $sImageHeight, $sImageWidth, $dSize, $mimeType)
{
    global $_CONF;

    $JpegQuality = 85;

    if ( $_CONF['debug_image_upload'] ) {
        COM_errorLog("IMG_squareThumbnail: Resizing using GD2: Src = " . $srcImage . " mimetype = " . $mimeType);
    }
    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            $image = @imagecreatefromjpeg($srcImage);
            break;
        case 'image/png' :
            $image = @imagecreatefrompng($srcImage);
            break;
        case 'image/bmp' :
            $image = @imagecreatefromwbmp($srcImage);
            break;
        case 'image/gif' :
            $image = @imagecreatefromgif($srcImage);
            break;
        case 'image/x-targa' :
        case 'image/tga' :
            COM_errorLog("IMG_squareThumbnail: TGA files not supported by GD2 Libs");
            return array(false,'TGA format not supported by GD2 Libs');;
        default :
            COM_errorLog("IMG_squareThumbnail: GD2 only supports JPG, PNG, and GIF image types.");
            return array(false,'GD2 only supports JPG, PNG and GIF image types');
    }
    if ( !$image ) {
        COM_errorLog("IMG_squareThumbnail: GD Libs failed to create working image.");
        return array(false,'GD Libs failed to create working image.');
    }
	$original_width = $sImageWidth;
	$original_height = $sImageHeight;

	if($original_width > $original_height){
		$new_height = $dSize;
		$new_width = $new_height*($original_width/$original_height);
	}

	if($original_height > $original_width){
		$new_width = $dSize;
		$new_height = $new_width*($original_height/$original_width);
	}

	if($original_height == $original_width){
		$new_width = $dSize;
		$new_height = $dSize;
	}

	$new_width = round($new_width);
	$new_height = round($new_height);

	$smaller_image = imagecreatetruecolor($new_width, $new_height);
    imagealphablending( $smaller_image, false );
    imagesavealpha( $smaller_image, true );

	$square_image = imagecreatetruecolor($dSize, $dSize);
    imagealphablending( $square_image, false );
    imagesavealpha( $square_image, true );

	imagecopyresampled($smaller_image, $image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

	if($new_width>$new_height){
		$difference = $new_width-$new_height;
		$half_difference =  round($difference/2);
		imagecopyresampled($square_image, $smaller_image, 0-$half_difference+1, 0, 0, 0, $dSize+$difference, $dSize, $new_width, $new_height);
	}

	if($new_height>$new_width){
		$difference = $new_height-$new_width;
		$half_difference =  round($difference/2);
		imagecopyresampled($square_image, $smaller_image, 0, 0-$half_difference+1, 0, 0, $dSize, $dSize+$difference, $new_width, $new_height);
	}

	if($new_height == $new_width){
		imagecopyresampled($square_image, $smaller_image, 0, 0, 0, 0, $dSize, $dSize, $new_width, $new_height);
	}

    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            imagejpeg($square_image,$destImage,$JpegQuality);
            break;
        case 'image/png' :
            $pngQuality = ceil(intval(($JpegQuality / 100) + 8));
            imagepng($square_image,$destImage,$pngQuality);
            break;
        case 'image/bmp' :
            imagewbmp($square_image,$destImage);
            break;
        case 'image/gif' :
            imagegif($square_image,$destImage);
            break;
    }
    imagedestroy($square_image);
    return array(true,'');
}

function _img_RotateImage($srcImage, $direction,$mimeType) {
    global $_CONF;

    switch( $direction ) {
        case 'right' :
            $GD_rotate = "270";
            break;
        case 'left' :
            $GD_rotate = "90";
            break;
        default :
            COM_errorLog("IMG_rotateImage: Invalid direction passed to rotate, must be left or right");
            return array(false,'Invalid direction passed to rotate, must be left or right');
    }

    $tmpImage = $srcImage . '.rt';

    list($rc,$msg) = _img_gdRotate($srcImage,$tmpImage,$GD_rotate,$mimeType);
    if ( $rc == true ) {
        if ( $_CONF['jhead_enabled'] == 1 && ($mimeType == 'image/jpeg' || $mimeType == 'image/jpg' )) {
            $rc = UTL_execWrapper('"' . $_CONF['path_to_jhead'] . "/jhead" . '"' . " -te " . $srcImage . " " . $tmpImage);
        }
        $rc = copy($tmpImage, $srcImage);
        @unlink($tmpImage);
    } else {
        return array(false,$msg);
    }
    return array(true,'');
}

function _img_gdRotate( $src, $dest, $angle, $mimeType ) {

    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            $curImg = @imagecreatefromjpeg("$src");
            break;
        case 'image/png' :
            $curImg = @imagecreatefrompng("$src");
            break;
        case 'image/bmp' :
            $curImg = @imagecreatefromwbmp("$src");
            break;
        case 'image/gif' :
            $curImg = @imagecreatefromgif("$src");
            break;
        case 'image/x-targa' :
        case 'image/tga' :
            COM_errorLog("IMG_gdRotate: TGA files not supported by GD Libs");
            return array(false,'TGA files not supported by GD Libs');;
        default :
            COM_errorLog("IMG_gdRotate: GD Libs only support JPG, PNG, BMP, and GIF image formats.");
            return array(false,'GD Libs only supports JPG, PNG, BMP and GIF image formats.');
    }

    if ($angle == 180) {
        $dst_img = imagerotate($curImg, $angle, 0);
    } else {
        $final_img = imagerotate($curImg,$angle,0);
    }

    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            imagejpeg($final_img,$dest,100);
            break;
        case 'image/png' :
            imagepng($final_img,$dest,9);
            break;
        case 'image/bmp' :
            imagewbmp($final_img,$dest);
            break;
        case 'image/gif' :
            imagegif($final_img,$dest);
            break;
    }
    imagedestroy($final_img);
    return array(true,'');
}

function _img_convertImageFormat($srcImage,$destImage,$destFormat,$mimeType) {
    global $_CONF;

    switch ( $mimeType ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            $image = imagecreatefromjpeg($srcImage);
            break;
        case 'image/png' :
            $image = imagecreatefrompng($srcImage);
            break;
        case 'image/bmp' :
            $image = imagecreatefromwbmp($srcImage);
            break;
        case 'image/gif' :
            $image = imagecreatefromgif($srcImage);
            break;
        case 'image/tga' :
        case 'image/x-targa' :
        case 'image/photoshop' :
        case 'image/x-photoshop' :
        case 'image/psd' :
        case 'image/tiff' :
        case 'application/photoshop' :
        case 'application/psd' :
            COM_errorLog("IMG_convertImageFormat: GD Libs only support JPG, PNG, BMP, and GIF source formats.");
            return array(false,'GD Libs only support JPG, PNG, BMP, and GIF source formats.');
    }
    if ( !$image ) {
        COM_errorLog("IMG_convertImageFormat: GD Libs returned an error reading source image: " . $srcImage);
        return array(false,'GD Libs returned an error reading source image.');
    }

    $imgsize = @getimagesize($srcImage);
    if ($imgsize == false ) {
        COM_errorLog("IMG_convertImageFormat: GD Libs unable to determine dimensions for source image: " . $srcImage);
        return array(false,'GD Libs returned an error retrieving the dimensions of source image.');
    }
    $imgwidth = $imgsize[0];
    $imgheight = $imgsize[1];

    $newimage = imagecreatetruecolor($imgwidth, $imgheight);
    imagealphablending( $newimage, false );
    imagesavealpha( $newimage, true );
    imagecopyresampled($newimage, $image, 0,0,0,0,  $imgwidth, $imgheight, $imgwidth, $imgheight);
    imagedestroy($image);

    if ($_CONF['verbose'] ) {
        COM_errorLog("IMG_convertImageFormat: Outputting new image " . $destImage . " Format: " . $destFormat);
    }

    switch ( $destFormat ) {
        case 'image/jpeg' :
        case 'image/jpg' :
            $rc = imagejpeg($newimage,$destImage,$_CONF['jpg_orig_quality']);
            break;
        case 'image/png' :
            $rc = imagepng($newimage,$destImage,9);
            break;
        case 'image/bmp' :
            $rc = imagewbmp($newimage,$destImage);
            break;
        case 'image/gif' :
            $rc = imagegif($newimage,$destImage);
            break;
    }
    imagedestroy($newimage);
    if ( $rc == true ) {
        if ( $srcImage != $destImage) {
            @unlink($srcImage);
        }
    } else {
        COM_errorLog("IMG_convertImageFormat: GD Libs returned an error during conversion.");
        return array(false,'GD Libs returned an error during conversion');
    }
    return array(true,'');
}

function _img_watermarkImage($origImage, $watermarkImage, $opacity, $location, $mimeType ) {
    global $_CONF;

    if ( $_CONF['debug_image_upload'] ) {
        COM_errorLog("IMG_watermarkImage: Using GD Libs to watermark image.");
    }
    $newSrcTmp  = $origImage . '.tmp';
    $file_extension = strtolower(substr(strrchr($watermarkImage,"."),1));

    switch( $file_extension ) {
        case "png":
            $watermark = @imagecreatefrompng($watermarkImage);
            $useCopy = 1;
            break;
        case "jpg":
            $watermark = @imagecreatefromjpeg($watermarkImage);
            $useCopy = 0;
            break;
        default :
            COM_errorLog("IMG_watermarkImage: Unable to apply watermark, unrecognized filetype for watermark image");
            return array(false,'Unrecognized filetype for watermark image');
    }
    $size     = @getimagesize($origImage);
    if ( $size != false ) {
        $watermark_width  = imagesx($watermark);
        $watermark_height = imagesy($watermark);

        switch ( $mimeType ) {
            case 'image/jpeg' :
            case 'image/jpg' :
                $image = @imagecreatefromjpeg($origImage);
                break;
            case 'image/png' :
                $image = @imagecreatefrompng($origImage);
                break;
            case 'image/gif' :
                $image = @imagecreatefromgif($origImage);
                break;
            case 'image/x-targa' :
            case 'image/tga' :
                COM_errorLog("IMG_watermarkImage: TGA files not supported by GD Libs");
                return array(false,'TGA files not supported by GD Libs');
            default :
                COM_errorLog("IMG_watermarkImage: GD Libs only support JPG, PNG, and GIF image types.");
                return array(false,'GD Libs only support JPG,PNG, and GIF image types');
        }
        if ( $image == false ) {
            return array(false,'Unable to create GD image handle');
        }
        switch( $location ) {
            case 'topleft' :
                $dest_x = 5;
                $dest_y = 5;
                break;
            case 'topcenter' :    // top center
                $dest_x = ($size[0] - $watermark_width) / 2;
                $dest_y = 5;
                break;
            case 'topright' :
                $dest_x = $size[0] - $watermark_width - 5;
                $dest_y = 5;
                break;
            case 'leftmiddle' :
                $dest_x = 5;
                $dest_y = ($size[1] - $watermark_height) /2;
                break;
            case 'center' :
                $dest_x = ($size[0] - $watermark_width) / 2;
                $dest_y = ($size[1] - $watermark_height) /2;
                break;
            case 'rightmiddle' :
                $dest_x = $size[0] - $watermark_width - 5;
                $dest_y = ($size[1] - $watermark_height) /2;
                break;
            case 'bottomleft' :
                $dest_x = 5;
                $dest_y = $size[1] - $watermark_height - 5;
                break;
            case 'bottomcenter' :    // bottom center
                $dest_x = ($size[0] - $watermark_width) / 2;
                $dest_y = $size[1] - $watermark_height - 5;
                break;
            case 'bottomright' :
                $dest_x = $size[0] - $watermark_width - 5;
                $dest_y = $size[1] - $watermark_height - 5;
                break;
            default:
                COM_errorLog("IMG_watermarkImage: Unknown watermark location: " . $location);
                return array(false,'Unknown watermark location');
                break;
        }
        if ( $useCopy ) {
            imagecopy($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);
        } else {
            imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $opacity);
        }

        imagejpeg($image,"$newSrcTmp",100);

        if ( $_CONF['jhead_enabled'] == 1 ) {
            $rc = UTL_execWrapper('"' . $_CONF['path_to_jhead'] . "/jhead" . '"' . " -te " . $origImage . " " . $newSrcTmp);
        }
        @unlink($origImage);
        $rc = copy($newSrcTmp, $origImage);
        @unlink($newSrcTmp);
        imagedestroy($image);
        imagedestroy($watermark);
        COM_errorLog("IMG_watermarkImage: Watermark successfully applied (GD libs)");
    } else {
        COM_errorLog("IMG_watermarkImage: Unable to determine src image filesize for watermarking (GD libs)");
        return array(false,'Unable to determine src image dimensions');
    }
    return array(true,'');
}
/*
 * future enhancement
 *
function image_fix_orientation(&$image, $filename) {
    if ( function_exists('exif_read_data')) {
        $image = imagerotate($image, array_values([0, 0, 0, 180, 0, 0, -90, 0, 90])[@exif_read_data($filename)['Orientation'] ?: 0], 0);
    }
}
*/
?>