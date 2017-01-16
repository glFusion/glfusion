<?php
###############################################################################
# french_canada.php
#
# This is the Canadian French language file for the glFusion Calendar plugin
#
# Copyright (C) 2001 Tony Bibbs
# tony@tonybibbs.com
# Copyright (C) 2005 Trinity Bays
# trinity93@gmail.com
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
###############################################################################

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

# index.php
$LANG_CAL_1 = array(
    1 => 'Calendrier',
    2 => 'Désolé, il n\'y a rien à l\'horaire.',
    3 => 'Quand',
    4 => 'Où',
    5 => 'Description',
    6 => 'Ajout',
    7 => 'À venir',
    8 => 'Ajoutez cet évènement à votre calendrier personnel et accédez à vos évènements privés via la fonction calendrier de votre zone membre.',
    9 => 'Ajoutez à mon calendrier',
    10 => 'Retirez de mon calendrier',
    11 => 'Ajoutez au calendrier de %s',
    12 => 'Évènement',
    13 => 'Début',
    14 => 'Fin',
    15 => 'De retour au calendrier',
    16 => 'Calendrier',
    17 => 'Date de début',
    18 => 'Date de fin',
    19 => 'Soumissions',
    20 => 'Titre',
    21 => 'Date de départ',
    22 => 'URL',
    23 => 'Vos évènements',
    24 => 'Les évènements du site',
    25 => 'Il n\'y a aucun évènement à venir',
    26 => 'Soumettre un évènement',
    27 => "En soumettant un évènement à {$_CONF['site_name']}, vous acceptez que celui-ci soit vu par tous les usagers du site. Cette fonction est interdite aux envois de type personnels.<br" . XHTML . "><br" . XHTML . ">La soumission apparaîtra au calendrier général une fois approuvé par l\'administrateur du site.",
    28 => 'Titre',
    29 => 'Heure du début',
    30 => 'Heure de la fin',
    31 => 'Toute la journée',
    32 => 'Adresse 1',
    33 => 'Adresse 2',
    34 => 'Ville',
    35 => 'Région',
    36 => 'Code postal',
    37 => 'Type',
    38 => 'Éditez les types',
    39 => 'Endroit',
    40 => 'Ajoutez à',
    41 => 'Calendrier Général',
    42 => 'Calendrier Personnel',
    43 => 'Lien',
    44 => 'HTML non-permis',
    45 => 'Envoyez',
    46 => 'Évènements dans le système',
    47 => 'Le top10',
    48 => 'Hits',
    49 => 'Pas d\'évènements en perspective, ou vous n\'avez pas cliqué sur un évènement.',
    50 => 'Évènements',
    51 => 'Effacer',
    52 => 'Submitted By',
    53 => 'Calendar View'
);

$_LANG_CAL_SEARCH = array(
    'results' => 'Résultats',
    'title' => 'Titre',
    'date_time' => 'Date et heure',
    'location' => 'Endroit',
    'description' => 'Description'
);

###############################################################################
# calendar.php ($LANG30)

$LANG_CAL_2 = array(
    8 => 'Choisissez',
    9 => '%s Event',
    10 => 'Évènement pour',
    11 => 'Le Calendrier Général',
    12 => 'Mon Calendrier',
    25 => 'Retour à ',
    26 => 'Toute la journée',
    27 => 'Semaine',
    28 => 'Calendrier perso de',
    29 => 'Calendrier général',
    30 => 'Effacez',
    31 => 'Ajoutez',
    32 => 'Évènement',
    33 => 'Date',
    34 => 'Heure',
    35 => 'Ajout rapide',
    36 => 'Soumettre',
    37 => 'Désolé, cette fonction n\'est pas activée sur ce site',
    38 => 'Éditeur personnel',
    39 => 'Jour',
    40 => 'Semaine',
    41 => 'Mois',
    42 => 'Ajoutez un évènement général',
    43 => 'Soumission des évènements'
);

###############################################################################
# admin/plugins/calendar/index.php, formerly admin/event.php ($LANG22)

