<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | refresh.php                                                              |
// |                                                                          |
// | Refresh session                                                          |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2015-2016 by the following authors:                        |
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

if ( !isset($_POST['keepalive']) || !isset($_POST['token']) )  die();
$token = COM_applyFilter($_POST['token']);
switch ( $_POST['keepalive'] ) {
    case 'storyeditor' :
        if (!SEC_hasRights('story.edit')) {
            die();
        }
        break;
    case 'speditor' :
        if (!SEC_hasRights ('staticpages.edit')) {
            die();
        }
        break;
    case 'forumeditor' :
    case 'commenteditor' :
        $sql = "SELECT * FROM {$_TABLES['tokens']} WHERE token='".DB_escapeString($token)."'";
        $result = DB_query($sql);
        if ( DB_numRows($result) != 1 ) die();
        $sql = "UPDATE {$_TABLES['tokens']} SET created=NOW() WHERE token='".DB_escapeString($token)."'";
        DB_query($sql);
        exit;
    default :
        die();
}

if ( !isset($_COOKIE[$_CONF['cookie_name'].'adveditor'])) die();

if ( !isset($_COOKIE['token'])) die();

$sql = "SELECT * FROM {$_TABLES['tokens']} WHERE token='".DB_escapeString($token)."'";
$result = DB_query($sql);
if ( DB_numRows($result) != 1 ) die();

$advtoken = COM_applyFilter($_COOKIE[$_CONF['cookie_name'].'adveditor']);
$sql = "SELECT * FROM {$_TABLES['tokens']} WHERE token='".DB_escapeString($advtoken)."'";
$result = DB_query($sql);
if ( DB_numRows($result) != 1 ) die();

$admtoken = COM_applyFilter($_COOKIE['token']);
$sql = "SELECT * FROM {$_TABLES['tokens']} WHERE token='".DB_escapeString($admtoken)."'";
$result = DB_query($sql);
if ( DB_numRows($result) != 1 ) die();

// refresh tokens

$sql = "UPDATE {$_TABLES['tokens']} SET created=NOW() WHERE token='".DB_escapeString($token)."'";
DB_query($sql);

$sql = "UPDATE {$_TABLES['tokens']} SET created=NOW() WHERE token='".DB_escapeString($advtoken)."'";
DB_query($sql);

SEC_setCookie ($_CONF['cookie_name'].'adveditor', $advtoken,
               time() + 1200, $_CONF['cookie_path'],
               $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);

$sql = "UPDATE {$_TABLES['tokens']} SET created=NOW() WHERE token='".DB_escapeString($admtoken)."'";
DB_query($sql);
exit;
?>