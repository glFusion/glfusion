<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// |lib-widgets.php                                                           |
// |                                                                          |
// | A place for widget functions, mootools based or otherwise                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2014 by the following authors:                        |
// |                                                                          |
// | Joe Mucchiello         jmucchiello AT yahoo DOT com                      |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Eric Warren            eakwarren AT gmail DOT com                        |
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

/*
 * provides a generic interface to the widget library
 * to allow plugins to specify their own functions.
 */

function WIDGET_interface($function_name, $args='')
{
    $retval = '';

    $function = 'WIDGET_' . $function_name;
    if (function_exists($function)) {
        switch (count($args)) {
        case 0:
        case 1:
            $retval .= $function();
            break;
        case 2:
            $retval .= $function($args[1]);
            break;
        case 3:
            $retval .= $function($args[1], $args[2]);
            break;
        case 4:
            $retval .= $function($args[1], $args[2], $args[3]);
            break;
        case 5:
            $retval .= $function($args[1], $args[2], $args[3], $args[4]);
            break;
        case 6:
            $retval .= $function($args[1], $args[2], $args[3], $args[4], $args[5]);
            break;
        case 7:
            $retval .= $function($args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
            break;
        case 8:
            $retval .= $function($args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
            break;
        case 9:
            $retval .= $function($args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]);
            break;
        default:
            $retval .= $function($args);
            break;
        }
    }
    return $retval;
}


function WIDGET_autotranslations($header=0) {
    global $_CONF, $LANG_WIDGETS;

    $isoLang = array(
                 'ar' => 'Arabic',
                 'bg' => 'Bulgarian',
                 'ca' => 'Catalan',
                 'cs' => 'Czech',
                 'da' => 'Danish',
                 'de' => 'German',
                 'el' => 'Greek',
                 'en' => 'English',
                 'es' => 'Spanish',
                 'fi' => 'Finnish',
                 'fr' => 'French',
                 'hi' => 'Hindi',
                 'hr' => 'Croatian',
                 'id' => 'Indonesian',
                 'it' => 'Italian',
                 'iw' => 'Hebrew',
                 'ja' => 'Japanese',
                 'ko' => 'Korean',
                 'lt' => 'Lithuanian',
                 'lv' => 'Latvian',
                 'nl' => 'Dutch',
                 'no' => 'Norwegian',
                 'pl' => 'Polish',
                 'pt' => 'Portugese',
                 'ro' => 'Romanian',
                 'ru' => 'Russian',
                 'sk' => 'Slovak',
                 'sl' => 'Slovenian',
                 'sr' => 'Serbian',
                 'sv' => 'Swedish',
                 'tl' => 'Filipino',
                 'uk' => 'Ukrainian',
                 'vi' => 'Vietnamese',
                 'zh-CN' => 'Chinese Simplified',
                 'zh-TW' => 'Chinese Traditional',
            );
    asort($isoLang); //comment out this line to sort results by 2 digit language code instead of language names above
    $retval = '';
    if ($header) {
        $retval = '<h2>' . $LANG_WIDGETS['translate'] . '</h2>';
    }
    $retval .= '<ul class="autotranslations">';

    foreach ($isoLang AS $key => $language ) {
        $randID = rand();
        if ($key != $_CONF['iso_lang']) {
            $retval .= '<li class="sprite-' . $key . '"><a href="http://translate.google.com/translate?';
            $retval .= 'hl=' . $key; // 2 character language code of google header bar (usually the same as tl below)
            $retval .= '&amp;sl=' . $_CONF['rdf_language']; // default language of your site
            $retval .= '&amp;tl=' . $key; // 2 character language code to translate site into (usually should be the same as hl above)
            $retval .= '&amp;u=' . urlencode($_CONF['site_url'] . '?r=' . $randID); // address of your site appends a random string so Google won't cache the translated page
            $retval .= '"><img src="' . $_CONF['site_url'] . '/images/speck.gif" alt="'.$language.'" title="'.$language.'" /></a></li>';
        }
    }
    $retval .= '</ul><div style="clear:left;"></div>';
    return $retval;
}


function WIDGET_slider( $dataArray )
{
    global $_CONF;

    $rand = rand(1,1000);
    $slideCounter = 1;
    $captionDiv = '';
    $retval = '';

    // define the JS we need for this theme..
    $outputHandle = outputHandler::getInstance();
    // core js
    $outputHandle->addLinkScript($_CONF['layout_url'].'/js/jquery.nivo.slider.pack.js');
    $outputHandle->addLinkStyle($_CONF['layout_url'].'/css/nivo-slider.css');
    $outputHandle->addLinkStyle($_CONF['layout_url'].'/css/themes/default/default.css');
    if ( isset($dataArray['options']['width']) ) {
        $max_width = 'style="max-width:'.$dataArray['options']['width'] . 'px;"';
    } else {
        $max_width = '';
    }
    $retval .= '<div class="slide-wrapper theme-default" '.$max_width.'>';
    $retval .= '<div id="slide_'.$rand.'" class="nivoSlider">';

    foreach ($dataArray['images'] as $images ) {
        if ( isset($images['link']) && $images['link'] != '' ) {
            $retval .= '<a href="'.$images['link'].'">';
        }
        if (isset($images['caption']) && $images['caption'] != '' ) {
            $caption = ' title="#htmlCaption'.$slideCounter.'" ';
        } else {
            $caption = '';
        }
        $retval .= '<img src="'.$images['image'].'" data-thumb="'.$images['image'] .'" '.$caption.'alt="" />' ;
        if ( isset($images['link']) && $images['link'] != '' ) {
            $retval .= '</a>';
        }
        if ( isset($images['caption']) && $images['caption'] != '' ) {
            $captionDiv .= '<div id="htmlCaption'.$slideCounter.'" class="nivo-html-caption">';
            $captionDiv .= $images['caption'];
            $captionDiv .= '</div>';
        }
        $slideCounter++;
    }
    $retval .= '</div>';
    $retval .= $captionDiv;
    $retval .= '</div>';

    $retval .= '<script type="text/javascript">$(window).load(function() {';
    $retval .= '   $(\'#slide_'.$rand.'\').nivoSlider({';
    foreach ($dataArray['options'] as $option => $value ) {
        $retval .= $option . ": " . "'".$value . "',";
    }
    $retval .= 'prevText: \'Prev\', nextText: \'Next\' }); });</script>';

    return $retval;
}

function WIDGET_springMenu($dataArray)
{
    global $_CONF;

    $rand = rand(1,1000);
    $slideCounter = 1;
    $retval = '';

    // define the JS we need for this theme..
    $outputHandle = outputHandler::getInstance();
    // core js
    $outputHandle->addLinkScript($_CONF['layout_url'].'/js/jquery.accordionImageMenu.min.js');
    $outputHandle->addLinkStyle($_CONF['layout_url'].'/css/accordionImageMenu.css');

    $retval .= '<div class="spring-menu">';
    $retval .= '<div id="springmenu_'.$rand.'" class="aim">';

    foreach ($dataArray['images'] as $images ) {
        if ( isset($images['link']) && $images['link'] != '' ) {
            $retval .= '<a href="'.$images['link'].'">';
        }
        $retval .= '<img src="'.$images['image'].'" alt="" />' ;
        if ( isset($images['link']) && $images['link'] != '' ) {
            $retval .= '</a>';
        }
        $slideCounter++;
    }
    $retval .= '</div>';
    $retval .= '</div>';

    $retval .= '<script type="text/javascript">$(window).load(function() {';
    $retval .= '   $(\'#springmenu_'.$rand.'\').AccordionImageMenu({';
    foreach ($dataArray['options'] as $option => $value ) {
        $retval .= "'".$option."'" . ": " . "'".$value . "',";
    }
    $retval .= '}); });</script>';

    return $retval;
}

/*
 * replaces mooslide()
 */

function WIDGET_tabslide( $dataArray )
{
    global $_CONF,$_TABLES;

    $retval = '';

    $id = rand(1,1000);

    // define the JS we need for this theme..
    $outputHandle = outputHandler::getInstance();
    // core js
    $outputHandle->addLinkScript($_CONF['layout_url'].'/js/jquery.sliderTabs.min.js');
    $outputHandle->addLinkStyle($_CONF['layout_url'].'/css/jquery.sliderTabs.css');

    $page_ids = $dataArray['panels'];

	if (!is_array($page_ids[0])) {
	//we are in Static Pages Mode
	    $sql = "SELECT sp_id, sp_content, sp_php, sp_title FROM {$_TABLES['staticpage']} WHERE sp_id in ("
	         . implode(', ', array_map(create_function('$a','return "\'" . htmlspecialchars($a) . "\'";'), $page_ids))
	         . ')' . COM_getPermSQL('AND');

	    $res = DB_query($sql);
	    $pages = array();
	    for ($i = 0; $A = DB_fetchArray($res); ++$i) {
	        $content = SP_render_content($A['sp_content'], $A['sp_php']);
	        $title = htmlspecialchars($A['sp_title']);
	        $order = array_search($A['sp_id'],$page_ids); // find proper order
	        $pages[$order] = array('sp_id' => $A['sp_id'],'content' => $content, 'title' => $title, 'index' => $order+1);
	    }
	} else {
	//we have been passed pre-formatted pages
	    $pages = array();
	    for ($i = 0; $i < sizeof($page_ids); ++$i) {
	        $content = $page_ids[$i]['content'];
	        $title = htmlspecialchars($page_ids[$i]['title']);
	        $order = $i;
	        $pages[$order] = array('sp_id' => $title, 'content' => $content, 'title' => $title, 'index' => $order+1);
	    }
	}
    if (count($pages) == 0) {
        return '';
    }
    ksort($pages);
    $retval .= '<div id="slidertabs_'.$id.'" class="tab-slider">';
    $retval .= '<ul>';
    foreach ( $pages as $page ) {
        extract($page);
        $retval .= '<li><a href="#'.$sp_id.'">'.stripslashes($title).'</a></li>';
    }
    $retval .= '</ul>';
    foreach ($pages as $page) {
        extract($page);
        $retval .= '<div id="'.$sp_id.'">';
        $retval .= $content;
        $retval .= '</div>';
    }
    $retval .= '</div><div style="clear:both;"></div>';

    $retval .= '<script type="text/javascript">$(window).load(function() {';
    $retval .= '$("div#slidertabs_'.$id.'").sliderTabs({';

    foreach ($dataArray['options'] as $option => $value ) {
        if ( is_numeric($value) ) {
            $retval .= $option . ": " . $value . ",";
        } else {
            $retval .= $option . ": " . "'".$value . "',";
        }
    }

    $retval .= '}); ';
    $retval .= ' });</script>';
    return $retval;
}


?>