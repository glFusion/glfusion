<?php
/**
* glFusion CMS
*
* glFusion CSRF Token Revalidation
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2011-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs        tony@tonybibbs.com
*  Mark Limburg      mlimburg@users.sourceforge.net
*  Jason Whittenburg jwhitten@securitygeeks.com
*
*/

if (!defined ('GVERSION')) {
    die('This file can not be used on its own.');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;
use \glFusion\User\UserAuth;
use \glFusion\User\UserInterface;
use \Delight\Cookie\Session;


USES_lib_user();

if ( isset($_POST['type']) ) {
    $mode = $_POST['type'];
} elseif (isset($_GET['type']) ) {
    $mode = $_GET['type'];
} else {
    $mode = 'other';
}

$desturl = '';
if ( SESS_isSet('glfusion.auth.dest') ) {
    $desturl = SESS_getVar('glfusion.auth.dest');
    if (substr((string) $desturl, 0, strlen($_CONF['site_url'])) != $_CONF['site_url']) {
        $desturl = $_CONF['site_url'];
    }
    SESS_unSet('glfusion.auth.dest');
}

$display = '';

$userManager = $_USER['instance'];

switch ($mode) {
    case 'user' :

        try {
            if ($userManager->reconfirmPassword($_POST['passwd']) === false) {
                Log::write('system',Log::WARNING,'revalidate.inc.php :: password not valid.');
                $display .= COM_siteHeader('menu');
                $display .= SEC_tokenreauthForm($LANG_ACCESS['validation_failed'],$desturl);
                $display .= COM_siteFooter();
                echo $display;
                exit;
            }
        } catch (\glFusion\User\Exceptions\NotLoggedInException $e) {
            // user not logged in
            Log::write('system',Log::WARNING,'revalidate.inc.php :: password validation failed since user was not logged in');
            COM_setMsg($LANG20[6],'error');
            COM_refresh($_CONF['site_url']);
            exit;
        } catch (\glFusion\User\Exceptions\TooManyRequestsException $e) {
            Log::write('system',Log::WARNING,'auth.inc.php :: password validation hit the throttle mark');
            Session::delete('glfusion.auth.get');
            Session::delete('glfusion.auth.post');
            Session::delete('glfusion.auth.method');
            Session::delete('glfusion.auth.files');

            @header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            @header('Status: 403 Forbidden');
            $retval = COM_siteHeader('menu', $LANG12[26])
                    . COM_showMessageText($LANG04[112],$LANG12[26],false,'error')
                    . COM_siteFooter();
            echo $retval;
            exit;
        }
        _rebuild_data();

        break;
    case 'other' :
        COM_clearSpeedlimit($_CONF['login_speedlimit'], 'tokenexpired');
        if (COM_checkSpeedlimit('tokenexpired', 5) > 0) {
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
            _rebuild_data();
            unset($_POST['loginname']);
            COM_clearSpeedlimit(0, 'tokenexpired');
            return;
        }
        $display  = COM_siteHeader();
        $display .= SEC_tokenreauthForm($LANG_ACCESS['validation_failed'],$desturl);
        $display .= COM_siteFooter();
        echo $display;
        exit;
        break;
}

function _rebuild_data()
{
    global $_CONF, $_POST, $_GET, $_FILES;

    if (Session::has('glfusion.auth.post')) {
        $post = Session::take('glfusion.auth.post');
        $_POST = json_decode($post,true);
    }
    if (Session::has('glfusion.auth.get')) {
        $get = Session::take('glfusion.auth.get');
        $_GET = json_decode($get,true);
    }
    if (Session::has('aglfusion.auth.method')) {
        $_SERVER['REQUEST_METHOD'] = Session::take('glfusion.auth.method');
    }
    if (Session::has('glfusion.auth.file')) {
        $filedata = Session::take('glfusion.auth.file');
        $file_array = json_decode($filedata,true);
    }

    if (empty($_FILES) && (isset($file_array) && is_array($file_array) ) ) {
        foreach ($file_array as $fkey => $file) {
            if ( isset($file['name']) && is_array($file['name']) ) {
                foreach($file AS $key => $data) {
                    foreach ($data AS $offset => $value) {
                        if ( $key == 'tmp_name' ) {
                            $filename = COM_sanitizeFilename(basename($value), true);
                            $value = $_CONF['path_data'] . 'temp/'.$filename;
                            if ( $filename == '' ) {
                                $value = '';
                            }
                            $_FILES[$fkey]['_data_dir'][$offset] = true;
                        }
                        $_FILES[$fkey][$key][$offset] = $value;
                        if (!isset($_FILES[$fkey]['tmp_name']) || !isset($_FILES[$fkey]['tmp_name'][$offset]) || ! file_exists($_FILES[$fkey]['tmp_name'][$offset])) {
                            $_FILES[$fkey]['tmp_name'][$offset] = '';
                            $_FILES[$fkey]['error'][$offset] = 4;
                        }
                    }
                }
            } else {
                foreach($file AS $key => $value) {
                    if ($key == 'tmp_name' ) {
                        $filename = COM_sanitizeFilename(basename($value), true);
                        $value = $_CONF['path_data'] . 'temp/'.$filename;
                        if ( $filename == '' ) {
                            $value = '';
                        }
                        // set _data_dir attribute to key upload class to not use move_uploaded_file()
                        $_FILES[$fkey]['_data_dir'] = true;
                    }
                    $_FILES[$fkey][$key] = $value;
                }
                if (! file_exists($_FILES[$fkey]['tmp_name'])) {
                    $_FILES[$fkey]['tmp_name'] = '';
                    $_FILES[$fkey]['error'] = 4;
                }
            }
        }
    }

    if (!is_array($_POST)) $_POST = array();
    if (!is_array($_GET)) $_GET = array();

    // refresh the token (easier to create new one than try to fake referer)
    if ( @array_key_exists(CSRF_TOKEN, $_POST) || @array_key_exists(CSRF_TOKEN,$_GET) ) {
        $newToken = SEC_createToken();
        $_POST[CSRF_TOKEN] = $newToken;
        $_GET[CSRF_TOKEN] = $newToken;
    }
    if ( !isset($_GET) || !is_array($_GET) ) {
        $_GET = array();
    }
    if ( !isset($_POST) || !is_array($_POST) ) {
        $_POST = array();
    }
    $_REQUEST = array_merge($_GET, $_POST);

    return;
}

?>