<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | plugins.php                                                              |
// |                                                                          |
// | glFusion plugin administration page.                                     |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark A. Howard         mark AT usable-web DOT com                        |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
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

require_once '../lib-common.php';
require_once 'auth.inc.php';

// Uncomment the line below if you need to debug the HTTP variables being passed
// to the script.  This will sometimes cause errors but it will allow you to see
// the data being passed in a POST operation
// echo COM_debug($_POST);

// Number of plugins to list per page
// We use 25 here instead of the 50 entries in other lists to leave room
// for the list of uninstalled plugins.

define ('PLUGINS_PER_PAGE', 25);

$display = '';

if (!SEC_hasrights ('plugin.edit')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_startBlock ($MESSAGE[30], '',
                                COM_getBlockTemplate ('_msg_block', 'header'));
    $display .= $MESSAGE[38];
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to illegally access the plugin administration screen.");
    echo $display;
    exit;
}

function PLUGINS_getPluginXML($pluginDir)
{
    global $_CONF, $pluginData;

    if (!$dh = @opendir($pluginDir)) {
        return false;
    }

    $filename = $pluginDir . '/plugin.xml';

    if (!($fp=@fopen($filename, "r"))) {
        return -1;
    }

    $pluginData = array();

    if (!($xml_parser = xml_parser_create()))
        return false;

    xml_set_element_handler($xml_parser,"PLUGINS_startElementHandler","PLUGINS_endElementHandler");
    xml_set_character_data_handler( $xml_parser, "PLUGINS_characterDataHandler");

    while( $data = fread($fp, 4096)){
        if(!xml_parse($xml_parser, $data, feof($fp))) {
            break;
        }
    }
    xml_parser_free($xml_parser);
}

/**
* XML startElement callback
*
* used for plugin.xml parsing
*
* @param    object $parser  Handle to the parser object
* @param    string $name    Name of element
* @param    array  $attrib  array of attributes for element
* @return   none
*
*/
function PLUGINS_startElementHandler($parser,$name,$attrib)
{
    global $pluginData, $state;

    switch ($name) {
        case 'ID' :
            $state = 'id';
            break;
        case 'NAME' :
            $state = 'pluginname';
            break;
        case 'VERSION' :
            $state = 'pluginversion';
            break;
        case 'GLFUSIONVERSION' :
            $state = 'glfusionversion';
            break;
        case 'PHPVERSION' :
            $state = 'phpversion';
            break;
        case 'DESCRIPTION' :
            $state = 'description';
            break;
        case 'URL' :
            $state = 'url';
            break;
        case 'MAINTAINER' :
            $state = 'maintainer';
            break;
        case 'DATABASE' :
            $state = 'database';
            break;
        case 'REQUIRES' :
            $state = 'requires';
            break;
        case 'DATAPROXYDRIVER' :
            $state = 'dataproxydriver';
            break;
        case 'LAYOUT' :
            $state = 'layout';
            break;
        case 'RENAMEDIST' :
            $state = 'renamedist';
            break;
    }
}

function PLUGINS_endElementHandler($parser,$name)
{
    global $pluginData, $state;

    $state='';
}

function PLUGINS_characterDataHandler($parser, $data)
{
    global $pluginData, $state;

    if (!$state) {
        return;
    }

    switch ($state) {
        case 'id' :
            $pluginData['id'] = $data;
            break;
        case 'pluginname' :
            $pluginData['name'] = $data;
            break;
        case 'pluginversion' :
            $pluginData['version'] = $data;
            break;
        case 'glfusionversion' :
            $pluginData['glfusionversion'] = $data;
            break;
        case 'phpversion' :
            $pluginData['phpversion'] = $data;
            break;
        case 'description' :
            $pluginData['description'] = $data;
            break;
        case 'url' :
            $pluginData['url'] = $data;
            break;
        case 'maintainer' :
            $pluginData['author'] = $data;
            break;
        case 'database' :
            $pluginData['database'] = $data;
            break;
        case 'requires' :
            $pluginData['requires'][] = $data;
            break;
        case 'dataproxydriver' :
            $pluginData['dataproxydriver'] = $data;
            break;
        case 'layout' :
            $pluginData['layout'] = $data;
            break;
        case 'renamedist' :
            $pluginData['renamedist'][] = $data;
            break;
    }
}

