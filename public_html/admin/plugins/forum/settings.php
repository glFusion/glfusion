<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.6 for Geeklog - The Ultimate Weblog               |
// | Release date: Oct 30,2006                                                 |
// +---------------------------------------------------------------------------+
// | settings.php                                                              |
// | Forum Plugin admin settings                                               |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001,2002,2003 by the following authors:               |
// | Geeklog Author: Tony Bibbs       - tony@tonybibbs.com                     |
// +---------------------------------------------------------------------------+
// | Script Author                                                             |
// | Blaine Lang,                  blaine@portalparts.com, www.portalparts.com |
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

include_once('gf_functions.php');

function gf_RadioButtonSetting(&$template,$title,$help,$parm,$value,$id=1) {
    $template->set_var ('LANG_title', $title);
    $template->set_var ('LANG_description', $help);
    $template->set_var ('parm_name', $parm);
    $template->set_var ('option_yes', ($value)? 'CHECKED=CHECKED': '');
    $template->set_var ('option_no', ($value)? '' : 'CHECKED=CHECKED');
    $template->parse ("group{$id}_options", 'radioBtn_setting',true);
}

function gf_TextSetting(&$template,$title,$help,$parm2,$value2,$id=1) {
    $template->set_var ('LANG_title', $title);
    $template->set_var ('LANG_description', $help);
    $template->set_var ('parm_name', $parm2);
    $template->set_var ('parm_value', $value2);
    $template->parse ("group{$id}_options", 'text_setting',true);
}

function gf_RankSetting(&$template,$title,$help,$value1,$value2,$id) {
    $template->set_var ('LANG_level', $title);
    $template->set_var ('LANG_leveldscp', $help);
    $template->set_var ('parm1_name', "level{$id}");
    $template->set_var ('parm1_value', $value1);
    $template->set_var ('parm2_name', "level{$id}name");
    $template->set_var ('parm2_value', $value2);
    $template->parse ("ranking_options", 'rank_setting',true);
}

echo COM_siteHeader();
echo COM_startBlock($LANG_GF92['gfsettings']);
echo ppNavbar($navbarMenu,$LANG_GF06['2']);

