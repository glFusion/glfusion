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
*  Based on prior work Copyright (C) 2004-2008 by the following authors:
*  Tom Willett          tomw AT pigstye DOT net
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $LANG32;

$LANG_SX00 = array (
	'comdel'	    => ' commentaires supprimés.',
	'deletespam'    => 'Supprimer Commentaire',
	'masshead'	    => '<hr><h1 align="center">Suppression de commentaires en lot</h1>',
	'masstb'        => '<hr><h1 align="center">Mass Delete Trackback Spam</h1>',
	'note1'		    => '<p>Note: La suppression en lot a pour but de faciliter la tâche lorsque vous êtes victime d\'un',
	'note2'		    => ' commentaire indésirable et que Spam-X ne le reconnaît pas.  <ul><li>Trouvez d\'abord le(s) lien(s) ou autre ',
	'note3'		    => 'identificateurs de ce commentaire indésirable et ajoutez-le à votre liste noire personnelle.</li><li>Ensuite ',
	'note4'		    => 'revenez ici et exécutez Spam-X pour vérifier les derniers commentaires.</li></ul><p>Les commentaires ',
	'note5'		    => 'sont vérifiés à partir des plus récents -- vérifier plus de commentaires ',
	'note6'		    => 'nécessite plus de temps pour la vérification</p>',
	'numtocheck'    => 'Nombre de commentaires à vérifier',
    'RDF'           => 'URL du fichier RDF: ',
    'URL'           => 'URL vers la Liste Spam-X: ',
    'access_denied' => 'Accès refusé',
    'access_denied_msg' => 'Seulement les utilisateurs Root ont accès à cette page.  Votre code d\'usager et votre adresse IP ont été enregistrés.',
    'acmod'         => 'Modules Action de Spam-X',
    'actmod'        => 'Modules actifs',
    'add1'          => 'Ajouté ',
    'add2'          => ' entrées de la',
    'add3'          => "' liste noire de.",
    'addcen'        => 'Ajouter Liste de Censure',
    'addentry'      => 'Ajouter l\'entrée',
    'admin'         => 'Administration Plugin',
    'adminc'        => 'Commandes Administration:',
    'all'           => 'All',
    'allow_url_fopen' => '<p>Désolé, la configuration de votre serveur web ne permet pas la lecture de fichiers distants (<code>allow_url_fopen</code> est désactivé). Veuillez télécharger la liste noire de l\'adresse suivante et placez-la dans le répertoire "data" de glFusion, <tt>%s</tt>, avant un nouvel essai:',
    'auto_refresh_off' => 'Auto Refresh Off',
    'auto_refresh_on' => 'Auto Refresh On',
    'availb'        => 'Listes Noires Disponibles',
    'avmod'         => 'Modules disponibles',
    'blacklist'     => 'Blacklist',
    'blacklist_prompt' => 'Enter words to trigger spam',
    'blacklist_success_delete' => 'Selected items successfully deleted',
    'blacklist_success_save' => 'Spam-X Filter Saved Successfully',
    'blocked'       => 'Blocked',
    'cancel'        => 'Cancel',
    'cancel'        => 'Cancel',
    'clearlog'      => 'Vider fichier ',
    'clicki'        => 'Cliquez pour Importer la Liste Noire',
    'clickv'        => 'Cliquez pour voir la Liste Noire',
    'comment'       => 'Commentaire',
    'coninst'       => '<hr>Cliquez sur un module actif pour le supprimer, cliquez sur un module disponible pour l\'ajouter.<br>Les modules sont exécutés dans l\'ordre affiché.',
    'conmod'        => 'Configurer utilisation du module Spam-X',
    'content'       => 'Teneur',
    'content_type'  => 'Content Type',
    'delete'        => 'Delete',
    'delete_confirm' => 'Are you sure you want to delete this item?',
    'delete_confirm_2' => 'Are you REALLY SURE you want to delete this item',
    'documentation' => 'Documentation du Plugin Spam-X',
    'e1'            => 'Pour supprimer une entrée cliquez dessus.',
    'e2'            => 'Pour ajouter une entrée, entrez-la dans la case et cliquez Ajouter.  Les entrées peuvent utiliser les expressions régulières complètes de Perl.',
    'e3'            => 'Pour ajouter les mots de la liste de Censure glFusions Cliquez le bouton:',
    'edit_filter_entry' => 'Edit Filter',
    'edit_filters'  => 'Edit Filters',
    'email'         => 'Email',
    'emailmsg'      => "Un nouveau commentaire indésirable a été envoyé à \"%s\"\nUser UID:\"%s\"\n\nContent:\"%s\"",
    'emailsubject'  => 'Spam post at %s',
    'enabled'       => 'Désactivez le plugin avant la désinstallation.',
    'entries'       => ' entrées.',
    'entriesadded'  => 'entrées ajoutées',
    'entriesdeleted'=> 'entrées supprimées',
    'exmod'         => 'Modules Vérification de Spam-X',
    'filter'        => 'Filter',
    'filter_instruction' => 'Here you can define filters which will be applied to each registration and post on the site. If any of the checks return true, the registration / post will be blocked as spam',
    'filters'       => 'Filters',
    'forum_post'    => 'Forum Post',
    'foundspam'     => 'Correspondance de commentaire indésirable trouvée ',
    'foundspam2'    => ' envoyé par l\'utilisateur ',
    'foundspam3'    => ' adresse IP ',
    'fsc'           => 'Correspondance de commentaire indésirable trouvée ',
    'fsc1'          => ' envoyé par l\'utilisateur ',
    'fsc2'          => ' adresse IP ',
    'headerblack'   => 'Spam-X HTTP Header Blacklist',
    'headers'       => 'Request headers:',
    'history'       => 'Past 3 Months',
    'http_header'   => 'HTTP Header',
    'http_header_prompt' => 'Header',
    'impinst1a'     => 'Avant d\'utiliser l\'utilitaire anti-comentaires indésirables Spam-X pour voir et importer les listes noires personnelles des autres',
    'impinst1b'     => ' sites, je vous demande d\'utiliser les deux boutons suivants. (Le dernier est obligatoire.)',
    'impinst2'      => 'Le premier soumet votre site web au site Gplugs/Spam-X afin de l\'ajouter à la liste principale des ',
    'impinst2a'     => 'sites partagant leur liste noire. (Note: si vous avez plusieurs sites, vous devriez en désigner un comme site maître ',
    'impinst2b'     => 'et soumettre ce dernier seulement. Ceci vous permettra de mettre votre site à jour facilement tout en conservant une liste plus petite.) ',
    'impinst2c'     => 'Après avoir cliqué le bouton Soumettre, utilisez le bouton [précédent] de votre navigateur pour revenir à cette page.',
    'impinst3'      => 'Les données suivantes seront envoyées: (vous pouvez les modifier si nécessaire).',
    'import_failure'=> '<p><strong>Erreur:</strong> Aucune entrée trouvée.',
    'import_success'=> '<p>Importation avec succès de %d entrées dans la liste noire.',
    'initial_Pimport'=> '<p>Importer Liste Noire Personnelle"',
    'initial_import' => 'Importer Liste Noire Principale Originale',
    'inst1'         => '<p>Si vous faites ce qui suit, alors tout le monde ',
    'inst2'         => 'pourra voir et importer votre Liste Noire personnelle. Nous pouvons alors créer une base de données ',
    'inst3'         => 'plus efficace.</p><p>Si vous avez soumis votre site et ne désirez pas qu\'il reste sur cette liste ',
    'inst4'         => 'envoyez un courriel à  <a href="mailto:spamx@pigstye.net">spamx@pigstye.net</a> pour me le faire savoir. ',
    'inst5'         => 'Toutes les demandes seront honorées',
    'install'       => 'Installer',
    'install_failed' => 'Echec de l\'installation  -- Voyez votre le fichier d\'erreur pour en connaître la raison.',
    'install_header' => 'Installer/Désinstaller Plugin',
    'install_success' => 'Installation réussie',
    'installdoc'    => 'Document d\'installation.',
    'installed'     => 'Le Plugin est installé',
    'instructions'  => 'Spam-X allows you to define words, URLs, and other items that can be used to block spam posts on your site.',
    'interactive_tester' => 'Interactive Tester',
    'invalid_email_or_ip'   => 'Invalid e-mail address or IP address has been blocked',
    'invalid_item_id' => 'Invalid ID',
    'ip_address'    => 'IP Address',
    'ip_blacklist'  => 'IP Blacklist',
    'ip_error'  => 'The entry does not appear to be a valid IP or IP range',
    'ip_prompt' => 'Enter IP to block',
    'ipblack' => 'Spam-X IP Blacklist',
    'ipofurl'   => 'IP of URL',
    'ipofurl_prompt' => 'Enter IP of links to block',
    'ipofurlblack' => 'Spam-X IP of URL Blacklist',
    'logcleared' => '- fichier log Spam-X vide',
    'mblack' => 'Ma Liste Noire:',
    'new_entry' => 'New Entry',
    'new_filter_entry' => 'New Filter Entry',
    'no_bl_data_error' => 'No errors',
    'no_blocked' => 'No spam has been blocked by this module',
    'no_filter_data' => 'No filters have been defined',
    'ok' => 'OK',
    'pblack' => 'Liste Noire Personnelle Spam-X',
    'plugin' => 'Plugin',
    'plugin_name' => 'Spam-X',
    'readme' => 'ARRÊTEZ! Avant d\'installer veuillez lire le ',
    'referrer'      => 'Referrer',
    'response'      => 'Response',
    'rlinks' => 'Liens relatifs:',
    'rsscreated' => 'Fil RSS Créé',
    'scan_comments' => 'Scan Comments',
    'scan_trackbacks' => 'Scan Trackbacks',
    'secbut' => 'Ce second bouton permet de créer un fichier RDF afin de permettre l\'importation de votre liste.',
    'sitename' => 'Nom du Site: ',
    'slvwhitelist' => 'SLV Whitelist',
    'spamdeleted' => 'Commentaire indésirable supprimé',
    'spamx_filters' => 'Spam-X Filters',
    'stats_deleted' => 'Posts deleted as spam',
    'stats_entries' => 'Entries',
    'stats_header' => 'HTTP headers',
    'stats_headline' => 'Spam-X Statistics',
    'stats_ip' => 'Blocked IPs',
    'stats_ipofurl' => 'Blocked by IP of URL',
    'stats_mtblacklist' => 'MT-Blacklist',
    'stats_page_title' => 'Blacklist',
    'stats_pblacklist' => 'Personal Blacklist',
    'submit'        => 'Soumettre',
    'submit' => 'Submit',
    'subthis' => 'à la base de données centrale Spam-X',
    'type'  => 'Type',
    'uMTlist' => 'Mettre à jour Liste Noire principale',
    'uMTlist2' => ': Ajouté ',
    'uMTlist3' => ' entrées et supprimées ',
    'uPlist' => 'Mettre à jour Liste Noire personnelle',
    'uninstall' => 'Désinstaller',
    'uninstall_msg' => 'Désinstallation du Plugin réussie',
    'uninstalled' => 'le Plugin n\'est pas installé',
    'user_agent'    => 'User Agent',
    'username'  => 'Username',
    'value' => 'Valeur',
    'viewlog' => 'Voir fichier log Spam-X',
    'warning' => 'Attention! Plugin encore activé',
);


