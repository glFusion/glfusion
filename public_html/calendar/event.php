<?php
// +--------------------------------------------------------------------------+
// | Calendar Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | event.php                                                                |
// |                                                                          |
// | Shows details of an event or events                                      |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
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

require_once '../lib-common.php';

if (!in_array('calendar', $_PLUGINS)) {
    COM_404();
    exit;
}

require_once $_CONF['path_system'] . 'classes/calendar.class.php';

$display = '';

if (COM_isAnonUser() &&
    (($_CONF['loginrequired'] == 1) || ($_CA_CONF['calendarloginrequired'] == 1))) {
    $display .= CALENDAR_siteHeader($LANG_CAL_1[41]);
    $display .= SEC_loginRequiredForm();
    $display .= CALENDAR_siteFooter();
    echo $display;
    exit;
}

/**
* Adds an event to the user's calendar
*
* The user has asked that an event be added to their personal
* calendar.  Show a confirmation screen.
*
* @param    string  $eid    event ID to add to user's calendar
* @return   string          HTML for confirmation form
*
*/
function CALENDAR_addUserEvent($eid)
{
    global $_CONF, $_USER, $_TABLES, $LANG_CAL_1;

    $retval = '';

    $eventsql = "SELECT * FROM {$_TABLES['events']} WHERE eid='".DB_escapeString($eid)."'" . COM_getPermSql ('AND');
    $result = DB_query($eventsql);
    $nrows = DB_numRows($result);
    if ($nrows == 1) {
        $retval .= COM_startBlock (sprintf ($LANG_CAL_1[11],
                                            COM_getDisplayName()));
        $A = DB_fetchArray($result);
        $cal_template = new Template($_CONF['path'] . 'plugins/calendar/templates/');
        $cal_template->set_file(array('addevent' => 'addevent.thtml'));
        $cal_template->set_var('intro_msg', $LANG_CAL_1[8]);
        $cal_template->set_var('lang_event', $LANG_CAL_1[12]);
        $event_title = $A['title'];

        if (!empty ($A['url']) && ($A['url'] != 'http://')) {
            $cal_template->set_var ('event_url', $A['url']);
            $event_title = COM_createLink($event_title, $A['url']);
        } else {
            $cal_template->set_var ('event_url', '');
        }
        $cal_template->set_var ('event_title', $event_title);
        $cal_template->set_var('lang_starts', $LANG_CAL_1[13]);
        $cal_template->set_var('lang_ends', $LANG_CAL_1[14]);

        $thestart = COM_getUserDateTimeFormat($A['datestart'] . ' ' . $A['timestart']);
        $theend = COM_getUserDateTimeFormat($A['dateend'] . ' ' . $A['timeend']);
        if ($A['allday'] == 0) {
            $cal_template->set_var('event_start', $thestart[0]);
            $cal_template->set_var('event_end', $theend[0]);
        } else {
            $cal_template->set_var('event_start', strftime('%x', $thestart[1]));
            $cal_template->set_var('event_end', strftime('%x', $theend[1]));
        }

        $cal_template->set_var('lang_where',$LANG_CAL_1[4]);

        $location = $A['location'] . '<br/>'
                  . $A['address1'] . '<br/>'
                  . $A['address2'] . '<br/>'
                  . $A['city']
                  . ', ' . $A['state'] . ' ' . $A['zipcode'];

/* this will eventually replace the above code

        // build well-formatted location html
        $location = '';
        $keys = array('location','address1','address2','city','state','zipcode');
        foreach($key as $keys) {
            if (!empty($A[$key])) {
                if ($key == 'zipcode') {
                    $location .= $A[$key];
                } else {
                    $location .= stripslashes($A[$key]);
                }
                switch ($key) {
                    case 'city':
                        if (!empty($A['state']) || !empty($A['zip'])) {
                            if (!empty($A['state'])) {
                                $location .= ', ';
                            } elseif (!empty($A['zipcode'])) {
                                    $location .= ' ';
                            }
                        } else {
                            $location .= '<br/>';
                        }
                        break;
                    case 'state':
                        if (!empty($A['zipcode'])) {
                            $location .= ' ';
                        } else {
                            $location .= '<br/>';
                        }
                        break;
                    default:
                        $location .= '<br/>';
                        break;
                }
            }
        }
*/
        $cal_template->set_var('event_location', $location);
        $cal_template->set_var('lang_description', $LANG_CAL_1[5]);
        $description = $A['description'];
        if (empty($A['postmode']) || ($A['postmode'] == 'plaintext')) {
            $description = nl2br ($description);
        }
        $cal_template->set_var ('event_description',
                                PLG_replaceTags ($description,'calendar','description'));
        $cal_template->set_var('event_id', $eid);
        $cal_template->set_var('lang_addtomycalendar', $LANG_CAL_1[9]);
        $cal_template->set_var('gltoken_name', CSRF_TOKEN);
        $cal_template->set_var('gltoken', SEC_createToken());
        $cal_template->parse('output', 'addevent');
        $retval .= $cal_template->finish($cal_template->get_var('output'));
        $retval .= COM_endBlock ();
    } else {
        $retval .= COM_showMessage(23);
    }

    return $retval;
}

