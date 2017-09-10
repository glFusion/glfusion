<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | dvlpupdate.php                                                           |
// |                                                                          |
// | glFusion Development SQL Updates                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
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
    COM_errorLog("Someone has tried to access the glFusion Development Code Upgrade Routine without proper permissions.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
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
    ) ENGINE=MYISAM
    ";
    $_SQL[] = "ALTER TABLE {$_TABLES['comments']} ADD name varchar(32) default NULL AFTER indent";
    $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD comment_expire datetime NOT NULL default '1000-01-01 00:00:00.000000' AFTER comments";
    $_SQL[] = "REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('database_version', '1')";
    $_SQL[] = "ALTER TABLE {$_TABLES['syndication']} CHANGE type type varchar(30) NOT NULL default 'article'";
    $_SQL[] = "UPDATE {$_TABLES['syndication']} SET type = 'article' WHERE type = 'glfusion'";
    $_SQL[] = "UPDATE {$_TABLES['configuration']} SET type='select',default_value='s:10:\"US/Central\";' WHERE name='timezone'";
    $_SQL[] = "UPDATE {$_TABLES['configuration']} SET value='s:10:\"US/Central\";' WHERE name='timezone' AND value=''";
    $_SQL[] = "REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('glfusion', '1.1.0svn')";
    $_SQL[] = "ALTER TABLE {$_TABLES['staticpage']} ADD sp_search tinyint(4) NOT NULL default '1' AFTER postmode";

    $_SQL[] = "ALTER TABLE {$_TABLES['blocks']} DROP INDEX blocks_bid";
    $_SQL[] = "ALTER TABLE {$_TABLES['events']} DROP INDEX events_eid";
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} DROP INDEX forum_id";
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
    DB_query("ALTER TABLE {$_TABLES['ff_forums']} ADD `rating_view` INT( 8 ) NOT NULL ,ADD `rating_post` INT( 8 ) NOT NULL",1);
    DB_query("ALTER TABLE {$_TABLES['ff_userinfo']} ADD `rating` INT( 8 ) NOT NULL ",1);
    DB_query("ALTER TABLE {$_TABLES['ff_userinfo']} ADD signature MEDIUMTEXT NOT NULL",1 );
    DB_query("ALTER TABLE {$_TABLES['ff_userprefs']} ADD notify_full tinyint(1) NOT NULL DEFAULT '0' AFTER alwaysnotify",1);
    $sql = "CREATE TABLE IF NOT EXISTS {$_TABLES['ff_rating_assoc']} ( "
            . "`user_id` mediumint( 9 ) NOT NULL , "
            . "`voter_id` mediumint( 9 ) NOT NULL , "
            . "`grade` smallint( 6 ) NOT NULL  , "
            . "`topic_id` int( 11 ) NOT NULL , "
            . " KEY `user_id` (`user_id`), "
            . " KEY `voter_id` (`voter_id`) );";
    DB_query($sql);
    DB_query("ALTER TABLE {$_TABLES['ff_rating_assoc']} ADD topic_id int(11) NOT NULL AFTER grade",1);
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
              ) ENGINE=MyISAM;";

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
               ) ENGINE=MyISAM;";

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

