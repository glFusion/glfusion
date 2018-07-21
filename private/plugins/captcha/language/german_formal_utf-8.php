<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | german_formal_utf-8.php                                                  |
// |                                                                          |
// | German formal language file, addressing the user as "Sie"                |
// | Modifiziert: August 09 Tony Kluever	                                  |
// | Siegfried Gutschi (Oktober 2017) <sigi AT modellbaukalender DOT info>    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

###############################################################################

$LANG_CP00 = array(
    'menulabel' => 'CAPTCHA',
    'plugin' => 'CAPTCHA',
    'access_denied' => 'Zugriff verweigert',
    'access_denied_msg' => 'Sie besitzen nicht die nötigen Berechtigungen, um auf diese Seite zugreifen zu können.  Ihr Benutzername und Ihre IP wurden aufgezeichnet.',
    'admin' => 'CAPTCHA-Administration',
    'install_header' => 'CAPTCHA-Plugin Installieren / Deinstallieren',
    'installed' => 'CAPTCHA ist installiert',
    'uninstalled' => 'CAPTCHA ist nicht installiert',
    'install_success' => 'CAPTCHA-Installation erfolgreich.<br /><br />Bitte lesen Sie die Dokumentation durch und besuchen Sie die <a href="%s">Kommandozentrale</a> um sicherzustellen, dass Ihre Einstellungen zu Ihrer Hosting-Umgebung passen.',
    'install_failed' => 'Installation fehlgeschlagen! Überprüfen Sie die Datei "error.log" für weitere Informationen.',
    'uninstall_msg' => 'Plugin erfolgreich deinstalliert',
    'install' => 'Installieren',
    'uninstall' => 'Deinstallieren',
    'warning' => 'Warnung! Plugin ist noch akiviert',
    'enabled' => 'Deaktivieren Sie das Plugin, bevor Sie es deinstallieren.',
    'readme' => 'CAPTCHA-Plugin-Installation',
    'installdoc' => "<a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Installationsanleitung</a>",
    'overview' => 'CAPTCHA ist ein natives glFusion-Plugin, dass zusätzlichen Schutz vor Spambots gewährt. <br /><br />Ein CAPTCHA (ein Akronym für "Completely Automated Public Turing test to tell Computers and Humans Apart", TM by Carnegie Mellon University) ist ein Frage/Antwort-Test, um festzustellen, ob der Benutzer ein Mensch oder nicht. Durch das Anzeigen eine schwer lesbaren Grafik mit Buchstaben und Zahlen, geht man davon aus, dass nur ein Mensch sie lesen und die entsprechenden Zeichen eingeben kann. Das Implementieren von CAPTCHA soll helfen, die Anzahl der Spambots auf Deiner Seite zu reduzieren.',
    'details' => 'Das CAPTCHA-Plugin verwendet statische (vorab generierte) CAPTCHA-Bilder, es sei denn, Sie konfigurieren CAPTCHA so, dass dynamisch Bilder mittels der GD Graphic Library oder ImageMagick generiert werden.  Um die GD Libraries oder ImageMagick zu verwenden, müssen True-Type-Schriftarten unterstützen.  Bitte erkundigen Sie sich bei ihrem Webhoster, ob TTF unterstützt wird.',
    'preinstall_check' => 'CAPTCHA erfordert folgendes:',
    'glfusion_check' => 'glFusion v1.4.3 oder höher, derzeitige Version ist <b>%s</b>.',
    'php_check' => 'PHP v5.2.0 oder höher, derzeitige Version ist <b>%s</b>.',
    'preinstall_confirm' => "Für weitere Details zum Installieren von CAPTCHA, schauen Sie bitte in die <a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Installationsanleitung</a>.",
    'captcha_help' => 'Geben Sie die Zeichen ein',
    'bypass_error' => 'Sie haben versucht die CAPTCHA-Abfrage auf dieser Seite zu umgehen, bitte verwenden Sie den "Neuer Benutzer" Link zur Registrierung.',
    'bypass_error_blank' => 'Sie haben versucht die CAPTCHA-Abfrage auf dieser Seite zu umgehen, bitte geben Sie eine gültige CAPTCHA-Zeichenfolge ein.',
    'entry_error' => 'Die eingegebene CAPTCHA-Zeichenfolge stimmt nicht mit den Zeichen in der Grafik überein, bitte versuchen Sie es erneut und beachten Sie <b>Groß-und Kleinschreibung</b>!',
    'entry_error_pic' => 'Die ausgewählten CAPTCHA-Bilder entsprechen nicht den geforderten in der Grafik. Bitte versuchen Sie es erneut.',
    'captcha_info' => 'Das CAPTCHA-Plugin bietet Ihrer Seite zusätzlichen Schutz vor Spambots.  Schauen Sie in das <a href="%s">Online-Dokumentation-Wiki</a> für weitere Infos.',
    'enabled_header' => 'Aktuelle CAPTCHA-Einstellungen',
    'on' => 'Ein',
    'off' => 'Aus',
    'captcha_alt' => 'Sie müssen den grafischen Text eingeben - kontaktieren Sie den Seiten-Admin, wenn es Ihnen nicht möglich ist, die Grafik zu lesen',
    'save' => 'Speichern',
    'cancel' => 'Abbruch',
    'success' => 'Konfiguration erfolgreich gespeichert.',
    'reload' => 'Neues Bild',
    'reload_failed' => 'CAPTCHA-Bild kann nicht automatisch neu geladen werden. Senden Sie das Formular ab um ein neues CAPTCHA-Bild zu generieren',
    'reload_too_many' => 'Sie könnent max. 5 neue Bilder generieren lassen',
    'session_expired' => 'Ihre CAPTCHA-Gültigkeit ist abgelaufen, bitte versuchen Sie es erneut',
    'picture' => 'Bild',
    'characters' => 'Zeichen',
    'ayah_error' => 'Sorry, aber wir waren nicht in der Lage Sie als Mensch zu identifizieren. Bitte versuchen Sie es erneut.',
    'captcha_math' => 'Geben Sie die Antwort ein',
    'captcha_prompt' => 'Sind Sie ein Mensch?',
    'recaptcha_entry_error' => 'Die CAPTCHA-Überprüfung ist fehlgeschlagen. Bitte versuche Sie es erneut.'
);

