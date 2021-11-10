<?php

$_FF_CONF['enable_prefixes'] = 1;

//$_TABLES['ff_warninglevels'] = 'gl_forum_warninglevels';
//$_TABLES['ff_warningtypes'] = 'gl_forum_warningtypes';
//$_TABLES['ff_warnings'] = 'gl_forum_warnings';
$_FF_CONF['warning_enabled'] = 1;
$_FF_CONF['warning_max_points'] = 100;

global $LANG_GF01;
$LANG_GFXX = array(
    'warnings' => 'Warnings',
    'warning_levels' => 'Warning Levels',
    'warning_level' => 'Warning Level',
    'warning_types' => 'Warning Types',
    'warning_type' => 'Warning Type',
    'log' => 'Log',
    'month' => 'Month',
    'year' => 'Year',
    'week' => 'Week',
    'day' => 'Day',
    'months' => 'Months',
    'years' => 'Years',
    'weeks' => 'Weeks',
    'days' => 'Days',
    'dscp' => 'Description',
    'points' => 'Points',
    'issued' => 'Issued',
    'expires' => 'Expires',
    'expires_after'  => 'Expires After',
    'issued_by' => 'Issued By',
    'status' => 'status',
    'username' => 'User Name',
    'revoked_by' => 'Revoked by',
    'expired' => 'Expired',
    'active' => 'Active',
    'no_restriction' => 'No restriction',
    'user_banned' => 'User is banned from the forum',
    'user_suspended' => 'User\'s posting permission is suspended',
    'user_moderated' => 'User\'s forum posts are moderated',
    'action' => 'Action',
    'percent' => 'Percent',
    'admin_notes' => 'Administrative Notes',
    'revoke_reason' => 'Reason for Revocation',
    'revoke' => 'Revoke',
);
$LANG_GF01 = array_merge($LANG_GF01, $LANG_GFXX);
