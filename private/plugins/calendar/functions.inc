<?php
// +--------------------------------------------------------------------------+
// | Calendar Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2010-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony AT tonybibbs DOT com                    |
// |          Tom Willett      - twillett AT users DOT sourceforge DOT net    |
// |          Blaine Lang      - langmail AT sympatico DOT ca                 |
// |          Dirk Haun        - dirk AT haun-online DOT de                   |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$plugin_path = $_CONF['path'] . 'plugins/calendar/';

// load the language file(s), including custom strings if any

$langfile = $_CONF['path'] . 'plugins/calendar/language/' . $_CONF['language'] . '.php';
$custfile = $_CONF['path'] . 'plugins/calendar/language/custom/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/calendar/language/english.php';
    $custfile = $_CONF['path'].'plugins/calendar/language/custom/english.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}
/*
* Check and see if we need to load the plugin configuration
*/
if (!isset($_CA_CONF['calendarloginrequired'])) {
    $ca_config = config::get_instance();
    $_CA_CONF = $ca_config->get_config('calendar');
}

require_once $_CONF['path'].'plugins/calendar/calendar.php';

// +---------------------------------------------------------------------------+
// | glFusion Plugin API Implementations                                       |
// +---------------------------------------------------------------------------+

/**
* Returns the items for this plugin that should appear on the main menu
*
* NOTE: this MUST return the url/value pairs in the following format
* $<arrayname>[<label>] = <url>
*
*/
function plugin_getmenuitems_calendar ()
{
    global $_CONF, $_USER, $_CA_CONF, $LANG_CAL_1;

    $anon = (COM_isAnonUser() ) ? true : false;
    if (($_CA_CONF['hidecalendarmenu'] == 1) || ($anon &&
            ($_CONF['loginrequired'] || $_CA_CONF['calendarloginrequired']))) {
        return false;
    }

    $menuitems[$LANG_CAL_1[16]] = $_CONF['site_url'] . '/calendar/index.php';

    return $menuitems;
}

/**
* Returns the upcoming events block
*
* Returns the HTML for any upcoming events in the calendar
*
* @param        string      $help       Help file for block
* @param        string      $title      Title to be used in block header
* @return   string  HTML formatted block containing events.
*/
function phpblock_calendar( $help='', $title='' )
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $_CA_CONF, $LANG_CAL_1, $_DB_dbms;

    $retval = '';

    if( !$_USER['noboxes'] && $_CA_CONF['showupcomingevents'] ) {
        $range = $_CA_CONF['upcomingeventsrange'];
        if( $range == 0 ) {
            $range = 14; // fallback: 14 days
        }
        $dt = new Date(time(),$_USER['tzid']);
        $dateonly = $_CONF['dateonly'];
        if( empty( $dateonly )) {
            $dateonly = 'd-M'; // fallback: day - abbrev. month name
        }

        if( empty( $title )) {
            $title = DB_getItem( $_TABLES['blocks'], 'title',
                                 "name = 'events_block'" );
        }

        $eventSql = 'SELECT eid,title,url,datestart,dateend,group_id,owner_id,perm_owner,perm_group,perm_members,perm_anon,UNIX_TIMESTAMP(datestart) AS datestart_unix, UNIX_TIMESTAMP(dateend) AS dateend_unix '
        . "FROM {$_TABLES['events']} ";
        $eventSql .= "WHERE dateend >= (NOW() - INTERVAL 24 HOUR) AND (TO_DAYS(datestart) - TO_DAYS(NOW()) < $range ) AND (status = 1)";
        $eventSql .= 'ORDER BY datestart,timestart';

        if(( $_CA_CONF['personalcalendars'] == 1 ) && !COM_isAnonUser() ) {
            $personaleventsql = 'SELECT eid,title,url,datestart,dateend,group_id,owner_id,perm_owner,perm_group,perm_members,perm_anon,UNIX_TIMESTAMP(datestart) AS datestart_unix, UNIX_TIMESTAMP(dateend) AS dateend_unix '
            . "FROM {$_TABLES['personal_events']} ";
            $personaleventsql .= "WHERE uid = {$_USER['uid']} AND dateend >= (NOW() - INTERVAL 24 HOUR)  AND (TO_DAYS(datestart) - TO_DAYS(NOW()) < $range) ";
            $personaleventsql .= 'ORDER BY datestart, dateend';
        }

        $allEvents = DB_query( $eventSql );
        $numRows = DB_numRows( $allEvents );
        $totalrows = $numRows;

        $numDays = 0;          // Without limits, I'll force them.
        $theRow = 1;           // Start with today!
        $oldDate1 = 'no_day';  // Invalid Date!
        $oldDate2 = 'last_d';  // Invalid Date!

        if (($_CA_CONF['personalcalendars'] == 1) AND !COM_isAnonUser()) {
            $iterations = 2;
        } else {
            $iterations = 1;
        }

        $eventsFound = 0;
        $skipFirstBreak = false;

        for( $z = 1; $z <= $iterations; $z++ ) {
            if( $z == 2 ) {
                $allEvents = DB_query( $personaleventsql );
                $numRows = DB_numRows( $allEvents );
                $totalrows = $totalrows + $numRows;

                $numDays = 0;          // Without limits, I'll force them.
                $theRow = 1;           // Start with today!
                $oldDate1 = 'no_day';  // Invalid Date!
                $oldDate2 = 'last_d';  // Invalid Date!
                $classname = 'list-new-plugins';
                $headline = false;
            } else {
                $classname = 'list-new-plugins';
                $headline = false;
            }
            if( $_CA_CONF['personalcalendars'] == 0 ) {
                $headline = true; // no headline needed
                $skipFirstBreak = true;
            }

            while( $theRow <= $numRows AND $numDays < $range ) {
                // Retreive the next event, and format the start date.
                $theEvent = DB_fetchArray( $allEvents );

                if( SEC_hasAccess( $theEvent['owner_id'], $theEvent['group_id'],
                                   $theEvent['perm_owner'], $theEvent['perm_group'],
                                   $theEvent['perm_members'], $theEvent['perm_anon'] ) > 0 ) {
                    $eventsFound++;

                    if( !$headline ) {
                        if($z == 2) {
                            if( $numRows > 0 ) {
                                $retval .= '<b>' . $LANG_CAL_1[23] . '</b>';
                            }
                        } else {
                            if( $totalrows > 0 ) {
                                $retval .= '<b>' . $LANG_CAL_1[24] . '</b>';
                            }
                        }
                        $headline = true;
                    }

                    // Start Date strings...
                    $startDate = $theEvent['datestart'];
                    $theTime1 = strtotime( $startDate );
                    $dayName1 = strftime( '%A', $theTime1 );
                    if ( $_SYSTEM['swedish_date_hack'] == true && function_exists('iconv') ) {
                        $dayName1 = iconv('ISO-8859-1','UTF-8',$dayName1);
                    }
                    $dt->setTimestamp($theEvent['datestart_unix']);
                    $abbrDate1 = $dt->format($dateonly,true);
                    // End Date strings...
                    $endDate = $theEvent['dateend'];
                    $theTime2 = strtotime( $endDate );
                    $dayName2 = strftime( '%A', $theTime2 );
                    if ( $_SYSTEM['swedish_date_hack'] == true && function_exists('iconv') ) {
                        $dayName2 = iconv('ISO-8859-1','UTF-8',$dayName2);
                    }
                    $dt->setTimestamp($theEvent['dateend_unix']);
                    $abbrDate2 = $dt->format($dateonly,true);
                    if ( COM_getCharset() == 'utf-8' ) {
                        if ( !MBYTE_is_utf8($dayName1) ) {
                            $dayName1 = utf8_encode($dayName1);
                        }
                        if ( !MBYTE_is_utf8($dayName2) ) {
                            $dayName2 = utf8_encode($dayName2);
                        }
                    }

                    $todaysEvent = false;
                    if( date( 'Ymd', $theTime1 ) == date( 'Ymd', time())) {
                        $todaysEvent = true;
                        if( $z == 2 ) {
                            $todaysClassName = 'personal-event-today';
                        } else {
                            $todaysClassName = 'site-event-today';
                        }
                    }

                    // If either of the dates [start/end] change, then display a new header.
                    if( $oldDate1 != $abbrDate1 OR $oldDate2 != $abbrDate2 ) {
                        $oldDate1 = $abbrDate1;
                        $oldDate2 = $abbrDate2;
                        $numDays ++;

                        if( $numDays < $range ) {
                            if( !empty( $newevents )) {
                                 $retval .= COM_makeList( $newevents, $classname );
                            }
                            if( $skipFirstBreak ) {
                                $skipFirstBreak = false;
                            } else {
                                $retval .= '<br>';
                            }
                            if( $todaysEvent ) {
                                $retval .= '<span class="' . $todaysClassName . '">';
                            }
                            $retval .= '<b>' . $dayName1 . '</b>&nbsp;<small>'
                                    . $abbrDate1 . '</small>';

                            // If different start and end dates, then display end date:
                            if( $abbrDate1 != $abbrDate2 ) {
                                $retval .= ' - <b>' . $dayName2 . '</b>&nbsp;<small>' . $abbrDate2 . '</small>';
                            }
                            if( $todaysEvent ) {
                                $retval .= '</span>';
                            }
                        }
                        $newevents = array();
                    }

                    // Now display this event record.
                    if( $numDays < $range ) {
                        // Display the url now!
                        $newevent_url = $_CONF['site_url']
                                  . '/calendar/event.php?';

                        if( $z == 2 ) {
                            $newevent_url .= 'mode=personal&amp;';
                        }

                        $newevent_url .= 'eid=' . $theEvent['eid'] /*. '"' */;
                        $attr = array();
                        if( $todaysEvent ) {
                            $attr = array('class' => $todaysClassName);
                        }

                        $newevent = COM_createLink(
                             $theEvent['title'],
                            $newevent_url,
                            $attr
                        );
                        $newevents[] = $newevent;
                    }

                    if( !empty( $newevents )) {
                        $retval .= COM_makeList( $newevents, $classname );
                        $newevents = array();
                    }
                }
                $theRow++;
            }
        } // end for z

        if( $eventsFound == 0 ) {
            // There aren't any upcoming events, show a nice message
            $retval .= $LANG_CAL_1[25];
        }
    }

    return $retval;
}

