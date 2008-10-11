<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// |lib-widgets.php                                                            |
// |                                                                          |
// | A place for widget functions, mootools based or otherwise |
// +--------------------------------------------------------------------------+
// | $Id:: lib-widgets.php 3334 2008-10-10 02:31:21Z ewarren                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Joe Mucchiello         jmucchiello AT yahoo DOT com                      |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Eric Warren         eakwarren AT gmail DOT com                      |
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
				}
		});
	});
</script>
 
<div id="$id" class="gl_slide">
EOJ;

    $sql = "SELECT sp_content, sp_php, sp_title FROM {$_TABLES['staticpage']} WHERE sp_id in ("
         . implode(', ', array_map(create_function('$a','return "\'" . htmlspecialchars($a) . "\'";'), $pages))
         . ')';
    
    $res = DB_query($sql);
    for ($i = 0; $A = DB_fetchArray($res); ++$i) {
        $content = SP_render_content($A['sp_content'], $A['sp_php']);
        $display .= <<<EOS
<div class="tab-pane" id="tab-$i-pane">
	<h1 class="tab-title">{$A['sp_title']}</h1>
	<div>$content</div>
</div>
EOS;
    }

    $display .= '</div>';

    return $display;
}
// add you own static pages here.
//$slides = Array('staticpage id 1', 'staticpage id 2');
//$display = COM_siteHeader();
// test the parameters, or not
//$display .= WIDGET_mooslide($slides);
//$display .= WIDGET_mooslide($slides, 550, 160, 'div id of mooSlide');
//$display .= COM_siteFooter();
//echo $display;


// A portal staticpage (that bases itself on a user created proper portal block called gl_mootickerRSS)
//modified from LWC's forum post http://www.geeklog.net/forum/viewtopic.php?showtopic=67396 by Mark R. Evans and Joe Mucchiello
function WIDGET_mootickerRSS() {
	global $_CONF, $_TABLES;
	$retval = '';
	$result = DB_query("SELECT *, rdfupdated as date FROM
{$_TABLES['blocks']} WHERE name='gl_mootickerRSS'");
	$numRows = DB_numRows($result);
	if ( $numRows < 1 || $result == NULL ) {
		return $retval;
	}
	$B = DB_fetchArray($result);
	if ( $B['is_enabled'] == 0 ) {
		$retval = <<<EOT
<script type="text/javascript"
src="{site_url}/javascript/mootools/gl_mooticker.js"></script>
<script type="text/javascript">
         window.addEvent('load', function() {
                 var x = new MooTicker('gl_mooticker', {
                         controls: true,
                         delay: 2500,
                         duration: 600 });
         });
</script>
<div id="gl_mooticker">
   <span class="tickertitle">Latest News:</span>
EOT;
		$retval = str_replace('{site_url}',$_CONF['site_url'] , $retval);
		$retval .= $B['content'] . '</div>';
	}
	return $retval;
}
?>