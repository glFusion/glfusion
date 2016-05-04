<?php
###############################################################################
# german_utf-8.php
#
# This is the German language file for the glFusion Static Pages plugin
#
# Copyright (C) 2001 Tony Bibbs
# tony AT tonybibbs DOT com
#
# German translation by Dirk Haun <dirk AT haun-online DOT de>
# and Markus Wollschläger
# Modifiziert: August 09 Tony Kluever
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

$LANG_STATIC = array(
    'newpage' => 'Neue Seite',
    'adminhome' => 'Kommandozentrale',
    'staticpages' => 'Statische Seiten',
    'staticpageeditor' => 'Editor für Statische Seiten',
    'writtenby' => 'Autor',
    'date' => 'Letzte Änderung',
    'title' => 'Titel',
    'content' => 'Inhalt',
    'hits' => 'Aufrufe',
    'staticpagelist' => 'Liste der Statischen Seiten',
    'url' => 'URL',
    'edit' => 'Bearbeiten',
    'lastupdated' => 'Letzte Änderung',
    'pageformat' => 'Seitenformat',
    'leftrightblocks' => 'Blöcke links &amp; rechts',
    'blankpage' => 'Leere Seite',
    'noblocks' => 'Keine Blöcke',
    'leftblocks' => 'Blöcke links',
    'rightblocks' => 'Right Blocks',
    'addtomenu' => 'Ins Menü aufnehmen',
    'label' => 'Label',
    'nopages' => 'Es sind keine statischen Seiten vorhanden.',
    'save' => 'Speichern',
    'preview' => 'Vorschau',
    'delete' => 'Löschen',
    'cancel' => 'Abbruch',
    'access_denied' => 'Zugriff verweigert',
    'access_denied_msg' => 'Unerlaubter Zugriff, auf eine der Admin-Seiten für Statische Seiten. Hinweis: Alle derartigen Versuche werden protokolliert',
    'all_html_allowed' => 'Alle HTML-Tags sind erlaubt',
    'results' => 'Gefundene Statische Seiten',
    'author' => 'Autor',
    'no_title_or_content' => 'Bitte mindestens die Felder <b>Titel</b> und <b>Inhalt</b> ausfüllen.',
    'no_such_page_anon' => 'Bitte anmelden.',
    'no_page_access_msg' => "Dies könnte passiert sein, weil Du nicht angemeldet bist, oder kein Mitglied bist von  {$_CONF['site_name']}. Bitte <a href=\"{$_CONF['site_url']}/users.php?mode=new\"> Mitglied werden</a> bei {$_CONF['site_name']}, um vollen Zugriff zu erhalten.",
    'php_msg' => 'PHP: ',
    'php_warn' => 'Hinweis: Wenn diese Option aktiviert ist, wird in der Seite enthaltener PHP-Code ausgeführt. <em>Bitte mit Bedacht verwenden!</em>',
    'exit_msg' => 'Hinweistext: ',
    'exit_info' => 'Art des Hinweistextes, wenn kein Zugriff auf die Seite erlaubt ist: Aktiviert = "Anmeldung erforderlich", nicht aktiviert = "Zugriff verweigert".',
    'deny_msg' => 'Zugriff auf diese Seite ist nicht möglich. Die Seite wurde entweder umbenannt oder gelöscht oder Du hast nicht die nötigen Zugriffsrechte.',
    'stats_headline' => 'Top Ten der Statischen Seiten',
    'stats_page_title' => 'Titel',
    'stats_hits' => 'Aufrufe',
    'stats_no_hits' => 'Es gibt keine Statischen Seiten oder sie wurden von niemandem gelesen.',
    'id' => 'ID',
    'duplicate_id' => 'Diese ID wird bereits für eine andere Statische Seite benutzt. Bitte andere ID wählen.',
    'instructions' => 'Um eine Statische Seite zu ändern oder zu löschen, auf das Ändern-Icon klicken. Um eine Statische Seite anzusehen, auf deren Titel klicken. Auf Neu anlegen (s.o.) klicken, um einen neue Statische Seite anzulegen. Auf das Kopie-Icon klicken, um eine Kopie einer vorhandenen Seite zu erhalten.',
    'centerblock' => 'Centerblock: ',
    'centerblock_msg' => 'Wenn angekreuzt wird diese Seite als Block auf der Index-Seite angezeigt.',
    'topic' => 'Kategorie: ',
    'position' => 'Position: ',
    'all_topics' => 'Alle',
    'no_topic' => 'Nur auf der Startseite',
    'position_top' => 'Seitenanfang',
    'position_feat' => 'Nach Hauptartikel',
    'position_bottom' => 'Seitenende',
    'position_entire' => 'Ganze Seite',
    'position_nonews' => 'Only if No Other News',
    'head_centerblock' => 'Centerblock',
    'centerblock_no' => 'Nein',
    'centerblock_top' => 'oben',
    'centerblock_feat' => 'Hauptartikel',
    'centerblock_bottom' => 'unten',
    'centerblock_entire' => 'Ganze Seite',
    'centerblock_nonews' => 'If No News',
    'inblock_msg' => 'Im Block: ',
    'inblock_info' => 'Block-Templates für diese Seite verwenden.',
    'title_edit' => 'Seite ändern',
    'title_copy' => 'Seite kopieren',
    'title_display' => 'Seite anzeigen',
    'select_php_none' => 'PHP nicht ausführen',
    'select_php_return' => 'PHP ausführen (mit return)',
    'select_php_free' => 'PHP ausführen',
    'php_not_activated' => "Das Verwenden von PHP in statischen Seiten ist nicht aktiviert. Hinweise zur Aktivierung finden sich in der <a href=\"{$_CONF['site_url']}/docs/staticpages.html#php\">Dokumentation</a>.",
    'printable_format' => 'Druckfähige Version',
    'copy' => 'Kopieren',
    'limit_results' => 'Ergebnisse einschränken',
    'search' => 'Suchen',
    'submit' => 'Absenden',
    'delete_confirm' => 'Möchtest Du diese Seite wirklich löschen?',
    'allnhp_topics' => 'All Topics (No Homepage)',
    'page_list' => 'Page List',
    'instructions_edit' => 'This screen allows you to create / edit a new static page. Pages can contain PHP code and HTML code.',
    'attributes' => 'Attributes',
    'preview_help' => 'Select the <b>Preview</b> button to refresh the preview display'
);
###############################################################################
# autotag descriptions

