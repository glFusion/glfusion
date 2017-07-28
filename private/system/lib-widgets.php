<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// |lib-widgets.php                                                           |
// |                                                                          |
// | A place for widget functions, jquery based or otherwise                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
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

    $T = new Template($_CONF['path_layout'].'/widgets');
    $T->set_file('widget', 'translations.thtml');

    if ( $header ) {
        $T->set_var('lang_header',$LANG_WIDGETS['translate']);
    }

    $T->set_block('widget', 'flags', 'f');

    foreach ($isoLang AS $key => $language ) {
        $randID = rand();
        if ($key != $_CONF['iso_lang']) {

            $T->set_var(array(
                'key'   => $key,
                'src_lng' => $_CONF['rdf_language'],
                'lang' => $language,
                'rand' => $randID,
            ));
            $T->parse('f', 'flags',true);
        }
    }

    $T->parse('output','widget');
    $retval = $T->finish($T->get_var('output'));

    return $retval;
}

function WIDGET_UIKITslider( $dataArray )
{
    global $_CONF, $_TABLES;

    $optionTypeArray = array(
        'animation' => 's',         // Defines the preferred transition between items.
        'duration'  => 'n',         // Defines the transition duration.
        'height'    => 's',         // Defines the slideshow height.
        'start'     => 'n',         // Defines the first slideshow item to be displayed.
        'autoplay'  => 'b',         // Defines whether or not the slideshow items should switch automatically.
        'pauseOnHover' => 'b',      // Pause autoplay when hovering a slideshow.
        'autoplayInterval' => 'n',  // Defines the timespan between switching slideshow items.
        'videoautoplay' => 'b',     // Defines whether or not a video starts automatically.
        'videomute' => 'b',         // Defines whether or not a video is muted.
        'kenburns' => 'b',          // Defines whether or not the Ken Burns effect is active. If kenburns is a numeric value, it will be used as the animation duration.
    );

    $templateFile = 'uikit-slider.thtml';
    $last = 0;
    $first = 0;
    $rand = rand(1,1000);
    $slideCounter = 0;
    $captionDiv = '';
    $retval = '';
    $dotnav = '';

    $T = new Template($_CONF['path_layout'].'/widgets');
    if ( isset($dataArray['template'] ) ) {
        $templateFile = $dataArray['template'];
    }
    $T->set_file('widget', $templateFile);

    $T->set_var('rand',$rand);

    $optionValue = '';
    $T->set_block('widget', 'options', 'o');

    foreach ($dataArray['options'] as $option => $value ) {
        $T->unset_var('optionvalue');
        $optionValue = '';
        if ( isset($optionTypeArray[$option]) ) {
            if ( $last > 0 ) $optionValue .= ',';
            $optionValue .= $option . ": ";
            switch ($optionTypeArray[$option]) {
                case 's' :
                    $optionValue .= "'" . $value . "'";
                    break;
                case 'b' :
                    $optionValue .= $value == 0 ? 'false' : 'true';
                    break;
                case 'n' :
                    $optionValue .= $value;
                    break;
                default :
                    $optionValue .= "'" . $value ."'";
                    break;
            }
            $last++;
            $T->set_var('optionvalue',$optionValue);
            $T->parse('o', 'options',true);
        }
    }

    $T->set_block('widget', 'dotnav', 'd');
    $T->set_block('widget', 'pages', 'p');
    $T->set_block('widget', 'images', 'i');

    $page_ids = array();

    // static page mode
    if (isset($dataArray['pages']) && is_array($dataArray['pages']) ) {
        $page_ids = $dataArray['pages'];

	    $sql = "SELECT sp_id, sp_content, sp_php, sp_title FROM {$_TABLES['staticpage']} WHERE sp_id in ("
	         . implode(', ', array_map(function($a) { return "'".htmlspecialchars($a)."'"; } , $page_ids))
	         . ')' . COM_getPermSQL('AND');

	    $res = DB_query($sql);
	    $pages = array();
	    for ($i = 0; $A = DB_fetchArray($res); ++$i) {
	        $content = SP_render_content($A['sp_content'], $A['sp_php']);
	        $title = htmlspecialchars($A['sp_title']);
	        $order = array_search($A['sp_id'],$page_ids); // find proper order
	        $pages[$order] = array('sp_id' => $A['sp_id'],'content' => $content, 'title' => $title, 'index' => $order+1);

	        $T->set_var('slide',$content);
            $T->set_var('slidecounter',$slideCounter);

            $T->parse('p', 'pages',true);
            $T->parse('d', 'dotnav',true);
            $slideCounter++;
	    }
    }

    $clearArray = array();

    if ( isset($dataArray['images']) ) {
        $T->set_block('widget', 'images', 'i');
        foreach ($dataArray['images'] as $images ) {
            $T->unset_var('imageurl');
            $T->unset_var('slidecounter');
            $T->unset_var('link');
            $T->unset_var('caption');
            if (count($clearArray) > 0 ) $T->unset_var($clearArray);
            $clearArray = array();

            $imageURL = str_replace( "%site_url%", $_CONF['site_url'], $images['image'] );

            foreach($images AS $item => $value) {
                $T->set_var($item,$value);
                $clearArray[] = $item;
            }

            $T->set_var('imageurl',$imageURL);
            $T->set_var('slidecounter',$slideCounter);

            $first++;
            $retval .= '>';
            if (isset($images['caption']) && $images['caption'] != '' ) {
                if ( isset($images['link']) && $images['link'] != '' ) {
                    $T->set_var('link',$images['link']);
                }
                if ( isset($images['caption']) && $images['caption'] != '' ) {
                    $T->set_var('caption',$images['caption']);
                }
            }
            $T->parse('i', 'images',true);
            $T->parse('d', 'dotnav',true);
            $slideCounter++;
        }
    }

    $T->parse('output','widget');
    $retval = $T->finish($T->get_var('output'));

    return $retval;
}

