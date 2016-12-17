<?php
/**
 * glFusion Ajax Handler
 *
 * Ajax Handler
 *
 * LICENSE: This program is free software; you can redistribute it
 *  and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @category   glFusion CMS
 * @package    Ajax
 * @author     Mark R. Evans  mark AT glFusion DOT org
 * @copyright  2015-2016 - Mark R. Evans
 * @license    http://opensource.org/licenses/gpl-2.0.php - GNU Public License v2 or later
 * @since      File available since Release 1.6.3
 */

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

class ajaxHandler
{
    private $errorCode = 0;
    private $msg = '';
    private $attributes = array();
    protected $response;

    public function __construct()
    {
        $this->attributes = array();
        if ( !$this->isAjax() ) die();
    }

    public function isAjax() {
    	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    public function setErrorCode( $errorCode )
    {
        $this->errorCode = $errorCode;
    }

    public function setMessage ( $msg )
    {
        $this->msg = $msg;
    }

    public function setResponse( $item, $value )
    {
        $this->attributes[$item] = $value;
    }

    public function clearResponse()
    {
        $this->attributes = array();
    }

    public function sendResponse()
    {
        $retval['errorCode'] = $this->errorCode;
        foreach ( $this->attributes AS $name => $value) {
            $retval[$name] = $value;
        }
        $retval['message'] = $this->msg;
        $return["js"] = json_encode($retval);
        echo json_encode($return);
        exit;
    }
}