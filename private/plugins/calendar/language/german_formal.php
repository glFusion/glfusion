<?php
###############################################################################
# german_formal.php
#
# This is the formal German language file 
# for the glFusion Calendar Plugin, addressing the user as "Sie"
#
# Authors: Dirk Haun <dirk AT haun-online DOT de>
#          Markus Wollschl�ger
# Modifiziert: Oct 2010
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

# index.php
$LANG_CAL_1 = array(
    1 => 'Terminkalender',
    2 => 'Es gibt keine Termine anzuzeigen.',
    3 => 'Wann',
    4 => 'Wo',
    5 => 'Beschreibung',
    6 => 'Termin hinzuf�gen',
    7 => 'Anstehende Termine',
    8 => 'Wenn Sie diesen Termin zu Ihrem Kalender hinzuf�gen, k�nnen Sie sich schneller einen �berblick �ber die Termine verschaffen, die Sie interessieren, indem Sie einfach auf "Mein Kalender" klicken.',
    9 => 'Zu Meinem Kalender hinzuf�gen',
    10 => 'Aus Meinem Kalender entfernen',
    11 => 'Termin wird zum Kalender von %s hinzugef�gt',
    12 => 'Termin',
    13 => 'Beginnt',
    14 => 'Endet',
    15 => 'Zur�ck zum Kalender',
    16 => 'Kalender',
    17 => 'Datum-Beginn',
    18 => 'Datum-Ende',
    19 => 'Kalender-Einsendungen',
    20 => 'Titel',
    21 => 'Datum-Beginn',
    22 => 'URL',
    23 => 'Meine Termine',
    24 => 'Allgemeine Termine',
    25 => 'Es stehen keine Termine an',
    26 => 'Einen Termin einsenden',
    27 => "<ul><li>Wenn Sie einen Termin bei {$_CONF['site_name']} einsenden, wird er in den Kalender aufgenommen, von wo aus ihn andere User in ihren pers�nlichen Kalender �bernehmen k�nnen.</li><li>Dies ist <b>NICHT</b> dazu gedacht, private Termine und Ereignisse wie etwa Geburtstage zu verwalten.</li><li>Wenn Sie einen Termin einreichen, wird er an die Administratoren weitergeleitet und sobald er von diesen akzeptiert wird, wird er im Kalender erscheinen.</li></ul>",
    28 => 'Titel',
    29 => 'Uhrzeit-Ende',
    30 => 'Uhrzeit-Beginn',
    31 => 'Ganzt�giger Termin',
    32 => 'Address-Zeile 1',
    33 => 'Address-Zeile 2',
    34 => 'Stadt',
    35 => 'Bundesland',
    36 => 'Postleitzahl',
    37 => 'Art des Termins',
    38 => 'Termin-Arten �ndern',
    39 => 'Ort',
    40 => 'Termin hinzuf�gen zu',
    41 => '�ffentlicher Kalender',
    42 => 'Pers�nlicher Kalender',
    43 => 'Link',
    44 => 'HTML ist nicht erlaubt',
    45 => 'Absenden',
    46 => 'Anzahl Termine',
    47 => 'Top 10 der Termine',
    48 => 'Angezeigt',
    49 => 'Es wurden noch keine Termine eingetragen.',
    50 => 'Termine',
    51 => 'L�schen',
    52 => 'Eingereicht von',
    53 => 'Kalender-Ansicht'
);

$_LANG_CAL_SEARCH = array(
    'results' => 'Gefundene Termine',
    'title' => 'Titel',
    'date_time' => 'Datum und Uhrzeit',
    'location' => 'Ort',
    'description' => 'Beschreibung'
);

###############################################################################
# calendar.php ($LANG30)

