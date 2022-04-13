<?php
/**
* glFusion CMS
*
* glFusion Development Update
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../lib-common.php';

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\Admin\AdminActions;

// Only let admin users access this page

/* Removing for 2.1.0 as auth method changed
if (!SEC_inGroup('Root')) {
    // Someone is trying to the page without proper access
    log::logAccessViolation('DvlpUpdate');
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG27[12]);
    $display .= $LANG27[12];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}
*/

$retval = '';

function glfusion_150()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();
    $c->add('min_username_length','4','text',4,4,NULL,60,TRUE);

// BB2 changes
    $_SQL[] = "ALTER TABLE {$_TABLES['bad_behavior2_ban']} ADD `reason` VARCHAR(255) NULL DEFAULT NULL;";

// story table change....

    $_SQL[] = "ALTER TABLE  {$_TABLES['stories']} ADD `alternate_tid` VARCHAR(20) NULL DEFAULT NULL AFTER `tid`, ADD INDEX `alternate_topic` (`alternate_tid`) ;";
    $_SQL[] = "ALTER TABLE  {$_TABLES['tokens']} CHANGE `urlfor` `urlfor` VARCHAR( 1024 ) NOT NULL";
    $_SQL[] = "ALTER TABLE  {$_TABLES['comments']} CHANGE  `ipaddress`  `ipaddress` VARCHAR( 45 ) NOT NULL DEFAULT  ''";
    $_SQL[] = "ALTER TABLE  {$_TABLES['rating_votes']} CHANGE  `ip_address`  `ip_address` VARCHAR( 45 ) NOT NULL";
    $_SQL[] = "ALTER TABLE  {$_TABLES['sessions']} CHANGE  `remote_ip`  `remote_ip` VARCHAR( 45 ) NOT NULL DEFAULT  ''";
    $_SQL[] = "ALTER TABLE  {$_TABLES['trackback']}  `ipaddress`  `ipaddress` VARCHAR( 45 ) NOT NULL DEFAULT  ''";
    $_SQL[] = "ALTER TABLE  {$_TABLES['users']} CHANGE  `remote_ip`  `remote_ip` VARCHAR( 45 ) NOT NULL DEFAULT  ''";
    $_SQL[] = "ALTER TABLE  {$_TABLES['cp_sessions']} ADD `ip` VARCHAR(16) NOT NULL";
    $_SQL[] = "ALTER TABLE  {$_TABLES['cp_sessions']} CHANGE `counter` `counter` INT(11) NOT NULL DEFAULT '0';";

// update topic length
    $_SQL[] = "ALTER TABLE {$_TABLES['topics']} CHANGE `tid` `tid` VARCHAR(128) NOT NULL DEFAULT '';";
    $_SQL[] = "ALTER TABLE {$_TABLES['topics']} CHANGE `topic` `topic` VARCHAR(128) NULL DEFAULT NULL;";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} CHANGE `tid` `tid` VARCHAR(128) NOT NULL DEFAULT 'General';";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} CHANGE `alternate_tid` `alternate_tid` VARCHAR(128) NULL DEFAULT NULL;";
    $_SQL[] = "ALTER TABLE {$_TABLES['blocks']} CHANGE `tid` `tid` VARCHAR(128) NOT NULL DEFAULT 'All';";
    $_SQL[] = "ALTER TABLE {$_TABLES['storysubmission']} CHANGE `tid` `tid` VARCHAR(128) NOT NULL DEFAULT 'General';";

    $_SQL[] = "ALTER TABLE {$_TABLES['staticpage']} CHANGE `sp_tid` `sp_tid` VARCHAR(128) NOT NULL DEFAULT 'none';";

    if ($use_innodb) {
        $statements = count($_SQL);
        for ($i = 0; $i < $statements; $i++) {
            $_SQL[$i] = str_replace('MyISAM', 'InnoDB', $_SQL[$i]);
        }
    }

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

    $result = DB_query("SELECT * FROM {$_TABLES['autotags']} WHERE tag='uikitlogin'");
    if ( DB_numRows($result) < 1 ) {
        $sql = "INSERT INTO {$_TABLES['autotags']} (`tag`, `description`, `is_enabled`, `is_function`, `replacement`) VALUES ('uikitlogin', 'UIKit Login Widget', '1', '1', NULL);";
        DB_query($sql,1);
    }

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    $c->add('fs_sfs', NULL, 'fieldset', 0, 1, NULL, 0, true, 'spamx');
    $c->add('sfs_username_check', false, 'select',0, 1, 1, 10, true, 'spamx');
    $c->add('sfs_email_check', true, 'select',0, 1, 1, 20, true, 'spamx');
    $c->add('sfs_ip_check', true, 'select',0, 1, 1, 30, true, 'spamx');
    $c->add('sfs_username_confidence', '99.00', 'text',0, 1, 1, 40, true, 'spamx');
    $c->add('sfs_email_confidence', '50.00', 'text',0, 1, 1, 50, true, 'spamx');
    $c->add('sfs_ip_confidence', '25.00', 'text',0, 1, 1, 60, true, 'spamx');

    $c->add('sg_spam', NULL, 'subgroup', 8, 0, NULL, 0, TRUE);
    $c->add('fs_spam_config', NULL, 'fieldset', 8, 1, NULL, 0, TRUE);
    $c->add('bb2_enabled',1,'select',8,1,0,10,TRUE);
    $c->add('bb2_ban_enabled',0,'select',8,1,0,15,TRUE);
    $c->add('bb2_ban_timeout',24,'text',8,1,0,16,TRUE);
    $c->add('bb2_display_stats',1,'select',8,1,0,20,TRUE);
    $c->add('bb2_strict',0,'select',8,1,0,30,TRUE);
    $c->add('bb2_verbose',0,'select',8,1,0,40,TRUE);
    $c->add('bb2_logging',0,'select',8,1,0,50,TRUE);
    $c->add('bb2_httpbl_key','','text',8,1,NULL,60,TRUE);
    $c->add('bb2_httpbl_threat',25,'text',8,1,NULL,70,TRUE);
    $c->add('bb2_httpbl_maxage',30,'text',8,1,NULL,80,TRUE);
    $c->add('bb2_offsite_forms',0,'select',8,1,0,90,TRUE);
    $c->add('bb2_eu_cookie',0,'select',8,1,0,100,TRUE);

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.5.0',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.5.0' WHERE name='glfusion'",1);
}

function glfusion_151()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    $_SQL[] = "ALTER TABLE {$_TABLES['article_images']} CHANGE `ai_sid` `ai_sid` VARCHAR(128);";
    $_SQL[] = "ALTER TABLE {$_TABLES['comments']} CHANGE `sid` `sid` VARCHAR(128);";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} CHANGE `sid` `sid` VARCHAR(128);";
    $_SQL[] = "ALTER TABLE {$_TABLES['storysubmission']} CHANGE `sid` `sid` VARCHAR(128);";
    $_SQL[] = "ALTER TABLE {$_TABLES['syndication']} CHANGE `topic` `topic` VARCHAR(128);";
    $_SQL[] = "ALTER TABLE {$_TABLES['trackback']} CHANGE `sid` `sid` VARCHAR(128);";

    if ($use_innodb) {
        $statements = count($_SQL);
        for ($i = 0; $i < $statements; $i++) {
            $_SQL[$i] = str_replace('MyISAM', 'InnoDB', $_SQL[$i]);
        }
    }

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.5.1',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.5.1' WHERE name='glfusion'",1);
}

function glfusion_152()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    $_SQL[] = "REPLACE INTO {$_TABLES['autotags']} (tag, description, is_enabled, is_function, replacement) VALUES ('vimeo', 'Embed Vimeo videos into content. Usage:[vimeo:ID height:PX width:PX align:LEFT/RIGHT pad:PX responsive:0/1]', 1, 1, NULL)";

    if ($use_innodb) {
        $statements = count($_SQL);
        for ($i = 0; $i < $statements; $i++) {
            $_SQL[$i] = str_replace('MyISAM', 'InnoDB', $_SQL[$i]);
        }
    }

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }
    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.5.2',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.5.2' WHERE name='glfusion'",1);
}

