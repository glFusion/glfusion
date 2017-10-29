<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-glfusion.php                                                         |
// |                                                                          |
// | glFusion Enhancement Library                                             |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

function glfusion_UpgradeCheck() {
    global $_CONF,$_SYSTEM,$_VARS,$_TABLES,$LANG01;

    if (!SEC_inGroup ('Root')) {
        return;
    }

    $retval = '';
    $msg = '';

    $dbversion = $_VARS['glfusion'];
    $comparison = version_compare( GVERSION, $dbversion );
    $install_url = $_CONF['site_admin_url'] . '/install/index.php';

    switch ($comparison) {
        case 1:
            $msg .= sprintf($LANG01[504], $dbversion, GVERSION, $install_url ) . '<br>';
            break;
        case -1:
            $msg .= sprintf($LANG01[505], $dbversion, GVERSION ) . '<br>';
            break;
    }

    if ( $msg != '' ) {
        $retval = '<p style="width:100%;text-align:center;"><span class="alert pluginAlert" style="text-align:center;font-size:1.5em;">' . $msg . '</span></p>';
    }
    return $retval;
}

function glfusion_SecurityCheck() {
    global $_CONF,$_SYSTEM,$LANG01;

    if (!SEC_inGroup ('Root') OR (isset($_SYSTEM['development_mode']) AND $_SYSTEM['development_mode'])) {
        return;
    }

    $retval = '';
    $msg = '';
    if ( file_exists($_CONF['path_admin'] . 'install/') ) {
        $msg .= $LANG01[500].'<br>';
    }
    if ( $_SYSTEM['rootdebug'] ) {
        $msg .= $LANG01[501].'<br>';
    }
    if ( $_SYSTEM['no_fail_sql'] ) {
        $msg .= $LANG01[502].'<br>';
    }
    if ( $_CONF['maintenance_mode'] ) {
        $msg .= $LANG01[503].'<br>';
    }
    if ( $msg != '' ) {

        $retval = COM_showMessageText($msg,'',false, 'error');
    }
    return $retval;
}

/*
 *  Story Picker Block - by Joe Mucchiello
 *  Make a list of n number of story headlines linked to their articles.
 *  Block does not appear if there are no stories in the current topic.
 *  If no topic is selected, all stories are listed.
 *
 *  Required Block Settings:
 *      Block Name = storypicker
 *      Topic = All
 *      Block Type = PHP Block
 *      Block Function = phpblock_storypicker
 *
 *  Issues:
 *      Does not handle stories that may have expired.
 */
function phpblock_storypicker() {
    global $_TABLES, $_CONF, $topic;

    $LANG_STORYPICKER = array('choose' => 'Choose a story');
    $max_stories = 5; //how many stories to display in the list

    $topicsql = '';
    $sid = '';
    if (isset($_GET['story'])) {
        $sid = COM_applyFilter($_GET['story']);
        $stopic = DB_getItem($_TABLES['stories'], 'tid', 'sid = \''.DB_escapeString($sid).'\'');
        if (!empty($stopic)) {
            $topic = $stopic;
        } else {
            $sid = '';
        }
    }

    if ( empty($topic) ) {
        if ( isset($_GET['topic']) ) {
            $topic = COM_applyFilter($_GET['topic']);
        } elseif (isset($_POST['topic']) ) {
            $topic = COM_applyFilter($_POST['topic']);
        } else {
            $topic = '';
        }
    }
    if (!empty($topic)) {
        $topicsql = " AND tid = '".DB_escapeString($topic)."'";
    }
    if (empty($topicsql)) {
        $topic = DB_getItem($_TABLES['topics'], 'tid', 'archive_flag = 1');
        if (empty($topic)) {
            $topicsql = '';
        } else {
            $topicsql = " AND tid <> '".DB_escapeString($topic)."'";
        }
    }
    $sql = 'SELECT sid, title FROM ' .$_TABLES['stories']
         . ' WHERE draft_flag = 0 AND date <= now()'
         . COM_getPermSQL(' AND')
         . COM_getTopicSQL(' AND')
         . $topicsql
         . ' ORDER BY date DESC LIMIT ' . $max_stories;

    $res = DB_query($sql);
    $list = '';
    while ($A = DB_fetchArray($res)) {
        $url = COM_buildUrl ($_CONF['site_url'] . '/article.php?story=' . $A['sid']);
        $list .= '<li><a href=' . $url .'>'
		//uncomment the 2 lines below to limit of characters displayed in the title
		. htmlspecialchars(COM_truncate($A['title'],41,'...')) . "</a></li>\n";
    }
    return $list;
}

