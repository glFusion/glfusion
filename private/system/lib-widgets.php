<?php
/**
* glFusion CMS
*
* Common functions and startup code
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans    mark AT glfusion DOT org
*   Eric Warren      eakwarren AT gmail DOT com
*   Joe Mucchiello   jmucchiello AT yahoo DOT com
*
*/

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;

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
    global $_CONF, $LANG_LOCALE, $LANG_WIDGETS;

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
                'src_lng' => isset($LANG_LOCALE) ? $LANG_LOCALE : 'en_US';
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

    $db = Database::getInstance();

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
        $page_ids_list = implode(',',$page_ids);

	    $sql = "SELECT sp_id, sp_content, sp_php, sp_title
	            FROM `{$_TABLES['staticpage']}`
	            WHERE sp_id in (?)" . $db->getPermSQL('AND');

        $stmt = $db->conn->executeQuery($sql,array($page_ids),array(Database::PARAM_STR_ARRAY));

        $pData = $stmt->fetchAll(Database::ASSOCIATIVE);

        foreach($pData AS $A) {
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

    $retval .= '<script>$(document).ready(function(){';
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

    $db = Database::getInstance();

    $id = rand(1,1000);

    // define the JS we need for this theme..
    $outputHandle = outputHandler::getInstance();
    // core js
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/addons/slider-tabs/jquery.sliderTabs.min.js');
    $outputHandle->addLinkStyle($_CONF['site_url'].'/javascript/addons/slider-tabs/styles/jquery.sliderTabs.min.css');

    $page_ids = $dataArray['panels'];

	if (!is_array($page_ids[0])) {
	//we are in Static Pages Mode

	    $sql = "SELECT sp_id, sp_content, sp_php, sp_title
	                 FROM `{$_TABLES['staticpage']}`
	                 WHERE sp_id in (?)" . $db->getPermSQL('AND');

        $stmt = $db->conn->executeQuery($sql,array($page_ids),array(Database::PARAM_STR_ARRAY));

        $pData = $stmt->fetchAll(Database::ASSOCIATIVE);
	    $pages = array();

        foreach ($pData AS $A) {
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

        for ( $i = 0; $i < $number_of_items; $i++ ) {
            $item = $feed->get_item($i);
            $title = $item->get_title();
            if (empty($title)) {
                $title = $LANG21[61];
            }
            $link      = $item->get_permalink();
            $enclosure = $item->get_enclosure();

            if ($link != '') {
                if ( isset($_CONF['open_ext_url_new_window']) && $_CONF['open_ext_url_new_window'] == 1 ) {
                    if ( strncasecmp ( $_CONF['site_url'],$link, (strlen($_CONF['site_url']) - 1) ) == 0 ) {
                        $attr = array('rel' => 'noopener noreferrer');
                    } else {
                        $attr = array('target' => '_blank', 'rel' => 'noopener noreferrer');
                    }
                } else {
                    $attr = array('target' => '_blank', 'rel' => 'noopener noreferrer');
                }
                $content = COM_createLink($title, $link, $attr);
            } elseif ($enclosure != '') {
                if ( isset($_CONF['open_ext_url_new_window']) && $_CONF['open_ext_url_new_window'] == 1 ) {
                    if ( strncasecmp ( $_CONF['site_url'],$enclosure, (strlen($_CONF['site_url']) - 1) ) == 0 ) {
                        $attr = array('rel' => 'noopener noreferrer');
                    } else {
                        $attr = array('target' => '_blank', 'rel' => 'noopener noreferrer');
                    }
                } else {
                    $attr = array('target' => '_blank', 'rel' => 'noopener noreferrer');
                }
                $content = COM_createLink($title, $enclosure, $attr);
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
<script>
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
        Log::write('system',Log::ERROR,$err);
        $retval = $err;
    }
    return $retval;
}
?>
