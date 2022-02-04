<?php
/**
* glFusion CMS - Forum Plugin
*
* Plugin Upgrade
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2010 by the following authors:
*   Blaine Lang          blaine AT portalparts DOT com
*                        www.portalparts.com
*   Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com
*   Prototype & Concept :        Mr.GxBlock, www.gxblock.com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

require_once $_CONF['path'].'plugins/forum/forum.php';

use \glFusion\Log\Log;

/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function forum_upgrade() {
    global $_CONF, $_TABLES, $_FF_CONF, $_DB_dbms;

    require_once $_CONF['path_system'] . 'classes/config.class.php';

    $curversion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name = 'forum'");

    switch ($curversion) {
        case "2.3" :
        case "2.3.2" :
            if (upgrade_232() == 0 )  {
                DB_query("UPDATE {$_TABLES['plugins']} SET `pi_version` = '2.5RC1' WHERE `pi_name` = 'forum' LIMIT 1");
            } else {
                return false;
            }
        case "2.5RC1" :
            if (upgrade_25() == 0 )  {
                DB_query("UPDATE {$_TABLES['plugins']} SET `pi_version` = '2.7', `pi_gl_version` = '1.4.1' WHERE `pi_name` = 'forum' LIMIT 1");
            } else {
                return false;
            }
        case "2.6" :
        case "2.7" :
        case "2.7.1" :
        case "2.7.2" :
            if (upgrade_30() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET `pi_version` = '3.0', `pi_gl_version` = '1.0.0' WHERE `pi_name` = 'forum' LIMIT 1");
            } else {
                return false;
            }
        case "3.0.0" :
        case "3.0" :
            // need to migrate the configuration to our new online configuration.
            require_once $_CONF['path_system'] . 'classes/config.class.php';
            require_once $_CONF['path'] . 'plugins/forum/install_defaults.php';
            plugin_initconfig_forum();
            include $_CONF['path'].'plugins/forum/forum.php';
        case "3.1.0.fusion" :
            $c = config::get_instance();
            $c->add('enable_fm_integration', 0, 'select',
                    0,1,0,120, true, 'forum');
            $c->add('allow_memberlist', 0, 'select',
                    0, 0, 0, 25, true, 'forum');
            $c->del('show_popular_perpage','forum');
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '3.1.1',pi_gl_version='1.1.2' WHERE pi_name = 'forum'");
        case '3.1.1' :
        case '3.1.2' :
        case '3.1.3' :
            $c = config::get_instance();
            $c->add('enable_user_rating_system',FALSE, 'select', 0,0,0,22, TRUE, 'forum');
            $c->add('bbcode_signature', TRUE, 'select',0, 0, 0, 37, true, 'forum');
            $c->add('use_wysiwyg_editor', false, 'select', 0, 2, 0, 85, true, 'forum');
            DB_query("ALTER TABLE {$_TABLES['ff_forums']} ADD `rating_view` INT( 8 ) NOT NULL ,ADD `rating_post` INT( 8 ) NOT NULL",1);
            DB_query("ALTER TABLE {$_TABLES['ff_userinfo']} ADD `rating` INT( 8 ) NOT NULL DEFAULT '0'");
            DB_query("ALTER TABLE {$_TABLES['ff_userinfo']} ADD signature MEDIUMTEXT" );
            DB_query("ALTER TABLE {$_TABLES['ff_userprefs']} ADD notify_full tinyint(1) NOT NULL DEFAULT '0' AFTER alwaysnotify");
            $sql = "CREATE TABLE IF NOT EXISTS {$_TABLES['ff_rating_assoc']} ( "
                    . "`user_id` mediumint( 9 ) NOT NULL , "
                    . "`voter_id` mediumint( 9 ) NOT NULL , "
                    . "`grade` smallint( 6 ) NOT NULL  , "
                    . "`topic_id` int( 11 ) NOT NULL , "
                    . " KEY `user_id` (`user_id`), "
                    . " KEY `voter_id` (`voter_id`) );";
            DB_query($sql);
            // add forum.html feature
            DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('forum.html','Can post using HTML',0)",1);
            $ft_id = DB_insertId();
            $grp_id = intval(DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'forum Admin'"));
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $grp_id)", 1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '3.1.4',pi_gl_version='1.1.5' WHERE pi_name = 'forum'");
        case '3.1.4' :
            DB_query("ALTER TABLE {$_TABLES['ff_rating_assoc']} DROP PRIMARY KEY",1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '3.1.5',pi_gl_version='1.1.6' WHERE pi_name = 'forum'");
        case '3.1.5' :
        case '3.1.6' :
        case '3.1.7' :
            DB_query("ALTER TABLE {$_TABLES['ff_userprefs']} ADD topic_order varchar(10) NOT NULL DEFAULT 'ASC' AFTER notify_once");
            DB_query("ALTER TABLE {$_TABLES['ff_userprefs']} ADD use_wysiwyg_editor tinyint(3) NOT NULL DEFAULT '1' AFTER topic_order");
            DB_query("ALTER TABLE {$_TABLES['ff_topic']} ADD `status` int(10) unsigned NOT NULL DEFAULT '0' AFTER locked");

            $c = config::get_instance();
            $c->add('bbcode_disabled', 0, 'select', 0, 2, 6, 165, true, 'forum');
            $c->add('smilies_disabled', 0, 'select', 0, 2, 6, 170, true, 'forum');
            $c->add('urlparse_disabled', 0, 'select', 0, 2, 6, 175, true, 'forum');
        case '3.2.0' :
            // convert watch records
            $c = config::get_instance();
            $c->del('pre2.5_mode', 'forum');
            $c->del('mysql4+', 'forum');
            $c->add('use_sfs', true, 'select',0, 2, 0, 135, true, 'forum');

            DB_query("UPDATE {$_TABLES['conf_values']} SET value='s:11:\"m/d/y h:i a\";' WHERE name='default_Datetime_format' AND group_name='forum'");
            DB_query("UPDATE {$_TABLES['conf_values']} SET value='s:11:\"M d Y H:i a\";' WHERE name='default_Topic_Datetime_format' AND group_name='forum'");

            _forum_cvt_watch();
            // drop watch table

            // attachment handling...
            DB_query("ALTER TABLE {$_TABLES['ff_topic']} ADD attachments INT NOT NULL DEFAULT '0' AFTER views");
            $sql = "SELECT id FROM {$_TABLES['ff_topic']} WHERE pid=0";
            $result = DB_query($sql);
            while ( $F = DB_fetchArray($result) ) {
                $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic left join {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $F['id']. " OR topic.pid=".$F['id'].") and att.filename <> ''";
                $attResult = DB_query($sql);
                if ( DB_numRows($attResult) > 0 ) {
                    list($attCount) = DB_fetchArray($attResult);
                    DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".$attCount." WHERE id=".(int) $F['id']);
                }
            }
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '3.3.0',pi_gl_version='1.3.0' WHERE pi_name = 'forum'");
        case '3.3.0' :
            $c = config::get_instance();
            $c->add('allowed_html','p,b,i,strong,em,br,pre,img,ol,ul,li,u', 'text',0, 2, 0, 82, true, 'forum');
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '3.3.1',pi_gl_version='1.4.0' WHERE pi_name = 'forum'");

        case '3.3.1' :
            DB_query("ALTER TABLE {$_TABLES['ff_banned_ip']} CHANGE `host_ip` `host_ip` VARCHAR(128) NULL DEFAULT NULL;");

        case '3.3.2' :
            DB_query("ALTER TABLE {$_TABLES['ff_topic']} ADD `lastedited` VARCHAR(12) NULL DEFAULT NULL AFTER `lastupdated`;",1);

        case '3.3.3' :
            $c = config::get_instance();
            $c->del('default_Datetime_format','forum');
            $c->del('default_Topic_Datetime_format','forum');

        case '3.3.4' :
            $_FF_DEFAULT = array();
            $_FF_DEFAULT['geshi_line_numbers']     = false;
            $_FF_DEFAULT['geshi_overall_style']    = 'font-size: 12px; color: #000066; border: 1px solid #d0d0d0; background-color: #fafafa;margin-top:5px;margin-bottom:5px;';
            $_FF_DEFAULT['geshi_line_style']       = 'font: normal normal 95% \'Courier New\', Courier, monospace; color: #003030;font-weight: 700; color: #006060; background: #fcfcfc;';
            $_FF_DEFAULT['geshi_code_style']       = 'color: #000020;';
            $_FF_DEFAULT['geshi_header_style']     = 'font-family: Verdana, Arial, sans-serif; color: #fff; font-size: 90%; font-weight: 700; background-color: #3299D6; border-bottom: 1px solid #d0d0d0; padding: 2px;';

            $c = config::get_instance();
            $c->add('geshi_line_numbers', $_FF_DEFAULT['geshi_line_numbers'], 'select',0, 2, 0, 121, true, 'forum');
            $c->add('geshi_line_style', $_FF_DEFAULT['geshi_line_style'], 'text',0, 2, 0, 122, true, 'forum');
            $c->add('geshi_overall_style', $_FF_DEFAULT['geshi_overall_style'], 'text',0, 2, 0, 123, true, 'forum');
            $c->add('geshi_code_style', $_FF_DEFAULT['geshi_code_style'], 'text',0, 2, 0, 124, true, 'forum');
            $c->add('geshi_header_style', $_FF_DEFAULT['geshi_header_style'], 'text',0, 2, 0, 125, true, 'forum');

        case '3.3.5' :
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

            $_SQL['ff_ranks'] = "CREATE TABLE {$_TABLES['ff_ranks']} (
              `posts` int(11) unsigned NOT NULL DEFAULT '0',
              `dscp` varchar(40) NOT NULL DEFAULT '',
              PRIMARY KEY (`posts`)
            ) ENGINE=MyISAM;";

            $_SQL['ff_likes_assoc'] = "CREATE TABLE `{$_TABLES['ff_likes_assoc']}` (
              `poster_id` mediumint(9) NOT NULL,
              `voter_id` mediumint(9) NOT NULL,
              `topic_id` int(11) NOT NULL,
              `username` varchar(40),
              `like_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`poster_id`,`voter_id`,`topic_id`),
              KEY `voter_id` (`voter_id`),
              KEY `poster_id` (`poster_id`)
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

            if (($_DB_dbms == 'mysql') && (DB_getItem($_TABLES['vars'], 'value', "name = 'database_engine'") == 'InnoDB')) {
                $use_innodb = true;
            } else {
                $use_innodb = false;
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
            $c = config::get_instance();
            $c->del('grouptags','forum');

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

        case '3.4.0' :
            DB_query("ALTER TABLE {$_TABLES['ff_badges']} ADD `fb_inherited` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `fb_enabled`;",1);
            // Change badge css designators to actual color strings
            $bg_success = 'a:2:{s:7:"fgcolor";s:7:"#ffffff";s:7:"bgcolor";s:7:"#82bb42";}';
            $bg_danger  = 'a:2:{s:7:"fgcolor";s:7:"#ffffff";s:7:"bgcolor";s:7:"#d32c46";}';
            $bg_warning = 'a:2:{s:7:"fgcolor";s:7:"#ffffff";s:7:"bgcolor";s:7:"#faa732";}';
            $bg_default = 'a:2:{s:7:"fgcolor";s:7:"#ffffff";s:7:"bgcolor";s:7:"#009dd8";}';
            DB_query("UPDATE {$_TABLES['ff_badges']} SET fb_data='".DB_escapeString($bg_success)."' WHERE fb_data='uk-badge-success'",1);
            DB_query("UPDATE {$_TABLES['ff_badges']} SET fb_data='".DB_escapeString($bg_danger)."' WHERE fb_data='uk-badge-danger'",1);
            DB_query("UPDATE {$_TABLES['ff_badges']} SET fb_data='".DB_escapeString($bg_warning)."' WHERE fb_data='uk-badge-warning'",1);
            DB_query("UPDATE {$_TABLES['ff_badges']} SET fb_data='".DB_escapeString($bg_default)."' WHERE fb_data=''",1);

        case '3.4.1' :
            DB_query("ALTER TABLE {$_TABLES['ff_topic']} ADD `lastedited` VARCHAR(12) NULL DEFAULT NULL AFTER `lastupdated`;",1);

        case '3.4.2' :
            // no changes to db schema

        case '3.4.3' :
        case '3.4.3.1' :
            $_SQL = array(
            "CREATE TABLE `{$_TABLES['ff_warnings']}` (
                `w_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `w_uid` int(11) unsigned NOT NULL DEFAULT 0,
                `wt_id` int(11) unsigned NOT NULL DEFAULT 0,
                `w_topic_id` int(1) unsigned NOT NULL DEFAULT 0,
                `w_dscp` varchar(255) NOT NULL DEFAULT '',
                `ts` int(11) unsigned NOT NULL DEFAULT 0,
                `w_points` int(5) unsigned NOT NULL DEFAULT 0,
                `w_expires` int(11) unsigned NOT NULL DEFAULT 0,
                `w_issued_by` int(11) unsigned NOT NULL DEFAULT 0,
                `revoked_date` int(11) unsigned NOT NULL DEFAULT 0,
                `revoked_by` int(11) unsigned NOT NULL DEFAULT 0,
                `revoked_reason` varchar(255) NOT NULL DEFAULT '',
                `w_notes` text NOT NULL,
                PRIMARY KEY (`w_id`),
                KEY `uid_expires` (`w_uid`,`w_expires`)
                ) ENGINE=MyISAM;",
            "CREATE TABLE `{$_TABLES['ff_warningtypes']}` (
                `wt_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `wt_dscp` varchar(120) NOT NULL DEFAULT '',
                `wt_points` smallint(5) unsigned NOT NULL DEFAULT 0,
                `wt_expires_qty` int(5) unsigned NOT NULL DEFAULT 1,
                `wt_expires_period` varchar(7) NOT NULL DEFAULT 'day',
                PRIMARY KEY (`wt_id`)
                ) ENGINE=MyISAM;",
            "CREATE TABLE `{$_TABLES['ff_warninglevels']}` (
                `wl_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `wl_pct` int(3) unsigned NOT NULL DEFAULT 0,
                `wl_action` smallint(5) unsigned NOT NULL DEFAULT 0,
                `wl_duration` int(11) unsigned NOT NULL DEFAULT 86400,
                `wl_duration_qty` int(5) unsigned NOT NULL DEFAULT 1,
                `wl_duration_period` varchar(7) NOT NULL DEFAULT 'day',
                `wl_other` varchar(255) NOT NULL DEFAULT 'a:0{}',
                PRIMARY KEY (`wl_id`)
                ) ENGINE=MyISAM;",
            "ALTER TABLE {$_TABLES['ff_userinfo']}
               ADD `ban_expires` int(11) NOT NULL DEFAULT  0",
            "ALTER TABLE {$_TABLES['ff_userinfo']}
                ADD `suspend_expires` int(11) NOT NULL DEFAULT 0",
            "ALTER TABLE {$_TABLES['ff_userinfo']}
                ADD `moderate_expires` int(11) NOT NULL DEFAULT 0",
            "ALTER TABLE {$_TABLES['ff_topic']}
                ADD `approved` tinyint(1) unsigned NOT NULL DEFAULT 1",
            );
            "ALTER TABLE {$_TABLES['ff_userinfo']} CHANGE `rating` `rating` INT( 8 ) NOT NULL DEFAULT '0'";

            if (($_DB_dbms == 'mysql') && (DB_getItem($_TABLES['vars'], 'value', "name = 'database_engine'") == 'InnoDB')) {
                $use_innodb = true;
            } else {
                $use_innodb = false;
            }
            foreach ($_SQL AS $sql) {
                if ($use_innodb) {
                    $sql = str_replace('MyISAM', 'InnoDB', $sql);
                }
                DB_query($sql,1);
            }
            // default data for warnings
            $_SQL = array();
            $_SQL[] = "INSERT INTO `{$_TABLES['ff_warninglevels']}` (`wl_id`, `wl_pct`, `wl_action`, `wl_duration`, `wl_duration_qty`, `wl_duration_period`, `wl_other`)
                    VALUES
                        (18,20,1,86400,1,'day','a:0{}'),
                        (19,60,2,1209600,2,'week','a:0{}'),
                        (20,85,15,2592000,1,'month','a:0{}'),
                        (21,99,127,86400,1,'day','a:0{}');";

            $_SQL[] = "INSERT INTO `{$_TABLES['ff_warningtypes']}` (`wt_id`, `wt_dscp`, `wt_points`, `wt_expires_qty`, `wt_expires_period`)
                       VALUES
                        (8,'Spam',20,1,'day'),
                        (9,'Inappropriate',30,1,'week'),
                        (10,'Harassing Members',45,2,'month');";

            foreach ($_SQL AS $sql) {
                DB_query($sql,1);
            }

        case '3.4.3.2' : // internal dev version
            // no changes

        default :
            forum_update_config();
            DB_query("ALTER TABLE {$_TABLES['ff_forums']} DROP INDEX forum_id",1);
            DB_query("ALTER TABLE {$_TABLES['ff_rating_assoc']} DROP PRIMARY KEY",1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '".$_FF_CONF['pi_version']."',pi_gl_version='".$_FF_CONF['gl_version']."' WHERE pi_name = 'forum'");
            return true;
    }
}

function upgrade_232() {
    global $_TABLES;

    $_SQL = array();


    // Version 2.3 to 2.3.2 added one field - Add if this field does not exist
    $fields = DB_query("SHOW COLUMNS FROM {$_TABLES['ff_userprefs']}");
    while ($A = DB_fetchArray($fields)) {
        if (in_array($A['Field'],array('enablenotify'))) {
            $fieldfound = true;
        }
    }
    if (!$fieldfound) {
        $_SQL[] = "ALTER TABLE {$_TABLES['ff_userprefs']} ADD enablenotify tinyint(1) DEFAULT '1' NOT NULL AFTER viewanonposts";
    }

    /* Add new forum table fields */
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD is_hidden tinyint(1) DEFAULT '0' NOT NULL AFTER grp_id";
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD is_readonly tinyint(1) DEFAULT '0' NOT NULL AFTER is_hidden";
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD no_newposts tinyint(1) DEFAULT '0' NOT NULL AFTER is_readonly";

    $_SQL[] = "ALTER TABLE {$_TABLES['ff_moderators']} ADD mod_uid mediumint(8) DEFAULT '0' NOT NULL AFTER mod_id";
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_moderators']} ADD mod_groupid mediumint(8) DEFAULT '0' NOT NULL AFTER mod_uid";

    /* Add new userprefs field */
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_userprefs']} ADD notify_once tinyint(1) DEFAULT '0' NOT NULL AFTER showiframe";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        Log::write('system',Log::INFO,'Forum Plugin 2.5 update: Executing SQL => ' . current($_SQL));
        DB_query(current($_SQL),'1');
        if (DB_error()) {
            Log::write('system',Log::ERROR,'SQL Error during Forum plugin update');
            return 1;
            break;
        }
        next($_SQL);
    }

    if (!DB_count($_TABLES['blocks'],'phpblockfn','phpblock_forum_menu')) {
        // Add new block definition for Forum Menu
        $fields = 'is_enabled,name,type,title,tid,blockorder,onleft,phpblockfn,group_id,owner_id,perm_owner,perm_group,perm_members,perm_anon';
        $sql = "INSERT INTO {$_TABLES['blocks']} ($fields) " ;
        $sql .= "VALUES (0, 'forum_menu', 'phpblock', 'Forum Menu', 'all', 0, 1, 'phpblock_forum_menu', 2,2,3,2,2,2)";
        DB_query($sql);
    }

    // Update the moderator records - now that we have a uid field
    $query = DB_query("SELECT mod_id,mod_username FROM {$_TABLES['ff_moderators']}");
    while ($A = DB_fetchArray($query)) {
        $mod_uid = DB_getItem($_TABLES['users'],'uid',"username='{$A['mod_username']}'");
        if ($mod_uid > 0) {
            DB_query("UPDATE {$_TABLES['ff_moderators']} SET mod_uid = $mod_uid WHERE mod_id={$A['mod_id']}");
        }
    }
    Log::write('system',Log::INFO,'Success - Completed Forum plugin version 2.5 update');
    return 0;
}


