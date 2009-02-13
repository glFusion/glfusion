<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// |                                                                          |
// | This file does two things: 1) it implements the necessary glFusion Plugin|
// | API method and 2) implements all the common code needed by the Media     |
// | Gallery PHP files.                                                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2009 by the following authors:                        |
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

if (!defined ('GVERSION'))
{
    die ('This file can not be used on its own.');
}

/**
* Language file Include
*/
$langfile = $_CONF['path'] . 'plugins/mediagallery/language/' . $_CONF['language'] . '.php';

if (file_exists ($langfile)) {
    include_once $langfile;
} else {
    include_once $_CONF['path'] . 'plugins/mediagallery/language/english.php';
}

/*
* Include MediaGallery config file
*/
require_once $_CONF['path'].'plugins/mediagallery/mediagallery.php';
require_once $_CONF['path'].'plugins/mediagallery/config.php';
require_once $_MG_CONF['path_html'].'common.php';
require_once $_CONF['path'].'plugins/mediagallery/include/classAlbum.php';

MG_initAlbums();

/**
* Check to see if this Plugin uses comments
*
* @return   boolean true or false
*
*/
function plugin_commentsupport_mediagallery()
{
    return true;
}


/**
 * Plugin function that is called after comment form is submitted.
 * Needs to at least save the comment and check return value.
 * Add any additional logic your plugin may need to perform on comments.
 *
 * $title       comment title
 * $comment     comment text
 * $id          Item id to which $cid belongs
 * $pid         comment parent
 * $postmode    'html' or 'text'
 *
 */
function plugin_savecomment_mediagallery($title,$comment,$id,$pid,$postmode) {
    global $_CONF, $_MG_CONF, $_TABLES, $LANG03;

    $title = strip_tags ($title);
    $pid = COM_applyFilter ($pid, true);
    $postmode = COM_applyFilter ($postmode);

    $ret = CMT_saveComment ( $title, $comment, $id, $pid, 'mediagallery',$postmode);

    if ( $ret > 0 ) {
        return COM_siteHeader() . CMT_commentform ($title, $comment, $id, $pid, 'mediagallery', $LANG03[14], $postmode) . COM_siteFooter();
    } else {
        $comments = DB_count ($_TABLES['comments'], array('sid','type'), array($id, 'mediagallery'));
        DB_change($_TABLES['mg_media'],'media_comments', $comments, 'media_id',$id);
        return (COM_refresh ($_MG_CONF['site_url'] . "/media.php?s=$id") );
    }
}
function plugin_editcomment_mediagallery($cid,$id) {
    global $_CONF, $_MG_CONF, $_TABLES;

    echo COM_refresh ($_MG_CONF['site_url'] . "/media.php?s=$id");
    exit;
}

/**
 * Plugin function to delete a comment
 * $cid    Comment to be deleted
 * $id     Item id to which $cid belongs
 *
 */
function plugin_deletecomment_mediagallery($cid,$id) {
    global $_CONF, $_MG_CONF, $_TABLES;

    // find the album that holds this peice of media

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} WHERE media_id='" . $id . "'";
    $result = DB_query($sql);
    $nRows  = DB_numRows($result);
    if ( $nRows > 0 ) {
        $row = DB_fetchArray($result);
        $sql = "SELECT group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['mg_albums']} WHERE album_id=" . $row['album_id'];
        $result = DB_query($sql);
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $access = SEC_hasAccess ($row['owner_id'],
                        $row['group_id'],
                        $row['perm_owner'],
                        $row['perm_group'],
                        $row['perm_members'],
                        $row['perm_anon']);
        } else {
            $access = 0;
        }
    } else {
        $access = 0;
    }

    if ($access == 3 || SEC_hasRights('mediagallery.admin')){
        if (CMT_deleteComment($cid, $id, 'mediagallery') == 0) {
            //reduce count in media table
            $comments = DB_count ($_TABLES['comments'], array('sid','type'), array($id, 'mediagallery'));
            DB_change($_TABLES['mg_media'],'media_comments', $comments, 'media_id', $id);
            // Now redirect the program flow to the view of the file and its comments
            return (COM_refresh($_MG_CONF['site_url'] . "/media.php?s=$id"));

        } else {
            return false;
        }
    } else {
        return false;
    }
}

function plugin_getcommenturlid_mediagallery( ) {
    global $_CONF, $_MG_CONF;
    $retval = array();
    $retval[] = $_MG_CONF['site_url'] . '/media.php';
    $retval[] = 's';
    return $retval;
}

/**
 * Plugin function to display a specific comment thread
 * $id      Unique idenifier for item comment belongs to
 * $cid     Comment id to display (possibly including sub-comments)
 * $title   Page/comment title
 * $order   'ASC' or 'DSC' or blank
 * $format  'threaded', 'nested', or 'flat'
 * $page    Page number of comments to display
 * $view    True to view comment (by cid), false to display (by $pid)
 */
function plugin_displaycomment_mediagallery($id,$commentid,$title,$order,$format,$page,$view) {
    global $_CONF, $_USER, $_MG_CONF, $LANG_LOGIN;

    if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $_MG_CONF['loginrequired'] == 1) {
        $display = MG_siteHeader();
        $display .= COM_startBlock ($LANG_LOGIN[1], '',
                  COM_getBlockTemplate ('_msg_block', 'header'));
        $login = new Template($_CONF['path_layout'] . 'submit');
        $login->set_file (array ('login'=>'submitloginrequired.thtml'));
        $login->set_var ('login_message', $LANG_LOGIN[2]);
        $login->set_var ('site_url', $_CONF['site_url']);
        $login->set_var ('lang_login', $LANG_LOGIN[3]);
        $login->set_var ('lang_newuser', $LANG_LOGIN[4]);
        $login->parse ('output', 'login');
        $display .= $login->finish ($login->get_var('output'));
        $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
        $display .= MG_siteFooter();
        echo $display;
        exit;
    }

    $retval = '';
    require_once $_CONF['path'] . 'plugins/mediagallery/include/classAlbum.php';
    require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-media.php';
    list($ptitle,$retval,$themeCSS,$album_id) =  MG_displayMediaImage( $id, 0, 0, 0 );

    $retval = $themeCSS . $retval;
    if (SEC_hasRights('mediagallery.admin')){
        $delete_option = true;
    } else
        $delete_option = false;

    if ($view == 1) {
        $retval .= CMT_userComments ($id, $title, 'mediagallery',$order,$format,$commentid,$page,true,$delete_option);
    } else {
        $retval .= CMT_userComments ($id, $title, 'mediagallery',$order,$format,$commentid,$page,false,$delete_option);
    }
    return $retval;
}


/**
* Returns the items for this plugin that should appear on the main menu
*
* NOTE: this MUST return the url/value pairs in the following format
* $<arrayname>[<label>] = <url>
*
* Universal Plugin:  Uncomment and fill in variables;
*
* @return    array   Associative array of menu items to be added to menu
*
*/

function plugin_getmenuitems_mediagallery() {
    global $_CONF, $_USER, $_MG_CONF;

    $menulabel = $_MG_CONF['menulabel'];
    $menurul   = $_MG_CONF['site_url'] . "/index.php";     // Menu url here

    $menuitems = array();
    $menuitems["$menulabel"] = $menurul;

    return $menuitems;
}

function plugin_getheadercss_mediagallery() {
    global $_CONF, $_MG_CONF, $mgLightBox, $glversion,$swfjsinclude,$themeStyle;

    $styles = array();

    if ( $_MG_CONF['template_path'] == $_CONF['path'] . 'plugins/mediagallery/templates' ) {
	    $styleFile = $_MG_CONF['path_html'] . 'style.css';
	} else {
		$styleFile = $_CONF['layout_path'] . '/mediagallery/style.css';
	}
	$styles[] = $styleFile;

    // check for random skin css
    $cacheInstance = 'mgrs' . $_MG_CONF['random_skin'] . '__' . $_CONF['theme'];
    $retval = CACHE_check_instance($cacheInstance, 0);
    if ( $retval !== false) {
        $styles[] = CACHE_instance_filename($cacheInstance,0);
    } else {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/classFrame.php';
        $nFrame = new mgFrame();
        $nFrame->constructor( $_MG_CONF['random_skin'] );
        $fCSS= $nFrame->getCSS();
        CACHE_create_instance($cacheInstance, $fCSS, 0);
        $styles[] = CACHE_instance_filename($cacheInstance,0);
    }

	return ($styles);
}

function plugin_getheaderjs_mediagallery() {
    global $_CONF, $_MG_CONF, $mgLightBox, $glversion,$swfjsinclude,$themeStyle;

    $js = array();

    $js[] = $_MG_CONF['path_html'] . 'js/swfobject.js';
    $js[] = $_MG_CONF['path_html'] . 'js/mediagallery.js';
    if ( $_MG_CONF['disable_lightbox'] != true ) {
        $js[] = $_MG_CONF['path_html'] . 'js/slimbox.js';
    }
    $js[] = $_MG_CONF['path_html'] . 'js/rating.js';
    $js[] = $_MG_CONF['path_html'] . 'js/slideshow.js';

	return ($js);
}

function plugin_getglobaljs_mediagallery() {
    global $_MG_CONF, $_CONF;

    $globalJS = array();
    $globalJS['mgBaseURL'] = $_MG_CONF['site_url'];

    return($globalJS);
}

function plugin_getheadercode_mediagallery() {
    global $_CONF, $_MG_CONF, $mgLightBox, $glversion,$swfjsinclude,$themeStyle;

    $mg_header = '';

    if ( $themeStyle != '' ) {
        $mg_header .= $themeStyle;
    }
    return $mg_header;
}

/**
  * Whats New Block API Support
  * Return the Headline and Byline for the new section in the Whatsnew Block
*/
function plugin_whatsnewsupported_mediagallery() {
    global $_CONF, $_MG_CONF, $LANG_MG00,$mg_installed_version;


    if ( $_MG_CONF['whatsnew'] == 0 ) {
        return '';
    }

    if ( $mg_installed_version != $_MG_CONF['pi_version'] ) {
        return '';
    }

    $label = sprintf($LANG_MG00['whatsnew_prompt'],$_MG_CONF['whatsnew_time']);

    return array($LANG_MG00['whatsnew_title'],$label);
}
/**
  * API function provides the content of our "What's New" feed
*/
function plugin_getwhatsnew_mediagallery() {
    global $MG_albums, $_TABLES, $_CONF, $_MG_CONF, $_USER, $LANG_MG03, $glversion,$mg_installed_version;

    $retval = '';

    if ($_MG_CONF['whatsnew'] == 0 ) {
        return;
    }

    if ( $mg_installed_version != $_MG_CONF['pi_version'] ) {
        return;
    }

    $newMediaCount    = 0;
    $newCommentCount = 0;

    $items = array();

    $newAge = time() - ($_MG_CONF['whatsnew_time'] * 86400);

    $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE last_update >= " . $newAge . " ORDER BY last_update DESC";
    $result = DB_query($sql,1);
    while ($row = DB_fetchArray($result)) {
        $aid = $row['album_id'];

        if ( $MG_albums[$aid]->access > 0 && $MG_albums[$aid]->hidden != 1 && $MG_albums[$aid]->getOffset() != -1) {
            $title = $MG_albums[$aid]->title;
            if ( strlen($title) > $_MG_CONF['title_length'] ) {
                $dTitle = substr($title,0,$_MG_CONF['title_length'] - 3) . '...';
            } else {
                $dTitle = $title;
            }
            $items[] = '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $aid . '">' . $dTitle . '</a>';
            $newMediaCount++;
        }

    }
    if ( $newMediaCount == 0 ) {
        $retval .= $LANG_MG03['no_new_items'] . '<br' . XHTML . '>' . LB;
    }
    if ( $_MG_CONF['disable_whatsnew_comments'] == 0 ) {
         $sql = "SELECT * FROM {$_TABLES['comments']} AS c LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON c.sid=ma.media_id LEFT JOIN {$_TABLES['mg_albums']} AS a ON ma.album_id=a.album_id WHERE c.date >=  DATE_SUB(NOW(), INTERVAL " . $_MG_CONF['whatsnew_time'] . " DAY ) AND c.type='mediagallery' GROUP BY c.sid ORDER BY c.date DESC LIMIT 15";

        $result = DB_query( $sql,1);
        $nrows2 = DB_numRows( $result );
        if( $nrows2 == 0 ) {
            $retval .= '' . '<br' . XHTML . '>' . LB;
        } else {
            while ($A = DB_fetchArray($result)) {
                $access = SEC_hasAccess ($A['owner_id'],
                                         $A['group_id'],
                                         $A['perm_owner'],
                                         $A['perm_group'],
                                         $A['perm_members'],
                                         $A['perm_anon']);
                if ( $access > 0 ) {
                    $titleLength = $_MG_CONF['title_length'];        // Compensate for the added HTML
                    $title = "<b>C:</b>&nbsp;".$A['title'];
                    $str = "<a href=\"{$_CONF['site_url']}/comment.php?mode=view&amp;cid={$A['cid']}&amp;sid={$A['sid']}&amp;type=mediagallery\">";
                    $str .= substr($title,0,$titleLength);
                    $str .= '</a>';
                    $items[] = $str;
                    $newCommentCount++;
                }
            }
        }
        if ( $newCommentCount == 0 ) {
            $retval .= $LANG_MG03['no_comments'] . '<br' . XHTML . '>' . LB;
        }
    }
    if ( $newMediaCount == 0 && $newCommentCount == 0 ) {
        return $retval;
    } else {
        return $items;
    }
}

