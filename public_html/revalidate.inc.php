<?php
/**
* glFusion CMS
*
* glFusion CSRF Token Revalidation
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2011-2019 by the following authors:
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
    if (substr($desturl, 0, strlen($_CONF['site_url'])) != $_CONF['site_url']) {
        $desturl = $_CONF['site_url'];
    }
    SESS_unSet('glfusion.auth.dest');
}

$display = '';
switch ($mode) {
    case 'user' :
        $oldUserID = $_USER['uid'];
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
            $uid = DB_getItem($_TABLES['users'],'uid','username="'.DB_escapeString($loginname).'"');
            if ( $uid != $oldUserID) {
                $authenticated = 0;
            } else {
                $authenticated = 1;
            }
            SESS_completeLogin($uid,$authenticated);
            _rebuild_data();
            unset($_POST['loginname']);
            COM_clearSpeedlimit(0, 'tokenexpired');
        } else {
            $display .= COM_siteHeader();
            $display .= SEC_tokenreauthForm($LANG_ACCESS['validation_failed'],$desturl);
            $display .= COM_siteFooter();
            echo $display;
            exit;
        }
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
    global $_CONF;

    $method = '';
    if ( SESS_isSet('glfusion.auth.method') ) {
        $method = SESS_getVar('glfusion.auth.method');
        SESS_unSet('glfusion.auth.method');
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
        $file_array = unserialize($filedata);
    }
    $filedata = '';
    if (empty($_FILES) && isset($file_array) && is_array($file_array) ) {
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
    $_POST = array();
    $_GET  = array();
    $_SERVER['REQUEST_METHOD'] = $method;
    $_POST = unserialize($postdata);
    $_GET =  unserialize($getdata);

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