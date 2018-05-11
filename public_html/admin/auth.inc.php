<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | auth.inc.php                                                             |
// |                                                                          |
// | glFusion admin authentication module                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2011-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
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
    die('This file can not be used on its own.');
}

USES_lib_user();

/* --- Main Processing Loop --- */

$display    = '';
$uid        = '';
$status     = '';
$token      = '';
$message    = '';

if ( !isset($_SYSTEM['admin_session']) ) {
    $_SYSTEM['admin_session'] = 1200;
}

// determine the destination of this request
$destination = COM_getCurrentURL();

// validate the destination is not blank and is part of our site...
if ( $destination == '' ) {
    $destination = $_CONF['site_admin_url'] . '/index.php';
}
if ( substr($destination, 0,strlen($_CONF['site_url'])) != $_CONF['site_url']) {
    $destination = $_CONF['site_admin_url'] . '/index.php';
}

if ( !COM_isAnonUser() ) {
    $currentUID = $_USER['uid'];
} else {
    $currentUID = 1;
}
// is user sending credentials?
if ( isset($_POST['loginname']) && !empty($_POST['loginname']) && isset($_POST['passwd']) && !empty($_POST['passwd']) ) {
    COM_updateSpeedlimit('login');
    $loginname = $_POST['loginname'];
    if ( !USER_validateUsername($loginname,1) ) {
        $status = '';
        $message = $LANG20[2];
    } else {
        $passwd = $_POST['passwd'];
        if ($_CONF['user_login_method']['3rdparty'] &&
            isset($_POST['service']) && !empty($_POST['service'])) {
            /* Distributed Authentication */
            $service = $_POST['service'];
            // safety check to ensure this user is really a known remote user
            $sql = "SELECT uid
                    FROM {$_TABLES['users']}
                    WHERE remoteusername='". DB_escapeString($loginname)."'
                    AND remoteservice='". DB_escapeString($service)."'";
            $result = DB_query($sql);
            if ( DB_numRows($result) != 1 ) {
                $status = -1;
            } else {
                $status = SEC_remoteAuthentication($loginname, $passwd, $service, $uid);
            }
        } else {
            $status = SEC_authenticate($loginname, $passwd, $uid);
        }
        if ( $status != USER_ACCOUNT_ACTIVE ) {
            $message = $LANG20[2];
        }
    }
}
if ($status == USER_ACCOUNT_ACTIVE) {
    SESS_completeLogin($uid);
    $_GROUPS = SEC_getUserGroups( $_USER['uid'] );
    if (!SEC_isModerator() && !SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit','OR')
             && (count(PLG_getAdminOptions()) == 0)) {
        COM_clearSpeedlimit($_CONF['login_speedlimit'], 'login');
        if (COM_checkSpeedlimit('login', $_CONF['login_attempts']) > 0) {
            if ( isset($_POST['token_filedata']) ) {
                $filedata = urldecode($_POST['token_filedata']);
                SEC_cleanupFiles($filedata);
            }
            $display = COM_siteHeader('menu', $LANG12[26])
                    . COM_startBlock($LANG12[26], '')
                    . $LANG04[112]
                    . COM_endBlock()
                    . COM_siteFooter();
            echo $display;
            exit;
        }
        $method = '';
        if (isset($_POST['token_requestmethod'])) {
            $method = COM_applyFilter($_POST['token_requestmethod']);
        }
        $postdata = '';
        if (isset($_POST['token_postdata'])) {
            $postdata = urldecode($_POST['token_postdata']);
        }
        $getdata = '';
        if (isset($_POST['token_getdata'])) {
            $getdata = urldecode($_POST['token_getdata']);
        }
        $filedata = '';
        if ( isset($_POST['token_filedata']) ) {
            $filedata = urldecode($_POST['token_filedata']);
        }
        $display  = COM_siteHeader('menu');
        $display .= SEC_reauthform($destination,$LANG20[9],$method,$postdata,$getdata,$filedata);
        $display .= COM_siteFooter();
        echo $display;
        exit;
    }
    COM_resetSpeedlimit('login', $_SERVER['REAL_ADDR']);
    if ( $_SYSTEM['admin_session'] != 0 ) {
        $token = SEC_createTokenGeneral('administration',$_SYSTEM['admin_session']);
        SEC_setCookie('token',$token,0,$_CONF['cookie_path'],$_CONF['cookiedomain'],$_CONF['cookiesecure'],true);
    }
    if ( $currentUID != $_USER['uid'] ) {
        // remove tokens for previous user
        if ( $currentUID > 1 ) {
            DB_delete($_TABLES['tokens'],'owner_id',(int)$currentUID);
        }
        echo COM_refresh($destination);
        exit;
    }

    $method = '';
    if (isset($_POST['token_requestmethod'])) {
        $method = COM_applyFilter($_POST['token_requestmethod']);
    }
    $postdata = '';
    if (isset($_POST['token_postdata'])) {
        $postdata = urldecode($_POST['token_postdata']);
    }
    $getdata = '';
    if (isset($_POST['token_getdata'])) {
        $getdata = urldecode($_POST['token_getdata']);
    }
    $filedata = '';
    if ( isset($_POST['token_filedata']) ) {
        $filedata = urldecode($_POST['token_filedata']);
        $file_array = unserialize($filedata);
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
    $_POST = array();
    $_GET  = array();
    $_SERVER['REQUEST_METHOD'] = $method;
    $_POST = unserialize($postdata);
    $_GET =  unserialize($getdata);
    if (!is_array($_POST) ) {
        $_POST = array();
    }
    if (!is_array($_GET) ) {
        $_GET = array();
    }
    // refresh the token (easier to create new one than try to fake referer)
    if ( @array_key_exists(CSRF_TOKEN, $_POST) || @array_key_exists(CSRF_TOKEN,$_GET) ) {
        $newToken = SEC_createToken();
        $_POST[CSRF_TOKEN] = $newToken;
        $_GET[CSRF_TOKEN] = $newToken;
    }
    $_REQUEST = array_merge($_GET, $_POST);

  // we have a logged in user - make sure they have permissions to be here...
} else if (!SEC_isModerator() && !SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit','OR')
         && (count(PLG_getAdminOptions()) == 0)) {
    COM_clearSpeedlimit($_CONF['login_speedlimit'], 'login');
    if (COM_checkSpeedlimit('login', $_CONF['login_attempts']) > 0) {
        if ( isset($_POST['token_filedata']) ) {
            $filedata = urldecode($_POST['token_filedata']);
            SEC_cleanupFiles($filedata);
        }
        $retval = COM_siteHeader('menu', $LANG12[26])
                . COM_startBlock($LANG12[26], '')
                . $LANG04[112]
                . COM_endBlock()
                . COM_siteFooter();
        echo $retval;
        exit;
    }

    $method = '';
    if (isset($_POST['token_requestmethod'])) {
        $method = COM_applyFilter($_POST['token_requestmethod']);
    }
    $postdata = '';
    if (isset($_POST['token_postdata'])) {
        $postdata = urldecode($_POST['token_postdata']);
    }
    $getdata = '';
    if (isset($_POST['token_getdata'])) {
        $getdata = urldecode($_POST['token_getdata']);
    }
    $filedata = '';
    if ( isset($_POST['token_filedata']) ) {
        $filedata = urldecode($_POST['token_filedata']);
    }
    $display .= COM_siteHeader('menu');
    $options = array(
        'title'   => $LANG_LOGIN[1],
        'message' => $LANG20[9]
    );
    $display .=  SEC_loginForm($options);
    $display .= COM_siteFooter();
    echo $display;
    exit;
} else {
    if ( isset($_COOKIE['token']) ) {
        $token = COM_applyFilter($_COOKIE['token']);
        if ( $message == '' )
            $message = $LANG20[8];
    } else {
        if ($message == '' )
            $message = $LANG20[9];
        $token = '';
    }
}
if ( $_SYSTEM['admin_session'] != 0 ) {
    // validate admin token
    if ( !SEC_checkTokenGeneral($token,'administration') ) {

        $method = '';
        if (isset($_POST['token_requestmethod'])) {
            $method = COM_applyFilter($_POST['token_requestmethod']);
        } else {
            $method   = strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' ? 'GET' : 'POST';
        }
        $postdata = '';
        if (isset($_POST['token_postdata'])) {
            $postdata = urldecode($_POST['token_postdata']);
        } else {
            $postdata = serialize($_POST);
        }
        $getdata = '';
        if (isset($_POST['token_getdata'])) {
            $getdata = urldecode($_POST['token_getdata']);
        } else {
            $getdata  = serialize($_GET);
        }
        $filedata = '';
        if ( isset($_POST['token_filedata']) ) {
            $filedata = urldecode($_POST['token_filedata']);
        }

        if (! empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if ( is_array($file['name']) ) {
                    foreach ($file['name'] as $offset => $filename) {
                        if ( !empty($file['name'][$offset]) ) {
                            $filename = basename($file['tmp_name'][$offset]);
                            move_uploaded_file($file['tmp_name'][$offset],$_CONF['path_data'] . 'temp/'. $filename);
                            $_FILES[$key]['tmp_name'][$offset] = $filename;
                        }
                    }
                } else {
                    if (! empty($file['name']) && !empty($file['tmp_name'])) {
                        $filename = basename($file['tmp_name']);
                        move_uploaded_file($file['tmp_name'],$_CONF['path_data'] . 'temp/'. $filename);
                        $_FILES[$key]['tmp_name'] = $filename;
                    }
                }
            }
            $filedata = serialize($_FILES);
        }
        COM_clearSpeedlimit($_CONF['login_speedlimit'], 'login');
        if (COM_checkSpeedlimit('login', $_CONF['login_attempts']) > 0) {

            SEC_cleanupFiles($filedata);

            $retval = COM_siteHeader('menu', $LANG12[26])
                    . COM_startBlock($LANG12[26], '')
                    . $LANG04[112]
                    . COM_endBlock()
                    . COM_siteFooter();
            echo $retval;
            exit;
        }
        $username = isset($_USER['username']) ? $_USER['username'] : '';

        $display .= COM_siteHeader();
        $display .= SEC_reauthform($destination,$message,$method,$postdata,$getdata,$filedata);
        $display .= COM_siteFooter();
        echo $display;
        exit;
    } else {
        // re-init the token...
        if ( $token != '' ) {
            DB_query("UPDATE {$_TABLES['tokens']} SET created=NOW() WHERE token='".DB_escapeString($token)."'");
        }
    }
}
if ( $_CONF['allow_user_themes'] == 0 ) {
    $_USER['theme'] = $_CONF['theme'];
}
?>