/**
* Shows the statistics for the plugin on stats.php.  If
* $showsitestats is 1 then we are to only print the overall stats in the 'site
* statistics' box otherwise we show the detailed stats for the plugin
*
* Universal Plugin:  Modify/uncomment use it
*
* @param    int showsitestate   Flag to let us know which stats to get
* @return   string  returns formatted HTML to insert in stats page
*
*/
function plugin_showstats_mediagallery($showsitestats) {
    global $_CONF, $_MG_CONF, $_TABLES, $LANG_MG00, $MG_albums, $_USER, $glversion;

    if ( (!isset($_USER['uid']) || $_USER['uid'] < 2 ) && $_MG_CONF['loginrequired'] == 1) {
        return;
    }
    $previd = -1;
    $loopcounter = 0;

    $retval='';
    if ($showsitestats == 1) {
        // This shows in the summary box
        $total_pages=DB_count($_TABLES['mg_media']);
        $summary_label = $LANG_MG00['items_in'] . ' ' .  $LANG_MG00['plugin'];

        $retval = "<table border = '0' width='100%' cellspacing='0' cellpadding='0'>";
        $retval .= "<tr><td>$summary_label</td>";
        $retval .= "<td align='right'>" . $total_pages . "&nbsp;&nbsp </td></tr></table>";
    } else {

        $header_arr = array(
            array('text' => $LANG_MG00['media_col_header'], 'field' => 'title','header_class' => 'stats-header-title'),
            array('text' => $LANG_MG00['rating'], 'field' => 'rating','field_class' => 'stats-list-count'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     => false,
                          'title'        => $LANG_MG00['stats_rate_title'],
        );
        $sql = "SELECT a.album_id,m.media_original_filename,m.media_title,m.media_id,m.media_rating FROM {$_TABLES['mg_albums']} as a LEFT JOIN {$_TABLES['mg_media_albums']} as ma
                on a.album_id=ma.album_id LEFT JOIN {$_TABLES['mg_media']} as m on ma.media_id=m.media_id WHERE
                m.media_rating <> 0 AND a.hidden=0 " . COM_getPermSQL('and') . " ORDER BY m.media_rating DESC LIMIT 10";

        $result = DB_query($sql);
        $numrows = DB_numRows($result);
        if ($numrows > 0) {
            for ($i = 0; $i < $numrows && $i < 10; $i++) {
                $A = DB_fetchArray($result);
                $aid = $A['album_id'];
                if ( $A['media_title'] == '' || $A['media_title'] == " ") {
                    if ( $A['media_original_filename'] == '' ) {
                        $title = '<b>' . $LANG_MG00['album'] . '</b>' . '<em>' . strip_tags($MG_albums[$aid]->title) . ' - ' . $LANG_MG00['no_title'] . '</em>';
                    } else {
                        $title = $A['media_original_filename'];
                    }
                } else {
                     $title = strip_tags($A['media_title']);
                }

                $S['title'] = '<a href="' . $_MG_CONF['site_url'] . '/media.php?s=' . $A['media_id'] . '">' . $title . '</a>';
//                $S['rating']  = $A['media_rating'] / 2  . '/5.00';
                $S['rating'] = @number_format($A['media_rating']/2,2) . '/5.00';
                $data_arr[$i] = $S;
            }
            $retval .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
        }

        $loopcounter = 0;
        $previd = '';

        $header_arr = array(
            array('text' => $LANG_MG00['media_col_header'], 'field' => 'title','header_class' => 'stats-header-title'),
            array('text' => $LANG_MG00['hitsmsg'], 'field' => 'views','field_class' => 'stats-list-count'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     => false,
                          'title'        => $LANG_MG00['stats_title'],
        );
        $sql    =  "SELECT DISTINCT m.media_id,m.media_title,ma.album_id,m.media_original_filename,m.media_views
                    FROM " . $_TABLES['mg_media'] . " as m" .
                    " LEFT JOIN " . $_TABLES['mg_media_albums'] . " as ma" .
                    " ON m.media_id=ma.media_id " .
                    " WHERE m.media_views > 0 " .
                    " ORDER BY m.media_views DESC LIMIT 10";
        $result  = DB_query($sql);
        $numrows = DB_numRows($result);
        if ($numrows > 0) {
            for ($i = 0; $i < $numrows; $i++) {
                $A = DB_fetchArray($result);
                if ( $A['media_id'] == $previd )
                    continue;
                $aid = $A['album_id'];
                $previd = $A['media_id'];
                if ( $A['media_id'] != '' && $MG_albums[$aid]->access > 0 ) {
                    $loopcounter++;

                    if ( $A['media_title'] == '' || $A['media_title'] == " ") {
                        if ( $A['media_original_filename'] == '' ) {
                            $title = '<b>' . $LANG_MG00['album'] . '</b>' . '<em>' . strip_tags($MG_albums[$aid]->title) . ' - ' . $LANG_MG00['no_title'] . '</em>';
                        } else {
                            $title = $A['media_original_filename'];
                        }
                    } else {
                         $title = strip_tags($A['media_title']);
                    }
                    $S['title'] = '<a href="' . $_MG_CONF['site_url'] . '/media.php?s=' . $A['media_id'] . '">' . $title . '</a>';
                    $S['views']  = $A['media_views'];
                    $data_arr[$loopcounter] = $S;
                }
                if ( $loopcounter > 10 )
                    break;
            }
            $retval .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
        }
    }
    return $retval;
}

function plugin_statssummary_mediagallery() {
    global $_CONF, $_MG_CONF, $_TABLES, $LANG_MG00, $_USER;

    if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $_MG_CONF['loginrequired'] == 1) {
        return;
    }

    // This shows in the summary box
    $total_items=DB_count($_TABLES['mg_media']);
    $summary_label = $LANG_MG00['items_in'] . ' ' .  $LANG_MG00['plugin'];
    $retval[] = $summary_label;
    $retval[] = $total_items;
    return $retval;
}



/**
* This will put an option for the plugin in the command and control block on moderation.php
*
* Add the plugin name, icon and link to the command and control block in moderation.php
*
* @return   array   Array containing (plugin name, admin url, url of plugin icon)
*
*/

function plugin_cclabel_mediagallery() {
    global $_CONF, $_MG_CONF, $LANG_MG00;

    if (SEC_hasRights('mediagallery.config') ) {
        return array($LANG_MG00['plugin'],
            $_MG_CONF['admin_url'] . 'index.php',
            MG_getImageFile('mediagallery.png'));
    } else {
        return '';
    }
}

/**
* Returns the administrative option for this plugin
*
* Adds the plugin to the Admin menu
*
* Universal Plugin:  Modify/Uncomment use it
*
* @return   array   Array containing (plugin name, plugin admin url, # of items in plugin or '')
*
*/
function plugin_getadminoption_mediagallery() {
    global $_CONF, $_TABLES, $LANG_MG00, $_MG_CONF;

    if (SEC_hasRights('mediagallery.config')) {
        return array($LANG_MG00['plugin'], $_MG_CONF['admin_url'] . 'index.php', DB_count($_TABLES['mg_albums']));
    }
}


function plugin_autotags_mediagallery ($op, $content = '', $autotag = '') {
    global $MG_albums, $_MG_CONF, $_CONF, $_MG_USERPREFS, $_TABLES, $LANG_MG00, $LANG_MG03, $side_count, $swfjsinclude;
    global $mgAutoTagArray, $mg_installed_version,$glversion;

    static $ss_count = 0;

    if ( $mg_installed_version != $_MG_CONF['pi_version'] ) {
        return $content;
    }
    if ($op == 'tagname' ) {
        return array('album','media','img','slideshow','fslideshow','video','audio','download','image','oimage','mlink','playall');
    } else if ($op == 'parse') {
        /*
         * Process the auto tag to remove any embedded &nbsp;
         */
        $tag = str_replace('&nbsp;',' ',$autotag['tagstr']);
        $parms = explode (' ', $tag);
        // Extra test to see if autotag was entered with a space
        // after the module name
        if (substr ($parms[0], -1) == ':') {
            $startpos = strlen ($parms[0]) + strlen ($parms[1]) + 2;
            $label = str_replace (']', '', substr ($tag, $startpos));
            $tagid = $parms[1];
        } else {
            $label = str_replace (']', '',
                     substr ($tag, strlen ($parms[0]) + 1));
            $parms = explode (':', $parms[0]);
            if (count ($parms) > 2) {
                // whoops, there was a ':' in the tag id ...
                array_shift ($parms);
                $tagid = implode (':', $parms);
            } else {
                $tagid = $parms[1];
            }
        }
        $autotag['parm1'] = str_replace(']','',$tagid);
        $autotag['parm2'] = $label;
        /*
         * end of tag replacement
         */

        $T = new Template($_MG_CONF['template_path']);
        // see if we have an alignment option included
        $caption = $autotag['parm2'];
        $aSet = 0;
        $skip = 0;

        // default values for parameters
        $border         = $_MG_CONF['at_border'];
        $align          = $_MG_CONF['at_align'];
        $width          = $_MG_CONF['at_width'];
        $height         = $_MG_CONF['at_height'];
        $src            = $_MG_CONF['at_src'];
        $autoplay       = $_MG_CONF['at_autoplay'];
        $enable_link    = $_MG_CONF['at_enable_link'];
        $delay          = $_MG_CONF['at_delay'];
        $transition     = 'Fade';
        $showtitle      = $_MG_CONF['at_showtitle'];
        $destination    = 'content';
        $target         = '';
        $linkID         = 0;
        $alt            = 0;

        if ( $align != '' ) {
            $aSet = 1;
        }

        // parameter processing - logic borrowed from
        // Dirk Haun's Flickr plugin

        $px = explode (' ', trim ($autotag['parm2']));
        if (is_array ($px)) {
            foreach ($px as $part) {
                if (substr ($part, 0, 6) == 'width:') {
                    $a = explode (':', $part);
                    $width = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 7) == 'height:') {
                    $a = explode (':', $part);
                    $height = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 7) == 'border:') {
                    $a = explode (':', $part);
                    $border = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 6) == 'align:') {
                    $a = explode(':', $part);
                    $align = $a[1];
                    $skip++;
                    $aSet = 1;
                } elseif (substr($part,0,4) == 'src:') {
                    $a = explode(':', $part);
                    $src = $a[1];
                    $skip++;
                } elseif (substr($part,0,9) == 'autoplay:') {
                    $a = explode(':', $part);
                    $autoplay = $a[1];
                    $skip++;
                } elseif (substr($part,0,5) == 'link:') {
                    $a = explode(':',$part);
                    $enable_link = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 6) == 'delay:') {
                    $a = explode (':', $part);
                    $delay = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 11) == 'transition:') {
                    $a = explode (':', $part);
                    $transition = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 6) == 'title:' ) {
                    $a = explode (':',$part);
                    $showtitle = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 5) == 'dest:') {
                    $a = explode (':', $part);
                    $destination = $a[1];
                    $skip++;
                    if ( $destination != 'content' && $destination != 'block' ) {
                        $destination = 'content';
                    }
                } elseif ( substr($part,0,7) == 'linkid:' ) {
                    $a = explode (':',$part);
                    $linkID = $a[1];
                    $skip++;
                } elseif ( substr($part,0,4) == 'alt:' ) {
                    $a = explode (':',$part);
                    $alt = $a[1];
                    $skip++;
                } elseif ( substr($part,0,7) == 'target:' ) {
                    $a = explode (':',$part);
                    $target = $a[1];
                    $skip++;
                } else {
                    break;
                }
            }

            if ($skip != 0) {
                if (count ($px) > $skip) {
                    for ($i = 0; $i < $skip; $i++) {
                        array_shift ($px);
                    }
                    $caption = trim (implode (' ', $px));
                } else {
                    $caption = '';
                }
            }
        } else {
            $caption = trim ($autotag['parm2']);
        }

        if ( !is_numeric($autotag['parm1'][0]) ) {
            switch ($autotag['parm1'][0] ) {
                case 'n' :
                    $align = '';
                    $aSet = 1;
                    break;
                case 'c' :
                    $align="center";
                    $aSet = 1;
                    break;
                case 'l' :
                    $align = "left";
                    $aSet = 1;
                    break;
                case 'r' :
                    $align = "right";
                    $aSet = 1;
                    break;
                case 'a' :
                    $align=(!($side_count % 2) ? "left" : "right" );
                    $side_count++;
                    $aSet = 1;
                    break;
                default :
                    $align=(!($side_count % 2) ? "left" : "right" );
                    $side_count++;
                    break;
            }
            $parm1 = COM_applyFilter(substr($autotag['parm1'],1,strlen($autotag['parm1'])-1));
        } else {
            $parm1 = COM_applyFilter($autotag['parm1']);
            if ( $aSet == 0 || $align == 'auto') {
                $align=(!($side_count % 2) ? "left" : "right" );
                $side_count++;
            }
        }
        if ( $align == 'none' ) {
            $align = '';
        }
        // sanity check incase the album has been deleted or something...
        if ( $autotag['tag'] != 'media' && $autotag['tag'] != 'image' && $autotag['tag'] != 'video' && $autotag['tag'] != 'audio' && $autotag['tag'] != 'download' && $autotag['tag'] != 'oimage' && $autotag['tag'] != 'img' && $autotag['tag'] != 'mlink' && $autotag['tag'] != 'playall') {
            if ( !isset($MG_albums[$parm1]->id) ) {
                $link = '';
                $content = str_replace ($autotag['tagstr'], $link, $content);
                return $content;
            }
        }
        $ss_count = mt_rand(0,32768);
        switch( $autotag['tag'] ) {
            case 'download' :
                $side_count--;
                $sql = "SELECT ma.album_id FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . addslashes($parm1) . "'";
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
                    if ( !isset($MG_albums[$aid]->id) || $MG_albums[$aid]->access == 0 ) {
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                        return $content;
                    }
                    $link = '<a href="' . $_MG_CONF['site_url'] . '/download.php?mid=' . $parm1 . '">';
                    if ( $caption != "" ) {
                        $link .= $caption;
                    } else {
                        $link .= 'download';
                    }
                    $link .= '</a>';
                    if ( $destination != 'block' ) {
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    } else {
                        $autoTagCount = $mgAutoTagArray['count'];
                        $mgAutoTagArray['tags'][$autoTagCount] = $link;
                        $mgAutoTagArray['count']++;
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    }
                    return $content;
                } else {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                break;
            case 'mlink' :
                $side_count--;
                $sql = "SELECT m.remote_url,ma.album_id FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . addslashes($parm1) . "'";
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
                    if ( !isset($MG_albums[$aid]->id) || $MG_albums[$aid]->access == 0 ) {
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                        return $content;
                    }
                    if ( $alt == 1 && $row['remote_url'] != '' ) {
                        $link = '<a href="' . $row['remote_url'] . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>';
                    } else {
                        $link = '<a href="' . $_MG_CONF['site_url'] . '/media.php?f=0&amp;sort=0&amp;s=' . $parm1 . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>';
                    }
                    if ( $caption != "" ) {
                        $link .= $caption;
                    } else {
                        $link .= $LANG_MG03['click_here'];
                    }
                    $link .= '</a>';
                    if ( $destination != 'block' ) {
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    } else {
                        $autoTagCount = $mgAutoTagArray['count'];
                        $mgAutoTagArray['tags'][$autoTagCount] = $link;
                        $mgAutoTagArray['count']++;
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    }
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                } else {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                break;
            case 'playall' :
               if ( !isset($MG_albums[$parm1]->id) || $MG_albums[$parm1]->access == 0 ) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                $V = new Template( MG_getTemplatePath(0) );
                $V->set_file (array ('xspf' => 'xspf_radio.thtml'));
                $V->set_var(array(
                	'aid'				=> $parm1,
                	'site_url'			=> $_MG_CONF['site_url'],
                	'autoplay'			=> $autoplay ? 'play' : 'stop',
                	'id'				=> 'mp3radio' . rand(),
                	'id2'				=> 'mp3radio' . rand(),
                	'xhtml'             => XHTML,
                ));
                $V->parse('output','xspf');
                if ( $align != '' && $align != "center") {
                    $link = '<span style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output')) . '</span>';
                } else if ($align == "center") {
                    $link = '<span style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output')) . '</span>';
                } else {
                    $link = '<span style="padding:5px;">' . $V->finish($V->get_var('output')) . '</span>';
                }
                if ( $destination != 'block' ) {
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $autoTagCount = $mgAutoTagArray['count'];
                    $mgAutoTagArray['tags'][$autoTagCount] = $link;
                    $mgAutoTagArray['count']++;
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                }
                $content = str_replace ($autotag['tagstr'], $link, $content);
                return $content;
                break;
            case 'video' :
                $sql = "SELECT ma.album_id,m.media_id,m.mime_type,m.remote_url,m.media_filename,m.media_mime_ext,m.media_original_filename,m.media_tn_attached,m.media_resolution_x,m.media_resolution_y,m.remote_media FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . addslashes($parm1) . "'";
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
                    if ( !isset($MG_albums[$aid]->id) || $MG_albums[$aid]->access == 0 ) {
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                        return $content;
                    }
                    // determine height / width and aspect
                    if ( $width === 'auto' && $row['media_resolution_x'] > 0 && $row['media_resolution_y'] > 0 ) {
                        $videoheight = $row['media_resolution_y'];
                        $videowidth  = $row['media_resolution_x'];
                    } else {
                        if ( $row['media_resolution_x'] > 0 && $row['media_resolution_y'] > 0 ) {
                            if ( $row['media_resolution_x'] >= $row['media_resolution_y'] ) {
                                // landscape
                                $ratio = $row['media_resolution_y'] / $row['media_resolution_x'];
                                $orientation = 0;
                            } else {
                                // portrait
                                $ratio = $row['media_resolution_x'] / $row['media_resolution_y'];
                                $orientation = 1;
                            }
                        } else {
                            $ratio =  0.75;
                            $orientation = 0;
                        }
                        if ( $orientation == 0 ) {
                            if ( $width > 0 && $height == 0 ) {
                                $videoheight = round($width * $ratio);
                                $videowidth  = $width;
                            } else if ( $width == 0 && $height == 0 ) {
                                $videoheight = 200 * $ratio;
                                $videowidth  = 200;
                            } else if ( $width == 0 && $height > 0 ) {
                                $videowidth = round($height / $ratio);
                                $videoheight = $height;
                            } else if ( $width > 0 && $height > 0 ) {
                                $videowidth = $width;
                                $videoheight = $height;
                            }
                        } else {
                            if ( $width > 0 && $height == 0 ) {
                                $videoheight = round($width / $ratio);
                                $videowidth  = $width;
                            } else if ( $width == 0 && $height == 0 ) {
                                $videoheight = 200;
                                $videowidth  = round(200 / $ratio);
                            } else if ( $width == 0 && $height > 0 ) {
                                $videowidth = round($height * $ratio);
                                $videoheight = $height;
                            } else  if ( $width > 0 && $height > 0 ) {
                                $videowidth = $width;
                                $videoheight = $height;
                            }
                        }
                    }
                    switch( $row['mime_type'] ) {
                        case 'embed' :
                            if ( $align != '' && $align != "center") {
                                $link = '<span style="float:' . $align . ';padding:5px;">' . $row['remote_url'] . '</span>';
                            } else if ( $align == 'center' ) {
                                $link = 'span style="text-align:center;padding:5px;">' . $row['remote_url'] . '</span>';
                            } else {
                                $link = '<span style="padding:5px;">' . $row['remote_url'] . '</span>';
                            }
                            if ( $destination != 'block' ) {
                                $content = str_replace ($autotag['tagstr'], $link, $content);
                            } else {
                                $autoTagCount = $mgAutoTagArray['count'];
                                $mgAutoTagArray['tags'][$autoTagCount] = $link;
                                $mgAutoTagArray['count']++;
                                $link = '';
                                $content = str_replace ($autotag['tagstr'], $link, $content);
                            }
                            return $content;
                            break;
                        case 'video/x-ms-asf' :
                        case 'video/x-ms-asf-plugin' :
                        case 'video/avi' :
                        case 'video/msvideo' :
                        case 'video/x-msvideo' :
                        case 'video/avs-video' :
                        case 'video/x-ms-wmv' :
                        case 'video/x-ms-wvx' :
                        case 'video/x-ms-wm' :
                        case 'application/x-troff-msvideo' :
                        case 'application/x-ms-wmz' :
                        case 'application/x-ms-wmd' :
                            $V = new Template( MG_getTemplatePath(0) );
                            $V->set_file (array ('video' => 'view_asf.thtml'));
                            $V->set_var(array(
                                'autostart'         => $autoplay ? 'true' : 'false',
                                'enablecontextmenu' => 'true',
                                'stretchtofit'      => 'false',
                                'showstatusbar'     => 'false',
                                'showcontrols'      => 'true',
                                'showdisplay'       => 'false',
                                'height'            => $videoheight,
                                'width'             => $videowidth,
                                'bgcolor'           => '#FFFFFF',
                                'playcount'         => '9999',
                                'loop'              => 'true',
                                'movie'             => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                                'autostart0'         => $autoplay ? '1' : '0',
                                'enablecontextmenu0' => '1',
                                'stretchtofit0'      => '0',
                                'showstatusbar0'     => '0',
                                'uimode0'            => 'none',
                                'showcontrols0'      => '1',
                                'showdisplay0'       => '0',
                                'xhtml'              => XHTML,
                            ));
                            $V->parse('output','video');
                            if ( $align != '' && $align != "center") {
                                $u_image = '<span style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output')) . '</span>';
                            } else if ( $align == 'center' ) {
                                $u_image = '<span style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output')) . '</span>';
                            } else {
                                $u_image = '<span style="padding:5px;">' . $V->finish($V->get_var('output')) . '</span>';
                            }
                            break;
                        case 'video/mpeg' :
                        case 'video/x-motion-jpeg' :
                        case 'video/quicktime' :
                        case 'video/mpeg' :
                        case 'video/x-mpeg' :
                        case 'video/x-mpeq2a' :
                        case 'video/x-qtc' :
                        case 'video/x-m4v' :
                            $V = new Template( MG_getTemplatePath(0) );
                            $V->set_file (array ('video' => 'view_quicktime.thtml'));
                            $V->set_var(array(
                                'autoref'       => 'true',
                                'autoplay'      => $autoplay ? 'true' : 'false',
                                'controller'    => 'true',
                                'kioskmode'     => 'true',
                                'scale'         => 'aspect',
                                'height'        => $videoheight,
                                'width'         => $videowidth,
                                'bgcolor'       => '#F0F0F0',
                                'loop'          => 'true',
                                'movie'         => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                                'xhtml'         => XHTML,
                            ));
                            $V->parse('output','video');
                            if ( $align != '' && $align != "center" ) {
                                $u_image = '<div style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            } else if ( $align == 'center' ) {
                                $u_image = '<div style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            } else {
                                $u_image = '<div style="padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            }
                            break;
                        case 'application/x-shockwave-flash' :
                            // set the default playback options...
                            $playback_options['play']    = $_MG_CONF['swf_play'];
                            $playback_options['menu']    = $_MG_CONF['swf_menu'];
                            $playback_options['quality'] = $_MG_CONF['swf_quality'];
                            $playback_options['height']  = $_MG_CONF['swf_height'];
                            $playback_options['width']   = $_MG_CONF['swf_width'];
                            $playback_options['loop']    = $_MG_CONF['swf_loop'];
                            $playback_options['scale']   = $_MG_CONF['swf_scale'];
                            $playback_options['wmode']   = $_MG_CONF['swf_wmode'];
                            $playback_options['allowscriptaccess'] = $_MG_CONF['swf_allowscriptaccess'];
                            $playback_options['bgcolor']    = $_MG_CONF['swf_bgcolor'];
                            $playback_options['swf_version'] = $_MG_CONF['swf_version'];
                            $playback_options['flashvars']   = $_MG_CONF['swf_flashvars'];

                            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . $row['media_id'] . "'");
                            while ( $poRow = DB_fetchArray($poResult) ) {
                                $playback_options[$poRow['option_name']] = $poRow['option_value'];
                            }

                            if ( $swfjsinclude > 0 ) {
                                $u_image = '';
                            } else {
                                $S = new Template( MG_getTemplatePath(0) );
                                $S->set_file(array('swf' => 'swfobject.thtml'));
                                $S->set_var(array(
                                    'site_url'  => $_MG_CONF['site_url'],
                                ));
                                $S->parse('output','swf');
                                $u_image = $S->finish($S->get_var('output'));
                                $swfjsinclude++;
                            }

                            $V = new Template( MG_getTemplatePath(0) );
                            $V->set_file (array ('video' => 'view_swf.thtml'));
                            $V->set_var(array(
                                'site_url'  => $_MG_CONF['site_url'],
                                'lang_noflash' => $LANG_MG03['no_flash'],
                                'play'      => ($autoplay ? 'true' : 'false'),
                                'menu'      => ($playback_options['menu'] ? 'true' : 'false'),
                                'loop'      => ($playback_options['loop'] ? 'true' : 'false'),
                                'scale'     => $playback_options['scale'],
                                'wmode'     => $playback_options['wmode'],
                                'flashvars' => $playback_options['flashvars'],
                                'quality'   => $playback_options['quality'],
                                'height'    => $videoheight,
                                'width'     => $videowidth,
                                'asa'       => $playback_options['allowscriptaccess'],
                                'bgcolor'   => $playback_options['bgcolor'],
                                'swf_version' => $playback_options['swf_version'],
                                'filename'  => $row['media_original_filename'],
                                'id'        => $row['media_filename'] . rand(),
                                'id2'       => $row['media_filename'] . rand(),
                                'movie'     => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                                'xhtml'     => XHTML,
                            ));
                            $V->parse('output','video');
                            if ( $align != '' && $align != "center") {
                                $u_image = '<div style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output'))  . '</div>';
                            } else if ($align == "center") {
                                $u_image = '<div style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output'))  . '</div>';
                            } else {
                                $u_image = '<div style="padding:5px;">' . $V->finish($V->get_var('output'))  . '</div>';
                            }
                            break;
                        case 'video/x-flv' :
				            // set the default playback options...
				            $playback_options['play']    = $_MG_CONF['swf_play'];
				            $playback_options['menu']    = $_MG_CONF['swf_menu'];
				            $playback_options['quality'] = $_MG_CONF['swf_quality'];
				            $playback_options['height']  = $_MG_CONF['swf_height'];
				            $playback_options['width']   = $_MG_CONF['swf_width'];
				            $playback_options['loop']    = $_MG_CONF['swf_loop'];
				            $playback_options['scale']   = $_MG_CONF['swf_scale'];
				            $playback_options['wmode']   = $_MG_CONF['swf_wmode'];
				            $playback_options['allowscriptaccess'] = $_MG_CONF['swf_allowscriptaccess'];
				            $playback_options['bgcolor']    = $_MG_CONF['swf_bgcolor'];
				            $playback_options['swf_version'] = $_MG_CONF['swf_version'];
				            $playback_options['flashvars']   = $_MG_CONF['swf_flashvars'];

				            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . $row['media_id'] . "'");
				            while ( $poRow = DB_fetchArray($poResult) ) {
				                $playback_options[$poRow['option_name']] = $poRow['option_value'];
				            }
				            if ( $swfjsinclude > 0 ) {
				                $u_image = '';
				            } else {
				                $S = new Template( MG_getTemplatePath(0) );
				                $S->set_file(array('swf' => 'swfobject.thtml'));
				                $S->set_var(array(
				                    'site_url'  => $_MG_CONF['site_url'],
				                ));
				                $S->parse('output','swf');
				                $u_image = $S->finish($S->get_var('output'));
				                $swfjsinclude++;
				            }

				            $V = new Template( MG_getTemplatePath(0) );
				    		$V->set_file('video','view_flv_light.thtml');

				            // now the player specific items.
				    		$F = new Template( MG_getTemplatePath(0) );
				           	$F->set_file(array('player' => 'flvfp.thtml'));
				        	if ( $autoplay == 1 ) {  // auto start
				        		$playButton = '';
				        	} else {
				                if ( $row['media_tn_attached'] == 1 ) {
                                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                            $playImage = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                            break;
                                        }
                                    }
				                } else {
				                	$playImage = MG_getImageFile('blank_blk.jpg');
								}
								$playButton = "{ url: '" . $playImage . "', overlayId: 'play' },";
							}
				            if ( $row['remote_media'] == 1 ) {
					            $urlParts = array();
					            $urlParts = parse_url($row['remote_url']);

					            $pathParts = array();
					            $pathParts = explode('/',$urlParts['path']);

					            $ppCount = count($pathParts);
					            $pPath = '';
					            for ($row=1; $row<$ppCount-1;$row++) {
						            $pPath .= '/' . $pathParts[$row];
					            }
					            $videoFile = $pathParts[$ppCount-1];

						        $pos = strrpos($videoFile, '.');
						        if($pos === false) {
						            $basefilename = $videoFile;
						        } else {
						            $basefilename = substr($videoFile,0,$pos);
						        }
						        $videoFile            = $basefilename;
					           	$streamingServerURL   = "streamingServerURL: '" . $urlParts['scheme'] . '://' . $urlParts['host'] . $pPath . "',";
					           	$streamingServer      = "streamingServer: 'fms',";
				    		} else {
				    			$streamingServerURL   = '';
				    			$streamingServer      = '';
				    			$videoFile            = urlencode($_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
				  			}
				  			$width  = $videowidth;
				  			$height = $videoheight + 22;
							$resolution_x = $videowidth;
							$resolution_y = $videoheight;
				            $id  = 'id_'  . rand();
				            $id2 = 'id2_' . rand();
				            $F->set_var(array(
				                'site_url'  		=> $_MG_CONF['site_url'],
				                'lang_noflash' 		=> $LANG_MG03['no_flash'],
				                'play'          	=> ($autoplay ? 'true' : 'false'),
				                'menu'          	=> ($playback_options['menu'] ? 'true' : 'false'),
				                'loop'          	=> ($playback_options['loop'] ? 'true' : 'false'),
				                'scale'         	=> $playback_options['scale'],
				                'wmode'         	=> $playback_options['wmode'],
				                'width'				=> $width,
				                'height'			=> $height,
					           	'streamingServerURL'=> $streamingServerURL,
					           	'streamingServer'	=> $streamingServer,
					           	'videoFile'			=> $videoFile,
					           	'playButton'		=> $playButton,
				                'id'            	=> $id,
				                'id2'           	=> $id2,
				                'resolution_x'  	=> $resolution_x,
				                'resolution_y'  	=> $resolution_y,
				                'xhtml'             => XHTML,
				            ));
				    		$F->parse('output','player');
				    		$flv_player = $F->finish($F->get_var('output'));

				    		$V->set_var(array(
				                'site_url'  	=> $_MG_CONF['site_url'],
				                'lang_noflash'  => $LANG_MG03['no_flash'],
				                'id'            => $id,
				                'id2'           => $id2,
				                'width'         => $resolution_x,
				                'height'        => $resolution_y,
				                'flv_player'	=> $flv_player,
				                'xhtml'         => XHTML,
							));

                            $V->parse('output','video');
                            if ( $align != '' && $align != "center") {
                                $u_image .= '<span style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output'))  . '</span>';
                            } else if ($align == "center") {
                                $u_image .= '<span style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output'))  . '</span>';
                            } else {
                                $u_image .= '<span style="padding:5px;">' . $V->finish($V->get_var('output'))  . '</span>';
                            }
                            break;
                    }
                    $link = $u_image;
                    if ( $destination != 'block' ) {
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    } else {
                        $autoTagCount = $mgAutoTagArray['count'];
                        $mgAutoTagArray['tags'][$autoTagCount] = $link;
                        $mgAutoTagArray['count']++;
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    }
                    return $content;
                } else {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                break;
            case 'audio' :
                $sql = "SELECT ma.album_id,m.media_title,m.mime_type,m.media_tn_attached,m.media_filename,m.media_mime_ext FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . addslashes($parm1) . "'";
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
                    if ( !isset($MG_albums[$aid]->id) || $MG_albums[$aid]->access == 0 ) {
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                        return $content;
                    }
                    switch( $row['mime_type'] ) {
                        case 'audio/mpeg' :
                            $playback_options['height'] = 50;;
                            $playback_options['width']  = 300;
                            $V = new Template( MG_getTemplatePath(0) );
			                $tfile = 'view_mp3_flv.thtml';
			                $autostart = $autoplay ? 'play' : 'stop';

                            if ( $row['media_tn_attached'] == 1 ) {
                                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                        $u_tn = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                        $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext);
                                        break;
                                    }
                                }
                                $border_width = $media_size_disp[0] + 15;
                                $u_pic = '<img src="' . $u_tn . '" alt="" style="border:none;"' . XHTML . '>';
                                $playback_options['width']  = 200;
                            } else {
                                $u_pic='';
                                $playback_options['width']  = 200;
                            }

                            $V->set_file (array ('audio' => $tfile));
                            $V->set_var(array(
                                'autostart'         => $autostart,
                                'enablecontextmenu' => 'true',
                                'stretchtofit'      => 'false',
                                'showstatusbar'     => 'true',
                                'uimode'            => 'mini',
                                'height'            => $playback_options['height'],
                                'width'             => $playback_options['width'],
                                'bgcolor'           => '#FFFFFF',
                                'loop'              => 'true',
                                'u_pic'             => $u_pic,
                                'title'				=> urlencode($row['media_title']),
                                'id'				=> 'mp3' . rand(),
                                'id2'				=> 'mp3' . rand(),
                                'site_url'			=> $_MG_CONF['site_url'],
                                'movie'             => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                                'xhtml'             =>  XHTML,
                            ));
                            $V->parse('output','audio');
                            if ( $align != '' && $align != "center") {
                                $u_image = '<span style="float:' . $align . ';padding:5px;text-align:center;">' . $V->finish($V->get_var('output')) . '</span>';
                            } else if ($align == "center") {
                                $u_image = '<span style="text-align:center;padding:5px;text-align:center;">' . $V->finish($V->get_var('output')) . '</span>';
                            } else {
                                $u_image = '<span style="padding:5px;text-align:center;">' . $V->finish($V->get_var('output')) . '</span>';
                            }
                            break;
                        case 'audio/x-ms-wma' :
                        case 'audio/x-ms-wax' :
                        case 'audio/x-ms-wmv' :
                            $playback_options['height'] = 50;;
                            $playback_options['width']  = 300;

                            $V = new Template( MG_getTemplatePath(0) );
                            $tfile = 'view_mp3_wmp.thtml';
                            $autostart = $autoplay ? '1' : '0';
                            if ( $row['media_tn_attached'] == 1 ) {
                                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                        $u_tn = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                        $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext);
                                        break;
                                    }
                                }
                                $border_width = $media_size_disp[0] + 15;
                                $u_pic = '<div class=out style="width:' . $border_width . 'px"><div class="in ltin tpin"><img src="' . $u_tn . '" alt=""' . XHTML . '></div></div>';
                                $playback_options['height'] = 50;
                                $playback_options['width']  = 200;
                            } else {
                                $u_pic='';
                                $playback_options['height'] = 50;
                                $playback_options['width']  = 200;
                            }

                            $V->set_file (array ('audio' => $tfile));
                            $V->set_var(array(
                                'autostart'         => $autostart, // $autoplay ? 'true' : 'false',
                                'enablecontextmenu' => 'true',
                                'stretchtofit'      => 'false',
                                'showstatusbar'     => 'true',
                                'uimode'            => 'mini',
                                'height'            => $playback_options['height'],
                                'width'             => $playback_options['width'],
                                'bgcolor'           => '#FFFFFF',
                                'loop'              => 'true',
                                'u_pic'             => $u_pic,
                                'movie'             => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                                'xhtml'             => XHTML,
                            ));
                            $V->parse('output','audio');
                            if ( $align != '' && $align != "center") {
                                $u_image = '<div style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            } else if ($align == "center") {
                                $u_image = '<div style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            } else {
                                $u_image = '<div style="padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            }
                            break;
                    }
                    $link = $u_image;

                    if ( $destination != 'block' ) {
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    } else {
                        $autoTagCount = $mgAutoTagArray['count'];
                        $mgAutoTagArray['tags'][$autoTagCount] = $link;
                        $mgAutoTagArray['count']++;
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    }
                    return $content;
                } else {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                break;
            case 'fslideshow' :
                if ( $parm1 == '' || $parm1 == 0 ) {
                    return $content;
                }
                if ( !isset($MG_albums[$parm1]->id) || $MG_albums[$parm1]->access == 0 ) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }

                if ( $width > 0 && $height == 0 ) {
                    $height = $width * 0.75;
                } else if ( $width == 0 && $height == 0 ) {
                    $height = $width = 200;
                } else if ( $width == 0  && $height > 0 ) {
                    $width = $height * 1.3333;
                }
                // if none of the above, assume height and width both specified.

                if ($caption == '' ) {
                    $caption = $MG_albums[$aid]->title;
                }
                $captionHTML = '<br' . XHTML . '><span style="width:' . $width . 'px;font-style:italic;font-size: smaller;text-indent:0;">' . $caption . '</span>' . LB;
                $ss_count++;

                $T = new Template( MG_getTemplatePath(0) );
                $T->set_file(array('fslideshow' => 'fsat.thtml'));
                $T->set_var(array(
                    'site_url'  => $_MG_CONF['site_url'],
                ));
                $T->set_var(array(
                    'id'            => 'mms' . $ss_count,
                    'id2'           => 'fsid' . $ss_count,
                    'movie'         => $_MG_CONF['site_url'] . '/xml.php?aid=' . $parm1 . '%26src=' . trim($src),
                    'dropshadow'    => 'true',
                    'delay'         => $delay,
                    'nolink'        => ($MG_albums[$parm1]->hidden || $enable_link == 0) ? 'true' : 'false',
                    'showtitle'     => ( $showtitle == 'bottom' || $showtitle == 'top' ) ? '&showTitle=' . $showtitle : '',
                    'width'         => $width,
                    'height'        => $height,
                    'xhtml'         => XHTML,
                ));
                $T->parse('output','fslideshow');
                $swfobject = $T->finish($T->get_var('output'));
                $link = $swfobject . $captionHTML;

                if ( $align != '' && $align != "center") {
                    $link = '<span style="float:' . $align . ';padding:5px;text-align:center;">' . $link . '</span>';
                } else if ($align == "center") {
                    $link = '<center><span style="padding:5px;text-align:center;">' . $link . '</span></center>';
                } else {
                    $link = '<span style="padding:5px;text-align:center;">' . $link . '</span>';
                }
                if ( $destination != 'block' ) {
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $autoTagCount = $mgAutoTagArray['count'];
                    $mgAutoTagArray['tags'][$autoTagCount] = $link;
                    $mgAutoTagArray['count']++;
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                }
                return $content;
                break;
            case 'slideshow' :
                if ( $parm1 == '' || $parm1 == 0 ) {
                    return $content;
                }
                if ( !isset($MG_albums[$parm1]->id) || $MG_albums[$parm1]->access == 0 ) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                if ($caption == '' ) {
                    $caption = $MG_albums[$parm1]->title;
                }
                $T->set_file (array('tag'      => 'autotag_ss.thtml'));
                $aid = $parm1;
                $pics = '';
                $counter = 0;
                $maxwidth = 0;
                $maxheight = 0;
                $ss_count++;
                $sql = "SELECT m.media_filename,m.media_mime_ext,m.remote_url FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                       " ON ma.media_id=m.media_id WHERE ma.album_id=" . addslashes($aid) . " AND m.media_type=0 AND m.include_ss=1";
                $result = DB_query($sql);
                while ($row = DB_fetchArray($result)) {
                    switch ( $src ) {
                        case 'orig' :
                            $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
                            $ext = $row['media_mime_ext'];
                            break;
                        case 'disp' :
                            foreach ($_MG_CONF['validExtensions'] as $tnext ) {
                                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $tnext) ) {
                                    $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $tnext);
                                    $ext = substr($tnext,1,3);
                                    break;
                                }
                            }
                            break;
                        default :
                            foreach ($_MG_CONF['validExtensions'] as $tnext ) {
                                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $tnext) ) {
                                    $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $tnext);
                                    $ext = substr($tnext,1,3);
                                    break;
                                }
                            }
                            $src = 'tn';
                            break;
                    }
                    if ( $media_size == false ) {
                        continue;
                    }

                    $counter++;
                    if ( $width > 0 && $height == 0 ) {
                        if ( $media_size[0] > $media_size[1] ) {        // landscape
                            $ratio = $media_size[0] / $width;
                            $newwidth = $width;
                            $newheight = round($media_size[1] / $ratio);
                        } else {    // portrait
                            $ratio = $media_size[1] / $width;
                            $newheight = $width;
                            $newwidth = round($media_size[0] / $ratio);
                        }
                    } else if ( $width == 0 && $height == 0 ) {
                        if ( $media_size[0] > $media_size[1] ) {        // landscape
                            $ratio = $media_size[0] / 200;
                            $newwidth = 200;
                            $newheight = round($media_size[1] / $ratio);
                        } else {    // portrait
                            $ratio = $media_size[1] / 200;
                            $newheight = 200;
                            $newwidth = round($media_size[0] / $ratio);
                        }
                    } else if ( $width == 0 && $height > 0 ) {
                        if ( $height > $media_size[1] ) {
                            $newheight = $media_size[1];
                            $newwidth = $media_size[0];
                        } else {
                            $ratio = $height / $media_size[1];
                            $newheight = $height;
                            $newwidth = round($media_size[0] * $ratio);
                        }
                    } else {
                        $newwidth = $width;
                        $newheight = $height;
                    }

                    if ( $newheight > $maxheight ) {
                        $maxheight = $newheight;
                    }
                    if ( $newwidth > $maxwidth ) {
                        $maxwidth  = $newwidth;
                    }
                    if ( $MG_albums[$parm1]->hidden == 1 || $enable_link == 0 ) {
                        $pics .= '<img class="slideshowThumbnail' . $ss_count . '" src="' . $_MG_CONF['mediaobjects_url'] . '/' . $src . '/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $ext . '" alt="" style="width:' . $newwidth . 'px;height:' . $newheight . 'px;border:none;position:absolute;left:0px;top:0px;"' . XHTML . '>' . LB;
                    } else {
                        $pics .= '<img class="slideshowThumbnail' . $ss_count . '" src="' . $_MG_CONF['mediaobjects_url'] . '/' . $src . '/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $ext . '" alt="" style="width:' . $newwidth . 'px;height:' . $newheight . 'px;border:none;position:absolute;left:0px;top:0px;"' . XHTML . '>' . LB;
                    }
                }
                if ( $delay <= 0 ) {
                    $delay = 10;
                }
                if ( $MG_albums[$parm1]->hidden == 1 || $enable_link == 0 ) {
                    $ss_url = '';
                } else {
                    $ss_url = '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $aid . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>';
                    $ss_url = $_MG_CONF['site_url'] . '/album.php?aid=' . $aid;
                }

                if ( $counter != 0 ) {
                    $T->set_var(array(
                        'align'     => $align,
                        'pics'      => $pics,
                        'caption'   => $caption,
                        'maxheight' => $maxheight,
                        'maxwidth'  => $maxwidth,
                        'width'     => $maxwidth,
                        'framewidth' => $maxwidth + 10,
                        'ss_count'  => $ss_count,
                        'delay'     => $delay * 1000,
                        'border'    => $border ? 'border: silver solid;border-width: 1px;' : '',
                        'xhtml'      => XHTML,
                        'sslink'     => $ss_url,
                    ));
                    if ( $align == 'left' || $align == 'right' ) {
                        $T->set_var('float','float: ' . $align . ';');
                    } else {
                        $T->set_var('float','float:left;');
                        $align = 'left';
                    }
                    if ( $align == 'left' ) {
                        $T->set_var('margin-right','margin-right:15px;');
                    } else {
                        $T->set_var('margin-right','');
                    }
                    $T->parse('output','tag');
                    $link = $T->finish($T->get_var('output'));
                } else {
                    $link = '';
                }
