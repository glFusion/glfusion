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
// | Copyright (C) 2008-2009 by the following authors:                        |
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
//
require_once '../lib-common.php';
require_once 'auth.inc.php';
require_once 'filecheck_data.php';
USES_lib_admin();

$fullIgnore = array('cgi-bin','.','..','.svn','layout_cache','tn','orig','disp','custom');

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

function FILECHECK_getDirectory( $path = '.', $where,$level = 0, $prefix=array())
{
    global $glfusionFiles, $glfusionDir, $fullIgnore, $data_arr;

    $ignore = $fullIgnore;

    $dh = @opendir( $path );

    while( false !== ( $file = readdir( $dh ) ) ){
        if( !in_array( $file, $ignore ) ){
            if( is_dir( "$path/$file" ) ){
                $tdir = $where .'/';
                foreach($prefix AS $stuff ) {
                    $tdir .= $stuff .'/';
                }
                $tdir .= $file;
                $tdir = str_replace("-","_",$tdir);
                if ( $glfusionDir[$tdir] == 1 ) {
                    $prefix[] = $file;
                    FILECHECK_getDirectory( "$path/$file", $where,($level+1),$prefix );
                    array_pop($prefix);
                }
            } else {
                $indexVar = $where .'/';
                $bdir = '';
                foreach($prefix AS $stuff ) {
                    $indexVar .= $stuff .'/';
                    $bdir .= $stuff .'/';
                }
                $dir = $indexVar;
                $indexVar .= $file;
                $indexVar = str_replace("-","_",$indexVar);
                if ( !isset($glfusionFiles[$indexVar])) {
                    $data_arr[] = array('where'    => $where,
                                        'dir'      => $bdir,
                                        'filename' => $file
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
        case 'where':
            $retval = $fieldvalue . '/';
            break;
        case 'delete':
            $retval = '<input type="checkbox" name="sel[]" value="' . $counter . '"'.XHTML.'>';
            $retval .= '<input type="hidden" name="dir[' . $counter . ']" value="';
            $retval .= ($A['where'] == 'private' ? $_CONF['path']  : $_CONF['path_html'] ) . $A['dir'] . $A['filename'] .'" '.XHTML.'>';
            $counter++;
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
        $_CONF['layout_url'] . '/images/icons/envcheck.png'
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    $header_arr = array(
        array('text' => $LANG_ADMIN['delete'],    'field' => 'delete', 'align' => 'center'),
        array('text' => $LANG_FILECHECK['where'], 'field' => 'where'),
        array('text' => $LANG_FILECHECK['directory'],  'field' => 'dir'),
        array('text' => $LANG_FILECHECK['filename'],  'field' => 'filename')
    );
    $data_arr = array();
    $text_arr = array();
    $form_arr = array();

    $text_arr = array(
        'form_url'   => $_CONF['site_admin_url'] . '/filecheck.php'
    );

    $bottom = '<br' . XHTML . '><input type="submit" name="delete" value="' . $LANG_ADMIN['delete'] . '"' . XHTML . '>'
            . '&nbsp;&nbsp;<input type="submit" name="cancel" value="' . $LANG_ADMIN['cancel'] . '"' . XHTML . '>';

    $form_arr = array('bottom' => $bottom);

    FILECHECK_getDirectory($_CONF['path'],'private');
    FILECHECK_getDirectory($_CONF['path_html'],'public_html');

    sort($data_arr);

    $retval .= ADMIN_simpleList("FILECHECK_getListField", $header_arr, $text_arr, $data_arr,NULL,$form_arr);

    return $retval;
}

function FILECHECK_delete( )
{
    global $_CONF, $_POST;

    if ( defined('DEMO_MODE') ) {
        return '';
    }

    $rc = '';

    $numItems = count($_POST['sel']);
    for ($i=0; $i < $numItems; $i++) {
        $filename = COM_applyFilter($_POST['sel'][$i]);
        $where    = COM_applyFilter($_POST['dir'][$filename]);
        $rc .= $where . '<br />';
        @unlink($where);
    }
    return $rc;
}

// MAIN ========================================================================


$action = '';
$expected = array('delete','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    }
}

$rc = '';
switch ($action) {

    case 'cancel':
        echo COM_refresh ($_CONF['site_admin_url'] . '/index.php');
        exit;
        break;

    case 'delete':
        $rc = FILECHECK_delete( );
        break;
}

$display .= COM_siteHeader();
if (!empty($rc)) {
    $display .= COM_showMessage($LANG_FILECHECK['removed']);
}
$display .= FILECHECK_list() . COM_siteFooter();

echo $display;

?>
