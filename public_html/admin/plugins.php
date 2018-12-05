<?php
/**
* glFusion CMS
*
* glFusion plugin administration page
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*   Mark A. Howard  mark AT usable-web DOT com
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Authors: Tony Bibbs        - tony AT tonybibbs DOT com
*           Mark Limburg      - mlimburg AT users DOT sourceforge DOT net
*           Jason Whittenburg - jwhitten AT securitygeeks DOT com
*           Dirk Haun         - dirk AT haun-online DOT de
*
*/

@ini_set('opcache.enable','0');

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database;
use \glFusion\FileSystem;

// Number of plugins to list per page
// We use 25 here instead of the 50 entries in other lists to leave room
// for the list of uninstalled plugins.

define ('PLUGINS_PER_PAGE', 25);

$display = '';
$page    = '';
$pageTitle = '';

if (!SEC_hasrights ('plugin.edit')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[38],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to access the plugin administration screen.");
    echo $display;
    exit;
}

require_once $_CONF['path_system'].'lib-install.php';

// list of directories to ignore
$glfIgnore = array('.','..','.svn','.git','CVS','cgi-bin','.htaccess','robots.txt');

$glfPlugins = array(
    'bad_behavior2',
    'calendar',
    'captcha',
    'ckeditor',
    'commentfeeds',
    'filemgmt',
    'forum',
    'links',
    'mediagallery',
    'polls',
    'spamx',
    'staticpages'
);

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
        'form_action_url'   =>  $_CONF['site_admin_url'] .'/plugins.php',
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

    $db = Database::getInstance();

    $stmt = $db->conn->query("SELECT pi_name,pi_version,pi_gl_version,pi_enabled,pi_homepage FROM `{$_TABLES['plugins']}`");

    while ($P = $stmt->fetch(Database::ASSOCIATIVE)) {
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
        $P['bundled'] = (isset($P['pi_name']) && in_array($P['pi_name'], $glfPlugins)) ? 1 : 0;
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
    global $_CONF, $_TABLES, $_PLUGINS, $_PLUGIN_INFO, $LANG32, $glfIgnore;

    $plugins_dir = $_CONF['path'] . 'plugins/';
    $fd = opendir($plugins_dir);
    while (($pi_name = @readdir ($fd)) == TRUE) {
        if ((!in_array($pi_name, $glfIgnore)) && is_dir($plugins_dir . $pi_name)) {
            clearstatcache ();
            if (!array_key_exists($pi_name,$_PLUGIN_INFO)) {
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
    CACHE_clear();
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

    $msg = '';
    if (PLG_uninstall ($pi_name)) {
        $msg = 45;
        $retval .= COM_showMessage (45);
    } else {
        $msg = 95;
        $retval .= COM_showMessage (95);
    }
    CACHE_clear();

    if ( $msg != '' ) {
        COM_setMsg($MESSAGE[$msg],'error');
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
    global $_CONF, $LANG32, $MESSAGE;

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
        $msg = 91;
        $retval .= COM_showMessage(91);
    }
    CACHE_clear();

    if ( $msg != '' ) {
        COM_setMsg($MESSAGE[$msg],'error');
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
    global $_TABLES, $_PLUGINS, $_PLUGIN_INFO, $_DB_table_prefix;

    $db = Database::getInstance();

    $currentStatus = array();
    $pluginsToProcess = array();

    if (!isset($pluginarray) || !is_array($pluginarray) ) {
        $pluginarray = array();
    }
    if ( !isset( $plugin_name_arr ) || !is_array( $plugin_name_arr ) ) {
        $plugin_name_arr = array();
    }

    // get current status
    $stmt = $db->conn->query("SELECT * FROM `{$_TABLES['plugins']}`");
    while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
        $currentStatus[$row['pi_name']] = $row['pi_enabled'];
    }
    foreach( $pluginarray AS $plugin => $junk ) {
        if ( $currentStatus[$plugin] == 1 ) { // was enabled...
            if ( !isset($plugin_name_arr[$plugin] ) ) {
                // was enabled - asking it to be disabled
                $pluginsToProcess[$plugin] = 0;
            }
        } else { // currently disabled
            if ( isset($plugin_name_arr[$plugin] ) ) {
                // was disabled - now marked as enable
                $pluginsToProcess[$plugin] = 1;
            }
        }
    }
    foreach ( $pluginsToProcess AS $plugin => $status ) {
        if ( $status == 0 ) {
            // disable plugin
            $rc = PLG_enableStateChange ($plugin, false);
            if ( $rc != 99 ) {
                $db->conn->update($_TABLES['plugins'],array('pi_enabled' => 0),array('pi_name'=>$plugin));
                $i = array_search($plugin,$_PLUGINS);
                unset($_PLUGINS[$i]);
            }
            $_PLUGIN_INFO[$plugin]['pi_enabled'] = 0;
        } else {
            $db->conn->update($_TABLES['plugins'],array('pi_enabled' => 1),array('pi_name'=>$plugin));
            $_PLUGIN_INFO[$plugin]['pi_version'] = $db->getItem($_TABLES['plugins'],'pi_version',array('pi_name' => $plugin));
            $_PLUGIN_INFO[$plugin]['pi_enabled'] = 1;
            $_PLUGINS[] = $plugin;
            PLG_enableStateChange ($plugin, true);
        }
    }
    CACHE_clear();
    return;
}

/**
* Main driver to handle the uploaded plugin
*
* Determines if a new style (supports automated installer) or
* an old style.
*
* @return   string              Formatted HTML containing the page body
*
*/
function PLUGINS_processUpload()
{
    global $_CONF, $_PLUGINS, $_PLUGIN_INFO, $_TABLES, $pluginData, $LANG_ADMIN, $LANG32, $_DB_dbms, $_DB_table_prefix,$_IMAGE_TYPE;

    $retval = '';
    $upgrade = false;

    $fs = new FileSystem();

    $db = Database::getInstance();

    if (count($_FILES) > 0 && $_FILES['pluginfile']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload = new upload();

        if (isset ($_CONF['debug_image_upload']) && $_CONF['debug_image_upload']) {
            $upload->setLogFile ($_CONF['path'] . 'logs/error.log');
            $upload->setDebug (true);
        }
        $upload->setMaxFileUploads (1);
        $upload->setMaxFileSize(25165824);
        $upload->setAllowedMimeTypes (array (
                'application/x-gzip'=> '.gz,.gzip,tgz',
                'application/zip'   => '.zip',
                'application/octet-stream' => '.gz,.gzip,.tgz,.zip,.tar,.tar.gz',
                'application/x-tar' => '.tar,.tar.gz,.gz',
                'application/x-gzip-compressed' => '.tar.gz,.tgz,.gz',
                ));
        $upload->setFieldName('pluginfile');

        if (!$upload->setPath($_CONF['path_data'] . 'temp')) {
            return _pi_errorBox($upload->printErrors(false));
        }

        $filename = $_FILES['pluginfile']['name'];

        $upload->setFileNames($filename);
        $upload->uploadFiles();

        if ($upload->areErrors()) {
            return PLUGINS_uploadError($upload->printErrors(false));
         }
        $Finalfilename = $_CONF['path_data'] . 'temp/' . $filename;

    } else {
        return PLUGINS_uploadError($LANG32[46]);
    }

    // decompress into temp directory
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 60 );
    }

    $tmp = $fs->mkTmpDir();
    if ($tmp === false) {
        return PLUGINS_uploadError($LANG32[47]);
    }
    if ( !COM_decompress($Finalfilename,$_CONF['path_data'].$tmp) ) {
        $fs->deleteDir($_CONF['path_data'].$tmp);
        return PLUGINS_uploadError($LANG32[48]);
    }
    @unlink($Finalfilename);

    // read XML data file, places in $pluginData;

    $pluginData = array();
    $rc = _pi_parseXML($_CONF['path_data'].$tmp);

    if ( $rc == -1 ) {
        // no xml file found
        $fs->deleteDir($_CONF['path_data'].$tmp);
        return PLUGINS_uploadError($LANG32[40]);
    }

    if ( !isset($pluginData['id']) || !isset($pluginData['version']) ) {
        $fs->deleteDir($_CONF['path_data'].$tmp);
        return PLUGINS_uploadError($LANG32[40]);
    }

    // proper glfusion version
    if (!COM_checkVersion(GVERSION, $pluginData['glfusionversion'])) {
        $fs->deleteDir($_CONF['path_data'].$tmp);
        return PLUGINS_uploadError(sprintf($LANG32[49],$pluginData['glfusionversion']));
    }

    if ( !COM_checkVersion(phpversion (),$pluginData['phpversion'])) {
        $retval .= sprintf($LANG32[50],$pluginData['phpversion']);
        $fs->deleteDir($_CONF['path_data'].$tmp);
        return PLUGINS_uploadError(sprintf($LANG32[50],$pluginData['phpversion']));
    }

    // check prerequisites
    $errors = '';
    if ( isset($pluginData['requires']) && is_array($pluginData['requires']) ) {
        foreach ($pluginData['requires'] AS $reqPlugin ) {
            if ( strstr($reqPlugin,",") !== false ) {
                list($reqPlugin, $required_ver) = @explode(',', $reqPlugin);
            } else {
                $reqPlugin = $reqPlugin;
                $required_ver = '';
            }
            if (!isset($_PLUGIN_INFO[$reqPlugin]) || $_PLUGIN_INFO[$reqPlugin]['pi_enabled'] == 0 ) {
                // required plugin not installed
                $errors .= sprintf($LANG32[51],$pluginData['id'],$reqPlugin,$reqPlugin);
            } elseif (!empty($required_ver)) {
                $installed_ver = $_PLUGIN_INFO[$reqPlugin]['pi_version'];
                if (!COM_checkVersion($installed_ver, $required_ver)) {
                    // required plugin installed, but wrong version
                    $errors .= sprintf($LANG32[90],$required_ver,$reqPlugin,$installed_ver,$reqPlugin);
                }
            }
        }
    }

    if ( $errors != '' ) {
        $fs->deleteDir($_CONF['path_data'].$tmp);
        return PLUGINS_uploadError($errors);
    }
    // check if plugin already exists
    // if it does, check that this is an upgrade
    // if not, error
    // else validate we really want to upgrade

    $P = $db->conn->fetchAssoc("SELECT * FROM `{$_TABLES['plugins']}` WHERE pi_name = ?", array($pluginData['id']));

    if ($P !== false) {
        if ($P['pi_version'] == $pluginData['version'] ) {
            $fs->deleteDir($_CONF['path_data'].$tmp);
            return PLUGINS_uploadError(sprintf($LANG32[52],$pluginData['id']));
        }
        // if we are here, it must be an upgrade or disabled plugin....
        $rc = COM_checkVersion($pluginData['version'],$P['pi_version']);
        if ( $rc < 1 ) {
            $fs->deleteDir($_CONF['path_data'].$tmp);
            return PLUGINS_uploadError(sprintf($LANG32[53],$pluginData['id'],$pluginData['version'],$P['pi_version']));
        }
        if ( $P['pi_enabled'] != 1 ) {
            $fs->deleteDir($_CONF['path_data'].$tmp);
            return PLUGINS_uploadError($LANG32[72]);
        }

        $upgrade = true;
    }

    $permError = 0;
    $permErrorList = '';
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }

    // check for directory with plugin.xml
    $pluginTmpDir = '';
    if ( is_dir($_CONF['path_data'].$tmp.'/'.$pluginData['id'])) {
        $pluginTmpDir = $_CONF['path_data'].$tmp.'/'.$pluginData['id'] . '/';
    } else {
        foreach (glob($_CONF['path_data'].$tmp.'/'.$pluginData['id'].'*') as $filename) {
            if ( is_dir($filename)) {
                if ( file_exists($filename.'/plugin.xml')) {
                    $pluginTmpDir = $filename . '/';
                }
            }
        }
    }
    if ( $pluginTmpDir == '' ) {
        $permError = 1;
        $permErrorLisg .= 'Unable to locate temporary plugin directory';
        $fs->deleteDir($_CONF['path_data'].$tmp);
        return PLUGINS_uploadError($errorMessage);
    }

    // test copy to proper directories
    $rc = $fs->testCopy($pluginTmpDir, $_CONF['path'].'plugins/'.$pluginData['id']);
    if ($rc === false) {
        $permError = 1;
        $failed = $fs->getErrorFiles();
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }

    $rc = $fs->testCopy($pluginTmpDir.'admin/', $_CONF['path_admin'].'plugins/'.$pluginData['id']);
    if ( $rc === false ) {
        $permError = 1;
        $failed = $fs->getErrorFiles();
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }

    $fnCustomDir = 'plugin_custom_public_dir_'.$pluginData['id'];
    if ( function_exists($fnCustomDir)) {
        $public_dir_name = $fnCustomDir();
    } else {
        $public_dir_name = $pluginData['id'];
    }
    if ($public_dir_name == '' ) $public_dir_name = $pluginData['id'];

    $rc = $fs->testCopy($pluginTmpDir.'public_html/', $_CONF['path_html'].$public_dir_name);
    if ($rc === false) {
        $permError = 1;
        $failed = $fs->getErrorFiles();
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }

    if ( $permError != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];
        $fs->deleteDir($_CONF['path_data'].$tmp);
        return PLUGINS_uploadError($errorMessage);
    }

    USES_lib_admin();

    $menu_arr = array (
                    array('url' => $_CONF['site_admin_url'],
                          'text' => $LANG_ADMIN['admin_home']));

    $T = new Template($_CONF['path_layout'] . 'admin/plugins');
    $T->set_file('form','plugin_upload_confirm.thtml');

    $T->set_var('admin_menu',ADMIN_createMenu(
        $menu_arr,
        $pluginData['id'] . ' ' . $LANG32[62],
        $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
    ));

    $T->set_var(array(
        'form_action_url'   => $_CONF['site_admin_url'] .'/plugins.php',
        'action'            => 'processupload',
        'pi_name'           => $pluginData['id'],
        'pi_version'        => $pluginData['version'],
        'pi_url'            => $pluginData['url'],
        'pi_gl_version'     => $pluginData['glfusionversion'],
        'pi_desc'           => $pluginData['description'],
        'pi_author'         => $pluginData['author'],
        'plugin_old_version' => isset($P['pi_version']) ? $P['pi_version'] : '',
        'upgrade'           => $upgrade,
        'temp_dir'          => $tmp,
    ));

    $retval .= $T->parse('output', 'form');
    return $retval;
}


