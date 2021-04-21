<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Media Gallery Search
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';

use \glFusion\Log\Log;

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
    $display = MG_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

$pageBody = '';

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
MG_initAlbums();

function MG_displaySearchBox($msg = '') {
    global $_CONF, $_MG_CONF, $_TABLES, $LANG_MG01, $LANG_MG03;

    $retval = '';

    $T = new Template( MG_getTemplatePath(0) );
    $T->set_file ('search','search.thtml');

    $cat_select = '<select name="cat_id">';
    $cat_select .= '<option value="">' . $LANG_MG03['all_categories'] . '</option>';
    $result = DB_query("SELECT * FROM {$_TABLES['mg_category']} ORDER BY cat_id ASC");
    $nRows = DB_numRows($result);
    for ( $i=0; $i < $nRows; $i++ ) {
        $row = DB_fetchArray($result);
        $cat_select .= '<option value="' . $row['cat_id'] . '">' . $row['cat_name'] . '</option>';
    }
    $cat_select .= '</select>';
    $keytype = '<select name="keyType">';
    $keytype .= '<option value="phrase">' . $LANG_MG03['exact_phrase'] . '</option>';
    $keytype .= '<option value="all">' . $LANG_MG03['all'] . '</option>';
    $keytype .= '<option value="any">' . $LANG_MG03['any'] . '</option>';
    $keytype .= '</select>';

    $swhere = '<select name="swhere">';
    $swhere .= '<option value="0">' . $LANG_MG03['title_desc_keywords'] . '</option>';
    $swhere .= '<option value="1">' . $LANG_MG03['keywords_only'] . '</option>';
    $swhere .= '<option value="2">' . $LANG_MG03['title_desc_only'] . '</option>';
    $swhere .= '<option value="3">' . $LANG_MG01['artist'] . '</option>';
    $swhere .= '<option value="4">' . $LANG_MG01['music_album'] . '</option>';
    $swhere .= '<option value="5">' . $LANG_MG01['genre'] . '</option>';
    $swhere .= '</select>';

    $nresults = '<select name="numresults">';
    $nresults .= '<option value="10">10</option>';
    $nresults .= '<option value="20">20</option>';
    $nresults .= '<option value="30">30</option>';
    $nresults .= '<option value="40">40</option>';
    $nresults .= '<option value="50">50</option>';
    $nresults .= '</select>';

    $userselect = '<select name="uid">';
    $userselect .= '<option value="0">' . $LANG_MG01['all_users'] . '</option>';
    $sql = "SELECT uid,username,fullname FROM {$_TABLES['users']} WHERE uid > 1 ORDER BY username";
    $result = DB_query($sql);
    while ($U = DB_fetchArray($result) ) {
	    $userselect .= '<option value="' . $U['uid'] . '">' . COM_getDisplayName($U['uid']) . '</option>' . LB;
    }
    $userselect .= '</select>';

    $T->set_var(array(
        'msg'               => $msg,
        's_form_action'     => $_MG_CONF['site_url'] . '/search.php',
        'mode'              => 'search',
        'action'            => '',
        'cat_select'        => $cat_select,
        'keytype_select'    => $keytype,
        'swhere_select'     => $swhere,
        'nresults_select'   => $nresults,
        'user_select'		=> $userselect,
        'lang_search_title' => $LANG_MG03['advanced_search'],
        'lang_search_query' => $LANG_MG03['search_query'],
        'lang_search_help'  => $LANG_MG03['search_help'],
        'lang_options'      => $LANG_MG03['options'],
        'lang_keywords'     => $LANG_MG03['keywords'],
        'lang_category'     => $LANG_MG03['category'],
        'lang_all_fields'   => $LANG_MG03['all_fields'],
        'lang_keyword_only' => $LANG_MG03['keywords_only'],
        'lang_return_results' => $LANG_MG03['return_results'],
        'lang_search_for'   => $LANG_MG03['search_for'],
        'lang_search_in'    => $LANG_MG03['search_in'],
        'lang_results'      => $LANG_MG03['results'],
        'lang_per_page'     => $LANG_MG03['per_page'],
        'lang_search'       => $LANG_MG01['search'],
        'lang_cancel'       => $LANG_MG01['cancel'],
        'lang_user'			=> $LANG_MG01['select_user'],
    ));

    $T->parse('output','search');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
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
function MG_search($id,$page) {
    global $MG_albums, $_USER, $_TABLES, $_CONF, $_MG_CONF, $LANG_MG00, $LANG_MG01,$LANG_MG03;

    $retval = '';

    $columns_per_page = $_MG_CONF['search_columns'];
    $rows_per_page    = $_MG_CONF['search_rows'];
    $media_per_page   = $columns_per_page * $rows_per_page;
    $playback_type    = $_MG_CONF['search_playback_type'];

    $current_print_page = $page;

    // pull the query from the search database...

    $result = DB_query("SELECT * FROM {$_TABLES['mg_sort']} WHERE sort_id='" . DB_escapeString($id) . "'");
    $nrows  = DB_numRows($result);
    if ( $nrows < 1 ) {
        return(MG_displaySearchBox('<div class="pluginAlert">' . $LANG_MG03['no_search_found'] . '</div>'));
    }
    $S = DB_fetchArray($result);

    if ( COM_isAnonUser() ) {
        $sort_user = 1;
    } else {
        $sort_user = $_USER['uid'];
    }
    if ($sort_user != $S['sort_user'] && $S['sort_user'] != 1 ) {
        return(MG_displaySearchBox('<div class="pluginAlert">' . $LANG_MG03['no_search_found'] . '</div>'));
    }
    $sqltmp = $S['sort_query'];
    $numresults = $S['sort_results'];

	$numresults  = $media_per_page;

    $sql = "SELECT DISTINCT * FROM " .
            $_TABLES['mg_media'] . " as m " .
            " INNER JOIN " . $_TABLES['mg_media_albums'] . " as ma " .
            " ON m.media_id=ma.media_id " . $sqltmp . " ORDER BY m.media_time DESC;";

    $result = DB_query($sql);

    $mycount = DB_numRows($result);

    if ( $mycount < 1 ) {
        return(MG_displaySearchBox('<div class="pluginAlert">' . $LANG_MG03['no_search_found'] . '</div>'));
    }

    $arrayCounter = 0;
    $mediaRows = 0;
    if ( $mycount > 0 ) {
        for ($i=0; $i < $mycount; $i++)     {
            $row = DB_fetchArray( $result );
            if ( $MG_albums[$row['album_id']]->access == 0 || ($MG_albums[$row['album_id']]->hidden == 1 && $MG_albums[0]->owner_id != 1) ) {
                continue;
            }
            $media = new Media();
            $media->constructor($row,$row['album_id']);
            $MG_media[$arrayCounter] = $media;
            $M[$arrayCounter] = $row;
            $arrayCounter++;
            $mediaRows++;
        }
    }
    if ( $mediaRows == 0 ) {
        return(MG_displaySearchBox('<div class="pluginAlert">' . $LANG_MG03['no_search_found'] . '</div>'));
    }

    $page  = $page - 1;
    $begin = $page * $numresults;
    $end   = ($page * $numresults) + ($numresults - 1);

    $total_print_pages = ceil($mediaRows / $numresults);

    // new stuff
    $T = new Template( MG_getTemplatePath(0) );
    $T->set_file (array(
        'page'      => 'search_results2.thtml',
    ));

    $T->set_var(array(
        'site_url'              => $_MG_CONF['site_url'],
        'table_columns'         => $columns_per_page,
        'table_column_width'    => intval(100 / $columns_per_page) . '%',
        'top_pagination'        => COM_printPageNavigation($_MG_CONF['site_url'] . '/search.php?id=' . $id, $page+1,ceil($mediaRows / $numresults),'&amp;page='),
        'bottom_pagination'     => COM_printPageNavigation($_MG_CONF['site_url'] . '/search.php?id=' . $id, $page+1,ceil($mediaRows / $numresults),'&amp;page='),
        'page_number'           => sprintf("%s %d %s %d",$LANG_MG03['page'], $current_print_page, $LANG_MG03['of'], $total_print_pages),
        'lang_search_results'   => $LANG_MG03['search_results'],
        'lang_return_to_index'  => $LANG_MG03['return_to_index'],
        'return_url'            => $S['referer'] == '' ? $_MG_CONF['site_url'] : htmlentities($S['referer'], ENT_QUOTES, COM_getEncodingt()),
        'search_keywords'       => $S['keywords'],
        'lang_search'           => $LANG_MG01['search'],
    ));

    $howmany = $mediaRows - ($page * $numresults);
    if ( $howmany > $mediaRows )
        $howmany = $mediaRows;

    $total_media = $mediaRows;
    if ( $howmany == 0 ) {
        $T->set_var(array(
            'lang_no_image'       =>  $LANG_MG03['no_media_objects']
        ));
        $T->parse('album_noimages', 'noitems');
    }

    $noParse = 0;
    if ( $howmany > 0 ) {
        $k = 0;

        $T->set_block('page', 'ImageColumn', 'IColumn');
        $T->set_block('page', 'ImageRow','IRow');

    	for ( $i = $begin; $i < $media_per_page + $begin; $i += $columns_per_page ) {
            for ($j = $i; $j < ($i + $columns_per_page); $j++) {
                if ($j >= $total_media)
                {
                    $k = ($i+$columns_per_page) - $j;
                    $m = $k % $columns_per_page;
                    for ( $z = $m; $z > 0; $z--) {
                        $T->set_var(array(
                            'CELL_DISPLAY_IMAGE'  =>  '',
                        ));
                        $T->parse('IColumn', 'ImageColumn',true);
                    }
                    $noParse = 1;
	                break;
                }
                $previous_image = $i - 1;
                if ( $previous_image < 0 )
                {
                    $previous_image = -1;
                }
                $next_image = $i + 1;
                if ( $next_image >= $total_media - 1 )
                {
                    $next_image = -1;
                }
                $z = $j; // +$start;
                $celldisplay = MG_searchDisplayThumb($M[$j],0,$id,$page+1);

                if ( $MG_media[$j]->type == 1 ) {
                    $PhotoURL = '';
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $MG_media[$j]->filename[0] .'/' . $MG_media[$j]->filename . $ext) ) {
                            $PhotoURL = $_MG_CONF['mediaobjects_url'] . '/disp/' . $MG_media[$j]->filename[0] .'/' . $MG_media[$j]->filename . $ext;
                            break;
                        }
                    }
                    $T->set_var(array(
                        'URL' => $PhotoURL,
                    ));
                }
                $T->set_var(array(
                    'CELL_DISPLAY_IMAGE'  =>  $celldisplay,
                ));
                $T->parse('IColumn', 'ImageColumn',true);
            }
            $T->parse('IRow','ImageRow',true);
            $T->set_var('IColumn','');
            if ( $noParse == 1 )
                break;
        }
    }
    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}