function upgrade_25() {
    global $_TABLES;

    $_SQL = array();

    /* Add new fields */
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD topic_count mediumint(8) DEFAULT '0' NOT NULL AFTER no_newposts";
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD post_count mediumint(8) DEFAULT '0' NOT NULL AFTER topic_count";
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD last_post_rec mediumint(8) DEFAULT '0' NOT NULL AFTER post_count";
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} ADD last_reply_rec mediumint(8) DEFAULT '0' NOT NULL AFTER lastupdated";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        Log::write('system',Log::INFO,'Forum Plugin 2.6 update: Executing SQL => ' . current($_SQL));
        DB_query(current($_SQL),'1');
        if (DB_error()) {
            Log::write('system',Log::ERROR,'SQL Error during Forum plugin update');
            return 1;
            break;
        }
        next($_SQL);
    }

    Log::write('system',Log::INFO,'Success - Completed Forum plugin version 2.6 update');
    return 0;

}

function upgrade_30() {
    global $_TABLES;

    $_SQL = array();

    $_SQL[] = "CREATE TABLE IF NOT EXISTS {$_TABLES['ff_bookmarks']} (
      uid mediumint(8) NOT NULL,
      topic_id int(11) NOT NULL,
      pid int(11) NOT NULL default '0',
      KEY topic_id (`topic_id`),
      KEY pid (pid),
      KEY uid (uid)
    ) ENGINE=MyISAM ;";


    $_SQL[] = "CREATE TABLE IF NOT EXISTS {$_TABLES['ff_attachments']} (
      id int(11) NOT NULL auto_increment,
      topic_id int(11) NOT NULL,
      repository_id int(11) default NULL,
      filename varchar(255) NOT NULL,
      tempfile tinyint(1) NOT NULL default '0',
      show_inline tinyint(4) NOT NULL default '0',
      PRIMARY KEY  (id),
      KEY topic_id (topic_id)
    ) ENGINE=MyISAM;";

    // Set default access to use attachments to be the Root group
    $_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD use_attachment_grpid mediumint(8) DEFAULT '1' NOT NULL AFTER grp_id";

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        Log::write('system',Log::INFO,'Forum Plugin 3.0 update: Executing SQL => ' . current($_SQL));
        DB_query(current($_SQL),'1');
        if (DB_error()) {
            Log::write('system',Log::ERROR,'SQL Error during Forum plugin update: ' . $_SQL);
            return 1;
            break;
        }
        next($_SQL);
    }

    Log::write('system',Log::INFO,'Success - Completed Forum plugin version 3.0 update');
    return 0;
}

function _forum_cvt_watch() {
    global $_CONF, $_USER, $_TABLES, $LANG_GF02;

    $converted = 0;

    $complete = DB_getItem($_TABLES['vars'],'value','name="watchcvt"');
    if ( $complete == 1 ) {
        return $converted;
    }

    $fName = array();
    $tName = array();

    $dt = new Date('now',$_USER['tzid']);

    $processed = array();

    $sql = "SELECT * FROM {$_TABLES['ff_topic']} WHERE pid=0";
    $result = DB_query($sql);
    while ( ( $T = DB_fetchArray($result) ) != NULL ) {
        $pids[] = $T['id'];
    }

    $sql = "SELECT * FROM {$_TABLES['ff_watch']} ORDER BY topic_id ASC";
    $result = DB_query($sql);

    while ( ( $W = DB_fetchArray($result) ) != NULL ) {

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

function forum_update_config()
{
    global $_CONF, $_FF_CONF, $_TABLES;

    USES_lib_install();

    require_once $_CONF['path'].'plugins/forum/sql/forum_config_data.php';
    _update_config('forum', $forumConfigData);

}

?>
