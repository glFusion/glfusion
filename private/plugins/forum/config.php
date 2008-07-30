<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | config.php                                                               |
// |                                                                          |
// | Forum configuration options.                                             |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Forum Plugin for Geeklog CMS                                |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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

$CONF_FORUM['version'] = '3.1.0.fusion';

$CONF_FORUM['debug'] = false;

/********************* FORUM PLUGIN v2.7+ Setup for block layout to use ******
* Fourm Plugin for glFusion v1.0.- available at http://www.glfusion.org
* Set the following for which glFusion block columns you want to show along
* with the forum. Options are: 'leftblocks', 'rightblocks', 'allblocks',
* 'noblocks'
*
* For example, set to noblocks to not show any blocks (and have the forum
* span the entire page.)
*****************************************************************************/
$CONF_FORUM['showblocks'] = 'leftblocks';

/********************* FORUM PLUGIN v2.7+  Setup for user menu style to use **
* Show the usermenu as a block menu or as a top navbar
* Note: Need to show leftblocks or rightblocks if usermenu option set to
* blockmenu.  Options are 'blockmenu' or 'navbar' or 'none'
******************************************************************************/
$CONF_FORUM['usermenu'] = 'blockmenu';

// Set to true if you are using MYSQL 4.0 or greater and this will improve
// performance.
$CONF_FORUM['mysql4+'] = false;

// Set to true if you need to handle previous version 2.5 quotes and new line
// formatting - setting to false should be faster
$CONF_FORUM['pre2.5_mode'] = false;

// When a user or moderator edits a story - if the default should be to not
// change post timestamps and trigger any user notifications - then set default
// as true
$CONF_FORUM['silent_edit_default'] = true;

// Able to set the width of the member avatar in pixels.
// Default is to use the member's uploaded image and size GL site has for creating this thumbnail image.
// If a value is defined for this setting, the forum will resize the displayed image to this defined width
$CONF_FORUM['avatar_width'] = '';

// The BBCode tag [img] is enabled by default - set this to false to disable it
$CONF_FORUM['allow_img_bbcode'] = true;

// Disabled by default for performance gains. Enable if you need to show moderators on the main forum index page
$CONF_FORUM['show_moderators'] = false;

$CONF_FORUM['imgset'] = $_CONF['layout_url'] .'/forum/image_set';
$CONF_FORUM['imgset_path'] = $_CONF['path_layout'] .'/forum/image_set';

/* The forum uses a number of icons and you may have a need to use a mixture of image types.
 * Enabling the $CONF_FORUM['autoimagetype'] feature will invoke a function that will first
 * check for an image of the type set in your themes function.php $_IMAGE_TYPE
 * If the icon of that image type is not found, then it will use an image of type
 * specified by the $CONF_FORUM['image_type_override'] setting.

 * Set $CONF_FORUM['autoimagetype'] to false to disable this feature and
 * only icons of type set by the themes $_IMAGE_TYPE setting will be used
*/
$CONF_FORUM['autoimagetype'] = true;
$CONF_FORUM['image_type_override'] = 'gif';

// Default date/time format to use if Forum setting for allow user-dateformat is disabled
$CONF_FORUM['default_Datetime_format'] = '%m/%d/%y %H:%M %p';
// $CONF_FORUM['default_Datetime_format'] = '%b %e @ %H:%M';

// Date format that is shown at the top of of the topic post used if Forum setting for allow user-dateformat is disabled
$CONF_FORUM['default_Topic_Datetime_format'] = '%B %d %Y %H:%M %p';

/* Number of characters of the topic contents when hovering over the topic post subject link */
$CONF_FORUM['contentinfo_numchars'] = 256;
/* Width of pop-up info window that is displayed when hovering over topic posts. Also refer to the CSS declaration 'info' */
$CONF_FORUM['linkinfo_width'] = 40;

/* Format style for quotes */
$CONF_FORUM['quoteformat'] = "[QUOTE][u]Quote by: %s[/u][p]%s[/p][/QUOTE]";

$CONF_FORUM['show_popular_perpage'] = '20';    // @TODO: Need to make this an online admin setting

$CONF_FORUM['show_last_post_count'] = '20';    // @TODO: Number of posts to show in the member last post page

$CONF_FORUM['use_glmenu'] = false;          // Should glMenu be used for this menublock;