/**
* Copies and installs new style plugins
*
* Copies all files the proper place and runs the automated installer
* or upgrade.
*
* @return   string              Formatted HTML containing the page body
*
*/
function PLUGINS_post_uploadProcess() {

    global $_CONF, $_PLUGINS, $_PLUGIN_INFO, $_TABLES, $pluginData, $LANG32,$_DB_dbms, $_DB_table_prefix ;

    $retval = '';
    $upgrade = false;
    $masterErrorCount   = 0;
    $masterErrorMsg     = '';

    $pluginData = array();
    $pluginData['id']               = COM_applyFilter($_POST['pi_name']);
    $pluginData['name']             = $pluginData['id'];
    $pluginData['version']          = COM_applyFilter($_POST['pi_version']);
    $pluginData['url']              = COM_applyFilter($_POST['pi_url']);
    $pluginData['glfusionversion']  = COM_applyFilter($_POST['pi_gl_version']);
    $upgrade                        = COM_applyFilter($_POST['upgrade'],true);
    $tdir                           = COM_applyFilter($_POST['temp_dir']);
    $tdir = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '',$tdir );
    $tdir = str_replace( '..', '', $tdir );
    $tmp = $_CONF['path_data'].$tdir;

    $pluginData = array();
    $rc = _pi_parseXML($tmp);
    if ( $rc == -1 ) {
        // no xml file found
        return _pi_errorBox($LANG32[74]);
    }

    $fs = new FileSystem();

    clearstatcache();

    $permError = 0;
    $permErrorList = '';

    // copy to proper directories

    if ( defined('DEMO_MODE') ) {
        $fs->deleteDir($tmp);
        COM_setMessage(503);
        echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php');
        exit;
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }

    $pluginTmpDir = '';
    if ( is_dir($tmp.'/'.$pluginData['id'])) {
        $pluginTmpDir = $tmp.'/'.$pluginData['id'] . '/';
    } else {
        foreach (glob($tmp.'/'.$pluginData['id'].'*') as $filename) {
            if ( is_dir($filename)) {
                if ( file_exists($filename.'/plugin.xml')) {
                    $pluginTmpDir = $filename . '/';
                }
            }
        }
    }
    if ( $pluginTmpDir == '' ) {
        $permError = 1;
        $permErrorLisg .= 'Unable to locate temporary plugin directory';
        $fs->deleteDir($_CONF['path_data'].$tmp);
        return PLUGINS_uploadError($errorMessage);
    }

    $rc = $fs->dirCopy($pluginTmpDir, $_CONF['path'].'plugins/'.$pluginData['id']);
    if ($rc === false) {
        $t = $fs->getErrorFiles();
        $permError++;
        if ( is_array($t) ) {
            foreach ($t AS $failedFile) {
                $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'plugins/'.$pluginData['id']);
            }
        }
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if ( file_exists($pluginTmpDir.'admin/') ) {
        $rc = $fs->dirCopy($pluginTmpDir.'admin/', $_CONF['path_admin'].'plugins/'.$pluginData['id']);
        if ($rc === false) {
            $permError++;
            $t = $fs->getErrorFiles();
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'plugins/'.$pluginData['id']);
                }
            }
        }
        $fs->deleteDir($_CONF['path'].'plugins/'.$pluginData['id'].'/admin/');
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }

    $fnCustomDir = 'plugin_custom_public_dir_'.$pluginData['id'];
    if ( function_exists($fnCustomDir)) {
        $public_dir_name = $fnCustomDir();
    } else {
        $public_dir_name = $pluginData['id'];
    }
    if ($public_dir_name == '' ) $public_dir_name = $pluginData['id'];

    if ( file_exists($pluginTmpDir.'public_html/') ) {

        $rc = $fs->dirCopy($pluginTmpDir.'public_html/', $_CONF['path_html'].$public_dir_name);

        if ($rc === false) {
            $t = $fs->getErrorFiles();
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'plugins/'.$pluginData['id']);
                }
            }
        }
        $fs->deleteDir($_CONF['path'].'plugins/'.$pluginData['id'].'/public_html/');
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if ( file_exists($pluginTmpDir.'themefiles/') ) {
        // determine where to copy them, first check to see if layout was defined in xml
        if ( isset($pluginData['layout']) && $pluginData['layout'] != '') {
            $destinationDir = $_CONF['path_html'] . 'layout/' . $pluginData['layout'] .'/';
            $fs->mkDir($destinationDir);
        } else {
            $destinationDir = $_CONF['path_html'] . 'layout/cms/'.$pluginData['id'].'/';
        }
        $rc = $fs->dirCopy($pluginTmpDir.'themefiles/', $destinationDir);
        if ($rc === false) {
            $t = $fs->getErrorFiles();
            $permError++;
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'plugins/'.$pluginData['id']);
                }
            }
        }
        $fs->deleteDir($_CONF['path'].'plugins/'.$pluginData['id'].'/themefiles/');
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if ( $permError != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];
        $fs->deleteDir($tmp);
        return PLUGINS_uploadError($errorMessage);
    }

    if ( isset($pluginData['dataproxydriver']) && $pluginData['dataproxydriver'] != '' ) {
        if ( file_exists($_CONF['path'].'plugins/dataproxy/drivers/') ) {
            $src  = $pluginTmpDir.'dataproxy/'.$pluginData['dataproxydriver'];
            $dest = $_CONF['path'].'plugins/dataproxy/drivers/'.$pluginData['dataproxydriver'];
            @copy($src,$dest);
        }
    }

    $fs->deleteDir($tmp);

    if ( isset($pluginData['renamedist']) && is_array($pluginData['renamedist']) ) {
        foreach ($pluginData['renamedist'] AS $fileToRename) {
            $rc = true;
            if (strncmp($fileToRename,'admin',5) == 0 ) {
                // we have a admin file to rename....
                $absoluteFileName = substr($fileToRename,6);
                $lastSlash = strrpos($fileToRename,'/');
                if ( $lastSlash === false ) {
                    continue;
                }
                $pathTo = substr($fileToRename,0,$lastSlash);
                if ( $pathTo != '' ) {
                    $pathTo .= '/';
                }
                $lastSlash++;
                $fileNameDist = substr($fileToRename,$lastSlash);

                $lastSlash = strrpos($fileNameDist,'.');
                if ( $lastSlash === false ) {
                    continue;
                }
                $fileName = substr($fileNameDist,0,$lastSlash);

                if ( !file_exists($_CONF['path_admin'].'plugins/'.$pluginData['id'].$pathTo.$fileName) ) {
                    COM_errorLog("PLG-INSTALL: Renaming " . $fileNameDist ." to " . $_CONF['path_admin'].'plugins/'.$pluginData['id'].$pathTo.$fileName);
                    $rc = @copy ($_CONF['path_admin'].'plugins/'.$pluginData['id'].$absoluteFileName,$_CONF['path_admin'].'plugins/'.$pluginData['id'].$pathTo.$fileName);
                    if ( $rc === false ) {
                        COM_errorLog("PLG-INSTALL: Unable to copy ".$_CONF['path_admin'].'plugins/'.$pluginData['id'].$absoluteFileName." to ".$_CONF['path_admin'].'plugins/'.$pluginData['id'].$pathTo.$fileName);
                        $masterErrorCount++;
                        $masterErrorMsg .= sprintf($LANG32[75],$_CONF['path_admin'].'plugins/'.$pluginData['id'].$absoluteFileName,$_CONF['path_admin'].'plugins/'.$pluginData['id'].$pathTo.$fileName);
                    }
                }
            } elseif (strncmp($fileToRename,'public_html',10) == 0 ) {
                // we have a public_html file to rename...
                $absoluteFileName = substr($fileToRename,11);
                $lastSlash = strrpos($absoluteFileName,'/');
                if ( $lastSlash !== false ) {
                    $pathTo = substr($absoluteFileName,0,$lastSlash);
                    if ( $pathTo != '' ) {
                        $pathTo .= '/';
                    }
                } else {
                    $pathTo = '';
                }
                $lastSlash++;
                $fileNameDist = substr($absoluteFileName,$lastSlash);

                $lastSlash = strrpos($fileNameDist,'.');
                if ( $lastSlash === false ) {
                    continue;
                }
                $fileName = substr($fileNameDist,0,$lastSlash);

                if ( !file_exists($_CONF['path_html'].$public_dir_name.$pathTo.$fileName) ) {
                    COM_errorLog("PLG-INSTALL: Renaming " . $fileNameDist ." to " . $_CONF['path_html'].$pluginData['id'].$pathTo.$fileName);
                    $rc = @copy ($_CONF['path_html'].$public_dir_name.$absoluteFileName,$_CONF['path_html'].$public_dir_name.$pathTo.$fileName);
                    if ( $rc === false ) {
                        COM_errorLog("PLG-INSTALL: Unable to copy ".$_CONF['path_html'].$public_dir_name.$absoluteFileName." to ".$_CONF['path_html'].$public_dir_name.$pathTo.$fileName);
                        $masterErrorCount++;
                        $masterErrorMsg .= sprintf($LANG32[75],$_CONF['path_html'].$public_dir_name.$absoluteFileName,$_CONF['path_html'].$public_dir_name.$pathTo.$fileName);
                    }
                }
            } else {
                // must be some other file relative to the plugin/pluginname/ directory
                $absoluteFileName = $fileToRename;
                $lastSlash = strrpos($fileToRename,'/');

                $pathTo = substr($fileToRename,0,$lastSlash);
                if ( $pathTo != '' ) {
                    $pathTo .= '/';
                }
                $lastSlash++;
                $fileNameDist = substr($fileToRename,$lastSlash);

                $lastSlash = strrpos($fileNameDist,'.');
                if ( $lastSlash === false ) {
                    continue;
                }
                $fileName = substr($fileNameDist,0,$lastSlash);
                if ( !file_exists($_CONF['path'].'plugins/'.$pluginData['id'].'/'.$pathTo.$fileName) ) {
                    COM_errorLog("PLG-INSTALL: Renaming " . $fileNameDist ." to " . $_CONF['path'].'plugins/'.$pluginData['id'].'/'.$pathTo.$fileName);
                    $rc = @copy ($_CONF['path'].'plugins/'.$pluginData['id'].'/'.$absoluteFileName,$_CONF['path'].'plugins/'.$pluginData['id'].'/'.$pathTo.$fileName);
                    if ( $rc === false ) {
                        COM_errorLog("PLG-INSTALL: Unable to copy ".$_CONF['path'].'plugins/'.$pluginData['id'].'/'.$absoluteFileName." to ".$_CONF['path'].'plugins/'.$pluginData['id'].'/'.$pathTo.$fileName);
                        $masterErrorCount++;
                        $masterErrorMsg .= sprintf($LANG32[75],$_CONF['path'].'plugins/'.$pluginData['id'].'/'.$absoluteFileName,$_CONF['path'].'plugins/'.$pluginData['id'].'/'.$pathTo.$fileName);
                    }
                }
            }

        }
    }

    // handle masterErrorCount here, if not 0, display error and ask use to manually install via the plugin admin screen.
    // all files have been copied, so all they really should need to do is fix the error above and then run.

    if ( $masterErrorCount != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$masterErrorMsg.'<br />'.$LANG32[44];
        return PLUGINS_uploadError($errorMessage);
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if ( $upgrade == 0 ) { // fresh install

        USES_lib_install();

        $pi_name         = $pluginData['id'];
        $pi_display_name = $pluginData['name'];
        $pi_version      = $pluginData['version'];
        $gl_version      = $pluginData['glfusionversion'];
        $pi_url          = $pluginData['url'];

        if ( file_exists($_CONF['path'].'plugins/'.$pluginData['id'].'/autoinstall.php') ) {

            require_once $_CONF['path'].'plugins/'.$pluginData['id'].'/autoinstall.php';

            $ret = INSTALLER_install($INSTALL_plugin[$pi_name]);

            if ( $ret == 0 ) {
                CACHE_clear();
                COM_setMessage(44);
                echo COM_refresh ($_CONF['site_admin_url']. '/plugins.php');
                exit;
            } else {
                return PLUGINS_uploadError($LANG32[54]);
            }
        } else {
            return PLUGINS_uploadError($LANG32[55]);
        }
    } else {
        // upgrade - force refresh to load new functions.inc
        header("Location:" . $_CONF['site_admin_url'] . '/plugins.php?upgradeplugin=x&pi=' .$pluginData['id']);
        exit;
    }

    CACHE_clear();
    // show status (success or fail)
    return $retval;
}


