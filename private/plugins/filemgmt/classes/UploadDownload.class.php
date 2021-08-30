<?php
/**
 * Class to handle file uploads and downloads for the Filemgmt plugin.
 * This is an adaptation of the original glFusion `upload` and `downloader`
 * classes. Common functions and data, such as allowed mime types, are thus
 * shared for consistency.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2019 Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2015-2016 Mark R. Evans <mark AT glfusion DOT org>
 * @copyright   Copyright (c) 2002-2009 Tony Bibbs <tony AT tonybibbs DOT com>
 *
 * @package     filemgmt
 * @version     v1.0.0
 * @since       v1.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Filemgmt;


/**
 * File upload and download class.
 * @package filemgmt
 */
class UploadDownload
{
    /** File storage directory.
     * @var string */
    private $_filePath = '';

    /** Array of error messages accumulated during processing.
     * @var array */
    private $_errors = array();

    /** Array of warning messages accumulated during procesing.
     * @var array */
    private $_warnings = array();

    /** Array of debug messages accumulated during processing.
     * @var array */
    private $_debugMessages = array();

    /** Array of all allowed mimetypes. Subset of `$_availableMimeTypes`.
     * @see self::$_availableMimeTypes
     * @var array */
    private $_allowedMimeTypes = array();

    /** Name of the $_FILES form variable containing the uploaded file(s).
     * @var string */
    private $_fieldName = '';

    /** Array of all available mimetypes.
     * @see self::$_allowedMimeTypes
     * @var array */
    private $_availableMimeTypes = array();

    /** Array of files to be uploaded.
     * @var array */
    private $_filesToUpload = array();

    /** Array of current file information (name, extension, type, etc.)
     * @var array */
    private $_currentFile = array();

    /** Array of IP addresses that are allowed to upload files.
     * @see self::limitByIP()
     * @var array */
    private $_allowedIPS = array();

    /** Array of uploaded file information.
     * @var array */
    private $_uploadedFiles = array();

    /** Maximum width, in pixels, for uploaded images.
     * @var integer */
    private $_maxImageWidth = 300;

    /** Maximum height, in pixels, for uploaded images.
     * @var integer */
    private $_maxImageHeight = 300;

    /** Maximum uploaded file size, in bytes.
     * @var integer */
    private $_maxFileSize = 1048576;

    /** Auto-resize images upon upload?
     * @var boolean */
    private $_autoResize = false;

    /** Allow any MIME type to be uploaded or downloaded?
     * @var boolean */
    private $_allowAnyType = false;

    /** Keep the original image after resizing?
     * @var boolean */
    private $_keepOriginalImage = false;

    /** Maximum uploads allowed per form.
     * @var integer */
    private $_maxFileUploadsPerForm = 5;

    /** Array of destination file names.
     * @var array */
    private $_fileNames = '';

    /** Array of file permissions.
     * @var array */
    private $_permissions = '';

    /** Log file name.
     * @var string */
    private $_logFile = '';

    /** Log activity?
     * @var boolean */
    private $_doLogging = false;

    /** Continue processing if an error is encountered?
     * @var boolean */
    private $_continueOnError = false;

    /** Enable debugging?
     * @var boolean */
    private $_debug = false;

    /** Limit uploads to authorized IP addresses?
     * @see self::$_allowedIPS
     * @see self::limitByIP()
     * @var boolean */
    private $_limitByIP = false;

    /** Index into the filenames and permissions arrays.
     * @var integer */
    private $_imageIndex = 0;


    /**
     * Constructor.
     * Sets the available MIME types to the default.
     */
    public function __construct()
    {
        $this->setAvailableMimeTypes();
    }


    /**
     * Adds a warning that was encountered.
     *
     * @param   string  $warningText     Text of warning
     */
    private function _addWarning($warningText)
    {
        $nwarnings = count($this->_warnings);
        $nwarnings = $nwarnings + 1;
        $this->_warnings[$nwarnings] = $warningText;
        if ($this->loggingEnabled()) {
            COM_errorLog($warningText, SHOP_LOG_WARNING);
        }
    }


    /**
     * Adds an error that was encountered.
     *
     * @param   string      $errorText      Text of error
     */
    private function _addError($errorText)
    {
        $nerrors = count($this->_errors);
        $nerrors = $nerrors + 1;
        $this->_errors[$nerrors] = $errorText;
        if ($this->loggingEnabled()) {
            COM_errorLog($errorText, SHOP_LOG_ERROR);
        }
    }


