<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | install_defaults.php                                                     |
// |                                                                          |
// | Initial Installation Defaults used when loading the online configuration |
// | records. These settings are only used during the initial installation    |
// | and not referenced any more once the plugin is installed.                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
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

if (strpos($_SERVER['PHP_SELF'], 'install_defaults.php') !== false) {
    die('This file can not be used on its own!');
}

/*
 * Forum default settings
 *
 * Initial Installation Defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 *
 */

global $_FF_DEFAULT;
$_FF_DEFAULT = array();
$_FF_DEFAULT['registration_required']  = false;
$_FF_DEFAULT['registered_to_post']     = true;
$_FF_DEFAULT['allow_html']             = false;
$_FF_DEFAULT['post_htmlmode']          = false;
$_FF_DEFAULT['use_glfilter']           = true;
$_FF_DEFAULT['use_geshi']              = true;
$_FF_DEFAULT['use_censor']             = true;
$_FF_DEFAULT['show_moods']             = true;
$_FF_DEFAULT['allow_smilies']          = true;
$_FF_DEFAULT['allow_notification']     = true;
$_FF_DEFAULT['allow_user_dateformat']  = true;
$_FF_DEFAULT['show_topicreview']       = true;
$_FF_DEFAULT['use_autorefresh']        = true;
$_FF_DEFAULT['autorefresh_delay']      = 5;
$_FF_DEFAULT['show_subject_length']    = 40;
$_FF_DEFAULT['show_topics_perpage']    = 10;
$_FF_DEFAULT['show_posts_perpage']     = 10;
$_FF_DEFAULT['show_messages_perpage']  = 20;
$_FF_DEFAULT['show_searches_perpage']  = 20;
$_FF_DEFAULT['views_tobe_popular']     = 20;
$_FF_DEFAULT['convert_break']          = false;
$_FF_DEFAULT['min_comment_length']     = 5;
$_FF_DEFAULT['min_username_length']    = 2;
$_FF_DEFAULT['min_subject_length']     = 2;
$_FF_DEFAULT['post_speedlimit']        = 60;
$_FF_DEFAULT['use_smilies_plugin']     = false;
$_FF_DEFAULT['use_pm_plugin']          = false;
$_FF_DEFAULT['use_spamx_filter']       = true;
$_FF_DEFAULT['show_centerblock']       = false;
$_FF_DEFAULT['centerblock_homepage']   = true;
$_FF_DEFAULT['centerblock_where']      = 2;
$_FF_DEFAULT['cb_subject_size']        = 40;
$_FF_DEFAULT['centerblock_numposts']   = 10;
$_FF_DEFAULT['sb_subject_size']        = 20;
$_FF_DEFAULT['sb_latestpostonly']      = false;
$_FF_DEFAULT['sideblock_numposts']     = 5;
$_FF_DEFAULT['allowed_editwindow']     = 0;
$_FF_DEFAULT['level1']                 = 1;
$_FF_DEFAULT['level2']                 = 15;
$_FF_DEFAULT['level3']                 = 35;
$_FF_DEFAULT['level4']                 = 70;
$_FF_DEFAULT['level5']                 = 120;
$_FF_DEFAULT['level1name']             = 'Newbie';
$_FF_DEFAULT['level2name']             = 'Junior';
$_FF_DEFAULT['level3name']             = 'Chatty';
$_FF_DEFAULT['level4name']             = 'Regular Member';
$_FF_DEFAULT['level5name']             = 'Active Member';

/**
* the Forum plugin's config array
*/
global $_FF_CONF;
$_FF_CONF = array();

