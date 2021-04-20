<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Batch System Interface
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';

use \glFusion\Log\Log;

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

$display = '';

if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
    $display = MG_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-batch.php';
MG_initAlbums();

/**
* Main
*/

$mode = COM_applyFilter ($_REQUEST['mode']);

$display = '';

if ( isset ($_POST['cancel_button'] ) ) {
    $session_id = COM_applyFilter($_GET['sid']);
    // Pull the session status info
    $sql = "SELECT * FROM {$_TABLES['mg_sessions']} WHERE session_id='" . DB_escapeString($session_id) . "'";
    $result = DB_query($sql,1);
    if ( DB_error() ) {
        Log::write('system',Log::ERROR,"Media Gallery Error - Unable to retrieve batch session data");
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        exit;
    }
    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        $session = DB_fetchArray($result);
    } else {
        Log::write('system',Log::ERROR,"Media Gallery Error: Unable to find batch session id");
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        exit;       // no session found
    }
    echo COM_refresh($session['session_origin']);
    exit;
}

$display = MG_siteHeader();

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
            $item_limit = intval(COM_applyFilter($_POST['item_limit'],true));
        } else {
            if ( isset($_GET['limit']) ) {
                $item_limit = intval(COM_applyFilter($_GET['limit'],true));
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