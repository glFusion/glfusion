<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | install_defaults.php                                                     |
// |                                                                          |
// | Initial Installation Defaults used when loading the online configuration |
// | records. These settings are only used during the initial installation    |
// | and not referenced any more once the plugin is installed.                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
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

/*
 * Forum default settings
 *
 * Initial installation defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 *
 */

global $_FF_DEFAULT;

$_FF_DEFAULT = array();
$_FF_DEFAULT['registration_required']  = false;
$_FF_DEFAULT['enable_rating_system']   = false;
$_FF_DEFAULT['registered_to_post']     = true;
$_FF_DEFAULT['allow_html']             = false;
$_FF_DEFAULT['post_htmlmode']          = false;
$_FF_DEFAULT['use_glfilter']           = true;
$_FF_DEFAULT['use_geshi']              = true;
$_FF_DEFAULT['use_censor']             = true;
$_FF_DEFAULT['show_moods']             = true;
$_FF_DEFAULT['allow_smilies']          = true;
$_FF_DEFAULT['allow_notification']     = true;
$_FF_DEFAULT['allow_user_dateformat']  = true;
$_FF_DEFAULT['show_topicreview']       = true;
$_FF_DEFAULT['showtopic_review_order'] = 'DESC';
$_FF_DEFAULT['use_autorefresh']        = true;
$_FF_DEFAULT['autorefresh_delay']      = 5;
$_FF_DEFAULT['show_subject_length']    = 40;
$_FF_DEFAULT['show_topics_perpage']    = 10;
$_FF_DEFAULT['show_posts_perpage']     = 10;
$_FF_DEFAULT['show_messages_perpage']  = 20;
$_FF_DEFAULT['show_searches_perpage']  = 20;
$_FF_DEFAULT['views_tobe_popular']     = 20;
$_FF_DEFAULT['convert_break']          = false;
$_FF_DEFAULT['min_comment_length']     = 5;
$_FF_DEFAULT['min_username_length']    = 2;
$_FF_DEFAULT['min_subject_length']     = 2;
$_FF_DEFAULT['post_speedlimit']        = 60;
$_FF_DEFAULT['use_smilies_plugin']     = false;
$_FF_DEFAULT['use_pm_plugin']          = false;
$_FF_DEFAULT['use_spamx_filter']       = true;
$_FF_DEFAULT['use_sfs']                = true;
$_FF_DEFAULT['show_centerblock']       = true;
$_FF_DEFAULT['centerblock_homepage']   = true;
$_FF_DEFAULT['centerblock_where']      = 2;
$_FF_DEFAULT['cb_subject_size']        = 120;
$_FF_DEFAULT['centerblock_numposts']   = 10;
$_FF_DEFAULT['sb_subject_size']        = 20;
$_FF_DEFAULT['sb_latestpostonly']      = false;
$_FF_DEFAULT['sideblock_numposts']     = 5;
$_FF_DEFAULT['allowed_editwindow']     = 0;
$_FF_DEFAULT['level1']                 = 1;
$_FF_DEFAULT['level2']                 = 15;
$_FF_DEFAULT['level3']                 = 35;
$_FF_DEFAULT['level4']                 = 70;
$_FF_DEFAULT['level5']                 = 120;
$_FF_DEFAULT['level1name']             = 'Newbie';
$_FF_DEFAULT['level2name']             = 'Junior';
$_FF_DEFAULT['level3name']             = 'Chatty';
$_FF_DEFAULT['level4name']             = 'Regular Member';
$_FF_DEFAULT['level5name']             = 'Active Member';
$_FF_DEFAULT['showblocks']              = 'leftblocks';     // noblocks, leftblocks, rightblocks
$_FF_DEFAULT['usermenu']                = 'navbar';         // blockmenu, navbar, none
$_FF_DEFAULT['silent_edit_default']     = true;
$_FF_DEFAULT['avatar_width']            = 115;
$_FF_DEFAULT['allow_img_bbcode']        = true;
$_FF_DEFAULT['show_moderators']         = false;
$_FF_DEFAULT['imgset']                  = $_CONF['layout_url'] .'/forum/image_set';
$_FF_DEFAULT['imgset_path']             = $_CONF['path_layout'] .'/forum/image_set';
$_FF_DEFAULT['autoimagetype']           = true;
$_FF_DEFAULT['image_type_override']     = 'gif';
//$_FF_DEFAULT['default_Datetime_format'] = 'm/d/y h:i a';
//$_FF_DEFAULT['default_Topic_Datetime_format'] = 'M d Y H:i a';
$_FF_DEFAULT['contentinfo_numchars']    = 256;
$_FF_DEFAULT['linkinfo_width']          = 40;
$_FF_DEFAULT['quoteformat'] = "[QUOTE][u]Quote by: %s[/u][p]%s[/p][/QUOTE]";
$_FF_DEFAULT['show_last_post_count']    = '20';
$_FF_DEFAULT['use_glmenu']              = false;
$_FF_DEFAULT['grouptags'] = array(
    'Root'              => 'siteadmin_badge.png',
    'Logged-in Users'   => 'forum_user.png',
    'Group A'           => 'badge1.png',
    'Group B'           => 'badge2.png'
);
$_FF_DEFAULT['maxattachments']          = 2;      // Maximum number of attachments allowed in a single post
$_FF_DEFAULT['uploadpath']              = $_CONF['path_html'] . 'forum/media';
$_FF_DEFAULT['downloadURL']             = $_CONF['site_url'] . '/forum/media';
$_FF_DEFAULT['fileperms']               = '0755';  // Needs to be a string for the upload class use.
$_FF_DEFAULT['max_uploadimage_width']   = '2100';
$_FF_DEFAULT['max_uploadimage_height']  = '1600';
$_FF_DEFAULT['max_uploadfile_size']     = '6553600';     // 6.400 MB
$_FF_DEFAULT['inlineimage_width']       = '300';
$_FF_DEFAULT['inlineimage_height']      = '300';
$_FF_DEFAULT['bbcode_signature']        = true;