function glfusion_160()
{
    global $_TABLES, $_CONF, $_CK_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();
    $c->add('infinite_scroll',1,'select',1,1,0,25,TRUE);

    if ( !isset($_CONF['comment_engine'])) {
        $c->add('comment_engine','internal','select',4,6,30,1,TRUE);
        $c->add('comment_disqus_shortname','not defined','text',4,6,NULL,2,TRUE);
        $c->add('comment_fb_appid','not defined','text',4,6,NULL,3,TRUE);
    }
    if ( !isset($_CONF['fb_appid'])) {
        $c->add('fb_appid','','text',0,0,NULL,90,TRUE);
    }

    if ( !isset($_CK_CONF['filemanager_fileperm'] ) ) {
        $c->add('filemanager_fileperm', '0664', 'text', 0, 2, NULL, 110, true, 'ckeditor');
        $c->add('filemanager_dirperm', '0775', 'text', 0, 2, NULL, 120, true, 'ckeditor');
    }

    $res = DB_query("SELECT * FROM {$_TABLES['conf_values']} WHERE name='social_site_extra' AND group_name='social_internal'");
    $num = DB_numRows($res);

    if ( $num == 0 ) {
        $c->add('social_site_extra','', 'text',0,0,NULL,1,TRUE,'social_internal');
    }

    $c->del('atom_max_items', 'staticpages');

    $_SQL[] = "UPDATE {$_TABLES['plugins']} SET pi_enabled='0' WHERE pi_name='ban'";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `subtitle` VARCHAR(128) DEFAULT NULL AFTER `title`;";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `story_image` VARCHAR(128) DEFAULT NULL AFTER `alternate_tid`;";
    $_SQL[] = "ALTER TABLE {$_TABLES['autotags']} CHANGE `description` `description` VARCHAR(250) NULL DEFAULT '';";
    $_SQL[] = "REPLACE INTO {$_TABLES['autotags']} (tag, description, is_enabled, is_function, replacement) VALUES ('vimeo', 'Embed Vimeo videos into content. Usage:[vimeo:ID height:PX width:PX align:LEFT/RIGHT pad:PX responsive:0/1]', 1, 1, NULL)";
    $_SQL[] = "REPLACE INTO {$_TABLES['autotags']} (tag, description, is_enabled, is_function, replacement) VALUES ('headlines', 'HTML: embeds article headslines. usage: [headlines:<i>topic_name or all</i> display:## meta:0/1 titlelink:0/1 featured:0/1 frontpage:0/1 cols:# template:template_name]', 1, 1, '');";
    $_SQL[] = "REPLACE INTO {$_TABLES['autotags']} (tag, description, is_enabled, is_function, replacement) VALUES ('newimage', 'HTML: embeds new images in flexible grid. usage: [newimage:<i>#</i> - How many images to display <i>truncate:0/1</i> - 1 = truncate number of images to keep square grid <i>caption:0/1</i> 1 = include title]', 1, 1, '');";
    $_SQL[] = "ALTER TABLE {$_TABLES['staticpage']} DROP `sp_catid`;";

    $_SQL[] = "ALTER TABLE {$_TABLES['rating']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';";
    $_SQL[] = "ALTER TABLE {$_TABLES['rating_votes']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';";
    $_SQL[] = "ALTER TABLE {$_TABLES['subscriptions']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';";
    $_SQL[] = "ALTER TABLE {$_TABLES['logo']} CHANGE `config_name` `config_name` varchar(128) DEFAULT NULL;";
    $_SQL[] = "DROP INDEX `trackback_url` ON {$_TABLES['trackback']};";
    $_SQL[] = "ALTER TABLE {$_TABLES['rating']} CHANGE `item_id` `item_id` VARCHAR(128) NOT NULL DEFAULT '';";
    $_SQL[] = "ALTER TABLE {$_TABLES['rating_votes']} CHANGE `item_id` `item_id` VARCHAR(128) NOT NULL DEFAULT '';";
    $_SQL[] = "ALTER TABLE {$_TABLES['subscriptions']} CHANGE `id` `id` VARCHAR(128) NOT NULL DEFAULT '';";

    // create social share table

    $_SQL[] = "CREATE TABLE `{$_TABLES['social_share']}` (
      `id` varchar(128) NOT NULL DEFAULT '',
      `name` varchar(128) NOT NULL DEFAULT '',
      `display_name` varchar(128) NOT NULL DEFAULT '',
      `icon` varchar(128) NOT NULL DEFAULT '',
      `url` varchar(128) NOT NULL DEFAULT '',
      `enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
      PRIMARY KEY (id)
    ) ENGINE=MyISAM;
    ";

    $_SQL[] = "CREATE TABLE {$_TABLES['social_follow_services']} (
      `ssid` int(10) UNSIGNED NOT NULL auto_increment,
      `url` varchar(128) NOT NULL DEFAULT '',
      `enabled` tinyint(1) NOT NULL DEFAULT '1',
      `icon` varchar(128) NOT NULL,
      `service_name` varchar(128) NOT NULL,
      `display_name` varchar(128) NOT NULL,
      UNIQUE KEY `ssid` (`ssid`),
      UNIQUE KEY `service_name` (`service_name`)
    ) ENGINE=MyISAM;";

    $_SQL[] = "CREATE TABLE {$_TABLES['social_follow_user']} (
      `suid` int(10) NOT NULL AUTO_INCREMENT,
      `ssid` int(11) NOT NULL DEFAULT '0',
      `uid` int(11) NOT NULL,
      `ss_username` varchar(128) NOT NULL DEFAULT '',
      UNIQUE KEY `suid` (`suid`),
      UNIQUE KEY `ssid` (`ssid`,`uid`)
    ) ENGINE=MyISAM;";

    $_SQL[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES
                ('fb', 'facebook', 'Facebook', 'facebook', 'http://www.facebook.com/sharer.php?s=100', 1),
                ('gg', 'google-plus', 'Google+', 'google-plus', 'https://plus.google.com/share?url', 1),
                ('li', 'linkedin', 'LinkedIn', 'linkedin', 'http://www.linkedin.com', 1),
                ('lj', 'livejournal', 'Live Journal', 'pencil', 'http://www.livejournal.com', 1),
                ('mr', 'mail-ru', 'Mail.ru', 'at', 'http://mail-ru.com', 1),
                ('ok', 'odnoklassniki', 'Odnoklassniki', 'odnoklassniki', 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1', 1),
                ('pt', 'pinterest', 'Pinterest', 'pinterest-p', 'http://www.pinterest.com', 1),
                ('rd', 'reddit', 'reddit', 'reddit-alien', 'http://reddit.com/submit?url=%%u&title=%%t', 1),
                ('tw', 'twitter', 'Twitter', 'twitter', 'http://www.twitter.com', 1),
                ('vk', 'vk', 'vk', 'vk', 'http://www.vk.org', 1);";

    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(1, 'https://twitter.com/%%u', 1, 'twitter', 'twitter', 'Twitter');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(2, 'http://facebook.com/%%u', 1, 'facebook', 'facebook', 'Facebook');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(3, 'http://pinterest.com/%%u', 1, 'pinterest-p', 'pinterest', 'Pinterest');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(4, 'http://youtube.com/%%u', 1, 'youtube', 'youtube', 'Youtube');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(5, 'http://plus.google.com/+%%u', 1, 'google-plus', 'google-plus', 'Google+');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(6, 'http://linkedin.com/in/%%u', 1, 'linkedin', 'linkedin', 'LinkedIn');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(14, 'http://linkedin.com/company/%%u', 1, 'linkedin-square', 'linkedin-co', 'LinkedIn (Company)');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(7, 'http://github.com/%%u', 1, 'github', 'github', 'GitHub');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(8, 'http://instagram.com/%%u', 1, 'instagram', 'instagram', 'Instagram');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(9, 'http://vimeo.com/%%u', 1, 'vimeo', 'vimeo', 'Vimeo');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(10, 'http://flickr.com/photos/%%u', 1, 'flickr', 'flickr', 'Flickr');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(11, 'http://foursquare.com/%%u', 1, 'foursquare', 'foursquare', 'Foursquare');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(12, 'http://yelp.com/biz/%%u', 1, 'yelp', 'yelp', 'Yelp');";
    $_SQL[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(13, 'http://dribbble.com/%%u', 1, 'dribbble', 'dribbble', 'Dribbble');";
    $_SQL[] = "REPLACE INTO {$_TABLES['blocks']} (`bid`, `is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES(56, 1, 'followusblock', 'phpblock', 'Follow Us', 'all', 0, '', 0, '', '1000-01-01 00:00:00.000000', NULL, NULL, 0, 0, 'phpblock_social', '', 4, 4, 3, 2, 2, 2);";

    if ($use_innodb) {
        $statements = count($_SQL);
        for ($i = 0; $i < $statements; $i++) {
            $_SQL[$i] = str_replace('MyISAM', 'InnoDB', $_SQL[$i]);
        }
    }

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

    $_DATA[] = "REPLACE INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('headlines', 'HTML: embeds article headslines. usage: [headlines:<i>topic_name or all</i> display:## meta:0/1 titlelink:0/1 featured:0/1 frontpage:0/1 cols:# template:template_name]', 1, 1, '');";
    $_DATA[] = "REPLACE INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('mgslider', 'HTML: displays Media Gallery album. usage: [mgslider:<i>#album_id#</i> - Album ID for images <i>kenburns:0/1</i> - 1 = Enable Ken Burns effect <i>autoplay:0/1</i> 1 = Autoplay the slides <i>template:_name_</i> - Custom template name if wanted]', 1, 1, '');";

    foreach ($_DATA as $sql) {
        DB_query($sql,1);
    }


    // add new social features
    $sis_admin_ft_id = 0;
    $sis_group_id    = 0;

    $tmp_admin_ft_id = DB_getItem ($_TABLES['features'], 'ft_id',"ft_name = 'social.admin'");
    if (empty ($tmp_admin_ft_id)) {
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('social.admin','Ability to manage social features.',1)",1);
        $sis_admin_ft_id  = DB_insertId();
    }
    // now check for the group
    $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='Social Admin'");
    if ( DB_numRows($result) == 0 ) {
        DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core, grp_default) VALUES ('Social Admin','Has full access to manage social integrations.',1,0)");
        $sis_group_id  = DB_insertId();
    }
    if ( $sis_admin_ft_id != 0 && $sis_group_id != 0 ) {
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (".$sis_admin_ft_id.",".$sis_group_id.")");
    }
    if ( $sis_group_id != 0 ) {
        DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id,ug_grp_id) VALUES (".$sis_group_id.",1)");
    }

    $standard = ($_CONF['user_login_method']['standard']) ? true : false;
    $thirdparty = ($_CONF['user_login_method']['3rdparty']) ? true: false;
    $oauth = ($_CONF['user_login_method']['oauth']) ? true: false;

    if ( $standard === false && $thirdparty === false && $oauth === false ) {
        $standard = true;
    }

    $c->del('user_login_method', 'Core');
    $c->add('user_login_method',array('standard' => $standard , '3rdparty' => $thirdparty , 'oauth' => $oauth),'@select',4,1,1,120,TRUE);

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.6.0',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.6.0' WHERE name='glfusion'",1);
}

