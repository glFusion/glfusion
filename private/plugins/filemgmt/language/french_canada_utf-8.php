<?php
/**
* glFusion CMS
*
* UTF-8 Language File for FileMgt Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by the following authors:
*   Consult4Hire Inc.
*   Blaine Lang  - blaine AT portalparts DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

$LANG_FM00 = array (
    'access_denied'     => 'Accès refusé',
    'access_denied_msg' => 'Seuls les utilisateurs root ont accès à cette page. Votre nom d`utilisateur et IP ont été enregistrées.',
    'admin'             => 'Plugin Admin',
    'install_header'    => 'Installer / Désinstaller Plugin.',
    'installed'         => 'Le plugin et Bloc sont maintenant installés, <p> <i> Profitez,<br><a href="MAILTO:support@glfusion.org">glFusion équipe</a></i>',
    'uninstalled'       => 'Le plugin n`est pas installé',
    'install_success'   => 'Installation réussie<p><b>prochaines étapes</b>:
        <ol><li>Utilisez le Filemgmt Admin pour compléter la configuration du plugin</ol>
        <p>Revoir le <a href="%s">Notes d`installation </a> pour plus d`informations.',
    'install_failed'    => 'Échec de l`installation - Consultez votre journal des erreurs pour savoir pourquoi.',
    'uninstall_msg'     => 'Plugin succès Uninstalled',
    'install'           => 'Installer',
    'uninstall'         => 'Désinstallation',
    'editor'            => 'Éditeur plugin',
    'warning'           => 'De-Installez Avertissement',
    'enabled'           => '<p style="padding: 15px 0px 5px 25px;">Plugin est installé et activé. <br> Désactiver d`abord si vous souhaitez le désinstaller.</p><div style="padding:5px 0px 5px 25px;"><a href="'.$_CONF['site_admin_url'].'/plugins.php">Éditeur plugin</a></div',
    'WhatsNewLabel'    => 'Fichiers',
    'WhatsNewPeriod'   => ' Les jours de la %s dernier',
    'new_upload'        => 'Nouveau fichier soumis à ',
    'new_upload_body'   => 'Un nouveau fichier a été soumis à la file d`attente d`envoi à ',
    'details'           => 'Détails du fichier',
    'filename'          => 'Nom du fichier',
    'uploaded_by'       => 'Téléchargé par',
    'not_found'         => 'Download Not Found',
);

// Admin Navbar
$LANG_FM02 = array(
    'instructions' => 'Pour modifier ou supprimer un fichier, cliquez sur l`icône de modification de fichier ci-dessous. Pour afficher ou modifier les catégories, sélectionnez l`option Catégories ci-dessus.',
    'nav1'  => 'Paramètres',
    'nav2'  => 'Catégories',
    'nav3'  => 'Ajouter un fichier',
    'nav4'  => 'Téléchargements (%s)',
    'nav5'  => 'Fichiers brisé (%s)',
    'edit'  => 'Modifier',
    'file'  => 'Nom du fichier',
    'category' => 'Nom de la Catégorie',
    'version' => 'Version',
    'size'  => 'Superficie',
    'date' => 'Date',
);

$LANG_FILEMGMT = array(
    'newpage' => "Nouvelle Page",
    'adminhome' => "Accueil Admin",
    'plugin_name' => "Gestion des fichiers",
    'searchlabel' => "Une liste de fichiers",
    'searchlabel_results' => "Liste des fichiers Résultats",
    'downloads' => "Mes Téléchargements",
    'report' => "Haut Téléchargements",
    'usermenu1' => "Téléchargements",
    'usermenu2' => "&nbsp;&nbsp;Les mieux notés",
    'usermenu3' => "Télécharger un fichier",
    'admin_menu' => "Filemgmt Admin",
    'writtenby' => "Écrit Par",
    'date' => "Dernière mise à jour",
    'title' => "Titre",
    'content' => "Teneur",
    'hits' => "Hits",
    'Filelisting' => "Une liste de fichiers",
    'DownloadReport' => "Télécharger Histoire de fichier unique",
    'StatsMsg1' => "Haut Dix des fichiers accédés en dépôt",
    'StatsMsg2' => "Il semble qu`il n`y a pas de fichiers définis pour le plugin filemgmt sur ​​ce site ou ne les a jamais consultée.",
    'usealtheader' => "Utilisez Alt. Rubrique",
    'url' => "URL",
    'edit' => "Modifier",
    'lastupdated' => "Dernière mise à jour",
    'pageformat' => "Format de page",
    'leftrightblocks' => "Gauche et blocs Droite",
    'blankpage' => "Page Vierge",
    'noblocks' => "Pas de Blocs",
    'leftblocks' => "Gauche Blocs",
    'addtomenu' => 'Ajouter au menu',
    'label' => 'étiquette',
    'nofiles' => 'Nombre de fichiers dans notre référentiel (Téléchargements)',
    'save' => 'sauver',
    'preview' => 'avant-première',
    'delete' => 'effacer',
    'cancel' => 'annuler',
    'access_denied' => 'Accès refusé',
    'invalid_install' => 'Quelqu`un a essayé d`accéder illégalement la gestion du fichier de page d`installation / désinstallation. Identifiant de l`utilisateur: ',
    'start_install' => 'D`essayer d`installer le plugin Filemgmt',
    'start_dbcreate' => 'Tenter de créer des tables pour Filemgmt plug-in',
    'install_skip' => '... sautée comme par filemgmt.cfg',
    'access_denied_msg' => 'Vous essayez illégalement l`accès aux pages d`administration de Mgmt de fichier. S`il vous plaît noter que toutes les tentatives pour accéder illégalement cette page sont enregistrés',
    'installation_complete' => 'Installation terminée',
    'installation_complete_msg' => 'Les structures de données pour le plugin Gestion de fichiers pour glFusion ont été installés avec succès dans votre base de données! Si vous avez besoin de désinstaller ce plugin, s`il vous plaît lire le document README fourni avec ce plugin.',
    'installation_failed' => 'Échec de l`installation',
    'installation_failed_msg' => 'L`installation du plugin Gestion du fichier a échoué. S`il vous plaît voir le fichier error.log de ​​glFusion informations de diagnostic',
    'system_locked' => 'Système Fermé',
    'system_locked_msg' => 'Le plugin de gestion: de fichiers a déjà été installé et est verrouillée. Si vous essayez de désinstaller ce plugin, s`il vous plaît lire le document README fourni avec ce plugin',
    'uninstall_complete' => 'Désinstallez Complet',
    'uninstall_complete_msg' => 'Les structures de données pour le plugin Gestion de fichiers ont été supprimés à partir de votre base de données glFusion <br> Vous devrez supprimer tous les fichiers manuellement dans votre référentiel de fichiers.',
    'uninstall_failed' => 'Désinstaller Échec.',
    'uninstall_failed_msg' => 'La désinstallation du plugin Gestion du fichier a échoué. S`il vous plaît voir le fichier error.log de ​​glFusion informations de diagnostic',
    'install_noop' => 'Plugin Installer',
    'install_noop_msg' => 'Le plugin filemgmt installer exécuté mais il n`y avait rien à faire. <br> Vérifiez votre plugin install.cfg file.',
    'all_html_allowed' => 'Le code HTML est autorisé',
    'no_new_files'  => 'Pas de nouveaux fichiers',
    'no_comments'   => 'Pas de nouveaux commentaires',
    'more'          => '<em>d`autre ...</em>'
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Lien: vers une page de détail de téléchargement du fichier. LINK_TEXT par défaut le titre du fichier. utilisation: [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Lien: pour un téléchargement direct de fichiers. LINK_TEXT par défaut le titre du fichier. utilisation: [file_download:<i>file_id</i> {link_text}]',
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'FileMgmt',
    'title'                 => 'Configuration FileMgmt'
);
$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'Activer Quoi de neuf Annonce',
    'perpage'               => 'Téléchargements affichés par page',
    'popular_download'      => 'Résultats d`être populaire',
    'newdownloads'          => 'Nombre de Téléchargements la Nouvelle sur Haut Page',
    'trimdesc'              => 'Descriptions du fichier de finition à l`annonce',
    'dlreport'              => 'Limiter l`accès aux Télécharger le rapport',
    'selectpriv'            => 'Restreindre l`accès au groupe \'utilisateurs enregistrés\' Seulement',
    'uploadselect'          => 'Autoriser connecté Mises',
    'uploadpublic'          => 'Permettre Mises Anonymes',
    'useshots'              => 'Afficher la Catégorie Images',
    'shotwidth'             => 'Thumbnail Img Largeur',
    'Emailoption'           => 'Email Nom si Fichier Approuvé',
    'FileStore'             => 'Répertoire pour les Fichiers de Banque',
    'SnapStore'             => 'Annuaire de Fichier de Magasin Vignettes',
    'SnapCat'               => 'Répertoire pour stocker Catégorie Miniatures',
    'FileStoreURL'          => 'URL de Fichiers',
    'FileSnapURL'           => 'URL de Fichier Miniatures',
    'SnapCatURL'            => 'URL à la Catégorie Miniatures',
    'whatsnewperioddays'    => 'Quoi de neuf Jours',
    'whatsnewtitlelength'   => 'Quoi de neuf Titre Durée',
    'showwhatsnewcomments'  => 'Afficher les commentaires dans l` Qu`est-ce \\ Nouveau bloc',
    'numcategoriesperrow'   => 'Catégories Row',
    'numsubcategories2show' => 'Sous-Catégories Ligne',
    'outside_webroot'       => 'Stocker des fichiers en dehors racine Web',
    'enable_rating'         => 'Activer Évaluations',
    'displayblocks'         => 'Afficher glFusion Blocs',
    'silent_edit_default'   => 'Silencieux sur Modifier',
);
$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Paramètres Principaux'
);
$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Paramètres FileMgmt Publique',
    'fs_admin'              => 'Paramètres Administrateur FileMgmt',
    'fs_permissions'        => 'Autorisations par Défaut',
    'fm_access'             => 'Contrôle d`Accès FileMgmt',
    'fm_general'            => 'Paramètres FileMgmt Générales',
);
// Note: entries 0, 1 are the same as in $LANG_configselects['Core']
$LANG_configSelect['filemgmt'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    2 => array(5 => ' 5', 10 => '10', 15 => '15', 20 => '20', 25 => '25',30 => '30',50 => '50'),
    3 => array(0=>'Gauche Blocs', 1=>'Right Blocks', 2=>'Gauche et blocs Droite', 3=>'None')
);

$PLG_filemgmt_MESSAGE1 = 'Filemgmt Plugin Installer Aborted <br> fichier: plugins/filemgmt/filemgmt.php est pas accessible en écriture';
$PLG_filemgmt_MESSAGE3 = 'Ce plugin nécessite glFusion version 1.0 ou supérieure, niveau avorté.';
$PLG_filemgmt_MESSAGE4 = 'Version Plugin 1.5 du code non détecté - mise à niveau abandonnée.';
$PLG_filemgmt_MESSAGE5 = 'Filemgmt Plugin Version actuelle du plugin de Aborted n`est pas 1.3';

// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Merci pour l'information. Nous examinerons votre requête rapidement.");
define("_MD_BACKTOTOP","Retour au Top Téléchargements");
define("_MD_THANKSFORHELP","Merci de nous aider à maintenir l'intégrité de ce répertoire.");
define("_MD_FORSECURITY","Pour des raisons de sécurité, votre nom d'utilisateur et votre adresse IP seront temporairement enregistrés.");

define("_MD_SEARCHFOR","Recherche pour");
define("_MD_MATCH","Rencontre");
define("_MD_ALL","TOUT");
define("_MD_ANY","ANY");
define("_MD_NAME","Nom");
define("_MD_DESCRIPTION","Description");
define("_MD_SEARCH","Recherche");

define("_MD_MAIN","Réseau de distribution");
define("_MD_SUBMITFILE","Envoyer un fichier");
define("_MD_POPULAR","Populaire");
define("_MD_NEW","Nouveau");
define("_MD_TOPRATED","Les mieux notés");

define("_MD_NEWTHISWEEK","Nouveau cette semaine");
define("_MD_UPTHISWEEK","Mise à jour de la semaine");

define("_MD_POPULARITYLTOM","Popularité (Moins au plus Hits)");
define("_MD_POPULARITYMTOL","Popularité (Plus au moins Hits)");
define("_MD_TITLEATOZ","Titre (A to Z)");
define("_MD_TITLEZTOA","Titre (Z to A)");
define("_MD_DATEOLD","Date (Anciens fichiers en premier)");
define("_MD_DATENEW","Date (Nouveaux fichiers en premier)");
define("_MD_RATINGLTOH","Note (plus bas au plus grand score)");
define("_MD_RATINGHTOL","Notation (du plus au plus petit Score)");

define("_MD_NOSHOTS","Non Miniatures disponible");
define("_MD_EDITTHISDL","Modifier ce téléchargement");

define("_MD_LISTINGHEADING","<b>Déposer annonce: Il ya des fichiers %s dans notre base de données</b>");
define("_MD_LATESTLISTING","<b>Dernière annonce:</b>");
define("_MD_DESCRIPTIONC","Description:");
define("_MD_EMAILC","Email: ");
define("_MD_CATEGORYC","Catégorie: ");
define("_MD_LASTUPDATEC","Dernière mise à jour: ");
define("_MD_DLNOW","Télécharger maintenant!");
define("_MD_VERSION","Ver");
define("_MD_SUBMITDATE","Date");
define("_MD_DLTIMES","téléchargé %s fois");
define("_MD_FILESIZE","Taille du fichier");
define("_MD_SUPPORTEDPLAT","Plates-formes supportées");
define("_MD_HOMEPAGE","Accueil");
define("_MD_HITSC","Hits: ");
define("_MD_RATINGC","Rating: ");
define("_MD_ONEVOTE","1 vote");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","N/A");
define("_MD_NUMPOSTS","%s votes");
define("_MD_COMMENTSC","Commentaires: ");
define ("_MD_ENTERCOMMENT", "Créer le premier commentaire");
define("_MD_RATETHISFILE","Noter ce fichier");
define("_MD_MODIFY","Modifier");
define("_MD_REPORTBROKEN","Rapport de fichier brisé");
define("_MD_TELLAFRIEND","Envoyer à un ami");
define("_MD_VSCOMMENTS","Voir / Envoyer Commentaires");
define("_MD_EDIT","Modifier");

define("_MD_THEREARE","Il ya des fichiers% dans notre base de données");
define("_MD_LATESTLIST","Dernières listes");

define("_MD_REQUESTMOD","Requête de modification");
define("_MD_FILE","File");
define("_MD_FILEID","File ID: ");
define("_MD_FILETITLE","Titre: ");
define("_MD_DLURL","Télécharger URL: ");
define("_MD_HOMEPAGEC","Accueil: ");
define("_MD_VERSIONC","Version: ");
define("_MD_FILESIZEC","Taille du fichier: ");
define("_MD_NUMBYTES","%s bytes");
define("_MD_PLATFORMC","Plate-forme: ");
define("_MD_CONTACTEMAIL","Contactez Email: ");
define("_MD_SHOTIMAGE","Thumbnail Img: ");
define("_MD_SENDREQUEST","Envoyer la demande");

define("_MD_VOTEAPPRE","Votre vote est apprécié.");
define("_MD_THANKYOU","Merci d`avoir pris le temps de voter ici à %s"); // %s is your site name
define("_MD_VOTEFROMYOU","L`apport des utilisateurs comme vous aidera d'autres visiteurs à mieux décider le fichier à télécharger.");
define("_MD_VOTEONCE","S`il vous plaît ne pas voter pour la même ressource plus d`une fois.");
define("_MD_RATINGSCALE","The scale is 1 - 10, with 1 being poor and 10 being excellent.");
define("_MD_BEOBJECTIVE","S`il vous plaît être objectif, si tous reçoivent un 1 ou un 10, les notations ne sont pas très utiles.");
define("_MD_DONOTVOTE","Ne votez pas pour vos propres ressources.");
define("_MD_RATEIT","Notez-le!");

define("_MD_INTFILEAT","Intéressant Téléchargement de fichier à %s"); // %s is your site name
define("_MD_INTFILEFOUND","Voici un fichier de téléchargement intéressante que j`ai trouvé à %s"); // %s is your site name

define("_MD_RECEIVED","Nous avons reçu vos informations de téléchargement. Merci!");
define("_MD_WHENAPPROVED","Vous recevrez un e-mail quand il est approuvé.");
define("_MD_SUBMITONCE","Envoyer votre fichier / script une seule fois.");
define("_MD_APPROVED", "Votre fichier a été approuvé");
define("_MD_ALLPENDING","Toutes les informations fichier / script sont publiés après vérification.");
define("_MD_DONTABUSE","Nom d`utilisateur et IP sont enregistrés, s`il vous plaît ne pas abuser du système.");
define("_MD_TAKEDAYS","Il peut prendre plusieurs jours pour votre fichier / script pour être ajouté à notre base de données.");
define("_MD_FILEAPPROVED", "Votre fichier a été ajoutée au fichier référentiel");

define("_MD_RANK","Rank");
define("_MD_CATEGORY","Catégorie");
define("_MD_HITS","Résultats");
define("_MD_RATING","Évaluation");
define("_MD_VOTE","Votez");

define("_MD_SEARCHRESULT4","Résultats de Recherche pour <b>%s</b>:");
define("_MD_MATCHESFOUND","%s matche(s) trouvé.");
define("_MD_SORTBY","Trier par:");
define("_MD_TITLE","Titre");
define("_MD_DATE","Date");
define("_MD_POPULARITY","Popularité");
define("_MD_CURSORTBY","Fichiers actuellement triés par: ");
define("_MD_FOUNDIN","Résultats:");
define("_MD_PREVIOUS","Précédent");
define("_MD_NEXT","Suivant");
define("_MD_NOMATCH","Aucune correspondance trouvée à votre requête");

define("_MD_TOP10","%s Haut 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Catégories");

define("_MD_SUBMIT","Soumettre");
define("_MD_CANCEL","Annuler");

define("_MD_BYTES","Bytes");
define("_MD_ALREADYREPORTED","Vous avez déjà soumis un rapport cassé pour cette ressource.");
define("_MD_MUSTREGFIRST","Désolé, vous n`avez pas l`autorisation d`effectuer cette action. <br> S`il vous plaît vous inscrire ou vous connecter d`abord!");
define("_MD_NORATING","Pas d`évaluation choisi.");
define("_MD_CANTVOTEOWN","Vous ne pouvez pas voter sur la ressource que vous avez soumis. <br> Tous les votes sont enregistrés et examinés.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Enregistrer votre note de fichier");
define("_MD_ADMINTITLE","Administration Gestion des fichiers");
define("_MD_UPLOADTITLE","Gestion de Fichiers - Ajouter un Fichier");
define("_MD_CATEGORYTITLE","Une liste de Fichiers - l`affichage des catégories");
define("_MD_DLCONF","Téléchargements Configuration");
define("_MD_GENERALSET","Paramètres de Configuration");
define("_MD_ADDMODFILENAME","Ajouter un nouveau fichier");
define ("_MD_ADDCATEGORYSNAP", 'Optional Image:<div style="font-size:8pt;">Haut Niveau Catégories Seulement</div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;">Hauteur de l`image sera automatiquement redimensionnée à 50</span>');
define("_MD_ADDMODCATEGORY","<b>Catégories:</b> Ajouter, Modifier et Supprimer les Catégories");
define("_MD_DLSWAITING","Téléchargements en attente de validation");
define("_MD_BROKENREPORTS","Rapports de Fichier Brisé");
define("_MD_MODREQUESTS","Télécharger Demandes Infos Modification");
define("_MD_EMAILOPTION","Email expéditeur si le fichier a approuvé: ");
define("_MD_COMMENTOPTION","Activer les commentaires:");
define("_MD_SUBMITTER","Envoyé par: ");
define("_MD_DOWNLOAD","Télécharger");
define("_MD_FILELINK","Lien de Fichier");
define("_MD_SUBMITTEDBY","Soumis par: ");
define("_MD_APPROVE","Approuver");
define("_MD_DELETE","Effacer");
define("_MD_NOSUBMITTED","Pas de Nouveau Soumis Téléchargements.");
define("_MD_ADDMAIN","Ajouter Principale Catégorie");
define("_MD_TITLEC","Titre: ");
define("_MD_CATSEC", "Voir d`Accès: ");
define("_MD_UPLOADSEC", "Envoyez Accès: ");
define("_MD_IMGURL","<br>Nom du Fichier Image <font size='-2'> (situé dans le filemgmt_data/category_snaps category_snaps - Hauteur de l`image sera automatiquement redimensionnée à 50)</font>");
define("_MD_ADD","Ajouter");
define("_MD_ADDSUB","Ajouter Sous-Catégorie");
define("_MD_IN","dans");
define("_MD_ADDNEWFILE","Ajouter un Nouveau Fichier");
define("_MD_MODCAT","Modification d`une Catégorie");
define("_MD_MODDL","Modifier Télécharger Infos");
define("_MD_USER","Utilisateur");
define("_MD_IP","IP Adresse");
define("_MD_USERAVG","Note moyenne des utilisateurs");
define("_MD_TOTALRATE","Total des Évaluations");
define("_MD_NOREGVOTES","Aucun vote d`utilisateurs enregistrés");
define("_MD_NOUNREGVOTES","Aucun vote d`utilisateurs non-enregistrés");
define("_MD_VOTEDELETED","Voter les données supprimées.");
define("_MD_NOBROKEN","Non rapporté les fichiers endommagés.");
define("_MD_IGNOREDESC","Ignorer (Ignore le rapport et ne supprime cette entrée signalé</b>)");
define("_MD_DELETEDESC","Supprimer (Supprime <b> l`entrée du fichier indiqué dans le référentiel </b> mais pas le fichier réel)");
define("_MD_REPORTER","Rapport Expéditeur");
define("_MD_FILESUBMITTER","Nom du Fichier");
define("_MD_IGNORE","Ignore");
define("_MD_FILEDELETED","Fichier Supprimé.");
define("_MD_FILENOTDELETED","L`enregistrement a été retiré mais fichier n`a pas été supprimé. <p> Plus de 1 fiche de pointage à même fichier.");
define("_MD_BROKENDELETED","Rapport de Fichier Brisé Supprimé.");
define("_MD_USERMODREQ","Demandes Utilisateur Télécharger Infos Modification");
define("_MD_ORIGINAL","Original");
define("_MD_PROPOSED","Proposé");
define("_MD_OWNER","Propriétaire: ");
define("_MD_NOMODREQ","Pas de Modification pour un Fichier d'appel.");
define("_MD_DBUPDATED","Base de Données mise à jour avec Succès!");
define("_MD_MODREQDELETED","Demande de Modification Supprimé.");
define("_MD_IMGURLMAIN",'Image<div style="font-size:8pt;">Hauteur de l`image sera automatiquement redimensionnée à 50px</div>');
define("_MD_PARENT","Catégorie Parente:");
define("_MD_SAVE","Enregistrer les modifications");
define("_MD_CATDELETED","Catégorie Supprimé.");
define("_MD_WARNING","AVERTISSEMENT: Êtes-vous sûr de vouloir supprimer cette catégorie et tous ses fichiers et commentaires?");
define("_MD_YES","Oui");
define("_MD_NO","Aucun");
define("_MD_NEWCATADDED","Nouvelle Catégorie Ajouté avec Succès!");
define("_MD_CONFIGUPDATED","Nouvelle configuration sauvegardée");
define("_MD_ERROREXIST","ERREUR: Les informations de téléchargement que vous avez fourni est déjà dans la base de données!");
define("_MD_ERRORNOFILE","ERREUR: Fichier introuvable sur le disque dans la base de données!");
define("_MD_ERRORTITLE","ERREUR: Vous devez saisir TITRE!");
define("_MD_ERRORDESC","ERREUR: Vous devez saisir DESCRIPTION!");
define("_MD_NEWDLADDED","Nouveau téléchargement ajouté à la base de données.");
define("_MD_NEWDLADDED_DUPFILE","Attention: Dupliquer Déposer. Nouveau téléchargement ajouté à la base de données.");
define("_MD_NEWDLADDED_DUPSNAP","Attention: Dupliquer Snap. Nouveau téléchargement ajouté à la base de données.");
define("_MD_HELLO","Bonjour %s");
define("_MD_WEAPPROVED","Nous avons approuvé le téléchargement soumission à notre section de téléchargements. Le nom du fichier est: ");
define("_MD_THANKSSUBMIT","Merci pour votre présentation!");
define("_MD_UPLOADAPPROVED","Votre fichier téléchargé a été approuvé");
define("_MD_DLSPERPAGE","Téléchargements par Page affichée: ");
define("_MD_HITSPOP","Coups d`être populaires: ");
define("_MD_DLSNEW","Nombre de Téléchargements la Nouvelle sur Haut Page: ");
define("_MD_DLSSEARCH","Nombre de téléchargements dans Résultats de la recherche: ");
define("_MD_TRIMDESC","Coupez les descriptions de fichier dans l'annonce: ");
define("_MD_DLREPORT","Restreindre l`accès aux Télécharger le rapport");
define("_MD_WHATSNEWDESC","Activer Quoi de neuf Annonce");
define("_MD_SELECTPRIV","Restreindre l`accès au groupe 'Logged-In Users' uniquement: ");
define("_MD_ACCESSPRIV","Activer l`accès anonyme: ");
define("_MD_UPLOADSELECT","Autoriser les téléchargements connecté: ");
define("_MD_UPLOADPUBLIC","Autoriser les téléchargements anonymes: ");
define("_MD_USESHOTS","Afficher la Catégorie Images: ");
define("_MD_IMGWIDTH","Thumbnail Img Largeur: ");
define("_MD_MUSTBEVALID","Thumbnail image doit être un fichier image valide en vertu de %s répertoire (ex. shot.gif). Laissez ce champ vide si aucun fichier d`image.");
define("_MD_REGUSERVOTES","Votes des utilisateurs enregistrés (total des votes: %s)");
define("_MD_ANONUSERVOTES","Anonymous votes des utilisateurs (votes au total: %s)");
define("_MD_YOURFILEAT","Votre dossier soumis à %s"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Visitez notre section de téléchargements à %s");
define("_MD_DLRATINGS","Télécharger Note (total des votes: %s)");
define("_MD_CONFUPDATED","Configuration mise à jour avec succès!");
define("_MD_NOFILES","Aucun résultat");
define("_MD_APPROVEREQ","* Télécharger doit être approuvé dans cette catégorie");
define("_MD_REQUIRED","* Champ Obligatoire");
define("_MD_SILENTEDIT","Modifier le Silence: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Pas encore évalué");
define("_IFNOTRELOAD","Si la page ne se recharge pas automatiquement, s'il vous plaît cliquez sur <a href=\"%s\">ici</a>");
define("_GL_ERRORNOACCESS","ERREUR: Pas d`accès à cette section Archives de documents");
define("_GL_ERRORNOUPLOAD","ERREUR: Vous n`avez pas les privilèges d`envoi");
define("_GL_ERRORNOADMIN","ERREUR: Cette fonction est limitée");
define("_GL_NOUSERACCESS","ne pas avoir accès à l`archivage des documents");
define("_MD_ERRUPLOAD","Filemgmt: Impossible de télécharger - vérifiez les autorisations pour les répertoires de stockage de fichiers");
define("_MD_DLFILENAME","Nom du Fichier: ");
define("_MD_REPLFILENAME","Fichier de Remplacement:");
define("_MD_SCREENSHOT","Captures d`écran");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Les commentaires sont appréciés");
define("_MD_CLICK2SEE","Cliquez pour voir: ");
define("_MD_CLICK2DL","Cliquez pour télécharger: ");
define("_MD_ORDERBY","Trier par: ");
?>