/* Define Messages that are shown when Spam-X module action is taken */
$PLG_spamx_MESSAGE128 = 'Commentaire indésirable détecté et Commentaire ou Message supprimé.';
$PLG_spamx_MESSAGE8   = 'Commentaire indésirable détecté et Commentaire supprimé. Courriel envoyé à l\\Administrateur.';

// Messages for the plugin upgrade
$PLG_spamx_MESSAGE3001 = 'Plugin Mise à niveau non pris en charge.';
$PLG_spamx_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['spamx'] = array(
    'label' => 'Spam-X',
    'title' => 'Spam-X Configuration'
);

$LANG_confignames['spamx'] = array(
    'action' => 'Spam-X Actions',
    'notification_email' => 'Courriel de notification',
    'admin_override' => "Don't Filter Admin Posts",
    'logging' => 'Enable Logging',
    'timeout' => 'Timeout',
    'sfs_username_check' => 'Enable User name validation',
    'sfs_email_check' => 'Enable email validation',
    'sfs_ip_check' => 'Enable IP address validation',
    'sfs_username_confidence' => 'Minimum confidence level on Username match to trigger spam block',
    'sfs_email_confidence' => 'Minimum confidence level on Email match to trigger spam block',
    'sfs_ip_confidence' => 'Minimum confidence level on IP address match to trigger spam block',
    'slc_max_links' => 'Maximum Links allowed in post',
    'debug' => 'Debug Logging',
    'akismet_enabled' => 'Akismet Module Enabled',
    'akismet_api_key' => 'Akismet API Key (Required)',
    'fc_enable' => 'Enable Form Check',
    'sfs_enable' => 'Enable Stop Forum Spam',
    'slc_enable' => 'Enable Spam Link Counter',
    'action_delete' => 'Delete Identified Spam',
    'action_mail' => 'Mail Admin when Spam Caught',
);

$LANG_configsubgroups['spamx'] = array(
    'sg_main' => 'Paramètres Principaux'
);

$LANG_fs['spamx'] = array(
    'fs_main' => 'Spam-X Main Settings',
    'fs_sfs'  => 'Stop Forum Spam',
    'fs_slc'  => 'Spam Link Counter',
    'fs_akismet' => 'Akismet',
    'fs_formcheck' => 'Form Check',
);

$LANG_configSelect['spamx'] = array(
    0 => array(1 => 'True', 0 => 'False'),
    1 => array(TRUE => 'True', FALSE => 'False')
);
?>