/**
* Save an event to user's personal calendar
*
* User has seen the confirmation screen and they still wants to
* add this event to their calendar.  Actually save it now.
*
* @param    string  $eid    ID of event to save
* @return   string          HTML refresh
*
*/
function CALENDAR_saveUserEvent($eid)
{
    global $_CONF, $_TABLES, $_USER;

    if (!COM_isAnonUser() ) {

        // Try to delete the event first in case it has already been added
        DB_query ("DELETE FROM {$_TABLES['personal_events']} WHERE uid={$_USER['uid']} AND eid='".DB_escapeString($eid)."'");

        $result = DB_query ("SELECT eid FROM {$_TABLES['events']} WHERE (eid = '".DB_escapeString($eid)."')" . COM_getPermSql ('AND'));
        if (DB_numRows ($result) == 1) {

            $savesql = "INSERT INTO {$_TABLES['personal_events']} "
             . "(eid,status,uid,title,event_type,datestart,dateend,timestart,timeend,allday,location,address1,address2,city,state,"
             . "zipcode,url,description,group_id,owner_id,perm_owner,perm_group,perm_members,perm_anon) "
             . " SELECT eid, status,".(int)$_USER['uid'].",title,event_type,datestart,dateend,timestart,timeend,allday,location,address1,address2,"
             . "city,state,zipcode,url,description,group_id,owner_id,perm_owner,perm_group,perm_members,perm_anon FROM "
             . "{$_TABLES['events']} WHERE eid = '".DB_escapeString($eid)."'";

            DB_query ($savesql);

            return COM_refresh ($_CONF['site_url']
                                . '/calendar/index.php?mode=personal&amp;msg=24');
        }
    }

    return COM_refresh ($_CONF['site_url'] . '/index.php');
}

