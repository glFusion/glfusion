<?php
/**
* glFusion CMS
*
* glFusion File System utility functions
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

namespace glFusion;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Log\Log;

/**
 * Class FileSystem
 *
 * @package glFusion
 */
class FileSystem
{

    /**
    * @var array
    */
     private $errors = array();

    /**
    * @var integer
    */
     private $bytesCopied = 0;

    /**
    * @var array
    */
     private $errorFiles = array();

    /**
     * constructor
     */
    public function __construct()
    {
        $this->errors = array();
        $this->bytesCopied = 0;
        $this->errorFiles = array();
    }

    /**
     * Create a directory
     *
     * @param    string $target Directory to create
     * @param    bool           True on success, false on fail
     */
    public static function mkDir($target)
    {
        if (@is_dir($target) || empty($target)) {
            return true; // already exists
        }

        if (@file_exists($target) && !@is_dir($target)) {
            return false;   // file exists - cannot create directory with same name
        }

        if (self::mkDir(substr($target,0,strrpos($target,'/')))) {
            $ret = @mkdir($target,0755);
            @chmod($target, 0755);
            return (bool) $ret;
        }
        return true;
    }

    /**
    * Deletes a directory (with recursive sub-directory support)
    *
    * @parm     string            Path of directory to remove
    * @return   bool              True on success, false on fail
    */
    public static function deleteDir($path)
    {
        if (!is_string($path) || $path == "") {
            return false;
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(30);
        }
        if (@is_dir($path)) {
            if (!$dh = @opendir($path)) {
                return false;
            }

            while (false !== ($f = readdir($dh))) {
                if ($f == '..' || $f == '.') {
                    continue;
                }
                self::deleteDir("$path/$f");
            }
            closedir($dh);
            return @rmdir($path);
        } else {
            return @unlink($path);
        }
        return false;
    }


    /**
    * Copies srcdir to destdir (recursive)
    *
    * @param    string  $srcdir Source Directory
    * @param    string  $dstdir Destination Directory
    *
    * @return   bool              True on success, false on fail
    */
    public function dirCopy($srcdir, $dstdir)
    {
        static $fail = 0;

        if (!@is_dir($srcdir)) {
            $this->errors[] = 'dirCopy :: Invalid source directory';
            return false;
        }

        if (!@is_dir($dstdir)) {
            $rc = self::mkDir($dstdir);
            if ($rc === false) {
                $this->errors[] = 'dirCopy :: Unable to create destination directory: ' . $dstdir;
                return false;
            }
        }

        if ($curdir = @opendir($srcdir)) {
            while (false !== ($file = readdir($curdir))) {
                if ($file != '.' && $file != '..') {
                    $srcfile = $srcdir . '/' . $file;
                    $dstfile = $dstdir . '/' . $file;
                    if (is_file($srcfile)) {
                        if (@copy($srcfile, $dstfile)) {
                            @touch($dstfile, filemtime($srcfile));
                            @chmod($dstfile, 0644);
                            $this->bytesCopied += filesize($dstfile);
                        } else {
                            Log::write('system',Log::ERROR,"File '$srcfile' could not be copied - check directory permissions.");
                            $fail++;
                            $this->errors[] = 'dirCopy :: Unable to copy '. $srcfile;
                            $this->errorFiles[] = $dstfile;
                        }
                    } elseif (@is_dir($srcfile)) {
                        $rc = $this->dirCopy($srcfile,$dstfile);
                        if ($rc === false) {
                            $fail++;
                        }
                    }
                }
            }
            closedir($curdir);
        } else {
            Log::write('system',Log::ERROR,"Unable to open temporary directory: " . $srcdir);
            $this->errors[] = 'Unable to create temporary directory';
            return false;
        }

        if ($fail > 0) {
            return false;
        }
        return true;
    }