/**
* Shows the plugin upload form
*
*/
function PLUGINS_showUploadForm($token)
{
    global $_CONF,$LANG_ADMIN,$LANG32;

    $retval = '';

    $T = new Template($_CONF['path_layout'] . 'admin/plugins');
    $T->set_file('form','plugin_upload_form.thtml');

    $T->set_var(array(
        'form_action_url'   =>  $_CONF['site_admin_url'] .'/plugin_upload.php',
        'lang_upload_plugin' => $LANG32[57],
    ));

    $retval .= $T->parse('output', 'form');

    return $retval;
}


/**
* Adds installed plugins to the list
*
*/
function PLUGINS_installedPlugins()
{
    global $_TABLES, $data_arr;

    $result = DB_query("SELECT pi_name,pi_version,pi_gl_version,pi_enabled,pi_homepage FROM {$_TABLES['plugins']} WHERE 1=1");
    $rows = DB_numRows($result);
    for ($i=0; $i<$rows; $i++) {
        $data_arr[] = DB_fetchArray($result);
    }
    return $rows;
}

/**
* Adds new/uninstalled plugins (if any) to the list
*
*/
function PLUGINS_newPlugins()
{
    global $_CONF, $_TABLES, $LANG32, $data_arr, $pluginData;

    $plugins = array ();
    $plugins_dir = $_CONF['path'] . 'plugins/';
    $fd = opendir($plugins_dir);
    $found = 0;
    $retval = '';
    while (($dir = @readdir ($fd)) == TRUE) {
        if (($dir <> '.') && ($dir <> '..') && ($dir <> 'CVS') &&
           ($dir <> '.svn') && (substr($dir, 0 , 1) <> '.') && is_dir($plugins_dir . $dir)) {
            clearstatcache ();
            // Check and see if this plugin is installed - if there is a record.
            // If not then it's a new plugin
            if (DB_count($_TABLES['plugins'],'pi_name',$dir) == 0) {
                // additionally, check if a 'functions.inc' exists
                if (file_exists ($plugins_dir . $dir . '/functions.inc')) {
                    // and finally, since we're going to link to it, check if
                    // an install script exists
                    $adminurl = $_CONF['site_admin_url'];
                    if (strrpos ($adminurl, '/') == strlen ($adminurl)) {
                        $adminurl = substr ($adminurl, 0, -1);
                    }
                    $pos = strrpos ($adminurl, '/');
                    if ($pos === false) {
                        // didn't work out - use the URL
                        $admindir = $_CONF['site_admin_url'];
                    } else {
                        $admindir = $_CONF['path_html']
                                  . substr ($adminurl, $pos + 1);
                    }
                    $fh = @fopen ($admindir . '/plugins/' . $dir
                        . '/install.php', 'r');
                    if ($fh) {
                        fclose ($fh);
                        $rc = PLUGINS_getPluginXML($_CONF['path'] . 'plugins/' . $dir);
                        $url = ($rc <> -1) ? $pluginData['url'] : '';
                        $glfusionversion = ($rc <> -1) ? $pluginData['glfusionversion'] : '';
                        $version = ($rc <> -1) ? $pluginData['version'] : '';
                        $data_arr[] = array(
                            'install' => true,
                            'pi_name' => $dir,
                            'pi_homepage' => $url,
                            'pi_gl_version' => $glfusionversion,
                            'pi_code_version' => $version
                        );

                        $A['pi_homepage'] = (!empty($pluginData['url'])) ? $pluginData['url'] : '';
                        $found++;
                    }
                }
            }
        }
    }
    return ($found > 0) ? true : false;
}

