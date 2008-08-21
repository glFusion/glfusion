<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | gf_format.php                                                            |
// |                                                                          |
// | General formatting routines                                              |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Forum Plugin for Geeklog CMS                                |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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
if (strpos ($_SERVER['PHP_SELF'], 'gf_format.php') !== false)
{
    die ('This file can not be used on its own.');
}

if (!class_exists('StringParser') ) {
    require_once ($_CONF['path'] . 'lib/bbcode/stringparser_bbcode.class.php');
}

function gf_siteHeader($subject = '') {
    global $CONF_FORUM;

    // Display Common headers
    if (!isset($CONF_FORUM['showblocks'])) $CONF_FORUM['showblocks'] = 'leftblocks';
    if (!isset($CONF_FORUM['usermenu'])) $CONF_FORUM['usermenu'] = 'blockmenu';

    if ($CONF_FORUM['showblocks'] == 'noblocks' OR $CONF_FORUM['showblocks'] == 'rightblocks') {
        echo COM_siteHeader('none', $subject);
    } elseif ($CONF_FORUM['showblocks'] == 'leftblocks' OR $CONF_FORUM['showblocks'] == 'allblocks' ) {
        if ($CONF_FORUM['usermenu'] == 'blockmenu') {
            echo COM_siteHeader( array('custom_showBlocks',$CONF_FORUM['leftblocks']), $subject );
        } else {
            echo COM_siteHeader('menu', $subject);
        }
    } else {
        echo COM_siteHeader('menu', $subject);
    }
}

function gf_siteFooter() {
    global $CONF_FORUM;

    if ($CONF_FORUM['showblocks'] == 'noblocks' OR $CONF_FORUM['showblocks'] == 'leftblocks') {
        echo COM_siteFooter(false);
    } elseif ($CONF_FORUM['showblocks'] == 'rightblocks') {
        if ($CONF_FORUM['usermenu'] == 'blockmenu') {
            echo COM_siteFooter(true, array('custom_showBlocks',$CONF_FORUM['leftblocks']) );
        } else {
            echo COM_siteFooter(true);
        }
    } elseif ($CONF_FORUM['showblocks'] == 'allblocks') {
        echo COM_siteFooter(true);
    } else {
        echo COM_siteFooter();
    }
}

function convertlinebreaks ($text) {
    return preg_replace ("/\015\012|\015|\012/", "\n", $text);
}

function bbcode_stripcontents ($text) {
    return preg_replace ("/[^\n]/", '', $text);
}

function bbcode_htmlspecialchars($text) {
    global $CONF_FORUM;

    return (@htmlspecialchars ($text,ENT_QUOTES, $CONF_FORUM['charset']));
}

function gf_fixtemplate($text) {
    $text = str_replace('{','&#123;',$text);
    $text = str_replace('}','&#125;',$text);

    return $text;
}

function do_bbcode_url ($action, $attributes, $content, $params, $node_object) {
    global $CONF_FORUM;

    if ($action == 'validate') {
        return true;
    }
    if (!isset ($attributes['default'])) {
        if ( stristr($content,'http') ) {
            return '<a href="'.$content.'">'.@htmlspecialchars ($content,ENT_QUOTES, $CONF_FORUM['charset']).'</a>';
        } else {
            return '<a href="http://'.$content.'">'.@htmlspecialchars ($content,ENT_QUOTES, $CONF_FORUM['charset']).'</a>';
        }
    }
    if ( stristr($attributes['default'],'http') ) {
        return '<a href="'.$attributes['default'].'">'.$content.'</a>';
    } else {
        return '<a href="http://'.$attributes['default'].'">'.$content.'</a>';
    }
}

function do_bbcode_list ($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        return true;
    }
    if (!isset ($attributes['default'])) {
        return '</p><ul>'.$content.'</ul><p>';
    } else {
        if ( is_numeric($attributes['default']) ) {
            return '<ol>'.$content.'</ol>';
        } else {
            return '</p><ul>'.$content.'</ul><p>';
        }
    }
    return '</p><ul>'.$content.'</ul><p>';
}

function do_bbcode_file ($action, $attributes, $content, $params, $node_object) {
    global $_CONF,$_TABLES,$CONF_FORUM,$topicRec,$forumfiles;
    global $previewitem,$filemgmt_FileStoreURL,$LANG_GF10;

    $retval = '';
    if ( $action == 'validate') {
        return true;
    }
    $sql = "SELECT id,filename,repository_id,show_inline,topic_id FROM {$_TABLES['gf_attachments']} ";
    if (isset($_POST['uniqueid']) AND $_POST['uniqueid'] > 0) {  // User is previewing a new post
        $sql .= "WHERE topic_id = {$_POST['uniqueid']} AND tempfile=1 ";
    } else if(isset($previewitem['id'])) {
         $sql .= "WHERE topic_id = {$previewitem['id']} ";
    } else if(isset($topicRec['id'])){
        $sql .= "WHERE topic_id = {$topicRec['id']} ";
    } else {
        return '';
    }
    $sql .= "ORDER BY id";
    $query = DB_query($sql);
    $i = 1;

    if ( isset($attributes['align'] ) ) {
        $align = ' align=' . $attributes['align'] . ' ';
    } else {
        $align = '';
    }

    if ( isset($attributes['lightbox'] ) ) {
        $lb = ' rel="lightbox" ';
    } else {
        $lb = '';
    }

    while (list($id,$fileinfo,$repository_id,$showinline,$topicid) = DB_fetchArray($query)) {
        if ($i == $content) {
            if ($showinline == 0) {
                DB_query("UPDATE {$_TABLES['gf_attachments']} SET show_inline = 1 WHERE id=$id");
            }
            $forumfiles[$i] = $id;   // uses to track attachments used inline and reset others in case user is changing them
            $fileparts = explode(':',$fileinfo);
            $pos = strrpos($fileparts[0],'.');
            $filename = substr($fileparts[0], 0,$pos);
            $ext = substr($fileparts[0], $pos+1);
            if ($repository_id > 0) {
                $srcImage = "{$filemgmt_FileStoreURL}/{$filename}.{$ext}";
            } else {
                $srcImage = "{$CONF_FORUM['downloadURL']}/{$filename}.{$ext}";
            }

            if (file_exists("{$CONF_FORUM['uploadpath']}/tn/{$filename}.{$ext}")) {
                $srcThumbnail = "{$CONF_FORUM['downloadURL']}/tn/{$filename}.{$ext}";
            } else {
                if (file_exists("{$_CONF['path_html']}/forum/images/icons/{$ext}.gif")) {
                    $srcThumbnail = "{$_CONF['site_url']}/forum/images/icons/{$ext}.gif";
                } else {
                    $srcThumbnail = "{$_CONF['site_url']}/forum/images/icons/none.gif";
                }
            }
            $retval = '<a href="'.$srcImage.'" '.$lb.' target="_new"><img src="'. $srcThumbnail . '" '.$align.' style="padding:5px;" title="'.$LANG_GF10['click2download'].'"></a>';
            break;
         }
        $i++;
    }

    return $retval;

}

