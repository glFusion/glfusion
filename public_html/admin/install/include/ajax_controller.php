<?php
/**
* glFusion CMS
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2017 by Mark R. Evans - mark AT glfusion DOT org
*/

if (is_ajax()) {

    if (!defined('GVERSION')) {
        define('GVERSION', '1.7.0');
    }

    require_once 'install.lib.php';

    if (isset($_POST["action"]) && !empty($_POST["action"])) {
        $action = $_POST["action"];
        switch ( $action ) {
            case "test":
                test_function();
                break;
            case 'remove' :
                remove_install();
                break;
            default :
                die();
        }
    } else {
        die();
    }
} else {
    die();
}

/*
 * Determine if a valid ajax request
 */
function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function remove_install()
{
    $errorCount = 0;

    $path = __DIR__;

    $pos = strripos ( $path , DIRECTORY_SEPARATOR);
    $path = substr($path,0,$pos);

    if (!is_string($path) || $path == "") die();
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if (@is_dir($path)) {
        if (!$dh = @opendir($path)) die();
        while (false !== ($f = readdir($dh))) {
            if ($f == '..' || $f == '.') continue;
            $rc = remove_files("$path".DIRECTORY_SEPARATOR."$f");
            if ( $rc == false ) $errorCount++;
        }
        closedir($dh);
        if ( @rmdir($path) == false ) {
            $errorCount++;
        }
    } else {
        if ( @unlink($path) == false ) {
            $errorCount++;
        }
    }
    $retval = array();
    if ( $errorCount > 0 ) {
        $retval['errorCode'] = 1;
        $retval['statusMessage'] = 'Error Removing Installation Files - Please Manually Remove the admin/install/ directory.';
    } else {
        $retval['errorCode'] = 0;
        $retval['statusMessage'] = 'Installation Files Successfully Removed';
    }
    $return["js"] = json_encode($retval);
    echo json_encode($return);
    exit;
}

function remove_files($path)
{
    if (!is_string($path) || $path == "") return false;
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if (@is_dir($path)) {
        if (!$dh = @opendir($path)) return false;
        while (false !== ($f = readdir($dh))) {
            if ($f == '..' || $f == '.') continue;
            remove_files("$path".DIRECTORY_SEPARATOR."$f");
        }
        closedir($dh);
        return @rmdir($path);
    } else {
        return @unlink($path);
    }
    return false;
}

/*
 * Test function - used for debugging
 */
function test_function()
{
    $retval = array();
    $retval['errorCode'] = 0;
    $retval['statusMessage'] = 'Success';

    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}
?>