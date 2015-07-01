<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | batch.php                                                                |
// |                                                                          |
// | Batch system interface                                                   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2015 by the following authors:                        |
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

require_once '../lib-common.php';

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