function glfusion_120()
{
    global $_TABLES, $_FM_TABLES, $_CONF;

    $_SQL = array();

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // FileMgmt enable rating config setting
    $c->add('enable_rating', 1,'select',0, 2, 0, 35, true, 'filemgmt');
    $c->add('silent_edit_default', 1,'select',0, 2, 0, 37, true, 'filemgmt');
    $c->add('displayblocks', 0,'select',0, 0, 3, 115, true, 'filemgmt');
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '1.7.7',pi_gl_version='1.2.0' WHERE pi_name = 'filemgmt'");

    // Forum configuration options (for new features option)
    $c->add('bbcode_disabled', 0, 'select', 0, 2, 6, 165, true, 'forum');
    $c->add('smilies_disabled', 0, 'select', 0, 2, 6, 170, true, 'forum');
    $c->add('urlparse_disabled', 0, 'select', 0, 2, 6, 175, true, 'forum');
    $c->add('displayblocks', 0,'select', 0, 0, 13, 115, true, 'calendar');

    // links plugin configuration optoins
    $c->add('displayblocks',0,'select',0, 0, 13, 60, true, 'links');

    // polls plugin configuration options
    $c->add('displayblocks',0, 'select', 0, 0, 13, 85, true, 'polls');

    // Forum user pref for topic order
    DB_query("ALTER TABLE {$_TABLES['ff_userprefs']} ADD topic_order varchar(10) NOT NULL DEFAULT 'ASC' AFTER notify_once",1);
    DB_query("ALTER TABLE {$_TABLES['ff_userprefs']} ADD use_wysiwyg_editor tinyint(3) NOT NULL DEFAULT '1' AFTER topic_order",1);
    DB_query("ALTER TABLE {$_TABLES['ff_topic']} ADD `status` int(10) unsigned NOT NULL DEFAULT '0' AFTER locked",1);

    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '3.2.0',pi_gl_version='1.2.0' WHERE pi_name = 'forum'");

    DB_query("ALTER TABLE {$_TABLES['groups']} ADD grp_default tinyint(1) unsigned NOT NULL default '0' AFTER grp_gl_core",1);
    DB_query("ALTER TABLE {$_TABLES['users']} CHANGE `passwd` `passwd` VARCHAR( 40 ) NOT NULL default ''");

    $c->add('article_comment_close_enabled',0,'select',4,21,0,1695,TRUE);
    $c->add('session_ip_check',1,'select',7,30,26,545,TRUE);
    $c->del('default_search_order','Core');

    DB_query("ALTER TABLE {$_TABLES['staticpage']} ADD sp_status tinyint(3) NOT NULL DEFAULT '1' AFTER sp_id",1);
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '1.5.5',pi_gl_version='1.2.0' WHERE pi_name = 'staticpages'");

    DB_query("ALTER TABLE {$_TABLES['events']} ADD status tinyint(3) NOT NULL DEFAULT '1' AFTER eid",1);
    DB_query("ALTER TABLE {$_TABLES['eventsubmission']} ADD status tinyint(3) NOT NULL DEFAULT '1' AFTER eid",1);
    DB_query("ALTER TABLE {$_TABLES['personal_events']} ADD status tinyint(3) NOT NULL DEFAULT '1' AFTER eid",1);
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '1.0.7',pi_gl_version='1.2.0' WHERE pi_name = 'calendar'");

    DB_query("UPDATE {$_TABLES['conf_values']} SET selectionArray = '0' WHERE name='searchloginrequired' AND group_name='Core'",1);

    // fixup the group names and admin switch
    DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='Bad Behavior2 Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_name='calendar Admin' WHERE grp_name='Calendar Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='calendar Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='filemgmt Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='forum Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_name='links Admin' WHERE grp_name='Links Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='links Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='mediagallery Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_name='polls Admin' WHERE grp_name='Polls Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='polls Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='sitetailor Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_name='staticpages Admin' WHERE grp_name='Static Page Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='staticpages Admin'",1);
    DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='spamx Admin'",1);

    // move multi-language support to its own area
    DB_query("INSERT INTO {$_TABLES['conf_values']} (name,value,type,group_name,default_value,subgroup,selectionArray,sort_order,fieldset) VALUES ('fs_mulitlanguage','N;','fieldset','Core','N;',6,-1,0,41)",1);
    DB_query("UPDATE {$_TABLES['conf_values']} SET fieldset='41' WHERE name='language_files' AND group_name='Core'",1);
    DB_query("UPDATE {$_TABLES['conf_values']} SET fieldset='41' WHERE name='languages' AND group_name='Core'",1);

    // topic sort order
    DB_query("ALTER TABLE {$_TABLES['topics']} ADD sort_by TINYINT(1) NOT NULL DEFAULT '0' AFTER archive_flag",1);
    DB_query("ALTER TABLE {$_TABLES['topics']} ADD sort_dir CHAR( 4 ) NOT NULL DEFAULT 'DESC' AFTER sort_by",1);

    // static pages configuration options
    $c->add('include_search', 1, 'select',0, 0, 0, 95, true, 'staticpages');
    $c->add('comment_code', -1, 'select',0, 0,17, 97, true, 'staticpages');
    $c->add('status_flag', 1, 'select',0, 0, 13, 99, true, 'staticpages');

    // new stats.view permission
    $result = DB_query("SELECT * FROM {$_TABLES['features']} WHERE ft_name='stats.view'");
    if ( DB_numRows($result) > 0 ) {
        COM_errorLog("glFusion 1.2.0 Development update: stats.view permission already exists");
    } else {
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('stats.view','Allows access to the Stats page.',0)",1);
        $ft_id = DB_insertId();
        $all_grp_id = intval(DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'All Users'"));
        $loggedin_grp_id = intval(DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'Logged-in Users'"));
        $root_grp_id = intval(DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'Root'"));
        if ( $_CONF['statsloginrequired'] || $_CONF['loginrequired'] ) {
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $loggedin_grp_id)", 1);
        } else {
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $all_grp_id)", 1);
        }
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $root_grp_id)", 1);
        $c->del('statsloginrequired','Core');
    }

    // registration
    $c->add('registration_type',0,'select',4,19,27,785,TRUE,'Core');
    DB_query("ALTER TABLE {$_TABLES['users']} ADD act_token VARCHAR(32) NOT NULL DEFAULT '' AFTER pwrequestid",1);
    DB_query("ALTER TABLE {$_TABLES['users']} ADD act_time DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00.000000' AFTER act_token",1);

    // session handling
    $c->del('cookie_ip','Core');
    DB_query("ALTER TABLE {$_TABLES['sessions']} DROP PRIMARY KEY",1);
    DB_query("ALTER TABLE {$_TABLES['sessions']} ADD PRIMARY KEY (md5_sess_id)",1);

    // comment editor
    $c->add('comment_postmode','plaintext','select',4,21,5,1693,TRUE);
    $c->add('comment_editor',0,'select',4,21,28,1694,TRUE);

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.2.0',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.2.0' WHERE name='glfusion'",1);
}

function glfusion_121()
{
    global $_TABLES, $_FM_TABLES, $_CONF, $_DB_table_prefix;

    $_SQL = array();

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.2.1',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.2.1' WHERE name='glfusion'",1);
}

