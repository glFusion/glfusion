<?php
/**
* glFusion CMS
*
* UTF-8 Language File for glFusion CAPTCHA Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

$LANG_CP00 = array (
    'menulabel'         => 'CAPTCHA',
    'plugin'            => 'CAPTCHA',
    'access_denied'     => 'P&auml;&auml;sy kielletty',
    'access_denied_msg' => 'Sinulla ei ole tarvittavia oikeuksia tälle sivulle.',
    'admin'             => 'CAPTCHA Administration',
    'install_header'    => 'CAPTCHA Lisäosan Asennus/Asennuksen poisto',
    'installed'         => 'CAPTCHA on asennettu',
    'uninstalled'       => 'CAPTCHA ei ole asennettu',
    'install_success'   => 'CAPTCHA Asennettu onnistuneesti.  <br /><br />Katso systeemin documentaatio ja vieraile  <a href="%s">ylläpito alueella</a> varmistaaksesi että asetukset vastaa palveluympäristön vaatimuksia.',
    'install_failed'    => 'Asennus epäonnistui -- Katso error logi.',
    'uninstall_msg'     => 'Lisäosa asennus poistettu',
    'install'           => 'Asenna',
    'uninstall'         => 'Poista asennus',
    'warning'           => 'Varoitus! Lisäosa on edelleen käytössä',
    'enabled'           => 'Ota pois käytöstä ennenkuin poistat asennuksen.',
    'readme'            => 'CAPTCHA Lisäosan asennus',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Install Document</a>",
    'overview'          => 'CAPTCHA on natiivi glFusion lisäosa joka tarjoaa lisä suojaa roskaposti botteja vasataan. <br /><br />CAPTCHA (on acronyymi "Completely Automated Public Turing test to tell Computers and Humans Apart", trademarked by Carnegie Mellon University) on varmistus systeemi joka yrittää päätellä onko käyttäjä botti vai ihminen.  Esittämällä vaikeasti luettavia kuvia jossa kirjaimia, oletus on että vain ihminen osaa lukea kuvan kirjaimet. Tämän pitäisi vähentää roskaposti botteja sivustollasi.',
    'details'           => 'CAPTCHA lisäosa käyttää staattisia (valmiiksi luotuja) CAPTCHA kuvia, paitsi jos määrittelet CAPTCHAn käyttämään dynaamisia kuvia käyttämällä joko GD Grafiikka kirjastoja tai ImageMagick.  Käyttääksesi GD Kirjastoa tai ImageMagick, niiden täytyy tukea True Type fontteja.  tarkista palveluntarjoajaltasi TTF tuki.',
    'preinstall_check'  => 'CAPTCHA:lla on seuraavat vaatimukset:',
    'glfusion_check'    => 'glFusion v1.0.1 tai uudempi, nykyinen versio on <b>%s</b>.',
    'php_check'         => 'PHP v4.3.0 tai uudempi, nykyinen versio on <b>%s</b>.',
    'preinstall_confirm' => "Tiedot CAPTCHAn asentamiseen, lue <a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Installation Manual</a>.",
    'captcha_help'      => 'Solve the Problem',
    'bypass_error'      => "Yritit ohittaa CAPTCHAn, käytä Uusi Käyttäjä linkkiä ja rekisteröidy.",
    'bypass_error_blank' => "Yritit ohittaa CAPTCHAn, anna oikea CAPTCHA lauseke.",
    'entry_error'       => 'Annettu CAPTCHA merkkijono ei vastaa kuvassa olevaa, yritä uudestaan. <b>Merkkikokoriippuvainen.</b>',
    'entry_error_pic'   => 'The selected CAPTCHA images did not match the request on the graphic, please try again.',
    'captcha_info'      => 'CAPTCHA Lisäosa tarjoaa lisäsuojaa Roskapostia vastaan glFusion sivustollasi.  Katso lisätietoja<a href="%s">Online Documentation Wiki</a>.',
    'enabled_header'    => 'Nykyiset CAPTCHA Asetukset',
    'on'                => 'On',
    'off'               => 'Off',
    'captcha_alt'       => 'Anna kuvassa oleva teksti - jos et voi lukea tekstiä ota tarvittaessa yhteys ylläpitoon',
    'save'              => 'Tallenna',
    'cancel'            => 'Peruuta',
    'success'           => 'Asetusvalinnat Tallennettu.',
    'reload'            => 'päivitä',
    'reload_failed'     => 'Valitan, CAPTCHA kuvaa ei voitu ladata uudestaan.Lähetä lomake niin uusi CAPTCHA ladataan',
    'reload_too_many'   => 'Max 5 päivitys',
    'session_expired'   => 'CAPTCHA Aika on päättynyt, yritä uudestaan',
    'picture'           => 'Kuva',
    'characters'        => 'Merkit',
    'ayah_error'        => 'Sorry, but we were not able to verify you as human. Please try again.',
    'captcha_math'      => 'Enter the answer',
    'captcha_prompt'    => 'Are You Human?',
    'recaptcha_entry_error'  => 'The CAPTCHA verification failed. Please try again.',
);

// Localization of the Admin Configuration UI
$LANG_configsections['captcha'] = array(
    'label'                 => 'CAPTCHA',
    'title'                 => 'CAPTCHA Asetukset'
);
$LANG_confignames['captcha'] = array(
    'gfxDriver'             => 'Grafiika juuri',
    'gfxFormat'             => 'Grafiikka Formaatti',
    'imageset'              => 'Stattinen Kuva Asetus',
    'debug'                 => 'Debug',
    'gfxPath'               => 'Polku ImageMagick Muunto Tyäkaluun',
    'remoteusers'           => 'pakoita CAPTCHA kaikille etäkäyttäjille',
    'logging'               => 'Tallenna logiin epäkelvot CAPTCHA Yritykset',
    'anonymous_only'        => 'Vain Tuntemattomat',
    'enable_comment'        => 'Käytä Kommenteissa',
    'enable_story'          => 'Käytä Jutuissa',
    'enable_registration'   => 'Käytä Rekisteröitymisessä',
    'enable_loginform'      => 'Enable Login',
    'enable_forgotpassword' => 'Enable Forgot Password',
    'enable_contact'        => 'Käytä Yhteyslomakkeessa',
    'enable_emailstory'     => 'Käytä Sähköposti Jutuissa',
    'enable_forum'          => 'Käytä Foorumilla',
    'enable_mediagallery'   => 'Käytä Media Galleriassa (Postikortit)',
    'enable_rating'         => 'Ota Arviointi Lisäosan Tuki Käyttöön',
    'enable_links'          => 'Ota Linkki Lisäosan Tuki Käyttöön',
    'enable_calendar'       => 'Ota Kalenteri Lisäosan Tuki Käyttöön',
    'expire'                => 'Kuinka monta sekuntia CAPTCHA Istunto on voimassa',
    'publickey'             => 'reCAPTCHA Public Key - <a href="https://www.google.com/recaptcha/admin/create" target=_blank>reCAPTCHA Signup</a>',
    'privatekey'            => 'reCAPTCHA Private Key',
    'recaptcha_theme'       => 'reCAPTCHA Teema',

);
$LANG_configsubgroups['captcha'] = array(
    'sg_main'               => 'Configuration Settings'
);
$LANG_fs['captcha'] = array(
    'cp_public'                 => 'Yleis Asetukset',
    'cp_integration'            => 'CAPTCHA Integraatio',
);

$LANG_configSelect['captcha'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    2 => array(0=>'GD Libs', 3=>'reCAPTCHA', 6=>'Math Equation'),
    4 => array('default'=>'Oletus','simple'=>'Simple'),
    5 => array('jpg'=>'JPG','png'=>'PNG'),
    6 => array('light' => 'light','dark' => 'dark'),
);

$PLG_captcha_MESSAGE1 = 'CAPTCHA plugin upgrade: Update completed successfully.';
$PLG_captcha_MESSAGE2 = 'CAPTCHA plugin upgrade failed - check error.log';
$PLG_captcha_MESSAGE3 = 'CAPTCHA Plugin Successfully Installed';
?>