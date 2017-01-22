<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | glFusion Story Image Import                                              |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2016 by the following authors:                        |
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
//

require_once '../../../../../lib-common.php';
require_once '../../../../auth.inc.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/init.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-batch.php';

USES_lib_story();
USES_class_story();

class mgAlbumg {

    var $id;
    var $mgid;
    var $sid;
    var $counter;
    var $title;
    var $description;
    var $parent;
    var $mgparent;
    var $order;
    var $hidden;
    var $cover;
    var $cover_filename;
    var $media_count;
    var $album_disk_usage;
    var $last_update;
    var $views;
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

    function __construct () {
        global $_MG_CONF;

        $this->children             = array();
        $this->id                   = 0;
        $this->mgid				    = 0;
        $this->title                = '';
        $this->description          = '';
        $this->parent               = 0;
        $this->mgparent			    = 0;
        $this->order                = 0;
        $this->hidden               = 0;
        $this->cover                = '-1';
        $this->cover_filename       = '';
        $this->media_count          = 0;
        $this->album_disk_usage     = 0;
        $this->last_update          = 0;
        $this->views                = 0;
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
        $this->enable_shutterfly    = $_MG_CONF['ad_enable_shutterfly'];
        $this->enable_views         = $_MG_CONF['ad_enable_views'];
        $this->display_album_desc   = $_MG_CONF['ad_display_album_desc'];
        $this->display_album_desc   = $_MG_CONF['ad_display_album_desc'];
        $this->enable_sort          = $_MG_CONF['ad_enable_sort'];
        $this->enable_rss           = $_MG_CONF['ad_enable_rss'];
        $this->enable_postcard      = $_MG_CONF['ad_enable_postcard'];
        $this->albums_first         = $_MG_CONF['ad_albums_first'];
        $this->allow_download       = $_MG_CONF['ad_allow_download'];
        $this->full                 = $_MG_CONF['ad_full_display'];
        $this->tn_size              = $_MG_CONF['ad_tn_size'];
        $this->max_image_height     = $_MG_CONF['ad_max_image_height'];
        $this->max_image_width      = $_MG_CONF['ad_max_image_width'];
        $this->max_filesize         = $_MG_CONF['ad_max_filesize'];
        $this->display_image_size   = $_MG_CONF['ad_display_image_size'];
        $this->display_rows         = $_MG_CONF['ad_display_rows'];
        $this->display_columns      = $_MG_CONF['ad_display_columns'];
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
        $this->access = 3;

    }

    function constructor( $album ) {
        global $_USER;
        $this->id               = $album['album_id'];
        $this->title            = $album['album_title'];
        $this->parent           = $album['album_parent'];
        $this->description      = $album['album_desc'];
        $this->order            = $album['album_order'];

        $this->owner_id         = $_USER['uid'];
        $this->group_id         = $album['group_id'];
        $this->mod_group_id     = $album['mod_group_id'];
        $this->offset           = $album['offset'];
    }

