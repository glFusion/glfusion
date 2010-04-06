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
require_once $_CONF['path'] . 'filecheck_data.php';

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

function FILECHECK_tick()
{
    list($usec, $sec) = explode( ' ',microtime());
    return (float)$sec + (float)$usec;
}

function FILECHECK_timer( $action='' )
{
    static $start;
    static $run = false;
USES_lib_admin();

    $retval = 0;
    switch ($action) {
        case 'start':
            $run = true;
            $start = FILECHECK_tick();
            $retval = $start;
            break;
        case 'stop':
            $run = false;
            $retval = round(FILECHECK_tick() - $start, 5);
            break;
        case 'check':
            $retval = ($run) ? round(FILECHECK_tick() - $start, 5) : false;
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
    global $_CONF, $glfDir, $glfFile, $data_arr, $max_time;

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
                if (!isset($dir['ping'])) {
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
        if (FILECHECK_timer('check') > $max_time ) {
            return false;
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
        // ok now check for unpinged files that are not set to be ignored
        if ((!isset($file['ping'])) && ($file['test'] <> 'I')) {
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
        if (FILECHECK_timer('check') > $max_time ) {
            return false;
        }
    }
    return true;
}

function FILECHECK_search($needle, $haystack)
{
    $hsCount = count($haystack);
    for ($i=0; $i < $hsCount; $i++) {
        if ($haystack[$i]['path'] == $needle) {
            return array($haystack[$i]['test'],$i);
        }
    }
    return array('',0);
}

function FILECHECK_scanPositive( $path = '.', $where, $level = 0, $prefix=array())
{
    global $glfFile, $glfDir, $glfIgnore, $data_arr, $max_time;

    $ignore = $glfIgnore;

    if (FILECHECK_timer('check') > $max_time ) {
        return false;
    }

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
                    if ($test == 'R') {
                        // recurse into this directory only if allowed
                        $prefix[] = $file;
                        FILECHECK_scanPositive( "$path/$file", $where, ($level+1), $prefix );
                        array_pop($prefix);
                    }
                    // flag unbundled plugin dirs that are discovered
                    if ($test == 'P') {
                        $data_arr[] = array(
                            'where' => $where,
                            'path'  => $path . '/' . $file,
                            'file'  => '',
                            'type'  => 'P',
                            'delta' => '+'
                        );
                    }
                } else {
                    // directory is not recognized, add to list if not ignored
                    // flag directories associated with unbundled plugins
                    if ($test <> 'I') {
                        $data_arr[] = array(
                            'where' => $where,
                            'path'  => $path . '/' . $file,
                            'file'  => '',
                            'type'  => 'D',
                            'delta' => '+'
                        );
                    }
                    // fix to recurse unrecognized directories
                    $prefix[] = $file;
                    FILECHECK_scanPositive( "$path/$file", $where, ($level+1), $prefix );
                    array_pop($prefix);
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

    return true;

}

function FILECHECK_initMenu($max_time)
{
    global $_CONF, $LANG_ADMIN, $LANG_FILECHECK, $LANG01;

    $menu_arr = array (
        array('url'  => $_CONF['site_admin_url'].'/envcheck.php',
              'text' => $LANG01['env_check']),
        array('url'  => $_CONF['site_admin_url'].'/filecheck.php?expired=x',
              'text' => $LANG_FILECHECK['abort']),
        array('url'  => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval = COM_startBlock($LANG_FILECHECK['filecheck'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        sprintf($LANG_FILECHECK['scan'], $max_time + 1),
        $_CONF['layout_url'] . '/images/icons/filecheck.png'
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

function FILECHECK_expirationMenu($max_time)
{
    global $_CONF, $LANG_ADMIN, $LANG_FILECHECK, $LANG01;

    $menu_arr = array (
        array('url'  => $_CONF['site_admin_url'].'/filecheck.php',
              'text' => $LANG_FILECHECK['recheck']),
        array('url'  => $_CONF['site_admin_url'].'/envcheck.php',
              'text' => $LANG01['env_check']),
        array('url'  => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval = COM_startBlock($LANG_FILECHECK['filecheck'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_FILECHECK['expiration1'] .
        sprintf($LANG_FILECHECK['expiration2'],$max_time + 1),
        $_CONF['layout_url'] . '/images/icons/filecheck.png'
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

function FILECHECK_scanMenu()
{
    global $_CONF, $LANG_ADMIN, $LANG_FILECHECK, $LANG01;

    $menu_arr = array (
        array('url'  => $_CONF['site_admin_url'].'/filecheck.php',
              'text' => $LANG_FILECHECK['recheck']),
        array('url'  => $_CONF['site_admin_url'].'/envcheck.php',
              'text' => $LANG01['env_check']),
        array('url'  => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval = COM_startBlock($LANG_FILECHECK['filecheck'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        sprintf($LANG_FILECHECK['results'], GVERSION),
        $_CONF['layout_url'] . '/images/icons/filecheck.png'
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

function FILECHECK_addPlugins()
{
    global $_TABLES, $glfPlugins, $glfDir, $data_arr;

    $result = DB_query("SELECT pi_name FROM {$_TABLES['plugins']} WHERE 1=1");
    $num_plugins = DB_numRows($result);
    for ($i = 0; $i < $num_plugins; $i++) {
        $A = DB_fetchArray($result);
        if(!in_array($A['pi_name'], $glfPlugins)) {
            // dirs that are associated with unbundled plugins need to be
            // handled a little differently
            // by setting the 'P' flag, we tell filecheck to not recurse those
            // directories, but if they are discovered, then we will flag them
            // in our results array
            $glfDir[] = array(
                'test' => 'P',
                'preq' => 'R',
                'path' => 'private/plugins/' . $A['pi_name']
            );
            $glfDir[] = array(
                'test' => 'P',
                'preq' => 'R',
                'path' => 'public_html/' . $A['pi_name']
            );
            $glfDir[] = array(
                'test' => 'P',
                'preq' => 'R',
                'path' => 'public_html/admin/plugins/' . $A['pi_name']
            );
        }
    }
    return true;
}

function FILECHECK_getListField($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_FILECHECK;

    static $counter = 0;

    $retval = '<span style="font-size:smaller">';

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

        case 'type':
            switch ($A['type']) {
                case 'F':
                    $retval .= $LANG_FILECHECK['file'];
                    break;
                case 'D':
                    $retval .= $LANG_FILECHECK['dir'];
                    break;
                case 'P':
                    $retval .= $LANG_FILECHECK['plugin'];
                    break;
            }
            break;

        case 'delta':
            $retval .= ($fieldvalue<>'-') ? $type . ' ' . $LANG_FILECHECK['added'] : $type . ' ' . $LANG_FILECHECK['missing'];
            break;

        case 'location':
            $retval .= $A['path'] . '/' . $A['file'];
            break;

        default:
            $retval .= $fieldvalue;
            break;
    }

    $retval .= '</span>';

    return $retval;
}

function FILECHECK_scan()
{
    global $_CONF, $LANG_ADMIN, $LANG_FILECHECK, $data_arr;

    $retval = false;
    $data_arr = array();

    // detect unbundled plugins
    FILECHECK_addPlugins();

    // begin timing scan process
    $start_time = FILECHECK_timer('start');

    if ( FILECHECK_scanPositive(substr($_CONF['path'],0,-1),'private') &&
         FILECHECK_scanPositive(substr($_CONF['path_html'],0,-1),'public_html') &&
         FILECHECK_scanNegative()) {

        // scanning succeeded, sort the array and then capture the elapsed time
        sort($data_arr);
        $elapsed_time = FILECHECK_timer('stop');

        // build the menu
        $retval = FILECHECK_scanMenu();

        // build the list of results
        $header_arr = array(
            array('text' => $LANG_ADMIN['delete'],   'field' => 'delete', 'align' => 'center'),
            array('text' => $LANG_FILECHECK['where'], 'field' => 'where', 'align' => 'center'),
            array('text' => $LANG_FILECHECK['type'], 'field' => 'type', 'align' => 'center'),
            array('text' => $LANG_FILECHECK['delta'], 'field' => 'delta', 'align' => 'center'),
            array('text' => $LANG_FILECHECK['location'], 'field' => 'location')
        );

        $text_arr = array(
            'form_url'   => $_CONF['site_admin_url'] . '/filecheck.php'
        );

        $bottom = '<br' . XHTML . '><input type="submit" onclick="return confirm(\'' . $LANG_FILECHECK['confirm'] . '\');" name="delete" value="' . $LANG_ADMIN['delete'] . '"' . XHTML . '>'
                    . '&nbsp;&nbsp;<input type="submit" name="cancel" value="' . $LANG_ADMIN['cancel'] . '"' . XHTML . '>'
                    . '&nbsp;&nbsp;' . sprintf($LANG_FILECHECK['elapsed'], $elapsed_time);
        $form_arr = array('bottom' => $bottom);

        $retval .= ADMIN_simpleList("FILECHECK_getListField", $header_arr, $text_arr, $data_arr, NULL, $form_arr);
    }
    return $retval;
}

function FILECHECK_delete()
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
$expected = array('delete','cancel','scan','expired');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
        $action = $provided;
    }
}

$files = 0;
$results = '';
$max_time = ini_get('max_execution_time') - 1;

$display = COM_siteHeader();

switch ($action) {

    case 'cancel':
        echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        exit;
        break;

    case 'delete':
        $files = FILECHECK_delete();
        break;

    case 'expired' :
        $display .= FILECHECK_expirationMenu($max_time);
        $display .= $LANG_FILECHECK['aborted'] . COM_siteFooter();
        break;

    case 'scan':
        $results = FILECHECK_scan();
        if (!$results) {
            echo COM_refresh($_CONF['site_admin_url'] . '/filecheck.php?expired=x');
            exit;
        }

    default:
        if ($files > 0) {
            $desc = ($files > 1) ? 'files were' : 'file was';
            $display .= COM_showMessageText(sprintf($LANG_FILECHECK['removed'],$files,$desc));
        }
        if (empty($results)) {
            $display .= FILECHECK_initMenu($max_time);
            $display .= $LANG_FILECHECK['working'] . COM_siteFooter();
            $display .= COM_refresh($_CONF['site_admin_url'] . '/filecheck.php?scan=x');
        } else {
            $display .= $results;
            $display .= COM_siteFooter();
        }
        break;
}

echo $display;

?>
