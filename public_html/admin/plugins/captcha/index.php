<?php
// +---------------------------------------------------------------------------+
// | CAPTCHA v3 Plugin                                                         |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | Admin Interface to CAPTCHA Plugin.                                        |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2007 by the following authors:                              |
// |                                                                           |
// | Author: mevans@ecsnet.com                                                 |
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

require_once('../../../lib-common.php');

function CP_array_sort($array, $key) {
    for ($i=0;$i<sizeof($array);$i++) {
        $sort_values[$i] = $array[$i][$key];
    }
    asort($sort_values);
    reset($sort_values);
    while (list($arr_key, $arr_val) = each($sort_values)) {
        $sorted_arr[] = $array[$arr_key];
    }
    return $sorted_arr;
}


// Only let admin users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the CAPTCHA Administration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG27[12]);
    $display .= $LANG27[12];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

$msg = '';

if ( isset($_POST['mode']) ) {
    $mode = COM_applyFilter($_POST['mode']);
} else {
    $mode = '';
}

if ( $mode == $LANG_CP00['cancel'] && !empty($LANG_CP00['cancel']) ) {
    header('Location:' . $_CONF['site_admin_url'] . '/moderation.php');
    exit;
}

if ( $mode == $LANG_CP00['save'] && !empty($LANG_CP00['save']) ) {
    $settings['anonymous_only']         = isset($_POST['anononly']) ? $_POST['anononly'] == 'on' ? 1 : 0 : 0;
    $settings['remoteusers']            = isset($_POST['remoteusers']) ? $_POST['remoteusers'] == 'on' ? 1 : 0 : 0;
    $settings['enable_comment']         = isset($_POST['comment']) ? $_POST['comment'] == 'on' ? 1 : 0 : 0;
    $settings['enable_story']           = isset($_POST['story']) ? $_POST['story'] == 'on' ? 1 : 0 : 0;
    $settings['enable_registration']    = isset($_POST['registration']) ? $_POST['registration'] == 'on' ? 1 : 0 : 0;
    $settings['enable_contact']         = isset($_POST['contact']) ? $_POST['contact'] == 'on' ? 1 : 0 : 0;
    $settings['enable_emailstory']      = isset($_POST['emailstory']) ? $_POST['emailstory'] == 'on' ? 1 : 0 : 0;
    $settings['enable_forum']           = isset($_POST['forum']) ? $_POST['forum'] == 'on' ? 1 : 0 : 0;
    $settings['enable_mediagallery']    = isset($_POST['mediagallery']) ? $_POST['mediagallery'] == 'on' ? 1: 0 : 0;
    $settings['enable_rating']          = isset($_POST['rating']) ? $_POST['rating'] == 'on' ? 1: 0 : 0;
    $settings['enable_links']           = isset($_POST['links']) ? $_POST['links'] == 'on' ? 1: 0 : 0;
    $settings['enable_calendar']        = isset($_POST['calendar']) ? $_POST['calendar'] == 'on' ? 1: 0 : 0;
    $settings['gfxDriver']              = COM_applyFilter($_POST['gfxdriver']);
    $settings['gfxFormat']              = isset($_POST['gfxformat']) ? COM_applyFilter($_POST['gfxformat']) : '';
    $settings['gfxPath']                = isset($_POST['gfxpath']) ? COM_applyFilter(COM_stripslashes($_POST['gfxpath'])) : '';
    $settings['debug']                  = isset($_POST['debug']) ? $_POST['debug'] == 'on' ? 1 : 0 : 0;
    $settings['imageset']               = isset($_POST['imageset']) ? COM_applyFilter($_POST['imageset']) : '';
    $settings['logging']                = isset($_POST['logging']) ? $_POST['logging'] == 'on' ? 1 : 0 : 0;

    foreach($settings AS $option => $value ) {
        $value = addslashes($value);
        DB_save($_TABLES['cp_config'],"config_name,config_value","'$option','$value'");
        $_CP_CONF[$option] = stripslashes($value);
    }
    $msg = $LANG_CP00['success'];
}

$display = '';
$display = COM_siteHeader();

$T = new Template($_CONF['path'] . 'plugins/captcha/templates');
$T->set_file (array ('admin' => 'admin.thtml'));

$imageset = array();
$i = 0;
$directory = $_CONF['path'] . 'plugins/captcha/images/static/';

$dh = @opendir($directory);
while ( ( $file = @readdir($dh) ) != false ) {
    if ( $file == '..' || $file == '.' ) {
        continue;
    }
    $imagedir = $directory . $file;
    if (@is_dir($imagedir)) {
        if ( file_exists($imagedir . '/' . 'imageset.inc') ) {
            include ( $imagedir . '/' . 'imageset.inc');
            $imageset[$i]['dir'] = $file;
            $imageset[$i]['name'] = $staticimageset['name'];
            $i++;
        }
    }
}
@closedir($dh);

if ( !isset($_CP_CONF['imageset']) ) {
    $_CP_CONF['imageset'] = '';
}

