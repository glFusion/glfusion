<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// | Admin menu.                                                              |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2010 by the following authors:                        |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

function MG_navigation() {
    global $_MG_CONF, $_CONF, $_TABLES, $LANG_MG01;

    $retval = '';
    $T = new Template($_MG_CONF['template_path']);
    $T->set_file ('admin','mg_navigation.thtml');

    $queue_count = DB_count($_TABLES['mg_media_album_queue'],'','');

    $T->set_var(array(
        'site_url'                  => $_MG_CONF['site_url'],
        'site_admin_url'            => $_CONF['site_admin_url'],
        'admin_url'                 => $_MG_CONF['admin_url'],
        'lang_configuration'        => $LANG_MG01['configuration'],
        'lang_system_options'       => $LANG_MG01['system_options'],
        'lang_exif_iptc'            => $LANG_MG01['exif_admin_header'],
        'lang_categories'           => $LANG_MG01['category_manage_help'],
        'lang_system_defaults'      => $LANG_MG01['system_default_editor'],
        'lang_album_defaults'       => $LANG_MG01['album_default_editor'],
        'lang_av_defaults'          => $LANG_MG01['av_default_editor'],
        'lang_media_queue'          => sprintf("%s (%d)",$LANG_MG01['media_queue'],$queue_count),
        'lang_reports'              => $LANG_MG01['reports'],
        'lang_usage_reports'        => $LANG_MG01['usage_reports'],
        'lang_quota_reports'        => $LANG_MG01['quota_reports'],
        'lang_batch_sessions'       => $LANG_MG01['batch_sessions'],
        'lang_paused_sessions'      => $LANG_MG01['paused_sessions'],
        'lang_rebuild_thumbs'       => $LANG_MG01['rebuild_thumb'],
        'lang_resize_images'        => $LANG_MG01['resize_display'],
        'lang_remove_originals'     => $LANG_MG01['discard_originals'],
        'lang_utilities'            => $LANG_MG01['utilities'],
        'lang_logviewer'            => $LANG_MG01['log_viewer'],
        'lang_php_info'             => $LANG_MG01['phpinfo'],
        'lang_documentation'        => $LANG_MG01['documentation'],
        'lang_import_wizards'       => $LANG_MG01['import_wizards'],
        'lang_inmemoriam'           => $LANG_MG01['inm_import'],
        'lang_4images'              => $LANG_MG01['fourimages_import'],
        'lang_gallery1'             => $LANG_MG01['gallery_import'],
        'lang_gallery2'             => $LANG_MG01['gallery_v2_import'],
        'lang_coppermine'           => $LANG_MG01['coppermine_import'],
        'lang_geekary'              => $LANG_MG01['geekary_import'],
        'lang_xp_publishing'        => $LANG_MG01['xppubwizard_install'],
        'session_count'             => DB_count($_TABLES['mg_sessions'],'session_status','1'),
        'lang_member_album_options' => $LANG_MG01['member_album_options'],
        'lang_rebuild_quota'        => $LANG_MG01['rebuild_quota'],
        'lang_batch_create_members' => $LANG_MG01['batch_create_members'],
        'lang_member_albums'        => $LANG_MG01['member_albums'],
        'lang_static_sort_albums'   => $LANG_MG01['static_sort_albums'],
        'lang_static_sort_media'    => $LANG_MG01['static_sort_media'],
        'lang_mass_delete'          => $LANG_MG01['batch_delete_albums'],
        'lang_rss_options'          => $LANG_MG01['rss_options'],
        'lang_reset_member_attr'    => $LANG_MG01['reset_members'],
        'lang_rss_rebuild_all'      => $LANG_MG01['rss_rebuild_all'],
        'lang_rss_rebuild_album'    => $LANG_MG01['rss_rebuild_album'],
        'lang_rss_feeds'            => $LANG_MG01['rss_feeds'],
        'lang_album_sort'           => $LANG_MG01['sort_albums'],
        'lang_global_attr'          => $LANG_MG01['globalattr'],
        'lang_global_perm'          => $LANG_MG01['globalperm'],
        'lang_member_purge_album'   => $LANG_MG01['purge_member_albums'],
        'lang_reset_defaults'       => $LANG_MG01['reset_defaults'],
        'lang_filecheck'            => $LANG_MG01['filecheck'],
        'lang_4images'              => $LANG_MG01['fourimages_import'],
        'lang_glstory'              => $LANG_MG01['gl_story'],
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}
?>