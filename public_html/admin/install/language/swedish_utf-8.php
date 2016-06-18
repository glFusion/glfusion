<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | swedish_utf-8.php                                                        |
// |                                                                          |
// | swedish language file for the glFusion installation script               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Pierre "Vazze" Vasmatzis-Ahlby   pierre AT ahlby DOT se                  |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Randy Kolenko     - randy AT nextide DOT ca                     |
// |          Matt West         - matt AT mattdanger DOT net                  |
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
    die ('This file can not be used on its own.');
}

// +---------------------------------------------------------------------------+

$LANG_CHARSET = 'utf-8';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'back_to_top' => 'Tillbaka till toppen',
    'calendar' => 'Ladda Kalender till&auml;gg?',
    'calendar_desc' => 'En elektronisk kalender / evenemangssystem. Inkluderar en kalender f&ouml;r hela sidan och personlig kalender f&ouml;r anv&auml;ndare.',
    'connection_settings' => 'Anslutningsval',
    'content_plugins' => 'Inneh&aring;ll och Till&auml;gg',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> &auml;r en &ouml;ppen programvara sl&auml;ppt under <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 Licens.</a>',
    'core_upgrade_error' => 'Det uppstod ett fel med uppgraderingen av k&auml;rnan.',
    'correct_perms' => 'V&auml;nligen r&auml;tta till de fr&aring;gor som tas upp nedan. N&auml;r dem r&auml;ttats till, anv&auml;nd <b>Kontrollera</b> knappen f&ouml;r att validera villkoren.',
    'current' => 'Nuvarande',
    'database_exists' => 'Databasen inneh&aring;ller redan glFusion tabeller. V&auml;nligen radera glFusion tabellerna innan du g&ouml;r en ny installation.',
    'database_info' => 'Databas Information',
    'db_hostname' => 'Databas V&auml;rdnamn',
    'db_hostname_error' => 'Databas V&auml;rdnamn kan inte vara blankt.',
    'db_name' => 'Databas Namn',
    'db_name_error' => 'Databas Namn kan inte vara blankt.',
    'db_pass' => 'Databas L&ouml;senord',
    'db_table_prefix' => 'Databas Tabell Prefix',
    'db_type' => 'Databas Typ',
    'db_type_error' => 'Databas Typ m&aring;ste vara vald',
    'db_user' => 'Databas Anv&auml;ndarnamn',
    'db_user_error' => 'Databas Anv&auml;ndarnamn kan inte vara blankt.',
    'dbconfig_not_found' => 'Kan inte hitta db-config.php eller db-config.php.dist. V&auml;nligen kontrollera att du angett r&auml;tt s&ouml;kv&auml;g till mappen private.',
    'dbconfig_not_writable' => 'db-config.php &auml;r inte skrivbar, kontrollera att webservern har r&auml;ttighter att skriva till denna fil.',
    'directory_permissions' => 'Katalog R&auml;ttigheter',
    'enabled' => 'Aktiv',
    'env_check' => 'Kontroll av upps&auml;ttning',
    'error' => 'Fel',
    'file_permissions' => 'Fil R&auml;ttigheter',
    'file_uploads' => 'M&aring;nga funktioner i glFusion kr&auml;ver f&ouml;rm&aring;ga att kunna ladda upp filer, detta b&ouml;r vara aktiverat.',
    'filemgmt' => 'Ladda FileMgmt Till&auml;gg?',
    'filemgmt_desc' => 'Filnedladdningshanteraren. Ett enkelt s&auml;tt att erbjuda filnedladdningar, arrangerad i kategori.',
    'filesystem_check' => 'Kontrollera filsystem',
    'forum' => 'Ladda Forum Till&auml;gg?',
    'forum_desc' => 'Ett online forum system. Ger gemenskap, samarbete och interaktivitet.',
    'hosting_env' => 'Webbplatskontroll',
    'install' => 'Installera',
    'install_heading' => 'glFusion Installation',
    'install_steps' => 'INSTALLATIONS STEG',
    'language' => 'Spr&aring;l',
    'language_task' => 'Spr&aring;k & Uppgift',
    'libcustom_not_writable' => 'lib-custom.php &auml;r inte skrivbar.',
    'links' => 'Ladda L&auml;nk Till&auml;gget?',
    'links_desc' => 'Ett l&auml;nk hanterar system. Skapar l&auml;nkar till andra intressanta sajter, organiserade genom kategorirer.',
    'load_sample_content' => 'Ladda Prov Webbplats Inneh&aring;ll?',
    'mediagallery' => 'Ladda Media Galleri Till&auml;gg?',
    'mediagallery_desc' => 'Ett multi-media hanteringssystem. Kan anv&auml;ndas som ett enkelt fotogalleri eller ett robust mediahanteringssystem som st&ouml;djer ljud, video och bilder.',
    'memory_limit' => 'Det &auml;r rekommenderat att du minst har 48M av minne aktiverat p&aring; din sajt.',
    'missing_db_fields' => 'V&auml;nligen skriv in alla n&ouml;dv&auml;ndiga databasf&auml;lt.',
    'new_install' => 'Ny Installation',
    'next' => 'N&auml;sta',
    'no_db' => 'Databasen &auml;r inte giltig.',
    'no_db_connect' => 'Kan inte ansluta till databasen',
    'no_innodb_support' => 'Du har valt MySQL med InnoDB, men din databas kan inte hantera InnoDB index.',
    'no_migrate_glfusion' => 'Du kan inte migrera en befintlig glFusion sajt. V&auml;nligen v&auml;lj Uppgradera ist&auml;llet.',
    'none' => 'Ingen',
    'not_writable' => 'EJ SKRIVBAR',
    'notes' => 'Noteringar',
    'off' => 'Av',
    'ok' => 'OK',
    'on' => 'P&aring;',
    'online_help_text' => 'Online installerings hj&auml;lp<br /> hos glFusion.org',
    'online_install_help' => 'Online Installerings hj&auml;lp',
    'open_basedir' => 'Om <strong>open_basedir</strong> restriktioner &auml;r p&aring;slagna p&aring; din sajt, s&aring; kan det bli r&auml;ttighetsfel under installationen. Fil Systemskontrollen nedan borde visa eventuella fr&aring;gor.',
    'path_info' => 's&ouml;kv&auml;g Information',
    'path_prompt' => 's&ouml;kv&auml;g till private/ katalogen',
    'path_settings' => 's&ouml;kv&auml;gs inst&auml;llningar',
    'perform_upgrade' => 'Utf&ouml;r Uppgradering',
    'php_req_version' => 'glFusion beh&ouml;ver PHP version 5.3.0 eller senare.',
    'php_settings' => 'PHP inst&auml;llningar',
    'php_version' => 'PHP Version',
    'php_warning' => 'Om n&aring;gon av nedanst&aring;ende saker &auml;r markerade i <span class="no">r&ouml;tt</span>, s&aring; kan du komma att f&aring; problem med din glFusion sajt. Kontrollera med din webbplats leverant&ouml;r f&ouml;r information om f&ouml;r&auml;ndring av n&aring;gon av dessa inst&auml;llningar.',
    'plugin_install' => 'Till&auml;ggs Installation',
    'plugin_upgrade_error' => 'Det var ett problem med uppgraderingen av %s -till&auml;gget, v&auml;nligen kontrollera error.log f&ouml;r mer detaljer.<br />',
    'plugin_upgrade_error_desc' => 'F&ouml;ljande till&auml;gg blev inte uppgraderade. V&auml;nligen kontrollera error.log f&ouml;r mer detaljer.<br />',
    'polls' => 'Ladda Omr&ouml;stnings Till&auml;gget?',
    'polls_desc' => 'Ett online omr&ouml;stnings system. Ger omr&ouml;stningar f&ouml;r din sajts anv&auml;ndare. D&auml;r de kan r&ouml;sta p&aring; olika &auml;mnen.',
    'post_max_size' => 'glFusion till&aring;ter dig att ladda upp till&auml;gg, bilder och filer. Du b&ouml;r ha minst 8M f&ouml;r den maximala inskicksstorleken.',
    'previous' => 'Tillbaks',
    'proceed' => 'Vidare',
    'recommended' => 'Rekommenderad',
    'register_globals' => 'Om PHP\'s <strong>register_globals</strong> &auml;r p&aring;slaget, kan det inneb&auml;ra s&auml;kerhetsproblem.',
    'safe_mode' => 'Om PHP\'s <strong>safe_mode</strong> &auml;r p&aring;slaget, kan vissa funktioner i glFusion kanske inte fungera ordentligt. Speciellt Media Till&auml;gget.',
    'samplecontent_desc' => 'Om ikryssad, installera prov webbplats inneh&aring;ll, s&aring;som block, nyheter och statiska sidor. <strong>Detta &auml;r rekommenderat f&ouml;r nya anv&auml;ndare av glFusion.</strong>',
    'select_task' => 'V&auml;lj uppgift',
    'session_error' => 'Din session har passerat. V&auml;nligen starta om installations proceduren.',
    'setting' => 'Inst&auml;llningar',
    'site_admin_url' => 'Sajt Admin URL',
    'site_admin_url_error' => 'Sajt Admin URL kan inte vara blankt.',
    'site_email' => 'Sajt Email',
    'site_email_error' => 'Sajt Email kan inte vara blankt.',
    'site_email_notvalid' => 'Sajt Email &auml;r inte en giltigt emailadress.',
    'site_info' => 'Sajt Information',
    'site_name' => 'Sajt Namn',
    'site_name_error' => 'Sajt Namn kan inte vara blankt.',
    'site_noreply_email' => 'Sajtens Inget Svar Email "No Reply"',
    'site_noreply_email_error' => 'Sajt Inget Svar Email "No Reply" kan inte vara blankt.',
    'site_noreply_notvalid' => 'Inget Svar Email &auml;r inte en giltigt emailadress.',
    'site_slogan' => 'Sajt Slogan',
    'site_upgrade' => 'Uppgradera en Existerande glFusion sajt',
    'site_url' => 'Sajt URL',
    'site_url_error' => 'Sajt URL kan inte vara blank.',
    'siteconfig_exists' => 'En existerande siteconfig.php hittades. V&auml;nligen radera den filen innan du utf&ouml;r en ny installation.',
    'siteconfig_not_found' => 'Kan inte hitta siteconfig.php, &auml;r du s&auml;ker p&aring; att detta &auml;r en uppgradering?',
    'siteconfig_not_writable' => 'Filen siteconfig.php &auml;r inte skrivbar, Eller s&aring; &auml;r katalogen d&auml;r siteconfig.php inte skrivbar. V&auml;nligen korrigera dessa fel innan du forts&auml;tter.',
    'sitedata_help' => 'V&auml;lj typ av databas att anv&auml;nda fr&aring;n rullgardinsmenyn. Det &auml;r vanligtvis <strong>MySQL</strong>. V&auml;lj ocks&aring; om du skall anv&auml;nda <strong>UTF-8</strong> teckenupps&auml;ttningen (detta b&ouml;r vara valt f&ouml;r flerspr&aring;kssajter.)<br /><br /><br />Skriv i v&auml;rdnamnet f&ouml;r databasservern. Det beh&ouml;ver inte vara detsamma som din webbserver, kontrollera det med din webbplatsleverant&ouml;r om du &auml;r os&auml;ker.<br /><br />Skriv i namnet p&aring; din databas. <strong>Databasen m&aring;ste finnas.</strong> Om du inte vet namnet p&aring; din databas, kontakta din webbplatsleverant&ouml;r.<br /><br />Skriv i anv&auml;ndarnamnet f&ouml;r att ansluta till din databas. Om du inte vet ditt databasanv&auml;ndarnamn, kontakta din webbplatsleverant&ouml;r.<br /><br /><br />Skriv i l&ouml;senordet f&ouml;r att ansluta till din databas. Om du inte vet ditt databas l&ouml;senord, kontakta din webbplatsleverant&ouml;r.<br /><br />Skriv i ett tabellprefix f&ouml;r anv&auml;ndning i databastabellen. Detta &auml;r hj&auml;lpfullt f&ouml;r att separera flera sajter eller system n&auml;r man anv&auml;nder en databas.<br /><br />Fyll i namnet p&aring; sajt. Det kommer synas i sajthuvudet. T.ex, glFusion eller Mark\'s Leksaker. Ingen fara, det g&aring;r alltid att &auml;ndra det efter.<br /><br />Ange slogan f&ouml;r din sajt. det kommer synas i sajthuvudet under ditt sajtnamn. T.ex, synergi - stabilitet - stil. Ingen fara, det g&aring;r alltid att &auml;ndra det efter.<br /><br />Fyll i din sajts huvudsakliga emailadress. Det h&auml;r &auml;r emailadressen f&ouml;r standard Admin kontot. Ingen fara, det g&aring;r alltid att &auml;ndra det efter.<br /><br />Fyll i din sajts inget svar "No Reply" emailadress. Det anv&auml;nds f&ouml;r att automatiskt skicka nya anv&auml;ndare, l&ouml;senords &aring;terst&auml;llning, och andra notifierings email. Ingen fara, det g&aring;r alltid att &auml;ndra det efter.<br /><br />V&auml;nligen konfirmera att detta &auml;r webbadressen eller URLen att anv&auml;nda f&ouml;r att komma &aring;t din sajt.<br /><br /><br />V&auml;nligen konfirmera att detta &auml;r webbadressen eller URLen f&ouml;r att komma &aring;t admin delen av din sajt.',
    'sitedata_missing' => 'F&ouml;ljande problem uppt&auml;cktes med sajt informationen du angivit:',
    'system_path' => 's&ouml;v&auml;g inst&auml;llningar',
    'unable_mkdir' => 'Kan inte skapa katalog',
    'unable_to_find_ver' => 'Kan inte avg&ouml;ra versionen av glFusion.',
    'upgrade_error' => 'Uppgraderingsfel',
    'upgrade_error_text' => 'Ett fel uppstod vid uppgradering av din glFusion installation.',
    'upgrade_steps' => 'UPPGRADERINGSSTEG',
    'upload_max_filesize' => 'glFusion till&aring;ter dig att ladda upp till&auml;gg, bilder och filer. Du b&ouml;r till&aring;ta minst 8M som uppladdningstorlek.',
    'use_utf8' => 'Anv&auml;nd UTF-8',
    'welcome_help' => 'V&auml;lkommen till glFusion CMS Installations Verktyg. Du kan installera en ny glFusion sajt, uppgradera en existerande glFusion.<br /><br />V&auml;nligen v&auml;lj spr&aring;k f&ouml;r verktygen, och sen uppgiften att utf&ouml;ra, tryck sedan <strong>N&auml;sta</strong>.',
    'wizard_version' => 'v'.GVERSION.' Installation Verktyg',
    'system_path_prompt' => 'Fyll i hela den absoluta s&ouml;kv&auml;gen till din servers glFusions <strong>private/</strong> katalog.<br /><br />Den h&auml;r katalogen inneh&aring;ller <strong>db-config.php.dist</strong> eller <strong>db-config.php</strong> filen.<br /><br />Exempel: /home/www/glfusion/private eller c:/www/glfusion/private.<br /><br /><strong>Tips:</strong> Den absoluta s&ouml;kv&auml;gen till din <strong>public_html/</strong> <i>(inte <strong>private/</strong>)</i> katalog verkar vara:<br /><br />%s<br /><br /><strong>Avancerade Inst&auml;llningar</strong> till&aring;ter dig att forcera vissa av standard s&ouml;kv&auml;garna. Vanligtvis beh&ouml;ver du inte modifiera eller specificera dessa s&ouml;kv&auml;gar, systemet kommer best&auml;mma dem automatiskt.',
    'advanced_settings' => 'Avancerade Inst&auml;llningar',
    'log_path' => 'S&ouml;kv&auml;g till Logs',
    'lang_path' => 'S&ouml;kv&auml;g till Spr&aring;k (Lang)',
    'backup_path' => 'S&ouml;kv&auml;g Till Backup',
    'data_path' => 'S&ouml;kv&auml;g till Data',
    'language_support' => 'Language Support',
    'language_pack' => 'glFusion ships in English, but after installation you can download and install the <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">Language Pack</a> which contains the language files for all supported languages.',
    'libcustom_not_found' => 'Unable to located lib-custom.php.dist.',
    'no_db_driver' => 'You must have the MySQL extension loaded in PHP to install glFusion',
    'version_check' => 'Check For Updates',
    'check_for_updates' => "Goto the <a href=\"{$_CONF['site_admin_url']}/vercheck.php\">Upgrade Checker</a> to see if there are any glFusion CMS or Plugin updates available.",
    'quick_start' => 'glFusion Quick Start Guide',
    'quick_start_help' => 'Please review  the <a href="https://www.glfusion.org/wiki/glfusion:quickstart">glFusion CMS Quick Start Guide</a> and the full <a href="https://www.glfusion.org/wiki/">glFusion CMS Documentation</a> site for details on configurating your new glFusion site.',
    'upgrade' => 'Upgrade',
    'support_resources' => 'Support Resources',
    'plugins' => 'glFusion Plugins',
    'support_forums' => 'glFusion Support Forums',
    'instruction_step' => 'Instructions',
    'install_stepheading' => 'New Install Tasks',
    'install_doc_alert' => 'To ensure a smooth installation, please read the <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">Insallation Documentation</a> before proceeding.',
    'install_header'            => 'Before installing glFusion, you will need to know a few key pieces of information. Write down the following information. If you are unsure what to put for each of the items below, please contact your system administrator or you hosting provider.',
    'install_bullet1' => 'Site&nbsp;<abbr title="Uniform Resource Locator">URL</abbr>',
    'install_bullet2' => 'Database Server',
    'install_bullet3' => 'Database Name',
    'install_bullet4' => 'Database Login ID',
    'install_bullet5' => 'Database Password',
    'install_bullet6' => 'Path to glFusion Private Files. This is where the db-config.php.dist file is stored. <strong>these files should not be available via the Internet, so they go outside of your web root directory.</strong> If you must install them in the webroot, please refer to the <a href="https://www.glfusion.org/wiki/glfusion:installation:webroot" target="_blank">Installing Private Files in Webroot</a> instructions to learn how to properly secure these files.',
    'install_doc_alert2' => 'For more detailed upgrade instructions, please refer to the <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">glFusion Installation Documentation</a>.',
    'upgrade_heading' => 'Important Upgrade Information',
    'doc_alert' => 'To ensure a smooth upgrade process, please read the <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">Upgrade Documentation</a> before proceeding.',
    'doc_alert2' => 'For more detailed upgrade instructions, please refer to the <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">glFusion Documentation on Upgrading</a>.',
    'backup' => 'Backup, Backup, Backup!',
    'backup_instructions' => 'Take extreme care to back up any files from your current installation that have any custom code in them. Be sure to back up any modified themes and images from your current installation.',
    'upgrade_bullet1' => 'Back Up your current glFusion Database (Database Administration option under Command and Control).',
    'upgrade_bullet2' => 'If you are using a theme other than the default CMS, make sure your theme has been updated to support glFusion. There are several theme changes that must be made to custom themes to allow glFusion to work properly. Verify you have all the necessary template changes made by visiting the&nbsp;<a  target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Template Changes</a>&nbsp;page.',
    'upgrade_bullet3' => 'If you have customized any of the theme templates, check the&nbsp;<a target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Template Changes</a>&nbsp;for the current release to see if you need to make any updates to your customizations.',
    'upgrade_bullet4' => 'Check any third party plugins to ensure they are compatible or if they will need to be updated.',
    'upgrade_bullet_title' => 'It is recommended that yo do the following:'
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Installation f&auml;rdig',
    1 => 'Installation av glFusion ',
    2 => ' f&auml;rdig!',
    3 => 'Grattis, du har framg&aring;ngsrikt ',
    4 => ' glFusion. V&auml;nligen ta en stund att l&auml;sa informationen presenterad nedanf&ouml;r.',
    5 => 'F&ouml;r att logga in till din nya glFusion sajt, v&auml;nligen anv&auml;nd det h&auml;r kontot:',
    6 => 'Anv&auml;ndarnamn:',
    7 => 'Admin',
    8 => 'L&ouml;senord:',
    9 => 'password',
    10 => 'S&auml;kerhets varning',
    11 => 'Gl&ouml;m inte att g&ouml;ra',
    12 => 'saker',
    13 => 'Ta bort eller byt namn p&aring; installationsmappen,',
    14 => '&auml;ndra',
    15 => 'konto l&ouml;senordet.',
    16 => 'S&auml;tt r&auml;ttigheter p&aring;',
    17 => 'och',
    18 => 'tillbaks till',
    19 => '<strong>Notis:</strong> Bara f&ouml;r att s&auml;kerhetsmodellen har &auml;ndrats, har vi skapat ett nytt konto med de r&auml;ttigheter du beh&ouml;ver f&ouml;r att administrera din nya sajt.  Anv&auml;ndarnamnet f&ouml;r ditt nya konto &auml;r <b>NewAdmin</b> och l&ouml;senordet &auml;r <b>password</b>',
    20 => 'installerad',
    21 => 'uppgraderad'
);

?>