$_FF_DEFAULT['allowablefiletypes']    = array(
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
        'application/vnd.ms-word.document.macroEnabled.12'                               =>         '.docm',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'        =>         '.docx',
        'application/vnd.ms-word.template.macroEnabled.12'                               =>         '.dotm',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template'        =>         '.dotx',
        'application/vnd.ms-powerpoint.template.macroEnabled.12'                         =>         '.potm',
        'application/vnd.openxmlformats-officedocument.presentationml.template'          =>         '.potx',
        'application/vnd.ms-powerpoint.addin.macroEnabled.12'                            =>         '.ppam',
        'application/vnd.ms-powerpoint.slideshow.macroEnabled.12'                        =>         '.ppsm',
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow'         =>         '.ppsx',
        'application/vnd.ms-powerpoint.presentation.macroEnabled.12'                     =>         '.pptm',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'      =>         '.pptx',
        'application/vnd.ms-excel.addin.macroEnabled.12'                                 =>         '.xlam',
        'application/vnd.ms-excel.sheet.binary.macroEnabled.12'                          =>         '.xlsb',
        'application/vnd.ms-excel.sheet.macroEnabled.12'                                 =>         '.xlsm',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'              =>         '.xlsx',
        'application/vnd.ms-excel.template.macroEnabled.12'                              =>         '.xltm',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template'           =>         '.xltx',
        'application/octet-stream'          => '.zip,.vsd,.fla,.psd,.xls,.doc,.ppt,.pdf,.swf,.mpp,.txt,.dwg,.docx,.ppsx,.pptx,.xlsx,.xltx'
        );

$_FF_DEFAULT['inlineimageypes']    = array(
        'image/bmp'                         => '.bmp,',
        'image/gif'                         => '.gif',
        'image/pjpeg'                       => '.jpg,.jpeg',
        'image/jpeg'                        => '.jpg,.jpeg',
        'image/png'                         => '.png',
        'image/x-png'                       => '.png'
);
$_FF_DEFAULT['enable_fm_integration'] = false;
$_FF_DEFAULT['allow_memberlist']      = false;
$_FF_DEFAULT['allowed_html'] = 'p,b,i,strong,em,br,pre,code,img[src|alt|style|title],ol,ul,li,u';

