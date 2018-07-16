<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | stats.php                                                                |
// |                                                                          |
// | glFusion system statistics page.                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
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

require_once 'lib-common.php';
USES_lib_admin();

$display = '';

if ( !SEC_hasRights('stats.view') ) {
    COM_404();
}

// MAIN

$dt = new Date('now',$_USER['tzid']);

$display .= COM_siteHeader ('menu', $LANG10[1]);

// Overall Site Statistics

$header_arr = array(
    array('text' => $LANG10[1], 'field' => 'title', 'header_class' => 'stats-header-title'),
    array('text' => "", 'field' => 'stats', 'header_class' => 'stats-header-count', 'field_class' => 'stats-list-count'),
);
$data_arr = array();
$text_arr = array('has_menu'     =>  false,
                  'title'        => $LANG10[1],
);

$totalhits = DB_getItem ($_TABLES['vars'], 'value', "name = 'totalhits'");
$data_arr[] = array('title' => $LANG10[2], 'stats' => COM_NumberFormat ($totalhits));

if ($_CONF['lastlogin']) {
    // if we keep track of the last login date, count the number of users
    // that have logged in during the last 4 weeks
    $result = DB_query ("SELECT COUNT(*) AS count FROM {$_TABLES['users']} AS u,{$_TABLES['userinfo']} AS i WHERE (u.uid > 1) AND (u.uid = i.uid) AND (lastlogin <> '') AND (lastlogin >= UNIX_TIMESTAMP(DATE_SUB('".$_CONF['_now']->toMySQL(true)."', INTERVAL 28 DAY)))");
    list($active_users) = DB_fetchArray ($result);
} else {
    // otherwise, just count all users with status 'active'
    // (i.e. those that logged in at least once and have not been banned since)
    $active_users = DB_count ($_TABLES['users'], 'status', 3);
    $active_users--; // don't count the anonymous user account
}
$data_arr[] = array('title' => $LANG10[27], 'stats' => COM_NumberFormat ($active_users));

$topicsql = COM_getTopicSql ('AND');

$id = array ('draft_flag', 'date');
$values = array ('0', '"'.$_CONF['_now']->toMySQL(true).'"');
$result = DB_query ("SELECT COUNT(*) AS count,SUM(comments) AS ccount FROM {$_TABLES['stories']} WHERE (draft_flag = 0) AND (date <= '".$_CONF['_now']->toMySQL(true)."')" . COM_getPermSQL ('AND') . $topicsql);
$A = DB_fetchArray ($result);
if (empty ($A['ccount'])) {
    $A['ccount'] = 0;
}
$data_arr[] = array('title' => $LANG10[3],
                    'stats' => COM_NumberFormat ($A['count'])
                           . " (". COM_NumberFormat ($A['ccount']) . ")");

// new stats plugin API call
$plg_stats = PLG_getPluginStats (3);
if (count ($plg_stats) > 0) {
    foreach ($plg_stats as $pstats) {
        if (is_array ($pstats[0])) {
            foreach ($pstats as $pmstats) {
                $data_arr[] = array('title' => $pmstats[0], 'stats' => $pmstats[1]);
            }
        } else {
            $data_arr[] = array('title' => $pstats[0], 'stats' => $pstats[1]);
        }
    }
}

$display .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);

// Detailed story statistics

$result = DB_query("SELECT sid,title,hits FROM {$_TABLES['stories']} WHERE (draft_flag = 0) AND (date <= '".$_CONF['_now']->toMySQL(true)."') AND (Hits > 0)" . COM_getPermSQL ('AND') . $topicsql . " ORDER BY hits DESC LIMIT 10");
$nrows  = DB_numRows($result);