/**
*
* Checks that the current user has the rights to moderate the
* plugin, returns true if this is the case, false otherwise
*
* @return        boolean       Returns true if moderator
*
*/
function plugin_ismoderator_calendar()
{
    return SEC_hasRights('calendar.moderate');
}

/**
* Returns SQL & Language texts to moderation.php
*
* @return   mixed   Plugin object or void if not allowed
*
*/
function plugin_itemlist_calendar()
{
    global $_TABLES, $LANG_CAL_1;

    if (plugin_ismoderator_calendar()) {
        $plugin = new Plugin();
        $plugin->submissionlabel = $LANG_CAL_1[19];
        $plugin->submissionhelpfile = 'cceventsubmission.html';
        $plugin->getsubmissionssql = "SELECT eid AS id,title,datestart as day,url,owner_id AS uid "
                                   . "FROM {$_TABLES['eventsubmission']} "
                                   . "ORDER BY datestart ASC";
        $plugin->addSubmissionHeading($LANG_CAL_1[20]);
        $plugin->addSubmissionHeading($LANG_CAL_1[21]);
        $plugin->addSubmissionHeading($LANG_CAL_1[22]);
        $plugin->addSubmissionHeading($LANG_CAL_1[52]);

        return $plugin;
    }
}

/**
* returns list of moderation values
*
* The array returned contains (in order): the row 'id' label, main plugin
* table, moderation fields (comma seperated), and plugin submission table
*
* @return       array        Returns array of useful moderation values
*
*/
function plugin_moderationvalues_calendar()
{
    global $_TABLES;

    return array (
        'eid',
        $_TABLES['events'],
        "eid,status,title,description,location,address1,address2,city,state,zipcode,datestart,timestart,dateend,timeend,url",
        $_TABLES['eventsubmission']
    );
}


/**
* Performs plugin exclusive work for items approved by moderation
*
* While moderation.php handles the actual move from linkssubmission
* to links tables, within the function we handle all other approval
* relate tasks
*
* @param      string       $id      Identifying string
* @return     string       Any wanted HTML output
*
*/
function plugin_moderationapprove_calendar ($id)
{
    global $_CA_CONF, $_GROUPS, $_TABLES, $_USER;

    $A = array ();
    SEC_setDefaultPermissions ($A, $_CA_CONF['default_permissions']);

    // Since the eventsubmission table does not contain fields for the owner
    // and group, we set those to the current user. Also set the default
    // permissions as specified in the plugin's configuration
    if (isset ($_GROUPS['Calendar Admin'])) {
        $group_id = $_GROUPS['Calendar Admin'];
    } else {
        $group_id = SEC_getFeatureGroup ('calendar.moderate');
    }

    DB_query ("UPDATE {$_TABLES['events']} SET "
             ."group_id = '$group_id',perm_owner = {$A['perm_owner']}, "
             ."perm_group = {$A['perm_group']}, perm_members = {$A['perm_members']},"
             ."perm_anon = {$A['perm_anon']} WHERE eid = '".DB_escapeString($id)."'");
}

/**
* Performs plugin exclusive work for items deleted by moderation
*
* While moderation.php handles the actual removal from <plugin>submission
* table, within this function we handle all other deletion
* relate tasks
*
* @param      string       $id      Identifying string
* @return     string       Any wanted HTML output
*
*/
function plugin_moderationdelete_calendar($id)
{
    global $_TABLES;

    // these tables should not contain any rows with ml_id = $id
    // this is done 'just in case'
    DB_delete ($_TABLES['eventsubmission'], 'eid', DB_escapeString($id));

    return '';
}

/**
* Send an email notification for a new submission.
*
* @param    string  $table  Table where the new submission can be found
* @param    array   $A      submission data
*
*/
function CALENDAR_sendNotification($table, $A)
{
    global $_CONF, $_TABLES, $LANG01, $LANG08, $LANG09, $LANG_CAL_1, $LANG_CAL_2;

    $title = $A['title'];
    $description = $A['description'];

    $mailbody = "$LANG09[16]: $title\n"
              . "$LANG09[17]: " . strftime ('%d-%b',
                strtotime ($A['datestart'] . ' ' . $A['timestart']));
    if ($A['allday']) {
        $mailbody .= ' (' . $LANG_CAL_2[26] . ')';
    }
    $mailbody .= "\n";
    if (!empty ($A['url']) && ($A['url'] != 'http://')) {
        $mailbody .= "$LANG09[33]: <" . $A['url'] . ">\n";
    }
    $mailbody .= "\n" . $description . "\n\n";
    if ($table == $_TABLES['eventsubmission']) {
        $mailbody .= "$LANG01[10] <{$_CONF['site_admin_url']}/moderation.php>\n\n";
    } else {
        $mailbody .= "$LANG_CAL_1[12] <{$_CONF['site_url']}/calendar/event.php?eid={$A['eid']}>\n\n";
    }
    $mailsubject = $_CONF['site_name'] . ' ' . $LANG_CAL_2[43];

    $mailbody .= "\n------------------------------\n";
    $mailbody .= "\n$LANG08[34]\n";
    $mailbody .= "\n------------------------------\n";

    $to = array();
    $to = COM_formatEmailAddress( '',$_CONF['site_mail'] );
    COM_mail ($to, $mailsubject, $mailbody);
}