$_FF_DEFAULT['geshi_line_numbers']     = false;
$_FF_DEFAULT['geshi_overall_style']    = 'font-size: 12px; color: #000066; border: 1px solid #d0d0d0; background-color: #fafafa;';
$_FF_DEFAULT['geshi_line_style']       = 'font: normal normal 95% \'Courier New\', Courier, monospace; color: #003030;font-weight: 700; color: #006060; background: #fcfcfc;';
$_FF_DEFAULT['geshi_code_style']       = 'color: #000020;';
$_FF_DEFAULT['geshi_header_style']     = 'font-family: Verdana, Arial, sans-serif; color: #fff; font-size: 90%; font-weight: 700; background-color: #3299D6; border-bottom: 1px solid #d0d0d0; padding: 2px;';


/**
* the Forum plugin's config array
*/
global $_FF_CONF, $_DB_table_prefix;
$_FF_CONF = array();

$_TABLES['ff_settings']     = $_DB_table_prefix . 'forum_settings';

if ( isset($_TABLES['ff_settings']) ) {
    $result = DB_query("SELECT * FROM {$_TABLES['ff_settings']}",1);
    $numRows = DB_numRows($result);
    if ( $numRows > 0 ) {
        $A = DB_fetchArray($result);

        if ( isset($A['registrationrequired']) )
            $_FF_CONF['registration_required']  = $A['registrationrequired'];
        if ( isset($A['registerpost']))
            $_FF_CONF['registered_to_post']     = $A['registerpost'];
        if ( isset($A['allowhtml']) )
            $_FF_CONF['allow_html']             = $A['allowhtml'];
        if ( isset($A['allowed_html']) )
            $_FF_CONF['allowed_html']             = $A['allowed_html'];
        if ( isset($A['post_htmlmode']) )
            $_FF_CONF['post_htmlmode']          = $A['post_htmlmode'];
        if ( isset($A['glfilter']) )
            $_FF_CONF['use_glfilter']           = $A['glfilter'];
        if ( isset($A['use_geshi_formatting']) )
            $_FF_CONF['use_geshi']              = $A['use_geshi_formatting'];
        if ( isset($A['censor']) )
            $_FF_CONF['use_censor']             = $A['censor'];
        if ( isset($A['showmood']) )
            $_FF_CONF['show_moods']             = $A['showmood'];
        if ( isset($A['allowsmilies']) )
            $_FF_CONF['allow_smilies']          = $A['allowsmilies'];
        if ( isset($A['allow_notify']) )
            $_FF_CONF['allow_notification']     = $A['allow_notify'];

        if ( isset($A['allow_userdatefmt'] ) )
            $_FF_CONF['allow_user_dateformat']  = $A['allow_userdatefmt'];
        if ( isset($A['showiframe']) )
            $_FF_CONF['show_topicreview']       = $A['showiframe'];
        if ( isset($A['autorefresh']) )
            $_FF_CONF['use_autorefresh']        = $A['autorefresh'];
        if ( isset($A['refresh_delay'] ) )
            $_FF_CONF['autorefresh_delay']      = $A['refresh_delay'];
        if ( isset($A['viewtopicnumchars']) )
            $_FF_CONF['show_subject_length']    = $A['viewtopicnumchars'];
        if ( isset($A['topicsperpage']) )
            $_FF_CONF['show_topics_perpage']    = $A['topicsperpage'];
        if ( isset($A['postsperpage']) )
            $_FF_CONF['show_posts_perpage']     = $A['postsperpage'];
        if ( isset($A['messagesperpage']) )
            $_FF_CONF['show_messages_perpage']  = $A['messagesperpage'];
        if ( isset($A['searchesperpage']) )
            $_FF_CONF['show_searches_perpage']  = $A['searchesperpage'];
        if ( isset($A['popular']) )
            $_FF_CONF['views_tobe_popular']     = $A['popular'];
        if ( isset($A['html_newline']) )
            $_FF_CONF['convert_break']          = $A['html_newline'];
        if ( isset($A['min_comment_len']) )
            $_FF_CONF['min_comment_length']     = $A['min_comment_len'];
        if ( isset($A['min_name_len']) )
            $_FF_CONF['min_username_length']    = $A['min_name_len'];
        if ( isset($A['min_subject_len']) )
            $_FF_CONF['min_subject_length']     = $A['min_subject_len'];
        if ( isset($A['speedlimit']) )
            $_FF_CONF['post_speedlimit']        = $A['speedlimit'];
        if ( isset($A['use_smilieplugin']) )
            $_FF_CONF['use_smilies_plugin']     = $A['use_smiliesplugin'];
        if ( isset($A['use_pmplugin']) )
            $_FF_CONF['use_pm_plugin']          = $A['use_pmplugin'];
        if ( isset($A['use_spamxfilter']) )
            $_FF_CONF['use_spamx_filter']       = $A['use_spamxfilter'];
        if ( isset($A['cb_enable']) )
            $_FF_CONF['show_centerblock']       = $A['cb_enable'];
        if ( isset($A['cb_homepage']) )
            $_FF_CONF['centerblock_homepage']   = $A['cb_homepage'];
        if ( isset($A['cb_where']) )
            $_FF_CONF['centerblock_where']      = $A['cb_where'];
        if ( isset($A['cb_subjectsize']) )
            $_FF_CONF['cb_subject_size']        = $A['cb_subjectsize'];
        if ( isset($A['cb_numposts']) )
            $_FF_CONF['centerblock_numposts']   = $A['cb_numposts'];
        if ( isset($A['sb_subjectsize']) )
            $_FF_CONF['sb_subject_size']        = $A['sb_subjectsize'];
        if ( isset($A['sb_latestposts']) )
            $_FF_CONF['sb_latestpostonly']      = $A['sb_latestposts'];
        if ( isset($A['sb_numposts']) )
            $_FF_CONF['sideblock_numposts']     = $A['sb_numposts'];
        if ( isset($A['edit_timewindow']) )
            $_FF_CONF['allowed_editwindow']     = $A['edit_timewindow'];
        if ( isset($A['level1']) )
            $_FF_CONF['level1']                 = $A['level1'];
        if ( isset($A['level2']) )
            $_FF_CONF['level2']                 = $A['level2'];
        if ( isset($A['level3']) )
            $_FF_CONF['level3']                 = $A['level3'];
        if ( isset($A['level4']) )
            $_FF_CONF['level4']                 = $A['level4'];
        if ( isset($A['level5']) )
            $_FF_CONF['level5']                 = $A['level5'];
        if ( isset($A['level1name']) )
            $_FF_CONF['level1name']             = $A['level1name'];
        if ( isset($A['level2name']) )
            $_FF_CONF['level2name']             = $A['level2name'];
        if ( isset($A['level3name']) )
            $_FF_CONF['level3name']             = $A['level3name'];
        if ( isset($A['level4name']) )
            $_FF_CONF['level4name']             = $A['level4name'];
        if ( isset($A['level5name']) )
            $_FF_CONF['level5name']             = $A['level5name'];
    }
}

