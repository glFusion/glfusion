<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-rating.php                                                           |
// |                                                                          |
// | Rating Interface                                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
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
    die ('This file can not be used on its own.');
}

/*
 * We will check when they rate the item if they have already
 * rated it based on type, uid, ip_address ...
 *
 * A plugin will be responsible for keeping track of the items rating
 * and how many votes have been cast.  This is more for performance
 * issues than anything else.
 *
 * When an item is rated, the following plugin APIs will be called:
 *
 * PLG_canUserRate( $type, $id, $uid )
 *
 * This will let the plugin validate any permissions to ensure the user
 * can rate the item.  The plugin should also validate if the owner of
 * the item is the same as the user voting and not allow.
 *
 * Maybe this item should return a set of known error messages...???
 *
 * PLG_itemRated( $type, $id, $rating, $votes )
 *
 * This will allow the plugin to update their records that the item has
 * been rated and store the rating and votes.
 *
 */

function RATING_ratingBar($type, $id, $total_votes, $total_value, $voted=0, $units='', $static='',$size='',$wrapper = 1) {
    global $_USER, $_TABLES, $_CONF, $LANG13;

    if ( $size == 'sm') {
        $rating_unitwidth = 15;
    } else {
        $rating_unitwidth     = 30;
    }

    //set some variables
    $ip     = $_SERVER['REAL_ADDR'];
    $uid    = isset($_USER['uid']) ? $_USER['uid'] : 1;

    if (!$units) {
        $units = 5;
    }
    if (!$static) {
        $static = FALSE;
    }

    if ($total_votes < 1) {
	    $count = 0;
    } else {
	    $count=$total_votes; //how many votes total
    }

    $current_rating=$total_value;
    $tense = ($count==1) ? $LANG13['vote'] : $LANG13['votes'];

    // determine whether the user has voted, so we know how to draw the ul/li

    // now draw the rating bar
    $rating_width   = @number_format($current_rating,2)*$rating_unitwidth;
    $rating1        = @number_format($current_rating,2);
    $rating2        = @number_format($current_rating,2);

    if ( $static ) {
        $static_rater = '';
        if ( $wrapper ) {
    	    $static_rater .= '<div class="ratingbar">';
        	$static_rater .= '<div id="unit_long'.$id.'">';
        }
    	if ( $size == 'sm' ) {
    	    $static_rater .= '<ul id="unit_ul'.$id.'" class="small-rating-unit" style="width:'.$rating_unitwidth*$units.'px;">';
    	} else {
    	    $static_rater .= '<ul id="unit_ul'.$id.'" class="rating-unit" style="width:'.$rating_unitwidth*$units.'px;">';
    	}
    	$static_rater .= '<li class="current-rating" style="width:'.$rating_width.'px;">'.$LANG13['currently'].' '.$rating2.'/'.$units.'</li>';
    	$static_rater .= '</ul>';
    	$static_rater .= '<span class="static">' . $LANG13['rating'] . ': <strong> '.$rating1.'</strong>/'.$units.' ('.$count.' '.$tense.' '.$LANG13['cast'] . ')</span>';
        if ( $wrapper ) {
    	    $static_rater .= '</div>';
        	$static_rater .= '</div>';
        }
    	return $static_rater;
    } else {
        $rater ='';
        if ( $wrapper ) {
            $rater.='<div class="ratingbar">';
            $rater.='<div id="unit_long'.$id.'">';
        }
        if ( $size == 'sm' ) {
            $rater.='  <ul id="unit_ul'.$id.'" class="small-rating-unit" style="width:'.$rating_unitwidth*$units.'px;">';
        } else {
            $rater.='  <ul id="unit_ul'.$id.'" class="rating-unit" style="width:'.$rating_unitwidth*$units.'px;">';
        }
        $rater.='     <li class="current-rating" style="width:'.$rating_width.'px;">'.$LANG13['currently'].' '.$rating2.'/'.$units.'</li>';

        for ($ncount = 1; $ncount <= $units; $ncount++) { // loop from 1 to the number of units
            if(!$voted) { // if the user hasn't yet voted, draw the voting stars
                $rater.='<li><a href="'.$_CONF['site_url'].'/rater.php?p='.$type.'&amp;j='.$ncount.'&amp;q='.$id.'&amp;t='.$ip.'&amp;c='.$units.'&amp;s='.$size.'" title="'.$ncount.' out of '.$units.'" class="r'.$ncount.'-unit rater" rel="nofollow">'.$ncount.'</a></li>';
            }
        }
        $ncount=0; // resets the count

        $rater.='  </ul>';
        $rater.='  <span';
        if ( $voted ){
            $rater.=' class="voted"';
        }
        $rater.='>' . $LANG13['rating'] . ': <strong> ' . $rating1 . '</strong>/'.$units.' ('.$count.' '.$tense.' '.$LANG13['cast'].')';
        $rater.='  </span>';
        if ( $wrapper ) {
            $rater.='</div>';
            $rater.='</div>';
        }
        return $rater;
    }
}