    /**
    * Copies srcfile to destdir
    *
    * @param    string  $srcdir Source Directory
    * @param    string  $dstdir Destination Directory
    *
    * @return   bool    true on success, false on fail
    */
    public function fileCopy($srcfile, $dstdir )
    {
        if (!@is_dir($dstdir)) {
            $ret = self::mkDir($dstdir);
            if ($ret === false) {
                $this->errors[] = 'fileCopy :: Unable to create destination directory';
                return false;
            }
        }

        if (@is_file($srcfile)) {
            $dstfile = $dstdir . '/' . basename($srcfile);
            if (@copy($srcfile, $dstfile)) {
                @touch($dstfile, filemtime($srcfile));
                @chmod($dstfile, 0644);
            } else {
                Log::write('system',Log::ERROR,"File '$srcfile' could not be copied");
                return false;
            }
        } else {
            Log::write('system',Log::ERROR,"ERROR: Unable to open temporary file: " . $srcfile);
            return false;
        }
        return true;
    }


    /**
    * Performs a test copy of srcdir to destdir
    *
    * @param    string  $srcdir Source Directory
    * @param    string  $dstdir Destination Directory
    *
    * @return   bool    true on success, false on fail
    */
    public function testCopy($srcdir, $dstdir)
    {
        $num        = 0;
        $fail       = 0;
        $sizetotal  = 0;
        $createdDst = 0;
        $ret        = '';
        $verbose    = 0;

        $failedFiles = array();

        if (!@is_dir($dstdir)) {
            $rc = self::mkDir($dstdir);
            if ($rc == false ) {
                $this->errorFiles[] = $dstdir;
                Log::write('system',Log::ERROR,"ERROR: Unable to create directory " . $dstdir);
                return false;
            }
            $createdDst = 1;
        }

        if ($curdir = @opendir($srcdir)) {
            while (false !== ($file = readdir($curdir))) {
                if ($file != '.' && $file != '..') {
                    $srcfile = $srcdir . '/' . $file;
                    $dstfile = $dstdir . '/' . $file;
                    if (is_file($srcfile)) {
//forced error
//if ( $this->isWritable($dstfile) ) {
                        if ( ! self::isWritable($dstfile) ) {
                            $this->errorFiles[] = $dstfile;
                            Log::write('system',Log::ERROR,"ERROR: File '$dstfile' cannot be written");
                            $fail++;
                        }
                    } else if (@is_dir($srcfile)) {
                        $ret = $this->testCopy($srcfile,$dstfile,$verbose);
//forced error
//$ret = false;
                        if ($ret === false) {
                            $fail++;
                        }
                    }
                }
            }
            closedir($curdir);
        }
        if ($createdDst == 1) {
            @rmdir($dstdir);
        }
        if ($fail > 0) {
            return false;
        }
        return true;
    }

    /**
    * Tests if a file / directory is writable
    *
    * @param    string  $path   path to test
    *
    * @return   bool    true if writable, false on fail
    */
    public static function isWritable($path)
    {
        if ($path[strlen($path)-1] == '/') {
            return self::isWritable($path.uniqid(mt_rand()).'.tmp');
        }

        if (@file_exists($path)) {
            if (!($f = @fopen($path, 'r+'))) {
                return false;
            }
            @fclose($f);
            return true;
        }

        if (!($f = @fopen($path, 'w'))) {
            return false;
        }

        @fclose($f);
        @unlink($path);

        return true;
    }


    /**
    * Creates a unique temporary directory
    *
    * Creates a temp directory int he $_CONF['path_data] directory
    *
    * @return   bool / string false on fail - directory name on success
    */
    public static function mkTmpDir()
    {
        global $_CONF;

        $base   = $_CONF['path_data'];
        $dir    = md5(uniqid(mt_rand(), true));
        $tmpdir = $base.$dir;

        if (self::mkDir($tmpdir)) {
            return($dir);
        } else {
            return false;
        }
    }

    public function getErrorFiles()
    {
        return $this->errorFiles;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getBytesCopied()
    {
        return $bytesCopied;
    }

}
