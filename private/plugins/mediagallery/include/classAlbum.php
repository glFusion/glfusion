<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | classAlbum.php                                                           |
// |                                                                          |
// | Album class                                                              |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
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

class mgAlbum {
    var $id;
    var $title;
    var $description;
    var $parent;
    var $order;
    var $hidden;
    var $cover;
    var $cover_filename;
    var $media_count;
    var $album_disk_usage;
    var $last_update;
    var $views;
    var $podcast;
    var $mp3ribbon;
    var $display_album_desc;
    var $enable_album_views;
    var $album_view_type;
    var $image_skin;
    var $album_skin;
    var $display_skin;
    var $enable_comments;
    var $exif_display;
    var $enable_rating;
    var $playback_type;
    var $tn_attached;
    var $enable_slideshow;
    var $enable_random;
    var $enable_shutterfly;
    var $enable_views;
    var $enable_keywords;
    var $enable_html;
    var $enable_sort;
    var $enable_rss;
    var $enable_postcard;
    var $albums_first;
    var $allow_download;
    var $full;
    var $tn_size;
    var $max_image_height;
    var $max_image_width;
    var $max_filesize;
    var $display_image_size;
    var $display_rows;
    var $display_columns;
    var $valid_formats;
    var $filename_title;
    var $shopping_cart;
    var $wm_auto;
    var $wm_id;
    var $wm_opacity;
    var $wm_location;
    var $album_sort_order;
    var $member_uploads;
    var $moderate;
    var $email_mod;
    var $featured;
    var $cbposition;
    var $cbpage;
    var $owner_id;
    var $group_id;
    var $mod_group_id;
    var $perm_owner;
    var $perm_group;
    var $perm_members;
    var $perm_anon;
    var $access;
    var $children;
    var $imageFrameTemplate;
    var $albumFrameTemplate;
    var $displayFrameTemplate;
    var $frWidth;
    var $frHeight;
    var $afrWidth;
    var $afrHeight;
    var $dfrWidth;
    var $dfrHeight;
    var $tnHeight;
    var $tnWidth;
    var $useAlternate;
    var $skin;
    var $rssChildren;

    function __construct () {
        global $_MG_CONF;

        $this->children             = array();
        $this->id                   = 0;
        $this->title                = '';
        $this->description          = '';
        $this->parent               = 0;
        $this->order                = 0;
        $this->hidden               = 0;
        $this->cover                = '-1';
        $this->cover_filename       = '';
        $this->media_count          = 0;
        $this->album_disk_usage     = 0;
        $this->last_update          = 0;
        $this->views                = 0;
        $this->podcast				= 0;
        $this->mp3ribbon            = isset($_MG_CONF['ad_mp3ribbon']) ? $_MG_CONF['ad_mp3ribbon'] : 0;
        $this->enable_album_views   = $_MG_CONF['ad_enable_album_views'];
        $this->image_skin           = $_MG_CONF['ad_image_skin'];
        $this->album_skin           = $_MG_CONF['ad_album_skin'];
        $this->display_skin         = $_MG_CONF['ad_display_skin'];
        $this->enable_comments      = $_MG_CONF['ad_enable_comments'];
        $this->exif_display         = $_MG_CONF['ad_exif_display'];
        $this->enable_rating        = $_MG_CONF['ad_enable_rating'];
        $this->playback_type        = $_MG_CONF['ad_playback_type'];
        $this->tn_attached          = 0;
        $this->enable_slideshow     = $_MG_CONF['ad_enable_slideshow'];
        $this->enable_random        = $_MG_CONF['ad_enable_random'];
        $this->enable_shutterfly    = 0; //$_MG_CONF['ad_enable_shutterfly'];
        $this->enable_views         = $_MG_CONF['ad_enable_views'];
        $this->enable_keywords      = $_MG_CONF['ad_enable_keywords'];
        $this->enable_html          = 0;
        $this->display_album_desc   = $_MG_CONF['ad_display_album_desc'];
        $this->display_album_desc   = $_MG_CONF['ad_display_album_desc'];
        $this->enable_sort          = $_MG_CONF['ad_enable_sort'];
        $this->enable_rss           = $_MG_CONF['ad_enable_rss'];
        $this->enable_postcard      = $_MG_CONF['ad_enable_postcard'];
        $this->albums_first         = $_MG_CONF['ad_albums_first'];
        $this->allow_download       = $_MG_CONF['ad_allow_download'];
        $this->full                 = $_MG_CONF['ad_full_display'];
        $this->tn_size              = $_MG_CONF['ad_tn_size'];
        $this->tnHeight             = isset($_MG_CONF['ad_tn_height']) ? $_MG_CONF['ad_tn_height'] : 0;
        $this->tnWidth              = isset($_MG_CONF['ad_tn_width']) ? $_MG_CONF['ad_tn_width'] : 0;
        $this->max_image_height     = $_MG_CONF['ad_max_image_height'];
        $this->max_image_width      = $_MG_CONF['ad_max_image_width'];
        $this->max_filesize         = $_MG_CONF['ad_max_filesize'];
        $this->display_image_size   = $_MG_CONF['ad_display_image_size'];
        $this->display_rows         = intval($_MG_CONF['ad_display_rows']);
        $this->display_columns      = intval($_MG_CONF['ad_display_columns']);
        $this->valid_formats        = $_MG_CONF['ad_valid_formats'];
        $this->filename_title       = $_MG_CONF['ad_filename_title'];
        $this->shopping_cart        = 0;
        $this->wm_auto              = $_MG_CONF['ad_wm_auto'];
        $this->wm_id                = $_MG_CONF['ad_wm_id'];
        $this->wm_opacity           = $_MG_CONF['ad_wm_opacity'];
        $this->wm_location          = $_MG_CONF['ad_wm_location'];
        $this->album_sort_order     = $_MG_CONF['ad_album_sort_order'];
        $this->member_uploads       = $_MG_CONF['ad_member_uploads'];
        $this->moderate             = $_MG_CONF['ad_moderate'];
        $this->email_mod            = $_MG_CONF['ad_email_mod'];
        $this->featured             = 0;
        $this->cbposition           = 0;
        $this->cbpage               = 'all';
        $this->owner_id             = 0;
        $this->group_id             = 0;
        $this->mod_group_id         = $_MG_CONF['ad_mod_group_id'];
        $this->perm_owner           = $_MG_CONF['ad_perm_owner'];
        $this->perm_group           = $_MG_CONF['ad_perm_group'];
        $this->perm_members         = $_MG_CONF['ad_perm_members'];
        $this->perm_anon            = $_MG_CONF['ad_perm_anon'];
        $this->useAlternate         = isset($_MG_CONF['ad_use_alternate']) ? $_MG_CONF['ad_use_alternate'] : 0;
        $this->skin                 = isset($_MG_CONF['ad_skin']) ? $_MG_CONF['ad_skin'] : 'default';
        $this->rssChildren          = isset($_MG_CONF['ad_rsschildren']) ? $_MG_CONF['ad_rsschildren'] : 0;
    }

