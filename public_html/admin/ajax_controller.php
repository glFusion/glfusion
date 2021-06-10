<?php
/**
* glFusion CMS
*
* glFusion AJAX controller
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';

use \glFusion\Log\Log;

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
            case 'menu-element-toggle' :
                menu_element_toggle();
                break;
            case 'menu-toggle' :
                menu_toggle();
                break;
            case 'sp-toggle' :
                $enabledstaticpages = array();
                if (isset($_POST['enabledstaticpages'])) {
                    $enabledstaticpages = $_POST['enabledstaticpages'];
                }
                $sp_idarray = array();
                if ( isset($_POST['sp_idarray']) ) {
                    $sp_idarray = $_POST['sp_idarray'];
                }
                SP_toggleStatus($enabledstaticpages,$sp_idarray);
                break;
            case 'sistoggle' :
                sis_toggle();
                break;
            case 'sfmtoggle' :
                sfm_toggle();
                break;
            case 'articlepreview' :
                articlePreview();
                break;

            case 'pagepreview' :
                pagePreview();
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

function pagePreview()
{
    $page = '';

        $sp_php = isset($_POST['sp_php']) ? $_POST['sp_php'] : '';
        $editor_content = isset($_POST['sp_content']) ? $_POST['sp_content'] : '';

        if ( isset($_POST['sp_php']) && (int) $_POST['sp_php'] > 0 && defined('DEMO_MODE') ) {
            $preview_content = 'StaticPage Preview is disabled in Demo Mode';
        } else {
            $preview_content = SP_render_content ($editor_content, $sp_php);
        }


    $outputHandle = outputHandler::getInstance();
    $page .= $outputHandle->renderHeader('style');
    $page .= $outputHandle->renderHeader('script');
    $page .= $outputHandle->renderHeader('raw');
    $page .= $preview_content;
    
    $retval['preview'] = $page;
    $retval['errorCode'] = 0;

    $return["json"] = json_encode($retval);
    echo json_encode($return);

}

function articlePreview()
{
    global $_CONF, $_TABLES;

    $article = new \glFusion\Article\Article();

    if ($article->retrieveArticleFromVars($_POST) != \glFusion\Article\Article::STORY_LOADED_OK) {
        $retval['errorCode'] = 1;
        $retval['preview'] = 'Error loading preview - please use the Preview Button';
    } else {
        $retval['preview'] = $article->getDisplayArticle('p');
        $retval['errorCode'] = 0;
    }
    $return["json"] = json_encode($retval);
    echo json_encode($return);

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

/**
* Enable and Disable social integration service
*/
function sis_toggle() {
    global $_CONF, $_TABLES, $LANG_SOCIAL;

    // Make sure user has rights to access this page
    if (!SEC_hasRights ('social.admin')) {
        die();
    }
    if ( !_sec_checkToken(1) ) {
        $retval['statusMessage'] = 'Invalid security token. Please refresh the page.';
        $retval['errorCode'] = 1;
    } else {
        $enabledsis = array();
        if (isset($_POST['enabledsis'])) {
            $enabledsis = $_POST['enabledsis'];
        }

        $sisarray = array();
        if ( isset($_POST['sisarray']) ) {
            $sisarray = $_POST['sisarray'];
        }

        if (isset($sisarray) ) {
            foreach ($sisarray AS $id => $junk ) {
                if ( isset($enabledsis[$id]) ) {
                    $sql = "UPDATE {$_TABLES['social_share']} SET enabled = '1' WHERE id='".DB_escapeString($id)."'";
                    DB_query($sql);
                } else {
                   $sql = "UPDATE {$_TABLES['social_share']} SET enabled = '0' WHERE id='".DB_escapeString($id)."'";
                    DB_query($sql);
                }
            }
        }
        $retval['statusMessage'] = $LANG_SOCIAL['state_toggled'];
        $retval['errorCode'] = 0;
    }
    $return["json"] = json_encode($retval);
    echo json_encode($return);
}

/**
* Enable and Disable social follow service
*/
function sfm_toggle() {
    global $_CONF, $_TABLES, $LANG_SOCIAL;

    // Make sure user has rights to access this page
    if (!SEC_hasRights ('social.admin')) {
        die();
    }
    if ( !_sec_checkToken(1) ) {
        $retval['statusMessage'] = 'Invalid security token. Please refresh the page.';
        $retval['errorCode'] = 1;
    } else {
        $enabledsfm = array();
        if (isset($_POST['enabledsfm'])) {
            $enabledsfm = $_POST['enabledsfm'];
        }

        $sfmarray = array();
        if ( isset($_POST['sfmarray']) ) {
            $sfmarray = $_POST['sfmarray'];
        }

        if (isset($sfmarray) ) {
            foreach ($sfmarray AS $id => $junk ) {
                if ( isset($enabledsfm[$id]) ) {
                    $sql = "UPDATE {$_TABLES['social_follow_services']} SET enabled = '1' WHERE ssid=".(int) $id;
                    DB_query($sql);
                } else {
                    $sql = "UPDATE {$_TABLES['social_follow_services']} SET enabled = '0' WHERE ssid=".(int) $id;
                    DB_query($sql);
                }
            }
        }
        $retval['statusMessage'] = $LANG_SOCIAL['state_toggled'];
        $retval['errorCode'] = 0;
    }
    $return["json"] = json_encode($retval);
    echo json_encode($return);
}


function menu_element_toggle()
{
    global $_CONF, $_TABLES;

    if (!SEC_hasRights('menu.admin')) die();

    $retval = array();

    MB_changeActiveStatusElement ($_POST['enableditem']);
    $retval['statusMessage'] = 'Menu Element state has been toggled.';
    $retval['errorCode'] = 0;

    $return["json"] = json_encode($retval);
    echo json_encode($return);
}

function menu_toggle()
{
    global $_CONF, $_TABLES;

    if (!SEC_hasRights('menu.admin')) die();

    $retval = array();

    MB_changeActiveStatusMenu ($_POST['enabledmenu']);
    $retval['statusMessage'] = 'Menu state has been toggled.';
    $retval['errorCode'] = 0;

    $return["json"] = json_encode($retval);
    echo json_encode($return);
}


function SP_toggleStatus($enabledstaticpages, $sp_idarray)
{
    global $_TABLES, $_DB_table_prefix;

    if ( !_sec_checkToken(1) ) {
        $retval['statusMessage'] = 'Invalid security token. Please refresh the page.';
        $retval['errorCode'] = 1;
    } else {
        if (isset($sp_idarray) && is_array($sp_idarray) ) {
            foreach ($sp_idarray AS $sp_id => $junk ) {
                $sp_id = COM_applyFilter($sp_id);
                if (isset($enabledstaticpages[$sp_id])) {
                    DB_query ("UPDATE {$_TABLES['staticpage']} SET sp_status = '1' WHERE sp_id = '".DB_escapeString($sp_id)."'");
                } else {
                    DB_query ("UPDATE {$_TABLES['staticpage']} SET sp_status = '0' WHERE sp_id = '".DB_escapeString($sp_id)."'");
                }
                PLG_itemSaved($sp_id,'staticpages');
            }
        }
        glFusion\Cache::getInstance()->deleteItemsByTag('staticpage');
        $retval['statusMessage'] = 'StaticPage state has been toggled.';
        $retval['errorCode'] = 0;

        $return["json"] = json_encode($retval);
        echo json_encode($return);
    }
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
