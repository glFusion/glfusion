<?php
/**
* glFusion CMS
*
* Database Initialization
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

require_once $_CONF['path'].'db-config.php';

global $_TABLES, $_DB_host, $_DB_name, $_DB_user, $_DB_pass, $_DB_table_prefix, $_DB_dbms;

$_TABLES['access']              = $_DB_table_prefix . 'access';
$_TABLES['article_images']      = $_DB_table_prefix . 'article_images';
$_TABLES['autotag_perm']        = $_DB_table_prefix . 'autotag_perm';
$_TABLES['autotag_usage']       = $_DB_table_prefix . 'autotag_usage';
$_TABLES['autotags']            = $_DB_table_prefix . 'autotags';
$_TABLES['blocks']              = $_DB_table_prefix . 'blocks';
$_TABLES['commentcodes']        = $_DB_table_prefix . 'commentcodes';
$_TABLES['commentedits']        = $_DB_table_prefix . 'commentedits';
$_TABLES['commentmodes']        = $_DB_table_prefix . 'commentmodes';
$_TABLES['comments']            = $_DB_table_prefix . 'comments';
$_TABLES['conf_values']         = $_DB_table_prefix . 'conf_values';
$_TABLES['cookiecodes']         = $_DB_table_prefix . 'cookiecodes';
$_TABLES['dateformats']         = $_DB_table_prefix . 'dateformats';
$_TABLES['featurecodes']        = $_DB_table_prefix . 'featurecodes';
$_TABLES['features']            = $_DB_table_prefix . 'features';
$_TABLES['frontpagecodes']      = $_DB_table_prefix . 'frontpagecodes';
$_TABLES['group_assignments']   = $_DB_table_prefix . 'group_assignments';
$_TABLES['groups']              = $_DB_table_prefix . 'groups';
$_TABLES['logo']                = $_DB_table_prefix . 'logo';
$_TABLES['maillist']            = $_DB_table_prefix . 'maillist';
$_TABLES['menu']                = $_DB_table_prefix . 'menu';
$_TABLES['menu_config']         = $_DB_table_prefix . 'menu_config';
$_TABLES['menu_elements']       = $_DB_table_prefix . 'menu_elements';
$_TABLES['pingservice']         = $_DB_table_prefix . 'pingservice';
$_TABLES['plugins']             = $_DB_table_prefix . 'plugins';
$_TABLES['postmodes']           = $_DB_table_prefix . 'postmodes';
$_TABLES['rating']              = $_DB_table_prefix . 'rating';
$_TABLES['rating_votes']        = $_DB_table_prefix . 'rating_votes';
$_TABLES['sessions']            = $_DB_table_prefix . 'sessions';
$_TABLES['social_share']        = $_DB_table_prefix . 'social_share';
$_TABLES['social_follow_services'] = $_DB_table_prefix . 'social_follow_services';
$_TABLES['social_follow_user']  = $_DB_table_prefix . 'social_follow_user';
$_TABLES['sortcodes']           = $_DB_table_prefix . 'sortcodes';
$_TABLES['speedlimit']          = $_DB_table_prefix . 'speedlimit';
$_TABLES['statuscodes']         = $_DB_table_prefix . 'statuscodes';
$_TABLES['stories']             = $_DB_table_prefix . 'stories';
$_TABLES['storysubmission']     = $_DB_table_prefix . 'storysubmission';
$_TABLES['subscriptions']       = $_DB_table_prefix . 'subscriptions';
$_TABLES['syndication']         = $_DB_table_prefix . 'syndication';
$_TABLES['tokens']              = $_DB_table_prefix . 'tokens';
$_TABLES['topics']              = $_DB_table_prefix . 'topics';
$_TABLES['trackback']           = $_DB_table_prefix . 'trackback';
$_TABLES['trackbackcodes']      = $_DB_table_prefix . 'trackbackcodes';
$_TABLES['usercomment']         = $_DB_table_prefix . 'usercomment';
$_TABLES['userindex']           = $_DB_table_prefix . 'userindex';
$_TABLES['userinfo']            = $_DB_table_prefix . 'userinfo';
$_TABLES['userprefs']           = $_DB_table_prefix . 'userprefs';
$_TABLES['users']               = $_DB_table_prefix . 'users';
$_TABLES['vars']                = $_DB_table_prefix . 'vars';
$_TABLES['tfa_backup_codes']    = $_DB_table_prefix . 'tfa_backup_codes';

// force the initialization of the DB driver which forces the initial connection
glFusion\Database::getInstance();

$_DB_dbms = 'mysql';

unset($_DB_host);
//unset($_DB_name);
unset($_DB_user);
unset($_DB_pass);

// load the compatiblity layer
require_once $_CONF['path_system'] . 'lib-database.php';

?>