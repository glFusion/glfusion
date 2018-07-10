<?php
// +--------------------------------------------------------------------------+
// | Spam-X Plugin - glFusion CMS                                             |
// +--------------------------------------------------------------------------+
// | Tester                                                                   |
// |                                                                          |
// | Test Spam Submission                                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2017-2018 by the following authors:                        |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

// Only let admin users access this page
if (!SEC_hasRights ('spamx.admin')) {
    COM_accessLog ("Someone has tried to access the Spam-X Tester page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: {$_SERVER['REMOTE_ADDR']}", 1);
    $display = COM_siteHeader ('menu', $LANG_SX00['access_denied']);
    $display .= COM_startBlock ($LANG_SX00['access_denied']);
    $display .= $LANG_SX00['access_denied_msg'];
    $display .= COM_endBlock ();
    $display .= COM_siteFooter (true);
    echo $display;
    exit;
}

/**
* Main
*/

if ( !defined('DVLP_DEBUG'))
    define('DVLP_DEBUG',true);

$page = '';
$response = '';

USES_lib_admin();

$menu_arr = array (
    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['plugin_name']),
    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/filters.php','text' => $LANG_SX00['filters']),
    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/tester.php','text' => $LANG_SX00['interactive_tester'],'active'=>true),
    array('url' => $_CONF['site_admin_url'] . '/index.php','text' => $LANG_ADMIN['admin_home']),
);

$username    = '';
$email       = '';
$ip          = '';
$useragent   = '';
$referer     = '';
$contenttype = '';
$content     = '';

if ( isset($_POST['submit'] ) ) {
    // pull the posted vars

    $username    = $_POST['username'];
    $email       = $_POST['email'];
    $ip          = $_POST['ipaddress'];
    $useragent   = $_POST['useragent'];
    $referer     = $_POST['referer'];
    $contenttype = $_POST['content_type'];
    $content     = $_POST['content'];

    $data['username'] = $username;
    $data['email']    = $email;
    $_SERVER['REAL_ADDR'] = $ip;
    $_SERVER['REMOTE_ADDR'] = $ip;
    $data['ip'] = $ip;
    $data['type'] = $contenttype;
    $_SERVER['HTTP_REFERER'] = $referer;
    $_SERVER['HTTP_USER_AGENT'] = $useragent;

    $response = print_r($data,true). LB;

    $spamx_path = $_CONF['path'] . 'plugins/spamx/modules/';

    // Set up Spamx_Examine array
    $Spamx_Examine = array ();
    if ($dir = @opendir ($spamx_path)) {
        while (($file = readdir ($dir)) !== false) {
            if (is_file ($spamx_path . $file)) {
                if (substr ($file, -18) == '.Examine.class.php') {
                    $sfile = str_replace ('.Examine.class.php', '', $file);
                    if ( $sfile != 'Formcheck') {
                        $Spamx_Examine[] = $sfile;
                    }
                }
            }
        }
        closedir ($dir);
    }

    $result = 0;
    foreach ($Spamx_Examine as $Examine) {
        $filename = $Examine . '.Examine.class.php';
        require_once ($spamx_path . $filename);
        $EX = new $Examine;
        $result = $EX->execute ($content, $data);
        $response .= LB . "Module: " . $filename . LB;
        $res = $EX->getResponse();
        $response .= print_r($res,true) . LB;
    }
}


$T = new Template($_CONF['path'].'plugins/spamx/templates');
$T->set_file('page','tester.thtml');

$T->set_var('admin_menu',
    ADMIN_createMenu($menu_arr, $LANG_SX00['instructions'],
                     $_CONF['site_admin_url'] . '/plugins/spamx/images/spamx.png'));

$T->set_var(array(
    'username'    => $username,
    'email'       => $email,
    'ip'          => $ip,
    'useragent'   => $useragent,
    'referer'     => $referer,
    'contenttype' => $contenttype,
    'content'     => $content,
));

$T->set_var('response',$response);

$T->parse( 'output', 'page' );
$page .= $T->finish( $T->get_var( 'output' ));

$display = COM_siteHeader ('menu', $LANG_SX00['plugin_name']);
$display .= COM_startBlock ($LANG_SX00['interactive_tester'], '',COM_getBlockTemplate ('_admin_block', 'header'));
$display .= $page;
    $display .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
$display .= COM_siteFooter();
echo $display;
?>