<?php
// | FileMgmt Plugin - glFusion CMS                                              |
// + ----------------------------------------------- --------------------------- +
// | French_canada_utf-8.php                                                     |
// |                                                                             |
// | File de langue anglaise                                                     |
// + ----------------------------------------------- --------------------------- +
// | $ Id $ ::                                                                   |
// + ----------------------------------------------- --------------------------- +
// | Copyright (C) 2008-2011 par les auteurs suivants:                           |
// |                                                                             |
// | Mark R. Evans marque AT glFusion DOT org                                    |
// |                                                                             |
// | Basé sur le plugin FileMgmt pour Geeklog                                    |
// | Copyright (C) 2004 par Consult4Hire Inc.                                    |
// | Auteur:                                                                     |
// | Blaine Lang blaine@portalparts.com                                          |
// + ----------------------------------------------- --------------------------- +
// |                                                                             |
// | Ce programme est un logiciel libre; vous pouvez le redistribuer et / ou     |
// | Modifier selon les termes de la Licence Publique Générale GNU               |
// | Publié par la Free Software Foundation; soit la version 2                   |
// | De la Licence, ou (à votre choix) toute version ultérieure.                 |
// |                                                                             |
// | Ce programme est distribué dans l`espoir qu`il sera utile,                  |
// | Mais SANS AUCUNE GARANTIE; sans même la garantie implicite de               |
// | COMMERCIALISATION ou D`ADAPTATION À UN USAGE PARTICULIER. Voir l`           |
// | Licence Publique Générale GNU pour plus de détails.                         |
// |                                                                             |
// | Vous devriez avoir reçu une copie de la Licence Publique Générale GNU       |
// | Avec ce programme; si pas, écrivez à la Free Software Foundation,           |
// | Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.              |
// |                                                                             |
// + ----------------------------------------------- --------------------------- +

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
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
    'newpage' => 'Nouvelle Page',
    'adminhome' => 'Accueil Admin',
    'plugin_name' => 'Gestion des fichiers',
    'searchlabel' => 'Une liste de fichiers',
    'searchlabel_results' => 'Liste des fichiers Résultats',
    'downloads' => 'Mes Téléchargements',
    'report' => 'Haut Téléchargements',
    'usermenu1' => 'Téléchargements',
    'usermenu2' => '&nbsp;&nbsp;Les mieux notés',
    'usermenu3' => 'Télécharger un fichier',
    'admin_menu' => 'Filemgmt Admin',
    'writtenby' => 'Écrit Par',
    'date' => 'Dernière mise à jour',
    'title' => 'Titre',
    'content' => 'Teneur',
    'hits' => 'Hits',
    'Filelisting' => 'Une liste de fichiers',
    'DownloadReport' => 'Télécharger Histoire de fichier unique',
    'StatsMsg1' => 'Haut Dix des fichiers accédés en dépôt',
    'StatsMsg2' => 'Il semble qu`il n`y a pas de fichiers définis pour le plugin filemgmt sur ​​ce site ou ne les a jamais consultée.',
    'usealtheader' => 'Utilisez Alt. Rubrique',
    'url' => 'URL',
    'edit' => 'Modifier',
    'lastupdated' => 'Dernière mise à jour',
    'pageformat' => 'Format de page',
    'leftrightblocks' => 'Gauche et blocs Droite',
    'blankpage' => 'Page Vierge',
    'noblocks' => 'Pas de Blocs',
    'leftblocks' => 'Gauche Blocs',
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
    'desc_file'                 => 'Link: to a File download detail page.  link_text defaults to the file title. usage: [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Link: to a direct File download.  link_text defaults to the file title. usage: [file_download:<i>file_id</i> {link_text}]',
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'FileMgmt',
    'title'                 => 'FileMgmt Configuration'
);
$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'Enable WhatsNew Listing',
    'perpage'               => 'Displayed Downloads per Page',
    'popular_download'      => 'Hits to be Popular',
    'newdownloads'          => 'Number of Downloads as New on Top Page',
    'trimdesc'              => 'Trim File Descriptions in Listing',
    'dlreport'              => 'Restrict Access to Download Report',
    'selectpriv'            => 'Restrict Access to Group \'Logged-In Users\' Only',
    'uploadselect'          => 'Allow Logged-In Uploads',
    'uploadpublic'          => 'Allow Anonymous Uploads',
    'useshots'              => 'Display Category Images',
    'shotwidth'             => 'Thumbnail Img Width',
    'Emailoption'           => 'Email Submitter if File Approved',
    'FileStore'             => 'Directory to Store Files',
    'SnapStore'             => 'Directory to Store File Thumbnails',
    'SnapCat'               => 'Directory to Store Category Thumbnails',
    'FileStoreURL'          => 'URL to Files',
    'FileSnapURL'           => 'URL to File Thumbnails',
    'SnapCatURL'            => 'URL to Category Thumbnails',
    'whatsnewperioddays'    => 'What\'s New Days',
    'whatsnewtitlelength'   => 'What\'s New Title Length',
    'showwhatsnewcomments'  => 'Show Comments in What\'s New Block',
    'numcategoriesperrow'   => 'Categories per Row',
    'numsubcategories2show' => 'Sub Categories per Row',
    'outside_webroot'       => 'Store Files Outside Web Root',
    'enable_rating'         => 'Enable Ratings',
    'displayblocks'         => 'Display glFusion Blocks',
    'silent_edit_default'   => 'Silent Edit Default',
);
$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Main Settings'
);
$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Public FileMgmt Settings',
    'fs_admin'              => 'FileMgmt Admin Settings',
    'fs_permissions'        => 'Default Permissions',
    'fm_access'             => 'FileMgmt Access Control',
    'fm_general'            => 'FileMgmt General Settings',
);
// Note: entries 0, 1 are the same as in $LANG_configselects['Core']
$LANG_configselects['filemgmt'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE),
    2 => array(' 5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25,'30' => 30,'50' => 50),
    3 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);



