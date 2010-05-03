<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// | This glFusion log file viewer.                                           |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2003-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tom Willett - twillett AT users DOT sourceforge DOT net         |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

// Path to this file
$path = $_MG_CONF['admin_url'];

// Only let mg admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the LogView page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display = COM_siteHeader();
    $display .= COM_startBlock("Access Denied!!!");
    $display .= "You are illegally trying to access the File LogView page.  This attempt has been logged";
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}
$log = isset($_POST['log']) ? COM_applyFilter($_POST['log']) : '';
/*
* Main Function
*/

$retval = '';

$display = COM_siteHeader();
$T = new Template($_MG_CONF['template_path']);
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['version'],
));

$retval .= "<br /><p>Views/Clear the glFusion Log Files.<p>";
$retval .= "<form method=\"post\" action=\"{$path}logview.php\">";
$retval .= "File:&nbsp;&nbsp;&nbsp;";
$files = array();
if ($dir = @opendir($_CONF['path_log'])) {
    while(($file = readdir($dir)) !== false) {
        if (is_file($_CONF['path_log'] . $file)) { array_push($files,$file); }
    }
    closedir($dir);
}
$retval .= '<SELECT name="log">';
if (empty($log)) { $log = $files[0]; } // default file to show
for ($i = 0; $i < count($files); $i++) {
    $retval .= '<option value="' . $files[$i] . '"';
    if ($log == $files[$i]) { $retval .= ' SELECTED'; }
    $retval .= '>' . $files[$i] . '</option>';
    next($files);
}
$retval .= "</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;";
$retval .= "<input type=\"submit\" name=\"action\" value=\"View Log File\">";
$retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
$retval .= "<input type=\"submit\" name=\"action\" value=\"Clear Log File\">";
$retval .= "</form>";

$action = isset($_POST['action']) ? COM_applyFilter($_POST['action']) : '';

if ($action == 'Clear Log File') {
    unlink($_CONF['path_log'] . $_POST['log']);
    $timestamp = strftime( "%c" );
    $fd = fopen( $_CONF['path_log'] . $_POST['log'], a );
    fputs( $fd, "$timestamp - Log File Cleared \n" );
    fclose($fd);
    $action = 'View Log File';
}
if ($action == 'View Log File') {
    $retval .= "<hr><p><b>Log File: " . $_POST['log'] . "</b></p><pre>";
    $retval .= implode('', file($_CONF['path_log'] . $_POST['log']));
    $retval .= "</pre>";
}

$T->set_var(array(
    'admin_body'    => $retval,
    'mg_navigation' => MG_navigation(),
    'title'         => $LANG_MG01['log_viewer'],
));

$T->parse('output', 'admin');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>