    function constructor( $album, $mgadmin, $root, $groups ) {
        $this->id               = $album['album_id'];
        $this->title            = (!empty($album['album_title']) && $album['album_title'] != ' ') ? $album['album_title'] : '';
        $this->parent           = $album['album_parent'];
        $this->description      = (!empty($album['album_desc']) && $album['album_desc'] != ' ') ? $album['album_desc'] : '';
        $this->order            = $album['album_order'];
        $this->hidden           = $album['hidden'];
        $this->podcast			= $album['podcast'];
        $this->mp3ribbon        = $album['mp3ribbon'];
        $this->cover            = $album['album_cover'];
        $this->cover_filename   = $album['album_cover_filename'];
        $this->media_count      = $album['media_count'];
        $this->album_disk_usage = $album['album_disk_usage'];
        $this->last_update      = $album['last_update'];
        $this->views            = $album['album_views'];
        $this->enable_album_views = $album['enable_album_views'];
        $this->image_skin       = $album['image_skin'];
        $this->album_skin       = $album['album_skin'];
        $this->display_skin     = $album['display_skin'];
        $this->display_album_desc = $album['display_album_desc'];
        $this->enable_comments  = $album['enable_comments'];
        $this->exif_display     = $album['exif_display'];
        $this->enable_rating    = $album['enable_rating'];
        $this->playback_type    = $album['playback_type'];
        $this->tn_attached      = $album['tn_attached'];
        $this->enable_slideshow = $album['enable_slideshow'];
        $this->enable_random    = $album['enable_random'];
        $this->enable_shutterfly = 0; //$album['enable_shutterfly'];
        $this->enable_views     = $album['enable_views'];
        $this->enable_keywords  = $album['enable_keywords'];
        $this->enable_html      = $album['enable_html'];
        $this->enable_sort      = $album['enable_sort'];
        $this->enable_rss       = $album['enable_rss'];
        $this->enable_postcard  = $album['enable_postcard'];
        $this->albums_first     = $album['albums_first'];
        $this->allow_download   = $album['allow_download'];
        $this->full             = $album['full_display'];
        $this->tn_size          = $album['tn_size'];
        $this->max_image_height = $album['max_image_height'];
        $this->max_image_width  = $album['max_image_width'];
        $this->max_filesize     = $album['max_filesize'];
        $this->display_image_size = $album['display_image_size'];
        $this->display_rows     = intval($album['display_rows']);
        $this->display_columns  = intval($album['display_columns']);
        $this->valid_formats    = $album['valid_formats'];
        $this->filename_title   = $album['filename_title'];
        $this->shopping_cart    = 0;
        $this->wm_auto          = $album['wm_auto'];
        $this->wm_id            = $album['wm_id'];
        $this->wm_opacity       = $album['opacity'];
        $this->wm_location      = $album['wm_location'];
        $this->album_sort_order = $album['album_sort_order'];
        $this->member_uploads   = $album['member_uploads'];
        $this->moderate         = $album['moderate'];
        $this->email_mod        = $album['email_mod'];
        $this->featured         = $album['featured'];
        $this->cbposition       = $album['cbposition'];
        $this->cbpage           = $album['cbpage'];
        $this->owner_id         = $album['owner_id'];
        $this->group_id         = $album['group_id'];
        $this->mod_group_id     = $album['mod_group_id'];
        $this->perm_owner       = $album['perm_owner'];
        $this->perm_group       = $album['perm_group'];
        $this->perm_members     = $album['perm_members'];
        $this->perm_anon        = $album['perm_anon'];
        $this->tnHeight			= $album['tnheight'];
        $this->tnWidth			= $album['tnwidth'];
        $this->useAlternate     = $album['usealternate'];
        $this->skin             = isset($album['skin']) ? $album['skin'] : 'default';
        $this->rssChildren      = isset($album['rsschildren']) ? $album['rsschildren'] : 0;
        $this->setAccessRights($mgadmin,$root,$groups);
    }

    function setChild( $id ) {
        $this->children[$id] = $id;
    }

