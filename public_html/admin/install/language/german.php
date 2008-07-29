<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | german.php                                                               |
// |                                                                          |
// | German language file for the glFusion installation script                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
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

// +---------------------------------------------------------------------------+

$LANG_CHARSET = 'iso-8859-15';

// +---------------------------------------------------------------------------+
// | Array Format:                                                             |
// | $LANG_NAME[XX]:  $LANG - variable name                                    |
// |                 NAME  - where array is used                               |
// |                 XX    - phrase id number                                  |
// +---------------------------------------------------------------------------+

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    0 => 'glFusion - Technology Fused with Style',
    1 => 'Hilfe zur Installation',
    2 => 'Technology Fused with Style',
    3 => 'glFuson-Installation',
    4 => 'PHP 4.1.0 benötigt',
    5 => 'Sorry, glFusion benötigt mindestens PHP 4.1.0 (Du hast Version ',
    6 => '). Bitte <a href="http://www.php.net/downloads.php">aktualisiere Deine PHP-Installation</a> oder bitte Deinen Hosting-Provider darum.',
    7 => 'glFusion-Dateien nicht gefunden',
    8 => 'Das Installations-Skript hat einige wichtige glFusion-Dateien nicht gefunden. Wahrscheinlich hast Du diese in andere Verzeichnisse verschoben. Bitte gib hier die Pfade zu den Dateien und Verzeichnissen an:',
    9 => 'Willkommen und Danke, dass Du glFusion gewählt hast!',
    10 => 'Datei/Verzeichnis',
    11 => 'Zugriffsrechte',
    12 => 'Ändern auf',
    13 => 'Derzeit',
    14 => 'Change directory to',
    15 => 'Export of glFusion headlines is switched off. The <code>backend</code> directory was not tested',
    17 => 'User photos are disabled. The <code>userphotos</code> directory was not tested',
    18 => 'Images in articles are disabled. The <code>articles</code> directory was not tested',
    19 => 'glFusion setzt voraus, dass bestimmte Dateien und Verzeichnisse für den Webserver schreibbar sind. Es folgt eine Liste der Dateien und Verzeichnisse, die geändert werden müssen.',
    20 => 'Warnung!',
    21 => 'glFusion und Deine Website werden nicht funktionsfähig sein, solange diese Fehler nicht korrigiert werden. Bitte nimm die notwendigen Änderungen vor, bevor Du mit der Installation fortfährst.',
    22 => 'unbekannt',
    23 => 'Bitte wähle eine Installationsmethode:',
    24 => 'Neuinstallation',
    25 => 'Upgrade',
    26 => 'Unable to modify',
    27 => '. Did you make sure the file is write-able by the web server?',
    28 => 'siteconfig.php. Did you make sure the file is write-able by the web server?',
    29 => 'glFusion Site',
    30 => 'Technology Fused with Style',
    31 => 'Erforderliche Informationen zum Setup',
    32 => 'Name der Website',
    33 => 'Site-Slogan',
    34 => 'Art der Datenbank',
    35 => 'MySQL',
    36 => 'MySQL mit Support für InnoDB-Tabellen',
    37 => 'Microsoft SQL',
    38 => '',
    39 => 'Datenbank-Server',
    40 => 'Name der Datenbank',
    41 => 'Datenbank-Username',
    42 => 'Datenbank-Passwort',
    43 => 'Präfix für Tabellen',
    44 => 'Optionale Einstellungen',
    45 => 'URL der Website',
    46 => '(ohne Slash am Ende)',
    47 => 'Pfad für das "admin"-Verzeichnis',
    48 => 'Website-Email-Adresse',
    49 => '"No Reply"-Email-Adresse',
    50 => 'Installieren',
    51 => 'MySQL 3.23.2 benötigt',
    52 => 'Sorry, but glFusion requires at least MySQL 3.23.2 to run (you have version ',
    53 => '). Please <a href="http://dev.mysql.com/downloads/mysql/">upgrade your MySQL</a> install or ask your hosting service to do it for you.',
    54 => 'Incorrect database information',
    55 => 'Sorry, but the database information you entered does not appear to be correct. Please go back and try again.',
    56 => 'Could not connect to database',
    57 => 'Sorry, but the installer could not find the database you specified. Either the database does not exist or you misspelled the name. Please go back and try again.',
    58 => '. Did you make sure the file is write-able by the web server?',
    59 => 'Hinweis:',
    60 => 'InnoDB tables are not supported by your version of MySQL. Would you like to continue the installation without InnoDB support?',
    61 => 'zurück',
    62 => 'weiter',
    63 => 'An installed glFusion database already exists. The installer will not allow you to run a fresh install on an existing glFusion database. To continue you must do one of the following:',
    64 => 'Delete the tables from the existing database. Or simply drop the database and recreate it. Then click "Retry" below.',
    65 => 'Perform an upgrade on your database (to a newer glFusion version) by selecting the "Upgrade" option below.',
    66 => 'Retry',
    67 => 'Error Setting up the glFusion Database',
    68 => 'The database is not empty. Please drop all tables in the database and start again.',
    69 => 'Upgrading glFusion',
    70 => 'Before we get started it is important that you back up your database current glFusion files. This installation script will alter your glFusion database so if something goes wrong and you need to restart the upgrade process, you will need a backup of your original database. YOU HAVE BEEN WARNED!',
    71 => 'Please make sure to select the correct glFusion version you are coming from below. This script will do incremental upgrades after this version (i.e. you can upgrade directly from any old version to ',
    72 => ').',
    73 => 'Please note this script will not upgrade any beta or release candidate versions of glFusion.',
    74 => 'Database already up to date!',
    75 => 'It looks like your database is already up to date. You probably ran the upgrade before. If you need to run the upgrade again, please re-install your database backup and try again.',
    76 => 'Select Your Current glFusion Version',
    77 => 'The installer was unable to determine your current version of glFusion, please select it from the list below:',
    78 => 'Upgrade Error',
    79 => 'An error occured while upgrading your glFusion installation.',
    80 => 'Ändern',
    81 => 'Stop!',
    82 => 'Es ist unbedingt nötig, die Zugriffsrechte der unten aufgeführten Dateien zu ändern. Andernfalls wirst Du glFusion nicht installieren können.',
    83 => 'Installation Error',
    84 => 'The path "',
    85 => '" does not appear to be correct. Please go back and try again.',
    86 => 'Sprache',
    87 => 'http://www.gllabs.org',
    88 => 'Change directory and containing files to',
    89 => 'Aktuelle Version:',
    90 => 'Leere Datenbank?',
    91 => 'It appears that either your database is empty or the database credentials you entered are incorrect. Or maybe you wanted to perform a New Install (instead of an Upgrade)? Please go back and try again.',
    92 => 'Benutze UTF-8'
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Installation erfolgreich',
    1 => 'Installation von glFusion ',
    2 => ' abgeschlossen!',
    3 => 'Glückwunsch, Du hast glFusion erfolgreich ',
    4 => '. Bitte nimm Dir einen Moment Zeit, um die unten stehenden Informationen zu lesen.',
    5 => 'Um Dich in Deine neue glFusion-Site einzuloggen, benutze bitte diesen Account:',
    6 => 'Username:',
    7 => 'Admin',
    8 => 'Passwort:',
    9 => 'password',
    10 => 'Sicherheitshinweis',
    11 => 'Bitte vergiss nicht, die folgenden ',
    12 => ' Dinge zu tun',
    13 => 'Das Installationsverzeichnis löschen oder umbenennen:',
    14 => 'Das Passwort für den Account ',
    15 => 'ändern.',
    16 => 'Die Zugriffsrechte für',
    17 => 'und',
    18 => 'zurücksetzen auf',
    19 => '<strong>Note:</strong> Because the security model has been changed, we have created a new account with the rights you need to administer your new site.  The username for this new account is <b>NewAdmin</b> and the password is <b>password</b>',
    20 => 'installiert',
    21 => 'aktualisiert'
);