$PLG_filemgmt_MESSAGE1 = 'Filemgmt Plugin Install Aborted<br>File: plugins/filemgmt/filemgmt.php is not writeable';
$PLG_filemgmt_MESSAGE3 = 'This plugin requires glFusion Version 1.0 or greater, upgrade aborted.';
$PLG_filemgmt_MESSAGE4 = 'Plugin version 1.5 code not detected - upgrade aborted.';
$PLG_filemgmt_MESSAGE5 = 'Filemgmt Plugin Upgrade Aborted<br>Current plugin version is not 1.3';


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
define("_MD_EDIT","Edit");

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

define("_MD_INTFILEAT","Interesting Download File at %s"); // %s is your site name
define("_MD_INTFILEFOUND","Here is an interesting download file I have found at %s"); // %s is your site name

define("_MD_RECEIVED","We received your download information. Thanks!");
define("_MD_WHENAPPROVED","You'll receive an E-mail when it's approved.");
define("_MD_SUBMITONCE","Submit your file/script only once.");
define("_MD_APPROVED", "Your file has been approved");
define("_MD_ALLPENDING","All file/script information are posted pending verification.");
define("_MD_DONTABUSE","Username and IP are recorded, so please don't abuse the system.");
define("_MD_TAKEDAYS","It may take several days for your file/script to be added to our database.");
define("_MD_FILEAPPROVED", "Your file has been added to the file repository");

define("_MD_RANK","Rank");
define("_MD_CATEGORY","Category");
define("_MD_HITS","Hits");
define("_MD_RATING","Rating");
define("_MD_VOTE","Vote");

define("_MD_SEARCHRESULT4","Search results for <b>%s</b>:");
define("_MD_MATCHESFOUND","%s matche(s) found.");
define("_MD_SORTBY","Sort by:");
define("_MD_TITLE","Title");
define("_MD_DATE","Date");
define("_MD_POPULARITY","Popularity");
define("_MD_CURSORTBY","Files currently sorted by: ");
define("_MD_FOUNDIN","Found in:");
define("_MD_PREVIOUS","Previous");
define("_MD_NEXT","Next");
define("_MD_NOMATCH","No matches found to your query");