    function saveAlbum( ) {
        global $_TABLES, $MG_albums;

        $this->album_disk_usage = (int) $this->album_disk_usage;
        $this->last_update      = (int) $this->last_update;
        $this->views            = (int) $this->views;
        $this->enable_keywords  = (int) $this->enable_keywords;
        $this->enable_html      = (int) $this->enable_html;
        $this->title            = DB_escapeString($this->title);
        $this->description      = DB_escapeString($this->description);

        $sqlFieldList  = 'album_id,album_title,album_desc,album_parent,album_order,skin,hidden,album_cover,album_cover_filename,media_count,album_disk_usage,last_update,album_views,display_album_desc,enable_album_views,image_skin,album_skin,display_skin,enable_comments,exif_display,enable_rating,playback_type,tn_attached,enable_slideshow,enable_random,enable_shutterfly,enable_views,enable_keywords,enable_html,enable_sort,enable_rss,enable_postcard,albums_first,allow_download,full_display,tn_size,max_image_height,max_image_width,max_filesize,display_image_size,display_rows,display_columns,valid_formats,filename_title,shopping_cart,wm_auto,wm_id,opacity,wm_location,album_sort_order,member_uploads,moderate,email_mod,featured,cbposition,cbpage,owner_id,group_id,mod_group_id,perm_owner,perm_group,perm_members,perm_anon,podcast,mp3ribbon,tnheight,tnwidth,usealternate,rsschildren';
        $sqlDataValues = "$this->id,'$this->title','$this->description',$this->parent,$this->order,'$this->skin',$this->hidden,'$this->cover','$this->cover_filename',$this->media_count,$this->album_disk_usage,$this->last_update,$this->views,$this->display_album_desc,$this->enable_album_views,'$this->image_skin','$this->album_skin','$this->display_skin',$this->enable_comments,$this->exif_display,$this->enable_rating,$this->playback_type,$this->tn_attached,$this->enable_slideshow,$this->enable_random,$this->enable_shutterfly,$this->enable_views,$this->enable_keywords,$this->enable_html,$this->enable_sort,$this->enable_rss,$this->enable_postcard,$this->albums_first,$this->allow_download,$this->full,$this->tn_size,$this->max_image_height,$this->max_image_width,$this->max_filesize,$this->display_image_size,$this->display_rows,$this->display_columns,$this->valid_formats,$this->filename_title,$this->shopping_cart,$this->wm_auto,$this->wm_id,$this->wm_opacity,$this->wm_location,$this->album_sort_order,$this->member_uploads,$this->moderate,$this->email_mod,$this->featured,$this->cbposition,'$this->cbpage',$this->owner_id,$this->group_id,$this->mod_group_id,$this->perm_owner,$this->perm_group,$this->perm_members,$this->perm_anon,$this->podcast,$this->mp3ribbon,$this->tnHeight,$this->tnWidth,$this->useAlternate,$this->rssChildren";
        DB_save($_TABLES['mg_albums'], $sqlFieldList, $sqlDataValues);
        $c = glFusion\Cache::getInstance()->deleteItemsByTag('whatsnew');
    }

    function createAlbumID( ) {
        global $_TABLES;

        $sql = "SELECT MAX(album_id) + 1 AS nextalbum_id FROM " . $_TABLES['mg_albums'];
        $result = DB_query( $sql );
        $row = DB_fetchArray( $result );
        $aid = $row['nextalbum_id'];
        if ( $aid < 1 ) {
            $aid = 1;
        }
        if ( $aid == 0 ) {
            COM_errorLog("MediaGallery: Error - Returned 0 as album_id");
            $aid = 1;
        }
        return $aid;
    }

    function isMemberAlbum() {
        global $_MG_CONF, $MG_albums;

        if ( $_MG_CONF['member_albums'] != 1 ) {
            return false;
        }
        if ($MG_albums[0]->owner_id/*SEC_hasRights('mediagallery.admin')*/) {
            return false;
        }
        if ( $_MG_CONF['member_album_root'] == $this->parent ) {
            return true;
        }
        if ( $_MG_CONF['member_album_root'] == 0 ) {    // if root is the member root, everything will fall into
                                                        // the member album slot if they are enabled....
            return true;
        }

        // now walk up the chain and see if any parents are member root
        $parent = $this->parent;
        while ( $parent != 0 ) {
            if ( $MG_albums[$parent]->id == $_MG_CONF['member_album_root'] ) {
                return true;
            }
            $parent = $MG_albums[$parent]->parent;
        }
        return false;
    }

    function getTopParent() {
        global $MG_albums;

        $a = $this->parent;
        $p = $this->parent;
        while ( $p != 0 ) {
            $a = $p;
            $p = $MG_albums[$p]->parent;
        }
        return $a;
    }

    function getOffset() {
        global $MG_albums;

        $offset = 0;
        $topParent = $this->getTopParent();

        if ( $topParent == 0 ) {
            $pid = 0;
        } else {
            if ( isset($MG_albums[$topParent]->parent) ) {
                $pid = $MG_albums[$topParent]->parent;
            } else {
                return -1;
            }
        }
        if (!isset($MG_albums[$pid]->id) ) {
            return -1;
        }
        $children = $MG_albums[$pid]->getChildren();
        $rcount = count($children);

        if ( $this->parent == 0 ) {
            $matchID = $this->id;
        } else {
            $matchID = $MG_albums[$topParent]->id;
        }

        for ( $i=0; $i < $rcount; $i++ ) {
            if ( $MG_albums[$children[$i]]->access == 0 || ( $MG_albums[$children[$i]]->hidden && $MG_albums[$children[$i]]->access != 3) ) {
                //no op
            } else {
                $offset++;
            }
            if ( $children[$i] == $matchID /*$this->id*/ ) {
                return $offset - 1;
            }
        }
        return $offset -1 ;
    }


    function getNextSortOrder() {
        global $_TABLES;

        $sql = "SELECT MAX(album_order) + 10 AS nextalbum_order FROM " . $_TABLES['mg_albums'];
        $result = DB_query( $sql );
        $row = DB_fetchArray( $result);
        if ($row == NULL || $result == NULL ) {
            $albumOrder = 10;
        } else {
            $albumOrder = $row['nextalbum_order'];
            if ( $albumOrder < 0 ) {
                $albumOrder = 10;
            }
        }
        if ( $albumOrder == NULL )
            $albumOrder = 10;
        return $albumOrder;
    }