/*
                if ( $align == 'center' ) {
                    $link = '<center>' . $link . '</center>';
                }
*/
                if ( $destination != 'block' ) {
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $autoTagCount = $mgAutoTagArray['count'];
                    $mgAutoTagArray['tags'][$autoTagCount] = $link;
                    $mgAutoTagArray['count']++;
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                }
                return $content;
                break;
            case 'album' :
                if ( $parm1 == '' || $parm1 == 0 ) {
                    $side_count--;
                    return $content;
                }
                if ( !isset($MG_albums[$parm1]->id) || $MG_albums[$parm1]->access == 0 ) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    $side_count--;
                    return $content;
                }
                $ss_count++;
                if ( $border == 0 ) {
                    $T->set_file(array('tag'    => 'autotag_nb.thtml'));
                } else {
                    $T->set_file (array('tag'      => 'autotag.thtml'));
                }
                if ( $caption != '' ) {
                    $alttag = ' alt="' . $caption . '" title="' . $caption . '"';
                } else {
                    $alttag = ' alt=""';
                    if ( $_MG_CONF['autotag_caption'] ) {
                        $caption = $MG_albums[$parm1]->title;
                    }
                }
                $aid = $parm1;

                if ( $MG_albums[$parm1]->tn_attached == 1 ) {
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $parm1 . $ext) ) {
                            $tnImage = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $parm1 . $ext;
                            $tnFileName = $_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $parm1 . $ext;
                            break;
                        }
                    }
                } else {
                    $filename = $MG_albums[$aid]->findCover();
                    if ( $filename != '' ) {
                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] .'/' . $filename . $ext) ) {
                                $tnImage = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] .'/' . $filename . $ext;
                                $tnFileName = $_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] .'/' . $filename . $ext;
                                break;
                            }
                        }
                    } else {
                        $tnImage = $_MG_CONF['mediaobjects_url'] . '/empty.png';
                        $tnFileName = $_MG_CONF['path_mediaobjects'] . 'empty.png';
                    }
                }
                $media_size = @getimagesize($tnFileName);
                if ( $media_size == false ) {
                    $tnImage = $_MG_CONF['mediaobjects_url'] . '/missing.png';
                    $tnFileName = $_MG_CONF['path_mediaobjects'] . 'missing.png';
                    $media_size = @getimagesize($tnFileName);
                }
                if ( $width > 0 && $height == 0 ) {
                    $ratio = $media_size[0] / $width;
                    $newwidth = $width;
                    $newheight = round($media_size[1] / $ratio);
                } else if ( $width == 0 && $height == 0 ) {
                    if ( $media_size[0] > $media_size[1] ) {        // landscape
                        $ratio = $media_size[0] / 200;
                        $newwidth = 200;
                        $newheight = round($media_size[1] / $ratio);
                    } else {    // portrait
                        $ratio = $media_size[1] / 200;
                        $newheight = 200;
                        $newwidth = round($media_size[0] / $ratio);
                    }
                } else if ( $width == 0 && $height > 0 ) {
                    $ratio = $height / $media_size[1];
                    $newheight = $height;
                    $newwidth = round($media_size[0] * $ratio);
                } else {
                    $newwidth = $width;
                    $newheight = $height;
                }
                $album_image = '<img src="' . $tnImage . '" ' . $alttag . 'style="width:' . $newwidth . 'px;height:' . $newheight . 'px;border:none;"' . XHTML . '>';

                $tagtext = $album_image;
                if ( $linkID == 0 ) {
                    $url = $_MG_CONF['site_url'] . '/album.php?aid=' . $parm1;
                } else {
                    if ( $linkID < 1000000 ) {
                        $url = $_MG_CONF['site_url'] . '/album.php?aid=' . $linkID;
                    } else {
                        $url = $_MG_CONF['site_url'] . '/media.php?s=' . $linkID;
                    }
                }
                if ( $enable_link == 0 ) {
                    $link = $tagtext;
                } else {
                    $link = '<a href="' . $url . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>' . $tagtext . '</a>';
                }
                $T->set_var(array(
                    'ss_count'   => $ss_count,
                    'align'      =>  $align,
                    'autotag'    => $link,
                    'caption'    => $caption,
                    'width'      => $newwidth,
                    'framewidth' => $newwidth + 10,
                    'xhtml'      => XHTML,
                ));
                if ( $align == 'left' || $align == 'right' ) {
                    $T->set_var('float','float:' . $align . ';');
                } else {
                    $T->set_var('float','');
                }
                if ( $align == 'left' ) {
                    $T->set_var('margin-right','margin-right:15px;');
                } else {
                    $T->set_var('margin-right','');
                }
                $T->parse('output','tag');
                $link = $T->finish($T->get_var('output'));
                if ( $align == 'center' ) {
                    $link = '<center>' . $link . '</center>';
                }
                if ( $destination != 'block' ) {
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $autoTagCount = $mgAutoTagArray['count'];
                    $mgAutoTagArray['tags'][$autoTagCount] = $link;
                    $mgAutoTagArray['count']++;
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                }
                return $content;
                break;
            case 'media' :
            /* image, oimage and img are depreciated */
            case 'image' :
            case 'oimage' :
            case 'img' :
                if ( $parm1 == '' || $parm1 == 0 ) {
                    return $content;
                }
                $direct_link = '';
                $ss_count++;
                if ( $border == 0 ) {
                    $T->set_file(array('tag'    => 'autotag_nb.thtml'));
                } else {
                    $T->set_file (array('tag'      => 'autotag.thtml'));
                }
                if ( $caption != '' ) {
                    $alttag = ' alt="' . $caption . '" title="' . $caption . '"';
                } else {
                    $alttag = ' alt=""';
                }
                $sql = "SELECT ma.album_id,m.media_title,m.media_type,m.media_filename,m.media_mime_ext,m.mime_type,m.media_tn_attached,m.remote_url FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . addslashes($parm1) . "'";
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
                    if ( !isset($MG_albums[$aid]->id) || $MG_albums[$aid]->access == 0 ) {
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                        return $content;
                    }
                    if ( $caption == '' && $_MG_CONF['autotag_caption'] ) {
                        $caption = $row['media_title'];
                    }
                    switch( $row['media_type'] ) {
                        case 0 :    // standard image
                            if ($autotag['tag'] == 'oimage' ) {
                                if ( $_MG_CONF['discard_originals'] == 1 ) {
                                    $default_thumbnail = 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                                } else {
                                    $default_thumbnail = 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                                }
                            } else {
                                switch ( $src ) {
                                    case 'orig' :
                                        if ( $_MG_CONF['discard_originals'] == 1 ) {
                                            $default_thumbnail = 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                                        } else {
                                            $default_thumbnail = 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                                        }
                                        break;
                                    case 'disp' :
                                        $default_thumbnail = 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                                        break;
                                    case 'tn' :
                                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                                                $default_thumbnail = 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                                                break;
                                            }
                                        }
                                        break;
                                    default :
                                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                                                $default_thumbnail = 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                                                break;
                                            }
                                        }
                                        break;
                                }

                                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                                        $direct_link = $_MG_CONF['mediaobjects_url'] . '/disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                                        break;
                                    }
                                }
                            }
                            break;
                        case 1 :    // video file
                            switch ( $row['mime_type'] ) {
                                case 'application/x-shockwave-flash' :
                                    $default_thumbnail = 'flash.png';
                                    break;
                                case 'video/quicktime' :
                                case 'video/mpeg' :
                                case 'video/x-m4v' :
                                    $default_thumbnail = 'quicktime.png';
                                    break;
                                case 'video/x-ms-asf' :
                                case 'video/x-ms-wvx' :
                                case 'video/x-ms-wm' :
                                case 'video/x-ms-wmx' :
                                case 'video/x-msvideo' :
                                case 'application/x-ms-wmz' :
                                case 'application/x-ms-wmd' :
                                    $default_thumbnail = 'wmp.png';
                                    break;
                                default :
                                    $default_thumbnail = 'video.png';
                                    break;
                            }
                            $src = 'tn';
                            break;
                        case 2 :    // music file
                            $src = 'tn';
                            $default_thumbnail = 'audio.png';
                            break;
                    }
                    if ( $row['media_tn_attached'] == 1 && ($src != 'orig' && $src != 'disp')) {
                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . 'tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                break;
                            }
                        }
                    } else {
                        $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
                        $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
                    }
                    if ( $autotag['tag'] == 'img' ) {
                        if ( $align != '' && $align != 'center' ) {
                            $album_image = '<span style="float:' . $align . ';padding:5px;"><img src="' . $media_thumbnail . '" ' . $alttag . 'style="border:none;"' . XHTML . '></span>';
                        } else {
                            $album_image = '<img src="' . $media_thumbnail . '" ' . $alttag . 'style="border:none;"' . XHTML . '>';
                        }
                    } else {
                        $album_image = '<img src="' . $media_thumbnail . '" ' . $alttag . 'style="border:none;"' . XHTML . '>';
                    }
                } else {
                    return $content; // no image found
                }
                $mediaSize = @getimagesize($media_thumbnail_file);
                if ( $mediaSize == false ) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }

                if ( $autotag['tag'] == 'oimage' || $src == 'orig') {
                    $newwidth = $mediaSize[0];
                    $newheight = $mediaSize[1];
                } else {
                    if ( $width > 0 ) {
                        $tn_height = $width;
                    } else {
                        switch ($src) {
                            case 'orig' :
                                $tn_height = $mediaSize[0];
                                break;
                            case 'disp' :
                                $tn_height = $mediaSize[0];
                                break;
                            case 'tn' :
                                $tn_height = 200;
                                break;
                            default :
                                $tn_height = 200;
                                break;
                        }
                    }

                    if ( $mediaSize[0] > $mediaSize[1] ) {
                        $ratio = $mediaSize[0] / $tn_height;
                        $newwidth = $tn_height;
                        $newheight = round($mediaSize[1] / $ratio);
                    } else {
                        $ratio = $mediaSize[1] / $tn_height;
                        $newheight = $tn_height;
                        $newwidth = round($mediaSize[0] / $ratio);
                    }
                }
                $album_image = '<img src="' . $media_thumbnail . '" ' . $alttag . ' style="width:' . $newwidth . 'px;height:' . $newheight . 'px;border:none;"' . XHTML . '>';

                $tagtext = $album_image;
                $link = '';
                if ( $alt == 1 && $row['remote_url'] != '' ) {
                    $url = $row['remote_url'];
                    if ( $autotag['tag'] != 'image' && $enable_link != 0) {
                        $link = '<a href="' . $url . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>' . $tagtext . '</a>';
                    } else {
                        $link = $tagtext;
                    }
                } else if ( $linkID == 0 ) {
                    $url = $_MG_CONF['site_url'] . '/media.php?s=' . $parm1;
                } else {
                    if ( $linkID < 1000000 ) {
                        if ( isset($MG_albums[$linkID]->id ) ) {
                            $url = $_MG_CONF['site_url'] . '/album.php?aid=' . $linkID;
                            if ( $autotag['tag'] != 'image' && $MG_albums[$linkID]->hidden != 1 && $enable_link != 0 ) {
                                $link = '<a href="' . $url . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>' . $tagtext . '</a>';
                             } else {
                                $link = $tagtext;
                            }
                        } else {
                            $url = $_MG_CONF['site_url'] . '/media.php?s=' . $parm1;
                        }
                    } else {
                        $linkAID = intval(DB_getItem($_TABLES['mg_media_albums'],'album_id','media_id="' . $linkID . '"'));
                        if ( $linkAID != 0 ) {
                            $url = $_MG_CONF['site_url'] . '/media.php?s=' . $linkID;
                            if ( $autotag['tag'] != 'image' && $MG_albums[$linkAID]->hidden != 1 && $enable_link != 0 ) {
                                $link = '<a href="' . $url . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>' . $tagtext . '</a>';
                            } else {
                                $link = $tagtext;
                            }
                        } else {
                            $url = $_MG_CONF['site_url'] . '/media.php?s=' . $parm1;
                        }
                    }
                }


                if ( $link == '' ) {

                    if ( $autotag['tag'] != 'image' && $MG_albums[$aid]->hidden != 1 && $enable_link != 0) {
                        if ( $enable_link == 2 && $direct_link != '' ) {
                            if ( $_MG_CONF['disable_lightbox'] == true ) {
                                $link = $tagtext;
                            } else {
                                $link = '<a href="' . $direct_link . '" rel="lightbox" title="' . strip_tags($caption) . '">' . $tagtext . '</a>';
                            }
                        } else {
                            $link = '<a href="' . $url . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>' . $tagtext . '</a>';
                        }
                    } else {
                        $link = $tagtext;
                    }
                }
                if ( $autotag['tag'] == 'img' ) {
                    if ( $align != '' && $align != 'center' ) {
                        $link = '<span style="float:' . $align . ';padding:5px;">'.$link.'</span>';
                    }
                    if ( $destination != 'block' ) {
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    } else {
                        $autoTagCount = $mgAutoTagArray['count'];
                        $mgAutoTagArray['tags'][$autoTagCount] = $link;
                        $mgAutoTagArray['count']++;
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    }
                    return $content;
                }
                $T->set_var(array(
                    'ss_count'  => $ss_count,
                    'align'     => $align,
                    'autotag'   => $link,
                    'caption'   => $caption,
                    'width'     => $newwidth,
                    'framewidth' => $newwidth + 10,
                    'xhtml'      => XHTML,
                ));
                if ( $align == 'left' || $align == 'right' ) {
                    $T->set_var('float','float:' . $align . ';');
                } else {
                    $T->set_var('float','');
                }
                if ( $align == 'left' ) {
                    $T->set_var('margin-right','margin-right:15px;');
                } else {
                    $T->set_var('margin-right','');
                }
                $T->parse('output','tag');
                $link = $T->finish($T->get_var('output'));

                if ( $align == 'center' ) {
                    $link = '<center>' . $link . '</center>';
                }
                if ( $destination != 'block' ) {
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $autoTagCount = $mgAutoTagArray['count'];
                    $mgAutoTagArray['tags'][$autoTagCount] = $link;
                    $mgAutoTagArray['count']++;
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                }
                return $content;
                break;
        }
    }
}


