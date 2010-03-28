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

function FILECHECK_isWriteable( $perms )
{
    return (($perms & 0x0080) || ($perms & 0x0010) || ($perms & 0x0002));
}

function FILECHECK_permString( $perms )
{

    if (($perms & 0xC000) == 0xC000) {
        // Socket
        $info = 's';
    } elseif (($perms & 0xA000) == 0xA000) {
        // Symbolic Link
        $info = 'l';
    } elseif (($perms & 0x8000) == 0x8000) {
        // Regular
        $info = '-';
    } elseif (($perms & 0x6000) == 0x6000) {
        // Block special
        $info = 'b';
    } elseif (($perms & 0x4000) == 0x4000) {
        // Directory
        $info = 'd';
    } elseif (($perms & 0x2000) == 0x2000) {
        // Character special
        $info = 'c';
    } elseif (($perms & 0x1000) == 0x1000) {
        // FIFO pipe
        $info = 'p';
    } else {
        // Unknown
        $info = 'u';
    }

    // Owner
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ?
                (($perms & 0x0800) ? 's' : 'x' ) :
                (($perms & 0x0800) ? 'S' : '-'));

    // Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ?
                (($perms & 0x0400) ? 's' : 'x' ) :
                (($perms & 0x0400) ? 'S' : '-'));

    // World
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ?
                (($perms & 0x0200) ? 't' : 'x' ) :
                (($perms & 0x0200) ? 'T' : '-'));

    return $info;
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
                'delta' => '-',
                'perms' => '',
                'preq'  => $glfDir[$i]['preq']
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
                'delta' => '-',
                'perms' => '',
                'preq'  => $glfFile[$i]['preq']
            );
        }
    }
    return;
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
                // search distribution directory list
                $D = array();
                foreach($glfDir AS $D) {
                    if ($D['path'] == $needle) {
                        $test = $D['test'];
                        $preq = $D['preq'];
                    }
                }
                if (!empty($test)) {
                    if ($test=='R') {
                        // directory was recognized - recurse only if allowed
                        $prefix[] = $file;
                        FILECHECK_scanPositive( "$path/$file", $where, ($level+1), $prefix );
                        array_pop($prefix);
                    }
                } else {
                    // directory is not recognized, add to list
                    $perms = fileperms("$path/$file");
                    $data_arr[] = array(
                        'where' => $where,
                        'path'  => $path . '/' . $file,
                        'file'  => '',
                        'type'  => 'D',
                        'delta' => '+',
                        'perms' => $perms,
                        'preq'  => ''
                    );
                }
            } else {
                // this is a file - get it's permissions for later testing
                $perms = fileperms("$path/$file");
                // construct the search term
                foreach($prefix AS $pdir ) {
                    $needle .= $pdir .'/';
                }
                $needle .= $file;
                // search the distribution file list
                $F = array();
                foreach($glfFile AS $F) {
                    if ($F['path'] == $needle) {
                        $test = $F['test'];
                        $preq = $F['preq'];
                    }
                }
                if (empty($test)) {
                    // file is not recognized, add to list
                    $data_arr[] = array('where' => $where,
                                        'path'  => $path,
                                        'file'  => $file,
                                        'type'  => 'F',
                                        'delta' => '+',
                                        'perms' => $perms,
                                        'preq'  => $preq
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
            if ((FILECHECK_isWriteable($A['perms'])) && $A['delta'] == '+' && $A['type'] == 'F') {
                $retval = '<input type="checkbox" name="sel[]" value="' . $counter . '"'.XHTML.'>';
                $retval .= '<input type="hidden" name="dir[' . $counter . ']" value="';
                $retval .= $A['path'] . '/' . $A['file'] .'" '.XHTML.'>';
                $counter++;
            } else {
                $retval = '';
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
        case 'perms':
            $retval = ($A['delta'] == '+') ? FILECHECK_permString($fieldvalue) : '';
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
        array('text' => $LANG_FILECHECK['file'],  'field' => 'file'),
        array('text' => $LANG_FILECHECK['perms'], 'field' => 'perms', 'align' => 'center')
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

    FILECHECK_scanPositive(substr($_CONF['path'],0,-1),'private');
    FILECHECK_scanPositive(substr($_CONF['path_html'],0,-1),'public_html');
    FILECHECK_scanNegative();

    sort($data_arr);

    $retval .= ADMIN_simpleList("FILECHECK_getListField", $header_arr, $text_arr, $data_arr, NULL, $form_arr);

    return $retval;
}

function FILECHECK_delete( )
{
    global $_CONF, $_POST;

    if ( defined('DEMO_MODE') ) {
        return '';
    }

    $numItems = count($_POST['sel']);
    for ($i=0; $i < $numItems; $i++) {
        $index = COM_applyFilter($_POST['sel'][$i]);
        $filespec = COM_applyFilter($_POST['dir'][$index]);
        @unlink($filespec);
        COM_errorLog('filecheck.php: admin deleted: ' . $filespec);
    }
    return $numItems;
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
$display .= FILECHECK_list() . COM_siteFooter();

echo $display;

?>
