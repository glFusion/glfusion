<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | message.class.php                                                        |
// |                                                                          |
// | glFusion Message Handler                                                 |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

/* ---------------------------------------------------------------------------
 * Proof of concept work, not production ready....
 * ---------------------------------------------------------------------------
 */

define('GENERAL',  1);
define('WARNING',  2);
define('ERROR',    3);

/**
 * messageHandler class
 *
 * @package 	glFusion.Framework
 * @subpackage	output
 * @since 		1.2.0
 */

class messageHandler {

    public  $generalMessageArray = array();
    public  $warningMessageArray = array();
    public  $errorMessageArray = array();


    /**************************************************************************/
    // Public Methods:

    /**
     * Constructor, initializes the output buffering.
     */

    public function __construct() {

    }

	/**
	 * Returns a reference to a global messageHandler object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>$messageHandle = messageHandler::getInstance();</pre>
	 *
	 * @static
	 * @return	object	The messageHandler object.
	 * @since	1.2.0
	 */
    function &getInstance()
    {
        static $instance;

        if (!$instance) {
            $instance = new messageHandler();
        }

        return $instance;
    }

    function addMessage( $class, $type, $message )
    {
        if ( $type == '' ) {
            $type = 'glFusion';
        }

        switch ( $class ) {
            case GENERAL :
                $this->generalMessageArray[$type] = $message;
                break;
            case WARNING :
                $this->warningMessageArray[$type] = $message;
                break;
            case ERROR :
                $this->errorMessageArray[$type] = $message;
                break;
        }
    }

    function errorMessageCount()
    {
        return count($this->errorMessageArray);
    }

    function getErrorMessages()
    {
        return $this->errorMessageArray;
    }

    function generalMessageCount()
    {
        return count($this->generalMessageArray);
    }

    function getGeneralMessages()
    {
        return $this->generalMessageArray;
    }

}
?>