/**
* returns list of moderation values
*
* The array returned contains (in order): the row 'id' label, main plugin
* table, moderation fields (comma seperated), and plugin submission table
*
* @return       array        Returns array of useful moderation values
*
*/
function plugin_moderationvalues_mediagallery() {
    return;
}

/**
* show files for moderation on submissions page
*
* Uses the Plugin class to return data required by moderation.php to list
* plugin objects that need to be moderated.
*
* @return       Plugin       return Plugin class containing submission data
*
*/
function plugin_itemlist_mediagallery() {
    return;
}

/**
* Returns the plugins submission form
*
* Returns a string contain the HTML of the submission form for the plugin.
*
* @return       string       The HTML form
*
*/
function plugin_submit_mediagallery() {
    $retval = '<h1>submit file</h1>';
    $retval .= '<p>This would be the input form for uploading files. ';
    $retval .= 'But that functionality is already implemented elsewhere.</p>';

    return($retval);
}

/**
*
* Checks that the current user has the rights to moderate the
* plugin, returns true if this is the case, false otherwise
*
* @return        boolean       Returns true if moderator
*
*/
function plugin_ismoderator_mediagallery() {
    return SEC_hasRights('mediagallery.admin');
}

/**
* Returns the number of submissions waiting moderation
*
* Calculates the number of submissions that are currently waiting
* to be moderated and returns that number.
*
* @return      int       number of submissions awaiting moderation
*/
function plugin_submissioncount_mediagallery() {
    return;
}

