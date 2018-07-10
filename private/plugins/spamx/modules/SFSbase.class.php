<?php

/**
* File: SFSbase.class.php
* Stop Forum Spam (SFS) Base Class
*
* Copyright (C) 2011-2018 by the following authors:
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

    var $response = '';

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
    function CheckForSpam ($post,$data)
    {
        global $_SPX_CONF, $REMOTE_ADDR;

        $retval = false;

        if ( !isset($_SPX_CONF['sfs_username_check']) ) $_SPX_CONF['sfs_username_check'] = false;
        if ( !isset($_SPX_CONF['sfs_email_check']) ) $_SPX_CONF['sfs_email_check'] = true;
        if ( !isset($_SPX_CONF['sfs_ip_check']) ) $_SPX_CONF['sfs_ip_check'] = true;

        $username = '';
        $email    = '';
        $ip       = '';
        $type     = '';

        if (isset($data['username']) && $_SPX_CONF['sfs_username_check'] == true ) {
            $username = $data['username'];
        }
        if ( isset($data['email']) && $_SPX_CONF['sfs_email_check'] == true ) {
            $email = $data['email'];
        }
        if ( isset($data['ip']) && $_SPX_CONF['sfs_ip_check'] == true) {
            $ip = $data['ip'];
        }

        if ( isset($data['type']) ) {
            $type = $data['type'];
        } else {
            $type = 'comment';
        }

        if ( $ip == '' ) $ip = $REMOTE_ADDR;

        if ( $post == '' && $username == '' && $email =='' && $ip == '' ) return $retval;

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
        if ( $email != '' ) {
            $requestArgs .= 'email='.urlencode($email).'&';
        }
        if ( $username != '' ) {
            $requestArgs .= 'username='.urlencode($username).'&';
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
                $this->response = $result;
                if (!$result) {
                    return $retval;     // invalid data, assume ok
                }
                if ( isset($result['ip']) && $result['ip']['appears'] == 1 ) {
                    if ( (float) $result['ip']['confidence'] > (float) $_SPX_CONF['sfs_ip_confidence']) {
                        $retval = true;
                        SPAMX_log ("SFS: spam detected on " . $type);
                        SPAMX_log("SFS: found match on IP (".$ip."), confidence level was " . $result['ip']['confidence']);
                    } else if ( isset($_SPX_CONF['debug']) && $_SPX_CONF['debug'] == 1 ) {
                        SPAMX_log("SFS: " . $type . "found match on IP (".$ip."), confidence level was " . $result['ip']['confidence'] . " which is below the configured threshold of " . $_SPX_CONF['sfs_ip_confidence']);
                    }
                }
                if ( isset($result['email']) && $result['email']['appears'] == 1 ) {
                    if ( (float) $result['email']['confidence'] > (float) $_SPX_CONF['sfs_email_confidence']) {
                        $retval = true;
                        SPAMX_log ("SFS: spam detected on " . $type);
                        SPAMX_log("SFS: found match on email (".$email."), confidence level was " . $result['email']['confidence']);
                    } else if ( isset($_SPX_CONF['debug']) && $_SPX_CONF['debug'] == 1 ) {
                        SPAMX_log("SFS: " . $type . " found match on email (".$email."), confidence level was " . $result['ip']['confidence'] . " which is below the configured threshold of " . $_SPX_CONF['sfs_email_confidence']);
                    }
                }
                if ( isset($result['username']) && $result['username']['appears'] == 1 ) {
                    if ( (float) $result['username']['confidence'] > (float) $_SPX_CONF['sfs_username_confidence']) {
                        $retval = true;
                        SPAMX_log ("SFS: spam detected on " . $type);
                        SPAMX_log("SFS: found match on username (".$username."), confidence level was " . $result['username']['confidence']);
                    } else if ( isset($_SPX_CONF['debug']) && $_SPX_CONF['debug'] == 1 ) {
                        SPAMX_log("SFS: ". $type . " found match on username (".$username."), confidence level was " . $result['username']['confidence'] . " which is below the configured threshold of " . $_SPX_CONF['sfs_username_confidence']);
                    }
                }
            }
        }
        return $retval;
    }
    public function getResponse()
    {
        return $this->response;
    }
}

?>