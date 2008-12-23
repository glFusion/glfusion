<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | rater_rpc.php                                                            |
// |                                                                          |
// | This page handles the 'AJAX' type response if the user has               |
// | Javascript enabled.                                                      |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006,2007,2008 by the following authors:                   |
// |                                                                          |
// | Authors:                                                                 |
// | Ryan Masuga, masugadesign.com  - ryan@masugadesign.com                   |
// | Masuga Design                                                            |
// |http://masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/ |
// | Komodo Media (http://komodomedia.com)                                    |
// | Climax Designs (http://slim.climaxdesigns.com/)                          |
// | Ben Nolan (http://bennolan.com/behaviour/) for Behavio(u)r!              |
// |                                                                          |
// | Homepage for this script:                                                |
// |http://www.masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/
// +--------------------------------------------------------------------------+
// | This (Unobtusive) AJAX Rating Bar script is licensed under the           |
// | Creative Commons Attribution 3.0 License                                 |
// |  http://creativecommons.org/licenses/by/3.0/                             |
// |                                                                          |
// | What that means is: Use these files however you want, but don't          |
// | redistribute without the proper credits, please. I'd appreciate hearing  |
// | from you if you're using this script.                                    |
// |                                                                          |
// | Suggestions or improvements welcome - they only serve to make the script |
// | better.                                                                  |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Licensed under a Creative Commons Attribution 3.0 License.               |
// | http://creativecommons.org/licenses/by/3.0/                              |
// |                                                                          |
// +--------------------------------------------------------------------------+

require_once '../lib-common.php';

header("Cache-Control: no-cache");
header("Pragma: nocache");

$rating_unitwidth     = 30;

//getting the values
$vote_sent  = preg_replace("/[^0-9]/","",$_REQUEST['j']);
$id_sent    = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['q']);
$ip_num     = preg_replace("/[^0-9\.]/","",$_REQUEST['t']);
$units      = preg_replace("/[^0-9]/","",$_REQUEST['c']);
$size       = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['s']);
$ip         = $_SERVER['REMOTE_ADDR'];
$ratingdate = time();
$uid        = isset($_USER['uid']) ? $_USER['uid'] : 1;

if ($size == 'sm') {
    $rating_unitwidth = 10;
} else {
    $rating_unitwidth = 30;
}

if ($vote_sent > $units) die("Sorry, vote appears to be invalid."); // kill the script because normal users will never see this.

if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $_MG_CONF['loginrequired'] == 1 )  {
    die("Sorry, user must login first");
}

$sql = "SELECT media_votes,media_rating,media_user_id FROM {$_TABLES['mg_media']} WHERE media_id='" . $id_sent . "'";
$result         = DB_query($sql);
$row            = DB_fetchArray($result);
$count          = $row['media_votes'];
$current_rating = $row['media_rating'] * $count;
$owner_id       = $row['media_user_id'];
if ( !isset($owner_id) || $owner_id == '' ) {
    $owner_id = 2;
}

$sum = ($vote_sent * 2 ) +$current_rating; // add together the current vote value and the total vote value
$tense = ($count==1) ? $LANG_MG03['vote'] : $LANG_MG03['votes']; //plural form votes/vote

if ( $uid == 1 ) {
    $sql = "SELECT id FROM {$_TABLES['mg_rating']} WHERE ip_address='$ip' AND media_id='$id_sent'";
} else {
    $sql = "SELECT id FROM {$_TABLES['mg_rating']} WHERE (uid=$uid OR ip_address='$ip') AND media_id='$id_sent'";
}
$checkResult = DB_query($sql);
if ( DB_numRows($checkResult) > 0 ) {
    $voted = 1;
} else {
    $voted = 0;
}

COM_clearSpeedlimit($_MG_CONF['rating_speedlimit'],'mgrate');
$last = COM_checkSpeedlimit ('mgrate');
if ( $last > 0 ) {
    $speedlimiterror = 1;
} else {
    $speedlimiterror = 0;
}


// checking to see if the first vote has been tallied
// or increment the current number of votes
($sum==0 ? $added=0 : $added=$count+1);

