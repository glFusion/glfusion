<?php
/**
* glFusion CMS
*
* UTF-8 Spam-X Language File
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001 by the following authors:
*  Tony Bibbs       tony AT tonybibbs DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}


global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_STATIC = array(
    'newpage' => 'Nouvelle page',
    'adminhome' => 'Entrée - admin',
    'staticpages' => 'Pages statiques',
    'staticpageeditor' => 'Éditeur de pages statiques',
    'writtenby' => 'Écrit par',
    'date' => 'Dernière mise à jour',
    'title' => 'Titre',
    'content' => 'Contenu',
    'hits' => 'Clicks',
    'staticpagelist' => 'Liste des pages statiques',
    'url' => 'URL',
    'edit' => 'Édition',
    'lastupdated' => 'Dernière mise à jour',
    'pageformat' => 'Format des pages',
    'leftrightblocks' => 'Blocs à gauche et à droite',
    'blankpage' => 'Nouvelle page vierge',
    'noblocks' => 'Pas de blocs',
    'leftblocks' => 'Blocs à gauche',
    'rightblocks' => 'Right Blocks',
    'addtomenu' => 'Ajoutez au menu',
    'label' => 'Libellé',
    'nopages' => 'Il n\'y a pas de pages statiques enregistrées dans le système présentement',
    'save' => 'sauvegarde',
    'preview' => 'aperçu',
    'delete' => 'effacer',
    'cancel' => 'annuler',
    'access_denied' => 'Acces refusé',
    'access_denied_msg' => 'Vous essayez d\'accéder illégalement à l\'une des pages administratives relatives aux pages statiques. Veuillez noter que toutes les tentatives illégales en ce sens seront enregistrées',
    'all_html_allowed' => 'Toutes les balises HTML sont acceptées',
    'results' => 'Résultats des pages statiques',
    'author' => 'Auteur',
    'no_title_or_content' => 'Vous devez au minimum inscrire quelque chose dans les champs <b>titre</b> et <b>contenu</b>.',
    'no_such_page_anon' => 'Prière de vous enregistrer',
    'no_page_access_msg' => "Ce pourrait être parce que vous ne vous êtes pas enregistré, ou inscrit comme membre de {$_CONF['site_name']}. Veuillez <a href=\"{$_CONF['site_url']}/users.php?mode=new\"> vous inscrire comme membre</a> de {$_CONF['site_name']} pour recevoir toutes les permissions nécessaires",
    'php_msg' => 'PHP: ',
    'php_warn' => 'Avertissement: le code PHP de votre page sera évalué si vous utilisez cette fonction. Utilisez avec précaution !!',
    'exit_msg' => 'Type de sortie: ',
    'exit_info' => 'Activez pour obtenir le message d\'accès requis. Gardez non-sélectionné pour actionner les vérifications et les messages de sécurité normaux.',
    'deny_msg' => 'L\'accès à cette page est impossible. Soit la page à été déplacée, soit vous n\'avez pas les permissions nécessaires.',
    'stats_headline' => 'Top-10 des pages statiques les plus fréquentées',
    'stats_page_title' => 'Titre des pages',
    'stats_hits' => 'Clics',
    'stats_no_hits' => 'Il serait possible qu\'il n\'y ait aucune page statique sur ce site, ou alors personne ne les a encore consultées.',
    'id' => 'Identification',
    'duplicate_id' => 'L\'identification choisie pour cette page est déjà utilisée par une autre page. Veuillez en choisir une autre.',
    'instructions' => 'Pour modifier une page statique, cliquez sur son numéro ci-dessous. Pour voir une page statique, cliquez sur le titre de la page. Pour créer une page statique, cliquez sur Nouvelle page ci-dessous. Cliquez sur [C] pour créer une copie de page existante.',
    'centerblock' => 'Bloc du centre: ',
    'centerblock_msg' => 'Lorsque sélectionnée, cette page statique sera disposée comme le bloc central de la page d\'index.',
    'topic' => 'Sujet: ',
    'position' => 'Position: ',
    'all_topics' => 'Tout',
    'no_topic' => 'Page d\'accueil seulement',
    'position_top' => 'Haut de page',
    'position_feat' => 'Après les articles',
    'position_bottom' => 'Bas de page',
    'position_entire' => 'Page entière',
    'position_nonews' => 'Only if No Other News',
    'head_centerblock' => 'Bloc du centre',
    'centerblock_no' => 'Non',
    'centerblock_top' => 'Haut',
    'centerblock_feat' => 'Article principal',
    'centerblock_bottom' => 'Bas',
    'centerblock_entire' => 'Bloc entier',
    'centerblock_nonews' => 'If No News',
    'inblock_msg' => 'In a block: ',
    'inblock_info' => 'Wrap Static Page in a block.',
    'title_edit' => 'Edit page',
    'title_copy' => 'Make a copy of this page',
    'title_display' => 'Display page',
    'select_php_none' => 'do not execute PHP',
    'select_php_return' => 'execute PHP (return)',
    'select_php_free' => 'execute PHP',
    'php_not_activated' => "The use of PHP in static pages is not activated. Please see the <a href=\"{$_CONF['site_url']}/docs/staticpages.html#php\">documentation</a> for details.",
    'printable_format' => 'Printable Format',
    'copy' => 'Copy',
    'limit_results' => 'Limit Results',
    'search' => 'Search',
    'submit' => 'Submit',
    'delete_confirm' => 'Are you sure you want to delete this page?',
    'allnhp_topics' => 'All Topics (No Homepage)',
    'page_list' => 'Liste des pages statiques',
    'instructions_edit' => 'This screen allows you to create / edit a new static page. Pages can contain PHP code and HTML code.',
    'attributes' => 'Attributs de l\'album',
    'preview_help' => 'Select the <b>Preview</b> button to refresh the preview display',
    'page_saved' => 'Page has been successfully saved.',
    'page_deleted' => 'Page has been successfully deleted.',
    'searchable' => 'Search',
);

$LANG_SP_AUTOTAG = array(
    'desc_staticpage'           => 'Link: to a staticpage on this site; link_text defaults to staticpage title. usage: [staticpage:<i>page_id</i> {link_text}]',
    'desc_staticpage_content'   => 'HTML: renders the content of a staticpage.  usage: [staticpage_content:<i>page_id</i>]',
);

$PLG_staticpages_MESSAGE19 = '';
$PLG_staticpages_MESSAGE20 = '';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Plugin Mise à niveau non pris en charge.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Static Pages',
    'title' => 'Static Pages Configuration'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'Allow PHP?',
    'sort_by' => 'Sort Centerblocks by',
    'sort_menu_by' => 'Sort Menu Entries by',
    'delete_pages' => 'Delete Pages with Owner?',
    'in_block' => 'Wrap Pages in Block?',
    'show_hits' => 'Show Hits?',
    'show_date' => 'Show Date?',
    'filter_html' => 'Filter HTML?',
    'censor' => 'Censor Content?',
    'default_permissions' => 'Page Default Permissions',
    'aftersave' => 'After Saving Page',
    'atom_max_items' => 'Max. Pages in Webservices Feed',
    'comment_code' => 'Comment Default',
    'include_search' => 'Site Search Default',
    'status_flag' => 'Default Page Mode',
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Paramètres Principaux'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Static Pages Main Settings',
    'fs_permissions' => 'Autorisations par Défaut'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['staticpages'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    2 => array('date'=>'Date', 'id'=>'Page ID', 'title'=>'Titre'),
    3 => array('date'=>'Date', 'id'=>'Page ID', 'title'=>'Titre', 'label'=>'étiquette'),
    9 => array('item'=>'Forward to Page', 'list'=>'Display Admin List', 'plugin'=>'Display Public List', 'home'=>'Display Home', 'admin'=>'Display Admin'),
    12 => array(0=>'No access', 2=>'Lecture-Seule', 3=>'Read-Write'),
    13 => array(1=>'Enabled', 0=>'Inactif'),
    17 => array(0=>'Commentaires Activé', 1=>'Commentaires Handicapés'),
);

?>
