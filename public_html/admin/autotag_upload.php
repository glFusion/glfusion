<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | autotag_upload.php                                                       |
// |                                                                          |
// | glFusion Automated autotag installer                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2018 by the following authors:                        |
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

require_once '../lib-common.php';
require_once 'auth.inc.php';

$display = '';

if (!SEC_hasrights ('autotag.admin')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[38], $MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} attempted to access the autotag administration screen.");
    echo $display;
    exit;
}

USES_lib_install();

/**
* Main driver to handle the uploaded autotag
*
* Determines if a new style (supports automated installer) or
* an old style.
*
* @return   string              Formatted HTML containing the page body
*
*/
function processAutotagUpload()
{
    global $_CONF, $_PLUGINS, $_TABLES, $autotagData, $LANG32, $_DB_dbms, $_DB_table_prefix ;

    $retval = '';
    $upgrade = false;
    $errors = '';

    $fs = new \glFusion\FileSystem();

    if (count($_FILES) > 0 && $_FILES['autotagfile']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload = new upload();

        if (isset ($_CONF['debug_image_upload']) && $_CONF['debug_image_upload']) {
            $upload->setLogFile ($_CONF['path'] . 'logs/error.log');
            $upload->setDebug (true);
        }
        $upload->setMaxFileUploads (1);
        $upload->setMaxFileSize(4194304);
        $upload->setAllowedMimeTypes (array (
                'application/x-gzip'=> '.gz,.gzip,tgz',
                'application/zip'   => '.zip',
                ));
        $upload->setFieldName('autotagfile');

        if (!$upload->setPath($_CONF['path_data'] . 'temp')) {
            return _at_errorBox($upload->printErrors(false));
            exit;
        }

        $filename = COM_sanitizeFilename($_FILES['autotagfile']['name'],true);

        $upload->setFileNames($filename);
        $upload->uploadFiles();

        if ($upload->areErrors()) {
            return _at_errorBox($upload->printErrors(false));
            exit;
        }
        $Finalfilename = $_CONF['path_data'] . 'temp/' . $filename;

    } else {
        return _at_errorBox($LANG32[46]);
    }

    // decompress into temp directory
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 60 );
    }
    if (!($tmp = \glFusion\FileSystem::mkTmpDir())) {
        return _at_errorBox($LANG32[47]);
    }

    if ( !COM_decompress($Finalfilename,$_CONF['path_data'].$tmp) ) {
        \glFusion\FileSystem::deleteDir($_CONF['path_data'].$tmp);
        return _at_errorBox($LANG32[48]);
    }
    @unlink($Finalfilename);

    // read XML data file, places in $autotagData;

    $autotagData = array();
    $rc = _at_parseXML($_CONF['path_data'].$tmp);

    if ( $rc == -1 ) {
        // no xml file found
        \glFusion\FileSystem::deleteDir($_CONF['path_data'].$tmp);
        return _at_errorBox(sprintf($LANG32[49],$autotagData['glfusionversion']));
    }

    if ( !isset($autotagData['id']) || !isset($autotagData['version']) ) {
        \glFusion\FileSystem::deleteDir($_CONF['path_data'].$tmp);
        return _at_errorBox(sprintf($LANG32[49],$autotagData['glfusionversion']));
    }

    // proper glfusion version
    if (!COM_checkVersion(GVERSION, $autotagData['glfusionversion'])) {
        \glFusion\FileSystem::deleteDir($_CONF['path_data'].$tmp);
        return _at_errorBox(sprintf($LANG32[49],$autotagData['glfusionversion']));
    }

    if ( !COM_checkVersion(phpversion (),$autotagData['phpversion'])) {
        $retval .= sprintf($LANG32[50],$autotagData['phpversion']);
        \glFusion\FileSystem::deleteDir($_CONF['path_data'].$tmp);
        return _at_errorBox(sprintf($LANG32[50],$autotagData['phpversion']));
    }

    if ( $errors != '' ) {
        \glFusion\FileSystem::deleteDir($_CONF['path_data'].$tmp);
        return _at_errorBox($errors);
    }

    // check to see if an auto tag already exists...
    // removed so we can update existing auto tags