    function createAlbum( ) {
        global $mgAlbums, $_USER, $_TABLES, $_MG_CONF;

        if ($_MG_CONF['htmlallowed'] == 1 ) {
            $this->description  = DB_escapeString($this->description);
        } else {
            $this->title        = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords($this->title))));
            $this->description  = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords($this->description))));
        }

        // make sure we do not have SQL overflows...

	    $this->title = substr($this->title,0,254);

        $this->album_disk_usage = intval($this->album_disk_usage);
        $this->last_update      = intval($this->last_update);
        $this->views            = intval($this->views);
        $this->title            = DB_escapeString($this->title);
        $this->description      = DB_escapeString($this->description);

        $sqlFieldList  = 'album_id,album_title,album_desc,album_parent,album_order,hidden,album_cover,album_cover_filename,media_count,album_disk_usage,last_update,album_views,display_album_desc,enable_album_views,image_skin,album_skin,display_skin,enable_comments,exif_display,enable_rating,playback_type,tn_attached,enable_slideshow,enable_random,enable_shutterfly,enable_views,enable_sort,enable_rss,enable_postcard,albums_first,allow_download,full_display,tn_size,max_image_height,max_image_width,max_filesize,display_image_size,display_rows,display_columns,valid_formats,filename_title,shopping_cart,wm_auto,wm_id,opacity,wm_location,album_sort_order,member_uploads,moderate,email_mod,featured,cbposition,cbpage,owner_id,group_id,mod_group_id,perm_owner,perm_group,perm_members,perm_anon';
        $sqlDataValues = "$this->mgid,'$this->title','$this->description',$this->mgparent,$this->order,$this->hidden,'$this->cover','$this->cover_filename',0,$this->album_disk_usage,$this->last_update,$this->views,$this->display_album_desc,$this->enable_album_views,'$this->image_skin','$this->album_skin','$this->display_skin',$this->enable_comments,$this->exif_display,$this->enable_rating,$this->playback_type,$this->tn_attached,$this->enable_slideshow,$this->enable_random,$this->enable_shutterfly,$this->enable_views,$this->enable_sort,$this->enable_rss,$this->enable_postcard,$this->albums_first,$this->allow_download,$this->full,$this->tn_size,$this->max_image_height,$this->max_image_width,$this->max_filesize,$this->display_image_size,$this->display_rows,$this->display_columns,$this->valid_formats,$this->filename_title,$this->shopping_cart,$this->wm_auto,$this->wm_id,$this->wm_opacity,$this->wm_location,$this->album_sort_order,$this->member_uploads,$this->moderate,$this->email_mod,$this->featured,$this->cbposition,'$this->cbpage',$this->owner_id,$this->group_id,$this->mod_group_id,$this->perm_owner,$this->perm_group,$this->perm_members,$this->perm_anon";
        DB_save($_TABLES['mg_albums'], $sqlFieldList, $sqlDataValues);
    }

    function setChild( $id ) {
        global $mgAlbums;

        $this->children[$id] = $id;
    }

    function setAccessRights() {
        global $_USER;

        if (SEC_hasRights('mediagallery.admin')) {
            $this->access = 3;
        } else {
            $this->access = SEC_hasAccess($this->owner_id,
                                          $this->group_id,
                                          $this->perm_owner,
                                          $this->perm_group,
                                          $this->perm_members,
                                          $this->perm_anon);
        }
    }

    function getChildren() {
        return (array_keys($this->children));
    }

    function getChildcount() {
        global $mgAlbums;

        $numChildren = 0;
        $children = $this->getChildren();
        $x = count($children);
        for ($i=0; $i < $x; $i++ ) {
            if ( $mgAlbums[$children[$i]]->access > 0 ) {
                $numChildren++;
            }
        }
        return $numChildren;
    }

    function getAlbumID( ) {
        global $_TABLES;

        // set the album_id
        $sql = "SELECT MAX(album_id) + 1 AS nextalbum_id FROM " . $_TABLES['mg_albums'];
        $result = DB_query( $sql );
        $row = DB_fetchArray( $result );
        $album_id = $row['nextalbum_id'];
        if ( $album_id < 1 ) {
            $album_id = 1;
        }
        if ( $album_id == 0 ) {
            COM_errorLog("Media Gallery Error - Returned 0 as album_id");
            $album_id = 1;
        }
        return $album_id;
    }

    function getRoot( ) {
        global $mgAlbums, $_CONF, $_MG_CONF;

        if ( $this->parent == '0' ) {
            // done we are at the top...
            return (0);
        }
        $tree = $mgAlbums[$this->parent];
        while ($tree->parent != 0 ) {
            $tree = $mgAlbums[$tree->parent];
        }
        return ($tree->id);
    }

    function showTree( $depth ) {
        global $_CONF, $mgAlbums, $map, $revmap, $level, $counter;

        $z = 1;
        $retval = '';
        $px = ($level - 1 ) * 15;
        $block = '';
        if ( $this->id != 0 && $this->access > 0 ) {
            if ( $level == 1 && $depth != 1) {
                // our first one...
                $retval .= '<p>';
            }
            if ( !empty($this->children)) {

                $retval .= "<script><!--
                function enableBlock" . $this->id . "() {
                   if ( document.galselect.elements['gallery[" . $this->id ."]'].checked ) {" . LB;
                if ( !empty($this->children)) {
                    $children = $this->getChildren();
                    foreach($children as $child) {
                        $retval .= "document.galselect.elements['gallery[" . $mgAlbums[$child]->id . "]'].disabled = false;" . LB;
                    }
                }
                $retval .= LB . "} else {" . LB;
                if ( !empty($this->children)) {
                    $children = $this->getChildren();
                    foreach($children as $child) {
                        $retval .= "document.galselect.elements['gallery[" . $mgAlbums[$child]->id . "]'].disabled = true;" . LB;
                        $retval .= "document.galselect.elements['gallery[" . $mgAlbums[$child]->id . "]'].checked = false;" . LB;
                    }
                }
                $retval .= "}" . LB;
                if ( !empty($this->children)) {
                    $children = $this->getChildren();
                    foreach($children as $child) {
                        if (!empty ($mgAlbums[$child]->children) ) {
                            $retval .= 'enableBlock' . $mgAlbums[$child]->id . '();' . LB;
                        }
                    }
                }
                $retval .= LB . " }" . LB . "// -->" . LB . "</script>";
                $block = $this->id;
                $block = 'onclick="enableBlock' . $this->id . '()" onchange="enableBlock' . $this->id . '()"';
                if ( $this->parent != 0 )
                    $block .= ' disabled="disabled" ';
            } else {
                if ( $this->parent != 0 )
                    $block = 'disabled="disabled"';
            }

            if ( $depth == 0 ) {
                $retval .= "<div style=\"margin-left:" . $px . "px;\">"  . '<input type="checkbox" name="gallery[' . $this->id . ']" id="gallery[' . $this->id . ']" value="1" ' . $block . ' />&nbsp;&nbsp;' . strip_tags(COM_stripslashes($this->title)) . ' (' . $this->media_count . ') </div>' . LB;
            } else {
                if ( $level <= $depth ) {
                    $retval .= "<div style=\"margin-left:" . $px . "px;\">" . '<a href="' . $_CONF['site_url'] . '/mediagallery/album.php?aid=' . $this->id . '&page=1">' . strip_tags(COM_stripslashes($this->title)) . '</a></div>';
                }
            }
        } else {
            if ($this->id == 0 ) {
                $retval .= '<br />';
            }
        }
        $counter++;

        if ( !empty($this->children)) {
            $children = $this->getChildren();
            foreach($children as $child) {
                $level++;
                $retval .= $mgAlbums[$child]->showTree($depth);
                $level--;
            }
        }
        return $retval;
    }
    function getAlbumCount( $access ) {
        global $mgAlbums;

        $count = 0;
        if ( $this->access >= $access ) {
            $count++;
        }
        if ( !empty($this->children)) {
            $children = $this->getChildren();
            foreach($children as $child) {
                $count += $mgAlbums[$child]->getAlbumCount($access);
            }
        }
        return $count;
    }
}

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.admin')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Import Page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function _MG_remove_images($sid, $intro, $body, $usage='html') {
    global $_CONF, $_TABLES, $LANG24;

    $result = DB_query("SELECT ai_filename FROM {$_TABLES['article_images']} WHERE ai_sid = '$sid' ORDER BY ai_img_num");
    $nrows = DB_numRows($result);
    $errors = array();
    $stdImageLoc = true;
    if (!strstr($_CONF['path_images'], $_CONF['path_html'])) {
        $stdImageLoc = false;
    }
    for ($i = 1; $i <= $nrows; $i++) {
        $A = DB_fetchArray($result);
        $norm  = '[image' . $i . ']';
        $left  = '[image' . $i . '_left]';
        $right = '[image' . $i . '_right]';

        $unscalednorm  = '[unscaled' . $i . ']';
        $unscaledleft  = '[unscaled' . $i . '_left]';
        $unscaledright = '[unscaled' . $i . '_right]';

        $intro = str_replace($norm, '', $intro);
        $body  = str_replace($norm, '', $body);

        $intro = str_replace($left, '', $intro);
        $body  = str_replace($left, '', $body);

        $intro = str_replace($right, '', $intro);
        $body  = str_replace($right, '', $body);

        if (($_CONF['allow_user_scaling'] == 1) and ($_CONF['keep_unscaled_image'] == 1)) {
            $intro = str_replace($unscalednorm,  '', $intro);
            $body  = str_replace($unscalednorm,  '', $body);
            $intro = str_replace($unscaledleft,  '', $intro);
            $body  = str_replace($unscaledleft,  '', $body);
            $intro = str_replace($unscaledright, '', $intro);
            $body  = str_replace($unscaledright, '', $body);
        }
    }
    return array($errors, $intro, $body);
}


