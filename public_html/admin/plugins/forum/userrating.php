<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | userrating.php                                                           |
// |                                                                          |
// | Forum Plugin Community Moderation User administration                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
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

/**
 * used for the list of users in admin/user.php
 *
 */
function ADMIN_getListField_ratings($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG04, $LANG28, $_IMAGE_TYPE;

    $retval = '';

    switch ($fieldname) {
        case 'uid':
            $retval = COM_createLink($fieldvalue, $_CONF['site_admin_url']
                    . '/plugins/forum/userrating_detail.php?vid=' .  $A['uid']);
            break;
        case 'rating' :
            $retval = '<input type="text" name="new_rating-'.$A['uid'].'" value="'.intval($A['rating']).'" size="5" />';
            break;
        case 'username':
            $retval = COM_createLink($fieldvalue, $_CONF['site_admin_url']
                    . '/plugins/forum/userrating_detail.php?uid=' .  $A['uid']);
            break;
        case $_TABLES['users'] . '.uid':
            $retval = $A['uid'];
            break;
        default:
            $retval = $fieldvalue;
            break;
    }

    return $retval;
}

function _listUsers( )
{
    global $LANG28, $_CONF, $_TABLES, $LANG_ADMIN, $LANG_GF98;

    USES_lib_admin();

    $retval = '';

    $menu_arr = array (
        array('url'  => $_CONF['site_admin_url'].'/plugins/forum/rating.php',
              'text' => $LANG_GF98['board_ratings']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_GF98['user_ratings_desc'],
        $_CONF['site_url'] . '/forum/images/forum.png'
    );

    $header_arr = array(      # display 'text' and use table field 'field'
                    array('text' => $LANG_GF98['uid'], 'field' => 'uid', 'sort' => true),
                    array('text' => $LANG28[3], 'field' => 'username', 'sort' => true),
                    array('text' => $LANG28[4], 'field' => 'fullname', 'sort' => true),
                    array('text' => $LANG28[7], 'field' => 'email', 'sort' => true),
                    array('text' => $LANG_GF98['rating'],   'field' => 'rating', 'sort' => true)
    );

    $defsort_arr = array('field'     => $_TABLES['users'].'.uid',
                         'direction' => 'ASC');



    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . '/plugins/forum/userrating.php',
        'help_url'   => ''
    );

    $sql = "SELECT {$_TABLES['users']}.uid, username,fullname,email,status,rating FROM {$_TABLES['users']} LEFT JOIN {$_TABLES['gf_userinfo']} on {$_TABLES['users']}.uid={$_TABLES['gf_userinfo']}.uid";
    $query_arr = array('table' => 'users',
                       'sql' => $sql,
                       'query_fields' => array($_TABLES['users'].'.username', $_TABLES['users'].'.email', $_TABLES['users'].'.fullname'),
                       'default_filter' => " WHERE {$_TABLES['users']}.uid > 1");

    $form_arr = array('bottom' => '<div style="text-align:center;padding:5px;"><input type="submit" value="submit" name="submit" /></div>');

    $retval .= ADMIN_list('user', 'ADMIN_getListField_ratings', $header_arr,
                          $text_arr, $query_arr, $defsort_arr,'','','',$form_arr);

    return $retval;
}

/*
 * Need to update anything?
 */

if ( isset($_POST['submit']) && $_POST['submit'] == 'submit' ) {
	$res = DB_query("SELECT u.uid,rating FROM {$_TABLES['users']} AS u LEFT JOIN {$_TABLES['gf_userinfo']} AS gu ON u.uid=gu.uid");
	while($row = DB_fetchArray($res)) {
	    if ( isset($_POST["new_rating-{$row['uid']}"]) && $_POST["new_rating-{$row['uid']}"] != $row['rating'] ) {
		    $rating = intval(COM_applyFilter($_POST["new_rating-{$row['uid']}"],true));
    		DB_query("REPLACE INTO {$_TABLES['gf_userinfo']} (uid,rating) VALUES ({$row['uid']},$rating)");
    		DB_query("INSERT INTO {$_TABLES['gf_rating_assoc']} (user_id,voter_id,grade,topic_id) VALUES({$row['uid']},{$_USER['uid']},$rating,-1)");
    	}
	}
}

$display = '';

$display = COM_siteHeader();
$display .= COM_startBlock($LANG_GF98['user_rating_title'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));
$display .= glfNavbar($navbarMenu,$LANG_GF06['8']);
$display .= _listUsers( );
$display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
$display .= COM_siteFooter();

echo $display;
?>