// +---------------------------------------------------------------------------+
// help.php

$LANG_HELP = array(
    0 => 'glFusion Installation Support',
    1 => 'The name of your website.',
    2 => 'A simple description of your website.',
    3 => 'glFusion can be installed using a MySQL database.<br><br><strong>Hinweis:</strong> InnoDB-Tabellen können zu besserer Performance auf (sehr) großen Websites führen, machen den Backup-Prozess aber komplizierter.',
    4 => 'The network name (or IP address) of your database server. This is typically "localhost". If you are not sure contact your hosting provider.',
    5 => 'The name of your database. If you are not sure what this is contact your hosting provider.',
    6 => 'Your database user account. If you are not sure what this is contact your hosting provider.',
    7 => 'Your database account password. If you are not sure what this is contact your hosting provider.',
    8 => 'Some users want to install multiple copies of glFusion on the same database. In order for each copy of glFusion to function correctly it must have its own unique table prefix (i.e. gl1_, gl2_, etc).',
    9 => 'Make sure this is the correct URL to your site, i.e. to where glFusion\'s <code>index.php</code> file resides (no trailing slash).',
    10 => 'Some hosting services have a preconfigured admin directory. In that case, you need to rename glFusion\'s admin directory to something like "myadmin" and change the following URL as well. Leave as is until you experience any problems accessing glFusion\'s admin menu.',
    11 => 'This is the return address for all email sent by glFusion and contact info displayed in syndication feeds.',
    12 => 'This is the sender\'s address of emails sent by the system when users register, etc. This should be either the same as Site Email or a bouncing address to prevent spammers from getting your email address by registering on the site. If this is NOT the same as above, there will be a message in sent messages that replying to those emails is recommended.',
    13 => 'Indicate whether to use UTF-8 as the default character set for your site. Recommended especially for multi-lingual setups.'
);

?>