function glfusion_130()
{
    global $_TABLES, $_CONF, $_PLUGINS, $LANG_AM, $_DB_table_prefix;

    $_SQL = array();

    $_TABLES['autotags'] = $_DB_table_prefix . 'autotags';

    $_SQL[] = "CREATE TABLE IF NOT EXISTS {$_TABLES['autotags']} (
      tag varchar( 24 ) NOT NULL DEFAULT '',
      description varchar( 128 ) DEFAULT '',
      is_enabled tinyint( 1 ) NOT NULL DEFAULT '0',
      is_function tinyint( 1 ) NOT NULL DEFAULT '0',
      replacement text,
      PRIMARY KEY ( tag )
    ) ENGINE=MYISAM;";

    $_SQL[] = "CREATE TABLE IF NOT EXISTS {$_TABLES['autotag_perm']} (
      autotag_id varchar(128) NOT NULL,
      autotag_namespace varchar(128) NOT NULL,
      autotag_name varchar(128) NOT NULL,
      PRIMARY KEY (autotag_id)
    ) ENGINE=MyISAM";

    $_SQL[] = "CREATE TABLE IF NOT EXISTS {$_TABLES['autotag_usage']} (
      autotag_id varchar(128) NOT NULL,
      autotag_allowed tinyint(1) NOT NULL DEFAULT '1',
      usage_namespace varchar(128) NOT NULL,
      usage_operation varchar(128) NOT NULL,
      KEY autotag_id (autotag_id)
    ) ENGINE=MyISAM";

    $_SQL[] = "CREATE TABLE IF NOT EXISTS {$_TABLES['subscriptions']} (
      sub_id int(11) NOT NULL AUTO_INCREMENT,
      type varchar(128) NOT NULL,
      category varchar(128) NOT NULL DEFAULT '',
      category_desc varchar(255) NOT NULL DEFAULT '',
      id varchar(40) NOT NULL,
      id_desc varchar(255) NOT NULL DEFAULT '',
      uid int(11) NOT NULL,
      date_added datetime NOT NULL,
      PRIMARY KEY (`sub_id`),
      UNIQUE KEY type (type,category,id,uid),
      KEY uid (uid)
    ) ENGINE=MyISAM";

    $_SQL[] = "CREATE TABLE {$_TABLES['logo']} (
      id int(11) NOT NULL auto_increment,
      config_name varchar(255) default NULL,
      config_value varchar(255) NOT NULL,
      PRIMARY KEY  (id),
      UNIQUE KEY config_name (config_name)
    ) ENGINE=MyISAM;
    ";

    $_SQL[] = "CREATE TABLE {$_TABLES['menu']} (
      id int(11) NOT NULL auto_increment,
      menu_name varchar(64) NOT NULL,
      menu_type tinyint(4) NOT NULL,
      menu_active tinyint(3) NOT NULL,
      group_id mediumint(9) NOT NULL,
      PRIMARY KEY  (id),
      KEY menu_name (menu_name)
    ) ENGINE=MyISAM;";

    $_SQL[] = "CREATE TABLE {$_TABLES['menu_config']} (
      id int(11) NOT NULL auto_increment,
      menu_id int(11) NOT NULL,
      conf_name varchar(64) NOT NULL,
      conf_value varchar(64) NOT NULL,
      PRIMARY KEY  (id),
      UNIQUE KEY Config (menu_id,conf_name),
      KEY menu_id (menu_id)
    ) ENGINE=MyISAM;";

    $_SQL[] = "CREATE TABLE {$_TABLES['menu_elements']} (
      id int(11) NOT NULL auto_increment,
      pid int(11) NOT NULL,
      menu_id int(11) NOT NULL default '0',
      element_label varchar(255) NOT NULL,
      element_type int(11) NOT NULL,
      element_subtype varchar(255) NOT NULL,
      element_order int(11) NOT NULL,
      element_active tinyint(4) NOT NULL,
      element_url varchar(255) NOT NULL,
      element_target varchar(255) NOT NULL,
      group_id mediumint(9) NOT NULL,
      PRIMARY KEY( id ),
      INDEX ( pid )
    ) ENGINE=MyISAM;";

    $_SQL[] = "ALTER TABLE {$_TABLES['sessions']} ADD browser varchar(255) default '' AFTER sess_id";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='l F d, Y @h:iA' WHERE dfid=1";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='l F d, Y @H:i' WHERE dfid=2";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='l F d @H:i' WHERE dfid=4";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='H:i d F Y' WHERE dfid=5";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='H:i l d F Y' WHERE dfid=6";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='h:iA -- l F d Y' WHERE dfid=7";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='D F d, h:iA' WHERE dfid=8";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='D F d, H:i' WHERE dfid=9";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='m-d-y H:i' WHERE dfid=10";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='d-m-y H:i' WHERE dfid=11";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='m-d-y h:iA' WHERE dfid=12";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='h:iA  F d, Y' WHERE dfid=13";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='D M d, \'y h:iA' WHERE dfid=14";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='Day z, h ish' WHERE dfid=15";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='y-m-d h:i' WHERE dfid=16";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='d/m/y H:i' WHERE dfid=17";
    $_SQL[] = "UPDATE {$_TABLES['dateformats']} SET format='D d M h:iA' WHERE dfid=18";

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

    $complete = DB_getItem($_TABLES['vars'],'value','name="stcvt"');
    if ( $complete != 1 ) {
        if (!in_array('sitetailor', $_PLUGINS)) {
            $_TABLES['st_config']       = $_DB_table_prefix . 'st_config';
            $_TABLES['st_menus']        = $_DB_table_prefix . 'st_menus';
            $_TABLES['st_menus_config'] = $_DB_table_prefix . 'st_menus_config';
            $_TABLES['st_menu_elements']= $_DB_table_prefix . 'st_menu_elements';
        }
        $_SQL = array();
        $_SQL[] = "INSERT INTO {$_TABLES['logo']} SELECT * FROM {$_TABLES['st_config']}";
        $_SQL[] = "INSERT INTO {$_TABLES['menu']} SELECT * FROM {$_TABLES['st_menus']}";
        $_SQL[] = "INSERT INTO {$_TABLES['menu_config']} SELECT * FROM {$_TABLES['st_menus_config']}";
        $_SQL[] = "INSERT INTO {$_TABLES['menu_elements']} SELECT * FROM {$_TABLES['st_menu_elements']}";

        foreach ($_SQL as $sql) {
            DB_query($sql,1);
        }
        DB_query("UPDATE {$_TABLES['plugins']} SET pi_enabled=0 WHERE pi_name='sitetailor'",1);
        DB_query("INSERT INTO {$_TABLES['vars']} (name,value) VALUES ('stcvt','1')",1);
    }
    $_SQL = array();

    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='tl_menu_background_color' WHERE conf_name='main_menu_bg_color'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='tl_menu_background_color_hover' WHERE conf_name='main_menu_hover_bg_color'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='tl_menu_text_color' WHERE conf_name='main_menu_text_color'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='tl_menu_text_color_hover' WHERE conf_name='main_menu_hover_text_color'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='ch_menu_text_color' WHERE conf_name='submenu_text_color'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='ch_menu_text_color_hover' WHERE conf_name='submenu_hover_text_color'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='ch_menu_background_color' WHERE conf_name='submenu_background_color'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='ch_menu_background_color_hover' WHERE conf_name='submenu_hover_bg_color'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='ch_menu_element_border_top_color' WHERE conf_name='submenu_highlight_color'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='ch_menu_element_border_bottom_color' WHERE conf_name='submenu_shadow_color'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='tl_menu_background_image' WHERE conf_name='menu_bg_filename'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='tl_menu_text_hover_image' WHERE conf_name='menu_hover_filename'";
    $_SQL[] = "UPDATE {$_TABLES['menu_config']} SET conf_name='ch_menu_parent_image' WHERE conf_name='menu_parent_filename'";

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }
    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // logo
    $c->add('fs_logo', NULL, 'fieldset', 5, 28, NULL, 0, TRUE);
    $c->add('max_logo_height',150,'text',5,28,NULL,1630,TRUE);
    $c->add('max_logo_width', 500,'text',5,28,NULL,1640,TRUE);

    // whats new cache time
    $c->add('whatsnew_cache_time',3600,'text',3,15,NULL,1060,TRUE);

    // add user photo option to whosonline block
    $c->add('whosonline_photo',FALSE,'select',3,14,0,930,TRUE);

    $c->del('wikitext_editor','Core');
    $c->del('microsummary_short', 'Core');

    // add oauth user_login_method
    $standard = ($_CONF['user_login_method']['standard']) ? true : false;
    $openid = ($_CONF['user_login_method']['openid']) ? true : false;
    $thirdparty = ($_CONF['user_login_method']['3rdparty']) ? true: false;
    if ( isset($_CONF['user_login_method']['oauth'] ) ) {
        $oauth = $_CONF['user_login_method']['oauth'];
    } else {
        $oauth = false;
    }
    $c->del('user_login_method', 'Core');
    $c->add('user_login_method',array('standard' => $standard , 'openid' => $openid , '3rdparty' => $thirdparty , 'oauth' => $oauth),'@select',4,16,1,320,TRUE);

    // OAuth configuration settings

    if ( !isset($_CONF['facebook_login']) ) {
        $c->add('facebook_login',0,'select',4,16,1,350,TRUE);
        $c->add('facebook_consumer_key','not configured yet','text',4,16,NULL,351,TRUE);
        $c->add('facebook_consumer_secret','not configured yet','text',4,16,NULL,352,TRUE);
    }
    if ( !isset($_CONF['google_login']) ) {
        $c->add('google_login',0,'select',4,16,1,353,TRUE);
        $c->add('google_consumer_key','not configured yet','text',4,16,NULL,354,TRUE);
        $c->add('google_consumer_secret','not configured yet','text',4,16,NULL,355,TRUE);
    }