function MG_searchDisplayThumb( $M, $sortOrder, $id, $page, $force=0 ) {
    global $_CONF, $_USER, $_MG_CONF, $MG_albums, $_TABLES, $_MG_USERPREFS, $LANG_MG03, $LANG_MG01, $ratedIds;

    $playback_type = $_MG_CONF['search_playback_type'];

    $retval = '';

	$nFrame = new mgFrame();
	$nFrame->constructor( 'mgShadow' );
	$imageFrameTemplate = $nFrame->getTemplate();
	$frWidth = $nFrame->frame['wHL'] + $nFrame->frame['wHR'];
	$frHeight = $nFrame->frame['hVT'] + $nFrame->frame['hVB'];

    $T = new Template( MG_getTemplatePath(0) );

    $T->set_file (array(
        'media_cell_image'      => 'album_page_body_media_cell.thtml',
        'media_rate_results'    => 'album_page_body_media_cell_rating.thtml',
        'media_comments'        => 'album_page_body_media_cell_comment.thtml',
        'media_views'           => 'album_page_body_media_cell_view.thtml',
        'media_cell_keywords'   => 'album_page_body_media_cell_keywords.thtml',
        'mp3_podcast'			=> 'mp3_podcast.thtml',
    ));

    $F = new Template($_MG_CONF['template_path']);
    $F->set_var('media_frame',$imageFrameTemplate); //$MG_albums[0]->imageFrameTemplate);
    // --- set the default thumbnail

    $default_thumbnail = 'generic.png';
    switch( $M['media_type'] ) {
        case 0 :    // standard image
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $M['media_filename'][0] . '/' . $M['media_filename'] . $ext) ) {
                    $default_thumbnail = 'tn/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $ext;
                    break;
                }
            }
            break;
        case 1 :    // video file
            switch ( $M['mime_type'] ) {
                case 'video/x-flv' :
                    $default_thumbnail = 'flv.png';
                    break;
                case 'application/x-shockwave-flash' :
                    $default_thumbnail = 'flash.png';
                    break;
                case 'video/mpeg' :
                case 'video/x-mpeg' :
                case 'video/x-mpeq2a' :
    				if ( $_MG_CONF['use_wmp_mpeg'] == 1 ) {
        				$default_thumbnail = 'wmp.png';
        				break;
        			}
                case 'video/x-motion-jpeg' :
                case 'video/quicktime' :
                case 'video/x-qtc' :
                case 'audio/mpeg' :
                    $default_thumbnail = 'quicktime.png';
                    break;
                case 'asf' :
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
                    $default_thumbnail = 'wmp.png';
                    break;
                default :
                    $default_thumbnail = 'video.png';
                    break;
            }
            break;
        case 2 :    // music file
            $default_thumbnail = 'audio.png';
            break;
        case 4 :    // other files
            switch ($M['mime_type']) {
                case 'application/zip' :
                case 'zip' :
                case 'arj' :
                case 'rar' :
                case 'gz'  :
                    $default_thumbnail = 'zip.png';
                    break;
                case 'pdf' :
                case 'application/pdf' :
                    $default_thumbnail = 'pdf.png';
                    break;
                default :
                    switch ( $M['media_mime_ext'] ) {
                        case 'pdf' :
                            $default_thumbnail = 'pdf.png';
                            break;
                        case 'arj' :
                            $default_thumbnail = 'zip.png';
                            break;
                        case 'gz' :
                            $default_thumbnail = 'zip.png';
                            break;
                        default :
                            $default_thumbnail = 'generic.png';
                            break;
                    }
                    break;
            }
            break;
        case 5 :
            case 'embed' :
				if (preg_match("/youtube/i", $M['remote_url'])) {
					$default_thumbnail = 'youtube.png';
				} else if (preg_match("/google/i", $M['remote_url'])) {
					$default_thumbnail = 'googlevideo.png';
				} else {
					$default_thumbnail = 'remote.png';
				}
				break;

    }

    if ( $M['media_tn_attached'] == 1 ) {
        $media_thumbnail_file = '';
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $M['media_filename'][0] . '/tn_' . $M['media_filename'] . $ext) ) {
                $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/tn/'.  $M['media_filename'][0] . '/tn_' . $M['media_filename'] . $ext;
                $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . 'tn/'.  $M['media_filename'][0] . '/tn_' . $M['media_filename'] . $ext;
                break;
            }
        }
        if ( $media_thumbnail_file == '' ) {
            $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
            $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
        }
    } else {
        $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
        $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
    }


    $resolution_x = 0;
    $resolution_y = 0;

    // type == 1 video
    // type == 2 audio
    if ( ($M['media_type'] == 1 || $M['media_type'] == 2 || $M['media_type'] == 5 ) && ($playback_type == 0 || $playback_type == 1) /*&& $_MG_CONF['popup_from_album'] == 1*/) {
        if ( $playback_type == 0 || $playback_type == 1) {
        	if ( $M['media_type'] == 2 ) {
		        // determine what type of player we will use (WMP, QT or Flash)
		        $player = $_MG_CONF['mp3_player'];
		        if ( isset($_MG_USERPREFS['mp3_player']) && $_MG_USERPREFS['mp3_player'] != -1 ) {
			        $player = $_MG_USERPREFS['mp3_player'];
		        }
		        switch ( $player ) {
			        case 0 :	// WMP
			        	$new_y = 60;
			        	$new_x = 350;
			        	break;
			        case 1 :	// QT
			        	$new_y = 25;
			        	$new_x = 350;
			        	break;
			        case 2 :
			        	$new_y = 360;
			        	$new_x = 580;
			        	break;
		        }
		        if ( $M['media_tn_attached'] == 1 && $player != 2) {
			        $tnsize = @getimagesize($media_thumbnail_file);
			        $new_y += $tnsize[0];
			        if ( $tnsize[1] > $new_x ) {
				        $new_x = $tnsize[1];
			        }
		        }
	            if ( $playback_type == 0 ) {
	                $url_display_item = "javascript:showVideo('" . $_MG_CONF['site_url'] . '/video.php?n=' . $M['media_id'] . "'," . $new_y . ',' . $new_x . ')';
	            } else {
	                $url_display_item = $_MG_CONF['site_url'] . '/download.php?mid=' . $MG_media->id;
	            }
	            $resolution_x = $new_x;
	            $resolution_y = $new_y;
            } else { // must be a video...
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

	            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($M['media_id']) . "'");
	            while ( $poRow = DB_fetchArray($poResult) ) {
	                $playback_options[$poRow['option_name']] = $poRow['option_value'];
	            }

	            if ( isset($M['media_resolution_x']) && $M['media_resolution_x'] > 0 ) {
	                $resolution_x = $M['media_resolution_x'];
	                $resolution_y = $M['media_resolution_y'];
	            } else {
	                if ( $M['media_resolution_x'] == 0 && $M['remote_media'] != 1) {
	                    $getID3 = new getID3;
	                    // Analyze file and store returned data in $MG_mediaFileInfo
	                    $MG_mediaFileInfo = $getID3->analyze($_MG_CONF['path_mediaobjects'] . 'orig/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.' . $M['media_mime_ext']);
	                    getid3_lib::CopyTagsToComments($MG_mediaFileInfo);
	                    if ( $MG_mediaFileInfo['video']['resolution_x'] < 1 || $MG_mediaFileInfo['video']['resolution_y'] < 1 ) {
	                        if (isset($MG_mediaFileInfo['meta']['onMetaData']['width']) && isset($MG_mediaFileInfo['meta']['onMetaData']['height']) ) {
	                            $resolution_x = $MG_mediaFileInfo['meta']['onMetaData']['width'];
	                            $resolution_y = $MG_mediaFileInfo['meta']['onMetaData']['height'];
	                        } else {
	                            $resolution_x = -1;
	                            $resolution_y = -1;
	                        }
	                    } else {
	                        $resolution_x = $MG_mediaFileInfo['video']['resolution_x'];
	                        $resolution_y = $MG_mediaFileInfo['video']['resolution_y'];
	                    }
	                    if ( $resolution_x != 0 ) {
	                        $sql = "UPDATE " . $_TABLES['mg_media'] . " SET media_resolution_x=" . $resolution_x . ",media_resolution_y=" . $resolution_y . " WHERE media_id='" . DB_escapeString($M['media_id']) . "'";
	                        DB_query( $sql,1 );
	                    }
	                } else {
	                    $resolution_x = $M['media_resolution_x'];
	                    $resolution_y = $M['media_resolution_y'];
	                }
	            }
	            $resolution_x = $playback_options['width'];
	            $resolution_y = $playback_options['height'];
	            if ( $resolution_x < 1 || $resolution_y < 1 ) {
	                $resolution_x = 480;
	                $resolution_y = 320;
	            } else {
	                $resolution_x = $resolution_x + 40;
	                $resolution_y = $resolution_y + 40;
	            }
            	if ( $M['mime_type'] == 'video/x-flv' && $_MG_CONF['use_flowplayer'] != 1) {
            	    $resolution_x = $resolution_x + 60;
	            	if ( $resolution_x < 590 ) {
		            	$resolution_x = 590;
	            	}
	            	$resolution_y = $resolution_y + 80;
	            	if ( $resolution_y < 500 ) {
	            	    $resolution_y = 500;
	                }
            	}
            	if ( $M['media_type'] == 5 ) {
	            	$resolution_x = 460;
	            	$resolution_y = 380;
            	}
                $url_display_item = "javascript:showVideo('" . $_MG_CONF['site_url'] . '/video.php?n=' . $M['media_id'] . "'," . $resolution_y . ',' . $resolution_x . ')';
            }
        } else {
            $url_display_item = $_MG_CONF['site_url'] . '/download.php?mid=' . $M['media_id'];
        }
        // check to see if comments and rating are enabled, if not, put a link to edit...
        if ( $MG_albums[0]->access == 3 ) {
    		$T->set_var(array(
    			'edit_link'		=> '<br/><a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=mediaedit&amp;s=1&amp;album_id=' . $M['album_id'] . '&amp;mid=' . $M['media_id'] . '">' . $LANG_MG01['edit'] . '</a>',
    		));
		} else {
    		$T->set_var(array(
    			'edit_link'	=> '',
    		));
		}
    } else {
        $url_display_item  = $_MG_CONF['site_url'] . '/media.php?f=0' . '&amp;sort=' . $sortOrder . '&amp;s=' . $M['media_id'] . '&amp;i=' . $id . '&amp;p=' . $page  ;
    }
    if ( $M['media_type'] == 4 ) { // other
        $url_display_item  = $_MG_CONF['site_url'] . '/download.php?mid=' . $M['media_id'] ;
    }

    $media_size        = @getimagesize($media_thumbnail_file);

    if ( $media_size == false ) {
        $default_thumbnail    = 'missing.png';
        $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
        $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
        $media_size           = @getimagesize($media_thumbnail_file);
    }

	if ( $_MG_CONF['use_upload_time'] == 1 ) {
    	$media_time        = MG_getUserDateTimeFormat($M['media_upload_time']);
    } else {
    	$media_time        = MG_getUserDateTimeFormat($M['media_time']);
    }

    $url_media_item = $url_display_item;

    // -- decide what thumbnail size to use, small, medium, large...

    if (isset($_MG_USERPREFS['tn_size']) && $_MG_USERPREFS['tn_size'] != -1 ) {
        $tn_size = $_MG_USERPREFS['tn_size'];
    } else {
        $tn_size = $_MG_CONF['gallery_tn_size'];
    }

    switch ($tn_size ) {
        case '0' :      //small
            $tn_height = 100;
            break;
        case '1' :      //medium
            $tn_height = 150;
            break;
        case '2' :
            $tn_height = 200;
            break;
        case '3' :
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

    if ( $media_size[0] > $media_size[1] ) {
        $ratio = $media_size[0] / 50;
        $smallwidth = 50;
        $smallheight = round($media_size[1] / $ratio);
    } else {
        $ratio = $media_size[1] / 50;
        $smallheight = 50;
        $smallwidth = round($media_size[0] / $ratio);
    }

    if ( $M['media_user_id'] != "" && $M['media_user_id'] > 1 ) {
        $username = DB_getItem($_TABLES['users'],'username',"uid=" . $M['media_user_id']);
    } else {
        $username = 'anonymous';
    }

    if ( $M['mime_type'] == 'audio/mpeg' ) {
    	$T->set_var(array(
    		'play_now'		    => '',
    		'download_now'	    => $_MG_CONF['site_url'] . '/download.php?mid=' . $M['media_id'],
    		'play_in_popup'     => "javascript:showVideo('" . $_MG_CONF['site_url'] . '/video.php?n=' . $M['media_id'] . "'," . $resolution_y . ',' . $resolution_x . ')',
    		'mp3_file'          => $_MG_CONF['mediaobjects_url'] . '/orig/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.' . $M['media_mime_ext'],
            'site_url'			=> $_MG_CONF['site_url'],
            'id'				=> $M['media_mime_ext'] . rand(),
    	));
        $T->parse('mp3_podcast','mp3_podcast');
	} else {
    	$T->set_var(array(
    		'mp3_podcast' => '',
    	));
	}

	/*
	 * build the small rating bar
	 *
	 */

    if ( $MG_albums[$M['album_id']]->enable_rating > 0 ) {
        $ip     = $_SERVER['REMOTE_ADDR'];
        $uid    = COM_isAnonUser() ? 1 : $_USER['uid'];
        $static = false;
        // check to see if we are the owner, if so, no rating for us...
        if (isset($_USER['uid']) && $_USER['uid'] == $M['media_user_id'] ) {
            $static = true;
        } else {
            if (in_array($M['media_id'], $ratedIds)) {
                $static = true;
            } else {
                $static = '';
            }
        }
        if ( $MG_albums[$M['album_id']]->enable_rating == 1 && (COM_isAnonUser() ) ) {
            $static = 'static';
        }
        $rating_box = RATING_ratingBar('mediagallery',$M['media_id'], $M['media_votes'], $M['media_rating'], $static, 5,'','sm');
    } else {
        $rating_box = '';
    }

    $T->set_var('rating_box',$rating_box);

    if ( $M['media_type'] == 0 ) {
        $direct_url = 'disp/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.' . $M['media_mime_ext'];
        if ( !file_exists($_MG_CONF['path_mediaobjects'] . $direct_url) ) {
            $direct_url = $_MG_CONF['mediaobjects_url'] . '/' . 'disp/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.jpg';
        } else {
            $direct_url = $_MG_CONF['mediaobjects_url'] . '/' . $direct_url;
        }
    } else {
        $direct_url = $media_thumbnail;
    }

    if ($MG_albums[$M['album_id']]->access == 3 ) {
        $edit_item = '<a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=mediaedit&amp;s=1&amp;album_id=' . $M['album_id'] . '&amp;mid=' . $M['media_id'] . '">' . $LANG_MG01['edit'] . '</a>';
    } else {
        $edit_item = '';
    }

	$L = new Template( MG_getTemplatePath(0) );
	$L->set_file('media_link','medialink.thtml');
	$L->set_var('href',$url_media_item);
	$L->set_var('hrefdirect',$direct_url);
	$L->set_var('caption',PLG_replaceTags($M['media_title'],'mediagallery','media_title'));
	$L->set_var('id','id' . rand());
	$L->parse('media_link_start','media_link');
	$media_start_link = $L->finish($L->get_var('media_link_start'));

    $T->set_var(array(
        'row_height'        => $tn_height + 40,
        'media_title'       => (isset($M['media_title']) && $M['media_title'] != '' && $M['media_title'] != ' ') ? PLG_replaceTags($M['media_title'],'mediagallery','media_title') : '',
        'media_description' => (isset($M['media_desc']) && $M['media_desc'] != '' && $M['media_desc'] != ' ') ? PLG_replaceTags($M['media_desc'],'mediagallery','media_title') : '',
        'media_tag'         => (isset($M['media_title']) && $M['media_title'] != '' && $M['media_title'] != ' ') ? strip_tags($M['media_title']) : '',
        'media_time'        => $media_time[0],
        'media_owner'		=> $username,
        'site_url'			=> $_MG_CONF['site_url'],
        'lang_published'	=> $LANG_MG03['published'],
        'lang_on'			=> $LANG_MG03['on'],
        'media_link_start'  => '<a href="' . $url_media_item . '">',
        'display_url'       => $url_media_item,
        'media_link_end'    => '</a>',
        'raw_media_thumbnail' => $media_thumbnail,
        'artist'			=> (isset($M['artist']) && $M['artist'] != ' ') ? $M['artist'] : '',
        'musicalbum'		=> (isset($M['album']) && $M['album'] != ' ') ? $M['album'] : '',
        'genre'				=> (isset($M['genre']) && $M['genre'] != ' ') ? $M['genre'] : '',
        'search_album'      => $LANG_MG01['album'] . ': <a href="'.$_MG_CONF['site_url'].'/album.php?aid='.$M['album_id'].'">'.$MG_albums[$M['album_id']]->title.'</a>',
    ));

    // frame template variables
    $F->set_var(array(
        'media_link_start'  => $media_start_link, // '<a href="' . $url_media_item . '">',
        'media_link_end'    => '</a>',
        'url_media_item'    =>  $url_media_item,
        'url_display_item'  =>  $url_display_item,
        'media_thumbnail'   =>  $media_thumbnail,
        'raw_media_thumbnail' => $media_thumbnail,
        'media_size'        =>  'width="' . $newwidth . '" height="' . $newheight . '"',
        'media_height'      =>  $newheight,
        'media_width'       =>  $newwidth,
        'border_width'      =>  $newwidth + 15,
        'border_height'     =>  $newheight + 15,
        'row_height'        =>  $tn_height + 40,
        'frWidth'           =>  $newwidth  - $frWidth,
        'frHeight'          =>  $newheight - $frHeight,
        'media_tag'         =>  strip_tags($M['media_desc']),
        'search_album'      => $LANG_MG01['album'] . ': <a href="'.$_MG_CONF['site_url'].'/album.php?aid='.$M['album_id'].'">'.$MG_albums[$M['album_id']]->title.'</a>',

    ));
    $F->parse('media','media_frame');
    $media_item_thumbnail = $F->finish($F->get_var('media'));

    $T->set_var('media_item_thumbnail',$media_item_thumbnail);

    if ( !empty($M['media_keywords'] ) ) {
        $kwText = '';
        $keyWords = array();
        $keyWords = explode(' ',$M['media_keywords']);
        $numKeyWords = count($keyWords);
        for ( $i=0;$i<$numKeyWords;$i++ ) {
            $keyWords[$i] = str_replace('"',' ',$keyWords[$i]);
            $searchKeyword = $keyWords[$i];
            $keyWords[$i] = str_replace('_',' ',$keyWords[$i]);
            $kwText .= $keyWords[$i] . ' ';
        }

        $T->set_var(array(
            'media_keywords'  =>  $kwText, // $M['media_keywords'],
            'lang_keywords'   =>  $LANG_MG01['keywords'],
        ));
        $T->parse('media_cell_keywords','media_cell_keywords');
    } else {
        $T->set_var('lang_keywords','');
    }

   if ( $_MG_CONF['search_enable_rating'] ) {
        if ( $M['media_type'] == 4 || ($M['media_type'] == 1 && $playback_type != 2) || ($M['media_type'] == 2 && $playback_type != 2)  || ($M['media_type'] == 5 && $playback_type != 2)) {
            $rateLink = '<a href="' . $_MG_CONF['site_url'] . '/media.php?f=0' . '&amp;sort=' . $sortOrder . '&amp;s=' . $M['media_id'] . '">' . $LANG_MG03['rating'] . '</a>';
        } else {
            $rateLink = $LANG_MG03['rating'];
        }
        $rating = $rateLink . ': <strong> ' . $M['media_rating'] / 2 .'</strong>/5 ('.$M['media_votes'] . ' ' . $LANG_MG03['votes'] . ')';
        $T->set_var(array(
            'media_rating'  =>  $rating,
        ));
        $T->parse('media_rate_results','media_rate_results');
    }
    if ( $_MG_CONF['search_enable_views'] ) {
        $T->set_var(array(
            'media_views_count'     =>  $M['media_views'],
            'lang_views'            =>  $LANG_MG03['views']
        ));
        $T->parse('media_views','media_views');
    }

    $T->parse('media_cell','media_cell_image');
    $retval = $T->finish($T->get_var('media_cell'));
    return $retval;
}

