<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
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

require_once('../lib-common.php');
require_once($_MG_CONF['path_html'] . 'lib-batch.php');

if (!function_exists('MG_usage')) {
    // The plugin is disabled
    $display = COM_siteHeader();
    $display .= COM_startBlock('Plugin disabled');
    $display .= '<br />The Media Gallery plugin is currently disabled.';
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

$display = '';

if (!isset($_USER['uid']) || $_USER['uid'] < 2 ) {
    $display = MG_siteHeader();
    $display .= COM_startBlock ($LANG_LOGIN[1], '',
              COM_getBlockTemplate ('_msg_block', 'header'));
    $login = new Template($_CONF['path_layout'] . 'submit');
    $login->set_file (array ('login'=>'submitloginrequired.thtml'));
    $login->set_var ('login_message', $LANG_LOGIN[2]);
    $login->set_var ('site_url', $_CONF['site_url']);
    $login->set_var ('lang_login', $LANG_LOGIN[3]);
    $login->set_var ('lang_newuser', $LANG_LOGIN[4]);
    $login->parse ('output', 'login');
    $display .= $login->finish ($login->get_var('output'));
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

/**
* Main
*/

$mode = COM_applyFilter ($_REQUEST['mode']);

$display = '';
$display = MG_siteHeader();

if ( isset ($_POST['cancel_button'] ) ) {
    $session_id = COM_applyFilter($_GET['sid']);
    // Pull the session status info
    $sql = "SELECT * FROM {$_TABLES['mg_sessions']} WHERE session_id='" . $session_id . "'";
    $result = DB_query($sql,1);
    if ( DB_error() ) {
        COM_errorLog("Media Gallery Error - Unable to retrieve batch session data");
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        exit;
    }
    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        $session = DB_fetchArray($result);
    } else {
        COM_errorLog("Media Gallery Error: Unable to find batch session id");
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        exit;       // no session found
    }
    echo COM_refresh($session['session_origin']);
    exit;
}

if (($mode == 'continue') ) {
    if ( isset($_GET['sid']) ) {
        $sid = COM_applyFilter($_GET['sid']);
        if ( isset($_POST['refresh_rate']) ) {
            $refresh_rate = COM_applyFilter($_POST['refresh_rate'],true);
        } else {
            if ( isset($_GET['refresh']) ) {
                $refresh_rate = COM_applyFilter($_GET['refresh'],true);
            } else {
                $refresh_rate = $_MG_CONF['def_refresh_rate'];
            }
        }
        if ( isset($_POST['item_limit']) ) {
            $item_limit = COM_applyFilter($_POST['item_limit'],true);
        } else {
            if ( isset($_GET['limit']) ) {
                $item_limit = COM_applyFilter($_GET['limit'],true);
            } else {
                $item_limit = $_MG_CONF['def_item_limit'];
            }
        }
        $display .= MG_continueSession( $sid, $item_limit, $refresh_rate );
    }
    $display .= MG_siteFooter();
    echo $display;
    exit;
}
echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
?>