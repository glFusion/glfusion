<?php
/**
* glFusion CMS
*
* glFusion Log Viewer
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008
*
*  Authors: Tom Willett        - twillett@users.sourceforge.net
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Log\Log;

define('MAX_LOG_SIZE',262144); // 256kb

USES_lib_admin();

$availableLogs = array();
$info = Log::getLogs();
foreach($info AS $name => $item) {
    $availableLogs[$name] = $item->fileName;
}

$files = array();
if ($dir = @opendir($_CONF['path_log'])) {
    while(($file = readdir($dir)) !== false) {
        if (is_file($_CONF['path_log'] . $file) && $file != 'index.html' && !in_array($file,$availableLogs)) {
            $availableLogs[$file] = $file;
        }
    }
    closedir($dir);
}

if ( isset($_GET['log']) ) {
    $log = COM_sanitizeFilename(COM_applyFilter($_GET['log']),true);
} else if ( isset( $_POST['log']) ) {
    $log = COM_sanitizeFilename(COM_applyFilter($_POST['log']),true);
} else {
    $log = 'system';
}

if ( isset($_POST['clearlog']) ) {
    if (isset($info[$log])) {
        Log::close($log);
        @unlink($_CONF['path_log'] . $availableLogs[$log]);
        clearstatcache();
        Log::config($log,
            array(  'type'  => 'file',
                    'path'  => $_CONF['path_log'],
                    'file'  => $availableLogs[$log],
                    'level' => $_CONF['log_level']
                 )
        );
        Log::write($log,Log::INFO, sprintf("Log %s Initialized.", $availableLogs[$log]));
    } else {
        // old school...
        @unlink($_CONF['path_log'] . $availableLogs[$log]);
        $timestamp = strftime( "%c" );
        $fd = fopen( $_CONF['path_log'] . $availableLogs[$log], 'a' );
        fputs( $fd, "$timestamp - Log File Cleared \n" );
        fclose($fd);
    }
    $_POST['viewlog'] = 1;
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

// now build a list of registered logs
$logOption = '';

foreach($availableLogs AS $name => $file) {
    $logOption .= '<option value="'.$name.'"';
    if ($log == $name) {
        $logOption .= ' selected="selected"';
    }
    $logOption .= '>'.$name.'</option>';
}

if (!file_exists($_CONF['path_log'] . $availableLogs[$log])) {
    $buffer = 'No data has been logged at this time.';
} else {
    $fs = filesize ( $_CONF['path_log'] . $availableLogs[$log] );

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
        $fp = fopen($_CONF['path_log'] . $availableLogs[$log], 'r');
        fseek($fp, $seekPosition);
        while(!feof($fp)) {
            $buffer .= fread($fp, MAX_LOG_SIZE);
        }
    } else {
        $buffer = file_get_contents($_CONF['path_log'] . $availableLogs[$log]);
    }
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
