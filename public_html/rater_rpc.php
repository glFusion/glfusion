<?php
/**
* glFusion CMS
*
* glFusion Rating Interface v2
*
* @license Creative Commons Attribution 3.0 License.
*     http://creativecommons.org/licenses/by/3.0/                              |
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on original work Copyright (C) 2006,2007,2008 by the following authors:
*   Ryan Masuga, masugadesign.com  - ryan@masugadesign.com
*   Masuga Design
*      http://masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar
*   Komodo Media (http://komodomedia.com)
*   Climax Designs (http://slim.climaxdesigns.com/)
*   Ben Nolan (http://bennolan.com/behaviour/) for Behavio(u)r!
*
*  Homepage for this script:
*  http://www.masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/
*
*  This (Unobtusive) AJAX Rating Bar script is licensed under the
*  Creative Commons Attribution 3.0 License
*    http://creativecommons.org/licenses/by/3.0/
*
*  What that means is: Use these files however you want, but don't
*  redistribute without the proper credits, please. I'd appreciate hearing
*  from you if you're using this script.
*
*/

require_once 'lib-common.php';

if ( !isset($_CONF['rating_speedlimit']) ) {
    $_CONF['rating_speedlimit'] = 15;
}

header("Cache-Control: no-cache");
header("Pragma: nocache");

$status     = 0;
$vote_sent  = (int)$_GET['j'];
$id_sent    = COM_applyFilter($_GET['q']);
$ip_sent    = preg_replace("/[^0-9\.\:]/","",$_GET['t']);
$units      = (int)$_GET['c'];
$size       = preg_replace("/[^0-9a-zA-Z]/","",$_GET['s']);
$plugin     = isset($_GET['p']) ? COM_applyFilter($_GET['p']) : '';
$ip         = $_SERVER['REAL_ADDR'];
$ratingdate = time();
$uid        = isset($_USER['uid']) ? (int)$_USER['uid'] : 1;

if ($plugin == '') {
    die('no type specified');
}
if ($vote_sent > $units || $vote_sent < 1) {
    // kill the script because normal users will never see this.
    die("Sorry, vote appears to be invalid.");
}

$canRate = PLG_canUserRate($plugin, $id_sent, $uid);
$Rater = \glFusion\Rater\Rater::create($plugin, $id_sent);

if ($canRate) {
    // Check if the user has already voted.
    $status = $Rater->userHasVoted();

    COM_clearSpeedlimit($_CONF['rating_speedlimit'], 'rate');
    $last = COM_checkSpeedlimit('rate');
    if ($last == 0 && !$status && $ip == $ip_sent) {
        //if the user hasn't yet voted, then vote normally...
        // keep votes within range, make sure IP matches - no monkey business!
        $Rater->addVote($vote_sent);
        COM_updateSpeedlimit ('rate');
    } else {
        $status = 2;
    }
} else {
    $status = 3;
}
$total_votes = $Rater->getTotalVotes();
$new_rating = $Rater->getRating();
$tense = ($total_votes == 1) ? $LANG13['vote'] : $LANG13['votes'];
$withJS = true;

// set message
switch ($status) {
case 1:     // either IP or UID has already voted
    $message = "<script>alert('". $LANG13['ip_rated'] . "');</script>";
    $withJS = true;
    break;
case 2:     // voting too frequently
    $message = "<script>alert('" .
        sprintf($LANG13['rate_speedlimit'],$last,$_CONF['rating_speedlimit']) .
        "');</script>";
    $withJS=true;
    break;
case 3:     // no permission to vote or your already own the item
    $message = "<script>alert('".$LANG13['own_rated']."');</script>";
    $withJS=true;
    break;
default:    // vote recorded normally
    $message = '<span class="thanks">&nbsp;' . $LANG13['thanks_for_vote'] . '</span>';
    $withJS=false;
    break;
}

// Updating the ratingbar and echo back to the javascript
$newBar = $Rater->withWrapper(false)->withJS($withJS)->withSize($size)->Render();
echo implode("\n", array($newBar, $message));