if ($nrows > 0) {
    $header_arr = array(
        array('text' => $LANG10[8], 'field' => 'sid', 'header_class' => 'stats-header-title'),
        array('text' => $LANG10[9], 'field' => 'hits', 'header_class' => 'stats-header-count', 'field_class' => 'stats-list-count'),
    );
    $data_arr = array();
    $text_arr = array('has_menu'     =>  false,
                      'title'        => $LANG10[7],
    );

    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray($result);
        $A['title'] = str_replace('$','&#36;',$A['title']);
        $A['sid'] = COM_createLink($A['title'],  COM_buildUrl ($_CONF['site_url']
                  . "/article.php?story={$A['sid']}"));
        $A['hits'] = COM_NumberFormat ($A['hits']);
        $data_arr[$i] = $A;

    }
    $display .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
} else {
    $display .= COM_startBlock($LANG10[7]);
    $display .= $LANG10[10];
    $display .= COM_endBlock();
}

// Top Ten Commented Stories

$result = DB_query("SELECT sid,title,comments FROM {$_TABLES['stories']} WHERE (draft_flag = 0) AND (date <= '".$_CONF['_now']->toMySQL(true)."') AND (comments > 0)" . COM_getPermSQL ('AND') . $topicsql . " ORDER BY comments DESC LIMIT 10");
$nrows  = DB_numRows($result);
if ($nrows > 0) {
    $header_arr = array(
        array('text' => $LANG10[8], 'field' => 'sid', 'header_class' => 'stats-header-title'),
        array('text' => $LANG10[12], 'field' => 'comments', 'header_class' => 'stats-header-count', 'field_class' => 'stats-list-count'),
    );
    $data_arr = array();
    $text_arr = array('has_menu'     =>  false,
                      'title'        => $LANG10[11],
    );
    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray($result);
        $A['title'] = str_replace('$','&#36;',$A['title']);
        $A['sid'] = COM_createLink($A['title'], COM_buildUrl ($_CONF['site_url']
                  . "/article.php?story={$A['sid']}"));
        $A['comments'] = COM_NumberFormat ($A['comments']);
        $data_arr[$i] = $A;
    }
    $display .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);

} else {
    $display .= COM_startBlock($LANG10[11],'',COM_getBlockTemplate('_admin_block', 'header'));
    $display .= $LANG10[13];
    $display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
}

// Top Ten Trackback Comments

if ($_CONF['trackback_enabled'] || $_CONF['pingback_enabled']) {
    $result = DB_query ("SELECT {$_TABLES['stories']}.sid,{$_TABLES['stories']}.title,COUNT(*) AS count FROM {$_TABLES['stories']},{$_TABLES['trackback']} AS t WHERE (draft_flag = 0) AND ({$_TABLES['stories']}.date <= '".$_CONF['_now']->toMySQL(true)."') AND ({$_TABLES['stories']}.sid = t.sid) AND (t.type = 'article')" . COM_getPermSql ('AND') . $topicsql . " GROUP BY t.sid,{$_TABLES['stories']}.sid,{$_TABLES['stories']}.title ORDER BY count DESC LIMIT 10");
    $nrows = DB_numRows ($result);
    if ($nrows > 0) {
        $header_arr = array(
            array('text' => $LANG10[8], 'field' => 'sid', 'header_class' => 'stats-header-title'),
            array('text' => $LANG10[12], 'field' => 'count', 'header_class' => 'stats-header-count', 'field_class' => 'stats-list-count'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     =>  false,
                          'title'        => $LANG10[25],
        );
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray ($result);
            $A['title'] = str_replace('$','&#36;',$A['title']);
            $A['sid'] = COM_createLink($A['title'], COM_buildUrl ($_CONF['site_url']
                      . "/article.php?story={$A['sid']}"));
            $A['count'] = COM_NumberFormat ($A['count']);
            $data_arr[$i] = $A;
        }
        $display .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);

    } else {
        $display .= COM_startBlock ($LANG10[25],'',COM_getBlockTemplate('_admin_block', 'header'));
        $display .= $LANG10[26];
        $display .= COM_endBlock (COM_getBlockTemplate('_admin_block', 'footer'));
    }
}