/**
* Calls the plugins update routines
*
* @param    string              Plugin name
* @return   string              Formatted HTML containing the page body
*
*/
function PLUGINS_upload_update ($pi_name)
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
            echo COM_refresh ($_CONF['site_admin_url'].'/plugins.php');
        } else {  // Plugin returned a message number
            COM_setMessage($result);
            echo COM_refresh ($_CONF['site_admin_url'].'/plugins.php?plugin='.$pi_name);
        }
    } else {  // Plugin function returned a false
        $retval .= COM_showMessage(95);
    }
    $c = glFusion\Cache::getInstance()->deleteItemsByTags(array('menu','plugin'));
    return $retval;
}


function PLUGINS_uploadError($errMsg = '' )
{
    global $_CONF,$LANG32;

    $retval = '';

    $retval .= COM_showMessageText($errMsg, $LANG32[56], true, 'error');
    $token = SEC_createToken();
    $retval .= PLUGINS_list($token);

    return $retval;
}

// MAIN ========================================================================

$pageTitle = $LANG32[5];

$action = '';
$expected = array('update','delete','cancel','remove','processupload','installplugin','upgradeplugin');
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

if ( isset($_POST['cancel']) ) {
    if ( isset($_POST['temp_dir']) ) {

        $fs = new FileSystem();

        $tmpDir = COM_sanitizeFilename(COM_applyFilter($_POST['temp_dir']));

        $len = strlen($_CONF['path_data']);

        $tDir = $_CONF['path_data'] . $tmpDir;
        if ( is_dir($tDir)) {
            $fs->deleteDir($tDir);
        } else {
            COM_errorLog("PLG-Install: Directory mismatch after cancel operation - Temp directory not deleted");
        }
    }
    if ( isset($_POST['pi_name']) ) {
        $pi_name = COM_applyFilter($_POST['pi_name']);

        @unlink($_CONF['path_data'] . 'temp/' . $pi_name . '*');
    }
    $action = '';
}


