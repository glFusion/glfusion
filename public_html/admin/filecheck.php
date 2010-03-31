<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | filecheck.php                                                            |
// |                                                                          |
// | glFusion File Validation Utility                                         |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark Howard            mark AT usable-web DOT com                        |
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
//
_stopwatch('start');

require_once '../lib-common.php';
require_once 'auth.inc.php';
require_once 'filecheck_data.php';

USES_lib_admin();

$display = '';

if (!SEC_inGroup ('Root')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30])
        . COM_startBlock ($MESSAGE[30], '',COM_getBlockTemplate ('_msg_block', 'header'))
        . $MESSAGE[200]
        . COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'))
        . COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to illegally access the file integrity check screen");
    echo $display;
    exit;
}

function _current_tick()
{
    list($usec, $sec) = explode( ' ',microtime());
    return (float)$sec + (float)$usec;
}

function _stopwatch( $action='' )
{
    static $start;
    $retval = 0;
    switch ($action) {
        case 'start':
            $start = _current_tick();
            break;
        case 'stop':
            $retval = round(_current_tick() - $start, 5);
            break;
    }
    return $retval;
}

function FILECHECK_isWriteable( $file )
{
    $perms = fileperms($file);
    return (($perms & 0x0080) || ($perms & 0x0010) || ($perms & 0x0002));
}

function FILECHECK_scanNegative()
{
    global $_CONF, $glfDir, $glfFile, $data_arr;

    // check for missing directories
    foreach( $glfDir as $dir ) {
        // replace the generic prefix with the actual directory
        $rdir = $dir['path'];
        if (strtolower(substr($rdir,0,7)) == 'private') {
            $where = 'private';
            $rdir = (strtolower($rdir) <> $where) ? $_CONF['path'] . substr($rdir,8) : substr($_CONF['path'],0,-1);
        } elseif (strtolower(substr($rdir,0,11)) == 'public_html') {
            $where = 'public_html';
            $rdir = (strtolower($rdir) <> $where) ? $_CONF['path_html'] . substr($rdir,12) : substr($_CONF['path_html'],0,-1);
        } else {
            COM_errorlog( 'filecheck: unexpected root dirspec(not private/ or public_html/): ' . $dir );
        }
        // how we check depends upon whether we were allowed to recurse there
        switch ($dir['test']) {
            case 'E':
                // we were not allowed to recurse here, check manually
                if (!is_dir($rdir)) {
                    $data_arr[] = array(
                        'where' => $where,
                        'path'  => $rdir,
                        'file'  => '',
                        'type'  => 'D',
                        'delta' => '-'
                    );
                }
                break;

            case 'R':
                // we recursed here, unpinged dirs must be missing
                if ($dir['ping'] <> 1) {
                    $data_arr[] = array(
                        'where' => $where,
                        'path'  => $rdir,
                        'file'  => '',
                        'type'  => 'D',
                        'delta' => '-'
                    );
                }
                break;
        }
    }

    // check for missing files
    foreach( $glfFile as $file ) {
        // replace the generic prefix with the actual directory
        $rdir = $file['path'];
        if (strtolower(substr($rdir,0,7)) == 'private') {
            $where = 'private';
            $rdir = (strtolower($rdir) <> $where) ? $_CONF['path'] . substr($rdir,8) : substr($_CONF['path'],0,-1);
        } elseif (strtolower(substr($rdir,0,11)) == 'public_html') {
            $where = 'public_html';
            $rdir = (strtolower($rdir) <> $where) ? $_CONF['path_html'] . substr($rdir,12) : substr($_CONF['path_html'],0,-1);
        } else {
            COM_errorlog( 'filecheck: unexpected root dirspec(not private/ or public_html/): ' . $dir );
        }
        // ok now check for unpinged files
        if ($file['ping'] <> 1) {
            // ostensibly, this file was not found - get the dir and file parts
            $pathinfo = pathinfo($rdir);
            $dirname = $pathinfo['dirname'];
            $filename = $pathinfo['filename'].'.'.$pathinfo['extension'];
            // check to see if we were allowed to recurse into this dir
            list($test,$i) = FILECHECK_search($dirname, $glfDir);
            if ($test == 'R') {
                // yes, we were allowed to look here, and the file was not found
                $data_arr[] = array(
                    'where' => $where,
                    'path'  => $dirname,
                    'file'  => $filename,
                    'type'  => 'F',
                    'delta' => '-'
                );
            } else {
                // no, we were not allowed to look here, so test manually
                if(!file_exists($rdir)) {
                    $data_arr[] = array(
                        'where' => $where,
                        'path'  => $dirname,
                        'file'  => $filename,
                        'type'  => 'F',
                        'delta' => '-'
                    );
                }
            }
        }
    }

    return;
}

