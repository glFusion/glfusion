<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | usersig.php                                                              |
// |                                                                          |
// | User signature maintenance                                               |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
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
require_once $_CONF['path'] . 'plugins/forum/include/gf_format.php';

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() || $CONF_FORUM['bbcode_signature'] == 0 ) {
    echo COM_refresh($_CONF['site_url'].'/forum/index.php');
    exit;
}

USES_lib_bbcode();

function GF_sigEditor( $signature = '', $errors = array() )
{
    global $_CONF, $CONF_FORUM, $_TABLES, $_USER;

    $retval = '';

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $T->set_file (array (
        'signature'    =>  'signature.thtml',
    ));

    $T->set_var(array(
        'signature'   => $signature,
    ));
    $error_message = '';
    if ( count($errors) > 0 ) {
        foreach($errors AS $error) {
            $error_message .= $error .'<br />';
        }
    }
    $T->set_var('error_message',$error_message);

    $T->set_var('gltoken', SEC_createToken());
    $T->set_var('gltoken_name', CSRF_TOKEN);

    $T->parse ('output', 'signature');
    $retval .= $T->finish ($T->get_var('output'));

    return $retval;
}

function GF_previewSig( $signature = '' )
{
    global $_CONF, $CONF_FORUM, $_USER, $_TABLES;

    $retval = '';

    $signature = COM_stripslashes($_POST['signature']);

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $T->set_file (array ('message'=>'signature.thtml'));

    $T->set_var(array(
        'preview_sig' => BBC_formatTextBlock($signature,'text'),
        'signature'   => $signature,
    ));

    $T->set_var('gltoken', SEC_createToken());
    $T->set_var('gltoken_name', CSRF_TOKEN);

    $T->parse ('output', 'message');
    $retval .= $T->finish ($T->get_var('output'));
    return $retval;
}

function GF_saveSig()
{
    global $_CONF, $CONF_FORUM, $_USER, $_TABLES;

    $retval = '';

    if ( !SEC_checkToken() ) {
        $errArray[] = 'Security Token Failure';
        return array(false,$errArray);
    }
    $signature = COM_stripslashes($_POST['signature']);

    // see if user already has a preference record...
    // Get user specific settings from database
    $result = DB_query("SELECT * FROM {$_TABLES['gf_userinfo']} WHERE uid={$_USER['uid']}");
    $nrows = DB_numRows($result);
    if ($nrows == 0) {
        // Insert a new blank record. Defaults are set in SQL Defintion for table.
        DB_query("INSERT INTO {$_TABLES['gf_userinfo']} (uid) VALUES ('{$_USER['uid']}')");
    }
    $sql = "UPDATE {$_TABLES['gf_userinfo']} SET signature='".addslashes($signature)."' WHERE uid=".$_USER['uid'];
    DB_query($sql);
    return array(true,'');
}

if ( isset($_POST['submit']) ) {
    $mode = 'submit';
} elseif (isset($_POST['preview']) ) {
    $mode = 'preview';
} elseif (isset($_POST['cancel']) ) {
    $mode = 'cancel';
} else {
    $mode = 'edit';
}

$message = '';
$body = '';

switch ( $mode ) {
    case 'edit' :
        $signature = DB_getItem($_TABLES['gf_userinfo'],'signature','uid='.$_USER['uid']);
        $body = GF_sigEditor($signature);
        break;
    case 'preview' :
        $body = GF_previewSig();
        break;
    case 'submit' :
        list($rc,$errors) = GF_saveSig( );
        if ( !$rc ) {
            $signature = COM_stripslashes($_POST['signature']);
            $body      = GF_sigEditor($signature,$errors);
        } else {
            echo COM_refresh($_CONF['site_url'].'/forum/index.php?msg=1');
            exit;
        }
        break;
    case 'cancel' :
        echo COM_refresh($_CONF['site_url'].'/forum/index.php');
        exit;
        break;
}

gf_siteHeader();
if ($CONF_FORUM['usermenu'] == 'navbar') {
    echo forumNavbarMenu($LANG_GF01['signature']);
}

echo $body;
gf_siteFooter();
?>