function WIDGET_slider( $dataArray )
{
    global $_CONF;

    $optionTypeArray = array(
        'effect' => array('map' => 'effect', 'type' => 's', 'valid' => array('fold','fade','sliceDown'), 'default' => 'fade'),
        'slices' => array('map' => 'slices', 'type' => 'n'),
        'boxCols' => array('map' => 'boxCols', 'type' => 'n'),
        'boxRows' => array('map' => 'boxRows', 'type' => 'n'),
        'animSpeed' => array('map' => 'animSpeed', 'type' => 'n'),
        'pauseTime' => array('map' => 'pauseTime', 'type' => 'n', 'default' => 5000),
        'startSlide' => array('map' => 'startSlide', 'type' => 'n'),
        'directionNav' => array('map' => 'directionNav', 'type' => 'b'),
        'controlNav' => array('map' => 'controlNav', 'type' => 'b', 'default' => true),
        'controlNavThumbs' => array('map' => 'controlNavThumbs', 'type' => 'b'),
        'pauseOnHover' => array('map' => 'pauseOnHover', 'type' => 'b'),
        'manualAdvance' => array('map' => 'manualAdvance', 'type' => 'b'),
        'prevText' => array('map' => 'prevText', 'type' => 's'),
        'nextText' => array('map' => 'nextText', 'type' => 's'),
        'randomStart' => array('map' => 'randomStart', 'type' => 'b'),
        'link'        => array('map' => 'link', 'type' => 's', 'valid' => array('image','caption'),'default' => 'image'),

        'mode' => array('map' => 'effect', 'type' => 's', 'valid' => array('fold','fade','sliceDown'), 'default' => 'fade'),
        'slideWidth' => array('map' => '', 'type' => 'n'),
        'speed' => array('map' => 'animSpeed', 'type' => 'n'),
        'slideMargin' => array('map' => '', 'type' => 'n'),
//        'startSlide' => array('map' => 'startSlide', 'type' => 'n'),
//        'randomStart' => array('map' => 'randomStart', 'type' => 'b'),
        'infiniteLoop' => array('map' => '', 'type' => 'b'),
        'hideControlOnEnd' => array('map' => '', 'type' => 'b'),
        'captions' => array('map' => '', 'type' => 'b'),
        'responsive' => array('map' => '', 'type' => 'b'),
        'touchEnabled' => array('map' => '', 'type' => 'b'),
        'pager' => array('map' => 'controlNav', 'type' => 'b'),
        'pagerType' => array('map' => '', 'type' => 's'),
        'auto' => array('map' => '', 'type' => 'b'),
    );

    $retval = '';
    $last = 0;
    $first = 0;
    $rand = rand(1,1000);
    $slideCounter = 0;
    $captionDiv = '';
    $captionHTML = '';
    $templateFile = 'slider.thtml';

    $dotnav = '';

    $T = new Template($_CONF['path_layout'].'/widgets');
    if ( isset($dataArray['template'] ) ) {
        $templateFile = $dataArray['template'];
    }
    $T->set_file('widget', $templateFile);

    $T->set_var('rand',$rand);

    $T->set_block('widget', 'images', 'i');
    $T->set_block('widget', 'captions', 'c');

    foreach ($dataArray['images'] as $images ) {
        $T->unset_var('imageurl');
        $T->unset_var('slidecounter');
        $T->unset_var('caption');
        $T->unset_var('link');

        $imageURL = str_replace( "%site_url%", $_CONF['site_url'], $images['image'] );

        $T->set_var('imageurl',$imageURL);
        $T->set_var('slidecounter',$slideCounter);

        if ( $images['caption'] != '' ) {
            $T->set_var('caption',$images['caption']);

            $retval .= 'title="#slider-'.$rand.'-slide'.$slideCounter.'-caption"';
        }
        $T->set_var('link',$images['link']);
        $T->parse('i', 'images',true);
        $T->parse('c', 'captions',true);
        $slideCounter++;
    }

    $T->set_block('widget', 'options', 'o');

    foreach ($dataArray['options'] as $option => $value ) {
        $optionLine = '';
        $T->unset_var('option');
        if ( isset($optionTypeArray[$option]) ) {
            if ( $optionTypeArray[$option]['map'] == '' ) continue;
            if ( isset($optionTypeArray[$option]['valid']) ) {
                if (!in_array($value,$optionTypeArray[$option]['valid']) ) {
                    $value = $optionTypeArray[$option]['default'];
                }
            }
            if ( $last > 0 ) $optionLine .= ',';
            $optionLine .= $optionTypeArray[$option]['map'] . ": ";
            switch ($optionTypeArray[$option]['type']) {
                case 's' :
                    $optionLine .= "'" . $value . "'";
                    break;
                case 'b' :
                    $optionLine .= $value == 0 ? 'false' : 'true';
                    break;
                case 'n' :
                    $optionLine .= $value;
                    break;
                default :
                    $optionLine .= "'" . $value ."'";
                    break;
            }
            $last++;
            $T->set_var('option',$optionLine);
            $T->parse('o', 'options',true);
        }
    }

    $T->parse('output','widget');
    $retval = $T->finish($T->get_var('output'));

    return $retval;
}

