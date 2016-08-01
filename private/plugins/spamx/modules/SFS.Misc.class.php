<?php
/**
*   SFS.Misc.class.php
*   Special examiner to check email and IP addresses during registration.
*   Checks stopforumspam.com and, if the result is positive, addes the
*   email and/or IP address to the spamx table.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2010 Lee Garner <lee@leegarner.com>
*   @package    spamx
*   @subpackage Modules
*   @version    1.0.0
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

require_once $_CONF['path'] . 'lib/http/http.php';

/**
* Include Abstract Examine Class
*/
require_once $_CONF['path'] . 'plugins/spamx/modules/' . 'BaseCommand.class.php';

/**
* Examines Comment according to Personal IP Blacklist
*
* @author Tom Willett, tomw AT pigstye DOT net
*
* @package Spam-X
*
*/
class SFSreg extends BaseCommand {
    /**
     * No Constructor - use BaseCommand constructor
     */

    /**
     * The execute method examines the IP address a comment is coming from,
     * comparing it against a blacklist of banned IP addresses.
     *
     * @param   string  $comment    Comment text to examine
     * @return  int                 0: no spam, else: spam detected
     */
    function execute($type, $email = '', $ip = '', $username = '')
    {
        global $result;

        $result = $this->_process($type, $email, $ip, $username);
        return $result;
    }


    /**
     * Private internal method, this actually processes a given ip
     * address against a blacklist of IP regular expressions.
     *
     * @param   strint  $ip     IP address of comment poster
     * @return  int             0: no spam, else: spam detected
     * @access  private
     */
    function _process($type, $email = '', $ip = '', $username = '')
    {
        global $_TABLES, $_SPX_CONF, $LANG_SX00;

        if ( !isset($_SPX_CONF['sfs_username_confidence']) ) $_SPX_CONF['sfs_username_confidence'] = (float) 99.00;
        if ( !isset($_SPX_CONF['sfs_email_confidence']) ) $_SPX_CONF['sfs_email_confidence'] = (float) 50.00;
        if ( !isset($_SPX_CONF['sfs_ip_confidence']) ) $_SPX_CONF['sfs_ip_confidence'] = (float) 25.00;

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
            if ( $error != "" || strlen($body) == 0 )
                return 0;
            $response = $response . $body;
            $result = @unserialize($response);
            if (!$result) return 0;     // invalid data, assume ok

            if ( isset($result['ip']) && $result['ip']['appears'] == 1 ) {
                if ( $result['ip']['confidence'] > (float) $_SPX_CONF['sfs_ip_confidence']) {
                    SPAMX_log ($type . ' - Found ' . $type . ' matching ' . 'Stop Forum Spam (SFS)'.
                        'for IP '  . $ip . ' with confidence level of ' . $result['ip']['confidence'] .
                        $LANG_SX00['foundspam3'] . $_SERVER['REMOTE_ADDR']);
                    return 1;
                } else {
                    COM_errorLog("Spamx: SFS found match on IP, but confidence level was only " . $result['ip']['confidence']);
                }
            }
            if ( isset($result['email']) && $result['email']['appears'] == 1 ) {
                if ( $result['email']['confidence'] > (float) $_SPX_CONF['sfs_email_confidence']) {
                    SPAMX_log ($type . ' - Found ' . $type . ' matching ' . 'Stop Forum Spam (SFS)'.
                        'for email '  . $email . ' with confidence level of ' . $result['email']['confidence'] .
                        $LANG_SX00['foundspam3'] . $_SERVER['REMOTE_ADDR']);
                    return 1;
                }
            }
            if ( isset($result['username']) && $result['username']['appears'] == 1 ) {
                if ( $result['username']['confidence'] > (float) $_SPX_CONF['sfs_username_confidence']) {
                    SPAMX_log ($type . ' - Found ' . $type . ' matching ' . 'Stop Forum Spam (SFS)'.
                        'for username '  . $username . ' with confidence level of ' . $result['username']['confidence'] .
                        $LANG_SX00['foundspam3'] . $_SERVER['REMOTE_ADDR']);
                    return 1;
                }
            }
            // Passed the checks
            return 0;
        }
        return 0;
    }
}

?>