/*
    if ( !isset($_CONF['yahoo_login']) ) {
        $c->add('yahoo_login',0,'select',4,16,1,356,TRUE);
        $c->add('yahoo_consumer_key','not configured yet','text',4,16,NULL,357,TRUE);
        $c->add('yahoo_consumer_secret','not configured yet','text',4,16,NULL,358,TRUE);
    }
*/
    if ( !isset($_CONF['microsoft_login']) ) {
        $c->add('microsoft_login',0,'select',4,16,1,357,TRUE);
        $c->add('microsoft_consumer_key','not configured yet','text',4,16,NULL,358,TRUE);
        $c->add('microsoft_consumer_secret','not configured yet','text',4,16,NULL,359,TRUE);
    }
    if ( !isset($_CONF['linkedin_login']) ) {
        $c->add('linkedin_login',0,'select',4,16,1,358,TRUE);
        $c->add('linkedin_consumer_key','not configured yet','text',4,16,NULL,359,TRUE);
        $c->add('linkedin_consumer_secret','not configured yet','text',4,16,NULL,360,TRUE);
    }

    if ( !isset($_CONF['twitter_login']) ) {
        $c->add('twitter_login',0,'select',4,16,1,361,TRUE);
        $c->add('twitter_consumer_key','not configured yet','text',4,16,NULL,362,TRUE);
        $c->add('twitter_consumer_secret','not configured yet','text',4,16,NULL,363,TRUE);
    }

    $c->del('yahoo_login','Core');
    $c->del('yahoo_consumer_key','Core');
    $c->del('yahoo_consumer_secret','Core');