if(isset($_POST['savesettings']) && $_POST['savesettings'] == 'yes'){

    $registrationrequired = COM_applyFilter($_POST['registrationrequired'],true);
    $registerpost = COM_applyFilter($_POST['registerpost'],true);
    $allowhtml = COM_applyFilter($_POST['allowhtml'],true);
    $glfilter = COM_applyFilter($_POST['glfilter'],true);
    $censor = COM_applyFilter($_POST['censor'],true);
    $showmood = COM_applyFilter($_POST['showmood'],true);
    $allowsmilies = COM_applyFilter($_POST['allowsmilies'],true);
    $allow_notify = COM_applyFilter($_POST['allow_notify'],true);
    $post_htmlmode = COM_applyFilter($_POST['post_htmlmode'],true);
    $allow_userdatefmt = COM_applyFilter($_POST['userdateformat'],true);
    $showiframe = COM_applyFilter($_POST['showiframe'],true);
    $autorefresh = COM_applyFilter($_POST['autorefresh'],true);
    $refresh_delay = COM_applyFilter($_POST['refreshdelay'],true);
    $viewtopicnumchars = COM_applyFilter($_POST['viewtopicnumchars'],true);
    $topicsperpage = COM_applyFilter($_POST['topicsperpage'],true);
    $postsperpage = COM_applyFilter($_POST['postsperpage'],true);
    $messagesperpage = COM_applyFilter($_POST['messagesperpage'],true);
    $searchesperpage = COM_applyFilter($_POST['searchesperpage'],true);
    $popular = COM_applyFilter($_POST['popular'],true);
    $speedlimit = COM_applyFilter($_POST['speedlimit'],true);
    $edit_timewindow = 60 * COM_applyFilter($_POST['edit_timewindow'],true);
    $use_spamxfilter = COM_applyFilter($_POST['use_spamxfilter'],true);
    $use_geshi_formatting = COM_applyFilter($_POST['use_geshi'],true);
    $use_pmplugin = COM_applyFilter($_POST['use_pmplugin'],true);
    $use_smiliesplugin = COM_applyFilter($_POST['use_smiliesplugin'],true);
    $min_comment_len = COM_applyFilter($_POST['mincomment_length'],true);
    $min_name_len = COM_applyFilter($_POST['minname_length'],true);
    $min_subject_len = COM_applyFilter($_POST['minsubject_length'],true);
    $html_newline = COM_applyFilter($_POST['convertbreak'],true);
    $level1 = COM_applyFilter($_POST['level1'],true);
    $level2 = COM_applyFilter($_POST['level2'],true);
    $level3 = COM_applyFilter($_POST['level3'],true);
    $level4 = COM_applyFilter($_POST['level4'],true);
    $level5 = COM_applyFilter($_POST['level5'],true);
    $level1name = @htmlspecialchars($_POST['level1name'],ENT_QUOTES,$CONF_FORUM['charset']);
    $level2name = @htmlspecialchars($_POST['level2name'],ENT_QUOTES,$CONF_FORUM['charset']);
    $level3name = @htmlspecialchars($_POST['level3name'],ENT_QUOTES,$CONF_FORUM['charset']);
    $level4name = @htmlspecialchars($_POST['level4name'],ENT_QUOTES,$CONF_FORUM['charset']);
    $level5name = @htmlspecialchars($_POST['level5name'],ENT_QUOTES,$CONF_FORUM['charset']);
    $cb_enable = COM_applyFilter($_POST['cb_enable'],true);
    $cb_homepage = COM_applyFilter($_POST['cb_homepage'],true);
    $cb_where = COM_applyFilter($_POST['cb_where'],true);
    $cb_subjectsize = COM_applyFilter($_POST['cb_subjectsize'],true);
    $cb_numposts = COM_applyFilter($_POST['cb_numposts'],true);
    $sb_subjectsize = COM_applyFilter($_POST['sb_subjectsize'],true);
    $sb_numposts = COM_applyFilter($_POST['sb_numposts'],true);
    $sb_latestposts = COM_applyFilter($_POST['sb_latestposts'],true);

    $CONF_FORUM['autorefresh_delay'] = $refreshdelay;  // Set this so that it can take immediate effect

    DB_query("UPDATE {$_TABLES['gf_settings']} SET
        registrationrequired='$registrationrequired',
        registerpost='$registerpost',
        allowhtml='$allowhtml',
        glfilter='$glfilter',
        censor='$censor',
        showmood='$showmood',
        allowsmilies='$allowsmilies',
        allow_notify='$allow_notify',
        post_htmlmode='$post_htmlmode',
        allow_userdatefmt='$allow_userdatefmt',
        showiframe='$showiframe',
        autorefresh='$autorefresh',
        refresh_delay='$refresh_delay',
        viewtopicnumchars='$viewtopicnumchars',
        topicsperpage='$topicsperpage',
        postsperpage='$postsperpage',
        messagesperpage='$messagesperpage',
        searchesperpage='$searchesperpage',
        popular='$popular',
        speedlimit='$speedlimit',
        edit_timewindow='$edit_timewindow',
        use_spamxfilter='$use_spamxfilter',
        use_geshi_formatting='$use_geshi_formatting',
        use_pmplugin='$use_pmplugin',
        use_smiliesplugin='$use_smiliesplugin',
        min_comment_len='$min_comment_len',
        min_name_len='$min_name_len',
        min_subject_len='$min_subject_len',
        html_newline='$html_newline',
        level1='$level1',
        level2='$level2',
        level3='$level3',
        level4='$level4',
        level5='$level5',
        level1name='$level1name',
        level2name='$level2name',
        level3name='$level3name',
        level4name='$level4name',
        level5name='$level5name',
        cb_enable='$cb_enable',
        cb_homepage='$cb_homepage',
        cb_where='$cb_where',
        cb_subjectsize='$cb_subjectsize',
        cb_numposts='$cb_numposts',
        sb_subjectsize='$sb_subjectsize',
        sb_numposts='$sb_numposts',
        sb_latestposts='$sb_latestposts'
    ");

  forum_statusMessage($LANG_GF92['setsave'],"{$_CONF['site_admin_url']}/plugins/forum/settings.php",$LANG_GF92['setsavemsg']);
  echo COM_endBlock();
  echo COM_siteFooter();
  exit();
  }

$result = DB_query("SELECT * FROM {$_TABLES['gf_settings']}");

/* Retrieve Settings that can be over-ridden by user preference to show global settings */
$A = DB_fetchArray($result);
$CONF_FORUM['show_topicreview']       = $A['showiframe'];
$CONF_FORUM['use_autorefresh']        = $A['autorefresh'];
$CONF_FORUM['views_tobe_popular']     = $A['popular'];              // * Added as of Version 2.4
$CONF_FORUM['show_subject_length']    = $A['viewtopicnumchars'];
$CONF_FORUM['show_topics_perpage']    = $A['topicsperpage'];
$CONF_FORUM['show_posts_perpage']     = $A['postsperpage'];
// $CONF_FORUM['statusmsg_pause']        = $A['statusmsg_pause'];      // Added as of Version 2.4


$settings = new Template($_CONF['path_layout'] . 'forum/layout/admin');
$settings->set_file (array ('settings'=>'settings.thtml',
                                    'radioBtn_setting' => 'radiosetting_option.thtml',
                                    'text_setting' => 'textsetting_option.thtml',
                                    'rank_setting' => 'ranksetting_option.thtml'));

$settings->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/settings.php');
$settings->set_var ('LANG_YES', $LANG_GF01['YES']);
$settings->set_var ('LANG_NO', $LANG_GF01['NO']);
$settings->set_var ('LANG_POSTS', $LANG_GF01['POSTS']);
$settings->set_var ('LANG_NAME', $LANG_GF01['NAME']);
$settings->set_var ('LANG_cbsettings', $LANG_GF92['cbsettings']);
$settings->set_var ('LANG_savesettings', $LANG_GF92['savesettings']);
$settings->set_var ('LANG_gensettings', $LANG_GF92['gensettings']);
$settings->set_var ('LANG_topicsettings', $LANG_GF92['topicsettings']);
$settings->set_var ('LANG_blocksettings', $LANG_GF92['blocksettings']);
$settings->set_var ('LANG_ranksettings', $LANG_GF92['ranksettings']);

/* General Settings */
gf_RadioButtonSetting($settings,
            $LANG_GF92['regview'],
            $LANG_GF92['regviewdscp'],
            'registrationrequired',
            $CONF_FORUM['registration_required']);
gf_RadioButtonSetting($settings,
            $LANG_GF92['regpost'],
            $LANG_GF92['regpostdscp'],
            'registerpost',
            $CONF_FORUM['registered_to_post']);
gf_RadioButtonSetting($settings,
            $LANG_GF92['allownotify'],
            $LANG_GF92['allownotifydscp'],
            'allow_notify',
            $CONF_FORUM['allow_notification']);
gf_RadioButtonSetting($settings,
            $LANG_GF92['showiframe'],
            $LANG_GF92['showiframedscp'],
            'showiframe',
            $CONF_FORUM['show_topicreview']);
gf_RadioButtonSetting($settings,
            $LANG_GF92['userdatefmt'],
            $LANG_GF92['userdatefmtdscp'],
            'userdateformat',
            $CONF_FORUM['allow_user_dateformat']);
gf_RadioButtonSetting($settings,
            $LANG_GF92['pmplugin'],
            $LANG_GF92['pmplugindscp'],
            'use_pmplugin',
            $CONF_FORUM['use_pm_plugin']);

/*
gf_RadioButtonSetting($settings,
            $LANG_GF92['allowhtmlsig'],
            $LANG_GF92['allowhtmlsigdscp'],
            'allow_htmlsig',
            $CONF_FORUM['allow_HTML_signatures']);
*/

gf_RadioButtonSetting($settings,
            $LANG_GF92['autorefresh'],
            $LANG_GF92['autorefreshdscp'],
            'autorefresh',
            $CONF_FORUM['use_autorefresh']);
gf_TextSetting($settings,
            $LANG_GF92['refreshdelay'],
            $LANG_GF92['refreshdelaydscp'],
            'refreshdelay',
            $CONF_FORUM['autorefresh_delay']);
gf_TextSetting($settings,
            $LANG_GF92['topicspp'],
            $LANG_GF92['topicsppdscp'],
            'topicsperpage',
            $CONF_FORUM['show_topics_perpage']);
gf_TextSetting($settings,
            $LANG_GF92['postspp'],
            $LANG_GF92['postsppdscp'],
            'postsperpage',
            $CONF_FORUM['show_posts_perpage']);
gf_TextSetting($settings,
            $LANG_GF92['messagespp'],
            $LANG_GF92['messagesppdscp'],
            'messagesperpage',
            $CONF_FORUM['show_messages_perpage']);
gf_TextSetting($settings,
            $LANG_GF92['searchespp'],
            $LANG_GF92['searchesppdscp'],
            'searchesperpage',
            $CONF_FORUM['show_searches_perpage']);

/* Topic Posting Settings */
gf_TextSetting($settings,
            $LANG_GF92['titleleng'],
            $LANG_GF92['titlelengdscp'],
            'viewtopicnumchars',
            $CONF_FORUM['show_subject_length'],2);
gf_TextSetting($settings,
            $LANG_GF92['minnamelength'],
            $LANG_GF92['minnamedscp'],
            'minname_length',
            $CONF_FORUM['min_username_length'],2);
gf_TextSetting($settings,
            $LANG_GF92['minsubjectlength'],
            $LANG_GF92['minsubjectdscp'],
            'minsubject_length',
            $CONF_FORUM['min_subject_length'],2);
gf_TextSetting($settings,
            $LANG_GF92['mincommentlength'],
            $LANG_GF92['mincommentdscp'],
            'mincomment_length',
            $CONF_FORUM['min_comment_length'],2);
gf_TextSetting($settings,
            $LANG_GF92['popular'],
            $LANG_GF92['populardscp'],
            'popular',
            $CONF_FORUM['views_tobe_popular'],2);
gf_TextSetting($settings,
            $LANG_GF92['speedlimit'],
            $LANG_GF92['speedlimitdscp'],
            'speedlimit',
            $CONF_FORUM['post_speedlimit'],2);
gf_TextSetting($settings,
            $LANG_GF92['edit_timewindow'],
            $LANG_GF92['edit_timewindowdscp'],
            'edit_timewindow',
            ($CONF_FORUM['allowed_editwindow']/60),2);
gf_RadioButtonSetting($settings,
            $LANG_GF92['allowhtml'],
            $LANG_GF92['allowhtmldscp'],
            'allowhtml',
            $CONF_FORUM['allow_html'],2);
gf_RadioButtonSetting($settings,
            $LANG_GF92['defaultmode'],
            $LANG_GF92['defaultmodedscp'],
            'post_htmlmode',
            $CONF_FORUM['post_htmlmode'],2);
gf_RadioButtonSetting($settings,
            $LANG_GF92['convertbreak'],
            $LANG_GF92['convertbreakdscp'],
            'convertbreak',
            $CONF_FORUM['convert_break'],2);
gf_RadioButtonSetting($settings,
            $LANG_GF92['censor'],
            $LANG_GF92['censordscp'],
            'censor',
            $CONF_FORUM['use_censor'],2);
gf_RadioButtonSetting($settings,
            $LANG_GF92['glfilter'],
            $LANG_GF92['glfilterdscp'],
            'glfilter',
            $CONF_FORUM['use_glfilter'],2);
gf_RadioButtonSetting($settings,
            $LANG_GF92['geshiformat'],
            $LANG_GF92['geshiformatdscp'],
            'use_geshi',
            $CONF_FORUM['use_geshi'],2);
gf_RadioButtonSetting($settings,
            $LANG_GF92['spamxplugin'],
            $LANG_GF92['spamxplugindscp'],
            'use_spamxfilter',
            $CONF_FORUM['use_spamx_filter'],2);
gf_RadioButtonSetting($settings,
            $LANG_GF92['showmoods'],
            $LANG_GF92['showmoodsdscp'],
            'showmood',
            $CONF_FORUM['show_moods'],2);
gf_RadioButtonSetting($settings,
            $LANG_GF92['allowsmilies'],
            $LANG_GF92['allowsmiliesdscp'],
            'allowsmilies',
            $CONF_FORUM['allow_smilies'],2);
gf_RadioButtonSetting($settings,
            $LANG_GF92['smiliesplugin'],
            $LANG_GF92['smiliesplugindscp'],
            'use_smiliesplugin',
            $CONF_FORUM['use_smilies_plugin'],2);

/* Centerblock Settings */
gf_RadioButtonSetting($settings,
            $LANG_GF92['cbenable'],
            $LANG_GF92['cbenabledscp'],
            'cb_enable',
            $CONF_FORUM['show_centerblock'],3);
gf_RadioButtonSetting($settings,
            $LANG_GF92['cbhomepage'],
            $LANG_GF92['cbhomepage'],
            'cb_homepage',
            $CONF_FORUM['centerblock_homepage'],3);
gf_TextSetting($settings,
            $LANG_GF92['cb_numposts'],
            $LANG_GF92['cb_numpostsdscp'],
            'cb_numposts',
            $CONF_FORUM['centerblock_numposts'],3);
gf_TextSetting($settings,
            $LANG_GF92['cb_subjectsize'],
            $LANG_GF92['cb_subjectsizedscp'],
            'cb_subjectsize',
            $CONF_FORUM['cb_subject_size'],3);

$settings->set_var ('LANG_cbposition', $LANG_GF92['cbposition']);
$settings->set_var ('LANG_cbpositiondscp', $LANG_GF92['cbpositiondscp']);
$position = '<select name="cb_where">';
$position .= '<option value="1"';
if ($A['cb_where'] == 1) {
    $position .= ' selected="selected"';
}
$position .= '>' . $LANG_GF92['position_top'] . '</option>';
$position .= '<option value="2"';
if ($A['cb_where'] == 2) {
    $position .= ' selected="selected"';
}
$position .= '>' . $LANG_GF92['position_feat'] . '</option>';
$position .= '<option value="3"';
if ($A['cb_where'] == 3) {
    $position .= ' selected="selected"';
}
$position .= '>' . $LANG_GF92['position_bottom'] . '</option>';
$position .= '</select>';
$settings->set_var ('pos_selection', $position);


/* Latest Posts Block Settings */
gf_TextSetting($settings,
            $LANG_GF92['sb_numposts'],
            $LANG_GF92['sb_numpostsdscp'],
            'sb_numposts',
            $CONF_FORUM['sideblock_numposts'],4);
gf_TextSetting($settings,
            $LANG_GF92['sb_subjectsize'],
            $LANG_GF92['sb_subjectsizedscp'],
            'sb_subjectsize',
            $CONF_FORUM['sb_subject_size'],4);
gf_RadioButtonSetting($settings,
            $LANG_GF92['sb_latestposts'],
            $LANG_GF92['sb_latestpostsdscp'],
            'sb_latestposts',
            $CONF_FORUM['sb_latestpostonly'],4);

/* Ranking Settings */
gf_RankSetting($settings,
            $LANG_GF92['lev1'],
            $LANG_GF92['lev1dscp'],
            $CONF_FORUM['level1'],
            $CONF_FORUM['level1name'],1);
gf_RankSetting($settings,
            $LANG_GF92['lev2'],
            $LANG_GF92['lev2dscp'],
            $CONF_FORUM['level2'],
            $CONF_FORUM['level2name'],2);
gf_RankSetting($settings,
            $LANG_GF92['lev3'],
            $LANG_GF92['lev3dscp'],
            $CONF_FORUM['level3'],
            $CONF_FORUM['level3name'],3);
gf_RankSetting($settings,
            $LANG_GF92['lev4'],
            $LANG_GF92['lev4dscp'],
            $CONF_FORUM['level4'],
            $CONF_FORUM['level4name'],4);
gf_RankSetting($settings,
            $LANG_GF92['lev5'],
            $LANG_GF92['lev5dscp'],
            $CONF_FORUM['level5'],
            $CONF_FORUM['level5name'],5);

$settings->parse ('output', 'settings');
echo $settings->finish ($settings->get_var('output'));

echo COM_endBlock();
echo COM_siteFooter();

?>