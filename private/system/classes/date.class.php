<?php
/**
* glFusion CMS
*
* glFusion date/time handling library
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2011-2017 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the Joomla Framework                                            |
*  Copyright (C) 2005-2011 Open Source Matters, Inc.
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
 * Date is a class that stores a date and provides logic to manipulate
 * and render that date in a variety of formats.
 */
class Date extends DateTime
{
    const DAY_ABBR = "\x021\x03";
    const DAY_NAME = "\x022\x03";
    const MONTH_ABBR = "\x023\x03";
    const MONTH_NAME = "\x024\x03";

    /**
     * The format string to be applied when using the __toString() method.
     *
     * @var     string
     */
    public static $format = 'Y-m-d H:i:s';

    /**
     * Placeholder for a DateTimeZone object with GMT as the time zone.
     *
     * @var     object
     */
    protected static $gmt;

    /**
     * Placeholder for a DateTimeZone object with the default server
     * time zone as the time zone.
     *
     * @var     object
     */
    protected static $stz;


    /**
     * The DateTimeZone object for usage in rending dates as strings.
     *
     * @var     object
     */
    protected $_tz;

    /**
     * Holding place for the initial user timezone
     *
     * @var     string
     */
    protected $_user_time_zone;

    /**
     * Constructor.
     *
     * @param   string  String in a format accepted by strtotime(), defaults to "now".
     * @param   mixed   Time zone to be used for the date.
     * @return  void
     *
     */
    public function __construct($date = 'now', $tz = null)
    {
        if ( $tz == null ) {
            $this->_user_time_zone = 'UTC';
        } else {
            $this->_user_time_zone = $tz;
        }

        // Create a base GMT and a server time zone object
        if (empty(self::$gmt) || empty(self::$stz)) {
            self::$gmt = new DateTimeZone('GMT');
            self::$stz = new DateTimeZone(@date_default_timezone_get());
        }
        // validate we have a good timezone
        if ( @date_default_timezone_set($tz) == FALSE ) {
            $tz = self::$gmt;
        }
        // If the time zone object is not set, attempt to build it.
        if (!($tz instanceof DateTimeZone)) {
            if ($tz === null) {
                $tz = self::$gmt;
            } elseif (is_numeric($tz)) {
                // Translate from offset.
                $tz = new DateTimeZone(self::$offsets[(string) $tz]);
            } elseif (is_string($tz)) {
                $tz = new DateTimeZone($tz);
            }
        }
        // If the date is numeric assume a unix timestamp and convert it.
        date_default_timezone_set('UTC');
        $date = is_numeric($date) ? date('c', $date) : $date;

        // Call the DateTime constructor.
        parent::__construct($date, $tz);

        // Set the timezone object for access later.
        $this->_tz = $tz;
    }


    /**
     * Magic method to access properties of the date given by class to the format method.
     *
     * @param   string  The name of the property.
     * @return  mixed   A value if the property name is valid, null otherwise.
     */
    public function __get($name)
    {
        $value = null;

        switch ($name) {
            case 'daysinmonth':
                $value = $this->format('t', true);
                break;

            case 'dayofweek':
                $value = $this->format('N', true);
                break;

            case 'dayofyear':
                $value = $this->format('z', true);
                break;

            case 'day':
                $value = $this->format('d', true);
                break;

            case 'isleapyear':
                $value = (boolean) $this->format('L', true);
                break;

            case 'hour':
                $value = $this->format('H', true);
                break;

            case 'minute':
                $value = $this->format('i', true);
                break;

            case 'month':
                $value = $this->format('m', true);
                break;

            case 'ordinal':
                $value = $this->format('S', true);
                break;

            case 'second':
                $value = $this->format('s', true);
                break;

            case 'week':
                $value = $this->format('W', true);
                break;

            case 'year':
                $value = $this->format('Y', true);
                break;

            default:
                $trace = debug_backtrace();
                trigger_error(
                    'Undefined property via __get(): ' . $name .
                    ' in ' . $trace[0]['file'] .
                    ' on line ' . $trace[0]['line'],
                    E_USER_NOTICE
                );
        }

        return $value;
    }

    /**
     * Magic method to render the date object in the format specified in the public
     * static member $format.
     *
     * @return  string  The date as a formatted string.
     */
    public function __toString()
    {
        return (string) parent::format(self::$format);
    }


    /**
     * Set date / time stamp
     *
     * @param   int   unix timestamp
     * @return  void
     */
    public function setTimestamp ( $unixtimestamp )
    {
        parent::setTimezone(self::$gmt);
        $date = getdate( (int) $unixtimestamp );
        parent::setDate( $date['year'] , $date['mon'] , $date['mday'] );
        parent::setTime( $date['hours'] , $date['minutes'] , $date['seconds'] );
    }


    /**
     * Set date / time stamp
     *
     * @param   int   year (YYYY)
     * @param   int   month
     * @param   int   day number
     * @param   int   hour (24hr format)
     * @param   int   minute
     * @param   int   seconds
     * @return  void
     */
    public function setDateTimestamp ( $year,$month,$day,$hour,$min,$sec )
    {
        parent::setDate ( $year , $month , $day );
        parent::setTime ( $hour , $min, $sec );
    }

