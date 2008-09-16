<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | lib-rating.php                                                           |
// |                                                                          |
// | Rating interface library                                                 |
// +--------------------------------------------------------------------------+
// | $Id:: lib-rating.php 2869 2008-07-31 14:38:32Z mevans0263               $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on prior work by:                                                  |
// | Copyright (C) 2006,2007, 2008 by the following authors:                  |
// | Authors:                                                                 |
// | Ryan Masuga, masugadesign.com  - ryan@masugadesign.com                   |
// | Masuga Design                                                            |
// |http://masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar  |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
{
    die ('This file can not be used on its own.');
}

function mgRating_bar($id, $total_votes, $total_value, $voted=0, $units='', $static='',$size='') {
    global $_USER, $_MG_CONF, $_TABLES, $_CONF, $LANG_MG03;

    if ( $size == 'sm') {
        $rating_unitwidth = 10;
    } else {
        $rating_unitwidth     = 30;
    }

    //set some variables
    $ip     = $_SERVER['REMOTE_ADDR'];
    $uid    = isset($_USER['uid']) ? $_USER['uid'] : 1;

    if (!$units) {
        $units = 10;
    }
    if (!$static) {
        $static = FALSE;
    }

    if ($total_votes < 1) {
	    $count = 0;
    } else {
	    $count=$total_votes; //how many votes total
    }

    $current_rating=$total_value;           //total number of rating added together and stored
    $tense = ($count==1) ? $LANG_MG03['vote'] : $LANG_MG03['votes']; //plural form votes/vote

    // determine whether the user has voted, so we know how to draw the ul/li

    // now draw the rating bar
    $rating_width   = @number_format($current_rating/$count,2)*$rating_unitwidth;
    $rating1        = @number_format($current_rating/$count,1);
    $rating2        = @number_format($current_rating/$count,2);

    if ($static == 'static') {
        $static_rater = array();
    	$static_rater[] .= "\n".'<div class="ratingblock">';
    	$static_rater[] .= '<div id="unit_long'.$id.'">';
    	if ( $size == 'sm' ) {
    	    $static_rater[] .= '<ul id="unit_ul'.$id.'" class="small-unit-rating" style="width:'.$rating_unitwidth*$units.'px;">';
    	} else {
    	    $static_rater[] .= '<ul id="unit_ul'.$id.'" class="unit-rating" style="width:'.$rating_unitwidth*$units.'px;">';
    	}
    	$static_rater[] .= '<li class="current-rating" style="width:'.$rating_width.'px;">Currently '.$rating2.'/'.$units.'</li>';
    	$static_rater[] .= '</ul>';
    	$static_rater[] .= '<span class="static">' . $LANG_MG03['rating'] . ': <strong> '.$rating1.'</strong>/'.$units.' ('.$count.' '.$tense.' cast)</span>';
    	$static_rater[] .= '</div>';
    	$static_rater[] .= '</div>';
    	return join("\n", $static_rater);
    } else {
        $rater ='';
        $rater.='<div class="ratingblock">';
        $rater.='<div id="unit_long'.$id.'">';
        if ( $size == 'sm' ) {
            $rater.='  <ul id="unit_ul'.$id.'" class="small-unit-rating" style="width:'.$rating_unitwidth*$units.'px;">';
        } else {
            $rater.='  <ul id="unit_ul'.$id.'" class="unit-rating" style="width:'.$rating_unitwidth*$units.'px;">';
        }
        $rater.='     <li class="current-rating" style="width:'.$rating_width.'px;">Currently '.$rating2.'/'.$units.'</li>';

        for ($ncount = 1; $ncount <= $units; $ncount++) { // loop from 1 to the number of units
            if(!$voted) { // if the user hasn't yet voted, draw the voting stars
                $rater.='<li><a href="rater.php?j='.$ncount.'&amp;q='.$id.'&amp;t='.$ip.'&amp;c='.$units.'&amp;s='.$size.'" title="'.$ncount.' out of '.$units.'" class="r'.$ncount.'-unit rater" rel="nofollow">'.$ncount.'</a></li>';
            }
        }
        $ncount=0; // resets the count

        $rater.='  </ul>';
        $rater.='  <span';
        if($voted){
            $rater.=' class="voted"';
        }
        $rater.='>' . $LANG_MG03['rating'] . ': <strong> '.$rating1.'</strong>/'.$units.' ('.$count.' '.$tense.' cast)';
        $rater.='  </span>';
        $rater.='</div>';
        $rater.='</div>';
        return $rater;
    }
}
?>