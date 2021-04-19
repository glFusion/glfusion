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
    'access_denied'     => 'Přístup odepřen',
    'access_denied_msg' => 'Nemáš odpovídající práva k přístupu na tuto stránku. Tvé uživatelské jméno a IP adresa jsou zaznamenány.',
    'admin'             => 'Administrace CAPTCHA',
    'install_header'    => 'Instalovat/odinstalovat CAPTCHA plugin',
    'installed'         => 'CAPTCHA je nainstalována',
    'uninstalled'       => 'CAPTCHA není nainstalován',
    'install_success'   => 'CAPTCHA instalace úspěšná.  <br /><br />Zkontrolujte prosím systémovou dokumentaci a také navštivte sekci  <a href="%s">administrace</a> a ujistěte se, že vaše nastavení odpovídají hostitelskému prostředí.',
    'install_failed'    => 'Instalace se nezdařila -- Podívejte se na protokol chyb a zjistěte proč.',
    'uninstall_msg'     => 'Plugin byl úspěšně odinstalován',
    'install'           => 'Instalovat',
    'uninstall'         => 'Odinstalovat',
    'warning'           => 'Varování! Plugin je stále povolen',
    'enabled'           => 'Zakázat plugin před odinstalací.',
    'readme'            => 'Instalace CAPTCHA pluginu',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Nainstalovat dokument</a>",
    'overview'          => 'CAPTCHA je nativní glFusion plugin, který poskytuje další vrstvu zabezpečení spambotů. <br /><br />CAPTCHA (zkratka pro "Kompletně automatizovaný  Turingův veřejný test  s cílem říct počítačům a lidem  ", ochranná známka Carnegie Mellon University) je typ testu, který se používá při výpočtech k určení, zda je uživatel člověk- předložením obtížně čitelné  grafické podoby písmen a čísel o. Předpokládá se, že pouze člověk může číst a správně zadávat znaky. Provedením CAPTCHA testu by mělo pomoci snížit  množství  Spambotů  na vašem webu.',
    'details'           => 'CAPTCHA plugin bude používat statické (již generované) CAPTCHA obrázky, pokud nenastavíte CAPTCHA pro vytváření dynamických obrázků pomocí GD grafické knihovny nebo ImageMagicku. Aby bylo možné používat GD knihovny nebo ImageMagick, musí podporovat písma True Typu. Podívejte se na svého poskytovatele hostingu a určte, zda podporují TTF.',
    'preinstall_check'  => 'CAPTCHA má následující požadavky:',
    'glfusion_check'    => 'glFusion v1.4.3 nebo vyšší, nahlášená verze je <b>%s</b>.',
    'php_check'         => 'PHP v5.3.3 nebo větší, nahlášená verze je <b>%s</b>.',
    'preinstall_confirm' => "Podrobné informace o instalaci CAPTCHA naleznete v <a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Instalační příručce</a>.",
    'captcha_help'      => 'Vložte tučný text',
    'bypass_error'      => "Pokusili jste se obejít zpracování CAPTCHA na této stránce, prosím použijte odkaz pro registraci nového uživatele.",
    'bypass_error_blank' => "Pokusili jste se obejít zpracování CAPTCHA na této stránce, prosím vyplňte CAPTCHA.",
    'entry_error'       => 'zadaný text se neshoduje s textem v obrázku, zkus to znovu. <b>Pozor na malá a velká písmena.</b>',
    'entry_error_pic'   => 'Vybrané CAPTCHA obrázky neodpovídají požadavku na grafiku, zkuste to prosím znovu.',
    'captcha_info'      => 'Doplněk CAPTCHA poskytuje další vrstvu ochrany proti SpamBots pro vaše glFusion stránky. Více informací naleznete v <a href="%s">Online dokumentační Wiki</a>.',
    'enabled_header'    => 'Aktuální nastavení CAPTCHA',
    'on'                => 'Zapnuto',
    'off'               => 'Vypnuto',
    'captcha_alt'       => 'Musíš zadat grafický text - pokud nemůžeš přečíst obrázek, kontaktuj Admina',
    'save'              => 'Ulož',
    'cancel'            => 'Zrušit',
    'success'           => 'Konfigurace byla úspěšně zaktualizována.',
    'reload'            => 'Znovu načíst obrázek',
    'reload_failed'     => 'Omlouváme se, nelze automaticky načíst obrázek CAPTCHA. Odešlete formulář a bude načten nový CAPTCHA',
    'reload_too_many'   => 'Můžeš si vyžádat nejvýše 5 obrázků',
    'session_expired'   => 'Tvé CAPTCHA sezení skončilo, zkus to znova',
    'picture'           => 'Obrázek',
    'characters'        => 'Písmena',
    'ayah_error'        => 'Omlouváme se, ale nemohli jsme tě ověřit že jste člověk. Zkuste to prosím znovu.',
    'captcha_math'      => 'Zadejte odpověď',
    'captcha_prompt'    => 'Jste člověk?',
    'recaptcha_entry_error'  => 'Ověření karty selhalo. Zkuste to prosím znovu.',
);

