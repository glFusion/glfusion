<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | logview.php                                                              |
// |                                                                          |
// | glFusion log viewer.                                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
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

define('MAX_LOG_SIZE',262144); // 256kb

USES_lib_admin();

if ( isset($_GET['log']) ) {
    $log = COM_sanitizeFilename(COM_applyFilter($_GET['log']),true);
} else if ( isset( $_POST['log']) ) {
    $log = COM_sanitizeFilename(COM_applyFilter($_POST['log']),true);
} else {
    $log = 'error.log';
}

$pageBody = '';

$menu_arr = array (
    array('url' => $_CONF['site_admin_url'],
          'text' => $LANG_ADMIN['admin_home'])
);

$pageBody  = COM_startBlock ($LANG_LOGVIEW['logview'],'', COM_getBlockTemplate ('_admin_block', 'header'));
$pageBody .= ADMIN_createMenu( $menu_arr,
                             $LANG_LOGVIEW['info'],
                             $_CONF['layout_url'] . '/images/icons/logview.'. $_IMAGE_TYPE);

$T = new Template($_CONF['path_layout'] . 'admin');
$T->set_file('page', 'logview.thtml');

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
if (empty($log)) {
    $log = $files[0];
}

$logOption = '';

for ($i = 0; $i < count($files); $i++) {
    $logOption .= '<option value="' . $files[$i] . '"';
    if ($log == $files[$i]) {
        $logOption .= ' selected="selected"';
    }
    $logOption .= '>' . $files[$i] . '</option>';
    next($files);
}

if ( isset($_POST['clearlog']) ) {
    @unlink($_CONF['path_log'] . $log);
    $timestamp = strftime( "%c" );
    $fd = fopen( $_CONF['path_log'] . $log, 'a' );
    fputs( $fd, "$timestamp - Log File Cleared \n" );
    fclose($fd);
    $_POST['viewlog'] = 1;
}

$fs = filesize ( $_CONF['path_log'] . $log );

if ( $fs > MAX_LOG_SIZE ) {
    if ( $fs > 1048576) {
        $measure = 'mb';
        $divider = 1024;
    } else {
        $measure = 'kb';
        $divider = 1;
    }
    $T->set_var('lang_too_big',sprintf($LANG_LOGVIEW['too_large'], (($fs / 1024) / $divider),$measure));
    $buffer = '';
    $seekPosition = $fs - MAX_LOG_SIZE;
    $fp = fopen($_CONF['path_log'] . $log, 'r');
    fseek($fp, $seekPosition);
    while(!feof($fp)) {
        $buffer .= fread($fp, MAX_LOG_SIZE);
    }
} else {
    $buffer = file_get_contents($_CONF['path_log'] . $log);
}
$T->set_var('log_data', @htmlentities($buffer,ENT_NOQUOTES,COM_getEncodingt()));

$T->set_var(array(
    'log_options'   => $logOption,
    'lang_logs'     => $LANG_LOGVIEW['logs'],
    'lang_view'     => $LANG_LOGVIEW['view'],
    'lang_clear'    => $LANG_LOGVIEW['clear'],
    'lang_logfile'  => $LANG_LOGVIEW['log_file'],
    'log'           => $log,
));

$pageBody .= $T->finish ($T->parse ('output', 'page'));
$pageBody .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

$display = COM_siteHeader();
$display .= $pageBody;
$display .= COM_siteFooter();
echo $display;
?>