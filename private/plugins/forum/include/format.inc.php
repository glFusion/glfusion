<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | format.inc.php                                                           |
// |                                                                          |
// | General formatting routines                                              |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Eric M. Kingsley       kingsley AT trains-n-town DOTcom                  |
// |                                                                          |
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
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

// Magic url types
define('MAGIC_URL_EMAIL', 1);
define('MAGIC_URL_FULL', 2);
define('MAGIC_URL_LOCAL', 3);
define('MAGIC_URL_WWW', 4);

define('DISABLE_BBCODE',1);
define('DISABLE_SMILIES',2);
define('DISABLE_URLPARSE',4);

USES_lib_html2text();

if (!class_exists('StringParser') ) {
    require_once $_CONF['path'] . 'lib/bbcode/stringparser_bbcode.class.php';
}

function convertlinebreaks ($text) {
    return preg_replace ("/\015\012|\015|\012/", "\n", $text);
}

function bbcode_stripcontents ($text) {
    return preg_replace ("/[^\n]/", '', $text);
}

function bbcode_htmlspecialchars($text) {
    global $_FF_CONF;

    return (@htmlspecialchars ($text,ENT_QUOTES, COM_getEncodingt()));
}

function do_bbcode_url ($action, $attributes, $content, $params, $node_object) {
    global $_FF_CONF;

    if ($action == 'validate') {
        return true;
    }

    if (!isset ($attributes['default'])) {
        if ( stristr($content,'http') ) {
            return '<a href="'.strip_tags($content).'" rel="nofollow">'.@htmlspecialchars ($content,ENT_QUOTES, COM_getEncodingt()).'</a>';
        } else {
            return '<a href="http://'.strip_tags($content).'" rel="nofollow">'.@htmlspecialchars ($content,ENT_QUOTES, COM_getEncodingt()).'</a>';

        }
    }
    if ( stristr($attributes['default'],'http') ) {
        return '<a href="'.strip_tags($attributes['default']).'" rel="nofollow">'.$content.'</a>';
    } else {
        return '<a href="http://'.strip_tags($attributes['default']).'" rel="nofollow">'.$content.'</a>';
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
    global $_CONF,$_TABLES,$_FF_CONF,$topicRec,$forumfiles;
    global $previewitem,$filemgmt_FileStoreURL,$LANG_GF10;

    $retval = '';
    if ( $action == 'validate') {
        return true;
    }

    $uniqueID = 0;
    if ( isset($_POST['uniqueid']) ) {
        $uniqueID = COM_applyFilter($_POST['uniqueid'],true);
    }

    $sql = "SELECT id,filename,repository_id,show_inline,topic_id FROM {$_TABLES['ff_attachments']} ";
    if ( $uniqueID > 0 ) {  // User is previewing a new post
        $sql .= "WHERE topic_id = ". (int) $uniqueID ." AND tempfile=1 ";
    } else if (isset($previewitem['id'])) {
         $sql .= "WHERE topic_id = ".(int) $previewitem['id']." ";
    } else if (isset($topicRec['id'])){
        $sql .= "WHERE topic_id = ".(int) $topicRec['id']." ";
    } else {
        return '';
    }
    $sql .= "ORDER BY id";
    $query = DB_query($sql);
    $i = 1;

    if ( isset($attributes['align'] ) ) {
        if ( !in_array(strtolower($attributes['align']),array('left','right','center') ) ) {
            $attributes['align'] = 'left';
        }
        $align = ' align="' . $attributes['align'] . '" ';
    } else {
        $align = '';
    }

    if ( isset($attributes['lightbox'] ) ) {
        $lb = ' rel="lightbox" data-uk-lightbox ';
    } else {
        $lb = '';
    }

    while (list($id,$fileinfo,$repository_id,$showinline,$topicid) = DB_fetchArray($query)) {
        if ($i == $content) {
            if ($showinline == 0) {
                DB_query("UPDATE {$_TABLES['ff_attachments']} SET show_inline = 1 WHERE id=".(int)$id);
            }
            $forumfiles[$i] = $id;   // used to track attachments used inline and reset others in case user is changing them
            $fileparts = explode(':',$fileinfo);
            $pos = strrpos($fileparts[0],'.');
            $filename = substr($fileparts[0], 0,$pos);
            $ext = substr($fileparts[0], $pos+1);
            if ($repository_id > 0) {
                $srcImage = "{$filemgmt_FileStoreURL}/{$filename}.{$ext}";
            } else {
                $srcImage = "{$_FF_CONF['downloadURL']}/{$filename}.{$ext}";
            }

            if (file_exists("{$_FF_CONF['uploadpath']}/tn/{$filename}.{$ext}")) {
                $srcThumbnail = "{$_FF_CONF['downloadURL']}/tn/{$filename}.{$ext}";
            } else {
                if (file_exists("{$_CONF['path_html']}/forum/images/icons/{$ext}.gif")) {
                    $srcThumbnail = "{$_CONF['site_url']}/forum/images/icons/{$ext}.gif";
                } else {
                    $srcThumbnail = "{$_CONF['site_url']}/forum/images/icons/none.gif";
                }
            }
            $retval = '<a href="'.$srcImage.'" '.$lb.' target="_new"><img src="'. $srcThumbnail . '" '.$align.' style="padding:5px;" title="'.$LANG_GF10['click2download'].'" alt="'.$LANG_GF10['click2download'].'"/></a>';
            break;
         }
        $i++;
    }

    return $retval;

}

function do_bbcode_img ($action, $attributes, $content, $params, $node_object) {
    global $_FF_CONF;

    if ($action == 'validate') {
        if (isset($attributes['caption'])) {
            $node_object->setFlag('paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
            if ($node_object->_parent->type() == STRINGPARSER_NODE_ROOT OR
                in_array($node_object->_parent->_codeInfo['content_type'], array('block', 'list', 'listitem'))) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    if ($_FF_CONF['allow_img_bbcode']) {
        if ( isset($attributes['h']) AND isset ($attributes['w']) ) {
            $dim = 'width=' . (int) $attributes['w'] . ' height=' . (int) $attributes['h'];
        } else {
            $dim = '';
        }
        if ( isset($attributes['align'] ) ) {
            if ( !in_array(strtolower($attributes['align']),array('left','right','center') ) ) {
                $attributes['align'] = 'left';
            }
            $align = ' align=' . $attributes['align'] . ' ';
        } else {
            $align = '';
        }
        $content = bbcode_cleanHTML($content);
        return '<img src="'.htmlspecialchars($content,ENT_QUOTES, COM_getEncodingt()).'" ' . $dim . $align . ' alt=""/>';
    } else {
        return '[img]' . bbcode_cleanHTML($content) . '[/img]';
    }
}

function do_bbcode_size  ($action, $attributes, $content, $params, $node_object) {
    if ( $action == 'validate') {
        return true;
    }
    return '<span style="font-size: '.(int) $attributes['default'].'px;">'.$content.'</span>';
}

function do_bbcode_color  ($action, $attributes, $content, $params, $node_object) {
    if ( $action == 'validate') {
        return true;
    }
    return '<span style="color: '. strip_tags($attributes['default']).';">'.$content.'</span>';
}

function do_bbcode_code($action, $attributes, $content, $params, $node_object) {
    global $_FF_CONF, $_ff_pm;

    if ( $action == 'validate') {
        return true;
    }

    if($_FF_CONF['allow_smilies']) {
        if (function_exists('msg_restoreEmoticons') AND $_FF_CONF['use_smilies_plugin']) {
            $content = msg_restoreEmoticons($content);
        } else {
            $content = forum_xchsmilies($content,true);
        }
    }
    if ($_FF_CONF['use_geshi']) {
        /* Support for formatting various code types : [code=java] for example */
        if (!isset ($attributes['default'])) {
            $codeblock = '</p>' . _ff_geshi_formatted($content) . '<p>';
        } else {
            $codeblock = '</p>' . _ff_geshi_formatted($content,strtoupper(strip_tags($attributes['default']))) . '<p>';
        }
    } else {
        $codeblock = '<pre class="codeblock">'  . @htmlspecialchars($content,ENT_QUOTES, COM_getEncodingt()) . '</pre>';
    }

    $codeblock = str_replace('{','&#123;',$codeblock);
    $codeblock = str_replace('}','&#125;',$codeblock);

    if ( ($_FF_CONF['use_wysiwyg_editor'] == 1 && $_ff_pm != 'text') || $_ff_pm == 'html' ) {
        $codeblock = str_replace('&lt;','<',$codeblock);
        $codeblock = str_replace('&gt;','>',$codeblock);
        $codeblock = str_replace('&amp;','&',$codeblock);
        $codeblock = str_replace("<br /><br />","<br />",$codeblock);
        $codeblock = str_replace("<p>","",$codeblock);
        $codeblock = str_replace("</p>","",$codeblock);
    }

    return $codeblock;
}

/**
* Cleans (filters) HTML - only allows safe HTML tags
*
* @param        string      $str    string to filter
* @return       string      filtered HTML code
*/
function bbcode_cleanHTML($str) {
    global $_FF_CONF, $_CONF;

    $filter = sanitizer::getInstance();
    $AllowedElements = $filter->makeAllowedElements($_FF_CONF['allowed_html']);
    $filter->setAllowedelements($AllowedElements);
    $filter->setNamespace('forum','post');
    $filter->setPostmode('html');

    return $filter->filterHTML($str);
}

/* for display */
function FF_formatTextBlock($str,$postmode='html',$mode='',$status = 0) {
    global $_CONF, $_FF_CONF, $_ff_pm;

    $bbcode = new StringParser_BBCode ();
    $bbcode->setGlobalCaseSensitive (false);
    $filter = sanitizer::getInstance();

    $status = (int) $status;

    if ($postmode == 'text' ) {
        $_ff_pm = 'text';
    } else {
        $_ff_pm = 'html';
    }
    $filter->setPostmode($postmode);
    if ( $postmode == 'text') {
        $bbcode->addParser (array ('block', 'inline', 'link', 'listitem'), 'bbcode_htmlspecialchars');
    }
    if ( $_FF_CONF['use_glfilter'] == 1 && ($postmode == 'html' || $postmode == 'HTML')) {
        $str = str_replace('<pre>','[code]',$str);
        $str = str_replace('</pre>','[/code]',$str);
    }
    if ( $postmode != 'html' && $postmode != 'HTML') {
        $bbcode->addParser(array('block','inline','link','listitem'), 'nl2br');
    }

//    $bbcode->addParser(array('block','inline','link','listitem'), '_ff_fixtemplate');

    if ( ! ( $status & DISABLE_BBCODE ) ) {

        $bbcode->addParser ('list', 'bbcode_stripcontents');
        $bbcode->addCode ('code', 'usecontent', 'do_bbcode_code', array ('usecontent_param' => 'default'),
                          'code', array ('listitem', 'block', 'inline', 'link'), array ());

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
        if ($mode != 'noquote' ) {
            $bbcode->addCode ('quote','simple_replace',null,array('start_tag' => '</p><blockquote>', 'end_tag' => '</blockquote><p>'),
                              'inline', array('listitem','block','inline','link'), array());
//            $bbcode->addCode ('quote','simple_replace',null,array('start_tag' => '<blockquote>', 'end_tag' => '</blockquote>'),
//                              'inline', array('listitem','block','inline','link'), array());
        }
        $bbcode->addCode ('url', 'usecontent?', 'do_bbcode_url', array ('usecontent_param' => 'default'),
                          'link', array ('listitem', 'block', 'inline'), array ('link'));
        $bbcode->addCode ('img', 'usecontent', 'do_bbcode_img', array (),
                          'image', array ('listitem', 'block', 'inline', 'link'), array ());
        $bbcode->addCode ('file', 'usecontent', 'do_bbcode_file', array (),
                          'image', array ('listitem', 'block', 'inline', 'link'), array ());
        $bbcode->setCodeFlag ('quote', 'paragraph_type', BBCODE_PARAGRAPH_ALLOW_INSIDE);
        $bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
        $bbcode->setCodeFlag ('*', 'paragraphs', true);
        $bbcode->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
        $bbcode->setCodeFlag ('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
    }
    $bbcode->addParser(array('block','inline','link','listitem'), '_ff_replacetags');

    if ( ! ($status & DISABLE_SMILIES ) ) {
        $bbcode->addParser(array('block','inline','link','listitem'), '_ff_replacesmilie');      // calls replacesmilie on all text blocks
    }

    $bbcode->setRootParagraphHandling (true);

    if ($_FF_CONF['use_censor']) { // and $mode == 'preview') {
        $str = COM_checkWords($str);
    }
    $str = $bbcode->parse ($str);

    if ( ! ($status & DISABLE_URLPARSE ) ) {
        $str = $filter->linkify($str);
    }

    return $str;
}



function FF_getSignature( $tagline, $signature, $postmode = 'html'  )
{
    global $_CONF, $_FF_CONF, $_TABLES;

    USES_lib_bbcode();

    $retval = '';
    $sig    = '';

    if ( $_FF_CONF['bbcode_signature'] && $signature != '') {
        $retval = '<div class="signature">'.BBC_formatTextBlock( $signature, 'text').'</div><div style="clear:both;"></div>';
    } else {
        if (!empty ($tagline)) {
            if ( $postmode == 'html' ) {
                $retval = nl2br($tagline);
            } else {
                $retval = nl2br($tagline);
            }
            $retval = '<strong>'.$retval.'</strong>';
        }
    }

    return $retval;
}

function _ff_geshi_formatted($str,$type='PHP') {
    global $_CONF;

    include_once($_CONF['path'].'lib/geshi/geshi.php');
    $geshi = new Geshi($str,$type,"{$_CONF['path']}lib/geshi");
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

function _ff_FormatForEmail( $str, $postmode='html' ) {
    global $_CONF, $_FF_CONF;

    $_FF_CONF['use_geshi']     = true;
    $_FF_CONF['allow_smilies'] = false;
    $str = FF_formatTextBlock($str,$postmode,'text');

    $str = str_replace('<img src="' . $_CONF['site_url'] . '/forum/images/img_quote.gif" alt=""/>','',$str);

    // we don't have a stylesheet for email, so replace our div with the style...
    $str = str_replace('<div class="quotemain">','<div style="border: 1px dotted #000;border-left: 4px solid #8394B2;color:#465584;  padding: 4px;  margin: 5px auto 8px auto;">',$str);
    return $str;
}

function gfm_getoutput( $id ) {
    global $_TABLES,$LANG_GF01,$LANG_GF02,$_CONF,$_FF_CONF,$_USER;

    $dt = new Date('now',$_USER['tzid']);

    $id = COM_applyFilter($id,true);
    $result = DB_query("SELECT * FROM {$_TABLES['ff_topic']} WHERE id=".(int) $id);
    $A = DB_fetchArray($result);

    if ( $A['pid'] == 0 ) {
        $pid = $id;
    } else {
        $pid = $A['pid'];
    }
    $permalink = $_CONF['site_url'].'/forum/viewtopic.php?topic='.$id.'#'.$id;
    $A['name'] = COM_checkWords($A['name']);
    $A['name'] = @htmlspecialchars($A['name'],ENT_QUOTES, COM_getEncodingt());
    $A['subject'] = COM_checkWords($A['subject']);
    $A['subject'] = @htmlspecialchars($A["subject"],ENT_QUOTES, COM_getEncodingt());
    $A['comment'] = _ff_FormatForEmail( $A['comment'], $A['postmode'] );
    $notifymsg = sprintf($LANG_GF02['msg27'],'<a href="'.$_CONF['site_url'].'/forum/notify.php">'.$_CONF['site_url'].'/forum/notify.php</a>');
    $dt->setTimestamp($A['date']);
    $date = $dt->format('F d Y @ h:i a');
    if ($A['pid'] == '0') {
        $postid = $A['id'];
    } else {
        $postid = $A['pid'];
    }
    $T = new Template($_CONF['path'] . 'plugins/forum/templates');
    $T->set_file ('email', 'notifymessage.thtml');

    $T->set_var(array(
        'post_id'       => $postid,
        'topic_id'      => $A['id'],
        'post_subject'  => $A['subject'],
        'post_date'     => $date,
        'post_name'     => $A['name'],
        'post_comment'  => $A['comment'],
        'notify_msg'    => $notifymsg,
        'site_name'     => $_CONF['site_name'],
        'online_version' => sprintf($LANG_GF02['view_online'],$permalink),
        'permalink'     => $permalink,
    ));
    $T->parse('output','email');
    $message = $T->finish($T->get_var('output'));

    $T = new Template($_CONF['path'] . 'plugins/forum/templates');
    $T->set_file ('email', 'notifymessage_text.thtml');

    $T->set_var(array(
        'post_id'       => $postid,
        'topic_id'      => $A['id'],
        'post_subject'  => $A['subject'],
        'post_date'     => $date,
        'post_name'     => $A['name'],
        'post_comment'  => $A['comment'],
        'notify_msg'    => $notifymsg,
        'site_name'     => $_CONF['site_name'],
        'online_version' => sprintf($LANG_GF02['view_online'],$_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$postid.'&lastpost=true#'.$A['id']),
    ));
    $T->parse('output','email');
    $msgText = $T->finish($T->get_var('output'));

    $html2txt = new html2text($msgText,false);

    $messageText = $html2txt->get_text();
    return array($message,$messageText);
}

function _ff_checkHTMLforSQL($str,$postmode='html') {
    global $_FF_CONF;

    $bbcode = new StringParser_BBCode ();
    $bbcode->setGlobalCaseSensitive (false);

    if ( $postmode == 'html' || $postmode == 'HTML') {
        $bbcode->addParser(array('block','inline'), '_ff_cleanHTML');
    }
    $bbcode->addCode ('code', 'simple_replace', null, array ('start_tag' => '[code]', 'end_tag' => '[/code]'),
                      'code', array ('listitem', 'block', 'inline', 'link'), array ());
    $str = $bbcode->parse ($str);
    return $str;
}

/**
* Cleans (filters) HTML - only allows HTML tags specified in the
* $_FF_CONF['allowed_html'] string.  This function is designed to be called
* by the stringparser class to filter everything except [code] blocks.
*
* @param        string      $message        The topic post to filter
* @return       string      filtered HTML code
*/
function _ff_cleanHTML($message) {
    global $_CONF, $_FF_CONF;

    $filter = sanitizer::getInstance();
    $AllowedElements = $filter->makeAllowedElements($_FF_CONF['allowed_html']);
    $filter->setAllowedelements($AllowedElements);
    $filter->setNamespace('forum','post');
    $filter->setPostmode('html');
    return $filter->filterHTML($message);
}

function _ff_fixtemplate($text) {
    $text = str_replace('{','&#123;',$text);
    $text = str_replace('}','&#125;',$text);

    return $text;
}

function _ff_replaceTags($text) {
    return PLG_replaceTags($text,'forum','post');
}

function _ff_preparefordb($message,$postmode) {
    global $_FF_CONF, $_CONF;

    if ( $postmode == 'html' || $postmode == 'HTML' ) {
        $message = _ff_checkHTMLforSQL($message,$postmode);
    }

    if ($_FF_CONF['use_censor']) {
        $message = COM_checkWords($message);
    }

    $message = DB_escapeString($message);
    return $message;
}

function _ff_replacesmilie($str) {
    global $_CONF,$_TABLES,$_FF_CONF;

    if($_FF_CONF['allow_smilies']) {
        if (function_exists('msg_showsmilies') AND $_FF_CONF['use_smilies_plugin']) {
            $str = msg_replaceEmoticons($str);
        } else {
            $str = forum_xchsmilies($str);
        }
    }

    return $str;
}

function _ff_showattachments($topic,$mode='') {
    global $_TABLES,$_CONF,$_FF_CONF;

    $retval = '';
    $sql = "SELECT id,repository_id,filename FROM {$_TABLES['ff_attachments']} WHERE topic_id=".(int) $topic." ";
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

    while (list($id,$lid,$field_value) =  DB_fetchArray($query)) {
        $retval .= '<div class="tblforumfile">';
        $filename = explode(':',$field_value);
        if ($filemgmtSupport AND $lid > 0) {   // Check and see if user has access to file
            $groupsql = filemgmt_buildAccessSql();
            $sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_filedetail']} a ";
            $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
            $sql .= "WHERE a.lid='$lid' $groupsql";
            list($testaccess_cnt) = DB_fetchArray(DB_query($sql));
        }
        if ($lid > 0 AND (!$filemgmtSupport OR $testaccess_cnt == 0 OR DB_count($_TABLES['filemgmt_filedetail'],"lid",$lid ) == 0)) {
            $retval .= "<img src=\"{$_CONF['site_url']}/forum/images/document_sm.gif\" border=\"0\" alt=\"\"/>Insufficent Access";
        } elseif (!empty($field_value)) {
            $retval .= "<img src=\"{$_CONF['site_url']}/forum/images/document_sm.gif\" border=\"0\" alt=\"\"/>";
            $retval .= "<a href=\"{$_CONF['site_url']}/forum/getattachment.php?id=$id\" target=\"_new\">";
            $retval .= "{$filename[1]}</a>&nbsp;";
            if ($mode == 'edit') {
                $retval .= "<a href=\"#\" onclick='ajaxDeleteFile($topic,$id);'>";
                $retval .= "<img src=\"{$_CONF['site_url']}/forum/images/delete.gif\" border=\"0\" alt=\"\"/></a>";
            }
        } else {
            $retval .= 'N/A&nbsp;';
        }
        $retval .= '</div>';
    }
    return $retval;
}


// +--------------------------------------------------------------------------+
// | The following functions are from the phpBB3 Open Source Project          |
// | Copyright (C) 2005-2010 by the phpBB Group                               |
// +--------------------------------------------------------------------------+

/**
* A subroutine of make_clickable used with preg_replace
* It places correct HTML around an url, shortens the displayed text
* and makes sure no entities are inside URLs
*/
function make_clickable_callback($type, $whitespace, $url, $relative_url, $class)
{
    $orig_url       = $url;
    $orig_relative  = $relative_url;
    $append         = '';
    $url            = htmlspecialchars_decode($url);
    $relative_url   = htmlspecialchars_decode($relative_url);

    // make sure no HTML entities were matched
    $chars = array('<', '>', '"');
    $split = false;

    foreach ($chars as $char) {
        $next_split = strpos($url, $char);
        if ($next_split !== false) {
            $split = ($split !== false) ? min($split, $next_split) : $next_split;
        }
    }

    if ($split !== false) {
        // an HTML entity was found, so the URL has to end before it
        $append         = substr($url, $split) . $relative_url;
        $url            = substr($url, 0, $split);
        $relative_url   = '';
    } else if ($relative_url) {
        // same for $relative_url
        $split = false;
        foreach ($chars as $char) {
            $next_split = strpos($relative_url, $char);
            if ($next_split !== false) {
                $split = ($split !== false) ? min($split, $next_split) : $next_split;
            }
        }

        if ($split !== false) {
            $append         = substr($relative_url, $split);
            $relative_url   = substr($relative_url, 0, $split);
        }
    }

    // if the last character of the url is a punctuation mark, exclude it from the url
    $last_char = ($relative_url) ? $relative_url[strlen($relative_url) - 1] : $url[strlen($url) - 1];

    switch ($last_char) {
        case '.':
        case '?':
        case '!':
        case ':':
        case ',':
            $append = $last_char;
            if ($relative_url) {
                $relative_url = substr($relative_url, 0, -1);
            } else {
                $url = substr($url, 0, -1);
            }
        break;

        // set last_char to empty here, so the variable can be used later to
        // check whether a character was removed
        default:
            $last_char = '';
        break;
    }

    $short_url = (strlen($url) > 55) ? substr($url, 0, 39) . ' ... ' . substr($url, -10) : $url;

    switch ($type) {
        case MAGIC_URL_LOCAL:
            $tag            = 'l';
            $relative_url   = preg_replace('/[&?]sid=[0-9a-f]{32}$/', '', preg_replace('/([&?])sid=[0-9a-f]{32}&/', '$1', $relative_url));
            $url            = $url . '/' . $relative_url;
            $text           = $relative_url;

            // this url goes to http://domain.tld/path/to/board/ which
            // would result in an empty link if treated as local so
            // don't touch it and let MAGIC_URL_FULL take care of it.
            if (!$relative_url) {
                return $whitespace . $orig_url . '/' . $orig_relative; // slash is taken away by relative url pattern
            }
        break;

        case MAGIC_URL_FULL:
            $tag    = 'm';
            $text   = $short_url;
        break;

        case MAGIC_URL_WWW:
            $tag    = 'w';
            $url    = 'http://' . $url;
            $text   = $short_url;
        break;

        case MAGIC_URL_EMAIL:
            $tag    = 'e';
            $text   = $short_url;
            $url    = 'mailto:' . $url;
        break;
    }

    $url    = htmlspecialchars($url);
    $text   = htmlspecialchars($text);
    $append = htmlspecialchars($append);

    $html   = "$whitespace<a$class href=\"$url\" rel=\"nofollow\">$text</a>$append";

    return $html;
}
//@TODO remove
/**
* make_clickable function
*
* Replace magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
* Cuts down displayed size of link if over 50 chars, turns absolute links
* into relative versions when the server/script path matches the link
*/
function make_clickableXXX($text, $server_url = false, $class = 'postlink')
{
    global $_CONF;

    if ($server_url === false) {
        $server_url = $_CONF['site_url'];;
    }

    static $magic_url_match;
    static $magic_url_replace;
    static $static_class;

    if (!is_array($magic_url_match) || $static_class != $class) {
        $static_class = $class;
        $class = ($static_class) ? ' class="' . $static_class . '"' : '';
        $local_class = ($static_class) ? ' class="' . $static_class . '-local"' : '';

        $magic_url_match = $magic_url_replace = array();
        // Be sure to not let the matches cross over. ;)

        // relative urls for this board
        $magic_url_match[] = '#(^|[\n\t (>.])(' . preg_quote($server_url, '#') . ')/(' . get_preg_expression('relative_url_inline') . ')#ie';
        $magic_url_replace[] = "make_clickable_callback(MAGIC_URL_LOCAL, '\$1', '\$2', '\$3', '$local_class')";

        // matches a xxxx://aaaaa.bbb.cccc. ...
        $magic_url_match[] = '#(^|[\n\t (>.])(' . get_preg_expression('url_inline') . ')#ie';
        $magic_url_replace[] = "make_clickable_callback(MAGIC_URL_FULL, '\$1', '\$2', '', '$class')";

        // matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
        $magic_url_match[] = '#(^|[\n\t (>])(' . get_preg_expression('www_url_inline') . ')#ie';
        $magic_url_replace[] = "make_clickable_callback(MAGIC_URL_WWW, '\$1', '\$2', '', '$class')";

        // matches an email@domain type address at the start of a line, or after a space or after what might be a BBCode.
        $magic_url_match[] = '/(^|[\n\t (>])(' . get_preg_expression('email') . ')/ie';
        $magic_url_replace[] = "make_clickable_callback(MAGIC_URL_EMAIL, '\$1', '\$2', '', '')";
    }

    return preg_replace($magic_url_match, $magic_url_replace, $text);
}

/**
* This function returns a regular expression pattern for commonly used expressions
* Use with / as delimiter for email mode and # for url modes
* mode can be: email|bbcode_htm|url|url_inline|www_url|www_url_inline|relative_url|relative_url_inline|ipv4|ipv6
*/
function get_preg_expression($mode)
{
	switch ($mode)
	{
		case 'email':
			return '(?:[a-z0-9\'\.\-_\+\|]++|&amp;)+@[a-z0-9\-]+\.(?:[a-z0-9\-]+\.)*[a-z]+';
		break;

		case 'bbcode_htm':
			return array(
				'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
				'#<!\-\- l \-\-><a (?:class="[\w-]+" )?href="(.*?)(?:(&amp;|\?)sid=[0-9a-f]{32})?">.*?</a><!\-\- l \-\->#',
				'#<!\-\- ([mw]) \-\-><a (?:class="[\w-]+" )?href="(.*?)">.*?</a><!\-\- \1 \-\->#',
				'#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
				'#<!\-\- .*? \-\->#s',
				'#<.*?>#s',
			);
		break;

		// Whoa these look impressive!
		// The code to generate the following two regular expressions which match valid IPv4/IPv6 addresses
		// can be found in the develop directory
		case 'ipv4':
			return '#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#';
		break;

		case 'ipv6':
			return '#^(?:(?:(?:[\dA-F]{1,4}:){6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){5}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:))$#i';
		break;

		case 'url':
		case 'url_inline':
			$inline = ($mode == 'url') ? ')' : '';
			$scheme = ($mode == 'url') ? '[a-z\d+\-.]' : '[a-z\d+]'; // avoid automatic parsing of "word" in "last word.http://..."
			// generated with regex generation file in the develop folder
			return "[a-z]$scheme*:/{2}(?:(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'www_url':
		case 'www_url_inline':
			$inline = ($mode == 'www_url') ? ')' : '';
			return "www\.(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})+(?::\d*)?(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'relative_url':
		case 'relative_url_inline':
			$inline = ($mode == 'relative_url') ? ')' : '';
			return "(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;
	}

	return '';
}

function forum_pagination( $base_url, $curpage, $num_pages,
                                  $page_str='page=', $do_rewrite=false, $msg='',
                                  $open_ended = '',$suffix='')
{
    global $_CONF, $LANG05;

    $retval = '';

    $output = outputHandler::getInstance();

    if ( $num_pages < 2 ) {
        return $retval;
    }

    $T = new Template($_CONF['path'] . 'plugins/forum/templates');
    $T->set_file('pagination','pagination.thtml');

    if ( !$do_rewrite ) {
        $hasargs = strstr( $base_url, '?' );
        if ( $hasargs ) {
            $sep = '&amp;';
        } else {
            $sep = '?';
        }
    } else {
        $sep = '/';
        $page_str = '';
    }

    if ( $curpage > 1 ) {
        $T->set_var('first',true);
        $T->set_var('first_link',$base_url . $sep . $page_str . '1' . $suffix);
        $pg = $sep . $page_str . ( $curpage - 1 );
        $T->set_var('prev',true);
        $T->set_var('prev_link',$base_url . $pg . $suffix);
        $output->addLink('prev', urldecode($base_url . $pg . $suffix));
    } else {
        $T->unset_var('first');
        $T->unset_var('first_link');
        $T->unset_var('prev');
        $T->unset_var('prev_link');
    }

    $T->set_block('pagination', 'datarow', 'datavar');

    if ( $curpage == 1 ) {
        $T->set_var('page_str','1');
        $T->set_var('page_link','#');
        $T->set_var('disabled',true);
        $T->set_var('active',true);
        $T->parse('datavar', 'datarow',true);
        $T->unset_var('active');
        $T->unset_var('disabled');
    } else {
        $T->set_var('page_str','1');
        $pg = $sep . $page_str . 1;
        $T->set_var('page_link',$base_url . $pg . $suffix);
        $T->parse('datavar', 'datarow',true);
    }

    if ( $num_pages > 5 ) {
        $start_cnt = min(max(1, $curpage - 4), $num_pages - 5);
        $end_cnt = max(min($num_pages,$curpage + 2), 6);
        if ( $start_cnt > 1 ) {
            $T->set_var('page_str','...');
            $T->set_var('page_link','#');
            $T->set_var('disabled',true);
            $T->parse('datavar', 'datarow',true);
        }

        for ( $i = ($start_cnt + 1); $i < $end_cnt; $i++ ) {
            if ( $i == $curpage ) {
                $T->set_var('page_str',$i);
                $T->set_var('page_link','#');
                $T->set_var('disabled',true);
                $T->set_var('active',true);
            } else {
                $T->set_var('page_str',$i);
                $pg = $sep . $page_str . $i;
                $T->set_var('page_link',$base_url . $pg . $suffix);
            }
            $T->parse('datavar', 'datarow',true);
            $T->unset_var('active');
            $T->unset_var('disabled');
        }
        if ( $end_cnt < $num_pages ) {
            $T->set_var('page_str','...');
            $T->set_var('page_link','#');
            $T->set_var('disabled',true);
            $T->parse('datavar', 'datarow',true);
        }
        if ( $curpage == $num_pages ) {
            $T->set_var('page_str',$num_pages);
            $T->set_var('page_link','#');
            $T->set_var('active',true);
        } else {
            $T->set_var('page_str',$num_pages);
            $pg = $sep . $page_str . $num_pages;
            $T->set_var('page_link',$base_url . $pg . $suffix);
        }
        $T->parse('datavar', 'datarow',true);
    } else {
        for( $pgcount = ( $curpage - 10 ); ( $pgcount <= ( $curpage + 9 )) AND ( $pgcount <= $num_pages ); $pgcount++ ) {
            if ( $pgcount <= 0 ) {
                $pgcount = 2;
            }
            if ( $pgcount == $curpage ) {
                $T->set_var('active',true);
                $T->set_var('page_str',$curpage);
            } else {
                $T->unset_var('active');
                $T->set_var('page_str',$pgcount);
                $pg = $sep . $page_str . $pgcount;
                $T->set_var('page_link',$base_url . $pg . $suffix);
            }
            $T->parse('datavar', 'datarow',true);
        }
    }
    if ( !empty( $open_ended )) {
        $T->set_var('open_ended',true);
    } else if ( $curpage == $num_pages ) {
        $T->unset_var('open_ended');
        $T->unset_var('next');
        $T->unset_var('last');
        $T->unset_var('next_link');
        $T->unset_var('last_link');
    } else {
        $T->set_var('next',true);
        $T->set_var('next_link',$base_url . $sep.$page_str . ($curpage + 1) . $suffix);
        $T->set_var('last',true);
        $T->set_var('last_link',$base_url . $sep.$page_str . $num_pages . $suffix);
        $output->addLink('next', urldecode($base_url . $sep. $page_str . ($curpage + 1) . $suffix));
    }
    if (!empty($msg) ) {
        $T->set_var('msg',$msg);
    }

    $retval = $T->finish ($T->parse('output','pagination'));
    return $retval;
}
?>