function FILECHECK_search($needle, $haystack)
{
    for ($i=0; $i < count($haystack); $i++) {
        if ($haystack[$i]['path'] == $needle) {
            return array($haystack[$i]['test'],$i);
        }
    }
    return array('',0);
}

function FILECHECK_scanPositive( $path = '.', $where, $level = 0, $prefix=array())
{
    global $glfFile, $glfDir, $glfIgnore, $data_arr;

    $ignore = $glfIgnore;

    $dh = @opendir( $path );

    while( false !== ($file = readdir($dh)) ) {
        // ignore some files & directories
        $test = '';
        $needle = $where . '/';
        if( !in_array( $file, $ignore ) ){
            // not on the ignore list, so determine whether it is a file or dir
            if( is_dir( "$path/$file" ) ){
                // this is a directory - construct the search term
                foreach($prefix AS $pdir ) {
                    $needle .= $pdir .'/';
                }
                $needle .= $file;
                list($test, $i) = FILECHECK_search($needle,$glfDir);
                if (!empty($test)) {
                    // directory was recognized
                    $glfDir[$i]['ping'] = 1;
                    if ($test=='R') {
                        // recurse only if allowed
                        $prefix[] = $file;
                        FILECHECK_scanPositive( "$path/$file", $where, ($level+1), $prefix );
                        array_pop($prefix);
                    }
                } else {
                    // directory is not recognized, add to list
                    $data_arr[] = array(
                        'where' => $where,
                        'path'  => $path . '/' . $file,
                        'file'  => '',
                        'type'  => 'D',
                        'delta' => '+'
                    );
                }
            } else {
                // this is a file
                // construct the search term
                foreach($prefix AS $pdir ) {
                    $needle .= $pdir .'/';
                }
                $needle .= $file;
                // search the distribution file list
                list($test, $i) = FILECHECK_search($needle,$glfFile);
                if (empty($test)) {
                    // file is not recognized, add to list
                    $data_arr[] = array('where' => $where,
                                        'path'  => $path,
                                        'file'  => $file,
                                        'type'  => 'F',
                                        'delta' => '+'
                    );
                } else {
                    $glfFile[$i]['ping']=1;
                }
            }
        }
    }
    closedir( $dh );
}

function FILECHECK_getListField($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF;

    static $counter = 0;

    $retval = false;

    switch($fieldname) {
        case 'delete':
            if ($A['type'] == 'F' && $A['delta'] == '+') {
                if (FILECHECK_isWriteable($A['path'] . '/' . $A['file'])) {
                    $retval = '<input type="checkbox" name="sel[]" value="' . $counter . '"'.XHTML.'>';
                    $retval .= '<input type="hidden" name="dir[' . $counter . ']" value="';
                    $retval .= $A['path'] . '/' . $A['file'] .'" '.XHTML.'>';
                    $counter++;
                }
            } else {
                    $retval = '<input type="checkbox" name="disabled" value="x" DISABLED'.XHTML.'>';
            }
            break;
        case 'delta':
            $type = ($A['type'] == 'F') ? 'File' : 'Directory';
            $retval = ($fieldvalue<>'-') ? $type . ' added:' : $type . ' missing:';
            break;
        case 'path':
            $retval = $fieldvalue . '/';
            break;
        default:
            $retval = $fieldvalue;
            break;
    }

    return $retval;
}

