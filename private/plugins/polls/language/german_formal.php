<?php
###############################################################################
# german_formal.php
#
# This is the German language file for the glFusion Polls Plugin,
# addressing the user as "Sie" (formal German).
#
# Authors: Dirk Haun <dirk AT haun-online DOT de>
#          Markus Wollschl�ger
# Modifiziert: August 09 Tony Kluever
# Siegfried Gutschi (November 2016) <sigi AT modellbaukalender DOT info>
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

$LANG_POLLS = array(
    'polls' => 'Umfragen',
    'results' => 'Ergebnisse',
    'pollresults' => 'Umfrage-Ergebnisse',
    'votes' => 'Stimmen',
    'vote' => 'Abstimmen',
    'pastpolls' => '�ltere Umfragen',
    'savedvotetitle' => 'Stimme gespeichert',
    'savedvotemsg' => 'Ihre Stimme wurde gespeichert f�r die Umfrage',
    'pollstitle' => 'Umfragen im System',
    'polltopics' => 'Weitere Umfragen',
    'stats_top10' => 'Top 10 der Umfragen',
    'stats_topics' => 'Umfrage-Kategorie',
    'stats_votes' => 'Stimmen',
    'stats_none' => 'Es wurden noch keine Umfragen erstellt.',
    'stats_summary' => 'Anzahl Umfragen (Stimmen)',
    'open_poll' => 'Abstimmen m�glich',
    'answer_all' => 'Bitte alle �brigen Fragen beantworten',
    'not_saved' => 'Ergebnis nicht gespeichert',
    'upgrade1' => 'Neue Version des Umfrage-Plugins installiert. Bitte',
    'upgrade2' => 'aktualisieren',
    'editinstructions' => 'Bitte die Umfrage-ID und mindestens eine Frage und zwei Antworten eintragen.',
    'pollclosed' => 'Diese Umfrage ist derzeit geschlossen.',
    'pollhidden' => 'Die Umfrage-Ergebnisse stehen erst nach dem schlie�en der Umfrage zur Verf�gung.',
    'start_poll' => 'Umfrage starten',
    'deny_msg' => 'Der Zugang zu dieser Umfrage wird verweigert. Entweder wurde die Umfrage verschoben, entfernt, oder Sie verf�gen nicht �ber ausreichende Berechtigungen.',
    'login_required' => "<a href=\"{$_CONF['site_url']}/users.php\" rel=\"nofollow\">Anmelden</a> um zu stimmen.",
    'username' => 'Benutzername',
    'ipaddress' => 'IP-Adresse',
    'date_voted' => 'Abgestimmt am',
    'description' => 'Beschreibung',
    'general' => 'Allgemein',
    'poll_questions' => 'Umfrage Fragen',
    'permissions' => 'Berechtigungen'
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Kommentaranzeige',
    2 => 'Bitte eine Kategorie und mindestens eine Frage und eine Antwort f�r die Frage eintragen.',
    3 => 'Umfrage erstellt',
    4 => 'Umfrage %s gespeichert',
    5 => 'Umfrage-Editor',
    6 => 'Umfrage-ID',
    7 => '(keine Leerzeichen benutzen)',
    8 => 'Erscheint im Umfrageblock',
    9 => 'Kategorie',
    10 => 'Antworten / Stimmen / Bemerkungen',
    11 => 'Es trat ein Fehler auf beim auslesen der Antworten f�r die Umfrage %s',
    12 => 'Es trat ein Fehler auf beim auslesen der Fragen f�r die Umfrage %s',
    13 => 'Umfrage erstellen',
    14 => 'Speichern',
    15 => 'Abbrechen',
    16 => 'L�schen',
    17 => 'Bitte Umfrage-ID eingeben',
    18 => 'Liste der Umfragen',
    19 => '<ul><li>Um eine Umfrage zu bearbeiten oder zu l�schen, auf das Bearbeiten-Icon klicken.</li><li>Um eine neue Umfrage zu er�ffnen, bitte auf "Neu anlegen" klicken.</li></ul>',
    20 => 'Umfrage-Ende',
    21 => 'Kein Zugang',
    22 => "Sie versuchen auf eine Umfrage zuzugreifen, f�r die Sie keine Berechtigung haben. Dieser Versuch wurde aufgezeichnet. Bitte gehen Sie zur�ck zur <a href=\"{$_CONF['site_admin_url']}/poll.php\">Umfrage-Verwaltung</a>.",
    23 => 'Neue Umfrage',
    24 => 'Kommandozentrale',
    25 => 'Ja',
    26 => 'Nein',
    27 => 'Bearbeiten',
    28 => 'Senden',
    29 => 'Suchen',
    30 => 'Ergebnisse eingrenzen',
    31 => 'Frage',
    32 => 'Um diese Frage aus der Umfrage zu entfernen, den Fragetext l�schen.',
    33 => 'Umfrage l�uft',
    34 => 'Umfrage-Kategorie:',
    35 => 'Diese Umfrage hat noch ',
    36 => 'Fragen.',
    37 => 'Ergebnisse ausblenden wenn Umfrage l�uft',
    38 => 'W�hrend diese Umfrage l�uft, k�nnen nur der Eigent�mer &amp; Root die Ergebnisse sehen.',
    39 => 'Die Kategorie wird nur angezeigt, wenn sie mehr als eine Frage enth�lt.',
    40 => 'Alle Antworten zu dieser Umfrage ansehen',
    41 => 'M�chtest du diese Umfrage wirklich l�schen?',
    42 => 'Sind Sie absolut sicher, dass Sie diese Umfrage l�schen m�chten? Alle Fragen, Antworten und Kommentare, die mit dieser Umfrage verkn�pft sind, werden dauerhaft gel�scht.',
    43 => 'Anmelden um zu stimmen.'
);

