<?php
/**
* glFusion CMS
*
* glFusion File Upload Class
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2009 by the following authors:
*   Authors: Tony Bibbs  tony AT tonybibbs DOT com
*            Dirk Haun   dirk AT haun-online DOT de
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;
use \glFusion\FileSystem;

/**
* This class will allow you to securely upload one or more files from a form
* submitted via POST method.  Please read documentation as there are a number of
* security related features that will come in handy for you.
*
* @author       Tony Bibbs, tony AT tonybibbs DOT com
*
*/
class upload
{
    /**
    * @access private
    */
    var $_errors = array();               // Array
    /**
    * @access private
    */
    var $_warnings = array();             // Array
    /**
    * @access private
    */
    var $_debugMessages = array();        // Array
    /**
    * @access private
    */
    var $_allowedMimeTypes = array();     // Array
    /**
    * @access private
    */
    var $_fieldName = '';
    /**
    * @access private
    */
    var $_availableMimeTypes = array();   // Array
    /**
    * @access private
    */
    var $_filesToUpload = array();        // Array
    /**
    * @access private
    */
    var $_currentFile = array();          // Array
    /**
    * @access private
    */
    var $_allowedIPS = array();           // Array
    /**
    * @access private
    */
    var $_uploadedFiles = array();        // Array
    /**
    * @access private
    */
    var $_maxImageWidth = 300;            // Pixels
    /**
    * @access private
    */
    var $_maxImageHeight = 300;           // Pixels
    /**
    * @access private
    */
    var $_maxFileSize = 1048576;          // Long, in bytes
    /**
    * @access private
    */
    var $_jpegQuality = 0;                // compatibility only - not used
    /**
    * @access private
    */
    var $_pathToMogrify = '';             // String
    /**
    * @access private
    */
    var $_pathToNetPBM= '';               // String
    /**
    * @access private
    */
    var $_imageLib = '';                 // Integer
    /**
    * @access private
    */
    var $_autoResize = false;             // boolean
    /**
    * @access private
    */
    var $_allowAnyType = false;           // boolean
    /**
    * @access private
    */
    var $_keepOriginalImage = false;      // boolean
    /**
    * @access private
    */
    var $_maxFileUploadsPerForm = 5;
    /**
    * @access private
    */
    var $_fileUploadDirectory = '';       // String
    /**
    * @access private
    */
    var $_fileNames = '';                 // String
    /**
    * @access private
    */
    var $_permissions = '';               // String
    /**
    * @access private
    */
    var $_logFile = '';                   // String
    /**
    * @access private
    */
    var $_doLogging = false;              // Boolean
    /**
    * @access private
    */
    var $_continueOnError = false;        // Boolean
    /**
    * @access private
    */
    var $_debug = false;                  // Boolean
    /**
    * @access private
    */
    var $_limitByIP = false;              // Boolean
    /**
    * @access private
    */
    var $_numSuccessfulUploads = 0;       // Integer
    /**
    * @access private
    */
    var $_imageIndex = 0;                 // Integer

    /**
    * @access private
    */
    var $_wasResized = false;             // Boolean


    /**
    * Constructor
    *
    */
    function __construct()
    {
        $this->_setAvailableMimeTypes();
    }

    // PRIVATE METHODS

    /**
    * Adds a warning that was encountered
    *
    * @access   private
    * @param    string  $warningText     Text of warning
    *
    */
    function _addWarning($warningText)
    {
        $nwarnings = count($this->_warnings);
        $nwarnings = $nwarnings + 1;
        $this->_warnings[$nwarnings] = $warningText;
        if ($this->loggingEnabled()) {
            Log::write('system',Log::WARNING,$warningText);
        }
    }

    /**
    * Adds an error that was encountered
    *
    * @access   private
    * @param    string      $errorText      Text of error
    *
    */
    function _addError($errorText)
    {
        $nerrors = count($this->_errors);
        $nerrors = $nerrors + 1;
        $this->_errors[$nerrors] = $errorText;
        if ($this->loggingEnabled()) {
            Log::write('system',Log::ERROR,$errorText);
        }
    }

    /**
    * Adds a debug message
    *
    * @access   private
    * @param        string      $debugText      Text of debug message
    *
    */
    function _addDebugMsg($debugText)
    {
        $nmsgs = count($this->_debugMessages);
        $nmsgs = $nmsgs + 1;
        $this->_debugMessages[$nmsgs] = $debugText;
        if ($this->loggingEnabled()) {
            Log::write('system',Log::DEBUG,$debugText);
        }
    }