function do_bbcode_img ($action, $attributes, $content, $params, $node_object) {
    global $CONF_FORUM;

    if ($action == 'validate') {
        if (isset($attributes['caption'])) {
            $node_object->setFlag('paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
            if ($node_object->_parent->type() == STRINGPARSER_NODE_ROOT OR
                in_array($node_object->_parent->_codeInfo['content_type'], array('block', 'list', 'listitem'))) {
                return true;
            }
            else return false;
        }
        else return true;
    }

    if ($CONF_FORUM['allow_img_bbcode']) {
        if ( isset($attributes['h']) AND isset ($attributes['w']) ) {
            $dim = 'width=' . $attributes['w'] . ' height=' . $attributes['h'];
        } else {
            $dim = '';
        }
        if ( isset($attributes['align'] ) ) {
            $align = ' align=' . $attributes['align'] . ' ';
        } else {
            $align = '';
        }

        return '<img src="'.htmlspecialchars($content,ENT_QUOTES, $CONF_FORUM['charset']).'" ' . $dim . $align . ' alt=""' . XHTML . '>';
    } else {
        return '[img]' . $content . '[/img]';
    }
}

function do_bbcode_size  ($action, $attributes, $content, $params, $node_object) {
    if ( $action == 'validate') {
        return true;
    }
    return '<span style="font-size: '.$attributes['default'].'px;">'.$content.'</span>';
}

function do_bbcode_color  ($action, $attributes, $content, $params, $node_object) {
    if ( $action == 'validate') {
        return true;
    }
    return '<span style="color: '.$attributes['default'].';">'.$content.'</span>';
}

function do_bbcode_code($action, $attributes, $content, $params, $node_object) {
    global $CONF_FORUM, $oldPost;

    if ( $action == 'validate') {
        return true;
    }

    if ( $oldPost ) {
        $content = str_replace("&#36;","$", $content);
        $content = html_entity_decode($content);
    }

    if($CONF_FORUM['allow_smilies']) {
        if (function_exists('msg_restoreEmoticons') AND $CONF_FORUM['use_smilies_plugin']) {
            $content = msg_restoreEmoticons($content);
        } else {
            $content = forum_xchsmilies($content,true);
        }
    }
    if ($CONF_FORUM['use_geshi']) {
        /* Support for formatting various code types : [code=java] for example */
        if (!isset ($attributes['default'])) {
            $codeblock = '</p>' . geshi_formatted($content) . '<p>';
        } else {
            $codeblock = '</p>' . geshi_formatted($content,strtoupper($attributes['default'])) . '<p>';
        }
    } else {
        $codeblock = '<pre class="codeblock">'  . @htmlspecialchars($content,ENT_QUOTES, $CONF_FORUM['charset']) . '</pre>';
    }

    $codeblock = str_replace('{','&#123;',$codeblock);
    $codeblock = str_replace('}','&#125;',$codeblock);

    return $codeblock;
}

function forumNavbarMenu($current='') {
    global $_CONF,$_USER,$LANG_GF01,$LANG_GF02;

    include ($_CONF['path_system'] . 'classes/navbar.class.php');
    $navmenu = new navbar;
    $navmenu->add_menuitem($LANG_GF01['INDEXPAGE'],"{$_CONF['site_url']}/forum/index.php");
    if ($_USER['uid'] > 1) {
        $navmenu->add_menuitem($LANG_GF01['USERPREFS'],"{$_CONF['site_url']}/forum/userprefs.php");
        $navmenu->add_menuitem($LANG_GF01['SUBSCRIPTIONS'],"{$_CONF['site_url']}/forum/notify.php");
        $navmenu->add_menuitem($LANG_GF01['BOOKMARKS'],"{$_CONF['site_url']}/forum/index.php?op=bookmarks");
    }
    $navmenu->add_menuitem($LANG_GF01['LASTX'],"{$_CONF['site_url']}/forum/index.php?op=lastx");
    $navmenu->add_menuitem($LANG_GF02['msg201'],"{$_CONF['site_url']}/forum/index.php?op=popular");
    if ($current != '') {
        $navmenu->set_selected($current);
    }
    return $navmenu->generate();

}

function ForumHeader($forum,$showtopic) {
    global $_TABLES, $_USER, $_CONF, $CONF_FORUM, $LANG_GF01, $LANG_GF02;

    $forum_outline_header = new Template($_CONF['path_layout'] . '/forum/layout');
    $forum_outline_header->set_file (array ('forum_outline_header'=>'forum_outline_header.thtml'));
    $forum_outline_header->set_var('xhtml',XHTML);
    $forum_outline_header->set_var ('imgset', $CONF_FORUM['imgset']);
    $forum_outline_header->parse ('output', 'forum_outline_header');
    echo $forum_outline_header->finish($forum_outline_header->get_var('output'));

    $navbar = new Template($_CONF['path_layout'] . 'forum/layout');
    $navbar->set_file (array ('topicheader'=>'navbar.thtml'));
    $navbar->set_var ('xhtml',XHTML);
    $navbar->set_var ('site_url', $_CONF['site_url']);
    $navbar->set_var ('search_forum', f_forumsearch());
    $navbar->set_var ('select_forum', f_forumjump());

    if ($CONF_FORUM['usermenu'] == 'navbar') {
        if ($forum == 0) {
            $navbar->set_var('navmenu', forumNavbarMenu($LANG_GF01['INDEXPAGE']));
        } else {
            $navbar->set_var('navmenu', forumNavbarMenu());
        }
    } else {
        $navbar->set_var('navmenu','');
    }
    $navbar->parse ('output', 'topicheader');
    echo $navbar->finish($navbar->get_var('output'));

    if (($forum != '') || ($showtopic != '')) {
        if ($showtopic != '') {
            $forum_id = DB_getItem($_TABLES['gf_topic'],'forum',"id='$showtopic'");
            $grp_id = DB_getItem($_TABLES['gf_forums'],'grp_id',"forum_id='$forum_id'");
        } elseif ($forum != "") {
            $grp_id = DB_getItem($_TABLES['gf_forums'],'grp_id',"forum_id='$forum'");
        }
        $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='$grp_id'");
        if (!SEC_inGroup($groupname)) {
            BlockMessage($LANG_GF01['ACCESSERROR'],$LANG_GF02['msg77'],false);
            $forum_outline_footer= new Template($_CONF['path_layout'] . 'forum/layout');
            $forum_outline_footer->set_file (array ('forum_outline_footer'=>'forum_outline_footer.thtml'));
            $forum_outline_footer->set_var('xhtml',XHTML);
            $forum_outline_footer->set_var ('imgset', $CONF_FORUM['imgset']);
            $forum_outline_footer->parse ('output', 'forum_outline_footer');
            echo $forum_outline_footer->finish ($forum_outline_footer->get_var('output'));
            gf_siteFooter();
            exit;
        }
    }

    $forum_outline_footer= new Template($_CONF['path_layout'] . 'forum/layout');
    $forum_outline_footer->set_file (array ('forum_outline_footer'=>'forum_outline_footer.thtml'));
    $forum_outline_footer->set_var ('xhtml',XHTML);
    $forum_outline_footer->set_var ('imgset', $CONF_FORUM['imgset']);
    $forum_outline_footer->parse ('output', 'forum_outline_footer');
    echo $forum_outline_footer->finish ($forum_outline_footer->get_var('output'));
}

function gf_checkHTMLforSQL($str,$postmode='html') {
    global $CONF_FORUM;

    $bbcode = new StringParser_BBCode ();
    $bbcode->setGlobalCaseSensitive (false);

    if ( $CONF_FORUM['use_glfilter'] == 1 && ($postmode == 'html' || $postmode == 'HTML')) {
        $bbcode->addParser(array('block','inline'), 'gf_cleanHTML');
    }
    $bbcode->addCode ('code', 'simple_replace', null, array ('start_tag' => '[code]', 'end_tag' => '[/code]'),
                      'code', array ('listitem', 'block', 'inline', 'link'), array ());
    $str = $bbcode->parse ($str);
    return $str;
}

/**
* Cleans (filters) HTML - only allows HTML tags specified in the
* $_CONF['user_html'] string.  This function is designed to be called
* by the stringparser class to filter everything except [code] blocks.
*
* @param        string      $message        The topic post to filter
* @return       string      filtered HTML code
*/
function gf_cleanHTML($message) {
    global $_CONF;

    if( isset( $_CONF['skip_html_filter_for_root'] ) &&
             ( $_CONF['skip_html_filter_for_root'] == 1 ) &&
            SEC_inGroup( 'Root' ))
    {
        return $message;
    }

    return COM_filterHTML( $message);
}


function gf_preparefordb($message,$postmode) {
    global $CONF_FORUM, $_CONF;

    // if magic quotes is on, remove the slashes from the $_POST
    if(get_magic_quotes_gpc() ) {
       $message = stripslashes($message);
    }

    if ( $CONF_FORUM['use_glfilter'] == 1 && ($postmode == 'html' || $postmode == 'HTML') ) {
        $message = gf_checkHTMLforSQL($message,$postmode);
    }

    if ($CONF_FORUM['use_censor']) {
        $message = COM_checkWords($message);
    }

    $message = addslashes($message);
    return $message;
}

function geshi_formatted($str,$type='PHP') {
    global $_CONF;

    include_once('geshi.php');

    $geshi = new Geshi($str,$type,"{$_CONF['path_html']}/forum/include/geshi");
    $geshi->set_header_type(GESHI_HEADER_DIV);
    //$geshi->enable_strict_mode(true);
    //$geshi->enable_classes();
    $geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS, 5);
    $geshi->set_overall_style('font-size: 12px; color: #000066; border: 1px solid #d0d0d0; background-color: #FAFAFA;', true);
    // Note the use of set_code_style to revert colours...
    $geshi->set_line_style('font: normal normal 95% \'Courier New\', Courier, monospace; color: #003030;', 'font-weight: bold; color: #006060;', true);
    $geshi->set_code_style('color: #000020;', 'color: #000020;');
    $geshi->set_line_style('background: red;', true);
    $geshi->set_link_styles(GESHI_LINK, 'color: #000060;');
    $geshi->set_link_styles(GESHI_HOVER, 'background-color: #f0f000;');

    $geshi->set_header_content("$type Formatted Code");
    $geshi->set_header_content_style('font-family: Verdana, Arial, sans-serif; color: #808080; font-size: 90%; font-weight: bold; background-color: #f0f0ff; border-bottom: 1px solid #d0d0d0; padding: 2px;');

    return $geshi->parse_code();
}



