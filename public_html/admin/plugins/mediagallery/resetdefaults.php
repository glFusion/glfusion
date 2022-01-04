<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Restores all configuration options to install defaults
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

use \glFusion\Log\Log;

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    Log::write('system',Log::WARNING,"Someone has tried to access the Media Gallery Configuration page.  User id: ".$_USER['uid']);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

$step = isset($_REQUEST['step']) ? COM_applyFilter($_REQUEST['step']) : '';
$mode = isset($_POST['submit']) ? COM_applyFilter($_POST['submit']) : '';

if ( $mode == $LANG_MG01['cancel'] ) {
    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php');
    exit;
}

switch ($step) {
    case 'two' :
        $ftp_path = $_CONF['path'] . 'plugins/mediagallery/uploads/';
        $tmp_path = $_CONF['path'] . 'plugins/mediagallery/tmp/';

        require_once $_CONF['path'] . 'plugins/mediagallery/sql/sql_defaults.php';
        // Insert default configuration
        Log::write('system',Log::INFO,"Media Gallery: Restoring system defaults");
        for ($i = 1; $i <= count($_SQL_DEF); $i++) {
            DB_query(current($_SQL_DEF));
            if (DB_error()) {
                Log::write('system',Log::ERROR,"Media Gallery: Error inserting Media Gallery Defaults: " . curent($_SQL_DEF));
            }
            next($_SQL_DEF);
        }
        Log::write('system',Log::INFO,"Media Gallery: Success - default data added to Media Gallery tables");
        echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=9');
        exit;
    default :
        $T = new Template($_MG_CONF['template_path'].'/admin');
        $T->set_file (array ('admin' => 'administration.thtml'));

        $B = new Template($_MG_CONF['template_path'].'/admin');
        $B->set_file (array ('admin' => 'thumbs.thtml'));
        $B->set_var('site_url', $_CONF['site_url']);
        $B->set_var('site_admin_url', $_CONF['site_admin_url']);
        // display the album list...
        $B->set_var(array(
            'lang_title'            =>  $LANG_MG01['reset_defaults'],
            's_form_action'         =>  $_MG_CONF['admin_url'] . 'resetdefaults.php?step=two',
            'lang_next'             =>  $LANG_MG01['next'],
            'lang_cancel'           =>  $LANG_MG01['cancel'],
            'lang_details'          =>  $LANG_MG01['reset_defaults_details'],
        ));
        $B->parse('output', 'admin');

        $T->set_var(array(
            'site_admin_url'    => $_CONF['site_admin_url'],
            'site_url'          => $_MG_CONF['site_url'],
            'admin_body'        => $B->finish($B->get_var('output')),
            'mg_navigation'     => MG_navigation(),
            'title'             => $LANG_MG01['reset_defaults'],
            'lang_admin'        => $LANG_MG00['admin'],
            'version'           => $_MG_CONF['pi_version'],
            'lang_help'         => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
            'help_url'          => $_MG_CONF['site_url'] . '/docs/usage.html#Reset_System_Options',


        ));
        $T->parse('output', 'admin');
        $display = COM_siteHeader();
        $display .= $T->finish($T->get_var('output'));
        $display .= COM_siteFooter();
        echo $display;
        exit;
        break;
}
?>