// Top Ten Emailed Stories

$result = DB_query("SELECT sid,title,numemails FROM {$_TABLES['stories']} WHERE (numemails > 0) AND (draft_flag = 0) AND (date <= '".$_CONF['_now']->toMySQL(true)."')" . COM_getPermSQL ('AND') . $topicsql . " ORDER BY numemails DESC LIMIT 10");
$nrows = DB_numRows($result);

if ($nrows > 0) {
    $header_arr = array(
        array('text' => $LANG10[8], 'field' => 'sid', 'header_class' => 'stats-header-title'),
        array('text' => $LANG10[23], 'field' => 'numemails', 'header_class' => 'stats-header-count', 'field_class' => 'stats-list-count'),
    );
    $data_arr = array();
    $text_arr = array('has_menu'     =>  false,
                      'title'        => $LANG10[22],
    );
    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray($result);
        $A['title'] = str_replace('$','&#36;',$A['title']);
        $A['sid'] = COM_createLink($A['title'], COM_buildUrl ($_CONF['site_url']
                  . "/article.php?story={$A['sid']}"));
        $A['numemails'] = COM_NumberFormat ($A['numemails']);
        $data_arr[$i] = $A;

    }
    $display .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
} else {
    $display .= COM_startBlock($LANG10[22],'',COM_getBlockTemplate('_admin_block', 'header'));
    $display .= $LANG10[24];
    $display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
}

// Last 10 Logins

if ( SEC_inGroup('Root') ) {
    $result = DB_query("SELECT u.uid AS uid, u.username AS 'username', ui.lastlogin AS 'login' FROM ".$_TABLES['userinfo']." AS ui LEFT JOIN ".$_TABLES['users']." AS u ON ui.uid=u.uid WHERE u.uid NOT IN (1) AND ui.lastlogin != 0 ORDER BY ui.lastlogin DESC LIMIT 10");
} else {
    $result = DB_query("SELECT u.uid AS uid, u.username AS 'username', ui.lastlogin AS 'login', up.showonline FROM ".$_TABLES['userinfo']." AS ui LEFT JOIN ".$_TABLES['users']." AS u ON ui.uid=u.uid LEFT JOIN {$_TABLES['userprefs']} AS up ON u.uid=up.uid WHERE u.uid NOT IN (1) AND ui.lastlogin != 0 AND up.showonline != 0 ORDER BY ui.lastlogin DESC LIMIT 10");
}
$nrows  = DB_numRows ($result);

if ($nrows > 0) {
    $header_arr = array(
        array('text' => $LANG10[4], 'field' => 'user', 'header_class' => 'stats-header-title-narrow'),
        array('text' => $LANG10[5], 'field' => 'date', 'header_class' => 'stats-header-count-wide','field_class' => 'stats-list-count'),
    );
    $data_arr = array();
    $text_arr = array('has_menu'     =>  false,
                      'title'        => $LANG10[6],
    );
    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray($result);
        $A['username'] = str_replace('$','&#36;',$A['username']);
        $A['user'] = "<a href=\"" . $_CONF['site_url']
                  . "/users.php?mode=profile&amp;uid={$A['uid']}" . "\">{$A['username']}</a>";
        if ( $A['login'] ) {
            $dt->setTimestamp($A['login']);
            $A['date'] = $dt->format($dt->getUserFormat(),true);

        } else {
            $A['date'] = $LANG28[36];
        }
        $data_arr[$i] = $A;
    }
    $display .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
} else {
    $display .= COM_startBlock($LANG10[6],'',COM_getBlockTemplate('_admin_block', 'header'));
    $display .= $LANG10[28];
    $display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
}

// Now show stats for any plugins that want to be included
$display .= PLG_getPluginStats(2);
$display .= COM_siteFooter();

echo $display;

?>