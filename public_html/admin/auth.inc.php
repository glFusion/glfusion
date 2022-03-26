<?php
/**
* glFusion CMS
*
* glFusion admin authentication module
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
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

/* --- Main Processing Loop --- */

$display    = '';
$uid        = '';
$status     = '';
$token      = '';
$message    = '';

$db = Database::getInstance();

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

$userManager = new UserAuth();

if ($userManager->isLoggedIn() === false) {
    Log::write('system',Log::WARNING,'auth.inc.php :: user not logged in');
    // punt - we are not logged in...
    COM_setMsg($LANG20[6],'error');
    COM_refresh($_CONF['site_url'].'/users.php');
}

if (!SEC_isModerator() && !SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit,search.admin,actions.admin,autotag.admin,cache.admin,database.admin,env.admin,logo.admin,menu.admin,social.admin,upgrade.admin','OR')
             && (count(PLG_getAdminOptions()) == 0)) {
    // user is not an admin
    Log::write('system',Log::WARNING,'auth.inc.php :: user not an admin user');
    COM_setMsg($LANG20[6],'error');
    COM_refresh($_CONF['site_url']);
}

if (isset($_POST['passwd'])) {
    // we are sending our new password
    try {
        if ($userManager->reconfirmPassword($_POST['passwd']) === false) {
            Log::write('system',Log::WARNING,'auth.inc.php :: password not valid.');
            COM_setMsg($LANG20[3],'error');
            // rinse and repeat
            COM_refresh($destination);
        }
    } catch (\glFusion\User\Exceptions\NotLoggedInException $e) {
        // user not logged in
        Log::write('system',Log::WARNING,'auth.inc.php :: password validation failed since user was not logged in');
        COM_setMsg($LANG20[6],'error');
        COM_refresh($_CONF['site_url']);
        exit;
    } catch (\glFusion\User\Exceptions\TooManyRequestsException $e) {
        if (Session::has('admin.files')) {
            $filedata = json_decode(Session::take('admin.files'),true);
            SEC_cleanupFiles($filedata);
            Session::delete('admin.get');
            Session::delete('admin.post');
            Session::delete('admin.method');
        }
        Log::write('system',Log::WARNING,'auth.inc.php :: password validation hit the throttle mark');
        COM_setMsg('Administrative access has been locked out for '. $e->getCode() . ' seconds - please try again later','error');
        @header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
        @header('Status: 403 Forbidden');
        $retval = COM_siteHeader('menu', $LANG12[26])
                . COM_showMessageText($LANG04[112],$LANG12[26],false,'error')
                . COM_siteFooter();
        echo $retval;
        exit;
    }
    // all good - so let it go forth...need to rebuild the environment
    if (Session::has('admin.post')) {
        $post = Session::take('admin.post');
        $_POST = json_decode($post,true);
    }
    if (Session::has('admin.get')) {
        $get = Session::take('admin.get');
        $_GET = json_decode($get,true);
    }
    if (Session::has('admin.method')) {
        $_SERVER['REQUEST_METHOD'] = Session::take('admin.method');
    }
    if (Session::has('admin.files')) {
        $filedata = Session::take('admin.files');
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
    Session::set($userManager::SESSION_FIELD_ADMIN_SESSION, \time() + $_SYSTEM['admin_session']);
}

if ( $_SYSTEM['admin_session'] != 0 || $userManager->isRemembered() ) {
    // we require a session or we need a password since our primary session is 'remembered'

    // need to check the admin session to see if the time is still good
    $adminSession = 0;
    if (Session::has($userManager::SESSION_FIELD_ADMIN_SESSION)) {
        $adminSession = Session::get($userManager::SESSION_FIELD_ADMIN_SESSION);
    }
    if ((($adminSession + $_SYSTEM['admin_session']) < \time()) || $userManager->isRemembered() ) {

        if ($adminSession + $_SYSTEM['admin_session'] < \time()) Log::write('system',Log::DEBUG,'Admin session expired');
        if ($userManager->isRemembered()) Log::write('system',Log::DEBUG,'User logged in via remember me - require password authentication.');

        try {
            $userManager->throttle([ 'reconfirmPassword', $userManager->getIpAddress() ], 3, (60 * 60), 4, true);
        } catch (\glFusion\User\Exceptions\TooManyRequestsException $e) {
            if (Session::has('admin.files')) {
                $filedata = json_decode(Session::get('admin.files'),true);
                SEC_cleanupFiles($filedata);
                Session::delete('admin.get');
                Session::delete('admin.post');
                Session::delete('admin.method');
            }
            COM_setMsg('Administrative access has been locked out for '. $e->getCode() . ' seconds - please try again later','error');
            COM_refresh($_CONF['site_url']);
        }
        // save everything so we can rebuild after getting the correct password
        if (isset($_POST) && count($_POST) > 0) {
            Session::set('admin.post', json_encode($_POST));
        }
        if (isset($_GET) && count($_GET) > 0) {
            Session::set('admin.get',json_encode($_GET));
        }
        if (isset($_SERVER['REQUEST_METHOD'])) {
            Session::set('admin.method', $_SERVER['REQUEST_METHOD']);
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
            Session::set('admin.files',json_encode($_FILES));
        }
        Log::write('system',Log::DEBUG,'Calling UserInterface::reauthForm()');
        // build the reauth form
        $display .= COM_siteHeader();
        $display .= UserInterface::reauthForm($destination,$message, $LANG04[111]);
        $display .= COM_siteFooter();
        echo $display;
        exit;
    }
}
