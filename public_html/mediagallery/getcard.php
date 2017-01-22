<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | getcard.php                                                              |
// |                                                                          |
// | User interface to retrieve electronic postcards                          |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2017 by the following authors:                        |
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

require_once '../lib-common.php';


if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
    $display = MG_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

require_once $_CONF['path'] . 'plugins/mediagallery/include/init.php';
MG_initAlbums();

$pcid = isset($_GET['id']) ? COM_applyFilter($_GET['id']) : 0;

$result = DB_query("SELECT * FROM {$_TABLES['mg_postcard']} WHERE pc_id='" . DB_escapeString($pcid) . "'");
$numRows = DB_numRows($result);

if ( $numRows < 1 ) {
    $display  = MG_siteHeader();
    $T = new Template( MG_getTemplatePath(0) );
    $T->set_file ('postcard', 'pc-error.thtml');
    $T->set_var(array(
        'lang_error_postcard'   => $LANG_MG03['error_postcard'],
        'lang_error_retrieve_text' => sprintf($LANG_MG03['error_retrieve_text'],$_MG_CONF['postcard_retention']),
        'lang_thank_you'        => $LANG_MG03['thank_you'],
        's_form_action'         =>  $_MG_CONF['site_url'] . '/postcard.php',
    ));
    $T->parse('output','postcard');
    $display .= $T->finish($T->get_var('output'));
    $display .= MG_siteFooter();
    echo $display;
    exit;
}

$P = DB_fetchArray($result);

$mid        = $P['mid'];
$toname     = $P['to_name'];
$toemail    = $P['to_email'];
$fromname   = $P['from_name'];
$fromemail  = $P['from_email'];
$subject    = $P['subject'];
$message    = $P['message'];

$retval = '';

$aid  = DB_getItem($_TABLES['mg_media_albums'], 'album_id','media_id="' . DB_escapeString($mid) . '"');

if ( $MG_albums[$aid]->access == 0 ) {
    $display  = MG_siteHeader();
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    $display .= MG_siteFooter();
    echo $display;
    exit;
}

$sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma LEFT JOIN " . $_TABLES['mg_media'] . " as m " .
        " ON ma.media_id=m.media_id WHERE m.media_id='" . DB_escapeString($mid) . "'";
$result = DB_query( $sql );
$nRows = DB_numRows( $result );
if ( $nRows < 1 ) {
    $display  = MG_siteHeader();
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    $display .= MG_siteFooter();
    echo $display;
    exit;
}
$M = DB_fetchArray($result);

// build the template...
$T = new Template( MG_getTemplatePath($aid) );
$T->set_file ('postcard', 'getcard.thtml');

$media_size        = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.jpg');

$T->set_var(array(
    's_form_action'     =>  $_MG_CONF['site_url'] . '/postcard.php',
    'mid'               =>  $mid,
    'media_title'       =>  $M['media_title'],
    'alt_media_title'   =>  htmlspecialchars(strip_tags($M['media_title'])),
    'media_description' =>  $M['media_description'],
    'media_url'         =>  $_MG_CONF['site_url'] . '/media.php?s=' . $mid,
    'media_image'       =>  $_MG_CONF['mediaobjects_url'] . '/disp/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.jpg',
    'site_url'          =>  $_MG_CONF['site_url'] . '/',
    'postcard_subject'  =>  $subject,
    'postcard_message'  =>  $message,
    'from_email'        =>  $fromemail,
    'site_name'         =>  $_CONF['site_name'],
    'site_slogan'       =>  $_CONF['site_slogan'],
    'to_name'           =>  $toname,
    'from_name'         =>  $fromname,
    'stamp_url'         =>  MG_getImageFile('stamp.gif'),
    'lang_to'           =>  $LANG_MG03['to'],
    'lang_from'         =>  $LANG_MG03['from'],
    'lang_send_to'      =>  $LANG_MG03['send_to'],
    'lang_subject'      =>  $LANG_MG03['subject'],
    'lang_send'         =>  $LANG_MG03['send'],
    'lang_cancel'       =>  $LANG_MG03['cancel'],
    'lang_preview'      =>  $LANG_MG03['preview'],
    'lang_message'      =>  $LANG_MG03['message_body'],
    'lang_send_from'    =>  $LANG_MG03['send_from'],
    'lang_postcard_from' => $LANG_MG03['postcard_from'],
    'lang_visit'        => $LANG_MG03['visit'],
));

$T->parse('output','postcard');
$retval .= $T->finish($T->get_var('output'));

$display = MG_siteHeader();
$display .= $retval;
$display .= MG_siteFooter();
echo $display;
?>