<?php
/**
* glFusion CMS
*
* UTF-8 Language File for glFusion CKEditor Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

$LANG_CK00 = array (
    'menulabel'         => 'CKEditor',
    'plugin'            => 'ckeditor',
    'access_denied'     => 'Access Denied',
    'access_denied_msg' => 'You do not have the proper security privilege to access to this page.  Your user name and IP have been recorded.',
    'admin'             => 'CKEditor Administration',
    'install_header'    => 'CKEditor Plugin Install/Uninstall',
    'installed'         => 'CKEditor is Installed',
    'uninstalled'       => 'CKEditor is Not Installed',
    'install_success'   => 'CKEditor Installation Successful.  <br /><br />Please review the system documentation and also visit the  <a href="%s">administration section</a> to insure your settings correctly match the hosting environment.',
    'install_failed'    => 'Installation Failed -- See your error log to find out why.',
    'uninstall_msg'     => 'Plugin Successfully Uninstalled',
    'install'           => 'Install',
    'uninstall'         => 'UnInstall',
    'warning'           => 'Warning! Plugin is still Enabled',
    'enabled'           => 'Disable plugin before uninstalling.',
    'readme'            => 'CKEditor Plugin Installation',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Install Document</a>",
    'overview'          => 'CKEditor is a native glFusion plugin that provides WYSIWYG editor capabilities.',
    'details'           => 'The CKEditor plugin will provide wysiwyg editor features to your site.',
    'preinstall_check'  => 'CKEditor has the following requirements:',
    'glfusion_check'    => 'glFusion v1.3.0 or greater, version reported is <b>%s</b>.',
    'php_check'         => 'PHP v5.2.0 or greater, version reported is <b>%s</b>.',
    'preinstall_confirm'=> "For full details on installing CKEditor, please refer to the <a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Installation Manual</a>.",
    'visual'            => 'Visual',
    'html'              => 'HTML',
);

// Localization of the Admin Configuration UI
$LANG_configsections['ckeditor'] = array(
    'label'                 => 'CKEditor',
    'title'                 => 'CKEditor Configuration'
);
$LANG_confignames['ckeditor'] = array(
    'enable_comment'        => 'Enable Comment',
    'enable_story'          => 'Enable Story',
    'enable_submitstory'    => 'Enable User Story Contribute',
    'enable_contact'        => 'Enable Contact',
    'enable_emailstory'     => 'Enable Email Story',
    'enable_sp'             => 'Enable Pages Editor Support',
    'enable_block'          => 'Enable Block Editor',
    'filemanager_fileroot'  => 'Relative Path (from public_html) to Files',
    'filemanager_per_user_dir' => 'Use Per User Directories',
    'filemanager_browse_only'       => 'Browse only mode',
    'filemanager_default_view_mode' => 'Default view mode',
    'filemanager_show_confirmation' => 'Show confirmation',
    'filemanager_search_box'        => 'Show search box',
    'filemanager_file_sorting'      => 'File sorting',
    'filemanager_chars_only_latin'  => 'Allow only latin chars',
    'filemanager_date_format'       => 'Date time format',
    'filemanager_show_thumbs'       => 'Show thumbnails',
    'filemanager_generate_thumbnails' => 'Generate thumbnails',
    'filemanager_upload_restrictions' => 'Allowed file extensions',
    'filemanager_upload_overwrite'  => 'Overwrite existing file',
    'filemanager_upload_images_only' => 'Upload images only',
    'filemanager_upload_file_size_limit' => 'Upload file size limit (MB)',
    'filemanager_unallowed_files'   => 'Unallowed files',
    'filemanager_unallowed_dirs'    => 'Unallowed directories',
    'filemanager_unallowed_files_regexp' => 'Regular expression for unallowed files',
    'filemanager_unallowed_dirs_regexp' => 'Regular expression for unallowed directories',
    'filemanager_images_ext'        => 'Image file extensions',
    'filemanager_show_video_player' => 'Show video player',
    'filemanager_videos_ext'        => 'Video file extensions',
    'filemanager_videos_player_width' => 'Video player width (px)',
    'filemanager_videos_player_height' => 'Video player height (px)',
    'filemanager_show_audio_player' => 'Show audio player',
    'filemanager_audios_ext'        => 'Audio file extensions',
    'filemanager_edit_enabled'      => 'Editor Enabled',
    'filemanager_edit_linenumbers'  => 'Line Numbers',
    'filemanager_edit_linewrapping' => 'Line Wrapping',
    'filemanager_edit_codehighlight' => 'Code Highlighting',
    'filemanager_edit_editext' => 'Allowed Edit Extensions',
    'filemanager_fileperm'     => 'Permission for new files',
    'filemanager_dirperm'       => 'Permission for new directories',

);
$LANG_configsubgroups['ckeditor'] = array(
    'sg_main'               => 'Configuration Settings'
);
$LANG_fs['ckeditor'] = array(
    'ck_public'                 => 'CKEditor Configuration',
    'ck_integration'            => 'CKEditor Integration',
    'fs_filemanager_general'    => 'Filemanager General Settings',
    'fs_filemanager_upload'     => 'Filemanager Upload Settings',
    'fs_filemanager_images'     => 'Filemanager Image Settings',
    'fs_filemanager_videos'     => 'Filemanager Video Settings',
    'fs_filemanager_audios'     => 'Filemanager Audio Settings',
    'fs_filemanager_editor'     => 'Filemanager Embedded Editor',
);
// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['ckeditor'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    2 => array('grid'=>'grid', 'list' => 'list'),
    3 => array('default' => 'default', 'NAME_ASC'=>'Name (asc)', 'NAME_DESC'=>'Name (desc)', 'TYPE_ASC'=>'Type (asc)', 'TYPE_DESC'=>'Type (desc)', 'MODIFIED_ASC'=>'Modified (asc)', 'MODIFIED_DESC'=>'Modified (desc)'),
);

$PLG_ckeditor_MESSAGE1 = 'CKEditor plugin upgrade: Update completed successfully.';
$PLG_ckeditor_MESSAGE2 = 'CKEditor plugin upgrade failed - check error.log';
$PLG_ckeditor_MESSAGE3 = 'CKEditor Plugin Successfully Installed';
?>