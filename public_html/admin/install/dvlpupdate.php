<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | dvlpupdate.php                                                           |
// |                                                                          |
// | glFusion Development SQL Updates                                         |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

require_once '../../lib-common.php';

// Only let admin users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the glFusion Development Code Upgrade Routine.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG27[12]);
    $display .= $LANG27[12];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

$retval = '';

function glfusion_110() {
    global $_TABLES, $_CONF;

    $_SQL = array();
    $_SQL[] = "
    CREATE TABLE {$_TABLES['commentedits']} (
      cid int(10) NOT NULL,
      uid mediumint(8) NOT NULL,
      time datetime NOT NULL,
      PRIMARY KEY (cid)
    ) TYPE=MYISAM
    ";
    $_SQL[] = "ALTER TABLE {$_TABLES['comments']} ADD name varchar(32) default NULL AFTER indent";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD comment_expire datetime NOT NULL default '0000-00-00 00:00:00' AFTER comments";
    $_SQL[] = "REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('database_version', '1')";
    $_SQL[] = "ALTER TABLE {$_TABLES['syndication']} CHANGE type type varchar(30) NOT NULL default 'article'";
    $_SQL[] = "UPDATE {$_TABLES['syndication']} SET type = 'article' WHERE type = 'geeklog'";
    $_SQL[] = "UPDATE {$_TABLES['syndication']} SET type = 'article' WHERE type = 'glfusion'";
    $_SQL[] = "UPDATE {$_TABLES['configuration']} SET type='select',default_value='s:10:\"US/Central\";' WHERE name='timezone'";
    $_SQL[] = "UPDATE {$_TABLES['configuration']} SET value='s:10:\"US/Central\";' WHERE name='timezone' AND value=''";
    $_SQL[] = "REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('glfusion', '1.1.0svn')";
    $_SQL[] = "ALTER TABLE {$_TABLES['staticpage']} ADD sp_search tinyint(4) NOT NULL default '1' AFTER postmode";

    $_SQL[] = "ALTER TABLE {$_TABLES['blocks']} DROP INDEX blocks_bid";
    $_SQL[] = "ALTER TABLE {$_TABLES['events']} DROP INDEX events_eid";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_forums']} DROP INDEX forum_id";
    $_SQL[] = "ALTER TABLE {$_TABLES['group_assignments']} DROP INDEX ug_main_grp_id";
    $_SQL[] = "ALTER TABLE {$_TABLES['polltopics']} DROP INDEX pollquestions_pid";
    $_SQL[] = "ALTER TABLE {$_TABLES['sessions']} DROP INDEX sess_id";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} DROP INDEX stories_sid";
    $_SQL[] = "ALTER TABLE {$_TABLES['userindex']} DROP INDEX userindex_uid";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("glFusion 1.1.0svn Development update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        next($_SQL);
    }

    $c = config::get_instance();

    $c->add('comment_code',0,'select',4,21,17,1670,TRUE);
    $c->add('comment_edit',0,'select',4,21,0,1680,TRUE);
    $c->add('comment_edittime',1800,'text',4,21,NULL,1690,TRUE);
    $c->add('article_comment_close_days',30,'text',4,21,NULL,1700,TRUE);
    $c->add('comment_close_rec_stories',0,'text',4,21,NULL,1710,TRUE);

    $c->add('jhead_enabled',0,'select',5,22,0,1480,TRUE);
    $c->add('path_to_jhead','','text',5,22,NULL,1490,TRUE);
    $c->add('jpegtrans_enabled',0,'select',5,22,0,1500,TRUE);
    $c->add('path_to_jpegtrans','','text',5,22,NULL,1510,TRUE);
    $c->add('jpg_orig_quality','85','text',5,23,NULL,1500,TRUE);

    // search stuff (temp for now)
    $c->add('fs_search', NULL, 'fieldset', 0, 6, NULL, 0, TRUE);
    $c->add('search_style','google','select',0,6,18,650,TRUE);
    $c->add('search_limits','10,15,25,30','text',0,6,NULL,660,TRUE);
    $c->add('num_search_results',25,'text',0,6,NULL,670,TRUE);
    $c->add('search_show_limit',TRUE,'select',0,6,1,680,TRUE);
    $c->add('search_show_sort',TRUE,'select',0,6,1,690,TRUE);
    $c->add('search_show_num',TRUE,'select',0,6,1,700,TRUE);
    $c->add('search_show_type',TRUE,'select',0,6,1,710,TRUE);
    $c->add('search_show_user',TRUE,'select',0,6,1,720,TRUE);
    $c->add('search_show_hits',TRUE,'select',0,6,1,730,TRUE);
    $c->add('search_no_data','<i>Not available...</i>','text',0,6,NULL,740,TRUE);
    $c->add('search_separator',' &gt; ','text',0,6,NULL,750,TRUE);
    $c->add('search_def_keytype','phrase','select',0,6,19,760,TRUE);


    //$c->restore_param('num_search_results', 'Core');

    // This option should only be set during the install/upgrade because of all
    // the setting up thats required. So hide it from the user.
    $c->add('search_use_fulltext',FALSE,'hidden',0,6);

    $c->add('hide_adminmenu',TRUE,'select',3,12,1,1170,TRUE);

    $c->del('use_glfilter', 'forum');

    $c->add('mail_backend','mail','select',0,1,20,60,TRUE);
    $c->add('mail_sendmail_path','','text',0,1,NULL,70,TRUE);
    $c->add('mail_sendmail_args','','text',0,1,NULL,80,TRUE);
    $c->add('mail_smtp_host','','text',0,1,NULL,90,TRUE);
    $c->add('mail_smtp_port','25','text',0,1,NULL,100,TRUE);
    $c->add('mail_smtp_auth',FALSE,'select',0,1,0,110,TRUE);
    $c->add('mail_smtp_username','','text',0,1,NULL,120,TRUE);
    $c->add('mail_smtp_password','','text',0,1,NULL,130,TRUE);
    $c->add('mail_smtp_secure','none','select',0,1,21,140,TRUE);
    $c->del('mail_settings','Core');

    // New 2008-Sept-25

    $c->add('default_search_order','date','select',0,6,22,770,TRUE);
    $c->add('compress_css',TRUE,'select',2,11,0,1370,TRUE);
    $c->add('allow_embed_object',TRUE,'select',7,34,1,1720,TRUE);
    $c->del('use_safe_html','Core');
    $c->del('user_html','Core');
    $c->del('admin_html','Core');
    $c->del('allowed_protocols','Core');

    $c->add('showtopic_review_order', 'DESC', 'select',0, 0, 5, 45, true, 'forum');

    // New 2008-10-10
    $c->add('digg_enabled',1,'select',7,31,0,2000,TRUE);

    $c->add('allow_memberlist',FALSE, 'select',0, 0, 0, 25, true, 'forum');
}