function MG_importSelectAlbums() {
    global $mgAlbums, $_TABLES, $_CONF, $_MG_CONF, $LANG_MG02, $_USER, $_POST;
    global $MG_albums, $album_jumpbox;

    $valid_albums = '';
    $display = '';

    $T = new Template($_MG_CONF['template_path']);
    $T->set_file(array('page' => 'gl_story_import.thtml'));

    $level = 0;
    $album_jumpbox  = '<select name="destination">';
    $album_jumpbox .= '<option value="0">Create Album Using Story Title</option>';
    $valid_albums .= $MG_albums[0]->buildJumpBox('',3);
    $album_jumpbox .= '</select>';

    MG_buildImportAlbums();
    $picklist = $mgAlbums[0]->showTree(0);

    $T->set_var(array(
        'form_action'   => $_CONF['site_admin_url'] . '/plugins/mediagallery/importers/gl_story/index.php',
        'dest_select'   => $album_jumpbox,
        'picklist'      => $picklist,
    ));
    $T->parse('output','page');
    $display .= $T->finish($T->get_var('output'));
    return $display;
}


function MG_buildImportAlbums( ) {
    global $mgAlbums, $_TABLES, $_CONF, $_MG_CONF, $_USER;

    $mgalbum = new mgAlbumg();
    $mgalbum->id = 0;
    $mgalbum->title = 'root';
    $mgAlbums[0] = $mgalbum;

    $counter = 1;

//    $sql = "SELECT * FROM {$_TABLES['stories']}";
    $sql = "SELECT *, UNIX_TIMESTAMP(date) AS unixdate, UNIX_TIMESTAMP(expire) as expireunix, UNIX_TIMESTAMP(comment_expire) as cmt_expire_unix FROM {$_TABLES['stories']}";
    $result     = DB_query( $sql );
    $numRows    = DB_numRows( $result );
    for ($i=0; $i < $numRows; $i++ ) {
        $I      = DB_fetchArray($result);
        $story = new Story();
        $story->loadFromArray($I);
        $tmpsid = $I['sid'];
        $icount = DB_count ($_TABLES['article_images'], 'ai_sid', $tmpsid);
        if ( $icount == 0 ) {
            continue;
        }
        $mgalbum = new mgAlbumg();
        $mgalbum->id = $counter;
        $mgalbum->sid = $I['sid'];

        $mgalbum->title = $I['title'];
        $mgalbum->parent = 0;
        $intro = $story->replaceImages($I['introtext']);
        $body  = $story->replaceImages($I['bodytext']);
//        list($intro,$body) = STORY_replace_images($I['sid'],$I['introtext'],$I['bodytext']);
        list($errors,$intro_notags,$body_notags) = _MG_remove_images($I['sid'], $intro, $body,'html');

        $mgalbum->description   = ''; //$intro_notags;
        $mgalbum->views         = 0;
        $mgalbum->media_count	= $icount;
        $mgalbum->owner_id      = $I['owner_id'];
        $mgalbum->perm_owner    = $I['perm_owner'];
        $mgalbum->perm_group    = $I['perm_group'];
        $mgalbum->perm_members  = $I['perm_members'];
        $mgalbum->perm_anon     = $I['perm_anon'];
        $mgAlbums[$counter] = $mgalbum;
        $counter++;
    }

    foreach( $mgAlbums as $id => $mgalbum) {
        if ($id != 0 && isset($mgAlbums[$mgalbum->parent]->id) ) {
            $mgAlbums[$mgalbum->parent]->setChild($id);
        }
    }
    return;
}

