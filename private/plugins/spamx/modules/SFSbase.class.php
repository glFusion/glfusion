<?php

/**
* File: SFSbase.class.php
* Stop Forum Spam (SFS) Base Class
*
* Copyright (C) 2011 by the following authors:
* Author        Mark R. Evans   mark AT glfusion DOT org
*
* Licensed under the GNU General Public License
*
* @package Spam-X
* @subpackage Modules
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

/**
* Sends posts to SFS (http://www.stopforumspam.com) for examination
*
* @author Mark R. Evans     mark AT glfusion DOT org
* based on the works of Tom Willet (Spam-X) and Lee Garner (SFS)
* @package Spam-X
*
*/
class SFSbase {

    var $_debug = false;

    var $_verbose = false;

    /**
    * Constructor
    */
    function __construct()
    {
        $this->_debug = false;
        $this->_verbose = false;
    }

    /**
    * Check for spam links
    *
    * @param    string  $post   post to check for spam
    * @return   boolean         true = spam found, false = no spam
    *
    * Note: Also returns 'false' in case of problems communicating with SFS.
    *       Error messages are logged in glFusion's error.log
    *
    */
    function CheckForSpam ($post)
    {
        global $_SPX_CONF, $REMOTE_ADDR;

        require_once 'HTTP/Request2.php';
        $retval = false;
        $ip = $REMOTE_ADDR;

        if ( empty ($post) || $ip == '' ) {
            return $retval;
        }

        $request = new HTTP_Request2('http://www.stopforumspam.com/api',
                                     HTTP_Request2::METHOD_GET, array('use_brackets' => true));
        $url = $request->getUrl();

        $checkData['f'] = 'serial';
        if ( $ip != '' ) {
            $checkData['ip'] = $ip;
        }
        $url->setQueryVariables($checkData);
        $url->setQueryVariable('cmd', 'display');

        try {
            $response = $request->send();
        } catch (Exception $e) {
            return 0;
        }
        $result = @unserialize($response->getBody());

        if (!$result) return false;     // invalid data, assume ok
        if ($result['ip']['appears'] == 1 && $result['ip']['confidence'] > (float) 25) {
            $retval = true;
            SPAMX_log ("SFS: spam detected");
        } else if ($this->_verbose) {
            SPAMX_log ("SFS: no spam detected");
        }

        return $retval;
    }
}

?>