/**
* Initialize FileMgmt plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist. Initial values will be taken from $_FM_CONF if available (e.g. from
* an old config.php), uses $_FM_DEFAULT otherwise.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_forum()
{
    global $_FF_CONF, $_FF_DEFAULT;

    if (is_array($_FF_CONF) && (count($_FF_CONF) > 1)) {
        $_FF_DEFAULT = array_merge($_FF_DEFAULT, $_FF_CONF);
    }
    $c = config::get_instance();
    if (!$c->group_exists('forum')) {

        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, 'forum');
        $c->add('ff_public', NULL, 'fieldset', 0, 0, NULL, 0, true, 'forum');

        $c->add('registration_required', $_FF_DEFAULT['registration_required'], 'select',
                0, 0, 0, 10, true, 'forum');
        $c->add('registered_to_post', $_FF_DEFAULT['registered_to_post'], 'select',
                0, 0, 0, 20, true, 'forum');
        $c->add('enable_user_rating_system', $_FF_DEFAULT['enable_rating_system'], 'select',
                0, 0, 0, 25, true, 'forum');
        $c->add('allow_memberlist', $_FF_DEFAULT['allow_memberlist'], 'select',
                0, 0, 0, 30, true, 'forum');
        $c->add('allow_notification', $_FF_DEFAULT['allow_notification'], 'select',
                0, 0, 0, 35, true, 'forum');
        $c->add('bbcode_signature', $_FF_DEFAULT['bbcode_signature'], 'select',
                0, 0, 0, 40, true, 'forum');
        $c->add('show_topicreview', $_FF_DEFAULT['show_topicreview'], 'select',
                0, 0, 0, 45, true, 'forum');
        $c->add('showtopic_review_order', $_FF_DEFAULT['showtopic_review_order'], 'select',
                0, 0, 5, 50, true, 'forum');
        $c->add('allow_user_dateformat', $_FF_DEFAULT['allow_user_dateformat'], 'select',
                0, 0, 0, 55, true, 'forum');
        $c->add('use_pm_plugin', $_FF_DEFAULT['use_pm_plugin'], 'select',
                0, 0, 0, 60, true, 'forum');
        $c->add('use_autorefresh', $_FF_DEFAULT['use_autorefresh'], 'select',
                0, 0, 0, 70, true, 'forum');
        $c->add('autorefresh_delay', $_FF_DEFAULT['autorefresh_delay'], 'text',
                0, 0, 0, 80, true, 'forum');
        $c->add('show_topics_perpage', $_FF_DEFAULT['show_topics_perpage'], 'text',
                0, 0, 0, 90, true, 'forum');
        $c->add('show_posts_perpage', $_FF_DEFAULT['show_posts_perpage'], 'text',
                0, 0, 0, 100, true, 'forum');
        $c->add('show_messages_perpage', $_FF_DEFAULT['show_messages_perpage'], 'text',
                0, 0, 0, 100, true, 'forum');
        $c->add('show_searches_perpage', $_FF_DEFAULT['show_searches_perpage'], 'text',
                0, 0, 0, 110, true, 'forum');
        $c->add('showblocks', $_FF_DEFAULT['showblocks'], 'select',
                0, 0, 3, 120, true, 'forum');
        $c->add('usermenu', $_FF_DEFAULT['usermenu'], 'select',
                0, 0, 4, 130, true, 'forum');
        $c->add('silent_edit_default', $_FF_DEFAULT['silent_edit_default'], 'select',
                0, 0, 0, 160, true, 'forum');
        $c->add('avatar_width', $_FF_DEFAULT['avatar_width'], 'text',
                0, 0, 0, 170, true, 'forum');
        $c->add('allow_img_bbcode', $_FF_DEFAULT['allow_img_bbcode'], 'select',
                0, 0, 0, 180, true, 'forum');
        $c->add('show_moderators', $_FF_DEFAULT['show_moderators'], 'select',
                0, 0, 0, 190, true, 'forum');
        $c->add('contentinfo_numchars', $_FF_DEFAULT['contentinfo_numchars'], 'text',
                0, 0, 0, 220, true, 'forum');
        $c->add('linkinfo_width', $_FF_DEFAULT['linkinfo_width'], 'text',
                0, 0, 0, 230, true, 'forum');
        $c->add('quoteformat', $_FF_DEFAULT['quoteformat'], 'text',
                0, 0, 0, 240, true, 'forum');
        $c->add('show_last_post_count', $_FF_DEFAULT['show_last_post_count'], 'text',
                0, 0, 0, 260, true, 'forum');
        $c->add('grouptags',$_FF_DEFAULT['grouptags'],'*text',
                0,0,NULL,280,true,'forum');

        $c->add('ff_attachments_settings', NULL, 'fieldset', 0, 1, NULL, 0, true,'forum');

        $c->add('maxattachments', $_FF_DEFAULT['maxattachments'], 'text',
                0, 1, 0, 10, true, 'forum');
        $c->add('uploadpath', $_FF_DEFAULT['uploadpath'], 'text',
                0, 1, 0, 20, true, 'forum');
        $c->add('downloadURL', $_FF_DEFAULT['downloadURL'], 'text',
                0, 1, 0, 30, true, 'forum');
        $c->add('fileperms', $_FF_DEFAULT['fileperms'], 'text',
                0, 1, 0, 40, true, 'forum');
        $c->add('max_uploadimage_width', $_FF_DEFAULT['max_uploadimage_width'], 'text',
                0, 1, 0, 50, true, 'forum');
        $c->add('max_uploadimage_height', $_FF_DEFAULT['max_uploadimage_height'], 'text',
                0, 1, 0, 60, true, 'forum');

        $c->add('max_uploadfile_size', $_FF_DEFAULT['max_uploadfile_size'], 'text',
                0, 1, 0, 70, true, 'forum');
        $c->add('inlineimage_width', $_FF_DEFAULT['inlineimage_width'], 'text',
                0, 1, 0, 80, true, 'forum');
        $c->add('inlineimage_height', $_FF_DEFAULT['inlineimage_height'], 'text',
                0, 1, 0, 90, true, 'forum');
        $c->add('allowablefiletypes',$_FF_DEFAULT['allowablefiletypes'], '*text',
                0,1,NULL,100,true,'forum');
        $c->add('inlineimageypes',$_FF_DEFAULT['inlineimageypes'], '*text',
                0,1,NULL,110,true,'forum');
        $c->add('enable_fm_integration', $_FF_DEFAULT['enable_fm_integration'], 'select',
                0,1,0,120, true, 'forum');

        $c->add('ff_topic_post_settings', NULL, 'fieldset', 0, 2, NULL, 0, true,'forum');

        $c->add('show_subject_length', $_FF_DEFAULT['show_subject_length'], 'text',
                0, 2, 0, 10, true, 'forum');
        $c->add('min_username_length', $_FF_DEFAULT['min_username_length'], 'text',
                0, 2, 0, 20, true, 'forum');
        $c->add('min_subject_length', $_FF_DEFAULT['min_subject_length'], 'text',
                0, 2, 0, 30, true, 'forum');
        $c->add('min_comment_length', $_FF_DEFAULT['min_comment_length'], 'text',
                0, 2, 0, 40, true, 'forum');
        $c->add('views_tobe_popular', $_FF_DEFAULT['views_tobe_popular'], 'text',
                0, 2, 0, 50, true, 'forum');
        $c->add('post_speedlimit', $_FF_DEFAULT['post_speedlimit'], 'text',
                0, 2, 0, 60, true, 'forum');
        $c->add('allowed_editwindow', $_FF_DEFAULT['allowed_editwindow'], 'text',
                0, 2, 0, 70, true, 'forum');
        $c->add('allow_html', $_FF_DEFAULT['allow_html'], 'select',
                0, 2, 0, 80, true, 'forum');
        $c->add('allowed_html', $_FF_DEFAULT['allowed_html'], 'text',
                0, 2, 0, 82, true, 'forum');
        $c->add('use_wysiwyg_editor', false, 'select',
                0, 2, 0, 85, true, 'forum');
        $c->add('post_htmlmode', $_FF_DEFAULT['post_htmlmode'], 'select',
                0, 2, 0, 90, true, 'forum');
        $c->add('use_censor', $_FF_DEFAULT['use_censor'], 'select',
                0, 2, 0, 100, true, 'forum');
        $c->add('use_geshi', $_FF_DEFAULT['use_geshi'], 'select',
                0, 2, 0, 120, true, 'forum');
        $c->add('geshi_line_numbers', $_FF_DEFAULT['geshi_line_numbers'], 'select',
                0, 2, 0, 130, true, 'forum');
        $c->add('geshi_line_style', $_FF_DEFAULT['geshi_line_style'], 'text',
                0, 2, 0, 140, true, 'forum');
        $c->add('geshi_overall_style', $_FF_DEFAULT['geshi_overall_style'], 'text',
                0, 2, 0, 150, true, 'forum');
        $c->add('geshi_code_style', $_FF_DEFAULT['geshi_code_style'], 'text',
                0, 2, 0, 160, true, 'forum');
        $c->add('geshi_header_style', $_FF_DEFAULT['geshi_header_style'], 'text',
                0, 2, 0, 170, true, 'forum');
        $c->add('use_spamx_filter', $_FF_DEFAULT['use_spamx_filter'], 'select',
                0, 2, 0, 180, true, 'forum');
        $c->add('use_sfs', $_FF_DEFAULT['use_sfs'], 'select',
                0, 2, 0, 190, true, 'forum');
        $c->add('show_moods', $_FF_DEFAULT['show_moods'], 'select',
                0, 2, 0, 200, true, 'forum');
        $c->add('allow_smilies', $_FF_DEFAULT['allow_smilies'], 'select',
                0, 2, 0, 210, true, 'forum');
        $c->add('use_smilies_plugin', $_FF_DEFAULT['use_smilies_plugin'], 'select',
                0, 2, 0, 220, true, 'forum');
        $c->add('bbcode_disabled', 0, 'select', 0, 2, 6, 230, true, 'forum');
        $c->add('smilies_disabled', 0, 'select', 0, 2, 6, 240, true, 'forum');
        $c->add('urlparse_disabled', 0, 'select', 0, 2, 6, 250, true, 'forum');

        $c->add('ff_centerblock', NULL, 'fieldset', 0, 3, NULL, 0, true,
                'forum');

        $c->add('show_centerblock', $_FF_DEFAULT['show_centerblock'], 'select',
                0, 3, 0, 10, true, 'forum');
        $c->add('centerblock_homepage', $_FF_DEFAULT['centerblock_homepage'], 'select',
                0, 3, 0, 20, true, 'forum');
        $c->add('centerblock_numposts', $_FF_DEFAULT['centerblock_numposts'], 'text',
                0, 3, 0, 30, true, 'forum');
        $c->add('cb_subject_size', $_FF_DEFAULT['cb_subject_size'], 'text',
                0, 3, 0, 40, true, 'forum');
        $c->add('centerblock_where', $_FF_DEFAULT['centerblock_where'], 'select',
                0, 3, 2, 50, true, 'forum');

        $c->add('ff_latest_post_block', NULL, 'fieldset', 0, 4, NULL, 0, true,
                'forum');

        $c->add('sideblock_numposts', $_FF_DEFAULT['sideblock_numposts'], 'text',
                0, 4, 0, 10, true, 'forum');
        $c->add('sb_subject_size', $_FF_DEFAULT['sb_subject_size'], 'text',
                0, 4, 0, 20, true, 'forum');
        $c->add('sb_latestpostonly', $_FF_DEFAULT['sb_latestpostonly'], 'select',
                0, 4, 0, 20, true, 'forum');

        $c->add('ff_rank_settings', NULL, 'fieldset', 0, 5, NULL, 0, true,
                'forum');
        $c->add('level1', $_FF_DEFAULT['level1'], 'text',
                0, 5, 0, 10, true, 'forum');
        $c->add('level1name', $_FF_DEFAULT['level1name'], 'text',
                0, 5, 0, 20, true, 'forum');
        $c->add('level2', $_FF_DEFAULT['level2'], 'text',
                0, 5, 0, 30, true, 'forum');
        $c->add('level2name', $_FF_DEFAULT['level2name'], 'text',
                0, 5, 0, 40, true, 'forum');
        $c->add('level3', $_FF_DEFAULT['level3'], 'text',
                0, 5, 0, 50, true, 'forum');
        $c->add('level3name', $_FF_DEFAULT['level3name'], 'text',
                0, 5, 0, 60, true, 'forum');
        $c->add('level4', $_FF_DEFAULT['level4'], 'text',
                0, 5, 0, 70, true, 'forum');
        $c->add('level4name', $_FF_DEFAULT['level4name'], 'text',
                0, 5, 0, 80, true, 'forum');
        $c->add('level5', $_FF_DEFAULT['level5'], 'text',
                0, 5, 0, 90, true, 'forum');
        $c->add('level5name', $_FF_DEFAULT['level5name'], 'text',
                0, 5, 0, 100, true, 'forum');
    }

    return true;
}
?>