function glfusion_112()
{
    global $_TABLES, $_CONF;

    COM_errorLog("glFusion: Running code update for glFusion v1.1.2svn");

    $c = config::get_instance();

    $c->add('story_submit_by_perm_only',0,'select',4,20,0,780,TRUE);
    $c->add('use_from_site_mail',0,'select',0,1,0,150,TRUE);
    $c->del('pdf_enabled','Core');
    $c->del('show_popular_perpage','forum');
    $_FM_DEFAULT['FileStoreURL']     = $_CONF['site_url']  . '/filemgmt_data/files/';
    $c->add('FileStoreURL', $_FM_DEFAULT['FileStoreURL'], 'text',0, 2, 0, 70, true, 'filemgmt');
    $c->add('outside_webroot', 0, 'select', 0, 2, 0, 90, true, 'filemgmt');
}

function glfusion_113()
{
    global $_TABLES, $_CONF;

    $_SQL[] = "ALTER TABLE {$_TABLES['users']} ADD remote_ip varchar(15) default NULL AFTER num_reminders";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("glFusion 1.1.3 Development update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        next($_SQL);
    }

    $c = config::get_instance();
    $c->add('hidestorydate',0,'select',1,7,0,1205,TRUE);
    $c->add('fs_caching', NULL, 'fieldset', 2, 12, NULL, 0, TRUE);
    $c->add('cache_templates',TRUE,'select',2,12,0,1375,TRUE);
    $c->add('template_comments',FALSE,'select',2,11,0,1373,TRUE);
    $c->del('instance_cache', 'Core');
}

function glfusion_114()
{
    global $_TABLES, $_CONF;

    $_SQL = array();

    $_SQL[] = "UPDATE {$_TABLES['conf_values']} SET type='passwd' WHERE name='mail_smtp_password' LIMIT 1";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("glFusion 1.1.4 Development update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        next($_SQL);
    }

    $c = config::get_instance();
}

