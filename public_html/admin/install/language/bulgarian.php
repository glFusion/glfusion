<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | english.php                                                              |
// |                                                                          |
// | English language file for the glFusion installation script               |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

// +---------------------------------------------------------------------------+

$LANG_CHARSET = 'windows-1251';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'back_to_top' => 'Най Отгоре',
    'calendar' => 'Календар Plugin?',
    'calendar_desc' => 'Мулти Функционален календар / Това е календар със възможност да се добавят сабития,подредени по дириктории.',
    'connection_settings' => 'Настройки на връзката',
    'content_plugins' => 'Настройка на съдържанието',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> е безплатен софтуер и е пуснат под правата на <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 License.</a>',
    'core_upgrade_error' => 'Грешка при опит за обновяване.',
    'correct_perms' => 'Моля поправете проблемите отбелязани отдолу. Когато бъдат корегирани, Използвайте <b>Recheck</b> бутона за да потвърдите.',
    'current' => 'Тъкошен',
    'database_exists' => 'Датабазата вече има glfusion таблаци. Моля премахнете таблиците преди нова инсталация.',
    'database_info' => 'Database Информация',
    'db_hostname' => 'Database Хост име',
    'db_hostname_error' => 'Database Полето за хоста не може да е празно.',
    'db_name' => 'Database Име',
    'db_name_error' => 'Database Полето за името не може да е празно.',
    'db_pass' => 'Database Парола',
    'db_table_prefix' => 'Database Наставка на таблиците(пример:glfusion_site)',
    'db_type' => 'Database Тип',
    'db_type_error' => 'Database Трябва да има избран тип',
    'db_user' => 'Database Потребител',
    'db_user_error' => 'Database Полето за Потребителското име не може да е празно.',
    'dbconfig_not_found' => 'Не може да намери db-config.php или db-config.php.dist файла. Моля оверете се че сте вавели правилно линка към дирикторията.',
    'dbconfig_not_writable' => 'Не може да се пише върху db-config.php. Моля проверете дали сървара има права да пише върху този файл.',
    'directory_permissions' => 'Права за дирикториите',
    'enabled' => 'Вклучено',
    'env_check' => 'Проверка на средата',
    'error' => 'Грешка',
    'file_permissions' => 'Права за файловете',
    'file_uploads' => 'Много ехтри на glFusion изискват способноста да се качват файлове, това би трябвало да е позволено.',
    'filemgmt' => 'Зареди FileMgmt Plugin?',
    'filemgmt_desc' => 'File Download Manager. Лесен начин да се сдобиите със файлове за свалне,подредени по категории.',
    'filesystem_check' => 'Проверка на файловата система',
    'forum' => 'Зареди Forum Plugin?',
    'forum_desc' => 'Онлине форумна система. Предлага на общността сътрудничество и интерактивност.',
    'geeklog_migrate' => 'Мигрирай във Geeklog v1.5+ Site',
    'hosting_env' => 'Проверка на среда за хостинг',
    'install' => 'Инсталирай',
    'install_heading' => 'glFusion Инсталация',
    'install_steps' => 'Стъпки на инсталация',
    'invalid_geeklog_version' => 'Инсталатора не можа да намери siteconfig.php. Сигорни ли сте че мигрирате от Geeklog v1.4.1 или по горна версия?  Ако името по стара Geeklog инсталация, моля обновеете до Geeklog v1.4.1 и пробвайте отново.',
    'language' => 'Език',
    'language_task' => 'Език и Задачи',
    'libcustom_not_writable' => 'lib-custom.php Няма права за писане.',
    'links' => 'Зареди Links Plugin?',
    'links_desc' => 'Система за подреждане на линковеете. Предлага линкове към други интересни сайтове,подредени по категории.',
    'load_sample_content' => 'Зареди Sample Site Content?',
    'mediagallery' => 'Зареди Media Gallery Plugin?',
    'mediagallery_desc' => 'Мулти-медийна система. Може да бъде използвана като проста фото галерия или система която поддържа видео,аудио и картини.',
    'memory_limit' => 'It is recommended that you have at least 48M of memory enabled on your site.',
    'missing_db_fields' => 'Моля попълнете всички полета нъжни за датабазата.',
    'new_install' => 'Нова Инсталация',
    'next' => 'Следващо',
    'no_db' => 'Изглежда че такава датабаза няма.',
    'no_db_connect' => 'Не може да се свърже към датабазата',
    'no_innodb_support' => 'Избрали сте MySQL със InnoDB но вашата датабаза не поддържа InnoDB.',
    'no_migrate_glfusion' => 'Не можете да мигрирате glFusion сайт. Моля изберете обновяващта опция..',
    'none' => 'Няма',
    'not_writable' => 'НЯМА ПРАВА ЗА ПИСАНЕ',
    'notes' => 'Notes',
    'off' => 'икзлучен',
    'ok' => 'добре',
    'on' => 'Вклучен',
    'online_help_text' => 'Онлине инсталационна помощ<br /> на glFusion.org',
    'online_install_help' => 'Онлине инсталационна помощ',
    'open_basedir' => 'Ако <strong>open_basedir</strong> ограниченията са позволени на вашия сайт, то тогава може да пречини проблеми по време на инсталацията.Проверката на файловеете долу трябва да покаже дали има грешки.',
    'path_info' => 'Информация за пътя',
    'path_prompt' => 'Път към private/ дериктория',
    'path_settings' => 'Настройки на пътищата',
    'perform_upgrade' => 'Направи обновяване',
    'php_req_version' => 'glFusion изисква PHP версия 4.3.0 или по нова.',
    'php_settings' => 'PHP Настройки',
    'php_version' => 'PHP Версия',
    'php_warning' => 'Ако някой от нещата са отбелязани със <span class="no">червено</span>, то тогава може да срещнете проблеми със glFusion site.  Проверете вашия Хост представител дали има промяна във някой от тези PHP настройки.',
    'plugin_install' => 'Инсталация на Добавка',
    'plugin_upgrade_error' => 'Имаше проблем при инсталацията на  %s добавката, Моля проверете error.log за повече подробности.<br />',
    'plugin_upgrade_error_desc' => 'Следните добавки ня бяха обновени. Моля вижте error.log за повече информация.<br />',
    'polls' => 'Зареди Polls Plugin?',
    'polls_desc' => 'Онлине анкетна система. Предлага анкета за вашия сайт,обсъждайте важни теми.',
    'post_max_size' => 'glFusion има вазможност за качване на добавки, картини, и други файлове. Трябва да позволите поне 8MB за махималния размер.',
    'previous' => 'Предишен',
    'proceed' => 'Продължи',
    'recommended' => 'Препоръчва',
    'register_globals' => 'Ако PHP\'s <strong>register_globals</strong> е позволен, то тогава може да пречини проблеми със защитата.',
    'safe_mode' => "Ако PHP's <strong>safe_mode</strong> е позволен, някоtranslating\n функции на glFusion може да не работят правилно. Специално за Медиа-Галерията.",
    'samplecontent_desc' => "Ако е проверено, инсталираtranslating\nте прости нещта като блокове,истории,и статични страници.<strong>Това е препоръка за нови потребители на glFusion.</strong>",
    'select_task' => 'Избери задача',
    'session_error' => "Session-а беше изчерпан.  Моля рестартирай\nте инсталациония процес.",
    'setting' => 'Настройка',
    'site_admin_url' => 'Администраторски линк (URL)',
    'site_admin_url_error' => 'Полето за администраторския линк не може да е празен.',
    'site_email' => 'Email на Сайта',
    'site_email_error' => 'Полето за Email на сайта не може да е празно.',
    'site_email_notvalid' => 'Email на сайта не е валиден email адрес.',
    'site_info' => 'Информация за сайта',
    'site_name' => 'Име на Сайта',
    'site_name_error' => 'Полето за името на сайта не може да бъде празно.',
    'site_noreply_email' => 'Site No Reply Email',
    'site_noreply_email_error' => 'Полето за Site No Reply Email не може да бъде празно.',
    'site_noreply_notvalid' => 'No Reply Email не е валиден email адрес.',
    'site_slogan' => 'Информация за Сайта',
    'site_upgrade' => 'Обнови вече съществуващ glFusion сайт',
    'site_url' => 'Адрес на Сайта',
    'site_url_error' => 'Полето за адреса на сайта не може да бъде празно.',
    'siteconfig_exists' => 'Беше намерен съществуващ siteconfig.php файл. Моля изтрийтего преди да правите нова инсталация.',
    'siteconfig_not_found' => 'Не може да се намери siteconfig.php, сигорнилисте че това е upgrade?',
    'siteconfig_not_writable' => 'Върху този siteconfig.php не може да се пише , или дерикторията кадето е siteconfig.php няма права за писане. Моля поправете грешките преди да продължите.',
    'sitedata_help' => 'Изберете тип-а на датабазата от листа. Тов е главния <strong>MySQL</strong>. Както и изберете каде да бъде използван <strong>UTF-8</strong> character set (това ще е главно нужно за сайтове със мулти-езици.)<br /><br /><br />Въведете името на вашия съврвер със датабазата. Това може да не е същия нет сървер, затова проверете вашия доставчик на хостинг ако несте сигорни.<br /><br />Въведете името на вашата датабаза. <strong>Има датабаза със такова име.</strong> Ако не знаете името на датабазата , свържете се със вашия хостинг доставчик.<br /><br />Въведете потребителя за да се свържете към датабазата. Ако не знаете потребителското име на датабазата, свържете се със вашия хостинг доставчик.<br /><br /><br />Въведете паролата към вашата датабаза. Ако не знаете паролата към датабазата, свържете се със вашия хостинг доставчик.<br /><br />Въведете наставката за таблиците във датабазата. Това е полезно когато имате мулти-сайтове или имате повече от една системи във една датабаза.<br /><br />Въведете името на сайта ви. То ще бъде показано като заглавието на сайта. Пример, glFusion или Mark\'s Marbles. Не се безпокойте ако объркате, то винаги може да се смени по късно.<br /><br />Въведете информацията за вашия сайт. Тя ще бъде показана под заглавието на сайта. Пример, synergy - stability - style. Не се безпокойте ако объркате, то винаги може да се смени по късно.<br /><br />Въведете главния email адрес на сайта ви. Този email адрес ще е основия за Администраторския акаунт. Не се безпокойте ако объркате, то винаги може да се смени по късно.<br /><br />Въвдете вашия no reply email адрес. Той ще бъде използван автоматично да изпраща на потребители, смяна на паролите, и други информителни съобщения. Не се безпокойте ако объркате, то винаги може да се смени по късно.<br /><br />Please confirm that this is the web address or URL used to access the homepage of your site.<br /><br />Моля потвърдете че това е интернет адреса  или URL използван за админ секцията на сайта.',
    'sitedata_missing' => 'Следните проблеми излезнаха при от ваведената от вас информация:',
    'system_path' => 'Настройки на Пътя',
    'unable_mkdir' => 'Не мога да създам дериктория',
    'unable_to_find_ver' => 'Не мога да определя версията на glFusion .',
    'upgrade_error' => 'Грешка при Обновяването',
    'upgrade_error_text' => 'Излезна грешка докато се системата се обновяваше.',
    'upgrade_steps' => 'СТЪПКИ ЗА ОБНОВЯВАНЕ',
    'upload_max_filesize' => 'glFusion ви позволява да качвате добавки,картини,и други файлове. Трябва да сте позволили поне 8MB като махимално място за качване.',
    'use_utf8' => 'Използвайте UTF-8',
    'welcome_help' => 'Добре дошли във Инсталациония Помощтник на glFusion CMS. Вие можете да инсталирате нов glFusion сайт,да обновите вече съществуващ сайт, Или да мигрирате от съществуващ Geekblog 1.4.1 сайт.<br /><br />Моля изберете език за инсталатора и натиснете <strong>Напред</strong>.',
    'wizard_version' => 'v1.1.3.svn Инсталационен Помощтник',
    'system_path_prompt' => 'Въведете пълния път към  glFusion\ <strong>private/</strong> directory.<br /><br />Тази директория съдържа <strong>db-config.php.dist</strong> или <strong>db-config.php</strong> файл.<br /><br />Примери: /home/www/glfuison/private или c:/www/glfusion/private.<br /><br /><strong>Забележка:</strong> Пълния път към  public_html/ дерикторията изглежда че е:<br />%s<br /><br /><strong>Още настройки</strong> ви позволява да принуди някой от пътищата. Не е нужно да се редактират тези пътища, системата ще ги определи автоматично.',
    'advanced_settings' => 'Още настройки',
    'log_path' => 'път към logs',
    'lang_path' => 'път към езиците',
    'backup_path' => 'път към бакъпите',
    'data_path' => 'път към дата',
    'language_support' => 'Помощ за езиците',
    'language_pack' => 'glFusion е на Английски, но след инсталацията можете да свалите и инсталирате <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=1" target="_blank">Езиков пакет</a> който съдържа всички езикови файлове подържани от нас.',
    'libcustom_not_found' => 'Unable to located lib-custom.php.dist.'
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Инсталацията Завършена',
    1 => 'Инсталация на glFusion ',
    2 => ' завършено!',
    3 => 'Поздравления,вие успешно ',
    4 => ' glFusion. Моля отделете минутка и прочетете информацията долу.',
    5 => 'За да влезете във вашия нов glFusion сайт,моля използвайте тези:',
    6 => 'Потребител:',
    7 => 'Админ',
    8 => 'Парола:',
    9 => 'парола',
    10 => 'Предопреждение във защитата',
    11 => 'Не забравайте да направите',
    12 => 'неща',
    13 => 'Изтрийте или преместете инсталационата дириктория,',
    14 => 'Смени',
    15 => 'потребителска парола.',
    16 => 'Задай права на',
    17 => 'и',
    18 => 'върни се към',
    19 => '<strong>Забележка:</strong> Поради пречината че безопасния модел е сменен, ние създадохме нов акаунт със права за да можете да админстрирате вашия сайт.  Новото име за този акаунт е <b>NewAdmin</b> а паролата е <b>password</b>',
    20 => 'инсталиран',
    21 => 'обновен'
);

?>