    function updateChildPermissions( $force_update ){
        global $_TABLES, $MG_albums;

        if ( $this->id == 0 ) {
            return true;
        }

        $children = $this->getChildren();

        foreach($children as $child) {
            $change = 0;
            if ($MG_albums[$child]->perm_owner > $this->perm_owner || $force_update) {
                $MG_albums[$child]->perm_owner = $this->perm_owner;
                $change = 1;
            }
            if ($MG_albums[$child]->perm_group > $this->perm_group || $force_update) {
                $MG_albums[$child]->perm_group = $this->perm_group;
                $change = 1;
            }
            if ($MG_albums[$child]->perm_members > $this->perm_members || $force_update) {
                $MG_albums[$child]->perm_members = $this->perm_members;
                $change = 1;
            }
            if ($MG_albums[$child]->perm_anon > $this->perm_anon || $force_update) {
                $MG_albums[$child]->perm_anon = $this->perm_anon;
                $change = 1;
            }
            if ($this->hidden || $force_update ) {
                $MG_albums[$child]->hidden = $this->hidden;
                $change = 1;
            }
            if ( $change == 1 ) {
                $MG_albums[$child]->saveAlbum();
            }
            $MG_albums[$child]->updateChildPermissions($force_update);
        }
        return true;
    }

    function setAccessRights( $mgadmin, $root, $groups ) {
        global $_USER, $MG_albums;

        if ($mgadmin || $root) {
            $this->access = 3;
        } else {
            if (COM_isAnonUser()) {
                $uid = 1;
            } else {
                $uid = $_USER['uid'];
            }
            if ( $uid == $this->owner_id ) {
                $this->access = $this->perm_owner;
            } else {
                if ( in_array( $this->group_id, $groups ) ) {
                    $this->access = $this->perm_group;
                } else {
                    if ( $uid == 1 ) {
                        $this->access = $this->perm_anon;
                    } else {
                        $this->access = $this->perm_members;
                    }
                }
            }
        }
        if ( $this->access == 3 ) {
            $MG_albums[0]->member_uploads = 1;
        }
        if ( $this->member_uploads ) {
            $MG_albums[0]->member_uploads = 1;
        }
    }

    function getChildren() {
        return (array_keys($this->children));
    }

    function getChildcount() {
        global $MG_albums;

        $numChildren = 0;
        $children = $this->getChildren();
        $x = count($children);
        for ($i=0; $i < $x; $i++ ) {
            if ( $MG_albums[$children[$i]]->access > 0 ) {
                if ( $MG_albums[$children[$i]]->hidden == 1 ) {
                    if ( $MG_albums[$children[$i]]->access == 3 ) {
                        $numChildren++;
                    }
                } else {
                    $numChildren++;
                }
            }
        }
        return $numChildren;
    }