/**
* Check calendar submission form for missing fields
* and Saves a calendar submission
*
* @param    array   $A  Data for that submission
* @return   string      HTML redirect
*
*/
function plugin_savesubmission_calendar($A)
{
    global $_CONF, $_CA_CONF, $_TABLES, $_USER, $LANG12, $LANG_CAL_1;

    if ( isset($A['startampm_selection']) ) {
        $A['start_ampm'] = COM_applyFilter($A['startampm_selection']);
    } else {
        $A['start_ampm'] =     'am';
    }

    $A['title'] = strip_tags (COM_checkWords ($A['title']));
    $A['start_year'] = COM_applyFilter ($A['start_year'], true);
    $A['start_month'] = COM_applyFilter ($A['start_month'], true);
    $A['start_day'] = COM_applyFilter ($A['start_day'], true);

    // check for missing textfields
    if (empty ($A['title']) || empty ($A['start_month']) || empty ($A['start_day']) || empty ($A['start_year'])) {
        $retval .= COM_showMessageText($LANG12[23],$LANG12[22],true,'error')
                . plugin_submit_calendar ($A['calendar_type'],$A);

        return $retval;
    }
    $msg = PLG_itemPreSave('calendar',$A);
    if ( !empty($msg)) {
        $retval .= COM_showMessageText($msg,$LANG12[22],true,'error')
                . plugin_submit_calendar($A['calendar_type'],$A);
        return $retval;
    }

    // check ok, proceed to saving
    if (isset($A['end_year'])) {
        $A['end_year'] = COM_applyFilter($A['end_year'], true);
    }
    if (isset($A['end_month'])) {
        $A['end_month'] = COM_applyFilter($A['end_month'], true);
    }
    if (isset($A['end_day'])) {
        $A['end_day'] = COM_applyFilter($A['end_day'], true);
    }

    $A['datestart'] = sprintf ('%4d-%02d-%02d',
                        $A['start_year'], $A['start_month'], $A['start_day']);
    if (empty ($A['end_year']) || empty ($A['end_month']) ||
            empty ($A['end_day'])) {
        $A['dateend'] = $A['datestart'];
    } else {
        $A['dateend'] = sprintf ('%4d-%02d-%02d',
                            $A['end_year'], $A['end_month'], $A['end_day']);
    }

    // for the quickadd form, which doesn't have end date/time fields
    if (!isset ($A['end_hour'])) {
        $A['end_hour'] = $A['start_hour'];
    }
    if (!isset ($A['end_minute'])) {
        $A['end_minute'] = $A['start_minute'];
    }

    $A['title']       = (isset($A['title']) ? $A['title'] : '');
    $A['url']         = (isset($A['url']) ? COM_sanitizeUrl($A['url']) : '');
    $A['location']    = (isset($A['location']) ? $A['location'] : '');
    $A['address1']    = (isset($A['address1']) ? $A['address1'] : '');
    $A['address2']    = (isset($A['address2']) ? $A['address2'] : '');
    $A['city']        = (isset($A['city']) ? $A['city'] : '');
    $A['zipcode']     = (isset($A['zipcode']) ? $A['zipcode'] : '');
    $A['state']       = (isset($A['state']) ? $A['state'] : '');
    $A['description'] = (isset($A['description']) ? $A['description'] : '');
    $A['event_type']  = (isset($A['event_type']) ? $A['event_type'] : '');

    if ($A['url'] == 'http://') {
        // remove default entry now to avoid false spam reports
        $A['url'] = '';
    }

    // pseudo-formatted event description for the spam check
    $spamcheck = '';
    if (empty($A['url'])) {
        $spamcheck .= $A['title'];
    } else {
        $spamcheck .= COM_createLink($A['title'], $A['url']);
    }
    $spamcheck .= '<br/>' . $A['location'] . '<br/>'
               . $A['address1'] . '<br/>' . $A['address2']
               . '<br/>' . $A['city'] . ', ' . $A['zipcode']
               . '<br/>' . $A['description'] . '</p>';

    if ( COM_isAnonUser() ) {
        $uname = '';
        $email = '';
    } else {
        $uname = $_USER['username'];
        $email = $_USER['email'];
    }

    $spamData = array(
        'username' => '',
        'email' => '',
        'ip'    => $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']),
        'type' => 'event',
    );

    $result = PLG_checkforSpam($spamcheck, $_CONF['spamx'],$spamData);
    if ($result > 0) {
        COM_updateSpeedlimit ('submit');
        COM_displayMessageAndAbort ($result, 'spamx', 403, 'Forbidden');
    }

    $A['description'] = DB_escapeString (htmlspecialchars (COM_checkWords ($A['description'])));
    $A['address1'] = DB_escapeString (strip_tags (COM_checkWords ($A['address1'])));
    $A['address2'] = DB_escapeString (strip_tags (COM_checkWords ($A['address2'])));
    $A['city'] = DB_escapeString (strip_tags (COM_checkWords ($A['city'])));
    $A['zipcode'] = DB_escapeString (strip_tags (COM_checkWords ($A['zipcode'])));
    $A['state'] = DB_escapeString (strip_tags (COM_checkWords ($A['state'])));
    $A['location'] = DB_escapeString (strip_tags (COM_checkWords ($A['location'])));
    $A['event_type'] = DB_escapeString (strip_tags (COM_checkWords ($A['event_type'])));
    $A['title'] = DB_escapeString ($A['title']);

    $A['url'] = DB_escapeString(COM_sanitizeUrl($A['url']));

    if (!empty ($A['eid'])) {
        $A['eid'] = DB_escapeString (COM_applyFilter ($A['eid']));
    }
    if (empty ($A['eid'])) {
        $A['eid'] = DB_escapeString (COM_makeSid ());
    }

    COM_updateSpeedlimit ('submit');

    if (!isset($A['status']) OR (isset ($A['status']) && ($A['status'] == 'on'))) {
        $A['status'] = 1;
    } else {
        $A['status'] = 0;
    }

    if (isset ($A['allday']) && ($A['allday'] == 'on')) {
        $A['allday'] = 1;
    } else {
        $A['allday'] = 0;
    }

    if (isset ($A['hour_mode']) && ($A['hour_mode'] == 24)) {
        $start_hour = COM_applyFilter ($A['start_hour'], true);
        if ($start_hour >= 12) {
            $A['start_ampm'] = 'pm';
            $A['start_hour'] = $start_hour - 12;
        } else {
            $A['start_ampm'] = 'am';
            $A['start_hour'] = $start_hour;
        }
        if ($A['start_hour'] == 0) {
            $A['start_hour'] = 12;
        }
        $end_hour = COM_applyFilter ($A['end_hour'], true);
        if ($end_hour >= 12) {
            $A['end_ampm'] = 'pm';
            $A['end_hour'] = $end_hour - 12;
        } else {
            $A['end_ampm'] = 'am';
            $A['end_hour'] = $end_hour;
        }
        if ($A['end_hour'] == 0) {
            $A['end_hour'] = 12;
        }
    }
    if (!isset ($A['end_ampm'])) {
        $A['end_ampm'] = $A['start_ampm'];
    }

    $A['start_hour'] = COM_applyFilter ($A['start_hour'], true);
    $A['start_minute'] = COM_applyFilter ($A['start_minute'], true);
    $A['end_hour'] = COM_applyFilter ($A['end_hour'], true);
    $A['end_minute'] = COM_applyFilter ($A['end_minute'], true);

    if ($A['start_ampm'] == 'pm' AND $A['start_hour'] <> 12) {
        $A['start_hour'] = $A['start_hour'] + 12;
    }
    if ($A['start_ampm'] == 'am' AND $A['start_hour'] == 12) {
        $A['start_hour'] = '00';
    }
    if ($A['end_ampm'] == 'pm' AND $A['end_hour'] <> 12) {
        $A['end_hour'] = $A['end_hour'] + 12;
    }
    if ($A['end_ampm'] == 'am' AND $A['end_hour'] == 12) {
        $A['end_hour'] = '00';
    }
    $A['timestart'] = $A['start_hour'] . ':' . $A['start_minute'] . ':00';
    $A['timeend'] = $A['end_hour'] . ':' . $A['end_minute'] . ':00';

    if (COM_isAnonUser()) {
        $owner_id = 1; // anonymous user
    } else {
        $owner_id = $_USER['uid'];
    }

    if ($A['calendar_type'] == 'master') { // add to site calendar

        if (($_CA_CONF['eventsubmission'] == 1) &&
                !SEC_hasRights('calendar.submit')) {

            $fields = 'eid,status,title,event_type,url,owner_id,datestart,timestart,dateend,timeend,allday,location,address1,address2,city,state,zipcode,description';
            DB_save ($_TABLES['eventsubmission'],
                     'eid,status,title,event_type,url,owner_id,datestart,timestart,dateend,timeend,allday,location,address1,address2,city,state,zipcode,description',
                     "'{$A['eid']}',{$A['status']},'{$A['title']}','{$A['event_type']}','{$A['url']}',$owner_id,'{$A['datestart']}','{$A['timestart']}','{$A['dateend']}','{$A['timeend']}',{$A['allday']},'{$A['location']}','{$A['address1']}','{$A['address2']}','{$A['city']}','{$A['state']}','{$A['zipcode']}','{$A['description']}'");

            if (isset ($_CA_CONF['notification']) &&
                    ($_CA_CONF['notification'] == 1)) {
                CALENDAR_sendNotification ($_TABLES['eventsubmission'], $A);
            }

            $retval = COM_refresh($_CONF['site_url']
                                  . '/calendar/index.php?msg=4');
        } else {
            if (COM_isAnonUser()) {
                $owner_id = 1; // anonymous user
            } else {
                $owner_id = $_USER['uid'];
            }

            if (SEC_hasRights('calendar.submit')) {
                $A['group_id'] = SEC_getFeatureGroup('calendar.submit');
            } else {
                $A['group_id'] = DB_getItem($_TABLES['groups'], 'grp_id',
                                            "grp_name = 'All Users'");
            }
            SEC_setDefaultPermissions($A, $_CA_CONF['default_permissions']);

            DB_save ($_TABLES['events'],
                     'eid,status,title,event_type,url,datestart,timestart,dateend,timeend,allday,location,address1,address2,city,state,zipcode,description,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon',
                     "'{$A['eid']}',{$A['status']},'{$A['title']}','{$A['event_type']}','{$A['url']}','{$A['datestart']}','{$A['timestart']}','{$A['dateend']}','{$A['timeend']}',{$A['allday']},'{$A['location']}','{$A['address1']}','{$A['address2']}','{$A['city']}','{$A['state']}','{$A['zipcode']}','{$A['description']}',$owner_id,{$A['group_id']},{$A['perm_owner']},{$A['perm_group']},{$A['perm_members']},{$A['perm_anon']}");

            PLG_itemSaved($A['eid'], 'calendar');

            if (isset($_CA_CONF['notification']) &&
                    ($_CA_CONF['notification'] == 1)) {
                CALENDAR_sendNotification($_TABLES['events'], $A);
            }
            COM_rdfUpToDateCheck('calendar', $A['event_type'], $A['eid']);

            $retval = COM_refresh($_CONF['site_url']
                                  . '/calendar/index.php?msg=17');
        }

    } else if ($_CA_CONF['personalcalendars'] == 1) { // add to personal calendar
        if (COM_isAnonUser()) {
            // anonymous users don't have personal calendars - bail
            COM_accessLog("Attempt to write to the personal calendar of user '{$A['uid']}'.");

            $retval = COM_refresh($_CONF['site_url'] . '/calendar/index.php');
        } else {
            DB_save ($_TABLES['personal_events'],
                     'uid,eid,status,title,event_type,url,datestart,timestart,dateend,timeend,allday,location,address1,address2,city,state,zipcode,description',
                     "{$_USER['uid']},'{$A['eid']}',{$A['status']},'{$A['title']}','{$A['event_type']}','{$A['url']}','{$A['datestart']}','{$A['timestart']}','{$A['dateend']}','{$A['timeend']}',{$A['allday']},'{$A['location']}','{$A['address1']}','{$A['address2']}','{$A['city']}','{$A['state']}','{$A['zipcode']}','{$A['description']}'");
            $retval = COM_refresh ($_CONF['site_url']
                                   . '/calendar/index.php?mode=personal&amp;msg=17');
        }

    } else { // personal calendars are disabled
        $retval = COM_refresh ($_CONF['site_url'] . '/calendar/index.php');
    }

    return $retval;
}