    /**
    * Adds PHP upload error
    *
    * @access   private
    * @param    int       $error      PHP returned error code
    *
    */
    function _uploadError($error)
    {
        switch ( $error ) {
            case UPLOAD_ERR_INI_SIZE :
                $this->_addError('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
                break;
            case UPLOAD_ERR_FORM_SIZE :
                $this->_addError('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
                break;
            case UPLOAD_ERR_PARTIAL :
                $this->_addError('The uploaded file was only partially uploaded.');
                break;
            case UPLOAD_ERR_NO_FILE :
                $this->_addError('No file was uploaded.');
                break;
            case UPLOAD_ERR_NO_TMP_DIR :
                $this->_addError('Missing a temporary folder.');
                break;
            case UPLOAD_ERR_CANT_WRITE :
                $this->_addError('Failed to write file to disk.');
                break;
            default :
                $this->_addError('Unknown error uploading file.');
                break;
        }
    }

    /**
    * Logs an item to the log file
    *
    * @access   private
    * @param    string      $logtype    can be 'warning' or 'error'
    * @param    string      $text       Text to log to log file
    * @return   boolean     Whether or not we successfully logged an item
    *
    */
    function _logItem($logtype, $text)
    {
        switch(strtolower($logtype)) {
            case 'warning' :
                Log::write('system',Log::WARNING,$text);
                break;
            case 'error' :
                Log::write('system',Log::ERROR,$text);
                break;
        }
       return true;
    }

    /**
    * Defines superset of available Mime types.
    *
    * @access   private
    * @param    array   $mimeTypes  string array of valid mime types this object will accept
    *
    */
    function _setAvailableMimeTypes($mimeTypes = array())
    {
        if (sizeof($mimeTypes) == 0) {
            $this->_availableMimeTypes =
            array(
                'application/x-gzip-compressed'     => '.tar.gz,.tgz,.gz',
                'application/x-zip-compressed'      => '.zip',
                'application/x-tar'                 => '.tar,.tar.gz,.gz',
                'application/x-gtar'                => '.tar',
                'text/plain'                        => '.phps,.txt,.inc',
                'text/html'                         => '.html,.htm',
                'image/bmp'                         => '.bmp,.ico',
                'image/gif'                         => '.gif',
                'image/pjpeg'                       => '.jpg,.jpeg',
                'image/jpeg'                        => '.jpg,.jpeg',
                'image/png'                         => '.png',
                'image/x-png'                       => '.png',
                'audio/mpeg'                        => '.mp3',
                'audio/wav'                         => '.wav',
                'application/pdf'                   => '.pdf',
                'application/x-shockwave-flash'     => '.swf',
                'application/msword'                => '.doc',
                'application/vnd.ms-excel'          => '.xls',
                'application/octet-stream'          => '.fla,.psd'
            );
        } else {
            $this->_availableMimeTypes = $mimeTypes;
        }
    }

    /**
    * Checks if current file is an image
    *
    * @access private
    * @return boolean   returns true if file is an image, otherwise false
    */
    function _isImage()
    {
        if (strpos ($this->_currentFile['type'], 'image/') === 0) {
            $isImage = true;
        } else {
            $isImage = false;
        }
        if ($this->_debug) {
            $msg = 'File, ' . $this->_currentFile['name'] . ' is of mime type '
                . $this->_currentFile['type'];
            if (!$isImage) {
                $msg .= ' and is NOT an image file.';
            } else {
                $msg .= ' and IS an image file.';
            }
            $this->_addDebugMsg($msg);
        }

        return $isImage;
    }

    /**
    * Verifies the file size meets specified size limitations
    *
    * @access private
    * @return boolean   returns true of file size is within our limits otherwise false
    */
    function _fileSizeOk()
    {
        if ($this->_debug) {
            $this->_addDebugMsg('File size for ' . $this->_currentFile['name'] . ' is ' . $this->_currentFile['size'] . ' bytes');
        }

        if ($this->_currentFile['size'] > $this->_maxFileSize) {
            Log::write('system',Log::WARNING,"Uploaded file: ".$this->_currentFile['name']." exceeds max file size of " . $this->_maxFileSize);
            return false;
        } else {
            return true;
        }
    }

    /**
    * Checks to see if file is an image and, if so, whether or not
    * it meets width and height limitations
    *
    * @access   private
    * @return   boolean     returns true if image height/width meet our limitations otherwise false
    *
    */
    function _imageSizeOK($doResizeCheck=true)
    {
        if (!$this->_isImage()) {
            return true;
        }

        $imageInfo = $this->_getImageDimensions($this->_currentFile['tmp_name']);

        $sizeOK = true;

        if ($this->_debug) {
            $this->_addDebugMsg('Max allowed width = ' . $this->_maxImageWidth . ', Image width = ' . $imageInfo['width']);
            $this->_addDebugMsg('Max allowed height = ' . $this->_maxImageHeight . ', Image height = ' . $imageInfo['height']);
        }

        if ( $this->_maxImageWidth == 0 && $this->_maxImageHeight == 0 ) {
            return $sizeOK;
        }

        // If user set _autoResize then ignore these settings and try to resize on upload
        if (($doResizeCheck AND !($this->_autoResize)) OR (!($doResizeCheck))) {
            if ($imageInfo['width'] > $this->_maxImageWidth) {
                $sizeOK = false;
                if ($doResizeCheck) {
                    $this->_addError('Image, ' . $this->_currentFile['name'] . ' does not meet width limitations (is: ' . $imageInfo['width'] . ', max: ' . $this->_maxImageWidth . ')');
                }
            }

            if ($imageInfo['height'] > $this->_maxImageHeight) {
                $sizeOK = false;
                if ($doResizeCheck) {
                    $this->_addError('Image, ' . $this->_currentFile['name'] . ' does not meet height limitations (is: ' . $imageInfo['height'] . ', max: ' . $this->_maxImageHeight . ')');
                }
            }
        }

        if ($this->_debug) {
            $this->_addDebugMsg('File, ' . $this->_currentFile['name'] . ' has a width of '
                . $imageInfo['width'] . ' and a height of ' . $imageInfo['height']);
        }

        return $sizeOK;
    }

    /**
    * Gets the width and height of an image
    *
    * @access private
    * @return array     Array with width and height of current image
    */
    function _getImageDimensions()
    {
        $dimensions = @getimagesize($this->_currentFile['tmp_name']);
        if ($this->_debug) {
            $this->_addDebugMsg('in _getImageDimensions I got a width of ' . $dimensions[0] . ', and a height of ' . $dimensions[1]);
        }
        return array('width' => $dimensions[0], 'height' => $dimensions[1]);
    }

    /**
    * Calculate the factor to scale images with if they're not meeting
    * the size restrictions.
    *
    * @access   private
    * @param    int     $width      width of the unscaled image
    * @param    int     $height     height of the unscaled image
    * @return   double              resize factor
    *
    */
    function _calcSizefactor ($width, $height) // 1000
    {
        if (($width > $this->_maxImageWidth) ||
                ($height > $this->_maxImageHeight)) {
            // get both sizefactors that would resize one dimension correctly
            $sizefactor_w = (double) ($this->_maxImageWidth / $width);
            $sizefactor_h = (double) ($this->_maxImageHeight / $height);
            // check if the height is ok after resizing the width
            if ( ($height * $sizefactor_w) > ($this->_maxImageHeight) ){
                // if no, get new sizefactor from height instead
                $sizefactor = $sizefactor_h;
            } else {
                // otherwise the width factor it ok to fit max dimensions
                $sizefactor = $sizefactor_w;
            }
        } else {
            $sizefactor = 1.0;
        }

        return $sizefactor;
    }

    /**
    * Keep the original (unscaled) image file, if configured.
    *
    * @access   private
    * @param    string  $filename   name of uploaded file
    * @return   bool                true: okay, false: an error occured
    *
    */
    function _keepOriginalFile ($filename)
    {
        if ($this->_keepOriginalImage) {
            $lFilename_large = substr_replace ($this->_getDestinationName (),
                '_original.', strrpos ($this->_getDestinationName (), '.'), 1);
            $lFilename_large_complete = $this->_fileUploadDirectory . '/'
                                      .  $lFilename_large;
            if (!copy ($filename, $lFilename_large_complete)) {
                $this->_addError ("Couldn't copy $filename to $lFilename_large_complete.  You'll need to remove both files.");
                $this->printErrors ();

                return false;
            }
        }

        return true;
    }

    /**
    * Gets destination file name for current file
    *
    * @access private
    * @return string    returns destination file name
    *
    */
    function _getDestinationName()
    {
        if (is_array($this->_fileNames)) {
            $name = $this->_fileNames[$this->_imageIndex];
        }

        if (empty($name)) {
            $name = $this->_currentFile['name'];
        }

        return $name;
    }

    /**
    * Gets permissions for a file.  This is used to do a chmod
    *
    * @access   private
    * @return   string  returns final permisisons for current file
    *
    */
    function _getPermissions()
    {
        if (is_array($this->_permissions)) {
            if (count($this->_permissions) > 1) {
                $perms = $this->_permissions[$this->_imageIndex];
            } else {
                $perms = $this->_permissions[0];
            }
        }

        if (empty($perms)) {
            $perms = '';
        }

        return $perms;
    }

    /**
    * This function actually completes the upload of a file
    *
    * @access   private
    * @return   boolean     true if copy succeeds otherwise false
    *
    */
    function _copyFile()
    {
        if (!is_writable($this->_fileUploadDirectory)) {
            // Developer didn't check return value of setPath() method which would
            // have told them the upload directory was not writable.  Error out now
            $this->_addError('Specified upload directory, ' . $this->_fileUploadDirectory . ' exists but is not writable');
            return false;
        }
        $sizeOK = true;
        if (!($this->_imageSizeOK(false)) AND $this->_autoResize) {
            $imageInfo = $this->_getImageDimensions($this->_currentFile['tmp_name']);
            if ($imageInfo['width'] > $this->_maxImageWidth) {
                $sizeOK = false;
            }

            if ($imageInfo['height'] > $this->_maxImageHeight) {
                $sizeOK = false;
            }
        }
        if (isset($this->_currentFile['_data_dir']) && $this->_currentFile['_data_dir']) {
            $returnMove = @copy($this->_currentFile['tmp_name'], $this->_fileUploadDirectory . '/' . $this->_getDestinationName());
            @unlink($this->_currentFile['tmp_name']);
        } else {
            $returnMove = move_uploaded_file($this->_currentFile['tmp_name'], $this->_fileUploadDirectory . '/' . $this->_getDestinationName());
        }
        if (!($sizeOK)) {
            // OK, resize
            $sizefactor = $this->_calcSizefactor ($imageInfo['width'],
                                                  $imageInfo['height']);
            $newwidth = (int) ($imageInfo['width'] * $sizefactor);
            $newheight = (int) ($imageInfo['height'] * $sizefactor);
            $newsize = $newwidth.'x'.$newheight;
            if (!$this->_keepOriginalFile ($this->_fileUploadDirectory . '/' . $this->_getDestinationName())) {
                exit;
            }
            list($retval,$msg) = IMG_resizeImage($this->_fileUploadDirectory . '/' . $this->_getDestinationName(), $this->_fileUploadDirectory . '/' . $this->_getDestinationName(), $newheight, $newwidth, $this->_currentFile['type'], 0 );
            if ($retval !== true) {
                $this->_addError('Image, ' . $this->_currentFile['name'] . ' ' . $msg);

                $this->printErrors();
                exit;
            } else {
                $this->_addDebugMsg ('Image, ' . $this->_currentFile['name'] . ' was resized from ' . $imageInfo['width'] . 'x' . $imageInfo['height'] . ' to ' . $newsize);
            }
        }
        $returnChmod = true;
        $perms = $this->_getPermissions();
        if (!empty($perms)) {
            $returnChmod = @chmod ($this->_fileUploadDirectory . '/' . $this->_getDestinationName (), octdec ($perms));
        }

        if ($returnMove AND $returnChmod) {
            return true;
        } else {
            if (!$returnMove) {
                $this->_addError('Upload of ' . $this->_currentFile['name'] . ' failed.');
            }

            if (!$returnChmod) {
                $this->_addError('Chmod of ' . $this->_currentFile['name'] . ' to ' . $perms . ' failed');
            }

            return false;
        }
    }

    /**
    * Sets $_FILES fieldname
    *
    * @param    string    $fieldname of $_FILES array
    *
    */
    function setFieldName($fieldname)
    {
        $this->_fieldName = $fieldname;
    }

    /**
    * Sets mode to allow any mime type
    *
    * @param    boolean    $switch  True to turn on, false to turn off
    *
    */
    function setAllowAnyMimeType($switch)
    {
        $this->_allowAnyType = $switch;
    }

    /**
    * Sets mode to automatically resize images that are either too wide or
    * too tall
    *
    * @param    boolean    $switch  True to turn on, false to turn off
    *
    */
    function setAutomaticResize($switch)
    {
        $this->_autoResize = $switch;
    }

    /**
    * Allows you to override default max file size
    *
    * @param    int     $size_in_bytes      Max. size for uploaded files
    * @return   boolean true if we set it OK, otherwise false
    *
    */
    function setMaxFileSize($size_in_bytes)
    {
        if (!is_numeric($size_in_bytes)) {
            return false;
        }
        $this->_maxFileSize = $size_in_bytes;
        return true;
    }

    /**
    * Allows you to override default max. image dimensions
    *
    * @param    int    $width_pixels    Max. width allowed
    * @param    int    $height_pixels   Max. height allowed
    * @return   boolean true if we set values OK, otherwise false
    *
    */
    function setMaxDimensions($width_pixels, $height_pixels)
    {
        if (!is_numeric($width_pixels) AND !is_numeric($height_pixels)) {
            return false;
        }

        $this->_maxImageWidth = $width_pixels;
        $this->_maxImageHeight = $height_pixels;

        return true;
    }

    /**
    * Sets the max number of files that can be uploaded per form
    *
    * @param     int       $maxfiles    Maximum number of files to allow. Default is 5
    * @return    boolean   True if set, false otherwise
    *
    */
    function setMaxFileUploads($maxfiles)
    {
        $this->_maxFileUploadsPerForm = $maxfiles;
        return true;
    }

    /**
    * Allows you to keep the original (unscaled) image.
    *
    * @param    boolean   $keepit   true = keep original, false = don't
    * @return   boolean   true if we set values OK, otherwise false
    *
    */
    function keepOriginalImage ($keepit)
    {
        $this->_keepOriginalImage = $keepit;

        return true;
    }

    /**
    * Extra security option that forces all attempts to upload a file to be done
    * so from a set of VERY specific IP's.  This is only good for those who are
    * paranoid
    *
    * @param    array   $validIPS   Array of valid IP addresses to allow file uploads from
    * @return   boolean returns true if we successfully limited the IP's, otherwise false
    */
    function limitByIP($validIPS = array('127.0.0.1'))
    {
        if (is_array($validIPS)) {
            $this->_limitByIP = true;
            $this->_allowedIPS = $validIPS;
            return true;
        } else {
            $this->_addError('Bad call to method limitByIP(), must pass array of valid IP addresses');
            return false;
        }
    }

    /**
    * Allows you to specify whether or not to continue processing other files
    * when an error occurs or exit immediately. Default is to exit immediately
    *
    * NOTE: this only affects the actual file upload process.
    *
    * @param    boolean     $switch     true or false
    *
    */
    function setContinueOnError($switch)
    {
        if ($switch) {
            $this->_continueOnError = true;
        } else {
            $this->_continueOnError = false;
        }
    }

    /**
    * Sets log file
    *
    * @param    string  $fileName   fully qualified path to log files
    * @return   boolean returns true if we set the log file, otherwise false
    *
    */
    function setLogFile($logFile = '')
    {
        if (empty($logFile) OR !file_exists($logFile)) {
            // Log file doesn't exist, produce warning
            $this->_addWarning('Log file, ' . $logFile . ' does not exists, setLogFile() method failed');
            $this->_doLogging = false;
            return false;
        }
        $this->_logFile = $logFile;
        return true;
    }

    /**
    * Enables/disables logging of errors and warnings
    *
    * @param    boolean     $switch     flag, true or false
    *
    */
    function setLogging($switch)
    {
        if ($switch AND !empty($this->_logFile)) {
            $this->_doLogging = true;
        } else {
            if ($switch AND empty($this->_logFile)) {
                $this->_addWarning('Unable to enable logging because no log file was set.  Use setLogFile() method');
            }
            $this->_doLogging = false;
        }
    }

    /**
    * Returns whether or not logging is enabled
    *
    * @return   boolean returns true if logging is enabled otherwise false
    *
    */
    function loggingEnabled()
    {
        return $this->_doLogging;
    }

    /**
    * Will force the debug messages in this class to be
    * printed
    *
    * @param    boolean     $switch     flag, true or false
    *
    */
    function setDebug($switch)
    {
        if ($switch) {
            $this->_debug = true;
            // setting debugs implies logging is on too
            $this->setLogging(true);
        } else {
            $this->_debug = false;
        }
    }

    /**
    * This function will print any errors out.  This is useful in debugging
    *
    * @param    boolean     $verbose    whether or not to print immediately or return only a string
    * @return   string  if $verbose is false it returns all errors otherwise just an empty string
    *
    */
    function printErrors($verbose=true)
    {
        if (isset($this->_errors) AND is_array($this->_errors)) {
            $retval = '';
            reset($this->_errors);
            $nerrors = count($this->_errors);
            for ($i = 1; $i <= $nerrors; $i++) {
                if ($verbose) {
                    print current($this->_errors) . "<br" . XHTML . ">\n";
                } else {
                    $retval .= current($this->_errors) . "<br" . XHTML . ">\n";
                }
                next($this->_errors);
            }
            return $retval;
        }
    }

    /**
    * This function will print any warnings out.  This is useful in debugging
    *
    */
    function printWarnings()
    {
        if (isset($this->_warnings) AND is_array($this->_warnings)) {
            reset($this->_warnings);
            $nwarnings = count($this->_warnings);
            for ($i = 1; $i <= $nwarnings; $i++) {
                print current($this->_warnings) . "<br" . XHTML . ">\n";
                next($this->_warnings);
            }
        }
    }

    /**
    * This function will print any debug messages out.
    *
    */
    function printDebugMsgs()
    {
        if (isset($this->_debugMessages) AND is_array($this->_debugMessages)) {
            reset($this->_debugMessages);
            $nmsgs = count($this->_debugMessages);
            for ($i = 1; $i <= $nmsgs; $i++) {
                print current($this->_debugMessages) . "<br" . XHTML . ">\n";
                next($this->_debugMessages);
            }
        }
    }

    /**
    * Returns if any errors have been encountered thus far
    *
    * @return   boolean returns true if there were errors otherwise false
    *
    */
    function areErrors()
    {
        if (count($this->_errors) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Sets allowed mime types for this instance
    *
    * @param    array   allowedMimeTypes        Array of allowed mime types
    *
    */
    function setAllowedMimeTypes($mimeTypes = array())
    {
        $this->_allowedMimeTypes = $mimeTypes;
    }

    /**
    * Gets allowed mime types for this instance
    *
    * @return   array   Returns array of allowed mime types
    *
    */
    function getAllowedMimeTypes()
    {
        return $this->_allowedMimeTypes;
    }

    /**
    * Checks to see that mime type for current file is allowed for upload
    *
    * @return   boolean     true if current file's mime type is allowed otherwise false
    *
    */
    function checkMimeType()
    {
        if ( $this->_allowAnyType == true ) {
            return true;
        }
        $metaData = IMG_getMediaMetaData( $this->_currentFile['tmp_name'] );

        if ( !isset($metaData['mime_type']) ) {
            $this->_addError('Unable to determine mime type for ' . $this->_currentFile['name']);
            return false;
        }

        if ( $metaData['mime_type'] != '' ) {
            $this->_currentFile['type'] = $metaData['mime_type'];
        } else {
            $this->_currentFile['type'] = 'application/octet-stream';
        }
        $sc = strpos ($this->_currentFile['type'], ';');
        if ($sc > 0) {
            $this->_currentFile['type'] = substr ($this->_currentFile['type'], 0, $sc);
        }
        $mimeTypes = $this->getAllowedMimeTypes ();
        foreach ($mimeTypes as $mimeT => $extList) {
            if ($mimeT == $this->_currentFile['type']) {
                $extensions = explode (',', $extList);
                $fileName = $this->_currentFile['name'];
                foreach ($extensions as $ext) {
                    $ext = trim($ext);
                    if (strcasecmp (substr ($fileName, -strlen ($ext)), $ext) == 0) {
                        return true;
                    }
                }
            }
        }
        $this->_addError ('Mime type, ' . $this->_currentFile['type']
                          . ', or extension of ' . $this->_currentFile['name']
                          . ' not in list of allowed types.');
        return false;
    }

    /**
    * Sets file upload path
    *
    * @param    string  $uploadDir  Directory on server to store uploaded files
    * @return   boolean returns true if we successfully set path otherwise false
    *
    */
    function setPath($uploadDir)
    {
        if (!is_dir($uploadDir)) {
            if (FileSystem::mkDir($uploadDir) === false) {
                $this->_addError('Specified upload directory, ' . $uploadDir . ' is not a valid directory');
                return false;
            }
        }

        if (!is_writable($uploadDir)) {
            $this->_addError('Specified upload directory, ' . $uploadDir . ' exists but is not writable');
            return false;
        }

        $this->_fileUploadDirectory = $uploadDir;

        return true;
    }

    /**
    * Returns directory to upload to
    *
    * @return   string  returns path to file upload directory
    *
    */
    function getPath()
    {
        return $this->_fileUploadDirectory;
    }

    /**
    * Sets file name(s) for files
    *
    * This function will set the name of any files uploaded.  If the
    * number of file names sent doesn't match the number of uploaded
    * files a warning will be generated but processing will continue
    *
    * @param    string|array    $fileNames      A string or string array of file names
    *
    */
    function setFileNames($fileNames = 'glfusion_uploadedfile')
    {
        if (isset($fileNames) AND is_array($fileNames)) {
            // this is an array of file names, set them
            $this->_fileNames = $fileNames;
        } else {
            $this->_fileNames = array($fileNames);
        }
    }

    /**
    * Changes permissions for uploaded files.  If only one set of perms is
    * sent then they are applied to all uploaded files.  If more then one set
    * of perms is sent (i.e. $perms is an array) then permissions are applied
    * one by one.  Any files not having an associated permissions will be
    * left alone.  NOTE: this is meant to be called BEFORE you do the upload
    * and ideally is called right after setFileNames()
    *
    * @param    string|array    $perms      A string or string array of file permissions
    *
    */
    function setPerms($perms)
    {
        if (isset($perms) AND is_array($perms)) {
            // this is an array of file names, set them
            $this->_permissions = $perms;
        } else {
            $this->_permissions = array($perms);
        }
    }

    /**
    * Returns how many actual files were sent for upload.  NOTE: this will
    * ignore HTML file fields that were left blank.
    *
    * @return   int returns number of files were sent to be uploaded
    *
    */
    function numFiles()
    {
        if (empty($this->_filesToUpload)) {
            if ( empty($this->_fieldName) ) {
                $this->_filesToUpload = $_FILES;
            } else {
                $this->_filesToUpload = $_FILES[$this->_fieldName];
            }
        }

        $fcount = 0;

        if ( is_array($this->_filesToUpload['name']) ) {
            $fcount = 0;
            for ($i = 0; $i <= count($this->_filesToUpload['name']); $i++) {
                $curFile = current($this->_filesToUpload['name']);
                // Make sure file field on HTML form wasn't empty
                if (!empty($curFile)) {
                    $fcount++;
                }
                next($this->_filesToUpload['name']);
            }
            reset($this->_filesToUpload['name']);
        } else {
            if ( !empty($this->_filesToUpload['name']) ) {
                $fcount = 1;
            }
        }

        return $fcount;
    }

    /**
    * Uploads any posted files.
    *
    * @return   boolean returns true if no errors were encountered otherwise false
    */
    function uploadFiles()
    {
        // Before we do anything, let's see if we are limiting file uploads by
        // IP address and, if so, verify the poster is originating from one of
        // those places
        if ($this->_limitByIP) {
            if (!in_array($_SERVER['REAL_ADDR'], $this->_allowedIPS)) {
                $this->_addError('The IP, ' . $_SERVER['REAL_ADDR'] . ' is not in the list of '
                    . 'accepted IP addresses.  Refusing to allow file upload(s)');
                return false;
            }
        }

        if (empty($this->_filesToUpload)) {
            if ( empty($this->_fieldName) || $this->_fieldName == '' ) {
                $this->_filesToUpload = $_FILES;
            } else {
                $this->_filesToUpload = $_FILES[$this->_fieldName];
            }
        }
        $numFiles = $this->numFiles();

        // For security sake, check to make sure a DOS isn't happening by making
        // sure there is a limit of the number of files being uploaded
        if ($numFiles > $this->_maxFileUploadsPerForm) {
            $this->_addError('Max. number of files you can upload from a form is '
                . $this->_maxFileUploadsPerForm . ' and you sent ' . $numFiles);
            return false;
        }

        // Verify upload directory is valid
        if (!$this->_fileUploadDirectory) {
            $this->_addError('No Upload Directory Specified, use setPath() method');
        }

        // Verify allowed mime types exist
        if (!$this->_allowedMimeTypes && $this->_allowAnyType == false) {
            $this->_addError('No allowed mime types specified, use setAllowedMimeTypes() method');
        }

        if ( is_array($this->_filesToUpload['name']) ) {
            foreach ($this->_filesToUpload["error"] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $this->_currentFile['name'] = $this->_filesToUpload["name"][$key];
                    $this->_currentFile['tmp_name'] = $this->_filesToUpload["tmp_name"][$key];
                    $this->_currentFile['type'] = $this->_filesToUpload["type"][$key];
                    $this->_currentFile['size'] = $this->_filesToUpload["size"][$key];
                    $this->_currentFile['error'] = $this->_filesToUpload["error"][$key];
                    $this->_currentFile['_data_dir'] = isset($this->_filesToUpload["_data_dir"][$key]) ? $this->_filesToUpload["_data_dir"][$key] : '';
                    $this->_currentFile['localerror'] = array();

                    $metaData = IMG_getMediaMetaData( $this->_currentFile['tmp_name'] );
                    if ( $metaData['mime_type'] != '' ) {
                        $this->_currentFile['type'] = $metaData['mime_type'];
                    } else {
                        $this->_currentFile['type'] = 'application/octet-stream';
                    }

                    if (!empty($this->_currentFile['name'])) {
                        // Verify file meets size limitations
                        if (!$this->_fileSizeOk()) {
//@TODO - Translate
                            $this->_addError('File, ' . $this->_currentFile['name'] . ', is larger than the ' . COM_numberFormat($this->_maxFileSize,0) . ' byte limit');
                            $this->_currentFile['localerror'][] = $this->_currentFile['name'] . ', is larger than the ' . COM_numberFormat($this->_maxFileSize,0) . ' byte limit';
                        }

                        // If all systems check, do the upload
                        if ($this->checkMimeType() AND $this->_imageSizeOK() AND empty($this->_currentFile['localerror'])) {
                            if ($this->_copyFile()) {
                                $this->_uploadedFiles[] = $this->_fileUploadDirectory . '/' . $this->_getDestinationName();
                            }
                        }

                        $this->_currentFile = array();

                        if ($this->areErrors() AND !$this->_continueOnError) {
                            return false;
                        }
                    }
                } else {
                    if ( $error != UPLOAD_ERR_NO_FILE ) {
                        $this->_uploadError($error);
                    }
                }
                $this->_imageIndex++;
            }
        } else {
            if ( $this->_filesToUpload['name'] != '' && $this->_filesToUpload['error'] == UPLOAD_ERR_OK ) {
                $this->_currentFile['name'] = $this->_filesToUpload["name"];
                $this->_currentFile['tmp_name'] = $this->_filesToUpload["tmp_name"];
                $this->_currentFile['type'] = $this->_filesToUpload["type"];
                $this->_currentFile['size'] = $this->_filesToUpload["size"];
                $this->_currentFile['error'] = $this->_filesToUpload["error"];
                $this->_currentFile['_data_dir'] = isset($this->_filesToUpload["_data_dir"]) ? $this->_filesToUpload["_data_dir"] : '' ;

                $metaData = IMG_getMediaMetaData( $this->_currentFile['tmp_name'] );
                if ( isset($metaData['mime_type']) && $metaData['mime_type'] != '' ) {
                    $this->_currentFile['type'] = $metaData['mime_type'];
                } else {
                    $this->_currentFile['type'] = 'application/octet-stream';
                }
                if (!empty($this->_currentFile['name'])) {
                    // Verify file meets size limitations

                    if (!$this->_fileSizeOk()) {
                        $this->_addError('File, ' . $this->_currentFile['name'] . ', is bigger than the ' . $this->_maxFileSize . ' byte limit');
                    }
// this is where we check the image size.
                    // If all systems check, do the upload
                    if ($this->checkMimeType() AND $this->_imageSizeOK() AND !$this->areErrors()) {
                        if ($this->_copyFile()) {
                            $this->_uploadedFiles[] = $this->_fileUploadDirectory . '/' . $this->_getDestinationName();
                        }
                    }
                    if ($this->areErrors() AND !$this->_continueOnError) {
                        return false;
                    }
                }
            } else {
                $this->_uploadError($this->_filesToUpload['error']);
            }

        }
        // This function returns false if any errors were encountered
        if ($this->areErrors()) {
            return false;
        } else {
            return true;
        }
    }

    // kept for comapatibility...

    /**
    * Sets the path to where the mogrify ImageMagick function is
    *
    * @param     string    $path_to_mogrify    Absolute path to mogrify
    * @return    boolean   True if set, false otherwise
    *
    */
    function setMogrifyPath($path_to_mogrify)
    {
        return true;
    }

    /**
    * Sets the path to where the netpbm utilities are
    *
    * @param     string    $path_to_netpbm    Absolute path to netpbm dir
    * @return    boolean   True if set, false otherwise
    *
    */
    function setNetPBM($path_to_netpbm)
    {
        return true;
    }

    /**
    * Configure upload to use GD library
    *
    * @return    boolean   True if set, false otherwise
    *
    */
    function setGDLib()
    {
        return true;
    }

    /**
    * If enabled will ignore the MIME checks on file uploads
    *
    * @param    boolean     $switch     flag, true or false
    *
    */
    function setIgnoreMimeCheck($switch)
    {
    }


    /**
    * Set JPEG quality
    *
    * NOTE:     The 'quality' is an arbitrary value used by the IJG library.
    *           It is not a percent value! The default (and a good value) is 75.
    *
    * @param    int       $quality  JPEG quality (0-100)
    * @return   boolean   true if we set values OK, otherwise false
    *
    */
    function setJpegQuality($quality)
    {
        return true;
    }
}

?>
