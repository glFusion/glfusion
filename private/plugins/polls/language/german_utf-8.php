<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Polls Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001-2005 by the following authors:
*   Tony Bibbs - tony AT tonybibbs DOT com
*   Trinity Bays - trinity93 AT gmail DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

$LANG_POLLS = array(
    'polls'             => 'Umfragen',
    'results'           => 'Ergebnisse',
    'pollresults'       => 'Umfrage-Ergebnisse',
    'votes'             => 'Stimmen',
    'vote'              => 'Abstimmen',
    'pastpolls'         => 'Ältere Umfragen',
    'savedvotetitle'    => 'Stimme gespeichert',
    'savedvotemsg'      => 'Deine Stimme wurde gespeichert für die Umfrage',
    'pollstitle'        => 'Umfragen im System',
    'polltopics'        => 'Weitere Umfragen',
    'stats_top10'       => 'Top 10 der Umfragen',
    'stats_topics'      => 'Umfrage-Kategorie',
    'stats_votes'       => 'Stimmen',
    'stats_none'        => 'Es wurden noch keine Umfragen erstellt.',
    'stats_summary'     => 'Anzahl Umfragen (Stimmen)',
    'open_poll'         => 'Abstimmen möglich',
    'answer_all'        => 'Bitte alle übrigen Fragen beantworten',
    'not_saved'         => 'Ergebnis nicht gespeichert',
    'upgrade1'          => 'Neue Version des Umfrage-Plugins installiert. Bitte',
    'upgrade2'          => 'aktualisieren',
    'editinstructions'  => 'Bitte die Umfrage-ID und mindestens eine Frage und zwei Antworten eintragen.',
    'pollclosed'        => 'Diese Umfrage ist derzeit geschlossen.',
    'pollhidden'        => 'Die Umfrage-Ergebnisse stehen erst nach dem schließen der Umfrage zur Verfügung.',
    'start_poll'        => 'Umfrage starten',
    'deny_msg' => 'Der Zugang zu dieser Umfrage wird verweigert. Entweder wurde die Umfrage verschoben, entfernt, oder Du verfügst nicht über ausreichende Berechtigungen.',
    'login_required'    => '<a href="'.$_CONF['site_url'].'/users.php" rel="nofollow">Anmelden</a> um abzustimmen',
    'username'          => 'Benutzername',
    'ipaddress'         => 'IP-Adresse',
    'date_voted'        => 'Abgestimmt am',
    'description'       => 'Beschreibung',
    'general'           => 'Allgemein',
    'poll_questions'    => 'Umfrage Fragen',
    'permissions'       => 'Berechtigungen',
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Kommentaranzeige',
    2 => 'Bitte eine Kategorie und mindestens eine Frage und eine Antwort für die Frage eintragen.',
    3 => 'Umfrage erstellt',
    4 => "Umfrage %s gespeichert",
    5 => 'Umfrage-Editor',
    6 => 'Umfrage-ID',
    7 => '(keine Leerzeichen benutzen)',
    8 => 'Erscheint im Umfrageblock',
    9 => 'Kategorie',
    10 => 'Antworten / Stimmen / Bemerkungen',
    11 => "Es trat ein Fehler auf beim auslesen der Antworten für die Umfrage %s",
    12 => "Es trat ein Fehler auf beim auslesen der Fragen für die Umfrage %s",
    13 => 'Umfrage erstellen',
    14 => 'Speichern',
    15 => 'Abbrechen',
    16 => 'Löschen',
    17 => 'Bitte Umfrage-ID eingeben',
    18 => 'Liste der Umfragen',
    19 => '<ul><li>Um eine Umfrage zu bearbeiten oder zu löschen, auf das Bearbeiten-Icon klicken.</li><li>Um eine neue Umfrage zu eröffnen, bitte auf "Neu anlegen" klicken.</li></ul>',
    20 => 'Umfrage-Ende',
    21 => 'Kein Zugang',
    22 => "Du versuchst auf eine Umfrage zuzugreifen, für die Du keine Berechtigung hast. Dieser Versuch wurde aufgezeichnet. Bitte gehe zurück zur <a href=\"{$_CONF['site_admin_url']}/poll.php\">Umfrage-Verwaltung</a>.",
    23 => 'Neue Umfrage',
    24 => 'Kommandozentrale',
    25 => 'Ja',
    26 => 'Nein',
    27 => 'Bearbeiten',
    28 => 'Senden',
    29 => 'Suchen',
    30 => 'Ergebnisse eingrenzen',
    31 => 'Frage',
    32 => 'Um diese Frage aus der Umfrage zu entfernen, den Fragetext löschen.',
    33 => 'Umfrage läuft',
    34 => 'Umfrage-Kategorie:',
    35 => 'Diese Umfrage hat noch ',
    36 => 'Fragen.',
    37 => 'Ergebnisse ausblenden wenn Umfrage läuft',
    38 => 'Während diese Umfrage läuft, können nur der Eigentümer & Root die Ergebnisse sehen.',
    39 => 'Die Kategorie wird nur angezeigt, wenn sie mehr als eine Frage enthält.',
    40 => 'Alle Antworten zu dieser Umfrage ansehen',
    41 => 'Möchtest du diese Umfrage wirklich löschen?',
    42 => 'Bist Du absolut sicher, dass Sie diese Umfrage löschen möchtest? Alle Fragen, Antworten und Kommentare, die mit dieser Umfrage verknüpft sind, werden dauerhaft gelöscht.',
    43 => 'Anmelden um zu stimmen.',
);