function plugin_getheadercss_calendar()
{
    global $_CONF;

    $styles = array();

    if ( @file_exists($_CONF['path_layout'].'plugins/calendar/style.css') ) {
        $styleFile = $_CONF['path_layout'].'plugins/calendar/style.css';
    } else {
        $styleFile = $_CONF['path_html'].'/calendar/style.css';
    }
	$styles[] = $styleFile;

    return $styles;
}

/**
* Shows event submission form or diverts to event editor if admin calls in
*
*/
function plugin_submit_calendar($mode = 'master',$A = array())
{
    global $_CONF, $_USER, $_CA_CONF, $LANG_CAL_1;

    $valid_modes = array('personal','master');

    if (isset($_POST['calendar_type'])) {
        $mode = $_POST['calendar_type'];
    } else if (isset($_REQUEST['mode']) && ($_REQUEST['mode'] == 'personal')) {
        $mode = 'personal';
    } else {
        $mode = 'master';
    }

    if ( !in_array($mode,$valid_modes) ) {
        $mode = 'master';
    }

    if (($_CA_CONF['personalcalendars'] == 1) && ($mode == 'quickadd')) {
        // quick add form, just save it.
        $display = plugin_savesubmission_calendar($_POST);
        return $display;
    } else if (SEC_hasRights('calendar.edit') && ($mode != 'personal')) {
        // admin posts non-personal, go to editor
        if (isset ($_REQUEST['year'])) {
            $year = COM_applyFilter ($_REQUEST['year'], true);
        } else {
            $year = date ('Y', time ());
        }
        if (isset ($_REQUEST['month'])) {
            $month = COM_applyFilter ($_REQUEST['month'], true);
        } else {
            $month = date ('m', time ());
        }
        if (isset ($_REQUEST['day'])) {
            $day = COM_applyFilter ($_REQUEST['day'], true);
        } else {
            $day = date ('d', time ());
        }
        if (isset ($_REQUEST['hour'])) {
            $hour = COM_applyFilter ($_REQUEST['hour'], true);
        } else {
            $hour = date ('H', time ());
        }
        $startat = '';
        if ($year > 0) {
            $startat = '&amp;datestart='
                     . urlencode (sprintf ('%04d-%02d-%02d', $year,
                                           $month, $day))
                     . '&amp;timestart=' . urlencode (sprintf ('%02d:00:00',
                                                           $hour));
        }

        echo COM_refresh ($_CONF['site_admin_url']
                          . '/plugins/calendar/index.php?edit=x' . $startat);
        exit;
    }
    // otherwise non-admin or admin-personal. do personal form or public submission.

    if (isset ($_REQUEST['year'])) {
        $year = COM_applyFilter ($_REQUEST['year'], true);
    } else {
        $year = date ('Y', time ());
    }
    if (isset ($_REQUEST['month'])) {
        $month = COM_applyFilter ($_REQUEST['month'], true);
    } else {
        $month = date ('m', time ());
    }
    if (isset ($_REQUEST['day'])) {
        $day = COM_applyFilter ($_REQUEST['day'], true);
    } else {
        $day = date ('d', time ());
    }
    if (isset ($_REQUEST['hour'])) {
        $hour = COM_applyFilter ($_REQUEST['hour'], true);
    } else {
        $hour = date ('H', time ());
    }

    // Pull all the POST data so we can pre-fill the form.

    $title      = isset($_POST['title']) ? $_POST['title'] : '';
    $event_type = isset($_POST['event_type']) ? $_POST['event_type'] : '';
    $url        = isset($_POST['url']) ? $_POST['url'] : '';
    $allday     = isset($_POST['allday']) ? COM_applyFilter ($_POST['allday']) : '';
    $start_month = isset($_POST['start_month']) ? COM_applyFilter ($_POST['start_month'], true) : $month;
    $start_day   = isset($_POST['start_day']) ? COM_applyFilter ($_POST['start_day'], true) : $day;
    $start_year  = isset($_POST['start_year']) ? COM_applyFilter ($_POST['start_year'], true) : $year;
    $start_hour  = isset($_POST['start_hour']) ? COM_applyFilter ($_POST['start_hour'], true) : $hour;
    $start_minute = isset($_POST['start_minute']) ? COM_applyFilter ($_POST['start_minute'], true) : 0;
    $start_ampm   = isset($_POST['start_ampm']) ? $_POST['start_ampm'] : '';
    $end_month  = isset($_POST['end_month']) ? COM_applyFilter ($_POST['end_month'], true) : $month;
    $end_day    = isset($_POST['end_day']) ? COM_applyFilter ($_POST['end_day'], true) : $day;
    $end_year   = isset($_POST['end_year']) ? COM_applyFilter ($_POST['end_year'], true) : $year;
    $end_hour   = isset($_POST['end_hour']) ? COM_applyFilter ($_POST['end_hour'], true) : $hour;
    $end_minute = isset($_POST['end_minute']) ? COM_applyFilter ($_POST['end_minute'], true) : 0;
    $end_ampm   = isset($_POST['end_ampm']) ?  $_POST['end_ampm'] : '';
    $location   = isset($_POST['location']) ? $_POST['location'] : '';
    $address1   = isset($_POST['address1']) ? $_POST['address1'] : '';
    $address2   = isset($_POST['address2']) ? $_POST['address2'] : '';
    $city       = isset($_POST['city']) ? $_POST['city'] : '';
    $state      = isset($_POST['state']) ? $_POST['state'] : '';
    $zipcode    = isset($_POST['zipcode']) ? $_POST['zipcode'] : '';
    $description= isset($_POST['description']) ? $_POST['description'] : '';
    $postmode   = isset($_POST['postmode']) ? $_POST['postmode'] : '';

    $retval = '';

    $retval .= COM_startBlock ($LANG_CAL_1[26], 'submitevent.html');
    $eventform = new Template ($_CONF['path'] . 'plugins/calendar/templates/');
    $eventform->set_file ('eventform', 'submitevent.thtml');
    if ($mode != 'personal') {
        $eventform->set_var ('explanation', $LANG_CAL_1[27]);
        $eventform->set_var ('submit_url', '/calendar/index.php');
    } else {
        $eventform->set_var ('explanation', '');
        $eventform->set_var ('submit_url', '/calendar/index.php?view=savepersonal');
    }
    if (isset ($_CA_CONF['hour_mode']) && ($_CA_CONF['hour_mode'] == 24)) {
        $eventform->set_var ('hour_mode', 24);
    } else {
        $eventform->set_var ('hour_mode', 12);
    }
    $eventform->set_var ('lang_title', $LANG_CAL_1[28]);

    $eventform->set_var('lang_eventtype', $LANG_CAL_1[37]);
    $eventform->set_var('lang_editeventtypes', $LANG_CAL_1[38]);
    $eventform->set_var('type_options', CALENDAR_eventTypeList ($event_type));

    $eventform->set_var('lang_link', $LANG_CAL_1[43]);
    $eventform->set_var('max_url_length', 255);
    $eventform->set_var('lang_startdate', $LANG_CAL_1[21]);
    $eventform->set_var('lang_starttime', $LANG_CAL_1[30]);

    if (empty ($start_month) || $start_month == 0) {
        $start_month = date ('m', time ());
    }
    if (empty ($start_day) || $start_day == 0) {
        $start_day = date ('d', time ());
    }
    if (empty ($start_year)) {
        $start_year = date ('Y', time ());
    }
    $eventform->set_var ('start_month_options', COM_getMonthFormOptions ($start_month));
    $eventform->set_var ('start_day_options', COM_getDayFormOptions ($start_day));
    $eventform->set_var ('start_year_options', COM_getYearFormOptions ($start_year));


    if (empty ($end_month)) {
        $end_month = date ('m', time ());
    }
    if (empty ($end_day)) {
        $end_day = date ('d', time ());
    }
    if (empty ($end_year)) {
        $end_year = date ('Y', time ());
    }
    $eventform->set_var ('end_month_options', COM_getMonthFormOptions ($end_month));
    $eventform->set_var ('end_day_options', COM_getDayFormOptions ($end_day));
    $eventform->set_var ('end_year_options', COM_getYearFormOptions ($end_year));

    if (empty ($start_hour) || ($start_hour < 0)) {
        $start_hour = date ('H', time ());
    }
    $start_hour_24 = $start_hour % 24;
    if ($start_hour >= 12) {
        $start_ampm = 'pm';
    } else {
        $start_ampm = 'am';
    }

    $eventform->set_var ('startampm_selection',
                         CALENDAR_getAmPmFormSelection ('start_ampm', $start_ampm, 'update_ampm()'));

    if ($start_hour > 12) {
        $start_hour = $start_hour - 12;
    } else if ($start_hour == 0) {
        $start_hour = 12;
    }

    if (isset ($_CA_CONF['hour_mode']) && ($_CA_CONF['hour_mode'] == 24)) {
        $eventform->set_var ('start_hour_options',
                             COM_getHourFormOptions ($start_hour_24, 24));
    } else {
        $eventform->set_var ('start_hour_options',
                             COM_getHourFormOptions ($start_hour));
    }
    if ( empty($start_minute) ) {
        $cur_min = intval (date ('i') / 15) * 15;
    } else {
        $cur_min = $start_minute;
    }
    $eventform->set_var ('start_minute_options',
                         COM_getMinuteFormOptions ($cur_min, 15));

    // end hours
    if (empty ($end_hour) || ($end_hour < 0)) {
        $end_hour = date ('H', time ());
    }
    $end_hour_24 = $end_hour % 24;
    if ($end_hour >= 12) {
        $end_ampm = 'pm';
    } else {
        $end_ampm = 'am';
    }
    $eventform->set_var ('endampm_selection',
                         CALENDAR_getAmPmFormSelection ('end_ampm', $end_ampm));

    if ($end_hour > 12) {
        $end_hour = $end_hour - 12;
    } else if ($end_hour == 0) {
        $end_hour = 12;
    }

    if (isset ($_CA_CONF['hour_mode']) && ($_CA_CONF['hour_mode'] == 24)) {
        $eventform->set_var ('end_hour_options',
                             COM_getHourFormOptions ($end_hour_24, 24));
    } else {
        $eventform->set_var ('end_hour_options',
                             COM_getHourFormOptions ($end_hour));
    }

    if (empty($end_minute) ) {
        $end_min = intval (date ('i') / 15) * 15;
    } else {
        $end_min = $end_minute;
    }

    $eventform->set_var ('end_minute_options',
                         COM_getMinuteFormOptions ($end_min, 15));

    if ( $allday == 'on' ) {
        $eventform->set_var('all_day_checked','checked="checked"');
    }
    $eventform->set_var('lang_enddate', $LANG_CAL_1[18]);
    $eventform->set_var('lang_endtime', $LANG_CAL_1[29]);
    $eventform->set_var('lang_alldayevent',$LANG_CAL_1[31]);
    $eventform->set_var('lang_addressline1',$LANG_CAL_1[32]);
    $eventform->set_var('lang_addressline2',$LANG_CAL_1[33]);
    $eventform->set_var('lang_city',$LANG_CAL_1[34]);
    $eventform->set_var('lang_state',$LANG_CAL_1[35]);
    $eventform->set_var('state_options', '');
    $eventform->set_var('lang_zipcode',$LANG_CAL_1[36]);
    $eventform->set_var('lang_location', $LANG_CAL_1[39]);
    $eventform->set_var('lang_description', $LANG_CAL_1[5]);
    $eventform->set_var('lang_htmnotallowed', $LANG_CAL_1[44]);
    $eventform->set_var('lang_submit', $LANG_CAL_1[45]);
    $eventform->set_var('mode', $mode);
    PLG_templateSetVars('calendar',$eventform);
    if ($mode == 'personal') {
        $token = SEC_createToken();
        $hidden_fields = '<input type="hidden" name="' . CSRF_TOKEN
                       . "\" value=\"{$token}\"" . XHTML . ">";
        $eventform->set_var('hidden_fields', $hidden_fields);
    } else {
        $eventform->set_var('hidden_fields', '');
    }

    // set the fields
    $eventform->set_var('title',htmlspecialchars($title,ENT_QUOTES,COM_getEncodingt()));
    $eventform->set_var('event_type',$event_type);
    $eventform->set_var('url',htmlspecialchars($url,ENT_QUOTES,COM_getEncodingt()));
    $eventform->set_var('allday',$allday);
    $eventform->set_var('location',htmlspecialchars($location,ENT_QUOTES,COM_getEncodingt()));
    $eventform->set_var('address1',htmlspecialchars($address1,ENT_QUOTES,COM_getEncodingt()));
    $eventform->set_var('address2',htmlspecialchars($address2,ENT_QUOTES,COM_getEncodingt()));
    $eventform->set_var('city',htmlspecialchars($city,ENT_QUOTES,COM_getEncodingt()));
    $eventform->set_var('state',htmlspecialchars($state,ENT_QUOTES,COM_getEncodingt()));
    $eventform->set_var('zipcode',htmlspecialchars($zipcode,ENT_QUOTES,COM_getEncodingt()));
    $eventform->set_var('description',htmlspecialchars($description,ENT_QUOTES,COM_getEncodingt()));
    $eventform->set_var('postmode',htmlspecialchars($postmode,ENT_QUOTES,COM_getEncodingt()));
    $eventform->parse('theform', 'eventform');
    $retval .= $eventform->finish($eventform->get_var('theform'));
    $retval .= COM_endBlock();

    return $retval;
}