function MG_importAlbums( $aid, $parent, $session_id=0 ) {
    global $mgAlbums, $_TABLES, $_CONF, $_MG_CONF, $_USER, $_POST;

    $children = $mgAlbums[$aid]->getChildren();
    $nrows = count($children);
    $checkCounter = 0;

    for ($i=0; $i < $nrows; $i++ ) {
        $x = $mgAlbums[$children[$i]]->id;
        if ( $_POST['gallery'][$x] == 1 ) {
            if ( $parent == 0 ) {
                $sql = "SELECT MAX(album_id) + 1 AS nextalbum_id FROM " . $_TABLES['mg_albums'];
                $result2 = DB_query( $sql );
                $row2 = DB_fetchArray( $result2 );
                $A['album_id'] = $row2['nextalbum_id'];
                if ( $A['album_id'] < 1 ) {
                    $A['album_id'] = 1;
                }
                if ( $A['album_id'] == 0 ) {
                    COM_errorLog("Media Gallery Error - Returned 0 as album_id");
                    $A['album_id'] = 1;
                }

                // now, let's create this bad boy....

                $sql = "SELECT MAX(album_order) + 1 AS nextalbum_order FROM " . $_TABLES['mg_albums'];
                $result2 = DB_query( $sql );
                $row2 = DB_fetchArray( $result2 );
                if ($row2 == NULL || $result2 == NULL ) {
                    $A['album_order'] = 10;
                } else {
                    $A['album_order'] = $row2['nextalbum_order'];
                    if ( $A['album_order'] < 0 ) {
                        $A['album_order'] = 10;
                    }
                }
                if ( $A['album_order'] == NULL )
                    $A['album_order'] = 10;

                $mgAlbums[$children[$i]]->mgid = $A['album_id'];
                $mgAlbums[$children[$i]]->order = $A['album_order'];

                if (!empty($mgAlbums[$children[$i]]->children) ) {
                    $subChildren = $mgAlbums[$children[$i]]->getChildren();
                    foreach($subChildren AS $child1) {
                        $mgAlbums[$child1]->mgparent = $A['album_id'];
                    }
                }
                $usergroups = SEC_getUserGroups();
                for ($m = 0; $m < count($usergroups); $m++) {
                    if ('mediagallery Admin' == key($usergroups)) {
                        $mgAlbums[$children[$i]]->group_id = $usergroups[key($usergroups)];
                        $mgAlbums[$children[$i]]->mod_group_id = $usergroups[key($usergroups)];
                    }
                    next($usergroups);
                }
                $rc = $mgAlbums[$children[$i]]->createAlbum();
            } else {
                $mgAlbums[$children[$i]]->mgid = $parent;
            }
            COM_errorLog("Media Gallery: glFusion Story Import processed " . $mgAlbums[$children[$i]]->title . " MGID: " . $mgAlbums[$children[$i]]->mgid . " Parent: " . $mgAlbums[$children[$i]]->mgparent);

            MG_importFiles($mgAlbums[$children[$i]]->mgid, $mgAlbums[$children[$i]]->id,$session_id);

            if (!empty($mgAlbums[$children[$i]]->children) ) {
                MG_importAlbums($mgAlbums[$children[$i]]->id, $A['album_id'], $session_id);
            }

        }
    }
}

