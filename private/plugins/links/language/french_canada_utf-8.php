<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Links Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001-2007 by the following authors:
*   Tony Bibbs - tony AT tonybibbs DOT com
*   Trinity Bays - trinity93 AT gmail DOT com
*   Tom Willett - twillett AT users DOT sourceforge DOT net
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:    $LANG - variable name
#                 XX - file id number
#                 YY - phrase id number
###############################################################################

/**
* the link plugin's lang array
*
* @global array $LANG_LINKS
*/
$LANG_LINKS = array(
    10 => 'Soumissions',
    14 => 'Liens',
    84 => 'LIENS',
    88 => 'Pas de nouveaux liens',
    114 => 'Liens',
    116 => 'Ajoutez un lien',
    117 => 'Report Broken Link',
    118 => 'Rapport de Lien Brisé',
    119 => 'Le lien suivant a été signalé à être rompu: ',
    120 => 'Pour modifier le lien, cliquez ici: ',
    121 => 'Le Lien cassé a été signalé par: ',
    122 => 'Merci d`avoir signalé ce lien rompu. L`administrateur va corriger le problème dès que possible',
    123 => 'Merci',
    124 => 'Go',
    125 => 'Catégories',
    126 => 'Vous êtes ici:',
    'root' => 'Root',
    'error_header'  => 'Lien Soumission Erreur',
    'verification_failed' => 'L`URL spécifiée ne semble pas être une URL valide',
    'category_not_found' => 'La Catégorie ne semble pas être valide',
    'no_links'  => 'No links have been entered.',
);

###############################################################################
# for stats
/**
* the link plugin's lang stats array
*
* @global array $LANG_LINKS_STATS
*/
$LANG_LINKS_STATS = array(
    'links' => 'Liens (Clics) dans le système',
    'stats_headline' => 'Haut dix des liens',
    'stats_page_title' => 'Liens',
    'stats_hits' => 'Clics',
    'stats_no_hits' => 'Il semble qu`il n`y a pas de liens sur ce site ou personne n`a jamais cliqué sur un.',
);

###############################################################################
# for the search
/**
* the link plugin's lang search array
*
* @global array $LANG_LINKS_SEARCH
*/
$LANG_LINKS_SEARCH = array(
 'results' => 'Lien Résultats',
 'title' => 'Titre',
 'date' => 'Date Ajouté',
 'author' => 'Envoyé par',
 'hits' => 'Clics'
);

###############################################################################
# for the submission form
/**
* the link plugin's lang submit form array
*
* @global array $LANG_LINKS_SUBMIT
*/
$LANG_LINKS_SUBMIT = array(
    1 => 'Soumettez un lien',
    2 => 'Lien',
    3 => 'Catégorie',
    4 => 'Autre',
    5 => 'Si autre, s`il vous plaît spécifier',
    6 => 'Erreur : Catégorie manquante',
    7 => 'Lorsque vous sélectionnez "Autre" s`il vous plaît fournir également un nom de catégorie',
    8 => 'Titre',
    9 => 'URL',
    10 => 'Catégorie',
    11 => 'Liens soumis',
    12 => 'Soumis Par',
);

###############################################################################
# autotag description

