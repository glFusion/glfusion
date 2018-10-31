<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | rater.php                                                                |
// |                                                                          |
// | Non AJAX rating script                                                   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006-2018 by the following authors:                        |
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

//getting the values
$vote_sent  = preg_replace("/[^0-9]/","",$_REQUEST['j']);
$id_sent    = COM_applyFilter($_GET['q']);
$ip_num     = preg_replace("/[^0-9\.]/","",$_REQUEST['t']);
$units      = preg_replace("/[^0-9]/","",$_REQUEST['c']);
$size       = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['s']);
$plugin     = COM_applyFilter($_GET['p']);
$ip         = $_SERVER['REAL_ADDR'];
$ratingdate = time();
$uid        = isset($_USER['uid']) ? $_USER['uid'] : 1;
$uid        = (int) $uid;

// validate the referer here - just to be safe....
$referer = isset($_SERVER['HTTP_REFERER']) ? COM_sanitizeUrl($_SERVER['HTTP_REFERER']) : $_CONF['site_url'];
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

$canRate = PLG_canUserRate($plugin, $id_sent, $uid);
if ( !$canRate ) {
    header("Location: $referer");
}

if ( $vote_sent < 1 || $vote_sent == 0) {
    header("Location: $referer");
    exit;
}

// look up the item in our database....

$sql = "SELECT * FROM {$_TABLES['rating']} WHERE type='".DB_escapeString($plugin)."' AND item_id='".DB_escapeString($id_sent)."'";
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
    $sql = "SELECT id FROM {$_TABLES['rating_votes']} WHERE ip_address='".DB_escapeString($ip)."' AND item_id='".DB_escapeString($id_sent)."'";
} else {
    $sql = "SELECT id FROM {$_TABLES['rating_votes']} WHERE (uid=$uid OR ip_address='".DB_escapeString($ip)."') AND item_id='".DB_escapeString($id_sent)."'";
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
    list($new_rating,$num_votes) = RATING_addVote( $plugin,$id_sent,$vote_sent,$uid,$ip);

    COM_updateSpeedlimit ('rate');

    header("Location: " . $referer); // go back to the page we came from
    exit;
}
header("Location: $referer");
?>