function glfusion_161()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    $c->del('fs_mysql','Core');
    $c->del('allow_mysqldump','Core');
    $c->del('mysqldump_path','Core');
    $c->del('mysqldump_options','Core');

    $c->del('atom_max_stories','Core');
    $c->del('restrict_webservices','Core');
    $c->del('disable_webservices','Core');
    $c->del('fs_webservices','Core');

    $_SQL[] = "ALTER TABLE {$_TABLES['blocks']} CHANGE `title` `title` VARCHAR(255) NULL DEFAULT NULL;";

    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `attribution_url` VARCHAR(255) NOT NULL default '' AFTER `expire`;";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `attribution_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `attribution_url`;";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `attribution_author` VARCHAR(255) NOT NULL DEFAULT '' AFTER `attribution_name`;";

    if ($use_innodb) {
        $statements = count($_SQL);
        for ($i = 0; $i < $statements; $i++) {
            $_SQL[$i] = str_replace('MyISAM', 'InnoDB', $_SQL[$i]);
        }
    }

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.6.1',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.6.1' WHERE name='glfusion'",1);

}

function glfusion_162()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // put config updates here

    // check if Non-Logged-in Users exists
    $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='Non-Logged-in Users'");
    if ( DB_numRows($result) == 0 ) {
        DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core, grp_default) VALUES ('Non-Logged-in Users','Non Logged-in Users (anonymous users)',1,0)",1);
        $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='Non-Logged-in Users'");
        if ( $result !== false ) {
            $row = DB_fetchArray($result);
            $nonloggedin_group_id = $row['grp_id'];
            // assign all anonymous users to the group
            DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (".$nonloggedin_group_id.",1,NULL) ",1);
            // assign root group
            DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (".$nonloggedin_group_id.",NULL,1) ",1);
            $sql = "UPDATE {$_TABLES['menu']} SET group_id = " . $nonloggedin_group_id . " WHERE group_id = 998";
            DB_query($sql);
            $sql = "UPDATE {$_TABLES['menu_elements']} SET group_id = " . $nonloggedin_group_id . " WHERE group_id = 998";
            DB_query($sql);
        } else {
            Log::write('system',Log::ERROR,"dvlpupdate: Error retrieving non-loggedin-user group id");
        }
    }
    $_SQL = array();

    // put SQL updates here

    if ($use_innodb) {
        $statements = count($_SQL);
        for ($i = 0; $i < $statements; $i++) {
            $_SQL[$i] = str_replace('MyISAM', 'InnoDB', $_SQL[$i]);
        }
    }

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }
    // add the URL auto tag
    $atSQL = "REPLACE INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('url', 'HTML: Create a link with description. usage: [url:<i>http://link.com/here</i> - Full URL <i>text</i> - text to be used for the URL link]', 1, 1, '');";
    DB_query($atSQL,1);

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.6.2',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.6.2' WHERE name='glfusion'",1);

}

function glfusion_163()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // put config updates here

    // sql updates here
    DB_query("ALTER TABLE {$_TABLES['subscriptions']} DROP INDEX `type`",1);
    DB_query("ALTER TABLE {$_TABLES['subscriptions']} DROP INDEX `type`",1);
    DB_query("ALTER TABLE {$_TABLES['sessions']} CHANGE `md5_sess_id` `md5_sess_id` VARCHAR(128) NOT NULL DEFAULT '';",1);
    DB_query("ALTER TABLE {$_TABLES['stories']} ADD `subtitle` VARCHAR(128) DEFAULT NULL AFTER `title`;",1);
    DB_query("ALTER TABLE {$_TABLES['stories']} ADD `story_image` VARCHAR(128) DEFAULT NULL AFTER `alternate_tid`;",1);
    DB_query("ALTER TABLE {$_TABLES['autotags']} CHANGE `description` `description` VARCHAR(250) NULL DEFAULT '';",1);
    DB_query("ALTER TABLE {$_TABLES['rating']} CHANGE `item_id` `item_id` VARCHAR(128) NOT NULL DEFAULT '';",1);
    DB_query("ALTER TABLE {$_TABLES['rating_votes']} CHANGE `item_id` `item_id` VARCHAR(128) NOT NULL DEFAULT '';",1);
    DB_query("ALTER TABLE {$_TABLES['subscriptions']} CHANGE `id` `id` VARCHAR(128) NOT NULL DEFAULT '';",1);
    DB_query("ALTER TABLE {$_TABLES['rating']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';",1);
    DB_query("ALTER TABLE {$_TABLES['rating_votes']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';",1);
    DB_query("ALTER TABLE {$_TABLES['subscriptions']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';",1);
    DB_query("ALTER TABLE {$_TABLES['logo']} CHANGE `config_name` `config_name` varchar(128) DEFAULT NULL;",1);
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_enabled='0' WHERE pi_name='ban'",1);
    DB_query("REPLACE INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,1,NULL)",1);

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.6.3',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.6.3' WHERE name='glfusion'",1);

}

function glfusion_165()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // new configuration option
    $c->add('open_ext_url_new_window',0,'select',7,2,0,40,TRUE);
    $c->add('enable_404_logging',1,'select',7,3,0,20,TRUE);
    $c->add('debug_oauth',0,'select',7,3,0,30,TRUE);
    $c->add('debug_html_filter',0,'select',7,3,0,40,TRUE);

    $res = DB_query("SELECT * FROM {$_TABLES['conf_values']} WHERE name='dbback_exclude' AND group_name='dbadmin_internal'");
    $num = DB_numRows($res);
    if ( $num == 0 ) {
        $c->add('dbback_exclude','', 'text',0,0,NULL,1,TRUE,'dbadmin_internal');
    }

    $_SQL = array();
    // drop unused fields
    $_SQL[] = "ALTER TABLE {$_TABLES['comments']} DROP score;";
    $_SQL[] = "ALTER TABLE {$_TABLES['comments']} DROP reason;";
    // change IP address in speed limit
    $_SQL[] = "ALTER TABLE {$_TABLES['speedlimit']} CHANGE `ipaddress` `ipaddress` VARCHAR(39) NULL DEFAULT NULL;";
    // use appropriate topic id length in syndication
    $_SQL[] = "ALTER TABLE {$_TABLES['syndication']} CHANGE `header_tid` `header_tid` VARCHAR(128) NULL DEFAULT NULL;";

    // forum updates
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} ADD `lastedited` VARCHAR(12) NULL DEFAULT NULL AFTER `lastupdated`;";

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.6.5',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.6.5' WHERE name='glfusion'",1);

}

function glfusion_166()
{
    global $_TABLES, $_CONF, $_VARS, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // new configuration option
    $c->add('standard_auth_first',1,'select',4,1,1,125,TRUE);

    $_SQL = array();

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.6.6',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.6.6' WHERE name='glfusion'",1);

}

