<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | ajaxrate.php                                                             |
// |                                                                          |
// | AJAX code to update community moderation value                           |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2017 by the following authors:                        |
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
if (!in_array('forum', $_PLUGINS)) {
    exit;
}

USES_forum_functions();
USES_forum_format();

if ( !$_FF_FORUM['enable_user_rating_system'] ) {
    die;
}

$vid  = isset($_GET['vid']) ? COM_applyFilter($_GET['vid'],true) : 0;
$pid  = isset($_GET['pid']) ? COM_applyFilter($_GET['pid'],true) : 0;
$tid  = isset($_GET['tid']) ? COM_applyFilter($_GET['tid'],true) : 0;
$vote = isset($_GET['vote']) ? COM_applyFilter($_GET['vote'],true) : 0;
$mode = isset($_GET['mode']) ? COM_applyFilter($_GET['mode'],true) : 0;

if (!COM_isAnonUser() && $_USER['uid'] == $vid && $pid >= 1) {

    if ( $mode == 1 ) {
    	if ($_USER['uid'] == $pid) {
    		exit;
    	}
    	$user_already_voted_res = DB_query("SELECT * FROM {$_TABLES['ff_rating_assoc']} WHERE user_id=".(int) $pid." AND voter_id=".(int) $_USER['uid']);
    	if (DB_numRows($user_already_voted_res) < 1) {
        	//Increate or decrease user rating
        	$user_rating_res = DB_query("SELECT rating FROM {$_TABLES['ff_userinfo']} WHERE uid=".(int) $pid);
            if ( DB_numRows($user_rating_res) === 0 ) {
                $check = DB_query("SELECT SUM(grade) AS user_rating FROM {$_TABLES['ff_rating_assoc']} WHERE user_id=".(int) $pid);
                $check_row = DB_fetchArray($check);
                $user_rating = (int) $check_row['user_rating'];
                DB_query("INSERT INTO {$_TABLES['ff_userinfo']} (uid,location,aim,icq,yim,msnm,interests,occupation,signature,rating)
                    VALUES (".(int)$pid.",'','','','','','','','',".$user_rating.");",1);
            } else {
            	$user_rating_row = DB_fetchArray($user_rating_res);
            	$user_rating = $user_rating_row['rating'];
            }
        	if ( $vote > 0 ) {
        		$user_rating++;
        		$grade = 1;
        	} else {
        		$user_rating--;
        		$grade = -1;
        	}
        	DB_query("UPDATE {$_TABLES['ff_userinfo']} SET rating = $user_rating WHERE uid=".(int)$pid);
        	DB_query("INSERT INTO {$_TABLES['ff_rating_assoc']} (user_id, voter_id, grade,topic_id)
        						VALUES (".(int) $pid.", ".(int)$_USER['uid'].", ".(int)$grade.",".(int)$tid.")");
        } else {
            // get user_rating;
        	$user_rating_res = DB_query("SELECT rating FROM {$_TABLES['ff_userinfo']} WHERE uid = ".(int)$pid);
        	$user_rating_row = DB_fetchArray($user_rating_res);
        	$user_rating = $user_rating_row['rating'];
        }
    } elseif( $mode == 0 ) {
        // validate an entry exists
        $res1 = DB_query("SELECT * FROM {$_TABLES['ff_rating_assoc']} WHERE user_id = ".(int) $pid ." AND voter_id = ".(int)$_USER['uid']);
        if ( DB_numRows($res1) > 0 ) {
        	//Increate or decrease user rating
        	$user_rating_res = DB_query("SELECT rating FROM {$_TABLES['ff_userinfo']} WHERE uid = ".(int)$pid);
        	$user_rating_row = DB_fetchArray($user_rating_res);
        	$user_rating = (int) $user_rating_row['rating'];
        	if ( $vote > 0 ) {
        		$user_rating++;
        	} else {
        		$user_rating--;
        	}
        	DB_query("UPDATE {$_TABLES['ff_userinfo']} SET rating = ".(int)$user_rating." WHERE uid = ".(int)$pid);
        	//Delete Their vote in the associative table
        	DB_query("DELETE FROM {$_TABLES['ff_rating_assoc']} WHERE user_id = ".(int) $pid." AND voter_id = ".(int)$_USER['uid']);
        }
    }
   	// grab the poster's current rating...
	$rating = DB_getItem($_TABLES['ff_userinfo'],'rating','uid='.(int) $pid);
	if ($rating > 0) {
		$grade = '+'. $rating;
	} else {
		$grade = $rating;
	}

	//Find out if user has rights to increase / decrease score
	if ( $_USER['uid'] > 1 && $_USER['uid'] != $pid ) { //Can't vote for yourself & must be logged in
		$user_already_voted_res = DB_query("SELECT grade FROM {$_TABLES['ff_rating_assoc']} WHERE user_id=".(int)$pid." AND voter_id=".(int) $_USER['uid']);
		if (DB_numRows($user_already_voted_res) <= 0 ) {
		// user has never voted for this poster
		    $vote_language = $LANG_GF01['grade_user'];
		    $plus_vote  = '<a href="#" onclick="ajax_voteuser('.$_USER['uid'].','.$pid.','.$tid.',1,1);return false;"><img src="'.$_CONF['site_url'].'/forum/images/plus.png" alt="plus" /></a>';
            $minus_vote = '<a href="#" onclick="ajax_voteuser('.$_USER['uid'].','.$pid.','.$tid.',-1,1);return false;"><img src="'.$_CONF['site_url'].'/forum/images/minus.png" alt="minus" /></a>';
        } else {
            // user has already voted for this poster
            $vote_language = $LANG_GF01['retract_grade'];
			$user_already_voted_row = DB_fetchArray($user_already_voted_res);
            if ($user_already_voted_row['grade'] > 0 ) {
                // gave a +1 show the minus to retract
                $plus_vote = '';
                $minus_vote = '<a href="#" onclick="ajax_voteuser('.$_USER['uid'].','.$pid.','.$tid.',-1,0);return false;"><img src="'.$_CONF['site_url'].'/forum/images/minus.png" alt="minus" /></a>';
			} else {
                // gave a -1 show the plus to retract
                $minus_vote = '';
                $plus_vote = '<a href="#" onclick="ajax_voteuser('.$_USER['uid'].','.$pid.','.$tid.',1,0);return false;"><img src="'.$_CONF['site_url'].'/forum/images/plus.png" alt="plus" /></a>';
			}
		}
		$voteHTML = $vote_language.'<br />'.$minus_vote.$plus_vote.'<br />'.$LANG_GF01['grade'].': '.$grade;
    }

    $html = htmlentities ($voteHTML);

    $retval = "<result>";
    $retval .= "<topic>$tid</topic>";
    $retval .= "<html>$html</html>";
    $retval .= "</result>";

    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("content-type: text/xml");

    print $retval;
}
?>