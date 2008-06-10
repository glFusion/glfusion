<?php
// +---------------------------------------------------------------------------+
// | CAPTCHA v3 Plugin                                                         |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2007 by the following authors:                              |
// |                                                                           |
// | Author: Pascal Rehfeldt <Pascal@Pascal-Rehfeldt.com>                      |
// |                                                                           |
// | Adapted for Geeklog by: Mark R. Evans <mevans@ecsnet.com>                 |
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

// this file can't be used on its own
if (strpos ($_SERVER['PHP_SELF'], 'captcha.class.php') !== false)
{
    die ('This file can not be used on its own.');
}

require($_CONF['path'] . 'plugins/captcha/class/filter.class.php');
require($_CONF['path'] . 'plugins/captcha/class/error.class.php');

$imgSet = $_CONF['path'] . 'plugins/captcha/images/static/' . $_CP_CONF['imageset'] . '/imageset.inc';

if (file_exists ($imgSet)) {
    include_once ($imgSet);
} else {
    include_once ($_CONF['path'] . 'plugins/captcha/images/static/default/imageset.inc');
}

class captcha {
    var $Length;
    var $CaptchaString = NULL;
    var $fontpath;
    var $bgpath;
    var $fonts;
    var $backgrounds;

    var $driver;
    var $imageset;
    var $convertpath;
    var $gfxformat;
    var $debug;
    var $session_id;

    function captcha ($csid,$length = 6) {
        global $_CONF, $_CP_CONF;

        $this->driver       = $_CP_CONF['gfxDriver'];
        $this->imageset     = $_CP_CONF['imageset'];
        $this->convertpath  = $_CP_CONF['gfxPath'];
        $this->gfxformat    = $_CP_CONF['gfxFormat'];
        $this->debug        = $_CP_CONF['debug'];
        $this->sizemin      = "34";
        $this->sizemax      = "34";
        $this->blurmin      = "1";
        $this->blurmax      = "5";
        $this->anglemin     = "-5";
        $this->anglemax     = "5";
        $this->swirlmin     = "10";
        $this->swirlmax     = "15";
        $this->wavemin      = "1";
        $this->wavemax      = "5";
        $this->session_id   = $csid;

        if ($this->driver == 2 ) { // static images
            $this->stringGen();
            $this->makeCaptcha();
        } else {
            $this->Length   = $length;

            $this->fontpath = $_CONF['path'] . 'plugins/captcha/images/fonts/';
            $this->bgpath   = $_CONF['path'] . 'plugins/captcha/images/backgrounds/';

            $this->fonts       = $this->getFonts();
            $this->backgrounds = $this->getBackGrounds();

            $errormgr       = new error;

            if ($this->fonts == FALSE) {
                $errormgr->addError('No fonts available!');
                $errormgr->displayError();
                die();
            }

            if (function_exists('imagettftext') == FALSE && $this->driver == 0) {
                $errormgr->addError('');
                $errormgr->displayError();
                die();
            }

            $this->stringGen();
            $this->makeCaptcha();
        }
    }

    function getFonts () {
        $fonts = array();

        if ($handle = @opendir($this->fontpath)) {
            while (($file = readdir($handle)) !== FALSE) {
                $extension = strtolower(substr($file, strlen($file) - 3, 3));
                if ($extension == 'ttf') {
                    $fonts[] = $file;
                }
            }
            closedir($handle);
        } else {
            return FALSE;
        }

        if (count($fonts) == 0) {
            return FALSE;
        } else {
            return $fonts;
        }
    } //getFonts

    function getBackGrounds () {
        $backgrounds = array();

        if ($handle = @opendir($this->bgpath)) {
            while (($file = readdir($handle)) !== FALSE) {
                $extension = strtolower(substr($file, strlen($file) - 3, 3));
                if ($extension == $this->gfxformat) {
                    $backgrounds[] = $file;
                }
            }
            closedir($handle);
        } else {
            return FALSE;
        }

        if (count($backgrounds) == 0) {
            return FALSE;
        } else {
            return $backgrounds;
        }
    } //getBackGrounds


    function getRandFont () {
        return $this->fontpath . $this->fonts[mt_rand(0, count($this->fonts) - 1)];
    } //getRandFont

    function getRandBackground () {
        return $this->bgpath . $this->backgrounds[mt_rand(0, count($this->backgrounds) - 1)];
    } //getRandBackground