/**
*
* Counts the items that are submitted
*
*/
function plugin_submissioncount_calendar()
{
    global $_TABLES;

    $num = 0;

    if( SEC_hasRights( 'calendar.moderate' ))
    {
        $num += DB_count( $_TABLES['eventsubmission'] );
    }

    return $num;
}

/**
* Implements the [event:] autotag.
*
*/
function plugin_autotags_calendar ($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES, $LANG_CAL_AUTOTAG;

    if ($op == 'tagname' ) {
        return 'calendar';
    } else if ($op == 'tagusage') {
        $tagUsage = array(
            array('namespace' => 'calendar','usage' => 'description')
        );
        return $tagUsage;
    } else if ($op == 'desc' ) {
        switch ($content) {
            case 'calendar' :
                return $LANG_CAL_AUTOTAG['desc_calendar'];
                break;
            default :
                return '';
                break;
       }
    } else if ($op == 'parse') {
        if (($autotag['tag'] == 'calendar')) {
            $eid = COM_applyFilter ($autotag['parm1']);
            if (empty($eid) ) {
                $content = str_replace ($autotag['tagstr'], '', $content);
            } else {
                $perm = COM_getPermSQL( 'AND' );
                $sql  = "SELECT status,title FROM {$_TABLES['events']} WHERE eid='".DB_escapeString($eid)."' AND status=1 " . $perm;
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $C = DB_fetchArray($result);
                    $url = $_CONF['site_url'] . '/calendar/event.php?eid=' . $eid;
                    if (empty ($autotag['parm2'])) {
                        $linktext = $C['title'];
                    } else {
                        $linktext = $autotag['parm2'];
                    }
                    $link = COM_createLink($linktext, $url);
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $content = str_replace ($autotag['tagstr'], '', $content);
                }
            }
        }
        return $content;
    }
}

/*
* Do we support feeds?
*/
function plugin_getfeednames_calendar()
{
    global $LANG_CAL_1;

    $feeds = array ();

    $feeds[] = array ('id' => 'calendar', 'name' => $LANG_CAL_1[16]);

    return $feeds;

}

