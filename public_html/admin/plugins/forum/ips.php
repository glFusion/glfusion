<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | ips.php                                                                  |
// |                                                                          |
// | Program to administrate IP access/restriction to forum                   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

if (!SEC_hasRights('forum.edit')) {
  $display = COM_siteHeader();
  $display .= COM_startBlock($LANG_GF00['access_denied']);
  $display .= $LANG_GF00['admin_only'];
  $display .= COM_endBlock();
  $display .= COM_siteFooter(true);
  echo $display;
  exit();
}

USES_forum_functions();
USES_forum_format();
USES_forum_admin();

$ip     = isset($_REQUEST['ip']) ? COM_applyFilter($_REQUEST['ip']) : '';
$forum  = isset($_REQUEST['forum']) ? COM_applyFilter($_REQUEST['forum'],true) : 0;
$op     = isset($_REQUEST['op']) ? COM_applyFilter($_REQUEST['op']) : '';

$dipslay  = FF_siteHeader();
$display .= FF_Navbar($navbarMenu,$LANG_GF06['7']);
$display .=  COM_startBlock($LANG_GF96['gfipman']);

if (($op == 'banip') && ($ip != '')) {
    if($_POST['sure'] == 'yes') {
        DB_query("INSERT INTO {$_TABLES['ff_banned_ip']} (host_ip) VALUES ('".DB_escapeString($ip)."')");
        $display .= FF_statusMessage($LANG_GF96['ipbanned'],$_CONF['site_admin_url'] .'/plugins/forum/ips.php',$LANG_GF96['ipbanned']);
        $display .= COM_endBlock();
        $display .= FF_adminfooter();
        $display .= FF_siteFooter();
        echo $display;
        exit;
    }

    if ($_POST['sure'] != 'yes') {
        $ips_unban = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
        $ips_unban->set_file ('ips_unban','ips_unban.thtml');
        $ips_unban->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/ips.php');
        $ips_unban->set_var ('deletenote1', sprintf($LANG_GF93['deleteforumnote1'], $forumname));
        $ips_unban->set_var ('deletenote2', $LANG_GF93['deleteforumnote21']);
        $ips_unban->set_var ('mode', 'banip');
        $ips_unban->set_var ('sure', 'yes');
        $ips_unban->set_var ('ip', $ip);
        $ips_unban->set_var ('msg1', $LANG_GF96['banip']);
        $ips_unban->set_var ('msg2', sprintf($LANG_GF96['banipmsg'], $ip));
        $ips_unban->set_var ('ban', $LANG_GF96['ban']);
        $ips_unban->parse ('output', 'ips_unban');
        $display .= $ips_unban->finish ($ips_unban->get_var('output'));
        $display .= COM_endBlock();
        $display .= FF_adminfooter();
        $display .= FF_siteFooter();
        echo $display;
        exit;
    }

} elseif (($op == 'banip') && ($ip == '')) {
    $messagetemplate = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $messagetemplate->set_file (array ('messagetemplate'=>'message.thtml'));
    $messagetemplate->set_var ('message', $LANG_GF01['ERROR']);
    $messagetemplate->set_var ('transfer', $LANG_GF96['specip']);
    $messagetemplate->parse ('output', 'messagetemplate');
    $display .= $messagetemplate->finish ($messagetemplate->get_var('output'));
    $display .= COM_endBlock();
    $display .= FF_adminfooter();
    $display .= FF_siteFooter();
    echo $display;
    exit();
}

if (($op == 'unban') && ($ip != '')) {
    DB_query ("DELETE FROM {$_TABLES['ff_banned_ip']} WHERE (host_ip='".DB_escapeString($ip)."')");
    $display .= FF_statusMessage($LANG_GF96['ipunbanned'],$_CONF['site_admin_url'] .'/plugins/forum/ips.php',$LANG_GF96['ipunbanned']);
    $display .= COM_endBlock();
    $display .= FF_adminfooter();
    $display .= FF_siteFooter();
    echo $display;
    exit;
}


if (!empty($forum)) {
    $theforum = "WHERE forum=".(int) $forum;
} else {
    $theforum = '';
}


if ($op == '') {
    $bannedsql = DB_query("SELECT * FROM {$_TABLES['ff_banned_ip']} ORDER BY host_ip DESC");
    $bannum = DB_numRows($bannedsql);
    $p = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $p->set_file (array ('page' => 'banip_mgmt.thtml', 'records' => 'ip_records.thtml'));
    if ($bannum == 0) {
        $p->set_var ('alertmessage', $LANG_GF96['noips']);
        $p->set_var ('showalert','');
    } else {
        $p->set_var ('showalert','none');
    }
    $p->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/ips.php');
    $p->set_var ('LANG_IP',$LANG_GF96['ipbanned']);
    $p->set_var ('LANG_Actions', $LANG_GF01['ACTIONS']);
    $i = 1;
    while($A = DB_fetchArray($bannedsql)) {
        $p->set_var ('ip', $A['host_ip']);
        $p->set_var ('unban', $LANG_GF96['unban']);
        $p->set_var ('csscode', $i);
        $p->parse ('ip_records', 'records',true);
        $i = ($i == 1 ) ? 2 : 1;
    }
    $p->parse ('output', 'page');
    $display .= $p->finish ($p->get_var('output'));
}

$display .= COM_endBlock();
$display .= FF_adminfooter();
$display .= FF_siteFooter();
echo $display;
?>