// Localization of the Admin Configuration UI
$LANG_configsections['captcha'] = array(
    'label' => 'CAPTCHA',
    'title' => 'CAPTCHA-Konfiguration'
);

$LANG_confignames['captcha'] = array(
    'gfxDriver' => 'Grafik-Treiber',
    'gfxFormat' => 'Grafik-Formate',
    'imageset' => 'Statisches Bildset',
    'debug' => 'Detailierte Fehlermeldung',
    'gfxPath' => 'Pfad zu ImageMagick',
    'remoteusers' => 'Für Remote-Benutzer erzwingen',
    'logging' => 'Ungültige Versuche aufzeichnen',
    'anonymous_only' => 'Nur für Gäste',
    'enable_comment' => 'Für Kommentare',
    'enable_story' => 'Für Artikel-Einsendung',
    'enable_registration' => 'Für Registrierung',
    'enable_loginform' => 'Enable Login',
    'enable_forgotpassword' => 'Enable Forgot Password',
    'enable_contact' => 'Für Kontakt-Formulare',
    'enable_emailstory' => 'Für Artikel-Versand',
    'enable_forum' => 'Für Forum-Einträge',
    'enable_mediagallery' => 'Für Medien-Versand',
    'enable_rating' => 'Für Bewertungen',
    'enable_links' => 'Für Link-Einsendung',
    'enable_calendar' => 'Für Kalender-Einsendung',
    'expire' => 'Gültigkeit des CAPTCHA in Sek.',
    'publickey' => '<a href="https://www.google.com/recaptcha/admin/create">reCAPTCHA</a> Öffentlicher-Schlüssel',
    'privatekey' => 'reCAPTCHA Privater-Schlüssel',
    'recaptcha_theme' => 'reCAPTCHA Oberfläche'
);

$LANG_configsubgroups['captcha'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['captcha'] = array(
    'cp_public' => 'Allgemeine-Einstellungen',
    'cp_integration' => 'CAPTCHA-Einbindung'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['captcha'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => true, 'Nein' => false),
    2 => array('GD Libs' => 0, 'reCAPTCHA' => 3, 'Rechnung' => 6),
    4 => array('Standard' => 'default', 'Einfach' => 'simple'),
    5 => array('JPG' => 'jpg', 'PNG' => 'png'),
    6 => array('Hell' => 'light', 'Dunkel' => 'dark')
);
$PLG_captcha_MESSAGE1 = 'CAPTCHA-Plugin Aktualisierung: Aktualisierung erfolgreich abgeschlossen.';
$PLG_captcha_MESSAGE2 = 'CAPTCHA Plugin Successfully Installed';
$PLG_captcha_MESSAGE3 = 'CAPTCHA Plugin Successfully Installed';

?>