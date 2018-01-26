<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | Edit Media Gallery Member Albums defaults.                               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2017 by the following authors:                        |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_editMemberDefaults( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $LANG_MG03, $LANG_ACCESS, $LANG_DIRECTION;
    global $album_jumpbox, $album_selectbox, $MG_albums, $LANG04;

    MG_initAlbums();

    $retval = '';
    $T = new Template($_MG_CONF['template_path'].'/admin');

    $T->set_file(array(
        'admin'         =>  'editmember.thtml',
        'admin_formats' =>  'editalbum_formats.thtml',
    ));

    $navbar = new navbar;
    $navbar->add_menuitem($LANG_MG01['member_albums'],'showhideMGAdminEditorDiv("members",0);return false;',true);
    $navbar->add_menuitem($LANG_MG01['allowed_media_formats'],'showhideMGAdminEditorDiv("media",1);return false;',true);
    $navbar->add_menuitem($LANG_MG01['album_attributes'],'showhideMGAdminEditorDiv("attributes",2);return false;',true);
    $navbar->add_menuitem($LANG_MG01['anonymous_uploads_prompt'],'showhideMGAdminEditorDiv("useruploads",3);return false;',true);
    $navbar->add_menuitem($LANG_ACCESS['accessrights'],'showhideMGAdminEditorDiv("access",4);return false;',true);
    $navbar->set_selected($LANG_MG01['member_albums']);
    $T->set_var ('navbar', $navbar->generate());
    $T->set_var ('no_javascript_warning',$LANG04[150]);

    $T->set_var(array(
        'jpg_checked'   => ($_MG_CONF['member_valid_formats'] & MG_JPG ? ' checked="checked"' : ''),
        'png_checked'   => ($_MG_CONF['member_valid_formats'] & MG_PNG ? ' checked="checked"' : ''),
        'tif_checked'   => ($_MG_CONF['member_valid_formats'] & MG_TIF ? ' checked="checked"' : ''),
        'gif_checked'   => ($_MG_CONF['member_valid_formats'] & MG_GIF ? ' checked="checked"' : ''),
        'bmp_checked'   => ($_MG_CONF['member_valid_formats'] & MG_BMP ? ' checked="checked"' : ''),
        'tga_checked'   => ($_MG_CONF['member_valid_formats'] & MG_TGA ? ' checked="checked"' : ''),
        'psd_checked'   => ($_MG_CONF['member_valid_formats'] & MG_PSD ? ' checked="checked"' : ''),
        'mp3_checked'   => ($_MG_CONF['member_valid_formats'] & MG_MP3 ? ' checked="checked"' : ''),
        'ogg_checked'   => ($_MG_CONF['member_valid_formats'] & MG_OGG ? ' checked="checked"' : ''),
        'asf_checked'   => ($_MG_CONF['member_valid_formats'] & MG_ASF ? ' checked="checked"' : ''),
        'swf_checked'   => ($_MG_CONF['member_valid_formats'] & MG_SWF ? ' checked="checked"' : ''),
        'mov_checked'   => ($_MG_CONF['member_valid_formats'] & MG_MOV ? ' checked="checked"' : ''),
        'mp4_checked'   => ($_MG_CONF['member_valid_formats'] & MG_MP4 ? ' checked="checked"' : ''),
        'mpg_checked'   => ($_MG_CONF['member_valid_formats'] & MG_MPG ? ' checked="checked"' : ''),
        'zip_checked'   => ($_MG_CONF['member_valid_formats'] & MG_ZIP ? ' checked="checked"' : ''),
        'flv_checked'   => ($_MG_CONF['member_valid_formats'] & MG_FLV ? ' checked="checked"' : ''),
        'rflv_checked'  => ($_MG_CONF['member_valid_formats'] & MG_RFLV ? ' checked="checked"' : ''),
        'emb_checked'   => ($_MG_CONF['member_valid_formats'] & MG_EMB ? ' checked="checked"' : ''),
        'other_checked' => ($_MG_CONF['member_valid_formats'] & MG_OTHER ? ' checked="checked"' : ''),
        'lang_jpg'              => $LANG_MG01['jpg'],
        'lang_png'              => $LANG_MG01['png'],
        'lang_tif'              => $LANG_MG01['tif'],
        'lang_gif'              => $LANG_MG01['gif'],
        'lang_bmp'              => $LANG_MG01['bmp'],
        'lang_tga'              => $LANG_MG01['tga'],
        'lang_psd'              => $LANG_MG01['psd'],
        'lang_mp3'              => $LANG_MG01['mp3'],
        'lang_ogg'              => $LANG_MG01['ogg'],
        'lang_asf'              => $LANG_MG01['asf'],
        'lang_swf'              => $LANG_MG01['swf'],
        'lang_mov'              => $LANG_MG01['mov'],
        'lang_mp4'              => $LANG_MG01['mp4'],
        'lang_mpg'              => $LANG_MG01['mpg'],
        'lang_zip'              => $LANG_MG01['zip'],
        'lang_flv'              => $LANG_MG01['flv'],
        'lang_rflv'             => $LANG_MG01['rflv'],
        'lang_emb'              => $LANG_MG01['emb'],
        'lang_other'            => $LANG_MG01['other'],
        'lang_allowed_formats'  => $LANG_MG01['allowed_media_formats'],
        'lang_image'            => $LANG_MG01['image'],
        'lang_audio'            => $LANG_MG01['audio'],
        'lang_video'            => $LANG_MG01['video'],
    ));
    $T->parse('valid_formats','admin_formats');

    $member_albums   = '<input type="checkbox" name="member_albums" value="1" ' . ($_MG_CONF['member_albums'] ? ' checked="checked"' : '') . '/>';
    $auto_create     = '<input type="checkbox" name="auto_create" value="1" '   . ($_MG_CONF['member_auto_create'] ? ' checked="checked"' : '') . '/>';
    $allow_create    = '<input type="checkbox" name="allow_create" value="1" '  . ($_MG_CONF['member_create_new'] ? ' checked="checked"' : '') . '/>';

    $album_jumpbox = '';
    $MG_albums[0]->buildJumpBox($_MG_CONF['member_album_root']);

    $album_list_root  = '<select name="member_root">';
    $album_list_root .= '<option value="0">' . $LANG_MG01['root_album'] . '</option>';
    $album_list_root .= $album_jumpbox;
    $album_list_root .= '</select>';

    $MG_albums[0]->buildAlbumBox($_MG_CONF['member_album_archive'],3,-1,'upload');
    $member_archive   = '<select name="member_archive">';
    $member_archive .= '<option value="0">' . $LANG_MG01['do_not_archive'] . '</option>';
    $member_archive .= $album_selectbox;
    $member_archive .= '</select>';

    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $ri_select              = '<input type="checkbox" name="enable_random" value="1" ' . ($_MG_CONF['member_enable_random'] ? ' checked="checked"' : '') . '/>';
    $max_image_height_input = '<input type="text" size="4" name="max_image_height" value="' . $_MG_CONF['member_max_height'] . '"' . '/>';
    $max_image_width_input  = '<input type="text" size="4" name="max_image_width" value="' . $_MG_CONF['member_max_width'] . '"' . '/>';
    $max_filesize_input     = '<input type="text" size="10" name="max_filesize" value="' . $_MG_CONF['member_max_filesize'] . '"' . '/>';
    $email_mod_select       = '<input type="checkbox" name="email_mod" value="1" ' . ($_MG_CONF['member_email_mod'] ? ' checked="checked"' : '') . '/>';

    // permission template

    $usergroups = SEC_getUserGroups();
    $groupdd = '';
    $moddd = '';

    $gresult = DB_query("SELECT grp_id FROM {$_TABLES['groups']} WHERE grp_name LIKE 'mediagallery Admin'");
    $grow = DB_fetchArray($gresult);
    $grp_id = $grow['grp_id'];
    if ( !isset($_MG_CONF['ad_group_id'])) {
        $_MG_CONF['ad_group_id'] = $grp_id;
    }
    if ( !isset($_MG_CONF['member_mod_group_id']) ) {
        $_MG_CONF['member_mod_group_id'] = $grp_id;
    }

    $groupdd .= '<select name="group_id">';
    $moddd .= '<select name="mod_id">';
    for ($i = 0; $i < count($usergroups); $i++) {
        $groupdd .= '<option value="' . $usergroups[key($usergroups)] . '"';
        $moddd   .= '<option value="' . $usergroups[key($usergroups)] . '"';
        if ($_MG_CONF['ad_group_id'] == $usergroups[key($usergroups)]) {
            $groupdd .= ' selected="selected"';
            $groupname = key($usergroups);
        }
        if ($_MG_CONF['member_mod_group_id'] == $usergroups[key($usergroups)]) {
            $moddd   .= ' selected="selected"';
        }
        $groupdd .= '>' . key($usergroups) . '</option>';
        $moddd   .= '>' . key($usergroups) . '</option>';
        next($usergroups);
    }
    $groupdd .= '</select>';
    $moddd .= '</select>';

    $upload_select   = '<input type="checkbox" name="uploads" value="1" ' . ($_MG_CONF['member_uploads'] ? ' checked="checked"' : '') . '/>';
    $moderate_select = '<input type="checkbox" name="moderate" value="1" ' . ($_MG_CONF['member_moderate'] ? ' checked="checked"' : '') . '/>';

    if ( !isset($_MG_CONF['member_use_fullname']) ) {
        $_MG_CONF['member_use_fullname'] = 0;
    }
    $fullname_select = '<input type="checkbox" name="member_use_fullname" value="1" ' . ($_MG_CONF['member_use_fullname'] ? ' checked="checked"' : '') . '/>';
    if ( !isset($_MG_CONF['feature_member_album']) ) {
        $_MG_CONF['feature_member_album'] = 0;
    }
    $feature_select = '<input type="checkbox" name="feature_member_album" value="1" ' . ($_MG_CONF['feature_member_album'] ? ' checked="checked"' : '') . '/>';
    if ( !isset($_MG_CONF['allow_remote']) ) {
        $_MG_CONF['allow_remote'] = 0;
    }
    $allow_remote = '<input type="checkbox" name="allow_remote" value="1" ' . ($_MG_CONF['allow_remote'] ? ' checked="checked"' : '') . '/>';

    $T->set_var(array(
        'site_url'              => $_MG_CONF['site_url'],
        'member_albums'         => $member_albums,
        'album_list_root'       => $album_list_root,
        'member_archive'        => $member_archive,
        'auto_create'           => $auto_create,
        'allow_create'          => $allow_create,
        'ri_select'             => $ri_select,
        'height_input'          => $max_image_height_input,
        'width_input'           => $max_image_width_input,
        'email_mod_select'      => $email_mod_select,
        'uploads'               => $upload_select,
        'moderate'              => $moderate_select,
        'member_quota'          => $_MG_CONF['member_quota'] /  1048576,
        'max_filesize'          => $_MG_CONF['member_max_filesize'] / 1024,
        'member_use_fullname'   => $fullname_select,
        'feature_member_album'  => $feature_select,
        'allow_remote'          => $allow_remote,
        'lang_uploads'          => $LANG_MG01['anonymous_uploads_prompt'],
        'lang_accessrights'     => $LANG_ACCESS['accessrights'],
        'lang_owner'            => $LANG_ACCESS['owner'],
        'lang_group'            => $LANG_ACCESS['group'],
        'lang_permissions'      => $LANG_ACCESS['permissions'],
        'lang_perm_key'         => $LANG_ACCESS['permissionskey'],
        'permissions_editor'    => SEC_getPermissionsHTML($_MG_CONF['member_perm_owner'],$_MG_CONF['member_perm_group'],$_MG_CONF['member_perm_members'],$_MG_CONF['member_perm_anon']),
        'permissions_msg'       => $LANG_ACCESS['permmsg'],
        'group_dropdown'        => $groupdd,
        'mod_dropdown'          => $moddd,
        'lang_member_upload'    => $LANG_MG01['member_upload'],
        'lang_moderate_album'   => $LANG_MG01['mod_album'],
        'lang_mod_group'        => $LANG_MG01['moderation_group'],
        'lang_zero_unlimited'   => $LANG_MG01['zero_unlimited'],
        'lang_ri_enable'        => $LANG_MG01['ri_enable'],
        'lang_max_image_height' => $LANG_MG01['max_image_height'],
        'lang_max_image_width'  => $LANG_MG01['max_image_width'],
        'lang_max_filesize'     => $LANG_MG01['max_filesize'],
        'lang_display_image_size' => $LANG_MG01['display_image_size'],
        'lang_email_mods_on_submission' => $LANG_MG01['email_mods_on_submission'],
        'lang_album_attributes' => $LANG_MG01['album_attributes'],
        'lang_member_albums'    => $LANG_MG01['member_albums'],
        'lang_enable_member_albums' => $LANG_MG01['enable_member_albums'],
        'lang_member_quota'     => $LANG_MG01['default_member_quota'],
        'lang_auto_create'      => $LANG_MG01['auto_create'],
        'lang_allow_create'     => $LANG_MG01['allow_create'],
        'lang_member_root'      => $LANG_MG01['member_root'],
        'lang_member_archive'   => $LANG_MG01['member_archive'],
        'lang_member_use_fullname' => $LANG_MG01['member_use_fullname'],
        'lang_feature_member_album' => $LANG_MG01['feature_member_album'],
        'lang_allow_remote'     => $LANG_MG01['allow_remote'],
        'lang_save'             => $LANG_MG01['save'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        's_form_action'         => $_MG_CONF['admin_url'] . 'member.php',
        'rtl'                   => $LANG_DIRECTION == "rtl" ? "rtl" : "",
    ));
    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_saveMemberDefaults( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $_POST;

    $member_albums          = isset($_POST['member_albums']) ? COM_applyFilter($_POST['member_albums'],true) : 0;
    $member_quota           = COM_applyFilter($_POST['member_quota'],true) * 1048576;
    $auto_create            = isset($_POST['auto_create']) ? COM_applyFilter($_POST['auto_create'],true) : 0;
    $allow_create           = isset($_POST['allow_create']) ? COM_applyFilter($_POST['allow_create'],true) : 0;
    $member_use_fullname    = isset($_POST['member_use_fullname']) ? COM_applyFilter($_POST['member_use_fullname'],true) : 0;
    $feature_member_album   = isset($_POST['feature_member_album']) ? COM_applyFilter($_POST['feature_member_album'],true) : 0;
    $allow_remote           = isset($_POST['allow_remote']) ? COM_applyFilter($_POST['allow_remote'],true) : 0;
    $member_root            = isset($_POST['member_root']) ? COM_applyFilter($_POST['member_root'],true) : 0;
    $member_archive         = isset($_POST['member_archive']) ? COM_applyFilter($_POST['member_archive'],true) : 0;
    $enable_random          = isset($_POST['enable_random']) ? COM_applyFilter($_POST['enable_random'],true) : 0;
    $max_image_width        = COM_applyFilter($_POST['max_image_width'],true);
    $max_image_height       = COM_applyFilter($_POST['max_image_height'],true);
    $max_filesize           = COM_applyFilter($_POST['max_filesize'],true) * 1024;
    $uploads                = isset($_POST['uploads']) ? COM_applyFilter($_POST['uploads'],true) : 0;
    $moderate               = isset($_POST['moderate']) ? COM_applyFilter($_POST['moderate'],true) : 0;
    $mod_id                 = COM_applyFilter($_POST['mod_id'],true);
    $email_mod              = isset($_POST['email_mod']) ? COM_applyFilter($_POST['email_mod'],true) : 0;
    $tperm_owner            = isset($_POST['perm_owner']) ? $_POST['perm_owner'] : 0;
    $tperm_group            = isset($_POST['perm_group']) ? $_POST['perm_group'] : 0;
    $tperm_members          = isset($_POST['perm_members']) ? $_POST['perm_members'] : 0;
    $tperm_anon             = isset($_POST['perm_anon']) ? $_POST['perm_anon'] : 0;
    list($perm_owner,$perm_group,$perm_members,$perm_anon) = SEC_getPermissionValues($tperm_owner,$tperm_group,$tperm_members,$tperm_anon);

    // valid media formats....
    $format_jpg                 = isset($_POST['format_jpg']) ? COM_applyFilter($_POST['format_jpg'],true) : 0;
    $format_png                 = isset($_POST['format_png']) ? COM_applyFilter($_POST['format_png'],true) : 0;
    $format_tif                 = isset($_POST['format_tif']) ? COM_applyFilter($_POST['format_tif'],true) : 0;
    $format_gif                 = isset($_POST['format_gif']) ? COM_applyFilter($_POST['format_gif'],true) : 0;
    $format_bmp                 = isset($_POST['format_bmp']) ? COM_applyFilter($_POST['format_bmp'],true) : 0;
    $format_tga                 = isset($_POST['format_tga']) ? COM_applyFilter($_POST['format_tga'],true) : 0;
    $format_psd                 = isset($_POST['format_psd']) ? COM_applyFilter($_POST['format_psd'],true) : 0;
    $format_mp3                 = isset($_POST['format_mp3']) ? COM_applyFilter($_POST['format_mp3'],true) : 0;
    $format_ogg                 = isset($_POST['format_ogg']) ? COM_applyFilter($_POST['format_ogg'],true) : 0;
    $format_asf                 = isset($_POST['format_asf']) ? COM_applyFilter($_POST['format_asf'],true) : 0;
    $format_swf                 = isset($_POST['format_swf']) ? COM_applyFilter($_POST['format_swf'],true) : 0;
    $format_mov                 = isset($_POST['format_mov']) ? COM_applyFilter($_POST['format_mov'],true) : 0;
    $format_mp4                 = isset($_POST['format_mp4']) ? COM_applyFilter($_POST['format_mp4'],true) : 0;
    $format_mpg                 = isset($_POST['format_mpg']) ? COM_applyFilter($_POST['format_mpg'],true) : 0;
    $format_zip                 = isset($_POST['format_zip']) ? COM_applyFilter($_POST['format_zip'],true) : 0;
    $format_other               = isset($_POST['format_other']) ? COM_applyFilter($_POST['format_other'],true) : 0;
    $format_flv                 = isset($_POST['format_flv']) ? COM_applyFilter($_POST['format_flv'],true) : 0;
    $format_rflv                = isset($_POST['format_rflv']) ? COM_applyFilter($_POST['format_rflv'],true) : 0;
    $format_emb                 = isset($_POST['format_emb']) ? COM_applyFilter($_POST['format_emb'],true) : 0;
    $member_valid_formats       = ($format_jpg + $format_png + $format_tif + $format_gif + $format_bmp + $format_tga + $format_psd + $format_mp3 + $format_ogg + $format_asf + $format_swf + $format_mov + $format_mp4 + $format_mpg + $format_zip + $format_other + $format_flv + $format_rflv + $format_emb);

    // put any error checking / validation here

    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_albums','$member_albums'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_use_fullname','$member_use_fullname'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'feature_member_album','$feature_member_album'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'allow_remote','$allow_remote'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_quota','$member_quota'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_auto_create','$auto_create'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_create_new','$allow_create'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_album_root','$member_root'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_album_archive','$member_archive'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_enable_random','$enable_random'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_max_width','$max_image_width'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_max_height','$max_image_height'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_max_filesize','$max_filesize'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_uploads','$uploads'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_moderate','$moderate'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_mod_group_id','$mod_id'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_email_mod','$email_mod'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_perm_owner','$perm_owner'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_perm_group','$perm_group'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_perm_members','$perm_members'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_perm_anon','$perm_anon'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'member_valid_formats','$member_valid_formats'");

    $c = glFusion\Cache::getInstance()->deleteItemsByTag('menu');

    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=12');
    exit;
}

/**
* Main
*/

$display = '';
$mode = '';

if (isset ($_POST['mode'])) {
    $mode = COM_applyFilter($_POST['mode']);
} else if (isset ($_GET['mode'])) {
    $mode = COM_applyFilter($_GET['mode']);
}

$T = new Template($_MG_CONF['template_path'].'/admin');
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],
));

if ($mode == $LANG_MG01['save'] && !empty ($LANG_MG01['save'])) {   // save the config
    $T->set_var(array(
        'admin_body'    => MG_saveMemberDefaults(),
    ));
} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_editMemberDefaults(),
        'title'         => $LANG_MG01['member_album_options'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"' . '/>',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Member_Album_Options',

    ));
}
$T->parse('output', 'admin');
$display = COM_siteHeader('menu','');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>