<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | logview.php                                                              |
// |                                                                          |
// | glFusion log viewer.                                                     |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Original work by                                            |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tom Willett        - twillett@users.sourceforge.net             |
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
require_once $_CONF['path_system'] . 'lib-admin.php';

if (!SEC_inGroup('Root')) {
    $display = COM_siteHeader ('menu');
    $display .= COM_showMessageText($LANG27[12],$LANG27[12],true);
    $display .= COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to illegally access the log viewer utility.");
    echo $display;
    exit;
}

if ( isset($_GET['log']) ) {
    $log = COM_applyFilter($_GET['log']);
} else if ( isset( $_POST['log']) ) {
    $log = COM_applyFilter($_POST['log']);
} else {
    $log = '';
}

$log = preg_replace('/[^a-z0-9\.\-_]/', '', $log);

$retval = '';

$display = COM_siteHeader();

$menu_arr = array (
    array('url' => $_CONF['site_admin_url'],
          'text' => $LANG_ADMIN['admin_home'])
);

$retval  = COM_startBlock ($LANG_LOGVIEW['logview'],'', COM_getBlockTemplate ('_admin_block', 'header'));
$retval .= ADMIN_createMenu( $menu_arr,
                             $LANG_LOGVIEW['info'],
                             $_CONF['layout_url'] . '/images/icons/logview.'. $_IMAGE_TYPE
);

$retval .= '<form method="post" action="'.$_CONF['site_admin_url'].'/logview.php">';
$retval .= $LANG_LOGVIEW['logs'].':&nbsp;&nbsp;&nbsp;';
$files = array();
if ($dir = @opendir($_CONF['path_log'])) {
    while(($file = readdir($dir)) !== false) {
        if (is_file($_CONF['path_log'] . $file) && $file != 'index.html' ) {
            array_push($files,$file);
        }
    }
    closedir($dir);
}
$retval .= '<select name="log">';
if (empty($log)) {
    $log = $files[0];
}

for ($i = 0; $i < count($files); $i++) {
    $retval .= '<option value="' . $files[$i] . '"';
    if ($log == $files[$i]) {
        $retval .= ' selected="selected"';
    }
    $retval .= '>' . $files[$i] . '</option>';
    next($files);
}
$retval .= '</select>&nbsp;&nbsp;&nbsp;&nbsp;';
$retval .= '<input type="submit" name="viewlog" value="'.$LANG_LOGVIEW['view'].'"'.XHTML.'>';
$retval .= '&nbsp;&nbsp;&nbsp;&nbsp;';
$retval .= '<input type="submit" name="clearlog" value="'.$LANG_LOGVIEW['clear'].'"'.XHTML.'>';
$retval .= '</form>';

if ( isset($_POST['clearlog']) ) {
    @unlink($_CONF['path_log'] . $log);
    $timestamp = strftime( "%c" );
    $fd = fopen( $_CONF['path_log'] . $log, 'a' );
    fputs( $fd, "$timestamp - Log File Cleared \n" );
    fclose($fd);
    $_POST['viewlog'] = 1;
}
if ( isset($_POST['viewlog']) ) {
    $retval .= '<p><strong>'.$LANG_LOGVIEW['log_file'].': ' . $log . '</strong></p>';
    $retval .= '<div style="margin:10px 0 5px;border-bottom:1px solid #cccccc;"></div>';
    $retval .= '<div style="overflow:scroll; height:500px;"><pre>';
    $retval .= htmlentities(implode('', file($_CONF['path_log'] . $log)),ENT_NOQUOTES,COM_getEncodingt());
    $retval .= "</pre></div>";
}

$retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

$display .= $retval;
$display .= COM_siteFooter();
echo $display;
exit;
?>
