<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Calendar Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001-2005 by the following authors:
*   Tony Bibbs - tony AT tonybibbs DOT com
*   Trinity Bays - trinity93 AT gmail DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

# index.php
$LANG_CAL_1 = array(
    1 => 'Calendar of Events',
    2 => 'I\'m sorry, there are no events to display.',
    3 => 'When',
    4 => 'Root',
    5 => 'Description',
    6 => 'Add An Event',
    7 => 'Upcoming Events',
    8 => 'By adding this event to your calendar you can quickly view only the events you are interested in by clicking "My Calendar" from the User Functions area.',
    9 => 'Add to My Calendar',
    10 => 'Remove from My Calendar',
    11 => 'Adding Event to %s\'s Calendar',
    12 => 'Event',
    13 => 'Starts',
    14 => 'Ends',
    15 => 'Back to Calendar',
    16 => 'Calendar',
    17 => 'Startdatum',
    18 => 'Einddatum',
    19 => 'Calendar Submissions',
    20 => 'Titel',
    21 => 'Startdatum',
    22 => 'URL',
    23 => 'Your Events',
    24 => 'Site Events',
    25 => 'There are no upcoming events',
    26 => 'Submit an Event',
    27 => "Submitting an event to {$_CONF['site_name']} will put your event on the master calendar where users can optionally add your event to their personal calendar. This feature is <b>NOT</b> meant to store your personal events such as birthdays and anniversaries.<br><br>Once you submit your event it will be sent to our administrators and if approved, your event will appear on the master calendar.",
    28 => 'Titel',
    29 => 'End Time',
    30 => 'Start Time',
    31 => 'All Day Event',
    32 => 'Adresregel 1',
    33 => 'Adresregel 2',
    34 => 'Plaats',
    35 => 'Provincie',
    36 => 'Postcode',
    37 => 'Event Type',
    38 => 'Edit Event Types',
    39 => 'Lokatie',
    40 => 'Add Event to',
    41 => 'Master Calendar',
    42 => 'Personal Calendar',
    43 => 'Link',
    44 => 'HTML tags zijn niet toegestaan',
    45 => 'Insturen',
    46 => 'Events in the system',
    47 => 'Top Ten Events',
    48 => 'Treffers',
    49 => 'It appears that there are no events on this site or no one has ever clicked on one.',
    50 => 'Events',
    51 => 'Verwijderen',
    52 => 'Submitted By',
    53 => 'Calendar View',
);

$_LANG_CAL_SEARCH = array(
    'results' => 'Calendar Results',
    'title' => 'Titel',
    'date_time' => 'Date & Time',
    'location' => 'Lokatie',
    'description' => 'Description'
);

###############################################################################
# calendar.php ($LANG30)

$LANG_CAL_2 = array(
    8 => 'Add Personal Event',
    9 => '%s Event',
    10 => 'Events for',
    11 => 'Master Calendar',
    12 => 'My Calendar',
    25 => 'Back to ',
    26 => 'Gehele Dag',
    27 => 'Week',
    28 => 'Personal Calendar for',
    29 => 'Public Calendar',
    30 => 'delete event',
    31 => 'Toevoegen',
    32 => 'Event',
    33 => 'Datum',
    34 => 'Tijdstip',
    35 => 'Quick Add',
    36 => 'Insturen',
    37 => 'Sorry, the personal calendar feature is not enabled on this site',
    38 => 'Personal Event Editor',
    39 => 'Day',
    40 => 'Week',
    41 => 'Month',
    42 => 'Add Master Event',
    43 => 'Event Submissions'
);

###############################################################################
# admin/plugins/calendar/index.php, formerly admin/event.php ($LANG22)

