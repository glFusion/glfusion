<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | validate.php                                                             |
// |                                                                          |
// | Security token validation                                                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2011 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2002-2011 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
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

require_once 'lib-common.php';
USES_lib_user();

/**
* Re-send a request after successful re-validation
*
* Creates the GET and POST requests originally passed in the input.
*
*/
function TOKEN_resend_request()
{
    global $_CONF;

    require_once 'HTTP/Request.php';

    $method = '';
    if ( SESS_isSet('glfusion.auth.method') ) {
        $method = SESS_getVar('glfusion.auth.method');
        SESS_unSet('glfusion.auth.method');
    }
    $desturl = '';
    if ( SESS_isSet('glfusion.auth.dest') ) {
        $desturl = SESS_getVar('glfusion.auth.dest');
        if (substr($desturl, 0, strlen($_CONF['site_url'])) != $_CONF['site_url']) {
            $desturl = $_CONF['site_url'];
        }
        SESS_unSet('glfusion.auth.dest');
    }
    $postdata = '';
    if (SESS_isSet('glfusion.auth.post')) {
        $postdata = SESS_getVar('glfusion.auth.post');
        SESS_unSet('glfusion.auth.post');
    }
    $getdata = '';
    if (SESS_isSet('glfusion.auth.get')) {
        $getdata = SESS_getVar('glfusion.auth.get');
        SESS_unSet('glfusion.auth.get');
    }
    $filedata = '';
    if ( SESS_isSet('glfusion.auth.file')) {
        $filedata = SESS_getVar('glfusion.auth.file');
        SESS_unSet('glfusion.auth.file');
    }
    if (!empty($method) && !empty($desturl) && ((($method == 'POST') && !empty($postdata)) || (($method == 'GET') && !empty($getdata)))) {
        $req = new HTTP_Request($desturl);
        if ($method == 'POST') {
            $req->setMethod(HTTP_REQUEST_METHOD_POST);
            $data = unserialize($postdata);
            foreach ($data as $key => $value) {
                if ($key == CSRF_TOKEN) {
                    $req->addPostData($key, SEC_createToken());
                } else {
                    $req->addPostData($key, $value);
                }
            }
            $req->addPostData('glfusion_auth_file',$filedata);
        } else {
            $req->setMethod(HTTP_REQUEST_METHOD_GET);
            $data = unserialize($getdata);
            foreach ($data as $key => $value) {
                if ($key == CSRF_TOKEN) {
                    $req->addQueryString($key, SEC_createToken());
                } else {
                    $req->addQueryString($key, $value);
                }
            }
            $req->addPostData('glfusion_auth_file',$filedata);
        }
        $req->addHeader('User-Agent', 'glFusion/' . GVERSION);
        $req->addHeader('Referer', COM_getCurrentUrl());
        foreach ($_COOKIE as $cookie => $value) {
            $req->addCookie($cookie, $value);
        }

        $response = $req->sendRequest();

        if (PEAR::isError($response)) {
            if (! empty($files)) {
                SEC_cleanupFiles($files);
            }
            trigger_error("HTTP Request " . $method." failed: " . $response->getMessage());
        } else {
            $location = $req->getResponseHeader('location');
            if ( !empty($location) ) {
                echo COM_refresh($location);
                exit;
            }
            echo $req->getResponseBody();
            exit;
        }
    } else {
        if (! empty($files)) {
            SEC_cleanupFiles($files);
        }
        echo COM_refresh($_CONF['site_url'] . '/index.php');
    }
    exit;
}

if ( isset($_POST['type']) ) {
    $mode = $_POST['type'];
} elseif (isset($_GET['type']) ) {
    $mode = $_GET['type'];
} else {
    $mode = 'other';
}

$display = '';

switch ($mode) {
    case 'user' :
        $status = -2;
        COM_clearSpeedlimit($_CONF['login_speedlimit'], 'tokenexpired');
        if (COM_checkSpeedlimit('tokenexpired', $_CONF['login_attempts']) > 0) {
            echo COM_refresh($_CONF['site_url']);
            exit;
        }
        $loginname = '';
        if (isset ($_POST['loginname'])) {
            $loginname = $_POST['loginname'];
            if ( !USER_validateUsername($loginname) ) {
                $loginname = '';
            }
        }
        $passwd = '';
        if (isset ($_POST['passwd'])) {
            $passwd = $_POST['passwd'];
        }
        $uid = '';
        if (!empty($loginname) && !empty($passwd)) {
            COM_updateSpeedlimit('tokenexpired');
            $status = SEC_authenticate($loginname, $passwd, $uid);
        }
        if ( $status == USER_ACCOUNT_ACTIVE ) {
            TOKEN_resend_request();
        } else {
            $display .= COM_siteHeader();
            $display .= SEC_tokenreauthForm($LANG_ACCESS['validation_failed']);
            $display .= COM_siteFooter();
            echo $display;
            exit;
        }
        break;
    case 'other' :
        COM_clearSpeedlimit($_CONF['login_speedlimit'], 'tokenexpired');
        if (COM_checkSpeedlimit('tokenexpired', $_CONF['login_attempts']) > 0) {
            echo COM_refresh($_CONF['site_url']);
            exit;
        }
        if ( isset($_POST['captcha']) ) {
            $str = COM_applyFilter($_POST['captcha']);
        } else {
            $str = '';
        }
        COM_updateSpeedlimit('tokenexpired');
        list( $rc, $msg) = CAPTCHA_checkInput( $str, 'token' );
        if ( $rc == 1 ) {
            TOKEN_resend_request();
        }
        $display = COM_siteHeader();
        $display .= SEC_tokenreauthForm($LANG_ACCESS['validation_failed']);
        $display .= COM_siteFooter();
        echo $display;
        exit;
        break;
}
?>