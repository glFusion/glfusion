<?php
/**
* glFusion CMS
*
* glFusion Calendar Library
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*   Authors: Tony Bibbs       - tony AT tonybibbs DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
* This class represents the logical structure of a calendar day and is used by
* the greater calendar class
*
*/
class CalendarDay {
    var $daynumber = 0;
    var $year = 0;

    var $weekendflag = false;
    var $holidayflag = false;
    var $selectedflag = false;

    /**
    * Constructur
    *
    */
    function __construct()
    {
        $this->weekendflag = false;
        $this->holidayflag = false;
        $this->selectedflag = false;
    }

    /**
    * Returns if this day is on a weekend
    *
    * @return   boolean     true if is on weekend otherwise false
    *
    */
    function isWeekend()
    {
        return $this->weekendflag;
    }

    /**
    * Returns if this day is a holiday
    *
    * @return   boolean     true if day is a holiday otherwise false
    *
    */
    function isHoliday()
    {
        return $this->holidayflag;
    }

    /**
    * Returns if this day is selected
    *
    * @return   boolean     true if day is selected otherwise false
    *
    */
    function isSelected()
    {
        return $this->selectedflag;
    }

}

class Calendar {

    // PRIVATE PROPERTIES

    /**
    * @access private
    */
    var $_rollingmode;
    /**
    * @access private
    */
    var $_lang_days;
    /**
    * @access private
    */
    var $_lang_months;
    /**
    * @access private
    */
    var $_week_start;
    /**
    * @access private
    */
    var $_default_year;
    /**
    * @access private
    */
    var $_selected_days;
    /**
    * @access private
    */
    var $_holidays;
    /**
    * @access private
    */
    var $_matrix;

    // PRIVATE METHODS

    /**
    * Returns if calendar is in rolling mode
    *
    * @return   boolean     returns true if in rolling mode otherwise false
    * @access   private
    *
    */
    function _isRollingMode()
    {
        return $this->_rollingmode;
    }

    /**
    * Constructor
    *
    * Initializes calendar object
    *
    */
    function __construct()
    {
        $this->setRollingMode(false);
        $dateArray = getdate(time());
        $this->_default_year = $dateArray['year'];
        $this->setLanguage();
    }

    /**
    * Returns the day of the week (1-7) for given date
    *
    * @param    int     $day        Number of day in month (1-31)
    * @param    int     $month      Number of the month (1-12)
    * @param    int     $year       Four digit year
    * @return   int     Returns integer for day of week 1 = Sunday through 7 = Saturday
    *
    */
    function getDayOfWeek($day = 1, $month = 1, $year = '')
    {
	    if (empty($year)) {
            $year = $this->_default_year;
        }

        $dateArray = getdate(mktime(0,0,0,$month,$day,$year));
        $result = $dateArray['wday'];
        if ($this->_week_start == 'Mon') {
            $result = ($result == 0) ? 6 : $result - 1;
        }
        return $result;
    }

    /**
    * Returns the week of the month (1-5) for a given date
    *
    * @param    int     $day        Number of day in month (1-31)
    * @param    int     $month      Number of the month (1-12)
    * @param    int     $year       Four digit year
    * @return   int     Week of the month, 1 - 5
    *
    */
    function getWeekOfMonth($day = 1, $month = 1, $year = '')
    {
        if (empty($year)) {
            $year = $this->_default_year;
        }

        $firstDayOfMonth = 1 + $this->getDayOfWeek(1,$month,$year);

        //we know first day of month is in the first week
        $dDiff = $day - $firstDayOfMonth;
        $wDiff = round($dDiff/7);
        return $wDiff + 1;
    }

    /**
    * Determines if given year is a leap year or not
    *
    * @param    int     $year   Four digit year
    * @return   boolean     returns true if year is a leap year otherwise false
    *
    */
    function isLeapYear($year = '')
    {
        if (empty($year)) {
            $year = $this->_default_year;
        }

        if (($year % 4) == 0) {
            if (($year % 100) == 0) {
                if (($year % 400) == 0) {
                    return 1;
                } else {
                    return 0;
                }
            }
            return 1;
        } else {
            return 0;
        }
    }

    /**
    * Returns the number of days in a given month/year
    *
    * If no year is given, the default year is used
    *
    * @param    int     $month      Month (1-12)
    * @param    int     $year       Four digit year
    * @return   int     Returns the number of days in the month
    *
    */
    function getDaysInMonth($month = 1, $year = '')
    {
        if (empty($year)) {
            $year = $this->_default_year;
        }
        switch ($month - 1) {
        case 0:
            return 31;
            break;
        case 1:
            if ($this->isLeapYear($year)) {
                return 29;
            } else {
                return 28;
            }
            break;
        case 2:
            return 31;
            break;
        case 3:
            return 30;
            break;
        case 4:
            return 31;
            break;
        case 5:
            return 30;
            break;
        case 6:
            return 31;
            break;
        case 7:
            return 31;
            break;
        case 8:
            return 30;
            break;
        case 9:
            return 31;
            break;
        case 10:
            return 30;
            break;
        case 11:
            return 31;
            break;
        default:
            return 0;
            break;

        }
    }

