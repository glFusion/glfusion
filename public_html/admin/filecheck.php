<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | filecheck.php                                                            |
// |                                                                          |
// | glFusion File Validation Utility                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
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

if ( defined('DEMO_MODE') ) {
    echo COM_refresh($_CONF['site_admin_url'] . '/envcheck.php');
    exit;
}

if (!SEC_inGroup ('Root')) {
    $display = COM_siteHeader ('menu', $MESSAGE[30])
        . COM_showMessageText($MESSAGE[200],$MESSAGE[30],true,'error')
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
    global $_CONF, $D, $F, $data_arr, $max_time;

    // check for missing directories
    foreach( $D AS $key => $value ) {
        // replace the generic prefix with the actual directory
        $rdir = $key;
        if (strtolower(substr($rdir,0,7)) == 'private') {
            $where = 'private';
            $rdir = (strtolower($rdir) <> $where) ? $_CONF['path'] . substr($rdir,8) : substr($_CONF['path'],0,-1);
        } elseif (strtolower(substr($rdir,0,11)) == 'public_html') {
            $where = 'public_html';
            $rdir = (strtolower($rdir) <> $where) ? $_CONF['path_html'] . substr($rdir,12) : substr($_CONF['path_html'],0,-1);
        } elseif ( $rdir == 'README' || $rdir == '.gitignore' ) {
            continue;
        } else {
            COM_errorlog( 'filecheck: unexpected root dirspec(not private/ or public_html/): ' . $rdir );
        }
        // how we check depends upon whether we were allowed to recurse there
        $test = $value[0];
        switch ($test) {

            case 'E':
                // we were not allowed to recurse here, check manually
                if (!is_dir($rdir)) {
                    $data_arr[] = array(
                        'where'     => $where,
                        'type'      => 'D',
                        'delta'     => '-',
                        'location'  => $rdir . '/',
                    );
                }
                break;

            case 'R':
                // we recursed here, unpinged dirs must be missing
                if (stristr('!',$value)) {
                    $data_arr[] = array(
                        'where'     => $where,
                        'type'  => 'D',
                        'delta' => '-',
                        'location'  => $rdir . '/',
                    );
                }
                break;
        }
    }

    // check for missing files
    foreach( $F as $key => $value) {

        // replace the generic prefix with the actual directory
        $rdir = $key;
        if (strtolower(substr($rdir,0,7)) == 'private') {
            $where = 'private';
            $rdir = (strtolower($rdir) <> $where) ? $_CONF['path'] . substr($rdir,8) : substr($_CONF['path'],0,-1);
        } elseif (strtolower(substr($rdir,0,11)) == 'public_html') {
            $where = 'public_html';
            $rdir = (strtolower($rdir) <> $where) ? $_CONF['path_html'] . substr($rdir,12) : substr($_CONF['path_html'],0,-1);
        } elseif ($rdir == 'README' || $rdir == '.gitignore' ) {
            continue;
        } else {
            COM_errorlog( 'filecheck: unexpected root dirspec(not private/ or public_html/): ' . $rdir );
        }

        // ok now check for unpinged files that are not set to be ignored
        $test = $value[0];
        if ((!stristr('!',$value)) && ($test <> 'I')) {
            // ostensibly, this file was not found - get the dir and file parts
            $pathinfo = pathinfo($rdir);
            $dirname = $pathinfo['dirname'];
            $filename = $pathinfo['filename']. (isset($pathinfo['extension']) ? '.'.$pathinfo['extension'] : '');

            // check to see if we were allowed to recurse into this dir
            if ($test == 'R') {
                // yes, we were allowed to look here, and the file was not found
                $data_arr[] = array(
                    'where'     => $where,
                    'type'      => 'F',
                    'delta'     => '-',
                    'location'  => $rdir,
               );

            } else {
                // no, we were not allowed to look here, so test manually
                if(!file_exists($rdir)) {
                    $data_arr[] = array(
                        'where'     => $where,
                        'type'      => 'F',
                        'delta'     => '-',
                        'location'  => $rdir,
                    );
                }
            }

        }
    }
    return true;
}

