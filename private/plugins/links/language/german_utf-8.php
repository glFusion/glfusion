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
    10 => 'Einsendungen',
    14 => 'Links',
    84 => 'Links',
    88 => 'Keine neuen Links',
    114 => 'Links',
    116 => 'Link hinzufügen',
    117 => 'Fehlerhaften Link melden',
    118 => 'Fehlerhafte Links',
    119 => 'Der folgende Link wurde als fehlerhaft gemeldet: ',
    120 => 'Um den Link zu editieren, bitte hier klicken: ',
    121 => 'Fehlerhafte Link wurde gemeldet von: ',
    122 => 'Vielen Dank für Deinen Hinweis. Der Administrator wird das Problem so schnell wie möglich beheben',
    123 => 'Danke',
    124 => 'Los',
    125 => 'Kategorien',
    126 => 'Du bist hier:',
    'root' => 'Stammverzeichnis',
    'error_header'  => 'Link-Einsendung Fehler',
    'verification_failed' => 'Die angegebene URL scheint keine gültige URL zu sein',
    'category_not_found' => 'Die angegebene Kategorie scheint fehlerhaft zu sein',
    'no_links'  => 'Es wurden keine Links eingegeben.',
);

###############################################################################
# for stats
/**
* the link plugin's lang stats array
*
* @global array $LANG_LINKS_STATS
*/
$LANG_LINKS_STATS = array(
    'links' => 'Anzahl der Links (Klicks)',
    'stats_headline' => 'Top 10 der Links',
    'stats_page_title' => 'Links',
    'stats_hits' => 'Aufrufe',
    'stats_no_hits' => 'Es wurden noch keine Links eingetragen.',
);

###############################################################################
# for the search
/**
* the link plugin's lang search array
*
* @global array $LANG_LINKS_SEARCH
*/
$LANG_LINKS_SEARCH = array(
 'results' => 'Ergebnisse: Links',
 'title' => 'Titel',
 'date' => 'Hinzugefügt',
 'author' => 'Eingesendet von',
 'hits' => 'Aufrufe'
);

###############################################################################
# for the submission form
/**
* the link plugin's lang submit form array
*
* @global array $LANG_LINKS_SUBMIT
*/
$LANG_LINKS_SUBMIT = array(
    1 => 'Einen Link einsenden',
    2 => 'Link',
    3 => 'Kategorie',
    4 => 'Andere',
    5 => 'oder neue Kategorie',
    6 => 'Fehler: Kategorie fehlt',
    7 => 'Wenn Du "Andere" auswählst, gib bitte auch eine neue Kategorie ein',
    8 => 'Titel',
    9 => 'URL',
    10 => 'Kategorie',
    11 => 'Link-Einsendungen',
    12 => 'Eingereicht von',
);

###############################################################################
# autotag description

