<?php
// +--------------------------------------------------------------------------+
// | Calendar Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | glFusion Calendar Plugin administration page.                            |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark A. Howard         mark AT usable-web DOT com                        |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

$display = '';

// Ensure user even has the rights to access this page
if (!SEC_hasRights('calendar.edit')) {
    $display .= COM_siteHeader('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[35],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter();

    // Log attempt to error.log
    COM_accessLog("User {$_USER['username']} tried to illegally access the event administration screen.");

    echo $display;

    exit;
}


/**
* Shows event editor
*
* @param    string  $action action we are performing: 'edit', 'clone' or 'moderate'
* @param    array   $A      array holding the event's details
* @param    string  $msg    an optional error message to display
* @return   string          HTML for event editor or error message
*
*/
function CALENDAR_edit($action, $A, $msg = '')
{
    global $_CONF, $_USER, $_GROUPS, $_TABLES, $_USER, $_CA_CONF, $LANG_CAL_1,
           $LANG_CAL_ADMIN, $LANG10, $LANG12, $LANG_ACCESS, $LANG_ADMIN,
           $MESSAGE;

    USES_lib_admin();

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/calendar/index.php',
              'text' => $LANG_CAL_ADMIN[40]),
        array('url' => $_CONF['site_admin_url'] . '/moderation.php',
              'text' => $LANG_ADMIN['submissions']),
        array('url' => $_CONF['site_admin_url'] . '/plugins/calendar/index.php?batchadmin=x',
              'text' => $LANG_CAL_ADMIN[38]),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    switch ($action) {

        case 'edit':
        case 'clone':
            $blocktitle = $LANG_CAL_ADMIN[1];       // Event Editor
            $saveoption = $LANG_ADMIN['save'];      // Save
            break;

        case 'moderate':
            $blocktitle = $LANG_CAL_ADMIN[37];      // Moderate Event
            $saveoption = $LANG_ADMIN['moderate'];  // Save & Approve
            break;
    }

    if (!empty ($msg)) {
        $retval .= COM_showMessageText($msg,$LANG_CAL_ADMIN[2],true);
    }

    $event_templates = new Template($_CONF['path'] . 'plugins/calendar/templates/admin');
    $event_templates->set_file('editor','eventeditor.thtml');
    $event_templates->set_var('lang_allowed_html', COM_allowedHTML(SEC_getUserPermissions(),false,'calendar','description'));
    $event_templates->set_var('lang_postmode', $LANG_CAL_ADMIN[3]);

    if (!isset($A['perm_owner']) ) {
        $A['perm_owner'][0] = "0";
    }
    if (!isset($A['perm_group']) ) {
        $A['perm_group'][0] = "0";
    }
    if (!isset($A['perm_members']) ) {
        $A['perm_members'][0] = "0";
    }
    if (!isset($A['perm_anon']) ) {
        $A['perm_anon'][0] = "0";
    }

    if ($action <> 'moderate' AND !empty($A['eid'])) {
        // Get what level of access user has to this object
        $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);
        if ($access == 0 OR $access == 2) {
            // Uh, oh!  User doesn't have access to this object
            $retval .= COM_showMessageText($LANG_CAL_ADMIN[17],$LANG_ACCESS['accessdenied'],true,'error');
            COM_accessLog("User {$_USER['username']} tried to illegally submit or edit event $eid.");
            return $retval;
        }
    } else {
        if ( !isset($A['owner_id']) || $A['owner_id'] == '' ) {
            $A['owner_id'] = $_USER['uid'];
        }
        if (isset ($_GROUPS['Calendar Admin'])) {
            $A['group_id'] = $_GROUPS['Calendar Admin'];
        } else {
            $A['group_id'] = SEC_getFeatureGroup('calendar.edit');
        }
        SEC_setDefaultPermissions ($A, $_CA_CONF['default_permissions']);
        $access = 3;
    }

    if ($action == 'moderate') {
        $event_templates->set_var('post_options', COM_optionList($_TABLES['postmodes'],'code,name','plaintext'));
    } else {
        if (!isset ($A['postmode'])) {
            $A['postmode'] = $_CONF['postmode'];
        }
        $event_templates->set_var('post_options', COM_optionList($_TABLES['postmodes'],'code,name',$A['postmode']));
    }

    $retval .= COM_startBlock($blocktitle, '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_CAL_ADMIN[41], plugin_geticon_calendar());

    if (!empty($A['eid'])) {
        $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                   . '" name="delete"%s/>';
        $jsconfirm = ' onclick="return confirm(\'' . $MESSAGE[76] . '\');"';
        $event_templates->set_var( 'lang_delete_confirm',$MESSAGE[76]);
        $event_templates->set_var ('delete_option',
                                   sprintf ($delbutton, $jsconfirm));
        $event_templates->set_var ('delete_option_no_confirmation',
                                   sprintf ($delbutton, ''));
        if ($action == 'moderate') {
            $event_templates->set_var('submission_option',
                '<input type="hidden" name="type" value="submission"/>');
        }
    } else { // new event
        $A['eid'] = COM_makesid ();
        $A['status'] = 1;
        $A['title'] = '';
        $A['description'] = '';
        $A['url'] = '';
        $A['hits'] = 0;

        // in case a start date/time has been passed from the calendar,
        // pick it up for the end date/time
        if (empty ($A['dateend'])) {
            $A['dateend'] = $A['datestart'];
        }
        if (empty ($A['timeend'])) {
            $A['timeend'] = $A['timestart'];
        }
        $A['event_type'] = '';
        $A['location'] = '';
        $A['address1'] = '';
        $A['address2'] = '';
        $A['city'] = '';
        $A['state'] = '';
        $A['zipcode'] = '';
        $A['allday'] = 0;
    }

    $event_templates->set_var('event_id', $A['eid']);
    $event_templates->set_var('lang_eventtitle', $LANG_ADMIN['title']);
    $A['title'] = str_replace('{','&#123;',$A['title']);
    $A['title'] = str_replace('}','&#125;',$A['title']);
    $A['title'] = str_replace('"','&quot;',$A['title']);
    $event_templates->set_var('event_title', $A['title']);
    $event_templates->set_var('lang_eventtype', $LANG_CAL_1[37]);
    $event_templates->set_var('lang_editeventtypes', $LANG12[50]);
    $event_templates->set_var('type_options',
                              CALENDAR_eventTypeList ($A['event_type']));
    $event_templates->set_var('status_checked',$A['status'] == 1 ? ' checked="checked"' : '');
    $event_templates->set_var('lang_eventurl', $LANG_CAL_ADMIN[4]);
    $event_templates->set_var('max_url_length', 255);
    $event_templates->set_var('event_url', $A['url']);
    $event_templates->set_var('lang_includehttp', $LANG_CAL_ADMIN[9]);
    $event_templates->set_var('lang_eventstartdate', $LANG_CAL_ADMIN[5]);
    //$event_templates->set_var('event_startdate', $A['datestart']);
    $event_templates->set_var('lang_starttime', $LANG_CAL_1[30]);

    // Combine date/time for easier manipulation
    $A['datestart'] = trim ($A['datestart'] . ' ' . $A['timestart']);
    if (empty ($A['datestart'])) {
        $start_stamp = time ();
    } else {
        $start_stamp = strtotime ($A['datestart']);
    }
    $A['dateend'] = trim ($A['dateend'] . ' ' . $A['timeend']);
    if (empty ($A['dateend'])) {
        $end_stamp = time ();
    } else {
        $end_stamp = strtotime ($A['dateend']);
    }
    $start_month = date('m', $start_stamp);
    $start_day = date('d', $start_stamp);
    $start_year = date('Y', $start_stamp);
    $end_month = date('m', $end_stamp);
    $end_day = date('d', $end_stamp);
    $end_year = date('Y', $end_stamp);

    $start_hour = date ('H', $start_stamp);
    $start_minute = intval (date ('i', $start_stamp) / 15) * 15;
    if ($start_hour >= 12) {
        $startampm = 'pm';
    } else {
        $startampm = 'am';
    }
    $start_hour_24 = $start_hour % 24;
    if ($start_hour > 12) {
        $start_hour = $start_hour - 12;
    } else if ($start_hour == 0) {
        $start_hour = 12;
    }

    $end_hour = date('H', $end_stamp);
    $end_minute = intval (date('i', $end_stamp) / 15) * 15;
    if ($end_hour >= 12) {
        $endampm = 'pm';
    } else {
        $endampm = 'am';
    }
    $end_hour_24 = $end_hour % 24;
    if ($end_hour > 12) {
        $end_hour = $end_hour - 12;
    } else if ($end_hour == 0) {
        $end_hour = 12;
    }

    $month_options = COM_getMonthFormOptions ($start_month);
    $event_templates->set_var ('startmonth_options', $month_options);

    $month_options = COM_getMonthFormOptions ($end_month);
    $event_templates->set_var ('endmonth_options', $month_options);

    $day_options = COM_getDayFormOptions ($start_day);
    $event_templates->set_var ('startday_options', $day_options);

    $day_options = COM_getDayFormOptions ($end_day);
    $event_templates->set_var ('endday_options', $day_options);

    $year_options = COM_getYearFormOptions ($start_year);
    $event_templates->set_var ('startyear_options', $year_options);

    $year_options = COM_getYearFormOptions ($end_year);
    $event_templates->set_var ('endyear_options', $year_options);

    if (isset ($_CA_CONF['hour_mode']) && ($_CA_CONF['hour_mode'] == 24)) {
        $hour_options = COM_getHourFormOptions ($start_hour_24, 24);
        $event_templates->set_var ('starthour_options', $hour_options);

        $hour_options = COM_getHourFormOptions ($end_hour_24, 24);
        $event_templates->set_var ('endhour_options', $hour_options);

        $event_templates->set_var ('hour_mode', 24);
    } else {
        $hour_options = COM_getHourFormOptions ($start_hour);
        $event_templates->set_var ('starthour_options', $hour_options);

        $hour_options = COM_getHourFormOptions ($end_hour);
        $event_templates->set_var ('endhour_options', $hour_options);

        $event_templates->set_var ('hour_mode', 12);
    }

    $event_templates->set_var ('startampm_selection',
                        CALENDAR_getAmPmFormSelection ('start_ampm', $startampm, 'update_ampm()'));
    $event_templates->set_var ('endampm_selection',
                        CALENDAR_getAmPmFormSelection ('end_ampm', $endampm));

    $event_templates->set_var ('startminute_options',
                               COM_getMinuteFormOptions ($start_minute, 15));
    $event_templates->set_var ('endminute_options',
                               COM_getMinuteFormOptions ($end_minute, 15));

    $event_templates->set_var('lang_enddate', $LANG12[13]);
    $event_templates->set_var('lang_eventenddate', $LANG_CAL_ADMIN[6]);
    $event_templates->set_var('event_enddate', $A['dateend']);
    $event_templates->set_var('lang_enddate', $LANG12[13]);
    $event_templates->set_var('lang_endtime', $LANG_CAL_1[29]);
    $event_templates->set_var('lang_alldayevent', $LANG_CAL_1[31]);
    if ($A['allday'] == 1) {
        $event_templates->set_var('allday_checked', 'checked="checked"');
    }
    $event_templates->set_var('lang_location',$LANG12[51]);
    $event_templates->set_var('event_location', $A['location']);
    $event_templates->set_var('lang_addressline1',$LANG12[44]);
    $event_templates->set_var('event_address1', $A['address1']);
    $event_templates->set_var('lang_addressline2',$LANG12[45]);
    $event_templates->set_var('event_address2', $A['address2']);
    $event_templates->set_var('lang_city',$LANG12[46]);
    $event_templates->set_var('event_city', $A['city']);
    $event_templates->set_var('lang_state',$LANG12[47]);
    $event_templates->set_var('state_options', '');
    $event_templates->set_var('event_state', $A['state']);
    $event_templates->set_var('lang_zipcode',$LANG12[48]);
    $event_templates->set_var('event_zipcode', $A['zipcode']);
    $event_templates->set_var('lang_eventlocation', $LANG_CAL_ADMIN[7]);
    $event_templates->set_var('event_location', $A['location']);
    $event_templates->set_var('lang_eventdescription', $LANG_CAL_ADMIN[8]);
    $event_templates->set_var('event_description', $A['description']);
    $event_templates->set_var('lang_hits', $LANG10[30]);
    $event_templates->set_var('hits', COM_numberFormat ($A['hits']));
    $event_templates->set_var('lang_save', $saveoption);
    $event_templates->set_var('lang_cancel', $LANG_ADMIN['cancel']);

    // user access info
    $event_templates->set_var('lang_accessrights',$LANG_ACCESS['accessrights']);
    $event_templates->set_var('lang_owner', $LANG_ACCESS['owner']);
    $ownername = COM_getDisplayName ($A['owner_id']);
    $event_templates->set_var('owner_username', DB_getItem($_TABLES['users'],
                              'username', "uid = {$A['owner_id']}"));
    $event_templates->set_var('owner_name', $ownername);
    $event_templates->set_var('owner', $ownername);
    $event_templates->set_var('owner_id', $A['owner_id']);
    $event_templates->set_var('lang_group', $LANG_ACCESS['group']);
    $event_templates->set_var('group_dropdown',
                              SEC_getGroupDropdown ($A['group_id'], $access));
    $event_templates->set_var('lang_permissions', $LANG_ACCESS['permissions']);
    $event_templates->set_var('lang_permissionskey', $LANG_ACCESS['permissionskey']);
    $event_templates->set_var('permissions_editor', SEC_getPermissionsHTML($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']));
    $event_templates->set_var('gltoken_name', CSRF_TOKEN);
    $event_templates->set_var('gltoken', SEC_createToken());
    $event_templates->parse('output', 'editor');
    $retval .= $event_templates->finish($event_templates->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}

/**
* Saves an event to the database
*
* @param    array       $_POST fields
* @return   string      HTML redirect or error message
*
*/
function CALENDAR_save( $eid, $C )
{
    global $_CONF, $_TABLES, $_USER, $LANG_CAL_ADMIN, $MESSAGE, $_CA_CONF;

    $allday = (isset($C['allday'])) ? COM_applyFilter($C['allday']) : '';
    $hour_mode = (isset($C['hour_mode']) && ($C['hour_mode'] == 24)) ? 24 : 12;
    if ($hour_mode == 24) {
        // these aren't set in 24 hour mode
        $C['start_ampm'] = '';
        $C['end_ampm'] = '';
    }
    $status = $C['status'];
    $title = $C['title'];
    $event_type = $C['event_type'];
    $url = $C['url'];
    $start_month = COM_applyFilter ($C['start_month'], true);
    $start_day = COM_applyFilter ($C['start_day'], true);
    $start_year = COM_applyFilter ($C['start_year'], true);
    $start_hour = COM_applyFilter ($C['start_hour'], true);
    $start_minute = COM_applyFilter ($C['start_minute'], true);
    $start_ampm = $C['start_ampm'];
    $end_month = COM_applyFilter ($C['end_month'], true);
    $end_day = COM_applyFilter ($C['end_day'], true);
    $end_year = COM_applyFilter ($C['end_year'], true);
    $end_hour = COM_applyFilter ($C['end_hour'], true);
    $end_minute = COM_applyFilter ($C['end_minute'], true);
    $end_ampm = $C['end_ampm'];
    $location = $C['location'];
    $address1 = $C['address1'];
    $address2 = $C['address2'];
    $city = $C['city'];
    $state = $C['state'];
    $zipcode = $C['zipcode'];
    $description = $C['description'];
    $postmode = $C['postmode'];
    $owner_id = COM_applyFilter ($C['owner_id'], true);
    $group_id = COM_applyFilter ($C['group_id'], true);
    $perm_owner = $C['perm_owner'];
    $perm_group = $C['perm_group'];
    $perm_members = isset($C['perm_members']) ? $C['perm_members'] : '';
    $perm_anon = isset($C['perm_anon']) ? $C['perm_anon'] : '';
    $type = (isset($C['type'])) ? COM_applyFilter($C['type']) : '';
    $C['datestart'] = sprintf('%4d-%02d-%02d',$start_year, $start_month, $start_day);
    $C['timestart'] = $start_hour . ':' . $start_minute . ':00';
    $C['dateend'] = sprintf('%4d-%02d-%02d', $end_year, $end_month, $end_day);
    $C['timeend'] = $end_hour . ':' . $end_minute . ':00';
    $C['allday'] = $allday;
    $C['hits'] = 0;

    $retval = '';

    // Convert array values to numeric permission values
    list($perm_owner,
        $perm_group,
        $perm_members,
        $perm_anon) = SEC_getPermissionValues($perm_owner,
                                              $perm_group,
                                              $perm_members,
                                              $perm_anon);

    $access = 0;
    if (DB_count ($_TABLES['events'], 'eid', $eid) > 0) {
        $result = DB_query ("SELECT owner_id,group_id,perm_owner,perm_group,"
                           ."perm_members,perm_anon FROM {$_TABLES['events']} "
                           ."WHERE eid = '{$eid}'");
        $A = DB_fetchArray ($result);
        $access = SEC_hasAccess ($A['owner_id'], $A['group_id'],
                $A['perm_owner'], $A['perm_group'], $A['perm_members'],
                $A['perm_anon']);
    } else {
        $access = SEC_hasAccess ($owner_id, $group_id, $perm_owner, $perm_group,
                $perm_members, $perm_anon);
    }
    if (($access < 3) || !SEC_inGroup ($group_id)) {
        $retval .= COM_siteHeader('menu', $MESSAGE[30]);
        $retval .= COM_showMessageText($MESSAGE[31],$MESSAGE[30],true,'error');
        $retval .= COM_siteFooter();
        COM_accessLog ("User {$_USER['username']} tried to illegally submit or edit event $eid.");
        return $retval;
    }

    if ($hour_mode == 24) {
        // to avoid having to mess with the tried and tested code below, map
        // the 24-hour values onto their 12-hour counterparts and use those
        if ($start_hour >= 12) {
            $start_ampm = 'pm';
            $start_hour = $start_hour - 12;
        } else {
            $start_ampm = 'am';
            $start_hour = $start_hour;
        }
        if ($start_hour == 0) {
            $start_hour = 12;
        }
        if ($end_hour >= 12) {
            $end_ampm = 'pm';
            $end_hour = $end_hour - 12;
        } else {
            $end_ampm = 'am';
            $end_hour = $end_hour;
        }
        if ($end_hour == 0) {
            $end_hour = 12;
        }
    }

    if ($allday == 'on') {
        $allday = 1;
    } else {
        $allday = 0;
    }

    // Make sure start date is before end date
    if (checkdate ($start_month, $start_day, $start_year)) {
        $datestart = sprintf('%4d-%02d-%02d',
                             $start_year, $start_month, $start_day);
        $timestart = $start_hour . ':' . $start_minute . ':00';
    } else {
        $retval .= COM_siteHeader ('menu', $LANG_CAL_ADMIN[2]);
        $retval .= COM_showMessageText($LANG_CAL_ADMIN[23],$LANG_CAL_ADMIN[2],true,'error');
        $retval .= CALENDAR_edit('edit',$C,'');
        $retval .= COM_siteFooter ();
        return $retval;
    }
    if (checkdate ($end_month, $end_day, $end_year)) {
        $dateend = sprintf('%4d-%02d-%02d', $end_year, $end_month, $end_day);
        $timeend = $end_hour . ':' . $end_minute . ':00';
    } else {
        $retval .= COM_siteHeader ('menu', $LANG_CAL_ADMIN[2]);
        $retval .= COM_showMessageText($LANG_CAL_ADMIN[24],$LANG_CAL_ADMIN[2],true,'error');
        $retval .= CALENDAR_edit('edit',$C,'');
        $retval .= COM_siteFooter ();
        return $retval;
    }
    if ($allday == 0) {
        if ($dateend < $datestart) {
            $retval .= COM_siteHeader ('menu', $LANG_CAL_ADMIN[2]);
            $retval .= COM_showMessageText($LANG_CAL_ADMIN[25],$LANG_CAL_ADMIN[2],true,'error');
            $retval .= CALENDAR_edit('edit',$C,'');
            $retval .= COM_siteFooter ();
            return $retval;
        }
    } else {
        if ($dateend < $datestart) {
            // Force end date to be same as start date
            $dateend = $datestart;
        }
    }

    // clean 'em up
    if ($postmode == 'html') {
        $description = COM_checkHTML (COM_checkWords ($description));
    } else {
        $postmode = 'plaintext';
        $description = @htmlspecialchars (COM_checkWords ($description));
    }
    $description = DB_escapeString ($description);
    $title       = DB_escapeString (COM_checkHTML (COM_checkWords ($title)));
    $location    = DB_escapeString (COM_checkHTML (COM_checkWords ($location)));
    $address1    = DB_escapeString (COM_checkHTML (COM_checkWords ($address1)));
    $address2    = DB_escapeString (COM_checkHTML (COM_checkWords ($address2)));
    $city        = DB_escapeString (COM_checkHTML (COM_checkWords ($city)));
    $state       = DB_escapeString (COM_checkHTML (COM_checkWords ($state)));
    $zipcode     = DB_escapeString (COM_checkHTML (COM_checkWords ($zipcode)));
    $event_type  = DB_escapeString (strip_tags (COM_checkWords ($event_type)));
    $url         = DB_escapeString (strip_tags ($url));

    if ($allday == 0) {
        // Add 12 to make time on 24 hour clock if needed
        if ($start_ampm == 'pm' AND $start_hour <> 12) {
            $start_hour = $start_hour + 12;
        }
        // If 12AM set hour to 00
        if ($start_ampm == 'am' AND $start_hour == 12) {
            $start_hour = '00';
        }
        // Add 12 to make time on 24 hour clock if needed
        if ($end_ampm == 'pm' AND $end_hour <> 12) {
           $end_hour = $end_hour + 12;
        }
        // If 12AM set hour to 00
        if ($end_ampm == 'am' AND $end_hour == 12) {
            $end_hour = '00';
        }
        $timestart = $start_hour . ':' . $start_minute . ':00';
        $timeend = $end_hour . ':' . $end_minute . ':00';
    }

    if (!empty ($eid) AND !empty ($description) AND !empty ($title)) {

        DB_delete ($_TABLES['eventsubmission'], 'eid', $eid);

        DB_save($_TABLES['events'],
               'eid,status,title,event_type,url,allday,datestart,dateend,timestart,'
               .'timeend,location,address1,address2,city,state,zipcode,description,'
               .'postmode,owner_id,group_id,perm_owner,perm_group,perm_members,'
               .'perm_anon',
               "'$eid',$status,'$title','$event_type','$url',$allday,'$datestart',"
               ."'$dateend','$timestart','$timeend','$location','$address1',"
               ."'$address2','$city','$state','$zipcode','$description','$postmode',"
               ."$owner_id,$group_id,$perm_owner,$perm_group,$perm_members,$perm_anon");
        if (DB_count ($_TABLES['personal_events'], 'eid', $eid) > 0) {
            $result = DB_query ("SELECT uid FROM {$_TABLES['personal_events']} "
                               ."WHERE eid = '{$eid}'");
            $numrows = DB_numRows ($result);
            for ($i = 1; $i <= $numrows; $i++) {
                $P = DB_fetchArray ($result);
                DB_save ($_TABLES['personal_events'],
                        'eid,status,title,event_type,datestart,dateend,address1,address2,'
                       .'city,state,zipcode,allday,url,description,postmode,'
                       .'group_id,owner_id,perm_owner,perm_group,perm_members,'
                       .'perm_anon,uid,location,timestart,timeend',
                        "'$eid',$status,'$title','$event_type','$datestart','$dateend',"
                       ."'$address1','$address2','$city','$state','$zipcode',"
                       ."$allday,'$url','$description','$postmode',$group_id,"
                       ."$owner_id,$perm_owner,$perm_group,$perm_members,"
                       ."$perm_anon,{$P['uid']},'$location','$timestart','$timeend'");
            }
        }
        PLG_itemSaved($eid, 'calendar');
        COM_rdfUpToDateCheck('calendar', $event_type, $eid);
        // if we just saved a submission, then return to the submissions page
        if ($type == 'submission') {
            return COM_refresh($_CONF['site_admin_url'] . '/moderation.php');
        } else {
            return PLG_afterSaveSwitch (
                    $_CA_CONF['aftersave'],
                    $_CONF['site_url'] . '/calendar/event.php?eid=' . $eid,
                    'calendar',
                    17
                );
        }
    } else {
        $retval .= COM_siteHeader ('menu', $LANG_CAL_ADMIN[2]);
        $retval .= COM_showMessageText($LANG_CAL_ADMIN[10],$LANG_CAL_ADMIN[2],true,'error');
        $retval .= CALENDAR_edit('edit',$C,'');
        $retval .= COM_siteFooter ();
        return $retval;
    }
}

/**
* Delete an event
*
* @param    string  $eid    id of event to delete
* @param    string  $type   'submission' when attempting to delete a submission
* @param    string          HTML redirect
*/
function CALENDAR_delete($eid, $type = '')
{
    global $_CONF, $_TABLES, $_USER;

    if (empty($type)) { // delete regular event
        $result = DB_query("SELECT * FROM {$_TABLES['events']} WHERE eid = '".DB_escapeString($eid)."'");
        $A = DB_fetchArray($result);
        $access = SEC_hasAccess($A['owner_id'], $A['group_id'],
                    $A['perm_owner'], $A['perm_group'], $A['perm_members'],
                    $A['perm_anon']);
        if ($access < 3) {
            COM_accessLog("User {$_USER['username']} tried to illegally delete event $eid.");
            return COM_refresh($_CONF['site_admin_url']
                               . '/plugins/calendar/index.php');
        }

        DB_delete($_TABLES['events'], 'eid', DB_escapeString($eid));
        DB_delete($_TABLES['personal_events'], 'eid', DB_escapeString($eid));
        PLG_itemDeleted($eid, 'calendar');
        COM_rdfUpToDateCheck('calendar', $A['event_type'], $A['eid']);

        return COM_refresh($_CONF['site_admin_url'] . '/plugins/calendar/index.php?msg=18');
    } elseif ($type == 'submission') {
        if (plugin_ismoderator_calendar()) {
            DB_delete($_TABLES['eventsubmission'], 'eid', DB_escapeString($eid));
            return COM_refresh($_CONF['site_admin_url'] . '/moderation.php');
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally delete event submission $eid.");
        }
    } else {
        COM_accessLog("User {$_USER['username']} tried to illegally delete event $eid of type $type.");
    }

    return COM_refresh($_CONF['site_admin_url']
                       . '/plugins/calendar/index.php');
}


/**
* This function deletes the events selected in the CALENDAR_listBatch function
*
* @return   string          HTML with success or error message
*
*/
function CALENDAR_batchDelete() {
    global $_CONF, $LANG_CAL_ADMIN;
    $msg = '';
    $event_list = array();
    if (isset($_POST['delitem'])) {
        $event_list = $_POST['delitem'];
    }

    if (count($event_list) == 0) {
        $msg = $LANG_CAL_ADMIN[33] . "<br/>";
    }
    $c = 0;
    if (isset($event_list) AND is_array($event_list)) {
        foreach($event_list as $delitem) {
            $delitem = COM_applyFilter($delitem);
            if (!CALENDAR_delete($delitem)) {
                $msg .= "<strong>{$LANG_CAL_ADMIN[34]} $delitem $LANG_CAL_ADMIN[35]}</strong><br/>\n";
            } else {
                $c++; // count the deleted users
            }
        }
    }
    // Since this function is used for deletion only, its necessary to say that
    // zero were deleted instead of just leaving this message away.
    COM_numberFormat($c); // just in case we have more than 999)..
    $msg .= "{$LANG_CAL_ADMIN[36]}: $c<br/>\n";
    return $msg;
}

function CALENDAR_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $LANG28, $LANG_ACCESS, $LANG_ADMIN, $LANG_CAL_MESSAGE;

    $retval = '';

    $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],
                            $A['perm_group'],$A['perm_members'],$A['perm_anon']);
    $enabled = ($A['status']==1) ? true : false;

    switch($fieldname) {
        case "edit":
            if ($access == 3) {
                $attr['title'] = $LANG_ADMIN['edit'];
                $retval = COM_createLink(
                    $icon_arr['edit'],
                    $_CONF['site_admin_url'] . '/plugins/calendar/index.php'
                    . '?edit=x&amp;eid=' . $A['eid'], $attr);
            } else {
                $retval = $icon_arr['blank'];
            }
            break;

        case 'copy':
            if ($access >= 2) {
                $attr['title'] = $LANG_ADMIN['copy'];
                $retval = COM_createLink(
                    $icon_arr['copy'],
                    $_CONF['site_admin_url'] . '/plugins/calendar/index.php'
                    . '?clone=x&amp;eid=' . $A['eid'], $attr);
            } else {
                $retval = $icon_arr['blank'];
            }
            break;

        case 'title':
            $title = $A['title'];
            if ($enabled) {
                $retval = COM_createLink( $title,"{$_CONF['site_url']}/calendar/event.php?eid={$A['eid']}" );
            } else {
                $retval = '<span class="disabledfield">' . $title . '</span>';
            }
            break;

        case 'username':
            $owner = COM_getDisplayName( $A['owner_id'], $A['username'], $A['fullname'] );
            $retval = ($enabled) ? $owner : '<span class="disabledfield">' . $owner . '</span>';
            break;

        case 'access':
            if ($access == 3) {
                $privs = $LANG_ACCESS['edit'];
            } else {
                $privs = $LANG_ACCESS['readonly'];
            }
            $retval = ($enabled) ? $privs : '<span class="disabledfield">' . $privs . '</span>';
            break;

        case 'allday':
            $check = ($enabled) ? $icon_arr['check'] : $icon_arr['greycheck'];
            $retval = ($A['allday'] == 1) ? $check : '';
            break;

        case 'delete':
            if ($access == 3) {
                $attr['title'] = $LANG_ADMIN['delete'];
                $attr['onclick'] = "return confirm('" . $LANG_CAL_MESSAGE['delete_confirm'] . "');";
                $retval = COM_createLink(
                    $icon_arr['delete'],
                    $_CONF['site_admin_url'] . '/plugins/calendar/index.php'
                    . '?delete=x&amp;eid=' . $A['eid'] . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            } else {
                $retval = $icon_arr['blank'];
            }
            break;

        case 'status':
            if ($access == 3) {
                if ($enabled) {
                    $switch = ' checked="checked"';
                    $title = 'title="' . $LANG_ADMIN['disable'] . '" ';
                } else {
                    $switch = '';
                    $title = 'title="' . $LANG_ADMIN['enable'] . '" ';
                }
                $retval = '<input type="checkbox" name="enabledevents[' . $A['eid'] . ']" ' . $title
                    . 'onclick="submit()" value="1"' . $switch . '/>';
                $retval .= '<input type="hidden" name="eidarray[' . $A['eid'] . ']" value="1" />';
            } else {
                $retval = ($enabled) ? $LANG_ACCESS['yes'] : $LANG_ACCESS['no'];
            }
            break;

        case 'rostatus':
            $retval = ($enabled) ? $LANG_ACCESS['yes'] : '<span class="disabledfield">' . $LANG_ACCESS['no'] . '</span>';
            break;


      default:
            $retval = ($enabled) ? $fieldvalue : '<span class="disabledfield">' . $fieldvalue . '</span>';
            break;
    }
    return $retval;
}

function CALENDAR_list()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_CAL_ADMIN, $LANG_ACCESS, $LANG_CAL_2,
           $_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/calendar/index.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'] . '/moderation.php',
              'text' => $LANG_ADMIN['submissions']),
        array('url' => $_CONF['site_admin_url'] . '/plugins/calendar/index.php?batchadmin=x',
              'text' => $LANG_CAL_ADMIN[38]),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG_CAL_ADMIN[11], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_CAL_ADMIN[12], plugin_geticon_calendar());

    $header_arr = array(      # display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center', 'width' => '35px'),
    	array('text' => $LANG_ADMIN['copy'], 'field' => 'copy', 'sort' => false, 'align' => 'center', 'width' => '35px'),
        array('text' => $LANG_ADMIN['title'], 'field' => 'title', 'sort' => true),
        array('text' => $LANG_CAL_ADMIN[13], 'field' => 'username', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG_ACCESS['access'], 'field' => 'access', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_CAL_ADMIN[14], 'field' => 'datestart', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG_CAL_ADMIN[15], 'field' => 'dateend', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG_CAL_2[26], 'field' => 'allday', 'sort' => true, 'align' => 'center', 'width' => '40px'),
	array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center', 'width' => '35px'),
        array('text' => $LANG_ADMIN['enabled'], 'field' => 'status', 'sort' => true, 'align' => 'center', 'width' => '35px')
    );

    $defsort_arr = array('field' => 'datestart', 'direction' => 'desc');

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . '/plugins/calendar/index.php'
    );

    // sql query which drives the list
    $sql = "SELECT {$_TABLES['events']}.*, {$_TABLES['users']}.username, {$_TABLES['users']}.fullname "
          ."FROM {$_TABLES['events']} "
          ."LEFT JOIN {$_TABLES['users']} "
          ."ON {$_TABLES['events']}.owner_id={$_TABLES['users']}.uid "
          ."WHERE 1=1 ";

    $query_arr = array(
        'table' => 'events',
        'sql' => $sql,
        'query_fields' => array('title', 'datestart', 'dateend'),
        'default_filter' => COM_getPermSQL('AND')
    );

    // create the security token, and embed it in the list form
    // also set the hidden var which signifies that this list allows for pages
    // to be enabled/disabled via checkbox
    $token = SEC_createToken();
    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'.$token.'"/>',
        'bottom' => '<input type="hidden" name="eventenabler" value="true"/>'
    );

    $retval .= ADMIN_list ('calendar', 'CALENDAR_getListField',
                           $header_arr, $text_arr, $query_arr,
                           $defsort_arr, '', $token, '', $form_arr);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

