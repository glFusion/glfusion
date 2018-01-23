<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-social.php                                                           |
// |                                                                          |
// | glFusion Enhancement Library                                             |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2015-2016 by the following authors:                        |
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

function SOC_getShareIcons( $title = '', $summary = '', $itemUrl = '', $image = '', $type = '' )
{
    global $_CONF, $_TABLES, $LANG_SOCIAL;

    $retval = '';

    $retval = PLG_replaceSocialShare($type,$title,$itemUrl,$summary);
    if ( $retval != '' ) return $retval;

    $replacementArray = array('%%t', '%%s', '%%u', '%%i');

    $retval = '';

    $T = new Template( $_CONF['path_layout'].'social' );
    $T->set_file('social_icons','socialshare.thtml');

    $sql = "SELECT * FROM {$_TABLES['social_share']} WHERE enabled=1";
    $result = DB_query($sql);
    $numRows = DB_numRows($result);

    if ( $numRows <= 0 ) return $retval;

    $T->set_block('social_icons','social_buttons','sb');

    $output = outputHandler::getInstance();
    $output->addLinkScript($_CONF['site_url'].'/javascript/socialshare.js');

    for ( $x = 0; $x < $numRows; $x++ ) {
        $row = DB_fetchArray($result);

        $id = $row['id'];
        $name = $row['name'];
        $display_name = $row['display_name'];
        $icon = $row['icon'];
        $url  = $row['url'];

        // now parse the URL and replace with stuff

        foreach($replacementArray AS $item ) {
            switch ($item) {
                case '%%t' :
                    $replacementItem = $title;
                    break;
                case '%%s' :
                    $replacementItem = $summary;
                    break;
                case '%%u' :
                    $replacementItem = $itemUrl;
                    break;
                case '%%i' :
                    $replacementItem = $image;
                    break;
                default :
                    $replacementItem = '';
                    break;
            }
            $url = str_replace($item,$replacementItem,$url);
        }

        // now build the actual button
        $T->set_var('service_name',$name);
        $T->set_var('service_id',$id);
        $T->set_var('service_display_name',$display_name);
        $T->set_var('icon',$icon);
        $T->parse('sb','social_buttons',true);
    }
    $T->set_var('lang_share_it', $LANG_SOCIAL['share_it_label']);
    $retval = $T->finish ($T->parse('output','social_icons'));

    return $retval;
}

function SOC_getFollowMeIcons( $uid = 0, $templateFile = 'follow_user.thtml' )
{
    global $_CONF, $_TABLES, $_USER, $LANG_SOCIAL;

    $retval = '';

    if ( $uid == 0 ) {
        if ( COM_isAnonUser()) return;
        $uid = $_USER['uid'];
    }

    if ( $uid == -1 ) { // site social followme
        $hash = CACHE_security_hash() . md5($templateFile);
        $instance_id = 'social_site_followme__'.$_USER['theme'];

        if ( ($cache = CACHE_check_instance($instance_id, 0)) !== FALSE ) {
            return $cache;
        }
    }

    $T = new Template( $_CONF['path_layout'].'social' );
    $T->set_file('links',$templateFile);

    // build SQL to pull this user's active social integrations

    $sql = "SELECT * FROM {$_TABLES['social_follow_services']} as ss LEFT JOIN
            {$_TABLES['social_follow_user']} AS su ON ss.ssid = su.ssid
            WHERE su.uid = " . (int) $uid . " AND ss.enabled = 1 ORDER BY service_name";

    $result = DB_query($sql);
    $numRows = DB_numRows($result);
    if ( $numRows > 0 ) {

        $T->set_block('links','social_buttons','sb');

        for ( $x = 0; $x < $numRows; $x++ ) {
            $row = DB_fetchArray($result);

            $social_url = str_replace("%%u", $row['ss_username'],$row['url']);

            $T->set_var(array(
                'service_icon'  => $row['icon'],
                'service_display_name' => $row['display_name'],
                'social_url'   => $social_url,
            ));
            $T->set_var('lang_follow_me', $LANG_SOCIAL['follow_me']);
            $T->set_var('lang_follow_us', $LANG_SOCIAL['follow_us']);
            $T->parse('sb','social_buttons',true);
        }
        $T->set_var('lang_share_it', $LANG_SOCIAL['share_it_label']);
        $T->set_var('lang_follow_us', $LANG_SOCIAL['follow_us']);

        if ( $uid == -1 ) {
            $cfg =& config::get_instance();
            $_SOCIAL = $cfg->get_config('social_internal');

            if ( isset($_SOCIAL['social_site_extra'])) {
                $T->set_var('extra',$_SOCIAL['social_site_extra']);
            }
        }
        $retval = $T->finish ($T->parse('output','links'));
    }
    if ( $uid == -1 ) {
        CACHE_create_instance($instance_id, $retval, 0);
    }
    return $retval;
}

function SOC_followMeProfile( $uid )
{
    global $_CONF, $_TABLES, $_USER, $LANG_SOCIAL;

    $retval = '';

    $userFollowMe = array();

    if ( COM_isAnonUser() ) return;

    $socialServicesArray = array();
    $userServicesArray = array();

    $sql = "SELECT * FROM {$_TABLES['social_follow_services']} WHERE enabled=1 ORDER BY service_name ASC";
    $result = DB_query($sql);
    while ( ($row = DB_fetchArray($result)) != NULL ) {
        $id = $row['ssid'];
        $socialServicesArray[$id] = $row;
    }
    $sql = "SELECT * FROM {$_TABLES['social_follow_user']} WHERE uid=". (int) $uid;
    $result = DB_query($sql);
    while ( ($row = DB_fetchArray($result)) != NULL ) {
        $id = $row['ssid'];
        $userServicesArray[$id] = $row;
    }
    foreach ($socialServicesArray AS $id => $data) {
        if ( isset($userServicesArray[$id]) ) {
            $socialServicesArray[$id]['ss_username'] = $userServicesArray[$id]['ss_username'];
        } else {
            $socialServicesArray[$id]['ss_username'] = '';
        }
        $userFollowMe[] = array(
                    'service_id'           => $id,
                    'service_display_name' => $socialServicesArray[$id]['display_name'],
                    'service'              => $socialServicesArray[$id]['service_name'],
                    'service_username'     => $socialServicesArray[$id]['ss_username'],
                    'service_url'          => substr($socialServicesArray[$id]['url'],0,strpos($socialServicesArray[$id]['url'],"%%u")),
        );

    }

    return $userFollowMe;
}
?>