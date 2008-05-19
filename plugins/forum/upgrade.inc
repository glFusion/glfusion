<?php


function upgrade_232() {
    global $_TABLES;

    $_SQL = array();

    /* Drop these extra fields in case they still exist */
    $dropfields = array('img_width','img_height','topicnumchars','newtopic','newreply','mouseover','mouseout');
    $fields = DB_query("SHOW COLUMNS FROM {$_TABLES['gf_settings']}");
    while ($A = DB_fetchArray($fields)) {
        if (in_array($A['Field'],$dropfields)) {
            $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} DROP {$A['Field']}";
        }
    }

    // Version 2.3 to 2.3.2 added one field - Add if this field does not exist
    $fields = DB_query("SHOW COLUMNS FROM {$_TABLES['gf_userprefs']}");
    while ($A = DB_fetchArray($fields)) {
        if (in_array($A['Field'],array('enablenotify'))) {         
            $fieldfound = true;
        }
    }
    if (!$fieldfound) {    
        $_SQL[] = "ALTER TABLE {$_TABLES['gf_userprefs']} ADD enablenotify tinyint(1) DEFAULT '1' NOT NULL AFTER viewanonposts";
    }
    
    /* Rename this field */
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} CHANGE  `msgauto` `autorefresh` TINYINT(1) DEFAULT '1' NOT NULL ";

    /* Add these new fields after `allow_notify` */
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD post_htmlmode tinyint(1) DEFAULT '1' NOT NULL AFTER allow_notify";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD allow_userdatefmt tinyint(1) DEFAULT '0' NOT NULL AFTER post_htmlmode";

    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD refresh_delay tinyint(1) DEFAULT '0' NOT NULL AFTER autorefresh";

    /* Add these new fields after `postsperpage` */
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD messagesperpage int(4) DEFAULT '20' NOT NULL AFTER postsperpage";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD searchesperpage int(4) DEFAULT '20' NOT NULL AFTER messagesperpage";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD popular int(4) DEFAULT '0' NOT NULL AFTER searchesperpage";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD speedlimit int(1) DEFAULT '60' NOT NULL AFTER popular";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD use_spamxfilter tinyint(1) DEFAULT '1' NOT NULL AFTER speedlimit";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD use_smiliesplugin tinyint(1) DEFAULT '0' NOT NULL AFTER use_spamxfilter";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD use_pmplugin tinyint(1) DEFAULT '0' NOT NULL AFTER use_smiliesplugin";


    /* Add these new fields after `cb_where` */
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD cb_subjectsize tinyint(1) DEFAULT '40' NOT NULL AFTER cb_where";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD cb_numposts tinyint(1) DEFAULT '5' NOT NULL AFTER cb_subjectsize";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD sb_subjectsize tinyint(1) DEFAULT '20' NOT NULL AFTER cb_numposts";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD sb_numposts tinyint(1) DEFAULT '5' NOT NULL AFTER sb_subjectsize";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD sb_latestposts tinyint(1) DEFAULT '5' NOT NULL AFTER sb_numposts";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD min_comment_len tinyint(1) DEFAULT '5' NOT NULL AFTER sb_latestposts";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD min_name_len tinyint(1) DEFAULT '2' NOT NULL AFTER min_comment_len";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD min_subject_len tinyint(1) DEFAULT '2' NOT NULL AFTER min_name_len";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD html_newline tinyint(1) DEFAULT '0' NOT NULL AFTER min_subject_len";

    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD use_geshi_formatting tinyint(1) DEFAULT '0' NOT NULL AFTER glfilter";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD edit_timewindow int(11) DEFAULT '300' NOT NULL AFTER speedlimit";

    /* Add new forum table fields */
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_forums']} ADD is_hidden tinyint(1) DEFAULT '0' NOT NULL AFTER grp_id";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_forums']} ADD is_readonly tinyint(1) DEFAULT '0' NOT NULL AFTER is_hidden";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_forums']} ADD no_newposts tinyint(1) DEFAULT '0' NOT NULL AFTER is_readonly";

    $_SQL[] = "ALTER TABLE {$_TABLES['gf_moderators']} ADD mod_uid mediumint(8) DEFAULT '0' NOT NULL AFTER mod_id";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_moderators']} ADD mod_groupid mediumint(8) DEFAULT '0' NOT NULL AFTER mod_uid";
    
    /* Add new userprefs field */
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_userprefs']} ADD notify_once tinyint(1) DEFAULT '0' NOT NULL AFTER showiframe"; 

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
    $query = DB_query("SELECT mod_id,mod_username FROM {$_TABLES['gf_moderators']}");
    while ($A = DB_fetchArray($query)) {
        $mod_uid = DB_getItem($_TABLES['users'],'uid',"username='{$A['mod_username']}'");
        if ($mod_uid > 0) {
            DB_query("UPDATE {$_TABLES['gf_moderators']} SET mod_uid = $mod_uid WHERE mod_id={$A['mod_id']}");
        }
    }
    COM_errorLog("Success - Completed Forum plugin version 2.5 update",1);
    return 0;
}


function upgrade_25() {
    global $_TABLES;

    $_SQL = array();

    /* Add new fields */
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_forums']} ADD topic_count mediumint(8) DEFAULT '0' NOT NULL AFTER no_newposts";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_forums']} ADD post_count mediumint(8) DEFAULT '0' NOT NULL AFTER topic_count";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_forums']} ADD last_post_rec mediumint(8) DEFAULT '0' NOT NULL AFTER post_count";
    $_SQL[] = "ALTER TABLE {$_TABLES['gf_topic']} ADD last_reply_rec mediumint(8) DEFAULT '0' NOT NULL AFTER lastupdated";

    // Check if the field 'allow_htmlsig' exists - from a ver2.5 install
    $query = DB_query("SELECT * FROM {$_TABLES['gf_settings']} ");
    $numfields =  DB_numFields($query);
    $A = DB_fetchArray($query,false);
    $fieldname = DB_fieldName($query,1);
    if (array_key_exists('allow_htmlsig',$A)) {
        /* Rename this field */
        $_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} CHANGE  `allow_htmlsig` `post_htmlmode` TINYINT(1) DEFAULT '1' NOT NULL ";
    }

    // Disable the convert new lines to BR tags - may not even be needed anymore but will add extra newlines if enabled.
    $_SQL[] = "UPDATE {$_TABLES['gf_settings']} set html_newline=0";
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


?>