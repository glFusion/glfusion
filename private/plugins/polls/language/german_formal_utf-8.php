<?php
###############################################################################
# german_formal_utf-8.php
#
# This is the German language file for the glFusion Polls Plugin,
# addressing the user as "Sie" (formal German).
#
# Authors: Dirk Haun <dirk AT haun-online DOT de>
#          Markus Wollschläger
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
    'pastpolls' => 'Ältere Umfragen',
    'savedvotetitle' => 'Stimme gespeichert',
    'savedvotemsg' => 'Stimme wurde für die Umfrage gespeichert: ',
    'pollstitle' => 'Umfragen im System',
    'polltopics' => 'Andere Umfragen',
    'stats_top10' => 'Top Ten der Umfragen',
    'stats_topics' => 'Umfragekategorie',
    'stats_votes' => 'Stimmen',
    'stats_none' => 'Es gibt keine Umfragen oder es wurden keine Stimmen abgegeben.',
    'stats_summary' => 'Anzahl Umfragen (Stimmen)',
    'open_poll' => 'Abstimmen möglich',
    'answer_all' => 'Bitte alle übrigen Fragen beantworten',
    'not_saved' => 'Ergebnis nicht gespeichert',
    'upgrade1' => 'Neue Version des Umfrage-Plugins installiert. Bitte',
    'upgrade2' => 'upgraden',
    'editinstructions' => 'Bitte für die Umfrage-ID mindestens eine Frage und zwei Antworten eintragen.',
    'pollclosed' => 'Diese Umfrage ist für die Abstimmung geschlossen.',
    'pollhidden' => 'Sie haben bereits abgestimmt. Diese Umfrage-Ergebnisse werden nur angezeigt, wenn eine Abstimmung abgegeben werden.',
    'start_poll' => 'Umfrage starten',
    'deny_msg' => 'Der Zugang zu dieser Umfrage wird verweigert. Entweder wurde die Umfrage verschoben, entfernt, oder Sie verfügen nicht über ausreichende Berechtigungen.',
    'login_required' => "<a href=\"{$_CONF['site_url']}/users.php\" rel=\"nofollow\">Login</a> required to vote"
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Kommentaranzeige',
    2 => 'Bitte eine Kategorie, mindestens eine Frage und eine Antwort für die Frage eintragen.',
    3 => 'Umfrage erstellt',
    4 => 'Umfrage %s gespeichert',
    5 => 'Umfrage bearbeiten',
    6 => 'Umfrage-ID',
    7 => '(keine Leerzeichen benutzen)',
    8 => 'Erscheint im Umfrageblock',
    9 => 'Kategorie',
    10 => 'Antworten / Abstimmungen / Bemerkungen',
    11 => 'Es trat ein Fehler auf beim Holen der Antwortdaten für Umfrage %s',
    12 => 'Es trat ein Fehler auf beim Holen der Fragedaten für Umfrage %s',
    13 => 'Umfrage erstellen',
    14 => 'speichern',
    15 => 'abbrechen',
    16 => 'löschen',
    17 => 'Bitte Umfrage-ID eingeben',
    18 => 'Liste der Umfragen',
    19 => 'Um eine Umfrage zu bearbeiten oder zu löschen, auf das Bearbeiten-Icon klicken.  Um eine neue Umfrage zu eröffnen, bitte auf "Neu anlegen" oben klicken.',
    20 => 'Umfrageende',
    21 => 'Kein Zugang',
    22 => "Sie versuchen auf eine Umfrage zuzugreifen, für die Sie keine Berechtigung haben. Dieser Versuch wurde aufgezeichnet. Bitte <a href=\"{$_CONF['site_admin_url']}/poll.php\">gehen Sie zurück zur Umfrage-Administration</a>.",
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
    38 => 'Während diese Umfrage läuft, können nur der Eigentümer &amp; Root die Ergebnisse sehen.',
    39 => 'Die Kategorie wird nur angezeigt, wenn sie mehr als eine Frage enthält.',
    40 => 'Alle Antworten zu dieser Umfrage ansehen',
    41 => 'Are you sure you want to delete this Poll?',
    42 => 'Are you absolutely sure you want to delete this Poll?  All questions, answers and comments that are associated with this Poll will also be permanently deleted from the database.',
    43 => 'Login Required to Vote'
);

###############################################################################
# autotag descriptions

$LANG_PO_AUTOTAG = array(
    'desc_poll' => 'Link: to a Poll on this site.  link_text defaults to the Poll topic.  usage: [poll:<i>poll_id</i> {link_text}]',
    'desc_poll_result' => 'HTML: renders the results of a Poll on this site.  usage: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote' => 'HTML: renders a voting block for a Poll on this site.  usage: [poll_vote:<i>poll_id</i>]'
);

$PLG_polls_MESSAGE19 = 'Umfrage wurde gespeichert.';
$PLG_polls_MESSAGE20 = 'Umfrage wurde gelöscht.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Plugin-Upgrade nicht unterstützt.';
$PLG_polls_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Umfragen',
    'title' => 'Umfragekonfiguration'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'Zur Einsicht anmelden erforderlich?',
    'hidepollsmenu' => 'Menüeintrag ausblenden?',
    'maxquestions' => 'Max. Fragen pro Umfrage',
    'maxanswers' => 'Max. Möglichkeiten pro Frage',
    'answerorder' => 'Ergebnisse sortieren ...',
    'pollcookietime' => 'Voter Cookie gültig für',
    'polladdresstime' => 'Voter IP-Adresse gültig für',
    'delete_polls' => 'Umfragen mit Benutzer löschen?',
    'aftersave' => 'Nach speichern der Umfrage',
    'default_permissions' => 'Standardeinstellungen Umfragen',
    'displayblocks' => 'Anzeige glFusion Blöcke'
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'Allgemeine Umfrageeinstellungen',
    'fs_permissions' => 'Standardberechtigeungen - Umfragen'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['polls'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => true, 'Nein' => false),
    2 => array('Wie eingesandt' => 'submitorder', 'Nach Abstimmung' => 'voteorder'),
    9 => array('Zur Umfrage weiterleiten' => 'item', 'Admin Liste anzeigen' => 'list', 'Öffentliche Liste anzeigen' => 'plugin', 'Startseite anzeigen' => 'home', 'Kommandozentrale' => 'admin'),
    12 => array('Kein Zugang' => 0, 'Nur lesen' => 2, 'Lesen-Schreiben' => 3),
    13 => array('Linker Block' => 0, 'Rechter Block' => 1, 'Linker & Rechter Block' => 2, 'Keine' => 3)
);

?>