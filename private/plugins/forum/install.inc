<?php
// +---------------------------------------------------------------------------+
// | Forum Plugin                                                              |
// | glFusion auto install / uninstall routines                                |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                              |
// |                                                                           |
// | Author: Mark R. Evans - mark@gllabs.org                                   |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

// this file can't be used on its own
if (strpos ($_SERVER['PHP_SELF'], 'install.inc') !== false)
{
    die ('This file can not be used on its own.');
}

function plugin_install_forum($_DB_table_prefix )
{
    global $_TABLES, $_CONF;

    $pi_name = 'forum';                          // Plugin name
    $pi_version = '3.0';        // Plugin Version
    $gl_version = '1.5';                         // GL Version plugin for
    $pi_url = 'http://www.portalparts.com/';     // Plugin Homepage

    // Adding the Forum Plugin tables to $_TABLES array
    $_TABLES['gf_userprefs']    = $_DB_table_prefix . 'forum_userprefs';
    $_TABLES['gf_topic']        = $_DB_table_prefix . 'forum_topic';
    $_TABLES['gf_categories']   = $_DB_table_prefix . 'forum_categories';
    $_TABLES['gf_forums']       = $_DB_table_prefix . 'forum_forums';
    $_TABLES['gf_settings']     = $_DB_table_prefix . 'forum_settings';
    $_TABLES['gf_watch']        = $_DB_table_prefix . 'forum_watch';
    $_TABLES['gf_moderators']   = $_DB_table_prefix . 'forum_moderators';
    $_TABLES['gf_banned_ip']    = $_DB_table_prefix . 'forum_banned_ip';
    $_TABLES['gf_log']          = $_DB_table_prefix . 'forum_log';
    $_TABLES['gf_userinfo']     = $_DB_table_prefix . 'forum_userinfo';
    $_TABLES['gf_attachments']  = $_DB_table_prefix . 'forum_attachments';
    $_TABLES['gf_bookmarks']    = $_DB_table_prefix . 'forum_bookmarks';

    // Default data
    // Insert table name and sql to insert default data for your plugin.

    $DEFVALUES = array();

    // Example default data

    $DEFVALUES['gf_settings'] = "INSERT INTO {$_TABLES['gf_settings']}
        (`slogan`, `registrationrequired`, `registerpost`, `allowhtml`, `glfilter`, `use_geshi_formatting`, `censor`, `showmood`, `allowsmilies`, `allowavatar`, `allow_notify`, `post_htmlmode`, `allow_userdatefmt`, `showiframe`, `autorefresh`, `refresh_delay`, `xtrausersettings`, `viewtopicnumchars`, `topicsperpage`, `postsperpage`, `messagesperpage`, `searchesperpage`, `popular`, `speedlimit`, `edit_timewindow`, `use_spamxfilter`, `use_smiliesplugin`, `use_pmplugin`, `imgset`, `cb_enable`, `cb_homepage`, `cb_where`, `cb_subjectsize`, `cb_numposts`, `sb_subjectsize`, `sb_numposts`, `sb_latestposts`, `min_comment_len`, `min_name_len`, `min_subject_len`, `html_newline`, `level1`, `level2`, `level3`, `level4`, `level5`, `level1name`, `level2name`, `level3name`, `level4name`, `level5name`)
        VALUES ('',0,0,0,1,1,1,1,1,1,1,0,0,1,1,5,0,40,10,10,20,20,20,60,60,1,0,0,'',1,1,2,40,10,20,5,0,5,2,2,0,1,15,35,70,120,'Newbie','Junior','Chatty','Regular Member','Active Member');";

    $DEFVALUES['block1'] = "INSERT INTO {$_TABLES['blocks']} (is_enabled,name,type,title,tid,blockorder,onleft,phpblockfn,group_id,owner_id,perm_owner,perm_group,perm_members,perm_anon) "
         . " VALUES ('0','Forum News','phpblock','Forumposts','all',0,0,'phpblock_forum_newposts',2,2,3,3,2,2)";

    $DEFVALUES['block2'] = "INSERT INTO {$_TABLES['blocks']} (is_enabled,name,type,title,tid,blockorder,onleft,phpblockfn,group_id,owner_id,perm_owner,perm_group,perm_members,perm_anon) "
         . " VALUES (0, 'forum_menu', 'phpblock', 'Forum Menu', 'all', 0, 1, 'phpblock_forum_menu', 2,2,3,2,2,2)";

    $DEFVALUES['d1'] = "INSERT INTO {$_TABLES['gf_categories']} (`cat_order`, `cat_name`, `cat_dscp`, `id`) VALUES (0,'General','General News and Discussions',1);";
    $DEFVALUES['d2'] = "INSERT INTO {$_TABLES['gf_forums']} (`forum_order`, `forum_name`, `forum_dscp`, `forum_id`, `forum_cat`, `grp_id`, `use_attachment_grpid`, `is_hidden`, `is_readonly`, `no_newposts`, `topic_count`, `post_count`, `last_post_rec`) VALUES (0,'News and Announcements','Site News and Special Announcements',1,1,2,1,0,1,0,1,1,1);";
    $DEFVALUES['d3'] = "INSERT INTO {$_TABLES['gf_moderators']} (`mod_id`, `mod_uid`, `mod_groupid`, `mod_username`, `mod_forum`, `mod_delete`, `mod_ban`, `mod_edit`, `mod_move`, `mod_stick`) VALUES (1,2,0,'Admin','1',1,1,1,1,1);";
    $DEFVALUES['d4'] = "INSERT INTO {$_TABLES['gf_topic']} (`id`, `forum`, `pid`, `uid`, `name`, `date`, `lastupdated`, `last_reply_rec`, `email`, `website`, `subject`, `comment`, `postmode`, `replies`, `views`, `ip`, `mood`, `sticky`, `moved`, `locked`) VALUES (1,1,0,2,'Admin','1211775931','1211775931',0,NULL,'','Welcome to glFusion','Welcome to glFusion!  We hope you enjoy using your new glFusion site.\r\n\r\nglFusion is designed to provide you with features, functionality, and style, all in an easy to use package.\r\n\r\nYou can visit the [url=http://www.gllabs.org/wiki/]gl Fusion Wiki[/url] for the latest information on features and how to use them.\r\n\r\nThanks and enjoy!\r\nThe gl Labs Team\r\n','text',0,1,'127.0.0.1','',0,0,0);";

    $NEWFEATURE = array();
    $NEWFEATURE['forum.edit'] = 'Forum Admin';
    $NEWFEATURE['forum.user'] = 'Forum Viewer';

    $uninstall_plugin = 'plugin_uninstall_' . $pi_name;

    // Create the Plugins Tables
    require_once($_CONF['path'] . 'plugins/forum/sql/mysql_install_3.0.php');

    for ($i = 1; $i <= count($_SQL); $i++) {
        $progress .= "executing " . current($_SQL) . "<br>\n";
        DB_query(current($_SQL),'1');
        if (DB_error()) {
            $uninstall_plugin ('DeletePlugin');
            return false;
            exit;
        }
        next($_SQL);
    }

    // Insert Default Data

    foreach ($DEFVALUES as $table => $sql) {
        DB_query($sql,1);
        if (DB_error()) {
            $uninstall_plugin ();
            return false;
            exit;
        }
    }

    // Create the plugin admin security group
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr) "
        . "VALUES ('$pi_name Admin', 'Users in this group can administer the $pi_name plugin')",1);
    if (DB_error()) {
        $uninstall_plugin();
        return false;
        exit;
    }
    $query = DB_query("SELECT max(grp_id) FROM {$_TABLES['groups']} ");
    list ($group_id) = DB_fetchArray($query);

    // Save the grp id for later uninstall
    DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_admin', $group_id)",1);
    if (DB_error()) {
        $uninstall_plugin ();
        return false;
        exit;
    }

    // Add plugin Features
    foreach ($NEWFEATURE as $feature => $desc) {
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr) "
            . "VALUES ('$feature','$desc')",1);
        if (DB_error()) {
            $uninstall_plugin ();
            return false;
            exit;
        }
        $query = DB_query("SELECT max(ft_id) FROM {$_TABLES['features']} ");
        list ($feat_id) = DB_fetchArray($query);

        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)");
        if (DB_error()) {
            $uninstall_plugin ();
            return false;
            exit;
        }
    }

    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($group_id, NULL, 1)");
    if (DB_error()) {
        $uninstall_plugin ();
        return false;
        exit;
    }

    // Register the plugin with Geeklog
    DB_delete($_TABLES['plugins'],'pi_name',$pi_name);
    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
        . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error()) {
        $uninstall_plugin ();
        return false;
        exit;
    }

    return true;
}