function glfusion_170()
{
    global $_TABLES, $_CONF,$_VARS, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();
    $c->del('digg_enabled','Core');

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `story_video` VARCHAR(255) NULL DEFAULT NULL AFTER `story_image`;";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `sv_autoplay` TINYINT(3) NOT NULL DEFAULT '0' AFTER `story_video`;";
    $_SQL[] = "ALTER TABLE {$_TABLES['topics']} ADD `description` TEXT AFTER `topic`;";

    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `frontpage_date` DATETIME NULL DEFAULT NULL AFTER `frontpage`;";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD INDEX `frontpage_date` (`frontpage_date`);";

// add comment queued field
    $_SQL[] = "ALTER TABLE {$_TABLES['comments']} ADD queued TINYINT(3) NOT NULL DEFAULT '0' AFTER pid;";
    $_SQL[] = "ALTER TABLE {$_TABLES['comments']} ADD COLUMN `postmode` VARCHAR(15) NULL DEFAULT NULL AFTER `queued`;";


    $cmt_updates = DB_getItem($_TABLES['features'],'ft_id', 'ft_name = "comment.moderate"');

    if ( $cmt_updates == '' || (int) $cmt_updates == 0 ) {
        $_SQL[] = "INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) VALUES ('Comment Admin', 'Can moderate comments', 1)";
        $_SQL[] = "INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('comment.moderate', 'Ability to moderate comments', 1)";
        $_SQL[] = "INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('comment.submit', 'Comments bypass submission queue', 1)";
    }
    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

    if ( $cmt_updates == '' || (int) $cmt_updates == 0 ) {
        // comment groups and permissions
        $cmt_mod_id     = DB_getItem($_TABLES['features'], 'ft_id',"ft_name = 'comment.moderate'");
        $cmt_sub_id     = DB_getItem($_TABLES['features'], 'ft_id',"ft_name = 'comment.submit'");
        $cmt_admin      = DB_getItem($_TABLES['groups'], 'grp_id',"grp_name = 'Comment Admin'");

        if ( DB_count($_TABLES['access'],array('acc_ft_id','acc_grp_id'),array($cmt_mod_id,$cmt_admin)) == 0 ) {
            // ties comment.moderate feature to Comment Admin group
            if (($cmt_mod_id > 0) && ($cmt_admin > 0)) {
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($cmt_mod_id, $cmt_admin)");
            }
            // adds comment.submit feature to comment admin group
            if (($cmt_sub_id > 0) && ($cmt_admin > 0)) {
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($cmt_sub_id, $cmt_admin)");
            }
            // adds comment admin group to Root group
            if ($cmt_admin > 0) {
                DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES ($cmt_admin,NULL,1)");
            }
        }
    }

    // add new configuration items
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // encrypt
    if ( !isset($_VARS['guid'])) {
        $rk = COM_randomKey(80);
        DB_query("INSERT INTO {$_TABLES['vars']} (name,value) VALUES ('guid','".$rk."')");
        $_VARS['guid'] = $rk;
        // encrypt mail_smtp_password
        $_coreCfg = $c->get_config('Core');
        $c->set('mail_smtp_password', $_coreCfg['mail_smtp_password'],'Core');
    }

    // comments submission queue feature
    $c->add('commentssubmission',0,'select',4,6,31,35,TRUE,'Core');

    $c->add('bb2_reverse_proxy',0,'select',8,1,0,120,TRUE);
    $c->add('bb2_reverse_proxy_header','X-Forwarded-For','text',8,1,0,130,TRUE);
    $c->add('bb2_reverse_proxy_addresses',array(),'*text',8,1,0,140,TRUE);

    $c->del('path_pear','Core');
    $c->del('have_pear','Core');
    $c->del('fs_pear','Core');

    DB_query("UPDATE {$_TABLES['syndication']} SET update_info = '0' WHERE type='commentfeeds'",1);

    DB_query("INSERT INTO {$_TABLES['autotags']} (tag, description, is_enabled, is_function, replacement) VALUES ('iteminfo', 'HTML: Returns an info from content. usage: [iteminfo:<i>content_type</i> - Content Type - i.e.; article, mediagallery <i>id:</i> - id of item to get info from <i>what:</i> - what to return, i.e.; url, description, excerpt, date, author, etc.]', 1, 1, '');",1);

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.7.0',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.7.0' WHERE name='glfusion'",1);

}

function glfusion_171()
{
    global $_TABLES, $_CONF,$_VARS, $_FF_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} CHANGE `introtext` `introtext` MEDIUMTEXT NULL DEFAULT NULL;";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} CHANGE `bodytext` `bodytext` MEDIUMTEXT NULL DEFAULT NULL;";
    $_SQL[] = "ALTER TABLE {$_TABLES['storysubmission']} CHANGE `introtext` `introtext` MEDIUMTEXT NULL DEFAULT NULL;";
    $_SQL[] = "ALTER TABLE {$_TABLES['storysubmission']} CHANGE `bodytext` `bodytext` MEDIUMTEXT NULL DEFAULT NULL;";

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.7.1',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.7.1' WHERE name='glfusion'",1);

}

function glfusion_172()
{
    global $_TABLES, $_CONF,$_VARS, $_FF_CONF, $_SPX_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    if (!isset($_CONF['comment_indent'])) {
        $c->add('comment_indent',15,'text',4,6,NULL,150,TRUE,'Core');
    }

    $_SQL = array();

    $_SQL[] = "CREATE TABLE {$_TABLES['tfa_backup_codes']} (
    	`uid` MEDIUMINT(8) NULL DEFAULT NULL,
    	`code` VARCHAR(128) NULL DEFAULT NULL,
    	`used` TINYINT(4) NULL DEFAULT '0',
    	INDEX `uid` (`uid`),
    	INDEX `code` (`code`)
    ) ENGINE=MyISAM
    ";

    $_SQL[] = "ALTER TABLE {$_TABLES['users']} ADD COLUMN `tfa_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `act_time`;";
    $_SQL[] = "ALTER TABLE {$_TABLES['users']} ADD COLUMN `tfa_secret` VARCHAR(128) NULL DEFAULT NULL AFTER `tfa_enabled`;";
    $_SQL[] = "ALTER TABLE {$_TABLES['sessions']} ADD INDEX `uid` (`uid`);";

    if ($use_innodb) {
        $statements = count($_SQL);
        for ($i = 0; $i < $statements; $i++) {
            $_SQL[$i] = str_replace('MyISAM', 'InnoDB', $_SQL[$i]);
        }
    }

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

// forum plugin

    if ( !DB_checkTableExists('ff_badges') ) {
        $_SQL = array();

        $_SQL['ff_badges'] = "CREATE TABLE {$_TABLES['ff_badges']} (
          `fb_id` int(11) NOT NULL AUTO_INCREMENT,
          `fb_grp` varchar(20) NOT NULL DEFAULT '',
          `fb_order` int(3) NOT NULL DEFAULT '99',
          `fb_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `fb_gl_grp` MEDIUMINT(8) NOT NULL,
          `fb_type` varchar(10) DEFAULT 'img',
          `fb_data` varchar(255) DEFAULT NULL,
          `fb_dscp` varchar(40) DEFAULT NULL,
          PRIMARY KEY (`fb_id`),
          KEY `grp` (`fb_grp`,`fb_order`)
        ) ENGINE=MyISAM;";

        // Copy existing badge images from the original directory to the
        // new location under public_html/images
        $dst = $_CONF['path_html'] . 'images/forum/badges';
        if (!is_dir($dst)) {
            $status = @mkdir($dst, 0755, true);
        }
        if (is_dir($dst) && is_writable($dst)) {
            $src = $_CONF['path_html'] . 'forum/images/badges';
            $dir = opendir($src);
            while(false !== ($file = readdir($dir))) {
                if ($file != '.' && $file != '..' ) {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
            closedir($dir);
        }

        foreach ($_SQL AS $sql) {
            if ($use_innodb) {
                $sql = str_replace('MyISAM', 'InnoDB', $sql);
            }
            DB_query($sql,1);
        }

        $counter = 10;
        $groupTags = $_FF_CONF['grouptags'];
        foreach ($groupTags AS $group => $badge ) {
            $groupID = DB_getItem($_TABLES['groups'],'grp_id','grp_name="'.DB_escapeString($group).'"');
            if ( $groupID != '' && $groupID != 0 ) {
                $sql = "INSERT INTO {$_TABLES['ff_badges']}
                    (fb_grp,fb_order,fb_enabled,fb_gl_grp,fb_type,fb_data)
                    VALUES ('site',{$counter},1,'{$groupID}','img','{$badge}' )";
                DB_query($sql);
            }
            $counter += 10;
        }
        $c->del('grouptags','forum');
    }

    $_SQL = array();

    $buildRanks = false;
    $buildLikes = false;

    if ( !DB_checkTableExists('ff_ranks') ) {
        $_SQL['ff_ranks'] = "CREATE TABLE {$_TABLES['ff_ranks']} (
          `posts` int(11) unsigned NOT NULL DEFAULT '0',
          `dscp` varchar(40) NOT NULL DEFAULT '',
          PRIMARY KEY (`posts`)
        ) ENGINE=MyISAM;";
        $buildRanks = true;
    }

    if ( !DB_checkTableExists('ff_likes_assoc') ) {
        $_SQL['ff_likes_assoc'] = "CREATE TABLE `{$_TABLES['ff_likes_assoc']}` (
          `poster_id` mediumint(9) NOT NULL,
          `voter_id` mediumint(9) NOT NULL,
          `topic_id` int(11) NOT NULL,
          PRIMARY KEY (`poster_id`,`voter_id`,`topic_id`)
        ) ENGINE=MyISAM;";
        $buildLikes = true;
    }
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_likes_assoc']}
        ADD `like_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ADD KEY `voter_id` (`voter_id`),
        ADD KEY `poster_id` (`poster_id`)";

    $_SQL[] = "ALTER TABLE {$_TABLES['ff_likes_assoc']}
        ADD `username` varchar(40) AFTER topic_id";

    foreach ($_SQL AS $sql) {
        if ($use_innodb) {
            $sql = str_replace('MyISAM', 'InnoDB', $sql);
        }
        DB_query($sql,1);
    }

    if ( $buildRanks == true ) {
        for ($i = 1; $i < 6; $i++) {
            $lvl = 'level' . $i;
            if (!isset($_FF_CONF[$lvl]) || !isset($_FF_CONF[$lvl . 'name'])) continue;
            $posts = (int)$_FF_CONF[$lvl];
            $dscp = DB_escapeString($_FF_CONF[$lvl . 'name']);
            $sql = "INSERT INTO {$_TABLES['ff_ranks']}
                    (posts, dscp) VALUES ($posts, '$dscp')";
            DB_query($sql);
            $c->del($lvl, 'forum');
            $c->del($lvl . 'name', 'forum');
        }
        $c->del('ff_rank_settings', 'forum');
    }
    _forum_update_config();
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '".$_FF_CONF['pi_version']."',pi_gl_version='".$_FF_CONF['gl_version']."' WHERE pi_name = 'forum'");
    // end of forum plugin updates

    // spam-x updates
    $_SQL = array();
    $_SQL[] = "ALTER TABLE {$_TABLES['spamx']} ADD COLUMN id INT(10) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id)";
    $_SQL[] = "
    CREATE TABLE {$_TABLES['spamx_stats']} (
      `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `module` VARCHAR(128) NOT NULL DEFAULT '',
      `type` VARCHAR(50) NOT NULL DEFAULT '',
      `blockdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `ip` VARCHAR(50) NOT NULL DEFAULT '',
      `email` VARCHAR(50) NOT NULL DEFAULT '',
      `username` VARCHAR(50) NOT NULL DEFAULT '',
      PRIMARY KEY (`id`),
      INDEX `type` (`type`),
      INDEX `blockdate` (`blockdate`)
    ) ENGINE=MyISAM
    ";
    foreach ($_SQL AS $sql) {
        if ($use_innodb) {
            $sql = str_replace('MyISAM', 'InnoDB', $sql);
        }
        DB_query($sql,1);
    }
    _spamx_update_config();
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='".$_SPX_CONF['pi_version']."',pi_gl_version='".$_SPX_CONF['gl_version']."' WHERE pi_name='spamx' LIMIT 1");
    // end of spam-x

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.7.2',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.7.2' WHERE name='glfusion'",1);

}