$LANG_LI_AUTOTAG = array(
    'desc_link'                 => 'Link: zur Link-Detail-Seite. (Standart link_text: Link-Name). Anwendung: [link:<i>link_id</i> {link_text}]',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Danke für Deinen Beitrag zu {$_CONF['site_name']}. Dein Link wurde an unser Team weitergeleitet. Wenn er akzeptiert wird, wird er bald unter den <a href=\"{$_CONF['site_url']}/links/index.php\">Links</a> aufgelistet werden.";
$PLG_links_MESSAGE2 = 'Dein Link wurde erfolgreich gespeichert.';
$PLG_links_MESSAGE3 = 'Der Link wurde erfolgreich gelöscht.';
$PLG_links_MESSAGE4 = "Danke für Deinen Link. Du findest ihn nun unter den <a href=\"{$_CONF['site_url']}/links/index.php\">Links</a>.";
$PLG_links_MESSAGE5 = "Keine ausreichenden Rechte, diese Kategorie anzusehen.";
$PLG_links_MESSAGE6 = 'Keine ausreichenden Rechte, diese Kategorie zu bearbeiten.';
$PLG_links_MESSAGE7 = 'Bitte gib den Namen der Kategorie und die Beschreibung ein.';

$PLG_links_MESSAGE10 = 'Die Kategorie wurde erfolgreich gespeichert.';
$PLG_links_MESSAGE11 = 'ID nicht "site" oder "user" nennen - dies sind reservierte Worte zum internen Gebrauch.';
$PLG_links_MESSAGE12 = 'Du versuchst eine Oberkategorie zur Unterkategorie seiner eigenen Unterkategorie zu machen. Dies würde eine verwaiste Kategorie produzieren. Bitte erst die Unterkategorie einen Level höher verschieben.';
$PLG_links_MESSAGE13 = 'Die Kategorie wurde erfolgreich gelöscht.';
$PLG_links_MESSAGE14 = 'Die Kategorie enthält Links und / oder Kategorien. Bitte diese erst entfernen.';
$PLG_links_MESSAGE15 = 'Keine ausreichenden Rechte, diese Kategorie zu löschen.';
$PLG_links_MESSAGE16 = 'So eine Kategorie existiert nicht.';
$PLG_links_MESSAGE17 = 'Diese Kategorie-ID existiert schon.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Link-Plugin Aktualisierung: Diese Version kann nicht automatisch aktualisiert werden.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/link.php
/**
* the link plugin's lang admin array
*
* @global array $LANG_LINKS_ADMIN
*/
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
    10 => 'Einen Titel, eine URL und eine Beschreibung für den Link angeben.',
    11 => 'Link-Verwaltung',
    12 => '<ul><li>Auf das Bearbeiten-Icon klicken, um einen Link zu bearbeiten oder zu löschen.</li><li>Mit "Neu anlegen" kann ein neuer Link angelegt werden.</li><li>Um Kategorien zu bearbeiten oder neu anzulegen, wähle "Kategorie-Liste"</li></ul>',
    14 => 'Kategorie',
    16 => 'Zugriff verweigert',
    17 => "Du versuchst, auf einen Link zuzugreifen, für den Du keine Rechte haben. Dieser Versuch wurde protokolliert. Bitte gehe zurück zur <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">Link-Verwaltung</a>.",
    20 => 'Andere Kategorie bitte eingeben',
    21 => 'Speichern',
    22 => 'Abbrechen',
    23 => 'Löschen',
    24 => 'Link nicht gefunden',
    25 => 'Der zu bearbeitende Link konnte nicht gefunden werden.',
    26 => 'Links überprüfen',
    27 => 'HTML-Status',
    28 => 'Kategorie bearbeiten',
    29 => '<ul><li>Hier können Sie Ihre Links bearbeiten oder erstellen.</li><li>Es müssen alle Felder eingegeben werden.</li></ul>',
    30 => 'Kategorie',
    31 => 'Beschreibung',
    32 => 'Kategorie-ID',
    33 => 'Kategorie',
    34 => 'Übergeordnete Kategorie',
    35 => 'Alle',
    40 => 'Dies Kategorie bearbeiten',
    41 => 'Unterkategorie einrichten',
    42 => 'Diese Kategorie löschen',
    43 => 'Kategorie der Seite',
    44 => 'Unterkategorie&nbsp;hinzufügen',
    46 => 'Benutzer %s hat unrechtmäßig versucht die Kategorie %s zu löschen.',
    50 => 'Kategorie-Liste',
    51 => 'Neue anlegen',
    52 => 'Neue Kategorie',
    53 => 'Link-Liste',
    54 => 'Kategorie-Verwaltung',
    55 => 'Die Kategorien unten bearbeiten. Bitte beachten, Kategorie können nicht gelöscht werden, die andere Kategorien oder Links enthalten. - Sie müssen erst gelöscht oder verschoben werden.',
    56 => 'Kategorie-Editor',
    57 => 'Noch nicht überprüft',
    58 => 'Jetzt überprüfen',
    59 => '<p>Um alle aufgeführten Links zu überprüfen, einfach "Jetzt überprüfen" unten anklicken. Es kann etwas dauern, abhängig davon, wie viele Links aufgeführt sind.</p>',
    60 => 'Benutzer %s hat unrechtmäßig versucht, die Kategorie %s zu editieren.',
    61 => 'Besitzer',
    62 => 'Letzte Aktualisierung',
    63 => 'Soll dieser Link wirklich gelöscht werden?',
    64 => 'Soll diese Kategorie wirklich gelöscht werden?',
    65 => 'Link überprüfen und freigeben',
    66 => 'Hier kannst Du Deine Links erstellen oder bearbeiten.',
    67 => 'Hier kannst Du Deine Link-Kategorien erstellen oder bearbeiten.',
);