    function stringGen () {
        global $cCount;

        if ( $this->driver == 2 ) { // static
            $i = mt_rand(0,$cCount);
            $this->CaptchaString = $i;
        } else {
            $uppercase  = range('A', 'Z');
            //$lowercase  = range('a', 'z');
            $numeric    = range(0, 9);

            $CharPool   = array_merge($uppercase, $numeric);
            $PoolLength = count($CharPool) - 1;

            for ($i = 0; $i < $this->Length; $i++) {
                $this->CaptchaString .= $CharPool[mt_rand(0, $PoolLength)];
            }
        }
    } //StringGen

    function makeCaptcha () {
        global $cString, $_CONF, $_TABLES;

        if ( $this->session_id != 0 ) {
            $sql = "UPDATE {$_TABLES['cp_sessions']} SET validation='" . $this->getCaptchaString() . "' WHERE session_id='" . $this->session_id . "'";
            DB_query($sql);
        } else {
           CAPTCHA_errorLog("CAPTCHA: No valid session id passed");
           exit;
        }

        if ( $this->driver == 2 ) { // static images
            header('Content-type: image/jpeg');
            $filename = $cString[$this->CaptchaString] . '.jpg';
            $fp = fopen($_CONF['path'] . 'plugins/captcha/images/static/' . $this->imageset . '/' . $filename, 'r');
            if ( $fp != NULL ) {
                while (!feof($fp)) {
                    $buf = fgets($fp, 8192);
                    echo $buf;
                }
                fclose($fp);
            } else {
                COM_errorLog("CAPTCHA: Unable to open static image file");
            }
        } else {
            if ( $this->gfxformat != 'png' && $this->gfxformat != 'jpg') {
                header('Content-type: image/gif');
                COM_errorLog("CAPTCHA: No valid gfxFormat specified");
                $errormgr = new error;
                $errormgr->addError('');
                $errormgr->displayError();
                die();
            }

            $header = 'Content-type: image/' . $this->gfxformat;
            header($header);

            if ( $this->driver == 0 ) {
                $imagelength = $this->Length * 25 + 16;
                $imageheight = 75;
                $image       = imagecreate($imagelength, $imageheight);
                $bgcolor     = imagecolorallocate($image, 255, 255, 255);
                $stringcolor = imagecolorallocate($image, 0, 0, 0);
                $filter      = new filters;
                $filter->signs($image, $this->getRandFont());
                for ($i = 0; $i < strlen($this->CaptchaString); $i++) {
                    imagettftext($image, 25, mt_rand(-15, 15), $i * 25 + 10,
                            mt_rand(30, 70),
                            $stringcolor,
                            $this->getRandFont(),
                            $this->CaptchaString{$i});
                }
                switch ($this->gfxformat ) {
                    case 'png' :
                        imagepng($image);
                        break;
                    case 'jpg' :
                        imagejpeg($image);
                        break;
                }
                imagedestroy($image);
            } else {
                // ImageMagick code originally written by
                // Thom Skrtich  (email : bisohpthom@supertwist.net)
                // used in SecureImage a CAPTCHA plugin for WordPress.
                $gravity = 'Center';
                # modify the image according to the generated settings
                $size =  rand($this->sizemin,  $this->sizemax);
                $blur =  rand($this->blurmin,  $this->blurmax);
                $angle = rand($this->anglemin, $this->anglemax);
                $swirl = rand($this->swirlmin, $this->swirlmax);
                $wave =  rand($this->wavemin,  $this->wavemax);

                $cString = $this->CaptchaString;
                $i = strlen($cString);
                $newString = '';
                for ($x=0; $x<$i;$x++) {
                    $newString .= $cString[$x];
                    $newString .= ' ';
                }

                # prepare our image magick command
                $cmd    = '"' . $this->convertpath . '"';
                $cmd .= ' -font "'.$this->getRandFont().'"';
                #$cmd .= ' -fill "'.$color.'"';
                $cmd .= ' -pointsize '.$size;
                $cmd .= ' -gravity "'.$gravity.'"';
                $cmd .= ' -annotate 0 "' . $newString . '"';
                $cmd .= ' -blur '.$blur;
                $cmd .= ' -rotate '.$angle;
                $cmd .= ' -swirl '.$swirl;
                $cmd .= ' -wave '.$wave.'x80';
                $cmd .= ' ' . $this->getRandBackground() . ' - ';

                if (PHP_OS == "WINNT") {
                    $pcmd = 'cmd /c " ' . $cmd . '"';
                } else {
                    $pcmd = $cmd;
                }
                if ($this->debug) {
                    COM_errorLog("CAPTCHA cmd: " . $pcmd);
                }
                passthru($pcmd);
            }
        }
    } //MakeCaptcha

    function getCaptchaString () {
        global $cString;

        if ( $this->driver == 2 ) { // static images
            return $cString[$this->CaptchaString];
        }
        return $this->CaptchaString;
    } //GetCaptchaString
} //class: captcha
?>