<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | directory.php                                                            |
// |                                                                          |
// | Directory of all the stories on a glFusion site.                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004-2009 by the following authors:                        |
// |                                                                          |
// | Authors: Dirk Haun         - dirk AT haun-online DOT de                  |
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

require_once 'lib-common.php';

// configuration option:
// List stories for the current month on top of the overview page
// (if set = true)
$conf_list_current_month = false;

$display = '';
$pageBody = '';

if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
                                   ($_CONF['directoryloginrequired'] == 1))) {
    $display .= COM_siteHeader('menu', $LANG_DIR['title']);
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

/**
* Helper function: Calculate last day of a given month
*
* @param    int     $month  Month
* @param    int     $year   Year
* @return   int             Number of days in that month
* @todo     Bug: Will fail from 2038 onwards ...
*
* "The last day of any given month can be expressed as the "0" day
* of the next month", http://www.php.net/manual/en/function.mktime.php
*
*/
function DIR_lastDayOfMonth($month, $year)
{
    $month++;
    if ($month > 12) {
        $month = 1;
        $year++;
    }

    $lastday = mktime(0, 0, 0, $month, 0, $year);

    return intval(strftime('%d', $lastday));
}


/**
* Build link to a month's page
*
* @param    string  $dir_topic  current topic
* @param    int     $year   year to link to
* @param    int     $month  month to link to
* @param    int     $count  number of stories for that month (may be 0)
* @return   string          month name + count, as link or plain text
*
*/
function DIR_monthLink($dir_topic, $year, $month, $count)
{
    global $_CONF, $LANG_MONTH;

    $retval = $LANG_MONTH[$month] . ' (' . COM_numberFormat ($count) . ')' . LB;

    if ($count > 0) {
        $month_url = COM_buildUrl($_CONF['site_url'] . '/directory.php'
            . '?topic=' . urlencode ($dir_topic) . '&amp;year='
            . $year . '&amp;month=' . $month);
        $retval =  COM_createLink($retval, $month_url);
    }

    $retval .= LB;

    return $retval;
}

/**
* Display navigation bar
*
* @param    string  $dir_topic  current topic
* @param    int     $year   current year
* @param    int     $month  current month (or 0 for year view pages)
* @return   string          navigation bar with prev, next, and "up" links
*
*/
function DIR_navBar($dir_topic, $year, $month = 0)
{
    global $_CONF, $_TABLES, $LANG05, $LANG_DIR;

    $retval = '';

    if ($month == 0) {
        $prevyear = $year - 1;
        $nextyear = $year + 1;
    } else {
        $prevyear = $year;
        $prevmonth = $month - 1;
        if ($prevmonth == 0) {
            $prevmonth = 12;
            $prevyear--;
        }
        $nextyear = $year;
        $nextmonth = $month + 1;
        if ($nextmonth > 12) {
            $nextmonth = 1;
            $nextyear++;
        }
    }

    $result = DB_query("SELECT MIN(EXTRACT(Year from date)) AS year FROM {$_TABLES['stories']}");
    $A = DB_fetchArray($result);
    if ($prevyear < $A['year']) {
        $prevyear = 0;
    }

    $currenttime = time();
    $currentyear = date('Y', $currenttime);
    if ($nextyear > $currentyear) {
        $nextyear = 0;
    }
    if (($month > 0) && ($nextyear > 0) && ($nextyear >= $currentyear)) {
        $currentmonth = date('n', $currenttime);
        if ($nextmonth > $currentmonth) {
            $nextyear = 0;
        }
    }

    if ($prevyear > 0) {
        $url = $_CONF['site_url'] . '/directory.php' . '?topic='
             . urlencode($dir_topic) . '&amp;year=' . $prevyear;
        if ($month > 0) {
            $url .= '&amp;month=' . $prevmonth;
        }
        $retval .= COM_createLink($LANG05[6], COM_buildUrl($url));
    } else {
        $retval .= $LANG05[6];
    }

    $retval .= ' | ';

    $url = $_CONF['site_url'] . '/directory.php';
    if ($dir_topic != 'all') {
        $url = COM_buildUrl($url . '?topic=' . urlencode($dir_topic));
    }

    $retval .= COM_createLink($LANG_DIR['nav_top'] , $url);

    $retval .= ' | ';

    if ($nextyear > 0) {
        $url = $_CONF['site_url'] . '/directory.php' . '?topic='
             . urlencode($dir_topic) . '&amp;year=' . $nextyear;
        if ($month > 0) {
            $url .= '&amp;month=' . $nextmonth;
        }
        $retval .= COM_createLink($LANG05[5], COM_buildUrl($url));
    } else {
        $retval .= $LANG05[5];
    }

    return $retval;
}

/**
* Display month view
*
* @param    ref    &$template   reference of the template
* @param    string  $dir_topic  current topic
* @param    int     $year   year to display
* @param    int     $month  month to display
* @return   string          list of articles for the given month
*
*/
function DIR_displayMonth(&$template, $dir_topic, $year, $month)
{
    global $_CONF, $_USER, $_TABLES, $LANG_MONTH, $LANG_DIR;

    $retval = '';

    $dt = new Date('now',$_USER['tzid']);

    $start = sprintf ('%04d-%02d-01 00:00:00', $year, $month);
    $lastday = DIR_lastDayOfMonth ($month, $year);
    $end   = sprintf ('%04d-%02d-%02d 23:59:59', $year, $month, $lastday);

    $sql = "SELECT sid,title,UNIX_TIMESTAMP(date) AS day,DATE_FORMAT(date, '%e') AS mday FROM {$_TABLES['stories']} WHERE (date >= '$start') AND (date <= '$end') AND (draft_flag = 0) AND (date <= NOW())";
    if ($dir_topic != 'all') {
        $sql .= " AND ((tid = '".DB_escapeString($dir_topic)."') || alternate_tid= '".DB_escapeString($dir_topic)."')";
    }
    $sql .= COM_getTopicSql ('AND') . COM_getPermSql ('AND')
         . COM_getLangSQL ('sid', 'AND') . " ORDER BY date ASC";

    $result = DB_query($sql);
    $numrows = DB_numRows($result);

    if ($numrows > 0) {
        $entries = array();
        $mday = 0;

        for ($i = 0; $i < $numrows; $i++) {
            $A = DB_fetchArray($result);

            if ($mday != $A['mday']) {
                if (count($entries) > 0) {
                    $retval .= COM_makeList($entries);
                    $entries = array();
                }
                $dt->setTimestamp($A['day']);
                $day = $dt->format($_CONF['shortdate'],true);

                $template->set_var('section_title', $day);
                $retval .= $template->parse('title', 'section-title') . LB;

                $mday = $A['mday'];
            }

            $url = COM_buildUrl($_CONF['site_url'] . '/article.php?story='
                                . $A['sid']);
            $entries[] = COM_createLink($A['title'], $url);
        }

        if (count($entries) > 0) {
            $retval .= COM_makeList($entries);
        }

    } else {
        $retval .= $template->parse('message', 'no-articles') . LB;
    }

    $retval .= LB;

    return $retval;
}

/**
* Display year view
*
* @param    ref    &$template   reference of the template
* @param    string  $dir_topic  current topic
* @param    int     $year   year to display
* @return   string          list of months (+ number of stories) for given year
*
*/
function DIR_displayYear(&$template,$topic, $year, $main = false)
{
    global $_CONF, $_TABLES, $LANG_MONTH, $LANG_DIR;

    $retval = '';

    $currentyear = date ('Y', time ());
    $currentmonth = date ('m', time ());

    $start = sprintf ('%04d-01-01 00:00:00', $year);
    $end   = sprintf ('%04d-12-31 23:59:59', $year);

    $monthsql = array();
    $monthsql['mysql'] = "SELECT DISTINCT MONTH(date) AS month,COUNT(*) AS count FROM {$_TABLES['stories']} WHERE (date >= '$start') AND (date <= '$end') AND (draft_flag = 0) AND (date <= NOW())";

    if ($topic != 'all') {
        $monthsql['mysql'] .= " AND ((tid = '".DB_escapeString($topic)."') || alternate_tid = '".DB_escapeString($topic). "' )";
    }
    $monthsql['mysql'] .= COM_getTopicSql ('AND') . COM_getPermSql ('AND')
              . COM_getLangSQL ('sid', 'AND');

    $msql = array();
    $msql['mysql'] = $monthsql['mysql'] . " GROUP BY MONTH(date) ORDER BY date ASC";

    $mresult = DB_query($msql);
    $nummonths = DB_numRows($mresult);

    if ($nummonths > 0) {
        $items = array();
        $lastm = 1;
        for ($j = 0; $j < $nummonths; $j++) {
            $M = DB_fetchArray($mresult);

            for (; $lastm < $M['month']; $lastm++) {
                $items[] = DIR_monthLink($topic, $year, $lastm, 0);
            }
            $lastm = $M['month'] + 1;

            $items[] = DIR_monthLink($topic, $year, $M['month'], $M['count']);
        }

        if ($year == $currentyear) {
            $fillm = $currentmonth;
        } else {
            $fillm = 12;
        }

        if ($lastm <= $fillm) {
            for (; $lastm <= $fillm; $lastm++) {
                $items[] = DIR_monthLink($topic, $year, $lastm, 0);
            }
        }
        $retval .= COM_makeList($items);
    } else {
        $retval .= $template->parse('message', 'no-articles') . LB;
    }

    $retval .= LB;

    return $retval;
}

/**
* Display main view (list of years)
*
* Displays an overview of all the years and months, starting with the first
* year for which a story has been posted. Can optionally display a list of
* the stories for the current month at the top of the page.
*
* @param    ref    &$template  reference of the template
* @param    string  $dir_topic current topic
* @return   string             list of all the years in the db
*
*/
function DIR_displayAll(&$template, $dir_topic)
{
    global $_TABLES, $LANG_DIR;

    $retval = '';

    $yearsql = array();
    $yearsql['mysql'] = "SELECT DISTINCT YEAR(date) AS year,date FROM {$_TABLES['stories']} WHERE (draft_flag = 0) AND (date <= NOW())" . COM_getTopicSql ('AND') . COM_getPermSql ('AND')  . COM_getLangSQL ('sid', 'AND');
    $ysql = array();
    $ysql['mysql'] = $yearsql['mysql'] . " GROUP BY YEAR(date) ORDER BY date DESC";

    $yresult = DB_query($ysql);
    $numyears = DB_numRows($yresult);
    if ($numyears > 0) {
        for ($i = 0; $i < $numyears; $i++) {
            $Y = DB_fetchArray($yresult);

            $template->set_var('section_title', $Y['year']);
            $retval .= $template->parse('title', 'section-title') . LB;

            $retval .= DIR_displayYear($template, $dir_topic, $Y['year']);
        }
    } else {
        $retval .= $template->parse('message', 'no-articles') . LB;
    }

    return $retval;
}

/**
* Return a canonical link
*
* @param    string  $dir_topic  current topic or 'all'
* @param    int     $year   current year
* @param    int     $month  current month
* @return   string          <link rel="canonical"> tag
*
*/
function DIR_canonicalLink($dir_topic, $year = 0, $month = 0)
{
    global $_CONF;

    $script = $_CONF['site_url'] . '/directory.php';

    $tp = '?topic=' . urlencode($dir_topic);
    $parts = '';
    if (($year != 0) && ($month != 0)) {
        $parts .= "&amp;year=$year&amp;month=$month";
    } elseif ($year != 0) {
        $parts .= "&amp;year=$year";
    } elseif ($dir_topic == 'all') {
        $tp = '';
    }
    $url = COM_buildUrl($script . $tp . $parts);

    return '<link rel="canonical" href="' . $url . '">' . LB;
}


if (isset($_POST['topic']) && isset($_POST['year']) && isset($_POST['month'])) {
    $dir_topic = $_POST['topic'];
    $year = $_POST['year'];
    $month = $_POST['month'];
} else {
    COM_setArgNames(array('topic', 'year', 'month'));
    $dir_topic = COM_getArgument('topic');
    $year = COM_getArgument('year');
    $month = COM_getArgument('month');
}

$dir_topic = COM_applyFilter($dir_topic);
if (empty($dir_topic)) {
    $dir_topic = 'all';
}

//Set topic for rest of site
if ($dir_topic == 'all') {
    $topic = '';
} else {
    $topic = $dir_topic;
}
// See if user has access to view topic.
if ($topic != '') {
    $test_topic = DB_getItem($_TABLES['topics'], 'tid', "tid = '$topic' " . COM_getPermSQL('AND'));
    if (strtolower($topic) != strtolower($test_topic)) {
        $topic = '';
        $dir_topic = 'all';
    } else {
        $topic = $test_topic;
        $dir_topic = $test_topic;
    }
}

$year = COM_applyFilter($year, true);
if ($year < 0) {
    $year = 0;
}
$month = COM_applyFilter($month, true);
if (($month < 1) || ($month > 12)) {
    $month = 0;
}

$dir_topicName = '';
if ($dir_topic != 'all') {
    $dir_topicName = DB_getItem($_TABLES['topics'], 'topic',
                            "tid = '" . DB_escapeString($dir_topic) . "'");
}

$template = new Template($_CONF['path_layout']);
$template->set_file('t_directory', 'directory.thtml');
$template->set_block('t_directory', 'section-title');
$template->set_block('t_directory', 'no-articles');
$template->set_var('lang_no_articles', $LANG_DIR['no_articles']);

if (($year != 0) && ($month != 0)) {
    $title = sprintf ($LANG_DIR['title_month_year'],
                      $LANG_MONTH[$month], $year);
    if ($dir_topic != 'all') {
        $title .= ': ' . $dir_topicName;
    }
    $headercode = DIR_canonicalLink($dir_topic, $year, $month);
    $directory = DIR_displayMonth($template, $dir_topic, $year, $month);
    $page_navigation = DIR_navBar($dir_topic, $year, $month);
    $block_title = $LANG_MONTH[$month] . ' ' . $year;
    $val_year = $year;
    $val_month = $month;
} else if ($year != 0) {
    $title = sprintf($LANG_DIR['title_year'], $year);
    if ($dir_topic != 'all') {
        $title .= ': ' . $dir_topicName;
    }
    $headercode = DIR_canonicalLink($dir_topic, $year);
    $directory = DIR_displayYear($template, $dir_topic, $year);
    $page_navigation = DIR_navBar($dir_topic, $year);
    $block_title = $year;
    $val_year = $year;
    $val_month = 0;

} else {
    $title = $LANG_DIR['title'];
    if ($dir_topic != 'all') {
        $title .= ': ' . $dir_topicName;
    }
    $headercode = DIR_canonicalLink($dir_topic);
    $directory = DIR_displayAll($template, $dir_topic);
    $page_navigation = '';
    $block_title = $LANG_DIR['title'];
    $val_year = 0;
    $val_month = 0;

    if ($conf_list_current_month) {
        $currenttime = time();
        $currentyear  = date('Y', $currenttime);
        $currentmonth = date('n', $currenttime);
        $thismonth = COM_startBlock($LANG_MONTH[$currentmonth])
                   . DIR_displayMonth($template, $dir_topic,
                         $currentyear, $currentmonth)
                   . COM_endBlock();
        $template->set_var('current_month', $thismonth);
    }
}

$topic_list = '<option value="all">'.$LANG21[7].'</option>';
$topics = COM_topicArray('tid,topic', 1, false, 2);
if ( is_array($topics) ) {
    foreach ($topics as $tid => $topic) {
        $topic_list .= '<option value="' . $tid . '"';
        if ($tid == $dir_topic) {
            $topic_list .= ' selected="selected"';
        }
        $topic_list .= '>' . $topic;
        if ( isset($tid) ) {
            $topic_list .= ' (' . $tid . ')';
        }
        $topic_list .=  '</option>' . LB;
    }
}

$template->set_var(array(
    'url'             => $_CONF['site_url'] . '/directory.php',
    'topic_list'      => $topic_list,
    'blockheader'     => COM_startBlock($block_title),
    'val_year'        => $val_year,
    'val_month'       => $val_month,
    'directory'       => $directory,
    'page_navigation' => $page_navigation,
    'blockfooter'     => COM_endBlock(),
));
$template->parse('output', 't_directory');
$pageBody = $template->finish($template->get_var('output'));
$display .= COM_siteHeader('menu', $title,$headercode);
$display .= $pageBody;
$display .= COM_siteFooter (true);
echo $display;
?>
