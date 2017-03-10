<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | finnish_utf-8.php                                                        |
// |                                                                          |
// | finnish language file                                                    |
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
    'access_denied' => 'P&auml;&auml;sy ev&auml;tty',
    'access_denied_msg' => 'Sinulla ei ole tarvittavia oikeuksia t&auml;lle sivulle.',
    'admin' => 'CAPTCHA Administration',
    'install_header' => 'CAPTCHA Lis&auml;osan Asennus/Asennuksen poisto',
    'installed' => 'CAPTCHA on asennettu',
    'uninstalled' => 'CAPTCHA ei ole asennettu',
    'install_success' => 'CAPTCHA Asennettu onnistuneesti.  <br /><br />Katso systeemin documentaatio ja vieraile  <a href="%s">yll&auml;pito alueella</a> varmistaaksesi ett&auml; asetukset vastaa palveluymp&auml;rist&ouml;n vaatimuksia.',
    'install_failed' => 'Asennus ep&auml;onnistui -- Katso error logi.',
    'uninstall_msg' => 'Lis&auml;osa asennus poistettu',
    'install' => 'Asenna',
    'uninstall' => 'Poista asennus',
    'warning' => 'Varoitus! Lis&auml;osa on edelleen k&auml;yt&ouml;ss&auml;',
    'enabled' => 'Ota pois k&auml;yt&ouml;st&auml; ennenkuin poistat asennuksen.',
    'readme' => 'CAPTCHA Lis&auml;osan asennus',
    'installdoc' => "<a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Install Document</a>",
    'overview' => 'CAPTCHA on natiivi glFusion lis&auml;osa joka tarjoaa lis&auml; suojaa roskaposti botteja vasataan. <br /><br />CAPTCHA (on acronyymi "Completely Automated Public Turing test to tell Computers and Humans Apart", trademarked by Carnegie Mellon University) on varmistus systeemi joka yritt&auml;&auml; p&auml;&auml;tell&auml; onko k&auml;ytt&auml;j&auml; botti vai ihminen.  Esitt&auml;m&auml;ll&auml; vaikeasti luettavia kuvia jossa kirjaimia, oletus on ett&auml; vain ihminen osaa lukea kuvan kirjaimet. T&auml;m&auml;n pit&auml;isi v&auml;hent&auml;&auml; roskaposti botteja sivustollasi.',
    'details' => 'CAPTCHA lis&auml;osa k&auml;ytt&auml;&auml; staattisia (valmiiksi luotuja) CAPTCHA kuvia, paitsi jos m&auml;&auml;rittelet CAPTCHAn k&auml;ytt&auml;m&auml;&auml;n dynaamisia kuvia k&auml;ytt&auml;m&auml;ll&auml; joko GD Grafiikka kirjastoja tai ImageMagick.  K&auml;ytt&auml;&auml;ksesi GD Kirjastoa tai ImageMagick, niiden t&auml;ytyy tukea True Type fontteja.  tarkista palveluntarjoajaltasi TTF tuki.',
    'preinstall_check' => 'CAPTCHA:lla on seuraavat vaatimukset:',
    'glfusion_check' => 'glFusion v1.0.1 tai uudempi, nykyinen versio on <b>%s</b>.',
    'php_check' => 'PHP v4.3.0 tai uudempi, nykyinen versio on <b>%s</b>.',
    'preinstall_confirm' => "Tiedot CAPTCHAn asentamiseen, lue <a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Installation Manual</a>.",
    'captcha_help' => 'Solve the Problem',
    'bypass_error' => 'Yritit ohittaa CAPTCHAn, k&auml;yt&auml; Uusi K&auml;ytt&auml;j&auml; linkki&auml; ja rekister&ouml;idy.',
    'bypass_error_blank' => 'Yritit ohittaa CAPTCHAn, anna oikea CAPTCHA lauseke.',
    'entry_error' => 'Annettu CAPTCHA merkkijono ei vastaa kuvassa olevaa, yrit&auml; uudestaan. <b>Merkkikokoriippuvainen.</b>',
    'entry_error_pic' => 'The selected CAPTCHA images did not match the request on the graphic, please try again.',
    'captcha_info' => 'CAPTCHA Lis&auml;osa tarjoaa lis&auml;suojaa Roskapostia vastaan glFusion sivustollasi.  Katso lis&auml;tietoja<a href="%s">Online Documentation Wiki</a>.',
    'enabled_header' => 'Nykyiset CAPTCHA Asetukset',
    'on' => 'On',
    'off' => 'Off',
    'captcha_alt' => 'Anna kuvassa oleva teksti - jos et voi lukea teksti&auml; ota tarvittaessa yhteys yll&auml;pitoon',
    'save' => 'Tallenna',
    'cancel' => 'Peruuta',
    'success' => 'Asetusvalinnat Tallennettu.',
    'reload' => 'p&auml;ivit&auml;',
    'reload_failed' => "Valitan, CAPTCHA kuvaa ei voitu ladata uudestaan\nL&auml;het&auml; lomake niin uusi CAPTCHA ladataan",
    'reload_too_many' => 'Max 5 p&auml;ivitys',
    'session_expired' => 'CAPTCHA Aika on p&auml;&auml;ttynyt, yrit&auml; uudestaan',
    'picture' => 'Kuva',
    'characters' => 'Merkit',
    'ayah_error' => 'Sorry, but we were not able to verify you as human. Please try again.',
    'captcha_math' => 'Enter the answer',
    'captcha_prompt' => 'Are You Human?',
    'recaptcha_entry_error' => 'The CAPTCHA verification failed. Please try again.'
);

