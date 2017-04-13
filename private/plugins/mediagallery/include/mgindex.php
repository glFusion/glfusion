<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | mgindex.php                                                              |
// |                                                                          |
// | Main index page for Media Gallery                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2017 by the following authors:                        |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

MG_initAlbums();

function MG_index() {
    global $_USER, $_MG_CONF, $_CONF, $_TABLES, $MG_albums, $LANG_MG00, $LANG_MG01, $LANG_MG02, $LANG_MG03, $themeStyle;

    $display = '';
    $media_size = false;

    $page = 0;
    if ( isset($_GET['page']) ) {
        $page      = COM_applyFilter($_GET['page'],true);
    }
    if ( $page != 0 ) {
        $page = $page - 1;
    }

    $themeStyle = MG_getThemeCSS(0);

    if (!isset($_MG_CONF['album_display_columns']) || $_MG_CONF['album_display_columns'] < 1 ) {
        $_MG_CONF['album_display_columns'] = 1;
    }

    switch( $_MG_CONF['album_display_columns'] ) {
        case 1 :
            $albumListTemplate = 'gallery_page_body_1.thtml';
            $albumColumnWidth = "100%";
            break;
        case 2 :
            $albumListTemplate = 'gallery_page_body_2.thtml';
            $albumColumnWidth = "50%";
            break;
        default :
            $albumListTemplate = 'gallery_page_body_3.thtml';
            $albumColumnWidth = @intval(100 / $_MG_CONF['album_display_columns']) . '%';
            if ( $albumColumnWidth == 0 ) {
                $albumColumnWidth = "25%";
            }
            break;
    }
    $T = new Template( MG_getTemplatePath(0) );

    $T->set_file( array (
        'page'      =>  'gallery_page.thtml',
        'body'      =>  $albumListTemplate,
        'noitems'   =>  'gallery_page_noitems.thtml',
    ));

    $T->set_var(array(
        'lang_menulabel'    => $_MG_CONF['menulabel'],
        'lang_search'       => $LANG_MG01['search'],
        'site_url'          => $_MG_CONF['site_url'],
    ));

    if ( $_MG_CONF['rss_full_enabled'] ) {
        $feedUrl = MG_getFeedUrl($_MG_CONF['rss_feed_name'].'.rss');
        $rsslink = '<a href="' . $feedUrl . '"' . ' type="application/rss+xml">';
        $rsslink .= '<img src="' . MG_getImageFile('feed.png') . '" alt="" style="border:none;"/></a>';
        $T->set_var('rsslink', $rsslink);
        $T->set_var('rsslink_url',$feedUrl);
    } else {
        $T->set_var('rsslink','');
    }

    $nFrame = new mgFrame();
    $nFrame->constructor( $_MG_CONF['indexskin'] );
    $MG_albums[0]->albumFrameTemplate = $nFrame->getTemplate();
    $MG_albums[0]->afrWidth = $nFrame->frame['wHL'] + $nFrame->frame['wHR'];
    $MG_albums[0]->afrHeight = $nFrame->frame['hVT'] + $nFrame->frame['hVB'];

    // Let's build our admin menu options

    $showAdminBox = 0;
    $admin_box_item = '';

    $admin_box  = '<form name="adminbox" id="adminbox" action="' . $_MG_CONF['site_url'] . '/admin.php" method="get" style="margin:0;padding:0;">' . LB;
    $admin_box .= '<div>';
    $admin_box .= '<select onchange="javascript:forms[\'adminbox\'].submit();" name="mode">' . LB;
    $admin_box_item .= '<option label="' . $LANG_MG01['options'] . '" value="">' . $LANG_MG01['options'] . '</option>' . LB;
    $disabled = '';
    if ( ($MG_albums[0]->member_uploads || $MG_albums[0]->access == 3) && (!COM_isAnonUser() ) )  {
        if ( count($MG_albums) == 1 ) {
            $disabled = ' disabled="disabled" ';
        }
        $admin_box_item .= '<option value="upload"'.$disabled.'>' . $LANG_MG01['add_media'] . '</option>' . LB;
        $showAdminBox = 1;
    }
    if ( $MG_albums[0]->owner_id ) {
        $admin_box_item .= '<option value="albumsort">'  . $LANG_MG01['sort_albums'] . '</option>' . LB;
        $admin_box_item .= '<option value="globalattr">' . $LANG_MG01['globalattr'] . '</option>' . LB;
        $admin_box_item .= '<option value="globalperm">' . $LANG_MG01['globalperm'] . '</option>' . LB;
        $queue_count = DB_count($_TABLES['mg_media_album_queue']);
        $admin_box_item .= '<option value="moderate">' . $LANG_MG01['media_queue'] . ' (' . $queue_count . ')</option>' . LB;
        $admin_box_item .= '<option value="wmmanage">' . $LANG_MG01['wm_management'] . '</option>' . LB;
        $admin_box_item .= '<option value="create">' . $LANG_MG01['create_album'] . '</option>' . LB;
        $showAdminBox = 1;
    } elseif ( $MG_albums[0]->access == 3 ) {
        $admin_box_item .= '<option value="create">' . $LANG_MG01['create_album'] . '</option>' . LB;
        $showAdminBox = 1;
    } elseif ( $_MG_CONF['member_albums'] == 1 && ( !COM_isAnonUser() ) && $_MG_CONF['member_album_root'] == 0 && $_MG_CONF['member_create_new']) {
        $admin_box_item .= '<option value="create">' . $LANG_MG01['create_album'] . '</option>' . LB;
        $showAdminBox = 1;
    }
    $admin_box .= $admin_box_item;
    $admin_box .= '</select>' . LB;
    $admin_box .= '<input type="hidden" name="album_id" value="0"/>' . LB;
    $admin_box .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '"/>' . LB;
    $admin_box .= '</div>';
    $admin_box .= '</form>';

// build ul
    $admin_menu = '';
    $showAdminMenu = 0;
    $admin_url = $_MG_CONF['site_url'] . '/admin.php?album_id=0';
    if ( ($MG_albums[0]->member_uploads || $MG_albums[0]->access == 3) && (!COM_isAnonUser() ) )  {
        $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=upload">'.$LANG_MG01['add_media'].'</a></li>';
        $showAdminMenu = 1;
    }
    if ( $MG_albums[0]->owner_id ) {
        $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=albumsort">'.$LANG_MG01['sort_albums'].'</a></li>';
        $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=globalattr">'.$LANG_MG01['globalattr'] . '</a></li>' . LB;
        $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=globalperm">'.$LANG_MG01['globalperm'] . '</a></li>' . LB;
        $queue_count = DB_count($_TABLES['mg_media_album_queue']);
        $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=moderate">'.$LANG_MG01['media_queue'] . ' (' . $queue_count . ')</a></li>' . LB;
        $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=wmmanage">'.$LANG_MG01['wm_management'] . '</a></li>' . LB;
        $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=create">' . $LANG_MG01['create_album'] . '</a></li>' . LB;
        $showAdminMenu = 1;
    } elseif ( $MG_albums[0]->access == 3 ) {
        $admin_Menu .= '<li><a href="'.$abmin_url.'&amp;mode=create">' . $LANG_MG01['create_album'] . '</a></li>' . LB;
        $showAdminMenu = 1;
    } elseif ( $_MG_CONF['member_albums'] == 1 && ( !COM_isAnonUser() ) && $_MG_CONF['member_album_root'] == 0 && $_MG_CONF['member_create_new']) {
        $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=create">' . $LANG_MG01['create_album'] . '</a></li>' . LB;
        $showAdminMenu = 1;
    }
// end of ul

    if ( $showAdminBox == 0 ) {
        $admin_box = '';
        $admin_box_item = '';
    }

    if ( $showAdminMenu == 1 ) {
        $T->set_var('admin_menu',$admin_menu);
    }

    $T->set_var('select_adminbox',$admin_box);
    $T->set_var('select_box_items',$admin_box_item);

    $album_count = 0;

    $width = intval(100 / $_MG_CONF['album_display_columns']);
    $rowcounter = 0;

    $albumCount = 0;
    $indexCounter = 0;
    if ( COM_isAnonUser() ) {
        $lastlogin = time();
    } else {
        if ( !COM_isAnonUser() ) {
            $lastlogin = $_USER['lastlogin'];
        } else {
            $lastlogin = time();
        }
    }

    $children = $MG_albums[0]->getChildren();
    $nrows = count($children);
    $checkCounter = 0;

    $aCount = 0;
    $achild = array();
    for ($i=0; $i<$nrows;$i++) {
        $access = $MG_albums[$children[$i]]->access;
        if ( $access == 0 || ( $MG_albums[$children[$i]]->hidden == 1 && $access != 3)) {
            // no op
        } else {
            $achild[] = $MG_albums[$children[$i]]->id;
            $aCount++;
        }
    }

    if ( $_MG_CONF['album_display_rows'] < 1 ) {
        $_MG_CONF['album_display_rows'] = 9;
    }

    $items_per_page = $_MG_CONF['album_display_columns'] * $_MG_CONF['album_display_rows'];
    $begin = $items_per_page * $page;
    $end   = $items_per_page;
    $nrows = count($achild);
    $indexCounter = $begin;
    $noParse = 0;
    $needFinalParse = 0;
    if ( $nrows > 0 ) {
        $k = 0;

        $T->set_block('body', 'AlbumColumn', 'AColumn');
        $T->set_block('body', 'AlbumRow','ARow');

        for ( $i = $begin; $i < ($begin+$items_per_page ); $i += $_MG_CONF['album_display_columns']) {
            for ($j = $i; $j < ($i + $_MG_CONF['album_display_columns']); $j++) {
                $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                if ($j >= $nrows) {
                    $k = ($i+$_MG_CONF['album_display_columns']) - $j;
                    $m = $k % $_MG_CONF['album_display_columns'];
                    for ( $z = $m; $z > 0; $z--) {
                        $needFinalParse = 1;
                    }
                    if ( $needFinalParse == 1 ) {
                        $T->parse('ARow','AlbumRow',true);
                        $T->set_var('AColumn','');
                    }
                    $noParse = 1;
                    break;
                }
                $access = $MG_albums[$achild[$indexCounter]]->access;
                if ( $access == 0 || ( $MG_albums[$achild[$indexCounter]]->hidden == 1 && $access != 3)) {
                    $j--;
                    $indexCounter++;
                    continue;
                }

                $albumCount++;
                if ( $MG_albums[$achild[$indexCounter]]->media_count > 0 ) {
                    if ( $MG_albums[$achild[$indexCounter]]->cover_filename != '' && $MG_albums[$achild[$indexCounter]]->cover_filename != '0') {
                        $album_last_update  = MG_getUserDateTimeFormat($MG_albums[$achild[$indexCounter]]->last_update);
                        if ( substr($MG_albums[$achild[$indexCounter]]->cover_filename,0,3) == 'tn_' ) {
                            $offset = 3;
                        } else {
                            $offset = 0;
                        }

                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $MG_albums[$achild[$indexCounter]]->cover_filename[$offset] .'/' . $MG_albums[$achild[$indexCounter]]->cover_filename . $ext) ) {
                                $album_last_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $MG_albums[$achild[$indexCounter]]->cover_filename[$offset] .'/' . $MG_albums[$achild[$indexCounter]]->cover_filename . $ext;
                                $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $MG_albums[$achild[$indexCounter]]->cover_filename[$offset] .'/' . $MG_albums[$achild[$indexCounter]]->cover_filename . $ext);
                                break;
                            }
                        }
                        $album_media_count  = $MG_albums[$achild[$indexCounter]]->media_count;

                        if ( !COM_isAnonUser() ) {
                            if ($MG_albums[$achild[$indexCounter]]->last_update > $lastlogin) {
                                $album_last_update[0] = '<font color="red">' . $album_last_update[0] . '</font>';
                            }
                        }
                        $T->set_var(array(
                            'updated_prompt'    =>  ($_MG_CONF['dfid']=='99' ? '' : $LANG_MG03['updated_prompt'])
                        ));

                    } else {
                        $album_media_count  = $MG_albums[$achild[$indexCounter]]->media_count;
                        $album_last_update  = MG_getUserDateTimeFormat($MG_albums[$achild[$indexCounter]]->last_update);

                        $filename = $MG_albums[$achild[$indexCounter]]->findCover();

                        if ( $filename == '' ) {
                            $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                            $media_size = array(200,200);
                        } else {
                            if ( substr($filename,0,3) == 'tn_' ) {
                                $offset = 3;
                            } else {
                                $offset = 0;
                            }
                            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[$offset] .'/' . $filename . $ext) ) {
                                    $album_last_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[$offset] .'/' . $filename . $ext;
                                    $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[$offset] .'/' . $filename . $ext);
                                    break;
                                }
                            }
                        }
                        $T->set_var(array(
                            'updated_prompt'    =>  ($_MG_CONF['dfid']=='99' ? '' : $LANG_MG03['updated_prompt'])
                        ));
                    }
                } else {  // nothing in the album yet...

                    // here we need to search the sub-albums if any and see if we can find a picture....

                    $album_media_count = 0;
                    $album_last_update[0] = "";
                    $filename = $MG_albums[$achild[$indexCounter]]->findCover();
                    if ( $filename == '' ) {
                        $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                        $media_size = array(200,200);
                    } else {
                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] .'/' . $filename . $ext) ) {
                                $album_last_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] .'/' . $filename . $ext;
                                $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] .'/' . $filename . $ext);
                                break;
                            }
                        }
                    }
                    $T->set_var('updated_prompt', '');
                }
                $T->clear_var(array('lang_views','views'));
                if ( $MG_albums[$achild[$indexCounter]]->enable_album_views ) {
                    $T->set_var(array(
                        'lang_views'    => $LANG_MG03['views'],
                        'views'         => $MG_albums[$achild[$indexCounter]]->views,
                    ));
                }

                if ( $MG_albums[$achild[$indexCounter]]->tn_attached == 1 ) {
                    $media_size = false;
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $MG_albums[$achild[$indexCounter]]->id . $ext) ) {
                            $album_last_image = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $MG_albums[$achild[$indexCounter]]->id . $ext;
                            $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $MG_albums[$achild[$indexCounter]]->id . $ext);
                            break;
                        }
                    }
                }

                // a little fail safe here to make sure we don't show empty boxes...

                if ( $media_size === false || $media_size[0] == 0 || $media_size[1] == 0 ) {
                    $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                    $media_size = array(200,200);
                }

                // set the image size here...
                switch ($_MG_CONF['gallery_tn_size'] ) {
                    case '0' :      //small
                        $tn_height = 100;
                        $tn_width  = 100;
                        break;
                    case '1' :      //medium
                        $tn_height = 150;
                        $tn_width  = 150;
                        break;
                    case '2' :
                        $tn_height = 200;
                        $tn_width  = 200;
                        break;
                    case '3' :
                        $tn_height = $_MG_CONF['gallery_tn_height'];
                        $tn_width  = $_MG_CONF['gallery_tn_width'];
                        break;
                    default :
                        $tn_height = 200;
                        $tn_width  = 200;
                        break;
                }

                if ( $media_size[0] > $media_size[1] ) {
                    $ratio = $media_size[0] / $tn_height;
                    $newwidth = $tn_height;
                    $newheight = @round($media_size[1] / $ratio);
                } else {
                    $ratio = $media_size[1] / $tn_height;
                    $newheight = $tn_height;
                    $newwidth = @round($media_size[0] / $ratio);
                }

                // pull the sub-album info here
                $subAlbumDisplay = '';
                if ( isset($_MG_CONF['subalbum_select']) && $_MG_CONF['subalbum_select'] == 1  ) {
                    $subAlbumDisplay = '<form name="subalbums' . $MG_albums[$achild[$indexCounter]]->id . '" action="' . $_MG_CONF['site_url'] . '/album.php' . '" method="get" style="margin:0;padding:0">';
                    $subAlbumDisplay .= '<select name="aid" onchange="forms[\'subalbums' . $MG_albums[$achild[$indexCounter]]->id . '\'].submit()">';
                    $subAlbumDisplay .= '<optgroup label="' . $LANG_MG01['select_subalbum'] . '">' . LB;
                }
                $saRows = 0;
                $T->clear_var(array('lang_subalbums','subalbumcount','subalbumlist'));
                if ( !empty($MG_albums[$achild[$indexCounter]]->children ) ) {
                    $SAchildren = $MG_albums[$achild[$indexCounter]]->getChildren();
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
                        $subAlbumDisplay .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '" />';
                        $subAlbumDisplay .= '<input type="hidden" name="page" value="1"/>';
                        $subAlbumDisplay .= '</form>';
                    }
                    if ( $_MG_CONF['album_display_columns'] > 1 && $_MG_CONF['subalbum_select'] != 1 ) {
                        $T->set_var(array(
                            'subalbumlist'  => '<span style="font-weight:bold;">' . $LANG_MG01['subalbums'] . '</span> (' . $saRows . ')'
                        ));
                    } else {
                        $T->set_var(array(
                            'lang_subalbums'    =>  $LANG_MG01['subalbums'],
                            'subalbumcount'     =>  '(' . $saRows . ')',
                            'subalbumlist'      =>  $subAlbumDisplay
                        ));
                    }
                }
                if ( $saRows == 0) {
                    $T->clear_var(array('lang_subalbums','subalbumcount','subalbumlist'));
                }
                $T->clear_var(array('saulstart','saulend'));
                if ( $saRows > 0 && $_MG_CONF['subalbum_select'] != 1) {
                    $T->set_var(array(
                       'saulstart'         =>  '<ul>',
                       'saulend'           =>  '</ul>',
                    ));
                }
                // now pull the total image count for all sub albums...

                $total_images_subalbums = $MG_albums[$achild[$indexCounter]]->getMediaCount();
                $owner_id = $MG_albums[$achild[$indexCounter]]->owner_id;

                if ( $owner_id == '' || !isset($MG_albums[$achild[$indexCounter]]->owner_id) ) {
                    $owner_id = 0;
                }
                $ownername = DB_getItem ($_TABLES['users'],'username', "uid=".intval($owner_id));

                $F = new Template($_MG_CONF['template_path']);

                $F->set_var('media_frame',$MG_albums[0]->albumFrameTemplate);

                $F->set_var(array(
                    'border_width'          => $newwidth + 20,
                    'border_height'         => $newheight + 20,
                    'media_link_start'      => '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$achild[$indexCounter]]->id . '&amp;page=1' . '">',
                    'media_link_end'        => '</a>',
                    'url_media_item'        => $_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$achild[$indexCounter]]->id . '&amp;page=1',
                    'media_thumbnail'       => $album_last_image,
                    'media_size'            => 'width="' . $newwidth . '" height="' . $newheight . '"',
                    'media_height'          => $newheight,
                    'media_width'           => $newwidth,
                    'media_tag'             => strip_tags($MG_albums[$achild[$indexCounter]]->title),
                    'frWidth'               =>  $newwidth  - $MG_albums[0]->afrWidth,
                    'frHeight'              =>  $newheight - $MG_albums[0]->afrHeight,
                ));

                $F->parse('media','media_frame');
                $media_item_thumbnail = $F->finish($F->get_var('media'));

                $T->set_var(array(
                    'media_item_thumbnail' => $media_item_thumbnail,
                    'class'                => $rowcounter % 2,
                    'table_column_width' => 'width="' . $width . '%"',
                    'album_id'          => $MG_albums[$achild[$indexCounter]]->id,
                    'album_title'       => PLG_replaceTags($MG_albums[$achild[$indexCounter]]->title,'mediagallery','album_title'),
                    'album_desc'        => $MG_albums[$achild[$indexCounter]]->description == '' ? '' : PLG_replaceTags($MG_albums[$achild[$indexCounter]]->description,'mediagallery','album_description'),
                    'album_media_count' => $album_media_count,
                    'subalbum_media_count' => $total_images_subalbums,
                    'album_owner'       => $ownername,
                    'album_last_update' => $album_last_update[0],
                    'column_width'      => $albumColumnWidth,
                    'column_width2'     => $tn_height + 35 . 'px',
                    'lang_album'        => $LANG_MG00['album'],
                    'border_width'          => $newwidth + 20,
                    'border_height'         => $newheight + 20,
                    'media_link_start'      => '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$achild[$indexCounter]]->id . '&amp;page=1' . '">',
                    'media_link_end'        => '</a>',
                    'url_media_item'        => $_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$achild[$indexCounter]]->id . '&amp;page=1',
                    'media_thumbnail'       => $album_last_image,
                    'media_size'            => 'width="' . $newwidth . '" height="' . $newheight . '"',
                    'media_height'          => $newheight,
                    'media_width'           => $newwidth,
                    'media_tag'             => strip_tags($MG_albums[$achild[$indexCounter]]->title),
                    'frWidth'               =>  $newwidth  - $MG_albums[0]->afrWidth,
                    'frHeight'              =>  $newheight - $MG_albums[0]->afrHeight,
                    'url_media_item'        => $_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$achild[$indexCounter]]->id . '&amp;page=1',
                ));
                $T->parse('AColumn', 'AlbumColumn',true);
                $indexCounter++;
            }

            if ( $noParse == 1 ) {
                break;
            } else {
                $T->parse('ARow','AlbumRow',true);
                $T->set_var('AColumn','');
            }
        }
    }
    $T->set_var(array(
        'bottom_pagination'     => COM_printPageNavigation($_MG_CONF['site_url'] . '/index.php', $page+1,ceil($aCount  / $items_per_page)),
        'table_columns'         => $_MG_CONF['album_display_columns'],
    ));

    if ($albumCount == 0 ) {
        $T->set_var(array(
            'noitems'       =>  $LANG_MG03['no_album_objects']
        ));
        $T->parse('gallery_noitems','noitems');
    } else {
        $T->parse('gallery_body','body');
    }
    $T->parse('output','page');
    $nCSS = $nFrame->getCSS();
    if ( $nCSS != '' ) {
        $outputHandle = outputHandler::getInstance();
        $outputHandle->addStyle($nCSS);
    }

    $display .= MG_siteHeader($LANG_MG00['plugin']);
    $display .= $T->finish($T->get_var('output'));
    $display .= MG_siteFooter();
    echo $display;
    exit;
}
?>