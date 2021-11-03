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

    /** Put the action keys and strings in an array for easy access.
     * @var array */
    private static $keys = array(
        self::MODERATE => 'moderate',
        self::SUSPEND  => 'suspend',
        self::BAN => 'ban',
    );


    /**
     * Get a descriptive string related to the action.
     * Used to index into language files.
     *
     * @param   integer $key     key ID
     * @return  string      Descriptive key
     */
    public static function getDscp(int $key) : string
    {
        if ($key > 0 && isset(self::$keys[$key])) {
            return self::$keys[$key] . '_user';
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
        if (!is_integer($key)) {
            var_dump(debug_backtrace(0));die;
        }
        $retval = array(
            'severity' => '',
            'message' => 'No restriction',
        );
        if ($key >= self::BAN) {
            $retval = array(
                'severity' => 'danger',
                'message' => 'User is banned from the forum.',
            );
        } elseif ($key >= self::SUSPEND) {
            $retval = array(
                'severity' => 'warning',
                'message' => 'User\'s posting permission is suspended.',
            );
        } elseif ($key >= self::MODERATE) {
            $retval = array(
                'severity' => 'warning',
                'message' => 'User\'s forum posts are moderated.',
            );
        }
        return $retval;
    }

}
