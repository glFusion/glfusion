<?php
// +--------------------------------------------------------------------------+
// | CKEditor Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | french_canada.php                                                        |
// |                                                                          |
// | French Canadian language file                                            |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2014-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org						  |
// | Michael Brusletten     mbrusl AT spacequad DOT net		                  |
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
    'access_denied'     => 'Accès refusé',
    'access_denied_msg' => 'Vous n`avez pas le privilège de sécurité approprié pour accéder à cette page. Votre nom d`utilisateur et IP ont été enregistrées.',
    'admin'             => 'CKEditor Plugin Installer / Désinstaller',
    'install_header'    => 'CKEditor Plugin Installer / DÃ©sinstaller',
    'installed'         => 'CKEditor est installé',
    'uninstalled'       => 'CKEditor n`est pas installé',
    'install_success'   => 'CKEditor installation réussie. <br /> <br /> S`il vous plaît examiner la documentation du système et également visiter la section <a administration de href="%s"> </ a> pour assurer vos paramètres correspondent correctement l`environnement d`hébergement.',
    'install_failed'    => 'Échec de l`installation - Consultez votre journal des erreurs pour savoir pourquoi.',
    'uninstall_msg'     => 'Plugin succès Uninstalled',
    'install'           => 'Installer',
    'uninstall'         => 'Désinstallation',
    'warning'           => 'Attention! Plugin est toujours activé',
    'enabled'           => 'Désactiver le plugin avant de désinstaller',
    'readme'            => "CKEditor Plugin Installation",
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Installez Document</a>",
    'overview'          => 'CKEditor est un plugin de glFusion natif qui fournit des capacitÃ©s de l`Ã©diteur WYSIWYG.',
    'details'           => 'CKEditor est un plugin de glFusion natif qui fournit des capacités de l`éditeur WYSIWYG.',
    'preinstall_check'  => 'CKEditor a les exigences suivantes:',
    'glfusion_check'    => 'v1.3.0 de glFusion ou plus, la version indiquée est <b>%s</b>.',
    'php_check'         => "PHP v5.2.0 ou supérieure, version rapportée est <b>%s</b>.",
    'preinstall_confirm'=> "Pour plus de détails sur l'installation CKEditor, s'il vous plaît se référer à la <a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Manuel d`installation</a>.",
    'visual'            => 'Visuel',
    'html'              => 'HTML',
);

// Localization of the Admin Configuration UI
$LANG_configsections['ckeditor'] = array(
    'label'                 => 'CKEditor',
    'title'                 => 'Configuration CKEditor'
);
$LANG_confignames['ckeditor'] = array(
    'enable_comment'        => 'Activer Commentaire',
    'enable_story'          => 'Activer histoire',
    'enable_submitstory'    => 'Activer histoire soumission d`utilisateur',
    'enable_contact'        => 'Activer Contacter',
    'enable_emailstory'     => 'Activer Email histoire',
    'enable_sp'             => 'Activer le support StaticPages',
    'enable_block'          => 'Activer l`éditeur de blocs',
    'filemanager_fileroot'  => 'Chemin d`accès relatif (de public_html) aux fichiers',
    'filemanager_per_user_dir' => 'Utilisez par répertoires utilisateur',
    'filemanager_browse_only'       => 'Parcourir seul mode',
    'filemanager_default_view_mode' => 'Vue par défaut en mode',
    'filemanager_show_confirmation' => 'Afficher confirmation',
    'filemanager_search_box'        => 'Case Afficher la recherche',
    'filemanager_file_sorting'      => 'Tri du fichier',
    'filemanager_chars_only_latin'  => 'Autoriser uniquement les caractères latins',
    'filemanager_date_format'       => 'Date de format de l`heure',
    'filemanager_show_thumbs'       => 'Afficher les miniatures',
    'filemanager_generate_thumbnails' => 'Créer des vignettes',
    'filemanager_upload_restrictions' => 'Extensions de fichiers autorisés',
    'filemanager_upload_overwrite'  => 'Ecraser le fichier existant',
    'filemanager_upload_images_only' => 'Télécharger des images seulement',
    'filemanager_upload_file_size_limit' => 'Téléchargez limite de taille de fichier (MB)',
    'filemanager_unallowed_files'   => 'Fichiers non autorisées',
    'filemanager_unallowed_dirs'    => 'Répertoires non autorisées',
    'filemanager_unallowed_files_regexp' => 'Expression régulière pour les fichiers non autorisées',
    'filemanager_unallowed_dirs_regexp' => 'Expression Régulière verser les Fichiers non Permitted',
    'filemanager_images_ext'        => 'Les extensions de fichier de l`image',
    'filemanager_show_video_player' => 'Afficher lecteur de vidéo',
    'filemanager_videos_ext'        => 'Les extensions de fichier vidéo',
    'filemanager_videos_player_width' => 'Largeur de lecteur vidéo (px)',
    'filemanager_videos_player_height' => 'Hauteur de lecteur vidéo (px)',
    'filemanager_show_audio_player' => 'Montrez lecteur audio',
    'filemanager_audios_ext'        => 'Les extensions de fichier audio',
    'filemanager_edit_enabled'      => 'Éditeur Activé',
    'filemanager_edit_linenumbers'  => 'Numéros de ligne',
    'filemanager_edit_linewrapping' => 'Retour à la ligne',
    'filemanager_edit_codehighlight' => 'Code surbrillance',
    'filemanager_edit_editext' => 'Autorisés Modifier Extensions',
    'filemanager_fileperm'     => 'Permission for new files',
    'filemanager_dirperm'       => 'Permission for new directories',

);
$LANG_configsubgroups['ckeditor'] = array(
    'sg_main'               => 'Paramètres de configuration',
);
$LANG_fs['ckeditor'] = array(
    'ck_public'                 => 'Configuration CKEditor',
    'ck_integration'            => 'Intégration de CKEditor',
	'fs_filemanager_general'    => 'Paramètres Filemanager Générales',
	'fs_filemanager_upload'     => 'Filemanager Paramètres de Téléchargement',
	'fs_filemanager_images'     => 'Filemanager Paramètres d`Image',
	'fs_filemanager_videos'     => 'Filemanager Paramètres Vidéo',
	'fs_filemanager_audios'     => 'Paramètres Audio Filemanager',
	'fs_filemanager_editor'     => 'Éditeur Filemanager intégré',
);
// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['ckeditor'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE),
	2 => array('grid' => 'grid', 'list' => 'list'),
	3 => array('default' => 'default', 'Name (asc)' => 'NAME_ASC', 'Name (desc)' => 'NAME_DESC', 'Type (asc)' => 'TYPE_ASC', 'Type (desc)' => 'TYPE_DESC', 'Modified (asc)' => 'MODIFIED_ASC', 'Modified (desc)' => 'MODIFIED_DESC'),
);

$PLG_ckeditor_MESSAGE1 = 'Mise à jour du plugin CKEditor: Mise à jour effectuée avec succès.';
$PLG_ckeditor_MESSAGE2 = 'CKEditor mise à jour du plugin a échoué - chèque error.log';
$PLG_ckeditor_MESSAGE3 = 'CKEditor Plugin installé avec succès';
?>