    /**
     * Translates day of week number to a string.
     *
     * @param   integer The numeric day of the week.
     * @param   boolean Return the abreviated day string?
     * @return  string  The day of the week.
     */
    protected function dayToString($day, $abbr = false)
    {
        global $LANG_WEEK;

        switch ($day) {
            case 0: return $abbr ? $LANG_WEEK[8]  : $LANG_WEEK[1];
            case 1: return $abbr ? $LANG_WEEK[9]  : $LANG_WEEK[2];
            case 2: return $abbr ? $LANG_WEEK[10] : $LANG_WEEK[3];
            case 3: return $abbr ? $LANG_WEEK[11] : $LANG_WEEK[4];
            case 4: return $abbr ? $LANG_WEEK[12] : $LANG_WEEK[5];
            case 5: return $abbr ? $LANG_WEEK[13] : $LANG_WEEK[6];
            case 6: return $abbr ? $LANG_WEEK[14] : $LANG_WEEK[7];
        }
    }

    /**
     * Gets the date as a formatted string.
     *
     * @param   string  The date format specification string (see {@link PHP_MANUAL#date})
     * @param   boolean True to return the date string in the local time zone, false to return it in GMT.
     * @return  string  The date string in the specified format format.
     */
    public function format($format, $local = false)
    {
        // Do string replacements for date format options that can be translated.
        $format = preg_replace('/(^|[^\\\])D/', "\\1".self::DAY_ABBR, $format);
        $format = preg_replace('/(^|[^\\\])l/', "\\1".self::DAY_NAME, $format);
        $format = preg_replace('/(^|[^\\\])M/', "\\1".self::MONTH_ABBR, $format);
        $format = preg_replace('/(^|[^\\\])F/', "\\1".self::MONTH_NAME, $format);

        // If the returned time should not be local use GMT.
        if ($local == false) {
            parent::setTimezone(self::$gmt);
        } else {
            parent::setTimezone($this->_tz);
        }

        // Format the date.
        $return = parent::format($format);

        // Manually modify the month and day strings in the formated time.
        if (strpos($return, self::DAY_ABBR) !== false) {
            $return = str_replace(self::DAY_ABBR, $this->dayToString(parent::format('w'), true), $return);
        }
        if (strpos($return, self::DAY_NAME) !== false) {
            $return = str_replace(self::DAY_NAME, $this->dayToString(parent::format('w')), $return);
        }
        if (strpos($return, self::MONTH_ABBR) !== false) {
            $return = str_replace(self::MONTH_ABBR, $this->monthToString(parent::format('n'), true), $return);
        }
        if (strpos($return, self::MONTH_NAME) !== false) {
            $return = str_replace(self::MONTH_NAME, $this->monthToString(parent::format('n')), $return);
        }

        if ($local == false) {
            parent::setTimezone($this->_tz);
        }
        return $return;
    }

    /**
     * Determine if date is today
     *
     * @return  boolean true is date is today - false otherwise
     */
    public function isToday()
    {

        @date_default_timezone_set($this->_user_time_zone);
        $now = time();
        $year  = date('Y',$now);
        $month = date('m',$now);
        $day   = date('d',$now);
        @date_default_timezone_set('UTC');

        if ( (int) $this->year == (int) $year && (int) $this->month == (int) $month
            && (int) $this->day == (int) $day ) {
            return true;
        }
        return false;
    }

    /**
     * Get the time offset from GMT in hours or seconds.
     *
     * @param   boolean True to return the value in hours.
     * @return  float   The time offset from GMT either in hours in seconds.
     */
    public function getOffsetFromGMT($hours = false)
    {
        return (float) $hours ? ($this->_tz->getOffset($this) / 3600) : $this->_tz->getOffset($this);
    }

    /**
     * Translates month number to a string.
     *
     * @param   integer The numeric month of the year.
     * @param   boolean Return the abreviated month string?
     * @return  string  The month of the year.
     */
    public function monthToString($month, $abbr = false)
    {
        global $LANG_MONTH;

        switch ($month) {
            case 1:  return $abbr ? $LANG_MONTH[13] : $LANG_MONTH[1];
            case 2:  return $abbr ? $LANG_MONTH[14] : $LANG_MONTH[2];
            case 3:  return $abbr ? $LANG_MONTH[15] : $LANG_MONTH[3];
            case 4:  return $abbr ? $LANG_MONTH[16] : $LANG_MONTH[4];
            case 5:  return $abbr ? $LANG_MONTH[17] : $LANG_MONTH[5];
            case 6:  return $abbr ? $LANG_MONTH[18] : $LANG_MONTH[6];
            case 7:  return $abbr ? $LANG_MONTH[19] : $LANG_MONTH[7];
            case 8:  return $abbr ? $LANG_MONTH[20] : $LANG_MONTH[8];
            case 9:  return $abbr ? $LANG_MONTH[21] : $LANG_MONTH[9];
            case 10: return $abbr ? $LANG_MONTH[22] : $LANG_MONTH[10];
            case 11: return $abbr ? $LANG_MONTH[23] : $LANG_MONTH[11];
            case 12: return $abbr ? $LANG_MONTH[24] : $LANG_MONTH[12];
        }
    }