function glfusion_173()
{
    global $_TABLES, $_CONF,$_VARS, $_FF_CONF, $_SPX_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // forum badge update
    DB_query("ALTER TABLE {$_TABLES['ff_badges']} ADD `fb_inherited` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `fb_enabled`;",1);
    // Change badge css designators to actual color strings
    $b_groups =\Forum\Badge::getAll();
    foreach ($b_groups as $grp) {
        foreach ($grp as $badge) {
            if ($badge->fb_type == 'css') {
                switch ($badge->fb_data) {
                case 'uk-badge-success':
                    $badge->fb_bgcolor = '#82bb42';
                    break;
                case 'uk-badge-danger':
                    $badge->fb_bgcolor = '#d32c46;';
                    break;
                case 'uk-badge-warning':
                    $badge->fb_bgcolor = '#d32c46;';
                    break;
                default:
                    $fc = substr($badge->fb_data,0,1);
                    if ( $fc != 'a' ) {
                        $badge->fb_bgcolor = '#009dd8';
                    }
                    break;
                }
                $badge->Save();
            }
        }
    }

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.7.3',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.7.3' WHERE name='glfusion'",1);

}

function glfusion_174()
{
    global $_TABLES, $_CONF,$_VARS, $_FF_CONF, $_SPX_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    $_SQL = array();

    // increase homepage field to 255 bytes
    $_SQL[] = "ALTER TABLE {$_TABLES['users']} CHANGE `homepage` `homepage` VARCHAR(255) NULL DEFAULT NULL;";

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.7.4',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.7.4' WHERE name='glfusion'",1);

}

