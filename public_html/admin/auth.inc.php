<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | auth.inc.php                                                             |
// |                                                                          |
// | glFusion admin authentication module                                     |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die('This file can not be used on its own.');
}

// MAIN
COM_clearSpeedlimit($_CONF['login_speedlimit'], 'login');
if (COM_checkSpeedlimit('login', $_CONF['login_attempts']) > 0) {
    $pageHandle->setPageTitle($LANG04[112]);
    $pageHandle->displayError('Access denied');
}

$uid = '';
$loginName = $inputHandler->getVar('strict','loginname','post','');
$passwd    = $inputHandler->getVar('strict','passwd','post','');
if (!empty($loginName) && !empty($passwd)) {
    if ($_CONF['user_login_method']['standard']) {
        $status = SEC_authenticate($loginName,$passwd,$uid);
    } else {
        $status = '';
    }
} else {
    $status = '';
}
$display = '';

if ($status == USER_ACCOUNT_ACTIVE) {
    DB_change($_TABLES['users'], 'pwrequestid', "NULL", 'uid', $uid);
    $_USER = SESS_getUserDataFromId($uid);
    $sessid = SESS_newSession($_USER['uid'], $_SERVER['REMOTE_ADDR'],
            $_CONF['session_cookie_timeout'], $_CONF['cookie_ip']);
    SESS_setSessionCookie($sessid, $_CONF['session_cookie_timeout'],
            $_CONF['cookie_session'], $_CONF['cookie_path'],
            $_CONF['cookiedomain'], $_CONF['cookiesecure']);
    PLG_loginUser($_USER['uid']);

    // Now that we handled session cookies, handle longterm cookie

    $sessionCookie = $inputHandler->getVar('strict',$_CONF['cookie_name'],'cookie','');

    if (empty($sessionCookie)) {

        // Either their cookie expired or they are new

        $cooktime = COM_getUserCookieTimeout();

        if (!empty($cooktime)) {

            // They want their cookie to persist for some amount of time so set it now

            setcookie($_CONF['cookie_name'], $_USER['uid'],
                      time() + $cooktime, $_CONF['cookie_path'],
                      $_CONF['cookiedomain'], $_CONF['cookiesecure']);
            DB_query("UPDATE {$_TABLES['users']} set remote_ip='".$_SERVER['REMOTE_ADDR']."' WHERE uid='".$_USER['uid']."'",1);
        }
    }
    if (!SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,syndication.edit','OR')) {
        $pageHandle->redirect($_CONF['site_admin_url'] . '/index.php');
    } else {
        $pageHandle->redirect($_CONF['site_url'] . '/index.php');
    }
    exit;
} else if (!SEC_isModerator() && !SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit','OR') && (count(PLG_getAdminOptions()) == 0)) {
    COM_updateSpeedlimit('login');

    $display .= COM_startBlock($LANG20[1]);

    if (!$_CONF['user_login_method']['standard']) {
        $display .= '<p>' . $LANG_LOGIN[2] . '</p>';
    } else {
        $warning = $inputHandler->getVar('strict','warn','post','');
        if (!empty($warning)) {
            $display .= $LANG20[2]
                     . '<br' . XHTML . '><br' . XHTML . '>'
                     . COM_accessLog($LANG20[3] . ' ' . htmlspecialchars($loginName));
        }

        $display .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">'
            .'<table cellspacing="0" cellpadding="0" border="0" width="100%">'.LB
            .'<tr><td align="right">'.$LANG20[4].'&nbsp;</td>'.LB
            .'<td><input type="text" name="loginname" size="16" maxlength="16"' . XHTML . '></td>'.LB
            .'</tr>'.LB
            .'<tr>'.LB
            .'<td align="right">'.$LANG20[5].'&nbsp;</td>'.LB
            .'<td><input type="password" name="passwd" size="16" maxlength="16"' . XHTML . '></td>'
            .'</tr>'.LB
            .'<tr>'.LB
            .'<td colspan="2" align="center" class="warning">'.$LANG20[6].'<input type="hidden" name="warn" value="1"' . XHTML . '>'
            .'<br' . XHTML . '><input type="submit" name="mode" value="'.$LANG20[7].'"' . XHTML . '></td>'.LB
            .'</tr>'.LB
            .'</table></form>';
    }

    $display .= COM_endBlock();
    $pageHandle->addContent($display);
    $pageHandle->displayPage();
    exit;
}

?>