<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | cperror.class.php                                                        |
// |                                                                          |
// | CAPTCHA error processing                                                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Orignal Author of error.class.php                                        |
// |    Pascal Rehfeldt <Pascal@Pascal-Rehfeldt.com>                          |
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

namespace Captcha;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

class cperror
{

    var $errors;

    public function __construct()
    {
        $this->errors = array();

    } //error

    function addError ($errormsg)
    {
        $this->errors[] = $errormsg;

    } //addError

    function displayError ()
    {
        $iheight     = count($this->errors) * 20 + 10;
        $iheight     = ($iheight < 130) ? 130 : $iheight;

        $image       = imagecreate(600, $iheight);

        $errorsign   = imagecreatefromjpeg('./gfx/errorsign.jpg');
        imagecopy($image, $errorsign, 1, 1, 1, 1, 180, 120);

        $bgcolor     = imagecolorallocate($image, 255, 255, 255);

        $stringcolor = imagecolorallocate($image, 0, 0, 0);

        for ($i = 0; $i < count($this->errors); $i++)
        {
            $imx = ($i == 0) ? $i * 20 + 5 : $i * 20;

            $msg = 'Error[' . $i . ']: ' . $this->errors[$i];

            imagestring($image, 5, 190, $imx, $msg, $stringcolor);
        }

        imagepng($image);

        imagedestroy($image);

    } //displayError

    function isError ()
    {
        if (count($this->errors) == 0) {
            return FALSE;

        } else {
            return TRUE;
        }

    } //isError

} //class: error

?>