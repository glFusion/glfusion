<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | config-install.php                                                       |
// |                                                                          |
// | Initial configuration setup.                                             |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2007-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Aaron Blankstein  - kantai AT gmail DOT com                     |
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
    die ('This file can not be used on its own!');
}

function install_config($site_url)
{
    global $_CONF, $_TABLES;

    if (preg_match("@^https://@",$site_url)) {
        $cookiesecure = 1;
    } else {
        $cookiesecure = 0;
    }

    $c = config::get_instance();

    // Subgroup: Site
    $c->add('sg_site', NULL, 'subgroup', 0, 0, NULL, 0, TRUE);

    $c->add('fs_site', NULL, 'fieldset', 0, 0, NULL, 0, TRUE);
    $c->add('site_url','','text',0,0,NULL,10,TRUE);
    $c->add('site_admin_url','','text',0,0,NULL,20,TRUE);
    $c->add('site_name','','text',0,0,NULL,30,TRUE);
    $c->add('site_slogan','','text',0,0,NULL,40,TRUE);
    $site_disabled_msg = urldecode($site_url) . '/sitedown.html';
    $c->add('site_disabled_msg','','text',0,0,NULL,50,TRUE);
    $c->add('maintenance_mode',0,'select',0,0,0,60,TRUE);
    $c->add('copyrightyear','2016','text',0,0,NULL,70,FALSE);
    $c->add('url_rewrite',FALSE,'select',0,0,1,80,TRUE);
    $c->add('fb_appid','','text',0,0,NULL,90,TRUE);

    $c->add('fs_mail', NULL, 'fieldset', 0, 1, NULL, 0, TRUE);
    $c->add('site_mail','','text',0,1,NULL,10,TRUE);
    $c->add('noreply_mail','','text',0,1,NULL,20,TRUE);
    $c->add('mail_backend','mail','select',0,1,20,30,TRUE);
    $c->add('mail_sendmail_path','','text',0,1,NULL,40,TRUE);
    $c->add('mail_sendmail_args','','text',0,1,NULL,50,TRUE);
    $c->add('mail_smtp_host','','text',0,1,NULL,60,TRUE);
    $c->add('mail_smtp_port','25','text',0,1,NULL,70,TRUE);
    $c->add('mail_smtp_auth',FALSE,'select',0,1,0,80,TRUE);
    $c->add('mail_smtp_username','','text',0,1,NULL,90,TRUE);
    $c->add('mail_smtp_password','','passwd',0,1,NULL,100,TRUE);
    $c->add('mail_smtp_secure','none','select',0,1,21,110,TRUE);
    $c->add('use_from_site_mail',FALSE,'select',0,1,0,120,TRUE);

    $c->add('fs_syndication', NULL, 'fieldset', 0, 2, NULL, 0, TRUE);
    $c->add('backend',1,'select',0,2,0,10,TRUE);
    $c->add('rdf_file','','text',0,2,NULL,20,TRUE);
    $c->add('rdf_limit',10,'text',0,2,NULL,30,TRUE);
    $c->add('rdf_storytext',1,'text',0,2,NULL,40,TRUE);
    $c->add('rdf_language','en-gb','text',0,2,NULL,50,TRUE);
    $c->add('syndication_max_headlines',0,'text',0,2,NULL,60,TRUE);

    $c->add('fs_paths', NULL, 'fieldset', 0, 3, NULL, 0, TRUE);
    $c->add('path_html','','text',0,3,NULL,10,TRUE);
    $c->add('path_log','','text',0,3,NULL,20,TRUE);
    $c->add('path_language','','text',0,3,NULL,30,TRUE);
    $c->add('backup_path','','text',0,3,NULL,40,TRUE);
    $c->add('path_data','','text',0,3,NULL,50,TRUE);
    $c->add('path_images','','text',0,3,NULL,60,TRUE);

    $c->add('fs_pear', NULL, 'fieldset', 0, 4, NULL, 0, TRUE);
    $c->add('have_pear','','select',0,4,1,10,TRUE);
    $c->add('path_pear','','text',0,4,NULL,20,TRUE);

    $c->add('fs_search', NULL, 'fieldset', 0, 5, NULL, 0, TRUE);
    $c->add('search_style','google','select',0,5,18,10,TRUE);
    $c->add('search_limits','10,25,50,100','text',0,5,NULL,20,TRUE);
    $c->add('num_search_results',10,'text',0,5,NULL,30,TRUE);
    $c->add('search_show_num',TRUE,'select',0,5,1,40,TRUE);
    $c->add('search_show_type',TRUE,'select',0,5,1,50,TRUE);
    $c->add('search_show_user',TRUE,'select',0,5,1,60,TRUE);
    $c->add('search_show_hits',TRUE,'select',0,5,1,70,TRUE);
    $c->add('search_no_data','<i>Not available...</i>','text',0,5,NULL,80,TRUE);
    $c->add('search_separator',' &gt; ','text',0,5,NULL,90,TRUE);
    $c->add('search_def_keytype','phrase','select',0,5,19,100,TRUE);

    $c->add('fs_update', NULL, 'fieldset', 0, 6, NULL, 0, TRUE);
    $c->add('update_check_interval','86400','select',0,6,29,10,TRUE);
    $c->add('send_site_data',TRUE,'select',0,6,1,20,TRUE);

    // Subgroup: Stories and Trackback
    $c->add('sg_stories', NULL, 'subgroup', 1, 0, NULL, 0, TRUE);

    $c->add('fs_story', NULL, 'fieldset', 1, 1, NULL, 0, TRUE);
    $c->add('maximagesperarticle',5,'text',1,1,NULL,10,TRUE);
    $c->add('limitnews',10,'text',1,1,NULL,20,TRUE);
    $c->add('infinite_scroll',1,'select',1,1,0,30,TRUE);
    $c->add('minnews',1,'text',1,1,NULL,40,TRUE);
    $c->add('contributedbyline',1,'select',1,1,0,50,TRUE);
    $c->add('hidestorydate',0,'select',1,1,0,60,TRUE);
    $c->add('hideviewscount',0,'select',1,1,0,70,TRUE);
    $c->add('hideemailicon',0,'select',1,1,0,80,TRUE);
    $c->add('hideprintericon',0,'select',1,1,0,90,TRUE);
    $c->add('digg_enabled',0,'select',1,1,0,100,TRUE);
    $c->add('rating_enabled',1,'select',1,1,24,110,TRUE);
    $c->add('allow_page_breaks',1,'select',1,1,0,120,TRUE);
    $c->add('page_break_comments','last','select',1,1,7,130,TRUE);
    $c->add('article_image_align','right','select',1,1,8,140,TRUE);
    $c->add('show_topic_icon',1,'select',1,1,0,150,TRUE);
    $c->add('draft_flag',0,'select',1,1,0,160,TRUE);
    $c->add('frontpage',1,'select',1,1,0,170,TRUE);
    $c->add('hide_no_news_msg',0,'select',1,1,0,180,TRUE);
    $c->add('hide_main_page_navigation',0,'select',1,1,0,190,TRUE);
    $c->add('onlyrootfeatures',0,'select',1,1,0,200,TRUE);
    $c->add('aftersave_story','list','select',1,1,9,210,TRUE);

    $c->add('fs_trackback', NULL, 'fieldset', 1, 2, NULL, 0, TRUE);
    $c->add('trackback_enabled',TRUE,'select',1,2,1,10,TRUE);
    $c->add('trackback_code',0,'select',1,2,3,20,TRUE);
    $c->add('trackbackspeedlimit',300,'text',1,2,NULL,30,TRUE);
    $c->add('check_trackback_link',2,'select',1,2,4,40,TRUE);
    $c->add('multiple_trackbacks',0,'select',1,2,2,50,TRUE);

    $c->add('fs_pingback', NULL, 'fieldset', 1, 3, NULL, 0, TRUE);
    $c->add('pingback_enabled',TRUE,'select',1,3,1,10,TRUE);
    $c->add('pingback_excerpt',TRUE,'select',1,3,1,20,TRUE);
    $c->add('pingback_self',0,'select',1,3,13,30,TRUE);
    $c->add('ping_enabled',TRUE,'select',1,3,1,40,TRUE);

    // Subgroup: Theme
    $c->add('sg_theme', NULL, 'subgroup', 2, 0, NULL, 0, TRUE);

    $c->add('fs_theme', NULL, 'fieldset', 2, 1, NULL, 0, TRUE);
    $c->add('theme','cms','select',2,1,NULL,10,TRUE);
    $c->add('path_themes','','text',2,1,NULL,20,TRUE);

    $c->add('fs_theme_advanced', NULL, 'fieldset', 2, 2, NULL, 0, TRUE);
    $c->add('show_right_blocks',TRUE,'select',2,2,1,10,TRUE);
    $c->add('showfirstasfeatured',0,'select',2,2,0,20,TRUE);
    $c->add('compress_css',TRUE,'select',2,2,0,30,TRUE);
    $c->add('template_comments',FALSE,'select',2,2,0,40,TRUE);

    $c->add('fs_caching', NULL, 'fieldset', 2, 3, NULL, 0, TRUE);
    $c->add('cache_templates',TRUE,'select',2,3,0,10,TRUE);

    // Subgroup: Blocks
    $c->add('sg_blocks', NULL, 'subgroup', 3, 0, NULL, 0, TRUE);

    $c->add('fs_admin_block', NULL, 'fieldset', 3, 1, NULL, 0, TRUE);
    $c->add('sort_admin',TRUE,'select',3,1,1,20,TRUE);
    $c->add('link_documentation',1,'select',3,1,0,20,TRUE);
    $c->add('link_versionchecker',1,'select',3,1,0,30,TRUE);
    $c->add('hide_adminmenu',TRUE,'select',3,1,1,40,TRUE);

    $c->add('fs_topics_block', NULL, 'fieldset', 3, 2, NULL, 0, TRUE);
    $c->add('sortmethod','sortnum','select',3,2,15,10,TRUE);
    $c->add('showstorycount',1,'select',3,2,0,20,TRUE);
    $c->add('showsubmissioncount',1,'select',3,2,0,30,TRUE);
    $c->add('hide_home_link',0,'select',3,2,0,40,TRUE);

    $c->add('fs_whosonline_block', NULL, 'fieldset', 3, 3, NULL, 0, TRUE);
    $c->add('whosonline_threshold',300,'text',3,3,NULL,10,TRUE);
    $c->add('whosonline_anonymous',0,'select',3,3,0,20,TRUE);
    $c->add('whosonline_photo',FALSE,'select',3,3,0,30,TRUE);

    $c->add('fs_whatsnew_block', NULL, 'fieldset', 3, 4, NULL, 0, TRUE);
    $c->add('newstoriesinterval',86400,'text',3,4,NULL,10,TRUE);
    $c->add('newcommentsinterval',172800,'text',3,4,NULL,20,TRUE);
    $c->add('newtrackbackinterval',172800,'text',3,4,NULL,30,TRUE);
    $c->add('hidenewstories',0,'select',3,4,0,40,TRUE);
    $c->add('hidenewcomments',0,'select',3,4,0,50,TRUE);
    $c->add('hidenewtrackbacks',0,'select',3,4,0,60,TRUE);
    $c->add('hidenewplugins',0,'select',3,4,0,70,TRUE);
    $c->add('hideemptyblock',0,'select',3,4,0,80,TRUE);
    $c->add('title_trim_length',200,'text',3,4,NULL,90,TRUE);
    $c->add('whatsnew_cache_time',3600,'text',3,4,NULL,100,TRUE);

    // Subgroup: Users and Submissions
    $c->add('sg_users', NULL, 'subgroup', 4, 0, NULL, 0, TRUE);

    $c->add('fs_users', NULL, 'fieldset', 4, 1, NULL, 0, TRUE);
    $c->add('disable_new_user_registration',FALSE,'select',4,1,0,10,TRUE);
    $c->add('allow_user_themes',0,'select',4,1,0,20,TRUE);
    $c->add('allow_user_language',0,'select',4,1,0,30,TRUE);
    $c->add('allow_user_photo',1,'select',4,1,0,40,TRUE);
    $c->add('allow_username_change',0,'select',4,1,0,50,TRUE);
    $c->add('allow_account_delete',0,'select',4,1,0,60,TRUE);
    $c->add('hide_author_exclusion',0,'select',4,1,0,70,TRUE);
    $c->add('show_fullname',0,'select',4,1,0,80,TRUE);
    $c->add('hide_exclude_content',1,'select',4,1,0,90,TRUE);
    $c->add('show_servicename',TRUE,'select',4,1,1,100,TRUE);
    $c->add('custom_registration',FALSE,'select',4,1,1,110,TRUE);
    $c->add('user_login_method',array('standard' => true, '3rdparty' => false, 'oauth' => false),'@select',4,1,1,120,TRUE);
    $c->add('facebook_login',0,'select',4,1,1,130,TRUE);
    $c->add('facebook_consumer_key','not configured yet','text',4,1,NULL,140,TRUE);
    $c->add('facebook_consumer_secret','not configured yet','text',4,1,NULL,150,TRUE);
    $c->add('linkedin_login',0,'select',4,1,1,160,TRUE);
    $c->add('linkedin_consumer_key','not configured yet','text',4,1,NULL,170,TRUE);
    $c->add('linkedin_consumer_secret','not configured yet','text',4,1,NULL,180,TRUE);
    $c->add('twitter_login',0,'select',4,1,1,190,TRUE);
    $c->add('twitter_consumer_key','not configured yet','text',4,1,NULL,200,TRUE);
    $c->add('twitter_consumer_secret','not configured yet','text',4,1,NULL,210,TRUE);
    $c->add('google_login',0,'select',4,1,1,220,TRUE);
    $c->add('google_consumer_key','not configured yet','text',4,1,NULL,230,TRUE);
    $c->add('google_consumer_secret','not configured yet','text',4,1,NULL,240,TRUE);
    $c->add('microsoft_login',0,'select',4,1,1,250,TRUE);
    $c->add('microsoft_consumer_key','not configured yet','text',4,1,NULL,260,TRUE);
    $c->add('microsoft_consumer_secret','not configured yet','text',4,1,NULL,270,TRUE);
    $c->add('github_login',0,'select',4,1,1,280,TRUE);
    $c->add('github_consumer_key','not configured yet','text',4,1,NULL,290,TRUE);
    $c->add('github_consumer_secret','not configured yet','text',4,1,NULL,300,TRUE);
    $c->add('aftersave_user','item','select',4,1,9,310,TRUE);

    $c->add('fs_spamx', NULL, 'fieldset', 4, 2, NULL, 0, TRUE);
    $c->add('spamx',128,'text',4,2,NULL,10,TRUE);

    $c->add('fs_login', NULL, 'fieldset', 4, 3, NULL, 0, TRUE);
    $c->add('lastlogin',TRUE,'select',4,3,1,10,TRUE);
    $c->add('loginrequired',0,'select',4,3,0,20,TRUE);
    $c->add('submitloginrequired',0,'select',4,3,0,30,TRUE);
    $c->add('commentsloginrequired',0,'select',4,3,0,40,TRUE);
    $c->add('statsloginrequired',0,'select',4,3,0,50,TRUE);
    $c->add('searchloginrequired',0,'select',4,3,0,60,TRUE);
    $c->add('profileloginrequired',0,'select',4,3,0,70,TRUE);
    $c->add('emailuserloginrequired',0,'select',4,3,0,80,TRUE);
    $c->add('emailstoryloginrequired',0,'select',4,3,0,90,TRUE);
    $c->add('directoryloginrequired',0,'select',4,3,0,100,TRUE);
    $c->add('passwordspeedlimit',300,'text',4,3,NULL,110,TRUE);
    $c->add('login_attempts',3,'text',4,3,NULL,120,TRUE);
    $c->add('login_speedlimit',300,'text',4,3,NULL,130,TRUE);

    $c->add('fs_user_submission', NULL, 'fieldset', 4, 4, NULL, 0, TRUE);
    $c->add('usersubmission',0,'select',4,4,0,10,TRUE);
    $c->add('registration_type',0,'select',4,4,27,20,TRUE);
    $c->add('allow_domains','','text',4,4,NULL,30,TRUE);
    $c->add('disallow_domains','','text',4,4,NULL,40,TRUE);
    $c->add('user_reg_fullname',1,'select',4,4,25,50,TRUE);
    $c->add('min_username_length','4','text',4,4,NULL,60,TRUE);

    $c->add('fs_submission', NULL, 'fieldset', 4, 5, NULL, 0, TRUE);
    $c->add('storysubmission',1,'select',4,5,0,10,TRUE);
    $c->add('story_submit_by_perm_only',0,'select',4,5,0,20,TRUE);
    $c->add('listdraftstories',0,'select',4,5,0,30,TRUE);
    $c->add('postmode','html','select',4,5,5,40,TRUE);
    $c->add('mailuser_postmode','html','select',4,5,5,50,TRUE);
    $c->add('speedlimit',45,'text',4,5,NULL,60,TRUE);
    $c->add('skip_preview',0,'select',4,5,0,70,TRUE);

    $c->add('fs_comments', NULL, 'fieldset', 4, 6, NULL, 0, TRUE);
    $c->add('comment_engine','internal','select',4,6,30,10,TRUE);
    $c->add('comment_disqus_shortname','','text',4,6,NULL,20,TRUE);
    $c->add('comment_fb_appid','','text',4,6,NULL,30,TRUE);
    $c->add('commentspeedlimit',45,'text',4,6,NULL,40,TRUE);
    $c->add('comment_limit',100,'text',4,6,NULL,50,TRUE);
    $c->add('comment_mode','nested','select',4,6,11,60,TRUE);
    $c->add('comment_code',0,'select',4,6,17,70,TRUE);
    $c->add('comment_edit',0,'select',4,6,0,80,TRUE);
    $c->add('comment_edittime',1800,'text',4,6,NULL,90,TRUE);
    $c->add('comment_postmode','plaintext','select',4,6,5,100,TRUE);
    $c->add('article_comment_close_enabled',0,'select',4,6,0,110,TRUE);
    $c->add('article_comment_close_days',30,'text',4,6,NULL,120,TRUE);
    $c->add('comment_close_rec_stories',0,'text',4,6,NULL,130,TRUE);

    $c->add('fs_rating',NULL, 'fieldset', 4,7,NULL,0,TRUE);
    $c->add('rating_speedlimit',15,'text',4,7,NULL,10,TRUE);

    // Subgroup: Images
    $c->add('sg_images', NULL, 'subgroup', 5, 0, NULL, 0, TRUE);

    $c->add('fs_imagelib', NULL, 'fieldset', 5, 1, NULL, 0, TRUE);
    $c->add('image_lib','gdlib','select',5,1,10,10,TRUE);
    $c->add('path_to_mogrify','','text',5,1,NULL,20,FALSE);
    $c->add('path_to_netpbm','','text',5,1,NULL,30,FALSE);
    $c->add('jhead_enabled',0,'select',5,1,0,40,TRUE);
    $c->add('path_to_jhead','','text',5,1,NULL,50,TRUE);
    $c->add('jpegtrans_enabled',0,'select',5,1,0,60,TRUE);
    $c->add('path_to_jpegtrans','','text',5,1,NULL,70,TRUE);

    $c->add('fs_upload', NULL, 'fieldset', 5, 2, NULL, 0, TRUE);
    $c->add('keep_unscaled_image',0,'select',5,2,0,10,TRUE);
    $c->add('allow_user_scaling',1,'select',5,2,0,20,TRUE);
    $c->add('jpg_orig_quality','85','text',5,2,NULL,30,TRUE);
    $c->add('debug_image_upload',FALSE,'select',5,2,1,40,TRUE);

    $c->add('fs_articleimg', NULL, 'fieldset', 5, 3, NULL, 0, TRUE);
    $c->add('max_image_width',160,'text',5,3,NULL,10,TRUE);
    $c->add('max_image_height',160,'text',5,3,NULL,20,TRUE);
    $c->add('max_image_size',1048576,'text',5,3,NULL,30,TRUE);

    $c->add('fs_topicicon', NULL, 'fieldset', 5, 4, NULL, 0, TRUE);
    $c->add('max_topicicon_width',48,'text',5,4,NULL,10,TRUE);
    $c->add('max_topicicon_height',48,'text',5,4,NULL,20,TRUE);
    $c->add('max_topicicon_size',65536,'text',5,4,NULL,30,TRUE);

    $c->add('fs_userphoto', NULL, 'fieldset', 5, 5, NULL, 0, TRUE);
    $c->add('max_photo_width',300,'text',5,5,NULL,10,TRUE);
    $c->add('max_photo_height',300,'text',5,5,NULL,20,TRUE);
    $c->add('max_photo_size',8388608,'text',5,5,NULL,30,TRUE);
    $c->add('force_photo_width',75,'text',5,5,NULL,40,FALSE);
    $def_photo = urldecode($site_url) . '/images/userphotos/default.jpg';
    $c->add('default_photo',$def_photo,'text',5,5,NULL,50,TRUE);

    $c->add('fs_gravatar', NULL, 'fieldset', 5, 6, NULL, 0, TRUE);
    $c->add('use_gravatar',FALSE,'select',5,6,1,10,TRUE);
    $c->add('gravatar_rating','R','text',5,6,NULL,20,FALSE);

    $c->add('fs_logo', NULL, 'fieldset', 5, 7, NULL, 0, TRUE);
    $c->add('max_logo_height',150,'text',5,7,NULL,10,TRUE);
    $c->add('max_logo_width',1024,'text',5,7,NULL,20,TRUE);

    // Subgroup: Languages and Locale
    $c->add('sg_locale', NULL, 'subgroup', 6, 0, NULL, 0, TRUE);

    $c->add('fs_language', NULL, 'fieldset', 6, 1, NULL, 0, TRUE);
    $c->add('language','english','select',6,1,NULL,10,TRUE);

    $c->add('fs_locale', NULL, 'fieldset', 6, 2, NULL, 0, TRUE);
    $c->add('locale','en_US','text',6,2,NULL,10,TRUE);
    $c->add('date','l, F d Y @ h:i A T','text',6,2,NULL,20,TRUE);
    $c->add('daytime','m/d h:iA','text',6,2,NULL,30,TRUE);
    $c->add('shortdate','m/d/y','text',6,2,NULL,40,TRUE);
    $c->add('dateonly','d-M','text',6,2,NULL,50,TRUE);
    $c->add('timeonly','H:iA','text',6,2,NULL,60,TRUE);
    $c->add('week_start','Sun','select',6,2,14,70,TRUE);
    $c->add('hour_mode',12,'select',6,2,6,80,TRUE);
    $c->add('thousand_separator',",",'text',6,2,NULL,90,TRUE);
    $c->add('decimal_separator',".",'text',6,2,NULL,100,TRUE);
    $c->add('decimal_count',"2",'text',6,2,NULL,110,TRUE);
    $c->add('timezone','America/Chicago','select',6,2,NULL,120,TRUE);

    $c->add('fs_mulitlanguage', NULL, 'fieldset', 6, 3, NULL, 0, TRUE);
    $c->add('language_files',array('en'=>'english_utf-8', 'de'=>'german_formal_utf-8'),'*text',6,3,NULL,10,FALSE);
    $c->add('languages',array('en'=>'English', 'de'=>'Deutsch'),'*text',6,3,NULL,20,FALSE);

    $c->add('sg_misc', NULL, 'subgroup', 7, 0, NULL, 0, TRUE);

    $c->add('fs_cookies', NULL, 'fieldset', 7, 1, NULL, 0, TRUE);
    $c->add('cookie_session','glf_session','text',7,1,NULL,10,TRUE);
    $c->add('cookie_name','glfusion','text',7,1,NULL,20,TRUE);
    $c->add('session_ip_check',1,'select',7,1,26,30,TRUE);
    $c->add('cookie_password','glf_password','text',7,1,NULL,40,TRUE);
    $c->add('cookie_theme','glf_theme','text',7,1,NULL,50,TRUE);
    $c->add('cookie_language','glf_language','text',7,1,NULL,60,TRUE);
    $c->add('cookie_tzid','glf_timezone','text',7,1,NULL,70,TRUE);
    $c->add('default_perm_cookie_timeout',28800,'text',7,1,NULL,80,TRUE);
    $c->add('session_cookie_timeout',7200,'text',7,1,NULL,90,TRUE);
    $c->add('cookie_path','/','text',7,1,NULL,100,TRUE);
    $c->add('cookiedomain','','text',7,1,NULL,110,TRUE);
    $c->add('cookiesecure',$cookiesecure,'select',7,1,1,120,TRUE);

    $c->add('fs_misc', NULL, 'fieldset', 7, 2, NULL, 0, TRUE);
    $c->add('notification',array(),'%text',7,2,NULL,10,TRUE);
    $c->add('cron_schedule_interval',86400,'text',7,2,NULL,20,TRUE);
    $c->add('disable_autolinks',0,'select',7,2,0,30,TRUE);

    $c->add('fs_debug', NULL, 'fieldset', 7, 3, NULL, 0, TRUE);
    $c->add('rootdebug',FALSE,'select',7,3,1,10,TRUE);

    $c->add('fs_daily_digest', NULL, 'fieldset', 7, 4, NULL, 0, TRUE);
    $c->add('emailstories',0,'select',7,4,0,10,TRUE);
    $c->add('emailstorieslength',1,'text',7,4,NULL,20,TRUE);
    $c->add('emailstoriesperdefault',0,'select',7,4,0,30,TRUE);

    $c->add('fs_htmlfilter', NULL, 'fieldset', 7, 5, NULL, 0, TRUE);
    $c->add('allow_embed_object',TRUE,'select',7,5,1,10,TRUE);
    $c->add('skip_html_filter_for_root',0,'select',7,5,0,20,TRUE);
    $c->add('htmlfilter_default','p,b,a,i,strong,em,br','text',7,5,NULL,30,true);
    $c->add('htmlfilter_comment','p,b,a,i,strong,em,br,tt,hr,li,ol,ul,code,pre','text',7,5,NULL,35,TRUE);
    $c->add('htmlfilter_story','div[class],h1,h2,h3,pre,br,p[style],b[style],s,strong[style],i[style],em[style],u[style],strike,a[style|href|title|target],ol[style|class],ul[style|class],li[style|class],hr[style],blockquote[style],img[style|alt|title|width|height|src|align],table[style|width|bgcolor|align|cellspacing|cellpadding|border],tr[style],td[style],th[style],tbody,thead,caption,col,colgroup,span[style|class],sup,sub','text',7,5,NULL,40,TRUE);
    $c->add('htmlfilter_root','div[style|class],span[style|class],table,tr,td,th,img[src|width|height|class|style]','text',7,5,NULL,50,TRUE);

    $c->add('fs_censoring', NULL, 'fieldset', 7, 6, NULL, 0, TRUE);
    $c->add('censormode',1,'select',7,6,23,10,TRUE);
    $c->add('censorreplace','*censored*','text',7,6,NULL,20,TRUE);
    $c->add('censorlist', array('fuck','cunt','fucker','fucking','pussy','cock','c0ck',' cum ','twat','clit','bitch','fuk','fuking','motherfucker'),'%text',7,6,NULL,30,TRUE);

    $c->add('fs_iplookup', NULL, 'fieldset', 7, 7, NULL, 0, TRUE);
    $c->add('ip_lookup','/admin/plugins/nettools/whois.php?domain=*','text',7,7,NULL,10,FALSE);

    $c->add('fs_perm_story', NULL, 'fieldset', 7, 8, NULL, 0, TRUE);
    $c->add('default_permissions_story',array(3, 2, 2, 2),'@select',7,8,12,10,TRUE);

    $c->add('fs_perm_topic', NULL, 'fieldset', 7, 9, NULL, 0, TRUE);
    $c->add('default_permissions_topic',array(3, 2, 2, 2),'@select',7,9,12,10,TRUE);

    $c->add('fs_perm_block', NULL, 'fieldset', 7, 10, NULL, 0, TRUE);
    $c->add('default_permissions_block',array(3, 2, 2, 2),'@select',7,10,12,10,TRUE);

    $c->add('fs_webservices', NULL, 'fieldset', 7, 11, NULL, 0, TRUE);
    $c->add('disable_webservices',   1, 'select', 7, 11, 0, 10, TRUE);
    $c->add('restrict_webservices',  0, 'select', 7, 11, 0, 20, TRUE);
    $c->add('atom_max_stories',     10, 'text',   7, 11, 0, 30, TRUE);

    // hidden system configuration options

    $c->add('social_site_extra','', 'text',0,0,NULL,1,TRUE,'social_internal');
}
?>