/**
* Allows user to edit a personal calendar event
*
* @param    array   $A  Record to display
* @return   string      HTML for event editor
*
*/
function CALENDAR_editPersonalEvent($A)
{
    global $_CONF, $_USER, $_CA_CONF, $LANG_CAL_1;

    $cal_templates = new Template($_CONF['path'] . 'plugins/calendar/templates/');
    $cal_templates->set_file('form','editpersonalevent.thtml');

    $cal_templates->set_var ('lang_title', $LANG_CAL_1[28]);
    $title = $A['title'];
    $title = str_replace ('{', '&#123;', $title);
    $title = str_replace ('}', '&#125;', $title);
    $title = str_replace ('"', '&quot;', $title);
    $cal_templates->set_var ('event_title', $title);

    $cal_templates->set_var('lang_eventtype', $LANG_CAL_1[37]);
    $type_options = CALENDAR_eventTypeList($A['event_type']);
    $cal_templates->set_var('type_options', $type_options);

    // Handle start date/time
    $cal_templates->set_var('lang_startdate', $LANG_CAL_1[21]);
    $cal_templates->set_var('lang_starttime', $LANG_CAL_1[30]);
    $A['startdate'] = $A['datestart'] . ' ' . $A['timestart'];

    $start_month = date ('n', strtotime ($A['startdate']));
    $month_options = COM_getMonthFormOptions ($start_month);
    $cal_templates->set_var ('startmonth_options', $month_options);

    $start_day = date ('j', strtotime ($A['startdate']));
    $day_options = COM_getDayFormOptions ($start_day);
    $cal_templates->set_var('startday_options', $day_options);

    $start_year = date ('Y', strtotime ($A['startdate']));
    $year_options = COM_getYearFormOptions ($start_year);
    $cal_templates->set_var('startyear_options', $year_options);

    if (isset ($_CA_CONF['hour_mode']) && ($_CA_CONF['hour_mode'] == 24)) {
        $start_hour = date ('H', strtotime ($A['startdate']));
        $hour_options = COM_getHourFormOptions ($start_hour, 24);
        $cal_templates->set_var ('starthour_options', $hour_options);
    } else {
        $start_hour = date ('g', strtotime ($A['startdate']));
        $hour_options = COM_getHourFormOptions ($start_hour);
        $cal_templates->set_var ('starthour_options', $hour_options);
    }

    $startmin = intval (date ('i', strtotime ($A['startdate'])) / 15) * 15;
    $cal_templates->set_var ('startminute_options',
                             COM_getMinuteFormOptions ($startmin, 15));

    $ampm = date ('a', strtotime ($A['startdate']));
    $cal_templates->set_var ('startampm_selection',
                     CALENDAR_getAmPmFormSelection ('startampm_selection', $ampm, 'update_ampm()'));

    // Handle end date/time
    $cal_templates->set_var('lang_enddate', $LANG_CAL_1[18]);
    $cal_templates->set_var('lang_endtime', $LANG_CAL_1[29]);
    $A['enddate'] = $A['dateend'] . ' ' . $A['timeend'];

    $end_month = date ('n', strtotime ($A['enddate']));
    $month_options = COM_getMonthFormOptions ($end_month);
    $cal_templates->set_var ('endmonth_options', $month_options);

    $end_day = date ('j', strtotime ($A['enddate']));
    $day_options = COM_getDayFormOptions ($end_day);
    $cal_templates->set_var ('endday_options', $day_options);

    $end_year = date ('Y', strtotime ($A['enddate']));
    $year_options = COM_getYearFormOptions ($end_year);
    $cal_templates->set_var ('endyear_options', $year_options);

    if (isset ($_CA_CONF['hour_mode']) && ($_CA_CONF['hour_mode'] == 24)) {
        $end_hour = date ('H', strtotime ($A['enddate']));
        $hour_options = COM_getHourFormOptions ($end_hour, 24);
        $cal_templates->set_var ('endhour_options', $hour_options);
    } else {
        $end_hour = date ('g', strtotime ($A['enddate']));
        $hour_options = COM_getHourFormOptions ($end_hour);
        $cal_templates->set_var ('endhour_options', $hour_options);
    }

    $endmin = intval (date ('i', strtotime ($A['enddate'])) / 15) * 15;
    $cal_templates->set_var ('endminute_options',
                             COM_getMinuteFormOptions ($endmin, 15));

    $ampm = date ('a', strtotime ($A['enddate']));
    $cal_templates->set_var ('endampm_selection',
                         CALENDAR_getAmPmFormSelection ('endampm_selection', $ampm));

    $cal_templates->set_var ('lang_alldayevent', $LANG_CAL_1[31]);
    if ($A['allday'] == 1) {
        $cal_templates->set_var ('allday_checked', 'checked="checked"');
    } else {
        $cal_templates->set_var ('allday_checked', '');
    }

    $cal_templates->set_var('lang_location',$LANG_CAL_1[39]);
    $cal_templates->set_var('event_location', htmlspecialchars($A['location']));

    $cal_templates->set_var('lang_addressline1', $LANG_CAL_1[32]);
    $cal_templates->set_var('event_address1', $A['address1']);
    $cal_templates->set_var('lang_addressline2', $LANG_CAL_1[33]);
    $cal_templates->set_var('event_address2', $A['address2']);

    $cal_templates->set_var('lang_city', $LANG_CAL_1[34]);
    $cal_templates->set_var('event_city', $A['city']);

    $cal_templates->set_var('lang_state', $LANG_CAL_1[35]);
    $cal_templates->set_var('state_options', '');
    $cal_templates->set_var('event_state', $A['state']);

    $cal_templates->set_var('lang_zipcode', $LANG_CAL_1[36]);
    $cal_templates->set_var('event_zipcode', $A['zipcode']);

    $cal_templates->set_var('lang_link', $LANG_CAL_1[43]);
    $cal_templates->set_var('event_url', $A['url']);

    $cal_templates->set_var('lang_description', $LANG_CAL_1[5]);
    $cal_templates->set_var('event_description',
                            nl2br (htmlspecialchars($A['description'])));

    $cal_templates->set_var('lang_htmlnotallowed', $LANG_CAL_1[44]);
    $cal_templates->set_var('lang_submit', $LANG_CAL_1[45]);
    $cal_templates->set_var('lang_delete', $LANG_CAL_1[51]);
    $cal_templates->set_var('eid', $A['eid']);
    $cal_templates->set_var('uid', $A['uid']);
    if (isset ($_CA_CONF['hour_mode']) && ($_CA_CONF['hour_mode'] == 24)) {
        $cal_templates->set_var ('hour_mode', 24);
    } else {
        $cal_templates->set_var ('hour_mode', 12);
    }
    $cal_templates->set_var('gltoken_name', CSRF_TOKEN);
    $cal_templates->set_var('gltoken', SEC_createToken());

    return $cal_templates->parse ('output', 'form');
}

