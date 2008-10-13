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

function WIDGET_mooslide($pages, $width = 550, $height = 160, $id = 'gl_slide')
//standalone page example is at the bottom of the function.
{
    global $_TABLES, $_CONF;

    if (count($pages) == 0) {
        return '';
    }

    $display = <<<EOJ
<script type="text/javascript" src="{$_CONF['site_url']}/javascript/mootools/gl_mooslide.js"></script>
<script type="text/javascript">
	window.addEvent('domready', function() { //leave as domready so list won't display until ready
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
         . implode(', ', array_map(create_function('$a','return "\'" . htmlspecialchars($a) . "\'";'), $pages))
         . ')';

    $res = DB_query($sql);
    $list = array();
    for ($i = 0; $A = DB_fetchArray($res); ++$i) {
        $content = SP_render_content(stripslashes($A['sp_content']), $A['sp_php']);
		$title = htmlspecialchars(stripslashes($A['sp_title']));
		$list[$A['sp_id']] = Array('content' => $content, 'title' => $title);
	}
	foreach ($pages as $page) {
	    extract($list[$page]);
        $display .= <<<EOS
<div class="tab-pane" id="tab-$i-pane">
	<h1 class="tab-title">$title</h1>
	<div>$content</div>
</div>
EOS;
    }

    $display .= '</div>';

    return $display;
}

/*  Sample staticpage:

USES_lib_widget();
// add you own static pages here.
$slides = Array('staticpage id 1', 'staticpage id 2');
$display = COM_siteHeader();
// test the parameters, or not
$display .= WIDGET_mooslide($slides);
$display .= WIDGET_mooslide($slides, 550, 160, 'div id of mooSlide');
$display .= COM_siteFooter();
echo $display;
*/

// A portal staticpage (that bases itself on a user created proper portal block called gl_mootickerRSS)
//modified from LWC's forum post http://www.geeklog.net/forum/viewtopic.php?showtopic=67396 by Mark R. Evans and Joe Mucchiello
function WIDGET_mootickerRSS($block = 'gl_mootickerRSS', $id = 'gl_mooticker') {
	global $_CONF, $_TABLES, $LANG_WIDGETS;
	$retval = '';
	$result = DB_query("SELECT *, rdfupdated as date FROM {$_TABLES['blocks']} WHERE name='" . $block . "'");
	$numRows = DB_numRows($result);
	if ( $numRows < 1 || $result == NULL ) {
		return $retval;
	}
	$B = DB_fetchArray($result);
	if ( $B['is_enabled'] == 0 ) {
		$retval = <<<EOT
<script type="text/javascript">
var MooTicker=new Class({options:{controls:true,delay:2000,duration:800,blankimage:'{site_url}/images/speck.gif'},initialize:function(b,c){this.setOptions(c);this.element=\$(b)||null;this.element.addEvents({'mouseenter':this.stop.bind(this),'mouseleave':this.play.bind(this)});this.news=this.element.getElements('ul li');this.current=0;this.fx=[];this.news.getParent().setStyle('position','relative');var d=this;this.news.each(function(a,i){a.setStyle('position','absolute');this.fx[i]=new Fx.Style(a,'opacity',{duration:this.options.duration,onStart:function(){d.transitioning=true},onComplete:function(){d.transitioning=false}}).set(1);if(!i)return;a.setStyle('opacity',0)},this);if(this.options.controls)this.addControls();this.status='stop';window.addEvent('load',this.play.bind(this));return this},addControls:function(){var a=new Element('span',{'class':'controls'}).injectTop(this.element);this.arrowPrev=new Element('img',{'class':'control-prev','title':'Previous','alt':'prev','src':this.options.blankimage}).inject(a);this.arrowNext=new Element('img',{'class':'control-next','title':'Next','alt':'next','src':this.options.blankimage}).inject(a);this.arrowPrev.addEvent('click',this.previous.bind(this));this.arrowNext.addEvent('click',this.next.bind(this));return this},previous:function(){if(this.transitioning)return this;var a=(!this.current)?this.news.length-1:this.current-1;this.fx[this.current].start(0);this.fx[a].start(1);this.current=a;return this},next:function(){if(this.transitioning)return this;var a=(this.current==this.news.length-1)?0:this.current+1;this.fx[this.current].start(0);this.fx[a].start(1);this.current=a;return this},play:function(){if(this.status=='play')return this;this.status='play';this.timer=this.next.periodical(this.options.delay+this.options.duration,this);return this},stop:function(){this.status='stop';\$clear(this.timer);return this}});MooTicker.implement(new Options,new Events);
</script>
<script type="text/javascript">
         window.addEvent('domready', function() { //leave as domready so list won't display until ready
                 var x = new MooTicker('$id', {
                         controls: true,
                         delay: 2500,
                         duration: 600 });
         });
</script>
<div id="$id">
EOT;
		$retval = str_replace('{site_url}',$_CONF['site_url'] , $retval);
		$retval .= '<span class="tickertitle">' . $LANG_WIDGETS['latest_news'] . ':</span>';
		$retval .= $B['content'] . '</div>';
	}
	return $retval;
}
?>