<?php
/**
* glFusion CMS
*
* UTF-8 Spam-X Language File
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004-2008 by the following authors:
*  Tom Willett          tomw AT pigstye DOT net
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $LANG32;

$LANG_SX00 = array (
	'comdel'	    => ' komentáře smazány.',
	'deletespam'    => 'Odstranit veškerý Spam',
	'masshead'	    => '<hr/><h1 align="center">Hromadné smazání komentářů nevyžádané pošty</h1>',
	'masstb'        => '<hr/><h1 align="center">Hromadné odstranění nevyžádané pošty z trackbaků </h1>',
	'note1'		    => '<p>Poznámka: Hromadné smazání vám má pomoci při zasažení',
	'note2'		    => ' comment spam and Spam-X does not catch it.</p><ul><li>First find the link(s) or other ',
	'note3'		    => 'identifikátory tohoto spamového komentáře a přidejte jej do vaší osobní černé listiny.</li><li>Pak ',
	'note4'		    => 'vraťte se a zkontrolujte spamem-X komentáře pro nejnovější spam.</li></ul><p>Komentáře ',
	'note5'		    => 'jsou zkontrolovány komentáře od nejnovějšího po nejstarší -- kontrolováno více komentářů ',
	'note6'		    => 'vyžaduje více času pro kontrolu.</p>',
	'numtocheck'    => 'Počet komentářů ke kontrole',
    'RDF'           => 'RDF url: ',
    'URL'           => 'Adresa URL k seznamu ze Spamu-X: ',
    'access_denied' => 'Přístup odepřen',
    'access_denied_msg' => 'Přístup na tuto stránku má pouze root.  Tvé uživatelské jméno a IP adresa byly zaznamenány.',
    'acmod'         => 'Akční moduly Spam-X',
    'actmod'        => 'Aktivní moduly',
    'add1'          => 'Přidáno ',
    'add2'          => ' položky z ',
    'add3'          => "'černá listina.",
    'addcen'        => 'Přidat censora k seznamu',
    'addentry'      => 'Přidat položku',
    'admin'         => 'Administrace pluginu',
    'adminc'        => 'Administrativní příkazy:',
    'all'           => 'Vše',
    'allow_url_fopen' => '<p>Omlouváme se, vaše konfigurace webserveru neumožňuje čtení vzdálených souborů (<code>allow_url_fopen</code> je vypnut). Stáhněte si černou listinu z následující adresy URL a nahrajte ji do adresáře glFusion, <tt>%s</tt>, před dalším pokusem:',
    'auto_refresh_off' => 'Automaticky obnovit',
    'auto_refresh_on' => 'Automaticky obnovit',
    'availb'        => 'Dostupné černé listiny',
    'avmod'         => 'Dostupné moduly',
    'blacklist'     => 'Černá listina',
    'blacklist_prompt' => 'Zadejte slova pro označení spamu',
    'blacklist_success_delete' => 'Vybrané položky byly úspěšně odstraněny',
    'blacklist_success_save' => 'Filtr černého listu pluginu Spam-X byl úspěšně uložen',
    'blocked'       => 'Blokováno',
    'cancel'        => 'Zrušit',
    'cancel'        => 'Zrušit',
    'clearlog'      => 'Vymazat soubor protokolu',
    'clicki'        => 'Klikněte pro import černé listiny',
    'clickv'        => 'Klikněte pro zobrazení černé listiny',
    'comment'       => 'Komentář',
    'coninst'       => '<hr>Klikněte na aktivní modul pro jeho odstranění, klikněte na Dostupný modul pro jeho přidání.<br>Příkazy pro moduly jsou provedeny v daném pořadí.',
    'conmod'        => 'Konfigurace použití Spam-X modulu',
    'content'       => 'Obsah',
    'content_type'  => 'Typ obsahu',
    'delete'        => 'Smazat',
    'delete_confirm' => 'Opravdu chcete odstranit tuto položku?',
    'delete_confirm_2' => 'Opravdu chcete odstranit tuto položku',
    'documentation' => 'Dokumentace Spam-X pluginu',
    'e1'            => 'Chcete-li odstranit položku, klepněte na ni.',
    'e2'            => 'Chcete-li přidat položku, zadejte ji do políčka a klikněte na tlačítko Přidat. Položky mohou používat  regulérní výrazy z jazyka Perl.',
    'e3'            => 'Chcete-li přidat censorovaná slova ze seznamu glFusion stiskněte tlačítko:',
    'edit_filter_entry' => 'Upravit filtr',
    'edit_filters'  => 'Upravit filtr',
    'email'         => 'E-mail',
    'emailmsg'      => "Nový spam byl přijat na \"%s\"\nUživatelské UID: \"%s\"\n\nobsah:\"%s\"",
    'emailsubject'  => 'Nevyžádaná pošta v %s',
    'enabled'       => 'Zakázat plugin před odinstalací.',
    'entries'       => ' články.',
    'entriesadded'  => 'Položky přidány',
    'entriesdeleted'=> 'Položky odstraněny',
    'exmod'         => 'Testovací moduly Spam-X',
    'filter'        => 'Filtr',
    'filter_instruction' => 'Zde můžete definovat filtry, které budou aplikovány pro každou registraci a příspěvek na stránce. Pokud se některá z kontrol vrátí positivní, registrace / příspěvek bude zablokován jako spam',
    'filters'       => 'Filtry',
    'forum_post'    => 'Příspěvek fóra',
    'foundspam'     => 'Nalezen znak nevyžádaného příspěvku ',
    'foundspam2'    => ' publikováno uživatelem ',
    'foundspam3'    => ' z IP ',
    'fsc'           => 'Nalezen znak nevyžádaného příspěvku ',
    'fsc1'          => ' publikováno uživatelem ',
    'fsc2'          => ' z IP ',
    'headerblack'   => 'Černá listina záhlaví Spam-X HTTP',
    'headers'       => 'Požadované hlavičky:',
    'history'       => 'V poslední 3 měsících',
    'http_header'   => 'HTTP hlavička',
    'http_header_prompt' => 'Http hlavička',
    'impinst1a'     => 'Než použijete blokádu nevyžádané pošty pluginem spam-X k zobrazení a importu osobních Blacklistů z jiných',
    'impinst1b'     => ' stránky, vyzývám vás, abyste stiskli následující dvě tlačítka. (Musíte stisknout poslední tlačítko.)',
    'impinst2'      => 'Tento první odešle váš web na stránku Gplugs/Spam-X, aby mohl být přidán do hlavního seznamu ',
    'impinst2a'     => 'stránky, které sdílejí jejich černé listiny. (Poznámka: pokud máte více stránek, můžete je označit jednu za ',
    'impinst2b'     => 'master a pouze odeslat jeho název. To vám umožní snadno aktualizovat váš web a ponechat seznam menší.) ',
    'impinst2c'     => 'Po stisknutí tlačítka Odeslat stiskněte [back] pro návrat.',
    'impinst3'      => 'Následující hodnoty budou odeslány: (můžete je upravit, pokud nebudou správně).',
    'import_failure'=> '<p><strong>Chyba:</strong> Nebyly nalezeny žádné záznamy.',
    'import_success'=> 'úspěšně importované položky %d z černého listu.',
    'initial_Pimport'=> '<p>Importovat vlastní černou listinu',
    'initial_import' => 'Počáteční import MT-Blacklistu',
    'inst1'         => '<p>Pokud to uděláte, pak ostatní ',
    'inst2'         => 'bude moci zobrazit a importovat Vaši osobní černou listinu a my můžeme vytvořit efektivnější ',
    'inst3'         => 'distribuovaná databáze.</p><p>Pokud jste odeslali své webové stránky a rozhodli jste, že nechcete, aby vaše webové stránky zůstaly na seznamu ',
    'inst4'         => 'pošlete e-mail na <a href="mailto:spamx@pigstye.net">spamx@pigstye.net</a>, který mi řekne. ',
    'inst5'         => 'Všechny požadavky budou oceněny.',
    'install'       => 'Instalovat',
    'install_failed' => 'Instalace se nezdařila -- Podívejte se na protokol chyb a zjistěte proč.',
    'install_header' => 'Instalovat/odinstalovat plugin',
    'install_success' => 'Instalace úspěšná',
    'installdoc'    => 'Nainstalovat dokument.',
    'installed'     => 'Plugin je nainstalován',
    'instructions'  => 'Spam-X umožňuje definovat slova, URL a další předměty, které mohou být použity k blokování nevyžádané pošty na vašem webu.',
    'interactive_tester' => 'Interactive Tester',
    'invalid_email_or_ip'   => 'Neplatná e-mailová adresa nebo IP adresa byla zablokována',
    'invalid_item_id' => 'Invalid ID',
    'ip_address'    => 'IP adresa',
    'ip_blacklist'  => 'Černá listina IP',
    'ip_error'  => 'Položka se nezdá být platným IP nebo rozsahem IP',
    'ip_prompt' => 'Zadejte IP adresu pro blokování',
    'ipblack' => 'Černá listina IP pro plugin spam-X',
    'ipofurl'   => 'IP adresy URL',
    'ipofurl_prompt' => 'Zadejte IP adresu odkazů k blokování',
    'ipofurlblack' => 'Spam-X IP of URL Blacklist',
    'logcleared' => 'log soubor pluginu Spam-X vymazán',
    'mblack' => 'Má černá listina:',
    'new_entry' => 'Přidat Příspěvek',
    'new_filter_entry' => 'Nový filtr',
    'no_bl_data_error' => 'Žádné chyby',
    'no_blocked' => 'Tento modul nezablokoval žádný spam',
    'no_filter_data' => 'Nebyly definovány žádné filtry',
    'ok' => 'OK',
    'pblack' => 'Osobní černá listina pro plugin Spam-X',
    'plugin' => 'Plugin',
    'plugin_name' => 'Plugin Spam-X',
    'readme' => 'STOP! Než stisknete, přečtěte si prosím ',
    'referrer'      => 'Odkazující',
    'response'      => 'Odpověď',
    'rlinks' => 'Související odkazy:',
    'rsscreated' => 'RSS kanál vytvořen',
    'scan_comments' => 'Skenovat komentáře',
    'scan_trackbacks' => 'Skenovat Trackbacky',
    'secbut' => 'Toto druhé tlačítko vytvoří rdf kanál, takže ostatní mohou importovat váš seznam.',
    'sitename' => 'Název webu: ',
    'slvwhitelist' => 'Seznam povolených SLV',
    'spamdeleted' => 'Nevyžádaná pošta smazána',
    'spamx_filters' => 'Filtry pluginu Spam-X',
    'stats_deleted' => 'Příspěvky odstraněny jako spam',
    'stats_entries' => 'Články',
    'stats_header' => 'HTTP Hlavičky',
    'stats_headline' => 'Statistiky pluginu Spam-X',
    'stats_ip' => 'Blokované IP adresy',
    'stats_ipofurl' => 'Zablokováno pomocí IP adresy URL',
    'stats_mtblacklist' => 'MT-černá listina',
    'stats_page_title' => 'Černá listina',
    'stats_pblacklist' => 'Osobní černá listina',
    'submit'        => 'Publikování',
    'submit' => 'Publikování',
    'subthis' => 'tyto informace do centrální databáze pluginu Spam-X',
    'type'  => 'Typ',
    'uMTlist' => 'Aktualizovat MT-černou listinu',
    'uMTlist2' => ': Přidáno ',
    'uMTlist3' => ' položky a odstraněné položky ',
    'uPlist' => 'Aktualizovat osobní černou listinu',
    'uninstall' => 'Odinstalovat',
    'uninstall_msg' => 'Plugin byl úspěšně odinstalován',
    'uninstalled' => 'Plugin není nainstalován',
    'user_agent'    => 'Prohlížeč',
    'username'  => 'Uživatelské jméno',
    'value' => 'Hodnota',
    'viewlog' => 'Zobrazit protokol pluginu Spam-X',
    'warning' => 'Varování! Plugin je stále povolen',
);


/* Define Messages that are shown when Spam-X module action is taken */
$PLG_spamx_MESSAGE128 = 'Nevyžádaná pošta byla detekována. Příspěvek byl odstraněn.';
$PLG_spamx_MESSAGE8   = 'Zjištěna nevyžádaná pošta. Upozorňující E-mail byl odeslán správci.';

