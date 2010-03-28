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

    $retval .= '<br />';
    for ($i=0; $i < count($glfDir); $i++) {
        $where = '';
        $dir = $glfDir[$i]['path'];
        if (strtolower(substr($dir,0,7)) == 'private') {
            $where = 'private';
            $dir = (strtolower($dir) <> $where) ? $_CONF['path'] . substr($dir,8) : substr($_CONF['path'],0,-1);
        } elseif (strtolower(substr($dir,0,11)) == 'public_html') {
            $where = 'public_html';
            $dir = (strtolower($dir) <> $where) ? $_CONF['path_html'] . substr($dir,12) : substr($_CONF['path_html'],0,-1);
        } else {
            COM_errorlog( 'filecheck: unexpected root dirspec(not private/ or public_html/): ' . $dir );
        }
        if (!empty($where) && !(file_exists($dir))) {
            // directory was not found - add to list and display preq
            $data_arr[] = array(
                'where' => $where,
                'path'  => $dir,
                'file'  => '',
                'type'  => 'D',
                'delta' => '-'
            );
        }
    }
    for ($i=0; $i < count($glfFile); $i++) {
        $where = '';
        $file = $glfFile[$i]['path'];
        if (strtolower(substr($file,0,7)) == 'private') {
            $where = 'private';
            $file = (strtolower($file) <> $where) ? $_CONF['path'] . substr($file,8) : substr($_CONF['path'],0,-1);
        } elseif (strtolower(substr($file,0,11)) == 'public_html') {
            $where = 'public_html';
            $file = (strtolower($file) <> $where) ? $_CONF['path_html'] . substr($file,12) : substr($_CONF['path_html'],0,-1);
        } else {
            COM_errorlog( 'filecheck: unexpected root dirspec(not private/ or public_html/): ' . $file );
        }
        if (!empty($where) && !(file_exists($file))) {
            // file was not found - add to list and display preq
            $pathinfo = pathinfo($file);
            $data_arr[] = array(
                'where' => $where,
                'path'  => $pathinfo['dirname'],
                'file'  => $pathinfo['filename'] . '.' . $pathinfo['extension'],
                'type'  => 'F',
                'delta' => '-'
            );
        }
    }
    return;
}

function FILECHECK_search($needle, $haystack)
{
    $retval = '';
    foreach ($haystack as $row) {
        if ($row['path'] == $needle) {
            return $row['test'];
        }
    }
    return $retval;
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
                $test = FILECHECK_search($needle,$glfDir);
                if (!empty($test)) {
                    if ($test=='R') {
                        // directory was recognized - recurse only if allowed
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
                $test = FILECHECK_search($needle,$glfFile);
                if (empty($test)) {
                    // file is not recognized, add to list
                    $data_arr[] = array('where' => $where,
                                        'path'  => $path,
                                        'file'  => $file,
                                        'type'  => 'F',
                                        'delta' => '+'
                    );
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