$sImageSet = CP_array_sort($imageset,'name');
$set_select = '<select name="imageset" id="imageset">';
for ( $i=0; $i < count($sImageSet); $i++ ) {
    $set_select .= '<option value="' . $sImageSet[$i]['dir'] . '"' . ($_CP_CONF['imageset'] == $sImageSet[$i]['dir'] ? ' selected="selected" ': '') .'>' . $sImageSet[$i]['name'] .  '</option>';
}
$set_select .= '</select>';

$T->set_var(array(
    'site_admin_url'            => $_CONF['site_admin_url'],
    'site_url'                  => $_CONF['site_url'],
    'anonchecked'               => ($_CP_CONF['anonymous_only'] ? ' checked="checked"' : ''),
    'remotechecked'             => ($_CP_CONF['remoteusers'] ? ' checked="checked"' : ''),
    'commentchecked'            => ($_CP_CONF['enable_comment'] ? ' checked="checked"' : ''),
    'storychecked'              => ($_CP_CONF['enable_story'] ? ' checked="checked"' : ''),
    'registrationchecked'       => ($_CP_CONF['enable_registration'] ? ' checked="checked"' : ''),
    'contactchecked'            => ($_CP_CONF['enable_contact'] ? ' checked="checked"' : ''),
    'emailstorychecked'         => ($_CP_CONF['enable_emailstory'] ? ' checked="checked"' : ''),
    'forumchecked'              => ($_CP_CONF['enable_forum'] ? ' checked="checked"' : ''),
    'mediagallerychecked'       => ($_CP_CONF['enable_mediagallery'] ? ' checked="checked"' : ''),
    'ratingchecked'             => ($_CP_CONF['enable_rating'] ? ' checked="checked"' : ''),
    'linkschecked'              => ($_CP_CONF['enable_links'] ? ' checked="checked"' : ''),
    'calendarchecked'           => ($_CP_CONF['enable_calendar'] ? ' checked="checked"' : ''),
    'gdselected'                => ($_CP_CONF['gfxDriver'] == 0 ? ' selected="selected"' : ''),
    'imselected'                => ($_CP_CONF['gfxDriver'] == 1 ? ' selected="selected"' : ''),
    'noneselected'              => ($_CP_CONF['gfxDriver'] == 2 ? ' selected="selected"' : ''),
    'jpgselected'               => ($_CP_CONF['gfxFormat'] == 'jpg' ? ' selected="selected"' : ''),
    'pngselected'               => ($_CP_CONF['gfxFormat'] == 'png' ? ' selected="selected"' : ''),
    'loggingchecked'            => ($_CP_CONF['logging'] ? ' checked="checked"' : ''),
    'gfxpath'                   => $_CP_CONF['gfxPath'],
    'debugchecked'              => ($_CP_CONF['debug'] ? ' checked="checked"' : ''),
    'lang_overview'             => sprintf($LANG_CP00['captcha_info'], 'http://www.gllabs.org/wiki/doku.php?id=captcha:start'),
    'lang_view_logfile'         => $LANG_CP00['view_logfile'],
    'lang_admin'                => $LANG_CP00['admin'],
    'lang_settings'             => $LANG_CP00['enabled_header'],
    'lang_anonymous_only'       => $LANG_CP00['anonymous_only'],
    'lang_enable_comment'       => $LANG_CP00['enable_comment'],
    'lang_enable_story'         => $LANG_CP00['enable_story'],
    'lang_enable_registration'  => $LANG_CP00['enable_registration'],
    'lang_enable_contact'       => $LANG_CP00['enable_contact'],
    'lang_enable_emailstory'    => $LANG_CP00['enable_emailstory'],
    'lang_enable_forum'         => $LANG_CP00['enable_forum'],
    'lang_enable_mediagallery'  => $LANG_CP00['enable_mediagallery'],
    'lang_enable_rating'        => $LANG_CP00['enable_rating'],
    'lang_enable_links'         => $LANG_CP00['enable_links'],
    'lang_enable_calendar'      => $LANG_CP00['enable_calendar'],
    'lang_save'                 => $LANG_CP00['save'],
    'lang_cancel'               => $LANG_CP00['cancel'],
    'lang_gfx_driver'           => $LANG_CP00['gfx_driver'],
    'lang_gfx_format'           => $LANG_CP00['gfx_format'],
    'lang_convert_path'         => $LANG_CP00['convert_path'],
    'lang_gd_libs'              => $LANG_CP00['gd_libs'],
    'lang_imagemagick'          => $LANG_CP00['imagemagick'],
    'lang_static_images'        => $LANG_CP00['static_images'],
    'lang_debug'                => $LANG_CP00['debug'],
    'lang_configuration'        => $LANG_CP00['configuration'],
    'lang_integration'          => $LANG_CP00['integration'],
    'lang_imageset'             => $LANG_CP00['image_set'],
    'lang_remoteusers'          => $LANG_CP00['remoteusers'],
    'lang_logging'              => $LANG_CP00['logging'],
    'selectImageSet'            => $set_select,
    'lang_msg'                  => $msg,
    'version'                   => $_CP_CONF['version'],
    's_form_action'             => $_CONF['site_admin_url'] . '/plugins/captcha/index.php',
));


$T->parse('output', 'admin');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;

?>