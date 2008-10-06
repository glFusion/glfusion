<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | pgp.php                                                                  |
// |                                                                          |
// | Display a user's pgp key.                                                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
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
//

require_once 'lib-common.php';

$uid = COM_applyFilter ($_GET['uid'], true);
if (is_numeric ($uid) && ($uid > 1)) {
    $result = DB_query ("SELECT {$_TABLES['users']}.uid,username,fullname,regdate,lastlogin,homepage,about,location,pgpkey,photo,email,status,showonline FROM {$_TABLES['userinfo']},{$_TABLES['userprefs']},{$_TABLES['users']} WHERE {$_TABLES['userinfo']}.uid = {$_TABLES['users']}.uid AND {$_TABLES['userinfo']}.uid = {$_TABLES['userprefs']}.uid AND {$_TABLES['users']}.uid = $uid");
    $nrows = DB_numRows ($result);
    if ($nrows == 0) { // no such user
        die("Invalid request");
    }
    $A = DB_fetchArray ($result);

    if ($A['status'] == USER_ACCOUNT_DISABLED && !SEC_hasRights ('user.edit')) {
        die("Invalid Request");
    }
    $display = '<pre>'.$A['pgpkey'].'</pre>';
    if( empty( $LANG_CHARSET )) {
        $charset = $_CONF['default_charset'];
        if( empty( $charset )) {
            $charset = 'iso-8859-1';
        }
    } else {
        $charset = $LANG_CHARSET;
    }
    header ('Content-Type: text/html; charset=' . $charset);
    echo $display;
    exit;
}
?>