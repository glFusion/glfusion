<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-bbcode.php                                                           |
// |                                                                          |
// | glFusion bbcode processing library                                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006-2009 by the following authors:                        |
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
//

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

if (!class_exists('StringParser') ) {
    require_once $_CONF['path'] . 'lib/bbcode/stringparser_bbcode.class.php';
}

/**
 * BBC_formatTextBlock()
 */

function BBC_formatTextBlock( $str, $postmode='html', $parser = array(), $code = array() )
{
    global $_CONF;

    $postmode = strtolower($postmode);

    $bbcode = new StringParser_BBCode ();
    $bbcode->setGlobalCaseSensitive (false);

    if ( $postmode == 'text') {
        $bbcode->addParser (array ('block', 'inline', 'link', 'listitem'), '_bbcode_htmlspecialchars');
        $bbcode->addParser(array('block','inline','link','listitem'), 'nl2br');
    } else {
        $bbcode->addParser(array('block','inline','link','listitem'), 'COM_checkHTML');
    }

    $bbcode->addParser(array('block','inline','link','listitem'), 'PLG_replacetags');

    $bbcode->addParser ('list', '_bbcode_stripcontents');

    if ( is_array($parser) && count($parser) > 0 ) {
        foreach ($parser AS $extraparser) {
            $bbcode->addParser($extraparser[0],$extraparser[1]);
        }
    }

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
    $bbcode->addCode ('size', 'callback_replace', '_bbcode_size', array('usecontent_param' => 'default'),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('color', 'callback_replace', '_bbcode_color', array ('usercontent_param' => 'default'),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('list', 'callback_replace', '_bbcode_list', array ('usecontent_param' => 'default'),
                      'list', array ('inline','block', 'listitem'), array ());
    $bbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'),
                      'listitem', array ('list'), array ());
    $bbcode->addCode ('quote','simple_replace',null,array('start_tag' => '</p><div class="quotemain"><img src="' . $_CONF['site_url'] . '/forum/images/img_quote.gif" alt=""/>', 'end_tag' => '</div><p>'),
                      'inline', array('listitem','block','inline','link'), array());
    $bbcode->addCode ('url', 'usecontent?', '_bbcode_url', array ('usecontent_param' => 'default'),
                      'link', array ('listitem', 'block', 'inline'), array ('link'));
    $bbcode->addCode ('link', 'callback_replace_single', '_bbcode_url', array (),
                      'link', array ('listitem', 'block', 'inline'), array ('link'));
    $bbcode->addCode ('img', 'usecontent', '_bbcode_img', array (),
                      'image', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('code', 'usecontent', '_bbcode_code', array ('usecontent_param' => 'default'),
                      'code', array ('listitem', 'block', 'inline', 'link'), array ());

    if ( is_array($code) && count($code) > 0 ) {
        foreach ($code AS $extracode) {
            $bbcode->addCode(
                $extracode[0],
                $extracode[1],
                $extracode[2],
                $extracode[3],
                $extracode[4],
                $extracode[5],
                $extracode[6]
            );
        }
    }

    $bbcode->setCodeFlag ('quote', 'paragraph_type', BBCODE_PARAGRAPH_ALLOW_INSIDE);
    $bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
    $bbcode->setCodeFlag ('*', 'paragraphs', true);
    $bbcode->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
    $bbcode->setCodeFlag ('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);

    $bbcode->setRootParagraphHandling (true);

    if ($_CONF['censormode']) {
        $str = COM_checkWords($str);
    }
    $str = $bbcode->parse ($str);

    return $str;
}

function _bbcode_stripcontents ($text) {
    return preg_replace ("/[^\n]/", '', $text);
}

function _bbcode_htmlspecialchars($text) {

    return (@htmlspecialchars ($text,ENT_QUOTES, COM_getEncodingt()));
}

function _bbcode_url ($action, $attributes, $content, $params, $node_object) {

    if ($action == 'validate') {
        return true;
    }
    if (!isset ($attributes['default'])) {
        if ( stristr($content,'http') ) {
            return '<a href="'.$content.'">'.@htmlspecialchars ($content,ENT_QUOTES, COM_getEncodingt()).'</a>';
        } else {
            return '<a href="http://'.$content.'">'.@htmlspecialchars ($content,ENT_QUOTES, COM_getEncodingt()).'</a>';
        }
    }
    if ( stristr($attributes['default'],'http') ) {
        return '<a href="'.$attributes['default'].'">'.$content.'</a>';
    } else {
        return '<a href="http://'.$attributes['default'].'">'.$content.'</a>';
    }
}

function _bbcode_list ($action, $attributes, $content, $params, $node_object) {
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


function _bbcode_img ($action, $attributes, $content, $params, $node_object) {

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

    return '<img src="'.htmlspecialchars($content,ENT_QUOTES, COM_getEncodingt()).'" ' . $dim . $align . ' alt=""' . XHTML . '>';
}

function _bbcode_size  ($action, $attributes, $content, $params, $node_object) {
    if ( $action == 'validate') {
        return true;
    }
    return '<span style="font-size: '.$attributes['default'].'px;">'.$content.'</span>';
}

function _bbcode_color  ($action, $attributes, $content, $params, $node_object) {
    if ( $action == 'validate') {
        return true;
    }
    return '<span style="color: '.$attributes['default'].';">'.$content.'</span>';
}

function _bbcode_code($action, $attributes, $content, $params, $node_object) {

    if ( $action == 'validate') {
        return true;
    }

    /* Support for formatting various code types : [code=java] for example */
    if (!isset ($attributes['default'])) {
        $codeblock = '</p>' . _geshi($content) . '<p>';
    } else {
        $codeblock = '</p>' . _geshi($content,strtoupper($attributes['default'])) . '<p>';
    }

    $codeblock = str_replace('{','&#123;',$codeblock);
    $codeblock = str_replace('}','&#125;',$codeblock);

    return $codeblock;
}

function _geshi($str,$type='PHP') {
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
?>