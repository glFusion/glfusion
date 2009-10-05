<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | rating.php                                                               |
// |                                                                          |
// | Forum Plugin Community Moderation Setting administration                 |
// +--------------------------------------------------------------------------+
// | $Id:: rating.php 4800 2009-08-08 22:00:48Z mevans0263                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Josh Pendergrass       cendent AT syndicate-gaming DOT com               |
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

require_once 'gf_functions.php';
require_once $_CONF['path'] . 'plugins/forum/include/gf_format.php';

if ( !$CONF_FORUM['enable_user_rating_system'] ) {
    echo COM_refresh($_CONF['site_url'].'/forum/index.php');
    exit;
}

USES_lib_admin();

$retval = '';

echo COM_siteHeader();
echo COM_startBlock($LANG_GF93['gfboard']);
echo glfNavbar($navbarMenu,$LANG_GF06['8']);

$menu_arr = array (
    array('url'  => $_CONF['site_admin_url'].'/plugins/forum/userrating.php',
          'text' => $LANG_GF98['user_ratings']),
    array('url' => $_CONF['site_admin_url'],
          'text' => $LANG_ADMIN['admin_home'])
);

$retval .= ADMIN_createMenu(
    $menu_arr,
    $LANG_GF98['forum_settings'],
    $_CONF['site_url'] . '/forum/images/forum.png'
);
echo $retval;

if(isset($_POST['save_changes'])) {
	//Save changes to forum requirements
	$res = DB_query("SELECT forum_id FROM {$_TABLES['gf_forums']}");
	while( $row = DB_fetchArray($res) ) {
		$rating_view = COM_applyFilter($_POST["viewRating-". $row['forum_id']],true);
		$rating_post = COM_applyFilter($_POST["postRating-". $row['forum_id']],true);
		DB_query("UPDATE {$_TABLES['gf_forums']} SET rating_view = '".intval($rating_view)."', rating_post = '".intval($rating_post)."' WHERE forum_id = ". $row['forum_id'] ."");
	}
}

$boards = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
$boards->set_file (array (
    'categories'    => 'rating_board_categories.thtml',
    'forums'        => 'rating_board_forums.thtml',
    'rating_bottom' => 'rating_bottom.thtml')
);
$boards->set_var ('self_url', $_CONF['site_admin_url'].'/plugins/forum/rating.php');

/* Display each Forum Category */
$asql = DB_query("SELECT * FROM {$_TABLES['gf_categories']} ORDER BY cat_order");
while ($A = DB_FetchArray($asql)) {
    $boards->set_var ('catid', $A['id']);
    $boards->set_var ('catname', $A['cat_name']);
    $boards->set_var ('order', $A['cat_order']);

    /* Display each forum within this category */
    $bsql = DB_query("SELECT * FROM {$_TABLES['gf_forums']} WHERE forum_cat={$A['id']} ORDER BY forum_order");
    $bnrows = DB_numRows($bsql);

    for ($j = 1; $j <= $bnrows; $j++) {
        $B = DB_FetchArray($bsql);
        $boards->set_var ('forumname', $B['forum_name']);
        $boards->set_var ('forumid', $B['forum_id']);
		$boards->set_var ('viewRating', $B['rating_view']);
		$boards->set_var ('postRating', $B['rating_post']);
        /* Check if this is a private forum */
        if ($B['grp_id'] != '2') {
            $grp_name = DB_getItem($_TABLES['groups'],'grp_name', "grp_id='{$B['grp_id']}'");
            $boards->set_var ('forumdscp', "[{$LANG_GF93['private']}&nbsp;-&nbsp;{$grp_name}]<br" . XHTML . ">{$B['forum_dscp']}");
        } else {
            $boards->set_var ('forumdscp', $B['forum_dscp']);
        }
        $boards->set_var ('forumorder', $B['forum_order']);
        if ($j == 1) {
            $boards->parse ('forum_records', 'forums');
        } else {
            $boards->parse ('forum_records', 'forums',true);
        }
    }

    if ($bnrows == 0) {
        $boards->set_var('hide_options','none');
        $boards->parse ('forum_records', '');
    }  else {
        $boards->set_var('hide_options','');
    }
    echo $boards->parse ('forum_listing_records', 'categories',true);
}
echo $boards->parse('output', 'rating_bottom');

echo COM_endBlock();
echo adminfooter();
echo COM_siteFooter();
?>