    function getPath($hot=0, $sortOrder=0, $page=0) {
        global $MG_albums, $_MG_CONF;

        $path = ' ' . $_MG_CONF['seperator'] . ' ' . ($hot ? '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $this->id . '&amp;sort=' . $sortOrder . '&amp;page=' . $page . '">' : '') . ($_MG_CONF['truncate_breadcrumb'] > 0 ? COM_truncate(strip_tags($this->title),$_MG_CONF['truncate_breadcrumb'],'...') : strip_tags($this->title));

        $tree = $MG_albums[$this->parent];
        while ($tree->id != 0 ) {
            $path = ' ' . $_MG_CONF['seperator'] . ' <a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $tree->id . '&amp;sort=' . $sortOrder .'">' . ($_MG_CONF['truncate_breadcrumb'] > 0 ? COM_truncate(strip_tags($tree->title),$_MG_CONF['truncate_breadcrumb'],'...') : strip_tags($tree->title)) . '</a>' . $path;
            $tree = $MG_albums[$tree->parent];
        }
        return $path;
    }

    function getPath_ul($hot=0, $sortOrder=0, $page=0) {
        global $MG_albums, $_MG_CONF;

        $path = '<li>' . ($hot ? '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $this->id . '&amp;sort=' . $sortOrder . '&amp;page=' . $page . '">' : '') . ($_MG_CONF['truncate_breadcrumb'] > 0 ? COM_truncate(strip_tags($this->title),$_MG_CONF['truncate_breadcrumb'],'...') : strip_tags($this->title)).'</li>';

        $tree = $MG_albums[$this->parent];
        while ($tree->id != 0 ) {
            $path = '<li><a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $tree->id . '&amp;sort=' . $sortOrder .'">' . ($_MG_CONF['truncate_breadcrumb'] > 0 ? COM_truncate(strip_tags($tree->title),$_MG_CONF['truncate_breadcrumb'],'...') : strip_tags($tree->title)) . '</a></li>' . $path;
            $tree = $MG_albums[$tree->parent];
        }
        return $path;
    }

    function getMediaCount() {
        global $MG_albums;

        $mediaCount = 0;

        if ($this->access != 0 ) {
            if ( ($this->hidden && $MG_albums[0]->owner_id == 1 ) || $this->hidden != 1) {
                $mediaCount = $this->media_count;
            }
        }
        if (!empty($this->children) ) {
            $children = $this->getChildren();
            foreach($children AS $child) {
                if ($MG_albums[$child]->access > 0 ) {
                    if ( ( $MG_albums[$child]->hidden && $MG_albums[0]->owner_id == 1 ) || $MG_albums[$child]->hidden != 1 ) {
                        $mediaCount += $MG_albums[$child]->getMediaCount();
                    }
                }
            }
        }
        return $mediaCount;
    }

    function findCover() {
        global $MG_albums;

        if ( $this->cover_filename == '' ) {
            if ( !empty($this->children)) {
                $children = $this->getChildren();
                foreach($children as $child) {
                    if ( $MG_albums[$child]->cover_filename != '' && ($MG_albums[$child]->hidden != 1 || $MG_albums[$child]->hidden && $MG_albums[0]->owner_id) && $MG_albums[$child]->access > 0) {
                        return $MG_albums[$child]->cover_filename;
                    }
                    if ( ($MG_albums[$child]->hidden != 1 || ($MG_albums[$child]->hidden && $MG_albums[0]->owner_id == 1)) && $MG_albums[$child]->access > 0) {
                        $filename = $MG_albums[$child]->findCover();
                        if ( $filename != '' ) {
                            return $filename;
                        }
                    }
                }
            }
        } else {
            return $this->cover_filename;
        }
        return '';
    }

    function showTree( $depth ) {
        global $_CONF, $_MG_CONF, $MG_albums, $level;

        if ( $this->hidden == 1 && $this->access != 3 ) {
            return;
        }

        $retval = '';
        $px = ($level - 1 ) * 15;

        if ( $this->title != 'root album' && $this->access > 0 ) {
            if ( $level == 1 && $depth != 1) {
                $retval .= '<p>';
            }

            if ( $depth == 0 ) {
                $retval .= "<div style=\"margin-left:" . $px . "px;\">" . '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $this->id . '&amp;page=1">' . strip_tags($this->title) . '</a></div>';
            } else {
                if ( $level <= $depth ) {
                    $retval .= "<div style=\"margin-left:" . $px . "px;\">" . '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $this->id . '&amp;page=1">' . strip_tags($this->title) . '</a></div>';
                }
            }
        } else {
            if ($this->id == 0 ) {
                $retval .= '<br>';
            }
        }

        if ( !empty($this->children)) {
            $children = $this->getChildren();
            foreach($children as $child) {
                $level++;
                $retval .= $MG_albums[$child]->showTree($depth);
                $level--;
            }
        }
        return $retval;
    }

    function showCBTree( $depth ) {
        global $_CONF, $_MG_CONF, $MG_albums, $level;

        if ( $this->hidden == 1 && $this->access != 3 ) {
            return;
        }

        $z = 1;
        $retval = '';
        $px = ($level - 1 ) * 15;

        if ( $this->title != 'root album' && $this->access > 0 ) {
            if ( $level == 1 && $depth != 1) {
                $retval .= '<p>';
            }

            if ( $depth == 0 ) {
                $retval .= "<div style=\"margin-left:" . $px . "px;\">" . '<input type="checkbox" name="sel[]" value="' . $this->id . '">' . strip_tags($this->title) . '</div>';
            } else {
                if ( $level <= $depth ) {
                    $retval .= "<div style=\"margin-left:" . $px . "px;\">" . '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $this->id . '&amp;page=1">' . strip_tags($this->title) . '</a></div>';
                }
            }
        } else {
            if ($this->id == 0 ) {
                $retval .= '<br>';
            }
        }

        if ( !empty($this->children)) {
            $children = $this->getChildren();
            foreach($children as $child) {
                $level++;
                $retval .= $MG_albums[$child]->showCBTree($depth);
                $level--;
            }
        }
        return $retval;
    }


    function showSelectTree( $depth ) {
        global $_CONF, $MG_albums, $level, $counter;

        $z = 1;
        $retval = '';
        $block = '';
        $px = ($level - 1 ) * 15;

        if ( $this->id != 0 && $this->access > 0 ) {
            if ( $level == 1 && $depth != 1) {
                // our first one...
                $retval .= '<p>';
            }
            if ( !empty($this->children)) {

                $retval .= "<script><!--
                function enableBlock" . $this->id . "() {
                   if ( document.galselect.elements['album[" . $this->id ."]'].checked ) {" . LB;
                if ( !empty($this->children)) {
                    $children = $this->getChildren();
                    foreach($children as $child) {
                        $retval .= "document.galselect.elements['album[" . $MG_albums[$child]->id . "]'].disabled = true;" . LB;
                        $retval .= "document.galselect.elements['album[" . $MG_albums[$child]->id . "]'].checked = true;" . LB;
                    }
                }
                $retval .= LB . "} else {" . LB;
                if ( !empty($this->children)) {
                    $children = $this->getChildren();
                    foreach($children as $child) {
                        $retval .= "document.galselect.elements['album[" . $MG_albums[$child]->id . "]'].disabled = false;" . LB;
                        $retval .= "document.galselect.elements['album[" . $MG_albums[$child]->id . "]'].checked = false;" . LB;
                    }
                }
                $retval .= "}" . LB;
                if ( !empty($this->children)) {
                    $children = $this->getChildren();
                    foreach($children as $child) {
                        if (!empty ($MG_albums[$child]->children) ) {
                            $retval .= 'enableBlock' . $MG_albums[$child]->id . '();' . LB;
                        }
                    }
                }
                $retval .= LB . " }" . LB . "// -->" . LB . "</script>";
                $block = $this->id;
                $block = 'onclick="enableBlock' . $this->id . '()" onchange="enableBlock' . $this->id . '()"';
                if ( $this->parent != 0 )
                    $block .= ''; // $block .= ' disabled="enabled" ';
            } else {
                if ( $this->parent != 0 )
                    $block = ''; // $block = 'disabled="enabled"';
            }

            if ( $depth == 0 ) {
                $retval .= "<div style=\"margin-left:" . $px . "px;\">"  . '<input type="checkbox" name="album[' . $this->id . ']" id="album[' . $this->id . ']" value="1" ' . $block . '>&nbsp;&nbsp;' . strip_tags($this->title) . ' (' . COM_numberFormat($this->album_disk_usage/1024) . ' Kb)</div>' . LB;
            } else {
                if ( $level <= $depth ) {
                    $retval .= "<div style=\"margin-left:" . $px . "px;\">" . '<a href="' . $_CONF['site_url'] . '/mediagallery/album.php?aid=' . $this->id . '&amp;page=1">' . strip_tags($this->title) . '</a> (' . COM_numberFormat($this->album_disk_usage/1024) . ' Kb)</div>';
                }
            }
        } else {
            if ($this->id == 0 ) {
                $retval .= '<br>';
            }
        }
        $counter++;

        if ( !empty($this->children)) {
            $children = $this->getChildren();
            foreach($children as $child) {
                $level++;
                $retval .= $MG_albums[$child]->showSelectTree($depth);
                $level--;
            }
        }
        return $retval;
    }



    function getAlbumCount( $access ) {
        global $MG_albums;

        $count = 0;
        if ( $this->access >= $access ) {
            $count++;
        }
        if ( !empty($this->children)) {
            $children = $this->getChildren();
            foreach($children as $child) {
                $count += $MG_albums[$child]->getAlbumCount($access);
            }
        }
        return $count;
    }


    /*
     * This function will build a select box of albums the user has access to
     * this is used to create the jumpbox at the bottom of the page.
     */
    function buildJumpBox( $selected, $access=1,$hide=0 ) {
        global $MG_albums, $level, $album_jumpbox, $_MG_CONF;

        $count = 0;
        $indent = '';
        $z = 1;
        while ($z < $level) {
            $indent .= "--";
            $z++;
        }

        if ( $this->access >= $access || $this->id == 0 ) {
            if ( $this->id != $hide ) {
                if ( !$this->hidden || ( $this->hidden && ($MG_albums[0]->owner_id /*SEC_hasRights('mediagallery.admin')*/ ) ) ) {
                   $album_jumpbox .= '<option value="' . $this->id . '"' . ($this->id == $selected ? ' selected="selected" ' : '') .'>' . $indent;
                    $tatitle = strip_tags($this->title);
                    if ( strlen( $tatitle ) > 50 ) {
                        $aTitle = substr( $this->title, 0, 50 ) . '...';
                    } else {
                        $aTitle = $tatitle;
                    }
                    $album_jumpbox .= $aTitle .'</option>' . LB;
                    $count++;
                }
            }

            if ( !empty($this->children)) {
                $children = $this->getChildren();
                foreach($children as $child) {
                    $level++;
                    $count += $MG_albums[$child]->buildJumpBox($selected,$access,$hide);
                    $level--;
                }
            }
        }
        return $count;
    }

    /*
     * this function will return a list of valid albums for maint routines
     */
    function buildAlbumBox( $selected, $access=1,$hide=0, $type='upload' ) {
        global $MG_albums, $level, $album_selectbox;
        global $_MG_USERPREFS, $_USER, $_MG_CONF;

        $count = 0;
        $indent = '';
        $z = 1;
        while ($z < $level) {
            $indent .= "--";
            $z++;
        }

        if ( $type == 'upload' ) {
            if (
                ($_MG_CONF['member_albums'] && $this->isMemberAlbum() && $this->owner_id == $_USER['uid'] && $_MG_USERPREFS['active']) ||
                ( $this->member_uploads && $this->access >= 2 ) ||
                ( $this->access >= $access ) ||
                ( $MG_albums[0]->owner_id /*SEC_hasRights('mediagallery.admin')*/ )
                ) {

                if ( $this->id != $hide ) {
                    if ( !$this->hidden || ( $this->hidden && ($MG_albums[0]->owner_id /*SEC_hasRights('mediagallery.admin')*/ ) ) ) {
                        if ( $this->id != 0 ) {
                            $album_selectbox .= '<option value="' . $this->id . '"' . ($this->id == $selected ? ' selected="selected" ' : '') .'>' . $indent;
                            $tatitle = strip_tags($this->title);
                            if ( strlen( $tatitle ) > 25 ) {
                                $aTitle = substr( $this->title, 0, 25 ) . '...';
                            } else {
                                $aTitle = $tatitle;
                            }
                            $album_selectbox .= $aTitle .'</option>';
                            $count++;
                        }
                    }
                }
            }
        }

        if ( $type == 'edit' ) {
            if (
                ( $this->id == $selected ) ||
                ( $_MG_CONF['member_albums'] && $_MG_CONF['member_album_root'] == $this->id && $_MG_CONF['member_create_new'] && $_MG_USERPREFS['active']) ||
                ( $this->access >= $access )
                ) {
                if ( $this->id != $hide ) {
                    if ( !$this->hidden || ( $this->hidden && ($MG_albums[0]->owner_id /*SEC_hasRights('mediagallery.admin')*/ ) ) ) {
                        $album_selectbox .= '<option value="' . $this->id . '"' . ($this->id == $selected ? ' selected="selected" ' : '') .'>' . $indent;
                        $tatitle = strip_tags($this->title);
                        if ( strlen( $tatitle ) > 25 ) {
                            $aTitle = substr( $this->title, 0, 25 ) . '...';
                        } else {
                            $aTitle = $tatitle;
                        }
                        $aTitle = $tatitle; //  . '(' . $this->access . ')';
                        $album_selectbox .= $aTitle .'</option>';
                        $count++;
                    }
                }
            }
        }
        if ( $type == 'create' ) {
            if (
                ( $_MG_CONF['member_albums'] && $_MG_CONF['member_album_root'] == $this->id && $_MG_CONF['member_create_new'] && $_MG_USERPREFS['active']) ||
                ( $this->access >= $access )
                ) {
                if ( $this->id != $hide ) {
                    if ( !$this->hidden || ( $this->hidden && ($MG_albums[0]->owner_id ) ) ) {
                        if ( $this->id != 0 || (SEC_hasRights('mediagallery.admin') || ($_MG_CONF['member_albums'] == 1 && $_MG_CONF['member_album_root'] == 0 && $_MG_CONF['member_create_new']))) {
                            $album_selectbox .= '<option value="' . $this->id . '"' . ($this->id == $selected ? ' selected="selected" ' : '') .'>' . $indent;
                            $tatitle = strip_tags($this->title);
                            if ( strlen( $tatitle ) > 25 ) {
                                $aTitle = substr( $this->title, 0, 25 ) . '...';
                            } else {
                                $aTitle = $tatitle;
                            }
                            $album_selectbox .= $aTitle .'</option>';
                            $count++;
                        }
                    }
                }
            }
        }
        if ( $type == 'manage' ) {
            if ( $this->access >= $access ) {
                if ( !$this->hidden || ( $this->hidden && ($MG_albums[0]->owner_id ) ) ) {
                    if ( $this->id != 0 || (SEC_hasRights('mediagallery.admin') || ($_MG_CONF['member_albums'] == 1 && $_MG_CONF['member_album_root'] == 0 && $_MG_CONF['member_create_new']))) {
                        $album_selectbox .= '<option ' .  ($this->id == $hide ? 'disabled="disabled" ' : '') . ' value="' . $this->id . '"' . ($this->id == $selected && $this->id != $hide ? ' selected="selected" ' : '') . '>' . $indent;
                        $tatitle = strip_tags($this->title);
                        if ( strlen( $tatitle ) > 25 ) {
                            $aTitle = substr( $this->title, 0, 25 ) . '...';
                        } else {
                            $aTitle = $tatitle;
                        }
                        $album_selectbox .= $aTitle .'</option>';
                        $count++;
                    }
                }
            }
        }

        if ( !empty($this->children) ) {
            if ( $this->id != $hide || ( $this->id == $hide && $type == 'manage' ) ) {
                $children = $this->getChildren();
                foreach($children as $child) {
                    $level++;
                    $count += $MG_albums[$child]->buildAlbumBox($selected,$access,$hide,$type);
                    $level--;
                }
            }
        }
        return $count;
    }


    function albumThumbnail( ) {
        global $_CONF, $_MG_CONF, $_MG_USERPREFS, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $LANG_MG03, $MG_albums;

        if ($this->media_count  > 0 ) {
            if ( $this->cover_filename != '' && $this->cover_filename != '0' ) {
                $media_size = false;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $this->cover_filename[0] .'/' . $this->cover_filename . $ext) ) {
                        $album_last_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $this->cover_filename[0] .'/' . $this->cover_filename . $ext;
                        $mediasize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $this->cover_filename[0] .'/' . $this->cover_filename . $ext);
                        break;
                    }
                }
                $album_last_update  = MG_getUserDateTimeFormat($this->last_update);
                if ($mediasize == false ) {
                    $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                    $mediasize = array($this->tnWidth,$this->tnHeight);
                }
            } else {
                $filename = $this->findCover();
                if ( $filename == '' || $filename == NULL || $filename == " ") {
                    $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                    $mediasize = array($this->tnWidth,$this->tnHeight);
                } else {
                    $mediasize = false;
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] .'/' . $filename . $ext) ) {
                            $album_last_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] .'/' . $filename . $ext;
                            $mediasize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] .'/' . $filename . $ext);
                            break;
                        }
                    }
                    if ($mediasize == false ) {
                        $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                        $mediasize = array($this->tnWidth,$this->tnHeight); //@getimagesize($_MG_CONF['path_mediaobjects'] . 'missing.png');
                    }
                }
            }
            $album_media_count  = $this->media_count;
            if ( $this->last_update > 0 ) {
                $album_last_update = MG_getUserDateTimeFormat($this->last_update);
                $lang_updated = ($_MG_CONF['dfid']=='99' ? '' : $LANG_MG03['updated_prompt']);
            } else {
                $album_last_update[0] = '';
                $lang_updated = '';
            }
            $lang_updated = ($_MG_CONF['dfid']=='99' ? '' : $LANG_MG03['updated_prompt']);

            if (!COM_isAnonUser() ) {
                $lastlogin = DB_getItem ($_TABLES['userinfo'], 'lastlogin', "uid = '" . (int) $_USER['uid'] . "'");
                if ($this->last_update > $lastlogin) {
                    $album_last_update[0] = '<font color="red">' . $album_last_update[0] . '</font>';
                }
            }
        } else {  // nothing in the album yet...
            $filename = $this->findCover();
            if ( $filename == '' ) {
                $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                $mediasize = array($this->tnWidth,$this->tnHeight);
            } else {
                $mediasize = false;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] .'/' . $filename . $ext) ) {
                        $album_last_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] .'/' . $filename . $ext;
                        $mediasize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] .'/' . $filename . $ext);
                        break;
                    }
                }
                if ($mediasize == false ) {
                    $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                    $mediasize = array($this->tnWidth,$this->tnHeight); // @getimagesize($_MG_CONF['path_mediaobjects'] . 'missing.png');
                }
            }
            $album_last_update[0] = '';
            $lang_updated = '';
        }

        if ( $this->tn_attached == 1 ) {
            $mediasize = false;
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $this->id . $ext) ) {
                    $album_last_image = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $this->id . $ext;
                    $mediasize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $this->id . $ext);
                    break;
                }
            }
            if ($mediasize == false ) {
                $album_last_image = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                $mediasize = array($this->tnWidth,$this->tnHeight); //@getimagesize($_MG_CONF['path_mediaobjects'] . 'missing.png');
            }
        }

        $subalbums = count($this->children);
        $total_images_subalbums = $this->getMediaCount();

        if (isset($_MG_USERPREFS['tn_size']) && $_MG_USERPREFS['tn_size'] != -1 ) {
            $tn_size = $_MG_USERPREFS['tn_size'];
        } else {
            $tn_size = $MG_albums[$this->parent]->tn_size;
        }

        switch ($tn_size ) {
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
            case '4' :
            	$tn_height = $MG_albums[$this->parent]->tnHeight;
            	$tn_width  = $MG_albums[$this->parent]->tnWidth;
            	if ( $tn_height == 0 ) {
            	    $tn_height = 200;
            	}
            	if ( $tn_width == 0 ) {
            	    $tn_width = 200;
            	}
            	break;
            default :
                $tn_height = 200;
                $tn_width  = 200;
                break;
        }

        if ( $mediasize[0] > $mediasize[1] ) {
            $ratio = $mediasize[0] / $tn_height;
            $newwidth = $tn_height;
            $newheight = round($mediasize[1] / $ratio);
        } else {
            $ratio = $mediasize[1] / $tn_height;
            $newheight = $tn_height;
            $newwidth = round($mediasize[0] / $ratio);
        }

        $F = new Template($_MG_CONF['template_path']);
        $F->set_var('media_frame',$MG_albums[$this->parent]->albumFrameTemplate);
        $F->set_var(array(
            'border_width'          => $newwidth + 20,
            'border_height'         => $newheight + 20,
            'media_link_start'		=> '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $this->id .'&amp;page=1' . '">',
            'media_link_end'		=> '</a>',
            'url_media_item'        => $_MG_CONF['site_url'] . '/album.php?aid=' . $this->id .'&amp;page=1',
            'media_thumbnail'       => $album_last_image,
            'media_size'            => 'width="' . $newwidth . '" height="' . $newheight . '"',
            'media_height'          => $newheight,
            'media_width'           => $newwidth,
            'media_tag'             => $this->title,
            'frWidth'               => $newwidth  - $MG_albums[$this->parent]->afrWidth,
            'frHeight'              => $newheight - $MG_albums[$this->parent]->afrHeight,
        ));

        $F->parse('media','media_frame');
        $media_item_thumbnail = $F->finish($F->get_var('media'));

        $C = new Template( MG_getTemplatePath($this->parent) );
        if ( $this->parent != 0 && $MG_albums[$this->parent]->display_columns == 1 ) {
	        $C->set_file('cell','album_page_body_album_cell_1.thtml' );
        } else {
        	$C->set_file ('cell', 'album_page_body_album_cell.thtml');
    	}

        $C->set_var(array(
            'media_item_thumbnail' => $media_item_thumbnail,
            'media_item_thumbnail_raw' => $album_last_image,
            'media_link_start'		=> '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $this->id .'&amp;page=1' . '">',
            'media_link_end'		=> '</a>',
            'u_viewalbum'       => $_MG_CONF['site_url'] . '/album.php?aid=' . $this->id .'&amp;page=1',
            'album_last_image'  => $album_last_image,
            'album_title'       => $this->title,
            'album_media_count'  => $this->media_count,
            'subalbum_media_count' => $total_images_subalbums,
            'album_desc'        => PLG_replaceTags($this->description,'mediagallery','album_description'),
            'album_last_update' => $album_last_update[0],
            'img_height'        => $newheight,
            'img_width'         => $newwidth,
            's_media_size'      => 'width="' . $newwidth . '" height="' . $newheight . '"',
            'border_width'      => $newwidth + 20,
            'border_height'     => $newheight + 20,
            'row_height'        => $tn_height + 40,
            'updated'           => $lang_updated,
            'lang_album'        => $LANG_MG00['album'],
            'lang_views'        => $LANG_MG03['views'],
            'views'             => $this->views,
        ));
        if ( $this->enable_album_views ) {
            $C->set_var(array(
                'lang_views'        => $LANG_MG03['views'],
                'views'             => $this->views,
            ));
        } else {
            $C->set_var(array(
                'lang_views'        => '',
                'views'             => '',
            ));
        }

        if ( $subalbums > 0 ) {
            $C->set_var(array(
                'subalbumcount'     => '(' . $subalbums . ')',
                'lang_subalbums'    => $LANG_MG01['subalbums']
            ));
        } else {
            $C->set_var(array(
                'subalbumcount'     => '',
                'lang_subalbums'    => ''
            ));
        }
        PLG_templateSetVars('mediagallery',$C);
        $C->parse('output','cell');
        $celldisplay = $C->finish($C->get_var('output'));
        return $celldisplay;
    }

    function mooMenu( $depth=1, $counter=1, $prefix='' ) {
        global $_CONF, $_MG_CONF, $MG_albums;

        $retval = '';

        if ($this->parent != 0 ) {
            $parent = $prefix;
            $prefix = $prefix . '_' . $counter;
        } else {
            $prefix = $counter;
            $parent = '';
        }

        if ( $this->id != 0 && $this->access > 0 ) {
            $id = preg_replace('/_/','.',$prefix);
            if ( $parent == '' ) {
                $retval .=
                    "var node" . $prefix . " = tree.insert({
                        text:'" . strip_tags($this->title) . "',
                        open:false,
                        id:'" . $this->id . "'
                    });" . LB;
            } else {
                $retval .= "var node" . $prefix . " = node" . $parent . ".insert({text:'" . strip_tags($this->title) . "', open:false, id:'" . $this->id . "'});" . LB;
            }
        }

        if ( !empty($this->children)) {
            $children = $this->getChildren();
            foreach($children as $child) {
                $retval .= $MG_albums[$child]->mooMenu($depth,$counter,$prefix);
                $depth++;
                $counter++;
            }
        }
        return $retval;
    }
}
?>