/**
* Updates a plugin (call its upgrade function).
*
* @param    pi_name   string   name of the plugin to uninstall
* @return             string   HTML for error or success message
*
*/
function PLUGINS_update($pi_name)
{
    global $_CONF, $LANG32, $LANG08, $MESSAGE, $_IMAGE_TYPE;

    $retval = '';

    if (strlen ($pi_name) == 0) {
        $retval .= COM_startBlock ($LANG32[13], '',
                            COM_getBlockTemplate ('_msg_block', 'header'));
        $retval .= COM_errorLog ($LANG32[12]);
        $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));

        return $retval;
    }
    $result = PLG_upgrade ($pi_name);
    if ($result > 0 ) {
        if ($result === TRUE) { // Catch returns that are just true/false
            $retval .= COM_refresh ($_CONF['site_admin_url']
                    . '/plugins.php?msg=60');
        } else {  // Plugin returned a message number
            $retval = COM_refresh ($_CONF['site_admin_url']
                    . '/plugins.php?msg=' . $result . '&amp;plugin='
                    . $pi_name);
        }
    } else {  // Plugin function returned a false
        $retval .= COM_showMessage(95);
    }
    CTL_clearCache();
    return $retval;
}


/**
* Uninstall a plugin (call its uninstall function).
*
* @param    pi_name   string   name of the plugin to uninstall
* @return             string   HTML for error or success message
*
*/
function PLUGINS_unInstall($pi_name)
{
    global $_CONF, $_DB_table_prefix, $_TABLES, $LANG32, $LANG08, $MESSAGE, $_IMAGE_TYPE;

    $retval = '';

    if (strlen ($pi_name) == 0) {
        $retval .= COM_startBlock ($LANG32[13], '',
                            COM_getBlockTemplate ('_msg_block', 'header'));
        $retval .= COM_errorLog ($LANG32[12]);
        $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));

        return $retval;
    }

    // if the plugin is disabled, load the functions.inc now
    if (!function_exists ('plugin_uninstall_' . $pi_name)) {
        require_once ($_CONF['path'] . 'plugins/' . $pi_name . '/functions.inc');
    }

    if ( !function_exists('plugin_autouninstall_'.$pi_name) && file_exists($_CONF['path'].'plugins/'.$pi_name.'/autoinstall.php') ) {
        require_once $_CONF['path'].'plugins/'.$pi_name.'/autoinstall.php';
    }

    $msg = '';
    if (PLG_uninstall ($pi_name)) {
        $msg = 45;
        $retval .= COM_showMessage (45);
    } else {
        $msg = 95;
        $retval .= COM_showMessage (95);
    }
    CTL_clearCache();

    if ( $msg != '' ) {
        $refreshURL = $_CONF['site_admin_url'].'/plugins.php?msg='.$msg;
    } else {
        $refreshURL = $_CONF['site_admin_url'].'/plugins.php';
    }

    echo COM_refresh($refreshURL);
    exit;
}

/**
 * used for the list of plugins in admin/plugins.php
 *
 */
