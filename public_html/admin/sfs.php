<?php
/**
* glFusion CMS
*
* User validation against Stop Forum Spam
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Cache\Cache;

// comment out for production
//define('DVLP_VERSION', 'Y');

USES_lib_admin();

// MAIN
if (isset($_GET['mode']) && ($_GET['mode'] == 'logout')) {
    print COM_refresh($_CONF['site_url'] . '/users.php?mode=logout');
}

$page = '';

/*
 * return 0 - OK
 * return 1 - email found
 * return 2 - ip found
 * return 4 - username found
*/

function _checkSFS($username, $email, $ip = '')
{
    global $_TABLES, $_SPX_CONF, $LANG_SX00;

    $rc = 0;
    $arguments = array();
    $response = '';

    if ( defined('DEMO_MODE') ) return 0;

    $http=new http_class;
    $http->timeout=0;
    $http->data_timeout=0;
    $http->debug=0;
    $http->html_debug=0;
    $http->user_agent = 'glFusion/' . GVERSION;
    $url="http://www.stopforumspam.com/api";
    $requestArgs = '?f=serial&';
    if ( $ip != '' ) {
        $requestArgs .= 'ip='.$ip.'&';
    }
    if ( $email != '' ) {
        $requestArgs .= 'email='.urlencode($email).'&';
    }
    if ( $username != '' ) {
        $requestArgs .= 'username='.urlencode($username).'&';
    }
    $requestArgs .= 'cmd=display';
    $url = $url . $requestArgs;
    $error = $http->GetRequestArguments($url,$arguments);
    $error=$http->Open($arguments);
    $error=$http->SendRequest($arguments);
    if ( $error == "" ) {
        $error=$http->ReadReplyBody($body,1024);
        if ( $error == "" || strlen($body) > 0 ) {
            $response = $response . $body;
            $result = @unserialize($response);
            if (!$result) return 0;     // invalid data, assume ok

            if ( (isset($result['email']) && $result['email']['appears'] == 1) ) {
                $rc = $rc + 1;
            }
            if ( isset($result['ip']) && $result['ip']['appears'] == 1 )  {
                $rc = $rc + 2;
            }
            if ( isset($result['username']) && $result['username']['appears'] == 1 )  {
                $rc = $rc + 4;
            }
        }
    }
    return $rc;
}

function SFS_getListField($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_SFS;

    $retval = '';
    $matchCount = 0;

    switch($fieldname) {

        case 'status' :
            if (defined('DVLP_VERSION') ) {
                $rc = rand(0,7);
            } else {
                $rc = _checkSFS($A['username'], $A['email'], $A['remote_ip']);
            }
            if ($rc == 0) {
                $retval = ''; // make the field blank if OK.
            } else {
                if ( $rc & 1 ) {
                    $retval .= $LANG_SFS['sfs_email_match'];
                    $matchCount++;
                }
                if ( $rc & 2 ) {
                    if ( $matchCount > 0 ) $retval .= ' :: ';
                    $retval .= $LANG_SFS['sfs_ip_match'];
                    $matchCount++;
                }
                if ( $rc & 4 ) {
                    if ($matchCount > 0 ) $retval .= ' :: ';
                    $retval .= $LANG_SFS['sfs_username_match'];
                }
            }
            break;

        default:
            $retval .= $fieldvalue;
            break;
    }

    return $retval;
}