function glfusion_200()
{
    global $_TABLES, $_CONF,$_VARS, $_FF_CONF, $_SPX_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';

    static $alreadyRun = 0;

    if ($alreadyRun > 0) {
        print "already been run - why is it calling twice???";
        exit;
    }
    $alreadyRun++;

    $c = config::get_instance();
    $db = Database::getInstance();

    $_SQL = array();
    $_SQL = array(
        0  => "ALTER TABLE `{$_TABLES['stories']}`
               CHANGE COLUMN `comment_expire` `comment_expire` DATETIME NULL DEFAULT NULL,
               CHANGE COLUMN `expire` `expire` DATETIME NULL DEFAULT NULL;",
        1  => "UPDATE `{$_TABLES['stories']}`
                SET `comment_expire` = NULL
                WHERE CAST(`comment_expire` AS CHAR(20)) = '0000-00-00 00:00:00'
                  OR CAST(`comment_expire` AS CHAR(20)) = '1000-01-01 00:00:00'
                  OR comment_expire = '1970-01-01 00:00:00'
                  OR comment_expire = '1999-01-01 00:00:00';",
        2  => "UPDATE `{$_TABLES['stories']}`
                SET `expire` = NULL
                WHERE CAST(`expire` AS CHAR(20)) = '0000-00-00 00:00:00'
                OR CAST(`expire` AS CHAR(20)) = '1000-01-01 00:00:00'
                OR `expire`= '1970-01-01 00:00:00'
                OR `expire`= '1999-01-01 00:00:00';",
        3  => "UPDATE `{$_TABLES['stories']}` SET `frontpage_date` = NULL WHERE `frontpage_date` = '1000-01-01 00:00:00';",
        4  => "ALTER TABLE `{$_TABLES['stories']}`
                CHANGE COLUMN `comment_expire` `comment_expire` DATETIME NULL DEFAULT NULL,
                CHANGE COLUMN `expire` `expire` DATETIME NULL DEFAULT NULL;",
        5  => "UPDATE `{$_TABLES['syndication']}`
                SET `updated` = '1970-01-01 00:00:00'
                WHERE CAST(`updated` AS CHAR(20)) = '0000-00-00 00:00:00'
                OR CAST(`updated` AS CHAR(20)) = '1000-01-01 00:00:00';",
        6  => "ALTER TABLE `{$_TABLES['syndication']}` CHANGE COLUMN `updated` `updated` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00';",
        7  => "ALTER TABLE `{$_TABLES['users']}`
    	        CHANGE COLUMN `regdate` `regdate` DATETIME NULL DEFAULT NULL AFTER `sig`,
    	        CHANGE COLUMN `act_time` `act_time` DATETIME NULL DEFAULT NULL AFTER `act_token`;",
        8  => "UPDATE `{$_TABLES['users']}`
                SET `act_time` = '1970-01-01 00:00:00'
                WHERE CAST(`act_time` AS CHAR(20)) = '0000-00-00 00:00:00'
                OR CAST(`act_time` AS CHAR(20)) = '1000-01-01 00:00:00';",
        9  => "UPDATE `{$_TABLES['users']}`
                SET `regdate` = '1970-01-01 00:00:00'
                WHERE CAST(`regdate` AS CHAR(20)) = '0000-00-00 00:00:00'
                OR CAST(`regdate` AS CHAR(20)) = '1000-01-01 00:00:00';",
        10 => "UPDATE `{$_TABLES['blocks']}`
                SET `rdfupdated` = '1970-01-01 00:00:00'
                WHERE CAST(`rdfupdated` AS CHAR(20)) = '0000-00-00 00:00:00'
                OR CAST(`rdfupdated` AS CHAR(20)) = '1000-01-01 00:00:00';",
        11 => "ALTER TABLE `{$_TABLES['blocks']}` CHANGE COLUMN `rdfupdated` `rdfupdated` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00';",
    );

    Log::write('system',Log::DEBUG,"Executing date fix SQL");

    foreach ($_SQL AS $sql) {
        try {
            $db->conn->query($sql);
        } catch(Throwable $e) {
            $err = $db->conn->errorInfo();
            if (isset($err[2])) {
                $output = preg_replace('!\s+!', ' ', $err[2]);
                Log::write('system',Log::DEBUG,"SQL failed in dvlpupdate: " . $sql);
                Log::write('system',Log::DEBUG,"SQL Error: " . $output);
            }
        }
    }

    $_SQL = array();
    $_SQL[] = "
        CREATE TABLE `{$_TABLES['admin_action']}` (
          `id`          mediumint(8) auto_increment,
          `datetime`    datetime  default NULL,
          `module`      varchar(100) NOT NULL DEFAULT 'system',
          `action`      varchar(100) NULL DEFAULT NULL,
          `description` text,
          `user`        varchar(48) default NULL,
          `ip`          varchar(48) default NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM
        ";
    $_SQL[] = "
        CREATE TABLE `{$_TABLES['themes']}` (
          `theme` varchar(40) NOT NULL DEFAULT '',
          `logo_type` tinyint(1) NOT NULL DEFAULT -1,
          `display_site_slogan` tinyint(1) NOT NULL DEFAULT -1,
          `logo_file` varchar(40) NOT NULL DEFAULT '',
          `grp_access` int(11) unsigned NOT NULL DEFAULT 2,
          PRIMARY KEY (`theme`)
        ) ENGINE=MyISAM;
        ";
    $_SQL[] = "INSERT INTO {$_TABLES['themes']} (theme, logo_type, display_site_slogan)
        VALUES ('_default', 99, 1), ('cms', -1, -1)";


    $_SQL[] = "CREATE TABLE `{$_TABLES['search_index']}` (
      `item_id` varchar(128) NOT NULL DEFAULT '',
      `type` varchar(20) NOT NULL DEFAULT '',
      `content` MEDIUMTEXT,
      `parent_id` varchar(128) NOT NULL DEFAULT '',
      `parent_type` varchar(50) NOT NULL DEFAULT '',
      `ts` int(11) unsigned NOT NULL DEFAULT '0',
      `grp_access` mediumint(8) NOT NULL DEFAULT '2',
      `title` varchar(200) NOT NULL DEFAULT '',
      `owner_id` mediumint(9) NOT NULL DEFAULT '0',
      `author` varchar(40) NOT NULL DEFAULT '',
      PRIMARY KEY (`type`, `item_id`),
      INDEX `type` (`type`),
      INDEX `item_date` (`ts`),
      INDEX `author` (`author`)
    ) ENGINE=MyISAM";

    $_SQL[] = "CREATE TABLE `{$_TABLES['search_stats']}` (
      `term` varchar(200) NOT NULL,
      `hits` int(11) unsigned NOT NULL DEFAULT '1',
      `results` int(11) unsigned NOT NULL DEFAULT '1',
      PRIMARY KEY (`term`),
      KEY `hits` (`hits`)
    ) ENGINE=MyISAM";


    if ($use_innodb) {
        $statements = count($_SQL);
        for ($i = 0; $i < $statements; $i++) {
            $_SQL[$i] = str_replace('MyISAM', 'InnoDB', $_SQL[$i]);
        }
    }

    Log::write('system',Log::DEBUG,"Creating new glFusion v2.0 tables");

    foreach ($_SQL AS $sql) {
        try {
            $db->conn->query($sql);
        } catch(Throwable $e) {
            $err = $db->conn->errorInfo();
            if (isset($err[2])) {
                $output = preg_replace('!\s+!', ' ', $err[2]);
                Log::write('system',Log::DEBUG,"SQL failed in dvlpupdate: " . $sql);
                Log::write('system',Log::DEBUG,"SQL Error: " . $output);
            }
        }
    }

    // Transfer settings from the logos table to themes
    glFusion\Theme\Theme::upgradeFromLogo();

    // only execute if Forum plugin is enabled
    if (in_array('forum',$_PLUGINS)) {
        // forum badge update
        DB_query("ALTER TABLE {$_TABLES['ff_badges']} ADD `fb_inherited` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `fb_enabled`;",1);
        // Change badge css designators to actual color strings
        $b_groups =\Forum\Badge::getAll();
        foreach ($b_groups as $grp) {
            foreach ($grp as $badge) {
                if ($badge->fb_type == 'css') {
                    switch ($badge->fb_data) {

                    case 'uk-badge-success':
                        $badge->fb_bgcolor = '#82bb42';
                        break;
                    case 'uk-badge-danger':
                        $badge->fb_bgcolor = '#d32c46;';
                        break;
                    case 'uk-badge-warning':
                        $badge->fb_bgcolor = '#d32c46;';
                        break;
                    default:
                        $fc = substr($badge->fb_data,0,1);
                        if ( $fc != 'a' ) {
                            $badge->fb_bgcolor = '#009dd8';
                        }
                        break;
                    }
                    $badge->Save();
                }
            }
        }
        DB_query("ALTER TABLE {$_TABLES['ff_topic']} ADD `lastedited` VARCHAR(12) NULL DEFAULT NULL AFTER `lastupdated`;",1);
        // Add configurable limit for likes shown in profile
        $c->add('likes_prf_limit','20','text',0,0,NULL,75,TRUE, 'forum');
    }

    // modify story table

    $sql = "ALTER TABLE `{$_TABLES['stories']}`
                ADD COLUMN `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	            DROP PRIMARY KEY,
	            ADD UNIQUE INDEX `stories_sid` (`sid`),
	            ADD PRIMARY KEY (`id`);";

    DB_query($sql,1);


    // core items
    if (!isset($_VARS['last_maint_run'])) {
        DB_query("INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('last_maint_run','') ",1);
    }

    // change comment feeds
    DB_query("UPDATE {$_TABLES['syndication']} SET type='comment' WHERE type='commentfeeds'",1);
    DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name='commentfeeds'",1);

    // add the new system.root permission
    $newCapabilities = array(
        array('ft_name' => 'system.root',   'ft_desc' => 'Allows Root Access'),
        array('ft_name' => 'actions.admin', 'ft_desc' => 'Ability to view Admin Actions'),
        array('ft_name' => 'cache.admin',   'ft_desc' => 'Ability to clear caches'),
        array('ft_name' => 'config.admin',  'ft_desc' => 'Ability to configuration glFusion'),
        array('ft_name' => 'database.admin','ft_desc' => 'Ability to perform Database Administration'),
        array('ft_name' => 'env.admin',     'ft_desc' => 'Ability to view Environment Check'),
        array('ft_name' => 'logview.admin', 'ft_desc' => 'Ability to view / clear glFusion Logs'),
        array('ft_name' => 'upgrade.admin', 'ft_desc' => 'Ability to run Upgrade Check'),
        array('ft_name' => 'search.admin',  'ft_desc' => 'Ability to manage the Search Engine')
    );

    foreach($newCapabilities AS $feature) {
        $admin_ft_id = 0;
        $group_id = 0;
        $tmp_admin_ft_id = $db->conn->fetchOne("SELECT ft_id FROM {$_TABLES['features']} WHERE ft_name = ?", array($feature['ft_name']));
        if ($tmp_admin_ft_id === false) {
            $db->conn->insert(
                $_TABLES['features'],
                array('ft_name' => $feature['ft_name'], 'ft_descr' => $feature['ft_desc'], 'ft_gl_core' => 1)
            );
            $admin_ft_id = $db->conn->lastInsertId();
            // assign new feature to root
            $db->conn->insert($_TABLES['access'],
                array('acc_ft_id' => $admin_ft_id, 'acc_grp_id' => 1)
            );
        } else {
            Log::write('system',Log::DEBUG,"Feature Already Exists: " . $feature['ft_name']. " skiping...");
        }
    }

    // zero out paths

    $path_html = _getHtmlPath();
    $cfg_path_html = $c->get('path_html','Core');
    if (!empty($cfg_path_html) && $path_html == $cfg_path_html) {
        $c->set('path_html','','Core');
    }
    $path_images = $_CONF['path_html'] . 'data/images/';
    $cfg_path_images = $c->get('path_images','Core');
    if (!empty($cfg_path_images) && $path_images == $cfg_path_images) {
        $c->set('path_images','','Core');
    }
    $path_log = $_CONF['path']  . 'logs/';
    $cfg_path_log = $c->get('path_log','Core');
    if (!empty($path_log) && $path_log == $cfg_path_log) {
        $c->set('path_log','','Core');
    }
    $path_language = $_CONF['path']  . 'language/';
    $cfg_path_language = $c->get('path_language','Core');
    if (!empty($cfg_path_language) && $path_language == $cfg_path_language) {
        $c->set('path_language','','Core');
    }
    $backup_path = $_CONF['path'] . 'backups/';
    $cfg_backup_path = $c->get('backup_path','Core');
    if (!empty($cfg_backup_path) && $backup_path == $cfg_backup_path) {
        $c->set('backup_path','','Core');
    }
    $path_data = $_CONF['path']  . 'data/';
    $cfg_path_data = $c->get('path_data','Core');
    if (!empty($cfg_path) && $path_data == $cfg_path_data) {
        $c->set('path_data','','Core');
    }
    $path_themes = $_CONF['path_html'] . 'layout/';
    $cfg_path_themes = $c->get('path_themes','Core');
    if (!empty($cfg_path_themes) && $path_themes == $cfg_path_themes) {
        $c->set('path_themes','','Core');
    }
/* - removing - no longer using this
    // create new path_rss
    $pos = strrpos($_CONF['rdf_file'],'/');
    if ($pos !== false) {
        $rdf_file = substr($_CONF['rdf_file'], $pos +1);
        if (strpos($rdf_file,'.') !== false) {
            // we are pretty sure we have a filename
            $rdf_path = substr($_CONF['rdf_file'],0,$pos+1);
            $c->set('rdf_file',$rdf_file,'Core');
            $path_rss = $_CONF['path_html'] . 'backend/';
            if ($path_rss == $rdf_path) {
                $c->set('path_rss','','Core');
            } else {
                $c->set('path_rss', $rdf_path,'Core');
            }
        }
    }
-- */
    // clean up the group assignment table
    DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id='2'",1);
    DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id='13'",1);


    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='2.0.0',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='2.0.0' WHERE name='glfusion'",1);

    \glFusion\Admin\AdminAction::write('system','dvlpupdate','System has been updated to latest glFusion version using DvlpUpdate.');
}

function glfusion_201()
{
    global $_TABLES, $_CONF,$_VARS, $_FF_CONF, $_SPX_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';

    static $alreadyRun = 0;

    if ($alreadyRun > 0) {
        print "already been run - why is it calling twice???";
        exit;
    }
    $alreadyRun++;

    $c = config::get_instance();
    $db = Database::getInstance();

    $_SQL = array();

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='2.0.1',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='2.0.1' WHERE name='glfusion'",1);

    \glFusion\Admin\AdminAction::write('system','dvlpupdate','System has been updated to latest glFusion version using DvlpUpdate.');
}

function glfusion_210()
{
    global $_TABLES, $_CONF,$_VARS, $_FF_CONF, $_SPX_CONF, $_PLUGINS, $LANG_AM, $use_innodb, $_DB_table_prefix, $_CP_CONF;

    require_once $_CONF['path_system'].'classes/config.class.php';

    static $alreadyRun = 0;

    if ($alreadyRun > 0) {
        print "already been run - why is it calling twice???";
        exit;
    }
    $alreadyRun++;

    $c = config::get_instance();
    $db = Database::getInstance();

    $_SQL = array();

    $_SQL[] = "ALTER TABLE `{$_TABLES['users']}` ADD `verified` tinyint(1) unsigned NOT NULL DEFAULT '0';";
    $_SQL[] = "ALTER TABLE `{$_TABLES['users']}` ADD `resettable` tinyint(1) unsigned NOT NULL DEFAULT '1';";
    $_SQL[] = "ALTER TABLE `{$_TABLES['users']}` ADD `roles_mask` int(10) unsigned NOT NULL DEFAULT '0';";
    $_SQL[] = "ALTER TABLE `{$_TABLES['users']}` ADD `force_logout` mediumint(7) unsigned NOT NULL DEFAULT '0';";

    $_SQL[] = "
        CREATE TABLE IF NOT EXISTS `{$_TABLES['users_confirmations']}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(10) unsigned NOT NULL,
            `email` varchar(249) NOT NULL,
            `selector` varchar(16) NOT NULL,
            `token` varchar(255) NOT NULL,
            `expires` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `selector` (`selector`),
            KEY `email_expires` (`email`,`expires`),
            KEY `user_id` (`user_id`)
        ) ENGINE=MyISAM;
    ";

    $_SQL[] = "
        CREATE TABLE IF NOT EXISTS `{$_TABLES['users_remembered']}` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user` int(10) unsigned NOT NULL,
            `selector` varchar(24) NOT NULL,
            `token` varchar(255) NOT NULL,
            `expires` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `selector` (`selector`),
            KEY `user` (`user`)
        ) ENGINE=MyISAM;
    ";

    $_SQL[] = "
        CREATE TABLE IF NOT EXISTS `{$_TABLES['users_resets']}` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user` int(10) unsigned NOT NULL,
            `selector` varchar(20) NOT NULL,
            `token` varchar(255) NOT NULL,
            `expires` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `selector` (`selector`),
            KEY `user_expires` (`user`,`expires`)
        ) ENGINE=MyISAM;
    ";

    $_SQL[] = "
        CREATE TABLE IF NOT EXISTS `{$_TABLES['users_throttling']}` (
            `bucket` varchar(44) NOT NULL,
            `tokens` float unsigned NOT NULL,
            `replenished_at` int(10) unsigned NOT NULL,
            `expires_at` int(10) unsigned NOT NULL,
            PRIMARY KEY (`bucket`),
            KEY `expires_at` (`expires_at`)
        ) ENGINE=MyISAM;
    ";

    foreach ($_SQL AS $sql) {
        if ($use_innodb) {
            $sql = str_replace('MyISAM', 'InnoDB', $sql);
        }
        DB_query($sql,1);
    }

    DB_query("UPDATE {$_TABLES['users']} SET verified=1",1);
    DB_query("UPDATE {$_TABLES['users']} SET status=3 WHERE status=1",1);
    DB_query("UPDATE {$_TABLES['users']} SET status=3 WHERE status=4",1);

    $have_badges = DB_checkTableExists('badges');
    $_SQL = [];
    $_SQL[] = "CREATE TABLE `{$_TABLES['badges']}` (
      `b_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `b_bg_id` int(11) unsigned NOT NULL DEFAULT 1,
      `b_order` int(4) NOT NULL DEFAULT 9999,
      `b_enabled` tinyint(1) unsigned NOT NULL DEFAULT 1,
      `b_inherit` tinyint(1) unsigned NOT NULL DEFAULT 1,
      `b_gl_grp` mediumint(8) NOT NULL DEFAULT 0,
      `b_type` varchar(10) NOT NULL DEFAULT 'img',
      `b_data` text DEFAULT NULL,
      `b_dscp` varchar(40) NOT NULL DEFAULT '',
      PRIMARY KEY (`b_id`),
      KEY `grp` (`b_bg_id`,`b_order`)
    ) ENGINE=MyISAM";
    $_SQL[] = "CREATE TABLE `{$_TABLES['badge_groups']}` (
      `bg_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `bg_order` int(4) DEFAULT 9999,
      `bg_name` varchar(128) NOT NULL DEFAULT '',
      `bg_singular` tinyint(1) unsigned NOT NULL DEFAULT 1,
      `bg_enabled` tinyint(1) unsigned NOT NULL DEFAULT 1,
      PRIMARY KEY (`bg_id`),
      UNIQUE KEY `bg_name` (`bg_name`),
      KEY `orderby` (`bg_order`)
    ) ENGINE=MyISAM";
    foreach ($_SQL AS $sql) {
        if ($use_innodb) {
            $sql = str_replace('MyISAM', 'InnoDB', $sql);
        }
        DB_query($sql,1);
    }

    // Only add badge records if creating the table.
    if (!$have_badges) {
        $_SQL = array();
        // Add the default badge group
        $_SQL[] = "INSERT INTO {$_TABLES['badge_groups']}
            (bg_order, bg_name, bg_singular) VALUES (10, 'Miscellaneous', 0)";
        // Collect any user-defined badge groups from Forum.
        $_SQL[] = "INSERT INTO {$_TABLES['badge_groups']} (bg_name)
            (SELECT DISTINCT(fb_grp) FROM {$_TABLES['ff_badges']} WHERE fb_grp <> '')";

        // Move forum badges that have an empty group name into group #1
        $_SQL[] = "INSERT INTO {$_TABLES['badges']} (
            SELECT 0, 1, b.fb_order, b.fb_enabled, b.fb_inherited, b.fb_gl_grp,
            b.fb_type, b.fb_data, b.fb_dscp FROM {$_TABLES['ff_badges']} b
            WHERE b.fb_grp = '')";
       // Move forum badges that have a group name into the new badge groups
        $_SQL[] = "INSERT INTO {$_TABLES['badges']} (
            SELECT 0, g.bg_id, b.fb_order, b.fb_enabled, b.fb_inherited, b.fb_gl_grp,
            b.fb_type, b.fb_data, b.fb_dscp FROM {$_TABLES['ff_badges']} b
            LEFT JOIN {$_TABLES['badge_groups']} g ON g.bg_name = b.fb_grp
            WHERE b.fb_grp <> '')";
        foreach ($_SQL as $sql) {
            DB_query($sql, 1);
        }
    }


    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='2.1.0',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='2.1.0' WHERE name='glfusion'",1);

    \glFusion\Admin\AdminAction::write('system','dvlpupdate','System has been updated to latest glFusion version using DvlpUpdate.');
}

