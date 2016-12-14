<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-bbcode.php                                                           |
// |                                                                          |
// | glFusion bbcode processing library                                       |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006-2015 by the following authors:                        |
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
    require_once $_CONF['path'].'lib/bbcode/stringparser_bbcode.class.php';
}

/**
 * Parse text block and interpret BBcodes
 *
 * @param   string  $str        text to parse
 * @param   string  $postmode   Either html or text
 * @param   array   $parser     Additional parsers for the bbcode interpreter
 * @param   array   $code       Additional bbcodes
 * @return  string              the formatted string
 */
function FUSION_formatTextBlock( $str, $postmode='html', $parser = array(), $code = array() )
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

    // add a parser to handle the [imageX_????] tags

    $bbcode->addParser(array('block','inline','link','listitem'), '_bcode_replacetags');

    $bbcode->addParser ('list', '_bbcode_stripcontents');

    if ( is_array($parser) && count($parser) > 0 ) {
        foreach ($parser AS $extraparser) {
            if ( isset($extraparser[0]) ) {
                $parm1 = $extraparser[0];
            } else {
                $parm1 = '';
            }
            if ( isset($extraparser[1]) ) {
                $parm2 = $extraparser[1];
            } else {
                $parm2 = '';
            }
            $bbcode->addParser($parm1,$parm2);
        }
    }

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

/**
 * Parse text block and interpret BBcodes
 *
 * @param   string  $str        text to parse
 * @param   string  $postmode   Either html or text
 * @param   array   $parser     Additional parsers for the bbcode interpreter
 * @param   array   $code       Additional bbcodes
 * @return  string              the formatted string
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

    $bbcode->addParser(array('block','inline','link','listitem'), '_bcode_replacetags');

    $bbcode->addParser ('list', '_bbcode_stripcontents');

    if ( is_array($parser) && count($parser) > 0 ) {
        foreach ($parser AS $extraparser) {
            if ( isset($extraparser[0]) ) {
                $parm1 = $extraparser[0];
            } else {
                $parm1 = '';
            }
            if ( isset($extraparser[1]) ) {
                $parm2 = $extraparser[1];
            } else {
                $parm2 = '';
            }
            $bbcode->addParser($parm1,$parm2); // $extraparser[0],$extraparser[1]);
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
    $bbcode->addParser(array('block','inline','link','listitem'), '_bbcode_replacetags');

    $bbcode->setRootParagraphHandling (true);

    if ($_CONF['censormode']) {
        $str = COM_checkWords($str);
    }
    $str = $bbcode->parse ($str);

    return $str;
}


/**
 * BBCode editor
 *
 * @param   string  $editorText      Text being edited
 * @param   string  $formName        Name of the form containing the bbcode editor
 * @param   string  $textName        Name of the textarea field
 * @param   array   $additionalCodes Additional BBcodes
 * @param   array   $disabledCode    array of bbcodes to disable
 * @return  string                   complete HTML for the editor
 */