$LANG_LINKS_STATUS = array(
    100 => "Fortsetzen",
    101 => "Wechsel Protokolle",
    200 => "OK",
    201 => "Erstellt",
    202 => "Akzeptiert",
    203 => "Unmaßgebliche Information",
    204 => "Kein Inhalt",
    205 => "Inhalt zurücksetzten",
    206 => "Teilweiser Inhalt",
    300 => "Mehrfache Möglichkeiten",
    301 => "Permanent verschoben",
    302 => "Gefunden",
    303 => "Siehe andere",
    304 => "Nicht verändert",
    305 => "Proxy verwenden",
    307 => "Vorübergehend umleiten",
    400 => "Ungültige Anfrage",
    401 => "Nicht autorisiert",
    402 => "Zahlung erbeten",
    403 => "Kein Zugang",
    404 => "Nicht gefunden",
    405 => "Methode nicht erlaubt",
    406 => "Nicht akzeptabel",
    407 => "Proxy-Authentifizierung erforderlich",
    408 => "Anfragedauer überschritten",
    409 => "Konflikt",
    410 => "Verschwunden",
    411 => "Länge erforderlich",
    412 => "Voraussetzung nicht refüllt",
    413 => "Anfrage-Objekt zu groß",
    414 => "Anfrage-URI zu lang",
    415 => "Medientyp nicht unterstützt",
    416 => "Angeforderte Operation nicht ausführbar",
    417 => "Erwartung fehlgeschlagen",
    500 => "Interner Server Fehler",
    501 => "Nicht Implementiert",
    502 => "Ungültige Verbindung",
    503 => "Service nicht verfügbar",
    504 => "Verbindung Zeitüberschreitung",
    505 => "HTTP Version wird nicht unterstützt",
    999 => "Zeitüberschreitung der Verbindung"
);


// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Links',
    'title' => 'Links-Einstellungen'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => 'Anmelden für Lesen',
    'linksubmission' => 'Links moderieren',
    'newlinksinterval' => 'Zeitabstand neue Links',
    'hidenewlinks' => 'In "Was ist neu" ausblenden',
    'hidelinksmenu' => 'Menüeintrag ausblenden',
    'linkcols' => 'Kategorien pro Spalte',
    'linksperpage' => 'Links pro Seite',
    'show_top10' => 'Top 10 Links zeigen',
    'notification' => 'E-Mail Benachrichtigung',
    'delete_links' => 'Links mit Benutzer löschen',
    'aftersave' => 'Nach Abspeichern des Links',
    'show_category_descriptions' => 'Kategoriebeschreibung anzeigen?',
    'root' => 'ID der Oberkategorie',
    'default_permissions' => 'Standardberechtigungen - Links',
    'target_blank' => 'Links in neuen Fenster öffnen',
    'displayblocks' => 'Anzeige glFusion Blöcke',
    'submission'    => 'Link-Einsendungen',
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['links'] = array(
    'fs_public' => 'Öffentliche-Einstellungen',
    'fs_admin' => 'Admin-Einstellungen',
    'fs_permissions' => 'Standard-Berechtigungen'
);

$LANG_configSelect['links'] = array(
    0 => array(1=>'Ja', 0=>'Nein'),
    1 => array(true=>'Ja', false=>'Nein'),
    9 => array('item'=>'Weiter zur Seite', 'list'=>'Admin Liste anzeigen', 'plugin'=>'Link-Liste anzeigen', 'home'=>'Startseite anzeigen', 'admin'=>'Kommandozentrale'),
    12 => array(0=>'Kein Zugang', 2=>'Nur lesen', 3=>'Lesen-Schreiben'),
    13 => array(0=>'Linke Blöcke', 1=>'Rechte Blöcke', 2=>'Linke & Rechte Blöcke', 3=>'Keine'),
    14 => array(0=>'Aus', 1=>'Nur eingeloggte', 2=>'Jeder', 3=>'Aus')

);

?>
