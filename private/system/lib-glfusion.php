<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-glfusion.php                                                         |
// |                                                                          |
// | glFusion Enhancement Library                                             |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2011 by the following authors:                        |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

$TEMPLATE_OPTIONS['hook']['set_root'] = '_template_set_root';

function _template_set_root($root) {
    global $_CONF, $_USER;

    $retval = array();

    if (!is_array($root)) {
        $root = array($root);
    }

    foreach ($root as $r) {
        if (substr($r, -1) == '/') {
            $r = substr($r, 0, -1);
        }
        if ( strpos($r,"plugins") != 0 ) {
            $p = str_replace($_CONF['path'],$_CONF['path_themes'] . $_USER['theme'] . '/', $r);
            $x = str_replace("/templates", "",$p);
            $retval[] = $x;
        }
        if ( $r != '' ) {
            $retval[] = $r . '/custom';
            $retval[] = $r;
            if ( $_USER['theme'] != 'nouveau' ) {
                $retval[] = $_CONF['path_themes'] . 'nouveau/' .substr($r, strlen($_CONF['path_layout']));
            }
        }
    }
    return $retval;
}

function glfusion_UpgradeCheck() {
    global $_CONF,$_SYSTEM,$_TABLES,$LANG01;

    if (!SEC_inGroup ('Root')) {
        return;
    }

    $retval = '';
    $msg = '';

    $dbversion = DB_getItem($_TABLES['vars'],'value','name="glfusion"');
    $comparison = version_compare( GVERSION, $dbversion );
    $install_url = $_CONF['site_url'] . '/admin/install/index.php';

    switch ($comparison) {
        case 1:
            $msg .= sprintf($LANG01[504], $dbversion, GVERSION, $install_url ) . '<br />';
            break;
        case -1:
            $msg .= sprintf($LANG01[505], $dbversion, GVERSION ) . '<br />';
            break;
    }

    if ( $msg != '' ) {
        $retval = '<p style="width:100%;text-align:center;"><span class="alert pluginAlert" style="text-align:center;font-size:1.5em;">' . $msg . '</span></p>';
    }
    return $retval;
}

function glfusion_SecurityCheck() {
    global $_CONF,$_SYSTEM,$LANG01;

    if (!SEC_inGroup ('Root') OR (isset($_SYSTEM['development_mode']) AND $_SYSTEM['development_mode'])) {
        return;
    }

    $retval = '';
    $msg = '';
    if ( file_exists($_CONF['path_html'] . 'admin/install/') ) {
        $msg .= $LANG01[500].'<br />';
    }

    if ( $_SYSTEM['rootdebug'] ) {
        $msg .= $LANG01[501].'<br />';
    }
    if ( $_SYSTEM['no_fail_sql'] ) {
        $msg .= $LANG01[502].'<br />';
    }
    if ( $_CONF['maintenance_mode'] ) {
        $msg .= $LANG01[503].'<br />';
    }
    if ( $msg != '' ) {
        $retval = '<p style="width:100%;text-align:center;"><span class="alert pluginAlert" style="text-align:center;font-size:1.5em;">' . $msg . '</span></p>';
    }
    return $retval;
}
/*
function glfusion_SubmissionsCheck()
{
    $retval = '';
    return $retval;
}
*/
/*
 *  Story Picker Block - by Joe Mucchiello
 *  Make a list of n number of story headlines linked to their articles.
 *  Block does not appear if there are no stories in the current topic.
 *  If no topic is selected, all stories are listed.
 *
 *  Required Block Settings:
 *      Block Name = storypicker
 *      Topic = All
 *      Block Type = PHP Block
 *      Block Function = phpblock_storypicker
 *
 *  Issues:
 *      Does not handle stories that may have expired.
 */
function phpblock_storypicker() {
    global $_TABLES, $_CONF, $topic;

    $LANG_STORYPICKER = array('choose' => 'Choose a story');
    $max_stories = 5; //how many stories to display in the list

    $topicsql = '';
    $sid = '';
    if (isset($_GET['story'])) {
        $sid = COM_applyFilter($_GET['story']);
        $stopic = DB_getItem($_TABLES['stories'], 'tid', 'sid = \''.DB_escapeString($sid).'\'');
        if (!empty($stopic)) {
            $topic = $stopic;
        } else {
            $sid = '';
        }
    }

    if ( empty($topic) ) {
        if ( isset($_GET['topic']) ) {
            $topic = COM_applyFilter($_GET['topic']);
        } elseif (isset($_POST['topic']) ) {
            $topic = COM_applyFilter($_POST['topic']);
        } else {
            $topic = '';
        }
    }
    if (!empty($topic)) {
        $topicsql = " AND tid = '".DB_escapeString($topic)."'";
    }
    if (empty($topicsql)) {
        $topic = DB_getItem($_TABLES['topics'], 'tid', 'archive_flag = 1');
        if (empty($topic)) {
            $topicsql = '';
        } else {
            $topicsql = " AND tid <> '".DB_escapeString($topic)."'";
        }
    }
    $sql = 'SELECT sid, title FROM ' .$_TABLES['stories']
         . ' WHERE draft_flag = 0 AND date <= now()'
         . COM_getPermSQL(' AND')
         . COM_getTopicSQL(' AND')
         . $topicsql
         . ' ORDER BY date DESC LIMIT ' . $max_stories;

    $res = DB_query($sql);
    $list = '';
    while ($A = DB_fetchArray($res)) {
        $url = COM_buildUrl ($_CONF['site_url'] . '/article.php?story=' . $A['sid']);
        $list .= '<li><a href=' . $url .'>'
		//uncomment the 2 lines below to limit of characters displayed in the title
		. htmlspecialchars(COM_truncate($A['title'],41,'...')) . "</a></li>\n";
    }
    return $list;
}


?>