/**
* Get content for a feed that holds all events.
*
* @param    string   $feed     feed ID
* @param    string   $link     link to homepage
* @param    string   $update   list of story ids
* @return   array              content of the feed
*
*/
function plugin_getfeedcontent_calendar( $feed, &$link, &$update, $feedType, $feedVersion )
{
    global $_CONF, $_TABLES;

    if ( $feedType == 'ICS' ) {
        $sql = "SELECT * FROM {$_TABLES['events']} "
              ."WHERE perm_anon > 0 "
              ."AND status = 1 "
              ."ORDER BY datestart,timestart";
        $result = DB_query($sql);

        $content = array();
        $eids = array();
        $nrows = DB_numRows( $result );

        for( $i = 1; $i <= $nrows; $i++ ) {
            $row = DB_fetchArray( $result );
            $eids[] = $row['eid'];

            $eventtitle = $row['title'];

            $eventtext = SYND_truncateSummary( $row['description'], MBYTE_strlen($row['description']));
            $eventlink  = $_CONF['site_url'] . '/calendar/event.php?eid='
                        . $row['eid'];

            // Need to reparse the date from the event id
            $myyear = substr( $row['eid'], 0, 4 );
            $mymonth = substr( $row['eid'], 4, 2 );
            $myday = substr( $row['eid'], 6, 2 );
            $myhour = substr( $row['eid'], 8, 2 );
            $mymin = substr( $row['eid'], 10, 2 );
            $mysec = substr( $row['eid'], 12, 2 );
            $newtime = "{$mymonth}/{$myday}/{$myyear} {$myhour}:{$mymin}:{$mysec}";
            $creationtime = strtotime( $newtime );
            $extensionTags = array();
            $content[] = array( 'title'      => $eventtitle,
                                'summary'    => $eventtext,
                                'link'       => $eventlink,
                                'uid'        => $row['owner_id'],
                                'author'     => COM_getDisplayName( $row['owner_id'] ),
                                'date'       => $creationtime,
                                'dtstart'    => date("Ymd\THi00",strtotime($row['datestart'] . $row['timestart'])),
                                'dtend'      => date("Ymd\THi00",strtotime($row['dateend'] . $row['timeend'])),
                                'location'   => $row['location'],
                                'categories' => $row['event_type'],
                                'format'     => 'plaintext',
                                'extensions' => $extensionTags
                              );
        }

        $link = $_CONF['site_url'] . '/calendar/index.php';
        $update = implode( ',', $eids );

    } else {
        $limit = DB_getItem($_TABLES['syndication'], 'limits', "fid = '".DB_escapeString($feed)."'");

        $where = '';
        if( !empty( $limit )) {
            if( substr( $limit, -1 ) == 'h' ) {// next xx hours
                $limitsql = '';
                $hours = substr( $limit, 0, -1 );
                $where = " AND (datestart <= DATE_ADD(NOW(), INTERVAL $hours HOUR))";
            }
            else {
                $limitsql = ' LIMIT ' . $limit;
            }
        }
        else {
            $limitsql = ' LIMIT 10';
        }

        $sql = "SELECT eid,owner_id,title,description FROM {$_TABLES['events']} "
              ."WHERE status = 1 AND perm_anon > 0 AND dateend >= NOW() $where "
              ."ORDER BY datestart,timestart $limitsql";
        $result = DB_query($sql);

        $content = array();
        $eids = array();
        $nrows = DB_numRows( $result );

        for( $i = 1; $i <= $nrows; $i++ )
        {
            $row = DB_fetchArray( $result );
            $eids[] = $row['eid'];

            $eventtitle = stripslashes( $row['title'] );
            $eventtext = SYND_truncateSummary( $row['description'], MBYTE_strlen($row['description']));
            $eventlink  = $_CONF['site_url'] . '/calendar/event.php?eid='
                        . $row['eid'];

            // Need to reparse the date from the event id
            $myyear = substr( $row['eid'], 0, 4 );
            $mymonth = substr( $row['eid'], 4, 2 );
            $myday = substr( $row['eid'], 6, 2 );
            $myhour = substr( $row['eid'], 8, 2 );
            $mymin = substr( $row['eid'], 10, 2 );
            $mysec = substr( $row['eid'], 12, 2 );
            $newtime = "{$mymonth}/{$myday}/{$myyear} {$myhour}:{$mymin}:{$mysec}";
            $creationtime = strtotime( $newtime );
            $extensionTags = array(); // PLG_getFeedElementExtensions('calendar', $row['eid'], $feedType, $feedVersion, $eventtitle, );
            $content[] = array( 'title'  => $eventtitle,
                                'summary'   => $eventtext,
                                'link'   => $eventlink,
                                'uid'    => $row['owner_id'],
                                'author' => COM_getDisplayName( $row['owner_id'] ),
                                'date'   => $creationtime,
                                'format' => 'plaintext',
                                'extensions' => $extensionTags
                              );
        }

        $link = $_CONF['site_url'] . '/calendar/index.php';
        $update = implode( ',', $eids );
    }
    $update = trim($update);
    return $content;
}

/**
* Checking if calendar feeds are up to date
*
* @param    int     $feed           id of feed to be checked
* @param    string  $topic          topic (actually: category)
* @param    string  $update_data    data describing current feed contents
* @param    string  $limit          number of entries or number of hours
* @param    string  $updated_type   (optional) type of feed to be updated
* @param    string  $updated_topic  (optional) feed's "topic" to be updated
* @param    string  $updated_id     (optional) id of entry that has changed
*
*/
function plugin_feedupdatecheck_calendar ($feed, $topic, $update_data, $limit,
                    $updated_type = '', $updated_topic = '', $updated_id = '')
{
    global $_CONF, $_TABLES, $_SYND_DEBUG;

    $where = '';
    if ( !empty( $limit )) {
        if( substr( $limit, -1 ) == 'h' ) { // next xx hours
            $limitsql = '';
            $hours = substr( $limit, 0, -1 );
            $where = " AND (datestart <= DATE_ADD(NOW(), INTERVAL $hours HOUR))";
        } else {
            $limitsql = ' LIMIT ' . intval($limit);
        }
    } else {
        $limitsql = ' LIMIT 10';
    }

    $result = DB_query( "SELECT eid FROM {$_TABLES['events']} WHERE status = 1 AND perm_anon > 0 AND dateend >= NOW()$where ORDER BY datestart,timestart $limitsql" );
    $nrows = DB_numRows( $result );

    $eids = array();
    for( $i = 0; $i < $nrows; $i++ ) {
        $A = DB_fetchArray( $result );

        if ( $A['eid'] == $updated_id ) {
            // no need to look any further - this feed has to be updated
            return false;
        }

        $eids[] = $A['eid'];
    }
    $current = trim(implode( ',', $eids ));

    if ($_SYND_DEBUG) {
        COM_errorLog ("Update check for events: comparing new list ($current) with old list ($update_info)", 1);
    }

    $rc = ( $current != $update_data ) ? false : true;

    return $rc;
}


/**
* Calendar will not use comments
*/
function plugin_commentsupport_calendar()
{
    return false;
}


/**
* Shows the statistics for the Calendar plugin on stats.php.
* If $showsitestats is 1 then we are to only print the overall stats in the
* 'site statistics box' otherwise we show the detailed stats
*
* @param    int     showsitestate   Flag to let us know which stats to get
*/
function plugin_showstats_calendar ($showsitestats)
{
    global $_CONF, $_TABLES, $LANG_CAL_1;

    $display = '';

    // Top Ten Events
    $result = DB_query("SELECT eid,title,hits from {$_TABLES['events']} WHERE (status = 1) AND (hits > 0)" . COM_getPermSQL ('AND') . " ORDER BY hits DESC LIMIT 10");
    $nrows  = DB_numRows($result);
    if ($nrows > 0) {
        $header_arr = array(
            array('text' => $LANG_CAL_1[12], 'field' => 'sid', 'header_class' => 'stats-header-title'),
            array('text' => $LANG_CAL_1[48], 'field' => 'hits', 'field_class'  => 'stats-list-count'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     => false,
                          'title'        => $LANG_CAL_1[47],
        );
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray($result);
            $A['title'] = str_replace('$','&#36;',$A['title']);
            $A['sid'] = COM_createLink($A['title'], $_CONF['site_url']
                      . "/calendar/event.php?eid={$A['eid']}");
            $A['hits'] = COM_NumberFormat ($A['hits']);
            $data_arr[$i] = $A;
        }
        $display .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
    } else {
        $display .= COM_startBlock($LANG_CAL_1[47]);
        $display .= $LANG_CAL_1[49];
        $display .= COM_endBlock();
    }

    return $display;
}

/**
* New stats plugin API function for proper integration with the site stats
*
* @return   array(item text, item count);
*
*/
function plugin_statssummary_calendar ()
{
    global $LANG_CAL_1, $_TABLES;

    $result = DB_query ("SELECT COUNT(*) AS count FROM {$_TABLES['events']}" . COM_getPermSQL ());
    $A = DB_fetchArray ($result);
    return array ($LANG_CAL_1[46], COM_NumberFormat ($A['count']));
}


/**
* This will put an option for the calendar in the command and control block on
* moderation.php
*
*/
function plugin_cclabel_calendar()
{
    global $_CONF, $LANG_CAL_1;

    if (SEC_hasRights ('calendar.edit')) {
        return array ($LANG_CAL_1[16],
                $_CONF['site_admin_url'] . '/plugins/calendar/index.php',
                plugin_geticon_calendar ());
    }

    return false;
}


/**
* returns the administrative option for this plugin
*
*/
function plugin_getadminoption_calendar()
{
    global $_CONF, $_TABLES, $LANG_CAL_1;

    if (SEC_hasRights ('calendar.edit')) {
        $result = DB_query ("SELECT COUNT(*) AS cnt FROM {$_TABLES['events']}" . COM_getPermSQL ());
        $A = DB_fetchArray ($result);
        $total_events = $A['cnt'];

        return array ($LANG_CAL_1[16],
                      $_CONF['site_admin_url'] . '/plugins/calendar/index.php',
                      $total_events);
    }
}

