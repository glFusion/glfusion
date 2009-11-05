<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// |lib-widgets.php                                                           |
// |                                                                          |
// | A place for widget functions, mootools based or otherwise                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
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

function WIDGET_mooslide($page_ids, $width = 550, $height = 160, $id = 'gl_slide')
/*  Sample standalone staticpage:

USES_lib_widgets();
// add you own static pages here.
$slides = Array('staticpage_id_1', 'staticpage_id_2');
$display = COM_siteHeader();
// test the parameters, or not
$display .= WIDGET_mooslide($slides);
$display .= WIDGET_mooslide($slides, 560, 160, 'div id of mooSlide');
$display .= COM_siteFooter();
echo $display;
*/
{
    global $_TABLES, $_CONF;

    if (count($page_ids) == 0) {
        return '';
    }

    $display = <<<EOJ
<script type="text/javascript" src="{$_CONF['site_url']}/javascript/mootools/gl_mooslide.js"></script>
<script type="text/javascript">
    window.addEvent('domready', function() {
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
                }
        });
    });
</script>

<div id="$id" class="gl_slide">
EOJ;
    $sql = "SELECT sp_id, sp_content, sp_php, sp_title FROM {$_TABLES['staticpage']} WHERE sp_id in ("
         . implode(', ', array_map(create_function('$a','return "\'" . htmlspecialchars($a) . "\'";'), $page_ids))
         . ')' . COM_getPermSQL('AND');

    $res = DB_query($sql);
    $pages = array();
    for ($i = 0; $A = DB_fetchArray($res); ++$i) {
        $content = SP_render_content(stripslashes($A['sp_content']), $A['sp_php']);
        $title = htmlspecialchars(stripslashes($A['sp_title']));
        $order = array_search($A['sp_id'],$page_ids); // find proper order
        $pages[$order] = Array('content' => $content, 'title' => $title, 'index' => $order+1);
    }
    if (count($pages) == 0) {
        return '';
    }
    ksort($pages);
    foreach ($pages as $page) {
        extract($page);
        $display .= <<<EOS
<div class="tab-pane" id="tab-$index-pane">
    <h1 class="tab-title">$title</h1>
    <div>$content</div>
</div>
EOS;
    }

    $display .= '</div><div style="clear:both;"></div>';

    return $display;
}

// A portal staticpage (that bases itself on a user created proper portal block called gl_mootickerRSS)
//modified from LWC's forum post http://www.geeklog.net/forum/viewtopic.php?showtopic=67396 by Mark R. Evans and Joe Mucchiello
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
    $result = DB_query("SELECT *, rdfupdated as date FROM {$_TABLES['blocks']} WHERE name='" . addslashes($block) . "'");
    $numRows = DB_numRows($result);
    if ( $numRows < 1 || $result == NULL ) {
        return $retval;
    }
    $B = DB_fetchArray($result);
    if ( $B['is_enabled'] == 0 ) {
        COM_formatBlock($B, true);
        $retval = '<script type="text/javascript">' . LB . file_get_contents($_CONF['path_html'] . 'javascript/mootools/gl_mooticker.js') . LB . '</script>' . LB;
        $retval .= <<<EOT
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
	$retval .= '<div class="autotranslations"><ul>';

    foreach ($isoLang AS $key => $language ) {
		$randID = rand();
        if ($key != $_CONF['iso_lang']) {
        	$retval .= '<li><a href="http://translate.google.com/translate?';
        	$retval .= 'hl=' . $key; // 2 character language code of google header bar (usually the same as tl below)
        	$retval .= '&amp;sl=' . $_CONF['rdf_language']; // default language of your site
        	$retval .= '&amp;tl=' . $key; // 2 character language code to translate site into (usually should be the same as hl above)
        	$retval .= '&amp;u=' . urlencode($_CONF['site_url'] . '?r=' . $randID); // address of your site appends a random string so Google won't cache the translated page
        	$retval .= '">';
        	$retval .= '<img src="' . $_CONF['site_url'] . '/images/translations/';
        	$retval .= $key.'.png" alt="'.$language.'" title="'.$language.'"' . XHTML . '></a></li>';
        }
    }
    $retval .= '</ul></div><div style="clear:left;"></div>';
	return $retval;
}

// inserts the call to the javascript file, referencing the full path to gl_moospring.js
function WIDGET_moospring() {
    global $_CONF;

    $retval = '<script type="text/javascript" src="' . $_CONF['site_url'] . '/javascript/mootools/gl_moospring.js"></script>';
    return $retval;
}

//gl_moorotator widget: inserts absolute references to the site url, as well as localizing the prev/playpause/next strings
function WIDGET_moorotator() {
	global $_CONF, $LANG_WIDGETS;
	$retval = '<script type="text/javascript">' . LB . file_get_contents( $_CONF['path_html'] . 'javascript/mootools/gl_moorotator.js' ) . LB . '</script>' . LB;
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
	$display = <<<EOW
	<script type="text/javascript">

/***********************************************
* IFrame SSI script II- © Dynamic Drive DHTML code library (http://www.dynamicdrive.com)
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
$('noOpera').appendText('This script does not automatically resize the iframe in the Opera browser.');
});
};

</script>
EOW;

    return $display;
}
?>