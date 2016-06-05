<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | social.php                                                               |
// |                                                                          |
// | Social Integration management console                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2015-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

// this is the social integration administrative interface

require_once '../lib-common.php';
require_once 'auth.inc.php';

// ensure the current user has rights to administer social integration

if (!SEC_hasRights('social.admin')) {
    COM_accessLog ("User {$_USER['username']} tried to access the Social Integrations administration screen without the proper permissions.");
    $display = COM_siteHeader('menu')
        . COM_showMessageText($MESSAGE[34],$MESSAGE[30],true)
        . COM_siteFooter();
    echo $display;
    exit;
}

USES_lib_social();

function SI_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $LANG_ADMIN, $_IMAGE_TYPE;

    $retval = false;

    $enabled = ($A['enabled'] == 1) ? true : false;

    switch($fieldname) {

        case 'enabled':

            if ($enabled) {
                $switch = ' checked="checked"';
                $title = 'title="' . $LANG_ADMIN['disable'] . '" ';
            } else {
                $title = 'title="' . $LANG_ADMIN['enable'] . '" ';
                $switch = '';
            }
            $retval = '<input class="sis-clicker" type="checkbox" id="enabledsis['.$A['id'].']" name="enabledsis[' . $A['id'] . ']" ' . $title
                      . 'onclick="submit()" value="' . $A['id'] . '"' . $switch .'>';
            $retval .= '<input type="hidden" name="sisarray[' . $A['id'] . ']" value="1" >';
            break;

        default:
            $retval = ($enabled) ? $fieldvalue : '<span class="disabledfield">' . $fieldvalue . '</span>';
            break;
    }

    return $retval;
}

function SI_getListField_FM($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $LANG_ADMIN, $_IMAGE_TYPE;

    $retval = false;

    $enabled = ($A['enabled'] == 1) ? true : false;

    switch($fieldname) {

        case 'enabled':

            if ($enabled) {
                $switch = ' checked="checked"';
                $title = 'title="' . $LANG_ADMIN['disable'] . '" ';
            } else {
                $title = 'title="' . $LANG_ADMIN['enable'] . '" ';
                $switch = '';
            }
            $retval = '<input class="sfm-clicker" type="checkbox" id="enabledsfm['.$A['ssid'].']" name="enabledsfm[' . $A['ssid'] . ']" ' . $title
                      . 'onclick="submit()" value="' . $A['ssid'] . '"' . $switch .'>';
            $retval .= '<input type="hidden" name="sfmarray[' . $A['ssid'] . ']" value="1" >';
            break;

        default:
            $retval = ($enabled) ? $fieldvalue : '<span class="disabledfield">' . $fieldvalue . '</span>';
            break;
    }

    return $retval;
}


// generate a list of all social integrations

function SI_list()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_SOCIAL;

    USES_lib_admin();

    $retval = '';

    // if an social admin is using this page, offer navigation to the admin page(s)

    if (SEC_hasRights('social.admin')) {
        $menu_arr = array (
            array('url' => $_CONF['site_admin_url'] . '/social.php?list=f','text' => $LANG_SOCIAL['social_follow']),
            array('url' => $_CONF['site_admin_url'] . '/index.php', 'text' => $LANG_ADMIN['admin_home']),
        );
    } else {
        $menu_arr = array();
    }

    // display the header and instructions

    $retval .= COM_startBlock($LANG_SOCIAL['social_share'], '', COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_SOCIAL['share_instructions'],
                $_CONF['layout_url'] . '/images/icons/share.png');

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    // default sort array and direction

    $defsort_arr = array('field' => 'display_name', 'direction' => 'asc');

    // render the list of share options

    $header_arr = array(
        array('text' => $LANG_SOCIAL['id'], 'field' => 'id', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_SOCIAL['name'], 'field' => 'display_name', 'sort' => true),
        array('text' => $LANG_SOCIAL['enabled'], 'field' => 'enabled', 'sort' => true, 'align' => 'center'),
    );

    $text_arr = array(
        'form_url'   => $_CONF['site_admin_url'] . '/social.php'
    );

    $query_arr = array(
        'table' => 'social_share',
        'sql' => "SELECT * FROM {$_TABLES['social_share']} WHERE 1 = 1",
        'query_fields' => array('id', 'name'),
        'default_filter' => COM_getPermSql ('AND')
    );

    $token = SEC_createToken();

    // sisenabler is a hidden field which if set, indicates that one of the
    // social share has been enabled or disabled - the value is the onleft var

    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
        'bottom' => '<input type="hidden" name="sisenabler" value="1">'
    );

    $retval .= ADMIN_list(
        'social_share', 'SI_getListField', $header_arr, $text_arr,
        $query_arr, $defsort_arr, '', $token, '', $form_arr
    );

    $outputHandle = outputHandler::getInstance();
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/admin.js',HEADER_PRIO_NORMAL,'text/javascript');

    return $retval;
}

// generate a list of all social integrations

