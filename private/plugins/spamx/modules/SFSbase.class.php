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

require_once $_CONF['path'] . 'lib/http/http.php';

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

        $retval = false;
        $ip = $REMOTE_ADDR;

        if ( empty ($post) || $ip == '' ) {
            return $retval;
        }

        $arguments = array();
        $response = '';

        $http=new http_class;
        $http->timeout=0;
        $http->data_timeout=0;
        $http->debug=0;
        $http->html_debug=0;
        $http->user_agent = 'glFusion/' . GVERSION;
        $url="http://www.stopforumspam.com/api";
        $requestArgs = '?f=serial&';

        if ( $ip != '' ) {
            $requestArgs .= 'ip='.$ip.'&';
        }
        $requestArgs .= 'cmd=display';
        $url = $url . $requestArgs;
        $error = $http->GetRequestArguments($url,$arguments);
        $error=$http->Open($arguments);
        $error=$http->SendRequest($arguments);
        if ( $error == "" ) {
            $error=$http->ReadReplyBody($body,1024);
            if ( $error == "" || strlen($body) > 0 ) {
                $response = $response . $body;
                $result = @unserialize($response);

                if (!$result) return 0;     // invalid data, assume ok

                if (isset($result['ip']) && $result['ip']['appears'] == 1 && $result['ip']['confidence'] > (float) 25) {
                    $retval = true;
                    SPAMX_log ("SFS: spam detected");
                }
            }
        }
        return $retval;
    }
}

?>