$LANG_CAL_ADMIN = array(
    1 => 'Event Editor',
    2 => 'Foutmelding',
    3 => 'Opmaak',
    4 => 'Event URL',
    5 => 'Event Start Date',
    6 => 'Event End Date',
    7 => 'Event Location',
    8 => 'Event Description',
    9 => '(include http://)',
    10 => 'You must provide the dates/times, event title, and description',
    11 => 'Calendar Manager',
    12 => 'To modify or delete an event, click on that event\'s edit icon below.  To create a new event, click on "Create New" above. Click on the copy icon to create a copy of an existing event.',
    13 => 'Eigenaar',
    14 => 'Startdatum',
    15 => 'Einddatum',
    16 => '',
    17 => "You are trying to access an event that you don't have rights to.  This attempt has been logged. Please <a href=\"{$_CONF['site_admin_url']}/plugins/calendar/index.php\">go back to the event administration screen</a>.",
    18 => '',
    19 => '',
    20 => 'Opslaan',
    21 => 'Annuleren',
    22 => 'Verwijderen',
    23 => 'Bad start date.',
    24 => 'Bad end date.',
    25 => 'End date is before start date.',
    26 => 'Batch Event Manager',
    27 => 'These are the events in your database that are older than ',
    28 => ' months. Update the time period as desired, and then click Update List.  Select one or more events from the results displayed, and then click on the Delete icon below to remove these older events from your database.  Only events displayed and selected on this page will be deleted.',
    29 => '',
    30 => 'Update lijst',
    31 => 'Are You sure you want to permanently delete ALL selected users?',
    32 => 'Toon alles',
    33 => 'No events selected for deletion',
    34 => 'Event ID',
    35 => 'Kan niet worden verwijderd',
    36 => 'Sucessfully deleted',
    37 => 'Moderate Event',
    38 => 'Batch Event Admin',
    39 => 'Event Admin',
    40 => 'Event List',
    41 => 'This screen allows you to edit / create events. Edit the fields below and save.',
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Link: to a Calendar event on this site; link_text defaults to event title: [calendar:<i>event_id</i> {link_text}]',
);

$LANG_CAL_MESSAGE = array(
    'save' => 'Your event has been successfully saved.',
    'delete' => 'The event has been successfully deleted.',
    'private' => 'The event has been saved to your calendar',
    'login' => 'Cannot open your personal calendar until you login',
    'removed' => 'Event was successfully removed from your personal calendar',
    'noprivate' => 'Sorry, personal calendars are not enabled on this site',
    'unauth' => 'Sorry, you do not have access to the event administration page.  Please note that all attempts to access unauthorized features are logged',
    'delete_confirm' => 'Are you sure you want to delete this event?'
);

$PLG_calendar_MESSAGE4 = "Thank-you for submitting an event to {$_CONF['site_name']}.  It has been submitted to our staff for approval.  If approved, your event will be seen here, in our <a href=\"{$_CONF['site_url']}/calendar/index.php\">calendar</a> section.";
$PLG_calendar_MESSAGE17 = 'Your event has been successfully saved.';
$PLG_calendar_MESSAGE18 = 'The event has been successfully deleted.';
$PLG_calendar_MESSAGE24 = 'The event has been saved to your calendar.';
$PLG_calendar_MESSAGE26 = 'The event has been successfully deleted.';

// Messages for the plugin upgrade
$PLG_calendar_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Calendar',
    'title' => 'Calendar Configuration'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => 'Calendar Login Required',
    'hidecalendarmenu' => 'Hide Calendar Menu Entry',
    'personalcalendars' => 'Enable Personal Calendars',
    'eventsubmission' => 'Enable Submission Queue',
    'showupcomingevents' => 'Show Upcoming Events',
    'upcomingeventsrange' => 'Upcoming Events Range',
    'event_types' => 'Event Types',
    'hour_mode' => 'Uur Notatie',
    'notification' => 'Notification Email',
    'delete_event' => 'Delete Events with Owner',
    'aftersave' => 'After Saving Event',
    'default_permissions' => 'Event Default Permissions',
    'only_admin_submit' => 'Only Allow Admins to Submit',
    'displayblocks' => 'Display glFusion Blocks',
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'General Calendar Settings',
    'fs_permissions' => 'Default Permissions'
);

$LANG_configSelect['calendar'] = array(
    0 => array(1=> 'Ja', 0 => 'Nee'),
    1 => array(true => 'Ja', false => 'Nee'),
    6 => array(12 => '12', 24 => '24'),
    9 => array('item'=>'Forward to Event', 'list'=>'Display Admin List', 'plugin'=>'Display Calendar', 'home'=>'Toon Startpagina', 'admin'=>'Toon Beheerpagina'),
    12 => array(0=>'Geen Toegang', 2=>'Alleen lezen', 3=>'Lezen-Schrijven'),
    13 => array(0=>'Linker Blokken', 1=>'Right Blocks', 2=>'Linker & Rechter Blokken', 3=>'Geen')
);

?>
