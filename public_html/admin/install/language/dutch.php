<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | dutch.php                                                                |
// |                                                                          |
// | Dutch language file voor het glFusion installatiescript                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Theo Claassen          tc AT i2work DOT com                              |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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
// | Dit programma is gratis software; je mag het ditribueren en/of           |
// | aanpassen onder de condities van GNU General Public License              |
// | zoals gepubliseerd door de Free Software Foundation; vanaf versie 2      |
// | van de licentie of (at your option) elke latere versie.                  |
// |                                                                          |
// | Dit programma is gedistribueerd in de hoop dat het zinvol is,            |
// | maar ZONDER ENIGE GARANTIE; zelfs zonder de impliciete garantie          |
// | MERCHANTABILITY or FITNESS voor particulier gebruik.  Zie de             |
// | GNU General Public License voor meer details.                            |
// |                                                                          |
// | Je hebt als het goed is een kopie ontvangen van de GNU General Public    |
// | License dat samengevoegd is met dit programma; indien niet dan schrijf   |
// | naar de Free Software Foundation, Inc., 59 Temple Place - Suite 330,     |
// | Boston, MA  02111-1307, USA.                                             |
// |                                                                          |
// +--------------------------------------------------------------------------+

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

// +---------------------------------------------------------------------------+

$LANG_CHARSET = 'iso-8859-1';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'back_to_top' => 'Terug naar het begin',
    'calendar' => 'Laden van Kalender Plugin?',
    'calendar_desc' => 'Online kalender / event systeem. Inclusief een website brede kalender en prive kalender voor website users.',
    'connection_settings' => 'Verbindings instellingen',
    'content_plugins' => 'Inhoud & Plugins',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> is gratis software uitgegeven onder de <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 Licentie.</a>',
    'core_upgrade_error' => 'Een fout is opgetreden gedurende de basis/ kern upgrade.',
    'correct_perms' => 'Corrigeer a.u.b. het geindentificeerde probleem onderin. Eenmaal gecorrigeerd, gebruik de <b>Recheck</b> button voor validatie van de omgeving.',
    'current' => 'Huidige',
    'database_exists' => 'De database bevat al glFusion tabellen. Verwijder a.u.b. de glFusion tabellen voordat je begint met een nieuwe installatie.',
    'database_info' => 'Database Informatie',
    'db_hostname' => 'Database Hostnaam',
    'db_hostname_error' => 'Database Hostnaam mag niet leeg zijn.',
    'db_name' => 'Database Naam',
    'db_name_error' => 'Database Naam mag niet leeg zijn.',
    'db_pass' => 'Database Password / wachtwoord',
    'db_table_prefix' => 'Database Tabel Prefix',
    'db_type' => 'Database Type',
    'db_type_error' => 'Database Type moet geselecteerd zijn',
    'db_user' => 'Database Gebruikers naam',
    'db_user_error' => 'Database Gebruikers naam mag niet leeg zijn.',
    'dbconfig_not_found' => 'Kan het bestand/file db-config.php of db-config.php.dist niet vinden. Verifieer a.u.b. dat het juiste pad is ingevuld verwijzend naar je prive directory.',
    'dbconfig_not_writable' => 'Het db-config.php bestand is niet beschrijfbaar. Verifieer a.u.b. dat de web server voldoende rechten (permissies) heeft om in het bestand te schrijven.',
    'directory_permissions' => 'Directory Permissie / rechten',
    'enabled' => 'Geactiveerd / mogelijk gemaakt',
    'env_check' => 'Omgeving (instelling) controle',
    'error' => 'Foutmelding',
    'file_permissions' => 'Bestand Permissies / rechten',
    'file_uploads' => 'Vele functies/onderdelen van glFusion vereisen de mogelijkheid om bestanden te uploaden, dit moet mogelijk zijn.',
    'filemgmt' => 'Laden van FileMgmt Plugin?',
    'filemgmt_desc' => 'Bestand download Manager. Een eenvoudige manier om bestanden te downloaden, georganiseerd in categoriën.',
    'filesystem_check' => 'Bestandssysteem controle',
    'forum' => 'Laden van Forum Plugin?',
    'forum_desc' => 'Een online community forum systeem. Verschaft community collaboratie (samenwerking) en gezamenlijke interactie.',
    'hosting_env' => 'Hosting omgeving/instellingen controle',
    'install' => 'Installatie',
    'install_heading' => 'glFusion Installatie',
    'install_steps' => 'INSTALLATIE STAPPEN (fasen)',
    'language' => 'Taal',
    'language_task' => 'Taal & Taak',
    'libcustom_not_writable' => 'lib-custom.php is niet schrijfbaar.',
    'links' => 'Laden van Links Plugin?',
    'links_desc' => 'Een links management systeem. Links naar andere interressante  sites, georganiseerd in categoriën.',
    'load_sample_content' => 'Laden van voorbeeld Site Inhoud?',
    'mbstring_support' => 'It is recommended that you have the multi-byte string extension loaded (enabled). Without multi-byte string support, some features will be automatically disabled. Specifically, the File Browser in the story WYSIWYG editor will not work.',
    'mediagallery' => 'Laden van Media Gallery Plugin?',
    'mediagallery_desc' => 'Een multi-media management systeem. Kan gebruikt worden als een eenvoudige  photo gallerie of al een robuust media management systeem voor audio, video, en foto\'s.',
    'memory_limit' => 'Aanbevolen wordt om minimaal 48M RAM (geheugen) beschikbaar te hebben voor je site.',
    'missing_db_fields' => 'Voer a.u.b. alle vereiste database velden in.',
    'new_install' => 'Nieuwe Installatie',
    'next' => 'Volgende',
    'no_db' => 'Database bestaat schijnbaar nog niet.',
    'no_db_connect' => 'Niet in staat om met database verbinding te maken',
    'no_innodb_support' => 'Je hebt MySQL met InnoDB geselecteerd maar jou database ondersteund geen InnoDB indexatie.',
    'no_migrate_glfusion' => 'Je kunt geen bestaande glFusion site migreren. Kies a.u.b. dan voor de  Upgrade optie.',
    'none' => 'Geen',
    'not_writable' => 'NIET SCHRIJFBAAR',
    'notes' => 'Notes',
    'off' => 'Uit',
    'ok' => 'OK',
    'on' => 'Aan',
    'online_help_text' => 'Online installatie help<br /> at glFusion.org',
    'online_install_help' => 'Online Installatie Help',
    'open_basedir' => 'Als <strong>open_basedir</strong> restricties zijn toegepast op je site, kan dit permissie (rechten) problemen veroorzaken gedurende de installatie. De Bestandsysteem Controle (Check) verderop geeft mogelijke issues aan.',
    'path_info' => 'Pad voor Informatie',
    'path_prompt' => 'Pad naar prive / directory',
    'path_settings' => 'Pad settings',
    'perform_upgrade' => 'Uitvoeren Upgrade',
    'php_req_version' => 'glFusion vereist PHP versie 5.3.0 of nieuwer.',
    'php_settings' => 'PHP Settings',
    'php_version' => 'PHP Versie',
    'php_warning' => 'Indien één van de items hieronder zijn gemarkeerd <span class="no">rood</span>, kun je problemen tegenkomen op je glFusion site. Controleer met je hosting provider of de informatie klopt of dat deze PHP instellingen gewijzigd kunnen worden.',
    'plugin_install' => 'Plugin Installatie',
    'plugin_upgrade_error' => 'Er was een probleem upgrading de %s plugin, controleer de error.log voor meer details.<br />',
    'plugin_upgrade_error_desc' => 'De volgende plugins zijn niet ge-upgrade. Controleer de error.log voor meer details.<br />',
    'polls' => 'Laden van Polls Plugin?',
    'polls_desc' => 'Een online stemsysteem (opinie). Verschaft een opinie overzicht voor je website bezoekers die stemmen op bepaalde onderwerpen.',
    'post_max_size' => 'glFusion staat toe om plugins te uploaden, plaatjes, fotos en bestanden. Je moet in iedergeval minimaal 8M reserveren voor de maximale post grootte.',
    'previous' => 'Terug',
    'proceed' => 'Verder',
    'recommended' => 'Aanbevolen',
    'register_globals' => 'Indien PHP\'s <strong>register_globals</strong> is toegestaan, kunnen mogelijk veiligheidsrisico\'s onstaan.',
    'safe_mode' => 'Indien PHP\'s <strong>safe_mode</strong> is toegestaan, sommige functies van glFusion kunnen dan niet meer goed werken. Specifiek de Media Gallery plugin.',
    'samplecontent_desc' => 'Indien gecontroleerd, installatie van voorbeeld content zoals blocks, stories, en statitsche pagina\'s. <strong> Dit is aanbevolen voor nieuwe gebruikers.</strong>',
    'select_task' => 'Selecteer Taak',
    'session_error' => 'Jou sessie is verlopen. Start de installatieprocedure opnieuw a.u.b.',
    'setting' => 'Instellingen',
    'site_admin_url' => 'Site Admin URL',
    'site_admin_url_error' => 'Site Admin URL mag niet leeg zijn.',
    'site_email' => 'Site Email',
    'site_email_error' => 'Site Email mag niet leeg zijn.',
    'site_email_notvalid' => 'Site Email is geen valide email adres.',
    'site_info' => 'Site Informatie',
    'site_name' => 'Site Naam',
    'site_name_error' => 'Site Naam mag niet leeg zijn.',
    'site_noreply_email' => 'Site No Reply Email',
    'site_noreply_email_error' => 'Site No Reply Email mag niet leeg zijn.',
    'site_noreply_notvalid' => 'No Reply Email is geen valide email adres.',
    'site_slogan' => 'Site Slogan',
    'site_upgrade' => 'Upgrade van een bestaande glFusion Site',
    'site_url' => 'Site URL',
    'site_url_error' => 'Site URL mag niet leeg zijn.',
    'siteconfig_exists' => 'Een bestaande siteconfig.php bestand is gevonden. Verwijder a.u.b. dit bestande voordat je verder gaat met deze nieuwe installatie.',
    'siteconfig_not_found' => 'Niet mogelijk om het bestand siteconfig.php te vinden, weet je zeker dat dit een upgrade is?',
    'siteconfig_not_writable' => 'Het bestand siteconfig.php is niet beschrijfbaar of de directory waar siteconfig.php is opgeslagen is niet schrijfbaar. Corrigeer het probleem a.u.b. voordat je verder gaat.',
    'sitedata_help' => 'Selecteer het type van de database dat je gebruikt uit de drop down lijst. Dit is in het algemeen <strong>MySQL</strong>. Selecteer ook of je gebruik maakt van de <strong>UTF-8</strong> karakter set (dit is in het algemeen gecontroleerd bij meervoudige taal sites.)<br /><br /><br />Geeft de  hostnaam van de database server. Dit mag niet dezelfde zijn als je web server, controleer met je hosting provider voor alle zekerheid.<br /><br />Voer de naam van je database in. <strong>De database moet al bestaan.</strong> Indien je niet de naam van je database weet neem contact op met je hosting provider.<br /><br />Voer de gebruikersnaam in om verbinding te leggen met je database. Indien je niet de gebruikersnaam van de database weet neem contact op met je hosting provider.<br /><br /><br />Voer het wachtwoord in om verbinding te maken met de database. Indien je niet de database wachtwoord weer neem contact op met je hosting provider.<br /><br />Voer de tabel prefix die gebruikt gaat worden voor de database tabellen. Dit is bruikbaar bij verschillende afzonderlijke sites of systemen als een enkelvoudige database wordt gebruikt.<br /><br />Voer de naam van je site in. Dit zal worden weergegeven in de site header. Bijvoorbeeld, glFusion of Mark\'s Marbles. Verontrust niet dit kan achteraf altijd gewijzigd worden.<br /><br />Voer de slogan van je site in. Dit wordt weergegeven in desite header net onder de site naam. Bijvoorbeeld, synergy - stability - style. Verontrust niet dit kan achteraf altijd gewijzigd worden.<br /><br />Voor je site\'s hoofd email adres in. Dit is het email adres voor het standaard Admin account. Verontrust niet dit kan achteraf altijd gewijzigd worden.<br /><br />Voer jesite\'s geen antwoord (no reply) email adres in. Dit wordt automatisch gebruikt bij het versturen van nieuwe gebruiks, wachtwoord reset of elke andere e-mail notificatie. Verontrust niet dit kan achteraf altijd gewijzigd worden.<br /><br />Bevestig a.u.b. dat dit web adres of URL die gebruikt wordt om toegang te krijgen tot de homepagina van je site.<br /><br /><br />Bevestig a.u.b. dat dit web adres of URL die gebruikt wordt om toegang te krijgen tot de admin sectie van je site.',
    'sitedata_missing' => 'De volgende problemen zijn gedetecteerd met de site gegegevens die je hebt ingevoerd',
    'system_path' => 'Pad instellingen',
    'unable_mkdir' => 'Kan geen directory creeëren',
    'unable_to_find_ver' => 'kan niet glFusion versie bepalen.',
    'upgrade_error' => 'Upgrade Foutmelding',
    'upgrade_error_text' => 'Een fout is opgetreden error tijdens upgrading van de glFusion installatie.',
    'upgrade_steps' => 'UPGRADE STAPPEN',
    'upload_max_filesize' => 'glFusion staat toe om plugins, images, en bestanden te uploaden. Er dient minimaal 8M beschikbaar te zijn als upload grootte.',
    'use_utf8' => 'Gebruik UTF-8',
    'welcome_help' => 'Welkom bij de glFusion CMS Installatie Wizard. Je kunt een nieuwe glFusion site installatie uitvoeren, een upgrade van een bestaande glFusion site doen.<br /><br />Selecteer a.u.b. een taal voor de wizard en een taak die mote worden uitgevoerd en klik daarna op <strong>Volgende</strong>.',
    'wizard_version' => 'v%s Installatie Wizard',
    'system_path_prompt' => 'Voer het volledige pad in, het zogenaamde absolute pad op je server naar glFusion\'s <strong>private/</strong> directory.<br /><br />Deze directory bevat de  <strong>db-config.php.dist</strong> of <strong>db-config.php</strong> bestand.<br /><br />Voorbeelden: /home/www/glfusion/private of c:/www/glfusion/private.<br /><br /><strong>Hint:</strong> Het absolute pad naar je <strong>public_html/</strong> <i>(niet <strong>private/</strong>)</i> directory schijnt te zijn:<br /><br />%s<br /><br /><strong>Geavanceerde Instellingen</strong> staan jou toe om standaard instellingen te overschrijven.  Over het algemeen hoef jij deze instellingen niet aan te passen of te wijzigen, het systeem bepaald dat automatisch voor jou.',
    'advanced_settings' => 'Geavanceerde settings',
    'log_path' => 'Logs-pad',
    'lang_path' => 'Taal-pad',
    'backup_path' => 'Backup-pad',
    'data_path' => 'Data-pad',
    'language_support' => 'Taal ondersteuning',
    'language_pack' => 'glFusion heeft als standaard instelling Engels, na de installatie kun je via de link <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">een taal pakket Pack</a> downloaden en installeren die alle tot nu toe geondersteunde talen bevat.',
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
    'install_header' => 'Before installing glFusion, you will need to know a few key pieces of information. Write down the following information. If you are unsure what to put for each of the items below, please contact your system administrator or you hosting provider.',
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
    'upgrade_bullet_title' => 'It is recommended that yo do the following:',
    'cleanup' => 'Obsolete File Removal',
    'obsolete_confirm' => 'File Cleanup Confirmation',
    'remove_skip_warning' => 'Are you sure you want to skip removing the obsolete files? These files are no longer needed and should be removed for security reasons. If you choose to skip the automatic removal, please consider removing them manually.',
    'removal_failure' => 'Removal Failures',
    'removal_fail_msg' => 'You will need to manually delete the files below. See the <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Obsolete Files</a> for a detailed list of files to remove.',
    'removal_success' => 'Obsolete Files Deleted',
    'removal_success_msg' => 'All obsolete files have been successfully removed. Select <b>Complete</b> to finish the upgrade.',
    'remove_obsolete' => 'Remove Obsolete Files',
    'remove_instructions' => '<p>With each release of glFusion, there are files that are updated and in some cases removed from the glFusion system. From a security perspective, it is important to remove old, unused files. The Upgrade Wizard can remove the old files, if you wish, otherwise you will need to manually delete them.</p><p>If you wish to manually delete the files - please check the <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Obsolete Files</a> to get a list of obsolete files to remove. Select <span class="uk-text-bold">Skip</span> below to complete the upgrade process.</p><p>To have the Upgrade Wizard automatically delete the files, please select <b>Delete Files</b> below to complete the upgrade.',
    'complete' => 'Complete',
    'delete_files' => 'Delete Files',
    'cancel' => 'Cancel',
    'show_files_to_delete' => 'Show Files to Delete',
    'skip' => 'Skip',
    'no_utf8' => 'You have selected to use UTF-8 (which is recommended), but the database is not configured with a UTF-8 collation. Please create the database with the proper UTF-8 collation. Please see the <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Database Setup Guide</a> in the glFusion Documentation Wiki for more information.',
    'no_check_utf8' => 'You have not selected to use UTF-8 (which is recommended), but the database is configured with a UTF-8 collation. Please select UTF-8 option on install screen. Please see the <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Database Setup Guide</a> in the glFusion Documentation Wiki for more information.',
    'ext_installed' => 'Installed',
    'ext_missing' => 'Missing',
    'ext_required' => 'Required',
    'ext_optional' => 'Optional',
    'ext_required_desc' => 'must be installed in PHP',
    'ext_optional_desc' => 'should be installed in PHP - Missing extension could impact some features of glFusion.',
    'ext_good' => 'properly installed.',
    'ext_heading' => 'PHP Extensions'
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Installatie is compleet',
    1 => 'Installatie van glFusion ',
    2 => ' compleet!',
    3 => 'Gefeliciteerd, je was succesvol ',
    4 => ' glFusion. Neem een paar minuten leestijd voor de weergegeven informatie.',
    5 => 'Om in te loggen op je nieuwe glFusion site, gebruik a.u.b. dit account:',
    6 => 'Gebruikersnaam:',
    7 => 'Admin',
    8 => 'Wachtwoord:',
    9 => 'password',
    10 => 'Veiligheidswaarschuwing',
    11 => 'Vergeet niet om te doen',
    12 => 'dingen',
    13 => 'Verwijder of hernoem de installatie directory,',
    14 => 'Wijzig de',
    15 => 'account wachtwoord.',
    16 => 'Zet permissies aan',
    17 => 'en',
    18 => 'terug naar',
    19 => '<strong>Notitie:</strong> Omdat het veiligheidsmodel is gewijzigd hebben we een nieuw account gecreërd met de rechten die je nodig hebt om je nieuwe site te beheren. De gebruikersnaam voor dit nieuwe account is <b>NewAdmin</b> en het wachtwoord is <b>password</b>',
    20 => 'geïnstalleerd',
    21 => 'upgraded'
);

?>