/**
* Performs plugin exclusive work for items approved by moderation
*
* While moderation.php handles the actual move from mediagallery submission
* to mediagallery tables, within the function we handle all other approval
* relate tasks
*
* @param      string       $id      Identifying string
* @return     string       Any wanted HTML output
*
*/
function plugin_moderationapprove_mediagallery($id)
{
    return '';
}

/**
* Performs plugin exclusive work for items deleted by moderation
*
* While moderation.php handles the actual removal from mediagallery submission
* table, within this function we handle all other deletion
* relate tasks
*
* @param      string       $id      Identifying string
* @return     string       Any wanted HTML output
*
*/
function plugin_moderationdelete_mediagallery($id) {
    return;
}

/**
* saves submission to mediagallery submission table
*
* takes data input by submission form and populates the mediagallery submission
* table.
*
* @param       array       $A       array (key => value) of form items
* @return      boolean     returns true if successful
*
*/
function plugin_savesubmission_mediagallery($A) {
    global $_TABLES, $_USER, $_CONF;
    return false;
}

function plugin_searchtypes_mediagallery()
{
    global $_MG_CONF,$LANG_MG00;

    if ( $_MG_CONF['disable_search_integration'] == 1) {
        return '';
    }

    $tmp['mediagallery'] = $LANG_MG00['plugin'];
    return $tmp;
}