// Localization of the Admin Configuration UI
$LANG_configsections['captcha'] = array(
    'label' => 'CAPTCHA',
    'title' => 'CAPTCHA Asetukset'
);

$LANG_confignames['captcha'] = array(
    'gfxDriver' => 'Grafiika juuri',
    'gfxFormat' => 'Grafiikka Formaatti',
    'imageset' => 'Stattinen Kuva Asetus',
    'debug' => 'Debug',
    'gfxPath' => 'Polku ImageMagick Muunto Ty&auml;kaluun',
    'remoteusers' => 'pakoita CAPTCHA kaikille et&auml;k&auml;ytt&auml;jille',
    'logging' => 'Tallenna logiin ep&auml;kelvot CAPTCHA Yritykset',
    'anonymous_only' => 'Vain Tuntemattomat',
    'enable_comment' => 'K&auml;yt&auml; Kommenteissa',
    'enable_story' => 'K&auml;yt&auml; Jutuissa',
    'enable_registration' => 'K&auml;yt&auml; Rekister&ouml;itymisess&auml;',
    'enable_contact' => 'K&auml;yt&auml; Yhteyslomakkeessa',
    'enable_emailstory' => 'K&auml;yt&auml; S&auml;hk&ouml;posti Jutuissa',
    'enable_forum' => 'K&auml;yt&auml; Foorumilla',
    'enable_mediagallery' => 'K&auml;yt&auml; Media Galleriassa (Postikortit)',
    'enable_rating' => 'Ota Arviointi Lis&auml;osan Tuki K&auml;ytt&ouml;&ouml;n',
    'enable_links' => 'Ota Linkki Lis&auml;osan Tuki K&auml;ytt&ouml;&ouml;n',
    'enable_calendar' => 'Ota Kalenteri Lis&auml;osan Tuki K&auml;ytt&ouml;&ouml;n',
    'expire' => 'Kuinka monta sekuntia CAPTCHA Istunto on voimassa',
    'publickey' => 'reCAPTCHA Public Key - <a href="http://recaptcha.net/api/getkey?app=php">reCAPTCHA Signup</a>',
    'privatekey' => 'reCAPTCHA Private Key',
    'recaptcha_theme' => 'reCAPTCHA Teema'
);

$LANG_configsubgroups['captcha'] = array(
    'sg_main' => 'Configuration Settings'
);

$LANG_fs['captcha'] = array(
    'cp_public' => 'Yleis Asetukset',
    'cp_integration' => 'CAPTCHA Integraatio'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['captcha'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    2 => array('GD Libs' => 0, 'ImageMagick' => 1, 'Static Images' => 2, 'reCAPTCHA' => 3, 'Math Equation' => 6),
    4 => array('Default' => 'default', 'Simple' => 'simple'),
    5 => array('JPG' => 'jpg', 'PNG' => 'png'),
    6 => array('light' => 'light', 'dark' => 'dark')
);
$PLG_captcha_MESSAGE1 = 'CAPTCHA Lis&auml;osan P&auml;ivitys: P&auml;ivitys Valmis.';
$PLG_captcha_MESSAGE2 = 'CAPTCHA Plugin Successfully Installed';
$PLG_captcha_MESSAGE3 = 'CAPTCHA Plugin Successfully Installed';

?>