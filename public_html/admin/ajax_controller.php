<?php
/**
* glFusion CMS
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014 by Mark R. Evans - mark AT glfusion DOT org
*/

require_once '../lib-common.php';

if (is_ajax()) {
    if (isset($_POST["action"]) && !empty($_POST["action"])) {
        $action = $_POST["action"];
        switch ( $action ) {
            case "test":
                test_function();
                break;
            case 'blocktoggle' :
                block_toggle();
                break;
        }
    } else {
        die();
    }
}

/*
 * Determine if a valid ajax request
 */
function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
* Enable and Disable block
*/
function block_toggle() {
    global $_CONF, $_TABLES;

    // Make sure user has rights to access this page
    if (!SEC_hasRights ('block.edit')) {
        die();
    }
    if ( !_sec_checkToken(1) ) {
        $retval['statusMessage'] = 'Invalid security token. Please refresh the page.';
        $retval['errorCode'] = 1;
    } else {
        $side = COM_applyFilter($_POST['blockenabler'], true);
        $enabledblocks = array();
        if (isset($_POST['enabledblocks'])) {
            $enabledblocks = $_POST['enabledblocks'];
        }
        $bidarray = array();
        if ( isset($_POST['bidarray']) ) {
            $bidarray = $_POST['bidarray'];
        }
        if (isset($bidarray) ) {
            foreach ($bidarray AS $bid => $side ) {
                $bid = (int) $bid;
                $side = (int) $side;
                if ( isset($enabledblocks[$bid]) ) {
                    $sql = "UPDATE {$_TABLES['blocks']} SET is_enabled = '1' WHERE bid=$bid AND onleft=$side";
                    DB_query($sql);
                } else {
                    $sql = "UPDATE {$_TABLES['blocks']} SET is_enabled = '0' WHERE bid=$bid AND onleft=$side";
                    DB_query($sql);
                }
            }
        }
        $retval['statusMessage'] = 'Block state has been toggled.';
        $retval['errorCode'] = 0;
    }
    $return["json"] = json_encode($retval);
    echo json_encode($return);
}

/*
 * Test function - used for debugging
 */
function test_function(){

    if (!SEC_inGroup('Root')) {
        COM_accessLog ("Non root user attempted to access ajax controller");
        die();
    }

  $return = $_POST;

  $return["json"] = json_encode($return);
  echo json_encode($return);
}
?>