/*
    $result = DB_query("SELECT * FROM {$_TABLES['autotags']} WHERE tag='".DB_escapeString($autotagData['id'])."'");
    if ( DB_numRows($result) > 0 ) {
        _pi_deleteDir($_CONF['path_data'].$tmp);
        return _at_errorBox(sprintf($LANG32[52],$autotagData['id']));
    }
*/
    $permError = 0;
    $permErrorList = '';
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    // test copy to proper directories
    $autotagData['id'] = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '',$autotagData['id'] );

    $rc = $fs->testCopy($_CONF['path_data'].$tmp.'/'.$autotagData['id'].'/',
                                      $_CONF['path_system'].'autotags/');
    if ($rc === false) {
        $failed = $fs->getErrorFiles();
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }

    if ( $permError != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];
        \glFusion\FileSystem::deleteDir($_CONF['path_data'].$tmp);
        return _at_errorBox($errorMessage);
    }

    $T = new Template($_CONF['path_layout'] . 'admin/autotag');
    $T->set_file('form','autotag_upload_confirm.thtml');

    $T->set_var(array(
        'form_action_url'   => $_CONF['site_admin_url'] .'/autotag_upload.php',
        'action'            => 'processupload',
        'pi_name'           => $autotagData['id'],
        'pi_version'        => $autotagData['version'],
        'pi_url'            => $autotagData['url'],
        'pi_gl_version'     => $autotagData['glfusionversion'],
        'pi_desc'           => $autotagData['description'],
        'pi_author'         => $autotagData['author'],
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
function post_uploadProcess() {

    global $_CONF, $_PLUGINS, $_TABLES, $autotagData, $LANG32,$_DB_dbms, $_DB_table_prefix ;

    $retval = '';
    $upgrade = false;
    $masterErrorCount   = 0;
    $masterErrorMsg     = '';

    $fs = new \glFusion\FileSystem();

    $autotagData = array();
    $autotagData['id']               = COM_applyFilter($_POST['pi_name']);
    $autotagData['name']             = $autotagData['id'];
    $autotagData['version']          = COM_applyFilter($_POST['pi_version']);
    $autotagData['glfusionversion']  = COM_applyFilter($_POST['pi_gl_version']);
    $tdir                            = COM_applyFilter($_POST['temp_dir']);
    $tdir = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '',$tdir );
    $tdir = str_replace( '..', '', $tdir );
    $tmp = $_CONF['path_data'].$tdir;

    $autotagData = array();
    $rc = _at_parseXML($tmp);
    if ( $rc == -1 ) {
        // no xml file found
        return _at_errorBox($LANG32[74]);
    }

    clearstatcache();

    $permError = 0;
    $permErrorList = '';

    // copy to proper directories

    if ( defined('DEMO_MODE') ) {
        \glFusion\FileSystem::deleteDir($tmp);
        echo COM_refresh($_CONF['site_admin_url'] . '/autotag.php?msg=503');
        exit;
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }

    $autotagData['id'] = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '',$autotagData['id'] );

    $rc = $fs->fileCopy($tmp.'/'.$autotagData['id'].'.class.php', $_CONF['path_system'].'autotags/');
    if ( $rc === false ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];
        \glFusion\FileSystem::deleteDir($tmp);
        return _at_errorBox($errorMessage);
    }
    // copy template files, if any
    if ( isset($autotagData['template']) && is_array($autotagData['template']) ) {
        foreach ($autotagData['template'] AS $filename ) {
            $rc = $fs->fileCopy($tmp.'/'.$filename, $_CONF['path_system'].'autotags/');
            if ( $rc === false ) {
                @unlink ($_CONF['path_system'].$autotagData['id'].'.class.php');
                $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];
                \glFusion\FileSystem::deleteDir($tmp);
                return _at_errorBox($errorMessage);
            }
        }
    }
    $tag    = DB_escapeString($autotagData['id']);
    $desc   = DB_escapeString($autotagData['description']);
    $is_enabled = 1;
    $is_function = 1;
    $replacement = '';
    DB_query("REPLACE INTO {$_TABLES['autotags']} (tag,description,is_enabled,is_function,replacement) VALUES ('".$tag."','".$desc."',".$is_enabled.",".$is_function.",'')");

    \glFusion\FileSystem::deleteDir($tmp);

    CACHE_clear();
    // show status (success or fail)
    return $retval;
}


