<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | upgrade.php                                                              |
// |                                                                          |
// | Plugin upgrade                                                           |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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

require_once $_CONF['path'].'plugins/forum/forum.php';

/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function forum_upgrade() {
    global $_CONF, $_TABLES, $_FF_CONF, $_FF_CONF;

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
            DB_query("ALTER TABLE {$_TABLES['ff_userinfo']} ADD `rating` INT( 8 ) NOT NULL ");
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

        default :
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
        COM_errorLOG("Forum Plugin 2.5 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),'1');
        if (DB_error()) {
            COM_errorLog("SQL Error during Forum plugin update",1);
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
    COM_errorLog("Success - Completed Forum plugin version 2.5 update",1);
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
        COM_errorLOG("Forum Plugin 2.6 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),'1');
        if (DB_error()) {
            COM_errorLog("SQL Error during Forum plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    COM_errorLog("Success - Completed Forum plugin version 2.6 update",1);
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
      id` int(11) NOT NULL auto_increment,
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
        COM_errorLOG("Forum Plugin 3.0 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),'1');
        if (DB_error()) {
            COM_errorLog("SQL Error during Forum plugin update",1);
            return 1;
            break;
        }
        next($_SQL);
    }

    COM_errorLog("Success - Completed Forum plugin version 3.0 update",1);
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
?>