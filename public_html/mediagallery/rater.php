<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// | lib-rating.php - The function that draws the rating bar.                  |
// +---------------------------------------------------------------------------+
// | $Id:: rater.php 2118 2008-04-01 01:37:05Z mevans0263                     $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2006,2007,2008 by the following authors:                    |
// |                                                                           |
// | Authors:                                                                  |
// | Ryan Masuga, masugadesign.com  - ryan@masugadesign.com                    |
// | Masuga Design                                                             |
// |(http://masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/)|
// | Komodo Media (http://komodomedia.com)                                     |
// | Climax Designs (http://slim.climaxdesigns.com/)                           |
// | Ben Nolan (http://bennolan.com/behaviour/) for Behavio(u)r!               |
// |                                                                           |
// | Homepage for this script:                                                 |
// |http://www.masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/
// +---------------------------------------------------------------------------+
// | This (Unobtusive) AJAX Rating Bar script is licensed under the            |
// | Creative Commons Attribution 3.0 License                                  |
// |  http://creativecommons.org/licenses/by/3.0/                              |
// |                                                                           |
// | What that means is: Use these files however you want, but don't           |
// | redistribute without the proper credits, please. I'd appreciate hearing   |
// | from you if you're using this script.                                     |
// |                                                                           |
// | Suggestions or improvements welcome - they only serve to make the script  |
// | better.                                                                   |
// +---------------------------------------------------------------------------+
// | Adapted for Media Gallery by:                                             |
// | Mark R. Evans                  - mark@gllabs.org                          |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | Licensed under a Creative Commons Attribution 3.0 License.                |
// | http://creativecommons.org/licenses/by/3.0/                               |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

require_once('../lib-common.php');

//getting the values
$vote_sent  = preg_replace("/[^0-9]/","",$_REQUEST['j']);
$id_sent    = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['q']);
$ip_num     = preg_replace("/[^0-9\.]/","",$_REQUEST['t']);
$units      = preg_replace("/[^0-9]/","",$_REQUEST['c']);
$ip         = $_SERVER['REMOTE_ADDR'];
$referer    = $_SERVER['HTTP_REFERER'];
$ratingdate = time();
$uid        = isset($_USER['uid']) ? $_USER['uid'] : 1;

if ($vote_sent > $units) {
    die("Sorry, vote appears to be invalid."); // kill the script because normal users will never see this.
}

$sql = "SELECT media_votes,media_rating,media_user_id FROM {$_TABLES['mg_media']} WHERE media_id='" . $id_sent . "'";
$result         = DB_query($sql);
$row            = DB_fetchArray($result);
$count          = $row['media_votes'];
$current_rating = $row['media_rating'];
$owner_id       = $row['media_user_id'];
if ( !isset($owner_id) || $owner_id == '' ) {
    $owner_id = 2;
}

$sql = "SELECT id FROM {$_TABLES['mg_rating']} WHERE (uid=$uid OR ip_address='$ip') AND media_id='$id'";
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

$sum = ($vote_sent * 2) + $current_rating; // add together the current vote value and the total vote value
$tense = ($count==1) ? $LANG_MG03['vote'] : $LANG_MG03['votes']; //plural form votes/vote

// checking to see if the first vote has been tallied
// or increment the current number of votes
($sum==0 ? $added=0 : $added=$count+1);

$new_rating = $sum / $added;

if(!$voted  && !$speedlimiterror) {
    if (($vote_sent >= 1 && $vote_sent <= ($units * 2) ) && ($ip == $ip_num)) { // keep votes within range
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
    header("Location: " . $referer); // go back to the page we came from
    exit;
}
header("Location: $referer");
?>