function BBC_editor($editorText,$formName,$textName,$additionalCodes = array(),$disabledCodes = array())
{
    global $_CONF, $LANG_BBCODE;

    $retval = '';
    $smileys = '';

    $standardCodes = array();
    $standardCodes = array(
                     array('name'=>'bold','label' => $LANG_BBCODE['bold_label'],'help' => $LANG_BBCODE['bold_help'],'start_tag' => '[b]','end_tag'=>'[/b]','select'=>''),
                     array('name'=>'italic','label' => $LANG_BBCODE['italic_label'],'help' => $LANG_BBCODE['italic_help'],'start_tag'=>'[i]','end_tag'=>'[/i]','select'=>''),
                     array('name'=>'underline','label' => $LANG_BBCODE['underline_label'],'help' => $LANG_BBCODE['underline_help'],'start_tag'=>'[u]','end_tag'=>'[/u]','select'=>''),
                     array('name'=>'quote','label' => $LANG_BBCODE['quote_label'],'help' => $LANG_BBCODE['quote_help'],'start_tag'=>'[quote]','end_tag'=>'[/quote]','select'=>''),
                     array('name'=>'code','label' => $LANG_BBCODE['code_label'], 'help' => $LANG_BBCODE['code_help'],'start_tag'=>'[code]','end_tag'=>'[/code]','select'=>''),
                     array('name'=>'list','label' => $LANG_BBCODE['list_label'],'help' => $LANG_BBCODE['list_help'],'start_tag'=>'[list]','end_tag'=>'[/list]','select'=>''),
                     array('name'=>'olist','label' => $LANG_BBCODE['olist_label'],'help' => $LANG_BBCODE['olist_help'],'start_tag'=>'[list=]','end_tag'=>'[/list]','select'=>''),
                     array('name'=>'listitem','label' => $LANG_BBCODE['listitem_label'], 'help' => $LANG_BBCODE['listitem_help'],'start_tag'=>'[*]','end_tag'=>'[/*]','select'=>''),
                     array('name'=>'img','label' => $LANG_BBCODE['img_label'],'help' => $LANG_BBCODE['img_help'],'start_tag'=>'[img]','end_tag'=>'[/img]','select'=>''),
                     array('name'=>'url','label' => $LANG_BBCODE['url_label'],'help' => $LANG_BBCODE['url_help'],'start_tag'=>'[url]','end_tag'=>'[/url]','select'=>''),
                     array('name'=>'smileys','label' => $LANG_BBCODE['smiley_label'],'help' => $LANG_BBCODE['smiley_help'],'start_tag'=>'smileys','end_tag'=>'','select'=>''),
                     array('name'=>'size','label' => $LANG_BBCODE['size_label'],'help' => $LANG_BBCODE['size_help'],'start_tag'=>'[size=]','end_tag'=>'[/size]','select'=>array('7'=>$LANG_BBCODE['size_tiny'],'9'=>$LANG_BBCODE['size_small'],'12'=>'*'.$LANG_BBCODE['size_normal'],'18'=>$LANG_BBCODE['size_large'],'24'=>$LANG_BBCODE['size_huge']) ),
                     array('name'=>'color','label'=> $LANG_BBCODE['color_label'],'help' => $LANG_BBCODE['color_help'],'start_tag'=>'[color=]','end_tag'=>'[/color]','select'=>array('#'=>'*'.$LANG_BBCODE['color_default'],'darkred'=>$LANG_BBCODE['color_darkred'],'red'=>$LANG_BBCODE['color_red'],'orange'=>$LANG_BBCODE['color_orange'],'brown'=>$LANG_BBCODE['color_brown'],'yellow'=>$LANG_BBCODE['color_yellow'],'green'=>$LANG_BBCODE['color_green'],'olive'=>$LANG_BBCODE['color_olive'],'cyan'=>$LANG_BBCODE['color_cyan'],'blue'=>$LANG_BBCODE['color_blue'],'darkblue'=>$LANG_BBCODE['color_darkblue'],'indigo'=>$LANG_BBCODE['color_indigo'],'violet'=>$LANG_BBCODE['color_violet'],'white'=>$LANG_BBCODE['color_white'],'black'=>$LANG_BBCODE['color_black']) ),
                    );

    $T = new Template($_CONF['path_layout'] . 'bbcode/','keep');
    $T->set_file (array (
        'editor'    =>  'editor.thtml',
    ));


    $offset = 0;
    $bbtags = 'var bbtags = new Array(';
    $buttons = array();
    $first = 1;
    if ( is_array( $standardCodes ) ) {
        foreach( $standardCodes AS $bbcode ) {
            if (in_array($bbcode['name'],$disabledCodes) === TRUE ) {
                continue;
            }
            if ( $first == 1 ) {
                $first = 0;
            } else {
                $bbtags .= ',';
            }
            $bbtags .= "'".$bbcode['start_tag']."','".$bbcode['end_tag']."'";

            $buttons[] = array($bbcode['label'],$bbcode['help'],$offset,$bbcode['select'],$bbcode['start_tag'],$bbcode['end_tag']);
            $offset = $offset + 2;
        }
    }
    if ( is_array($additionalCodes) ) {
        foreach($additionalCodes AS $bbcode ) {
            if (in_array($bbcode['name'],$disabledCodes) === TRUE ) {
                continue;
            }
            if ( $first == 1 ) {
                $first = 0;
            } else {
                $bbtags .= ',';
            }
            $bbtags .= "'".$bbcode['start_tag']."','".$bbcode['end_tag']."'";
            $buttons[] = array($bbcode['label'],$bbcode['help'],$offset,$bbcode['select'],$bbcode['start_tag'],$bbcode['end_tag']);
            $offset = $offset + 2;
        }
    }
    $bbtags .= ');';

    $buttonText = '';
    $buttonSelectText = '';
    if ( is_array($buttons) ) {
        foreach( $buttons AS $button ) {
            if (is_array($button[3]) ) {
                $selectText = '';
                $selected = 0;
                $indexoffset = 0;
                $selectoffset = 0;
                foreach ($button[3] AS $value => $option ) {
                    if ( substr($option,0,1) == "*" ) {
                        $option = substr($option,1);
                        $selected = 1;
                        $selectoffset = $indexoffset;
                    } else {
                        $selected = 0;
                    }
                    $selectText .= '<option value="'.$value.'"'. ($selected == 1 ? ' selected="selected"' : '') .'>'.$option.'</option>' . LB;
                    $indexoffset++;
                }
                $start_tag = substr($button[4],0,-1);
                $end_tag   = $button[5];

                $buttonSelectText .= '<span style="white-space:nowrap;">'.$button[0] . ':&nbsp;';
                $buttonSelectText .= '<select  name="bbcodesel'.$button[2].'" id="bbcodesel'.$button[2].'" title="'.$button[1].'" onchange="bbfontstyle(\''.$start_tag.'\' + this.form.bbcodesel'.$button[2].'.options[this.form.bbcodesel'.$button[2].'.selectedIndex].value + \']\', \''.$end_tag.'\');this.form.bbcodesel'.$button[2].'.selectedIndex = '.$selectoffset.';">' . LB;
                $buttonSelectText .= $selectText;
                $buttonSelectText .= '</select></span>' . LB;
            } else {
                if ($button[4] == 'smileys' ) {
                    if ( function_exists('msg_showsmilies') ) {
                        $buttonText .= '<input class="button2" type="button" title="'.$LANG_BBCODE['smiley_help'].'" style="text-decoration: underline; width: 40px;" value="'.$LANG_BBCODE['smiley_label'].'" name="toggleV" id="toggleV" accesskey="s"/>'.LB;
                        $smileys = msg_showsmilies();
                    }
                } else {
                    $buttonText .= '<input class="button2" type="button" title="'.$button[1].'" onclick="bbstyle('.$button[2].')" style="width: 30px;" value=" '.$button[0].' " name="addbbcode'.$button[2].'" accesskey="b"/>' . LB;
                }
            }
        }
    }

    $buttonText = $buttonText . $buttonSelectText;


    $T->set_var('tags', $bbtags);
    $T->set_var('buttons',$buttonText);
    $T->set_var(array(
        'form_name'   => $formName,
        'text_name'   => $textName,
        'bbocode_text'=> htmlentities($editorText, ENT_QUOTES, COM_getEncodingt()),
        'smileys'     => $smileys,
    ));

    $T->parse ('output', 'editor');
    $retval .= $T->finish ($T->get_var('output'));

    return $retval;
}