$LANG_SP_AUTOTAG = array(
    'desc_staticpage' => 'Link: to a staticpage on this site; link_text defaults to staticpage title. usage: [staticpage:<i>page_id</i> {link_text}]',
    'desc_staticpage_content' => 'HTML: renders the content of a staticpage.  usage: [staticpage_content:<i>page_id</i>]'
);


$PLG_staticpages_MESSAGE19 = '';
$PLG_staticpages_MESSAGE20 = '';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Plugin-Upgrade nicht unterstützt.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Statische Seiten',
    'title' => 'Konfiguration - Statische Seiten'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'PHP erlauben?',
    'sort_by' => 'Centerblocks sortieren nach',
    'sort_menu_by' => 'Menüeinträge sortieren nach',
    'delete_pages' => 'Seiten mit Benutzer löschen?',
    'in_block' => 'Block-Template verwenden?',
    'show_hits' => 'Aufrufe anzeigen?',
    'show_date' => 'Datum anzeigen?',
    'filter_html' => 'HTML filtern?',
    'censor' => 'Inhalt zensieren?',
    'default_permissions' => 'Standardeinstellungen - Statische Seiten',
    'aftersave' => 'Nach dem Speichern der Seiten',
    'atom_max_items' => 'Max. Seiten in Webservices News-Feed',
    'comment_code' => 'Comment Default',
    'include_search' => 'Site Search Default',
    'status_flag' => 'Default Page Mode'
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Statische Seiten - Haupteinstellungen',
    'fs_permissions' => 'Standardberechtigungen - Statische Seiten'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['staticpages'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => true, 'Nein' => false),
    2 => array('Datum' => 'date', 'Seiten-ID' => 'id', 'Titel' => 'title'),
    3 => array('Datum' => 'date', 'Seiten-ID' => 'id', 'Titel' => 'title', 'Menüpunkt' => 'label'),
    9 => array('Zur Seite weiterleiten' => 'item', 'Liste anzeigen' => 'list', 'Startseite' => 'home', 'Kommandozentrale' => 'admin'),
    12 => array('Kein Zugang' => 0, 'Nur lesen' => 2, 'Lesen-Schreiben' => 3),
    13 => array('Enabled' => 1, 'Disabled' => 0),
    17 => array('Comments Enabled' => 0, 'Comments Disabled' => -1)
);

?>