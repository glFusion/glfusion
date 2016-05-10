<?php
// +--------------------------------------------------------------------------+
// | CKEditor Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | install_defaults.php                                                     |
// |                                                                          |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2014-2016 by the following authors:                        |
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
 * CKEditor default settings
 *
 * Initial Installation Defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 *
 */

global $_CK_DEFAULT;
$_CK_DEFAULT = array();

$_CK_DEFAULT['enable_comment'] = 1;
$_CK_DEFAULT['enable_contact'] = 1;
$_CK_DEFAULT['enable_emailstory'] = 1;
$_CK_DEFAULT['enable_registration'] = 1;
$_CK_DEFAULT['enable_story'] = 1;
$_CK_DEFAULT['enable_submitstory'] = 1;
$_CK_DEFAULT['enable_sp'] = 1;
$_CK_DEFAULT['enable_block'] = 1;

/**
* the ckeditor plugin's config array
*/
global $_CK_CONF;
$_CK_CONF = array();

/**
* Initialize CKEditor plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_ckeditor()
{
    global $_CK_CONF, $_CK_DEFAULT;

    if (is_array($_CK_CONF) && (count($_CK_CONF) > 1)) {
        $_CK_DEFAULT = array_merge($_CK_DEFAULT, $_CK_CONF);
    }
    $c = config::get_instance();
    if (!$c->group_exists('ckeditor')) {

        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, 'ckeditor');
        $c->add('ck_public', NULL, 'fieldset', 0, 0, NULL, 0, true, 'ckeditor');

        $c->add('ck_integration', NULL, 'fieldset', 0, 1, NULL, 0, true,'ckeditor');

        $c->add('enable_comment', $_CK_DEFAULT['enable_comment'],'select',0, 1, 0, 30, true, 'ckeditor');
        $c->add('enable_story', $_CK_DEFAULT['enable_story'],'select',0, 1, 0, 40, true, 'ckeditor');
        $c->add('enable_submitstory', $_CK_DEFAULT['enable_submitstory'],'select',0, 1, 0, 50, true, 'ckeditor');
        $c->add('enable_contact', $_CK_DEFAULT['enable_contact'],'select',0, 1, 0, 60, true, 'ckeditor');
        $c->add('enable_emailstory', $_CK_DEFAULT['enable_emailstory'],'select',0, 1, 0, 70, true, 'ckeditor');
        $c->add('enable_sp', $_CK_DEFAULT['enable_sp'],'select',0, 1, 0, 120, true, 'ckeditor');
        $c->add('enable_block', $_CK_DEFAULT['enable_block'],'select',0, 1, 0, 130, true, 'ckeditor');
        $c->add('fs_filemanager_general', NULL, 'fieldset', 0, 2, NULL, 0, true, 'ckeditor');
//        $c->add('filemanager_fileroot', '/images/library/userfiles/', 'text', 0, 2, NULL, 20, true, 'ckeditor');
        $c->add('filemanager_per_user_dir', true, 'select', 0, 2, 1, 30, true, 'ckeditor');
        $c->add('filemanager_browse_only', false, 'select', 0, 2, 1, 40, true, 'ckeditor');
        $c->add('filemanager_default_view_mode', 'grid', 'select', 0, 2, 2, 50, true, 'ckeditor');
//        $c->add('filemanager_show_confirmation', true, 'select', 0, 2, 1, 60, true, 'ckeditor');
//        $c->add('filemanager_search_box', true, 'select', 0, 2, 1, 70, true, 'ckeditor');
//        $c->add('filemanager_file_sorting', 'default', 'select', 0, 2, 3, 80, true, 'ckeditor');
//        $c->add('filemanager_chars_only_latin', true, 'select', 0, 2, 1, 90, true, 'ckeditor');
        $c->add('filemanager_date_format', 'Y-m-d H:i:s', 'text', 0, 2, NULL, 100, true, 'ckeditor');
//        $c->add('filemanager_show_thumbs', true, 'select', 0, 2, 1, 120, true, 'ckeditor');
//        $c->add('filemanager_generate_thumbnails', true, 'select', 0, 2, 1, 130, true, 'ckeditor');
//        $c->add('fs_filemanager_upload', NULL, 'fieldset', 0, 3, NULL, 0, true, 'ckeditor');
//        $c->add('filemanager_upload_restrictions', 'jpg,jpeg,gif,png,svg,txt,pdf,odp,ods,odt,rtf,doc,docx,xls,xlsx,ppt,pptx,ogv,mp4,webm,ogg,mp3,wav', 'text', 0, 3, NULL, 10, true, 'ckeditor');
//        $c->add('filemanager_upload_overwrite', false, 'select', 0, 3, 1, 20, true, 'ckeditor');
//        $c->add('filemanager_upload_images_only', false, 'select', 0, 3, 1, 30, true, 'ckeditor');
//        $c->add('filemanager_upload_file_size_limit', 16, 'text', 0, 3, NULL, 40, true, 'ckeditor');
//        $c->add('filemanager_unallowed_files', '.htaccess,web.config', 'text', 0, 3, NULL, 50, true, 'ckeditor');
//        $c->add('filemanager_unallowed_dirs', '_thumbs,.CDN_ACCESS_LOGS,cloudservers', 'text', 0, 3, NULL, 60, true, 'ckeditor');
//        $c->add('filemanager_unallowed_files_regexp', '/^\\./uis', 'text', 0, 3, NULL, 70, true, 'ckeditor');
//        $c->add('filemanager_unallowed_dirs_regexp', '/^\\./uis', 'text', 0, 3, NULL, 80, true, 'ckeditor');
//        $c->add('fs_filemanager_images', NULL, 'fieldset', 0, 4, NULL, 0, true, 'ckeditor');
//        $c->add('filemanager_images_ext', 'jpg,jpeg,gif,png,svg', 'text', 0, 4, NULL, 10, true, 'ckeditor');
//        $c->add('fs_filemanager_videos', NULL, 'fieldset', 0, 5, NULL, 0, true, 'ckeditor');
//        $c->add('filemanager_show_video_player', true, 'select', 0, 5, 1, 10, true, 'ckeditor');
//        $c->add('filemanager_videos_ext', 'ogv,mp4,webm', 'text', 0, 5, NULL, 20, true, 'ckeditor');
//        $c->add('filemanager_videos_player_width', 400, 'text', 0, 5, NULL, 30, true, 'ckeditor');
//        $c->add('filemanager_videos_player_height', 222, 'text', 0, 5, NULL, 40, true, 'ckeditor');
//        $c->add('fs_filemanager_audios', NULL, 'fieldset', 0, 6, NULL, 0, true, 'ckeditor');
//        $c->add('filemanager_show_audio_player', true, 'select', 0, 6, 1, 10, true, 'ckeditor');
//        $c->add('filemanager_audios_ext', 'ogg,mp3,wav', 'text', 0, 6, NULL, 20, true, 'ckeditor');
//        $c->add('fs_filemanager_editor', NULL, 'fieldset', 0, 7, NULL, 0, true, 'ckeditor');
//        $c->add('filemanager_edit_enabled', false, 'select', 0, 7, 1, 10, true, 'ckeditor');
//        $c->add('filemanager_edit_linenumbers', true, 'select', 0, 7, 1, 20, true, 'ckeditor');
//        $c->add('filemanager_edit_linewrapping', true, 'select', 0, 7, 1, 30, true, 'ckeditor');
//        $c->add('filemanager_edit_codehighlight', false, 'select', 0, 7, 1, 40, true, 'ckeditor');
//        $c->add('filemanager_edit_editext', 'txt,csv', 'text', 0, 7, NULL, 50, true, 'ckeditor');

    }

    return true;
}
?>