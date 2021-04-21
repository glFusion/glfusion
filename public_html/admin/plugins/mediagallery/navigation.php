<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Admin Menu
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';

MG_initAlbums();

$TEMPLATE_OPTIONS['default_vars'] = array_merge($TEMPLATE_OPTIONS['default_vars'],array('true_site_admin_url' => $_CONF['site_admin_url']));

function MG_navigation() {
    global $_MG_CONF, $_CONF, $_TABLES, $LANG_MG01;

    $retval = '';

    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file ('admin','mg_navigation.thtml');

    $queue_count = DB_count($_TABLES['mg_media_album_queue'],'','');

    $T->set_var(array(
        'site_url'                  => $_MG_CONF['site_url'],
        'admin_url'                 => $_MG_CONF['admin_url'],
        'lang_configuration'        => $LANG_MG01['configuration'],
        'lang_system_options'       => $LANG_MG01['system_options'],
        'lang_exif_iptc'            => $LANG_MG01['exif_admin_header'],
        'lang_categories'           => $LANG_MG01['category_manage_help'],
        'lang_system_defaults'      => $LANG_MG01['system_default_editor'],
        'lang_album_defaults'       => $LANG_MG01['album_default_editor'],
        'lang_av_defaults'          => $LANG_MG01['av_default_editor'],
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
        'lang_glstory'              => $LANG_MG01['gl_story'],
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}
?>