/*
* Main Function
*/


if ( isset($_REQUEST['mode']) ) {
    $mode = COM_applyFilter ($_REQUEST['mode']);
} else {
    $mode = '';
}

if ( isset($_SERVER['HTTP_REFERER']) ) {
    $referer = COM_sanitizeUrl($_SERVER['HTTP_REFERER']);
} else {
    $referer = '';
}

$themeStyle = MG_getThemeCSS(0);

if (($mode == $LANG_MG01['search'] && !empty ($LANG_MG01['search'])) || $mode == 'search') {
    $keywords       = isset($_REQUEST['keywords']) ? COM_applyFilter($_REQUEST['keywords']) : '';
    $stype          = isset($_REQUEST['keyType']) ? COM_applyFilter($_REQUEST['keyType']) : '';
    $category       = isset($_REQUEST['cat_id']) ? COM_applyFilter($_REQUEST['cat_id'],1) : 0;
    $skeywords      = isset($_REQUEST['swhere']) ? COM_applyFilter($_REQUEST['swhere'],1) : 1;
    $numresults     = isset($_REQUEST['numresults']) ? COM_applyFilter($_REQUEST['numresults'],true) : 10;
    $users			= isset($_REQUEST['uid']) ? COM_applyFilter($_REQUEST['uid'],true) : 0;
    $sortyby        = 'title';
    $sortdirection  = 'DESC';

    if ( $keywords == '' ) {
        $display  = MG_siteHeader();
        $display .= MG_errorHandler( $LANG_MG03['search_error'] );
        $display .= MG_siteFooter();
        echo $display;
        exit;
    }
    if ( $keywords == '*' ) {
        $keywords = '';
    }
    $keywords = strip_tags($keywords);

    // build the query and put into our database...

    $sqltmp = " WHERE 1=1 ";
    $keywords_db = DB_escapeString($keywords);
    if ( $stype == 'phrase' ) { // search phrase
        switch ( $skeywords ) {
            case 0 :
                $sqltmp .= "AND (m.media_title LIKE '%$keywords_db%' OR m.media_desc LIKE '%$keywords%' OR m.media_keywords LIKE '%$keywords%' OR m.artist LIKE '%$keywords%' OR m.album LIKE '%$keywords%' OR m.genre LIKE '%$keywords%')";
                break;
            case 1 :
                $sqltmp .= "AND (m.media_keywords LIKE '%$keywords_db%')";
                break;
            case 2 :
                $sqltmp .= "AND (m.media_title LIKE '%$keywords_db%' OR m.media_desc LIKE '%$keywords%')";
                break;
            case 3 :
                $sqltmp .= "AND (m.artist LIKE '%$keywords_db%')";
                break;
            case 4 :
                $sqltmp .= "AND (m.album LIKE '%$keywords_db%')";
                break;
            case 5 :
                $sqltmp .= "AND (m.genre LIKE '%$keywords_db%')";
                break;
        }
    } else if ( $stype == 'any') {
        $sqltmp .= ' AND ';
        $tmp = '';
        $mywords = explode( ' ', $keywords );
        foreach( $mywords AS $mysearchitem ) {
            $mysearchitem = DB_escapeString( $mysearchitem );
            switch ( $skeywords ) {
                case 0 :
                    $tmp .= "( m.media_title LIKE '%$mysearchitem%' OR m.media_desc LIKE '%$mysearchitem%' OR m.media_keywords LIKE '%$mysearchitem%' OR m.artist LIKE '%$keywords%' OR m.album LIKE '%$keywords%' OR m.genre LIKE '%$keywords%') OR ";
                    break;
                case 1 :
                    $tmp .= "( m.media_keywords LIKE '%$mysearchitem%') OR ";
                    break;
                case 2 :
                    $tmp .= "( m.media_title LIKE '%$mysearchitem%' OR m.media_desc LIKE '%$mysearchitem%') OR ";
                    break;
                case 3 :
                    $tmp .= "(m.artist LIKE '%$mysearchitem%') OR ";
                    break;
                case 4 :
                    $tmp .= "(m.album LIKE '%$mysearchitem%') OR ";
                    break;
                case 5 :
                    $tmp .= "(m.genre LIKE '%$keywords%') OR ";
                    break;
            }
        }
        $tmp = substr($tmp, 0, strlen($tmp) - 3);
        $sqltmp .= "($tmp)";
    } else if ( $stype == 'all' ) {
        $sqltmp .= 'AND ';
        $tmp = '';
        $mywords = explode( ' ', $keywords );
        foreach( $mywords AS $mysearchitem ) {
            $mysearchitem = DB_escapeString( $mysearchitem );
            switch ( $skeywords ) {
                case 0 :
                    $tmp .= "( m.media_title LIKE '%$mysearchitem%' OR m.media_desc LIKE '%$mysearchitem%' OR m.media_keywords LIKE '%$mysearchitem%' OR m.artist LIKE '%$keywords%' OR m.album LIKE '%$keywords%' OR m.genre LIKE '%$keywords%') AND ";
                    break;
                case 1 :
                    $tmp .= "( m.media_keywords LIKE '%$mysearchitem%') AND ";
                    break;
                case 2 :
                    $tmp .= "( m.media_title LIKE '%$mysearchitem%' OR m.media_desc LIKE '%$mysearchitem%') AND ";
                    break;
                case 3 :
                    $tmp .= "(m.artist LIKE '%$mysearchitem%') AND ";
                    break;
                case 4 :
                    $tmp .= "(m.album LIKE '%$mysearchitem%') AND ";
                    break;
                case 5 :
                    $tmp .= "(m.genre LIKE '%$keywords%') AND ";
                    break;
            }
        }
        $tmp = substr($tmp, 0, strlen($tmp) - 4);
        $sqltmp .= "($tmp)";
    } else {
        $sqltmp = "WHERE (m.media_title LIKE '%$keywords_db%' OR m.media_desc LIKE '%$keywords_db%' OR m.media_keywords LIKE '%$keywords_db%')";
    }

    if ( $category != 0 ) {
        $sqltmp .= " AND m.media_category=" . (int) $category;
    }
    if ( $users > 0 ) {
	    $sqltmp .= " AND m.media_user_id=" . $users;
    }
    $sqltmp = DB_escapeString($sqltmp);

    $sort_id = COM_makesid();
    if ( COM_isAnonUser() ) {
        $sort_user = 1;
    } else {
        $sort_user = $_USER['uid'];
    }
    $sort_datetime = time();

    $referer = DB_escapeString($referer);
    $keywords = DB_escapeString($keywords);

    $sql = "INSERT INTO {$_TABLES['mg_sort']} (sort_id,sort_user,sort_query,sort_results,sort_datetime,referer,keywords)
            VALUES ('$sort_id',$sort_user,'$sqltmp',$numresults,$sort_datetime,'$referer','$keywords')";
    $result = DB_query($sql);
    if ( DB_error() ) {
        Log::write('system',Log::ERROR,"Media Gallery: Error placing sort query into database");
    }

    $sort_purge = time() - 3660; // 43200;
    DB_query("DELETE FROM {$_TABLES['mg_sort']} WHERE sort_datetime < " . $sort_purge);

    $pageBody .= MG_search($sort_id,1);

} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['site_url'] . '/index.php');
    exit;
} elseif (isset($_GET['id']) ) {
    $id = COM_applyFilter($_GET['id']);
    $page = COM_applyFilter($_GET['page'],true);
    if ( $page < 1 )
      $page = 1;
    $pageBody .= MG_search($id,$page);
} else {
    $pageBody .= MG_displaySearchBox('');
}

$display  = MG_siteHeader($LANG_MG00['results']);
$display .= $pageBody;
$display .= MG_siteFooter();
echo $display;

?>