function MG_importFiles( $album_id, $import_album_id, $session_id ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG02, $LANG_MG03, $_POST, $new_media_id, $glversion;
    global $mgAlbums;

    // now grab the images for this story....

    $tmpsid = $mgAlbums[$import_album_id]->sid;

    $sql = "SELECT * FROM {$_TABLES['article_images']} WHERE ai_sid='" . $tmpsid . "'";
    $iResult = DB_query( $sql );
    while ( $I=DB_fetchArray($iResult) ) {
        $imageNumber = $I['ai_img_num'];
        if ($_CONF['keep_unscaled_image'] == 1) {
	        $lFilename = substr_replace ($I['ai_filename'], '_original.',
    	            strrpos ($I['ai_filename'], '.'), 1);
        	$lFilename_complete = $_CONF['path_images'] . 'articles/'
            	                      . $lFilename;
		} else {
			$lFilename = $I['ai_filename'];
			$lFilename_complete = $_CONF['path_images'] . 'articles/' . $lFilename;
		}
		$mid   = 0;
        $aid   = $album_id;
        $data  = $lFilename_complete;
        $data2 = $I['ai_img_num'];
        $data3 = $tmpsid;

        DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data,data2,data3,status)
                  VALUES('$session_id','$mid',$aid,'" . DB_escapeString($data) . "','" . DB_escapeString($data2) . "','" . DB_escapeString($data3) . "',0)");
    }
}


// -- main processing here...
MG_initAlbums();
$display = '';

if (isset($_POST['mode']) ) {
    $mode = COM_applyFilter($_POST['mode']);

    switch ($mode) {
        case 'convert' :
            // pull the session options...

            $autotag        = COM_applyFilter($_POST['autotag']);
            $alignment      = COM_applyFilter($_POST['alignment']);
            $delete         = COM_applyFilter($_POST['delete']);
            $destination    = COM_applyFilter($_POST['destination']);

            $session_description = 'glFusion Story Image Import';
            $session_id = MG_beginSession('gl_storyimport',$_MG_CONF['site_url'] . '/index.php',$session_description,$autotag, $alignment, $delete,$destination );
            MG_buildImportAlbums();
            MG_importAlbums( 0, $destination, $session_id );
            $display  = MG_siteHeader();
            $display .= MG_continueSession($session_id,0,30);
            $display .= MG_siteFooter();
            echo $display;
            exit;
        case 'cancel' :
            echo COM_refresh($_CONF['site_admin_url'] . '/plugins/mediagallery/index.php');
            exit;
            break;
        default :
            $display = MG_siteHeader();
            $display .= MG_importSelectAlbums();
            echo $display;
            exit;
    }
}
$display  = MG_siteHeader();
$display .= MG_importSelectAlbums();
$display .= MG_siteFooter();
echo $display;
?>