function PLUGINS_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $LANG_ADMIN, $LANG32, $_PLUGINS, $pluginData;

    $retval = false;

    $install = $A['install'];
    $enabled = ($A['pi_enabled'] == 1) ? true : false;
    if ($install) {
        $code_version = $A['pi_code_version'];
    } elseif ($enabled) {
        $code_version = PLG_chkVersion($A['pi_name']);
    } else {
        $rc = PLUGINS_getPluginXML($_CONF['path'] . 'plugins/' . $A['pi_name']);
        $code_version = ($rc <> -1) ? $pluginData['version'] : '?.?.?';
    }

    switch($fieldname) {

        case 'control':
            if ($install) {
                $attr['title'] = $LANG32[60];
                $retval = COM_createLink($icon_arr['wrench'],
                    $_CONF['site_admin_url'] . '/plugins/' . $A['pi_name'] . '/install.php'
                    . '?action=install'
                    . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            } else {
                if ($enabled) {
                    $switch = ' checked="checked"';
                    $title = 'title="' . $LANG_ADMIN['disable'] . '" ';
                } else {
                    $switch = '';
                    $title = 'title="' . $LANG_ADMIN['enable'] . '" ';
                }
                $retval = '<input type="checkbox" name="enabledplugins[' . $A['pi_name'] . ']"'
                    . ' onclick="submit()" value="1"' . $switch
                    . $title
                    . XHTML . ">";
                $retval .= '<input type="hidden" name="pluginarray['.$A['pi_name'].']" value="1" />';
            }
            break;
/*
        case 'pi_name' :
            if ($install) {
                $retval = $fieldvalue;
            } else {
                $retval = ($enabled) ? $fieldvalue : '<span class="disabledfield">' . $fieldvalue . '</span>';
            }
            break;
*/
        case 'pi_code_version':
            $fieldvalue = $code_version;
            if ($enabled) {
                $retval = ($fieldvalue <> $A['pi_version']) ? '<span class="warning">' . $fieldvalue . '</span>' : $fieldvalue;
            } else {
                $retval = '<span class="disabledfield">' . $fieldvalue . '</span>';
            }
            break;

        case 'pi_version':
            if ($enabled) {
                $retval = ($fieldvalue <> $code_version) ? '<span class="warning">' . $fieldvalue . '</span>' : $fieldvalue;
            } else {
                $retval = '<span class="disabledfield">' . $fieldvalue . '</span>';
            }
            break;

        case 'update':
            if ($enabled AND ($A['pi_version'] <> $code_version)) {
                $retval = '';
                $attr['title'] = $LANG32[38];
                $attr['onclick'] = 'return confirm(\'' . $LANG32[77] . '\');';
                $retval .= COM_createLink($icon_arr['update'],
                    $_CONF['site_admin_url'] . '/plugins.php'
                    . '?update=x'
                    . '&amp;pi_name=' . $A['pi_name']
                    . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            } else {
                $retval = '';
            }
            break;

        case 'delete':
            if ($install) {
                $retval = '';
            } else {
                $attr['title'] = $LANG_ADMIN['delete'];
                $attr['onclick'] = 'return doubleconfirm(\'' . $LANG32[76] . '\',\'' . $LANG32[31] . '\');';
                $retval = COM_createLink($icon_arr['delete'],
                    $_CONF['site_admin_url'] . '/plugins.php'
                    . '?delete=x'
                    . '&amp;pi_name=' . $A['pi_name']
                    . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            }
            break;

        default:
            $retval = ($enabled) ? $fieldvalue : '<span class="disabledfield">' . $fieldvalue . '</span>';
            break;
    }
    return $retval;
}

/**
* List available plugins
*
* @return   string                  formatted list of plugins
*
*/
function PLUGINS_list($token)
{
    global $_CONF, $_TABLES, $LANG32, $LANG_ADMIN, $_IMAGE_TYPE, $data_arr;

    USES_lib_admin();

    $retval .= COM_startBlock($LANG32[5], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $menu_arr = array (
                    array('url' => $_CONF['site_admin_url'],
                          'text' => $LANG_ADMIN['admin_home']));


    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG32[11],
        $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
    );

    $retval .= PLUGINS_showUploadForm($token);  // show the plugin upload form

    $data_arr = array();
    $oldplugins = PLUGINS_installedPlugins();   // installed plugins
    $newplugins = PLUGINS_newPlugins();         // uninstalled/new plugins

    $header_arr = array(
        array('text' => $LANG32[78], 'field' => 'control', 'align' => 'center', 'width' => '40px'),
        array('text' => $LANG32[16], 'field' => 'pi_name'),
        array('text' => $LANG32[27], 'field' => 'pi_homepage'),
        array('text' => $LANG32[18], 'field' => 'pi_gl_version', 'align' => 'center', 'width' => '75px'),
        array('text' => $LANG32[36], 'field' => 'pi_code_version', 'align' => 'center', 'width' => '75px'),
        array('text' => $LANG32[17], 'field' => 'pi_version', 'align' => 'center', 'width' => '75px'),
        array('text' => $LANG32[38], 'field' => 'update', 'align' => 'center','width' => '40px'),
        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'align' => 'center', 'width' => '40px'),
    );

    $text_arr = array(
        'form_url'     => $_CONF['site_admin_url'] . '/plugins.php'
    );


    $options_arr = array();

    // set security token and plugin enable/disable indicator
    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"/>',
        'bottom' => '<input type="hidden" name="pluginenabler" value="true"/>'
    );

    $retval .= ADMIN_simpleList('PLUGINS_getListField', $header_arr,
                $text_arr, $data_arr, $options_arr, $form_arr, $token);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

/**
* Toggle status of a plugin from enabled to disabled and back
*
* @param    string  $pi_name    name of the plugin
* @return   void
*
*/
function PLUGINS_toggleStatus($plugin_name_arr, $pluginarray)
{
    global $_TABLES, $_DB_table_prefix;

    if (isset($pluginarray) && is_array($pluginarray) ) {
        foreach ($pluginarray AS $plugin => $junk ) {
            $plugin = COM_applyFilter($plugin);
            if ( isset($plugin_name_arr[$plugin]) ) {
                DB_query ("UPDATE {$_TABLES['plugins']} SET pi_enabled = '1' WHERE pi_name = '".DB_escapeString($plugin)."'");
                PLG_enableStateChange ($plugin, true);
            } else {
                $rc = PLG_enableStateChange ($plugin, false);
                if ( $rc != -2 ) {
                    DB_query ("UPDATE {$_TABLES['plugins']} SET pi_enabled = '0' WHERE pi_name = '".DB_escapeString($plugin)."'");
                }
            }
        }
    }
    CTL_clearCache();
    return;
}

// MAIN ========================================================================

$action = '';
$expected = array('update','delete','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

$pi_name = '';
if (isset($_POST['pi_name'])) {
    $pi_name = COM_applyFilter($_POST['pi_name']);
} elseif (isset($_GET['pi_name'])) {
    $pi_name = COM_applyFilter($_GET['pi_name']);
}

$validtoken = SEC_checkToken();

if (isset ($_POST['pluginenabler']) && $validtoken) {
    $enabledplugins = array();
    if (isset($_POST['enabledplugins'])) {
        $enabledplugins = $_POST['enabledplugins'];
    }
    $pluginarray = array();
    if ( isset($_POST['pluginarray']) ) {
        $pluginarray = $_POST['pluginarray'];
    }
    PLUGINS_toggleStatus($enabledplugins,$pluginarray);

    // force a refresh so that the information of the plugin that was just
    // enabled / disabled (menu entries, etc.) is displayed properly

//    echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php');
//    exit;
    header ('Location: ' . $_CONF['site_admin_url'] . '/plugins.php');
    exit;
}

switch ($action) {

    case 'update':
        if ($validtoken) {
            $display .= COM_siteHeader ('menu', $LANG32[13]);
            $display .= PLUGINS_update($pi_name);
            $display .= COM_siteFooter();
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally update plugin $pi_name and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'delete':
        if ($validtoken) {
            $display .= COM_siteHeader ('menu', $LANG32[30]);
            $display .= PLUGINS_unInstall($pi_name);
            $token = SEC_createToken();
            $display .= PLUGINS_list($token);
            $display .= COM_siteFooter ();
            $display .= COM_siteFooter ();
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally delete plugin $pi_name and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        $display .= COM_siteHeader ('menu', $LANG32[5]);

        $msg = 0;
        if (isset ($_POST['msg'])) {
            $msg = COM_applyFilter ($_POST['msg'], true);
        } else if (isset ($_GET['msg'])) {
            $msg = COM_applyFilter ($_GET['msg'], true);
        }
        $plugin = '';
        if (isset ($_POST['plugin'])) {
            $plugin = COM_applyFilter ($_POST['plugin']);
        } else if (isset ($_GET['plugin'])) {
            $plugin = COM_applyFilter ($_GET['plugin']);
        }
        $display .= ($msg > 0) ? COM_showMessage($msg,$plugin) : '';
        $token = SEC_createToken();
        $display .= PLUGINS_list($token);
        $display .= COM_siteFooter();
        break;

}

echo $display;

?>