/**
* this searches for pages matching the user query and returns an array of
* for the header and table rows back to search.php where it will be formated and
* printed
*
* @query            string          Keywords user is looking for
* @datestart        date/time       Start date to get results for
* @dateend          date/time       End date to get results for
* @topic            string          The topic they were searching in
* @type             string          Type of items they are searching
* @author           string          Get all results by this author
*
*/
function plugin_dopluginsearch_mediagallery($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage) {
    global $MG_albums, $_TABLES, $_CONF, $_MG_CONF, $LANG_MG00, $LANG_MG01;

    if ( $_MG_CONF['disable_search_integration'] == 1) {
        return '';
    }

    $query = trim(addslashes(htmlspecialchars($query)));

    $sql = "SELECT DISTINCT a.*,ma.album_id,a.perm_anon, m.media_id AS id,m.media_user_id AS uid,m.media_desc AS description ,m.media_title AS title,m.media_time AS date,m.media_views AS hits, CONCAT('/mediagallery/media.php?s=',m.media_id) AS url  FROM " .
            $_TABLES['mg_media'] . " as m " .
            " INNER JOIN " . $_TABLES['mg_media_albums'] . " as ma " .
            " ON m.media_id=ma.media_id " .
            " INNER JOIN {$_TABLES['mg_albums']} AS a ON ma.album_id=a.album_id " .
            " WHERE 1=1 ";
    $sql .= COM_getPermSQL('AND') . ' ';

    if( !SEC_inGroup( 'Root')) {
        $sql .= ' AND hidden=0 ';
    }

    if (!empty($datestart) && !empty($dateend))
    {
        $delim = substr($datestart, 4, 1);
        if (!empty($delim))
        {
            $DS = explode($delim, $datestart);
            $DE = explode($delim, $dateend);
            $startdate = mktime(0,0,0,$DS[1],$DS[2],$DS[0]);
            $enddate = mktime(23,59,59,$DE[1],$DE[2],$DE[0]);
            $sql .= "AND (m.media_time BETWEEN '$startdate' AND '$enddate') ";
        }
    }
    if (!empty ($author))
        $sql .= "AND (m.media_user_id = '$author') ";

    $search = new SearchCriteria('mediagallery', 'Media Gallery');
    $columns = array('m.media_desc','m.media_title');
    list($sql,$ftsql) = $search->buildSearchSQL($keyType, $query, $columns, $sql);
    $search->setSQL($sql);
    $search->setFTSQL($ftsql);
    $search->setRank(3);

    return $search;

}

/**
* Returns the user option for this plugin
*
* Adds the plugin to the user menu
*
* Universal Plugin:  modify/uncomment use it
*
* @return   array   Array containing (plugin name, plugin user url, 0)
*
*/
function plugin_getuseroption_mediagallery() {
    global $_CONF, $_MG_CONF, $_USER, $LANG_MG01, $LANG_MG03, $_TABLES;

    if ( $_MG_CONF['up_display_rows_enabled'] == 0 &&
         $_MG_CONF['up_display_columns_enabled'] == 0 &&
         $_MG_CONF['up_mp3_player_enabled'] == 0 &&
         $_MG_CONF['up_av_playback_enabled'] == 0 &&
         $_MG_CONF['up_thumbnail_size_enabled'] == 0) {
             return '';
    }

    $title = $LANG_MG01['user_prefs'];

    if ( $_MG_CONF['member_albums'] == 1 && $_MG_CONF['link_to_member_album'] == 1 && !COM_isAnonUser() ) {
        $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . $_USER['uid'] . " AND album_parent=" . $_MG_CONF['member_album_root'] . " LIMIT 1";
        $result = DB_query($sql);
        $numRows = DB_numRows($result);
        if ( $numRows > 0 ) {
            $A = DB_fetchArray($result);
            $album_id = $A['album_id'];
            $title .= '</a> <a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id . '">' . $LANG_MG03['your_member_album'];
        }
    }

    if ( isset($_USER['uid']) && $_USER['uid'] > 1) {
        return array($title, $_MG_CONF['site_url'] . '/userprefs.php', 0);
    }
}

function phpblock_mg_randommedia(){
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG03, $_DB_dbms, $mg_installed_version;

    $retval = '';
    $gotit = 0;

    if ( $mg_installed_version != $_MG_CONF['pi_version'] ) {
        return $retval;
    }

    if ( $_DB_dbms == 'mssql' ) {
        $randomSelect = "NewID() ";
    } else {
        $randomSelect = "RAND(NOW()) ";
    }

    $sql = "SELECT ma.album_id,m.media_id,m.media_filename,m.media_title FROM {$_TABLES['mg_albums']} as a LEFT JOIN {$_TABLES['mg_media_albums']} as ma
            on a.album_id=ma.album_id LEFT JOIN {$_TABLES['mg_media']} as m on ma.media_id=m.media_id WHERE
            m.media_type=0 AND a.enable_random=1 AND a.hidden=0 " . COM_getPermSQL('and') . " ORDER BY " . $randomSelect . "LIMIT 1";

    $result = DB_query( $sql,1 );
    $nRows  = DB_numRows( $result );
    if ( $nRows > 0 ) {
        $row    = DB_fetchArray( $result );
        $gotit = true;
    } else {
        $gotit = false;
    }

    if ( $gotit ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/classFrame.php';
        $nFrame = new mgFrame();
        $nFrame->constructor( $_MG_CONF['random_skin'] );
        $imageFrameTemplate = $nFrame->getTemplate();
        $frWidth = $nFrame->frame['wHL'] + $nFrame->frame['wHR'];
        $frHeight = $nFrame->frame['hVT'] + $nFrame->frame['hVB'];
        $fCSS= $nFrame->getCSS();

        $F = new Template($_MG_CONF['template_path']);
        $F->set_var('xhtml',XHTML);
        $F->set_var('media_frame',$imageFrameTemplate);


        $T = new Template( MG_getTemplatePath(0) );
        $T->set_file (array ('rblock' => 'random_block.thtml'));
        $T->set_var('site_url', $_CONF['site_url']);
        $T->set_var('site_admin_url', $_CONF['site_admin_url']);

        $url_media      = $_MG_CONF['site_url'] . '/media.php?s=' . $row['media_id'];
        $url_album      = $_MG_CONF['site_url'] . '/album.php?aid=' . $row['album_id'];

        $msize = false;
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext) ) {
                $media_thumbnail= $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
                break;
            }
        }

        if ($msize == false ) {
            return '';
        }
        $imgwidth = $msize[0];
        $imgheight = $msize[1];
        if ( !isset($_MG_CONF['random_width']) || $_MG_CONF['random_width'] == 0 ) {
            $_MG_CONF['random_width'] = 120;
        }
        if ( $imgwidth > $imgheight ) {
            $ratio = $imgwidth / $_MG_CONF['random_width'];
            $width = $_MG_CONF['random_width'];
            $height = round($imgheight / $ratio);
        } else {
            $ratio = $imgheight / $_MG_CONF['random_width'];
            $height = $_MG_CONF['random_width'];
            $width = round($imgwidth / $ratio);
        }

        // frame template variables
        $F->set_var(array(
            'media_link_start'  => '<a href="' . $url_media . '">',
            'media_link_end'    => '</a>',
            'url_display_item'  =>  $url_media,
            'media_thumbnail'   =>  $media_thumbnail,
            'media_size'        =>  'width="' . $width . '" height="' . $height . '"',
            'media_height'      =>  $height,
            'media_width'       =>  $width,
            'border_width'      =>  $width + 15,
            'border_height'     =>  $height + 15,
            'row_height'        =>  $height + 40,
            'frWidth'           =>  $width  - $frWidth,
            'frHeight'          =>  $height - $frHeight,
            'media_tag'         =>  '',
        ));
        $F->parse('media','media_frame');
        $media_item_thumbnail = $F->finish($F->get_var('media'));

        $T->set_var(array(
            'url_media'         =>  $url_media,
            'url_album'         =>  $url_album,
            'media_thumbnail'   =>  $media_item_thumbnail,
            'media_height'      =>  $height,
            'media_width'       =>  $width,
            'border_width'      =>  $width + 14,
            'media_title'       =>  strip_tags($row['media_title']),
            'lang_browse_album' =>  $LANG_MG03['browse_album'],
            'xhtml'             =>  XHTML,
            'framecss'          =>  '', //$fCSS,
        ));

        $T->parse('output', 'rblock');
        $retval .= $T->finish($T->get_var('output'));
    }

    return $retval;
}

function phpblock_mg_maenroll(){
    global $_CONF, $_MG_CONF, $_MG_USERPREFS, $_TABLES, $_USER, $LANG_MG03;

    if ( $_MG_CONF['member_albums'] != 1 || $_MG_USERPREFS['member_gallery'] == 1 || $_USER['uid'] < 3) {
        return;
    }
    if ( SEC_inGroup("Remote Users") && $_MG_CONF['allow_remote'] != 1 ) {
        return;
    }

    $url = sprintf($LANG_MG03['member_album_signup'], $_MG_CONF['site_url'] . '/enroll.php');
    return $url;
}