$LANG_CAL_ADMIN = array(
    1 => 'Éditeur',
    2 => 'Erreur',
    3 => 'Mode Post',
    4 => 'URL',
    5 => 'Date de départ',
    6 => 'Date de fin',
    7 => 'Endroit',
    8 => 'Description',
    9 => '(inclure http://)',
    10 => 'Vous devez compléter tous les champs',
    11 => 'Calendar Manager',
    12 => 'Pour modifier ou supprimer un événement, cliquez sur Modifier l`icône de cet événement ci-dessous. Pour créer un nouvel événement, cliquez sur "Créer un nouveau" ci-dessus. Cliquez sur l`icône de copie pour créer une copie d`un événement existant.',
    13 => 'Auteur',
    14 => 'Date de départ',
    15 => 'Date de fin',
    16 => '',
    17 => "Vous essayez d'accéder à un événement que vous ne disposez pas des droits à. Cette tentative a été enregistré. s`il vous plaît <a href=\"{$_CONF['site_admin_url']}/plugins/calendar/index.php\">revenir à l`écran d`administration de l`événement</a>.",
    18 => '',
    19 => '',
    20 => 'Sauvegarder',
    21 => 'Annuler',
    22 => 'Effacer',
    23 => 'Mauvaise date de départ.',
    24 => 'Mauvaise date de fin.',
    25 => 'La fin précède le départ.',
    26 => 'Supprimer les anciennes entrées',
    27 => 'Ce sont les événements qui sont âgés de plus de ',
    28 => ' mois. S`il vous plaît cliquer sur l`icône de la corbeille sur le fond pour les supprimer, ou sélectionnez un laps de temps différent: <br /> Trouver toutes les entrées qui sont âgés de plus de ',
    29 => '',
    30 => 'Update List',
    31 => 'Etes-vous sûr de vouloir supprimer définitivement TOUS les utilisateurs sélectionnés?',
    32 => 'La Liste de TOUS',
    33 => 'Pas événements sélectionnés pour la suppression',
    34 => 'Event ID',
    35 => 'Ne pouvaient pas être supprimées',
    36 => 'Supprimé Avec Succès',
    37 => 'Événement Modérée',
    38 => 'Batch Event Admin',
    39 => 'Event Admin',
    40 => 'Event Liste',
    41 => 'Cet écran vous permet de modifier / créer des événements. Modifiez les champs ci-dessous et enregistrez.'
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Lien: à un événement de calendrier sur ce site; LINK_TEXT par défaut titre de l`événement: [calendar:<i>event_id</i> {link_text}]'
);

$LANG_CAL_MESSAGE = array(
    'save' => 'Évènement ajouté avec succès.',
    'delete' => 'Évènement effacé avec succès.',
    'private' => 'Évènement sauvegardé à votre calendrier',
    'login' => 'Vous ne pouvez pas ouvrir votre calendrier personnel jusqu`à ce que vous vous connectez',
    'removed' => 'L`événement a été supprimé de votre calendrier personnel',
    'noprivate' => 'Désolé, les calendriers persos ne sont pas admis sur ce site',
    'unauth' => 'Désolé, vous n`avez pas accès à la page d`administration de l`événement. S`il vous plaît noter que toutes les tentatives d`accès non autorisées caractéristiques sont enregistrés',
    'delete_confirm' => 'Etes-vous sûr de vouloir supprimer cet événement?'
);

$PLG_calendar_MESSAGE4 = "Merci de soumettre un évènement à {$_CONF['site_name']}.  Vous pourrez le visualisé sur le <a href=\"{$_CONF['site_url']}/calendar/index.php\">calendrier</a> une fois approuvé.";
$PLG_calendar_MESSAGE17 = 'Évènement sauvegardé avec succès.';
$PLG_calendar_MESSAGE18 = 'Évènement effacé avec succès.';
$PLG_calendar_MESSAGE24 = 'Évènement sauvegardé sur votre calendrier.';
$PLG_calendar_MESSAGE26 = 'Évènement effacé avec succès.';

// Messages for the plugin upgrade
$PLG_calendar_MESSAGE3001 = 'Plugin Mise à niveau non pris en charge.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Calendrier',
    'title' => 'Configuration du Calendrier'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => 'Identifiez-vous nécessaire?',
    'hidecalendarmenu' => 'Cachez Calendrier Menu entrée?',
    'personalcalendars' => 'Activer Calendriers personnels?',
    'eventsubmission' => 'Permettre la présentation de file d`attente?',
    'showupcomingevents' => 'Afficher les événements à venir?',
    'upcomingeventsrange' => 'Événements à venir Gamme',
    'event_types' => 'Types d`événements',
    'hour_mode' => 'Mode Heure',
    'notification' => 'Courriel de notification?',
    'delete_event' => 'Supprimer événements avec le propriétaire?',
    'aftersave' => 'Après l`enregistrement de l`événement',
    'default_permissions' => 'Permission cas de défaut',
    'only_admin_submit' => 'Seulement Autoriser les administrateurs à Soumettre',
    'displayblocks' => 'Afficher glFusion Blocs'
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'Paramètres Principaux'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'Calendrier des Paramètres Généraux',
    'fs_permissions' => 'Autorisations par Défaut'
);

// Note: entries 0, 1, 6, 9, 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['calendar'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    6 => array('12' => 12, '24' => 24),
    9 => array('Forward to Event' => 'item', 'Display Admin List' => 'list', 'Display Calendar' => 'plugin', 'Display Home' => 'home', 'Display Admin' => 'admin'),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
    13 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);

?>