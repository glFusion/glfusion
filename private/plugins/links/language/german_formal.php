<?php
###############################################################################
# german_formal.php
#
# This is the German language file for the glFusion Links Plugin,
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
    die ('This file can not be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_LINKS = array(
    10 => 'Einsendungen',
    14 => 'Links',
    84 => 'Links',
    88 => 'Keine neuen Links',
    114 => 'Links',
    116 => 'Link hinzuf�gen',
    117 => 'Fehlerhaften Link melden',
    118 => 'Fehlerhafte Links',
    119 => 'Der folgende Link wurde als fehlerhaft gemeldet: ',
    120 => 'Um den Link zu editieren, bitte hier klicken: ',
    121 => 'Fehlerhafte Link wurde gemeldet von: ',
    122 => 'Vielen Dank f�r Ihren Hinweis. Der Administrator wird das Problem so schnell wie m�glich beheben',
    123 => 'Danke',
    124 => 'Los',
    125 => 'Kategorien',
    126 => 'Sie sind hier:',
    'root' => 'Root',
    'error_header' => 'Link-Einsendung Fehler',
    'verification_failed' => 'Die angegebene URL scheint keine g�ltige URL zu sein',
    'category_not_found' => 'Die angegebene Kategorie scheint fehlerhaft zu sein'
);

###############################################################################
# for stats

$LANG_LINKS_STATS = array(
    'links' => 'Anzahl der Links (Klicks)',
    'stats_headline' => 'Top 10 der Links',
    'stats_page_title' => 'Links',
    'stats_hits' => 'Aufrufe',
    'stats_no_hits' => 'Es wurden noch keine Links eingetragen.'
);

###############################################################################
# for the search

$LANG_LINKS_SEARCH = array(
    'results' => 'Ergebnisse: Links',
    'title' => 'Titel',
    'date' => 'Hinzugef�gt',
    'author' => 'Eingesendet von',
    'hits' => 'Aufrufe'
);

###############################################################################
# for the submission form

$LANG_LINKS_SUBMIT = array(
    1 => 'Einen Link einsenden',
    2 => 'Link',
    3 => 'Kategorie',
    4 => 'Andere',
    5 => 'oder neue Kategorie',
    6 => 'Fehler: Kategorie fehlt',
    7 => 'Wenn Sie "Andere" ausw�hlen, geben Sie bitte auch eine neue Kategorie ein',
    8 => 'Titel',
    9 => 'URL',
    10 => 'Kategorie',
    11 => 'Link-Einsendungen',
    12 => 'Eingereicht von'
);

###############################################################################
# autotag description