function WIDGET_sliderBX( $dataArray )
{
    global $_CONF;

    $optionTypeArray = array(
      'mode' => 's',
      'slideWidth' => 'n',
      'speed' => 'n',
      'slideMargin' => 'n',
      'startSlide' => 'n',
      'randomStart' => 'b',
      'infiniteLoop' => 'b',
      'hideControlOnEnd' => 'b',
      'captions' => 'b',
      'responsive' => 'b',
      'touchEnabled' => 'b',
      'pager' => 'b',
      'pagerType' => 's',
      'auto' => 'b'
    );

    $last = 0;
    $first = 0;
    $rand = rand(1,1000);
    $slideCounter = 1;
    $captionDiv = '';
    $retval = '';

    // define the JS we need for this theme..
    $outputHandle = outputHandler::getInstance();
    // core js
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/addons/bxslider/jquery.bxslider.min.js');
    $outputHandle->addLinkStyle($_CONF['site_url'].'/javascript/addons/bxslider/jquery.bxslider.css');

    $retval .= '<script type="text/javascript">$(document).ready(function(){';
    $retval .= '   $(\'.slide_'.$rand.'\').bxSlider({';

    foreach ($dataArray['options'] as $option => $value ) {
        if ( isset($optionTypeArray[$option]) ) {
            if ( $last > 0 ) $retval .= ',';
            $retval .= $option . ": ";
            switch ($optionTypeArray[$option]) {
                case 's' :
                    $retval .= "'" . $value . "'";
                    break;
                case 'b' :
                    $retval .= $value == 0 ? 'false' : 'true';
                    break;
                case 'n' :
                    $retval .= $value;
                    break;
            }
            $last++;
        }
    }
    $retval .= ' }); });</script>';

    $retval .= '<div class="slide-wrapper" style="margin:0 auto;">';
    $retval .= '<ul class="slide_'.$rand.'" style="margin:0;">';

    foreach ($dataArray['images'] as $images ) {
        $retval .= '<li>';

        if ( isset($images['link']) && $images['link'] != '' ) {
            $retval .= '<a href="'.$images['link'].'">';
        }
        $retval .= '<img src="'.$images['image'].'" alt="" ' ;

        if (isset($images['caption']) && $images['caption'] != '' ) {
            $retval .= ' title="'.$images['caption'].'" ';
        }

        $first++;

        $retval .= '>';

        if ( isset($images['link']) && $images['link'] != '' ) {
            $retval .= '</a>';
        }
        $retval .= '</li>';
        $slideCounter++;
    }
    $retval .= '</ul>';
    $retval .= '</div>';

    return $retval;
}

function WIDGET_springMenu($dataArray)
{
    global $_CONF;

    $rand = rand(1,1000);
    $slideCounter = 1;
    $retval = '';
    $templateFile = 'spring-menu.thtml';

    // define the JS we need for this theme..
    $outputHandle = outputHandler::getInstance();
    // core js
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/addons/accordion-image-menu/jquery.accordionImageMenu.min.js');
    $outputHandle->addLinkStyle( $_CONF['site_url'].'/javascript/addons/accordion-image-menu/accordionImageMenu.css');

    $T = new Template($_CONF['path_layout'].'/widgets');

    if ( isset($dataArray['template'] ) ) {
        $templateFile = $dataArray['template'];
    }

    $T->set_file('widget', $templateFile);
    $T->set_var('rand',$rand);

    $T->set_block('widget', 'images', 'i');

    foreach ($dataArray['images'] as $images ) {
        $T->unset_var('link');
        $T->unset_var('image');
        $T->unset_var('slidecounter');
        if ( isset($images['link']) && $images['link'] != '' ) {
            $T->set_var('link',$images['link']);
        }
        $imageURL = str_replace( "%site_url%", $_CONF['site_url'], $images['image'] );
        $T->set_var('image',$imageURL);
        $T->set_var('slidecounter',$slideCounter);
        $T->parse('i', 'images',true);
        $slideCounter++;
    }

    $last = 0;
    $T->set_block('widget', 'options', 'o');
    foreach ($dataArray['options'] as $option => $value ) {
        $optionLine = '';
        if ( $last > 0 ) $optionLine .= ',';
        $optionLine .= "'".$option."'" . ": " . "'".$value . "'";
        $T->set_var('optionvalue',$optionLine);
        $T->parse('o', 'options',true);
        $last++;
    }
    $T->parse('output','widget');
    $retval = $T->finish($T->get_var('output'));

    return $retval;
}

