<?php
//
// +---------------------------------------------------------------------------+
// | Forum Plugin for Geeklog - The Ultimate Weblog                            |
// +---------------------------------------------------------------------------+
// | config.php                                                                |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2002 by the following authors:                              |
// |                                                                           |
// | Author:                                                                   |
// | Copyright (C) 2002,2003,2004,2005 by the following authors:               |
// | Blaine Lang                 -    blaine@portalparts.com                   |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//


$CONF_FORUM['debug'] = false;
$CONF_FORUM['version'] = '2.7';

// Set to true if you are using MYSQL 4.0 or greater and this will improve performance.
$CONF_FORUM['mysql4+'] = false;

// Set to true if you need to handle previous version 2.5 quotes and new line formatting - setting to false should be faster
$CONF_FORUM['pre2.5_mode'] = true;

// When a user or moderator edits a story - if the default should be to not change post timestamps
// and trigger any user notifications - then set default as true
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

$CONF_FORUM['use_glmenu'] = false;            // Should glMenu be used for this menublock;


// Mapping of Group Names to badges that can optionally be displayed in Forum Post under user avatar
// Place images in the directory {theme}/forum/image_set/badges
// Note Root needs a unique mapping since if you are in the Root group, then you are in all groups
$CONF_FORUM['grouptags'] = array(
    'Root'      => 'siteadmin_badge.png',
    'Logged-in Users' => 'forum_user.png',
    'Group A'   => 'badge1.png',
    'Group B'   => 'badge2.png'
);


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

if ($pi_version >= 2.6) {
    /* Retrieve the list of blocks to show on the left side and make the forum menu the first block */
    $CONF_FORUM['leftblocks'] = array ('forum_menu');
    $CONF_FORUM['leftblocks'] = ppGetUserBlocks($CONF_FORUM['leftblocks']);

    /* Don't change any settings below this line */
    /* Retrieve the forum global settings and user preferences and save to config array */
    $result = DB_query("SELECT * FROM {$_TABLES['gf_settings']}");
    $A = DB_fetchArray($result);
    $CONF_FORUM['registration_required']  = $A['registrationrequired'];
    $CONF_FORUM['registered_to_post']     = $A['registerpost'];
    $CONF_FORUM['allow_html']             = $A['allowhtml'];
    $CONF_FORUM['post_htmlmode']          = $A['post_htmlmode'];    
    $CONF_FORUM['use_glfilter']           = $A['glfilter'];
    $CONF_FORUM['use_geshi']              = $A['use_geshi_formatting'];
    $CONF_FORUM['use_censor']             = $A['censor'];
    $CONF_FORUM['show_moods']             = $A['showmood'];
    $CONF_FORUM['allow_smilies']          = $A['allowsmilies'];
    $CONF_FORUM['allow_notification']     = $A['allow_notify'];
    $CONF_FORUM['allow_user_dateformat']  = $A['allow_userdatefmt'];
    $CONF_FORUM['show_topicreview']       = $A['showiframe'];
    $CONF_FORUM['use_autorefresh']        = $A['autorefresh'];
    $CONF_FORUM['autorefresh_delay']      = $A['refresh_delay'];
    $CONF_FORUM['show_subject_length']    = $A['viewtopicnumchars'];
    $CONF_FORUM['show_topics_perpage']    = $A['topicsperpage'];
    $CONF_FORUM['show_posts_perpage']     = $A['postsperpage'];
    $CONF_FORUM['show_messages_perpage']  = $A['messagesperpage'];
    $CONF_FORUM['show_searches_perpage']  = $A['searchesperpage'];
    $CONF_FORUM['views_tobe_popular']     = $A['popular'];
    $CONF_FORUM['convert_break']          = $A['html_newline'];
    $CONF_FORUM['min_comment_length']     = $A['min_comment_len'];
    $CONF_FORUM['min_username_length']    = $A['min_name_len'];
    $CONF_FORUM['min_subject_length']     = $A['min_subject_len'];
    $CONF_FORUM['post_speedlimit']        = $A['speedlimit'];
    $CONF_FORUM['use_smilies_plugin']     = $A['use_smiliesplugin'];
    $CONF_FORUM['use_pm_plugin']          = $A['use_pmplugin'];
    $CONF_FORUM['use_spamx_filter']       = $A['use_spamxfilter'];
    $CONF_FORUM['show_centerblock']       = $A['cb_enable'];
    $CONF_FORUM['centerblock_homepage']   = $A['cb_homepage'];
    $CONF_FORUM['centerblock_where']      = $A['cb_where'];
    $CONF_FORUM['cb_subject_size']        = $A['cb_subjectsize'];
    $CONF_FORUM['centerblock_numposts']   = $A['cb_numposts'];
    $CONF_FORUM['sb_subject_size']        = $A['sb_subjectsize'];
    $CONF_FORUM['sb_latestpostonly']      = $A['sb_latestposts'];
    $CONF_FORUM['sideblock_numposts']     = $A['sb_numposts'];
    $CONF_FORUM['allowed_editwindow']     = $A['edit_timewindow'];

    $CONF_FORUM['level1']                 = $A['level1'];
    $CONF_FORUM['level2']                 = $A['level2'];
    $CONF_FORUM['level3']                 = $A['level3'];
    $CONF_FORUM['level4']                 = $A['level4'];
    $CONF_FORUM['level5']                 = $A['level5'];
    $CONF_FORUM['level1name']             = $A['level1name'];
    $CONF_FORUM['level2name']             = $A['level2name'];
    $CONF_FORUM['level3name']             = $A['level3name'];
    $CONF_FORUM['level4name']             = $A['level4name'];
    $CONF_FORUM['level5name']             = $A['level5name'];

    // User Preference Config Parms. Check if user has set their preference or use defaults
    if(!empty($_USER['uid']) AND DB_getItem($_TABLES['gf_userprefs'],"uid","uid='{$_USER['uid']}'") == $_USER['uid']) {
        $sql = DB_query("Select * from {$_TABLES['gf_userprefs']} where uid = '{$_USER['uid']}'");
        $userprefs = DB_fetchArray($sql);
        $CONF_FORUM['show_topics_perpage']        = $userprefs['topicsperpage'];
        $CONF_FORUM['show_posts_perpage']         = $userprefs['postsperpage'];
        $CONF_FORUM['popular_limit']              = $userprefs['popularlimit'];
        $CONF_FORUM['show_members_perpage']       = $userprefs['membersperpage'];
        $CONF_FORUM['show_search_perpage']        = $userprefs['searchlines'];
        $CONF_FORUM['show_topicreview']           = $userprefs['showiframe'];
        $CONF_FORUM['show_anonymous_posts']       = $userprefs['viewanonposts'];
        $CONF_FORUM['notify_once']                = $userprefs['notify_once'];
    } else {
        // How many messages to show on the Most Popular page
        $CONF_FORUM['popular_limit'] = '20';
        // How many lines to show on one page in the search results
        $CONF_FORUM['show_search_perpage'] = 15;
        // How many users to show on one page in the memberlist results
        $CONF_FORUM['show_members_perpage'] = 100;
        // View Anonymous Posts - registed users can set this false
        $CONF_FORUM['show_anonymous_posts'] = 1;
        // Only send Notification once - even if more posts are created since your last visit
        $CONF_FORUM['notify_once'] = 1;
    }

}

?>