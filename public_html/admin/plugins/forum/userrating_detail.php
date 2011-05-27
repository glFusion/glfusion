<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | userrating_detail.php                                                    |
// |                                                                          |
// | Forum Plugin Community Moderation User administration                    |
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

USES_forum_functions();
USES_forum_format();
USES_forum_admin();

/**
 * used for the list of users in admin/user.php
 *
 */
function ADMIN_getListField_ratings($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG04, $LANG28, $LANG_GF98,$_FF_CONF;

    $retval = '';

    switch ($fieldname) {
        case 'grade' :
            $retval = intval($fieldvalue);
            break;
        case 'rating' :
            $retval = '<input type="text" name="new_rating-'.$A['uid'].'" value="'.intval($A['rating']).'" size="5" />';
            break;
        case 'voter_id' :
            $uname = DB_getItem($_TABLES['users'],'username','uid='.$A['voter_id']);
            $retval = COM_createLink($uname, $_CONF['site_admin_url']
                    . '/plugins/forum/userrating_detail.php?vid=' .  $A['voter_id']);
            break;
        case 'user_id' :
            $uname = DB_getItem($_TABLES['users'],'username','uid='.$A['user_id']);
            if ( $uname == '' ) {
                $retval = COM_createLink($A['user_id'], $_CONF['site_admin_url']
                        . '/plugins/forum/userrating_detail.php?uid=' .  $A['user_id']);
            } else {
                $retval = COM_createLink($uname, $_CONF['site_admin_url']
                        . '/plugins/forum/userrating_detail.php?uid=' .  $A['user_id']);
            }
            break;
        case 'topic_id' :
            if ( intval($A['topic_id']) > 0 ) {
                $res = DB_query("SELECT id,pid,forum,subject,comment,status FROM {$_TABLES['ff_topic']} WHERE id=".$A['topic_id']);
                list($id,$pid,$forum,$subject,$comment,$status) = DB_fetchArray($res);
                $testText        = FF_formatTextBlock($comment,'text','text',$status);
                $testText        = strip_tags($testText);
                $lastpostinfogll = htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$_FF_CONF['contentinfo_numchars']). '...')),ENT_QUOTES,COM_getEncodingt());
                if ( $subject == '' ) {
                    $subject = '<em>'.$LANG_GF98['no_subject_defined'].'</em>';
                }
                $retval = '<a class="gf_mootip" style="text-decoration:none;" href="' . $_CONF['site_url'] . '/forum/viewtopic.php?showtopic=' . ($pid == 0 ? $id : $pid) . '&amp;topic='.$id.'#'.$id.'" title="' . $subject . '::' . $lastpostinfogll . '" rel="nofollow">' . $subject . '</a>';
            } elseif ($A['topic_id'] == -1 ) {
                $retval = $LANG_GF98['admin_set_value'];
            } else {
                $retval = $LANG_GF98['no_topic_defined'];
            }
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

function _listUserDetail( $uid )
{
    global $LANG28, $_CONF, $_TABLES,$LANG_ADMIN, $LANG_GF98;

    USES_lib_admin();

    $retval = '';

    $header_arr = array(      # display 'text' and use table field 'field'
                    array('text' => $LANG_GF98['voter'], 'field' => 'voter_id', 'sort' => false),
                    array('text' => $LANG_GF98['grade'], 'field' => 'grade', 'sort' => false),
                    array('text' => $LANG_GF98['topic'], 'field' => 'topic_id', 'sort' => false)
    );

    $defsort_arr = array('field'     => 'user_id',
                         'direction' => 'ASC');

    $text_arr = array(
        'has_extras' => false,
        'form_url'   => $_CONF['site_admin_url'] . '/plugins/forum/userrating_detail.php',
        'help_url'   => ''
    );

    $sql = "SELECT * FROM {$_TABLES['ff_rating_assoc']} WHERE user_id=".$uid;

    $query_arr = array('table' => 'ff_rating_assoc',
                       'sql' => $sql,
                       'query_fields' => array('uid'),
                       'default_filter' => " WHERE user_id = ".$uid);

    $retval .= ADMIN_list('user', 'ADMIN_getListField_ratings', $header_arr,
                          $text_arr, $query_arr, $defsort_arr);


    return $retval;
}

function _listUserVotes( $uid )
{
    global $LANG28, $_CONF, $_TABLES, $LANG_ADMIN, $LANG_GF98;

    USES_lib_admin();

    $retval = '';

    $header_arr = array(
                    array('text' => 'User Rated', 'field' => 'user_id', 'sort' => false),
                    array('text' => 'Grade', 'field' => 'grade', 'sort' => false),
                    array('text' => 'Topic',   'field' => 'topic_id', 'sort' => false)
    );

    $defsort_arr = array('field'     => 'user_id',
                         'direction' => 'ASC');

    $text_arr = array(
        'has_extras' => false,
        'form_url'   => $_CONF['site_admin_url'] . '/plugins/forum/userrating_detail.php',
        'help_url'   => ''
    );

    $sql = "SELECT * FROM {$_TABLES['ff_rating_assoc']} WHERE voter_id = ".$uid;

    $query_arr = array('table' => 'ff_rating_assoc',
                       'sql' => $sql,
                       'query_fields' => array('uid'),
                       'default_filter' => " WHERE voter_id = ".$uid);

    $retval .= ADMIN_list('user', 'ADMIN_getListField_ratings', $header_arr,
                          $text_arr, $query_arr, $defsort_arr);

    return $retval;
}

$display = '';

$display = COM_siteHeader();

$navbarMenu = array_merge($navbarMenu,array($LANG_GF98['board_ratings']  => $_CONF['site_admin_url'].'/plugins/forum/rating.php'));
$navbarMenu = array_merge($navbarMenu,array($LANG_GF98['user_ratings']  => $_CONF['site_admin_url'].'/plugins/forum/userrating.php'));

if ( isset($_GET['uid']) ) {
    $display .= FF_Navbar($navbarMenu,$LANG_GF06['8']);
    $display .= COM_startBlock($LANG_GF98['user_rating_details'].DB_getItem($_TABLES['users'],'username','uid='.intval($_GET['uid'])), '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $display .= _listUserDetail( intval($_GET['uid']) );
} elseif ( isset($_GET['vid']) ) {
    $display .= FF_Navbar($navbarMenu,$LANG_GF06['8']);
    $display .= COM_startBlock($LANG_GF98['user_voting_details'].DB_getItem($_TABLES['users'],'username','uid='.intval($_GET['vid'])), '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $display .= _listUserVotes( intval($_GET['vid']) );
}
$display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
$display .= COM_siteFooter();

echo $display;
?>