function glfusion_115()
{
    global $_TABLES, $_CONF;

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['users']} CHANGE username username varchar (48) NOT NULL default ''";
    $_SQL[] = "ALTER TABLE {$_TABLES['topics']} CHANGE sortnum sortnum mediumint(8) default NULL";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("glFusion 1.1.5 Development update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        next($_SQL);
    }

    DB_query("ALTER TABLE {$_TABLES['stories']} DROP INDEX stories_in_transit",1);
    DB_query("ALTER TABLE {$_TABLES['stories']} DROP COLUMN in_transit",1);
    DB_query("ALTER TABLE {$_TABLES['userprefs']} ADD search_result_format VARCHAR( 48 ) NOT NULL DEFAULT 'google'",1);
    DB_query("UPDATE {$_TABLES['conf_values']} SET type='text' WHERE name='mail_smtp_host'",1);
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.5',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.5' WHERE name='glfusion'",1);
    DB_query("DELETE FROM {$_TABLES['vars']} WHERE name='database_version'",1);
    DB_query("UPDATE {$_TABLES['conf_values']} SET selectionArray='23' WHERE name='censormode'",1);
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();
    $c->add('hide_exclude_content',0,'select',4,16,0,295,TRUE);
    $c->add('maintenance_mode',0,'select',0,0,0,511,TRUE);
    $c->del('search_show_limit', 'Core');
    $c->del('search_show_sort', 'Core');

    // Forum plugin
    $c->add('enable_user_rating_system',FALSE, 'select', 0,0,0,22, TRUE, 'forum');
    $c->add('bbcode_signature', TRUE, 'select',0, 0, 0, 37, true, 'forum');
    $c->add('use_wysiwyg_editor', false, 'select', 0, 2, 0, 85, true, 'forum');
    DB_query("ALTER TABLE {$_TABLES['gf_forums']} ADD `rating_view` INT( 8 ) NOT NULL ,ADD `rating_post` INT( 8 ) NOT NULL",1);
    DB_query("ALTER TABLE {$_TABLES['gf_userinfo']} ADD `rating` INT( 8 ) NOT NULL ",1);
    DB_query("ALTER TABLE {$_TABLES['gf_userinfo']} ADD signature MEDIUMTEXT NOT NULL",1 );
    DB_query("ALTER TABLE {$_TABLES['gf_userprefs']} ADD notify_full tinyint(1) NOT NULL DEFAULT '0' AFTER alwaysnotify",1);
    $sql = "CREATE TABLE IF NOT EXISTS {$_TABLES['gf_rating_assoc']} ( "
            . "`user_id` mediumint( 9 ) NOT NULL , "
            . "`voter_id` mediumint( 9 ) NOT NULL , "
            . "`grade` smallint( 6 ) NOT NULL  , "
            . "`topic_id` int( 11 ) NOT NULL , "
            . " KEY `user_id` (`user_id`), "
            . " KEY `voter_id` (`voter_id`) );";
    DB_query($sql);
    DB_query("ALTER TABLE {$_TABLES['gf_rating_assoc']} ADD topic_id int(11) NOT NULL AFTER grade",1);
    // add forum.html feature
    DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('forum.html','Can post using HTML',0)",1);
    $ft_id = DB_insertId();
    $grp_id = intval(DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'forum Admin'"));
    DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $grp_id)", 1);
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '3.1.4',pi_gl_version='1.1.5' WHERE pi_name = 'forum'");
}

function glfusion_116()
{
    global $_TABLES, $_CONF;

    $_SQL = array();

    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.6',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.6' WHERE name='glfusion'",1);
}