$LANG_LI_AUTOTAG = array(
    'desc_link'                 => 'Lien: vers la page de détail d`un lien sur ce site; LINK_TEXT par défaut le nom du lien. utilisation: [link:<i>link_id</i> {link_text}]',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Merci de soumettre un lien vers {$_CONF['site_name']}.  Il a été soumis à notre personnel pour approbation. Si elle est approuvée, votre lien sera vu dans le <a href={$_CONF['site_url']}/links/index.php>section des liens</a>.";
$PLG_links_MESSAGE2 = 'Votre lien a bien été enregistré.';
$PLG_links_MESSAGE3 = 'Le lien a été supprimé avec succès.';
$PLG_links_MESSAGE4 = "Merci de soumettre un lien vers {$_CONF['site_name']}. Vous pouvez voir maintenant dans le <a href={$_CONF['site_url']}/links/index.php>section des liens</a>.";
$PLG_links_MESSAGE5 = "Vous n`avez pas les droits d`accès suffisants pour visualiser cette catégorie.";
$PLG_links_MESSAGE6 = 'Vous n`avez pas les droits suffisants pour modifier cette catégorie.';
$PLG_links_MESSAGE7 = 'S`il vous plaît entrer un Nom de la catégorie et la description.';

$PLG_links_MESSAGE10 = 'Votre catégorie a été enregistré avec succès.';
$PLG_links_MESSAGE11 = 'Vous n`êtes pas autorisé à mettre l`id d`une catégorie de (site) ou (user) - ceux-ci sont réservés à un usage interne.';
$PLG_links_MESSAGE12 = 'Vous essayez de faire une catégorie mère de l`enfant de son propre sous-catégorie. Cela créerait une catégorie d`orphelin, donc s`il vous plaît passer d`abord la ou les catégories enfant jusqu`à un niveau plus élevé.';
$PLG_links_MESSAGE13 = 'La catégorie a été supprimé avec succès.';
$PLG_links_MESSAGE14 = 'Catégorie contient des liens et / ou catégories. S`il vous plaît supprimer ces premiers.';
$PLG_links_MESSAGE15 = 'Vous n`avez pas les droits suffisants pour supprimer cette catégorie.';
$PLG_links_MESSAGE16 = 'Une telle catégorie existe.';
$PLG_links_MESSAGE17 = 'Cette catégorie id est déjà en cours d`utilisation.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Plugin Mise à niveau non pris en charge.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/link.php
/**
* the link plugin's lang admin array
*
* @global array $LANG_LINKS_ADMIN
*/
$LANG_LINKS_ADMIN = array(
    1 => 'Lien Éditeur',
    2 => 'Lien ID',
    3 => 'Titre du Lien',
    4 => 'Lien URL',
    5 => 'Catégorie',
    6 => '(inclure http://)',
    7 => 'Autre',
    8 => 'Lien Clics',
    9 => 'Description du Lien',
    10 => 'Vous devez fournir un lien Titre, URL et description.',
    11 => 'Lien Directeur',
    12 => 'Pour modifier ou supprimer un lien, cliquez sur Modifier l`icône de ce lien ci-dessous. Pour créer un nouveau lien, cliquez sur "Créer un nouveau" ci-dessus.',
    14 => 'Lien Catégorie',
    16 => 'Accès Refusé',
    17 => "Vous essayez d`accéder à un lien que vous n`avez pas droit à. Cette tentative a été enregistré. s`il vous plaît <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">revenir à l`écran d`administration de lien</a>.",
    20 => 'Si autre, précisez',
    21 => 'sauver',
    22 => 'Annuler',
    23 => 'Effacer',
    24 => 'Lien introuvable',
    25 => 'Le lien que vous avez sélectionné pour l`édition n`a pu être trouvée.',
    26 => 'Valider Liens',
    27 => 'HTML Etat',
    28 => 'Modifier une catégorie',
    29 => 'Saisir ou modifier les détails ci-dessous.',
    30 => 'Catégorie',
    31 => 'Description',
    32 => 'Catégorie ID',
    33 => 'Sujet',
    34 => 'Mère',
    35 => 'Tous',
    40 => 'Modifier cette catégorie',
    41 => 'Créez catégorie enfant',
    42 => 'Supprimer cette catégorie',
    43 => 'Catégories du site',
    44 => 'Ajouter un enfant',
    46 => 'Utilisateur %s a tenté de supprimer une catégorie à laquelle ils n`ont pas de droits d`accès',
    50 => 'Catégories de la Liste',
    51 => 'Nouveau Lien',
    52 => 'Nouvelle Catégorie',
    53 => 'Liste des liens',
    54 => 'Catégorie Directeur',
    55 => 'Modifier les catégories ci-dessous. Notez que vous ne pouvez pas supprimer une catégorie qui contient d`autres catégories ou des liens - vous devez supprimer ces premiers, ou les déplacer vers une autre catégorie.',
    56 => 'Catégorie Éditeur',
    57 => 'Pas encore validé',
    58 => 'Validez Maintenant',
    59 => '<p>Pour valider tous les liens, s`il vous plaît cliquer sur le lien "Valider maintenant" ci-dessous. S`il vous plaît noter que cela peut prendre du temps en fonction de la quantité de liens affichés.</p>',
    60 => 'Utilisateur %s tenté illégalement de modifier la catégorie %s.',
    61 => 'Propriétaire',
    62 => 'Dernière Mise à Jour',
    63 => 'Etes-vous sûr de vouloir supprimer ce lien?',
    64 => 'Etes-vous sûr de vouloir supprimer cette catégorie?',
    65 => 'Lien Modérée',
    66 => 'Cet écran vous permet de créer / modifier des liens.',
    67 => 'Cet écran vous permet de créer / modifier une catégorie de liens.',
);

$LANG_LINKS_STATUS = array(
    100 => "Continuer",
    101 => "Protocoles de Commutation",
    200 => "Bien",
    201 => "Établi",
    202 => "Accepté",
    203 => "Informations non-autoritaire",
    204 => "Aucun Contenu",
    205 => "Réinitialiser Contenu",
    206 => "Contenu Partiel",
    300 => "Choix Multiples",
    301 => "Déplacé de Manière Permanente",
    302 => "Trouvé",
    303 => "Voir les Autres",
    304 => "Pas de Modification",
    305 => "Utilisez Proxy",
    307 => "Redirection Temporaire",
    400 => "Mauvais Demande",
    401 => "Non Autorisé",
    402 => "Paiement Requis",
    403 => "Interdit",
    404 => "Introuvable",
    405 => "Méthode Non Autorisée",
    406 => "Pas Acceptable",
    407 => "Proxy Authentication Required",
    408 => "Durée de la Demande",
    409 => "Conflit",
    410 => "Finie",
    411 => "Longueur Requise",
    412 => "Condition Échec",
    413 => "Requête Trop Grande",
    414 => "URI de la demande et à long",
    415 => "Type de support non pris en charge",
    416 => "Demandé Plage Non Satisfiable",
    417 => "Échec Attente",
    500 => "Erreur Interne du Serveur",
    501 => "Non Mise en Oeuvre",
    502 => "Mauvaise Passerelle",
    503 => "Service Non Disponible",
    504 => "Passerelle Timeout",
    505 => "Version HTTP non prise en charge",
    999 => "Temps de connexion dehors"
);


// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Liens',
    'title' => 'Configuration des Liens'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => 'Liens Connexion requise?',
    'linksubmission' => 'Permettre la Présentation de File d`Attente?',
    'newlinksinterval' => 'Nouveau Liens Intervalle',
    'hidenewlinks' => 'Hide Nouvelle-Liens?',
    'hidelinksmenu' => 'Masquer Liens entrée de menu?',
    'linkcols' => 'Catégories par Colonne',
    'linksperpage' => 'Liens par Page',
    'show_top10' => 'Voir Haut 10 Liens?',
    'notification' => 'Courriel de Notification?',
    'delete_links' => 'Supprimer Liens avec le Propriétaire?',
    'aftersave' => 'Après Enregistrement Lien',
    'show_category_descriptions' => 'Voir Description de la Catégorie?',
    'root' => 'ID de catégorie Root',
    'default_permissions' => 'Permission Lien par Défaut',
    'target_blank' => 'Les liens s`ouvrent dans une nouvelle fenêtre',
    'displayblocks' => 'Afficher glFusion Blocs',
    'submission'    => 'Link Submission',
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'Paramètres Principaux'
);

$LANG_fs['links'] = array(
    'fs_public' => 'Liens Publiques Paramètres de la Liste',
    'fs_admin' => 'Liens Paramètres Administrateur',
    'fs_permissions' => 'Autorisations par Défaut'
);

$LANG_configSelect['links'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    9 => array('item'=>'Forward to Linked Site', 'list'=>'Display Admin List', 'plugin'=>'Display Public List', 'home'=>'Display Home', 'admin'=>'Display Admin'),
    12 => array(0=>'No access', 2=>'Lecture-Seule', 3=>'Read-Write'),
    13 => array(0=>'Gauche Blocs', 1=>'Right Blocks', 2=>'Gauche et blocs Droite', 3=>'None'),
    14 => array(0=>'None', 1=>'Logged-in Only', 2=>'Anyone', 3=>'None')

);

?>