// Localization of the Admin Configuration UI
$LANG_configsections['captcha'] = array(
    'label'                 => 'CAPTCHA',
    'title'                 => 'Nastavení CAPTCHA'
);
$LANG_confignames['captcha'] = array(
    'gfxDriver'             => 'Ovladač grafiky',
    'gfxFormat'             => 'Formát grafiky',
    'imageset'              => 'Nastavení statických obrázků',
    'debug'                 => 'Ladění',
    'gfxPath'               => 'Plná cesta ke nástroji ImageMagick pro konverzi obrázků',
    'remoteusers'           => 'Vynutit CAPTCHA pro všechny vzdálené uživatele',
    'logging'               => 'Zaznamenat neplatné CAPTCHA pokusy',
    'anonymous_only'        => 'Pouze pro anonymní hosty',
    'enable_comment'        => 'Povolit komentáře',
    'enable_story'          => 'Publikovat článek',
    'enable_registration'   => 'Povolení registrace',
    'enable_loginform'      => 'Povolit přihlášení',
    'enable_forgotpassword' => 'Povolit zapomenuté heslo',
    'enable_contact'        => 'Povolit kontakt',
    'enable_emailstory'     => 'Povolit zaslání článku',
    'enable_forum'          => 'Povolit fórum',
    'enable_mediagallery'   => 'Povolit mediální galerii (Postcards)',
    'enable_rating'         => 'Povolit podporu hodnotícího modulu',
    'enable_links'          => 'Povolit podporu pluginu odkazů',
    'enable_calendar'       => 'Povolit podporu pluginu Kalendáře',
    'expire'                => 'Kolik sekund je CAPTCHA relace platná',
    'publickey'             => 'reCAPTCHA veřejný klíč - <a href="https://www.google.com/recaptcha/admin/create" target=_blank>reCAPTCHA registrace</a>',
    'privatekey'            => 'privátní klíč ReCAPTCHA',
    'recaptcha_theme'       => 'reCAPTCHA thema',

);
$LANG_configsubgroups['captcha'] = array(
    'sg_main'               => 'Konfigurační nastavení'
);
$LANG_fs['captcha'] = array(
    'cp_public'                 => 'Obecná nastavení',
    'cp_integration'            => 'Integrace CAPTCHA',
);

$LANG_configSelect['captcha'] = array(
    0 => array(1=>'Ano', 0=>'Ne'),
    1 => array(true=>'Ano', false=>'Ne'),
    2 => array(0=>'GD Libs', 3=>'reCAPTCHA', 6=>'Matematická rovnice'),
    4 => array('default'=>'Výchozí','simple'=>'Jednoduchý'),
    5 => array('jpg'=>'JPG','png'=>'PNG'),
    6 => array('light' => 'světlá','dark' => 'tmavé'),
);

$PLG_captcha_MESSAGE1 = 'Aktualizace pluginu CAPTCHA: Aktualizace byla úspěšně dokončena.';
$PLG_captcha_MESSAGE2 = 'Aktualizace pluginu CAPTCHA se nezdařila - zkontrolujte error.log';
$PLG_captcha_MESSAGE3 = 'CAPTCHA plugin úspěšně nainstalován';
?>