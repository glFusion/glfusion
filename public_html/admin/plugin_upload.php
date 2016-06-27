<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | plugin_upload.php                                                        |
// |                                                                          |
// | glFusion Automated plugin installer                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2015 by the following authors:                        |
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

if (!SEC_hasrights ('plugin.edit')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[38],$MESSAGE[30],true);
    $display .= COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to illegally access the plugin administration screen.");
    echo $display;
    exit;
}

require_once $_CONF['path_system'].'lib-install.php';

/**
* Process old style plugins
*
* Displays the confirmation screen if an old style plugin is detected.
*
* @param    string  $tmpDir     Location of the temporary directory
* @return   string              Formatted HTML containing the page body
*
*/
function processOldPlugin( $tmpDir )
{
    global $_CONF, $_PLUGINS, $_TABLES, $pluginData, $_DB_dbms, $_DB_table_prefix,$LANG32 ;

    $retval = '';
    $pi_name = '';
    $dirCount = 0;

    if (!$dh = @opendir($_CONF['path_data'].$tmpDir)) {
        _pi_deleteDir($_CONF['path_data'].$tmpDir);
        return ( _pi_errorBox( $LANG32[39] ));

    }

    while ( ( $file = readdir($dh) ) != false ) {
        if ( $file == '..' || $file == '.' ) {
            continue;
        }

        if ( @is_dir($_CONF['path_data'].$tmpDir . '/' . $file) ) {
            $pi_name = $file;
            $dirCount++;
        }
    }
    closedir($dh);

    if ( $pi_name == '' || $dirCount > 1) {
        _pi_deleteDir($_CONF['path_data'].$tmpDir);
        return _pi_errorBox($LANG32[40]);
    }

    $result = DB_query("SELECT * FROM {$_TABLES['plugins']} WHERE pi_name='".DB_escapeString($pi_name)."' LIMIT 1");
    if ( DB_numRows($result) > 0 ) {
        $P = DB_fetchArray($result);

        if ( $P['pi_enabled'] != 1 ) {
            _pi_deleteDir($_CONF['path_data'].$tmpDir);
            return _pi_errorBox($LANG32[72]);
        }

        $upgrade = true;
    }

    $T = new Template($_CONF['path_layout'] . 'admin/plugins');
    $T->set_file('form','plugin_upload_old_confirm.thtml');

    $T->set_var(array(
        'form_action_url'    => $_CONF['site_admin_url'] .'/plugin_upload.php',
        'action'             => 'processoldupload',
        'pi_name'            => $pi_name,
        'plugin_old_version' => $P['pi_version'],
        'upgrade'            => $upgrade,
        'temp_dir'           => $tmpDir,
    ));

    $retval .= $T->parse('output', 'form');
    return $retval;
}

