<?php

/**
 * Basic Command Abstract class
 *
 * @author Tom Willett	tomw AT pigstye DOT net
 *
 * @package Spam-X
 * @subpackage Modules
 * @abstract
 *
 */

class BaseCommand {
    /**
     *
     * @access public
     */

    var $result = null; //Result of execute command
    var $num = 0; // Action Number

    var $response = '';

    /**
     * Constructor
     *
     * @access public
     */
    function __construct()
    {
    }

    function execute($comment,$data)
    {
        return 0;
    }

    function result()
    {
        global $result;

        return $result;
    }
    function number()
    {
        global $num;

        return $num;
    }

    function getResponse()
    {
        return $this->response;
    }
}

?>
