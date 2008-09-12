<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | Restores all Media Gallery configuration options install defaults         |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2005-2008 by the following authors:                         |
// |                                                                           |
// | Mark R. Evans              - mark@gllabs.org                              |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

require_once '../../../lib-common.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
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
        COM_errorLog("Media Gallery: Restoring system defaults",1);
        for ($i = 1; $i <= count($_SQL_DEF); $i++) {
            DB_query(current($_SQL_DEF));
            if (DB_error()) {
                COM_errorLog("Error inserting Media Gallery Defaults: " . curent($_SQL_DEF),1);
            }
            next($_SQL_DEF);
        }
        COM_errorLog("Media Gallery: Success - default data added to Media Gallery tables",1);
        echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=9');
        exit;
    default :
        $display = COM_siteHeader();
        $T = new Template($_MG_CONF['template_path']);
        $T->set_file (array ('admin' => 'administration.thtml'));
        $T->set_var('xhtml',XHTML);

        $B = new Template($_MG_CONF['template_path']);
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
            'xhtml'                 =>  XHTML,
        ));
        $B->parse('output', 'admin');

        $T->set_var(array(
            'site_admin_url'    => $_CONF['site_admin_url'],
            'site_url'          => $_MG_CONF['site_url'],
            'admin_body'        => $B->finish($B->get_var('output')),
            'mg_navigation'     => MG_navigation(),
            'title'             => $LANG_MG01['reset_defaults'],
            'lang_admin'        => $LANG_MG00['admin'],
            'version'           => $_MG_CONF['version'],
            'lang_help'         => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"' . XHTML . '>',
            'help_url'          => $_MG_CONF['site_url'] . '/docs/usage.html#Reset_System_Options',


        ));
        $T->parse('output', 'admin');
        $display .= $T->finish($T->get_var('output'));
        $display .= COM_siteFooter();
        echo $display;
        exit;
        break;
}
?>