$LANG_PO_AUTOTAG = array(
    'desc_poll'                 => 'Link: zu einer Umfrage dieser Seite. (Standart link_text: Umfrage-Titel). Anwendung: [poll:<i>poll_id</i> {link_text}]',
    'desc_poll_result'          => 'HTML: zeigt die Ergebnisse einer Umfrage dieser Seite. Anwendung: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote'            => 'HTML: zeigt einen Abstimmungs-Block für eine Umfrage. Anwendung: [poll_vote:<i>poll_id</i>]',
);

$PLG_polls_MESSAGE19 = 'Umfrage wurde erfolgreich gespeichert.';
$PLG_polls_MESSAGE20 = 'Umfrage wurde erfolgreich gelöscht.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Umfrage-Plugin Aktualisierung: Diese Version kann nicht automatisch aktualisiert werden.';
$PLG_polls_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Umfragen',
    'title' => 'Umfrage-Konfiguration'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'Anmelden für Einsicht',
    'hidepollsmenu' => 'Menüeintrag ausblenden',
    'maxquestions' => 'Max. Fragen pro Umfrage',
    'maxanswers' => 'Max. Antworten pro Frage',
    'answerorder' => 'Ergebnisse sortieren ...',
    'pollcookietime' => 'Umfrage-Wartezeit in Sek.',
    'polladdresstime' => 'IP-Wartezeit in Sek. ',
    'delete_polls' => 'Umfragen mit Benutzer löschen?',
    'aftersave' => 'Nach speichern der Umfrage',
    'default_permissions' => 'Standardeinstellungen Umfragen',
    'displayblocks' => 'Anzeige glFusion Blöcke',
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'Allgemeine-Einstellungen',
    'fs_permissions' => 'Standardrechte-Umfragen'
);

$LANG_configSelect['polls'] = array(
    0 => array(1=>'Ja', 0=>'Nein'),
    1 => array(true=>'Nein', false=>'Ja'),
    2 => array('submitorder'=>'Wie eingesandt', 'voteorder'=>'Nach Abstimmung'),
    9 => array('item'=>'Zur Umfrage weiterleiten', 'list'=>'Admin Liste anzeigen', 'plugin'=>'Öffentliche Liste anzeigen', 'home'=>'Startseite anzeigen', 'admin'=>'Kommandozentrale'),
    12 => array(0=>'Kein Zugang', 2=>'Nur lesen', 3=>'Lesen-Schreiben'),
    13 => array(0=>'Linke Blöcke', 1=>'Rechte Blöcke', 2=>'Linke & Rechte Blöcke', 3=>'Keine Blöcke')
);

?>