function plugin_centerblock_mediagallery($where=1,$page=1,$topic =''){
    global $_CONF, $_MG_CONF, $MG_albums, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $LANG_MG03,$mg_installed_version;

    $pi_name = 'mediagallery';     // Plugin name
    $retval = '';

	if ( $_MG_CONF['gallery_only'] == 1 && $topic == '') {
		MG_galleryOnly();
	}

    if ( $mg_installed_version != $_MG_CONF['pi_version'] ) {
        return $retval;
    }

    if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $_MG_CONF['loginrequired'] == 1 )  {
        return '';
    }

    if ( $topic == '' ) {
        $sTopic = "none";
    } else {
        $sTopic = $topic;
    }

    if ( !isset($_MG_CONF['feature_member_album']) ) {
        $_MG_CONF['feature_member_album'] = 1;
    }

    if ($_MG_CONF['feature_member_album'] == 1 && $_MG_CONF['member_albums'] == 1 && $_USER['uid'] > 1 && $where == 1) {
        $cbpos = 1; //top of page
        $cbpage = 'none';

        if ($cbpage == 'none' && ($page > 1 OR $topic != "")){
            $cbenable = 0;
        } elseif ($cbpage == 'all' && $page > 1 ){
            $cbenable = 0;
        } elseif ($cbpage != 'none' && $cbpage != 'all' && $cbpage != $topic) { // $cbpage != $topic) {
            $cbenable = 0;
        } else
            $cbenable = 1;

        if ( $cbenable == 1 ) {
            $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . $_USER['uid'] . " AND album_parent=" . $_MG_CONF['member_album_root'] . " LIMIT 1";
            $result = DB_query($sql);
            $numRows = DB_numRows($result);
            if ( $numRows > 0 ) {
                $A = DB_fetchArray($result);
                $album_id = $A['album_id'];

                $T = new Template( MG_getTemplatePath(0) );
                $T->set_file ('page', 'cb_featured_album.thtml');
                $T->set_var('xhtml',XHTML);
                require_once $_CONF['path'] . 'plugins/mediagallery/include/classAlbum.php';

                if ($MG_albums[$album_id]->last_update > 0 ) {
                    $album_last_update = MG_getUserDateTimeFormat($MG_albums[$album_id]->last_update);
                } else {
                    $album_last_update = '';
                }
                $cover = $MG_albums[$album_id]->findCover();
                if ( $cover != '' ) {
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $cover[0] .'/' . $cover . $ext) ) {
                            $album_last_image   = $_MG_CONF['mediaobjects_url'] . '/tn/' . $cover[0] .'/' . $cover . $ext;
                            $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $cover[0] .'/' . $cover . $ext);
                            break;
                        }
                    }
                } else {
                    $album_last_image = $_MG_CONF['mediaobjects_url'] . '/empty.png';
                    $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'empty.png');
                }
                $album_media_count = $MG_albums[$album_id]->getMediaCount();
                $updated_prompt = ($_MG_CONF['dfid']=='99' ? '' : $LANG_MG03['updated_prompt']);
                $album_title = $MG_albums[$album_id]->title;
                $album_desc  = $MG_albums[$album_id]->description;

                if ( $MG_albums[$album_id]->tn_attached == 1 ) {
                    $media_size = false;
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $MG_albums[$album_id]->id . $ext) ) {
                            $album_last_image = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $MG_albums[$album_id]->id . $ext;
                            $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $MG_albums[$album_id]->id . $ext);
                            break;
                        }
                    }
                }
                if ( $media_size == false ) {
                    $album_last_image = $_MG_CONF['mediaobjects_url'] . '/missing.png';
                    $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'missing.png');
                }

                if ( !empty($MG_albums[$album_id]->children ) ) {
                    $saRows = 0;
                    $SAchildren = $MG_albums[$album_id]->getChildren();
    	            if ( isset($_MG_CONF['subalbum_select']) && $_MG_CONF['subalbum_select'] == 1  ) {
    	                $subAlbumDisplay = '<form name="subalbums' . $MG_albums[$achild[$indexCounter]]->id . '" action="' . $_MG_CONF['site_url'] . '/album.php' . '" method="get" style="margin:0;padding:0">';
    	                $subAlbumDisplay .= '<select name="aid" onchange="forms[\'subalbums' . $MG_albums[$achild[$indexCounter]]->id . '\'].submit()">';
    	                $subAlbumDisplay .= '<optgroup label="' . $LANG_MG01['select_subalbum'] . '">' . LB;
    	            } else {
    	                $subAlbumDisplay = '';
    	            }
                    foreach($SAchildren as $SAchild) {
                        if ( $MG_albums[$SAchild]->access > 0 ) {
                            if ( $MG_albums[$SAchild]->hidden ) {
                                if ( $MG_albums[$SAchild]->access == 3 ) {
                                    $mediaCount = $MG_albums[$SAchild]->getMediaCount();
                                    if ( $_MG_CONF['subalbum_select'] == 1 ) {
                                        if ( strlen( $MG_albums[$SAchild]->title ) > 50 ) {
                                            $aTitle = substr( $MG_albums[$SAchild]->title, 0, 50 ) . '...';
                                        } else {
                                            $aTitle = $MG_albums[$SAchild]->title;
                                        }
                                        $subAlbumDisplay .= '<option value="' . $MG_albums[$SAchild]->id . '">' . $aTitle . ' (' . $mediaCount . ')</option>';
                                    } else {
                                        $subAlbumDisplay .= '<li><a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$SAchild]->id . '&amp;page=1' . '">' . $MG_albums[$SAchild]->title . ' (' . $mediaCount . ')</a></li>';
                                    }
                                    $saRows++;
                                }
                            } else {
                                $mediaCount = $MG_albums[$SAchild]->getMediaCount();
                                if ( $_MG_CONF['subalbum_select'] == 1 ) {
                                    if ( strlen( $MG_albums[$SAchild]->title ) > 50 ) {
                                        $aTitle = substr( $MG_albums[$SAchild]->title, 0, 50 ) . '...';
                                    } else {
                                        $aTitle = $MG_albums[$SAchild]->title;
                                    }
                                    $subAlbumDisplay .= '<option value="' . $MG_albums[$SAchild]->id . '">' . $aTitle . ' (' . $mediaCount . ')</option>';
                                } else {
                                    $subAlbumDisplay .= '<li><a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$SAchild]->id . '&amp;page=1' . '">' . $MG_albums[$SAchild]->title . ' (' . $mediaCount . ')</a></li>';
                                }
                                $saRows++;
                            }
                        }
                    }

	                if ( $_MG_CONF['subalbum_select'] == 1 ) {
	                    $subAlbumDisplay .= '</optgroup></select>';
	                    $subAlbumDisplay .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '" ' . XHTML . '>';
	                    $subAlbumDisplay .= '<input type="hidden" name="page" value="1"' . XHTML . '>';
	                    $subAlbumDisplay .= '</form>';
	                }
    	            if ( $saRows > 0 && $_MG_CONF['subalbum_select'] != 1) {
    	                $T->set_var(array(
    	                   'saulstart'         =>  '<ul>',
    	                   'saulend'           =>  '</ul>',
    	                ));
    	            } else {
    	                $T->set_var(array(
    	                   'saulstart'         =>  '',
    	                   'saulend'           =>  '',
    	                ));
    	            }
                    $T->set_var(array(
                        'lang_subalbums'    =>  $LANG_MG01['subalbums'],
                        'subalbumcount'     =>  '(' . $saRows . ')',
                        'subalbumlist'      =>  $subAlbumDisplay,
                    ));
                } else {
                    $T->set_var(array(
                        'lang_subalbums'    =>  '',
                        'subalbumcount'     =>  '',
                        'subalbumlist'      =>  '',
                        'saulstart'         =>  '',
                        'saulend'           =>  '',
                    ));
                }

                switch ($_MG_CONF['gallery_tn_size'] ) {
                    case '0' :      //small
                        $tn_height = 100;
                        break;
                    case '1' :      //medium
                        $tn_height = 150;
                        break;
                    case '2' :
                        $tn_height = 200;
                        break;
                    default :
                        $tn_height = 150;
                        break;
                }
                if ( $media_size[0] > $media_size[1] ) {
                    $ratio = $media_size[0] / $tn_height;
                    $newwidth = $tn_height;
                    $newheight = round($media_size[1] / $ratio);
                } else {
                    $ratio = $media_size[1] / $tn_height;
                    $newheight = $tn_height;
                    $newwidth = round($media_size[0] / $ratio);
                }

                $T->set_var(array(
                    'site_url'          => $_CONF['site_url'],
                    'album_id'          => $album_id,
                    'album_title'       => $album_title,
                    'album_desc'        => $album_desc,
                    'album_media_count' => $album_media_count,
                    'subalbum_media_count' => $album_media_count,
                    'album_last_update' => $album_last_update[0],
                    'updated_prompt'    => ($MG_albums[$album_id]->last_update > 0 ? $updated_prompt : ''),
                    'album_last_image'  => $album_last_image,
                    'img_height'        => $newheight,
                    'img_width'         => $newwidth,
                    'media_size'        => 'width="' . $newwidth . '" height="' . $newheight . '"',
                    'border_width'      => $newwidth + 20,
                    'border_height'     => $newheight + 20,
                    'column_width'      => $newwidth + 30,
                    'u_viewalbum'       => $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id . '&amp;page=1',
                    'lang_album'        => $LANG_MG00['album'],
                    'featured_album'    => $LANG_MG03['your_member_album'], // $LANG_MG03['featured_album']
                ));

                $T->parse('output','page');
                $retval = $T->finish($T->get_var('output'));
                return $retval;
            }
        }
    }

    $result = DB_query("SELECT album_id, cbpage, cbposition FROM {$_TABLES['mg_albums']} WHERE featured='1' AND (cbpage='" . $sTopic . "' OR cbpage='all') " . COM_getPermSQL('and') . " LIMIT 1");

    if (DB_numRows($result) == 1) {
        list($album_id,$cbpage,$cbpos) = DB_fetchArray($result);

        // If enabled only for homepage and this is not page 1 or a topic page, then set disable flag
        if ($cbpage == 'none' && ($page > 1 OR $topic != "")){
            $cbenable = 0;
        } elseif ($cbpage == 'all' && $page > 1 ){
            $cbenable = 0;
        } elseif ($cbpage != 'none' && $cbpage != 'all' && $cbpage != $topic) { // $cbpage != $topic) {
            $cbenable = 0;
        } else
            $cbenable = 1;

        // Check if there are no featured articles in this topic and if so then place it at the top of the page
        if ($topic != "") {
            $wheresql = "WHERE tid='$topic' AND featured > 0";
        } else {
            $wheresql = "WHERE featured = 1";
        }
        $query = DB_query("SELECT count(*) as count FROM {$_TABLES['stories']} $wheresql");
        $result = DB_fetchArray($query);
        if ($result['count'] == 0 && $cbpos == 2) {
            $cbpos = 1;//top of page
        }

        if ($cbenable AND $cbpos == $where) {
            $T = new Template( MG_getTemplatePath(0) );
            $T->set_file ('page', 'cb_featured_album.thtml');

            require_once $_CONF['path'] . 'plugins/mediagallery/include/classAlbum.php';

            if ($MG_albums[$album_id]->last_update > 0 ) {
                $album_last_update = MG_getUserDateTimeFormat($MG_albums[$album_id]->last_update);
            } else {
                $album_last_update = '';
            }
            $cover = $MG_albums[$album_id]->findCover();
            if ( $cover != '' ) {
                $media_size = false;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $cover[0] .'/' . $cover . $ext) ) {
                        $album_last_image   = $_MG_CONF['mediaobjects_url'] . '/tn/' . $cover[0] .'/' . $cover . $ext;
                        $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $cover[0] .'/' . $cover . $ext);
                        break;
                    }
                }
            } else {
                $album_last_image = $_MG_CONF['mediaobjects_url'] . '/empty.png';
                $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'empty.png');
            }
            $album_media_count = $MG_albums[$album_id]->getMediaCount();
            $updated_prompt = ($_MG_CONF['dfid']=='99' ? '' : $LANG_MG03['updated_prompt']);
            $album_title = $MG_albums[$album_id]->title;
            $album_desc  = $MG_albums[$album_id]->description;

            if ( $MG_albums[$album_id]->tn_attached == 1 ) {
                $media_size = false;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $MG_albums[$album_id]->id . $ext) ) {
                        $album_last_image = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $MG_albums[$album_id]->id . $ext;
                        $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $MG_albums[$album_id]->id . $ext);
                        break;
                    }
                }
            }
            if ( $media_size == false ) {
                $album_last_image = $_MG_CONF['mediaobjects_url'] . '/missing.png';
                $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'missing.png');
            }

            if ( !empty($MG_albums[$album_id]->children ) ) {
                $saRows = 0;
                $SAchildren = $MG_albums[$album_id]->getChildren();
	            if ( isset($_MG_CONF['subalbum_select']) && $_MG_CONF['subalbum_select'] == 1  ) {
	                $subAlbumDisplay = '<form name="subalbums' . $MG_albums[$achild[$indexCounter]]->id . '" action="' . $_MG_CONF['site_url'] . '/album.php' . '" method="get" style="margin:0;padding:0">';
	                $subAlbumDisplay .= '<select name="aid" onchange="forms[\'subalbums' . $MG_albums[$achild[$indexCounter]]->id . '\'].submit()">';
	                $subAlbumDisplay .= '<optgroup label="' . $LANG_MG01['select_subalbum'] . '">' . LB;
	            } else {
	                $subAlbumDisplay = '';
	            }
                foreach($SAchildren as $SAchild) {
                    if ( $MG_albums[$SAchild]->access > 0 ) {
                        if ( $MG_albums[$SAchild]->hidden ) {
                            if ( $MG_albums[$SAchild]->access == 3 ) {
                                $mediaCount = $MG_albums[$SAchild]->getMediaCount();
                                if ( $_MG_CONF['subalbum_select'] == 1 ) {
                                    if ( strlen( $MG_albums[$SAchild]->title ) > 50 ) {
                                        $aTitle = substr( $MG_albums[$SAchild]->title, 0, 50 ) . '...';
                                    } else {
                                        $aTitle = $MG_albums[$SAchild]->title;
                                    }
                                    $subAlbumDisplay .= '<option value="' . $MG_albums[$SAchild]->id . '">' . $aTitle . ' (' . $mediaCount . ')</option>';
                                } else {
                                    $subAlbumDisplay .= '<li><a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$SAchild]->id . '&amp;page=1' . '">' . $MG_albums[$SAchild]->title . ' (' . $mediaCount . ')</a></li>';
                                }
                                $saRows++;
                            }
                        } else {
                            $mediaCount = $MG_albums[$SAchild]->getMediaCount();
                            if ( $_MG_CONF['subalbum_select'] == 1 ) {
                                if ( strlen( $MG_albums[$SAchild]->title ) > 50 ) {
                                    $aTitle = substr( $MG_albums[$SAchild]->title, 0, 50 ) . '...';
                                } else {
                                    $aTitle = $MG_albums[$SAchild]->title;
                                }
                                $subAlbumDisplay .= '<option value="' . $MG_albums[$SAchild]->id . '">' . $aTitle . ' (' . $mediaCount . ')</option>';
                            } else {
                                $subAlbumDisplay .= '<li><a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$SAchild]->id . '&amp;page=1' . '">' . $MG_albums[$SAchild]->title . ' (' . $mediaCount . ')</a></li>';
                            }
                            $saRows++;
                        }
                    }
                }
                if ( $_MG_CONF['subalbum_select'] == 1 ) {
                    $subAlbumDisplay .= '</optgroup></select>';
                    $subAlbumDisplay .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '" ' . XHTML . '>';
                    $subAlbumDisplay .= '<input type="hidden" name="page" value="1"' . XHTML . '>';
                    $subAlbumDisplay .= '</form>';
                }
	            if ( $saRows > 0 && $_MG_CONF['subalbum_select'] != 1) {
	                $T->set_var(array(
	                   'saulstart'         =>  '<ul>',
	                   'saulend'           =>  '</ul>',
	                ));
	            } else {
	                $T->set_var(array(
	                   'saulstart'         =>  '',
	                   'saulend'           =>  '',
	                ));
	            }
                $T->set_var(array(
                    'lang_subalbums'    =>  $LANG_MG01['subalbums'],
                    'subalbumcount'     =>  '(' . $saRows . ')',
                    'subalbumlist'      =>  $subAlbumDisplay
                ));
            } else {
                $T->set_var(array(
                    'lang_subalbums'    =>  '',
                    'subalbumcount'     =>  '',
                    'subalbumlist'      =>  ''
                ));
            }

            switch ($_MG_CONF['gallery_tn_size'] ) {
                case '0' :      //small
                    $tn_height = 100;
                    break;
                case '1' :      //medium
                    $tn_height = 150;
                    break;
                case '2' :
                    $tn_height = 200;
                    break;
                default :
                    $tn_height = 150;
                    break;
            }
            if ( $media_size[0] > $media_size[1] ) {
                $ratio = $media_size[0] / $tn_height;
                $newwidth = $tn_height;
                $newheight = round($media_size[1] / $ratio);
            } else {
                $ratio = $media_size[1] / $tn_height;
                $newheight = $tn_height;
                $newwidth = round($media_size[0] / $ratio);
            }

            $T->set_var(array(
                'album_id'          => $album_id,
                'album_title'       => $album_title,
                'album_desc'        => $album_desc,
                'album_media_count' => $album_media_count,
                'subalbum_media_count' => $album_media_count,
                'album_last_update' => $album_last_update[0],
                'updated_prompt'    => ($MG_albums[$album_id]->last_update > 0 ? $updated_prompt : ''),
                'album_last_image'  => $album_last_image,
                'img_height'        => $newheight,
                'img_width'         => $newwidth,
                'media_size'        => 'width="' . $newwidth . '" height="' . $newheight . '"',
                'border_width'      => $newwidth + 20,
                'border_height'     => $newheight + 20,
                'column_width'      => $newwidth + 30,
                'u_viewalbum'       => $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id . '&amp;page=1',
                'lang_album'        => $LANG_MG00['album'],
                'featured_album'    => $LANG_MG03['featured_album']
            ));

            $T->parse('output','page');
            $retval = $T->finish($T->get_var('output'));
            return $retval;
        }
    }
}

function plugin_chkVersion_mediagallery() {
    global $_MG_CONF;

    return $_MG_CONF['pi_version'];
}

function MG_clean_directories($path, $needle = '')
{
    if ( $path[strlen($path)-1] != '/' ) {
        $path .= '/';
    }
    if ($dir = @opendir($path)) {
        while ($entry = readdir($dir)) {
            if ($entry == '.' || $entry == '..' || is_link($entry)) {
                continue;
            } elseif (is_dir($path . $entry)) {
                MG_clean_directories($path . $entry, $needle);
                @rmdir($entry);
            } elseif (empty($needle) || strpos($entry, $needle) !== false) {
                @unlink($path . $entry);
            }
        }
        @closedir($dir);
    }
}

function MG_cleanup_plugin($plugin)
{
    global $TEMPLATE_OPTIONS;

    if (!empty($plugin)) {
        $plugin = '__' . $plugin . '__';
    }
    MG_clean_directories($TEMPLATE_OPTIONS['path_cache'], $plugin);
}

function plugin_upgrade_mediagallery() {
    global $_CONF, $_TABLES,$_MG_CONF;

    require_once $_CONF['path'].'plugins/mediagallery/upgrade.php';

    return mediagallery_upgrade();
}

function plugin_user_create_mediagallery( $uid, $force = 0 ) {
    global $_TABLES, $_MG_CONF, $LANG_MG01;

    $sql = "INSERT INTO {$_TABLES['mg_userprefs']} (uid, active, display_rows, display_columns, mp3_player, playback_mode, tn_size, quota, member_gallery) VALUES (" . $uid . ",1,0,0,-1,-1,-1," . $_MG_CONF['member_quota'] . ",0)";
    DB_query($sql,1);

    $retval = -1;
    if ( $force == 1 || ($_MG_CONF['member_albums'] == 1 && $_MG_CONF['member_auto_create'] == 1) ) {
        $username               = DB_getItem($_TABLES['users'],'username','uid=' . $uid);
        $fullname               = DB_getItem($_TABLES['users'],'fullname','uid=' . $uid);
        $grp_id                 = DB_getItem($_TABLES['vars'], 'value','name="mediagallery_gid"');

        $album = new mgAlbum();

        $title = $username . $LANG_MG01['member_album_postfix'];
        if ( !empty($fullname) && $_MG_CONF['member_use_fullname'] == 1 ) {
            $title = $fullname . $LANG_MG01['member_album_postfix'];
        }
        $album->title           = htmlspecialchars(strip_tags(COM_checkWords($title)));
        $album->parent          = $_MG_CONF['member_album_root'];
        $album->group_id        = $grp_id;
        $album->mod_group_id    = $grp_id; // $_MG_CONF['member_mod_group_id'];
        $album->owner_id        = $uid;
        $album->moderate        = $_MG_CONF['member_moderate'];
        $album->email_mod       = $_MG_CONF['member_email_mod'];
        $album->perm_owner      = $_MG_CONF['member_perm_owner'];
        $album->perm_group      = $_MG_CONF['member_perm_group'];
        $album->perm_members    = $_MG_CONF['member_perm_members'];
        $album->perm_anon       = $_MG_CONF['member_perm_anon'];
        $album->id              = $album->createAlbumID();
        $retval = $album->id;
        $album->saveAlbum();
        $result = DB_query("UPDATE {$_TABLES['mg_userprefs']} SET member_gallery=1 WHERE uid=" . $uid,1);
    }
    return $retval;
}

