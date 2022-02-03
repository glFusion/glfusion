<?php
/**
* glFusion CMS
*
* glFusion Installation UTF-8 Language File
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*   Tony Bibbs          tony AT tonybibbs DOT com
*   Mark Limburg        mlimburg AT users DOT sourceforge DOT net
*   Jason Whittenburg   jwhitten AT securitygeeks DOT com
*   Dirk Haun           dirk AT haun-online DOT de
*   Randy Kolenko       randy AT nextide DOT ca
*   Matt West           matt AT mattdanger DOT net
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$LANG_CHARSET = 'utf-8';

$LANG_INSTALL = array(
    'adminuser'                 => 'Uživatelské jméno správce',
    'back_to_top'               => 'Zpět nahoru',
    'calendar'                  => 'Načíst plugin kalendáře?',
    'calendar_desc'             => 'Systém online kalendáře a událostí. Obsahuje globální kalendář a osobní kalendáře pro uživatele webu.',
    'connection_settings'       => 'Vlastnosti spojení',
    'content_plugins'           => 'Obsah & Pluginy',
    'copyright'                 => '<a href="https://www.glfusion.org" target="_blank">glFusion</a> je svobodný software uvolněný pod licencí <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0.</a>',
    'core_upgrade_error'        => 'Při aktualizaci došlo k chybě.',
    'correct_perms'             => 'Opravte níže uvedené problémy. Jakmile budou odstraněny, použijte tlačítko <b>Opakovat krk</b> pro ověření prostředí.',
    'current'                   => 'Současně',
    'database_exists'           => 'Databáze již obsahuje glFusion tabulky. Prosím odstraňte glFusion tabulky před provedením nové instalace.',
    'database_info'             => 'Informace o databázi',
    'db_hostname'               => 'Název hostitelské databáze',
    'db_hostname_error'         => 'Název hostitelské databáze nemůže být prázdný.',
    'db_name'                   => 'Název databáze',
    'db_name_error'             => 'Název databáze nemůže být prázdný.',
    'db_pass'                   => 'Heslo k databázi',
    'db_table_prefix'           => 'DB prefix tabulek',
    'db_type'                   => 'Typ databáze',
    'db_type_error'             => 'Musí být vybrán typ databáze',
    'db_user'                   => 'Db uživatelské jméno',
    'db_user_error'             => 'Název hostitelské databáze nemůže být prázdný.',
    'db_too_old'                => 'MySQL verze je příliš stará - Musíte mít MySQL v%s nebo novější',
    'dbconfig_not_found'        => 'Nepodařilo se nalézt soubor db-config.php nebo db-config.php.dist. Ujistěte se, že jste zadali správnou cestu do vašeho soukromého adresáře.',
    'dbconfig_not_writable'     => 'Soubor db-config.php není zapisovatelný. Ujistěte se, že webový server má oprávnění k zápisu do tohoto souboru.',
    'directory_permissions'     => 'Oprávnění adresáře',
    'enabled'					=> 'Povoleno',
    'env_check'					=> 'Kontrola prostředí',
    'error'                     => 'Chyba',
    'file_permissions'          => 'Oprávnění souboru',
    'file_uploads'				=> 'Mnoho funkcí glFusion vyžaduje možnost nahrávání souborů, toto by mělo být povoleno.',
    'filemgmt'                  => 'Načíst FileMgmt Plugin?',
    'filemgmt_desc'             => 'Správce stahování souborů. Jednoduchý způsob, jak zajistit stahování souborů, uspořádané podle kategorie.',
    'filesystem_check'          => 'Kontrola souborového systému',
    'forum'                     => 'Načíst plugin fórum?',
    'forum_desc'                => 'Online systém komunitního fóra. Poskytuje komunitní spolupráci a interaktivitu.',
    'hosting_env'               => 'Kontrola prostředí hostingu',
    'install'                   => 'Instalovat',
    'install_heading'           => 'instalace glFusion',
    'install_steps'             => 'Instalační postup',
    'language'                  => 'Jazyk',
    'language_task'             => 'Jazyk a úkol',
    'libcustom_not_writable'    => 'do souboru lib-custom.php nelze zapisovat.',
    'links'                     => 'Načíst modul odkazy?',
    'links_desc'                => 'Systém správy odkazů. Poskytuje odkazy na další zajímavé stránky uspořádané podle kategorie.',
    'load_sample_content'       => 'Načíst ukázkový obsah stránky?',
    'mbstring_support'          => 'Doporučuje se, aby bylo načítáno rozšíření multi-byte řetězce (povoleno). Bez podpory vícenásobných řetězců budou některé funkce automaticky zakázány. Konkrétně prohlížeč souborů v příběhu WYSIWYG editor nebude fungovat.',
    'mediagallery'              => 'Načíst Media Gallery Plugin?',
    'mediagallery_desc'         => 'Systém správy multimediálního obsahu. Může být použit jako jednoduchá fotogalerie nebo robustní systém pro správu médií, který podporuje audio, video a obrázky.',
    'memory_limit'				=> 'Doporučuje se, aby bylo na vašich stránkách povoleno alespoň 64M paměti.',
    'missing_db_fields'         => 'Zadejte prosím všechna povinná pole databáze.',
    'new_install'               => 'Nová instalace',
    'next'                      => 'Další',
    'no_db'                     => 'Vypadá to, že databáze neexistuje.',
    'no_db_connect'             => 'Nelze se připojit k databázi',
    'no_innodb_support'         => 'Vybrali jste MySQL s InnoDB, ale vaše databáze nepodporuje InnoDB indexy.',
    'no_migrate_glfusion'       => 'Nemůžete migrovat existující glFusion stránku. Místo toho vyberte možnost Upgrade.',
    'none'                      => 'Žádná',
    'not_writable'              => 'Není zapisovatelný',
    'notes'						=> 'Poznámky',
    'off'                       => 'Vypnuto',
    'ok'                        => 'OK',
    'on'                        => 'Zapnuto',
    'online_help_text'          => 'Instalovat dokumentaci na glFusion.org',
    'online_install_help'       => 'On-line nápověda k instalaci',
    'open_basedir'				=> 'Pokud jsou na vašem webu povolena omezení <strong>open_basedir</strong>, může to způsobit problémy s oprávněním během instalace. Kontrola systému souborů níže by měla poukázat na jakékoli problémy.',
    'path_info'					=> 'Informace o cestě',
    'path_prompt'               => 'Cesta k soukromí/adresáři',
    'path_settings'             => 'Nastavení cesty',
    'perform_upgrade'			=> 'Provést aktualizaci',
    'php_req_version'			=> 'glFusion vyžaduje PHP verzi %s nebo novější.',
    'php_settings'				=> 'Nastavení PHP',
    'php_version'				=> 'Verze PHP',
    'php_warning'				=> 'Pokud jsou některé z níže uvedených položek označeny v <span class="no">červené</span>, můžete se setkat s problémy na vaší stránce glFusion Zkontroluj si u svého poskytovatele hostingu informace o změně nastavení PHP.',
    'plugin_install'			=> 'Instalace pluginu',
    'plugin_upgrade_error'      => 'Při aktualizaci pluginu %s došlo k chybě, zkontrolujte prosím error.log pro další podrobnosti.<br />',
    'plugin_upgrade_error_desc' => 'Následující zásuvné moduly nebyly aktualizovány. Pro více informací viz error.log.<br />',
    'polls'                     => 'Načíst plugin fórum?',
    'polls_desc'                => 'Online systém hlasování. Poskytuje průzkumy pro uživatele webu k hlasování o různých tématech.',
    'post_max_size'				=> 'glFusion umožňuje nahrávat pluginy, obrázky a soubory. Měli byste povolit alespoň 8M pro maximální velikost příspěvku.',
    'previous'                  => 'Zpět',
    'proceed'                   => 'Pokračovat',
    'recommended'               => 'Doporučené',
    'register_globals'			=> 'Pokud je PHP <strong>register_globals</strong> povoleno, může vytvářet bezpečnostní problémy.',
    'safe_mode'					=> 'Je-li povolena funkce PHP <strong>safe_mode</strong>, některé funkce glFusion nemusí fungovat správně. Konkrétně plugin Media Gallery plugin.',
    'samplecontent_desc'        => 'Je-li zaškrtnuto, nainstalujte vzorový obsah, jako jsou bloky, příběhy a statické stránky. <strong>Toto je doporučeno pro nové uživatele glFusion.</strong>',
    'select_task'               => 'Vyberte úkol',
    'session_error'             => 'Vaše relace vypršela. Prosím restartujte instalační proces.',
    'setting'                   => 'Nastavení',
    'securepassword'            => 'Heslo Správce',
    'securepassword_error'      => 'Heslo správce nemůže být prázdné',
    'site_admin_url'            => 'URL adresa správce webu',
    'site_admin_url_error'      => 'URL adresa správce webu nemůže být prázdná.',
    'site_email'                => 'Kontaktní E-mail webu',
    'site_email_error'          => 'Kontaktní E-mail na web nemůže být prázdný.',
    'site_email_notvalid'       => 'Toto není platná emailová adresa.',
    'site_info'					=> 'Informace o webu',
    'site_name'                 => 'Název webu',
    'site_name_error'           => 'Název webu nemůže být prázdný.',
    'site_noreply_email'        => 'E-mail adresa na webovou adresu No reply Email',
    'site_noreply_email_error'  => 'Adresa E-mailu bez odpovědi na web nemůže být prázdná.',
    'site_noreply_notvalid'     => 'E-mailová adresa na "No reply email" je neplatná.',
    'site_slogan'               => 'Slogan webu',
    'site_upgrade'              => 'Upgrade existující instalace glFusion',
    'site_url'                  => 'URL webu',
    'site_url_error'            => 'Adresa URL webu nemůže být prázdná.',
    'siteconfig_exists'         => 'Byl nalezen soubor siteconfig.php. Prosím odstraňte tento soubor před provedením nové instalace.',
    'siteconfig_not_found'      => 'Nelze nalézt soubor siteconfig.php, jste si jistí, že se jedná o upgrade?',
    'siteconfig_not_writable'   => 'Soubor siteconfig.php není zapisovatelný, nebo do adresáře, do kterého je uložen siteconfig.php, nelze zapisovat. Před pokračováním tento problém prosím opravte.',
    'sitedata_help'             => 'Select the type of database to use from the drop down list. This is generally <strong>MySQL</strong>. Your database should be setup to use <strong>UTF-8</strong> collation.<br /><br />Enter the hostname of the database server. This may not be the same as your web server, so check with your hosting provider if you are not sure.<br /><br />Enter the name of your database. <strong>The database must already exist.</strong> If you do not know the name of your database, contact your hosting provider.<br /><br />Enter the user name to connect to the database. If you do not know the database user name, contact your hosting provider.<br /><br />Enter the password to connect to the database. If you do not know the database password, contact your hosting provider.<br /><br />Enter a table prefix to be used for the database tables. This is helpful to separate multiple sites or systems when using a single database.<br /><br />Enter the name of your site. It will be displayed in the site header. For example, glFusion or Mark\'s Marbles. Don\'t worry, it can always be changed later.<br /><br />Enter the slogan of your site. It will be displayed in the site header below the site name. For example, synergy - stability - style. Don\'t worry, it can always be changed later.<br /><br />Enter your site\'s main email address. This is the email address for the default Admin account. Don\'t worry, it can always be changed later.<br /><br />Enter your site\'s no reply email address. It will be used to automatically send new user, password reset, and other notification emails. Don\'t worry, it can always be changed later.<br /><br />Please confirm that this is the web address or URL used to access the homepage of your site.<br /><br />Please confirm that this is the web address or URL used to access the admin section of your site.',
    'sitedata_missing'          => 'Byly zjištěny následující problémy s údaji které jste zadali:',
    'system_path'               => 'Nastavení cesty',
    'unable_mkdir'              => 'Nelze vytvořit adresář',
    'unable_to_find_ver'        => 'Nepodařilo se určit verzi glFusion.',
    'upgrade_error'             => 'Chyba aktualizace',
    'upgrade_error_text'        => 'Při aktualizaci instalace glFusion došlo k chybě.',
    'upgrade_steps'             => 'Postupy aktualizace',
    'upload_max_filesize'		=> 'glFusion umožňuje nahrávat pluginy, obrázky a soubory. Měli byste povolit alespoň 8M pro maximální velikost příspěvku.',
    'use_utf8'                  => 'Použít UTF-8',
    'welcome_help'              => 'Vítejte v instalačním průvodci glFusion CMS. Můžete nainstalovat novou stránku glFusion a aktualizovat existující stránku glFusion<br /><br />Vyberte jazyk průvodce a proveďte úkol, poté stiskněte <strong>Další</strong>.',
    'wizard_version'            => 'v%s instalační průvodce',
    'system_path_prompt'        => 'Enter the full, absolute path on your server to glFusion\'s <strong>private/</strong> directory.<br /><br />For a new install, this is the directory that contains the <strong>db-config.php.dist</strong> file, or for an upgrade, and existing <strong>db-config.php</strong> file.<br /><br />Directory Examples:<br />/home/www/glfusion/private/<br />c:/www/glfusion/private/<br /><br /><strong>Hint:</strong> From a security perspective, the most desirable location for the private/ directory is outside of the web root.  The web root is the directory that is served by your web host that relates to the root url of your site (http://www.yoursite.com/).<br /><br />It appears that the absolute path to your <strong>public_html/</strong> <i>(not <strong>private/</strong>)</i> directory is:<br /><br />%s<br /><br />We suggest that you place your private/ directory somewhere outside of the web root, if your web host allows this.<br /><br />If your web host does not allow for placement of files outside of the web root, please follow the instructions on <a href="https://www.glfusion.org/wiki/glfusion:install:pathsetting" target="_blank">Installing the private/ directory in the public web space</a> at the glFusion Documentation Wiki.',
    'advanced_settings'         => 'Rozšířená nastavení',
    'log_path'                  => 'Adresář systémových záznamů',
    'lang_path'                 => 'Nastavení cesty k volbě jazyka',
    'backup_path'               => 'Cesta k zálohování',
    'data_path'                 => 'Cesta k datům',
    'language_support'          => 'Jazyková podpora',
    'language_pack'             => 'glFusion je v angličtině, ale po instalaci si můžete stáhnout a nainstalovat <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">jazykový balíček</a>, který obsahuje jazykové soubory pro všechny podporované jazyky.',
    'libcustom_not_found'       => 'Nelze najít lib-custom.php.dist.',
    'no_db_driver'              => 'Pro instalaci glFusion musíte mít MySQL rozšíření načteno v PHP',
    'version_check'             => 'Kontrolovat aktualizace',
    'check_for_updates'         => 'Přejděte na příkaz & Control -> Kontrola aktualizace a zjistěte, zda jsou k dispozici nějaké aktualizace glFusion CMS nebo pluginu.',
    'quick_start'               => 'startovací průvodce glFusion',
    'quick_start_help'          => 'Přečtěte si prosím <a href="https://www.glfusion.org/wiki/glfusion:quickstart" target="_blank">glFusion CMS Quick Start Guide</a> a kompletní <a href="https://www.glfusion.org/wiki/" target="_blank">glFusion CMS Documentation</a> stránku pro podrobnosti o konfiguraci vašeho nového glFusion webu.',
    'upgrade'                   => 'Aktualizace',
    'support_resources'         => 'Zdroje podpory',
    'plugins'                   => 'pluginy pro glFusion',
    'support_forums'            => 'podpůrné fórum glFusion',
    'community_chat'            => 'Komunitní chat @ Discord',
    'instruction_step'          => 'Instrukce',
    'install_stepheading'       => 'Post instalační úkoly',
    'install_doc_alert'         => 'Chcete-li zajistit bezproblémovou instalaci, přečtěte si prosím <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">Insallation dokumentace</a> před pokračováním.',
    'install_header'            => 'Před instalací glFusion, budete muset znát několik klíčových informací. Poznamenejte si následující informace. Pokud si nejste jisti, co chcete vložit pro každou z níže uvedených položek, obraťte se na správce systému nebo poskytovatele hostingu.',
    'install_bullet1'           => 'Web&nbsp;<abbr title="Uniform Resource Locator">URL</abbr>',
    'install_bullet2'           => 'Databázový server',
    'install_bullet3'           => 'Název databáze',
    'install_bullet4'           => 'ID přihlášení do databáze',
    'install_bullet5'           => 'Heslo k databázi',
    'install_bullet6'           => 'Cesta k glFusion Soukromým souborům. Zde je uložen soubor db-config.php.dist. <strong>tyto soubory by neměly být dostupné přes internet a mimo váš kořenový adresář.</strong> Pokud je musíte nainstalovat do kořenového adresáře webu, prosím podívejte se na instrukce <a href="https://www.glfusion.org/wiki/glfusion:installation:web root" target="_blank">Instalace soukromých souborů v kořenovém adresáři webu</a>, abyste se dozvěděli, jak správně zabezpečit tyto soubory.',
    'install_doc_alert2'        => 'Podrobnější pokyny k aktualizaci naleznete v <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">dokumentaci instalace glFusion</a>.',
    'upgrade_heading'           => 'Důležité informace o aktualizaci',
    'doc_alert'                 => 'Chcete-li zajistit plynulý proces aktualizace, přečtěte si prosím <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">Aktualizovat dokumentaci</a> před pokračováním.',
    'doc_alert2'                => 'Podrobnější pokyny k aktualizaci naleznete v <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">dokumentace glFusion při aktualizaci</a>.',
    'backup'                    => 'Zálohovat, zálohovat!',
    'backup_instructions'       => 'Udělejte si mimořádnou pozornost a zálohujte všechny soubory z vaší současné instalace, které obsahují kód patřící uživateli. Nezapomeňte zálohovat všechny upravené motivy a obrázky z vaší současné instalace.',
    'upgrade_bullet1'           => 'Zálohujte aktuální databázi glFusion (pomocí správy databáze v administrátorském panelu).',
    'upgrade_bullet2'           => 'Pokud používáte jinou šablonu než výchozí CMS, ujistěte se, že vaše šablona byla aktualizována na podporu glFusion. Existuje několik změn motivů, které musí být provedeny pro vlastní motivy, aby glFusion fungoval správně. Ověřte, zda máte všechny potřebné změny šablon provedené navštívením stránky &nbsp;<a  target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">šablony změny</a>&nbsp;.',
    'upgrade_bullet3'           => 'Pokud jste upravili kteroukoli šablonu, zkontrolujte&nbsp;<a target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Template changes</a>&nbsp;pro aktuální vydání, abyste zjistili, zda potřebujete provést aktualizace vašich úprav.',
    'upgrade_bullet4'           => 'Zkontrolujte, zda jsou pluginy třetích stran kompatibilní nebo zda budou muset být aktualizovány.',
    'upgrade_bullet_title'      => 'Doporučuje se, aby jste udělal:',
    'cleanup'                   => 'Odstranění zastaralých souboru',
    'obsolete_confirm'          => 'Potvrzení o vyčištění souborů',
    'remove_skip_warning'       => 'Opravdu chcete přeskočit odstranění zastaralých souborů? Tyto soubory již nejsou potřebné a měly by být z bezpečnostních důvodů odstraněny. Pokud se rozhodnete přeskočit automatické odstranění, zvažte prosím jejich odstranění ručně.',
    'removal_failure'           => 'Selhání odstranění',
    'removal_fail_msg'          => 'Budete muset ručně odstranit soubory uvedené níže. Podívejte se na <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Zastaralé soubory</a> pro podrobný seznam souborů k odstranění.',
    'removal_success'           => 'Zastaralé soubory odstraněny',
    'removal_success_msg'       => 'Všechny zastaralé soubory byly úspěšně odstraněny. Vyberte <b>Dokončit</b> pro dokončení aktualizace.',
    'remove_obsolete'           => 'Odstranit zastaralé soubory',
    'remove_instructions'       => '<p>Při každé verzi glFusion existují soubory, které jsou aktualizovány a v některých případech odstraněny z glFusion systému. Z hlediska zabezpečení je důležité odstranit staré, nepoužité soubory. Průvodce aktualizací může odstranit staré soubory automaticky, pokud chcete, jinak je budete muset ručně odstranit.</p><p>Pokud si přejete ručně odstranit soubory - podívejte se prosím na <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Zastaralé soubory</a> pro odstranění seznamu zastaralých souborů. Vyberte <span class="uk-text-bold">Přeskočit</span> níže pro dokončení aktualizace.</p><p>Chcete-li mít průvodce aktualizací automaticky smazat soubory, vyberte prosím <b>Odstranit soubory</b> níže pro dokončení aktualizace.',
    'complete'                  => 'Dokončeno',
    'delete_files'              => 'Odstranit soubory',
    'cancel'                    => 'Zrušit',
    'show_files_to_delete'      => 'Zobrazit soubory k odstranění',
    'skip'                      => 'Přeskočit',
    'no_utf8'                   => 'Vybrali jste pro použití UTF-8 (což je doporučeno), ale databáze není nakonfigurována s UTF-8. Vytvořte databázi s odpovídajícím kodováním UTF-8. Více informací naleznete v <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Průvodci nastavením databáze</a> v glFusion Documentation Wiki.',
    'no_check_utf8'             => 'Nevybrali jste pro použití UTF-8 (což je doporučeno), ale databáze je nakonfigurována s UTF-8. Prosím vyberte možnost UTF-8 na instalační obrazovce. Více informací naleznete v <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Průvodci nastavením databáze</a> v glFusion Documentation Wiki.',
    'ext_installed'             => 'Nainstalováno',
    'ext_missing'               => 'Chybějící',
    'ext_required'              => 'Požadováno',
    'ext_optional'              => 'Volitelné',
    'ext_required_desc'         => 'musí být nainstalováno v PHP',
    'ext_optional_desc'         => 'by měl být nainstalován v PHP - Chybějící rozšíření by mohlo ovlivnit některé funkce glFusion.',
    'ext_good'                  => 'správně nainstalováno.',
    'ext_heading'               => 'PHP rozšíření',
    'curl_extension'            => 'Rozšíření Curl',
    'ctype_extension'           => 'Rozšíření Ctype',
    'date_extension'            => 'Rozšíření dat',
    'filter_extension'          => 'Rozšíření filtrů',
    'gd_extension'              => 'Rozšíření grafické knihovny GD',
    'gettext_extension'         => 'Rozšíření knihovny gettext',
    'hash_extension'            => 'Rozšíření Hash zprávy Digest',
    'json_extension'            => 'Rozšíření knihovny Json',
    'mbstring_extension'        => 'Multibyte (mbstring) rozšíření',
    'mysqli_extension'          => 'Rozšíření MySQLi',
    'mysql_extension'           => 'MySQL Driver (buď pdo_mysql nebo mysqli)',
    'openssl_extension'         => 'OpenSSL rozšíření',
    'session_extension'         => 'Rozšíření relace',
    'xml_extension'             => 'Rozšíření XML',
    'zlib_extension'            => 'zlib rozšíření',
    'required_php_ext'          => 'Požadovaná PHP rozšíření',
    'all_ext_present'           => 'Všechna požadovaná a volitelná rozšíření PHP jsou správně nainstalována.',
    'short_open_tags'           => '<b>short_open_tag</b> PHP by měl být vypnutý.',
    'max_execution_time'        => 'glFusion doporučuje výchozí hodnotu PHP minimálně 30 sekund, ale nahrávání pluginů a další operace mohou trvat déle, než je tomu v závislosti na vašem hostingovém prostředí. Pokud je safe_mode (výše) vypnuto, to můžete zvýšit změnou hodnoty <b>max_execution_time</b> ve vašem php.ini souboru.',
    'glfusion_v2_header'        => 'glFusion v2 Upgrade Requirements',
    'glfusion_v2_notes'         => 'glFusion v2.0 has changed the locations of serveral files, as a result, your site must have a private/data/ directory and a public_html/data/ directory that is writeable by the webserver.',
    'data_dir_error'            => 'The path <strong>%s</strong> does not have the proper permissions to allow the web server to write to this location. Please correct this error before proceeding.',
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Instalace byla dokončena',
    1 => 'Instalace glFusion ',
    2 => ' dokončeno!',
    3 => 'Gratulujeme, úspěšně jste ',
    4 => ' glFusion. Prosím přečtěte si níže uvedené informace.',
    5 => 'Chcete-li se přihlásit do svého nového glFusion webu, použijte tento účet:',
    6 => 'Jméno uživatele:',
    7 => 'Admin', // do not translate
    8 => 'Heslo:',
    9 => 'heslo', // do not translate
    10 => 'Bezpečnostní varování',
    11 => 'Nezapomeňte udělat',
    12 => 'věci',
    13 => 'Odstranit nebo přejmenovat instalační adresář,',
    14 => 'Změnit',
    15 => 'heslo účtu.',
    16 => 'Nastavit oprávnění na',
    17 => 'a',
    18 => 'zpět na',
    19 => '<strong>Note:</strong> Because the security model has been changed, we have created a new account with the rights you need to administer your new site.  The user name for this new account is <b>NewAdmin</b> and the password is <b>password</b>',
    20 => 'nainstalováno',
    21 => 'aktualizováno',
    22 => 'Odstranit instalační adresář',
    23 => 'Je důležité buď odstranit nebo přejmenovat instalační adresář na vašich stránkách. Ponechání instalačních souborů na místě je problém s bezpečností. Vyberte prosím tlačítko <strong>Odstranit instalační soubory</strong> pro automatické odstranění všech instalačních souborů. Pokud se rozhodnete neodebrat instalační soubory - prosím ručně přejmenujte adresář <strong>admin/install/</strong> na něco, co nelze snadno uhodnout.',
    24 => 'Odstranit instalační soubory',
    25 => 'Co je nového',
    26 => 'Podívejte se na glFusion Wiki - <a href="https://www.glfusion.org/wiki/glfusion:upgrade:whatsnew" target="_blank">Co je Nový oddíl</a> pro důležité informace o této verzi glFusion.',
    27 => 'Přejít na vstupní straku vašeho webu',
    28 => 'Instalační soubory odstraněny',
    29 => 'Chyba při odstraňování souborů',
    30 => 'Chyba při odstraňování instalačních souborů - Odstraňte je prosím ručně.',
    31 => 'Prosím zaznamenejte si výše uvedené heslo - musíte ho mít pro přihlášení do nového webu.',
    32 => 'Zaznamenali jste své heslo?',
    33 => 'Pokračovat na vstupní stránku',
    34 => 'Zrušit',
);
?>