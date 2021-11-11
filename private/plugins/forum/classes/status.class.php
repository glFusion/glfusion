<?php
/**
 * Define order states.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     forum
 * @version     v1.3.0
 * @since       v1.3.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Forum;

/**
 * Enumerate possible warning actions.
 * @package forum
 */
class Status
{
    /** No action or restriction applies to the user.
     */
    public const NONE = 0;

    /** User can post but posts are moderated.
     */
    public const MODERATE = 1;

    /** User is temporarily suspended from posting, can still view.
     */
    public const SUSPEND = 2;

    /** User is banned from the forum completely.
     */
    public const BAN = 15;

    /** User is banned from the entire site permanently.
     */
    public const SITE_BAN = 127;

    /** Put the action keys and strings in an array for easy access.
     * @var array */
    private static $keys = array(
        self::MODERATE => 'moderate',
        self::SUSPEND  => 'suspend',
        self::BAN => 'forum_ban',
        self::SITE_BAN => 'site_ban',
    );


    private $status = 0;
    private $expiration = 0;


    /**
     * Get a descriptive string related to the action.
     * Used to index into language files.
     *
     * @param   integer $key     key ID
     * @return  string      Descriptive key
     */
    public static function getDscp(int $key) : string
    {
        global $LANG_GF01;

        if ($key > 0 && isset(self::$keys[$key])) {
            return $LANG_GF01[self::$keys[$key]];
        } else {
            return 'none';
        }
    }


    /**
     * Get a text string related to the action.
     * Used to create fieldnames, etc.
     *
     * @param   integer $key     key ID
     * @return  string      Descriptive key
     */
    public static function getKey(int $key) : string
    {
        if ($key > 0 && isset(self::$keys[$key])) {
            return self::$keys[$key];
        } else {
            return '';
        }
    }


    public static function getSeverity($key) : array
    {
        global $_CONF, $LANG_GF01;

        $retval = array(
            'severity' => '',
            'message' => $LANG_GF01['no_restriction'],
        );
        if (isset($key['expires']) && $key['expires'] > time()) {
            $dt = new \Date($key['expires'], $_CONF['timezone']);
            $dt_str = ' until ' . $dt->toMySQL(true);
        } else {
            $dt_str = '';
        }
        if ($key >= self::BAN) {
            $retval = array(
                'severity' => 'danger',
                'message' => $LANG_GF01['user_banned'] . $dt_str,
            );
        } elseif ($key >= self::SUSPEND) {
            $retval = array(
                'severity' => 'warning',
                'message' => $LANG_GF01['user_suspended'] . $dt_str,
            );
        } elseif ($key >= self::MODERATE) {
            $retval = array(
                'severity' => 'warning',
                'message' => $LANG_GF01['user_moderated'] . $dt_str,
            );
        }
        return $retval;
    }


    /**
     * Get the option elements for a selection list.
     *
     * @param   integer $sel    Currently-selected option
     * @return  string      HTML for options
     */
    public static function getOptionList(?int $sel=0) : string
    {
        global $LANG_GF01;

        $retval = '';
        foreach (self::$keys as $key=>$tag) {
            $retval .= '<option value="' . $key . '"';
            if ($sel == $key) {
                $retval .= ' selected="selected"';
            }
            $retval .= '>' . $LANG_GF01[$tag] . '</option>' . LB;
        }
        return $retval;
    }

}
