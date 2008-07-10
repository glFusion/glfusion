<?php
// +---------------------------------------------------------------------------+
// | VideoEmbed Plugin for FCKeditor                                           |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                              |
// |                                                                           |
// | Author:                                                                   |
// | Mark R. Evans              - mark AT gllabs DOT org                       |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

require_once('../../../../lib-common.php');

$ve_base_path = '/fckeditor/editor/plugins/video';

$langfile = $_CONF['path_html'] . $ve_base_path . '/langs/' . $_CONF['language'] . '.php';

if (file_exists ($langfile)) {
    include_once ($langfile);
} else {
    include_once ($_CONF['path_html'] . $ve_base_path . '/langs/english.php');
}

function VE_popupHeader($pagetitle = '') {
    global $_CONF, $LANG_CHARSET,$LANG_DIRECTION,$ve_base_path,$jslangfile;

    // send out the charset header

    if( empty( $LANG_CHARSET )) {
        $charset = $_CONF['default_charset'];
        if( empty( $charset )) {
            $charset = 'iso-8859-1';
        }
    } else {
        $charset = $LANG_CHARSET;
    }
    header ('Content-Type: text/html; charset=' . $charset);
	$header = new Template($_CONF['path_html'] . $ve_base_path . '/templates');
    $header->set_file( array(
        'header'        => 'embed.thtml',
    ));
    if( empty( $pagetitle ) && isset( $_CONF['pagetitle'] )) {
        $pagetitle = $_CONF['pagetitle'];
    }
    $header->set_var( 'page_title', $_CONF['site_name'] . ' :: ' . $pagetitle );

    $header->set_var( 'site_url', $_CONF['site_url'] );
    $header->set_var( 'site_name', $_CONF['site_name'] );
    $header->set_var( 'css_url', $_CONF['site_url'] . $ve_base_path . '/css/style.css' );
    $header->set_var( 'js_url', $_CONF['site_url'] . $ve_base_path . '/jscripts/functions.js');

    if( empty( $LANG_CHARSET )) {
        $charset = $_CONF['default_charset'];
        if( empty( $charset )) {
            $charset = 'iso-8859-1';
        }
    } else {
        $charset = $LANG_CHARSET;
    }

    $header->set_var( 'charset', $charset );
    if( empty( $LANG_DIRECTION )) {
        // default to left-to-right
        $header->set_var( 'direction', 'ltr' );
    } else {
        $header->set_var( 'direction', $LANG_DIRECTION );
    }

    $header->parse( 'output', 'header' );
    $retval = $header->finish ($header->get_var('output'));
    return $retval;
}
function VE_popupFooter() {

	$retval = '</body></html>';
	return $retval;
}

/*
* Main Function
*/

$instance  = COM_applyFilter($_REQUEST['i']);

$T = new Template($_CONF['path_html'] . $ve_base_path . '/templates');

$T->set_file (array(
    'body'		=> 'embed_body.thtml',
));

$alignment_select  = '<select name="alignment">';
$alignment_select .= '<option value="none">' . $LANG_ve['none'] . '</option>';
$alignment_select .= '<option value="left">' . $LANG_ve['left'] . '</option>';
$alignment_select .= '<option value="right">' . $LANG_ve['right'] . '</option>';
$alignment_select .= '<option value="center">' . $LANG_ve['center'] . '</option>';
$alignment_select .= '</select>';

$T->set_var(array(
    'instance'  =>  $instance,
    's_form_action'			=> $_SERVER['PHP_SELF'],
    'lang_insert'			=> $LANG_ve['insert'],
    'lang_cancel'			=> $LANG_ve['cancel'],
    'lang_title'            => $LANG_ve['title'],
    'lang_help'             => $LANG_ve['help'],
    'lang_alignment'        => $LANG_ve['alignment'],
    'align_select'          => $alignment_select,
));
$T->parse('output','body');

ob_start();
echo VE_popupHeader('Video Embed');
echo $T->finish($T->get_var('output'));
echo VE_popupFooter();
$data = ob_get_contents();
ob_end_clean();
echo $data;
exit;
?>