/*
    $c->del('microsoft_login','Core');
    $c->del('microsoft_consumer_key','Core');
    $c->del('microsoft_consumer_secret','Core');
*/
    // date / time format changes

    $c->add('date','l, F d Y @ h:i A T','text',6,29,NULL,370,TRUE);
    $c->add('daytime','m/d h:iA','text',6,29,NULL,380,TRUE);
    $c->add('shortdate','m/d/y','text',6,29,NULL,390,TRUE);
    $c->add('dateonly','d-M','text',6,29,NULL,400,TRUE);
    $c->add('timeonly','H:iA','text',6,29,NULL,410,TRUE);

    // hide what's new if empty
    $c->add('hideemptyblock',0,'select',3,15,0,1045,TRUE);

    // update check
    $c->add('fs_update', NULL, 'fieldset', 0, 7, NULL, 0, TRUE);
    $c->add('update_check_interval','86400','select',0,7,29,765,TRUE);
    $c->add('send_site_data',TRUE,'select',0,7,1,770,TRUE);

    // rating information
    $c->add('fs_rating',NULL, 'fieldset', 4,7,NULL,0,TRUE);
    $c->add('rating_speedlimit',15,'text',4,7,NULL,10,TRUE);

    // add new logo.admin permission
    $result = DB_query("SELECT * FROM {$_TABLES['features']} WHERE ft_name='logo.admin'");
    if ( DB_numRows($result) > 0 ) {
        COM_errorLog("glFusion 1.3.0 Development update: logo.admin permission already exists");
    } else {
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('logo.admin','Ability to modify site logo',1)",1);
        $ft_id  = DB_insertId();
        $grp_id = (int) DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'Root'");
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $grp_id)", 1);
    }

    // add new menu.admin permission
    $result = DB_query("SELECT * FROM {$_TABLES['features']} WHERE ft_name='menu.admin'");
    if ( DB_numRows($result) > 0 ) {
        COM_errorLog("glFusion 1.3.0 Development update: menu.admin permission already exists");
    } else {
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('menu.admin','Ability to create/edit site menus',1)",1);
        $ft_id  = DB_insertId();
        $grp_id = (int) DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'Root'");
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $grp_id)", 1);
    }

    // add new autotag_perm permission
