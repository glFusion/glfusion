<?php
/**
* glFusion CMS
*
* LogHandle Class
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*
*/

namespace glFusion\Log;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

class LogHandle
{
    public $log;

    public $level;

    private $file;

}