/**
* Return an array of all voting records
*
* Returns an array of all voting records for either a $type or an $item_id.
*
* @param        string      $type     plugin name
* @param        string      $item_id  item id (optional)
* @param        string      $sort     column to sort data by
* @param        string      $sortdir  asc or desc
* @param        array       $filterArray An array of fields => values for where clause
* @return       array       an array of all voting records that match the search criteria
*
*/
function RATING_getVoteData( $type, $item_id='', $sort='ratingdate', $sortdir = 'desc', $filterArray = '' )
{
    global $_TABLES;

    $whereClause = '';
    $retval = array();

    $validFields    = array('id','type','item_id','uid','vote','ip_address','ratingdate');
    $validDirection = array('asc','desc');

    if ( !in_array($sort,$validFields) ) {
        $sort = 'ratingdate';
    }
    if ( !in_array($sortdir,$validDirection) ) {
        $sortdir = 'desc';
    }
    if ( $item_id != '' ) {
        $whereClause = " AND item_id='" . $item_id . "' ";
    }
    if ( is_array($filterArray) ) {
        foreach ( $filterArray AS $bType => $filter ) {
            $whereClause .= ' ' . $bType . ' ' . $filter;
        }
    }

    $sql = "SELECT * FROM {$_TABLES['rating_votes']} AS r LEFT JOIN {$_TABLES['users']} AS u ON r.uid=u.uid WHERE type='".$type."'" . $whereClause . ' ORDER BY ' . $sort . ' ' . $sortdir;

    $result = DB_query($sql);

    while ( $row = DB_fetchArray($result) ) {
        $retval[] = $row;
    }
    return $retval;
}

/**
* Returns the rating data for an item.
*
* Returns an array consisting of the rating_id, votes and rating of a specific
* item.
*
* @param        string      $type     plugin name
* @param        string      $item_id  item id
* @return       array       an array of rating_id, rating, votes
*
*/
function RATING_getRating( $type, $item_id )
{
    global $_TABLES;

    $sql = "SELECT * FROM {$_TABLES['rating']} WHERE type='".DB_escapeString($type)."' AND item_id='".DB_escapeString($item_id)."'";
    $result = DB_query($sql);
    if ( DB_numRows($result) > 0 ) {
        $row            = DB_fetchArray($result);
        $votes          = $row['votes'];
        $rating         = $row['rating'];
        $rating_id      = $row['id'];
    } else {
        $votes          = 0;
        $rating         = 0;
        $rating_id      = 0;
    }

    return array($rating_id, $rating, $votes);
}


/**
* Check if user has already rated for an item
*
* Determines if user or IP has already rated the item.
*
* @param        string      $type     plugin name
* @param        string      $item_id  item id
* @param        int         $uid      user id
* @param        string      $ip       IP address of user
* @return       bool        true if user or ip has already rated, false if not
*
*/
function RATING_hasVoted( $type, $item_id, $uid, $ip )
{
    global $_TABLES;

    $voted = 0;

    if ( $uid == 1 ) {
        $sql = "SELECT id FROM {$_TABLES['rating_votes']} WHERE ip_address='".DB_escapeString($ip)."' AND item_id='".DB_escapeString($item_id)."'";
    } else {
        $sql = "SELECT id FROM {$_TABLES['rating_votes']} WHERE (uid=$uid OR ip_address='".DB_escapeString($ip)."') AND item_id='".DB_escapeString($item_id)."'";
    }
    $checkResult = DB_query($sql);
    if ( DB_numRows($checkResult) > 0 ) {
        $voted = 1;
    } else {
        $voted = 0;
    }

    return $voted;

}

/**
* Removes all rating data for an item
*
* Removes all rating data for an item
*
* @param        string      $type     plugin name
* @param        string      $item_id  item id
* @return       none
*
*/
function RATING_resetRating( $type, $item_id )
{
    global $_TABLES;

    DB_delete($_TABLES['rating'],array('type','item_id'),array($type,$item_id));
    DB_delete($_TABLES['rating_votes'],array('type','item_id'),array($type,$item_id));

    PLG_itemRated( $type, $item_id, 0, 0 );
}

