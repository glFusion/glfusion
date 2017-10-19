<?php
/**
* glFusion CMS
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2017 by Mark R. Evans - mark AT glfusion DOT org
*/
require_once '../../../lib-common.php';

if (is_ajax()) {

    require_once 'install.lib.php';

    $errorMessages = array();

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
    global $_CONF, $errorMessages;

    $errorCount = 0;

    $path = $_CONF['path_admin'].'install';

    if (!is_string($path) || $path == "") die();
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if (@is_dir($path)) {
        if (!$dh = @opendir($path)) die();
        while (false !== ($f = readdir($dh))) {
            if ($f == '..' || $f == '.') continue;
            $rc = remove_files("$path".DIRECTORY_SEPARATOR."$f");
            if ( $rc == false ) {
                $errorCount++;
                $errorMessages[] = 'remove_files (in remove_install) returned false: '."$path".DIRECTORY_SEPARATOR."$f";
            }
        }
        closedir($dh);
        if ( @rmdir($path) == false ) {
            $errorMessages[] = 'Failed removing directory: '.$path;
            $errorCount++;
        }
    } else {
        if ( @unlink($path) == false ) {
            $errorMessages[] = 'Failed removing file: '.$path;
            $errorCount++;
        }
    }
    $retval = array();
    if ( $errorCount > 0 ) {
        $retval['errorCode'] = 1;
        $retval['statusMessage'] = 'Errors removing files';
        $retval['errors'] = $errorMessages;
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
    global $errorMessages;

    if (!is_string($path) || $path == "") {
        $errorMessages[] = 'In remove_files - path is blank or not a string';
        return true;
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if (@is_dir($path)) {
        if (!$dh = @opendir($path)) return false;
        while (false !== ($f = readdir($dh))) {
            if ($f == '..' || $f == '.') continue;
            $rc = remove_files("$path".DIRECTORY_SEPARATOR."$f");
            if ( $rc === false ) {
                $errorMessages[] = 'remove_files (in remove_files) returned false: '."$path".DIRECTORY_SEPARATOR."$f";
                return false;
            }
        }
        closedir($dh);
        $rc = @rmdir($path);
        if ( $rc === false ) {
            $errorMessages[] = 'Unable to remove directory (in remove_files): '.$path;
            return false;
        } else {
            return true;
        }
    } else {
        $rc = @unlink($path);
        if ( $rc === false ) {
            $errorMessages[] = 'Unable to remove file (in remove_files): ' . $path;
            return false;
        } else {
            return true;
        }
    }
    $errorMessages[] = 'ERROR - returning false from remove_files';
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