/*
 * replaces mooslide()
 */

function WIDGET_tabslide( $dataArray )
{
    global $_CONF,$_TABLES;

    $retval = '';
    $templateFile = 'tab-slider.thtml';

    $id = rand(1,1000);

    // define the JS we need for this theme..
    $outputHandle = outputHandler::getInstance();
    // core js
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/addons/slider-tabs/jquery.sliderTabs.min.js');
    $outputHandle->addLinkStyle($_CONF['site_url'].'/javascript/addons/slider-tabs/styles/jquery.sliderTabs.min.css');

    $page_ids = $dataArray['panels'];

	if (!is_array($page_ids[0])) {
	//we are in Static Pages Mode
	    $sql = "SELECT sp_id, sp_content, sp_php, sp_title FROM {$_TABLES['staticpage']} WHERE sp_id in ("
	         . implode(', ', array_map(function($a) { return "'".htmlspecialchars($a)."'"; } , $page_ids))
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

    $T = new Template($_CONF['path_layout'].'/widgets');
    if ( isset($dataArray['template'] ) ) {
        $templateFile = $dataArray['template'];
    }
    $T->set_file('widget', $templateFile);

    $T->set_var('rand',$id);

    $T->set_block('widget', 'tabs', 't');

    foreach ( $pages as $page ) {
        extract($page);
        $T->set_var('sp_id',$sp_id);
        $T->set_var('title',stripslashes($title));
        $T->parse('t', 'tabs',true);
    }

    $T->set_block('widget', 'slide_content', 's');
    foreach ($pages as $page) {
        extract($page);
        $T->set_var('sp_id',$sp_id);
        $T->set_var('content',$content);
        $T->parse('s', 'slide_content',true);
    }

    $T->set_block('widget', 'optionvalues', 'o');
    foreach ($dataArray['options'] as $option => $value ) {
        if ( is_numeric($value) ) {
            $T->set_var('optionline', $option . ": " . $value . ",");
        } else {
            $T->set_var('optionline', $option . ": " . "'".$value . "',");
        }
        $T->parse('o','optionvalues',true);
    }

    $T->parse('output','widget');
    $retval = $T->finish($T->get_var('output'));

    return $retval;
}

function WIDGET_tickerRSS($feedurl, $options = array() ) {
    global $_CONF, $LANG21, $LANG_WIDGETS;

// styles:
//  .ticker-feed > li {
//  	font-size:20px !important;
//  }
//  .ticker-feed > li > a:hover {
//	    text-decoration:none !important;
//  }
//  .ticker-feed {
//	    background:#000 !important;
//  }

    $defaultOptions = array(
        'speed'	            => 50,      // The movement speed in pixels per second
        'moving'	        => true,    // Defines if the WebTicker should start in moving state or a paused state
        'startEmpty'	    => true,    // Defines whether the elemtents should start outside of the visible area
        'hoverpause'	    => true,    // Pauses the animation if the user hovers over the ticker
        'transition'	    => 'linear', // The easing function used throughout for transitions 'linear' or 'ease'
        'height'	        => '30px'   // The height of the ticker element. The string value needs to include the unit
    );

    $optionsArray = array_merge($defaultOptions, $options);

    require_once $_CONF['path'].'/lib/simplepie/autoloader.php';

    $outputHandle = outputHandler::getInstance();

    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/addons/webticker/jquery.webticker.min.js');

    $retval = '';

    // Load the actual feed handlers:
    $feed = new SimplePie();
    $feed->set_useragent('glFusion/' . GVERSION.' '.SIMPLEPIE_USERAGENT);
    $feed->set_feed_url($feedurl);
    $feed->set_cache_location($_CONF['path'].'/data/layout_cache');
    $rc = $feed->init();
    if ( $rc == true ) {
        $feed->handle_content_type();
        /* We have located a reader, and populated it with the information from
         * the syndication file. Now we will sort out our display, and update
         * the block.
         */
        $maxheadlines = 50;
        if (!empty($_CONF['syndication_max_headlines'])) {
            $maxheadlines = $_CONF['syndication_max_headlines'];
        }

        if ( $maxheadlines == 0 ) {
            $number_of_items = $feed->get_item_quantity();
        } else{
            $number_of_items = $feed->get_item_quantity($maxheadlines);
        }
        $etag = '';
        $update = date('Y-m-d H:i:s');
        $last_modified = $update;
        $last_modified = DB_escapeString($last_modified);

        for ( $i = 0; $i < $number_of_items; $i++ ) {
            $item = $feed->get_item($i);
            $title = $item->get_title();
            if (empty($title)) {
                $title = $LANG21[61];
            }
            $link      = $item->get_permalink();
            $enclosure = $item->get_enclosure();

            if ($link != '') {
                $content = COM_createLink($title, $link, $attr = array('target' => '_blank', 'rel' => 'noopener noreferrer'));
            } elseif ($enclosure != '') {
                $content = COM_createLink($title, $enclosure, $attr = array('target' => '_blank', 'rel' => 'noopener noreferrer'));
            } else {
                $content = $title;
            }
            $articles[] = $content;
        }

        // build a list
        $content = COM_makeList($articles, 'ticker-feed');
        $content = str_replace(array("\015", "\012"), '', $content);

        $retval = str_replace('<ul class="','<ul class="ticker-feed ' , $content);

        $optionText  = 'speed : ' . $optionsArray['speed'] . ','.LB;
        $optionText .= 'moving : ' . $optionsArray['moving'] . ','.LB;
        $optionText .= 'startEmpty : '. $optionsArray['startEmpty'] . ','.LB;
        $optionText .= 'hoverpause : ' . $optionsArray['hoverpause'] .  ','.LB;
        $optionText .= 'transition : "' . $optionsArray['transition'] . '"'.  ','.LB;
        $optionText .= 'height : "' . $optionsArray['height'] . '"'. LB;

        $retval .= <<<EOT
<script type="text/javascript">
         $(document).ready(function(){
                 $('.ticker-feed').webTicker({
                    duplicate : true,
                    $optionText
                });
         });
</script>
EOT;

    } else {
        $err = $feed->error();
        COM_errorLog($err);
        $retval = $err;
    }
    return $retval;
}


// OLD MooTools Widgets - depreciated
function WIDGET_mooslide($page_ids, $width = 550, $height = 160, $id = 'gl_', $slide_interval = 0)
/*  Sample standalone staticpage:

USES_lib_widgets();
// add you own static pages here.
$slides = Array('staticpage_id_1', 'staticpage_id_2');
$display = COM_siteHeader();
// test the parameters, or not
$display .= WIDGET_mooslide($slides);
$display .= WIDGET_mooslide($slides, 560, 160, 'gl_', 5000);
$display .= COM_siteFooter();
echo $display;
*/
{
    global $_TABLES, $_CONF;

    if (count($page_ids) == 0) {
        return '';
    }

	// Backwards Compatibility - if $id is still the old default 'gl_slide', change it.
	if ($id == 'gl_slide') {$id = 'gl_';}

    $display = <<<EOJ
<script type="text/javascript" src="{$_CONF['site_url']}/javascript/mootools/gl_mooslide.js"></script>
<script type="text/javascript">
    window.addEvent('load', function() {
        var myFilm = new gl_Slide(\$('$id'), {
            fx: {
                wait: true,
                duration: 1000
                },
            scrollFX: {
                transition: Fx.Transitions.Cubic.easeIn
                },
            dimensions: {
                width: $width,
                height: $height
                },
			cssvars: {
				customID: '$id'
				},
			autoScroll: {
				interval: $slide_interval    // in ms, thus 1000 is 1 second, set to 0 to turn off.
				}
        });
    });
</script>
<div id="$id" class="{$id}slide">
EOJ;

	if (!is_array($page_ids[0])) {
	//we are in Static Pages Mode
	    $sql = "SELECT sp_id, sp_content, sp_php, sp_title FROM {$_TABLES['staticpage']} WHERE sp_id in ("
	         . implode(', ', array_map(function($a) { return "'".htmlspecialchars($a)."'"; } , $page_ids))
	         . ')' . COM_getPermSQL('AND');

	    $res = DB_query($sql);
	    $pages = array();
	    for ($i = 0; $A = DB_fetchArray($res); ++$i) {
	        $content = SP_render_content(stripslashes($A['sp_content']), $A['sp_php']);
	        $title = htmlspecialchars(stripslashes($A['sp_title']));
	        $order = array_search($A['sp_id'],$page_ids); // find proper order
	        $pages[$order] = Array('content' => $content, 'title' => $title, 'index' => $order+1);
	    }
	} else {
	//we have been passed pre-formatted pages
	    $pages = array();
	    for ($i = 0; $i < sizeof($page_ids); ++$i) {
	        $content = stripslashes($page_ids[$i]['content']);
	        $title = htmlspecialchars(stripslashes($page_ids[$i]['title']));
	        $order = $i;
	        $pages[$order] = Array('content' => $content, 'title' => $title, 'index' => $order+1);
	    }
	}
/* 	COM_errorLog(print_r($pages,true),1); */
    if (count($pages) == 0) {
        return '';
    }
    ksort($pages);
    foreach ($pages as $page) {
        extract($page);
        $display .= <<<EOS
<div class="{$id}tab-pane" id="{$id}tab-$index-pane">
    <h1 class="{$id}tab-title">$title</h1>
    <div>$content</div>
</div>
EOS;
    }

    $display .= '</div><div style="clear:both;"></div>';

    return $display;
}

function WIDGET_mootickerRSS($block = 'gl_mootickerRSS', $id = 'gl_mooticker') {
    global $_CONF, $_TABLES, $LANG_WIDGETS;

    $query = "SELECT bid, rdfurl, rdflimit, UNIX_TIMESTAMP(rdfupdated) AS date FROM {$_TABLES['blocks']} WHERE type = 'portal' AND is_enabled = 0";

    // Update feeds from blocks where 'is_enabled' -tag is set to 0
    $blocksql['mysql'] = $query;
    $blocksql['mssql'] .= $query;
    $result = DB_query( $blocksql );
    $nrows = DB_numRows( $result );

    $blocks = array();
    for( $i = 0; $i < $nrows; $i++ )
    {
        $blocks[] = DB_fetchArray( $result );
    }

    // Loop though resulting sorted array and pass associative arrays
    // to COM_rdfCheck
    foreach( $blocks as $A )
    {
        COM_rdfCheck( $A['bid'], $A['rdfurl'], $A['date'], $A['rdflimit'] );
    };

    $retval = '';
    $result = DB_query("SELECT *, rdfupdated as date FROM {$_TABLES['blocks']} WHERE name='" . DB_escapeString($block) . "'");
    $numRows = DB_numRows($result);
    if ( $numRows < 1 || $result == NULL ) {
        return $retval;
    }
    $B = DB_fetchArray($result);
    if ( $B['is_enabled'] == 0 ) {
        COM_formatBlock($B, true);
        $retval = <<<EOT
<script type="text/javascript">
var MooTicker=new Class({options:{controls:true,delay:2000,duration:800,blankimage:'{site_url}/images/speck.gif'},initialize:function(b,c){this.setOptions(c);this.element=$(b)||null;this.element.addEvents({'mouseenter':this.stop.bind(this),'mouseleave':this.play.bind(this)});this.news=this.element.getElements('ul li');this.current=0;this.fx=[];this.news.getParent().setStyle('position','relative');var d=this;this.news.each(function(a,i){a.setStyle('position','absolute');this.fx[i]=new Fx.Style(a,'opacity',{duration:this.options.duration,onStart:function(){d.transitioning=true},onComplete:function(){d.transitioning=false}}).set(1);if(!i)return;a.setStyle('opacity',0)},this);if(this.options.controls)this.addControls();this.status='stop';window.addEvent('load',this.play.bind(this));return this},addControls:function(){var a=new Element('span',{'class':'controls'}).injectTop(this.element);this.arrowPrev=new Element('img',{'class':'control-prev','title':'{prev}','alt':'{prev}','src':this.options.blankimage}).inject(a);this.arrowNext=new Element('img',{'class':'control-next','title':'{next}','alt':'{next}','src':this.options.blankimage}).inject(a);this.arrowPrev.addEvent('click',this.previous.bind(this));this.arrowNext.addEvent('click',this.next.bind(this));return this},previous:function(){if(this.transitioning)return this;var a=(!this.current)?this.news.length-1:this.current-1;this.fx[this.current].start(0);this.fx[a].start(1);this.current=a;return this},next:function(){if(this.transitioning)return this;var a=(this.current==this.news.length-1)?0:this.current+1;this.fx[this.current].start(0);this.fx[a].start(1);this.current=a;return this},play:function(){if(this.status=='play')return this;this.status='play';this.timer=this.next.periodical(this.options.delay+this.options.duration,this);return this},stop:function(){this.status='stop';\$clear(this.timer);return this}});MooTicker.implement(new Options,new Events);
</script>
<script type="text/javascript">
         window.addEvent('domready', function() {
                 var x = new MooTicker('$id', {
                         controls: true,
                         delay: 2500,
                         duration: 600 });
         });
</script>
<div id="$id">
EOT;
        $retval = str_replace('{site_url}',$_CONF['site_url'] , $retval);
        $retval = str_replace('{prev}',$LANG_WIDGETS['prev'] , $retval);
        $retval = str_replace('{next}',$LANG_WIDGETS['next'] , $retval);
        $retval .= '<span class="tickertitle">' . $LANG_WIDGETS['latest_news'] . ':</span>';
        $retval .= $B['content'] . '</div>';
    }
    return $retval;
}

// inserts the call to the javascript file, referencing the full path to gl_moospring.js
function WIDGET_moospring() {
    global $_CONF;

    $retval = '<script type="text/javascript" src="' . $_CONF['site_url'] . '/javascript/mootools/gl_moospring.js"></script>';
    return $retval;
}

//Powers the gl_moorotator. It is here so that the blankimage reference can be built with the full site url
function WIDGET_moorotator() {
    global $_CONF, $LANG_WIDGETS;

    $retval = '';
    $retval = <<<EOR
<script type="text/javascript">
var gl_mooRotator=new Class({options:{controls:true,duration:1000,delay:5000,autoplay:false,blankimage:'{site_url}/images/speck.gif'},initialize:function(a,b){this.container=$(a);this.setOptions(b);this.images=this.container.getElements('.gl_moorotatorimage > img');this.content=this.container.getElements('.gl_moorotatortext');this.current=0;this.build();this.attachEvents();this.status='pause';if(this.options.autoplay)window.addEvent('domready',this.play.bind(this));return this},build:function(){var b=this;$$(this.content,this.images).setStyle('position','absolute');var c=this.images.slice(1);var d=this.content.slice(1);c.each(function(a){a.injectAfter(this.images[0]).setStyle('opacity',0)},this);d.each(function(a){a.injectAfter(this.content[0]).setStyle('opacity',0)},this);var e=$$('.gl_moorotator').slice(1);e.each(function(a){a.empty().remove()});if(this.options.controls == 1){var f=new Element('div',{'class':'controls'}).inject(this.container);} else {var f=new Element('div',{'class':''}).inject(this.container);}this.arrowPrev=new Element('img',{'class':'control-prev','title':'{prev}','alt':'{prev}','src':this.options.blankimage}).inject(f);this.arrowPlay=new Element('img',{'id':'play-pause','class':'control-pause','title':'{playpause}','alt':'{playpause}','src':this.options.blankimage}).inject(f);this.arrowNext=new Element('img',{'class':'control-next','title':'{next}','alt':'{next}','src':this.options.blankimage}).inject(f);if(this.options.corners){(this.images.length).times(function(i){(2).times(function(j){new Element('div',{'class':'i'+(j+1)}).inject(this.images[i])}.bind(this))}.bind(this))}(4).times(function(i){new Element('div',{'class':'corner c'+(i+1)}).inject(this.content[0].getParent())}.bind(this));this.fx=[];(this.content.length).times(function(i){this.fx[i]=[new Fx.Style(this.images[i],'opacity',{duration:this.options.duration,onStart:function(){b.transitioning=true},onComplete:function(){b.transitioning=false}}),new Fx.Style(this.content[i],'opacity',{duration:this.options.duration})]}.bind(this));return this},attachEvents:function(){var a=this,playstop=$('play-pause');this.arrowPrev.addEvent('click',this.previous.bind(this));this.arrowNext.addEvent('click',this.next.bind(this));this.arrowPlay.addEvent('click',function(){if(a.status=='play'){a.stop();playstop.className='control-play'}else{a.play();playstop.className='control-pause'}});return this},previous:function(){if(this.transitioning)return this;var b=(!this.current)?this.content.length-1:this.current-1;this.fx[this.current].each(function(a){a.start(0)});this.fx[b].each(function(a){a.start(1)});this.current=b;return this},next:function(){if(this.transitioning)return this;var b=(this.current==this.content.length-1)?0:this.current+1;this.fx[this.current].each(function(a){a.start(0)});this.fx[b].each(function(a){a.start(1)});this.current=b;return this},play:function(){if(this.status=='play')return this;this.status='play';this.timer=this.next.periodical(this.options.delay+this.options.duration,this);return this},stop:function(){this.status='pause';\$clear(this.timer);return this}});gl_mooRotator.implement(new Events,new Options);</script>
EOR;
    $retval = str_replace('{site_url}',$_CONF['site_url'] , $retval);
    $retval = str_replace('{prev}',$LANG_WIDGETS['prev'] , $retval);
    $retval = str_replace('{next}',$LANG_WIDGETS['next'] , $retval);
    $retval = str_replace('{playpause}',$LANG_WIDGETS['playpause'] , $retval);
    return $retval;
}

//wrapper widget: wraps a page outside of glFusion (but on the same server)
//and auto adjusts the height of the iframe to whatever page is loaded
//also, load links to parent site in parent window (see public_html/javascript/common.js)
//this script borks in Opera
function WIDGET_wrapper() {
//add the javascript to take care of dynamic iframe
    global $LANG_WIDGETS;

    $display = <<<EOW
    <script type="text/javascript">

/***********************************************
* IFrame SSI script II - Dynamic Drive DHTML code library (http://www.dynamicdrive.com)
* Visit DynamicDrive.com for hundreds of original DHTML scripts
* This notice must stay intact for legal use
***********************************************/

//Input the IDs of the IFRAMES you wish to dynamically resize to match its content height:
//Separate each ID with a comma. Examples: ["myframe1", "myframe2"] or ["myframe"] or [] for none:
var iframeids=["myframe"]

//Should script hide iframe from browsers that don't support this script (non IE5+/NS6+ browsers)
//This script is tested to work in FF, IE, Safari, Chrome, but borks in Opera
var iframehide="no"

var getFFVersion=navigator.userAgent.substring(navigator.userAgent.indexOf("Firefox")).split("/")[1]
var FFextraHeight=parseFloat(getFFVersion)>=0.1? 16 : 0 //extra height in px to add to iframe in FireFox 1.0+ browsers

function resizeCaller() {
var dyniframe=new Array()
for (i=0; i<iframeids.length; i++){
if (document.getElementById)
resizeIframe(iframeids[i])
//reveal iframe for lower end browsers? (see var above):
if ((document.all || document.getElementById) && iframehide=="no"){
var tempobj=document.all? document.all[iframeids[i]] : document.getElementById(iframeids[i])
tempobj.style.display="block"
}
}
}

function resizeIframe(frameid){
var currentfr=document.getElementById(frameid)
if (currentfr && !window.opera){
currentfr.style.display="block"
if (currentfr.contentDocument && currentfr.contentDocument.body.offsetHeight) //ns6 syntax
currentfr.height = currentfr.contentDocument.body.offsetHeight+FFextraHeight;
else if (currentfr.Document && currentfr.Document.body.scrollHeight) //ie5+ syntax
currentfr.height = currentfr.Document.body.scrollHeight;
if (currentfr.addEventListener)
currentfr.addEventListener("load", readjustIframe, false)
else if (currentfr.attachEvent){
currentfr.detachEvent("onload", readjustIframe) // Bug fix line
currentfr.attachEvent("onload", readjustIframe)
}
}
}

function readjustIframe(loadevt) {
var crossevt=(window.event)? event : loadevt
var iframeroot=(crossevt.currentTarget)? crossevt.currentTarget : crossevt.srcElement
if (iframeroot)
resizeIframe(iframeroot.id);
}

function loadintoIframe(iframeid, url){
if (document.getElementById)
document.getElementById(iframeid).src=url
}

if (window.addEventListener)
window.addEventListener("load", resizeCaller, false)
else if (window.attachEvent)
window.attachEvent("onload", resizeCaller)
else
window.onload=resizeCaller

if (Browser.Engine.presto) { //mootools 1.2 code to display Opera only message
window.addEvent('domready', function(){
$('noOpera').appendText('{noOpera}');
});
};

</script>
EOW;

    $display = str_replace('{noOpera}',$LANG_WIDGETS['noOpera'] , $display);
    return $display;
}



Class ul
{
    private $level = -1;
    private $ul_array = array();
    private $last_value;
    private $parent;
    private $parents = array();
    private $li_array = array();
    private $counter = 0;
    private $tree = array();
    private $id = 1;

    function __construct($list)
    {
        /**
        * Initialization of XML Parser
        *
        * @var Resource $xml_parser XML Parser
        */
        $xml_parser = xml_parser_create();
        /**
        * The method that will take care of the XML data
        */
        xml_set_character_data_handler($xml_parser, "character_data");
        /**
        * The method that will take care of the XML data elements
        */
        xml_set_element_handler($xml_parser, "start_element", "end_element");
        /**
        * We set the parse to be used inside an object
        */
        xml_set_object ( $xml_parser, $this );

        xml_parse($xml_parser, $list);

        xml_parser_free($xml_parser);
    }
    private function start_element($parser, $name, $attrs)
    {
        if($name=="UL")
        {
            $this->level++;
            if($this->level > 0)
            {
                $this->parent = $this->last_value;
                if(strlen($this->parent))
                {
                    $this->ul_array[$this->level][]=$this->parent;
                }
            }
            $this->counter++;
        }
    }
    private function end_element($parser, $name)
    {
        if($name=="UL")
        {
            if(strlen($this->parent))
            {
                foreach($this->li_array[$this->level] as $key=>$li)
                {
                    if(is_array($li))
                    {
                        foreach($li as $key1=>$kid)
                        {
                            $this->tree[$this->counter][$this->level][] = $kid;
                        }
                        unset($this->li_array[$this->level][$key]);
                    }
                }
            }
            else
            {
                foreach($this->li_array[$this->level] as $key=>$li)
                {
                    if(is_array($li))
                    {
                        foreach($li as $key1=>$kid)
                        {
                            $this->tree[$this->counter][$this->level][] = $kid;
                        }
                        unset($this->li_array[$this->level][$key]);
                    }
                }
            }

            $this->level--;
            $this->counter++;
            $this->parent="";
        }
    }
    private function character_data($parser = NULL, $data = NULL)
    {
        if(strlen(trim($data)))
        {
            $data = trim($data);
//            $data = $data."-|".$this->id++."|";
            $this->li_array[$this->level][$this->counter][]=$data;
            $this->last_value = $data;
        }
    }
    public function levels()
    {
        $parents = array();

        foreach($this->tree as $key=>&$lis)
        {
            if(is_array($lis))
            {
                foreach($lis as $key_1=>$lis_item)
                {
                    $parent="";
                    if(isset($this->ul_array[$key_1]))
                    {
                        $parent = array_shift($this->ul_array[$key_1]);
                    }
                    if(strlen($parent))
                    {
                        $lis[$key_1]['parent'] = $parent;
                        $parents[] = $parent;
                    }
                }
            }
        }

        //print_r($this->ul_array);
        //print_r($this->li_array);
        //print_r($this->counter);
        //print_r($this->tree);
		return $this->tree;
    }
}

?>