/**
* Copies the contents of an old style plugin to the proper directories
*
*
* @return   string              Formatted HTML containing the page body
*
*/
function processOldPluginInstall(  )
{
    global $_CONF, $_PLUGINS, $_TABLES, $pluginData, $LANG32,$_DB_dbms, $_DB_table_prefix ;

    $retval = '';

    $pluginData = array();
    $pluginData['id']               = COM_applyFilter($_POST['pi_name']);
    $pluginData['name']             = $pluginData['id'];
    $upgrade                        = COM_applyFilter($_POST['upgrade'],true);
    $tdir                           = COM_applyFilter($_POST['temp_dir']);

    $tdir = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '',$tdir );
    $tdir = str_replace( '..', '', $tdir );

    $tmp = $_CONF['path_data'].$tdir;

    $permError = 0;
    $permErrorList = '';

    // test copy to proper directories
    list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/', $_CONF['path'].'plugins/'.$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }
    list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/admin/', $_CONF['path_html'].'admin/plugins/'.$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }
    list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/public_html/', $_CONF['path_html'].$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }

    if ( file_exists($tmp.'/'.$pluginData['id'].'/themefiles/') ) {
        list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/themefiles/', $_CONF['path_html'].'layout/nouveau/');
        if ( $rc > 0 ) {
            $permError = 1;
            foreach($failed AS $filename) {
                $permErrorList .= sprintf($LANG32[41],$filename);
            }
        }
    }

    if ( file_exists($tmp.'/'.$pluginData['id'].'/system/') ) {
        list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/system/', $_CONF['path_system']);
        if ( $rc > 0 ) {
            $permError = 1;
            foreach($failed AS $filename) {
                $permErrorList .= sprintf($LANG32[41],$filename);
            }
        }
    }

    if ( $permError != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];

        _pi_deleteDir($tmp);
        return (_pi_errorBox($errorMessage));
    }

    clearstatcache();

    if ( defined('DEMO_MODE') ) {
        _pi_deleteDir($tmp);
        COM_setMessage(503);
        echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php');
        exit;
    }

    $permError = 0;
    $permErrorList = '';

    // copy to proper directories
    $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/', $_CONF['path'].'plugins/'.$pluginData['id']);
    list($success,$failed,$size,$faillist) = explode(',',$rc);
    if ( $failed > 0 ) {
        $permError++;
        $t = array();
        $t = explode('|',$faillist);
        if ( is_array($t) ) {
            foreach ($t AS $failedFile) {
                $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'plugins/'.$pluginData['id']);
            }
        }
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/admin/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/admin/', $_CONF['path_html'].'admin/plugins/'.$pluginData['id']);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path_html'].'admin/plugins/'.$pluginData['id']);
                }
            }
        }
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/public_html/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/public_html/', $_CONF['path_html'].$pluginData['id']);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path_html'].$pluginData['id']);
                }
            }
        }
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/themefiles/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/themefiles/', $_CONF['path_html'].'layout/nouveau/');
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG45,$failedFile,$_CONF['path_html'].'layout/nouveau/');
                }
            }
        }
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/system/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/system/', $_CONF['path_system']);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG45,$failedFile,$_CONF['path_system']);
                }
            }
        }
    }
    if ( $permError != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];
        _pi_deleteDir($tmp);
        return _pi_errorBox($errorMessage);
    }
    _pi_deleteDir($tmp);

    COM_setMessage(502);
    echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php');
    exit;
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
function processPluginUpload()
{
    global $_CONF, $_PLUGINS, $_PLUGIN_INFO, $_TABLES, $pluginData, $LANG_ADMIN, $LANG32, $_DB_dbms, $_DB_table_prefix,$_IMAGE_TYPE;

    $retval = '';
    $upgrade = false;

    if (count($_FILES) > 0 && $_FILES['pluginfile']['error'] != UPLOAD_ERR_NO_FILE) {
        require_once($_CONF['path_system'] . 'classes/upload.class.php');
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
                'application/x-tar' => '.tar,.tar.gz,.gz',
                'application/x-gzip-compressed' => '.tar.gz,.tgz,.gz',
                ));
        $upload->setFieldName('pluginfile');

        if (!$upload->setPath($_CONF['path_data'] . 'temp')) {
            return _pi_errorBox($upload->printErrors(false));
            exit;
        }

        $filename = $_FILES['pluginfile']['name'];

        $upload->setFileNames($filename);
        $upload->uploadFiles();

        if ($upload->areErrors()) {
            return _pi_errorBox($upload->printErrors(false));
            exit;
        }
        $Finalfilename = $_CONF['path_data'] . 'temp/' . $filename;

    } else {
        return _pi_errorBox($LANG32[46]);
    }

    // decompress into temp directory
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 60 );
    }
    if (!($tmp = _io_mktmpdir())) {
        return _pi_errorBox($LANG32[47]);
    }

    if ( !COM_decompress($Finalfilename,$_CONF['path_data'].$tmp) ) {
        _pi_deleteDir($_CONF['path_data'].$tmp);
        return _pi_errorBox($LANG32[48]);
    }
    @unlink($Finalfilename);

    // read XML data file, places in $pluginData;

    $pluginData = array();
    $rc = _pi_parseXML($_CONF['path_data'].$tmp);

    if ( $rc == -1 ) {
        // no xml file found
        return ( processOldPlugin( $tmp ) );
    }

    if ( !isset($pluginData['id']) || !isset($pluginData['version']) ) {
        return ( processOldPlugin( $tmp ) );
    }

    // proper glfusion version
    if (!COM_checkVersion(GVERSION, $pluginData['glfusionversion'])) {
        _pi_deleteDir($_CONF['path_data'].$tmp);
        return _pi_errorBox(sprintf($LANG32[49],$pluginData['glfusionversion']));
    }

    if ( !COM_checkVersion(phpversion (),$pluginData['phpversion'])) {
        $retval .= sprintf($LANG32[50],$pluginData['phpversion']);
        _pi_deleteDir($_CONF['path_data'].$tmp);
        return _pi_errorBox(sprintf($LANG32[50],$pluginData['phpversion']));
    }

    // check prerequisites
    $errors = '';
    if ( isset($pluginData['requires']) && is_array($pluginData['requires']) ) {
        foreach ($pluginData['requires'] AS $reqPlugin ) {
            list($reqPlugin, $required_ver) = explode(',', $reqPlugin);
            if (!isset($_PLUGIN_INFO[$reqPlugin])) {
                // required plugin not installed
                $errors .= sprintf($LANG32[51],$pluginData['id'],$reqPlugin,$reqPlugin);
            } elseif (!empty($required_ver)) {
                $installed_ver = $_PLUGIN_INFO[$reqPlugin];
                if (!COM_checkVersion($installed_ver, $required_ver)) {
                    // required plugin installed, but wrong version
                    $errors .= sprintf($LANG32[90],$required_ver,$reqPlugin,$installed_ver,$reqPlugin);
                }
            }
        }
    }

    if ( $errors != '' ) {
        _pi_deleteDir($_CONF['path_data'].$tmp);
        return _pi_errorBox($errors);
    }
    // check if plugin already exists
    // if it does, check that this is an upgrade
    // if not, error
    // else validate we really want to upgrade
    $result = DB_query("SELECT * FROM {$_TABLES['plugins']} WHERE pi_name='".DB_escapeString($pluginData['id'])."'");
    if ( DB_numRows($result) > 0 ) {
        $P = DB_fetchArray($result);
        if ($P['pi_version'] == $pluginData['version'] ) {
            _pi_deleteDir($_CONF['path_data'].$tmp);
            return _pi_errorBox(sprintf($LANG32[52],$pluginData['id']));
        }
        // if we are here, it must be an upgrade or disabled plugin....
        $rc = COM_checkVersion($pluginData['version'],$P['pi_version']);
        if ( $rc < 1 ) {
            _pi_deleteDir($_CONF['path_data'].$tmp);
            return _pi_errorBox(sprintf($LANG32[53],$pluginData['id'],$pluginData['version'],$P['pi_version']));
        }
        if ( $P['pi_enabled'] != 1 ) {
            _pi_deleteDir($_CONF['path_data'].$tmp);
            return _pi_errorBox($LANG32[72]);
        }

        $upgrade = true;
    }

    $permError = 0;
    $permErrorList = '';
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    // test copy to proper directories
    list($rc,$failed) = _pi_test_copy($_CONF['path_data'].$tmp.'/'.$pluginData['id'].'/', $_CONF['path'].'plugins/'.$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }
    list($rc,$failed) = _pi_test_copy($_CONF['path_data'].$tmp.'/'.$pluginData['id'].'/admin/', $_CONF['path_html'].'admin/plugins/'.$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }
    list($rc,$failed) = _pi_test_copy($_CONF['path_data'].$tmp.'/'.$pluginData['id'].'/public_html/', $_CONF['path_html'].$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }

    if ( $permError != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];
        _pi_deleteDir($_CONF['path_data'].$tmp);
        return _pi_errorBox($errorMessage);
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
        'form_action_url'   => $_CONF['site_admin_url'] .'/plugin_upload.php',
        'action'            => 'processupload',
        'pi_name'           => $pluginData['id'],
        'pi_version'        => $pluginData['version'],
        'pi_url'            => $pluginData['url'],
        'pi_gl_version'     => $pluginData['glfusionversion'],
        'pi_desc'           => $pluginData['description'],
        'pi_author'         => $pluginData['author'],
        'plugin_old_version' => $P['pi_version'],
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

    global $_CONF, $_PLUGINS, $_TABLES, $pluginData, $LANG32,$_DB_dbms, $_DB_table_prefix ;

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

    clearstatcache();

    $permError = 0;
    $permErrorList = '';

    // copy to proper directories

    if ( defined('DEMO_MODE') ) {
        _pi_deleteDir($tmp);
        COM_setMessage(503);
        echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php');
        exit;
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/', $_CONF['path'].'plugins/'.$pluginData['id']);
    list($success,$failed,$size,$faillist) = explode(',',$rc);
    if ( $failed > 0 ) {
        $permError++;
        $t = array();
        $t = explode('|',$faillist);
        if ( is_array($t) ) {
            foreach ($t AS $failedFile) {
                $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'plugins/'.$pluginData['id']);
            }
        }
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/admin/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/admin/', $_CONF['path_html'].'admin/plugins/'.$pluginData['id']);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'plugins/'.$pluginData['id']);
                }
            }
        }
        _pi_deleteDir($_CONF['path'].'plugins/'.$pluginData['id'].'/admin/');
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/public_html/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/public_html/', $_CONF['path_html'].$pluginData['id']);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'plugins/'.$pluginData['id']);
                }
            }
        }
        _pi_deleteDir($_CONF['path'].'plugins/'.$pluginData['id'].'/public_html/');
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/themefiles/') ) {
        // determine where to copy them, first check to see if layout was defined in xml
        if ( isset($pluginData['layout']) && $pluginData['layout'] != '') {
            $destinationDir = $_CONF['path_html'] . 'layout/' . $pluginData['layout'] .'/';
            fusion_io_mkdir_p($destinationDir);
        } else {
            $destinationDir = $_CONF['path_html'] . 'layout/nouveau/'.$pluginData['id'].'/';
        }
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/themefiles/', $destinationDir);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'plugins/'.$pluginData['id']);
                }
            }
        }
        _pi_deleteDir($_CONF['path'].'plugins/'.$pluginData['id'].'/themefiles/');
    }
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if ( $permError != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];
        _pi_deleteDir($tmp);
        return _pi_errorBox($errorMessage);
    }

    if ( isset($pluginData['dataproxydriver']) && $pluginData['dataproxydriver'] != '' ) {
        if ( file_exists($_CONF['path'].'plugins/dataproxy/drivers/') ) {
            $src  = $tmp.'/'.$pluginData['id'].'/dataproxy/'.$pluginData['dataproxydriver'];
            $dest = $_CONF['path'].'plugins/dataproxy/drivers/'.$pluginData['dataproxydriver'];
            @copy($src,$dest);
        }
    }

    _pi_deleteDir($tmp);

    if ( is_array($pluginData['renamedist']) ) {
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

                if ( !file_exists($_CONF['path_html'].'admin/plugins/'.$pluginData['id'].$pathTo.$fileName) ) {
                    COM_errorLog("PLG-INSTALL: Renaming " . $fileNameDist ." to " . $_CONF['path_html'].'admin/plugins/'.$pluginData['id'].$pathTo.$fileName);
                    $rc = @copy ($_CONF['path_html'].'admin/plugins/'.$pluginData['id'].$absoluteFileName,$_CONF['path_html'].'admin/plugins/'.$pluginData['id'].$pathTo.$fileName);
                    if ( $rc === false ) {
                        COM_errorLog("PLG-INSTALL: Unable to copy ".$_CONF['path_html'].'admin/plugins/'.$pluginData['id'].$absoluteFileName." to ".$_CONF['path_html'].'admin/plugins/'.$pluginData['id'].$pathTo.$fileName);
                        $masterErrorCount++;
                        $masterErrorMsg .= sprintf($LANG32[75],$_CONF['path_html'].'admin/plugins/'.$pluginData['id'].$absoluteFileName,$_CONF['path_html'].'admin/plugins/'.$pluginData['id'].$pathTo.$fileName);
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

                if ( !file_exists($_CONF['path_html'].$pluginData['id'].$pathTo.$fileName) ) {
                    COM_errorLog("PLG-INSTALL: Renaming " . $fileNameDist ." to " . $_CONF['path_html'].$pluginData['id'].$pathTo.$fileName);
                    $rc = @copy ($_CONF['path_html'].$pluginData['id'].$absoluteFileName,$_CONF['path_html'].$pluginData['id'].$pathTo.$fileName);
                    if ( $rc === false ) {
                        COM_errorLog("PLG-INSTALL: Unable to copy ".$_CONF['path_html'].$pluginData['id'].$absoluteFileName." to ".$_CONF['path_html'].$pluginData['id'].$pathTo.$fileName);
                        $masterErrorCount++;
                        $masterErrorMsg .= sprintf($LANG32[75],$_CONF['path_html'].$pluginData['id'].$absoluteFileName,$_CONF['path_html'].$pluginData['id'].$pathTo.$fileName);
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
        return _pi_errorBox($errorMessage);
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
                CTL_clearCache();
                COM_setMessage(44);
                echo COM_refresh ($_CONF['site_admin_url']. '/plugins.php');
                exit;
            } else {
                return _pi_errorBox($LANG32[54]);
            }
        } else {
            return _pi_errorBox($LANG32[55]);
        }
    } else {
        // upgrade - force refresh to load new functions.inc
        echo COM_refresh($_CONF['site_admin_url'] . '/plugin_upload.php?mode=upgrade&amp;pi=' .$pluginData['id']);
        exit;
    }

    CTL_clearCache();
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
function pi_update ($pi_name)
{
    global $_CONF, $LANG32, $LANG08, $MESSAGE, $_IMAGE_TYPE;

    $retval = '';

    if (strlen ($pi_name) == 0) {
        $retval .= COM_showMessageText($LANG32[12],$LANG32[13],true);
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
            $retval = COM_refresh ($_CONF['site_admin_url'].'/plugins.php?plugin='.$pi_name);
        }
    } else {  // Plugin function returned a false
        $retval .= COM_showMessage(95);
    }
    CACHE_remove_instance('stmenu');
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
            $display .= processPluginUpload();
            break;
        case 'processoldupload' :
            $display .= processOldPluginInstall();
            break;
        case 'installplugin' :
            $display .= post_uploadProcess();
            break;
        case 'upgrade' :
            if ( isset($_GET['pi']) ) {
                $pi = COM_sanitizeID(COM_applyFilter($_GET['pi']));
            } else {
                $pi = '';
            }
            echo pi_update($pi);
            exit;
            break;
        default :
            echo COM_refresh($_CONF['site_admin_url'] .'/plugins.php');
            exit;
            break;
    }
} else if ( isset($_POST['cancel']) ) {
    if ( isset($_POST['temp_dir']) ) {
        $tmpDir = COM_applyFilter($_POST['temp_dir']);

        $len = strlen($_CONF['path_data']);
        if ( strncmp($_CONF['path_data'],$tmpDir,$len-1) == 0 ) {
            _pi_deleteDir($tmpDir);
        } else {
            COM_errorLog("PLG-Install: Directory mismatch after cancel operatio - Temp directory not deleted");
        }
    }
    if ( isset($_POST['pi_name']) ) {
        $pi_name = COM_applyFilter($_POST['pi_name']);

        @unlink($_CONF['path_data'] . 'temp/' . $pi_name . '*');
    }
    echo COM_refresh($_CONF['site_admin_url'] .'/plugins.php');
    exit;

} else {
    echo COM_refresh($_CONF['site_admin_url'] .'/plugins.php');
    exit;

}

echo COM_siteHeader('menu');
echo _pi_Header();
$display .= COM_siteFooter();
echo $display;
exit;
?>
