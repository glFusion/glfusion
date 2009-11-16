<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | rater.php                                                                |
// |                                                                          |
// | non AJAX based rating script                                             |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2009 by the following authors:                        |
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

require_once 'lib-common.php';

$_CONF['rating_speedlimit'] = 15;

//getting the values
$vote_sent  = preg_replace("/[^0-9]/","",$_REQUEST['j']);
$id_sent    = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['q']);
$ip_num     = preg_replace("/[^0-9\.]/","",$_REQUEST['t']);
$units      = preg_replace("/[^0-9]/","",$_REQUEST['c']);
$size       = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['s']);
$plugin     = COM_applyFilter($_GET['p']);
$ip         = $_SERVER['REMOTE_ADDR'];
$ratingdate = time();
$uid        = isset($_USER['uid']) ? $_USER['uid'] : 1;

// validate the referer here - just to be safe....
$referer = $_SERVER['HTTP_REFERER'];
if ( $referer == '' ) {
    $referer = $_CONF['site_url'];
}

$sLength = strlen($_CONF['site_url']);
if ( substr($referer,0,$sLength) != $_CONF['site_url'] ) {
    $referer = $_CONF['site_url'];
}

if ($vote_sent > $units) {
    die("Sorry, vote appears to be invalid."); // kill the script because normal users will never see this.
}

$canRate = PLG_canUserRate($plugin, $id_sent, $USER['uid']);
if ( !$canRate ) {
    header("Location: $referer");
}

// look up the item in our database....

$sql = "SELECT * FROM {$_TABLES['rating']} WHERE type='".addslashes($plugin)."' AND item_id='".addslashes($id_sent)."'";
$result = DB_query($sql);
if ( DB_numRows($result) > 0 ) {
    $row            = DB_fetchArray($result);
    $count          = $row['votes'];
    $current_rating = $row['rating'];
    $rating_id      = $row['id'];
} else {
    $count          = 0;
    $current_rating = 0;
    $rating_id      = 0;
}

if ( $uid == 1 ) {
    $sql = "SELECT id FROM {$_TABLES['rating_votes']} WHERE ip_address='".addslashes($ip)."' AND item_id='".addslashes($id_sent)."'";
} else {
    $sql = "SELECT id FROM {$_TABLES['rating_votes']} WHERE (uid=$uid OR ip_address='".addslashes($ip)."') AND item_id='".addslashes($id_sent)."'";
}
$checkResult = DB_query($sql);
if ( DB_numRows($checkResult) > 0 ) {
    $voted = 1;
} else {
    $voted = 0;
}

COM_clearSpeedlimit($_CONF['rating_speedlimit'],'rate');
$last = COM_checkSpeedlimit ('rate');
if ( $last > 0 ) {
    $speedlimiterror = 1;
} else {
    $speedlimiterror = 0;
}

if(!$voted  && !$speedlimiterror) {

    $sum = $vote_sent  + ( $current_rating * $count ); // add together the current vote value and the total vote value
    $tense = ($count==1) ? $LANG_MG03['vote'] : $LANG_MG03['votes']; //plural form votes/vote

    // checking to see if the first vote has been tallied
    // or increment the current number of votes
    ($sum==0 ? $added=0 : $added=$count+1);

    $new_rating = $sum / $added;

    if (($vote_sent >= 1 && $vote_sent <= $units ) && ($ip == $ip_num)) { // keep votes within range
	    if ( $rating_id != 0 ) {
            $sql = "UPDATE {$_TABLES['rating']} SET votes=".$added.", rating=".$new_rating." WHERE id = ".$rating_id;
            DB_query($sql);
        } else {
            $sql = "SELECT MAX(id) + 1 AS newid FROM " . $_TABLES['rating'];
            $result = DB_query( $sql );
            $row = DB_fetchArray( $result );
            $newid = $row['newid'];
            if ( $newid < 1 ) {
                $newid = 1;
            }
            $sql = "INSERT INTO {$_TABLES['rating']} (id,type,item_id,votes,rating) VALUES (" . $newid . ", '". $plugin . "','" . addslashes($id_sent). "'," . $added . "," . $new_rating . " )";
            DB_query($sql);
        }
        $sql = "INSERT INTO {$_TABLES['rating_votes']} (type,item_id,uid,ip_address,ratingdate) " .
               "VALUES ('".addslashes($plugin)."','".addslashes($id_sent)."',".$uid.",'".addslashes($ip)."',".$ratingdate.");";
        DB_query($sql);
        PLG_itemRated( $plugin, $id_sent, $new_rating, $added );
        COM_updateSpeedlimit ('rate');
        COM_resetSpeedlimit('rate');
	}

    header("Location: " . $referer); // go back to the page we came from
    exit;
}
header("Location: $referer");
?>