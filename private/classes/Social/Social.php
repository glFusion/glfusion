<?php
/**
* glFusion CMS
*
* glFusion Social Integration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2015-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

namespace glFusion\Social;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Formatter;
use \glFusion\Log\Log;
use \glFusion\Admin\AdminAction;

class Social
{


    public static function getShareIcons( $title = '', $summary = '', $itemUrl = '', $image = '', $type = '' )
    {
        global $_CONF, $_TABLES, $LANG_SOCIAL;

        $retval = '';

        // first check if a plugin has replaced this functionality
        $retval = PLG_replaceSocialShare($type,$title,$itemUrl,$summary);
        if ( $retval != '' ) {
            return $retval;
        }

        $db = Database::getInstance();

        $replacementArray = array('%%t', '%%s', '%%u', '%%i');

        $retval = '';

        $T = new \Template( $_CONF['path_layout'].'social' );
        $T->set_file('social_icons','socialshare.thtml');

        $stmt = $db->conn->executeQuery(
                "SELECT * FROM `{$_TABLES['social_share']}` WHERE enabled=1",
                array(),
                array(),
                new \Doctrine\DBAL\Cache\QueryCacheProfile(86400, 'social_share')
        );
        $socialShares = $stmt->fetchAll(Database::ASSOCIATIVE);
        if (count($socialShares) == 0) {
            return '';
        }
        $output = \outputHandler::getInstance();
        $output->addLinkScript($_CONF['site_url'].'/javascript/socialshare.js');

        $T->set_block('social_icons','social_buttons','sb');

        foreach($socialShares AS $row) {
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
            if ($icon === 'facebook') {
                $T->set_var('service-postfix', '-official');
            } else {
                $T->set_var('service-postfix', '');
            }
            $T->parse('sb','social_buttons',true);
        }
        $T->set_var('lang_share_it', $LANG_SOCIAL['share_it_label']);
        $retval = $T->finish ($T->parse('output','social_icons'));

        return $retval;
    }

    public static function getFollowMeIcons( $uid = 0, $templateFile = 'follow_user.thtml' )
    {
        global $_CONF, $_TABLES, $_USER, $LANG_SOCIAL;

        static $fmCache = array();

        $retval = '';
        $counter = 0;

        if ( $uid == 0 ) {
            if ( COM_isAnonUser()) return;
            $uid = $_USER['uid'];
        }

        if ( isset($fmCache[$uid])) {
            return $fmCache[$uid];
        }

        $db = Database::getInstance();

        $T = new \Template( $_CONF['path_layout'].'social' );
        $T->set_file('links',$templateFile);

        // build SQL to pull this user's active social integrations

        $sql = "SELECT * FROM `{$_TABLES['social_follow_services']}` as ss LEFT JOIN
                `{$_TABLES['social_follow_user']}` AS su ON ss.ssid = su.ssid
                WHERE su.uid = ? AND ss.enabled = 1
                ORDER BY service_name";

        $stmt = $db->conn->executeQuery(
                    $sql,
                    array($uid),
                    array(Database::INTEGER)
        );
        if ($stmt === false || $stmt === null) {
            return $retval;
        }
        $T->set_block('links','social_buttons','sb');
        while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
            $social_url = str_replace("%%u", $row['ss_username'],$row['url']);

            $T->set_var(array(
                'service_icon'  => $row['icon'],
                'service_display_name' => $row['display_name'],
                'social_url'   => $social_url,
            ));
            if ($row['icon'] === 'facebook') {
                $T->set_var('service-postfix', '-official');
            } else {
                $T->set_var('service-postfix', '');
            }
            $T->set_var('lang_follow_me', $LANG_SOCIAL['follow_me']);
            $T->set_var('lang_follow_us', $LANG_SOCIAL['follow_us']);
            $T->parse('sb','social_buttons',true);
            $counter++;
        }
        if ($counter == 0) return '';
        $T->set_var('lang_share_it', $LANG_SOCIAL['share_it_label']);
        $T->set_var('lang_follow_us', $LANG_SOCIAL['follow_us']);

        if ( $uid == -1 ) {
            $cfg =& \config::get_instance();
            $_SOCIAL = $cfg->get_config('social_internal');

            if ( isset($_SOCIAL['social_site_extra'])) {
//                $T->set_var('extra',@unserialize($_SOCIAL['social_site_extra']));
                $T->set_var('extra',$_SOCIAL['social_site_extra']);
            }
        }
        $retval = $T->finish ($T->parse('output','links'));

        $fmCache[$uid] = $retval;

        return $retval;
    }

    public static function followMeProfile( $uid )
    {
        global $_CONF, $_TABLES, $_USER, $LANG_SOCIAL;

        $retval = '';

        $userFollowMe = array();

        if ( COM_isAnonUser() ) {
            return;
        }

        $db = Database::getInstance();

        $socialServicesArray = array();
        $userServicesArray = array();

        $sql = "SELECT * FROM `{$_TABLES['social_follow_services']}` WHERE enabled=1 ORDER BY service_name ASC";
        $stmt = $db->conn->executeQuery(
            $sql,
            array(),
            array(),
            new \Doctrine\DBAL\Cache\QueryCacheProfile(86400, 'social')
        );
        while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
            $id = $row['ssid'];
            $socialServicesArray[$id] = $row;
        }

        $sql = "SELECT * FROM `{$_TABLES['social_follow_user']}` WHERE uid=?";
        $stmt = $db->conn->executeQuery(
                $sql,
                array($uid),
                array(Database::INTEGER)
        );
        while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
            $id = $row['ssid'];
            $userServicesArray[$id] = $row;
        }

        foreach ($socialServicesArray AS $id => $data) {
            if ( isset($userServicesArray[$id]) ) {
                $socialServicesArray[$id]['ss_username'] = $userServicesArray[$id]['ss_username'];
            } else {
                $socialServicesArray[$id]['ss_username'] = '';
            }
            if ($socialServicesArray[$id]['service_name'] === 'facebook') {
                $postfix = '-official';
            } else {
                $postfix = '';
            }
            $userFollowMe[] = array(
                        'service_id'           => $id,
                        'service_display_name' => $socialServicesArray[$id]['display_name'],
                        'service'              => $socialServicesArray[$id]['service_name'],
                        'service_postfix'      => $postfix,
                        'service_username'     => $socialServicesArray[$id]['ss_username'],
                        'service_url'          => substr($socialServicesArray[$id]['url'],0,strpos($socialServicesArray[$id]['url'],"%%u")),
            );

        }

        return $userFollowMe;
    }

}