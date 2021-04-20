<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* RSS Feeds
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

use \glFusion\Log\Log;

// $display = '';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    Log::write('system',Log::ERROR,'Someone has tried to access the Media Gallery Configuration page.  User id: '.$_USER['uid'].'/'.$_USER['username'].', IP: ' . $_SERVER['REMOTE_ADDR']);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_editRSS( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01;

    $retval = '';
    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file ('admin','rssedit.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);
    $rss_full_select = '<input type="checkbox" name="rss_full_enabled" value="1" ' . ($_MG_CONF['rss_full_enabled'] ? ' checked="checked"' : '') .'/>';

    $rss_type_select =  "<select name='rss_feed_type'>";
    $rss_type_select .= "<option value='RSS-2.0'"  . ($_MG_CONF['rss_feed_type'] == "RSS-2.0" ? ' selected="selected"' : "") . ">RSS2.0</option>";
    $rss_type_select .= "<option value='RSS-1.0'"  . ($_MG_CONF['rss_feed_type'] == "RSS-1.0" ? ' selected="selected"' : "") . ">RSS1.0</option>";
    $rss_type_select .= "<option value='RSS-0.91'" . ($_MG_CONF['rss_feed_type'] == "RSS-0.91" ? ' selected="selected"' : "") . ">RSS0.91</option>";
    $rss_type_select .= "<option value='PIE-0.1'"  . ($_MG_CONF['rss_feed_type'] == "PIE-0.1" ? ' selected="selected"' : "") . ">PIE0.1</option>";
    $rss_type_select .= "<option value='ATOM-1.0'" . ($_MG_CONF['rss_feed_type'] == "ATOM-1.0" ? ' selected="selected"' : "") . ">ATOM</option>";
    $rss_type_select .= "<option value='ATOM-0.3'" . ($_MG_CONF['rss_feed_type'] == "ATOM-0.3" ? ' selected="selected"' : "") . ">ATOM0.3</option>";
    $rss_type_select .= "</select>";

    $hide_email_select = '<input type="checkbox" name="hide_email" value="1"/>';

    $rss_ignore_empty_select = '<input type="checkbox" name="rss_ignore_empty" value="1" ' . ($_MG_CONF['rss_ignore_empty'] ? ' checked="checked"' : '') .'/>';
    $rss_anonymous_only_select = '<input type="checkbox" name="rss_anonymous_only" value="1" ' . ($_MG_CONF['rss_anonymous_only'] ? ' checked="checked"' : '') .'/>';

    $T->set_var(array(
        'lang_rss_options'          => $LANG_MG01['rss_options'],
        'lang_rss_full'             => $LANG_MG01['rss_full'],
        'lang_rss_type'             => $LANG_MG01['rss_type'],
        'lang_rss_ignore_empty'     => $LANG_MG01['rss_ignore_empty'],
        'lang_rss_anonymous_only'   => $LANG_MG01['rss_anonymous_only'],
        'lang_rss_feed_name'        => $LANG_MG01['rss_feed_name'],
        'lang_save'                 => $LANG_MG01['save'],
        'lang_cancel'               => $LANG_MG01['cancel'],
        'lang_reset'                => $LANG_MG01['reset'],
        'rss_full_select'           => $rss_full_select,
        'rss_type_select'           => $rss_type_select,
        'hide_email_select'         => $hide_email_select,
        'lang_hide_email'           => $LANG_MG01['hide_email'],
        'rss_ignore_empty_select'   => $rss_ignore_empty_select,
        'rss_anonymous_only_select' => $rss_anonymous_only_select,
        'rss_feed_name'             => $_MG_CONF['rss_feed_name'],
        's_form_action'             => $_MG_CONF['admin_url'] . 'rss.php'
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_saveRSS( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $_POST;

    $rss_full_enabled   = isset($_POST['rss_full_enabled']) ? COM_applyFilter($_POST['rss_full_enabled'],true) : 0;
    $rss_feed_type      = COM_applyFilter($_POST['rss_feed_type']);
    $rss_ignore_empty   = isset($_POST['rss_ignore_empty']) ? COM_applyFilter($_POST['rss_ignore_empty'],true) : 0;
    $rss_anonymous_only = isset($_POST['rss_anonymous_only']) ? COM_applyFilter($_POST['rss_anonymous_only'],true) : 0;
    $rss_feed_name      = COM_applyFilter($_POST['rss_feed_name']);
    $hide_email         = isset($_POST['hide_email']) ? COM_applyFilter($_POST['hide_email'],true) : 0;

    DB_save($_TABLES['mg_config'],"config_name, config_value","'rss_full_enabled','$rss_full_enabled'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'rss_feed_type','$rss_feed_type'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'rss_ignore_empty','$rss_ignore_empty'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'rss_anonymous_only','$rss_anonymous_only'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'rss_feed_name','$rss_feed_name'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'hide_author_email','$hide_email'");

    $_MG_CONF['rss_full_enabled'] = $rss_full_enabled;

    MG_buildFullRSS();

    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=6');
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
        'admin_body'    => MG_saveRSS(),
        'mg_navigation' => MG_navigation()
    ));
} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_editRSS(),
        'title'         => $LANG_MG01['rss_options'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#RSS_Feed_Options',
    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>