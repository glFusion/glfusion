<?php
/**
* glFusion CMS
*
* Social Integration management console
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2015-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Social\Social;
use \glFusion\Log\Log;

// ensure the current user has rights to administer social integration

if (!SEC_hasRights('social.admin')) {
    Log::logAccessViolation('Socail Integration Mgt');
    $display = COM_siteHeader('menu')
        . COM_showMessageText($MESSAGE[34],$MESSAGE[30],true,'error')
        . COM_siteFooter();
    echo $display;
    exit;
}

/**
* Get List Field for social services
*
* This function builds the appropriate checkboxes and field formatting
* for the social services list.
*
* @param    string  $fieldname  Name of field being process
* @param    mixed   $fieldvalue Data value for fieldname
* @param    array   $A          Full array of data
* @param    array   $icon_arr
* @param    string  $token      Security Token
* @return   string              Formatted field
*
*/
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

/**
* Get List Field for social follow services
*
* This function builds the appropriate checkboxes and field formatting
* for the social follow services list.
*
* @param    string  $fieldname  Name of field being process
* @param    mixed   $fieldvalue Data value for fieldname
* @param    array   $A          Full array of data
* @param    array   $icon_arr
* @param    string  $token      Security Token
* @return   string              Formatted field
*
*/
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

/**
* Display admin list of social services
*
* Builds admin list of all available social services
*
* @return   string              HTML to display
*
*/
function SI_list()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_SOCIAL;

    USES_lib_admin();

    $retval = '';
    $menu_arr = array();

    $overridden = PLG_overrideSocialShare();

    // if an social admin is using this page, offer navigation to the admin page(s)

    if (SEC_hasRights('social.admin')) {
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/social.php','text' => $LANG_SOCIAL['social_share'],'active' => true);
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/social.php?list=f','text' => $LANG_SOCIAL['social_follow']);
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/social.php?list=s','text' => $LANG_SOCIAL['site_memberships']);
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/index.php', 'text' => $LANG_ADMIN['admin_home']);
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

    if ( $overridden === false ) {
        $retval .= ADMIN_list(
            'social_share', 'SI_getListField', $header_arr, $text_arr,
            $query_arr, $defsort_arr, '', $token, '', $form_arr
        );
    } else {
        $lang_override = sprintf($LANG_SOCIAL['overridden'],$overridden);
        $T = new Template($_CONF['path_layout'] . 'admin/social');
        $T->set_file('page','social_override.thtml');
        $T->set_var('lang_override',$lang_override);
        $retval .= $T->finish ($T->parse ('output', 'page'));
    }

    $outputHandle = outputHandler::getInstance();
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/admin.js',HEADER_PRIO_NORMAL,'text/javascript');

    return $retval;
}