function _at_parseXML($tmpDirectory)
{
    global $_CONF, $autotagData;

    $filename = $tmpDirectory . '/autotag.xml';

    if (!($fp=@fopen($filename, "r"))) {
        return -1;
    }

    $autotagData = array();

    if (!($xml_parser = xml_parser_create()))
        return false;

    xml_set_element_handler($xml_parser,"_at_startElementHandler","_at_endElementHandler");
    xml_set_character_data_handler( $xml_parser, "_at_characterDataHandler");

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
function _at_startElementHandler ($parser,$name,$attrib) {
    global $autotagData;
    global $state;

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
        case 'TEMPLATE' :
            $state = 'template';
            break;
    }
}

function _at_endElementHandler ($parser,$name){
    global $autotagData;
    global $state;

    $state='';
}

function _at_characterDataHandler ($parser, $data) {
    global $autotagData;
    global $state;


    if (!$state) {
        return;
    }

    switch ($state) {
        case 'id' :
            $autotagData['id'] = $data;
            break;
        case 'pluginname' :
            $autotagData['name'] = $data;
            break;
        case 'pluginversion' :
            $autotagData['version'] = $data;
            break;
        case 'glfusionversion' :
            $autotagData['glfusionversion'] = $data;
            break;
        case 'phpversion' :
            $autotagData['phpversion'] = $data;
            break;
        case 'description' :
            $autotagData['description'] = $data;
            break;
        case 'url' :
            $autotagData['url'] = $data;
            break;
        case 'maintainer' :
            $autotagData['author'] = $data;
            break;
        case 'database' :
            $autotagData['database'] = $data;
            break;
        case 'template' :
            $autotagData['template'][] = $data;
            break;
    }
}

function _at_errorBox( $errMsg )
{
    global $_CONF,$LANG32;

    $retval = '';
    $retval .= '<div id="msgbox" style="width:95%;margin:10px;border:1px solid black;">';
    $retval .= '<div style="padding:5px;font-weight:bold;color:#FFFFFF;background:url('.$_CONF['layout_url'].'/images/header-bg.png) #1A3955;">';
    $retval .= $LANG32[86];
    $retval .= '</div>';
    $retval .= '<div style="padding:5px 15px 15px 15px;border-top:3px solid black;background:#E7E7E7;">';
    $retval .= $errMsg;
    $retval .= '</div>';
    $retval .= '</div>';

    $retval .= '&nbsp;&nbsp;&nbsp;<a href="'.$_CONF['site_admin_url'].'/autotag.php?list=x">'.$LANG32[71].'</a>';

    return $retval;
}

function _at_Header()
{
    global $_CONF, $LANG_ADMIN,$LANG32;

    $retval = '';

    $retval .= COM_startBlock($LANG32[87], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));


    return $retval;
}


/*
 * Main program processing
 */

$action = '';
$mode   = '';

if ( isset($_POST['action']) ) {
    $action = COM_applyFilter($_POST['action']);
}

if ( isset($_GET['mode']) ) {
    $mode = COM_applyFilter($_GET['mode']);
    $action = $mode;
    $_POST['submit'] = 1;
}

if ( isset($_POST['submit']) ) {
    switch ($action) {
        case 'processupload' :
            $display .= processAutotagUpload();
            break;
        case 'installautotag' :
            $display .= post_uploadProcess();
            echo COM_refresh($_CONF['site_admin_url'] .'/autotag.php?list=x');
            exit;
            break;
        default :
            echo COM_refresh($_CONF['site_admin_url'] .'/autotag.php?list=x');
            exit;
            break;
    }
} else if ( isset($_POST['cancel']) ) {
    if ( isset($_POST['temp_dir']) ) {
        $tmpDir = COM_applyFilter($_POST['temp_dir']);

        $len = strlen($_CONF['path_data']);
        if ( strncmp($_CONF['path_data'],$tmpDir,$len-1) == 0 ) {
            \glFusion\FileSystem::deleteDir($tmpDir);
        } else {
            COM_errorLog("Install: Directory mismatch after cancel operation - Temp directory not deleted");
        }
    }
    if ( isset($_POST['pi_name']) ) {
        $pi_name = COM_sanitizeFilename(COM_applyFilter($_POST['pi_name']),true);

        @unlink($_CONF['path_data'] . 'temp/' . $pi_name . '*');
    }
    echo COM_refresh($_CONF['site_admin_url'] .'/autotag.php?list=x');
    exit;

} else {
    echo COM_refresh($_CONF['site_admin_url'] .'/autotag.php?list=x');
    exit;

}

echo COM_siteHeader('menu');
echo _at_Header();
$display .= COM_siteFooter();
echo $display;
exit;
?>