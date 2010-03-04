<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | gettopic.php                                                             |
// |                                                                          |
// | Display available topics for merge.                                      |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2010 by the following authors:                             |
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

require_once '../lib-common.php';
require_once $_CONF['path'] . 'plugins/forum/include/gf_format.php';

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() ) {
    COM_404();
    exit;
}

function ADMIN_getListField_gettopic($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG04, $LANG28, $_IMAGE_TYPE;
    global $CONF_FORUM,$_SYSTEM,$LANG_GF02, $LANG_GF03;

    USES_lib_html2text();

    $retval = '';

    switch ($fieldname) {
        case 'author' :
            $retval = $A['name'];
            break;
        case 'date':
            $retval = @strftime( $CONF_FORUM['default_Datetime_format'], $fieldvalue );
            if ( $_SYSTEM['swedish_date_hack'] == true && function_exists('iconv') ) {
                $retval = iconv('ISO-8859-1','UTF-8',$retval);
            }
            break;
        case 'lastupdated':
            $retval = @strftime( $CONF_FORUM['default_Datetime_format'], $fieldvalue );
            if ( $_SYSTEM['swedish_date_hack'] == true && function_exists('iconv') ) {
                $retval = iconv('ISO-8859-1','UTF-8',$retval);
            }
            break;
        case 'subject':
            $testText        = gf_formatTextBlock($A['comment'],'text','text',$A['status']);
            $testText        = strip_tags($testText);
            $html2txt        = new html2text($testText,false);
            $testText        = trim($html2txt->get_text());
            $lastpostinfogll = htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$CONF_FORUM['contentinfo_numchars']). '...')),ENT_QUOTES,COM_getEncodingt());
            $retval = '<span class="gf_mootip" style="text-decoration:none;" title="' . $A['subject'] . '::' . $lastpostinfogll . '">' . $fieldvalue . '</span>';
            break;
        case 'select' :
            $retval = '[&nbsp;<a href="#" onclick="insert_topic(\''.$A['id'].'\'); return false;">'.$LANG_GF03['select'].'</a>&nbsp;]';
            break;
        default:
            $retval = $fieldvalue;
            break;
    }

    return $retval;
}

$forum_id = COM_applyFilter($_GET['fid'],true);

$T = new Template($_CONF['path'] . 'plugins/forum/templates/');
$T->set_file('confirm','gettopic.thtml');

USES_lib_admin();

$retval = '';

if ( !isset($_CONF['css_cache_filename']) ) {
    $_CONF['css_cache_filename'] = 'stylecache_';
}
if ( $_SYSTEM['use_direct_style_js'] ) {
    $cacheURL  = $_CONF['site_url'].'/'.$_CONF['css_cache_filename'].$_CONF['theme'].'.css?t='.$_CONF['theme'];
} else {
    $cacheURL  = $_CONF['site_url'].'/css.php?t='.$_CONF['theme'];
}
$cacheURL  = $_CONF['site_url'].'/css.php?t='.$_CONF['theme'];
$T->set_var('style_cache_url',$cacheURL);
if ( !isset($_CONF['js_cache_filename']) ) {
    $_CONF['js_cache_filename'] = 'jscache_';
}

if ( $_SYSTEM['use_direct_style_js'] ) {
    $cacheURL  = $_CONF['site_url'].'/'.$_CONF['js_cache_filename'].'.js?t='.$_CONF['theme'];
} else {
    $cacheURL  = $_CONF['site_url'].'/js.php?t='.$_CONF['theme'];
}
$T->set_var('js_cache_url',$cacheURL);
$T->set_var('theme',$_CONF['theme']);

$forumList = array();
$categoryResult = DB_query("SELECT * FROM {$_TABLES['gf_categories']} ORDER BY cat_order ASC");
while($A = DB_fetchArray($categoryResult)) {
    $cat_id = $A['cat_name'];

    if ( SEC_inGroup('Root') ) {
        $sql = "SELECT forum_id,forum_name,forum_dscp FROM {$_TABLES['gf_forums']} WHERE forum_cat ='{$A['id']}' ORDER BY forum_order ASC";
    } else {
        $sql = "SELECT * FROM {$_TABLES['gf_moderators']} a , {$_TABLES['gf_forums']} b ";
        $sql .= "WHERE b.forum_cat='{$A['id']}' AND a.mod_forum = b.forum_id AND (a.mod_uid='{$_USER['uid']}' OR a.mod_groupid in ($modgroups)) ORDER BY forum_order ASC";
    }
    $forumResult = DB_query($sql);

    while($B = DB_fetchArray($forumResult)) {
        $forumList[$cat_id][$B['forum_id']] = $B['forum_name'];
    }
}
$target = 0;
$forum_select = '<select name="fid" id="fid" style="line-height:1.5em;width:25em;" onchange="this.form.submit();">' . LB;
foreach ($forumList AS $category => $forums ) {
    if ( count ($forums) > 0 ) {
        $target = 1;
        $forum_select .= '<optgroup label="'.$category.'">' . LB;
        foreach ($forums AS $id => $name ) {
            $forum_select .= '<option value="'.$id.'"' . ($id == $forum_id ? ' selected="selected"' : '') . '>'.$name.'</option>'. LB;
        }
        $forum_select .= '</optgroup>' . LB;
    }
}
$forum_select .= '</select>';

$T->set_var('forum_select',$forum_select);

$header_arr = array(      # display 'text' and use table field 'field'
              array('text' => $LANG_GF03['select'],    'field' => 'select', 'sort' => false),
              array('text' => $LANG_GF01['TOPIC'],     'field' => 'subject', 'sort' => false),
              array('text' => $LANG_GF01['AUTHOR'],    'field' => 'author', 'sort' => false),
              array('text' => $LANG_GF01['DATE'],      'field' => 'date', 'sort' => false, 'nowrap' => true)
);

$text_arr = array(
    'has_extras' => true,
    'form_url'   => $_CONF['site_url'] . '/forum/gettopic.php?fid='.$forum_id.'&amp;query_limit=20',
    'help_url'   => '',
    'nowrap'     => 'date'
);

$defsort_arr = array('field'     => 'date',
                     'direction' => 'DESC');

$groups = array ();
$usergroups = SEC_getUserGroups();
foreach ($usergroups as $group) {
    $groups[] = $group;
}
$grouplist = implode(',',$groups);

$sql = "SELECT * FROM {$_TABLES['gf_topic']} WHERE pid=0 AND forum=".$forum_id;

$query_arr = array('table'          => 'topic',
                   'sql'            => $sql,
                   'query_fields'   => array('subject','comment'),
                   'default_filter' => '');

$retval .= ADMIN_list('topics', 'ADMIN_getListField_gettopic', $header_arr,
                      $text_arr, $query_arr, $defsort_arr);


$T->set_var('selection_page',$retval);
$T->parse ('output', 'confirm');
$retval = $T->finish($T->get_var('output'));

echo $retval;
exit();

?>