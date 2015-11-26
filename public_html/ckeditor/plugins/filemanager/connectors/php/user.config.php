<?php
/**
 *	Filemanager PHP connector
 *  This file should at least declare auth() function
 *  and instantiate the Filemanager as '$fm'
 *
 *  IMPORTANT : by default Read and Write access is granted to everyone
 *  Copy/paste this file to 'user.config.php' file to implement your own auth() function
 *  to grant access to wanted users only
 *
 *	filemanager.php
 *	use for ckeditor filemanager
 *
 *	@license	MIT License
 *  @author		Simon Georget <simon (at) linea21 (dot) com>
 *	@copyright	Authors
 */

/**
 *	Check if user is authorized
 *
 *
 *	@return boolean true if access granted, false if no access
 */
require_once '../../../../../lib-common.php';

/**
 *	Check if user is authorized
 *
 *
 *	@return boolean true if access granted, false if no access
 */
function auth() {
    global $_CONF, $REMOTE_ADDR;

    $urlfor = 'advancededitor';
    if (COM_isAnonUser() ) {
        $urlfor = 'advancededitor'.md5($REMOTE_ADDR);
    }
    $cookiename = $_CONF['cookie_name'].'adveditor';
    if ( isset($_COOKIE[$cookiename]) ) {
        $token = $_COOKIE[$cookiename];
    } else {
        $token = '';
    }
    if (SEC_checkTokenGeneral($token,$urlfor)  ) {
        return true;
    } else {
        return false;
    }
    return false;
}
$fm = new Filemanager();
?>