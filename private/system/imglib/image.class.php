<?php

if (strpos ($_SERVER['PHP_SELF'], 'image.class.php') !== false)
{
    die ('This file can not be used on its own.');
}

class image {
    var $path;
    var $height;
    var $width;
    var $filesize;
    var $mimetype;
    var $_debugMessages = array();        // Array
    var $_debug = false;                  // Boolean

    // The parameterized factory method
    public static function factory()
    {
        global $_CONF;

        switch ( $_CONF['image_lib'] ) {
            case 'imagemagick' :    // ImageMagick...
                require_once($_CONF['path'] . '/system/imglib/im_image.php');
                break;
            case 'netpbm' :    // NetPBM
                require_once($_CONF['path'] . '/system/imglib/pbm_image.php');
                break;
            case 'gdlib' :    // GD Library
                require_once($_CONF['path'] . '/system/imglib/gd_image.php');
                break;
            default:
                throw new Exception ('Driver not found');
        }
        $classname = 'something here';
        return new $classname;
    }

    /*
     * $mysql = image::factory();
     */


    function image($pathtoimage) {

        $metaData = $this->_getMediaMetaData($pathtoimage);
        $this->mimetype = $metaData['mime_type'];


    }

    function convertImageFormat {
        global $_CONF;

        $newSrc = $srcImage;

        if ($_CONF['debug_image_upload'] ) {
            COM_errorLog("IMG_convertImageFormat: Entering IMG_convertImageFormat()");
        }

        $metaData = array();

        $metaData = IMG_getMediaMetaData($srcImage);
        $mimeType = $metaData['mime_type'];

        $imgsize = @getimagesize($srcImage);
        if ( $imgsize == false &&
             $mimeType != 'image/x-targa' &&
             $mimeType != 'image/tga' &&
             $mimeType != 'image/photoshop' &&
             $mimeType != 'image/x-photoshop' &&
             $mimeType != 'image/psd' &&
             $mimeType != 'application/photoshop' &&
             $mimeType != 'application/psd' &&
             $mimeType != 'image/tiff' ) {
            COM_errorLog("IMG_convertImageFormat: Error - unable to retrieve srcImage resolution");
            return array(false,'Unable to determine source image resolution');
        }

        $imgwidth = $imgsize[0];
        $imgheight = $imgsize[1];

        if ($imgsize == false || $imgwidth == 0 || $imgheight == 0 ) {
            $imgwidth = 620;
            $imgheight = 465;
        }
        if ( $mimeType == $destFormat ) {
            if ( $srcImage != $destImage ) {
                $rc = copy($srcImage, $destImage);
            }
            return array(true,'Original image copied to destination image.');
        }

        list($rc,$msg) = _img_convertImageFormat($srcImage,$destImage,$destFormat, $mimeType);

        return array($rc,$msg);
    }