$new_rating = $sum / $added;

if(!$voted && !$speedlimiterror) {     //if the user hasn't yet voted, then vote normally...
	if (($vote_sent >= 1 && $vote_sent <= $units) && ($ip == $ip_num)) { // keep votes within range, make sure IP matches - no monkey business!
        $sql = "UPDATE {$_TABLES['mg_media']} SET media_votes = $added, media_rating = '$new_rating'
                        WHERE media_id='" . $id_sent . "'";
        DB_query($sql);

        $sql = "SELECT MAX(id) + 1 AS newid FROM " . $_TABLES['mg_rating'];
        $result = DB_query( $sql );
        $row = DB_fetchArray( $result );
        $newid = $row['newid'];
        if ( $newid < 1 ) {
            $newid = 1;
        }
        $sql = "INSERT INTO {$_TABLES['mg_rating']} (id,ip_address,uid,media_id,ratingdate,owner_id) VALUES (" . $newid . ", '" . $ip . "'," . $uid . ",'" . $id_sent . "'," . $ratingdate . "," . $owner_id . " )";
        DB_query($sql);
        COM_updateSpeedlimit ('mgrate');
	}
} //end for the "if(!$voted)"
// these are new queries to get the new values!

$sql = "SELECT media_votes,media_rating FROM {$_TABLES['mg_media']} WHERE media_id='" . $id_sent . "'";
$result         = DB_query($sql);
$row            = DB_fetchArray($result);
$count          = $row['media_votes'];
$current_rating = $row['media_rating'] * $count;

$tense = ($count==1) ? "vote" : "votes"; //plural form votes/vote

// $new_back is what gets 'drawn' on your page after a successful 'AJAX/Javascript' vote

$new_back = array();

if ( $size == 'sm' ) {
    $new_back[] .= '<ul class="small-unit-rating" style="width:'.$units*$rating_unitwidth.'px;">';
} else {
    $new_back[] .= '<ul class="unit-rating" style="width:'.$units*$rating_unitwidth.'px;">';
}
$new_back[] .= '<li class="current-rating" style="width:'.@number_format(($current_rating/$count)/2,2)*$rating_unitwidth.'px;">Current rating.</li>';
$new_back[] .= '<li class="r1-unit">1</li>';
$new_back[] .= '<li class="r2-unit">2</li>';
$new_back[] .= '<li class="r3-unit">3</li>';
$new_back[] .= '<li class="r4-unit">4</li>';
$new_back[] .= '<li class="r5-unit">5</li>';
$new_back[] .= '<li class="r6-unit">6</li>';
$new_back[] .= '<li class="r7-unit">7</li>';
$new_back[] .= '<li class="r8-unit">8</li>';
$new_back[] .= '<li class="r9-unit">9</li>';
$new_back[] .= '<li class="r10-unit">10</li>';
$new_back[] .= '</ul><div style="clear:both;"></div>';
$new_back[] .= '<span class="voted">' . $LANG_MG03['rating'] . ': <strong>'.@number_format(($current_rating/$count)/2,1).'</strong>/'.$units.' ('.$count.' '.$tense.' ' . $LANG_MG03['cast'] . ')</span>';
if ( $speedlimiterror ) {
    $new_back[] .= '<span class="thanks">&nbsp;' . sprintf($LANG_MG02['rate_speedlimit'],$last,$_MG_CONF['rating_speedlimit']) . '</span>';
} else if ( $voted ) {
    if ( $uid == 1 ) {
        $new_back[] .= '<span class="thanks">&nbsp;' . $LANG_MG03['ip_rated'] . '</span>';
    } else {
        $new_back[] .= '<span class="thanks">&nbsp;' . $LANG_MG03['uid_rated'] . '</span>';
    }
} else {
    $new_back[] .= '<span class="thanks">&nbsp;' . $LANG_MG03['thanks_for_vote'] . '</span>';
}
$allnewback = join("\n", $new_back);

// ========================

//name of the div id to be updated | the html that needs to be changed
$output = $allnewback;
echo $output;
?>