/**
* Deletes a specific rating entry
*
* Deletes a specific rating entry and recalculates the new rating
*
* @param        string      $voteID   The ID of the rating_votes record
* @return       bool        true if successful otherwise false
*
*/
function RATING_deleteVote( $voteID )
{
    global $_TABLES;

    $retval = false;

    $result = DB_query("SELECT * FROM {$_TABLES['rating_votes']} WHERE id=".$voteID);
    if ( DB_numRows($result) > 0 ) {
        $row = DB_fetchArray($result);
        $item_id = $row['item_id'];
        $type = $row['type'];
        $user_rating = $row['rating'];

        list($rating_id, $current_rating, $current_votes) = RATING_getRating( $type, $item_id );

        if ( $current_votes > 0 ) {
            $tresult = DB_query("SELECT SUM( rating ),COUNT( item_id ) FROM  {$_TABLES['rating_votes']} WHERE item_id = ".$item_id." AND type='".$type."'");
            list($total_rating,$total_votes) = DB_fetchArray($tresult);
            $new_total_rating = $total_rating - $user_rating;
            $new_total_votes  = $total_votes  - 1;
            if ( $new_total_rating > 0 && $new_total_votes > 0 ) {
                $new_rating = $new_total_rating / $new_total_votes;
                $votes = $new_total_votes;
            } else {
                $new_rating = 0;
                $new_total_votes = 0;
                $votes = 0;
            }
            $new_rating = sprintf("%2.02f",$new_rating);
            $sql = "UPDATE {$_TABLES['rating']} SET votes=".$new_total_votes.", rating='".DB_escapeString($new_rating)."' WHERE id = ".$rating_id;
            DB_query($sql);
            DB_delete($_TABLES['rating_votes'],'id',$voteID);
            PLG_itemRated( $type, $item_id, $new_rating, $votes );
            $retval = true;
        }
    }
    return $retval;
}

/**
* Add a new rating to an item
*
* Adds a new rating for an item. This will calculate the new overall
* rating, update the vote table with the user / ip info and ask the
* plugin to update its records.
*
* @param        string      $type     plugin name
* @param        string      $item_id  item id
* @param        int         $rating   rating sent by user
* @param        int         $uid      user id of rater
* @param        string      $ip       IP address of rater
* @return       array       an array with the new overall rating and total number
*                           of votes.
*
*/
function RATING_addVote( $type, $item_id, $rating, $uid, $ip )
{
    global $_TABLES;

    $ratingdate = time();

    list($rating_id, $current_rating, $current_votes) = RATING_getRating( $type, $item_id );

    if ( $rating < 1 ) {
        return array($current_rating, $current_votes);
    }

    $tresult = DB_query("SELECT SUM( rating ),COUNT( item_id ) FROM  {$_TABLES['rating_votes']} WHERE item_id = '".DB_escapeString($item_id)."' AND type='".DB_escapeString($type)."'");
    if ( DB_numRows($tresult) > 0 ) {
        list($total_rating,$total_votes) = DB_fetchArray($tresult);
    } else {
        $total_rating = 0;
        $total_votes  = 0;
    }
    $sum = $total_rating + $rating;
    $votes = $total_votes + 1;

    if ( $sum > 0 && $votes > 0 ) {
        $new_rating = $sum / $votes;
    } else {
        $new_rating = 0;
        $sum = 0;
        $votes = 0;
    }

    $new_rating = sprintf("%2.02f",$new_rating);

    if ( $rating_id != 0 ) {
        $sql = "UPDATE {$_TABLES['rating']} SET votes=".$votes.", rating='".DB_escapeString($new_rating)."' WHERE id = ".$rating_id;
        DB_query($sql);
    } else {
        $sql = "SELECT MAX(id) + 1 AS newid FROM " . $_TABLES['rating'];
        $result = DB_query( $sql );
        $row = DB_fetchArray( $result );
        $newid = $row['newid'];
        if ( $newid < 1 ) {
            $newid = 1;
        }
        $sql = "INSERT INTO {$_TABLES['rating']} (id,type,item_id,votes,rating) VALUES (" . $newid . ", '". $type . "','" . DB_escapeString($item_id). "'," . $votes . ",'" . DB_escapeString($new_rating) . "' )";
        DB_query($sql);
    }
    $sql = "INSERT INTO {$_TABLES['rating_votes']} (type,item_id,rating,uid,ip_address,ratingdate) " .
           "VALUES ('".DB_escapeString($type)."','".DB_escapeString($item_id)."',".$rating.",".$uid.",'".DB_escapeString($ip)."',".$ratingdate.");";
    DB_query($sql);
    PLG_itemRated( $type, $item_id, $new_rating, $votes );

    return array($new_rating, $votes);
}


/**
* Retrieve an array of item_id's the current user has rated
*
* This function will return an array of all the items the user
* has rated for the specific type.
*
* @param        string      $type     plugin name
* @return       array       array of item ids
*
*/
function RATING_getRatedIds($type)
{
    global $_TABLES, $_USER;

    $ip     = DB_escapeString($_SERVER['REAL_ADDR']);
    $uid    = isset($_USER['uid']) ? $_USER['uid'] : 1;

    $ratedIds = array();
    if ( $uid == 1 ) {
        $sql = "SELECT item_id FROM {$_TABLES['rating_votes']} WHERE type='".$type."' AND (ip_address='".DB_escapeString($ip)."')";
    } else {
        $sql = "SELECT item_id FROM {$_TABLES['rating_votes']} WHERE type='".$type."' AND (uid=".(int)$uid." OR ip_address='".DB_escapeString($ip)."')";
    }
    $result = DB_query($sql,1);
    while ( $row = DB_fetchArray($result) ) {
        $ratedIds[] = $row['item_id'];
    }
    return $ratedIds;
}
?>