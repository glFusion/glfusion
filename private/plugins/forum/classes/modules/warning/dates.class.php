<?php
/**
 * Class to handle forum warning system.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Forum\Modules\Warning;

class Dates
{
    /** Correlate the number of seconds to each interval type.
     * @var array */
    private static $durations = array(
        'year'  => 31104000,    // 12 * 30 days
        'month' =>  2592000,    // Assumes 30 days
        'week'  =>   604800,
        'day'   =>    86400,
    );


    /**
     * Get the descriptive elements for a number of seconds.
     * For example, 86400 returns array(1, 'day').
     *
     * @param   integer $seconds    Number of seconds
     * @return  array   Array of (number, descrption)
     */
    public static function secondsToParts(int $seconds) : array
    {
        $retval = array(
            'year' => 0,
            'month' => 0,
            'week' => 0,
            'day' => 0,
        );
        foreach (self::$durations as $key=>$val) {
            if ($seconds >= $val) {
                $mod = $seconds % $val;
                $quo = floor($seconds / $val);
                $retval[$key] += $quo;
                $seconds = $mod;
            }
        }
        return $retval;
    }


    public static function secondsToDscp(int $seconds) : string
    {
        $parts = array();
        $arr = self::secondsToParts($seconds);
        foreach ($arr as $period=>$num) {
            if ($num > 0) {
                $parts[] = $num . ' ' . ucfirst($period) . '(s)';
            }
        }
        return implode(', ', $parts);
    }


    /**
     * Take a description of an interval in two parts and convert to seconds.
     * E.g. "1 day" returns 86400
     *
     * @param   integer $num    Number of elements
     * @param   string  $type   Type of element (day, week, month, year)
     * @return  integer     Number of seconds
     */
    public static function dscpToSeconds(int $num, string $type) : int
    {
        if (isset(self::$durations[$type])) {
            $seconds = $num * self::$durations[$type];
        } else {
            $seconds = self::$durations['month'];
        }
        return $seconds;
    }

}