function SI_FollowMelist()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_SOCIAL;

    USES_lib_admin();

    $outputHandle = outputHandler::getInstance();
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/admin.js',HEADER_PRIO_NORMAL,'text/javascript');

    $retval = '';

    // if an social admin is using this page, offer navigation to the admin page(s)

    if (SEC_hasRights('social.admin')) {
        $menu_arr = array (
            array('url' => $_CONF['site_admin_url'] . '/social.php?list=s','text' => 'Social Share'),
            array('url' => $_CONF['site_admin_url'] . '/index.php', 'text' => $LANG_ADMIN['admin_home']),
        );
    } else {
        $menu_arr = array();
    }

    // display the header and instructions

    $retval .= COM_startBlock($LANG_SOCIAL['social_share'], '', COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_SOCIAL['follow_instructions'],
                $_CONF['layout_url'] . '/images/icons/share.png');

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    // default sort array and direction

    $defsort_arr = array('field' => 'display_name', 'direction' => 'asc');

    // render the list of share options

    $header_arr = array(
        array('text' => $LANG_SOCIAL['id'], 'field' => 'ssid', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_SOCIAL['name'], 'field' => 'display_name', 'sort' => true),
        array('text' => $LANG_SOCIAL['enabled'], 'field' => 'enabled', 'sort' => true, 'align' => 'center'),
    );

    $text_arr = array(
        'form_url'   => $_CONF['site_admin_url'] . '/social.php'
    );

    $query_arr = array(
        'table' => 'social_follow_services',
        'sql' => "SELECT * FROM {$_TABLES['social_follow_services']} WHERE 1 = 1",
        'query_fields' => array('ssid', 'service_name'),
        'default_filter' => COM_getPermSql ('AND')
    );

    $token = SEC_createToken();

    // sisenabler is a hidden field which if set, indicates that one of the
    // social share has been enabled or disabled - the value is the onleft var

    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
        'bottom' => '<input type="hidden" name="sfmenabler" value="1">'
    );

    $retval .= ADMIN_list(
        'social_follow_services', 'SI_getListField_FM', $header_arr, $text_arr,
        $query_arr, $defsort_arr, '', $token, '', $form_arr
    );


    return $retval;
}

/**
* Enable and Disable block
*/
function SI_SIS_toggleStatus($enabledsis, $sisarray)
{
    global $_CONF, $_TABLES;

    if (isset($sisarray) ) {
        foreach ($sisarray AS $id => $junk) {
            if ( isset($enabledsis[$id]) ) {
                $sql = "UPDATE {$_TABLES['social_share']} SET enabled = '1' WHERE id='".DB_escapeString($id)."'";
                DB_query($sql);
            } else {
                $sql = "UPDATE {$_TABLES['social_share']} SET enabled = '0' WHERE id='".DB_escapeString($id)."'";
                DB_query($sql);
            }
        }
    }

    return;
}

/**
* Enable and Disable block
*/
function SI_SFM_toggleStatus($enabledsfm, $sfmarray)
{
    global $_CONF, $_TABLES;

    if (isset($sfmarray) ) {
        foreach ($sfmarray AS $ssid => $junk) {
            $ssid = (int) $ssid;
            if ( isset($enabledsfm[$ssid]) ) {
                $sql = "UPDATE {$_TABLES['social_follow_services']} SET enabled = '1' WHERE ssid=$ssid";
                DB_query($sql);
            } else {
                $sql = "UPDATE {$_TABLES['social_follow_services']} SET enabled = '0' WHERE ssid=$ssid";
                DB_query($sql);
            }
        }
    }

    return;
}

$action = '';

if (isset($_GET['list']) ) {
    $action = $_GET['list'];
}

if (isset($_POST['sisenabler']) && SEC_checkToken()) {
    $side = COM_applyFilter($_POST['sisenabler'], true);
    $enabledsis = array();
    if (isset($_POST['enabledsis'])) {
        $enabledsis = $_POST['enabledsis'];
    }
    $sisarray = array();
    if ( isset($_POST['sisarray']) ) {
        $sisarray = $_POST['sisarray'];
    }
    SI_SIS_toggleStatus($enabledsis, $sisarray);
    $action = 's';
}

if (isset($_POST['sfmenabler']) && SEC_checkToken()) {
    $side = COM_applyFilter($_POST['sfmenabler'], true);
    $enabledsfm = array();
    if (isset($_POST['enabledsfm'])) {
        $enabledsfm = $_POST['enabledsfm'];
    }
    $sfmarray = array();
    if ( isset($_POST['sfmarray']) ) {
        $sfmarray = $_POST['sfmarray'];
    }
    SI_SFM_toggleStatus($enabledsfm, $sfmarray);
    $action = 'f';
}

switch ($action) {
    case 'f' :
        $page = SI_FollowMelist();
        break;
    default :
        $page = SI_list();
        break;
}

echo COM_siteHeader();
echo $page;
echo COM_siteFooter();
?>