if ( isset($CONF_FORUM['registration_required']) && $CONF_FORUM['level1name'] != '' ) {
    $_FF_CONF['registration_required']  = $CONF_FORUM['registration_required'];
    $_FF_CONF['registered_to_post']     = $CONF_FORUM['registered_to_post'];
    $_FF_CONF['allow_html']             = $CONF_FORUM['allow_html'];
    $_FF_CONF['post_htmlmode']          = $CONF_FORUM['post_htmlmode'];
    $_FF_CONF['use_glfilter']           = $CONF_FORUM['use_glfilter'];
    $_FF_CONF['use_geshi']              = $CONF_FORUM['use_geshi'];
    $_FF_CONF['use_censor']             = $CONF_FORUM['use_censor'];
    $_FF_CONF['show_moods']             = $CONF_FORUM['show_moods'];
    $_FF_CONF['allow_smilies']          = $CONF_FORUM['allow_smilies'];
    $_FF_CONF['allow_notification']     = $CONF_FORUM['allow_notification'];
    $_FF_CONF['allow_user_dateformat']  = $CONF_FORUM['allow_user_dateformat'];
    $_FF_CONF['show_topicreview']       = $CONF_FORUM['show_topicreview'];
    $_FF_CONF['use_autorefresh']        = $CONF_FORUM['use_autorefresh'];
    $_FF_CONF['autorefresh_delay']      = $CONF_FORUM['autorefresh_delay'];
    $_FF_CONF['show_subject_length']    = $CONF_FORUM['show_subject_length'];
    $_FF_CONF['show_topics_perpage']    = $CONF_FORUM['show_topics_perpage'];
    $_FF_CONF['show_posts_perpage']     = $CONF_FORUM['show_posts_perpage'];
    $_FF_CONF['show_messages_perpage']  = $CONF_FORUM['show_messages_perpage'];
    $_FF_CONF['show_searches_perpage']  = $CONF_FORUM['show_searches_perpage'];
    $_FF_CONF['views_tobe_popular']     = $CONF_FORUM['views_tobe_popular'];
    $_FF_CONF['convert_break']          = $CONF_FORUM['convert_break'];
    $_FF_CONF['min_comment_length']     = $CONF_FORUM['min_comment_length'];
    $_FF_CONF['min_username_length']    = $CONF_FORUM['min_username_length'];
    $_FF_CONF['min_subject_length']     = $CONF_FORUM['min_subject_length'];
    $_FF_CONF['post_speedlimit']        = $CONF_FORUM['post_speedlimit'];
    $_FF_CONF['use_smilies_plugin']     = $CONF_FORUM['use_smilies_plugin'];
    $_FF_CONF['use_pm_plugin']          = $CONF_FORUM['use_pm_plugin'];
    $_FF_CONF['use_spamx_filter']       = $CONF_FORUM['use_spamx_filter'];
    $_FF_CONF['show_centerblock']       = $CONF_FORUM['show_centerblock'];
    $_FF_CONF['centerblock_homepage']   = $CONF_FORUM['centerblock_homepage'];
    $_FF_CONF['centerblock_where']      = $CONF_FORUM['centerblock_where'];
    $_FF_CONF['cb_subject_size']        = $CONF_FORUM['cb_subject_size'];
    $_FF_CONF['centerblock_numposts']   = $CONF_FORUM['centerblock_numposts'];
    $_FF_CONF['sb_subject_size']        = $CONF_FORUM['sb_subject_size'];
    $_FF_CONF['sb_latestpostonly']      = $CONF_FORUM['sb_latestpostonly'];
    $_FF_CONF['sideblock_numposts']     = $CONF_FORUM['sideblock_numposts'];
    $_FF_CONF['allowed_editwindow']     = $CONF_FORUM['allowed_editwindow'];
    $_FF_CONF['level1']                 = $CONF_FORUM['level1'];
    $_FF_CONF['level2']                 = $CONF_FORUM['level2'];
    $_FF_CONF['level3']                 = $CONF_FORUM['level3'];
    $_FF_CONF['level4']                 = $CONF_FORUM['level4'];
    $_FF_CONF['level5']                 = $CONF_FORUM['level5'];
    $_FF_CONF['level1name']             = $CONF_FORUM['level1name'];
    $_FF_CONF['level2name']             = $CONF_FORUM['level2name'];
    $_FF_CONF['level3name']             = $CONF_FORUM['level3name'];
    $_FF_CONF['level4name']             = $CONF_FORUM['level4name'];
    $_FF_CONF['level5name']             = $CONF_FORUM['level5name'];
}

