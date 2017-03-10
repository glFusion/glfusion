<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | dutch_utf-8.php                                                          |
// |                                                                          |
// | Dutch language file                                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2009 by the following authors:                        |
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
    'access_denied' => 'Toegang Verboden',
    'access_denied_msg' => 'You do not have the proper security privilege to access to this page.  Your user name and IP have been recorded.',
    'admin' => 'CAPTCHA Beheer',
    'install_header' => 'CAPTCHA Plugin Installeren/Verwijderen',
    'installed' => 'CAPTCHA is Geinstalleerd',
    'uninstalled' => 'CAPTCHA is Niet Geinstalleerd',
    'install_success' => 'CAPTCHA Installatie Succesvol.  <br /><br />Please review the system documentation and also visit the  <a href="%s">administration section</a> to insure your settings correctly match the hosting environment.',
    'install_failed' => 'Installatie Mislukt -- Bekijk uw fouten log om te zien waarom.',
    'uninstall_msg' => 'Plugin Succesvol Verwijderd',
    'install' => 'Installeren',
    'uninstall' => 'Verwijderen',
    'warning' => 'Waarschuwing! Plugin is nog steeds Ingeschakeld',
    'enabled' => 'Schakel de Plugin uit voor deze te verwijderen.',
    'readme' => 'CAPTCHA Plugin Installatie',
    'installdoc' => "<a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Install Document</a>",
    'overview' => 'CAPTCHA is a native glFusion plugin that provides an additional layer of security for spambots. <br /><br />A CAPTCHA (an acronym for "Completely Automated Public Turing test to tell Computers and Humans Apart", trademarked by Carnegie Mellon University) is a type of challenge-response test used in computing to determine whether or not the user is human.  By presenting a difficult to read graphic of letters and numbers, it is assumed that only a human could read and enter the characters properly.  By implementing the CAPTCHA test, it should help reduce the number of Spambot entries on your site.',
    'details' => 'The CAPTCHA plugin will use static (already generated) CAPTCHA images unless you configure CAPTCHA to build dynamic images using either the GD Graphic Library or ImageMagick.  In order to use either GD libraries or ImageMagick, they must support True Type fonts.  Check with your hosting provider to determine if they support TTF.',
    'preinstall_check' => 'CAPTCHA has the following requirements:',
    'glfusion_check' => 'glFusion v1.0.1 or greater, version reported is <b>%s</b>.',
    'php_check' => 'PHP v4.3.0 or greater, version reported is <b>%s</b>.',
    'preinstall_confirm' => "For full details on installing CAPTCHA, please refer to the <a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Installation Manual</a>.",
    'captcha_help' => 'Solve the Problem',
    'bypass_error' => 'You have attempted to bypass the CAPTCHA processing at this site, please use the New User link to register.',
    'bypass_error_blank' => 'You have attempted to bypass the CAPTCHA processing at this site, please enter a valid CAPTCHA phrase.',
    'entry_error' => 'The entered CAPTCHA string did not match the characters on the graphic, please try again. <b>This is case sensitive.</b>',
    'entry_error_pic' => 'The selected CAPTCHA images did not match the request on the graphic, please try again.',
    'captcha_info' => 'The CAPTCHA Plugin provides another layer of protection against SpamBots for your glFusion site.  See the <a href="%s">Online Documentation Wiki</a> for more info.',
    'enabled_header' => 'Huidige CAPTCHA Instellingen',
    'on' => 'Aan',
    'off' => 'Uit',
    'captcha_alt' => 'Neem contact op met de beheerder als u geen afbeelding ziet.',
    'save' => 'Opslaan',
    'cancel' => 'Annuleer',
    'success' => 'Instellingen succesvol opgeslagen.',
    'reload' => 'Nieuwe Afbeelding',
    'reload_failed' => "Sorrie, kan de CAPTCHA afbeelding niet opnieuw laden\nSubmit het formulier en er zal een nieuwe CAPTCHA afbeeldingen worden geladen",
    'reload_too_many' => 'U mag max. 5 afbeeldingen wijzigen',
    'session_expired' => 'Uw CAPTCHA Sessie is verlopen, probeer het a.u.b. opnieuw',
    'picture' => 'Afbeelding',
    'characters' => 'Karakters',
    'ayah_error' => 'Sorry, but we were not able to verify you as human. Please try again.',
    'captcha_math' => 'Enter the answer',
    'captcha_prompt' => 'Are You Human?',
    'recaptcha_entry_error' => 'The CAPTCHA verification failed. Please try again.'
);

// Localization of the Admin Configuration UI
$LANG_configsections['captcha'] = array(
    'label' => 'CAPTCHA',
    'title' => 'CAPTCHA Instellingen'
);

$LANG_confignames['captcha'] = array(
    'gfxDriver' => 'Grafische Driver',
    'gfxFormat' => 'Grafisch Formaat',
    'imageset' => 'Statische Afbeeldingen',
    'debug' => 'Debug',
    'gfxPath' => 'Volledige Pad naar ImageMagick\'s Converteer Tool',
    'remoteusers' => 'Forceer CAPTCHA voor alle Remote Gebruikers',
    'logging' => 'Log Invalide CAPTCHA pogingen',
    'anonymous_only' => 'Alleen Anoniem',
    'enable_comment' => 'Bij Reactie\'s Inschakelen',
    'enable_story' => 'Bij Artikelen Inschakelen',
    'enable_registration' => 'Bij Registratie Inschakelen',
    'enable_contact' => 'Bij Contact Inschakelen',
    'enable_emailstory' => 'Bij Artikel Emailen Inschakelen',
    'enable_forum' => 'Bij Forum Inschakelen',
    'enable_mediagallery' => 'Bij Media Gallerij (Postkaarten) Inschakelen',
    'enable_rating' => 'Bij Waardering Plugin Inschakelen',
    'enable_links' => 'Bij Links Plugin Inschakelen',
    'enable_calendar' => 'Bij Kalender Plugin Inschakelen',
    'expire' => 'Aantal seconden dat een CAPTCHA Sessie geldig is',
    'publickey' => 'reCAPTCHA Public Key - <a href="https://www.google.com/recaptcha/admin/create">reCAPTCHA Signup</a>',
    'privatekey' => 'reCAPTCHA Private Key',
    'recaptcha_theme' => 'reCAPTCHA Theme'
);

$LANG_configsubgroups['captcha'] = array(
    'sg_main' => 'Instellingen'
);

$LANG_fs['captcha'] = array(
    'cp_public' => 'Algemene Instellingen',
    'cp_integration' => 'CAPTCHA Integratie'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['captcha'] = array(
    0 => array('Ja' => 1, 'Nee' => 0),
    1 => array('Ja' => true, 'Nee' => false),
    2 => array('GD Libs' => 0, 'ImageMagick' => 1, 'Statische Afbeeldingen' => 2, 'reCAPTCHA' => 3, 'Math Equation' => 6),
    4 => array('Standaard' => 'default', 'Simpel' => 'simple'),
    5 => array('JPG' => 'jpg', 'PNG' => 'png'),
    6 => array('light' => 'light', 'dark' => 'dark')
);
$PLG_captcha_MESSAGE1 = 'CAPTCHA plugin upgrade: Update succesvol gereed.';
$PLG_captcha_MESSAGE2 = 'CAPTCHA Plugin Successfully Installed';
$PLG_captcha_MESSAGE3 = 'CAPTCHA Plugin Successfully Installed';

?>