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

if (!SEC_inGroup ('Root')) {
    $display .= COM_siteHeader ('menu');
    $display .= COM_startBlock ($LANG20[1], '',
                                COM_getBlockTemplate ('_msg_block', 'header'));
    $display .= '<p>' . $LANG20[6] . '</p>';
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

$fullIgnore = array('cgi-bin','.','..','.svn','layout_cache','tn','orig','disp');

require_once 'filecheck_data.php';

/*
 * Main Processing
 */

$mode = '';

if (isset ($_POST['submit'])) {
    $mode = COM_applyFilter($_POST['submit']);
}

$rc = '';

if ($mode == 'Cancel') {
    echo COM_refresh ($_CONF['site_admin_url'] . '/index.php');
    exit;
} elseif ($mode == 'Delete') {
    $rc = fileCheckDelete( );
}

require_once $_CONF['path_system'] . 'lib-admin.php';

$header_arr = array(
    array('text' => 'Where', 'field' => 'where'),
    array('text' => 'Directory',  'field' => 'dir'),
    array('text' => 'Filename',  'field' => 'filename'),
    array('text' => 'Delete',    'field' => 'delete')
);
$data_arr = array();
$text_arr = array();
$form_arr = array();

    $text_arr = array(
        'has_extras' => true,
        'title'      => "FileCheck: This list shows files that glFusion does not recognize as files included in the glFusion v".GVERSION." distribution.  This DOES NOT mean you should simple delete the files below, instead use this tool to assist you in removing files that have been removed or moved in the glFusion distribution.",
        'form_url'   => $_CONF['site_admin_url'] . '/filecheck.php'
    );

$form_arr = array('bottom' => '<input type="submit" name="submit" value="Delete"' . XHTML . '>&nbsp;&nbsp;<input type="submit" name="submit" value="Cancel"' . XHTML . '>');

getDirectory($_CONF['path'],'private');
getDirectory($_CONF['path_html'],'public_html');

sort($data_arr);

$retval .= ADMIN_simpleList("ADMIN_getListField_filecheck", $header_arr, $text_arr, $data_arr,NULL,$form_arr);

echo COM_siteHeader();
if ( $rc != '' ) {
    echo COM_showMessage('Selected files have been removed.');
}
echo $retval;
echo COM_siteFooter();
exit;


function getDirectory( $path = '.', $where,$level = 0, $prefix=array()){
    global $glfusionFiles, $glfusionDir,$fullIgnore,$data_arr;

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
                    getDirectory( "$path/$file", $where,($level+1),$prefix );
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


function ADMIN_getListField_filecheck($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF;

    static $counter = 0;

    $retval = false;

    switch($fieldname) {
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

function fileCheckDelete( ) {
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
?>