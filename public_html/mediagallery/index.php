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
require_once($_MG_CONF['path_html'] . 'classFrame.php');
require_once($_MG_CONF['path_html'] . 'mgindex.php');

if (!function_exists('MG_usage')) {
    // The plugin is disabled
    $display = COM_siteHeader();
    $display .= COM_startBlock('Plugin disabled');
    $display .= '<br' . XHTML . '><br' . XHTML . '>The Media Gallery plugin is currently disabled.';
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $_MG_CONF['loginrequired'] == 1 )  {
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

MG_index();
?>