/* ------------------
    $result = DB_query("SELECT * FROM {$_TABLES['features']} WHERE ft_name='autotag_perm.admin'");
    if ( DB_numRows($result) > 0 ) {
        COM_errorLog("glFusion 1.3.0 Development update: autotag_perm.admin permission already exists");
    } else {
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('autotag_perm.admin','AutoTag Permissions Admin',1)",1);
        $ft_id  = DB_insertId();
        $grp_id = (int) DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'Root'");
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $grp_id)", 1);
    }
------------------- */
    // forum update
    $c->del('pre2.5_mode', 'forum');
    $c->del('mysql4+', 'forum');
    $c->add('use_sfs', true, 'select',0, 2, 0, 135, true, 'forum');

    _forum_cvt_watch();
    _forum_fix_watch();
    // attachment handling...
    DB_query("ALTER TABLE {$_TABLES['ff_topic']} ADD attachments INT NOT NULL DEFAULT '0' AFTER views",1);
    $sql = "SELECT id FROM {$_TABLES['ff_topic']} WHERE pid=0";
    $result = DB_query($sql,1);
    while ( $F = DB_fetchArray($result) ) {
        $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic left join {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $F['id']. " OR topic.pid=".$F['id'].") and att.filename <> ''";
        $attResult = DB_query($sql,1);
        if ( DB_numRows($attResult) > 0 ) {
            list($attCount) = DB_fetchArray($attResult);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".$attCount." WHERE id=".(int) $F['id'],1);
        }
    }
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '3.3.0',pi_gl_version='1.3.0' WHERE pi_name = 'forum'");

    // autotag

    $_TABLES['am_autotags'] = $_DB_table_prefix . 'am_autotags';

    if ( DB_checkTableExists('am_autotags') ) {
        // we have an installed version of autotags plugin....
        DB_query("INSERT INTO {$_TABLES['autotags']} SELECT * FROM " . $_TABLES['am_autotags'],1);

        // delete the old autotag plugin
        require_once $_CONF['path_system'].'lib-install.php';
        $remvars = array (
            /* give the name of the tables, without $_TABLES[] */
            'tables' => array ( 'am_autotags' ),
            /* give the full name of the group, as in the db */
            'groups' => array('AutoTag Admin','AutoTag Users'),
            /* give the full name of the feature, as in the db */
            'features' => array('autotag.admin','autotag.user', 'autotag.PHP'),
            /* give the full name of the block, including 'phpblock_', etc */
            'php_blocks' => array(),
            /* give all vars with their name */
            'vars'=> array()
        );
        // removing tables
        for ($i=0; $i < count($remvars['tables']); $i++) {
            COM_errorLog ("Dropping table {$_TABLES[$remvars['tables'][$i]]}", 1);
            DB_query ("DROP TABLE {$_TABLES[$remvars['tables'][$i]]}", 1    );
            COM_errorLog ('...success', 1);
        }

        // removing variables
        for ($i = 0; $i < count($remvars['vars']); $i++) {
            COM_errorLog ("Removing variable {$remvars['vars'][$i]}", 1);
            DB_delete($_TABLES['vars'], 'name', $remvars['vars'][$i]);
            COM_errorLog ('...success', 1);
        }

        // removing groups
        for ($i = 0; $i < count($remvars['groups']); $i++) {
            $grp_id = DB_getItem ($_TABLES['groups'], 'grp_id',
                                  "grp_name = '{$remvars['groups'][$i]}'");
            if (!empty ($grp_id)) {
                COM_errorLog ("Attempting to remove the {$remvars['groups'][$i]} group", 1);
                DB_delete($_TABLES['groups'], 'grp_id', $grp_id);
                COM_errorLog ('...success', 1);
                COM_errorLog ("Attempting to remove the {$remvars['groups'][$i]} group from all groups.", 1);
                DB_delete($_TABLES['group_assignments'], 'ug_main_grp_id', $grp_id);
                COM_errorLog ('...success', 1);
            }
        }

        // removing features
        for ($i = 0; $i < count($remvars['features']); $i++) {
            $access_id = DB_getItem ($_TABLES['features'], 'ft_id',"ft_name = '{$remvars['features'][$i]}'");
            if (!empty ($access_id)) {
                COM_errorLog ("Attempting to remove {$remvars['features'][$i]} rights from all groups" ,1);
                DB_delete($_TABLES['access'], 'acc_ft_id', $access_id);
                COM_errorLog ('...success', 1);
                COM_errorLog ("Attempting to remove the {$remvars['features'][$i]} feature", 1);
                DB_delete($_TABLES['features'], 'ft_name', $remvars['features'][$i]);
                COM_errorLog ('...success', 1);
            }
        }
        if ($c->group_exists('autotag')) {
            $c->delGroup('autotag');
        }
        DB_delete($_TABLES['plugins'], 'pi_name', 'autotag');
    } else {
        $_DATA = array();
        $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('cipher', '{$LANG_AM['desc_cipher']}', 1, 1, NULL)";
        $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('topic', '{$LANG_AM['desc_topic']}', 1, 1, NULL)";
        $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('glfwiki', '{$LANG_AM['desc_glfwiki']}', 1, 1, NULL)";
        $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('lang', '{$LANG_AM['desc_lang']}', 0, 1, NULL)";
        $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('conf', '{$LANG_AM['desc_conf']}', 0, 1, NULL)";
        $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('user', '{$LANG_AM['desc_user']}', 0, 1, NULL)";
        $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('wikipedia', '{$LANG_AM['desc_wikipedia']}', 1, 1, NULL)";
        $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('youtube', '{$LANG_AM['desc_youtube']}', 1, 0, '<object width=\"425\" height=\"350\"><param name=\"movie\" value=\"http://www.youtube.com/v/%1%\"></param><param name=\"wmode\" value=\"transparent\"></param><embed src=\"http://www.youtube.com/v/%1%\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"425\" height=\"350\"></embed></object>')";
        foreach ($_DATA as $sql) {
            DB_query($sql,1);
        }
    }

    // add new autotag features
    $autotag_admin_ft_id = 0;
    $autotag_php_ft_id   = 0;
    $autotag_group_id    = 0;
    $result = DB_query("SELECT * FROM {$_TABLES['features']} WHERE ft_name='autotag.admin'");
    if ( DB_numRows($result) > 0 ) {
        COM_errorLog("glFusion 1.3.0 Development update: autotag.admin permission already exists");
    } else {
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('autotag.admin','Ability to create / edit autotags',1)",1);
        $autotag_admin_ft_id  = DB_insertId();
    }
    $result = DB_query("SELECT * FROM {$_TABLES['features']} WHERE ft_name='autotag.PHP'");
    if ( DB_numRows($result) > 0 ) {
        COM_errorLog("glFusion 1.3.0 Development update: autotag.PHP permission already exists");
    } else {
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('autotag.PHP','Ability to create / edit autotags utilizing PHP functions',1)",1);
        $autotag_php_ft_id  = DB_insertId();
    }
    // now check for the group
    $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='Autotag Admin'");
    if ( DB_numRows($result) > 0 ) {
        COM_errorLog("glFusion 1.3.0 Development update: Autotag Admin group already exists");
    } else {
        DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core, grp_default) VALUES ('Autotag Admin','Has full access to create and modify autotags',1,0)",1);
        $autotag_group_id  = DB_insertId();
    }
    if ( $autotag_admin_ft_id != 0 && $autotag_group_id != 0 ) {
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (".$autotag_admin_ft_id.",".$autotag_group_id.")",1);
    }
    if ( $autotag_php_ft_id != 0 && $autotag_group_id != 0 ) {
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (".$autotag_php_ft_id.",".$autotag_group_id.")",1);
    }
    if ( $autotag_group_id != 0 ) {
        DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id,ug_grp_id) VALUES (".$autotag_group_id.",1)");
    }

    // update syndication feeds
    DB_query("UPDATE {$_TABLES['syndication']} set format='RSS-0.91' WHERE format='RSS-09x'",1);
    DB_query("UPDATE {$_TABLES['syndication']} set format='RDF-1.0' WHERE format='RSS-1.0'",1);

    DB_query("UPDATE {$_TABLES['mg_config']} set rss_feed_type='RSS-2.0' WHERE rss_feed_type='RSS2.0'",1);
    DB_query("UPDATE {$_TABLES['mg_config']} set rss_feed_type='RSS-1.0' WHERE rss_feed_type='RSS1.0'",1);
    DB_query("UPDATE {$_TABLES['mg_config']} set rss_feed_type='RSS-0.91' WHERE rss_feed_type='RSS0.91'",1);

    // remove microsummary feature
    $c->del('microsummary_short', 'Core');

    // alter the users table
    DB_query("ALTER TABLE {$_TABLES['users']} ADD account_type smallint(5) NOT NULL default '1' AFTER status",1);

    _updateConfig();

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.3.0',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.3.0' WHERE name='glfusion'",1);
}

