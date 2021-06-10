<?php
/**
* glFusion CMS
*
* glFusion Admin Interface
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Jason Whittenburg    jwhitten AT securitygeeks DOT com
*  Based on prior work Copyright (C) 2008-2010 by the following authors:
*  Joe Mucchiello       joe AT throwingdice DOT com
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database\Database;

// MAIN
if (isset($_GET['mode']) && ($_GET['mode'] == 'logout')) {
    print COM_refresh($_CONF['site_url'] . '/users.php?mode=logout');
}

// this defines the amount of icons displayed next to another in the CC-block
define ('ICONS_PER_ROW', 6);

function _checkUpgrades()
{
    global $_CONF, $_TABLES, $LANG_UPGRADE;

    $retval = '';

    $db = Database::getInstance();

    $lastrun = $db->getItem( $_TABLES['vars'], 'value', array('name' => 'updatecheck'));
    if ( $lastrun + $_CONF['update_check_interval'] <= time() ) {
        // run version check
        list($upToDate,$pluginsUpToDate,$pluginData) = _checkVersion();
        if ( $upToDate == 0 || $pluginsUpToDate == 0 ) {
            $T = new Template($_CONF['path_layout']);
            $T->set_file('alert', 'alert.thtml');
            $T->set_var('alert_msg', sprintf($LANG_UPGRADE['updates_available'],$_CONF['site_admin_url']));
            $retval = $T->finish($T->parse('output', 'alert'));
        }
        $db->conn->query("REPLACE INTO `{$_TABLES['vars']}` (name, value) VALUES ('updatecheck',UNIX_TIMESTAMP())");
    }
    return $retval;
}

/**
* Renders an entry (icon) for the "Command and Control" center
*
* @param    template    $template   template to use
* @param    string      $url        URL the entry links to
* @param    string      $image      URL of the icon
* @param    string      $label      text to use under the icon
* @return   void
*
*/
function render_cc_item (&$template, $url = '', $image = '', $label = '', $count = 0)
{
    if (!empty ($url)) {
        $template->set_var ('page_url', $url);
        $template->set_var ('page_image', $image);
        $template->set_var ('option_label', $label);
        $template->set_var ('cell_width', ((int)(100 / ICONS_PER_ROW)) . '%');
        if ( $count > 0 ) {
            $template->set_var('count',$count);
        } else {
            $template->unset_var('count');
        }

        return $template->parse ('cc_main_options', 'ccitem', false);
    }

    return '';
}