function _bbcode_stripcontents ($text) {
    return preg_replace ("/[^\n]/", '', $text);
}

function _bbcode_htmlspecialchars($text) {

    return (@htmlspecialchars ($text,ENT_QUOTES, COM_getEncodingt()));
}

function _bbcode_url ($action, $attributes, $content, $params, $node_object) {
    global $_CONF;

    if ($action == 'validate') {
        return true;
    }
    if ( stristr($content,'http') || stristr($content,'mailto') ) {
        $content = _bbcode_cleanHTML($content);
    } else {
        $content = _bbcode_htmlspecialchars($content);
    }

    if (!isset ($attributes['default'])) {
        if ( stristr($content,'http') || stristr($content,'mailto') ) {
            $url = _bbcode_cleanHTML($content);
        } else {
            $url = 'http://'._bbcode_cleanHTML($content);
        }
    }
    if ( stristr($attributes['default'],'http') || stristr($attributes['default'],'mailto') ) {
        $url = _bbcode_cleanHTML($attributes['default']);
    } else {
        $url = 'http://'._bbcode_cleanHTML($attributes['default']);
    }

    if ( isset($_CONF['open_ext_url_new_window']) && $_CONF['open_ext_url_new_window'] == true && stristr($url,$_CONF['site_url']) === false ) {
        $target = ' target="_blank" ';
    }
    return '<a href="'. $url .'" rel="nofollow"'.$target.'>'.$content.'</a>';
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
    if ( stristr($content,'http') || stristr($content,'mailto') ) {
        return '<img src="'._bbcode_cleanHTML($content).'" ' . $dim . $align . ' alt="" />';
    } else {
        return '<img src="'._bbcode_cleanHTML(_bbcode_htmlspecialchars($content)).'" ' . $dim . $align . ' alt="" />';
    }
}

