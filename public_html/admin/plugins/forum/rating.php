<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | rating.php                                                               |
// |                                                                          |
// | Forum Plugin Community Moderation Setting administration                 |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2011 by the following authors:                        |
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

if ( !$_FF_CONF['enable_user_rating_system'] ) {
    echo COM_refresh($_CONF['site_url'].'/forum/index.php');
    exit;
}

USES_forum_functions();
USES_forum_format();
USES_forum_admin();
USES_lib_admin();

$retval = '';

$display  = FF_siteHeader();
$navbarMenu = array_merge($navbarMenu,array($LANG_GF98['user_ratings'] => $_CONF['site_admin_url'].'/plugins/forum/userrating.php'));
$display .= FF_Navbar($navbarMenu,$LANG_GF06['8']);
$display .=  COM_startBlock($LANG_GF93['gfboard']);

if(isset($_POST['save_changes'])) {
	//Save changes to forum requirements
	$res = DB_query("SELECT forum_id FROM {$_TABLES['ff_forums']}");
	while( $row = DB_fetchArray($res) ) {
		$rating_view = COM_applyFilter($_POST["viewRating-". $row['forum_id']],true);
		$rating_post = COM_applyFilter($_POST["postRating-". $row['forum_id']],true);
		DB_query("UPDATE {$_TABLES['ff_forums']} SET rating_view = '".(int) $rating_view."', rating_post = '".(int) $rating_post."' WHERE forum_id = ". (int) $row['forum_id'] ."");
	}
}

$boards = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
$boards->set_file('rating','rating.thtml');

$boards->set_var ('self_url', $_CONF['site_admin_url'].'/plugins/forum/rating.php');

/* Display each Forum Category */
$asql = DB_query("SELECT * FROM {$_TABLES['ff_categories']} ORDER BY cat_order");

while ($A = DB_FetchArray($asql)) {
    $boards->set_var ('catid', $A['id']);
    $boards->set_var ('catname', $A['cat_name']);
    $boards->set_var ('order', $A['cat_order']);

    /* Display each forum within this category */
    $bsql = DB_query("SELECT * FROM {$_TABLES['ff_forums']} WHERE forum_cat=".(int) $A['id']." ORDER BY forum_order");
    $bnrows = DB_numRows($bsql);

    $boards->set_block('rating', 'catrows', 'crow');
    $boards->clear_var('frow');
    $boards->set_block('rating', 'forumrows', 'frow');

    for ($j = 1; $j <= $bnrows; $j++) {
        $B = DB_fetchArray($bsql);
        $boards->set_var (array(
                'forumname' => $B['forum_name'],
                'forumid'   => $B['forum_id'],
		        'viewRating'=> $B['rating_view'],
		        'postRating'=> $B['rating_post']));

        /* Check if this is a private forum */
        if ($B['grp_id'] != '2') {
            $grp_name = DB_getItem($_TABLES['groups'],'grp_name', "grp_id=".(int) $B['grp_id']);
            $boards->set_var ('forumdscp', "[{$LANG_GF93['private']}&nbsp;-&nbsp;{$grp_name}]<br" . XHTML . ">{$B['forum_dscp']}");
        } else {
            $boards->set_var ('forumdscp', $B['forum_dscp']);
        }
        $boards->set_var ('forumorder', $B['forum_order']);
        $boards->parse('frow', 'forumrows',true);
    }
    $boards->parse('crow', 'catrows',true);
}
$boards->parse ('output', 'rating');
$display .= $boards->finish ($boards->get_var('output'));

$display .= COM_endBlock();
$display .= FF_adminfooter();
$display .= FF_siteFooter();
echo $display;
?>