function plugin_getuseroption_calendar()
{
    global $_CONF, $LANG_CAL_1, $_CA_CONF;

    if( $_CA_CONF['personalcalendars'] == 1 ) {
        $url = $_CONF['site_url'] . '/calendar/index.php?mode=personal';
        return array ($LANG_CAL_1[42], $url, '');
    }
}

/**
* A user is about to be merged. Update ownership of any events owned
* by that user
*
* @param   origUID   int   User id of original user
* @param   destUID   int   User id of the user receiving the items
*
*/
function plugin_user_move_calendar($origUID,$destUID)
{
    global $_TABLES, $_CA_CONF;

    $sql = "UPDATE {$_TABLES['personal_events']} SET owner_id=".(int)$destUID." WHERE owner_id=".(int)$origUID;
    DB_query($sql,1);
    $sql = "UPDATE {$_TABLES['events']} SET owner_id = ".(int) $destUID." WHERE owner_id = ".(int) $origUID;
    DB_query($sql,1);
}

/**
* A user is about to be deleted. Update ownership of any events owned
* by that user or delete them.
*
* @param   uid   int   User id of deleted user
*
*/
function plugin_user_delete_calendar ($uid)
{
    global $_TABLES, $_CA_CONF;

    DB_delete ($_TABLES['personal_events'], 'owner_id', intval($uid));

    if ($_CA_CONF['delete_event'] == 1) {
        // delete the events
        DB_delete ($_TABLES['events'], 'owner_id', intval($uid));

    } else {
        // assign ownership to a user from the Root group
        $rootgroup = DB_getItem ($_TABLES['groups'], 'grp_id',
                                 "grp_name = 'Root'");
        $result = DB_query ("SELECT DISTINCT ug_uid FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $rootgroup ORDER BY ug_uid LIMIT 1");
        list($rootuser) = DB_fetchArray ($result);
        if (intval($rootuser) < 2 ) {
            $rootuser = 2;
        }
        DB_query ("UPDATE {$_TABLES['events']} SET owner_id = $rootuser WHERE owner_id = ".intval($uid));
    }
}

/**
* Return the current version of code.
* Used in the Plugin Editor to show the registered version and code version
*/
function plugin_chkVersion_calendar()
{
    global $_CA_CONF;

    return $_CA_CONF['pi_version'];
}

/**
* Update the Calendar plugin
*
* @return   int     Number of message to display (true = generic success msg)
*
*/
function plugin_upgrade_calendar()
{
    global $_CONF, $_CA_CONF, $_TABLES;

    if ( @file_exists($_CONF['path'].'plugins/calendar/upgrade.php') ) {
        require_once $_CONF['path'].'plugins/calendar/upgrade.php';
        return calendar_upgrade();
    } else {
        // upgrades are done by the install script - return a generic error
        COM_errorLog("Calendar plugin upgrade function not implemented");
        return 3001;
    }
}

/**
* glFusion informs us that we're about to be enabled or disabled
*
* @param    boolean     $enabled    true = we're being enabled, false = disabled
* @return   void
*/
function plugin_enablestatechange_calendar ($enable)
{
    global $_TABLES;

    $is_enabled = $enable ? 1 : 0;

    // toggle calendar feeds
    DB_query ("UPDATE {$_TABLES['syndication']} SET is_enabled = $is_enabled WHERE type = 'calendar'");

    // toggle upcoming events block
    DB_query ("UPDATE {$_TABLES['blocks']} SET is_enabled = $is_enabled WHERE (type = 'phpblock') AND (phpblockfn = 'phpblock_calendar')");
}


/**
* Get path for the template files.
*
* @param    string  $path   subdirectory within the base template path
* @return   string          full path to template directory
*
*/
function calendar_templatePath ($path = '')
{
    global $_CONF;

    if (empty ($path)) {
        $layout_path = $_CONF['path_layout'] . calendar;
    } else {
        $layout_path = $_CONF['path_layout'] . calendar . '/' . $path;
    }

    if (is_dir ($layout_path)) {
        $retval = $layout_path;
    } else {
        $retval = $_CONF['path'] . 'plugins/calendar/templates';
        if (!empty ($path)) {
            $retval .= '/' . $path;
        }
    }

    return $retval;
}

/**
* Returns the URL of the plugin's icon
*
* @return   string      URL of the icon
*
*/
function plugin_geticon_calendar ()
{
    global $_CONF;

    return $_CONF['site_url'] . '/calendar/images/calendar.png';
}

/**
* glFusion is asking us to provide any items that show up in the type
* drop-down on search.php.  Let's users search for events.
*
* @return   array   (plugin name/entry title) pair for the dropdown
*
*/
function plugin_searchtypes_calendar()
{
    global $LANG_CAL_1;

    $tmp['calendar'] = $LANG_CAL_1[50];

    return $tmp;
}

/**
* This searches for events matching the user query and returns an array for the
* header and table rows back to search.php where it will be formated and printed
*
* @param    string  $query      Keywords user is looking for
* @param    date    $datestart  Start date to get results for
* @param    date    $dateend    End date to get results for
* @param    string  $topic      The topic they were searching in
* @param    string  $type       Type of items they are searching, or 'all' (deprecated)
* @param    int     $author     Get all results by this author
* @param    string  $keyType    search key type: 'all', 'phrase', 'any'
* @param    int     $page       page number of current search (deprecated)
* @param    int     $perpage    number of results per page (deprecated)
* @return   object              search result object
*
*/
function plugin_dopluginsearch_calendar($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $_TABLES, $LANG_CAL_1, $_USER;

    if ( COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }


    // Make sure the query is SQL safe
    $query = DB_escapeString(trim(htmlspecialchars($query)));

    $sql_e = "SELECT eid AS id, title, description, UNIX_TIMESTAMP(datestart) AS date, owner_id AS uid, hits, CONCAT('/calendar/event.php?eid=',eid) AS url ";
    $sql_e .= "FROM {$_TABLES['events']} WHERE status=1 ";

    $sql_p = "SELECT eid AS id, title, description, UNIX_TIMESTAMP(datestart) AS date, owner_id AS uid, 0 AS hits, ";
    $sql_p .= "CONCAT('/calendar/event.php?mode=personal&eid=',eid) AS url ";
    $sql_p .= "FROM {$_TABLES['personal_events']} WHERE (uid = $uid) ";

    $sql = COM_getPermSQL('AND') . ' ';

    if (!empty ($author))
        $sql .= "AND (owner_id = '".DB_escapeString($author)."') ";

    // Search the public events
    $search_e = new SearchCriteria('calendar', array($LANG_CAL_1[16],$LANG_CAL_1[24]));
    $columns = array('location','description','title');
    $sql .= $search_e->getDateRangeSQL('AND', 'UNIX_TIMESTAMP(datestart)', $datestart, $dateend);
    list($sql_tmp,$ftsql_tmp) = $search_e->buildSearchSQL($keyType, $query, $columns, $sql_e . $sql);
    $search_e->setSQL($sql_tmp);
    $search_e->setFTSQL($ftsql_tmp);
    $search_e->setRank(2);

    if ( COM_isAnonUser() ) {
        return $search_e;
    }

    // Search personal events
    $search_p = new SearchCriteria('calendar', array($LANG_CAL_1[16],$LANG_CAL_1[23]));
    $columns = array('location','description','title');
    $sql_tmp .= $search_p->getDateRangeSQL('AND', 'UNIX_TIMESTAMP(datestart)', $datestart, $dateend);
    list($sql_tmp,$ftsql_tmp) = $search_p->buildSearchSQL($keyType, $query, $columns, $sql_p . $sql);
    $search_p->setSQL($sql_tmp);
    $search_p->setFTSQL($ftsql_tmp);
    $search_p->setRank(2);

    return array($search_e,$search_p);
}


/**
* Set template variables
*
* @param    string  $templatename   name of template, e.g. 'header'
* @param    ref     $template       reference of actual template
* @return   void
*
* Note: A plugin should use its name as a prefix for the names of its
* template variables, e.g. 'calendar_xxx' and 'lang_calendar_xxx'.
* 'button_calendar' is an exception, as such a variable existed for header.thtml
*
*/
function plugin_templatesetvars_calendar ($templatename, &$template)
{
    global $LANG_CAL_1;

    if ($templatename == 'header') {
        $template->set_var ('button_calendar', $LANG_CAL_1[16]);
    }
}