/**
* Initialize FileMgmt plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist. Initial values will be taken from $_FM_CONF if available (e.g. from
* an old config.php), uses $_FM_DEFAULT otherwise.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_forum()
{
    global $_FF_CONF, $_FF_DEFAULT;

    if (is_array($_FF_CONF) && (count($_FF_CONF) > 1)) {
        $_FF_DEFAULT = array_merge($_FF_DEFAULT, $_FF_CONF);
    }
    $c = config::get_instance();
    if (!$c->group_exists('forum')) {

        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, 'forum');
        $c->add('ff_public', NULL, 'fieldset', 0, 0, NULL, 0, true, 'forum');

        $c->add('registration_required', $_FF_DEFAULT['registration_required'], 'select',
                0, 0, 0, 10, true, 'forum');
        $c->add('registered_to_post', $_FF_DEFAULT['registered_to_post'], 'select',
                0, 0, 0, 20, true, 'forum');
        $c->add('allow_notification', $_FF_DEFAULT['allow_notification'], 'select',
                0, 0, 0, 30, true, 'forum');
        $c->add('show_topicreview', $_FF_DEFAULT['show_topicreview'], 'select',
                0, 0, 0, 40, true, 'forum');
        $c->add('allow_user_dateformat', $_FF_DEFAULT['allow_user_dateformat'], 'select',
                0, 0, 0, 50, true, 'forum');
        $c->add('use_pm_plugin', $_FF_DEFAULT['use_pm_plugin'], 'select',
                0, 0, 0, 60, true, 'forum');
        $c->add('use_autorefresh', $_FF_DEFAULT['use_autorefresh'], 'select',
                0, 0, 0, 70, true, 'forum');
        $c->add('autorefresh_delay', $_FF_DEFAULT['autorefresh_delay'], 'text',
                0, 0, 0, 80, true, 'forum');
        $c->add('show_topics_perpage', $_FF_DEFAULT['show_topics_perpage'], 'text',
                0, 0, 0, 90, true, 'forum');
        $c->add('show_posts_perpage', $_FF_DEFAULT['show_posts_perpage'], 'text',
                0, 0, 0, 100, true, 'forum');
        $c->add('show_messages_perpage', $_FF_DEFAULT['show_messages_perpage'], 'text',
                0, 0, 0, 100, true, 'forum');
        $c->add('show_searches_perpage', $_FF_DEFAULT['show_searches_perpage'], 'text',
                0, 0, 0, 110, true, 'forum');

        $c->add('ff_topic_post_settings', NULL, 'fieldset', 0, 1, NULL, 0, true,'forum');

        $c->add('show_subject_length', $_FF_DEFAULT['show_subject_length'], 'text',
                0, 1, 0, 10, true, 'forum');
        $c->add('min_username_length', $_FF_DEFAULT['min_username_length'], 'text',
                0, 1, 0, 20, true, 'forum');
        $c->add('min_subject_length', $_FF_DEFAULT['min_subject_length'], 'text',
                0, 1, 0, 30, true, 'forum');
        $c->add('min_comment_length', $_FF_DEFAULT['min_comment_length'], 'text',
                0, 1, 0, 40, true, 'forum');
        $c->add('views_tobe_popular', $_FF_DEFAULT['views_tobe_popular'], 'text',
                0, 1, 0, 50, true, 'forum');
        $c->add('post_speedlimit', $_FF_DEFAULT['post_speedlimit'], 'text',
                0, 1, 0, 60, true, 'forum');
        $c->add('allowed_editwindow', $_FF_DEFAULT['allowed_editwindow'], 'text',
                0, 1, 0, 70, true, 'forum');
        $c->add('allow_html', $_FF_DEFAULT['allow_html'], 'select',
                0, 1, 0, 80, true, 'forum');
        $c->add('post_htmlmode', $_FF_DEFAULT['post_htmlmode'], 'select',
                0, 1, 0, 90, true, 'forum');
        $c->add('use_censor', $_FF_DEFAULT['use_censor'], 'select',
                0, 1, 0, 100, true, 'forum');
        $c->add('use_glfilter', $_FF_DEFAULT['use_glfilter'], 'select',
                0, 1, 0, 110, true, 'forum');
        $c->add('use_geshi', $_FF_DEFAULT['use_geshi'], 'select',
                0, 1, 0, 120, true, 'forum');
        $c->add('use_spamx_filter', $_FF_DEFAULT['use_spamx_filter'], 'select',
                0, 1, 0, 130, true, 'forum');
        $c->add('show_moods', $_FF_DEFAULT['show_moods'], 'select',
                0, 1, 0, 140, true, 'forum');
        $c->add('allow_smilies', $_FF_DEFAULT['allow_smilies'], 'select',
                0, 1, 0, 150, true, 'forum');
        $c->add('use_smilies_plugin', $_FF_DEFAULT['use_smilies_plugin'], 'select',
                0, 1, 0, 160, true, 'forum');

        $c->add('ff_centerblock', NULL, 'fieldset', 0, 2, NULL, 0, true,
                'forum');

        $c->add('show_centerblock', $_FF_DEFAULT['show_centerblock'], 'select',
                0, 2, 0, 10, true, 'forum');
        $c->add('centerblock_homepage', $_FF_DEFAULT['centerblock_homepage'], 'select',
                0, 2, 0, 20, true, 'forum');
        $c->add('centerblock_numposts', $_FF_DEFAULT['centerblock_numposts'], 'text',
                0, 2, 0, 30, true, 'forum');
        $c->add('cb_subject_size', $_FF_DEFAULT['cb_subject_size'], 'text',
                0, 2, 0, 40, true, 'forum');
        $c->add('centerblock_where', $_FF_DEFAULT['centerblock_where'], 'select',
                0, 2, 2, 50, true, 'forum');

        $c->add('ff_latest_post_block', NULL, 'fieldset', 0, 3, NULL, 0, true,
                'forum');

        $c->add('sideblock_numposts', $_FF_DEFAULT['sideblock_numposts'], 'text',
                0, 3, 0, 10, true, 'forum');
        $c->add('sb_subject_size', $_FF_DEFAULT['sb_subject_size'], 'text',
                0, 3, 0, 20, true, 'forum');
        $c->add('sb_latestpostonly', $_FF_DEFAULT['sb_latestpostonly'], 'select',
                0, 3, 0, 20, true, 'forum');

        $c->add('ff_rank_settings', NULL, 'fieldset', 0, 4, NULL, 0, true,
                'forum');
        $c->add('level1', $_FF_DEFAULT['level1'], 'text',
                0, 4, 0, 10, true, 'forum');
        $c->add('level1name', $_FF_DEFAULT['level1name'], 'text',
                0, 4, 0, 20, true, 'forum');
        $c->add('level2', $_FF_DEFAULT['level2'], 'text',
                0, 4, 0, 30, true, 'forum');
        $c->add('level2name', $_FF_DEFAULT['level2name'], 'text',
                0, 4, 0, 40, true, 'forum');
        $c->add('level3', $_FF_DEFAULT['level3'], 'text',
                0, 4, 0, 50, true, 'forum');
        $c->add('level3name', $_FF_DEFAULT['level3name'], 'text',
                0, 4, 0, 60, true, 'forum');
        $c->add('level4', $_FF_DEFAULT['level4'], 'text',
                0, 4, 0, 70, true, 'forum');
        $c->add('level4name', $_FF_DEFAULT['level4name'], 'text',
                0, 4, 0, 80, true, 'forum');
        $c->add('level5', $_FF_DEFAULT['level5'], 'text',
                0, 4, 0, 90, true, 'forum');
        $c->add('level5name', $_FF_DEFAULT['level5name'], 'text',
                0, 4, 0, 100, true, 'forum');
    }

    return true;
}
?>