define("_MD_TOP10","%s Top 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Categories");

define("_MD_SUBMIT","Submit");
define("_MD_CANCEL","Cancel");

define("_MD_BYTES","Bytes");
define("_MD_ALREADYREPORTED","You have already submitted a broken report for this resource.");
define("_MD_MUSTREGFIRST","Sorry, you don't have the permission to perform this action.<br>Please register or login first!");
define("_MD_NORATING","No rating selected.");
define("_MD_CANTVOTEOWN","You cannot vote on the resource you submitted.<br>All votes are logged and reviewed.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Record your file rating");
define("_MD_ADMINTITLE","File Management Administration");
define("_MD_UPLOADTITLE","File Management - Add new file");
define("_MD_CATEGORYTITLE","File Listing - Category View");
define("_MD_DLCONF","Downloads Configuration");
define("_MD_GENERALSET","Configuration Settings");
define("_MD_ADDMODFILENAME","Add new file");
define ("_MD_ADDCATEGORYSNAP", 'Optional Image:<div style="font-size:8pt;">Top Level Categories only</div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;">Image height will be resized to 50</span>');
define("_MD_ADDMODCATEGORY","<b>Categories:</b> Add, Modify, and Delete Categories");
define("_MD_DLSWAITING","Downloads Waiting for Validation");
define("_MD_BROKENREPORTS","Broken File Reports");
define("_MD_MODREQUESTS","Download Info Modification Requests");
define("_MD_EMAILOPTION","Email submitter if file approved: ");
define("_MD_COMMENTOPTION","Enable comments:");
define("_MD_SUBMITTER","Submitter: ");
define("_MD_DOWNLOAD","Download");
define("_MD_FILELINK","File Link");
define("_MD_SUBMITTEDBY","Submitted by: ");
define("_MD_APPROVE","Approve");
define("_MD_DELETE","Delete");
define("_MD_NOSUBMITTED","No New Submitted Downloads.");
define("_MD_ADDMAIN","Add MAIN Category");
define("_MD_TITLEC","Title: ");
define("_MD_CATSEC", "View Access: ");
define("_MD_UPLOADSEC", "Upload Access: ");
define("_MD_IMGURL","<br>Image Filename <font size='-2'> (located in your filemgmt_data/category_snaps directory - Image height will be resized to 50)</font>");
define("_MD_ADD","Add");
define("_MD_ADDSUB","Add SUB-Category");
define("_MD_IN","in");
define("_MD_ADDNEWFILE","Add New File");
define("_MD_MODCAT","Modify Category");
define("_MD_MODDL","Modify Download Info");
define("_MD_USER","User");
define("_MD_IP","IP Address");
define("_MD_USERAVG","User AVG Rating");
define("_MD_TOTALRATE","Total Ratings");
define("_MD_NOREGVOTES","No Registered User Votes");
define("_MD_NOUNREGVOTES","No Unregistered User Votes");
define("_MD_VOTEDELETED","Vote data deleted.");
define("_MD_NOBROKEN","No reported broken files.");
define("_MD_IGNOREDESC","Ignore (Ignores the report and only deletes this reported entry</b>)");
define("_MD_DELETEDESC","Delete (Deletes <b>the reported file entry in the repository</b> but not the actual file)");
define("_MD_REPORTER","Report Sender");
define("_MD_FILESUBMITTER","File Submitter");
define("_MD_IGNORE","Ignore");
define("_MD_FILEDELETED","File Deleted.");
define("_MD_FILENOTDELETED","Record was removed but File was not Deleted.<p>More then 1 record pointing to same file.");
define("_MD_BROKENDELETED","Broken file report deleted.");
define("_MD_USERMODREQ","User Download Info Modification Requests");
define("_MD_ORIGINAL","Original");
define("_MD_PROPOSED","Proposed");
define("_MD_OWNER","Owner: ");
define("_MD_NOMODREQ","No Download Modification Request.");
define("_MD_DBUPDATED","Database Updated Successfully!");
define("_MD_MODREQDELETED","Modification Request Deleted.");
define("_MD_IMGURLMAIN",'Image<div style="font-size:8pt;">Image height will be resized to 50px</div>');
define("_MD_PARENT","Parent Category:");
define("_MD_SAVE","Save Changes");
define("_MD_CATDELETED","Category Deleted.");
define("_MD_WARNING","WARNING: Are you sure you want to delete this Category and ALL its Files and Comments?");
define("_MD_YES","Yes");
define("_MD_NO","No");
define("_MD_NEWCATADDED","New Category Added Successfully!");
define("_MD_CONFIGUPDATED","New configuration saved");
define("_MD_ERROREXIST","ERROR: The download info you provided is already in the database!");
define("_MD_ERRORNOFILE","ERROR: File not found on record in the database!");
define("_MD_ERRORTITLE","ERROR: You need to enter TITLE!");
define("_MD_ERRORDESC","ERROR: You need to enter DESCRIPTION!");
define("_MD_NEWDLADDED","New download added to the database.");
define("_MD_NEWDLADDED_DUPFILE","Warning: Duplicate File. New download added to the database.");
define("_MD_NEWDLADDED_DUPSNAP","Warning: Duplicate Snap. New download added to the database.");
define("_MD_HELLO","Hello %s");
define("_MD_WEAPPROVED","We approved your download submission to our downloads section. The file name is: ");
define("_MD_THANKSSUBMIT","Thanks for your submission!");
define("_MD_UPLOADAPPROVED","Your uploaded file was approved");
define("_MD_DLSPERPAGE","Displayed Downloads per Page: ");
define("_MD_HITSPOP","Hits to be Popular: ");
define("_MD_DLSNEW","Number of Downloads as New on Top Page: ");
define("_MD_DLSSEARCH","Number of Downloads in Search Results: ");
define("_MD_TRIMDESC","Trim File Descriptions in Listing: ");
define("_MD_DLREPORT","Restrict access to Download report");
define("_MD_WHATSNEWDESC","Enable WhatsNew Listing");
define("_MD_SELECTPRIV","Restrict access to group 'Logged-In Users' only: ");
define("_MD_ACCESSPRIV","Enable Anonymous access: ");
define("_MD_UPLOADSELECT","Allow Logged-In uploads: ");
define("_MD_UPLOADPUBLIC","Allow Anonymous uploads: ");
define("_MD_USESHOTS","Display Category Images: ");
define("_MD_IMGWIDTH","Thumbnail Img Width: ");
define("_MD_MUSTBEVALID","Thumbnail image must be a valid image file under %s directory (ex. shot.gif). Leave it blank if no image file.");
define("_MD_REGUSERVOTES","Registered User Votes (total votes: %s)");
define("_MD_ANONUSERVOTES","Anonymous User Votes (total votes: %s)");
define("_MD_YOURFILEAT","Your file submitted at %s"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Visit our downloads section at %s");
define("_MD_DLRATINGS","Download Rating (total votes: %s)");
define("_MD_CONFUPDATED","Configuration Updated Successfully!");
define("_MD_NOFILES","No Files Found");
define("_MD_APPROVEREQ","* Upload needs to be approved in this category");
define("_MD_REQUIRED","* Required field");
define("_MD_SILENTEDIT","Silent Edit: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Not rated yet");
define("_IFNOTRELOAD","If the page does not automatically reload, please click <a href=\"%s\">here</a>");
define("_GL_ERRORNOACCESS","ERROR: No access to this Document Repository Section");
define("_GL_ERRORNOUPLOAD","ERROR: You do not have upload privilages");
define("_GL_ERRORNOADMIN","ERROR: This function is restricted");
define("_GL_NOUSERACCESS","does not have access to the Document Repository");
define("_MD_ERRUPLOAD","Filemgmt: Unable to upload - check permissions for the file store directories");
define("_MD_DLFILENAME","Filename: ");
define("_MD_REPLFILENAME","Replacement  File: ");
define("_MD_SCREENSHOT","Screenshot");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Comments are appreciated");
define("_MD_CLICK2SEE","Click to see: ");
define("_MD_CLICK2DL","Click to download: ");
define("_MD_ORDERBY","Order By: ");
?>