// Messages for the plugin upgrade
$PLG_spamx_MESSAGE3001 = 'Aktualizace pluginu není podporována.';
$PLG_spamx_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['spamx'] = array(
    'label' => 'Plugin Spam-X',
    'title' => 'Nastavení pluginu Spam-X'
);

$LANG_confignames['spamx'] = array(
    'action' => 'Akce pluginu Spam-X',
    'notification_email' => 'E-mailová potvrzení',
    'admin_override' => "Nefiltrovat administrátorské příspěvky",
    'logging' => 'Povolit protokolování',
    'timeout' => 'Časový limit',
    'sfs_username_check' => 'Povolit ověření uživatelského jména',
    'sfs_email_check' => 'Povolit ověření e-mailu',
    'sfs_ip_check' => 'Povolit ověření IP adresy',
    'sfs_username_confidence' => 'Minimální úroveň spolehlivosti u uživatelského jména odpovídá úrovni aktivního blokování spamu',
    'sfs_email_confidence' => 'Minimální úroveň spolehlivosti u uživatelského jména odpovídá úrovni aktivace blokování spamu',
    'sfs_ip_confidence' => 'Minimální úroveň důvěry na IP adresu aktivuje blokaci nevyžádané pošty',
    'slc_max_links' => 'Maximální povolený počet odkazů v příspěvku',
    'debug' => 'Protokolování při ladění',
    'akismet_enabled' => 'Modul Akismet povolen',
    'akismet_api_key' => 'Akismet API klíč (vyžadováno)',
    'fc_enable' => 'Povolit kontrolu formuláře',
    'sfs_enable' => 'Povolit ochranu fóra před nevyžádanou poštou',
    'slc_enable' => 'Povolit počítadlo nevyžádané pošty',
    'action_delete' => 'Odstranit identifikovanou nevyžádanou poštu',
    'action_mail' => 'Upozorní administrátora při zachycení nevyžádané pošty',
);

$LANG_configsubgroups['spamx'] = array(
    'sg_main' => 'Hlavní nastavení'
);

$LANG_fs['spamx'] = array(
    'fs_main' => 'Hlavní nastavení pluginu Spam-X',
    'fs_sfs'  => 'Zastavit nevyžádanou poštu fóra',
    'fs_slc'  => 'Povolit počítadlo nevyžádané pošty',
    'fs_akismet' => 'Akismet',
    'fs_formcheck' => 'Kontrola formuláře',
);

$LANG_configSelect['spamx'] = array(
    0 => array(1 => 'Ano', 0 => 'Ne'),
    1 => array(TRUE => 'Ano', FALSE => 'Ne')
);
?>