function _getHtmlPath()
{
    $path = str_replace('\\', '/', __FILE__);
    if ( $path[1] == '/' ) {
        $double = true;
    } else {
        $double = false;
    }
    $path = str_replace('//', '/', $path);
    $parts = explode('/', $path);
    $num_parts = count($parts);
    if (($num_parts < 3) || ($parts[$num_parts-1] != 'dvlpupdate.php')) {
        die('Fatal error - can not figure out my own path');
    }
    $returnPath = implode('/', array_slice($parts, 0, $num_parts - 3)) . '/';
    if ( $double ) {
        $returnPath = '/'.$returnPath;
    }
    return $returnPath;
}

function _spamx_update_config()
{
    global $_CONF, $_SPX_CONF, $_TABLES;

    $c = config::get_instance();

    require_once $_CONF['path'].'plugins/spamx/sql/spamx_config_data.php';

    // remove stray items
    $result = DB_query("SELECT * FROM {$_TABLES['conf_values']} WHERE group_name='spamx'");
    while ( $row = DB_fetchArray($result) ) {
        $item = $row['name'];
        if ( ($key = _searchForIdKey($item,$spamxConfigData)) === NULL ) {
            DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE name='".DB_escapeString($item)."' AND group_name='spamx'");
        } else {
            $spamxConfigData[$key]['indb'] = 1;
        }
    }
    // add any missing items
    foreach ($spamxConfigData AS $cfgItem ) {
        if (!isset($cfgItem['indb']) ) {
            _addConfigItem( $cfgItem );
        }
    }
    $c = config::get_instance();
    $c->initConfig();
    $tcnf = $c->get_config('spamx');
    // sync up sequence, etc.
    foreach ( $spamxConfigData AS $cfgItem ) {
        $c->sync(
            $cfgItem['name'],
            $cfgItem['default_value'],
            $cfgItem['type'],
            $cfgItem['subgroup'],
            $cfgItem['fieldset'],
            $cfgItem['selection_array'],
            $cfgItem['sort'],
            $cfgItem['set'],
            $cfgItem['group']
        );
    }
}


