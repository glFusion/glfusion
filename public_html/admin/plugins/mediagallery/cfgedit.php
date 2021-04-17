<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Configuration Editor
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/init.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/classFrame.php';

MG_initAlbums();

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}",1);
    $display  = COM_siteHeader();
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_MG00['access_denied'],true,'error');
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_editConfig( $msgString = '' ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $LANG_DIRECTION,$LANG04;

    $retval = '';
    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file ('admin','cfgedit.thtml');
    $T->set_var('site_url', $_MG_CONF['site_url']);
    if ( $msgString != '' ) {
        $T->set_var('feedback',$msgString);
    }

    if (!isset($_MG_CONF['rating_max']) ) {
        $_MG_CONF['rating_max'] = 5;
    }

    if ( !isset($_MG_CONF['moderation']) ) {
        $_MG_CONF['moderation'] = 0;
    }

    // build our graphics package select...

    $gp_select =  "<select name='graphicspackage'>";
    $gp_select .= "<option value='0'" . ($_MG_CONF['graphicspackage'] == 0 ? ' selected="selected"' : "") . ">ImageMagick</option>";
    $gp_select .= "<option value='1'" . ($_MG_CONF['graphicspackage'] == 1 ? ' selected="selected"' : "") . ">NetPBM</option>";
    $gp_select .= "<option value='2'" . ($_MG_CONF['graphicspackage'] == 2 ? ' selected="selected"' : "") . ">GD Libraries</option>";
    $gp_select .= "</select>";

    $block_select =  '<select name="displayblocks">';
    $block_select .= '<option value="0"' . ($_MG_CONF['displayblocks'] == 0 ? ' selected="selected"' : "") . '>' . $LANG_MG01['left_blocks_only'] . '</option>';
    $block_select .= '<option value="1"' . ($_MG_CONF['displayblocks'] == 1 ? ' selected="selected"' : "") . '>' . $LANG_MG01['right_blocks_only'] . '</option>';
    $block_select .= '<option value="2"' . ($_MG_CONF['displayblocks'] == 2 ? ' selected="selected"' : "") . '>' . $LANG_MG01['left_right_blocks'] . '</option>';
    $block_select .= '<option value="3"' . ($_MG_CONF['displayblocks'] == 3 ? ' selected="selected"' : "") . '>' . $LANG_MG01['none'] . '</option>';
    $block_select .= '</select>';

    $dfid_select   = '<select name="dfid"><option value="99">' . $LANG_MG00['no_date'] . '</option>' . COM_optionList ($_TABLES['dateformats'], 'dfid,description',$_MG_CONF['dfid'],0) . '</select>';

    if ( isset($_MG_CONF['index_all']) && $_MG_CONF['index_all'] == 1 ) {
        $T->set_var('index_all_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('index_all_no_checked', ' checked="checked"');
    }

    if ($_MG_CONF['loginrequired'] == 1 ) {
        $T->set_var('lr_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('lr_no_checked',' checked="checked"');
    }

    if (isset($_MG_CONF['moderation']) && $_MG_CONF['moderation'] == 1 ) {
        $T->set_var('au_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('au_no_checked',' checked="checked"');
    }
    if ($_MG_CONF['htmlallowed'] == 1 ) {
        $T->set_var('ha_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('ha_no_checked',' checked="checked"');
    }

    if ($_MG_CONF['whatsnew'] == 1 ) {
        $T->set_var('wn_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('wn_no_checked',' checked="checked"');
    }

    if ($_MG_CONF['usage_tracking'] == 1 ) {
        $T->set_var('ut_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('ut_no_checked',' checked="checked"');
    }
    if ($_MG_CONF['preserve_filename'] == 1 ) {
        $T->set_var('pf_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('pf_no_checked',' checked="checked"');
    }
    if ($_MG_CONF['discard_original'] == 1 ) {
        $T->set_var('do_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('do_no_checked',' checked="checked"');
    }
    if ($_MG_CONF['verbose'] == 1 ) {
        $T->set_var('verbose_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('verbose_no_checked',' checked="checked"');
    }
    if ($_MG_CONF['disable_whatsnew_comments'] == 1 ) {
        $T->set_var('dwnc_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('dwnc_no_checked',' checked="checked"');
    }
    if ($_MG_CONF['enable_media_id'] == 1 ) {
        $T->set_var('emid_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('emid_no_checked',' checked="checked"');
    }
    if ($_MG_CONF['full_in_popup'] == 1 ) {
        $T->set_var('fip_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('fip_no_checked',' checked="checked"');
    }
    if ($_MG_CONF['commentbar'] == 1 ) {
        $T->set_var('cmtbar_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('cmtbar_no_checked',' checked="checked"');
    }
    if ($_MG_CONF['profile_hook'] == 1 ) {
        $T->set_var('ph_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('ph_no_checked',' checked="checked"');
    }
    if ($_MG_CONF['subalbum_select'] == 1 ) {
        $T->set_var('sa_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('sa_no_checked', ' checked="checked"');
    }
    $T->set_var('wn_length', $_MG_CONF['title_length']);

    // -- auto tag defaults
    if ( $_MG_CONF['at_border'] == 1 ) {
        $T->set_var('at_border_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('at_border_no_checked', ' checked="checked"');
    }
    if ( $_MG_CONF['at_autoplay'] == 1 ) {
        $T->set_var('at_autoplay_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('at_autoplay_no_checked', ' checked="checked"');
    }
    if ( $_MG_CONF['at_enable_link'] == 2 ) {
        $T->set_var('at_enable_link_lb_checked', ' checked="checked"');
    } elseif ( $_MG_CONF['at_enable_link'] == 1 ) {
        $T->set_var('at_enable_link_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('at_enable_link_no_checked', ' checked="checked"');
    }
    if ( $_MG_CONF['at_showtitle'] == 1 ) {
        $T->set_var('at_showtitle_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('at_showtitle_no_checked', ' checked="checked"');
    }

    if ( $_MG_CONF['search_enable_views'] == 1 ) {
        $T->set_var('search_enable_views_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('search_enable_views_no_checked', ' checked="checked"');
    }
    if ( $_MG_CONF['search_enable_rating'] == 1 ) {
        $T->set_var('search_enable_rating_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('search_enable_rating_no_checked', ' checked="checked"');
    }
    if ( $_MG_CONF['gallery_only'] == 1 ) {
        $T->set_var('gallery_only_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('gallery_only_no_checked', ' checked="checked"');
    }

    $search_playback_type  = '<select name="search_playback_type">';
    $search_playback_type .= '<option value="0"' . ($_MG_CONF['search_playback_type']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['play_in_popup'] . '</option>';
    $search_playback_type .= '<option value="1"' . ($_MG_CONF['search_playback_type']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['download_to_local'] . '</option>';
    $search_playback_type .= '<option value="2"' . ($_MG_CONF['search_playback_type']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['play_inline'] . '</option>';
    $search_playback_type .= '<option value="3"' . ($_MG_CONF['search_playback_type']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['use_mms'] . '</option>';
    $search_playback_type .= '</select>';

    $at_align_select =  '<select name="at_align">';
    $at_align_select .= '<option value="none"' . ($_MG_CONF['at_align'] == 'none' ? ' selected="selected"' : "") . '>' . $LANG_MG01['none'] . '</option>';
    $at_align_select .= '<option value="auto"' . ($_MG_CONF['at_align'] == 'auto' ? ' selected="selected"' : "") . '>' . $LANG_MG01['auto'] . '</option>';
    $at_align_select .= '<option value="left"' . ($_MG_CONF['at_align'] == 'left' ? ' selected="selected"' : "") . '>' . $LANG_MG01['left'] . '</option>';
    $at_align_select .= '<option value="right"' .($_MG_CONF['at_align'] == 'right' ? ' selected="selected"' : "") . '>' . $LANG_MG01['right'] . '</option>';
    $at_align_select .= '<option value="center"' . ($_MG_CONF['at_align'] == 'center' ? ' selected="selected"' : "") . '>' . $LANG_MG01['center'] . '</option>';
    $at_align_select .= '</select>';

    $at_src_select =  '<select name="at_src">';
    $at_src_select .= '<option value="tn"' . ($_MG_CONF['at_src'] == 'tn' ? ' selected="selected"' : "") . '>' . $LANG_MG01['thumbnail'] . '</option>';
    $at_src_select .= '<option value="disp"' . ($_MG_CONF['at_src'] == 'disp' ? ' selected="selected"' : "") . '>' . $LANG_MG01['display_image'] . '</option>';
    $at_src_select .= '<option value="orig"' . ($_MG_CONF['at_src'] == 'orig' ? ' selected="selected"' : "") . '>' . $LANG_MG01['original_image'] . '</option>';
    $at_src_select .= '</select>';


    $wn_time_select =  '<select name="whatsnew_time">';
    $wn_time_select .= '<option value="1"' . ($_MG_CONF['whatsnew_time'] == 1 ? ' selected="selected"' : "") . '>1 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="2"' . ($_MG_CONF['whatsnew_time'] == 2 ? ' selected="selected"' : "") . '>2 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="3"' . ($_MG_CONF['whatsnew_time'] == 3 ? ' selected="selected"' : "") . '>3 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="4"' . ($_MG_CONF['whatsnew_time'] == 4 ? ' selected="selected"' : "") . '>4 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="5"' . ($_MG_CONF['whatsnew_time'] == 5 ? ' selected="selected"' : "") . '>5 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="6"' . ($_MG_CONF['whatsnew_time'] == 6 ? ' selected="selected"' : "") . '>6 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="7"' . ($_MG_CONF['whatsnew_time'] == 7 ? ' selected="selected"' : "") . '>7 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="8"' . ($_MG_CONF['whatsnew_time'] == 8 ? ' selected="selected"' : "") . '>8 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="9"' . ($_MG_CONF['whatsnew_time'] == 9 ? ' selected="selected"' : "") . '>9 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="10"' . ($_MG_CONF['whatsnew_time'] == 10 ? ' selected="selected"' : "") . '>10 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="11"' . ($_MG_CONF['whatsnew_time'] == 11 ? ' selected="selected"' : "") . '>11 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="12"' . ($_MG_CONF['whatsnew_time'] == 12 ? ' selected="selected"' : "") . '>12 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="13"' . ($_MG_CONF['whatsnew_time'] == 13 ? ' selected="selected"' : "") . '>13 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="14"' . ($_MG_CONF['whatsnew_time'] == 14 ? ' selected="selected"' : "") . '>14 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="15"' . ($_MG_CONF['whatsnew_time'] == 15 ? ' selected="selected"' : "") . '>15 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="16"' . ($_MG_CONF['whatsnew_time'] == 16 ? ' selected="selected"' : "") . '>16 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="17"' . ($_MG_CONF['whatsnew_time'] == 17 ? ' selected="selected"' : "") . '>17 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="18"' . ($_MG_CONF['whatsnew_time'] == 18 ? ' selected="selected"' : "") . '>18 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="19"' . ($_MG_CONF['whatsnew_time'] == 19 ? ' selected="selected"' : "") . '>19 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="20"' . ($_MG_CONF['whatsnew_time'] == 20 ? ' selected="selected"' : "") . '>20 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="21"' . ($_MG_CONF['whatsnew_time'] == 21 ? ' selected="selected"' : "") . '>21 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="22"' . ($_MG_CONF['whatsnew_time'] == 22 ? ' selected="selected"' : "") . '>22 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="23"' . ($_MG_CONF['whatsnew_time'] == 23 ? ' selected="selected"' : "") . '>23 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="24"' . ($_MG_CONF['whatsnew_time'] == 24 ? ' selected="selected"' : "") . '>24 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="25"' . ($_MG_CONF['whatsnew_time'] == 25 ? ' selected="selected"' : "") . '>25 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="26"' . ($_MG_CONF['whatsnew_time'] == 26 ? ' selected="selected"' : "") . '>26 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="27"' . ($_MG_CONF['whatsnew_time'] == 27 ? ' selected="selected"' : "") . '>27 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="28"' . ($_MG_CONF['whatsnew_time'] == 28 ? ' selected="selected"' : "") . '>28 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="29"' . ($_MG_CONF['whatsnew_time'] == 29 ? ' selected="selected"' : "") . '>29 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '<option value="30"' . ($_MG_CONF['whatsnew_time'] == 30 ? ' selected="selected"' : "") . '>30 ' . $LANG_MG01['days'] . '</option>';
    $wn_time_select .= '</select>';

    $rating_select =  '<select name="rating_max">';
    $rating_select .= '<option value="5"'  . ($_MG_CONF['rating_max'] == 5 ? ' selected="selected"' : "") . '>5</option>';
    $rating_select .= '<option value="10"' . ($_MG_CONF['rating_max'] == 10 ? ' selected="selected"' : "") . '>10</option>';
    $rating_select .= '<option value="15"' . ($_MG_CONF['rating_max'] == 15 ? ' selected="selected"' : "") . '>15</option>';
    $rating_select .= '<option value="20"' . ($_MG_CONF['rating_max'] == 20 ? ' selected="selected"' : "") . '>20</option>';
    $rating_select .= '</select>';

    $gallery_tn_size_select  = '<select name="gallery_tn_size">';
    $gallery_tn_size_select .= '<option value="0"' . ($_MG_CONF['gallery_tn_size']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['small'] . '</option>';
    $gallery_tn_size_select .= '<option value="1"' . ($_MG_CONF['gallery_tn_size']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['medium'] . '</option>';
    $gallery_tn_size_select .= '<option value="2"' . ($_MG_CONF['gallery_tn_size']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['large'] . '</option>';
    $gallery_tn_size_select .= '<option value="3"' . ($_MG_CONF['gallery_tn_size']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['custom'] . '</option>';
    $gallery_tn_size_select .= '<option value="4"' . ($_MG_CONF['gallery_tn_size']==4 ? 'selected="selected"' : '') . '>' . $LANG_MG01['square'] . '</option>';
    $gallery_tn_size_select .= '</select>';

    $gallery_tnheight_input = '<input type="text" size="3" name="tnheight" value="' . $_MG_CONF['gallery_tn_height'] . '" />';
    $gallery_tnwidth_input  = '<input type="text" size="3" name="tnwidth" value="'  . $_MG_CONF['gallery_tn_width'] . '" />';

    $mp3_select  = '<select name="mp3_player">';
    $mp3_select .= '<option value="0"' . ($_MG_CONF['mp3_player']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['windows_media_player'] . '</option>';
    $mp3_select .= '<option value="1"' . ($_MG_CONF['mp3_player']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['quicktime'] . '</option>';
    $mp3_select .= '<option value="2"' . ($_MG_CONF['mp3_player']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['flashplayer'] . '</option>';
    $mp3_select .= '</select>';

    $flv_select  = '<select name="use_flowplayer">';
    $flv_select .= '<option value="0"' . ($_MG_CONF['use_flowplayer']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['mgflv'] . '</option>';
    $flv_select .= '<option value="1"' . ($_MG_CONF['use_flowplayer']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['flowplayer'] . '</option>';
    $flv_select .= '</select>';

    $T->set_var(array(
        'popupfromalbum_selected'  => $_MG_CONF['popup_from_album'] ? ' checked="checked"' : '',
        'autotag_caption_selected' => $_MG_CONF['autotag_caption'] ? ' checked="checked"' : '',
        'up_dr_selected'        => $_MG_CONF['up_display_rows_enabled'] ? ' checked="checked"' : '',
        'up_dc_selected'        => $_MG_CONF['up_display_columns_enabled'] ? ' checked="checked"' : '',
        'up_mp3_selected'       => $_MG_CONF['up_mp3_player_enabled'] ? ' checked="checked"' : '',
        'up_playback_selected'  => $_MG_CONF['up_av_playback_enabled'] ? ' checked="checked"' : '',
        'up_tn_size_selected'   => $_MG_CONF['up_thumbnail_size_enabled'] ? ' checked="checked"' : '',
        'jh_selected'           => $_MG_CONF['jhead_enabled'] ? ' checked="checked"' : '',
        'jt_selected'           => $_MG_CONF['jpegtran_enabled'] ? ' checked="checked"' : '',
        'zip_selected'          => $_MG_CONF['zip_enabled'] ? ' checked="checked"' : '',
        'ffmpeg_selected'       => $_MG_CONF['ffmpeg_enabled'] ? ' checked="checked"' : '',
        'at_align_select'       => $at_align_select,
        'at_width'              => $_MG_CONF['at_width'],
        'at_height'             => $_MG_CONF['at_height'],
        'at_src_select'         => $at_src_select,
        'at_delay'              => $_MG_CONF['at_delay'],
        'flv_select'            => $flv_select,
        'search_playback_type'  => $search_playback_type,
        'search_columns'        => $_MG_CONF['search_columns'],
        'search_rows'           => $_MG_CONF['search_rows'],
    ));

    if ( $_MG_CONF['up_display_rows_enabled'] == 1 ) {
        $T->set_var('up_dr_yes_checked', ' checked="checked"');
    } else {
        $T->set_var('up_dr_no_checked', ' checked="checked"');
    }

    if ( ini_get('safe_mode') != 1 && $_MG_CONF['skip_file_find'] == 0 ) {  // don't check in safe mode, the file_exists() will fail
        if (PHP_OS == "WINNT") {
            $binary = "/unzip.exe";
        } else {
            $binary = "/unzip";
        }
        clearstatcache();
        if ( file_exists( $_MG_CONF['zip_path'] . $binary ) ) {
            // do nothing..
        } else {
            clearstatcache();
            $_MG_CONF['zip_path'] = '/usr/bin';
            if ( file_exists( $_MG_CONF['zip_path'] . $binary ) ) {
                // do nothing..
            } else {
                clearstatcache();
                $_MG_CONF['zip_path'] = '/usr/local/bin';
                if ( file_exists( $_MG_CONF['zip_path'] . $binary ) ) {
                    // do nothing..
                } else {
                    clearstatcache();
                    $_MG_CONF['zip_path'] = '/usr/X11R6/bin';
                    if ( file_exists( $_MG_CONF['zip_path'] . $binary ) ) {
                        // do nothing..
                    }
                }
            }
        }

        if (PHP_OS == "WINNT") {
            $binary = "/ffmpeg.exe";
        } else {
            $binary = "/ffmpeg";
        }

        clearstatcache();
        if ( file_exists( $_MG_CONF['ffmpeg_path'] . $binary ) ) {
            // do nothing..
        } else {
            clearstatcache();
            $_MG_CONF['ffmpeg_path'] = '/usr/bin';
            if ( file_exists( $_MG_CONF['ffmpeg_path'] . $binary ) ) {
                // do nothing..
            } else {
                clearstatcache();
                $_MG_CONF['ffmpeg_path'] = '/usr/local/bin';
                if ( file_exists( $_MG_CONF['ffmpeg_path'] . $binary ) ) {
                    // do nothing..
                } else {
                    clearstatcache();
                    $_MG_CONF['ffmpeg_path'] = '/usr/X11R6/bin';
                    if ( file_exists( $_MG_CONF['ffmpeg_path'] . $binary ) ) {
                        // do nothing..
                    }
                }
            }
        }
    }

    $frames = new mgFrame();
    $skins = array();
    $skins = $frames->getFrames();

    if ( !isset($_MG_CONF['random_skin']) || $_MG_CONF['random_skin'] == '' ) {
        $_MG_CONF['random_skin'] = 'mgShadow';
    }

    $skin_select = '<select name="skin">';
    $rskin_select = '<select name="rskin">';
    for ( $i=0; $i < count($skins); $i++ ) {
        $skin_select .= '<option value="' . $skins[$i]['dir'] . '"' . ($_MG_CONF['indexskin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
        $rskin_select .= '<option value="' . $skins[$i]['dir'] . '"' . ($_MG_CONF['random_skin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
    }
    $skin_select .= '</select>';
    $rskin_select .= '</select>';

    $themes = array();
    $themes = MG_getThemes();
    $theme_select = '<select name="theme">';
    for ($i=0;$i < count($themes); $i++ ) {
		$theme_select .= '<option value="' . $themes[$i] . '"' . ($_MG_CONF['indextheme'] == $themes[$i] ? ' selected="selected" ': '') .'>' . $themes[$i] .  '</option>';
	}
	$theme_select .= '</select>';

    $navbar = new navbar;
    $navbar->add_menuitem($LANG_MG01['general_options'],'showhideMGAdminEditorDiv("general",0);return false;',true);
    $navbar->add_menuitem($LANG_MG01['display_options'],'showhideMGAdminEditorDiv("display",1);return false;',true);
    $navbar->add_menuitem($LANG_MG01['batch_options'],'showhideMGAdminEditorDiv("batch",2);return false;',true);
    $navbar->add_menuitem($LANG_MG01['up_overrides'],'showhideMGAdminEditorDiv("userprefs",3);return false;',true);
    $navbar->add_menuitem($LANG_MG01['graphicspackage_prompt'],'showhideMGAdminEditorDiv("graphics",4);return false;',true);
    $navbar->set_selected($LANG_MG01['general_options']);
    $T->set_var ('navbar', $navbar->generate());

    $T->set_var(array(
        'no_javascript_warning'     => $LANG04[150],
        'lang_config_title'         => $LANG_MG01['configuration_title'],
        'lang_config_help'          => $LANG_MG01['configuration_help'],
        'lang_config_header'        => $LANG_MG01['configuration_header'],
        'lang_yes'                  => $LANG_MG01['yes'],
        'lang_no'                   => $LANG_MG01['no'],
        'lang_save'                 => $LANG_MG01['save'],
        'lang_cancel'               => $LANG_MG01['cancel'],
        'lang_reset'                => $LANG_MG01['reset'],
        'lang_album_columns'        => $LANG_MG01['album_display_columns_prompt'],
        'lang_album_colums_help'    => $LANG_MG01['album_display_columns_help'],
        'lang_album_rows'           => $LANG_MG01['album_display_rows'],
        'lang_subalbum_select'      => $LANG_MG01['subalbum_select'],
        'lang_loginrequired'        => $LANG_MG01['loginrequired_prompt'],
        'lang_loginrequired_help'   => $LANG_MG01['loginrequired_help'],
        'lang_anonymous_uploads'    => $LANG_MG01['anonymous_uploads_prompt'],
        'lang_anonymous_uploads_help'    => $LANG_MG01['anonymous_uploads_help'],
        'lang_comments'             => $LANG_MG01['comments_prompt'],
        'lang_comments_help'        => $LANG_MG01['comments_help'],
        'lang_graphicspackage'      => $LANG_MG01['graphicspackage_prompt'],
        'lang_graphicspackage_help' => $LANG_MG01['graphicspackage_help'],
        'lang_gp_path'              => $LANG_MG01['graphicspackage_path_prompt'],
        'lang_gp_path_help'         => $LANG_MG01['graphicspackage_path_help'],
        'lang_userpref_options'     => $LANG_MG01['up_overrides'],
        'lang_display_rows_enabled' => $LANG_MG01['up_rows_override'],
        'lang_display_columns_enabled' => $LANG_MG01['up_columns_override'],
        'lang_mp3_player_enabled'   => $LANG_MG01['up_mp3_override'],
        'lang_av_playback_enabled'  => $LANG_MG01['up_av_override'],
        'lang_thumbnail_size_enabled' => $LANG_MG01['up_tn_override'],
        'lang_album_skin'           => $LANG_MG01['index_album_skin'],
        'lang_display_rows'         => $LANG_MG01['album_display_rows'],
        'album_display_columns'     => $_MG_CONF['album_display_columns'],
        'album_display_rows'        => $_MG_CONF['album_display_rows'],
        'loginrequired'             => $_MG_CONF['loginrequired'],
        'anonymous_uploads'         => $_MG_CONF['moderation'],
        'graphicspackage'           => $gp_select,
        'graphicspackage_path'      => $_MG_CONF['graphicspackage_path'],
        'lang_displayblock'         => $LANG_MG01['displayblock_prompt'],
        'lang_displayblock_help'    => $LANG_MG01['displayblock_help'],
        'lang_jhead_enable'         => $LANG_MG01['jhead_enable'],
        'lang_jh_path'              => $LANG_MG01['jhead_path'],
        'lang_jpegtran_enable'      => $LANG_MG01['jpegtran_enable'],
        'lang_ffmpeg_enable'        => $LANG_MG01['ffmpeg_enable'],
        'lang_jt_path'              => $LANG_MG01['jpegtran_path'],
        'lang_zip_enable'           => $LANG_MG01['zip_enable'],
        'lang_zip_path'             => $LANG_MG01['zip_path'],
        'lang_tmp_path'             => $LANG_MG01['tmp_path'],
        'lang_ffmpeg_path'          => $LANG_MG01['ffmpeg_path'],
        'jhead_path'                => $_MG_CONF['jhead_path'],
        'jpegtran_path'             => $_MG_CONF['jpegtran_path'],
        'zip_path'                  => $_MG_CONF['zip_path'],
        'tmp_path'                  => DB_getItem($_TABLES['mg_config'],'config_value','config_name="tmp_path"'),
        'ftp_path'                  => DB_getItem($_TABLES['mg_config'],'config_value','config_name="ftp_path"'),
        'ffmpeg_path'               => $_MG_CONF['ffmpeg_path'],
        'displayblock'              => $block_select,
        'dfidselect'                => $dfid_select,
        'rating_select'             => $rating_select,
        'wn_time_select'            => $wn_time_select,
        'custom_image_height'       => $_MG_CONF['custom_image_height'],
        'random_width'              => $_MG_CONF['random_width'],
        'random_skin'               => $_MG_CONF['random_skin'],
        'custom_image_width'        => $_MG_CONF['custom_image_width'],
        'refresh_rate'              => $_MG_CONF['def_refresh_rate'],
        'item_limit'                => $_MG_CONF['def_item_limit'],
        'time_limit'                => $_MG_CONF['def_time_limit'],
        'gallery_tn_size_select'    => $gallery_tn_size_select,
        'gallery_tnheight_input'    => $gallery_tnheight_input,
        'gallery_tnwidth_input'     => $gallery_tnwidth_input,
        'jpg_quality'               => $_MG_CONF['jpg_quality'],
        'tn_jpg_quality'            => $_MG_CONF['tn_jpg_quality'],
        'orig_jpg_quality'          => $_MG_CONF['jpg_orig_quality'],
        'truncate_breadcrumb'       => $_MG_CONF['truncate_breadcrumb'],
        'seperator'                 => $_MG_CONF['seperator'],
        'mp3_select'                => $mp3_select,
        'skin_select'               => $skin_select,
        'rskin_select'              => $rskin_select,
        'theme_select'				=> $theme_select,
        'postcard_retention'        => $_MG_CONF['postcard_retention'],
        'lang_wn_time'              => $LANG_MG01['whatsnew_time'],
        'lang_theme_select'			=> $LANG_MG01['index_theme'],
        'lang_gallery_tn_size'      => $LANG_MG01['gallery_tn_size'],
        'lang_jpg_quality'          => $LANG_MG01['jpg_quality'],
        'lang_tn_jpg_quality'       => $LANG_MG01['tn_jpg_quality'],
        'lang_orig_jpg_quality'     => $LANG_MG01['orig_jpg_quality'],
        'lang_truncate_breadcrumb'  => $LANG_MG01['truncate_breadcrumb'],
        'lang_seperator'            => $LANG_MG01['seperator'],
        'lang_mp3_player'           => $LANG_MG01['mp3_player'],
        'lang_htmlallowed'          => $LANG_MG01['htmlallowed'],
        'lang_whatsnew'             => $LANG_MG01['whatsnew'],
        'lang_dfid'                 => $LANG_MG01['dfid'],
        'lang_general_options'      => $LANG_MG01['general_options'],
        'lang_display_options'      => $LANG_MG01['display_options'],
        'lang_graphics_options'     => $LANG_MG01['graphics_options'],
        'lang_usage_tracking'       => $LANG_MG01['usage_tracking'],
        'lang_gallery_import'       => $LANG_MG01['gallery_import'],
        'lang_4images_import'       => $LANG_MG01['fourimages_import'],
        'lang_xppubwiz_install'     => $LANG_MG01['xppubwizard_install'],
        'lang_logviewer'            => $LANG_MG01['log_viewer'],
        'lang_preserve_filename'    => $LANG_MG01['preserve_filename'],
        'lang_discard_originals'    => $LANG_MG01['discard_originals'],
        'lang_custom_image_height'  => $LANG_MG01['custom_image_height'],
        'lang_custom_image_width'   => $LANG_MG01['custom_image_width'],
        'lang_verbose'              => $LANG_MG01['verbose'],
        'lang_dwnc'                 => $LANG_MG01['disable_wn_comments'],
        'lang_emid'                 => $LANG_MG01['enable_mid'],
        'lang_fip'                  => $LANG_MG01['full_in_popup'],
        'lang_cmtbar'               => $LANG_MG01['cmtbar'],
        'lang_wn_length'            => $LANG_MG01['wn_title_length'],
        'lang_batch_options'        => $LANG_MG01['batch_options'],
        'lang_refresh_rate'         => $LANG_MG01['refresh_rate'],
        'lang_time_limit'           => $LANG_MG01['time_limit'],
        'lang_item_limit'           => $LANG_MG01['item_limit'],
        'lang_ftp_path'             => $LANG_MG01['ftp_path'],
        'lang_characters'           => $LANG_MG01['characters'],
        'lang_postcard_retention' => $LANG_MG01['postcard_retention'],
        'lang_profile_hook'         => $LANG_MG01['profile_hook'],
        's_form_action'             => $_MG_CONF['admin_url'] . 'cfgedit.php',
        'rtl'                       => $LANG_DIRECTION == "rtl" ? "rtl" : "",
        'lang_autotag_caption'      => $LANG_MG01['autotag_caption'],
        'lang_popup_from_album'     => $LANG_MG01['popup_from_album'],
        'lang_random_size'          => $LANG_MG01['random_size'],
        'lang_random_skin'          => $LANG_MG01['random_skin'],
        'lang_auto_tag_defaults'    => $LANG_MG01['auto_tag_defaults'],
        'lang_alignment'            => $LANG_MG01['alignment'],
        'lang_border'               => $LANG_MG01['border'],
        'lang_width'                => $LANG_MG01['width'],
        'lang_height'               => $LANG_MG01['height'],
        'lang_source'               => $LANG_MG01['source'],
        'lang_autoplay'             => $LANG_MG01['autoplay'],
        'lang_link_to_media'        => $LANG_MG01['link_to_media'],
        'lang_ss_delay'             => $LANG_MG01['ss_delay'],
        'lang_show_titles'          => $LANG_MG01['show_titles'],
        'lang_flv'                  => $LANG_MG01['flash_video_player'],
        'lang_search_result_options'=> $LANG_MG01['search_result_options'],
        'lang_search_columns'       => $LANG_MG01['search_columns'],
        'lang_search_rows'          => $LANG_MG01['search_rows'],
        'lang_search_av_playback'   => $LANG_MG01['search_av_playback'],
        'lang_search_views'         => $LANG_MG01['search_views'],
        'lang_search_rating'        => $LANG_MG01['search_rating'],
        'lang_gallery_only'         => $LANG_MG01['gallery_only'],
        'lang_tnheight'			    => $LANG_MG01['tn_height'],
        'lang_tnwidth'			    => $LANG_MG01['tn_width'],
        'lang_index_all'            => $LANG_MG01['index_all'],
        'lang_menulabel'            => $LANG_MG01['menulabel'],
        'lang_path_mg'              => $LANG_MG01['path_mg'],
        'lang_path_mediaobjects'    => $LANG_MG01['path_mediaobjects'],
        'lang_mediaobjects_url'     => $LANG_MG01['mediaobjects_url'],
        'path_mediaobjects'         => DB_getItem($_TABLES['mg_config'],'config_value','config_name="path_mediaobjects"'),
        'mediaobjects_url'          => DB_getItem($_TABLES['mg_config'],'config_value','config_name="mediaobjects_url"'),
        'path_mg'                   => $_MG_CONF['path_mg'],
        'menulabel'                 => $_MG_CONF['menulabel'],
        'gltoken_name'              => CSRF_TOKEN,
        'gltoken'                   => SEC_createToken(),
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function MG_saveConfig( ) {
    global $display, $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG09;

    $gallery_only           = COM_applyFilter($_POST['gallery_only'],true);
    $index_all              = COM_applyFilter($_POST['index_all'],true);
    $album_display_columns  = COM_applyFilter($_POST['albumdisplaycolumns'],true);
    $album_display_rows     = COM_applyFilter($_POST['albumdisplayrows'],true);
    $loginrequired          = COM_applyFilter($_POST['loginrequired'],true);
    $anonymous_uploads      = isset($_POST['anonymousuploads'])  ? COM_applyFilter($_POST['anonymousuploads'],true) : 0;
    $zip_path               = COM_applyFilter($_POST['zip_path']);
    $ffmpeg_path            = COM_applyFilter($_POST['ffmpeg_path']);
    $tmp_path               = COM_applyFilter($_POST['tmp_path']);
    $ftp_path               = COM_applyFilter($_POST['ftp_path']);
    $displayblocks          = COM_applyFilter($_POST['displayblocks']);
    $usage_tracking         = COM_applyFilter($_POST['usagetracking']);
    $dfid                   = COM_applyFilter($_POST['dfid'],true);
//    $htmlallowed            = COM_applyFilter($_POST['htmlallowed'],true);
    $whatsnew               = COM_applyFilter($_POST['whatsnew'],true);
    $orig_jpg_quality       = COM_applyFilter($_POST['orig_jpg_quality'],true);
    $jpg_quality            = COM_applyFilter($_POST['jpg_quality'],true);
    $tn_jpg_quality         = COM_applyFilter($_POST['tn_jpg_quality'],true);
    $truncate_breadcrumb    = COM_applyFilter($_POST['truncate_breadcrumb'],true);
    $seperator              = COM_applyFilter($_POST['seperator']);
    $whatsnew_time          = COM_applyFilter($_POST['whatsnew_time'],true);
    $gallery_tn_size        = COM_applyFilter($_POST['gallery_tn_size'],true);
    $gallery_tn_height      = COM_applyFilter($_POST['tnheight'],true);
    $gallery_tn_width       = COM_applyFilter($_POST['tnwidth'],true);

    $flv_player             = COM_applyFilter($_POST['use_flowplayer'],true);

    $preserve_filename      = COM_applyFilter($_POST['preserve_filename'],true);
    $discard_originals      = COM_applyFilter($_POST['discard_originals'],true);
    $verbose                = COM_applyFilter($_POST['verbose'],true);
    $dwnc                   = COM_applyFilter($_POST['dwnc'],true);
    $emid                   = COM_applyFilter($_POST['emid'],true);
    $fip                    = COM_applyFilter($_POST['fip'],true);
    $cmtbar                 = COM_applyFilter($_POST['cmtbar'],true);
    $wn_length              = COM_applyFilter($_POST['wn_length'],true);

    $custom_image_height    = COM_applyFilter($_POST['custom_image_height'],true);
    $custom_image_width     = COM_applyFilter($_POST['custom_image_width'],true);
    $random_width           = COM_applyFilter($_POST['random_width'],true);
    $time_limit             = COM_applyFilter($_POST['time_limit'],true);
    $item_limit             = COM_applyFilter($_POST['item_limit'],true);
    $refresh_rate           = COM_applyFilter($_POST['refresh_rate'],true);
    $postcard_retention     = COM_applyFilter($_POST['postcard_retention'],true);
    $profile_hook           = COM_applyFilter($_POST['profile_hook'],true);
    $index_album_skin       = COM_applyFilter($_POST['skin']);
    $random_skin            = COM_applyFilter($_POST['rskin']);
    $subalbum_select        = COM_applyFilter($_POST['subalbum_select'],true);

    $at_border              = COM_applyFilter($_POST['at_border'],true);
    $at_align               = COM_applyFilter($_POST['at_align']);
    $at_width               = COM_applyFilter($_POST['at_width'],true);
    $at_height              = COM_applyFilter($_POST['at_height'],true);
    $at_src                 = COM_applyFilter($_POST['at_src']);
    $at_autoplay            = COM_applyFilter($_POST['at_autoplay'],true);
    $at_enable_link         = COM_applyFilter($_POST['at_enable_link'],true);
    $at_delay               = COM_applyFilter($_POST['at_delay'],true);
    $at_showtitle           = COM_applyFilter($_POST['at_showtitle'],true);

    $search_columns         = COM_applyFilter($_POST['search_columns'],true);
    $search_rows            = COM_applyFilter($_POST['search_rows'],true);
    $search_enable_rating   = COM_applyFilter($_POST['search_enable_rating'],true);
    $search_playback_type   = COM_applyFilter($_POST['search_playback_type'],true);
    $search_enable_views    = COM_applyFilter($_POST['search_enable_views'],true);

    $popup_from_album       = isset($_POST['popupfromalbum']) ? COM_applyFilter($_POST['popupfromalbum'],true) : 0;
    $autotag_caption        = isset($_POST['autotag_caption']) ? COM_applyFilter($_POST['autotag_caption'],true) : 0;
    $indextheme             = COM_applyFilter($_POST['theme']);

    $menulabel              = COM_applyFilter($_POST['menulabel']);
    $path_mg                = COM_applyFilter($_POST['path_mg']);
    $path_mediaobjects      = COM_applyFilter($_POST['path_mediaobjects']);
    $mediaobjects_url       = COM_applyFilter($_POST['mediaobjects_url']);

    if (isset($_POST['up_display_rows_enabled'])) {
        $up_display_rows_enabled = 1;
    } else {
        $up_display_rows_enabled = 0;
    }

    if (isset($_POST['up_display_columns_enabled'])) {
        $up_display_columns_enabled = 1;
    } else {
        $up_display_columns_enabled = 0;
    }

    if (isset($_POST['up_mp3_player_enabled'])) {
        $up_mp3_player_enabled = 1;
    } else {
        $up_mp3_player_enabled = 0;
    }

    if (isset($_POST['up_av_playback_enabled'])) {
        $up_av_playback_enabled = 1;
    } else {
        $up_av_playback_enabled = 0;
    }
    if (isset($_POST['up_thumbnail_size_enabled'])) {
        $up_thumbnail_size_enabled = 1;
    } else {
        $up_thumbnail_size_enabled = 0;
    }

    if ( isset($_POST['enable_jhead']) ) {
        $enable_jhead = 1;
    } else {
        $enable_jhead = 0;
    }

    if ( isset($_POST['enable_jpegtran']) ) {
        $enable_jpegtran = 1;
    } else {
        $enable_jpegtran = 0;
    }

    if ( isset($_POST['enable_zip']) ) {
        $enable_zip = 1;
    } else {
        $enable_zip = 0;
    }

    if ( isset($_POST['enable_ffmpeg']) ) {
        $enable_ffmpeg = 1;
    } else {
        $enable_ffmpeg = 0;
    }

    $tmp_path = rtrim($tmp_path);
    if (!empty($tmp_path)) {
        if (!preg_match('/^.*\/$/', $tmp_path)) {
            $tmp_path .= '/';
        }
    }

    // sanity check on values...

    if ( $album_display_columns < 1 || $album_display_columns > 5 ) {
        $album_display_columns = 2;
    }
    if ( $loginrequired < 0 || $loginrequired > 1 ) {
        $loginrequired = 1;
    }
    if ( $displayblocks < 0 || $displayblocks > 3 ) {
        $displayblocks = 0;
    }
    if ( $usage_tracking < 0 || $usage_tracking > 1 ) {
        $usage_tracking = 0;
    }
    if ( $whatsnew < 0 || $whatsnew > 1 ) {
        $whatsnew = 0;
    }
    if ( $orig_jpg_quality < 25 || $orig_jpg_quality > 100 ) {
        $orig_jpg_quality = 75;
    }
    if ( $jpg_quality < 25 || $jpg_quality > 100 ) {
        $jpg_quality = 75;
    }

    if ( $tn_jpg_quality < 25 || $tn_jpg_quality > 100 ) {
        $tn_jpg_quality = 75;
    }
    if ( $truncate_breadcrumb == '' ) {
        $truncate_breadcrumb = 0;
    }
    if ( $seperator == '' ) {
        $seperator = '/';
    }

    // check the batch options...
    if ( $time_limit < 30 ) {
        $time_limit = 30;
    }
    if ( $item_limit < 5 ) {
        $item_limit = 5;
    }
    if ( $refresh_rate < 5 ) {
        $refresh_rate = 5;
    }

    $mediaobjects_url = rtrim($mediaobjects_url);
    $path_mediaobjects = rtrim($path_mediaobjects);

    $filter = sanitizer::getInstance();
    if (!empty($mediaobjects_url)) {
        $mediaobjects_url = rtrim($filter->sanitizeUrl( $mediaobjects_url, array('http','https')),'\\/');
    }
    if (!empty($path_mediaobjects)) {
        $path_mediaobjects = (substr($path_mediaobjects,-1)!='/') ? $path_mediaobjects.='/' : $path_mediaobjects;
    }

    DB_save($_TABLES['mg_config'],"config_name, config_value","'path_mg','".DB_escapeString($path_mg)."'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'path_mediaobjects','".DB_escapeString($path_mediaobjects)."'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mediaobjects_url','".DB_escapeString($mediaobjects_url)."'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'menulabel','".DB_escapeString($menulabel)."'");

    DB_save($_TABLES['mg_config'],"config_name, config_value","'loginrequired',         '$loginrequired'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'anonymous_uploads',     '$anonymous_uploads'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'album_display_columns', '$album_display_columns'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'album_display_rows',    '$album_display_rows'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'displayblocks',         '$displayblocks'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'usage_tracking',        '$usage_tracking'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'dfid',                  '$dfid'");
//    DB_save($_TABLES['mg_config'],"config_name, config_value","'htmlallowed',           '$htmlallowed'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'whatsnew',              '$whatsnew'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'jpg_orig_quality',      '$orig_jpg_quality'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'jpg_quality',           '$jpg_quality'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'tn_jpg_quality',        '$tn_jpg_quality'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'truncate_breadcrumb',   '$truncate_breadcrumb'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'whatsnew_time',         '$whatsnew_time'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'gallery_tn_size',       '$gallery_tn_size'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'gallery_tn_height',     '$gallery_tn_height'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'gallery_tn_width',      '$gallery_tn_width'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'use_flowplayer',        '$flv_player'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'seperator',             '$seperator'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'up_display_rows_enabled',   '$up_display_rows_enabled'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'up_display_columns_enabled','$up_display_columns_enabled'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'up_mp3_player_enabled',     '$up_mp3_player_enabled'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'up_av_playback_enabled',    '$up_av_playback_enabled'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'up_thumbnail_size_enabled', '$up_thumbnail_size_enabled'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'zip_enabled',           '$enable_zip'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'zip_path',              '$zip_path'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'tmp_path',              '$tmp_path'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ftp_path',              '$ftp_path'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ffmpeg_enabled', '$enable_ffmpeg'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ffmpeg_path', '$ffmpeg_path'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'preserve_filename',' $preserve_filename'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'discard_original','$discard_originals'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'verbose','$verbose'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'disable_whatsnew_comments','$dwnc'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'enable_media_id','$emid'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'full_in_popup','$fip'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'commentbar','$cmtbar'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'title_length','$wn_length'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'custom_image_height','$custom_image_height'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'custom_image_width','$custom_image_width'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'random_width','$random_width'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'def_refresh_rate','$refresh_rate'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'def_time_limit','$time_limit'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'def_item_limit','$item_limit'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'postcard_retention','$postcard_retention'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'profile_hook','$profile_hook'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'indexskin','$index_album_skin'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'random_skin','$random_skin'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'subalbum_select','$subalbum_select'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'popup_from_album','$popup_from_album'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'autotag_caption','$autotag_caption'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'indextheme','$indextheme'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'at_border','$at_border'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'at_align','$at_align'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'at_width','$at_width'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'at_height','$at_height'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'at_src','$at_src'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'at_autoplay','$at_autoplay'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'at_enable_link','$at_enable_link'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'at_delay','$at_delay'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'at_showtitle','$at_showtitle'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'search_columns','$search_columns'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'search_rows','$search_rows'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'search_playback_type','$search_playback_type'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'search_enable_views','$search_enable_views'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'search_enable_rating','$search_enable_rating'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'gallery_only','$gallery_only'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'index_all','$index_all'");

    // now reset anything in the prefs that need to be reset...

    if ( $up_display_rows_enabled == 0 ) {
        DB_query("UPDATE {$_TABLES['mg_userprefs']} SET display_rows=0");
    }
    if ( $up_display_columns_enabled == 0 ) {
        DB_query("UPDATE {$_TABLES['mg_userprefs']} SET display_columns=0");
    }
    if ( $up_mp3_player_enabled == 0 ) {
        DB_query("UPDATE {$_TABLES['mg_userprefs']} SET mp3_player=-1");
    }
    if ( $up_av_playback_enabled == 0 ) {
        DB_query("UPDATE {$_TABLES['mg_userprefs']} SET playback_mode=-1");
    }
    if ( $up_thumbnail_size_enabled == 0 ) {
        DB_query("UPDATE {$_TABLES['mg_userprefs']} SET tn_size=-1");
    }

    $result = DB_query("SELECT * FROM " . $_TABLES['mg_config'],1);
    $nRows  = DB_numRows($result);
    for ( $x=0; $x < $nRows ; $x++ ) {
        $row = DB_fetchArray($result);
        $_MG_CONF[$row['config_name']] = $row['config_value'];
    }

    return MG_editConfig($LANG_MG09[2]);
}

/**
* Main
*/

$display = '';
$mode = '';

if ( isset($_POST['save']) ) {
    $mode = 'save';
}
if ( isset($_POST['cancel']) ) {
    $mode = 'cancel';
}

$T = new Template($_MG_CONF['template_path'].'/admin');
$T->set_file ('admin','administration.thtml');
$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],
));

if ($mode == 'save' && SEC_checkToken() ) {
    $T->set_var(array(
        'admin_body'    => MG_saveConfig(),
        'mg_navigation' => MG_navigation()
    ));
} elseif ($mode == 'cancel' ) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} elseif ($mode == $LANG_MG01['continue']) {
    COM_setMessage(2);
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_editConfig(),
        'title'         => $LANG_MG01['system_options'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?" />',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#System_Options',
    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader('menu','');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>