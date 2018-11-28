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
    'access_denied'     => 'Acceso Denegado',
    'access_denied_msg' => 'No tienes el privilegio de seguridad apropiado para acceder a esta pagina.  Su usuario y dirección IP han sido registradas.',
    'admin'             => 'CKEditor Administración',
    'install_header'    => 'CKEditor Plugin Install/Uninstall',
    'installed'         => 'CKEditor is Installed',
    'uninstalled'       => 'CKEditor is Not Installed',
    'install_success'   => 'CKEditor Installation Successful.  <br /><br />Please review the system documentation and also visit the  <a href="%s">administration section</a> to insure your settings correctly match the hosting environment.',
    'install_failed'    => 'Falló la Instalación -- Revisa el registro de errores para encontrar el porque.',
    'uninstall_msg'     => 'Extensión Desinstalada Exitosamente',
    'install'           => 'Instalar',
    'uninstall'         => 'Desinstalar',
    'warning'           => '¡Advertencia! La Extensión sigue habilitada',
    'enabled'           => 'Deshabilita la Extensión antes de desinstalar.',
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
    'enable_comment'        => 'Habilitar en Comentarios',
    'enable_story'          => 'Habilitar en Noticias',
    'enable_submitstory'    => 'Enable User Story Contribute',
    'enable_contact'        => 'Habilitar en el Contacto',
    'enable_emailstory'     => 'Habilitar en E-mail de Noticias',
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
    'sg_main'               => 'Principal'
);
$LANG_fs['ckeditor'] = array(
    'ck_public'                 => 'Configuración CKEditor',
    'ck_integration'            => 'Integración',
    'fs_filemanager_general'    => 'General',
    'fs_filemanager_upload'     => 'Upload Settings',
    'fs_filemanager_images'     => 'Image Settings',
    'fs_filemanager_videos'     => 'Video Settings',
    'fs_filemanager_audios'     => 'Audio Settings',
    'fs_filemanager_editor'     => 'Embedded Editor',
);
// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['ckeditor'] = array(
    0 => array(1=>'Si', 0=>'No'),
    1 => array(true=>'Si', false=>'No'),
    2 => array('grid'=>'grid', 'list' => 'listado'),
    3 => array('default' => 'por defecto', 'NAME_ASC'=>'Name (asc)', 'NAME_DESC'=>'Name (desc)', 'TYPE_ASC'=>'Type (asc)', 'TYPE_DESC'=>'Type (desc)', 'MODIFIED_ASC'=>'Modified (asc)', 'MODIFIED_DESC'=>'Modified (desc)'),
);

$PLG_ckeditor_MESSAGE1 = 'CKEditor plugin upgrade: Update completed successfully.';
$PLG_ckeditor_MESSAGE2 = 'CKEditor plugin upgrade failed - check error.log';
$PLG_ckeditor_MESSAGE3 = 'CKEditor Plugin Successfully Installed';
?>