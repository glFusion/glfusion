<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-bbcode.php                                                           |
// |                                                                          |
// | glFusion bbcode processing library                                       |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006-2018 by the following authors:                        |
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

    $format = new glFusion\Formatter();
    $format->setNamespace('bbcode');
    $format->setAction('post');
    $format->setType($postmode);
    $format->enableCache(false);
    if ($_CONF['censormode']) {
        $format->setCensor(true);
    }
    $format->setParseAutoTags(true);
    $format->setProcessBBCode(false);
    $format->setProcessSmilies(true);

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
            $format->addFilter($parm1,$parm2);
        }
    }
    if ( is_array($code) && count($code) > 0 ) {
        foreach ($code AS $extracode) {
            $format->addCode(
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
    $str = $format->parse ($str);

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
function BBC_formatTextBlock( $str, $postmode='html', $parser = array(), $code = array(), $exclude = array() )
{
    global $_CONF;

    $postmode = strtolower($postmode);

    $format = new glFusion\Formatter();
    $format->setNamespace('bbcode');
    $format->setAction('post');
    $format->setType($postmode);
    $format->setProcessSmilies(true);
    $format->setParseAutoTags(true);
    $format->setProcessBBCode(true);
    if ($_CONF['censormode']) {
        $format->setCensor(true);
    }

    if ( in_array('img',$exclude ) ) {
        $format->setBbcodeBlackList(array('img'));
    }

    $format->enableCache(false);

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
            $format->addFilter($parm1,$parm2);
        }
    }
    if ( is_array($code) && count($code) > 0 ) {
        foreach ($code AS $extracode) {
            $format->addCode(
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
    $str = $format->parse ($str);

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
?>