$LANG_CAL_2 = array(
    8 => 'Pers�nlichen Termin eintragen',
    9 => '%s Termin',
    10 => 'Termine am',
    11 => '�ffentlicher Kalender',
    12 => 'Pers�nlicher Kalender',
    25 => 'Zur�ck zu  ',
    26 => 'Ganzt�gig',
    27 => 'Woche',
    28 => 'Pers�nlicher Kalender f�r',
    29 => '�ffentlicher Kalender',
    30 => 'Termin l�schen',
    31 => 'Hinzuf�gen',
    32 => 'Termin',
    33 => 'Datum',
    34 => 'Uhrzeit',
    35 => 'Neuer Termin',
    36 => 'Absenden',
    37 => 'Der pers�nliche Kalender ist auf dieser Seite leider deaktiviert.',
    38 => 'Pers�nliche Termin-Verwaltung',
    39 => 'Tag',
    40 => 'Woche',
    41 => 'Monat',
    42 => '�ffentlichen Termin eintragen',
    43 => 'Termin-Einsendungen'
);

###############################################################################
# admin/plugins/calendar/index.php, formerly admin/event.php ($LANG22)

$LANG_CAL_ADMIN = array(
    1 => 'Termin-Editor',
    2 => 'Fehler',
    3 => 'Titel',
    4 => 'URL',
    5 => 'Datum-Beginn',
    6 => 'Datum-Ende',
    7 => 'Ort',
    8 => 'Beschreibung',
    9 => '(mit http://)',
    10 => 'Es m�ssen mindestens Datum und Uhrzeit, Titel und Beschreibung eingegeben werden!',
    11 => 'Termin-Verwaltung',
    12 => '<ul><li>Auf das Bearbeiten-Icon klicken, um einen Termin zu bearbeiten oder zu l�schen.</li><li>Mit "Neu anlegen" wird ein neuer Termin angelegt.</li><li>Das Kopie-Icon erzeugt eine Kopie eines vorhandenen Termins.</li></ul>',
    13 => 'Autor',
    14 => 'Datum-Beginn',
    15 => 'Datum-Ende',
    16 => '',
    17 => "Sie haben keine Berechtigung auf diesen Termin zuzugreifen. Dieser Zugriffsversuch wurde protokolliert. <a href=\"{$_CONF['site_admin_url']}/plugins/calendar/index.php\">Zur�ck zur Termin-Verwaltung</a>.",
    18 => '',
    19 => '',
    20 => 'Speichern',
    21 => 'Abbruch',
    22 => 'L�schen',
    23 => 'Datum-Beginn ung�ltig!',
    24 => 'Datum-Ende ung�ltig!',
    25 => 'FEHLER: Datum-Ende ist vor Datum-Beginn.',
    26 => 'Alte Eintr�ge l�schen',
    27 => 'Diese Termine sind �lter als ',
    28 => ' Monate.<ul><li>Aktualisieren Sie den Zeitraum, wie gew�nscht, und klicken Sie dann auf "Liste aktualisieren".</li><li>W�hlen Sie einen oder mehrere Termine aus den angezeigten Ergebnissen aus.</li><li>Klicken Sie anschlie�end auf das Symbol L�schen, um diese Termine aus Ihrer Datenbank zu entfernen.</li><li>Nur Termine, die auf dieser Seite angezeigt und ausgew�hlt sind, werden gel�scht.</li></ul>',
    29 => '',
    30 => 'Liste aktualisieren',
    31 => 'Sind Sie sicher, dass Sie alle ausgew�hlten Benutzer dauerhaft l�schen m�chten?',
    32 => 'Alle auflisten',
    33 => 'Keine Termine zum l�schen ausgew�hlt',
    34 => 'Termin ID',
    35 => 'konnte nicht gel�scht werden',
    36 => 'Erfolgreich gel�scht',
    37 => 'Termine �berpr�fen',
    38 => 'Bereinigen',
    39 => 'Termin-Verwaltung',
    40 => 'Termin-Liste',
    41 => 'Hier k�nnen Sie neue Termine erstellen oder vorhandene Eintr�ge bearbeiten oder l�schen.'
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Link: zu einem Kalender-Eintrag; link_text defaults to event title: [calendar:<i>event_id</i> {link_text}]'
);

