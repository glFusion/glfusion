<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | centerblock.inc.php                                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2015 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';

function _mg_centerblock( $where=1, $page=1, $topic ='' ) {
    global $_CONF, $_MG_CONF, $MG_albums, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $LANG_MG03,$mg_installed_version;

    static $mgCBdata = array();

    $pi_name = 'mediagallery';     // Plugin name
    $retval = '';

    if ( $topic == '' ) {
        $sTopic = "none";
    } else {
        $sTopic = $topic;
    }

    if ( !isset($_MG_CONF['feature_member_album']) ) {
        $_MG_CONF['feature_member_album'] = 1;
    }

    if ($_MG_CONF['feature_member_album'] == 1 && $_MG_CONF['member_albums'] == 1 && !COM_isAnonUser() && $where == 1) {
        $cbpos = CENTERBLOCK_TOP; //top of page
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
            MG_initAlbums();
            $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . (int) $_USER['uid'] . " AND album_parent='" . $_MG_CONF['member_album_root'] . "' LIMIT 1";
            $result = DB_query($sql);
            $numRows = DB_numRows($result);
            if ( $numRows > 0 ) {
                $A = DB_fetchArray($result);
                $album_id = $A['album_id'];

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
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $cover[0] .'/' . $cover . $ext) ) {
                            $album_last_image   = $_MG_CONF['mediaobjects_url'] . '/tn/' . $cover[0] .'/' . $cover . $ext;
                            $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $cover[0] .'/' . $cover . $ext);
                            break;
                        }
                    }
                } else {
                    $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                    $media_size = array(200,200); //$media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'empty.png');
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
                    $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                    $media_size = array(200,200); // @getimagesize($_MG_CONF['path_mediaobjects'] . 'missing.png');
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
	                    $subAlbumDisplay .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '" />';
	                    $subAlbumDisplay .= '<input type="hidden" name="page" value="1" />';
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
                    'featured_album'    => $LANG_MG03['your_member_album'],
                ));

                $T->parse('output','page');
                $retval = $T->finish($T->get_var('output'));
                return $retval;
            }
        }
    }

    if ( $mgCBdata == null ) {
        $result = DB_query("SELECT album_id, cbpage, cbposition FROM {$_TABLES['mg_albums']} WHERE featured='1' AND (cbpage='" . DB_escapeString($sTopic) . "' OR cbpage='all' OR cbpage='allnhp') " . COM_getPermSQL('and') . " LIMIT 1");
        $mgCBdata = array();
        while ( ($row = DB_fetchArray($result))!= NULL ) {
            $mgCBdata[$row['cbposition']] = $row;
        }
        if ( isset($mgCBdata[$where] ) ) {
            $centerblocks = count($mgCBdata[$where]);
        } else {
            $centerblocks = 0;
        }
    }
    if ( isset($mgCBdata[$where] ) ) {
        $record = $mgCBdata[$where];
        $album_id = $record['album_id'];
        $cbpage   = $record['cbpage'];
        $cbpos    = $record['cbposition'];

        // If enabled only for homepage and this is not page 1 or a topic page, then set disable flag
        if ($cbpage == 'none' && ($page > 1 OR $topic != "")){
            $cbenable = 0;
        } elseif ($cbpage == 'all' && $page > 1 ){
            $cbenable = 0;
        } elseif ($cbpage == 'allnhp' && COM_onFrontpage() ) {
            $cbenable = 0;
        } elseif ($cbpage != 'allnhp' && $cbpage != 'none' && $cbpage != 'all' && $cbpage != $topic) {
            $cbenable = 0;
        } else
            $cbenable = 1;

        if ($cbenable AND $cbpos == $where) {
            MG_initAlbums();
            $T = new Template( MG_getTemplatePath(0) );
            $T->set_file ('page', 'cb_featured_album.thtml');

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
                $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                $media_size = array(200,200); // $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'empty.png');
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
                $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                $media_size = array(200,200); // @getimagesize($_MG_CONF['path_mediaobjects'] . 'missing.png');
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
                    $subAlbumDisplay .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '" />';
                    $subAlbumDisplay .= '<input type="hidden" name="page" value="1" />';
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
?>