function _bbcode_size  ($action, $attributes, $content, $params, $node_object) {
    if ( $action == 'validate') {
        return true;
    }
    return '<span style="font-size: '.(int) $attributes['default'].'px;">'.$content.'</span>';
}

function _bbcode_color  ($action, $attributes, $content, $params, $node_object) {
    if ( $action == 'validate') {
        return true;
    }
    return '<span style="color: '._bbcode_cleanHTML($attributes['default']).';">'.$content.'</span>';
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

/**
* Cleans (filters) HTML - only allows safe HTML tags
*
* @param        string      $str    string to filter
* @return       string      filtered HTML code
*/
function _bbcode_cleanHTML($str) {
    global $_CONF;

    require_once $_CONF['path'] . 'lib/htmLawed/htmLawed.php';
    $configArray = array('safe' => 1,
                         'balance'  => 1,
                         'valid_xhtml' => 1
                        );

    return htmLawed($str,$configArray);
}

function _bbcode_replaceTags($text) {
    return PLG_replaceTags($text,'bbcode','post');
}

function _geshi($str,$type='PHP') {
    global $_CONF, $LANG_BBCODE;

    include_once($_CONF['path'].'lib/geshi/geshi.php');

    $geshi = new Geshi($str,$type,$_CONF['path'].'lib/geshi');
    $geshi->set_header_type(GESHI_HEADER_DIV);
    $geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS, 5);
    $geshi->set_overall_style('font-size: 12px; color: #000066; border: 1px solid #d0d0d0; background-color: #FAFAFA;', true);
    // Note the use of set_code_style to revert colours...
    $geshi->set_line_style('font: normal normal 95% \'Courier New\', Courier, monospace; color: #003030;', 'font-weight: bold; color: #006060;', true);
    $geshi->set_code_style('color: #000020;', 'color: #000020;');
    $geshi->set_line_style('background: red;', true);
    $geshi->set_link_styles(GESHI_LINK, 'color: #000060;');
    $geshi->set_link_styles(GESHI_HOVER, 'background-color: #f0f000;');

    $geshi->set_header_content("$type ".$LANG_BBCODE['formatted_code']);
    $geshi->set_header_content_style('font-family: Verdana, Arial, sans-serif; color: #808080; font-size: 90%; font-weight: bold; background-color: #f0f0ff; border-bottom: 1px solid #d0d0d0; padding: 2px;');

    return $geshi->parse_code();
}
?>