$LANG_CAL_MESSAGE = array(
    'save' => 'Der Termin wurde erfolgreich gespeichert.',
    'delete' => 'Der Termin wurde erfolgreich gel�scht.',
    'private' => 'Der Termin wurde erfolgreich in Ihren Kalender eingetragen.',
    'login' => 'Sie m�ssen angemeldet sein, um auf Ihren pers�nlichen Kalender zugreifen zu k�nnen.',
    'removed' => 'Der Termin wurde erfolgreich aus Ihrem pers�nlichen Kalender entfernt.',
    'noprivate' => 'Pers�nliche Kalender sind auf dieser Site nicht verf�gbar.',
    'unauth' => 'Sie haben keinen Zugriff auf die Termin-Verwaltung. Alle Versuche, auf Bereiche ohne entsprechende Berechtigung zuzugreifen, werden protokolliert.',
    'delete_confirm' => 'Sind Sie sicher. dass Sie diesen Termin l�schen wollen?'
);

$PLG_calendar_MESSAGE4 = "{$_CONF['site_name']} bedankt sich f�r Ihre Einsendung. Ihr Termin wurde erfolgreich an unser Team weitergeleitet. Sobald er akzeptiert wird, wird er im <a href=\"{$_CONF['site_url']}/calendar/index.php\">Kalender</a> erscheinen.";
$PLG_calendar_MESSAGE17 = 'Ihr Termin wurde erfolgreich gespeichert.';
$PLG_calendar_MESSAGE18 = 'Der Termin wurde erfolgreich gel�scht.';
$PLG_calendar_MESSAGE24 = 'Der Termin wurde erfolgreich in Ihren Kalender eingetragen.';
$PLG_calendar_MESSAGE26 = 'Der Termin wurde erfolgreich gel�scht.';

// Messages for the plugin upgrade
$PLG_calendar_MESSAGE3001 = 'Plugin-Aktualisierung wird nicht unterst�tzt.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Kalender',
    'title' => 'Kalender-Einstellungen'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => 'Zur Einsicht anmelden n�tig?',
    'hidecalendarmenu' => 'Men�eintrag ausblenden?',
    'personalcalendars' => 'Pers�nliche Kalender?',
    'eventsubmission' => 'Eintr�ge pr�fen?',
    'showupcomingevents' => 'Zuk�nftige Termine anzeigen?',
    'upcomingeventsrange' => 'Zeitraum zuk�nftige Termine',
    'event_types' => 'Art der Termine',
    'hour_mode' => 'Stunden-Modus',
    'notification' => 'Benachrichtigungs E-Mail?',
    'delete_event' => 'Termine mit Benutzer l�schen?',
    'aftersave' => 'Nach speichern des Termins',
    'default_permissions' => 'Standard-Terminrechte',
    'only_admin_submit' => 'Nur Admins das Eintragen erlauben',
    'displayblocks' => 'glFusion Bl�cke anzeigen'
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'Allgemeine-Einstellungen',
    'fs_permissions' => 'Standard-Berechtigungen'
);

// Note: entries 0, 1, 6, 9, 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['calendar'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => true, 'Nein' => false),
    6 => array('12' => 12, '24' => 24),
    9 => array('Zum Termin weiterleiten' => 'item', 'Admin Liste anzeigen' => 'list', 'Kalender anzeigen' => 'plugin', 'Startseite anzeigen' => 'home', 'Kommandozentrale' => 'admin'),
    12 => array('Kein Zugang' => 0, 'Nur lesen' => 2, 'Lesen-Schreiben' => 3),
    13 => array('Linke Bl�cke' => 0, 'Rechte Bl�cke' => 1, 'Linke & Rechte Bl�cke' => 2, 'Keine' => 3)
);

?>