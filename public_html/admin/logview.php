<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | logview.php                                                              |
// |                                                                          |
// | glFusion log viewer.                                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2014 by the following authors:                        |
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
require_once 'auth.inc.php';

USES_lib_admin();

if ( isset($_GET['log']) ) {
    $log = COM_applyFilter($_GET['log']);
} else if ( isset( $_POST['log']) ) {
    $log = COM_applyFilter($_POST['log']);
} else {
    $log = '';
}

$log = preg_replace('/[^a-z0-9\.\-_]/', '', $log);

$pageBody = '';

$menu_arr = array (
    array('url' => $_CONF['site_admin_url'],
          'text' => $LANG_ADMIN['admin_home'])
);

$pageBody  = COM_startBlock ($LANG_LOGVIEW['logview'],'', COM_getBlockTemplate ('_admin_block', 'header'));
$pageBody .= ADMIN_createMenu( $menu_arr,
                             $LANG_LOGVIEW['info'],
                             $_CONF['layout_url'] . '/images/icons/logview.'. $_IMAGE_TYPE
);

$pageBody .= '<form method="post" action="'.$_CONF['site_admin_url'].'/logview.php">';
$pageBody .= $LANG_LOGVIEW['logs'].':&nbsp;&nbsp;&nbsp;';
$files = array();
if ($dir = @opendir($_CONF['path_log'])) {
    while(($file = readdir($dir)) !== false) {
        if (is_file($_CONF['path_log'] . $file) && $file != 'index.html' ) {
            array_push($files,$file);
        }
    }
    closedir($dir);
}
sort($files);
$pageBody .= '<select name="log">';
if (empty($log)) {
    $log = $files[0];
}

for ($i = 0; $i < count($files); $i++) {
    $pageBody .= '<option value="' . $files[$i] . '"';
    if ($log == $files[$i]) {
        $pageBody .= ' selected="selected"';
    }
    $pageBody .= '>' . $files[$i] . '</option>';
    next($files);
}
$pageBody .= '</select>&nbsp;&nbsp;&nbsp;&nbsp;';
$pageBody .= '<input type="submit" name="viewlog" value="'.$LANG_LOGVIEW['view'].'"/>';
$pageBody .= '&nbsp;&nbsp;&nbsp;&nbsp;';
$pageBody .= '<input type="submit" name="clearlog" value="'.$LANG_LOGVIEW['clear'].'"/>';
$pageBody .= '</form>';

if ( isset($_POST['clearlog']) ) {
    @unlink($_CONF['path_log'] . $log);
    $timestamp = strftime( "%c" );
    $fd = fopen( $_CONF['path_log'] . $log, 'a' );
    fputs( $fd, "$timestamp - Log File Cleared \n" );
    fclose($fd);
    $_POST['viewlog'] = 1;
}
if ( isset($_POST['viewlog']) ) {
    $pageBody .= '<p><strong>'.$LANG_LOGVIEW['log_file'].': ' . $log . '</strong></p>';
    $pageBody .= '<div style="margin:10px 0 5px;border-bottom:1px solid #cccccc;"></div>';
    $pageBody .= '<div class="logview" style="overflow:scroll; height:500px;"><pre>';
    $pageBody .= @htmlentities(implode('', file($_CONF['path_log'] . $log)),ENT_NOQUOTES,COM_getEncodingt());
    $pageBody .= "</pre></div>";
}

$pageBody .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

$display = COM_siteHeader();
$display .= $pageBody;
$display .= COM_siteFooter();
echo $display;
exit;
?>