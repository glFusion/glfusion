<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | plugins.php                                                              |
// |                                                                          |
// | glFusion plugin administration page.                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark A. Howard         mark AT usable-web DOT com                        |
// |                                                                          |
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
require_once $_CONF['path'] . 'filecheck_data.php';

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
    $display .= COM_showMessageText($MESSAGE[38],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to access the plugin administration screen.");
    echo $display;
    exit;
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
            $pluginData['maintainer'] = $data;
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
* Parses the plugin.xml form to the global $pluginData
*
*/
function PLUGINS_getPluginXML($pluginDir)
{
    global $_CONF, $pluginData;

    if (!$dh = @opendir($pluginDir)) {
        return false;
    }

    $filename = $pluginDir . '/plugin.xml';

    if (!($fp=@fopen($filename, "r"))) {
        return false;
    }

    $pluginData = array();

    if (!($xml_parser = xml_parser_create())) {
        return false;
    }

    xml_set_element_handler($xml_parser,"PLUGINS_startElementHandler","PLUGINS_endElementHandler");
    xml_set_character_data_handler( $xml_parser, "PLUGINS_characterDataHandler");

    while( $data = fread($fp, 4096)){
        if(!xml_parse($xml_parser, $data, feof($fp))) {
            break;
        }
    }
    xml_parser_free($xml_parser);

    return $pluginData;
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
function PLUGINS_loadPlugins(&$data_arr)
{
    global $_CONF, $_TABLES, $glfPlugins;

    $result = DB_query("SELECT pi_name,pi_version,pi_gl_version,pi_enabled,pi_homepage FROM {$_TABLES['plugins']} WHERE 1=1");
    $rows = DB_numRows($result);

    for ($i=0; $i<$rows; $i++) {
        $P = DB_fetchArray($result);
        $pluginData = PLUGINS_getPluginXML($_CONF['path'] . 'plugins/' . $P['pi_name']);
        // ok the plugin is in the plugins table, so it's installed
        $P['installed'] = 1;
        // determine the plugin code version
        if ($P['pi_enabled'] == 1) {
            $P['pi_code_version'] = PLG_chkVersion($P['pi_name']);
        } else {
            $P['pi_code_version'] = (is_array($pluginData)) ? $pluginData['version'] : '0.0.0';
        }
        $P['name'] = (is_array($pluginData)) ? $pluginData['name'] : '';
        $P['description'] = (is_array($pluginData)) ? $pluginData['description'] : '';
        $P['maintainer'] = (is_array($pluginData)) ? $pluginData['maintainer'] : '';
        $P['phpversion'] = (is_array($pluginData)) ? $pluginData['phpversion'] : '';
        $P['glfusionversion'] = (is_array($pluginData)) ? $pluginData['glfusionversion'] : '';
        $P['update'] = (($P['pi_enabled'] == 1) AND ($P['pi_version'] <> $P['pi_code_version'])) ? 1 : 0;
        $P['bundled'] = (in_array($P['pi_name'], $glfPlugins)) ? 1 : 0;
        $data_arr[] = $P;
    }
    return;
}

/**
* Adds new/uninstalled plugins (if any) to the list
*
*/
function PLUGINS_loadNewPlugins(&$data_arr)
{
    global $_CONF, $_TABLES, $LANG32, $glfIgnore;

    $plugins_dir = $_CONF['path'] . 'plugins/';
    $fd = opendir($plugins_dir);

    while (($pi_name = @readdir ($fd)) == TRUE) {
        if ((!in_array($pi_name, $glfIgnore)) && is_dir($plugins_dir . $pi_name)) {
            clearstatcache ();
            if (DB_count($_TABLES['plugins'], 'pi_name', $pi_name) == 0) {
                // plugin is not in the plugins table, check prereqs for installation
                if (file_exists ($plugins_dir . $pi_name . '/functions.inc')) {
                    // functions.inc exists (essential)
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
                    $fh = @fopen ($admindir . '/plugins/' . $pi_name
                        . '/install.php', 'r');
                    if ($fh) {
                        // ok if we got here, we have an install.php
                        fclose ($fh);
                        // so all we have is a name at this point, try to parse a plugin.xml
                        $pluginData = PLUGINS_getPluginXML($_CONF['path'] . 'plugins/' . $pi_name);
                        $data_arr[] = array(
                            'installed' => 0,
                            'bundled' => 0,
                            'update' => 0,
                            'pi_name' => $pi_name,
                            'pi_enabled' => 0,
                            'pi_homepage' => ((is_array($pluginData)) ? $pluginData['url'] : ''),
                            'pi_gl_version' => ((is_array($pluginData)) ? $pluginData['glfusionversion'] : '0.0.0'),
                            'pi_code_version' => ((is_array($pluginData)) ? $pluginData['version'] : '0.0.0'),
                            'name' => ((is_array($pluginData)) ? $pluginData['name'] : ''),
                            'description' => ((is_array($pluginData)) ? $pluginData['description'] : ''),
                            'maintainer' => ((is_array($pluginData)) ? $pluginData['maintainer'] : ''),
                            'phpversion' => ((is_array($pluginData)) ? $pluginData['phpversion'] : ''),
                            'glfusionversion' => ((is_array($pluginData)) ? $pluginData['glfusionversion'] : ''),
                        );
                    }
                }
            }
        }
    }
    return;
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
        $retval .= COM_showMessageText($LANG32[12],$LANG32[13],true,'error');
        COM_errorLog ($LANG32[12]);
        return $retval;
    }

    $result = PLG_upgrade ($pi_name);
    if ($result > 0 ) {
        if ($result === TRUE) { // Catch returns that are just true/false
            COM_setMessage(60);
            $retval .= COM_refresh ($_CONF['site_admin_url'].'/plugins.php');
        } else {  // Plugin returned a message number
            COM_setMessage($result);
            $retval = COM_refresh ($_CONF['site_admin_url'].'/plugins.php?plugin='.urlencode($pi_name));
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
        $retval .= COM_showMessageText($LANG32[12],$LANG32[13],true,'error');
        COM_errorLog ($LANG32[12]);
        return $retval;
    }

    // if the plugin is disabled, load the functions.inc now
    if (!function_exists ('plugin_uninstall_' . $pi_name)) {
        if ( !file_exists($_CONF['path'] . 'plugins/' . $pi_name . '/functions.inc') ) {
            COM_errorLog("Unable to locate the plugin directory for " . $pi_name." - cannot uninstall");
        } else {
            require_once ($_CONF['path'] . 'plugins/' . $pi_name . '/functions.inc');
        }
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
        COM_setMessage($msg);
        $refreshURL = $_CONF['site_admin_url'].'/plugins.php';
    } else {
        $refreshURL = $_CONF['site_admin_url'].'/plugins.php';
    }

    echo COM_refresh($refreshURL);
    exit;
}

/**
* Remove a plugin that is sitting in the public/private tree.
* If they exist, the following directories are deleted recursively:
*
* 1. public_html/admin/plugins/{pi_name}
* 2. public_html/{pi_name}
* 3. private/plugins/{pi_name}
*
* @param    pi_name   string   name of the plugin to remove
* @return             string   HTML for error or success message
*
*/
function PLUGINS_remove($pi_name)
{
    global $_CONF, $LANG32;

    $retval = '';

    if (strlen ($pi_name) == 0) {
        $retval .= COM_showMessageText($LANG32[12],$LANG32[13],true,'error');
        COM_errorLog ($LANG32[12]);
        return $retval;
    }

    COM_errorLog( "Removing the {$pi_name} plugin file structure");

    $msg = '';
    if (PLG_remove($pi_name)) {
        COM_errorLog("Plugin removal was successful.");
        $msg = 116;
        $retval .= COM_showMessage(116);
    } else {
        COM_errorLog("Error removing the plugin file structure - the web server may not have sufficient permissions");
        $msg = 95;
        $retval .= COM_showMessage(95);
    }
    CTL_clearCache();

    if ( $msg != '' ) {
        COM_setMessage($msg);
        $refreshURL = $_CONF['site_admin_url'].'/plugins.php';
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

    $update = ($A['update'] == 1) ? true : false;
    $bundled = ($A['bundled'] == 1) ? true : false;
    $installed = ($A['installed'] == 1) ? true : false;
    $enabled = ($A['pi_enabled'] == 1) ? true : false;

    switch($fieldname) {

        case 'control':
            if (!$installed) {
                $attr['title'] = $LANG32[60];
                $attr['onclick'] = 'return confirm(\'' . $LANG32[80] . '\');';
                $retval = COM_createLink($icon_arr['add'],
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

        case 'version':
                if ($update) {
                    $retval = $A['pi_version'] . '&nbsp;';
                    $attr['title'] = $LANG32[38];
                    $attr['onclick'] = 'return confirm(\'' . $LANG32[77] . '\');';
                    $attr['style'] = 'vertical-align:top;';
                    $retval .= COM_createLink($icon_arr['update'],
                        $_CONF['site_admin_url'] . '/plugins.php'
                        . '?update=x'
                        . '&amp;pi_name=' . $A['pi_name']
                        . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
                    $retval .= '&nbsp;<span class="warning">'
                        . $A['pi_code_version']
                        . '</span><br ' . XHTML . '>';
                } elseif ($enabled) {
                    $retval = $A['pi_version'];
                } elseif (!$installed) {
                    $retval = '<span class="disabledfield">' . $A['pi_code_version'] . '</span>';
                } else {
                    $retval = '<span class="disabledfield">' . $A['pi_version'] . '</span>';
                }
                break;

        case 'info':
            $tip = $A['name']
                . '::'
                . $A['description']
                . '<p><b>' . $LANG32[81] . ':</b></p>'
                . '<p>' . $A['maintainer'] . '</p>'
                . '<p><b>' . $LANG32[82] . ':</b></p>'
                . '<p>glFusion: v' . $A['glfusionversion'] . '<br />' . 'PHP: v' . $A['phpversion'] . '</p>';
            $attr['class'] = COM_getTooltipStyle();
            $attr['title'] = $tip;
             if ($enabled) {
                $retval = COM_createLink($icon_arr['info'], '#', $attr);
             } else {
                $retval = COM_createLink($icon_arr['greyinfo'], '#', $attr);
             }
             break;

        case 'bundled':
            if ($bundled) {
                $retval = ($enabled) ? $icon_arr['check'] : $icon_arr['greycheck'];
            } else {
                $retval = '';
            }
            break;

        case 'pi_homepage':
            if ($enabled) {
                $attr['target'] = '_blank';
                $retval = COM_createLink($fieldvalue, $fieldvalue, $attr);
            } else {
                $retval = ($enabled) ? $fieldvalue : '<span class="disabledfield">' . $fieldvalue . '</span>';
            }
            break;

        case 'unplug':
            if ($installed) {
                $attr['title'] = $LANG32[79];
                $attr['onclick'] = 'return doubleconfirm(\'' . $LANG32[76] . '\',\'' . $LANG32[31] . '\');';
                $retval = COM_createLink($icon_arr['delete'],
                    $_CONF['site_admin_url'] . '/plugins.php'
                    . '?delete=x'
                    . '&amp;pi_name=' . $A['pi_name']
                    . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            } else {
                $attr['title'] = $LANG32[79];
                $attr['onclick'] = 'return doubleconfirm(\'' . $LANG32[88] . '\',\'' . $LANG32[89] . '\');';
                $retval = COM_createLink($icon_arr['delete'],
                    $_CONF['site_admin_url'] . '/plugins.php'
                    . '?remove=x'
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

    $T = new Template($_CONF['path_layout'] .'admin/plugins/');
    $T->set_file('admin-list', 'plugin_list.thtml');

    $T->set_var('block_start', COM_startBlock($LANG32[5], '',
                              COM_getBlockTemplate('_admin_block', 'header')));

    $menu_arr = array (
                    array('url' => $_CONF['site_admin_url'],
                          'text' => $LANG_ADMIN['admin_home']));

    $T->set_var('admin_menu',ADMIN_createMenu(
        $menu_arr,
        $LANG32[11],
        $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
    ));

    $T->set_var('upload_form',PLUGINS_showUploadForm($token));  // show the plugin upload form

    $data_arr = array();
    PLUGINS_loadPlugins($data_arr);             // installed plugins
    PLUGINS_loadNewPlugins($data_arr);          // uninstalled/new plugins

    $defsort_arr = array('field' => 'pi_name', 'direction' => 'asc');

    $header_arr = array(
        array('text' => $LANG32[78], 'field' => 'control', 'align' => 'center', 'width' => '40px'),
        array('text' => $LANG32[16], 'field' => 'pi_name', 'sort' => true),
        array('text' => $LANG32[36], 'field' => 'version', 'align' => 'center', 'nowrap' => true, 'width' => '75px'),
        array('text' => $LANG32[83], 'field' => 'info', 'align' => 'center', 'width' => '40px'),
        array('text' => $LANG32[84], 'field' => 'bundled', 'align' => 'center', 'width' => '40px', 'sort' => true),
        array('text' => $LANG32[27], 'field' => 'pi_homepage', 'nowrap' => true, 'width' => '150px', 'sort' => true),
        array('text' => $LANG32[18], 'field' => 'pi_gl_version', 'align' => 'center', 'width' => '75px', 'sort' => true),
        array('text' => $LANG32[79], 'field' => 'unplug', 'align' => 'center', 'width' => '40px'),
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

    $T->set_var('plugin_list',ADMIN_listArray('plugins', 'PLUGINS_getListField', $header_arr,
                            $text_arr, $data_arr, $defsort_arr, '', $token,
                            $options_arr, $form_arr));

    $T->set_var('block_end',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $retval = $T->parse('output','admin-list');
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
    global $_TABLES, $_PLUGIN_INFO, $_DB_table_prefix;

    if (isset($pluginarray) && is_array($pluginarray) ) {
        foreach ($pluginarray AS $plugin => $junk ) {
            $plugin = COM_applyFilter($plugin);
            if ( isset($plugin_name_arr[$plugin]) ) {
                DB_query ("UPDATE {$_TABLES['plugins']} SET pi_enabled = '1' WHERE pi_name = '".DB_escapeString($plugin)."'");
                $_PLUGIN_INFO[$plugin] = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='".DB_escapeString($plugin)."'");
                PLG_enableStateChange ($plugin, true);
            } else {
                $rc = PLG_enableStateChange ($plugin, false);
                if ( $rc != 99 ) {
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
$expected = array('update','delete','cancel','remove');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
        $action = $provided;
    }
}

$pi_name = '';
if (isset($_POST['pi_name'])) {
    $pi_name = COM_sanitizeID(COM_applyFilter($_POST['pi_name']));
} elseif (isset($_GET['pi_name'])) {
    $pi_name = COM_sanitizeID(COM_applyFilter($_GET['pi_name']));
}

if (isset ($_POST['pluginenabler']) && SEC_checkToken()) {
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

    header ('Location: ' . $_CONF['site_admin_url'] . '/plugins.php');
    exit;
}

switch ($action) {

    case 'update':
        if (SEC_checkToken()) {
            $display .= COM_siteHeader ('menu', $LANG32[13]);
            $display .= PLUGINS_update($pi_name);
            $display .= COM_siteFooter();
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally update plugin $pi_name and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'delete':
        if (SEC_checkToken()) {
            $display .= COM_siteHeader ('menu', $LANG32[30]);
            $display .= PLUGINS_unInstall($pi_name);
            $token = SEC_createToken();
            $display .= PLUGINS_list($token);
            $display .= COM_siteFooter ();
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally delete plugin $pi_name and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'remove':
        if (SEC_checkToken()) {
            $display .= COM_siteHeader ('menu', $LANG32[30]);
            $display .= PLUGINS_remove($pi_name);
            $token = SEC_createToken();
            $display .= PLUGINS_list($token);
            $display .= COM_siteFooter ();
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally remove plugin $pi_name and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        $display .= COM_siteHeader ('menu', $LANG32[5]);

        $msg = COM_getMessage();
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