function CALENDAR_listBatch()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_CAL_ADMIN, $LANG_CAL_2,
           $LANG_ACCESS, $LANG01, $_IMAGE_TYPE;

    USES_lib_admin();

    $display = COM_startBlock($LANG_CAL_ADMIN[26], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    if (isset($_REQUEST['usr_time'])) {
        $usr_time = $_REQUEST['usr_time'];
    } else {
        $usr_time = 12;
    }

    // create the menu at the top
    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/calendar/index.php',
            'text' => $LANG_CAL_ADMIN[39]),
        array('url' => $_CONF['site_admin_url'],
            'text' => $LANG_ADMIN['admin_home'])
    );

    $cal_templates = new Template($_CONF['path'] . 'plugins/calendar/templates/admin');
    $cal_templates->set_file (array ('form' => 'batchadmin.thtml'));
    $cal_templates->set_var('usr_time', $usr_time);
    $cal_templates->set_var('lang_text_start', $LANG_CAL_ADMIN[27]);
    $cal_templates->set_var('lang_text_end', $LANG_CAL_ADMIN[28]);
    $cal_templates->set_var('lang_updatelist', $LANG_CAL_ADMIN[30]);
    $cal_templates->set_var('lang_delete_sel', $LANG_ADMIN['delete_sel']);
    $cal_templates->set_var('lang_delconfirm', $LANG_CAL_ADMIN[31]);
    $cal_templates->parse('form', 'form');
    $desc = $cal_templates->finish($cal_templates->get_var('form'));

    $display .= ADMIN_createMenu($menu_arr, $desc, plugin_geticon_calendar());

    $header_arr = array(      # display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['title'], 'field' => 'title', 'sort' => true),
        array('text' => $LANG_CAL_ADMIN[13], 'field' => 'username', 'sort' => true),
        array('text' => $LANG_ACCESS['access'], 'field' => 'access', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_CAL_ADMIN[14], 'field' => 'datestart', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG_CAL_ADMIN[15], 'field' => 'dateend', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG_CAL_2[26], 'field' => 'allday', 'sort' => true, 'align' => 'center', 'width' => '40px'),
        array('text' => $LANG_ADMIN['enabled'], 'field' => 'rostatus', 'sort' => true, 'align' => 'center', 'width' => '40px')
    );

    $text_arr = array(
        'has_extras' => true,
        'form_url' => $_CONF['site_admin_url'] . "/plugins/calendar/index.php?batchadmin=x"
    );

    $sql = "SELECT {$_TABLES['events']}.*, {$_TABLES['users']}.username, {$_TABLES['users']}.fullname "
        ."FROM {$_TABLES['events']} "
        ."LEFT JOIN {$_TABLES['users']} "
        ."ON {$_TABLES['events']}.owner_id={$_TABLES['users']}.uid "
        ."WHERE 1=1 ";

    $filterstr = " AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(dateend) > " . $usr_time * 2592000 . " ";

    $query_arr = array (
        'table' => 'events',
        'sql' => $sql,
        'query_fields' => array('title', 'datestart', 'dateend'),
        'default_filter' => $filterstr . COM_getPermSQL('AND')
    );

    $defsort_arr = array('field' => 'datestart', 'direction' => 'desc');

    $options = array('chkselect' => true, 'chkfield' => 'eid');

    // create the security token, and embed it in the list form
    $token = SEC_createToken();
    $form_arr['bottom'] = "<input type=\"hidden\" name=\"" . CSRF_TOKEN
                        . "\" value=\"{$token}\"/>";

    $display .= ADMIN_list('calendar', 'CALENDAR_getListField',
                            $header_arr, $text_arr, $query_arr,
                            $defsort_arr, '', $token, $options, $form_arr);

    $display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $display;
}