    /**
    * Returns the name of the day of the week
    *
    * This aims to help with multilingual support
    *
    * @param    int     $day    Numeric day of week (1-7)
    * @return   string  Returns the text for the given day of the week
    *
    */
    function getDayName($day = 1) {
        if ($this->_week_start == 'Mon') {
            if ($day == 7) {
                return $this->_lang_days['sunday'];
            } else {
                $day += 1;
            }
        }
        switch ($day - 1) {
        case 0:
            return $this->_lang_days['sunday'];
            break;
        case 1:
            return $this->_lang_days['monday'];
            break;
        case 2:
            return $this->_lang_days['tuesday'];
            break;
        case 3:
            return $this->_lang_days['wednesday'];
            break;
        case 4:
            return $this->_lang_days['thursday'];
            break;
        case 5:
            return $this->_lang_days['friday'];
            break;
        case 6:
            return $this->_lang_days['saturday'];
            break;
        default:
            return 0;
            break;
        }
    }

    /**
    * Returns the name of the given month (can handle different languages)
    *
    * This aims to help with multi-lingual support
    *
    * @param    int     $month      Month (1-12) to get name of
    * @return   string  returns text for current month name
    *
    */
    function getMonthName($month = 1)
    {
	$month = $month - 1;
	if (empty($this->_lang_months)) $this->setLanguage();

        switch ($month) {
        case 0:
            return $this->_lang_months['january'];
            break;
        case 1:
            return $this->_lang_months['february'];
            break;
        case 2:
            return $this->_lang_months['march'];
            break;
        case 3:
            return $this->_lang_months['april'];
            break;
        case 4:
            return $this->_lang_months['may'];
            break;
        case 5:
            return $this->_lang_months['june'];
            break;
        case 6:
            return $this->_lang_months['july'];
            break;
        case 7:
            return $this->_lang_months['august'];
            break;
        case 8:
            return $this->_lang_months['september'];
            break;
        case 9:
            return $this->_lang_months['october'];
            break;
        case 10:
            return $this->_lang_months['november'];
            break;
        case 11:
            return $this->_lang_months['december'];
            break;
        default:
            return 0;
            break;
        }
    }

    /**
    * Sets the rolling mode status
    *
    * Will put calendar in normal mode or in rolling mode.  Rolling
    * mode
    *
    * @param    boolean     $flag   True of False
    *
    */
    function setRollingMode($flag)
    {
        $this->_rollingmode = $flag;
    }

    /**
    * Sets the language for days of the week and months of year
    *
    * This function defaults to English.
    * Day array format is _lang_days['<daynameinenglish>'] = '<translation>'
    * Mondy array format is _lang_months['<monthnameinenglish'] = '<translation>'
    *
    * @param    array       $lang_days      Array of strings holding day language
    * @param    array       $lang_months    Array of string holding month language
    *
    */
    function setLanguage($lang_days='', $lang_months='', $week_start='Sun')
    {
        if (empty($lang_days)) {
            $this->_lang_days['sunday'] = 'Sunday';
            $this->_lang_days['monday'] = 'Monday';
            $this->_lang_days['tuesday'] = 'Tuesday';
            $this->_lang_days['wednesday'] = 'Wednesday';
            $this->_lang_days['thursday'] = 'Thursday';
            $this->_lang_days['friday'] = 'Friday';
            $this->_lang_days['saturday'] = 'Saturday';
        } else {
            $this->_lang_days = $lang_days;
        }

        if (empty($lang_months)) {
            $this->_lang_months['january'] = 'January';
            $this->_lang_months['february'] = 'February';
            $this->_lang_months['march'] = 'March';
            $this->_lang_months['april'] = 'April';
            $this->_lang_months['may'] = 'May';
            $this->_lang_months['june'] = 'June';
            $this->_lang_months['july'] = 'July';
            $this->_lang_months['august'] = 'August';
            $this->_lang_months['september'] = 'September';
            $this->_lang_months['october'] = 'October';
            $this->_lang_months['november'] = 'November';
            $this->_lang_months['december'] = 'December';
        } else {
            $this->_lang_months = $lang_months;
        }
        if ($week_start != 'Mon') {
            $this->_week_start = 'Sun';
        } else {
            $this->_week_start = $week_start;
        }
    }

    /**
    * Builds logical model of the month in memory
    *
    * @param    int     $month          Month to build matrix for
    * @param    int     $year           Year to build matrix for
    * @param    string  $selecteddays   Comma seperated list of days to select
    *
    */
    function setCalendarMatrix($month, $year, $selecteddays='')
    {
        $firstday = 1 + $this->getDayOfWeek(1,$month,$year);
        $nextday = 1;
        $lastday = $this->getDaysInMonth();

        $monthname = $this->getMonthName($month);

        // There are as many as 6 weeks in a calendar grid (I call these
        // absolute weeks hence the variable name aw.  Loop through and
        // populate data
        for ($aw = 1; $aw <= 6; $aw++) {
            for ($wd = 1;  $wd <= 7; $wd++) {
                if ($aw == 1 && $wd < $firstday) {
                    $week[$aw][$wd] = '';
                } else {
                    $cur_day = new CalendarDay();
                    $cur_day->year = $year;
                    $cur_day->daynumber = $nextday;
                    $week[$aw][$wd] = $cur_day;

                    // Bail if we just printed last day
                    if ($nextday < $this->getDaysInMonth($month,$year)) {
                        $nextday++;
                    } else {
                        $aw++;
                        while ($aw <= 6) {
                            $week[$aw] = '';
                            $aw++;
                        }
                        $aw = 7;
                    }
                }
            }
        }

        $this->_matrix = $week;

    }

    /**
    * Gets data for a given day
    *
    * @param    int     $week       week of day to get data for
    * @param    int     $daynum     Number of day to get data for
    * @return   object  Returns calendarDay object
    *
    */
    function getDayData($week,$daynum) {
        if (isset ($this->_matrix[$week][$daynum])) {
            return $this->_matrix[$week][$daynum];
        }

        return '';
    }

}

?>