function _forum_update_config()
{
    global $_CONF, $_FF_CONF, $_TABLES;

    $c = config::get_instance();

    require_once $_CONF['path'].'plugins/forum/sql/forum_config_data.php';

    // remove stray items
    $result = DB_query("SELECT * FROM {$_TABLES['conf_values']} WHERE group_name='forum'");
    while ( $row = DB_fetchArray($result) ) {
        $item = $row['name'];
        if ( ($key = _searchForIdKey($item,$forumConfigData)) === NULL ) {
            DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE name='".DB_escapeString($item)."' AND group_name='forum'");
        } else {
            $forumConfigData[$key]['indb'] = 1;
        }
    }
    // add any missing items
    foreach ($forumConfigData AS $cfgItem ) {
        if (!isset($cfgItem['indb']) ) {
            _addConfigItem( $cfgItem );
        }
    }
    $c = config::get_instance();
    $c->initConfig();
    $tcnf = $c->get_config('forum');
    // sync up sequence, etc.
    foreach ( $forumConfigData AS $cfgItem ) {
        $c->sync(
            $cfgItem['name'],
            $cfgItem['default_value'],
            $cfgItem['type'],
            $cfgItem['subgroup'],
            $cfgItem['fieldset'],
            $cfgItem['selection_array'],
            $cfgItem['sort'],
            $cfgItem['set'],
            $cfgItem['group']
        );
    }
}


function _updateConfig() {
    global $_CONF, $_TABLES, $coreConfigData;

    $db = Database::getInstance();

    $site_url = $_CONF['site_url'];
    $cookiesecure = $_CONF['cookiesecure'];
    $c = config::get_instance();

    require_once $_CONF['path'].'sql/core_config_data.php';

    // remove stray items
    $stmt = $db->conn->query("SELECT * FROM `{$_TABLES['conf_values']}` WHERE group_name='Core'");
    while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
        $item = $row['name'];
        if ( ($key = _searchForIdKey($item,$coreConfigData)) === NULL ) {
            $db->conn->delete(
                $_TABLES['conf_values'],
                array('name' => $item, 'group_name' => 'Core'),
                array(Database::STRING, Database::STRING)
            );
        } else {
            $coreConfigData[$key]['indb'] = 1;
        }
    }
    foreach ($coreConfigData AS $cfgItem ) {
        if (!isset($cfgItem['indb']) ) {
            _addConfigItem( $cfgItem );
        }
    }
    $c = config::get_instance();
    $c->initConfig();
    $tcnf = $c->get_config('Core');

    $site_url = $tcnf['site_url'];
    $cookiesecure = $tcnf['cookiesecure'];
    $def_photo = urldecode($_CONF['site_url']) . '/images/userphotos/default.jpg';

    foreach ( $coreConfigData AS $cfgItem ) {
        if ( $cfgItem['name'] == 'default_photo' )
            $cfgItem['default_value'] = $def_photo;

        $c->sync(
            $cfgItem['name'],
            $cfgItem['default_value'],
            $cfgItem['type'],
            $cfgItem['subgroup'],
            $cfgItem['fieldset'],
            $cfgItem['selection_array'],
            $cfgItem['sort'],
            $cfgItem['set'],
            $cfgItem['group']
        );
    }
}


function _forum_cvt_watch() {
    global $_CONF, $_TABLES, $LANG_GF02;

    $converted = 0;

    $fName = array();
    $tName = array();


    $complete = DB_getItem($_TABLES['vars'],'value','name="watchcvt"');
    if ( $complete == 1 ) {
        DB_query("DELETE FROM {$_TABLES['vars']} WHERE name=\"watchcvt\"");
    }

    $dt = new Date('now',$_CONF['timezone']);

    $processed = array();

    $sql = "SELECT * FROM {$_TABLES['ff_topic']} WHERE pid=0";
    $result = DB_query($sql);
    while ( ( $T = DB_fetchArray($result) ) != FALSE ) {
        $pids[] = $T['id'];
    }
    // grab all the full forum subscriptions first...
    $sql = "SELECT * FROM {$_TABLES['ff_watch']} ORDER BY topic_id ASC";
    $result = DB_query($sql,1);
    if ( $result === FALSE ) {
        $sql = "INSERT INTO {$_TABLES['vars']} (name,value) VALUES ('watchcvt','1')";
        DB_query($sql);
        return 0;
    }

    while ( ( $W = DB_fetchArray($result) ) != FALSE ) {
        if ( !isset($fName[$W['forum_id']]) ) {
           $forum_name = DB_getItem($_TABLES['ff_forums'],'forum_name','forum_id='.(int)$W['forum_id']);
           $fName[$W['forum_id']] = $forum_name;
        } else {
            $forum_name = $fName[$W['forum_id']];
        }

        if ( $W['topic_id'] != 0 ) {
            if ( $W['topic_id'] < 0 ) {
                $searchID = abs($W['topic_id']);
            } else {
                $searchID = $W['topic_id'];
            }
            if ( !isset($tName[$searchID]) ) {
                $topic_name = DB_getItem($_TABLES['ff_topic'],'subject','id='.(int)$searchID);
                $tName[$searchID] = $topic_name;
            } else {
                $topic_name = $tName[$searchID];
            }
        } else {
            $topic_name = $LANG_GF02['msg138'];
        }

        if ( $W['topic_id'] == 0 || (in_array($searchID,$pids) && !isset($processed[$W['topic_id']]))) {
            $sql="INSERT INTO {$_TABLES['subscriptions']} ".
                 "(type,uid,category,id,date_added,category_desc,id_desc) VALUES " .
                 "('forum',".
                 (int)$W['uid'].",'".
                 DB_escapeString($W['forum_id'])."','".
                 DB_escapeString($W['topic_id'])."','".
                 $dt->toMySQL(true)."','".
                 DB_escapeString($forum_name)."','".
                 DB_escapeString($topic_name)."')";
            DB_query($sql,1);
            $processed[$W['topic_id']] = 1;
            $converted++;
        }
    }
    $sql = "INSERT INTO {$_TABLES['vars']} (name,value) VALUES ('watchcvt','1')";
    DB_query($sql);

    return $converted;
}

function _forum_fix_watch() {
    global $_CONF, $_TABLES, $LANG_GF02;

    $converted = 0;

    $fName = array();
    $tName = array();
    $catlist = '';

    $dt = new Date('now',$_CONF['timezone']);

    $processed = array();

    // process by user....


    $sql = "SELECT * FROM {$_TABLES['subscriptions']} where type='forum' AND id=0 ORDER BY uid ASC";
    $result = DB_query($sql);

    $prevuid = 0;

    while ( ( $W = DB_fetchArray($result) ) != FALSE ) {
        if ( $W['uid'] != $prevuid && $prevuid != 0 ) {
            // we have a uid change... do the delete now
            DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int) $prevuid." AND id <> 0 AND category in (".$catlist .")",1);
            $catlist = '';
        }
        if ( $catlist != '' ) {
            $catlist .= ',';
        }
        $catlist .= $W['category'];
        $prevuid = $W['uid'];
    }
    return;
}

function _searchForId($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['name'] === $id) {
           return $array[$key];
       }
   }
   return null;
}

function _searchForIdKey($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['name'] === $id) {
           return $key;
       }
   }
   return null;
}

function _addConfigItem($data = array() )
{
    global $_TABLES;

    $Qargs = array(
                   $data['name'],
                   $data['set'] ? serialize($data['default_value']) : 'unset',
                   $data['type'],
                   $data['subgroup'],
                   $data['group'],
                   $data['fieldset'],
                   ($data['selection_array'] === null) ?
                    -1 : $data['selection_array'],
                   $data['sort'],
                   $data['set'],
                   serialize($data['default_value']));
    $Qargs = array_map('DB_escapeString', $Qargs);

    $sql = "INSERT INTO {$_TABLES['conf_values']} (name, value, type, " .
        "subgroup, group_name, selectionArray, sort_order,".
        " fieldset, default_value) VALUES ("
        ."'{$Qargs[0]}',"   // name
        ."'{$Qargs[1]}',"   // value
        ."'{$Qargs[2]}',"   // type
        ."{$Qargs[3]},"     // subgroup
        ."'{$Qargs[4]}',"   // groupname
        ."{$Qargs[6]},"     // selection array
        ."{$Qargs[7]},"     // sort order
        ."{$Qargs[5]},"     // fieldset
        ."'{$Qargs[9]}')";  // default value

    DB_query($sql);
}

// main system

$use_innodb = false;
if (($_DB_dbms == 'mysql') && (DB_getItem($_TABLES['vars'], 'value', "name = 'database_engine'") == 'InnoDB')) {
    $use_innodb = true;
}

$retval .= 'Performing database upgrades if necessary...<br>';

glfusion_210();

$retval .= 'Performing Configuration upgrades if necessary...<br>';

_updateConfig();

$stdPlugins=array('staticpages','spamx','links','polls','calendar','sitetailor','captcha','bad_behavior2','forum','mediagallery','filemgmt','commentfeeds');
foreach ($stdPlugins AS $pi_name) {
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_gl_version='".GVERSION."', pi_homepage='https://www.glfusion.org' WHERE pi_name='".$pi_name."'",1);
}

// purge all caches...
CACHE_clear();

header('Location: '.$_CONF['site_admin_url'].'/plugins.php?msg=600');
exit;
?>