/**
* Toggle status of a staticpage from enabled to disabled and back
*
* @param    array   $enabledstaticpages    array of sp_id's available
* @param    array   $spidarray             array of status (1/0)
* @return   void
*
*/
function CALENDAR_toggleStatus($enabledevents, $eidarray)
{
    global $_TABLES, $_DB_table_prefix;
    if (isset($eidarray) && is_array($eidarray) ) {
        foreach ($eidarray AS $eid => $junk ) {
            $eid = COM_applyFilter($eid);
            if (isset($enabledevents[$eid])) {
                DB_query ("UPDATE {$_TABLES['events']} SET status = '1' WHERE eid = '".DB_escapeString($eid)."'");
            } else {
                DB_query ("UPDATE {$_TABLES['events']} SET status = '0' WHERE eid = '".DB_escapeString($eid)."'");
            }
        }
    }
    PLG_itemSaved($eid,'calendar');
    CTL_clearCache();
    /* fixme - add logic to update personal events table as well */
    /* logic should enable/disable all personal_event 'children' */
}

// MAIN ========================================================================

$action = '';
$expected = array('edit','moderate','clone','save','delete','batchadmin','delbutton_x','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

$eid = 0;
if (isset($_POST['eid'])) {
    $eid = COM_applyFilter($_POST['eid'], true);
} elseif (isset($_GET['eid'])) {
    $eid = COM_applyFilter($_GET['eid'], true);
}

$msg = (isset($_GET['msg'])) ? COM_applyFilter($_GET['msg']) : '';
$type = (isset($_POST['type'])) ? COM_applyFilter($_POST['type']) : '';

//$validtoken = SEC_checkToken();

if (isset($_POST['eventenabler']) && SEC_checkToken()) {
    $enabledevents = array();
    if (isset($_POST['enabledevents'])) {
        $enabledevents = $_POST['enabledevents'];
    }
    $eidarray = array();
    if ( isset($_POST['eidarray']) ) {
        $eidarray = $_POST['eidarray'];
    }
    CALENDAR_toggleStatus($enabledevents,$eidarray);
    // force a refresh to redisplay calendar event status
    header ('Location: ' . $_CONF['site_admin_url'] . '/plugins/calendar/index.php');
    exit;
}

switch ($action) {

    case 'edit':
    case 'moderate':
        if (empty ($eid)) {
            // new event submission by admin, set default values
            $A = array ();
            $A['datestart'] = '';
            if (isset($_POST['datestart'])) {
                $A['datestart'] = COM_applyFilter($_POST['datestart']);
            } elseif (isset($_GET['datestart'])) {
                $A['datestart'] = COM_applyFilter($_GET['datestart']);
            }
            $A['timestart'] = '';
            if (isset($_POST['timestart'])) {
                $A['timestart'] = COM_applyFilter($_POST['timestart']);
            } elseif (isset($_GET['timestart'])) {
                $A['timestart'] = COM_applyFilter($_GET['timestart']);
            }
        } else {
            if ($action == 'edit') {
                $title = $LANG_CAL_ADMIN[1]; // Event Editor
                $result = DB_query("SELECT * FROM {$_TABLES['events']} WHERE eid ='$eid'");
                $A = DB_fetchArray($result);
            } else {
                $title = $LANG_CAL_ADMIN[37]; // Moderate Event
                $result = DB_query("SELECT * FROM {$_TABLES['eventsubmission']} WHERE eid ='$eid'");
                $A = DB_fetchArray($result);
                $A['hits'] = 0;
            }
        }
        $display .= COM_siteHeader ('menu', $LANG_CAL_ADMIN[1]);
        $display .= CALENDAR_edit($action, $A);
        $display .= COM_siteFooter ();
        break;

    case 'clone':
        $result = DB_query("SELECT * FROM {$_TABLES['events']} WHERE eid ='$eid'");
        $A = DB_fetchArray($result);
        $A['eid'] = COM_makesid();
        $title = $A['title'] . ' (' . $LANG_ADMIN['copy'] . ')';
        $A['title'] = $title;
        $A['owner_id'] = $_USER['uid'];
        $display .= COM_siteHeader ('menu', $LANG_CAL_ADMIN[1]);
        $display .= CALENDAR_edit($action, $A);
        $display .= COM_siteFooter();
        break;

    case 'save':
        if (SEC_checkToken()) {
            $allday = (isset($_POST['allday'])) ? COM_applyFilter($_POST['allday']) : '';
            $hour_mode = (isset($_POST['hour_mode']) && ($_POST['hour_mode'] == 24)) ? 24 : 12;
            if ($hour_mode == 24) {
                // these aren't set in 24 hour mode
                $_POST['start_ampm'] = '';
                $_POST['end_ampm'] = '';
            }
            $display .= CALENDAR_save($eid,$_POST);
        } else {
            COM_accessLog('User ' . $_USER['username'] . ' tried to illegally edit event ' . $eid . ' and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'delete':
        if (!isset ($eid) || empty ($eid) || ($eid == 0)) {
            COM_errorLog ('User ' . $_USER['username'] . ' attempted to delete event, eid empty, null, or is 0');
            $display .= COM_refresh($_CONF['site_admin_url'] . '/plugins/calendar/index.php');
        } elseif (SEC_checkToken()) {
            $display .= CALENDAR_delete($eid, $type);
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally delete event $eid and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'batchadmin':
        $display .= COM_siteHeader ('menu', $LANG_CAL_ADMIN[11]);
        $display .= (is_numeric($msg) && ($msg > 0)) ? COM_showMessage( $msg, 'calendar' ) : '';
        $display .= CALENDAR_listBatch();
        $display .= COM_siteFooter ();
        break;

    case 'delbutton_x':
        if (SEC_checkToken()) {
            $msg = CALENDAR_batchDelete();
            $display .= COM_siteHeader ('menu', $LANG_CAL_ADMIN[11])
                . COM_showMessageText($msg)
                . CALENDAR_listBatch()
                . COM_siteFooter();
        } else {
            COM_accessLog('User ' . $_USER['username'] . ' tried to illegally batch delete events and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        if (($action == 'cancel') && ($type == 'submission')) {
            echo COM_refresh($_CONF['site_admin_url'] . '/moderation.php');
        } else {
            $display .= COM_siteHeader ('menu', $LANG_CAL_ADMIN[11]);
            if(isset($msg)) {
                $display .= (is_numeric($msg)) ? COM_showMessage($msg, 'calendar') : COM_showMessageText( $msg );
            }
            $display .= CALENDAR_list();
            $display .= COM_siteFooter();
        }
        break;

}

echo $display;

?>