/**
* Return information for an event
*
* @param    string  $eid        event ID or '*'
* @param    string  $what       comma-separated list of properties
* @param    int     $uid        user ID or 0 = current user
* @param    array   $options    (reserved for future extensions)
* @return   mixed               string or array of strings with the information
*
*/
function plugin_getiteminfo_calendar($eid, $what, $uid = 0, $options = array())
{
    global $_CONF, $_TABLES, $LANG_CAL_1;

    $properties = explode(',', $what);
    $fields = array();
    foreach ($properties as $p) {
        switch ($p) {
        case 'date' :
            $fields[] = 'UNIX_TIMESTAMP(datestart) AS unixdate';
            break;
        case 'description':
        case 'excerpt':
        case 'searchidx' :
            $fields[] = 'description';
            break;
        case 'raw-description' :
            $fields[] = 'description';
            break;
        case 'id':
            $fields[] = 'eid';
            break;
        case 'title':
            $fields[] = 'title';
            break;
        case 'url':
            // used whe $eid == * or when only requesting URL (so $fields is not empty)
            $fields[] = 'eid';
            break;
        case 'label':
		case 'status':
            $fields[] = 'eid';
            $fields[] = 'status';
            break;
        case 'author' :
        case 'author_name' :
            $fields[] = 'owner_id';
            break;
        case 'perms' :
            $fields[] = 'owner_id';
            $fields[] = 'group_id';
            $fields[] = 'perm_owner';
            $fields[] = 'perm_group';
            $fields[] = 'perm_members';
            $fields[] = 'perm_anon';
            break;
        case 'hits' :
            $fields[] = 'hits';
            break;
        default:
            break;
        }
    }

    $fields = array_unique($fields);

    if (count($fields) == 0) {
        $retval = array();
        return $retval;
    }

    if ($eid == '*') {
        $where = '';
        $permOp = 'WHERE';
    } else {
        $where = " WHERE eid = '" . DB_escapeString($eid) . "'";
        $permOp = 'AND';
    }
    if ($uid > 0) {
        $permSql = COM_getPermSql($permOp, $uid);
    } else {
        $permSql = COM_getPermSql($permOp);
    }
    $sql = "SELECT " . implode(',', $fields) . " FROM {$_TABLES['events']}" . $where . $permSql;
    if ($eid != '*') {
        $sql .= ' LIMIT 1';
    }

    $result = DB_query($sql);
    $numRows = DB_numRows($result);

    $retval = array();
    for ($i = 0; $i < $numRows; $i++) {
        $A = DB_fetchArray($result);

        $props = array();
        foreach ($properties as $p) {
            switch ($p) {
            case 'date' :
                $props[$p] = $A['unixdate'];
                break;
            case 'description':
            case 'excerpt':
            case 'searchidx' :
                $props[$p] = PLG_replaceTags($A['description'],'calendar','description');
                break;
            case 'raw-description' :
                $props[$p] = $A['description'];
                break;
            case 'id':
                $props['id'] = $A['eid'];
                break;
            case 'title':
                $props['title'] = $A['title'];
                break;
            case 'url':
                if (empty($A['eid'])) {
                    $props['url'] = $_CONF['site_url'] . '/calendar/event.php?eid=' . $eid;
                } else {
                    $props['url'] = $_CONF['site_url'] . '/calendar/event.php?eid=' . $A['eid'];
                }
                break;
            case 'label' :
                $props['label'] = $LANG_CAL_1[16];
                break;
            case 'status':
                $props['status'] = $A['status'];
                break;
            case 'author' :
                $props['author'] = $A['owner_id'];
                break;
            case 'author_name' :
                $props['author_name'] = COM_getDisplayName($A['owner_id']);
                break;
            case 'hits' :
                $props['hits'] = $A['hits'];
                break;
            case 'perms' :
                $props['perms'] = array(
                    'owner_id' => $A['owner_id'],
                    'group_id' => $A['group_id'],
                    'perm_owner' => $A['perm_owner'],
                    'perm_group' => $A['perm_group'],
                    'perm_members' => $A['perm_members'],
                    'perm_anon' => $A['perm_anon'],
                );
                break;
            default:
                $props[$p] = '';
                break;
            }
        }

        $mapped = array();
        foreach ($props as $key => $value) {
            if ($eid == '*') {
                if ($value != '') {
                    $mapped[$key] = $value;
                }
            } else {
                $mapped[$key] = $value;
            }
        }

        if ($eid == '*') {
            $retval[] = $mapped;
        } else {
            $retval = $mapped;
            break;
        }
    }

    if (($eid != '*') && (count($retval) == 1)) {
        $tRet = array_values($retval);
        $retval = $tRet[0];
    }

    return $retval;
}

function plugin_getconfigelementhelp_calendar($type, $option, $doclang = 'english' )
{
    global $_CONF;

    $retval = '';

    $baseUrl = $_CONF['site_url'];
    $cfg = 'docs/' . $doclang . '/calendar.html';
    if (@file_exists($_CONF['path_html'] . $cfg)) {
        $descUrl = $baseUrl . '/' . $cfg;
    } else {
        $descUrl = $baseUrl . '/docs/english/calendar.html';
    }
    $retval = $descUrl;

    return array($retval,0);
}

/**
* Creates a dropdown list of all the event types
*
* @param    string  $currtype   current event type (to preselect in the list)
* @return   string              <option> list of event types
*
*/
function CALENDAR_eventTypeList ($currtype = '')
{
    global $_CA_CONF;

    $retval = '';

    if (!isset($_CA_CONF['event_types']) ||
            !is_array($_CA_CONF['event_types'])) {
        $event_types = array();
    } else {
        $event_types = $_CA_CONF['event_types'];
    }
    asort($event_types);

    foreach ($event_types as $type) {
        $retval .= '<option value="' . $type . '"';
        if ($currtype == $type) {
            $retval .= ' selected="selected"';
        }
        $retval .= '>' . $type . '</option>';
    }

    return $retval;
}

function CALENDAR_siteHeader($title='', $meta='')
{
    global $_CA_CONF;

    switch( $_CA_CONF['displayblocks'] ) {
        case 0 : // left only
        case 2 :
            return( COM_siteHeader('menu',$title,$meta) );
            break;
        case 1 : // right only
        case 3 :
            return ( COM_siteHeader('none',$title,$meta) );
            break;
        default :
            return ( COM_siteHeader('menu',$title,$meta) );
            break;
    }
}

function CALENDAR_siteFooter() {
    global $_CONF, $_CA_CONF;

    $retval = '';

    switch( $_CA_CONF['displayblocks'] ) {
        case 0 : // left only
        case 3 : // none
            $retval .= COM_siteFooter();
            break;
        case 1 : // right only
        case 2 : // left and right
            $retval .= COM_siteFooter( true );
            break;
        default :
            $retval .= COM_siteFooter();
            break;
    }
    return $retval;
}

/**
* Create an am/pm selector dropdown menu
*
* @param    string  $name       name of the <select>
* @param    string  $selected   preselection: 'am' or 'pm'
* $param    string  $changefn   name of onchange() function to call
* @return   string  HTML for the dropdown; empty string in 24 hour mode
*
*/
function CALENDAR_getAmPmFormSelection( $name, $selected = '', $changefn = '' )
{
    global $_CONF;

    $retval = '';
    if( isset( $_CONF['hour_mode'] ) && ( $_CONF['hour_mode'] == 24 )) {
        $retval = '';
    } else {
        $selected = (empty($selected)) ? date('a') : $selected;
        $onchange = (empty($changefn)) ? '' : ' onchange="(' . $changefn . ')"';
        $retval .= '<select name="' . $name . '" id="' . $name . '"' . $onchange .'>' . LB;
        $retval .= '<option value="am"';
        $retval .= ($selected == 'am') ? ' selected="selected"' : '';
        $retval .= '>am</option>' . LB . '<option value="pm"';
        $retval .= ($selected == 'pm') ? ' selected="selected"' : '';
        $retval .= '>pm</option>' . LB . '</select>' . LB;
    }
    return $retval;
}

/**
* Set localised day and month names.
*
* @param    object  $aCalendar  reference(!) to a Calendar object
*
*/
function CALENDAR_setLanguage(&$aCalendar)
{
    global $_CONF, $LANG_WEEK, $LANG_MONTH;

    $lang_days = array ('sunday'    => $LANG_WEEK[1],
                        'monday'    => $LANG_WEEK[2],
                        'tuesday'   => $LANG_WEEK[3],
                        'wednesday' => $LANG_WEEK[4],
                        'thursday'  => $LANG_WEEK[5],
                        'friday'    => $LANG_WEEK[6],
                        'saturday'  => $LANG_WEEK[7]);

    $lang_months = array ('january'   => $LANG_MONTH[1],
                          'february'  => $LANG_MONTH[2],
                          'march'     => $LANG_MONTH[3],
                          'april'     => $LANG_MONTH[4],
                          'may'       => $LANG_MONTH[5],
                          'june'      => $LANG_MONTH[6],
                          'july'      => $LANG_MONTH[7],
                          'august'    => $LANG_MONTH[8],
                          'september' => $LANG_MONTH[9],
                          'october'   => $LANG_MONTH[10],
                          'november'  => $LANG_MONTH[11],
                          'december'  => $LANG_MONTH[12]);

    $aCalendar->setLanguage($lang_days, $lang_months, $_CONF['week_start']);
}
?>