function xml2array($contents, $get_attributes = 1, $priority = 'tag')
{
    if (!function_exists('xml_parser_create')) {
        return array ();
    }
    $parser = xml_parser_create('');

    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if (!$xml_values)
        return;
    $xml_array = array ();
    $parents = array ();
    $opened_tags = array ();
    $arr = array ();
    $current = & $xml_array;
    $repeated_tag_index = array ();
    foreach ($xml_values as $data) {
        unset ($attributes, $value);
        extract($data);
        $result = array ();
        $attributes_data = array ();
        if (isset ($value)) {
            if ($priority == 'tag') {
                $result = $value;
            } else {
                $result['value'] = $value;
            }
        }
        if (isset ($attributes) and $get_attributes) {
            foreach ($attributes as $attr => $val) {
                if ($priority == 'tag') {
                    $attributes_data[$attr] = $val;
                } else {
                    $result['attr'][$attr] = $val;
                }
            }
        }
        if ($type == "open") {
            $parent[$level -1] = & $current;
            if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                $current[$tag] = $result;
                if ($attributes_data) {
                    $current[$tag . '_attr'] = $attributes_data;
                }
                $repeated_tag_index[$tag . '_' . $level] = 1;
                $current = & $current[$tag];
            } else {
                if (isset ($current[$tag][0])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else {
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if (isset ($current[$tag . '_attr'])) {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset ($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = & $current[$tag][$last_item_index];
            }
        } elseif ($type == "complete") {
            if (!isset ($current[$tag])) {
                @$current[$tag] = @$result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data)
                    @$current[$tag . '_attr'] = @$attributes_data;
            } else {
                if (isset ($current[$tag][0]) and is_array($current[$tag])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    if ($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else {
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes) {
                        if (isset ($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }
                        if ($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }
        } elseif ($type == 'close') {
            $current = & $parent[$level -1];
        }
    }
    return ($xml_array);
}

function _upToDate($currentVersion,$installedVersion)
{
    $upToDate = 0;

    list($latestMajor,$latestMinor,$latestRev,$latestExtra)     = explode('.',$currentVersion.'....');
    list($currentMajor,$currentMinor,$currentRev,$currentExtra) = explode('.',$installedVersion.'....');

    if ( $currentMajor >= $latestMajor ) {
        if ( $currentMajor > $latestMajor ) {
            $upToDate = 2;
        } else if ( $currentMinor >= $latestMinor ) {
            if ( $currentMinor > $latestMinor ) {
                $upToDate = 2;
            } else if ( $currentRev >= $latestRev ) {
                if ($currentRev > $latestRev ) {
                    $upToDate = 2;
                } else if ( $currentExtra != '' || $latestExtra != '' ) {
                    if ( strcmp($currentExtra,$latestExtra) == 0 ) {
                        $upToDate = 1;
                    }
                } else {
                    $upToDate = 1;
                }
            }
        }
    }
    return $upToDate;
}

function _checkVersion()
{
    global $_CONF, $_USER, $_PLUGIN_INFO;

    // build XML request

    $result = '';

    $http=new http_class;
    $http->timeout=5;
    $http->data_timeout=5;
    $http->debug=0;
    $http->html_debug=0;
    $http->user_agent = 'glFusion/' . GVERSION;
    $url="http://www.glfusion.org/versions/index.php";
    $error=$http->GetRequestArguments($url,$arguments);
    $arguments["RequestMethod"]="POST";
    $arguments["PostValues"]=array(
        "v"=>GVERSION.PATCHLEVEL,
        "p"=>PHP_VERSION,
        "d"=>urlencode(DB_getVersion())
    );
    if ( $_CONF['send_site_data'] ) {
        $arguments["PostValues"]['s'] = $_CONF['site_url'];
    }
    $error=$http->Open($arguments);
    if ( $error == "" ) {
        $error=$http->SendRequest($arguments);
        if ( $error == "" ) {
            for(;;) {
                $error = $http->ReadReplyBody($body,1000);
                if ( $error != "" || strlen($body) == 0 )
                    break;
                $result = $result . $body;
            }
        }
    }

    if (!$result) {
        return array(-1,-1,array());
    }

    // parse XML response

    $response = xml2array($result);

    $latest = 'unknown';
    if ( isset( $response['response'] ) ) {
        if ( isset($response['response']['glfusion'] ) ) {
            $latest = $response['response']['glfusion']['version'];
        } else {
            $latest = 'unknown';
        }
        if ( isset($response['response']['glfusion']['date']) ) {
            $releaseDate = $response['response']['glfusion']['date'];
        } else {
            $releaseDate = 'unknown';
        }
    } else {
        return array(-1,-1,array());
    }

    // check glFusion CMS version

    $current = GVERSION.PATCHLEVEL;

    list($latestMajor,$latestMinor,$latestRev,$latestExtra)     = explode('.',$latest.'....');
    list($currentMajor,$currentMinor,$currentRev,$currentExtra) = explode('.',$current.'....');

    $glFusionUpToDate = 0;

    if ( $currentMajor >= $latestMajor ) {
        if ( $currentMajor > $latestMajor ) {
            $glFusionUpToDate = 2;
        } else if ( $currentMinor >= $latestMinor ) {
            if ( $currentMinor > $latestMinor ) {
                $glFusionUpToDate = 2;
            } else if ( $currentRev >= $latestRev ) {
                if ($currentRev > $latestRev ) {
                    $glFusionUpToDate = 2;
                } else if ( $currentExtra != '' || $latestExtra != '' ) {
                    $rc = strcmp($currentExtra,$latestExtra);
                    if ( $rc == 0 ) {
                        $glFusionUpToDate = 1;
                    } elseif ( $rc < 0 ) {
                        $glFusionUpToDate = 0;
                    } else {
                        $glFusionUpToDate = 2;
                    }
                } else {
                    $glFusionUpToDate = 1;
                }
            }
        }
    }

    // run through all our active plugins and see if any are out of date

    $pluginsUpToDate = 1;
    $done = 0;

    if ( isset($response['response']) && is_array($response['response']['plugin'] ) ) {
        foreach ($_PLUGIN_INFO AS $iPlugin ) {
            if ( $iPlugin['pi_enabled'] ) {
                $upToDate = 0;
                foreach ($response['response']['plugin'] AS $plugin ) {
                    if ( strcmp($plugin['name'],$iPlugin['pi_name']) == 0 ) {
                        if ( _upToDate($plugin['version'],$iPlugin['pi_version']) == 0 ) {
                            $pluginsUpToDate = 0;
                            $done = 1;
                            break;
                        }
                    }
                }
            }
            if ( $done ) {
                break;
            }
        }
    }

    // build data if we need it...

    $pluginData = array();

    $pluginData['glfusioncms']['plugin'] = 'glfusioncms';
    $pluginData['glfusioncms']['installed_version'] = $current;
    $pluginData['glfusioncms']['display_name'] = '';
    $pluginData['glfusioncms']['latest_version'] = $latest;
    $pluginData['glfusioncms']['release_date'] = $releaseDate;
    $pluginData['glfusioncms']['url'] = '';

    if ( is_array($response['response']['plugin'] ) ) {
        foreach ($_PLUGIN_INFO AS $iPlugin ) {
            if ( $iPlugin['pi_enabled'] ) {
                $upToDate = 0;
                $pluginData[$iPlugin['pi_name']]['plugin'] = $iPlugin['pi_name'];
                $pluginData[$iPlugin['pi_name']]['installed_version'] = $iPlugin['pi_version'];
                $pluginData[$iPlugin['pi_name']]['display_name'] = $iPlugin['pi_name'];
                $pluginData[$iPlugin['pi_name']]['latest_version'] = 0;
                $pluginData[$iPlugin['pi_name']]['release_date'] = 0;
                $pluginData[$iPlugin['pi_name']]['url'] = '';
                foreach ($response['response']['plugin'] AS $plugin ) {
                    if ( strcmp($plugin['name'],$iPlugin['pi_name']) == 0 ) {
                        $pluginData[$iPlugin['pi_name']]['display_name'] = $plugin['displayname'];
                        $pluginData[$iPlugin['pi_name']]['latest_version'] = $plugin['version'];
                        $pluginData[$iPlugin['pi_name']]['release_date'] = $plugin['date'];
                        if (isset($plugin['url']) ) {
                            $pluginData[$iPlugin['pi_name']]['url'] = $plugin['url'];
                        }
                    }
                }
            }
        }
    }

    return array($glFusionUpToDate,$pluginsUpToDate,$pluginData);
}

/**
 * Check if the user's PHP version is supported
 *
 * @return bool True if supported, falsed if not supported
 *
 */
function _phpUpToDate()
{
    // supported version is 5.6 or greater

    $uptodate = false;

    $phpv = explode('.', phpversion());

    switch ( $phpv[0] ) {
        case 5 :
            if ( $phpv[1] >= 6 ) {
                $uptodate = true;
            }
            break;
        case '7' :
            $uptodate = true;
            break;

        default :
            $uptodate = false;
            break;
    }

    return $uptodate;
}

function _doSiteConfigUpgrade() {
    global $_SYSTEM, $_CONF;

    $retval = false;

    $_SYSDEFAULT = array(
        'use_direct_style_js'         => true,
        'html_filter'                 => 'htmlpurifier',
        'site_enabled'                => true,
        'maintenance_mode'            => false,
        'no_fail_sql'                 => false,
        'no_cache_config'             => false,
        'disable_instance_caching'    => false,
        'admin_session'               => 1200,
        'swedish_date_hack'           => false,
        'verification_token_ttl'      => 86400,
        'token_ip'                    => false,
        'max_captcha_atttempts'       => 4,
        'custom_topic_templates'      => false,
        'token_ttl'                   => 1200,
        'alert_timeout'               => 4000,
        'alert_position'              => 'top-right',
        'db_backup_rows'              => 10000,
        'style_type'                  => 'undefined',
        'sp_pages_in_plugin_menu'     => false,
        'skip_upgrade_check'          => false,
    );

    if (is_array($_SYSTEM) && (count($_SYSTEM) > 1)) {
        $_NEWSYSTEM = array_merge($_SYSDEFAULT, $_SYSTEM);
    } else {
        $_NEWSYSTEM = $_SYSDEFAULT;
    }

    $_CFDEFAULT['css_cache_filename']            = 'style.cache';
    $_CFDEFAULT['js_cache_filename']             = 'js.cache';
    $_CFDEFAULT['path']                          = '/path/to/glfusion/';
    $_CFDEFAULT['path_system']                   = $_CONF['path'] . 'system/';
    $_CFDEFAULT['default_charset']               = 'iso-8859-1';

    if ( empty($_CONF['db_charset'])) {
        $result = DB_query("SELECT @@character_set_database",1);
        $collation = DB_fetchArray($result);

        $_CFDEFAULT['db_charset'] = $collation["@@character_set_database"];
        if ( empty($_CFDEFAULT['db_charset'])) {
            $_CFDEFAULT['db_charset'] = '';
        } else {
            $_CONF['db_charset'] = $_CFDEFAULT['db_charset'];
        }
    }

    if (!defined('CONFIG_CACHE_FILE_NAME')) {
      define('CONFIG_CACHE_FILE_NAME',"'$$$config$$$.cache'");
    }
    $_CFDEFAULT['config_cache_file_name']       = CONFIG_CACHE_FILE_NAME;

    $_NEWSYSCONF = array_merge($_CFDEFAULT,$_CONF);

    $T = new Template($_CONF['path_layout'].'/admin/');
    $T->set_file('page', 'siteconfig.thtml');

    $T->set_var(array(
        'use_direct_style_js'         => $_NEWSYSTEM['use_direct_style_js'] ? 'true' : 'false',
        'html_filter'                 => $_NEWSYSTEM['html_filter'],
        'site_enabled'                => $_NEWSYSTEM['site_enabled'] ? 'true' : 'false',
        'maintenance_mode'            => $_NEWSYSTEM['maintenance_mode'] ? 'true' : 'false',
        'no_fail_sql'                 => $_NEWSYSTEM['no_fail_sql'] ? 'true' : 'false',
        'no_cache_config'             => $_NEWSYSTEM['no_cache_config'] ? 'true' : 'false',
        'disable_instance_caching'    => $_NEWSYSTEM['disable_instance_caching'] ? 'true' : 'false',
        'admin_session'               => $_NEWSYSTEM['admin_session'],
        'swedish_date_hack'           => $_NEWSYSTEM['swedish_date_hack'] ? 'true' : 'false',
        'verification_token_ttl'      => $_NEWSYSTEM['verification_token_ttl'],
        'token_ip'                    => $_NEWSYSTEM['token_ip'] ? 'true' : 'false',
        'max_captcha_atttempts'       => $_NEWSYSTEM['max_captcha_atttempts'],
        'custom_topic_templates'      => $_NEWSYSTEM['custom_topic_templates'] ? 'true' : 'false',
        'css_cache_filename'          => $_NEWSYSCONF['css_cache_filename'],
        'js_cache_filename'           => $_NEWSYSCONF['js_cache_filename'],
        'path'                        => $_NEWSYSCONF['path'],
        'default_charset'             => $_NEWSYSCONF['default_charset'],
        'db_charset'                  => $_NEWSYSCONF['db_charset'],
        'config_cache_file_name'      => $_NEWSYSCONF['config_cache_file_name'],
        'token_ttl'                   => $_NEWSYSTEM['token_ttl'],
        'alert_timeout'               => $_NEWSYSTEM['alert_timeout'],
        'alert_position'              => $_NEWSYSTEM['alert_position'],
        'style_type'                  => $_NEWSYSTEM['style_type'],
        'db_backup_rows'              => $_NEWSYSTEM['db_backup_rows'],
        'sp_pages_in_plugin_menu'     => $_NEWSYSTEM['sp_pages_in_plugin_menu']  ? 'true' : 'false',
        'skip_upgrade_check'          => $_NEWSYSTEM['skip_upgrade_check'] ? 'true' : 'false',
        'beginphp'                    => '<?php',
        'endphp'                      => '?>',
    ));

    if ( $_CONF['path'] .'system/' == $_CONF['path_system']) {
        $T->set_var('path_system', "\$_CONF['path'] . 'system/'");
    } else {
        $T->set_var('path_system',$_NEWSYSTEM['path_system']);
    }

    $T->parse('output','page');
    $siteconfig_data = $T->finish($T->get_var('output'));

    $siteconfig_path = $_CONF['path_html'] . 'siteconfig.php';
    if (is_writable($siteconfig_path)) {
        $siteconfig_file = fopen($siteconfig_path, 'w');
        if (fwrite($siteconfig_file, $siteconfig_data)) {
            fclose ($siteconfig_file);
            $retval = true;
            COM_errorLog("UPGRADE: Successfully updated siteconfig.php with latest options.");
        } else {
            COM_errorLog("UPGRADE: Unable to update siteconfig.php due to permissions.");
        }
    } else {
        COM_errorLog("UPGRADE: Unable to update siteconfig.php due to permissions.");
    }
    CTL_clearCache();
    return $retval;
}
?>