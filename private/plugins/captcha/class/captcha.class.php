<?php
/**
* glFusion CMS - CAPTCHA Plugin
*
* CAPTCHA Class
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Orignal Author of captcha.class.php
*    Pascal Rehfeldt <Pascal@Pascal-Rehfeldt.com>
*
*/

namespace Captcha;

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

global $_CONF, $_CP_CONF;

class captcha {
    var $Length;
    var $CaptchaString = NULL;
    var $first;
    var $second;
    var $operator;
    var $fontpath;
    var $bgpath;
    var $fonts;
    var $backgrounds;

    var $driver;
    var $gfxformat;
    var $debug;
    var $session_id = '';
    var $publickey;

    public function __construct($csid, $length = 6)
    {
        global $_CONF, $_CP_CONF;

        $this->driver       = $_CP_CONF['gfxDriver'];
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
        $this->publickey    = $_CP_CONF['publickey'];

        $this->Length   = $length;

        $this->fontpath = $_CONF['path'] . 'plugins/captcha/images/fonts/';
        $this->bgpath   = $_CONF['path'] . 'plugins/captcha/images/backgrounds/';

        $this->fonts       = $this->getFonts();
        $this->backgrounds = $this->getBackGrounds();

        $errormgr       = new cperror;

        if ($this->fonts == FALSE) {
            $errormgr->addError('No fonts available!');
            $errormgr->displayError();
            die();
        }

        if (function_exists('imagettftext') == FALSE && ( $this->driver == 0 || $this->driver == 6) ) {
            $errormgr->addError('GD Library imagetftext function not available');
            $errormgr->displayError();
            die();
        }

        $this->stringGen();
        $this->makeCaptcha();
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

        if ($this->driver == 0 ) {
            $CharPool = "23456789ABCDEFGHJKMNPQRSTUVWXYZ";
            $PoolLength = strlen($CharPool) - 1;

            for ($i = 0; $i < $this->Length; $i++) {
                $this->CaptchaString .= $CharPool[mt_rand(0, $PoolLength)];
            }
        } else if ( $this->driver == 6 ) {
            $operator_pool = array('+','-');
            $first_number_pool = array(40,39,38,37,36,35,34,33,32,31,30,29,28,27,26,25,24,23,22,21,20,19,18,17,16,15);
            $second_number_pool = array(1,2,3,4,5,6,8,9,10,11,12,13,14,15);
            $this->operator = $operator_pool[mt_rand(0,count($operator_pool) - 1)];
            $this->first = $first_number_pool[mt_rand(0, count($first_number_pool) - 1)];
            $this->second = $second_number_pool[mt_rand(0, count($second_number_pool) - 1)];
            switch ($this->operator) {
                case '+' :
                    $this->CaptchaString = ((int)$this->first + (int)$this->second);
                    break;
                case '-' :
                    $this->CaptchaString = ((int)$this->first - (int)$this->second);
                    break;
            }
        }
    }

    function makeCaptcha () {
        global $cString, $_CONF, $_TABLES, $LANG_CP00;

        if ( $this->session_id != "" ) {
            $sql = "UPDATE {$_TABLES['cp_sessions']} SET validation='" . $this->getCaptchaString() . "' WHERE session_id='" . DB_escapeString($this->session_id) . "'";
            DB_query($sql);
        } else {
           CAPTCHA_errorLog("CAPTCHA: No valid session id passed");
           exit;
        }

        switch ($this->driver) {
            case 0 :
                if ( $this->gfxformat != 'png' && $this->gfxformat != 'jpg') {
                    header('Content-type: image/gif');
                    Log::write('system',Log::ERROR,'CAPTCHA: No valid gfxFormat specified');
                    $errormgr = new cperror;
                    $errormgr->addError('');
                    $errormgr->displayError();
                    die();
                }

                $header = 'Content-type: image/' . $this->gfxformat;
                header($header);

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
                            $this->CaptchaString[$i]);
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
                break;

            case 1 :
            case 2 :
            case 3 :
            case 4 :
            case 5 :
                break;
            case 6 :
/*
                $output =  $this->first . ' '.$this->operator . ' ' . $this->second . ' = ';
                $imagelength = $this->Length * 25 + 16;
                $imageheight = 75;
                $im = imagecreate($imagelength, $imageheight);
                // White background and blue text
                $bg = imagecolorallocate($im, 255, 255, 255);
                $textcolor = imagecolorallocate($im, 0, 0, 255);
                // Write the string at the top left
                imagestring($im, 5, 35, 15, $output, $textcolor);
                imagestring($im, 25, 5, 45, $LANG_CP00['captcha_help'], $textcolor);
                // Output the image
                header('Content-type: image/png');
                imagepng($im);
                imagedestroy($im);
*/
                $output =  $this->first . ''.$this->operator . '' . $this->second . '=';
                $font = $this->fontpath . 'bluehigl.ttf';
                $imagelength = $this->Length * 25 + 16;
                $imageheight = 75;
                $image       = imagecreate($imagelength, $imageheight);
                $bgcolor     = imagecolorallocate($image, 255, 255, 255);
                $stringcolor = imagecolorallocate($image, 0, 0, 0);
                for ($i = 0; $i < strlen($output); $i++) {
                    imagettftext($image, 25, mt_rand(-15, 15), $i * 25 + 10,
                            45,
                            $stringcolor,
                            $font,
                            $output[$i]);
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

                break;
            default :
                break;
        }

    } //MakeCaptcha

    function getCaptchaString () {
        global $cString;

        return $this->CaptchaString;
    } //GetCaptchaString
} //class: captcha
?>
