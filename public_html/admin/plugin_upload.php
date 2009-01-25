<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | plugin_upload.php                                                        |
// |                                                                          |
// | glFusion Automated plugin installer                                      |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
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
    $display .= COM_startBlock ($MESSAGE[30], '',
                                COM_getBlockTemplate ('_msg_block', 'header'));
    $display .= $MESSAGE[38];
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to illegally access the plugin administration screen.");
    echo $display;
    exit;
}

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

    if (!$dh = @opendir($tmpDir)) {
        _pi_deleteDir($tmpDir);
        return ( _pi_errorBox( $LANG32[39] ));

    }

    while ( ( $file = readdir($dh) ) != false ) {
        if ( $file == '..' || $file == '.' ) {
            continue;
        }

        if ( is_dir($tmpDir . '/' . $file) ) {
            $pi_name = $file;
            $dirCount++;
        }
    }
    closedir($dh);

    if ( $pi_name == '' || $dirCount > 1) {
        _pi_deleteDir($tmpDir);
        return _pi_errorBox($LANG32[40]);
    }

    $result = DB_query("SELECT * FROM {$_TABLES['plugins']} WHERE pi_name='".addslashes($pi_name)."' LIMIT 1");
    if ( DB_numRows($result) > 0 ) {
        $P = DB_fetchArray($result);

        if ( $P['pi_enabled'] != 1 ) {
            _pi_deleteDir($tmpDir);
            return _pi_errorBox($LANG32[72]);
        }

        $upgrade = true;
    }

    $T = new Template($_CONF['path_layout'] . 'admin/plugins');
    $T->set_file('form','plugin_upload_old_confirm.thtml');

    $T->set_var(array(
        'form_action_url'   =>  $_CONF['site_admin_url'] .'/plugin_upload.php',
        'action'            =>  'processoldupload',
        'pi_name'           => $pi_name,
        'plugin_old_version' => $pluginVersion,
        'upgrade'           => $upgrade,
        'temp_dir'          => $tmpDir,
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
    $tmp                            = COM_applyFilter($_POST['temp_dir']);

    $permError = 0;
    $permErrorList = '';

    // test copy to proper directories
    list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/', $_CONF['path'].'/plugins/'.$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }
    list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/admin/', $_CONF['path_html'].'/admin/plugins/'.$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }
    list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/public_html/', $_CONF['path_html'].'/'.$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }

    if ( $permError != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];

        _pi_deleteDir($tmp);
        return (_pi_errorBox($errorMessage));
    }

    clearstatcache();

    $permError = 0;
    $permErrorList = '';

    // copy to proper directories
    $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/', $_CONF['path'].'/plugins/'.$pluginData['id']);
    list($success,$failed,$size,$faillist) = explode(',',$rc);
    if ( $failed > 0 ) {
        $permError++;
        $t = array();
        $t = explode('|',$faillist);
        if ( is_array($t) ) {
            foreach ($t AS $failedFile) {
                $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'/plugins/'.$pluginData['id']);
            }
        }
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/admin/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/admin/', $_CONF['path_html'].'/admin/plugins/'.$pluginData['id']);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path_html'].'/admin/plugins/'.$pluginData['id']);
                }
            }
        }
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/public_html/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/public_html/', $_CONF['path_html'].'/'.$pluginData['id']);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path_html'].'/'.$pluginData['id']);
                }
            }
        }
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/themefiles/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/themefiles/', $_CONF['path_html'].'/layout/nouveau/'.$pluginData['id']);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG45,$failedFile,$_CONF['path_html'].'/layout/nouveau/'.$pluginData['id']);
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

    echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=502');
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
    global $_CONF, $_PLUGINS, $_TABLES, $pluginData, $LANG32, $_DB_dbms, $_DB_table_prefix ;

    $retval = '';
    $upgrade = false;

    if (count($_FILES) > 0 ) {
        require_once($_CONF['path_system'] . 'classes/upload.class.php');
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

    if (!($tmp = io_mktmpdir())) {
        return _pi_errorBox($LANG32[47]);
    }

    if ( !COM_decompress($Finalfilename,$tmp) ) {
        _pi_deleteDir($tmp);
        return _pi_errorBox($LANG32[48]);
    }
    @unlink($Finalfilename);

    // read XML data file, places in $pluginData;

    $pluginData = array();
    $rc = _pi_parseXML($tmp);

    if ( $rc == -1 ) {
        // no xml file found
        return ( processOldPlugin( $tmp ) );
    }

    if ( !isset($pluginData['id']) || !isset($pluginData['version']) ) {
        return ( processOldPlugin( $tmp ) );
    }

    // proper glfusion version
    if (!COM_checkVersion(GVERSION, $pluginData['glfusionversion'])) {
        _pi_deleteDir($tmp);
        return _pi_errorBox(sprintf($LANG32[49],$pluginData['glfusionversion']));
    }

    if ( !COM_checkVersion(phpversion (),$pluginData['phpversion'])) {
        $retval .= sprintf($LANG32[50],$pluginData['phpversion']);
        _pi_deleteDir($tmp);
        return _pi_errorBox(sprintf($LANG32[50],$pluginData['phpversion']));
    }

    // check prerequisites
    $errors = '';
    if ( is_array($pluginData['requires']) ) {
        foreach ($pluginData['requires'] AS $reqPlugin ) {
            if ( ($key = array_search($reqPlugin,$_PLUGINS)) === false ) {
                $errors .= sprintf($LANG32[51],$pluginData['id'],$reqPlugin,$reqPlugin);
            }
        }
    }
    if ( $errors != '' ) {
        _pi_deleteDir($tmp);
        return _pi_errorBox($errors);
    }
    // check if plugin already exists
        // if it does, check that this is an upgrade
            // if not, error
        // else validate we really want to upgrade
    $result = DB_query("SELECT * FROM {$_TABLES['plugins']} WHERE pi_name='".addslashes($pluginData['id'])."'");
    if ( DB_numRows($result) > 0 ) {
        $P = DB_fetchArray($result);
        if ($P['pi_version'] == $pluginData['version'] ) {
            _pi_deleteDir($tmp);
            return _pi_errorBox(sprintf($LANG32[52],$pluginData['id']));
        }
        // if we are here, it must be an upgrade or disabled plugin....
        $rc = COM_checkVersion($pluginData['version'],$P['pi_version']);
        if ( $rc < 1 ) {
            _pi_deleteDir($tmp);
            return _pi_errorBox(sprintf($LANG32[53],$pluginData['id'],$pluginData['version'],$pluginVersion));
        }
        if ( $P['pi_enabled'] != 1 ) {
            _pi_deleteDir($tmp);
            return _pi_errorBox($LANG32[72]);
        }

        $upgrade = true;
    }

    $permError = 0;
    $permErrorList = '';

    // test copy to proper directories
    list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/', $_CONF['path'].'/plugins/'.$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }
    list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/admin/', $_CONF['path_html'].'/admin/plugins/'.$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }
    list($rc,$failed) = _pi_test_copy($tmp.'/'.$pluginData['id'].'/public_html/', $_CONF['path_html'].'/'.$pluginData['id']);
    if ( $rc > 0 ) {
        $permError = 1;
        foreach($failed AS $filename) {
            $permErrorList .= sprintf($LANG32[41],$filename);
        }
    }

    if ( $permError != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];
        _pi_deleteDir($tmp);
        return _pi_errorBox($errorMessage);
    }

    $T = new Template($_CONF['path_layout'] . 'admin/plugins');
    $T->set_file('form','plugin_upload_confirm.thtml');

    $T->set_var(array(
        'form_action_url'   =>  $_CONF['site_admin_url'] .'/plugin_upload.php',
        'action'            =>  'processupload',
        'pi_name'           => $pluginData['id'],
        'pi_version'        => $pluginData['version'],
        'pi_url'            => $pluginData['url'],
        'pi_gl_version'     => $pluginData['glfusionversion'],
        'pi_desc'           => $pluginData['description'],
        'pi_author'         => $pluginData['author'],
        'plugin_old_version' => $pluginVersion,
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
    $tmp                            = COM_applyFilter($_POST['temp_dir']);

    $pluginData = array();
    $rc = _pi_parseXML($tmp);
//FIXME: add error check here incase we cannot parse the XML file.

    clearstatcache();

    $permError = 0;
    $permErrorList = '';

    // copy to proper directories

    $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/', $_CONF['path'].'/plugins/'.$pluginData['id']);
    list($success,$failed,$size,$faillist) = explode(',',$rc);
    if ( $failed > 0 ) {
        $permError++;
        $t = array();
        $t = explode('|',$faillist);
        if ( is_array($t) ) {
            foreach ($t AS $failedFile) {
                $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'/plugins/'.$pluginData['id']);
            }
        }
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/admin/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/admin/', $_CONF['path_html'].'/admin/plugins/'.$pluginData['id']);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'/plugins/'.$pluginData['id']);
                }
            }
        }
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/public_html/') ) {
        $rc = _pi_dir_copy($tmp.'/'.$pluginData['id'].'/public_html/', $_CONF['path_html'].'/'.$pluginData['id']);
        list($success,$failed,$size,$faillist) = explode(',',$rc);
        if ( $failed > 0 ) {
            $permError++;
            $t = array();
            $t = explode('|',$faillist);
            if ( is_array($t) ) {
                foreach ($t AS $failedFile) {
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'/plugins/'.$pluginData['id']);
                }
            }
        }
    }
    if ( file_exists($tmp.'/'.$pluginData['id'].'/themefiles/') ) {
        // determine where to copy them, first check to see if layout was defined in xml
        if ( isset($pluginData['layout']) && $pluginData['layout'] != '') {
            $destinationDir = $_CONF['path_html'] . 'layout/' . $pluginData['layout'] .'/';
            io_mkdir_p($destinationDir);
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
                    $permErrorList .= sprintf($LANG32[45],$failedFile,$_CONF['path'].'/plugins/'.$pluginData['id']);
                }
            }
        }
    }

    if ( $permError != 0 ) {
        $errorMessage = '<h2>'.$LANG32[42].'</h2>'.$LANG32[43].$permErrorList.'<br />'.$LANG32[44];
        _pi_deleteDir($tmp);
        return _pi_errorBox($errorMessage);
    }

    if ( isset($pluginData['dataproxydriver']) && $pluginData['dataproxydriver'] != '' ) {
        $src  = $tmp.'/'.$pluginData['id'].'/dataproxy/'.$pluginData['dataproxydriver'];
        $dest = $_CONF['path'].'plugins/dataproxy/drivers/'.$pluginData['dataproxydriver'];
        @copy($src,$dest);
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

                if ( !file_exists($_CONF['path_html'].'admin/plugins/'.$pluginData['id'].'/'.$pathTo.$fileName) ) {
                    COM_errorLog("PLG-INSTALL: Renaming " . $fileNameDist ." to " . $_CONF['path_html'].'admin/plugins/'.$pluginData['id'].'/'.$pathTo.$fileName);
                    $rc = @copy ($_CONF['path_html'].'admin/plugins/'.$pluginData['id'].'/'.$absoluteFileNameDist,$_CONF['path_html'].'admin/plugins/'.$pluginData['id'].'/'.$pathTo.$fileName);
                    if ( $rc === false ) {
                        COM_errorLog("PLG-INSTALL: Unable to copy ".$_CONF['path_html'].'admin/plugins/'.$pluginData['id'].'/'.$absoluteFileNameDist." to ".$_CONF['path_html'].'admin/plugins/'.$pluginData['id'].'/'.$pathTo.$fileName);
                        $masterErrorCount++;
                        $msterErrorMsg .= "Unable to copy ".$_CONF['path_html'].'admin/plugins/'.$pluginData['id'].'/'.$absoluteFileNameDist." to ".$_CONF['path_html'].'admin/plugins/'.$pluginData['id'].'/'.$pathTo.$fileName."<br />";
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

                if ( !file_exists($_CONF['path_html'].'public_html/'.$pluginData['id'].'/'.$pathTo.$fileName) ) {
                    COM_errorLog("PLG-INSTALL: Renaming " . $fileNameDist ." to " . $_CONF['path_html'].$pluginData['id'].'/'.$pathTo.$fileName);
                    $rc = @copy ($_CONF['path_html'].$pluginData['id'].'/'.$absoluteFileNameDist,$_CONF['path_html'].$pluginData['id'].'/'.$pathTo.$fileName);
                    if ( $rc === false ) {
                        COM_errorLog("PLG-INSTALL: Unable to copy ".$_CONF['path_html'].$pluginData['id'].'/'.$absoluteFileNameDist." to ".$_CONF['path_html'].$pluginData['id'].'/'.$pathTo.$fileName);
                        $masterErrorCount++;
                        $msterErrorMsg .= "Unable to copy ".$_CONF['path_html'].$pluginData['id'].'/'.$absoluteFileNameDist." to ".$_CONF['path_html'].$pluginData['id'].'/'.$pathTo.$fileName."<br />";
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
                        $msterErrorMsg .= "Unable to copy ".$_CONF['path'].'plugins/'.$pluginData['id'].'/'.$absoluteFileName." to ".$_CONF['path'].'plugins/'.$pluginData['id'].'/'.$pathTo.$fileName."<br />";
                    }
                }
            }

        }
    }

    // handle masterErrorCount here, if not 0, display error and ask use to manually install via the plugin admin screen.
    // all files have been copied, so all they really should need to do is fix the error above and then run.


    if ( $upgrade == 0 ) { // fresh install

        require_once $_CONF['path'] . '/system/lib-install.php';

        $pi_name         = $pluginData['id'];
        $pi_display_name = $pluginData['name'];
        $pi_version      = $pluginData['version'];
        $gl_version      = $pluginData['glfusionversion'];
        $pi_url          = $pluginData['url'];

        if ( file_exists($_CONF['path'].'/plugins/'.$pluginData['id'].'/autoinstall.php') ) {

            require_once $_CONF['path'].'/plugins/'.$pluginData['id'].'/autoinstall.php';

            $ret = INSTALLER_install($INSTALL_plugin[$pi_version]);

            if ( $ret == 0 ) {
                CTL_clearCache();
                echo COM_refresh ($_CONF['site_admin_url']. '/plugins.php?msg=44');
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
    CACHE_remove_instance('stmenu');
    return $retval;
}

/**
* Creates a unique temporary directory
*
* Creates a temp directory int he $_CONF['path_data] directory
*
* @return   bool              True on success, false on fail
*
*/
function io_mktmpdir() {
    global $_CONF;

    $base = $_CONF['path_data'];
    $dir  = md5(uniqid(mt_rand(), true));
    $tmpdir = $base.$dir;

    if(io_mkdir_p($tmpdir)) {
        return($tmpdir);
    } else {
        return false;
    }
}

/**
* Creates a directory
*
* @parm     string  $target   Directory to create.
* @return   bool              True on success, false on fail
*
*/
function io_mkdir_p($target){
    global $_CONF;

    if (@is_dir($target)||empty($target)) return 1; // best case check first

    if (@file_exists($target) && !is_dir($target)) return 0;
    //recursion
    if (io_mkdir_p(substr($target,0,strrpos($target,'/')))){
        $ret = @mkdir($target,0755);
        @chmod($target, 0755);
        return $ret;
    }
    return 0;
}


/**
* Deletes a directory (with recursive sub-directory support)
*
* @parm     string            Path of directory to remove
* @return   bool              True on success, false on fail
*
*/
function _pi_deleteDir($path) {
    if (!is_string($path) || $path == "") return false;

    if (is_dir($path)) {
      if (!$dh = @opendir($path)) return false;

      while ($f = readdir($dh)) {
        if ($f == '..' || $f == '.') continue;
        _pi_deleteDir("$path/$f");
      }

      closedir($dh);
      return @rmdir($path);
    } else {
      return @unlink($path);
    }

    return false;
}


function _pi_parseXML($tmpDirectory)
{
    global $_CONF, $pluginData;

    if (!$dh = @opendir($tmpDirectory)) {
        return false;
    }
   while ( ( $file = readdir($dh) ) != false ) {
        if ( $file == '..' || $file == '.' ) {
            continue;
        }
        if ( is_dir($tmpDirectory . '/' . $file) ) {
            $filename = $tmpDirectory . '/' . $file . '/plugin.xml';
            break;
        }
    }
    closedir($dh);

    if (!($fp=@fopen($filename, "r"))) {
        return -1;
    }

    $pluginData = array();

    if (!($xml_parser = xml_parser_create()))
        return false;

    xml_set_element_handler($xml_parser,"_pi_startElementHandler","_pi_endElementHandler");
    xml_set_character_data_handler( $xml_parser, "_pi_characterDataHandler");

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
function _pi_startElementHandler ($parser,$name,$attrib) {
    global $pluginData;
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

function _pi_endElementHandler ($parser,$name){
    global $pluginData;
    global $state;

    $state='';
}

function _pi_characterDataHandler ($parser, $data) {
    global $pluginData;
    global $state;


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
* Copies srcdir to destdir (recursive)
*
* @param    string  $srcdir Source Directory
* @param    string  $dstdir Destination Directory
*
* @return   string          comma delimited list success,fail,size,failedfiles
*                           5,2,150000,\SOMEPATH\SOMEFILE.EXT|\SOMEPATH\SOMEOTHERFILE.EXT
*
*/
function _pi_dir_copy($srcdir, $dstdir )
{
    $num = 0;
    $fail = 0;
    $sizetotal = 0;
    $fifail = '';
    if (!is_dir($dstdir)) io_mkdir_p($dstdir);
    if ($curdir = @opendir($srcdir)) {
        while ($file = readdir($curdir)) {
            if ($file != '.' && $file != '..') {
                $srcfile = $srcdir . '/' . $file;
                $dstfile = $dstdir . '/' . $file;
                if (is_file($srcfile)) {
                    if (copy($srcfile, $dstfile)) {
                        touch($dstfile, filemtime($srcfile)); $num++;
                        @chmod($dstfile, 0644);
                        $sizetotal = ($sizetotal + filesize($dstfile));
                    } else {
                        COM_errorLog("PLG-INSTALL: File '$srcfile' could not be copied!");
                        $fail++;
                        $fifail = $fifail.$srcfile.'|';
                    }
                }
                else if (is_dir($srcfile)) {
                    $res = explode(',',$ret);
                    $ret = _pi_dir_copy($srcfile, $dstfile, $verbose);
                    $mod = explode(',',$ret);
                    $imp = array($res[0] + $mod[0],$mod[1] + $res[1],$mod[2] + $res[2],$mod[3].$res[3]);
                    $ret = implode(',',$imp);
                }
            }
        }
        closedir($curdir);
    } else {
        COM_errorLog("PLG-INSTALL: Unable to open temporary directory: " . $srcdir);
        $ret ='0,1,0,Unable to open temp. directory';
        return $ret;
    }
    $red = explode(',',$ret);
    $ret = ($num + $red[0]).','.($fail + $red[1]).','.($sizetotal + $red[2]).','.$fifail.$red[3];
    return $ret;
}


function _pi_test_copy($srcdir, $dstdir)
{
    $num        = 0;
    $fail       = 0;
    $sizetotal  = 0;
    $fifail     = '';
    $createdDst = 0;

    $failedFiles = array();

    if(!is_dir($dstdir)) {
        $rc = io_mkdir_p($dstdir);
        if ($rc == false ) {
            $failedFiles[] = $dstdir;
            COM_errorLog("PLG-INSTALL: Error: Unable to create directory " . $dstdir);
            return array(1,$failedFiles);
        }
        $createdDst = 1;
    }

    if($curdir = @opendir($srcdir)) {
        while($file = readdir($curdir)) {
            if($file != '.' && $file != '..') {
                $srcfile = $srcdir . '/' . $file;
                $dstfile = $dstdir . '/' . $file;
                if(is_file($srcfile)) {
                    if ( !is__writable($dstfile) ) {
                        $failedFiles[] = $dstfile;
                        COM_errorLog("PLG-INSTALL: Error: File '$dstfile' cannot be written");
                        $fail++;
                        $fifail = $fifail.$srcfile.'|';
                    }
                } else if(is_dir($srcfile)) {
                    $res = explode(',',$ret);
                    list($ret,$failed) = _pi_test_copy($srcfile, $dstfile, $verbose);
                    $failedFiles = array_merge($failedFiles,$failed);
                    $mod = explode(',',$ret);
                    $imp = array($res[0] + $mod[0],$mod[1] + $res[1],$mod[2] + $res[2],$mod[3].$res[3]);
                    $ret = implode(',',$imp);
                }
            }
        }
        closedir($curdir);
    }
    if ($createdDst == 1) {
        @rmdir($dstdir);
    }

    $red = explode(',',$ret);
    $ret = ($num + $red[0]).','.($fail + $red[1]).','.($sizetotal + $red[2]).','.$fifail.$red[3];
    return array($fail,$failedFiles);
}


function _pi_errorBox( $errMsg )
{
    global $_CONF,$LANG32;

    $retval = '';

    $retval .= '<div id="msgbox" style="width:95%;margin:10px;border:1px solid black;">';
    $retval .= '<div style="padding:5px;font-weight:bold;color:#FFFFFF;background:url('.$_CONF['layout_url'].'/images/header-bg.png) #1A3955;">';
    $retval .= $LANG32[56];
    $retval .= '</div>';
    $retval .= '<div style="padding:5px 15px 15px 15px;border-top:3px solid black;background:#E7E7E7;">';
    $retval .= $errMsg;
    $retval .= '</div>';
    $retval .= '</div>';
    $retval .= '<form action="'.$_CONF['site_admin_url'] . '/plugins.php" method="get">';
    $retval .= '&nbsp;&nbsp;&nbsp;<input type="submit" name="cont" value="Continue" />';
    $retval .= '</form>';

    return $retval;
}

function is__writable($path) {
    if ($path{strlen($path)-1}=='/')
        return is__writable($path.uniqid(mt_rand()).'.tmp');

    if (file_exists($path)) {
        if (!($f = @fopen($path, 'r+')))
            return false;
        fclose($f);
        return true;
    }

    if (!($f = @fopen($path, 'w')))
        return false;
    fclose($f);
    unlink($path);
    return true;
}

function _pi_Header()
{
    global $_CONF, $LANG_ADMIN,$LANG32;

    $retval = '';

    $retval .= COM_startBlock($LANG32[73], '',
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
            $display .= processPluginUpload();
            break;
        case 'processoldupload' :
            $display .= processOldPluginInstall();
            break;
        case 'installplugin' :
            $display .= post_uploadProcess();
            break;
        case 'upgrade' :
            echo pi_update($_GET['pi']);
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

        @unlink($_CONF['path'] . 'data/temp/' . $pi_name . '*');
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