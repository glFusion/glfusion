<?php
/**
* glFusion CMS
*
* UTF-8 Language File for FileMgt Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by the following authors:
*   Consult4Hire Inc.
*   Blaine Lang  - blaine AT portalparts DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

$LANG_FM00 = array (
    'access_denied'     => 'Přístup odepřen',
    'access_denied_msg' => 'Přístup na tuto stránku má pouze root.  Tvé uživatelské jméno a IP adresa byly zaznamenány.',
    'admin'             => 'Admin pluginu',
    'install_header'    => 'Instalovat/odinstalovat plugin',
    'installed'         => 'Plugin a blok jsou nyní nainstalovány,<p><i>Užijte si,<br><a href="MAILTO:support@glfusion.org">glFusion tým</a></i>',
    'uninstalled'       => 'Plugin není nainstalován',
    'install_success'   => 'Instalace úspěšná<p><b>Další kroky</b>:
        <ol><li>Použijte Filemgmt Admin k dokončení konfigurace pluginu</ol>
        <p>Zkontrolujte <a href="%s">Install Notes</a> pro více informací.',
    'install_failed'    => 'Instalace se nezdařila -- Podívejte se na protokol chyb a zjistěte proč.',
    'uninstall_msg'     => 'Plugin byl úspěšně odinstalován',
    'install'           => 'Instalovat',
    'uninstall'         => 'Odinstalovat',
    'editor'            => 'Editor pluginů',
    'warning'           => 'Upozornění před odinstalací',
    'enabled'           => '<p style="padding: 15px 0px 5px 25px;">Plugin je nainstalován a povolen.<br>Nejprve vypněte, pokud jej chcete odinstalovat.</p><div style="padding:5px 0px 5px 25px;"><a href="'.$_CONF['site_admin_url'].'/plugins.php">Editor pluginu</a></div',
    'WhatsNewLabel'     => 'Soubory',
    'WhatsNewPeriod'    => ' za posledních %s dní',
    'new_upload'        => 'Nový soubor odeslán v ',
    'new_upload_body'   => 'Nový soubor byl odeslán do fronty v ',
    'details'           => 'Podrobnosti',
    'filename'          => 'Jméno souboru',
    'uploaded_by'       => 'Přidal',
    'not_found'         => 'Stahování nenalezeno',
);

// Admin Navbar
$LANG_FM02 = array(
    'instructions' => 'Pro úpravu nebo odstranění souboru klikněte na ikonu souborů níže. Chcete-li zobrazit nebo upravit kategorie, vyberte možnost Kategorie výše.',
    'nav1'  => 'Nastavení',
    'nav2'  => 'Kategorie',
    'nav3'  => 'Přidej soubor',
    'nav4'  => 'Staženo (%s)',
    'nav5'  => 'Poškozené soubory (%s)',
    'edit'  => 'Editovat',
    'file'  => 'Jméno souboru',
    'category' => 'Název kategorie',
    'version' => 'Verze',
    'size'  => 'Velikost',
    'date' => 'Datum',
);

$LANG_FILEMGMT = array(
    'newpage'               => "Nová strana",
    'adminhome'             => "Administrace domů",
    'plugin_name'           => "Správa souborů",
    'searchlabel'           => "Má stahování",
    'searchlabel_results'   => "Výsledky stahování",
    'downloads'             => "Má stahování",
    'report'                => "Nejstahovanější",
    'usermenu1'             => "Má stahování",
    'usermenu2'             => "  Nejlépe hodnocené",
    'usermenu3'             => "Nahraj soubor",
    'admin_menu'            => "Admin pluginu Filemgmt",
    'writtenby'             => "Zapsáno",
    'date'                  => "Naposledy aktualizováno",
    'title'                 => "Název",
    'content'               => "Obsah",
    'hits'                  => "Hity",
    'Filelisting'           => "Výpis souborů",
    'DownloadReport'        => "Stáhnout historii jednoho souboru",
    'StatsMsg1'             => "Top Ten přístupných souborů v úložišti",
    'StatsMsg2'             => "Vypadá to, že v úložišti (filemgmt plugin) nejsou na těchto stránkách žádné soubory nebo k nim není přístup.",
    'usealtheader'          => "Use Alt. Header",
    'url'                   => "URL",
    'edit'                  => "Editovat",
    'lastupdated'           => "Naposledy aktualizováno",
    'pageformat'            => "Formát stránky",
    'leftrightblocks'       => "Bloky nalevo a napravo",
    'blankpage'             => "Prázdná stránka",
    'noblocks'              => "Bez bloků",
    'leftblocks'            => "Bloky nalevo",
    'addtomenu'             => 'Přidat do menu',
    'label'                 => 'Štítek',
    'nofiles'               => 'Počet souborů v úložišti (celkem staženo)',
    'save'                  => 'Ulož',
    'preview'               => 'Náhled',
    'delete'                => 'Smazat',
    'cancel'                => 'Zrušit',
    'access_denied'         => 'Přístup odepřen',
    'invalid_install'       => 'Někdo se pokusil nelegálně vstoupit na plugin File Management Instalovat /odinstalovat soubory. Id uživatele: ',
    'start_install'         => 'Pokus o instalaci Filemgmt Pluginu',
    'start_dbcreate'        => 'Pokus o vytvoření tabulek pro Filemgmt plugin',
    'install_skip'          => '... přeskočeno na filemgmt.cfg',
    'access_denied_msg'     => 'Neoprávněně zkoušíte přístup k administraci pluginu File Mgmt. Vezměte prosím na vědomí, že všechny pokusy o neoprávněný přístup na tuto stránku jsou zaznamenány',
    'installation_complete' => 'Instalace dokončena',
    'installation_complete_msg' => 'Datové struktury pro plugin File Mgmt pro glFusion byly úspěšně nainstalovány do vaší databáze! Pokud někdy potřebujete odinstalovat tento plugin, přečtěte si prosím dokument README, který přišel s tímto pluginem.',
    'installation_failed'   => 'Instalace se nezdařila',
    'installation_failed_msg' => 'Instalace pluginu File Mgmt selhala. Pro diagnostické informace si prosím přečtěte soubor glFusion error.log',
    'system_locked'         => 'Systém uzamčen',
    'system_locked_msg'     => 'Plugin File Mgmt již byl nainstalován a je uzamčen. Pokud se pokoušíte odinstalovat tento plugin, přečtěte si prosím dokument README, který je dodáván s tímto pluginem',
    'uninstall_complete'    => 'Odinstalace dokončena',
    'uninstall_complete_msg' => 'Data pro plugin File Mgmt byly úspěšně odstraněny z databáze glFusion<br><br>Budete muset ručně odstranit všechny soubory v úložišti souborů.',
    'uninstall_failed'      => 'Odinstalace se nezdařila.',
    'uninstall_failed_msg'  => 'Instalace pluginu File Mgmt selhala. Pro diagnostické informace si prosím přečtěte soubor glFusion error.log',
    'install_noop'          => 'Plugin installed',
    'install_noop_msg'      => 'Instalace pluginu filemgmt provedena, ale nebylo co dělat.<br><br>Zkontrolujte konfiguraci pluginu v install.cfg.',
    'all_html_allowed'      => 'HTML tagy povoleny',
    'no_new_files'          => 'Nejsou nové soubory',
    'no_comments'           => 'Nejsou nové komentáře',
    'more'                  => '<em>další ...</em>',
    'newly_uploaded'        => 'Nově nahrané',
    'click_to_view'         => 'Klikni pro zobrazení',
    'no_file_uploaded'      => 'Nebyly nahrány žádné soubory',
    'description'           => 'Popis',
    'category'              => 'Kategorie',
    'err_req_fields'        => 'Některá povinná pole nebyla zadána',
    'go_back'               => 'Zpět',
    'err_demomode'          => 'Nahrávání je zakázáno v demo režimu',
    'edit_category'         => 'Edituj kategorii',
    'create_category'       => 'Vytvoř kategorii',
    'can_view'              => 'Může zobrazit',
    'can_upload'            => 'Může nahrávat',
    'delete_category'       => 'Odstranit kategorii',
    'new_category'          => 'Nová kategorie',
    'new_file'              => 'Nový soubor',
    'remote_ip'             => 'Vzdálená IP',
    'back_to_listing'       => 'Zpět na seznam',
    'remote_url'            => 'Vzdálený uživatel',
    'file_missing'          => 'File Missing',
);

$LANG_FILEMGMT_ERRORS = array(
    "1101" => "Chyba oprávnění při nahrávání: Dočasný soubor nebyl nalezen. Zkontrolujte chyby.log",
    "1102" => "Chyba při nahrávání: Dočasný soubor souboru nebyl vytvořen. Zkontrolujte error.log",
    "1103" => "Soubor, které jste zadali, je již v databázi!",
    "1104" => "Informace o souboru jsou nedostatečné - Je třeba zadat název nového souboru",
    "1105" => "Informace o souboru jsou nedostatečné - Je třeba zadat popis nového souboru",
    "1106" => "Chyba při nahrávání: Nový soubor nebyl vytvořen. Zkontrolujte error.log",
    "1107" => "Chyba při nahrávání: Dočasný soubor nebyl vytvořen. Zkontrolujte error.log",
    "1108" => "Duplicitní soubor - již existuje v úložišti souborů",
    "1109" => "Tento typ souboru není povolen",
    "1110" => "Musíte definovat a vybrat kategorii pro nahraný soubor",
    "1111" => "Velikost souboru přesahuje maximální velikost %s",
    "9999" => "Neznámá chyba"
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Odkaz: na stránku s podrobnostmi o stažení souboru. Příkaz  [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Odkaz: k přímému stahování souborů. link_text výchozí je název souboru. použijte: [file_download:<i>file_id</i> {link_text}]',
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'Plugin FileMgmt',
    'title'                 => 'FileMgmt - Konfigurace'
);
$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'Povolit zveřejnění Co je nového',
    'perpage'               => 'Zobrazené množství souborů stažených ze stránky',
    'popular_download'      => 'Množství zobrazení aby se zobrazilo jako populární',
    'newdownloads'          => 'Počet stažení označených jako nové na Top stránce',
    'trimdesc'              => 'Oříznout délku názvu souborů v seznamu',
    'dlreport'              => 'Omezit přístup ke statistice o stažení souborů',
    'selectpriv'            => 'Omezit přístup pouze pro skupinu \'Přihlášení uživatelé\'',
    'uploadselect'          => 'Povolit nahrávání souborů pouze pro přihlášené',
    'uploadpublic'          => 'Povolit nahrávání souborů komukoliv',
    'useshots'              => 'Zobrazit kategorie obrázků',
    'shotwidth'             => 'Šířka náhledu',
    'Emailoption'           => 'E-mail odesilatele, pokud je soubor schválen',
    'FileStore'             => 'Adresář pro ukládání souborů',
    'SnapStore'             => 'Adresář pro ukládání náhledů souborů',
    'SnapCat'               => 'Adresář pro ukládání kategorie náhledů souborů',
    'FileStoreURL'          => 'URL k souborům',
    'FileSnapURL'           => 'URL pro náhledy souborů',
    'SnapCatURL'            => 'URL k kategoriím náhledů',
    'whatsnewperioddays'    => 'Co je nového v období',
    'whatsnewtitlelength'   => 'Co je nového - délka názvu',
    'showwhatsnewcomments'  => 'Zobrazit komentáře v bloku - Co je nového',
    'numcategoriesperrow'   => 'Počet kategorií na řádek',
    'numsubcategories2show' => 'Počet podkategorií na řádek',
    'outside_webroot'       => 'Uložit soubory mimo webový kořenový adresář',
    'enable_rating'         => 'Povolit hodnocení příspěvků',
    'displayblocks'         => 'Zobrazit bloky glFusion',
    'silent_edit_default'   => 'Silent Edit Default',
    'extensions_map'        => 'Extenze souboru použitá pro stahování',
    'EmailOption'           => 'Odesílatel e-mailu při schválení?',
);
$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Hlavní nastavení'
);
$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Veřejné nastavení pluginu FileMgmt',
    'fs_admin'              => 'Správcovské nastavení pluginu FileMgmt',
    'fs_permissions'        => 'Výchozí oprávnění',
    'fm_access'             => 'Nastavení přístupu k pluginu FileMgmt',
    'fm_general'            => 'Obecné nastavení pluginu FileMgmt',
);
// Note: entries 0, 1 are the same as in $LANG_configselects['Core']
$LANG_configSelect['filemgmt'] = array(
    0 => array(1=>'Ano', 0=>'Ne'),
    1 => array(true=>'Ano', false=>'Ne'),
    2 => array(5 => ' 5', 10 => '10', 15 => '15', 20 => '20', 25 => '25',30 => '30',50 => '50'),
    3 => array(0=>'Bloky nalevo', 1=>'Bloky vpravo', 2=>'Bloky nalevo a napravo', 3=>'Žádná')
);

$PLG_filemgmt_MESSAGE1 = 'Instalace pluginu Filemgmt přerušeno<br>Soubor: plugins/filemgmt/filemgmt.php není zapisovatelný';
$PLG_filemgmt_MESSAGE3 = 'Tento modul vyžaduje glFusion verzi 1.0 nebo vyšší, aktualizace byla přerušena.';
$PLG_filemgmt_MESSAGE4 = 'Plugin 1.5 nebyl zjištěn - aktualizace byla zrušena.';
$PLG_filemgmt_MESSAGE5 = 'Aktualizace pluginu Filemgmt přerušena<br>Aktuální verze pluginu není 1.3';

// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Dík za informaci. Brzy se na to podíváme.");
define("_MD_BACKTOTOP","Zpět na přehled stahování");
define("_MD_THANKSFORHELP","Dík za pomoc s údržbou integrity tohoto systému.");
define("_MD_FORSECURITY","Z bezpečnostních důvodů bylo dočasně zaznamenáno tvé uživatelské jméno a IP adresa.");

define("_MD_SEARCHFOR","Hledej (co)");
define("_MD_MATCH","shoda");
define("_MD_ALL","vše");
define("_MD_ANY","cokoli");
define("_MD_NAME","jméno");
define("_MD_DESCRIPTION","popis");
define("_MD_SEARCH","Hledej");

define("_MD_MAIN","Hlavní");
define("_MD_SUBMITFILE","Pošli soubor");
define("_MD_POPULAR","Oblíbený");
define("_MD_POP", "Pop");   // abbrevision for listing badge
define("_MD_NEW","Nový");
define("_MD_TOPRATED","Nejvyšší hodnocení");

define("_MD_NEWTHISWEEK","Nové v tomto týdnu");
define("_MD_UPTHISWEEK","Aktualizované v tomto týdnu");

define("_MD_POPULARITYLTOM","Obliba (hity vzestupně)");
define("_MD_POPULARITYMTOL","Obliba (hity sestupně)");
define("_MD_TITLEATOZ","Názvu (A-Ž)");
define("_MD_TITLEZTOA","Názvu (Ž-A)");
define("_MD_DATEOLD","Datumu (od nejstaršího)");
define("_MD_DATENEW","Datumu (od nejnovějšího)");
define("_MD_RATINGLTOH","Hodnocení (od nejnižšího)");
define("_MD_RATINGHTOL","Hodnocení (od nejvyššího)");

define("_MD_NOSHOTS","Náhled není");
define("_MD_EDITTHISDL","Oprav tento download");

define("_MD_LISTINGHEADING","<b>Výpis souborů: Počet souborů v databázi je %s</b>");
define("_MD_LATESTLISTING","<b>Výpis nejnovějších:</b>");
define("_MD_DESCRIPTIONC","Popis:");
define("_MD_EMAILC","E-mail: ");
define("_MD_CATEGORYC","Kategorie: ");
define("_MD_LASTUPDATEC","Poslední aktualizace: ");
define("_MD_DLNOW","Stáhni!");
define("_MD_VERSION","Ver.");
define("_MD_SUBMITDATE","Datum");
define("_MD_DLTIMES","Stáhnuto %sx");
define("_MD_FILESIZE","Délka souboru");
define("_MD_SUPPORTEDPLAT","Podporované platformy");
define("_MD_HOMEPAGE","Na přehled \"Ke Stažení\"");
define("_MD_HITSC","Hitů: ");
define("_MD_RATINGC","Hodnocení: ");
define("_MD_ONEVOTE","1 hlas");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","Není dostupný");
define("_MD_NUMPOSTS","%s hlasů");
define("_MD_COMMENTSC","Komentář: ");
define ("_MD_ENTERCOMMENT", "Vytvoř první komentář");
define("_MD_RATETHISFILE","Oceň soubor (rating)");
define("_MD_MODIFY","Změň");
define("_MD_REPORTBROKEN","Oznam poškozený soubor");
define("_MD_TELLAFRIEND","Pošli známému");
define("_MD_VSCOMMENTS","Čtených/poslaných komentářů");
define("_MD_EDIT","Editovat");

define("_MD_THEREARE","Počet souborů v databázi: %s");
define("_MD_LATESTLIST","Poslední výpisy");

define("_MD_REQUESTMOD","Žádost o změnu stahování");
define("_MD_FILE","Soubor");
define("_MD_FILEID","ID souboru: ");
define("_MD_FILETITLE","Název: ");
define("_MD_DLURL","Stáhnout URL: ");
define("_MD_HOMEPAGEC","Domovská stránka: ");
define("_MD_VERSIONC","Verze: ");
define("_MD_FILESIZEC","Velikost souboru: ");
define("_MD_NUMBYTES","%s bytů");
define("_MD_PLATFORMC","Platforma: ");
define("_MD_CONTACTEMAIL","Kontaktní e-mail: ");
define("_MD_SHOTIMAGE","Obr.náhledu: ");
define("_MD_SENDREQUEST","Pošli požadavek");

define("_MD_VOTEAPPRE","Váš hlas byl přijat.");
define("_MD_THANKYOU","Díky za váš čas při hlasování na %s"); // %s is your site name
define("_MD_VOTEFROMYOU","Odezva od uživatelů jako jste vy, pomůže ostatním návštěvníkům se lépe rozhodnout co stáhnout.");
define("_MD_VOTEONCE","Prosím nehlasujte pro jeden soubor víc než jednou.");
define("_MD_RATINGSCALE","Rozsah je 1 - 10, 1=nejslabší, 10=vynikající.");
define("_MD_BEOBJECTIVE","Prosím, buďte objektivní, kdy každý dal 1 nebo 10, tak by hodnocení moc užitečný nebyl.");
define("_MD_DONOTVOTE","Nehlasuj pro vlastní soubor.");
define("_MD_RATEIT","Spočti to!");

define("_MD_INTFILEAT","Zajímavý soubor ke stažení na %s"); // %s is your site name
define("_MD_INTFILEFOUND","Na %s je zajímavý soubor"); // %s is your site name

define("_MD_RECEIVED","Dostali jsme Vaši informaci o stažení. Díky!");
define("_MD_WHENAPPROVED","Po schválení obdržíte email.");
define("_MD_SUBMITONCE","Pošli svůj soubor/skript jen jednou.");
define("_MD_APPROVED", "Váš soubor byl schválen");
define("_MD_ALLPENDING","Každá informace o souboru/skriptu je odeslána k ověření.");
define("_MD_DONTABUSE","Uživ. jméno a IP adresa je zaznamenána, tak prosím nezneužívejte systém.");
define("_MD_TAKEDAYS","Může to trvat i několik dní, něž bude soubor/skript přidán do naší databáze.");
define("_MD_FILEAPPROVED", "Váš soubor byl přidán do úložiště souborů");

define("_MD_RANK","Úroveň");
define("_MD_CATEGORY","Kategorie");
define("_MD_HITS","Hitů");
define("_MD_RATING","Hodnocení");
define("_MD_VOTE","Hlasování");

define("_MD_SEARCHRESULT4","Výsledek hledání <b>%s</b>:");
define("_MD_MATCHESFOUND","%s nalezena/y shoda/y.");
define("_MD_SORTBY","Setřiď podle:");
define("_MD_TITLE","Názvu");
define("_MD_DATE","Datumu");
define("_MD_POPULARITY","Popularita");
define("_MD_CURSORTBY","Soubory jsou nyní tříděny podle: ");
define("_MD_FOUNDIN","Nalezeno v:");
define("_MD_PREVIOUS","Předchozí");
define("_MD_NEXT","Další");
define("_MD_NOMATCH","Váš filtr nic nenalezl");

define("_MD_TOP10","%s Top 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Kategorie");

define("_MD_SUBMIT","Pošli");
define("_MD_CANCEL","Stornuj");

define("_MD_BYTES","Bytů");
define("_MD_ALREADYREPORTED","Již jste odeslali chybnou zprávu o tomto dokumentu.");
define("_MD_MUSTREGFIRST","Bohužel nemáte oprávnění pro tuto akci.<br>Nejprve se, prosím, přihlašte!");
define("_MD_NORATING","Nebylo vybráno hodnocení.");
define("_MD_CANTVOTEOWN","Nemůžete hlasovat pro vámi zaslaný soubor.<br>Všechny hlasy jsou zaznamenány a revidovány.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Zaznamenej své hodnocení souboru");
define("_MD_ADMINTITLE","Správa souborů");
define("_MD_UPLOADTITLE","File Mngmt - Přidej nový soubor");
define("_MD_CATEGORYTITLE","Výpis souborů - Kategorie");
define("_MD_DLCONF","Konfigurace stahování");
define("_MD_GENERALSET","Konfigurační nastavení");
define("_MD_ADDMODFILENAME","Přidej nový soubor");
define ("_MD_ADDCATEGORYSNAP", 'Volitelný obrázek:<div style="font-size:8pt;">Pouze kategorie nejvyšší úrovně</div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;">Výška obrázku bude změněna na 50</span>');
define("_MD_ADDMODCATEGORY","<b>Kategorie:</b> Přidat, upravit a odstranit kategorie");
define("_MD_DLSWAITING","Stahování čeká na ověření");
define("_MD_BROKENREPORTS","Zprávy o porušených souborech");
define("_MD_MODREQUESTS","Stáhnout informace o žádosti na modifikaci");
define("_MD_EMAILOPTION","E-mail odesilatele, pokud je soubor schválen: ");
define("_MD_COMMENTOPTION","Komentáře možné:");
define("_MD_SUBMITTER","Uložil: ");
define("_MD_DOWNLOAD","Stáhni si");
define("_MD_FILELINK","Link na soubor");
define("_MD_SUBMITTEDBY","Uložil: ");
define("_MD_APPROVE","Schválil");
define("_MD_DELETE","Smaž");
define("_MD_NOSUBMITTED","Žádné nové soubory ke stahování.");
define("_MD_ADDMAIN","Přidat MAIN kategorii");
define("_MD_TITLEC","Název: ");
define("_MD_CATSEC", "Smí prohlížet: ");
define("_MD_UPLOADSEC", "Smí přidávat: ");
define("_MD_IMGURL","<br>Název souboru obrázku <font size='-2'> (umístěný ve vašem adresáři filemgmt_data/category_snaps - výška obrázku bude změněna na 50)</font>");
define("_MD_ADD","Přidej");
define("_MD_ADDSUB","Přidej SUB-kategorii");
define("_MD_IN","v");
define("_MD_ADDNEWFILE","Přidej nový soubor");
define("_MD_MODCAT","Změň iiategory");
define("_MD_MODDL","Změň info o stažení");
define("_MD_USER","Uživatel");
define("_MD_IP","IP adresa");
define("_MD_USERAVG","Průměrné hodnocení uživateli");
define("_MD_TOTALRATE","Celkové hodnocení");
define("_MD_NOREGVOTES","Žádné hlasování registrovanými uživateli");
define("_MD_NOUNREGVOTES","Žádné hlasování neregistrovanými uživateli");
define("_MD_VOTEDELETED","Data hlasování byla smazána.");
define("_MD_NOBROKEN","Žádné nahlášené poškozené soubory.");
define("_MD_IGNOREDESC","Ignorovat (Ignoruje zprávu a pouze smazat tuto nahlášenou položku</b>)");
define("_MD_DELETEDESC","Odstranit (Deletes <b>nahlášený záznam souboru v úložišti</b>, ale ne skutečný soubor)");
define("_MD_REPORTER","Odesílatel zprávy");
define("_MD_FILESUBMITTER","Odesílatel souboru");
define("_MD_IGNORE","Ignoruj");
define("_MD_FILEDELETED","Soubor smazán.");
define("_MD_FILENOTDELETED","Záznam byl odstraněn, ale soubor nebyl smazán.<p>Více než 1 záznam ukazující na stejný soubor.");
define("_MD_BROKENDELETED","Upozornění na poškozený soubor bylo smazáno.");
define("_MD_USERMODREQ","Stáhnout informace o uživateli se žádosti o modifikaci");
define("_MD_ORIGINAL","Originál");
define("_MD_PROPOSED","Návrh");
define("_MD_OWNER","Vlastník: ");
define("_MD_NOMODREQ","Žádná žádost o úpravu stahování.");
define("_MD_DBUPDATED","Database byla úspěšně aktualizována!");
define("_MD_MODREQDELETED","Požadavek na změnu byl odstraněn.");
define("_MD_IMGURLMAIN",'<div style="font-size:8pt;">Výška obrázku bude změněna na 50 px</div>');
define("_MD_PARENT","Nadřazená kategorie");
define("_MD_SAVE","Ulož změny");
define("_MD_CATDELETED","Kategorie smazána.");
define("_MD_WARNING","VAROVÁNÍ: OPRAVDU chceš tuto kategorii zrušit včetně všech souborů a komentářů?");
define("_MD_YES","Ano");
define("_MD_NO","Ne");
define("_MD_NEWCATADDED","Nová Kategorie byla úspěšně přidána!");
define("_MD_CONFIGUPDATED","Nová konfigurace uložena");
define("_MD_ERROREXIST","CHYBA: The download info you provided is already in the database!");
define("_MD_ERRORNOFILE","CHYBA: V databázi nebyl nalezen záznam o souboru!");
define("_MD_ERRORTITLE","CHYBA: Je třeba zadat NÁZEV!");
define("_MD_ERRORDESC","CHYBA: Je třeba zadat POPIS!");
define("_MD_NEWDLADDED","Nové stažení bylo přidáno do databáze.");
define("_MD_NEWDLADDED_DUPFILE","Pozor: Duplikovaný Soubor. New download added to the database.");
define("_MD_NEWDLADDED_DUPSNAP","Varování: Duplicitní Snap. Přidán nový soubor pro stažení do databáze.");
define("_MD_DLUPDATED", "Soubor byl aktualizován.");
define("_MD_HELLO","Ahoj %s");
define("_MD_WEAPPROVED","Schválili jsme váš příspěvek do naší sekce ke stažení. Název souboru je: ");
define("_MD_THANKSSUBMIT","Díky za váš příspěvek!");
define("_MD_UPLOADAPPROVED","Váš uploadovaný soubor byl schválen");
define("_MD_DLSPERPAGE","Zobrazené počty stažení souborů na stránku: ");
define("_MD_HITSPOP","Nejvíce populární přístupy: ");
define("_MD_DLSNEW","Počet stažení označených jako nové na Top stránce: ");
define("_MD_DLSSEARCH","Počet stažení ve výsledcích hledání: ");
define("_MD_TRIMDESC","Oříznout délku názvu souborů v seznamu: ");
define("_MD_DLREPORT","Omezit přístup ke souhrnné zprávě o stahování");
define("_MD_WHATSNEWDESC","Povolit zveřejnění Co je nového");
define("_MD_SELECTPRIV","Omezit přístup pouze pro skupinu 'Přihlášení uživatelé': ");
define("_MD_ACCESSPRIV","Povolit anonymní přístup: ");
define("_MD_UPLOADSELECT","Povolit nahrávání souborů pouze pro přihlášené: ");
define("_MD_UPLOADPUBLIC","Povolit nahrávání souborů komukoliv: ");
define("_MD_USESHOTS","Zobrazit kategorie obrázků: ");
define("_MD_IMGWIDTH","Šířka náhledu: ");
define("_MD_MUSTBEVALID","Obrázek náhledu musí být platný soubor obrázku v adresáři %s (např. shot.gif). Ponechte prázdný, pokud nemáte soubor s obrázkem.");
define("_MD_REGUSERVOTES","Hlasování registrovanými uživateli (celkem hlasů: %s)");
define("_MD_ANONUSERVOTES","Hlasování neregistrovanými uživateli (celkem hlasů: %s)");
define("_MD_YOURFILEAT","Na %s byl přidán Váš článek"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Navštivte naši sekci stahování v %s");
define("_MD_DLRATINGS","Stáhnout hodnocení (celkem hlasů: %s)");
define("_MD_CONFUPDATED","Konfigurace úspěšně aktualizována!");
define("_MD_NOFILES","Soubory nebyly nalezeny");
define("_MD_APPROVEREQ","* Soubory poslané do označených kat. budou schvalovány");
define("_MD_REQUIRED","* Vyžadované pole");
define("_MD_SILENTEDIT","Editace na pozadí: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Ještě nehodnoceno");
define("_IFNOTRELOAD","Pokus se stránka automaticky neobnoví, klikněte prosím <a href=\"%s\">zde</a>");
define("_GL_ERRORNOACCESS","ERROR: Do této sekce úložiště není přístup");
define("_GL_ERRORNOUPLOAD","ERROR: Nemáte prava pro ukládání souborů");
define("_GL_ERRORNOADMIN","ERROR: Tato funkce je zakázána");
define("_GL_NOUSERACCESS","nemá přístup k úložišti dokumentů");
define("_MD_ERRUPLOAD","Plugin Filemgmt: Nelze nahrát - zkontrolujte oprávnění pro adresáře úložiště souborů");
define("_MD_DLFILENAME","Název souboru: ");
define("_MD_REPLFILENAME","Nahrazující soubor: ");
define("_MD_SCREENSHOT","Snímek obrazovky");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Komentáře jsou ceněny");
define("_MD_CLICK2SEE","Klikni a prohlédni si: ");
define("_MD_CLICK2DL","Klikni a stáhni si: ");
define("_MD_ORDERBY","Seřaď dle: ");