###############################################################################
# autotag descriptions

$LANG_PO_AUTOTAG = array(
    'desc_poll' => 'Link: zu einer Umfrage dieser Seite. (Standart link_text: Umfrage-Titel). Anwendung: [poll:<i>poll_id</i> {link_text}]',
    'desc_poll_result' => 'HTML: zeigt die Ergebnisse einer Umfrage dieser Seite. Anwendung: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote' => 'HTML: zeigt einen Abstimmungs-Block f�r eine Umfrage. Anwendung: [poll_vote:<i>poll_id</i>]'
);

$PLG_polls_MESSAGE19 = 'Umfrage wurde erfolgreich gespeichert.';
$PLG_polls_MESSAGE20 = 'Umfrage wurde erfolgreich gel�scht.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Umfrage-Plugin Aktualisierung: Diese Version kann nicht automatisch aktualisiert werden.';
$PLG_polls_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Umfragen',
    'title' => 'Umfrage-Konfiguration'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'Anmelden f�r Einsicht',
    'hidepollsmenu' => 'Men�eintrag ausblenden',
    'maxquestions' => 'Max. Fragen pro Umfrage',
    'maxanswers' => 'Max. Antworten pro Frage',
    'answerorder' => 'Ergebnisse sortieren ...',
    'pollcookietime' => 'Umfrage-Wartezeit in Sek.',
    'polladdresstime' => 'IP-Wartezeit in Sek. ',
    'delete_polls' => 'Umfragen mit Benutzer l�schen?',
    'aftersave' => 'Nach speichern der Umfrage',
    'default_permissions' => 'Standardeinstellungen Umfragen',
    'displayblocks' => 'Anzeige glFusion Bl�cke'
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'Allgemeine-Einstellungen',
    'fs_permissions' => 'Standardrechte-Umfragen'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['polls'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => true, 'Nein' => false),
    2 => array('Wie eingesandt' => 'submitorder', 'Nach Abstimmung' => 'voteorder'),
    9 => array('Zur Umfrage weiterleiten' => 'item', 'Admin Liste anzeigen' => 'list', '�ffentliche Liste anzeigen' => 'plugin', 'Startseite anzeigen' => 'home', 'Kommandozentrale' => 'admin'),
    12 => array('Kein Zugang' => 0, 'Nur lesen' => 2, 'Lesen-Schreiben' => 3),
    13 => array('Linke Bl�cke' => 0, 'Rechte Bl�cke' => 1, 'Linke & Rechte Bl�cke' => 2, 'Keine Bl�cke' => 3)
);

?>