    /**
     * Adds a debug message.
     *
     * @param   string      $debugText      Text of debug message
     */
    private function _addDebugMsg($debugText)
    {
        $nmsgs = count($this->_debugMessages);
        $nmsgs = $nmsgs + 1;
        $this->_debugMessages[$nmsgs] = $debugText;
        if ($this->loggingEnabled()) {
            COM_errorLog($debugText, SHOP_LOG_DEBUG);
        }
    }


    /**
     * Adds PHP upload error.
     *
     * @param   int       $error      PHP returned error code
     */
    private function _uploadError($error)
    {
        switch ($error) {
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
     * Logs an item to the log file.
     *
     * @param   string      $logtype    can be 'warning' or 'error'
     * @param   string      $text       Text to log to log file
     * @return  boolean     Whether or not we successfully logged an item
     */
    public function logItem($logtype, $text)
    {
        $timestamp = strftime("%c");
        if (!$file = fopen($this->_logFile, 'a')) {
            // couldn't open log file for writing so let's disable logging and add an error
            $this->setLogging(false);
            $this->_addError('Error writing to log file: ' . $this->_logFile . '.  Logging has been disabled');
            return false;
        }
        fputs ($file, "$timestamp - $logtype: $text \n");
        fclose($file);
        return true;
    }


    /**
     * Defines superset of available Mime types.
     *
     * @param   array   $mimeTypes  Array of ($type=>array(ext,ext,etc.))
     * @return  object  $this
     */
    public function setAvailableMimeTypes($mimeTypes = array())
    {
        if (sizeof($mimeTypes) == 0) {
            $this->_availableMimeTypes = array(
                'application/x-gzip-compressed'     => array('tar.gz','tgz','gz'),
                'application/x-zip-compressed'      => array('zip'),
                'application/x-tar'                 => array('tar','tar.gz','gz'),
                'application/x-gtar'                => array('tar'),
                'text/plain'                        => array('phps','txt','inc','php','md'),
                'text/html'                         => array('html','htm'),
                'image/bmp'                         => array('bmp','ico'),
                'image/gif'                         => array('gif'),
                'image/pjpeg'                       => array('jpg','jpeg'),
                'image/jpeg'                        => array('jpg','jpeg'),
                'image/png'                         => array('png'),
                'image/x-png'                       => array('png'),
                'audio/mpeg'                        => array('mp3'),
                'audio/wav'                         => array('wav'),
                'application/pdf'                   => array('pdf'),
                'application/x-shockwave-flash'     => array('swf'),
                'application/msword'                => array('doc'),
                'application/msexcel'               => array('xls'),
                'application/mspowerpoint'          => array('ppt'),
                'application/vnd.ms-excel'          => array('xls'),
                'application/vnd.ms-office'         => array('xls'),
                'application/octet-stream'          => array('fla','psd'),
                'application/pdf'                   => array('pdf'),
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => array('xlsx'),
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => array('docx'),
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => array('pptx'),
                'application/msaccess'              => array('mdb'),
            );
        } else {
            $this->_availableMimeTypes = self::_fixMimeArrayCase($mimeTypes);
        }
        return $this;
    }


    /**
     * Checks if current file is an image.
     *
     * @return  boolean   returns true if file is an image, otherwise false
     */
    private function _isImage()
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
     * Verifies the file size meets specified size limitations.
     *
     * @return  boolean   returns true of file size is within our limits otherwise false
     */
    private function _fileSizeOk()
    {
        if ($this->_debug) {
            $this->_addDebugMsg('File size for ' . $this->_currentFile['name'] . ' is ' . $this->_currentFile['size'] . ' bytes');
        }

        if ($this->_currentFile['size'] > $this->_maxFileSize) {
            COM_errorLog(
                "Uploaded file: ".$this->_currentFile['name']." exceeds max file size of " . $this->_maxFileSize,
                SHOP_LOG_WARNING
            );
            return false;
        } else {
            return true;
        }
    }


    /**
     * Checks to see if file is an image and meets size limitiations.
     *
     * @param   boolean $doResizeCheck  True to log messages if the size isn't valid.
     * @return  boolean     True if image height/width meet our limitations otherwise false
     */
    private function _imageSizeOK($doResizeCheck=true)
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
        if (
            ($doResizeCheck && !$this->_autoResize) ||
            !$doResizeCheck
        ) {
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
     * Gets the width and height of an image.
     *
     * @return  array     Array with width and height of current image
     */
    private function _getImageDimensions()
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
     * @param   int     $width      width of the unscaled image
     * @param   int     $height     height of the unscaled image
     * @return  double              resize factor
     */
    private function _calcSizefactor ($width, $height) // 1000
    {
        if (
            ($width > $this->_maxImageWidth) ||
            ($height > $this->_maxImageHeight)
        ) {
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
     * @param   string  $filename   name of uploaded file
     * @return  boolean     true: okay, false: an error occured
     */
    private function _keepOriginalFile($filename)
    {
        if ($this->_keepOriginalImage) {
            $lFilename_large = substr_replace(
                $this->_getDestinationName(),
                '_original.',
                strrpos($this->_getDestinationName (), '.'),
                1
            );
            $lFilename_large_complete = $this->_filePath . '/' . $lFilename_large;
            if (!copy ($filename, $lFilename_large_complete)) {
                $this->_addError ("Couldn't copy $filename to $lFilename_large_complete.  You'll need to remove both files.");
                $this->printErrors(true);
                return false;
            }
        }
        return true;
    }


    /**
     * Gets destination file name for current file.
     *
     * @return  string    returns destination file name
     */
    private function _getDestinationName()
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
     * Gets permissions for a file.  This is used to do a chmod.
     *
     * @return  string  returns final permisisons for current file
     */
    private function _getPermissions()
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
     * This function actually completes the upload of a file.
     *
     * @return  boolean     true if copy succeeds otherwise false
     */
    private function _copyFile()
    {
        if (!is_writable($this->_filePath)) {
            // Developer didn't check return value of setPath() method which would
            // have told them the upload directory was not writable.  Error out now
            $this->_addError('Specified upload directory, ' . $this->_filePath . ' exists but is not writable');
            return false;
        }
        $sizeOK = true;
        if (!($this->_imageSizeOK(false)) && $this->_autoResize) {
            $imageInfo = $this->_getImageDimensions($this->_currentFile['tmp_name']);
            if ($imageInfo['width'] > $this->_maxImageWidth) {
                $sizeOK = false;
            }

            if ($imageInfo['height'] > $this->_maxImageHeight) {
                $sizeOK = false;
            }
        }

        if (isset($this->_currentFile['_data_dir']) && $this->_currentFile['_data_dir']) {
            $returnMove = @copy($this->_currentFile['tmp_name'], $this->_filePath . '/' . $this->_getDestinationName());
            @unlink($this->_currentFile['tmp_name']);
        } else {
            $returnMove = move_uploaded_file($this->_currentFile['tmp_name'], $this->_filePath . '/' . $this->_getDestinationName());
        }

        if (!($sizeOK)) {
            // OK, resize
            $sizefactor = $this->_calcSizefactor($imageInfo['width'], $imageInfo['height']);
            $newwidth = (int)($imageInfo['width'] * $sizefactor);
            $newheight = (int)($imageInfo['height'] * $sizefactor);
            $newsize = $newwidth.'x'.$newheight;
            if (!$this->_keepOriginalFile($this->_filePath . '/' . $this->_getDestinationName())) {
                exit;
            }
            list($retval,$msg) = IMG_resizeImage($this->_filePath . '/' . $this->_getDestinationName(), $this->_filePath . '/' . $this->_getDestinationName(), $newheight, $newwidth, $this->_currentFile['type'], 0 );
            if ($retval !== true) {
                $this->_addError('Image, ' . $this->_currentFile['name'] . ' ' . $msg);

                $this->printErrors(true);
                exit;
            } else {
                $this->_addDebugMsg ('Image, ' . $this->_currentFile['name'] . ' was resized from ' . $imageInfo['width'] . 'x' . $imageInfo['height'] . ' to ' . $newsize);
            }
        }
        $returnChmod = true;
        $perms = $this->_getPermissions();
        if (!empty($perms)) {
            $returnChmod = @chmod ($this->_filePath . '/' . $this->_getDestinationName (), octdec($perms));
        }
        if ($returnMove && $returnChmod) {
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
     * Sets $_FILES fieldname.
     *
     * @param   string  $fieldname  Index into $_FILES array
     * @return  object  $this
     */
    public function setFieldName($fieldname)
    {
        $this->_fieldName = $fieldname;
        return $this;
    }


    /**
     * Sets mode to allow any mime type.
     *
     * @param   boolean $switch  True to turn on, false to turn off
     * @return  object  $this
     */
    public function setAllowAnyMimeType($switch)
    {
        $this->_allowAnyType = $switch ? true : false;
        return $this;
    }


    /**
     * Sets mode to automatically resize images that are too large.
     *
     * @param   boolean $switch  True to turn on, false to turn off
     * @return  object  $this
     */
    public function setAutomaticResize($switch)
    {
        $this->_autoResize = $switch;
        return $this;
    }


    /**
     * Allows you to override default max file size.
     *
     * @param   integer $size_in_bytes  Max. size for uploaded files
     * @return  object  $this
     */
    public function setMaxFileSize($size_in_bytes)
    {
        if (is_numeric($size_in_bytes)) {
            $this->_maxFileSize = (int)$size_in_bytes;
        }
        return $this;
    }


    /**
     * Allows you to override default max. image dimensions.
     *
     * @param   integer $width_pixels    Max. width allowed
     * @param   integer $height_pixels   Max. height allowed
     * @return  object  $this
     */
    public function setMaxDimensions($width_pixels, $height_pixels)
    {
        if (is_numeric($width_pixels) && is_numeric($height_pixels)) {
            $this->_maxImageWidth = (int)$width_pixels;
            $this->_maxImageHeight = (int)$height_pixels;
        }
        return $this;
    }


    /**
     * Sets the max number of files that can be uploaded per form.
     *
     * @param   int       $maxfiles    Maximum number of files to allow. Default is 5
     * @return  boolean   True if set, false otherwise
     */
    public function setMaxFileUploads($maxfiles)
    {
        $this->_maxFileUploadsPerForm = (int)$maxfiles;
        return $this;
    }


    /**
     * Allows you to keep the original (unscaled) image.
     *
     * @param   boolean   $keepit   true = keep original, false = don't
     * @return  object  $this
     */
    public function keepOriginalImage($keepit)
    {
        $this->_keepOriginalImage = $keepit ? true : false;
        return $this;
    }


    /**
     * Extra security option to allow uploads only from a specific set of IP addresses.
     * This is only good for those who are paranoid.
     *
     * @param   array   $validIPS   Array of valid IP addresses to allow file uploads from
     * @return  object  $this
     */
    public function limitByIP($validIPS = array('127.0.0.1'))
    {
        if (is_array($validIPS)) {
            $this->_limitByIP = true;
            $this->_allowedIPS = $validIPS;
        } else {
            $this->_addError('Bad call to method limitByIP(), must pass array of valid IP addresses');
        }
        return $this;
    }


    /**
     * Allows you to specify whether or not to continue processing other files
     * when an error occurs or exit immediately. Default is to exit immediately.
     *
     * NOTE: this only affects the actual file upload process.
     *
     * @param   boolean     $switch     true or false
     * @return  object  $this
     */
    public function setContinueOnError($switch)
    {
        $this->_continueOnError = $switch ? true : false;
        return $this;
    }


    /**
     * Sets log file.
     *
     * @param   string  $logFile    Fully qualified path to log files
     * @return  object  $this
     */
    public function setLogFile($logFile = '')
    {
        if (empty($logFile) OR !file_exists($logFile)) {
            // Log file doesn't exist, produce warning
            $this->_addWarning('Log file, ' . $logFile . ' does not exists, setLogFile() method failed');
            $this->_doLogging = false;
        }
        $this->_logFile = $logFile;
        return $this;
    }


    /**
     * Enables/disables logging of errors and warnings.
     *
     * @param   boolean     $switch     flag, true or false
     * @return  object  $this
     */
    public function setLogging($switch)
    {
        if ($switch AND !empty($this->_logFile)) {
            $this->_doLogging = true;
        } else {
            if ($switch AND empty($this->_logFile)) {
                $this->_addWarning('Unable to enable logging because no log file was set. Use setLogFile() method');
            }
            $this->_doLogging = false;
        }
        return $this;
    }


    /**
     * Returns whether or not logging is enabled.
     *
     * @return  boolean returns true if logging is enabled otherwise false
     */
    public function loggingEnabled()
    {
        return $this->_doLogging;
    }


    /**
     * Will force the debug messages in this class to be printed.
     *
     * @param   boolean     $switch     flag, true or false
     * @return  object  $this
     */
    public function setDebug($switch)
    {
        if ($switch) {
            $this->_debug = true;
            // setting debugs implies logging is on too
            $this->setLogging(true);
        } else {
            $this->_debug = false;
        }
        return $this;
    }


    /**
     * This function will print any errors out.  This is useful in debugging.
     *
     * @param   boolean     $verbose    whether or not to print immediately or return only a string
     * @return  string  if $verbose is false it returns all errors otherwise just an empty string
     */
    public function printErrors($verbose=false)
    {
        $retval = '';
        if (isset($this->_errors) && is_array($this->_errors)) {
            foreach ($this->_errors as $msg) {
                if ($verbose) {
                    print "$msg<br />\n";
                } else {
                    $retval .= "$msg<br />\n";
                }
            }
        }
        return $retval;
    }


    /**
     * Return the error messages accumulated.
     *
     * @return  array   Array of error message strings.
     */
    public function getErrors()
    {
        return $this->_errors;
    }


    /**
     * Return the warning messages accumulated.
     *
     * @return  array   Array of warning message strings.
     */
    public function getWarnings()
    {
        return $this->_warnings;
    }


    /**
     * This function will print any warnings out.  This is useful in debugging.
     */
    public function printWarnings()
    {
        if (isset($this->_warnings) AND is_array($this->_warnings)) {
            foreach ($this->_warnings as $msg) {
                print $msg . "<br />\n";
            }
        }
    }


    /**
     * This function will print any debug messages out.
     */
    public function printDebugMsgs()
    {
        if (isset($this->_debugMessages) AND is_array($this->_debugMessages)) {
            foreach ($this->_debugMessages as $msg) {
                print $msg . "<br />\n";
            }
        }
    }


    /**
     * Returns if any errors have been encountered thus far.
     *
     * @return  boolean     True if there were errors otherwise False
     */
    public function areErrors()
    {
        if (count($this->_errors) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Ensure that all mime-types are lower-case.
     * Also strips leading `.` characters from extensions, and converts
     * the extensions to an array if the old-style comma-separated string
     * is supplied.
     *
     * @param   array   $arr    Original array
     * @return  array   Array of lower-case mimetype=>extensions
     */
    private static function _fixMimeArrayCase($arr)
    {
        $arr = array_change_key_case($arr, CASE_LOWER);
        foreach ($arr as $key=>$data) {
            if (is_string($data)) {
                // Convert to array if extensions are a comma-separated list
                $data = explode(',', $data);
            }
            foreach ($data as $idx=>$ext) {
                if (strpos($ext, '.') === 0) {
                    $data[$idx] = substr($ext, 1);
                }
            }
            $arr[$key] = array_map('strtolower', $data);
        }
        return $arr;
    }


    /**
     * Sets allowed mime types for this instance.
     *
     * @param   array   allowedMimeTypes        Array of allowed mime types
     * @return  object  $this
     */
    public function setAllowedMimeTypes($mimeTypes = array())
    {
        if (empty($this->_availableMimeTypes)) {
            $this->setAvailableMimeTypes();
        }

        // If nothing set, use all available Mime types.
        if (empty($mimeTypes)) {
            $this->_allowedMimeTypes = $this->_availableMimeTypes;
        } else {
            $this->_allowedMimeTypes = self::_fixMimeArrayCase($mimeTypes);
        }
        return $this;
    }


    /**
     * Add a mime-type and extension set to an internal variable.
     * Both `_allowedMimeTypes` and `_availableMimeTypes` share the same
     * structure, so this function can be used to add elements to both.
     *
     * @param   array   $arr    Array to affect, by reference
     * @param   string  $mime   Mime-Type to be added
     * @param   string|array    $exts   One or more extensions to be added
     * @return  object  $this
     */
    private function _addMimeType(&$arr, $mime, $exts)
    {
        // Extension is expected to not include the leading dot
        if (!is_array($exts)) {
            $exts = explode(',', $exts);
        }

        $mime = strtolower($mime);
        if (!array_key_exists($mime, $arr)) {
            // Add the mime-type key if it doesn't exist
            $arr[$mime] = array();
        }
        foreach ($exts as $ext) {
            if ($ext[0] == '.') {
                $ext = substr($ext, 1);
            }
            $ext = trim(strtolower($ext));
            if (!in_array($arr[$mime], $ext)) {
                $arr[$mime][] = $ext;
            }
        }
        return $this;
    }


    /**
     * Add a single mime type and extension to the Allowed list.
     *
     * @uses    self::_addMimeType()
     * @param   string  $mime   Mime type
     * @param   string|array  $exts   File extension
     * @return  object  $this
     */
    public function addAllowedMimeType($mime, $exts)
    {
        $this->_addMimeType($this->_allowedMimeTypes, $mime, $exts);
        return $this;
    }


    /**
     * Add a single mime type and extension to the Available list.
     * This is to allow the addition of new mime types without updating
     * the class.
     * The `$allowed` parameter defaults to `true` since it typically
     * won't make sense to add to the available types unless the new type
     * is also to be allowed.
     *
     * @uses    self::_addMimeType()
     * @param   string  $mime       Mime type
     * @param   string|array    $exts       One or more file extensions
     * @param   boolean $allowed    True to also add to the allowed mime types
     * @return  object  $this
     */
    public function addAvailableMimeType($mime, $exts, $allowed=true)
    {
        $this->_addMimeType($this->_availableMimeTypes, $mime, $exts);
        if ($allowed) {
            $this->_addMimeType($this->addAllowedMimeType, $mime, $exts);
        }
        return $this;
    }


    /**
     * Gets allowed mime types for this instance.
     *
     * @return  array   Returns array of allowed mime types
     */
    public function getAllowedMimeTypes()
    {
        return $this->_allowedMimeTypes;
    }


    /**
     * Checks to see that mime type for current file is allowed for upload.
     *
     * @return  boolean     true if current file's mime type is allowed otherwise false
     */
    public function checkMimeType()
    {
        if ($this->_allowAnyType == true) {
            return true;
        }
        $metaData = IMG_getMediaMetaData($this->_currentFile['tmp_name']);
        if (!isset($metaData['mime_type'])) {
            $this->_addError('Unable to determine mime type for ' . $this->_currentFile['name']);
            return false;
        }

        if ($metaData['mime_type'] != '') {
            $this->_currentFile['type'] = $metaData['mime_type'];
        } else {
            $this->_currentFile['type'] = 'application/octet-stream';
        }
        $sc = strpos($this->_currentFile['type'], ';');
        if ($sc > 0) {
            $this->_currentFile['type'] = substr($this->_currentFile['type'], 0, $sc);
        }
        $mimeTypes = $this->getAllowedMimeTypes ();
        foreach ($mimeTypes as $mimeT => $extList) {
            if ($mimeT == $this->_currentFile['type']) {
                if (in_array($this->_currentFile['extension'], $extList)) {
                    return true;
                }
            }
        }
        $this->_addError(
            'Mime type, ' . $this->_currentFile['type']
            . ', or extension of ' . $this->_currentFile['name']
            . ' not in list of allowed types.'
        );
        //$this->_addError("allowed types: " . print_r($mimeTypes,true));
        return false;
    }


    /**
     * Sets file path.
     *
     * @param   string  $path   Directory on server to store uploaded files
     * @return  object  $this
     */
    public function setPath($path)
    {
        $path = rtrim($path, '/') . '/';
        if (!is_dir($path)) {
            $this->_addError('Specified upload directory, ' . $path . ' is not a valid directory');
        } elseif (!is_writable($path)) {
            $this->_addError('Specified upload directory, ' . $path. ' exists but is not writable');
        } else {
            $this->_filePath = $path;
        }
        return $this;
    }


    /**
     * Returns the file path.
     *
     * @return  string  returns path to file upload directory
     */
    public function getPath()
    {
        return $this->_filePath;
    }


    /**
     * This function will set the target names of any files uploaded.
     * If the number of file names sent doesn't match the number of uploaded
     * files a warning will be generated but processing will continue.
     *
     * @param   string|array    $fileNames      A string or string array of file names
     * @return  object  $this
     */
    public function setFileNames($fileNames = 'glfusion_uploadedfile')
    {
        if (isset($fileNames) && is_array($fileNames)) {
            // this is an array of file names, set them
            $this->_fileNames = $fileNames;
        } else {
            $this->_fileNames = array($fileNames);
        }
        return $this;
    }


    /**
     * Changes permissions for uploaded files.
     * If only one set of perms is sent then they are applied to all uploaded files.
     * If more then one set of perms is sent (i.e. $perms is an array) then
     * permissions are applied one by one.
     * Any files not having an associated permissions will be left alone.
     * NOTE: this is meant to be called BEFORE you do the upload and ideally
     * is called right after setFileNames().
     *
     * @param   string|array    $perms      A string or string array of file permissions
     * @return  object  $this
     */
    public function setPerms($perms)
    {
        if (is_array($perms)) {
            // this is an array of file names, set them
            $this->_permissions = $perms;
        } else {
            $this->_permissions = array($perms);
        }
        return $this;
    }


    /**
     * Returns how many actual files were sent for upload.
     * This will ignore HTML file fields that were left blank.
     *
     * @return  integer Number of files sent for upload
     */
    public function numFiles()
    {
        if (empty($this->_filesToUpload)) {
            if (empty($this->_fieldName)) {
                $this->_filesToUpload = $_FILES;
            } else {
                $this->_filesToUpload = $_FILES[$this->_fieldName];
            }
        }

        $fcount = 0;
        if (is_array($this->_filesToUpload['name'])) {
            $fcount = 0;
            foreach ($this->_filesToUpload['name'] as $key=>$filename) {
                if (!empty($filename)) {
                    $fcount++;
                }
            }
            reset($this->_filesToUpload['name']);
        } else {
            if (!empty($this->_filesToUpload['name'])) {
                $fcount = 1;
            }
        }

        return $fcount;
    }


    /**
     * Handle the upload processing for a single file.
     * The _currentFile array is expected to be set by the caller.
     * Errors are added to the global _errors array. No return value.
     */
    private function _uploadCurrentFile()
    {
        $metaData = IMG_getMediaMetaData($this->_currentFile['tmp_name']);
        if ($metaData['mime_type'] != '') {
            $this->_currentFile['type'] = $metaData['mime_type'];
        } else {
            $this->_currentFile['type'] = 'application/octet-stream';
        }

        if (!empty($this->_currentFile['name'])) {
            // Verify file meets size limitations
            if (!$this->_fileSizeOk()) {
                $err_msg = 'File, ' . $this->_currentFile['name'] . ', is larger than the ' .
                    COM_numberFormat($this->_maxFileSize,0) . ' byte limit';
                $this->_addError($err_msg);
                $this->_currentFile['localerror'][] = $err_msg;
            }

            // If all systems check, do the upload
            if (
                $this->checkMimeType() &&
                $this->_imageSizeOK() &&
                empty($this->_currentFile['localerror'])
            ) {
                if ($this->_copyFile()) {
                    //$this->_uploadedFiles[] = $this->_filePath . '/' . $this->_getDestinationName();
                    $this->_uploadedFiles[] = $this->_currentFile;
                }
            }

            $this->_currentFile = array();
        }
    }


    /**
     * Uploads any posted files.
     * If `false` is returned the caller should get error messages by calling
     * getErrors().
     *
     * @uses    self::_uploadCurrentFile()
     * @return  boolean     True if no errors were encountered otherwise false
     */
    public function uploadFiles()
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
            if (empty($this->_fieldName) || $this->_fieldName == '') {
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
        if (!$this->_filePath) {
            $this->_addError('No Upload Directory Specified, use setPath() method');
        }

        // Verify allowed mime types exist
        if (!$this->_allowedMimeTypes && $this->_allowAnyType == false) {
            // TODO: Better to just assume allowed = available?
            $this->_addError('No allowed mime types specified, use setAllowedMimeTypes() method');
        }

        if (is_array($this->_filesToUpload['name'])) {
            // Multiple files uploaded
            foreach ($this->_filesToUpload["error"] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $fparts = pathinfo($this->_filesToUpload['name'][$key]);
                    //TODO set extension
                    $this->_currentFile['name'] = $fparts['basename'];
                    $this->_currentFile['extension'] = strtolower($fparts['extension']);
                    $this->_currentFile['tmp_name'] = $this->_filesToUpload["tmp_name"][$key];
                    $this->_currentFile['type'] = $this->_filesToUpload["type"][$key];
                    $this->_currentFile['size'] = $this->_filesToUpload["size"][$key];
                    $this->_currentFile['error'] = $this->_filesToUpload["error"][$key];
                    $this->_currentFile['_data_dir'] = isset($this->_filesToUpload["_data_dir"][$key]) ? $this->_filesToUpload["_data_dir"][$key] : '';
                    $this->_currentFile['localerror'] = array();

                    $this->_uploadCurrentFile();
                    // If errors were set in _uploadCurrentFile(), and _continueOnError is not set,
                    // abort immediately.
                    if ($this->areErrors() && !$this->_continueOnError) {
                        break;
                    }

                } else {
                    if ($error != UPLOAD_ERR_NO_FILE) {
                        $this->_uploadError($error);
                    }
                }
                $this->_imageIndex++;
            }
        } else {
            if (
                $this->_filesToUpload['name'] != '' &&
                $this->_filesToUpload['error'] == UPLOAD_ERR_OK
            ) {
                $fparts = pathinfo($this->_filesToUpload['name']);
                $this->_currentFile['name'] = $fparts['basename'];
                $this->_currentFile['extension'] = strtolower($fparts['extension']);
                $this->_currentFile['tmp_name'] = $this->_filesToUpload["tmp_name"];
                $this->_currentFile['type'] = $this->_filesToUpload["type"];
                $this->_currentFile['size'] = $this->_filesToUpload["size"];
                $this->_currentFile['error'] = $this->_filesToUpload["error"];
                $this->_currentFile['_data_dir'] = isset($this->_filesToUpload["_data_dir"]) ? $this->_filesToUpload["_data_dir"] : '' ;
                $this->_uploadCurrentFile();
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


    /**
     * Attempts to dowload a file.
     *
     * @param   string      $fileName       file to download without path
     * @return  boolean     true on success otherwise false
     */
    public function downloadFile($fileName)
    {
        if (strstr( PHP_OS, "WIN")) {  // Added as test1 below was failing on Windows platforms
            $strPathSeparator = '\\';
            $this->_filePath = str_replace('/','\\', $this->_filePath);
        } else {
            $strPathSeparator = '/';
        }

        $fileSpec = $this->_filePath . $fileName;
        // Ensure file exists and is accessible
        if (
            !is_file($fileSpec) ||
            ($this->_filePath <> (dirname($fileSpec) . $strPathSeparator))
        ) {
            $this->_addError('Specified file ' . $fileSpec . ' does not exist or is not accessible');
            return false;
        }

        // Make sure file is readable - test 2
        clearstatcache();
        if (!is_readable($fileSpec)) {
            $this->_addError('Specified file, ' . $fileSpec . ' exists but is not readable');
            return false;
        }

        // OK, file is valid, get file parts
        $fparts = pathinfo($fileSpec);

        // Load the currentFile array to use the same functions as uploads.
        $this->_currentFile = array(
            'name'      => $fileName,       // full name, including extension
            'extension' => $fparts['extension'],
            'tmp_name'  => $fileSpec,
            'type'      => mime_content_type($fileSpec),
            'size'      => filesize($fileSpec),
            'error'     => array(),
            'localerror' => array(),
        );

        // If application has not set the allowedExtensions then initialize to the default
        if (!$this->checkMimeType()) {
            $this->_addError("Mime type {$this->_currentFile['type']} is not allowed");
            return false;
        }

        // Send headers.
        header('Content-Type: ' . $this->_currentFile['type']);
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '. $this->_currentFile['size']);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        session_write_close();
        ob_end_flush();
        // Send file contents.
        $fp = fopen($fileSpec, 'rb');
        ob_end_clean();
        fpassthru($fp);
        fclose($fp);
        return true;
    }


    /**
     * Sets the path to where the mogrify ImageMagick function is.
     *
     * @comment NOOP compatibility function
     * @param   string    $path_to_mogrify    Absolute path to mogrify
     * @return  object  $this
     */
    public function setMogrifyPath($path_to_mogrify)
    {
        return $this;
    }

    /**
     * Sets the path to where the netpbm utilities are.
     *
     * @comment NOOP compatibility function
     * @param   string    $path_to_netpbm    Absolute path to netpbm dir
     * @return  object  $this
     */
    public function setNetPBM($path_to_netpbm)
    {
        return $this;
    }


    /**
     * Configure upload to use GD library.
     *
     * @comment NOOP compatibility function
     * @return  object  $this
     */
    function setGDLib()
    {
        return $this;
    }


    /**
     * If enabled will ignore the MIME checks on file uploads
     *
     * @comment NOOP compatibility function
     * @param   boolean $switch     flag, true or false
     * @return  object  $this
     */
    public function setIgnoreMimeCheck($switch)
    {
        return $this;
    }


    /**
     * Set JPEG quality.
     * The 'quality' is an arbitrary value used by the IJG library.
     * It is not a percent value! The default (and a good value) is 75.
     *
     * @comment NOOP compatibility function
     * @param   int       $quality  JPEG quality (0-100)
     * @return  object  $this
     */
    public function setJpegQuality($quality)
    {
        return $This;
    }


    /**
     * Return the private _uploadedFiles property.
     *
     * @return  array   Array of uploaded files (full paths)
     */
    public function getUploadedFiles()
    {
        return $this->_uploadedFiles;
    }


    /**
     * Get the current file.
     *
     * @return  array   Array of file data
     */
    public function getCurrentFile()
    {
        return $this->_currentFile;
    }


    /**
     * Return the private _fileNames property.
     *
     * @return  array   Array of filenames (base names)
     */
    public function getFileNames()
    {
        return $this->_fileNames;
    }

}