function glfusion_132()
{
    global $_TABLES, $_CONF, $_PLUGINS, $LANG_AM, $_DB_table_prefix, $_CP_CONF;

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    if ( !isset($_CP_CONF['pc_publickey'] ) ) {
        $c->add('pc_publickey', '','text',0, 0, 0, 48, true, 'captcha');
        $c->add('pc_privatekey', '','text',0, 0, 0, 49, true, 'captcha');
    }

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.3.2',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.3.2' WHERE name='glfusion'",1);
}

function glfusion_140()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $_DB_table_prefix, $_CP_CONF;

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // remove menu_elements - no longer used
    $c->del('menu_elements','Core');
    $c->del('mailstory_postmode','Core');
    $c->del('comment_editor','Core');
    $c->del('advanced_editor','Core');

    // add mailuser_postmode

    if ( !isset($_CONF['mailuser_postmode'] ) ) {
        $c->add('mailuser_postmode','html','select',4,5,5,43,TRUE);
    }

    // set the initial set of html elements
    if ( !isset($_CONF['htmlfilter_comment']) ) {
        $c->add('htmlfilter_default','p,b,a,i,strong,em,br','text',7,5,NULL,30,true);
        $c->add('htmlfilter_comment','p,b,a,i,strong,em,br,tt,hr,li,ol,ul,code,pre','text',7,5,NULL,35,TRUE);
        $c->add('htmlfilter_story','p,b,a,i,strong,em,br,tt,hr,li,ol,ul,code,pre,blockquote,img','text',7,5,NULL,40,TRUE);
        $c->add('htmlfilter_root','div,span,table,tr,td,th','text',7,5,NULL,50,TRUE);
    }

    if ( !isset($_FF_CONF['allowed_html']) ) {
        $c->add('allowed_html','p,b,i,strong,em,br,pre,img,ol,ul,li,u', 'text',0, 2, 0, 82, true, 'forum');
    }

    $sql = "REPLACE INTO ".$_TABLES['autotags']." (tag, description, is_enabled, is_function, replacement) VALUES ('youtube', 'Embed Youtube videos into content. Usage:[youtube:ID height:PX width:PX align:LEFT/RIGHT pad:PX]', 1, 1, NULL)";
    DB_query($sql);

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.4.0',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.4.0' WHERE name='glfusion'",1);
}

function glfusion_141()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $_DB_table_prefix, $_CP_CONF;

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    $c->add('fs_filemanager_general', NULL, 'fieldset', 0, 2, NULL, 0, true, 'ckeditor');
    $c->add('filemanager_fileroot', '/images/library/usersfiles/', 'text', 0, 2, NULL, 20, true, 'ckeditor');
    $c->add('filemanager_per_user_dir', true, 'select', 0, 2, 1, 30, true, 'ckeditor');
    $c->add('filemanager_browse_only', false, 'select', 0, 2, 1, 40, true, 'ckeditor');
    $c->add('filemanager_default_view_mode', 'grid', 'select', 0, 2, 2, 50, true, 'ckeditor');
    $c->add('filemanager_show_confirmation', true, 'select', 0, 2, 1, 60, true, 'ckeditor');
    $c->add('filemanager_search_box', true, 'select', 0, 2, 1, 70, true, 'ckeditor');
    $c->add('filemanager_file_sorting', 'default', 'select', 0, 2, 3, 80, true, 'ckeditor');
    $c->add('filemanager_chars_only_latin', true, 'select', 0, 2, 1, 90, true, 'ckeditor');
    $c->add('filemanager_date_format', 'Y-m-d H:i:s', 'text', 0, 2, NULL, 100, true, 'ckeditor');
    $c->add('filemanager_show_thumbs', true, 'select', 0, 2, 1, 120, true, 'ckeditor');
    $c->add('filemanager_generate_thumbnails', true, 'select', 0, 2, 1, 130, true, 'ckeditor');
    $c->add('fs_filemanager_upload', NULL, 'fieldset', 0, 3, NULL, 0, true, 'ckeditor');
    $c->add('filemanager_upload_restrictions', 'jpg,jpeg,gif,png,svg,txt,pdf,odp,ods,odt,rtf,doc,docx,xls,xlsx,ppt,pptx,ogv,mp4,webm,ogg,mp3,wav', 'text', 0, 3, NULL, 10, true, 'ckeditor');
    $c->add('filemanager_upload_overwrite', false, 'select', 0, 3, 1, 20, true, 'ckeditor');
    $c->add('filemanager_upload_images_only', false, 'select', 0, 3, 1, 30, true, 'ckeditor');
    $c->add('filemanager_upload_file_size_limit', 16, 'text', 0, 3, NULL, 40, true, 'ckeditor');
    $c->add('filemanager_unallowed_files', '.htaccess,web.config', 'text', 0, 3, NULL, 50, true, 'ckeditor');
    $c->add('filemanager_unallowed_dirs', '_thumbs,.CDN_ACCESS_LOGS,cloudservers', 'text', 0, 3, NULL, 60, true, 'ckeditor');
    $c->add('filemanager_unallowed_files_regexp', '/^\\./uis', 'text', 0, 3, NULL, 70, true, 'ckeditor');
    $c->add('filemanager_unallowed_dirs_regexp', '/^\\./uis', 'text', 0, 3, NULL, 80, true, 'ckeditor');
    $c->add('fs_filemanager_images', NULL, 'fieldset', 0, 4, NULL, 0, true, 'ckeditor');
    $c->add('filemanager_images_ext', 'jpg,jpeg,gif,png,svg', 'text', 0, 4, NULL, 10, true, 'ckeditor');
    $c->add('fs_filemanager_videos', NULL, 'fieldset', 0, 5, NULL, 0, true, 'ckeditor');
    $c->add('filemanager_show_video_player', true, 'select', 0, 5, 1, 10, true, 'ckeditor');
    $c->add('filemanager_videos_ext', 'ogv,mp4,webm', 'text', 0, 5, NULL, 20, true, 'ckeditor');
    $c->add('filemanager_videos_player_width', 400, 'text', 0, 5, NULL, 30, true, 'ckeditor');
    $c->add('filemanager_videos_player_height', 222, 'text', 0, 5, NULL, 40, true, 'ckeditor');
    $c->add('fs_filemanager_audios', NULL, 'fieldset', 0, 6, NULL, 0, true, 'ckeditor');
    $c->add('filemanager_show_audio_player', true, 'select', 0, 6, 1, 10, true, 'ckeditor');
    $c->add('filemanager_audios_ext', 'ogg,mp3,wav', 'text', 0, 6, NULL, 20, true, 'ckeditor');
    $c->add('fs_filemanager_editor', NULL, 'fieldset', 0, 7, NULL, 0, true, 'ckeditor');
    $c->add('filemanager_edit_enabled', false, 'select', 0, 7, 1, 10, true, 'ckeditor');
    $c->add('filemanager_edit_linenumbers', true, 'select', 0, 7, 1, 20, true, 'ckeditor');
    $c->add('filemanager_edit_linewrapping', true, 'select', 0, 7, 1, 30, true, 'ckeditor');
    $c->add('filemanager_edit_codehighlight', false, 'select', 0, 7, 1, 40, true, 'ckeditor');
    $c->add('filemanager_edit_editext', 'txt,csv', 'text', 0, 7, NULL, 50, true, 'ckeditor');

    if ( !isset($_CONF['github_login']) ) {
        $c->add('github_login',0,'select',4,1,1,271,TRUE);
        $c->add('github_consumer_key','not configured yet','text',4,1,NULL,272,TRUE);
        $c->add('github_consumer_secret','not configured yet','text',4,1,NULL,273,TRUE);
    }


    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.4.1',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.4.1' WHERE name='glfusion'",1);
}