/**
* Display admin list of social follow me services
*
* Builds admin list of all available social follow me services
*
* @return   string              HTML to display
*
*/
function SI_FollowMelist()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_SOCIAL;

    USES_lib_admin();

    $overridden = PLG_overrideSocialShare();

    $outputHandle = outputHandler::getInstance();
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/admin.js',HEADER_PRIO_NORMAL,'text/javascript');

    $retval = '';

    // if an social admin is using this page, offer navigation to the admin page(s)

    if (SEC_hasRights('social.admin')) {
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/social.php','text' => $LANG_SOCIAL['social_share']);
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/social.php?list=f','text' => $LANG_SOCIAL['social_follow'],'active' => true);
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/social.php?list=s','text' => $LANG_SOCIAL['site_memberships']);
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/index.php', 'text' => $LANG_ADMIN['admin_home']);
    } else {
        $menu_arr = array();
    }

    // display the header and instructions

    $retval .= COM_startBlock($LANG_SOCIAL['social_follow'], '', COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_SOCIAL['follow_instructions'],
                $_CONF['layout_url'] . '/images/icons/share.png');

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    // default sort array and direction

    $defsort_arr = array('field' => 'display_name', 'direction' => 'asc');

    // render the list of share options

    $header_arr = array(
//        array('text' => $LANG_SOCIAL['id'], 'field' => 'ssid', 'sort' => false, 'align' => 'center'),
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

/**
* Display admin list of site memberships for social follow for the site
*
* Displays all available social services for the site
*
* @return   string              HTML to display
*
*/
function SI_get_site()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_SOCIAL;

    USES_lib_admin();

    $retval = '';
    $menu_arr = array();

    $overridden = PLG_overrideSocialShare();

    // if an social admin is using this page, offer navigation to the admin page(s)

    if (SEC_hasRights('social.admin')) {
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/social.php','text' => $LANG_SOCIAL['social_share']);
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/social.php?list=f','text' => $LANG_SOCIAL['social_follow']);
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/social.php?list=s','text' => $LANG_SOCIAL['site_memberships'],'active' => true);
        $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/index.php', 'text' => $LANG_ADMIN['admin_home']);
    } else {
        $menu_arr = array();
    }

    $cfg =& config::get_instance();
    $_SOCIAL = $cfg->get_config('social_internal');

    $extra = '';
    if ( isset($_SOCIAL['social_site_extra'])) {
        $extra = $_SOCIAL['social_site_extra'];
    }

    $T = new Template($_CONF['path_layout'] . 'admin/social');
    $T->set_file('page','site_social.thtml');

    $T->set_var('start_block',COM_startBlock($LANG_SOCIAL['site_memberships'], '', COM_getBlockTemplate('_admin_block', 'header')));
    $T->set_var('admin_menu',
                ADMIN_createMenu($menu_arr, $LANG_SOCIAL['membership_instructions'],
                $_CONF['layout_url'] . '/images/icons/share.png'));

    $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->set_block('page','social_links','sl');

    $follow_me = Social::followMeProfile( -1 );
    if ( is_array($follow_me) && count($follow_me) > 0 ) {
        foreach ( $follow_me AS $service ) {
            $T->set_var(array(
                'service_display_name'  => $service['service_display_name'],
                'service'               => $service['service'],
                'service_username'      => $service['service_username'],
                'service_url'           => $service['service_url']
            ));
            $T->parse('sl','social_links',true);
        }
    }

    $T->set_var(array(
        'security_token_name'   => CSRF_TOKEN,
        'security_token'        => SEC_createToken(),
        'extra'                 => $extra,
        'lang_service_name'     => $LANG_SOCIAL['service_name'],
        'lang_service_url'      => $LANG_SOCIAL['service_url'],
        'lang_site_username'    => $LANG_SOCIAL['site_username'],
        'lang_additional_html'  => $LANG_SOCIAL['additional_html'],
        'lang_save'             => $LANG_ADMIN['save'],
        'lang_cancel'           => $LANG_ADMIN['cancel'],
        'form_action'           => $_CONF['site_admin_url'].'/social.php',
    ));

    $retval = $T->finish ($T->parse ('output', 'page'));
    return $retval;
}

/**
* Saves the site social memberships
*
* @return   none
*
*/
function SI_save_site()
{
    global $_CONF, $_TABLES;

    $retval = '';

    $uid = -1; // use -1 for site items

    $cfg =& config::get_instance();

    // run through the POST vars to see which ones are set.

    $social_services = Social::followMeProfile( $uid );
    foreach ( $social_services AS $service ) {
        $service_input = $service['service'].'_username';
        if ( isset( $_POST['$service_input'])) {
            $_POST[$service_input] = strip_tags($_POST[$service_input]);
        }
    }

    foreach ( $social_services AS $service ) {
        $service_input = $service['service'].'_username';
        $_POST[$service_input] = DB_escapeString($_POST[$service_input]);
        if ( $_POST[$service_input] != '' ) {
            $sql  = "REPLACE INTO {$_TABLES['social_follow_user']} (ssid,uid,ss_username) ";
            $sql .= " VALUES (" . (int) $service['service_id'] . ",".$uid.",'".$_POST[$service_input]."');";
            DB_query($sql,1);
        } else {
            $sql = "DELETE FROM {$_TABLES['social_follow_user']} WHERE ssid = ".(int) $service['service_id']." AND uid=".(int) $uid;
            DB_query($sql,1);
        }
    }

    if ( isset($_POST['extra'])) {
        $extra = $_POST['extra'];
        $cfg->set('social_site_extra', $extra, 'social_internal');
    }

    return $retval;
}

$action = '';
$msg    = '';

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

if ( isset($_POST['savesitememberships']) && SEC_checkToken() ) {
    $rc = SI_save_site();
    if ( $rc == 0 ) {
        $msg = $LANG_SOCIAL['saved_msg'];
    }
    $action = 's';
}

switch ($action) {
    case 'f' :
        $page = SI_FollowMelist();
        break;

    case 's' :
        $page = SI_get_site();
        break;

    default :
        $page = SI_list();
        break;
}

echo COM_siteHeader();
if ( $msg != '' ) {
    echo COM_showMessageText($msg, '',false, 'success');
}
echo $page;
echo COM_siteFooter();
?>