function gf_checkHTML($str) {
    global $CONF_FORUM, $_CONF;

    // just return if admin doesn't want to filter html
    if ( $CONF_FORUM['use_glfilter'] != 1 ) {
        return $str;
    }
    // if glFusion is configured to allow root to use all html, no need to call
    if( isset( $_CONF['skip_html_filter_for_root'] ) &&
             ( $_CONF['skip_html_filter_for_root'] == 1 ) &&
            SEC_inGroup( 'Root' ))
    {
        return $str;
    }
    return COM_filterHTML($str);
}


function gf_formatTextBlock($str,$postmode='html',$mode='') {
    global $_CONF, $CONF_FORUM;

    $bbcode = new StringParser_BBCode ();
    $bbcode->setGlobalCaseSensitive (false);

    if ( $postmode == 'text') {
        $bbcode->addParser (array ('block', 'inline', 'link', 'listitem'), 'bbcode_htmlspecialchars');
    }
    if ( $CONF_FORUM['use_glfilter'] == 1 && ($postmode == 'html' || $postmode == 'HTML')) {
        $bbcode->addParser(array('block','inline','link','listitem'), 'gf_checkHTML');      // calls GL's checkHTML on all text blocks
    }
    if ( $postmode != 'html' && $postmode != 'HTML') {
        $bbcode->addParser(array('block','inline','link','listitem'), 'nl2br');
    }
    $bbcode->addParser(array('block','inline','link','listitem'), 'gf_replacesmilie');      // calls replacesmilie on all text blocks
    $bbcode->addParser(array('block','inline','link','listitem'), 'gf_fixtemplate');
    $bbcode->addParser(array('block','inline','link','listitem'), 'PLG_replacetags');

    $bbcode->addParser ('list', 'bbcode_stripcontents');
    $bbcode->addCode ('b', 'simple_replace', null, array ('start_tag' => '<b>', 'end_tag' => '</b>'),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<i>', 'end_tag' => '</i>'),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('u', 'simple_replace', null, array ('start_tag' => '<span style="text-decoration: underline;">', 'end_tag' => '</span>'),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('p', 'simple_replace', null, array ('start_tag' => '<p>', 'end_tag' => '</p>'),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('s', 'simple_replace', null, array ('start_tag' => '<del>', 'end_tag' => '</del>'),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('size', 'callback_replace', 'do_bbcode_size', array('usecontent_param' => 'default'),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('color', 'callback_replace', 'do_bbcode_color', array ('usercontent_param' => 'default'),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('list', 'callback_replace', 'do_bbcode_list', array ('usecontent_param' => 'default'),
                      'list', array ('inline','block', 'listitem'), array ());
    $bbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'),
                      'listitem', array ('list'), array ());
    $bbcode->addCode ('quote','simple_replace',null,array('start_tag' => '</p><div class="quotemain"><img src="' . $_CONF['layout_url'] . '/forum/image_set/img_quote.gif" alt=""/>', 'end_tag' => '</div><p>'),
                      'inline', array('listitem','block','inline','link'), array());
    $bbcode->addCode ('url', 'usecontent?', 'do_bbcode_url', array ('usecontent_param' => 'default'),
                      'link', array ('listitem', 'block', 'inline'), array ('link'));
    $bbcode->addCode ('link', 'callback_replace_single', 'do_bbcode_url', array (),
                      'link', array ('listitem', 'block', 'inline'), array ('link'));
    $bbcode->addCode ('img', 'usecontent', 'do_bbcode_img', array (),
                      'image', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('file', 'usecontent', 'do_bbcode_file', array (),
                      'image', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('code', 'usecontent', 'do_bbcode_code', array ('usecontent_param' => 'default'),
                      'code', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->setCodeFlag ('quote', 'paragraph_type', BBCODE_PARAGRAPH_ALLOW_INSIDE);
    $bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
    $bbcode->setCodeFlag ('*', 'paragraphs', true);
    $bbcode->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
    $bbcode->setCodeFlag ('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);

    $bbcode->setRootParagraphHandling (true);

    if ($CONF_FORUM['use_censor']) { // and $mode == 'preview') {
        $str = COM_checkWords($str);
    }
    $str = $bbcode->parse ($str);

    return $str;
}


function bbcode_oldpost($text) {
    global $CONF_FORUM;

    if ($CONF_FORUM['pre2.5_mode'] == true ) {
        $comment = str_replace("&#36;","$", $text);
        $comment = str_replace("<br />","\r",$comment);
        $comment = str_replace("<br>","\r",$comment);
        $comment = str_replace ( '&amp;', '&', $comment );
        $comment = str_replace ( '&#039;', '\'', $comment );
        $comment = str_replace ( '&quot;', '"', $comment );
        $comment = str_replace ( '&lt;', '<', $comment );
        $comment = str_replace ( '&gt;', '>', $comment );
        $comment = str_replace ( '<b>', '[b]', $comment );
        $comment = str_replace ( '</b>', '[/b]', $comment );
        $comment = str_replace ( '<i>', '[i]', $comment );
        $comment = str_replace ( '</i>', '[/i]', $comment );
        $comment = str_replace ( '<p>', '[p]', $comment );
        $comment = str_replace ( '</p>', '[/p]', $comment );
    } else {
        return $text;
    }
    return $comment;
}

function gf_formatOldPost($str,$postmode='html',$mode='') {
    global $CONF_FORUM;

    $oldPost = 0;

    if ( $CONF_FORUM['pre2.5_mode'] != true ) {
        return $str;
    }

    if (strstr($str,'<pre class="forumCode">') !== false)  $oldPost = 1;
    if (strstr($str,"[code]<code>") !== false) $oldPost = 1;
    if (strstr($str,"<pre>") !== false ) $oldPost = 1;

    if ( stristr($str,'[code') == false || stristr($str,'[code]<code>') == true) {
        if (strstr($str,"<pre>") !== false)  $oldPost = 1;
        $str = str_replace('<pre>','[code]',$str);
        $str = str_replace('</pre>','[/code]',$str);
    }
    $str = str_ireplace("[code]<code>",'[code]',$str);
    $str = str_ireplace("</code>[/code]",'[/code]',$str);
    $str = str_replace(array("<br />\r\n","<br />\n\r","<br />\r","<br />\n"), '<br />', $str );
    $str = preg_replace("/\[QUOTE\sBY=\s(.+?)\]/i","[QUOTE] Quote by $1:",$str);
    /* Reformat code blocks - version 2.3.3 and prior */
    $str = str_replace( '<pre class="forumCode">', '[code]', $str );
    $str = preg_replace("/\[QUOTE\sBY=(.+?)\]/i","[QUOTE] Quote by $1:",$str);

    $bbcode = new StringParser_BBCode ();
    $bbcode->setGlobalCaseSensitive (false);

    if ( $postmode == 'text') {
        $bbcode->addParser (array ('block', 'inline', 'link', 'listitem'), 'bbcode_htmlspecialchars');
    }
    if ( $CONF_FORUM['use_glfilter'] == 1 && ($postmode == 'html' || $postmode == 'HTML') ) {
        $bbcode->addParser(array('block','inline','link','listitem'), 'gf_checkHTML');      // calls checkHTML on all text blocks
    }
    $bbcode->addParser(array('block','inline','link','list','listitem'), 'bbcode_oldpost');

    $bbcode->addCode ('code', 'simple_replace', null, array ('start_tag' => '[code]', 'end_tag' => '[/code]'),
                      'code', array ('listitem', 'block', 'inline', 'link'), array ());

    if ( $CONF_FORUM['use_censor'] ) {
        $str = COM_checkWords($str);
    }
    $str = $bbcode->parse ($str);

    // If we have identified an old post based on the checks above
    // it is possible that code blocks will have htmlencoded items
    // we need to reverse that ...
    if ( $oldPost ) {
        if ( strstr($str,"\\'") !== false ) {
            $str = stripslashes($str);
        }
        $str = str_replace("&#36;","$", $str);
        $str = str_replace("<br />","\r",$str);
        $str = str_replace("<br>","\r",$str);
        $str = str_replace ( '&amp;', '&', $str );
        $str = str_replace ( '&#039;', '\'', $str );
        $str = str_replace ( '&quot;', '"', $str );
        $str = str_replace ( '&lt;', '<', $str );
        $str = str_replace ( '&gt;', '>', $str );
    }

    $str = str_replace ( '&#92;', '\\',$str);

    return $str;
}

function gf_replacesmilie($str) {
    global $_CONF,$_TABLES,$CONF_FORUM;

    if($CONF_FORUM['allow_smilies']) {
        if (function_exists('msg_showsmilies') AND $CONF_FORUM['use_smilies_plugin']) {
            $str = msg_replaceEmoticons($str);
        } else {
            $str = forum_xchsmilies($str);
        }
    }

    return $str;
}

/* Function gf_getImage - used to return the image URL for icons
 * The forum uses a number of icons and you may have a need to use a mixture of image types.
 * Enabling the $CONF_FORUM['autoimagetype'] feature will invoke a test that will first
 * check for an image of the type set in your themes function.php $_IMAGE_TYPE
 * If the icon of that image type is not found, then it will use an image of type
 * specified by the $CONF_FORUM['image_type_override'] setting.

 * Set $CONF_FORUM['autoimagetype'] to false in the plugins config.php to disable this feature and
 * only icons of type set by the themes $_IMAGE_TYPE setting will be used
*/
function gf_getImage($image,$directory='') {
    global $CONF_FORUM,$_IMAGE_TYPE;

    if ($directory != '')  {
        $fullImagePath = "{$CONF_FORUM['imgset_path']}/{$directory}/{$image}.{$_IMAGE_TYPE}";
    } else {
        $fullImagePath = "{$CONF_FORUM['imgset_path']}/{$image}.{$_IMAGE_TYPE}";
    }
    if ($CONF_FORUM['autoimagetype']) {
        $fullImageURL = "{$CONF_FORUM['imgset']}/";
        if ($directory != '')  $fullImageURL .= "{$directory}/";

        if (file_exists($fullImagePath)) {
            $fullImageURL .= "{$image}.{$_IMAGE_TYPE}";
        } else {
            $fullImageURL .= "{$image}.{$CONF_FORUM['image_type_override']}";
        }
    } else {
        $fullImageURL = "{$CONF_FORUM['imgset']}/{$image}.{$_IMAGE_TYPE}";
    }
    return $fullImageURL;
}


function BlockMessage($title,$message='',$sitefooter=true){

    echo COM_startBlock($title);
    echo $message;
    echo COM_endBlock();
    if ($sitefooter) {
        echo COM_siteFooter();
    }
    return;
}

function alertMessage($message,$title='',$prompt='') {
    global $_CONF, $CONF_FORUM,$LANG_GF02;

    $alertmsg = new Template($_CONF['path_layout'] . 'forum/layout');
    $alertmsg->set_file (array (
        'outline_header'=>'forum_outline_header.thtml',
        'alertmsg'=>'alertmsg.thtml',
        'outline_footer'=>'forum_outline_footer.thtml'));

    $alertmsg->set_var ('xhtml',XHTML);
    $alertmsg->set_var ('imgset', $CONF_FORUM['imgset']);
    $alertmsg->set_var ('layout_url', $_CONF['layout_url']);
    $alertmsg->set_var ('alert_title', $title);
    $alertmsg->set_var ('alert_message', $message);
    if ($prompt == '') {
        $alertmsg->set_var ('prompt', $LANG_GF02['msg148']);
    } else {
        $alertmsg->set_var ('prompt', $prompt);
    }
    $alertmsg->parse ('alert_header', 'outline_header');
    $alertmsg->parse ('alert_footer', 'outline_footer');
    $alertmsg->parse ('output', 'alertmsg');
    echo $alertmsg->finish ($alertmsg->get_var('output'));
    return;
}


function BaseFooter($showbottom=true) {
    global $_USER,$_CONF,$LANG_GF02,$forum,$CONF_FORUM;

    if (!$CONF_FORUM['registration_required'] OR $_USER['uid'] > 1) {
        $footer = new Template($_CONF['path_layout'] . 'forum/layout');
        $footer->set_file (array ('footerblock'=>'footer/footer.thtml',
                  'header'=>'forum_outline_header.thtml',
                  'footer'=>'forum_outline_footer.thtml'
        ));
        $footer->set_var ('xhtml',XHTML);
        $footer->set_var ('imgset', $CONF_FORUM['imgset']);
        $footer->parse('outline_header','header',true);
        $footer->parse('outline_footer','footer',true);
        if ($forum == '') {
            $footer->set_var ('forum_time', f_forumtime() );
            if ($showbottom == "true") {
                $footer->set_var ('forum_legend', f_legend() );
                $footer->set_var ('forum_whosonline', f_whosonline() );
            }
          } else {
            $footer->set_var ('forum_time', f_forumtime() );
            if ($showbottom == "true") {
                $footer->set_var ('forum_legend', f_legend() );
                $footer->set_var ('forum_rules', f_forumrules() );
            }
        }
        $footer->set_var ('search_forum', f_forumsearch() );
        $footer->set_var ('select_forum', f_forumjump() );
        $footer->parse ('output', 'footerblock');
        echo $footer->finish($footer->get_var('output'));
    }
}

function f_forumsearch() {
    global $_CONF,$_TABLES,$LANG_GF01,$LANG_GF02,$forum;

    $forum_search = new Template($_CONF['path_layout'] . 'forum/layout');
    $forum_search->set_file (array ('forum_search'=>'forum_search.thtml'));
    $forum_search->set_var ('forum', $forum);
    $forum_search->set_var ('xhtml',XHTML);
    if ($forum == "") {
        $forum_search->set_var ('search', $LANG_GF02['msg117']);
    } else {
        $forum_search->set_var ('search', $LANG_GF02['msg118']);
    }
    $forum_search->set_var ('jumpheading', $LANG_GF02['msg103']);
    $forum_search->set_var ('LANG_GO', $LANG_GF01['GO']);
    $forum_search->parse ('output', 'forum_search');
    return $forum_search->finish($forum_search->get_var('output'));
}

function f_forumjump($action='',$selected=0) {
    global $CONF_FORUM, $_CONF,$_TABLES,$LANG_GF01,$LANG_GF02;

    $initialOptGroup = 0;
    $selecthtml = "";
    $asql = DB_query("SELECT * FROM {$_TABLES['gf_categories']} ORDER BY cat_order ASC");
    while($A = DB_fetchArray($asql)) {
        $firstforum=true;
        $bsql = DB_query("SELECT * FROM {$_TABLES['gf_forums']} WHERE forum_cat='$A[id]' ORDER BY forum_order ASC");
        while($B = DB_fetchArray($bsql)) {
            $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='{$B['grp_id']}'");
            if (SEC_inGroup($B['grp_id'])) {
                if ($firstforum) {
                    $selecthtml .= '<optgroup label="' .$A['cat_name']. '">';
                    $initialOptGroup = 1;
                }
                $firstforum=false;
                if ($selected > 0 AND $selected == $B['forum_id']) {
                    $selecthtml .= LB .'<option value="' .$B['forum_id']. '" selected="selected">&#187;&nbsp;' .$B['forum_name']. '</option>';
                } else {
                    $selecthtml .= LB .'<option value="' .$B['forum_id']. '">&#187;&nbsp;' .$B['forum_name']. '</option>';
                }
            }
        }
        if ( $initialOptGroup == 1 ) {
            $selecthtml .= '</optgroup>';
        }
    }
    $forum_jump = new Template($_CONF['path_layout'] . 'forum/layout');
    $forum_jump->set_file (array ('forum_jump'=>'forum_jump.thtml'));
    $forum_jump->set_var ('xhtml',XHTML);
    $forum_jump->set_var ('LANG_msg103', $LANG_GF02['msg103']);
    $forum_jump->set_var ('LANG_msg106', $LANG_GF02['msg106']);
    $forum_jump->set_var ('jumpheading', $LANG_GF02['msg103']);
    $forum_jump->set_var ('imgset', $CONF_FORUM['imgset']);
    if ($action == '') {
        $forum_jump->set_var ('action', $_CONF['site_url'] . '/forum/index.php');
    } else {
        $forum_jump->set_var ('action', $action);
    }
    $forum_jump->set_var ('selecthtml', $selecthtml);
    $forum_jump->set_var ('LANG_GO', $LANG_GF01['GO']);
    $forum_jump->parse ('output', 'forum_jump');
    return $forum_jump->finish($forum_jump->get_var('output'));
}

function f_forumtime() {
    global $CONF_FORUM, $_CONF,$_TABLES,$LANG_GF01,$LANG_GF02,$forum;

    $forum_time = new Template($_CONF['path_layout'] . 'forum/layout/footer');
    $forum_time->set_file (array ('forum_time'=>'forum_time.thtml'));
    $forum_time->set_var('xhtml',XHTML);
    $timezone = strftime('%Z');
    $time = strftime('%I:%M %p');
    $forum_time->set_var ('imgset', $CONF_FORUM['imgset']);
    $forum_time->set_var ('message', sprintf($LANG_GF02['msg121'],$timezone,$time));
    $forum_time->parse ('output', 'forum_time');
    return $forum_time->finish($forum_time->get_var('output'));
}

function f_legend() {
    global $CONF_FORUM,$forum,$_CONF,$LANG_GF01,$LANG_GF02;

    $forum_legend = new Template($_CONF['path_layout'] . 'forum/layout/footer');
    $forum_legend->set_file (array ('forum_legend'=>'forum_legend.thtml'));
    $forum_legend->set_var ('xhtml',XHTML);
    $forum_legend->set_var ('imgset', $CONF_FORUM['imgset']);

    if ($forum == '') {
        $forum_legend->set_var ('normal_msg', $LANG_GF02['msg194']);
        $forum_legend->set_var ('new_msg', $LANG_GF02['msg108']);
        $forum_legend->set_var ('normal_icon','<img src="'.gf_getImage('quietforum').'" alt="'.$LANG_GF02['msg194'].'" title="' .$LANG_GF02['msg194']. '"' . XHTML . '>');
        $forum_legend->set_var ('new_icon','<img src="'.gf_getImage('busyforum').'" alt="'.$LANG_GF02['msg111'].'" title="' .$LANG_GF02['msg111']. '"' . XHTML . '>');
        $forum_legend->set_var ('viewnew_icon','<img src="'.gf_getImage('viewnew').'" alt="' . $LANG_GF02['msg112'] .'" title="' .$LANG_GF02['msg112']. '"' . XHTML . '>');
        $forum_legend->set_var ('viewnew_msg', $LANG_GF02['msg112']);
        $forum_legend->set_var ('markread_icon','<img src="'.gf_getImage('allread').'" alt="' . $LANG_GF02['msg84'] .'" title="' .$LANG_GF02['msg84']. '"' . XHTML . '>');
        $forum_legend->set_var ('markread_msg', $LANG_GF02['msg84']);
    } else {
        $sticky_icon = '<img src="'.gf_getImage('sticky').'" alt="' .$LANG_GF02['msg61']. '" title="' .$LANG_GF02['msg61']. '"' . XHTML . '>';
        $locked_icon = '<img src="'.gf_getImage('locked').'" alt="' .$LANG_GF02['msg114']. '" title="' .$LANG_GF02['msg114']. '"' . XHTML . '>';
        $stickynew_icon = '<img src="'.gf_getImage('sticky_new').'" alt="' .$LANG_GF02['msg115']. '" title="' .$LANG_GF02['msg115']. '"' . XHTML . '>';
        $lockednew_icon = '<img src="'.gf_getImage('locked_new').'" alt="' .$LANG_GF02['msg116']. '" title="' .$LANG_GF02['msg116']. '"' . XHTML . '>';
        $forum_legend->set_var ('normal_icon','<img src="'.gf_getImage('noposts').'" alt="'.$LANG_GF02['msg59'].'" title="' .$LANG_GF02['msg59']. '"' . XHTML . '>');
        $forum_legend->set_var ('new_icon','<img src="'.gf_getImage('newposts').'" alt="'.$LANG_GF02['msg60'].'" title="' .$LANG_GF02['msg60']. '"' . XHTML . '>');
        $forum_legend->set_var ('normal_msg', $LANG_GF02['msg59']);
        $forum_legend->set_var ('new_msg', $LANG_GF02['msg60']);
        $forum_legend->set_var ('sticky_msg',$LANG_GF02['msg61']);
        $forum_legend->set_var ('locked_msg', $LANG_GF02['msg114']);
        $forum_legend->set_var ('stickynew_msg', $LANG_GF02['msg115']);
        $forum_legend->set_var ('lockednew_msg', $LANG_GF02['msg116']);
        $forum_legend->set_var ('locked_icon', $locked_icon);
        $forum_legend->set_var ('sticky_icon', $sticky_icon);
        $forum_legend->set_var ('stickynew_icon', $stickynew_icon);
        $forum_legend->set_var ('lockednew_icon', $lockednew_icon);
    }

    $forum_legend->parse ('output', 'forum_legend');
    return $forum_legend->finish($forum_legend->get_var('output'));
}

function f_whosonline(){
    global $CONF_FORUM, $_CONF,$_TABLES,$LANG_GF02;

    $onlineusers = phpblock_whosonline();
    $forum_users = new Template($_CONF['path_layout'] . 'forum/layout/footer');
    $forum_users->set_file (array ('forum_users'=>'forum_users.thtml'));
    $forum_users->set_var ('xhtml',XHTML);
    $forum_users->set_var ('LANG_msg07', $LANG_GF02['msg07']);
    $forum_users->set_var ('imgset', $CONF_FORUM['imgset']);
    $forum_users->set_var ('onlineusers', $onlineusers);
    $forum_users->parse ('output', 'forum_users');
    return $forum_users->finish($forum_users->get_var('output'));
}

function f_forumrules() {
    global $_CONF,$_USER,$LANG_GF01,$LANG_GF02,$CONF_FORUM,$canPost;

    if ( ($CONF_FORUM['registered_to_post'] AND ($_USER['uid'] < 2 OR empty($_USER['uid'])) ) || $canPost == 0 ) {
        $postperm_msg = $LANG_GF01['POST_PERM_MSG1'];
        $post_perm_image = '<img src="'.gf_getImage('red_dot').'" alt=""' . XHTML . '>';
    } else {
        $postperm_msg = $LANG_GF01['POST_PERM_MSG1'];
        $post_perm_image = '<img src="'.gf_getImage('green_dot').'" alt=""' . XHTML . '>';
    }
    if ($CONF_FORUM['allow_html']) {
        $html_perm_image = '<img src="'.gf_getImage('green_dot').'" alt=""' . XHTML . '>';
        if ($CONF_FORUM['use_glfilter']) {
            $htmlmsg = $LANG_GF01['HTML_FILTER_MSG'];
        } else {
            $htmlmsg = $LANG_GF01['HTML_FULL_MSG'];
        }
    } else {
        $htmlmsg = $LANG_GF01['HTML_MSG'];
        $html_perm_image = '<img src="'.gf_getImage('red_dot').'" alt=""' . XHTML . '>';
    }
    if ($CONF_FORUM['use_censor']) {
        $censor_perm_image = '<img src="'.gf_getImage('green_dot').'" alt=""' . XHTML . '>';
    } else {
        $censor_perm_image = '<img src="'.gf_getImage('red_dot').'" alt=""' . XHTML . '>';
    }

    if ($CONF_FORUM['show_anonymous_posts']) {
        $anon_perm_image = '<img src="'.gf_getImage('green_dot').'" alt=""' . XHTML . '>';
    } else {
        $anon_perm_image = '<img src="'.gf_getImage('red_dot').'" alt=""' . XHTML . '>';
    }
    $forum_rules = new Template($_CONF['path_layout'] . 'forum/layout/footer');
    $forum_rules->set_file (array ('forum_rules'=>'forum_rules.thtml'));
    $forum_rules->set_var ('xhtml',XHTML);
    $forum_rules->set_var ('imgset', $CONF_FORUM['imgset']);
    $forum_rules->set_var ('LANG_title', $LANG_GF02['msg101']);

    $forum_rules->set_var ('anonymous_msg', $LANG_GF01['ANON_PERM_MSG']);
    $forum_rules->set_var ('anon_perm_image', $anon_perm_image);

    $forum_rules->set_var ('postingperm_msg',$postperm_msg);
    $forum_rules->set_var ('post_perm_image', $post_perm_image);

    $forum_rules->set_var ('html_msg', $htmlmsg);
    $forum_rules->set_var ('html_perm_image', $html_perm_image);
    $forum_rules->set_var ('censor_msg', $LANG_GF01['CENSOR_PERM_MSG']);
    $forum_rules->set_var ('censor_perm_image', $censor_perm_image);

    $forum_rules->parse ('output', 'forum_rules');
    return $forum_rules->finish($forum_rules->get_var('output'));

}

/*
 * The purpose of this function is to update 2 items:
 *
 * 1 - gf_forum - the last_post_rec
 * 2 - gf_topic - the lastupdated and last_reply_rec for a topic parent record
 */

function gf_updateLastPost($forumid,$topicparent=0) {
    global $_TABLES;

    if ($topicparent == 0) {
        // Get the last topic in this forum
        $query = DB_query("SELECT MAX(id)as maxid FROM {$_TABLES['gf_topic']} WHERE forum=$forumid");
        list($topicparent) = DB_fetchArray($query);
        if ($topicparent > 0) {
            $lastrecid = $topicparent;
            DB_query("UPDATE {$_TABLES['gf_forums']} SET last_post_rec=$lastrecid WHERE forum_id=$forumid");
        }
    } else {
        $query = DB_query("SELECT MAX(id)as maxid FROM {$_TABLES['gf_topic']} WHERE pid=$topicparent");
        list($lastrecid) = DB_fetchArray($query);
    }

    if ($lastrecid == NULL AND $topicparent > 0) {
        $topicdatecreated = DB_getITEM($_TABLES['gf_topic'],date,"id=$topicparent");
        DB_query("UPDATE {$_TABLES['gf_topic']} SET last_reply_rec=$topicparent, lastupdated='$topicdatecreated' WHERE id={$topicparent}");
    } elseif ($topicparent > 0) {
        $topicdatecreated = DB_getITEM($_TABLES['gf_topic'],date,"id=$lastrecid");
        DB_query("UPDATE {$_TABLES['gf_topic']}  SET last_reply_rec=$lastrecid, lastupdated=$topicdatecreated WHERE id={$topicparent}");
    }
    if ($topicparent > 0) {
        // Recalculate and Update the number of replies
        $numreplies = DB_Count($_TABLES['gf_topic'], "pid", $topicparent);
        DB_query("UPDATE {$_TABLES['gf_topic']} SET replies = '$numreplies' WHERE id=$topicparent");
    }
}

function gf_showattachments($topic,$mode='') {
    global $_TABLES,$_CONF,$CONF_FORUM,$_FM_TABLES;

    $retval = '';
    $sql = "SELECT id,repository_id,filename FROM {$_TABLES['gf_attachments']} WHERE topic_id=$topic ";
    if ($mode != 'edit') {
        $sql .= "AND show_inline=0 ";
    }
    $sql .= "ORDER BY id";
    $query = DB_query($sql);

    // Check and see if the filemgmt plugin is installed and enabled
    if (function_exists('filemgmt_buildAccessSql')) {
        $filemgmtSupport = true;
    } else {
        $filemgmtSupport = false;
    }

//    $retval .= '<div id="fileattachlist">';
    while (list($id,$lid,$field_value) =  DB_fetchArray($query)) {
        $retval .= '<div class="tblforumfile">';
        $filename = explode(':',$field_value);
        if ($filemgmtSupport AND $lid > 0) {   // Check and see if user has access to file
            $groupsql = filemgmt_buildAccessSql();
            $sql = "SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_filedetail']} a ";
            $sql .= "LEFT JOIN {$_FM_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
            $sql .= "WHERE a.lid='$lid' $groupsql";
            list($testaccess_cnt) = DB_fetchArray(DB_query($sql));
        }
        if ($lid > 0 AND (!$filemgmtSupport OR $testaccess_cnt == 0 OR DB_count($_FM_TABLES['filemgmt_filedetail'],"lid",$lid ) == 0)) {
            $retval .= "<img src=\"{$CONF_FORUM['imgset']}/document_sm.gif\" border=\"0\" alt=\"\"" . XHTML . ">Insufficent Access";
        } elseif (!empty($field_value)) {
            $retval .= "<img src=\"{$CONF_FORUM['imgset']}/document_sm.gif\" border=\"0\" alt=\"\"" . XHTML . ">";
            $retval .= "<a href=\"{$_CONF['site_url']}/forum/getattachment.php?id=$id\" target=\"_new\">";
            $retval .= "{$filename[1]}</a>&nbsp;";
            if ($mode == 'edit') {
                $retval .= "<a href=\"#\" onclick='ajaxDeleteFile($topic,$id);'>";
                $retval .= "<img src=\"{$CONF_FORUM['imgset']}/delete.gif\" border=\"0\" alt=\"\"" . XHTML . "></a>";
            }
        } else {
            $retval .= 'N/A&nbsp;';
        }
        $retval .= '</div>';
    }
//    $retval .= '</div>';
    return $retval;
}


// Generates the HTML Select element for the listing of filemgmt plugin the user has access to
function gf_makeFilemgmtCatSelect($uid) {
    global $_CONF,$_FM_TABLES,$_DB_name;

    include_once($_CONF['path_html']."filemgmt/include/xoopstree.php");
    include_once($_CONF['path_html']."filemgmt/include/textsanitizer.php");
    $_GROUPS = SEC_getUserGroups( $uid );
    $mytree = new XoopsTree($_DB_name,$_FM_TABLES['filemgmt_cat'],"cid","pid");
    $mytree->setGroupUploadAccessFilter($_GROUPS);
    return $mytree->makeMySelBox('title', 'title','','','filemgmtcat');
}

function ADMIN_getListField_forum($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG04, $LANG28, $_IMAGE_TYPE;
    global $CONF_FORUM;

    $retval = '';

    switch ($fieldname) {
        case 'date':
            $retval = strftime( $CONF_FORUM['default_Datetime_format'], $fieldvalue );
            break;
        case 'lastupdated':
            $retval = strftime( $CONF_FORUM['default_Datetime_format'], $fieldvalue );
            break;
        case 'subject':
            $testText        = gf_formatTextBlock($A['comment'],'text','text');
            $testText        = strip_tags($testText);
            $lastpostinfogll = htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$CONF_FORUM['contentinfo_numchars']). '...')));
            $retval = '<a class="gf_mootip" style="text-decoration:none;" href="' . $_CONF['site_url'] . '/forum/viewtopic.php?showtopic=' . $A['id'] . '" title="' . $A['subject'] . '::' . $lastpostinfogll . '" rel="nofollow">' . $fieldvalue . '</a>';
            break;
        default:
            $retval = $fieldvalue;
            break;
    }

    return $retval;
}

?>