function glfusion_142()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $_DB_table_prefix, $_CP_CONF;

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.4.2',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.4.2' WHERE name='glfusion'",1);
}

function glfusion_143()
{
    global $_TABLES, $_CONF, $_FF_CONF, $_PLUGINS, $LANG_AM, $_DB_table_prefix, $_CP_CONF;

    // new config options
    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();
    $c->add('min_username_length','4','text',4,4,NULL,60,TRUE);

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.4.3',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.4.3' WHERE name='glfusion'",1);
}


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

    _updateConfig();

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
            COM_errorLog("dvlpupdate: Error retrieving non-loggedin-user group id");
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


    _updateConfig();

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

    _updateConfig();

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

    _updateConfig();

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

    _updateConfig();

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

// add comment queued field
    $_SQL[] = "ALTER TABLE {$_TABLES['comments']} ADD queued TINYINT(3) NOT NULL DEFAULT '0' AFTER pid;";

    $_SQL[] = "INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core) VALUES ('Comment Admin', 'Can moderate comments', 1)";
    $_SQL[] = "INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('comment.moderate', 'Ability to moderate comments', 1)";
    $_SQL[] = "INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('comment.submit', 'Comments bypass submission queue', 1)";

    foreach ($_SQL as $sql) {
        DB_query($sql,1);
    }

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

    _updateConfig();

    // update version number
    DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.7.0',name='glfusion'",1);
    DB_query("UPDATE {$_TABLES['vars']} SET value='1.7.0' WHERE name='glfusion'",1);

}

function _updateConfig() {
    global $_CONF, $_TABLES, $coreConfigData;

    $site_url = $_CONF['site_url'];
    $cookiesecure = $_CONF['cookiesecure'];
    $c = config::get_instance();

    require_once $_CONF['path'].'sql/core_config_data.php';

    // remove stray items
    $result = DB_query("SELECT * FROM {$_TABLES['conf_values']} WHERE group_name='Core'");
    while ( $row = DB_fetchArray($result) ) {
        $item = $row['name'];
        if ( ($key = _searchForIdKey($item,$coreConfigData)) === NULL ) {
            DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE name='".DB_escapeString($item)."' AND group_name='Core'");
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

$use_innodb = false;
if (($_DB_dbms == 'mysql') && (DB_getItem($_TABLES['vars'], 'value', "name = 'database_engine'") == 'InnoDB')) {
    $use_innodb = true;
}

$retval .= 'Performing database upgrades if necessary...<br />';

glfusion_170();

$stdPlugins=array('staticpages','spamx','links','polls','calendar','sitetailor','captcha','bad_behavior2','forum','mediagallery','filemgmt','commentfeeds');
foreach ($stdPlugins AS $pi_name) {
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_gl_version='".GVERSION."', pi_homepage='http://www.glfusion.org' WHERE pi_name='".$pi_name."'",1);
}

// need to clear the template cache so do it here
CTL_clearCache();

header('Location: '.$_CONF['site_admin_url'].'/plugins.php?msg=600');
exit;
?>
