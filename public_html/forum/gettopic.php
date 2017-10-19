<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | gettopic.php                                                             |
// |                                                                          |
// | Display available topics for merge.                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2010-2015 by the following authors:                        |
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

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() ) {
    COM_404();
    exit;
}

USES_forum_functions();
USES_forum_format();

function _ff_getListField_gettopic($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG04, $LANG28, $_IMAGE_TYPE;
    global $_FF_CONF,$_SYSTEM,$LANG_GF02, $LANG_GF03;

    $dt = new Date('now',$_USER['tzid']);

    $retval = '';

    switch ($fieldname) {
        case 'author' :
            $retval = $A['name'];
            break;
        case 'date':
            $dt->setTimestamp($fieldvalue);
            $retval = $dt->format($_FF_CONF['default_Datetime_format'],true);
            break;
        case 'lastupdated':
            $dt->setTimestamp($fieldvalue);
            $retval = $dt->format($_FF_CONF['default_Datetime_format'],true);
            break;
        case 'subject':
            $testText        = FF_formatTextBlock($A['comment'],'text','text',$A['status']);
            $testText        = strip_tags($testText);
            $html2txt        = new Html2Text\Html2Text($testText,false);
            $testText        = trim($html2txt->get_text());
            $lastpostinfogll = htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$_FF_CONF['contentinfo_numchars']). '...')),ENT_QUOTES,COM_getEncodingt());
            $retval = '<span class="'.COM_getTooltipStyle().'" style="text-decoration:none;" title="' . $A['subject'] . '::' . $lastpostinfogll . '">' . $fieldvalue . '</span>';
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

$forum_id        = COM_applyFilter($_GET['fid'],true);
$topic_parent_id = COM_applyFilter($_GET['pid'],true);

$T = new Template($_CONF['path'] . 'plugins/forum/templates/');
$T->set_file('confirm','gettopic.thtml');

USES_lib_admin();

$retval = '';


list ($cachefile,$cacheURL) = COM_getStyleCacheLocation();
$T->set_var('style_cache_url',$cacheURL);

list ($js_cache_file,$js_cache_url) = COM_getJSCacheLocation();
$T->set_var('js_cache_url',$js_cache_url);
$T->set_var('theme',$_USER['theme']);

$forumList = array();
$categoryResult = DB_query("SELECT * FROM {$_TABLES['ff_categories']} ORDER BY cat_order ASC");
while($A = DB_fetchArray($categoryResult)) {
    $cat_id = $A['cat_name'];

    if ( SEC_inGroup('Root') ) {
        $sql = "SELECT forum_id,forum_name,forum_dscp FROM {$_TABLES['ff_forums']} WHERE forum_cat =".(int) $A['id']." ORDER BY forum_order ASC";
    } else {
        $sql = "SELECT * FROM {$_TABLES['ff_moderators']} a , {$_TABLES['ff_forums']} b ";
        $sql .= "WHERE b.forum_cat=".(int)$A['id']." AND a.mod_forum = b.forum_id AND (a.mod_uid=".(int) $_USER['uid']." OR a.mod_groupid in ($modgroups)) ORDER BY forum_order ASC";
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
    'form_url'   => $_CONF['site_url'] . '/forum/gettopic.php?fid='.$forum_id.'&amp;pid='.$topic_parent_id.'&amp;query_limit=20',
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

$sql = "SELECT * FROM {$_TABLES['ff_topic']} WHERE pid=0 AND id<> ".(int) $topic_parent_id." AND forum=".(int) $forum_id;

$query_arr = array('table'          => 'topic',
                   'sql'            => $sql,
                   'query_fields'   => array('subject','comment'),
                   'default_filter' => '');

$retval .= ADMIN_list('topics', '_ff_getListField_gettopic', $header_arr,
                      $text_arr, $query_arr, $defsort_arr);

$T->set_var('topic_parent_id',$topic_parent_id);
$T->set_var('selection_page',$retval);
$T->parse ('output', 'confirm');
$retval = $T->finish($T->get_var('output'));

echo $retval;
exit();

?>