function FILECHECK_list()
{
    global $_CONF, $LANG_ADMIN, $LANG_FILECHECK, $LANG01, $data_arr;

    $retval = '';

    $menu_arr = array (
        array('url'  => $_CONF['site_admin_url'].'/filecheck.php',
              'text' => $LANG_FILECHECK['recheck']),
        array('url'  => $_CONF['site_admin_url'].'/envcheck.php',
              'text' => $LANG01['env_check']),
        array('url'  => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG_FILECHECK['filecheck'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        sprintf($LANG_FILECHECK['explanation'], GVERSION),
        $_CONF['layout_url'] . '/images/icons/filecheck.png'
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    // list files that have been added

    $header_arr = array(
        array('text' => $LANG_ADMIN['delete'],   'field' => 'delete', 'align' => 'center'),
        array('text' => $LANG_FILECHECK['where'], 'field' => 'where', 'align' => 'center'),
        array('text' => $LANG_FILECHECK['delta'], 'field' => 'delta', 'align' => 'right'),
        array('text' => $LANG_FILECHECK['path'], 'field' => 'path'),
        array('text' => $LANG_FILECHECK['file'],  'field' => 'file')
    );

    $data_arr = array();
    $text_arr = array();
    $form_arr = array();

    $text_arr = array(
        'form_url'   => $_CONF['site_admin_url'] . '/filecheck.php'
    );

    $bottom = '<br' . XHTML . '><input type="submit" onclick="return confirm(\'' . $LANG_FILECHECK['confirm'] . '\');" name="delete" value="' . $LANG_ADMIN['delete'] . '"' . XHTML . '>'
            . '&nbsp;&nbsp;<input type="submit" name="cancel" value="' . $LANG_ADMIN['cancel'] . '"' . XHTML . '>';

    $form_arr = array('bottom' => $bottom);

    _stopwatch('start');
    FILECHECK_scanPositive(substr($_CONF['path'],0,-1),'private');
    COM_errorLog( 'Completed scanPositive(private) in ' . _stopwatch('stop') . ' sec');
    _stopwatch('start');
    FILECHECK_scanPositive(substr($_CONF['path_html'],0,-1),'public_html');
    COM_errorLog( 'Completed scanPositive(public_html) in ' . _stopwatch('stop') . ' sec');
    _stopwatch('start');
    FILECHECK_scanNegative();
    COM_errorLog( 'Completed scanNegative(private+public_html) in ' . _stopwatch('stop') . ' sec');

    _stopwatch('start');
    sort($data_arr);
    COM_errorLog( 'Sorted scan results in ' . _stopwatch('stop') . ' sec');


    _stopwatch('start');
    $retval .= ADMIN_simpleList("FILECHECK_getListField", $header_arr, $text_arr, $data_arr, NULL, $form_arr);
    COM_errorLog( 'Completed list output in ' . _stopwatch('stop') . ' sec');

    return $retval;
}

function FILECHECK_delete( )
{
    global $_CONF, $_POST;

    if ( defined('DEMO_MODE') ) {
        return '';
    }

    $n = count($_POST['sel']);
    for ($i=0; $i < $n; $i++) {
        $index = COM_applyFilter($_POST['sel'][$i]);
        $filespec = COM_applyFilter($_POST['dir'][$index]);
        @unlink($filespec);
        COM_errorLog('filecheck.php: admin deleted: ' . $filespec);
    }
    return $n;
}

// MAIN ========================================================================


$action = '';
$expected = array('delete','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    }
}

$files = 0;
switch ($action) {

    case 'cancel':
        echo COM_refresh ($_CONF['site_admin_url'] . '/index.php');
        exit;
        break;

    case 'delete':
        $files = FILECHECK_delete( );
        break;
}

$display .= COM_siteHeader();
if ($files > 0) {
    $desc = ($files > 1) ? 'files were' : 'file was';
    $display .= COM_showMessageText(sprintf($LANG_FILECHECK['removed'],$files,$desc));
}
COM_errorLog('Completed initialization in ' . _stopwatch('stop') . ' sec');
$display .= FILECHECK_list() . COM_siteFooter();

echo $display;

?>
