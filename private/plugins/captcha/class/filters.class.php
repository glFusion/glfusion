<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | filter.class.php                                                         |
// |                                                                          |
// | Filters                                                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Orignal Author of filter.class.php                                       |
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

class filters
{

    function noise (&$image, $runs = 30)
    {
        $w = imagesx($image);
        $h = imagesy($image);

        for ($n = 0; $n < $runs; $n++) {
            for ($i = 1; $i <= $h; $i++) {
                $randcolor = imagecolorallocate($image,
                mt_rand(0, 255),
                mt_rand(0, 255),
                mt_rand(0, 255));

                imagesetpixel($image,
                mt_rand(1, $w),
                mt_rand(1, $h),
                $randcolor);
            }
        }
    } //noise

    function signs (&$image, $font, $cells = 3)
    {

        $w = imagesx($image);
        $h = imagesy($image);

        for ($i = 0; $i < $cells; $i++) {
            $centerX     = mt_rand(1, $w);
            $centerY     = mt_rand(1, $h);
            $amount      = mt_rand(9, 11);
            $stringcolor = imagecolorallocate($image, 100, 100, 100);

            for ($n = 0; $n < $amount; $n++) {

                $signs = range('A', 'Z');
                $sign  = $signs[mt_rand(0, count($signs) - 1)];

                imagettftext($image, 25,
                mt_rand(-15, 15),
                $centerX + mt_rand(-50, 50),
                $centerY + mt_rand(-50, 50),
                $stringcolor, $font, $sign);

            }
        }

    } //signs

    function blur (&$image, $radius = 3)
    {

        $radius  = round(max(0, min($radius, 50)) * 2);

        $w       = imagesx($image);
        $h       = imagesy($image);

        $imgBlur = imagecreate($w, $h);

        for ($i = 0; $i < $radius; $i++) {

            imagecopy     ($imgBlur, $image,   0, 0, 1, 1, $w - 1, $h - 1);
            imagecopymerge($imgBlur, $image,   1, 1, 0, 0, $w,     $h,     50.0000);
            imagecopymerge($imgBlur, $image,   0, 1, 1, 0, $w - 1, $h,     33.3333);
            imagecopymerge($imgBlur, $image,   1, 0, 0, 1, $w,     $h - 1, 25.0000);
            imagecopymerge($imgBlur, $image,   0, 0, 1, 0, $w - 1, $h,     33.3333);
            imagecopymerge($imgBlur, $image,   1, 0, 0, 0, $w,     $h,     25.0000);
            imagecopymerge($imgBlur, $image,   0, 0, 0, 1, $w,     $h - 1, 20.0000);
            imagecopymerge($imgBlur, $image,   0, 1, 0, 0, $w,     $h,     16.6667);
            imagecopymerge($imgBlur, $image,   0, 0, 0, 0, $w,     $h,     50.0000);
            imagecopy     ($image  , $imgBlur, 0, 0, 0, 0, $w,     $h);

        }

        imagedestroy($imgBlur);

    } //blur

} //class: filters

?>