function glfusion_117()
{
    global $_TABLES, $_FM_TABLES, $_CONF;

    $_SQL = array();

    // new tables for ratings

    $_SQL[] = "CREATE TABLE IF NOT EXISTS {$_TABLES['rating']} (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `type` varchar(254) NOT NULL DEFAULT '',
                `item_id` varchar(40) NOT NULL,
                `votes` int(11) NOT NULL,
                `rating` decimal(4,2) NOT NULL,
                KEY `id` (`id`)
              ) Type=MyISAM;";

    $_SQL[] = "CREATE TABLE IF NOT EXISTS {$_TABLES['rating_votes']} (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `type` varchar(254) NOT NULL DEFAULT '',
                 `item_id` varchar(40) NOT NULL,
                 `uid` mediumint(8) NOT NULL,
                 `ip_address` varchar(14) NOT NULL,
                 `ratingdate` int(11) NOT NULL,
                 PRIMARY KEY (`id`),
                 KEY `uid` (`uid`),
                 KEY `ip_address` (`ip_address`),
                 KEY `type` (`type`)
               ) TYPE=MyISAM;";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("glFusion 1.1.7 Development update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        next($_SQL);
    }

    DB_query("ALTER TABLE {$_TABLES['rating_votes']} ADD rating INT NOT NULL DEFAULT '0' AFTER item_id ",1);

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    $c->add('rating_enabled',1,'select',1,7,24,1237,TRUE);

    // - new CAPTCHA settings
    $c->add('publickey', '','text',0, 0, 0, 42, true, 'captcha');
    $c->add('privatekey', '','text',0, 0, 0, 44, true, 'captcha');
    $c->add('recaptcha_theme', 'white','select',0, 0, 6, 46, true, 'captcha');
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='3.2.4' WHERE pi_name='captcha'");
    // -- *** NEED TO ADD RATING SPEED LIMIT ***

    // - option to turn on rating in filemgmt

    // new fields in story table to hold rating / votes

    $_SQL = array();

    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `rating` float NOT NULL DEFAULT '0' AFTER hits";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `votes` int(11) NOT NULL DEFAULT '0' AFTER rating";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("glFusion 1.1.7 Development update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        next($_SQL);
    }

    // convert the existing filemgmt ratings to new rating system...

    $fm_version = DB_getItem($_TABLES['plugins'],'pi_version','pi_name="filemgmt"');

    if ( $fm_version != '1.7.5' ) {
        DB_query("UPDATE {$_FM_TABLES['filemgmt_filedetail']} set rating = rating / 2",1);
        $result = DB_query("SELECT * FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE votes > 0");
        while ( $F = DB_fetchArray($result) ) {
            $item_id = $F['lid'];
            $votes   = $F['votes'];
            $rating  = $F['rating'];
            DB_query("INSERT INTO {$_TABLES['rating']} (type,item_id,votes,rating) VALUES ('filemgmt','".$item_id."',$votes,$rating);",1);
        }

        $result = DB_query("SELECT * FROM {$_FM_TABLES['filemgmt_votedata']}");
        while ( $H = DB_fetchArray($result) ) {
            $item_id = $H['lid'];
            $user_id = $H['ratinguser'];
            $ip      = $H['ratinghostname'];
            $time    = $H['ratingtimestamp'];
            $rating  = $H['rating'] / 2;
            DB_query("INSERT INTO {$_TABLES['rating_votes']} (type,item_id,rating,uid,ip_address,ratingdate) VALUES ('filemgmt','".$item_id."',$rating,$user_id,'".$ip."',$time);");
        }
        DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.7.5' WHERE pi_name='filemgmt'");
    }

    // convert the existing Media Gallery ratings to new rating system...

    $mg_version = DB_getItem($_TABLES['plugins'],'pi_version','pi_name="mediagallery"');
    if ( $fm_version != '1.6.8' ) {

        DB_query("UPDATE {$_TABLES['mg_media']} set media_rating = media_rating / 2",1);
        $result = DB_query("SELECT * FROM {$_TABLES['mg_media']} WHERE media_votes > 0");
        while ( $F = DB_fetchArray($result) ) {
            $item_id = $F['media_id'];
            $votes   = $F['media_votes'];
            $rating  = $F['media_rating'];
            DB_query("INSERT INTO {$_TABLES['rating']} (type,item_id,votes,rating) VALUES ('mediagallery','".$item_id."',$votes,$rating);",1);
        }

        $result = DB_query("SELECT * FROM {$_TABLES['mg_rating']}");
        while ( $H = DB_fetchArray($result) ) {
            $item_id = $H['media_id'];
            $user_id = $H['uid'];
            $ip      = $H['ip_address'];
            $time    = $H['ratingdate'];
            DB_query("INSERT INTO {$_TABLES['rating_votes']} (type,item_id,uid,ip_address,ratingdate) VALUES ('mediagallery','".$item_id."',$user_id,'".$ip."',$time);");
        }
        DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.6.8' WHERE pi_name='mediagallery'");
    }

    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.7',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.7' WHERE name='glfusion'",1);
}

function glfusion_118()
{
    global $_TABLES, $_FM_TABLES, $_CONF;

    $_SQL = array();

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // new full name support at registration
    $c->add('user_reg_fullname',1,'select',4,19,25,980,TRUE);

    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.8',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.8' WHERE name='glfusion'",1);
}

function glfusion_119()
{
    global $_TABLES, $_FM_TABLES, $_CONF;

    $_SQL = array();

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // FileMgmt enable rating config setting
    $c->add('enable_rating', 1,'select',0, 2, 0, 35, true, 'filemgmt');

    // Forum user pref for topic order
    DB_query("ALTER TABLE {$_TABLES['gf_userprefs']} ADD topic_order varchar(10) NOT NULL DEFAULT 'ASC' AFTER notify_once",1);

    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.9',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.9' WHERE name='glfusion'",1);
}

$retval .= 'Performing database upgrades if necessary...<br />';

glfusion_119();

// probably need to clear the template cache so do it here
CTL_clearCache();

$retval .= 'Development Code upgrades complete - see error.log for details<br>';

$display = COM_siteHeader();
$display .= $retval;
$display .= COM_siteFooter();
echo $display;
exit;
?>