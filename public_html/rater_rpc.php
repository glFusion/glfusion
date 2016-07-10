<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | rater_rpc.php                                                            |
// |                                                                          |
// | This page handles the 'AJAX' type response if the user has               |
// | Javascript enabled.                                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006-2016 by the following authors:                        |
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
// |                                                                          |
// | Licensed under a Creative Commons Attribution 3.0 License.               |
// | http://creativecommons.org/licenses/by/3.0/                              |
// |                                                                          |
// +--------------------------------------------------------------------------+

require_once 'lib-common.php';

if ( !isset($_CONF['rating_speedlimit']) ) {
    $_CONF['rating_speedlimit'] = 15;
}

header("Cache-Control: no-cache");
header("Pragma: nocache");

$rating_unitwidth     = 30;

$status = 0;

$vote_sent  = preg_replace("/[^0-9]/","",$_GET['j']);
$id_sent    = COM_applyFilter($_GET['q']);
$ip_num     = preg_replace("/[^0-9\.]/","",$_GET['t']);
$units      = preg_replace("/[^0-9]/","",$_GET['c']);
$size       = preg_replace("/[^0-9a-zA-Z]/","",$_GET['s']);
$plugin     = isset($_GET['p']) ? COM_applyFilter($_GET['p']) : '';
$ip         = $_SERVER['REMOTE_ADDR'];
$ratingdate = time();
$uid        = isset($_USER['uid']) ? $_USER['uid'] : 1;

if ($size == 'sm') {
    $rating_unitwidth = 15;
} else {
    $rating_unitwidth = 30;
}

if ( $plugin == '' ) die('no type specified');

if ($vote_sent > $units) die("Sorry, vote appears to be invalid."); // kill the script because normal users will never see this.

$canRate = PLG_canUserRate( $plugin, $id_sent, $uid );

if ( $canRate ) {
    // look up the item in our database....

    list($rating_id, $current_rating, $current_votes) = RATING_getRating( $plugin, $id_sent );

    $voted = RATING_hasVoted( $plugin, $id_sent, $uid, $ip );
    $status = $voted;

    COM_clearSpeedlimit($_CONF['rating_speedlimit'],'rate');
    $last = COM_checkSpeedlimit ('rate');
    if ( $last > 0 ) {
        $speedlimiterror = 1;
        $status = 2;
    } else {
        $speedlimiterror = 0;
    }
    if(!$voted && !$speedlimiterror) {     //if the user hasn't yet voted, then vote normally...
       	if (($vote_sent >= 1 && $vote_sent <= $units) && ($ip == $ip_num)) { // keep votes within range, make sure IP matches - no monkey business!
            list($new_rating,$added) = RATING_addVote( $plugin, $id_sent, $vote_sent, $uid, $ip );
            COM_updateSpeedlimit ('rate');
$voted = 1;
    	}
    } else {
        $added = $current_votes;
        $new_rating = $current_rating;
    }
} else {
    list($rating_id, $current_rating, $cout) = RATING_getRating( $plugin, $id_sent );
    $added = $count;
    $new_rating = $current_rating;
    $status = 3;
    $voted = 1;
}

$count          = $added;
$current_rating = $new_rating ;

$tense = ($count==1) ? $LANG13['vote'] : $LANG13['votes'];

// set message

if ( $status == 1 ) {
    // either IP or UID has already voted
    $message = "<script>alert('". $LANG13['ip_rated'] . "');</script>";
} elseif ( $status == 2 ) {
    $message = "<script>alert('".sprintf($LANG13['rate_speedlimit'],$last,$_CONF['rating_speedlimit'])."');</script>";
} elseif ( $status == 3 ) {
    // no permission to vote or your already own the item
    $message = "<script>alert('".$LANG13['own_rated']."');</script>";
} else {
    $message = '<br><span class="thanks">&nbsp;' . $LANG13['thanks_for_vote'] . '</span>';
}

// $new_back is what gets 'drawn' on your page after a successful 'AJAX/Javascript' vote

$new_back = array();

$newBar = RATING_ratingBar( $plugin,$id_sent, $count,$current_rating, $voted,5,$voted,$size,0);
echo join("\n",array($newBar,$message));
?>