switch ($action) {
    case 'update':
        if (SEC_checkToken()) {
            $pageTitle = $LANG32[13];
            $page .= PLUGINS_update($pi_name);
        } else {
            COM_accessLog("User {$_USER['username']} tried to update plugin $pi_name and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'delete':
        if (SEC_checkToken()) {
            if ( !function_exists('plugin_autouninstall_'.$pi_name) && file_exists($_CONF['path'].'plugins/'.$pi_name.'/autoinstall.php') ) {
                require_once $_CONF['path'].'plugins/'.$pi_name.'/autoinstall.php';
            }
            $page  = PLUGINS_unInstall($pi_name);
            $token = SEC_createToken();
            $page .= PLUGINS_list($token);
            $pageTitle = $LANG32[30];
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally delete plugin $pi_name and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'remove':
        if (SEC_checkToken()) {
            $pageTitle = $LANG32[30];
            $page .= PLUGINS_remove($pi_name);
            $token = SEC_createToken();
            $page .= PLUGINS_list($token);
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally remove plugin $pi_name and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'processupload' :
        $page .= PLUGINS_processUpload();
        break;

    case 'installplugin' :
        $page .= PLUGINS_post_uploadProcess();
        break;

    case 'upgradeplugin' :
        if ( isset($_GET['pi']) ) {
            $pi = COM_sanitizeID(COM_applyFilter($_GET['pi']));
        } else {
            $pi = '';
        }
        $page .= PLUGINS_upload_update($pi);
        break;

    default:
        $msg = COM_getMessage();
        $plugin = '';
        if (isset ($_POST['plugin'])) {
            $plugin = COM_applyFilter ($_POST['plugin']);
        } else if (isset ($_GET['plugin'])) {
            $plugin = COM_applyFilter ($_GET['plugin']);
        }
        if ( $msg > 0 ) {
            $msgType = 'info';
            $msgPersist = 'false';
            if ( $msg == 72 ) {
                $msgType = 'error';
                $msgPersist = 'true';
            }
        }
        $page .= ($msg > 0) ? COM_showMessage($msg,$plugin,'',$msgPersist,$msgType) : '';
        $token = SEC_createToken();
        $page .= PLUGINS_list($token);
        break;

}

$display = COM_siteHeader ('menu', $pageTitle);
$display .= $page;
$display .= COM_siteFooter();
echo $display;

?>
