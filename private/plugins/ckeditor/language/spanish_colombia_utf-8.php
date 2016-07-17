<?php
// +--------------------------------------------------------------------------+
// | CKEditor Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | spanish_colombia_utf-8.php                                               |
// |                                                                          |
// | Spanish (Colombia) language file                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2014-2015 by the following authors:                        |
// | John J. Toro A.        john DOT toro AT newroute DOT net                 |
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

$LANG_CK00 = array (
    'menulabel'         => 'CKEditor',
    'plugin'            => 'ckeditor',
    'access_denied'     => 'Acceso Denegado',
    'access_denied_msg' => 'You do not have the proper security privilege to access to this page.  Your user name and IP have been recorded.',
    'admin'             => 'CKEditor Administración',
    'install_header'    => 'CKEditor Plugin Install/Uninstall',
    'installed'         => 'CKEditor is Installed',
    'uninstalled'       => 'CKEditor is Not Installed',
    'install_success'   => 'CKEditor Installation Successful.  <br /><br />Please review the system documentation and also visit the  <a href="%s">administration section</a> to insure your settings correctly match the hosting environment.',
    'install_failed'    => 'Installation Failed -- See your error log to find out why.',
    'uninstall_msg'     => 'Plugin Successfully Uninstalled',
    'install'           => 'Instalar',
    'uninstall'         => 'Desinstalar',
    'warning'           => 'Warning! Plugin is still Enabled',
    'enabled'           => 'Disable plugin before uninstalling.',
    'readme'            => 'CKEditor Plugin Installation',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Install Document</a>",
    'overview'          => 'CKEditor es un complemento nativo de glFusion que provee capacidades WYSIWYG al editor.',
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
    'title'                 => 'Configuración CKEditor'
);
$LANG_confignames['ckeditor'] = array(
    'enable_comment'        => 'Enable Comment',
    'enable_story'          => 'Enable Story',
    'enable_submitstory'    => 'Enable User Story Contribute',
    'enable_contact'        => 'Enable Contact',
    'enable_emailstory'     => 'Enable Email Story',
    'enable_sp'             => 'Enable StaticPages Support',
    'enable_block'          => 'Enalbe Block Editor',
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
    'sg_main'  => 'Principal',
);
$LANG_fs['ckeditor'] = array(
    'ck_public'                 => 'Configuración CKEditor',
    'ck_integration'            => 'Integración',
	'fs_filemanager_general'    => 'Filemanager General Settings',
	'fs_filemanager_upload'     => 'Filemanager Upload Settings',
	'fs_filemanager_images'     => 'Filemanager Image Settings',
	'fs_filemanager_videos'     => 'Filemanager Video Settings',
	'fs_filemanager_audios'     => 'Filemanager Audio Settings',
	'fs_filemanager_editor'     => 'Filemanager Embedded Editor',
);
// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['ckeditor'] = array(
    0 => array('Sí' => 1, 'No' => 0),
    1 => array('Sí' => TRUE, 'No' => FALSE),
	2 => array('grid' => 'grid', 'list' => 'list'),
	3 => array('default' => 'default', 'Nombre (asc)' => 'NAME_ASC', 'Nombre (desc)' => 'NAME_DESC', 'Tipo (asc)' => 'TYPE_ASC', 'Tipo (desc)' => 'TYPE_DESC', 'Modified (asc)' => 'MODIFIED_ASC', 'Modified (desc)' => 'MODIFIED_DESC'),
);

$PLG_ckeditor_MESSAGE1 = 'CKEditor plugin upgrade: Update completed successfully.';
$PLG_ckeditor_MESSAGE2 = 'CKEditor plugin upgrade failed - check error.log';
$PLG_ckeditor_MESSAGE3 = 'CKEditor Plugin Successfully Installed';
?>