/**
* Removes the datastructures for this plugin from the Geeklog database
* This may get called by the install routine to undue anything created during the install.
* Added check to see that plugin is first disabled.
*/
function plugin_uninstall_forum($installCheck='')
{
    global $_TABLES, $LANG_GF00;
exit;
    $pi_name='forum';
    $FEATURES = array ('forum.edit', 'forum.user');
    $TABLES = array ('gf_topic','gf_categories','gf_forums','gf_settings','gf_watch','gf_moderators','gf_banned_ip', 'gf_log', 'gf_userprefs','gf_userinfo'.'gf_attachments','gf_bookmarks');

    // Ok to proceed and delete plugin - Unregister the plugin with Geeklog
     DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = 'forum'",1);
    // Drop tables
    foreach($TABLES as $table) {
        $t = $_TABLES["$table"];
         DB_query("DROP TABLE $t",1);
    }

    // Remove Admin Security for this plugin
    $grp_id = DB_getItem($_TABLES['vars'], 'value', "name = '{$pi_name}_admin'");
    DB_query("DELETE FROM {$_TABLES['groups']} WHERE grp_id = $grp_id",1);
    DB_query("DELETE FROM {$_TABLES['vars']} WHERE name = '{$pi_name}_admin'");
    DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $grp_id",1);

    // Remove all the associated features - access rights. The feature ID's were stored in the vars table during install.
    foreach ($FEATURES as $feature) {
        $feat_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$feature'");
        DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $feat_id",1);
        DB_query("DELETE FROM {$_TABLES['features']} WHERE ft_id = $feat_id",1);
    }

    DB_query("DELETE FROM {$_TABLES['blocks']} WHERE phpblockfn = 'phpblock_forum_newposts'",1);
    DB_query("DELETE FROM {$_TABLES['blocks']} WHERE phpblockfn = 'phpblock_forum_newposts2'",1);
    DB_query("DELETE FROM {$_TABLES['blocks']} WHERE phpblockfn = 'phpblock_forum_menu'",1);

    return true;
}
?>