function SFS_adminList()
{
    global $_CONF, $_USER, $_TABLES, $LANG_SFS, $LANG_ADMIN;

    // force the query limit to 10 if user has not set a limit.

    if ( !isset($_GET['query_limit']) && !isset($_POST['query_limit']) ) {
        $_GET['query_limit'] = 10;
    }

    if (defined('DVLP_VERSION') ) {
        $block_title = '<strong>DEVELOPMENT TOGGLE ENABLED</strong> - ' . $LANG_SFS['title'];
    } else {
        $block_title = $LANG_SFS['title'];
    }

    $retval = COM_startBlock($block_title, '', COM_getBlockTemplate('_admin_block', 'header'));

    $menu_arr = array (
         array('url' => $_CONF['site_admin_url'].'/index.php', 'text' => $LANG_ADMIN['admin_home']),
    );

    $retval .= ADMIN_createMenu($menu_arr, $LANG_SFS['instructions'],
                $_CONF['layout_url'] . '/images/icons/sfs.png');

    $header_arr = array(
        array('text' => $LANG_SFS['uid'],       'field' => 'uid', 'align' => 'center'),
        array('text' => $LANG_SFS['username'],  'field' => 'username', 'align' => 'left'),
        array('text' => $LANG_SFS['email'],     'field' => 'email', 'align' => 'left'),
        array('text' => $LANG_SFS['remote_ip'], 'field' => 'remote_ip','align' => 'left'),
        array('text' => $LANG_SFS['sfs_status'],'field' => 'status')
    );

    $text_arr = array(
        'no_data'    => $LANG_SFS['no_data'],
        'form_url'   => $_CONF['site_admin_url'] . '/sfs.php',
        'help_url'   => '',
        'has_limit'  => true,
        'has_search' => false,
        'has_paging' => true,
    );

    $actions = '<input name="banbutton" type="image" src="'
        . $_CONF['layout_url'] . '/images/admin/delete.png'
        . '" style="vertical-align:text-bottom;" title="' . $LANG_SFS['action_text']
        . '" onclick="return doubleconfirm(\'' . $LANG_SFS['confirm_one'] . '\',\'' . $LANG_SFS['confirm_two'] . '\');"'
        . '/>&nbsp;' . $LANG_SFS['action_text'];

    $actions .= '&nbsp;&nbsp;&nbsp;&nbsp;<input name="delbutton" type="image" src="'
        . $_CONF['layout_url'] . '/images/admin/delete.png'
        . '" style="vertical-align:text-bottom;" title="' . $LANG_SFS['action_text_del']
        . '" onclick="return doubleconfirm(\'' . $LANG_SFS['confirm_one_del'] . '\',\'' . $LANG_SFS['confirm_two_del'] . '\');"'
        . '/>&nbsp;' . $LANG_SFS['action_text_del'];
    $actions .= '<br /><br />';

    $option_arr = array(
        'chkselect'     => true,
        'chkall'        => true,
        'chkfield'      => 'uid',
        'chkname'       => 'actionitem',
        'chkactions'    => $actions,
    );

    $query_arr = array('table' => 'users',
                       'sql' => "SELECT uid,username,email,status,remote_ip FROM {$_TABLES['users']} WHERE status <> 0 ",
                       'query_fields' => array('status'),
                       'default_filter' => "");

    $token = SEC_createToken();

    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"/>',
        'bottom' => '<input type="hidden" name="userban" value="true"/>'
    );

    $defsort_arr = array(
        'field'     => 'regdate',
        'direction' => 'DESC',
    );

    $retval .= ADMIN_list("sfs", "SFS_getListField", $header_arr, $text_arr, $query_arr, $defsort_arr, '', $token, $option_arr, $form_arr);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

function SFS_banUsers()
{
    global $_CONF, $_TABLES, $LANG_SFS;

    if ( defined('DEMO_MODE') ) return 'Banning Users is disabled in Demo Mode';

    if (isset($_POST['actionitem']) AND is_array($_POST['actionitem'])) {
        foreach($_POST['actionitem'] as $actionitem) {
            $uid = COM_applyFilter($actionitem);
            if ( $uid <> 2 ) {
                DB_query("UPDATE {$_TABLES['users']} SET status = 0 WHERE uid = " . (int) $uid);
            }
        }
    }
    return $LANG_SFS['confirmation_ban'];
}

function SFS_delUsers()
{
    global $_USER, $_CONF, $_TABLES, $LANG_SFS, $LANG28;

    if ( defined('DEMO_MODE') ) return 'Deleting users is disabled in Demo Mode';

    $msg = '';

    if (isset($_POST['actionitem']) AND is_array($_POST['actionitem'])) {
        foreach($_POST['actionitem'] as $actionitem) {
            $uid = COM_applyFilter($actionitem);
            if ( $uid <> 2 && $uid <> $_USER['uid']) {
                if (defined('DVLP_VERSION') ) {
                    print "We would delete userid " . $uid . " here<br />";
                } else {
                    if (!USER_deleteAccount($uid)) {
                        $msg .= "<strong>{$LANG28[2]} $delitem {$LANG28[70]}</strong><br/>\n";
                    }
                }
            }
        }
    }
    $c = Cache::getInstance()->deleteItemsByTag('menu');
    return $LANG_SFS['confirmation_del'] . '<br />' . $msg;
}

// MAIN

$pageBody = '';
$action = '';
$expected = array('banbutton_x','delbutton_x' );

foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
        $action = $provided;
    }
}

switch($action) {
    case 'banbutton_x':
        if (SEC_checkToken()) {
            $msg = SFS_banUsers();
            $pageBody .= COM_showMessageText($msg) . SFS_adminList();
        } else {
            COM_accessLog('User ' . $_USER['username'] . ' tried to ban users and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'delbutton_x' :
        if (SEC_checkToken()) {
            $msg = SFS_delUsers();
            $pageBody .= COM_showMessageText($msg) . SFS_adminList();
        } else {
            COM_accessLog('User ' . $_USER['username'] . ' tried to del users and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default :
        $pageBody .= SFS_adminList();
        break;
}

$display  = COM_siteHeader('menu',$LANG_SFS['title']);
$display .= $pageBody;
$display .= COM_siteFooter();

echo $display;

?>