// Mapping of Group Names to badges that can optionally be displayed in Forum Post under user avatar
// Place images in the directory {theme}/forum/image_set/badges
// Note Root needs a unique mapping since if you are in the Root group, then you are in all groups
$CONF_FORUM['grouptags'] = array(
    'Root'              => 'siteadmin_badge.png',
    'Logged-in Users'   => 'forum_user.png',
    'Group A'           => 'badge1.png',
    'Group B'           => 'badge2.png'
);

/* Settings for the attachment feature */
$CONF_FORUM['maxattachments']   = 5;      // Maximum number of attachments allowed in a single post
$CONF_FORUM['uploadpath']       = $_CONF['path_html'] . 'forum/media';
$CONF_FORUM['downloadURL']      = $_CONF['site_url'] . '/forum/media';
$CONF_FORUM['fileperms']        = '0755';  // Needs to be a string for the upload class use.

$CONF_FORUM['max_uploadimage_width']    = '2100';
$CONF_FORUM['max_uploadimage_height']   = '1600';
$CONF_FORUM['max_uploadfile_size']      = '6553600';     // 6.400 MB

// Identify the allowable file types for the attachment support feature
// Mapping of MIME types to attachment extension type
$CONF_FORUM['allowablefiletypes']    = array(
        'application/x-gzip-compressed'     => '.tgz',
        'application/x-zip-compressed'      => '.zip',
        'application/zip'                   => '.zip',
        'application/x-tar'                 => '.tar',
        'application/x-gtar'                => '.gtar',
        'application/x-gzip'                => '.gz',
        'text/plain'                        => '.php,.txt,.inc',
        'text/html'                         => '.html,.htm',
        'image/bmp'                         => '.bmp,.ico',
        'image/gif'                         => '.gif',
        'image/pjpeg'                       => '.jpg,.jpeg',
        'image/jpeg'                        => '.jpg,.jpeg',
        'image/png'                         => '.png',
        'image/x-png'                       => '.png',
        'audio/mpeg'                        => '.mp3',
        'audio/wav'                         => '.wav',
        'application/pdf'                   => '.pdf',
        'application/x-shockwave-flash'     => '.swf',
        'application/msword'                => '.doc',
        'application/vnd.ms-excel'          => '.xls',
        'application/vnd.ms-powerpoint'     => '.ppt',
        'application/vnd.ms-project'        => '.mpp',
        'application/vnd.visio'             => '.vsd',
        'text/plain'                        => '.txt',
        'application/x-pangaeacadsolutions' => '.dwg',
        'application/x-zip-compresseed'     => '.zip',
        'application/octet-stream'          => '.zip,.vsd,.fla,.psd,.xls,.doc,.ppt,.pdf,.swf,.mpp,.txt,.dwg'
        );

// Identify the MIME types that will support inline image use and auto generation of the thumbnails
// Mapping of MIME types to attachment extension type
$CONF_FORUM['inlineimageypes']    = array(
        'image/bmp'                         => '.bmp,',
        'image/gif'                         => '.gif',
        'image/pjpeg'                       => '.jpg,.jpeg',
        'image/jpeg'                        => '.jpg,.jpeg',
        'image/png'                         => '.png',
        'image/x-png'                       => '.png'
);
// Resize larger images automatically to the following size
// Creating a thumbnail image and retaining original
$CONF_FORUM['inlineimage_width']    = '300';
$CONF_FORUM['inlineimage_height']   = '300';

/*************************************************************************
*          Do not modify any settings below this area                    *
*************************************************************************/

// Adding the Forum Plugin tables to $_TABLES array
$_TABLES['gf_userprefs']    = $_DB_table_prefix . 'forum_userprefs';
$_TABLES['gf_topic']        = $_DB_table_prefix . 'forum_topic';
$_TABLES['gf_categories']   = $_DB_table_prefix . 'forum_categories';
$_TABLES['gf_forums']       = $_DB_table_prefix . 'forum_forums';
$_TABLES['gf_settings']     = $_DB_table_prefix . 'forum_settings';
$_TABLES['gf_watch']        = $_DB_table_prefix . 'forum_watch';
$_TABLES['gf_moderators']   = $_DB_table_prefix . 'forum_moderators';
$_TABLES['gf_banned_ip']    = $_DB_table_prefix . 'forum_banned_ip';
$_TABLES['gf_log']          = $_DB_table_prefix . 'forum_log';
$_TABLES['gf_userinfo']     = $_DB_table_prefix . 'forum_userinfo';
$_TABLES['gf_attachments']  = $_DB_table_prefix . 'forum_attachments';
$_TABLES['gf_bookmarks']    = $_DB_table_prefix . 'forum_bookmarks';
?>