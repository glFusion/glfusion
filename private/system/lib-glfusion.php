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
// | Copyright (C) 2008-2009 by the following authors:                        |
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

$TEMPLATE_OPTIONS['hook']['set_root'] = 'glf_template_set_root';
$TEMPLATE_OPTIONS['default_vars']['digg_enabled'] = $_CONF['digg_enabled'];

function glf_template_set_root($root) {
    global $_CONF;

    $retval = array();

    if (!is_array($root)) {
        $root = array($root);
    }

    foreach ($root as $r) {
        if (substr($r, -1) == '/') {
            $r = substr($r, 0, -1);
        }
        if ( strpos($r,"plugins") != 0 ) {
            $p = str_replace($_CONF['path'],$_CONF['path_themes'] . $_CONF['theme'] . '/', $r);
            $x = str_replace("/templates", "",$p);
            $retval[] = $x;
        }
        if ( $r != '' ) {
            $retval[] = $r . '/custom';
            $retval[] = $r;
            $retval[] = $_CONF['path_themes'] . 'nouveau/' .
                substr($r, strlen($_CONF['path_layout']));
        }
    }
    return $retval;
}

function glfusion_SecurityCheck() {
    global $_CONF,$_SYSTEM,$LANG01;

    if (!SEC_inGroup ('Root')) {
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
    if ( $_SYSTEM['maintenance_mode'] ) {
        $msg .= $LANG01[503].'<br />';
    }
    if ( $msg != '' ) {
        $retval = '<p style="width:100%;text-align:center;"><span class="alert pluginAlert" style="text-align:center;font-size:1.5em;">' . $msg . '</span></p>';
    }
    return $retval;
}

function phpblock_blogroll ()
{
    global $_CONF, $_TABLES, $_ST_CONF;

    // configuration options:

    $cat = $_ST_CONF['blogroll_category']; // Category to take links from
    $directlink = false;    // Use direct links (true) or portal.php (false)
    $random = false;        // Random order (true) or sort by $sort (false)
    $sort = 'date';         // Sort by ... e.g. 'date', 'title', 'url'

    // === you shouldn't need to change anything below this line ==============
    $retval = '';

    if ( function_exists('LINKS_countLinksAndClicks') ) {

        $result = DB_query ("SELECT lid,url,title,description,hits FROM {$_TABLES['links']} WHERE cid = '".addslashes($cat)."'" . COM_getPermSql ('AND') . " ORDER BY $sort");
        $numLinks = DB_numRows ($result);

        $links = array ();
        for ($i = 0; $i < $numLinks; $i++) {
            $A = DB_fetchArray ($result);

            if ($directlink) {
                $url = $A['url'];
                $link = '<a href="' . $url . '">';

            } else {
                $url = $_CONF['site_url']
                     . COM_buildUrl ('/links/portal.php?what=link&amp;item=' . $A['lid']);
                $link = '<a href="' . $url . '" title="' . $A['url'] . '">';

            }
            $links[] = $link . COM_truncate($A['title'],25,'...') . '</a>'; // . ' (' . ($A['hits']) . ')' . '<br' . XHTML . '><em>' . ($A['description']) . '</em><br' . XHTML . '><br' . XHTML . '>';
        }

        if (count ($links) > 0) {

            if ($random) {
                $min = 0;
                $max = count ($links) - 1;

                $newlist = array ();
                do {
                    $r = rand ($min, $max);

                    if (!empty ($links[$r])) {
                        $newlist[] = $links[$r];
                        unset ($links[$r]);
                    }

                    if ($r == $min) {
                        $min = $r + 1;
                    } else if ($r == $max) {
                        $max = $r - 1;
                    }
                    if ($min == $max) {
                        if (!empty ($links[$min])) {
                            $newlist[] = $links[$min];
                        }
                        break;
                    }
                }
                while ($max > $min);

                $retval = COM_makeList ($newlist, 'list-blogroll');
            } else {
                $retval = COM_makeList ($links, 'list-blogroll');
            }
        }
    }

    return $retval;
}

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

    $LANG_STORYPICKER = Array('choose' => 'Choose a story');
    $max_stories = 5; //how many stories to display in the list

    $topicsql = '';
    $sid = '';
    if (isset($_GET['story'])) {
        $sid = COM_applyFilter($_GET['story']);
        $stopic = DB_getItem($_TABLES['stories'], 'tid', 'sid = \''.addslashes($sid).'\'');
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
        $topicsql = " AND tid = '".addslashes($topic)."'";
    }
    if (empty($topicsql)) {
        $topic = DB_getItem($_TABLES['topics'], 'tid', 'archive_flag = 1');
        if (empty($topic)) {
            $topicsql = '';
        } else {
            $topicsql = " AND tid <> '".addslashes($topic)."'";
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

function CTL_clearCacheDirectories($path, $needle = '')
{
    if ( $path[strlen($path)-1] != '/' ) {
        $path .= '/';
    }
    if ($dir = @opendir($path)) {
        while ($entry = readdir($dir)) {
            if ($entry == '.' || $entry == '..' || is_link($entry) || $entry == '.svn' || $entry == 'index.html') {
                continue;
            } elseif (is_dir($path . $entry)) {
                CTL_clearCacheDirectories($path . $entry, $needle);
                @rmdir($path . $entry);
            } elseif (empty($needle) || strpos($entry, $needle) !== false) {
                @unlink($path . $entry);
            }
        }
        @closedir($dir);
    }
}


function CTL_clearCache($plugin='')
{
    global $TEMPLATE_OPTIONS, $_CONF, $_SYSTEM;

    if (!empty($plugin)) {
        $plugin = '__' . $plugin . '__';
    }

    CTL_clearCacheDirectories($_CONF['path_data'] . 'layout_cache/', $plugin);

    if ( $_SYSTEM['use_direct_style_js'] ) {
        foreach (glob($_CONF['path_html'].$_CONF['css_cache_filename']."*.*") as $filename) {
            @unlink($filename);
        }
        foreach (glob($_CONF['path_html'].$_CONF['js_cache_filename']."*.*") as $filename) {
            @unlink($filename);
        }
    }

    css_out();
    js_out();
}

// +-------------------------------------------------------------------------+
// | Copyright (C) 2004 by Consult4Hire Inc.                                 |
// | From lib-portalparts.php                                                |
// | Author:                                                                 |
// | Blaine Lang                 -    blaine@portalparts.com                 |
// +-------------------------------------------------------------------------+

function glfGetUserBlocks(&$blocks) {
    global $_TABLES, $_CONF, $_USER, $LANG21, $topic, $page, $newstories;

    $retval = '';
    $sql = "SELECT name,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['blocks']} WHERE onleft = 1 AND is_enabled = 1";

    // Get user preferences on blocks
    if( !isset( $_USER['noboxes'] ) || !isset( $_USER['boxes'] )) {
        if( !empty( $_USER['uid'] )) {
            $result = DB_query( "SELECT boxes,noboxes FROM {$_TABLES['userindex']} WHERE uid = {$_USER['uid']}" );
            list($_USER['boxes'], $_USER['noboxes']) = DB_fetchArray( $result );
        } else {
            $_USER['boxes'] = '';
            $_USER['noboxes'] = 0;
        }
    }
    $sql .= " AND (tid = 'all' AND type <> 'layout')";
    if( !empty( $_USER['boxes'] )) {
        $BOXES = str_replace( ' ', ',', trim($_USER['boxes']) );
        $sql .= " AND (bid NOT IN ($BOXES) OR bid = '-1')";
    }

    $sql .= ' ORDER BY blockorder,title asc';
    $result = DB_query( $sql );
    $nrows = DB_numRows( $result );

    for( $i = 1; $i <= $nrows; $i++ ) {
        $A = DB_fetchArray( $result );
        if( SEC_hasAccess( $A['owner_id'], $A['group_id'], $A['perm_owner'], $A['perm_group'], $A['perm_members'], $A['perm_anon']) > 0 ) {
            $blocks[] = $A['name'];
        }
    }
    return $blocks;
}

function glfRandomFilename() {
    $length=10;
    srand((double)microtime()*1000000);
    $possible_charactors = "abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $string = "";
    while(strlen($string)<$length) {
        $string .= substr($possible_charactors, rand()%(strlen($possible_charactors)),1);
    }
    $string .= date('mdHms');   // Now add the numerical MonthDayHourSecond just to ensure no possible duplicate
    return($string);

}
function glfPrepareForDB($var) {
    // Need to call addslashes again as COM_checkHTML stips it out
    $var = COM_checkHTML($var);
    $var = addslashes($var);
    return $var;
}
function glfNavbar ($menuitems, $selected='', $parms='') {
    global $_CONF;

    $navbar = new Template($_CONF['path_layout'] . 'navbar');
    $navbar->set_file (array (
        'navbar'       => 'navbar.thtml',
        'menuitem'     => 'menuitem.thtml',
        ));
    for ($i=1; $i <= count($menuitems); $i++)  {
        $parms = explode( "=",current($menuitems) );
        $navbar->set_var( 'link',   current($menuitems));
        if (key($menuitems) == $selected) {
            $navbar->set_var( 'cssactive', 'id="active"');
            $navbar->set_var( 'csscurrent','id="current"');
        } else {
            $navbar->set_var( 'cssactive', '');
            $navbar->set_var( 'csscurrent','');
        }
        $navbar->set_var( 'label',  key($menuitems));
        $navbar->parse( 'menuitems', 'menuitem', true );
        next($menuitems);
    }
    $navbar->parse ('output', 'navbar');
    $retval = $navbar->finish($navbar->get_var('output'));
    return $retval;
}
?>