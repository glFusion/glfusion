<?php
/**
* glFusion CMS
*
* Log Exception
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

class LogException extends \Exception
{
    public function errorMessage() {
    //error message
    $errorMsg = 'Error on line '.__LINE__.' in '.__FILE__
    .': <b>'.$this->getMessage().'</b>';
    return $errorMsg;
    }
}