    /**
     * Method to wrap the setTimezone() function and set the internal
     * time zone object.
     *
     * @param   object  The new DateTimeZone object.
     * @return  object  The old DateTimeZone object.
     */
    public function setTimezone($tz)
    {
        if ( $tz == null ) {
            $this->_user_time_zone = 'UTC';
        } else {
            $this->_user_time_zone = $tz;
        }
        $this->_tz = $tz;
        return parent::setTimezone($tz);
    }


    /**
     * Gets the date as an ISO 8601 string.  IETF RFC 3339 defines the ISO 8601 format
     * and it can be found at the IETF Web site.
     *
     * @link http://www.ietf.org/rfc/rfc3339.txt
     *
     * @param   boolean True to return the date string in the local time zone, false to return it in GMT.
     * @return  string  The date string in ISO 8601 format.
     */
    public function toISO8601($local = false)
    {
        return $this->format(DateTime::RFC3339, $local);
    }

    /**
     * Gets the date as an MySQL datetime string.
     *
     * @link http://dev.mysql.com/doc/refman/5.0/en/datetime.html
     *
     * @param   boolean True to return the date string in the local time zone, false to return it in GMT.
     * @return  string  The date string in MySQL datetime format.
     */
    public function toMySQL($local = false)
    {
        return $this->format('Y-m-d H:i:s', $local);
    }

    /**
     * Gets the date as an RFC 822 string.  IETF RFC 2822 supercedes RFC 822 and its definition
     * can be found at the IETF Web site.
     *
     * @link http://www.ietf.org/rfc/rfc2822.txt
     *
     * @param   boolean True to return the date string in the local time zone, false to return it in GMT.
     * @return  string  The date string in RFC 822 format.
     */
    public function toRFC822($local = false)
    {
        return $this->format(DateTime::RFC2822, $local);
    }

    /**
     * Gets the date as UNIX time stamp.
     *
     * @return  integer The date as a UNIX timestamp.
     */
    public function toUnix()
    {
        return (int) parent::format('U');
    }

    /**
     * Returns user's preference date / time format string
     *
     * @return  string  The date / time format string
     */

    public function getUserFormat()
    {
        global $_CONF, $_USER;

        $dateformat = $_CONF['date'];

        if ( !COM_isAnonUser() && !empty( $_USER['format'] )) {
            $dateformat = $_USER['format'];
        }
        return $dateformat;
    }

    /**
     * Returns select HTML for all timezones
     *
     * @todo    deprecate
     * @param   string  current timezone selection
     * @param   array   attributes i.e.; array('name'=>'tzid','id'=>'tzid')
     * @return  string  HTML select box
     */
    public static function getTimeZoneDropDown($selectedzone = '', $attributes = array())
    {

        $select = '<select';
        foreach ($attributes as $name => $value) {
            $select .= sprintf(' %s="%s"', $name, $value);
        }
        $select .= '>' . LB;

        $all = timezone_identifiers_list();

        $i = 0;
        foreach($all AS $zone) {
            $zone = explode('/',$zone);
            $zonen[$i]['continent'] = isset($zone[0]) ? $zone[0] : '';
            $zonen[$i]['city'] = isset($zone[1]) ? $zone[1] : '';
            $zonen[$i]['subcity'] = isset($zone[2]) ? $zone[2] : '';
            $i++;
        }

        asort($zonen);
        foreach( $zonen AS $zone ) {
            extract($zone);
            if ($continent == 'Africa' || $continent == 'America' || $continent == 'Antarctica' || $continent == 'Arctic' || $continent == 'Asia' || $continent == 'Atlantic' || $continent == 'Australia' || $continent == 'Europe' || $continent == 'Indian' || $continent == 'Pacific') {
                if (isset($city) != '') {
                    if (!empty($subcity) != '') {
                        $city = $city . '/'. $subcity;
                    }
                    $select .= "<option ".((($continent.'/'.$city)==$selectedzone)?'selected="selected "':'')." value=\"".($continent.'/'.$city)."\">".$continent.'/'.str_replace('_',' ',$city)."</option>"; //Timezone
                } else {
                    if (!empty($subcity) != ''){
                        $city = $city . '/'. $subcity;
                    }
                }

                $selectcontinent = $continent;
            }
        }
        $select .= '</select>' . LB;
        return $select;
    }


    /**
     * Returns option elements for a timezone selector.
     *
     * @param   string  $selected   Current timezone selection
     * @return  string      Option elements to embed in select tags
     */
    public static function getTimeZoneOptions($selected='')
    {
        $retval = '';
        $all = timezone_identifiers_list();
        foreach($all AS $zone) {
            $retval .= '<option value="' . $zone . '"';
            if ($zone == $selected) {
                $retval .= ' selected="selected"';
            }
            $retval .= '>' . str_replace('_', ' ', $zone) . '</option>' . LB;
        }
        return $retval;
    }

}

?>