/**
* Prints the command & control block at the top
*
* TODO: The moderation items should be displayed with the help of <ul><li>
* instead of div's.
*
*/
function commandcontrol()
{
    global $_CONF, $_TABLES, $LANG01, $LANG_MB01, $LANG_AM, $LANG_LOGO, $LANG29, $LANG_LOGVIEW, $LANG_SOCIAL, $_IMAGE_TYPE;

    USES_lib_comment();

    $db = Database::getInstance();

    $retval = '';

    $T = new Template($_CONF['path_layout'] . 'admin/moderation');
    $T->set_file('cc', 'moderation.thtml');
    $T->set_var(array(
        'gversion'  => GVERSION,
        'patchlevel' => PATCHLEVEL,
        'title' => $LANG29[34],
    ) );

    $showTrackbackIcon = (
        ($_CONF['trackback_enabled'] || $_CONF['pingback_enabled'] || $_CONF['ping_enabled'])
        && SEC_hasRights('story.ping')
    );

    // Get counters and other elements for certain options
    $doclang = COM_getLanguageName();
    if ( @file_exists($_CONF['path_html'] . 'docs/' . $doclang . '/index.html') ) {
        $docUrl = $_CONF['site_url'].'/docs/'.$doclang.'/index.html';
    } else {
        $docUrl = $_CONF['site_url'].'/docs/english/index.html';
    }

    $modnum = 0;
    /*if (
        SEC_hasRights( 'story.edit,story.moderate', 'OR' ) ||
        (( $_CONF['usersubmission'] == 1 ) && SEC_hasRights( 'user.edit,user.delete' ))
    ) {*/
        if ( SEC_hasRights( 'story.moderate' )) {
            $modnum = $db->getCount($_TABLES['storysubmission']);
        }

    if (
        $_CONF['usersubmission'] == 1 &&
        SEC_hasRights('user.edit,user.delete')
    ) {
        $modnum += $db->getCount($_TABLES['users'], 'status', 2, Database::INTEGER);
    }
    // add the submission count for plugins
    $modnum += PLG_getSubmissionCount();

    $cc_arr = array(
        $LANG01[11] => array(
            'condition' => SEC_hasRights('story.edit'),
            'url' => $_CONF['site_admin_url'] . '/story.php',
            'lang' => $LANG01[11],
            'image' => $_CONF['layout_url'] . '/images/icons/story.' . $_IMAGE_TYPE,
        ),
        $LANG01[12] => array(
            'condition' => SEC_hasRights('block.edit'),
            'url' => $_CONF['site_admin_url'] . '/block.php',
            'lang' => $LANG01[12],
            'image' => $_CONF['layout_url'] . '/images/icons/block.' . $_IMAGE_TYPE,
        ),
        $LANG01[13] => array(
            'condition' => SEC_hasRights('topic.edit'),
            'url' => $_CONF['site_admin_url'] . '/topic.php',
            'lang' => $LANG01[13],
            'image' => $_CONF['layout_url'] . '/images/icons/topic.' . $_IMAGE_TYPE,
        ),
        $LANG01[17] => array(
            'condition' => SEC_hasRights('user.edit'),
            'url' => $_CONF['site_admin_url'] . '/user.php',
            'lang' => $LANG01[17],
            'image' => $_CONF['layout_url'] . '/images/icons/user.' . $_IMAGE_TYPE,
        ),
        $LANG01[96] => array(
            'condition' => SEC_hasRights('group.edit'),
            'url' => $_CONF['site_admin_url'] . '/group.php',
            'lang' => $LANG01[96],
            'image' => $_CONF['layout_url'] . '/images/icons/group.' . $_IMAGE_TYPE,
        ),
        $LANG01[105] => array(
            'condition' => SEC_hasRights('user.mail'),
            'url' => $_CONF['site_admin_url'] . '/mail.php',
            'lang' => $LANG01[105],
            'image' => $_CONF['layout_url'] . '/images/icons/mail.' . $_IMAGE_TYPE,
        ),
        $LANG01[38] => array(
            'condition' => SEC_hasRights ('syndication.edit'),
            'url' => $_CONF['site_admin_url'] . '/syndication.php',
            'lang' => $LANG01[38],
            'image' => $_CONF['layout_url'] . '/images/icons/syndication.' . $_IMAGE_TYPE,
        ),
        $LANG01[116] => array(
            'condition' => $showTrackbackIcon,
            'url' => $_CONF['site_admin_url'] . '/trackback.php',
            'lang' => $LANG01[116],
            'image' => $_CONF['layout_url'] . '/images/icons/trackback.' . $_IMAGE_TYPE,
        ),
        $LANG01[98] => array(
            'condition' => SEC_hasRights('plugin.edit'),
            'url' => $_CONF['site_admin_url'] . '/plugins.php',
            'lang' => $LANG01[98],
            'image' => $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE,
        ),
        $LANG01['ctl'] => array(
            'condition' => SEC_hasRights('cache.admin'),
            'url' => $_CONF['site_admin_url'] . '/clearctl.php',
            'lang' => $LANG01['ctl'],
            'image' => $_CONF['layout_url'] . '/images/icons/ctl.' . $_IMAGE_TYPE,
        ),
        $LANG01['env_check'] => array(
            'condition' => SEC_hasRights('env.admin'),
            'url' => $_CONF['site_admin_url'].'/envcheck.php',
            'lang' => $LANG01['env_check'],
            'image' => $_CONF['layout_url'] . '/images/icons/envcheck.' . $_IMAGE_TYPE,
        ),
        $LANG01['logview'] => array(
            'condition' => SEC_hasRights('log.admin'),
            'url' => $_CONF['site_admin_url'] . '/logview.php',
            'lang' => $LANG_LOGVIEW['logview'],
            'image' => $_CONF['layout_url'] . '/images/icons/logview.' . $_IMAGE_TYPE,
        ),
        $LANG_MB01['menu_builder'] => array(
            'condition' => SEC_hasRights('menu.admin'),
            'url' => $_CONF['site_admin_url'] . '/menu.php',
            'lang' => $LANG_MB01['menu_builder'],
            'image' => $_CONF['layout_url'] . '/images/icons/menubuilder.' . $_IMAGE_TYPE,
        ),
        $LANG_LOGO['logo_admin'] => array(
            'condition' => SEC_hasRights('logo.admin'),
            'url' => $_CONF['site_admin_url'] . '/logo.php',
            'lang' => $LANG_LOGO['logo_admin'],
            'image' => $_CONF['layout_url'] . '/images/icons/logo.' . $_IMAGE_TYPE,
        ),
        $LANG_AM['title'] => array(
            'condition' => SEC_hasRights('autotag.admin'),
            'url' => $_CONF['site_admin_url'] . '/autotag.php',
            'lang' => $LANG_AM['title'],
            'image' => $_CONF['layout_url'] . '/images/icons/at.' . $_IMAGE_TYPE,
        ),
        'SFS User Check' => array(
            'condition' => SEC_hasRights('user.edit'),
            'url' => $_CONF['site_admin_url'] . '/sfs.php',
            'lang' => 'SFS User Check',
            'image' => $_CONF['layout_url'] . '/images/icons/sfs.' . $_IMAGE_TYPE,
        ),
        $LANG_SOCIAL['label'] => array(
            'condition' => SEC_hasRights('social.admin'),
            'url' => $_CONF['site_admin_url'].'/social.php',
            'lang' => $LANG_SOCIAL['label'],
            'image' => $_CONF['layout_url'] . '/images/icons/social.' . $_IMAGE_TYPE,
        ),
        $LANG01[103] => array(
            'condition' => SEC_hasRights('database.admin'),
            'url' => $_CONF['site_admin_url'] . '/database.php',
            'lang' => $LANG01[103],
            'image' => $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE,
        ),
        'Admin Actions' => array(
            'condition' => SEC_hasRights('actions.admin') &&
                isset($_CONF['enable_admin_actions']) &&
                $_CONF['enable_admin_actions'] == 1,
            'url' => $_CONF['site_admin_url'] . '/actions.php',
            'lang' => 'Admin Actions',
            'image' => $_CONF['layout_url'] . '/images/icons/actions.' . $_IMAGE_TYPE,
        ),
        $LANG01[113] => array(
            'condition' => ($_CONF['link_documentation'] == 1),
            'url' => $docUrl,
            'lang' => $LANG01[113],
            'image' => $_CONF['layout_url'] . '/images/icons/docs.' . $_IMAGE_TYPE,
        ),
        $LANG01[107] => array(
            'condition' => (SEC_hasRights ('upgrade.admin') &&
                              ($_CONF['link_versionchecker'] == 1)),
            'url' => $_CONF['site_admin_url'].'/vercheck.php',
            'lang' => $LANG01[107],
            'image' => $_CONF['layout_url'] . '/images/icons/versioncheck.' . $_IMAGE_TYPE,
        ),
        $LANG01[129] => array(
            'condition' => (SEC_hasRights ('config.admin')),
            'url'=>$_CONF['site_admin_url'] . '/configuration.php',
            'lang' => $LANG01[129],
            'image' => $_CONF['layout_url'] . '/images/icons/configuration.' . $_IMAGE_TYPE,
        ),
        $LANG01[10] => array(
            'condition' => SEC_isModerator(),
            'url' =>$_CONF['site_admin_url'] . '/moderation.php',
            'lang' => $LANG01[10],
            'image' => $_CONF['layout_url'] . '/images/icons/moderation.' . $_IMAGE_TYPE,
            'count' => $modnum,
        ),
        $LANG01[131] => array(
            'condition' => SEC_hasRights('system.root'),
            'url' =>$_CONF['site_admin_url'] . '/feature.php',
            'lang' => $LANG01[131],
            'image' => $_CONF['layout_url'] . '/images/icons/feature.' . $_IMAGE_TYPE,
        ),
        $LANG01[131] => array(
            'condition' => SEC_hasRights('system.root'),
            'url' =>$_CONF['site_admin_url'] . '/feature.php',
            'lang' => $LANG01[131],
            'image' => $_CONF['layout_url'] . '/images/icons/feature.' . $_IMAGE_TYPE,
        ),
    );

    // now add the plugins
    $plugins = PLG_getCCOptions();
    foreach ($plugins as $plugin) {
        $cc_arr[$plugin->adminlabel] = array(
            'url' => $plugin->adminurl,
            'lang' => $plugin->adminlabel,
            'image' => $plugin->plugin_image,
            'condition' => true,        // already evaluated by the plugin
        );
    }

    // and finally, add the remaining admin items

    if ($_CONF['sort_admin']) {
        uksort ($cc_arr, 'strcasecmp');
    }
    // logout is always the last entry
    $cc_arr[$LANG01[35]] = array(
        'condition' => true,
        'url' => $_CONF['site_url'] . '/users.php?mode=logout',
        'lang' => $LANG01[35],
        'image' => $_CONF['layout_url'] . '/images/icons/logout.' . $_IMAGE_TYPE,
    );

    $T->set_block('cc', 'Items', 'item');
    foreach ($cc_arr as $label=>$item) {
        if ($item['condition']) {
            if (isset($item['count'] ) ) {
                $count = $item['count'];
            } else {
                $count = 0;
            }
            $T->set_var(array(
                'page_url' => $item['url'],
                'page_image' => $item['image'],
                'option_label' => $label,
                'count' => $count,
            ) );
            $T->parse('item', 'Items', true);
        }
    }

    $retval .= $T->finish($T->parse('output', 'cc'));
    return $retval;
}


/**
* Display a reminder to execute the security check script
*
*/
function security_check_reminder()
{
    global $_CONF, $_TABLES, $_IMAGE_TYPE, $MESSAGE;

    $retval = '';

    if (!SEC_inGroup ('Root')) {
        return $retval;
    }

    $db = Database::getInstance();

    $done = $db->getItem ($_TABLES['vars'], 'value', array('name' => 'security_check'));
    if ($done != 1) {
        $retval .= COM_showMessage(92,'','',false,'error');
    }

    return $retval;
}

// MAIN

$display = COM_siteHeader('menu', $LANG29[34]);

$msg = COM_getMessage();
if ($msg > 0) {
    $plugin = '';
    if (isset($_GET['plugin'])) {
        $plugin = COM_applyFilter($_GET['plugin']);
    }
    $display .= COM_showMessage($msg, $plugin);
}

if ( !isset($_SYSTEM['skip_upgrade_check'] ) || $_SYSTEM['skip_upgrade_check'] == false ) {
    $display .= _checkUpgrades();
}

$display .= security_check_reminder();
$display .= commandcontrol();
$display .= COM_siteFooter();

echo $display;

?>