function plugin_user_delete_mediagallery( $uid ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01;

    // remove any user preferences in the database

    $sql = "DELETE FROM {$_TABLES['mg_userprefs']} WHERE uid=" . $uid;
    DB_query($sql,1);

    if ( $_MG_CONF['member_albums'] ) {
        if ( $_MG_CONF['member_album_archive'] == 0 ) {
            $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . $uid;
            $result = DB_query($sql);
            while ( $A = DB_fetchArray($result) ) {
                MG_deleteMemberAlbums($A['album_id']);
            }
        } else {
            // update parent album to be archive album
            $sql = "UPDATE {$_TABLES['mg_albums']} SET album_parent=" . $_MG_CONF['member_album_archive'] . " WHERE owner_id=" . $uid . " AND album_parent=" . $_MG_CONF['member_album_root'];
            DB_query($sql);
        }
    }
}

function MG_deleteMemberAlbums( $album_id ){
    global $_CONF, $_MG_CONF, $_TABLES, $_USER;

    $sql = "SELECT ma.media_id, m.media_filename, m.media_mime_ext
            FROM " . $_TABLES['mg_media_albums'] .
            " as ma LEFT JOIN " . $_TABLES['mg_media'] .
            " as m ON ma.media_id=m.media_id
            WHERE ma.album_id = " . $album_id;

    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );
    $mediarow = array();
    for ( $i=0; $i < $nRows; $i++) {
      $row = DB_fetchArray( $result );
      $mediarow[] = $row;
    }
    if ( count( $mediarow ) != 0 ) {
        for ( $i = 0; $i < count( $mediarow ); $i++ ) {
            $sql = "SELECT COUNT(media_id) AS count FROM " . $_TABLES['mg_media_albums'] . "  WHERE media_id = '" . $mediarow[$i]['media_id'] . "'";
            $result = DB_query( $sql );
            $row = DB_fetchArray( $result );
            if ( $row['count'] <= 1 ) {
                @unlink($_MG_CONF['path_mediaobjects'] . 'tn/'   . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . '.*');
                @unlink($_MG_CONF['path_mediaobjects'] . 'disp/' . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . '.*');
                @unlink($_MG_CONF['path_mediaobjects'] . 'orig/' . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . '.' . $mediarow[$i]['media_mime_ext']);
                $sql = "DELETE FROM " . $_TABLES['mg_media'] . "  WHERE media_id = '" . $mediarow[$i]['media_id'] . "'";
                DB_query( $sql );
                DB_delete($_TABLES['comments'], 'sid', $mediarow[$i]['media_id']);
                DB_delete($_TABLES['mg_playback_options'],'media_id', $mediarow[$i]['media_id']);
            }
        }
    }
    $sql = "DELETE FROM " . $_TABLES['mg_media_albums'] . " WHERE album_id = " . $album_id;
    DB_query( $sql );
    $sql = "DELETE FROM " . $_TABLES['mg_albums'] . " WHERE album_id = " . $album_id;
    DB_query( $sql );
}

// display user info in profile

function plugin_profileblocksdisplay_mediagallery( $uid ) {
    global $MG_albums,$_TABLES, $_MG_CONF, $_CONF, $LANG_MG10, $_USER;

    $retval = '';

    if ( $_MG_CONF['profile_hook'] != 1 ) {
        return '';
    }

    if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $_MG_CONF['loginrequired'] == 1) {
        return '';
    }

    if ( $uid == '' ) {
        return '';
    }

    $template = new Template( MG_getTemplatePath(0) );
    $template->set_file(array(
        'mblock' => 'mediablock.thtml',
        'mrow'   => 'mediarow.thtml',
    ));

    $username = DB_getItem($_TABLES['users'], 'username', 'uid=' . $uid);
    if ( $username == '' ) {
        return '';
    }

    $template->set_var('start_block_last10mediaitems', COM_startBlock($LANG_MG10['last_10'] . $username));
    $template->set_var('start_block_useralbums', COM_startBlock($LANG_MG10['albums_owned'] . $username));
    $template->set_var('lang_thumbnail', $LANG_MG10['thumbnail']);
    $template->set_var('lang_title', $LANG_MG10['title']);
    $template->set_var('lang_album', $LANG_MG10['album']);
    $template->set_var('lang_album_description', $LANG_MG10['album_desc']);
    $template->set_var('lang_upload_date', $LANG_MG10['upload_date']);
    $template->set_var('end_block', COM_endBlock());

    $class = 0;

    $sql = "SELECT a.album_id,m.media_upload_time,m.media_id,m.media_filename,m.mime_type,m.media_mime_ext,m.media_title,m.remote_media,m.media_type FROM {$_TABLES['mg_albums']} as a LEFT JOIN {$_TABLES['mg_media_albums']} as ma
            on a.album_id=ma.album_id LEFT JOIN {$_TABLES['mg_media']} as m on ma.media_id=m.media_id WHERE
            m.media_user_id=" . $uid . " AND a.hidden=0 " . COM_getPermSQL('and') . " ORDER BY m.media_upload_time DESC LIMIT 5";

    $result = DB_query($sql);
    $mCount = 0;
    while ( $row = DB_fetchArray($result)) {
        $album_id = $row['album_id'];
        $album_title = strip_tags($MG_albums[$album_id]->title);
        $upload_time = MG_getUserDateTimeFormat($row['media_upload_time']);
        $url_media = $_MG_CONF['site_url'] . '/media.php?s=' . $row['media_id'];
        $url_album = $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id;

        switch( $row['media_type'] ) {
            case 0 :    // standard image
                $msize = false;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
                        break;
                    }
                }
                break;
            case 1 :    // video file
                switch ( $row['mime_type'] ) {
                    case 'application/x-shockwave-flash' :
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/flash.png';
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'flash.png');
                        break;
                    case 'video/quicktime' :
                    case 'video/mpeg' :
                    case 'video/x-m4v' :
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/quicktime.png';
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'quicktime.png');
                        break;
                    case 'video/x-ms-asf' :
                    case 'video/x-ms-wvx' :
                    case 'video/x-ms-wm' :
                    case 'video/x-ms-wmx' :
                    case 'video/x-msvideo' :
                    case 'application/x-ms-wmz' :
                    case 'application/x-ms-wmd' :
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/wmp.png';
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'wmp.png');
                        break;
                    default :
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/video.png';
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'video.png');
                        break;
                }
                break;
            case 2 :    // music file
                $url_thumb = $_MG_CONF['mediaobjects_url'] . '/audio.png';
                $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'audio.png');
                break;
            case 4 :    // other files
                switch ($row['media_mime_ext']) {
                    case 'zip' :
                    case 'arj' :
                    case 'rar' :
                    case 'gz'  :
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/zip.png';
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'zip.png');
                        break;
                    case 'pdf' :
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/pdf.png';
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'pdf.png');
                        break;
                    default :
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/generic.png';
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'generic.png');
                        break;
                }
                break;
            case 5 :
                $url_thumb = $_MG_CONF['mediaobjects_url'] . '/remote.png';
                $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'remote.png');
                break;
        }

        if ($msize == false ) {
            $url_thumb = $_MG_CONF['mediaobjects_url'] . '/missing.png';
            $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'missing.png');
        }
        $imgwidth = $msize[0];
        $imgheight = $msize[1];

        if ( $imgwidth > $imgheight ) {
            $ratio = $imgwidth / 120;
            $width = 120;
            $height = round($imgheight / $ratio);
        } else {
            $ratio = $imgheight / 120;
            $height = 120;
            $width = round($imgwidth / $ratio);
        }

        $template->set_var('mediaitem_image', '<img src="' . $url_thumb . '" alt="" style="width:' . $width . 'px;height:' . $height . 'px;border:none;"' . XHTML . '>');
        $template->set_var('mediaitem_begin_href', '<a href="' . $url_media . '">');
        $template->set_var('mediaitem_title', strip_tags($row['media_title']));
        $template->set_var('mediaitem_end_href', '</a>');
        $template->set_var('mediaitem_album_begin_href', '<a href="' . $url_album . '">');
        $template->set_var('mediaitem_album_title', $album_title);
        $template->set_var('mediaitem_date', $upload_time[0]);
        $template->set_var('rowclass', ($class % 2) ? '1' : '2');
        $template->parse('mediaitem_row', 'mrow', true);
        $class++;
        $mCount++;
    }
    // end of media block
    $template->parse('output', 'mblock', true);
    if ( $mCount != 0 ) {
        $retval .= $template->finish ($template->get_var('output'));
    }

    $template = new Template( MG_getTemplatePath(0) );
    $template->set_file(array(
        'mblock' => 'albumblock.thtml',
        'arow'   => 'albumrow.thtml'
    ));

    $template->set_var('start_block_useralbums', COM_startBlock($LANG_MG10['albums_owned'] . $username));
    $template->set_var('lang_thumbnail', $LANG_MG10['thumbnail']);
    $template->set_var('lang_album', $LANG_MG10['album']);
    $template->set_var('lang_album_description', $LANG_MG10['album_desc']);
    $template->set_var('end_block', COM_endBlock());

    $sql = "SELECT album_id,album_title,album_desc,tn_attached "
         . "FROM " . $_TABLES['mg_albums']
         . " WHERE owner_id=" . $uid . " AND hidden=0 " . COM_getPermSQL('and') . " ORDER BY last_update DESC LIMIT 10";

    $result = DB_query($sql);
    $aCount = 0;
    while ($row = DB_fetchArray($result)) {
        $aid        = $row['album_id'];
        $url_album  = $_MG_CONF['site_url'] . '/album.php?aid=' . $row['album_id'];

        $url_thumb = '';
        $msize = false;
        if ( $row['tn_attached'] == 1 ) {
            $msize = false;
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $row['album_id'] . $ext) ) {
	                $url_thumb = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $row['album_id'] . $ext;
                    $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $row['album_id'] . $ext);
                    break;
                }
            }
        } else {
            $cover_file = $MG_albums[$aid]->findCover();
            if ( $cover_file != '' ) {
                if ( substr($cover_file,0,3) == 'tn_' ) {
                    $offset = 3;
                } else {
                    $offset = 0;
                }
                $msize = false;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $cover_file[$offset] .'/' . $cover_file . $ext) ) {
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/tn/' . $cover_file[$offset] .'/' . $cover_file . $ext;
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $cover_file[$offset] .'/' . $cover_file . $ext);
                        break;
                    }
                }
            }
        }

        if ($msize == false || $url_thumb == '' ) {
            $url_thumb = $_MG_CONF['mediaobjects_url'] . '/empty.png';
            $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'empty.png');
        }
        $imgwidth = $msize[0];
        $imgheight = $msize[1];

        if ( $imgwidth == 0 || $imgheight == 0 ) {
            $url_thumb = $_MG_CONF['mediaobjects_url'] . '/empty.png';
            $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'empty.png');
            $imgwidth = $msize[0];
            $imgheight = $msize[1];
            if ( $imgwidth == 0 || $imgheight == 0 ) {
                continue;
            }
        }

        if ( $imgwidth > $imgheight ) {
            $ratio = $imgwidth / 120;
            $width = 120;
            $height = round($imgheight / $ratio);
        } else {
            $ratio = $imgheight / 120;
            $height = 120;
            $width = round($imgwidth / $ratio);
        }
        $template->set_var('album_cover', '<img src="' . $url_thumb . '" alt="" style="width:' . $width . 'px;height:' . $height . 'px;border:none;"' . XHTML . '>');
        $template->set_var('album_begin_href', '<a href="' . $url_album . '">');
        $template->set_var('album_title', strip_tags($row['album_title']));
        $template->set_var('album_end_href', '</a>');
        $template->set_var('album_desc', strip_tags($row['album_desc']));
        $template->set_var('rowclass', ($class % 2) ? '1' : '2');
        $template->parse('useralbum_row', 'arow', true);
        $class++;
        $aCount++;
    }
    $template->parse('output', 'mblock', true);
    if ( $aCount != 0 ) {
        $retval .= $template->finish ($template->get_var('output'));
    }
    return $retval;
}

function MG_galleryOnly() {
	global $_MG_CONF, $_CONF, $_USER, $LANG_LOGIN;

	require_once $_CONF['path'] . 'plugins/mediagallery/include/mgindex.php';

	if (!function_exists('MG_usage')) {
	    // The plugin is disabled
	    $display = COM_siteHeader();
	    $display .= COM_startBlock('Plugin disabled');
	    $display .= '<br' . XHTML . '><br' . XHTML . '>The Media Gallery plugin is currently disabled.';
	    $display .= COM_endBlock();
	    $display .= COM_siteFooter(true);
	    echo $display;
	    exit;
	}

	if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $_MG_CONF['loginrequired'] == 1 )  {
	    $display = MG_siteHeader();
	    $display .= COM_startBlock ($LANG_LOGIN[1], '',
	              COM_getBlockTemplate ('_msg_block', 'header'));
	    $login = new Template($_CONF['path_layout'] . 'submit');
	    $login->set_file (array ('login'=>'submitloginrequired.thtml'));
	    $login->set_var ('login_message', $LANG_LOGIN[2]);
	    $login->set_var ('site_url', $_CONF['site_url']);
	    $login->set_var ('lang_login', $LANG_LOGIN[3]);
	    $login->set_var ('lang_newuser', $LANG_LOGIN[4]);
	    $login->parse ('output', 'login');
	    $display .= $login->finish ($login->get_var('output'));
	    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
	    $display .= COM_siteFooter();
	    echo $display;
	    exit;
	}

	MG_index();
}

/*
 * Helper functions for executing external programs...
 */

function MG_exec($cmd) {
    global $_CONF, $_MG_CONF;

    $debugfile = "";
    $status="";
    $results=array();
    if ($_MG_CONF['verbose'] ) {
        COM_errorLog(sprintf("MG_exec: Executing: %s",$cmd));
    }

    $debugfile = $_CONF['path'] . 'logs/debug.log';

    if (PHP_OS == "WINNT") {
        $cmd .= ">NUL 2>&1";
//        exec("cmd.exe /c " . $cmd, $results, $status);
        exec('"' . $cmd . '"',$results,$status);
    } else {
        exec($cmd, $results, $status);
    }

    return array($results, $status);
}

function MG_execWrapper($cmd) {

    list($results, $status) = MG_exec($cmd);

    if ( $status == 0 ) {
        return true;
    } else {
        COM_errorLog("MG_execWrapper: Failed Command: " . $cmd);
        return false;
    }
}

require_once $_CONF['path'] . '/system/imglib/lib-image.php';

/*
 * Get mime type of image
 */

function MG_getMediaMetaData( $filename ) {

    return IMG_getMediaMetaData( $filename );
}

/*
 * Generic resize image function
 */

function MG_resizeImage($srcImage, $destImage, $dImageHeight, $dImageWidth, $mimeType='', $deleteSrc=0 ) {

    return IMG_resizeImage($srcImage, $destImage, $dImageHeight, $dImageWidth, $mimeType, $deleteSrc );
}

/*
 * Rotates an image left or right 90 degrees
 */

function MG_rotateImage( $srcImage, $direction ) {
    return IMG_rotateImage( $srcImage, $direction );
}

/*
 * convert an image from one format to another
 */

function MG_convertImageFormat ( $srcImage, $destImage, $destFormat, $deleteOriginal=1 ) {

    return IMG_convertImageFormat ( $srcImage, $destImage, $destFormat, $deleteOriginal );
}

function MG_watermarkImage( $origImage, $watermarkImage, $opacity, $location ) {
    return IMG_watermarkImage( $origImage, $watermarkImage, $opacity, $location );
}
?>