$LANG_LI_AUTOTAG = array(
    'desc_link'                 => 'Link: zur Link-Detail-Seite. (Standart link_text: Link-Name). Anwendung: [link:<i>link_id</i> {link_text}]',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Danke f�r Ihren Beitrag zu {$_CONF['site_name']}. Ihr Link wurde an unser Team weitergeleitet. Wenn er akzeptiert wird, wird er bald unter den <a href=\"{$_CONF['site_url']}/links/index.php\">Links</a> aufgelistet werden.";
$PLG_links_MESSAGE2 = 'Ihr Link wurde erfolgreich gespeichert.';
$PLG_links_MESSAGE3 = 'Der Link wurde erfolgreich gel�scht.';
$PLG_links_MESSAGE4 = "Danke f�r Ihren Link. Sie finden ihn nun unter den <a href=\"{$_CONF['site_url']}/links/index.php\">Links</a>.";
$PLG_links_MESSAGE5 = 'Keine ausreichenden Rechte, diese Kategorie anzusehen.';
$PLG_links_MESSAGE6 = 'Keine ausreichenden Rechte, diese Kategorie zu editieren.';
$PLG_links_MESSAGE7 = 'Bitte geben Sie den Namen der Kategorie und die Beschreibung ein.';
$PLG_links_MESSAGE10 = 'Die Kategorie wurde erfolgreich gespeichert.';
$PLG_links_MESSAGE11 = 'ID nicht "site" oder "user" nennen - dies sind reservierte Worte zum internen Gebrauch.';
$PLG_links_MESSAGE12 = 'Sie versuchen eine Oberkategorie zur Unterkategorie seiner eigenen Unterkategorie zu machen. Dies w�rde eine verwaiste Kategorie produzieren. Bitte erst die Unterkategorie einen Level h�her verschieben.';
$PLG_links_MESSAGE13 = 'Die Kategorie wurde erfolgreich gel�scht.';
$PLG_links_MESSAGE14 = 'Die Kategorie enth�lt Links und / oder Kategorien. Bitte diese erst entfernen.';
$PLG_links_MESSAGE15 = 'Keine ausreichenden Rechte, diese Kategorie zu l�schen.';
$PLG_links_MESSAGE16 = 'So eine Kategorie existiert nicht.';
$PLG_links_MESSAGE17 = 'Diese Kategorie-ID existiert schon.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Link-Plugin Aktualisierung: Diese Version kann nicht automatisch aktualisiert werden.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/plugins/links/index.php

$LANG_LINKS_ADMIN = array(
    1 => 'Link-Editor',
    2 => 'Link-ID',
    3 => 'Titel',
    4 => 'URL',
    5 => 'Kategorie',
    6 => '(mit http://)',
    7 => 'Andere',
    8 => 'Angeklickt',
    9 => 'Beschreibung',
    10 => 'Einen Titel, eine URL und eine Beschreibung f�r den Link angeben.',
    11 => 'Link-Verwaltung',
    12 => '<ul><li>Auf das Bearbeiten-Icon klicken, um einen Link zu bearbeiten oder zu l�schen.</li><li>Mit "Neu anlegen" kann ein neuer Link angelegt werden.</li><li>Um Kategorien zu bearbeiten oder neu anzulegen, w�hle "Kategorie-Liste"</li></ul>',
    14 => 'Kategorie',
    16 => 'Zugriff verweigert',
    17 => "Sie versuchen, auf einen Link zuzugreifen, f�r den Sie keine Rechte haben. Dieser Versuch wurde protokolliert. Bitte gehen Sie zur�ck zur <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">Link-Verwaltung</a>.",
    20 => 'Andere Kategorie bitte eingeben',
    21 => 'Speichern',
    22 => 'Abbrechen',
    23 => 'L�schen',
    24 => 'Link nicht gefunden',
    25 => 'Der zu bearbeitende Link konnte nicht gefunden werden.',
    26 => 'Links �berpr�fen',
    27 => 'HTML Status',
    28 => 'Kategorie bearbeiten',
    29 => '<ul><li>Hier k�nnen Sie Ihre Links bearbeiten oder erstellen.</li><li>Es m�ssen alle Felder eingegeben werden.</li></ul>',
    30 => 'Kategorie',
    31 => 'Beschreibung',
    32 => 'Kategorie-ID',
    33 => 'Kategorie',
    34 => '�bergeordnete Kategorie',
    35 => 'Alle',
    40 => 'Dies Kategorie bearbeiten',
    41 => 'Unterkategorie einrichten',
    42 => 'Diese Kategorie l�schen',
    43 => 'Kategorie der Seite',
    44 => 'Unterkategorie&nbsp;hinzuf�gen',
    46 => 'Benutzer %s hat unrechtm��ig versucht die Kategorie %s zu l�schen.',
    50 => 'Kategorie-Liste',
    51 => 'Neue anlegen',
    52 => 'Neue Kategorie',
    53 => 'Link-Liste',
    54 => 'Kategorie-Verwaltung',
    55 => 'Die Kategorien unten bearbeiten. Bitte beachten, Kategorie k�nnen nicht gel�scht werden, die andere Kategorien oder Links enthalten. - Sie m�ssen erst gel�scht oder verschoben werden.',
    56 => 'Kategorie-Editor',
    57 => 'Noch nicht �berpr�ft',
    58 => 'Jetzt �berpr�fen',
    59 => '<p>Um alle aufgef�hrten Links zu �berpr�fen, einfach "Jetzt �berpr�fen" unten anklicken. Es kann etwas dauern, abh�ngig davon, wie viele Links aufgef�hrt sind.</p>',
    60 => 'Benutzer %s hat unrechtm��ig versucht, die Kategorie %s zu editieren.',
    61 => 'Besitzer',
    62 => 'Letzte Aktualisierung',
    63 => 'Soll dieser Link wirklich gel�scht werden?',
    64 => 'Soll diese Kategorie wirklich gel�scht werden?',
    65 => 'Link �berpr�fen und freigeben',
    66 => 'Hier k�nnen Sie Ihre Links erstellen oder bearbeiten.',
    67 => 'Hier k�nnen Sie Ihre Link-Kategorien erstellen oder bearbeiten.'
);


$LANG_LINKS_STATUS = array(
    100 => 'Fortsetzen',
    101 => 'Wechsel Protokolle',
    200 => 'OK',
    201 => 'Erstellt',
    202 => 'Akzeptiert',
    203 => 'Unma�gebliche Information',
    204 => 'Kein Inhalt',
    205 => 'Inhalt zur�cksetzten',
    206 => 'Teilweiser Inhalt',
    300 => 'Mehrfache M�glichkeiten',
    301 => 'Permanent verschoben',
    302 => 'Gefunden',
    303 => 'Siehe andere',
    304 => 'Nicht ver�ndert',
    305 => 'Proxy verwenden',
    307 => 'Vor�bergehend umleiten',
    400 => 'Ung�ltige Anfrage',
    401 => 'Nicht autorisiert',
    402 => 'Zahlung erbeten',
    403 => 'Kein Zugang',
    404 => 'Nicht gefunden',
    405 => 'Methode nicht erlaubt',
    406 => 'Nicht akzeptabel',
    407 => 'Proxy-Authentifizierung erforderlich',
    408 => 'Anfragedauer �berschritten',
    409 => 'Konflikt',
    410 => 'Verschwunden',
    411 => 'L�nge erforderlich',
    412 => 'Voraussetzung nicht ref�llt',
    413 => 'Anfrage-Objekt zu gro�',
    414 => 'Anfrage-URI zu lang',
    415 => 'Medientyp nicht unterst�tzt',
    416 => 'Angeforderte Operation nicht ausf�hrbar',
    417 => 'Erwartung fehlgeschlagen',
    500 => 'Interner Server Fehler',
    501 => 'Nicht Implementiert',
    502 => 'Ung�ltige Verbindung',
    503 => 'Service nicht verf�gbar',
    504 => 'Verbindung Zeit�berschreitung',
    505 => 'HTTP Version wird nicht unterst�tzt',
    999 => 'Zeit�berschreitung der Verbindung'
);


// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Links',
    'title' => 'Links-Einstellungen'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => 'Anmelden f�r Lesen',
    'linksubmission' => 'Links moderieren',
    'newlinksinterval' => 'Zeitabstand neue Links',
    'hidenewlinks' => 'In "Was ist neu" ausblenden',
    'hidelinksmenu' => 'Men�eintrag ausblenden',
    'linkcols' => 'Kategorien pro Spalte',
    'linksperpage' => 'Links pro Seite',
    'show_top10' => 'Top 10 Links zeigen',
    'notification' => 'E-Mail Benachrichtigung',
    'delete_links' => 'Links mit Benutzer l�schen',
    'aftersave' => 'Nach Abspeichern des Links',
    'show_category_descriptions' => 'Kategoriebeschreibung anzeigen?',
    'root' => 'ID der Oberkategorie',
    'default_permissions' => 'Standardberechtigungen - Links',
    'target_blank' => 'Links in neuen Fenster �ffnen',
    'displayblocks' => 'Anzeige glFusion Bl�cke'
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['links'] = array(
    'fs_public' => '�ffentliche-Einstellungen',
    'fs_admin' => 'Admin-Einstellungen',
    'fs_permissions' => 'Standard-Berechtigungen'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['links'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => true, 'Nein' => false),
    9 => array('Weiter zur Seite' => 'item', 'Admin Liste anzeigen' => 'list', 'Link-Liste anzeigen' => 'plugin', 'Startseite anzeigen' => 'home', 'Kommandozentrale' => 'admin'),
    12 => array('Kein Zugang' => 0, 'Nur lesen' => 2, 'Lesen-Schreiben' => 3),
    13 => array('Linke Bl�cke' => 0, 'Rechte Bl�cke' => 1, 'Linke & Rechte Bl�cke' => 2, 'Keine' => 3)
);

?>