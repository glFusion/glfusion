<?php
/**
* glFusion CMS
*
* Forum Plugin Configuration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$forumConfigData = array(
    array(
        'name' => 'sg_main',
        'default_value' => NULL,
        'type' => 'subgroup',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'ff_public',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'registration_required',
        'default_value' => '0',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'registered_to_post',
        'default_value' => '0',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'enable_user_rating_system',
        'default_value' => false,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 30,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'enable_likes',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 40,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'enable_likes_profile',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 50,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'allow_memberlist',
        'default_value' => false,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 60,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'allow_notification',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 70,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'bbcode_signature',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 80,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'show_topicreview',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 90,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'showtopic_review_order',
        'default_value' => 'DESC',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 5,
        'sort' => 100,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'allow_user_dateformat',
        'default_value' => '0',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 110,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'use_pm_plugin',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 120,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'use_autorefresh',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 130,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'autorefresh_delay',
        'default_value' => '2',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 140,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'show_topics_perpage',
        'default_value' => '15',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 150,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'show_posts_perpage',
        'default_value' => '15',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 160,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'show_messages_perpage',
        'default_value' => '20',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 170,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'show_searches_perpage',
        'default_value' => '20',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 180,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'showblocks',
        'default_value' => 'leftblocks',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 190,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'usermenu',
        'default_value' => 'navbar',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 4,
        'sort' => 200,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'silent_edit_default',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 210,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'avatar_width',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 220,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'allow_img_bbcode',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 230,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'show_moderators',
        'default_value' => false,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 240,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'contentinfo_numchars',
        'default_value' => 256,
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 250,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'linkinfo_width',
        'default_value' => 40,
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 260,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'quoteformat',
        'default_value' => '[QUOTE][u]Quote by: %s[/u][p]%s[/p][/QUOTE]',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 270,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'show_last_post_count',
        'default_value' => '20',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 280,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'ff_attachments_settings',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'maxattachments',
        'default_value' => 5,
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'uploadpath',
        'default_value' => $_CONF['path_html'].'forum/media',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'downloadURL',
        'default_value' => $_CONF['site_url'].'/forum/media',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 30,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'fileperms',
        'default_value' => '0755',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 40,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'max_uploadimage_width',
        'default_value' => '2100',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 50,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'max_uploadimage_height',
        'default_value' => '1600',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 60,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'max_uploadimage_size',
        'default_value' => NULL,
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 70,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'inlineimage_width',
        'default_value' => '300',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 80,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'inlineimage_height',
        'default_value' => '300',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 90,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'allowablefiletypes',
        'default_value' => array ( 'application/x-gzip-compressed' => '.tgz', 'application/x-zip-compressed' => '.zip', 'application/zip' => '.zip', 'application/x-tar' => '.tar', 'application/x-gtar' => '.gtar', 'application/x-gzip' => '.gz', 'text/plain' => '.txt', 'text/html' => '.html,.htm', 'image/bmp' => '.bmp,.ico', 'image/gif' => '.gif', 'image/pjpeg' => '.jpg,.jpeg', 'image/jpeg' => '.jpg,.jpeg', 'image/png' => '.png', 'image/x-png' => '.png', 'audio/mpeg' => '.mp3', 'audio/wav' => '.wav', 'application/pdf' => '.pdf', 'application/x-shockwave-flash' => '.swf', 'application/msword' => '.doc', 'application/vnd.ms-excel' => '.xls', 'application/vnd.ms-powerpoint' => '.ppt', 'application/vnd.ms-project' => '.mpp', 'application/vnd.visio' => '.vsd', 'application/x-pangaeacadsolutions' => '.dwg', 'application/x-zip-compresseed' => '.zip', 'application/vnd.ms-word.document.macroEnabled.12' => '.docm', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx', 'application/vnd.ms-word.template.macroEnabled.12' => '.dotm', 'application/vnd.openxmlformats-officedocument.wordprocessingml.template' => '.dotx', 'application/vnd.ms-powerpoint.template.macroEnabled.12' => '.potm', 'application/vnd.openxmlformats-officedocument.presentationml.template' => '.potx', 'application/vnd.ms-powerpoint.addin.macroEnabled.12' => '.ppam', 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12' => '.ppsm', 'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => '.ppsx', 'application/vnd.ms-powerpoint.presentation.macroEnabled.12' => '.pptm', 'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx', 'application/vnd.ms-excel.addin.macroEnabled.12' => '.xlam', 'application/vnd.ms-excel.sheet.binary.macroEnabled.12' => '.xlsb', 'application/vnd.ms-excel.sheet.macroEnabled.12' => '.xlsm', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx', 'application/vnd.ms-excel.template.macroEnabled.12' => '.xltm', 'application/vnd.openxmlformats-officedocument.spreadsheetml.template' => '.xltx', 'application/octet-stream' => '.zip,.vsd,.fla,.psd,.xls,.doc,.ppt,.pdf,.swf,.mpp,.txt,.dwg,.docx,.ppsx,.pptx,.xlsx,.xltx' ),
        'type' => '*text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => NULL,
        'sort' => 100,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'inlineimageypes',
        'default_value' => array ( 'image/bmp' => '.bmp,', 'image/gif' => '.gif', 'image/pjpeg' => '.jpg,.jpeg', 'image/jpeg' => '.jpg,.jpeg', 'image/png' => '.png', 'image/x-png' => '.png' ),
        'type' => '*text',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => NULL,
        'sort' => 110,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'enable_fm_integration',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 120,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'ff_topic_post_settings',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'show_subject_length',
        'default_value' => '70',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'min_username_length',
        'default_value' => '2',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'min_subject_length',
        'default_value' => '2',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 30,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'min_comment_length',
        'default_value' => '5',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 40,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'views_tobe_popular',
        'default_value' => '20',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 50,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'post_speedlimit',
        'default_value' => '60',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 60,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'allowed_editwindow',
        'default_value' => '300',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 70,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'allow_html',
        'default_value' => '0',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 80,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'allowed_html',
        'default_value' => 'p,b,i,strong,em,br,pre,img[src],ol,ul,li,u',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 90,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'use_wysiwyg_editor',
        'default_value' => false,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 100,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'post_htmlmode',
        'default_value' => '0',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 110,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'use_censor',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 120,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'use_geshi',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 130,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'geshi_line_numbers',
        'default_value' => false,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 140,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'geshi_line_style',
        'default_value' => 'font: normal normal 95% \'Courier New\', Courier, monospace; color: #003030;font-weight: 700; color: #006060; background: #fcfcfc;',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 150,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'geshi_overall_style',
        'default_value' => 'font-size: 12px; color: #000066; border: 1px solid #d0d0d0; background-color: #fafafa;',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 160,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'geshi_code_style',
        'default_value' => 'color: #000020;',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 170,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'geshi_header_style',
        'default_value' => 'font-family: Verdana, Arial, sans-serif; color: #fff; font-size: 90%; font-weight: 700; background-color: #3299D6; border-bottom: 1px solid #d0d0d0; padding: 2px;',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 180,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'use_spamx_filter',
        'default_value' => '0',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 190,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'use_sfs',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 200,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'show_moods',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 210,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'allow_smilies',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 220,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'use_smilies_plugin',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 0,
        'sort' => 230,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'bbcode_disabled',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 6,
        'sort' => 240,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'smilies_disabled',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 6,
        'sort' => 250,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'urlparse_disabled',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 6,
        'sort' => 260,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'ff_centerblock',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 3,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'show_centerblock',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 3,
        'selection_array' => 0,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'centerblock_homepage',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 3,
        'selection_array' => 0,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'centerblock_numposts',
        'default_value' => '10',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 3,
        'selection_array' => 0,
        'sort' => 30,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'cb_subject_size',
        'default_value' => '40',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 3,
        'selection_array' => 0,
        'sort' => 40,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'centerblock_where',
        'default_value' => '2',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 3,
        'selection_array' => 2,
        'sort' => 50,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'ff_latest_post_block',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 4,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'sideblock_numposts',
        'default_value' => '5',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 4,
        'selection_array' => 0,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'sb_subject_size',
        'default_value' => '20',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 4,
        'selection_array' => 0,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'forum'
    ),

    array(
        'name' => 'sb_latestpostonly',
        'default_value' => '0',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 4,
        'selection_array' => 0,
        'sort' => 30,
        'set' => TRUE,
        'group' => 'forum'
    ),
);
?>