// MAIN ========================================================================

$display = '';

$action = '';
if (isset ($_REQUEST['action'])) {
    $action = COM_applyFilter ($_REQUEST['action']);
}

switch ($action) {
case 'addevent':
    if (($_CA_CONF['personalcalendars'] == 1) && !COM_isAnonUser()) {
        $display .= CALENDAR_siteHeader ();

        $eid = COM_applyFilter ($_GET['eid']);
        if (!empty ($eid)) {
            $display .= CALENDAR_addUserEvent ($eid);
        } else {
            $display .= COM_showMessage (23);
        }

        $display .= CALENDAR_siteFooter ();
    } else {
        $display = COM_refresh ($_CONF['site_url'] . '/calendar/index.php');
    }
    break;

case 'saveuserevent':
    if (($_CA_CONF['personalcalendars'] == 1) && SEC_checkToken()) {
        $eid = COM_applyFilter ($_POST['eid']);
        if (!empty ($eid)) {
            $display .= CALENDAR_saveUserEvent($eid);
        } else {
            $display .= CALENDAR_siteHeader ();
            $display .= COM_showMessage (23);
            $display .= CALENDAR_siteFooter ();
        }
    } else {
        $display = COM_refresh ($_CONF['site_url'] . '/calendar/index.php');
    }
    break;

case $LANG_CAL_1[45]: // save edited personal event
    if (!empty($LANG_CAL_1[45]) && ($_CA_CONF['personalcalendars'] == 1) && (!COM_isAnonUser()) &&
            (isset ($_POST['calendar_type']) &&
             ($_POST['calendar_type'] == 'personal')) && SEC_checkToken()) {
        $display = plugin_savesubmission_calendar ($_POST);
    } else {
        $display = COM_refresh ($_CONF['site_url'] . '/calendar/index.php');
    }
    break;

case 'deleteevent':
case $LANG_CAL_1[51]:
    if (($_CA_CONF['personalcalendars'] == 1) && SEC_checkToken()) {
        $eid = COM_applyFilter ($_REQUEST['eid']);
        if (!empty ($eid) && (!COM_isAnonUser() )) {
            DB_query ("DELETE FROM {$_TABLES['personal_events']} WHERE uid={$_USER['uid']} AND eid='".DB_escapeString($eid)."'");
            $display .= COM_refresh ($_CONF['site_url']
                     . '/calendar/index.php?mode=personal&amp;msg=26');
        } else {
            $display = COM_refresh ($_CONF['site_url'] . '/calendar/index.php');
        }
    } else {
        $display = COM_refresh ($_CONF['site_url'] . '/calendar/index.php');
    }
    break;

case 'edit':
    if ($_CA_CONF['personalcalendars'] == 1) {
        $eid = COM_applyFilter ($_GET['eid']);
        if (!empty ($eid) && (!COM_isAnonUser())) {
            $result = DB_query ("SELECT * FROM {$_TABLES['personal_events']} WHERE (eid = '".DB_escapeString($eid)."') AND (uid = {$_USER['uid']})");
            if (DB_numRows ($result) == 1) {
                $A = DB_fetchArray ($result);
                $display .= CALENDAR_siteHeader ($LANG_CAL_2[38])
                         . COM_startBlock ($LANG_CAL_2[38])
                         . CALENDAR_editPersonalEvent($A)
                         . COM_endBlock ()
                         . CALENDAR_siteFooter ();
            } else {
                $display = COM_refresh ($_CONF['site_url'] . '/calendar/index.php');
            }
        } else {
            $display = COM_refresh ($_CONF['site_url'] . '/calendar/index.php');
        }
    } else {
        $display = COM_refresh ($_CONF['site_url'] . '/calendar/index.php');
    }
    break;

default:
    $mode = '';
    if (isset ($_GET['mode'])) {
        $mode = COM_applyFilter ($_GET['mode']);
    }
    $eid = '';
    if (isset ($_GET['eid'])) {
        $eid = COM_applyFilter ($_GET['eid']);
    }
    $query = '';
    if (isset($_GET['query'])) {
        $query = COM_applyFilter($_GET['query']);
    }
    if (!empty ($eid)) {
        if (($mode == 'personal') && ($_CA_CONF['personalcalendars'] == 1) &&
                (!COM_isAnonUser() )) {
            $datesql = "SELECT * FROM {$_TABLES['personal_events']} "
                     . "WHERE (eid = '$eid') AND (uid = {$_USER['uid']}) AND status = 1";
            $pagetitle = $LANG_CAL_2[28] . ' ' . COM_getDisplayName();
        } else {
            $datesql = "SELECT * FROM {$_TABLES['events']} WHERE eid = '$eid' AND status = 1";
            if (strpos ($LANG_CAL_2[9], '%') === false) {
                $pagetitle = $LANG_CAL_2[9];
            } else {
                $pagetitle = sprintf ($LANG_CAL_2[9], $_CONF['site_name']);
            }
            DB_query ("UPDATE {$_TABLES['events']} SET hits = hits + 1 WHERE eid = '".DB_escapeString($eid)."'");
        }

        $display .= CALENDAR_siteHeader( $pagetitle);
        if (isset($_GET['msg'])) {
            $msg = COM_applyFilter($_GET['msg'], true);
            if ($msg > 0) {
                $display .= COM_showMessage($msg, 'calendar');
            }
        }
        $display .= COM_startBlock($pagetitle);

    } else {
        $year = 0;
        if (isset ($_GET['year'])) {
            $year = COM_applyFilter ($_GET['year'], true);
        }
        $month = 0;
        if (isset ($_GET['month'])) {
            $month = COM_applyFilter ($_GET['month'], true);
        }
        $day = 0;
        if (isset ($_GET['day'])) {
            $day = COM_applyFilter ($_GET['day'], true);
        }
        if (($year == 0) || ($month == 0) || ($day == 0)) {
            $year = date ('Y');
            $month = date ('n');
            $day = date ('j');
        }

        $pagetitle = $LANG_CAL_2[10] . ' ' . strftime ('%x',
                                         mktime (0, 0, 0, $month, $day, $year));
        $display .= CALENDAR_siteHeader ( $pagetitle);
        $display .= COM_startBlock ($pagetitle);

        $thedate = sprintf ('%4d-%02d-%02d', $year, $month, $day);
        $datesql = "SELECT * FROM {$_TABLES['events']} "
                 . "WHERE \"$thedate\" BETWEEN DATE_FORMAT(datestart,'%Y-%m-%d') "
                 . "and DATE_FORMAT(dateend,'%Y-%m-%d') "
                 . "ORDER BY datestart ASC,timestart ASC,title";
    }
    $cal_templates = new Template($_CONF['path'] . 'plugins/calendar/templates/');
    $cal_templates->set_file (array (
            'events'    => 'events.thtml',
            'details'   => 'eventdetails.thtml',
            'addremove' => 'addremoveevent.thtml'
            ));

    $cal_templates->set_var ( 'xhtml', XHTML );
    if ($mode != 'personal') {
        if ( $_CA_CONF['only_admin_submit'] == 1 ) {
            if ( SEC_hasRights('calendar.edit') ) {
                $cal_templates->set_var ('lang_addevent', $LANG_CAL_1[6]);
            }
        } else {
            $cal_templates->set_var ('lang_addevent', $LANG_CAL_1[6]);
        }
    } else {
        $cal_templates->set_var ('lang_addevent', $LANG_CAL_1[6]);
    }

//    if ( $mode != 'personal' && $_CA_CONF['only_admin_submit'] == 1 && !SEC_hasRights('calendar.edit') ) {
//        $cal_templates->set_var ('lang_addevent', $LANG_CAL_1[6]);
//    }
    $cal_templates->set_var ('lang_backtocalendar', $LANG_CAL_1[15]);
    if ($mode == 'personal') {
        $cal_templates->set_var ('calendar_mode', 'mode=personal');
    } else {
        $cal_templates->set_var ('calendar_mode', '');
    }

    $result = DB_query($datesql);
    $nrows = DB_numRows($result);
    if ($nrows == 0) {
        $cal_templates->set_var('lang_month','');
        $cal_templates->set_var('event_year','');
        $cal_templates->set_var('event_details','');
        $cal_templates->set_var('site_url', $_CONF['site_url']);
        $cal_templates->set_var('site_admin_url', $_CONF['site_admin_url']);
        $cal_templates->set_var('layout_url', $_CONF['layout_url']);
        $cal_templates->parse('output','events');
        $display .= $cal_templates->finish($cal_templates->get_var('output'));
        $display .= $LANG_CAL_1[2];
    } else {
        $cal = new Calendar();
        CALENDAR_setLanguage($cal);

        $currentmonth = '';
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray($result);
            if (SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],
                              $A['perm_group'],$A['perm_members'],$A['perm_anon']) > 0) {
                if (strftime('%B',strtotime($A['datestart'])) != $currentmonth) {
                    $str_month = $cal->getMonthName(strftime('%m',strtotime($A['datestart'])));
                    $cal_templates->set_var('lang_month', $str_month);
                    $cal_templates->set_var('event_year', strftime('%Y',strtotime($A['datestart'])));
// full month name
                    $currentmonth = strftime('%B',strtotime($A['datestart']));
                }
                $cal_templates->set_var('event_title', stripslashes($A['title']));
                $cal_templates->set_var('site_url', $_CONF['site_url']);
                $cal_templates->set_var('site_admin_url', $_CONF['site_admin_url']);
                $cal_templates->set_var('layout_url', $_CONF['layout_url']);
                $event_title = $A['title'];
                if (!empty($A['url'])) {
                    $event_title = COM_createLink($event_title, $A['url']);
                }
                $cal_templates->set_var('event_title', $event_title);
                if (($_CA_CONF['personalcalendars'] == 1)
                        && !COM_isAnonUser()) {
                    $tmpresult = DB_query("SELECT * FROM {$_TABLES['personal_events']} WHERE eid='".DB_escapeString($A['eid'])."' AND uid={$_USER['uid']}");
                    $tmpnrows = DB_numRows($tmpresult);
                    if ($tmpnrows > 0) {
                        $token = SEC_createToken();
                        $addremovelink = $_CONF['site_url']
                             . '/calendar/event.php?eid=' . $A['eid']
                             . '&amp;mode=personal&amp;action=deleteevent&amp;'
                             . CSRF_TOKEN . '=' . $token;
                        $addremovetxt = $LANG_CAL_1[10];
                    } else {
                        $addremovelink = $_CONF['site_url']
                            . '/calendar/event.php?eid=' . $A['eid']
                            . '&amp;mode=personal&amp;action=addevent';
                        $addremovetxt = $LANG_CAL_1[9];
                    }
                    $cal_templates->set_var('lang_addremovefromcal',
                        COM_createLink($addremovetxt, $addremovelink));
                    $cal_templates->parse('addremove_event','addremove');
                }
                $cal_templates->set_var('lang_when', $LANG_CAL_1[3]);
                if ($A['allday'] == 0) {
                    $thedatetime = COM_getUserDateTimeFormat ($A['datestart'] .
                                                        ' ' . $A['timestart']);
                    $cal_templates->set_var ('event_start', $thedatetime[0]);

                    if ($A['datestart'] == $A['dateend']) {
// HH:MM
                        $thedatetime[0] = strftime ('%I:%M%p',
                            strtotime ($A['dateend'] . ' ' . $A['timeend']));

                    } else {
                        $thedatetime = COM_getUserDateTimeFormat ($A['dateend']
                                                        . ' ' . $A['timeend']);
                    }
                    $cal_templates->set_var ('event_end', $thedatetime[0]);

                } else if ($A['allday'] == 1 AND $A['datestart'] <> $A['dateend']) {
// DOW_NAME . %x(full date based on local)
                    $thedatetime1 = strftime ('%A, ' . '%x',
                                             strtotime ($A['datestart']));
                    $cal_templates->set_var ('event_start', $thedatetime1);
                    $thedatetime2 = strftime ('%A, ' . '%x',
                                                  strtotime ($A['dateend']));
                    $cal_templates->set_var ('event_end', $thedatetime2
                                                        . ' ' . $LANG_CAL_2[26]);
                } else {
                    $thedatetime = strftime ('%A, ' . '%x',
                                             strtotime ($A['datestart']));
                    $cal_templates->set_var ('event_start', $thedatetime);
                    $cal_templates->set_var ('event_end', $LANG_CAL_2[26]);
                }

                // set the location variables
                $cal_templates->set_var ('lang_where', $LANG_CAL_1[4]);
                $cal_templates->set_var ('event_location',
                                         $A['location']);
                $cal_templates->set_var ('event_address1',
                                         $A['address1']);
                $cal_templates->set_var ('event_address2',
                                         $A['address2']);
                $cal_templates->set_var ('event_zip', $A['zipcode']);
                $cal_templates->set_var ('event_city',
                                         $A['city']);
                $cal_templates->set_var ('event_state_only', $A['state']);
                if (empty ($A['state']) || ($A['state'] == '--')) {
                    $cal_templates->set_var ('event_state', '');
                    $cal_templates->set_var ('event_state_name', '');
                    $cal_templates->set_var ('event_state_name_only', '');
                } else {
                    $cal_templates->set_var ('event_state', ', ' . $A['state']);
                    $cal_templates->set_var ('event_state_name', $A['state']);
                    $cal_templates->set_var ('event_state_name_only',
                                             $A['state']);
                }

                // now figure out which of the {brX} variables to set ...
                $hasCityEtc = (!empty ($A['city']) || !empty ($A['zip']) ||
                               !empty ($A['state']));
                if (empty ($A['location']) && empty ($A['address1']) &&
                        empty ($A['address2']) && !$hasCityEtc) {
                    $cal_templates->set_var ('br0', '');
                    $cal_templates->set_var ('br1', '');
                    $cal_templates->set_var ('br2', '');
                } else {
                    if (empty ($A['location']) || (empty ($A['address1']) &&
                                    empty ($A['address2']) && !$hasCityEtc)) {
                        $cal_templates->set_var ('br0', '');
                    } else {
                        $cal_templates->set_var ('br0', '<br/>');
                    }
                    if (empty ($A['address1']) || (empty ($A['address2']) &&
                                                   !$hasCityEtc)) {
                        $cal_templates->set_var ('br1', '');
                    } else {
                        $cal_templates->set_var ('br1', '<br/>');
                    }
                    if (empty ($A['address2']) || !$hasCityEtc) {
                        $cal_templates->set_var ('br2', '');
                    } else {
                        $cal_templates->set_var ('br2', '<br/>');
                    }
                }

                $cal_templates->set_var('lang_description', $LANG_CAL_1[5]);
                $description = $A['description'];
                if (empty($A['postmode']) || ($A['postmode'] == 'plaintext')) {
                    $description = nl2br($description);
                }
                $description = PLG_replaceTags($description,'calendar','description');
                if (!empty($query)) {
                    $description = COM_highlightQuery($description, $query);
                }
                $cal_templates->set_var ('event_description', $description);
                $cal_templates->set_var ('lang_event_type', $LANG_CAL_1[37]);
                $cal_templates->set_var ('event_type', $A['event_type']);

                if ($mode == 'personal') {
                    $editurl = $_CONF['site_url']
                             . '/calendar/event.php?action=edit' . '&amp;eid='
                             . $A['eid'];
                    $cal_templates->set_var('event_edit',
                            COM_createLink($LANG01[4], $editurl));
                    $img = '<img src="' . $_CONF['layout_url'] . '/images/edit.'
                        . $_IMAGE_TYPE . '" alt="' . $LANG01[4] . '" title="'
                        . $LANG01[4] . '"/>';
                    $cal_templates->set_var('edit_icon',
                            COM_createLink($img, $editurl));
                } else if ((SEC_hasAccess ($A['owner_id'], $A['group_id'],
                        $A['perm_owner'], $A['perm_group'], $A['perm_members'],
                        $A['perm_anon']) == 3) && SEC_hasRights ('calendar.edit')) {
                    $editurl = $_CONF['site_admin_url']
                             . '/plugins/calendar/index.php?edit=x&amp;eid='
                             . $A['eid'];
                    $cal_templates->set_var('event_edit',
                            COM_createLink($LANG01[4], $editurl));
                    $img = '<img src="' . $_CONF['layout_url'] . '/images/edit.'
                        . $_IMAGE_TYPE . '" alt="' . $LANG01[4] . '" title="'
                        . $LANG01[4] . '"/>';
                    $cal_templates->set_var('edit_icon',
                            COM_createLink($img, $editurl));
                    $cal_templates->set_var('hits_admin',
                                            COM_numberFormat($A['hits']));
                    $cal_templates->set_var('lang_hits_admin', $LANG10[30]);
                } else {
                    $cal_templates->set_var('event_edit', '');
                    $cal_templates->set_var('edit_icon', '');
                }
                if ($mode == 'personal') {
                    // personal events don't have a hits counter
                    $cal_templates->set_var('lang_hits', '');
                    $cal_templates->set_var('hits', '');
                } else {
                    $cal_templates->set_var('lang_hits', $LANG10[30]);
                    $cal_templates->set_var('hits', COM_numberFormat($A['hits']));
                }
                $cal_templates->parse ('event_details', 'details', true);
            }
        }

        $cal_templates->parse ('output', 'events');
        $display .= $cal_templates->finish ($cal_templates->get_var ('output'));
    }

    $display .= COM_endBlock() . CALENDAR_siteFooter();

} // end switch

echo $display;

?>