function FILECHECK_scanPositive( $path = '.', $where, $level = 0, $prefix=array())
{
    global $glfDir, $D, $glfFile, $F, $glfIgnore, $data_arr, $max_time;

    $ignore = $glfIgnore;

    if (FILECHECK_timer('check') > $max_time ) {
        return false;
    }

    $dh = @opendir( $path );

    while( false !== ($file = readdir($dh)) ) {
        // ignore some files & directories
        $test = '';
        $needle = $where . '/';
        if (!in_array( $file, $ignore ) ){
            // not on the ignore list, so determine whether it is a file or dir
            if (is_dir( "$path/$file" )) {
                // this is a directory - construct the search term
                foreach($prefix AS $pdir ) {
                    $needle .= $pdir .'/';
                }
                $needle .= $file;
                $test = (isset($D[$needle])) ? $D[$needle] : '';
                if (!empty($test)) {
                    // directory was recognized, set found flag
                    $D[$needle] .= '!';
                    if ($test == 'R') {
                        // recurse into this directory only if allowed
                        $prefix[] = $file;
                        FILECHECK_scanPositive( "$path/$file", $where, ($level+1), $prefix );
                        array_pop($prefix);
                    }
                    // flag unbundled plugin dirs that are discovered
                    if ($test == 'P') {
                        $data_arr[] = array(
                            'where'     => $where,
                            'type'      => 'P',
                            'delta'     => '+',
                            'location'  => $path . '/' . $file,
                        );
                    }
                } else {
                    // directory is not recognized, add to list if not ignored
                    // flag directories associated with unbundled plugins
                    if ($test <> 'I') {
                        $data_arr[] = array(
                            'where'     => $where,
                            'type'      => 'D',
                            'delta'     => '+',
                            'location'  => $path . '/' . $file,
                        );
                    }
                    // fix to recurse unrecognized directories
                    $prefix[] = $file;
//                    FILECHECK_scanPositive( "$path/$file", $where, ($level+1), $prefix );
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
                $test = (isset($F[$needle])) ? $F[$needle] : '';
                if (empty($test)) {
                    // file is not recognized, add to list
                    $data_arr[] = array('where' => $where,
                                        'type'  => 'F',
                                        'delta' => '+',
                                        'location'  => $path . '/' . $file,
                    );
                } else {
                    $F[$needle] .= '!';
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

function FILECHECK_chkListField($A)
{
    $retval = (($A['type'] == 'F') && ($A['delta'] == '+') && FILECHECK_isWriteable($A['location'])) ? true : false;
    return $retval;
}

function FILECHECK_getListField($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_FILECHECK;

    $retval = '<span style="font-size:smaller">';

    switch($fieldname) {

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
            $retval .= ($fieldvalue<>'-') ? $A['type'] . ' ' . $LANG_FILECHECK['added'] : $A['type'] . ' ' . $LANG_FILECHECK['missing'];
            break;

        default:
            $retval .= $fieldvalue;
            break;
    }

    $retval .= '</span>';

    return $retval;
}

function FILECHECK_foldArrays() {
    global $_CONF, $glfDir, $glfFile, $D, $F;

    $fixAdminPath = 0;

    $adminPath = substr($_CONF['site_admin_url'],strlen($_CONF['site_url'])+1);
    if ( $adminPath != 'admin') {
        $fixAdminPath = 1;
    }

    $D = array();
    foreach($glfDir AS $dir) {
        if ( $fixAdminPath ) {
            $dir['path'] = str_replace('public_html/admin','public_html/'.$adminPath,$dir['path']);
        }
        $D[$dir['path']] = $dir['test'];
    }

    $F = array();
    foreach($glfFile AS $file) {
        if ( $fixAdminPath ) {
            $file['path'] = str_replace('public_html/admin','public_html/'.$adminPath,$file['path']);
        }

        $F[$file['path']] = $file['test'];
    }
}

function FILECHECK_scan()
{
    global $_CONF, $LANG_ADMIN, $LANG_FILECHECK, $data_arr;

    $retval = false;
    $data_arr = array();
    $form_arr = array();

    // detect unbundled plugins
    FILECHECK_addPlugins();

    FILECHECK_foldArrays();

    // begin timing scan process
    $start_time = FILECHECK_timer('start');

    if ( FILECHECK_scanPositive(substr($_CONF['path'],0,-1),'private')
         && FILECHECK_scanPositive(substr($_CONF['path_html'],0,-1),'public_html')
         && FILECHECK_scanNegative() ) {

        // scanning succeeded, sort the array and then capture the elapsed time
        sort($data_arr);

        $elapsed_time = FILECHECK_timer('stop');

        // build the menu
        $retval = FILECHECK_scanMenu();

        // build the list of results
        $header_arr = array(
            array('text' => $LANG_FILECHECK['where'], 'field' => 'where', 'align' => 'center'),
            array('text' => $LANG_FILECHECK['type'], 'field' => 'type', 'align' => 'center'),
            array('text' => $LANG_FILECHECK['delta'], 'field' => 'delta', 'align' => 'center'),
            array('text' => $LANG_FILECHECK['location'], 'field' => 'location')
        );

        $text_arr = array(
            'form_url'   => $_CONF['site_admin_url'] . '/filecheck.php'
        );

        $option_arr = array(
            'chkselect' => true,
            'chkall' => false,
            'chkfield' => 'location',
            'chkname' => 'actionitem',
            'chkfunction' => 'FILECHECK_chkListField',
        );

//        $top = sprintf($LANG_FILECHECK['elapsed'], $elapsed_time);
//        $form_arr = array('top' => $top);

        $retval .= ADMIN_simpleList("FILECHECK_getListField", $header_arr, $text_arr, $data_arr, $option_arr, $form_arr );
    }
    return $retval;
}

function FILECHECK_delete()
{
    global $_CONF, $_POST;

    if ( defined('DEMO_MODE') ) {
        return '';
    }

    $n = count($_POST['actionitem']);
    for ($i=0; $i < $n; $i++) {
        $filespec = COM_applyFilter($_POST['actionitem'][$i]);
        @unlink($filespec);
        COM_errorLog('filecheck.php: admin deleted: ' . $filespec);
    }
    return $n;
}


// MAIN ========================================================================

$action = '';
$expected = array('delbutton_x','cancel','scan','expired');
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

    case 'scan':
        $results = FILECHECK_scan();
        if (!$results) {
            echo COM_refresh($_CONF['site_admin_url'] . '/filecheck.php?expired=x');
            exit;
        }
        break;

    case 'cancel':
        echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        exit;
        break;

    case 'expired' :
        $display .= FILECHECK_expirationMenu($max_time);
        $display .= $LANG_FILECHECK['aborted'] . COM_siteFooter();
        break;

    case 'delbutton_x':
        $files = FILECHECK_delete();
        if ($files > 0) {
            $desc = ($files > 1) ? 'files were' : 'file was';
            $display .= COM_showMessageText(sprintf($LANG_FILECHECK['removed'],$files,$desc));
        }
}

if (empty($results)) {
    $display .= FILECHECK_initMenu($max_time);
    $display .= $LANG_FILECHECK['working'] . COM_siteFooter();
    $display .= COM_refresh($_CONF['site_admin_url'] . '/filecheck.php?scan=x&amp;stri=x');
} else {
    $display .= $results;
    $display .= COM_siteFooter();
}

echo $display;

?>
